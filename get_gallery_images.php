<?php
header('Content-Type: application/json');
include __DIR__ . '/db.php';

$defaultGalleryItems = [
    // --- GAIT COORDINATORS SECTION ---
    ['year' => '', 'src' => 'images/gallery/coordinator/ps_sir.png', 'title' => 'Gait Coordinator', 'meta' => 'EXECUTIVE', 'isCoordinator' => true],
    ['year' => '', 'src' => 'images/gallery/coordinator/DSC_9209.JPG', 'title' => 'Gait Coordinator', 'meta' => 'EXECUTIVE', 'isCoordinator' => true],
    ['year' => '', 'src' => 'images/gallery/coordinator/DSC_8288.JPG', 'title' => 'Gait Coordinator', 'meta' => 'EXECUTIVE', 'isCoordinator' => true],
    ['year' => '', 'src' => 'images/gallery/coordinator/DSC_8289.JPG', 'title' => 'Gait Coordinator', 'meta' => 'EXECUTIVE', 'isCoordinator' => true],
    ['year' => '', 'src' => 'images/gallery/coordinator/DSC_7800.JPG', 'title' => 'Gait Coordinator', 'meta' => 'EXECUTIVE', 'isCoordinator' => true],

    // --- 2025 SECTION ---
    ['year' => 2025, 'src' => 'images/gallery/2025/DSC_8864.JPG', 'title' => 'Ceremonial Lighting', 'meta' => 'EVOLUTION', 'isCoordinator' => false],
    ['year' => 2025, 'src' => 'images/gallery/2025/DSC_8896.JPG', 'title' => 'Quiz', 'meta' => 'EVOLUTION', 'isCoordinator' => false],
    ['year' => 2025, 'src' => 'images/gallery/2025/DSC_7947.JPG', 'title' => 'Software Contest Prelims', 'meta' => 'EVOLUTION', 'isCoordinator' => false],
    ['year' => 2025, 'src' => 'images/gallery/2025/DSC_7964.JPG', 'title' => '', 'meta' => 'EVOLUTION', 'isCoordinator' => false],

    // --- 2024 SECTION ---
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_7885.JPG', 'title' => 'Onspot Registration', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_7805.JPG', 'title' => 'Inaugural Ceremony', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_7811.JPG', 'title' => 'Ceremonial Lighting', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_7786.JPG', 'title' => 'Grand Inaugural', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_7823.JPG', 'title' => 'Paper Presentation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8077.JPG', 'title' => 'Quiz', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8070.JPG', 'title' => 'Web Design', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8104.JPG', 'title' => 'Web Design Judge', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8119.JPG', 'title' => 'Software Contest', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8199.JPG', 'title' => 'Software Contest Judge', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8165.JPG', 'title' => 'Dance', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8194.JPG', 'title' => 'Marketing', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8242.JPG', 'title' => 'Marketing Judge', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8262.JPG', 'title' => 'Insightful Observation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8310.JPG', 'title' => 'Insightful Observation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8291.JPG', 'title' => 'Valedictory Function', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/DSC_8324.JPG', 'title' => 'Chief Guest Momentum', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/momento/principal-momentum.JPG', 'title' => 'Principal Felicitation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/momento/DSC_8381.JPG', 'title' => 'Faculty Felicitation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/momento/DSC_8383.JPG', 'title' => 'Faculty Felicitation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/momento/DSC_8397.JPG', 'title' => 'Faculty Felicitation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/momento/DSC_8384.JPG', 'title' => 'Faculty Felicitation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/momento/DSC_8382.JPG', 'title' => 'Faculty Felicitation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/momento/DSC_8386.JPG', 'title' => 'Faculty Felicitation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/momento/DSC_8387.JPG', 'title' => 'Faculty Felicitation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    ['year' => 2024, 'src' => 'images/gallery/2024/momento/DSC_8388.JPG', 'title' => 'Faculty Felicitation', 'meta' => 'LEGACY', 'isCoordinator' => false],
    
    // --- 2023 SECTION ---
    ['year' => 2023, 'src' => 'images/gallery/2023.jpg', 'title' => '', 'meta' => 'LEGACY', 'isCoordinator' => false],

    // --- 2018 SECTION ---
    ['year' => 2018, 'src' => 'images/gallery/2018.jpg', 'title' => '', 'meta' => 'LEGACY', 'isCoordinator' => false]
];

try {
    $count = $db->gallery->countDocuments();
    
    // Seed default items if collection is completely empty
    if ($count === 0) {
        foreach ($defaultGalleryItems as $item) {
            $item['created_at'] = date('Y-m-d H:i:s');
            $db->gallery->insertOne($item);
        }
    }
    
    $cursor = $db->gallery->find([], ['sort' => ['created_at' => -1]]);
    $items = [];
    
    foreach ($cursor as $doc) {
        $items[] = [
            'id' => (string)$doc['_id'],
            'year' => isset($doc['year']) ? (is_numeric($doc['year']) ? (int)$doc['year'] : (string)$doc['year']) : '',
            'src' => $doc['src'] ?? '',
            'title' => $doc['title'] ?? '',
            'meta' => $doc['meta'] ?? 'LEGACY',
            'isCoordinator' => !empty($doc['isCoordinator'])
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
        'data' => $defaultGalleryItems
    ]);
}
