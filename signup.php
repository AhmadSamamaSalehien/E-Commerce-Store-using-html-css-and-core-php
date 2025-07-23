<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $result = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($result->num_rows > 0) {
        $error = "Username already exists";
    } else {
        $conn->query("INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')");
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - My Store</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
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

        /* Login Form Styles (match login.php) */
        .login-container {
            position: relative;
            width: 22.2rem;
        }

        .form-container {
            border: 1px solid hsla(0, 0%, 65%, 0.158);
            box-shadow: 0 0 36px 1px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            backdrop-filter: blur(20px);
            z-index: 99;
            padding: 2rem;
            background: #f8f9fa; /* Mazer light mode background */
            transition: all 0.2s ease;
        }

        .form-container:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .login-container form input {
            display: block;
            padding: 14.5px;
            width: 100%;
            margin: 2rem 0;
            color: #343a40; /* Mazer dark text */
            outline: none;
            background-color: #9191911f;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            letter-spacing: 0.8px;
            font-size: 15px;
            backdrop-filter: blur(15px);
        }

        .login-container form input:focus {
            box-shadow: 0 0 16px 1px rgba(0, 0, 0, 0.2);
            animation: wobble 0.3s ease-in;
        }

        .login-container form button {
            background-color: #435ebe; /* Mazer primary color */
            color: #FFFFFF;
            display: block;
            padding: 13px;
            border-radius: 5px;
            outline: none;
            font-size: 18px;
            letter-spacing: 1.5px;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
            margin-bottom: 2rem;
            transition: all 0.1s ease-in-out;
            border: none;
        }

        .login-container form button:hover {
            box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.15);
            transform: scale(1.02);
        }

        .circle {
            width: 8rem;
            height: 8rem;
            background: #435ebe; /* Mazer primary color */
            border-radius: 50%;
            position: absolute;
        }

        .circle-one {
            top: 0;
            left: 0;
            z-index: -1;
            transform: translate(-45%, -45%);
        }

        .circle-two {
            bottom: 0;
            right: 0;
            z-index: -1;
            transform: translate(45%, 45%);
        }

        .register-forget {
            margin: 1rem 0;
            display: flex;
            justify-content: space-between;
        }

        .opacity {
            opacity: 0.6;
            color: #343a40; /* Mazer dark text */
        }

        .theme-btn-container {
            display: none; /* Remove theme switcher buttons */
        }

        .error-message {
            color: #ff0000;
            margin-bottom: 1rem;
            text-align: center;
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
            background: #1A233A !important; /* Match navbar background in dark mode */
        }

        body.bg-dark .form-container {
            background: #1A233A;
            border-color: #4a5e8c;
        }

        body.bg-dark .login-container form input {
            color: #FFFFFF;
            background-color: #2c3e50; /* Darker input background */
        }

        body.bg-dark .login-container form button {
            background: #3B7DDD;
            border-color: #4a5e8c;
        }

        body.bg-dark .opacity {
            color: #FFFFFF;
        }

        body.bg-dark .error-message {
            color: #ff6666; /* Lighter red for visibility */
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

        @keyframes wobble {
            0% { transform: scale(1.025); }
            25% { transform: scale(1); }
            75% { transform: scale(1.025); }
            100% { transform: scale(1); }
        }

        /* Responsive Design */
        @media (max-width: 799px) {
            .login-container {
                width: 18rem;
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

    <section class="container">
        <div class="login-container">
            <div class="circle circle-one"></div>
            <div class="form-container">
                <h1 class="opacity">SIGN UP</h1>
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="username" placeholder="USERNAME" required />
                    <input type="email" name="email" placeholder="EMAIL" required />
                    <input type="password" name="password" placeholder="PASSWORD" required />
                    <button type="submit" class="opacity">SUBMIT</button>
                </form>
                <div class="register-forget opacity">
                    <a href="login.php">LOGIN</a>
                    <a href="#">FORGOT PASSWORD</a>
                </div>
            </div>
            <div class="circle circle-two"></div>
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