<?php
// app/helpers.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../app/db.php';


// ---------- CSRF helpers ----------
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}
function validate_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ---------- render helpers ----------
/**
 * render(view, data, useLayout=true)
 * Carga una vista dentro de app/views/.
 * Ejemplo: render('usuarios/listar.php', [...])
 */
function render($view, $data = [], $useLayout = true)
{
    extract($data, EXTR_SKIP);
    $basePath = __DIR__ . '/../app/views/';
    $filePath = $basePath . ltrim($view, '/');

    // Si no tiene extensión .php, agregarla
    if (!str_ends_with($filePath, '.php')) {
        $filePath .= '.php';
    }

    // Verificar existencia del archivo
    if (!file_exists($filePath)) {
        die("❌ Error: No se encontró la vista '$filePath'");
    }

    // Cargar vista con o sin layout
    if ($useLayout) {
        $header = $basePath . 'layouts/header.php';
        $footer = $basePath . 'layouts/footer.php';
        if (file_exists($header)) require $header;
        require $filePath;
        if (file_exists($footer)) require $footer;
    } else {
        require $filePath;
    }
}


// ---------- flash messages ----------
function flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}
function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// ---------- auth ----------
function is_logged() {
    return !empty($_SESSION['user']);
}
function require_login() {
    if (!is_logged()) {
        header('Location: ' . BASE_URL . '/index.php?page=login');
        exit;
    }
}
function login_user($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        // remove password before storing in session
        unset($user['password']);
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}
function logout_user() {
    session_unset();
    session_destroy();
}

// ---------- helpers de redirección ----------
function redirectTo($page) {
    header('Location: ' . BASE_URL . '/index.php?page=' . $page);
    exit;
}

// ---------- CRUD y utilidades (clientes, proyectos, materiales, empleados, costos) ----------
function get_all_clients() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM clientes ORDER BY id DESC");
    return $stmt->fetchAll();
}
function create_client($data) {
    global $pdo;
    $sql = "INSERT INTO clientes (nombre, cedula, telefono, correo, direccion)
            VALUES (:nombre,:cedula,:telefono,:correo,:direccion)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $data['nombre'],
        ':cedula' => $data['cedula'],
        ':telefono' => $data['telefono'],
        ':correo' => $data['correo'],
        ':direccion' => $data['direccion'],
    ]);
    return $pdo->lastInsertId();
}

function get_all_projects() {
    global $pdo;
    $sql = "SELECT p.*, c.nombre as cliente_nombre
            FROM proyectos p
            LEFT JOIN clientes c ON c.id = p.cliente_id
            ORDER BY p.id DESC";
    return $pdo->query($sql)->fetchAll();
}
function create_project($data) {
    global $pdo;
    $sql = "INSERT INTO proyectos (cliente_id,nombre,ubicacion,fecha_inicio,fecha_fin,estado,descripcion)
            VALUES (:cliente_id,:nombre,:ubicacion,:fecha_inicio,:fecha_fin,:estado,:descripcion)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':cliente_id' => $data['cliente_id'],
        ':nombre' => $data['nombre'],
        ':ubicacion' => $data['ubicacion'],
        ':fecha_inicio' => $data['fecha_inicio'] ?: null,
        ':fecha_fin' => $data['fecha_fin'] ?: null,
        ':estado' => $data['estado'],
        ':descripcion' => $data['descripcion'],
    ]);
    return $pdo->lastInsertId();
}

function get_all_materials() {
    global $pdo;
    $sql = "SELECT m.*, p.nombre as proyecto FROM materiales m LEFT JOIN proyectos p ON p.id = m.proyecto_id ORDER BY m.id DESC";
    return $pdo->query($sql)->fetchAll();
}
function create_material($data) {
    global $pdo;
    $sql = "INSERT INTO materiales (proyecto_id,nombre,cantidad,costo_unitario,proveedor)
            VALUES (:proyecto_id,:nombre,:cantidad,:costo_unitario,:proveedor)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':proyecto_id' => $data['proyecto_id'],
        ':nombre' => $data['nombre'],
        ':cantidad' => $data['cantidad'],
        ':costo_unitario' => $data['costo_unitario'],
        ':proveedor' => $data['proveedor']
    ]);
    // recalcular costos del proyecto afectado
    recalc_costs($data['proyecto_id']);
    return $pdo->lastInsertId();
}

