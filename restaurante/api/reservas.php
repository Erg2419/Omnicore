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
$reserva_id = end($uri_parts);

if (is_numeric($reserva_id)) {
    $reserva_id = (int)$reserva_id;
} else {
    $reserva_id = null;
}

switch ($method) {
    case 'GET':
        if ($reserva_id) {
            getReserva($reserva_id, $connection);
        } else {
            getReservas($connection);
        }
        break;
        
    case 'POST':
        $action = $_GET['action'] ?? null;
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['action'])) {
            $action = $input['action'];
        }

        if ($reserva_id && $action === 'cambiar_estado') {
            cambiarEstadoReserva($reserva_id, $connection);
        } elseif (!$reserva_id || $action !== 'cambiar_estado') {
            crearReserva($connection);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        }
        break;
        
    case 'PUT':
        if ($reserva_id) {
            actualizarReserva($reserva_id, $connection);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de reserva requerido']);
        }
        break;
        
    case 'DELETE':
        if ($reserva_id) {
            eliminarReserva($reserva_id, $connection);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de reserva requerido']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        break;
}

function getReservas($connection) {
    try {
        $filters = [];
        $params = [];
        
        // Filtros
        if (isset($_GET['estado'])) {
            $filters[] = "r.estado = ?";
            $params[] = sanitizeInput($_GET['estado']);
        }
        
        if (isset($_GET['fecha'])) {
            $filters[] = "DATE(r.fecha_reservacion) = ?";
            $params[] = sanitizeInput($_GET['fecha']);
        }
        
        if (isset($_GET['mesa'])) {
            $filters[] = "r.id_mesa = ?";
            $params[] = (int)$_GET['mesa'];
        }
        
        if (isset($_GET['cliente'])) {
            $filters[] = "r.id_cliente = ?";
            $params[] = (int)$_GET['cliente'];
        }
        
        if (isset($_GET['futuras'])) {
            $filters[] = "r.fecha_reservacion >= NOW()";
        }
        
        $where_clause = '';
        if (!empty($filters)) {
            $where_clause = 'WHERE ' . implode(' AND ', $filters);
        }
        
        $query = "SELECT 
                    r.*,
                    c.nombre as cliente_nombre,
                    c.telefono as cliente_telefono,
                    c.email as cliente_email,
                    m.numero_mesa,
                    m.capacidad as mesa_capacidad,
                    m.ubicacion as mesa_ubicacion
                 FROM reservaciones r
                 JOIN clientes c ON r.id_cliente = c.id_cliente
                 JOIN mesas m ON r.id_mesa = m.id_mesa
                 $where_clause
                 ORDER BY r.fecha_reservacion DESC
                 LIMIT 100";
        
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = array_map(function($reserva) {
            return [
                'id' => (int)$reserva['id_reservacion'],
                'cliente' => [
                    'id' => (int)$reserva['id_cliente'],
                    'nombre' => $reserva['cliente_nombre'],
                    'telefono' => $reserva['cliente_telefono'],
                    'email' => $reserva['cliente_email']
                ],
                'mesa' => [
                    'id' => (int)$reserva['id_mesa'],
                    'numero' => $reserva['numero_mesa'],
                    'capacidad' => (int)$reserva['mesa_capacidad'],
                    'ubicacion' => $reserva['mesa_ubicacion']
                ],
                'fecha_reservacion' => $reserva['fecha_reservacion'],
                'numero_personas' => (int)$reserva['numero_personas'],
                'estado' => $reserva['estado'],
                'estado_texto' => getStatusText($reserva['estado']),
                'observaciones' => $reserva['observaciones'],
                'fecha_creacion' => $reserva['fecha_creacion'],
                'es_futura' => strtotime($reserva['fecha_reservacion']) > time()
            ];
        }, $reservas);
        
        echo json_encode([
            'success' => true,
            'data' => $response,
            'total' => count($response)
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener reservas: ' . $e->getMessage()
        ]);
    }
}

