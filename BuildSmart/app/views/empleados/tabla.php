<table class="w-full border-collapse">
  <thead>
    <tr class="bg-orange-50 text-orange-600 text-left uppercase text-sm">
      <th class="p-3">ID</th>
      <th class="p-3">Nombre</th>
      <th class="p-3">Puesto</th>
      <th class="p-3 text-right">Salario</th>
      <th class="p-3">Teléfono</th>
      <th class="p-3 text-center">Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($empleados)): ?>
      <?php foreach ($empleados as $e): ?>
        <tr class="hover:bg-orange-50 transition">
          <td class="p-3 border-b border-gray-100"><?= $e['id'] ?></td>
          <td class="p-3 border-b border-gray-100 font-medium"><?= htmlspecialchars($e['nombre']) ?></td>
          <td class="p-3 border-b border-gray-100"><?= htmlspecialchars($e['puesto']) ?></td>
          <td class="p-3 border-b border-gray-100 text-right"><?= number_format($e['salario'], 2) ?> RD$</td>
          <td class="p-3 border-b border-gray-100"><?= htmlspecialchars($e['telefono']) ?></td>
          <td class="p-3 border-b border-gray-100 text-center flex justify-center gap-2">
            <!-- Botón Editar -->
            <a href="index.php?page=empleados/form&id=<?= $e['id'] ?>" 
               class="px-3 py-1 rounded-md text-xs bg-blue-500 hover:bg-blue-600 text-white font-semibold transition">
              ✏️ Editar
            </a>
            <!-- Botón Eliminar -->
            <a href="index.php?page=empleados/delete&id=<?= $e['id'] ?>" 
               class="px-3 py-1 rounded-md text-xs bg-red-500 hover:bg-red-600 text-white font-semibold transition"
               onclick="return confirm('¿Seguro que deseas eliminar este empleado?');">
              🗑️ Eliminar
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="6" class="text-center py-6 text-gray-500">No se encontraron empleados.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
