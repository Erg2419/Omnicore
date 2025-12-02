<?php
// includes/actions.php
session_start();
include 'database.php';

// Configurar cabecera para respuestas JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $db = new Database();
    $connection = $db->getConnection();
    
    switch ($action) {
        case 'agregar_mesa':
            agregarMesa($connection, $_POST);
            break;
            
        case 'crear_orden':
            crearOrden($connection, $_POST);
            break;
            
        case 'agregar_producto':
            agregarProducto($connection, $_POST);
            break;
            
        case 'editar_producto':
            editarProducto($connection, $_POST);
            break;
            
        case 'cambiar_estado_producto':
            cambiarEstadoProducto($connection, $_POST);
            break;
            
        case 'eliminar_producto':
            eliminarProducto($connection, $_POST);
            break;
            
        case 'agregar_categoria':
            agregarCategoria($connection, $_POST);
            break;
            
        case 'editar_categoria':
            editarCategoria($connection, $_POST);
            break;
            
        case 'crear_reserva':
            crearReserva($connection, $_POST);
            break;
            
        case 'obtener_categoria_por_nombre':
            obtenerCategoriaPorNombre($connection, $_POST);
            break;

        case 'editar_orden':
            editarOrden($connection, $_POST);
            break;

        case 'cambiar_estado_orden':
            cambiarEstadoOrden($connection, $_POST);
            break;

        case 'eliminar_orden':
            eliminarOrden($connection, $_POST);
            break;

        case 'eliminar_producto_orden':
            eliminarProductoOrden($connection, $_POST);
            break;

        case 'actualizar_perfil':
            actualizarPerfil($connection, $_POST);
            break;

        case 'agregar_usuario':
            agregarUsuario($connection, $_POST);
            break;

        case 'editar_usuario':
            editarUsuario($connection, $_POST);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $db = new Database();
    $connection = $db->getConnection();
    
    switch ($action) {
        case 'obtener_producto':
            obtenerProducto($connection, $_GET);
            break;
            
        case 'obtener_categoria':
            obtenerCategoria($connection, $_GET);
            break;
            
        case 'obtener_productos':
            obtenerProductos($connection);
            break;
            
        case 'obtener_categorias':
            obtenerCategorias($connection);
            break;
            
        case 'obtener_productos_por_categoria':
            obtenerProductosPorCategoria($connection, $_GET);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit;
    }
}

function agregarMesa($connection, $data) {
    try {
        $query = "INSERT INTO mesas (numero_mesa, capacidad, ubicacion) VALUES (?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->execute([
            $data['numero_mesa'],
            $data['capacidad'],
            $data['ubicacion']
        ]);
        
        $_SESSION['success'] = 'Mesa agregada exitosamente';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error al agregar mesa: ' . $e->getMessage();
    }
    
    header('Location: ../index.php?page=mesas');
    exit;
}

