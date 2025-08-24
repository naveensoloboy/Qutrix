<?php
// db.php - Secure database connection

// Check if we're in development or production
$isDevelopment = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1');

if ($isDevelopment) {
    // Local development settings
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "qutrix";
} else {
    // Production settings - Load from environment or secure config
    $servername = "sql212.infinityfree.com";
    $username = "if0_39780640";
    
    // For production, use environment variables or a separate config file
    // that is NOT committed to Git
    $password = getenv('DBQutrix'); // Recommended: Use environment variables
    $dbname = "if0_39780640_intercollegiatemeetqutrix";
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    if ($isDevelopment) {
        // Show detailed error in development
        die("Connection failed: " . $conn->connect_error);
    } else {
        // Generic error in production
        error_log("Database connection error: " . $conn->connect_error);
        die("Database connection error. Please try again later.");
    }
}