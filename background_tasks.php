<?php
require 'db.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;

$regId = $argv[1] ?? null;
if (!$regId) exit;

$doc = $db->registrations->findOne([
    '_id' => new MongoDB\BSON\ObjectId($regId)
]);

if (!$doc) exit;

/* ---------------- PDF ---------------- */
$pdfDir = __DIR__ . '/pdfs/';
if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);

$html = "<h2>Qutrix Registration</h2>
<p>College: {$doc['college_name']}</p>
<p>Department: {$doc['department']}</p>
<p>Event: {$doc['event']}</p>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();

$pdfPath = $pdfDir . "registration_$regId.pdf";
file_put_contents($pdfPath, $dompdf->output());

$db->registrations->updateOne(
    ['_id' => $doc['_id']],
    ['$set' => ['pdf_path' => $pdfPath]]
);

/* ---------------- EMAIL ---------------- */
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = $_ENV['SMTP_HOST'];
$mail->SMTPAuth = true;
$mail->Username = $_ENV['SMTP_USER'];
$mail->Password = $_ENV['SMTP_PASS'];
$mail->Port = 587;

$mail->setFrom($_ENV['SMTP_USER'], 'Qutrix 2026');

for ($i = 1; $i <= 5; $i++) {
    if (!empty($doc["{$i}_member_email"])) {
        $mail->addAddress($doc["{$i}_member_email"]);
    }
}

$mail->addAttachment($pdfPath);
$mail->isHTML(true);
$mail->Subject = "Qutrix Registration Confirmation";
$mail->Body = "<p>Your registration is confirmed.</p>";

$mail->send();
