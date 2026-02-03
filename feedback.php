<?php
require 'db.php'; // MongoDB connection file

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $college_name = $_POST['collegename'] ?? '';
    $opinion = $_POST['opinion'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $organization = $_POST['organization'] ?? '';
    $comments = $_POST['comments'] ?? '';

    $success = false;
    $message = "";

    try {
        $collection = $db->feedback;
        $insertOneResult = $collection->insertOne([
            'name' => $name,
            'college_name' => $college_name,
            'opinion' => $opinion,
            'experience' => $experience,
            'organization' => $organization,
            'comments' => $comments,
            'submission_date' => new MongoDB\BSON\UTCDateTime()
        ]);

        if ($insertOneResult->getInsertedCount() > 0) {
            $success = true;
            $message = "Thank you for your feedback!";
        } else {
            $message = "Error: Unable to save feedback.";
        }
    } catch (Exception $e) {
        $message = "System Error: " . $e->getMessage();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Status | QUTRIX 2K26</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');

        :root {
            --bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --accent: <?php echo $success ? '#10b981' : '#f43f5e'; ?>;
            --accent-glow: <?php echo $success ? 'rgba(16, 185, 129, 0.2)' : 'rgba(244, 63, 94, 0.2)'; ?>;
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(56, 189, 248, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(129, 140, 248, 0.05) 0%, transparent 40%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .status-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) forwards;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: var(--accent-glow);
            border: 2px solid var(--accent);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 25px;
            font-size: 32px;
            color: var(--accent);
            box-shadow: 0 0 20px var(--accent-glow);
        }

        h1 {
            color: var(--text-main);
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        p {
            color: var(--text-dim);
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--text-main);
            color: var(--bg);
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            filter: brightness(0.9);
        }

        .btn-home i {
            font-size: 16px;
        }

        @media (max-width: 480px) {
            .status-card {
                padding: 30px 20px;
            }
            h1 { font-size: 20px; }
        }
    </style>
</head>
<body>

    <div class="status-card">
        <div class="icon-wrapper">
            <i class="fa-solid <?php echo $success ? 'fa-check' : 'fa-xmark'; ?>"></i>
        </div>
        
        <h1><?php echo $success ? 'Submission Successful' : 'Submission Failed'; ?></h1>
        
        <p><?php echo htmlspecialchars($message); ?></p>

        <a href="index.html" class="btn-home">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Home
        </a>
    </div>

</body>
</html>
<?php
}
?>