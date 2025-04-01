<?php
require("../functions.php");
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "admin") {
    header("Location: ../../index.php");
    exit();
}

// Check if book ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "No book selected.";
    header("Location: manage_book.php");
    exit();
}

$book_id = $_GET['id'];

// Check if book exists and is not issued
$book = get_book_details($book_id);
if (!$book) {
    $_SESSION['error_message'] = "Book not found.";
    header("Location: manage_book.php");
    exit();
}

// Check if book is currently issued
$issued = is_book_issued($book_id);
if ($issued) {
    $_SESSION['error_message'] = "Cannot delete book that is currently issued.";
    header("Location: manage_book.php");
    exit();
}

// Delete the book
if (delete_book($book_id)) {
    $_SESSION['success_message'] = "Book deleted successfully.";
} else {
    $_SESSION['error_message'] = "Error deleting book.";
}

header("Location: manage_book.php");
exit();