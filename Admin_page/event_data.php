<?php

date_default_timezone_set('Asia/Kolkata');
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

// --- FULL CSV EXPORT LOGIC ---
if (isset($_GET['action']) && $_GET['action'] == 'export_csv') {
    // Clear any previous output to ensure a clean CSV
    ob_end_clean();
   
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Registrations_'.str_replace(' ', '_', $event).'_'.date('Ymd').'.csv');
   
    $output = fopen('php://output', 'w');
   
    // 1. Define Column Headers (All 31 Columns)
    fputcsv($output, [
        'S.No', 'College Name', 'Department', 'Event Category',
        'M1 Name', 'M1 RollNo', 'M1 Phone', 'M1 Email', 'M1 Bonafide',
        'M2 Name', 'M2 RollNo', 'M2 Phone', 'M2 Email', 'M2 Bonafide',
        'M3 Name', 'M3 RollNo', 'M3 Phone', 'M3 Email', 'M3 Bonafide',
        'M4 Name', 'M4 RollNo', 'M4 Phone', 'M4 Email', 'M4 Bonafide',
        'M5 Name', 'M5 RollNo', 'M5 Phone', 'M5 Email', 'M5 Bonafide',
        'Registration Timestamp'
    ]);

    // 2. Fetch and Write Data
    $export_result = $db->registrations->find($filter, $options);
    $count = 1;
    foreach ($export_result as $row) {
        fputcsv($output, [
            $count++,
            $row['college_name'] ?? 'N/A',
            $row['department'] ?? 'N/A',
            $row['event'] ?? 'N/A',
            // Member 1
            $row['first_member_name'] ?? '', $row['first_member_rollno'] ?? '', $row['first_member_phone'] ?? '', $row['first_member_email'] ?? '', $row['first_member_bonafide'] ?? '',
            // Member 2
            $row['second_member_name'] ?? '', $row['second_member_rollno'] ?? '', $row['second_member_phone'] ?? '', $row['second_member_email'] ?? '', $row['second_member_bonafide'] ?? '',
            // Member 3
            $row['third_member_name'] ?? '', $row['third_member_rollno'] ?? '', $row['third_member_phone'] ?? '', $row['third_member_email'] ?? '', $row['third_member_bonafide'] ?? '',
            // Member 4
            $row['fourth_member_name'] ?? '', $row['fourth_member_rollno'] ?? '', $row['fourth_member_phone'] ?? '', $row['fourth_member_email'] ?? '', $row['fourth_member_bonafide'] ?? '',
            // Member 5
            $row['fifth_member_name'] ?? '', $row['fifth_member_rollno'] ?? '', $row['fifth_member_phone'] ?? '', $row['fifth_member_email'] ?? '', $row['fifth_member_bonafide'] ?? '',
            // Date
            isset($row['created_at']) ? $row['created_at']->toDateTime()->format('Y-m-d H:i:s') : 'N/A'
        ]);
    }
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Data: <?php echo htmlspecialchars($event); ?></title>
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
            --table-border: #334155;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--primary);
            background-image: radial-gradient(circle at 50% 0%, rgba(56, 189, 248, 0.08) 0%, transparent 50%);
            color: var(--white);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .btn-export {
    background: #10b981; /* Emerald Green for Excel */
    color: var(--primary);
    border: none;
    text-decoration: none;
}

.btn-export:hover {
    background: #ffffff;
    color: #10b981;
    box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
}

/* Ensure buttons look uniform in the group */
.btn-group {
    display: flex;
    gap: 12px;
    align-items: center;
}

        /* --- CONTROL BAR --- */
        .controls {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .event-title-box {
            display: flex;
            flex-direction: column;
        }

        .event-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-group {
            display: flex;
            gap: 20px;
        }

        .stat-pill {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            padding: 10px 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-pill .num {
            display: block;
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--white);
        }

        .stat-pill .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--text-dim);
            letter-spacing: 1px;
        }

        /* --- TABLE AREA --- */
        .table-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            max-height: 70vh;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 2800px; /* Force scroll for large columns */
        }

        th {
            background: #0f172a;
            color: var(--accent);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 15px 12px;
            border: 1px solid var(--table-border);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .member-header {
            background: #1e293b;
            color: var(--white);
            font-weight: 800;
        }

        td {
            padding: 12px;
            font-size: 0.85rem;
            border: 1px solid var(--table-border);
            color: var(--text-dim);
            background: rgba(2, 6, 23, 0.4);
        }

        tr:hover td {
            background: rgba(56, 189, 248, 0.05);
            color: var(--white);
        }

        .college-badge {
            color: var(--accent);
            font-weight: 700;
        }

        .contact-info { font-family: monospace; }
        .email-link { color: var(--accent); text-decoration: none; }

        .view-btn {
            background: var(--accent);
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.75rem;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .view-btn:hover {
            background: var(--white);
            transform: translateY(-2px);
        }

        /* --- ACTION BUTTONS --- */
        .btn-group { display: flex; gap: 10px; }
       
        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            font-size: 0.9rem;
        }

        .btn-print { background: var(--white); color: var(--primary); }
        .btn-refresh { background: var(--glass); color: var(--white); border: 1px solid var(--glass-border); }
        .btn:hover { transform: scale(1.05); filter: brightness(1.1); }

        /* Custom Scrollbar */
        .table-container::-webkit-scrollbar { height: 10px; width: 10px; }
        .table-container::-webkit-scrollbar-track { background: var(--primary); }
        .table-container::-webkit-scrollbar-thumb { background: var(--table-border); border-radius: 5px; }
        .table-container::-webkit-scrollbar-thumb:hover { background: var(--accent); }

        @media print {
        /* 1. Global Reset for Paper */
        @page {
            size: A4 landscape;
            margin: 6mm; /* Tight margins to maximize data space */
        }
        body {
            background: #fff !important;
            background-image: none !important;
            color: #000 !important;
            padding: 0 !important;
            margin: 0 !important;
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
        } 
        
        /* 2. Hide Web Elements */
        .controls, .btn, .view-btn, .btn-group, i, .stat-group {
            display: none !important;
        }     
        
        /* 3. Reveal Print Header */
        .print-only-header {
            display: block !important;
        }      
        /* 4. Table Transformation */
        .container {
            max-width: 100% !important;
            width: 100% !important;
            animation: none !important;
        }   
        /* Reveal Executive Header */
    .print-executive-header {
        display: block !important;
        margin-bottom: 15px;
    }   

    .letterhead-container {
        display: flex;
        align-items: center;
        border-bottom: 2pt solid #000;
        padding-bottom: 8px;
        margin-bottom: 10px;
    }

    .institutional-seal {
        width: 70px;
        height: 70px;
        border: 2px solid #000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 20pt;
        margin-right: 20px;
    }

    .institutional-details h1 { font-size: 18pt; margin: 0; letter-spacing: 0.5px; }
    .institutional-details p { font-size: 9pt; margin: 0; }
    .department-branding { font-weight: bold; text-transform: uppercase; margin-top: 3px !important; }

    .document-title {
        text-align: center;
        font-weight: bold;
        font-size: 12pt;
        background-color: #f0f0f0 !important;
        border: 1px solid #000;
        padding: 4px;
    }

    .meta-data-bar {
        display: flex;
        justify-content: space-around;
        border: 1px solid #000;
        border-top: none;
        padding: 5px;
        font-size: 8pt;
        margin-bottom: 15px;
    }
        .table-card {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            overflow: visible !important;
        }      
        .table-container {
            overflow: visible !important;
            max-height: none !important;
        }   
           
        table {
            width: 100% !important;
            min-width: 100% !important; /* Allow it to shrink to fit page */
            border: 1px solid #000 !important;
            border-collapse: collapse !important;
            table-layout: fixed;
        }
        th, td {
            border: 0.5tx solid #000 !important;
            color: #000 !important;
            padding: 3px 1px !important;
            font-size: 8pt !important; /* Smaller font to fit many columns */
            background: transparent !important;
            word-wrap: break-word;
            text-align: center;
        }      
        th {
            background-color: #f2f2f2 !important;
            font-weight: bold !important;
            text-transform: uppercase;
        }     
        .member-header {
            background-color: #e5e5e5 !important;
        }      
        /* 5. Force text visibility */
        .college-badge, .num, .event-name {
            color: #000 !important;
            font-weight: bold !important;
        }      
        /* 6. Avoid breaking a squad across two pages */
        tr {
            page-break-inside: avoid;
        }
        /* Zebra Striping for Scannability across 31 columns */
        tr:nth-child(even) td {
            background-color: #f9f9f9 !important;
        } 
        /* --- SIGNATURE DOCK --- */
    .print-signature-dock {
        display: flex !important;
        justify-content: space-between;
        margin-top: 5rem;
        padding: 0 50px;
    }
    .sig-line {
        text-align: center;
        width: 180px;
       
        font-size: 9pt;
        padding-top: 5px;
        font-weight: bold;
    }

    /* Hide ID Proof content only */
    .view-btn { display: none !important; }

    /* Hide Timestamp content and heading */
    td:last-child,th:last-child { display: none !important; }

    /* Improve readability */
    table {
        table-layout: auto !important;
        font-size: 7pt !important;
    }

    th, td {
        white-space: normal !important;
        word-break: break-word;
    }

    .print-hide { display: none !important; }


    /* Prevent ugly text splitting */
    th, td {
        white-space: normal !important;
        font-size: 7pt !important;
    }

    /* Allow long text (department) to wrap normally */
    td:nth-child(3) {
        white-space: normal !important;
    }

    /* Slightly reduce padding to gain space */
    th, td {
        padding: 2px !important;
    }

}


/* Hide print header on screen */
.print-only-header {
    display: none;
}

/* Screen visibility controls */
.print-executive-header, .print-signature-dock { display: none; }
    </style>
</head>
<body>
<div class="print-executive-header">
    <div class="letterhead-container">
        <div class="institutional-seal">Q26</div>
        <div class="institutional-details">
            <h1>GOBI ARTS & SCIENCE COLLEGE (AUTONOMOUS)</h1>
            <p>Affiliated to Bharathiar University · Re-accredited with 'A' Grade by NAAC</p>
            <p class="department-branding">PG & RESEARCH DEPARTMENT OF COMPUTER SCIENCE</p>
        </div>
    </div>
   
    <div class="document-metadata">
        <div class="document-title">OFFICIAL REGISTRATION TRANSCRIPT – QUTRIX 2K26</div>
        <div class="meta-data-bar">
            <span><strong>EVENT : </strong> <?php echo htmlspecialchars($event); ?></span>
            <span><strong>TIMESTAMP : </strong> <?php echo date('d-M-Y h:i A'); ?></span>
            <span><strong>TOTAL TEAMS : </strong> <?php echo $totalRegistrations; ?></span>
            <span><strong>INSTITUTIONS : </strong> <?php echo $collegeCount; ?></span>
        </div>
    </div>
</div>
    <div class="container">
        <div class="controls">
            <div class="event-title-box">
                <span style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-dim); letter-spacing: 2px;">Database Records</span>
                <h1 class="event-name"><?php echo htmlspecialchars($event); ?></h1>
            </div>

            <div class="stat-group">
                <div class="stat-pill">
                    <span class="num"><?php echo $totalRegistrations; ?></span>
                    <span class="label">Registrations</span>
                </div>
                <div class="stat-pill">
                    <span class="num"><?php echo $collegeCount; ?></span>
                    <span class="label">Institutions</span>
                </div>
            </div>

            <div class="btn-group">
    <a href="?event=<?= urlencode($event) ?>&action=export_csv" class="btn btn-export">
        <i class="fas fa-file-excel"></i> Export to Excel
    </a>

    <button class="btn btn-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Report
    </button>

    <button class="btn btn-refresh" onclick="location.reload()">
        <i class="fas fa-sync-alt"></i>
    </button>
