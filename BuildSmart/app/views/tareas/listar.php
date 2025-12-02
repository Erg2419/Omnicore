<div class="p-8">
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold flex items-center gap-2 text-orange-500">
      ✅ <span>Gestión de Tareas</span>
    </h1>

    <div class="flex gap-3">
      <!-- Nueva Tarea -->
      <a href="index.php?page=tareas/form" 
         class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-xl shadow-md transition duration-200 flex items-center gap-2 text-sm font-semibold">
        ➕ Nueva Tarea
      </a>

      <!-- Generar Reporte -->
      <a id="btnReporte"
         href="index.php?page=tareas/reporte"
         class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-5 py-2 rounded-xl shadow-md transition duration-200 flex items-center gap-2 text-sm font-semibold">
        📤 Generar Reporte
      </a>
    </div>
  </div>

  <!-- Buscador -->
  <div class="mb-6 flex justify-between items-center flex-wrap gap-3">
    <input type="text" id="buscador" 
           placeholder="Buscar tarea por nombre o proyecto..." 
           class="w-full md:w-1/2 p-3 pl-4 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none transition duration-150 bg-white">
  </div>

  <!-- Tabla de tareas -->
  <div id="tabla-tareas" class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition-all duration-300">
      <?php include __DIR__ . '/tabla.php'; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('buscador');
    const tabla = document.getElementById('tabla-tareas');
    const btnReporte = document.getElementById('btnReporte');

    // Buscar tareas en tiempo real
    buscador.addEventListener('keyup', () => {
        const query = buscador.value.trim();

        // Si el campo está vacío, recargar todas las tareas
        const url = query
            ? `index.php?page=tareas/buscar&q=${encodeURIComponent(query)}`
            : `index.php?page=tareas/buscar`; // mostrará todas las tareas

        fetch(url)
            .then(res => res.text())
            .then(html => { 
                tabla.innerHTML = html; 
            })
            .catch(err => console.error('Error cargando tareas:', err));
    });

    // Generar reporte con filtro
    btnReporte.addEventListener('click', e => {
        e.preventDefault();
        const query = buscador.value.trim();
        const url = query 
            ? `index.php?page=tareas/reporte&q=${encodeURIComponent(query)}`
            : `index.php?page=tareas/reporte`;
        window.location.href = url;
    });
});
</script>
