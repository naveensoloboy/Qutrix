<?php
session_start();

require 'vendor/autoload.php'; // MongoDB PHP Library
include "db.php"; // this will contain MongoDB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_username = trim($_POST['username']);
    $admin_password = $_POST['password'];

    // select collection
    $collection = $client->Qutrix->admin;

    // find admin by username
    $admin = $collection->findOne(['username' => $admin_username]);

    if ($admin) {
        // check hashed password
        if (password_verify($admin_password, $admin['password'])) {
            $_SESSION['admin_id'] = (string)$admin['_id']; // using MongoDB _id
            $_SESSION['admin_username'] = $admin_username;
            echo "<script>alert('Login successful! Redirecting...');window.location.href='registration_data.php';</script>";
            // header("refresh:1;url=registration_data.php");
        } else {
            echo "<script>alert('Invalid password!');window.location.href='admin_login_form.html';</script>";
        }
    } else {
        echo "<script>alert('Admin not found!');</script>";
    }
}
?>
