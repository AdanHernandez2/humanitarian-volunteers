<?php
class Volunteer_Form_Handler
{
    public function __construct()
    {
        add_action('wp_ajax_volunteer_submit', [$this, 'handle_submission']);
        add_action('wp_ajax_nopriv_volunteer_submit', [$this, 'handle_submission']);
    }

    public function handle_submission()
    {
        $response = [
            'success' => false,
            'message' => 'Error desconocido'
        ];

        try {
            // Verificar nonce
            if (!isset($_POST['volunteer_nonce'])) {
                throw new Exception('Nonce de seguridad no proporcionado');
            }

            if (!wp_verify_nonce($_POST['volunteer_nonce'], 'volunteer_form_action')) {
                throw new Exception('Error de seguridad. Recarga la página');
            }

            // Validar campos requeridos
            $required_fields = [
                'first_name',
                'last_name',
                'email',
                'phone',
                'birth_date',
                'province',
                'skills',
                'weekend_availability',
                'travel_availability',
                'has_experience'
            ];

            $missing_fields = [];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    $missing_fields[] = $field;
                }
            }

            if (!empty($missing_fields)) {
                throw new Exception('Faltan campos obligatorios: ' . implode(', ', $missing_fields));
            }

            if (empty($_POST['terms_conditions'])) {
                throw new Exception('Debes aceptar los términos y condiciones');
            }

            // Sanitizar datos
            $data = [
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name' => sanitize_text_field($_POST['last_name']),
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

            if (!is_email($data['email'])) {
                throw new Exception('Email inválido');
            }

            // 1. Verificar si el usuario ya existe
            $user = get_user_by('email', $data['email']);
            $is_update = false;
            $user_id = 0;

            if ($user) {
                // Usuario existente: modo actualización
                $user_id = $user->ID;
                $is_update = true;

                // Actualizar datos básicos del usuario
                wp_update_user([
                    'ID' => $user_id,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'display_name' => $data['first_name'] . ' ' . $data['last_name']
                ]);
            } else {
                // Crear nuevo usuario
                $user_data = [
                    'user_login' => $data['email'],
                    'user_email' => $data['email'],
                    'user_pass' => wp_generate_password(),
                    'display_name' => $data['first_name'] . ' ' . $data['last_name'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'role' => 'subscriber'
                ];

                $user_id = wp_insert_user($user_data);

                if (is_wp_error($user_id)) {
                    throw new Exception('Error al crear usuario: ' . $user_id->get_error_message());
                }

                // Establecer metadatos iniciales solo para nuevos usuarios
                update_user_meta($user_id, '_user_type', 'employers');
                update_user_meta($user_id, 'hv_status', 'pending');
                update_user_meta($user_id, '_is_verified', 'no');
                update_user_meta($user_id, 'identity_verified', '0');
            }

            // 2. Manejar documento de identidad (opcional para actualizaciones)
            $document_id = 0;
            $current_document = get_user_meta($user_id, 'hv_identity_document', true);

            if (!empty($_FILES['identity_document']) && $_FILES['identity_document']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
                $file_type = $_FILES['identity_document']['type'];

                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception('Formato no permitido (solo JPG, PNG, PDF)');
                }

                $max_size = 5 * 1024 * 1024;
                if ($_FILES['identity_document']['size'] > $max_size) {
                    throw new Exception('Archivo demasiado grande (máx 5MB)');
                }

                $upload_overrides = ['test_form' => false];
                $upload = wp_handle_upload($_FILES['identity_document'], $upload_overrides);

                if ($upload && !isset($upload['error'])) {
                    $attachment = [
                        'post_mime_type' => $upload['type'],
                        'post_title' => sanitize_file_name($_FILES['identity_document']['name']),
                        'post_content' => '',
                        'post_status' => 'private'
                    ];

                    $document_id = wp_insert_attachment($attachment, $upload['file']);

                    if (is_wp_error($document_id)) {
                        throw new Exception('Error al guardar documento');
                    }

                    if (strpos($upload['type'], 'image') !== false) {
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        $attach_data = wp_generate_attachment_metadata($document_id, $upload['file']);
                        wp_update_attachment_metadata($document_id, $attach_data);
                    }

                    // Eliminar documento anterior si existe
                    if ($current_document && is_numeric($current_document)) {
                        wp_delete_attachment($current_document, true);
                    }
                } else {
                    throw new Exception('Error al subir: ' . ($upload['error'] ?? ''));
                }
            } elseif ($is_update && $current_document) {
                // Mantener documento existente en actualizaciones
                $document_id = $current_document;
            } elseif (!$is_update) {
                // Para nuevos registros, documento es obligatorio
                $error_code = $_FILES['identity_document']['error'] ?? UPLOAD_ERR_NO_FILE;

                if ($error_code === UPLOAD_ERR_NO_FILE) {
                    throw new Exception('Debes subir documento de identidad');
                } else {
                    $upload_errors = [
                        UPLOAD_ERR_INI_SIZE => 'Archivo excede tamaño máximo',
                        UPLOAD_ERR_FORM_SIZE => 'Archivo excede tamaño máximo',
                        UPLOAD_ERR_PARTIAL => 'Subida parcial',
                        UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
                        UPLOAD_ERR_CANT_WRITE => 'Error al escribir en disco',
                        UPLOAD_ERR_EXTENSION => 'Extensión detuvo la subida'
                    ];
                    throw new Exception($upload_errors[$error_code] ?? 'Error desconocido');
                }
            }

            // 3. Guardar metadatos
            $meta_map = [
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
                'identity_document' => 'hv_identity_document',
            ];

            foreach ($meta_map as $field => $meta_key) {
                if (isset($data[$field])) {
                    update_user_meta($user_id, $meta_key, $data[$field]);
                }
            }

            // Guardar documento si se subió uno nuevo
            if ($document_id) {
                update_user_meta($user_id, 'hv_identity_document', $document_id);
            }

            // 4. Disparar acciones según tipo de operación
            if ($is_update) {
                do_action('volunteer_updated', $user_id, $data);
                $response['message'] = '¡Perfil actualizado correctamente!';
            } else {
                do_action('volunteer_registered', $user_id, $data);
                $response['message'] = '¡Registro exitoso! Tu solicitud ha sido recibida.';
            }

            $response['success'] = true;
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        wp_send_json($response);
    }
}

if (!class_exists('Volunteer_Form_Handler')) {
    new Volunteer_Form_Handler();
}
