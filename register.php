<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// MongoDB and Dompdf namespaces at the top
require 'vendor/autoload.php';
use MongoDB\Client;
use Dompdf\Dompdf;
use Dompdf\Options;

  use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Kolkata');


function show_error_page($message) {
    die("
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css'>
        <style>
            :root {
                --primary: #020617;
                --error: #ef4444;
                --accent: #fbbf24;
                --glass: rgba(255, 255, 255, 0.03);
                --glass-border: rgba(255, 255, 255, 0.1);
            }
            body {
                background-color: var(--primary);
                color: #fff;
                font-family: 'Segoe UI', sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .error-card {
                background: var(--glass);
                backdrop-filter: blur(15px);
                border: 1px solid var(--glass-border);
                border-radius: 24px;
                padding: 40px;
                width: 100%;
                max-width: 500px;
                text-align: center;
                box-shadow: 0 25px 50px rgba(0,0,0,0.5);
                border-top: 4px solid var(--error);
            }
            .icon-box {
                font-size: 50px;
                color: var(--error);
                margin-bottom: 20px;
                filter: drop-shadow(0 0 10px rgba(239, 68, 68, 0.3));
            }
            h2 { font-size: 24px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; }
            p { color: #94a3b8; line-height: 1.6; font-size: 16px; margin-bottom: 30px; }
            .btn-back {
                display: inline-block;
                padding: 12px 30px;
                background: var(--accent);
                color: var(--primary);
                text-decoration: none;
                border-radius: 12px;
                font-weight: 800;
                text-transform: uppercase;
                transition: 0.3s;
            }
            .btn-back:hover { transform: translateY(-3px); filter: brightness(1.1); }
            .sys-log { margin-top: 30px; font-family: monospace; font-size: 11px; color: rgba(255,255,255,0.1); }
        </style>
    </head>
    <body>
        <div class='error-card'>
            <div class='icon-box'><i class='fa-solid fa-triangle-exclamation'></i></div>
            <h2>Registration Protocol Halted</h2>
            <p>$message</p>
            <a href='javascript:history.back()' class='btn-back'>Return to Form</a>
            <div class='sys-log'>ERR_REJECTED_BY_CORE_SYNC</div>
        </div>
    </body>
    </html>
    ");
}


function show_department_error($dept, $evt) {
    die("
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css'>
        <style>
            :root {
                --primary: #020617;
                --error: #ef4444;
                --accent: #fbbf24;
                --glass: rgba(255, 255, 255, 0.03);
                --glass-border: rgba(255, 255, 255, 0.1);
            }
            body {
                background-color: var(--primary);
                color: #fff;
                font-family: 'Segoe UI', sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                padding: 20px;
                overflow: hidden;
            }
            .error-card {
                background: var(--glass);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid var(--glass-border);
                border-radius: 30px;
                padding: 50px 30px;
                width: 100%;
                max-width: 550px;
                text-align: center;
                box-shadow: 0 40px 100px rgba(0,0,0,0.7);
                border-top: 4px solid var(--error);
                animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
            }
            @keyframes shake {
                10%, 90% { transform: translate3d(-1px, 0, 0); }
                20%, 80% { transform: translate3d(2px, 0, 0); }
                30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
                40%, 60% { transform: translate3d(4px, 0, 0); }
            }
            .icon-box {
                font-size: 60px;
                color: var(--error);
                margin-bottom: 25px;
                filter: drop-shadow(0 0 15px rgba(239, 68, 68, 0.4));
            }
            h2 { font-size: 22px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; }
            p { color: #94a3b8; line-height: 1.8; font-size: 16px; margin-bottom: 35px; }
            .dept-name { color: var(--accent); font-weight: 800; }
            .btn-return {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 15px 35px;
                background: var(--accent);
                color: var(--primary);
                text-decoration: none;
                border-radius: 12px;
                font-weight: 900;
                text-transform: uppercase;
                transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
            .btn-return:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(251, 191, 36, 0.3); }
        </style>
    </head>
    <body>
        <div class='error-card'>
            <div class='icon-box'><i class='fa-solid fa-users-slash'></i></div>
            <h2>Duplicate Entry Detected</h2>
            <p>Access denied. A team from the <span class='dept-name'>$dept</span> department has already been initialized for <b>$evt</b>. 
            <br><br>Policy: Only one squad per department per event is permitted.</p>
            <a href='javascript:history.back()' class='btn-return'>
                <i class='fa-solid fa-arrow-left'></i> Back to Form
            </a>
        </div>
    </body>
    </html>
    ");
}


// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Connect to MongoDB
        $client = new MongoDB\Client($uri);
        $collection = $client->Qutrix->registrations;

        // Maximum file size limit (900 KB)
        $maxFileSize = 900 * 1024;

        // Function to validate file size
        function validateFileSize($file, $maxFileSize) {
            if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
                return true; // No file uploaded is acceptable
            }
            return $file['size'] <= $maxFileSize;
        }

        // Function to validate file type
        function validateFileType($file) {
            if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
                return true; // No file uploaded is acceptable
            }
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $file['tmp_name']);
            finfo_close($file_info);
            
            return in_array($mime_type, $allowed_types);
        }

        // Function to upload file
        function uploadFile($file, $target_dir = "uploads/") {
            if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
                return null; // No file uploaded
            }
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
            $destination = $target_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                return $destination;
            }
            
            return null;
        }

        // File validation checks
        $fileErrors = [];
        
        if (!validateFileSize($_FILES["first_member_bonafide"], $maxFileSize)) {
            $fileErrors[] = "The first member's bonafide file exceeds the size limit of 900 KB.";
        }
        
        if (!validateFileType($_FILES["first_member_bonafide"])) {
            $fileErrors[] = "The first member's bonafide file must be a JPG, JPEG, PNG, or GIF image.";
        }
        
        if (isset($_FILES["second_member_bonafide"]) && !validateFileSize($_FILES["second_member_bonafide"], $maxFileSize)) {
            $fileErrors[] = "The second member's bonafide file exceeds the size limit of 900 KB.";
        }
        
        if (isset($_FILES["second_member_bonafide"]) && !validateFileType($_FILES["second_member_bonafide"])) {
            $fileErrors[] = "The second member's bonafide file must be a JPG, JPEG, PNG, or GIF image.";
        }
        
        if (isset($_FILES["third_member_bonafide"]) && !validateFileSize($_FILES["third_member_bonafide"], $maxFileSize)) {
            $fileErrors[] = "The third member's bonafide file exceeds the size limit of 900 KB.";
        }
        
        if (isset($_FILES["third_member_bonafide"]) && !validateFileType($_FILES["third_member_bonafide"])) {
            $fileErrors[] = "The third member's bonafide file must be a JPG, JPEG, PNG, or GIF image.";
        }
        
        if (isset($_FILES["fourth_member_bonafide"]) && !validateFileSize($_FILES["fourth_member_bonafide"], $maxFileSize)) {
            $fileErrors[] = "The fourth member's bonafide file exceeds the size limit of 900 KB.";
        }
        
        if (isset($_FILES["fourth_member_bonafide"]) && !validateFileType($_FILES["fourth_member_bonafide"])) {
            $fileErrors[] = "The fourth member's bonafide file must be a JPG, JPEG, PNG, or GIF image.";
        }

        if (isset($_FILES["fifth_member_bonafide"]) && !validateFileSize($_FILES["fifth_member_bonafide"], $maxFileSize)) {
            $fileErrors[] = "The fifth member's bonafide file exceeds the size limit of 900 KB.";
        }
        if (isset($_FILES["fifth_member_bonafide"]) && !validateFileType($_FILES["fifth_member_bonafide"])) {
            $fileErrors[] = "The fifth member's bonafide file must be a JPG, JPEG, PNG, or GIF image.";
        }
        
        if (!empty($fileErrors)) {
            die("<br><br><b>" . implode("<br>", $fileErrors) . "</b>");
        }

        // Collect form data
        $college_name = $_POST['collegename'];
        $department = ($_POST['department'] === 'Others') ? $_POST['other_department'] : $_POST['department'];
        $event = $_POST['event'];
        
        // Member information
        $first_member_name = $_POST['firstmembername'];
        $first_member_roll_no = $_POST['firstmemberrno'];
        $first_member_phone = $_POST['firstmemberphone'];
        $first_member_email = $_POST['firstmemberemail'];
        
        $second_member_name = $_POST['secondmembername'];
        $second_member_roll_no = $_POST['secondmemberrno'];
        $second_member_phone = $_POST['secondmemberphone'];
        $second_member_email = $_POST['secondmemberemail'];
        
        // Optional members
        $third_member_name = !empty($_POST['thirdmembername']) ? $_POST['thirdmembername'] : null;
        $third_member_roll_no = !empty($_POST['thirdmemberrno']) ? $_POST['thirdmemberrno'] : null;
        $third_member_phone = !empty($_POST['thirdmemberphone']) ? $_POST['thirdmemberphone'] : null;
        $third_member_email = !empty($_POST['thirdmemberemail']) ? $_POST['thirdmemberemail'] : null;
        
        $fourth_member_name = !empty($_POST['fourthmembername']) ? $_POST['fourthmembername'] : null;
        $fourth_member_roll_no = !empty($_POST['fourthmemberrno']) ? $_POST['fourthmemberrno'] : null;
        $fourth_member_phone = !empty($_POST['fourthmemberphone']) ? $_POST['fourthmemberphone'] : null;
        $fourth_member_email = !empty($_POST['fourthmemberemail']) ? $_POST['fourthmemberemail'] : null;

        $fifth_member_name = !empty($_POST['fifthmembername']) ? $_POST['fifthmembername'] : null;
        $fifth_member_roll_no = !empty($_POST['fifthmemberrno']) ? $_POST['fifthmemberrno'] : null;
        $fifth_member_phone = !empty($_POST['fifthmemberphone']) ? $_POST['fifthmemberphone'] : null;
        $fifth_member_email = !empty($_POST['fifthmemberemail']) ? $_POST['fifthmemberemail'] : null;

        
        $currentDateTime = new MongoDB\BSON\UTCDateTime();

        // Check if event is selected
        if (empty($event)) {
            die("<br><br><b>Please select an event.</b>");
        }

        // Check duplicate registration (same dept, same college, same event)
        $exists = $collection->findOne([
            "college_name" => $college_name,
            "department" => $department,
            "event" => $event
        ]);
        
        if ($exists) {
    show_department_error($department, $event);
}

        // Handle file uploads
        $first_member_bonafide = uploadFile($_FILES["first_member_bonafide"]);
        $second_member_bonafide = uploadFile($_FILES["second_member_bonafide"]);
        $third_member_bonafide = uploadFile($_FILES["third_member_bonafide"]);
        $fourth_member_bonafide = uploadFile($_FILES["fourth_member_bonafide"]);
        $fifth_member_bonafide = uploadFile($_FILES["fifth_member_bonafide"]);

        // Conflict pairs
        $conflicting_event_pairs = [
            "QUIZ" => ["WEB DESIGN", "NON TECHNICAL ROUND DANCING"],
            "WEB DESIGN" => ["QUIZ", "NON TECHNICAL ROUND DANCING"],
            "MARKETING" => ["NON TECHNICAL ROUND DANCING"],
            "SOFTWARE CONTEST" => ["WORD HUNT", "NON TECHNICAL ROUND DANCING"],
            "WORD HUNT" => ["SOFTWARE CONTEST", "NON TECHNICAL ROUND DANCING"],
            "NON TECHNICAL ROUND DANCING" => ["QUIZ", "WEB DESIGN", "WORD HUNT", "MARKETING", "SOFTWARE CONTEST"],
        ];

        // Check conflicts
        // Check for conflicting events (now also includes college & department for consistency)
