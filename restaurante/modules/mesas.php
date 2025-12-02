<?php
$db = new Database();
$connection = $db->getConnection();

// Obtener mesas de la base de datos
$mesas = [];
try {
    $query = "SELECT * FROM mesas ORDER BY numero_mesa";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="page-header animate-fade-in">
    <h1 class="page-title">Gestión de Mesas</h1>
    <p class="page-subtitle">Control y estado de todas las mesas del restaurante</p>
</div>

<div class="mesas-controls">
    <div class="controls-header">
        <div class="filters">
            <button class="filter-btn active" data-filter="all">Todas</button>
            <button class="filter-btn" data-filter="disponible">Disponibles</button>
            <button class="filter-btn" data-filter="ocupada">Ocupadas</button>
            <button class="filter-btn" data-filter="reservada">Reservadas</button>
        </div>
        <button class="btn btn-primary" id="addMesaBtn">
            <i class="fas fa-plus"></i>
            Agregar Mesa
        </button>
    </div>
    
    <div class="status-legends">
        <div class="legend-item">
            <div class="legend-color disponible"></div>
            <span>Disponible</span>
        </div>
        <div class="legend-item">
            <div class="legend-color ocupada"></div>
            <span>Ocupada</span>
        </div>
        <div class="legend-item">
            <div class="legend-color reservada"></div>
            <span>Reservada</span>
        </div>
        <div class="legend-item">
            <div class="legend-color mantenimiento"></div>
            <span>Mantenimiento</span>
        </div>
    </div>
</div>

<div class="mesas-grid stagger-animate">
    <?php foreach ($mesas as $mesa): ?>
        <div class="mesa-card <?php echo $mesa['estado']; ?>" data-estado="<?php echo $mesa['estado']; ?>">
            <div class="mesa-icon">
                <i class="fas fa-<?php echo $mesa['capacidad'] > 4 ? 'users' : 'user-friends'; ?>"></i>
            </div>
            <div class="mesa-numero">Mesa <?php echo htmlspecialchars($mesa['numero_mesa']); ?></div>
            <div class="mesa-capacidad"><?php echo $mesa['capacidad']; ?> personas</div>
            <div class="mesa-ubicacion"><?php echo htmlspecialchars($mesa['ubicacion']); ?></div>
            <div class="mesa-estado"><?php echo ucfirst($mesa['estado']); ?></div>
            
            <div class="mesa-actions">
                <select class="estado-select" data-mesa-id="<?php echo $mesa['id_mesa']; ?>" onchange="cambiarEstadoMesa(this)">
                    <option value="disponible" <?php echo $mesa['estado'] == 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                    <option value="ocupada" <?php echo $mesa['estado'] == 'ocupada' ? 'selected' : ''; ?>>Ocupada</option>
                    <option value="reservada" <?php echo $mesa['estado'] == 'reservada' ? 'selected' : ''; ?>>Reservada</option>
                    <option value="mantenimiento" <?php echo $mesa['estado'] == 'mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                </select>
            </div>
            
            <div class="mesa-buttons">
                <button class="btn-action edit" onclick="editarMesa(<?php echo $mesa['id_mesa']; ?>)">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-action info" onclick="verDetallesMesa(<?php echo $mesa['id_mesa']; ?>)">
                    <i class="fas fa-info"></i>
                </button>
                <button class="btn-action delete" onclick="eliminarMesa(<?php echo $mesa['id_mesa']; ?>)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal para agregar mesa -->
<div class="modal" id="addMesaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Agregar Nueva Mesa</h3>
            <button class="close-modal">&times;</button>
        </div>
        <form class="modal-form">
            <div class="form-group">
                <label class="form-label">Número de Mesa</label>
                <input type="text" name="numero_mesa" class="form-input" required 
                       placeholder="Ej: M07, M08...">
            </div>
            
            <div class="form-group">
                <label class="form-label">Capacidad</label>
                <input type="number" name="capacidad" class="form-input" required 
                       min="1" max="12" value="4">
            </div>
            
            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <select name="ubicacion" class="form-input" required>
                    <option value="Terraza">Terraza</option>
                    <option value="Sala Principal">Sala Principal</option>
                    <option value="Barra">Barra</option>
                    <option value="Sala VIP">Sala VIP</option>
                    <option value="Jardín">Jardín</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar Mesa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para editar mesa -->
<div class="modal" id="editMesaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Mesa</h3>
            <button class="close-modal">&times;</button>
        </div>
        <form class="modal-form" id="editMesaForm">
            <div class="form-group">
                <label class="form-label">Número de Mesa</label>
                <input type="text" name="numero_mesa" class="form-input" required 
                       placeholder="Ej: M07, M08...">
            </div>
            
            <div class="form-group">
                <label class="form-label">Capacidad</label>
                <input type="number" name="capacidad" class="form-input" required 
                       min="1" max="12">
            </div>
            
            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <select name="ubicacion" class="form-input" required>
                    <option value="Terraza">Terraza</option>
                    <option value="Sala Principal">Sala Principal</option>
                    <option value="Barra">Barra</option>
                    <option value="Sala VIP">Sala VIP</option>
                    <option value="Jardín">Jardín</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.mesas-controls {
    background: rgba(17, 17, 17, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.controls-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.filters {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 0.5rem 1rem;
    border: 2px solid rgba(102, 126, 234, 0.2);
    background: rgba(255, 255, 255, 0.8);
    border-radius: var(--border-radius-sm);
    cursor: pointer;
    transition: var(--transition);
    font-weight: 500;
}

.filter-btn.active,
.filter-btn:hover {
    background: var(--primary-gradient);
    color: #d4d4d4;
    border-color: transparent;
    transform: translateY(-2px);
}

.status-legends {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 4px;
}

.legend-color.disponible { background: var(--success-gradient); }
.legend-color.ocupada { background: var(--warning-gradient); }
.legend-color.reservada { background: var(--accent-gradient); }
.legend-color.mantenimiento { background: var(--text-light); }

.mesa-ubicacion {
    color: var(--text-light);
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.mesa-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.estado-select {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid rgba(102, 126, 234, 0.2);
    border-radius: var(--border-radius-sm);
    background: rgba(255, 255, 255, 0.8);
    font-size: 0.875rem;
    cursor: pointer;
    transition: var(--transition);
}

.estado-select:focus {
    outline: none;
    border-color: #667eea;
}

.action-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: var(--border-radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: white;
}

.action-btn.info {
    background: var(--accent-gradient);
}

.action-btn:hover {
    transform: scale(1.1);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
    animation: fadeIn 0.3s ease-out;
}

.modal-content {
    background: rgba(198,40,40,0.03);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-strong);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.9);
    animation: slideInUp 0.3s ease-out forwards;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-light);
    transition: var(--transition);
}

.close-modal:hover {
    color: var(--text-dark);
    transform: scale(1.1);
}

.modal-form {
    padding: 1.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.btn-secondary {
    background: rgba(0, 0, 0, 0.1);
    color: var(--text-dark);
}

.btn-secondary:hover {
    background: rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
}

/* Botones de acción en tarjetas */
.mesa-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    margin-top: 1rem;
}

.btn-action {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4d4d4;
    font-size: 0.9rem;
    transition: var(--transition);
    box-shadow: var(--shadow-soft);
}

.btn-action.edit { 
    background: var(--primary-gradient);
}

.btn-action.info { 
    background: var(--accent-gradient);
}

.btn-action.delete { 
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
}

.btn-action:hover {
    transform: scale(1.15);
    box-shadow: var(--shadow-medium);
}

@media (max-width: 768px) {
    .controls-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filters {
        justify-content: center;
    }
    
    .status-legends {
        justify-content: center;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const addProductoBtn = document.getElementById('addProductoBtn');
    const addCategoriaBtn = document.getElementById('addCategoriaBtn');
    const addFirstProductoBtn = document.getElementById('addFirstProductoBtn');
    const productoModal = document.getElementById('productoModal');
    const categoriaModal = document.getElementById('categoriaModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    
    // Producto modal
    [addProductoBtn, addFirstProductoBtn].forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function() {
                resetProductoForm();
                productoModal.classList.add('active');
            });
        }
    });
    
    // Categoría modal
    addCategoriaBtn.addEventListener('click', function() {
        resetCategoriaForm();
        categoriaModal.classList.add('active');
    });
    
    // Close modals
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            productoModal.classList.remove('active');
            categoriaModal.classList.remove('active');
        });
    });
    
    // Close modals when clicking outside
    [productoModal, categoriaModal].forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
    
    // Search functionality - CORREGIDA
    const searchInput = document.getElementById('searchProductos');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const productoCards = document.querySelectorAll('.producto-card');
            const categoriaSections = document.querySelectorAll('.categoria-section');
            
            let hasVisibleProducts = false;
            
            categoriaSections.forEach(section => {
                let hasVisibleInSection = false;
                const productosInSection = section.querySelectorAll('.producto-card');
                
                productosInSection.forEach(card => {
                    const productName = card.getAttribute('data-nombre');
                    const productDescription = card.querySelector('.producto-desc').textContent.toLowerCase();
                    
                    if (productName.includes(searchTerm) || productDescription.includes(searchTerm)) {
                        card.style.display = 'block';
                        hasVisibleInSection = true;
                        hasVisibleProducts = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Show/hide entire category section based on visible products
                section.style.display = hasVisibleInSection ? 'block' : 'none';
            });
            
            // Show empty state if no products found
            const emptyState = document.querySelector('.empty-state');
            if (emptyState) {
                emptyState.style.display = hasVisibleProducts ? 'none' : 'block';
            }
        });
    }
    
    // Form submissions
    document.getElementById('productoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitProductoForm();
    });
    
    document.getElementById('categoriaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitCategoriaForm();
    });
});

