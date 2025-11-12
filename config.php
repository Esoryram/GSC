<?php
$servername = "localhost";
$username   = "root";   
$password   = "";      
$dbname     = "gsc"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection with better error handling
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please try again later.");
}

// Set charset to prevent issues
$conn->set_charset("utf8mb4");
?>