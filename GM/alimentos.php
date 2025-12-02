<?php
session_start();
include 'db.php';

if(!isset($_SESSION['usuario_id'])){
    header("Location: index.php");
    exit;
}

$mensaje = "";
$error = "";
$total_calorias = 0;

$usuario_id = $_SESSION['usuario_id'];
$total_calorias = obtenerTotalCalorias($conn, $usuario_id);

// Procesar subida de imagen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen_comida'])) {
    if ($_FILES['imagen_comida']['error'] === UPLOAD_ERR_OK) {
        $carpeta_uploads = "uploads/";
        if (!is_dir($carpeta_uploads)) {
            mkdir($carpeta_uploads, 0777, true);
        }
        
        $extension = pathinfo($_FILES['imagen_comida']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = time() . '_' . uniqid() . '.' . $extension;
        $ruta_imagen = $carpeta_uploads . $nombre_archivo;
        
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($extension), $tipos_permitidos)) {
            $error = "Solo se permiten imágenes JPG, JPEG, PNG, GIF o WEBP";
        } elseif ($_FILES['imagen_comida']['size'] > 5 * 1024 * 1024) {
            $error = "La imagen es demasiado grande. Máximo 5MB permitidos";
        } elseif (move_uploaded_file($_FILES['imagen_comida']['tmp_name'], $ruta_imagen)) {
            $mensaje = "✅ Imagen subida correctamente";
            $_SESSION['imagen_subida'] = $ruta_imagen;
        } else {
            $error = "Error al subir la imagen";
        }
    } else {
        $error = "Error en la subida del archivo";
    }
}

// Registrar comida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comida'])) {
    $comida = $_POST['comida'];
    $nombre_alimento = trim($_POST['nombre_alimento']);
    $descripcion = trim($_POST['descripcion']);
    $calorias = floatval($_POST['calorias']);
    $proteina = floatval($_POST['proteina']);
    $grasa = floatval($_POST['grasa']);
    $carbohidrato = floatval($_POST['carbohidrato']);
    
    // Usar la imagen subida previamente o dejar vacío
    $imagen = $_SESSION['imagen_subida'] ?? '';

    if(empty($nombre_alimento) || empty($comida)){
        $error = "El nombre del alimento y tipo de comida son obligatorios";
    } else {
        $sql = "INSERT INTO registro_comidas 
                (usuario_id, comida, nombre_alimento, descripcion, calorias, proteina, grasa, carbohidrato, imagen)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssdddds", $usuario_id, $comida, $nombre_alimento, $descripcion, $calorias, $proteina, $grasa, $carbohidrato, $imagen);
        
        if($stmt->execute()){
            $mensaje = "✅ Comida registrada correctamente!";
            verificarMetas($conn, $usuario_id);
            $_POST = array();
            // Limpiar la imagen de sesión después de guardar
            unset($_SESSION['imagen_subida']);
        } else {
            $error = "❌ Error al guardar la comida: " . $conn->error;
        }
        $stmt->close();
    }
}

