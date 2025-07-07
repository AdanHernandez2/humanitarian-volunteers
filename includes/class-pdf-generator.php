<?php
// use Dompdf\Dompdf;
// use Endroid\QrCode\QrCode;

// class PDF_Generator {
//     public function generate_volunteer_card($post_id) {
//         $data = [
//             'name' => carbon_get_post_meta($post_id, 'hv_full_name'),
//             'code' => carbon_get_post_meta($post_id, 'hv_unique_code'),
//             'qr_url' => home_url('/verificar-voluntario?code=' . $code)
//         ];

//         // Generar QR
//         $qrCode = new QrCode($data['qr_url']);
//         $qrCode->setSize(100);
//         $data['qr'] = $qrCode->writeDataUri();

//         // Renderizar HTML con Bootstrap
//         ob_start();
//         include HV_PLUGIN_PATH . 'templates/pdf-card-template.php';
//         $html = ob_get_clean();

//         // Configurar Dompdf
//         $dompdf = new Dompdf();
//         $dompdf->loadHtml($html);
//         $dompdf->setPaper('A4', 'portrait');
//         $dompdf->render();

//         return $dompdf->output();
//     }
//}