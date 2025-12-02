<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// public/index.php - router central
// NOTA: abre el proyecto en http://localhost:8080/BuildSmart/public/

// define BASE_URL para links (sin trailing slash)
define('BASE_URL', rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../app/controllers/ClienteController.php';
require_once __DIR__ . '/../app/controllers/UsuariosController.php';



// decidir página (si no logueado => login)
$page = $_GET['page'] ?? (is_logged() ? 'dashboard' : 'login');
define('BASE_PATH', realpath(__DIR__ . '/../') . '/'); // apunta a BuildSmart/

// manejar rutas
switch ($page) {

  
   
    case 'register':
        require BASE_PATH . 'app/views/auth/register.php';
        break;

/* -------------------- USUARIOS -------------------- */
case 'usuarios':
    require_login();
    $usuarios = (new UsuariosController())->buscar(); // listar todos
    render('usuarios/listar.php', ['usuarios' => $usuarios]);
    break;

case 'usuarios/form':
    require_login();
    $controller = new UsuariosController();
    $controller->form(); // muestra el formulario (nuevo o editar)
    break;

case 'usuarios/guardar':
    require_login();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new UsuariosController())->guardar();
    } else {
        redirectTo('usuarios');
    }
    break;

case 'usuarios/buscar':
    require_login();
    $q = $_GET['q'] ?? '';
    $usuarios = (new UsuariosController())->buscar($q);
    include __DIR__ . '/../app/views/usuarios/tabla.php';
    exit;

case 'usuarios/delete':
    require_login();
    (new UsuariosController())->eliminar();
    break;

