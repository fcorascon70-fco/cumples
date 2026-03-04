<?php
session_start();
require_once 'config.php';
sendSecurityHeaders();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo json_encode(['logged_in' => true, 'usuario' => $_SESSION['usuario']]);
} else {
    echo json_encode(['logged_in' => false]);
}
