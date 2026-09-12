<?php
header('Content-Type: application/json');
include __DIR__ . '/db.php';

$defaultColleges = [
    "Erode Sengunthar Engineering College",
    "Kongu Engineering College",
    "Velalar College of Engineering and Technology",
    "Nandha Engineering College",
    "Nandha College of Technology",
    "Gobi Arts & Science College",
    "Government Arts College, Coimbatore",
    "PSG College of Technology",
    "PSG College of Arts and Science",
    "Coimbatore Institute of Technology",
    "Sri Ramakrishna Engineering College",
    "Sri Ramakrishna College of Arts and Science",
    "Sri Krishna College of Engineering and Technology",
    "Sri Krishna Arts and Science College",
    "Dr. N.G.P. Arts and Science College",
    "Dr. N.G.P. Institute of Technology",
    "KPR Institute of Engineering and Technology",
    "KPR College of Arts, Science and Research",
    "Hindusthan College of Arts and Science",
    "Hindusthan College of Engineering and Technology",
    "Hindusthan Institute of Technology",
    "RVS College of Arts and Science",
    "RVS College of Engineering and Technology",
    "VET Institute of Arts and Science",
    "Sri Vasavi College (Autonomous)",
    "P.K.R. Arts College for Women",
    "Kaamadhenu Arts and Science College",
    "Vellalar College for Women",
    "Al-Ameen Engineering College",
    "Kongu Arts and Science College (Autonomous)",
    "J.K.K. Nattraja College of Arts and Science",
    "J.K.K. Nattraja Educational Institutions",
    "Mahendra Engineering College",
    "Mahendra Arts and Science College",
    "Vivekanandha College of Arts and Sciences for Women (Autonomous)",
    "Vivekanandha College of Engineering for Women",
    "K.S. Rangasamy College of Arts and Science (Autonomous)",
    "K.S.R. College of Engineering",
    "KSR Institute for Engineering and Technology",
    "Selvam College of Technology",
    "Muthayammal College of Arts and Science (Autonomous)",
    "Kongunadu College of Engineering and Technology (Autonomous)",
    "Kongunadu Arts and Science College",
    "Nirmala College for Women (Autonomous)",
    "St. Joseph's College of Arts and Science (Autonomous)",
    "Nehru Arts and Science College (Autonomous)",
    "Rathinam College of Arts and Science (Autonomous)",
    "CMS College of Science and Commerce (Autonomous)",
    "Sri Ramakrishna Mission Vidyalaya College of Arts and Science",
    "Sankara College of Science and Commerce",
    "Chikkanna Government Arts College, Tiruppur",
    "Nehru Institute of Information Technology and Management",
    "Sri Ramakrishna Institute of Technology",
    "SNMV College of Arts and Science (Autonomous)",
    "Sree Saraswathi Thyagaraja College (Autonomous)",
    "Park College (Autonomous)",
    "SNS College of Technology",
    "Shree Venkateshwara Arts and Science (Co-Education) College",
    "Ayyan Thiruvalluvar College of Arts and Science",
    "Excel College for Commerce and Science",
    "Excel Engineering College (Autonomous)",
    "Government Arts and Science College, Thittamalai",
    "Dr. GRD College of Arts and Science",
    "PSGR Krishnammal College for Women",
    "Avinashi Government Arts and Science College",
    "Sree Amman Arts and Science College, Erode",
    "Arulmigu Arthanareeswarar Arts and Science College"
];

try {
    $count = $db->colleges->countDocuments();
    
    // Auto-seed default colleges if collection is empty
    if ($count === 0) {
        foreach ($defaultColleges as $collegeName) {
            $db->colleges->insertOne([
                'name' => trim($collegeName),
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ]);
        }
    }

    $cursor = $db->colleges->find([], ['sort' => ['name' => 1]]);
    $colleges = [];

    foreach ($cursor as $doc) {
        if (!empty($doc['name'])) {
            $colleges[] = (string)$doc['name'];
        }
    }

    // Ensure array unique and sorted
    $colleges = array_values(array_unique($colleges));
    sort($colleges);

    echo json_encode([
        'status' => 'success',
        'data' => $colleges
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'data' => $defaultColleges
    ]);
}
