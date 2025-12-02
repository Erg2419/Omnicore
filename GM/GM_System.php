<?php
require_once 'database.php';

class GM_System {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // ========== MÉTODOS DE USUARIOS ==========
    public function registrarUsuario($datos) {
        try {
            $query = "INSERT INTO usuarios (nombre, email, password, edad, peso, altura, genero) 
                     VALUES (:nombre, :email, :password, :edad, :peso, :altura, :genero)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':password', password_hash($datos['password'], PASSWORD_DEFAULT));
            $stmt->bindParam(':edad', $datos['edad']);
            $stmt->bindParam(':peso', $datos['peso']);
            $stmt->bindParam(':altura', $datos['altura']);
            $stmt->bindParam(':genero', $datos['genero']);
            
            if ($stmt->execute()) {
                $usuario_id = $this->db->lastInsertId();
                
                // Crear perfil por defecto
                $this->crearPerfilUsuario($usuario_id);
                // Crear configuraciones por defecto
                $this->crearConfiguracionesUsuario($usuario_id);
                
                return $usuario_id;
            }
            return false;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    private function crearPerfilUsuario($usuario_id) {
        $query = "INSERT INTO perfiles_usuario (usuario_id) VALUES (:usuario_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        return $stmt->execute();
    }
    
    private function crearConfiguracionesUsuario($usuario_id) {
        $query = "INSERT INTO configuraciones_usuario (usuario_id) VALUES (:usuario_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        return $stmt->execute();
    }
    
    public function loginUsuario($email, $password) {
        $query = "SELECT * FROM usuarios WHERE email = :email AND estado = 'activo'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $usuario['password'])) {
                return $usuario;
            }
        }
        return false;
    }
    
    public function obtenerUsuario($id) {
        $query = "SELECT u.*, pu.nivel_actividad, pu.tipo_cuerpo, cu.unidad_peso, cu.unidad_altura
                 FROM usuarios u 
                 LEFT JOIN perfiles_usuario pu ON u.id = pu.usuario_id
                 LEFT JOIN configuraciones_usuario cu ON u.id = cu.usuario_id
                 WHERE u.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // ========== MÉTODOS DE ALIMENTOS ==========
    public function obtenerAlimentos($categoria = null, $busqueda = null) {
        $query = "SELECT * FROM alimentos WHERE estado = 'activo'";
        
        $params = [];
        if ($categoria) {
            $query .= " AND categoria = :categoria";
            $params[':categoria'] = $categoria;
        }
        if ($busqueda) {
            $query .= " AND nombre LIKE :busqueda";
            $params[':busqueda'] = "%$busqueda%";
        }
        
        $query .= " ORDER BY nombre";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerAlimento($id) {
        $query = "SELECT * FROM alimentos WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function agregarAlimentoFavorito($usuario_id, $alimento_id) {
        try {
            $query = "INSERT INTO alimentos_favoritos (usuario_id, alimento_id) VALUES (:usuario_id, :alimento_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':alimento_id', $alimento_id);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function obtenerAlimentosFavoritos($usuario_id) {
        $query = "SELECT a.* FROM alimentos a 
                 JOIN alimentos_favoritos af ON a.id = af.alimento_id 
                 WHERE af.usuario_id = :usuario_id AND a.estado = 'activo'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ========== MÉTODOS DE REGISTRO DE COMIDAS ==========
    public function registrarComida($usuario_id, $datos) {
        try {
            $query = "INSERT INTO registro_comidas 
                     (usuario_id, comida, nombre_alimento, descripcion, calorias, proteina, grasa, carbohidrato) 
                     VALUES (:usuario_id, :comida, :nombre_alimento, :descripcion, :calorias, :proteina, :grasa, :carbohidrato)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':comida', $datos['comida']);
            $stmt->bindParam(':nombre_alimento', $datos['nombre_alimento']);
            $stmt->bindParam(':descripcion', $datos['descripcion']);
            $stmt->bindParam(':calorias', $datos['calorias']);
            $stmt->bindParam(':proteina', $datos['proteina']);
            $stmt->bindParam(':grasa', $datos['grasa']);
            $stmt->bindParam(':carbohidrato', $datos['carbohidrato']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function obtenerRegistroComidas($usuario_id, $fecha = null) {
        if (!$fecha) $fecha = date('Y-m-d');
        
        $query = "SELECT * FROM registro_comidas 
                 WHERE usuario_id = :usuario_id 
                 AND DATE(fecha_registro) = :fecha 
                 ORDER BY comida, fecha_registro DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ========== MÉTODOS DE ESTADÍSTICAS ==========
    public function obtenerEstadisticasDiarias($usuario_id, $fecha = null) {
        if (!$fecha) $fecha = date('Y-m-d');
        
        $query = "SELECT 
                 COALESCE(SUM(calorias), 0) as total_calorias,
                 COALESCE(SUM(proteina), 0) as total_proteina,
                 COALESCE(SUM(grasa), 0) as total_grasa,
                 COALESCE(SUM(carbohidrato), 0) as total_carbohidrato
                 FROM registro_comidas 
                 WHERE usuario_id = :usuario_id 
                 AND DATE(fecha_registro) = :fecha";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function obtenerProgresoPeso($usuario_id, $limite = 30) {
        $query = "SELECT * FROM progreso_peso 
                 WHERE usuario_id = :usuario_id 
                 ORDER BY fecha_registro DESC 
                 LIMIT :limite";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function registrarPeso($usuario_id, $peso, $notas = '') {
        try {
            $query = "INSERT INTO progreso_peso (usuario_id, peso, fecha_registro, notas) 
                     VALUES (:usuario_id, :peso, CURDATE(), :notas)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':peso', $peso);
            $stmt->bindParam(':notas', $notas);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // ========== MÉTODOS DE EJERCICIOS ==========
    public function registrarEjercicio($usuario_id, $datos) {
        try {
            $query = "INSERT INTO ejercicios 
                     (usuario_id, tipo_ejercicio, duracion_minutos, calorias_quemadas, intensidad, notas) 
                     VALUES (:usuario_id, :tipo_ejercicio, :duracion_minutos, :calorias_quemadas, :intensidad, :notas)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':tipo_ejercicio', $datos['tipo_ejercicio']);
            $stmt->bindParam(':duracion_minutos', $datos['duracion_minutos']);
            $stmt->bindParam(':calorias_quemadas', $datos['calorias_quemadas']);
            $stmt->bindParam(':intensidad', $datos['intensidad']);
            $stmt->bindParam(':notas', $datos['notas']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function obtenerEjercicios($usuario_id, $fecha = null) {
        if (!$fecha) $fecha = date('Y-m-d');
        
        $query = "SELECT * FROM ejercicios 
                 WHERE usuario_id = :usuario_id 
                 AND DATE(fecha_registro) = :fecha 
                 ORDER BY fecha_registro DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ========== MÉTODOS DE METAS ==========
    public function establecerMeta($usuario_id, $datos) {
        try {
            $query = "INSERT INTO metas 
                     (usuario_id, meta_calorias, meta_peso, tipo_meta, fecha_limite, recordatorio) 
                     VALUES (:usuario_id, :meta_calorias, :meta_peso, :tipo_meta, :fecha_limite, :recordatorio)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':meta_calorias', $datos['meta_calorias']);
            $stmt->bindParam(':meta_peso', $datos['meta_peso']);
            $stmt->bindParam(':tipo_meta', $datos['tipo_meta']);
            $stmt->bindParam(':fecha_limite', $datos['fecha_limite']);
            $stmt->bindParam(':recordatorio', $datos['recordatorio']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function obtenerMetaActual($usuario_id) {
        $query = "SELECT * FROM metas 
                 WHERE usuario_id = :usuario_id 
                 AND fecha_limite >= CURDATE() 
                 ORDER BY fecha_creacion DESC 
                 LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // ========== MÉTODOS DE RECETAS ==========
    public function obtenerRecetas($usuario_id = null, $estado = 'publica') {
        $query = "SELECT r.*, u.nombre as autor FROM recetas r 
                 LEFT JOIN usuarios u ON r.usuario_id = u.id 
                 WHERE r.estado = :estado";
        
        $params = [':estado' => $estado];
        
        if ($usuario_id) {
            $query .= " OR (r.usuario_id = :usuario_id AND r.estado = 'privada')";
            $params[':usuario_id'] = $usuario_id;
        }
        
        $query .= " ORDER BY r.fecha_creacion DESC";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ========== MÉTODOS DE NOTIFICACIONES ==========
    public function obtenerNotificaciones($usuario_id, $no_leidas = false) {
        $query = "SELECT * FROM notificaciones 
                 WHERE usuario_id = :usuario_id";
        
        if ($no_leidas) {
            $query .= " AND leido = FALSE";
        }
        
        $query .= " ORDER BY fecha_creacion DESC LIMIT 10";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>