function crearOrden($connection, $data) {
    try {
        // Debug: Verificar datos recibidos
        error_log("Datos recibidos en crearOrden: " . print_r($data, true));

        // Enable error reporting for debugging
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        
        // Iniciar transacción
        $connection->beginTransaction();

        // Manejar cliente
        $id_cliente = null;
        if (!empty($data['cliente_nombre'])) {
            // Buscar cliente existente o crear uno nuevo
            $query_buscar_cliente = "SELECT id_cliente FROM clientes WHERE nombre = ? LIMIT 1";
            $stmt_buscar = $connection->prepare($query_buscar_cliente);
            $stmt_buscar->execute([trim($data['cliente_nombre'])]);
            $cliente_existente = $stmt_buscar->fetch(PDO::FETCH_ASSOC);

            if ($cliente_existente) {
                $id_cliente = $cliente_existente['id_cliente'];
            } else {
                // Crear nuevo cliente
                $query_nuevo_cliente = "INSERT INTO clientes (nombre) VALUES (?)";
                $stmt_nuevo = $connection->prepare($query_nuevo_cliente);
                $stmt_nuevo->execute([trim($data['cliente_nombre'])]);
                $id_cliente = $connection->lastInsertId();
            }
        }

        // Preparar fecha - convertir de datetime-local a formato MySQL
        $fecha_pedido = date('Y-m-d H:i:s');
        if (!empty($data['fecha_pedido'])) {
            // Convertir de "2023-12-25T14:30" a "2023-12-25 14:30:00"
            $fecha_pedido = str_replace('T', ' ', $data['fecha_pedido']) . ':00';
        }

        // Crear la orden - SIN el campo estado ya que tiene valor por defecto
        $query = "INSERT INTO ordenes (id_mesa, id_cliente, id_empleado, tipo_orden, fecha_orden) VALUES (?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        
        $stmt->execute([
            !empty($data['id_mesa']) ? (int)$data['id_mesa'] : null,
            $id_cliente,
            (int)$data['id_empleado'],
            $data['tipo_orden'],
            $fecha_pedido
        ]);
        
        $id_orden = $connection->lastInsertId();
        error_log("Orden creada con ID: " . $id_orden);

        // Agregar productos a la orden
        $total = 0;
        $productos_procesados = [];

        // Buscar productos en los datos
        if (isset($data['productos']) && is_array($data['productos'])) {
            foreach ($data['productos'] as $id_producto => $producto_data) {
                $cantidad = intval($producto_data['cantidad'] ?? 0);

                if ($cantidad > 0) {
                    error_log("Procesando producto: ID=$id_producto, Cantidad=$cantidad");

                    // Obtener precio del producto
                    $query_producto = "SELECT precio, nombre FROM productos WHERE id_producto = ?";
                    $stmt_producto = $connection->prepare($query_producto);
                    $stmt_producto->execute([(int)$id_producto]);
                    $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

                    if ($producto) {
                        $precio_unitario = floatval($producto['precio']);
                        $subtotal = $precio_unitario * $cantidad;
                        $total += $subtotal;

                        error_log("Producto encontrado: {$producto['nombre']}, Precio: $precio_unitario, Subtotal: $subtotal");

                        // Insertar detalle de orden
                        $query_detalle = "INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
                        $stmt_detalle = $connection->prepare($query_detalle);
                        $stmt_detalle->execute([
                            $id_orden,
                            (int)$id_producto,
                            $cantidad,
                            $precio_unitario,
                            $subtotal
                        ]);

                        $productos_procesados[] = [
                            'id' => $id_producto,
                            'nombre' => $producto['nombre'],
                            'cantidad' => $cantidad,
                            'subtotal' => $subtotal
                        ];

                        error_log("Detalle insertado para producto $id_producto");
                    } else {
                        error_log("Producto no encontrado: $id_producto");
                        throw new Exception("Producto no encontrado: ID $id_producto");
                    }
                }
            }
        }

        // Verificar que se hayan procesado productos
        if (count($productos_procesados) === 0) {
            throw new Exception("No se seleccionaron productos válidos para la orden");
        }

        // Actualizar total de la orden
        $query_update = "UPDATE ordenes SET total = ? WHERE id_orden = ?";
        $stmt_update = $connection->prepare($query_update);
        $stmt_update->execute([$total, $id_orden]);
        
        error_log("Total actualizado: $total para orden $id_orden");

        // Actualizar estado de la mesa si es orden en mesa
        if (!empty($data['id_mesa']) && $data['tipo_orden'] === 'mesa') {
            $query_mesa = "UPDATE mesas SET estado = 'ocupada' WHERE id_mesa = ?";
            $stmt_mesa = $connection->prepare($query_mesa);
            $stmt_mesa->execute([(int)$data['id_mesa']]);
            error_log("Mesa actualizada a ocupada: " . $data['id_mesa']);
        }

        // Confirmar transacción
        $connection->commit();
        
        error_log("Orden creada exitosamente: $id_orden con " . count($productos_procesados) . " productos");

        echo json_encode([
            'success' => true, 
            'message' => 'Orden creada exitosamente',
            'id_orden' => $id_orden,
            'total' => $total,
            'productos' => $productos_procesados
        ]);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        error_log("Error al crear orden: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error al crear orden: ' . $e->getMessage()
        ]);
    }
    exit;
}

