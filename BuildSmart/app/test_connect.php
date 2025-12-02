<?php
$DB_HOST = 'sql100.infinityfree.com';
$DB_NAME = 'if0_40238529_buildsmart';
$DB_USER = 'if0_40238529';
$DB_PASS = 'RmAKlPgEk6hh';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    echo "✅ Conexión exitosa a la base de datos.";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
