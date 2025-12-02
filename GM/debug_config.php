<?php
session_start();
include 'db.php';

echo "<h3>🔍 Depuración de Configuración</h3>";

if(!isset($_SESSION['usuario_id'])){
    echo "❌ No hay sesión de usuario<br>";
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
echo "✅ Usuario ID: $usuario_id<br>";

// Verificar conexión a la base de datos
if ($conn->connect_error) {
    echo "❌ Error de conexión: " . $conn->connect_error . "<br>";
    exit;
}
echo "✅ Conexión a BD exitosa<br>";

// Verificar si existe la tabla
$table_check = $conn->query("SHOW TABLES LIKE 'configuracion_usuario'");
if ($table_check->num_rows == 0) {
    echo "❌ La tabla 'configuracion_usuario' NO existe<br>";
} else {
    echo "✅ La tabla 'configuracion_usuario' existe<br>";
}

// Verificar datos del usuario
$user_sql = "SELECT id, email, nombre FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows == 0) {
    echo "❌ No se encontró el usuario con ID: $usuario_id<br>";
} else {
    $user_data = $user_result->fetch_assoc();
    echo "✅ Usuario encontrado: " . $user_data['nombre'] . " (" . $user_data['email'] . ")<br>";
}
$stmt->close();

// Verificar configuración
$config_sql = "SELECT * FROM configuracion_usuario WHERE usuario_id = ?";
$stmt = $conn->prepare($config_sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$config_result = $stmt->get_result();

if ($config_result->num_rows == 0) {
    echo "❌ No hay configuración para el usuario ID: $usuario_id<br>";
} else {
    $config_data = $config_result->fetch_assoc();
    echo "✅ Configuración encontrada:<br>";
    echo "<pre>";
    print_r($config_data);
    echo "</pre>";
}
$stmt->close();

$conn->close();
?>