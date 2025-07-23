<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = $conn->real_escape_string($_POST['name']);
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $target_dir = "categories/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image_name = time() . '_' . basename($_FILES["category_image"]["name"]);
        $target_file = $target_dir . $image_name;
        
        // Validate image
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        
        if (!in_array($imageFileType, $allowed_types)) {
            $error = 'Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.';
        } elseif ($_FILES["category_image"]["size"] > 15000000) { // 15MB limit
            $error = 'Image size exceeds 15MB limit.';
        } else {
            if (move_uploaded_file($_FILES["category_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                $error = 'Failed to upload image. Check server permissions.';
            }
        }
    }
    
    if (!$error) {
        $sql = "INSERT INTO categories (name, image_path) VALUES ('$name', '$image_path')";
        if ($conn->query($sql)) {
            $_SESSION['toast_message'] = 'Category added successfully';
        } else {
            $error = 'Failed to add category';
        }
    }
    
    header("Location: admin_categories.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    
    // Fetch existing category to get the current image path
    $existing_query = $conn->query("SELECT image_path FROM categories WHERE id = $id");
    $existing_row = $existing_query->fetch_assoc();
    $image_path = $existing_row['image_path'];
    
    // Handle image upload if a new image is provided
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $target_dir = "categories/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image_name = time() . '_' . basename($_FILES["category_image"]["name"]);
        $target_file = $target_dir . $image_name;
        
        // Validate image
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        
        if (!in_array($imageFileType, $allowed_types)) {
            $error = 'Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.';
        } elseif ($_FILES["category_image"]["size"] > 15000000) { // 15MB limit
            $error = 'Image size exceeds 15MB limit.';
        } else {
            if (move_uploaded_file($_FILES["category_image"]["tmp_name"], $target_file)) {
                // Delete the old image if it exists
                if (!empty($image_path) && file_exists($image_path)) {
                    unlink($image_path);
                }
                $image_path = $target_file;
            } else {
                $error = 'Failed to upload image. Check server permissions.';
            }
        }
    }
    
    if (!$error) {
        $sql = "UPDATE categories SET name = '$name', image_path = '$image_path' WHERE id = $id";
        if ($conn->query($sql)) {
            $_SESSION['toast_message'] = 'Category updated successfully';
        } else {
            $error = 'Failed to update category';
        }
    }
    
    header("Location: admin_categories.php");
    exit;
}

if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];

    // Check if the category is associated with any products
    $check_query = $conn->query("SELECT COUNT(*) as count FROM products WHERE category_id = $id");
    $row = $check_query->fetch_assoc();
    if ($row['count'] > 0) {
        $_SESSION['toast_message'] = 'Cannot delete category because it is associated with ' . $row['count'] . ' product(s)';
    } else {
        // Get image path to delete the file
        $image_query = $conn->query("SELECT image_path FROM categories WHERE id = $id");
        $image_row = $image_query->fetch_assoc();
        if ($image_row['image_path'] && file_exists($image_row['image_path'])) {
            unlink($image_row['image_path']);
        }
        
        if ($conn->query("DELETE FROM categories WHERE id = $id")) {
            $_SESSION['toast_message'] = 'Category deleted successfully';
        } else {
            $_SESSION['toast_message'] = 'Failed to delete category';
        }
    }
    header("Location: admin_categories.php");
    exit;
}

