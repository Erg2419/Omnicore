<?php
require_once __DIR__ . '/../../../core/helpers.php';
require_login();
global $pdo;

$filter = $_GET['filter'] ?? '';

$sql = "
  SELECT pa.*, p.nombre AS proyecto
  FROM pagos pa
  LEFT JOIN proyectos p ON pa.proyecto_id = p.id
  WHERE pa.descripcion LIKE :filter OR p.nombre LIKE :filter
  ORDER BY pa.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':filter' => "%$filter%"]);
$pagos = $stmt->fetchAll();

// Cabecera para descargar como PDF (opcional)
// header('Content-Type: application/pdf');
// header('Content-Disposition: attachment; filename="reporte_pagos.pdf"');

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Pagos</title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 14px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    th { background-color: #f97316; color: white; }
  </style>
</head>
<body>
  <h2>Reporte de Pagos <?= $filter ? "(Filtro: " . htmlspecialchars($filter) . ")" : "" ?></h2>

  <table>
    <thead>
      <tr>
        <th>Proyecto</th>
        <th>Descripción</th>
        <th>Monto (RD$)</th>
        <th>Tipo</th>
        <th>Método</th>
        <th>Fecha</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($pagos)): ?>
        <?php foreach ($pagos as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['proyecto']) ?></td>
            <td><?= htmlspecialchars($p['descripcion']) ?></td>
            <td><?= number_format($p['monto'], 2) ?></td>
            <td><?= $p['tipo'] === 'entrada' ? 'Entrada' : 'Salida' ?></td>
            <td><?= ucfirst($p['metodo_pago']) ?></td>
            <td><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6">No se encontraron registros.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
