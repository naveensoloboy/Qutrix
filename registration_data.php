<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_form.html");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

$admin_id = $_SESSION['admin_id'];

// Fetch the admin's assigned event
$sql = "SELECT event FROM admin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($admin_event);
$stmt->fetch();
$stmt->close();

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

// If the admin is not "ADMINISTRATOR", filter by their assigned event
if ($admin_event !== "ADMINISTRATOR") {
    $sql = "SELECT college_name, COUNT(*) AS registration_count 
            FROM registrations 
            WHERE events = ? 
            GROUP BY college_name 
            ORDER BY registration_count DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $admin_event);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fetch all event registrations for "ADMINISTRATOR"
    $sql = "SELECT college_name, COUNT(*) AS registration_count 
            FROM registrations 
            GROUP BY college_name 
            ORDER BY registration_count DESC";

    $result = $conn->query($sql);
}

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
        <li><a href="event_data.php?event=<?= urlencode($event) ?>"><?= htmlspecialchars($event) ?></a></li>
    <?php endforeach; ?>
</ul>

<?php
if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>S. No.</th>
                <th>College Name</th>
                <th>Registration Count</th>
            </tr>";

    $serial_no = 1;

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $serial_no . "</td>
                <td>" . htmlspecialchars($row["college_name"]) . "</td>
                <td>" . $row["registration_count"] . "</td>
              </tr>";
        $serial_no++;
    }

    echo "</table>";
} else {
    echo "<p style='text-align: center;'>No registrations found.</p>";
}


if ($admin_event === "ADMINISTRATOR") { 
// Query to get all roll numbers from the participants table
$sql = "SELECT first_member_rollno, second_member_rollno, third_member_rollno, fourth_member_rollno FROM registrations";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $allRollNumbers = [];

    while ($row = $result->fetch_assoc()) {
        if (!empty($row['first_member_rollno'])) $allRollNumbers[] = $row['first_member_rollno'];
        if (!empty($row['second_member_rollno'])) $allRollNumbers[] = $row['second_member_rollno'];
        if (!empty($row['third_member_rollno'])) $allRollNumbers[] = $row['third_member_rollno'];
        if (!empty($row['fourth_member_rollno'])) $allRollNumbers[] = $row['fourth_member_rollno'];
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
} else {
    echo "<p style='text-align: center;'>No participants found.</p>";
}

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
