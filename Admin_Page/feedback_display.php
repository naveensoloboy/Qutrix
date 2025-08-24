<?php

include "db.php";

// Query to fetch feedback data
$sql = "SELECT name, college_name, opinion, experience, organization, comments FROM feedback ORDER BY submission_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Responses</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #2c3e50;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        .no-feedback {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            table {
                width: 100%;
            }

            th, td {
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>

<h2>Feedback Responses</h2>

<?php
if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Name</th>
                <th>College</th>
                <th>Opinion</th>
                <th>Experience</th>
                <th>Organization</th>
                <th>Comments</th>
                <th>Submitted At</th>
            </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row["name"]) . "</td>
                <td>" . htmlspecialchars($row["college_name"]) . "</td>
                <td>" . htmlspecialchars($row["opinion"]) . "</td>
                <td>" . htmlspecialchars($row["experience"]) . "</td>
                <td>" . htmlspecialchars($row["organization"]) . "</td>
                <td>" . htmlspecialchars($row["comments"]) . "</td>
                <td>" . htmlspecialchars($row["submission_date"]) . "</td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "<p class='no-feedback'>No feedback responses available.</p>";
}

// Close connection
$conn->close();
?>

</body>
</html>