case 'usuarios/reporte':
    require_login();
    global $pdo;
    $q = $_GET['q'] ?? '';

    if (!empty($q)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios 
                               WHERE nombre LIKE :q OR email LIKE :q OR rol LIKE :q
                               ORDER BY id DESC");
        $stmt->execute([':q' => "%$q%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC");
    }

    $usuarios = $stmt->fetchAll();

    // Exportar a Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=ReporteUsuarios_" . date('Ymd_His') . ".xls");
    echo "<table border='1' style='border-collapse:collapse'>";
    echo "<tr style='background:#f97316;color:white;'>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
          </tr>";

    foreach ($usuarios as $u) {
        echo "<tr>
                <td>{$u['id']}</td>
                <td>{$u['nombre']}</td>
                <td>{$u['email']}</td>
                <td>{$u['rol']}</td>
              </tr>";
    }
    echo "</table>";
    exit;

    /* -------------------- MATERIALES -------------------- */
case 'materiales':
    require_once __DIR__ . '/../app/controllers/MaterialesController.php';
    listarMateriales();
    break;

case 'materiales/form':
    require_once __DIR__ . '/../app/controllers/MaterialesController.php';
    formularioMaterial();
    break;

case 'materiales/guardar':
    require_once __DIR__ . '/../app/controllers/MaterialesController.php';
    guardarMaterial();
    break;

case 'materiales/delete':
    require_once __DIR__ . '/../app/controllers/MaterialesController.php';
    eliminarMaterial();
    break;

case 'materiales/buscar':
    require_once __DIR__ . '/../app/controllers/MaterialesController.php';
    buscarMaterial();
    break;

case 'materiales/reporte':
    require_login();
    global $pdo;
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=ReporteMateriales_" . date('Ymd_His') . ".xls");

    $q = $_GET['q'] ?? '';
    $materiales = $q ? buscarMateriales($pdo, $q) : obtenerMateriales($pdo);

    echo "<table border='1'><tr style='background:#f97316;color:white;'>
            <th>ID</th><th>Nombre</th><th>Cantidad</th><th>Costo Unitario</th><th>Proveedor</th><th>Proyecto</th></tr>";
    foreach ($materiales as $m) {
        echo "<tr>
                <td>{$m['id']}</td>
                <td>{$m['nombre']}</td>
                <td>{$m['cantidad']}</td>
                <td>\${$m['costo_unitario']}</td>
                <td>{$m['proveedor_nombre']}</td>
                <td>{$m['proyecto_nombre']}</td>
              </tr>";
    }
    echo "</table>";
    exit;

    /* -------------------- CLIENTES -------------------- */
case 'clientes':
    require_once __DIR__ . '/../app/controllers/ClienteController.php';
    listarClientes();
    break;

case 'clientes/form':
    require_once __DIR__ . '/../app/controllers/ClienteController.php';
    formularioCliente();
    break;

case 'clientes/guardar':
    require_once __DIR__ . '/../app/controllers/ClienteController.php';
    guardarCliente();
    break;

case 'clientes/delete':
    require_once __DIR__ . '/../app/controllers/ClienteController.php';
    eliminarCliente();
    break;

case 'clientes/buscar':
    require_once __DIR__ . '/../app/controllers/ClienteController.php';
    buscarCliente();
    break;

case 'clientes/reporte':
    require_login();
    global $pdo;
    $q = $_GET['q'] ?? '';
    $query = "SELECT * FROM clientes";
    $params = [];
    if (!empty($q)) {
        $query .= " WHERE nombre LIKE :q OR correo LIKE :q OR telefono LIKE :q";
        $params[':q'] = "%$q%";
    }
    $query .= " ORDER BY id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $clientes = $stmt->fetchAll();
    header("Content-Type: application/vnd.ms-excel");
    $nombreArchivo = empty($q)
        ? "ReporteClientes_" . date('Ymd_His') . ".xls"
        : "ReporteClientes_Filtrados_" . date('Ymd_His') . ".xls";
    header("Content-Disposition: attachment; filename=$nombreArchivo");
    echo "<table border='1' style='border-collapse:collapse'>";
    echo "<tr style='background-color:#f97316;color:white;'>
            <th>ID</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Dirección</th>
          </tr>";
    foreach ($clientes as $c) {
        echo "<tr>
                <td>{$c['id']}</td>
                <td>{$c['nombre']}</td>
                <td>{$c['telefono']}</td>
                <td>{$c['correo']}</td>
                <td>{$c['direccion']}</td>
              </tr>";
    }
    echo "</table>";
    exit;

/* -------------------- EMPLEADOS -------------------- */
case 'empleados':
    require_once __DIR__ . '/../app/controllers/EmpleadosController.php';
    listarEmpleados();
    break;

case 'empleados/form':
    require_once __DIR__ . '/../app/controllers/EmpleadosController.php';

    formularioEmpleado();
    break;

case 'empleados/guardar':
  require_once __DIR__ . '/../app/controllers/EmpleadosController.php';

    guardarEmpleado();
    break;

case 'empleados/delete':
    require_once __DIR__ . '/../app/controllers/EmpleadosController.php';
    eliminarEmpleado();
    break;

case 'empleados/buscar':
   require_once __DIR__ . '/../app/controllers/EmpleadosController.php';

    buscarEmpleado();
    break;

case 'empleados/reporte':
    require_login();
    global $pdo;
    $q = $_GET['q'] ?? '';
    $query = "SELECT * FROM empleados";
    $params = [];
    if (!empty($q)) {
        $query .= " WHERE nombre LIKE :q OR puesto LIKE :q OR telefono LIKE :q";
        $params[':q'] = "%$q%";
    }
    $query .= " ORDER BY id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $empleados = $stmt->fetchAll();
    header("Content-Type: application/vnd.ms-excel");
    $nombreArchivo = empty($q)
        ? "ReporteEmpleados_" . date('Ymd_His') . ".xls"
        : "ReporteEmpleados_Filtrados_" . date('Ymd_His') . ".xls";
    header("Content-Disposition: attachment; filename=$nombreArchivo");
    echo "<table border='1' style='border-collapse:collapse'>";
    echo "<tr style='background-color:#f97316;color:white;'>
            <th>ID</th>
            <th>Nombre</th>
            <th>Puesto</th>
            <th>Salario</th>
            <th>Teléfono</th>
          </tr>";
    foreach ($empleados as $e) {
        echo "<tr>
                <td>{$e['id']}</td>
                <td>{$e['nombre']}</td>
                <td>{$e['puesto']}</td>
                <td>{$e['salario']}</td>
                <td>{$e['telefono']}</td>
              </tr>";
    }
    echo "</table>";
    exit;

    /* -------------------- PROVEEDORES -------------------- */
case 'proveedores':
    require_once __DIR__ . '/../app/controllers/ProveedorController.php';
    listarProveedores();
    break;

case 'proveedores/form':
    require_once __DIR__ . '/../app/controllers/ProveedorController.php';
    formularioProveedor();
    break;

case 'proveedores/guardar':
    require_once __DIR__ . '/../app/controllers/ProveedorController.php';
    guardarProveedor();
    break;

case 'proveedores/delete':
    require_once __DIR__ . '/../app/controllers/ProveedorController.php';
    eliminarProveedor();
    break;

case 'proveedores/buscar':
    require_once __DIR__ . '/../app/controllers/ProveedorController.php';
    buscarProveedor();
    break;

case 'proveedores/reporte':
    require_login();
    global $pdo;
    $q = $_GET['q'] ?? '';
    $query = "SELECT * FROM proveedores";
    $params = [];
    if (!empty($q)) {
        $query .= " WHERE nombre LIKE :q OR correo LIKE :q OR telefono LIKE :q";
        $params[':q'] = "%$q%";
    }
    $query .= " ORDER BY id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $proveedores = $stmt->fetchAll();
    header("Content-Type: application/vnd.ms-excel");
    $nombreArchivo = empty($q)
        ? "ReporteProveedores_" . date('Ymd_His') . ".xls"
        : "ReporteProveedores_Filtrados_" . date('Ymd_His') . ".xls";
    header("Content-Disposition: attachment; filename=$nombreArchivo");
    echo "<table border='1' style='border-collapse:collapse'>";
    echo "<tr style='background-color:#f97316;color:white;'>
            <th>ID</th><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Dirección</th></tr>";
    foreach ($proveedores as $p) {
        echo "<tr>
                <td>{$p['id']}</td>
                <td>{$p['nombre']}</td>
                <td>{$p['telefono']}</td>
                <td>{$p['correo']}</td>
                <td>{$p['direccion']}</td>
              </tr>";
    }
    echo "</table>";
    exit;

    /* -------------------- PAGOS -------------------- */
case 'pagos':
case 'pagos/listar':
    require_login();
    global $pdo;

    // Búsqueda dinámica por proyecto o descripción
    $q = $_GET['q'] ?? '';
    $sql = "SELECT pa.*, pr.nombre AS proyecto
            FROM pagos pa
            LEFT JOIN proyectos pr ON pa.proyecto_id = pr.id";

    $params = [];
    if ($q) {
        $sql .= " WHERE pr.nombre LIKE :q OR pa.descripcion LIKE :q";
        $params[':q'] = "%$q%";
    }
    $sql .= " ORDER BY pa.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pagos = $stmt->fetchAll();

    render('pagos/listar.php', ['pagos' => $pagos, 'q' => $q]);
    break;

case 'pagos/form':
    require_login();
    global $pdo;

    $id = $_GET['id'] ?? null;
    $proyectos = $pdo->query("SELECT id, nombre FROM proyectos ORDER BY nombre ASC")->fetchAll();

    $pago = null;
    if ($id) {
        // Cargar datos para edición
        $stmt = $pdo->prepare("SELECT * FROM pagos WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $pago = $stmt->fetch();
    }

    render('pagos/form.php', ['proyectos' => $proyectos, 'csrf' => csrf_token(), 'pago' => $pago]);
    break;

case 'pagos/guardar':
    require_login();
    global $pdo;

    $id = $_GET['id'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validate_csrf($_POST['csrf'] ?? '')) die("⚠️ Petición no válida (CSRF).");

        if ($id) {
            // EDITAR
            $stmt = $pdo->prepare("UPDATE pagos 
                                   SET proyecto_id=:proyecto_id, descripcion=:descripcion, monto=:monto, tipo=:tipo, fecha=:fecha, metodo_pago=:metodo_pago 
                                   WHERE id=:id");
            $stmt->execute([
                ':proyecto_id'=>$_POST['proyecto_id'],
                ':descripcion'=>$_POST['descripcion'],
                ':monto'=>$_POST['monto'],
                ':tipo'=>$_POST['tipo'],
                ':fecha'=>$_POST['fecha'],
                ':metodo_pago'=>$_POST['metodo_pago'],
                ':id'=>$id
            ]);
            flash("💾 Pago actualizado correctamente");
        } else {
            // NUEVO
            $stmt = $pdo->prepare("INSERT INTO pagos (proyecto_id, descripcion, monto, tipo, fecha, metodo_pago) 
                                   VALUES (:proyecto_id, :descripcion, :monto, :tipo, :fecha, :metodo_pago)");
            $stmt->execute([
                ':proyecto_id'=>$_POST['proyecto_id'],
                ':descripcion'=>$_POST['descripcion'],
                ':monto'=>$_POST['monto'],
                ':tipo'=>$_POST['tipo'],
                ':fecha'=>$_POST['fecha'],
                ':metodo_pago'=>$_POST['metodo_pago']
            ]);
            flash("💾 Pago registrado correctamente");
        }

        redirectTo('pagos/listar'); // Redirige a la tabla de pagos
    }
    break;

case 'pagos/reporte':
    require_login();
    global $pdo;

    $q = $_GET['q'] ?? '';
    $sql = "SELECT pa.*, pr.nombre AS proyecto
            FROM pagos pa
            LEFT JOIN proyectos pr ON pa.proyecto_id = pr.id";

    $params = [];
    if ($q) {
        $sql .= " WHERE pr.nombre LIKE :q OR pa.descripcion LIKE :q";
        $params[':q'] = "%$q%";
    }
    $sql .= " ORDER BY pa.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pagos = $stmt->fetchAll();

    // Exportar a Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=ReportePagos_" . date('Ymd_His') . ".xls");
    echo "<table border='1'>";
    echo "<tr style='background:#f97316;color:white;'>
            <th>ID</th>
            <th>Proyecto</th>
            <th>Descripción</th>
            <th>Monto</th>
            <th>Tipo</th>
            <th>Método</th>
            <th>Fecha</th>
          </tr>";

    foreach ($pagos as $p) {
        echo "<tr>
                <td>{$p['id']}</td>
                <td>{$p['proyecto']}</td>
                <td>{$p['descripcion']}</td>
                <td>{$p['monto']}</td>
                <td>{$p['tipo']}</td>
                <td>{$p['metodo_pago']}</td>
                <td>{$p['fecha']}</td>
              </tr>";
    }
    echo "</table>";
    exit;


 /* -------------------- COSTOS -------------------- */
    case 'costos':
    case 'costos/listar':
        require_login();
        global $pdo;
        $sql = "SELECT c.*, p.nombre AS proyecto
                FROM costos c
                LEFT JOIN proyectos p ON c.proyecto_id = p.id
                ORDER BY c.id DESC";
        $costos = $pdo->query($sql)->fetchAll();
        render('costos/listar.php', ['costos' => $costos]);
        break;

    case 'costos/form':
        require_login();
        global $pdo;
        $proyectos = $pdo->query("SELECT id, nombre FROM proyectos ORDER BY nombre ASC")->fetchAll();
        render('costos/form.php', ['proyectos' => $proyectos, 'csrf' => csrf_token()]);
        break;

        case 'costos/guardar':
    require_login();
    global $pdo;

    // Verifica que el formulario venga por POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Validar token CSRF para evitar envíos maliciosos
        if (!validate_csrf($_POST['csrf'] ?? '')) {
            die("⚠️ Petición no válida (CSRF).");
        }

        // Limpiar y convertir valores numéricos (permitir comas o puntos)
        $costo_materiales = floatval(str_replace(',', '', $_POST['costo_materiales']));
        $costo_mano_obra  = floatval(str_replace(',', '', $_POST['costo_mano_obra']));
        $otros_gastos     = floatval(str_replace(',', '', $_POST['otros_gastos']));

        // Preparar e insertar en la base de datos
        $stmt = $pdo->prepare("
            INSERT INTO costos (proyecto_id, costo_materiales, costo_mano_obra, otros_gastos, total)
            VALUES (:proyecto_id, :costo_materiales, :costo_mano_obra, :otros_gastos,
                    (:costo_materiales + :costo_mano_obra + :otros_gastos))
        ");
        $stmt->execute([
            ':proyecto_id' => $_POST['proyecto_id'],
            ':costo_materiales' => $costo_materiales,
            ':costo_mano_obra'  => $costo_mano_obra,
            ':otros_gastos'     => $otros_gastos
        ]);

        // Mostrar mensaje de éxito y redirigir al listado
        flash("💰 Costo registrado correctamente");
        redirectTo('costos/listar');
    }
    break;


/* -------------------- COSTOS - REPORTE -------------------- */
case 'costos/reporte':
    require_login();
    global $pdo;

    $id = $_GET['id'] ?? null;
    $busqueda = $_GET['q'] ?? '';

    if ($id) {
        // Generar reporte de un solo costo
        $stmt = $pdo->prepare("
            SELECT p.nombre AS proyecto, c.costo_materiales, c.costo_mano_obra, 
                   c.otros_gastos, c.total
            FROM costos c
            LEFT JOIN proyectos p ON p.id = c.proyecto_id
            WHERE c.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $costos = $stmt->fetchAll();
    } else {
        // Generar reporte de todos o por búsqueda
        $sql = "SELECT p.nombre AS proyecto, c.costo_materiales, c.costo_mano_obra, 
                       c.otros_gastos, c.total
                FROM costos c
                LEFT JOIN proyectos p ON p.id = c.proyecto_id
                WHERE p.nombre LIKE :busqueda
                ORDER BY c.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':busqueda' => "%$busqueda%"]);
        $costos = $stmt->fetchAll();
    }

    // Generar archivo Excel simple
    header("Content-Type: application/vnd.ms-excel");
    $nombreArchivo = $id 
        ? "Reporte_Costo_ID_{$id}_" . date('Ymd_His') . ".xls"
        : "ReporteCostos_" . date('Ymd_His') . ".xls";
    header("Content-Disposition: attachment; filename=$nombreArchivo");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1' style='border-collapse:collapse'>";
    echo "<tr style='background-color:#f97316;color:white;'>
            <th>Proyecto</th>
            <th>Costo Materiales</th>
            <th>Mano de Obra</th>
            <th>Otros Gastos</th>
            <th>Total</th>
          </tr>";

    foreach ($costos as $c) {
        echo "<tr>
                <td>{$c['proyecto']}</td>
                <td>{$c['costo_materiales']}</td>
                <td>{$c['costo_mano_obra']}</td>
                <td>{$c['otros_gastos']}</td>
                <td>{$c['total']}</td>
              </tr>";
    }

    echo "</table>";
    exit;

/* -------------------- TAREAS -------------------- */
case 'tareas':
case 'tareas/listar':
    require_login();
    global $pdo;
    $busqueda = $_GET['q'] ?? '';
    $sql = "SELECT t.*, p.nombre AS proyecto 
            FROM tareas t
            LEFT JOIN proyectos p ON t.proyecto_id = p.id";
    if ($busqueda) {
        $sql .= " WHERE t.nombre LIKE :busqueda OR p.nombre LIKE :busqueda";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':busqueda' => "%$busqueda%"]);
    } else {
        $stmt = $pdo->query($sql . " ORDER BY t.id DESC");
        $stmt = $stmt; // fetchAll después
    }
    $tareas = $stmt->fetchAll();
    render('tareas/listar.php', ['tareas' => $tareas]);
    break;

  case 'tareas/buscar':
    require_login();
    global $pdo;

    $busqueda = trim($_GET['q'] ?? '');

    // 🔹 Si no hay texto, mostrar todas las tareas
    if ($busqueda === '') {
        $sql = "SELECT t.*, p.nombre AS proyecto 
                FROM tareas t
                LEFT JOIN proyectos p ON t.proyecto_id = p.id
                ORDER BY t.id DESC";
        $stmt = $pdo->query($sql);
    } else {
        // 🔹 Si hay texto, buscar por nombre de tarea o proyecto
        $sql = "SELECT t.*, p.nombre AS proyecto 
                FROM tareas t
                LEFT JOIN proyectos p ON t.proyecto_id = p.id
                WHERE t.nombre LIKE :busqueda OR p.nombre LIKE :busqueda
                ORDER BY t.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':busqueda' => "%$busqueda%"]);
    }

    $tareas = $stmt->fetchAll();

    // 🔹 Renderizar solo la tabla (sin header ni layout)
   include __DIR__ . '/../app/views/tareas/tabla.php';

    break;



