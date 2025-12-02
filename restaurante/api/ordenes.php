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
$orden_id = end($uri_parts);

if (is_numeric($orden_id)) {
    $orden_id = (int)$orden_id;
} else {
    $orden_id = null;
}

switch ($method) {
    case 'GET':
        if ($orden_id) {
            getOrden($orden_id, $connection);
        } else {
            getOrdenes($connection);
        }
        break;
        
    case 'POST':
        if ($orden_id && isset($_GET['action'])) {
            $action = $_GET['action'];
            switch ($action) {
                case 'agregar_producto':
                    agregarProductoOrden($orden_id, $connection);
                    break;
                case 'cambiar_estado':
                    cambiarEstadoOrden($orden_id, $connection);
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            }
        } else {
            crearOrden($connection);
        }
        break;
        
    case 'PUT':
        if ($orden_id) {
            actualizarOrden($orden_id, $connection);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de orden requerido']);
        }
        break;
        
    case 'DELETE':
        if ($orden_id) {
            if (isset($_GET['detalle_id'])) {
                eliminarDetalleOrden($orden_id, (int)$_GET['detalle_id'], $connection);
            } else {
                eliminarOrden($orden_id, $connection);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de orden requerido']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        break;
}

function getOrdenes($connection) {
    try {
        $filters = [];
        $params = [];
        
        // Filtros
        if (isset($_GET['estado'])) {
            $filters[] = "o.estado = ?";
            $params[] = sanitizeInput($_GET['estado']);
        }
        
        if (isset($_GET['mesa'])) {
            $filters[] = "o.id_mesa = ?";
            $params[] = (int)$_GET['mesa'];
        }
        
        if (isset($_GET['fecha'])) {
            $filters[] = "DATE(o.fecha_orden) = ?";
            $params[] = sanitizeInput($_GET['fecha']);
        }
        
        if (isset($_GET['tipo_orden'])) {
            $filters[] = "o.tipo_orden = ?";
            $params[] = sanitizeInput($_GET['tipo_orden']);
        }
        
        if (isset($_GET['empleado'])) {
            $filters[] = "o.id_empleado = ?";
            $params[] = (int)$_GET['empleado'];
        }
        
        $where_clause = '';
        if (!empty($filters)) {
            $where_clause = 'WHERE ' . implode(' AND ', $filters);
        }
        
        $query = "SELECT 
                    o.*,
                    m.numero_mesa,
                    c.nombre as cliente_nombre,
                    c.telefono as cliente_telefono,
                    e.nombre as empleado_nombre
                 FROM ordenes o
                 LEFT JOIN mesas m ON o.id_mesa = m.id_mesa
                 LEFT JOIN clientes c ON o.id_cliente = c.id_cliente
                 LEFT JOIN empleados e ON o.id_empleado = e.id_empleado
                 $where_clause
                 ORDER BY o.fecha_orden DESC
                 LIMIT 100";
        
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [];
        foreach ($ordenes as $orden) {
            // Obtener detalles de la orden
            $query_detalles = "SELECT 
                                do.*,
                                p.nombre as producto_nombre,
                                p.descripcion as producto_descripcion,
                                p.imagen as producto_imagen
                             FROM detalle_orden do
                             JOIN productos p ON do.id_producto = p.id_producto
                             WHERE do.id_orden = ?";
            $stmt_detalles = $connection->prepare($query_detalles);
            $stmt_detalles->execute([$orden['id_orden']]);
            $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
            
            $response[] = [
                'id' => (int)$orden['id_orden'],
                'codigo' => generateOrderCode($orden['id_orden']),
                'mesa' => $orden['numero_mesa'] ? [
                    'id' => (int)$orden['id_mesa'],
                    'numero' => $orden['numero_mesa']
                ] : null,
                'cliente' => $orden['cliente_nombre'] ? [
                    'id' => (int)$orden['id_cliente'],
                    'nombre' => $orden['cliente_nombre'],
                    'telefono' => $orden['cliente_telefono']
                ] : null,
                'empleado' => [
                    'id' => (int)$orden['id_empleado'],
                    'nombre' => $orden['empleado_nombre']
                ],
                'estado' => $orden['estado'],
                'estado_texto' => getStatusText($orden['estado']),
                'clase_css' => getOrdenStatusClass($orden['estado']),
                'tipo_orden' => $orden['tipo_orden'],
                'total' => (float)$orden['total'],
                'observaciones' => $orden['observaciones'],
                'fecha_orden' => $orden['fecha_orden'],
                'fecha_actualizacion' => $orden['fecha_actualizacion'],
                'detalles' => array_map(function($detalle) {
                    return [
                        'id' => (int)$detalle['id_detalle'],
                        'producto' => [
                            'id' => (int)$detalle['id_producto'],
                            'nombre' => $detalle['producto_nombre'],
                            'descripcion' => $detalle['producto_descripcion'],
                            'imagen' => $detalle['producto_imagen']
                        ],
                        'cantidad' => (int)$detalle['cantidad'],
                        'precio_unitario' => (float)$detalle['precio_unitario'],
                        'subtotal' => (float)$detalle['subtotal'],
                        'observaciones' => $detalle['observaciones'],
                        'estado' => $detalle['estado'],
                        'estado_texto' => getStatusText($detalle['estado'])
                    ];
                }, $detalles)
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
            'error' => 'Error al obtener órdenes: ' . $e->getMessage()
        ]);
    }
}

function getOrden($id, $connection) {
    try {
        $query = "SELECT 
                    o.*,
                    m.numero_mesa,
                    c.nombre as cliente_nombre,
                    c.telefono as cliente_telefono,
                    c.email as cliente_email,
                    e.nombre as empleado_nombre
                 FROM ordenes o
                 LEFT JOIN mesas m ON o.id_mesa = m.id_mesa
                 LEFT JOIN clientes c ON o.id_cliente = c.id_cliente
                 LEFT JOIN empleados e ON o.id_empleado = e.id_empleado
                 WHERE o.id_orden = ?";
        
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        $orden = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$orden) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Orden no encontrada'
            ]);
            return;
        }
        
        // Obtener detalles de la orden
        $query_detalles = "SELECT 
                            do.*,
                            p.nombre as producto_nombre,
                            p.descripcion as producto_descripcion,
                            p.imagen as producto_imagen,
                            p.tiempo_preparacion
                         FROM detalle_orden do
                         JOIN productos p ON do.id_producto = p.id_producto
                         WHERE do.id_orden = ?";
        $stmt_detalles = $connection->prepare($query_detalles);
        $stmt_detalles->execute([$id]);
        $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'id' => (int)$orden['id_orden'],
            'codigo' => generateOrderCode($orden['id_orden']),
            'mesa' => $orden['numero_mesa'] ? [
                'id' => (int)$orden['id_mesa'],
                'numero' => $orden['numero_mesa']
            ] : null,
            'cliente' => $orden['cliente_nombre'] ? [
                'id' => (int)$orden['id_cliente'],
                'nombre' => $orden['cliente_nombre'],
                'telefono' => $orden['cliente_telefono'],
                'email' => $orden['cliente_email']
            ] : null,
            'empleado' => [
                'id' => (int)$orden['id_empleado'],
                'nombre' => $orden['empleado_nombre']
            ],
            'estado' => $orden['estado'],
            'estado_texto' => getStatusText($orden['estado']),
            'clase_css' => getOrdenStatusClass($orden['estado']),
            'tipo_orden' => $orden['tipo_orden'],
            'total' => (float)$orden['total'],
            'observaciones' => $orden['observaciones'],
            'fecha_orden' => $orden['fecha_orden'],
            'fecha_actualizacion' => $orden['fecha_actualizacion'],
            'detalles' => array_map(function($detalle) {
                return [
                    'id' => (int)$detalle['id_detalle'],
                    'producto' => [
                        'id' => (int)$detalle['id_producto'],
                        'nombre' => $detalle['producto_nombre'],
                        'descripcion' => $detalle['producto_descripcion'],
                        'imagen' => $detalle['producto_imagen'],
                        'tiempo_preparacion' => (int)$detalle['tiempo_preparacion']
                    ],
                    'cantidad' => (int)$detalle['cantidad'],
                    'precio_unitario' => (float)$detalle['precio_unitario'],
                    'subtotal' => (float)$detalle['subtotal'],
                    'observaciones' => $detalle['observaciones'],
                    'estado' => $detalle['estado'],
                    'estado_texto' => getStatusText($detalle['estado'])
                ];
            }, $detalles)
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $response
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener orden: ' . $e->getMessage()
        ]);
    }
}

