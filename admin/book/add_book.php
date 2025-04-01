<?php
require("../functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../../index.php");
    exit();
}

// Get categories for dropdown
$categories = get_all_categories();
$authors = get_all_authors();
$publishers = get_all_publishers();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $isbn = $_POST['isbn'];
    $title = $_POST['title'];
    $author_id = $_POST['author_id'];
    $publisher_id = $_POST['publisher_id'];
    $category_id = $_POST['category_id'];
    $edition = $_POST['edition'];
    $price = $_POST['price'];
    
    // Validate input
    if (empty($isbn) || empty($title) || empty($author_id) || empty($publisher_id) || 
        empty($category_id) || empty($edition) || empty($price)) {
        $_SESSION['error_message'] = "All fields are required.";
    } else {
        // Add book
        if (add_book($isbn, $author_id, $publisher_id, $title, $edition, $category_id, $price)) {
            $_SESSION['success_message'] = "Book added successfully.";
            header("Location: manage_book.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error adding book.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book - Library Management System</title>
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
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-plus"></i> Add New Book</h4>
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

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="isbn" class="form-label"><i class="fas fa-barcode"></i> ISBN</label>
                                <input type="text" class="form-control" id="isbn" name="isbn" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="title" class="form-label"><i class="fas fa-book"></i> Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="author_id" class="form-label"><i class="fas fa-user-edit"></i> Author</label>
                                <select class="form-select" id="author_id" name="author_id" required>
                                    <option value="">Select Author</option>
                                    <?php while($author = mysqli_fetch_assoc($authors)): ?>
                                        <option value="<?php echo $author['authorID']; ?>">
                                            <?php echo htmlspecialchars($author['authorName']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="publisher_id" class="form-label"><i class="fas fa-building"></i> Publisher</label>
                                <select class="form-select" id="publisher_id" name="publisher_id" required>
                                    <option value="">Select Publisher</option>
                                    <?php while($publisher = mysqli_fetch_assoc($publishers)): ?>
                                        <option value="<?php echo $publisher['publisherID']; ?>">
                                            <?php echo htmlspecialchars($publisher['publisherName']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category_id" class="form-label"><i class="fas fa-tags"></i> Category</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php while($category = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $category['categoryID']; ?>">
                                            <?php echo htmlspecialchars($category['categoryName']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edition" class="form-label"><i class="fas fa-bookmark"></i> Edition</label>
                                <input type="text" class="form-control" id="edition" name="edition" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label"><i class="fas fa-dollar-sign"></i> Price</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Book
                                </button>
                                <a href="manage_book.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>