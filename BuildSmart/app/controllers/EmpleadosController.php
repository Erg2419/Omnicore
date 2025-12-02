<?php
require_once __DIR__ . '/../models/EmpleadoModel.php';
require_once __DIR__ . '/../../core/helpers.php';
require_login();

// ✅ LISTAR EMPLEADOS
function listarEmpleados() {
    require_login();
    global $pdo;

    $empleados = obtenerEmpleados($pdo);
    include __DIR__ . '/../views/empleados/listar.php';
}

// ✅ FORMULARIO CREAR/EDITAR
function formularioEmpleado() {
    require_login();
    global $pdo;

    $id = $_GET['id'] ?? null;
    $empleado = $id ? obtenerEmpleadoPorId($pdo, $id) : null;
    include __DIR__ . '/../views/empleados/form.php';
}

// ✅ GUARDAR
function guardarEmpleado() {
    require_login();
    global $pdo;

    guardarEmpleadoModel($pdo, $_POST);
    header('Location: index.php?page=empleados');
    exit;
}

// ✅ ELIMINAR
function eliminarEmpleado() {
    require_login();
    global $pdo;

    if (!empty($_GET['id'])) {
        eliminarEmpleadoModel($pdo, $_GET['id']);
    }
    header('Location: index.php?page=empleados');
    exit;
}

// ✅ BUSCAR (para AJAX)
function buscarEmpleado() {
    require_login();
    global $pdo;

    $q = $_GET['q'] ?? '';
    $empleados = buscarEmpleados($pdo, $q);
    include __DIR__ . '/../views/empleados/tabla.php';
}
