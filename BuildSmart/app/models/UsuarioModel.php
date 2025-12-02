<?php
// app/models/UsuarioModel.php
require_once __DIR__ . '/../../core/helpers.php';

class UsuarioModel
{
    private $table = 'usuarios';

    public function getAll() {
        global $pdo;
        $stmt = $pdo->query("SELECT id, nombre, email, rol FROM {$this->table} ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function find($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id, nombre, email, rol FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        global $pdo;

        // Evitar correos duplicados
        $check = $pdo->prepare("SELECT id FROM {$this->table} WHERE email = :email");
        $check->execute([':email' => $data['email']]);
        if ($check->fetch()) {
            throw new Exception("⚠️ Ya existe un usuario con ese correo electrónico.");
        }

        $stmt = $pdo->prepare("INSERT INTO {$this->table} (nombre, email, password, rol) 
                               VALUES (:nombre, :email, :password, :rol)");
        $stmt->execute([
            ':nombre'   => $data['nombre'],
            ':email'    => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':rol'      => $data['rol'] ?? 'Usuario'
        ]);

        return $pdo->lastInsertId();
    }

    public function update($id, $data) {
        global $pdo;

        // Validar email duplicado al actualizar
        $check = $pdo->prepare("SELECT id FROM {$this->table} WHERE email = :email AND id != :id");
        $check->execute([':email' => $data['email'], ':id' => $id]);
        if ($check->fetch()) {
            throw new Exception("⚠️ Otro usuario ya tiene ese correo electrónico.");
        }

        if (!empty($data['password'])) {
            $sql = "UPDATE {$this->table} 
                    SET nombre = :nombre, email = :email, password = :password, rol = :rol 
                    WHERE id = :id";
            $params = [
                ':nombre'   => $data['nombre'],
                ':email'    => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':rol'      => $data['rol'] ?? 'Usuario',
                ':id'       => $id
            ];
        } else {
            $sql = "UPDATE {$this->table} 
                    SET nombre = :nombre, email = :email, rol = :rol 
                    WHERE id = :id";
            $params = [
                ':nombre' => $data['nombre'],
                ':email'  => $data['email'],
                ':rol'    => $data['rol'] ?? 'Usuario',
                ':id'     => $id
            ];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function delete($id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
