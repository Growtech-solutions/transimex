<?php
if (isset($_POST['registrar_nomina'])) {
    $semana = $_POST['semana'];
    $fecha_inicial = $_POST['fecha_inicial'];
    $fecha_final = $_POST['fecha_final'];
    $percepcion_empresa = $_POST['percepcion_empresa'];

    $stmt = $conexion->prepare("INSERT INTO nomina (semana, fecha_inicial, fecha_final, percepcion_empresa) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $semana, $fecha_inicial, $fecha_final, $percepcion_empresa);

    if ($stmt->execute()) {
        echo "<script>alert('Nómina registrada correctamente'); window.location.href=window.location.href;</script>";
    } else {
        echo "<script>alert('Error al registrar');</script>";
    }

    $stmt->close();
}

// Filtros de fecha
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Paginación
$registros_por_pagina = 30;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Conteo total con filtro
$condiciones = "WHERE 1=1";
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $condiciones .= " AND fecha_inicial BETWEEN '$fecha_inicio' AND '$fecha_fin'";
}

$total_resultado = $conexion->query("SELECT COUNT(DISTINCT fecha_inicial, fecha_final) as total FROM nomina $condiciones");
$total_filas = $total_resultado->fetch_assoc()['total'];
$total_paginas = ceil($total_filas / $registros_por_pagina);

// Consulta con LIMIT y filtros
$nomina = "
    SELECT semana, fecha_inicial, fecha_final, SUM(percepcion_empresa) AS total_percepcion
    FROM nomina
    $condiciones
    GROUP BY fecha_inicial, fecha_final, semana
    ORDER BY fecha_inicial DESC
    LIMIT $offset, $registros_por_pagina
";
$result_nomina = $conexion->query($nomina);
?>

<style>
    h2 {
        text-align: center;
    }
    .boton-agregar {
        background-color: #0d6efd;
        color: white;
        padding: 10px 15px;
        margin: 10px auto;
        display: block;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }
    .boton-agregar:hover {
        background-color: #084298;
    }
    .modal-form input,
    .modal-form button {
        width: 90%;
    }
    .paginacion {
        text-align: center;
        margin-top: 15px;
    }
    .paginacion a,
    .paginacion span {
        margin: 0 5px;
        text-decoration: none;
        padding: 5px 10px;
        background: #eee;
        border-radius: 5px;
    }
    .paginacion span {
        background: #0d6efd;
        color: white;
    }
</style>

<div class="principal">
    <div>
    <h2>Resumen por Semana</h2>

    <!-- Filtro de fechas -->
    <form method="GET" style="text-align:center; margin-bottom:20px;">
        <label>Desde:</label>
        <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
        <label>Hasta:</label>
        <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
        <input type="hidden" name="pestaña" value="registro_de_nomina">
        <button type="submit">Filtrar</button>
    </form>

    <button class="boton-agregar" onclick="document.getElementById('modal_nomina').style.display='flex'">+ Agregar Nómina</button>

    <table border='1' style="width: 90%; margin: 0 auto; text-align:center;">
        <tr>
            <th>Fecha Inicial</th>
            <th>Fecha Final</th>
            <th>Semana</th>
            <th>Total Percepción</th>
        </tr>
        <?php
        while ($row = $result_nomina->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['fecha_inicial']) . "</td>
                    <td>" . htmlspecialchars($row['fecha_final']) . "</td>
                    <td>" . htmlspecialchars($row['semana']) . "</td>
                    <td>$" . number_format($row['total_percepcion'], 2) . "</td>
                  </tr>";
        }
        ?>
    </table>

    <!-- Paginación -->
    <div class="paginacion">
        <?php
        $url_base = "?pestaña=registro_de_nomina&";
        if ($fecha_inicio) $url_base .= "fecha_inicio=$fecha_inicio&";
        if ($fecha_fin) $url_base .= "fecha_fin=$fecha_fin&";

        if ($pagina_actual > 1) {
            echo "<a href='{$url_base}pagina=1'>Primera</a>";
            echo "<a href='{$url_base}pagina=" . ($pagina_actual - 1) . "'>Anterior</a>";
        }

        for ($i = 1; $i <= $total_paginas; $i++) {
            if ($i == $pagina_actual) {
                echo "<span>$i</span>";
            } else {
                echo "<a href='{$url_base}pagina=$i'>$i</a>";
            }
        }

        if ($pagina_actual < $total_paginas) {
            echo "<a href='{$url_base}pagina=" . ($pagina_actual + 1) . "'>Siguiente</a>";
            echo "<a href='{$url_base}pagina=$total_paginas'>Última</a>";
        }
        ?>
    </div>
</div>

<!-- Modal -->
<div id="modal_nomina" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
    background:rgba(0,0,0,0.6); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:10px; width:90%; max-width:400px;" class="modal-form">
        <h3 style="text-align:center;">Agregar Nómina</h3>
        <form method="POST" action="">
            <label>Semana:</label>
            <input type="number" name="semana" required><br><br>
            <label>Fecha Inicial:</label>
            <input type="date" name="fecha_inicial" required><br><br>
            <label>Fecha Final:</label>
            <input type="date" name="fecha_final" required><br><br>
            <label>Percepción Empresa:</label>
            <input type="number" step="0.01" name="percepcion_empresa" required><br><br>
            <button type="submit" name="registrar_nomina" style="background:#198754; color:white; border:none; padding:10px;">Guardar</button>
            <button type="button" onclick="document.getElementById('modal_nomina').style.display='none'" style="margin-top:10px;">Cancelar</button>
        </form>
    </div>
    </div>
</div>
