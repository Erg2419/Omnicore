<div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-[#f97316]">🧱 Control de Materiales</h1>
    <a href="<?= BASE_URL ?>/index.php?page=materiales_create"
       class="bg-[#f97316] hover:bg-orange-600 text-white px-4 py-2 rounded-md transition">
       ➕ Registrar Material
    </a>
  </div>

  <table class="min-w-full bg-white border rounded-lg text-sm">
    <thead class="bg-gray-100 text-gray-600 uppercase">
      <tr>
        <th class="px-6 py-3">#</th>
        <th class="px-6 py-3">Proyecto</th>
        <th class="px-6 py-3">Nombre</th>
        <th class="px-6 py-3">Cantidad</th>
        <th class="px-6 py-3">Costo Unitario</th>
        <th class="px-6 py-3">Proveedor</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($materiales as $m): ?>
      <tr class="border-b hover:bg-orange-50 transition">
        <td class="px-6 py-3"><?= $m['id'] ?></td>
        <td class="px-6 py-3"><?= htmlspecialchars($m['proyecto'] ?? '—') ?></td>
        <td class="px-6 py-3"><?= htmlspecialchars($m['nombre']) ?></td>
        <td class="px-6 py-3"><?= $m['cantidad'] ?></td>
        <td class="px-6 py-3"><?= number_format($m['costo_unitario'], 2) ?> RD$</td>
        <td class="px-6 py-3"><?= htmlspecialchars($m['proveedor']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
