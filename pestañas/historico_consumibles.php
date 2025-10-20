<?php
$consumible = $_GET['consumible'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$descripcion = $_GET['descripcion'] ?? '';
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Construcción de la consulta con filtros
$sql = "SELECT historial_consumibles.*, consumibles.nombre 
        FROM historial_consumibles 
        LEFT JOIN consumibles ON historial_consumibles.id_consumible = consumibles.id 
        WHERE 1";

if (!empty($consumible)) {
    $sql .= " AND consumible = '" . $conexion->real_escape_string($consumible) . "'";
}
if (!empty($tipo)) {
    $sql .= " AND tipo = '" . $conexion->real_escape_string($tipo) . "'";
}
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $sql .= " AND fecha BETWEEN '" . $conexion->real_escape_string($fecha_inicio) . "' AND '" . $conexion->real_escape_string($fecha_fin) . "'";
}
if (!empty($descripcion)) {
    $sql .= " AND descripcion LIKE '%" . $conexion->real_escape_string($descripcion) . "%'";
}
$sql .= " ORDER BY fecha DESC LIMIT $registros_por_pagina OFFSET $offset";

$result = $conexion->query($sql);

// Obtener el total de registros para la paginación
$sql_total = "SELECT COUNT(*) as total FROM historial_consumibles WHERE 1";
$result_total = $conexion->query($sql_total);
$total_registros = $result_total->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);
?>
<head>
    <title>Historial de Consumibles</title>
</head>

<body>
    <div class="principal">
        <section>
    <h2 style="text-align:center;">Historial de Consumibles</h2>
    <div style="display: flex; justify-content: center; margin-bottom: 20px;">
        <form class="reporte_formulario" method="GET" style="display: flex; gap: 10px; align-items: center;">
            <input class="formulario_reporte_ot" type="text" name="consumible" placeholder="ID Consumible" value="<?= htmlspecialchars($consumible) ?>">
            <input class="formulario_reporte_ot" type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
            <input class="formulario_reporte_ot" type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
            <input class="formulario_reporte_ot" type="text" name="descripcion" placeholder="Descripción" value="<?= htmlspecialchars($descripcion) ?>">
            <input class="formulario_reporte_ot" type="hidden" name="pestaña" value="historico_consumibles">
            <input type="submit" value="Buscar">
        </form>
    </div>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Consumible</th>
                <th>Cambio</th>
                <th>Tipo</th>
                <th>Fecha</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= htmlspecialchars($row['cambio']) ?></td>
                    <td><?= htmlspecialchars($row['tipo']) ?></td>
                    <td><?= htmlspecialchars($row['fecha']) ?></td>
                    <td><?= htmlspecialchars($row['descripcion']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <div class='paginacion'>
        <?php
        // Mostrar máximo 10 enlaces de página
        $max_links = 10;
        $start = max(1, $pagina_actual - floor($max_links / 2));
        $end = min($total_paginas, $start + $max_links - 1);
        if ($end - $start + 1 < $max_links) {
            $start = max(1, $end - $max_links + 1);
        }
        if ($start > 1) {
            echo '<a href="?pestaña=historico_consumibles&pagina=1&consumible=' . urlencode($consumible) . '&tipo=' . urlencode($tipo) . '&fecha_inicio=' . urlencode($fecha_inicio) . '&fecha_fin=' . urlencode($fecha_fin) . '&descripcion=' . urlencode($descripcion) . '">1</a>';
            if ($start > 2) echo '<span>...</span>';
        }
        for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i == $pagina_actual): ?>
                <span><?= $i ?></span>
            <?php else: ?>
                <a href="?pestaña=historico_consumibles&pagina=<?= $i ?>&consumible=<?= urlencode($consumible) ?>&tipo=<?= urlencode($tipo) ?>&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>&descripcion=<?= urlencode($descripcion) ?>">
                    <?= $i ?>
                </a>
            <?php endif; ?>
        <?php endfor;
        if ($end < $total_paginas) {
            if ($end < $total_paginas - 1) echo '<span>...</span>';
            echo '<a href="?pestaña=historico_consumibles&pagina=' . $total_paginas . '&consumible=' . urlencode($consumible) . '&tipo=' . urlencode($tipo) . '&fecha_inicio=' . urlencode($fecha_inicio) . '&fecha_fin=' . urlencode($fecha_fin) . '&descripcion=' . urlencode($descripcion) . '">' . $total_paginas . '</a>';
        }
        ?>
    </div>
    </section>
    </div>
</body>
</html>