$result = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - My Store</title>
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
        /* Card styling with increased width */
        .category-card {
            position: relative;
            width: 100%;
            max-width: 400px; /* Increased from 300px to 400px */
            height: 300px; /* Increased height to maintain aspect ratio */
            background-size: cover;
            background-position: center;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            margin: 15px auto;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .category-card .card-id-circle {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 40px;
            height: 40px;
            background-color: #1e3a8a;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .category-card .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
        }
        .category-card .card-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 12px;
            background: rgba(255, 255, 255, 0.85);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .category-card .card-name {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }
        .category-card .delete-btn, .category-card .edit-btn {
            padding: 6px 12px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 5px;
            transition: background-color 0.3s ease;
        }
        .category-card .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .category-card .delete-btn:hover {
            background-color: #c82333;
        }
        .category-card .edit-btn {
            background-color: #007bff;
            color: white;
        }
        .category-card .edit-btn:hover {
            background-color: #0056b3;
        }
        .no-image {
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 16px;
            height: 100%;
        }
        /* Ensure 3 cards per row with wider width */
        @media (min-width: 768px) {
            .category-card {
                flex: 0 0 33.333333%;
                max-width: 400px; /* Match the increased card width */
            }
            .row .col-md-4 {
                flex: 0 0 33.333333%;
                max-width: 400px; /* Ensure columns match card width */
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
                            <h3><i class="bi bi-list"></i> Manage Categories</h3>
                            <p class="text-subtitle text-muted">Add or remove categories</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Categories</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <section class="section">
                    <div style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border: none;" class="card">
                        <div class="card-header">
                            <h4 class="card-title">Category Management</h4>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="bi bi-plus-circle"></i> Add Category
                            </button>
                        </div>
                    </div>

                    <!-- Add Category Modal -->
                    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <form method="POST" class="row g-3" enctype="multipart/form-data">
                                        <div class="col-12">
                                            <label for="name" class="form-label">Category Name</label>
                                            <input type="text" name="name" id="name" class="form-control" placeholder="Category Name" required>
                                        </div>
                                        <div class="col-12">
                                            <label for="category_image" class="form-label">Category Image (Max 15MB, JPG/PNG/GIF)</label>
                                            <input type="file" name="category_image" id="category_image" class="form-control" accept="image/*">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="add_category" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Category</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Category Modal -->
                    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <form method="POST" class="row g-3" enctype="multipart/form-data">
                                        <input type="hidden" name="id" id="edit_category_id">
                                        <div class="col-12">
                                            <label for="edit_name" class="form-label">Category Name</label>
                                            <input type="text" name="name" id="edit_name" class="form-control" placeholder="Category Name" required>
                                        </div>
                                        <div class="col-12">
                                            <label for="edit_category_image" class="form-label">Category Image (Max 15MB, JPG/PNG/GIF)</label>
                                            <input type="file" name="category_image" id="edit_category_image" class="form-control" accept="image/*">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="edit_category" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border: none;" class="card">
                        <div class="card-header">
                            <h4 class="card-title">Category List</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <div class="col-12 col-md-4">
                                        <div class="category-card" style="background-image: url('<?php echo !empty($row['image_path']) && file_exists($row['image_path']) ? htmlspecialchars($row['image_path']) : ''; ?>');">
                                            <?php if (empty($row['image_path']) || !file_exists($row['image_path'])): ?>
                                                <div class="no-image">No Image</div>
                                            <?php else: ?>
                                                <div class="card-overlay"></div>
                                            <?php endif; ?>
                                            <div class="card-id-circle">#<?php echo $row['id']; ?></div>
                                            <div class="card-content">
                                                <span class="card-name"><?php echo htmlspecialchars($row['name']); ?></span>
                                                <div>
                                                    <button class="edit-btn edit-category-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editCategoryModal" 
                                                            data-category-id="<?php echo $row['id']; ?>" 
                                                            data-category-name="<?php echo htmlspecialchars($row['name']); ?>">
                                                            <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <button class="delete-btn delete-category-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteCategoryModal" 
                                                            data-category-id="<?php echo $row['id']; ?>" 
                                                            data-category-name="<?php echo htmlspecialchars($row['name']); ?>">
                                                            <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteCategoryModalLabel">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete the category "<span id="category-name"></span>"?
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
            const deleteButtons = document.querySelectorAll('.delete-category-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const categoryId = this.getAttribute('data-category-id');
                    const categoryName = this.getAttribute('data-category-name');
                    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
                    const categoryNameSpan = document.getElementById('category-name');

                    categoryNameSpan.textContent = categoryName;
                    confirmDeleteBtn.setAttribute('href', `?delete_id=${categoryId}`);
                });
            });

            // Handle edit button click to populate modal
            const editButtons = document.querySelectorAll('.edit-category-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const categoryId = this.getAttribute('data-category-id');
                    const categoryName = this.getAttribute('data-category-name');
                    const editCategoryIdInput = document.getElementById('edit_category_id');
                    const editNameInput = document.getElementById('edit_name');

                    editCategoryIdInput.value = categoryId;
                    editNameInput.value = categoryName;
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>