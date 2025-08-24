<?php

session_start();

include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_username = trim($_POST['username']);
    $admin_password = $_POST['password'];

    $sql = "SELECT id, password FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $admin_username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($admin_id, $password);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if ($admin_password==$password) { 
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['admin_username'] = $admin_username;
            echo "<script>alert('Login successful! Redirecting...');</script>";
            header("refresh:1;url=registration_data.php"); // Redirect to the admin dashboard
        } else {
            echo "<script>alert('Invalid password!');</script>";
        }
    } else {
        echo "<script>alert('Admin not found!');</script>";
    }

    $stmt->close();
}
$conn->close();
?>
