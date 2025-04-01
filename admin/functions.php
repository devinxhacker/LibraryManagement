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
    
    $query = "SELECT * FROM activity_logs ORDER BY timestamp DESC LIMIT 10";
    $result = mysqli_query($conn, $query);
    
    $activities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $activities[] = [
            'description' => $row['description'],
            'timestamp' => date('M d, Y H:i', strtotime($row['timestamp'])),
            'type' => $row['activity_type'],
            'user_type' => $row['user_type'],
            'details' => json_decode($row['details'], true)
        ];
    }
    
    return $activities;
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
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get book and reader details for logging
        $book_query = "SELECT b.title, b.ISBN, r.fname, r.lname, r.email 
                      FROM books b 
                      JOIN readers r ON r.readerID = ? 
                      WHERE b.bookID = ?";
        $stmt = mysqli_prepare($conn, $book_query);
        mysqli_stmt_bind_param($stmt, "ii", $reader_id, $book_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $details = mysqli_fetch_assoc($result);
        
        // Insert into issued_books
        $query = "INSERT INTO issued_books (bookID, readerID, issuedate) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iis", $book_id, $reader_id, $issue_date);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log admin activity
            $admin_description = "Book '{$details['title']}' (ISBN: {$details['ISBN']}) issued to {$details['fname']} {$details['lname']}";
            log_activity('book_issue', $admin_description, 'admin', $_SESSION['email'], $details);
            
            // Log reader activity
            $reader_description = "Book '{$details['title']}' (ISBN: {$details['ISBN']}) issued";
            log_activity('book_issue', $reader_description, 'reader', $details['email'], $details);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error issuing book: " . $e->getMessage());
        return false;
    }
}

// Return a book
function return_book($issue_id) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get book and reader details for logging
        $details_query = "SELECT b.title, b.ISBN, r.fname, r.lname, r.email, i.issuedate, i.duedate 
                         FROM issued_books i 
                         JOIN books b ON b.bookID = i.bookID 
                         JOIN readers r ON r.readerID = i.readerID 
                         WHERE i.issue_id = ?";
        $stmt = mysqli_prepare($conn, $details_query);
        mysqli_stmt_bind_param($stmt, "i", $issue_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $details = mysqli_fetch_assoc($result);
        
        // Delete from issued_books
        $query = "DELETE FROM issued_books WHERE issue_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $issue_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "Book '{$details['title']}' (ISBN: {$details['ISBN']}) returned by {$details['fname']} {$details['lname']}";
            log_activity('book_return', $description, 'admin', $_SESSION['email'], $details);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error returning book: " . $e->getMessage());
        return false;
    }
}

// Add new book
function add_book($isbn, $author_id, $publisher_id, $title, $edition, $category_id, $price) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert into books table
        $query = "INSERT INTO books (ISBN, authorID, publisherID, title, edition, categoryID, price) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "siissid", $isbn, $author_id, $publisher_id, $title, $edition, $category_id, $price);
        
        if (mysqli_stmt_execute($stmt)) {
            $book_id = mysqli_insert_id($conn);
            
            // Get book details for logging
            $details_query = "SELECT b.*, a.authorName, p.publisherName, c.categoryName 
                            FROM books b 
                            JOIN authors a ON a.authorID = b.authorID 
                            JOIN publishers p ON p.publisherID = b.publisherID 
                            JOIN categories c ON c.categoryID = b.categoryID 
                            WHERE b.bookID = ?";
            $stmt = mysqli_prepare($conn, $details_query);
            mysqli_stmt_bind_param($stmt, "i", $book_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $details = mysqli_fetch_assoc($result);
            
            // Log the activity
            $description = "New book added: '{$title}' by {$details['authorName']}";
            log_activity('book_add', $description, 'admin', $_SESSION['email'], $details);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error adding book: " . $e->getMessage());
        return false;
    }
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
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get book details for logging
        $details_query = "SELECT b.*, a.authorName, p.publisherName, c.categoryName 
                         FROM books b 
                         JOIN authors a ON a.authorID = b.authorID 
                         JOIN publishers p ON p.publisherID = b.publisherID 
                         JOIN categories c ON c.categoryID = b.categoryID 
                         WHERE b.bookID = ?";
        $stmt = mysqli_prepare($conn, $details_query);
        mysqli_stmt_bind_param($stmt, "i", $book_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $details = mysqli_fetch_assoc($result);
        
        // Delete from books table
        $query = "DELETE FROM books WHERE bookID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $book_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "Book deleted: '{$details['title']}' by {$details['authorName']}";
            log_activity('book_delete', $description, 'admin', $_SESSION['email'], $details);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error deleting book: " . $e->getMessage());
        return false;
    }
}

