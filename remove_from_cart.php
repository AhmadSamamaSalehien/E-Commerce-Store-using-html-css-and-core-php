<?php

include 'init.php';

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['id'])) {
    $cart_id = (int)$_GET['id'];

    // Find and remove the item from session cart
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['cart_id'] == $cart_id) {
            unset($_SESSION['cart'][$index]);
            // Reindex array to avoid gaps
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            break;
        }
    }

    header("Location: cart.php");
    exit;
} else {
    header("Location: cart.php?error=invalid_id");
    exit;
}
?>