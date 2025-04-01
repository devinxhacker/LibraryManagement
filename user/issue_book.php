<?php
require("../config/database.php");
session_start();

// Check if user is logged in and is a reader
if (!isset($_SESSION["email"]) || $_SESSION["who"] != "reader") {
    header("Location: ../index.php");
    exit();
}

// Check if book ID is provided
if (!isset($_POST['book_id'])) {
    header("Location: browse_books.php");
    exit();
}

$book_id = $_POST['book_id'];
$user_id = $_SESSION['id'];

// Check if book is available
$availability_query = "SELECT COUNT(*) as count FROM issued_books WHERE bookID = '$book_id'";
$availability_result = mysqli_query($conn, $availability_query);
$availability = mysqli_fetch_assoc($availability_result);
$is_available = $availability['count'] == 0;

// Check if user already has this book
$issued_query = "SELECT * FROM issued_books WHERE bookID = '$book_id' AND readerID = '$user_id'";
$issued_result = mysqli_query($conn, $issued_query);
$is_issued = mysqli_num_rows($issued_result) > 0;

if ($is_available && !$is_issued) {
    // Issue the book
    $issue_date = date('Y-m-d');
    $issue_query = "INSERT INTO issued_books (bookID, readerID, issuedate) VALUES ('$book_id', '$user_id', '$issue_date')";
    
    if (mysqli_query($conn, $issue_query)) {
        $_SESSION['success_message'] = "Book issued successfully!";
    } else {
        $_SESSION['error_message'] = "Error issuing book. Please try again.";
    }
} else {
    if ($is_issued) {
        $_SESSION['error_message'] = "You have already issued this book.";
    } else {
        $_SESSION['error_message'] = "This book is not available for issue.";
    }
}

// Redirect back to book details page
header("Location: book_details.php?id=" . $book_id);
exit(); 