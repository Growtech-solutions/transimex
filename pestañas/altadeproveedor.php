<!DOCTYPE html>
<html lang="en">
<body id="altadeproveedor">
    <div class="principal" >
        <div>
        <h2 class="titulo">Alta de proveedor</h2>
        <form class="servicios__form" action="../php/procesar_alta_proveedor.php" method="POST">
            <input class="entrada altadeproyecto__campo" type="text" id="proveedor" name="proveedor" placeholder="Nombre" required>
            <input class="entrada altadeproyecto__campo" type="text" id="direccion" name="direccion" placeholder="Direccion">
            <input class="entrada altadeproyecto__campo" type="text" id="telefono" name="telefono" placeholder="telefono">
            <input class="entrada altadeproyecto__campo" type="text" id="correo" name="correo" placeholder="correo">
            <input type="hidden" name="header_loc" value="<?php echo $header_loc; ?>">
            <div class="altadeproyecto__boton__enviar">
                <input class="boton__enviar" type="submit" value="Enviar">
            </div>
        </form>
        <?php
        // Verifica si el parámetro 'confirmacion' está presente en la URL
        if (isset($_GET['confirmacion'])) {
            // Sanear el valor para evitar inyección de archivos
            $confirmacion = htmlspecialchars($_GET['confirmacion']);
            // Mostrar la confirmación de forma adecuada
            echo "<div class='confirmacion'>$confirmacion</div>";
        }
        ?>
        </div>
    </div>
</body>
</html>