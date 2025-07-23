<?php
// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart quantity (number of unique products)
$cart_quantity = count($_SESSION['cart']);

// Determine the current page for active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav style="border-bottom: 1px solid lightgrey;" class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid">
        <a style="color: #3b7edd;" class="navbar-brand" href="index.php">
            <i style="color: #3b7edd;" class="fa-solid fa-shop"></i>
            My Store
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php"><i class="fa-solid fa-home icon-home"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>" href="products.php"><i class="fa-solid fa-box icon-products"></i> Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'cart.php' ? 'active' : ''; ?>" href="cart.php"><i class="fa-solid fa-cart-shopping icon-cart"></i> Cart <span id="cart-quantity" class="badge bg-primary"><?php echo $cart_quantity; ?></span></a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'logout.php' ? 'active' : ''; ?>" href="logout.php"><i class="fa-solid fa-sign-out-alt icon-logout"></i> Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'login.php' ? 'active' : ''; ?>" href="login.php"><i class="fa-solid fa-sign-in-alt icon-login"></i> Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'signup.php' ? 'active' : ''; ?>" href="signup.php"><i class="fa-solid fa-user-plus icon-signup"></i> Sign Up</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="dark-toggle"><i class="fa-solid fa-moon text-dark"></i></a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Colorful icons for navbar links */
    .navbar-nav .nav-link .icon-home {
        color: #28a745; /* Green for Home */
    }
    .navbar-nav .nav-link .icon-products {
        color: #007bff; /* Blue for Products */
    }
    .navbar-nav .nav-link .icon-cart {
        color: #ff9800; /* Orange for Cart */
    }
    .navbar-nav .nav-link .icon-login {
        color: #6f42c1; /* Purple for Login */
    }
    .navbar-nav .nav-link .icon-signup {
        color: #dc3545; /* Red for Sign Up */
    }
    .navbar-nav .nav-link .icon-logout {
        color: #17a2b8; /* Cyan for Logout */
    }

    /* Active link styling */
    .navbar-nav .nav-link.active {
        background-color: #3b7edd !important;
        color: #ffffff !important;
        border-radius: 8px;
    }
    .navbar-nav .nav-link.active i {
        color: #ffffff !important;
    }

    /* Ensure badge text remains readable */
    .navbar-nav .nav-link.active .badge {
        color: #ffffff;
        background-color: #0056b3; /* Slightly darker blue for contrast */
    }
</style>