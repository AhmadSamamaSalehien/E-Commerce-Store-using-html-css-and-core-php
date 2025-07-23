<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}





$associativeArray = [
    "student1" => [
        "name" => "Ali",
        "age" => 20,
        "marks" => [85, 90, 78]
    ],
    "student2" => [
        "name" => "Sara",
        "age" => 22,
        "marks" => [88, 92, 80]
    ]
];

// Accessing the multidimensional array
echo $associativeArray["student1"]["name"]; // Output: Ali
echo $associativeArray["student1"]["marks"][0]; // Output: 85
?>

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_brand'])) {
    $name = $conn->real_escape_string($_POST['name']);
    if ($conn->query("INSERT INTO brands (name) VALUES ('$name')")) {
        $_SESSION['toast_message'] = 'Brand added successfully';
    } else {
        $error = 'Failed to add brand';
    }
    header("Location: admin_brands.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_brand'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    
    $sql = "UPDATE brands SET name = '$name' WHERE id = $id";
    if ($conn->query($sql)) {
        $_SESSION['toast_message'] = 'Brand updated successfully';
    } else {
        $error = 'Failed to update brand';
    }
    
    header("Location: admin_brands.php");
    exit;
}

if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];

    // Check if the brand is associated with any products
    $check_query = $conn->query("SELECT COUNT(*) as count FROM products WHERE brand_id = $id");
    $row = $check_query->fetch_assoc();
    if ($row['count'] > 0) {
        $_SESSION['toast_message'] = 'Cannot delete brand because it is associated with ' . $row['count'] . ' product(s)';
    } else {
        if ($conn->query("DELETE FROM brands WHERE id = $id")) {
            $_SESSION['toast_message'] = 'Brand deleted successfully';
        } else {
            $_SESSION['toast_message'] = 'Failed to delete brand';
        }
    }
    header("Location: admin_brands.php");
    exit;
}

$result = $conn->query("SELECT * FROM brands");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Brands - My Store</title>
    <link rel="shortcut icon" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/svg/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/iconly.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background-color: #f2f7ff;}
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
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
                            <h3><i class="bi bi-tag"></i> Manage Brands</h3>
                            <p class="text-subtitle text-muted">Add, edit, or remove brands</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Brands</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <section class="section">
                    <div style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border: none;" class="card">
                        <div class="card-header">
                            <h4 class="card-title">Brand Management</h4>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                                <i class="bi bi-plus-circle"></i> Add Brand
                            </button>
                        </div>
                    </div>

                    <!-- Add Brand Modal -->
                    <div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addBrandModalLabel">Add New Brand</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <form method="POST" class="row g-3">
                                        <div class="col-12">
                                            <label for="name" class="form-label">Brand Name</label>
                                            <input type="text" name="name" id="name" class="form-control" placeholder="Brand Name" required>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="add_brand" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Brand</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Brand Modal -->
                    <div class="modal fade" id="editBrandModal" tabindex="-1" aria-labelledby="editBrandModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editBrandModalLabel">Edit Brand</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <form method="POST" class="row g-3">
                                        <input type="hidden" name="id" id="edit_brand_id">
                                        <div class="col-12">
                                            <label for="edit_name" class="form-label">Brand Name</label>
                                            <input type="text" name="name" id="edit_name" class="form-control" placeholder="Brand Name" required>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="edit_brand" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border: none;" class="card">
                        <div class="card-header">
                            <h4 class="card-title">Brand List</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo $row['name']; ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm edit-brand-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editBrandModal" 
                                                            data-brand-id="<?php echo $row['id']; ?>" 
                                                            data-brand-name="<?php echo htmlspecialchars($row['name']); ?>">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <button class="btn btn-danger btn-sm delete-brand-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteBrandModal" 
                                                            data-brand-id="<?php echo $row['id']; ?>" 
                                                            data-brand-name="<?php echo htmlspecialchars($row['name']); ?>">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteBrandModal" tabindex="-1" aria-labelledby="deleteBrandModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteBrandModalLabel">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete the brand "<span id="brand-name"></span>"?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <a href="#" id="confirm-delete-btn" class="btn btn-danger">Delete</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toast Notification -->
            <?php if (isset($_SESSION['toast_message'])): ?>
                <div class="toast-container">
                    <div class="toast align-items-center text-bg-<?php echo strpos($_SESSION['toast_message'], 'Failed') === false ? 'success' : 'danger'; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <?php echo $_SESSION['toast_message']; ?>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['toast_message']); ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/components/dark.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/initTheme.js"></script>
    <script>
        // Initialize and show toast if present
        document.addEventListener('DOMContentLoaded', function () {
            var toastEl = document.querySelector('.toast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl, {
                    autohide: true,
                    delay: 3000
                });
                toast.show();
            }

            // Handle delete button click to populate modal
            const deleteButtons = document.querySelectorAll('.delete-brand-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const brandId = this.getAttribute('data-brand-id');
                    const brandName = this.getAttribute('data-brand-name');
                    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
                    const brandNameSpan = document.getElementById('brand-name');

                    // Set the brand name in the modal
                    brandNameSpan.textContent = brandName;
                    // Set the delete URL in the confirm button
                    confirmDeleteBtn.setAttribute('href', `?delete_id=${brandId}`);
                });
            });

            // Handle edit button click to populate modal
            const editButtons = document.querySelectorAll('.edit-brand-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const brandId = this.getAttribute('data-brand-id');
                    const brandName = this.getAttribute('data-brand-name');
                    const editBrandIdInput = document.getElementById('edit_brand_id');
                    const editNameInput = document.getElementById('edit_name');

                    editBrandIdInput.value = brandId;
                    editNameInput.value = brandName;
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>