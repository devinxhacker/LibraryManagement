<?php
session_start();

// Process signup
if (isset($_POST['signup'])) {
    $connection = mysqli_connect("localhost", "root", "28092008");
    $db = mysqli_select_db($connection, "lms");
    $query = "select * from readers where email = '$_POST[email]'";
    $query_run = mysqli_query($connection, $query);
    if (mysqli_num_rows($query_run) > 0) {
        $signup_error = '<div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Email already exists!
                      </div>';
    } else {
        if ($_POST['password'] == $_POST['cpassword']) {
            $query = "insert into readers (fname, lname, email) values ('$_POST[fname]', '$_POST[lname]', '$_POST[email]')";
            $query_run = mysqli_query($connection, $query);
            if ($query_run) {
                $query = "select * from readers where email = '$_POST[email]'";
                $query_run = mysqli_query($connection, $query);
                $row = mysqli_fetch_assoc($query_run);
                $loginID = $row['loginID'];
                $query = "insert into auth (loginID, password) values ('$loginID', '$_POST[password]')";
                $query_run = mysqli_query($connection, $query);
                if ($query_run) {
                    $signup_success = '<div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> Registration successful! Please login.
                                  </div>';
                    header("refresh:2;url=index.php");
                } else {
                    $signup_error = '<div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i> Error in registration!
                                  </div>';
                }
            } else {
                $signup_error = '<div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> Error in registration!
                              </div>';
            }
        } else {
            $signup_error = '<div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> Passwords do not match!
                          </div>';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - Library Management System</title>
    <meta charset="utf-8" name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/common.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-book-reader"></i> Library Management System
                </a>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-user"></i> User Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_login.php">
                        <i class="fas fa-user-shield"></i> Admin Login
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="signup-container">
                    <center>
                        <h3><i class="fas fa-user-plus"></i> User Registration</h3>
                    </center>
                    <?php 
                    if (isset($signup_error)) echo $signup_error;
                    if (isset($signup_success)) echo $signup_success;
                    ?>
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="fname"><i class="fas fa-user"></i> First Name:</label>
                            <input type="text" name="fname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="lname"><i class="fas fa-user"></i> Last Name:</label>
                            <input type="text" name="lname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email ID:</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-key"></i> Password:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="cpassword"><i class="fas fa-key"></i> Confirm Password:</label>
                            <input type="password" name="cpassword" class="form-control" required>
                        </div>
                        <button type="submit" name="signup" class="btn btn-primary btn-block">
                            <i class="fas fa-user-plus"></i> Sign Up
                        </button>
                        <a href="index.php" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </a>
                    </form>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
</body>
</html>
