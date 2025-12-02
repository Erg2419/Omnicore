<?php
session_start();
include 'db.php';

if(!isset($_SESSION['usuario_id'])){
    header("Location: index.php");
    exit;
}

$mensaje = "";
$error = "";
$usuario_id = $_SESSION['usuario_id'];

// Procesar formulario de ejercicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_ejercicio'])) {
    $tipo_ejercicio = trim($_POST['tipo_ejercicio']);
    $duracion_minutos = intval($_POST['duracion_minutos']);
    $calorias_quemadas = floatval($_POST['calorias_quemadas']);
    $intensidad = $_POST['intensidad'];
    $notas = trim($_POST['notas']);

    if(empty($tipo_ejercicio) || $duracion_minutos <= 0){
        $error = "El tipo de ejercicio y duración son obligatorios";
    } else {
        $sql = "INSERT INTO ejercicios 
                (usuario_id, tipo_ejercicio, duracion_minutos, calorias_quemadas, intensidad, notas)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isidss", $usuario_id, $tipo_ejercicio, $duracion_minutos, $calorias_quemadas, $intensidad, $notas);
        
        if($stmt->execute()){
            $mensaje = "✅ Ejercicio registrado correctamente!";
            
            // Verificar logros de ejercicio
            verificarLogrosEjercicio($conn, $usuario_id);
            
            $_POST = array();
        } else {
            $error = "❌ Error al guardar el ejercicio: " . $conn->error;
        }
        $stmt->close();
    }
}

