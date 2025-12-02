<div class="overflow-x-auto w-full">
  <table class="min-w-full divide-y divide-gray-200 text-sm">
    <thead class="bg-orange-50 text-orange-700 font-semibold uppercase">
      <tr>
        <th class="px-4 py-2 text-left">ID</th>
        <th class="px-4 py-2 text-left">Cliente ID</th>
        <th class="px-4 py-2 text-left">Nombre</th>
        <th class="px-4 py-2 text-left">Ubicación</th>
        <th class="px-4 py-2 text-left">Fecha Inicio</th>
        <th class="px-4 py-2 text-left">Fecha Fin</th>
        <th class="px-4 py-2 text-left">Estado</th>
        <th class="px-4 py-2 text-left">Avance (%)</th>
        <th class="px-4 py-2 text-left">Descripción</th>
        <th class="px-4 py-2 text-center">Acciones</th>
      </tr>
    </thead>

    <tbody class="divide-y divide-gray-100">
      <?php foreach ($proyectos as $p): ?>
      <tr class="hover:bg-orange-50 transition">
        <td class="px-4 py-2"><?= htmlspecialchars($p['id']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($p['cliente_id']) ?></td>
        <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($p['nombre']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($p['ubicacion']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($p['fecha_inicio']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($p['fecha_fin']) ?></td>

        <td class="px-4 py-2">
          <?php $estado = strtolower(trim($p['estado'])); ?>
          <span class="px-3 py-1 rounded-full text-xs font-semibold
            <?php
              switch ($estado) {
                case 'finalizado': echo 'bg-green-100 text-green-700'; break;
                case 'en_ejecucion': echo 'bg-blue-100 text-blue-700'; break;
                case 'planificado': echo 'bg-yellow-100 text-yellow-700'; break;
                case 'cancelado': echo 'bg-red-100 text-red-700'; break;
                default: echo 'bg-gray-100 text-gray-400';
              }
            ?>">
            <?= ucfirst(str_replace('_', ' ', $p['estado'])) ?>
          </span>
        </td>

        <td class="px-4 py-2"><?= htmlspecialchars($p['avance']) ?>%</td>

        <td class="px-4 py-2 max-w-xs truncate" title="<?= htmlspecialchars($p['descripcion']) ?>">
          <?= htmlspecialchars(substr($p['descripcion'], 0, 60)) ?><?= strlen($p['descripcion']) > 60 ? '…' : '' ?>
        </td>

        <td class="px-4 py-2 text-center">
          <div class="flex justify-center gap-2">
            <a href="index.php?page=proyectos/form&id=<?= $p['id'] ?>" 
               class="inline-flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-full shadow transition">
              ✏️ <span>Editar</span>
            </a>

            <a href="index.php?page=proyectos/delete&id=<?= $p['id'] ?>" 
               onclick="return confirm('¿Eliminar este proyecto?')"
               class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-3 py-1 rounded-full shadow transition">
              🗑️ <span>Eliminar</span>
            </a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
