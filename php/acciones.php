<?php
include '../conexion.php';
$conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Verifica si la conexión fue exitosa
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}


class SelectDatos {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerOpciones($tabla, $campo, $name, $class) {
        echo '<select class="' . $class . '" name="' . $name . '">';
        echo '<option value="">'.$campo.'</option>';

        $sql = "SELECT $campo FROM $tabla where $campo is not null order by $campo";
        $resultado = $this->conexion->query($sql);
        
        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($fila[$campo]) . '">' . htmlspecialchars($fila[$campo]) . '</option>';
            }
        } else {
            echo '<option value="">No data</option>';
        }
        
        echo '</select>';
    }
}
// Crear instancia de la clase
$selectDatos = new SelectDatos($conexion);

class selectDatosExistentes {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerOpcionesExistentes($tabla, $campo, $name, $class, $valorSeleccionado = "") {
        echo '<select class="' . $class . '" name="' . $name . '">';
        
        // Primera opción con el valor actual de $fila['responsable']
        if (!empty($valorSeleccionado)) {
            echo '<option value="' . htmlspecialchars($valorSeleccionado) . '">' . htmlspecialchars($valorSeleccionado) . '</option>';
        } else {
            echo '<option value="">Seleccione una opción</option>';
        }

        // Consulta SQL para obtener los valores
        $sql = "SELECT DISTINCT $campo FROM $tabla WHERE $campo IS NOT NULL ORDER BY $campo";
        $resultado = $this->conexion->query($sql);
        
        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $valor = htmlspecialchars($fila[$campo]);
                
                // Evita duplicar el valor seleccionado
                if ($valor !== $valorSeleccionado) {
                    echo '<option value="' . $valor . '">' . $valor . '</option>';
                }
            }
        } else {
            echo '<option value="">No data</option>';
        }

        echo '</select>';
    }
}

// Crear instancia de la clase
$selectDatosExistentes = new selectDatosExistentes($conexion);

class SelectTrabajadores {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerNombres($name, $class) {
        echo '<select class="' . $class . '" name="' . $name . '">';
        echo '<option value="">Seleccione trabajador</option>';

        $sql = "SELECT * FROM trabajadores where estado='Activo' order by apellidos";
        $resultado = $this->conexion->query($sql);
        
        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($fila['id']) . '">' . htmlspecialchars($fila['apellidos']) .' '. htmlspecialchars($fila['nombre']) . '</option>';
            }
        } else {
            echo '<option value="">No data</option>';
        }
        
        echo '</select>';
    }
}
// Crear instancia de la clase
$SelectTrabajadores = new SelectTrabajadores($conexion);


if (isset($_POST['agregarPlanta'])) {
    $planta = $_POST['nombre_planta'];

    // Validar el campo
    if (!empty($planta)) {
        // Consulta para insertar la nueva planta
        $sql = "INSERT INTO planta (planta) VALUES (?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $planta);

        if ($stmt->execute()) {
            // Redireccionar para evitar reenvíos del formulario
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo "Error al agregar planta: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Por favor, ingresa un nombre válido para la planta.";
    }
}

if (isset($_POST['eliminarPlanta'])) {
    $id_planta = $_POST['id_planta'];

    // Eliminar la planta por ID
    $sql = "DELETE FROM planta WHERE id = $id_planta";
    if ($conexion->query($sql) === TRUE) {
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        echo "Error al eliminar planta: " . $conexion->error;
    }
}

if (isset($_POST['actualizar_prioridad'])) {
    // Recibir los datos del formulario
    $id_pieza = intval($_POST['id_pieza']);
    $prioridad = floatval($_POST['prioridad']);
    $sql_actualizar = "UPDATE piezas SET prioridad = $prioridad WHERE id = $id_pieza";
    $conexion->query($sql_actualizar);
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Manejar acciones de botones
if (isset($_POST['iniciado'])) {
    $id_pieza = $_POST['id_pieza'];
    $fecha_actual = date('Y-m-d H:i:s');
    $sql = "UPDATE piezas SET fecha_inicial = '$fecha_actual' WHERE id = $id_pieza";
    $conexion->query($sql);
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_POST['terminado'])) {
    $id_pieza = $_POST['id_pieza'];
    $fecha_actual = date('Y-m-d H:i:s');
    $sql = "UPDATE piezas SET fecha_final = '$fecha_actual' WHERE id = $id_pieza";
    $conexion->query($sql);
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_POST['agregarEncargado'])) {
    $id_pieza = $_POST['id_pieza'];
    $fecha_actual = date('Y-m-d H:i:s');
    $sql = "INSERT INTO encargado (id_pieza, fecha) VALUES ($id_pieza, '$fecha_actual')";
    if ($conexion->query($sql) === TRUE) {
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        echo "Error al agregar encargado: " . $conexion->error;
    }
}

