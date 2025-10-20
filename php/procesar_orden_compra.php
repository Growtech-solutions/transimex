<?php
include '../conexion.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($id) {
    // Conexión a la base de datos
    $conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);

    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $header_loc = $_POST['header_loc'] ?? null;
        $responsable = $_POST['responsable'] ?? null;
        $oc = $_POST['oc'] ?? null;
        $tipo_de_cambio = isset($_POST['tipo_de_cambio']) ? (float) $_POST['tipo_de_cambio'] : 0.00;
        $fecha_llegada = $_POST['fecha_llegada'] ?? null;
        $moneda = $_POST['moneda'] ?? null;
        $proveedor = $_POST['proveedor'] ?? null;
        $cotizacion = $_POST['cotizacion'] ?? null;
        $observaciones = $_POST['observaciones'] ?? null;
        $tipo_pago = $_POST['tipo_pago'] ?? null;
        $pago_estimado = $_POST['pago_estimado'] ?? null;
        $factura = isset($_POST['factura']) ? $_POST['factura'] : 0;



        // Consulta preparada
        $sql_update = "UPDATE orden_compra 
                       SET oc = ?, tipo_cambio = ?, responsable = ?, llegada_estimada = ?, proveedor = ?, moneda = ?, cotizacion = ?, observaciones = ?, tipo_pago = ?, pago_estimado = ?, factura = ?
                       WHERE id = ?";

        $stmt = $conexion->prepare($sql_update);
        if ($stmt) {
            $stmt->bind_param("sdsssssssssi", $oc, $tipo_de_cambio, $responsable, $fecha_llegada, $proveedor, $moneda, $cotizacion,$observaciones,$tipo_pago,$pago_estimado, $factura, $id);
            $sql_call_proc = "CALL calculate_and_update_totals($id)";
            if ($stmt->execute()) {
                if ($conexion->query($sql_call_proc) === TRUE) {
                header("Location: ../header_main_aside/$header_loc.php?pestaña=orden_compras");
                exit;
                } else {
                    echo "Error al llamar al procedimiento almacenado: " . $conexion->error;
                }
            } else {
                echo "Error al actualizar los datos: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $conexion->error;
        }
    }

    $conexion->close();
} else {
    echo "ID no proporcionado.";
    exit;
}
?>
