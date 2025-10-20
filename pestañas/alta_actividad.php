<?php
// Registrar actividad
if (isset($_POST['alta_actividad'])) {
    $actividad = $conexion->real_escape_string($_POST['actividad']);
    $departamento = $conexion->real_escape_string($_POST['departamento']);
    
    $insert_act = "INSERT INTO actividades (actividad, departamento) VALUES (?, ?)";
    $stmt = $conexion->prepare($insert_act);
    $stmt->bind_param("ss", $actividad, $departamento);
    $stmt->execute();
    $stmt->close();

    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $pdf_tmp = $_FILES['pdf_file']['tmp_name'];
        $pdf_name = basename($_FILES['pdf_file']['name']);
        $actividad_dir = "../documentos/$departamento/$actividad";
        if (!is_dir($actividad_dir)) {
            mkdir($actividad_dir, 0777, true);
        }
        $pdf_dest = "$actividad_dir/procedimiento_{$actividad}.pdf";
        move_uploaded_file($pdf_tmp, $pdf_dest);
    }
}

// Eliminar actividad
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Convertir a número para evitar inyección SQL
    
    $delete_act = "DELETE FROM actividades WHERE id = ?";
    $stm = $conexion->prepare($delete_act);
    $stm->bind_param("i", $id);
    $stm->execute();
    $stm->close();

    $select_pdf = $conexion->prepare("SELECT actividad, departamento FROM actividades WHERE id = ?");
    $select_pdf->bind_param("i", $id);
    $select_pdf->execute();
    $select_pdf->bind_result($actividad, $departamento);
    if ($select_pdf->fetch()) {
        $pdf_path = "../documentos/$departamento/$actividad/procedimiento_{$actividad}.pdf";
        if (file_exists($pdf_path)) {
            unlink($pdf_path);
            // Opcional: eliminar el directorio si está vacío
            $dir_path = "../documentos/$departamento/$actividad";
            if (is_dir($dir_path) && count(scandir($dir_path)) == 2) {
                rmdir($dir_path);
            }
        }
    }
    $select_pdf->close();

}


// Obtener actividades con su departamento desde la tabla listas
$sql = "SELECT * FROM actividades";
$resultado = $conexion->query($sql);

// Obtener opciones para el select de departamento
$sql_listas = "SELECT departamento FROM listas WHERE departamento IS NOT NULL";
$resultado_listas = $conexion->query($sql_listas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actividades</title>
    <style>
        .contenedor { display: grid; grid-template-columns: 70% 30%; gap: 20px; }
        h2 { text-align: center; }
        .eliminar { color: red; text-decoration: none; }
    </style>
</head>
<body>
    <div class="contenedor principal">
        <div>
            <h2>Lista de Actividades</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Actividad</th>
                    <th>Departamento</th>
                    <th>Acciones</th>
                </tr>
                <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <tr>
                    <td><?= $fila["id"] ?></td>
                    <td>
                        <a href="../documentos/<?= urlencode($fila["departamento"]) ?>/<?= urlencode($fila["actividad"]) ?>/procedimiento_<?= urlencode($fila["actividad"]) ?>.pdf" target="_blank">
                            <?= htmlspecialchars($fila["actividad"]) ?>
                        </a>
                    </td>
                    <td><?= $fila["departamento"] ?></td>
                    <td>
                        <a class="eliminar" href="?id=<?= $fila["id"] ?>&pestaña=alta_actividad" onclick="return confirm('¿Seguro que deseas eliminar?');">Eliminar</a>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
        <div>
            <h2>Registrar Nueva Actividad</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="text" name="actividad" placeholder="Nombre de la actividad" required>
                <select name="departamento">
                    <?php while ($departamento = $resultado_listas->fetch_assoc()) { ?>
                        <option value="<?= $departamento['departamento'] ?>"><?= $departamento['departamento'] ?></option>
                    <?php } ?>
                </select>
                <input type="file" name="pdf_file" accept="application/pdf">
                <button type="submit" name="alta_actividad">Agregar</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php $conexion->close(); ?>
