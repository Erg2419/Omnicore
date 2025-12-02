<?php
require_login();
global $pdo;

$id = $_GET['id'] ?? null;
$editing = false;
$proyectos = $pdo->query("SELECT id, nombre FROM proyectos ORDER BY nombre ASC")->fetchAll();

$pagosData = ['proyecto_id'=>'', 'descripcion'=>'', 'monto'=>'', 'tipo'=>'entrada', 'metodo_pago'=>'efectivo', 'fecha'=>date('Y-m-d')];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM pagos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch();
    if ($data) {
        $pagosData = $data;
        $editing = true;
    }
}
?>

<div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-md space-y-6 animate-fadeIn">
  <h1 class="text-2xl font-bold text-[#f97316] flex items-center gap-2">
    <?= $editing ? '✏️ Editar Pago' : '💳 Registrar Nuevo Pago' ?>
  </h1>

  <form action="<?= BASE_URL ?>/index.php?page=pagos/guardar<?= $editing ? '&id='.$id : '' ?>" method="POST" class="space-y-4">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div>
      <label>Proyecto</label>
      <select name="proyecto_id" required>
        <option value="">Seleccione un proyecto</option>
        <?php foreach ($proyectos as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $pagosData['proyecto_id']==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label>Descripción</label>
      <input type="text" name="descripcion" value="<?= htmlspecialchars($pagosData['descripcion']) ?>" required>
    </div>

    <div>
      <label>Monto</label>
      <input type="number" step="0.01" name="monto" value="<?= $pagosData['monto'] ?>" required>
    </div>

    <div>
      <label>Tipo</label>
      <select name="tipo">
        <option value="entrada" <?= $pagosData['tipo']=='entrada'?'selected':'' ?>>Entrada</option>
        <option value="salida" <?= $pagosData['tipo']=='salida'?'selected':'' ?>>Salida</option>
      </select>
    </div>

    <div>
      <label>Método</label>
      <select name="metodo_pago">
        <option value="efectivo" <?= $pagosData['metodo_pago']=='efectivo'?'selected':'' ?>>Efectivo</option>
        <option value="transferencia" <?= $pagosData['metodo_pago']=='transferencia'?'selected':'' ?>>Transferencia</option>
        <option value="cheque" <?= $pagosData['metodo_pago']=='cheque'?'selected':'' ?>>Cheque</option>
      </select>
    </div>

    <div>
      <label>Fecha</label>
      <input type="date" name="fecha" value="<?= $pagosData['fecha'] ?>">
    </div>

    <div class="flex justify-end gap-3">
      <a href="<?= BASE_URL ?>/index.php?page=pagos/listar" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</a>
      <button type="submit" class="px-4 py-2 bg-[#f97316] text-white rounded-md">
        <?= $editing ? '💾 Actualizar Pago' : '💾 Guardar Pago' ?>
      </button>
    </div>
  </form>
</div>
