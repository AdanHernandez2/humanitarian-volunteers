<?php
class Email_Manager {
    public function __construct() {
        add_action('volunteer_registered', [$this, 'send_registration_emails'], 10, 2);
        add_action('volunteer_verified', [$this, 'send_verification_email'], 10, 1);
    }

    public function send_registration_emails($post_id, $data) {
        // Email a administrador
        $this->send_email(
            get_option('admin_email'),
            'Nuevo Voluntario Registrado',
            HV_PLUGIN_PATH . 'templates/email-admin-new.html',
            $data
        );

        // Email a usuario (pendiente)
        $this->send_email(
            $data['email'],
            'Registro en Proceso',
            HV_PLUGIN_PATH . 'templates/email-user-pending.html',
            $data
        );
    }

    public function send_verification_email($post_id) {
        $volunteer = get_post($post_id);
        $email = carbon_get_post_meta($post_id, 'hv_email');
        $code = carbon_get_post_meta($post_id, 'hv_unique_code');

        // Generar PDFs
        $pdf_generator = new PDF_Generator();
        $pdf_content = $pdf_generator->generate_volunteer_card($post_id);
        
        // Enviar email con adjuntos
        $this->send_email(
            $email,
            '¡Verificación Completada!',
            HV_PLUGIN_PATH . 'templates/email-user-verified.html',
            ['code' => $code],
            [
                [
                    'content' => $pdf_content,
                    'filename' => 'credencial-voluntario.pdf',
                    'type' => 'application/pdf'
                ]
            ]
        );
    }

    private function send_email($to, $subject, $template, $data, $attachments = []) {
        // Implementación con PHPMailer
        // (Código completo requiere configuración SMTP)
    }
}
new Email_Manager();