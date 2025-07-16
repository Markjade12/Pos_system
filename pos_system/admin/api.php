<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$host = "localhost";
$dbname = "root";
$user = "";
$pass = "pos_act";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(["error" => "Database connection failed"]);
  exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

if (empty($request) || $request[0] !== 'products') {
  http_response_code(404);
  echo json_encode(["error" => "Invalid endpoint"]);
  exit();
}

$id = $request[1] ?? null;

switch ($method) {
  case 'GET':
    if ($id) {
      $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $result = $stmt->get_result();
      $product = $result->fetch_assoc();
      if ($product) {
        echo json_encode($product);
      } else {
        http_response_code(404);
        echo json_encode(["error" => "Product not found"]);
      }
    } else {
      $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
      $products = [];
      while ($row = $result->fetch_assoc()) {
        $products[] = $row;
      }
      echo json_encode($products);
    }
    break;

  case 'POST':
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
      http_response_code(400);
      echo json_encode(["error" => "Invalid JSON"]);
      exit();
    }
    $code = $conn->real_escape_string($data['code'] ?? '');
    $name = $conn->real_escape_string($data['product_name'] ?? '');
    $qty = intval($data['quantity'] ?? 0);
    $exp = $data['expiration_date'] ?? null;
    $price = floatval($data['price'] ?? 0);

    if (!$code || !$name || $qty < 0 || $price < 0) {
      http_response_code(400);
      echo json_encode(["error" => "Invalid input"]);
      exit();
    }

    $stmt = $conn->prepare("INSERT INTO products (code, product_name, quantity, expiration_date, price) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisd", $code, $name, $qty, $exp, $price);
    if ($stmt->execute()) {
      http_response_code(201);
      echo json_encode(["message" => "Product created", "id" => $stmt->insert_id]);
    } else {
      http_response_code(500);
      echo json_encode(["error" => "Insert failed"]);
    }
    break;

  case 'PUT':
    if (!$id) {
      http_response_code(400);
      echo json_encode(["error" => "Missing product ID"]);
      exit();
    }
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
      http_response_code(400);
      echo json_encode(["error" => "Invalid JSON"]);
      exit();
    }
    $code = $conn->real_escape_string($data['code'] ?? '');
    $name = $conn->real_escape_string($data['product_name'] ?? '');
    $qty = intval($data['quantity'] ?? 0);
    $exp = $data['expiration_date'] ?? null;
    $price = floatval($data['price'] ?? 0);

    if (!$code || !$name || $qty < 0 || $price < 0) {
      http_response_code(400);
      echo json_encode(["error" => "Invalid input"]);
      exit();
    }

    $stmt = $conn->prepare("UPDATE products SET code=?, product_name=?, quantity=?, expiration_date=?, price=? WHERE id=?");
    $stmt->bind_param("ssisdi", $code, $name, $qty, $exp, $price, $id);
    if ($stmt->execute()) {
      echo json_encode(["message" => "Product updated"]);
    } else {
      http_response_code(500);
      echo json_encode(["error" => "Update failed"]);
    }
    break;

  case 'DELETE':
    if (!$id) {
      http_response_code(400);
      echo json_encode(["error" => "Missing product ID"]);
      exit();
    }
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      echo json_encode(["message" => "Product deleted"]);
    } else {
      http_response_code(500);
      echo json_encode(["error" => "Delete failed"]);
    }
    break;

  case 'OPTIONS':
    http_response_code(204);
    exit();

  default:
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    break;
}

$conn->close();
?>
