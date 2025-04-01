<?php
require("../config/database.php");
session_start();

// Check if user is logged in and is a reader
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "reader") {
    header("Location: ../index.php");
    exit();
}

// Get user's profile information
$user_id = $_SESSION['id'];
$user_query = "SELECT * FROM readers WHERE readerID = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Process form submission
if (isset($_POST['update_profile'])) {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $phone_no = mysqli_real_escape_string($conn, $_POST['phone_no']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $update_query = "UPDATE readers SET 
                    fname = '$fname',
                    lname = '$lname',
                    phone_no = '$phone_no',
                    address = '$address'
                    WHERE readerID = '$user_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        $_SESSION['name'] = $fname . " " . $lname;
        header("Location: view_profile.php");
        exit();
    } else {
        $error_message = "Error updating profile. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile - Library Management System</title>
    <meta charset="utf-8" name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="../index.php">
                    <i class="fas fa-book-reader"></i> Library Management System
                </a>
            </div>
            <div class="navbar-right">
                <div class="user-info">
                    <span class="welcome-text">
                        <i class="fas fa-user-circle"></i> Welcome: <?php echo $_SESSION['name']; ?>
                    </span>
                    <span class="email-text">
                        <i class="fas fa-envelope"></i> <?php echo $_SESSION['email']; ?>
                    </span>
                </div>
                <ul class="nav navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-user-cog"></i> My Profile
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="view_profile.php">
                                <i class="fas fa-eye"></i> View Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item active" href="edit_profile.php">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="change_password.php">
                                <i class="fas fa-key"></i> Change Password
                            </a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd">
        <div class="container-fluid">
            <ul class="nav navbar-nav navbar-center">
                <li class="nav-item">
                    <a class="nav-link" href="user_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="browse_books.php">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view_issued_book.php">
                        <i class="fas fa-book-reader"></i> My Books
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-edit"></i> Edit Profile
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form action="" method="post">
                            <div class="form-group">
                                <label for="fname"><i class="fas fa-user"></i> First Name:</label>
                                <input type="text" name="fname" class="form-control" value="<?php echo $user['fname']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lname"><i class="fas fa-user"></i> Last Name:</label>
                                <input type="text" name="lname" class="form-control" value="<?php echo $user['lname']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                                <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="phone_no"><i class="fas fa-phone"></i> Phone Number:</label>
                                <input type="text" name="phone_no" class="form-control" value="<?php echo $user['phone_no']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="address"><i class="fas fa-map-marker-alt"></i> Address:</label>
                                <textarea name="address" class="form-control" rows="3" required><?php echo $user['address']; ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                            <a href="view_profile.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
</body>
</html>