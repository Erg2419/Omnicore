<?php
require_once __DIR__ . '/../../../core/helpers.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO pagos (proyecto_id, descripcion, monto, tipo, metodo_pago, fecha)
            VALUES (:proyecto_id, :descripcion, :monto, :tipo, :metodo_pago, :fecha)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':proyecto_id' => $_POST['proyecto_id'],
        ':descripcion' => $_POST['descripcion'],
        ':monto' => $_POST['monto'],
        ':tipo' => $_POST['tipo'],
        ':metodo_pago' => $_POST['metodo_pago'],
        ':fecha' => $_POST['fecha']
    ]);

    flash('Pago registrado correctamente ✅', 'success');
    redirectTo('pagos/listar');
} else {
    redirectTo('pagos/listar');
}
