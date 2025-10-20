<!DOCTYPE html>
<html lang="en">
<head>
    <title>Editar compras</title>
</head>
<body>
<?php
$header_loc = htmlspecialchars($_GET['header_loc']);
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    // Establecer la conexión a la base de datos
    $conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);

    // Verificar la conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Obtener los datos de la pieza
    $sql = "SELECT * FROM compras WHERE id = $id";
    $resultado = $conexion->query($sql);

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
    } else {
        echo "No se encontró la pieza.";
        exit;
    }

    $conexion->close();
} else {
    echo "ID no proporcionado.";
    exit;
}
?>
<style>
.editar_compra:nth-child(18) {
    grid-column: 2/5;
}
.editar_compra:nth-child(20) {
    grid-column: 2/5;
}
.espaciado {
    grid-column: 1/5;
    height: 40px;
    border: none;
    background-color: transparent;
}
h1 {
    padding-left: 160px;
}

.altadeproyecto__boton__enviar {
    grid-column: 3/4;
}
.label {
    display: block;
    text-align: right;
    margin-top: 8px;
}
</style>

<div class="contenedor__servicios">
    <h1>Editar Compra</h1>
    <form class="servicios__form" method="POST" action="../php/procesar_editarcompra.php?id=<?php echo $id; ?>">
        <label class="label" for="ot">OT:</label>
        <input class="entrada editar_compra" type="number" id="ot" name="ot" placeholder="OT" value="<?php echo $fila['ot']; ?>">
        <label class="label" for="req">Requisición:</label>
        <input class="entrada editar_compra" type="number" id="requisicion" name="req" placeholder="Requisicion" value="<?php echo $fila['id_oc']; ?>">
        <label class="label" for="responsable">Responsable:</label>
        <?php $selectDatosExistentes->obtenerOpcionesExistentes('listas', 'responsables', 'responsable', 'entrada editar_compra', $fila['responsable']); ?>
        <label class="label" for="precio_unitario">Precio unitario:</label>
        <input class="entrada editar_compra" type="number" step="0.0001" id="precio_unitario" name="precio_unitario" placeholder="Precio Unitario" value="<?php echo $fila['precio_unitario']; ?>">
        <label class="label" for="moneda">Moneda:</label>
        <?php $selectDatosExistentes->obtenerOpcionesExistentes('listas', 'moneda', 'moneda', 'entrada editar_compra', $fila['moneda']); ?>
        <label class="label" for="cantidad">Cantidad:</label>
        <input class="entrada editar_compra" type="number" step="0.0001" id="cantidad" name="cantidad" placeholder="Cantidad" value="<?php echo $fila['cantidad']; ?>">
        <label class="label" for="unidad">Unidad:</label>
        <?php $selectDatosExistentes->obtenerOpcionesExistentes('listas', 'unidades', 'unidad', 'entrada editar_compra', $fila['unidad']); ?>
        
        <label class="label" for="cotizacion">Cotización:</label>
        <select class="entrada editar_compra" id="cotizacion" name="cotizacion">
            <?php 
                if ($fila['cotizacion'] == null) {
                    echo '<option value="null">compra</option>';
                } else {
                    echo '<option value="' . $fila['cotizacion'] . '">' . $fila['cotizacion'] . '</option>';
                }
            ?>
            <option value="+IVA">+IVA</option>
            <option value="NETO">NETO</option>
        </select>
        <label class="label" for="comentarios">Compra:</label>
        <textarea class="entrada editar_compra" id="descripcion" name="descripcion" placeholder="Descripcion"><?php echo htmlspecialchars($fila['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        <label class="label" for="comentarios">Comentarios:</label>
        <textarea class="entrada editar_compra" id="comentarios" name="comentarios" placeholder="Comentarios"><?php echo $fila['comentarios']; ?></textarea>
        <input type="hidden" name="header_loc" value="<?php echo $header_loc; ?>">
        <input class="espaciado">
            <div class="altadeproyecto__boton__enviar">
                <input class="boton__enviar" type="submit" value="Actualizar">
            </div>
    </form>
    <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta compra?');" style="margin: 0;">
        <input type="submit"  name="eliminar_compra" value="Eliminar Compra" style="background-color: red; color: white;  border: none; border-radius: 5px; cursor: pointer;">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
    </form>
</div>
</body>
</html>




