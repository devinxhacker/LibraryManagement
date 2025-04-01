<?php
require_once(__DIR__ . '/../config/database.php');

// Get total user count
function get_user_count() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM readers";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Get total book count
function get_book_count() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM books";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Get total category count
function get_category_count() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM categories";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Get total issued books count
function get_issue_book_count() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM issued_books";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Get overdue books count
function get_overdue_books_count() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM issued_books WHERE duedate < CURRENT_DATE";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Get recent activities
function get_recent_activities() {
    global $conn;
    $activities = array();
    
    // Get recent book issues
    $query = "SELECT b.title, r.fname, r.lname, i.issuedate 
              FROM issued_books i 
              JOIN books b ON i.bookID = b.bookID 
              JOIN readers r ON i.readerID = r.readerID 
              ORDER BY i.issuedate DESC LIMIT 5";
    $result = mysqli_query($conn, $query);
    
    while($row = mysqli_fetch_assoc($result)) {
        $activities[] = array(
            'description' => "Book '{$row['title']}' issued to {$row['fname']} {$row['lname']}",
            'timestamp' => date('M d, Y', strtotime($row['issuedate']))
        );
    }
    
    // Get recent book returns
    $query = "SELECT b.title, r.fname, r.lname, i.duedate 
              FROM issued_books i 
              JOIN books b ON i.bookID = b.bookID 
              JOIN readers r ON i.readerID = r.readerID 
              WHERE i.duedate <= CURRENT_DATE 
              ORDER BY i.duedate DESC LIMIT 5";
    $result = mysqli_query($conn, $query);
    
    while($row = mysqli_fetch_assoc($result)) {
        $activities[] = array(
            'description' => "Book '{$row['title']}' returned by {$row['fname']} {$row['lname']}",
            'timestamp' => date('M d, Y', strtotime($row['duedate']))
        );
    }
    
    // Sort activities by timestamp
    usort($activities, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    // Return only the 5 most recent activities
    return array_slice($activities, 0, 5);
}

// Get books by category
function get_books_by_category($category_id) {
    global $conn;
    $query = "SELECT b.*, a.authorName, c.categoryName, p.publisherName 
              FROM books b 
              JOIN authors a ON b.authorID = a.authorID 
              JOIN categories c ON b.categoryID = c.categoryID 
              JOIN publishers p ON b.publisherID = p.publisherID 
              WHERE b.categoryID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
}

// Get user's issued books
function get_user_issued_books($user_id) {
    global $conn;
    $query = "SELECT b.*, i.issuedate, i.duedate, i.delaydays, i.fines 
              FROM issued_books i 
              JOIN books b ON i.bookID = b.bookID 
              WHERE i.readerID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
}

// Search books
function search_books($search_term) {
    global $conn;
    $search_term = "%$search_term%";
    $query = "SELECT b.*, a.authorName, c.categoryName, p.publisherName 
              FROM books b 
              JOIN authors a ON b.authorID = a.authorID 
              JOIN categories c ON b.categoryID = c.categoryID 
              JOIN publishers p ON b.publisherID = p.publisherID 
              WHERE b.title LIKE ? OR a.authorName LIKE ? OR c.categoryName LIKE ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $search_term, $search_term, $search_term);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
}

// Issue a book
function issue_book($book_id, $reader_id, $issue_date) {
    global $conn;
    $query = "INSERT INTO issued_books (bookID, readerID, issuedate) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iis", $book_id, $reader_id, $issue_date);
    return mysqli_stmt_execute($stmt);
}

// Return a book
function return_book($issue_id) {
    global $conn;
    $query = "DELETE FROM issued_books WHERE issue_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $issue_id);
    return mysqli_stmt_execute($stmt);
}

// Add new book
function add_book($isbn, $author_id, $publisher_id, $title, $edition, $category_id, $price) {
    global $conn;
    $query = "INSERT INTO books (ISBN, authorID, publisherID, title, edition, categoryID, price) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "siissid", $isbn, $author_id, $publisher_id, $title, $edition, $category_id, $price);
    return mysqli_stmt_execute($stmt);
}

// Update book
function update_book($book_id, $isbn, $author_id, $publisher_id, $title, $edition, $category_id, $price) {
    global $conn;
    $query = "UPDATE books SET ISBN = ?, authorID = ?, publisherID = ?, title = ?, 
              edition = ?, categoryID = ?, price = ? WHERE bookID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "siissidi", $isbn, $author_id, $publisher_id, $title, $edition, $category_id, $price, $book_id);
    return mysqli_stmt_execute($stmt);
}

// Delete book
function delete_book($book_id) {
    global $conn;
    $query = "DELETE FROM books WHERE bookID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $book_id);
    return mysqli_stmt_execute($stmt);
}

