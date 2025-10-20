<head>
    <link rel="stylesheet" href="https://use.typekit.net/oov2wcw.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
</head>
<body id="analisis_clientes">
<?php

// Parámetros de filtro
$anio = $_GET['anio'] ?? date('Y');
$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin = $_GET['fecha_fin'] ?? null;

// Condición según filtro
if ($fecha_inicio && $fecha_fin) {
    $condicion = "facturas.alta_sistema BETWEEN '$fecha_inicio' AND '$fecha_fin'";
} else {
    $condicion = "YEAR(facturas.alta_sistema) = '$anio'";
}

// Consulta con condición dinámica
$sql = "SELECT ot.cliente, SUM(facturas.valor_pesos) AS total_facturas 
        FROM facturas 
        left join pedido on facturas.id_pedido = pedido.id
        LEFT JOIN ot ON pedido.ot = ot.ot
        WHERE $condicion
        GROUP BY ot.cliente 
        ORDER BY total_facturas DESC ";

$result = $conexion->query($sql);

// Preparar datos
$clientes = [];
$facturas = [];

while ($row = $result->fetch_assoc()) {
    $clientes[] = $row['cliente'];
    $facturas[] = (float)$row['total_facturas'];
}

$conexion->close();
?>
<div>
<div class="contenedor__filtros" style="margin: 1em;">
    <form class="reporte_formulario" method="GET" action="">
        <label for="anio">Año:</label>
        <select name="anio" id="anio">
            <?php
            $anio_actual = date('Y');
            for ($i = $anio_actual; $i >= $anio_actual - 5; $i--) {
                $selected = (isset($_GET['anio']) && $_GET['anio'] == $i) ? 'selected' : '';
                echo "<option value='$i' $selected>$i</option>";
            }
            ?>
        </select>

        <label for="fecha_inicio">o desde:</label>
        <input type="date" name="fecha_inicio" value="<?= $_GET['fecha_inicio'] ?? '' ?>">

        <label for="fecha_fin">hasta:</label>
        <input type="date" name="fecha_fin" value="<?= $_GET['fecha_fin'] ?? '' ?>">

        <input type="hidden" name="pestaña" value="analisis_clientes">

        <input type="submit" value="Filtrar">
    </form>
</div>

<div class="contenedor__servicios">
    <h2 class="titulo">Análisis de Facturas por Cliente</h2>
    <canvas id="graficaFacturasClientes"></canvas>
</div>
</div>
<script>
    const ctx = document.getElementById('graficaFacturasClientes').getContext('2d');
    const data = {
        labels: <?php echo json_encode($clientes); ?>,
        datasets: [{
            label: 'Total Facturado por Cliente',
            data: <?php echo json_encode($facturas); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.5)',
                'rgba(54, 162, 235, 0.5)',
                'rgba(255, 206, 86, 0.5)',
                'rgba(75, 192, 192, 0.5)',
                'rgba(153, 102, 255, 0.5)',
                'rgba(255, 159, 64, 0.5)',
                'rgba(199, 199, 199, 0.5)',
                'rgba(83, 102, 255, 0.5)',
                'rgba(255, 102, 255, 0.5)',
                'rgba(102, 255, 178, 0.5)'
            ],
            borderColor: 'white',
            borderWidth: 1
        }]
    };

    const options = {
        plugins: {
            datalabels: {
    formatter: (value, context) => {
        const total = context.chart.data.datasets[0].data.reduce((a, b) => a + (parseFloat(b) || 0), 0);
        const porcentaje = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
        return porcentaje;
    },
    color: '#000',
    font: {
        weight: 'bold',
        size: 14
    }
},

            tooltip: {
                callbacks: {
                    label: function(context) {
                        const value = context.parsed;
                        return new Intl.NumberFormat('es-MX', {
                            style: 'currency',
                            currency: 'MXN'
                        }).format(value);
                    }
                }
            }
        }
    };

    new Chart(ctx, {
        type: 'pie',
        data: data,
        options: options,
        plugins: [ChartDataLabels]
    });
</script>

</body>
</html>

