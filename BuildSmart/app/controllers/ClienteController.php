<?php
require_once __DIR__ . '/../models/ClienteModel.php';
require_once __DIR__ . '/../../core/helpers.php';

function listarClientes() {
    require_login();
    global $pdo;

    $clientes = obtenerClientes($pdo);
    include __DIR__ . '/../views/clientes/listar.php';
}

function formularioCliente() {
    require_login();
    global $pdo;

    $id = $_GET['id'] ?? null;
    $cliente = $id ? obtenerClientePorId($pdo, $id) : null;
    include __DIR__ . '/../views/clientes/form.php';
}

function guardarCliente() {
    require_login();
    global $pdo;

    guardarClienteModel($pdo, $_POST);
    flash("✅ Cliente guardado correctamente");
    redirectTo('clientes');
}

function eliminarCliente() {
    require_login();
    global $pdo;

    if (!empty($_GET['id'])) {
        eliminarClienteModel($pdo, $_GET['id']);
        flash("🗑️ Cliente eliminado correctamente");
    }
    redirectTo('clientes');
}

function buscarCliente() {
    require_login();
    global $pdo;

    $q = $_GET['q'] ?? '';
    $clientes = buscarClientes($pdo, $q);
    include __DIR__ . '/../views/clientes/tabla.php';
}
