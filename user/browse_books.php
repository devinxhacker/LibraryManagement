<?php
require("../config/database.php");
session_start();

// Check if user is logged in and is a reader
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "reader") {
    header("Location: ../index.php");
    exit();
}

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY categoryName";
$categories = mysqli_query($conn, $categories_query);

// Get all books with their details
$books_query = "SELECT b.*, a.authorName, p.publisherName, c.categoryName 
                FROM books b 
                JOIN authors a ON b.authorID = a.authorID 
                JOIN publishers p ON b.publisherID = p.publisherID 
                JOIN categories c ON b.categoryID = c.categoryID 
                ORDER BY b.title";
$books = mysqli_query($conn, $books_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Browse Books - Library Management System</title>
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
            <!-- Categories Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-tags"></i> Categories
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="browse_books.php" class="list-group-item list-group-item-action active">
                                <i class="fas fa-th-large"></i> All Books
                            </a>
                            <?php while($category = mysqli_fetch_assoc($categories)): ?>
                            <a href="category_books.php?id=<?php echo $category['categoryID']; ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-folder"></i> <?php echo $category['categoryName']; ?>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Books List -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-book"></i> Available Books
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($books) > 0): ?>
                            <div class="row">
                                <?php while($book = mysqli_fetch_assoc($books)): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $book['title']; ?></h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i> <?php echo $book['authorName']; ?>
                                                </small>
                                            </p>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-building"></i> <?php echo $book['publisherName']; ?>
                                                </small>
                                            </p>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-tag"></i> <?php echo $book['categoryName']; ?>
                                                </small>
                                            </p>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-barcode"></i> <?php echo $book['ISBN']; ?>
                                                </small>
                                            </p>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-book"></i> Edition: <?php echo $book['edition']; ?>
                                                </small>
                                            </p>
                                            <p class="card-text">
                                                <strong>Price: $<?php echo number_format($book['price'], 2); ?></strong>
                                            </p>
                                            <a href="book_details.php?id=<?php echo $book['bookID']; ?>" class="btn btn-primary btn-block">
                                                <i class="fas fa-info-circle"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No books available at the moment.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 