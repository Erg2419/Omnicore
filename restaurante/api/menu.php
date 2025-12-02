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
$item_id = end($uri_parts);

if (is_numeric($item_id)) {
    $item_id = (int)$item_id;
} else {
    $item_id = null;
}

// Determinar si es producto o categoría
$tipo = 'producto';
if (strpos($request_uri, '/categorias/') !== false) {
    $tipo = 'categoria';
}

switch ($method) {
    case 'GET':
        if ($tipo === 'categoria') {
            if ($item_id) {
                getCategoria($item_id, $connection);
            } else {
                getCategorias($connection);
            }
        } else {
            if ($item_id) {
                getProducto($item_id, $connection);
            } else {
                getProductos($connection);
            }
        }
        break;
        
    case 'POST':
        if ($tipo === 'categoria') {
            crearCategoria($connection);
        } else {
            crearProducto($connection);
        }
        break;
        
    case 'PUT':
        if ($item_id) {
            if ($tipo === 'categoria') {
                actualizarCategoria($item_id, $connection);
            } else {
                actualizarProducto($item_id, $connection);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID requerido']);
        }
        break;
        
    case 'DELETE':
        if ($item_id) {
            if ($tipo === 'categoria') {
                eliminarCategoria($item_id, $connection);
            } else {
                eliminarProducto($item_id, $connection);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID requerido']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        break;
}

// Funciones para Productos
function getProductos($connection) {
    try {
        $filters = [];
        $params = [];
        
        // Filtros
        if (isset($_GET['categoria'])) {
            $filters[] = "p.id_categoria = ?";
            $params[] = (int)$_GET['categoria'];
        }
        
        if (isset($_GET['estado'])) {
            $filters[] = "p.estado = ?";
            $params[] = sanitizeInput($_GET['estado']);
        }
        
        if (isset($_GET['disponible'])) {
            $filters[] = "p.estado = 'disponible'";
        }
        
        $where_clause = '';
        if (!empty($filters)) {
            $where_clause = 'WHERE ' . implode(' AND ', $filters);
        }
        
        $query = "SELECT 
                    p.*,
                    c.nombre as categoria_nombre,
                    c.descripcion as categoria_descripcion
                 FROM productos p
                 LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                 $where_clause
                 ORDER BY c.nombre, p.nombre";
        
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = array_map(function($producto) {
            return [
                'id' => (int)$producto['id_producto'],
                'nombre' => $producto['nombre'],
                'descripcion' => $producto['descripcion'],
                'precio' => (float)$producto['precio'],
                'categoria' => [
                    'id' => (int)$producto['id_categoria'],
                    'nombre' => $producto['categoria_nombre'],
                    'descripcion' => $producto['categoria_descripcion']
                ],
                'imagen' => $producto['imagen'],
                'estado' => $producto['estado'],
                'estado_texto' => $producto['estado'] === 'disponible' ? 'Disponible' : 'No Disponible',
                'tiempo_preparacion' => (int)$producto['tiempo_preparacion'],
                'fecha_creacion' => $producto['fecha_creacion'],
                'disponible' => $producto['estado'] === 'disponible'
            ];
        }, $productos);
        
        echo json_encode([
            'success' => true,
            'data' => $response,
            'total' => count($response)
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener productos: ' . $e->getMessage()
        ]);
    }
}

function getProducto($id, $connection) {
    try {
        $query = "SELECT 
                    p.*,
                    c.nombre as categoria_nombre,
                    c.descripcion as categoria_descripcion
                 FROM productos p
                 LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                 WHERE p.id_producto = ?";
        
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Producto no encontrado'
            ]);
            return;
        }
        
        $response = [
            'id' => (int)$producto['id_producto'],
            'nombre' => $producto['nombre'],
            'descripcion' => $producto['descripcion'],
            'precio' => (float)$producto['precio'],
            'categoria' => [
                'id' => (int)$producto['id_categoria'],
                'nombre' => $producto['categoria_nombre'],
                'descripcion' => $producto['categoria_descripcion']
            ],
            'imagen' => $producto['imagen'],
            'estado' => $producto['estado'],
            'estado_texto' => $producto['estado'] === 'disponible' ? 'Disponible' : 'No Disponible',
            'tiempo_preparacion' => (int)$producto['tiempo_preparacion'],
            'fecha_creacion' => $producto['fecha_creacion'],
            'disponible' => $producto['estado'] === 'disponible'
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $response
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener producto: ' . $e->getMessage()
        ]);
    }
}

