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

// Never show PHP errors (file paths, SQL) to visitors unless APP_DEBUG=true; they still go to the error log.
$appDebug = strtolower((string) ($_ENV['APP_DEBUG'] ?? 'false')) === 'true';
ini_set('display_errors', $appDebug ? '1' : '0');
ini_set('log_errors', '1');

// Keep PHP and MySQL on the clinic's clock so check-in/out timestamps match on any server.
$appTimezone = $_ENV['APP_TIMEZONE'] ?? 'Asia/Manila';
if (!in_array($appTimezone, timezone_identifiers_list(), true)) {
    $appTimezone = 'Asia/Manila';
}
date_default_timezone_set($appTimezone);

// Get MySQL credentials from .env
$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_DATABASE'];
$dbUser = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$port = $_ENV['DB_PORT'];

// Create PDO connection
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdo->exec("SET time_zone = '" . (new DateTimeImmutable('now'))->format('P') . "'");
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('Unable to connect to the database. Please try again later.');
}
