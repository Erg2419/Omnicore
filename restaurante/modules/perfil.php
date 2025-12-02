<?php
$db = new Database();
$connection = $db->getConnection();

// Obtener información del usuario actual
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$empleado = null;

if ($user_id) {
    try {
        $query = "SELECT * FROM empleados WHERE id_empleado = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$user_id]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<div class="page-header animate-fade-in">
    <h1 class="page-title">Mi Perfil</h1>
    <p class="page-subtitle">Gestiona tu información personal y foto de perfil</p>
</div>

<div class="perfil-container">
    <div class="perfil-card">
        <div class="perfil-header">
            <div class="perfil-info">
                <h2><?php echo htmlspecialchars($empleado['nombre'] ?? 'Usuario'); ?></h2>
                <p class="perfil-puesto"><?php echo htmlspecialchars($empleado['puesto'] ?? 'Sin puesto'); ?></p>
                <p class="perfil-estado <?php echo $empleado['estado'] ?? 'activo'; ?>">
                    <?php echo ucfirst($empleado['estado'] ?? 'activo'); ?>
                </p>
            </div>
        </div>

        <form method="POST" action="includes/actions.php" class="perfil-form" id="perfilForm">
            <input type="hidden" name="action" value="actualizar_perfil">

            <div class="form-section">
                <div class="form-section-title">Información Personal</div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-input" value="<?php echo htmlspecialchars($empleado['nombre'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="usuario" class="form-input" value="<?php echo htmlspecialchars($empleado['usuario'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="telefono" class="form-input" value="<?php echo htmlspecialchars($empleado['telefono'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($empleado['email'] ?? ''); ?>">
                    </div>
                </div>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'administrador'): ?>
                <div class="form-group">
                    <label class="form-label">Puesto</label>
                    <select name="puesto" class="form-input" required>
                        <option value="mesero" <?php echo ($empleado['puesto'] ?? '') === 'mesero' ? 'selected' : ''; ?>>Mesero</option>
                        <option value="cocinero" <?php echo ($empleado['puesto'] ?? '') === 'cocinero' ? 'selected' : ''; ?>>Cocinero</option>
                        <option value="cajero" <?php echo ($empleado['puesto'] ?? '') === 'cajero' ? 'selected' : ''; ?>>Cajero</option>
                        <option value="administrador" <?php echo ($empleado['puesto'] ?? '') === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-section">
                <div class="form-section-title">Cambiar Contraseña</div>
                <p class="form-help">Deja estos campos vacíos si no deseas cambiar tu contraseña</p>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Contraseña Actual</label>
                        <input type="password" name="contrasena_actual" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" name="contrasena_nueva" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmar Nueva Contraseña</label>
                    <input type="password" name="contrasena_confirmar" class="form-input">
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="resetForm()">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.perfil-container {
    max-width: 800px;
    margin: 0 auto;
}

.perfil-card {
    background: rgba(17, 17, 17, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.perfil-header {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.perfil-info h2 {
    margin: 0 0 0.5rem 0;
    color: var(--color-white);
    font-size: 1.75rem;
}

.perfil-puesto {
    color: var(--primary-red);
    font-weight: 600;
    margin: 0 0 0.5rem 0;
}

.perfil-estado {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.perfil-estado.activo {
    background: rgba(39, 174, 96, 0.1);
    color: #27ae60;
}

.perfil-estado.inactivo {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.perfil-form {
    margin-top: 2rem;
}

.form-help {
    color: var(--text-light);
    font-size: 0.875rem;
    margin-bottom: 1rem;
    font-style: italic;
}

@media (max-width: 768px) {
    .perfil-header {
        text-align: center;
    }

    .perfil-info h2 {
        font-size: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar envío del formulario
    const perfilForm = document.getElementById('perfilForm');
    perfilForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitPerfil();
    });
});

function resetForm() {
    document.getElementById('perfilForm').reset();
}

async function submitPerfil() {
    const form = document.getElementById('perfilForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    // Validar contraseñas si se están cambiando
    const contrasenaNueva = formData.get('contrasena_nueva');
    const contrasenaConfirmar = formData.get('contrasena_confirmar');

    if (contrasenaNueva && contrasenaNueva !== contrasenaConfirmar) {
        showNotification('Las contraseñas nuevas no coinciden', 'error');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    try {
        const response = await fetch('includes/actions.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Perfil actualizado exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al actualizar perfil: ' + result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al actualizar perfil', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
    }
}

function showNotification(message, type = 'info') {
    if (window.restaurantApp && typeof window.restaurantApp.showNotification === 'function') {
        window.restaurantApp.showNotification(message, type);
    } else {
        alert(`${type.toUpperCase()}: ${message}`);
    }
}
</script>