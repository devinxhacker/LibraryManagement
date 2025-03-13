CREATE DATABASE lms;

USE lms;



-- Create the publishers table
CREATE TABLE publishers (
    publisherID INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    year_of_pub YEAR NOT NULL,
    publisherName VARCHAR(50) NOT NULL
);

-- Create the categories table
CREATE TABLE categories (
    categoryID INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    categoryName VARCHAR(20) NOT NULL
);

-- Create the authors table
CREATE TABLE authors (
    authorID INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    authorName VARCHAR(50) NOT NULL
);

-- Create the books table
CREATE TABLE books (
    bookID INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    ISBN VARCHAR(17) NOT NULL,
    authorID INT,
    publisherID INT,
    title VARCHAR(100) NOT NULL,
    edition INT NOT NULL,
    categoryID INT,
    price DECIMAL(10, 2),
    -- Foreign keys
    FOREIGN KEY (authorID) REFERENCES authors(authorID),
    FOREIGN KEY (publisherID) REFERENCES publishers(publisherID),
    FOREIGN KEY (categoryID) REFERENCES categories(categoryID)
);

-- Create the auth table for storing login credentials
CREATE TABLE auth (
    loginID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    password VARCHAR(50) NOT NULL
);

-- Create the readers table
CREATE TABLE readers (
    readerID INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    email VARCHAR(100) NOT NULL,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    address LONGTEXT,
    phone_no VARCHAR(10) NOT NULL,
    loginID INT,
    -- Foreign key
    FOREIGN KEY (loginID) REFERENCES auth(loginID)
);

-- Create the admins table
CREATE TABLE admins (
    adminID INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    email VARCHAR(100) NOT NULL,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    address LONGTEXT,
    phone_no VARCHAR(10) NOT NULL,
    loginID INT,
    -- Foreign key
    FOREIGN KEY (loginID) REFERENCES auth(loginID)
);


CREATE TABLE issued_books (
    issue_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    bookID INT,
    readerID INT,
    issuedate DATE NOT NULL,
    duedate DATE NOT NULL,
    delaydays INT,
    fines INT,
    -- Foreign keys
    FOREIGN KEY (bookID) REFERENCES books(bookID),
    FOREIGN KEY (readerID) REFERENCES readers(readerID)
);

-- Enable the event scheduler for automatic updates
SET GLOBAL event_scheduler = ON;

-- Create the before-insert trigger to calculate delaydays, fines, and duedate upon inserting a record
DELIMITER $$

CREATE TRIGGER before_insert_issued_books
BEFORE INSERT ON issued_books
FOR EACH ROW
BEGIN
    -- Set duedate automatically as 15 days after the issuedate
    SET NEW.duedate = DATE_ADD(NEW.issuedate, INTERVAL 15 DAY);
    
    -- Calculate delaydays and fines before inserting a new record
    SET NEW.delaydays = DATEDIFF(CURRENT_DATE, NEW.duedate);
    SET NEW.fines = CASE WHEN NEW.delaydays > 0 THEN 2 * NEW.delaydays ELSE 0 END;
END $$

DELIMITER ;

-- Create the before-update trigger to calculate delaydays and fines upon updating a record
DELIMITER $$

CREATE TRIGGER before_update_issued_books
BEFORE UPDATE ON issued_books
FOR EACH ROW
BEGIN
    -- Recalculate delaydays and fines before updating the record
    SET NEW.delaydays = DATEDIFF(CURRENT_DATE, NEW.duedate);
    SET NEW.fines = CASE WHEN NEW.delaydays > 0 THEN 2 * NEW.delaydays ELSE 0 END;
END $$

DELIMITER ;

SELECT * FROM issued_books;

INSERT INTO authors (authorName)
VALUES 
('J.K. Rowling'),
('George Orwell'),
('J.R.R. Tolkien'),
('Agatha Christie'),
('Leo Tolstoy');


INSERT INTO publishers (year_of_pub, publisherName)
VALUES 
(1997, 'Bloomsbury'),
(1949, 'Secker & Warburg'),
(1937, 'George Allen & Unwin'),
(1920, 'HarperCollins'),
(2000, 'The Russian Messenger');


INSERT INTO categories (categoryName)
VALUES 
('Fantasy'),
('Dystopian'),
('Adventure'),
('Mystery'),
('Historical Fiction');

select * from books;
truncate table publishers;

INSERT INTO books (ISBN, authorID, publisherID, title, edition, categoryID, price)
VALUES 
('9780747532743', 1, 1, 'Harry Potter and the Philosopher\'s Stone', 1, 1, 19.99),
('9780451524935', 2, 2, '1984', 1, 2, 14.99),
('9780261103573', 3, 3, 'The Hobbit', 1, 3, 12.99),
('9780062073488', 4, 4, 'Murder on the Orient Express', 1, 4, 10.99),
('9780307988406', 5, 5, 'War and Peace', 1, 5, 24.99);

INSERT INTO auth (password)
VALUES 
('password123'),
('securepass'),
('mypassword'),
('adminpass'),
('readerpass');

INSERT INTO readers (email, fname, lname, address, phone_no, loginID)
VALUES 
('john.doe@example.com', 'John', 'Doe', '123 Elm Street, Springfield', '1234567890', 1),
('jane.smith@example.com', 'Jane', 'Smith', '456 Oak Street, Rivertown', '2345678901', 2),
('alice.williams@example.com', 'Alice', 'Williams', '789 Pine Street, Lakeside', '3456789012', 3),
('bob.johnson@example.com', 'Bob', 'Johnson', '101 Maple Street, Hilltop', '4567890123', 4),
('carol.white@example.com', 'Carol', 'White', '202 Birch Street, Seaside', '5678901234', 5);

INSERT INTO admins (email, fname, lname, address, phone_no, loginID)
VALUES 
('admin1@example.com', 'Alice', 'Adams', '100 Admin Avenue', '6789012345', 1),
('admin2@example.com', 'Bob', 'Bates', '200 Admin Road', '7890123456', 2),
('admin3@example.com', 'Charlie', 'Carter', '300 Admin Street', '8901234567', 3),
('admin4@example.com', 'David', 'Davis', '400 Admin Blvd', '9012345678', 4),
('admin5@example.com', 'Eva', 'Evans', '500 Admin Lane', '0123456789', 5);

INSERT INTO issued_books (bookID, readerID, issuedate)
VALUES 
(1, 1, '2025-03-01'),  -- BookID 1 (Harry Potter) issued to ReaderID 1 (John)
(2, 2, '2025-03-02'),  -- BookID 2 (1984) issued to ReaderID 2 (Jane)
(3, 3, '2025-03-03'),  -- BookID 3 (The Hobbit) issued to ReaderID 3 (Alice)
(4, 4, '2025-03-04'),  -- BookID 4 (Murder on the Orient Express) issued to ReaderID 4 (Bob)
(5, 5, '2025-03-05');  -- BookID 5 (War and Peace) issued to ReaderID 5 (Carol)


INSERT INTO issued_books (bookID, readerID, issuedate)
VALUES (1, 1, '2025-03-01');


SELECT * FROM admins;
SELECT * FROM auth;
SELECT * FROM authors;
SELECT * FROM books;
SELECT * FROM categories;
SELECT * FROM issued_books;
SELECT * FROM publishers;
SELECT * FROM readers;