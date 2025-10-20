<?php
// Conexión (ajusta tus datos)
include '../conexion.php';
$conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Verifica si la conexión fue exitosa
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}


// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ot = $_POST['ot'];
    $responsable = $_POST['responsable'];
    $supervisor = (int)$_POST['supervisor'];
    $producto = (int)$_POST['producto'];
    $tiempos = (int)$_POST['tiempos'];
    $alcance = (int)$_POST['alcance'];
    $precios = (int)$_POST['precios'];
    $comentario = $conexion->real_escape_string($_POST['comentario']);

    $sql = "INSERT INTO clientes_respuestas (ot, responsable, atencion, calidad, tiempos,precios,alcance, comentario)
            VALUES ('$ot', '$responsable', $supervisor, $producto, $tiempos, $precios, $alcance, '$comentario')";

    if ($conexion->query($sql) === TRUE) {
        $confirmacion = "¡Datos guardados exitosamente!";
    } else {
        $confirmacion = "Error al guardar los datos: " . $conexion->error;
    }
}

// Obtener datos de URL
$ot = isset($_GET['ot']) ? htmlspecialchars($_GET['ot']) : '';
$responsable = isset($_GET['responsable']) ? htmlspecialchars($_GET['responsable']) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario Cliente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            padding: 30px;
        }
        .formulario {
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
            max-width: 400px;
            width: 100%;
        }
        .formulario h2 {
            margin-bottom: 20px;
        }
        .formulario label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        .formulario input[type="number"],
        .formulario textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 5px;
        }
        .formulario button {
            margin-top: 20px;
            background: #4CAF50;
            color: #fff;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 4px;
            cursor: pointer;
        }
        .formulario button:hover {
            background: #45a049;
        }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>

<body>
    <form class="formulario" method="POST">
        <h2>Responde las preguntas</h2>

        <input type="hidden" name="ot" value="<?php echo $ot; ?>">
        <input type="hidden" name="responsable" value="<?php echo $responsable; ?>">

        <p>Dando una calificación del 1 al 10 siendo 10 lo mejor</p>

        <div style="margin-bottom: 15px;">
            <label for="supervisor">¿Cómo calificaría la atención de nuestro supervisor?</label>
            <div style="display: flex; gap: 8px; margin-top: 5px;">
            <?php for ($i = 1; $i <= 10; $i++): ?>
            <label style="display: flex; flex-direction: column; align-items: center;">
                <input type="radio" name="supervisor" value="<?php echo $i; ?>" required>
                <?php echo $i; ?>
            </label>
            <?php endfor; ?>
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="producto">¿Cómo calificaría la calidad de nuestro producto?</label>
            <div style="display: flex; gap: 8px; margin-top: 5px;">
            <?php for ($i = 1; $i <= 10; $i++): ?>
            <label style="display: flex; flex-direction: column; align-items: center;">
                <input type="radio" name="producto" value="<?php echo $i; ?>" required>
                <?php echo $i; ?>
            </label>
            <?php endfor; ?>
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="tiempos">¿Cómo calificaría nuestro tiempo de entrega?</label>
            <div style="display: flex; gap: 8px; margin-top: 5px;">
            <?php for ($i = 1; $i <= 10; $i++): ?>
            <label style="display: flex; flex-direction: column; align-items: center;">
                <input type="radio" name="tiempos" value="<?php echo $i; ?>" required>
                <?php echo $i; ?>
            </label>
            <?php endfor; ?>
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="alcance">¿Cómo calificaría el cumplimiento a lo solicitado?</label>
            <div style="display: flex; gap: 8px; margin-top: 5px;">
            <?php for ($i = 1; $i <= 10; $i++): ?>
            <label style="display: flex; flex-direction: column; align-items: center;">
                <input type="radio" name="alcance" value="<?php echo $i; ?>" required>
                <?php echo $i; ?>
            </label>
            <?php endfor; ?>
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="precios">¿Cómo calificaría el precio del proyecto refieriendose a 10 como muy costoso?</label>
            <div style="display: flex; gap: 8px; margin-top: 5px;">
            <?php for ($i = 1; $i <= 10; $i++): ?>
            <label style="display: flex; flex-direction: column; align-items: center;">
                <input type="radio" name="precios" value="<?php echo $i; ?>" required>
                <?php echo $i; ?>
            </label>
            <?php endfor; ?>
            </div>
        </div>


        <label for="comentario">Comentario:</label>
        <textarea id="comentario" name="comentario" rows="4"></textarea>

        <button type="submit">Enviar</button>

        <?php if (isset($confirmacion)): ?>
            <p class="<?php echo strpos($confirmacion, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo $confirmacion; ?>
            </p>
        <?php endif; ?>
    </form>
</body>
</html>
