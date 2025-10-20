<?php
include '../../conexion.php';
$conexion = mysqli_connect($host, $usuario, $contrasena, $base_de_datos);
date_default_timezone_set('America/Monterrey');

// Verificar la conexión
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si los parámetros están definidos
    if (isset($_POST['image']) && isset($_POST['latitude']) && isset($_POST['longitude'])) {
        // Obtener los datos enviados por el formulario (imagen, latitud, longitud)
        $image = $_POST['image'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $timestamp = date('Y-m-d H:i:s');

        // Decodificar imagen base64 y guardarla en un archivo
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
        $temp_file = '/tmp/reconocimiento_' . uniqid() . '.png';
        file_put_contents($temp_file, $data);

        // Ejecutar el script Python con la ruta del archivo
        // Decodificar imagen base64 y guardarla en archivo temporal
$data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
$image_base64 = base64_encode($data); // para enviar al microservicio

// Enviar la imagen al microservicio Flask
$payload = json_encode(['image' => 'data:image/png;base64,' . $image_base64]);
$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json",
        'content' => $payload
    ]
];
$context = stream_context_create($options);
$response = file_get_contents('http://127.0.0.1:5000/reconocer', false, $context);
$resultado = json_decode($response, true);

// Revisar si hubo error en la respuesta
if (!$resultado || !isset($resultado['id'])) {
    error_log("Error en microservicio: " . $response);
    $worker_id = null;
} else {
    $worker_id = trim($resultado['id']);
}

error_log("ID del trabajador: " . $worker_id);

        unlink($temp_file); // Borrar después de usar

        // Depuración: Verificar el valor de $worker_id
        error_log("ID del trabajador: " . $worker_id);  // Log en el archivo de errores

        // Preparar la consulta SQL para obtener las ubicaciones dentro de un rango de 2 km
        $sql = "SELECT
            id,
            nombre,
            latitud,
            longitud,
            (6371000 * acos(
                cos(radians($latitude)) * cos(radians(latitud)) * cos(radians(longitud) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitud))
            )) AS distancia_m
        FROM ubicaciones
        HAVING distancia_m <= 100;";

        // Ejecutar la consulta
        $resultado = $conexion->query($sql);

        // Verificar los resultados
        if ($resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {
                $ubicacion_nombre = $row["nombre"];
            }
        } else {
            $ubicacion_nombre = "Fuera de planta";
        }

        // Guardar la asistencia en la base de datos
        $stmt = $conexion->prepare("INSERT INTO asistencia (latitud, longitud, fecha, trabajador_id, ubicacion) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ddsss", $latitude, $longitude, $timestamp, $worker_id, $ubicacion_nombre);
        $stmt->execute();

        // Consulta para obtener el nombre y apellido del trabajador
        $sql_nombre = "SELECT nombre, apellidos FROM trabajadores WHERE id = $worker_id";
        $resultado = $conexion->query($sql_nombre);

        // Si el trabajador existe, obtenemos el nombre y apellidos
        if ($resultado->num_rows > 0) {
            $trabajador = $resultado->fetch_assoc();
            $nombre = $trabajador['nombre'];
            $apellidos = $trabajador['apellidos'];
        } else {
            $nombre = "No identificado";
            $apellidos = "";
        }

        echo json_encode([
            'worker_id' => $worker_id,
            'worker_name' => $nombre . ' ' . $apellidos,
            'ubicacion' => $ubicacion_nombre
        ]);

        $stmt->close();
        $conexion->close();
    } else {
        echo json_encode(['error' => 'Faltan datos necesarios']);
    }
}
?>
