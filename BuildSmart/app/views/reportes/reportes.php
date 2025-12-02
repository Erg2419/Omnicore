<div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100">
  <h1 class="text-3xl font-bold text-[#f97316] mb-6">📊 Reporte de Costos de Proyectos</h1>

  <div class="overflow-x-auto">
    <table class="min-w-full bg-white border text-sm rounded-lg">
      <thead class="bg-gray-100 text-gray-600 uppercase">
        <tr>
          <th class="px-6 py-3">Proyecto</th>
          <th class="px-6 py-3">Materiales</th>
          <th class="px-6 py-3">Mano de Obra</th>
          <th class="px-6 py-3">Otros</th>
          <th class="px-6 py-3">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($costos as $c): ?>
          <tr class="border-b hover:bg-orange-50 transition">
            <td class="px-6 py-3 font-semibold"><?= htmlspecialchars($c['proyecto']) ?></td>
            <td class="px-6 py-3"><?= number_format($c['costo_materiales'], 2) ?> RD$</td>
            <td class="px-6 py-3"><?= number_format($c['costo_mano_obra'], 2) ?> RD$</td>
            <td class="px-6 py-3"><?= number_format($c['otros_gastos'], 2) ?> RD$</td>
            <td class="px-6 py-3 font-bold text-[#f97316]"><?= number_format($c['total'], 2) ?> RD$</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
