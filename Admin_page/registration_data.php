<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_form.html");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../db.php'; // MongoDB connection

use MongoDB\BSON\ObjectId;

$admin_id = $_SESSION['admin_id'];

// Convert string to ObjectId
$admin = $db->admin->findOne(['_id' => new ObjectId($admin_id)]);
$admin_event = $admin['event'] ?? null;


// List of all events
$all_events = [
    "PAPER PRESENTATION",
    "QUIZ",
    "WORD HUNT",
    "WEB DESIGN",
    "SOFTWARE CONTEST",
    "MARKETING",
    "NON TECHNICAL ROUND DANCING"
];

// If admin is not "ADMINISTRATOR", show only their assigned event
$display_events = ($admin_event === "ADMINISTRATOR") ? $all_events : [$admin_event];

// Fetch registrations grouped by college
if ($admin_event !== "ADMINISTRATOR") {
    $pipeline = [
        ['$match' => ['events' => $admin_event]],
        ['$group' => ['_id' => '$college_name', 'registration_count' => ['$sum' => 1]]],
        ['$sort' => ['registration_count' => -1]]
    ];
} else {
    $pipeline = [
        ['$group' => ['_id' => '$college_name', 'registration_count' => ['$sum' => 1]]],
        ['$sort' => ['registration_count' => -1]]
    ];
}

$result = $db->registrations->aggregate($pipeline);
?>





<?php
// ... [Keep all your existing PHP logic here: session_start, MongoDB connection, pipeline, and result fetching] ...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | QUTRIX 2K26</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #020617; 
            --accent: #38bdf8;  
            --accent-glow: rgba(56, 189, 248, 0.3);
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
            --white: #ffffff;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--primary);
            background-image: radial-gradient(circle at 10% 10%, rgba(56, 189, 248, 0.05) 0%, transparent 30%);
            color: var(--white);
            padding: clamp(10px, 4vw, 30px);
            min-height: 100vh;
        }

        .dashboard-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* --- HEADER & STATS --- */
        .header-section { text-align: center; margin-bottom: 40px; }
        
        h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(22px, 5vw, 32px);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }

        .unique-stats {
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid var(--accent);
            padding: 20px;
            border-radius: 20px;
            display: inline-block;
            box-shadow: 0 0 20px var(--accent-glow);
            margin-bottom: 30px;
        }

        /* --- EVENT BUTTONS --- */
        ul.event-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            list-style: none;
            margin-bottom: 50px;
        }

        ul.event-list li a {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            padding: 18px;
            border-radius: 15px;
            text-decoration: none;
            color: var(--white);
            font-weight: 700;
            font-size: 13px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            height: 100%;
        }

        ul.event-list li a:hover {
            background: var(--accent);
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px var(--accent-glow);
        }

        /* --- DATA TABLE --- */
        .table-container {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow-x: auto;
            margin-bottom: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }

        th {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent);
            text-align: center;
        }

        td {
            padding: 14px;
            font-size: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.02);
            text-align: center;
            color: var(--text-dim);
        }

        tr:hover td { background: rgba(255, 255, 255, 0.02); color: #fff; }

        /* --- ADMIN LINKS --- */
        .admin-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .admin-actions a {
            padding: 12px 25px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            color: var(--white);
            text-decoration: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            transition: 0.3s;
        }

        .admin-actions a:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        @media (max-width: 600px) {
            ul.event-list { grid-template-columns: 1fr; }
            .admin-actions { flex-direction: column; }
            .admin-actions a { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <div class="header-section">
        <h2>Event</h2>
        <p style="color: var(--text-dim); margin-top: -10px; margin-bottom: 20px;">Oversight Dashboard</p>
    </div>

    <ul class="event-list">
        <?php foreach ($display_events as $event) : ?>
            <li>
                <a href="event_data.php?event=<?= urlencode((string)($event ?? '')) ?>">
                    <?= htmlspecialchars((string)($event ?? '')) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="table-container">
        <?php
        $rows = iterator_to_array($result);
        if (count($rows) > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th>S. No.</th>
                        <th>College Name</th>
                        <th>Registrations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $serial_no = 1; foreach ($rows as $row) : ?>
                        <tr>
                            <td style="color: var(--accent); font-weight: bold;"><?= $serial_no ?></td>
                            <td style="text-align: left;"><?= htmlspecialchars((string)($row['_id'] ?? '')) ?></td>
                            <td style="font-weight: 800; color: #fff;"><?= ($row['registration_count'] ?? 0) ?></td>
                        </tr>
                    <?php $serial_no++; endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php
    // Extra queries for ADMINISTRATOR
if ($admin_event === "ADMINISTRATOR") {
    $cursor = $db->registrations->find([], [
        'projection' => [
            'first_member_rollno' => 1,
            'second_member_rollno' => 1,
            'third_member_rollno' => 1,
            'fourth_member_rollno' => 1
        ]
    ]);

    $allRollNumbers = [];
    foreach ($cursor as $doc) {
        foreach (['first_member_rollno','second_member_rollno','third_member_rollno','fourth_member_rollno'] as $key) {
            if (!empty($doc[$key])) $allRollNumbers[] = $doc[$key];
        }
    }

    $uniqueRollNumbers = array_unique($allRollNumbers);
    $uniqueCount = count($uniqueRollNumbers);
    
     if ($admin_event === "ADMINISTRATOR") : ?>
        <div class="header-section">
            <div class="unique-stats">
                <span style="font-size: 11px; text-transform: uppercase; color: var(--text-dim); display: block;">Total Unique Participants</span>
                <span style="font-size: 28px; font-weight: 800; color: var(--accent);"><?= $uniqueCount ?></span>
            </div>
            
            <div class="admin-actions">
                <a href='admin_register.html'><i class="fas fa-user-plus"></i> New Admin</a>
                <a href='feedback_display.php'><i class="fas fa-comment-dots"></i> Feedback Logs</a>
                <!-- <a href='logout.php' style="border-color: #ef4444; color: #ef4444;"><i class="fas fa-power-off"></i> Logout</a> -->
            </div>
        </div>
    <?php endif; }?>
</div>

</body>
</html>