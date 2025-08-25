<?php
require 'vendor/autoload.php'; // load composer autoload

// Your MongoDB Atlas URI (replace <username>, <password>, <cluster> properly)
$uri = "mongodb+srv://admin:qutrixpass2025@cluster1.duscp.mongodb.net/?retryWrites=true&w=majority&appName=Cluster1";

try {
    $client = new MongoDB\Client($uri);
    $db = $client->Qutrix; // Database name
    // echo "✅ Connected to MongoDB!";
} catch (Exception $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
