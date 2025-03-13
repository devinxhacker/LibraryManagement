<?php
    $connection = mysqli_connect('localhost', 'root', '28092008', 'lms', '3306');
    if(!$connection){
        die('Connection failed: '.mysqli_connect_error());
    }
    echo 'Connected successfully';

    $sql = "SELECT * FROM books";
//     Table: books
// Columns:
// book_id int AI PK 
// book_name varchar(250) 
// author_id int 
// cat_id int 
// book_no int 
// book_price int

    $result = mysqli_query($connection, $sql);

    if(!$result){
        die('Query failed: '.mysqli_error($connection));
    }
echo '<br>';
    while($row = mysqli_fetch_array($result)){
        echo $row['book_id'].' '.$row['book_name'].' '.$row['author_id'].' '.$row['cat_id'].' '.$row['book_no'].' '.$row['book_price'].'<br>';
    }
    mysqli_free_result($result);


    mysqli_close($connection);
?>