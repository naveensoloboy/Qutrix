<?php
require __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

// Load dotenv ONLY if .env exists (local dev)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Get env vars (works for local + Render)
$mongoUri = $_ENV['MONGO_URI'] ?? getenv('MONGO_URI');

if (!$mongoUri) {
    die('❌ MONGO_URI not configured');
}

$client = new Client($mongoUri);
$db = $client->Qutrix;
