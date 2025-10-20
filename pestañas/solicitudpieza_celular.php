<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de pieza</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }

        .contenedor__servicios {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .titulo {
            text-align: center;
            margin-bottom: 20px;
        }

        .escrito, .entrada {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
            font-weight: bold;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .altadepieza__campo__cantidad,
        .altadepieza__campo__descripcion,
        .altadepieza__campo__comentario {
            display: block;
        }

        .altadepieza__boton__enviar {
            text-align: center;
            margin-top: 20px;
        }

        .boton__enviar {
            padding: 10px 20px;
            background-color: #007BFF;
            border: none;
            color: white;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }

        .boton__enviar:hover {
            background-color: #0056b3;
        }

        .confirmacion {
            text-align: center;
            color: green;
            font-weight: bold;
            margin-bottom: 15px;
        }

            .servicios__form {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }

            .altadepieza__campo {
                grid-column: span 3;
            }

            .altadepieza_cam {
                grid-column: span 2;
            }

            .altadepieza__campo__cantidad,
            .altadepieza__campo__descripcion,
            .altadepieza__campo__comentario {
                grid-column: span 1;
            }

            .altadepieza__boton__enviar {
                grid-column: span 3;
            }
        
    </style>
</head>
<body id="solicitudpieza">
<div class="contenedor__servicios">
    <h2 class="titulo">Solicitud de pieza</h2>
    <?php
        if (isset($_GET['confirmacion'])) {
            $confirmacion = htmlspecialchars($_GET['confirmacion']);
            echo "<div class='confirmacion'>$confirmacion</div>";
        }
    ?>
    <form class="servicios__form" action="../php/procesar_solicitud_pieza.php" method="POST">
        <input class="escrito altadepieza" type="text" id="ot" name="ot_texto" placeholder="OT" required oninput="obtenerNombreProyecto()">
        <?php $selectDatos->obtenerOpciones('listas', 'area', 'area','escrito altadepieza_cam'); ?>
        <input class="escrito altadepieza__campo" type="text" name="nombreDelProyecto" id="nombreDelProyecto" placeholder="Nombre del proyecto" readonly>
        <input type="hidden" name="header_loc" value="<?php echo $header_loc; ?>">

        <?php for ($i = 1; $i <= 10; $i++): ?>
            <input type="number" class="entrada altadepieza__campo__cantidad" name="cantidad[]" placeholder="Cantidad">
            <input type="text" class="entrada altadepieza__campo__descripcion" name="descripcion[]" placeholder="Descripción">
            <input type="text" class="entrada altadepieza__campo__comentario" name="comentario[]" placeholder="Comentario">
        <?php endfor; ?>

        <div class="altadepieza__boton__enviar">
            <input class="boton__enviar" type="submit" value="Enviar">
        </div>
    </form>
</div>
</body>
</html>
