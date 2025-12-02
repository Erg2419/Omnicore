<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-4xl mx-auto bg-white shadow-lg rounded-2xl p-8 mt-10">
  <h2 class="text-2xl font-bold text-gray-800 mb-6">
    <?= isset($material) ? '✏️ Editar Material' : '➕ Nuevo Material' ?>
  </h2>

  <form action="index.php?page=materiales/guardar" method="POST" class="grid grid-cols-2 gap-6">
    <?php if (isset($material['id'])): ?>
      <input type="hidden" name="id" value="<?= htmlspecialchars($material['id']) ?>">
    <?php endif; ?>

    <div>
      <label class="block text-gray-600 font-semibold mb-2">Nombre</label>
      <input type="text" name="nombre" value="<?= htmlspecialchars($material['nombre'] ?? '') ?>" 
             required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
    </div>

    <div>
      <label class="block text-gray-600 font-semibold mb-2">Cantidad</label>
      <input type="number" name="cantidad" value="<?= htmlspecialchars($material['cantidad'] ?? '') ?>" 
             step="0.01" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
    </div>

    <div>
      <label class="block text-gray-600 font-semibold mb-2">Costo Unitario ($)</label>
      <input type="number" name="costo_unitario" value="<?= htmlspecialchars($material['costo_unitario'] ?? '') ?>" 
             step="0.01" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
    </div>

    <div>
      <label class="block text-gray-600 font-semibold mb-2">Proveedor</label>
      <select name="proveedor_id" class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
        <option value="">Seleccionar proveedor...</option>
        <?php foreach ($proveedores as $prov): ?>
          <option value="<?= $prov['id'] ?>" <?= isset($material['proveedor_id']) && $material['proveedor_id'] == $prov['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($prov['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="block text-gray-600 font-semibold mb-2">Proyecto</label>
      <select name="proyecto_id" class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
        <option value="">Sin proyecto asignado</option>
        <?php foreach ($proyectos as $pr): ?>
          <option value="<?= $pr['id'] ?>" <?= isset($material['proyecto_id']) && $material['proyecto_id'] == $pr['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($pr['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-span-2 flex justify-end gap-4 mt-6">
      <a href="index.php?page=materiales" class="bg-gray-400 hover:bg-gray-500 text-white px-5 py-2 rounded-xl transition duration-150">Cancelar</a>
      <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-xl transition duration-150">Guardar</button>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
