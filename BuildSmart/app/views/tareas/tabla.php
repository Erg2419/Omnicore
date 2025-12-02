<table class="w-full border-collapse">
  <thead>
    <tr class="bg-orange-50 text-orange-600 text-left uppercase text-sm">
      <th class="p-3">ID</th>
      <th class="p-3">Tarea</th>
      <th class="p-3">Proyecto</th>
      <th class="p-3">Inicio</th>
      <th class="p-3">Fin</th>
      <th class="p-3">Progreso</th>
      <th class="p-3 text-center">Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($tareas)): ?>
        <?php foreach ($tareas as $t): ?>
            <tr class="hover:bg-orange-50 transition">
                <td class="p-3 border-b border-gray-100"><?= $t['id'] ?></td>
                <td class="p-3 border-b border-gray-100 font-medium"><?= htmlspecialchars($t['nombre']) ?></td>
                <td class="p-3 border-b border-gray-100"><?= htmlspecialchars($t['proyecto'] ?? 'Sin proyecto') ?></td>
                <td class="p-3 border-b border-gray-100"><?= $t['fecha_inicio'] ?? '' ?></td>
                <td class="p-3 border-b border-gray-100"><?= $t['fecha_fin'] ?? '' ?></td>
                <td class="p-3 border-b border-gray-100"><?= isset($t['progreso']) ? $t['progreso'].'%' : '0%' ?></td>
                <td class="p-3 border-b border-gray-100 text-center flex justify-center gap-2">
                    <a href="index.php?page=tareas/form&id=<?= $t['id'] ?>" 
                       class="px-3 py-1 rounded-md text-xs bg-blue-500 hover:bg-blue-600 text-white font-semibold transition">
                        ✏️ Editar
                    </a>
                    <a href="index.php?page=tareas/delete&id=<?= $t['id'] ?>" 
                       class="px-3 py-1 rounded-md text-xs bg-red-500 hover:bg-red-600 text-white font-semibold transition"
                       onclick="return confirm('¿Seguro que deseas eliminar esta tarea?');">
                        🗑️ Eliminar
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="text-center py-6 text-gray-500">Escribe algo para buscar tareas...</td>
        </tr>
    <?php endif; ?>
  </tbody>
</table>
