<?php

require 'vendor/autoload.php'; // MongoDB library

// Connect to MongoDB
$client = new MongoDB\Client("mongodb+srv://admin:qutrixpass2025@cluster1.duscp.mongodb.net/?retryWrites=true&w=majority&appName=Cluster1");
$collection = $client->Qutrix->registrations;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Maximum file size limit (900 KB)
$maxFileSize = 900000;

// Function to validate file size
function validateFileSize($file, $maxFileSize) {
    return $file['size'] <= $maxFileSize;
}

// File size checks
if (!validateFileSize($_FILES["first_member_bonafide"], $maxFileSize)) {
    die("<br><br><b>The first member's bonafide file exceeds the size limit of 900 KB.</b>");
}
if (isset($_FILES["second_member_bonafide"]) && !validateFileSize($_FILES["second_member_bonafide"], $maxFileSize)) {
    die("<br><br><b>The second member's bonafide file exceeds the size limit of 900 KB.</b>");
}
if (isset($_FILES["third_member_bonafide"]) && !validateFileSize($_FILES["third_member_bonafide"], $maxFileSize)) {
    die("<br><br><b>The third member's bonafide file exceeds the size limit of 900 KB.</b>");
}
if (isset($_FILES["fourth_member_bonafide"]) && !validateFileSize($_FILES["fourth_member_bonafide"], $maxFileSize)) {
    die("<br><br><b>The fourth member's bonafide file exceeds the size limit of 900 KB.</b>");
}

// Collect form data
$college_name = $_POST['collegename'];
$department = $_POST['department'] === 'Others' ? $_POST['other_department'] : $_POST['department'];
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
$events = $_POST['event'] ?? []; 
$currentDateTime = new MongoDB\BSON\UTCDateTime();

// Check duplicate registration (same dept, same college, same event)
foreach ($events as $event) {
    $exists = $collection->findOne([
        "college_name" => $college_name,
        "department" => $department,
        "events" => $event
    ]);
    if ($exists) {
        die("<br><br><b>A team from your department has already registered for event: $event. Only one team per department is allowed per event.</b>");
    }
}

if (empty($events)) {
    die("<br><br><b>Please select at least one event.</b>");
}

// Upload file handler
function uploadFile($file, $target_dir = "uploads/") {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if (!getimagesize($file["tmp_name"])) return null;
    if (!in_array($imageFileType, ["jpg","jpeg","png","gif"])) return null;
    return move_uploaded_file($file["tmp_name"], $target_file) ? $target_file : null;
}

// Handle uploads
$first_member_bonafide = uploadFile($_FILES["first_member_bonafide"]);
$second_member_bonafide = isset($_FILES["second_member_bonafide"]) ? uploadFile($_FILES["second_member_bonafide"]) : null;
$third_member_bonafide = ($third_member_name && isset($_FILES["third_member_bonafide"])) ? uploadFile($_FILES["third_member_bonafide"]) : null;
$fourth_member_bonafide = ($fourth_member_name && isset($_FILES["fourth_member_bonafide"])) ? uploadFile($_FILES["fourth_member_bonafide"]) : null;

// Conflict pairs
$conflicting_event_pairs = [
    "QUIZ" => ["WEB DESIGN","NON TECHNICAL ROUND DANCING"],
    "WEB DESIGN" => ["QUIZ","NON TECHNICAL ROUND DANCING"],
    "MARKETING" => ["NON TECHNICAL ROUND DANCING"],
    "SOFTWARE CONTEST" => ["WORD HUNT", "NON TECHNICAL ROUND DANCING"],
    "WORD HUNT" => ["SOFTWARE CONTEST", "NON TECHNICAL ROUND DANCING"],
    "NON TECHNICAL ROUND DANCING" => ["QUIZ", "WEB DESIGN", "WORD HUNT", "MARKETING", "SOFTWARE CONTEST"],
];

// Check conflicts
function get_conflicting_event($collection, $roll_no, $conflicting_events) {
    if (!$roll_no) return false;
    $docs = $collection->find([
        '$or' => [
            ["first_member_rollno"=>$roll_no],
            ["second_member_rollno"=>$roll_no],
            ["third_member_rollno"=>$roll_no],
            ["fourth_member_rollno"=>$roll_no]
        ]
    ]);
    foreach ($docs as $doc) {
        foreach ($doc['events'] as $ev) {
            if (in_array($ev, $conflicting_events)) return $ev;
        }
    }
    return false;
}

