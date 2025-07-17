<?php
require '../Connection_db/api.php';  // your PDO connection

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

try {
    $pdo->beginTransaction();

    foreach ($data as $id => $item) {
        $productId = (int)$id;
        $qty = (int)$item['qty'];
        $price = (float)$item['price'];
        if ($qty <= 0) continue;

        // Check current stock to avoid negative
        $stmtStock = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
        $stmtStock->execute([$productId]);
        $stock = (int)$stmtStock->fetchColumn();

        if ($qty > $stock) {
            throw new Exception("Quantity for product ID $productId exceeds stock");
        }

        // Insert sale
        $stmtSale = $pdo->prepare("
            INSERT INTO sales (product_id, quantity_sold, sale_date, price_at_sale) 
            VALUES (?, ?, NOW(), ?)
        ");
        $stmtSale->execute([$productId, $qty, $price]);

        // Update product quantity
        $stmtUpdate = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $stmtUpdate->execute([$qty, $productId]);
    }

    $pdo->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
