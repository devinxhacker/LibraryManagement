<?php
require("../functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../../index.php");
    exit();
}

// Process book return if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['return_book'])) {
    $issue_id = $_POST['issue_id'];
    
    if (empty($issue_id)) {
        $_SESSION['error_message'] = "Please select a book to return.";
    } else {
        if (return_book($issue_id)) {
            $_SESSION['success_message'] = "Book returned successfully.";
        } else {
            $_SESSION['error_message'] = "Error returning book.";
        }
    }
    
    // Redirect to clear the POST data
    header("Location: view_issued_book.php");
    exit();
}

// Get all issued books with details
$issued_books = get_all_issued_books_with_details();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Issued Books - Library Management System</title>
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
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-book-reader"></i> Issued Books</h4>
                <div>
                    <a href="issue_book.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Issue New Book
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Reader Name</th>
                                <th>Reader Email</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($book = mysqli_fetch_assoc($issued_books)): ?>
                                <tr class="<?php echo ($book['delaydays'] > 0) ? 'table-danger' : ''; ?>">
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['authorName']); ?></td>
                                    <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                                    <td><?php echo htmlspecialchars($book['fname'] . ' ' . $book['lname']); ?></td>
                                    <td><?php echo htmlspecialchars($book['email']); ?></td>
                                    <td><?php echo $book['issuedate']; ?></td>
                                    <td><?php echo $book['duedate']; ?></td>
                                    <td>
                                        <?php if ($book['delaydays'] > 0): ?>
                                            <span class="badge bg-danger">
                                                Overdue (<?php echo $book['delaydays']; ?> days)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="issue_id" value="<?php echo $book['issue_id']; ?>">
                                                <button type="submit" name="return_book" class="btn btn-sm btn-success" 
                                                        onclick="return confirm('Are you sure you want to return this book?')" 
                                                        title="Return Book">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                            <a href="view_book_details.php?id=<?php echo $book['bookID']; ?>" 
                                               class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
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
