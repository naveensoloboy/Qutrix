<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_form.html");
    exit();
}

include __DIR__ . '/../db.php';

$message = '';
$message_type = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? 'QUTRIX 2K26');
    $event_date = trim($_POST['event_date'] ?? '2026-09-18 09:00:00');
    $poster_image = trim($_POST['existing_poster'] ?? '');
    
    // Handle File Upload for Poster
    if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['poster_file']['tmp_name'];
        $fileName = $_FILES['poster_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = 'poster_' . time() . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../uploads/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $poster_image = 'uploads/' . $newFileName;
            }
        }
    }

    $support_network = [
        'contact1_role' => trim($_POST['contact1_role'] ?? 'Registration Committee'),
        'contact1_name' => trim($_POST['contact1_name'] ?? 'Naveen S'),
        'contact1_phone' => trim($_POST['contact1_phone'] ?? '+919952655591'),
        
        'contact2_role' => trim($_POST['contact2_role'] ?? 'Registration Committee'),
        'contact2_name' => trim($_POST['contact2_name'] ?? 'Javakarbharathi K'),
        'contact2_phone' => trim($_POST['contact2_phone'] ?? '+916379979364'),
        
        'contact3_role' => trim($_POST['contact3_role'] ?? 'GAIT Treasurer'),
        'contact3_name' => trim($_POST['contact3_name'] ?? 'Vignesh P'),
        'contact3_phone' => trim($_POST['contact3_phone'] ?? '+917010520104'),
        
        'email' => trim($_POST['email'] ?? 'qutrix.official@gmail.com')
    ];

    // Handle Faculty Board lines
    $faculty_text = $_POST['faculty_board_text'] ?? '';
    $faculty_lines = array_map('trim', explode("\n", $faculty_text));
    $faculty_board = array_values(array_filter($faculty_lines, function($line) { return !empty($line); }));

    try {
        $db->settings->updateOne(
            ['_id' => 'event_config'],
            [
                '$set' => [
                    'title' => $title,
                    'event_date' => $event_date,
                    'poster_image' => $poster_image,
                    'support_network' => $support_network,
                    'faculty_board' => $faculty_board,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ],
            ['upsert' => true]
        );
        $message = "Settings updated successfully!";
        $message_type = "success";
    } catch (Exception $e) {
        $message = "Error saving settings: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Fetch Current Settings
$defaults = [
    'title' => 'QUTRIX 2K26',
    'subtitle' => 'An Intercollegiate Technical Symposium',
    'event_date' => '2026-09-18 09:00:00',
    'poster_image' => 'images/img02.jpeg',
    'support_network' => [
        'contact1_role' => 'Registration Committee',
        'contact1_name' => 'Naveen S',
        'contact1_phone' => '+919952655591',
        'contact2_role' => 'Registration Committee',
        'contact2_name' => 'Javakarbharathi K',
        'contact2_phone' => '+916379979364',
        'contact3_role' => 'GAIT Treasurer',
        'contact3_name' => 'Vignesh P',
        'contact3_phone' => '+917010520104',
        'email' => 'qutrix.official@gmail.com'
    ],
    'faculty_board' => [
        'Dr. D. VENUGOPAL (Principal)',
        'Dr. S. Meenakshi (Head)',
        'Dr. B. Srinivasan',
        'Dr. G.T. Prabavathi',
        'Dr. A. Dhanalakshmi',
        'Dr. P. Prabhusundhar',
        'Dr. G.A. Mylavathi',
        'Dr. S. Annapoorani',
        'Mr. P. Sathishkumar'
    ]
];

$doc = $db->settings->findOne(['_id' => 'event_config']);
if ($doc) {
    $data = (array)$doc;
    $settings = array_merge($defaults, $data);
    if (isset($data['support_network'])) {
        $settings['support_network'] = array_merge($defaults['support_network'], (array)$data['support_network']);
    }
    if (isset($data['faculty_board'])) {
        $settings['faculty_board'] = (array)$data['faculty_board'];
    }
} else {
    $settings = $defaults;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Settings | Admin Module</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #020617; 
            --accent: #fbbf24;  
            --accent-glow: rgba(251, 191, 36, 0.3);
            --skyblu: #71e4e4;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-dim: #94a3b8;
            --white: #ffffff;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--primary);
            background-image: radial-gradient(circle at 50% 0%, rgba(251, 191, 36, 0.08) 0%, transparent 50%);
            color: var(--white);
            padding: clamp(15px, 4vw, 40px);
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.8rem;
            color: var(--skyblu);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-back {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            color: var(--white);
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .btn-back:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .alert-success { background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; }
        .alert-danger { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; }

        .card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        .card h3 {
            font-family: 'Space Grotesk', sans-serif;
            color: var(--accent);
            font-size: 1.2rem;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input[type="text"], input[type="datetime-local"], input[type="file"], textarea {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            padding: 12px 15px;
            color: var(--white);
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: 0.3s;
        }

        input[type="text"]:focus, input[type="datetime-local"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .poster-preview {
            max-width: 180px;
            border-radius: 12px;
            margin-top: 10px;
            border: 1px solid var(--glass-border);
        }

        .btn-submit {
            background: var(--accent);
            color: #000;
            border: none;
            padding: 15px 35px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
            box-shadow: 0 10px 20px var(--accent-glow);
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-sliders-h"></i> Event Settings</h1>
        <!-- <div style="display: flex; gap: 10px;">
            <a href="gallery_manager.php" class="btn-back"><i class="fas fa-images"></i> Gallery Manager</a>
            <a href="registration_data.php" class="btn-back"><i class="fas fa-arrow-left"></i> Dashboard</a>
        </div> -->
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?>">
            <i class="fas <?= $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <!-- MAIN EVENT INFO -->
        <div class="card">
            <h3><i class="fas fa-calendar-star"></i> General Event Details</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Event Title / Year</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($settings['title']) ?>" placeholder="e.g. QUTRIX 2K26" required>
                </div>
                <div class="form-group">
                    <label>Event Date & Time (Countdown Target)</label>
                    <input type="text" name="event_date" value="<?= htmlspecialchars($settings['event_date']) ?>" placeholder="e.g. Sep 18, 2026 09:00:00 or 2026-09-18 09:00:00" required>
                </div>
            </div>
        </div>

        <!-- POSTER IMAGE -->
        <div class="card">
            <h3><i class="fas fa-image"></i> Event Poster</h3>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Upload New Poster Image</label>
                    <input type="file" name="poster_file" accept="image/*">
                    <input type="hidden" name="existing_poster" value="<?= htmlspecialchars($settings['poster_image']) ?>">
                </div>
                <?php if (!empty($settings['poster_image'])): ?>
                    <div class="form-group full-width">
                        <label>Current Poster</label>
                        <img src="../<?= htmlspecialchars($settings['poster_image']) ?>" alt="Current Poster" class="poster-preview" onerror="this.src='../images/img02.jpeg'">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SUPPORT NETWORK CONTACTS -->
        <div class="card">
            <h3><i class="fas fa-users-cog"></i> Support Network Contacts</h3>
            
            <p style="color: var(--text-dim); font-size: 0.85rem; margin-bottom: 20px;">Contact 1 (Registration Committee)</p>
            <div class="form-grid" style="margin-bottom: 25px;">
                <div class="form-group">
                    <label>Role / Title</label>
                    <input type="text" name="contact1_role" value="<?= htmlspecialchars($settings['support_network']['contact1_role']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="contact1_name" value="<?= htmlspecialchars($settings['support_network']['contact1_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="contact1_phone" value="<?= htmlspecialchars($settings['support_network']['contact1_phone']) ?>" required>
                </div>
            </div>

            <p style="color: var(--text-dim); font-size: 0.85rem; margin-bottom: 20px;">Contact 2 (Registration Committee)</p>
            <div class="form-grid" style="margin-bottom: 25px;">
                <div class="form-group">
                    <label>Role / Title</label>
                    <input type="text" name="contact2_role" value="<?= htmlspecialchars($settings['support_network']['contact2_role']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="contact2_name" value="<?= htmlspecialchars($settings['support_network']['contact2_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="contact2_phone" value="<?= htmlspecialchars($settings['support_network']['contact2_phone']) ?>" required>
                </div>
            </div>

            <p style="color: var(--text-dim); font-size: 0.85rem; margin-bottom: 20px;">Contact 3 (GAIT Treasurer)</p>
            <div class="form-grid" style="margin-bottom: 25px;">
                <div class="form-group">
                    <label>Role / Title</label>
                    <input type="text" name="contact3_role" value="<?= htmlspecialchars($settings['support_network']['contact3_role']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="contact3_name" value="<?= htmlspecialchars($settings['support_network']['contact3_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="contact3_phone" value="<?= htmlspecialchars($settings['support_network']['contact3_phone']) ?>" required>
                </div>
            </div>

            <p style="color: var(--text-dim); font-size: 0.85rem; margin-bottom: 20px;">Official Email Inquiry</p>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Email Address</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($settings['support_network']['email']) ?>" required>
                </div>
            </div>
        </div>

        <!-- FACULTY BOARD MEMBERS -->
        <div class="card">
            <h3><i class="fas fa-user-tie"></i> Faculty Board Members</h3>
            <div class="form-group full-width">
                <label>Faculty Names (One per line)</label>
                <textarea name="faculty_board_text" rows="10" placeholder="e.g. Dr. D. VENUGOPAL (Principal)&#10;Dr. S. Meenakshi (Head)"><?= htmlspecialchars(implode("\n", $settings['faculty_board'] ?? [])) ?></textarea>
                <small style="color: var(--text-dim); margin-top: 5px; display: block;">
                    <i class="fas fa-info-circle"></i> The top 3 members are displayed as featured board members on the About page. Additional members are shown in the collapsible board list.
                </small>
            </div>
        </div>

        <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Settings</button>
    </form>
</div>

</body>
</html>
