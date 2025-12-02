<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white w-72 transition-all duration-300">

  <!-- BOTÓN DE COLAPSAR -->
  <div class="flex justify-end p-3 border-b">
    <button id="collapseSidebar" class="text-gray-700 hover:text-orange-500 transition text-xl">
      ←
    </button>
  </div>

  <!-- LOGO -->
  <div onclick="window.location.href='<?= BASE_URL ?>/index.php?page=dashboard'" 
       class="logo-section flex items-center gap-3 p-4 cursor-pointer border-b">
    <span class="text-4xl">🏗️</span>
    <div class="flex flex-col">
      <h1 class="font-bold text-lg text-orange-500">BuildSmart</h1>
      <p class="text-xs text-gray-500">Gestión</p>
    </div>
  </div>

  <!-- MENÚ -->
  <nav class="flex-1 p-2 flex flex-col gap-1">
    <?php 
      $menu = [
        'usuarios' => '👤 Usuarios',
        'clientes' => '👥 Clientes',
        'empleados' => '👷 Empleados',
        'proveedores' => '🏢 Proveedores',
        'proyectos' => '🏗️ Proyectos',
        'materiales' => '🧱 Materiales',
        'tareas' => '📋 Tareas',
        'costos' => '💰 Costos',
        'pagos' => '💵 Pagos',
      ];
      foreach ($menu as $key => $label):
        $active = ($_GET['page'] ?? '') === $key ? 'active' : '';
    ?>
      <a href="<?= BASE_URL ?>/index.php?page=<?= $key ?>" 
         class="sidebar-link flex items-center gap-3 <?= $active ?>">
        <span class="menu-icon"><?= explode(" ", $label)[0] ?></span>
        <span class="menu-text"><?= substr($label, 2) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- Cerrar sesión -->
  <div class="p-3 border-t mt-auto">
    <a href="<?= BASE_URL ?>/index.php?page=logout"
       class="block w-full text-center py-2 rounded-lg font-semibold bg-gradient-to-r from-orange-500 to-orange-600 text-white text-sm shadow-md hover:shadow-lg">
       🚪 Cerrar
    </a>
  </div>
</aside>

<style>
  /* Ocultar texto cuando está colapsado */
  #sidebar.collapsed .menu-text {
    display: none;
  }

  /* Reducir ancho cuando está colapsado */
  #sidebar.collapsed {
    width: 4.5rem;
  }

  /* Centrar íconos cuando está colapsado */
  #sidebar.collapsed .menu-icon {
    display: flex;
    justify-content: center;
    width: 100%;
  }

  #sidebar.collapsed .logo-section div {
    display: none;
  }

  #collapseSidebar {
    transition: transform 0.3s;
  }

  #sidebar.collapsed #collapseSidebar {
    transform: rotate(180deg);
  }
</style>

<script>
const collapseBtn = document.getElementById('collapseSidebar');
const sidebar = document.getElementById('sidebar');

collapseBtn.addEventListener('click', () => {
  sidebar.classList.toggle('collapsed');
});
</script>
