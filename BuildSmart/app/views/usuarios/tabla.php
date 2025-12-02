<table class="w-full border-collapse">
  <thead>
    <tr class="bg-orange-50 text-orange-600 text-left uppercase text-sm">
      <th class="p-3">ID</th>
      <th class="p-3">Nombre</th>
      <th class="p-3">Email</th>
      <th class="p-3">Rol</th>
      <th class="p-3 text-center">Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($usuarios)): ?>
      <?php foreach ($usuarios as $u): ?>
        <tr class="hover:bg-orange-50 transition">
          <td class="p-3 border-b border-gray-100"><?= $u['id'] ?></td>
          <td class="p-3 border-b border-gray-100 font-medium"><?= htmlspecialchars($u['nombre']) ?></td>
          <td class="p-3 border-b border-gray-100"><?= htmlspecialchars($u['email']) ?></td>
          <td class="p-3 border-b border-gray-100"><?= htmlspecialchars($u['rol']) ?></td>
          <td class="p-3 border-b border-gray-100 text-center flex justify-center gap-2">
            <a href="index.php?page=usuarios/form&id=<?= $u['id'] ?>" 
               class="px-3 py-1 rounded-md text-xs bg-blue-500 hover:bg-blue-600 text-white font-semibold transition">
              ✏️ Editar
            </a>
            <a href="index.php?page=usuarios/delete&id=<?= $u['id'] ?>" 
               class="px-3 py-1 rounded-md text-xs bg-red-500 hover:bg-red-600 text-white font-semibold transition"
               onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">
              🗑️ Eliminar
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="5" class="text-center py-6 text-gray-500">No se encontraron usuarios.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
