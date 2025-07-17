<?php
session_start();
require '../Connection_db/api.php';

// Fetch products ordered by quantity ascending
$products = $pdo->query("SELECT id, code, product_name, category_id, supplier_id, quantity, expiration_date, price FROM products ORDER BY quantity ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>POS System</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      margin: 0;
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
    }
    nav a {
      color: #58a6ff;
      text-decoration: none;
    }
    .sidebar, .main {
      padding: 90px 25px 20px;
      height: 100vh;
      overflow-y: auto;
    }
    .sidebar {
      width: 50%;
      background: #ffffff;
      border-right: 1px solid #ccc;
    }
    .main {
      width: 50%;
      background: #f1f5f9;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      background-color: white;
      border-radius: 8px;
      overflow: hidden;
    }
    thead {
      background: #1e293b;
      color: white;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    tr:hover {
      background-color: #e2e8f0;
    }
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
      font-size: 15px;
    }
    input[type="number"] {
      width: 60px;
      padding: 5px;
      font-size: 14px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    .btn-add, .btn-confirm, .btn-cancel, .btn-qty {
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
    }
    .btn-add {
      background-color: #3b82f6;
      color: white;
      padding: 6px 12px;
    }
    .btn-qty {
      background: #e5e7eb;
      color: #111827;
      padding: 4px 10px;
      font-weight: bold;
      border-radius: 20px;
    }
    .btn-confirm {
      background-color: #10b981;
      color: white;
      padding: 10px 16px;
    }
    .btn-cancel {
      background-color: #ef4444;
      color: white;
      padding: 10px 16px;
      margin-left: 10px;
    }
    input[type="search"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 14px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      font-size: 15px;
    }
    .low-stock {
      color: white;
      background: #dc2626;
      padding: 2px 8px;
      border-radius: 4px;
      display: inline-block;
      font-size: 13px;
    }
    .med-stock {
      color: white;
      background: #f59e0b;
      padding: 2px 8px;
      border-radius: 4px;
      display: inline-block;
      font-size: 13px;
    }
    .high-stock {
      color: white;
      background: #22c55e;
      padding: 2px 8px;
      border-radius: 4px;
      display: inline-block;
      font-size: 13px;
    }
    td button:hover {
      opacity: 0.85;
    }
  </style>
</head>
<body>

<nav>
  <div>🛒 POS SYSTEM</div>
  <a href="logout.php" style="color: white; text-decoration: none;">Logout</a>
</nav>

<div class="sidebar">
  <h2>Product List</h2>
  <input type="search" id="searchBar" placeholder="Search Product...">
  <table id="productTable">
    <thead>
      <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Quantity</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $products->fetch(PDO::FETCH_ASSOC)): 
        $stock_class = ($row['quantity'] <= 0) ? 'low-stock' : (($row['quantity'] < 20) ? 'med-stock' : 'high-stock');
        $stock_msg = ($row['quantity'] < 20) ? "⚠ Order from Supplier" : "✅ OK";
      ?>
        <tr data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['product_name'], ENT_QUOTES) ?>" data-price="<?= $row['price'] ?>" data-stock="<?= $row['quantity'] ?>">
          <td><?= htmlspecialchars($row['product_name']) ?></td>
          <td>₱<?= number_format($row['price'], 2) ?></td>
          <td class="<?= $stock_class ?>"><?= $row['quantity'] ?> <span style="font-size: 12px; margin-left: 5px;"><?= $stock_msg ?></span></td>
          <td>
            <input type="number" id="qty-<?= $row['id'] ?>" min="0" max="<?= $row['quantity'] ?>" value="0" />
          </td>
          <td>
            <button class="btn-add" onclick="addItemWithQty(<?= $row['id'] ?>, '<?= htmlspecialchars($row['product_name'], ENT_QUOTES) ?>', <?= $row['price'] ?>)">Add</button>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<div class="main">
  <h2>Receipt / Subtotal</h2>
  <table id="cartTable">
    <thead>
      <tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th>Action</th></tr>
    </thead>
    <tbody></tbody>
    <tfoot>
      <tr><td colspan="3"><strong>Subtotal</strong></td><td colspan="2" id="subtotal">₱0.00</td></tr>
    </tfoot>
  </table>
  <button class="btn-confirm" onclick="confirmSale()">Confirm Sale</button>
  <button class="btn-cancel" onclick="clearCart()">Cancel</button>
</div>

