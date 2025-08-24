<?php
require 'db.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $auth_key = trim($_POST['auth_key']);
    $event = trim($_POST['event']); // Capture the event from the form

    // Check if authentication key matches "secretkey"
    if ($auth_key !== "secretkey") {
        echo "<script>alert('Invalid Authentication Key!'); window.location.href='admin_register.html';</script>";
        exit();
    }

    // Check if username already exists
    $query = "SELECT id FROM admin WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("<script>alert('Username already exists! Choose a different one.'); window.history.back();</script>");
    }
    
    $stmt->close();

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into the database
    $sql = "INSERT INTO admin (username, password, event) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $event);

    if ($stmt->execute()) {
        echo "<script>alert('Admin Registered Successfully!'); window.location.href='admin_login_form.html';</script>";
    } else {
        echo "<script>alert('Sorry, Unable to Register. Please Try Again!'); window.location.href='admin_register.html';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
