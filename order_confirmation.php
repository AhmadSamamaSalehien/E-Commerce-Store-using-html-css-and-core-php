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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - My Store</title>
    <link rel="shortcut icon" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/svg/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/iconly.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 80px);
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

        /* Confirmation Section */
        .confirmation {
            text-align: center;
            background: #f8f9fa;
            border: 1px solid #d3d6d9;
            border-radius: 10px;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            transition: all 0.2s ease;
        }

        .confirmation:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .confirmation h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #343a40;
        }

        .confirmation p {
            font-size: 1rem;
            margin-bottom: 1.5rem;
            color: #343a40;
        }

        .confirmation a {
            background: #435ebe;
            color: #FFFFFF;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s ease;
        }

        .confirmation a:hover {
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

        body.bg-dark .container {
            background: #1A233A;
        }

        body.bg-dark .confirmation {
            background: #1A233A;
            border-color: #4a5e8c;
        }

        body.bg-dark .confirmation h2,
        body.bg-dark .confirmation p {
            color: #FFFFFF;
        }

        body.bg-dark .confirmation a {
            background: #3B7DDD;
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
            .confirmation {
                padding: 1.5rem;
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

    <section class="container">
        <div class="confirmation">
            <h2>Order Confirmed!</h2>
            <p>Thank you for your purchase. Your order has been successfully placed.</p>
            <a href="index.php">Continue Shopping</a>
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
    </script>
</body>
</html>
<?php $conn->close(); ?>