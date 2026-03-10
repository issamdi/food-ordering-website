<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$pdo = getDBConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int) $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $allowed_statuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        header('Location: orders.php?msg=updated');
        exit;
    }
}

// Filter
$filter = $_GET['filter'] ?? 'all';
$where = '';
$params = [];

if ($filter !== 'all') {
    $allowed = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
    if (in_array($filter, $allowed)) {
        $where = 'WHERE status = ?';
        $params[] = $filter;
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$count_sql = "SELECT COUNT(*) as total FROM orders $where";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total = $count_stmt->fetch()['total'];
$total_pages = ceil($total / $per_page);

$sql = "SELECT * FROM orders $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0f0f23; color: #e0e0e0; min-height: 100vh; }
        .sidebar {
            position: fixed; top: 0; left: 0; width: 250px; height: 100vh;
            background: rgba(255,255,255,0.03); border-right: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(20px); padding: 1.5rem 0; z-index: 100; display: flex; flex-direction: column;
        }
        .sidebar-brand { padding: 0 1.5rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); text-align: center; }
        .sidebar-brand h2 { font-size: 1.2rem; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .sidebar-brand small { color: rgba(255,255,255,0.4); font-size: 0.75rem; }
        .sidebar-nav { list-style: none; padding: 1rem 0; flex: 1; }
        .sidebar-nav li a { display: flex; align-items: center; gap: 0.75rem; padding: 0.8rem 1.5rem; color: rgba(255,255,255,0.6); text-decoration: none; transition: all 0.3s; font-size: 0.9rem; }
        .sidebar-nav li a:hover, .sidebar-nav li a.active { color: #fff; background: rgba(102,126,234,0.15); border-left: 3px solid #667eea; }
        .sidebar-nav li a i { width: 20px; text-align: center; }
        .sidebar-footer { padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.08); }
        .sidebar-footer a { color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; }
        .sidebar-footer a:hover { color: #ff6b6b; }

        .main-content { margin-left: 250px; padding: 2rem; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { font-size: 1.8rem; font-weight: 700; }

        .filters { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
        .filter-btn {
            padding: 0.5rem 1rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.6); text-decoration: none;
            font-size: 0.8rem; transition: all 0.3s; cursor: pointer;
        }
        .filter-btn:hover, .filter-btn.active { background: rgba(102,126,234,0.2); color: #667eea; border-color: #667eea; }

        .card {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 1.5rem; backdrop-filter: blur(10px);
        }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 0.8rem 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 0.85rem; }
        table th { color: rgba(255,255,255,0.5); font-weight: 500; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        table tr:hover { background: rgba(255,255,255,0.03); }

        .badge { padding: 0.3rem 0.7rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .badge-pending { background: rgba(255,159,67,0.2); color: #ff9f43; }
        .badge-confirmed { background: rgba(102,126,234,0.2); color: #667eea; }
        .badge-preparing { background: rgba(118,75,162,0.2); color: #764ba2; }
        .badge-ready { background: rgba(72,199,142,0.2); color: #48c78e; }
        .badge-delivered { background: rgba(72,199,142,0.3); color: #48c78e; }
        .badge-cancelled { background: rgba(255,107,107,0.2); color: #ff6b6b; }
        .badge-paid { background: rgba(72,199,142,0.2); color: #48c78e; }
        .badge-failed { background: rgba(255,107,107,0.2); color: #ff6b6b; }

        .status-form select {
            padding: 0.3rem 0.5rem; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
            border-radius: 8px; color: #fff; font-size: 0.75rem; font-family: inherit;
        }
        .status-form button {
            padding: 0.3rem 0.6rem; background: #667eea; border: none; border-radius: 8px;
            color: #fff; font-size: 0.7rem; cursor: pointer; transition: background 0.3s;
        }
        .status-form button:hover { background: #764ba2; }

        .pagination { display: flex; gap: 0.5rem; justify-content: center; margin-top: 1.5rem; }
        .pagination a, .pagination span {
            padding: 0.5rem 0.8rem; border-radius: 8px; font-size: 0.8rem; text-decoration: none;
            border: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.6);
        }
        .pagination a:hover { background: rgba(102,126,234,0.2); color: #667eea; }
        .pagination .current { background: #667eea; color: #fff; border-color: #667eea; }

        .success-msg { background: rgba(72,199,142,0.15); border: 1px solid rgba(72,199,142,0.3); color: #48c78e; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.85rem; }
        .empty-state { text-align: center; padding: 3rem; color: rgba(255,255,255,0.4); }
        .empty-state i { font-size: 2.5rem; margin-bottom: 1rem; display: block; }

        .order-details-btn { color: #667eea; cursor: pointer; text-decoration: underline; background: none; border: none; font-size: 0.85rem; font-family: inherit; }

        .mobile-toggle { display: none; position: fixed; top: 1rem; left: 1rem; background: rgba(102,126,234,0.9); border: none; color: #fff; width: 40px; height: 40px; border-radius: 10px; font-size: 1.1rem; cursor: pointer; z-index: 200; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-toggle { display: flex; align-items: center; justify-content: center; }
        }

        /* Modal */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 300; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal { background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 2rem; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; }
        .modal h3 { margin-bottom: 1rem; font-size: 1.2rem; }
        .modal-close { float: right; background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; }
        .detail-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 0.85rem; }
        .detail-row .label { color: rgba(255,255,255,0.5); }
        .items-list { margin-top: 1rem; }
        .items-list h4 { font-size: 0.9rem; margin-bottom: 0.5rem; color: rgba(255,255,255,0.7); }
        .item-row { padding: 0.4rem 0; font-size: 0.8rem; color: rgba(255,255,255,0.6); }
    </style>
</head>
<body>
    <button class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">
        <i class="fas fa-bars"></i>
    </button>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2><i class="fas fa-utensils"></i> Admin Panel</h2>
            <small><?php echo htmlspecialchars(APP_NAME); ?></small>
        </div>
        <ul class="sidebar-nav">
            <li><a href="index.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="orders.php" class="active"><i class="fas fa-shopping-bag"></i> Orders</a></li>
            <li><a href="menu.php"><i class="fas fa-hamburger"></i> Menu Items</a></li>
            <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="../index.html" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-shopping-bag"></i> Orders</h1>
            <span style="color: rgba(255,255,255,0.5); font-size: 0.9rem;"><?php echo $total; ?> total orders</span>
        </div>

        <?php if ($msg === 'updated'): ?>
            <div class="success-msg"><i class="fas fa-check-circle"></i> Order status updated successfully.</div>
        <?php endif; ?>

        <div class="filters">
            <a href="orders.php" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
            <a href="orders.php?filter=pending" class="filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
            <a href="orders.php?filter=confirmed" class="filter-btn <?php echo $filter === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
            <a href="orders.php?filter=preparing" class="filter-btn <?php echo $filter === 'preparing' ? 'active' : ''; ?>">Preparing</a>
            <a href="orders.php?filter=ready" class="filter-btn <?php echo $filter === 'ready' ? 'active' : ''; ?>">Ready</a>
            <a href="orders.php?filter=delivered" class="filter-btn <?php echo $filter === 'delivered' ? 'active' : ''; ?>">Delivered</a>
            <a href="orders.php?filter=cancelled" class="filter-btn <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
        </div>

        <div class="card">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No orders found.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <button class="order-details-btn" onclick="showOrderDetails(<?php echo htmlspecialchars(json_encode($order)); ?>)">
                                        <?php echo htmlspecialchars($order['order_number']); ?>
                                    </button>
                                </td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><span class="badge badge-<?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                                <td><?php echo date('M j, g:i A', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <form method="POST" class="status-form" style="display:flex;gap:0.3rem;align-items:center;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="new_status">
                                            <?php foreach (['pending','confirmed','preparing','ready','delivered','cancelled'] as $s): ?>
                                                <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_status"><i class="fas fa-check"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="orders.php?page=<?php echo $i; ?>&filter=<?php echo urlencode($filter); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Order Details Modal -->
    <div class="modal-overlay" id="orderModal">
        <div class="modal">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <h3>Order Details</h3>
            <div id="orderDetailsContent"></div>
        </div>
    </div>

    <script>
    function showOrderDetails(order) {
        let items = [];
        try { items = JSON.parse(order.items); } catch(e) {}
        
        let html = `
            <div class="detail-row"><span class="label">Order Number</span><span>${escapeHtml(order.order_number)}</span></div>
            <div class="detail-row"><span class="label">Customer</span><span>${escapeHtml(order.customer_name)}</span></div>
            <div class="detail-row"><span class="label">Email</span><span>${escapeHtml(order.customer_email)}</span></div>
            <div class="detail-row"><span class="label">Phone</span><span>${escapeHtml(order.customer_phone || 'N/A')}</span></div>
            <div class="detail-row"><span class="label">Address</span><span>${escapeHtml(order.delivery_address || 'N/A')}</span></div>
            <div class="detail-row"><span class="label">Subtotal</span><span>$${parseFloat(order.subtotal).toFixed(2)}</span></div>
            <div class="detail-row"><span class="label">Tax</span><span>$${parseFloat(order.tax_amount).toFixed(2)}</span></div>
            <div class="detail-row"><span class="label">Delivery Fee</span><span>$${parseFloat(order.delivery_fee).toFixed(2)}</span></div>
            <div class="detail-row"><span class="label">Total</span><strong>$${parseFloat(order.total_amount).toFixed(2)}</strong></div>
            <div class="detail-row"><span class="label">Status</span><span class="badge badge-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></div>
            <div class="detail-row"><span class="label">Payment</span><span class="badge badge-${order.payment_status}">${order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1)}</span></div>
            <div class="detail-row"><span class="label">Date</span><span>${order.created_at}</span></div>
        `;
        
        if (items.length > 0) {
            html += '<div class="items-list"><h4>Order Items:</h4>';
            items.forEach(item => {
                html += `<div class="item-row">${escapeHtml(item.title || item.name || 'Item')} x${item.quantity} — $${parseFloat(item.price).toFixed(2)}</div>`;
            });
            html += '</div>';
        }
        
        document.getElementById('orderDetailsContent').innerHTML = html;
        document.getElementById('orderModal').classList.add('show');
    }
    
    function closeModal() {
        document.getElementById('orderModal').classList.remove('show');
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    document.getElementById('orderModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    </script>
</body>
</html>
