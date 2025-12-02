<?php
$db = new Database();
$connection = $db->getConnection();

// Obtener reservas
$reservas = [];
$mesas = [];

try {
    // Obtener reservas
    $query = "
        SELECT
            r.*,
            c.nombre as cliente_nombre,
            c.telefono as cliente_telefono,
            c.email as cliente_email,
            m.numero_mesa,
            m.capacidad as mesa_capacidad,
            m.ubicacion as mesa_ubicacion
        FROM reservaciones r
        JOIN clientes c ON r.id_cliente = c.id_cliente
        JOIN mesas m ON r.id_mesa = m.id_mesa
        ORDER BY r.fecha_reservacion DESC
        LIMIT 50
    ";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener mesas disponibles
    $query = "SELECT * FROM mesas WHERE estado = 'disponible' ORDER BY numero_mesa";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Contadores por estado
$estados = [
    'pendiente' => 0,
    'confirmada' => 0,
    'completada' => 0,
    'cancelada' => 0
];

foreach ($reservas as $reserva) {
    if (isset($estados[$reserva['estado']])) {
        $estados[$reserva['estado']]++;
    }
}

// Preparar datos para el calendario
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Asegurar que el mes esté entre 1-12
if ($currentMonth < 1) {
    $currentMonth = 12;
    $currentYear--;
} elseif ($currentMonth > 12) {
    $currentMonth = 1;
    $currentYear++;
}

// Organizar reservas por fecha
$reservasPorFecha = [];
foreach ($reservas as $reserva) {
    $fecha = date('Y-m-d', strtotime($reserva['fecha_reservacion']));
    if (!isset($reservasPorFecha[$fecha])) {
        $reservasPorFecha[$fecha] = [];
    }
    $reservasPorFecha[$fecha][] = $reserva;
}

// Función para generar el calendario
function generarCalendario($year, $month, $reservasPorFecha) {
    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $lastDay = mktime(0, 0, 0, $month + 1, 0, $year);
    $daysInMonth = date('t', $firstDay);
    $startDayOfWeek = date('w', $firstDay); // 0 = Domingo, 6 = Sábado

    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    $html = '<div class="calendario-mes">';
    $html .= '<div class="dias-semana">';
    $html .= '<div>Lun</div><div>Mar</div><div>Mié</div><div>Jue</div><div>Vie</div><div>Sáb</div><div>Dom</div>';
    $html .= '</div>';

    $day = 1;
    $html .= '<div class="dias-calendario">';

    // Espacios vacíos para los días antes del primer día del mes
    for ($i = 0; $i < $startDayOfWeek; $i++) {
        if ($i === 0) $i = 7; // Ajustar para que lunes sea el primero
        $html .= '<div class="dia-vacio"></div>';
    }
    if ($startDayOfWeek === 0) { // Si es domingo
        for ($i = 0; $i < 6; $i++) {
            $html .= '<div class="dia-vacio"></div>';
        }
    }

    // Días del mes
    while ($day <= $daysInMonth) {
        $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $reservasDia = $reservasPorFecha[$currentDate] ?? [];
        $hasReservas = !empty($reservasDia);
        $isToday = ($currentDate === date('Y-m-d'));

        $class = 'dia-calendario';
        if ($hasReservas) $class .= ' con-reservas';
        if ($isToday) $class .= ' hoy';

        $html .= '<div class="' . $class . '" data-fecha="' . $currentDate . '">';
        $html .= '<div class="numero-dia">' . $day . '</div>';
        if ($hasReservas) {
            $html .= '<div class="reservas-count">' . count($reservasDia) . '</div>';
        }
        $html .= '</div>';

        $day++;
    }

    $html .= '</div></div>';
    return $html;
}
?>

<div class="page-header animate-fade-in">
    <h1 class="page-title">Gestión de Reservas</h1>
    <p class="page-subtitle">Control y seguimiento de todas las reservaciones</p>
</div>

<div class="reservas-header">
    <div class="header-actions">
        <button class="btn btn-primary" id="nuevaReservaBtn">
            <i class="fas fa-plus"></i>
            Nueva Reserva
        </button>
        <div class="stats-overview">
            <div class="stat-mini pendiente">
                <span class="stat-count"><?php echo $estados['pendiente']; ?></span>
                <span class="stat-label">Pendientes</span>
            </div>
            <div class="stat-mini confirmada">
                <span class="stat-count"><?php echo $estados['confirmada']; ?></span>
                <span class="stat-label">Confirmadas</span>
            </div>
            <div class="stat-mini completada">
                <span class="stat-count"><?php echo $estados['completada']; ?></span>
                <span class="stat-label">Completadas</span>
            </div>
            <div class="stat-mini cancelada">
                <span class="stat-count"><?php echo $estados['cancelada']; ?></span>
                <span class="stat-label">Canceladas</span>
            </div>
        </div>
    </div>
</div>

<div class="reservas-container">
    <!-- Vista de Calendario -->
    <div class="calendario-section">
        <h3 class="section-title">Calendario de Reservas</h3>
        <div class="calendario-header">
            <button class="btn-nav" id="prevMonth">&larr;</button>
            <h4 id="mesAno"><?php
                $meses = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                ];
                echo $meses[$currentMonth] . ' ' . $currentYear;
            ?></h4>
            <button class="btn-nav" id="nextMonth">&rarr;</button>
        </div>
        <div class="calendario-grid" id="calendarioGrid">
            <?php echo generarCalendario($currentYear, $currentMonth, $reservasPorFecha); ?>
        </div>
    </div>

    <!-- Lista de Reservas -->
    <div class="reservas-list-section">
        <h3 class="section-title">Lista de Reservas</h3>
        <div class="reservas-grid">
        <?php foreach ($reservas as $reserva): ?>
            <div class="reserva-card <?php echo $reserva['estado']; ?> animate-slide-in-up">
                <div class="reserva-header">
                    <div class="reserva-info">
                        <h3 class="reserva-id">Reserva #<?php echo str_pad($reserva['id_reservacion'], 4, '0', STR_PAD_LEFT); ?></h3>
                        <span class="reserva-mesa">Mesa <?php echo htmlspecialchars($reserva['numero_mesa']); ?></span>
                    </div>
                    <div class="reserva-estado-badge <?php echo $reserva['estado']; ?>">
                        <?php 
                        $estado_texto = [
                            'pendiente' => 'Pendiente',
                            'confirmada' => 'Confirmada',
                            'completada' => 'Completada',
                            'cancelada' => 'Cancelada'
                        ];
                        echo $estado_texto[$reserva['estado']] ?? $reserva['estado'];
                        ?>
                    </div>
                </div>
                
                <div class="reserva-body">
                    <div class="reserva-details">
                        <div class="detail-item">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($reserva['cliente_nombre']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($reserva['cliente_telefono']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reservacion'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <span><?php echo $reserva['numero_personas']; ?> personas</span>
                        </div>
                    </div>
                    
                    <?php if ($reserva['observaciones']): ?>
                        <div class="reserva-observaciones">
                            <strong>Observaciones:</strong>
                            <p><?php echo htmlspecialchars($reserva['observaciones']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="reserva-actions">
                    <button class="btn-action view" onclick="verDetallesReserva(<?php echo $reserva['id_reservacion']; ?>)">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-action edit" onclick="editarReserva(<?php echo $reserva['id_reservacion']; ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <?php if ($reserva['estado'] === 'pendiente'): ?>
                        <button class="btn-action success" onclick="cambiarEstadoReserva(<?php echo $reserva['id_reservacion']; ?>, 'confirmada')" title="Confirmar Reserva">
                            <i class="fas fa-check-circle"></i>
                        </button>
                    <?php endif; ?>
                    <?php if (in_array($reserva['estado'], ['pendiente', 'confirmada'])): ?>
                        <button class="btn-action danger" onclick="cambiarEstadoReserva(<?php echo $reserva['id_reservacion']; ?>, 'cancelada')" title="Cancelar Reserva">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    <?php endif; ?>
                    <?php if ($reserva['estado'] === 'confirmada'): ?>
                        <button class="btn-action info" onclick="cambiarEstadoReserva(<?php echo $reserva['id_reservacion']; ?>, 'completada')" title="Marcar como Completada">
                            <i class="fas fa-flag-checkered"></i>
                        </button>
                    <?php endif; ?>
                    <?php if (in_array($reserva['estado'], ['cancelada', 'completada'])): ?>
                        <button class="btn-action danger" onclick="eliminarReserva(<?php echo $reserva['id_reservacion']; ?>)" title="Eliminar Reserva">
                            <i class="fas fa-trash"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal para nueva reserva -->
<div class="modal" id="nuevaReservaModal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Crear Nueva Reserva</h3>
            <button class="close-modal">&times;</button>
        </div>

        <div class="modal-form-container">
            <form method="POST" action="includes/actions.php" class="modal-form" id="nuevaReservaForm">
                <input type="hidden" name="action" value="crear_reserva">

                <div class="form-section">
                    <div class="form-section-title">Información del Cliente</div>

                    <div class="form-group">
                        <label class="form-label">Nombre del Cliente</label>
                        <input type="text" name="nombre_cliente" id="nueva-nombre-cliente" class="form-input" required
                               placeholder="Ingrese el nombre del cliente">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Teléfono del Cliente</label>
                        <input type="tel" name="telefono" id="nueva-telefono" class="form-input"
                               placeholder="Ingrese el teléfono del cliente">
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="form-section">
                    <div class="form-section-title">Detalles de la Reserva</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Fecha y Hora</label>
                            <input type="datetime-local" name="fecha_reservacion" id="nueva-fecha-reservacion" class="form-input" required
                                   value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Número de Personas</label>
                            <input type="number" name="numero_personas" id="nueva-numero-personas" class="form-input" required
                                   min="1" max="20" value="2">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mesa</label>
                        <select name="id_mesa" id="nueva-id-mesa" class="form-input" required>
                            <option value="">Seleccionar Mesa</option>
                            <?php foreach ($mesas as $mesa): ?>
                                <option value="<?php echo $mesa['id_mesa']; ?>" data-capacidad="<?php echo $mesa['capacidad']; ?>">
                                    Mesa <?php echo htmlspecialchars($mesa['numero_mesa']); ?> - <?php echo $mesa['capacidad']; ?> personas (<?php echo htmlspecialchars($mesa['ubicacion']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="nueva-observaciones" class="form-input" rows="3" 
                                  placeholder="Observaciones especiales..."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Crear Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar reserva -->
<div class="modal" id="editarReservaModal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Editar Reserva <span id="editar-reserva-id"></span></h3>
            <button class="close-modal">&times;</button>
        </div>

        <div class="modal-form-container">
            <form method="POST" action="includes/actions.php" class="modal-form" id="editarReservaForm">
                <input type="hidden" name="action" value="editar_reserva">
                <input type="hidden" name="id_reservacion" id="editar-id-reservacion">

                <div class="form-section">
                    <div class="form-section-title">Información de la Reserva</div>

                    <div class="form-group">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="editar-observaciones" class="form-input" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Número de Personas</label>
                        <input type="number" name="numero_personas" id="editar-numero-personas" class="form-input" required
                               min="1" max="20">
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

<!-- Modal para detalles de reserva -->
<div class="modal" id="detallesReservaModal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Detalles de Reserva <span id="detalles-reserva-id"></span></h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body" id="detalles-reserva-content">
            <!-- Los detalles se cargarán aquí dinámicamente -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary close-modal">Cerrar</button>
        </div>
    </div>
</div>

<style>
.reservas-header {
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
    background: rgba(16, 16, 16, 0.8);
    box-shadow: var(--shadow-soft);
    min-width: 80px;
}

.stat-mini.pendiente { border-top: 4px solid #f39c12; }
.stat-mini.confirmada { border-top: 4px solid #3498db; }
.stat-mini.completada { border-top: 4px solid #27ae60; }
.stat-mini.cancelada { border-top: 4px solid #e74c3c; }

.stat-count {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-light);
}

.reservas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
}

.reserva-card {
    background: rgba(17, 17, 17, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
    border-top: 4px solid;
}

.reserva-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-medium);
}

.reserva-card.pendiente { border-top-color: #f39c12; }
.reserva-card.confirmada { border-top-color: #3498db; }
.reserva-card.completada { border-top-color: #27ae60; }
.reserva-card.cancelada { border-top-color: #e74c3c; }

.reserva-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.reserva-id {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-white);
}

.reserva-mesa {
    color: var(--text-light);
    font-size: 0.875rem;
}

.reserva-estado-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.reserva-estado-badge.pendiente { background: rgba(243, 156, 18, 0.1); color: #f39c12; }
.reserva-estado-badge.confirmada { background: rgba(52, 152, 219, 0.1); color: #3498db; }
.reserva-estado-badge.completada { background: rgba(39, 174, 96, 0.1); color: #27ae60; }
.reserva-estado-badge.cancelada { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }

.reserva-body {
    margin-bottom: 1rem;
}

.reserva-details {
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

.reserva-observaciones {
    padding: 1rem;
    background: rgba(102, 126, 234, 0.05);
    border-radius: var(--border-radius-sm);
    border: 1px solid rgba(102, 126, 234, 0.1);
}

.reserva-observaciones strong {
    color: var(--color-white);
    display: block;
    margin-bottom: 0.5rem;
}

.reserva-observaciones p {
    margin: 0;
    color: var(--text-light);
    font-size: 0.875rem;
}

.reserva-actions {
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
.btn-action.info { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.btn-action.danger { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }

.btn-action:hover {
    transform: scale(1.1);
}

/* Estilos para modales */
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

.modal-content.large {
    max-width: 800px;
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

/* Estilos del Calendario */
.calendario-section {
    margin-bottom: 3rem;
}

.section-title {
    color: var(--color-white);
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    font-weight: 600;
}

.calendario-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: rgba(17, 17, 17, 0.9);
    border-radius: var(--border-radius);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.calendario-header h4 {
    margin: 0;
    color: var(--color-white);
    font-size: 1.25rem;
}

.btn-nav {
    background: var(--primary-gradient);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: var(--transition);
}

.btn-nav:hover {
    transform: scale(1.1);
    box-shadow: var(--shadow-medium);
}

.calendario-mes {
    background: rgba(17, 17, 17, 0.9);
    border-radius: var(--border-radius);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
}

.dias-semana {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: rgba(102, 126, 234, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.dias-semana div {
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    color: var(--color-white);
    font-size: 0.875rem;
}

.dias-calendario {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.dia-calendario, .dia-vacio {
    min-height: 100px;
    padding: 0.5rem;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    position: relative;
}

.dia-vacio {
    background: rgba(17, 17, 17, 0.3);
}

.dia-calendario {
    background: rgba(17, 17, 17, 0.6);
    cursor: pointer;
    transition: var(--transition);
}

.dia-calendario:hover {
    background: rgba(102, 126, 234, 0.1);
}

.dia-calendario.con-reservas {
    background: rgba(39, 174, 96, 0.1);
    border-left: 3px solid #27ae60;
}

.dia-calendario.con-reservas:hover {
    background: rgba(39, 174, 96, 0.2);
}

.dia-calendario.hoy {
    background: rgba(102, 126, 234, 0.1);
    border: 2px solid #667eea;
}

.numero-dia {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--color-white);
    margin-bottom: 0.5rem;
}

.reservas-count {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: #27ae60;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}

.reservas-list-section {
    margin-top: 2rem;
}

/* Modal para reservas del día */
.reservas-dia-header {
    margin-bottom: 1.5rem;
}

.reservas-dia-header h4 {
    color: var(--color-white);
    margin: 0;
}

.reservas-dia-lista {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.reserva-dia-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: rgba(17, 17, 17, 0.8);
    border-radius: var(--border-radius-sm);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.reserva-dia-item.pendiente { border-left: 4px solid #f39c12; }
.reserva-dia-item.confirmada { border-left: 4px solid #3498db; }
.reserva-dia-item.completada { border-left: 4px solid #27ae60; }
.reserva-dia-item.cancelada { border-left: 4px solid #e74c3c; }

.reserva-dia-info {
    flex: 1;
}

.reserva-dia-hora {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--color-white);
    margin-bottom: 0.5rem;
}

.reserva-dia-cliente {
    color: var(--color-white);
    margin-bottom: 0.25rem;
}

.reserva-dia-mesa, .reserva-dia-estado {
    color: var(--text-light);
    font-size: 0.875rem;
}

.reserva-dia-actions {
    margin-left: 1rem;
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

.btn-secondary {
    background: rgba(0, 0, 0, 0.1);
    color: var(--text-dark);
}

.btn-secondary:hover {
    background: rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
}

.modal-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .header-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .stats-overview {
        justify-content: space-around;
    }

    .reservas-grid {
        grid-template-columns: 1fr;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }
}


@media (max-width: 768px) {
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const nuevaReservaBtn = document.getElementById('nuevaReservaBtn');
    const nuevaReservaModal = document.getElementById('nuevaReservaModal');
    const editarReservaModal = document.getElementById('editarReservaModal');
    const detallesReservaModal = document.getElementById('detallesReservaModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');

    // Nueva reserva modal
    nuevaReservaBtn.addEventListener('click', function() {
        resetNuevaReservaForm();
        nuevaReservaModal.classList.add('active');
    });

    // Close modals
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            nuevaReservaModal.classList.remove('active');
            editarReservaModal.classList.remove('active');
            detallesReservaModal.classList.remove('active');
        });
    });

    // Close modals when clicking outside
    [nuevaReservaModal, editarReservaModal, detallesReservaModal].forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Validación de capacidad de mesa
    const mesaSelect = document.getElementById('nueva-id-mesa');
    const personasInput = document.getElementById('nueva-numero-personas');

    if (mesaSelect && personasInput) {
        mesaSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const capacidad = selectedOption.getAttribute('data-capacidad');

            if (capacidad) {
                personasInput.max = capacidad;
                if (parseInt(personasInput.value) > parseInt(capacidad)) {
                    personasInput.value = capacidad;
                }
            }
        });
    }

    // Navegación del calendario
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');

    if (prevMonthBtn && nextMonthBtn) {
        prevMonthBtn.addEventListener('click', function() {
            const url = new URL(window.location);
            const currentMonth = parseInt(url.searchParams.get('month') || new Date().getMonth() + 1);
            const currentYear = parseInt(url.searchParams.get('year') || new Date().getFullYear());
            let newMonth = currentMonth - 1;
            let newYear = currentYear;
            if (newMonth < 1) {
                newMonth = 12;
                newYear--;
            }
            url.searchParams.set('month', newMonth);
            url.searchParams.set('year', newYear);
            window.location.href = url.toString();
        });

        nextMonthBtn.addEventListener('click', function() {
            const url = new URL(window.location);
            const currentMonth = parseInt(url.searchParams.get('month') || new Date().getMonth() + 1);
            const currentYear = parseInt(url.searchParams.get('year') || new Date().getFullYear());
            let newMonth = currentMonth + 1;
            let newYear = currentYear;
            if (newMonth > 12) {
                newMonth = 1;
                newYear++;
            }
            url.searchParams.set('month', newMonth);
            url.searchParams.set('year', newYear);
            window.location.href = url.toString();
        });
    }

    // Click en días del calendario
    document.addEventListener('click', function(e) {
        if (e.target.closest('.dia-calendario.con-reservas')) {
            const dia = e.target.closest('.dia-calendario');
            const fecha = dia.getAttribute('data-fecha');
            mostrarReservasDia(fecha);
        }
    });

    // Handle form submissions
    document.getElementById('nuevaReservaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitNuevaReserva();
    });

    document.getElementById('editarReservaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitEditarReserva();
    });
});

function resetNuevaReservaForm() {
    document.getElementById('nuevaReservaForm').reset();
    document.getElementById('nueva-fecha-reservacion').value = new Date().toISOString().slice(0, 16);
}

async function verDetallesReserva(idReserva) {
    try {
        const response = await fetch(`api/reservas.php/${idReserva}`);
        const result = await response.json();

        if (result.success) {
            const reserva = result.data;
            
            let detallesHtml = `
                <div class="reserva-info-section">
                    <div class="info-row">
                        <div class="info-item">
                            <strong>Cliente:</strong>
                            <div>${reserva.cliente.nombre}</div>
                            <div>${reserva.cliente.telefono}</div>
                            <div>${reserva.cliente.email}</div>
                        </div>
                        <div class="info-item">
                            <strong>Mesa:</strong>
                            <div>Mesa ${reserva.mesa.numero}</div>
                            <div>${reserva.mesa.capacidad} personas</div>
                            <div>${reserva.mesa.ubicacion}</div>
                        </div>
                        <div class="info-item">
                            <strong>Fecha y Hora:</strong>
                            <div>${new Date(reserva.fecha_reservacion).toLocaleString()}</div>
                        </div>
                        <div class="info-item">
                            <strong>Estado:</strong>
                            <div class="reserva-estado-badge ${reserva.estado}">${reserva.estado_texto}</div>
                        </div>
                        <div class="info-item">
                            <strong>Personas:</strong>
                            <div>${reserva.numero_personas}</div>
                        </div>
                    </div>
                </div>
            `;

            if (reserva.observaciones) {
                detallesHtml += `
                    <div class="form-divider"></div>
                    <div class="form-section">
                        <div class="form-section-title">Observaciones</div>
                        <div class="form-group">
                            <p>${reserva.observaciones}</p>
                        </div>
                    </div>
                `;
            }

            document.getElementById('detalles-reserva-id').textContent = `#${reserva.id}`;
            document.getElementById('detalles-reserva-content').innerHTML = detallesHtml;
            document.getElementById('detallesReservaModal').classList.add('active');
        } else {
            showNotification('Error al cargar detalles de la reserva', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar detalles de la reserva', 'error');
    }
}

async function editarReserva(idReserva) {
    try {
        const response = await fetch(`api/reservas.php/${idReserva}`);
        const result = await response.json();

        if (result.success) {
            const reserva = result.data;

            document.getElementById('editar-reserva-id').textContent = `#${reserva.id}`;
            document.getElementById('editar-id-reservacion').value = reserva.id;
            document.getElementById('editar-observaciones').value = reserva.observaciones || '';
            document.getElementById('editar-numero-personas').value = reserva.numero_personas;

            document.getElementById('editarReservaModal').classList.add('active');
        } else {
            showNotification('Error al cargar datos de la reserva', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar datos de la reserva', 'error');
    }
}

async function cambiarEstadoReserva(idReserva, nuevoEstado) {
    const estados = {
        'pendiente': 'Pendiente',
        'confirmada': 'Confirmada',
        'completada': 'Completada',
        'cancelada': 'Cancelada'
    };

    if (!confirm(`¿Está seguro de cambiar el estado de la reserva a "${estados[nuevoEstado]}"?`)) {
        return;
    }

    try {
        const response = await fetch(`api/reservas.php/${idReserva}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'cambiar_estado',
                nuevo_estado: nuevoEstado
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Estado de reserva actualizado exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al cambiar estado: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cambiar estado de la reserva', 'error');
    }
}

async function eliminarReserva(idReserva) {
    if (!confirm('¿Está seguro de eliminar esta reserva? Esta acción no se puede deshacer.')) {
        return;
    }

    try {
        const response = await fetch(`api/reservas.php/${idReserva}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Reserva eliminada exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al eliminar reserva: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al eliminar la reserva', 'error');
    }
}

function mostrarReservasDia(fecha) {
    // Filtrar reservas para la fecha seleccionada
    const reservasDia = <?php echo json_encode($reservasPorFecha); ?>[fecha] || [];

    if (reservasDia.length === 0) {
        showNotification('No hay reservas para esta fecha', 'info');
        return;
    }

    // Crear contenido para el modal
    let contenido = `<div class="reservas-dia-header">
        <h4>Reservas para ${new Date(fecha).toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        })}</h4>
    </div>`;

    contenido += '<div class="reservas-dia-lista">';
    reservasDia.forEach(reserva => {
        const estadoTexto = {
            'pendiente': 'Pendiente',
            'confirmada': 'Confirmada',
            'completada': 'Completada',
            'cancelada': 'Cancelada'
        }[reserva.estado] || reserva.estado;

        contenido += `
            <div class="reserva-dia-item ${reserva.estado}">
                <div class="reserva-dia-info">
                    <div class="reserva-dia-hora">${new Date(reserva.fecha_reservacion).toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</div>
                    <div class="reserva-dia-cliente">${reserva.cliente_nombre}</div>
                    <div class="reserva-dia-mesa">Mesa ${reserva.numero_mesa} - ${reserva.numero_personas} personas</div>
                    <div class="reserva-dia-estado">${estadoTexto}</div>
                </div>
                <div class="reserva-dia-actions">
                    <button class="btn-action view" onclick="verDetallesReserva(${reserva.id_reservacion})">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        `;
    });
    contenido += '</div>';

    // Mostrar en el modal de detalles
    document.getElementById('detalles-reserva-id').textContent = `Reservas del ${new Date(fecha).toLocaleDateString('es-ES')}`;
    document.getElementById('detalles-reserva-content').innerHTML = contenido;
    document.getElementById('detallesReservaModal').classList.add('active');
}

async function submitNuevaReserva() {
    const form = document.getElementById('nuevaReservaForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando Reserva...';

    try {
        const response = await fetch('includes/actions.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Reserva creada exitosamente', 'success');
            document.getElementById('nuevaReservaModal').classList.remove('active');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al crear reserva: ' + result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Crear Reserva';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al crear reserva', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Crear Reserva';
    }
}

async function submitEditarReserva() {
    const form = document.getElementById('editarReservaForm');
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
            showNotification('Reserva actualizada exitosamente', 'success');
            document.getElementById('editarReservaModal').classList.remove('active');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al actualizar reserva: ' + result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al actualizar reserva', 'error');
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