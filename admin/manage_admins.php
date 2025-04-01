<?php
require("functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../index.php");
    exit();
}

// Get all admins
$admins = get_all_admins();

// Handle admin deletion
if (isset($_POST['delete']) && isset($_POST['admin_id'])) {
    $admin_id = $_POST['admin_id'];
    if (delete_admin($admin_id)) {
        $_SESSION['success_message'] = "Admin deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting admin.";
    }
    header("Location: manage_admins.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    $errors = [];
    if (empty($fname)) $errors[] = "First name is required.";
    if (empty($lname)) $errors[] = "Last name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (strlen($phone) > 10) $errors[] = "Phone number must be at most 10 characters long.";
    if (empty($address)) $errors[] = "Address is required.";
    
    // Only validate password if it's being changed
    if (!empty($password)) {
        if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
    }

    // Check if email already exists (excluding current admin)
    if (email_exists($email) && $email !== $admin['email']) {
        $errors[] = "Email already exists.";
    }

    if (empty($errors)) {
        // $admin_id, $email, $fname, $lname, $address, $phone_no
        if (update_admin($admin_id, $email, $fname, $lname, $address, $phone)) {
            if($password != ""){
                update_password($admin['loginID'], $password);
            }
            $_SESSION['success_message'] = "Admin updated successfully.";
            header("Location: manage_admins.php");
            exit();
        } else {
            $errors[] = "Error updating admin.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Library Management System</title>
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
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
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
    <div class="container-fluid mt-4">
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

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-user-shield"></i> Manage Admins</h4>
                <a href="add_admin.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Admin
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($admin = mysqli_fetch_assoc($admins)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($admin['fname'] . ' ' . $admin['lname']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['phone_no']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['address']); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="edit_admin.php?id=<?php echo $admin['adminID']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (isset($_SESSION['loginID']) && $admin['adminID'] != $_SESSION['loginID']): ?>
                                                <form method="POST" action="" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                                    <input type="hidden" name="admin_id" value="<?php echo $admin['adminID']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
