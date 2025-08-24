<?php

include "db.php";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $college_name = mysqli_real_escape_string($conn, $_POST['collegename']);
    $opinion = mysqli_real_escape_string($conn, $_POST['opinion']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $organization = mysqli_real_escape_string($conn, $_POST['organization']);
    $comments = mysqli_real_escape_string($conn, $_POST['comments']);
    
    // Insert data into the database
    $sql = "INSERT INTO feedback (name, college_name, opinion, experience, organization, comments) 
            VALUES ('$name', '$college_name', '$opinion', '$experience', '$organization', '$comments')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Thank you for your feedback!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close connection
$conn->close();
?>
