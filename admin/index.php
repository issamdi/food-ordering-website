<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$pdo = getDBConnection();

// Dashboard stats
$stats = [];

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
$stats['total_orders'] = $stmt->fetch()['total'] ?? 0;

// Today's orders
$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
$stats['today_orders'] = $stmt->fetch()['total'] ?? 0;

// Total revenue
$stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE payment_status = 'paid'");
$stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;

// Pending orders
$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status IN ('pending', 'confirmed', 'preparing')");
$stats['pending_orders'] = $stmt->fetch()['total'] ?? 0;

// Menu items count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM menu_items");
$stats['menu_items'] = $stmt->fetch()['total'] ?? 0;

// Recent orders (last 10)
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
$recent_orders = $stmt->fetchAll();

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logoutAdmin();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f0f23;
            color: #e0e0e0;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 250px;
            height: 100vh;
            background: rgba(255,255,255,0.03);
            border-right: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(20px);
            padding: 1.5rem 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }
        .sidebar-brand {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            text-align: center;
        }
        .sidebar-brand h2 {
            font-size: 1.2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .sidebar-brand small { color: rgba(255,255,255,0.4); font-size: 0.75rem; }
        .sidebar-nav { list-style: none; padding: 1rem 0; flex: 1; }
        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.8rem 1.5rem;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        .sidebar-nav li a:hover,
        .sidebar-nav li a.active {
            color: #fff;
            background: rgba(102,126,234,0.15);
            border-left: 3px solid #667eea;
        }
        .sidebar-nav li a i { width: 20px; text-align: center; }
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-footer a {
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s;
        }
        .sidebar-footer a:hover { color: #ff6b6b; }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .page-header h1 { font-size: 1.8rem; font-weight: 700; }
        .page-header .admin-info {
            color: rgba(255,255,255,0.5);
            font-size: 0.85rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        .stat-card .stat-icon {
            width: 45px; height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        .stat-card .stat-icon.blue { background: rgba(102,126,234,0.2); color: #667eea; }
        .stat-card .stat-icon.green { background: rgba(72,199,142,0.2); color: #48c78e; }
        .stat-card .stat-icon.orange { background: rgba(255,159,67,0.2); color: #ff9f43; }
        .stat-card .stat-icon.purple { background: rgba(118,75,162,0.2); color: #764ba2; }
        .stat-card .stat-icon.red { background: rgba(255,107,107,0.2); color: #ff6b6b; }
        .stat-card .stat-value { font-size: 1.8rem; font-weight: 700; margin-bottom: 0.3rem; }
        .stat-card .stat-label { color: rgba(255,255,255,0.5); font-size: 0.85rem; }

        /* Table */
        .card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.2rem;
        }
        .card-header h3 { font-size: 1.1rem; font-weight: 600; }
        .card-header a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.85rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 0.8rem 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            font-size: 0.85rem;
        }
        table th {
            color: rgba(255,255,255,0.5);
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table tr:hover { background: rgba(255,255,255,0.03); }
        .badge {
            padding: 0.3rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-pending { background: rgba(255,159,67,0.2); color: #ff9f43; }
        .badge-confirmed { background: rgba(102,126,234,0.2); color: #667eea; }
        .badge-preparing { background: rgba(118,75,162,0.2); color: #764ba2; }
        .badge-ready { background: rgba(72,199,142,0.2); color: #48c78e; }
        .badge-delivered { background: rgba(72,199,142,0.3); color: #48c78e; }
        .badge-cancelled { background: rgba(255,107,107,0.2); color: #ff6b6b; }
        .badge-paid { background: rgba(72,199,142,0.2); color: #48c78e; }
        .badge-failed { background: rgba(255,107,107,0.2); color: #ff6b6b; }
        .badge-refunded { background: rgba(255,159,67,0.2); color: #ff9f43; }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: rgba(255,255,255,0.4);
        }
        .empty-state i { font-size: 2.5rem; margin-bottom: 1rem; display: block; }

        /* Mobile */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 1rem; left: 1rem;
            background: rgba(102,126,234,0.9);
            border: none;
            color: #fff;
            width: 40px; height: 40px;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            z-index: 200;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-toggle { display: flex; align-items: center; justify-content: center; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            table { font-size: 0.75rem; }
        }
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
            <li><a href="index.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
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
            <h1>Dashboard</h1>
            <div class="admin-info">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars(getAdminName()); ?> 
                <span style="color:rgba(255,255,255,0.3);">(<?php echo htmlspecialchars(getAdminRole()); ?>)</span>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
                <div class="stat-value"><?php echo $stats['today_orders']; ?></div>
                <div class="stat-label">Today's Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-value">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-clock"></i></div>
                <div class="stat-value"><?php echo $stats['pending_orders']; ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-utensils"></i></div>
                <div class="stat-value"><?php echo $stats['menu_items']; ?></div>
                <div class="stat-label">Menu Items</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Recent Orders</h3>
                <a href="orders.php">View All <i class="fas fa-arrow-right"></i></a>
            </div>

            <?php if (empty($recent_orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No orders yet. Orders will appear here once customers place them.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><span class="badge badge-<?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                                <td><?php echo date('M j, g:i A', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>