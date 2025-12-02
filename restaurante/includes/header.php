<?php
// Procesar logout si se solicita
if (isset($_GET['logout'])) {
    session_start();
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
    header('Location: Login.php');
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener el nombre del usuario de la sesión
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Administrador';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Restaurante - Gourmet Experience</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
/* Estilos para el menú de usuario y logout */
.nav-user {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.nav-user:hover {
    background: rgba(220, 20, 60, 0.1);
}


.user-name {
    white-space: nowrap;
    font-size: 14px;
    font-weight: 500;
}

.user-dropdown {
    position: absolute;
    top: 120%;
    right: 0;
    background: rgba(0, 0, 0, 0.95);
    backdrop-filter: blur(20px);
    border: 2px solid rgba(220, 20, 60, 0.3);
    border-radius: 15px;
    padding: 10px;
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    box-shadow: 0 10px 40px rgba(220, 20, 60, 0.3);
    z-index: 1000;
}

.nav-user:hover .user-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    color: #fff;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    font-size: 14px;
}

.dropdown-item:hover {
    background: linear-gradient(135deg, #dc143c 0%, #8b0000 100%);
    transform: translateX(5px);
}

.dropdown-item i {
    width: 20px;
    text-align: center;
    font-size: 16px;
}

.dropdown-divider {
    height: 1px;
    background: rgba(220, 20, 60, 0.3);
    margin: 8px 0;
}

.logout-btn {
    color: #ff6b6b !important;
}

.logout-btn:hover {
    background: linear-gradient(135deg, #dc143c 0%, #ff0000 100%) !important;
    color: #fff !important;
}


/* Theme toggle button */
.nav-actions {
    display: flex;
    align-items: center;
    margin-right: 12px;
}

.theme-toggle {
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(220, 20, 60, 0.3);
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #fff;
    flex-shrink: 0;
}

.theme-toggle:hover {
    background: rgba(220, 20, 60, 0.2);
    border-color: #dc143c;
    transform: scale(1.1);
}

.theme-toggle i {
    font-size: 16px;
}

/* Light theme variables */
:root {
    --bg-primary: #1a1a1a;
    --bg-secondary: #2a2a2a;
    --bg-tertiary: #333;
    --text-primary: #ffffff;
    --text-secondary: #cccccc;
    --text-muted: #888;
    --border-color: rgba(255, 255, 255, 0.1);
    --accent-color: #dc143c;
    --card-bg: rgba(26, 26, 26, 0.9);
    --input-bg: rgba(255, 255, 255, 0.05);
    --hover-bg: rgba(220, 20, 60, 0.1);
}

/* Light theme */
body.light-theme {
    --bg-primary: #f8f9fa;
    --bg-secondary: #ffffff;
    --bg-tertiary: #f1f3f4;
    --text-primary: #202124;
    --text-secondary: #5f6368;
    --text-muted: #80868b;
    --border-color: rgba(0, 0, 0, 0.12);
    --accent-color: #1a73e8;
    --card-bg: rgba(255, 255, 255, 0.95);
    --input-bg: rgba(255, 255, 255, 0.8);
    --hover-bg: rgba(26, 115, 232, 0.1);
}

body.light-theme .navbar {
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

body.light-theme .nav-link {
    color: var(--text-secondary);
}

body.light-theme .nav-link:hover,
body.light-theme .nav-link.active {
    background: var(--hover-bg);
    color: var(--accent-color);
}

body.light-theme .user-dropdown {
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
}

body.light-theme .dropdown-item {
    color: var(--text-primary);
}

body.light-theme .dropdown-item:hover {
    background: var(--hover-bg);
}

/* Responsive navigation */
@media (max-width: 1200px) {
    .nav-menu .nav-text {
        display: none;
    }
    
    .nav-link {
        padding: 8px 12px !important;
        min-width: auto;
        justify-content: center;
    }
    
    .user-name {
        display: none;
    }
}

@media (max-width: 768px) {
    .nav-container {
        padding: 0 12px;
    }
    
    .nav-logo .logo-text {
        font-size: 16px;
    }
    
    .nav-user {
        padding: 6px 8px;
    }
    
    .nav-actions {
        margin-right: 8px;
    }
    
    .theme-toggle {
        width: 32px;
        height: 32px;
    }
    
    .user-avatar {
        width: 28px;
        height: 28px;
    }
}

@media (max-width: 480px) {
    .nav-logo .logo-text {
        font-size: 14px;
    }
    
    .theme-toggle {
        width: 28px;
        height: 28px;
    }
    
    .theme-toggle i {
        font-size: 14px;
    }
}
</style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-utensils logo-icon"></i>
                <span class="logo-text">Full<span class="logo-accent">Menu</span></span>
            </div>
            
            <div class="nav-menu">
                <a href="index.php" class="nav-link" data-page="dashboard" title="Dashboard">
                    <i class="fas fa-chart-pie"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="index.php?page=mesas" class="nav-link" data-page="mesas" title="Mesas">
                    <i class="fas fa-chair"></i>
                    <span class="nav-text">Mesas</span>
                </a>
                <a href="index.php?page=ordenes" class="nav-link" data-page="ordenes" title="Órdenes">
                    <i class="fas fa-receipt"></i>
                    <span class="nav-text">Órdenes</span>
                </a>
                <a href="index.php?page=menu" class="nav-link" data-page="menu" title="Menú">
                    <i class="fas fa-book-open"></i>
                    <span class="nav-text">Menú</span>
                </a>
                <a href="index.php?page=reservas" class="nav-link" data-page="reservas" title="Reservas">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="nav-text">Reservas</span>
                </a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'administrador'): ?>
                <a href="index.php?page=configuracion" class="nav-link" data-page="configuracion" title="Configuración">
                    <i class="fas fa-cogs"></i>
                    <span class="nav-text">Config</span>
                </a>
                <?php endif; ?>
            </div>

            <div class="nav-actions">
                <button class="theme-toggle" id="themeToggle" title="Cambiar tema">
                    <i class="fas fa-moon"></i>
                </button>
            </div>

            <div class="nav-user">
                <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                <i class="fas fa-chevron-down" style="font-size: 12px; opacity: 0.7;"></i>

                <div class="user-dropdown">
                    <a href="index.php?page=perfil" class="dropdown-item">
                        <i class="fas fa-user-circle"></i>
                        <span>Mi Perfil</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="?logout=1" class="dropdown-item logout-btn" onclick="return confirm('¿Estás seguro que deseas cerrar sesión?');">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="floating-notification" id="notification">
        <i class="fas fa-check"></i>
        <span id="notification-text"></span>
    </div>

    <script>
        // Theme toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const themeIcon = themeToggle.querySelector('i');

            // Check for saved theme preference or default to dark
            const currentTheme = localStorage.getItem('theme') || 'dark';
            if (currentTheme === 'light') {
                body.classList.add('light-theme');
                themeIcon.className = 'fas fa-sun';
            } else {
                themeIcon.className = 'fas fa-moon';
            }

            // Toggle theme on button click
            themeToggle.addEventListener('click', function() {
                if (body.classList.contains('light-theme')) {
                    body.classList.remove('light-theme');
                    themeIcon.className = 'fas fa-moon';
                    localStorage.setItem('theme', 'dark');
                } else {
                    body.classList.add('light-theme');
                    themeIcon.className = 'fas fa-sun';
                    localStorage.setItem('theme', 'light');
                }
            });
        });
    </script>