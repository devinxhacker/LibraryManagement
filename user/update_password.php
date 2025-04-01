<?php
require("../config/database.php");
session_start();

// Check if user is logged in and is a reader
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "reader") {
    header("Location: ../index.php");
    exit();
}

// Get user's login ID
$user_id = $_SESSION['id'];
$user_query = "SELECT loginID FROM readers WHERE readerID = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
$loginID = $user['loginID'];

// Get current password
$auth_query = "SELECT password FROM auth WHERE loginID = '$loginID'";
$auth_result = mysqli_query($conn, $auth_query);
$auth = mysqli_fetch_assoc($auth_result);
$current_password = $auth['password'];

// Process password update
if (isset($_POST['update'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords
    if ($current_password != $old_password) {
        $_SESSION['error_message'] = "Current password is incorrect.";
        header("Location: change_password.php");
        exit();
    }

    if ($new_password == $old_password) {
        $_SESSION['error_message'] = "New password cannot be the same as current password.";
        header("Location: change_password.php");
        exit();
    }

    if ($new_password != $confirm_password) {
        $_SESSION['error_message'] = "New passwords do not match.";
        header("Location: change_password.php");
        exit();
    }

    // Update password
    $new_password = mysqli_real_escape_string($conn, $new_password);
    $update_query = "UPDATE auth SET password = '$new_password' WHERE loginID = '$loginID'";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success_message'] = "Password updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating password. Please try again.";
    }
}

header("Location: change_password.php");
exit();
?>