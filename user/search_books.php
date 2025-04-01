<?php
require("../config/database.php");
session_start();
if (!isset($_SESSION["email"])) {
    header("Location:../index.php");
}
else if($_SESSION["who"] != "user"){
    header("Location:../index.php");
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Build the query
$query = "SELECT b.*, a.authorName, c.categoryName, p.publisherName 
          FROM books b 
          JOIN authors a ON b.authorID = a.authorID 
          JOIN categories c ON b.categoryID = c.categoryID 
          JOIN publishers p ON b.publisherID = p.publisherID 
          WHERE 1=1";

$params = array();
$types = '';

if (!empty($search)) {
    $query .= " AND (b.title LIKE ? OR a.authorName LIKE ? OR c.categoryName LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, array($search_param, $search_param, $search_param));
    $types .= 'sss';
}

if (!empty($category)) {
    $query .= " AND b.categoryID = ?";
    $params[] = $category;
    $types .= 'i';
}

$query .= " ORDER BY b.title";

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get all categories for filter
$categories_query = "SELECT * FROM categories";
$categories = mysqli_query($conn, $categories_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Results - Library Management System</title>
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
                    <a class="nav-link" href="my_books.php">
                        <i class="fas fa-book-reader"></i> My Books
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <!-- Search Form -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-search"></i> Search Books
                    </div>
                    <div class="card-body">
                        <form action="" method="GET" class="search-container">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input type="text" name="search" class="form-control" 
                                               placeholder="Search by title or author..." 
                                               value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select name="category" class="form-control">
                                            <option value="">All Categories</option>
                                            <?php while($category = mysqli_fetch_assoc($categories)): ?>
                                            <option value="<?php echo $category['categoryID']; ?>"
                                                    <?php echo $category['categoryID'] == $_GET['category'] ? 'selected' : ''; ?>>
                                                <?php echo $category['categoryName']; ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Search Results
                        <?php if(!empty($search) || !empty($category)): ?>
                            <a href="browse_books.php" class="btn btn-sm btn-secondary float-right">
                                <i class="fas fa-times"></i> Clear Search
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <div class="row">
                                <?php while($book = mysqli_fetch_assoc($result)): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $book['title']; ?></h5>
                                            <p class="card-text">
                                                <strong>Author:</strong> <?php echo $book['authorName']; ?><br>
                                                <strong>Category:</strong> <?php echo $book['categoryName']; ?><br>
                                                <strong>Publisher:</strong> <?php echo $book['publisherName']; ?><br>
                                                <strong>ISBN:</strong> <?php echo $book['ISBN']; ?><br>
                                                <strong>Edition:</strong> <?php echo $book['edition']; ?><br>
                                                <strong>Price:</strong> $<?php echo $book['price']; ?>
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
                                <i class="fas fa-info-circle"></i> No books found matching your search criteria.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 