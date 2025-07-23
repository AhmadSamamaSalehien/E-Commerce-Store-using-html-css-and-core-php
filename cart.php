<?php
include 'init.php';
$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle quantity update directly in cart.php
if (isset($_GET['action']) && $_GET['action'] == 'update_quantity') {
    header('Content-Type: application/json'); // Ensure JSON response

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        exit;
    }

    if (!isset($_POST['cart_id']) || !isset($_POST['quantity'])) {
        echo json_encode(["success" => false, "message" => "Missing required parameters"]);
        exit;
    }

    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity < 1) {
        echo json_encode(["success" => false, "message" => "Quantity must be at least 1"]);
        exit;
    }

    // Find and update the cart item in session
    $item_found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['cart_id'] == $cart_id) {
            $item['quantity'] = $quantity;
            $item_found = true;
            break;
        }
    }

    if ($item_found) {
        echo json_encode(["success" => true, "message" => "Quantity updated"]);
    } else {
        echo json_encode(["success" => false, "message" => "Cart item not found"]);
    }

    exit;
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - My Store</title>
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
            margin: 0; /* Remove default margin */
        }

        body {
            margin: 0; /* Remove default margin */
            background: var(--background);
            color: var(--color);
            letter-spacing: 1px;
            transition: background 0.2s ease, color 0.2s ease;
            padding-top: 60px; /* Match with products.php */
            font-family: "Public Sans", sans-serif;
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
            max-width: 100%;
            margin: 0 auto;
            flex: 1;
        }

        /* Navbar Custom Styles */
        .navbar {
            padding: 1rem 1.5rem; /* Reduced padding */
            min-height: 50px; /* Reduced height */
            z-index: 1000; /* Ensure navbar stays above other content */
            margin-top: 0; /* Ensure no margin above navbar */
            background-color: #f8f9fa; /* Solid light background */
            transition: background-color 0.2s ease; /* Smooth transition for theme switch */
        }

        .navbar.bg-dark {
            background-color: #1A233A !important; /* Solid dark background */
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 8px; /* Space between icon and text */
            font-size: 1.5rem; /* Text size for logo */
            font-weight: bold;
            color: #435ebe; /* Fixed color for logo text */
            min-width: 150px; /* Fallback for logo container */
            min-height: 50px;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05); /* Hover effect */
        }

        .navbar-brand i {
            font-size: 2rem; /* Larger icon for logo */
            color: #435ebe; /* Fixed color for logo icon */
        }

        .navbar-nav .nav-item {
            margin-right: 0.5rem; /* Reduced spacing between buttons */
        }

        .navbar-nav .nav-link {
            font-size: 1rem; /* Reduced font size */
            padding: 0.5rem 1.5rem; /* Reduced padding */
            min-width: 100px; /* Reduced minimum width */
            display: flex;
            align-items: center;
            gap: 8px; /* Space between icon and text */
            transition: all 0.3s ease;
        }

        #dark-toggle {
            min-width: 60px; /* Smaller width for theme toggle button */
            padding: 0.75rem 1rem; /* Reduced padding for smaller size */
        }

        .navbar.bg-dark .nav-link {
            color: #FFFFFF; /* All navbar links white in dark mode */
        }

        .navbar-nav .nav-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 8px;
        }

        .navbar-nav .nav-link.active {
            font-weight: 600;
            color: white !important; /* Active link text always white */
            background-color: #435ebe; /* Match Mazer sidebar active background */
            border-radius: 8px;
        }

        .navbar-nav .nav-link.active i {
            color: white !important; /* Match sidebar active icon color */
        }

        .navbar-nav .nav-link i {
            font-size: 1.3rem; /* Larger icons */
        }

        .badge {
            font-size: 0.9rem;
            padding: 0.3em 0.6em;
        }

        /* Cart Section */
        #cart {
            padding: 80px 20px 20px; /* Match with products.php */
            text-align: center;
        }

        #cart h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #000000; /* Black color in light mode */
        }

        .cart-item {
            display: flex;
            align-items: center;
            background: #f8f9fa; /* Mazer light mode background */
            border: 1px solid #d3d6d9; /* Darker border color */
            border-radius: 10px; /* Mazer rounded corners */
            padding: 15px;
            margin-bottom: 20px;
            transition: all 0.2s ease;
        }

        .cart-item:hover {
            transform: scale(1.02); /* Mazer hover scale */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); /* Shadow only on hover */
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px; /* Rounded image */
            border: 1px solid #d3d6d9; /* Darker border color */
            margin-right: 15px;
        }

        .cart-item .details {
            flex-grow: 1;
            text-align: left;
        }

        .cart-item .details h5 {
            font-size: 1.2rem;
            margin-bottom: 5px;
            color: #343a40; /* Mazer dark text */
        }

        .cart-item .details p {
            font-size: 1rem;
            opacity: 0.6;
            margin-bottom: 5px;
            color: #343a40; /* Mazer dark text */
        }

        .cart-item .remove {
            background: #e94560; /* Red color for remove */
            color: #FFFFFF;
            width: 40px;
            height: 40px;
            line-height: 40px;
            border-radius: 50%;
            border: 1px solid #d3d6d9; /* Darker border color */
            transition: transform 0.2s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }

        .cart-item .remove:hover {
            transform: scale(1.1);
        }

        /* Quantity Input (Mazer Style) */
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 5px;
        }

        .quantity-control button {
            background: #435ebe; /* Mazer primary color */
            color: #FFFFFF;
            border: 1px solid #d3d6d9;
            border-radius: 5px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .quantity-control button:hover {
            transform: scale(1.05);
        }

        .quantity-control input {
            width: 50px;
            text-align: center;
            border: 1px solid #d3d6d9;
            border-radius: 5px;
            background: #ffffff;
            color: #343a40;
            padding: 0;
            height: 30px;
        }

        .total {
            text-align: right;
            font-size: 1.5rem;
            margin-top: 20px;
            color: #000000; /* Black color in light mode */
        }

        .checkout-btn {
            background: #435ebe; /* Mazer primary color */
            color: #FFFFFF;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: transform 0.2s ease;
        }

        .checkout-btn:hover {
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

        body.bg-dark #cart {
            background: #1A233A;
        }

        body.bg-dark #cart h2,
        body.bg-dark .total {
            color: #FFFFFF; /* White text for titles */
        }

        body.bg-dark .cart-item {
            background: #1A233A; /* Same as body background in dark mode */
            border-color: #4a5e8c; /* Highlighted border color */
        }

        body.bg-dark .cart-item .details h5,
        body.bg-dark .cart-item .details p {
            color: #FFFFFF; /* White text in dark mode */
        }

        body.bg-dark .cart-item img {
            border-color: #4a5e8c; /* Highlighted border color */
        }

        body.bg-dark .cart-item .remove {
            border-color: #4a5e8c; /* Highlighted border color */
        }

        body.bg-dark .quantity-control button {
            background: #3B7DDD; /* Dark mode primary color */
            border-color: #4a5e8c;
        }

        body.bg-dark .quantity-control input {
            background: #1A233A;
            color: #FFFFFF;
            border-color: #4a5e8c;
        }

        body.bg-dark .checkout-btn {
            background: #3B7DDD; /* Dark mode primary color */
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

        @media (max-width: 799px) {
            .cart-item {
                flex-direction: column;
                height: auto;
                text-align: center;
            }
            .cart-item img {
                margin-bottom: 15px;
            }
            .cart-item .details {
                margin-bottom: 15px;
            }
            .cart-item .remove {
                margin-left: 0;
            }
            .navbar-brand {
                font-size: 1.2rem; /* Smaller text on mobile */
            }
            .navbar-brand i {
                font-size: 1.5rem; /* Smaller icon on mobile */
            }
            .navbar-nav .nav-item {
                margin-right: 0; /* Remove spacing in mobile view */
            }
            .navbar-nav .nav-link {
                min-width: auto; /* Allow natural width in mobile */
                padding: 0.5rem 1rem; /* Adjust padding for mobile */
            }
            #dark-toggle {
                min-width: auto; /* Adjust for mobile */
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

    <section id="cart" class="section-p1">
        <div class="container">
            <h2>Your Cart</h2>
            <?php if (empty($_SESSION['cart'])): ?>
                <p>Your cart is empty. <a href="products.php">Browse products</a> to add items.</p>
            <?php else: ?>
                <?php foreach ($_SESSION['cart'] as $row): ?>
                    <?php $subtotal = $row['price'] * $row['quantity']; $total += $subtotal; ?>
                    <div class="cart-item" data-id="<?php echo $row['cart_id']; ?>">
                        <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                        <div class="details">
                            <h5><?php echo $row['name']; ?></h5>
                            <p>Price: Rs. <span class="price"><?php echo number_format($row['price'], 2); ?></span></p>
                            <div class="quantity-control">
                                <button class="decrease">-</button>
                                <input type="number" class="quantity" value="<?php echo $row['quantity']; ?>" min="1" readonly>
                                <button class="increase">+</button>
                            </div>
                            <p>Subtotal: Rs. <span class="subtotal"><?php echo number_format($subtotal, 2); ?></span></p>
                        </div>
                        <a href="remove_from_cart.php?id=<?php echo $row['cart_id']; ?>" class="remove"><i class="fa-solid fa-trash"></i></a>
                    </div>
                <?php endforeach; ?>
                <div class="total">Total: Rs. <span id="total"><?php echo number_format($total, 2); ?></span></div>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'checkout.php' : 'login.php'; ?>" class="checkout-btn">Proceed to Checkout</a>
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

        // Quantity update functionality with enhanced debug
        document.querySelectorAll('.cart-item').forEach(item => {
            const decreaseBtn = item.querySelector('.decrease');
            const increaseBtn = item.querySelector('.increase');
            const quantityInput = item.querySelector('.quantity');
            const cartId = item.getAttribute('data-id');
            const price = parseFloat(item.querySelector('.price').textContent.replace(/,/g, ''));
            const subtotalElement = item.querySelector('.subtotal');
            const totalElement = document.getElementById('total');

            console.log('Initializing item:', cartId, 'with quantity:', quantityInput.value, 'and price:', price);

            const updateQuantity = async (newQuantity) => {
                console.log('Updating quantity to:', newQuantity, 'for cart ID:', cartId);
                try {
                    const response = await fetch('cart.php?action=update_quantity', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `cart_id=${cartId}&quantity=${newQuantity}`
                    });

                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const data = await response.json();
                    console.log('Response data:', data);

                    if (data.success) {
                        quantityInput.value = newQuantity;
                        const subtotal = price * newQuantity;
                        subtotalElement.textContent = subtotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                        // Update total
                        let total = 0;
                        document.querySelectorAll('.cart-item').forEach(cartItem => {
                            const qty = parseInt(cartItem.querySelector('.quantity').value);
                            const itemPrice = parseFloat(cartItem.querySelector('.price').textContent.replace(/,/g, ''));
                            total += qty * itemPrice;
                        });
                        totalElement.textContent = total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error('Fetch error:', error.message);
                    alert('An error occurred while updating the quantity: ' + error.message);
                }
            };

            decreaseBtn.addEventListener('click', () => {
                let quantity = parseInt(quantityInput.value);
                if (quantity > 1) {
                    updateQuantity(quantity - 1);
                } else {
                    console.log('Quantity cannot go below 1');
                }
            });

            increaseBtn.addEventListener('click', () => {
                let quantity = parseInt(quantityInput.value);
                updateQuantity(quantity + 1);
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>