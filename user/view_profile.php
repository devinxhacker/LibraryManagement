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

// Get user's issued books count
$issued_books_query = "SELECT COUNT(*) as count FROM issued_books WHERE readerID = '$user_id'";
$issued_books_result = mysqli_query($conn, $issued_books_query);
$issued_books_count = mysqli_fetch_assoc($issued_books_result)['count'];

// Get user's overdue books count
$overdue_books_query = "SELECT COUNT(*) as count FROM issued_books WHERE readerID = '$user_id' AND delaydays > 0";
$overdue_books_result = mysqli_query($conn, $overdue_books_query);
$overdue_books_count = mysqli_fetch_assoc($overdue_books_result)['count'];

// Get user's total fines
$fines_query = "SELECT SUM(fines) as total FROM issued_books WHERE readerID = '$user_id'";
$fines_result = mysqli_query($conn, $fines_query);
$total_fines = mysqli_fetch_assoc($fines_result)['total'] ?? 0;
?>
<!DOCTYPE html>
<html>

<head>
    <title>View Profile - Library Management System</title>
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
                            <a class="dropdown-item active" href="view_profile.php">
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
            <!-- Profile Information -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user"></i> Profile Information
                    </div>
                    <div class="card-body">
                        <div class="profile-info">
                            <p><strong><i class="fas fa-user"></i> Name:</strong> <?php echo $user['fname'] . ' ' . $user['lname']; ?></p>
                            <p><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo $user['email']; ?></p>
                            <p><strong><i class="fas fa-phone"></i> Phone:</strong> <?php echo $user['phone_no']; ?></p>
                            <p><strong><i class="fas fa-map-marker-alt"></i> Address:</strong> <?php echo $user['address']; ?></p>
                        </div>
                        <div class="mt-3">
                            <a href="edit_profile.php" class="btn btn-primary btn-block">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Library Statistics -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar"></i> Library Statistics
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <i class="fas fa-book-reader"></i>
                                    <h3><?php echo $issued_books_count; ?></h3>
                                    <p>Issued Books</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <h3><?php echo $overdue_books_count; ?></h3>
                                    <p>Overdue Books</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <i class="fas fa-dollar-sign"></i>
                                    <h3>$<?php echo number_format($total_fines, 2); ?></h3>
                                    <p>Total Fines</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="fas fa-history"></i> Recent Activity
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_activity_query = "SELECT b.title, i.issuedate, i.duedate, i.delaydays, i.fines 
                                                FROM issued_books i 
                                                JOIN books b ON i.bookID = b.bookID 
                                                WHERE i.readerID = '$user_id' 
                                                ORDER BY i.issuedate DESC LIMIT 5";
                        $recent_activity = mysqli_query($conn, $recent_activity_query);
                        ?>
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
                                    <?php while($activity = mysqli_fetch_assoc($recent_activity)): ?>
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
    </div>
</body>

</html>