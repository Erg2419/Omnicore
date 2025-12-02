<?php
/**
 * Setup Database - Script de inicialización
 * Crear la base de datos y tablas necesarias
 */

$host = "localhost";
$username = "root";
$password = "";
$db_name = "sistema_restaurante";

// Conectar sin especificar BD
try {
    $conn = new PDO("mysql:host=" . $host, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear base de datos si no existe
    $sql = "CREATE DATABASE IF NOT EXISTS " . $db_name;
    $conn->exec($sql);
    
    // Conectar a la BD
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tabla mesas
    $sql = "CREATE TABLE IF NOT EXISTS mesas (
        id_mesa INT AUTO_INCREMENT PRIMARY KEY,
        numero_mesa INT UNIQUE NOT NULL,
        capacidad INT NOT NULL,
        ubicacion VARCHAR(100),
        estado ENUM('disponible', 'ocupada', 'reservada', 'mantenimiento') DEFAULT 'disponible',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    
    // Crear tabla ordenes
    $sql = "CREATE TABLE IF NOT EXISTS ordenes (
        id_orden INT AUTO_INCREMENT PRIMARY KEY,
        id_mesa INT,
        numero_orden INT UNIQUE,
        estado ENUM('pendiente', 'confirmada', 'en_preparacion', 'lista', 'entregada', 'pagada', 'cancelada') DEFAULT 'pendiente',
        monto_total DECIMAL(10, 2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa)
    )";
    $conn->exec($sql);
    
    // Crear tabla inventario
    $sql = "CREATE TABLE IF NOT EXISTS inventario (
        id_inventario INT AUTO_INCREMENT PRIMARY KEY,
        nombre_ingrediente VARCHAR(100) NOT NULL,
        cantidad DECIMAL(10, 2) NOT NULL DEFAULT 0,
        unidad_medida VARCHAR(20),
        stock_minimo DECIMAL(10, 2) DEFAULT 0,
        proveedor VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    
    // Crear tabla reservas
    $sql = "CREATE TABLE IF NOT EXISTS reservas (
        id_reserva INT AUTO_INCREMENT PRIMARY KEY,
        id_mesa INT,
        nombre_cliente VARCHAR(100) NOT NULL,
        email_cliente VARCHAR(100),
        telefono_cliente VARCHAR(20),
        cantidad_personas INT NOT NULL,
        fecha_reserva DATETIME NOT NULL,
        estado ENUM('pendiente', 'confirmada', 'cancelada', 'completada') DEFAULT 'pendiente',
        notas TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa)
    )";
    $conn->exec($sql);
    
    // Crear tabla menu
    $sql = "CREATE TABLE IF NOT EXISTS menu (
        id_producto INT AUTO_INCREMENT PRIMARY KEY,
        nombre_producto VARCHAR(100) NOT NULL,
        descripcion TEXT,
        precio DECIMAL(10, 2) NOT NULL,
        categoria VARCHAR(50),
        disponible BOOLEAN DEFAULT 1,
        tiempo_preparacion INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    
    // Insertar datos de ejemplo en mesas
    $sql = "INSERT IGNORE INTO mesas (numero_mesa, capacidad, ubicacion, estado) VALUES
        (1, 2, 'Entrada', 'disponible'),
        (2, 2, 'Entrada', 'disponible'),
        (3, 4, 'Centro', 'disponible'),
        (4, 4, 'Centro', 'disponible'),
        (5, 6, 'Terraza', 'disponible'),
        (6, 8, 'Terraza', 'disponible')";
    $conn->exec($sql);
    
    echo "✅ Base de datos y tablas creadas exitosamente.\n";
    echo "✅ Datos de ejemplo insertados.\n";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
