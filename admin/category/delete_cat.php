<?php
require("../functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../../index.php");
    exit();
}

// Check if category ID is provided
if (!isset($_GET['cid'])) {
    $_SESSION['error_message'] = "No category selected.";
    header("Location: manage_cat.php");
    exit();
}

$category_id = $_GET['cid'];

// Check if category has any books
$query = "SELECT COUNT(*) as count FROM books WHERE categoryID = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if ($row['count'] > 0) {
    $_SESSION['error_message'] = "Cannot delete category. It contains books.";
    header("Location: manage_cat.php");
    exit();
}

// Delete the category
if (delete_category($category_id)) {
    $_SESSION['success_message'] = "Category deleted successfully.";
} else {
    $_SESSION['error_message'] = "Error deleting category. Please try again.";
}

header("Location: manage_cat.php");
exit();