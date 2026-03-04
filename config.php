<?php
// Configuración de la base de datos
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Evitar que errores fatales devuelvan una página en blanco
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR)) {
        if (!headers_sent()) header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Fatal Error: ' . $error['message'], 'file' => $error['file'], 'line' => $error['line']]);
    }
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    if (!headers_sent()) header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => "PHP Error: $errstr", 'file' => $errfile, 'line' => $errline]);
    exit;
});

set_exception_handler(function($e) {
    if (!headers_sent()) header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => "Exception: " . $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
    exit;
});

define('DB_HOST', '162.216.5.50');
define('DB_NAME', 'directac_padron');
define('DB_USER', 'directac_fco');
define('DB_PASS', '**xmiswebs**');
define('DB_CHARSET', 'utf8mb4');

function getDatabaseConnection() {
    $host = getenv('DB_HOST') ?: DB_HOST;
    $db   = getenv('DB_NAME') ?: DB_NAME;
    $user = getenv('DB_USER') ?: DB_USER;
    $pass = getenv('DB_PASS') ?: DB_PASS;
    $charset = getenv('DB_CHARSET') ?: DB_CHARSET;

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        error_log('Error de conexión BD: ' . $e->getMessage());
        return null;
    }
}

function sendSecurityHeaders() {
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Content-Type: application/json');
    }
}
