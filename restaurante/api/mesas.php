<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include '../includes/database.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$db = new Database();
$connection = $db->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Obtener parámetros de la URL
$request_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('/', $request_uri);
$mesa_id = end($uri_parts);

if (is_numeric($mesa_id)) {
    $mesa_id = (int)$mesa_id;
} else {
    $mesa_id = null;
}

switch ($method) {
    case 'GET':
        if ($mesa_id) {
            getMesa($mesa_id, $connection);
        } else {
            getMesas($connection);
        }
        break;
        
    case 'POST':
        if ($mesa_id && isset($_GET['action'])) {
            $action = $_GET['action'];
            if ($action === 'cambiar_estado') {
                cambiarEstadoMesa($mesa_id, $connection);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            }
        } else {
            crearMesa($connection);
        }
        break;
        
    case 'PUT':
        if ($mesa_id) {
            actualizarMesa($mesa_id, $connection);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de mesa requerido']);
        }
        break;
        
    case 'DELETE':
        if ($mesa_id) {
            eliminarMesa($mesa_id, $connection);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de mesa requerido']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        break;
}

function getMesas($connection) {
    try {
        $filters = [];
        $params = [];
        
        // Filtros
        if (isset($_GET['estado'])) {
            $filters[] = "estado = ?";
            $params[] = sanitizeInput($_GET['estado']);
        }
        
        if (isset($_GET['ubicacion'])) {
            $filters[] = "ubicacion = ?";
            $params[] = sanitizeInput($_GET['ubicacion']);
        }
        
        if (isset($_GET['disponible'])) {
            $filters[] = "estado = 'disponible'";
        }
        
        $where_clause = '';
        if (!empty($filters)) {
            $where_clause = 'WHERE ' . implode(' AND ', $filters);
        }
        
        $query = "SELECT * FROM mesas $where_clause ORDER BY numero_mesa";
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [];
        foreach ($mesas as $mesa) {
            // Obtener órdenes activas para esta mesa
            $query_ordenes = "SELECT COUNT(*) as ordenes_activas FROM ordenes 
                             WHERE id_mesa = ? AND estado NOT IN ('pagada', 'cancelada')";
            $stmt_ordenes = $connection->prepare($query_ordenes);
            $stmt_ordenes->execute([$mesa['id_mesa']]);
            $ordenes_activas = $stmt_ordenes->fetch(PDO::FETCH_ASSOC)['ordenes_activas'];
            
            $response[] = [
                'id' => (int)$mesa['id_mesa'],
                'numero_mesa' => $mesa['numero_mesa'],
                'capacidad' => (int)$mesa['capacidad'],
                'ubicacion' => $mesa['ubicacion'],
                'estado' => $mesa['estado'],
                'estado_texto' => getStatusText($mesa['estado']),
                'clase_css' => getMesaStatusClass($mesa['estado']),
                'ordenes_activas' => (int)$ordenes_activas,
                'disponible' => $mesa['estado'] === 'disponible'
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $response,
            'total' => count($response)
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener mesas: ' . $e->getMessage()
        ]);
    }
}

function getMesa($id, $connection) {
    try {
        $query = "SELECT * FROM mesas WHERE id_mesa = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        $mesa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$mesa) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Mesa no encontrada'
            ]);
            return;
        }
        
        // Obtener órdenes activas
        $query_ordenes = "SELECT 
                            o.id_orden,
                            o.estado,
                            o.total,
                            o.fecha_orden,
                            e.nombre as empleado_nombre
                         FROM ordenes o
                         LEFT JOIN empleados e ON o.id_empleado = e.id_empleado
                         WHERE o.id_mesa = ? AND o.estado NOT IN ('pagada', 'cancelada')
                         ORDER BY o.fecha_orden DESC";
        
        $stmt_ordenes = $connection->prepare($query_ordenes);
        $stmt_ordenes->execute([$id]);
        $ordenes_activas = $stmt_ordenes->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener reservas futuras
        $query_reservas = "SELECT 
                            r.id_reservacion,
                            r.fecha_reservacion,
                            r.numero_personas,
                            r.estado,
                            c.nombre as cliente_nombre
                         FROM reservaciones r
                         JOIN clientes c ON r.id_cliente = c.id_cliente
                         WHERE r.id_mesa = ? AND r.fecha_reservacion >= NOW()
                         AND r.estado IN ('confirmada', 'pendiente')
                         ORDER BY r.fecha_reservacion";
        
        $stmt_reservas = $connection->prepare($query_reservas);
        $stmt_reservas->execute([$id]);
        $reservas_futuras = $stmt_reservas->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'id' => (int)$mesa['id_mesa'],
            'numero_mesa' => $mesa['numero_mesa'],
            'capacidad' => (int)$mesa['capacidad'],
            'ubicacion' => $mesa['ubicacion'],
            'estado' => $mesa['estado'],
            'estado_texto' => getStatusText($mesa['estado']),
            'clase_css' => getMesaStatusClass($mesa['estado']),
            'ordenes_activas' => array_map(function($orden) {
                return [
                    'id' => (int)$orden['id_orden'],
                    'estado' => $orden['estado'],
                    'estado_texto' => getStatusText($orden['estado']),
                    'total' => (float)$orden['total'],
                    'fecha_orden' => $orden['fecha_orden'],
                    'empleado' => $orden['empleado_nombre']
                ];
            }, $ordenes_activas),
            'reservas_futuras' => array_map(function($reserva) {
                return [
                    'id' => (int)$reserva['id_reservacion'],
                    'fecha_reservacion' => $reserva['fecha_reservacion'],
                    'numero_personas' => (int)$reserva['numero_personas'],
                    'estado' => $reserva['estado'],
                    'cliente' => $reserva['cliente_nombre']
                ];
            }, $reservas_futuras)
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $response
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener mesa: ' . $e->getMessage()
        ]);
    }
}

