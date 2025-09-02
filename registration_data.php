<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_form.html");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php'; // MongoDB connection

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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Event Registrations</title>
<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f4;
        color: #333;
        margin: 0;
        padding: 0;
    }

    h2 {
        text-align: center;
        margin-top: 50px;
        color: #2c3e50;
    }

    ul {
        list-style: none;
        padding: 0;
        max-width: 600px;
        margin: 50px auto;
    }

    li {
        margin: 15px 0;
        text-align: center;
    }

    a {
        font-weight: 600;
        display: block;
        padding: 15px;
        text-decoration: none;
        color: #fff;
        background-color: #0044ff;
        border-radius: 15px;
        transition: background-color 0.3s ease;
    }

    a:hover {
        background-color: white;
        color: #0044ff;
    }

    table {
        width: 80%;
        margin: 50px auto;
        border-collapse: collapse;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }

    th, td {
        padding: 15px;
        text-align: center;
        border: 1px solid #ddd;
    }

    th {
        background-color: #2c3e50;
        color: #fff;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tr:hover {
        background-color: #e1e1e1;
    }

    @media (max-width: 600px) {
        h2 {
            font-size: 1.5em;
        }

        a {
            font-size: 1em;
            padding: 12px;
        }

        table {
            width: 100%;
        }
    }
</style>
</head>
<body>

<h2>EVENT</h2>
<ul>
<?php foreach ($display_events as $event) : ?>
    <li>
        <a href="event_data.php?event=<?= urlencode((string)($event ?? '')) ?>">
            <?= htmlspecialchars((string)($event ?? '')) ?>
        </a>
    </li>
<?php endforeach; ?>
</ul>

<?php
// Display registration counts
$rows = iterator_to_array($result);
if (count($rows) > 0) {
    echo "<table>
            <tr>
                <th>S. No.</th>
                <th>College Name</th>
                <th>Registration Count</th>
            </tr>";

    $serial_no = 1;
    foreach ($rows as $row) {
        echo "<tr>
                <td>{$serial_no}</td>
                <td>" . htmlspecialchars((string)($row['_id'] ?? '')) . "</td>
                <td>" . ($row['registration_count'] ?? 0) . "</td>
              </tr>";
        $serial_no++;
    }
    echo "</table>";
} 

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

    echo "<div style='
        text-align: center; 
        font-size: 20px; 
        font-weight: bold; 
        background-color: #2c3e50; 
        color: white; 
        padding: 15px; 
        border-radius: 10px; 
        width: 50%; 
        margin: 20px auto;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    '>
    Number of Unique Members: " . $uniqueCount . "
    </div>";

    echo "<div class='d' style='
        text-align: center; 
        font-size: 20px; 
        font-weight: bold; 
        color: fff; 
        padding: 15px; 
        border-radius: 10px; 
        width: 50%; 
        margin: 20px auto;
    '><a href='admin_register.html'>Register New Admin</a></div>";

    echo "<div class='d' style='
        text-align: center; 
        font-size: 20px; 
        font-weight: bold; 
        color: fff; 
        padding: 15px; 
        border-radius: 10px; 
        width: 50%; 
        margin: 20px auto;
    '><a href='feedback_display.php'>Feedbacks</a></div>";
}
?>

</body>
</html>
