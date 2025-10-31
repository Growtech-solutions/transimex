<?php

// Si se actualiza la resolución o fecha
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $id = $_POST["id"];
    $fecha_contacto = $_POST["fecha_contacto"] ?: null;
    $resolucion = $_POST["resolucion"];

    $sql = "UPDATE contactos SET fecha_contacto = ?, resolucion = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssi", $fecha_contacto, $resolucion, $id);
    $stmt->execute();
}

// Consultar pendientes
$sql = "
SELECT * FROM contactos
WHERE resolucion = 'pendiente'
ORDER BY 
    CASE WHEN fecha_contacto IS NULL THEN 0 ELSE 1 END,
    fecha_contacto ASC
";
$result = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Contactos Pendientes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f7f9fb; }
.card { border-radius: 15px; }
.table th { background-color: #007bff; color: white; }
.btn-update { background-color: #28a745; color: white; }
</style>
</head>
<body>
<div class="container mt-5">
  <h3 class="text-center mb-4">📞 Contactos Pendientes</h3>
  <div class="card shadow">
    <div class="card-body">
      <table class="table table-hover align-middle text-center">
        <thead>
          <tr>
            <th>ID</th>
            <th>Solicitante</th>
            <th>Contacto</th>
            <th>Fecha de Contacto</th>
            <th>Resolución</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <form method="POST">
              <td><?= $row["id"] ?></td>
              <td><?= htmlspecialchars($row["solicitante"]) ?></td>
              <td><?= htmlspecialchars($row["contacto"]) ?></td>
              <td>
                <input type="date" name="fecha_contacto" value="<?= $row["fecha_contacto"] ?>" class="form-control">
              </td>
              <td>
                <select name="resolucion" class="form-select">
                  <option value="pendiente" <?= $row["resolucion"]=="pendiente"?"selected":"" ?>>Pendiente</option>
                  <option value="contratado" <?= $row["resolucion"]=="contratado"?"selected":"" ?>>Contratado</option>
                  <option value="rechazado" <?= $row["resolucion"]=="rechazado"?"selected":"" ?>>Rechazado</option>
                </select>
              </td>
              <td>
                <input type="hidden" name="id" value="<?= $row["id"] ?>">
                <button type="submit" class="btn btn-update btn-sm">Actualizar</button>
              </td>
            </form>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
