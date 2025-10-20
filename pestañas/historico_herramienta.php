<?php
// Filtros
$folio = $_GET['folio'] ?? '';
$herramienta = $_GET['herramienta'] ?? '';
$trabajador = $_GET['trabajador'] ?? '';
$area = $_GET['area'] ?? '';
$estado = $_GET['estado'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$registros_por_pagina = $_GET['registros_por_pagina'] ?? 20;
$pagina_actual = $_GET['pagina'] ?? 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Query base
$sql = "SELECT * FROM historial_herramientas WHERE 1=1";

// Aplicar filtros
if ($folio != '') $sql .= " AND folio LIKE '%$folio%'";
if ($herramienta != '') $sql .= " AND herramienta LIKE '%$herramienta%'";
if ($trabajador != '') $sql .= " AND (trabajador_anterior LIKE '%$trabajador%' OR trabajador_nuevo LIKE '%$trabajador%')";
if ($area != '') $sql .= " AND (area_anterior LIKE '%$area%' OR area_nueva LIKE '%$area%')";
if ($estado != '') $sql .= " AND (estado_anterior LIKE '%$estado%' OR estado_nuevo LIKE '%$estado%')";
if ($fecha_inicio != '' && $fecha_fin != '') $sql .= " AND fecha_modificacion BETWEEN '$fecha_inicio' AND '$fecha_fin'";

// Paginación
$sql_total = $conexion->query($sql);
$total_registros = $sql_total->num_rows;
$sql .= " ORDER BY fecha_modificacion DESC LIMIT $offset, $registros_por_pagina";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Herramientas</title>
</head>
<body>
    <div class="principal">
        <div>

<h2 style="text-align:center;">Historial de Herramientas</h2>

<div class="filtros">
    <form class="reporte_formulario" method="GET">
        <input type="text" name="folio" placeholder="Folio" value="<?= htmlspecialchars($folio) ?>">
        <input type="text" name="herramienta" placeholder="Herramienta" value="<?= htmlspecialchars($herramienta) ?>">
        <input type="text" name="trabajador" placeholder="Trabajador" value="<?= htmlspecialchars($trabajador) ?>">
        <input type="text" name="area" placeholder="Área" value="<?= htmlspecialchars($area) ?>">
        <input type="text" name="estado" placeholder="Estado" value="<?= htmlspecialchars($estado) ?>">
        <label>Entre:</label>
        <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
        <label>y</label>
        <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">

        <select name="registros_por_pagina" onchange="this.form.submit()">
            <?php foreach ([10, 20, 50, 100] as $num) : ?>
                <option value="<?= $num ?>" <?= $registros_por_pagina == $num ? 'selected' : '' ?>><?= $num ?> por página</option>
            <?php endforeach; ?>
        </select>

        <input type="hidden" name="pestaña" value="historico_herramienta">

        <input type="submit" value="Buscar">
    </form>
</div>

<?php if ($resultado->num_rows > 0): ?>
<table>
    <tr>
        <th>Folio</th>
        <th>Herramienta</th>
        <th>Trabajador Anterior</th>
        <th>Trabajador Nuevo</th>
        <th>Área Anterior</th>
        <th>Área Nueva</th>
        <th>Estado Anterior</th>
        <th>Estado Nuevo</th>
        <th>Fecha Modificación</th>
    </tr>
    <?php while ($row = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['folio']) ?></td>
            <td><?= htmlspecialchars($row['herramienta']) ?></td>
            <td><?= htmlspecialchars($row['trabajador_anterior']) ?></td>
            <td><?= htmlspecialchars($row['trabajador_nuevo']) ?></td>
            <td><?= htmlspecialchars($row['area_anterior']) ?></td>
            <td><?= htmlspecialchars($row['area_nueva']) ?></td>
            <td><?= htmlspecialchars($row['estado_anterior']) ?></td>
            <td><?= htmlspecialchars($row['estado_nuevo']) ?></td>
            <td><?= htmlspecialchars($row['fecha_modificacion']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<?php
$total_paginas = ceil($total_registros / $registros_por_pagina);
echo "<div class='paginacion'>";
// Mostrar paginación con máximo 10 botones y controles
$max_botones = 10;
$inicio = max(1, $pagina_actual - floor($max_botones / 2));
$fin = min($total_paginas, $inicio + $max_botones - 1);
if ($fin - $inicio < $max_botones - 1) {
    $inicio = max(1, $fin - $max_botones + 1);
}

// Construir base de la URL sin 'pagina'
$params = $_GET;
unset($params['pagina']);
$base_url = '?' . http_build_query($params);

// Botón primero
if ($pagina_actual > 1) {
    echo "<a href='{$base_url}&pagina=1' style='margin:0 5px;'>Primero</a>";
    echo "<a href='{$base_url}&pagina=" . ($pagina_actual - 1) . "' style='margin:0 5px;'>Anterior</a>";
}

// Botones de página
for ($i = $inicio; $i <= $fin; $i++) {
    $estilo = ($i == $pagina_actual) ? "font-weight:bold;" : "";
    echo "<a href='{$base_url}&pagina=$i' style='margin:0 5px;{$estilo}'>$i</a>";
}

// Botón siguiente y último
if ($pagina_actual < $total_paginas) {
    echo "<a href='{$base_url}&pagina=" . ($pagina_actual + 1) . "' style='margin:0 5px;'>Siguiente</a>";
    echo "<a href='{$base_url}&pagina={$total_paginas}' style='margin:0 5px;'>Último</a>";
}
echo "</div>";
?>

<?php else: ?>
    <p style="text-align:center;">No se encontraron resultados.</p>
<?php endif; ?>
</div>
</div>
</body>
</html>

<?php $conexion->close(); ?>
