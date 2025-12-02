<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="p-8">
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
      🧱 <span>Gestión de Materiales</span>
    </h1>
    <div class="flex gap-3">
      <a href="index.php?page=materiales/form"
         class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 
                text-white px-5 py-2 rounded-xl shadow-md transition duration-200 flex items-center gap-2 text-sm font-semibold">
        ➕ Nuevo Material
      </a>
      <a href="index.php?page=materiales/reporte"
         class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 
                text-white px-5 py-2 rounded-xl shadow-md transition duration-200 flex items-center gap-2 text-sm font-semibold">
        📤 Generar Reporte
      </a>
    </div>
  </div>

  <div class="mb-6 relative w-full md:w-1/2">
    <input type="text" id="buscador" 
           placeholder="🔍 Buscar material, proyecto o proveedor..." 
           class="w-full p-3 pl-4 border border-gray-300 rounded-xl shadow-sm 
                  focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none transition duration-150 bg-white">
  </div>

  <div id="tabla-materiales" class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
    <?php include __DIR__ . '/tabla.php'; ?>
  </div>
</div>

<script>
document.getElementById('buscador').addEventListener('keyup', function() {
  const query = this.value;
  fetch(`index.php?page=materiales/buscar&q=${encodeURIComponent(query)}`)
    .then(res => res.text())
    .then(html => {
      document.getElementById('tabla-materiales').innerHTML = html;
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
