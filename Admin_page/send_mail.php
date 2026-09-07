<?php
include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Kolkata');
$collection = $client->Qutrix->registrations;

$selectedDate = $_POST['selected_date'] ?? '';
$registrations = [];
$grouped = [];

if ($selectedDate) {
    $start = new MongoDB\BSON\UTCDateTime(strtotime($selectedDate . " 00:00:00") * 1000);
    $end   = new MongoDB\BSON\UTCDateTime(strtotime($selectedDate . " 23:59:59") * 1000);

    $registrations = $collection->find([
        "created_at" => [
            '$gte' => $start,
            '$lte' => $end
        ]
    ]);

    foreach ($registrations as $r) {
        $cName = $r['college_name'] ?? 'Unknown College';
        $grouped[$cName][] = $r;
    }
}

/* SEND MAIL LOGIC */
if (isset($_POST['send_mail'])) {
    $selectedEntries = $_POST['reg_ids'] ?? [];
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('qutrix.official@gmail.com', 'QUTRIX 2K26');
        $mail->isHTML(true);

        foreach ($selectedEntries as $entry) {
            list($regID, $email) = explode('|', $entry);
            $mail->clearAddresses();
            $mail->clearAttachments();
            $mail->addAddress($email);

            $userDoc = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($regID)]);
            if ($userDoc) {
                $mail->Subject = "Confirmation: " . $userDoc['event'] . " | QUTRIX 2K26";
                $mail->Body = "<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h2 style='color: #1e3a8a;'>Greetings from QUTRIX 2K26!</h2>
                    <p>Dear Participant,</p>
                    <p>We are excited to inform you that your registration for <b>" . ($userDoc['event'] ?? 'the Symposium') . "</b> has been successfully processed.</p>
                    <p>Attached is your <b>Official Entry Pass (PDF)</b>. Please bring a digital or printed copy to the venue.</p>
                    <hr style='border: 0; border-top: 1px solid #eee;'>
                    <p><b>Event Details:</b><br>
                    Venue: Gobi Arts & Science College (Autonomous)<br>
                    Department: PG & Research Dept of Computer Science</p>
                    <p>Best Regards,<br><b>Team QUTRIX</b></p>
                </div>";

                if (!empty($userDoc['pdf_path'])) {
                    $pdfSource = $userDoc['pdf_path'];
                    $attachmentName = str_replace(' ', '_', $userDoc['event'] ?? 'Registration') . "_Pass.pdf";

                    // Handle Cloudinary URL attachment
                    if (filter_var($pdfSource, FILTER_VALIDATE_URL)) {
                        $pdfData = @file_get_contents($pdfSource);
                        if ($pdfData !== false) {
                            $mail->addStringAttachment($pdfData, $attachmentName, 'base64', 'application/pdf');
                        }
                    } else {
                        // Fallback for local files
                        $absolutePath = __DIR__ . '/../' . $pdfSource;
                        if (file_exists($absolutePath)) {
                            $mail->addAttachment($absolutePath, $attachmentName);
                        }
                    }
                }

                $mail->send();
            }
        }
        echo "<script>alert('Batch transmission complete.');</script>";
    } catch (Exception $e) { 
        $mailError = $mail->ErrorInfo; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub | QUTRIX 2K26</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #020617; 
            --accent: #38bdf8; 
            --glass: rgba(255, 255, 255, 0.03); 
            --glass-border: rgba(255, 255, 255, 0.1); 
            --text-dim: #94a3b8; 
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--primary); 
            color: #fff; 
            padding: clamp(10px, 2vw, 30px);
            background-image: radial-gradient(circle at 50% 0%, rgba(56, 189, 248, 0.08) 0%, transparent 50%); 
        }

        .container { max-width: 1200px; margin: 0 auto; }

        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .btn-back {
            text-decoration: none; 
            color: #fff; 
            background: var(--glass);
            padding: 10px 16px; 
            border-radius: 12px; 
            border: 1px solid var(--glass-border);
            font-size: 14px; 
            transition: 0.3s;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
            background: var(--glass);
            padding: 20px;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            margin-bottom: 25px;
        }

        .input-group { position: relative; width: 100%; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--accent); }
        .input-group input {
            width: 100%; 
            background: rgba(0,0,0,0.2); 
            border: 1px solid var(--glass-border);
            padding: 12px 12px 12px 40px; 
            color: #fff; 
            border-radius: 12px; 
            outline: none;
        }

        .global-toggle { 
            background: rgba(56, 189, 248, 0.08); 
            padding: 15px 20px; 
            border-radius: 15px;
            border: 1px solid var(--accent); 
            margin-bottom: 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            cursor: pointer;
        }

        .college-card { 
            background: var(--glass); 
            border: 1px solid var(--glass-border); 
            border-radius: 24px; 
            margin-bottom: 20px; 
            overflow: hidden; 
        }

        .college-header { 
            background: rgba(255,255,255,0.05); 
            padding: 15px 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }

        .user-row { 
            display: grid; 
            grid-template-columns: 280px 1fr;
            padding: 20px; 
            border-bottom: 1px solid rgba(255,255,255,0.05);
            transition: 0.3s;
        }

        @media (max-width: 800px) {
            .user-row { grid-template-columns: 1fr; gap: 15px; }
            .event-info { border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px; }
        }

        .email-flex { display: flex; flex-wrap: wrap; gap: 8px; }

        .email-chip {
            background: rgba(255,255,255,0.03); 
            border: 1px solid var(--glass-border);
            padding: 6px 12px; 
            border-radius: 10px; 
            cursor: pointer; 
            display: flex;
            align-items: center; 
            gap: 8px; 
            font-size: 13px; 
            transition: 0.2s;
        }
        .email-chip:hover { border-color: var(--accent); background: rgba(56, 189, 248, 0.1); }

        .sticky-footer {
            position: sticky; 
            bottom: 15px; 
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(15px); 
            padding: 15px 25px; 
            border-radius: 20px;
            border: 1px solid var(--accent); 
            display: flex; 
            justify-content: space-between;
            align-items: center; 
            z-index: 1000; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            margin-top: 30px;
        }

        .btn-dispatch {
            background: var(--accent); 
            color: var(--primary); 
            padding: 12px 24px;
            border-radius: 12px; 
            font-weight: 800; 
            border: none; 
            cursor: pointer;
            display: flex; 
            align-items: center; 
            gap: 10px; 
            transition: 0.3s;
        }
        .btn-dispatch:hover { background: #fff; transform: translateY(-2px); }

        @media (max-width: 600px) {
            .sticky-footer { flex-direction: column; gap: 15px; text-align: center; }
            .btn-dispatch { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="top-nav">
        <a href="registration_data.php" class="btn-back"><i class="fas fa-chevron-left"></i> Back</a>
        <h2 style="font-family: 'Space Grotesk'; text-transform: uppercase;">Hub Center</h2>
    </div>

    <div class="filter-grid">
        <div class="input-group">
            <i class="fas fa-search"></i>
            <input type="text" id="liveSearch" placeholder="Filter colleges or events...">
        </div>
        <form method="post" style="display: flex; gap: 10px;">
            <input type="date" name="selected_date" value="<?= htmlspecialchars($selectedDate) ?>" style="flex:1; background: #000; border:1px solid var(--glass-border); color:#fff; border-radius:12px; padding:0 15px;" required>
            <button type="submit" class="btn-dispatch" style="padding: 10px 20px;">Fetch</button>
        </form>
    </div>

    <?php if (!empty($grouped)): ?>
    <form method="post">
        <input type="hidden" name="selected_date" value="<?= htmlspecialchars($selectedDate) ?>">

        <label class="global-toggle">
            <span><i class="fas fa-globe-asia"></i> GLOBAL SELECT</span>
            <input type="checkbox" id="global-master" style="transform: scale(1.4);">
        </label>

        <div id="dynamicContainer">
            <?php foreach ($grouped as $college => $items): $collegeID = 'col_' . md5($college); ?>
            <div class="college-card" data-name="<?= strtolower($college) ?>">
                <div class="college-header">
                    <span style="font-weight: 800; color: var(--accent); font-size: 14px;"><?= htmlspecialchars($college) ?></span>
                    <label style="font-size: 11px; cursor: pointer;">
                        <input type="checkbox" class="college-master" data-target="<?= $collegeID ?>"> SELECT ALL
                    </label>
                </div>

                <?php foreach ($items as $idx => $r): $eventID = $collegeID . '_e' . $idx; ?>
                <div class="user-row" data-meta="<?= strtolower($r['event'].' '.$r['department']) ?>">
                    <div class="event-info">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="checkbox" class="event-master <?= $collegeID ?>" data-target="<?= $eventID ?>">
                            <div>
                                <p style="font-weight: 800; font-size: 14px;"><?= htmlspecialchars($r['event']) ?></p>
                                <p style="font-size: 11px; color: var(--text-dim);"><?= htmlspecialchars($r['department']) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="email-flex">
                        <?php
                        $emails = array_filter([$r['first_member_email'] ?? '', $r['second_member_email'] ?? '', $r['third_member_email'] ?? '', $r['fourth_member_email'] ?? '', $r['fifth_member_email'] ?? '']);
                        foreach ($emails as $em): ?>
                            <label class="email-chip">
                                <input type="checkbox" name="reg_ids[]" value="<?= $r['_id'] ?>|<?= $em ?>" class="user-cb <?= $collegeID ?> <?= $eventID ?>">
                                <?= $em ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="sticky-footer">
            <div style="font-weight: 800; font-size: 14px;">
                SELECTED: <span id="selCount" style="color: var(--accent);">0</span>
            </div>
            <button name="send_mail" class="btn-dispatch">
                <i class="fas fa-paper-plane"></i> DISPATCH MAILS
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const globalMaster = document.getElementById('global-master');
    const collegeMasters = document.querySelectorAll('.college-master');
    const eventMasters = document.querySelectorAll('.event-master');
    const userCheckboxes = document.querySelectorAll('.user-cb');
    const selDisplay = document.getElementById('selCount');
    const search = document.getElementById('liveSearch');

    search.addEventListener('input', e => {
        const t = e.target.value.toLowerCase();
        document.querySelectorAll('.college-card').forEach(card => {
            const match = card.dataset.name.includes(t);
            let anyRow = false;
            card.querySelectorAll('.user-row').forEach(row => {
                const rMatch = row.dataset.meta.includes(t) || match;
                row.style.display = rMatch ? 'grid' : 'none';
                if (rMatch) anyRow = true;
            });
            card.style.display = anyRow ? 'block' : 'none';
        });
    });

    const updateCount = () => {
        selDisplay.innerText = document.querySelectorAll('.user-cb:checked').length;
    };

    globalMaster?.addEventListener('change', function() {
        document.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = this.checked);
        updateCount();
    });

    collegeMasters.forEach(m => {
        m.addEventListener('change', function() {
            const target = this.dataset.target;
            document.querySelectorAll('.' + target).forEach(c => c.checked = this.checked);
            updateCount();
        });
    });

    eventMasters.forEach(m => {
        m.addEventListener('change', function() {
            const target = this.dataset.target;
            document.querySelectorAll('.' + target).forEach(c => c.checked = this.checked);
            updateCount();
        });
    });

    userCheckboxes.forEach(c => c.addEventListener('change', updateCount));
});
</script>
</body>
</html>