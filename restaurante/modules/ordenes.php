<?php
$db = new Database();
$connection = $db->getConnection();

// Obtener órdenes activas
$ordenes = [];
try {
    $query = "
        SELECT o.*, m.numero_mesa, c.nombre as cliente_nombre, e.nombre as empleado_nombre
        FROM ordenes o 
        LEFT JOIN mesas m ON o.id_mesa = m.id_mesa 
        LEFT JOIN clientes c ON o.id_cliente = c.id_cliente 
        LEFT JOIN empleados e ON o.id_empleado = e.id_empleado 
        ORDER BY o.fecha_orden DESC 
        LIMIT 20
    ";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Obtener productos para el modal
$productos = [];
try {
    $query = "SELECT p.*, c.nombre as nombre_categoria FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.estado = 'disponible'";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Obtener mesas disponibles
$mesas = [];
try {
    $query = "SELECT * FROM mesas WHERE estado = 'disponible'";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Obtener empleados activos
$empleados = [];
try {
    $query = "SELECT * FROM empleados WHERE estado = 'activo'";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Obtener categorías activas
$categorias = [];
try {
    $query = "SELECT * FROM categorias WHERE estado = 'activo' ORDER BY nombre";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="page-header animate-fade-in">
    <h1 class="page-title">Gestión de Órdenes</h1>
    <p class="page-subtitle">Control y seguimiento de todas las órdenes</p>
</div>

<div class="ordenes-header">
    <div class="header-actions">
        <button class="btn btn-primary" id="nuevaOrdenBtn">
            <i class="fas fa-plus"></i>
            Nueva Orden
        </button>
        <div class="stats-overview">
            <div class="stat-mini pendiente">
                <span class="stat-count"><?php echo count(array_filter($ordenes, fn($o) => $o['estado'] === 'pendiente')); ?></span>
                <span class="stat-label">Pendientes</span>
            </div>
            <div class="stat-mini preparacion">
                <span class="stat-count"><?php echo count(array_filter($ordenes, fn($o) => $o['estado'] === 'en_preparacion')); ?></span>
                <span class="stat-label">En Cocina</span>
            </div>
            <div class="stat-mini lista">
                <span class="stat-count"><?php echo count(array_filter($ordenes, fn($o) => $o['estado'] === 'lista')); ?></span>
                <span class="stat-label">Listas</span>
            </div>
        </div>
    </div>
</div>

<div class="ordenes-container">
    <div class="ordenes-grid">
        <?php foreach ($ordenes as $orden): ?>
            <div class="orden-card <?php echo $orden['estado']; ?> animate-slide-in-up">
                <div class="orden-header">
                    <div class="orden-info">
                        <h3 class="orden-id">Orden #<?php echo str_pad($orden['id_orden'], 4, '0', STR_PAD_LEFT); ?></h3>
                        <span class="orden-mesa">Mesa <?php echo htmlspecialchars($orden['numero_mesa'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="orden-estado-badge <?php echo $orden['estado']; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $orden['estado'])); ?>
                    </div>
                </div>
                
                <div class="orden-body">
                    <div class="orden-details">
                        <div class="detail-item">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($orden['cliente_nombre'] ?? 'Cliente General'); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-user-tie"></i>
                            <span><?php echo htmlspecialchars($orden['empleado_nombre']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo date('H:i', strtotime($orden['fecha_orden'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="orden-total">
                        <strong>RD$ <?php echo number_format($orden['total'], 2); ?></strong>
                    </div>
                </div>
                
                <div class="orden-actions">
                    <button class="btn-action view" onclick="verDetallesOrden(<?php echo $orden['id_orden']; ?>)">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-action edit" onclick="editarOrden(<?php echo $orden['id_orden']; ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <?php if ($orden['estado'] === 'pendiente'): ?>
                        <button class="btn-action warning" onclick="cambiarEstadoOrden(<?php echo $orden['id_orden']; ?>, 'en_preparacion')" title="Enviar a Cocina">
                            <i class="fas fa-utensils"></i>
                        </button>
                    <?php elseif ($orden['estado'] === 'en_preparacion'): ?>
                        <button class="btn-action info" onclick="cambiarEstadoOrden(<?php echo $orden['id_orden']; ?>, 'lista')" title="Marcar como Lista">
                            <i class="fas fa-check-circle"></i>
                        </button>
                    <?php elseif ($orden['estado'] === 'lista'): ?>
                        <button class="btn-action success" onclick="cambiarEstadoOrden(<?php echo $orden['id_orden']; ?>, 'pagada')" title="Confirmar Pago">
                            <i class="fas fa-dollar-sign"></i>
                        </button>
                    <?php endif; ?>
                    <?php if (in_array($orden['estado'], ['pendiente', 'cancelada', 'pagada'])): ?>
                        <button class="btn-action danger" onclick="eliminarOrden(<?php echo $orden['id_orden']; ?>)" title="Eliminar Orden">
                            <i class="fas fa-trash"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal para nueva orden -->
<div class="modal" id="nuevaOrdenModal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Crear Nueva Orden</h3>
            <button class="close-modal">&times;</button>
        </div>

        <div class="modal-form-container">
            <form method="POST" action="includes/actions.php" class="modal-form" id="nuevaOrdenForm">
                <input type="hidden" name="action" value="crear_orden">

                <div class="form-section">
                    <div class="form-section-title">Información del Cliente</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nombre del Cliente</label>
                            <input type="text" name="cliente_nombre" id="nueva-cliente-nombre" class="form-input" placeholder="Nombre del cliente">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Empleado</label>
                            <select name="id_empleado" id="nueva-id-empleado" class="form-input" required>
                                <option value="">Seleccionar Empleado</option>
                                <?php foreach ($empleados as $empleado): ?>
                                    <option value="<?php echo $empleado['id_empleado']; ?>">
                                        <?php echo htmlspecialchars($empleado['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Fecha del Pedido</label>
                            <input type="datetime-local" name="fecha_pedido" id="nueva-fecha-pedido" class="form-input" required
                                   value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tipo de Orden</label>
                            <select name="tipo_orden" class="form-input" required>
                                <option value="mesa">En Mesa</option>
                                <option value="domicilio">Domicilio</option>
                                <option value="recoger">Recoger</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mesa (opcional para órdenes en mesa)</label>
                        <select name="id_mesa" class="form-input" id="nueva-id-mesa">
                            <option value="">Seleccionar Mesa</option>
                            <?php foreach ($mesas as $mesa): ?>
                                <option value="<?php echo $mesa['id_mesa']; ?>">
                                    Mesa <?php echo htmlspecialchars($mesa['numero_mesa']); ?> - <?php echo $mesa['capacidad']; ?> personas
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="form-section">
                    <div class="form-section-title">Productos</div>

                    <div class="productos-seleccionados" id="productos-seleccionados-nueva">
                        <p class="no-productos">No hay productos seleccionados. <button type="button" class="btn-link" id="btn-agregar-productos-nueva">Agregar productos</button></p>
                    </div>

                    <div class="orden-summary">
                        <div class="summary-item">
                            <span>Subtotal:</span>
                            <span id="subtotal-nueva">RD$ 0.00</span>
                        </div>
                        <div class="summary-item total">
                            <span>Total:</span>
                            <span id="total-nueva">RD$ 0.00</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Crear Orden
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para seleccionar productos -->
<div class="modal" id="seleccionarProductosModal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Seleccionar Productos</h3>
            <button class="close-modal">&times;</button>
        </div>

        <div class="modal-form-container">
            <!-- Filtro por categoría -->
            <div class="categoria-filter" style="margin-bottom: 1.5rem;">
                <label class="form-label">Filtrar por Categoría</label>
                <select id="categoria-filter" class="form-input" style="max-width: 300px;">
                    <option value="">Todas las Categorías</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['id_categoria']; ?>">
                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="productos-selector-modal">
                <?php foreach ($productos as $producto): ?>
                    <div class="producto-item-modal" data-id="<?php echo $producto['id_producto']; ?>"
                         data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"
                         data-precio="<?php echo $producto['precio']; ?>"
                         data-categoria="<?php echo $producto['id_categoria']; ?>">
                        <div class="producto-info">
                            <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                            <p class="producto-desc"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                            <span class="producto-precio">RD$ <?php echo number_format($producto['precio'], 2); ?></span>
                            <?php if ($producto['nombre_categoria']): ?>
                                <span class="producto-categoria" style="font-size: 0.8rem; color: var(--text-light);"><?php echo htmlspecialchars($producto['nombre_categoria']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="producto-controls">
                            <button type="button" class="btn-quantity minus">-</button>
                            <input type="number" class="quantity-input-modal" value="0" min="0" max="10">
                            <button type="button" class="btn-quantity plus">+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirmar-productos">
                    <i class="fas fa-check"></i>
                    Confirmar Selección
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de orden -->
<div class="modal" id="detallesOrdenModal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Detalles de Orden <span id="detalles-orden-id"></span></h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body" id="detalles-orden-content">
            <div class="orden-info-section">
                <div class="info-row">
                    <div class="info-item">
                        <strong>Mesa:</strong> <span id="detalles-mesa"></span>
                    </div>
                    <div class="info-item">
                        <strong>Cliente:</strong> <span id="detalles-cliente"></span>
                    </div>
                    <div class="info-item">
                        <strong>Empleado:</strong> <span id="detalles-empleado"></span>
                    </div>
                    <div class="info-item">
                        <strong>Estado:</strong> <span id="detalles-estado"></span>
                    </div>
                    <div class="info-item">
                        <strong>Fecha:</strong> <span id="detalles-fecha"></span>
                    </div>
                    <div class="info-item">
                        <strong>Total:</strong> <span id="detalles-total"></span>
                    </div>
                </div>
            </div>

            <div class="orden-detalles-section">
                <h4>Productos Ordenados</h4>
                <div id="detalles-productos" class="productos-lista">
                    <!-- Productos se cargarán aquí -->
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary close-modal">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal para editar orden -->
<div class="modal" id="editarOrdenModal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Editar Orden <span id="editar-orden-id"></span></h3>
            <button class="close-modal">&times;</button>
        </div>

        <div class="modal-form-container">
            <form id="editarOrdenForm" class="modal-form">
                <input type="hidden" id="editar-id-orden" name="id_orden">

                <div class="form-section">
                    <div class="form-section-title">Información del Cliente</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nombre del Cliente</label>
                            <input type="text" name="cliente_nombre" id="editar-cliente-nombre" class="form-input">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Empleado</label>
                            <select name="id_empleado" id="editar-id-empleado" class="form-input" required>
                                <option value="">Seleccionar Empleado</option>
                                <?php foreach ($empleados as $empleado): ?>
                                    <option value="<?php echo $empleado['id_empleado']; ?>">
                                        <?php echo htmlspecialchars($empleado['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observaciones</label>
                        <textarea id="editar-observaciones" name="observaciones" class="form-input" rows="3"></textarea>
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="form-section">
                    <div class="form-section-title">Productos Actuales</div>

                    <div id="editar-productos-actuales" class="productos-lista">
                        <!-- Productos actuales se cargarán aquí -->
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="form-section">
                    <div class="form-section-title">Agregar Productos</div>

                    <div class="productos-seleccionados" id="productos-seleccionados-editar">
                        <p class="no-productos">No hay productos para agregar. <button type="button" class="btn-link" id="btn-agregar-productos-editar">Agregar productos</button></p>
                    </div>

                    <div class="orden-summary">
                        <div class="summary-item">
                            <span>Subtotal:</span>
                            <span id="subtotal-editar">RD$ 0.00</span>
                        </div>
                        <div class="summary-item total">
                            <span>Total:</span>
                            <span id="total-editar">RD$ 0.00</span>
                        </div>
                    </div>
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
</div>

<style>
.ordenes-header {
    background: rgba(17, 17, 17, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.stats-overview {
    display: flex;
    gap: 1rem;
}

.stat-mini {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    border-radius: var(--border-radius-sm);
    background: #0F0F0F;
    box-shadow: var(--shadow-soft);
    min-width: 80px;
}

.stat-mini.pendiente { border-top: 4px solid #e74c3c; }
.stat-mini.preparacion { border-top: 4px solid #f39c12; }
.stat-mini.lista { border-top: 4px solid #27ae60; }

.stat-count {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: white;
}

.stat-label {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.8);
}

.ordenes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.orden-card {
    background: #0F0F0F !important;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
    border-top: 4px solid;
}

.orden-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-medium);
}

.orden-card.pendiente {
    background: #0F0F0F;
    border-top-color: #e74c3c;
    color: black;
}
.orden-card.confirmada { border-top-color: #3498db; }
.orden-card.en_preparacion {
    background: #0F0F0F;
    border-top-color: #f39c12;
    color: black;
}
.orden-card.lista {
    background: #0F0F0F;
    border-top-color: #27ae60;
    color: black;
}
.orden-card.entregada { border-top-color: #9b59b6; }
.orden-card.pagada { border-top-color: #2ecc71; }

.orden-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.orden-id {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-white);
}

.orden-mesa {
    color: var(--text-light);
    font-size: 0.875rem;
}

.orden-estado-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.orden-estado-badge.pendiente { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }
.orden-estado-badge.confirmada { background: rgba(52, 152, 219, 0.1); color: #3498db; }
.orden-estado-badge.en_preparacion { background: rgba(243, 156, 18, 0.1); color: #f39c12; }
.orden-estado-badge.lista { background: rgba(39, 174, 96, 0.1); color: #27ae60; }

.orden-body {
    margin-bottom: 1rem;
}

.orden-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-light);
}

.detail-item i {
    width: 16px;
    text-align: center;
}

.orden-total {
    text-align: right;
    font-size: 1.25rem;
    color: var(--color-white);
}

.orden-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.btn-action {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: var(--border-radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: #d4d4d4;
}

.btn-action.view { background: var(--accent-gradient); }
.btn-action.edit { background: var(--primary-gradient); }
.btn-action.success { background: var(--success-gradient); }
.btn-action.warning { background: var(--warning-gradient); }
.btn-action.info { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.btn-action.danger { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }

.btn-action:hover {
    transform: scale(1.1);
}

/* Modal grande */
.modal-content.large {
    max-width: 800px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.productos-selector {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: var(--border-radius-sm);
    padding: 1rem;
}

.producto-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.producto-item:hover {
    background: rgba(102, 126, 234, 0.05);
}

.producto-item:last-child {
    border-bottom: none;
}

.producto-info h4 {
    margin: 0 0 0.25rem 0;
    color: var(--text-dark);
}

.producto-desc {
    margin: 0 0 0.5rem 0;
    color: var(--text-light);
    font-size: 0.875rem;
}

.producto-precio {
    font-weight: 600;
    color: var(--text-dark);
}

.producto-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-quantity {
    width: 32px;
    height: 32px;
    border: 1px solid rgba(102, 126, 234, 0.2);
    background: #c2c2c2;
    border-radius: var(--border-radius-sm);
    cursor: pointer;
    transition: var(--transition);
}

.btn-quantity:hover {
    background: var(--primary-gradient);
    color: #d4d4d4;
    border-color: transparent;
}

.quantity-input {
    width: 60px;
    text-align: center;
    padding: 0.5rem;
    border: 1px solid rgba(102, 126, 234, 0.2);
    border-radius: var(--border-radius-sm);
}

.orden-summary {
    background: rgba(102, 126, 234, 0.05);
    padding: 1.5rem;
    border-radius: var(--border-radius-sm);
    margin: 1.5rem 0;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.summary-item.total {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-dark);
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    padding-top: 0.5rem;
    margin-top: 0.5rem;
}

.orden-info-section {
    margin-bottom: 2rem;
}

.info-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.info-item {
    padding: 1rem;
    background: rgba(198,40,40,0.03);
    border-radius: var(--border-radius-sm);
    border: 1px solid rgba(198,40,40,0.08);
}

.info-item strong {
    color: var(--color-white);
    display: block;
    margin-bottom: 0.5rem;
}

.orden-detalles-section h4 {
    color: var(--color-white);
    margin-bottom: 1rem;
    font-size: 1.25rem;
}

.productos-lista {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.producto-detalle-item, .producto-actual-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: rgba(198,40,40,0.03);
    border-radius: var(--border-radius-sm);
    border: 1px solid rgba(198,40,40,0.08);
}

.producto-detalle-info h5, .producto-actual-info h5 {
    margin: 0 0 0.5rem 0;
    color: var(--color-white);
}

.producto-detalle-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--text-light);
}

.producto-actual-info p {
    margin: 0;
    color: var(--text-light);
}

.btn-danger {
    background: var(--warning-gradient);
    color: #d4d4d4;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.modal-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: flex-end;
}

.productos-seleccionados {
    margin-bottom: 1rem;
}

.no-productos {
    text-align: center;
    color: var(--text-light);
    padding: 2rem;
    background: rgba(17, 17, 17, 0.8);
    border-radius: var(--border-radius-sm);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-link {
    background: none;
    border: none;
    color: var(--primary-red);
    cursor: pointer;
    text-decoration: underline;
    font-size: inherit;
}

.btn-link:hover {
    color: #ff6b6b;
}

.productos-selector-modal {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
    max-height: 400px;
    overflow-y: auto;
    margin-bottom: 2rem;
}

.producto-item-modal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: rgba(17, 17, 17, 0.8);
    border-radius: var(--border-radius-sm);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: var(--transition);
}

.producto-item-modal:hover {
    background: rgba(17, 17, 17, 0.9);
    border-color: rgba(102, 126, 234, 0.3);
}

.quantity-input-modal {
    width: 60px;
    text-align: center;
    padding: 0.5rem;
    border: 1px solid rgba(102, 126, 234, 0.2);
    border-radius: var(--border-radius-sm);
    background: rgba(17, 17, 17, 0.9);
    color: var(--color-white);
}

.producto-seleccionado-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: rgba(17, 17, 17, 0.8);
    border-radius: var(--border-radius-sm);
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 0.5rem;
}

.producto-seleccionado-info h5 {
    margin: 0 0 0.5rem 0;
    color: var(--color-white);
}

.producto-seleccionado-info p {
    margin: 0;
    color: var(--text-light);
}

/* Estilos para modales y formularios */
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
    background: rgba(17, 17, 17, 0.95);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-strong);
    width: 90%;
    max-width: 600px;
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

.modal-form-container {
    padding: 2rem;
}

.form-section {
    margin-bottom: 2rem;
}

.form-section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--color-white);
    border-bottom: 2px solid rgba(102, 126, 234, 0.2);
    padding-bottom: 0.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--color-white);
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid rgba(102, 126, 234, 0.1);
    border-radius: var(--border-radius-sm);
    font-size: 1rem;
    transition: var(--transition);
    background: rgba(17, 17, 17, 0.9);
    color: var(--color-white);
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
}

.form-divider {
    height: 1px;
    background: rgba(102, 126, 234, 0.2);
    margin: 2rem 0;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(102, 126, 234, 0.2);
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--border-radius-sm);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background: var(--primary-gradient);
    color: #d4d4d4;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.btn-success {
    background: var(--success-gradient);
    color: #d4d4d4;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.btn-secondary {
    background: rgba(0, 0, 0, 0.1);
    color: var(--text-dark);
}

.btn-secondary:hover {
    background: rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
}

.form-help {
    color: var(--text-light);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.imagen-actual {
    margin-top: 1rem;
    padding: 1rem;
    background: rgba(102, 126, 234, 0.1);
    border-radius: var(--border-radius-sm);
}

.imagen-actual p {
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
    color: var(--color-muted);
}

@media (max-width: 768px) {
    .header-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .stats-overview {
        justify-content: space-around;
    }

    .ordenes-grid {
        grid-template-columns: 1fr;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .producto-item {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }

    .producto-controls {
        justify-content: center;
    }

    .info-row {
        grid-template-columns: 1fr;
    }

    .producto-detalle-item, .producto-actual-item {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .productos-selector-modal {
        grid-template-columns: 1fr;
    }
    
    .producto-item-modal {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .producto-controls {
        justify-content: center;
    }
    
    .form-actions {
        flex-direction: column;
    }
}

/* Animaciones */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInUp {
    from { 
        transform: scale(0.9);
        opacity: 0;
    }
    to { 
        transform: scale(1);
        opacity: 1;
    }
}

.animate-fade-in {
    animation: fadeIn 0.6s ease-out;
}

.animate-slide-in-up {
    animation: slideInUp 0.5s ease-out;
}

.stagger-animate > * {
    animation: slideInUp 0.5s ease-out both;
}

.stagger-animate > *:nth-child(1) { animation-delay: 0.1s; }
.stagger-animate > *:nth-child(2) { animation-delay: 0.2s; }
.stagger-animate > *:nth-child(3) { animation-delay: 0.3s; }
.stagger-animate > *:nth-child(4) { animation-delay: 0.4s; }
.stagger-animate > *:nth-child(5) { animation-delay: 0.5s; }
</style>

<script>
// ... (el JavaScript completo que te envié anteriormente se mantiene igual)
let currentModalTarget = 'nueva';
let selectedProducts = {
    nueva: [],
    editar: []
};

document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const nuevaOrdenBtn = document.getElementById('nuevaOrdenBtn');
    const nuevaOrdenModal = document.getElementById('nuevaOrdenModal');
    const seleccionarProductosModal = document.getElementById('seleccionarProductosModal');
    const editarOrdenModal = document.getElementById('editarOrdenModal');
    const detallesOrdenModal = document.getElementById('detallesOrdenModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');

    // Nueva orden modal
    nuevaOrdenBtn.addEventListener('click', function() {
        resetNuevaOrdenForm();
        nuevaOrdenModal.classList.add('active');
    });

    // Close modals
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            nuevaOrdenModal.classList.remove('active');
            seleccionarProductosModal.classList.remove('active');
            editarOrdenModal.classList.remove('active');
            detallesOrdenModal.classList.remove('active');
        });
    });

    // Close modals when clicking outside
    [nuevaOrdenModal, seleccionarProductosModal, editarOrdenModal, detallesOrdenModal].forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Quantity controls for product selection modal
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-quantity')) {
            const input = e.target.parentElement.querySelector('.quantity-input-modal');
            let value = parseInt(input.value);
            
            if (e.target.classList.contains('plus')) {
                value++;
            } else if (e.target.classList.contains('minus') && value > 0) {
                value--;
            }
            
            input.value = value;
        }
    });

    // Handle add products buttons
    document.getElementById('btn-agregar-productos-nueva').addEventListener('click', function() {
        seleccionarProductosModal.classList.add('active');
        currentModalTarget = 'nueva';
        resetProductSelectionModal();
    });

    document.getElementById('btn-agregar-productos-editar').addEventListener('click', function() {
        seleccionarProductosModal.classList.add('active');
        currentModalTarget = 'editar';
        resetProductSelectionModal();
    });

    // Handle category filter
    document.getElementById('categoria-filter').addEventListener('change', function() {
        const selectedCategory = this.value;
        const productItems = document.querySelectorAll('.producto-item-modal');

        productItems.forEach(item => {
            const itemCategory = item.getAttribute('data-categoria');
            if (selectedCategory === '' || itemCategory === selectedCategory) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Handle confirm products selection
    document.getElementById('btn-confirmar-productos').addEventListener('click', function() {
        const selected = [];
        document.querySelectorAll('.producto-item-modal').forEach(item => {
            const quantity = parseInt(item.querySelector('.quantity-input-modal').value);
            if (quantity > 0) {
                selected.push({
                    id: item.getAttribute('data-id'),
                    nombre: item.getAttribute('data-nombre'),
                    precio: parseFloat(item.getAttribute('data-precio')),
                    cantidad: quantity,
                    subtotal: quantity * parseFloat(item.getAttribute('data-precio'))
                });
            }
        });

        selectedProducts[currentModalTarget] = selected;
        updateSelectedProductsDisplay(currentModalTarget);
        updateTotal(currentModalTarget);

        seleccionarProductosModal.classList.remove('active');
    });

    // Handle new order form submission
    const nuevaOrdenForm = document.getElementById('nuevaOrdenForm');
    nuevaOrdenForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitNuevaOrden();
    });

    // Handle edit order form submission
    const editarOrdenForm = document.getElementById('editarOrdenForm');
    editarOrdenForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitEditarOrden();
    });
});

function resetNuevaOrdenForm() {
    document.getElementById('nuevaOrdenForm').reset();
    document.getElementById('nueva-fecha-pedido').value = new Date().toISOString().slice(0, 16);
    selectedProducts.nueva = [];
    updateSelectedProductsDisplay('nueva');
    updateTotal('nueva');
}

function resetProductSelectionModal() {
    document.querySelectorAll('.quantity-input-modal').forEach(input => {
        input.value = 0;
    });
    // Reset category filter
    document.getElementById('categoria-filter').value = '';
    // Show all products
    document.querySelectorAll('.producto-item-modal').forEach(item => {
        item.style.display = 'flex';
    });
}

function updateSelectedProductsDisplay(target) {
    const container = document.getElementById('productos-seleccionados-' + target);
    const products = selectedProducts[target];

    if (products.length === 0) {
        container.innerHTML = '<p class="no-productos">No hay productos seleccionados. <button type="button" class="btn-link" id="btn-agregar-productos-' + target + '">Agregar productos</button></p>';
        
        // Re-attach event listener
        document.getElementById('btn-agregar-productos-' + target).addEventListener('click', function() {
            document.getElementById('seleccionarProductosModal').classList.add('active');
            currentModalTarget = target;
            resetProductSelectionModal();
        });
        return;
    }

    let html = '';
    products.forEach((product, index) => {
        html += `
            <div class="producto-seleccionado-item">
                <div class="producto-seleccionado-info">
                    <h5>${product.nombre}</h5>
                    <p>Cantidad: ${product.cantidad} - Precio: RD$ ${product.precio.toFixed(2)} - Subtotal: RD$ ${product.subtotal.toFixed(2)}</p>
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeProduct('${target}', ${index})">
                    <i class="fas fa-times"></i>
                </button>
                <input type="hidden" name="productos[${product.id}][cantidad]" value="${product.cantidad}">
            </div>
        `;
    });
    container.innerHTML = html;
}

function removeProduct(target, index) {
    selectedProducts[target].splice(index, 1);
    updateSelectedProductsDisplay(target);
    updateTotal(target);
}

function updateTotal(target) {
    const products = selectedProducts[target];
    const total = products.reduce((sum, product) => sum + product.subtotal, 0);
    document.getElementById('subtotal-' + target).textContent = 'RD$ ' + total.toFixed(2);
    document.getElementById('total-' + target).textContent = 'RD$ ' + total.toFixed(2);
}

async function submitNuevaOrden() {
    const form = document.getElementById('nuevaOrdenForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    // Check if products are selected
    let hasProducts = false;
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('productos[') && key.includes('][cantidad]') && parseInt(value) > 0) {
            hasProducts = true;
            break;
        }
    }

    if (!hasProducts) {
        showNotification('Debe seleccionar al menos un producto', 'error');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando Orden...';

    try {
        const response = await fetch('includes/actions.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Orden creada exitosamente', 'success');
            document.getElementById('nuevaOrdenModal').classList.remove('active');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al crear orden: ' + result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Crear Orden';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al crear orden', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Crear Orden';
    }
}

async function submitEditarOrden() {
    const form = document.getElementById('editarOrdenForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    try {
        // Prepare update data
        const updateData = {
            action: 'editar_orden',
            id_orden: formData.get('id_orden'),
            cliente_nombre: formData.get('cliente_nombre') || '',
            id_empleado: formData.get('id_empleado'),
            observaciones: formData.get('observaciones')
        };

        // Add new products if any
        selectedProducts.editar.forEach(product => {
            updateData[`productos_agregar[${product.id}]`] = product.cantidad;
        });

        const response = await fetch('includes/actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(updateData)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Orden actualizada exitosamente', 'success');
            document.getElementById('editarOrdenModal').classList.remove('active');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al actualizar orden: ' + result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al actualizar orden', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
    }
}

// Funciones para manejar órdenes
async function verDetallesOrden(idOrden) {
    try {
        const response = await fetch(`api/ordenes.php/${idOrden}`);
        const result = await response.json();

        if (result.success) {
            const orden = result.data;
            document.getElementById('detalles-orden-id').textContent = orden.id;
            document.getElementById('detalles-mesa').textContent = orden.mesa ? orden.mesa.numero : 'N/A';
            document.getElementById('detalles-cliente').textContent = orden.cliente ? orden.cliente.nombre : 'Cliente General';
            document.getElementById('detalles-empleado').textContent = orden.empleado.nombre;
            document.getElementById('detalles-estado').textContent = orden.estado_texto;
            document.getElementById('detalles-fecha').textContent = new Date(orden.fecha_orden).toLocaleString();
            document.getElementById('detalles-total').textContent = `RD$ ${parseFloat(orden.total).toFixed(2)}`;

            // Mostrar productos
            let productosHtml = '';
            orden.detalles.forEach(detalle => {
                productosHtml += `
                    <div class="producto-detalle-item">
                        <div class="producto-detalle-info">
                            <h5>${detalle.producto.nombre}</h5>
                            <div class="producto-detalle-meta">
                                <span>Cantidad: ${detalle.cantidad}</span>
                                <span>Precio: RD$ ${parseFloat(detalle.precio_unitario).toFixed(2)}</span>
                                <span>Subtotal: RD$ ${parseFloat(detalle.subtotal).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            document.getElementById('detalles-productos').innerHTML = productosHtml;

            document.getElementById('detallesOrdenModal').classList.add('active');
        } else {
            showNotification('Error al cargar detalles de la orden', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar detalles de la orden', 'error');
    }
}

async function editarOrden(idOrden) {
    try {
        const response = await fetch(`api/ordenes.php/${idOrden}`);
        const result = await response.json();

        if (result.success) {
            const orden = result.data;

            // Llenar formulario de edición
            document.getElementById('editar-id-orden').value = orden.id;
            document.getElementById('editar-cliente-nombre').value = orden.cliente ? orden.cliente.nombre : '';
            document.getElementById('editar-id-empleado').value = orden.empleado.id;
            document.getElementById('editar-observaciones').value = orden.observaciones || '';

            // Mostrar productos actuales
            let productosActualesHtml = '';
            orden.detalles.forEach(detalle => {
                productosActualesHtml += `
                    <div class="producto-actual-item">
                        <div class="producto-actual-info">
                            <h5>${detalle.producto.nombre}</h5>
                            <p>Cantidad: ${detalle.cantidad} - Precio: RD$ ${parseFloat(detalle.precio_unitario).toFixed(2)} - Subtotal: RD$ ${parseFloat(detalle.subtotal).toFixed(2)}</p>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarProductoOrden(${orden.id}, ${detalle.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            });
            document.getElementById('editar-productos-actuales').innerHTML = productosActualesHtml;

            // Resetear productos para agregar
            selectedProducts.editar = [];
            updateSelectedProductsDisplay('editar');
            updateTotal('editar');

            document.getElementById('editarOrdenModal').classList.add('active');
        } else {
            showNotification('Error al cargar datos de la orden', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar datos de la orden', 'error');
    }
}

async function cambiarEstadoOrden(idOrden, nuevoEstado) {
    if (!confirm(`¿Está seguro de cambiar el estado de la orden a "${nuevoEstado}"?`)) {
        return;
    }

    try {
        const response = await fetch('includes/actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                action: 'cambiar_estado_orden',
                id_orden: idOrden,
                nuevo_estado: nuevoEstado
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Estado de orden actualizado exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al cambiar estado: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cambiar estado de la orden', 'error');
    }
}

async function eliminarOrden(idOrden) {
    if (!confirm('¿Está seguro de eliminar esta orden? Esta acción no se puede deshacer.')) {
        return;
    }

    try {
        const response = await fetch('includes/actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                action: 'eliminar_orden',
                id_orden: idOrden
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Orden eliminada exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al eliminar orden: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al eliminar la orden', 'error');
    }
}

async function eliminarProductoOrden(idOrden, idDetalle) {
    if (!confirm('¿Está seguro de eliminar este producto de la orden?')) {
        return;
    }

    try {
        const response = await fetch('includes/actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                action: 'eliminar_producto_orden',
                id_orden: idOrden,
                id_detalle: idDetalle
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Producto eliminado de la orden', 'success');
            // Recargar la orden en el modal
            editarOrden(idOrden);
        } else {
            showNotification('Error al eliminar producto: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al eliminar producto de la orden', 'error');
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
