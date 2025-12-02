<?php
header('Content-Type: application/json');
require_once '../includes/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = new Database();
$connection = $db->getConnection();

// Verificar si el usuario es administrador
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit();
}

$request = $_SERVER['REQUEST_URI'];
$id = null;

// Extraer ID de la URL si existe
if (preg_match('/\/usuarios\.php\/(\d+)$/', $request, $matches)) {
    $id = $matches[1];
}

switch ($method) {
    case 'GET':
        if ($id) {
            // Obtener usuario específico
            try {
                $stmt = $connection->prepare("SELECT id_empleado, nombre, usuario, email, telefono, puesto, estado FROM empleados WHERE id_empleado = ?");
                $stmt->execute([$id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    echo json_encode(['success' => true, 'data' => $usuario]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
                }
            } catch(PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
            }
        } else {
            // Obtener todos los usuarios
            try {
                $stmt = $connection->prepare("SELECT id_empleado, nombre, usuario, email, telefono, puesto, estado FROM empleados ORDER BY nombre");
                $stmt->execute();
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $usuarios]);
            } catch(PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
            }
        }
        break;

    case 'POST':
        // Crear o actualizar usuario
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }

        try {
            if (isset($data['id_empleado']) && !empty($data['id_empleado'])) {
                // Actualizar usuario existente
                $updates = [];
                $params = [];

                if (isset($data['nombre'])) {
                    $updates[] = "nombre = ?";
                    $params[] = $data['nombre'];
                }
                if (isset($data['usuario'])) {
                    $updates[] = "usuario = ?";
                    $params[] = $data['usuario'];
                }
                if (isset($data['email'])) {
                    $updates[] = "email = ?";
                    $params[] = $data['email'];
                }
                if (isset($data['telefono'])) {
                    $updates[] = "telefono = ?";
                    $params[] = $data['telefono'];
                }
                if (isset($data['puesto'])) {
                    $updates[] = "puesto = ?";
                    $params[] = $data['puesto'];
                }
                if (isset($data['estado'])) {
                    $updates[] = "estado = ?";
                    $params[] = $data['estado'];
                }
                if (!empty($data['contrasena'])) {
                    $updates[] = "contrasena = ?";
                    $params[] = $data['contrasena']; // En producción usar hash
                }

                if (!empty($updates)) {
                    $params[] = $data['id_empleado'];
                    $query = "UPDATE empleados SET " . implode(', ', $updates) . " WHERE id_empleado = ?";
                    $stmt = $connection->prepare($query);
                    $stmt->execute($params);
                }

                echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
            } else {
                // Crear nuevo usuario
                $stmt = $connection->prepare("INSERT INTO empleados (nombre, usuario, email, telefono, puesto, estado, contrasena, fecha_contratacion) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())");
                $stmt->execute([
                    $data['nombre'],
                    $data['usuario'],
                    $data['email'],
                    $data['telefono'] ?? '',
                    $data['puesto'],
                    $data['estado'],
                    $data['contrasena'] // En producción usar hash
                ]);

                echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente', 'id' => $connection->lastInsertId()]);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al guardar usuario: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de usuario requerido']);
            exit();
        }

        try {
            // Verificar que no sea el propio usuario
            if ($id == $_SESSION['user_id']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No puedes eliminar tu propio usuario']);
                exit();
            }

            $stmt = $connection->prepare("DELETE FROM empleados WHERE id_empleado = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al eliminar usuario']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        break;
}
?>