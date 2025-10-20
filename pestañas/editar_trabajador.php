<!DOCTYPE html>
<html lang="en">
<head>
    <title>Editar trabajadores</title>
</head>
<body id="trabajadores">
    <style>
        .centrado {
            text-align: center;
        }
        .formulario_reporte_fecha {
            margin-right: 10px;
        }
        .formulario_reporte_area {
            margin-bottom: 10px;
        }
        .reporte_tabla {
            margin-top: 20px;
        }
        .principal {
            padding: 20px;
        }
        .trabajador-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            width: 80%;
            margin: auto;
        }
        .trabajador-info div {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .trabajador-info div:nth-child(odd) {
            background-color: #f2f2f2;
        }
        .trabajador-info h2 {
            grid-column: span 2;
            text-align: center;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }
        .trabajador-info label {
            display: block;
            margin-bottom: 5px;
        }
        .trabajador-info input, .trabajador-info select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .boton-rojo {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .boton-rojo:hover {
            background-color: #e53935;
        }
    </style>

    <div class="principal">
        <section>
            <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $id = $_POST['id'];
                    $nombre = $_POST['nombre'];
                    $apellidos = $_POST['apellidos'];
                    $CURP = $_POST['curp'];
                    $RFC = $_POST['rfc'];
                    $IMSS = $_POST['nss'];
                    $fecha_nacimiento = $_POST['fecha_nacimiento'];
                    $codigo_postal = $_POST['codigo_postal'];
                    $clave_entidad_fed = $_POST['clave_entidad_fed'];
                    $Empresa = $_POST['empresa'];
                    $fecha_ingreso = $_POST['fecha_ingreso'];
                    $area = $_POST['area'];
                    $Puesto = $_POST['puesto'];
                    $salario = $_POST['salario'];
                    $tipo_contrato = $_POST['contrato'];
                    $cronograma = isset($_POST['cronograma']) ? 1 : 0;
                    $tarjeta = $_POST['clave_bancaria'];
                    $forma_de_pago = $_POST['forma_de_pago'];
                    
                    // Verificar si hay una nueva imagen cargada
                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                        // Validar que la imagen sea un archivo PNG
                        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                        if (strtolower($extension) == 'png') {
                            // Definir la carpeta de destino
                            $ruta_destino = "../captures/fotos_trabajadores/" . $id . ".png";

                            // Si ya existe una imagen con ese nombre, eliminarla
                            if (file_exists($ruta_destino)) {
                                unlink($ruta_destino);
                            }

                            // Mover la nueva imagen a la carpeta destino
                            move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino);
                        } else {
                            echo "Solo se permite subir imágenes en formato PNG.";
                        }
                    }

                    $sql = "UPDATE trabajadores SET nombre='$nombre', apellidos='$apellidos', curp='$CURP', rfc='$RFC', nss='$IMSS', fecha_nacimiento='$fecha_nacimiento', codigo_postal='$codigo_postal', clave_entidad_fed='$clave_entidad_fed', empresa='$Empresa', fecha_ingreso='$fecha_ingreso', area='$area', puesto='$Puesto', salario='$salario', contrato='$tipo_contrato', cronograma='$cronograma', clave_bancaria='$tarjeta', forma_de_pago='$forma_de_pago' WHERE id='$id'";

                    if ($conexion->query($sql) === TRUE) {
                        echo "Registro actualizado correctamente.";
                    } else {
                        echo "Error actualizando el registro: " . $conexion->error;
                    }
                }

                if (isset($_GET['id'])) {
                    $trabajador_id = $_GET['id'];
                    $sql = "SELECT * FROM trabajadores WHERE id='$trabajador_id'";
                    $resultado = $conexion->query($sql);
                    if ($resultado->num_rows > 0) {
                        $fila = $resultado->fetch_assoc();
                        echo "<form method='POST' action=''>";
                        echo "<div class='trabajador-info'>";
                            echo "<h2>Información del Trabajador</h2>";
                            echo "<div>ID:</div><div><input type='text' name='id' value='" . $fila["id"] . "' readonly></div>";
                            echo "<div>Nombre:</div><div><input type='text' name='nombre' value='" . $fila["nombre"] . "'></div>";
                            echo "<div>Apellidos:</div><div><input type='text' name='apellidos' value='" . $fila["apellidos"] . "'></div>";
                            echo "<div>CURP:</div><div><input type='text' name='curp' value='" . $fila["curp"] . "'></div>";
                            echo "<div>RFC:</div><div><input type='text' name='rfc' value='" . $fila["rfc"] . "'></div>";
                            echo "<div>IMSS:</div><div><input type='text' name='nss' value='" . $fila["nss"] . "'></div>";
                            echo "<div>Fecha de Nacimiento:</div><div><input type='date' name='fecha_nacimiento' value='" . $fila["fecha_nacimiento"] . "'></div>";
                            echo "<div>Código Postal:</div><div><input type='text' name='codigo_postal' value='" . $fila["codigo_postal"] . "'></div>";
                            echo "<div>Clave Entidad Fed:</div><div>";
                            ?><select class="" name="clave_entidad_fed" id="estado" required>
                                <?php echo "<option value='".$fila['clave_entidad_fed']."'>".$fila['clave_entidad_fed']."</option>";
                                $ent_fed="select estado,cla from entidades_federativas";
                                $result=mysqli_query($conexion,$ent_fed);
                                while($row=mysqli_fetch_array($result)){
                                    echo "<option value='".$row['cla']."'>".$row['estado']."</option>";
                                }
                                ?>
                            </select><?php 
                            echo "</div>";
                            echo "<div>Empresa:</div><div>";
                            echo $selectDatosExistentes->obtenerOpcionesExistentes('listas', 'empresa', 'empresa', '', $fila['empresa']);
                            echo "</div>";
                            echo "<div>Fecha de Ingreso:</div><div><input type='date' name='fecha_ingreso' value='" . $fila["fecha_ingreso"] . "'></div>";
                            echo "<div>Área:</div><div>";
                            echo $selectDatosExistentes->obtenerOpcionesExistentes('listas', 'area', 'area', '', $fila['area']);
                            echo "</div>";
                            echo "<div>Puesto:</div><div>";
                            echo $selectDatosExistentes->obtenerOpcionesExistentes('listas', 'puesto', 'puesto', '', $fila['puesto']);
                            echo "</div>";
                            echo "<div>Salario Diario IMSS:</div><div><input type='text' name='salario' value='" . $fila["salario"] . "'></div>";
                            echo "<div>Contrato:</div><div>";
                            echo $selectDatosExistentes->obtenerOpcionesExistentes('listas', 'contrato', 'contrato', '', $fila['contrato']);
                            echo "</div>";
                            echo "<div>Tarjeta:</div><div><input type='text' name='clave_bancaria' value='" . $fila["clave_bancaria"] . "'></div>";
                            echo "<div>Forma de pago:</div><div>";
                            echo $selectDatosExistentes->obtenerOpcionesExistentes('listas', 'forma_pago', 'forma_de_pago', '', $fila['forma_de_pago']);
                            echo "</div>";
                            echo "<div>Cronograma:</div><div>";
                            echo "<input type='checkbox' name='cronograma' value='1' " . ($fila["cronograma"] == 1 ? "checked" : "") . ">";
                            echo "</div>";
                            

                // Mostrar la imagen si ya existe
                $imagen_path = "../captures/fotos_trabajadores/" . $fila['id'] . ".png";
                if (file_exists($imagen_path)) {
                    echo "<div><img src='$imagen_path' alt='Foto del trabajador' style='width: 100px;'></div>";
                }
                else{
                    echo "<div>No hay foto</div>";
                }

                echo "<div class='custom-file'>";
                echo "<input type='file' class='custom-file-input' id='foto' name='foto'>";
                echo "<label class='custom-file-label' for='foto'>Foto (Solo PNG)</label>";
                echo "</div>";
                echo "</div>";
                            echo "<div style='grid-column: span 2; text-align: center;'><input type='submit' value='Guardar Cambios'></div>";
                        echo "</div>";
                        echo "</form>";
                    } else {
                        echo "No se encontró información para el trabajador seleccionado.";
                    }
                }
            ?>
        </section>
    </div>
</body>
</html>