// Add new category
function add_category($category_name) {
    global $conn;
    $query = "INSERT INTO categories (categoryName) VALUES (?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $category_name);
    return mysqli_stmt_execute($stmt);
}

// Update category
function update_category($category_id, $category_name) {
    global $conn;
    $query = "UPDATE categories SET categoryName = ? WHERE categoryID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $category_name, $category_id);
    return mysqli_stmt_execute($stmt);
}

// Delete category
function delete_category($category_id) {
    global $conn;
    $query = "DELETE FROM categories WHERE categoryID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    return mysqli_stmt_execute($stmt);
}

// Get user profile
function get_user_profile($user_id) {
    global $conn;
    $query = "SELECT * FROM readers WHERE readerID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Update user profile
function update_user_profile($user_id, $fname, $lname, $email, $address, $phone_no) {
    global $conn;
    $query = "UPDATE readers SET fname = ?, lname = ?, email = ?, address = ?, phone_no = ? 
              WHERE readerID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssi", $fname, $lname, $email, $address, $phone_no, $user_id);
    return mysqli_stmt_execute($stmt);
}

// Change password
function change_password($user_id, $new_password) {
    global $conn;
    $query = "UPDATE auth SET password = ? WHERE loginID = (SELECT loginID FROM readers WHERE readerID = ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $new_password, $user_id);
    return mysqli_stmt_execute($stmt);
}

// Get admin profile
function get_admin_profile($email) {
    global $conn;
    $query = "SELECT * FROM admins WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Update admin profile
function update_admin_profile($email, $name, $mobile) {
    global $conn;
    $query = "UPDATE admins SET name = ?, mobile = ? WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $name, $mobile, $email);
    return mysqli_stmt_execute($stmt);
}

// Change admin password
function change_admin_password($email, $old_password, $new_password) {
    global $conn;
    
    // First verify old password
    $query = "SELECT a.* FROM admins a 
              JOIN auth au ON a.loginID = au.loginID 
              WHERE a.email = ? AND au.password = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $email, $old_password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Get the loginID
        $admin = mysqli_fetch_assoc($result);
        $login_id = $admin['loginID'];
        
        // Update password in auth table
        $query = "UPDATE auth SET password = ? WHERE loginID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $new_password, $login_id);
        return mysqli_stmt_execute($stmt);
    }
    
    return false;
}

// Add new admin
function add_admin($email, $fname, $lname, $address, $phone_no, $password) {
    global $conn;
    
    // First insert into auth table
    $query = "INSERT INTO auth (password, emailID) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $password, $email);
    mysqli_stmt_execute($stmt);
    $login_id = mysqli_insert_id($conn);
    
    // Then insert into admins table
    $query = "INSERT INTO admins (email, fname, lname, address, phone_no, loginID) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssi", $email, $fname, $lname, $address, $phone_no, $login_id);
    return mysqli_stmt_execute($stmt);
}

// Delete admin
function delete_admin($admin_id) {
    global $conn;
    
    // First get the loginID
    $query = "SELECT loginID FROM admins WHERE adminID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $login_id = $row['loginID'];
    
    // Delete from admins table
    $query = "DELETE FROM admins WHERE adminID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    
    // Delete from auth table
    $query = "DELETE FROM auth WHERE loginID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $login_id);
    return mysqli_stmt_execute($stmt);
}

// Get available books count
function get_available_books_count() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM books WHERE bookID NOT IN (SELECT bookID FROM issued_books)";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Get total author count
function get_author_count() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM authors";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Get total publisher count
function get_publisher_count() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM publishers";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Get total admin count
function get_admin_count() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM admins";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Get all issued books with details
function get_all_issued_books_with_details() {
    global $conn;
    $query = "SELECT i.*, b.title, b.ISBN, r.fname, r.lname, r.email, r.phone_no,
              a.authorName, c.categoryName, p.publisherName
              FROM issued_books i
              JOIN books b ON i.bookID = b.bookID
              JOIN readers r ON i.readerID = r.readerID
              JOIN authors a ON b.authorID = a.authorID
              JOIN categories c ON b.categoryID = c.categoryID
              JOIN publishers p ON b.publisherID = p.publisherID
              ORDER BY i.issuedate DESC";
    $result = mysqli_query($conn, $query);
    return $result;
}

// Get all books with details
function get_all_books_with_details() {
    global $conn;
    $query = "SELECT b.*, a.authorName, c.categoryName, p.publisherName, p.year_of_pub 
              FROM books b 
              JOIN authors a ON b.authorID = a.authorID 
              JOIN categories c ON b.categoryID = c.categoryID 
              JOIN publishers p ON b.publisherID = p.publisherID 
              ORDER BY b.title ASC";
    $result = mysqli_query($conn, $query);
    return $result;
}

