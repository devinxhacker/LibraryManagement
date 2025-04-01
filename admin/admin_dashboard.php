<?php
require("functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../index.php");
    exit();
}

// Get statistics
$total_users = get_user_count();
$total_books = get_book_count();
$total_categories = get_category_count();
$total_issued = get_issue_book_count();
$overdue_books = get_overdue_books_count();

// Get recent activities
$recent_activities = get_recent_activities();

// Get quick stats
$available_books = get_available_books_count();
$total_authors = get_author_count();
$total_publishers = get_publisher_count();
$total_admins = get_admin_count();

// Get admin profile for display
$admin_profile = get_admin_profile($_SESSION['email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="../css/common.css" rel="stylesheet">
    <style>
        .stats-card {
            transition: transform 0.2s;
            margin-bottom: 1rem;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #0d6efd;
            margin: 1rem 0;
        }
        .activity-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-item i {
            margin-right: 0.5rem;
            font-size: 0.8rem;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        .card-header i {
            margin-right: 0.5rem;
        }
        .list-group-item i {
            margin-right: 0.5rem;
            width: 1.2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-book-reader"></i> Library Management System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($admin_profile['fname'] . ' ' . $admin_profile['lname']); ?>
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

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd">
        <div class="container-fluid">
            <ul class="nav navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-book"></i> Books
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="book/add_book.php"><i class="fas fa-plus"></i> Add New Book</a></li>
                        <li><a class="dropdown-item" href="book/Regbooks.php"><i class="fas fa-list"></i> View All Books</a></li>
                        <li><a class="dropdown-item" href="book/view_issued_book.php"><i class="fas fa-book-reader"></i> View Issued Books</a></li>
                        <li><a class="dropdown-item" href="book/issue_book.php"><i class="fas fa-book-reader"></i> Issue Book</a></li>
                        <li><a class="dropdown-item" href="book/return_book.php"><i class="fas fa-undo"></i> Return Book</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="user/Regusers.php"><i class="fas fa-user-friends"></i> Manage Readers</a></li>
                        <li><a class="dropdown-item" href="manage_admins.php"><i class="fas fa-user-shield"></i> Manage Admins</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="category/add_cat.php"><i class="fas fa-tags"></i> Add Category</a></li>
                        <li><a class="dropdown-item" href="category/manage_cat.php"><i class="fas fa-tags"></i> Manage Categories</a></li>
                        <li><a class="dropdown-item" href="manage_authors.php"><i class="fas fa-pen-fancy"></i> Manage Authors</a></li>
                        <li><a class="dropdown-item" href="manage_publishers.php"><i class="fas fa-building"></i> Manage Publishers</a></li>
                    </ul>
                </li>
            </ul>
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

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-users"></i> Total Users
                        </h5>
                        <div class="stats-number"><?php echo $total_users; ?></div>
                        <a href="user/Regusers.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-users"></i> View Users
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-book"></i> Total Books
                        </h5>
                        <div class="stats-number"><?php echo $total_books; ?></div>
                        <a href="book/Regbooks.php" class="btn btn-success btn-sm">
                            <i class="fas fa-book"></i> View Books
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-tags"></i> Categories
                        </h5>
                        <div class="stats-number"><?php echo $total_categories; ?></div>
                        <a href="category/manage_cat.php" class="btn btn-warning btn-sm">
                            <i class="fas fa-tags"></i> View Categories
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-book-reader"></i> Issued Books
                        </h5>
                        <div class="stats-number"><?php echo $total_issued; ?></div>
                        <a href="book/view_issued_book.php" class="btn btn-info btn-sm">
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
                            <?php if (!empty($recent_activities)): ?>
                                <?php foreach($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <?php
                                        // Set icon and color based on activity type
                                        $icon = 'fa-circle';
                                        $color = 'primary';
                                        switch($activity['type']) {
                                            case 'book_issue':
                                                $icon = 'fa-book-reader';
                                                $color = 'success';
                                                break;
                                            case 'book_return':
                                                $icon = 'fa-undo';
                                                $color = 'info';
                                                break;
                                            case 'book_add':
                                                $icon = 'fa-plus-circle';
                                                $color = 'success';
                                                break;
                                            case 'book_delete':
                                                $icon = 'fa-trash';
                                                $color = 'danger';
                                                break;
                                            case 'reader_add':
                                                $icon = 'fa-user-plus';
                                                $color = 'success';
                                                break;
                                            case 'reader_update':
                                                $icon = 'fa-user-edit';
                                                $color = 'warning';
                                                break;
                                            case 'reader_delete':
                                                $icon = 'fa-user-minus';
                                                $color = 'danger';
                                                break;
                                            case 'admin_add':
                                                $icon = 'fa-user-shield';
                                                $color = 'success';
                                                break;
                                            case 'admin_update':
                                                $icon = 'fa-user-shield';
                                                $color = 'warning';
                                                break;
                                            case 'admin_delete':
                                                $icon = 'fa-user-shield';
                                                $color = 'danger';
                                                break;
                                            case 'category_add':
                                                $icon = 'fa-tags';
                                                $color = 'success';
                                                break;
                                            case 'category_update':
                                                $icon = 'fa-tags';
                                                $color = 'warning';
                                                break;
                                            case 'category_delete':
                                                $icon = 'fa-tags';
                                                $color = 'danger';
                                                break;
                                            case 'author_add':
                                                $icon = 'fa-pen-fancy';
                                                $color = 'success';
                                                break;
                                            case 'author_update':
                                                $icon = 'fa-pen-fancy';
                                                $color = 'warning';
                                                break;
                                            case 'author_delete':
                                                $icon = 'fa-pen-fancy';
                                                $color = 'danger';
                                                break;
                                            case 'publisher_add':
                                                $icon = 'fa-building';
                                                $color = 'success';
                                                break;
                                            case 'publisher_update':
                                                $icon = 'fa-building';
                                                $color = 'warning';
                                                break;
                                            case 'publisher_delete':
                                                $icon = 'fa-building';
                                                $color = 'danger';
                                                break;
                                        }
                                        ?>
                                        <i class="fas <?php echo $icon; ?> text-<?php echo $color; ?>"></i>
                                        <?php echo htmlspecialchars($activity['description']); ?>
                                        <small class="text-muted float-end">
                                            <?php echo $activity['timestamp']; ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No recent activities to display.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions and Alerts -->
            <div class="col-md-4">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="book/issue_book.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-book-reader"></i> Issue New Book
                            </a>
                            <a href="book/return_book.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-undo"></i> Process Book Return
                            </a>
                            <a href="book/add_book.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-plus"></i> Add New Book
                            </a>
                            <a href="category/add_cat.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-tags"></i> Add New Category
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Overdue Books Alert -->
                <?php if ($overdue_books > 0): ?>
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-exclamation-triangle"></i> Overdue Books Alert
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            There are <strong><?php echo $overdue_books; ?></strong> overdue books.
                            <a href="book/view_issued_book.php" class="btn btn-danger btn-sm float-end">
                                <i class="fas fa-exclamation-circle"></i> View Overdue
                            </a>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar"></i> Quick Stats
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Available Books
                                <span class="badge bg-success rounded-pill"><?php echo $available_books; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Authors
                                <span class="badge bg-primary rounded-pill"><?php echo $total_authors; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Publishers
                                <span class="badge bg-info rounded-pill"><?php echo $total_publishers; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Admins
                                <span class="badge bg-secondary rounded-pill"><?php echo $total_admins; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>