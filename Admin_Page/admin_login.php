<?php
session_start();
include "db.php"; // this should return a $db (MongoDB\Database) connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_username = trim($_POST['username']);
    $admin_password = $_POST['password'];

    // Select the collection
    $collection = $db->admin;

    // Find admin by username
    $admin = $collection->findOne(['username' => $admin_username]);

    if ($admin) {
        // Compare password (plain text check, same as original)
        if ($admin_password == $admin['password']) {
            $_SESSION['admin_id'] = (string)$admin['_id'];
            $_SESSION['admin_username'] = $admin['username'];
            echo "<script>alert('Login successful! Redirecting...');</script>";
            header("refresh:1;url=registration_data.php");
            exit();
        } else {
            echo "<script>alert('Invalid password!');</script>";
        }
    } else {
        echo "<script>alert('Admin not found!');</script>";
    }
}
?>
