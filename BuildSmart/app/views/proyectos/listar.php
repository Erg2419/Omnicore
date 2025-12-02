<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="p-8">
  <!-- Encabezado -->
  <div class="flex justify-between items-center mb-8 flex-wrap gap-3">
    <h1 class="text-3xl font-bold flex items-center gap-2 text-orange-500">
      🏗️ <span>Gestión de Proyectos</span>
    </h1>

    <div class="flex gap-3 flex-wrap">
      <a href="index.php?page=proyectos/form" 
         class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-xl shadow-md transition duration-200 flex items-center gap-2 text-sm font-semibold">
        ➕ Nuevo Proyecto
      </a>

      <a id="btnReporte"
         href="index.php?page=proyectos/reporte"
         class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-5 py-2 rounded-xl shadow-md transition duration-200 flex items-center gap-2 text-sm font-semibold">
        📤 Generar Reporte
      </a>
    </div>
  </div>

  <!-- Buscador -->
  <div class="mb-6 flex justify-between items-center flex-wrap gap-3">
    <input type="text" id="buscador" 
           placeholder="Buscar proyectos por nombre o estado..." 
           class="w-full md:w-1/2 p-3 pl-4 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none transition duration-150 bg-white">
  </div>

  <!-- Tabla de proyectos -->
  <div id="tabla-proyectos" class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition-all duration-300 mb-10">
    <?php include __DIR__ . '/tabla.php'; ?>
  </div>

  <!-- Diagrama de Gantt -->
  <h2 class="text-2xl font-bold text-orange-500 mb-4 flex items-center gap-2">
    📊 <span>Diagrama de Gantt de Proyectos</span>
  </h2>
  <div id="gantt" class="bg-white border border-gray-200 rounded-2xl shadow-md p-6 w-full" style="height:500px;">
    <p class="text-gray-500 text-center py-6">Cargando diagrama...</p>
  </div>
</div>

<!-- Frappe Gantt -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const buscador = document.getElementById('buscador');
  const tabla = document.getElementById('tabla-proyectos');
  const btnReporte = document.getElementById('btnReporte');

  // Buscar proyectos en tiempo real
  buscador.addEventListener('keyup', () => {
    const query = buscador.value.trim();
    fetch(`index.php?page=proyectos/buscar&q=${encodeURIComponent(query)}`)
      .then(res => res.text())
      .then(html => { tabla.innerHTML = html; })
      .catch(err => console.error('Error al buscar proyectos:', err));
  });

  // Generar reporte con filtro
  btnReporte.addEventListener('click', e => {
    e.preventDefault();
    const query = buscador.value.trim();
    const url = query 
      ? `index.php?page=proyectos/reporte&q=${encodeURIComponent(query)}`
      : `index.php?page=proyectos/reporte`;
    window.location.href = url;
  });

  // Cargar datos del Gantt con avances reales
  fetch('index.php?page=proyectos/ganttData')
    .then(res => res.json())
    .then(data => {
      if (!data || !Array.isArray(data) || data.length === 0) {
        document.getElementById('gantt').innerHTML = 
          '<p class="text-gray-500 text-center py-6">No hay proyectos para mostrar en el diagrama.</p>';
        return;
      }

      const tasks = data.map(p => ({
        id: p.id,
        name: p.name,
        start: p.start,
        end: p.end,
        progress: Number(p.progress), // ✅ avance real de la BD
        custom_class: p.custom_class
      }));

      new Gantt("#gantt", tasks, {
        view_mode: 'Month',
        bar_height: 28,
        padding: 30,
        language: 'es',
        custom_popup_html: task => `
          <div class="p-3 text-sm">
            <b>${task.name}</b><br>
            Estado: ${task.custom_class.replace(/-/g,' ')}<br>
            Progreso: ${task.progress}%
          </div>
        `
      });
    })
    .catch(err => {
      console.error('Error cargando datos del Gantt:', err);
      document.getElementById('gantt').innerHTML = 
        '<p class="text-gray-500 text-center py-6">Error al cargar el diagrama.</p>';
    });
});
</script>

<style>
/* Colores de barras por estado */
.bar.finalizado  { fill: #16a34a; }  /* Verde */
.bar.en-proceso  { fill: #f59e0b; }  /* Amarillo */
.bar.planificado { fill: #3b82f6; }  /* Azul */
.bar.cancelado   { fill: #ef4444; }  /* Rojo */
.bar.pendiente   { fill: #6b7280; }  /* Gris */
</style>
