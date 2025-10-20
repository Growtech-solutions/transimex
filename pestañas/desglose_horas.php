<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Historial de Horas</title>
    <style>
        .centrado { text-align: center; }
        h1{
            text-align:center;
        }
        form{
            text-align:center
        }
        table { border-collapse: collapse; width: 80%; margin: auto; }
        th, td { padding: 8px; text-align: left; }
        tbody tr:nth-child(odd) { background-color: #f7f7f7; }
         .principal {
           margin-top:1.5%;
           margin-bottom:3%;
        }
    </style>
</head>
<body>
    <div class="principal">
        <section>
            <h1>Historial de Horas</h1>

            <!-- Formulario para seleccionar la OT -->
            <form method="GET" action="">
                <label for="ot">Seleccione la OT:</label>
                <input type="text" id="ot" name="ot" value="<?php echo isset($_GET['ot']) ? htmlspecialchars($_GET['ot']) : ''; ?>">
                <input type="hidden" name="pestaña" value="desglose_horas">
                <button type="submit">Buscar</button>
            </form>
            <br>
            <?php

            // Verifica si se ha seleccionado una OT
            if (isset($_GET['ot']) && !empty($_GET['ot'])) {
                // Conexión a la base de datos (asegúrate de tener $conexion definido antes)
                $ot = $_GET['ot'];

                // Consulta principal usando parámetros preparados
                $sql_horas = "
                SELECT e.id_trabajador, e.tiempo, e.fecha, e.pieza_tardia, p.pieza AS nombre_pieza, t.nombre, t.apellidos
                FROM encargado e
                LEFT JOIN piezas p ON e.id_pieza = p.id
                LEFT JOIN trabajadores t ON e.id_trabajador = t.id
                WHERE e.ot_tardia = ? OR p.ot = ?
                ";

                $stmt = $conexion->prepare($sql_horas);
                $stmt->bind_param("ss", $ot, $ot); // 'ot' como string por si acaso
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<table border='1'>
                            <tr>
                                <th>Nombre</th>
                                <th>Tiempo</th>
                                <th>Fecha</th>
                                <th>Pieza Tardia</th>
                                <th>Nombre Pieza</th>
                            </tr>";

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['nombre']) . " " . htmlspecialchars($row['apellidos']) . "</td>
                                <td>" . htmlspecialchars($row['tiempo']) . "</td>
                                <td>" . htmlspecialchars($row['fecha']) . "</td>
                                <td>" . htmlspecialchars($row['pieza_tardia']) . "</td>
                                <td>" . htmlspecialchars($row['nombre_pieza']) . "</td>
                            </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "No se encontraron registros para la OT seleccionada.";
                }
                $stmt->close();
            } else {
                echo "<p>Por favor, seleccione una OT para ver el historial de horas.</p>";
            }
            $conexion->close();
            ?>
        </section>
    </div>
</body>
</html>


