<?php
session_start();

// Process login
if (isset($_POST['login'])) {
    $connection = mysqli_connect("localhost", "root", "28092008");
    $db = mysqli_select_db($connection, "lms");
    $query = "select * from readers where email = '$_POST[email]'";
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
                $_SESSION['id'] = $row['readerID'];
                $_SESSION['who'] = "reader";
                echo "<script>localStorage.setItem('userID', '{$_SESSION['id']}');</script>";
                header("Location: user/user_dashboard.php");
                exit();
            } else {
                $login_error = '<div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> Wrong Password!
                              </div>';
            }
        }
    }
}

// Process logout
if (isset($_POST['logout'])) {
    session_destroy();
    echo "<script>localStorage.removeItem('userID'); window.location.href = 'index.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Library Management System</title>
    <meta charset="utf-8" name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/common.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<style type="text/css">
    #main_content {
        background: rgba(245, 245, 245, 0.9);
        padding: 50px;
    }

    #side_bar {
        background: rgba(245, 245, 245, 0.9);
        padding: 50px;
    }

    body {
        background: rgba(245, 245, 245, 0.4);
        /* background-image: url("https://img.freepik.com/free-photo/abundant-collection-antique-books-wooden-shelves-generated-by-ai_188544-29660.jpg?size=626&amp;ext=jpg&amp;ga=GA1.1.1546980028.1704240000&amp;semt=sph"); */
    }
</style>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-book-reader"></i> Library Management System
                </a>
            </div>
            <?php if (!isset($_SESSION['name'])): ?>
                <ul class="nav navbar-nav navbar-right">
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
                    <li class="nav-item">
                        <a class="nav-link" href="signup.php">
                            <i class="fas fa-user-plus"></i> Signup
                        </a>
                    </li>
                </ul>
            <?php elseif ($_SESSION['who'] == "admin"): ?>
                <div class="user-info">
                    <span class="welcome-text">
                        <i class="fas fa-user-circle"></i> Welcome: <?php echo $_SESSION['name']; ?>
                    </span>
                    <span class="email-text">
                        <i class="fas fa-envelope"></i> <?php echo $_SESSION['email']; ?>
                    </span>
                </div>
                <ul class="nav navbar-nav navbar-right">
                    <li class="nav-item">
                        <a class="nav-link" href="admin/admin_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-user-cog"></i> My Profile
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="admin/view_profile.php">
                                <i class="fas fa-eye"></i> View Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="admin/edit_profile.php">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="admin/change_password.php">
                                <i class="fas fa-key"></i> Change Password
                            </a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            <?php else: ?>
                <div class="user-info">
                    <span class="welcome-text">
                        <i class="fas fa-user-circle"></i> Welcome: <?php echo $_SESSION['name']; ?>
                    </span>
                    <span class="email-text">
                        <i class="fas fa-envelope"></i> <?php echo $_SESSION['email']; ?>
                    </span>
                </div>
                <ul class="nav navbar-nav navbar-right">
                    <li class="nav-item">
                        <a class="nav-link" href="user/user_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-user-cog"></i> My Profile
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="user/view_profile.php">
                                <i class="fas fa-eye"></i> View Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="user/edit_profile.php">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="user/change_password.php">
                                <i class="fas fa-key"></i> Change Password
                            </a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </nav>
    <div class="row">
        <div class="col-md-4" id="side_bar">
            <h5>Today's Quote</h5>
            <h6>"There is more treasure in books than in all the pirate's loot on Treasure Island"</h6>
            <p>~ Walt Disney</p>
            <h5>Library Timing</h5>
            <ul>
                <li>Opening: 9:00 AM</li>
                <li>Closing: 12:00 PM</li>
            </ul>
            <h5>What We provide ?</h5>
            <ul>
                <li>AC Rooms</li>
                <li>Free Wi-fi</li>
                <li>Learning Environment</li>
                <li>Discussion Room</li>
                <li>Free Electricity</li>
            </ul>
        </div>
        <div class="col-md-8" id="main_content">
            <div id="login-section">
                <?php if (!isset($_SESSION['email'])): ?>
                    <center>
                        <h3><i class="fas fa-user-circle"></i> User Login Form</h3>
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
                        <button type="submit" name="login" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                        <a href="signup.php" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Signup now
                        </a>
                    </form>
                <?php elseif ($_SESSION['who'] == 'admin'): ?>
                    <center>
                        <h3><i class="fas fa-user-shield"></i> ADMIN</h3>
                    </center>
                    <div class="profile-section">
                        <p><i class="fas fa-user-circle"></i> Welcome, Admin <?php echo $_SESSION['name']; ?>!</p>
                        <p><i class="fas fa-envelope"></i> Email: <?php echo $_SESSION['email']; ?></p>
                        <form action="" method="post">
                            <button type="submit" name="logout" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <center>
                        <h3><i class="fas fa-user"></i> USER</h3>
                    </center>
                    <div class="profile-section">
                        <p><i class="fas fa-user-circle"></i> Welcome, <?php echo $_SESSION['name']; ?>!</p>
                        <p><i class="fas fa-envelope"></i> Email: <?php echo $_SESSION['email']; ?></p>
                        <form action="" method="post">
                            <button type="submit" name="logout" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>