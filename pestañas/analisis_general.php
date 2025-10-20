<!DOCTYPE html>
<html lang="en">
<head>
    <title>Análisis general</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Add custom styling here if needed */
        .contenedor__servicios {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
</head>
<body id="analisis_general">
    <div class="principal">
<?php
date_default_timezone_set('America/Mexico_City');

if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_final'])) {
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_final = $_GET['fecha_final'];
} else {
    $fecha_inicio = date('Y') . '-01-01';
    $fecha_final = date('Y-m-d');
}
// Totales
$total_proyectos = $conexion->query("SELECT COUNT(*) as total FROM ot WHERE fecha_alta BETWEEN '$fecha_inicio' AND '$fecha_final'")->fetch_assoc()['total'];

$total_proyectos_facturados = $conexion->query("
    SELECT COUNT(*) as total
    FROM (
        SELECT ot.ot
        FROM ot
        LEFT JOIN pedido ON ot.ot = pedido.ot
        LEFT JOIN facturas ON pedido.id = facturas.id_pedido
        WHERE ot.fecha_alta BETWEEN '$fecha_inicio' AND '$fecha_final'
        GROUP BY ot.ot
        HAVING 
            IFNULL(SUM(pedido.valor_pesos), 0) > 0 AND
            IFNULL(SUM(facturas.valor_pesos), 0) = IFNULL(SUM(pedido.valor_pesos), 0)
    ) AS facturados
")->fetch_assoc()['total'];

$total_proyectos_activos = $conexion->query("SELECT COUNT(*) as total FROM ot WHERE estado = 'Activo' AND fecha_alta BETWEEN '$fecha_inicio' AND '$fecha_final'")->fetch_assoc()['total'];
$total_proyectos_activos -= $total_proyectos_facturados;

$total_pedidos_por_monto = $conexion->query("SELECT COUNT(*) as total FROM ot WHERE estado = 'Perdido por monto' AND fecha_alta BETWEEN '$fecha_inicio' AND '$fecha_final'")->fetch_assoc()['total'];

$total_pedidos_por_tiempo = $conexion->query("SELECT COUNT(*) as total FROM ot WHERE estado = 'Perdido por tiempo' AND fecha_alta BETWEEN '$fecha_inicio' AND '$fecha_final'")->fetch_assoc()['total'];

$total_pedidos_cancelados = $conexion->query("SELECT COUNT(*) as total FROM ot WHERE estado = 'Cancelado' AND fecha_alta BETWEEN '$fecha_inicio' AND '$fecha_final'")->fetch_assoc()['total'];

// Suma de valor_pesos
$valor_pedidos = $conexion->query("SELECT IFNULL(SUM(valor_pesos), 0) as total FROM pedido WHERE fecha_alta BETWEEN '$fecha_inicio' AND '$fecha_final'")->fetch_assoc()['total'];

$valor_facturas = $conexion->query("SELECT IFNULL(SUM(valor_pesos), 0) as total FROM facturas WHERE fecha_pago BETWEEN '$fecha_inicio' AND '$fecha_final'")->fetch_assoc()['total'];

$valor_compras = $conexion->query("SELECT IFNULL(SUM(total_pesos), 0) as total FROM orden_compra WHERE fecha_solicitud BETWEEN '$fecha_inicio' AND '$fecha_final'")->fetch_assoc()['total'];

$valor_nominas = $conexion->query("SELECT IFNULL(SUM(Percepcion_empresa), 0) as total FROM nomina WHERE Fecha_final BETWEEN '$fecha_inicio' AND '$fecha_final'")->fetch_assoc()['total'];

$valor_incapacidades = $conexion->query("SELECT 
  COUNT(
    CASE 
      WHEN (e.ot_tardia IN (706, 707) OR piezas.ot IN (706, 707)) 
      THEN 1 
    END
  ) AS incapacidad
FROM encargado e
LEFT JOIN piezas ON e.id_pieza = piezas.id
WHERE e.fecha BETWEEN '$fecha_inicio' AND '$fecha_final';
")->fetch_assoc()['incapacidad'];


$resultado_valor_futuro = $valor_pedidos - $valor_facturas;

// Encuestas
$sql_encuestas = "
    SELECT 
        AVG(responsable) as avg_responsable,
        AVG(atencion) as avg_atencion,
        AVG(alcance) as avg_alcance,
        AVG(precios) as avg_precios,
        AVG(calidad) as avg_calidad,
        AVG(tiempos) as avg_tiempos,
        COUNT(*) as total_encuestas
    FROM clientes_respuestas 
    WHERE fecha_registro BETWEEN '$fecha_inicio' AND '$fecha_final'
";
$datos_encuestas = $conexion->query($sql_encuestas)->fetch_assoc();
$promedio_satisfaccion = (
    $datos_encuestas['avg_responsable'] +
    $datos_encuestas['avg_atencion'] +
    $datos_encuestas['avg_alcance'] +
    $datos_encuestas['avg_precios'] +
    $datos_encuestas['avg_calidad'] +
    $datos_encuestas['avg_tiempos']
) / 6;

// Query to calculate the sum of pedidos
$sql_pedido_pendiente = "SELECT SUM(valor_pesos) as total_pedidos FROM pedido left join ot on pedido.ot = ot.ot WHERE ot.fecha_alta >= '$fecha_inicio' AND ot.fecha_alta <= '$fecha_final'";
$resultado_pedidos_pendientes = $conexion->query($sql_pedido_pendiente);

// Query to calculate the sum of facturas
$sql_factura_pendiente = "SELECT SUM(facturas.valor_pesos) as total_facturas FROM facturas left join pedido on facturas.id_pedido = pedido.id left join ot on pedido.ot = ot.ot WHERE ot.fecha_alta >= '$fecha_inicio' AND ot.fecha_alta <= '$fecha_final'";
$resultado_facturas_pendientes = $conexion->query($sql_factura_pendiente);

// Fetch the results as associative arrays
$total_pedidos = $resultado_pedidos_pendientes->fetch_assoc()['total_pedidos'];
$total_facturas = $resultado_facturas_pendientes->fetch_assoc()['total_facturas'];

// Calculate the difference
$resultado_valor_futuro = $total_pedidos - $total_facturas;



// Formulario
echo '
<div class="contenedor__servicios">
<h2 class="titulo">Análisis general</h2>
<form method="get" style="align:center; margin-bottom: 2rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
    <label>Fecha inicio:
        <input type="date" name="fecha_inicio" value="' . htmlspecialchars($fecha_inicio) . '" required>
    </label>
    <label>Fecha final:
        <input type="date" name="fecha_final" value="' . htmlspecialchars($fecha_final) . '" required>
    </label>
    <input type="hidden" name="pestaña" value="analisis_general">
    <button type="submit" style="padding: 0.5rem 1.5rem; background: #007bff; color: #fff; border: none; border-radius: 4px;">Filtrar</button>
</form>';

// Mostrar resultados
echo '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; justify-items: center; margin-top: 2rem;">';

function tarjeta($titulo, $valor, $color_fondo, $color_texto = '#000') {
    echo "
    <div style='background: $color_fondo; color: $color_texto; padding: 0.7rem 1rem; border-radius: 8px; box-shadow: 0 2px 8px #0001; min-width: 140px; max-width: 200px; text-align: center;'>
        <div style='font-size: 1rem;'>$titulo</div>
        <div style='font-size: 1.2rem; font-weight: bold;'>$valor</div>
    </div>";
}

// Tarjetas
tarjeta("Total Proyectos", $total_proyectos, "#e3e3fa", "#6f42c1");
tarjeta("Proyectos Terminados", $total_proyectos_facturados, "#e3e3fa", "#6f42c1");
tarjeta("Proyectos Activos", $total_proyectos_activos, "#e3e3fa", "#6f42c1");
tarjeta("Perdidos por Monto", $total_pedidos_por_monto, "#f8d7da", "#dc3545");
tarjeta("Perdidos por Tiempo", $total_pedidos_por_tiempo, "#f8d7da", "#dc3545");
tarjeta("Pedidos Cancelados", $total_pedidos_cancelados, "#f5c6cb", "#e3342f");
tarjeta("Promedio satisfacción", number_format($promedio_satisfaccion, 2) . '%', "#e6f9d5", "#3a6b1a");
tarjeta("Pedidos", '$' . number_format($valor_pedidos, 2), "#e6f9d5", "#3a6b1a");
tarjeta("Facturas", '$' . number_format($valor_facturas, 2), "#e6f9d5", "#3a6b1a");
tarjeta("Compras", '$' . number_format($valor_compras, 2), "#e2e3e5", "#383d41");
tarjeta("Nomina", '$' . number_format($valor_nominas, 2), "#e2e3e5", "#383d41");
tarjeta("Pedidos pendientes", $resultado_valor_futuro, "#e2e3e5", "#383d41");

echo '</div>';
echo '</div>';
?>
</div>