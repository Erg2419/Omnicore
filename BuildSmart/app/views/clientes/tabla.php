<table class="w-full text-sm text-left border-collapse">
  <thead class="bg-orange-50 text-orange-700 uppercase font-semibold">
    <tr>
      <th class="p-3">ID</th>
      <th class="p-3">Nombre</th>
      <th class="p-3">Teléfono</th>
      <th class="p-3">Correo</th>
      <th class="p-3">Dirección</th>
      <th class="p-3 text-center">Acciones</th>
    </tr>
  </thead>

  <tbody>
    <?php if (!empty($clientes)): ?>
      <?php foreach ($clientes as $c): ?>
        <tr class="border-b border-gray-100 hover:bg-orange-50 transition">
          <td class="p-3"><?= $c['id'] ?></td>
          <td class="p-3 font-medium"><?= htmlspecialchars($c['nombre']) ?></td>
          <td class="p-3"><?= htmlspecialchars($c['telefono']) ?></td>
          <td class="p-3"><?= htmlspecialchars($c['correo']) ?></td>
          <td class="p-3"><?= htmlspecialchars($c['direccion']) ?></td>

          <td class="p-3 text-center flex justify-center gap-2">
            <!-- Botón Editar -->
            <a href="index.php?page=clientes/form&id=<?= $c['id'] ?>"
               class="inline-flex items-center gap-1 bg-blue-500 hover:bg-blue-600 
                      text-white text-xs font-semibold px-3 py-1 rounded-full shadow transition">
              ✏️ <span>Editar</span>
            </a>

            <!-- Botón Eliminar -->
            <a href="index.php?page=clientes/delete&id=<?= $c['id'] ?>"
               onclick="return confirm('¿Seguro que deseas eliminar este cliente?');"
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
          No se encontraron clientes.
        </td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
