<?php
require("functions.php");
session_start();
if (!isset($_SESSION["email"])) {
    header("Location:../index.php");
}
else if($_SESSION["who"] != "admin"){
    header("Location:../index.php");
}

// Get recent activities
$recent_activities = get_recent_activities();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard - Library Management System</title>
    <meta charset="utf-8" name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
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
                            <a class="dropdown-item" href="edit_profile.php">
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
                <li class="nav-item active">
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-book"></i> Books
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="add_book.php">
                            <i class="fas fa-plus"></i> Add New Book
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="manage_book.php">
                            <i class="fas fa-cog"></i> Manage Books
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="view_issued_book.php">
                            <i class="fas fa-list"></i> View Issued Books
                        </a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-tags"></i> Category
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="add_cat.php">
                            <i class="fas fa-plus"></i> Add New Category
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="manage_cat.php">
                            <i class="fas fa-cog"></i> Manage Category
                        </a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="Regusers.php">
                            <i class="fas fa-user-friends"></i> Manage Readers
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="manage_admins.php">
                            <i class="fas fa-user-shield"></i> Manage Admins
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="issue_book.php">
                        <i class="fas fa-book-reader"></i> Issue Book
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="return_book.php">
                        <i class="fas fa-undo"></i> Return Book
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <div class="stats-number"><?php echo get_user_count(); ?></div>
                        <a href="Regusers.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-users"></i> View Users
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Books</h5>
                        <div class="stats-number"><?php echo get_book_count(); ?></div>
                        <a href="Regbooks.php" class="btn btn-success btn-sm">
                            <i class="fas fa-book"></i> View Books
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">Categories</h5>
                        <div class="stats-number"><?php echo get_category_count(); ?></div>
                        <a href="Regcat.php" class="btn btn-warning btn-sm">
                            <i class="fas fa-tags"></i> View Categories
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">Issued Books</h5>
                        <div class="stats-number"><?php echo get_issue_book_count(); ?></div>
                        <a href="view_issued_book.php" class="btn btn-info btn-sm">
                            <i class="fas fa-book-reader"></i> View Issued
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities and Quick Actions -->
        <div class="row mt-4">
            <!-- Recent Activities -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history"></i> Recent Activities
                    </div>
                    <div class="card-body">
                        <div class="recent-activity">
                            <?php foreach($recent_activities as $activity): ?>
                            <div class="activity-item">
                                <i class="fas fa-circle text-primary"></i>
                                <?php echo $activity['description']; ?>
                                <small class="text-muted float-right">
                                    <?php echo $activity['timestamp']; ?>
                                </small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="issue_book.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-book-reader"></i> Issue New Book
                            </a>
                            <a href="return_book.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-undo"></i> Process Book Return
                            </a>
                            <a href="add_book.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-plus"></i> Add New Book
                            </a>
                            <a href="add_cat.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-tags"></i> Add New Category
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>