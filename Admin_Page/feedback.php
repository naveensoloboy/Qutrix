<?php
require 'db.php'; // MongoDB connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data safely
    $name = trim($_POST['name']);
    $college_name = trim($_POST['collegename']);
    $opinion = trim($_POST['opinion']);
    $experience = trim($_POST['experience']);
    $organization = trim($_POST['organization']);
    $comments = trim($_POST['comments']);

    // Prepare document to insert
    $feedbackData = [
        "name" => $name,
        "college_name" => $college_name,
        "opinion" => $opinion,
        "experience" => $experience,
        "organization" => $organization,
        "comments" => $comments,
        "submission_date" => date("Y-m-d H:i:s") // add timestamp
    ];

    try {
        $collection = $db->feedback; // select feedback collection
        $insertResult = $collection->insertOne($feedbackData);

        if ($insertResult->getInsertedCount() > 0) {
            echo "Thank you for your feedback!";
        } else {
            echo "Error: Unable to save feedback.";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
