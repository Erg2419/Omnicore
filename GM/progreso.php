<?php
session_start();
include 'db.php';

if(!isset($_SESSION['usuario_id'])){
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = "";
$error = "";

// Procesar registro de peso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['peso'])) {
    $peso = floatval($_POST['peso']);
    $fecha_registro = $_POST['fecha_registro'];
    $notas = trim($_POST['notas']);

    if($peso <= 0){
        $error = "El peso debe ser mayor a 0";
    } else {
        // Verificar si ya existe registro para esta fecha
        $check_sql = "SELECT id FROM progreso_peso WHERE usuario_id = ? AND fecha_registro = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("is", $usuario_id, $fecha_registro);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            // Actualizar registro existente
            $sql = "UPDATE progreso_peso SET peso = ?, notas = ? WHERE usuario_id = ? AND fecha_registro = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dsis", $peso, $notas, $usuario_id, $fecha_registro);
        } else {
            // Insertar nuevo registro
            $sql = "INSERT INTO progreso_peso (usuario_id, peso, fecha_registro, notas) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idss", $usuario_id, $peso, $fecha_registro, $notas);
        }
        
        if($stmt->execute()){
            $mensaje = "✅ Peso registrado correctamente!";
            
            // Actualizar peso en tabla usuarios
            $conn->query("UPDATE usuarios SET peso = '$peso' WHERE id = '$usuario_id'");
            
            // Verificar metas de peso
            verificarMetaPeso($conn, $usuario_id, $peso);
            
            $_POST = array();
        } else {
            $error = "❌ Error al guardar el peso: " . $conn->error;
        }
        $stmt->close();
    }
}

