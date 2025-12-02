<?php
function obtenerEmpleados($pdo) {
    return $pdo->query("SELECT * FROM empleados ORDER BY id DESC")->fetchAll();
}

function obtenerEmpleadoPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM empleados WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function guardarEmpleadoModel($pdo, $data) {
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare("UPDATE empleados SET nombre=?, puesto=?, salario=?, telefono=? WHERE id=?");
        $stmt->execute([$data['nombre'], $data['puesto'], $data['salario'], $data['telefono'], $data['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO empleados (nombre, puesto, salario, telefono) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['nombre'], $data['puesto'], $data['salario'], $data['telefono']]);
    }
}

function eliminarEmpleadoModel($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM empleados WHERE id=?");
    $stmt->execute([$id]);
}

function buscarEmpleados($pdo, $q) {
    $stmt = $pdo->prepare("SELECT * FROM empleados 
                           WHERE nombre LIKE ? OR puesto LIKE ? OR telefono LIKE ?
                           ORDER BY id DESC");
    $stmt->execute(["%$q%", "%$q%", "%$q%"]);
    return $stmt->fetchAll();
}