function crearOrden($connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        // Validar campos requeridos
        if (!isset($input['id_empleado']) || !isset($input['detalles'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Campos requeridos: id_empleado, detalles'
            ]);
            return;
        }

        if (!is_array($input['detalles']) || empty($input['detalles'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'La orden debe contener al menos un producto'
            ]);
            return;
        }

        // Iniciar transacción
        $connection->beginTransaction();

        // Manejar cliente
        $id_cliente = null;
        if (isset($input['cliente_nombre']) && !empty($input['cliente_nombre'])) {
            // Buscar cliente existente o crear uno nuevo
            $query_buscar_cliente = "SELECT id_cliente FROM clientes WHERE nombre = ? LIMIT 1";
            $stmt_buscar = $connection->prepare($query_buscar_cliente);
            $stmt_buscar->execute([sanitizeInput($input['cliente_nombre'])]);
            $cliente_existente = $stmt_buscar->fetch(PDO::FETCH_ASSOC);

            if ($cliente_existente) {
                $id_cliente = $cliente_existente['id_cliente'];
            } else {
                // Crear nuevo cliente
                $query_nuevo_cliente = "INSERT INTO clientes (nombre) VALUES (?)";
                $stmt_nuevo = $connection->prepare($query_nuevo_cliente);
                $stmt_nuevo->execute([sanitizeInput($input['cliente_nombre'])]);
                $id_cliente = $connection->lastInsertId();
            }
        }

        // Crear la orden
        $fecha_pedido = isset($input['fecha_pedido']) ? sanitizeInput($input['fecha_pedido']) : date('Y-m-d H:i:s');
        $query_orden = "INSERT INTO ordenes (id_mesa, id_cliente, id_empleado, tipo_orden, observaciones, estado, fecha_orden)
                       VALUES (?, ?, ?, ?, ?, 'pendiente', ?)";

        $stmt_orden = $connection->prepare($query_orden);
        $stmt_orden->execute([
            isset($input['id_mesa']) ? (int)$input['id_mesa'] : null,
            $id_cliente,
            (int)$input['id_empleado'],
            $input['tipo_orden'] ?? 'mesa',
            $input['observaciones'] ?? '',
            $fecha_pedido
        ]);

        $orden_id = $connection->lastInsertId();
        $total_orden = 0;

        // Agregar detalles de la orden
        foreach ($input['detalles'] as $detalle) {
            if (!isset($detalle['id_producto']) || !isset($detalle['cantidad']) || $detalle['cantidad'] <= 0) {
                continue;
            }

            // Obtener precio del producto
            $query_producto = "SELECT precio FROM productos WHERE id_producto = ? AND estado = 'disponible'";
            $stmt_producto = $connection->prepare($query_producto);
            $stmt_producto->execute([(int)$detalle['id_producto']]);
            $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception("Producto no disponible o no encontrado: " . $detalle['id_producto']);
            }

            $precio_unitario = (float)$producto['precio'];
            $subtotal = $precio_unitario * (int)$detalle['cantidad'];
            $total_orden += $subtotal;

            $query_detalle = "INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario, subtotal, observaciones)
                             VALUES (?, ?, ?, ?, ?, ?)";

            $stmt_detalle = $connection->prepare($query_detalle);
            $stmt_detalle->execute([
                $orden_id,
                (int)$detalle['id_producto'],
                (int)$detalle['cantidad'],
                $precio_unitario,
                $subtotal,
                $detalle['observaciones'] ?? ''
            ]);
        }

        // Actualizar total de la orden
        $query_update_total = "UPDATE ordenes SET total = ? WHERE id_orden = ?";
        $stmt_update = $connection->prepare($query_update_total);
        $stmt_update->execute([$total_orden, $orden_id]);

        // Actualizar estado de la mesa si es orden en mesa
        if (isset($input['id_mesa']) && ($input['tipo_orden'] ?? 'mesa') === 'mesa') {
            $query_update_mesa = "UPDATE mesas SET estado = 'ocupada' WHERE id_mesa = ?";
            $stmt_mesa = $connection->prepare($query_update_mesa);
            $stmt_mesa->execute([(int)$input['id_mesa']]);
        }

        // Confirmar transacción
        $connection->commit();

        logActivity('API', 'Orden creada', "Orden #{$orden_id} creada por empleado {$input['id_empleado']}");

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Orden creada exitosamente',
            'data' => [
                'id' => (int)$orden_id,
                'codigo' => generateOrderCode($orden_id),
                'total' => (float)$total_orden
            ]
        ]);

    } catch (Exception $e) {
        $connection->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al crear orden: ' . $e->getMessage()
        ]);
    }
}

