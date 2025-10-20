<?php
// Verificar si se han enviado datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Configurar la conexión a la base de datos
    include '../conexion.php'; 

    // Crear una conexión a la base de datos
    $conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);

    // Verificar la conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $curp = $_POST['CURP'];
    $rfc = $_POST['RFC'];
    $imss = $_POST['IMSS'];
    $cp = $_POST['CP'];
    $cla = $_POST['estado'];
    $fecha_nacimiento = $_POST['fechanacimiento'];
    $fecha_ingreso = $_POST['fechaingreso'];
    $puesto = $_POST['puesto'];
    $empresa = $_POST['empresa'];
    $tarjeta = $_POST['tarjeta'];
    $salario = $_POST['salario'];
    $forma_pago = $_POST['forma_pago'];
    $tipo_contrato = $_POST['tipo_contrato'];
    $header_loc = $_POST['header_loc'];
    $area = $_POST['area'];
    $estado = 1;  // Asumiendo que el estado predeterminado es 1

    // Preparar la consulta SQL para insertar los datos en la tabla 
    $sql = "INSERT INTO trabajadores (nombre, apellidos, curp, rfc, nss, codigo_postal, clave_entidad_fed, fecha_nacimiento, fecha_ingreso, puesto, empresa, clave_bancaria, salario, estado, contrato, area, forma_de_pago) 
            VALUES ('$nombre', '$apellido', '$curp', '$rfc', '$imss', '$cp', '$cla', '$fecha_nacimiento', '$fecha_ingreso', '$puesto', '$empresa', '$tarjeta', '$salario', '$estado', '$tipo_contrato','$area', '$forma_pago')"; 

    // Ejecutar la consulta SQL
    if ($conexion->query($sql) === TRUE) {
        $id = $conexion->insert_id;  // Obtener el ID del registro insertado
        // Crear una carpeta con el nombre del trabajador dentro de la carpeta "documentos"
        $carpeta_trabajador = "../documentos/RecursosHumanos/trabajadores/$nombre'_'$apellido'_'$id/";
        if (!file_exists($carpeta_trabajador)) {
            mkdir($carpeta_trabajador, 0777, true);
        }

        // Procesar la carga de archivos
        $nombres_documentos = array("curp_file", "rfc_file", "nss_file", "perfil"); // Nombres de los campos de archivos

        // Si se ha subido el archivo 'perfil', guardarlo también en fotos_trabajadores
        if (isset($_FILES['perfil']) && $_FILES['perfil']['error'] == UPLOAD_ERR_OK) {
            $ruta_foto = "../documentos/RecursosHumanos/fotos_trabajadores/{$id}.png";
            move_uploaded_file($_FILES['perfil']['tmp_name'], $ruta_foto);
        }

        foreach ($nombres_documentos as $nombre_documento) {
            // Verificar si se ha subido un archivo para este campo
            if ($_FILES[$nombre_documento]['error'] == UPLOAD_ERR_OK) {
                // Mover el archivo a la carpeta del trabajador
                $nombre_archivo = $_FILES[$nombre_documento]['name'];
                $ruta_temporal = $_FILES[$nombre_documento]['tmp_name'];
                $ruta_destino = $carpeta_trabajador . $nombre_archivo;
                move_uploaded_file($ruta_temporal, $ruta_destino);
            } else {
                $confirmacion = "Error al subir el archivo $nombre_documento.";
                header("Location: " . $_SERVER['HTTP_REFERER'] . "&confirmacion=" . urlencode($confirmacion));
                exit();
            }
        }
        $confirmacion = "Registro insertado correctamente.";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "&confirmacion=" . urlencode($confirmacion));
        exit();
    } else {
        $confirmacion = "Error al insertar el registro.";
        header("Location: " . $_SERVER['HTTP_REFERER'] . "&confirmacion=" . urlencode($confirmacion));
        exit();
    }

    // Cerrar la conexión a la base de datos
    $conexion->close();
} else {
    $confirmacion = "Error al procesar el formulario.";
    header("Location: " . $_SERVER['HTTP_REFERER'] . "&confirmacion=" . urlencode($confirmacion));
    exit();
}
?>
