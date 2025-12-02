<?php
// app/db.php

// Detectar si estamos en InfinityFree (hosting en línea)
$is_remote = isset($_SERVER['HTTP_HOST']) && !str_contains($_SERVER['HTTP_HOST'], 'localhost');

// Cargar variables del archivo .env
$env = parse_ini_file(__DIR__ . '/../.env');

// Elegir configuración según el entorno
if ($is_remote) {
    $DB_HOST = trim($env['REMOTE_DB_HOST']);
    $DB_NAME = trim($env['REMOTE_DB_NAME']);
    $DB_USER = trim($env['REMOTE_DB_USER']);
    $DB_PASS = trim($env['REMOTE_DB_PASS']);
} else {
    $DB_HOST = trim($env['DB_HOST']);
    $DB_NAME = trim($env['DB_NAME']);
    $DB_USER = trim($env['DB_USER']);
    $DB_PASS = trim($env['DB_PASS']);
}

try {
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Mostrar error en desarrollo, pero no en producción
    if (str_contains($_SERVER['HTTP_HOST'], 'localhost')) {
        die("❌ Error de conexión a la base de datos: " . $e->getMessage());
    } else {
        error_log("Error DB: " . $e->getMessage());
        die("⚠️ Error interno del servidor.");
    }
}