function get_all_employees() {
    global $pdo;
    return $pdo->query("SELECT * FROM empleados ORDER BY id DESC")->fetchAll();
}
function create_employee($data) {
    global $pdo;
    $sql = "INSERT INTO empleados (nombre,puesto,salario,telefono)
            VALUES (:nombre,:puesto,:salario,:telefono)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $data['nombre'],
        ':puesto' => $data['puesto'],
        ':salario' => $data['salario'],
        ':telefono' => $data['telefono'],
    ]);
    return $pdo->lastInsertId();
}

/**
 * Recalcula costos por proyecto: suma (cantidad * costo_unitario) de materiales
 * y actualiza/insert en tabla costos. Mantiene mano de obra/otros si ya existen.
 */
function recalc_costs($proyecto_id) {
    global $pdo;
    // total de materiales
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(cantidad * costo_unitario),0) as total_mat FROM materiales WHERE proyecto_id = ?");
    $stmt->execute([$proyecto_id]);
    $mat_total = floatval($stmt->fetchColumn());

    // obtener mano de obra y otros si existen
    $stmt2 = $pdo->prepare("SELECT costo_mano_obra, otros_gastos FROM costos WHERE proyecto_id = ?");
    $stmt2->execute([$proyecto_id]);
    $row = $stmt2->fetch();
    $mano_obra = $row ? floatval($row['costo_mano_obra']) : 0;
    $otros = $row ? floatval($row['otros_gastos']) : 0;

    $total = $mat_total + $mano_obra + $otros;

    if ($row) {
        $u = $pdo->prepare("UPDATE costos SET costo_materiales = ?, total = ? WHERE proyecto_id = ?");
        $u->execute([$mat_total, $total, $proyecto_id]);
    } else {
        $i = $pdo->prepare("INSERT INTO costos (proyecto_id,costo_materiales,costo_mano_obra,otros_gastos,total) VALUES (?,?,?,?,?)");
        $i->execute([$proyecto_id, $mat_total, $mano_obra, $otros, $total]);
    }
}

function get_all_costs() {
    global $pdo;
    $sql = "SELECT c.*, p.nombre as proyecto FROM costos c LEFT JOIN proyectos p ON p.id = c.proyecto_id ORDER BY c.id DESC";
    return $pdo->query($sql)->fetchAll();
}

// Dashboard counts
function count_table($table) {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM $table");
    return intval($stmt->fetchColumn());
}

// ========== PAGOS ==========
function get_all_pagos() {
    global $pdo;
    $sql = "SELECT pg.*, pr.nombre AS proyecto_nombre 
            FROM pagos pg 
            LEFT JOIN proyectos pr ON pr.id = pg.proyecto_id 
            ORDER BY pg.fecha DESC";
    return $pdo->query($sql)->fetchAll();
}

function create_pago($data) {
    global $pdo;
    $sql = "INSERT INTO pagos (proyecto_id, descripcion, monto, tipo, metodo_pago)
            VALUES (:proyecto_id, :descripcion, :monto, :tipo, :metodo_pago)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':proyecto_id' => $data['proyecto_id'],
        ':descripcion' => $data['descripcion'],
        ':monto' => $data['monto'],
        ':tipo' => $data['tipo'],
        ':metodo_pago' => $data['metodo_pago']
    ]);
    return $pdo->lastInsertId();
}

// helpers.php
function csrf_verify($token) {
    if (!isset($_SESSION)) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }

    return true;
}



// =============================================================
// Obtiene el usuario actualmente logueado desde la sesión
// =============================================================
function current_user()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    return $_SESSION['user'] ?? null;
}


// CSRF — Protección contra ataques de falsificación de solicitudes
function generar_csrf() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificar_csrf($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