// Add new category
function add_category($category_name) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        $query = "INSERT INTO categories (categoryName) VALUES (?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $category_name);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "New category added: {$category_name}";
            log_activity('category_add', $description, 'admin', $_SESSION['email']);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error adding category: " . $e->getMessage());
        return false;
    }
}

// Update category
function update_category($category_id, $category_name) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get current category name for logging
        $query = "SELECT categoryName FROM categories WHERE categoryID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $category_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $old_data = mysqli_fetch_assoc($result);
        
        $query = "UPDATE categories SET categoryName = ? WHERE categoryID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $category_name, $category_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "Category updated: {$old_data['categoryName']} → {$category_name}";
            $details = [
                'category_id' => $category_id,
                'old_name' => $old_data['categoryName'],
                'new_name' => $category_name
            ];
            log_activity('category_update', $description, 'admin', $_SESSION['email'], $details);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error updating category: " . $e->getMessage());
        return false;
    }
}

// Delete category
function delete_category($category_id) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get category name for logging
        $query = "SELECT categoryName FROM categories WHERE categoryID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $category_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $category = mysqli_fetch_assoc($result);
        
        if (!$category) {
            throw new Exception("Category not found");
        }
        
        // Check if category has any books
        if (!can_delete_category($category_id)) {
            throw new Exception("Cannot delete category with books");
        }
        
        $query = "DELETE FROM categories WHERE categoryID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $category_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "Category deleted: {$category['categoryName']}";
            log_activity('category_delete', $description, 'admin', $_SESSION['email'], $category);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error deleting category: " . $e->getMessage());
        return false;
    }
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
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get current reader details for logging
        $query = "SELECT * FROM readers WHERE readerID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $old_data = mysqli_fetch_assoc($result);
        
        // Update reader profile
        $query = "UPDATE readers SET fname = ?, lname = ?, email = ?, address = ?, phone_no = ? 
                  WHERE readerID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssi", $fname, $lname, $email, $address, $phone_no, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "Reader profile updated: {$fname} {$lname}";
            $details = [
                'reader_id' => $user_id,
                'old_data' => $old_data,
                'new_data' => [
                    'fname' => $fname,
                    'lname' => $lname,
                    'email' => $email,
                    'address' => $address,
                    'phone_no' => $phone_no
                ]
            ];
            log_activity('reader_profile_update', $description, 'reader', $email, $details);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error updating reader profile: " . $e->getMessage());
        return false;
    }
}

// Change password
function change_password($user_id, $new_password) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get reader details for logging
        $query = "SELECT r.*, a.loginID FROM readers r 
                  JOIN auth a ON r.loginID = a.loginID 
                  WHERE r.readerID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $reader = mysqli_fetch_assoc($result);
        
        if (!$reader) {
            throw new Exception("Reader not found");
        }
        
        // Update password in auth table
        $query = "UPDATE auth SET password = ? WHERE loginID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $new_password, $reader['loginID']);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "Reader password changed: {$reader['fname']} {$reader['lname']}";
            log_activity('reader_password_change', $description, 'reader', $reader['email']);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error changing reader password: " . $e->getMessage());
        return false;
    }
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
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get the loginID before deleting the admin
        $query = "SELECT loginID FROM admins WHERE adminID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $admin_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);
        
        if (!$admin) {
            throw new Exception("Admin not found");
        }
        
        $login_id = $admin['loginID'];
        
        // Delete from admins table
        $query = "DELETE FROM admins WHERE adminID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $admin_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error deleting admin");
        }
        
        // Delete from auth table
        $query = "DELETE FROM auth WHERE loginID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $login_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error deleting auth record");
        }
        
        mysqli_commit($conn);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error deleting admin: " . $e->getMessage());
        return false;
    }
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
    $query = "SELECT * FROM readers ORDER BY fname, lname";
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
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        $query = "INSERT INTO authors (authorName) VALUES (?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $author_name);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "New author added: {$author_name}";
            log_activity('author_add', $description, 'admin', $_SESSION['email']);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error adding author: " . $e->getMessage());
        return false;
    }
}

