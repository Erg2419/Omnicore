<div class="overflow-x-auto w-full">
  <table class="min-w-full divide-y divide-gray-200 text-sm border-collapse">
    <thead class="bg-orange-50 text-orange-700 font-semibold uppercase">
      <tr>
        <th class="px-4 py-2 text-left">ID</th>
        <th class="px-4 py-2 text-left">Nombre</th>
        <th class="px-4 py-2 text-left">Cantidad</th>
        <th class="px-4 py-2 text-left">Costo Unitario</th>
        <th class="px-4 py-2 text-left">Proveedor</th>
        <th class="px-4 py-2 text-left">Proyecto</th>
        <th class="px-4 py-2 text-center">Acciones</th>
      </tr>
    </thead>

    <tbody class="divide-y divide-gray-100">
      <?php if (!empty($materiales)): ?>
        <?php foreach ($materiales as $m): ?>
          <tr class="hover:bg-orange-50 transition">
            <td class="px-4 py-2"><?= $m['id'] ?></td>
            <td class="px-4 py-2 font-medium"><?= htmlspecialchars($m['nombre']) ?></td>
            <td class="px-4 py-2"><?= number_format($m['cantidad'], 3) ?></td>
            <td class="px-4 py-2"><?= number_format($m['costo_unitario'], 2) ?> $</td>
            <td class="px-4 py-2"><?= htmlspecialchars($m['proveedor'] ?? '—') ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($m['proyecto'] ?? '—') ?></td>

            <!-- 🟧 Acciones -->
            <td class="px-4 py-2 text-center flex justify-center gap-2">
              <!-- Botón Editar -->
              <a href="index.php?page=materiales/form&id=<?= $m['id'] ?>" 
                 class="inline-flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-full shadow transition">
                ✏️ <span>Editar</span>
              </a>

              <!-- Botón Eliminar -->
              <a href="index.php?page=materiales/delete&id=<?= $m['id'] ?>" 
                 onclick="return confirm('¿Seguro que deseas eliminar este material?');"
                 class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-3 py-1 rounded-full shadow transition">
                🗑️ <span>Eliminar</span>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="text-center py-6 text-gray-500">No se encontraron materiales.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
