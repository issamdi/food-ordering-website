<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$pdo = getDBConnection();
$msg = $_GET['msg'] ?? '';
$error = '';

// Handle add item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $prep_time = (int) ($_POST['preparation_time'] ?? 15);
    $ingredients = trim($_POST['ingredients'] ?? '');
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    if (empty($name) || $price <= 0 || empty($category)) {
        $error = 'Name, price, and category are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO menu_items (name, description, price, category, preparation_time, ingredients, is_available) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $category, $prep_time, $ingredients, $is_available]);
        header('Location: menu.php?msg=added');
        exit;
    }
}

// Handle edit item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
    $id = (int) $_POST['item_id'];
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $prep_time = (int) ($_POST['preparation_time'] ?? 15);
    $ingredients = trim($_POST['ingredients'] ?? '');
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    if (empty($name) || $price <= 0 || empty($category)) {
        $error = 'Name, price, and category are required.';
    } else {
        $stmt = $pdo->prepare("UPDATE menu_items SET name=?, description=?, price=?, category=?, preparation_time=?, ingredients=?, is_available=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$name, $description, $price, $category, $prep_time, $ingredients, $is_available, $id]);
        header('Location: menu.php?msg=updated');
        exit;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: menu.php?msg=deleted');
    exit;
}

// Handle toggle availability
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE menu_items SET is_available = NOT is_available, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: menu.php?msg=toggled');
    exit;
}

// Get all menu items
$stmt = $pdo->query("SELECT * FROM menu_items ORDER BY category, name");
$items = $stmt->fetchAll();

