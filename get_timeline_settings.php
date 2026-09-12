<?php
header('Content-Type: application/json');
include __DIR__ . '/db.php';

$defaultTimelineItems = [
    [
        'order' => 1,
        'time_slot' => '09:00 AM - 09:10 AM',
        'is_break' => false,
        'events' => [
            ['title' => 'Inauguration', 'badge' => 'OPENING', 'badge_type' => 'special', 'venue' => '📍 KAM Hall', 'icon' => 'fa-solid fa-flag-checkered']
        ]
    ],
    [
        'order' => 2,
        'time_slot' => '09:15 AM - 10:15 AM',
        'is_break' => false,
        'events' => [
            ['title' => 'Paper Presentation', 'badge' => 'FINALS', 'badge_type' => 'final', 'venue' => '📍 KAM Hall', 'icon' => 'fa-solid fa-file-lines']
        ]
    ],
    [
        'order' => 3,
        'time_slot' => '10:20 AM - 10:50 AM',
        'is_break' => false,
        'events' => [
            ['title' => 'Quiz', 'badge' => 'PRELIMS', 'badge_type' => 'prelim', 'venue' => '📍 KMR Auditorium', 'icon' => 'fa-solid fa-circle-question'],
            ['title' => 'Web Design', 'badge' => 'PRELIMS', 'badge_type' => 'prelim', 'venue' => '📍 KMR Auditorium', 'icon' => 'fa-solid fa-code']
        ]
    ],
    [
        'order' => 4,
        'time_slot' => '10:50 AM - 11:10 AM',
        'is_break' => true,
        'break_title' => '☕ REFRESHMENT',
        'break_venue' => 'KAM Hall Outside',
        'icon' => 'fa-solid fa-mug-hot'
    ],
    [
        'order' => 5,
        'time_slot' => '11:10 AM - 12:15 PM',
        'is_break' => false,
        'events' => [
            ['title' => 'Dance', 'badge' => 'FINALS', 'badge_type' => 'final', 'venue' => '📍 KAM Hall', 'icon' => 'fa-solid fa-music']
        ]
    ],
    [
        'order' => 6,
        'time_slot' => '11:15 AM - 11:45 AM',
        'is_break' => false,
        'events' => [
            ['title' => 'Marketing', 'badge' => 'PRELIMS', 'badge_type' => 'prelim', 'venue' => '📍 KMR Auditorium', 'icon' => 'fa-solid fa-bullhorn']
        ]
    ],
    [
        'order' => 7,
        'time_slot' => '11:45 AM - 12:15 PM',
        'is_break' => false,
        'events' => [
            ['title' => 'Software Contest', 'badge' => 'PRELIMS', 'badge_type' => 'prelim', 'venue' => '📍 Lab-II', 'icon' => 'fa-solid fa-laptop-code'],
            ['title' => 'Word Hunt', 'badge' => 'PRELIMS', 'badge_type' => 'prelim', 'venue' => '📍 KMR Auditorium', 'icon' => 'fa-solid fa-font']
        ]
    ],
    [
        'order' => 8,
        'time_slot' => '12:15 PM - 01:00 PM',
        'is_break' => true,
        'break_title' => '🍽 LUNCH',
        'break_venue' => 'KMR Auditorium',
        'icon' => 'fa-solid fa-utensils'
    ],
    [
        'order' => 9,
        'time_slot' => '01:00 PM - 02:00 PM',
        'is_break' => false,
        'events' => [
            ['title' => 'Quiz', 'badge' => 'FINALS', 'badge_type' => 'final', 'venue' => '📍 KMR Auditorium', 'icon' => 'fa-solid fa-circle-question'],
            ['title' => 'Web Design', 'badge' => 'FINALS', 'badge_type' => 'final', 'venue' => '📍 Lab-II', 'icon' => 'fa-solid fa-code']
        ]
    ],
    [
        'order' => 10,
        'time_slot' => '02:00 PM - 03:00 PM',
        'is_break' => false,
        'events' => [
            ['title' => 'Word Hunt', 'badge' => 'FINALS', 'badge_type' => 'final', 'venue' => '📍 KAM Hall', 'icon' => 'fa-solid fa-font'],
            ['title' => 'Software Contest', 'badge' => 'FINALS', 'badge_type' => 'final', 'venue' => '📍 Lab-II', 'icon' => 'fa-solid fa-laptop-code']
        ]
    ],
    [
        'order' => 11,
        'time_slot' => '03:00 PM - 04:00 PM',
        'is_break' => false,
        'events' => [
            ['title' => 'Marketing', 'badge' => 'FINALS', 'badge_type' => 'final', 'venue' => '📍 KAM Hall', 'icon' => 'fa-solid fa-bullhorn']
        ]
    ],
    [
        'order' => 12,
        'time_slot' => '04:15 PM - 05:00 PM',
        'is_break' => false,
        'events' => [
            ['title' => 'Valedictory Function', 'badge' => 'CLOSING CEREMONY', 'badge_type' => 'special', 'venue' => '📍 KAM Hall', 'icon' => 'fa-solid fa-trophy']
        ]
    ]
];

try {
    $count = $db->timeline->countDocuments();
    
    // Auto-seed if collection is empty
    if ($count === 0) {
        foreach ($defaultTimelineItems as $item) {
            $item['created_at'] = date('Y-m-d H:i:s');
            $db->timeline->insertOne($item);
        }
    }

    $cursor = $db->timeline->find([], ['sort' => ['order' => 1]]);
    $items = [];

    foreach ($cursor as $doc) {
        $items[] = [
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

    echo json_encode([
        'status' => 'success',
        'data' => $items
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'data' => $defaultTimelineItems
    ]);
}