function check_event_limit($collection, $roll_no) {
    if (!$roll_no) return false;
    $docs = $collection->find([
        '$or' => [
            ["first_member_rollno"=>$roll_no],
            ["second_member_rollno"=>$roll_no],
            ["third_member_rollno"=>$roll_no],
            ["fourth_member_rollno"=>$roll_no]
        ]
    ]);
    $count = 0;
    foreach ($docs as $doc) $count += count($doc['events']);
    return $count >= 2;
}

$all_roll_numbers = [$first_member_roll_no,$second_member_roll_no,$third_member_roll_no,$fourth_member_roll_no];

foreach ($all_roll_numbers as $roll_no) {
    foreach ($events as $event) {
        if (!$roll_no) continue;
        $exists = $collection->findOne([
            '$or' => [
                ["first_member_rollno"=>$roll_no],
                ["second_member_rollno"=>$roll_no],
                ["third_member_rollno"=>$roll_no],
                ["fourth_member_rollno"=>$roll_no]
            ],
            "events"=>$event
        ]);
        if ($exists) {
            die("<br><br><b>Roll number $roll_no has already registered for the event: $event.</b>");
        }
        if (isset($conflicting_event_pairs[$event])) {
            $conflict = get_conflicting_event($collection, $roll_no, $conflicting_event_pairs[$event]);
            if ($conflict) {
                die("<br><br><b>Roll number $roll_no cannot register for $event because already registered for $conflict.</b>");
            }
        }
        if (check_event_limit($collection, $roll_no)) {
            die("<br><br><b>Roll number $roll_no has already registered for two events.</b>");
        }
    }
}

// Insert registration
$collection->insertOne([
    "college_name"=>$college_name,
    "department"=>$department,
    "first_member_bonafide"=>$first_member_bonafide,
    "second_member_bonafide"=>$second_member_bonafide,
    "third_member_bonafide"=>$third_member_bonafide,
    "fourth_member_bonafide"=>$fourth_member_bonafide,
    "events"=>$events,
    "first_member_name"=>$first_member_name,
    "first_member_rollno"=>$first_member_roll_no,
    "second_member_name"=>$second_member_name,
    "second_member_rollno"=>$second_member_roll_no,
    "third_member_name"=>$third_member_name,
    "third_member_rollno"=>$third_member_roll_no,
    "fourth_member_name"=>$fourth_member_name,
    "fourth_member_rollno"=>$fourth_member_roll_no,
    "phone_no"=>$phone_no,
    "alt_phone_no"=>$alt_phone_no,
    "email"=>$email,
    "created_at"=>$currentDateTime
]);

// WhatsApp links
$whatsapp_links = [
    "PAPER PRESENTATION"=>"https://chat.whatsapp.com/H7m0gMxsiTSGwshDo7wt4q",
    "QUIZ"=>"https://chat.whatsapp.com/HG12g7tu72l7Hg0NMEQkP1",
    "WEB DESIGN"=>"https://chat.whatsapp.com/K62TaS736mOJvWHpR7OTXd",
    "MARKETING"=>"https://chat.whatsapp.com/EvLsT7oeohUC6x25QY6dI3",
    "SOFTWARE CONTEST"=>"https://chat.whatsapp.com/KIVg2FPShbhHO8Iyhp6tIu",
    "WORD HUNT"=>"https://chat.whatsapp.com/I9kzki1o8Js2CHKH1d2v6j",
    "NON TECHNICAL ROUND DANCING"=>"https://chat.whatsapp.com/HzQX1lKZLkM3iILgGw4Jep"
];

echo "<br><br><b>Your Registration Was Successful</b><br>";

foreach (array_unique($events) as $event) {
    if (isset($whatsapp_links[$event])) {
        echo"<script>alert('Please Join the Whatsapp Link');</script>";
        echo "<br>Join our WhatsApp group for the event <a href='".$whatsapp_links[$event]."' target='_blank'>$event</a><br>";
    }
}

?>
