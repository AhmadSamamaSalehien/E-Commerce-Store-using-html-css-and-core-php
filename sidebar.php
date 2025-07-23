<?php
// No session_start(); parent file handles it
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
?>

<div id="sidebar" class="active">
    <div style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border: none;" class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="admin_dashboard.php"><h4>👤</h4></a>
                </div>
                <div class="d-flex align-items-center">
                    <button id="theme-toggle" class="btn btn-sm btn-outline-secondary me-2" title="Toggle Theme">
                        <i class="bi bi-moon-stars-fill"></i>
                    </button>
                    <div class="sidebar-toggler x">
                        <a href="#" class="sidebar-hide"><i class="bi bi-x"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Menu</li>
                <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
                    <a href="admin_dashboard.php" class="sidebar-link">
                        <i class="bi bi-grid-fill text-primary"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>">
                    <a href="admin_users.php" class="sidebar-link">
                        <i class="bi bi-people text-primary"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_products.php' ? 'active' : ''; ?>">
                    <a href="admin_products.php" class="sidebar-link">
                        <i class="bi bi-box text-success"></i>
                        <span>Manage Products</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_brands.php' ? 'active' : ''; ?>">
                    <a href="admin_brands.php" class="sidebar-link">
                        <i class="bi bi-tag text-info"></i>
                        <span>Manage Brands</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_categories.php' ? 'active' : ''; ?>">
                    <a href="admin_categories.php" class="sidebar-link">
                        <i class="bi bi-list text-primary"></i>
                        <span>Manage Categories</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_sizes.php' ? 'active' : ''; ?>">
                    <a href="admin_sizes.php" class="sidebar-link">
                        <i class="bi bi-arrows-angle-contract text-warning"></i>
                        <span>Manage Sizes</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_colors.php' ? 'active' : ''; ?>">
                    <a href="admin_colors.php" class="sidebar-link">
                        <i class="bi bi-palette text-info"></i>
                        <span>Manage Colors</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'active' : ''; ?>">
                    <a href="admin_orders.php" class="sidebar-link">
                        <i class="bi bi-cart text-success"></i>
                        <span>Manage Orders</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="admin_logout.php" class="sidebar-link">
                        <i class="bi bi-box-arrow-right text-danger"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
    .sidebar-item.active .sidebar-link i {
        color: white !important;
    }
    #theme-toggle {
        padding: 5px 10px;
        font-size: 14px;
    }
    #theme-toggle .bi-sun-fill,
    #theme-toggle .bi-moon-stars-fill {
        font-size: 16px;
    }
</style>

<script>
    // Theme toggle functionality
    document.addEventListener('DOMContentLoaded', function () {
        const html = document.documentElement;
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = themeToggle.querySelector('i');

        // Function to update theme and icon
        function updateTheme(isDark) {
            if (isDark) {
                html.classList.add('dark');
                themeIcon.classList.remove('bi-moon-stars-fill');
                themeIcon.classList.add('bi-sun-fill');
                localStorage.setItem('theme', 'dark');
            } else {
                html.classList.remove('dark');
                themeIcon.classList.remove('bi-sun-fill');
                themeIcon.classList.add('bi-moon-stars-fill');
                localStorage.setItem('theme', 'light');
            }
        }

        // Load saved theme from localStorage or default to light
        const savedTheme = localStorage.getItem('theme') || 'light';
        updateTheme(savedTheme === 'dark');

        // Toggle theme on button click
        themeToggle.addEventListener('click', function () {
            const isDark = !html.classList.contains('dark');
            updateTheme(isDark);

            // Trigger Mazer template's theme change (if needed)
            if (typeof window.initTheme === 'function') {
                window.initTheme(); // Call Mazer's theme initializer if available
            }
        });
    });
</script>