// Get unique categories
$categories = array_unique(array_column($items, 'category'));
sort($categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Admin Dashboard</title>
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

        .btn {
            padding: 0.6rem 1.2rem; border-radius: 10px; border: none; color: #fff;
            font-size: 0.85rem; cursor: pointer; transition: all 0.3s; font-family: inherit; display: inline-flex; align-items: center; gap: 0.5rem;
        }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102,126,234,0.3); }
        .btn-danger { background: rgba(255,107,107,0.8); }
        .btn-danger:hover { background: #ff6b6b; }
        .btn-sm { padding: 0.3rem 0.7rem; font-size: 0.75rem; border-radius: 8px; }

        .card {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 1.5rem; backdrop-filter: blur(10px); margin-bottom: 1.5rem;
        }
        .card h3 { margin-bottom: 1rem; font-size: 1.1rem; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 0.8rem 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 0.85rem; }
        table th { color: rgba(255,255,255,0.5); font-weight: 500; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        table tr:hover { background: rgba(255,255,255,0.03); }

        .badge { padding: 0.3rem 0.7rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .badge-available { background: rgba(72,199,142,0.2); color: #48c78e; }
        .badge-unavailable { background: rgba(255,107,107,0.2); color: #ff6b6b; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { display: block; margin-bottom: 0.3rem; font-size: 0.8rem; color: rgba(255,255,255,0.6); }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 0.6rem 0.8rem; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
            border-radius: 8px; color: #fff; font-size: 0.85rem; font-family: inherit;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: #667eea; }
        .form-group input::placeholder, .form-group textarea::placeholder { color: rgba(255,255,255,0.3); }
        .form-group textarea { resize: vertical; min-height: 60px; }
        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; }
        .checkbox-group input[type="checkbox"] { width: auto; }

        .success-msg { background: rgba(72,199,142,0.15); border: 1px solid rgba(72,199,142,0.3); color: #48c78e; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.85rem; }
        .error-msg { background: rgba(255,77,77,0.15); border: 1px solid rgba(255,77,77,0.3); color: #ff6b6b; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.85rem; }
        .empty-state { text-align: center; padding: 3rem; color: rgba(255,255,255,0.4); }
        .empty-state i { font-size: 2.5rem; margin-bottom: 1rem; display: block; }

        .actions { display: flex; gap: 0.3rem; }
        .mobile-toggle { display: none; position: fixed; top: 1rem; left: 1rem; background: rgba(102,126,234,0.9); border: none; color: #fff; width: 40px; height: 40px; border-radius: 10px; font-size: 1.1rem; cursor: pointer; z-index: 200; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-toggle { display: flex; align-items: center; justify-content: center; }
            .form-grid { grid-template-columns: 1fr; }
        }

        /* Modal */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 300; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal { background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 2rem; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; }
        .modal h3 { margin-bottom: 1rem; }
        .modal-close { float: right; background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; }
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
            <li><a href="menu.php" class="active"><i class="fas fa-hamburger"></i> Menu Items</a></li>
            <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="../index.html" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-hamburger"></i> Menu Items</h1>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('show')">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>

        <?php if ($msg === 'added'): ?>
            <div class="success-msg"><i class="fas fa-check-circle"></i> Menu item added successfully.</div>
        <?php elseif ($msg === 'updated'): ?>
            <div class="success-msg"><i class="fas fa-check-circle"></i> Menu item updated successfully.</div>
        <?php elseif ($msg === 'deleted'): ?>
            <div class="success-msg"><i class="fas fa-check-circle"></i> Menu item deleted successfully.</div>
        <?php elseif ($msg === 'toggled'): ?>
            <div class="success-msg"><i class="fas fa-check-circle"></i> Availability toggled.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <?php if (empty($items)): ?>
                <div class="empty-state">
                    <i class="fas fa-utensils"></i>
                    <p>No menu items yet. Click "Add Item" to get started.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Prep Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <?php if ($item['description']): ?>
                                        <br><small style="color:rgba(255,255,255,0.4);"><?php echo htmlspecialchars(mb_strimwidth($item['description'], 0, 60, '...')); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['preparation_time']; ?> min</td>
                                <td>
                                    <a href="menu.php?toggle=<?php echo $item['id']; ?>" style="text-decoration:none;">
                                        <span class="badge <?php echo $item['is_available'] ? 'badge-available' : 'badge-unavailable'; ?>">
                                            <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                                        </span>
                                    </a>
                                </td>
                                <td>
                                    <div class="actions">
                                        <button class="btn btn-sm btn-primary" onclick='editItem(<?php echo json_encode($item); ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="menu.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add Item Modal -->
    <div class="modal-overlay" id="addModal">
        <div class="modal">
            <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('show')">&times;</button>
            <h3><i class="fas fa-plus"></i> Add Menu Item</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Margherita Pizza">
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <input type="text" name="category" required placeholder="e.g. Pizza" list="categories">
                        <datalist id="categories">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>Price ($) *</label>
                        <input type="number" name="price" step="0.01" min="0.01" required placeholder="12.99">
                    </div>
                    <div class="form-group">
                        <label>Prep Time (min)</label>
                        <input type="number" name="preparation_time" value="15" min="1">
                    </div>
                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea name="description" placeholder="Brief food description..."></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label>Ingredients</label>
                        <input type="text" name="ingredients" placeholder="Comma-separated: Dough, Cheese, Tomato Sauce">
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_available" id="add_available" checked>
                            <label for="add_available" style="margin:0;">Available</label>
                        </div>
                    </div>
                </div>
                <button type="submit" name="add_item" class="btn btn-primary" style="margin-top:0.5rem;">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <button class="modal-close" onclick="document.getElementById('editModal').classList.remove('show')">&times;</button>
            <h3><i class="fas fa-edit"></i> Edit Menu Item</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="item_id" id="edit_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <input type="text" name="category" id="edit_category" required list="categories">
                    </div>
                    <div class="form-group">
                        <label>Price ($) *</label>
                        <input type="number" name="price" id="edit_price" step="0.01" min="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Prep Time (min)</label>
                        <input type="number" name="preparation_time" id="edit_prep" min="1">
                    </div>
                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea name="description" id="edit_description"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label>Ingredients</label>
                        <input type="text" name="ingredients" id="edit_ingredients">
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_available" id="edit_available">
                            <label for="edit_available" style="margin:0;">Available</label>
                        </div>
                    </div>
                </div>
                <button type="submit" name="edit_item" class="btn btn-primary" style="margin-top:0.5rem;">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>

    <script>
    function editItem(item) {
        document.getElementById('edit_id').value = item.id;
        document.getElementById('edit_name').value = item.name;
        document.getElementById('edit_category').value = item.category;
        document.getElementById('edit_price').value = item.price;
        document.getElementById('edit_prep').value = item.preparation_time;
        document.getElementById('edit_description').value = item.description || '';
        document.getElementById('edit_ingredients').value = item.ingredients || '';
        document.getElementById('edit_available').checked = item.is_available == 1;
        document.getElementById('editModal').classList.add('show');
    }

    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('show');
        });
    });
    </script>
</body>
</html>