case 'tareas/form':
    require_login();
    global $pdo;

    $id = $_GET['id'] ?? null;
    $proyectos = $pdo->query("SELECT id, nombre FROM proyectos ORDER BY nombre ASC")->fetchAll();
    $csrf = csrf_token();
    $tarea = null;

    if ($id) {
        // Cargar tarea para edición
        $stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $tarea = $stmt->fetch();
        if (!$tarea) {
            flash("⚠️ Tarea no encontrada");
            redirectTo('tareas');
        }
    }

    render('tareas/form.php', ['proyectos' => $proyectos, 'csrf' => $csrf, 'tarea' => $tarea]);
    break;

case 'tareas/guardar':
    require_login();
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validate_csrf($_POST['csrf'] ?? '')) {
            die("⚠️ Petición no válida (CSRF).");
        }

        $id = $_POST['id'] ?? null;

        if ($id) {
            // ACTUALIZAR tarea
            $stmt = $pdo->prepare("UPDATE tareas SET 
                                    proyecto_id=:proyecto_id,
                                    nombre=:nombre,
                                    descripcion=:descripcion,
                                    estado=:estado,
                                    fecha_inicio=:fecha_inicio,
                                    fecha_fin=:fecha_fin
                                   WHERE id=:id");
            $stmt->execute([
                ':proyecto_id' => $_POST['proyecto_id'],
                ':nombre' => $_POST['nombre'],
                ':descripcion' => $_POST['descripcion'],
                ':estado' => $_POST['estado'],
                ':fecha_inicio' => $_POST['fecha_inicio'],
                ':fecha_fin' => $_POST['fecha_fin'],
                ':id' => $id
            ]);
            flash("✅ Tarea actualizada correctamente");
        } else {
            // NUEVA tarea
            $stmt = $pdo->prepare("INSERT INTO tareas 
                                   (proyecto_id, nombre, descripcion, estado, fecha_inicio, fecha_fin)
                                   VALUES (:proyecto_id, :nombre, :descripcion, :estado, :fecha_inicio, :fecha_fin)");
            $stmt->execute([
                ':proyecto_id' => $_POST['proyecto_id'],
                ':nombre' => $_POST['nombre'],
                ':descripcion' => $_POST['descripcion'],
                ':estado' => $_POST['estado'],
                ':fecha_inicio' => $_POST['fecha_inicio'],
                ':fecha_fin' => $_POST['fecha_fin']
            ]);
            flash("✅ Tarea registrada correctamente");
        }

        redirectTo('tareas');
    }
    break;

case 'tareas/delete':
    require_login();
    global $pdo;
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM tareas WHERE id=:id");
        $stmt->execute([':id' => $id]);
        flash("🗑️ Tarea eliminada correctamente");
    }
    redirectTo('tareas');
    break;

    case 'tareas/reporte':
    require_login();
    global $pdo;

    $id = $_GET['id'] ?? null;
    $busqueda = $_GET['q'] ?? '';

    $sql = "SELECT t.*, p.nombre AS proyecto 
            FROM tareas t
            LEFT JOIN proyectos p ON p.id = t.proyecto_id";

    if ($id) {
        // Reporte de una sola tarea
        $sql .= " WHERE t.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    } elseif ($busqueda) {
        // Reporte filtrado por búsqueda
        $sql .= " WHERE t.nombre LIKE :busqueda OR p.nombre LIKE :busqueda";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':busqueda' => "%$busqueda%"]);
    } else {
        // Reporte completo
        $stmt = $pdo->query($sql . " ORDER BY t.id DESC");
    }

    $tareas = $stmt->fetchAll();

    // Exportar a Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=ReporteTareas_" . date('Ymd_His') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1' style='border-collapse:collapse'>";
    echo "<tr style='background-color:#f97316;color:white;'>
            <th>ID</th>
            <th>Proyecto</th>
            <th>Nombre de la Tarea</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
          </tr>";

    foreach ($tareas as $t) {
        echo "<tr>
                <td>{$t['id']}</td>
                <td>{$t['proyecto']}</td>
                <td>{$t['nombre']}</td>
                <td>{$t['descripcion']}</td>
                <td>{$t['estado']}</td>
                <td>{$t['fecha_inicio']}</td>
                <td>{$t['fecha_fin']}</td>
              </tr>";
    }

    echo "</table>";
    exit;

    // ---------- LOGIN ----------
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!validate_csrf($token)) {
        $error = "Petición inválida (CSRF).";
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        if (login_user($email, $pass)) {
            $_POST = []; // Limpia los datos del form
            redirectTo('dashboard');
        } else {
            $error = "Credenciales incorrectas.";
        }
    }
} else {
    $error = null; // Evita mostrar error al recargar
}
render('auth/login.php', ['error' => $error, 'csrf' => csrf_token()], false);
        break;

    case 'logout':
        logout_user();
        redirectTo('login');
        break;

    case 'dashboard':
    require_login();
    // contadores para mostrar
    $counts = [
        'clientes'   => count_table('clientes'),
        'proyectos'  => count_table('proyectos'),
        'empleados'  => count_table('empleados'),
        'materiales' => count_table('materiales'),
        'tareas'     => count_table('tareas'),
        'costos'     => count_table('costos'),
        'pagos'      => count_table('pagos'),
    ];
    render('dashboard/dashboard.php', ['counts' => $counts]);
    break;


   /* -------------------- PROYECTOS -------------------- */
