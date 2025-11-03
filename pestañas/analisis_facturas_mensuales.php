<?php
$anio_actual = date('Y');
$anio_solicitud = isset($_GET['anio_solicitud']) ? (int)$_GET['anio_solicitud'] : $anio_actual - 3;
$anio_solicitudfin = isset($_GET['anio_solicitudfin']) ? (int)$_GET['anio_solicitudfin'] : $anio_actual;

$meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// Preparar array vacío
$facturas = [];
for ($y=$anio_solicitud;$y<=$anio_solicitudfin;$y++){
    for ($m=1;$m<=12;$m++) $facturas[$y][$m] = 0;
}

// Traer datos
$sql = "SELECT YEAR(alta_sistema) AS year, MONTH(alta_sistema) AS month, SUM(valor_pesos) AS total 
        FROM facturas 
        WHERE YEAR(alta_sistema) BETWEEN ? AND ? 
        GROUP BY YEAR(alta_sistema), MONTH(alta_sistema)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii",$anio_solicitud,$anio_solicitudfin);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()){
    $facturas[$row['year']][$row['month']] = (float)$row['total'];
}
$stmt->close();
$conexion->close();

// Preparar datos JS
$datasets = [];
$colores = [
    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', 
    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
];

$i=0;
foreach($facturas as $y=>$mesesData){
    $datasets[] = [
        'label' => "$y",
        'data' => array_values($mesesData),
        'borderColor' => $colores[$i % count($colores)],
        'backgroundColor' => $colores[$i % count($colores)] . '20',
        'borderWidth' => 2,
        'fill' => false,
        'tension' => 0.1
    ];
    $i++;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Facturas Mensuales</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .form-container {
        margin: 20px 0;
        padding: 15px;
        background-color: #f5f5f5;
        border-radius: 5px;
        text-align: center;
    }
    .form-group {
        display: inline-block;
        margin-right: 15px;
    }
    .button-group {
        margin: 20px 0;
        text-align: center;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    input, button {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }
    button {
        background-color: #007cba;
        color: white;
        cursor: pointer;
        margin: 0 5px;
    }
    button:hover {
        background-color: #005a8b;
    }
    button.active {
        background-color: #005a8b;
    }
</style>
</head>
<body>
    <div>
        <h2 style="text-align: center;">Análisis de Facturas por Mes</h2>
        
        <!-- Formulario de selección de fechas -->
        <div class="form-container">
            <form method="GET">
                <div class="form-group">
                    <label for="anio_solicitud">Año Inicial:</label>
                    <input type="number" id="anio_solicitud" name="anio_solicitud" 
                           value="<?php echo $anio_solicitud; ?>" 
                           min="2000" max="<?php echo $anio_actual + 1; ?>">
                </div>
                <div class="form-group">
                    <label for="anio_solicitudfin">Año Final:</label>
                    <input type="number" id="anio_solicitudfin" name="anio_solicitudfin" 
                           value="<?php echo $anio_solicitudfin; ?>" 
                           min="2000" max="<?php echo $anio_actual + 1; ?>">
                </div>
                <input type="hidden" name="pestaña" value="analisis_facturas_mensuales">
                <div class="form-group">
                    <button type="submit">Actualizar Gráfica</button>
                </div>
            </form>
        </div>

        <!-- Botones para cambiar vista -->
        <div class="button-group">
            <button id="btnMensual" class="active" onclick="cambiarVista('mensual')">Facturas por Mes</button>
            <button id="btnAcumulado" onclick="cambiarVista('acumulado')">Acumulado</button>
        </div>

        <canvas id="graficaFacturas" width="900" height="400"></canvas>
    </div>

<script>
const ctx = document.getElementById('graficaFacturas').getContext('2d');
const meses = <?php echo json_encode($meses); ?>;
const datasetsOriginales = <?php echo json_encode($datasets); ?>;

// Calcular datos acumulados
const datasetsAcumulados = datasetsOriginales.map(dataset => {
    const dataAcumulada = [];
    let acumulado = 0;
    dataset.data.forEach(valor => {
        acumulado += valor;
        dataAcumulada.push(acumulado);
    });
    return {
        ...dataset,
        data: dataAcumulada
    };
});

let grafica = new Chart(ctx, {
    type: 'line',
    data: { 
        labels: meses, 
        datasets: datasetsOriginales 
    },
    options: {
        responsive: true,
        scales: {
            y: { 
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('es-AR').format(value);
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': $' + new Intl.NumberFormat('es-AR').format(context.parsed.y);
                    }
                }
            }
        }
    }
});

function cambiarVista(tipo) {
    // Actualizar botones activos
    document.getElementById('btnMensual').classList.remove('active');
    document.getElementById('btnAcumulado').classList.remove('active');
    
    if (tipo === 'mensual') {
        document.getElementById('btnMensual').classList.add('active');
        grafica.data.datasets = datasetsOriginales;
    } else {
        document.getElementById('btnAcumulado').classList.add('active');
        grafica.data.datasets = datasetsAcumulados;
    }
    
    grafica.update();
}
</script>
</body>
</html>
