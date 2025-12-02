<?php
require_once __DIR__ . '/../../../core/helpers.php';
require_login();

global $pdo;
$proyectos = $pdo->query("SELECT id, nombre FROM proyectos ORDER BY nombre ASC")->fetchAll();
?>

<div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-md space-y-6 animate-fadeIn">
  <h1 class="text-2xl font-bold text-[#f97316]">📊 Registrar Costos</h1>

  <form action="<?= BASE_URL ?>/index.php?page=costos/guardar" method="POST" class="space-y-4">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <div>
      <label class="block text-gray-600 font-medium">Proyecto</label>
      <select name="proyecto_id" required class="w-full p-2 border rounded-md focus:ring-2 focus:ring-orange-400">
        <option value="">Seleccione un proyecto</option>
        <?php foreach ($proyectos as $p): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-gray-600 font-medium">Costo Materiales</label>
        <input type="text" name="costo_materiales" class="w-full p-2 border rounded-md text-right" placeholder="Ej: 8,000.00" required>
      </div>
      <div>
        <label class="block text-gray-600 font-medium">Mano de Obra</label>
        <input type="text" name="costo_mano_obra" class="w-full p-2 border rounded-md text-right" placeholder="Ej: 10,000.00" required>
      </div>
      <div>
        <label class="block text-gray-600 font-medium">Otros Gastos</label>
        <input type="text" name="otros_gastos" class="w-full p-2 border rounded-md text-right" placeholder="Ej: 2,000.00" required>
      </div>
    </div>

    <div class="pt-4 flex justify-end gap-3">
      <a href="<?= BASE_URL ?>/index.php?page=costos/listar" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition">Cancelar</a>
      <button type="submit" class="px-4 py-2 bg-[#f97316] text-white rounded-md hover:bg-orange-600 transition">💾 Guardar</button>
    </div>
  </form>
</div>

<style>
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fadeIn { animation: fadeIn .6s ease-in-out; }
</style>
