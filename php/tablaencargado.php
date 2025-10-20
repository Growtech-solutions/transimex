<?php

echo 
"<style>
    .agregarEncargado {
        margin-left: .5rem;
        text-align: center;
        cursor: pointer;
    }
    .encargado_nombre{
        width: 80px;
    }
    .encargado_cantidad{
        width: 50px;
    }
    .encargado_tiempo{
        width: 50px;
    }
    .guardar{
        margin-left:70%;
        margin-top: .8rem;
    }
    .centrado {
        text-align: center;
    }
    .eliminarEncargado {
        margin-left: .5rem;
        text-align: center;
        cursor: pointer;
        color: red;
    }
</style>";


// Consulta SQL
$id_pieza = $fila["id"];
$sql = "SELECT e.*, t.nombre, t.apellidos
FROM encargado e
left JOIN trabajadores t ON e.id_trabajador = t.id
WHERE e.id_pieza = $id_pieza AND e.fecha = CURDATE();
";

// Ejecutar la consulta
$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    // Mostrar los datos en una tabla
    echo "<table>";
        echo "<form action='' method='post'>";
            echo "<tr>
                <th>Nombre";
                echo "<input class='agregarEncargado' type='submit' name='agregarEncargado' value='+'>";
                echo "<input type='hidden' name='id_pieza' value='" . $fila["id"] . "'>"; 
        echo "</form>".
            "   </th>
                <th>Tiempo</th>
                <th>Cantidad</th>
                <th>Eliminar</th>
            </tr>";
        while($row = $result->fetch_assoc()) {
            echo "<form action='' method='post'>";
            echo "<tr>";
                if (($row["id_trabajador"]) == null) {
                    echo "<td class='encargado_nombre'>";
                        $SelectTrabajadores->obtenerNombres('id_trabajador[]','');  
                    echo "</td>";
                } else {
                    echo "<td>".$row["apellidos"]." ".$row["nombre"]."</td>";
                }

                if (($row["tiempo"]) == null) {
                    echo "<td>";
                    echo "<input class='encargado_tiempo' type='text' name='encargado_tiempo[]' value='" . ($row["tiempo"] ?? '') . "'>";
                    echo "</td>";
                } else {
                    echo "<td class='centrado'>".$row["tiempo"]."</td>";
                }
                if (($row["cantidad"]) == null) {
                    echo "<td>";
                    echo "<input class='encargado_cantidad' type='number' name='encargado_cantidad[]' value='" . ($row["cantidad"] ?? '') . "'>";
                    echo "</td>";
                } else {
                    echo "<td class='centrado'>".$row["cantidad"]."</td>";
                }

                echo "<td>";
                echo "<input class='eliminarEncargado' type='submit' name='eliminarEncargado' value='Eliminar'>";
                echo "<input type='hidden' name='id_encargado' value='" . $row["id"] . "'>"; 
                echo "</td>";
                
            echo "</tr>";
            echo "<input type='hidden' name='id_encargado[]' value='" . $row["id"] . "'>";
        }
    echo "</table>";
    echo "<form action='' method='post'>";
        echo "<input class='guardar' type='submit' name='guardar' value='Guardar'>";
        echo "<input type='hidden' name='id_pieza' value='" . $fila["id"] . "'>"; 
    echo "</form>";  
} else {
    echo "<form action='' method='post'>";
    echo "Agregar encargado";
        echo "<input class='agregarEncargado' type='submit' name='agregarEncargado' value='+'>";
        echo "<input type='hidden' name='id_pieza' value='" . $fila["id"] . "'>"; 
    echo "</form>";
}

?>