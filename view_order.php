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

$error = '';
$order = null;
$order_items = [];
if (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];

    // Fetch order details
    $stmt = $conn->prepare("SELECT o.*, u.username
                            FROM orders o
                            LEFT JOIN users u 
                            ON o.user_id = u.id 
                            WHERE o.id = ?"); // ager user delete ho gaya hai to value null show ho gi lekin order record zrur show ho ga
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if ($order) {
        // Check if order_items table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'order_items'");
        if ($table_check->num_rows > 0) {
            // Fetch order items
            $stmt = $conn->prepare("SELECT oi.*, p.name
                                    FROM order_items oi 
                                    LEFT JOIN products p 
                                    ON oi.product_id = p.id 
                                    WHERE oi.order_id = ?"); // ager product delete ho gaya hai tab bhi data show ho ga lekin product null show ho ga
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $order_items[] = $row;
            }
            $stmt->close();
        } else {
            $error = "Order items table does not exist. Please contact the administrator.";
        }
    } else {
        $error = "Order not found.";
    }
} else {
    $error = "Invalid order ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order - My Store</title>
    <link rel="shortcut icon" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/svg/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/iconly.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background-color: #f2f7ff;}
        .card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }
        .table-responsive {
            vertical-align: middle;
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
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav ms-auto mb-lg-0">
                                <li class="nav-item dropdown me-3">
                                    <a class="nav-link active dropdown-toggle text-gray-600" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-bell bi-sub fs-4"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li><h6 class="dropdown-header">Notifications</h6></li>
                                        <li><a class="dropdown-item">No notifications</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown me-1">
                                    <a class="nav-link active dropdown-toggle text-gray-600" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-person-circle fs-4"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li><h6 class="dropdown-header">Admin</h6></li>
                                        <li><a class="dropdown-item" href="admin_logout.php">Logout</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>

            <div class="page-heading">
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3><i class="bi bi-cart"></i> View Order</h3>
                            <p class="text-subtitle text-muted">Order details</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="admin_orders.php">Orders</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">View</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <section class="section">
                    <div style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border: none;" class="card">
                        <div class="card-header">
                            <h4 class="card-title">Order #<?php echo $order ? htmlspecialchars($order['id']) : 'N/A'; ?></h4>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php elseif ($order): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Order Information</h5>
                                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username'] ?: 'N/A'); ?></p>
                                        <p><strong>Total:</strong> Rs. <?php echo number_format($order['total'] ?? 0, 2); ?></p>
                                        <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                                        <p><strong>Date:</strong> <?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></p>
                                    </div>
                                </div>
                                <h5 class="mt-4">Order Items</h5>
                                <?php if (empty($order_items)): ?>
                                    <div class="alert alert-info">No items found for this order.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order_items as $item): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($item['name'] ?: 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                                        <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                                        <td>Rs. <?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-danger">Unable to load order details.</div>
                            <?php endif; ?>
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
</body>
</html>
<?php $conn->close(); ?>