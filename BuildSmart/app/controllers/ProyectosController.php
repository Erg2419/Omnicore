<?php
require_once __DIR__ . '/../models/ProyectoModel.php';
require_once __DIR__ . '/../../core/helpers.php';

function listarProyectos() {
    require_login();
    global $pdo;
    $proyectos = obtenerProyectos($pdo);
    include __DIR__ . '/../views/proyectos/listar.php';
}

function formularioProyecto() {
    require_login();
    global $pdo;

    $id = $_GET['id'] ?? null;
    $clientes = $pdo->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC")->fetchAll();
    $proyecto = $id ? obtenerProyectoPorId($pdo, $id) : null;
    include __DIR__ . '/../views/proyectos/form.php';
}

function guardarProyecto() {
    require_login();
    global $pdo;

    guardarProyectoModel($pdo, $_POST);
    header('Location: index.php?page=proyectos');
    exit;
}

function eliminarProyecto() {
    require_login();
    global $pdo;
    if (!empty($_GET['id'])) {
        eliminarProyectoModel($pdo, $_GET['id']);
    }
    header('Location: index.php?page=proyectos');
    exit;
}

function buscarProyecto() {
    require_login();
    global $pdo;
    $q = $_GET['q'] ?? '';
    $proyectos = buscarProyectos($pdo, $q);
    include __DIR__ . '/../views/proyectos/tabla.php';
}

function ganttData() {
    require_login();
    global $pdo;

    header('Content-Type: application/json; charset=utf-8');

    try {
        $sql = "SELECT id, nombre AS name, 
                       fecha_inicio AS start, fecha_fin AS end, 
                       COALESCE(avance, 0) AS progress, 
                       estado
                FROM proyectos
                WHERE fecha_inicio IS NOT NULL AND fecha_fin IS NOT NULL
                ORDER BY fecha_inicio ASC";
        $stmt = $pdo->query($sql);
        $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir estado en clase CSS para colores
        foreach($proyectos as &$p) {
            $p['custom_class'] = match(strtolower($p['estado'])) {
                'finalizado' => 'finalizado',
                'en_ejecucion' => 'en-proceso',
                'planificado' => 'planificado',
                'cancelado' => 'cancelado',
                default => 'pendiente'
            };
        }

        // ✅ El campo progress ya contiene el valor real de la BD
        echo json_encode($proyectos, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