if (isset($_POST['guardar'])) {
    $id_pieza = $_POST['id_pieza'];
    $id_encargado = isset($_POST['id_encargado']) ? $_POST['id_encargado'] : [];
    $id_trabajador = isset($_POST['id_trabajador']) ? $_POST['id_trabajador'] : [];
    $tiempos = isset($_POST['encargado_tiempo']) ? $_POST['encargado_tiempo'] : [];
    $cantidades = isset($_POST['encargado_cantidad']) ? $_POST['encargado_cantidad'] : [];
    $fecha_actual = date('Y-m-d');

    if (is_array($id_encargado) && count($id_encargado) > 0) {
        for ($i = 0; $i < count($id_encargado); $i++) {
            $nombre = isset($id_trabajador[$i]) ? $id_trabajador[$i] : null;
            $tiempo = isset($tiempos[$i]) ? $tiempos[$i] : 0;
            $cantidad = isset($cantidades[$i]) ? $cantidades[$i] : 0;
            $sql = "UPDATE encargado 
                SET id_trabajador = " . (!empty($nombre) ? intval($nombre) : "NULL") . ",
                    tiempo = " . (!empty($tiempo) ? floatval($tiempo) : "0") . ",
                    cantidad = " . (!empty($cantidad) ? intval($cantidad) : "0") . ",
                    fecha = '$fecha_actual' 
                WHERE id = " . intval($id_encargado[$i]);
                $conexion->query($sql);
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
    }
    exit;
}

if (isset($_POST['eliminarEncargado'])) {
    $id_encargado = $_POST['id_encargado'];
    if (is_array($id_encargado)) {
        $id_encargado = implode(',', $id_encargado);
    }
    $sql = "DELETE FROM encargado WHERE id = $id_encargado";
    if ($conexion->query($sql) === TRUE) {
        header("Location: " . $_SERVER['REQUEST_URI']);
        
    } else {
        echo "Error al eliminar encargado: " . $conexion->error;
    }
    exit;
}
// Manejo de la acción de agregar un nuevo prerrequisito
if (isset($_POST['agregarRequisito'])) {
    $id_pieza = intval($_POST['id_pieza']);
    $prerrequisito_pieza = !empty($_POST['prerrequisito']) ? intval($_POST['prerrequisito']) : null;
    $prerrequisito_compra = !empty($_POST['prerrequisito_compra']) ? intval($_POST['prerrequisito_compra']) : null;


    $sql = "INSERT INTO prerrequisitos (pieza, prerrequisito,compra) VALUES (?, ?,?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iii", $id_pieza, $prerrequisito_pieza,$prerrequisito_compra);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['REQUEST_URI']);
    } else {
        echo "Error al agregar requisito: " . $conexion->error;
    }
    $stmt->close();
}

if (isset($_POST['borrarRequisito'])) {
    $id_requisito = intval($_POST['id_requisito']);

    // Preparar la consulta para eliminar
    $sql = "DELETE FROM prerrequisitos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_requisito);

    if ($stmt->execute()) {
        // Redirigir a la misma página para evitar reenvíos de formulario
        header("Location: " . $_SERVER['REQUEST_URI']);
    } else {
        echo "Error al eliminar requisito: " . $stmt->error;
    }

    $stmt->close();
}


