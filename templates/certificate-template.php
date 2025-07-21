<?php
$fecha = date('d/m/Y', strtotime($user_data['fecha_recepcion']));
$bg_path = plugin_dir_url(__FILE__) . '../../assets/img/certificado-bg.jpg';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-image: url('<?= $bg_path ?>');
            background-size: cover;
            background-position: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }

        .certificate-container {
            position: relative;
            height: 100%;
            width: 100%;
        }

        .nombre {
            position: absolute;
            top: 50%;
            /* Ajustar según diseño */
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 36px;
            font-weight: bold;
            color: #000;
        }

        .descripcion {
            position: absolute;
            top: 60%;
            /* Ajustar según diseño */
            right: 15%;
            text-align: right;
            font-size: 18px;
            max-width: 600px;
        }
    </style>
</head>

<body>
    <div class="certificate-container">
        <div class="nombre">
            <?= "{$user_data['first_name']} {$user_data['last_name']}" ?>
        </div>
        <div class="descripcion">
            RECONOCEMOS FORMALMENTE SU INCORPORACIÓN COMO VOLUNTARIO
            ACTIVO DE LA ORGANIZACIÓN HUMANITARIOS DE LA REPÚBLICA
            DOMINICANA A PARTIR DEL <?= $fecha ?> A:
        </div>
    </div>
</body>

</html>