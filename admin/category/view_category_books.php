<?php
require("../functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../../index.php");
    exit();
}

// Check if category ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Category ID not provided.";
    header("Location: manage_cat.php");
    exit();
}

$category_id = $_GET['id'];

// Get category details
$category = get_category_by_id($category_id);
if (!$category) {
    $_SESSION['error_message'] = "Category not found.";
    header("Location: manage_cat.php");
    exit();
}

// Get admin profile
$admin_profile = get_admin_profile($_SESSION['email']);

// Get books in this category with additional details
$query = "SELECT b.*, a.authorName, p.publisherName, 
          CASE WHEN ib.bookID IS NOT NULL THEN 1 ELSE 0 END as is_issued,
          ib.issuedate, ib.duedate, r.fname as reader_fname, r.lname as reader_lname
          FROM books b
          LEFT JOIN authors a ON b.authorID = a.authorID
          LEFT JOIN publishers p ON b.publisherID = p.publisherID
          LEFT JOIN issued_books ib ON b.bookID = ib.bookID
          LEFT JOIN readers r ON ib.readerID = r.readerID
          WHERE b.categoryID = ?
          ORDER BY b.title";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$books = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books in <?php echo htmlspecialchars($category['categoryName']); ?> - Library Management System</title>
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
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($admin_profile['fname'] . ' ' . $admin_profile['lname']); ?>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-books"></i> Books in <?php echo htmlspecialchars($category['categoryName']); ?></h4>
            <a href="manage_cat.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Categories
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Publisher</th>
                                <th>ISBN</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($books as $book): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['authorName']); ?></td>
                                    <td><?php echo htmlspecialchars($book['publisherName']); ?></td>
                                    <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                                    <td>
                                        <?php if ($book['is_issued']): ?>
                                            <span class="badge bg-warning">
                                                Issued to <?php echo htmlspecialchars($book['reader_fname'] . ' ' . $book['reader_lname']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="../book/view_book_details.php?id=<?php echo $book['bookID']; ?>" 
                                               class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="../book/edit_book.php?id=<?php echo $book['bookID']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (!$book['is_issued']): ?>
                                                <a href="../book/issue_book.php?id=<?php echo $book['bookID']; ?>" 
                                                   class="btn btn-sm btn-success" title="Issue Book">
                                                    <i class="fas fa-book-reader"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 