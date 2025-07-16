<?php
require '../Connection_db/api.php';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role]);
        header("Location: user_management.php");
        exit;
    }

    if ($action === 'update') {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $role = $_POST['role'];

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, role=? WHERE id=?");
            $stmt->execute([$username, $password, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, role=? WHERE id=?");
            $stmt->execute([$username, $role, $id]);
        }

        header("Location: user_management.php");
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$id]);
        header("Location: user_management.php");
        exit;
    }
}

// Get users
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6fa; }
    .navbar { position: fixed; width: 100%; top: 0; background: #1e2a38; color: white; z-index: 1000; }
    .sidebar {
        position: fixed; top: 56px; left: 0; width: 250px; height: 100vh;
        background-color: #2c3e50; padding-top: 20px; z-index: 999;
    }
    .sidebar a {
        color: #d1d8e0; padding: 15px 20px; display: block; text-decoration: none;
    }
    .sidebar a:hover, .sidebar a.active {
        background-color: #1abc9c; color: white; border-left: 5px solid white;
    }
    .content { margin-left: 250px; padding: 80px 20px 20px; }
    .modal-content.bg-white {
        background-color: #ffffff !important;
        color: #000 !important;
    }
    @media (max-width: 768px) {
      .sidebar { width: 0; overflow: hidden; }
      .content { margin-left: 0; }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark px-3">
  <div class="container-fluid">
    <button class="btn btn-outline-light d-md-none" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    <a class="navbar-brand" href="#"><i class="fas fa-users"></i> User Management</a>
    <div class="text-white d-none d-md-block"><i class="fas fa-user-shield"></i> Admin</div>
  </div>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
  <a href="index.php"><i class="fas fa-boxes me-2"></i>Inventory</a>
  <a href="supplier.php"><i class="fas fa-truck me-2"></i>Suppliers</a>
  <a href="#" class="active"><i class="fas fa-users me-2"></i>Users</a>
</div>

<!-- Main Content -->
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Users List</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
      <i class="fas fa-user-plus"></i> Add User
    </button>
  </div>

 <div class="table-responsive">
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($users)): ?>
        <tr><td colspan="4" class="text-center">No users found.</td></tr>
      <?php else: ?>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $user['id'] ?>">
                <i class="fas fa-edit"></i>
              </button>
              <form method="post" class="d-inline" onsubmit="return confirm('Delete this user?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Place modals outside the table -->
<?php if (!empty($users)): ?>
  <?php foreach ($users as $user): ?>
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal<?= $user['id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <form method="post" class="modal-content bg-white p-3">
          <div class="modal-header">
            <h5 class="modal-title">Edit User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">

            <div class="mb-3">
              <label>Username</label>
              <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <div class="mb-3">
              <label>New Password (leave blank to keep current)</label>
              <input type="password" name="password" class="form-control">
            </div>

            <div class="mb-3">
              <label>Role</label>
              <select name="role" class="form-select" required>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>Staff</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success">Update</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content bg-white p-3 rounded shadow-sm">
      <div class="modal-header">
        <h5 class="modal-title">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="create">

        <div class="mb-3">
          <label>Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Role</label>
          <select name="role" class="form-select" required>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Add User</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const content = document.querySelector('.content');
    sidebar.style.width = sidebar.style.width === '250px' ? '0' : '250px';
    content.style.marginLeft = sidebar.style.width === '0' ? '0' : '250px';
  }
</script>

</body>
</html>
