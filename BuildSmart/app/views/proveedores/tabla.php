<table class="min-w-full text-sm text-left">
  <thead class="bg-orange-50 text-orange-700 uppercase font-semibold">
    <tr>
      <th class="px-4 py-2">ID</th>
      <th class="px-4 py-2">Nombre</th>
      <th class="px-4 py-2">Teléfono</th>
      <th class="px-4 py-2">Correo</th>
      <th class="px-4 py-2">Dirección</th>
      <th class="px-4 py-2 text-center">Acciones</th>
    </tr>
  </thead>

  <tbody>
    <?php if (!empty($proveedores)): ?>
      <?php foreach ($proveedores as $p): ?>
        <tr class="border-b hover:bg-orange-50 transition">
          <td class="px-4 py-2"><?= $p['id'] ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($p['nombre']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($p['telefono']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($p['correo']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($p['direccion']) ?></td>

          <td class="px-4 py-2 text-center flex justify-center gap-2">
            <!-- Botón Editar -->
            <a href="index.php?page=proveedores/form&id=<?= $p['id'] ?>"
               class="inline-flex items-center gap-1 bg-blue-500 hover:bg-blue-600 
                      text-white text-xs font-semibold px-3 py-1 rounded-full shadow transition">
              ✏️ <span>Editar</span>
            </a>

            <!-- Botón Eliminar -->
            <a href="index.php?page=proveedores/delete&id=<?= $p['id'] ?>"
               onclick="return confirm('¿Seguro que deseas eliminar este proveedor?');"
               class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 
                      text-white text-xs font-semibold px-3 py-1 rounded-full shadow transition">
              🗑️ <span>Eliminar</span>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="6" class="text-center py-6 text-gray-500">
          No hay proveedores registrados.
        </td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
