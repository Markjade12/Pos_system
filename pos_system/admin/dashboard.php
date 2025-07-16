<?php
require '../Connection_db/api.php';

// Sample KPIs - replace these with actual queries
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalSuppliers = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity < 10")->fetchColumn();

// Data for charts

// Products count by category
$productsByCategory = $pdo->query("
    SELECT c.name, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON c.category_id = p.category_id
    GROUP BY c.category_id
")->fetchAll(PDO::FETCH_ASSOC);

// Stock quantity by category
$stockByCategory = $pdo->query("
    SELECT c.name, COALESCE(SUM(p.quantity),0) AS total_stock
    FROM categories c
    LEFT JOIN products p ON c.category_id = p.category_id
    GROUP BY c.category_id
")->fetchAll(PDO::FETCH_ASSOC);

// Product count by supplier
$productsBySupplier = $pdo->query("
    SELECT s.name, COUNT(p.id) AS product_count
    FROM suppliers s
    LEFT JOIN products p ON s.supplier_id = p.supplier_id
    GROUP BY s.supplier_id
")->fetchAll(PDO::FETCH_ASSOC);

// Low stock products count by category
$lowStockByCategory = $pdo->query("
    SELECT c.name, COUNT(p.id) AS low_stock_count
    FROM categories c
    LEFT JOIN products p ON c.category_id = p.id AND p.quantity < 10
    GROUP BY c.category_id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

    .card-kpi {
      color: white;
      border: none;
      border-radius: 10px;
      padding: 20px;

      /* Set fixed height */
      height: 180px;

      /* Flexbox to center content vertically */
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      gap: 10px;
    }

    .card-products { background: #3498db; }
    .card-categories { background: #1abc9c; }
    .card-suppliers { background: #9b59b6; }
    .card-lowstock { background: #e74c3c; }

    .card-kpi h3 {
      font-size: 2.8rem;
      margin: 0;
      font-weight: 700;
    }

    .card-kpi p {
      margin: 0;
      font-size: 1rem;
      opacity: 0.9;
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
  <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
  <a href="index.php"><i class="fas fa-boxes me-2"></i>Inventory</a>
  
  <a href="supplier.php"><i class="fas fa-truck me-2"></i>Suppliers</a>
  <a href="user_management.php"><i class="fas fa-users me-2"></i>Users</a>
</div>

<!-- Main Content -->
<div class="content">
  <div class="container-fluid">
    <h2 class="mb-4">Welcome, Admin!</h2>

    <!-- KPI Cards -->
    <div class="row g-3">
      <div class="col-md-6 col-lg-3">
        <div class="card card-kpi card-products">
          <div class="card-body">
            <h5 class="card-title"><i class="fas fa-box"></i> Products</h5>
            <h3><?= $totalProducts ?></h3>
            <p>Total products in inventory</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card card-kpi card-categories">
          <div class="card-body">
            <h5 class="card-title"><i class="fas fa-tags"></i> Categories</h5>
            <h3><?= $totalCategories ?></h3>
            <p>Total product categories</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card card-kpi card-suppliers">
          <div class="card-body">
            <h5 class="card-title"><i class="fas fa-truck"></i> Suppliers</h5>
            <h3><?= $totalSuppliers ?></h3>
            <p>Total suppliers</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card card-kpi card-lowstock">
          <div class="card-body">
            <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Low Stock</h5>
            <h3><?= $lowStock ?></h3>
            <p>Items below stock threshold</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
  <!-- Charts Row 1: Two Bar Charts -->
<div class="row mt-4 g-4">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h5>Products by Category</h5>
      </div>
      <div class="card-body">
        <canvas id="chartProductsByCategory" height="200"></canvas>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h5>Stock Levels</h5>
      </div>
      <div class="card-body">
        <canvas id="chartStockLevels" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row 2: Two Other Charts -->
<div class="row mt-4 g-4">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h5>Supplier Distribution</h5>
      </div>
      <div class="card-body">
        <canvas id="chartSuppliers" height="200"></canvas>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h5>Low Stock Products</h5>
      </div>
      <div class="card-body">
        <canvas id="chartLowStock" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const content = document.querySelector('.content');
    sidebar.style.width = sidebar.style.width === '250px' || sidebar.style.width === '' ? '0' : '250px';
    content.style.marginLeft = sidebar.style.width === '0' ? '0' : '250px';
  }

  function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleTimeString();
  }
  setInterval(updateClock, 1000);
  updateClock();
</script>

<script>
  // Helper function to parse PHP arrays to chart data
  function extractData(arr, labelKey, dataKey) {
    return {
      labels: arr.map(item => item[labelKey]),
      data: arr.map(item => Number(item[dataKey]))
    };
  }

  const productsByCategoryData = extractData(<?= json_encode($productsByCategory) ?>, 'name', 'product_count');
  const stockByCategoryData = extractData(<?= json_encode($stockByCategory) ?>, 'name', 'total_stock');
  const productsBySupplierData = extractData(<?= json_encode($productsBySupplier) ?>, 'name', 'product_count');
  const lowStockByCategoryData = extractData(<?= json_encode($lowStockByCategory) ?>, 'name', 'low_stock_count');

  // Products by Category - Bar chart
  new Chart(document.getElementById('chartProductsByCategory'), {
    type: 'bar',
    data: {
      labels: productsByCategoryData.labels,
      datasets: [{
        label: 'Number of Products',
        data: productsByCategoryData.data,
        backgroundColor: '#3498db',
        borderRadius: 5,
      }]
    },
    options: {
      responsive: true,
      scales: { y: { beginAtZero: true } },
      plugins: { legend: { display: false } }
    }
  });

  // Stock Levels - Bar chart
  new Chart(document.getElementById('chartStockLevels'), {
    type: 'bar',
    data: {
      labels: stockByCategoryData.labels,
      datasets: [{
        label: 'Total Stock',
        data: stockByCategoryData.data,
        backgroundColor: '#1abc9c',
        borderRadius: 5,
      }]
    },
    options: {
      responsive: true,
      scales: { y: { beginAtZero: true } },
      plugins: { legend: { display: false } }
    }
  });

  // Supplier Distribution - Pie chart
  new Chart(document.getElementById('chartSuppliers'), {
    type: 'pie',
    data: {
      labels: productsBySupplierData.labels,
      datasets: [{
        label: 'Products by Supplier',
        data: productsBySupplierData.data,
        backgroundColor: [
          '#9b59b6', '#e67e22', '#f1c40f', '#e74c3c', '#34495e', '#2ecc71', '#1abc9c'
        ]
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });

  // Low Stock Products by Category - Doughnut chart
  new Chart(document.getElementById('chartLowStock'), {
    type: 'doughnut',
    data: {
      labels: lowStockByCategoryData.labels,
      datasets: [{
        label: 'Low Stock Count',
        data: lowStockByCategoryData.data,
        backgroundColor: [
          '#e74c3c', '#c0392b', '#e67e22', '#d35400', '#f39c12'
        ]
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });
</script>

</body>
</html>
