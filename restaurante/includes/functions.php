<?php
/**
 * Sistema Restaurante - Funciones Helper
 * Funciones utilitarias para todo el sistema
 */

/**
 * Convierte un estado de mesa a una clase CSS
 */
function getMesaStatusClass($estado) {
    $classes = [
        'disponible' => 'disponible',
        'ocupada' => 'ocupada',
        'reservada' => 'reservada',
        'mantenimiento' => 'mantenimiento'
    ];
    return $classes[$estado] ?? 'disponible';
}

/**
 * Convierte un estado de orden a una clase CSS
 */
function getOrdenStatusClass($estado) {
    $classes = [
        'pendiente' => 'pendiente',
        'confirmada' => 'confirmada',
        'en_preparacion' => 'en_preparacion',
        'lista' => 'lista',
        'entregada' => 'entregada',
        'pagada' => 'pagada',
        'cancelada' => 'cancelada'
    ];
    return $classes[$estado] ?? 'pendiente';
}

/**
 * Obtiene el texto legible para un estado
 */
function getStatusText($estado) {
    $textos = [
        'pendiente' => 'Pendiente',
        'confirmada' => 'Confirmada',
        'en_preparacion' => 'En Preparación',
        'lista' => 'Lista para Servir',
        'entregada' => 'Entregada',
        'pagada' => 'Pagada',
        'cancelada' => 'Cancelada',
        'disponible' => 'Disponible',
        'ocupada' => 'Ocupada',
        'reservada' => 'Reservada',
        'mantenimiento' => 'En Mantenimiento'
    ];
    return $textos[$estado] ?? $estado;
}

/**
 * Formatea una cantidad monetaria
 */
function formatCurrency($amount) {
    return 'S/. ' . number_format($amount, 2);
}

/**
 * Formatea una fecha para mostrar
 */
function formatDate($date, $includeTime = true) {
    if (!$date) return '-';
    
    $timestamp = strtotime($date);
    if ($includeTime) {
        return date('d/m/Y H:i', $timestamp);
    }
    return date('d/m/Y', $timestamp);
}

/**
 * Calcula el tiempo transcurrido desde una fecha
 */
function timeAgo($date) {
    if (!$date) return '-';
    
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'hace ' . $diff . ' segundos';
    } elseif ($diff < 3600) {
        return 'hace ' . floor($diff / 60) . ' minutos';
    } elseif ($diff < 86400) {
        return 'hace ' . floor($diff / 3600) . ' horas';
    } else {
        return 'hace ' . floor($diff / 86400) . ' días';
    }
}

/**
 * Valida y sanitiza un string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Genera un código único para órdenes
 */
function generateOrderCode($id_orden) {
    return 'ORD-' . str_pad($id_orden, 6, '0', STR_PAD_LEFT);
}

/**
 * Calcula el total de una orden
 */
function calculateOrderTotal($id_orden, $connection) {
    try {
        $query = "SELECT SUM(subtotal) as total FROM detalle_orden WHERE id_orden = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id_orden]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Obtiene estadísticas rápidas del dashboard
 */
function getDashboardStats($connection) {
    $stats = [
        'total_mesas' => 0,
        'mesas_ocupadas' => 0,
        'ordenes_hoy' => 0,
        'ventas_hoy' => 0,
        'reservas_hoy' => 0,
        'productos_disponibles' => 0
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
        
        // Reservas hoy
        $query = "SELECT COUNT(*) as total FROM reservaciones WHERE DATE(fecha_reservacion) = CURDATE()";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $stats['reservas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Productos disponibles
        $query = "SELECT COUNT(*) as total FROM productos WHERE estado = 'disponible'";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $stats['productos_disponibles'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
    } catch (PDOException $e) {
        error_log("Error getting dashboard stats: " . $e->getMessage());
    }
    
    return $stats;
}

/**
 * Verifica si una mesa está disponible para reserva
 */
function isMesaAvailable($id_mesa, $fecha, $hora, $connection) {
    try {
        $fecha_reservacion = $fecha . ' ' . $hora . ':00';
        
        $query = "SELECT COUNT(*) as total FROM reservaciones 
                 WHERE id_mesa = ? AND fecha_reservacion = ? AND estado IN ('confirmada', 'pendiente')";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id_mesa, $fecha_reservacion]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] == 0;
    } catch (PDOException $e) {
        error_log("Error checking mesa availability: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el historial de movimientos de inventario
 */
function getInventoryHistory($id_inventario, $connection, $limit = 10) {
    try {
        $query = "SELECT * FROM inventario_historial 
                 WHERE id_inventario = ? 
                 ORDER BY fecha_movimiento DESC 
                 LIMIT ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id_inventario, $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting inventory history: " . $e->getMessage());
        return [];
    }
}

/**
 * Envía una notificación (podría integrarse con WebSockets o email)
 */
function sendNotification($titulo, $mensaje, $tipo = 'info') {
    // Por ahora solo guardamos en sesión para mostrar en la UI
    $_SESSION['notifications'][] = [
        'title' => $titulo,
        'message' => $mensaje,
        'type' => $tipo,
        'timestamp' => time()
    ];
}

/**
 * Genera un reporte de ventas por período
 */
function generateSalesReport($fecha_inicio, $fecha_fin, $connection) {
    try {
        $query = "SELECT 
                    DATE(fecha_orden) as fecha,
                    COUNT(*) as total_ordenes,
                    SUM(total) as total_ventas,
                    AVG(total) as promedio_venta
                 FROM ordenes 
                 WHERE fecha_orden BETWEEN ? AND ? 
                 AND estado = 'pagada'
                 GROUP BY DATE(fecha_orden)
                 ORDER BY fecha DESC";
        
        $stmt = $connection->prepare($query);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error generating sales report: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene los productos más vendidos
 */
function getTopSellingProducts($connection, $limit = 5) {
    try {
        $query = "SELECT 
                    p.nombre,
                    p.id_producto,
                    SUM(do.cantidad) as total_vendido,
                    SUM(do.subtotal) as total_ingresos
                 FROM detalle_orden do
                 JOIN productos p ON do.id_producto = p.id_producto
                 JOIN ordenes o ON do.id_orden = o.id_orden
                 WHERE o.estado = 'pagada'
                 GROUP BY p.id_producto, p.nombre
                 ORDER BY total_vendido DESC
                 LIMIT ?";
        
        $stmt = $connection->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting top selling products: " . $e->getMessage());
        return [];
    }
}

/**
 * Verifica permisos de usuario (sistema básico)
 */
function checkPermission($permiso) {
    // Por ahora todos los usuarios tienen todos los permisos
    // En un sistema real, aquí verificaríamos roles y permisos
    return true;
}

/**
 * Log de actividades del sistema
 */
function logActivity($usuario, $accion, $detalles = '') {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'usuario' => $usuario,
        'accion' => $accion,
        'detalles' => $detalles,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Guardar en archivo de log
    $log_file = __DIR__ . '/../logs/activity.log';
    file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Valida email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida teléfono (formato básico)
 */
function isValidPhone($phone) {
    return preg_match('/^[0-9\-\+\s\(\)]{7,15}$/', $phone);
}

/**
 * Genera contraseña segura
 */
function generateSecurePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    return $password;
}

/**
 * Encripta contraseña
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifica contraseña
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>