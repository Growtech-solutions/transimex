<!DOCTYPE html>
<html lang="es">
<?php
// Consulta para obtener el siguiente valor de OT
$query = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$base_de_datos' AND TABLE_NAME = 'ot'";
$result = mysqli_query($conexion, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $siguiente_ot = $row['AUTO_INCREMENT'];
} else {
    // Manejo de error en caso de que la consulta falle
    echo "Error en la consulta: " . mysqli_error($conexion);
    $siguiente_ot = ''; // O establece un valor por defecto
}
$header_loc = htmlspecialchars($_GET['header_loc']);
?>
<style>
    @media (min-width: 768px) {
/* Alta de proyecto */
.entrada:nth-child(2) {
    grid-column: 2/5;
}
.entrada:nth-child(3) {
    grid-column: 1/3;
}
.entrada:nth-child(4) {
    grid-column: 3/5;
}
.entrada:nth-child(5) {
    grid-column: 1/5;
}
.altadeproyecto__boton__enviar {
    grid-column: 4;
}
    }

</style>
<body id="altadeproyecto">
    <div class="principal">
        <div>
        <h2 class="titulo">Alta de proyecto</h2>
         <?php
        // Verifica si el parámetro 'confirmacion' está presente en la URL
        if (isset($_GET['confirmacion'])) {
            // Sanear el valor para evitar inyección de archivos
            $confirmacion = htmlspecialchars($_GET['confirmacion']);
            // Mostrar la confirmación de forma adecuada
            echo "<div class='confirmacion'>$confirmacion</div>";
        }
        ?>
        <br>
        <form class="servicios__form" action="../php/procesar_alta_proyecto.php" method="POST">
            <input class="entrada" type="text" id="ot" name="ot" placeholder="OT" value="<?php echo $siguiente_ot; ?>" required readonly>
            
            <input class="entrada" type="text" id="nombreDelProyecto" name="descripcion" placeholder="Nombre del proyecto" required>
            
            <?php 
                $selectDatos->obtenerOpciones('cliente', 'razon_social', 'cliente', 'entrada'); 
                $selectDatos->obtenerOpciones('listas', 'planta', 'planta', 'entrada'); 
                $selectDatos->obtenerOpciones('listas', 'responsables', 'responsable', 'entrada'); 
            ?>

            <input type="hidden" name="header_loc" value="<?php echo $header_loc; ?>">

            <div class="altadeproyecto__boton__enviar">
                <input class="boton__enviar" type="submit" value="Enviar">
            </div>
        </form>
        </div>
    </div>
</body>
</html>
