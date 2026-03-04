<?php
ob_start();
session_start();

require_once 'config.php';
sendSecurityHeaders();

$pdo = getDatabaseConnection();
if (!$pdo) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos. Verifique los logs.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $usuario = $input['usuario'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($usuario) || empty($password)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Por favor, completa todos los campos.']);
        exit;
    }

    try {
        // Buscamos por la columna 'username'
        $stmt = $pdo->prepare('SELECT id, username, password FROM usuarios WHERE username = :usuario LIMIT 1');
        $stmt->execute(['usuario' => $usuario]);
        $user = $stmt->fetch();

        if ($user) {
            if ($password === $user['password']) {
                $_SESSION['logged_in'] = true;
                $_SESSION['usuario'] = $user['username'];
                ob_clean();
                echo json_encode(['success' => true]);
                exit;
            } else {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
                exit;
            }
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'El usuario no existe.']);
            exit;
        }

    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $e->getMessage()]);
        exit;
    }
} else {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
