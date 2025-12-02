<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="p-8">
  <h1 class="text-3xl font-bold text-orange-500 mb-6">
    <?= isset($proveedor) ? '✏️ Editar Proveedor' : '➕ Registrar Proveedor' ?>
  </h1>

  <form action="index.php?page=proveedores/guardar" method="POST" 
        class="bg-white shadow-md rounded-2xl p-8 space-y-6 border border-gray-100 max-w-3xl mx-auto">
    <input type="hidden" name="id" value="<?= $proveedor['id'] ?? '' ?>">

    <div class="grid grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-600">Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($proveedor['nombre'] ?? '') ?>" 
               required class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-600">Teléfono</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($proveedor['telefono'] ?? '') ?>" 
               class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-600">Correo</label>
        <input type="email" name="correo" value="<?= htmlspecialchars($proveedor['correo'] ?? '') ?>" 
               class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-600">Dirección</label>
        <input type="text" name="direccion" value="<?= htmlspecialchars($proveedor['direccion'] ?? '') ?>" 
               class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-orange-400 outline-none">
      </div>
    </div>

    <div class="flex justify-end gap-3 pt-4">
      <a href="index.php?page=proveedores"
         class="px-5 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 transition">⬅️ Cancelar</a>
      <button type="submit" 
              class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-xl shadow-md transition">
        💾 Guardar
      </button>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
