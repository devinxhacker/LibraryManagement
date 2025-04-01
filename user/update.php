<?php
require("../config/database.php");
session_start();

// Check if user is logged in and is a reader
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "reader") {
    header("Location: ../index.php");
    exit();
}

// Get user's ID
$user_id = $_SESSION['id'];

// Process form submission
if (isset($_POST['fname']) && isset($_POST['lname']) && isset($_POST['phone_no']) && isset($_POST['address'])) {
    // Sanitize input
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $phone_no = mysqli_real_escape_string($conn, $_POST['phone_no']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Update user profile
    $update_query = "UPDATE readers SET 
                    fname = '$fname',
                    lname = '$lname',
                    phone_no = '$phone_no',
                    address = '$address'
                    WHERE readerID = '$user_id'";

    if (mysqli_query($conn, $update_query)) {
        // Update session variables
        $_SESSION['fname'] = $fname;
        $_SESSION['lname'] = $lname;
        $_SESSION['name'] = $fname . " " . $lname;
        $_SESSION['phone_no'] = $phone_no;
        $_SESSION['address'] = $address;
        $_SESSION['success_message'] = "Profile updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating profile. Please try again.";
    }
}

// Redirect back to user dashboard
header("Location: user_dashboard.php");
exit();
