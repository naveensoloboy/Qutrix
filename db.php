<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use MongoDB\Client;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!isset($_ENV['MONGO_URI'])) {
    die("❌ MONGO_URI missing in .env");
}

$client = new Client($_ENV['MONGO_URI']);
$db = $client->Qutrix;
