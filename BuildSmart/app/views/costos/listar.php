<?php
require_once __DIR__ . '/../../../core/helpers.php';
require_login();

global $pdo;

// --- Si hay búsqueda, filtramos ---
$busqueda = $_GET['q'] ?? '';
$sql = "SELECT c.*, p.nombre AS proyecto 
        FROM costos c 
        LEFT JOIN proyectos p ON p.id = c.proyecto_id
        WHERE p.nombre LIKE :busqueda
        ORDER BY c.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':busqueda' => "%$busqueda%"]);
$costos = $stmt->fetchAll();
?>

<div class="space-y-6 animate-fadeIn">
  <!-- Cabecera -->
  <div class="flex justify-between items-center flex-wrap gap-3">
    <h1 class="text-3xl font-bold text-[#f97316] flex items-center gap-2">
      💰 Gestión de Costos
    </h1>

    <div class="flex gap-2 items-center">
      <input 
        type="text" 
        id="buscarProyecto"
        placeholder="Buscar proyecto..."
        class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 outline-none w-56"
      >

      <a href="<?= BASE_URL ?>/index.php?page=costos/form" 
         class="bg-[#f97316] text-white px-3 py-2 rounded-lg hover:bg-orange-600 transition">
         ➕ Registrar Costo
      </a>

      <button id="btnReporte"
         class="bg-green-500 text-white px-3 py-2 rounded-lg hover:bg-green-600 transition">
         📤 Generar Reporte
      </button>
    </div>
  </div>

  <!-- Contenedor de la tabla -->
  <div id="tablaCostos" class="bg-white shadow rounded-xl overflow-hidden border border-gray-100">
    <table class="w-full text-sm text-left text-gray-700">
      <thead class="bg-orange-50 text-[#f97316] uppercase text-xs">
        <tr>
          <th class="px-4 py-3 text-center">🟠</th>
          <th class="px-4 py-3">Proyecto</th>
          <th class="px-4 py-3">Materiales</th>
          <th class="px-4 py-3">Mano de Obra</th>
          <th class="px-4 py-3">Otros Gastos</th>
          <th class="px-4 py-3">Total</th>
        </tr>
      </thead>
      <tbody id="cuerpoTabla">
        <?php if (count($costos)): ?>
          <?php foreach ($costos as $c): ?>
            <tr class="border-b hover:bg-orange-50 transition">
              <td class="px-4 py-2 text-center">
                <input type="radio" name="selectCosto" value="<?= $c['id'] ?>">
              </td>
              <td class="px-4 py-2"><?= htmlspecialchars($c['proyecto']) ?></td>
              <td class="px-4 py-2"><?= number_format($c['costo_materiales'], 2, ',', '.') ?></td>
              <td class="px-4 py-2"><?= number_format($c['costo_mano_obra'], 2, ',', '.') ?></td>
              <td class="px-4 py-2"><?= number_format($c['otros_gastos'], 2, ',', '.') ?></td>
              <td class="px-4 py-2 font-semibold text-gray-800">RD$ <?= number_format($c['total'], 2, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center py-6 text-gray-500">No hay costos registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- JS para búsqueda en vivo y reporte -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('buscarProyecto');
  const cuerpoTabla = document.getElementById('cuerpoTabla');

  let timer = null;
  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      const valor = input.value.trim();

      // Buscar sin recargar todo
      fetch(`<?= BASE_URL ?>/index.php?page=costos&ajax=1&q=${encodeURIComponent(valor)}`)
        .then(res => res.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const nuevaTabla = doc.querySelector('#cuerpoTabla');
          if (nuevaTabla) cuerpoTabla.innerHTML = nuevaTabla.innerHTML;
        })
        .catch(err => console.error(err));
    }, 400); // espera 400 ms después de escribir
  });

  // Generar reporte (uno o todos)
  document.getElementById('btnReporte').addEventListener('click', () => {
    const seleccionado = document.querySelector('input[name="selectCosto"]:checked');
    const id = seleccionado ? seleccionado.value : '';
    const query = input.value.trim();
    let url = '<?= BASE_URL ?>/index.php?page=costos/reporte';

    if (id) url += `&id=${id}`;
    else if (query) url += `&q=${encodeURIComponent(query)}`;

    window.location.href = url;
  });
});
</script>

<style>
@keyframes fadeIn { from {opacity: 0; transform: translateY(10px);} to {opacity: 1; transform: translateY(0);} }
.animate-fadeIn { animation: fadeIn .6s ease-in-out; }
</style>
