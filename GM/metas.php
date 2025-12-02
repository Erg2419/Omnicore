<?php
session_start();
include 'db.php';

if(!isset($_SESSION['usuario_id'])){
    header("Location: index.php");
    exit;
}

$nombre = $_SESSION['nombre'];
$usuario_id = $_SESSION['usuario_id'];

// Traer foto del usuario
$sql = "SELECT foto FROM usuarios WHERE id='$usuario_id'";
$result = $conn->query($sql);
if($result->num_rows == 1){
    $user = $result->fetch_assoc();
    $foto = $user['foto'] ? $user['foto'] : "https://cdn-icons-png.flaticon.com/512/1077/1077114.png";
} else {
    $foto = "https://cdn-icons-png.flaticon.com/512/1077/1077114.png";
}

// Procesar formulario de metas
$mensaje_meta = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['meta_calorias'])){
    $meta_calorias = intval($_POST['meta_calorias']);
    $meta_peso = floatval($_POST['meta_peso']);
    $tipo_meta = $conn->real_escape_string($_POST['tipo_meta']);
    $fecha_limite = $conn->real_escape_string($_POST['fecha_limite']);
    $recordatorio = isset($_POST['recordatorio']) ? 1 : 0;
    
    // Verificar si ya existe una meta
    $check_sql = "SELECT id FROM metas WHERE usuario_id='$usuario_id'";
    $check_result = $conn->query($check_sql);
    
    if($check_result && $check_result->num_rows > 0){
        // Actualizar meta existente
        $sql = "UPDATE metas SET 
                meta_calorias = '$meta_calorias', 
                meta_peso = '$meta_peso', 
                tipo_meta = '$tipo_meta', 
                fecha_limite = '$fecha_limite', 
                recordatorio = '$recordatorio',
                fecha_actualizacion = NOW()
                WHERE usuario_id = '$usuario_id'";
    } else {
        // Insertar nueva meta
        $sql = "INSERT INTO metas (usuario_id, meta_calorias, meta_peso, tipo_meta, fecha_limite, recordatorio, fecha_creacion, fecha_actualizacion) 
                VALUES ('$usuario_id', '$meta_calorias', '$meta_peso', '$tipo_meta', '$fecha_limite', '$recordatorio', NOW(), NOW())";
    }
    
    if($conn->query($sql) === TRUE){
        $mensaje_meta = '<div class="alert-success">¡Meta actualizada correctamente!</div>';
        
        // Crear notificación
        $conn->query("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) 
                     VALUES ('$usuario_id', 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta')");
    } else {
        $mensaje_meta = '<div class="alert-error">Error al guardar la meta: ' . $conn->error . '</div>';
    }
}

// Obtener meta actual del usuario
$meta_actual = null;
$sql_meta = "SELECT * FROM metas WHERE usuario_id='$usuario_id' ORDER BY fecha_creacion DESC LIMIT 1";
$result_meta = $conn->query($sql_meta);
if($result_meta && $result_meta->num_rows > 0){
    $meta_actual = $result_meta->fetch_assoc();
}

// Datos para mostrar progreso
$fecha_hoy = date('Y-m-d');
$calorias = 0;
$res = $conn->query("SELECT SUM(calorias) AS total FROM registro_comidas WHERE usuario_id='$usuario_id' AND DATE(fecha_registro)='$fecha_hoy'");
if($res && $res->num_rows > 0){
    $row = $res->fetch_assoc();
    $calorias = $row['total'] ? $row['total'] : 0;
}

// Usar valores de la base de datos o valores por defecto
$meta_calorias = $meta_actual['meta_calorias'] ?? 2000;
$meta_peso = $meta_actual['meta_peso'] ?? 70;
$tipo_meta = $meta_actual['tipo_meta'] ?? 'mantener';
$fecha_limite_default = $meta_actual['fecha_limite'] ?? date('Y-m-d', strtotime('+1 month'));
$recordatorio_default = $meta_actual['recordatorio'] ?? 0;

// Calcular progreso
$progreso = 0;
if($meta_calorias > 0){
    $progreso = min(round(($calorias / $meta_calorias) * 100), 100);
}

// Calcular días restantes
$dias_restantes = 0;
if($meta_actual && $meta_actual['fecha_limite']){
    $fecha_limite = new DateTime($meta_actual['fecha_limite']);
    $hoy = new DateTime();
    $dias_restantes = $hoy->diff($fecha_limite)->days;
    $dias_restantes = $fecha_limite > $hoy ? $dias_restantes : 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Metas - GM</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
body { display:flex; min-height:100vh; background: #F8F9FA; color: #333; }
.sidebar { width: 260px; background: linear-gradient(135deg, #0F2027, #203A43, #2C5364); color: #fff; display: flex; flex-direction: column; padding: 35px 25px; box-shadow: 5px 0 25px rgba(0,0,0,0.1); }
.sidebar h2 { font-family:'Playfair Display', serif; font-size: 28px; color: #FFD700; margin-bottom: 50px; text-align:center; letter-spacing: 0.5px; }
.sidebar h2 i { margin-right:10px; vertical-align:middle; }
.sidebar a { color: #fff; text-decoration: none; margin: 15px 0; font-size: 16px; display:flex; align-items:center; padding: 14px 18px; border-radius: 14px; transition: 0.3s; font-weight:500; }
.sidebar a i { margin-right: 14px; font-size: 18px; }
.sidebar a:hover { background: rgba(255, 215, 0, 0.15); color: #FFD700; transform: translateX(5px); }
.sidebar a.active { background: rgba(255, 215, 0, 0.25); color: #FFD700; }
.main-content { flex:1; padding: 45px 60px; overflow-y:auto; background: #fff; border-top-left-radius: 35px; border-bottom-left-radius: 35px; box-shadow: -5px 0 25px rgba(0,0,0,0.05); }
.header { display:flex; justify-content:space-between; align-items:center; margin-bottom:40px; }
.header h1 { font-size: 38px; color: #203A43; font-weight:700; font-family:'Playfair Display', serif; }
.header .user-info { display:flex; align-items:center; gap:12px; cursor:pointer; }
.header .user-info img { width:55px; height:55px; border-radius:50%; border: 2px solid #203A43; object-fit: cover; }
.cards { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:25px; margin-bottom:40px; }
.card { background: linear-gradient(145deg, #F0F2F5, #E8EDF2); padding:25px 20px; border-radius:25px; transition:0.3s; position:relative; overflow:hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
.card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.1); }
.card h3 { font-size:18px; margin-bottom:12px; color:#203A43; font-weight:600; }
.card p { font-size:26px; font-weight:700; color:#203A43; }
.card .icon { position:absolute; top:15px; right:20px; font-size:40px; color:#FFD700; opacity:0.2; }
.meta-container { background: linear-gradient(135deg, #F0F2F5, #E8EDF2); padding:25px; border-radius:25px; margin-bottom:40px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
.meta-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; color: #203A43; font-weight: 600; }
.form-group input, .form-group select { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: border-color 0.3s; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: #203A43; box-shadow: 0 0 0 3px rgba(32, 58, 67, 0.1); }
.checkbox-group { display: flex; align-items: center; gap: 10px; }
.checkbox-group input { width: auto; transform: scale(1.2); }
.btn-submit { background: linear-gradient(135deg, #203A43, #2C5364); color: white; border: none; padding: 15px 30px; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; width: 100%; }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(32, 58, 67, 0.3); }
.alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #c3e6cb; text-align: center; }
.alert-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb; text-align: center; }
.meta-info { background: white; padding: 20px; border-radius: 10px; margin-top: 20px; border-left: 4px solid #FFD700; }
.meta-item { display: flex; justify-content: space-between; margin-bottom: 10px; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
.meta-item:last-child { border-bottom: none; }
.meta-label { font-weight: 600; color: #203A43; }
.meta-value { color: #666; }
.dias-restantes { background: #FFD700; color: #203A43; padding: 10px 15px; border-radius: 20px; font-weight: bold; text-align: center; margin-top: 10px; }
@media (max-width: 768px) {
    .meta-form { grid-template-columns: 1fr; }
    .main-content { padding: 25px; }
}
</style>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-dumbbell"></i> FIBGEN</h2>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="alimentos.php"><i class="fas fa-utensils"></i> Alimentos</a>
    <a href="ejercicios.php"><i class="fas fa-running"></i> Ejercicios</a>
    <a href="metas.php" class="active"><i class="fas fa-bullseye"></i> Metas</a>
    <a href="progreso.php"><i class="fas fa-chart-line"></i> Progreso</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Mis Metas</h1>
        <div class="user-info">
            <img src="<?php echo $foto; ?>" alt="Usuario">
            <span><?php echo htmlspecialchars($nombre); ?></span>
        </div>
    </div>

    <!-- Sección de Cards -->
    <div class="cards">
        <div class="card">
            <div class="icon"><i class="fas fa-bullseye"></i></div>
            <h3>Meta Calórica</h3>
            <p><?php echo $meta_calorias; ?> kcal</p>
        </div>
        <div class="card">
            <div class="icon"><i class="fas fa-flag"></i></div>
            <h3>Tipo de Meta</h3>
            <p><?php 
                switch($tipo_meta) {
                    case 'perder': echo 'Perder peso'; break;
                    case 'ganar': echo 'Ganar peso'; break;
                    default: echo 'Mantener peso'; break;
                }
            ?></p>
        </div>
        <div class="card">
            <div class="icon"><i class="fas fa-calendar"></i></div>
            <h3>Días Restantes</h3>
            <p><?php echo $dias_restantes; ?> días</p>
        </div>
    </div>

    <!-- Sección de Configuración de Metas -->
    <div class="meta-container">
        <h3><i class="fas fa-bullseye"></i> Configurar Metas</h3>
        <?php echo $mensaje_meta; ?>
        
        <form method="POST" action="">
            <div class="meta-form">
                <div class="form-group">
                    <label for="meta_calorias"><i class="fas fa-fire"></i> Meta Diaria de Calorías</label>
                    <input type="number" id="meta_calorias" name="meta_calorias" 
                           value="<?php echo $meta_calorias; ?>" min="500" max="10000" required>
                </div>
                
                <div class="form-group">
                    <label for="meta_peso"><i class="fas fa-weight"></i> Meta de Peso (kg)</label>
                    <input type="number" id="meta_peso" name="meta_peso" 
                           value="<?php echo $meta_peso; ?>" min="30" max="200" step="0.1" required>
                </div>
                
                <div class="form-group">
                    <label for="tipo_meta"><i class="fas fa-flag"></i> Tipo de Meta</label>
                    <select id="tipo_meta" name="tipo_meta" required>
                        <option value="perder" <?php echo $tipo_meta == 'perder' ? 'selected' : ''; ?>>Perder Peso</option>
                        <option value="ganar" <?php echo $tipo_meta == 'ganar' ? 'selected' : ''; ?>>Ganar Peso</option>
                        <option value="mantener" <?php echo $tipo_meta == 'mantener' ? 'selected' : ''; ?>>Mantener Peso</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fecha_limite"><i class="fas fa-calendar-alt"></i> Fecha Límite</label>
                    <input type="date" id="fecha_limite" name="fecha_limite" 
                           value="<?php echo $fecha_limite_default; ?>" 
                           min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="recordatorio" name="recordatorio" 
                               <?php echo $recordatorio_default ? 'checked' : ''; ?>>
                        <label for="recordatorio"><i class="fas fa-bell"></i> Activar recordatorios</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Guardar Metas
                    </button>
                </div>
            </div>
        </form>

        <?php if($meta_actual): ?>
        <div class="meta-info">
            <h4><i class="fas fa-info-circle"></i> Tu Meta Actual</h4>
            <div class="meta-item">
                <span class="meta-label">Calorías diarias:</span>
                <span class="meta-value"><?php echo $meta_actual['meta_calorias']; ?> kcal</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Peso objetivo:</span>
                <span class="meta-value"><?php echo $meta_actual['meta_peso']; ?> kg</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Tipo de meta:</span>
                <span class="meta-value">
                    <?php 
                    switch($meta_actual['tipo_meta']){
                        case 'perder': echo 'Perder Peso'; break;
                        case 'ganar': echo 'Ganar Peso'; break;
                        case 'mantener': echo 'Mantener Peso'; break;
                    }
                    ?>
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Fecha límite:</span>
                <span class="meta-value"><?php echo date('d/m/Y', strtotime($meta_actual['fecha_limite'])); ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Recordatorios:</span>
                <span class="meta-value"><?php echo $meta_actual['recordatorio'] ? 'Activados' : 'Desactivados'; ?></span>
            </div>
            <?php if($dias_restantes > 0): ?>
                <div class="dias-restantes">
                    <i class="fas fa-clock"></i> <?php echo $dias_restantes; ?> días restantes para alcanzar tu meta
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Actualizar fecha mínima automáticamente
document.getElementById('fecha_limite').min = new Date().toISOString().split('T')[0];

// Calcular meta calórica sugerida basada en el tipo de meta
document.getElementById('tipo_meta').addEventListener('change', function() {
    const tipoMeta = this.value;
    const metaCalorias = document.getElementById('meta_calorias');
    
    switch(tipoMeta) {
        case 'perder':
            metaCalorias.value = 1800;
            break;
        case 'ganar':
            metaCalorias.value = 2500;
            break;
        case 'mantener':
            metaCalorias.value = 2000;
            break;
    }
});
</script>

</body>
</html>
<?php $conn->close(); ?>