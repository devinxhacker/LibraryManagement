<?php
    session_start();
    #fetch data from database
    $connection = mysqli_connect("localhost","root","28092008");
    $db = mysqli_select_db($connection,"lms");
    $book_name = "";
    $author = "";
    $book_no = "";
    $student_name = "";
    $student_email = "";
    $issuedate = "";
    $duedate = "";
    $query = "select b.title, b.isbn, a.authorName, i.issuedate, i.duedate, r.fname, r.lname, r.email from books b join issued_books i on b.bookID = i.bookID join readers r on r.readerID = i.readerID join authors a on a.authorID = b.authorID;";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Issued Books</title>
    <meta charset="utf-8" name="viewport" content="width=device-width,intial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        #side_bar {
            background: rgba(245, 245, 245, 0.9);
            padding: 50px;
        }

        table {
            background: rgba(245, 245, 245, 0.9);
            padding: 50px;
            text-align: center;
            margin: auto;
        }

        td {
            padding: 10px;
            text-align: center;
            margin: auto;
        }

        th {
            padding: 10px;
            text-align: center;
            margin: auto;
            background: rgba(222, 222, 222, 0.95);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="../index.php">Library Management System (LMS)</a>
            </div>
            <div style="position: fixed; top: 20px; left: 50%;">
            <font style="color:blue; margin-top: 10px;"><span><strong>Welcome: <?php echo $_SESSION['name']; ?></strong></span>
                </font>
                <font style="color:blue; margin-top: 10px;"><span><strong>Email: <?php echo $_SESSION['email']; ?></strong></font>
            </div>
            <ul class="nav navbar-nav navbar-right">
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown">My Profile </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#">View Profile</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Edit Profile</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="change_password.php">Change Password</a>
                </div>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="../logout.php">Logout</a>
              </li>
            </ul>
        </div>
    </nav><br>
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd">
        <div class="container-fluid">
            
            <ul class="nav navbar-nav navbar-center">
              <li class="nav-item">
                <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown">Books </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="add_book.php">Add New Book</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="manage_book.php">Manage Books</a>
                </div>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown">Category </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="add_cat.php">Add New Category</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="manage_cat.php">Manage Category</a>
                </div>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown">Authors</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="add_author.php">Add New Author</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="manage_author.php">Manage Author</a>
                </div>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="issue_book.php">Issue Book</a>
              </li>
            </ul>
        </div>
    </nav><br>
    <span><marquee>This is library mangement system. Library opens at 8:00 AM and close at 8:00 PM</marquee></span><br><br>
        <center><h4>Issued Book's Detail</h4><br></center>
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <form>
                    <table class="table-bordered" width="900px" style="text-align: center">
                        <tr>
                            <th>Name</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Student Name</th>
                            <th>Student Email</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                        </tr>
                
                    <?php
                        $query_run = mysqli_query($connection,$query);
                        while ($row = mysqli_fetch_assoc($query_run)){
                            $book_name = $row['title'];
                            $author = $row['authorName'];
                            $book_no = $row['isbn'];
                            $student_name = $row['fname']." ".$row['lname'];
                            $student_email = $row['email'];
                            $issuedate = $row['issuedate'];
                            $duedate = $row['duedate'];
                            ?>
                            <tr>
                            <td><?php echo $book_name;?></td>
                            <td><?php echo $author;?></td>
                            <td><?php echo $book_no;?></td>
                            <td><?php echo $student_name;?></td>
                            <td><?php echo $student_email;?></td>
                            <td><?php echo $issuedate;?></td>
                            <td><?php echo $duedate;?></td>
                        </tr>

                    <?php
                        }
                    ?>    
                </table>
                </form>
            </div>
            <div class="col-md-2"></div>
        </div>
</body>
</html>
