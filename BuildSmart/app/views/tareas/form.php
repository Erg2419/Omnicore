<div class="max-w-4xl mx-auto bg-white shadow-lg rounded-2xl p-8 border border-gray-100 mt-8">
  <h2 class="text-2xl font-bold mb-6 text-orange-500 flex items-center gap-2">
    <?= isset($tarea) ? '✏️ Editar Tarea' : '➕ Nueva Tarea' ?>
  </h2>

  <form action="index.php?page=tareas/guardar" method="POST" class="space-y-6">
    <!-- CSRF Token -->
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

    <!-- ID solo si estamos editando -->
    <?php if (isset($tarea)): ?>
      <input type="hidden" name="id" value="<?= $tarea['id'] ?>">
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Nombre -->
      <div>
        <label class="block text-gray-600 font-medium mb-1">Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($tarea['nombre'] ?? '') ?>" required
               class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-orange-400 outline-none">
      </div>

      <!-- Descripción -->
      <div>
        <label class="block text-gray-600 font-medium mb-1">Descripción:</label>
        <textarea name="descripcion" rows="1"
                  class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-orange-400 outline-none"><?= htmlspecialchars($tarea['descripcion'] ?? '') ?></textarea>
      </div>

      <!-- Proyecto -->
      <div>
        <label class="block text-gray-600 font-medium mb-1">Proyecto:</label>
        <select name="proyecto_id" required
                class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-orange-400 outline-none bg-white">
          <option value="">Seleccione un proyecto</option>
          <?php foreach ($proyectos as $p): ?>
            <option value="<?= $p['id'] ?>" <?= (isset($tarea['proyecto_id']) && $tarea['proyecto_id']==$p['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Estado -->
      <div>
        <label class="block text-gray-600 font-medium mb-1">Estado:</label>
        <select name="estado" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-orange-400 outline-none bg-white">
          <?php $estados = ['pendiente','en progreso','completada']; 
          foreach($estados as $e): ?>
            <option value="<?= $e ?>" <?= isset($tarea['estado']) && $tarea['estado']==$e ? 'selected' : '' ?>>
              <?= ucfirst($e) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Fecha Inicio -->
      <div>
        <label class="block text-gray-600 font-medium mb-1">Fecha Inicio:</label>
        <input type="date" name="fecha_inicio" value="<?= $tarea['fecha_inicio'] ?? '' ?>"
               class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-orange-400 outline-none">
      </div>

      <!-- Fecha Fin -->
      <div>
        <label class="block text-gray-600 font-medium mb-1">Fecha Fin:</label>
        <input type="date" name="fecha_fin" value="<?= $tarea['fecha_fin'] ?? '' ?>"
               class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-orange-400 outline-none">
      </div>

      <!-- Progreso -->
      <div>
        <label class="block text-gray-600 font-medium mb-1">Progreso (%):</label>
        <input type="number" name="progreso" value="<?= $tarea['progreso'] ?? 0 ?>" min="0" max="100"
               class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-orange-400 outline-none">
      </div>
    </div>

    <!-- Botones -->
    <div class="flex justify-end gap-4 mt-6">
      <a href="index.php?page=tareas" 
         class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl transition">⬅ Volver</a>
      <button type="submit" 
              class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl shadow-md transition">
        💾 Guardar
      </button>
    </div>
  </form>
</div>
