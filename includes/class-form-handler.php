<?php
class Volunteer_Form_Handler
{
    public function __construct()
    {
        add_action('admin_post_nopriv_volunteer_submit', [$this, 'handle_submission']);
        add_action('admin_post_volunteer_submit', [$this, 'handle_submission']);
    }

    public function handle_submission()
    {
        try {
            // 1. Verificar nonce de seguridad
            if (!isset($_POST['volunteer_nonce']) || !wp_verify_nonce($_POST['volunteer_nonce'], 'volunteer_form_action')) {
                throw new Exception('Nonce de verificación inválido.');
            }

            // 2. Validar campos requeridos
            $required_fields = [
                'full_name',
                'email',
                'phone',
                'birth_date',
                'province',
                'skills',
                'weekend_availability',
                'travel_availability',
                'has_experience',
                'terms_conditions'
            ];

            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("El campo $field es requerido.");
                }
            }

            // 3. Validar y sanitizar datos
            $data = [
                'full_name' => sanitize_text_field($_POST['full_name']),
                'id_number' => sanitize_text_field($_POST['id_number'] ?? ''),
                'birth_date' => sanitize_text_field($_POST['birth_date']),
                'province' => sanitize_text_field($_POST['province']),
                'phone' => sanitize_text_field($_POST['phone']),
                'email' => sanitize_email($_POST['email']),
                'skills' => sanitize_text_field($_POST['skills']),
                'skills_other' => sanitize_text_field($_POST['skills_other'] ?? ''),
                'weekend_availability' => sanitize_text_field($_POST['weekend_availability']),
                'travel_availability' => sanitize_text_field($_POST['travel_availability']),
                'has_experience' => sanitize_text_field($_POST['has_experience']),
                'experience_desc' => sanitize_textarea_field($_POST['experience_desc'] ?? ''),
                'nationality' => sanitize_text_field($_POST['nationality'] ?? ''),
                'gender' => sanitize_text_field($_POST['gender'] ?? ''),
                'blood_type' => sanitize_text_field($_POST['blood_type'] ?? ''),
                'shirt_size' => sanitize_text_field($_POST['shirt_size'] ?? ''),
                'profession' => sanitize_text_field($_POST['profession'] ?? ''),
                'medical_condition' => sanitize_text_field($_POST['medical_condition'] ?? ''),
                'references' => sanitize_textarea_field($_POST['references'] ?? ''),
            ];

            // Validación adicional para email
            if (!is_email($data['email'])) {
                throw new Exception('El email proporcionado no es válido.');
            }

            // 4. Manejar subida de archivo
            $document_id = 0;
            if (!empty($_FILES['identity_document']) && $_FILES['identity_document']['error'] === UPLOAD_ERR_OK) {
                $upload = wp_handle_upload($_FILES['identity_document'], [
                    'test_form' => false,
                    'mimes' => [
                        'jpg|jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'pdf' => 'application/pdf'
                    ]
                ]);

                if ($upload && !isset($upload['error'])) {
                    $attachment = [
                        'post_mime_type' => $upload['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    ];

                    $document_id = wp_insert_attachment($attachment, $upload['file']);
                    if (is_wp_error($document_id)) {
                        throw new Exception('Error al guardar el documento de identidad.');
                    }

                    // Generar metadatos para la imagen (si es una imagen)
                    if (strpos($upload['type'], 'image') !== false) {
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        $attach_data = wp_generate_attachment_metadata($document_id, $upload['file']);
                        wp_update_attachment_metadata($document_id, $attach_data);
                    }
                } else {
                    throw new Exception('Error al subir el documento: ' . $upload['error']);
                }
            }

            // 5. Crear usuario
            $user_data = [
                'user_login' => $data['email'],
                'user_email' => $data['email'],
                'user_pass' => wp_generate_password(),
                'display_name' => $data['full_name'],
                'first_name' => $data['full_name'],
                'role' => 'subscriber'
            ];

            $user_id = wp_insert_user($user_data);

            if (is_wp_error($user_id)) {
                throw new Exception($user_id->get_error_message());
            }

            // 6. Guardar campos personalizados como metadatos
            $meta_fields = [
                'id_number' => 'hv_id_number',
                'birth_date' => 'hv_birth_date',
                'province' => 'hv_province',
                'phone' => 'hv_phone',
                'skills' => 'hv_skills',
                'skills_other' => 'hv_skills_other',
                'weekend_availability' => 'hv_weekend_availability',
                'travel_availability' => 'hv_travel_availability',
                'has_experience' => 'hv_has_experience',
                'experience_desc' => 'hv_experience_desc',
                'nationality' => 'hv_nationality',
                'gender' => 'hv_gender',
                'blood_type' => 'hv_blood_type',
                'shirt_size' => 'hv_shirt_size',
                'profession' => 'hv_profession',
                'medical_condition' => 'hv_medical_condition',
                'references' => 'hv_references',
                'identity_document' => 'hv_identity_document', // Guardaremos el ID del attachment
            ];

            foreach ($meta_fields as $field => $meta_key) {
                if (isset($data[$field])) {
                    update_user_meta($user_id, $meta_key, $data[$field]);
                }
            }

            // Guardar el ID del documento de identidad
            if ($document_id) {
                update_user_meta($user_id, 'hv_identity_document', $document_id);
            }

            // Marcar como usuario employer
            update_user_meta($user_id, '_user_type', 'employers');

            // Estado inicial: no verificado
            update_user_meta($user_id, 'hv_status', 'pending');
            update_user_meta($user_id, '_is_verified', 'no');
            update_user_meta($user_id, 'identity_verified', '0');

            // 7. Disparar acciones para emails y notificaciones
            // (Se implementará más adelante)
            do_action('volunteer_registered', $user_id, $data);

            // 8. Redirección con parámetro de éxito
            wp_redirect(add_query_arg('registration', 'success', home_url('/gracias')));
            exit;
        } catch (Exception $e) {
            // Registrar el error
            error_log('Error en Volunteer_Form_Handler: ' . $e->getMessage());

            // Redirección con parámetro de error
            wp_redirect(add_query_arg('registration', 'error', wp_get_referer()));
            exit;
        }
    }
}

// Inicialización condicional para evitar conflictos
if (!class_exists('Volunteer_Form_Handler')) {
    new Volunteer_Form_Handler();
}
