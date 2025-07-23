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
$success = '';
$alert_script = '';

if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
    $alert_script = "<script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastEl = document.createElement('div');
            toastEl.className = 'toast align-items-center text-white bg-success border-0';
            toastEl.innerHTML = `
                <div class=\"d-flex\">
                    <div class=\"toast-body\">
                        $success
                    </div>
                    <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                </div>
            `;
            document.getElementById('toast-container').appendChild(toastEl);
            var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
            toast.show();
        });
    </script>";
}

// Add new product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $brand_id = (int)$_POST['brand_id'];
    $category_id = (int)$_POST['category_id'];
    $size_id = (int)$_POST['size_id'];
    $color_ids = isset($_POST['color_id']) ? implode(',', array_map('intval', $_POST['color_id'])) : '';
    $stock = (int)$_POST['stock'];
    $status = $_POST['status'];

    // Validate inputs
    if (empty($name) || $price <= 0 || $stock < 0 || !in_array($status, ['drafted', 'published'])) {
        $error = "Please fill all required fields with valid data.";
        $alert_script = "<script>
            document.addEventListener('DOMContentLoaded', function() {
                var toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center text-white bg-danger border-0';
                toastEl.innerHTML = `
                    <div class=\"d-flex\">
                        <div class=\"toast-body\">
                            $error
                        </div>
                        <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                    </div>
                `;
                document.getElementById('toast-container').appendChild(toastEl);
                var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
                toast.show();
            });
        </script>";
    } else {
        // Handle single image upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024;
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $upload_dir = 'Uploads/';
            $upload_path = $upload_dir . $file_name;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (!in_array($file_type, $allowed_types)) {
                $error = "Single image must be JPEG, PNG, or GIF.";
            } elseif ($file_size > $max_size) {
                $error = "Single image size exceeds 5MB.";
            } elseif (!move_uploaded_file($file_tmp, $upload_path)) {
                $error = "Error uploading single image.";
            } else {
                $image_path = $upload_path;
            }
        } else {
            $error = "Single image is required.";
        }

        // Handle multiple images upload
        $image_paths = [];
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024;
            $upload_dir = 'Uploads/';

            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                    $file_type = $_FILES['images']['type'][$key];
                    $file_size = $_FILES['images']['size'][$key];
                    $file_tmp = $_FILES['images']['tmp_name'][$key];
                    $file_name = uniqid() . '_' . $name;
                    $upload_path = $upload_dir . $file_name;

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    if (!in_array($file_type, $allowed_types)) {
                        $error = "Multiple images must be JPEG, PNG, or GIF.";
                        break;
                    } elseif ($file_size > $max_size) {
                        $error = "Multiple image size exceeds 5MB.";
                        break;
                    } elseif (!move_uploaded_file($file_tmp, $upload_path)) {
                        $error = "Error uploading multiple images.";
                        break;
                    } else {
                        $image_paths[] = $upload_path;
                    }
                }
            }
        }
        $images = implode(',', $image_paths);

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, brand_id, category_id, size_id, color_id, stock, status, image, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdiiiissss", $name, $description, $price, $brand_id, $category_id, $size_id, $color_ids, $stock, $status, $image_path, $images);
            if ($stmt->execute()) {
                $success = "Product added successfully.";
                $alert_script = "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var toastEl = document.createElement('div');
                        toastEl.className = 'toast align-items-center text-white bg-success border-0';
                        toastEl.innerHTML = `
                            <div class=\"d-flex\">
                                <div class=\"toast-body\">
                                    $success
                                </div>
                                <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                            </div>
                        `;
                        document.getElementById('toast-container').appendChild(toastEl);
                        var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
                        toast.show();
                        setTimeout(() => {
                            window.location.href = 'admin_products.php';
                        }, 2000);
                    });
                </script>";
            } else {
                $error = "Error adding product: " . $conn->error;
                $alert_script = "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var toastEl = document.createElement('div');
                        toastEl.className = 'toast align-items-center text-white bg-danger border-0';
                        toastEl.innerHTML = `
                            <div class=\"d-flex\">
                                <div class=\"toast-body\">
                                    $error
                                </div>
                                <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                            </div>
                        `;
                        document.getElementById('toast-container').appendChild(toastEl);
                        var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
                        toast.show();
                    });
                </script>";
            }
            $stmt->close();
        } else {
            $alert_script = "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    var toastEl = document.createElement('div');
                    toastEl.className = 'toast align-items-center text-white bg-danger border-0';
                    toastEl.innerHTML = `
                        <div class=\"d-flex\">
                            <div class=\"toast-body\">
                                $error
                            </div>
                            <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                        </div>
                    `;
                    document.getElementById('toast-container').appendChild(toastEl);
                    var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
                    toast.show();
                });
            </script>";
        }
    }
}