function crearProducto($connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar campos requeridos
        $required_fields = ['nombre', 'precio', 'id_categoria'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => "Campo requerido: $field"
                ]);
                return;
            }
        }
        
        $query = "INSERT INTO productos (nombre, descripcion, precio, id_categoria, imagen, estado, tiempo_preparacion) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $connection->prepare($query);
        $stmt->execute([
            sanitizeInput($input['nombre']),
            sanitizeInput($input['descripcion'] ?? ''),
            (float)$input['precio'],
            (int)$input['id_categoria'],
            sanitizeInput($input['imagen'] ?? ''),
            sanitizeInput($input['estado'] ?? 'disponible'),
            (int)($input['tiempo_preparacion'] ?? 15)
        ]);
        
        $producto_id = $connection->lastInsertId();
        
        logActivity('API', 'Producto creado', "Producto {$input['nombre']} creado");
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'data' => ['id' => (int)$producto_id]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al crear producto: ' . $e->getMessage()
        ]);
    }
}

function actualizarProducto($id, $connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verificar si el producto existe
        $query_check = "SELECT * FROM productos WHERE id_producto = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Producto no encontrado'
            ]);
            return;
        }
        
        $updates = [];
        $params = [];
        
        $allowed_fields = ['nombre', 'descripcion', 'precio', 'id_categoria', 'imagen', 'estado', 'tiempo_preparacion'];
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                if ($field === 'precio') {
                    $params[] = (float)$input[$field];
                } elseif ($field === 'id_categoria' || $field === 'tiempo_preparacion') {
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
        $query = "UPDATE productos SET " . implode(', ', $updates) . " WHERE id_producto = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        
        logActivity('API', 'Producto actualizado', "Producto ID {$id} actualizado");
        
        echo json_encode([
            'success' => true,
            'message' => 'Producto actualizado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al actualizar producto: ' . $e->getMessage()
        ]);
    }
}

function eliminarProducto($id, $connection) {
    try {
        // Verificar si el producto existe
        $query_check = "SELECT * FROM productos WHERE id_producto = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Producto no encontrado'
            ]);
            return;
        }
        
        $producto = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Verificar si el producto está en órdenes activas
        $query_ordenes = "SELECT COUNT(*) as total FROM detalle_orden do 
                         JOIN ordenes o ON do.id_orden = o.id_orden 
                         WHERE do.id_producto = ? AND o.estado NOT IN ('pagada', 'cancelada')";
        $stmt_ordenes = $connection->prepare($query_ordenes);
        $stmt_ordenes->execute([$id]);
        
        if ($stmt_ordenes->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'No se puede eliminar el producto porque está en órdenes activas'
            ]);
            return;
        }
        
        // Eliminar producto
        $query = "DELETE FROM productos WHERE id_producto = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        
        logActivity('API', 'Producto eliminado', "Producto {$producto['nombre']} eliminado");
        
        echo json_encode([
            'success' => true,
            'message' => 'Producto eliminado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al eliminar producto: ' . $e->getMessage()
        ]);
    }
}

// Funciones para Categorías
function getCategorias($connection) {
    try {
        $query = "SELECT 
                    c.*,
                    COUNT(p.id_producto) as total_productos
                 FROM categorias c
                 LEFT JOIN productos p ON c.id_categoria = p.id_categoria
                 WHERE c.estado = 'activo'
                 GROUP BY c.id_categoria
                 ORDER BY c.nombre";
        
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = array_map(function($categoria) {
            return [
                'id' => (int)$categoria['id_categoria'],
                'nombre' => $categoria['nombre'],
                'descripcion' => $categoria['descripcion'],
                'estado' => $categoria['estado'],
                'total_productos' => (int)$categoria['total_productos'],
                'fecha_creacion' => $categoria['fecha_creacion']
            ];
        }, $categorias);
        
        echo json_encode([
            'success' => true,
            'data' => $response,
            'total' => count($response)
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener categorías: ' . $e->getMessage()
        ]);
    }
}

