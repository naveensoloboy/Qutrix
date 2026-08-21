<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_form.html");
    exit();
}

include __DIR__ . '/../db.php';

$message = '';
$message_type = '';

// Handle Add / Edit / Delete Timeline Slot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add' || $action === 'edit') {
        $time_slot = trim($_POST['time_slot'] ?? '');
        $order = (int)($_POST['order'] ?? 1);
        $is_break = isset($_POST['is_break']) && $_POST['is_break'] == '1';
        
        $doc = [
            'order' => $order,
            'time_slot' => $time_slot,
            'is_break' => $is_break,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($is_break) {
            $doc['break_title'] = trim($_POST['break_title'] ?? '☕ REFRESHMENT');
            $doc['break_venue'] = trim($_POST['break_venue'] ?? 'KAM Hall Outside');
            $doc['icon'] = trim($_POST['break_icon'] ?? 'fa-solid fa-mug-hot');
            $doc['events'] = [];
        } else {
            $event_title = trim($_POST['event_title'] ?? '');
            $event_venue = trim($_POST['event_venue'] ?? '📍 KAM Hall');
            $event_badge = trim($_POST['event_badge'] ?? 'PRELIMS');
            $event_badge_type = trim($_POST['event_badge_type'] ?? 'prelim');
            $event_icon = trim($_POST['event_icon'] ?? 'fa-solid fa-clock');

            // Ensure venue has pin emoji prefix if missing
            if (!empty($event_venue) && strpos($event_venue, '📍') === false) {
                $event_venue = '📍 ' . $event_venue;
            }

            $doc['events'] = [
                [
                    'title' => $event_title,
                    'badge' => $event_badge,
                    'badge_type' => $event_badge_type,
                    'venue' => $event_venue,
                    'icon' => $event_icon
                ]
            ];

            // Handle optional second parallel event in same time slot
            if (!empty($_POST['event_title2'])) {
                $event_title2 = trim($_POST['event_title2']);
                $event_venue2 = trim($_POST['event_venue2'] ?? '📍 KMR Auditorium');
                $event_badge2 = trim($_POST['event_badge2'] ?? 'PRELIMS');
                $event_badge_type2 = trim($_POST['event_badge_type2'] ?? 'prelim');
                $event_icon2 = trim($_POST['event_icon2'] ?? 'fa-solid fa-code');

                if (!empty($event_venue2) && strpos($event_venue2, '📍') === false) {
                    $event_venue2 = '📍 ' . $event_venue2;
                }

                $doc['events'][] = [
                    'title' => $event_title2,
                    'badge' => $event_badge2,
                    'badge_type' => $event_badge_type2,
                    'venue' => $event_venue2,
                    'icon' => $event_icon2
                ];
            }
        }

        if ($action === 'add') {
            $db->timeline->insertOne($doc);
            $message = "New timeline slot added successfully!";
            $message_type = "success";
        } elseif ($action === 'edit' && !empty($_POST['slot_id'])) {
            try {
                $objectId = new MongoDB\BSON\ObjectId($_POST['slot_id']);
                $db->timeline->updateOne(['_id' => $objectId], ['$set' => $doc]);
                $message = "Timeline slot updated successfully!";
                $message_type = "success";
            } catch (Exception $e) {
                $message = "Error updating timeline slot: " . $e->getMessage();
                $message_type = "error";
            }
        }
    } elseif ($action === 'delete' && !empty($_POST['slot_id'])) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($_POST['slot_id']);
            $db->timeline->deleteOne(['_id' => $objectId]);
            $message = "Timeline slot deleted successfully!";
            $message_type = "success";
        } catch (Exception $e) {
            $message = "Invalid timeline ID.";
            $message_type = "error";
        }
    }
}

