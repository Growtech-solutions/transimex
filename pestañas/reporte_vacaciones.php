<?php

$id_trabajador = isset($_GET['id_trabajador']) ? (int)$_GET['id_trabajador'] : 0;
$fecha_inicio = isset($_GET['fecha_inicio']) ? $conexion->real_escape_string($_GET['fecha_inicio']) : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $conexion->real_escape_string($_GET['fecha_fin']) : '';
$registros_por_pagina = isset($_GET['registros_por_pagina']) ? (int)$_GET['registros_por_pagina'] : 20;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener lista de trabajadores para el filtro
$trabajadores = [];
$result_trabajadores = $conexion->query("SELECT id, CONCAT(nombre, ' ', apellidos) AS nombre_completo FROM trabajadores ORDER BY nombre");
while ($row_trabajador = $result_trabajadores->fetch_assoc()) {
    $trabajadores[] = $row_trabajador;
}

// Contar resultados totales
$sql_total = "SELECT COUNT(*) as total FROM encargado 
    LEFT JOIN piezas ON piezas.id = encargado.id_pieza
    WHERE encargado.fecha >= ? AND encargado.fecha <= ? AND (piezas.ot = 703 OR piezas.ot = 705)";
if ($id_trabajador > 0) {
    $sql_total .= " AND encargado.id_trabajador = ?";
}
$stmt_total = $conexion->prepare($sql_total);
if ($id_trabajador > 0) {
    $stmt_total->bind_param("ssi", $fecha_inicio, $fecha_fin, $id_trabajador);
} else {
    $stmt_total->bind_param("ss", $fecha_inicio, $fecha_fin);
}
$stmt_total->execute();
$resultado_total = $stmt_total->get_result()->fetch_assoc();
$total_registros = $resultado_total['total'];
$stmt_total->close();

// Consulta de datos paginada
$sql = "SELECT 
    piezas.pieza AS nombre_pieza,
    piezas.area AS pieza_area,
    piezas.ot AS pieza_ot,
    encargado.id as encargado_id,
    encargado.ot_tardia AS encargado_ot,
    encargado.cantidad,
    trabajadores.nombre,
    trabajadores.apellidos,
    trabajadores.fecha_ingreso,
    encargado.tiempo,
    encargado.pieza_tardia,
    encargado.fecha
FROM 
    piezas 
RIGHT JOIN 
    encargado ON piezas.id = encargado.id_pieza 
LEFT JOIN
    trabajadores ON encargado.id_trabajador = trabajadores.id
WHERE 
    encargado.fecha >= ? AND encargado.fecha <= ?
    AND (piezas.ot = 703 OR piezas.ot = 705 or encargado.ot_tardia = 703 OR encargado.ot_tardia = 705)";
if ($id_trabajador > 0) {
    $sql .= " AND encargado.id_trabajador = ?";
}
$sql .= " ORDER BY encargado.fecha DESC LIMIT ? OFFSET ?";
$stmt = $conexion->prepare($sql);
if ($id_trabajador > 0) {
    $stmt->bind_param("sssii", $fecha_inicio, $fecha_fin, $id_trabajador, $registros_por_pagina, $offset);
} else {
    $stmt->bind_param("ssii", $fecha_inicio, $fecha_fin, $registros_por_pagina, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Producción</title>
</head>
<body id="reporte_produccion">
    <div class="principal">
        <section>
            <h1>Reporte de Producción</h1>

            <div class="buscador">
                <form class="reporte_formulario" method="GET" action="">
                    <label for="fecha_inicio">Fecha de Inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">

                    <label for="fecha_fin">Fecha Fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">

                    <label for="id_trabajador">Trabajador:</label>
                    <select name="id_trabajador" id="id_trabajador">
                        <option value="">Todos</option>
                        <?php foreach ($trabajadores as $trabajador): ?>
                            <option value="<?php echo $trabajador['id']; ?>" <?php echo ($id_trabajador == $trabajador['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($trabajador['nombre_completo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="hidden" name="pestaña" value="reporte_vacaciones">
                    <input type="submit" value="Buscar">
                </form>
            </div>

            <div class="registros-por-pagina">
                <form method="GET" action="">
                    <label for="registros_por_pagina">Registros por página:</label>
                    <select name="registros_por_pagina" id="registros_por_pagina" onchange="this.form.submit()">
                        <?php foreach ([10, 20, 50, 100] as $num): ?>
                            <option value="<?php echo $num; ?>" <?php echo $registros_por_pagina == $num ? 'selected' : ''; ?>><?php echo $num; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                    <input type="hidden" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                    <input type="hidden" name="id_trabajador" value="<?php echo $id_trabajador; ?>">
                </form>
            </div>

            <?php
            $activeFilters = [];
            if (!empty($fecha_inicio)) $activeFilters[] = "Desde: " . htmlspecialchars($fecha_inicio);
            if (!empty($fecha_fin)) $activeFilters[] = "Hasta: " . htmlspecialchars($fecha_fin);
            if ($id_trabajador > 0) {
                foreach ($trabajadores as $t) {
                    if ($t['id'] == $id_trabajador) {
                        $activeFilters[] = "Trabajador: " . htmlspecialchars($t['nombre_completo']);
                        break;
                    }
                }
            }
            ?>

            <?php if ($result && $result->num_rows > 0): ?>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Trabajador</th>
                            <th>Tiempo</th>
                            <th>Pieza Tardía</th>
                            <th>Fecha vacación</th>
                            <th>Fecha ingreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']); ?></td>
                                <td><?php echo htmlspecialchars($row['tiempo']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_pieza'] ?: $row['pieza_tardia']); ?></td>
                                <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($row['fecha_ingreso']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <?php
                $total_paginas = ceil($total_registros / $registros_por_pagina);
                echo '<div class="paginacion">';
                for ($i = 1; $i <= $total_paginas; $i++) {
                    if ($i == $pagina_actual) {
                        echo '<span>' . $i . '</span>';
                    } else {
                        echo '<a href="?pagina=' . $i . '&fecha_inicio=' . urlencode($fecha_inicio) . '&fecha_fin=' . urlencode($fecha_fin) . '&id_trabajador=' . $id_trabajador . '&pestaña=reporte_vacaciones&registros_por_pagina=' . $registros_por_pagina . '">' . $i . '</a>';
                    }
                }
                echo '</div>';
                ?>

            <?php else: ?>
                <p>No se encontraron resultados.</p>
            <?php endif; ?>

            <?php $conexion->close(); ?>
        </section>
    </div>
</body>
</html>
