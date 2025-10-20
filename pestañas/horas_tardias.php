<!DOCTYPE html>
<html lang="en">
<head>
    <title>Asignacion de horas</title>
</head>
<style>
    .ocultar{
        border: #fff;
        background-color: #fff;
    }
</style>
<?php $header_loc= $_GET['header_loc']; ?>
<body id="asignaciondehoras">
<div class="principal">
    <div>
        <h2 class="titulo">Asignaciones</h2>
        <?php 
            if (isset($_GET['confirmacion'])) {
                // Sanear el valor para evitar inyección de archivos
                $confirmacion = htmlspecialchars($_GET['confirmacion']);
                // Mostrar la confirmación de forma adecuada
                echo "<div class='confirmacion'>$confirmacion</div>";
            }
            echo "<br>";
        ?>
        <form class="servicios__form" action="../php/procesar_horas.php" method="POST">
            <input type="hidden" name="header_loc" value="<?php echo $header_loc; ?>">
            <input type="date" name="fecha" id="fecha" class="entrada" value="<?php echo date('Y-m-d'); ?>" required>
            <input type="text" class="ocultar desktop-only" name="" id="">
            <input type="text" class="ocultar desktop-only" name="" id="">
            <input type="text" class="ocultar desktop-only" name="" id="">
            <!-- Input fields for description, quantity, and comment -->
            <?php 
                for ($i = 1; $i <= 10; $i++):  
                $SelectTrabajadores->obtenerNombres('trabajador[]','entrada altadepieza__campo');
            ?>
                    <input class="entrada altadepieza__campo" type="text" id="ot" name="ot[]" placeholder="OT" >
                    <textarea class="entrada altadepieza__campo" name="descripcion[]" placeholder="Trabajo" ></textarea>
                    <input class="entrada altadepieza__campo" type="text" id="tiempo" name="tiempo[]" placeholder="tiempo">
            <?php 
                endfor; 
            ?>
            <div class="altadepieza__boton__enviar">
                <input class="boton__enviar" type="submit" value="Enviar">
            </div>
        </form>
    </div>
</body>
</html>







