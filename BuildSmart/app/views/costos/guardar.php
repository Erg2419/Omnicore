<?php
require_once __DIR__ . '/../../../core/helpers.php';
require_login();

global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        INSERT INTO costos (proyecto_id, costo_materiales, costo_mano_obra, otros_gastos, total)
        VALUES (:proyecto_id, :costo_materiales, :costo_mano_obra, :otros_gastos,
                (:costo_materiales + :costo_mano_obra + :otros_gastos))
    ");
    $stmt->execute([
        ':proyecto_id' => $_POST['proyecto_id'],
        ':costo_materiales' => $_POST['costo_materiales'],
        ':costo_mano_obra' => $_POST['costo_mano_obra'],
        ':otros_gastos' => $_POST['otros_gastos']
    ]);

    flash("💰 Costo registrado correctamente");
    redirectTo('costos');
}
?>
