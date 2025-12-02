<?php
require_once __DIR__ . '/../models/ProveedorModel.php';
require_once __DIR__ . '/../../core/helpers.php';

function listarProveedores() {
    require_login();
    global $pdo;
    $proveedores = obtenerProveedores($pdo);
   include __DIR__ . '/../views/proveedores/listar.php';

}

function formularioProveedor() {
    require_login();
    global $pdo;
    $id = $_GET['id'] ?? null;
    $proveedor = $id ? obtenerProveedorPorId($pdo, $id) : null;
      include __DIR__ . '/../views/proveedores/form.php';
}

function guardarProveedor() {
    require_login();
    global $pdo;
    guardarProveedorModel($pdo, $_POST);
    header('Location: index.php?page=proveedores');
    exit;
}

function eliminarProveedor() {
    require_login();
    global $pdo;
    if (!empty($_GET['id'])) eliminarProveedorModel($pdo, $_GET['id']);
    header('Location: index.php?page=proveedores');
    exit;
}

function buscarProveedor() {
    require_login();
    global $pdo;
    $q = $_GET['q'] ?? '';
    $proveedores = buscarProveedores($pdo, $q);
    include 'app/views/proveedores/tabla.php';
}
