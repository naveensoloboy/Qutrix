<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_form.html");
    exit();
}

include __DIR__ . '/../db.php';

$message = '';
$message_type = '';

// Handle Image Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $title = trim($_POST['title'] ?? '');
    
    // Support custom year if specified
    $year_val = !empty($_POST['custom_year']) ? trim($_POST['custom_year']) : trim($_POST['year'] ?? '2026');
    
    // Support custom meta tag if specified
    $meta = !empty($_POST['custom_meta']) ? strtoupper(trim($_POST['custom_meta'])) : trim($_POST['meta'] ?? 'CURRENT');
    
    $isCoordinator = ($year_val === 'COORDINATOR');
    $year = $isCoordinator ? '' : (is_numeric($year_val) ? (int)$year_val : $year_val);

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image_file']['tmp_name'];
        $fileName = $_FILES['image_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = 'gallery_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../uploads/gallery/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_src = 'uploads/gallery/' . $newFileName;

                $doc = [
                    'year' => $year,
                    'src' => $image_src,
                    'title' => $title,
                    'meta' => $meta,
                    'isCoordinator' => $isCoordinator,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $db->gallery->insertOne($doc);
                $message = "Gallery image uploaded successfully!";
                $message_type = "success";
            } else {
                $message = "Failed to save uploaded file.";
                $message_type = "error";
            }
        } else {
            $message = "Invalid file format! Only JPG, JPEG, PNG, WEBP, and GIF are allowed.";
            $message_type = "error";
        }
    } else {
        $message = "Please select an image file to upload.";
        $message_type = "error";
    }
}

// Handle Image Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delete_id = trim($_POST['image_id'] ?? '');

    if (!empty($delete_id)) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($delete_id);
            $doc = $db->gallery->findOne(['_id' => $objectId]);

            if ($doc) {
                // Delete local file if uploaded into uploads/
                if (!empty($doc['src']) && strpos($doc['src'], 'uploads/') === 0) {
                    $localFilePath = __DIR__ . '/../' . $doc['src'];
                    if (file_exists($localFilePath)) {
                        @unlink($localFilePath);
                    }
                }

                $db->gallery->deleteOne(['_id' => $objectId]);
                $message = "Image deleted successfully from gallery!";
                $message_type = "success";
            } else {
                $message = "Image record not found.";
                $message_type = "error";
            }
        } catch (Exception $e) {
            $message = "Invalid image ID provided.";
            $message_type = "error";
        }
    }
}

