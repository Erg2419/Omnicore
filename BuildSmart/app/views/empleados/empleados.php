<div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-[#f97316] flex items-center gap-2">
      👷 Gestión de Empleados
    </h1>
    <a href="<?= BASE_URL ?>/index.php?page=empleados_create"
       class="bg-[#f97316] hover:bg-orange-600 text-white px-4 py-2 rounded-md font-medium transition">
      ➕ Nuevo Empleado
    </a>
  </div>

  <div class="overflow-hidden rounded-lg border border-gray-200">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-gray-100 text-gray-600 uppercase">
        <tr>
          <th class="px-6 py-3">#</th>
          <th class="px-6 py-3">Nombre</th>
          <th class="px-6 py-3">Puesto</th>
          <th class="px-6 py-3">Salario</th>
          <th class="px-6 py-3">Teléfono</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($empleados as $e): ?>
          <tr class="border-b hover:bg-orange-50 transition">
            <td class="px-6 py-3 font-medium"><?= $e['id'] ?></td>
            <td class="px-6 py-3"><?= htmlspecialchars($e['nombre']) ?></td>
            <td class="px-6 py-3"><?= htmlspecialchars($e['puesto']) ?></td>
            <td class="px-6 py-3 text-right"><?= number_format($e['salario'], 2) ?> RD$</td>
            <td class="px-6 py-3"><?= htmlspecialchars($e['telefono']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
