<?php
// config.php - Veritabanı bağlantısı ve yardımcı fonksiyonlar

define('DB_HOST',    '212.64.193.200');
define('DB_USER',    'agnowzbg_logis');
define('DB_PASS',    'Kayra2190');
define('DB_NAME',    'agnowzbg_logi');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

function jsonResponse(bool $success, $data = null, string $message = ''): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'data'    => $data,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function input(string $key, string $default = ''): string {
    return trim($_REQUEST[$key] ?? $default);
}

function intInput(string $key, int $default = 0): int {
    return (int) ($_REQUEST[$key] ?? $default);
}