</div>
        </div>

        <div class="table-card">
            <div class="table-container">
                <div style="display:none;" class="print-header">
                    <h2>QUTRIX – <?php echo htmlspecialchars($event); ?> Report</h2>
                    <p>Total Registrations: <?php echo $totalRegistrations; ?> |
                       Institutions: <?php echo $collegeCount; ?></p>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th rowspan="2">S.No</th>
                            <th rowspan="2">Institution</th>
                            <th rowspan="2">Department</th>
                           
                            <th colspan="5" class="member-header">Primary Participant (Lead)</th>
                            <th colspan="5" class="member-header">Squad Member 02</th>
                            <th colspan="5" class="member-header">Squad Member 03</th>
                            <th colspan="5" class="member-header">Squad Member 04</th>
                            <th colspan="5" class="member-header">Squad Member 05</th>
                            <th rowspan="2">Timestamp</th>
                        </tr>
                        <tr>
                            <th>Full Name</th><th>Roll No</th><th>Contact</th><th>Email ID</th><th><span class="print-hide">ID Proof</span></th>
                            <th>Full Name</th><th>Roll No</th><th>Contact</th><th>Email ID</th><th><span class="print-hide">ID Proof</span></th>
                            <th>Full Name</th><th>Roll No</th><th>Contact</th><th>Email ID</th><th><span class="print-hide">ID Proof</span></th>
                            <th>Full Name</th><th>Roll No</th><th>Contact</th><th>Email ID</th><th><span class="print-hide">ID Proof</span></th>
                            <th>Full Name</th><th>Roll No</th><th>Contact</th><th>Email ID</th><th><span class="print-hide">ID Proof</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
