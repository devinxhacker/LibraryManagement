<?php
require("../config/database.php");
session_start();

// Check if user is logged in and is a reader
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "reader") {
    header("Location: ../index.php");
    exit();
}

// Check if book ID is provided
if (!isset($_GET['id'])) {
    header("Location: browse_books.php");
    exit();
}

$book_id = $_GET['id'];

// Get book details
$book_query = "SELECT b.*, a.authorName, p.publisherName, c.categoryName 
               FROM books b 
               JOIN authors a ON b.authorID = a.authorID 
               JOIN publishers p ON b.publisherID = p.publisherID 
               JOIN categories c ON b.categoryID = c.categoryID 
               WHERE b.bookID = '$book_id'";
$book_result = mysqli_query($conn, $book_query);
$book = mysqli_fetch_assoc($book_result);

// Check if book exists
if (!$book) {
    header("Location: browse_books.php");
    exit();
}

// Check if book is already issued to the user
$user_id = $_SESSION['id'];
$issued_query = "SELECT * FROM issued_books WHERE bookID = '$book_id' AND readerID = '$user_id'";
$issued_result = mysqli_query($conn, $issued_query);
$is_issued = mysqli_num_rows($issued_result) > 0;

// Check if book is available
$availability_query = "SELECT COUNT(*) as count FROM issued_books WHERE bookID = '$book_id'";
$availability_result = mysqli_query($conn, $availability_query);
$availability = mysqli_fetch_assoc($availability_result);
$is_available = $availability['count'] == 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $book['title']; ?> - Library Management System</title>
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
                <li class="nav-item">
                    <a class="nav-link" href="user_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item active">
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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-book"></i> Book Details
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h2><?php echo $book['title']; ?></h2>
                                <hr>
                                <div class="book-details">
                                    <p><strong><i class="fas fa-user"></i> Author:</strong> <?php echo $book['authorName']; ?></p>
                                    <p><strong><i class="fas fa-building"></i> Publisher:</strong> <?php echo $book['publisherName']; ?></p>
                                    <p><strong><i class="fas fa-tag"></i> Category:</strong> <?php echo $book['categoryName']; ?></p>
                                    <p><strong><i class="fas fa-barcode"></i> ISBN:</strong> <?php echo $book['ISBN']; ?></p>
                                    <p><strong><i class="fas fa-book"></i> Edition:</strong> <?php echo $book['edition']; ?></p>
                                    <p><strong><i class="fas fa-dollar-sign"></i> Price:</strong> $<?php echo number_format($book['price'], 2); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Book Status</h5>
                                        <?php if($is_issued): ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> This book is already issued to you.
                                            </div>
                                            <a href="view_issued_book.php" class="btn btn-primary btn-block">
                                                <i class="fas fa-book-reader"></i> View My Books
                                            </a>
                                        <?php elseif($is_available): ?>
                                            <div class="alert alert-success">
                                                <i class="fas fa-check-circle"></i> This book is available for issue.
                                            </div>
                                            <form action="issue_book.php" method="POST">
                                                <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                                                <button type="submit" class="btn btn-success btn-block">
                                                    <i class="fas fa-book"></i> Issue Book
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-circle"></i> This book is currently issued to another reader.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 