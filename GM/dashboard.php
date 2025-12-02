<?php
session_start();
include 'db.php';

if(!isset($_SESSION['usuario_id'])){
    header("Location: index.php");
    exit;
}

$nombre = $_SESSION['nombre'];
$usuario_id = $_SESSION['usuario_id'];

// Traer datos del usuario desde la tabla usuarios
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$foto = $user['foto'] ? $user['foto'] : "https://cdn-icons-png.flaticon.com/512/1077/1077114.png";

// Datos dinámicos para tarjetas
$fecha_hoy = date('Y-m-d');

// Calorías consumidas hoy (usando la tabla registro_comidas)
$calorias = 0;
$sql_calorias = "SELECT SUM(calorias) AS total FROM registro_comidas WHERE usuario_id = ? AND DATE(fecha_registro) = CURDATE()";
$stmt_calorias = $conn->prepare($sql_calorias);
$stmt_calorias->bind_param("i", $usuario_id);
$stmt_calorias->execute();
$result_calorias = $stmt_calorias->get_result();
if($result_calorias->num_rows > 0){
    $row = $result_calorias->fetch_assoc();
    $calorias = $row['total'] ? $row['total'] : 0;
}
$stmt_calorias->close();

// Calorías quemadas hoy (si tienes tabla ejercicios)
$calorias_quemadas = 0;
$sql_quemadas = "SELECT SUM(calorias_quemadas) AS total FROM ejercicios WHERE usuario_id = ? AND DATE(fecha_registro) = CURDATE()";
$stmt_quemadas = $conn->prepare($sql_quemadas);
$stmt_quemadas->bind_param("i", $usuario_id);
$stmt_quemadas->execute();
$result_quemadas = $stmt_quemadas->get_result();
if($result_quemadas && $result_quemadas->num_rows > 0){
    $row_ejercicio = $result_quemadas->fetch_assoc();
    $calorias_quemadas = $row_ejercicio['total'] ? $row_ejercicio['total'] : 0;
}
$stmt_quemadas->close();

// Meta diaria y progreso
$meta_calorias = 2000;
$meta_peso = 70;
$tipo_meta = 'mantener';

$sql_meta = "SELECT meta_calorias, meta_peso, tipo_meta FROM metas WHERE usuario_id = ? ORDER BY fecha_creacion DESC LIMIT 1";
$stmt_meta = $conn->prepare($sql_meta);
$stmt_meta->bind_param("i", $usuario_id);
$stmt_meta->execute();
$result_meta = $stmt_meta->get_result();
if($result_meta->num_rows > 0){
    $meta = $result_meta->fetch_assoc();
    $meta_calorias = $meta['meta_calorias'];
    $meta_peso = $meta['meta_peso'];
    $tipo_meta = $meta['tipo_meta'];
}
$stmt_meta->close();

// Calcular progreso
$progreso = 0;
if($meta_calorias > 0){
    $progreso = min(round(($calorias / $meta_calorias) * 100), 100);
}

// Peso actual del usuario
$peso_actual = $user['peso'] ?? 'No registrado';

// Notificaciones no leídas
$notificaciones_count = 0;
$sql_notif = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ? AND leido = FALSE";
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param("i", $usuario_id);
$stmt_notif->execute();
$result_notif = $stmt_notif->get_result();
if($result_notif->num_rows > 0){
    $notif_data = $result_notif->fetch_assoc();
    $notificaciones_count = $notif_data['total'];
}
$stmt_notif->close();

// Obtener notificaciones recientes
$notificaciones = $conn->query("SELECT * FROM notificaciones WHERE usuario_id='$usuario_id' ORDER BY fecha_creacion DESC LIMIT 5");

// Marcar notificaciones como leídas si se solicita
if(isset($_POST['marcar_leidas'])) {
    $conn->query("UPDATE notificaciones SET leido = TRUE WHERE usuario_id='$usuario_id' AND leido = FALSE");
    $notificaciones_count = 0;
    echo "<script>window.location.href = 'dashboard.php';</script>";
}

// Datos para gráfico de la semana
$fechas = [];
$caloriasSemana = [];
$caloriasQuemadasSemana = [];

