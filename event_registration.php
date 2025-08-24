<?php

include 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Maximum file size limit (900 KB)
$maxFileSize = 900000;

// Function to validate file size
function validateFileSize($file, $maxFileSize) {
    if ($file['size'] > $maxFileSize) {
        return false; // File size exceeds the limit
    }
    return true;
}

// Check file sizes
if (!validateFileSize($_FILES["first_member_bonafide"], $maxFileSize)) {
    echo "<br><br><b>The first member's bonafide file exceeds the size limit of 900 KB.</b>";
    exit();
}

if (isset($_FILES["second_member_bonafide"]) && !validateFileSize($_FILES["second_member_bonafide"], $maxFileSize)) {
    echo "<br><br><b>The second member's bonafide file exceeds the size limit of 900 KB.</b>";
    exit();
}

if (isset($_FILES["third_member_bonafide"]) && !validateFileSize($_FILES["third_member_bonafide"], $maxFileSize)) {
    echo "<br><br><b>The third member's bonafide file exceeds the size limit of 900 KB.</b>";
    exit();
}

if (isset($_FILES["fourth_member_bonafide"]) && !validateFileSize($_FILES["fourth_member_bonafide"], $maxFileSize)) {
    echo "<br><br><b>The fourth member's bonafide file exceeds the size limit of 900 KB.</b>";
    exit();
}

// Proceed with the rest of your code after validation

$college_name = $_POST['collegename'];
$department = $_POST['department'];
if ($department === 'Others') {
    $department = $_POST['other_department'];
}
$first_member_name = $_POST['firstmembername'];
$first_member_roll_no = $_POST['firstmemberrno'];
$second_member_name = $_POST['secondmembername'] ?? null;
$second_member_roll_no = $_POST['secondmemberrno'] ?? null;
$third_member_name = $_POST['thirdmembername'] ?? null; 
$third_member_roll_no = $_POST['thirdmemberrno'] ?? null;
$fourth_member_name = $_POST['fourthmembername'] ?? null;
$fourth_member_roll_no = $_POST['fourthmemberrno'] ?? null;
$phone_no = $_POST['phoneno'];
$alt_phone_no = $_POST['altphoneno'];
$email = $_POST['email'];
// Ensure events is set and is an array
$events = isset($_POST['event']) ? $_POST['event'] : []; // Default to empty array if not set
$currentDateTime = date('Y-m-d H:i:s'); // Gets current date and time in the format "YYYY-MM-DD HH:MM:SS"

// Check if a team from the same department in the same college has already registered
$stmt = $conn->prepare("SELECT * FROM registrations WHERE college_name = ? AND department = ? AND FIND_IN_SET(?, events)");
foreach ($events as $event) {
    $stmt->bind_param("sss", $college_name, $department, $event);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<br><br><b>A team from your department has already registered for event: $event. Only one team per department is allowed per event.</b>";
        exit();
    }
}


$stmt->close();

// Check if any events are selected
if (empty($events)) {
    echo "<br><br><b>Please select at least one event.</b>";
    exit();
}

// Function to handle file uploads
function uploadFile($file, $target_dir = "uploads/") {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null; // File not uploaded, return null
    }

    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is an image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return null; // File is not an image
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        return null; // File format not allowed
    }

    // Attempt to move the uploaded file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        return null; // Error uploading file
    }
}

// Handle bonafide uploads for each member
$first_member_bonafide = isset($_FILES["first_member_bonafide"]) ? uploadFile($_FILES["first_member_bonafide"]) : null;
$second_member_bonafide = isset($_FILES["second_member_bonafide"]) ? uploadFile($_FILES["second_member_bonafide"]) : null;
$third_member_bonafide = ($third_member_name && isset($_FILES["third_member_bonafide"])) ? uploadFile($_FILES["third_member_bonafide"]) : null;
$fourth_member_bonafide = ($fourth_member_name && isset($_FILES["fourth_member_bonafide"])) ? uploadFile($_FILES["fourth_member_bonafide"]) : null;

// Define conflicting event pairs
$conflicting_event_pairs = [
    "QUIZ" => ["WEB DESIGN","NON TECHNICAL ROUND DANCING"],
    "WEB DESIGN" => ["QUIZ","NON TECHNICAL ROUND DANCING"],
    "MARKETING" => ["NON TECHNICAL ROUND DANCING"],
    "SOFTWARE CONTEST" => ["WORD HUNT", "NON TECHNICAL ROUND DANCING"],
    "WORD HUNT" => ["SOFTWARE CONTEST", "NON TECHNICAL ROUND DANCING"],
    "NON TECHNICAL ROUND DANCING" => ["QUIZ", "WEB DESIGN", "WORD HUNT", "MARKETING", "SOFTWARE CONTEST"],
];

// Function to check if a roll number is registered for conflicting events
function get_conflicting_event($conn, $roll_no, $conflicting_events) {
    if (empty($roll_no)) {
        return false; // Skip check if roll number is empty
    }
    $stmt = $conn->prepare("SELECT events FROM registrations WHERE (first_member_rollno = ? OR second_member_rollno = ? OR third_member_rollno = ? OR fourth_member_rollno = ?)");
    $stmt->bind_param("ssss", $roll_no, $roll_no, $roll_no, $roll_no);
    $stmt->execute();
    $result = $stmt->get_result();

    // Loop through all registered events for the roll number
    while ($row = $result->fetch_assoc()) {
        $registered_events = explode(', ', $row['events']);
        foreach ($conflicting_events as $conflicting_event) {
            if (in_array($conflicting_event, $registered_events)) {
                return $conflicting_event; // Return the conflicting event name
            }
        }
    }
    return false;
}

