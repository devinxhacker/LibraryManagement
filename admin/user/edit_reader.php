<?php
require("../functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../../index.php");
    exit();
}

// Check if reader ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Reader ID not provided.";
    header("Location: Regusers.php");
    exit();
}

$reader_id = $_GET['id'];

// Get reader details
$reader = get_reader_by_id($reader_id);
if (!$reader) {
    $_SESSION['error_message'] = "Reader not found.";
    header("Location: Regusers.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone_no = trim($_POST['phone_no']);
    $address = trim($_POST['address']);
    
    // Validate input
    $errors = [];
    
    if (empty($fname)) {
        $errors[] = "First name is required.";
    }
    
    if (empty($lname)) {
        $errors[] = "Last name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif ($email != $reader['email'] && email_exists($email)) {
        $errors[] = "Email already exists.";
    }
    
    if (empty($phone_no)) {
        $errors[] = "Phone number is required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone_no)) {
        $errors[] = "Invalid phone number format.";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required.";
    }
    
    if (empty($errors)) {
        if (update_reader($reader_id, $fname, $lname, $email, $phone_no, $address)) {
            $_SESSION['success_message'] = "Reader updated successfully.";
            header("Location: Regusers.php");
            exit();
        } else {
            $errors[] = "Error updating reader.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reader - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="../../css/common.css" rel="stylesheet">
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../admin_dashboard.php">
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
                            <li><a class="dropdown-item" href="../view_profile.php"><i class="fas fa-id-card"></i> View Profile</a></li>
                            <li><a class="dropdown-item" href="../edit_profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a></li>
                            <li><a class="dropdown-item" href="../change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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
                        <h4 class="mb-0"><i class="fas fa-user-edit"></i> Edit Reader</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fname" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="fname" name="fname" 
                                           value="<?php echo htmlspecialchars($reader['fname']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lname" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lname" name="lname" 
                                           value="<?php echo htmlspecialchars($reader['lname']); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($reader['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone_no" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_no" name="phone_no" 
                                       value="<?php echo htmlspecialchars($reader['phone_no']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($reader['address']); ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Reader
                                </button>
                                <a href="Regusers.php" class="btn btn-secondary">
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