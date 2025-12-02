<?php
$db = new Database();
$connection = $db->getConnection();

// Obtener categorías y productos
$categorias = [];
$productos = [];

try {
    // Obtener categorías
    $query = "SELECT * FROM categorias WHERE estado = 'activo'";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener productos con información de categoría
    $query = "
        SELECT p.*, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
        ORDER BY c.nombre, p.nombre
    ";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Agrupar productos por categoría
$productosPorCategoria = [];
foreach ($productos as $producto) {
    $categoria = $producto['categoria_nombre'];
    if (!isset($productosPorCategoria[$categoria])) {
        $productosPorCategoria[$categoria] = [];
    }
    $productosPorCategoria[$categoria][] = $producto;
}
?>

<div class="page-header animate-fade-in">
    <h1 class="page-title">Menú del Restaurante</h1>
    <p class="page-subtitle">Gestiona los platos y productos disponibles</p>
</div>

<div class="menu-controls">
    <div class="controls-left">
        <button class="btn btn-primary" id="addProductoBtn">
            <i class="fas fa-plus"></i>
            Agregar Producto
        </button>
        <button class="btn btn-success" id="addCategoriaBtn">
            <i class="fas fa-folder-plus"></i>
            Nueva Categoría
        </button>
    </div>
    
    <div class="controls-right">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchProductos" placeholder="Buscar productos...">
        </div>
    </div>
</div>

<div class="menu-container">
    <?php if (empty($categorias)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-utensils"></i>
            </div>
            <h3>No hay categorías en el menú</h3>
            <p>Comienza agregando algunas categorías.</p>
            <button class="btn btn-primary" id="addFirstCategoriaBtn">
                <i class="fas fa-plus"></i>
                Agregar Primera Categoría
            </button>
        </div>
    <?php else: ?>
        <?php foreach ($categorias as $categoria): ?>
            <div class="categoria-section" data-categoria="<?php echo htmlspecialchars($categoria['nombre']); ?>">
                <div class="categoria-header">
                    <h2 class="categoria-title"><?php echo htmlspecialchars($categoria['nombre']); ?></h2>
                    <span class="productos-count"><?php echo isset($productosPorCategoria[$categoria['nombre']]) ? count($productosPorCategoria[$categoria['nombre']]) : 0; ?> productos</span>
                </div>

                <div class="productos-grid">
                    <?php
                    $productosCategoria = $productosPorCategoria[$categoria['nombre']] ?? [];
                    if (empty($productosCategoria)): ?>
                        <div class="empty-category">
                            <div class="empty-category-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <p>No hay productos en esta categoría</p>
                            <button class="btn btn-sm btn-primary add-product-to-category" data-categoria-id="<?php echo $categoria['id_categoria']; ?>">
                                <i class="fas fa-plus"></i>
                                Agregar Producto
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($productosCategoria as $producto): ?>
                            <div class="producto-card" data-nombre="<?php echo strtolower(htmlspecialchars($producto['nombre'])); ?>">
                                <div class="producto-image">
                                    <?php if ($producto['imagen']): ?>
                                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <?php else: ?>
                                        <div class="image-placeholder">
                                            <i class="fas fa-utensils"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="producto-badges">
                                        <?php if ($producto['estado'] === 'no_disponible'): ?>
                                            <span class="badge unavailable">No Disponible</span>
                                        <?php endif; ?>
                                        <?php if ($producto['tiempo_preparacion'] > 20): ?>
                                            <span class="badge time"><?php echo $producto['tiempo_preparacion']; ?> min</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Botones de acciones en la imagen -->
                                    <div class="producto-image-actions">
                                        <button class="btn-action edit" onclick="editarProducto(<?php echo $producto['id_producto']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action <?php echo $producto['estado'] === 'disponible' ? 'unavailable' : 'available'; ?>"
                                                onclick="cambiarEstadoProducto(<?php echo $producto['id_producto']; ?>, '<?php echo $producto['estado']; ?>')">
                                            <i class="fas fa-<?php echo $producto['estado'] === 'disponible' ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                        <button class="btn-action delete" onclick="eliminarProducto(<?php echo $producto['id_producto']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="producto-content">
                                    <h3 class="producto-name"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                    <p class="producto-desc"><?php echo htmlspecialchars($producto['descripcion']); ?></p>

                                    <div class="producto-footer">
                                        <div class="producto-precio">
                                            RD$ <?php echo number_format($producto['precio'], 2); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal para agregar/editar producto -->
<div class="modal" id="productoModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="productoModalTitle">Agregar Producto</h3>
            <button class="close-modal">&times;</button>
        </div>
        
        <div class="modal-form-container">
            <form method="POST" action="includes/actions.php" class="modal-form" enctype="multipart/form-data" id="productoForm">
                <input type="hidden" name="action" id="formAction" value="agregar_producto">
                <input type="hidden" name="id_producto" id="id_producto">
                
                <div class="form-section">
                    <div class="form-section-title">Información del Producto</div>
                    
                    <div class="form-group">
                        <label class="form-label">Nombre del Producto</label>
                        <input type="text" name="nombre" id="productoNombre" class="form-input" required 
                               placeholder="Ej: Lomo Saltado, Ceviche Mixto...">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="productoDescripcion" class="form-input" rows="3" 
                                  placeholder="Describe el producto..."></textarea>
                    </div>
                </div>
                
                <div class="form-divider"></div>
                
                <div class="form-section">
                    <div class="form-section-title">Detalles</div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Precio (RD$)</label>
                            <input type="number" name="precio" id="productoPrecio" class="form-input" required 
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Categoría</label>
                            <select name="id_categoria" id="productoCategoria" class="form-input" required>
                                <option value="">Seleccionar Categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id_categoria']; ?>">
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Tiempo de Preparación (min)</label>
                            <input type="number" name="tiempo_preparacion" id="productoTiempo" class="form-input" 
                                   min="1" max="120" value="15">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Estado</label>
                            <select name="estado" id="productoEstado" class="form-input" required>
                                <option value="disponible">Disponible</option>
                                <option value="no_disponible">No Disponible</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-divider"></div>
                
                <div class="form-section">
                    <div class="form-section-title">Imagen del Producto</div>
                    
                    <div class="form-group">
                        <input type="file" name="imagen" id="productoImagen" class="form-input" 
                               accept="image/*">
                        <small class="form-help">Formatos: JPG, PNG, GIF. Máx: 2MB</small>
                        <div id="imagenActual" class="imagen-actual" style="display: none;">
                            <p>Imagen actual:</p>
                            <img id="imagenActualSrc" src="" style="max-width: 200px; margin-top: 0.5rem;">
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="submitProductoBtn">
                        <i class="fas fa-save"></i>
                        <span id="submitProductoText">Guardar Producto</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para categorías -->
<div class="modal" id="categoriaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="categoriaModalTitle">Nueva Categoría</h3>
            <button class="close-modal">&times;</button>
        </div>
        
        <div class="modal-form-container">
            <form method="POST" action="includes/actions.php" class="modal-form" id="categoriaForm">
                <input type="hidden" name="action" id="categoriaAction" value="agregar_categoria">
                <input type="hidden" name="id_categoria" id="id_categoria">
                
                <div class="form-section">
                    <div class="form-section-title">Información de la Categoría</div>
                    
                    <div class="form-group">
                        <label class="form-label">Nombre de la Categoría</label>
                        <input type="text" name="nombre" id="categoriaNombre" class="form-input" required 
                               placeholder="Ej: Entradas, Platos Principales, Postres...">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="categoriaDescripcion" class="form-input" rows="3" 
                                  placeholder="Describe la categoría..."></textarea>
                    </div>
                </div>
                
                <div class="form-divider"></div>
                
                <div class="form-section">
                    <div class="form-section-title">Estado</div>
                    
                    <div class="form-group">
                        <select name="estado" id="categoriaEstado" class="form-input" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span id="submitCategoriaText">Guardar Categoría</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.menu-controls {
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

.search-box {
    position: relative;
    min-width: 300px;
}

.search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
}

.search-box input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 2px solid rgba(102, 126, 234, 0.1);
    border-radius: var(--border-radius-sm);
    font-size: 1rem;
    transition: var(--transition);
    background: rgba(17, 17, 17, 0.8);
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(17, 17, 17, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: var(--primary-gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: #d4d4d4;
    font-size: 2rem;
}

.empty-state h3 {
    margin-bottom: 0.5rem;
    color: var(--color-white);
}

.empty-state p {
    color: var(--color-muted);
    margin-bottom: 2rem;
}

.empty-category {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 2rem;
    background: rgba(17, 17, 17, 0.8);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.empty-category-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: #d4d4d4;
    font-size: 1.5rem;
}

.empty-category p {
    color: var(--color-muted);
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border-radius: var(--border-radius-sm);
}

.categoria-section {
    margin-bottom: 3rem;
}

.categoria-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid rgba(102, 126, 234, 0.2);
}

.categoria-title {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.productos-count {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.productos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.producto-card {
    background: rgba(17, 17, 17, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
    position: relative;
}

.producto-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-medium);
}

.producto-image {
    position: relative;
    height: 200px;
    background: var(--primary-gradient);
    overflow: hidden;
}

.producto-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4d4d4;
    font-size: 3rem;
}

.producto-badges {
    position: absolute;
    top: 1rem;
    right: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge.unavailable {
    background: rgba(231, 76, 60, 0.9);
    color: #d4d4d4;
}

.badge.time {
    background: rgba(52, 152, 219, 0.9);
    color: #d4d4d4;
}

/* Botones de acción en la imagen */
.producto-image-actions {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    opacity: 0;
    transition: var(--transition);
}

.producto-card:hover .producto-image-actions {
    opacity: 1;
}

.btn-action {
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 8px;
    color: #d4d4d4;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}

.btn-action:hover {
    transform: scale(1.1);
}

.btn-action.edit { 
    background: var(--primary-gradient); 
}
.btn-action.available { 
    background: var(--success-gradient); 
}
.btn-action.unavailable { 
    background: var(--warning-gradient); 
}
.btn-action.delete { 
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); 
}

.producto-content {
    padding: 1.5rem;
}

.producto-name {
    margin: 0 0 0.5rem 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-white);
}

.producto-desc {
    margin: 0 0 1rem 0;
    color: var(--color-muted);
    font-size: 0.875rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.producto-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.producto-precio {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-white);
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
    .menu-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .controls-left {
        justify-content: center;
    }
    
    .search-box {
        min-width: auto;
    }
    
    .productos-grid {
        grid-template-columns: 1fr;
    }
    
    .producto-image-actions {
        opacity: 1;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
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
// JavaScript completo aquí (el mismo que antes)
document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const addProductoBtn = document.getElementById('addProductoBtn');
    const addCategoriaBtn = document.getElementById('addCategoriaBtn');
    const addFirstProductoBtn = document.getElementById('addFirstProductoBtn');
    const addFirstCategoriaBtn = document.getElementById('addFirstCategoriaBtn');
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
    [addCategoriaBtn, addFirstCategoriaBtn].forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function() {
                resetCategoriaForm();
                categoriaModal.classList.add('active');
            });
        }
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
    
    // Search functionality
    const searchInput = document.getElementById('searchProductos');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const productoCards = document.querySelectorAll('.producto-card');
            const categoriaSections = document.querySelectorAll('.categoria-section');

            let hasVisibleProducts = false;

            categoriaSections.forEach(section => {
                let hasVisibleInSection = false;
                const productosInSection = section.querySelectorAll('.producto-card');

                productosInSection.forEach(card => {
                    const productName = card.getAttribute('data-nombre');
                    if (productName.includes(searchTerm)) {
                        card.style.display = 'block';
                        hasVisibleInSection = true;
                        hasVisibleProducts = true;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Also check if category name matches
                const categoryName = section.getAttribute('data-categoria').toLowerCase();
                if (categoryName.includes(searchTerm)) {
                    hasVisibleInSection = true;
                }

                // Show/hide entire category section
                section.style.display = hasVisibleInSection ? 'block' : 'none';
            });
        });
    }

    // Add product to specific category buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-product-to-category') || e.target.closest('.add-product-to-category')) {
            const button = e.target.classList.contains('add-product-to-category') ? e.target : e.target.closest('.add-product-to-category');
            const categoriaId = button.getAttribute('data-categoria-id');
            resetProductoForm();
            document.getElementById('productoCategoria').value = categoriaId;
            productoModal.classList.add('active');
        }
    });
    
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
        
        const response = await fetch(`includes/actions.php?action=obtener_producto&id=${idProducto}`);
        const resultado = await response.json();
        
        console.log('Respuesta obtener producto:', resultado);
        
        if (resultado.success) {
            const producto = resultado.data;
            
            document.getElementById('productoModalTitle').textContent = 'Editar Producto';
            document.getElementById('formAction').value = 'editar_producto';
            document.getElementById('id_producto').value = producto.id_producto;
            document.getElementById('productoNombre').value = producto.nombre;
            document.getElementById('productoDescripcion').value = producto.descripcion;
            document.getElementById('productoPrecio').value = producto.precio;
            document.getElementById('productoCategoria').value = producto.id_categoria;
            document.getElementById('productoTiempo').value = producto.tiempo_preparacion;
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
        showNotification('Error al cargar el producto', 'error');
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
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(resultado.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al cambiar el estado del producto', 'error');
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
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(resultado.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al eliminar el producto', 'error');
        }
    }
}

async function submitProductoForm() {
    const form = document.getElementById('productoForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitProductoBtn');
    
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
        showNotification('Error al guardar el producto', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> ' + document.getElementById('submitProductoText').textContent;
    }
}

async function submitCategoriaForm() {
    const form = document.getElementById('categoriaForm');
    const formData = new FormData(form);
    const submitBtn = document.querySelector('#categoriaForm button[type="submit"]');
    
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
        showNotification('Error al guardar la categoría', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> ' + document.getElementById('submitCategoriaText').textContent;
    }
}

// Función de notificación si no existe window.restaurantApp
function showNotification(message, type = 'info') {
    if (window.restaurantApp && typeof window.restaurantApp.showNotification === 'function') {
        window.restaurantApp.showNotification(message, type);
    } else {
        // Notificación simple si no existe el sistema de notificaciones
        alert(`${type.toUpperCase()}: ${message}`);
    }
}
</script>