function agregarProducto($connection, $data) {
    try {
        // Manejar la imagen
        $imagen = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/productos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                echo json_encode(['success' => false, 'message' => 'Formato de imagen no permitido. Use JPG, PNG o GIF.']);
                exit;
            }
            
            $fileName = uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadPath)) {
                $imagen = 'uploads/productos/' . $fileName;
            }
        }
        
        $query = "INSERT INTO productos (nombre, descripcion, precio, id_categoria, tiempo_preparacion, estado, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['precio'],
            $data['id_categoria'],
            $data['tiempo_preparacion'],
            $data['estado'],
            $imagen
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Producto agregado exitosamente']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al agregar producto: ' . $e->getMessage()]);
    }
    exit;
}

function editarProducto($connection, $data) {
    try {
        // Obtener producto actual para mantener la imagen si no se sube una nueva
        $query = "SELECT imagen FROM productos WHERE id_producto = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$data['id_producto']]);
        $productoActual = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $imagen = $productoActual['imagen'] ?? '';
        
        // Manejar la nueva imagen si se sube
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/productos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                echo json_encode(['success' => false, 'message' => 'Formato de imagen no permitido. Use JPG, PNG o GIF.']);
                exit;
            }
            
            // Eliminar imagen anterior si existe
            if ($imagen && file_exists('../' . $imagen)) {
                unlink('../' . $imagen);
            }
            
            $fileName = uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadPath)) {
                $imagen = 'uploads/productos/' . $fileName;
            }
        }
        
        $query = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, id_categoria = ?, tiempo_preparacion = ?, estado = ?, imagen = ? WHERE id_producto = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['precio'],
            $data['id_categoria'],
            $data['tiempo_preparacion'],
            $data['estado'],
            $imagen,
            $data['id_producto']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar producto: ' . $e->getMessage()]);
    }
    exit;
}

function obtenerProducto($connection, $data) {
    try {
        $id = $data['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de producto no proporcionado']);
            exit;
        }
        
        $query = "SELECT * FROM productos WHERE id_producto = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($producto) {
            echo json_encode(['success' => true, 'data' => $producto]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener producto: ' . $e->getMessage()]);
    }
    exit;
}

function cambiarEstadoProducto($connection, $data) {
    try {
        $id_producto = $data['id_producto'] ?? null;
        $estado = $data['estado'] ?? null;
        
        if (!$id_producto || !$estado) {
            echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
            exit;
        }
        
        // Validar que el estado sea válido
        $estadosValidos = ['disponible', 'no disponible'];
        if (!in_array($estado, $estadosValidos)) {
            echo json_encode(['success' => false, 'message' => 'Estado no válido']);
            exit;
        }
        
        $query = "UPDATE productos SET estado = ? WHERE id_producto = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$estado, $id_producto]);
        
        $estadoTexto = $estado === 'disponible' ? 'disponible' : 'no disponible';
        echo json_encode(['success' => true, 'message' => 'Producto marcado como ' . $estadoTexto]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()]);
    }
    exit;
}

function eliminarProducto($connection, $data) {
    try {
        $id_producto = $data['id_producto'] ?? null;
        if (!$id_producto) {
            echo json_encode(['success' => false, 'message' => 'ID de producto no proporcionado']);
            exit;
        }
        
        // Primero obtener la imagen para eliminarla
        $query = "SELECT imagen FROM productos WHERE id_producto = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Eliminar imagen si existe
        if ($producto && $producto['imagen'] && file_exists('../' . $producto['imagen'])) {
            unlink('../' . $producto['imagen']);
        }
        
        // Eliminar el producto
        $query = "DELETE FROM productos WHERE id_producto = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id_producto]);
        
        echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar producto: ' . $e->getMessage()]);
    }
    exit;
}

