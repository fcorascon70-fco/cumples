<?php
echo "<h1>Diagnóstico de PHP y MySQL</h1>";

// 1. Verificar si PDO MySQL está instalado
if (extension_loaded('pdo_mysql')) {
    echo "✅ Extensión PDO_MySQL: INSTALADA e INICIADA.<br>";
} else {
    echo "❌ Extensión PDO_MySQL: NO ENCONTRADA. Debes activarla en el archivo php.ini de XAMPP.<br>";
}

// 2. Probar conexión al servidor remoto
$host = '162.216.5.50';
$db   = 'directac_RNM_2025';
$user = 'directac_fco';
$pass = '**xmiswebs**';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    echo "✅ Conexión al servidor remoto: EXITOSA.<br>";
} catch (Exception $e) {
    echo "❌ Conexión al servidor remoto: FALLIDA.<br>";
    echo "Error: " . $e->getMessage();
}

echo "<br><br><a href='index.html'>Volver a la App</a>";
?>