// Update product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $brand_id = (int)$_POST['brand_id'];
    $category_id = (int)$_POST['category_id'];
    $size_id = (int)$_POST['size_id'];
    $color_ids = isset($_POST['color_id']) ? implode(',', array_map('intval', $_POST['color_id'])) : '';
    $stock = (int)$_POST['stock'];
    $status = $_POST['status'];

    // Validate inputs
    if (empty($name) || $price <= 0 || $stock < 0 || !in_array($status, ['drafted', 'published'])) {
        $error = "Please fill all required fields with valid data.";
        $alert_script = "<script>
            document.addEventListener('DOMContentLoaded', function() {
                var toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center text-white bg-danger border-0';
                toastEl.innerHTML = `
                    <div class=\"d-flex\">
                        <div class=\"toast-body\">
                            $error
                        </div>
                        <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                    </div>
                `;
                document.getElementById('toast-container').appendChild(toastEl);
                var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
                toast.show();
            });
        </script>";
    } else {
        // Fetch existing product data
        $stmt = $conn->prepare("SELECT image, images FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_product = $result->fetch_assoc();
        $stmt->close();

        // Handle single image upload
        $image_path = $current_product['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024;
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $upload_dir = 'Uploads/';
            $upload_path = $upload_dir . $file_name;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (!in_array($file_type, $allowed_types)) {
                $error = "Single image must be JPEG, PNG, or GIF.";
            } elseif ($file_size > $max_size) {
                $error = "Single image size exceeds 5MB.";
            } elseif (!move_uploaded_file($file_tmp, $upload_path)) {
                $error = "Error uploading single image.";
            } else {
                $image_path = $upload_path;
                if (file_exists($current_product['image']) && $current_product['image'] != '') {
                    unlink($current_product['image']);
                }
            }
        }

        // Handle multiple images upload
        $image_paths = array_filter(explode(',', $current_product['images']));
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024;
            $upload_dir = 'Uploads/';

            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                    $file_type = $_FILES['images']['type'][$key];
                    $file_size = $_FILES['images']['size'][$key];
                    $file_tmp = $_FILES['images']['tmp_name'][$key];
                    $file_name = uniqid() . '_' . $name;
                    $upload_path = $upload_dir . $file_name;

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    if (!in_array($file_type, $allowed_types)) {
                        $error = "Multiple images must be JPEG, PNG, or GIF.";
                        break;
                    } elseif ($file_size > $max_size) {
                        $error = "Multiple image size exceeds 5MB.";
                        break;
                    } elseif (!move_uploaded_file($file_tmp, $upload_path)) {
                        $error = "Error uploading multiple images.";
                        break;
                    } else {
                        $image_paths[] = $upload_path;
                    }
                }
            }
        }
        $images = implode(',', array_filter($image_paths));

        if (!$error) {
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, brand_id = ?, category_id = ?, size_id = ?, color_id = ?, stock = ?, status = ?, image = ?, images = ? WHERE id = ?");
            $stmt->bind_param("ssdiiiissssi", $name, $description, $price, $brand_id, $category_id, $size_id, $color_ids, $stock, $status, $image_path, $images, $id);
            if ($stmt->execute()) {
                $success = "Product updated successfully.";
                $alert_script = "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var toastEl = document.createElement('div');
                        toastEl.className = 'toast align-items-center text-white bg-success border-0';
                        toastEl.innerHTML = `
                            <div class=\"d-flex\">
                                <div class=\"toast-body\">
                                    $success
                                </div>
                                <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                            </div>
                        `;
                        document.getElementById('toast-container').appendChild(toastEl);
                        var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
                        toast.show();
                        setTimeout(() => {
                            window.location.href = 'admin_products.php';
                        }, 2000);
                    });
                </script>";
            } else {
                $error = "Error updating product: " . $conn->error;
                $alert_script = "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var toastEl = document.createElement('div');
                        toastEl.className = 'toast align-items-center text-white bg-danger border-0';
                        toastEl.innerHTML = `
                            <div class=\"d-flex\">
                                <div class=\"toast-body\">
                                    $error
                                </div>
                                <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                            </div>
                        `;
                        document.getElementById('toast-container').appendChild(toastEl);
                        var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
                        toast.show();
                    });
                </script>";
            }
            $stmt->close();
        } else {
            $alert_script = "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    var toastEl = document.createElement('div');
                    toastEl.className = 'toast align-items-center text-white bg-danger border-0';
                    toastEl.innerHTML = `
                        <div class=\"d-flex\">
                            <div class=\"toast-body\">
                                $error
                            </div>
                            <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                        </div>
                    `;
                    document.getElementById('toast-container').appendChild(toastEl);
                    var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
                    toast.show();
                });
            </script>";
        }
    }
}

