<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="p-8">
  <h1 class="text-3xl font-bold text-gray-800 mb-6">
    <?= isset($proyecto) && $proyecto ? '✏️ Editar Proyecto' : '➕ Nuevo Proyecto' ?>
  </h1>

  <form action="index.php?page=proyectos/guardar" method="post" class="bg-white p-8 rounded-2xl shadow-md space-y-6">
    <input type="hidden" name="id" value="<?= htmlspecialchars($proyecto['id'] ?? '') ?>">

    <div class="grid grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-semibold mb-1">Nombre del Proyecto</label>
        <input type="text" name="nombre" required
               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-400"
               value="<?= htmlspecialchars($proyecto['nombre'] ?? '') ?>">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Cliente</label>
        <select name="cliente_id" required
                class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-400">
          <option value="">-- Seleccione --</option>
          <?php foreach ($clientes as $c): ?>
          <option value="<?= $c['id'] ?>" <?= isset($proyecto['cliente_id']) && $proyecto['cliente_id'] == $c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nombre']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Ubicación</label>
        <input type="text" name="ubicacion"
               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-400"
               value="<?= htmlspecialchars($proyecto['ubicacion'] ?? '') ?>">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Estado</label>
        <select name="estado"
                class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-400">
          <option value="planificado" <?= ($proyecto['estado'] ?? '') == 'planificado' ? 'selected' : '' ?>>Planificado</option>
          <option value="en_ejecucion" <?= ($proyecto['estado'] ?? '') == 'en_ejecucion' ? 'selected' : '' ?>>En ejecución</option>
          <option value="finalizado" <?= ($proyecto['estado'] ?? '') == 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
          <option value="cancelado" <?= ($proyecto['estado'] ?? '') == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Fecha Inicio</label>
        <input type="date" name="fecha_inicio"
               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-400"
               value="<?= htmlspecialchars($proyecto['fecha_inicio'] ?? '') ?>">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Fecha Fin</label>
        <input type="date" name="fecha_fin"
               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-400"
               value="<?= htmlspecialchars($proyecto['fecha_fin'] ?? '') ?>">
      </div>

      <!-- NUEVO CAMPO: Avance -->
      <div>
        <label class="block text-sm font-semibold mb-1">Avance (%)</label>
        <input type="number" name="avance" min="0" max="100" step="1"
               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-400"
               value="<?= htmlspecialchars($proyecto['avance'] ?? 0) ?>">
      </div>
    </div>

    <div>
      <label class="block text-sm font-semibold mb-1">Descripción</label>
      <textarea name="descripcion"
                class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-400"><?= htmlspecialchars($proyecto['descripcion'] ?? '') ?></textarea>
    </div>

    <div class="flex justify-end gap-4 mt-6">
      <a href="index.php?page=proyectos"
         class="px-5 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 font-semibold">Cancelar</a>
      <button type="submit"
              class="px-5 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold">
        💾 Guardar
      </button>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