function agregarCategoria($connection, $data) {
    try {
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $estado = $data['estado'] ?? 'activo';
        
        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre de la categoría es requerido']);
            exit;
        }
        
        // Verificar si ya existe una categoría con el mismo nombre
        $query = "SELECT id_categoria FROM categorias WHERE nombre = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$nombre]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con ese nombre']);
            exit;
        }
        
        // Insertar la nueva categoría
        $query = "INSERT INTO categorias (nombre, descripcion, estado) VALUES (?, ?, ?)";
        $stmt = $connection->prepare($query);
        
        if ($stmt->execute([$nombre, $descripcion, $estado])) {
            echo json_encode([
                'success' => true, 
                'message' => 'Categoría agregada exitosamente. Recargando página...'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al insertar la categoría']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al agregar categoría: ' . $e->getMessage()]);
    }
    exit;
}

function editarCategoria($connection, $data) {
    try {
        $id_categoria = $data['id_categoria'] ?? null;
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $estado = $data['estado'] ?? 'activo';
        
        if (!$id_categoria || empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
            exit;
        }
        
        // Verificar si ya existe otra categoría con el mismo nombre
        $query = "SELECT id_categoria FROM categorias WHERE nombre = ? AND id_categoria != ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$nombre, $id_categoria]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otra categoría con ese nombre']);
            exit;
        }
        
        // Actualizar la categoría
        $query = "UPDATE categorias SET nombre = ?, descripcion = ?, estado = ? WHERE id_categoria = ?";
        $stmt = $connection->prepare($query);
        
        if ($stmt->execute([$nombre, $descripcion, $estado, $id_categoria])) {
            echo json_encode([
                'success' => true, 
                'message' => 'Categoría actualizada exitosamente. Recargando página...'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la categoría']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar categoría: ' . $e->getMessage()]);
    }
    exit;
}

function obtenerCategoria($connection, $data) {
    try {
        $id = $data['id'] ?? null;
        $nombre = $data['nombre'] ?? null;
        
        if ($id) {
            $query = "SELECT * FROM categorias WHERE id_categoria = ?";
            $stmt = $connection->prepare($query);
            $stmt->execute([$id]);
        } else if ($nombre) {
            $query = "SELECT * FROM categorias WHERE nombre = ?";
            $stmt = $connection->prepare($query);
            $stmt->execute([$nombre]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Parámetros insuficientes']);
            exit;
        }
        
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($categoria) {
            echo json_encode(['success' => true, 'data' => $categoria]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener categoría: ' . $e->getMessage()]);
    }
    exit;
}

function obtenerCategoriaPorNombre($connection, $data) {
    try {
        $nombre = $data['nombre_categoria'] ?? '';
        
        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Nombre de categoría no proporcionado']);
            exit;
        }
        
        $query = "SELECT * FROM categorias WHERE nombre = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$nombre]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($categoria) {
            echo json_encode(['success' => true, 'data' => $categoria]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada: ' . $nombre]);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener categoría: ' . $e->getMessage()]);
    }
    exit;
}

function crearReserva($connection, $data) {
    try {
        // Primero crear o obtener el cliente
        $cliente_id = 1; // Cliente general por defecto

        if (!empty($data['nombre_cliente'])) {
            $query = "INSERT INTO clientes (nombre, telefono) VALUES (?, ?)
                      ON DUPLICATE KEY UPDATE id_cliente=LAST_INSERT_ID(id_cliente), telefono=?";
            $stmt = $connection->prepare($query);
            $stmt->execute([$data['nombre_cliente'], $data['telefono'] ?? '', $data['telefono'] ?? '']);
            $cliente_id = $connection->lastInsertId();
        }

        // Parsear fecha_reservacion de datetime-local
        $fecha_reservacion = date('Y-m-d H:i:s');
        if (!empty($data['fecha_reservacion'])) {
            // Convertir de "2023-12-25T14:30" a "2023-12-25 14:30:00"
            $fecha_reservacion = str_replace('T', ' ', $data['fecha_reservacion']) . ':00';
        }

        $query = "INSERT INTO reservaciones (id_cliente, id_mesa, fecha_reservacion, numero_personas, observaciones) VALUES (?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->execute([
            $cliente_id,
            $data['id_mesa'],
            $fecha_reservacion,
            $data['numero_personas'],
            $data['observaciones'] ?? ''
        ]);

        // Actualizar estado de la mesa
        $query = "UPDATE mesas SET estado = 'reservada' WHERE id_mesa = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$data['id_mesa']]);

        echo json_encode(['success' => true, 'message' => 'Reserva creada exitosamente']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al crear reserva: ' . $e->getMessage()]);
    }
    exit;
}

