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
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    
    // Validate input
    if (empty($name) || empty($mobile)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: edit_profile.php");
        exit();
    }
    
    // Update profile
    if (update_admin_profile($_SESSION['email'], $name, $mobile)) {
        $_SESSION['success_message'] = "Profile updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating profile.";
    }
    
    header("Location: edit_profile.php");
    exit();
}

// If not POST request, redirect to edit profile page
header("Location: edit_profile.php");
exit();
