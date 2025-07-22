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
            'default_font' => 'dejavusans',
            'tempDir' => $this->get_temp_dir(),
            'img_dpi' => 96,
            'showImageErrors' => true,
            'pcre.backtrack_limit' => '10000000', // Aumentamos el límite
            'pcre.recursion_limit' => '10000000', // Aumentamos el límite
            'allow_output_buffering' => true
        ];
    }

    /**
     * Genera el certificado optimizado
     */
    public function generate_certificate($user_id, $user_data)
    {
        try {
            $mpdf = new Mpdf($this->get_mpdf_config('L'));

            // 1. Primero el HTML sin la imagen
            $html = $this->render_certificate_template($user_data, false);
            $mpdf->WriteHTML($html);

            // 2. Añadir la imagen después como elemento separado
            $img_path = HV_PLUGIN_PATH . 'assets/img/certificado-bg.jpg';
            if (!file_exists($img_path)) {
                throw new Exception("Imagen de certificado no encontrada en: $img_path");
            }

            $mpdf->Image($img_path, 0, 0, 297, 210, 'jpg', '', true, false);

            $filename = "certificado-{$user_data['first_name']}-{$user_id}.pdf";
            $filepath = $this->get_pdf_path($filename);

            $mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);

            return $filepath;
        } catch (\Exception $e) {
            error_log("Error generando certificado: " . $e->getMessage());
            throw new Exception("Error al generar el certificado: " . $e->getMessage());
        }
    }

    /**
     * Render optimizado para certificado
     */
    private function render_certificate_template($user_data, $include_image = true)
    {
        $fecha = date('d/m/Y', strtotime($user_data['fecha_recepcion']));

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
                    top: 40%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    font-size: 28pt;
                    font-weight: bold;
                    color: #000;
                    width: 80%;
                    text-align: center;
                    z-index: 2;
                }
                .descripcion {
                    position: absolute;
                    top: 60%;
                    right: 10%;
                    text-align: right;
                    font-size: 14pt;
                    width: 70%;
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
     * Genera la planilla optimizada
     */
    public function generate_planilla($user_id, $user_data, $qr_url)
    {
        try {
            $mpdf = new Mpdf($this->get_mpdf_config('P'));

            // Dividir el HTML en partes más pequeñas
            $html_header = $this->render_planilla_header($user_data);
            $html_body = $this->render_planilla_body($user_data, $qr_url);
            $html_footer = $this->render_planilla_footer();

            $mpdf->WriteHTML($html_header);
            $mpdf->WriteHTML($html_body);
            $mpdf->WriteHTML($html_footer);

            $filename = "planilla-{$user_data['first_name']}-{$user_id}.pdf";
            $filepath = $this->get_pdf_path($filename);

            $mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);

            return $filepath;
        } catch (\Exception $e) {
            error_log("Error generando planilla: " . $e->getMessage());
            throw new \Exception("Error al generar la planilla: " . $e->getMessage());
        }
    }

    /**
     * Divide la plantilla en partes más pequeñas
     */
    private function render_planilla_header($user_data)
    {
        $logo_path = HV_PLUGIN_URL . 'assets/img/logo.png';
        $fecha = date('d/m/Y', strtotime($user_data['fecha_recepcion']));

        return '
        <div class="page-header">
            <img src="' . $logo_path . '" alt="Logo" style="width:30mm;height:auto;">
            <div class="contact-info" style="text-align:right;font-size:10pt;">
                <p>Calle Oviedo No.113. Villa Consuelo. Distrito Nacional.</p>
                <p>República Dominicana. Tel. 809-663-7891.</p>
                <p>E-mail: humanitarios@gmail.com</p>
            </div>
        </div>
        <h1 class="welcome-title" style="color:#004da7;text-align:center;font-size:16pt;margin:5mm 0;">
            Bienvenida Oficial
        </h1>
        <div class="date">' . $fecha . '</div>';
    }

    private function render_planilla_body($user_data, $qr_url)
    {
        $logo_path = HV_PLUGIN_URL . 'assets/img/logo.png';

        return '
        <div class="modern-id-card" style="border:1px solid #004da7;border-radius:3mm;overflow:hidden;margin-top:5mm;">
            <div class="card-header" style="background-color:#004da7;color:white;padding:3mm;display:flex;align-items:center;">
                <img src="' . $logo_path . '" alt="Logo" style="width:15mm;height:15mm;">
                <h3 style="flex-grow:1;text-align:center;">Voluntariado Nacional</h3>
                <img src="' . $qr_url . '" alt="QR Code" style="width:15mm;height:15mm;">
            </div>
            <div class="info" style="padding:3mm;">
                <p class="name" style="color:#ce1126;font-weight:bold;">
                    ' . htmlspecialchars($user_data['first_name']) . ' ' . htmlspecialchars($user_data['last_name']) . '
                </p>
                <p>ID: ' . htmlspecialchars($user_data['code']) . '</p>
            </div>
        </div>';
    }

    private function render_planilla_footer()
    {
        return '
        <div class="footer" style="text-align:center;margin-top:10mm;font-size:9pt;color:#666;">
            <p>Calle Oviedo No.113. Villa Consuelo. Distrito Nacional. República Dominicana.</p>
            <p>Tel. 809-663-7891. E-mail: humanitarios@gmail.com</p>
        </div>';
    }

    /**
     * Genera un código QR en formato SVG base64
     */
    public function generate_qr_code($code)
    {
        $qr_url = home_url("/verificacion-voluntario/?hv_unique_code={$code}");

        $renderer = new ImageRenderer(
            new RendererStyle(150),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($qr_url);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

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
