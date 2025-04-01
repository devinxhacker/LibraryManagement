<?php
session_start();
if (isset($_SESSION['who']) && $_SESSION['who'] == "admin") {
    echo "<script>localStorage.removeItem('adminID');</script>";
} else {
    echo "<script>localStorage.removeItem('userID');</script>";
}
session_destroy();
header("Location: index.php");
?>