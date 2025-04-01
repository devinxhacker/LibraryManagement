<?php
require("../functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../../index.php");
    exit();
}

// Get all books and readers for dropdowns
$books = get_all_available_books();
$readers = get_all_readers();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['issue'])) {
    $book_id = $_POST['book_id'];
    $reader_id = $_POST['reader_id'];
    $issue_date = date('Y-m-d'); // Add current date as issue date
    
    // Validate input
    if (empty($book_id) || empty($reader_id)) {
        $_SESSION['error_message'] = "Please select both book and reader.";
    } else {
        // Check if book is available
        if (!is_book_available($book_id)) {
            $_SESSION['error_message'] = "This book is not available for issue.";
        } else {
            // Issue the book
            if (issue_book($book_id, $reader_id, $issue_date)) {
                $_SESSION['success_message'] = "Book issued successfully.";
                header("Location: view_issued_book.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Error issuing book.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Book - Library Management System</title>
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
                        <h4 class="mb-0"><i class="fas fa-book-reader"></i> Issue Book</h4>
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
                                <label for="book_id" class="form-label"><i class="fas fa-book"></i> Select Book</label>
                                <select class="form-select" id="book_id" name="book_id" required>
                                    <option value="">Choose a book...</option>
                                    <?php while($book = mysqli_fetch_assoc($books)): ?>
                                        <option value="<?php echo $book['bookID']; ?>">
                                            <?php echo htmlspecialchars($book['title'] . ' by ' . $book['authorName']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reader_id" class="form-label"><i class="fas fa-user"></i> Select Reader</label>
                                <select class="form-select" id="reader_id" name="reader_id" required>
                                    <option value="">Choose a reader...</option>
                                    <?php while($reader = mysqli_fetch_assoc($readers)): ?>
                                        <option value="<?php echo $reader['readerID']; ?>">
                                            <?php echo htmlspecialchars($reader['fname'] . ' ' . $reader['lname'] . ' (' . $reader['email'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="issue" class="btn btn-primary">
                                    <i class="fas fa-book-reader"></i> Issue Book
                                </button>
                                <a href="../admin_dashboard.php" class="btn btn-secondary">
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