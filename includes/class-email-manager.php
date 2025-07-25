<?php
class Email_Manager
{
    public function __construct()
    {
        add_action('volunteer_registered', [$this, 'send_registration_emails'], 10, 2);
        add_action('volunteer_verified', [$this, 'send_verification_email'], 10, 1);
        add_action('volunteer_updated', [$this, 'send_update_notification'], 10, 2);
    }

    public function send_registration_emails($user_id, $data)
    {
        $user = get_userdata($user_id);

        // Email a administrador
        $this->send_email(
            get_option('admin_email'),
            'Nuevo Voluntario Registrado',
            HV_PLUGIN_PATH . 'templates/email-admin-new.php',
            [
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->user_email,
                'phone' => get_user_meta($user_id, 'hv_phone', true),
                'province' => get_user_meta($user_id, 'hv_province', true),
                'skills' => get_user_meta($user_id, 'hv_skills', true),
                'profile_link' => admin_url('admin.php?page=volunteer_profile&user_id=' . $user_id)
            ]
        );

        // Email a usuario (pendiente)
        $this->send_email(
            $user->user_email,
            'Registro en Proceso',
            HV_PLUGIN_PATH . 'templates/email-user-pending.php',
            [
                'name' => $user->first_name,
                'site_name' => get_bloginfo('name'),
                'contact_email' => get_option('admin_email')
            ]
        );
    }

    public function send_verification_email($user_id)
    {
        $user = get_userdata($user_id);
        $code = get_user_meta($user_id, 'hv_unique_code', true);

        // Enviar email con adjuntos
        $this->send_email(
            $user->user_email,
            '¡Verificación Completada!',
            HV_PLUGIN_PATH . 'templates/email-user-verified.php',
            [
                'name' => $user->first_name,
                'code' => $code,
                'site_name' => get_bloginfo('name')
            ],
            [
                // Descomentar cuando tengas el generador de PDF
                /*
                [
                    'content' => $pdf_content,
                    'filename' => 'credencial-voluntario-' . $user_id . '.pdf',
                    'type' => 'application/pdf'
                ]
                */]
        );
    }

    public function send_update_notification($user_id, $data)
    {
        $user = get_userdata($user_id);

        $this->send_email(
            get_option('admin_email'),
            'Perfil de Voluntario Actualizado',
            HV_PLUGIN_PATH . 'templates/email-admin-updated.php',
            [
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->user_email,
                'changes' => $data,
                'profile_link' => admin_url('admin.php?page=volunteer_profile&user_id=' . $user_id)
            ]
        );
    }

    private function send_email($to, $subject, $template, $data = [], $attachments = [])
    {
        // Verificar si existe la plantilla
        if (!file_exists($template)) {
            error_log('Plantilla de email no encontrada: ' . $template);
            return false;
        }

        // Renderizar plantilla con datos
        ob_start();
        extract($data);
        include $template;
        $message = ob_get_clean();

        // Configurar headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];

        // Preparar archivos adjuntos temporales
        $temp_files = [];
        foreach ($attachments as $attachment) {
            if (isset($attachment['content'])) {
                // Crear archivo temporal
                $temp_file = tmpfile();
                fwrite($temp_file, $attachment['content']);
                $meta = stream_get_meta_data($temp_file);
                $temp_files[] = [
                    'handle' => $temp_file,
                    'path' => $meta['uri']
                ];
            }
        }

        // Obtener rutas de los adjuntos
        $attachment_paths = [];
        foreach ($temp_files as $file) {
            $attachment_paths[] = $file['path'];
        }

        // Enviar email
        $result = wp_mail($to, $subject, $message, $headers, $attachment_paths);

        // Limpiar archivos temporales
        foreach ($temp_files as $file) {
            if (is_resource($file['handle'])) {
                fclose($file['handle']);
            }
        }

        return $result;
    }
}
new Email_Manager();