case 'proyectos':
    require_once __DIR__ . '/../app/controllers/ProyectosController.php';
    listarProyectos();
    break;

case 'proyectos/form':
    require_once __DIR__ . '/../app/controllers/ProyectosController.php';
    formularioProyecto();
    break;

case 'proyectos/guardar':
    require_once __DIR__ . '/../app/controllers/ProyectosController.php';
    guardarProyecto();
    break;

case 'proyectos/delete':
    require_once __DIR__ . '/../app/controllers/ProyectosController.php';
    eliminarProyecto();
    break;

case 'proyectos/buscar':
    require_once __DIR__ . '/../app/controllers/ProyectosController.php';
    buscarProyecto();
    break;
    
case 'proyectos/ganttData':
    require_once __DIR__ . '/../app/controllers/ProyectosController.php';
    ganttData();
    break;

case 'proyectos/reporte':
    require_login();
    global $pdo;

    $q = $_GET['q'] ?? '';
    $sql = "SELECT p.*, c.nombre AS cliente_nombre FROM proyectos p LEFT JOIN clientes c ON c.id=p.cliente_id";
    if (!empty($q)) {
        $sql .= " WHERE p.nombre LIKE :q OR c.nombre LIKE :q";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':q' => "%$q%"]);
        $proyectos = $stmt->fetchAll();
    } else {
        $proyectos = $pdo->query($sql . " ORDER BY p.id DESC")->fetchAll();
    }

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=ReporteProyectos_" . date('Ymd_His') . ".xls");
    echo "<table border='1'><tr style='background:#f97316;color:white;'>
            <th>ID</th><th>Proyecto</th><th>Cliente</th><th>Ubicación</th><th>Estado</th></tr>";
    foreach ($proyectos as $p) {
        echo "<tr>
                <td>{$p['id']}</td>
                <td>{$p['nombre']}</td>
                <td>{$p['cliente_nombre']}</td>
                <td>{$p['ubicacion']}</td>
                <td>{$p['estado']}</td>
              </tr>";
    }
    echo "</table>";
    exit;

   /* -------------------- MATERIALES -------------------- */
