<?php
require("../config/database.php");
session_start();

// Check if user is logged in and is a reader
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "reader") {
    header("Location: ../index.php");
    exit();
}

// Get user's issued books
$user_id = $_SESSION['id'];
$issued_books_query = "SELECT b.title, b.ISBN, a.authorName, i.issuedate, i.duedate, i.delaydays, i.fines 
                      FROM issued_books i 
                      JOIN books b ON i.bookID = b.bookID 
                      JOIN authors a ON b.authorID = a.authorID 
                      WHERE i.readerID = '$user_id' 
                      ORDER BY i.issuedate DESC";
$issued_books = mysqli_query($conn, $issued_books_query);
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Issued Books - Library Management System</title>
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
                <li class="nav-item">
                    <a class="nav-link" href="browse_books.php">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                </li>
                <li class="nav-item active">
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
                        <i class="fas fa-book-reader"></i> My Issued Books
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($issued_books) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Book Title</th>
                                            <th>Author</th>
                                            <th>ISBN</th>
                                            <th>Issue Date</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Fine</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($book = mysqli_fetch_assoc($issued_books)): ?>
                                        <tr>
                                            <td><?php echo $book['title']; ?></td>
                                            <td><?php echo $book['authorName']; ?></td>
                                            <td><?php echo $book['ISBN']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($book['issuedate'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($book['duedate'])); ?></td>
                                            <td>
                                                <?php if($book['delaydays'] > 0): ?>
                                                    <span class="badge badge-danger">Overdue</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($book['fines'] > 0): ?>
                                                    <span class="text-danger">$<?php echo $book['fines']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-success">$0</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You haven't issued any books yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>