function getReserva($id, $connection) {
    try {
        $query = "SELECT 
                    r.*,
                    c.nombre as cliente_nombre,
                    c.telefono as cliente_telefono,
                    c.email as cliente_email,
                    m.numero_mesa,
                    m.capacidad as mesa_capacidad,
                    m.ubicacion as mesa_ubicacion
                 FROM reservaciones r
                 JOIN clientes c ON r.id_cliente = c.id_cliente
                 JOIN mesas m ON r.id_mesa = m.id_mesa
                 WHERE r.id_reservacion = ?";
        
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reserva) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Reserva no encontrada'
            ]);
            return;
        }
        
        $response = [
            'id' => (int)$reserva['id_reservacion'],
            'cliente' => [
                'id' => (int)$reserva['id_cliente'],
                'nombre' => $reserva['cliente_nombre'],
                'telefono' => $reserva['cliente_telefono'],
                'email' => $reserva['cliente_email']
            ],
            'mesa' => [
                'id' => (int)$reserva['id_mesa'],
                'numero' => $reserva['numero_mesa'],
                'capacidad' => (int)$reserva['mesa_capacidad'],
                'ubicacion' => $reserva['mesa_ubicacion']
            ],
            'fecha_reservacion' => $reserva['fecha_reservacion'],
            'numero_personas' => (int)$reserva['numero_personas'],
            'estado' => $reserva['estado'],
            'estado_texto' => getStatusText($reserva['estado']),
            'observaciones' => $reserva['observaciones'],
            'fecha_creacion' => $reserva['fecha_creacion'],
            'es_futura' => strtotime($reserva['fecha_reservacion']) > time()
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $response
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener reserva: ' . $e->getMessage()
        ]);
    }
}

function crearReserva($connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        // Validar campos requeridos
        $required_fields = ['nombre_cliente', 'id_mesa', 'fecha_reservacion', 'numero_personas'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => "Campo requerido: $field"
                ]);
                return;
            }
        }
        
        $fecha_reservacion = date('Y-m-d H:i:s', strtotime($input['fecha_reservacion']));
        
        // Verificar disponibilidad de la mesa
        $query_disponibilidad = "SELECT COUNT(*) as total FROM reservaciones 
                               WHERE id_mesa = ? AND fecha_reservacion = ? 
                               AND estado IN ('confirmada', 'pendiente')";
        $stmt_disponibilidad = $connection->prepare($query_disponibilidad);
        $stmt_disponibilidad->execute([(int)$input['id_mesa'], $fecha_reservacion]);
        
        if ($stmt_disponibilidad->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'La mesa no está disponible en esa fecha y hora'
            ]);
            return;
        }
        
        // Verificar capacidad de la mesa
        $query_capacidad = "SELECT capacidad FROM mesas WHERE id_mesa = ?";
        $stmt_capacidad = $connection->prepare($query_capacidad);
        $stmt_capacidad->execute([(int)$input['id_mesa']]);
        $mesa = $stmt_capacidad->fetch(PDO::FETCH_ASSOC);
        
        if (!$mesa || (int)$input['numero_personas'] > $mesa['capacidad']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'El número de personas excede la capacidad de la mesa'
            ]);
            return;
        }
        
        // Manejar cliente
        $id_cliente = null;
        if (!empty($input['nombre_cliente'])) {
            // Buscar cliente existente o crear uno nuevo
            $query_buscar_cliente = "SELECT id_cliente FROM clientes WHERE nombre = ? LIMIT 1";
            $stmt_buscar = $connection->prepare($query_buscar_cliente);
            $stmt_buscar->execute([sanitizeInput($input['nombre_cliente'])]);
            $cliente_existente = $stmt_buscar->fetch(PDO::FETCH_ASSOC);

            if ($cliente_existente) {
                $id_cliente = $cliente_existente['id_cliente'];
            } else {
                // Crear nuevo cliente
                $query_nuevo_cliente = "INSERT INTO clientes (nombre, telefono) VALUES (?, ?)";
                $stmt_nuevo = $connection->prepare($query_nuevo_cliente);
                $stmt_nuevo->execute([sanitizeInput($input['nombre_cliente']), sanitizeInput($input['telefono'] ?? '')]);
                $id_cliente = $connection->lastInsertId();
            }
        }

        // Crear reserva
        $query = "INSERT INTO reservaciones (id_cliente, id_mesa, fecha_reservacion, numero_personas, observaciones, estado)
                 VALUES (?, ?, ?, ?, ?, 'pendiente')";

        $stmt = $connection->prepare($query);
        $stmt->execute([
            $id_cliente,
            (int)$input['id_mesa'],
            $fecha_reservacion,
            (int)$input['numero_personas'],
            sanitizeInput($input['observaciones'] ?? '')
        ]);
        
        $reserva_id = $connection->lastInsertId();
        
        // Actualizar estado de la mesa
        $query_mesa = "UPDATE mesas SET estado = 'reservada' WHERE id_mesa = ?";
        $stmt_mesa = $connection->prepare($query_mesa);
        $stmt_mesa->execute([(int)$input['id_mesa']]);
        
        logActivity('API', 'Reserva creada', "Reserva #{$reserva_id} creada para cliente {$id_cliente}");
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Reserva creada exitosamente',
            'data' => ['id' => (int)$reserva_id]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al crear reserva: ' . $e->getMessage()
        ]);
    }
}

