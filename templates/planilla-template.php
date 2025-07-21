<?php
$qr_src = $this->generate_qr_code($code);
$fecha = date('d/m/Y', strtotime($user_data['fecha_recepcion']));
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bienvenida Oficial - Humanitarios de la República Dominicana</title>
    <style>
        :root {
            --primary-color: #004da7;
            --accent-color: #ce1126;
            --text-color: #444;
            --background-color: #f5f5f5;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
        }

        .container {
            width: 70%;
            margin: 20px auto;
            background-color: #fff;
            padding: 24px;
            box-shadow: var(--card-shadow);
            border-left: 6px solid var(--accent-color);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-header img {
            width: 100px;
        }

        .contact-info {
            text-align: right;
            font-size: 14px;
            color: var(--text-color);
        }

        .contact-info p,
        .address p,
        .date p,
        .signature p,
        .footer p {
            margin: 0;
        }

        .date {
            text-align: right;
            font-size: 14px;
            color: var(--text-color);
            margin-top: 10px;
        }

        .welcome-title {
            font-size: 28px;
            color: var(--primary-color);
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
        }

        .address {
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--text-color);
        }

        .salutation {
            font-size: 14px;
            color: var(--text-color);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 40px;
            gap: 20px;
        }

        .signature {
            font-size: 14px;
            color: var(--text-color);
        }

        .signature img {
            width: 120px;
            margin: 8px 0;
        }

        .modern-id-card {
            width: 360px;
            border-radius: 10px;
            overflow: hidden;
            background: linear-gradient(145deg, #ffffff, #f4f4f4);
            border: 2px solid var(--primary-color);
            box-shadow: var(--card-shadow);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
        }

        .card-header h3 {
            font-size: 16px;
            margin: 0 10px;
            flex: 1;
            text-align: center;
        }

        .logo {
            width: 50px;
            height: auto;
            border-radius: 4px;
            background-color: white;
            padding: 4px;
        }

        .qr {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            background-color: white;
            padding: 4px;
        }

        .info {
            padding: 12px;
            color: #333;
            font-size: 13px;
        }

        .name {
            font-size: 15px;
            font-weight: bold;
            color: var(--accent-color);
            margin-bottom: 6px;
        }

        .role {
            font-weight: bold;
            color: var(--primary-color);
            margin-top: 4px;
        }

        .mission {
            font-size: 11px;
            color: #555;
            margin-top: 10px;
            text-align: justify;
            line-height: 1.4;
            word-break: break-word;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }

        @media screen and (max-width: 600px) {
            .container {
                width: 95%;
                padding: 16px;
            }

            .signature-section {
                flex-direction: column;
                align-items: stretch;
            }

            .modern-id-card {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="page-header">
            <img src="<?= plugin_dir_url(__FILE__) ?>../../assets/img/logo.png" alt="Logo">
            <div class="contact-info">
                <p>Calle Oviedo No.113. Villa Consuelo. Distrito Nacional.</p>
                <p>República Dominicana. Tel. 809-663-7891.</p>
                <p>E-mail: humanitarios@gmail.com</p>
            </div>
        </div>

        <h1 class="welcome-title">Bienvenida Oficial</h1>

        <div class="date">
            <p><?= $fecha ?></p>
        </div>

        <div class="address">
            <p>Dr. Fernando Vásquez Páez</p>
            <p>Ministerio de Salud Pública</p>
            <p>Calle Oviedo no. 113,</p>
            <p>Distrito Nacional</p>
            <p>Santo Domingo, 10308</p>
            <p>República Dominicana</p>
        </div>

        <div class="salutation">
            <p><strong>Estimado/a <?= "{$user_data['first_name']} {$user_data['last_name']}" ?>:</strong></p>
            <p>
                Nos honra darte la más cordial bienvenida como nuevo voluntario de
                Humanitarios de la República Dominicana. Tu decisión de integrarte a
                esta familia solidaria representa un compromiso invaluable con el
                bienestar de nuestras comunidades.
            </p>
            <p>
                Como voluntario, estarás contribuyendo activamente en programas de
                respuesta ante emergencias, jornadas de capacitación, actividades
                comunitarias y misiones humanitarias. Además, tendrás acceso a
                oportunidades formativas, redes de apoyo, y reconocimiento por tu
                dedicación y entrega.
            </p>
            <p>
                Tu participación fortalece nuestra capacidad de actuar con eficiencia
                y humanidad donde más se necesita. Estamos seguros de que tu energía,
                tus valores y tu espíritu solidario serán un aporte significativo a
                cada causa que emprendamos.
            </p>
            <p>
                Gracias por tu compromiso. Estamos felices de tenerte con nosotros.
            </p>
        </div>

        <div class="signature-section">
            <div class="signature">
                <p>Con gratitud,</p>
                <img src="<?= plugin_dir_url(__FILE__) ?>../../assets/img/firma.png" alt="Firma">
                <p><strong>Dr. Fernando Vásquez Páez</strong></p>
                <p>Humanitarios de la República Dominicana</p>
            </div>

            <div class="modern-id-card">
                <div class="card-header">
                    <img class="logo" src="<?= plugin_dir_url(__FILE__) ?>../../assets/img/logo.png" alt="Logo">
                    <h3>Voluntariado Nacional</h3>
                    <img class="qr" src="<?= $qr_src ?>" alt="QR Code">
                </div>
                <div class="info">
                    <p class="name"><?= "{$user_data['first_name']} {$user_data['last_name']}" ?></p>
                    <p>ID: <?= $code ?></p>
                    <p class="role">Certificación de membresía</p>
                    <p class="mission">
                        Transformar vidas promoviendo el bienestar, la salud y el
                        desarrollo integral de comunidades vulnerables.
                    </p>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Calle Oviedo No.113. Villa Consuelo. Distrito Nacional. República Dominicana.</p>
            <p>Tel. 809-663-7891. E-mail: humanitarios@gmail.com</p>
        </div>
    </div>
</body>

</html>