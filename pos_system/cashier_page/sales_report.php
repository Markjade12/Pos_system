<?php
session_start();
require '../Connection_db/api.php'; // adjust path if needed

// Handle date filter from form
$fromDate = $_GET['from_date'] ?? null;
$toDate = $_GET['to_date'] ?? null;

$params = [];
$whereClause = "";

if ($fromDate && $toDate) {
    $whereClause = "WHERE DATE(s.sale_date) BETWEEN :from AND :to";
    $params = [
        ':from' => $fromDate,
        ':to' => $toDate,
    ];
}

// Query sales joined with product names, filtered by date if set
$sql = "
SELECT s.sale_id, p.product_name, s.quantity_sold, s.sale_date, s.price_at_sale
FROM sales s
JOIN products p ON s.product_id = p.id
" . $whereClause . "
ORDER BY s.sale_date ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate profit summaries
$profitSql = "
SELECT 
  SUM(quantity_sold * price_at_sale) AS total_profit,
  DATE(s.sale_date) AS sale_day,
  WEEK(s.sale_date, 1) AS sale_week,
  DATE_FORMAT(s.sale_date, '%Y-%m') AS sale_month
FROM sales s
" . $whereClause . "
GROUP BY sale_day, sale_week, sale_month
";


$profitStmt = $pdo->prepare($profitSql);
$profitStmt->execute($params);
$profits = $profitStmt->fetchAll(PDO::FETCH_ASSOC);

$now = new DateTime();
$dailyProfit = 0;
$weeklyProfit = 0;
$monthlyProfit = 0;
$grandTotal = 0;

foreach ($profits as $p) {
    $saleDay = new DateTime($p['sale_day']);
    $saleWeek = (int)$p['sale_week'];
    $saleMonth = $p['sale_month'];

    // Daily profit (today)
    if ($saleDay->format('Y-m-d') === $now->format('Y-m-d')) {
        $dailyProfit += $p['total_profit'];
    }

    // Weekly profit (current week of year)
    $currentWeek = (int)$now->format('W');
    if ($saleWeek === $currentWeek) {
        $weeklyProfit += $p['total_profit'];
    }

    // Monthly profit (current year-month)
    if ($saleMonth === $now->format('Y-m')) {
        $monthlyProfit += $p['total_profit'];
    }
}

// Calculate grand total for all displayed sales
foreach ($sales as $row) {
    $lineTotal = $row['quantity_sold'] * $row['price_at_sale'];
    $grandTotal += $lineTotal;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sales Report - POS System</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      display: flex;
      background: #f9fafb;
    }
    nav {
      background: #0d1117;
      color: white;
      padding: 12px 20px;
      width: 100%;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 10;
      display: flex;
      justify-content: space-between;
      font-size: 18px;
      box-sizing: border-box;
    }
    .nav-left {
      white-space: nowrap;
    }
    .nav-right a {
      color: #58a6ff;
      text-decoration: none;
      font-weight: 500;
    }
    .nav-right a:hover {
      text-decoration: underline;
    }
    .sidebar {
      width: 220px;
      background-color: #2c3e50;
      color: white;
      height: 100vh;
      margin-top: 48px; /* below nav */
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 48px;
      left: 0;
      overflow-y: auto;
    }
    .sidebar h2 {
      text-align: center;
      padding: 20px 0;
      margin: 0;
      background-color: #1abc9c;
    }
     .sidebar {
      width: 220px;
      background-color: #2c3e50;
      color: white;
      height: 100vh;
      padding-top: 60px; /* Space for nav */
      position: fixed;
      top: 0;
      left: 0;
    }

    .sidebar h2 {
      text-align: center;
      padding: 20px 0;
      background-color: #1abc9c;
    }

    .sidebar a {
      display: block;
      padding: 15px 20px;
      text-decoration: none;
      color: white;
      border-bottom: 1px solid #34495e;
    }

    .sidebar a:hover {
      background-color: #16a085;
    }

    
    .content {
      margin-left: 220px;
      padding: 70px 30px 30px;
      width: calc(100% - 220px);
      box-sizing: border-box;
    }
    h1 {
      margin-bottom: 20px;
      font-weight: 700;
    }
    form.filter-form {
      margin-bottom: 25px;
      display: flex;
      gap: 10px;
      align-items: center;
    }
    form.filter-form label {
      font-weight: 600;
      color: #333;
    }
    form.filter-form input[type="date"] {
      padding: 6px 8px;
      font-size: 14px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    form.filter-form button {
      padding: 7px 14px;
      font-weight: 700;
      border: none;
      background-color: #16a085;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    form.filter-form button:hover {
      background-color: #13856e;
    }
    .summary-boxes {
      display: flex;
      gap: 20px;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }
    .summary-box {
      background: white;
      padding: 20px;
      flex: 1 1 180px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
      text-align: center;
    }
    .summary-box h3 {
      margin: 0 0 10px;
      font-weight: 700;
      font-size: 1.2rem;
      color: #1e293b;
    }
    .summary-box p {
      font-size: 1.8rem;
      font-weight: 800;
      color: #16a085;
      margin: 0;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background-color: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgb(0 0 0 / 0.1);
    }
    thead {
      background: #1e293b;
      color: white;
    }
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
      font-size: 15px;
    }
    tr:hover {
      background-color: #f1f5f9;
    }
    tfoot td {
      font-weight: 700;
      background: #f9fafb;
    }
  </style>
</head>
<body>

<nav>
  <div class="nav-left">🛒 POS SYSTEM</div>
  <div class="nav-right">
    <a href="logout.php">Logout</a>
  </div>
</nav>

 <div class="sidebar">
     <a href="cashier_page.php" ><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
    <a href="sales_report.php" class="active">Sales</a>
  
    <a href="#">Logout</a>
  </div>


<div class="content">
  <h1>Sales Report</h1>

  <form method="GET" class="filter-form">
    <label for="from_date">From:</label>
    <input type="date" id="from_date" name="from_date" value="<?= htmlspecialchars($fromDate ?? '') ?>" required />
    <label for="to_date">To:</label>
    <input type="date" id="to_date" name="to_date" value="<?= htmlspecialchars($toDate ?? '') ?>" required />
    <button type="submit">Filter</button>
  </form>

  <div class="summary-boxes">
    <div class="summary-box">
      <h3>Today's Profit</h3>
      <p>₱<?= number_format($dailyProfit, 2) ?></p>
    </div>
    <div class="summary-box">
      <h3>This Week's Profit</h3>
      <p>₱<?= number_format($weeklyProfit, 2) ?></p>
    </div>
    <div class="summary-box">
      <h3>This Month's Profit</h3>
      <p>₱<?= number_format($monthlyProfit, 2) ?></p>
    </div>
    <div class="summary-box">
      <h3>Grand Total Sales</h3>
      <p>₱<?= number_format($grandTotal, 2) ?></p>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Sale ID</th>
        <th>Product</th>
        <th>Quantity Sold</th>
        <th>Price at Sale</th>
        <th>Total</th>
        <th>Sale Date & Time</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($sales) > 0): ?>
        <?php foreach ($sales as $row):
          $lineTotal = $row['quantity_sold'] * $row['price_at_sale'];
        ?>
          <tr>
            <td><?= htmlspecialchars($row['sale_id']) ?></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= htmlspecialchars($row['quantity_sold']) ?></td>
            <td>₱<?= number_format($row['price_at_sale'], 2) ?></td>
            <td>₱<?= number_format($lineTotal, 2) ?></td>
            <td><?= htmlspecialchars($row['sale_date']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center;">No sales found for selected date range.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>
