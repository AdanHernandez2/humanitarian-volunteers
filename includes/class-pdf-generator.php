<?php

// includes/class-pdf-generator.php

require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class PDF_Generator
{

    /**
     * Genera el certificado de voluntario
     * 
     * @param int $user_id
     * @param array $user_data
     * @return string Ruta del archivo generado
     */
    public function generate_certificate($user_id, $user_data)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Obtener plantilla HTML
        ob_start();
        $this->render_certificate_template($user_data);
        $html = ob_get_clean();

        // Generar PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = "certificado-{$user_data['first_name']}-{$user_id}.pdf";
        return $this->save_pdf($dompdf, $filename);
    }

    /**
     * Genera la planilla con código QR
     * 
     * @param int $user_id
     * @param array $user_data
     * @param string $qr_url URL del QR generado
     * @return string Ruta del archivo generado
     */
    public function generate_planilla($user_id, $user_data, $qr_url)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Obtener plantilla HTML
        ob_start();
        $this->render_planilla_template($user_data, $qr_url);
        $html = ob_get_clean();

        // Generar PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = "planilla-{$user_data['first_name']}-{$user_id}.pdf";
        return $this->save_pdf($dompdf, $filename);
    }

    /**
     * Renderiza la plantilla del certificado
     */
    private function render_certificate_template($user_data)
    {
        // Rutas de imágenes
        $bg_path = plugins_url('humanitarios/assets/img/certificado-bg.jpg');
        $fecha = date('d/m/Y', strtotime($user_data['fecha_recepcion']));
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
                    font-family: 'Times New Roman', serif;
                }

                .certificate-container {
                    position: relative;
                    height: 100%;
                    width: 100%;
                    text-align: center;
                }

                .nombre {
                    position: absolute;
                    top: 50%;
                    /* Ajusta esta posición según tu diseño */
                    left: 50%;
                    transform: translate(-50%, -50%);
                    font-size: 36px;
                    font-weight: bold;
                    color: #000;
                    width: 80%;
                }

                .descripcion {
                    position: absolute;
                    top: 60%;
                    /* Ajusta esta posición según tu diseño */
                    right: 10%;
                    text-align: right;
                    font-size: 18px;
                    max-width: 600px;
                }
            </style>
        </head>

        <body>
            <div class="certificate-container">
                <div class="nombre">
                    <?= $user_data['first_name'] . ' ' . $user_data['last_name'] ?>
                </div>
                <div class="descripcion">
                    RECONOCEMOS FORMALMENTE SU INCORPORACIÓN COMO VOLUNTARIO
                    ACTIVO DE LA ORGANIZACIÓN HUMANITARIOS DE LA REPÚBLICA
                    DOMINICANA A PARTIR DEL <?= $fecha ?> A:
                </div>
            </div>
        </body>

        </html>
    <?php
    }

    /**
     * Renderiza la plantilla de la planilla con QR
     */
    private function render_planilla_template($user_data, $qr_url)
    {
        // Rutas de imágenes
        $logo_path = plugins_url('humanitarios/assets/img/logo.png');
        $firma_path = plugins_url('humanitarios/assets/img/firma.png');
        $fecha = date('d/m/Y', strtotime($user_data['fecha_recepcion']));
    ?>
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Planilla de Verificación - Humanitarios RD</title>
            <style>
                /* Copia todo el CSS de tu plantilla original aquí */
                /* Por brevedad no lo repito, pero es esencial para el diseño */
            </style>
        </head>

        <body>
            <div class="container">
                <div class="page-header">
                    <img src="<?= $logo_path ?>" alt="Humanitarios Logo" />
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
                    <p><strong>Estimado/a <?= $user_data['first_name'] . ' ' . $user_data['last_name'] ?>:</strong></p>
                    <!-- Contenido de la carta -->
                </div>

                <div class="signature-section">
                    <div class="signature">
                        <p>Con gratitud,</p>
                        <img src="<?= $firma_path ?>" alt="Firma Dr. Fernando" />
                        <p><strong>Dr. Fernando Vásquez Páez</strong></p>
                        <p>Humanitarios de la República Dominicana</p>
                    </div>

                    <div class="modern-id-card">
                        <div class="card-header">
                            <img class="logo" src="<?= $logo_path ?>" alt="Logo Humanitarios" />
                            <h3>Voluntariado Nacional</h3>
                            <img class="qr" src="<?= $qr_url ?>" alt="QR Code" />
                        </div>
                        <div class="info">
                            <p class="name"><?= $user_data['first_name'] . ' ' . $user_data['last_name'] ?></p>
                            <p>ID: <?= $user_data['code'] ?></p>
                            <p class="role">Certificación de membresía</p>
                            <p class="mission">
                                Transformar vidas promoviendo el bienestar, la salud y el
                                desarrollo integral de comunidades vulnerables.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <p>
                        Calle Oviedo No.113. Villa Consuelo. Distrito Nacional. República
                        Dominicana.
                    </p>
                    <p>Tel. 809-663-7891. E-mail: humanitarios@gmail.com</p>
                </div>
            </div>
        </body>

        </html>
<?php
    }

    /**
     * Genera un código QR en formato SVG base64
     */
    public function generate_qr_code($code)
    {
        $qr_url = "https://midominio.com/verificacion-voluntario/?hv_unique_code={$code}";

        $renderer = new ImageRenderer(
            new RendererStyle(150),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($qr_url);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Guarda el PDF en el servidor
     */
    private function save_pdf($dompdf, $filename)
    {
        $upload_dir = wp_upload_dir();
        $save_path = $upload_dir['basedir'] . '/humanitarios-pdfs/';

        // Crear directorio si no existe
        if (!file_exists($save_path)) {
            wp_mkdir_p($save_path);
        }

        // Proteger el directorio con .htaccess
        if (!file_exists($save_path . '.htaccess')) {
            file_put_contents($save_path . '.htaccess', "Order deny,allow\nDeny from all");
        }

        $full_path = $save_path . $filename;
        file_put_contents($full_path, $dompdf->output());

        return $full_path;
    }

    /**
     * Verifica si un PDF ya existe para evitar regeneración
     */
    public function pdf_exists($user_id, $type = 'certificate')
    {
        $user_data = [
            'first_name' => get_user_meta($user_id, 'first_name', true)
        ];

        $filename = ($type === 'certificate')
            ? "certificado-{$user_data['first_name']}-{$user_id}.pdf"
            : "planilla-{$user_data['first_name']}-{$user_id}.pdf";

        $file_path = wp_upload_dir()['basedir'] . '/humanitarios-pdfs/' . $filename;

        return file_exists($file_path) ? $file_path : false;
    }
}