// FUNCIONES NUEVAS PARA OBTENER Y MOSTRAR DATOS

function obtenerProductos($connection) {
    try {
        $query = "SELECT p.*, c.nombre as nombre_categoria 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                  ORDER BY p.id_producto DESC";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $productos]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener productos: ' . $e->getMessage()]);
    }
    exit;
}

function obtenerCategorias($connection) {
    try {
        $query = "SELECT * FROM categorias WHERE estado = 'activo' ORDER BY nombre";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $categorias]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener categorías: ' . $e->getMessage()]);
    }
    exit;
}

function obtenerProductosPorCategoria($connection, $data) {
    try {
        $id_categoria = $data['id_categoria'] ?? null;

        if (!$id_categoria) {
            echo json_encode(['success' => false, 'message' => 'ID de categoría no proporcionado']);
            exit;
        }

        $query = "SELECT p.*, c.nombre as nombre_categoria
                  FROM productos p
                  LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                  WHERE p.id_categoria = ? AND p.estado = 'disponible'
                  ORDER BY p.nombre";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id_categoria]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $productos]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener productos: ' . $e->getMessage()]);
    }
    exit;
}

function editarOrden($connection, $data) {
    try {
        $id_orden = $data['id_orden'] ?? null;
        if (!$id_orden) {
            echo json_encode(['success' => false, 'message' => 'ID de orden no proporcionado']);
            exit;
        }

        // Iniciar transacción
        $connection->beginTransaction();

        // Manejar cliente
        $id_cliente = null;
        if (!empty($data['cliente_nombre'])) {
            // Buscar cliente existente o crear uno nuevo
            $query_buscar_cliente = "SELECT id_cliente FROM clientes WHERE nombre = ? LIMIT 1";
            $stmt_buscar = $connection->prepare($query_buscar_cliente);
            $stmt_buscar->execute([trim($data['cliente_nombre'])]);
            $cliente_existente = $stmt_buscar->fetch(PDO::FETCH_ASSOC);

            if ($cliente_existente) {
                $id_cliente = $cliente_existente['id_cliente'];
            } else {
                // Crear nuevo cliente
                $query_nuevo_cliente = "INSERT INTO clientes (nombre) VALUES (?)";
                $stmt_nuevo = $connection->prepare($query_nuevo_cliente);
                $stmt_nuevo->execute([trim($data['cliente_nombre'])]);
                $id_cliente = $connection->lastInsertId();
            }
        }

        // Actualizar orden
        $updates = [];
        $params = [];

        if (isset($data['id_empleado'])) {
            $updates[] = "id_empleado = ?";
            $params[] = (int)$data['id_empleado'];
        }

        if (isset($data['observaciones'])) {
            $updates[] = "observaciones = ?";
            $params[] = trim($data['observaciones']);
        }

        $updates[] = "id_cliente = ?";
        $params[] = $id_cliente;

        if (!empty($updates)) {
            $params[] = $id_orden;
            $query = "UPDATE ordenes SET " . implode(', ', $updates) . " WHERE id_orden = ?";
            $stmt = $connection->prepare($query);
            $stmt->execute($params);
        }

        // Agregar nuevos productos si los hay
        if (isset($data['productos_agregar']) && is_array($data['productos_agregar'])) {
            foreach ($data['productos_agregar'] as $id_producto => $cantidad) {
                if ($cantidad > 0) {
                    // Obtener precio del producto
                    $query_producto = "SELECT precio FROM productos WHERE id_producto = ?";
                    $stmt_producto = $connection->prepare($query_producto);
                    $stmt_producto->execute([(int)$id_producto]);
                    $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

                    if ($producto) {
                        $subtotal = $producto['precio'] * (int)$cantidad;

                        $query_detalle = "INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
                        $stmt_detalle = $connection->prepare($query_detalle);
                        $stmt_detalle->execute([$id_orden, (int)$id_producto, (int)$cantidad, $producto['precio'], $subtotal]);

                        // Actualizar total de la orden
                        $query_update = "UPDATE ordenes SET total = total + ? WHERE id_orden = ?";
                        $stmt_update = $connection->prepare($query_update);
                        $stmt_update->execute([$subtotal, $id_orden]);
                    }
                }
            }
        }

        // Confirmar transacción
        $connection->commit();
        echo json_encode(['success' => true, 'message' => 'Orden actualizada exitosamente']);

    } catch (Exception $e) {
        $connection->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al actualizar orden: ' . $e->getMessage()]);
    }
    exit;
}

