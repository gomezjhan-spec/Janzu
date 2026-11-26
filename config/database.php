<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}

$settings = [
    'host' => getenv('JANZU_DB_HOST') ?: '127.0.0.1',
    'name' => getenv('JANZU_DB_NAME') ?: 'janzu',
    'user' => getenv('JANZU_DB_USER') ?: 'root',
    'pass' => getenv('JANZU_DB_PASS') !== false ? getenv('JANZU_DB_PASS') : '',
];

$localSettingsFile = __DIR__ . '/database.local.php';
if (is_file($localSettingsFile)) {
    $localSettings = require $localSettingsFile;
    if (is_array($localSettings)) {
        $settings = array_replace($settings, $localSettings);
    }
}

function db(): PDO
{
    static $pdo = null;
    global $settings;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $dsn = 'mysql:host=' . $settings['host'] . ';dbname=' . $settings['name'] . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $settings['user'], $settings['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
        return $pdo;
    } catch (PDOException $exception) {
        error_log('[JANZU] Database connection failed: ' . $exception->getMessage());
        http_response_code(500);
        exit('No fue posible conectar con MySQL. Revisa la configuración local y que el servicio esté activo en Laragon.');
    }
}
