<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';
sendSecurityHeaders();

$pdo = getDatabaseConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $usuario = $input['usuario'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($usuario) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, completa todos los campos.']);
        exit;
    }

    try {
        // La contraseña en varchar(20) indica que puede no estar hasheada.
        // Verificamos si existe el usuario primero.
        $stmt = $pdo->prepare('SELECT id, usuario, password FROM usuarios WHERE usuario = :usuario LIMIT 1');
        $stmt->execute(['usuario' => $usuario]);
        $user = $stmt->fetch();

        if ($user) {
            // Evaluamos la contraseña. Se asume texto plano debido a longitud varchar(20).
            // Si estuviere en MD5 (no cabe), o SHA (no cabe).
            if ($password === $user['password']) {
                $_SESSION['logged_in'] = true;
                $_SESSION['usuario'] = $user['usuario'];
                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'El usuario no existe.']);
            exit;
        }

    } catch (\PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al consultar usuarios.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
