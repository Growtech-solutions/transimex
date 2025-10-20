<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Herramienta</title>
    <script>
        function confirmarActualizacion(cantidad) {
            return confirm("Se actualizarán " + cantidad + " registros a 'Almacén'. ¿Desea continuar?");
        }
    </script>
</head>
<body id="almacen">
<?php

    function construirFiltros($params, $conexion) {
        $filtros = " WHERE 1=1";
        foreach (['folio', 'herramienta', 'area', 'estado', 'trabajador'] as $campo) {
            if (!empty($params[$campo])) {
                $valor = $conexion->real_escape_string($params[$campo]);
                $filtros .= " AND $campo LIKE '%$valor%'";
            }
        }
        if (!empty($params['fecha_alta'])) {
            $filtros .= " AND fecha_alta >= '" . $conexion->real_escape_string($params['fecha_alta']) . "'";
        }
        if (!empty($params['fecha_alta_fin'])) {
            $filtros .= " AND fecha_alta <= '" . $conexion->real_escape_string($params['fecha_alta_fin']) . "'";
        }
        return $filtros;
    }

    $filtros = construirFiltros($_GET, $conexion);

    $queryCount = "SELECT COUNT(*) as total FROM almacen_herramienta $filtros";
    $resultadoCount = $conexion->query($queryCount);
    $totalRegistros = $resultadoCount->fetch_assoc()['total'];

    if (isset($_POST['actualizar_trabajador'])) {
        $filtrosPost = construirFiltros($_POST, $conexion);
        $updateQuery = "UPDATE almacen_herramienta SET trabajador = 'Almacen' $filtrosPost";
        $conexion->query($updateQuery);
    }
?>

<div class="principal">
    <section>
        <h1>Almacén</h1>
        <div class="buscador">
            <form class="reporte_formulario" method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <label for="folio">Folio:</label>
                <input class="formulario_reporte_ot" type="text" id="folio" name="folio" value="<?php echo htmlspecialchars($_GET['folio'] ?? '', ENT_QUOTES); ?>" placeholder="Buscar por Folio">

                <label for="herramienta">Herramienta:</label>
                <input class="formulario_reporte_ot" type="text" id="herramienta" name="herramienta" value="<?php echo htmlspecialchars($_GET['herramienta'] ?? '', ENT_QUOTES); ?>" placeholder="Herramienta">

                <label for="area">Área:</label>
                <input class="formulario_reporte_ot" type="text" id="area" name="area" value="<?php echo htmlspecialchars($_GET['area'] ?? '', ENT_QUOTES); ?>" placeholder="Área">

                <label for="estado">Estado:</label>
                <input class="formulario_reporte_ot" type="text" id="estado" name="estado" value="<?php echo htmlspecialchars($_GET['estado'] ?? '', ENT_QUOTES); ?>" placeholder="Estado">

                <br><br>
                <label for="trabajador">Selecciona un trabajador:</label>
                <select name="trabajador" id="trabajador">
                    <option value=''>Seleccione.</option>
                    <?php
                        $sql = "SELECT DISTINCT trabajador FROM almacen_herramienta ORDER BY trabajador";
                        $resultado = $conexion->query($sql);
                        while ($fila = $resultado->fetch_assoc()) {
                            $valor = htmlspecialchars($fila["trabajador"]);
                            $selected = (isset($_GET['trabajador']) && $_GET['trabajador'] == $valor) ? 'selected' : '';
                            echo "<option value='$valor' $selected>$valor</option>";
                        }
                    ?>
                </select>

                <label for="fecha_alta">Fecha Alta:</label>
                <input class="formulario_reporte_ot" type="date" id="fecha_alta" name="fecha_alta" value="<?php echo htmlspecialchars($_GET['fecha_alta'] ?? '', ENT_QUOTES); ?>">

                <label style="text-align:center" for="fecha_alta_fin">y :</label>
                <input class="formulario_reporte_ot" type="date" id="fecha_alta_fin" name="fecha_alta_fin" value="<?php echo htmlspecialchars($_GET['fecha_alta_fin'] ?? '', ENT_QUOTES); ?>">

                <input type="hidden" name="pestaña" value="herramienta">
                <input type="submit" value="Buscar">
            </form>
        </div>

        <form method="POST" action="" onsubmit="return confirmarActualizacion(<?php echo $totalRegistros; ?>)">
            <?php
                foreach ($_GET as $clave => $valor) {
                    echo "<input type='hidden' name='" . htmlspecialchars($clave) . "' value='" . htmlspecialchars($valor, ENT_QUOTES) . "'>";
                }
            ?>
            <input type="submit" name="actualizar_trabajador" value="Actualizar trabajador a 'Almacen'">
        </form>

        <?php
            $registros_por_pagina = isset($_GET['registros_por_pagina']) ? (int)$_GET['registros_por_pagina'] : 10;
            $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $offset = ($pagina_actual - 1) * $registros_por_pagina;
            
            $query = "SELECT * FROM almacen_herramienta $filtros ORDER BY folio DESC LIMIT $offset, $registros_por_pagina";
            $resultado = $conexion->query($query);
        ?>

        <table>
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Herramienta</th>
                    <th>Área</th>
                    <th>Estado</th>
                    <th>Fecha Alta</th>
                    <th>Trabajador</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($resultado->num_rows > 0) {
                    while ($row = $resultado->fetch_assoc()) {
                        echo "<tr>
                            <td><a href='../header_main_aside/$header_loc.php?pestaña=editar_herramienta&header_loc=$header_loc&id=" . htmlspecialchars($row['folio']) . "'>" . htmlspecialchars($row['folio']) . "</a></td>
                            <td>" . htmlspecialchars($row['herramienta']) . "</td>
                            <td>" . htmlspecialchars($row['area']) . "</td>
                            <td>" . htmlspecialchars($row['estado']) . "</td>
                            <td>" . htmlspecialchars($row['alta']) . "</td>
                            <td>" . htmlspecialchars($row['trabajador']) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No se encontraron registros.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="paginacion">
            <?php
            // Construir la cadena de filtros para mantenerlos en la paginación
            $filtros_url = '';
            foreach ($_GET as $clave => $valor) {
                if ($clave !== 'pagina' && $clave !== 'registros_por_pagina') {
                    $filtros_url .= '&' . urlencode($clave) . '=' . urlencode($valor);
                }
            }

            $total_paginas = ceil($totalRegistros / $registros_por_pagina);
            $max_paginas_mostradas = 5;
            $inicio = max(1, $pagina_actual - floor($max_paginas_mostradas / 2));
            $fin = min($total_paginas, $inicio + $max_paginas_mostradas - 1);

            if ($fin - $inicio + 1 < $max_paginas_mostradas) {
                $inicio = max(1, $fin - $max_paginas_mostradas + 1);
            }

            if ($inicio > 1) {
                echo "<a href='?pagina=1&registros_por_pagina=$registros_por_pagina$pestaña=herramienta$filtros_url'>&laquo; Primero</a> ";
            }

            for ($i = $inicio; $i <= $fin; $i++) {
                if ($i == $pagina_actual) {
                    echo "<span>$i</span> ";
                } else {
                    echo "<a href='?pagina=$i&registros_por_pagina=$registros_por_pagina$pestaña=herramienta$filtros_url'>$i</a> ";
                }
            }

            if ($fin < $total_paginas) {
                echo "<a href='?pagina=$total_paginas&registros_por_pagina=$registros_por_pagina$pestaña=herramienta$filtros_url'>Último &raquo;</a>";
            }
            ?>
        </div>

    </section>
</div>
</body>
</html>
