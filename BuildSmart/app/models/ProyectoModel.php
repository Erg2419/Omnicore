<?php
// app/models/ProyectoModel.php

// Obtener todos los proyectos
function obtenerProyectos($pdo) {
    $sql = "SELECT p.*, c.nombre AS cliente_nombre
            FROM proyectos p
            LEFT JOIN clientes c ON p.cliente_id = c.id
            ORDER BY p.id DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener un proyecto por su ID
function obtenerProyectoPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM proyectos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function guardarProyectoModel($pdo, $data) {
    $id = isset($data['id']) ? $data['id'] : null;

    $cliente_id   = trim($data['cliente_id']);
    $nombre       = trim($data['nombre']);
    $ubicacion    = trim($data['ubicacion']);
    $fecha_inicio = $data['fecha_inicio'];
    $fecha_fin    = $data['fecha_fin'];
    $estado       = $data['estado'];
    $descripcion  = trim($data['descripcion']);
    $avance       = isset($data['avance']) ? (int)$data['avance'] : 0; // ✅ NUEVO

    if (!empty($id)) {
        // Actualizar proyecto incluyendo avance
        $sql = "UPDATE proyectos 
                SET cliente_id = ?, nombre = ?, ubicacion = ?, fecha_inicio = ?, fecha_fin = ?, estado = ?, descripcion = ?, avance = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cliente_id, $nombre, $ubicacion, $fecha_inicio, $fecha_fin, $estado, $descripcion, $avance, $id]);
    } else {
        // Nuevo proyecto con avance
        $sql = "INSERT INTO proyectos (cliente_id, nombre, ubicacion, fecha_inicio, fecha_fin, estado, descripcion, avance)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cliente_id, $nombre, $ubicacion, $fecha_inicio, $fecha_fin, $estado, $descripcion, $avance]);
    }
}


// Eliminar proyecto
function eliminarProyectoModel($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM proyectos WHERE id = ?");
    $stmt->execute([$id]);
}

// Buscar proyectos
function buscarProyectos($pdo, $q) {
    $like = "%$q%";
    $sql = "SELECT p.*, c.nombre AS cliente_nombre
            FROM proyectos p
            LEFT JOIN clientes c ON p.cliente_id = c.id
            WHERE p.nombre LIKE ? OR p.descripcion LIKE ? OR p.ubicacion LIKE ?
            ORDER BY p.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$like, $like, $like]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// app/models/ProyectosModel.php

function obtenerProyectosParaGantt($pdo) {
    $sql = "SELECT id, nombre, fecha_inicio, fecha_fin, estado, COALESCE(avance,0) AS avance
            FROM proyectos
            WHERE fecha_inicio IS NOT NULL AND fecha_fin IS NOT NULL
            ORDER BY fecha_inicio ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
