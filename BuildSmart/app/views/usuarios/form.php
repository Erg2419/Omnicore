<div class="max-w-4xl mx-auto bg-white shadow-lg rounded-2xl p-8 border border-gray-100 mt-8">
  <h2 class="text-2xl font-bold mb-6 text-orange-500 flex items-center gap-2">
    <?= isset($usuario) ? '✏️ Editar Usuario' : '➕ Nuevo Usuario' ?>
  </h2>

  <form action="index.php?page=usuarios/guardar" method="POST" class="space-y-6">
    <!-- 🔹 CSRF Token -->
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

    <!-- 🔹 ID solo si estamos editando -->
    <?php if (isset($usuario)): ?>
      <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Nombre -->
      <div>
        <label class="block text-gray-600 font-medium mb-1">Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required
               class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-orange-400 outline-none">
      </div>

      <!-- Email -->
      <div>
        <label class="block text-gray-600 font-medium mb-1">Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required
               class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-orange-400 outline-none">
      </div>

      <!-- Rol -->
      <div>
        <label class="block text-gray-600 font-medium mb-1">Rol:</label>
        <select name="rol" required
                class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-orange-400 outline-none bg-white">
          <option value="">Seleccione un rol</option>
          <option value="admin" <?= (isset($usuario['rol']) && $usuario['rol'] == 'admin') ? 'selected' : '' ?>>Administrador</option>
          <option value="empleado" <?= (isset($usuario['rol']) && $usuario['rol'] == 'empleado') ? 'selected' : '' ?>>Empleado</option>
          <option value="cliente" <?= (isset($usuario['rol']) && $usuario['rol'] == 'cliente') ? 'selected' : '' ?>>Cliente</option>
        </select>
      </div>

      <!-- Contraseña -->
      <div>
        <label class="block text-gray-600 font-medium mb-1">Contraseña:</label>
        <input type="password" name="password" <?= isset($usuario) ? '' : 'required' ?>
               placeholder="<?= isset($usuario) ? 'Dejar en blanco para no cambiar' : '' ?>"
               class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-orange-400 outline-none">
      </div>
    </div>

    <!-- Botones -->
    <div class="flex justify-end gap-4 mt-6">
      <a href="index.php?page=usuarios" 
         class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl transition">⬅ Volver</a>
      <button type="submit" 
              class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl shadow-md transition">
        💾 Guardar
      </button>
    </div>
  </form>
</div>