function cambiarEstadoReserva($id, $connection) {
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
        $estados_permitidos = ['pendiente', 'confirmada', 'cancelada', 'completada'];
        
        if (!in_array($nuevo_estado, $estados_permitidos)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Estado no válido'
            ]);
            return;
        }
        
        // Verificar si la reserva existe
        $query_check = "SELECT * FROM reservaciones WHERE id_reservacion = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Reserva no encontrada'
            ]);
            return;
        }
        
        $reserva_actual = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Actualizar estado
        $query = "UPDATE reservaciones SET estado = ? WHERE id_reservacion = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$nuevo_estado, $id]);
        
        // Manejar cambios de estado de la mesa
        if (in_array($nuevo_estado, ['cancelada', 'completada'])) {
            // Liberar la mesa
            $query_mesa = "UPDATE mesas SET estado = 'disponible' WHERE id_mesa = ?";
            $stmt_mesa = $connection->prepare($query_mesa);
            $stmt_mesa->execute([$reserva_actual['id_mesa']]);
        } elseif ($nuevo_estado === 'confirmada') {
            // Asegurar que la mesa esté como reservada
            $query_mesa = "UPDATE mesas SET estado = 'reservada' WHERE id_mesa = ?";
            $stmt_mesa = $connection->prepare($query_mesa);
            $stmt_mesa->execute([$reserva_actual['id_mesa']]);
        }
        
        logActivity('API', 'Estado de reserva cambiado', 
            "Reserva #{$id} cambió de {$reserva_actual['estado']} a {$nuevo_estado}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Estado de reserva actualizado exitosamente',
            'data' => [
                'estado_anterior' => $reserva_actual['estado'],
                'nuevo_estado' => $nuevo_estado
            ]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al cambiar estado de reserva: ' . $e->getMessage()
        ]);
    }
}

function actualizarReserva($id, $connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verificar si la reserva existe
        $query_check = "SELECT * FROM reservaciones WHERE id_reservacion = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Reserva no encontrada'
            ]);
            return;
        }
        
        $reserva_actual = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        $updates = [];
        $params = [];
        
        $allowed_fields = ['observaciones', 'numero_personas'];
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                if ($field === 'numero_personas') {
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
        $query = "UPDATE reservaciones SET " . implode(', ', $updates) . " WHERE id_reservacion = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        
        logActivity('API', 'Reserva actualizada', "Reserva #{$id} actualizada");
        
        echo json_encode([
            'success' => true,
            'message' => 'Reserva actualizada exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al actualizar reserva: ' . $e->getMessage()
        ]);
    }
}

function eliminarReserva($id, $connection) {
    try {
        // Verificar si la reserva existe
        $query_check = "SELECT * FROM reservaciones WHERE id_reservacion = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Reserva no encontrada'
            ]);
            return;
        }
        
        $reserva = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Liberar la mesa
        $query_mesa = "UPDATE mesas SET estado = 'disponible' WHERE id_mesa = ?";
        $stmt_mesa = $connection->prepare($query_mesa);
        $stmt_mesa->execute([$reserva['id_mesa']]);
        
        // Eliminar reserva
        $query = "DELETE FROM reservaciones WHERE id_reservacion = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        
        logActivity('API', 'Reserva eliminada', "Reserva #{$id} eliminada");
        
        echo json_encode([
            'success' => true,
            'message' => 'Reserva eliminada exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al eliminar reserva: ' . $e->getMessage()
        ]);
    }
}
?>