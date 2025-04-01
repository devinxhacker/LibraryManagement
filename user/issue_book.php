<?php
require("../config/database.php");
require("../admin/functions.php");
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
$availability_query = "SELECT COUNT(*) as count FROM issued_books WHERE bookID = ?";
$stmt = mysqli_prepare($conn, $availability_query);
mysqli_stmt_bind_param($stmt, "i", $book_id);
mysqli_stmt_execute($stmt);
$availability_result = mysqli_stmt_get_result($stmt);
$availability = mysqli_fetch_assoc($availability_result);
$is_available = $availability['count'] == 0;

// Check if user already has this book
$issued_query = "SELECT * FROM issued_books WHERE bookID = ? AND readerID = ?";
$stmt = mysqli_prepare($conn, $issued_query);
mysqli_stmt_bind_param($stmt, "ii", $book_id, $user_id);
mysqli_stmt_execute($stmt);
$issued_result = mysqli_stmt_get_result($stmt);
$is_issued = mysqli_num_rows($issued_result) > 0;

if ($is_available && !$is_issued) {
    // Issue the book
    $issue_date = date('Y-m-d');
    
    // Get book and reader details for logging
    $details_query = "SELECT b.title, b.ISBN, r.fname, r.lname, r.email 
                      FROM books b 
                      JOIN readers r ON r.readerID = ? 
                      WHERE b.bookID = ?";
    $stmt = mysqli_prepare($conn, $details_query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $book_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $details = mysqli_fetch_assoc($result);
    
    // Insert into issued_books
    $issue_query = "INSERT INTO issued_books (bookID, readerID, issuedate) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $issue_query);
    mysqli_stmt_bind_param($stmt, "iis", $book_id, $user_id, $issue_date);
    
    if (mysqli_stmt_execute($stmt)) {
        // Log reader activity
        $description = "Book '{$details['title']}' (ISBN: {$details['ISBN']}) issued";
        log_activity('book_issue', $description, 'reader', $_SESSION['email'], $details);
        
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