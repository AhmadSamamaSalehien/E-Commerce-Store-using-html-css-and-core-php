<?php
include 'init.php';
$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate total price from session cart
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($total == 0) {
        header("Location: cart.php?error=empty_cart");
        exit;
    }

    // Insert order into database
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $conn->insert_id; // Get the newly inserted order ID
    $stmt->close();

    // Insert cart items into order_items table
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($_SESSION['cart'] as $item) {
        $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }
    $stmt->close();

    // Clear session cart after order is placed
    $_SESSION['cart'] = [];

    header("Location: order_confirmation.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - My Store</title>
    <link rel="shortcut icon" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/svg/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/iconly.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --background: #FFFFFF; /* Light mode background */
            --color: #FFFFFF; /* Match dark mode text color */
            --primary-color: #3B7DDD; /* Match dark mode primary color */
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            margin: 0;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--color);
            letter-spacing: 1px;
            transition: background 0.2s ease, color 0.2s ease;
            padding-top: 80px; /* Offset for fixed navbar */
            font-family: "Public Sans", sans-serif; /* Mazer default font */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            text-decoration: none;
            color: var(--color);
        }

        h1, h2, h4, p {
            margin: 0;
        }

        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            flex: 1;
        }

        /* Navbar Custom Styles (match index.php) */
        .navbar {
            padding: 1rem 1.5rem;
            min-height: 50px;
            z-index: 1000;
            margin-top: 0;
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        .navbar.bg-dark {
            background-color: #1A233A !important;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.5rem;
            font-weight: bold;
            color: #435ebe;
            min-width: 150px;
            min-height: 50px;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .navbar-brand i {
            font-size: 2rem;
            color: #435ebe;
        }

        .navbar-nav .nav-item {
            margin-right: 0.5rem;
        }

        .navbar-nav .nav-link {
            font-size: 1rem;
            padding: 0.5rem 1.5rem;
            min-width: 100px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        #dark-toggle {
            min-width: 60px;
            padding: 0.75rem 1rem;
        }

        .navbar.bg-dark .nav-link {
            color: #FFFFFF;
        }

        .navbar-nav .nav-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 8px;
        }

        .navbar-nav .nav-link.active {
            font-weight: 600;
            color: white !important;
            background-color: #435ebe;
            border-radius: 8px;
        }

        .navbar-nav .nav-link.active i {
            color: white !important;
        }

        .navbar-nav .nav-link i {
            font-size: 1.3rem;
        }

        .badge {
            font-size: 0.9rem;
            padding: 0.3em 0.6em;
        }

        /* Checkout Section */
        #checkout {
            padding: 80px 20px 20px;
            text-align: center;
        }

        #checkout h2 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            color: #343a40; /* Mazer dark text */
        }

        .checkout-form {
            max-width: 500px;
            margin: 0 auto;
            background: #f8f9fa; /* Mazer light mode background */
            border: 1px solid #d3d6d9; /* Darker border color */
            border-radius: 10px; /* Mazer rounded corners */
            padding: 20px;
            transition: all 0.2s ease;
        }

        .checkout-form:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            text-align: left;
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #343a40; /* Mazer dark text */
        }

        .form-group .input {
            resize: vertical;
            background: #ffffff;
            border: 1px solid #d3d6d9;
            border-radius: 6px;
            outline: none;
            padding: 0.65rem 0.75rem;
            font-size: 18px;
            width: 100%;
            color: #343a40;
            transition: all 0.25s ease;
        }

        .form-group .input:focus {
            border: 1px solid #435ebe; /* Mazer primary color */
            outline: 0;
            box-shadow: 0 0 0 2px rgba(67, 94, 190, 0.2);
        }

        .form-group select {
            background: #ffffff;
            border: 1px solid #d3d6d9;
            border-radius: 6px;
            outline: none;
            padding: 0.65rem 0.75rem;
            font-size: 18px;
            width: 100%;
            color: #343a40;
            transition: all 0.25s ease;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="gray" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
        }

        .form-group select:focus {
            border: 1px solid #435ebe;
            outline: 0;
            box-shadow: 0 0 0 2px rgba(59, 125, 221, 0.2);
        }

        .total {
            font-size: 1.5rem;
            margin: 20px 0;
            color: #088178; /* Match product.php price color */
        }

        .submit {
            background: #435ebe; /* Mazer primary color */
            color: #FFFFFF;
            outline: none;
            cursor: pointer;
            border: 1px solid #d3d6d9;
            font-weight: 500;
            letter-spacing: 0.25px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 1rem;
            text-align: center;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            transition: transform 0.2s ease;
        }

        .submit:hover {
            transform: scale(1.05);
        }

        /* Footer Styles */
        footer {
            background: #f8f9fa;
            border-top: 1px solid #d3d6d9;
            padding: 1.5rem;
            text-align: center;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        footer.text-white {
            color: #000000 !important;
        }

        footer a {
            color: #435ebe;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        footer a:hover {
            color: #3B7DDD;
        }

        /* Dark Mode Adjustments */
        body.bg-dark {
            background: #1A233A !important; /* Force match with navbar background in dark mode */
        }

        body.bg-dark #checkout {
            background: #1A233A;
        }

        body.bg-dark #checkout h2 {
            color: #FFFFFF;
        }

        body.bg-dark .checkout-form {
            background: #1A233A;
            border-color: #4a5e8c;
        }

        body.bg-dark .form-group label {
            color: #FFFFFF;
        }

        body.bg-dark .form-group .input,
        body.bg-dark .form-group select {
            background: #2A3A5A;
            border-color: #4a5e8c;
            color: #FFFFFF;
        }

        body.bg-dark .form-group .input:focus,
        body.bg-dark .form-group select:focus {
            border-color: #3B7DDD;
            box-shadow: 0 0 0 2px rgba(59, 125, 221, 0.2);
        }

        body.bg-dark .submit {
            border-color: #4a5e8c;
            background: #3B7DDD;
        }

        body.bg-dark footer {
            background: #1A233A;
            border-color: #4a5e8c;
        }

        body.bg-dark footer.text-white {
            color: #FFFFFF !important;
        }

        body.bg-dark footer a {
            color: #3B7DDD;
        }

        body.bg-dark footer a:hover {
            color: #FFFFFF;
        }

        /* Theme Switcher (Removed) */
        .theme-btn-container {
            display: none; /* Remove theme switcher buttons */
        }

        /* Responsive Design */
        @media (max-width: 799px) {
            .navbar-brand {
                font-size: 1.2rem;
            }
            .navbar-brand i {
                font-size: 1.5rem;
            }
            .navbar-nav .nav-item {
                margin-right: 0;
            }
            .navbar-nav .nav-link {
                min-width: auto;
                padding: 0.5rem 1rem;
            }
            #dark-toggle {
                min-width: auto;
                padding: 0.5rem 1rem;
            }
            footer {
                padding: 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'top_nav.php'; ?>

    <section id="checkout">
        <div class="container">
            <h2>Checkout</h2>
            <?php if ($total == 0): ?>
                <p>Your cart is empty. <a href="cart.php">Go to cart</a> to add items.</p>
            <?php else: ?>
                <form method="POST" class="checkout-form">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" class="input" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" class="input" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select required>
                            <option value="Cash on Delivery">Cash on Delivery</option>
                            <option value="Credit Card">Credit Card</option>
                        </select>
                    </div>
                    <div class="total">Total: Rs. <?php echo number_format($total, 2); ?></div>
                    <button type="submit" class="submit">Place Order</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/initTheme.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/components/dark.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/js/app.js"></script>
    <script>
        let isDarkTheme = false;

        const toggleTheme = () => {
            const root = document.querySelector(':root');
            const navbar = document.querySelector('.navbar');
            const toggleIcon = document.getElementById('dark-toggle').querySelector('i');
            if (isDarkTheme) {
                // Switch to light theme
                root.style.setProperty('--background', '#FFFFFF');
                root.style.setProperty('--color', '#FFFFFF');
                root.style.setProperty('--primary-color', '#3B7DDD');
                navbar.classList.remove('bg-dark');
                navbar.classList.add('bg-light');
                toggleIcon.classList.remove('fa-sun', 'text-light');
                toggleIcon.classList.add('fa-moon', 'text-dark');
                document.body.classList.remove('bg-dark');
            } else {
                // Switch to dark theme
                root.style.setProperty('--background', '#1A233A');
                root.style.setProperty('--color', '#FFFFFF');
                root.style.setProperty('--primary-color', '#3B7DDD');
                navbar.classList.remove('bg-light');
                navbar.classList.add('bg-dark');
                toggleIcon.classList.remove('fa-moon', 'text-dark');
                toggleIcon.classList.add('fa-sun', 'text-light');
                document.body.classList.add('bg-dark');
            }
            isDarkTheme = !isDarkTheme;
        };

        document.getElementById('dark-toggle').addEventListener('click', (e) => {
            e.preventDefault();
            toggleTheme();
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>