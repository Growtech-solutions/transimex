<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Infonavit</title>
    <style>
        .formulario {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
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
    <h2>Registrar Infonavit</h2>
    <form method="POST">
        <label for="trabajador">Seleccionar Trabajador:</label>
        <select id="trabajador" name="trabajador" required>
            <?php
            $sql = "SELECT id, nombre , apellidos FROM trabajadores WHERE Estado=1 ORDER BY nombre";
            $resultado = $conexion->query($sql);
            if ($resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<option value='" . $fila["id"] . "'>" . $fila["nombre"] . " " .$fila["apellidos"] . "</option>";
                }
            } else {
                echo "<option value=''>No hay trabajadores.</option>";
            }
            ?>
        </select>
        
        <label for="credito">Numero de credito:</label>
        <input type="number" id="credito" name="credito" required>

        <label for="tipo">Tipo de Infonavit:</label>
        <select id="tipo" name="tipo" required>
            <option value="mensual">Monto mensual</option>
            <option value="porcentaje">Factor</option>
        </select>

        <label for="monto">Monto:</label>
        <input type="number" id="monto" name="monto" step="0.01" required>

        <label for="fecha_inicial">Fecha Inicial:</label>
        <input type="date" id="fecha_inicial" name="fecha_inicial" required>

        <button type="submit">Registrar Infonavit</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $trabajador = $_POST['trabajador'];
        $credito = $_POST['credito'];
        $tipo = $_POST['tipo'];
        $monto = $_POST['monto'];
        $fecha_inicial = $_POST['fecha_inicial'];

        $sql = "INSERT INTO infonavit (id_trabajador, num_credito, tipo, monto, semanal, fecha_inicial) VALUES (?,?, ?, ?, NULL, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iisds", $trabajador,$credito, $tipo, $monto, $fecha_inicial);

        if ($stmt->execute()) {
            echo "<p>Infonavit registrado exitosamente.</p>";
        } else {
            echo "<p>Error al registrar el infonavit: " . $stmt->error . "</p>";
        }
    }
    ?>
</div>
</body>
</html>
