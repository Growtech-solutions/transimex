<?php
include '../conexion.php'; 

$conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$search = $_GET['q'] ?? '';
$sql = "SELECT id, CONCAT(nombre, ' ', apellidos) as nombre_completo FROM trabajadores 
        WHERE id LIKE ? OR CONCAT(nombre, ' ', apellidos) LIKE ? LIMIT 10";
$stmt = $conexion->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(["error" => "Error en la consulta"]);
    exit;
}
$param = '%' . $search . '%';
$stmt->bind_param('ss', $param, $param);
$stmt->execute();
$result = $stmt->get_result();

$resultados = [];
while ($row = $result->fetch_assoc()) {
    $resultados[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre_completo']
    ];
}

$stmt->close();
$conexion->close();

header('Content-Type: application/json');
echo json_encode($resultados);
