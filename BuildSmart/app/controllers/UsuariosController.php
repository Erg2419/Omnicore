<?php
// app/controllers/UsuariosController.php

require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../../core/helpers.php';

class UsuariosController
{
    private $model;

    public function __construct() {
        $this->model = new UsuarioModel();
    }

    // 🔍 Buscar usuarios
    public function buscar($q = '') {
        require_login();
        global $pdo;

        if (!empty($q)) {
            $stmt = $pdo->prepare("SELECT * FROM usuarios 
                                   WHERE nombre LIKE :q OR email LIKE :q OR rol LIKE :q
                                   ORDER BY id DESC");
            $stmt->execute([':q' => "%$q%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC");
        }

        return $stmt->fetchAll();
    }

    // 🧾 Mostrar formulario (crear o editar)
    public function form() {
        require_login();
        $id = $_GET['id'] ?? null;
        $usuario = $id ? $this->model->find($id) : null;
        render('usuarios/form.php', ['usuario' => $usuario, 'csrf' => csrf_token()], true);
    }

    // 💾 Guardar o actualizar usuario
    public function guardar() {
        require_login();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirectTo('usuarios');

        if (!validate_csrf($_POST['csrf'] ?? '')) {
            die("⚠️ Petición no válida (CSRF).");
        }

        // ✅ Obtener el id correctamente desde POST
        $id = $_POST['id'] ?? null;

        $data = [
            'nombre'   => $_POST['nombre'],
            'email'    => $_POST['email'],
            'password' => $_POST['password'] ?? '',
            'rol'      => $_POST['rol'] ?? 'Usuario'
        ];

        try {
            if ($id) {
                $this->model->update($id, $data);
                flash("✅ Usuario actualizado correctamente");
            } else {
                $this->model->create($data);
                flash("✅ Usuario creado correctamente");
            }
        } catch (PDOException $e) {
            // Captura duplicados o errores SQL
            if ($e->getCode() == 23000) {
                flash("⚠️ El email ya está registrado. Usa otro diferente.", "error");
            } else {
                flash("⚠️ Error al guardar: " . $e->getMessage(), "error");
            }
        }

        redirectTo('usuarios');
    }

    // 🗑️ Eliminar usuario
    public function eliminar() {
        require_login();
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->model->delete($id);
            flash("🗑️ Usuario eliminado correctamente");
        }
        redirectTo('usuarios');
    }

    // 📊 Exportar reporte (Excel)
    public function reporte() {
        require_login();
        global $pdo;

        $q = $_GET['q'] ?? '';

        if (!empty($q)) {
            $stmt = $pdo->prepare("SELECT * FROM usuarios 
                                   WHERE nombre LIKE :q OR email LIKE :q OR rol LIKE :q
                                   ORDER BY id DESC");
            $stmt->execute([':q' => "%$q%"]);
            $usuarios = $stmt->fetchAll();
        } else {
            $usuarios = $this->model->getAll();
        }

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=ReporteUsuarios_" . date('Ymd_His') . ".xls");

        echo "<table border='1' style='border-collapse:collapse'>";
        echo "<tr style='background:#f97316;color:white;'>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
              </tr>";

        foreach ($usuarios as $u) {
            echo "<tr>
                    <td>{$u['id']}</td>
                    <td>{$u['nombre']}</td>
                    <td>{$u['email']}</td>
                    <td>{$u['rol']}</td>
                  </tr>";
        }
        echo "</table>";
        exit;
    }
}