function cambiarEstadoOrden($connection, $data) {
    try {
        $id_orden = $data['id_orden'] ?? null;
        $nuevo_estado = $data['nuevo_estado'] ?? null;

        if (!$id_orden || !$nuevo_estado) {
            echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
            exit;
        }

        // Validar estado según tu estructura
        $estados_permitidos = ['pendiente', 'confirmada', 'en_preparacion', 'lista', 'entregada', 'pagada', 'cancelada'];
        if (!in_array($nuevo_estado, $estados_permitidos)) {
            echo json_encode(['success' => false, 'message' => 'Estado no válido']);
            exit;
        }

        // Obtener orden actual
        $query_orden = "SELECT * FROM ordenes WHERE id_orden = ?";
        $stmt_orden = $connection->prepare($query_orden);
        $stmt_orden->execute([$id_orden]);
        $orden = $stmt_orden->fetch(PDO::FETCH_ASSOC);

        if (!$orden) {
            echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
            exit;
        }

        // Actualizar estado
        $query = "UPDATE ordenes SET estado = ? WHERE id_orden = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$nuevo_estado, $id_orden]);

        // Si se paga la orden y tiene mesa, liberar la mesa
        if ($nuevo_estado === 'pagada' && $orden['id_mesa']) {
            $query_mesa = "UPDATE mesas SET estado = 'disponible' WHERE id_mesa = ?";
            $stmt_mesa = $connection->prepare($query_mesa);
            $stmt_mesa->execute([$orden['id_mesa']]);
        }

        echo json_encode(['success' => true, 'message' => 'Estado de orden actualizado exitosamente']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()]);
    }
    exit;
}

function eliminarOrden($connection, $data) {
    try {
        $id_orden = $data['id_orden'] ?? null;

        if (!$id_orden) {
            echo json_encode(['success' => false, 'message' => 'ID de orden no proporcionado']);
            exit;
        }

        // Verificar si la orden existe
        $query_check = "SELECT * FROM ordenes WHERE id_orden = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id_orden]);

        if ($stmt_check->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
            exit;
        }

        $orden = $stmt_check->fetch(PDO::FETCH_ASSOC);

        // Solo permitir eliminar órdenes en estados específicos
        $estados_permitidos = ['pendiente', 'cancelada', 'pagada'];
        if (!in_array($orden['estado'], $estados_permitidos)) {
            echo json_encode(['success' => false, 'message' => 'Solo se pueden eliminar órdenes en estado pendiente, cancelada o pagada']);
            exit;
        }

        // Iniciar transacción
        $connection->beginTransaction();

        // Eliminar detalles de la orden
        $query_detalles = "DELETE FROM detalle_orden WHERE id_orden = ?";
        $stmt_detalles = $connection->prepare($query_detalles);
        $stmt_detalles->execute([$id_orden]);

        // Eliminar orden
        $query_orden = "DELETE FROM ordenes WHERE id_orden = ?";
        $stmt_orden = $connection->prepare($query_orden);
        $stmt_orden->execute([$id_orden]);

        // Liberar mesa si era una orden en mesa
        if ($orden['id_mesa'] && $orden['tipo_orden'] === 'mesa') {
            $query_mesa = "UPDATE mesas SET estado = 'disponible' WHERE id_mesa = ?";
            $stmt_mesa = $connection->prepare($query_mesa);
            $stmt_mesa->execute([$orden['id_mesa']]);
        }

        // Confirmar transacción
        $connection->commit();

        echo json_encode(['success' => true, 'message' => 'Orden eliminada exitosamente']);

    } catch (PDOException $e) {
        $connection->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al eliminar orden: ' . $e->getMessage()]);
    }
    exit;
}

