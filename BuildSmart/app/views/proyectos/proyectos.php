<!-- app/views/proyectos.php -->
<div class="flex items-center justify-between mb-4">
  <h2 class="text-xl font-bold text-brand-500">Proyectos</h2>
  <a class="btn" href="<?= BASE_URL ?>/index.php?page=proyectos_create">+ Nuevo proyecto</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
  <table class="min-w-full">
    <thead class="bg-gray-100">
      <tr>
        <th class="p-3 text-left">#</th>
        <th class="p-3 text-left">Proyecto</th>
        <th class="p-3 text-left">Cliente</th>
        <th class="p-3">Fechas</th>
        <th class="p-3">Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($proyectos as $p): ?>
        <tr class="border-b">
          <td class="p-3"><?= htmlspecialchars($p['id']) ?></td>
          <td class="p-3"><?= htmlspecialchars($p['nombre']) ?></td>
          <td class="p-3"><?= htmlspecialchars($p['cliente_nombre']) ?></td>
          <td class="p-3"><?= htmlspecialchars($p['fecha_inicio']) ?> → <?= htmlspecialchars($p['fecha_fin']) ?></td>
          <td class="p-3"><?= htmlspecialchars($p['estado']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