function get_conflicting_event($collection, $roll_no, $conflicting_events, $college_name, $department) {
    if (!$roll_no) return false;
    
    $docs = $collection->find([
        '$or' => [
            ["first_member_rollno" => $roll_no],
            ["second_member_rollno" => $roll_no],
            ["third_member_rollno" => $roll_no],
            ["fourth_member_rollno" => $roll_no],
            ["fifth_member_rollno" => $roll_no]
            
        ],
        "college_name" => $college_name,
        "department" => $department
    ]);
    
    foreach ($docs as $doc) {
        if (in_array($doc['event'], $conflicting_events)) {
            return $doc['event'];
        }
    }
    
    return false;
}

// Check event limit (roll_no + college + department)
function check_event_limit($collection, $roll_no, $college_name, $department) {
    if (!$roll_no) return false;
    
    $event_count = $collection->countDocuments([
        '$or' => [
            ["first_member_rollno" => $roll_no],
            ["second_member_rollno" => $roll_no],
            ["third_member_rollno" => $roll_no],
            ["fourth_member_rollno" => $roll_no],
            ["fifth_member_rollno" => $roll_no]
        ],
        "college_name" => $college_name,
        "department" => $department
    ]);
    
    return $event_count >= 2;
}


        $all_roll_numbers = [
            $first_member_roll_no, 
            $second_member_roll_no, 
            $third_member_roll_no, 
            $fourth_member_roll_no,
            $fifth_member_roll_no
        ];

        foreach ($all_roll_numbers as $roll_no) {
    if (!$roll_no) continue;
    
    // Check if roll number already registered for this event
    $exists = $collection->findOne([
        '$or' => [
            ["first_member_rollno" => $roll_no],
            ["second_member_rollno" => $roll_no],
            ["third_member_rollno" => $roll_no],
            ["fourth_member_rollno" => $roll_no],
            ["fifth_member_rollno" => $roll_no]
        ],
        "event" => $event,
        "college_name" => $college_name,
        "department" => $department
    ]);
    
    if ($exists) {
    show_error_page("Roll number <b>$roll_no</b> has already registered for the event: <b>$event</b>.");
}

// Check for conflicting events
if (isset($conflicting_event_pairs[$event])) {
    $conflict = get_conflicting_event($collection, $roll_no, $conflicting_event_pairs[$event], $college_name, $department);
    if ($conflict) {
        show_error_page("Roll number <b>$roll_no</b> cannot register for <b>$event</b> because they are already registered for <b>$conflict</b>. These events happen simultaneously.");
    }
}

// Check event limit
if (check_event_limit($collection, $roll_no, $college_name, $department)) {
    show_error_page("Roll number <b>$roll_no</b> from $college_name ($department) has reached the maximum limit of <b>two events</b> per person.");
}
}


        // Insert registration
        $result = $collection->insertOne([
            "college_name" => $college_name,
            "department" => $department,
            "event" => $event,
            "first_member_name" => $first_member_name,
            "first_member_rollno" => $first_member_roll_no,
            "first_member_phone" => $first_member_phone,
            "first_member_email" => $first_member_email,
            "first_member_bonafide" => $first_member_bonafide,
            "second_member_name" => $second_member_name,
            "second_member_rollno" => $second_member_roll_no,
            "second_member_phone" => $second_member_phone,
            "second_member_email" => $second_member_email,
            "second_member_bonafide" => $second_member_bonafide,
            "third_member_name" => $third_member_name,
            "third_member_rollno" => $third_member_roll_no,
            "third_member_phone" => $third_member_phone,
            "third_member_email" => $third_member_email,
            "third_member_bonafide" => $third_member_bonafide,
            "fourth_member_name" => $fourth_member_name,
            "fourth_member_rollno" => $fourth_member_roll_no,
            "fourth_member_phone" => $fourth_member_phone,
            "fourth_member_email" => $fourth_member_email,
            "fourth_member_bonafide" => $fourth_member_bonafide,
            "fifth_member_name" => $fifth_member_name,
            "fifth_member_rollno" => $fifth_member_roll_no,
            "fifth_member_phone" => $fifth_member_phone,
            "fifth_member_email" => $fifth_member_email,
            "fifth_member_bonafide" => $fifth_member_bonafide,
            "created_at" => $currentDateTime
        ]);

        // Get the inserted ID for the PDF filename
        $registration_id = (string)$result->getInsertedId();

        // WhatsApp links
        $whatsapp_links = [
            "PAPER PRESENTATION" => "https://chat.whatsapp.com/EUHI8RzFKDAKiXJYdkI14c",
            "QUIZ" => "https://chat.whatsapp.com/KGOW9QlC6DYAm8hXVc32Xd",
            "WEB DESIGN" => "https://chat.whatsapp.com/LxzcVPsbIMrCa7fuIMraFO",
            "MARKETING" => "https://chat.whatsapp.com/ES8urKsnJB6IT7I1gAunfp",
            "SOFTWARE CONTEST" => "https://chat.whatsapp.com/B9XSJywZlzi3zdS1RieHoY",
            "WORD HUNT" => "https://chat.whatsapp.com/Iw739BFmakDHumBkdLNAUv",
            
        ];

        // Create PDF directory if it doesn't exist
        $pdf_dir = "pdfs/";
        if (!file_exists($pdf_dir)) {
            mkdir($pdf_dir, 0777, true);
        }

        // Build PDF content
        $pdfHtml = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Registration Confirmation</title>
    <style>
        @page { margin: 0px; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            margin: 0; 
            padding: 0; 
            background-color: #ffffff;
            color: #333;
        }
        .container { 
            margin: 40px;
            border: 2px solid #020617;
            position: relative;
            min-height: 900px;
        }
        /* Top Accent Bar */
        .top-bar {
            height: 10px;
            background: #fbbf24;
            width: 100%;
        }
        .header { 
            text-align: center; 
            background: #020617; 
            color: #ffffff; 
            padding: 30px 20px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 26px; 
            letter-spacing: 1px;
            color: #fbbf24; 
        }
        .header h2 { 
            margin: 8px 0 0 0; 
            font-size: 14px; 
            font-weight: normal; 
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #71e4e4;
        }
        .header .sub-title {
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #ffffff;
        }

        .section { 
            padding: 20px 40px; 
            border-bottom: 1px solid #eeeeee;
        }
        .section:last-child { border-bottom: none; }

        .section-title { 
            font-weight: bold; 
            font-size: 14px; 
            margin-bottom: 12px; 
            color: #020617; 
            text-transform: uppercase;
            border-left: 4px solid #fbbf24;
            padding-left: 10px;
        }

        .details-table { width: 100%; border-collapse: collapse; }
        .details-table td { 
            padding: 8px 0; 
            vertical-align: top; 
            font-size: 13px;
        }
        .label { color: #64748b; width: 150px; font-weight: bold; }
        
        .member-row {
            margin-bottom: 5px;
            padding: 5px 0;
        }

        .footer { 
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center; 
            padding: 20px 0; 
            background: #f8fafc; 
            font-size: 11px; 
            color: #64748b;
            border-top: 1px solid #eeeeee;
        }
        .watermark {
            position: absolute;
            top: 400px;
            left: 150px;
            font-size: 80px;
            color: #f1f1f1;
            transform: rotate(-45deg);
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='top-bar'></div>
        <div class='header'>
            <h1>GOBI ARTS & SCIENCE COLLEGE</h1>
            <h2>PG & Research Department of Computer Science</h2>
            <div class='sub-title'>QUTRIX 2026 - Entry Pass</div>
        </div>

        <div class='watermark'>CONFIRMED</div>

        <div class='section'>
            <div class='section-title'>Institution & Event</div>
            <table class='details-table'>
                <tr><td class='label'>College:</td><td>$college_name</td></tr>
                <tr><td class='label'>Department:</td><td>$department</td></tr>
                <tr><td class='label'>Event Registered:</td><td><strong style='color:#020617;'>$event</strong></td></tr>
            </table>
        </div>

        <div class='section'>
            <div class='section-title'>Squad Members</div>
            <table class='details-table'>
                <tr>
                    <td class='label'>Lead Member:</td>
                    <td><strong>$first_member_name</strong><br><small>$first_member_roll_no | $first_member_phone | $first_member_email</small></td>
                </tr>
                <tr>
                    <td class='label'>Second Member:</td>
                    <td><strong>$second_member_name</strong><br><small>$second_member_roll_no | $second_member_phone | $second_member_email</small></td>
                </tr>";

        if (!empty($third_member_name)) {
            $pdfHtml .= "<tr><td class='label'>Third Member:</td><td><strong>$third_member_name</strong><br><small>$third_member_roll_no</small></td></tr>";
        }
        if (!empty($fourth_member_name)) {
            $pdfHtml .= "<tr><td class='label'>Fourth Member:</td><td><strong>$fourth_member_name</strong><br><small>$fourth_member_roll_no</small></td></tr>";
        }
        if (!empty($fifth_member_name)) {
            $pdfHtml .= "<tr><td class='label'>Fifth Member:</td><td><strong>$fifth_member_name</strong><br><small>$fifth_member_roll_no</small></td></tr>";
        }

        $pdfHtml .= "
            </table>
        </div>

        <div class='section'>
            <div class='section-title'>Registration Metadata</div>
            <table class='details-table'>
                <tr><td class='label'>Pass ID:</td><td><code>$registration_id</code></td></tr>
                <tr><td class='label'>Issued On:</td><td>" . date('Y-m-d H:i:s') . "</td></tr>
            </table>
        </div>

        <div class='footer'>
            <p>This is a digitally generated confirmation. Presentation of this slip is mandatory at the venue.</p>
            <p><strong>Venue:</strong> Gobi Arts & Science College (Autonomous) | <strong>Contact:</strong> Organizers Team</p>
        </div>
    </div>
</body>
</html>";


        // Generate and save PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($pdfHtml);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Save PDF to pdfs folder
        $pdf_filename = "registration_" . $registration_id . ".pdf";
        $pdf_filepath = $pdf_dir . $pdf_filename;
        file_put_contents($pdf_filepath, $dompdf->output());

        // Update the database with PDF path
        $collection->updateOne(
            ['_id' => $result->getInsertedId()],
            ['$set' => ['pdf_path' => $pdf_filepath]]
        );

        // --- Styled Success Message ---
echo "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css'>
    <style>
        :root {
            --primary: #020617; 
            --accent: #fbbf24;  
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --white: #ffffff;
            --skyblu: #71e4e4;
        }

        body {
            background-color: var(--primary);
            color: var(--white);
            font-family: 'Segoe UI', Tahoma, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .success-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px 30px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(113, 228, 228, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 1px solid var(--skyblu);
        }

        .icon-box i {
            font-size: 40px;
            color: var(--skyblu);
        }

        h2 {
            font-size: 28px;
            margin-bottom: 10px;
            color: var(--white);
            letter-spacing: 1px;
        }

        .event-name {
            color: var(--accent);
            font-weight: 800;
            text-transform: uppercase;
        }

        p {
            color: #94a3b8;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: 0.3s;
        }

        .btn-pdf {
            background: var(--white);
            color: var(--primary);
        }

        .btn-wa {
            background: #25d366;
            color: white;
        }

        .btn:hover {
            transform: translateY(-3px);
            filter: brightness(1.1);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .footer-note {
            margin-top: 30px;
            font-size: 12px;
            color: rgba(255,255,255,0.2);
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class='success-card'>
        <div class='icon-box'>
            <i class='fa-solid fa-circle-check'></i>
        </div>
        <h2>Registration Successful!</h2>
        <p>You have successfully registered for <br><span class='event-name'>$event</span></p>

        <div class='btn-container'>";

        // Conditional WhatsApp Button
        if (isset($whatsapp_links[$event]) && $event !== "NON TECHNICAL ROUND DANCING") {
            echo "
            <script>alert('Please join the WhatsApp group for your event!');</script>
            <a href='" . $whatsapp_links[$event] . "' target='_blank' class='btn btn-wa'>
                <i class='fa-brands fa-whatsapp'></i> JOIN EVENT WHATSAPP GROUP
            </a>";
        } elseif ($event === "NON TECHNICAL ROUND DANCING") {
            echo "<p style='font-size: 12px; margin-bottom: 10px;'>Note: No WhatsApp group required for Dance.</p>";
        }

        // PDF Download Button
        echo "
            <a href='$pdf_filepath' download class='btn btn-pdf'>
                <i class='fa-solid fa-file-pdf'></i> DOWNLOAD CONFIRMATION SLIP
            </a>
            <a href='index.html' style='color: var(--text-gray); font-size: 13px; text-decoration: none; margin-top: 10px;'>Return to Home</a>
        </div>

        <div class='footer-note'>
            SYSTEM_PROTOCOL: REG_COMPLETE_2026
        </div>
    </div>
</body>
</html>";

      

// Collect all member emails
$emails = [];
if (!empty($first_member_email)) $emails[] = $first_member_email;
if (!empty($second_member_email)) $emails[] = $second_member_email;
if (!empty($third_member_email)) $emails[] = $third_member_email;
if (!empty($fourth_member_email)) $emails[] = $fourth_member_email;
if (!empty($fifth_member_email)) $emails[] = $fifth_member_email;


try {
    $mail = new PHPMailer(true);

    // DEBUG (remove after testing)
    $mail->SMTPDebug = 0;

    // SMTP CONFIG
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'naveen9222777@gmail.com';
    $mail->Password   = 'bzze mlve sqsw hivl';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // IMPORTANT: SAME AS USERNAME
    $mail->setFrom('naveen9222777@gmail.com', 'Qutrix 2026 Registration');

    // ADD RECIPIENTS SAFELY
    foreach ($emails as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($email);
        }
    }

    // ATTACH PDF
    $mail->addAttachment($pdf_filepath);

    // EMAIL CONTENT
    $mail->isHTML(true);
    $mail->Subject = "Qutrix 2026 Registration Confirmation - $event";
    $mail->Body = "
        <h2>Qutrix 2026 - Registration Confirmed</h2>
        <p>You have successfully registered for <b>$event</b>.</p>
        <p>Please find the attached confirmation slip.</p>
        <p><b>Date:</b> 19-09-2026<br>
           <b>Time:</b> 9:00 AM<br>
           <b>Venue:</b> Gobi Arts & Science College</p>
        <br>
        <p>— Qutrix Registration Team</p>
    ";

    $mail->send();

} catch (Exception $e) {
    // show_error_page("Mailer Error: " . $mail->ErrorInfo);
}


    } catch(Exception $e) {
        echo "<div style='text-align:center; padding:20px; background:#ffebee; border-radius:10px; margin:20px;'>";
        echo "<h2 style='color:#c0392b;'>Registration Error</h2>";
        echo "<p style='color:#c0392b;'>Error: " . $e->getMessage() . "</p>";
        echo "<p>Please try again or contact support if the problem persists.</p>";
        echo "</div>";
    }
} else {
    // If form wasn't submitted, redirect to form
    header("Location: new_form.html");
    exit();
}
?>