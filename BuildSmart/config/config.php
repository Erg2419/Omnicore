
<?php
// config/config.php

$env = parse_ini_file(__DIR__ . '/../.env');

if (!$env) {
    die("Falta el archivo .env en la raíz del proyecto.");
}

// Detectar si el sistema está en InfinityFree o en local
$is_remote = isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'infinityfreeapp.com') !== false ||
    strpos($_SERVER['HTTP_HOST'], 'epizy.com') !== false
);

// Seleccionar los datos de conexión según el entorno
if ($is_remote) {
    define('DB_HOST', $env['REMOTE_DB_HOST']);
    define('DB_NAME', $env['REMOTE_DB_NAME']);
    define('DB_USER', $env['REMOTE_DB_USER']);
    define('DB_PASS', $env['REMOTE_DB_PASS']);
} else {
    define('DB_HOST', $env['DB_HOST']);
    define('DB_NAME', $env['DB_NAME']);
    define('DB_USER', $env['DB_USER']);
    define('DB_PASS', $env['DB_PASS']);
}

// Configuración de rutas
define('BASE_PATH', $env['BASE_PATH']); 
define('BASE_URL', "http://{$_SERVER['HTTP_HOST']}" . BASE_PATH);
