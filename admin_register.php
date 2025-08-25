<?php
require 'db.php'; // MongoDB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $auth_key = trim($_POST['auth_key']);
    $event = trim($_POST['event']); // Capture the event from the form

    // ✅ Check if authentication key matches "secretkey"
    if ($auth_key !== "secretkey") {
        echo "<script>alert('Invalid Authentication Key!'); window.location.href='admin_register.html';</script>";
        exit();
    }

    // ✅ Check if username already exists in MongoDB
    $existingAdmin = $db->admin->findOne(['username' => $username]);
    if ($existingAdmin) {
        die("<script>alert('Username already exists! Choose a different one.'); window.history.back();</script>");
    }

    // ✅ Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Insert into MongoDB
    $insertResult = $db->admin->insertOne([
        'username' => $username,
        'password' => $hashed_password,
        'event'    => $event,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    if ($insertResult->getInsertedCount() > 0) {
        echo "<script>alert('Admin Registered Successfully!'); window.location.href='admin_login_form.html';</script>";
    } else {
        echo "<script>alert('Sorry, Unable to Register. Please Try Again!'); window.location.href='admin_register.html';</script>";
    }
}
?>
