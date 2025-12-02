<!-- app/views/dashboard.php -->
<div class="space-y-10 animate-fadeIn">

  <!-- ENCABEZADO -->
  <div class="flex justify-between items-center">
    <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-yellow-500 flex items-center gap-3">
      🏗️ Panel General
    </h1>
  </div>

  <!-- TARJETAS DE RESUMEN -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
    <?php
      $cards = [
        ['Clientes', $counts['clientes'], 'from-orange-400 via-orange-500 to-orange-600', '👥'],
        ['Proyectos', $counts['proyectos'], 'from-blue-500 via-blue-600 to-blue-700', '🏗️'],
        ['Empleados', $counts['empleados'], 'from-violet-500 via-violet-600 to-violet-700', '👷'],
        ['Materiales', $counts['materiales'], 'from-emerald-400 via-emerald-500 to-emerald-600', '🧱'],
        ['Tareas', $counts['tareas'], 'from-cyan-500 via-cyan-600 to-cyan-700', '📋'],
        ['Costos', $counts['costos'], 'from-amber-500 via-orange-500 to-yellow-500', '💰'],
        ['Pagos', $counts['pagos'], 'from-green-500 via-green-600 to-green-700', '💵'],
      ];
      foreach ($cards as $card): 
    ?>
      <div class="group p-6 bg-gradient-to-br <?= $card[2] ?> text-white rounded-2xl shadow-xl transform hover:-translate-y-2 hover:shadow-2xl transition duration-300 relative overflow-hidden">
        <div class="absolute inset-0 opacity-20 bg-gradient-to-br from-white/10 to-transparent"></div>
        <div class="flex justify-between items-center relative z-10">
          <div>
            <h2 class="text-sm opacity-90"><?= $card[0] ?></h2>
            <p class="text-4xl md:text-5xl font-extrabold"><?= intval($card[1]) ?></p>
          </div>
          <div class="text-5xl opacity-80 group-hover:scale-110 transition"><?= $card[3] ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- ACCESOS RÁPIDOS -->
  <div class="rounded-2xl shadow-xl border border-gray-100 bg-white/70 backdrop-blur-md p-8">
    <h3 class="text-2xl font-semibold text-gray-700 mb-6 flex items-center gap-2">
      ⚡ Accesos rápidos
    </h3>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
      <?php 
        $accesos = [
          ['clientes','👥','Clientes','from-orange-50 to-white','text-orange-700'],
          ['proyectos','🏗️','Proyectos','from-blue-50 to-white','text-blue-700'],
          ['empleados','👷','Empleados','from-violet-50 to-white','text-violet-700'],
          ['materiales','🧱','Materiales','from-emerald-50 to-white','text-emerald-700'],
          ['tareas','📋','Tareas','from-cyan-50 to-white','text-cyan-700'],
          ['costos','💰','Costos','from-amber-50 to-white','text-amber-700'],
          ['pagos','💵','Pagos','from-green-50 to-white','text-green-700'],
        ];
        foreach ($accesos as $a): 
      ?>
      <a href="<?= BASE_URL ?>/index.php?page=<?= $a[0] ?>" 
         class="group p-5 bg-gradient-to-r <?= $a[3] ?> hover:from-gray-100 hover:to-white rounded-xl border <?= $a[4] ?> font-medium text-center shadow-sm transition transform hover:-translate-y-1 hover:shadow-md">
         <span class="text-3xl block mb-2 group-hover:scale-110 transition"><?= $a[1] ?></span>
         <?= $a[2] ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ANIMACIONES -->
<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn { animation: fadeIn 0.6s ease-in-out; }
</style>
