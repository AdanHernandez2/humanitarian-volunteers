<?php
// includes/class-pdf-generator.php

require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class PDF_Generator
{

    /**
     * Devuelve el directorio temporal para mPDF
     */
    private function get_temp_dir()
    {
        // Usa el directorio temporal de WordPress si está disponible, si no, usa sys_get_temp_dir()
        if (function_exists('wp_upload_dir')) {
            $upload_dir = wp_upload_dir();
            if (!empty($upload_dir['basedir'])) {
                $temp_dir = trailingslashit($upload_dir['basedir']) . 'mpdf-tmp/';
                if (!file_exists($temp_dir)) {
                    wp_mkdir_p($temp_dir);
                }
                return $temp_dir;
            }
        }
        // Fallback
        return sys_get_temp_dir();
    }

    /**
     * Configuración optimizada de mPDF
     */
    private function get_mpdf_config($orientation = 'P')
    {
        return [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => $orientation,
            'default_font' => 'helvetica',
            'tempDir' => $this->get_temp_dir(),
            'img_dpi' => 96,
            'showImageErrors' => true,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
            'default_font_size' => 10,
            'useSubstitutions' => false,
            'pcre.backtrack_limit' => '10000000', // Aumentamos el límite
            'pcre.recursion_limit' => '10000000', // Aumentamos el límite
            'simpleTables' => true,
            'packTableData' => true
        ];
    }

    /**
     * Genera el certificado optimizado
     */

    public function generate_certificate($user_id, $user_data)
    {
        $qr_path = null;
        try {
            $mpdf = new Mpdf($this->get_mpdf_config('L'));

            // Generar QR code como archivo temporal
            $qr_path = $this->generate_qr_code($user_data['token'] ?? '');

            // Generar HTML del certificado SIN la imagen de fondo
            $html = $this->render_certificate_content($user_data, false);
            $mpdf->WriteHTML($html);

            // Añadir imagen de fondo como elemento de imagen
            $img_path = HV_PLUGIN_PATH . 'assets/img/certificado-bg.jpg';
            if (!file_exists($img_path)) {
                throw new Exception("Imagen de certificado no encontrada en: $img_path");
            }

            // Agregar imagen de fondo (cover completo)
            $mpdf->Image($img_path, 0, 0, 297, 210, 'jpg', '', true, false);

            // Agregar QR code como imagen
            $mpdf->Image($qr_path, 140, 150, 25, 25, 'svg', '', true, false);

            $filename = "certificado-{$user_data['first_name']}-{$user_id}.pdf";
            $filepath = $this->get_pdf_path($filename);

            $mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);

            // Eliminar archivo temporal del QR
            if ($qr_path && file_exists($qr_path)) {
                unlink($qr_path);
            }

            return $filepath;
        } catch (\Exception $e) {
            // Eliminar archivo temporal del QR en caso de error
            if ($qr_path && file_exists($qr_path)) {
                unlink($qr_path);
            }

            error_log("Error generando certificado: " . $e->getMessage());
            throw new Exception("Error al generar el certificado: " . $e->getMessage());
        }
    }

    /**
     * Renderiza el contenido HTML del certificado
     */
    private function render_certificate_content($user_data, $include_image = false)
    {
        $fecha = date('d/m/Y', strtotime($user_data['fecha_recepcion'] ?? 'now'));

        $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <style>
            body {
                margin: 0;
                padding: 0;
                width: 297mm;
                height: 210mm;
                position: relative;
                font-family: dejavusans;
            }
            .nombre {
                position: absolute;
                top: 400px;
                left: 0;
                width: 100%;
                font-size: 28pt;
                font-weight: bold;
                color: #000;
                text-align: center;
                z-index: 2;
            }
            .descripcion {
                position: absolute;
                top: 27%;
                right: 5%;
                text-align: right;
                font-size: 12pt;
                font-weight: bold;
                width: 680px;
                line-height: 1.5;
                z-index: 2;
            }
        </style>
    </head>
    <body>';

        if ($include_image) {
            $img_data = base64_encode(file_get_contents(HV_PLUGIN_PATH . 'assets/img/certificado-bg.jpg'));
            $html .= '<img src="data:image/jpeg;base64,' . $img_data . '" style="position:absolute;width:100%;height:100%;z-index:1">';
        }

        $html .= '

        <div class="nombre">' . htmlspecialchars($user_data['first_name']) . ' ' . htmlspecialchars($user_data['last_name']) . '</div>
        <div class="descripcion">
            RECONOCEMOS FORMALMENTE SU INCORPORACIÓN COMO VOLUNTARIO<br>
            ACTIVO DE LA ORGANIZACIÓN HUMANITARIOS DE LA REPÚBLICA<br>
            DOMINICANA A PARTIR DEL ' . htmlspecialchars($fecha) . ' A:
        </div>
    </body>
    </html>';

        return $html;
    }



    /**
     * Genera la planilla optimizada basada en tu versión funcional
     */
    public function generate_planilla($user_id, $user_data, $qr_url)
    {
        try {
            // Verificar recursos primero
            $this->verify_planilla_resources();

            $mpdf = new Mpdf($this->get_mpdf_config('P'));

            // Generar HTML completo
            $html = $this->render_planilla_complete($user_data, $qr_url);

            // Escribir HTML
            $mpdf->WriteHTML($html);

            // Guardar archivo
            $filename = "planilla-{$user_data['first_name']}-{$user_id}.pdf";
            $filepath = $this->get_pdf_path($filename);
            $mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);

            return $filepath;
        } catch (Exception $e) {
            error_log("Error generando planilla: " . $e->getMessage());
            throw new Exception("Error al generar planilla: " . $e->getMessage());
        }
    }

    private function verify_planilla_resources()
    {
        $required = [
            'logo.png' => HV_PLUGIN_PATH . 'assets/img/logo.png',
            'firma.png' => HV_PLUGIN_PATH . 'assets/img/firma.png'
        ];

        foreach ($required as $name => $path) {
            if (!file_exists($path)) {
                throw new Exception("Archivo requerido no encontrado: {$name}");
            }
        }
    }

    private function render_planilla_complete($user_data, $qr_url)
    {
        $logo_path = HV_PLUGIN_URL . 'assets/img/logo.png';
        $firma_path = HV_PLUGIN_URL . 'assets/img/firma.png';
        $fecha = date('d/m/Y', strtotime($user_data['fecha_recepcion']));

        return '
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        body {
            font-family: Arial;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
        }
        .page-header {
            margin-bottom: 10mm;
            font-size: 10pt;
        }
        .welcome-title {
            color: #004da7;
            text-align: center;
            font-size: 16pt;
            margin: 5mm 0;
        }
        .address {
            font-size: 10pt;
        }
        .salutation {
            font-size: 10pt;
            line-height: 1.5;
            margin-bottom: 5mm;
        }
        .signature-section {
            margin-top: 10mm;
        }
        .signature {
            width: 60mm;
        }
        .signature-image {
            width: 40mm;
            height: auto;
            margin: 2mm 0;
        }
        .id-card {
            width: 70mm;
            border: 1px solid #004da7;
            border-radius: 2mm;
            overflow: hidden;
        }
        .card-header {
            background-color: #004da7;
            color: white;
            padding: 2mm;
            display: flex;
            align-items: center;
        }
        .logo {
            width: 40mm;
            height: auto;
        }
        .card-title {
            flex-grow: 1;
            text-align: center;
            font-size: 10pt;
        }
        .card-qr {
            width: 15mm;
            height: 15mm;
        }
        .card-body {
            padding: 3mm;
        }
        .card-name {
            color: #ce1126;
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 1mm;
        }
        .card-id {
            font-size: 10pt;
        }
        .card-role {
            font-weight: bold;
            color: #004da7;
            font-size: 10pt;
            margin: 2mm 0;
        }
        .card-mission {
            font-size: 9pt;
            line-height: 1.4;
        }
        .footer {
            text-align: center;
            margin-top: 10mm;
            font-size: 9pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with side-by-side layout -->
        <table width="100%" style="margin-bottom: 10mm; font-size: 10pt;">
            <tr valign="top">
                <!-- Dirección institucional -->
                <td width="50%">
                    <img class="logo" src="' . $logo_path . '" alt="Logo"><br />
                    <strong>Dr. Fernando Vásquez Páez</strong><br />
                    Ministerio de Salud Pública<br />
                    Calle Oviedo no. 113, Distrito Nacional<br />
                    Santo Domingo, 10308<br />
                    República Dominicana
                </td>

                <!-- Contacto y fecha -->
                <td width="50%" align="right">
                    Calle Oviedo No.113. Villa Consuelo.<br />
                    Distrito Nacional. República Dominicana<br />
                    Tel. 809-663-7891<br />
                    <strong>' . $fecha . '</strong>
                </td>
            </tr>
        </table>

        <h1 class="welcome-title">Bienvenida Oficial</h1>

        <!-- Content -->
        <div class="salutation">
            <p><strong>Estimado/a ' . htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) . ':</strong></p>
            <p>Nos honra darte la más cordial bienvenida como nuevo voluntario de Humanitarios de la República Dominicana. Tu decisión de integrarte a esta familia solidaria representa un compromiso invaluable con el bienestar de nuestras comunidades.</p>
            <p>Como voluntario, estarás contribuyendo activamente en programas de respuesta ante emergencias, jornadas de capacitación, actividades comunitarias y misiones humanitarias. Además, tendrás acceso a oportunidades formativas, redes de apoyo, y reconocimiento por tu dedicación y entrega.</p>
            <p>Tu participación fortalece nuestra capacidad de actuar con eficiencia y humanidad donde más se necesita. Estamos seguros de que tu energía, tus valores y tu espíritu solidario serán un aporte significativo a cada causa que emprendamos.</p>
            <p>Gracias por tu compromiso. Estamos felices de tenerte con nosotros.</p>
        </div>

        <!-- Signature Section in Table -->
        <table width="100%" style="margin-top: 10mm; font-size: 10pt;">
            <tr valign="top">
                <!-- Bloque de Firma -->
                <td width="50%">
                    <table style="width: 100%; font-size: 10pt;">
                        <tr>
                            <td style="text-align: left;">
                                <p>Con gratitud,</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">
                                <img src="' . $firma_path . '" alt="Firma" 
                                    style="width: 45mm; height: auto; margin: 2mm 0;" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">
                                <p>Humanitarios de la República Dominicana</p>
                            </td>
                        </tr>
                    </table>
                </td>



                <!-- ID Card -->
                <td width="50%" align="right">
                    <table style="width: 75mm; border: 1px solid #004da7; border-radius: 3mm; font-family: Arial; font-size: 9pt;">
                        <!-- Encabezado con fondo azul oscuro -->
                        <tr>
                            <td colspan="3" style="background-color: #f2f2f2; color: white; padding: 3mm; text-align: center;">
                                <table width="100%">
                                    <tr>
                                        <!-- Logo / titulo-->
                                        <td style="width: 20mm;">
                                            <img src="' . $logo_path . '" alt="Logo" style="width: 35mm; height: auto; padding: 2mm;" />
                                            <strong style="font-size: 10pt;color: #004da7; padding:2mm;">Voluntariado Nacional</strong>
                                        </td>

                                        <!-- QR Code -->
                                        <td style="width: 35mm; text-align: right;">
                                            <img src="' . $qr_url . '" alt="QR Code" style="width: 25mm; height: 25mm;" />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                    <!-- Información del voluntario -->
                    <tr>
                        <td colspan="3" style="padding: 4mm;">
                            <p style="color: #ce1126; font-weight: bold; font-size: 11pt; margin: 0;">
                                ' . htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) . '
                            </p>
                            <p style="margin: 2mm 0 0 0; font-size: 10pt;">
                                ID: <strong>' . htmlspecialchars($user_data['code']) . '</strong>
                            </p>
                            <p style="font-weight: bold; color: #004da7; font-size: 10pt; margin: 3mm 0 4mm 0;">
                                Certificación de membresía
                            </p>
                            <p style="font-size: 9pt; line-height: 1.5; text-align: justify; margin: 0;">
                                Transformar vidas promoviendo el bienestar, la salud y el desarrollo integral de comunidades vulnerables.
                            </p>
                        </td>
                    </tr>

                    <!-- Pie decorativo -->
                    <tr>
                        <td colspan="3" style="background-color: #f2f2f2; text-align: center; padding: 2mm; color: #666;">
                            <em>Humanitarios de la República Dominicana</em>
                        </td>
                    </tr>
                </table>

                </td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>Calle Oviedo No.113. Villa Consuelo. Distrito Nacional. República Dominicana.</p>
            <p>Tel. 809-663-7891. E-mail: humanitarios@gmail.com</p>
        </div>
    </div>
</body>
</html>
';
    }

    /**
     * Genera un código QR con URL de verificación por token
     * Recibe el token directamente como parámetro
     */
    public function generate_qr_code($token)
    {
        // Crear la URL de verificación
        $qr_url = home_url("/verificacion-voluntario/?hv_unique_code={$token}");

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        // Generar QR con la URL en lugar del token
        $svgContent = $writer->writeString($qr_url);

        // Guardar como archivo temporal
        $upload_dir = wp_upload_dir();
        $temp_dir = trailingslashit($upload_dir['basedir']) . 'temp/';
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }

        $filename = 'qr_' . md5($token) . '.svg';
        $filepath = $temp_dir . $filename;

        file_put_contents($filepath, $svgContent);

        return $filepath;
    }

    /**
     * Genera un código QR en formato SVG base64
     */
    /*   public function generate_qr_code($code)
    {
        $qr_url = home_url("/verificacion-voluntario/?hv_unique_code={$code}");

        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($qr_url);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }*/

    /**
     * Obtiene la ruta para guardar PDFs
     */
    private function get_pdf_path($filename)
    {
        $upload_dir = wp_upload_dir();
        if (empty($upload_dir['basedir'])) {
            throw new Exception('No se pudo obtener el directorio de uploads de WordPress.');
        }
        $save_path = trailingslashit($upload_dir['basedir']) . 'humanitarios-pdfs/';

        if (!file_exists($save_path)) {
            wp_mkdir_p($save_path);
            file_put_contents($save_path . '.htaccess', "Order deny,allow\nDeny from all");
        }

        return $save_path . sanitize_file_name($filename);
    }


    /**
     * Verifica si un PDF ya existe
     */
    public function pdf_exists($user_id, $type = 'certificate')
    {
        $user_data = [
            'first_name' => get_user_meta($user_id, 'first_name', true)
        ];

        $filename = ($type === 'certificate')
            ? "certificado-{$user_data['first_name']}-{$user_id}.pdf"
            : "planilla-{$user_data['first_name']}-{$user_id}.pdf";

        $file_path = $this->get_pdf_path($filename);

        return file_exists($file_path) ? $file_path : false;
    }
}