function agregarProductoOrden($orden_id, $connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id_producto']) || !isset($input['cantidad'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Campos requeridos: id_producto, cantidad'
            ]);
            return;
        }
        
        // Verificar si la orden existe y está en estado válido
        $query_orden = "SELECT * FROM ordenes WHERE id_orden = ?";
        $stmt_orden = $connection->prepare($query_orden);
        $stmt_orden->execute([$orden_id]);
        $orden = $stmt_orden->fetch(PDO::FETCH_ASSOC);
        
        if (!$orden) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Orden no encontrada'
            ]);
            return;
        }
        
        if ($orden['estado'] === 'pagada' || $orden['estado'] === 'cancelada') {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'No se puede modificar una orden pagada o cancelada'
            ]);
            return;
        }
        
        // Obtener precio del producto
        $query_producto = "SELECT precio FROM productos WHERE id_producto = ? AND estado = 'disponible'";
        $stmt_producto = $connection->prepare($query_producto);
        $stmt_producto->execute([(int)$input['id_producto']]);
        $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Producto no disponible o no encontrado'
            ]);
            return;
        }
        
        $precio_unitario = (float)$producto['precio'];
        $subtotal = $precio_unitario * (int)$input['cantidad'];
        
        // Insertar detalle
        $query_detalle = "INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario, subtotal, observaciones) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt_detalle = $connection->prepare($query_detalle);
        $stmt_detalle->execute([
            $orden_id,
            (int)$input['id_producto'],
            (int)$input['cantidad'],
            $precio_unitario,
            $subtotal,
            $input['observaciones'] ?? ''
        ]);
        
        // Actualizar total de la orden
        $nuevo_total = (float)$orden['total'] + $subtotal;
        $query_update = "UPDATE ordenes SET total = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_orden = ?";
        $stmt_update = $connection->prepare($query_update);
        $stmt_update->execute([$nuevo_total, $orden_id]);
        
        logActivity('API', 'Producto agregado a orden', 
            "Producto {$input['id_producto']} agregado a orden #{$orden_id}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Producto agregado a la orden exitosamente',
            'data' => [
                'nuevo_total' => $nuevo_total,
                'subtotal_agregado' => $subtotal
            ]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al agregar producto: ' . $e->getMessage()
        ]);
    }
}

