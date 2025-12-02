<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Iniciar sesión — BuildSmart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: { brand: { 500: '#f97316', 600: '#ea580c' } }
        }
      }
    }
  </script>
</head>
<body class="min-h-screen bg-cover bg-center" style="background-image:url('https://cercademi.net/wp-content/uploads/2022/08/empresas-de-construccion.jpg')">
  <div class="flex items-center justify-center min-h-screen bg-black/40">
    <div class="bg-white/80 backdrop-blur-lg border border-white/30 rounded-2xl shadow-2xl w-full max-w-md p-8">
      <h2 class="text-3xl font-bold text-center text-brand-600 mb-3">🚧BuildSmart👷‍♀️</h2>
      <p class="text-center text-gray-600 mb-6">Planifica, controla y entrega tus proyectos</p>

      <?php if (!empty($error)): ?>
        <div class="mb-4 text-sm text-red-600 bg-red-100 border border-red-300 p-3 rounded"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= BASE_URL ?>/index.php?page=login" class="space-y-4">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? csrf_token()) ?>">

        <div>
          <label class="block text-sm font-semibold">Correo electrónico🦺</label>
          <input required name="email" type="email" placeholder="tucorreo@dominio.com"
            class="w-full px-4 py-2 rounded border focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>

        <div>
          <label class="block text-sm font-semibold">Contraseña💡</label>
          <input required name="password" type="password" placeholder="••••••••"
            class="w-full px-4 py-2 rounded border focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>

        <button type="submit"
          class="w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold py-2 rounded-lg shadow transition">
          ⛏️Iniciar sesión🧱
        </button>
      </form>

      <!-- Enlace a registro -->
      <p class="text-center mt-4 text-gray-600">
        ¿No tienes cuenta? 
        <a href="<?= BASE_URL ?>/index.php?page=register" class="text-brand-500 font-semibold hover:underline">
          Crea una aquí
        </a>
      </p>

    </div>
  </div>
</body>
</html>