// Get all categories
function get_all_categories() {
    global $conn;
    $query = "SELECT * FROM categories ORDER BY categoryName ASC";
    $result = mysqli_query($conn, $query);
    return $result;
}

// Get all available books (not issued)
function get_all_available_books() {
    global $conn;
    $query = "SELECT b.*, a.authorName, c.categoryName, p.publisherName 
              FROM books b 
              JOIN authors a ON b.authorID = a.authorID 
              JOIN categories c ON b.categoryID = c.categoryID 
              JOIN publishers p ON b.publisherID = p.publisherID 
              WHERE b.bookID NOT IN (SELECT bookID FROM issued_books)
              ORDER BY b.title ASC";
    $result = mysqli_query($conn, $query);
    return $result;
}

// Get all issued books
function get_all_issued_books() {
    global $conn;
    $query = "SELECT i.*, b.title, b.ISBN, r.fname, r.lname, r.email, r.phone_no,
              a.authorName, c.categoryName, p.publisherName
              FROM issued_books i
              JOIN books b ON i.bookID = b.bookID
              JOIN readers r ON i.readerID = r.readerID
              JOIN authors a ON b.authorID = a.authorID
              JOIN categories c ON b.categoryID = c.categoryID
              JOIN publishers p ON b.publisherID = p.publisherID
              WHERE i.duedate >= CURRENT_DATE
              ORDER BY i.issuedate DESC";
    $result = mysqli_query($conn, $query);
    return $result;
}

// Get all readers
function get_all_readers() {
    global $conn;
    $query = "SELECT * FROM readers ORDER BY fname ASC, lname ASC";
    $result = mysqli_query($conn, $query);
    return $result;
}

// Get all authors
function get_all_authors() {
    global $conn;
    $query = "SELECT * FROM authors ORDER BY authorName ASC";
    $result = mysqli_query($conn, $query);
    return $result;
}

// Get all publishers
function get_all_publishers() {
    global $conn;
    $query = "SELECT * FROM publishers ORDER BY publisherName ASC";
    $result = mysqli_query($conn, $query);
    return $result;
}

// Check if a book is available for issue
function is_book_available($book_id) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM issued_books WHERE bookID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $book_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] == 0;
}

// Book Management Functions
function get_book_by_id($book_id) {
    global $conn;
    $query = "SELECT b.*, a.authorName, c.categoryName, p.publisherName, p.year_of_pub 
              FROM books b 
              JOIN authors a ON b.authorID = a.authorID 
              JOIN categories c ON b.categoryID = c.categoryID 
              JOIN publishers p ON b.publisherID = p.publisherID 
              WHERE b.bookID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $book_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function add_author($author_name) {
    global $conn;
    $query = "INSERT INTO authors (authorName) VALUES (?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $author_name);
    return mysqli_stmt_execute($stmt);
}

function update_author($author_id, $author_name) {
    global $conn;
    $query = "UPDATE authors SET authorName = ? WHERE authorID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $author_name, $author_id);
    return mysqli_stmt_execute($stmt);
}

// Check if author has any books
function has_author_books($author_id) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM books WHERE authorID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $author_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
}

// Delete author
function delete_author($author_id) {
    global $conn;
    
    // Check if author has any books
    if (has_author_books($author_id)) {
        return false; // Cannot delete author with books
    }
    
    // If no books, proceed with deletion
    $query = "DELETE FROM authors WHERE authorID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $author_id);
    return mysqli_stmt_execute($stmt);
}

function add_publisher($publisher_name, $year_of_pub) {
    global $conn;
    $query = "INSERT INTO publishers (publisherName, year_of_pub) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $publisher_name, $year_of_pub);
    return mysqli_stmt_execute($stmt);
}

function update_publisher($publisher_id, $publisher_name, $year_of_pub) {
    global $conn;
    
    $sql = "UPDATE publishers SET publisherName = ?, year_of_pub = ? WHERE publisherID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $publisher_name, $year_of_pub, $publisher_id);
    
    return $stmt->execute();
}

// Check if publisher has any books
function has_publisher_books($publisher_id) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM books WHERE publisherID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $publisher_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
}

function delete_publisher($publisher_id) {
    global $conn;
    
    // Check if publisher has any books
    if (has_publisher_books($publisher_id)) {
        return false; // Cannot delete publisher with books
    }
    
    // If no books, proceed with deletion
    $query = "DELETE FROM publishers WHERE publisherID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $publisher_id);
    return mysqli_stmt_execute($stmt);
}