case 'materiales':
    require_once __DIR__ . '/../app/controllers/MaterialesController.php';
    listarMateriales();
    break;

case 'materiales/form':
    require_once __DIR__ . '/../app/controllers/MaterialesController.php';
    formularioMaterial();
    break;

case 'materiales/guardar':
    require_once __DIR__ . '/../app/controllers/MaterialesController.php';
    guardarMaterial();
    break;

case 'materiales/delete':
    require_once __DIR__ . '/../app/controllers/MaterialesController.php';
    eliminarMaterial();
    break;

case 'materiales/buscar':
    require_once __DIR__ . '/../app/controllers/MaterialesController.php';
    buscarMaterial();
    break;

case 'materiales/reporte':
    require_login();
    global $pdo;

    $q = $_GET['q'] ?? '';
    $sql = "SELECT m.*, p.nombre AS proyecto_nombre 
            FROM materiales m 
            LEFT JOIN proyectos p ON p.id = m.proyecto_id";
    if (!empty($q)) {
        $sql .= " WHERE m.nombre LIKE :q OR p.nombre LIKE :q OR m.proveedor LIKE :q";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':q' => "%$q%"]);
        $materiales = $stmt->fetchAll();
    } else {
        $materiales = $pdo->query($sql . " ORDER BY m.id DESC")->fetchAll();
    }

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=ReporteMateriales_" . date('Ymd_His') . ".xls");
    echo "<table border='1'><tr style='background:#f97316;color:white;'>
            <th>ID</th><th>Material</th><th>Proyecto</th><th>Proveedor</th><th>Cantidad</th><th>Costo Unitario</th></tr>";
    foreach ($materiales as $m) {
        echo "<tr>
                <td>{$m['id']}</td>
                <td>{$m['nombre']}</td>
                <td>{$m['proyecto_nombre']}</td>
                <td>{$m['proveedor']}</td>
                <td>{$m['cantidad']}</td>
                <td>{$m['costo_unitario']}</td>
              </tr>";
    }
    echo "</table>";
    exit;


    // ---------- EMPLEADOS ----------
    case 'empleados':
        require_login();
        $empleados = get_all_employees();
        render('empleados.php', ['empleados' => $empleados]);
        break;

    case 'empleados_create':
        require_login();
        $csrf = csrf_token();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf($_POST['csrf'] ?? '')) { $error = "Token inválido."; }
            else {
                create_employee([
                    'nombre' => $_POST['nombre'],
                    'puesto' => $_POST['puesto'],
                    'salario' => $_POST['salario'],
                    'telefono' => $_POST['telefono']
                ]);
                flash('Empleado agregado correctamente.');
                redirectTo('empleados');
            }
        }
        render('empleados_create.php', ['csrf' => $csrf, 'error' => $error]);
        break;

    // ---------- REPORTES ----------
    case 'reportes':
        require_login();
        $costos = get_all_costs();
        render('reportes.php', ['costos' => $costos]);
        break;

        
}
