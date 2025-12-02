<?php
require_once '../../config/db.php';
require_once '../../core/helpers.php';
require_login();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Reporte_Clientes_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

$q = $_GET['q'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM clientes 
                       WHERE nombre LIKE ? OR correo LIKE ?
                       ORDER BY id DESC");
$stmt->execute(["%$q%", "%$q%"]);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1'>";
echo "<tr>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Teléfono</th>
      </tr>";

foreach ($clientes as $c) {
    echo "<tr>
            <td>{$c['nombre']}</td>
            <td>{$c['correo']}</td>
            <td>{$c['telefono']}</td>
          </tr>";
}
echo "</table>";
exit;
