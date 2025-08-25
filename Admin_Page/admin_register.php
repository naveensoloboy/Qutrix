<?php
require 'db.php'; // Include your MongoDB connection ($db)

// Only handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $auth_key = trim($_POST['auth_key']);
    $event = trim($_POST['event']); // Capture the event from the form

    // Check authentication key
    if ($auth_key !== "secretkey") {
        echo "<script>alert('Invalid Authentication Key!'); window.location.href='admin_register.html';</script>";
        exit();
    }

    $collection = $db->admin;

    // Check if username already exists
    $existingUser = $collection->findOne(['username' => $username]);

    if ($existingUser) {
        die("<script>alert('Username already exists! Choose a different one.'); window.history.back();</script>");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new admin
    $insertResult = $collection->insertOne([
        'username' => $username,
        'password' => $hashed_password, // ✅ using hash now
        'event'    => $event
    ]);

    if ($insertResult->getInsertedCount() > 0) {
        echo "<script>alert('Admin Registered Successfully!'); window.location.href='admin_login_form.html';</script>";
    } else {
        echo "<script>alert('Sorry, Unable to Register. Please Try Again!'); window.location.href='admin_register.html';</script>";
    }
}
?>
