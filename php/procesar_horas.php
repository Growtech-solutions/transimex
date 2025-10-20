<?php
// Incluir el archivo de conexión a la base de datos
include '../conexion.php'; 
$conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$header_loc= $_GET['header_loc']; 
// Verificar si se enviaron datos mediante el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Verificar si se enviaron todas las variables necesarias
    if (isset($_POST['fecha']) && isset($_POST['ot']) && isset($_POST['trabajador']) && isset($_POST['descripcion']) && isset($_POST['tiempo'])) {

        // Recuperar los datos enviados por el formulario
        $ot = $_POST['ot'];
        $nombreArray = $_POST['trabajador'];
        $piezaextraArray = $_POST['descripcion'];
        $tiempoArray = $_POST['tiempo'];
        $fecha = $_POST['fecha'];
        $header_loc = $_POST['header_loc'];

        // Verificar si hay piezas para procesar
        if (!empty($nombreArray) && !empty($piezaextraArray) && !empty($tiempoArray)) {

            // Procesar cada pieza
            for ($i = 0; $i < count($nombreArray); $i++) {
                // Verificar si al menos uno de los campos de la pieza no está vacío
                if (!empty($nombreArray[$i]) || !empty($piezaextraArray[$i]) || !empty($ot[$i])) {

                    // Insertar la pieza en la base de datos
                    $query = "INSERT INTO encargado (ot_tardia, id_trabajador, fecha, pieza_tardia, tiempo) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conexion->prepare($query);

                    // Verificar si la preparación de la consulta ha tenido éxito
                    if ($stmt === false) {
                        echo "Error al preparar la consulta: " . $conexion->error . "<br>";
                        echo "Consulta: " . $query . "<br>";
                        continue; // Saltar a la siguiente iteración del bucle
                    }

                    $idPieza = !empty($ot[$i]) ? $ot[$i] : 0;
                    $nombre = !empty($nombreArray[$i]) ? $nombreArray[$i] : "";
                    $piezaextra = !empty($piezaextraArray[$i]) ? $piezaextraArray[$i] : "";
                    $tiempo = !empty($tiempoArray[$i]) ? $tiempoArray[$i] : 0;

                    $stmt->bind_param("iisss", $idPieza, $nombre, $fecha, $piezaextra, $tiempo);
                    
                    // Ejecutar la consulta y verificar el resultado
                    if ($stmt->execute()) {
                        echo "Registro insertado correctamente para el trabajador: $nombre";
                    } else {
                        echo "Error al insertar el registro para el trabajador: $nombre. Error: " . $stmt->error ;
                    }

                    $stmt->close();
                }
            }
            // Redirigir a asignaciondehoras.php después de procesar
            $confirmacion = "Registros insertados correctamente.";
            header("Location: " . $_SERVER['HTTP_REFERER'] . "&confirmacion=" . urlencode($confirmacion));
            exit;
        } else {
            $confirmacion = "No se han especificado todas las piezas para procesar.<br>";
        }
    } else {
        $confirmacion = "Error: Todos los campos son obligatorios.<br>";
    }
} else {
    $confirmacion = "Error: Método de solicitud incorrecto.<br>";
}
header("Location: " . $_SERVER['HTTP_REFERER'] . "&confirmacion=" . urlencode($confirmacion));
exit;

// Cerrar la conexión a la base de datos
$conexion->close();
?>





