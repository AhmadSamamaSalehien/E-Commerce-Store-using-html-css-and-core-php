<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_categories = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];
$pending_products = $conn->query("SELECT COUNT(*) AS count FROM products WHERE status = 'pending'")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$completed_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE status = 'completed'")->fetch_assoc()['count'];
$cancelled_orders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE status = 'cancelled'")->fetch_assoc()['count'];
$total_brands = $conn->query("SELECT COUNT(*) AS count FROM brands")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total) as revenue FROM orders")->fetch_assoc()['revenue'] ?? 0;

$monthly_total_orders = $conn->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM orders WHERE YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at)");
$monthly_pending_orders = $conn->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM orders WHERE status = 'pending' AND YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at)");
$monthly_completed_orders = $conn->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM orders WHERE status = 'completed' AND YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at)");
$monthly_cancelled_orders = $conn->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM orders WHERE status = 'cancelled' AND YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at)");

// Initialize arrays for months and order counts
$months = array_map(function($m) { return date('M', mktime(0, 0, 0, $m, 1)); }, range(1, 12));
$total_order_counts = array_fill(0, 12, 0);
$pending_order_counts = array_fill(0, 12, 0);
$completed_order_counts = array_fill(0, 12, 0);
$cancelled_order_counts = array_fill(0, 12, 0);

// Populate total orders
while ($row = $monthly_total_orders->fetch_assoc()) {
    $total_order_counts[$row['month'] - 1] = $row['count'];
}

// Populate pending orders
while ($row = $monthly_pending_orders->fetch_assoc()) {
    $pending_order_counts[$row['month'] - 1] = $row['count'];
}

// Populate completed orders
while ($row = $monthly_completed_orders->fetch_assoc()) {
    $completed_order_counts[$row['month'] - 1] = $row['count'];
}

// Populate cancelled orders
while ($row = $monthly_cancelled_orders->fetch_assoc()) {
    $cancelled_order_counts[$row['month'] - 1] = $row['count'];
}