// Function to check if a roll number is already registered for more than two events
function check_event_limit($conn, $roll_no) {
    if (empty($roll_no)) {
        return false; // Skip check if roll number is empty
    }
    $stmt = $conn->prepare("SELECT COUNT(events) as event_count FROM registrations WHERE first_member_rollno = ? OR second_member_rollno = ? OR third_member_rollno = ? OR fourth_member_rollno = ?");
    $stmt->bind_param("ssss", $roll_no, $roll_no, $roll_no, $roll_no);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the count of registered events
    $row = $result->fetch_assoc();
    return $row['event_count'] >= 2; // Returns true if already registered for two or more events
}

$selected_events = is_array($events) ? $events : explode(', ', $events);
$all_roll_numbers = [$first_member_roll_no, $second_member_roll_no, $third_member_roll_no, $fourth_member_roll_no];

// Check each roll number for conflicts and event limit
foreach ($all_roll_numbers as $roll_no) {
    foreach ($selected_events as $selected_event) {
        // Skip conflict check if roll number is empty
        if (empty($roll_no)) {
            continue;
        }

        // Check if the roll number is already registered for the same event
        $stmt = $conn->prepare("SELECT * FROM registrations WHERE (first_member_rollno = ? OR second_member_rollno = ? OR third_member_rollno = ? OR fourth_member_rollno = ?) AND FIND_IN_SET(?, events) > 0");
        $stmt->bind_param("sssss", $roll_no, $roll_no, $roll_no, $roll_no, $selected_event);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<br><br><b>Roll number $roll_no has already registered for the event: $selected_event.</b>";
            exit();
        }

        // Check for conflicting event selections from previous registrations
        if (isset($conflicting_event_pairs[$selected_event])) {
            $conflicting_event = get_conflicting_event($conn, $roll_no, $conflicting_event_pairs[$selected_event]);
            if ($conflicting_event) {
                echo "<br><br><b>Roll number $roll_no cannot register for the event: $selected_event because already registered for the event: $conflicting_event.</b>";
                exit();
            }
        }

        // Check if the roll number is already registered for more than two events
        if (check_event_limit($conn, $roll_no)) {
            echo "<br><br><b>Roll number $roll_no has already registered for two events and cannot register for any more events.</b>";
            exit();
        }
    }
}

// If no conflicts are found, insert the registration into the database

// Convert events array to string for storage
$events_string = implode(', ', $events);

// Insert data into the database
$stmt = $conn->prepare("INSERT INTO registrations (college_name, department, first_member_bonafide, second_member_bonafide, third_member_bonafide, fourth_member_bonafide, events, first_member_name, first_member_rollno, second_member_name, second_member_rollno, third_member_name, third_member_rollno, fourth_member_name, fourth_member_rollno, phone_no, alt_phone_no, email, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$events_string = implode(', ', $selected_events);

// Prepare values for binding
$bind_params = [
    $college_name, 
    $department, 
    $first_member_bonafide ?? '', // Convert null to empty string
    $second_member_bonafide ?? '', // Convert null to empty string
    $third_member_bonafide ?? '', // Convert null to empty string
    $fourth_member_bonafide ?? '', // Convert null to empty string
    $events_string, 
    $first_member_name, 
    $first_member_roll_no, 
    $second_member_name, 
    $second_member_roll_no, 
    $third_member_name, 
    $third_member_roll_no, 
    $fourth_member_name, 
    $fourth_member_roll_no, 
    $phone_no, 
    $alt_phone_no, 
    $email, 
    $currentDateTime
];

$types = str_repeat('s', count($bind_params)); // Generate string of 's' for each parameter
$stmt->bind_param($types, ...$bind_params);

$whatsapp_links = [
    "PAPER PRESENTATION"=>"https://chat.whatsapp.com/H7m0gMxsiTSGwshDo7wt4q",
    "QUIZ" => "https://chat.whatsapp.com/HG12g7tu72l7Hg0NMEQkP1",
    "WEB DESIGN" => "https://chat.whatsapp.com/K62TaS736mOJvWHpR7OTXd",
    "MARKETING" => "https://chat.whatsapp.com/EvLsT7oeohUC6x25QY6dI3",
    "SOFTWARE CONTEST" => "https://chat.whatsapp.com/KIVg2FPShbhHO8Iyhp6tIu",
    "WORD HUNT" => "https://chat.whatsapp.com/I9kzki1o8Js2CHKH1d2v6j",
    "NON TECHNICAL ROUND DANCING" => "https://chat.whatsapp.com/HzQX1lKZLkM3iILgGw4Jep"
];

if ($stmt->execute()) {
    echo "<br><br><b>Your Registration Was Successful</b><br>";

    // Generate a list of unique events the user registered for
    $selected_events = is_array($events) ? $events : explode(', ', $events);
    $unique_events = array_unique($selected_events);

    // Display WhatsApp links for each event the user registered for
    foreach ($unique_events as $event) {
        if (isset($whatsapp_links[$event])) {
            echo"<script>alert('Please Join the Whatsapp Link');</script>";
            echo "<br>Join our WhatsApp group for the event  <a href='" . $whatsapp_links[$event] . "' target='_blank'> $event</a><br>";
        }
    }
} else {
    echo "<br><br><b>Error: " . $stmt->error . "</b>";
}

$stmt->close();
$conn->close();

?>
