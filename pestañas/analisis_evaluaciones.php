<?php
$sql_evaluacion = "SELECT sum(calidad) as calidad, sum(tiempos) as tiempos, sum(atencion) as atencion, sum(alcance) as alcance, sum(precios) as precios, count(id) as cantidad FROM clientes_respuestas";
$result_evaluacion = $conexion->query($sql_evaluacion);
$row_eval = $result_evaluacion->fetch_assoc();

$calidad = $row_eval['cantidad'] ? $row_eval['calidad'] / $row_eval['cantidad'] : 0;
$tiempos = $row_eval['cantidad'] ? $row_eval['tiempos'] / $row_eval['cantidad'] : 0;
$atencion = $row_eval['cantidad'] ? $row_eval['atencion'] / $row_eval['cantidad'] : 0;
$alcance = $row_eval['cantidad'] ? $row_eval['alcance'] / $row_eval['cantidad'] : 0;
$precios = $row_eval['cantidad'] ? $row_eval['precios'] / $row_eval['cantidad'] : 0;

$labels = ['Calidad', 'Tiempo', 'Atencion', 'Alcance', 'Precios'];
$data = [
    (float)$calidad,
    (float)$tiempos,
    (float)$atencion,
    (float)$alcance,
    (float)$precios
];
$data_json = json_encode($data);
$labels_json = json_encode($labels);

echo "<div style='width: 400px; margin: 0 auto;'><canvas id='radarChart'></canvas></div>";
echo "
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<script>
const ctx = document.getElementById('radarChart').getContext('2d');
new Chart(ctx, {
    type: 'radar',
    data: {
        labels: $labels_json,
        datasets: [{
            label: 'Evaluación del Cliente',
            data: $data_json,
            fill: true,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgb(54, 162, 235)',
            pointBackgroundColor: 'rgb(54, 162, 235)'
        }]
    },
    options: {
        scales: {
            r: {
                min: 0,
                max: 10,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>
";
?>