function update_author($author_id, $author_name) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get current author name for logging
        $query = "SELECT authorName FROM authors WHERE authorID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $author_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $old_data = mysqli_fetch_assoc($result);
        
        $query = "UPDATE authors SET authorName = ? WHERE authorID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $author_name, $author_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "Author updated: {$old_data['authorName']} → {$author_name}";
            $details = [
                'author_id' => $author_id,
                'old_name' => $old_data['authorName'],
                'new_name' => $author_name
            ];
            log_activity('author_update', $description, 'admin', $_SESSION['email'], $details);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error updating author: " . $e->getMessage());
        return false;
    }
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
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get author name for logging
        $query = "SELECT authorName FROM authors WHERE authorID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $author_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $author = mysqli_fetch_assoc($result);
        
        if (!$author) {
            throw new Exception("Author not found");
        }
        
        // Check if author has any books
        if (has_author_books($author_id)) {
            throw new Exception("Cannot delete author with books");
        }
        
        $query = "DELETE FROM authors WHERE authorID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $author_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "Author deleted: {$author['authorName']}";
            log_activity('author_delete', $description, 'admin', $_SESSION['email'], $author);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error deleting author: " . $e->getMessage());
        return false;
    }
}

function add_publisher($publisher_name, $year_of_pub) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        $query = "INSERT INTO publishers (publisherName, year_of_pub) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $publisher_name, $year_of_pub);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "New publisher added: {$publisher_name}";
            $details = [
                'year_of_pub' => $year_of_pub
            ];
            log_activity('publisher_add', $description, 'admin', $_SESSION['email'], $details);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error adding publisher: " . $e->getMessage());
        return false;
    }
}

function update_publisher($publisher_id, $publisher_name, $year_of_pub) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get current publisher details for logging
        $query = "SELECT * FROM publishers WHERE publisherID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $publisher_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $old_data = mysqli_fetch_assoc($result);
        
        $query = "UPDATE publishers SET publisherName = ?, year_of_pub = ? WHERE publisherID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sii", $publisher_name, $year_of_pub, $publisher_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "Publisher updated: {$old_data['publisherName']} → {$publisher_name}";
            $details = [
                'publisher_id' => $publisher_id,
                'old_data' => $old_data,
                'new_data' => [
                    'publisherName' => $publisher_name,
                    'year_of_pub' => $year_of_pub
                ]
            ];
            log_activity('publisher_update', $description, 'admin', $_SESSION['email'], $details);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error updating publisher: " . $e->getMessage());
        return false;
    }
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
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get publisher details for logging
        $query = "SELECT * FROM publishers WHERE publisherID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $publisher_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $publisher = mysqli_fetch_assoc($result);
        
        if (!$publisher) {
            throw new Exception("Publisher not found");
        }
        
        // Check if publisher has any books
        if (has_publisher_books($publisher_id)) {
            throw new Exception("Cannot delete publisher with books");
        }
        
        $query = "DELETE FROM publishers WHERE publisherID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $publisher_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "Publisher deleted: {$publisher['publisherName']}";
            log_activity('publisher_delete', $description, 'admin', $_SESSION['email'], $publisher);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error deleting publisher: " . $e->getMessage());
        return false;
    }
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

