<?php
include 'init.php';
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}

// Initialize session cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Validate quantity
    if ($quantity < 1) {
        echo json_encode(["success" => false, "message" => "Invalid quantity."]);
        exit;
    }

    // Check kerna hai k product exist kerta hai ya nhin
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($product = $result->fetch_assoc()) {
        // Check krna hai k item pehly sy cart mein hai ya nhin
        $item_index = -1;
        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['product_id'] == $product_id) {
                $item_index = $index;
                break;
            }
        }

        if ($item_index >= 0) {
            // quantity ko update kerna hai
            $_SESSION['cart'][$item_index]['quantity'] += $quantity;
        } else {
            // Add new item to cart with a unique cart_id
            $cart_id = count($_SESSION['cart']) + 1; // Simple increment for unique ID
            $_SESSION['cart'][] = [
                'cart_id' => $cart_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image']
            ];
        }

        // Calculate total number of unique products
        $cart_quantity = count($_SESSION['cart']);

        $stmt->close();
        $conn->close();

        echo json_encode([
            "success" => true,
            "message" => "Product added to cart",
            "cart_quantity" => $cart_quantity
        ]);
        exit;
    } else {
        $stmt->close();
        $conn->close();
        echo json_encode(["success" => false, "message" => "Product not found."]);
        exit;
    }
} else {
    $conn->close();
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}
?>
