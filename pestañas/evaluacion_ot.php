<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reporte de OT</title>
    <style>
        .documento_ot {
            width: 80%;
            padding: 20px;
            border: 1px solid black;
            margin-top: 20px;
            margin-left: auto;
            margin-right: auto;
        }
        .logo_ot {
            height: 8rem;
        }
        .text-color {
          color: rgb(29, 20, 62);
        }
        .header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            align-items: center;
            text-align: center;
            justify-content: space-between;
        }
        .header h2 {
            flex: 1;
            text-align: center;
            margin: 0;
            font-size: 28px;
        }
        .ot {
            font-size: 28px;
            color: rgb(29, 20, 62);
        }
        .detalles {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            margin-top: 20px;
            gap: 20px; /* Optional: Adjust gap between grid items */
        }
        .resumen {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-top: 20px;
            gap: 20px; /* Optional: Adjust gap between grid items */
            text-align: center;
        }
        .resumen2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-top: 20px;
            gap: 20px; /* Optional: Adjust gap between grid items */
            text-align: center;
        }
        .detalles p, .resumen p {
            margin: 5px 0;
        }
        .centrado {
            text-align: center;
        }
        .linea-gris {
            width: 98%;
            border-top: 1px solid gray;
            margin: 20px auto;
        }
        .resumen p:nth-child(1),
        .resumen p:nth-child(2),
        .resumen p:nth-child(4),
        .resumen p:nth-child(6) {
            grid-column: 1 / 2;
        }
        .resumen p:nth-child(3),
        .resumen p:nth-child(5) {
            grid-column: 2 / 3;
        }
         .principal {
           margin-top:1.5%;
           margin-bottom:3%;
        }
    </style>
</head>
<body id="gerencia">

