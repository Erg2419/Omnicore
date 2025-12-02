<?php
require_once BASE_PATH . 'core/helpers.php';
require_once BASE_PATH . 'app/db.php';

// Manejo del POST
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "⚠️ Petición inválida (CSRF).";
    } else {
        $nombre = trim($_POST['nombre'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $pass   = $_POST['password'] ?? '';
        $pass2  = $_POST['password2'] ?? '';

        if (!$nombre || !$email || !$pass || !$pass2) {
            $error = "Todos los campos son obligatorios.";
        } elseif ($pass !== $pass2) {
            $error = "Las contraseñas no coinciden.";
        } else {
            // Verifica si el email ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email=:email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $error = "El correo ya está registrado.";
            } else {
                // Insertar usuario
                $hashed = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, creado_en) 
                                       VALUES (:nombre, :email, :password, 'empleado', NOW())");
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':email'  => $email,
                    ':password' => $hashed
                ]);
                flash("✅ Cuenta creada correctamente. Ahora puedes iniciar sesión.");
                redirectTo('login');
            }
        }
    }
}

$csrf = csrf_token();
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Crear cuenta — BuildSmart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: { brand: { 500: '#f97316', 600: '#ea580c' } } } }
    }
  </script>
</head>
<body class="min-h-screen bg-cover bg-center" style="background-image:url('https://cercademi.net/wp-content/uploads/2022/08/empresas-de-construccion.jpg')">
  <div class="flex items-center justify-center min-h-screen bg-black/40">
    <div class="bg-white/80 backdrop-blur-lg border border-white/30 rounded-2xl shadow-2xl w-full max-w-md p-8">
      <h2 class="text-3xl font-bold text-center text-brand-600 mb-3">🚧 BuildSmart 👷‍♀️</h2>
      <p class="text-center text-gray-600 mb-6">Crea tu cuenta para ingresar al sistema</p>

      <?php if ($error): ?>
        <div class="mb-4 text-sm text-red-600 bg-red-100 border border-red-300 p-3 rounded"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= BASE_URL ?>/index.php?page=register" class="space-y-4">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

        <div>
          <label class="block text-sm font-semibold">Nombre completo</label>
          <input required name="nombre" type="text" placeholder="Tu nombre"
                 class="w-full px-4 py-2 rounded border focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>

        <div>
          <label class="block text-sm font-semibold">Correo electrónico</label>
          <input required name="email" type="email" placeholder="tucorreo@dominio.com"
                 class="w-full px-4 py-2 rounded border focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>

        <div>
          <label class="block text-sm font-semibold">Contraseña</label>
          <input required name="password" type="password" placeholder="••••••••"
                 class="w-full px-4 py-2 rounded border focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>

        <div>
          <label class="block text-sm font-semibold">Repetir contraseña</label>
          <input required name="password2" type="password" placeholder="••••••••"
                 class="w-full px-4 py-2 rounded border focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>

        <button type="submit"
                class="w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold py-2 rounded-lg shadow transition">
          🏗️ Crear cuenta
        </button>
      </form>

      <p class="mt-4 text-center text-gray-600 text-sm">
        ¿Ya tienes cuenta? 
        <a href="<?= BASE_URL ?>/index.php?page=login" class="text-brand-600 hover:underline">Inicia sesión</a>
      </p>
    </div>
  </div>
</body>
</html>
