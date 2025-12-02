<?php
// app/models/TareasModel.php

function obtenerTareas($pdo) {
    $sql = "SELECT 
                t.id,
                t.nombre,
                t.descripcion,
                t.estado,
                t.proyecto_id,
                p.nombre AS proyecto_nombre,
                t.fecha_inicio,
                t.fecha_fin,
                t.progreso
            FROM tareas t
            LEFT JOIN proyectos p ON t.proyecto_id = p.id
            ORDER BY t.id DESC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerTareaPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function buscarTareas($pdo, $query) {
    $sql = "SELECT 
                t.id,
                t.nombre,
                t.descripcion,
                t.estado,
                t.proyecto_id,
                p.nombre AS proyecto_nombre,
                t.fecha_inicio,
                t.fecha_fin,
                t.progreso
            FROM tareas t
            LEFT JOIN proyectos p ON t.proyecto_id = p.id
            WHERE t.nombre LIKE :query OR p.nombre LIKE :query
            ORDER BY t.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['query' => "%$query%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function guardarTarea($pdo, $data) {
    if (!empty($data['id'])) {
        $sql = "UPDATE tareas 
                SET nombre=?, descripcion=?, estado=?, proyecto_id=?, fecha_inicio=?, fecha_fin=?, progreso=? 
                WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?? '',
            $data['estado'] ?? 'pendiente',
            $data['proyecto_id'],
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $data['progreso'] ?? 0,
            $data['id']
        ]);
    } else {
        $sql = "INSERT INTO tareas (nombre, descripcion, estado, proyecto_id, fecha_inicio, fecha_fin, progreso)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?? '',
            $data['estado'] ?? 'pendiente',
            $data['proyecto_id'],
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $data['progreso'] ?? 0
        ]);
    }
}

function eliminarTarea($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM tareas WHERE id = ?");
    $stmt->execute([$id]);
}