<script>
  const cart = {};
  const cartTableBody = document.querySelector("#cartTable tbody");
  const subtotalCell = document.getElementById("subtotal");

  function changeInputQty(inputId, delta) {
    const input = document.getElementById(inputId);
    let val = parseInt(input.value) || 0;
    val += delta;
    if (val < 0) val = 0;
    if (val > parseInt(input.max)) {
      alert("Quantity cannot exceed stock!");
      return;
    }
    input.value = val;
  }

  function addItemWithQty(id, name, price) {
    const qtyInput = document.getElementById('qty-' + id);
    let qty = parseInt(qtyInput.value);
    if (isNaN(qty) || qty <= 0) {
      alert('Please input quantity greater than 0');
      return;
    }
    const maxStock = parseInt(qtyInput.max);
    if (qty > maxStock) {
      alert('Quantity exceeds available stock!');
      return;
    }
    if (!cart[id]) {
      cart[id] = { id, name, price, qty };
    } else {
      if (cart[id].qty + qty > maxStock) {
        alert('Total quantity exceeds available stock!');
        return;
      }
      cart[id].qty += qty;
    }
    qtyInput.value = 0;
    renderCart();
  }

  function changeQty(id, delta) {
    if (cart[id]) {
      cart[id].qty += delta;
      if (cart[id].qty <= 0) {
        delete cart[id];
      } else {
        // Check stock
        const row = document.querySelector(`tr[data-id='${id}']`);
        const stock = parseInt(row.getAttribute('data-stock'));
        if (cart[id].qty > stock) {
          alert('Quantity exceeds available stock!');
          cart[id].qty -= delta;
          return;
        }
      }
      renderCart();
    }
  }

  function renderCart() {
    cartTableBody.innerHTML = '';
    let subtotal = 0;

    for (let id in cart) {
      const item = cart[id];
      const total = item.price * item.qty;
      subtotal += total;

      const row = `
        <tr>
          <td>${item.name}</td>
          <td>
            <button class="btn-qty" onclick="changeQty(${id}, -1)">−</button>
            ${item.qty}
            <button class="btn-qty" onclick="changeQty(${id}, 1)">+</button>
          </td>
          <td>₱${item.price.toFixed(2)}</td>
          <td>₱${total.toFixed(2)}</td>
          <td><button onclick="changeQty(${id}, -item.qty)">🗑</button></td>
        </tr>
      `;
      cartTableBody.insertAdjacentHTML('beforeend', row);
    }
    subtotalCell.textContent = '₱' + subtotal.toFixed(2);
  }

  function clearCart() {
    if (confirm("Cancel this transaction?")) {
      for (let id in cart) delete cart[id];
      renderCart();
    }
  }

  function showReceipt(data) {
    const now = new Date();
    const dateStr = now.toLocaleDateString();
    const timeStr = now.toLocaleTimeString();

    let receiptHTML = `
      <div style="font-family: monospace; max-width: 350px; margin: auto; padding: 20px; border: 2px solid black; border-radius: 8px; background: white;">
        <h2 style="text-align:center; margin-bottom: 10px;">####### RECEIPT #######</h2>
        <p style="text-align:center; margin: 0;">Date: ${dateStr}</p>
        <p style="text-align:center; margin: 0 0 15px;">Time: ${timeStr}</p>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 14px;">
          <tbody>
    `;

    let subtotal = 0;
    for (let id in data) {
      const item = data[id];
      const lineTotal = item.price * item.qty;
      subtotal += lineTotal;
      receiptHTML += `
        <tr>
          <td>${item.name}</td>
          <td style="text-align:center;">${item.qty}</td>
          <td style="text-align:right;">₱${item.price.toFixed(2)}</td>
          <td style="text-align:right;">₱${lineTotal.toFixed(2)}</td>
        </tr>
        <tr><td colspan="4" style="border-bottom: 1px dotted #000;"></td></tr>
      `;
    }

    const taxRate = 0.12;
    const taxAmount = subtotal * taxRate;
    const total = subtotal + taxAmount;

    receiptHTML += `
          </tbody>
        </table>

        <p style="border-top: 1px dotted #000; padding-top: 8px; margin: 0; font-weight: bold;">Subtotal: ₱${subtotal.toFixed(2)}</p>
        <p style="margin: 0; font-weight: bold;">Tax (12%): ₱${taxAmount.toFixed(2)}</p>
        <p style="margin: 0; font-size: 18px; font-weight: bold;">Total: ₱${total.toFixed(2)}</p>

        <p style="text-align:center; margin-top: 20px; font-size: 12px;">Thank you for your purchase!</p>
      </div>
    `;

    const receiptWindow = window.open('', '_blank', 'width=400,height=600');
    receiptWindow.document.write('<html><head><title>Receipt</title></head><body>');
    receiptWindow.document.write(receiptHTML);
    receiptWindow.document.write('</body></html>');
    receiptWindow.document.close();
  }

  function confirmSale() {
    if (Object.keys(cart).length === 0) {
      alert("No items in cart!");
      return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "save_sale.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(JSON.stringify(cart));

    xhr.onload = () => {
      if (xhr.status === 200) {
        alert("Sale Saved!");
        showReceipt(cart);
        location.reload();
      } else {
        alert("Error saving sale.");
      }
    };
  }

  // Search filter
  document.getElementById("searchBar").addEventListener("input", function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll("#productTable tbody tr");
    rows.forEach(row => {
      const product = row.children[0].textContent.toLowerCase();
      row.style.display = product.includes(filter) ? "" : "none";
    });
  });
</script>

</body>
</html>
