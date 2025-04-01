<?php
require("../config/database.php");
session_start();

// Check if user is logged in and is a reader
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "reader") {
    header("Location: ../index.php");
    exit();
}

// Get user's issued books
$user_id = $_SESSION['id'];
$issued_books_query = "SELECT b.title, i.issuedate, i.duedate, i.delaydays, i.fines 
                      FROM issued_books i 
                      JOIN books b ON i.bookID = b.bookID 
                      WHERE i.readerID = '$user_id' 
                      ORDER BY i.issuedate DESC";
$issued_books = mysqli_query($conn, $issued_books_query);

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY categoryName";
$categories = mysqli_query($conn, $categories_query);

// Get user's profile information
$user_query = "SELECT * FROM readers WHERE readerID = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get recent activities
$recent_activities_query = "SELECT b.title, i.issuedate, i.duedate, i.delaydays, i.fines 
                          FROM issued_books i 
                          JOIN books b ON i.bookID = b.bookID 
                          WHERE i.readerID = '$user_id' 
                          ORDER BY i.issuedate DESC LIMIT 5";
$recent_activities = mysqli_query($conn, $recent_activities_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - Library Management System</title>
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
        <!-- Search Section -->
    <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-search"></i> Search Books
                    </div>
                    <div class="card-body">
                        <form action="search_books.php" method="GET" class="search-container">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by title, author, or category...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories and Recent Activities -->
        <div class="row mt-4">
            <!-- Categories -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-tags"></i> Categories
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php while($category = mysqli_fetch_assoc($categories)): ?>
                            <a href="category_books.php?id=<?php echo $category['categoryID']; ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder"></i> <?php echo $category['categoryName']; ?>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history"></i> Recent Activities
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Fine</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($activity = mysqli_fetch_assoc($recent_activities)): ?>
                                    <tr>
                                        <td><?php echo $activity['title']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($activity['issuedate'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($activity['duedate'])); ?></td>
                                        <td>
                                            <?php if($activity['delaydays'] > 0): ?>
                                                <span class="badge badge-danger">Overdue</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($activity['fines'] > 0): ?>
                                                <span class="text-danger">$<?php echo $activity['fines']; ?></span>
                                            <?php else: ?>
                                                <span class="text-success">$0</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </div>
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="browse_books.php" class="btn btn-primary btn-block">
                                    <i class="fas fa-book"></i> Browse Books
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="view_issued_book.php" class="btn btn-info btn-block">
                                    <i class="fas fa-book-reader"></i> My Issued Books
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="view_profile.php" class="btn btn-success btn-block">
                                    <i class="fas fa-user"></i> View Profile
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="change_password.php" class="btn btn-warning btn-block">
                                    <i class="fas fa-key"></i> Change Password
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>