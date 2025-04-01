<?php
require_once('../config/database.php');

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
function get_admin_profile($admin_id) {
    global $conn;
    $query = "SELECT * FROM admins WHERE adminID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Update admin profile
function update_admin_profile($admin_id, $fname, $lname, $email, $address, $phone_no) {
    global $conn;
    $query = "UPDATE admins SET fname = ?, lname = ?, email = ?, address = ?, phone_no = ? 
              WHERE adminID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssi", $fname, $lname, $email, $address, $phone_no, $admin_id);
    return mysqli_stmt_execute($stmt);
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
?>
