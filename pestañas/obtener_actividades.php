<?php
include '../conexion.php';

$conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

if (isset($_POST["departamento"]) && $_POST["departamento"] !== "") {
    $departamento = $_POST["departamento"];

    $sql = "SELECT 
                periodicos.id AS id_periodico, 
                actividades.actividad, 
                periodicos.objeto 
            FROM periodicos 
            LEFT JOIN actividades ON periodicos.id_act = actividades.id
            WHERE actividades.departamento = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $departamento);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $options = "<option value=''>Seleccione una actividad</option>";
    while ($fila = $resultado->fetch_assoc()) {
        $actividad = htmlspecialchars($fila["actividad"]);
        $objeto = htmlspecialchars($fila["objeto"]);
        $id = htmlspecialchars($fila["id_periodico"]);
        $options .= "<option value='$id'>$actividad - $objeto</option>";
    }
    echo $options;
} else {
    echo "<option value=''>Seleccione un departamento primero</option>";
}
?>
