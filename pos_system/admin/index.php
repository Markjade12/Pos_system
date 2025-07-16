<?php
require '../Connection_db/api.php';

// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $code = $_POST['code'];
        $name = $_POST['product_name'];
        $category_id = $_POST['category_id'];
        $supplier_id = $_POST['supplier_id'];
        $quantity = (int)$_POST['quantity'];
        $expiration_date = $_POST['expiration_date'] ?: null;
        $price = $_POST['price'];

        $stmt = $pdo->prepare("INSERT INTO products (code, product_name, category_id, supplier_id, quantity, expiration_date, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $name, $category_id, $supplier_id, $quantity, $expiration_date, $price]);
        header("Location: index.php");
        exit;
    }

    if ($action === 'update') {
        $id = $_POST['id'];
        $code = $_POST['code'];
        $name = $_POST['product_name'];
        $category_id = $_POST['category_id'];
        $supplier_id = $_POST['supplier_id'];
        $quantity = (int)$_POST['quantity'];
        $expiration_date = $_POST['expiration_date'] ?: null;
        $price = $_POST['price'];

        $stmt = $pdo->prepare("UPDATE products SET code=?, product_name=?, category_id=?, supplier_id=?, quantity=?, expiration_date=?, price=? WHERE id=?");
        $stmt->execute([$code, $name, $category_id, $supplier_id, $quantity, $expiration_date, $price, $id]);
        header("Location: index.php");
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
        $stmt->execute([$id]);
        header("Location: index.php");
        exit;
    }
}

// Fetch products with JOINs for supplier & category
$products = $pdo->query("
   SELECT p.*, c.name AS category_name, s.name AS supplier_name
FROM products p
JOIN categories c ON p.category_id = c.category_id
JOIN suppliers s ON p.supplier_id = s.supplier_id;

")->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories & suppliers for dropdowns
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// If editing, get product data
$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Inventory Management</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f6fa;
      margin: 0;
      padding: 0;
    }

    .navbar {
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 1000;
      background: #1e2a38;
      color: #fff;
    }

    .navbar-brand,
    .navbar .text-white {
      color: #fff !important;
    }

    .sidebar {
      height: 100vh;
      width: 250px;
      position: fixed;
      top: 56px;
      left: 0;
      background-color: #2c3e50;
      padding-top: 20px;
      transition: all 0.3s;
      z-index: 999;
    }

    .sidebar a {
      color: #d1d8e0;
      padding: 15px 20px;
      display: block;
      text-decoration: none;
      font-weight: 500;
      transition: 0.3s;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: #1abc9c;
      color: white;
      border-left: 5px solid #ffffff;
    }

    .content {
      margin-left: 250px;
      padding: 80px 20px 20px 20px;
      transition: all 0.3s;
    }

    .table-responsive {
      max-height: 400px;
      overflow-y: auto;
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 0;
        overflow: hidden;
      }

      .content {
        margin-left: 0;
      }
    }
  </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-dark px-3">
  <div class="container-fluid">
    <button class="btn btn-outline-light d-md-none" onclick="toggleSidebar()">
      <i class="fas fa-bars"></i>
    </button>
    <a class="navbar-brand" href="#"><i class="fas fa-store"></i> POS Dashboard</a>
    <div class="text-white d-none d-md-block">
      <span class="me-3"><i class="fas fa-user"></i> Admin</span>
      <span><i class="fas fa-clock"></i> <span id="clock"></span></span>
    </div>
  </div>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar" id="sidebar">
  <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
  <a href="index.php" class="active"><i class="fas fa-boxes me-2"></i>Inventory</a>
  <a href="supplier.php"><i class="fas fa-truck me-2"></i>Suppliers</a>
  <a href="user_management.php"><i class="fas fa-users me-2"></i>Users</a>
</div> 
</div>

<div class="content">
  <div class="container-fluid">
    <h2 class="mb-4">Products List</h2>

    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addProductModal">
      <i class="fas fa-plus"></i> Add Product
    </button>

    <!-- Products Table -->
    <div class="card">
      <div class="card-header">Products List</div>
      <div class="table-responsive">
        <table class="table table-striped table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Code</th>
              <th>Name</th>
              <th>Category</th>
              <th>Supplier</th>
              <th>Qty</th>
              <th>Expiration</th>
              <th>Price (₱)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($products) === 0): ?>
              <tr><td colspan="9" class="text-center">No products found.</td></tr>
            <?php else: ?>
              <?php foreach ($products as $p): ?>
                <tr>
                  <td><?= $p['id'] ?></td>
                  <td><?= htmlspecialchars($p['code']) ?></td>
                  <td><?= htmlspecialchars($p['product_name']) ?></td>
                  <td><?= htmlspecialchars($p['category_name']) ?></td>
                  <td><?= htmlspecialchars($p['supplier_name']) ?></td>
                  <td><?= $p['quantity'] ?></td>
                  <td><?= $p['expiration_date'] ?: '-' ?></td>
                  <td><?= number_format($p['price'], 2) ?></td>
                  <td>
                    <a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this product?');">
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                      <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="post" class="modal-content">
      <input type="hidden" name="action" value="create">
      <div class="modal-header">
        <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label class="form-label">Code</label>
          <input type="text" name="code" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Product Name</label>
          <input type="text" name="product_name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-select" required>
            <option value="" disabled selected>Select Category</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Supplier</label>
          <select name="supplier_id" class="form-select" required>
            <option value="" disabled selected>Select Supplier</option>
            <?php foreach ($suppliers as $sup): ?>
              <option value="<?= $sup['supplier_id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Quantity</label>
          <input type="number" name="quantity" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Expiration Date</label>
          <input type="date" name="expiration_date" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Price (₱)</label>
          <input type="number" step="0.01" name="price" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Add Product</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Product Modal -->
<?php if ($editProduct): ?>
  <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form method="post" class="modal-content">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <div class="col-md-6">
            <label class="form-label">Code</label>
            <input type="text" name="code" class="form-control" required value="<?= htmlspecialchars($editProduct['code']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Product Name</label>
            <input type="text" name="product_name" class="form-control" required value="<?= htmlspecialchars($editProduct['product_name']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>" <?= $editProduct['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Supplier</label>
            <select name="supplier_id" class="form-select" required>
              <?php foreach ($suppliers as $sup): ?>
                <option value="<?= $sup['supplier_id'] ?>" <?= $editProduct['supplier_id'] == $sup['supplier_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($sup['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" required value="<?= $editProduct['quantity'] ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Expiration Date</label>
            <input type="date" name="expiration_date" class="form-control" value="<?= $editProduct['expiration_date'] ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Price (₱)</label>
            <input type="number" step="0.01" name="price" class="form-control" required value="<?= $editProduct['price'] ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success">Update Product</button>
          <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
      editModal.show();
    });
  </script>
<?php endif; ?>

<!-- Bootstrap JS (required for modals) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- JS Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.style.width = sidebar.style.width === '250px' || sidebar.style.width === '' ? '0' : '250px';
    document.querySelector('.content').style.marginLeft = sidebar.style.width === '0' ? '0' : '250px';
  }

  function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleTimeString();
  }
  setInterval(updateClock, 1000);
  updateClock();
</script>
</body>
</html>