$recent_orders = $conn->query("SELECT o.*, u.email
                               FROM orders o
                               JOIN users u 
                               ON o.user_id = u.id 
                               ORDER BY o.created_at 
                               DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - My Store</title>
    <link rel="shortcut icon" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/svg/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/iconly.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f2f7ff;
        }
        .card {
            border: none !important;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            padding: 15px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .card-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .text-primary { color: #0d6efd; }
        .text-success { color: #198754; }
        .text-warning { color: #ffc107; }
        .text-danger { color: #dc3545; }
        .text-info { color: #0dcaf0; }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        #main > header {
            display: none;
        }
        @media (max-width: 767.98px) {
            #main > header {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div id="app">
        
        <?php include 'sidebar.php'; ?>

        <div id="main">
            <header class="mb-3">
                <nav class="navbar navbar-expand navbar-light navbar-top">
                    <div class="container-fluid">
                        <a href="#" class="burger-btn d-block">
                            <i class="bi bi-justify fs-3"></i>
                        </a>
                    </div>
                </nav>
            </header>

            <div class="page-heading">
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3><i class="bi bi-speedometer2"></i> Dashboard</h3>
                            <p class="text-subtitle text-muted">Overview of store performance</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <section class="section">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card" style="background-color: rgba(13, 110, 253, 0.1);">
                                <div class="card-body">
                                    <div class="card-icon text-primary">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <h6 class="text-muted">Total Users</h6>
                                    <h3 class="mb-0"><?php echo $total_users; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card" style="background-color: rgba(25, 135, 84, 0.1);">
                                <div class="card-body">
                                    <div class="card-icon text-success">
                                        <i class="bi bi-box"></i>
                                    </div>
                                    <h6 class="text-muted">Total Products</h6>
                                    <h3 class="mb-0"><?php echo $total_products; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card" style="background-color: rgba(255, 193, 7, 0.1);">
                                <div class="card-body">
                                    <div class="card-icon text-warning">
                                        <i class="bi bi-hourglass"></i>
                                    </div>
                                    <h6 class="text-muted">Pending Products</h6>
                                    <h3 class="mb-0"><?php echo $pending_products; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card" style="background-color: rgba(13, 202, 240, 0.1);">
                                <div class="card-body">
                                    <div class="card-icon text-info">
                                        <i class="bi bi-cart"></i>
                                    </div>
                                    <h6 class="text-muted">Total Orders</h6>
                                    <h3 class="mb-0"><?php echo $total_orders; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card" style="background-color: rgba(255, 193, 7, 0.1);">
                                <div class="card-body">
                                    <div class="card-icon text-warning">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                    <h6 class="text-muted">Pending Orders</h6>
                                    <h3 class="mb-0"><?php echo $pending_orders; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card" style="background-color: rgba(25, 135, 84, 0.1);">
                                <div class="card-body">
                                    <div class="card-icon text-success">
                                        <i class="bi bi-check"></i>
                                    </div>
                                    <h6 class="text-muted">Completed Orders</h6>
                                    <h3 class="mb-0"><?php echo $completed_orders; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card" style="background-color: rgba(220, 53, 69, 0.1);">
                                <div class="card-body">
                                    <div class="card-icon text-danger">
                                        <i class="bi bi-x"></i>
                                    </div>
                                    <h6 class="text-muted">Cancelled Orders</h6>
                                    <h3 class="mb-0"><?php echo $cancelled_orders; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card" style="background-color: rgba(13, 202, 240, 0.1);">
                                <div class="card-body">
                                    <div class="card-icon text-info">
                                        <i class="bi bi-tag"></i>
                                    </div>
                                    <h6 class="text-muted">Total Brands</h6>
                                    <h3 class="mb-0"><?php echo $total_brands; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card" style="background-color: rgba(220, 53, 69, 0.1);">
                                <div class="card-body">
                                    <div class="card-icon text-danger">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                    <h6 class="text-muted">Total Revenue</h6>
                                    <h3 class="mb-0">Rs. <?php echo number_format($total_revenue, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card" style="background-color: rgba(13, 110, 253, 0.1);">
                                <div class="card-body">
                                    <div class="card-icon text-primary">
                                        <i class="bi bi-list"></i>
                                    </div>
                                    <h6 class="text-muted">Total Categories</h6>
                                    <h3 class="mb-0"><?php echo $total_categories; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Orders Chart -->
                    <div class="card">
                        <div class="card-header">
                            <h4>Monthly Orders (<?php echo date('Y'); ?>)</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyOrdersChart"></canvas>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4>Recent Orders</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>User Email</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $recent_orders->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo $row['email']; ?></td>
                                                <td>$<?php echo number_format($row['total'], 2); ?></td>
                                                <td><?php echo ucfirst($row['status']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/components/dark.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/initTheme.js"></script>
    <!-- Chart.js Script -->
    <script>
        const ctx = document.getElementById('monthlyOrdersChart').getContext('2d');
        const monthlyOrdersChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [
                    {
                        label: 'Total Orders',
                        data: <?php echo json_encode($total_order_counts); ?>,
                        backgroundColor: 'rgba(13, 110, 253, 0.5)', // Blue
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pending Orders',
                        data: <?php echo json_encode($pending_order_counts); ?>,
                        backgroundColor: 'rgba(255, 193, 7, 0.5)', // Yellow
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Completed Orders',
                        data: <?php echo json_encode($completed_order_counts); ?>,
                        backgroundColor: 'rgba(25, 135, 84, 0.5)', // Green
                        borderColor: 'rgba(25, 135, 84, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Cancelled Orders',
                        data: <?php echo json_encode($cancelled_order_counts); ?>,
                        backgroundColor: 'rgba(220, 53, 69, 0.5)', // Red
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Orders'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>