<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "root", "outstockdb");
if ($conn->connect_error) {
    die("Connection failed: " . mysqli_connect_error());
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

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $alert_script = "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    var toastEl = document.createElement('div');
                    toastEl.className = 'toast align-items-center text-white bg-success border-0';
                    toastEl.innerHTML = `
                        <div class=\"d-flex\">
                            <div class=\"toast-body\">
                                User deleted successfully.
                            </div>
                            <button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                        </div>
                    `;
                    document.getElementById('toast-container').appendChild(toastEl);
                    var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2000 });
                    toast.show();
                    setTimeout(() => {
                        window.location.href = 'admin_users.php?page=$current_page';
                    }, 2000);
                });
            </script>";
        } else {
            $error = "Error deleting user: " . $conn->error;
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
        $error = "Error preparing delete statement: " . $conn->error;
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

// Handle user addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
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
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
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
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
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
        // Check for duplicate username or email
        $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        if ($stmt) {
            $dummy_id = 0; // Since we're adding a new user, id check isn't needed
            $stmt->bind_param("ssi", $username, $email, $dummy_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = "Username or email already exists.";
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
            $error = "Error preparing statement: " . $conn->error;
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

        // Proceed with user addition if no error
        if (!$error) {
            // Handle profile picture upload
            $profile_picture = '';
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024;
                $file_type = $_FILES['profile_picture']['type'];
                $file_size = $_FILES['profile_picture']['size'];
                $file_tmp = $_FILES['profile_picture']['tmp_name'];
                $file_name = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
                $upload_dir = 'Uploads/';
                $upload_path = $upload_dir . $file_name;

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                if (!in_array($file_type, $allowed_types)) {
                    $error = "Profile picture must be JPEG, PNG, or GIF.";
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
                } elseif ($file_size > $max_size) {
                    $error = "Profile picture size exceeds 5MB.";
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
                } elseif (!move_uploaded_file($file_tmp, $upload_path)) {
                    $error = "Error uploading profile picture.";
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
                    $profile_picture = $upload_path;
                }
            }

            if (!$error) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_admin, profile_picture) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sssis", $username, $email, $hashed_password, $is_admin, $profile_picture);
                    if ($stmt->execute()) {
                        $success = "User added successfully.";
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
                                    window.location.href = 'admin_users.php?page=$current_page';
                                }, 2000);
                            });
                        </script>";
                    } else {
                        $error = "Error adding user: " . $conn->error;
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
                    $error = "Error preparing statement: " . $conn->error;
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
    }
}

// Handle user edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $id = (int)$_POST['id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    // Fetch existing user data
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->num_rows > 0 ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$user) {
        $error = "User not found.";
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
        $profile_picture = $user['profile_picture'];

        // Validate inputs
        if (empty($username) || empty($email)) {
            $error = "Username and email are required.";
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
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
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
            // Check for duplicate username or email
            $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            if ($stmt) {
                $stmt->bind_param("ssi", $username, $email, $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $error = "Username or email already exists.";
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
                $error = "Error preparing statement: " . $conn->error;
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

            // Handle profile picture upload
            if (!$error && isset($_FILES['edit_profile_picture']) && $_FILES['edit_profile_picture']['error'] == UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024;
                $file_type = $_FILES['edit_profile_picture']['type'];
                $file_size = $_FILES['edit_profile_picture']['size'];
                $file_tmp = $_FILES['edit_profile_picture']['tmp_name'];
                $file_name = time() . '_' . basename($_FILES['edit_profile_picture']['name']);
                $upload_dir = 'Uploads/';
                $upload_path = $upload_dir . $file_name;

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                if (!in_array($file_type, $allowed_types)) {
                    $error = "Profile picture must be JPEG, PNG, or GIF.";
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
                } elseif ($file_size > $max_size) {
                    $error = "Profile picture size exceeds 5MB.";
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
                } elseif (!move_uploaded_file($file_tmp, $upload_path)) {
                    $error = "Error uploading profile picture.";
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
                    $profile_picture = $upload_path;
                    if (file_exists($user['profile_picture']) && $user['profile_picture'] != '') {
                        unlink($user['profile_picture']);
                    }
                }
            }

            // Update user if no error
            if (!$error) {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, is_admin = ?, profile_picture = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("ssisi", $username, $email, $is_admin, $profile_picture, $id);
                    if ($stmt->execute()) {
                        $success = "User updated successfully.";
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
                                    window.location.href = 'admin_users.php?page=$current_page';
                                }, 2000);
                            });
                        </script>";
                    } else {
                        $error = "Error updating user: " . $conn->error;
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
                    $error = "Error preparing statement: " . $conn->error;
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
    }
}

