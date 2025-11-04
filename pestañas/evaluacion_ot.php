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
                        // Botón para abrir el modal
                        echo "<h2 class='text-color'>";
                        echo "<a href='#' id='editOtBtn' style='text-decoration:none;color:inherit;display:flex;align-items:center;justify-content:center;gap:10px;' title='Editar OT'>";
                        echo htmlspecialchars($row["ot"]);
                        echo "<span style='font-size:0.7em;opacity:0.7;'>✏️</span>";
                        echo "</a>";
                        echo "</h2>";

                        // Modal HTML mejorado
                        echo '
                        <div id="editOtModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;overflow:auto;background:rgba(0,0,0,0.5);backdrop-filter:blur(5px);">
                            <div style="background:#fff;margin:5% auto;padding:0;border-radius:15px;max-width:600px;box-shadow:0 10px 30px rgba(0,0,0,0.3);position:relative;animation:modalSlideIn 0.3s ease-out;">
                                <!-- Header del modal -->
                                <div style="background:#1d143e;color:#fff;padding:20px;border-radius:15px 15px 0 0;position:relative;">
                                    <h3 style="margin:0;font-size:22px;">Editar OT: ' . htmlspecialchars($row["ot"]) . '</h3>
                                    <span id="closeModal" style="position:absolute;top:15px;right:20px;font-size:28px;cursor:pointer;color:#fff;opacity:0.7;transition:opacity 0.3s;">&times;</span>
                                </div>
                                
                                <!-- Contenido del modal -->
                                <div style="padding:25px;">
                                    <form method="POST" action="">
                                        <input type="hidden" name="edit_ot" value="1">
                                        <input type="hidden" name="ot" value="' . htmlspecialchars($row["ot"]) . '">
                                        
                                        <div style="margin-bottom:20px;">
                                            <label for="descripcion" style="display:block;margin-bottom:8px;font-weight:bold;color:#333;">Descripción:</label>
                                            <textarea id="descripcion" name="descripcion" rows="3" style="width:100%;padding:12px;border:2px solid #e0e0e0;border-radius:8px;font-size:14px;resize:vertical;transition:border-color 0.3s;box-sizing:border-box;" placeholder="Ingrese la descripción de la OT">' . htmlspecialchars($row["descripcion"]) . '</textarea>
                                        </div>
                                        
                                        <div style="margin-bottom:20px;">
                                            <label for="planta" style="display:block;margin-bottom:8px;font-weight:bold;color:#333;">Planta:</label>';
                                            $selectDatosExistentes->obtenerOpcionesExistentes('listas', 'planta', 'planta', 'form-control', $row["planta"]);
                        echo '          </div>
                                        
                                        <div style="margin-bottom:20px;">
                                            <label for="cliente" style="display:block;margin-bottom:8px;font-weight:bold;color:#333;">Cliente:</label>';
                                            $selectDatosExistentes->obtenerOpcionesExistentes('cliente', 'razon_social', 'cliente', 'form-control', $row["cliente"]);
                        echo '          </div>
                                        
                                        <div style="margin-bottom:20px;">
                                            <label for="responsable" style="display:block;margin-bottom:8px;font-weight:bold;color:#333;">Responsable:</label>';
                                            $selectDatosExistentes->obtenerOpcionesExistentes('listas', 'responsables', 'responsable', 'form-control', $row["responsable"]);
                        echo '          </div>
                                        
                                        <div style="margin-bottom:25px;">
                                            <label for="cotizacion" style="display:block;margin-bottom:8px;font-weight:bold;color:#333;">Cotización:</label>
                                            <input type="number" id="cotizacion" name="cotizacion" step="0.01" value="' . htmlspecialchars($row["cotizacion"] ?? 0.00) . '" style="width:100%;padding:12px;border:2px solid #e0e0e0;border-radius:8px;font-size:14px;transition:border-color 0.3s;box-sizing:border-box;" placeholder="0.00">
                                        </div>
                                        
                                        <div style="display:flex;gap:10px;justify-content:flex-end;">
                                            <button type="button" id="cancelBtn" style="background:#6c757d;color:#fff;padding:12px 25px;border:none;border-radius:8px;cursor:pointer;font-size:14px;transition:background-color 0.3s;">Cancelar</button>
                                            <button type="submit" style="background:#1d143e;color:#fff;padding:12px 25px;border:none;border-radius:8px;cursor:pointer;font-size:14px;transition:background-color 0.3s;">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>';

                        // CSS para animaciones y estilos mejorados
                        echo '
                        <style>
                            @keyframes modalSlideIn {
                                from { transform: translateY(-50px); opacity: 0; }
                                to { transform: translateY(0); opacity: 1; }
                            }
                            
                            #editOtModal input:focus, 
                            #editOtModal textarea:focus,
                            #editOtModal select:focus {
                                border-color: #1d143e !important;
                                outline: none;
                                box-shadow: 0 0 0 3px rgba(29, 20, 62, 0.1);
                            }
                            
                            #editOtModal button:hover {
                                transform: translateY(-1px);
                                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                            }
                            
                            #closeModal:hover {
                                opacity: 1 !important;
                                transform: rotate(90deg);
                            }
                            
                            .form-control {
                                width: 100% !important;
                                padding: 12px !important;
                                border: 2px solid #e0e0e0 !important;
                                border-radius: 8px !important;
                                font-size: 14px !important;
                                transition: border-color 0.3s !important;
                                box-sizing: border-box !important;
                            }
                        </style>';

                        // JavaScript mejorado
                        echo '
                        <script>
                            document.getElementById("editOtBtn").onclick = function(e) {
                                e.preventDefault();
                                document.getElementById("editOtModal").style.display = "block";
                                document.body.style.overflow = "hidden";
                            };
                            
                            function closeModal() {
                                document.getElementById("editOtModal").style.display = "none";
                                document.body.style.overflow = "auto";
                            }
                            
                            document.getElementById("closeModal").onclick = closeModal;
                            document.getElementById("cancelBtn").onclick = closeModal;
                            
                            window.onclick = function(event) {
                                var modal = document.getElementById("editOtModal");
                                if (event.target == modal) {
                                    closeModal();
                                }
                            };
                            
                            // Cerrar modal con tecla ESC
                            document.addEventListener("keydown", function(event) {
                                if (event.key === "Escape") {
                                    closeModal();
                                }
                            });
                        </script>';

                        // Procesar edición si se envió el formulario
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_ot']) && $_POST['edit_ot'] == '1') {
                            $ot_edit = $conexion->real_escape_string($_POST['ot']);
                            $desc_edit = $conexion->real_escape_string($_POST['descripcion']);
                            $planta_edit = $conexion->real_escape_string($_POST['planta']);
                            $cliente_edit = $conexion->real_escape_string($_POST['cliente']);
                            $resp_edit = $conexion->real_escape_string($_POST['responsable']);
                            $cotizacion_edit = $conexion->real_escape_string($_POST['cotizacion'] ?? '0.00');
                            
                            $update_sql = "UPDATE ot SET descripcion='$desc_edit', planta='$planta_edit', cliente='$cliente_edit', responsable='$resp_edit', cotizacion='$cotizacion_edit' WHERE ot='$ot_edit'";
                            
                            if ($conexion->query($update_sql)) {
                                echo "<script>setTimeout(() => window.location.href=window.location.href, 1500);</script>";
                            } 
                        }
                    echo "</div>";
                    echo "<div class='linea-gris'></div>";

                    echo "<div class='detalles'>";
                        echo "<p><strong>Cliente:</strong> " . $row["cliente"] . "</p>";
                        echo "<p><strong>Alta:</strong> " . $row["fecha_alta"] . "</p>";
                        echo "<p><strong>Cotizacion:</strong> " . $row["cotizacion"] . "</p>";
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