// Obtener alimentos registrados hoy
$alimentos_hoy = $conn->query("
    SELECT * FROM registro_comidas 
    WHERE usuario_id = '$usuario_id' AND DATE(fecha_registro) = CURDATE() 
    ORDER BY fecha_registro DESC
");

// Obtener alimentos favoritos
$alimentos_favoritos = $conn->query("
    SELECT a.* FROM alimentos a 
    JOIN alimentos_favoritos af ON a.id = af.alimento_id 
    WHERE af.usuario_id = '$usuario_id'
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrar Comida - GM</title>
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
    background:#fff3cd;
    color:#856404;
    border:1px solid #ffeeba;
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
.favoritos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.favorito-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 15px;
    border-left: 4px solid #FFD700;
    cursor: pointer;
    transition: 0.3s;
}
.favorito-card:hover {
    transform: translateY(-5px);
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
.upload-simple {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
    border: 2px solid #e9ecef;
}
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: #fff;
}
.upload-area:hover {
    background: #f8f9fa;
    border-color: #4dabf7;
}
.file-input {
    display: none;
}
.upload-preview {
    margin-top: 15px;
    text-align: center;
}
.upload-preview img {
    max-width: 150px;
    max-height: 150px;
    border-radius: 10px;
    border: 3px solid #e9ecef;
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
}
</style>
</head>
<body>
<div class="sidebar">
    <h2><i class="fas fa-dumbbell"></i> FIBGEN</h2>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="alimentos.php" class="active"><i class="fas fa-utensils"></i> Alimentos</a>
    <a href="ejercicios.php"><i class="fas fa-running"></i> Ejercicios</a>
    <a href="metas.php"><i class="fas fa-bullseye"></i> Metas</a>
    <a href="progreso.php"><i class="fas fa-chart-line"></i> Progreso</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
</div>

<div class="main-content">
    <h1>Registrar Comida</h1>
    <p>Guarda lo que comiste hoy 🍽️</p>

    <?php if($mensaje) echo "<div class='mensaje'>$mensaje</div>"; ?>
    <?php if($error) echo "<div class='mensaje error'>$error</div>"; ?>

    <div class="mensaje total-calorias">
        <i class="fas fa-fire"></i> Total de calorías hoy: <?php echo $total_calorias; ?> kcal
    </div>

    <!-- Sección Simple de Subida -->
    <div class="upload-simple">
        <h3 style="margin-bottom: 15px; font-size: 18px; color: #203A43;">
            <i class="fas fa-camera"></i> Subir Foto de la Comida
        </h3>
        
        <form method="POST" action="" enctype="multipart/form-data" id="imagenForm">
            <div class="upload-area" id="uploadArea">
                <input type="file" name="imagen_comida" id="imagen_comida" class="file-input" accept="image/*">
                <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: #6c757d; margin-bottom: 10px;"></i>
                <p style="color: #6c757d; font-size: 14px; margin: 0;">
                    Haz clic aquí para seleccionar una imagen
                </p>
                <p style="color: #868e96; font-size: 12px; margin-top: 5px;">
                    JPG, PNG, GIF, WEBP - Máx. 5MB
                </p>
            </div>
            
            <?php if(isset($_SESSION['imagen_subida']) && file_exists($_SESSION['imagen_subida'])): ?>
            <div class="upload-preview">
                <img src="<?php echo $_SESSION['imagen_subida']; ?>" alt="Vista previa">
                <p style="color: #28a745; font-size: 12px; margin-top: 8px;">
                    <i class="fas fa-check"></i> Imagen lista para guardar con la comida
                </p>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Alimentos Favoritos -->
    <?php if($alimentos_favoritos && $alimentos_favoritos->num_rows > 0): ?>
    <div class="favoritos-section">
        <h3><i class="fas fa-star"></i> Tus Alimentos Favoritos</h3>
        <div class="favoritos-grid">
            <?php while($favorito = $alimentos_favoritos->fetch_assoc()): ?>
                <div class="favorito-card" onclick="rellenarFormulario(<?php echo htmlspecialchars(json_encode($favorito)); ?>)">
                    <h4><?php echo htmlspecialchars($favorito['nombre']); ?></h4>
                    <p><?php echo $favorito['calorias']; ?> kcal</p>
                    <small>P: <?php echo $favorito['proteina']; ?>g | C: <?php echo $favorito['carbohidrato']; ?>g | G: <?php echo $favorito['grasa']; ?>g</small>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Formulario de Registro de Comida -->
    <form method="POST" action="" id="comidaForm">
        <h3><i class="fas fa-edit"></i> Información de la Comida</h3>
        
        <div class="form-group">
            <label for="comida">Tipo de comida</label>
            <select name="comida" id="comida" required>
                <option value="">Selecciona</option>
                <option value="Desayuno" <?php echo isset($_POST['comida']) && $_POST['comida'] == 'Desayuno' ? 'selected' : ''; ?>>Desayuno</option>
                <option value="Almuerzo" <?php echo isset($_POST['comida']) && $_POST['comida'] == 'Almuerzo' ? 'selected' : ''; ?>>Almuerzo</option>
                <option value="Merienda" <?php echo isset($_POST['comida']) && $_POST['comida'] == 'Merienda' ? 'selected' : ''; ?>>Merienda</option>
                <option value="Cena" <?php echo isset($_POST['comida']) && $_POST['comida'] == 'Cena' ? 'selected' : ''; ?>>Cena</option>
                <option value="Snack" <?php echo isset($_POST['comida']) && $_POST['comida'] == 'Snack' ? 'selected' : ''; ?>>Snack</option>
            </select>
        </div>

        <div class="form-group">
            <label for="nombre_alimento">Nombre del alimento</label>
            <input type="text" name="nombre_alimento" id="nombre_alimento" required 
                   value="<?php echo isset($_POST['nombre_alimento']) ? htmlspecialchars($_POST['nombre_alimento']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción / Notas</label>
            <textarea name="descripcion" id="descripcion" rows="3"><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="calorias">Calorías</label>
                <input type="number" name="calorias" id="calorias" step="0.1" min="0"
                       value="<?php echo isset($_POST['calorias']) ? $_POST['calorias'] : ''; ?>">
            </div>
            <div class="form-group">
                <label for="proteina">Proteína (g)</label>
                <input type="number" name="proteina" id="proteina" step="0.1" min="0"
                       value="<?php echo isset($_POST['proteina']) ? $_POST['proteina'] : ''; ?>">
            </div>
            <div class="form-group">
                <label for="grasa">Grasa (g)</label>
                <input type="number" name="grasa" id="grasa" step="0.1" min="0"
                       value="<?php echo isset($_POST['grasa']) ? $_POST['grasa'] : ''; ?>">
            </div>
            <div class="form-group">
                <label for="carbohidrato">Carbohidrato (g)</label>
                <input type="number" name="carbohidrato" id="carbohidrato" step="0.1" min="0"
                       value="<?php echo isset($_POST['carbohidrato']) ? $_POST['carbohidrato'] : ''; ?>">
            </div>
        </div>

        <button type="submit">💾 Guardar Comida</button>
    </form>

    <!-- Alimentos Registrados Hoy -->
    <div class="table-container">
        <h3><i class="fas fa-history"></i> Alimentos Registrados Hoy</h3>
        <?php if($alimentos_hoy && $alimentos_hoy->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Comida</th>
                        <th>Alimento</th>
                        <th>Calorías</th>
                        <th>Proteína</th>
                        <th>Carbohidratos</th>
                        <th>Grasas</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($alimento = $alimentos_hoy->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($alimento['comida']); ?></td>
                            <td><?php echo htmlspecialchars($alimento['nombre_alimento']); ?></td>
                            <td><?php echo $alimento['calorias']; ?> kcal</td>
                            <td><?php echo $alimento['proteina']; ?>g</td>
                            <td><?php echo $alimento['carbohidrato']; ?>g</td>
                            <td><?php echo $alimento['grasa']; ?>g</td>
                            <td><?php echo date('H:i', strtotime($alimento['fecha_registro'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay alimentos registrados para hoy.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function rellenarFormulario(alimento) {
    document.getElementById('nombre_alimento').value = alimento.nombre;
    document.getElementById('descripcion').value = alimento.descripcion || '';
    document.getElementById('calorias').value = alimento.calorias || '';
    document.getElementById('proteina').value = alimento.proteina || '';
    document.getElementById('grasa').value = alimento.grasa || '';
    document.getElementById('carbohidrato').value = alimento.carbohidrato || '';
}

// Manejo de la subida de imágenes
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('imagen_comida');
    
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            document.getElementById('imagenForm').submit();
        }
    });
});
</script>
</body>
</html>

<?php $conn->close(); ?>