function eliminarProductoOrden($connection, $data) {
    try {
        $id_orden = $data['id_orden'] ?? null;
        $id_detalle = $data['id_detalle'] ?? null;

        if (!$id_orden || !$id_detalle) {
            echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
            exit;
        }

        // Verificar que el detalle pertenece a la orden
        $query_check = "SELECT subtotal FROM detalle_orden WHERE id_detalle = ? AND id_orden = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$id_detalle, $id_orden]);
        $detalle = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$detalle) {
            echo json_encode(['success' => false, 'message' => 'Detalle no encontrado']);
            exit;
        }

        // Eliminar detalle
        $query_delete = "DELETE FROM detalle_orden WHERE id_detalle = ?";
        $stmt_delete = $connection->prepare($query_delete);
        $stmt_delete->execute([$id_detalle]);

        // Actualizar total de la orden
        $query_update = "UPDATE ordenes SET total = total - ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_orden = ?";
        $stmt_update = $connection->prepare($query_update);
        $stmt_update->execute([$detalle['subtotal'], $id_orden]);

        echo json_encode(['success' => true, 'message' => 'Producto eliminado de la orden']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar producto: ' . $e->getMessage()]);
    }
    exit;
}

function actualizarPerfil($connection, $data) {
    try {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            error_log("Error: Usuario no autenticado - user_id: " . print_r($_SESSION, true));
            echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
            exit;
        }


        // Verificar empleado actual
        $query_check = "SELECT * FROM empleados WHERE id_empleado = ?";
        $stmt_check = $connection->prepare($query_check);
        $stmt_check->execute([$user_id]);
        $empleado_actual = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$empleado_actual) {
            error_log("Error: Empleado no encontrado - user_id: $user_id");
            echo json_encode(['success' => false, 'message' => 'Empleado no encontrado']);
            exit;
        }

        // Verificar unicidad de usuario si se está cambiando
        if (isset($data['usuario']) && $data['usuario'] !== $empleado_actual['usuario']) {
            $query_check_usuario = "SELECT COUNT(*) as total FROM empleados WHERE usuario = ? AND id_empleado != ?";
            $stmt_check = $connection->prepare($query_check_usuario);
            $stmt_check->execute([$data['usuario'], $user_id]);
            if ($stmt_check->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está en uso']);
                exit;
            }
        }

        // Verificar unicidad de email si se está cambiando
        if (isset($data['email']) && $data['email'] !== $empleado_actual['email']) {
            $query_check_email = "SELECT COUNT(*) as total FROM empleados WHERE email = ? AND id_empleado != ?";
            $stmt_check = $connection->prepare($query_check_email);
            $stmt_check->execute([$data['email'], $user_id]);
            if ($stmt_check->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
                exit;
            }
        }

        // Preparar actualización
        $updates = [];
        $params = [];

        // Campos básicos
        $allowed_fields = ['nombre', 'telefono', 'email', 'puesto', 'usuario'];
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }


        // Manejar cambio de contraseña
        if (!empty($data['contrasena_nueva'])) {
            if (empty($data['contrasena_actual'])) {
                echo json_encode(['success' => false, 'message' => 'Debe proporcionar la contraseña actual']);
                exit;
            }

            // Verificar contraseña actual (comparación directa ya que se almacenan en texto plano)
            if ($data['contrasena_actual'] !== $empleado_actual['contrasena']) {
                echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
                exit;
            }

            $updates[] = "contrasena = ?";
            $params[] = $data['contrasena_nueva']; // Almacenar en texto plano para consistencia
        }

        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'No hay cambios para guardar']);
            exit;
        }

        $params[] = $user_id;
        $query = "UPDATE empleados SET " . implode(', ', $updates) . " WHERE id_empleado = ?";
        error_log("Query: $query");
        error_log("Params: " . print_r($params, true));
        $stmt = $connection->prepare($query);
        $stmt->execute($params);

        // Actualizar sesión
        $_SESSION['user_name'] = $data['nombre'] ?? $empleado_actual['nombre'];

        echo json_encode(['success' => true, 'message' => 'Perfil actualizado exitosamente']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar perfil: ' . $e->getMessage()]);
    }
    exit;
}

