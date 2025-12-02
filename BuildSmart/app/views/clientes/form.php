<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="p-10">
  <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-xl border border-gray-100 p-10">
    
    <!-- Título -->
    <div class="flex items-center justify-between mb-10 border-b pb-4">
      <h1 class="text-3xl font-extrabold text-gray-800 flex items-center gap-3">
        <?= isset($cliente) ? '✏️ Editar Cliente' : '➕ Nuevo Cliente' ?>
      </h1>
      <a href="index.php?page=clientes" 
         class="text-gray-500 hover:text-orange-600 transition text-sm font-medium flex items-center gap-1">
        ← Volver al listado
      </a>
    </div>

    <!-- Formulario -->
    <form method="POST" action="index.php?page=clientes/guardar" class="space-y-8">
      <input type="hidden" name="id" value="<?= htmlspecialchars($cliente['id'] ?? '') ?>">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Nombre -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Cliente</label>
          <input type="text" name="nombre" required
                 value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>"
                 placeholder="Ej: Constructora Nova"
                 class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-400 outline-none shadow-sm transition">
        </div>

        <!-- Teléfono -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
          <input type="text" name="telefono"
                 value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>"
                 placeholder="Ej: 809-555-5555"
                 class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-400 outline-none shadow-sm transition">
        </div>

        <!-- Correo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Correo Electrónico</label>
          <input type="email" name="correo"
                 value="<?= htmlspecialchars($cliente['correo'] ?? '') ?>"
                 placeholder="cliente@empresa.com"
                 class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-400 outline-none shadow-sm transition">
        </div>

        <!-- Dirección -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección</label>
          <input type="text" name="direccion"
                 value="<?= htmlspecialchars($cliente['direccion'] ?? '') ?>"
                 placeholder="Ej: Calle Principal #45, Santo Domingo"
                 class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-400 outline-none shadow-sm transition">
        </div>
      </div>

      <!-- Botones -->
      <div class="flex justify-end gap-4 pt-6 border-t mt-10">
        <a href="index.php?page=clientes"
           class="px-6 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold shadow-sm transition">
          Cancelar
        </a>
        <button type="submit"
                class="px-6 py-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold shadow-md transition transform hover:-translate-y-0.5">
          💾 <?= isset($cliente) ? 'Guardar Cambios' : 'Registrar Cliente' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
