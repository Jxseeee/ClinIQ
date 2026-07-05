<?php
require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env from project root
$dotenvPath = dirname(__DIR__, 2);
if (!is_readable($dotenvPath . '/.env')) {
    error_log('.env not found or not readable in project root: ' . $dotenvPath . '/.env');
    http_response_code(500);
    die('Server configuration error. Please contact the administrator.');
}
$dotenv = Dotenv::createImmutable($dotenvPath);
$dotenv->load();

// Get MySQL credentials from .env
$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$port = $_ENV['DB_PORT'];

// Create PDO connection
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('Unable to connect to the database. Please try again later.');
}
