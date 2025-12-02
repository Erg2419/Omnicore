<?php
function obtenerProveedores($pdo) {
    $stmt = $pdo->query("SELECT * FROM proveedores ORDER BY id DESC");
    return $stmt->fetchAll();
}

function obtenerProveedorPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM proveedores WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

function guardarProveedorModel($pdo, $data) {
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare("UPDATE proveedores SET nombre=:nombre, telefono=:telefono, correo=:correo, direccion=:direccion WHERE id=:id");
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':telefono' => $data['telefono'],
            ':correo' => $data['correo'],
            ':direccion' => $data['direccion'],
            ':id' => $data['id']
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO proveedores (nombre, telefono, correo, direccion) VALUES (:nombre, :telefono, :correo, :direccion)");
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':telefono' => $data['telefono'],
            ':correo' => $data['correo'],
            ':direccion' => $data['direccion']
        ]);
    }
}

function eliminarProveedorModel($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM proveedores WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

function buscarProveedores($pdo, $q) {
    $stmt = $pdo->prepare("SELECT * FROM proveedores WHERE nombre LIKE :q OR correo LIKE :q OR telefono LIKE :q ORDER BY id DESC");
    $stmt->execute([':q' => "%$q%"]);
    return $stmt->fetchAll();
}
