<?php
require("../functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../../index.php");
    exit();
}

// Get all books with author details
$books = get_all_books_with_details();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Books - Library Management System</title>
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
    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-book"></i> Registered Books</h4>
                <a href="add_book.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Book
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                        <th>Author</th>
                                <th>ISBN</th>
                                <th>Category</th>
                        <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                    </tr>
                        </thead>
                        <tbody>
                            <?php while($book = mysqli_fetch_assoc($books)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['authorName']); ?></td>
                                    <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                                    <td><?php echo htmlspecialchars($book['categoryName']); ?></td>
                                    <td>$<?php echo number_format($book['price'], 2); ?></td>
                                    <td>
                                        <?php if ($book['status'] == 0): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Issued</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="edit_book.php?id=<?php echo $book['bookID']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="view_book_details.php?id=<?php echo $book['bookID']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($book['status'] == 0): ?>
                                                <form method="POST" action="delete_book.php" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this book?');">
                                                    <input type="hidden" name="book_id" value="<?php echo $book['bookID']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                        </tr>
                            <?php endwhile; ?>
                        </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>