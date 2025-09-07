<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../db.php'; // MongoDB connection

$event = isset($_GET['event']) ? $_GET['event'] : '';

// ✅ Query MongoDB
$filter = ['event' => ['$regex' => $event, '$options' => 'i']];
$options = ['sort' => ['college_name' => 1]]; 
$result = $db->registrations->find($filter, $options);

// Count total registrations
$totalRegistrations = $db->registrations->countDocuments($filter);

// Count unique colleges
$uniqueColleges = $db->registrations->distinct('college_name', $filter);
$collegeCount = count($uniqueColleges);

// Helper function to safely handle BSONArray or null values
if (!function_exists('safeString')) {
    function safeString($value) {
        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        } elseif (is_array($value) || $value instanceof MongoDB\Model\BSONArray) {
            // Convert array to a comma-separated string
            return htmlspecialchars(implode(", ", (array)$value), ENT_QUOTES, 'UTF-8');
        } elseif ($value instanceof MongoDB\BSON\UTCDateTime) {
            // Convert MongoDB date to string
            return $value->toDateTime()->format("Y-m-d H:i:s");
        } else {
            return "N/A";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Data: <?php echo htmlspecialchars($event); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #7209b7;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #ffffffff;
            --warning-color: #f72585;
            --gray-color: #6c757d;
            --light-gray: #e9ecef;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--dark-color);
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 2200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        header {
            background: var(--primary-color);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        
        h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .event-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: #3a0ca3;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 15px 20px;
            border-radius: 10px;
            text-align: center;
            min-width: 180px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--gray-color);
        }
        
        .controls {
            display: flex;
            justify-content: space-between;
            padding: 15px 25px;
            background: var(--light-gray);
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 30px;
            padding: 8px 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .search-box input {
            border: none;
            outline: none;
            padding: 8px 10px;
            font-size: 1rem;
            width: 250px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-secondary {
            background: var(--gray-color);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .table-container {
            width: 100%;
            overflow-x: auto;
            padding: 0 20px 20px;
            max-height: 70vh;
        }
        
        table {
    width: 100%;
    border-collapse: collapse;
    min-width: 2000px;
    margin-top: 20px;
}

th, td {
    border: 1px solid #000000ff; /* 👈 full borders */
    padding: 12px 10px;
    text-align: left;
    font-size: 0.9rem;
}
        
        th {
            background: var(--secondary-color);
            color: white;
            position: sticky;
            top: 0;
            font-weight: 600;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e9ecef;
        }
        
        .member-section {
            background: var(--light-gray);
            font-weight: 600;
        }
        
        .view-btn {
            padding: 6px 10px;
            background: var(--primary-color);
            border-radius: 4px;
            color: white;
            text-decoration: none;
            display: inline-block;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .view-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .contact-info {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--gray-color);
            font-size: 1.2rem;
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--light-gray);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            padding: 20px;
            gap: 10px;
        }
        
        .page-btn {
            padding: 8px 15px;
            background: var(--light-gray);
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .page-btn.active {
            background: var(--primary-color);
            color: white;
        }
        
        @media (max-width: 1200px) {
            .stats {
                flex-direction: column;
                align-items: center;
            }
            
            .stat-card {
                width: 100%;
                max-width: 300px;
            }
            
            .controls {
                flex-direction: column;
            }
            
            .search-box {
                width: 100%;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 1.8rem;
            }
            
            .event-name {
                font-size: 1.4rem;
            }
            
            .btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
        
        .college-badge {
            background: var(--success-color);
            /* color: white; */
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .email-link {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .email-link:hover {
            text-decoration: underline;
        }
        
        .phone-link {
            color: var(--dark-color);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- <header>
            <h1>Event Registration Data</h1>
            <p class="event-name"><?php echo htmlspecialchars($event); ?></p>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalRegistrations; ?></div>
                    <div class="stat-label">Total Registrations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $collegeCount; ?></div>
                    <div class="stat-label">Participating Colleges</div>
                </div>
                
            </div>
        </header> -->
        
        <div class="controls">
            <div class="search-box">
                <p class="event-name"><?php echo htmlspecialchars($event); ?></p>
            </div> 
            
            <div class="stat-card">
                    <div class="stat-number"><?php echo $totalRegistrations; ?></div>
                    <div class="stat-label">Total Registrations</div>
            </div>

            <div class="stat-card">
                    <div class="stat-number"><?php echo $collegeCount; ?></div>
                    <div class="stat-label">Participating Colleges</div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <button class="btn btn-secondary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        
        <div class="table-container">
            <table id="registrationsTable">
                <thead>
                    <tr>
                        <th>College Name</th>
                        <th>Department</th>
                        <th>Event</th>
                        <th colspan="5">First Member</th>
                        <th colspan="5">Second Member</th>
                        <th colspan="5">Third Member</th>
                        <th colspan="5">Fourth Member</th>
                        <th colspan="5">Fifth Member</th>
                        <th>Registered at</th>
                    </tr>
                    <tr class="member-section">
                        <th></th><th></th><th></th>
                        <th>Name</th><th>Roll No</th><th>Phone</th><th>Email</th><th>Bonafide</th>
                        <th>Name</th><th>Roll No</th><th>Phone</th><th>Email</th><th>Bonafide</th>
                        <th>Name</th><th>Roll No</th><th>Phone</th><th>Email</th><th>Bonafide</th>
                        <th>Name</th><th>Roll No</th><th>Phone</th><th>Email</th><th>Bonafide</th>
                        <th>Name</th><th>Roll No</th><th>Phone</th><th>Email</th><th>Bonafide</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $dataFound = false;
                    foreach ($result as $row) {
                        $dataFound = true;
                        
                        echo "<tr>
                            <td><span class='college-badge'>" . safeString($row["college_name"]) . "</span></td>
                            <td>" . safeString($row["department"]) . "</td>
                            <td>" . safeString($row["event"]) . "</td>

                            <!-- First Member -->
                            <td>" . safeString($row["first_member_name"]) . "</td>
                            <td>" . safeString($row["first_member_rollno"]) . "</td>
                            <td class='contact-info'>" . safeString($row["first_member_phone"]) . "</td>
                            <td class='contact-info'><a href='mailto:" . safeString($row["first_member_email"]) . "' class='email-link'>" . safeString($row["first_member_email"]) . "</a></td>
                            <td>" . (!empty($row["first_member_bonafide"]) ? "<a href='../" . safeString($row["first_member_bonafide"]) . "' target='_blank' class='view-btn'><i class='fas fa-eye'></i> View</a>" : "N/A") . "</td>

                            <!-- Second Member -->
                            <td>" . safeString($row["second_member_name"]) . "</td>
                            <td>" . safeString($row["second_member_rollno"]) . "</td>
                            <td class='contact-info'>" . safeString($row["second_member_phone"]) . "</td>
                            <td class='contact-info'><a href='mailto:" . safeString($row["second_member_email"]) . "' class='email-link'>" . safeString($row["second_member_email"]) . "</a></td>
                            <td>" . (!empty($row["second_member_bonafide"]) ? "<a href='../" . safeString($row["second_member_bonafide"]) . "' target='_blank' class='view-btn'><i class='fas fa-eye'></i> View</a>" : "N/A") . "</td>

                            <!-- Third Member -->
                            <td>" . safeString($row["third_member_name"] ?? "") . "</td>
                            <td>" . safeString($row["third_member_rollno"] ?? "") . "</td>
                            <td class='contact-info'>" . safeString($row["third_member_phone"] ?? "") . "</td>
                            <td class='contact-info'><a href='mailto:" . safeString($row["third_member_email"] ?? "") . "' class='email-link'>" . safeString($row["third_member_email"] ?? "") . "</a></td>
                            <td>" . (!empty($row["third_member_bonafide"]) ? "<a href='../" . safeString($row["third_member_bonafide"]) . "' target='_blank' class='view-btn'><i class='fas fa-eye'></i> View</a>" : "N/A") . "</td>

                            <!-- Fourth Member -->
                            <td>" . safeString($row["fourth_member_name"] ?? "") . "</td>
                            <td>" . safeString($row["fourth_member_rollno"] ?? "") . "</td>
                            <td class='contact-info'>" . safeString($row["fourth_member_phone"] ?? "") . "</td>
                            <td class='contact-info'><a href='mailto:" . safeString($row["fourth_member_email"] ?? "") . "' class='email-link'>" . safeString($row["fourth_member_email"] ?? "") . "</a></td>
                            <td>" . (!empty($row["fourth_member_bonafide"]) ? "<a href='../" . safeString($row["fourth_member_bonafide"]) . "' target='_blank' class='view-btn'><i class='fas fa-eye'></i> View</a>" : "N/A") . "</td>

                            <!-- Fifth Member -->
                            <td>" . safeString($row["fifth_member_name"] ?? "") . "</td>
                            <td>" . safeString($row["fifth_member_rollno"] ?? "") . "</td>
                            <td class='contact-info'>" . safeString($row["fifth_member_phone"] ?? "") . "</td>
                            <td class='contact-info'><a href='mailto:" . safeString($row["fifth_member_email"] ?? "") . "' class='email-link'>" . safeString($row["fifth_member_email"] ?? "") . "</a></td>
                            <td>" . (!empty($row["fifth_member_bonafide"] ?? null) ? "<a href='../" . safeString($row["fifth_member_bonafide"]) . "' target='_blank' class='view-btn'><i class='fas fa-eye'></i> View</a>" : "N/A") . "</td>


                            <td>" . (isset($row['created_at']) && $row['created_at'] instanceof MongoDB\BSON\UTCDateTime? $row['created_at']->toDateTime()->format('M j, Y g:i A'): "N/A") . "</td>

                        </tr>";
                    }

                    if (!$dataFound) {
                        echo "<tr><td colspan='31'>
                            <div class='no-data'>
                                <i class='fas fa-inbox'></i>
                                <p>No registrations found for this event</p>
                            </div>
                        </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn">Next <i class="fas fa-chevron-right"></i></button>
        </div> -->
    </div>

    <!-- <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#registrationsTable tbody tr');
            
            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Highlight search term in table
        function highlightText(text) {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            if (!searchTerm) return text;
            
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            return text.replace(regex, '<mark>$1</mark>');
        }
    </script> -->
</body>
</html>