// Pagination settings
$users_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $users_per_page;

// Get total users
$result_count = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $result_count ? $result_count->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_users / $users_per_page);

// Fetch users for current page
$stmt = $conn->prepare("SELECT id, username, email, is_admin, profile_picture FROM users ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $users_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - My Store</title>
    <link rel="shortcut icon" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/svg/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/iconly.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background-color: #f2f7ff;}
        .user-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
            display: flex;
            align-items: center;
        }
        .dark .user-card {
            border: 1px solid #2d2d44;
            background-color: transparent;
            display: flex;
            align-items: center;
        }
        .user-card .profile-pic {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }
        .user-card .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-card .user-info {
            flex-grow: 1;
        }
        .user-card .user-info .username {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .dark .user-card .user-info .username {
            color: #ddd;
        }
        .user-card .user-info .email {
            color: #007bff;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .dark .user-card .user-info .email {
            color: #6ab0ff;
        }
        .user-card .user-info .message {
            color: #6c757d;
            margin-bottom: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
        }
        .dark .user-card .user-info .message {
            color: #b0b0b0;
        }
        .user-card .user-info .message.admin {
            background-color: #d4edda;
        }
        .dark .user-card .user-info .message.admin {
            background-color: #1a3e2b;
        }
        .user-card .user-info .message.regular {
            background-color: #fff3cd;
        }
        .dark .user-card .user-info .message.regular {
            background-color: #4a3d1a;
        }
        .user-card .actions {
            display: flex;
            align-items: center;
            margin-left: auto;
            gap: 15px;
        }
        .btn-edit {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000000;
        }
        .btn-edit:hover {
            background-color: #e0a800 !important;
            border-color: #e0a800 !important;
        }
        .dark .btn-edit {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000000 !important;
        }
        .dark .btn-edit:hover {
            background-color: #e0a800 !important;
            border-color: #e0a800 !important;
        }
        .btn-remove {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
        }
        .btn-remove i {
            color: #fff !important;
        }
        .btn-remove:hover {
            background-color: #c82333 !important;
            border-color: #c82333 !important;
            color: #fff !important;
        }
        .btn-remove:hover i {
            color: #fff !important;
        }
        .dark .btn-remove {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
        }
        .dark .btn-remove i {
            color: #fff !important;
        }
        .dark .btn-remove:hover {
            background-color: #c82333 !important;
            border-color: #c82333 !important;
            color: #fff !important;
        }
        .dark .btn-remove:hover i {
            color: #fff !important;
        }
        .pagination {
            margin-top: 20px;
            justify-content: center;
        }
        .pagination .page-item.active .page-link {
            background-color: #435ebe;
            border-color: #435ebe;
        }
        .pagination .page-link {
            color: #435ebe;
        }
        .pagination .page-link:hover {
            background-color: #e9ecef;
        }
        .dark .pagination .page-item.active .page-link {
            background-color: #5a6ed4;
            border-color: #5a6ed4;
        }
        .dark .pagination .page-link {
            color: #5a6ed4;
        }
        .dark .pagination .page-link:hover {
            background-color: #3d3f4a;
        }
        /* Toast container styling */
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
        /* Delete Modal Styling */
        #deleteModal .modal-content {
            background-color: #fff;
            border-radius: 8px;
        }
        .dark #deleteModal .modal-content {
            background-color: #2d2d44;
            border: 1px solid #4a4a6a;
            color: #ddd;
        }
        #deleteModal .modal-header {
            border-bottom: 1px solid #e0e0e0;
        }
        .dark #deleteModal .modal-header {
            border-bottom: 1px solid #4a4a6a;
        }
        #deleteModal .modal-footer .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        #deleteModal .modal-footer .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .dark #deleteModal .modal-footer .btn-secondary {
            background-color: #5a6268;
            border-color: #5a6268;
            color: #fff;
        }
        .dark #deleteModal .modal-footer .btn-danger {
            background-color: #c82333;
            border-color: #c82333;
            color: #fff;
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
                            <h3><i class="bi bi-people"></i> Manage Users</h3>
                            <p class="text-subtitle text-muted">View and manage user accounts</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <section class="section">
                    <div style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border: none;" class="card">
                        <div class="card-header">
                            <h4 class="card-title">Users</h4>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="bi bi-person-plus"></i> Add User
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Users Card Layout -->
                            <?php if (empty($users)): ?>
                                <div class="alert alert-info">No users found.</div>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <div class="user-card">
                                        <div class="profile-pic">
                                            <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'assets/images/default_profile.png'; ?>" alt="Profile">
                                        </div>
                                        <div class="user-info">
                                            <div class="username"><?php echo htmlspecialchars($user['username']); ?></div>
                                            <div class="email"><?php echo htmlspecialchars($user['email']); ?></div>
                                            <div class="message <?php echo $user['is_admin'] ? 'admin' : 'regular'; ?>">
                                                <?php echo $user['is_admin'] ? 'Admin user' : 'Regular user'; ?>
                                            </div>
                                        </div>
                                        <div class="actions">
                                            <button class="btn btn-edit btn-sm edit-user-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal"
                                                    data-id="<?php echo $user['id']; ?>"
                                                    data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                    data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                    data-is_admin="<?php echo $user['is_admin']; ?>"
                                                    data-profile_picture="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'assets/images/default_profile.png'; ?>">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button class="btn btn-remove btn-sm delete-user-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal"
                                                    data-id="<?php echo $user['id']; ?>" 
                                                    data-page="<?php echo $current_page; ?>">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <!-- Previous Button -->
                                        <li class="page-item <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="admin_users.php?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                                                <span aria-hidden="true">«</span>
                                            </a>
                                        </li>
                                        <!-- Page Numbers (Limited Range) -->
                                        <?php
                                        $range = 2; // Show 2 pages before and after current page
                                        $start = max(1, $current_page - $range);
                                        $end = min($total_pages, $current_page + $range);
                                        if ($start > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="admin_users.php?page=1">1</a></li>';
                                            if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                        for ($i = $start; $i <= $end; $i++): ?>
                                            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                                <a class="page-link" href="admin_users.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor;
                                        if ($end < $total_pages) {
                                            if ($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            echo '<li class="page-item"><a class="page-link" href="admin_users.php?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                                        }
                                        ?>
                                        <!-- Next Button -->
                                        <li class="page-item <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="admin_users.php?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                                                <span aria-hidden="true">»</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Add User Modal -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                                        <div class="col-12 text-center">
                                            <div class="profile-pic-container" onclick="document.getElementById('profile_picture').click();">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <input type="file" name="profile_picture" id="profile_picture" class="form-control d-none" accept="image/*">
                                            <small class="text-muted">JPEG, PNG, GIF (Max 5MB)</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin" value="1">
                                                <label class="form-check-label" for="is_admin">Is Admin</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit User Modal -->
                    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                                        <input type="hidden" name="id" id="edit_id">
                                        <div class="col-md-6">
                                            <label for="edit_username" class="form-label">Username</label>
                                            <input type="text" name="username" id="edit_username" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_email" class="form-label">Email</label>
                                            <input type="email" name="email" id="edit_email" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_is_admin" class="form-label">Admin Status</label>
                                            <select name="is_admin" id="edit_is_admin" class="form-select" required>
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_profile_picture" class="form-label">Profile Picture</label>
                                            <input type="file" name="edit_profile_picture" id="edit_profile_picture" class="form-control" accept="image/*">
                                            <div class="mt-2">
                                                <img id="edit_profile_picture_preview" src="" alt="Profile" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete this user?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <a id="confirmDeleteBtn" class="btn btn-danger" href="#">Delete</a>
                                </div>
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
    <script>
        // Add User Profile Picture Preview
        document.getElementById('profile_picture').addEventListener('change', function() {
            const container = document.querySelector('.profile-pic-container');
            if (this.files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let img = container.querySelector('img');
                    if (!img) {
                        img = document.createElement('img');
                        container.innerHTML = '';
                        container.appendChild(img);
                    }
                    img.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Edit User Modal Population
        document.querySelectorAll('.edit-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const username = this.getAttribute('data-username');
                const email = this.getAttribute('data-email');
                const is_admin = this.getAttribute('data-is_admin');
                const profile_picture = this.getAttribute('data-profile_picture');

                document.getElementById('edit_id').value = id;
                document.getElementById('edit_username').value = username;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_is_admin').value = is_admin;
                document.getElementById('edit_profile_picture_preview').src = profile_picture;
            });
        });

        // Edit User Profile Picture Preview
        document.getElementById('edit_profile_picture').addEventListener('change', function() {
            if (this.files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit_profile_picture_preview').src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Delete User Modal
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const page = this.getAttribute('data-page');
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                document.getElementById('confirmDeleteBtn').href = `admin_users.php?delete_id=${id}&page=${page}`;
                modal.show();
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>