function crearMesa($connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar campos requeridos
        if (!isset($input['numero_mesa']) || !isset($input['capacidad']) || !isset($input['ubicacion'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Campos requeridos: numero_mesa, capacidad, ubicacion'
            ]);
            return;
        }
        
        // Verificar si el número de mesa ya existe
        $query_check = "SELECT COUNT(*) as total FROM mesas WHERE numero_mesa = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([sanitizeInput($input['numero_mesa'])]);
        
        if ($stmt_check->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'Ya existe una mesa con ese número'
            ]);
            return;
        }
        
        // Insertar nueva mesa
        $query = "INSERT INTO mesas (numero_mesa, capacidad, ubicacion, estado) VALUES (?, ?, ?, 'disponible')";
        $stmt = $connection->prepare($query);
        $stmt->execute([
            sanitizeInput($input['numero_mesa']),
            (int)$input['capacidad'],
            sanitizeInput($input['ubicacion'])
        ]);
        
        $nueva_mesa_id = $connection->lastInsertId();
        
        // Obtener la mesa creada
        $query_mesa = "SELECT * FROM mesas WHERE id_mesa = ?";
        $stmt_mesa = $connection->prepare($query_mesa);
        $stmt_mesa->execute([$nueva_mesa_id]);
        $mesa = $stmt_mesa->fetch(PDO::FETCH_ASSOC);
        
        logActivity('API', 'Mesa creada', "Mesa {$input['numero_mesa']} creada");
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Mesa creada exitosamente',
            'data' => [
                'id' => (int)$mesa['id_mesa'],
                'numero_mesa' => $mesa['numero_mesa'],
                'capacidad' => (int)$mesa['capacidad'],
                'ubicacion' => $mesa['ubicacion'],
                'estado' => $mesa['estado']
            ]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al crear mesa: ' . $e->getMessage()
        ]);
    }
}

