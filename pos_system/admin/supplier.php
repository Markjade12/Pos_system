<?php
require '../Connection_db/api.php';

// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name']);
        $contact = trim($_POST['contact']);
        $address = trim($_POST['address']);

        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact, address) VALUES (?, ?, ?)");
        $stmt->execute([$name, $contact, $address]);
        header("Location: supplier.php");
        exit;
    }

    if ($action === 'update') {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $contact = trim($_POST['contact']);
        $address = trim($_POST['address']);

        $stmt = $pdo->prepare("UPDATE suppliers SET name=?, contact=?, address=? WHERE supplier_id=?");
        $stmt->execute([$name, $contact, $address, $id]);
        header("Location: supplier.php");
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE supplier_id=?");
        $stmt->execute([$id]);
        header("Location: supplier.php");
        exit;
    }
}

// Fetch all suppliers
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// If editing, get supplier data
$editSupplier = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id=?");
    $stmt->execute([$_GET['edit']]);
    $editSupplier = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Suppliers Management</title>

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

    @media (max-width: 768px) {
      .sidebar {
        width: 0;
        overflow: hidden;
      }

      .content {
        margin-left: 0;
      }
    }

    .card {
      border-radius: 10px;
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
  <a href="index.php"><i class="fas fa-boxes me-2"></i>Inventory</a>
  <a href="supplier.php" class="active"><i class="fas fa-truck me-2"></i>Suppliers</a>
  <a href="user_management.php"><i class="fas fa-users me-2"></i>Users</a>
</div>
</div>

<!-- Main Content -->
<div class="content">
  <div class="container-fluid">
    <h2 class="mb-4">Suppliers Management</h2>

    <!-- Add Supplier Button -->
    <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
      <i class="fas fa-plus"></i> Add Supplier
    </button>

    <!-- Suppliers Table -->
    <div class="card">
      <div class="card-header">Suppliers List</div>
      <div class="table-responsive">
        <table class="table table-striped table-bordered mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Contact</th>
              <th>Address</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($suppliers) === 0): ?>
              <tr><td colspan="5" class="text-center">No suppliers found.</td></tr>
            <?php else: ?>
              <?php foreach ($suppliers as $s): ?>
                <tr>
                  <td><?= $s['supplier_id'] ?></td>
                  <td><?= htmlspecialchars($s['name']) ?></td>
                  <td><?= htmlspecialchars($s['contact']) ?></td>
                  <td><?= nl2br(htmlspecialchars($s['address'])) ?></td>
                  <td>
                    <a href="?edit=<?= $s['supplier_id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this supplier?');">
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="id" value="<?= $s['supplier_id'] ?>" />
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

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="action" value="create" />
        <div class="modal-header">
          <h5 class="modal-title" id="addSupplierModalLabel">Add New Supplier</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="name" class="form-label">Supplier Name</label>
            <input type="text" class="form-control" id="name" name="name" required />
          </div>
          <div class="mb-3">
            <label for="contact" class="form-label">Contact Info</label>
            <input type="text" class="form-control" id="contact" name="contact" />
          </div>
          <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Add Supplier</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Supplier Modal -->
<?php if ($editSupplier): ?>
  <div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form method="post" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="update" />
          <input type="hidden" name="id" value="<?= $editSupplier['supplier_id'] ?>" />
          <div class="mb-3">
            <label for="nameEdit" class="form-label">Supplier Name</label>
            <input type="text" class="form-control" id="nameEdit" name="name" required value="<?= htmlspecialchars($editSupplier['name']) ?>">
          </div>
          <div class="mb-3">
            <label for="contactEdit" class="form-label">Contact Info</label>
            <input type="text" class="form-control" id="contactEdit" name="contact" value="<?= htmlspecialchars($editSupplier['contact']) ?>">
          </div>
          <div class="mb-3">
            <label for="addressEdit" class="form-label">Address</label>
            <textarea class="form-control" id="addressEdit" name="address" rows="2"><?= htmlspecialchars($editSupplier['address']) ?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update</button>
          <a href="supplier.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Auto-show modal on page load -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var editModal = new bootstrap.Modal(document.getElementById('editSupplierModal'));
      editModal.show();
    });
  </script>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Optional utility JS -->
<script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const content = document.querySelector('.content');
    if (sidebar.style.width === '250px' || sidebar.style.width === '') {
      sidebar.style.width = '0';
      content.style.marginLeft = '0';
    } else {
      sidebar.style.width = '250px';
      content.style.marginLeft = '250px';
    }
  }

  function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleTimeString();
  }

  setInterval(updateClock, 1000);
  updateClock();
</script>
