<?php
// create_admin.php - ejecútalo UNA vez para crear admin
require_once __DIR__ . '/app/db.php';

$nombre = 'Administrador';
$correo = 'admin@buildsmart.local';
$password = 'Admin123!'; // cámbiala si quieres
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    if ($stmt->fetch()) {
        echo "El usuario ya existe: $correo";
        exit;
    }

    $i = $pdo->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?,?,?,?)");
    $i->execute([$nombre, $correo, $hash, 'admin']);
    echo "Administrador creado: $correo con contraseña: $password\n";
    echo "Por seguridad, cambia la contraseña después de ingresar.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
