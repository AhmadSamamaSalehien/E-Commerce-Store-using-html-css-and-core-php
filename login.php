<?php
include 'init.php';
$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Use prepared statement for login
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];

            // Initialize session cart if not set
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // Merge session cart with user's cart (if any)
            // Since cart is stored in session, no database cart merging is needed
            // Simply ensure unique cart IDs
            foreach ($_SESSION['cart'] as &$item) {
                if (!isset($item['cart_id'])) {
                    $item['cart_id'] = count($_SESSION['cart']) + 1;
                }
            }

            $stmt->close();
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - My Store</title>
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
            --color: #333;
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

        /* Login Form */
        .login-container {
            max-width: 400px;
            width: 100%;
        }

        .form-container {
            background: #f8f9fa;
            border: 1px solid #d3d6d9;
            border-radius: 10px;
            padding: 2rem;
            transition: all 0.2s ease;
            text-align: center;
        }

        .form-container:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .form-container h1 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .form-container input {
            display: block;
            padding: 0.65rem 0.75rem;
            width: 100%;
            margin: 1rem 0;
            color: #333;
            outline: none;
            background-color: #fff;
            border: 1px solid #d3d6d9;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 0.8px;
            transition: all 0.25s ease;
        }

        .form-container input:focus {
            border: 1px solid #435ebe;
            outline: 0;
            box-shadow: 0 0 0 2px rgba(67, 94, 190, 0.2);
        }

        .form-container button {
            background: #435ebe;
            color: #fff;
            display: block;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            outline: none;
            font-size: 18px;
            letter-spacing: 1.5px;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
            margin-top: 1rem;
            border: 1px solid #d3d6d9;
            transition: transform 0.2s ease;
        }

        .form-container button:hover {
            transform: scale(1.05);
        }

        .register-forget {
            margin: 1rem 0;
            display: flex;
            justify-content: space-between;
            color: #333;
        }

        .register-forget a {
            color: #435eab;
            text-decoration: none;
            font-size: .9rem;
        }

        .register-forget a:hover {
            text-decoration: underline;
        }

        .error {
            color: #ff0000;
            margin-bottom: 1rem;
            font-size: 0.9rem;
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
        body.bg-dark .form-container {
            background: #1A233A;
            border-color: #4a5e8c;
        }

        body.bg-dark .form-container h1,
        body.bg-dark .register-forget {
            color: #FFFFFF;
        }

        body.bg-dark .form-container input {
            background: #2A3A5A;
            border-color: #4a5e8c;
            color: #ffffff;
        }

        body.bg-dark .form-container input:focus {
            border-color: #3B7DDD;
            box-shadow: 0 0 0 2px rgba(59, 125, 221, 0.2);
        }

        body.bg-dark .form-container button {
            border-color: #4a5e8c;
            background: #3B7DDD;
        }

        body.bg-dark .register-forget a {
            color: #3B7DDD;
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
            .form-container {
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
        <div class="login-container">
            <div class="form-container">
                <h1>Login</h1>
                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Submit</button>
                </form>
                <div class="register-forget">
                    <a href="signup.php">Sign Up</a>
                    <a href="#">Forgot Password</a>
                </div>
            </div>
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
                root.style.setProperty('--color', '#333');
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