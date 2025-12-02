<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: Login.php');
    exit();
}

include 'includes/database.php';
include 'includes/header.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = ['dashboard', 'mesas', 'ordenes', 'menu', 'reservas', 'inventario', 'perfil', 'configuracion'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}
?>

<main class="main-content">
    <?php
    // Include the requested page
    $page_file = "modules/$page.php";
    if (file_exists($page_file)) {
        include $page_file;
    } else {
        include 'modules/dashboard.php';
    }
    ?>
</main>

<script src="js/main.js"></script>
<script src="js/animations.js"></script>

</body>
</html>