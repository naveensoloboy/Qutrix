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

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Connect to MongoDB
        $client = new Client("mongodb+srv://admin:qutrixpass2025@cluster1.duscp.mongodb.net/?retryWrites=true&w=majority&appName=Cluster1");
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
            die("<br><br><b>A team from your department has already registered for event: $event. Only one team per department is allowed per event.</b>");
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
        die("<br><br><b>Roll number $roll_no has already registered for the event: $event.</b>");
    }
    
    // Check for conflicting events
    if (isset($conflicting_event_pairs[$event])) {
        $conflict = get_conflicting_event($collection, $roll_no, $conflicting_event_pairs[$event], $college_name, $department);
        if ($conflict) {
            die("<br><br><b>Roll number $roll_no cannot register for $event because already registered for $conflict.</b>");
        }
    }
    
    // Check event limit
    if (check_event_limit($collection, $roll_no, $college_name, $department)) {
        die("<br><br><b>Roll number $roll_no from $college_name ($department) has already registered for two events.</b>");
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
            "PAPER PRESENTATION" => "https://chat.whatsapp.com/H7m0gMxsiTSGwshDo7wt4q",
            "QUIZ" => "https://chat.whatsapp.com/HG12g7tu72l7Hg0NMEQkP1",
            "WEB DESIGN" => "https://chat.whatsapp.com/K62TaS736mOJvWHpR7OTXd",
            "MARKETING" => "https://chat.whatsapp.com/EvLsT7oeohUC6x25QY6dI3",
            "SOFTWARE CONTEST" => "https://chat.whatsapp.com/KIVg2FPShbhHO8Iyhp6tIu",
            "WORD HUNT" => "https://chat.whatsapp.com/I9kzki1o8Js2CHKH1d2v6j",
            "NON TECHNICAL ROUND DANCING" => "https://chat.whatsapp.com/HzQX1lKZLkM3iILgGw4Jep"
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
                body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
                .header { text-align: center; margin-bottom: 30px; }
                .section { margin-bottom: 20px; }
                .section-title { font-weight: bold; font-size: 18px; border-bottom: 2px solid #333; padding-bottom: 5px; margin-bottom: 10px; }
                .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Event Registration Confirmation</h1>
                <h2>Qutrix 2025</h2>
            </div>
            
            <div class='section'>
                <div class='section-title'>College Information</div>
                <p><strong>College:</strong> $college_name</p>
                <p><strong>Department:</strong> $department</p>
                <p><strong>Event:</strong> $event</p>
            </div>
            
            <div class='section'>
                <div class='section-title'>Team Members</div>
                <p><strong>First Member:</strong> $first_member_name ($first_member_roll_no) - $first_member_phone - $first_member_email</p>
                <p><strong>Second Member:</strong> $second_member_name ($second_member_roll_no) - $second_member_phone - $second_member_email</p>";
        
        if ($third_member_name) {
            $pdfHtml .= "<p><strong>Third Member:</strong> $third_member_name ($third_member_roll_no) - $third_member_phone - $third_member_email</p>";
        }
        
        if ($fourth_member_name) {
            $pdfHtml .= "<p><strong>Fourth Member:</strong> $fourth_member_name ($fourth_member_roll_no) - $fourth_member_phone - $fourth_member_email</p>";
        }

        if ($fifth_member_name) {
            $pdfHtml .= "<p><strong>Fifth Member:</strong> $fifth_member_name ($fifth_member_roll_no) - $fifth_member_phone - $fifth_member_email</p>";
        }
        
        $pdfHtml .= "</div>
            
            <div class='section'>
                <div class='section-title'>Registration Details</div>
                <p><strong>Registration ID:</strong> $registration_id</p>
                <p><strong>Registration Date:</strong> " . date('Y-m-d H:i:s') . "</p>
            </div>
            
            <div class='footer'>
                <p>This is an auto-generated confirmation. Please keep this for your records.</p>
                <p>For any queries, contact the event organizers.</p>
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

        // Success message
        echo "<div style='text-align:center; padding:20px; background:#f0f8ff; border-radius:10px; margin:20px;'>";
        echo "<h2 style='color:#2c3e50;'>Registration Successful!</h2>";
        echo "<p style='font-size:18px;'>Thank you for registering for <strong>$event</strong></p>";
        
        if (isset($whatsapp_links[$event])) {
            echo "<script>alert('Please join the WhatsApp group for your event');</script>";
            echo "<p>Join the WhatsApp group for your event: <a href='".$whatsapp_links[$event]."' target='_blank' style='color:#3498db; text-decoration:none; font-weight:bold;'>$event WhatsApp Group</a></p>";
        }
        
        echo "<p>Download your registration confirmation: 
                <a href='$pdf_filepath' download style='display:inline-block; padding:10px 20px; background:#3498db; color:white; text-decoration:none; border-radius:5px; margin-top:15px;'>
                    Download PDF
                </a>
             </p>";
        echo "</div>";

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