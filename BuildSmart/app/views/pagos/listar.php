<?php
require_once __DIR__ . '/../../../core/helpers.php';
require_login();
global $pdo;

$stmt = $pdo->query("
  SELECT pa.*, p.nombre AS proyecto 
  FROM pagos pa
  LEFT JOIN proyectos p ON pa.proyecto_id = p.id
  ORDER BY pa.id DESC
");
$pagos = $stmt->fetchAll();
?>

<div class="space-y-6 animate-fadeIn">
  <div class="flex justify-between items-center">
    <h1 class="text-3xl font-bold text-[#f97316] flex items-center gap-2">
      💰 Gestión de Pagos
    </h1>
    <a href="<?= BASE_URL ?>/index.php?page=pagos/form" 
       class="px-4 py-2 bg-[#f97316] text-white rounded-lg shadow hover:bg-orange-600 transition">
       ➕ Registrar Pago
    </a>
  </div>

  <div class="bg-white shadow rounded-xl overflow-hidden border border-gray-100">
    <table class="w-full text-sm text-left text-gray-700">
      <thead class="bg-orange-50 text-[#f97316] uppercase text-xs">
        <tr>
          <th class="px-4 py-3">Proyecto</th>
          <th class="px-4 py-3">Descripción</th>
          <th class="px-4 py-3">Monto (RD$)</th>
          <th class="px-4 py-3">Tipo</th>
          <th class="px-4 py-3">Método</th>
          <th class="px-4 py-3">Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($pagos)): ?>
          <?php foreach ($pagos as $p): ?>
            <tr class="border-b hover:bg-orange-50 transition">
              <td class="px-4 py-2"><?= htmlspecialchars($p['proyecto']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($p['descripcion']) ?></td>
              <td class="px-4 py-2 font-semibold text-gray-800"><?= number_format($p['monto'], 2) ?></td>
              <td class="px-4 py-2"><?= $p['tipo'] === 'entrada' ? '💵 Entrada' : '💸 Salida' ?></td>
              <td class="px-4 py-2"><?= ucfirst($p['metodo_pago']) ?></td>
              <td class="px-4 py-2"><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center py-6 text-gray-500">No hay pagos registrados aún.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fadeIn { animation: fadeIn .6s ease-in-out; }
</style>
