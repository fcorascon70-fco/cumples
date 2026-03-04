<?php
// Configuración de la base de datos
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivado para producción

// Gestor de errores para devolver JSON en lugar de romper la respuesta
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => "PHP Error [$errno]: $errstr in $errfile on line $errline"]);
    exit;
});

set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => "Exception: " . $e->getMessage()]);
    exit;
});
define('DB_HOST', '162.216.5.50');
define('DB_NAME', 'directac_padron');
define('DB_USER', 'directac_fco');
define('DB_PASS', '**xmiswebs**');
define('DB_CHARSET', 'utf8mb4');

/**
 * Función para obtener una conexión PDO configurada de forma segura
 */
function getDatabaseConnection() {
    // Intenta leer de variables de entorno si están disponibles
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
        // En producción, no mostrar el mensaje de error detallado al usuario
        error_log('Error de conexión BD: ' . $e->getMessage());
        return null;
    }
}

/**
 * Encabezados de seguridad comunes
 */
function sendSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Content-Type: application/json');
}
