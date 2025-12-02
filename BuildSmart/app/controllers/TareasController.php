<?php
require_once __DIR__ . '/../models/TareasModel.php';
require_once __DIR__ . '/../models/ProyectosModel.php';
require_once __DIR__ . '/../../core/helpers.php';
function listarTareas() {
    require_login();
    global $pdo;
    $tareas = obtenerTareas($pdo);
    include __DIR__ . '/../views/tareas/listar.php';
}

function formularioTarea() {
    require_login();
    global $pdo;
    $id = $_GET['id'] ?? null;
    $tarea = $id ? obtenerTareaPorId($pdo, $id) : null;
    $proyectos = obtenerProyectos($pdo);
    $csrf = generar_csrf();

    // Incluir layout completo (header + form + footer)
    include __DIR__ . '/../views/tareas/layout_form.php';
}

function guardarTareaController() {
    require_login();
    global $pdo;

    if (!verificar_csrf($_POST['csrf'] ?? '')) {
        die('Token CSRF inválido');
    }

    $data = [
        'id' => $_POST['id'] ?? null,
        'nombre' => $_POST['nombre'],
        'descripcion' => $_POST['descripcion'] ?? '',
        'estado' => $_POST['estado'] ?? 'pendiente',
        'proyecto_id' => $_POST['proyecto_id'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin' => $_POST['fecha_fin'],
        'progreso' => $_POST['progreso'] ?? 0,
    ];

    guardarTarea($pdo, $data);
    header('Location: index.php?page=tareas');
    exit;
}

function eliminarTareaController() {
    require_login();
    global $pdo;
    if (!isset($_GET['id'])) die('ID de tarea no proporcionado');
    eliminarTarea($pdo, $_GET['id']);
    header('Location: index.php?page=tareas');
    exit;
}

function buscarTareasController() {
    require_login();
    global $pdo;
    $query = $_GET['q'] ?? '';
    $tareas = buscarTareas($pdo, $query);
    include __DIR__ . '/../views/tareas/tabla.php';
}