function getCategoria($id, $connection) {
    try {
        $query = "SELECT 
                    c.*,
                    COUNT(p.id_producto) as total_productos
                 FROM categorias c
                 LEFT JOIN productos p ON c.id_categoria = p.id_categoria
                 WHERE c.id_categoria = ?
                 GROUP BY c.id_categoria";
        
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$categoria) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Categoría no encontrada'
            ]);
            return;
        }
        
        // Obtener productos de esta categoría
        $query_productos = "SELECT * FROM productos WHERE id_categoria = ? AND estado = 'disponible'";
        $stmt_productos = $connection->prepare($query_productos);
        $stmt_productos->execute([$id]);
        $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'id' => (int)$categoria['id_categoria'],
            'nombre' => $categoria['nombre'],
            'descripcion' => $categoria['descripcion'],
            'estado' => $categoria['estado'],
            'total_productos' => (int)$categoria['total_productos'],
            'fecha_creacion' => $categoria['fecha_creacion'],
            'productos' => array_map(function($producto) {
                return [
                    'id' => (int)$producto['id_producto'],
                    'nombre' => $producto['nombre'],
                    'descripcion' => $producto['descripcion'],
                    'precio' => (float)$producto['precio'],
                    'imagen' => $producto['imagen'],
                    'tiempo_preparacion' => (int)$producto['tiempo_preparacion']
                ];
            }, $productos)
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $response
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener categoría: ' . $e->getMessage()
        ]);
    }
}

function crearCategoria($connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['nombre'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Campo requerido: nombre'
            ]);
            return;
        }
        
        // Verificar si la categoría ya existe
        $query_check = "SELECT COUNT(*) as total FROM categorias WHERE nombre = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([sanitizeInput($input['nombre'])]);
        
        if ($stmt_check->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'Ya existe una categoría con ese nombre'
            ]);
            return;
        }
        
        $query = "INSERT INTO categorias (nombre, descripcion, estado) VALUES (?, ?, 'activo')";
        $stmt = $connection->prepare($query);
        $stmt->execute([
            sanitizeInput($input['nombre']),
            sanitizeInput($input['descripcion'] ?? '')
        ]);
        
        $categoria_id = $connection->lastInsertId();
        
        logActivity('API', 'Categoría creada', "Categoría {$input['nombre']} creada");
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'data' => ['id' => (int)$categoria_id]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al crear categoría: ' . $e->getMessage()
        ]);
    }
}

function actualizarCategoria($id, $connection) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verificar si la categoría existe
        $query_check = "SELECT * FROM categorias WHERE id_categoria = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Categoría no encontrada'
            ]);
            return;
        }
        
        $updates = [];
        $params = [];
        
        $allowed_fields = ['nombre', 'descripcion', 'estado'];
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                $params[] = sanitizeInput($input[$field]);
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
        $query = "UPDATE categorias SET " . implode(', ', $updates) . " WHERE id_categoria = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        
        logActivity('API', 'Categoría actualizada', "Categoría ID {$id} actualizada");
        
        echo json_encode([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al actualizar categoría: ' . $e->getMessage()
        ]);
    }
}

function eliminarCategoria($id, $connection) {
    try {
        // Verificar si la categoría existe
        $query_check = "SELECT * FROM categorias WHERE id_categoria = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id]);
        
        if ($stmt_check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Categoría no encontrada'
            ]);
            return;
        }
        
        $categoria = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Verificar si la categoría tiene productos
        $query_productos = "SELECT COUNT(*) as total FROM productos WHERE id_categoria = ?";
        $stmt_productos = $connection->prepare($query_productos);
        $stmt_productos->execute([$id]);
        
        if ($stmt_productos->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'No se puede eliminar la categoría porque tiene productos asociados'
            ]);
            return;
        }
        
        // Eliminar categoría
        $query = "DELETE FROM categorias WHERE id_categoria = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        
        logActivity('API', 'Categoría eliminada', "Categoría {$categoria['nombre']} eliminada");
        
        echo json_encode([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al eliminar categoría: ' . $e->getMessage()
        ]);
    }
}
?>