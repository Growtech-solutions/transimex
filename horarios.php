<?php
// conexión a la base de datos
$conexion = new mysqli("localhost", "usuario", "contraseña", "basedatos");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// obtener turnos y horarios
$query = "SELECT t.id_turno, t.nombre_turno, t.descripcion, th.dia_semana, th.hora_entrada, th.hora_salida
          FROM turnos t
          LEFT JOIN turno_horarios th ON t.id_turno = th.id_turno
          ORDER BY t.id_turno, FIELD(th.dia_semana,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')";
$result = $conexion->query($query);

$turnos = [];
while ($row = $result->fetch_assoc()) {
    $turnos[$row['id_turno']]['nombre'] = $row['nombre_turno'];
    $turnos[$row['id_turno']]['descripcion'] = $row['descripcion'];
    $turnos[$row['id_turno']]['horarios'][] = [
        'dia' => $row['dia_semana'],
        'entrada' => $row['hora_entrada'],
        'salida' => $row['hora_salida']
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Turnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">📅 Lista de Turnos</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            ➕ Agregar Turno
        </button>
    </div>

    <?php foreach ($turnos as $id => $turno): ?>
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h4 class="card-title"><?= htmlspecialchars($turno['nombre']) ?></h4>
                <p class="text-muted"><?= htmlspecialchars($turno['descripcion']) ?></p>
                <table class="table table-sm table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Día</th>
                            <th>Hora Entrada</th>
                            <th>Hora Salida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($turno['horarios'] as $horario): ?>
                            <tr>
                                <td><?= htmlspecialchars($horario['dia']) ?></td>
                                <td><?= htmlspecialchars($horario['entrada']) ?></td>
                                <td><?= htmlspecialchars($horario['salida']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Agregar Turno -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="guardar_turno.php">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Nuevo Turno</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Nombre del Turno</label>
                <input type="text" name="nombre_turno" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control"></textarea>
            </div>
            <hr>
            <h6>Horarios por Día</h6>
            <?php
            $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
            foreach ($dias as $dia): ?>
                <div class="row mb-2">
                    <div class="col-md-3"><strong><?= $dia ?></strong></div>
                    <div class="col-md-4">
                        <input type="time" name="entrada[<?= $dia ?>]" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <input type="time" name="salida[<?= $dia ?>]" class="form-control">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar Turno</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