// Función para verificar metas de peso
function verificarMetaPeso($conn, $usuario_id, $peso_actual) {
    $meta = $conn->query("SELECT meta_peso, tipo_meta FROM metas WHERE usuario_id = '$usuario_id' ORDER BY fecha_creacion DESC LIMIT 1");
    
    if($meta && $meta->num_rows > 0) {
        $meta_data = $meta->fetch_assoc();
        $meta_peso = $meta_data['meta_peso'];
        $tipo_meta = $meta_data['tipo_meta'];
        
        $logro_alcanzado = false;
        $mensaje_logro = "";
        
        switch($tipo_meta) {
            case 'perder':
                if($peso_actual <= $meta_peso) {
                    $logro_alcanzado = true;
                    $mensaje_logro = "¡Felicidades! Has alcanzado tu meta de perder peso";
                }
                break;
            case 'ganar':
                if($peso_actual >= $meta_peso) {
                    $logro_alcanzado = true;
                    $mensaje_logro = "¡Felicidades! Has alcanzado tu meta de ganar peso";
                }
                break;
            case 'mantener':
                // Para mantener, considerar un rango de ±2kg
                if(abs($peso_actual - $meta_peso) <= 2) {
                    $logro_alcanzado = true;
                    $mensaje_logro = "¡Excelente! Estás manteniendo tu peso objetivo";
                }
                break;
        }
        
        if($logro_alcanzado) {
            $conn->query("INSERT INTO logros (usuario_id, tipo_logro, descripcion) 
                         VALUES ('$usuario_id', 'peso', '$mensaje_logro')");
            
            $conn->query("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) 
                         VALUES ('$usuario_id', 'Meta de Peso Alcanzada', '$mensaje_logro', 'meta')");
        }
    }
}

// Obtener historial de peso (últimos 30 días)
$historial_peso = $conn->query("
    SELECT * FROM progreso_peso 
    WHERE usuario_id = '$usuario_id' 
    AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ORDER BY fecha_registro DESC
");

// Obtener datos para gráfico
$peso_data = [];
$fecha_data = [];
$peso_result = $conn->query("
    SELECT peso, fecha_registro 
    FROM progreso_peso 
    WHERE usuario_id = '$usuario_id' 
    AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ORDER BY fecha_registro ASC
");

while($row = $peso_result->fetch_assoc()) {
    $peso_data[] = $row['peso'];
    $fecha_data[] = date('d/m', strtotime($row['fecha_registro']));
}

// Obtener peso actual
$peso_actual = 0;
$peso_actual_result = $conn->query("SELECT peso FROM usuarios WHERE id = '$usuario_id'");
if($peso_actual_result && $peso_actual_result->num_rows > 0) {
    $peso_actual = $peso_actual_result->fetch_assoc()['peso'];
}

// Obtener meta de peso
$meta_peso = 0;
$meta_result = $conn->query("SELECT meta_peso FROM metas WHERE usuario_id = '$usuario_id' ORDER BY fecha_creacion DESC LIMIT 1");
if($meta_result && $meta_result->num_rows > 0) {
    $meta_peso = $meta_result->fetch_assoc()['meta_peso'];
}

// Calcular progreso hacia la meta
$progreso_peso = 0;
if($peso_actual > 0 && $meta_peso > 0) {
    // Diferente cálculo según el tipo de meta
    $meta_tipo_result = $conn->query("SELECT tipo_meta FROM metas WHERE usuario_id = '$usuario_id' ORDER BY fecha_creacion DESC LIMIT 1");
    if($meta_tipo_result && $meta_tipo_result->num_rows > 0) {
        $tipo_meta = $meta_tipo_result->fetch_assoc()['tipo_meta'];
        
        switch($tipo_meta) {
            case 'perder':
                $peso_inicial = $conn->query("SELECT peso FROM progreso_peso WHERE usuario_id = '$usuario_id' ORDER BY fecha_registro ASC LIMIT 1");
                if($peso_inicial && $peso_inicial->num_rows > 0) {
                    $peso_inicial_val = $peso_inicial->fetch_assoc()['peso'];
                    $total_a_perder = $peso_inicial_val - $meta_peso;
                    $perdido = $peso_inicial_val - $peso_actual;
                    $progreso_peso = min(round(($perdido / $total_a_perder) * 100), 100);
                }
                break;
            case 'ganar':
                $peso_inicial = $conn->query("SELECT peso FROM progreso_peso WHERE usuario_id = '$usuario_id' ORDER BY fecha_registro ASC LIMIT 1");
                if($peso_inicial && $peso_inicial->num_rows > 0) {
                    $peso_inicial_val = $peso_inicial->fetch_assoc()['peso'];
                    $total_a_ganar = $meta_peso - $peso_inicial_val;
                    $ganado = $peso_actual - $peso_inicial_val;
                    $progreso_peso = min(round(($ganado / $total_a_ganar) * 100), 100);
                }
                break;
            case 'mantener':
                // Para mantener, mostrar 100% si está dentro del rango
                if(abs($peso_actual - $meta_peso) <= 2) {
                    $progreso_peso = 100;
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Progreso - GM</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
body{
    display:flex;
    min-height:100vh;
    background:linear-gradient(135deg,#f5f7fa 0%,#e4edf5 100%);
    color:#333;
}
.sidebar{
    width:280px;
    background:linear-gradient(165deg,#0F2027,#203A43,#2C5364);
    color:#fff;
    display:flex;
    flex-direction:column;
    padding:35px 25px;
    box-shadow:5px 0 25px rgba(0,0,0,0.1);
    z-index:10;
}
.sidebar h2{
    font-size:28px;
    color:#FFD700;
    margin-bottom:50px;
    text-align:center;
    font-family:'Playfair Display',serif;
}
.sidebar a{
    color:#fff;
    text-decoration:none;
    margin:12px 0;
    font-size:16px;
    display:flex;
    align-items:center;
    padding:14px 18px;
    border-radius:14px;
    transition:0.3s;
    font-weight:500;
}
.sidebar a i{margin-right:14px;font-size:18px;width:24px;text-align:center;}
.sidebar a:hover{background:rgba(255,215,0,0.15);color:#FFD700;transform:translateX(5px);}
.sidebar a.active{background:rgba(255,215,0,0.25);color:#FFD700;}
.main-content{
    flex:1;
    padding:45px 60px;
    background:#fff;
    border-top-left-radius:35px;
    border-bottom-left-radius:35px;
    overflow-y:auto;
    box-shadow:-5px 0 25px rgba(0,0,0,0.05);
}
.main-content h1{
    font-size:38px;
    color:#203A43;
    font-weight:700;
    margin-bottom:35px;
    font-family:'Playfair Display',serif;
    position:relative;
    padding-bottom:15px;
}
.main-content h1::after{
    content:'';
    position:absolute;
    bottom:0;
    left:0;
    width:80px;
    height:4px;
    background:linear-gradient(90deg,#FFD700,#FFB347);
    border-radius:2px;
}
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}
.card {
    background: linear-gradient(145deg, #F0F2F5, #E8EDF2);
    padding: 25px 20px;
    border-radius: 25px;
    transition: 0.3s;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.1);
}
.card h3 {
    font-size: 18px;
    margin-bottom: 12px;
    color: #203A43;
    font-weight: 600;
}
.card p {
    font-size: 26px;
    font-weight: 700;
    color: #203A43;
}
.card .icon {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 40px;
    color: #FFD700;
    opacity: 0.2;
}
.progress-bar {
    width: 100%;
    height: 8px;
    background: #e0e0e0;
    border-radius: 10px;
    margin-top: 10px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4CAF50, #8BC34A);
    border-radius: 10px;
}
form{
    background:#fff;
    padding:40px;
    border-radius:25px;
    box-shadow:0 12px 40px rgba(0,0,0,0.08);
    width:100%;
    border:1px solid rgba(0,0,0,0.05);
    margin-bottom:30px;
}
.form-group{margin-bottom:25px;}
label{font-weight:600;color:#203A43;display:block;margin-bottom:10px;font-size:15px;}
input,select,textarea{
    width:100%;
    padding:16px 18px;
    border-radius:14px;
    border:1.5px solid #e1e5e9;
    font-size:15px;
    transition:all 0.3s;
    background:#fafbfc;
}
input:focus,select:focus,textarea:focus{
    outline:none;
    border-color:#4dabf7;
    box-shadow:0 0 0 4px rgba(77,171,247,0.2);
    background:#fff;
}
textarea{resize:none;}
button{
    background:linear-gradient(90deg,#203A43,#2C5364);
    color:#fff;
    border:none;
    padding:18px;
    width:100%;
    border-radius:14px;
    cursor:pointer;
    font-size:17px;
    font-weight:600;
    transition:0.3s;
    box-shadow:0 6px 15px rgba(32,58,67,0.3);
}
button:hover{
    background:linear-gradient(90deg,#2C5364,#203A43);
    transform:translateY(-3px);
    box-shadow:0 10px 20px rgba(32,58,67,0.4);
}
.mensaje{
    margin-top:25px;
    padding:18px 20px;
    font-weight:600;
    text-align:center;
    border-radius:14px;
    font-size:16px;
    background:#d4edda;
    color:#155724;
    border:1px solid #c3e6cb;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}
.mensaje.error{
    background:#f8d7da;
    color:#721c24;
    border:1px solid #f5c6cb;
}
.chart-container, .table-container {
    background: #fff;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table th, table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}
table th {
    background: #203A43;
    color: #FFD700;
}
.tendencia-subida { color: #dc3545; }
.tendencia-bajada { color: #28a745; }
.tendencia-estable { color: #ffc107; }
@media(max-width:768px){
    body{flex-direction:column;}
    .sidebar{
        width:100%;
        flex-direction:row;
        overflow-x:auto;
        padding:20px 15px;
    }
    .sidebar h2{margin-bottom:0;margin-right:20px;}
    .sidebar a{margin:0 8px;white-space:nowrap;}
    .main-content{padding:30px 25px;border-radius:0;}
    .cards { grid-template-columns: 1fr; }
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
    <a href="progreso.php" class="active"><i class="fas fa-chart-line"></i> Progreso</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
    <h1>Seguimiento de Progreso</h1>
    <p>Monitoriza tu evolución y alcanza tus metas 📊</p>

    <?php if($mensaje) echo "<div class='mensaje'>$mensaje</div>"; ?>
    <?php if($error) echo "<div class='mensaje error'>$error</div>"; ?>

    <!-- Tarjetas de Resumen -->
    <div class="cards">
        <div class="card">
            <div class="icon"><i class="fas fa-weight"></i></div>
            <h3>Peso Actual</h3>
            <p><?php echo $peso_actual ? $peso_actual . ' kg' : 'No registrado'; ?></p>
        </div>
        <div class="card">
            <div class="icon"><i class="fas fa-bullseye"></i></div>
            <h3>Meta de Peso</h3>
            <p><?php echo $meta_peso ? $meta_peso . ' kg' : 'No establecida'; ?></p>
        </div>
        <div class="card">
            <div class="icon"><i class="fas fa-chart-line"></i></div>
            <h3>Progreso</h3>
            <p><?php echo $progreso_peso; ?>%</p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $progreso_peso; ?>%"></div>
            </div>
        </div>
        <div class="card">
            <div class="icon"><i class="fas fa-history"></i></div>
            <h3>Registros</h3>
            <p><?php echo $historial_peso ? $historial_peso->num_rows : '0'; ?></p>
            <small>Últimos 30 días</small>
        </div>
    </div>

    <!-- Formulario de Registro de Peso -->
    <form method="POST" action="">
        <h3><i class="fas fa-edit"></i> Registrar Peso</h3>
        <div class="form-group">
            <label for="peso">Peso (kg)</label>
            <input type="number" name="peso" id="peso" step="0.1" min="1" max="500" required 
                   value="<?php echo $peso_actual; ?>" placeholder="Ej: 68.5">
        </div>
        
        <div class="form-group">
            <label for="fecha_registro">Fecha de Registro</label>
            <input type="date" name="fecha_registro" id="fecha_registro" 
                   value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="notas">Notas (opcional)</label>
            <textarea name="notas" id="notas" rows="3" placeholder="Cómo te sientes, observaciones..."></textarea>
        </div>
        
        <button type="submit">💾 Guardar Registro</button>
    </form>

    <!-- Gráfico de Progreso -->
    <?php if(count($peso_data) > 1): ?>
    <div class="chart-container">
        <h3><i class="fas fa-chart-line"></i> Evolución del Peso (Últimos 30 días)</h3>
        <canvas id="pesoChart"></canvas>
    </div>
    <?php endif; ?>

    <!-- Historial de Peso -->
    <div class="table-container">
        <h3><i class="fas fa-history"></i> Historial de Peso</h3>
        <?php if($historial_peso && $historial_peso->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Peso</th>
                        <th>Diferencia</th>
                        <th>Tendencia</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $peso_anterior = null;
                    while($registro = $historial_peso->fetch_assoc()): 
                        $diferencia = '';
                        $tendencia = '';
                        $clase_tendencia = '';
                        
                        if($peso_anterior !== null) {
                            $diff = $registro['peso'] - $peso_anterior;
                            if($diff > 0) {
                                $diferencia = '+' . number_format($diff, 1);
                                $tendencia = '▲ Subida';
                                $clase_tendencia = 'tendencia-subida';
                            } elseif($diff < 0) {
                                $diferencia = number_format($diff, 1);
                                $tendencia = '▼ Bajada';
                                $clase_tendencia = 'tendencia-bajada';
                            } else {
                                $diferencia = '0.0';
                                $tendencia = '➡ Estable';
                                $clase_tendencia = 'tendencia-estable';
                            }
                        }
                        
                        $peso_anterior = $registro['peso'];
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($registro['fecha_registro'])); ?></td>
                            <td><strong><?php echo $registro['peso']; ?> kg</strong></td>
                            <td><?php echo $diferencia; ?></td>
                            <td class="<?php echo $clase_tendencia; ?>"><?php echo $tendencia; ?></td>
                            <td><?php echo htmlspecialchars($registro['notas']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay registros de peso en los últimos 30 días.</p>
        <?php endif; ?>
    </div>
</div>

<?php if(count($peso_data) > 1): ?>
<script>
// Gráfico de evolución del peso
const ctx = document.getElementById('pesoChart').getContext('2d');
const pesoChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($fecha_data); ?>,
        datasets: [{
            label: 'Peso (kg)',
            data: <?php echo json_encode($peso_data); ?>,
            borderColor: '#203A43',
            backgroundColor: 'rgba(32, 58, 67, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                title: {
                    display: true,
                    text: 'Peso (kg)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Fecha'
                }
            }
        }
    }
});
</script>
<?php endif; ?>

<script>
// Establecer fecha máxima como hoy
document.getElementById('fecha_registro').max = new Date().toISOString().split('T')[0];
</script>
</body>
</html>

<?php $conn->close(); ?>