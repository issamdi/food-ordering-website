<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$pdo = getDBConnection();

// Get customers from orders (unique emails)
$stmt = $pdo->query("
    SELECT 
        customer_email as email,
        customer_name as name,
        customer_phone as phone,
        COUNT(*) as total_orders,
        SUM(total_amount) as total_spent,
        MAX(created_at) as last_order
    FROM orders
    GROUP BY customer_email
    ORDER BY last_order DESC
");
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Admin Dashboard</title>
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
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-header h1 { font-size: 1.8rem; font-weight: 700; }

        .card {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 1.5rem; backdrop-filter: blur(10px);
        }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 0.8rem 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 0.85rem; }
        table th { color: rgba(255,255,255,0.5); font-weight: 500; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        table tr:hover { background: rgba(255,255,255,0.03); }
        .empty-state { text-align: center; padding: 3rem; color: rgba(255,255,255,0.4); }
        .empty-state i { font-size: 2.5rem; margin-bottom: 1rem; display: block; }

        .mobile-toggle { display: none; position: fixed; top: 1rem; left: 1rem; background: rgba(102,126,234,0.9); border: none; color: #fff; width: 40px; height: 40px; border-radius: 10px; font-size: 1.1rem; cursor: pointer; z-index: 200; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-toggle { display: flex; align-items: center; justify-content: center; }
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
            <li><a href="index.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
            <li><a href="menu.php"><i class="fas fa-hamburger"></i> Menu Items</a></li>
            <li><a href="customers.php" class="active"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="../index.html" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-users"></i> Customers</h1>
            <span style="color:rgba(255,255,255,0.5);font-size:0.9rem;"><?php echo count($customers); ?> customers</span>
        </div>

        <div class="card">
            <?php if (empty($customers)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>No customers yet. Customer data appears here after orders are placed.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Last Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['email']); ?></td>
                                <td><?php echo htmlspecialchars($c['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo $c['total_orders']; ?></td>
                                <td>$<?php echo number_format($c['total_spent'], 2); ?></td>
                                <td><?php echo date('M j, Y', strtotime($c['last_order'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
