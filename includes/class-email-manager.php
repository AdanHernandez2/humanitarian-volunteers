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
            HV_PLUGIN_PATH . 'templates/email/admin/email-admin-new.php',
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
            HV_PLUGIN_PATH . 'templates/email/user/email-user-pending.php',
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

        // Obtener rutas de los PDFs
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/humanitarios-pdfs/';

        $certificate_path = $pdf_dir . "certificado-{$user->first_name}-{$user_id}.pdf";
        $planilla_path = $pdf_dir . "planilla-{$user->first_name}-{$user_id}.pdf";

        // Verificar que existen
        $attachments = [];
        if (file_exists($certificate_path)) {
            $attachments[] = $certificate_path;
        }
        if (file_exists($planilla_path)) {
            $attachments[] = $planilla_path;
        }

        $this->send_email(
            $user->user_email,
            '¡Verificación Completada!',
            HV_PLUGIN_PATH . 'templates/email/user/email-user-verified.php',
            [
                'name' => $user->first_name,
                'code' => $code,
                'site_name' => get_bloginfo('name')
            ],
            $attachments // Adjuntar los PDFs aquí
        );
    }

    public function send_update_notification($user_id, $data)
    {
        $user = get_userdata($user_id);

        $this->send_email(
            get_option('admin_email'),
            'Perfil de Voluntario Actualizado',
            HV_PLUGIN_PATH . 'templates/email/user/email-admin-updated.php',
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

        // Preparar archivos adjuntos
        $attachment_paths = [];
        foreach ($attachments as $attachment) {
            if (is_string($attachment) && file_exists($attachment)) {
                // Si es una ruta de archivo válida, agregar directamente
                $attachment_paths[] = $attachment;
            } elseif (is_array($attachment) && isset($attachment['content'])) {
                // Si es contenido en memoria, crear archivo temporal
                $temp_file = tmpfile();
                fwrite($temp_file, $attachment['content']);
                $meta = stream_get_meta_data($temp_file);
                $attachment_paths[] = $meta['uri'];
                // Guardar handle para limpiar después
                $temp_files[] = $temp_file;
            }
        }

        // Enviar email
        $result = wp_mail($to, $subject, $message, $headers, $attachment_paths);

        // Limpiar archivos temporales
        if (isset($temp_files)) {
            foreach ($temp_files as $file) {
                if (is_resource($file)) {
                    fclose($file);
                }
            }
        }

        return $result;
    }
}
new Email_Manager();
