<?php
$db = new Database();
$connection = $db->getConnection();

// Get statistics
$stats = [
    'total_mesas' => 0,
    'mesas_ocupadas' => 0,
    'ordenes_hoy' => 0,
    'ventas_hoy' => 0
];

try {
    // Total mesas
    $query = "SELECT COUNT(*) as total FROM mesas";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $stats['total_mesas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Mesas ocupadas
    $query = "SELECT COUNT(*) as total FROM mesas WHERE estado = 'ocupada'";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $stats['mesas_ocupadas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Órdenes hoy
    $query = "SELECT COUNT(*) as total FROM ordenes WHERE DATE(fecha_orden) = CURDATE()";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $stats['ordenes_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Ventas hoy
    $query = "SELECT COALESCE(SUM(total), 0) as total FROM ordenes WHERE DATE(fecha_orden) = CURDATE() AND estado = 'pagada'";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $stats['ventas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="page-header animate-fade-in">
    <h1 class="page-title">Dashboard Principal</h1>
    <p class="page-subtitle">Resumen general del restaurante</p>
</div>

<div class="dashboard-grid stagger-animate">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-chair"></i>
        </div>
        <div class="stat-value"><?php echo $stats['total_mesas']; ?></div>
        <div class="stat-label">Total Mesas</div>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-utensils"></i>
        </div>
        <div class="stat-value"><?php echo $stats['mesas_ocupadas']; ?></div>
        <div class="stat-label">Mesas Ocupadas</div>
    </div>

    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-value"><?php echo $stats['ordenes_hoy']; ?></div>
        <div class="stat-label">Órdenes Hoy</div>
    </div>

</div>

<div class="quick-actions">
    <h2 style="margin-bottom: 1rem; color: var(--text-dark);">Acciones Rápidas</h2>
    <div class="action-buttons">
        <a href="index.php?page=ordenes" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nueva Orden
        </a>
        <a href="index.php?page=reservas" class="btn btn-success">
            <i class="fas fa-calendar-plus"></i>
            Nueva Reserva
        </a>
        <a href="index.php?page=mesas" class="btn btn-warning">
            <i class="fas fa-eye"></i>
            Ver Mesas
        </a>
    </div>
</div>

<style>

.quick-actions {
    background: rgba(17, 17, 17, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
    margin-top: 2rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

</style>