<div class="principal">
    <section class="mensaje">
        <div class="centrado">
            <h2>Reporte de OT</h2>
            <form method="GET" action="">
                <label for="ot">Ingrese la OT:</label>
                <input type="text" id="ot" name="ot" required>
                <input type="hidden" name="pestaña" value="evaluacion_ot">
                <input type="submit" value="Buscar">
            </form>
        </div>
        <?php
        if (isset($_GET['ot'])) {
            $ot = $conexion->real_escape_string($_GET['ot']);
            $sql_ot = "SELECT * FROM ot WHERE ot='$ot'";
            $result_ot = $conexion->query($sql_ot);
            $sql_mano_de_obra ="
                SELECT 
                    trabajadores.id AS trabajador_id,
                    encargado.tiempo,
                    encargado.fecha,
                    (
                        SELECT hs.valor_actual
                        FROM historial_salarios hs
                        WHERE hs.id_trabajador = trabajadores.id
                          AND hs.fecha_cambio <= encargado.fecha
                        ORDER BY hs.fecha_cambio DESC
                        LIMIT 1
                    ) AS salario_actual
                FROM 
                    encargado
                LEFT JOIN 
                    piezas ON encargado.id_pieza = piezas.id
                LEFT JOIN
                    trabajadores ON encargado.id_trabajador = trabajadores.id
                WHERE 
                    piezas.ot = $ot OR encargado.ot_tardia = $ot;
                ";
                $result_mano_de_obra = $conexion->query($sql_mano_de_obra);
                $costo_mano_de_obra = 0;
                if ($result_mano_de_obra && $result_mano_de_obra->num_rows > 0) {
                    while ($row = $result_mano_de_obra->fetch_assoc()) {
                        // Obtiene los valores del query
                        $salario_actual = $row['salario_actual'] !== null ? $row['salario_actual'] : 280; // Salario por d��a
                        $tiempo = $row['tiempo'] !== null ? $row['tiempo'] : 0; // Tiempo trabajado
                        $costo_mano = ($salario_actual / 8) * $tiempo;
                        $costo_mano_de_obra += $costo_mano*3;
                    }
                        
                } else {
                    $costo_mano_de_obra = 0;
                }

            $sql_tiempo = "SELECT SUM(tiempo) FROM encargado LEFT JOIN piezas ON encargado.id_pieza = piezas.id WHERE piezas.ot = $ot OR encargado.ot_tardia = $ot;";
                $result_tiempo = $conexion->query($sql_tiempo);
                $tiempo = 0;
                if ($result_tiempo && $result_tiempo->num_rows > 0) {
                    $row = $result_tiempo->fetch_assoc();
                    $tiempo = $row['SUM(tiempo)'];
                }
            
            $sql_pedidos = "SELECT SUM(valor_pesos) from pedido where ot= $ot ;";
            $result_pedidos = $conexion->query($sql_pedidos);
            $total_pedidos = 0;
            if ($result_pedidos && $result_pedidos->num_rows > 0) {
                $row = $result_pedidos->fetch_assoc();
                $total_pedidos = $row['SUM(valor_pesos)']; 
            }

            $sql_facturas = "SELECT SUM(facturas.valor_pesos) from facturas left join pedido on facturas.id_pedido = pedido.id where ot= $ot ;";
            $result_ot = $conexion->query($sql_facturas);
            $total_facturas = 0;
            if ($result_ot->num_rows > 0) {
                $row = $result_ot->fetch_assoc();
                $total_facturas = $row['SUM(facturas.valor_pesos)'];
            }
            
            $porcentaje_pendiente = ($total_pedidos > 0) ? (($total_facturas) / $total_pedidos) * 100 : 0;

            $sql_compras = "SELECT SUM(compras.cantidad * compras.precio_unitario * 
                CASE 
                    WHEN orden_compra.moneda = 'MXN' THEN 1 
                    ELSE orden_compra.tipo_cambio 
                END) AS total_pesos 
                FROM compras 
                LEFT JOIN orden_compra ON compras.id_oc = orden_compra.id 
                WHERE compras.ot = $ot";
            $result_ot = $conexion->query($sql_compras);
            $compras = 0;
            if ($result_ot->num_rows > 0) {
                $row = $result_ot->fetch_assoc();
                $compras = $row['total_pesos'];
            }
            if ($tiempo==0){
                $ev = ($total_pedidos-$compras);
            }
            else{
                $ev = ($total_pedidos-$compras)/$costo_mano_de_obra;

            }
            $ganancia = $total_pedidos - $compras - $costo_mano_de_obra;
            $result_ot = $conexion->query($sql_ot);
            if ($result_ot->num_rows > 0) {
                $row = $result_ot->fetch_assoc();
                echo "<div class='documento_ot'>";
                    echo "<div class='header'>";
                        echo "<img class='logo_ot' src='../img/logo.png' alt='Logo'>";
                        echo "<h2 class='text-color'> " . ucwords($row["descripcion"]) . "</h2>";
                        echo "<h2 class='text-color'>" . htmlspecialchars($row["ot"]) . "</h2>";
                    echo "</div>";
                    echo "<div class='linea-gris'></div>";

                    echo "<div class='detalles'>";
                        echo "<p><strong>Cliente:</strong> " . $row["cliente"] . "</p>";
                        echo "<p><strong>Alta:</strong> " . $row["fecha_alta"] . "</p>";
                        echo "<p><strong>Descripción:</strong> " . $row["descripcion"] . "</p>";
                        echo "<p><strong>Responsable:</strong> " . $row["responsable"] . "</p>";
                    echo "</div>";
                    echo "<div class='linea-gris'></div>";
                    echo "<div class='resumen'>";
                        echo "<p><strong>Horas trabajadas:</strong> <a href=\"$header_loc.php?pestaña=tiempo_piezas&header_loc=$header_loc&ot=" . urlencode($ot) . "\" target=\"_blank\">" . $tiempo . "</a></p>";
                        echo "<p><strong>Mano de obra:</strong> $" . number_format($costo_mano_de_obra, 2) . "</p>";

                        echo "<p><strong>Pedidos:</strong> <a href=\"$header_loc.php?pestaña=pedidos&header_loc=$header_loc&ot=" . urlencode($ot) . "\" target=\"_blank\">$" . number_format($total_pedidos, 2) . "</a></p>";
                        echo "<p><strong>Compras:</strong> <a href=\"$header_loc.php?pestaña=historial_de_compras&header_loc=$header_loc&ot=" . urlencode($ot) . "\" target=\"_blank\">$" . number_format($compras, 2) . "</a></p>";
                        echo "<p><strong>Facturas:</strong> <a href=\"$header_loc.php?pestaña=facturas&header_loc=$header_loc&ot=" . urlencode($ot) . "\" target=\"_blank\">$" . number_format($total_facturas, 2) . "</a>";
                        echo "</p>";
                    echo "</div>";
                    echo "<div class='linea-gris'></div>";
                    echo "<div class='resumen2'>";
                        echo "<p><strong>Facturado:</strong> " . number_format($porcentaje_pendiente, 2) . "%</p>";
                        echo "<p><strong>EV:</strong> " . $ev . "</p>";
                    echo "</div>";

                    echo "<div class='linea-gris'></div>";
                    echo "<div>";
                        $url = 'https://gestor.transimex.com.mx/CRM/formulario_cliente.php?ot=' . urlencode($row["ot"]) . '&responsable=' . urlencode($row["responsable"]);
                        echo "<p style='text-align:center'>";
                        echo "<a href=\"$url\" target=\"_blank\">Formulario de evaluación del cliente</a>";
                        echo " <button onclick=\"navigator.clipboard.writeText('$url');this.innerHTML='<span style=\\'color:green; border:none; font-weight:bold;\\'>&#10003; Copiado!</span>';setTimeout(()=>this.innerHTML='<span style=\\'vertical-align:middle;\\'>&#128203;</span> Copiar liga',1500);\" type=\"button\"><span style='vertical-align:middle;'>&#128203;</span> Copiar liga</button>";
                        echo "</p>";
                    echo "</div>";

                    echo "<div class='linea-gris'></div>";
                    echo "<div>";
                        $sql_evaluacion = "SELECT comentario FROM clientes_respuestas WHERE ot='$ot'";
                        $result_evaluacion = $conexion->query($sql_evaluacion);
                        if ($result_evaluacion && $result_evaluacion->num_rows > 0) {
                            $row_eval = $result_evaluacion->fetch_assoc();
                            if (!empty($row_eval['comentario'])) {
                                echo "<div style='width:80%;margin:20px auto;'><strong>Comentario del cliente:</strong> ";
                                echo nl2br(htmlspecialchars($row_eval['comentario'])) . "</div>";
                            }
                        }
                    echo "<div>";
                    
                    $sql_evaluacion = "SELECT * FROM clientes_respuestas WHERE ot='$ot'";
                    $result_evaluacion = $conexion->query($sql_evaluacion);
                    if ($result_evaluacion && $result_evaluacion->num_rows > 0) {
                        $row_eval = $result_evaluacion->fetch_assoc();
                        // Suponiendo que las columnas de evaluación son: calidad, tiempo, servicio, comunicacion, precio
                        $labels = ['Calidad', 'Tiempo', 'Atencion','Alcance', 'Precios'];
                        $data = [
                            (float)$row_eval['calidad'],
                            (float)$row_eval['tiempos'],
                            (float)$row_eval['atencion'],
                            (float)$row_eval['alcance'],
                            (float)$row_eval['precios']
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
                                        ticks: { stepSize: 2 }
                                    }
                                }
                            }
                        });
                        </script>
                        ";
                    } else {
                        echo "<p style='text-align:center'>No hay evaluación del cliente registrada para esta OT.</p>";
                    }
                    echo "</div>";

                echo "</div>";
                

            } else {
                echo "<p class='centrado'>No se encontraron resultados para la OT ingresada.</p>";
            }
        }
    ?>
    </section>
    
</div>
</body>
</html>