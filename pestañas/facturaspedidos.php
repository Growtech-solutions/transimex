<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Facturas/Pedidos</title>
    <style>
        .centrado {
            padding-top: 1rem;
            text-align: center;
        }
        .formulario_reporte_ot {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        table {
            border-collapse: collapse;
            width: 95%;
            margin: auto;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .oculto {
            display: none;
        }
        .dinero {
            text-align: right;
        }
        .total {
            margin-top: 1rem;
            margin-bottom: 1rem;
        }
        /* Estilos para el modal */
        .modal {
            display: none; /* Ocultar modal por defecto */
            position: fixed; /* Posición fija */
            z-index: 1; /* Sobre todo */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; /* Habilitar el desplazamiento */
            background-color: rgb(0,0,0); /* Fondo negro */
            background-color: rgba(0,0,0,0.9); /* Fondo negro con opacidad */
        }
        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 800px;
            max-height: 80%;
        }
        .close {
            color: #fff;
            font-size: 2rem;
            font-weight: bold;
            position: absolute;
            top: 15px;
            right: 25px;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }
        .principal {
           margin-top:1.5%;
           margin-bottom:3%;
        }
        
    </style>
</head>
<?php 
$header_loc= $_GET['header_loc'];
$pestaña= $_GET['pestaña'];  
?>

<body id="facturaspedidos">
    <div class="principal">
        <section class="mensaje">
            <div class="centrado">
                
                <h2>Reporte de OT</h2>
                <form class="reporte_formulario" method="GET" action="">
                    <label for="ot">OT:</label>
                    <input class="formulario_reporte_ot" type="text" id="ot" name="ot" placeholder="Buscar por OT">

                    <input type="hidden" name="pestaña" value="facturaspedidos">

                    <label for="facturacion">Estado:</label>
                    <select class="formulario_reporte_ot" name="facturacion" id="facturacion">
                        <option>Estado facturación</option>
                        <option value="terminado">Terminado</option>
                        <option value="pendiente">Pendiente</option>
                    </select>

                    <label for="fecha_inicio">Fecha Inicio:</label>
                    <input class="formulario_reporte_ot" type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-d', strtotime('-2 years')); ?>">

                    <label for="fecha_final">Fecha Final:</label>
                    <input class="formulario_reporte_ot" type="date" id="fecha_final" name="fecha_final" value="<?php echo date('Y-m-d'); ?>">

                    <label for="responsable">Responsable:</label>
                    <select class="formulario_reporte_ot" name="responsable" id="responsable">
                        <option>Seleccione responsable</option>
                        <?php
                        $sql_responsables = "SELECT DISTINCT responsable FROM ot";
                        $result_responsables = $conexion->query($sql_responsables);

                        if ($result_responsables->num_rows > 0) {
                            while ($fila_responsable = $result_responsables->fetch_assoc()) {
                                echo '<option value="' . $fila_responsable['responsable'] . '">' . $fila_responsable['responsable'] . '</option>';
                            }
                        } else {
                            echo '<option value="">No hay responsables disponibles</option>';
                        }
                        ?>
                    </select>

                    <label for="cliente">Cliente:</label>
                    <input class="formulario_reporte_ot" type="text" id="cliente" name="cliente" placeholder="Cliente" >

                    <input type="hidden" name="header_loc" value="<?php echo $header_loc; ?>">
                    <input type="hidden" name="pestana" value="facturaspedidos">
                    <input type="submit" name="buscar" value="Buscar">
                </form>
                

            </div>
            
            <?php
            echo '<script>
                function toggleTabla(id) {
                    var tablas_ocultas = document.querySelectorAll(".tabla_oculta");
                    for (var i = 0; i < tablas_ocultas.length; i++) {
                        if (tablas_ocultas[i].id === "tabla_oculta_" + id) {
                            tablas_ocultas[i].classList.toggle("oculto");
                        } else {
                            tablas_ocultas[i].classList.add("oculto");
                        }
                    }
                }

                function mostrarImagen(imagenUrl) {
                    document.getElementById("imagen_pedido").src = imagenUrl;
                    document.getElementById("modal_imagen").style.display = "block";
                }

                function cerrarModal() {
                    document.getElementById("modal_imagen").style.display = "none";
                }

                function actualizarEstado(ot, estado) {
                    const formData = new FormData();
                    formData.append("ot", ot);
                    formData.append("estado", estado);
                
                    fetch("../php/actualizar_estado.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        console.log(data); // Opcional: muestra el resultado en la consola
                        alert("Estado actualizado correctamente");
                         window.location.href = window.location.href;
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        alert("Ocurrió un error al actualizar el estado");
                    });
                }

            </script>';
            
            // Este código debe estar incluido en tu archivo PHP para manejar la actualización
            if (isset($_POST['ot']) && isset($_POST['estado'])) {
                $ot = intval($_POST['ot']);
                $estado = $_POST['estado'];
            
                // Actualizar el estado
                $sql = "UPDATE ot SET estado = ? WHERE ot = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("si", $estado, $ot);
                if ($stmt->execute()) {
                    echo "Estado actualizado correctamente";
                } else {
                    echo "Error: " . $conexion->error;
                }
                $stmt->close();
                exit; // Finaliza el script para evitar mostrar contenido extra
            }

            $sql_ot = "SELECT * FROM (
                SELECT ot.*, 
                    (SELECT IFNULL(SUM(valor_pesos), 0) FROM pedido WHERE pedido.ot = ot.ot) AS pedidos, 
                    (SELECT IFNULL(SUM(facturas.valor_pesos), 0)
                        FROM facturas 
                        LEFT JOIN pedido ON facturas.id_pedido = pedido.id 
                        WHERE pedido.ot = ot.ot
                    ) AS facturas
                FROM ot
            ) AS subconsulta
            WHERE 1=1";
             
            // Verificar si se enviaron los datos del formulario
            if (isset($_GET['ot'])) {
                $ot = $conexion->real_escape_string($_GET['ot']);
                $sql_ot .= " AND ot LIKE '%$ot%'";
            }

            if (isset($_GET['facturacion']) && $_GET['facturacion'] !== 'Estado facturación') {
                $estado = $_GET['facturacion'];
                if ($estado == 'terminado') {
                    $sql_ot .= " AND facturas = pedidos";
                }
                if ($estado == 'pendiente') {
                    $sql_ot .= " AND facturas != pedidos";
                }
            }

            if (isset($_GET['fecha_inicio'])) {
                $fecha_inicio = $_GET['fecha_inicio'];
                $sql_ot .= " AND fecha_alta >= '$fecha_inicio'";
            }

            if (isset($_GET['fecha_final'])) {
                $fecha_final = $_GET['fecha_final'];
                $sql_ot .= " AND fecha_alta <= '$fecha_final'";
            }

            if (isset($_GET['responsable']) && $_GET['responsable'] !== 'Seleccione responsable') {
                $responsable = $conexion->real_escape_string($_GET['responsable']);
                $sql_ot .= " AND responsable = '$responsable'";
            }

            if (isset($_GET['cliente']) && $_GET['cliente'] !== 'Seleccione cliente') {
                $cliente = $conexion->real_escape_string($_GET['cliente']);
                $sql_ot .= " AND cliente LIKE '%" . $cliente . "%'";
            }

            if (isset($_GET['buscar'])) {
                $sql_ot .= " ORDER BY ot DESC";
                $result_ot = $conexion->query($sql_ot);
            

                    // Query to calculate the sum of pedidos
                    $sql_pedido_pendiente = "SELECT SUM(valor_pesos) as total_pedidos FROM pedido left join ot on pedido.ot = ot.ot WHERE ot.fecha_alta >= '$fecha_inicio' AND ot.fecha_alta <= '$fecha_final'";
                    $resultado_pedidos_pendientes = $conexion->query($sql_pedido_pendiente);
                    
                    // Query to calculate the sum of facturas
                    $sql_factura_pendiente = "SELECT SUM(facturas.valor_pesos) as total_facturas FROM facturas left join pedido on facturas.id_pedido = pedido.id left join ot on pedido.ot = ot.ot WHERE ot.fecha_alta >= '$fecha_inicio' AND ot.fecha_alta <= '$fecha_final'";
                    $resultado_facturas_pendientes = $conexion->query($sql_factura_pendiente);
                    
                    //Query para numero de proyectos, proyectos activos, pedidos por monto y pedidos por tiempo
                    $sql_total_proyectos = "SELECT COUNT(*) as total_proyectos FROM ot WHERE fecha_alta >= '$fecha_inicio' AND fecha_alta <= '$fecha_final'";
                    $resultado_total_proyectos = $conexion->query($sql_total_proyectos);
                    $total_proyectos = $resultado_total_proyectos->fetch_assoc()['total_proyectos'];

                    // Obtener el número de proyectos completamente facturados
                    $sql_proyectos_facturados = "
    SELECT COUNT(*) as total_proyectos_facturados
    FROM (
        SELECT ot.ot
        FROM ot
        LEFT JOIN pedido ON ot.ot = pedido.ot
        LEFT JOIN (
            SELECT id_pedido, SUM(valor_pesos) AS total_facturado
            FROM facturas
            GROUP BY id_pedido
        ) AS facturas_total ON pedido.id = facturas_total.id_pedido
        WHERE 
            ot.fecha_alta >= '$fecha_inicio'
            AND ot.fecha_alta <= '$fecha_final'
        GROUP BY ot.ot
        HAVING 
            IFNULL(SUM(pedido.valor_pesos), 0) > 0
            AND IFNULL(SUM(facturas_total.total_facturado), 0) = IFNULL(SUM(pedido.valor_pesos), 0)
    ) as proyectos_facturados
";
                    $resultado_proyectos_facturados = $conexion->query($sql_proyectos_facturados);
                    $total_proyectos_facturados = $resultado_proyectos_facturados->fetch_assoc()['total_proyectos_facturados'];


                    $sql_proyectos_activos = "SELECT COUNT(*) as total_proyectos FROM ot WHERE estado = 'Activo' and fecha_alta >= '$fecha_inicio' AND fecha_alta <= '$fecha_final'";
                    $resultado_proyectos_activos = $conexion->query($sql_proyectos_activos);
                    $total_proyectos_activos = $resultado_proyectos_activos->fetch_assoc()['total_proyectos'];
                    $total_proyectos_activos=$total_proyectos_activos-$total_proyectos_facturados;

                    $sql_pedidos_por_monto = "SELECT COUNT(*) as total_pedidos_monto FROM ot WHERE estado = 'Perdido por monto' and fecha_alta >= '$fecha_inicio' AND fecha_alta <= '$fecha_final'";
                    $resultado_pedidos_por_monto = $conexion->query($sql_pedidos_por_monto);
                    $total_pedidos_por_monto = $resultado_pedidos_por_monto->fetch_assoc()['total_pedidos_monto'];
                    
                    $sql_pedidos_por_tiempo = "SELECT COUNT(*) as total_pedidos_tiempo FROM ot WHERE estado = 'Perdido por tiempo' and fecha_alta >= '$fecha_inicio' AND fecha_alta <= '$fecha_final'";
                    $resultado_pedidos_por_tiempo = $conexion->query($sql_pedidos_por_tiempo);
                    $total_pedidos_por_tiempo = $resultado_pedidos_por_tiempo->fetch_assoc()['total_pedidos_tiempo'];

                    $sql_pedidos_cancelados = "SELECT COUNT(*) as total_pedidos_cancelados FROM ot WHERE estado = 'Cancelado' and fecha_alta >= '$fecha_inicio' AND fecha_alta <= '$fecha_final'";
                    $resultado_pedidos_cancelados = $conexion->query($sql_pedidos_cancelados);
                    $total_pedidos_cancelados = $resultado_pedidos_cancelados->fetch_assoc()['total_pedidos_cancelados'];

                    // Fetch the results as associative arrays
                    $total_pedidos = $resultado_pedidos_pendientes->fetch_assoc()['total_pedidos'];
                    $total_facturas = $resultado_facturas_pendientes->fetch_assoc()['total_facturas'];
                    
                    // Calculate the difference
                    $resultado_valor_futuro = $total_pedidos - $total_facturas;
                    
                    // Output the result con diseño
                    echo '<div style="display: flex; justify-content: center; gap: 2rem; margin: 1.5rem 0; flex-wrap: wrap;">
                        <div style="background: #d4edda; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 1rem 2rem; min-width: 220px; text-align: center;">
                            <div style="font-size: 1.2rem; color: #555;">Total Proyectos</div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #218838;">'. $total_proyectos. '</div>
                        </div>
                        <div style="background: #d4edda; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 1rem 2rem; min-width: 220px; text-align: center;">
                            <div style="font-size: 1.2rem; color: #555;">Proyectos Terminados</div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #218838;">'. $total_proyectos_facturados. '</div>
                        </div>
                        <div style="background: #d4edda; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 1rem 2rem; min-width: 220px; text-align: center;">
                            <div style="font-size: 1.2rem; color: #555;">Proyectos Activos</div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #218838;">' . $total_proyectos_activos . '</div>
                        </div>
                        <div style="background: #f5c6cb; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 1rem 2rem; min-width: 220px; text-align: center;">
                            <div style="font-size: 1.2rem; color: #555;">Perdidos por Monto</div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #e3342f;">' . $total_pedidos_por_monto . '</div>
                        </div>
                        <div style="background: #f5c6cb; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 1rem 2rem; min-width: 220px; text-align: center;">
                            <div style="font-size: 1.2rem; color: #555;">Perdidos por Tiempo</div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #e3342f;">' . $total_pedidos_por_tiempo . '</div>
                        </div>
                        <div style="background: #f5c6cb; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 1rem 2rem; min-width: 220px; text-align: center;">
                            <div style="font-size: 1.2rem; color: #555;">Pedidos Cancelados</div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #e3342f;">' . $total_pedidos_cancelados . '</div>
                        </div>
                    </div>';
            
            if ($result_ot && $result_ot->num_rows > 0) {
                echo "<table>";
                while ($fila_ot = $result_ot->fetch_assoc()) {
                    $compras = $fila_ot['compras'] ?? 0;
                    $ot_cmo=$fila_ot['ot'];
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
                            trabajadores ON encargado.id = trabajadores.id
                        WHERE 
                            piezas.ot = '$ot_cmo' OR encargado.ot_tardia = '$ot_cmo';";
                        
                        $result_mano_de_obra = $conexion->query($sql_mano_de_obra);
                        $costo_mano_de_obra = 0;
                        $costo_mano=0;
                        if ($result_mano_de_obra && $result_mano_de_obra->num_rows > 0) {
                            while ($row_cmo = $result_mano_de_obra->fetch_assoc()) {
                                // Obtiene los valores del query
                                $salario_actual = isset($row_cmo['salario_actual']) && is_numeric($row_cmo['salario_actual']) ? $row_cmo['salario_actual'] : 280; // Salario por día (valor predeterminado)
                                $tiempo = isset($row_cmo['tiempo']) && is_numeric($row_cmo['tiempo']) ? $row_cmo['tiempo'] : 0; // Tiempo trabajado (valor predeterminado)
       
                                $costo_mano = ($salario_actual / 8) * $tiempo;
                                $costo_mano_de_obra += $costo_mano*3;
                            }
                                
                        }else {
                            $costo_mano_de_obra = 0;
                            $costo_mano=0;
                        }

                        $pedidos_ot="SELECT SUM(valor_pesos) as total_pedidos FROM pedido WHERE ot = '$ot_cmo'";
                        $result_pedidos_ot = $conexion->query($pedidos_ot);
                        $total_pedidos_ot = $result_pedidos_ot->fetch_assoc()['total_pedidos'];

                        $facturas_ot="SELECT SUM(facturas.valor_pesos) as total_facturas FROM facturas 
                        left join pedido on facturas.id_pedido = pedido.id
                        WHERE pedido.ot = '$ot_cmo'";
                        $result_facturas_ot = $conexion->query($facturas_ot);
                        $total_facturas_ot = $result_facturas_ot->fetch_assoc()['total_facturas'];

                        $compras_ot="SELECT SUM(cantidad * 
                                    CASE 
                                        WHEN orden_compra.moneda != 'MXN' THEN compras.precio_unitario * 22 
                                        ELSE precio_unitario 
                                    END) AS total_compras 
                                    FROM compras left join orden_compra on compras.id_oc = orden_compra.id
                                    WHERE ot = '$ot_cmo'";
                        $result_compras_ot = $conexion->query($compras_ot);
                        $total_compras_ot = $result_compras_ot->fetch_assoc()['total_compras'];
                        if ($total_pedidos_ot > 0) {
                            $porcentaje_facturado = ($total_facturas_ot / $total_pedidos_ot) * 100;
                        } else {
                            $porcentaje_facturado = 0;
                        }
                    
                    echo "<tr style='background-color: #f2f2f2;'>";
                        echo "<td>" . $fila_ot['fecha_alta'] . "</td>";
                        echo "<td><a href='" . $header_loc . ".php?pestaña=evaluacion_ot&header_loc=" . urlencode($header_loc) . "&ot=" . urlencode($fila_ot['ot']) . "' target='_blank'>" . $fila_ot['ot'] . "</a></td>";
                        echo "<td>" . $fila_ot['cliente'] . "</td>";
                        echo "<td>" . $fila_ot['descripcion'] . "</td>";
                        echo "<td>" . $fila_ot['responsable'] . "</td>";
                        echo "<td><i> Cotizacion $" . number_format($fila_ot['cotizacion'], 2) . "</i></td>";
                        echo "<td> Pedidos $" . number_format($total_pedidos_ot, 2) . "</td>";
                        echo "<td> Gastos $" . number_format($total_compras_ot + $costo_mano_de_obra, 2) . "</td>";
                        echo "<td>". number_format($porcentaje_facturado, 2)."%</td>";
                        echo "<td>";
                            echo "<select
                                    name='estado' 
                                    class='entrada' 
                                    onchange='actualizarEstado(" . $fila_ot['ot'] . ", this.value)'
                                >";
                            echo "<option value='activo'" . ($fila_ot['estado'] == 'activo' ? " selected" : "") . ">Activo</option>";
                            echo "<option value='Perdido por monto'" . ($fila_ot['estado'] == 'Perdido por monto' ? " selected" : "") . ">Perdido por monto</option>";
                            echo "<option value='Perdido por tiempo'" . ($fila_ot['estado'] == 'Perdido por tiempo' ? " selected" : "") . ">Perdido por tiempo</option>";
                            echo "<option value='Cancelado'" . ($fila_ot['estado'] == 'Cancelado' ? " selected" : "") . ">Cancelado</option>";
                            echo "</select>";
                        echo "</td>";
                        echo "<td class='toggle-btn' onclick='toggleTabla(" . $fila_ot['ot'] . ")'>▼</td>";
                    echo "</tr>";

                    echo "<tr>
                            <td colspan='10'>
                                <div id='tabla_oculta_" . $fila_ot['ot'] . "' class='tabla_oculta oculto'>
                                    <table border='1'>
                                        <tr>
                                            <th>Pedido</th>
                                            <th>Valor</th>
                                        </tr>";
                    $sql_pedidos = "SELECT * FROM pedido WHERE ot = '" . $fila_ot['ot'] . "'";
                    $resultado_pedidos = $conexion->query($sql_pedidos);
                    $total_pedidos = 0;
                    while ($fila_pedidos = $resultado_pedidos->fetch_assoc()) {
                        $total_pedidos += $fila_pedidos['valor_pesos'];
                        echo "<tr>";
                            echo "<td><a href='../documentos/finanzas/ot/{$fila_ot['ot']}/{$fila_pedidos['descripcion']}.pdf' target='_blank'>" . $fila_pedidos['descripcion'] . "</a></td>";
                            echo "<td class='dinero'>$" . number_format($fila_pedidos['valor_pesos'], 2) . "</td>";
                        echo "</tr>";
                    }

                    echo "</table>";
                    echo "<div class='dinero total'><strong>Total Pedidos: </strong>$" . number_format($total_pedidos, 2) . "</div>";

                    echo "<table border='1'>
                            <tr>
                                <th>Factura</th>
                                <th>Descripción</th>
                                <th>Pedido</th>
                                <th>Valor</th>
                            </tr>";
                    $sql_facturas = "SELECT 
                                        facturas.id as factura_id,
                                        facturas.descripcion as descripcion,
                                        facturas.folio as factura_folio,
                                        facturas.valor_pesos as factura_valor,
                                        facturas.id_pedido as pedido_id,
                                        pedido.ot as ot,
                                        pedido.descripcion as pedido
                                    FROM 
                                        facturas
                                    LEFT JOIN 
                                        pedido ON facturas.id_pedido = pedido.id
                                    WHERE pedido.ot = '" . $fila_ot['ot'] . "'";
                    $resultado_facturas = $conexion->query($sql_facturas);
                    $total_facturas = 0;
                    while ($fila_facturas = $resultado_facturas->fetch_assoc()) {
                        $total_facturas += $fila_facturas['factura_valor'];
                        echo "<tr>";
                        echo "<td><a href='../documentos/finanzas/ot/{$fila_ot['ot']}/{$fila_facturas['factura_folio']}.zip' target='_blank'>" . $fila_facturas['factura_folio'] . "</a></td>";
                        echo "<td>" . $fila_facturas['descripcion'] . "</td>";
                        echo "<td>" . $fila_facturas['pedido'] . "</td>";
                        echo "<td class='dinero'>$" . number_format($fila_facturas['factura_valor'], 2) . "</td>";
                        echo "</tr>";
                    }

                   
                    echo "</table>";
                    echo "<div class='dinero total'><strong>Total Facturas: </strong>$" . number_format($total_facturas, 2) . "</div>";
                    echo "<script>document.getElementById('porcentaje_facturado_" . $fila_ot['ot'] . "').innerHTML = 'Facturacion: " . number_format($porcentaje_facturado, 2) . "%';</script>";
                    echo "</div>
                            </td>
                        </tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No se encontraron resultados para los criterios de búsqueda proporcionados.</p>";
            }
        }
        ?>
            
        </section>
    </div>

    <!-- Modal para mostrar la imagen -->
    <div id="modal_imagen" class="modal">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <img class="modal-content" id="imagen_pedido">
    </div>
</body>
</html>
