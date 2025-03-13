<?php
session_start();
$connection = mysqli_connect("localhost", "root", "28092008");
$db = mysqli_select_db($connection, "lms");
$user_issue_book_count = 0;
$query = "select count(*) as book_count from issued_books where readerID = '$_SESSION[id]'";
$query_run = mysqli_query($connection, $query);
$row = mysqli_fetch_array($query_run);
$user_issue_book_count = $row['book_count'];

?>
<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <meta charset="utf-8" name="viewport" content="width=device-width,intial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<style type="text/css">
    body {
        background: rgba(245, 245, 245, 0.4);
        /* background-image: url("https://img.freepik.com/free-photo/abundant-collection-antique-books-wooden-shelves-generated-by-ai_188544-29660.jpg?size=626&amp;ext=jpg&amp;ga=GA1.1.1546980028.1704240000&amp;semt=sph"); */
    }
</style>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="../index.php">Library Management System (LMS)</a>
            </div>
            <div style="position: fixed; top: 20px; left: 50%;">
                <font style="color:blue; margin-top: 10px;"><span><strong>Welcome:
                            <?php echo $_SESSION['name']; ?></strong></span>
                </font>
                <font style="color:blue; margin-top: 10px;"><span><strong>Email:
                            <?php echo $_SESSION['email']; ?></strong></font>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li class="nav-item"><a class="nav-link" href="user_dashboard.php">Dashboard</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown">My Profile </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="view_profile.php">View Profile</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="edit_profile.php">Edit Profile</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="change_password.php">Change Password</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="row">
        <div class="col-md-3" style="margin: 25px">
            <div class="card bg-light" style="width: 300px">
                <div class="card-header">Book Issued</div>
                <div class="card-body">
                    <p class="card-text">No of book issued: <?php echo $user_issue_book_count; ?></p>
                    <a class="btn btn-success" href="view_issued_book.php">View Issued Books</a>
                </div>
            </div>
        </div>
        <div class="col-md-3"></div>
        <div class="col-md-3"></div>
    </div>
</body>

</html>