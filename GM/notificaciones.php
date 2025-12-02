<?php
session_start();
include 'db.php';

if(!isset($_SESSION['usuario_id'])){
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre = $_SESSION['nombre'];

// Obtener todas las notificaciones del usuario
$notificaciones = $conn->query("
    SELECT * FROM notificaciones 
    WHERE usuario_id = '$usuario_id' 
    ORDER BY fecha_creacion DESC
");

// Contar notificaciones no leídas
$notificaciones_no_leidas = $conn->query("
    SELECT COUNT(*) as total FROM notificaciones 
    WHERE usuario_id = '$usuario_id' AND leido = FALSE
")->fetch_assoc()['total'];

// Marcar notificaciones como leídas si se solicita
if(isset($_POST['marcar_todas_leidas'])) {
    $conn->query("UPDATE notificaciones SET leido = TRUE WHERE usuario_id = '$usuario_id'");
    $notificaciones_no_leidas = 0;
    // Recargar la página para actualizar el estado
    echo "<script>window.location.href = 'notificaciones.php';</script>";
}

// Marcar una notificación específica como leída
if(isset($_POST['marcar_leida'])) {
    $notificacion_id = $_POST['notificacion_id'];
    $conn->query("UPDATE notificaciones SET leido = TRUE WHERE id = '$notificacion_id' AND usuario_id = '$usuario_id'");
    // Recargar la página para actualizar el estado
    echo "<script>window.location.href = 'notificaciones.php';</script>";
}

// Eliminar una notificación
if(isset($_POST['eliminar_notificacion'])) {
    $notificacion_id = $_POST['notificacion_id'];
    $conn->query("DELETE FROM notificaciones WHERE id = '$notificacion_id' AND usuario_id = '$usuario_id'");
    // Recargar la página para actualizar la lista
    echo "<script>window.location.href = 'notificaciones.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis Notificaciones - FIBGEN</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
* { 
    margin:0; 
    padding:0; 
    box-sizing:border-box; 
    font-family: 'Inter', sans-serif; 
}

body { 
    display:flex; 
    min-height:100vh; 
    background: #F8F9FA; 
    color: #333; 
}

.sidebar { 
    width: 260px; 
    background: linear-gradient(135deg, #0F2027, #203A43, #2C5364); 
    color: #fff; 
    display: flex; 
    flex-direction: column; 
    padding: 35px 25px; 
    box-shadow: 5px 0 25px rgba(0,0,0,0.1); 
}

.sidebar h2 { 
    font-family:'Playfair Display', serif; 
    font-size: 28px; 
    color: #FFD700; 
    margin-bottom: 50px; 
    text-align:center; 
    letter-spacing: 0.5px; 
}

.sidebar h2 i { 
    margin-right:10px; 
    vertical-align:middle; 
}

.sidebar a { 
    color: #fff; 
    text-decoration: none; 
    margin: 15px 0; 
    font-size: 16px; 
    display:flex; 
    align-items:center; 
    padding: 14px 18px; 
    border-radius: 14px; 
    transition: 0.3s; 
    font-weight:500; 
}

.sidebar a i { 
    margin-right: 14px; 
    font-size: 18px; 
}

.sidebar a:hover { 
    background: rgba(255, 215, 0, 0.15); 
    color: #FFD700; 
    transform: translateX(5px); 
}

.sidebar a.active { 
    background: rgba(255, 215, 0, 0.2); 
    color: #FFD700; 
}

.main-content { 
    flex:1; 
    padding: 45px 60px; 
    overflow-y:auto; 
    background: #fff; 
    border-top-left-radius: 35px; 
    border-bottom-left-radius: 35px; 
    box-shadow: -5px 0 25px rgba(0,0,0,0.05); 
}

.header { 
    display:flex; 
    justify-content:space-between; 
    align-items:center; 
    margin-bottom:40px; 
}

.header h1 { 
    font-size: 38px; 
    color: #203A43; 
    font-weight:700; 
    font-family:'Playfair Display', serif; 
}

/* Estilos para las notificaciones */
.notifications-container {
    background: linear-gradient(135deg, #F0F2F5, #E8EDF2);
    padding: 40px;
    border-radius: 25px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}

.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e0e0e0;
}

.notifications-count {
    background: #203A43;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
}

.btn-mark-all {
    background: #FFD700;
    color: #203A43;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-mark-all:hover {
    background: #FFC107;
    transform: translateY(-2px);
}

.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notification-item {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: 0.3s;
    border-left: 5px solid;
    position: relative;
}

.notification-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.notification-item.unread {
    background: #f0f7ff;
    border-left-color: #203A43;
}

.notification-item.read {
    border-left-color: #6c757d;
    opacity: 0.8;
}

/* Colores según el tipo de notificación */
.notification-item.info { border-left-color: #17a2b8; }
.notification-item.success { border-left-color: #28a745; }
.notification-item.warning { border-left-color: #ffc107; }
.notification-item.danger { border-left-color: #dc3545; }
.notification-item.system { border-left-color: #6c757d; }

.notification-header-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.notification-title {
    font-size: 18px;
    font-weight: 600;
    color: #203A43;
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification-type {
    background: #203A43;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.notification-message {
    color: #555;
    line-height: 1.6;
    margin-bottom: 15px;
}

.notification-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}

.notification-time {
    color: #888;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.notification-actions {
    display: flex;
    gap: 10px;
}

.btn-action {
    background: transparent;
    border: 1px solid #ddd;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    transition: 0.3s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-mark-read {
    color: #28a745;
    border-color: #28a745;
}

.btn-mark-read:hover {
    background: #28a745;
    color: white;
}

.btn-delete {
    color: #dc3545;
    border-color: #dc3545;
}

.btn-delete:hover {
    background: #dc3545;
    color: white;
}

.no-notifications {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-notifications i {
    font-size: 60px;
    margin-bottom: 20px;
    color: #ddd;
}

.no-notifications h3 {
    color: #888;
    margin-bottom: 10px;
}

/* Iconos según tipo */
.type-icon-info { color: #17a2b8; }
.type-icon-success { color: #28a745; }
.type-icon-warning { color: #ffc107; }
.type-icon-danger { color: #dc3545; }
.type-icon-system { color: #6c757d; }

@media (max-width: 768px) {
    body { 
        flex-direction:column; 
    }
    .sidebar { 
        width:100%; 
        flex-direction:row; 
        overflow-x:auto; 
        padding:20px 15px; 
    }
    .sidebar a { 
        margin:0 10px; 
        white-space:nowrap; 
    }
    .main-content { 
        padding:25px; 
        border-radius:0; 
    }
    .notifications-container {
        padding: 25px;
    }
    .notification-header-item {
        flex-direction: column;
        gap: 10px;
    }
    .notification-footer {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    .notification-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-dumbbell"></i> FIBGEN</h2>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="alimentos.php"><i class="fas fa-utensils"></i> Alimentos</a>
    <a href="ejercicios.php"><i class="fas fa-running"></i> Ejercicios</a>
    <a href="metas.php"><i class="fas fa-bullseye"></i> Metas</a>
    <a href="progreso.php"><i class="fas fa-chart-line"></i> Progreso</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
    <div class="header">
        <h1><i class="fas fa-bell"></i> Mis Notificaciones</h1>
    </div>

    <div class="notifications-container">
        <div class="notifications-header">
            <div class="notifications-count">
                <i class="fas fa-inbox"></i> 
                <?php echo $notificaciones_no_leidas; ?> no leídas de <?php echo $notificaciones->num_rows; ?> total
            </div>
            
            <?php if($notificaciones_no_leidas > 0): ?>
            <form method="POST" style="margin: 0;">
                <button type="submit" name="marcar_todas_leidas" class="btn-mark-all">
                    <i class="fas fa-check-double"></i> Marcar todas como leídas
                </button>
            </form>
            <?php endif; ?>
        </div>

        <div class="notifications-list">
            <?php if($notificaciones && $notificaciones->num_rows > 0): ?>
                <?php while($notif = $notificaciones->fetch_assoc()): ?>
                    <div class="notification-item <?php echo $notif['leido'] ? 'read' : 'unread'; ?> <?php echo $notif['tipo']; ?>">
                        <div class="notification-header-item">
                            <div class="notification-title">
                                <?php 
                                $iconos = [
                                    'info' => 'fa-info-circle type-icon-info',
                                    'success' => 'fa-check-circle type-icon-success',
                                    'warning' => 'fa-exclamation-triangle type-icon-warning',
                                    'danger' => 'fa-exclamation-circle type-icon-danger',
                                    'system' => 'fa-cog type-icon-system'
                                ];
                                $icono = $iconos[$notif['tipo']] ?? 'fa-bell';
                                ?>
                                <i class="fas <?php echo $icono; ?>"></i>
                                <?php echo htmlspecialchars($notif['titulo']); ?>
                            </div>
                            <div class="notification-type">
                                <?php 
                                $tipos = [
                                    'info' => 'Informativa',
                                    'success' => 'Éxito',
                                    'warning' => 'Advertencia',
                                    'danger' => 'Urgente',
                                    'system' => 'Sistema'
                                ];
                                echo $tipos[$notif['tipo']] ?? 'Notificación';
                                ?>
                            </div>
                        </div>
                        
                        <div class="notification-message">
                            <?php echo nl2br(htmlspecialchars($notif['mensaje'])); ?>
                        </div>
                        
                        <div class="notification-footer">
                            <div class="notification-time">
                                <i class="fas fa-clock"></i>
                                <?php echo date('d/m/Y H:i', strtotime($notif['fecha_creacion'])); ?>
                            </div>
                            
                            <div class="notification-actions">
                                <?php if(!$notif['leido']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="notificacion_id" value="<?php echo $notif['id']; ?>">
                                    <button type="submit" name="marcar_leida" class="btn-action btn-mark-read">
                                        <i class="fas fa-check"></i> Marcar leída
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="notificacion_id" value="<?php echo $notif['id']; ?>">
                                    <button type="submit" name="eliminar_notificacion" class="btn-action btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar esta notificación?')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No tienes notificaciones</h3>
                    <p>Cuando recibas notificaciones, aparecerán aquí.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
<?php $conn->close(); ?>