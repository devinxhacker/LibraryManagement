<?php
session_start();
session_destroy();
echo "<script>localStorage.removeItem('userID'); localStorage.removeItem('adminID'); window.location.href = 'index.php';</script>";
header("Location: index.php");
?>