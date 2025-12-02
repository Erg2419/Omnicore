<?php
session_start();

// Si ya está logueado, redirigir al sistema
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'includes/database.php';

// Crear instancia de la base de datos
$database = new Database();
$conn = $database->getConnection();

$error = '';
$success = '';

// Procesar Login
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $stmt = $conn->prepare("SELECT id_empleado, nombre, contrasena, puesto FROM empleados WHERE email = :email AND estado = 'activo'");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Comparación directa de contraseña (texto plano)
            if ($password === $user['contrasena']) {
                $_SESSION['user_id'] = $user['id_empleado'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_role'] = $user['puesto'];
                header('Location: index.php');
                exit();
            } else {
                $error = 'Contraseña incorrecta';
            }
        } else {
            $error = 'Usuario no encontrado o inactivo';
        }
    } catch(PDOException $e) {
        $error = 'Error en el sistema: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Sistema Restaurante</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a0000 0%, #000000 50%, #330000 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .particle {
            position: absolute;
            background: rgba(220, 20, 60, 0.3);
            border-radius: 50%;
            pointer-events: none;
            animation: float 15s infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0) scale(1);
                opacity: 0;
            }
            10% { opacity: 0.6; }
            90% { opacity: 0.6; }
            50% {
                transform: translateY(-80vh) translateX(20px) scale(1.2);
            }
        }

        .container {
            position: relative;
            width: 450px;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 50px 40px;
            box-shadow: 0 20px 80px rgba(220, 20, 60, 0.4),
                        0 0 0 1px rgba(220, 20, 60, 0.2);
            animation: slideIn 0.6s ease-out;
            z-index: 10;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            animation: glow 2s ease-in-out infinite;
        }

        .logo h1 {
            color: #dc143c;
            font-size: 32px;
            font-weight: bold;
            text-shadow: 0 0 20px rgba(220, 20, 60, 0.6),
                         0 0 40px rgba(220, 20, 60, 0.4);
            letter-spacing: 2px;
        }

        @keyframes glow {
            0%, 100% {
                text-shadow: 0 0 20px rgba(220, 20, 60, 0.6),
                             0 0 40px rgba(220, 20, 60, 0.4);
            }
            50% {
                text-shadow: 0 0 30px rgba(220, 20, 60, 0.8),
                             0 0 60px rgba(220, 20, 60, 0.6);
            }
        }

        .logo p {
            color: #fff;
            margin-top: 10px;
            font-size: 14px;
            opacity: 0.8;
        }

        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .tab-btn {
            flex: 1;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(220, 20, 60, 0.3);
            color: #fff;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .tab-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(220, 20, 60, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .tab-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #dc143c 0%, #8b0000 100%);
            border-color: #dc143c;
            box-shadow: 0 0 20px rgba(220, 20, 60, 0.5);
        }

        .tab-btn span {
            position: relative;
            z-index: 1;
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(220, 20, 60, 0.3);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #dc143c;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 20px rgba(220, 20, 60, 0.3);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #dc143c 0%, #8b0000 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 25px rgba(220, 20, 60, 0.4);
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(220, 20, 60, 0.6);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: rgba(220, 20, 60, 0.2);
            border: 1px solid rgba(220, 20, 60, 0.5);
            color: #ff6b6b;
        }

        .alert-success {
            background: rgba(60, 220, 60, 0.2);
            border: 1px solid rgba(60, 220, 60, 0.5);
            color: #6bff6b;
        }

        .glow-effect {
            position: absolute;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(220, 20, 60, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        @media (max-width: 500px) {
            .container {
                width: 90%;
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <script>
        for (let i = 0; i < 15; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.width = Math.random() * 60 + 20 + 'px';
            particle.style.height = particle.style.width;
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 15 + 's';
            particle.style.animationDuration = Math.random() * 10 + 10 + 's';
            document.body.appendChild(particle);
        }
    </script>

    <div class="container">
        <div class="logo">
            <h1>🍽️ FullMenuRD</h1>
            <p>Sistema de Gestión</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="tab-buttons">
            <button class="tab-btn active">
                <span>Iniciar Sesión</span>
            </button>
        </div>

        <div id="login-form" class="form-container active">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="tu@email.com" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" name="login" class="submit-btn">
                    Ingresar al Sistema
                </button>
            </form>
        </div>

    </div>

    <div class="glow-effect" id="glowEffect"></div>

    <script>
        const glowEffect = document.getElementById('glowEffect');
        document.addEventListener('mousemove', (e) => {
            glowEffect.style.left = e.clientX - 100 + 'px';
            glowEffect.style.top = e.clientY - 100 + 'px';
        });

        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>