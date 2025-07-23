<?php
session_start();

// Clear cart for non-logged-in users on session start
// if (!isset($_SESSION['user_id']) && !isset($_SESSION['cart_cleared'])) {
//     $_SESSION['cart'] = [];
//     $_SESSION['cart_cleared'] = true;
// }

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
