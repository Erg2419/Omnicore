<?php

// 🧱 Obtener todos los materiales con proveedor y proyecto
function obtenerMateriales($pdo) {
    $stmt = $pdo->query("
        SELECT 
            m.*, 
            p.nombre AS proveedor, 
            pr.nombre AS proyecto
        FROM materiales m 
        LEFT JOIN proveedores p ON m.proveedor_id = p.id 
        LEFT JOIN proyectos pr ON m.proyecto_id = pr.id 
        ORDER BY m.id DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 🧱 Obtener un material por su ID
function obtenerMaterialPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM materiales WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 🧱 Guardar o actualizar material
function guardarMaterialModel($pdo, $data) {
    $proveedor_id = !empty($data['proveedor_id']) ? $data['proveedor_id'] : null;
    $proyecto_id  = !empty($data['proyecto_id']) ? $data['proyecto_id'] : null;

    if (!empty($data['id'])) {
        // ✏️ Editar material existente
        $stmt = $pdo->prepare("
            UPDATE materiales 
            SET nombre = :nombre,
                cantidad = :cantidad,
                costo_unitario = :costo_unitario,
                proveedor_id = :proveedor_id,
                proyecto_id = :proyecto_id
            WHERE id = :id
        ");
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':cantidad' => $data['cantidad'],
            ':costo_unitario' => $data['costo_unitario'],
            ':proveedor_id' => $proveedor_id,
            ':proyecto_id' => $proyecto_id,
            ':id' => $data['id']
        ]);
    } else {
        // 🟢 Crear nuevo material
        $stmt = $pdo->prepare("
            INSERT INTO materiales (nombre, cantidad, costo_unitario, proveedor_id, proyecto_id)
            VALUES (:nombre, :cantidad, :costo_unitario, :proveedor_id, :proyecto_id)
        ");
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':cantidad' => $data['cantidad'],
            ':costo_unitario' => $data['costo_unitario'],
            ':proveedor_id' => $proveedor_id,
            ':proyecto_id' => $proyecto_id
        ]);
    }
}

// 🧱 Eliminar material
function eliminarMaterialModel($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM materiales WHERE id = ?");
    $stmt->execute([$id]);
}

// 🧱 Buscar materiales por nombre, proveedor o proyecto
function buscarMateriales($pdo, $q) {
    $stmt = $pdo->prepare("
        SELECT 
            m.*, 
            p.nombre AS proveedor, 
            pr.nombre AS proyecto
        FROM materiales m
        LEFT JOIN proveedores p ON m.proveedor_id = p.id
        LEFT JOIN proyectos pr ON m.proyecto_id = pr.id
        WHERE 
            m.nombre LIKE ? 
            OR p.nombre LIKE ? 
            OR pr.nombre LIKE ?
        ORDER BY m.id DESC
    ");
    $stmt->execute(["%$q%", "%$q%", "%$q%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 🧱 Obtener lista de proveedores
function obtenerProveedores($pdo) {
    return $pdo->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
}

// 🧱 Obtener lista de proyectos
function obtenerProyectos($pdo) {
    return $pdo->query("SELECT id, nombre FROM proyectos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
}
