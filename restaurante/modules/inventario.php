<?php
$db = new Database();
$connection = $db->getConnection();

// Obtener inventario
$inventario = [];
$categorias_ingredientes = [];
$alertas_bajo_stock = [];

try {
    // Obtener todos los items del inventario
    $query = "SELECT * FROM inventario ORDER BY nombre_ingrediente";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener categorías únicas para filtros
    $query = "SELECT DISTINCT proveedor FROM inventario WHERE proveedor IS NOT NULL AND proveedor != ''";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $categorias_ingredientes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Identificar items con bajo stock
    $alertas_bajo_stock = array_filter($inventario, function($item) {
        return $item['cantidad'] <= $item['stock_minimo'];
    });

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="page-header animate-fade-in">
    <h1 class="page-title">Gestión de Inventario</h1>
    <p class="page-subtitle">Control de ingredientes y suministros del restaurante</p>
</div>

<!-- Alertas de Stock Bajo -->
<?php if (!empty($alertas_bajo_stock)): ?>
<div class="alertas-container">
    <div class="alerta-header">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>Alertas de Stock Bajo</h3>
        <span class="alerta-count"><?php echo count($alertas_bajo_stock); ?> items</span>
    </div>
    <div class="alertas-grid">
        <?php foreach ($alertas_bajo_stock as $alerta): ?>
            <div class="alerta-item">
                <div class="alerta-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="alerta-content">
                    <h4><?php echo htmlspecialchars($alerta['nombre_ingrediente']); ?></h4>
                    <p>Stock actual: <strong><?php echo $alerta['cantidad'] . ' ' . $alerta['unidad_medida']; ?></strong></p>
                    <p class="alerta-text">Mínimo requerido: <?php echo $alerta['stock_minimo'] . ' ' . $alerta['unidad_medida']; ?></p>
                </div>
                <button class="btn btn-warning btn-sm" onclick="mostrarModalAjuste(<?php echo $alerta['id_inventario']; ?>)">
                    <i class="fas fa-edit"></i>
                    Ajustar
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="inventario-controls">
    <div class="controls-left">
        <button class="btn btn-primary" id="agregarIngredienteBtn">
            <i class="fas fa-plus"></i>
            Agregar Ingrediente
        </button>
        <button class="btn btn-success" id="generarReporteBtn">
            <i class="fas fa-file-export"></i>
            Generar Reporte
        </button>
    </div>
    
    <div class="controls-right">
        <div class="filters">
            <select id="filterProveedor" class="filter-select">
                <option value="">Todos los proveedores</option>
                <?php foreach ($categorias_ingredientes as $proveedor): ?>
                    <option value="<?php echo htmlspecialchars($proveedor); ?>">
                        <?php echo htmlspecialchars($proveedor); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select id="filterStock" class="filter-select">
                <option value="">Todo el stock</option>
                <option value="bajo">Stock Bajo</option>
                <option value="optimo">Stock Óptimo</option>
                <option value="exceso">Stock en Exceso</option>
            </select>
        </div>
        
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInventario" placeholder="Buscar ingredientes...">
        </div>
    </div>
</div>

<div class="inventario-stats">
    <div class="stat-card mini">
        <div class="stat-icon">
            <i class="fas fa-boxes"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo count($inventario); ?></div>
            <div class="stat-label">Total Items</div>
        </div>
    </div>
    
    <div class="stat-card mini warning">
        <div class="stat-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo count($alertas_bajo_stock); ?></div>
            <div class="stat-label">Stock Bajo</div>
        </div>
    </div>
    
    <div class="stat-card mini success">
        <div class="stat-icon">
            <i class="fas fa-truck-loading"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo count($categorias_ingredientes); ?></div>
            <div class="stat-label">Proveedores</div>
        </div>
    </div>
    
    <div class="stat-card mini accent">
        <div class="stat-icon">
            <i class="fas fa-weight-hanging"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">
                <?php echo array_sum(array_column($inventario, 'cantidad')); ?>
            </div>
            <div class="stat-label">Unidades Totales</div>
        </div>
    </div>
</div>

<div class="inventario-table-container">
    <table class="inventario-table">
        <thead>
            <tr>
                <th>Ingrediente</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Unidad</th>
                <th>Proveedor</th>
                <th>Estado</th>
                <th>Última Actualización</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inventario as $item): 
                $porcentaje_stock = $item['stock_minimo'] > 0 ? ($item['cantidad'] / $item['stock_minimo']) * 100 : 100;
                $estado = '';
                $clase_estado = '';
                
                if ($item['cantidad'] <= $item['stock_minimo']) {
                    $estado = 'Stock Bajo';
                    $clase_estado = 'bajo';
                } elseif ($porcentaje_stock > 200) {
                    $estado = 'Exceso';
                    $clase_estado = 'exceso';
                } else {
                    $estado = 'Óptimo';
                    $clase_estado = 'optimo';
                }
            ?>
                <tr class="inventario-item" data-proveedor="<?php echo htmlspecialchars($item['proveedor'] ?? ''); ?>" 
                    data-estado="<?php echo $clase_estado; ?>">
                    <td>
                        <div class="ingrediente-info">
                            <strong><?php echo htmlspecialchars($item['nombre_ingrediente']); ?></strong>
                        </div>
                    </td>
                    <td>
                        <div class="stock-info">
                            <span class="stock-cantidad"><?php echo $item['cantidad']; ?></span>
                            <div class="stock-bar">
                                <div class="stock-fill <?php echo $clase_estado; ?>" 
                                     style="width: <?php echo min($porcentaje_stock, 100); ?>%"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="stock-minimo"><?php echo $item['stock_minimo']; ?></span>
                    </td>
                    <td>
                        <span class="unidad-badge"><?php echo htmlspecialchars($item['unidad_medida']); ?></span>
                    </td>
                    <td>
                        <?php if ($item['proveedor']): ?>
                            <span class="proveedor-tag"><?php echo htmlspecialchars($item['proveedor']); ?></span>
                        <?php else: ?>
                            <span class="no-info">No especificado</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $clase_estado; ?>">
                            <?php echo $estado; ?>
                        </span>
                    </td>
                    <td>
                        <span class="fecha-actualizacion">
                            <?php echo date('d/m/Y H:i', strtotime($item['fecha_actualizacion'])); ?>
                        </span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action primary" onclick="mostrarModalAjuste(<?php echo $item['id_inventario']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action success" onclick="agregarStock(<?php echo $item['id_inventario']; ?>)">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="btn-action warning" onclick="retirarStock(<?php echo $item['id_inventario']; ?>)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button class="btn-action info" onclick="verHistorial(<?php echo $item['id_inventario']; ?>)">
                                <i class="fas fa-history"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal para agregar ingrediente -->
<div class="modal" id="agregarIngredienteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Agregar Nuevo Ingrediente</h3>
            <button class="close-modal">&times;</button>
        </div>
        <form class="modal-form" id="agregarIngredienteForm" onsubmit="agregarIngrediente(event)">
            
            <div class="form-group">
                <label class="form-label">Nombre del Ingrediente</label>
                <input type="text" name="nombre_ingrediente" class="form-input" required 
                       placeholder="Ej: Arroz, Pollo, Tomate...">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Cantidad Inicial</label>
                    <input type="number" name="cantidad" class="form-input" required 
                           step="0.01" min="0" value="0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Unidad de Medida</label>
                    <select name="unidad_medida" class="form-input" required>
                        <option value="kg">Kilogramos (kg)</option>
                        <option value="g">Gramos (g)</option>
                        <option value="lb">Libras (lb)</option>
                        <option value="lt">Litros (lt)</option>
                        <option value="ml">Mililitros (ml)</option>
                        <option value="unidades">Unidades</option>
                        <option value="paquetes">Paquetes</option>
                        <option value="cajas">Cajas</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" class="form-input" required 
                           step="0.01" min="0" value="1" placeholder="Cantidad mínima requerida">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Proveedor</label>
                    <input type="text" name="proveedor" class="form-input" 
                           placeholder="Nombre del proveedor">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar Ingrediente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para ajustar stock -->
<div class="modal" id="ajustarStockModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ajustar Stock</h3>
            <button class="close-modal">&times;</button>
        </div>
        <form class="modal-form" id="ajustarStockForm" onsubmit="ajustarStock(event)">
            <input type="hidden" name="id_inventario" id="ajuste_id_inventario">
            <input type="hidden" name="cantidad_actual" id="ajuste_cantidad_actual">
            
            <div class="ajuste-info">
                <h4 id="ajuste_nombre_ingrediente"></h4>
                <p>Stock actual: <strong id="ajuste_stock_actual"></strong> <span id="ajuste_unidad"></span></p>
                <p>Stock mínimo: <strong id="ajuste_stock_minimo"></strong> <span id="ajuste_unidad_min"></span></p>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nueva Cantidad</label>
                <input type="number" name="nueva_cantidad" id="nueva_cantidad" class="form-input" required 
                       step="0.01" min="0">
            </div>
            
            <div class="form-group">
                <label class="form-label">Tipo de Movimiento</label>
                <div class="movimiento-options">
                    <label class="radio-option">
                        <input type="radio" name="movimiento_tipo" value="entrada" checked>
                        <span class="radio-custom"></span>
                        <span class="radio-label">
                            <i class="fas fa-arrow-down"></i>
                            Entrada de Stock
                        </span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="movimiento_tipo" value="salida">
                        <span class="radio-custom"></span>
                        <span class="radio-label">
                            <i class="fas fa-arrow-up"></i>
                            Salida de Stock
                        </span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="movimiento_tipo" value="ajuste">
                        <span class="radio-custom"></span>
                        <span class="radio-label">
                            <i class="fas fa-sync"></i>
                            Ajuste Directo
                        </span>
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Motivo (Opcional)</label>
                <textarea name="motivo" class="form-input" rows="2" 
                          placeholder="Ej: Compra, Consumo, Ajuste de inventario..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i>
                    Aplicar Ajuste
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.alertas-container {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 2rem;
    color: white;
    box-shadow: var(--shadow-medium);
}

.alerta-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.alerta-header i {
    font-size: 1.5rem;
}

.alerta-header h3 {
    margin: 0;
    flex: 1;
}

.alerta-count {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.alertas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.alerta-item {
    background: rgba(255, 255, 255, 0.9);
    border-radius: var(--border-radius-sm);
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    color: var(--text-dark);
}

.alerta-icon {
    width: 50px;
    height: 50px;
    background: var(--warning-gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.alerta-content {
    flex: 1;
}

.alerta-content h4 {
    margin: 0 0 0.5rem 0;
    color: var(--text-dark);
}

.alerta-content p {
    margin: 0.25rem 0;
    font-size: 0.875rem;
}

.alerta-text {
    color: #e74c3c;
    font-weight: 600;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.inventario-controls {
    background: rgba(17, 17, 17, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.controls-left {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.controls-right {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.filters {
    display: flex;
    gap: 0.5rem;
}

.filter-select {
    padding: 0.5rem;
    border: 1px solid rgba(102, 126, 234, 0.2);
    border-radius: var(--border-radius-sm);
    background: rgba(255, 255, 255, 0.8);
    font-size: 0.875rem;
    cursor: pointer;
}

.inventario-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card.mini.warning .stat-icon {
    background: var(--warning-gradient);
}

.stat-card.mini.success .stat-icon {
    background: var(--success-gradient);
}

.stat-card.mini.accent .stat-icon {
    background: var(--accent-gradient);
}

.inventario-table-container {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
}

.inventario-table {
    width: 100%;
    border-collapse: collapse;
}

.inventario-table th {
    background: var(--primary-gradient);
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}

.inventario-table td {
    padding: 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.inventario-table tr:last-child td {
    border-bottom: none;
}

.inventario-table tr:hover {
    background: rgba(102, 126, 234, 0.05);
}

.stock-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stock-cantidad {
    font-weight: 700;
    min-width: 40px;
}

.stock-bar {
    flex: 1;
    height: 8px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    overflow: hidden;
    min-width: 80px;
}

.stock-fill {
    height: 100%;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.stock-fill.bajo {
    background: var(--warning-gradient);
}

.stock-fill.optimo {
    background: var(--success-gradient);
}

.stock-fill.exceso {
    background: var(--accent-gradient);
}

.stock-minimo {
    color: var(--text-light);
    font-weight: 600;
}

.unidad-badge {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.proveedor-tag {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-badge.bajo {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.status-badge.optimo {
    background: rgba(39, 174, 96, 0.1);
    color: #27ae60;
}

.status-badge.exceso {
    background: rgba(155, 89, 182, 0.1);
    color: #9b59b6;
}

.fecha-actualizacion {
    color: var(--text-light);
    font-size: 0.875rem;
}

.table-actions {
    display: flex;
    gap: 0.25rem;
}

.table-actions .btn-action {
    width: 32px;
    height: 32px;
    font-size: 0.875rem;
}

.btn-action.primary { background: var(--primary-gradient); }
.btn-action.success { background: var(--success-gradient); }
.btn-action.warning { background: var(--warning-gradient); }
.btn-action.info { background: var(--accent-gradient); }

.ajuste-info {
    background: rgba(102, 126, 234, 0.05);
    padding: 1rem;
    border-radius: var(--border-radius-sm);
    margin-bottom: 1.5rem;
    text-align: center;
}

.ajuste-info h4 {
    margin: 0 0 0.5rem 0;
    color: var(--text-dark);
}

.ajuste-info p {
    margin: 0.25rem 0;
    color: var(--text-light);
}

.movimiento-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border: 2px solid rgba(102, 126, 234, 0.1);
    border-radius: var(--border-radius-sm);
    cursor: pointer;
    transition: var(--transition);
}

.radio-option:hover {
    border-color: rgba(102, 126, 234, 0.3);
    background: rgba(102, 126, 234, 0.05);
}

.radio-option input[type="radio"] {
    display: none;
}

.radio-custom {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(102, 126, 234, 0.3);
    border-radius: 50%;
    position: relative;
    transition: var(--transition);
}

.radio-option input[type="radio"]:checked + .radio-custom {
    border-color: #667eea;
    background: #667eea;
}

.radio-option input[type="radio"]:checked + .radio-custom::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .inventario-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .controls-left, .controls-right {
        justify-content: center;
    }
    
    .filters {
        flex-direction: column;
    }
    
    .inventario-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .inventario-table-container {
        overflow-x: auto;
    }
    
    .inventario-table {
        min-width: 1000px;
    }
    
    .stock-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .stock-bar {
        width: 100%;
    }
    
    .table-actions {
        flex-direction: column;
    }
    
    .alertas-grid {
        grid-template-columns: 1fr;
    }
    
    .alerta-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const agregarIngredienteBtn = document.getElementById('agregarIngredienteBtn');
    const agregarIngredienteModal = document.getElementById('agregarIngredienteModal');
    const ajustarStockModal = document.getElementById('ajustarStockModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const generarReporteBtn = document.getElementById('generarReporteBtn');
    
    agregarIngredienteBtn.addEventListener('click', function() {
        agregarIngredienteModal.classList.add('active');
    });
    
    generarReporteBtn.addEventListener('click', function() {
        window.restaurantApp.showNotification('Generando reporte de inventario...', 'info');
        // Aquí iría la lógica para generar el reporte
        setTimeout(() => {
            window.restaurantApp.showNotification('Reporte generado exitosamente', 'success');
        }, 2000);
    });
    
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            agregarIngredienteModal.classList.remove('active');
            ajustarStockModal.classList.remove('active');
        });
    });
    
    // Close modals when clicking outside
    [agregarIngredienteModal, ajustarStockModal].forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
    
    // Filter functionality
    const filterProveedor = document.getElementById('filterProveedor');
    const filterStock = document.getElementById('filterStock');
    const searchInventario = document.getElementById('searchInventario');
    
    function aplicarFiltros() {
        const proveedor = filterProveedor.value;
        const stock = filterStock.value;
        const searchTerm = searchInventario.value.toLowerCase();
        
        document.querySelectorAll('.inventario-item').forEach(item => {
            const itemProveedor = item.getAttribute('data-proveedor');
            const itemEstado = item.getAttribute('data-estado');
            const itemNombre = item.querySelector('.ingrediente-info strong').textContent.toLowerCase();
            
            const proveedorMatch = !proveedor || itemProveedor === proveedor;
            const stockMatch = !stock || itemEstado === stock;
            const searchMatch = !searchTerm || itemNombre.includes(searchTerm);
            
            if (proveedorMatch && stockMatch && searchMatch) {
                item.style.display = 'table-row';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    filterProveedor.addEventListener('change', aplicarFiltros);
    filterStock.addEventListener('change', aplicarFiltros);
    searchInventario.addEventListener('input', aplicarFiltros);
    
    // Movimiento type change
    document.querySelectorAll('input[name="movimiento_tipo"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const tipo = this.value;
            document.getElementById('ajuste_tipo').value = tipo;
        });
    });
});

function mostrarModalAjuste(idInventario) {
    // Aquí normalmente harías una llamada AJAX para obtener los datos del ingrediente
    // Por ahora, simulamos los datos
    const ingrediente = {
        id_inventario: idInventario,
        nombre_ingrediente: 'Ingrediente ' + idInventario,
        cantidad: 5,
        stock_minimo: 10,
        unidad_medida: 'kg'
    };
    
    document.getElementById('ajuste_id_inventario').value = ingrediente.id_inventario;
    document.getElementById('ajuste_cantidad_actual').value = ingrediente.cantidad;
    document.getElementById('ajuste_nombre_ingrediente').textContent = ingrediente.nombre_ingrediente;
    document.getElementById('ajuste_stock_actual').textContent = ingrediente.cantidad;
    document.getElementById('ajuste_stock_minimo').textContent = ingrediente.stock_minimo;
    document.getElementById('ajuste_unidad').textContent = ingrediente.unidad_medida;
    document.getElementById('ajuste_unidad_min').textContent = ingrediente.unidad_medida;
    document.getElementById('nueva_cantidad').value = ingrediente.cantidad;
    
    document.getElementById('ajustarStockModal').classList.add('active');
}

function agregarStock(idInventario) {
    // Lógica rápida para agregar stock
    if (confirm('¿Agregar 1 unidad al stock?')) {
        window.restaurantApp.showNotification('Stock agregado exitosamente', 'success');
        // Aquí iría la llamada AJAX
    }
}

function retirarStock(idInventario) {
    // Lógica rápida para retirar stock
    if (confirm('¿Retirar 1 unidad del stock?')) {
        window.restaurantApp.showNotification('Stock retirado exitosamente', 'success');
        // Aquí iría la llamada AJAX
    }
}

function verHistorial(idInventario) {
    window.restaurantApp.showNotification('Cargando historial del ingrediente...', 'info');
    // Aquí iría la lógica para mostrar el historial
}

function agregarIngrediente(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    const data = {
        nombre_ingrediente: formData.get('nombre_ingrediente'),
        cantidad: parseFloat(formData.get('cantidad')),
        unidad_medida: formData.get('unidad_medida'),
        stock_minimo: parseFloat(formData.get('stock_minimo')),
        proveedor: formData.get('proveedor')
    };
    
    fetch(window.API_BASE_URL + 'api/inventario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta de la API');
        }
        return response.json();
    })
    .then(data => {
        if (data.success || data.message) {
            window.restaurantApp.showNotification('Ingrediente agregado exitosamente', 'success');
            form.reset();
            document.getElementById('agregarIngredienteModal').classList.remove('active');
            // Recargar la tabla de inventario
            setTimeout(() => location.reload(), 800);
        } else {
            window.restaurantApp.showNotification('Error al agregar ingrediente: ' + (data.error || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.restaurantApp.showNotification('Error de conexión: ' + error.message, 'error');
    });
}

function ajustarStock(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const idInventario = formData.get('id_inventario');
    
    const data = {
        cantidad: parseFloat(formData.get('nueva_cantidad')),
        movimiento_tipo: document.querySelector('input[name="movimiento_tipo"]:checked').value,
        motivo: formData.get('motivo') || ''
    };
    
    fetch(window.API_BASE_URL + 'api/inventario.php/' + idInventario, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta de la API');
        }
        return response.json();
    })
    .then(response => {
        if (response.success || response.message) {
            window.restaurantApp.showNotification('Stock actualizado exitosamente', 'success');
            document.getElementById('ajustarStockModal').classList.remove('active');
            // Recargar la tabla de inventario
            setTimeout(() => location.reload(), 800);
        } else {
            window.restaurantApp.showNotification('Error al actualizar stock: ' + (response.error || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.restaurantApp.showNotification('Error de conexión: ' + error.message, 'error');
    });
}

</script>