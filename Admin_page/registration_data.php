<?php
session_start();

// ======================================================
// LOGIN CHECK
// ======================================================

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_form.html");
    exit();
}

// ======================================================
// ERROR REPORTING
// ======================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ======================================================
// DATABASE CONNECTION
// ======================================================

include __DIR__ . '/../db.php';

use MongoDB\BSON\ObjectId;

// Helper function for safe session invalidation
function terminate_invalid_session() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    header("Location: admin_login_form.html");
    exit();
}

// ======================================================
// GET LOGGED-IN ADMIN
// ======================================================

$admin_id = $_SESSION['admin_id'];

// Check whether admin ID is valid ObjectId
try {
    $admin = $db->admin->findOne([
        '_id' => new ObjectId($admin_id)
    ]);
} catch (\Throwable $e) {
    terminate_invalid_session();
}

// ======================================================
// ADMIN EXISTENCE CHECK
// ======================================================

if (!$admin) {
    terminate_invalid_session();
}

// ======================================================
// GET ADMIN EVENT / ROLE
// ======================================================

$admin_event = $admin['event'] ?? null;

// ======================================================
// ADMIN ROLE DEFINITIONS
// ======================================================

// Primary Administrator
$is_primary_admin = ($admin_event === "ADMINISTRATOR");

// Secondary Administrator
$is_secondary_admin = ($admin_event === "SECONDARY_ADMIN");

// Primary + Secondary Admin can view everything
$can_view_all_events = (
    $is_primary_admin ||
    $is_secondary_admin
);

// ======================================================
// ALL EVENTS
// ======================================================

$all_events = [
    "PAPER PRESENTATION",
    "QUIZ",
    "WORD HUNT",
    "WEB DESIGN",
    "SOFTWARE CONTEST",
    "MARKETING",
    "NON TECHNICAL ROUND DANCING"
];

// ======================================================
// DETERMINE EVENTS TO DISPLAY
// ======================================================

if ($can_view_all_events) {
    $display_events = $all_events;
} else {
    $display_events = [];
    if (!empty($admin_event)) {
        $display_events[] = $admin_event;
    }
}

// ======================================================
// FETCH REGISTRATIONS
// ======================================================

if ($can_view_all_events) {
    $pipeline = [
        [
            '$group' => [
                '_id' => '$college_name',
                'registration_count' => [
                    '$sum' => 1
                ]
            ]
        ],
        [
            '$sort' => [
                'registration_count' => -1
            ]
        ]
    ];
} else {
    $pipeline = [
        [
            '$match' => [
                'events' => $admin_event
            ]
        ],
        [
            '$group' => [
                '_id' => '$college_name',
                'registration_count' => [
                    '$sum' => 1
                ]
            ]
        ],
        [
            '$sort' => [
                'registration_count' => -1
            ]
        ]
    ];
}

// ======================================================
// RUN REGISTRATION QUERY
// ======================================================

$result = $db->registrations->aggregate($pipeline);

// ======================================================
// UNIQUE PARTICIPANTS
// ======================================================

$uniqueCount = 0;

if ($can_view_all_events) {
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
        foreach ([
            'first_member_rollno',
            'second_member_rollno',
            'third_member_rollno',
            'fourth_member_rollno'
        ] as $key) {
            if (!empty($doc[$key])) {
                $allRollNumbers[] = (string)$doc[$key];
            }
        }
    }

    $uniqueRollNumbers = array_unique($allRollNumbers);
    $uniqueCount = count($uniqueRollNumbers);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | QUTRIX 2K26</title>

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- GOOGLE FONTS -->
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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--primary);
            background-image: radial-gradient(
                circle at 10% 10%,
                rgba(56, 189, 248, 0.05) 0%,
                transparent 30%
            );
            color: var(--white);
            padding: clamp(10px, 4vw, 30px);
            min-height: 100vh;
        }

        .dashboard-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header-section {
            text-align: center;
            margin-bottom: 40px;
        }

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

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
            color: #fff;
        }

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
            ul.event-list {
                grid-template-columns: 1fr;
            }

            .admin-actions {
                flex-direction: column;
            }

            .admin-actions a {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">

    <!-- HEADER -->
    <div class="header-section">
        <h2>Event</h2>
        <p style="color: var(--text-dim); margin-top: -10px; margin-bottom: 20px;">
            Oversight Dashboard
        </p>
    </div>

    <!-- EVENT LIST -->
    <ul class="event-list">
        <?php foreach ($display_events as $event): ?>
            <li>
                <a href="event_data.php?event=<?= urlencode((string)$event) ?>">
                    <?= htmlspecialchars((string)$event) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- REGISTRATION TABLE -->
    <div class="table-container">
        <?php $rows = iterator_to_array($result); ?>
        <?php if (count($rows) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>S. No.</th>
                        <th>College Name</th>
                        <th>Registrations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $serial_no = 1;
                    foreach ($rows as $row):
                    ?>
                        <tr>
                            <td style="color: var(--accent); font-weight: bold;">
                                <?= $serial_no ?>
                            </td>
                            <td style="text-align: left;">
                                <?= htmlspecialchars((string)($row['_id'] ?? '')) ?>
                            </td>
                            <td style="font-weight: 800; color: #fff;">
                                <?= $row['registration_count'] ?? 0 ?>
                            </td>
                        </tr>
                    <?php
                    $serial_no++;
                    endforeach;
                    ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 30px; text-align: center; color: var(--text-dim);">
                No registrations found.
            </div>
        <?php endif; ?>
    </div>

    <!-- ADMIN CONTROLS -->
    <div class="header-section">

        <?php if ($is_secondary_admin): ?>
            <div class="unique-stats">
                <span style="font-size: 11px; text-transform: uppercase; color: var(--text-dim); display: block;">
                    Total Unique Participants
                </span>
                <span style="font-size: 28px; font-weight: 800; color: var(--accent);">
                    <?= $uniqueCount ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- PRIMARY ADMIN ONLY: STATS & MANAGEMENT -->
        <?php if ($is_primary_admin): ?>
            <div class="unique-stats">
                <span style="font-size: 11px; text-transform: uppercase; color: var(--text-dim); display: block;">
                    Total Unique Participants
                </span>
                <span style="font-size: 28px; font-weight: 800; color: var(--accent);">
                    <?= $uniqueCount ?>
                </span>
            </div>

            <div class="admin-actions" style="margin-bottom: 20px;">
                <a href="event_settings.php"><i class="fas fa-cog"></i> Event Settings</a>
                <a href="gallery_manager.php"><i class="fas fa-images"></i> Gallery Manager</a>
                <a href="timeline_manager.php"><i class="fas fa-calendar-alt"></i> Timeline Manager</a>
                <a href="send_mail.php"><i class="fas fa-envelope"></i> Mail</a>
                <a href="admin_register.html"><i class="fas fa-user-plus"></i> New Admin</a>
                <a href="feedback_display.php"><i class="fas fa-comment-dots"></i> Feedback Logs</a>
            </div>
        <?php endif; ?>

        <!-- LOGOUT (ACCESSIBLE TO ALL ADMINS) -->
        <div class="admin-actions">
            <a href="logout.php" style="border-color: #ef4444; color: #ef4444;">
                <i class="fas fa-power-off"></i> Logout
            </a>
        </div>

    </div>

</div>

</body>
</html>