<?php
session_start();
require_once 'GM_System.php';

$gm_system = new GM_System();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$alimentos = $gm_system->obtenerAlimentos();
$alimentos_favoritos = $gm_system->obtenerAlimentosFavoritos($usuario_id);

// Procesar registro de comida
if ($_POST && isset($_POST['registrar_comida'])) {
    if ($gm_system->registrarComida($usuario_id, $_POST)) {
        header("Location: index.php?success=3");
        exit();
    } else {
        $error = "Error al registrar la comida";
    }
}

// Procesar favoritos
if ($_POST && isset($_POST['agregar_favorito'])) {
    if ($gm_system->agregarAlimentoFavorito($usuario_id, $_POST['alimento_id'])) {
        $success_fav = "Alimento agregado a favoritos";
    } else {
        $error_fav = "Error al agregar a favoritos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Comida - GM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos similares al index.php - mantener consistencia */
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #7209b7;
            --health: #2ec4b6;
            --nutrition: #ff9f1c;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --border-radius: 15px;
            --box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--box-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo i {
            font-size: 32px;
            color: var(--primary);
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark);
        }

        .btn-secondary:hover {
            background: #d8dde3;
            transform: translateY(-2px);
        }

        .btn-nutrition {
            background: var(--nutrition);
            color: white;
        }

        .btn-nutrition:hover {
            background: #e68a00;
            transform: translateY(-2px);
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .content-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-gray);
        }

        .section-header h2 {
            color: var(--primary);
            font-size: 20px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
            background: white;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .alimentos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .alimento-card {
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius);
            padding: 15px;
            transition: var(--transition);
            cursor: pointer;
        }

        .alimento-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .alimento-card.selected {
            border-color: var(--success);
            background: rgba(76, 201, 240, 0.05);
        }

        .alimento-nombre {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .alimento-macros {
            font-size: 12px;
            color: var(--gray);
        }

        .alimento-calorias {
            font-weight: 600;
            color: var(--nutrition);
            margin-top: 5px;
        }

        .favorite-btn {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 16px;
            transition: var(--transition);
        }

        .favorite-btn:hover {
            color: var(--warning);
        }

        .favorite-btn.active {
            color: var(--warning);
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .alert.success {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border: 1px solid rgba(76, 201, 240, 0.3);
        }

        .alert.error {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border: 1px solid rgba(247, 37, 133, 0.3);
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-utensils"></i>
                <h1>Registrar Comida</h1>
            </div>
            <div>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success_fav)): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i> <?php echo $success_fav; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_fav)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_fav; ?>
            </div>
        <?php endif; ?>

        <div class="main-content">
            <!-- Formulario de registro -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-edit"></i> Registrar Nueva Comida</h2>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="comida">Tipo de Comida</label>
                        <select id="comida" name="comida" required>
                            <option value="Desayuno">Desayuno</option>
                            <option value="Almuerzo">Almuerzo</option>
                            <option value="Merienda">Merienda</option>
                            <option value="Cena">Cena</option>
                            <option value="Snack">Snack</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nombre_alimento">Nombre del Alimento</label>
                        <input type="text" id="nombre_alimento" name="nombre_alimento" required placeholder="Ej: Pechuga de pollo a la plancha">
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción (opcional)</label>
                        <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripción detallada de la comida..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="calorias">Calorías</label>
                        <input type="number" id="calorias" name="calorias" step="0.1" min="0" required placeholder="0.0">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="proteina">Proteína (g)</label>
                            <input type="number" id="proteina" name="proteina" step="0.1" min="0" required value="0">
                        </div>

                        <div class="form-group">
                            <label for="grasa">Grasa (g)</label>
                            <input type="number" id="grasa" name="grasa" step="0.1" min="0" required value="0">
                        </div>

                        <div class="form-group">
                            <label for="carbohidrato">Carbohidratos (g)</label>
                            <input type="number" id="carbohidrato" name="carbohidrato" step="0.1" min="0" required value="0">
                        </div>
                    </div>

                    <button type="submit" name="registrar_comida" class="btn btn-nutrition" style="width: 100%;">
                        <i class="fas fa-save"></i> Registrar Comida
                    </button>
                </form>
            </div>

            <!-- Lista de alimentos -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-search"></i> Buscar Alimentos</h2>
                </div>

                <div class="form-group">
                    <input type="text" id="buscarAlimento" placeholder="Buscar alimento..." style="margin-bottom: 15px;">
                </div>

                <div class="alimentos-grid" id="alimentosGrid">
                    <?php foreach ($alimentos as $alimento): ?>
                        <div class="alimento-card" onclick="seleccionarAlimento(this)" data-alimento='<?php echo json_encode($alimento); ?>'>
                            <div style="display: flex; justify-content: between; align-items: start;">
                                <div class="alimento-nombre"><?php echo htmlspecialchars($alimento['nombre']); ?></div>
                                <form method="POST" style="margin: 0;" onclick="event.stopPropagation()">
                                    <input type="hidden" name="alimento_id" value="<?php echo $alimento['id']; ?>">
                                    <button type="submit" name="agregar_favorito" class="favorite-btn <?php echo in_array($alimento['id'], array_column($alimentos_favoritos, 'id')) ? 'active' : ''; ?>">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="alimento-macros">
                                P: <?php echo $alimento['proteina']; ?>g | 
                                C: <?php echo $alimento['carbohidrato']; ?>g | 
                                G: <?php echo $alimento['grasa']; ?>g
                            </div>
                            <div class="alimento-calorias">
                                <?php echo $alimento['calorias']; ?> cal
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function seleccionarAlimento(card) {
            const alimento = JSON.parse(card.getAttribute('data-alimento'));
            
            // Llenar el formulario con los datos del alimento
            document.getElementById('nombre_alimento').value = alimento.nombre;
            document.getElementById('calorias').value = alimento.calorias;
            document.getElementById('proteina').value = alimento.proteina;
            document.getElementById('grasa').value = alimento.grasa;
            document.getElementById('carbohidrato').value = alimento.carbohidrato;
            
            // Efecto visual
            document.querySelectorAll('.alimento-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
        }

        // Búsqueda de alimentos
        document.getElementById('buscarAlimento').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const alimentos = document.querySelectorAll('.alimento-card');
            
            alimentos.forEach(alimento => {
                const nombre = alimento.querySelector('.alimento-nombre').textContent.toLowerCase();
                if (nombre.includes(searchTerm)) {
                    alimento.style.display = 'block';
                } else {
                    alimento.style.display = 'none';
                }
            });
        });

        // Efectos de interfaz
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>