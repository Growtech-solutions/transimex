<!DOCTYPE html>
<html lang="en">
<head>
    <title>Registro de Préstamos</title>
    <style>
        .formulario {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .formulario label {
            display: block;
            margin-bottom: 10px;
        }
        .formulario input, .formulario select {
            width: 90%;
            padding: 10px;
            margin-bottom: 10px;
        }
        .formulario button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
        }
        .formulario button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="formulario">
    <h2>Registrar Préstamo</h2>
    <form method="POST">
        <label for="trabajador">Seleccionar Trabajador:</label>
        <select id="trabajador" name="trabajador" required>
            <?php
            $sql = "SELECT id, nombre, apellidos FROM trabajadores WHERE Estado='Activo' ORDER BY nombre";
            $resultado = $conexion->query($sql);
            if ($resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<option value='" . $fila["id"] . "'>" . $fila["nombre"] ." ". $fila["apellidos"] . "</option>";
                }
            } else {
                echo "<option value=''>No hay trabajadores.</option>";
            }
            ?>
        </select>

        <label for="prestamo">Monto del Préstamo:</label>
        <input type="number" id="prestamo" name="prestamo" min="0" step="0.01" required>

        <label for="semanas">Número de Semanas:</label>
        <input type="number" id="semanas" name="semanas" min="1" required>

        <input type="hidden" name="header_loc" value="<?php echo $header_loc; ?>">
        <input type="hidden" name="pestaña" value="alta_prestamo">

        <button type="submit">Registrar Préstamo</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $trabajador = $_POST['trabajador'];
        $prestamo = $_POST['prestamo'];
        $semanas = $_POST['semanas'];

        $sql = "INSERT INTO prestamos (id_trabajador, prestamo, fecha, semanas) VALUES (?, ?, CURDATE(), ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("idi", $trabajador, $prestamo, $semanas);

        if ($stmt->execute()) {
            echo "<p>Préstamo registrado exitosamente.</p>";
        } else {
            echo "<p>Error al registrar el préstamo: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $conexion->close();
    }
    ?>
</div>
</body>
</html>
