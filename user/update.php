<?php
    session_start();
if (!isset($_SESSION["email"])) {
    header("Location:../index.php");
} else if ($_SESSION["who"] != "reader") {
    header("Location:../index.php");
}
    $connection = mysqli_connect("localhost","root","28092008");
    $db = mysqli_select_db($connection,"lms");
    $query = "update readers set fname = '$_POST[fname]',lname = '$_POST[lname]',phone_no = '$_POST[phone_no]',address = '$_POST[address]' where email = '$_SESSION[email]'";
    $query_run = mysqli_query($connection,$query);
    $_SESSION['fname'] = $_POST['fname'];
    $_SESSION['lname'] = $_POST['lname'];
    $_SESSION['name'] = $_POST['fname'] . " " . $_POST['lname'];
    $_SESSION['phone_no'] = $_POST['phone_no'];
    $_SESSION['address'] = $_POST['address'];
?>
<script type="text/javascript">
    alert("Updated successfully...");
    window.location.href = "user_dashboard.php";
</script>
