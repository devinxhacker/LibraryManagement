<?php
session_start();

// Process login
if (isset($_POST['login'])) {
    $connection = mysqli_connect("localhost", "root", "28092008");
    $db = mysqli_select_db($connection, "lms");
    $query = "select * from admins where email = '$_POST[email]'";
    $query_run = mysqli_query($connection, $query);
    while ($row = mysqli_fetch_assoc($query_run)) {
        if ($row['email'] == $_POST['email']) {
            $loginID = $row['loginID'];
            $query2 = "select * from auth where loginID = '$loginID'";
            $query_run2 = mysqli_query($connection, $query2);
            $row2 = mysqli_fetch_assoc($query_run2);
            if ($row2['password'] == $_POST['password']) {
                $_SESSION['fname'] = $row['fname'];
                $_SESSION['lname'] = $row['lname'];
                $_SESSION['name'] = $row['fname'] . " " . $row['lname'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['id'] = $row['adminID'];
                $_SESSION['who'] = "admin";
                echo "<script>localStorage.setItem('adminID', '{$_SESSION['id']}');</script>";
                header("Location: admin/admin_dashboard.php");
                exit();
            } else {
                $login_error = '<div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> Wrong Password!
                              </div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Login - Library Management System</title>
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
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="login-container">
                    <center>
                        <h3><i class="fas fa-user-shield"></i> Admin Login</h3>
                    </center>
                    <?php if (isset($login_error)) echo $login_error; ?>
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email ID:</label>
                            <input type="text" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-key"></i> Password:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
</body>

</html>