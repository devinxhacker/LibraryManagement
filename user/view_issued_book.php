<?php
session_start();
if (!isset($_SESSION["email"])) {
    header("Location:../index.php");
} else if ($_SESSION["who"] != "reader") {
    header("Location:../index.php");
}
#fetch data from database
$connection = mysqli_connect("localhost", "root", "28092008");
$db = mysqli_select_db($connection, "lms");
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
                <font style="color:blue; margin-top: 10px;"><span><strong>Welcome:
                            <?php echo $_SESSION['name']; ?></strong></span></font>
                <font style="color:blue; margin-top: 10px;"><span><strong>Email:
                            <?php echo $_SESSION['email']; ?>
                        </strong></font>
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
    </nav><br>
    <span>
        <marquee>This is library mangement system. Library opens at 8:00 AM and close at 8:00 PM</marquee>
    </span><br><br>
    <center>
        <h4>Issued Book's Detail</h4><br>
    </center>
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <form>
                <table class="table-bordered" width="900px" style="text-align: center">
                    <tr>
                        <th>ISBN</th>
                        <th>Book Name</th>
                        <th>Author</th>
                        <th>Publisher</th>
                        <th>Year of Publication</th>
                        <th>Edition</th>
                        <th>Category</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Delay Days</th>
                        <th>Fines</th>
                    </tr>

                    <?php
                    $query = "select bookID, issuedate, duedate, fines, delaydays from issued_books where readerID = '$_SESSION[id]'";
                    $result = mysqli_query($connection, $query);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_array($result)) {
                            // echo $row["bookID"] . " ";
                            // echo $row["issuedate"] . " ";
                            // echo $row["duedate"] . " ";
                            // echo $row["fines"] . " ";
                            // if row is null
                            $book_id = $row["bookID"];
                            $issuedate = $row["issuedate"];
                            $duedate = $row["duedate"];
                            $fines = $row["fines"];
                            $delaydays = $row["delaydays"];
                            $query2 = "select ISBN, title, authorID, publisherID, edition, categoryID from books where bookID = '$book_id'";
                            $result2 = mysqli_query($connection, $query2);
                            $row2 = mysqli_fetch_array($result2);
                            $isbn = $row2["ISBN"];
                            $book_name = $row2["title"];
                            $author_id = $row2["authorID"];
                            $publisher_id = $row2["publisherID"];
                            $edition = $row2["edition"];
                            $category_id = $row2["categoryID"];
                            $query3 = "select authorName from authors where authorID = '$author_id'";
                            $result3 = mysqli_query($connection, $query3);
                            $row3 = mysqli_fetch_array($result3);
                            $author_name = $row3["authorName"];
                            $query4 = "select publisherName, year_of_pub from publishers where publisherID = '$publisher_id'";
                            $result4 = mysqli_query($connection, $query4);
                            $row4 = mysqli_fetch_array($result4);
                            $publisher_name = $row4["publisherName"];
                            $year_of_pub = $row4["year_of_pub"];
                            $query5 = "select categoryName from categories where categoryID = '$category_id'";
                            $result5 = mysqli_query($connection, $query5);
                            $row5 = mysqli_fetch_array($result5);
                            $category_name = $row5["categoryName"];
                            echo "<tr>";
                            echo "<td>" . $isbn . "</td>";
                            echo "<td>" . $book_name . "</td>";
                            echo "<td>" . $author_name . "</td>";
                            echo "<td>" . $publisher_name . "</td>";
                            echo "<td>" . $year_of_pub . "</td>";
                            echo "<td>" . $edition . "</td>";
                            echo "<td>" . $category_name . "</td>";
                            echo "<td>" . $issuedate . "</td>";
                            echo "<td>" . $duedate . "</td>";
                            echo "<td>" . $delaydays . "</td>";
                            echo "<td>" . $fines . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11'>No Book Issued</td></tr>";
                    }

                    ?>
                </table>
            </form>
        </div>
        <div class="col-md-2"></div>
    </div>
</body>

</html>