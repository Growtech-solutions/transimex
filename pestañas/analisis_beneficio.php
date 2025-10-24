<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Análisis de beneficio</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body id="analisis_beneficio">
<?php
// Rango de fechas
$fecha_actual = date('Y-m-d');
$fecha_inicio = date('Y-01-01');
$fecha_solicitud = isset($_GET['fecha_solicitud']) ? $_GET['fecha_solicitud'] : $fecha_inicio;
$fecha_solicitudfin = isset($_GET['fecha_solicitudfin']) ? $_GET['fecha_solicitudfin'] : $fecha_actual;
$responsable = isset($_GET['responsable']) ? $_GET['responsable'] : 'todos';

// ===================== FACTURAS =====================
$facturas_sql = "
SELECT 
    YEAR(facturas.alta_sistema) AS year,
    MONTH(facturas.alta_sistema) AS month,
    ot.responsable,
    SUM(facturas.valor_pesos) AS total_facturas
FROM facturas
LEFT JOIN pedido ON facturas.id_pedido = pedido.id
LEFT JOIN ot ON pedido.ot = ot.ot
WHERE facturas.alta_sistema BETWEEN '$fecha_solicitud' AND '$fecha_solicitudfin'
";
if ($responsable != 'todos') {
    $facturas_sql .= " AND ot.responsable = '$responsable'";
}
$facturas_sql .= " GROUP BY YEAR(facturas.alta_sistema), MONTH(facturas.alta_sistema), ot.responsable";
$facturas_result = $conexion->query($facturas_sql);

// ===================== COMPRAS =====================
$compras_sql = "
SELECT 
    YEAR(orden_compra.fecha_solicitud) AS year,
    MONTH(orden_compra.fecha_solicitud) AS month,
    ot.responsable,
    SUM(compras.cantidad * compras.precio_unitario * 
        CASE 
            WHEN orden_compra.moneda = 'MXN' THEN 1 
            ELSE orden_compra.tipo_cambio 
        END) AS total_compras
FROM compras
LEFT JOIN orden_compra ON compras.id_oc = orden_compra.id
LEFT JOIN ot ON compras.ot = ot.ot
WHERE orden_compra.fecha_solicitud BETWEEN '$fecha_solicitud' AND '$fecha_solicitudfin'
";
if ($responsable != 'todos') {
    $compras_sql .= " AND ot.responsable = '$responsable'";
}
$compras_sql .= " GROUP BY YEAR(orden_compra.fecha_solicitud), MONTH(orden_compra.fecha_solicitud), ot.responsable";
$compras_result = $conexion->query($compras_sql);

// ===================== NÓMINA =====================
if ($responsable != 'todos') {
    $nomina_sql = "
    SELECT 
        YEAR(encargado.fecha) AS year,
        MONTH(encargado.fecha) AS month,
        ot.responsable,
        SUM(
            encargado.tiempo * (
                (
                    COALESCE(
                        (
                            SELECT hs.valor_actual
                            FROM historial_salarios hs
                            WHERE hs.id_trabajador = trabajadores.id
                              AND hs.fecha_cambio <= encargado.fecha
                            ORDER BY hs.fecha_cambio DESC
                            LIMIT 1
                        ), 280
                    ) / 8
                ) * 3
            )
        ) AS nomina
    FROM encargado
    LEFT JOIN piezas ON encargado.id_pieza = piezas.id
    LEFT JOIN trabajadores ON encargado.id_trabajador = trabajadores.id
    LEFT JOIN ot ON (encargado.ot_tardia = ot.ot OR piezas.ot = ot.ot)
    WHERE ot.responsable = '$responsable'
      AND encargado.fecha BETWEEN '$fecha_solicitud' AND '$fecha_solicitudfin'
    GROUP BY YEAR(encargado.fecha), MONTH(encargado.fecha), ot.responsable
    ORDER BY year DESC, month DESC
    ";
} else {
    $nomina_sql = "
    SELECT YEAR(Fecha_final) as year, MONTH(Fecha_final) as month, SUM(Percepcion_empresa) as nomina
    FROM nomina
    WHERE Fecha_inicial BETWEEN '$fecha_solicitud' AND '$fecha_solicitudfin'
    GROUP BY YEAR(Fecha_final), MONTH(Fecha_final)
    ";
}
$nomina_result = $conexion->query($nomina_sql);

