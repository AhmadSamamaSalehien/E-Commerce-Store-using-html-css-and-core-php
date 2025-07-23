<?php
include 'init.php';
$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("SELECT p.*, b.name as brand_name 
                        FROM products p 
                        LEFT JOIN brands b ON p.brand_id = b.id 
                        ORDER BY p.id DESC");

// Fetch all categories for the scrolling section
$categories_result = $conn->query("SELECT * FROM categories");
if (!$categories_result) {
    error_log("Error fetching categories: " . $conn->error);
    die("Error loading categories. Please try again later.");
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My E-Commerce Store</title>
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
            padding-top: 80px; /* Offset for fixed navbar */
        }

        a {
            text-decoration: none;
            color: var(--color);
        }

        h1, h2, h4, p {
            margin: 0;
        }

        .container {
            padding: 20px 20px;
            max-width: 100%;
            margin: 0 auto;
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

        /* Hero Section */
        #hero {
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
            background-size: cover;
            height: 80vh;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            text-align: left;
            color: var(--color);
            padding: 0 40px; 
            margin-top: 0;
            position: relative;
            overflow: hidden;
        }

        #hero video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        #hero h4 {
            font-size: 1.2rem;
            opacity: 0.6;
            position: relative;
            z-index: 1;
        }

        #hero h2 {
            font-size: 2.5rem;
            margin: 10px 0;
            position: relative;
            z-index: 1;
        }

        #hero h1 {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        #hero p {
            font-size: 1.2rem;
            opacity: 0.6;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        #hero button {
            background: var(--primary-color);
            color: var(--color);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.1s ease-in-out;
            position: relative;
            z-index: 1;
        }

        #hero button:hover {
            transform: scale(1.05);
        }

        /* Categories Scroller */
        .categories-scroller {
            width: 100%;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .categories-track {
            display: flex;
            animation: scroll 20s linear infinite;
            width: max-content;
        }

        .category-item {
            flex: 0 0 auto;
            width: 200px;
            height: 150px;
            margin: 0 10px;
            position: relative;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .category-item:hover {
            transform: scale(1.1);
        }

        .category-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #d3d6d9;
        }

        .category-item .category-name {
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            background: rgba(255, 255, 255, 0.8);
            color: #343a40;
            padding: 5px;
            font-size: 14px;
            font-weight: 500;
        }

        .no-image {
            width: 100%;
            height: 100%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #d3d6d9;
        }

        @keyframes scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        /* Product Section */
        #product1 {
            padding: 80px 20px 20px;
            text-align: center;
        }

        #product1 .container {
            max-width: 100%;
        }

        #product1 h2 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #000000; 
        }

        #product1 p {
            font-size: 1.2rem;
            opacity: 0.6;
            margin-bottom: 30px;
            color: #000000; 
        }

        .pro-container {
            display: flex;
            flex-wrap: wrap;
            padding: 40px;
        }

        .pro {
            width: 23%;
            min-width: 300px;
            background: #f8f9fa;
            border: 1px solid #d3d6d9;
            border-radius: 10px;
            padding: 15px;
            position: relative;
            margin: 15px 0;
            transition: all 0.2s ease;
            margin: 10px;
        }

        .pro:hover {
            transform: scale(1.02); 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); 
        }

        .pro img {
            width: 100%;
            height: 260px;
            object-fit: cover;
            border-radius: 8px; 
            border: 1px solid #d3d6d9;
        }

        .pro .des {
            padding: 10px 0;
            text-align: left;
        }

        .pro .des span {
            font-size: 12px;
            color: #6c757d;
        }

        .pro h5 {
            margin: 10px 0;
            font-size: 14px;
            color: #343a40;
        }

        .pro h4 {
            margin: 10px 0;
            font-size: 15px;
            color: #343a40; 
        }

        .pro .star i {
            color: #ffd700;
            margin: 0 2px;
            font-size: 12px;
        }

        .cart {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: #435ebe;
            color: #FFFFFF;
            width: 40px;
            height: 40px;
            line-height: 40px;
            border-radius: 50%;
            border: 1px solid #d3d6d9;
            transition: transform 0.2s ease;
            text-align: center;
            cursor: pointer;
        }

        .cart:hover {
            transform: scale(1.1);
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
        body.bg-dark #product1 {
            background: #1A233A; 
        }

        body.bg-dark #product1 h2,
        body.bg-dark #product1 p {
            color: #FFFFFF; 
        }

        body.bg-dark .pro {
            background: #1A233A; 
            border-color: #4a5e8c; 
        }

        body.bg-dark .pro .des span,
        body.bg-dark .pro h5,
        body.bg-dark .pro h4 {
            color: #FFFFFF; 
        }

        body.bg-dark .pro img {
            border-color: #4a5e8c; 
        }

        body.bg-dark .cart {
            border-color: #4a5e8c; 
        }

        body.bg-dark .category-item img {
            border-color: #4a5e8c;
        }

        body.bg-dark .category-item .category-name {
            background: rgba(26, 35, 58, 0.8);
            color: #FFFFFF;
        }

        body.bg-dark .no-image {
            background-color: #2A3A5A;
            color: #A0AEC0;
            border-color: #4a5e8c;
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
            .pro {
                width: calc(50% - 20px);
            }
            .category-item {
                width: 150px;
                height: 120px;
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

        @media (max-width: 477px) {
            .pro {
                width: 100%;
            }
            .category-item {
                width: 120px;
                height: 100px;
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
        }
    </style>
</head>
<body>
    <?php include 'top_nav.php'; ?>

    <section id="hero">
        <video autoplay muted loop>
            <source src="v1.mp4" type="video/mp4">
        </video>
        <h4>Trade-in-offer</h4>
        <h2>Super value deals</h2>
        <h1>On all products</h1>
        <p>Save more with coupons & up to 70% off!</p>
        <button onclick="window.location.href='products.php'">Shop Now</button>
    </section>

    <section id="product1" class="section-p1">
        <div class="container">
            <h2>Featured Products</h2>
            <p>Summer Collection New Modern Design</p>
            <!-- Categories Scroller -->
            <div class="categories-scroller">
                <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                    <div class="categories-track">
                        <?php while ($category = $categories_result->fetch_assoc()): ?>
                            <div class="category-item" onclick="window.location.href='products.php?category_id=<?php echo (int)$category['id']; ?>'">
                                <?php if (!empty($category['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($category['image_path']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">No Image</div>
                                <?php endif; ?>
                                <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                            </div>
                        <?php endwhile; ?>
                        <!-- Duplicate categories for seamless scrolling -->
                        <?php $categories_result->data_seek(0); ?>
                        <?php while ($category = $categories_result->fetch_assoc()): ?>
                            <div class="category-item" onclick="window.location.href='products.php?category_id=<?php echo (int)$category['id']; ?>'">
                                <?php if (!empty($category['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($category['image_path']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">No Image</div>
                                <?php endif; ?>
                                <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No categories available to display.</p>
                <?php endif; ?>
            </div>
            <div class="pro-container">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="pro">
                        <a href="product.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                            <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                            <div class="des">
                                <span><?php echo htmlspecialchars($row['brand_name'] ?: 'N/A'); ?></span>
                                <h5><?php echo $row['name']; ?></h5>
                                <div class="star">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <h4>Rs. <?php echo number_format($row['price'], 2); ?></h4>
                            </div>
                        </a>
                        <div class="cart" data-product-id="<?php echo $row['id']; ?>"><i class="fa-solid fa-cart-shopping"></i></div>
                    </div>
                <?php endwhile; ?>
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

        // Add to cart functionality
        document.querySelectorAll('.cart').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = button.getAttribute('data-product-id');
                
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
                        document.querySelector('.navbar-nav .nav-link[href="cart.php"] .badge').textContent = data.cart_quantity;

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
                        alert(data.message); // Show error message
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding to cart.');
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>