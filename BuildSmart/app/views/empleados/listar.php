<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="p-8">
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold flex items-center gap-2 text-orange-500">
      👷 <span>Gestión de Empleados</span>
    </h1>
    <div class="flex gap-3">
      <a href="index.php?page=empleados/form" 
         class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-xl shadow-md transition duration-200 flex items-center gap-2 text-sm font-semibold">
        ➕ Nuevo Empleado
      </a>

      <a id="btnReporte"
         href="index.php?page=empleados/reporte"
         class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-5 py-2 rounded-xl shadow-md transition duration-200 flex items-center gap-2 text-sm font-semibold">
        📤 Generar Reporte
      </a>
    </div>
  </div>

  <!-- 🔹 Buscador -->
  <div class="mb-6 flex justify-between items-center flex-wrap gap-3">
    <input type="text" id="buscador" 
           placeholder="Buscar empleado por nombre, puesto o teléfono..." 
           class="w-full md:w-1/2 p-3 pl-4 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none transition duration-150 bg-white">
  </div>

  <!-- 🔹 Tabla -->
  <div id="tabla-empleados" 
       class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition-all duration-300">
    <?php include __DIR__ . '/tabla.php'; ?>
  </div>
</div>

<script>
document.getElementById('buscador').addEventListener('keyup', function() {
  const query = this.value.trim();
  fetch(`index.php?page=empleados/buscar&q=${encodeURIComponent(query)}`)
    .then(res => res.text())
    .then(html => {
      document.getElementById('tabla-empleados').innerHTML = html;
    });
});

document.getElementById('btnReporte').addEventListener('click', function(e) {
  e.preventDefault();
  const query = document.getElementById('buscador').value.trim();
  const url = query 
    ? `index.php?page=empleados/reporte&q=${encodeURIComponent(query)}`
    : `index.php?page=empleados/reporte`;
  window.location.href = url;
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
