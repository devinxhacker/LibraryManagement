<?php
require("functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../index.php");
    exit();
}

// Get admin profile
$admin = get_admin_profile($_SESSION['email']);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $name = $_POST['fname'] . ' ' . $_POST['lname'];
    $mobile = $_POST['phone_no'];
    
    // Validate input
    if (empty($name) || empty($mobile)) {
        $_SESSION['error_message'] = "All fields are required.";
    } else {
        // Update profile
        if (update_admin_profile($_SESSION['email'], $name, $mobile)) {
            $_SESSION['success_message'] = "Profile updated successfully.";
            // Refresh admin data
            $admin = get_admin_profile($_SESSION['email']);
        } else {
            $_SESSION['error_message'] = "Error updating profile.";
        }
    }
    
    header("Location: edit_profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="../css/common.css" rel="stylesheet">
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="fas fa-book-reader"></i> Library Management System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($admin['fname'] . ' ' . $admin['lname']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="view_profile.php"><i class="fas fa-id-card"></i> View Profile</a></li>
                            <li><a class="dropdown-item" href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a></li>
                            <li><a class="dropdown-item" href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-user-edit"></i> Edit Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['success_message'];
                                unset($_SESSION['success_message']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($admin['email']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label"><i class="fas fa-user"></i> Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($admin['fname'] . ' ' . $admin['lname']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="mobile" class="form-label"><i class="fas fa-phone"></i> Mobile Number</label>
                                <input type="tel" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($admin['phone_no']); ?>" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="update" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                                <a href="view_profile.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>