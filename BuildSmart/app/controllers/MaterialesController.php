<?php
require_once __DIR__ . '/../models/MaterialesModel.php';
require_once __DIR__ . '/../../core/helpers.php';

function listarMateriales() {
    require_login();
    global $pdo;

    $materiales = obtenerMateriales($pdo);
    include __DIR__ . '/../views/materiales/listar.php';
}

function formularioMaterial() {
    require_login();
    global $pdo;

    $id = $_GET['id'] ?? null;
    $material = $id ? obtenerMaterialPorId($pdo, $id) : null;
    $proveedores = obtenerProveedores($pdo);
    $proyectos = obtenerProyectos($pdo);

    include __DIR__ . '/../views/materiales/form.php';
}

function guardarMaterial() {
    require_login();
    global $pdo;

    guardarMaterialModel($pdo, $_POST);
    header('Location: index.php?page=materiales');
    exit;
}

function eliminarMaterial() {
    require_login();
    global $pdo;

    if (!empty($_GET['id'])) {
        eliminarMaterialModel($pdo, $_GET['id']);
    }
    header('Location: index.php?page=materiales');
    exit;
}

function buscarMaterial() {
    require_login();
    global $pdo;

    $q = $_GET['q'] ?? '';
    $materiales = buscarMateriales($pdo, $q);
    include __DIR__ . '/../views/materiales/tabla.php';
}