// Reader Management Functions
function get_reader_by_id($reader_id) {
    global $conn;
    $query = "SELECT * FROM readers WHERE readerID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $reader_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function add_reader($email, $fname, $lname, $address, $phone_no, $password) {
    global $conn;
    
    // First insert into auth table
    $query = "INSERT INTO auth (password, emailID) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $password, $email);
    mysqli_stmt_execute($stmt);
    $login_id = mysqli_insert_id($conn);
    
    // Then insert into readers table
    $query = "INSERT INTO readers (email, fname, lname, address, phone_no, loginID) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssi", $email, $fname, $lname, $address, $phone_no, $login_id);
    return mysqli_stmt_execute($stmt);
}

function update_reader($reader_id, $email, $fname, $lname, $address, $phone_no) {
    global $conn;
    $query = "UPDATE readers SET email = ?, fname = ?, lname = ?, address = ?, phone_no = ? 
              WHERE readerID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssi", $email, $fname, $lname, $address, $phone_no, $reader_id);
    return mysqli_stmt_execute($stmt);
}

function delete_reader($reader_id) {
    global $conn;
    
    // First get the loginID
    $query = "SELECT loginID FROM readers WHERE readerID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $reader_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $login_id = $row['loginID'];
    
    // Delete from readers table
    $query = "DELETE FROM readers WHERE readerID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $reader_id);
    mysqli_stmt_execute($stmt);
    
    // Delete from auth table
    $query = "DELETE FROM auth WHERE loginID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $login_id);
    return mysqli_stmt_execute($stmt);
}

// Admin Management Functions
function get_all_admins() {
    global $conn;
    $query = "SELECT * FROM admins ORDER BY fname ASC, lname ASC";
    $result = mysqli_query($conn, $query);
    return $result;
}

function get_admin_by_id($admin_id) {
    global $conn;
    $query = "SELECT * FROM admins WHERE adminID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function update_admin($admin_id, $email, $fname, $lname, $address, $phone_no) {
    global $conn;
    $query = "UPDATE admins SET email = ?, fname = ?, lname = ?, address = ?, phone_no = ? 
              WHERE adminID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssi", $email, $fname, $lname, $address, $phone_no, $admin_id);
    return mysqli_stmt_execute($stmt);
}

// Authentication Functions
function verify_login($email, $password) {
    global $conn;
    $query = "SELECT a.*, au.password, au.loginID 
              FROM auth au 
              LEFT JOIN readers r ON au.emailID = r.email 
              LEFT JOIN admins a ON au.emailID = a.email 
              WHERE au.emailID = ? AND au.password = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Book Management Functions
function get_all_books() {
    global $conn;
    
    $query = "SELECT b.*, a.authorName, c.categoryName, p.publisherName 
              FROM books b 
              LEFT JOIN authors a ON b.authorID = a.authorID 
              LEFT JOIN categories c ON b.categoryID = c.categoryID 
              LEFT JOIN publishers p ON b.publisherID = p.publisherID 
              ORDER BY b.title";
              
    $result = mysqli_query($conn, $query);
    if (!$result) {
        error_log("Error in get_all_books: " . mysqli_error($conn));
        return false;
    }
    
    return $result;
}

// Category Management Functions
function get_category_by_id($category_id) {
    global $conn;
    $query = "SELECT * FROM categories WHERE categoryID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function get_category_books_count($category_id) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM books WHERE categoryID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

function get_category_books($category_id) {
    global $conn;
    $query = "SELECT b.*, a.authorName, p.publisherName, 
              CASE WHEN ib.bookID IS NOT NULL THEN 1 ELSE 0 END as is_issued,
              ib.issuedate, ib.duedate, r.fname as reader_fname, r.lname as reader_lname
              FROM books b
              LEFT JOIN authors a ON b.authorID = a.authorID
              LEFT JOIN publishers p ON b.publisherID = p.publisherID
              LEFT JOIN issued_books ib ON b.bookID = ib.bookID
              LEFT JOIN readers r ON ib.readerID = r.readerID
              WHERE b.categoryID = ?
              ORDER BY b.title";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function can_delete_category($category_id) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM books WHERE categoryID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] == 0;
}

function get_all_categories_with_book_count() {
    global $conn;
    $query = "SELECT c.*, COUNT(b.bookID) as book_count 
              FROM categories c 
              LEFT JOIN books b ON c.categoryID = b.categoryID 
              GROUP BY c.categoryID 
              ORDER BY c.categoryName";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get author by ID
function get_author_by_id($author_id) {
    global $conn;
    $query = "SELECT * FROM authors WHERE authorID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $author_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Get publisher by ID
function get_publisher_by_id($publisher_id) {
    global $conn;
    $query = "SELECT * FROM publishers WHERE publisherID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $publisher_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

?>