// Fetch all timeline items sorted by order
$timelineSlots = [];
try {
    $cursor = $db->timeline->find([], ['sort' => ['order' => 1]]);
    foreach ($cursor as $doc) {
        $timelineSlots[] = [
            'id' => (string)$doc['_id'],
            'order' => (int)($doc['order'] ?? 1),
            'time_slot' => $doc['time_slot'] ?? '',
            'is_break' => !empty($doc['is_break']),
            'break_title' => $doc['break_title'] ?? '',
            'break_venue' => $doc['break_venue'] ?? '',
            'icon' => $doc['icon'] ?? 'fa-solid fa-clock',
            'events' => isset($doc['events']) ? (array)$doc['events'] : []
        ];
    }
} catch (Exception $e) {
    $timelineSlots = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Manager | QUTRIX Admin</title>
    
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        /* --- TIMELINE LIST DISPLAY --- */
        .timeline-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .timeline-item-card {
            background: rgba(2, 6, 23, 0.8);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
        }

        .timeline-item-card:hover {
            border-color: var(--accent);
            transform: translateX(4px);
        }

        .slot-time-badge {
            background: linear-gradient(90deg, var(--accent), #f59e0b);
            color: #000;
            font-weight: 800;
            padding: 8px 18px;
            border-radius: 100px;
            font-size: 0.85rem;
            font-family: 'JetBrains Mono', monospace;
            white-space: nowrap;
        }

        .slot-content {
            flex-grow: 1;
        }

        .event-subitem {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 6px;
        }

        .event-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--white);
        }

        .badge-prelim { background: #2563eb; color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }
        .badge-final { background: #16a34a; color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }
        .badge-special { background: #9333ea; color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }
        .badge-break { background: #d97706; color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }

        .slot-venue {
            font-size: 0.85rem;
            color: var(--text-dim);
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
            .timeline-item-card { flex-direction: column; align-items: flex-start; }
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
            <a href="timeline_manager.php" class="active"><i class="fas fa-calendar-alt"></i> Timeline</a>
            <a href="send_mail.php"><i class="fas fa-envelope"></i> Mailer</a>
            <a href="feedback_display.php"><i class="fas fa-comment-dots"></i> Feedback</a>
        </div>
    </nav> -->

    <div class="container">

        <div class="page-header">
            <h1>Timeline <span>Manager</span></h1>
            <p style="color: var(--text-dim);">Alter event schedule times, venues, prelims/finals, and breaks</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?>">
                <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- ADD TIMELINE FORM CARD -->
        <div class="card-box">
            <h2><i class="fas fa-plus-circle"></i> Add New Timeline Schedule Slot</h2>
            <form action="timeline_manager.php" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Time Slot (e.g. 09:00 AM - 09:10 AM)</label>
                        <input type="text" name="time_slot" class="form-control" placeholder="09:00 AM - 09:10 AM" required>
                    </div>

                    <div class="form-group">
                        <label>Sequence Order Position (1, 2, 3...)</label>
                        <input type="number" name="order" class="form-control" value="<?= count($timelineSlots) + 1 ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Slot Type</label>
                        <select name="is_break" id="slotTypeSelect" class="form-control" onchange="toggleSlotType()">
                            <option value="0">Event Slot</option>
                            <option value="1">Break / Refreshment Slot</option>
                        </select>
                    </div>
                </div>

                <!-- EVENT SLOT FIELDS -->
                <div id="eventFields" style="margin-top: 20px;">
                    <h3 style="color: var(--skyblu); font-size: 1rem; margin-bottom: 15px;"><i class="fas fa-trophy"></i> Main Event Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Event Name (e.g. Paper Presentation)</label>
                            <input type="text" name="event_title" class="form-control" placeholder="e.g. Paper Presentation">
                        </div>

                        <div class="form-group">
                            <label>Venue (e.g. KAM Hall)</label>
                            <input type="text" name="event_venue" class="form-control" placeholder="📍 KAM Hall">
                        </div>

                        <div class="form-group">
                            <label>Badge Label</label>
                            <select name="event_badge" class="form-control">
                                <option value="PRELIMS">PRELIMS</option>
                                <option value="FINALS">FINALS</option>
                                <option value="OPENING">OPENING</option>
                                <option value="CLOSING CEREMONY">CLOSING CEREMONY</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Badge Type / Color</label>
                            <select name="event_badge_type" class="form-control">
                                <option value="prelim">Prelim (Blue)</option>
                                <option value="final">Final (Green)</option>
                                <option value="special">Special (Purple)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Event Icon</label>
                            <select name="event_icon" class="form-control">
                                <option value="fa-solid fa-file-lines">Paper Presentation (Doc Icon)</option>
                                <option value="fa-solid fa-circle-question">Quiz (Question Icon)</option>
                                <option value="fa-solid fa-code">Web Design (Code Icon)</option>
                                <option value="fa-solid fa-music">Dance (Music Icon)</option>
                                <option value="fa-solid fa-bullhorn">Marketing (Speaker Icon)</option>
                                <option value="fa-solid fa-laptop-code">Software Contest (Laptop Icon)</option>
                                <option value="fa-solid fa-font">Word Hunt (Font Icon)</option>
                                <option value="fa-solid fa-trophy">Valedictory (Trophy Icon)</option>
                                <option value="fa-solid fa-flag-checkered">Opening (Flag Icon)</option>
                            </select>
                        </div>
                    </div>

                    <!-- OPTIONAL PARALLEL SECOND EVENT -->
                    <h3 style="color: var(--text-dim); font-size: 0.9rem; margin-top: 25px; margin-bottom: 15px;"><i class="fas fa-layer-group"></i> Optional Parallel Second Event (Same Time Slot)</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>2nd Event Name (Optional)</label>
                            <input type="text" name="event_title2" class="form-control" placeholder="Leave empty if only 1 event">
                        </div>

                        <div class="form-group">
                            <label>2nd Event Venue</label>
                            <input type="text" name="event_venue2" class="form-control" placeholder="📍 KMR Auditorium">
                        </div>

                        <div class="form-group">
                            <label>2nd Event Badge</label>
                            <select name="event_badge2" class="form-control">
                                <option value="PRELIMS">PRELIMS</option>
                                <option value="FINALS">FINALS</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>2nd Event Icon</label>
                            <select name="event_icon2" class="form-control">
                                <option value="fa-solid fa-code">Web Design (Code Icon)</option>
                                <option value="fa-solid fa-font">Word Hunt (Font Icon)</option>
                                <option value="fa-solid fa-laptop-code">Software Contest (Laptop Icon)</option>
                                <option value="fa-solid fa-circle-question">Quiz (Question Icon)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- BREAK SLOT FIELDS -->
                <div id="breakFields" style="display: none; margin-top: 20px;">
                    <h3 style="color: var(--accent); font-size: 1rem; margin-bottom: 15px;"><i class="fas fa-mug-hot"></i> Break Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Break Title (e.g. ☕ REFRESHMENT or 🍽 LUNCH)</label>
                            <input type="text" name="break_title" class="form-control" placeholder="☕ REFRESHMENT">
                        </div>

                        <div class="form-group">
                            <label>Break Location</label>
                            <input type="text" name="break_venue" class="form-control" placeholder="KAM Hall Outside">
                        </div>

                        <div class="form-group">
                            <label>Break Icon</label>
                            <select name="break_icon" class="form-control">
                                <option value="fa-solid fa-mug-hot">Tea / Coffee (Mug Icon)</option>
                                <option value="fa-solid fa-utensils">Lunch (Utensils Icon)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-plus-circle"></i> Add Slot To Schedule
                </button>
            </form>
        </div>

        <!-- EXISTING TIMELINE ITEMS CARD -->
        <div class="card-box">
            <h2><i class="fas fa-list-ol"></i> Current Timeline Schedule (<?= count($timelineSlots) ?> Slots)</h2>

            <div class="timeline-list">
                <?php foreach ($timelineSlots as $slot): ?>
                    <div class="timeline-item-card">
                        <div class="slot-time-badge">
                            <i class="fas fa-clock"></i> <?= htmlspecialchars($slot['time_slot']) ?>
                        </div>

                        <div class="slot-content">
                            <?php if ($slot['is_break']): ?>
                                <div class="event-subitem">
                                    <span class="event-title" style="color: var(--accent);"><?= htmlspecialchars($slot['break_title']) ?></span>
                                    <span class="badge badge-break">BREAK</span>
                                </div>
                                <div class="slot-venue">📍 <?= htmlspecialchars($slot['break_venue']) ?></div>
                            <?php else: ?>
                                <?php foreach ($slot['events'] as $ev): ?>
                                    <div class="event-subitem">
                                        <i class="<?= htmlspecialchars($ev['icon'] ?? 'fa-solid fa-star') ?>" style="color: var(--accent);"></i>
                                        <span class="event-title"><?= htmlspecialchars($ev['title']) ?></span>
                                        <span class="badge badge-<?= htmlspecialchars($ev['badge_type'] ?? 'prelim') ?>"><?= htmlspecialchars($ev['badge'] ?? 'PRELIMS') ?></span>
                                        <span class="slot-venue"><?= htmlspecialchars($ev['venue'] ?? '') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <form action="timeline_manager.php" method="POST" onsubmit="return confirm('Delete this timeline slot?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="slot_id" value="<?= htmlspecialchars($slot['id']) ?>">
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
        function toggleSlotType() {
            const select = document.getElementById('slotTypeSelect');
            const eventFields = document.getElementById('eventFields');
            const breakFields = document.getElementById('breakFields');

            if (select.value === '1') {
                eventFields.style.display = 'none';
                breakFields.style.display = 'block';
            } else {
                eventFields.style.display = 'block';
                breakFields.style.display = 'none';
            }
        }
    </script>
</body>
</html>