// Función para verificar logros de ejercicio
function verificarLogrosEjercicio($conn, $usuario_id) {
    // Verificar ejercicio consistente (7 días seguidos)
    $ejercicio_7dias = $conn->query("
        SELECT COUNT(DISTINCT DATE(fecha_registro)) as dias 
        FROM ejercicios 
        WHERE usuario_id = '$usuario_id' 
        AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    
    if($ejercicio_7dias && $ejercicio_7dias->num_rows > 0) {
        $dias_data = $ejercicio_7dias->fetch_assoc();
        if($dias_data['dias'] >= 7) {
            // Verificar si ya tiene este logro
            $logro_existente = $conn->query("
                SELECT id FROM logros 
                WHERE usuario_id = '$usuario_id' 
                AND tipo_logro = 'ejercicio' 
                AND descripcion LIKE '%7 días consecutivos%'
            ");
            
            if(!$logro_existente || $logro_existente->num_rows == 0) {
                $conn->query("INSERT INTO logros (usuario_id, tipo_logro, descripcion) 
                             VALUES ('$usuario_id', 'ejercicio', '7 días consecutivos de ejercicio')");
                
                $conn->query("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) 
                             VALUES ('$usuario_id', '¡Nuevo Logro!', 'Has completado 7 días consecutivos de ejercicio', 'logro')");
            }
        }
    }
}

// Obtener ejercicios de hoy
$ejercicios_hoy = $conn->query("
    SELECT * FROM ejercicios 
    WHERE usuario_id = '$usuario_id' AND DATE(fecha_registro) = CURDATE() 
    ORDER BY fecha_registro DESC
");

// Calorías quemadas hoy
$calorias_quemadas_hoy = 0;
$res_calorias = $conn->query("SELECT SUM(calorias_quemadas) as total FROM ejercicios WHERE usuario_id = '$usuario_id' AND DATE(fecha_registro) = CURDATE()");
if($res_calorias && $res_calorias->num_rows > 0) {
    $calorias_data = $res_calorias->fetch_assoc();
    $calorias_quemadas_hoy = $calorias_data['total'] ? $calorias_data['total'] : 0;
}

// Ejercicios comunes para sugerencias
$ejercicios_comunes = [
    'Caminata' => 4,
    'Trotar' => 8,
    'Correr' => 12,
    'Ciclismo' => 10,
    'Natación' => 11,
    'Yoga' => 3,
    'Pesas' => 6,
    'CrossFit' => 13,
    'Baile' => 7,
    'Escalada' => 9
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ejercicios - FIBGEN</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
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
.mensaje.total-calorias{
    background:#e3f2fd;
    color:#1565c0;
    border:1px solid #bbdefb;
    margin-top:20px;
    font-size:18px;
    font-weight:700;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    padding:18px 20px;
    border-radius:14px;
    text-align:center;
}
.form-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}
.ejercicios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}
.ejercicio-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    transition: 0.3s;
    border: 2px solid transparent;
}
.ejercicio-card:hover {
    transform: translateY(-3px);
    border-color: #FFD700;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.table-container {
    background: #fff;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-top: 30px;
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
.intensidad-baja { color: #28a745; }
.intensidad-media { color: #ffc107; }
.intensidad-alta { color: #dc3545; }
.calculator {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
}
.calc-grid {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
    align-items: end;
}
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
    .calc-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<div class="sidebar">
    <h2><i class="fas fa-dumbbell"></i> FIBGEN</h2>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="alimentos.php"><i class="fas fa-utensils"></i> Alimentos</a>
    <a href="ejercicios.php" class="active"><i class="fas fa-running"></i> Ejercicios</a>
    <a href="metas.php"><i class="fas fa-bullseye"></i> Metas</a>
    <a href="progreso.php"><i class="fas fa-chart-line"></i> Progreso</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
    <h1>Registrar Ejercicio</h1>
    <p>Registra tu actividad física diaria 🏃‍♂️</p>

    <?php if($mensaje) echo "<div class='mensaje'>$mensaje</div>"; ?>
    <?php if($error) echo "<div class='mensaje error'>$error</div>"; ?>

    <!-- Total de calorías quemadas -->
    <div class="mensaje total-calorias">
        <i class="fas fa-burn"></i> Calorías quemadas hoy: <?php echo $calorias_quemadas_hoy; ?> kcal
    </div>

    <!-- Calculadora de calorías -->
    <div class="calculator">
        <h3><i class="fas fa-calculator"></i> Calculadora de Calorías</h3>
        <div class="calc-grid">
            <div class="form-group">
                <label>Ejercicio</label>
                <select id="calc_ejercicio">
                    <option value="">Selecciona un ejercicio</option>
                    <?php foreach($ejercicios_comunes as $ejercicio => $calorias): ?>
                        <option value="<?php echo $calorias; ?>"><?php echo $ejercicio; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Duración (minutos)</label>
                <input type="number" id="calc_duracion" min="1" value="30">
            </div>
            <div class="form-group">
                <label>Calorías estimadas</label>
                <input type="text" id="calc_resultado" readonly>
            </div>
            <button type="button" onclick="calcularCalorias()">Calcular</button>
        </div>
    </div>

    <!-- Ejercicios Comunes -->
    <h3>Ejercicios Comunes</h3>
    <div class="ejercicios-grid">
        <?php foreach($ejercicios_comunes as $ejercicio => $calorias): ?>
            <div class="ejercicio-card" onclick="seleccionarEjercicio('<?php echo $ejercicio; ?>', <?php echo $calorias; ?>)">
                <h4><?php echo $ejercicio; ?></h4>
                <small><?php echo $calorias; ?> kcal/min aprox.</small>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="POST" action="">
        <div class="form-group">
            <label for="tipo_ejercicio">Tipo de Ejercicio</label>
            <input type="text" name="tipo_ejercicio" id="tipo_ejercicio" required placeholder="Ej: Correr, Nadar, Pesas...">
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="duracion_minutos">Duración (minutos)</label>
                <input type="number" name="duracion_minutos" id="duracion_minutos" min="1" required>
            </div>
            <div class="form-group">
                <label for="calorias_quemadas">Calorías Quemadas</label>
                <input type="number" name="calorias_quemadas" id="calorias_quemadas" step="0.1" min="0">
            </div>
            <div class="form-group">
                <label for="intensidad">Intensidad</label>
                <select name="intensidad" id="intensidad" required>
                    <option value="baja">Baja</option>
                    <option value="media" selected>Media</option>
                    <option value="alta">Alta</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="notas">Notas / Observaciones</label>
            <textarea name="notas" id="notas" rows="3" placeholder="Cómo te sentiste, dificultad, etc..."></textarea>
        </div>

        <button type="submit">💪 Registrar Ejercicio</button>
    </form>

    <!-- Ejercicios Registrados Hoy -->
    <div class="table-container">
        <h3><i class="fas fa-history"></i> Ejercicios de Hoy</h3>
        <?php if($ejercicios_hoy && $ejercicios_hoy->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Ejercicio</th>
                        <th>Duración</th>
                        <th>Calorías</th>
                        <th>Intensidad</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($ejercicio = $ejercicios_hoy->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ejercicio['tipo_ejercicio']); ?></td>
                            <td><?php echo $ejercicio['duracion_minutos']; ?> min</td>
                            <td><?php echo $ejercicio['calorias_quemadas']; ?> kcal</td>
                            <td>
                                <span class="intensidad-<?php echo $ejercicio['intensidad']; ?>">
                                    <?php echo ucfirst($ejercicio['intensidad']); ?>
                                </span>
                            </td>
                            <td><?php echo date('H:i', strtotime($ejercicio['fecha_registro'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay ejercicios registrados para hoy.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function seleccionarEjercicio(ejercicio, caloriasPorMinuto) {
    document.getElementById('tipo_ejercicio').value = ejercicio;
    // Establecer calorías por minuto como data attribute para el cálculo
    document.getElementById('tipo_ejercicio').setAttribute('data-calorias', caloriasPorMinuto);
}

function calcularCalorias() {
    const ejercicioSelect = document.getElementById('calc_ejercicio');
    const duracion = document.getElementById('calc_duracion').value;
    const resultado = document.getElementById('calc_resultado');
    
    if(ejercicioSelect.value && duracion) {
        const caloriasPorMinuto = parseFloat(ejercicioSelect.value);
        const caloriasTotales = caloriasPorMinuto * duracion;
        resultado.value = Math.round(caloriasTotales) + ' kcal';
        
        // Rellenar automáticamente el formulario principal
        document.getElementById('tipo_ejercicio').value = ejercicioSelect.options[ejercicioSelect.selectedIndex].text;
        document.getElementById('duracion_minutos').value = duracion;
        document.getElementById('calorias_quemadas').value = Math.round(caloriasTotales);
    }
}

// Calcular calorías automáticamente cuando cambie la duración
document.getElementById('duracion_minutos').addEventListener('input', function() {
    const ejercicio = document.getElementById('tipo_ejercicio');
    const duracion = this.value;
    const caloriasInput = document.getElementById('calorias_quemadas');
    
    if(ejercicio.value && ejercicio.getAttribute('data-calorias') && duracion) {
        const caloriasPorMinuto = parseFloat(ejercicio.getAttribute('data-calorias'));
        const caloriasTotales = caloriasPorMinuto * duracion;
        caloriasInput.value = Math.round(caloriasTotales);
    }
});
</script>
</body>
</html>

<?php $conn->close(); ?>