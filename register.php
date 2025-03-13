<!-- email, fname, lname, address, phone_no, loginID(FK) -->




<?php
$connection = mysqli_connect("localhost", "root", "28092008");
$db = mysqli_select_db($connection, "lms");
$success = true;
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
// id, name, email, password, mobile, address
$queryAuth = "insert into auth(password, emailID) values('$_POST[password]', '$_POST[email]')";
try {
    $query_runAuth = mysqli_query($connection, $queryAuth);
} catch (Exception $ex) {
    echo "<script>alert('Error occurred while adding to auth4 table');</script>";
    $success = false;
}
$queryGetID = "select loginID from auth where emailID = '$_POST[email]'";
$query_runGetID = mysqli_query($connection, $queryGetID);
if (mysqli_num_rows($query_runGetID) > 0) {
    $row = mysqli_fetch_assoc($query_runGetID);
    $loginID = $row['loginID'];
    $query = "insert into readers(fname,lname,email,phone_no,address,loginID) values('$_POST[fname]','$_POST[lname]','$_POST[email]',$_POST[phone_no],'$_POST[address]',$loginID)";
    try {
        $query_run = mysqli_query($connection, $query);
    } catch (Exception $ex) {
        echo "<script>alert('Error occurred while adding to readers table');</script>";
        $queryDel = "delete from auth where loginID = '$loginID'";
        $query_runDel = mysqli_query($connection, $queryDel);
        echo $ex;
        $success = false;
    }
    if ($success) {
        echo "<script>alert('Registration successfull...You may Login now !!');</script>";
        echo "<script>window.location.href = 'index.php';</script>";
    } else {
        echo 'Something Went Wrong!';
    }
} else {
    echo "<script>alert('Error occurred while adding to auth table');</script>";
}
?>