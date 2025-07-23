<?php
include 'init.php';
$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("
    SELECT p.*, 
           b.name AS brand_name, 
           c.name AS category_name, 
           cl.name AS color_name, 
           s.name AS size_name 
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN colors cl ON p.color_id = cl.id 
    LEFT JOIN sizes s ON p.size_id = s.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();
if (!$product) {
    die("Product not found");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - My Store</title>
    <link rel="shortcut icon" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/svg/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/iconly.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --background: #FFFFFF;
            --color: #FFFFFF;
            --primary-color: #3B7DDD;
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
            padding-top: 80px;
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
            max-width: 1200px;
            margin: 0 auto;
            flex: 1;
        }

        /* Navbar Custom Styles */
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
            gap: 10px;
            font-size: 1.5rem;
            font-weight: bold;
            color: #435ebe;
            min-width: 150px;
            min-height: 38px;
            transition: all 0.3s ease;
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
            min-width: 0;
            padding: 10px 16px;
        }

        .navbar.bg-dark .navbar-nav .nav-link {
            color: #ffffff;
        }

        .navbar-nav .nav-link:hover {
            background-color: rgba(0,0,0,0.05);
            border-radius: 10px;
        }

        .navbar-nav .nav-link.active {
            font-weight: bold;
            color: #ffffff;
            background-color: #435ebe;
            border-radius: 10px;
        }

        .navbar-nav .nav-link.active i {
            color: #ffffff;
        }

        .navbar-nav .nav-link i {
            font-size: 20px;
        }

        .badge {
            font-size: 14px;
            padding: 5px 10px;
        }

        /* Product Details Section */
        #product-details {
            padding: 10px 20px 20px;
            text-align: center;
        }

        .product-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 0px;
        }

        .product-image {
            width: 50%;
            min-width: 300px;
            padding: 15px;
        }

        .product-image img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #d3d6d9;
            transition: transform 0.2s ease;
        }

        .product-image img:hover {
            transform: scale(1.02);
        }

        .product-info {
            width: 45%;
            min-width: 300px;
            background: #f8f9fa;
            border: 1px solid #d3d6d9;
            border-radius: 10px;
            padding: 15px;
            text-align: left;
            transition: all 0.2s ease;
            position: relative;
        }

        .product-info:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .product-info h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #343a40;
        }

        .product-info .star i {
            color: #ffd700;
            margin: 0 2px;
            font-size: 12px;
        }

        .product-info .price {
            font-size: 1.2rem;
            color: #088178;
            margin: 10px 0;
        }

        .product-info .description {
            font-size: 1rem;
            opacity: 0.6;
            margin-bottom: 20px;
            color: #343a40;
        }

        .product-info .cart {
            background: #435ebe;
            color: #FFFFFF;
            width: 200px;
            height: 50px;
            line-height: 50px;
            border-radius: 10px;
            border: 1px solid #d3d6d9;
            transition: transform 0.2s ease;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            padding: 0 10px;
            cursor: pointer;
        }

        .product-info .cart {
            background: #435ebe;
            color: #FFFFFF;
        }

        .product-info .cart i {
            margin-right: 8px;
        }

        .product-info .cart:hover {
            transform: scale(1.1);
        }

        /* Attribute Buttons (Brand, Category, Color, Size) */
        .product-info .attributes {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .product-info .attribute-btn {
            background: #ffffff;
            color: #343a40;
            border: 1px solid #d3d6d9;
            border-radius: 50px;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: default; /* Not clickable unless specified */
            transition: all time 0.2s ease;
        }

        .product-info .attribute-btn:hover {
            background: #e9ecef;
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
            background: #1A233A !important;
        }

        body.bg-dark #product-details {
            background: #1A233A;
        }

        body.bg-dark .product-info {
            background: #1A233A;
            border-color: #4a5e8c;
        }

        body.bg-dark .product-info h2,
        body.bg-dark .product-info .description,
        body.bg-dark .product-info .price {
            color: #FFFFFF;
        }

        body.bg-dark .product-image img {
            border-color: #4a5e8c;
        }

        body.bg-dark .product-info .cart {
            border-color: #4a5e8c;
            background: #3B7DDD;
        }

        body.bg-dark .product-info .attribute-btn {
            background: #2A3A5A;
            color: #FFFFFF;
            border-color: #4a5e8c;
        }

        body.bg-dark .product-info .attribute-btn:hover {
            background: #3A4A6A;
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

        /* Responsive Design */
        @media (max-width: 799px) {
            .product-image, .product-info {
                width: 100%;
            }
            .product-image img {
                height: 300px;
            }
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

    <section id="product-details" class="container my-5">
        <div class="product-container">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="product-info">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <div class="star">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
                <div class="price">Rs. <?php echo number_format($product['price'], 2); ?></div>
                <div class="attributes">
                    <?php if ($product['brand_name']): ?>
                        <span class="attribute-btn">Brand: <?php echo htmlspecialchars($product['brand_name']); ?></span>
                    <?php endif; ?>
                    <?php if ($product['category_name']): ?>
                        <span class="attribute-btn">Category: <?php echo htmlspecialchars($product['category_name']); ?></span>
                    <?php endif; ?>
                    <?php if ($product['color_name']): ?>
                        <span class="attribute-btn">Color: <?php echo htmlspecialchars($product['color_name']); ?></span>
                    <?php endif; ?>
                    <?php if ($product['size_name']): ?>
                        <span class="attribute-btn">Size: <?php echo htmlspecialchars($product['size_name']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="description"><?php echo htmlspecialchars($product['description']); ?></div>
                <div class="cart" data-product-id="<?php echo $product['id']; ?>"><i class="fa-solid fa-cart-shopping"></i> Add to Cart</div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/initTheme.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/components/dark.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/js/app.js"></script>
    <!-- SweetAlert2 Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.min.js"></script>
    <script>
        let isDarkTheme = false;

        const toggleTheme = () => {
            const root = document.querySelector(':root');
            const navbar = document.querySelector('.navbar');
            const toggleIcon = document.getElementById('dark-toggle').querySelector('i');
            if (isDarkTheme) {
                root.style.setProperty('--background', '#FFFFFF');
                root.style.setProperty('--color', '#FFFFFF');
                root.style.setProperty('--primary-color', '#3B7DDD');
                navbar.classList.remove('bg-dark');
                navbar.classList.add('bg-light');
                toggleIcon.classList.remove('fa-sun', 'text-light');
                toggleIcon.classList.add('fa-moon', 'text-dark');
                document.body.classList.remove('bg-dark');
            } else {
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

        // Add to cart functionality
        document.querySelector('.cart').addEventListener('click', (e) => {
            e.preventDefault();
            const productId = e.currentTarget.getAttribute('data-product-id');
            
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart quantity in navbar
                    const badge = document.getElementById('cart-quantity');
                    if (badge) {
                        badge.textContent = data.cart_quantity;
                    }

                    // Show SweetAlert2
                    Swal.fire({
                        title: "Product added to cart",
                        width: 600,
                        padding: "3em",
                        color: "#716add",
                        background: "#fff url(https://sweetalert2.github.io/images/trees.png)",
                        backdrop: `
                            rgba(0,0,123,0.4)
                            url("https://sweetalert2.github.io/images/nyan-cat.gif")
                            left top
                            no-repeat
                        `
                    });
                } else {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message || 'Something went wrong!'
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An error occurred while adding to cart.'
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>