<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Required libraries
require 'vendor/autoload.php';
require 'db.php';
use MongoDB\Client;
use Dompdf\Dompdf;
use Dompdf\Options;

try {
    // Connect to MongoDB
    $collection = $client->Qutrix->registrations;

    // Sample data
    $college_name = "Gobi Arts & Science CollegeGobi Arts & Science CollegeGobi Arts & Science College";
    $department   = "Computer ScienceGobi Arts & Science College";
    $event        = "QUIZGobi Arts & Science College";

    $first_member_name  = "Naveen S";
    $first_member_roll  = "CS001";
    $first_member_phone = "9876543210";
    $first_member_email = "naveen@example.com";

    $second_member_name  = "Arun K";
    $second_member_roll  = "CS002";
    $second_member_phone = "9876543211";
    $second_member_email = "arun@example.com";

    $third_member_name  = "Priya R";
    $third_member_roll  = "CS003";
    $third_member_phone = "9876543212";
    $third_member_email = "priya@example.com";

    $fourth_member_name = null;
    $fifth_member_name  = null;

    $currentDateTime = new MongoDB\BSON\UTCDateTime();

    // Insert sample registration
    $result = $collection->insertOne([
        "college_name" => $college_name,
        "department"   => $department,
        "event"        => $event,

        "first_member_name"  => $first_member_name,
        "first_member_rollno"=> $first_member_roll,
        "first_member_phone" => $first_member_phone,
        "first_member_email" => $first_member_email,

        "second_member_name"  => $second_member_name,
        "second_member_rollno"=> $second_member_roll,
        "second_member_phone" => $second_member_phone,
        "second_member_email" => $second_member_email,

        "third_member_name"  => $third_member_name,
        "third_member_rollno"=> $third_member_roll,
        "third_member_phone" => $third_member_phone,
        "third_member_email" => $third_member_email,

        "fourth_member_name" => $fourth_member_name,
        "fifth_member_name"  => $fifth_member_name,

        "created_at" => $currentDateTime
    ]);

    // Get inserted ID
    $registration_id = (string)$result->getInsertedId();

    // WhatsApp links
    $whatsapp_links = [
        "QUIZ" => "https://chat.whatsapp.com/KGOW9QlC6DYAm8hXVc32Xd",
        "WEB DESIGN" => "https://chat.whatsapp.com/LxzcVPsbIMrCa7fuIMraFO",
        "MARKETING" => "https://chat.whatsapp.com/ES8urKsnJB6IT7I1gAunfp",
        "SOFTWARE CONTEST" => "https://chat.whatsapp.com/B9XSJywZlzi3zdS1RieHoY",
        "WORD HUNT" => "https://chat.whatsapp.com/Iw739BFmakDHumBkdLNAUv",
        "NON TECHNICAL ROUND DANCING" => "https://chat.whatsapp.com/HzQX1lKZLkM3iILgGw4Je"
    ];

    // Create PDF directory
    $pdf_dir = "pdfs/";
    if (!file_exists($pdf_dir)) {
        mkdir($pdf_dir, 0777, true);
    }

    // Build PDF
    $pdfHtml = "
<html>
<head>
  <style>
    body { font-family: DejaVu Sans, sans-serif; margin:0; padding:0; }
    .container { border:1px solid #ccc; border-radius:10px; padding:20px; }
    .header { text-align:center; background:#6A0DAD; color:#fff; padding:15px; border-radius:10px 10px 0 0; }
    .header h1 { margin:0; font-size:24px; }
    .header h2 { margin:5px 0 0 0; font-size:16px; font-weight:normal; }

    .section { padding:15px; border-bottom:1px solid #ddd; }
    .section:last-child { border-bottom:none; }

    .section-title { font-weight:bold; font-size:16px; margin-bottom:10px; color:#6A0DAD; }

    .details-table { width:100%; border-collapse:collapse; }
    .details-table td { padding:6px 4px; vertical-align:top; }

    .right-box {
      float:right; width:160px; text-align:center; 
      border:2px solid #6A0DAD; border-radius:10px; padding:10px;
    }
    .date { font-size:28px; font-weight:bold; color:#6A0DAD; }
    .id { margin-top:5px; font-size:14px; }

    .footer { text-align:center; padding:15px; background:#f5f5f5; border-radius:0 0 10px 10px; font-size:12px; color:#333; }

    .btn { display:inline-block; background:#6A0DAD; color:#fff; padding:8px 15px; border-radius:5px; text-decoration:none; font-size:14px; }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'>
      <h1>GOBI ARTS & SCIENCE COLLEGE</h1>
      <h2>PG & RESEARCH DEPARTMENT OF COMPUTER SCIENCE</h2>
      <h3>QUTRIX Registration Confirmation</h3>
    </div>
</div>
    <div class='container'>
    <div class='section-title'>College Information</div>
      <table class='details-table'>
        <tr><td><strong>College:</strong></td><td>$college_name</td></tr>
        <tr><td><strong>Department:</strong></td><td>$department</td></tr>
        <tr><td><strong>Event:</strong></td><td>$event</td></tr>
      </table>
    </div>
    <?div>
    <div class='container'>
    <div class='section'>
      <div class='section-title'>Team Members</div>
      <table class='details-table'>
        <tr><td><strong>1:</strong></td><td>$first_member_name ($first_member_roll)<br>$first_member_phone<br>$first_member_email</td></tr>
        <tr><td><strong>2:</strong></td><td>$second_member_name ($second_member_roll)<br>$second_member_phone<br>$second_member_email</td></tr>
        <tr><td><strong>3:</strong></td><td>$third_member_name ($third_member_roll)<br>$third_member_phone<br>$third_member_email</td></tr>
      </table>
    </div>
    </div>

    <div class='container'>
    <div class='section'>
      <div class='section-title'>Registration Details</div>
      <table class='details-table'>
        <tr><td><strong>Registration ID:</strong></td><td>$registration_id</td></tr>
        <tr><td><strong>Date:</strong></td><td>".date('Y-m-d H:i:s')."</td></tr>
      </table>
      
    </div>
    </div>

    <div class='footer'>
      This is an auto-generated confirmation slip.<br>
      Please keep this for your records.
    </div>
  </div>
</body>
</html>";

    // Generate PDF
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($pdfHtml);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Save PDF
    $pdf_filename = "registration_" . $registration_id . ".pdf";
    $pdf_filepath = $pdf_dir . $pdf_filename;
    file_put_contents($pdf_filepath, $dompdf->output());

    // Update DB with PDF path
    $collection->updateOne(
        ['_id' => $result->getInsertedId()],
        ['$set' => ['pdf_path' => $pdf_filepath]]
    );

    // Success Output
    echo "<div style='text-align:center; padding:20px; background:#f0f8ff; border-radius:10px; margin:20px;'>";
        echo "<h2 style='color:#2c3e50;'>Registration Successful!</h2>";
        echo "<p style='font-size:18px;'>Thank you for registering for <strong>$event</strong></p>";
    echo "<p>Download your registration confirmation: 
                <a href='$pdf_filepath' download style='display:inline-block; padding:10px 20px; background:#3498db; color:white; text-decoration:none; border-radius:5px; margin-top:15px;'>
                    Download PDF
                </a>
             </p>";
    if (isset($whatsapp_links[$event])) {
        echo "<p><a href='".$whatsapp_links[$event]."' target='_blank'>Join WhatsApp Group</a></p>";
    }

} catch(Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
