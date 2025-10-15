<?php
// includes/db.php - Database Connection

$host = 'localhost';
$user = 'root'; // Change if your DB user is different
$pass = '';
$dbname = 'school_db'; // Change if your DB name is different

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Set charset to utf8mb4 for full Unicode support
$conn->set_charset('utf8mb4');
?> 