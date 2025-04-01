<?php
require("functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../index.php");
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: change_password.php");
        exit();
    }
    
    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "New passwords do not match.";
        header("Location: change_password.php");
        exit();
    }
    
    if ($old_password === $new_password) {
        $_SESSION['error_message'] = "New password must be different from the current password.";
        header("Location: change_password.php");
        exit();
    }
    
    // Attempt to change password
    if (change_admin_password($_SESSION['email'], $old_password, $new_password)) {
        $_SESSION['success_message'] = "Password updated successfully.";
    } else {
        $_SESSION['error_message'] = "Current password is incorrect.";
    }
    
    header("Location: change_password.php");
    exit();
}

// If not POST request, redirect to change password page
header("Location: change_password.php");
exit();
?>