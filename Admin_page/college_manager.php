<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_form.html");
    exit();
}

include __DIR__ . '/../db.php';
use MongoDB\BSON\ObjectId;

$message = '';
$message_type = '';

// Check admin role
$admin_id = $_SESSION['admin_id'];
try {
    $currentAdmin = $db->admin->findOne(['_id' => new ObjectId($admin_id)]);
} catch (Exception $e) {
    $currentAdmin = null;
}

// Handle Add / Delete College
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $college_name = trim($_POST['college_name'] ?? '');

        if (empty($college_name)) {
            $message = "Please enter a valid college name.";
            $message_type = "error";
        } else {
            // Check if college already exists
            $existing = $db->colleges->findOne(['name' => ['$regex' => '^' . preg_quote($college_name) . '$', '$options' => 'i']]);
            if ($existing) {
                $message = "This college already exists in the list!";
                $message_type = "error";
            } else {
                $db->colleges->insertOne([
                    'name' => $college_name,
                    'created_at' => new MongoDB\BSON\UTCDateTime()
                ]);
                $message = "College '" . htmlspecialchars($college_name) . "' added successfully!";
                $message_type = "success";
            }
        }
    } elseif ($action === 'delete' && !empty($_POST['college_id'])) {
        try {
            $objectId = new ObjectId($_POST['college_id']);
            $db->colleges->deleteOne(['_id' => $objectId]);
            $message = "College deleted successfully!";
            $message_type = "success";
        } catch (Exception $e) {
            $message = "Error deleting college: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Fetch all colleges sorted alphabetically
$collegesList = [];
try {
    $cursor = $db->colleges->find([], ['sort' => ['name' => 1]]);
    foreach ($cursor as $doc) {
        $collegesList[] = [
            'id' => (string)$doc['_id'],
            'name' => $doc['name'] ?? ''
        ];
    }
} catch (Exception $e) {
    $collegesList = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College List Manager | QUTRIX Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Space+Grotesk:wght@500;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --primary: #020617;
            --accent: #fbbf24;
            --accent-glow: rgba(251, 191, 36, 0.4);
            --skyblu: #71e4e4;
            --bg-dark: #020617;
            --glass: rgba(15, 23, 42, 0.75);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
            --white: #ffffff;
            --danger: #ef4444;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(251, 191, 36, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 100% 50%, rgba(113, 228, 228, 0.08) 0%, transparent 40%);
            color: #e2e8f0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            padding-bottom: 80px;
        }

        /* --- ADMIN TOP NAVBAR --- */
        .admin-nav {
            background: rgba(2, 6, 23, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .admin-brand {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--skyblu);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-brand span { color: var(--accent); }

        .admin-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .admin-links a {
            color: var(--text-dim);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            border: 1px solid var(--glass-border);
            background: var(--glass);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-links a:hover, .admin-links a.active {
            color: var(--accent);
            border-color: var(--accent);
            background: rgba(251, 191, 36, 0.1);
        }

        /* --- MAIN CONTAINER --- */
        .container {
            max-width: 1100px;
            margin: 40px auto 0;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--white);
            text-transform: uppercase;
        }

        .page-header h1 span { color: var(--accent); }

        /* --- ALERT MESSAGES --- */
        .alert {
            padding: 15px 20px;
            border-radius: 14px;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-success { background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e; color: #4ade80; }
        .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; }

        /* --- FORM CARD --- */
        .card-box {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 35px;
            backdrop-filter: blur(20px);
            margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .card-box h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            color: var(--white);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-box h2 i { color: var(--accent); }

        .form-row {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .form-control {
            flex-grow: 1;
            background: rgba(2, 6, 23, 0.8);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 14px 18px;
            color: var(--white);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 15px var(--accent-glow);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--accent), #f59e0b);
            color: #000;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 800;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px var(--accent-glow);
        }

        /* --- COLLEGE LIST TABLE --- */
        .search-container {
            margin-bottom: 20px;
        }

        .college-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .college-item {
            background: rgba(2, 6, 23, 0.7);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 16px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            transition: all 0.25s ease;
        }

        .college-item:hover {
            border-color: var(--accent);
            transform: translateX(4px);
        }

        .college-name {
            font-weight: 700;
            font-size: 1rem;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .college-name i {
            color: var(--accent);
            font-size: 1.1rem;
        }

        .btn-delete-small {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-delete-small:hover {
            background: #ef4444;
            color: #fff;
        }

        @media (max-width: 768px) {
            .admin-nav { flex-direction: column; gap: 15px; }
            .admin-links { justify-content: center; }
            .form-row { flex-direction: column; }
            .btn-submit { width: 100%; justify-content: center; }
            .college-item { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

    <!-- ADMIN NAVBAR -->
    <!-- <nav class="admin-nav">
        <a href="registration_data.php" class="admin-brand">
            QU<span>TRIX ADMIN</span>
        </a>
        <div class="admin-links">
            <a href="registration_data.php"><i class="fas fa-users"></i> Registrations</a>
            <a href="event_settings.php"><i class="fas fa-cog"></i> Event Settings</a>
            <a href="gallery_manager.php"><i class="fas fa-images"></i> Gallery</a>
            <a href="timeline_manager.php"><i class="fas fa-calendar-alt"></i> Timeline</a>
            <a href="college_manager.php" class="active"><i class="fas fa-university"></i> Colleges</a>
            <a href="send_mail.php"><i class="fas fa-envelope"></i> Mailer</a>
            <a href="feedback_display.php"><i class="fas fa-comment-dots"></i> Feedback</a>
        </div>
    </nav> -->

    <div class="container">

        <div class="page-header">
            <h1>College List <span>Management</span></h1>
            <!-- <p style="color: var(--text-dim);">Add new colleges anytime to make them instantly available in the registration form</p> -->
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?>">
                <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- ADD NEW COLLEGE CARD -->
        <div class="card-box">
            <h2><i class="fas fa-plus-circle"></i> Add New College</h2>
            <form action="college_manager.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <input type="text" name="college_name" class="form-control" placeholder="Enter Full College Name (e.g. KONGU ENGINEERING COLLEGE)" required>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus-circle"></i> Add College
                    </button>
                </div>
            </form>
        </div>

        <!-- LIST OF COLLEGES -->
        <div class="card-box">
            <h2><i class="fas fa-university"></i> Registered Colleges (<span id="collegeCount"><?= count($collegesList) ?></span>)</h2>

            <div class="search-container">
                <input type="text" id="searchFilter" class="form-control" placeholder="🔍 Search colleges in list..." onkeyup="filterColleges()">
            </div>

            <div class="college-list" id="collegeList">
                <?php foreach ($collegesList as $college): ?>
                    <div class="college-item" data-name="<?= htmlspecialchars(strtolower($college['name'])) ?>">
                        <div class="college-name">
                            <i class="fas fa-graduation-cap"></i>
                            <?= htmlspecialchars($college['name']) ?>
                        </div>
                        <form action="college_manager.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this college?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="college_id" value="<?= htmlspecialchars($college['id']) ?>">
                            <button type="submit" class="btn-delete-small">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <script>
        function filterColleges() {
            const query = document.getElementById('searchFilter').value.toLowerCase();
            const items = document.querySelectorAll('.college-item');
            let count = 0;

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                if (name.includes(query)) {
                    item.style.display = 'flex';
                    count++;
                } else {
                    item.style.display = 'none';
                }
            });

            document.getElementById('collegeCount').innerText = count;
        }
    </script>
</body>
</html>