$dataFound = false;
$sno = 1;

foreach ($result as $row) {
    $dataFound = true;

    // ✅ FIX: Convert UTC → IST BEFORE echo
    $createdAtIST = "N/A";
    if (isset($row['created_at']) && $row['created_at'] instanceof MongoDB\BSON\UTCDateTime) {
        $dt = $row['created_at']->toDateTime();
        $dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
        $createdAtIST = $dt->format('M j, Y g:i A');
    }

    echo "<tr>
        <td style='text-align:center; font-weight:700; color:white;'>".$sno++."</td>
        <td><span class='college-badge'>" . safeString($row['college_name']) . "</span></td>
        <td>" . safeString($row['department']) . "</td>

        <td>" . safeString($row['first_member_name']) . "</td>
        <td>" . safeString($row['first_member_rollno']) . "</td>
        <td class='contact-info'>" . safeString($row['first_member_phone']) . "</td>
        <td class='contact-info'><a href='mailto:" . safeString($row['first_member_email']) . "' class='email-link'>" . safeString($row['first_member_email']) . "</a></td>
        <td><span class='print-hide'>" .
(!empty($row['first_member_bonafide'])
    ? "<a href='" . safeString($row['first_member_bonafide']) . "' target='_blank' class='view-btn'>View</a>"
    : "N/A")
. "</span></td>

        <td>" . safeString($row['second_member_name']) . "</td>
        <td>" . safeString($row['second_member_rollno']) . "</td>
        <td class='contact-info'>" . safeString($row['second_member_phone']) . "</td>
        <td class='contact-info'><a href='mailto:" . safeString($row['second_member_email']) . "' class='email-link'>" . safeString($row['second_member_email']) . "</a></td>
       <td><span class='print-hide'>" .
(!empty($row['second_member_bonafide'])
    ? "<a href='" . safeString($row['second_member_bonafide']) . "' target='_blank' class='view-btn'>View</a>"
    : "N/A")
. "</span></td>

        <td>" . safeString($row['third_member_name'] ?? '') . "</td>
        <td>" . safeString($row['third_member_rollno'] ?? '') . "</td>
        <td class='contact-info'>" . safeString($row['third_member_phone'] ?? '') . "</td>
        <td class='contact-info'>" . safeString($row['third_member_email'] ?? '') . "</td>
        <td><span class='print-hide'>" .
(!empty($row['third_member_bonafide'])
    ? "<a href='" . safeString($row['third_member_bonafide']) . "' target='_blank' class='view-btn'>View</a>"
    : "N/A")
. "</span></td>

        <td>" . safeString($row['fourth_member_name'] ?? '') . "</td>
        <td>" . safeString($row['fourth_member_rollno'] ?? '') . "</td>
        <td class='contact-info'>" . safeString($row['fourth_member_phone'] ?? '') . "</td>
        <td class='contact-info'>" . safeString($row['fourth_member_email'] ?? '') . "</td>
        <td><span class='print-hide'>" .
(!empty($row['fourth_member_bonafide'])
    ? "<a href='" . safeString($row['fourth_member_bonafide']) . "' target='_blank' class='view-btn'>View</a>"
    : "N/A")
. "</span></td>

        <td>" . safeString($row['fifth_member_name'] ?? '') . "</td>
        <td>" . safeString($row['fifth_member_rollno'] ?? '') . "</td>
        <td class='contact-info'>" . safeString($row['fifth_member_phone'] ?? '') . "</td>
        <td class='contact-info'>" . safeString($row['fifth_member_email'] ?? '') . "</td>
        <td><span class='print-hide'>" .
(!empty($row['fifth_member_bonafide'])
    ? "<a href='" . safeString($row['fifth_member_bonafide']) . "' target='_blank' class='view-btn'>View</a>"
    : "N/A")
. "</span></td>

        <td><span class='print-hide'>{$createdAtIST}</span></td>
    </tr>";
}

if (!$dataFound) {
    echo "<tr>
        <td colspan='31' style='text-align:center; padding:50px; color:#94a3b8;'>
            No registration records found.
        </td>
    </tr>";
}
?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="print-signature-dock">
    <div class="sig-line">TEAM HEAD</div>
    <div class="sig-line">STAFF - INCHARGE</div>
    <div class="sig-line">GAIT CO-ORDINATOR</div>
</div>
</body>
</html>