// ===================== PREPARAR DATOS =====================
$data = [];
// Facturas
while ($row = $facturas_result->fetch_assoc()) {
    $key = $row['year'] . '-' . str_pad($row['month'], 2, '0', STR_PAD_LEFT);
    $data[$key]['facturas'] = $row['total_facturas'] ?? 0;
    $data[$key]['compras'] = 0;
    $data[$key]['nomina'] = 0;
}
// Compras
while ($row = $compras_result->fetch_assoc()) {
    $key = $row['year'] . '-' . str_pad($row['month'], 2, '0', STR_PAD_LEFT);
    if (!isset($data[$key])) $data[$key] = ['facturas'=>0,'compras'=>0,'nomina'=>0];
    $data[$key]['compras'] = $row['total_compras'] ?? 0;
}
// Nómina
while ($row = $nomina_result->fetch_assoc()) {
    $key = $row['year'] . '-' . str_pad($row['month'], 2, '0', STR_PAD_LEFT);
    if (!isset($data[$key])) $data[$key] = ['facturas'=>0,'compras'=>0,'nomina'=>0];
    $data[$key]['nomina'] = $row['nomina'] ?? 0;
}

// ===================== CALCULAR BENEFICIOS =====================
$months = [];
$facturas_arr = [];
$compras_arr = [];
$nomina_arr = [];
$diferencias = [];
$diferencia_acumulada = [];

$acumulado = 0;
foreach ($data as $key => $values) {
    $months[] = $key;
    $facturas_val = $values['facturas'] ?? 0;
    $compras_val = ($values['compras'] ?? 0) * -1; // negativo
    $nomina_val = ($values['nomina'] ?? 0) * -1;   // negativo
    $beneficio = $facturas_val + $compras_val + $nomina_val;

    $facturas_arr[] = $facturas_val;
    $compras_arr[] = $compras_val;
    $nomina_arr[] = $nomina_val;
    $diferencias[] = $beneficio;

    $acumulado += $beneficio;
    $diferencia_acumulada[] = $acumulado;
}
?>

<div class="contenedor__servicios">
    <h2 class="titulo">Análisis de Ingresos y Compras</h2>
    <form class="reporte_formulario" method="GET">
        <label for="fecha_solicitud">Desde:</label>
        <input type="date" id="fecha_solicitud" name="fecha_solicitud" value="<?php echo htmlspecialchars($fecha_solicitud, ENT_QUOTES); ?>">

        <label for="fecha_solicitudfin">Hasta:</label>
        <input type="date" id="fecha_solicitudfin" name="fecha_solicitudfin" value="<?php echo htmlspecialchars($fecha_solicitudfin, ENT_QUOTES); ?>">

        <label for="responsable">Responsable:</label>
        <?php 
        $select_responsables = "Select DISTINCT responsable from ot where fecha_alta BETWEEN '$fecha_solicitud' AND '$fecha_solicitudfin'";
        $responsables_result = $conexion->query($select_responsables);
        echo '<select id="responsable" name="responsable">';
        echo '<option value="todos"' . ($responsable == 'todos' ? ' selected' : '') . '>Todos</option>';
        while ($row = $responsables_result->fetch_assoc()) {
            $resp = $row['responsable'];
            $selected = ($responsable == $resp) ? ' selected' : '';
            echo "<option value=\"" . htmlspecialchars($resp, ENT_QUOTES) . "\"$selected>" . htmlspecialchars($resp) . "</option>";
        }
        echo '</select>';
        $responsables_result->free();
        ?>

        <input type="hidden" name="pestaña" value="analisis_beneficio">
        <input type="submit" value="Filtrar">
    </form>

    <canvas id="graficaComprasFacturas"></canvas>
</div>

<script>
var ctx = document.getElementById('graficaComprasFacturas').getContext('2d');
var graficaComprasFacturas = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [
            {
                label: 'Facturas',
                data: <?php echo json_encode($facturas_arr); ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 1,
                fill: true
            },
            {
                label: 'Compras',
                data: <?php echo json_encode($compras_arr); ?>,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderWidth: 1,
                fill: true
            },
            {
                label: 'Nómina',
                data: <?php echo json_encode($nomina_arr); ?>,
                borderColor: 'rgba(153, 102, 255, 1)',
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderWidth: 1,
                fill: true
            },
            {
                label: 'Beneficio',
                data: <?php echo json_encode($diferencias); ?>,
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: true
            },
            {
                label: 'Beneficio Acumulado',
                data: <?php echo json_encode($diferencia_acumulada); ?>,
                borderColor: 'rgba(255, 165, 0, 1)',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false
            }
        ]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>
