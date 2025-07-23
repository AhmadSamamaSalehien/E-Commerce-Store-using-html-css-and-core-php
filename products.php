<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'init.php';
$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}

// Handle search and category filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Fetch products with prepared statement
$query = "SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id";
$conditions = [];
$params = [];
$types = '';

if ($search) {
    $conditions[] = "p.name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}
if ($category_id) {
    $conditions[] = "category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    error_log("Error fetching products: " . $conn->error);
    die("Error loading products. Please try again later.");
}

// Fetch category name if category_id is set
$category_name = "All Products";
if ($category_id) {
    $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $category_result = $stmt->get_result();
    if ($category_result && $category_result->num_rows > 0) {
        $category_name = $category_result->fetch_assoc()['name'];
    } else {
        $category_name = "Unknown Category";
    }
    $stmt->close();
}

// Fetch all categories for the scrolling section
$categories_result = $conn->query("SELECT * FROM categories");
if (!$categories_result) {
    error_log("Error fetching categories: " . $conn->error);
    die("Error loading categories. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - My Store</title>
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
            padding: 20px 20px;
            max-width: 100%;
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

        /* Search Bar */
        .search-form {
            width: 90%;
            margin: -40px auto 30px;
            display: flex;
            gap: 10px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 10px;
            transition: 0.2s ease;
        }

        .search-form:hover {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .search-form .input {
            resize: vertical;
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            outline: none;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            width: 90%;
            color: #000000;
            margin: 0.5rem 0;
            transition: all 0.25s ease;
        }

        .search-form .input:focus {
            border-color: #3B7DDD;
            outline: 0;
            box-shadow: 0 0 0 2px rgba(59, 125, 221, 0.2);
        }

        .search-form .submit {
            height: 38px;
            width: 10%;
            outline: none;
            cursor: pointer;
            background: #435ebe;
            border: none;
            color: #ffffff;
            font-weight: 500;
            letter-spacing: 0.25px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 1rem;
            text-align: center;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            margin: 0.5rem 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: transform 0.2s ease;
        }

        .search-form .submit:hover {
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
        body.bg-dark {
            background: #1A233A !important;
        }

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

        body.bg-dark .search-form {
            background: #2c3e50;
            border-color: #2c3e50;
        }

        body.bg-dark .search-form .input {
            background: #1A233A;
            border-color: #2c3e50;
            color: #FFFFFF;
        }

        body.bg-dark .search-form .input:focus {
            border-color: #3B7DDD;
            box-shadow: 0 0 0 2px rgba(59, 125, 221, 0.2);
        }

        body.bg-dark .search-form .submit {
            background: #3B7DDD;
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
        }
    </style>
</head>
<body>
    <?php include 'top_nav.php'; ?>

    <section id="product1" class="section-p1">
        <div class="container">
            <form class="search-form" method="GET">
                <input class="input" type="search" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <?php if ($category_id): ?>
                    <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                <?php endif; ?>
                <button class="submit" type="submit"><i class="fas fa-search"></i> Search</button>
            </form>

            <!-- Categories Scroller -->
            <div class="categories-scroller">
                <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                    <div class="categories-track">
                        <?php while ($category = $categories_result->fetch_assoc()): ?>
                            <div class="category-item" onclick="window.location.href='products.php?category_id=<?php echo (int)$category['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>'">
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
                            <div class="category-item" onclick="window.location.href='products.php?category_id=<?php echo (int)$category['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>'">
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

            <h2><?php echo htmlspecialchars($category_name); ?></h2>
            <div class="pro-container">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="pro">
                            <a href="product.php?id=<?php echo (int)$row['id']; ?>" style="text-decoration: none; color: inherit;">
                                <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                <div class="des">
                                    <span><?php echo htmlspecialchars($row['brand_name'] ?: 'N/A'); ?></span>
                                    <h5><?php echo htmlspecialchars($row['name']); ?></h5>
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
                            <div class="cart" data-product-id="<?php echo (int)$row['id']; ?>"><i class="fa-solid fa-cart-shopping"></i></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No products found.</p>
                <?php endif; ?>
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
        document.querySelectorAll('.cart').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = button.getAttribute('data-product-id');

                // Validate productId
                if (!productId || isNaN(productId)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Product',
                        text: 'The product ID is invalid. Please try again.'
                    });
                    return;
                }

                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${encodeURIComponent(productId)}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Add to cart response:', data); // Debug response
                    if (data.success) {
                        const cartQuantityElement = document.getElementById('cart-quantity');
                        if (cartQuantityElement) {
                            cartQuantityElement.textContent = data.cart_quantity || 0;
                        } else {
                            console.error('Cart quantity badge not found in navbar');
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning',
                                text: 'Cart quantity badge not found in navbar. Please check top_nav.php.'
                            });
                        }
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
                                text: data.message || 'Failed to add product to cart.'
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Add to Cart Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while adding to cart. Please try again.'
                    });
                });
            });
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>