// Delete product
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Product deleted successfully.";
        error_log("Product deleted: ID $id"); // Debug log
        $alert_script = "<script>
            document.addEventListener('DOMContentLoaded', function() {
                var toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center text-white bg-success border-0';
                toastEl.innerHTML = `
                    <div class=\"d-flex\">
                        <div class=\"toast-body\">
                            $success
                        </div>
                        <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                    </div>
                `;
                document.getElementById('toast-container').appendChild(toastEl);
                var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
                toast.show();
                setTimeout(() => {
                    window.location.href = 'admin_products.php';
                }, 2000);
            });
        </script>";
    } else {
        $error = "Error deleting product: " . $conn->error;
        error_log("Delete error: " . $conn->error); // Debug log
        $alert_script = "<script>
            document.addEventListener('DOMContentLoaded', function() {
                var toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center text-white bg-danger border-0';
                toastEl.innerHTML = `
                    <div class=\"d-flex\">
                        <div class=\"toast-body\">
                            $error
                        </div>
                        <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                    </div>
                `;
                document.getElementById('toast-container').appendChild(toastEl);
                var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
                toast.show();
            });
        </script>";
    }
    $stmt->close();
}

$products = $conn->query("SELECT p.*, b.name as brand_name, c.name as category_name, s.name as size_name, col.name as color_name 
                          FROM products p 
                          LEFT JOIN brands b ON p.brand_id = b.id 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN sizes s ON p.size_id = s.id 
                          LEFT JOIN colors col ON p.color_id = col.id ORDER BY p.id DESC");
$brands = $conn->query("SELECT * FROM brands");
$categories = $conn->query("SELECT * FROM categories");
$sizes = $conn->query("SELECT * FROM sizes");
$colors = $conn->query("SELECT * FROM colors");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - My Store</title>
    <link rel="shortcut icon" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/svg/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/iconly.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background-color: #f2f7ff;}
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .image-preview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .select2-container .select2-selection--multiple {
            min-height: 38px;
        }
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        .dark .toast {
            background-color: #2d2d44 !important;
            color: #ddd !important;
            border: 1px solid #4a4a6a !important;
        }
        .dark .toast .toast-body {
            color: #ddd !important;
        }
        /* Hide navbar by default on medium and larger screens */
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
    <?php echo $alert_script; ?>
    <div id="toast-container"></div>
    <div id="app">
        <?php include 'sidebar.php'; ?>

        <div id="main">
            <!-- Navbar visible only on small screens -->
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
                            <h3><i class="bi bi-box"></i> Manage Products</h3>
                            <p class="text-subtitle text-muted">Add or manage products</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Products</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <section class="section">
                    <div style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border: none;" class="card">
                        <div class="card-header">
                            <h4 class="card-title">Products</h4>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                <i class="bi bi-plus-circle"></i> Add Product
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Brand</th>
                                            <th>Category</th>
                                            <th>Size</th>
                                            <th>Color</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                            <th>Image</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $products->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($row['brand_name'] ?: 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($row['category_name'] ?: 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($row['size_name'] ?: 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($row['color_name'] ?: 'N/A'); ?></td>
                                                <td><?php echo $row['stock']; ?></td>
                                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                                <td><img src="<?php echo $row['image']; ?>" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning edit-product-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editProductModal"
                                                            data-id="<?php echo $row['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                                            data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                                            data-price="<?php echo $row['price']; ?>"
                                                            data-brand_id="<?php echo $row['brand_id']; ?>"
                                                            data-category_id="<?php echo $row['category_id']; ?>"
                                                            data-size_id="<?php echo $row['size_id']; ?>"
                                                            data-color_id="<?php echo htmlspecialchars($row['color_id']); ?>"
                                                            data-stock="<?php echo $row['stock']; ?>"
                                                            data-status="<?php echo $row['status']; ?>"
                                                            data-image="<?php echo htmlspecialchars($row['image']); ?>"
                                                            data-images="<?php echo htmlspecialchars($row['images']); ?>">
                                                            <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-product-btn" data-id="<?php echo $row['id']; ?>" data-bs-toggle="modal" data-bs-target="#deleteProductModal"><i class="bi bi-trash"></i> Delete</button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Add Product Modal -->
                    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Product Name</label>
                                            <input type="text" name="name" id="name" class="form-control" placeholder="Product Name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="price" class="form-label">Price</label>
                                            <input type="number" name="price" id="price" class="form-control" placeholder="Price" step="0.01" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="brand_id" class="form-label">Brand</label>
                                            <select name="brand_id" id="brand_id" class="form-select" required>
                                                <option value="">Select a brand</option>
                                                <?php while ($brand = $brands->fetch_assoc()): ?>
                                                    <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                                <?php endwhile; $brands->data_seek(0); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="category_id" class="form-label">Category</label>
                                            <select name="category_id" id="category_id" class="form-select" required>
                                                <option value="">Select a category</option>
                                                <?php while ($category = $categories->fetch_assoc()): ?>
                                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                                <?php endwhile; $categories->data_seek(0); ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea name="description" id="description" class="form-control" placeholder="Description" required></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="size_id" class="form-label">Size</label>
                                            <select name="size_id" id="size_id" class="form-select" required>
                                                <option value="">Pick a size</option>
                                                <?php while ($size = $sizes->fetch_assoc()): ?>
                                                    <option value="<?php echo $size['id']; ?>"><?php echo htmlspecialchars($size['name']); ?></option>
                                                <?php endwhile; $sizes->data_seek(0); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="color_id" class="form-label">Colors</label>
                                            <select name="color_id[]" id="color_id" class="form-select" multiple>
                                                <?php while ($color = $colors->fetch_assoc()): ?>
                                                    <option value="<?php echo $color['id']; ?>"><?php echo htmlspecialchars($color['name']); ?></option>
                                                <?php endwhile; $colors->data_seek(0); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="image" class="form-label">Single Image</label>
                                            <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
                                            <small class="text-muted">JPEG, PNG, GIF (Max 5MB)</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="images" class="form-label">Multiple Images</label>
                                            <input type="file" name="images[]" id="images" class="form-control" accept="image/*" multiple>
                                            <small class="text-muted">JPEG, PNG, GIF (Max 5MB each)</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="stock" class="form-label">Stock</label>
                                            <input type="number" name="stock" id="stock" class="form-control" placeholder="Stock Quantity" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Status</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="status" id="drafted" value="drafted" checked>
                                                    <label class="form-check-label" for="drafted">Drafted</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="status" id="published" value="published">
                                                    <label class="form-check-label" for="published">Published</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="add_product" class="btn btn-primary">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Product Modal -->
                    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                                        <input type="hidden" name="id" id="edit_id">
                                        <div class="col-md-6">
                                            <label for="edit_name" class="form-label">Product Name</label>
                                            <input type="text" name="name" id="edit_name" class="form-control" placeholder="Product Name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_price" class="form-label">Price</label>
                                            <input type="number" name="price" id="edit_price" class="form-control" placeholder="Price" step="0.01" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_brand_id" class="form-label">Brand</label>
                                            <select name="brand_id" id="edit_brand_id" class="form-select" required>
                                                <option value="">Select a brand</option>
                                                <?php while ($brand = $brands->fetch_assoc()): ?>
                                                    <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                                <?php endwhile; $brands->data_seek(0); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_category_id" class="form-label">Category</label>
                                            <select name="category_id" id="edit_category_id" class="form-select" required>
                                                <option value="">Select a category</option>
                                                <?php while ($category = $categories->fetch_assoc()): ?>
                                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                                <?php endwhile; $categories->data_seek(0); ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="edit_description" class="form-label">Description</label>
                                            <textarea name="description" id="edit_description" class="form-control" placeholder="Description" rows="4" required></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_size_id" class="form-label">Size</label>
                                            <select name="size_id" id="edit_size_id" class="form-select" required>
                                                <option value="">Select a size</option>
                                                <?php while ($size = $sizes->fetch_assoc()): ?>
                                                    <option value="<?php echo $size['id']; ?>"><?php echo htmlspecialchars($size['name']); ?></option>
                                                <?php endwhile; $sizes->data_seek(0); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_color_id" class="form-label">Colors</label>
                                            <select name="color_id[]" id="edit_color_id" class="form-select" multiple>
                                                <?php while ($color = $colors->fetch_assoc()): ?>
                                                    <option value="<?php echo $color['id']; ?>"><?php echo htmlspecialchars($color['name']); ?></option>
                                                <?php endwhile; $colors->data_seek(0); ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_image" class="form-label">Single Image</label>
                                            <input type="file" name="image" id="edit_image" class="form-control" accept="image/*">
                                            <small class="text-muted">JPEG, PNG, GIF (Max 5MB). Leave blank to keep current image.</small>
                                            <div class="image-preview" id="edit_image_preview"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_images" class="form-label">Multiple Images</label>
                                            <input type="file" name="images[]" id="edit_images" class="form-control" accept="image/*" multiple>
                                            <small class="text-muted">JPEG, PNG, GIF (Max 5MB each). Leave blank to keep current images.</small>
                                            <div class="image-preview" id="edit_images_preview"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_stock" class="form-label">Stock</label>
                                            <input type="number" name="stock" id="edit_stock" class="form-control" placeholder="Stock Quantity" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Status</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="status" id="edit_drafted" value="drafted" required>
                                                    <label class="form-check-label" for="edit_drafted">Drafted</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="status" id="edit_published" value="published">
                                                    <label class="form-check-label" for="edit_published">Published</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Product Modal -->
                    <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteProductModalLabel">Confirm Deletion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete this product? This action cannot be undone.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/components/dark.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/initTheme.js"></script>
    <script>
        // File input label update for Add Product
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const label = input.nextElementSibling;
                if (input.files.length > 0) {
                    if (input.multiple) {
                        label.textContent = `${input.files.length} files selected`;
                    } else {
                        label.textContent = input.files[0].name;
                    }
                } else {
                    label.textContent = input.multiple ? 'No files chosen' : 'No file chosen';
                }
            });
        });

        // Edit Product Modal Population
        document.querySelectorAll('.edit-product-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const description = this.getAttribute('data-description');
                const price = this.getAttribute('data-price');
                const brand_id = this.getAttribute('data-brand_id');
                const category_id = this.getAttribute('data-category_id');
                const size_id = this.getAttribute('data-size_id');
                const color_ids = this.getAttribute('data-color_id').split(',').map(id => id.trim()).filter(id => id);
                const stock = this.getAttribute('data-stock');
                const status = this.getAttribute('data-status');
                const image = this.getAttribute('data-image');
                const images = this.getAttribute('data-images');

                document.getElementById('edit_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_price').value = price;
                document.getElementById('edit_brand_id').value = brand_id || '';
                document.getElementById('edit_category_id').value = category_id || '';
                document.getElementById('edit_size_id').value = size_id || '';
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_stock').value = stock;
                document.querySelector('#edit_drafted').checked = status === 'drafted';
                document.querySelector('#edit_published').checked = status === 'published';

                // Populate color multi-select
                const colorSelect = document.getElementById('edit_color_id');
                colorSelect.value = color_ids;

                // Populate image preview
                const imagePreview = document.getElementById('edit_image_preview');
                imagePreview.innerHTML = image ? `<img src="${image}" alt="Current Image">` : '';

                const imagesPreview = document.getElementById('edit_images_preview');
                imagesPreview.innerHTML = '';
                if (images) {
                    images.split(',').forEach(img => {
                        if (img) {
                            const imgElement = document.createElement('img');
                            imgElement.src = img;
                            imgElement.alt = 'Product Image';
                            imagesPreview.appendChild(imgElement);
                        }
                    });
                }
            });
        });

        // File input preview for Edit Product
        document.querySelectorAll('#edit_image, #edit_images').forEach(input => {
            input.addEventListener('change', function() {
                const preview = this.id === 'edit_image' ? document.getElementById('edit_image_preview') : document.getElementById('edit_images_preview');
                preview.innerHTML = '';
                if (this.files.length > 0) {
                    Array.from(this.files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.width = '100px';
                            img.style.height = '100px';
                            img.style.objectFit = 'cover';
                            img.style.borderRadius = '4px';
                            img.style.border = '1px solid #ddd';
                            preview.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    });
                }
            });
        });

        // Delete product confirmation with modal
        let productIdToDelete = null;
        document.querySelectorAll('.delete-product-btn').forEach(button => {
            button.addEventListener('click', function() {
                productIdToDelete = this.getAttribute('data-id');
                console.log('Delete button clicked for product ID:', productIdToDelete); // Debug log
            });
        });

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (productIdToDelete) {
                console.log('Confirmed deletion, redirecting to:', `admin_products.php?delete_id=${productIdToDelete}`); // Debug log
                window.location.href = `admin_products.php?delete_id=${productIdToDelete}`;
            }
        });

        // Initialize Select2 for multi-select
        $(document).ready(function() {
            $('#color_id').select2({
                placeholder: "Select colors",
                allowClear: true
            });
            $('#edit_color_id').select2({
                placeholder: "Select colors",
                allowClear: true
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>