function cambiarEstadoOrden($orden_id, $connection) {
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
        $estados_permitidos = ['pendiente', 'confirmada', 'en_preparacion', 'lista', 'entregada', 'pagada', 'cancelada'];
        
        if (!in_array($nuevo_estado, $estados_permitidos)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Estado no válido'
            ]);
            return;
        }
        
        // Verificar si la orden existe
        $query_orden = "SELECT * FROM ordenes WHERE id_orden = ?";
        $stmt_orden = $connection->prepare($query_orden);
        $stmt_orden->execute([$orden_id]);
        $orden = $stmt_orden->fetch(PDO::FETCH_ASSOC);
        
        if (!$orden) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Orden no encontrada'
            ]);
            return;
        }
        
        // Validaciones específicas por estado
        if ($nuevo_estado === 'pagada' && $orden['id_mesa']) {
            // Liberar la mesa cuando se paga la orden
            $query_mesa = "UPDATE mesas SET estado = 'disponible' WHERE id_mesa = ?";
            $stmt_mesa = $connection->prepare($query_mesa);
            $stmt_mesa->execute([$orden['id_mesa']]);
        }
        
        // Actualizar estado
        $query = "UPDATE ordenes SET estado = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_orden = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$nuevo_estado, $orden_id]);
        
        logActivity('API', 'Estado de orden cambiado', 
            "Orden #{$orden_id} cambió de {$orden['estado']} a {$nuevo_estado}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Estado de orden actualizado exitosamente',
            'data' => [
                'estado_anterior' => $orden['estado'],
                'nuevo_estado' => $nuevo_estado
            ]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al cambiar estado de orden: ' . $e->getMessage()
        ]);
    }
}

function actualizarOrden($orden_id, $connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        // Verificar si la orden existe
        $query_check = "SELECT * FROM ordenes WHERE id_orden = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$orden_id]);

        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Orden no encontrada'
            ]);
            return;
        }

        $orden_actual = $stmt_check->fetch(PDO::FETCH_ASSOC);

        // Manejar cliente
        $id_cliente = $orden_actual['id_cliente'];
        if (isset($input['cliente_nombre'])) {
            if (!empty($input['cliente_nombre'])) {
                // Buscar cliente existente o crear uno nuevo
                $query_buscar_cliente = "SELECT id_cliente FROM clientes WHERE nombre = ? LIMIT 1";
                $stmt_buscar = $connection->prepare($query_buscar_cliente);
                $stmt_buscar->execute([sanitizeInput($input['cliente_nombre'])]);
                $cliente_existente = $stmt_buscar->fetch(PDO::FETCH_ASSOC);

                if ($cliente_existente) {
                    $id_cliente = $cliente_existente['id_cliente'];
                } else {
                    // Crear nuevo cliente
                    $query_nuevo_cliente = "INSERT INTO clientes (nombre) VALUES (?)";
                    $stmt_nuevo = $connection->prepare($query_nuevo_cliente);
                    $stmt_nuevo->execute([sanitizeInput($input['cliente_nombre'])]);
                    $id_cliente = $connection->lastInsertId();
                }
            } else {
                $id_cliente = null;
            }
        }

        // Solo permitir actualizar ciertos campos
        $updates = [];
        $params = [];

        $allowed_fields = ['observaciones', 'id_empleado'];
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                $params[] = (int)$input[$field];
            }
        }

        // Siempre actualizar id_cliente
        $updates[] = "id_cliente = ?";
        $params[] = $id_cliente;

        if (empty($updates)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'No hay campos para actualizar'
            ]);
            return;
        }

        $params[] = $orden_id;
        $query = "UPDATE ordenes SET " . implode(', ', $updates) . ", fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_orden = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute($params);

        logActivity('API', 'Orden actualizada', "Orden #{$orden_id} actualizada");

        echo json_encode([
            'success' => true,
            'message' => 'Orden actualizada exitosamente'
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al actualizar orden: ' . $e->getMessage()
        ]);
    }
}

