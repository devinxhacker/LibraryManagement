<?php
require("../functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../../index.php");
    exit();
}

// Check if book ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "No book selected.";
    header("Location: Regbooks.php");
    exit();
}

$book_id = $_GET['id'];

// Get book details
$book = get_book_by_id($book_id);
if (!$book) {
    $_SESSION['error_message'] = "Book not found.";
    header("Location: Regbooks.php");
    exit();
}

// Check if book is issued
$is_issued = !is_book_available($book_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Details - Library Management System</title>
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-book"></i> Book Details</h4>
                        <div>
                            <a href="edit_book.php?id=<?php echo $book_id; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit Book
                            </a>
                            <a href="Regbooks.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Basic Information</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th><i class="fas fa-barcode"></i> ISBN</th>
                                        <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-book"></i> Title</th>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-bookmark"></i> Edition</th>
                                        <td><?php echo htmlspecialchars($book['edition']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-dollar-sign"></i> Price</th>
                                        <td>$<?php echo number_format($book['price'], 2); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3">Additional Information</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th><i class="fas fa-user-edit"></i> Author</th>
                                        <td><?php echo htmlspecialchars($book['authorName']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-building"></i> Publisher</th>
                                        <td><?php echo htmlspecialchars($book['publisherName']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-calendar"></i> Publication Year</th>
                                        <td><?php echo htmlspecialchars($book['year_of_pub']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-tags"></i> Category</th>
                                        <td><?php echo htmlspecialchars($book['categoryName']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Status Information</h5>
                                <div class="alert <?php echo $is_issued ? 'alert-warning' : 'alert-success'; ?>">
                                    <i class="fas <?php echo $is_issued ? 'fa-exclamation-triangle' : 'fa-check-circle'; ?>"></i>
                                    <?php echo $is_issued ? 'This book is currently issued to a reader.' : 'This book is available for issue.'; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($is_issued): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Issue Information</h5>
                                <?php
                                $query = "SELECT i.*, r.fname, r.lname, r.email, r.phone_no 
                                         FROM issued_books i 
                                         JOIN readers r ON i.readerID = r.readerID 
                                         WHERE i.bookID = ?";
                                $stmt = mysqli_prepare($conn, $query);
                                mysqli_stmt_bind_param($stmt, "i", $book_id);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                $issue = mysqli_fetch_assoc($result);
                                ?>
                                <table class="table table-bordered">
                                    <tr>
                                        <th><i class="fas fa-user"></i> Issued To</th>
                                        <td><?php echo htmlspecialchars($issue['fname'] . ' ' . $issue['lname']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-envelope"></i> Reader Email</th>
                                        <td><?php echo htmlspecialchars($issue['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-phone"></i> Reader Phone</th>
                                        <td><?php echo htmlspecialchars($issue['phone_no']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-calendar-alt"></i> Issue Date</th>
                                        <td><?php echo date('M d, Y', strtotime($issue['issuedate'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-calendar-check"></i> Due Date</th>
                                        <td><?php echo date('M d, Y', strtotime($issue['duedate'])); ?></td>
                                    </tr>
                                    <?php if ($issue['delaydays'] > 0): ?>
                                    <tr>
                                        <th><i class="fas fa-exclamation-circle text-danger"></i> Delay Days</th>
                                        <td class="text-danger"><?php echo $issue['delaydays']; ?> days</td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-dollar-sign text-danger"></i> Fine Amount</th>
                                        <td class="text-danger">$<?php echo number_format($issue['fines'], 2); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 