for($i = 6; $i >= 0; $i--){
    $d = date('Y-m-d', strtotime("-$i days"));
    $total_dia = 0;
    $total_quemadas = 0;
    
    // Calorías consumidas
    $res3 = $conn->query("SELECT SUM(calorias) AS total FROM registro_comidas WHERE usuario_id='$usuario_id' AND DATE(fecha_registro)='$d'");
    if($res3 && $res3->num_rows > 0){
        $row_dia = $res3->fetch_assoc();
        $total_dia = $row_dia['total'] ? $row_dia['total'] : 0;
    }
    
    // Calorías quemadas (si existe la tabla ejercicios)
    $res4 = $conn->query("SELECT SUM(calorias_quemadas) AS total FROM ejercicios WHERE usuario_id='$usuario_id' AND DATE(fecha_registro)='$d'");
    if($res4 && $res4->num_rows > 0){
        $row_quemadas = $res4->fetch_assoc();
        $total_quemadas = $row_quemadas['total'] ? $row_quemadas['total'] : 0;
    }
    
    $fechas[] = date('D', strtotime($d));
    $caloriasSemana[] = $total_dia;
    $caloriasQuemadasSemana[] = $total_quemadas;
}

// Historial de consumo - SIN COLUMNA 'alimento'
$historial = $conn->query("
    SELECT fecha_registro, calorias 
    FROM registro_comidas 
    WHERE usuario_id='$usuario_id' 
    ORDER BY fecha_registro DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard FIBGEN</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

.header .user-info { 
    display:flex; 
    align-items:center; 
    gap:12px; 
    cursor:pointer; 
    position:relative; 
}

.header .user-info img { 
    width:55px; 
    height:55px; 
    border-radius:50%; 
    border: 2px solid #203A43; 
    object-fit: cover; 
}

/* Estilos para la campana de notificaciones */
.notification-container {
    position: relative;
    margin-right: 20px;
}

.notification-bell {
    position: relative;
    background: #203A43;
    color: white;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: 0.3s;
    font-size: 20px;
}

.notification-bell:hover {
    background: #2C5364;
    transform: scale(1.1);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #FF4757;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.notification-dropdown {
    position: absolute;
    top: 60px;
    right: 0;
    width: 350px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    z-index: 1000;
    display: none;
    border: 1px solid #e0e0e0;
}

.notification-dropdown.active {
    display: block;
    animation: fadeInDown 0.3s ease;
}

.notification-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #203A43;
    color: white;
    border-radius: 15px 15px 0 0;
}

.notification-header h4 {
    margin: 0;
    color: #FFD700;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: 0.3s;
}

.notification-item:hover {
    background: #f8f9fa;
}

.notification-item.unread {
    background: #f0f7ff;
    border-left: 4px solid #203A43;
}

.notification-title {
    font-weight: 600;
    color: #203A43;
    margin-bottom: 5px;
}

.notification-message {
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
}

.notification-time {
    color: #999;
    font-size: 12px;
}

.notification-footer {
    padding: 15px 20px;
    border-top: 1px solid #e0e0e0;
    text-align: center;
}

.btn-mark-read {
    background: #203A43;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
    font-size: 14px;
}

.btn-mark-read:hover {
    background: #2C5364;
}

.no-notifications {
    padding: 30px 20px;
    text-align: center;
    color: #999;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.cards { 
    display:grid; 
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
    gap:25px; 
    margin-bottom:40px; 
}

.card { 
    background: linear-gradient(145deg, #F0F2F5, #E8EDF2); 
    padding:25px 20px; 
    border-radius:25px; 
    transition:0.3s; 
    position:relative; 
    overflow:hidden; 
    box-shadow: 0 8px 20px rgba(0,0,0,0.05); 
}

.card:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 12px 25px rgba(0,0,0,0.1); 
}

.card h3 { 
    font-size:18px; 
    margin-bottom:12px; 
    color:#203A43; 
    font-weight:600; 
}

.card p { 
    font-size:26px; 
    font-weight:700; 
    color:#203A43; 
}

.card .icon { 
    position:absolute; 
    top:15px; 
    right:20px; 
    font-size:40px; 
    color:#FFD700; 
    opacity:0.2; 
}

.progress-bar { 
    width:100%; 
    height:8px; 
    background:#e0e0e0; 
    border-radius:10px; 
    margin-top:10px; 
    overflow:hidden; 
}