function actualizarMesa($id, $connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verificar si la mesa existe
        $query_check = "SELECT * FROM mesas WHERE id_mesa = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Mesa no encontrada'
            ]);
            return;
        }
        
        $mesa_actual = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Construir query dinámica
        $updates = [];
        $params = [];
        
        $allowed_fields = ['numero_mesa', 'capacidad', 'ubicacion', 'estado'];
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                if ($field === 'capacidad') {
                    $params[] = (int)$input[$field];
                } else {
                    $params[] = sanitizeInput($input[$field]);
                }
            }
        }
        
        if (empty($updates)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'No hay campos para actualizar'
            ]);
            return;
        }
        
        $params[] = $id;
        $query = "UPDATE mesas SET " . implode(', ', $updates) . " WHERE id_mesa = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        
        // Si se cambia el estado a disponible, verificar que no tenga órdenes activas
        if (isset($input['estado']) && $input['estado'] === 'disponible') {
            $query_ordenes = "SELECT COUNT(*) as total FROM ordenes 
                             WHERE id_mesa = ? AND estado NOT IN ('pagada', 'cancelada')";
            $stmt_ordenes = $connection->prepare($query_ordenes);
            $stmt_ordenes->execute([$id]);
            
            if ($stmt_ordenes->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se puede cambiar a disponible porque la mesa tiene órdenes activas'
                ]);
                return;
            }
        }
        
        logActivity('API', 'Mesa actualizada', "Mesa ID {$id} actualizada");
        
        echo json_encode([
            'success' => true,
            'message' => 'Mesa actualizada exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al actualizar mesa: ' . $e->getMessage()
        ]);
    }
}

function cambiarEstadoMesa($id, $connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['nuevo_estado'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Campo requerido: nuevo_estado'
            ]);
            return;
        }
        
        $nuevo_estado = sanitizeInput($input['nuevo_estado']);
        $estados_permitidos = ['disponible', 'ocupada', 'reservada', 'mantenimiento'];
        
        if (!in_array($nuevo_estado, $estados_permitidos)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Estado no válido'
            ]);
            return;
        }
        
        // Verificar si la mesa existe
        $query_check = "SELECT * FROM mesas WHERE id_mesa = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Mesa no encontrada'
            ]);
            return;
        }
        
        $mesa_actual = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Validaciones específicas por estado
        if ($nuevo_estado === 'disponible' && $mesa_actual['estado'] === 'ocupada') {
            $query_ordenes = "SELECT COUNT(*) as total FROM ordenes 
                             WHERE id_mesa = ? AND estado NOT IN ('pagada', 'cancelada')";
            $stmt_ordenes = $connection->prepare($query_ordenes);
            $stmt_ordenes->execute([$id]);
            
            if ($stmt_ordenes->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se puede liberar la mesa porque tiene órdenes activas'
                ]);
                return;
            }
        }
        
        // Actualizar estado
        $query = "UPDATE mesas SET estado = ? WHERE id_mesa = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$nuevo_estado, $id]);
        
        logActivity('API', 'Estado de mesa cambiado', 
            "Mesa ID {$id} cambió de {$mesa_actual['estado']} a {$nuevo_estado}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Estado de mesa actualizado exitosamente',
            'data' => [
                'estado_anterior' => $mesa_actual['estado'],
                'nuevo_estado' => $nuevo_estado
            ]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al cambiar estado de mesa: ' . $e->getMessage()
        ]);
    }
}

function eliminarMesa($id, $connection) {
    try {
        // Verificar si la mesa existe
        $query_check = "SELECT * FROM mesas WHERE id_mesa = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Mesa no encontrada'
            ]);
            return;
        }
        
        $mesa = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Verificar si la mesa tiene órdenes activas
        $query_ordenes = "SELECT COUNT(*) as total FROM ordenes 
                         WHERE id_mesa = ? AND estado NOT IN ('pagada', 'cancelada')";
        $stmt_ordenes = $connection->prepare($query_ordenes);
        $stmt_ordenes->execute([$id]);
        
        if ($stmt_ordenes->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'No se puede eliminar la mesa porque tiene órdenes activas'
            ]);
            return;
        }
        
        // Verificar si la mesa tiene reservas futuras
        $query_reservas = "SELECT COUNT(*) as total FROM reservaciones 
                          WHERE id_mesa = ? AND fecha_reservacion >= NOW() 
                          AND estado IN ('confirmada', 'pendiente')";
        $stmt_reservas = $connection->prepare($query_reservas);
        $stmt_reservas->execute([$id]);
        
        if ($stmt_reservas->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'No se puede eliminar la mesa porque tiene reservas futuras'
            ]);
            return;
        }
        
        // Eliminar mesa
        $query = "DELETE FROM mesas WHERE id_mesa = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        
        logActivity('API', 'Mesa eliminada', "Mesa {$mesa['numero_mesa']} eliminada");
        
        echo json_encode([
            'success' => true,
            'message' => 'Mesa eliminada exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al eliminar mesa: ' . $e->getMessage()
        ]);
    }
}
?>