function add_reader($fname, $lname, $email, $password, $phone_no, $address) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // First check if email exists in readers table
        if (email_exists($email)) {
            throw new Exception("Email already exists");
        }
        
        // Check if email exists in auth table
        $query = "SELECT COUNT(*) as count FROM auth WHERE emailID = ?";
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception("Error preparing email check: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "s", $email);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing email check: " . mysqli_stmt_error($stmt));
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['count'] > 0) {
            throw new Exception("Email already exists in the system");
        }
        
        // First insert into auth table
        $query = "INSERT INTO auth (password, emailID) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception("Error preparing auth insert: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "ss", $password, $email);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing auth insert: " . mysqli_stmt_error($stmt));
        }
        
        $login_id = mysqli_insert_id($conn);
        if (!$login_id) {
            throw new Exception("Error getting login ID: " . mysqli_error($conn));
        }
        
        // Then insert into readers table
        $query = "INSERT INTO readers (fname, lname, email, phone_no, address, loginID) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception("Error preparing reader insert: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "sssssi", $fname, $lname, $email, $phone_no, $address, $login_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing reader insert: " . mysqli_stmt_error($stmt));
        }
        
        // Log the activity
        $description = "New reader added: {$fname} {$lname}";
        $details = [
            'email' => $email,
            'phone' => $phone_no,
            'address' => $address
        ];
        log_activity('reader_add', $description, 'admin', $_SESSION['email'], $details);
        
        mysqli_commit($conn);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error adding reader: " . $e->getMessage());
        return false;
    }
}

function update_reader($reader_id, $fname, $lname, $email, $phone_no, $address) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get current reader details for logging
        $query = "SELECT * FROM readers WHERE readerID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $reader_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $old_data = mysqli_fetch_assoc($result);
        
        // Update reader
        $query = "UPDATE readers SET fname = ?, lname = ?, email = ?, phone_no = ?, address = ? WHERE readerID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssi", $fname, $lname, $email, $phone_no, $address, $reader_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log the activity
            $description = "Reader updated: {$fname} {$lname}";
            $details = [
                'reader_id' => $reader_id,
                'old_data' => $old_data,
                'new_data' => [
                    'fname' => $fname,
                    'lname' => $lname,
                    'email' => $email,
                    'phone_no' => $phone_no,
                    'address' => $address
                ]
            ];
            log_activity('reader_update', $description, 'admin', $_SESSION['email'], $details);
            
            mysqli_commit($conn);
            return true;
        }
        
        mysqli_rollback($conn);
        return false;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error updating reader: " . $e->getMessage());
        return false;
    }
}

function delete_reader($reader_id) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Check if reader has any issued books
        $query = "SELECT COUNT(*) as count FROM issued_books WHERE readerID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $reader_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['count'] > 0) {
            throw new Exception("Cannot delete reader with issued books");
        }
        
        // Get reader details for logging
        $query = "SELECT * FROM readers WHERE readerID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $reader_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $reader = mysqli_fetch_assoc($result);
        
        if (!$reader) {
            throw new Exception("Reader not found");
        }
        
        $login_id = $reader['loginID'];
        
        // Delete from readers table
        $query = "DELETE FROM readers WHERE readerID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $reader_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error deleting reader");
        }
        
        // Delete from auth table
        $query = "DELETE FROM auth WHERE loginID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $login_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error deleting auth record");
        }
        
        // Log the activity
        $description = "Reader deleted: {$reader['fname']} {$reader['lname']}";
        log_activity('reader_delete', $description, 'admin', $_SESSION['email'], $reader);
        
        mysqli_commit($conn);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error deleting reader: " . $e->getMessage());
        return false;
    }
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

function email_exists($email) {
    global $conn;
    
    $query = "SELECT COUNT(*) as count FROM readers WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'] > 0;
}

function update_password($loginID, $password) {
    global $conn;
    $query = "UPDATE auth SET password = ? WHERE loginID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $password, $loginID);
    return mysqli_stmt_execute($stmt);
}

function log_activity($activity_type, $description, $user_type, $user_email, $details = null) {
    global $conn;
    
    $query = "INSERT INTO activity_logs (activity_type, description, user_type, user_email, details) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($details) {
        $details_json = json_encode($details);
    } else {
        $details_json = null;
    }
    
    mysqli_stmt_bind_param($stmt, "sssss", $activity_type, $description, $user_type, $user_email, $details_json);
    return mysqli_stmt_execute($stmt);
}
?>
