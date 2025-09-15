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

        // Check duplicate registration (max 5 teams per dept, per college, per event)
$count = $collection->countDocuments([
    "college_name" => $college_name,
    "department" => $department,
    "event" => $event
]);

if ($count >= 3) {
    die("<br><br><b>Your department has already registered 3 teams for event: $event. Maximum limit reached.</b>");
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
            body { font-family: DejaVu Sans, sans-serif; margin:0; padding:0; }
            .container { border:1px solid #ccc; border-radius:10px; padding:20px; margin:20px; }
            .header { text-align:center; background:#6A0DAD; color:#fff; padding:15px; border-radius:10px 10px 0 0; }
            .header h1 { margin:0; font-size:24px; }
            .header h2 { margin:5px 0 0 0; font-size:16px; font-weight:normal; }

            .section { padding:15px; border-bottom:1px solid #ddd; clear:both; }
            .section:last-child { border-bottom:none; }

            .section-title { font-weight:bold; font-size:16px; margin-bottom:10px; color:#6A0DAD; }

            .details-table { width:100%; border-collapse:collapse; }
            .details-table td { padding:6px 4px; vertical-align:top; }

            .right-box {
              float:right; width:160px; text-align:center; 
              border:2px solid #6A0DAD; border-radius:10px; padding:10px; margin-top:-40px;
            }
            .date { font-size:20px; font-weight:bold; color:#6A0DAD; }
            .id { margin-top:5px; font-size:14px; }

            .footer { text-align:center; padding:15px; background:#f5f5f5; border-radius:0 0 10px 10px; font-size:12px; color:#333; }
          </style>
        </head>
        <body>
          <div class='container'>
            <div class='header'>
                <h1>GOBI ARTS & SCIENCE COLLEGE</h1>
                <h2>PG & RESEARCH DEPARTMENT OF COMPUTER SCIENCE</h2>
                <h2>Qutrix 2025 </h2>
                <h2>Registration Confirmation</h2>
            </div>

            

            <div class='section'>
              <div class='section-title'>College Information</div>
              <table class='details-table'>
                <tr><td><strong>College:</strong></td><td>$college_name</td></tr>
                <tr><td><strong>Department:</strong></td><td>$department</td></tr>
                <tr><td><strong>Event:</strong></td><td>$event</td></tr>
              </table>
            </div>

            <div class='section'>
              <div class='section-title'>Team Members</div>
              <table class='details-table'>
                <tr><td><strong>First Member:</strong></td><td>$first_member_name ($first_member_roll_no) - $first_member_phone - $first_member_email</td></tr>
                <tr><td><strong>Second Member:</strong></td><td>$second_member_name ($second_member_roll_no) - $second_member_phone - $second_member_email</td></tr>";

        if (!empty($third_member_name)) {
            $pdfHtml .= "<tr><td><strong>Third Member:</strong></td><td>$third_member_name ($third_member_roll_no) - $third_member_phone - $third_member_email</td></tr>";
        }
        if (!empty($fourth_member_name)) {
            $pdfHtml .= "<tr><td><strong>Fourth Member:</strong></td><td>$fourth_member_name ($fourth_member_roll_no) - $fourth_member_phone - $fourth_member_email</td></tr>";
        }
        if (!empty($fifth_member_name)) {
            $pdfHtml .= "<tr><td><strong>Fifth Member:</strong></td><td>$fifth_member_name ($fifth_member_roll_no) - $fifth_member_phone - $fifth_member_email</td></tr>";
        }

        $pdfHtml .= "
              </table>
            </div>

            <div class='section'>
              <div class='section-title'>Registration Details</div>
              <table class='details-table'>
                <tr><td><strong>Registration ID:</strong></td><td>$registration_id</td></tr>
                <tr><td><strong>Registration Date:</strong></td><td>" . date('Y-m-d H:i:s') . "</td></tr>
              </table>
            </div>

            <div class='footer'>
              <p>This is an auto-generated confirmation. Please keep this for your records.</p>
              <p>For any queries, contact the event organizers.</p>
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

        // Success message
        echo "<div style='text-align:center; padding:20px; background:#f0f8ff; border-radius:10px; margin:20px;'>";
        echo "<h2 style='color:#2c3e50;'>Registration Successful!</h2>";
        echo "<p style='font-size:18px;'>Thank you for registering for <strong>$event</strong></p>";
        
        if (isset($whatsapp_links[$event]) && $event !== "NON TECHNICAL ROUND DANCING") {
            echo "<script>alert('Please join the WhatsApp group for your event');</script>";
            echo "<p>Join the WhatsApp group for your event: 
                    <a href='" . $whatsapp_links[$event] . "' target='_blank' 
                       style='color:#3498db; text-decoration:none; font-weight:bold;'>
                       $event WhatsApp Group</a>
                  </p>";
        } elseif ($event === "NON TECHNICAL ROUND DANCING") {
            echo "<p><strong>Note:</strong> No WhatsApp group is required for Dance participants.</p>";
        }

        
        echo "<p>Download your registration confirmation: 
                <a href='$pdf_filepath' download style='display:inline-block; padding:10px 20px; background:#3498db; color:white; text-decoration:none; border-radius:5px; margin-top:15px;'>
                    Download PDF
                </a>
             </p>";
        echo "</div>";

      

// Collect all member emails
$emails = [];
if (!empty($first_member_email)) $emails[] = $first_member_email;
if (!empty($second_member_email)) $emails[] = $second_member_email;
if (!empty($third_member_email)) $emails[] = $third_member_email;
if (!empty($fourth_member_email)) $emails[] = $fourth_member_email;
if (!empty($fifth_member_email)) $emails[] = $fifth_member_email;

try {
    $mail = new PHPMailer(true);

    // Server settings (use Gmail SMTP or other provider)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';   // Gmail SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'naveen9222777@gmail.com'; // Your Gmail
    $mail->Password   = 'lpyu sgqm qedr fqit';   // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender info
    $mail->setFrom('yourgmail@gmail.com', 'Qutrix 2025 Registration');

    // Add all recipients
    foreach ($emails as $email) {
        $mail->addAddress($email);
    }

    // Attach the PDF
    $mail->addAttachment($pdf_filepath);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = "Qutrix 2025 Registration Confirmation - $event";
    $mail->Body    = "
        <div style='font-family:Arial,sans-serif;'>
            <h2 style='color:#6A0DAD;'>Qutrix 2025 - Registration Confirmation</h2>
            <p>Dear Team,</p>
            <p>Your registration for the event <b>$event</b> has been successfully completed.</p>
            <p>Please find attached confirmation slip (PDF) and download it for conformation.</p>
            <br>
            <p>📍 <b>Venue:</b> Gobi Arts & Science College</p>
            <p>📅 <b>Date:</b> 19-09-2025</p>
            <p>🕒 <b>Time:</b> 9:00 AM</p>
            <br>
            <p>Regards,<br>Registration Committee<br>Qutrix 2025</p>
        </div>
    ";

    $mail->send();
    // echo "<p style='color:green; font-weight:bold;'>Confirmation email sent to all team members.</p>";

} catch (Exception $e) {
    // echo "<p style='color:red;'>Email could not be sent. Error: {$mail->ErrorInfo}</p>";
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