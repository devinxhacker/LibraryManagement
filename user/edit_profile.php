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
$fname = "";
$lname = "";
$email = "";
$phone_no = "";
$address = "";
$query = "select * from readers where email = '$_SESSION[email]'";
$query_run = mysqli_query($connection, $query);
while ($row = mysqli_fetch_assoc($query_run)) {
    $fname = $row['fname'];
    $lname = $row['lname'];
    $email = $row['email'];
    $phone_no = $row['phone_no'];
    $address = $row['address'];
}
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
    </nav><br>
    <span>
        <marquee>This is library mangement system. Library opens at 8:00 AM and close at 8:00 PM</marquee>
    </span><br><br>
    <center>
        <h4>Edit Profile</h4><br>
    </center>
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <form action="update.php" method="post">
                <div class="form-group">
                    <label for="fname">First Name:</label>
                    <input type="text" class="form-control" name="fname" value="<?php echo $fname; ?>">
                </div>
                <div class="form-group">
                    <label for="lname">Last Name:</label>
                    <input type="text" class="form-control" name="lname" value="<?php echo $lname; ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="text" name="email" class="form-control" value="<?php echo $email; ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="phone_no">Mobile:</label>
                    <input type="text" name="phone_no" class="form-control" value="<?php echo $phone_no; ?>">
                </div>
                <div class="form-group">
                    <label for="mobile">Address:</label>
                    <textarea rows="3" cols="40" name="address" class="form-control"><?php echo $address; ?></textarea>
                </div>
                <button type="submit" name="update" class="btn btn-primary">Update</button>
            </form>
        </div>
        <div class="col-md-4"></div>
    </div>
</body>

</html>