if (isset($_POST['eliminar_compra'])) {
    $id = $_POST['id'] ?? null;

    if ($id) {
        // Obtener id_oc antes de eliminar la compra
        $sql = "SELECT id_oc FROM compras WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $id_oc = null;
        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            $id_oc = $fila['id_oc'];
        }
        $stmt->close();

        // Eliminar la compra
        $sql_delete = "DELETE FROM compras WHERE id = ?";
        $stmt_delete = $conexion->prepare($sql_delete);
        $stmt_delete->bind_param("i", $id);
        $stmt_delete->execute();
        $stmt_delete->close();

        // Si hay id_oc válido, recalcular totales
        if ($id_oc && $id_oc > 1) {
            $conexion->query("CALL calculate_and_update_totals($id_oc);");
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}
if (isset($_POST['firma'])) {
    $id_firma_almacen = $_POST['id_firma_almacen'];
    $firma = date('Y-m-d'); // Obtener la fecha y hora actual
    $sql_firma_almacen = "UPDATE orden_compra SET firma_llegada = '$firma' WHERE id = '$id_firma_almacen'";
    if ($conexion->query($sql_firma_almacen) === TRUE) {
        header("Location: " . $_SERVER['REQUEST_URI']);
    } else {
        echo "Error actualizando el registro: " . $conexion->error;
    }
}

if (isset($_POST['submit_pagado'])) {
    $id_pagado = $_POST['pagado'];
    $pagado = date('Y-m-d'); // Obtener la fecha actual

    // Preparar la consulta para evitar inyección SQL
    $sql_pagado = "UPDATE orden_compra SET pago = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql_pagado);

    // Verificar si la preparación fue exitosa
    if ($stmt === false) {
        echo "Error en la preparación de la consulta: " . $conexion->error;
    } else {
        // Asociar los parámetros (el valor de $pagado y $id_pagado)
        $stmt->bind_param("si", $pagado, $id_pagado); // "s" para string, "i" para entero

        // Ejecutar la consulta
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit(); // Asegúrate de hacer un `exit` después de `header` para evitar que el script continúe ejecutándose
        } else {
            echo "Error actualizando el registro: " . $stmt->error;
        }

        // Cerrar la declaración
        $stmt->close();
    }
}
if (isset($_POST['guardar_base'])){
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $rfc = $_POST['rfc'] ?? '';
    $nss = $_POST['nss'] ?? '';
    $curp = $_POST['curp'] ?? '';

    // Asumiendo que tienes el ID del trabajador
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $conexion->prepare("UPDATE trabajadores SET nombre=?, apellidos=?, rfc=?, nss=?, curp=? WHERE id=?");
        $stmt->bind_param("sssssi", $nombre, $apellidos, $rfc, $nss, $curp, $id);
        $stmt->execute();
    }
    // Redirige o muestra mensaje
    header("Location: " . $_SERVER['HTTP_REFERER'] );
}
if (isset($_POST['guardar_personal'])) {
        $telefono = $_POST['telefono'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $estado = $_POST['estado'] ?? '';
        $cp = $_POST['codigo_postal'] ?? '';
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';

        // Asumiendo que tienes el ID del trabajador
        $id = $_POST['id'] ?? null;

        if ($id) {
            $stmt = $conexion->prepare("UPDATE trabajadores SET telefono=?, correo=?, direccion=?, clave_entidad_fed=?, codigo_postal=?, fecha_nacimiento=? WHERE id=?");
            $stmt->bind_param("ssssssi", $telefono, $correo, $direccion, $estado, $cp, $fecha_nacimiento, $id);
            $stmt->execute();
        }
        // Redirige o muestra mensaje
    header("Location: " . $_SERVER['HTTP_REFERER'] );
    }

    if (isset($_POST['guardar_laboral'])) {
    $empresa = $_POST['empresa'] ?? '';
    $area = $_POST['area'] ?? '';
    $puesto = $_POST['puesto'] ?? '';
    $supervisor = $_POST['supervisor'] ?? '';
    $turno = $_POST['turno'] ?? '';
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $conexion->prepare("UPDATE trabajadores SET empresa=?, area=?, puesto=?, supervisor=?, turno=? WHERE id=?");
        $stmt->bind_param("sssssi", $empresa, $area, $puesto, $supervisor, $turno, $id);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['HTTP_REFERER'] );
}


    if (isset($_POST['guardar_imss'])) {
        $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $conexion->prepare("UPDATE trabajadores SET fecha_ingreso=? WHERE id=?");
            $stmt->bind_param("si", $fecha_ingreso, $id);
            $stmt->execute();
        }
        // Redirige o muestra mensaje
    header("Location: " . $_SERVER['HTTP_REFERER'] );
    }

    if (isset($_POST['guardar_nomina'])) {
        if (isset($_POST['guardar_nomina'])) {
    $contrato = $_POST['contrato'] ?? '';
    $salario = $_POST['salario'] ?? '';
    $forma_pago = $_POST['forma_pago'] ?? '';
    $clave_bancaria = $_POST['clave_bancaria'] ?? '';
    $banco = $_POST['banco'] ?? '';
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $conexion->prepare("UPDATE trabajadores SET contrato=?, salario=?, forma_de_pago=?, clave_bancaria=?, banco=? WHERE id=?");
        $stmt->bind_param("sdsssi", $contrato, $salario, $forma_pago, $clave_bancaria, $banco, $id);
        $stmt->execute();
    }
}
    header("Location: " . $_SERVER['HTTP_REFERER'] );
    }