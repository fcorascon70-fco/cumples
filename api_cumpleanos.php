<?php
session_start();
require_once 'config.php';
sendSecurityHeaders();
header('Access-Control-Allow-Origin: *'); // Mantiene compatibilidad si es necesario

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['error' => 'No autorizado. Inicia sesión.']);
    exit;
}

$pdo = getDatabaseConnection();
if (!$pdo) {
    echo json_encode(['error' => 'Connection failed']);
    exit;
}


$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : null;
$dia = isset($_GET['dia']) ? (int)$_GET['dia'] : null;

$query = "SELECT nombre_completo, dia, celular, email FROM miembros WHERE 1=1";
$params = [];

if ($mes) {
    $query .= " AND mes = :mes";
    $params['mes'] = $mes;
}

if ($dia) {
    $query .= " AND dia = :dia";
    $params['dia'] = $dia;
}

// if neither month nor day is selected, maybe we shouldn't return the whole DB
// We require at least one filter to avoid overloading the browser
if (!$mes && !$dia) {
     echo json_encode(['results' => [], 'message' => 'Por favor selecciona un mes o un día.']);
     exit;
}

$query .= " ORDER BY dia ASC, nombre_completo ASC LIMIT 500"; // Added limit for safety

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    echo json_encode(['success' => true, 'results' => $results]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Query failed: ' . $e->getMessage()]);
}