.progress-fill { 
    height:100%; 
    background:linear-gradient(90deg, #4CAF50, #8BC34A); 
    border-radius:10px; 
}

.progress-fill.warning { 
    background:linear-gradient(90deg, #FF9800, #FFC107); 
}

.progress-fill.danger { 
    background:linear-gradient(90deg, #F44336, #FF5252); 
}

.chart-container, .table-container, .logros-container { 
    background: linear-gradient(135deg, #F0F2F5, #E8EDF2); 
    padding:25px; 
    border-radius:25px; 
    margin-bottom:40px; 
    box-shadow: 0 8px 20px rgba(0,0,0,0.05); 
}

table { 
    width:100%; 
    border-collapse: collapse; 
    color:#203A43; 
}

table th, table td { 
    padding:12px 15px; 
    text-align:left; 
}

table th { 
    background:#203A43; 
    color:#FFD700; 
}

.logros-grid { 
    display:grid; 
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
    gap:20px; 
}

.logro-card { 
    background:white; 
    padding:20px; 
    border-radius:15px; 
    border-left:4px solid #FFD700; 
}

.logro-card h4 { 
    color:#203A43; 
    margin-bottom:10px; 
}

.logro-card p { 
    color:#666; 
    font-size:14px; 
}

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
    .cards { 
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); 
    }
    .notification-dropdown {
        width: 280px;
        right: -80px;
    }
}
</style>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-dumbbell"></i> FIBGEN</h2>
    <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="alimentos.php"><i class="fas fa-utensils"></i> Alimentos</a>
    <a href="ejercicios.php"><i class="fas fa-running"></i> Ejercicios</a>
    <a href="metas.php"><i class="fas fa-bullseye"></i> Metas</a>
    <a href="progreso.php"><i class="fas fa-chart-line"></i> Progreso</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Dashboard</h1>
        <div style="display: flex; align-items: center; gap: 20px;">
            <!-- Campana de notificaciones -->
            <div class="notification-container">
                <div class="notification-bell" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <?php if($notificaciones_count > 0): ?>
                        <div class="notification-badge"><?php echo $notificaciones_count; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h4><i class="fas fa-bell"></i> Notificaciones</h4>
                        <?php if($notificaciones_count > 0): ?>
                            <form method="POST" style="margin: 0;">
                                <button type="submit" name="marcar_leidas" class="btn-mark-read">
                                    <i class="fas fa-check"></i> Marcar todas
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <div class="notification-list">
                        <?php if($notificaciones && $notificaciones->num_rows > 0): ?>
                            <?php while($notif = $notificaciones->fetch_assoc()): ?>
                                <div class="notification-item <?php echo $notif['leido'] ? '' : 'unread'; ?>">
                                    <div class="notification-title">
                                        <i class="fas fa-<?php echo $notif['leido'] ? 'envelope-open' : 'envelope'; ?>"></i>
                                        <?php echo htmlspecialchars($notif['titulo']); ?>
                                    </div>
                                    <div class="notification-message">
                                        <?php echo htmlspecialchars($notif['mensaje']); ?>
                                    </div>
                                    <div class="notification-time">
                                        <?php echo date('d/m/Y H:i', strtotime($notif['fecha_creacion'])); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-notifications">
                                <i class="fas fa-bell-slash" style="font-size: 40px; margin-bottom: 10px;"></i>
                                <p>No hay notificaciones</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="notification-footer">
                        <a href="notificaciones.php" style="color: #203A43; text-decoration: none; font-weight: 600;">
                            Ver todas las notificaciones
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información del usuario -->
            <div class="user-info">
                <img src="<?php echo $foto; ?>" alt="Usuario">
                <span><?php echo htmlspecialchars($nombre); ?></span>
            </div>
        </div>
    </div>

    <!-- Sección de Cards -->
    <div class="cards">
        <div class="card">
            <div class="icon"><i class="fas fa-fire"></i></div>
            <h3>Calorías consumidas</h3>
            <p id="calorias-consumidas"><?php echo $calorias; ?> kcal</p>
            <div class="progress-bar">
                <div class="progress-fill <?php echo $progreso > 100 ? 'danger' : ($progreso > 80 ? 'warning' : ''); ?>" 
                     id="progress-fill" style="width: <?php echo min($progreso, 100); ?>%"></div>
            </div>
            <small id="progreso-text"><?php echo $progreso; ?>% de tu meta (<?php echo $calorias; ?>/<?php echo $meta_calorias; ?> kcal)</small>
        </div>
        <div class="card">
            <div class="icon"><i class="fas fa-burn"></i></div>
            <h3>Calorías quemadas</h3>
            <p id="calorias-quemadas"><?php echo $calorias_quemadas; ?> kcal</p>
        </div>
        <div class="card">
            <div class="icon"><i class="fas fa-bullseye"></i></div>
            <h3>Meta diaria</h3>
            <p id="meta-calorias"><?php echo $meta_calorias; ?> kcal</p>
        </div>
        <div class="card">
            <div class="icon"><i class="fas fa-weight"></i></div>
            <h3>Peso actual</h3>
            <p><?php echo is_numeric($peso_actual) ? $peso_actual . ' kg' : $peso_actual; ?></p>
        </div>
    </div>

    <!-- Gráfico de progreso -->
    <div class="chart-container">
        <h3>Progreso de calorías (Últimos 7 días)</h3>
        <canvas id="caloriasChart"></canvas>
    </div>

    <!-- Historial de consumo (VERSIÓN SIMPLIFICADA) -->
    <div class="table-container">
        <h3>Historial de Consumo Reciente</h3>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Calorías</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if($historial && $historial->num_rows > 0){
                while($fila = $historial->fetch_assoc()){
                    echo "<tr>
                        <td>".date('d/m/Y H:i', strtotime($fila['fecha_registro']))."</td>
                        <td>".$fila['calorias']." kcal</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No hay registros de consumo hoy.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Función para mostrar/ocultar notificaciones
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('active');
}

// Cerrar notificaciones al hacer click fuera
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notificationDropdown');
    const bell = document.querySelector('.notification-bell');
    
    if (!bell.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('active');
    }
});

// Función para actualizar calorías automáticamente
function actualizarCaloriasDashboard() {
    fetch('actualizar_calorias.php')
        .then(response => response.json())
        .then(data => {
            if (!data.error) {
                // Actualizar calorías consumidas
                document.getElementById('calorias-consumidas').textContent = data.calorias_consumidas + ' kcal';
                
                // Actualizar calorías quemadas
                document.getElementById('calorias-quemadas').textContent = data.calorias_quemadas + ' kcal';
                
                // Actualizar meta de calorías
                document.getElementById('meta-calorias').textContent = data.meta_calorias + ' kcal';
                
                // Actualizar barra de progreso
                const progressFill = document.getElementById('progress-fill');
                progressFill.style.width = data.progreso + '%';
                
                // Cambiar color de la barra según el progreso
                if (data.progreso > 100) {
                    progressFill.className = 'progress-fill danger';
                } else if (data.progreso > 80) {
                    progressFill.className = 'progress-fill warning';
                } else {
                    progressFill.className = 'progress-fill';
                }
                
                // Actualizar texto de progreso
                document.getElementById('progreso-text').textContent = 
                    data.progreso + '% de tu meta (' + data.calorias_consumidas + '/' + data.meta_calorias + ' kcal)';
                
                console.log('Dashboard actualizado:', data);
            }
        })
        .catch(error => {
            console.error('Error al actualizar dashboard:', error);
        });
}

// Actualizar cada 5 segundos
setInterval(actualizarCaloriasDashboard, 5000);

// Actualizar cuando la página gana foco
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        actualizarCaloriasDashboard();
    }
});

// Actualizar inmediatamente al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    actualizarCaloriasDashboard();
});

// Chart dinámico
const ctx = document.getElementById('caloriasChart').getContext('2d');
const caloriasChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($fechas); ?>,
        datasets: [{
            label: 'Calorías consumidas',
            data: <?php echo json_encode($caloriasSemana); ?>,
            borderColor: '#203A43',
            borderWidth: 2,
            fill: true,
            backgroundColor: 'rgba(32,58,67,0.1)',
            tension: 0.4,
            pointBackgroundColor: '#203A43'
        }, {
            label: 'Calorías quemadas',
            data: <?php echo json_encode($caloriasQuemadasSemana); ?>,
            borderColor: '#FF4757',
            borderWidth: 2,
            fill: false,
            tension: 0.4,
            pointBackgroundColor: '#FF4757'
        }]
    },
    options:{
        responsive:true,
        scales:{ y:{ beginAtZero:true } }
    }
});
</script>

</body>
</html>
<?php $conn->close(); ?>