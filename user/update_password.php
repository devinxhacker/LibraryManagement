<?php
session_start();
if (!isset($_SESSION["email"])) {
    header("Location:../index.php");
} else if ($_SESSION["who"] != "reader") {
    header("Location:../index.php");
}
$connection = mysqli_connect("localhost", "root", "28092008");
$db = mysqli_select_db($connection, "lms");
$loginID = "";
$password = "";
$query = "select * from readers where email = '$_SESSION[email]'";
$result = mysqli_query($connection, $query);
$row = mysqli_fetch_array($result);
$loginID = $row["loginID"];
$query2 = "select * from auth where loginID = '$loginID'";
$result2 = mysqli_query($connection, $query2);
$row2 = mysqli_fetch_array($result2);
$password = $row2["password"];
if ($password != $_POST['old_password']) {
    echo '<script type="text/javascript">
        alert("Wrong User Old Password...");
        window.location.href = "change_password.php";
    </script>';
} else {
    if ($password == $_POST['new_password']) {
        echo '<script type="text/javascript">
            alert("New Password can not be same as Old Password...");
            window.location.href = "change_password.php";
        </script>';
    } else {
        $query3 = "update auth set password = '$_POST[new_password]' where loginID = '$loginID'";
        $result3 = mysqli_query($connection, $query3);
        echo '<script type="text/javascript">
            alert("Password Updated Successfully...");
            window.location.href = "change_password.php";
        </script>';
    }
}
?>