<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php'; // MongoDB connection file

$event = isset($_GET['event']) ? $_GET['event'] : '';

$collection = $db->registrations;

// MongoDB equivalent of FIND_IN_SET → search inside events array or string match
$filter = [
    '$or' => [
        ['events' => $event],            // if "events" stored as string
        ['events' => ['$in' => [$event]]] // if "events" stored as array
    ]
];

$options = [
    'sort' => ['college_name' => 1] // ASC
];

$result = $collection->find($filter, $options);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Data: <?php echo htmlspecialchars($event); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        h2 { text-align: center; color: #0044ff; }
        .table-container { width: 100%; overflow-x: auto; margin: 0 auto; background-color: #fff; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; min-width: 1200px; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #0044ff; color: #fff; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .view-btn { padding: 5px 10px; background-color: #0044ff;border-radius: 5px; color: white; border: none; cursor: pointer; text-decoration: none; }
    </style>
</head>
<body>

<h2>Data for <?php echo htmlspecialchars($event); ?></h2>
<div class="table-container">
<table>
    <tr>
        <th>College Name</th>
        <th>Department</th>
        <th>Event</th>
        <th colspan="3">First Member</th>
        <th colspan="3">Second Member</th>
        <th colspan="3">Third Member</th>
        <th colspan="3">Fourth Member</th>
        <th>Phone No</th>
        <th>Alt Phone No</th>
        <th>Email</th>
        <th>Register at</th>
    </tr>
    <tr>
        <th></th><th></th><th></th>
        <th>Name</th><th>Roll No</th><th>Bonafide</th>
        <th>Name</th><th>Roll No</th><th>Bonafide</th>
        <th>Name</th><th>Roll No</th><th>Bonafide</th>
        <th>Name</th><th>Roll No</th><th>Bonafide</th>
        <th></th><th></th><th></th><th></th>
    </tr>
    <?php
    $found = false;
    foreach ($result as $row) {
        $found = true;
        echo "<tr>
            <td>" . htmlspecialchars($row['college_name']) . "</td>
            <td>" . htmlspecialchars($row['department']) . "</td>
            <td>" . htmlspecialchars(is_array($row['events']) ? implode(', ', $row['events']) : $row['events']) . "</td>
            
            <td>" . htmlspecialchars($row['first_member_name']) . "</td>
            <td>" . htmlspecialchars($row['first_member_rollno']) . "</td>
            <td>" . (!empty($row['first_member_bonafide']) ? "<a href='../" . htmlspecialchars($row['first_member_bonafide']) . "' target='_blank' class='view-btn'>View</a>" : "N/A") . "</td>
            
            <td>" . htmlspecialchars($row['second_member_name']) . "</td>
            <td>" . htmlspecialchars($row['second_member_rollno']) . "</td>
            <td>" . (!empty($row['second_member_bonafide']) ? "<a href='../" . htmlspecialchars($row['second_member_bonafide']) . "' target='_blank' class='view-btn'>View</a>" : "N/A") . "</td>
            
            <td>" . (!empty($row['third_member_name']) ? htmlspecialchars($row['third_member_name']) : "N/A") . "</td>
            <td>" . (!empty($row['third_member_rollno']) ? htmlspecialchars($row['third_member_rollno']) : "N/A") . "</td>
            <td>" . (!empty($row['third_member_bonafide']) ? "<a href='../" . htmlspecialchars($row['third_member_bonafide']) . "' target='_blank' class='view-btn'>View</a>" : "N/A") . "</td>
            
            <td>" . (!empty($row['fourth_member_name']) ? htmlspecialchars($row['fourth_member_name']) : "N/A") . "</td>
            <td>" . (!empty($row['fourth_member_rollno']) ? htmlspecialchars($row['fourth_member_rollno']) : "N/A") . "</td>
            <td>" . (!empty($row['fourth_member_bonafide']) ? "<a href='../" . htmlspecialchars($row['fourth_member_bonafide']) . "' target='_blank' class='view-btn'>View</a>" : "N/A") . "</td>
            
            <td>" . htmlspecialchars($row['phone_no']) . "</td>
            <td>" . htmlspecialchars($row['alt_phone_no']) . "</td>
            <td>" . htmlspecialchars($row['email']) . "</td>
            <td>" . htmlspecialchars($row['created_at']) . "</td>
        </tr>";
    }

    if (!$found) {
        echo "<tr><td colspan='19'>No data found for this event</td></tr>";
    }
    ?>
</table>
</div>
</body>
</html>
