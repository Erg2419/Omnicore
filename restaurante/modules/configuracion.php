<?php
$db = new Database();
$connection = $db->getConnection();

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: index.php');
    exit();
}

// Obtener todos los empleados
$empleados = [];
try {
    $query = "SELECT * FROM empleados ORDER BY nombre";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="page-header animate-fade-in">
    <h1 class="page-title">Configuración del Sistema</h1>
    <p class="page-subtitle">Gestión de usuarios y configuración avanzada</p>
</div>

<div class="config-container">
    <div class="config-section">
        <h2 class="section-title">Gestión de Usuarios</h2>

        <div class="action-buttons">
            <button class="btn btn-primary" id="agregarUsuarioBtn">
                <i class="fas fa-user-plus"></i>
                Agregar Usuario
            </button>
        </div>

        <div class="usuarios-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Puesto</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empleados as $empleado): ?>
                    <tr>
                        <td><?php echo $empleado['id_empleado']; ?></td>
                        <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($empleado['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($empleado['email']); ?></td>
                        <td><?php echo ucfirst($empleado['puesto']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $empleado['estado']; ?>">
                                <?php echo ucfirst($empleado['estado']); ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <button class="btn-action edit" onclick="editarUsuario(<?php echo $empleado['id_empleado']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($empleado['id_empleado'] != $_SESSION['user_id']): ?>
                            <button class="btn-action danger" onclick="eliminarUsuario(<?php echo $empleado['id_empleado']; ?>, '<?php echo htmlspecialchars($empleado['nombre']); ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para agregar/editar usuario -->
<div class="modal" id="usuarioModal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="modalTitle">Agregar Usuario</h3>
            <button class="close-modal">&times;</button>
        </div>

        <div class="modal-form-container">
            <form method="POST" action="includes/actions.php" class="modal-form" id="usuarioForm">
                <input type="hidden" name="action" id="usuarioAction" value="agregar_usuario">
                <input type="hidden" name="id_empleado" id="usuarioId">

                <div class="form-section">
                    <div class="form-section-title">Información del Usuario</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" id="usuarioNombre" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="usuario" id="usuarioUsuario" class="form-input" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="usuarioEmail" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="telefono" id="usuarioTelefono" class="form-input">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Puesto</label>
                            <select name="puesto" id="usuarioPuesto" class="form-input" required>
                                <option value="mesero">Mesero</option>
                                <option value="cocinero">Cocinero</option>
                                <option value="cajero">Cajero</option>
                                <option value="administrador">Administrador</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Estado</label>
                            <select name="estado" id="usuarioEstado" class="form-input" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="passwordGroup">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="contrasena" id="usuarioContrasena" class="form-input">
                        <small class="form-help">Deja vacío para mantener la contraseña actual (solo al editar)</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span id="submitText">Guardar Usuario</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.config-container {
    max-width: 1200px;
    margin: 0 auto;
}

.config-section {
    background: rgba(17, 17, 17, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 2rem;
}

.section-title {
    color: var(--color-white);
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    font-weight: 600;
    border-bottom: 2px solid rgba(102, 126, 234, 0.2);
    padding-bottom: 0.5rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.usuarios-table {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(17, 17, 17, 0.8);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.data-table th,
.data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.data-table th {
    background: rgba(102, 126, 234, 0.1);
    color: var(--color-white);
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    color: var(--text-light);
}

.data-table tbody tr:hover {
    background: rgba(102, 126, 234, 0.05);
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.activo {
    background: rgba(39, 174, 96, 0.1);
    color: #27ae60;
}

.status-badge.inactivo {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.actions-cell {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: var(--border-radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: #d4d4d4;
}

.btn-action.edit {
    background: var(--primary-gradient);
}

.btn-action.danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.btn-action:hover {
    transform: scale(1.1);
}

@media (max-width: 768px) {
    .config-section {
        padding: 1rem;
    }

    .data-table th,
    .data-table td {
        padding: 0.5rem;
        font-size: 0.875rem;
    }

    .actions-cell {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const usuarioModal = document.getElementById('usuarioModal');
    const agregarUsuarioBtn = document.getElementById('agregarUsuarioBtn');
    const closeModalBtns = document.querySelectorAll('.close-modal');

    // Agregar usuario modal
    agregarUsuarioBtn.addEventListener('click', function() {
        resetUsuarioForm();
        document.getElementById('modalTitle').textContent = 'Agregar Usuario';
        document.getElementById('usuarioAction').value = 'agregar_usuario';
        document.getElementById('submitText').textContent = 'Crear Usuario';
        document.getElementById('passwordGroup').style.display = 'block';
        document.getElementById('usuarioContrasena').required = true;
        usuarioModal.classList.add('active');
    });

    // Close modals
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            usuarioModal.classList.remove('active');
        });
    });

    // Close modals when clicking outside
    usuarioModal.addEventListener('click', function(e) {
        if (e.target === usuarioModal) {
            usuarioModal.classList.remove('active');
        }
    });

    // Handle form submission
    document.getElementById('usuarioForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitUsuarioForm();
    });
});

function resetUsuarioForm() {
    document.getElementById('usuarioForm').reset();
    document.getElementById('usuarioId').value = '';
    document.getElementById('usuarioNombre').focus();
}

async function editarUsuario(idUsuario) {
    try {
        const response = await fetch(`api/usuarios.php/${idUsuario}`);
        const result = await response.json();

        if (result.success) {
            const usuario = result.data;

            document.getElementById('modalTitle').textContent = 'Editar Usuario';
            document.getElementById('usuarioAction').value = 'editar_usuario';
            document.getElementById('submitText').textContent = 'Actualizar Usuario';
            document.getElementById('passwordGroup').style.display = 'block';
            document.getElementById('usuarioContrasena').required = false;

            document.getElementById('usuarioId').value = usuario.id_empleado;
            document.getElementById('usuarioNombre').value = usuario.nombre;
            document.getElementById('usuarioUsuario').value = usuario.usuario;
            document.getElementById('usuarioEmail').value = usuario.email;
            document.getElementById('usuarioTelefono').value = usuario.telefono || '';
            document.getElementById('usuarioPuesto').value = usuario.puesto;
            document.getElementById('usuarioEstado').value = usuario.estado;

            document.getElementById('usuarioModal').classList.add('active');
        } else {
            showNotification('Error al cargar datos del usuario', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar datos del usuario', 'error');
    }
}

async function eliminarUsuario(idUsuario, nombreUsuario) {
    if (!confirm(`¿Está seguro de eliminar al usuario "${nombreUsuario}"? Esta acción no se puede deshacer.`)) {
        return;
    }

    try {
        const response = await fetch(`api/usuarios.php/${idUsuario}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Usuario eliminado exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al eliminar usuario: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al eliminar el usuario', 'error');
    }
}

async function submitUsuarioForm() {
    const form = document.getElementById('usuarioForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    try {
        const response = await fetch('includes/actions.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Usuario guardado exitosamente', 'success');
            document.getElementById('usuarioModal').classList.remove('active');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al guardar usuario: ' + result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> <span id="submitText">Guardar Usuario</span>';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al guardar usuario', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> <span id="submitText">Guardar Usuario</span>';
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