function eliminarDetalleOrden($orden_id, $detalle_id, $connection) {
    try {
        // Verificar si el detalle existe y pertenece a la orden
        $query_check = "SELECT * FROM detalle_orden WHERE id_detalle = ? AND id_orden = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$detalle_id, $orden_id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Detalle de orden no encontrado'
            ]);
            return;
        }
        
        $detalle = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Obtener la orden para actualizar el total
        $query_orden = "SELECT total FROM ordenes WHERE id_orden = ?";
        $stmt_orden = $connection->prepare($query_orden);
        $stmt_orden->execute([$orden_id]);
        $orden = $stmt_orden->fetch(PDO::FETCH_ASSOC);
        
        // Eliminar detalle
        $query_delete = "DELETE FROM detalle_orden WHERE id_detalle = ?";
        $stmt_delete = $connection->prepare($query_delete);
        $stmt_delete->execute([$detalle_id]);
        
        // Actualizar total de la orden
        $nuevo_total = (float)$orden['total'] - (float)$detalle['subtotal'];
        $query_update = "UPDATE ordenes SET total = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_orden = ?";
        $stmt_update = $connection->prepare($query_update);
        $stmt_update->execute([$nuevo_total, $orden_id]);
        
        logActivity('API', 'Detalle de orden eliminado', 
            "Detalle {$detalle_id} eliminado de orden #{$orden_id}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Producto eliminado de la orden exitosamente',
            'data' => [
                'nuevo_total' => $nuevo_total,
                'subtotal_eliminado' => (float)$detalle['subtotal']
            ]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al eliminar detalle de orden: ' . $e->getMessage()
        ]);
    }
}

function eliminarOrden($orden_id, $connection) {
    try {
        // Verificar si la orden existe
        $query_check = "SELECT * FROM ordenes WHERE id_orden = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$orden_id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Orden no encontrada'
            ]);
            return;
        }
        
        $orden = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Solo permitir eliminar órdenes en estados específicos
        $estados_permitidos = ['pendiente', 'cancelada'];
        if (!in_array($orden['estado'], $estados_permitidos)) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'Solo se pueden eliminar órdenes en estado pendiente o cancelada'
            ]);
            return;
        }
        
        // Iniciar transacción
        $connection->beginTransaction();
        
        // Eliminar detalles de la orden
        $query_detalles = "DELETE FROM detalle_orden WHERE id_orden = ?";
        $stmt_detalles = $connection->prepare($query_detalles);
        $stmt_detalles->execute([$orden_id]);
        
        // Eliminar orden
        $query_orden = "DELETE FROM ordenes WHERE id_orden = ?";
        $stmt_orden = $connection->prepare($query_orden);
        $stmt_orden->execute([$orden_id]);
        
        // Liberar mesa si era una orden en mesa
        if ($orden['id_mesa'] && $orden['tipo_orden'] === 'mesa') {
            $query_mesa = "UPDATE mesas SET estado = 'disponible' WHERE id_mesa = ?";
            $stmt_mesa = $connection->prepare($query_mesa);
            $stmt_mesa->execute([$orden['id_mesa']]);
        }
        
        // Confirmar transacción
        $connection->commit();
        
        logActivity('API', 'Orden eliminada', "Orden #{$orden_id} eliminada");
        
        echo json_encode([
            'success' => true,
            'message' => 'Orden eliminada exitosamente'
        ]);
        
    } catch (PDOException $e) {
        $connection->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al eliminar orden: ' . $e->getMessage()
        ]);
    }
}
?>