<?php
// Reporte de errores para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS Headers - Manejo de pre-flight (OPTIONS) para navegadores modernos
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json; charset=UTF-8");

$host = '162.216.5.50';
$db   = 'directac_RNM_2025';
$user = 'directac_fco';
$pass = '**xmiswebs**';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión: ' . $e->getMessage()
    ]);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'login':
            // Leer el cuerpo de la petición (POST)
            $raw_data = file_get_contents('php://input');
            $data = json_decode($raw_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['success' => false, 'error' => 'JSON inválido enviado al servidor']);
                break;
            }

            $usuario = $data['username'] ?? '';
            $password = $data['password'] ?? '';
            
            if (empty($usuario) || empty($password)) {
                echo json_encode(['success' => false, 'error' => 'Usuario y contraseña son obligatorios']);
                break;
            }

            $stmt = $pdo->prepare("SELECT usuario FROM usuarios WHERE usuario = ? AND password = ?");
            $stmt->execute([$usuario, $password]);
            $userRecord = $stmt->fetch();
            
            if ($userRecord) {
                echo json_encode(['success' => true, 'user' => $userRecord['usuario']]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Usuario o contraseña incorrectos']);
            }
            break;

        case 'get_months':
            $stmt = $pdo->query("SELECT mesid, mes FROM mes ORDER BY mesid ASC");
            echo json_encode($stmt->fetchAll());
            break;

        case 'get_days':
            $stmt = $pdo->query("SELECT dia FROM dias ORDER BY dia ASC");
            echo json_encode($stmt->fetchAll());
            break;

        case 'search_miembros':
            $mes = $_GET['mes'] ?? '';
            $dia = $_GET['dia'] ?? '';
            
            if (!$mes || !$dia) {
                echo json_encode(['success' => false, 'error' => 'Mes y día requeridos']);
                break;
            }
            
            $stmt = $pdo->prepare("SELECT nombre_completo, dia, celular, email FROM miembros WHERE mes = ? AND dia = ? ORDER BY nombre_completo ASC");
            $stmt->execute([$mes, $dia]);
            echo json_encode($stmt->fetchAll());
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida: ' . $action]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error: ' . $e->getMessage()]);
}
?>

