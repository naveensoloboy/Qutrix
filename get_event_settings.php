<?php
header('Content-Type: application/json');
include __DIR__ . '/db.php';

$defaults = [
    'title' => 'QUTRIX 2K26',
    'subtitle' => 'An Intercollegiate Technical Symposium',
    'event_date' => '2026-09-18 09:00:00',
    'poster_image' => 'images/poster_2k26.jpeg',
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

try {
    $doc = $db->settings->findOne(['_id' => 'event_config']);
    if ($doc) {
        $data = (array)$doc;
        unset($data['_id']);
        
        // Merge with defaults
        $settings = array_merge($defaults, $data);
        if (isset($data['support_network'])) {
            $settings['support_network'] = array_merge($defaults['support_network'], (array)$data['support_network']);
        }
        echo json_encode(['status' => 'success', 'data' => $settings]);
    } else {
        echo json_encode(['status' => 'success', 'data' => $defaults]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => $defaults]);
}
