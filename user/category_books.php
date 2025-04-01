<?php
require("../config/database.php");
session_start();

// Check if user is logged in and is a reader
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "reader") {
    header("Location: ../index.php");
    exit();
}

// Check if category ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid category selected.";
    header("Location: browse_books.php");
    exit();
}

$category_id = (int)$_GET['id'];

// Get category details using prepared statement
$query = "SELECT * FROM categories WHERE categoryID = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);

if (!$category) {
    $_SESSION['error_message'] = "Category not found.";
    header("Location: browse_books.php");
    exit();
}

// Get books in this category using prepared statement
$query = "SELECT b.*, a.authorName, c.categoryName, p.publisherName 
          FROM books b 
          JOIN authors a ON b.authorID = a.authorID 
          JOIN categories c ON b.categoryID = c.categoryID 
          JOIN publishers p ON b.publisherID = p.publisherID 
          WHERE b.categoryID = ? 
          ORDER BY b.title";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$books = mysqli_stmt_get_result($stmt);

// Get all categories for navigation
$categories_query = "SELECT * FROM categories ORDER BY categoryName";
$categories = mysqli_query($conn, $categories_query);

// Display success/error messages if any
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($category['categoryName']); ?> Books - Library Management System</title>
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
                        <i class="fas fa-user-circle"></i> Welcome: <?php echo htmlspecialchars($_SESSION['name']); ?>
                    </span>
                    <span class="email-text">
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['email']); ?>
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
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Categories Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-tags"></i> Categories
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                            <a href="category_books.php?id=<?php echo $cat['categoryID']; ?>" 
                               class="list-group-item list-group-item-action <?php echo $cat['categoryID'] == $category_id ? 'active' : ''; ?>">
                                <i class="fas fa-folder"></i> <?php echo htmlspecialchars($cat['categoryName']); ?>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Books in Category -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-book"></i> <?php echo htmlspecialchars($category['categoryName']); ?> Books
                        <a href="browse_books.php" class="btn btn-sm btn-secondary float-right">
                            <i class="fas fa-arrow-left"></i> Back to Browse Books
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($books) > 0): ?>
                            <div class="row">
                                <?php while($book = mysqli_fetch_assoc($books)): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                            <p class="card-text">
                                                <strong>Author:</strong> <?php echo htmlspecialchars($book['authorName']); ?><br>
                                                <strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisherName']); ?><br>
                                                <strong>ISBN:</strong> <?php echo htmlspecialchars($book['ISBN']); ?><br>
                                                <strong>Edition:</strong> <?php echo htmlspecialchars($book['edition']); ?><br>
                                                <strong>Price:</strong> $<?php echo number_format($book['price'], 2); ?>
                                            </p>
                                            <a href="book_details.php?id=<?php echo $book['bookID']; ?>" class="btn btn-primary">
                                                <i class="fas fa-info-circle"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No books found in this category.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 