// Fetch all gallery images
$galleryImages = [];
try {
    $cursor = $db->gallery->find([], ['sort' => ['created_at' => -1]]);
    foreach ($cursor as $doc) {
        $galleryImages[] = [
            'id' => (string)$doc['_id'],
            'year' => $doc['year'] ?? '',
            'src' => $doc['src'] ?? '',
            'title' => $doc['title'] ?? '',
            'meta' => $doc['meta'] ?? 'LEGACY',
            'isCoordinator' => !empty($doc['isCoordinator']),
            'created_at' => $doc['created_at'] ?? 'N/A'
        ];
    }
} catch (Exception $e) {
    $galleryImages = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Manager | QUTRIX Admin</title>
    
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
            max-width: 1200px;
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
            margin-bottom: 50px;
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

        .form-group label {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--skyblu);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'JetBrains Mono', monospace;
        }

        .form-control {
            background: rgba(2, 6, 23, 0.8);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 12px 16px;
            color: var(--white);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 15px var(--accent-glow);
        }

        select.form-control option {
            background: #020617;
            color: #fff;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--accent), #f59e0b);
            color: #000;
            border: none;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 800;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 25px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px var(--accent-glow);
        }

        /* --- GALLERY DISPLAY GRID --- */
        .gallery-admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }

        .image-card {
            background: rgba(2, 6, 23, 0.8);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .image-card:hover {
            border-color: var(--accent);
            transform: translateY(-4px);
        }

        .img-preview {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            background: #000;
        }

        .img-details {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .img-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--white);
            margin-bottom: 8px;
        }

        .img-tags {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .badge-year {
            background: rgba(251, 191, 36, 0.15);
            color: var(--accent);
            border: 1px solid rgba(251, 191, 36, 0.3);
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
        }

        .badge-meta {
            background: rgba(113, 228, 228, 0.15);
            color: var(--skyblu);
            border: 1px solid rgba(113, 228, 228, 0.3);
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.4);
        }

        @media (max-width: 768px) {
            .admin-nav { flex-direction: column; gap: 15px; }
            .admin-links { justify-content: center; }
            .card-box { padding: 20px; }
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
            <a href="gallery_manager.php" class="active"><i class="fas fa-images"></i> Gallery Manager</a>
            <a href="send_mail.php"><i class="fas fa-envelope"></i> Mailer</a>
            <a href="feedback_display.php"><i class="fas fa-comment-dots"></i> Feedback</a>
        </div>
    </nav> -->

    <div class="container">

        <div class="page-header">
            <h1>Gallery <span>Manager</span></h1>
            <p style="color: var(--text-dim);">Upload new photos or delete existing showcase images</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?>">
                <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- UPLOAD FORM CARD -->
        <div class="card-box">
            <h2><i class="fas fa-cloud-upload-alt"></i> Upload New Gallery Photo</h2>
            <form action="gallery_manager.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Image Title / Caption</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Inaugural Ceremony" required>
                    </div>

                    <div class="form-group">
                        <label>Edition Year / Section</label>
                        <select name="year" id="yearSelect" class="form-control" onchange="toggleCustomInputs()">
                            <option value="2026">2026 Edition (Current)</option>
                            <option value="2025">2025 Edition (Evolution)</option>
                            <option value="2024">2024 Edition (Legacy)</option>
                            <option value="2023">2023 Edition (Legacy)</option>
                            <option value="2018">2018 Edition (Legacy)</option>
                            <option value="COORDINATOR">GAIT Coordinator (Executive)</option>
                            <option value="CUSTOM">+ Add New / Custom Year</option>
                        </select>
                        <input type="text" name="custom_year" id="customYearInput" class="form-control" placeholder="Type new year (e.g. 2027)" style="display: none; margin-top: 8px;">
                    </div>

                    <div class="form-group">
                        <label>Category Meta Tag</label>
                        <select name="meta" id="metaSelect" class="form-control" onchange="toggleCustomInputs()">
                            <option value="CURRENT">CURRENT</option>
                            <option value="EVOLUTION">EVOLUTION</option>
                            <option value="LEGACY" selected>LEGACY</option>
                            <option value="EXECUTIVE">EXECUTIVE</option>
                            <option value="CUSTOM">+ Add Custom Meta Tag</option>
                        </select>
                        <input type="text" name="custom_meta" id="customMetaInput" class="form-control" placeholder="Type new tag (e.g. AWARDS)" style="display: none; margin-top: 8px;">
                    </div>

                    <div class="form-group">
                        <label>Select Photo File</label>
                        <input type="file" name="image_file" class="form-control" accept="image/*" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-upload"></i> Upload Photo To Gallery
                </button>
            </form>
        </div>

        <!-- EXISTING IMAGES CARD -->
        <div class="card-box">
            <h2><i class="fas fa-images"></i> Manage Gallery Photos (<?= count($galleryImages) ?> Images)</h2>

            <div class="gallery-admin-grid">
                <?php foreach ($galleryImages as $img): ?>
                    <div class="image-card">
                        <img src="../<?= htmlspecialchars($img['src']) ?>" alt="<?= htmlspecialchars($img['title']) ?>" class="img-preview" onerror="this.src='../images/img02.jpeg'">
                        <div class="img-details">
                            <div>
                                <div class="img-title"><?= htmlspecialchars($img['title'] ?: 'Untitled Photo') ?></div>
                                <div class="img-tags">
                                    <span class="badge-year"><?= htmlspecialchars($img['isCoordinator'] ? 'COORDINATOR' : ($img['year'] ? $img['year'] . ' EDITION' : 'GALLERY')) ?></span>
                                    <span class="badge-meta"><?= htmlspecialchars($img['meta']) ?></span>
                                </div>
                            </div>
                            
                            <form action="gallery_manager.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this photo permanently?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="image_id" value="<?= htmlspecialchars($img['id']) ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="fas fa-trash-alt"></i> Delete Photo
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <script>
        function toggleCustomInputs() {
            const yearSelect = document.getElementById('yearSelect');
            const customYearInput = document.getElementById('customYearInput');
            const metaSelect = document.getElementById('metaSelect');
            const customMetaInput = document.getElementById('customMetaInput');

            if (yearSelect.value === 'CUSTOM') {
                customYearInput.style.display = 'block';
                customYearInput.required = true;
            } else {
                customYearInput.style.display = 'none';
                customYearInput.required = false;
                customYearInput.value = '';
            }

            if (metaSelect.value === 'CUSTOM') {
                customMetaInput.style.display = 'block';
                customMetaInput.required = true;
            } else {
                customMetaInput.style.display = 'none';
                customMetaInput.required = false;
                customMetaInput.value = '';
            }
        }
    </script>
</body>
</html>
