<?php

function obtenerClientes($pdo) {
    $stmt = $pdo->query("SELECT * FROM clientes ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerClientePorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function guardarClienteModel($pdo, $data) {
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare("UPDATE clientes 
                               SET nombre = ?, correo = ?, telefono = ?
                               WHERE id = ?");
        $stmt->execute([
            trim($data['nombre']),
            trim($data['correo']),
            trim($data['telefono']),
            $data['id']
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO clientes (nombre, correo, telefono)
                               VALUES (?, ?, ?)");
        $stmt->execute([
            trim($data['nombre']),
            trim($data['correo']),
            trim($data['telefono'])
        ]);
    }
}

function eliminarClienteModel($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
}

function buscarClientes($pdo, $q) {
    $stmt = $pdo->prepare("SELECT * FROM clientes 
                           WHERE nombre LIKE ? OR correo LIKE ? 
                           ORDER BY id DESC");
    $stmt->execute(["%$q%", "%$q%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