function resetProductoForm() {
    document.getElementById('productoModalTitle').textContent = 'Agregar Producto';
    document.getElementById('formAction').value = 'agregar_producto';
    document.getElementById('id_producto').value = '';
    document.getElementById('productoForm').reset();
    document.getElementById('imagenActual').style.display = 'none';
    document.getElementById('submitProductoText').textContent = 'Guardar Producto';
    document.getElementById('productoImagen').required = false;
    
    // Reset file input
    const fileInput = document.getElementById('productoImagen');
    fileInput.value = '';
}

function resetCategoriaForm() {
    document.getElementById('categoriaModalTitle').textContent = 'Nueva Categoría';
    document.getElementById('categoriaAction').value = 'agregar_categoria';
    document.getElementById('id_categoria').value = '';
    document.getElementById('categoriaForm').reset();
    document.getElementById('submitCategoriaText').textContent = 'Guardar Categoría';
}

async function editarProducto(idProducto) {
    try {
        console.log('Editando producto:', idProducto);
        
        // Usar FormData para enviar la solicitud
        const formData = new FormData();
        formData.append('action', 'obtener_producto');
        formData.append('id', idProducto);
        
        const response = await fetch('includes/actions.php', {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        console.log('Respuesta obtener producto:', resultado);
        
        if (resultado.success) {
            const producto = resultado.data;
            
            document.getElementById('productoModalTitle').textContent = 'Editar Producto';
            document.getElementById('formAction').value = 'editar_producto';
            document.getElementById('id_producto').value = producto.id_producto;
            document.getElementById('productoNombre').value = producto.nombre;
            document.getElementById('productoDescripcion').value = producto.descripcion || '';
            document.getElementById('productoPrecio').value = parseFloat(producto.precio).toFixed(2);
            document.getElementById('productoCategoria').value = producto.id_categoria;
            document.getElementById('productoTiempo').value = producto.tiempo_preparacion || 15;
            document.getElementById('productoEstado').value = producto.estado;
            document.getElementById('submitProductoText').textContent = 'Guardar Cambios';
            document.getElementById('productoImagen').required = false;
            
            // Mostrar imagen actual si existe
            if (producto.imagen) {
                document.getElementById('imagenActualSrc').src = producto.imagen;
                document.getElementById('imagenActual').style.display = 'block';
            } else {
                document.getElementById('imagenActual').style.display = 'none';
            }
            
            document.getElementById('productoModal').classList.add('active');
        } else {
            showNotification('Error al cargar el producto: ' + resultado.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar el producto: ' + error.message, 'error');
    }
}

async function cambiarEstadoProducto(idProducto, estadoActual) {
    const nuevoEstado = estadoActual === 'disponible' ? 'no_disponible' : 'disponible';
    const mensaje = nuevoEstado === 'disponible' ? 
        '¿Estás seguro de marcar este producto como disponible?' : 
        '¿Estás seguro de marcar este producto como no disponible?';
    
    if (confirm(mensaje)) {
        try {
            console.log('Cambiando estado del producto:', idProducto, 'a:', nuevoEstado);
            
            const formData = new FormData();
            formData.append('action', 'cambiar_estado_producto');
            formData.append('id_producto', idProducto);
            formData.append('estado', nuevoEstado);
            
            const response = await fetch('includes/actions.php', {
                method: 'POST',
                body: formData
            });
            
            const resultado = await response.json();
            console.log('Respuesta cambiar estado:', resultado);
            
            if (resultado.success) {
                showNotification(resultado.message, 'success');
                // Recargar después de un breve delay para ver la notificación
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(resultado.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al cambiar el estado del producto: ' + error.message, 'error');
        }
    }
}

async function eliminarProducto(idProducto) {
    if (confirm('¿Estás seguro de eliminar este producto? Esta acción no se puede deshacer.')) {
        try {
            console.log('Eliminando producto:', idProducto);
            
            const formData = new FormData();
            formData.append('action', 'eliminar_producto');
            formData.append('id_producto', idProducto);
            
            const response = await fetch('includes/actions.php', {
                method: 'POST',
                body: formData
            });
            
            const resultado = await response.json();
            console.log('Respuesta eliminar producto:', resultado);
            
            if (resultado.success) {
                showNotification(resultado.message, 'success');
                // Recargar después de un breve delay para ver la notificación
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(resultado.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al eliminar el producto: ' + error.message, 'error');
        }
    }
}

async function submitProductoForm() {
    const form = document.getElementById('productoForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitProductoBtn');
    
    // Validar campos requeridos
    const nombre = formData.get('nombre');
    const precio = formData.get('precio');
    const id_categoria = formData.get('id_categoria');
    
    if (!nombre || !precio || !id_categoria) {
        showNotification('Por favor complete todos los campos requeridos', 'error');
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    try {
        console.log('Enviando formulario de producto...');
        
        const response = await fetch('includes/actions.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        console.log('Respuesta guardar producto:', result);
        
        if (result.success) {
            showNotification(result.message, 'success');
            // Cerrar modal y recargar
            document.getElementById('productoModal').classList.remove('active');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> ' + document.getElementById('submitProductoText').textContent;
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al guardar el producto: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> ' + document.getElementById('submitProductoText').textContent;
    }
}

async function submitCategoriaForm() {
    const form = document.getElementById('categoriaForm');
    const formData = new FormData(form);
    const submitBtn = document.querySelector('#categoriaForm button[type="submit"]');
    
    // Validar campos requeridos
    const nombre = formData.get('nombre');
    
    if (!nombre) {
        showNotification('El nombre de la categoría es requerido', 'error');
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    try {
        console.log('Enviando formulario de categoría...');
        
        const response = await fetch('includes/actions.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        console.log('Respuesta guardar categoría:', result);
        
        if (result.success) {
            showNotification(result.message, 'success');
            // Cerrar modal y recargar
            document.getElementById('categoriaModal').classList.remove('active');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> ' + document.getElementById('submitCategoriaText').textContent;
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al guardar la categoría: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> ' + document.getElementById('submitCategoriaText').textContent;
    }
}

// Función de notificación mejorada
function showNotification(message, type = 'info') {
    // Si existe el sistema de notificaciones del restaurante, usarlo
    if (window.restaurantApp && typeof window.restaurantApp.showNotification === 'function') {
        window.restaurantApp.showNotification(message, type);
    } else {
        // Crear notificación básica
        createBasicNotification(message, type);
    }
}

// Función para crear notificaciones básicas si no existe el sistema
function createBasicNotification(message, type) {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `basic-notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;
    
    // Estilos básicos para la notificación
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
        color: white;
        padding: 15px 20px;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    // Agregar al documento
    document.body.appendChild(notification);
    
    // Configurar cierre automático
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.onclick = () => {
        notification.remove();
    };
    
    // Cerrar automáticamente después de 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Agregar estilos CSS para la animación de notificación
if (!document.querySelector('#notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .basic-notification .notification-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .basic-notification .notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            margin-left: 10px;
        }
        
        .basic-notification .notification-close:hover {
            opacity: 0.8;
        }
    `;
    document.head.appendChild(style);
}
</script>