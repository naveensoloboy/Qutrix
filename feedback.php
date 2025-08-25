<?php
require 'db.php'; // MongoDB connection file

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data (no need for mysqli_real_escape_string in MongoDB)
    $name = $_POST['name'] ?? '';
    $college_name = $_POST['collegename'] ?? '';
    $opinion = $_POST['opinion'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $organization = $_POST['organization'] ?? '';
    $comments = $_POST['comments'] ?? '';

    // Insert data into MongoDB
    $collection = $db->feedback;
    $insertOneResult = $collection->insertOne([
        'name' => $name,
        'college_name' => $college_name,
        'opinion' => $opinion,
        'experience' => $experience,
        'organization' => $organization,
        'comments' => $comments,
        'submission_date' => new MongoDB\BSON\UTCDateTime() // auto timestamp
    ]);

    if ($insertOneResult->getInsertedCount() > 0) {
        echo "Thank you for your feedback!";
    } else {
        echo "Error: Unable to save feedback.";
    }
}
?>
