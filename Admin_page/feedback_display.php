<?php
include __DIR__ . '/../db.php'; // MongoDB connection

$collection = $db->feedback;
$cursor = $collection->find([], [
    'sort' => ['submission_date' => 1]
]);

// Convert cursor to array to check if empty reliably
$feedbackData = iterator_to_array($cursor);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Intelligence | QUTRIX 2K26</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #020617; 
            --accent: #38bdf8;  
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
            --white: #ffffff;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--primary);
            background-image: radial-gradient(circle at 50% 0%, rgba(56, 189, 248, 0.08) 0%, transparent 50%);
            color: var(--white);
            padding: clamp(15px, 4vw, 40px);
            min-height: 100vh;
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 40px;
        }

        h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(24px, 5vw, 32px);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .badge {
            background: rgba(56, 189, 248, 0.1);
            color: var(--accent);
            padding: 5px 15px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        /* --- TABLE STYLING --- */
        .table-wrapper {
            background: var(--glass);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            min-width: 1000px;
        }

        th {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent);
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 18px 20px;
            font-size: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.02);
            color: var(--text-dim);
            vertical-align: middle;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
            color: var(--white);
        }

        .name-cell { color: var(--white); font-weight: 600; }
        .college-cell { font-size: 13px; max-width: 250px; }
        .comment-cell { 
            font-style: italic; 
            max-width: 300px; 
            white-space: normal; 
            line-height: 1.5; 
        }

        /* Rating Indicators */
        .rating-tag {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .rating-very-good { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .rating-good { background: rgba(56, 189, 248, 0.1); color: #38bdf8; }
        .rating-satisfactory { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .rating-poor { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        .no-feedback {
            text-align: center;
            padding: 60px;
            color: var(--text-dim);
        }

        .no-feedback i {
            font-size: 50px;
            margin-bottom: 20px;
            opacity: 0.2;
        }

        /* Scrollbar Styling */
        .table-responsive::-webkit-scrollbar { height: 8px; }
        .table-responsive::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); }
        .table-responsive::-webkit-scrollbar-thumb { background: var(--glass-border); border-radius: 10px; }

        @media (max-width: 768px) {
            body { padding: 10px; }
            .table-wrapper { border-radius: 15px; }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        
        <h2>Feedback Responses</h2>
    </header>

    <div class="table-wrapper">
        <div class="table-responsive">
            <?php if (empty($feedbackData)): ?>
                <div class="no-feedback">
                    <i class="fas fa-comment-slash"></i>
                    <p>No feedback transmissions found in the database.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Participant</th>
                            <th>Institution</th>
                            <th>Opinion</th>
                            <th>Experience</th>
                            <th>Organization</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbackData as $row): 
                            // Function to get rating class
                            $getRatingClass = function($val) {
                                $val = strtolower($val ?? "");
                                if (strpos($val, 'very good') !== false) return 'rating-very-good';
                                if (strpos($val, 'good') !== false) return 'rating-good';
                                if (strpos($val, 'satisfactory') !== false) return 'rating-satisfactory';
                                if (strpos($val, 'poor') !== false) return 'rating-poor';
                                return '';
                            };
                        ?>
                        <tr>
                            <td class="name-cell">
                                <i class="far fa-user" style="margin-right: 8px; font-size: 12px; color: var(--accent);"></i>
                                <?= htmlspecialchars($row["name"] ?? "Anonymous") ?>
                            </td>
                            <td class="college-cell"><?= htmlspecialchars($row["college_name"] ?? "N/A") ?></td>
                            <td><span class="rating-tag <?= $getRatingClass($row['opinion']) ?>"><?= htmlspecialchars($row["opinion"] ?? "-") ?></span></td>
                            <td><span class="rating-tag <?= $getRatingClass($row['experience']) ?>"><?= htmlspecialchars($row["experience"] ?? "-") ?></span></td>
                            <td><span class="rating-tag <?= $getRatingClass($row['organization']) ?>"><?= htmlspecialchars($row["organization"] ?? "-") ?></span></td>
                            <td class="comment-cell"><?= nl2br(htmlspecialchars($row["comments"] ?? "No comments provided.")) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>