function agregarUsuario($connection, $data) {
    try {
        // Verificar permisos de administrador
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        }

        // Validar datos requeridos
        if (empty($data['nombre']) || empty($data['usuario']) || empty($data['email']) || empty($data['contrasena'])) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
            exit;
        }

        // Verificar unicidad de usuario
        $stmt = $connection->prepare("SELECT COUNT(*) as total FROM empleados WHERE usuario = ?");
        $stmt->execute([$data['usuario']]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
            echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está en uso']);
            exit;
        }

        // Verificar unicidad de email
        $stmt = $connection->prepare("SELECT COUNT(*) as total FROM empleados WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
            echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
            exit;
        }

        // Insertar nuevo usuario
        $stmt = $connection->prepare("INSERT INTO empleados (nombre, usuario, email, telefono, puesto, estado, contrasena, fecha_contratacion) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())");
        $stmt->execute([
            $data['nombre'],
            $data['usuario'],
            $data['email'],
            $data['telefono'] ?? '',
            $data['puesto'] ?? 'mesero',
            $data['estado'] ?? 'activo',
            $data['contrasena'] // En producción usar hash
        ]);

        echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al crear usuario: ' . $e->getMessage()]);
    }
    exit;
}

function editarUsuario($connection, $data) {
    try {
        // Verificar permisos de administrador
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        }

        $id_empleado = $data['id_empleado'] ?? null;
        if (!$id_empleado) {
            echo json_encode(['success' => false, 'message' => 'ID de empleado requerido']);
            exit;
        }

        // Verificar que el usuario existe
        $stmt = $connection->prepare("SELECT * FROM empleados WHERE id_empleado = ?");
        $stmt->execute([$id_empleado]);
        $usuario_actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario_actual) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }

        // Preparar actualización
        $updates = [];
        $params = [];

        // Verificar unicidad de usuario si se está cambiando
        if (isset($data['usuario']) && $data['usuario'] !== $usuario_actual['usuario']) {
            $stmt = $connection->prepare("SELECT COUNT(*) as total FROM empleados WHERE usuario = ? AND id_empleado != ?");
            $stmt->execute([$data['usuario'], $id_empleado]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está en uso']);
                exit;
            }
            $updates[] = "usuario = ?";
            $params[] = $data['usuario'];
        }

        // Verificar unicidad de email si se está cambiando
        if (isset($data['email']) && $data['email'] !== $usuario_actual['email']) {
            $stmt = $connection->prepare("SELECT COUNT(*) as total FROM empleados WHERE email = ? AND id_empleado != ?");
            $stmt->execute([$data['email'], $id_empleado]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
                exit;
            }
            $updates[] = "email = ?";
            $params[] = $data['email'];
        }

        // Campos que se pueden actualizar
        $allowed_fields = ['nombre', 'telefono', 'puesto', 'estado'];
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        // Contraseña (opcional)
        if (!empty($data['contrasena'])) {
            $updates[] = "contrasena = ?";
            $params[] = $data['contrasena']; // En producción usar hash
        }

        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'No hay cambios para guardar']);
            exit;
        }

        $params[] = $id_empleado;
        $query = "UPDATE empleados SET " . implode(', ', $updates) . " WHERE id_empleado = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar usuario: ' . $e->getMessage()]);
    }
    exit;
}


?>