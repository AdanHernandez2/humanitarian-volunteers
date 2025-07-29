<?php
class Volunteer_Profile
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_check_user_document', [$this, 'check_user_document']);
        add_action('wp_ajax_nopriv_check_user_document', [$this, 'check_user_document']);
        add_action('wp_ajax_verify_volunteer', [$this, 'verify_volunteer']); // Nueva acción AJAX
        add_action('wp_ajax_resend_credentials', [$this, 'resend_credentials']);
    }

    public function add_admin_menu()
    {
        add_submenu_page(
            null, // No mostrar en menú
            'Perfil de Voluntario',
            'Perfil de Voluntario',
            'manage_options',
            'volunteer_profile',
            [$this, 'render_profile_page']
        );
    }

    /**
     * Verifica si el usuario tiene documento subido
     */
    public function check_user_document()
    {
        // Verificar seguridad
        check_ajax_referer('hv_profile_nonce', 'security');

        if (!isset($_POST['user_id'])) {
            wp_send_json_error('User ID faltante');
        }

        $user_id = intval($_POST['user_id']);
        $has_document = !empty(get_user_meta($user_id, 'hv_identity_document', true));

        wp_send_json_success(['has_document' => $has_document]);
    }

    public function render_profile_page()
    {
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

        if (!$user_id) {
            echo '<div class="error"><p>Usuario no especificado</p></div>';
            return;
        }

        $user = get_userdata($user_id);
        $is_verified = get_user_meta($user_id, '_is_verified', true) === 'yes' ||
            get_user_meta($user_id, 'identity_verified', true) === '1';

        // Nuevos campos
        $unique_code = get_user_meta($user_id, 'hv_unique_code', true);
        $date_received = get_user_meta($user_id, 'hv_date_received', true);
        $received_observations = get_user_meta($user_id, 'hv_received_observations', true);

        // Verificar si es un employer
        $is_employer = get_user_meta($user_id, '_user_type', true) === 'employers';

        // Obtener campos de selección múltiple
        $interest_areas = maybe_unserialize(get_user_meta($user_id, 'hv_interest_areas', true));
        $availability_days = maybe_unserialize(get_user_meta($user_id, 'hv_availability_days', true));
?>
        <div class="wrap volunteer-profile">
            <h1 class="wp-heading-inline">Perfil de Voluntario</h1>
            <a href="<?php echo admin_url('admin.php?page=volunteers'); ?>" class="page-title-action">
                ← Volver a la lista
            </a>

            <?php if ($is_employer): ?>
                <div class="notice notice-info">
                    <p>Este usuario es un voluntario.</p>
                </div>
            <?php endif; ?>

            <div class="container-cars-volunteer_profiler-grid">
                <!-- Información Personal -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Información Personal</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre completo:</strong> <?php echo esc_html($user->display_name); ?></p>
                                <p><strong>Cédula:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_id_number', true)); ?></p>
                                <p><strong>Fecha de nacimiento:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_birth_date', true)); ?></p>
                                <p><strong>Provincia de residencia:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_province', true)); ?></p>
                                <p><strong>Dirección:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_address', true)); ?></p>
                                <p><strong>Estado civil:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_marital_status', true)); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Teléfono/WhatsApp:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_phone', true)); ?></p>
                                <p><strong>Correo electrónico:</strong> <?php echo esc_html($user->user_email); ?></p>
                                <p><strong>Estado:</strong>
                                    <?php if ($is_verified) : ?>
                                        <span class="badge bg-success">✅ Verificado</span>
                                        <?php if ($unique_code): ?>
                                            <br><strong>Código único:</strong> <?php echo esc_html($unique_code); ?>
                                            <br><strong>Fecha verificación:</strong> <?php echo esc_html(date('d/m/Y H:i', strtotime($date_received))); ?>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <span class="badge bg-danger">❌ No verificado</span>
                                    <?php endif; ?>
                                </p>

                                <?php if ($is_verified && current_user_can('manage_options')) : ?>
                                    <button id="resend-credentials-btn" class="button button-secondary" data-user-id="<?php echo esc_attr($user_id); ?>">Reenviar credenciales</button>
                                    <span id="resend-credentials-status"></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Académica -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Información Académica</h2>
                    </div>
                    <div class="card-body">
                        <p><strong>Nivel académico alcanzado:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_education_level', true)); ?></p>
                    </div>
                </div>

                <!-- Disponibilidad y Áreas de Interés -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Disponibilidad y Áreas de Interés</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h5 class="mb-2">Áreas de interés:</h5>
                            <?php if (!empty($interest_areas) && is_array($interest_areas)) : ?>
                                <ul>
                                    <?php foreach ($interest_areas as $area) : ?>
                                        <li><?php echo esc_html($area); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <p class="text-muted">No se han especificado áreas de interés</p>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-2">Disponibilidad:</h5>
                                <?php if (!empty($availability_days) && is_array($availability_days)) : ?>
                                    <p><strong>Días:</strong> <?php echo esc_html(implode(', ', $availability_days)); ?></p>
                                <?php else : ?>
                                    <p class="text-muted">No se han especificado días</p>
                                <?php endif; ?>
                                <p><strong>Horas por día:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_availability_hours', true)); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Disponible fines de semana:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_weekend_availability', true)); ?></p>
                                <p><strong>Puede viajar al interior:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_travel_availability', true)); ?></p>
                                <p><strong>Disponibilidad para misiones internacionales:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_international_availability', true)); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Experiencia en Voluntariado -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Experiencia en Voluntariado</h2>
                    </div>
                    <div class="card-body">
                        <p><strong>¿Tiene experiencia?:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_has_experience', true)); ?></p>
                        <p><strong>Descripción breve:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_experience_desc', true)); ?></p>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Información Adicional</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nacionalidad:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_nationality', true)); ?></p>
                                <p><strong>Género:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_gender', true)); ?></p>
                                <p><strong>Tipo de sangre:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_blood_type', true)); ?></p>
                                <p><strong>Limitaciones físicas o de salud:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_physical_limitations', true)); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Talla de camiseta:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_shirt_size', true)); ?></p>
                                <p><strong>Profesión:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_profession', true)); ?></p>
                                <p><strong>Condición médica:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_medical_condition', true)); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referencias Personales -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Referencias Personales</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Referencia 1:</strong><br>
                                    Nombre: <?php echo esc_html(get_user_meta($user_id, 'hv_reference1_name', true)); ?><br>
                                    Teléfono: <?php echo esc_html(get_user_meta($user_id, 'hv_reference1_phone', true)); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Referencia 2:</strong><br>
                                    Nombre: <?php echo esc_html(get_user_meta($user_id, 'hv_reference2_name', true)); ?><br>
                                    Teléfono: <?php echo esc_html(get_user_meta($user_id, 'hv_reference2_phone', true)); ?>
                                </p>
                            </div>
                        </div>
                        <p><strong>Otras referencias:</strong> <?php echo esc_html(get_user_meta($user_id, 'hv_references', true)); ?></p>
                    </div>
                </div>

                <!-- Documentos -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Documentos</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Documento de Identidad</h4>
                                <?php
                                $document_id = get_user_meta($user_id, 'hv_identity_document', true);
                                $this->render_document($document_id);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Autorización y Compromiso -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Autorización y Compromiso</h3>
                    </div>
                    <div class="card-body">
                        <p>Declaro que la información proporcionada es verídica y me comprometo a cumplir con las normas y valores de la institución. Entiendo que mi participación como voluntario es de carácter altruista y sin remuneración económica.</p>
                        <p class="mt-3"><strong>Fecha de registro:</strong> <?php echo date('d/m/Y', strtotime($user->user_registered)); ?></p>
                    </div>
                </div>

                <!-- Sección de Verificación (solo para employers) -->
                <?php if ($is_employer): ?>
                    <div class="card mt-4">
                        <div class="card-header bg-primary text-white">
                            <h2 class="h4 mb-0">Verificación de Voluntario</h2>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Verificar Documentos</h4>
                                    <p>Verifique que los documentos del voluntario estén completos y sean válidos antes de proceder con la verificación.</p>

                                    <div class="mb-3">
                                        <label for="received_observations" class="form-label">Observaciones:</label>
                                        <textarea id="received_observations" class="form-control" rows="3" placeholder="Ingrese observaciones si es necesario" <?php echo $is_verified ? 'readonly disabled' : ''; ?>><?php echo esc_textarea($received_observations); ?></textarea>
                                    </div>

                                    <?php if ($date_received): ?>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Fecha de recepción:</strong></label>
                                            <div>
                                                <?php echo esc_html(date('d/m/Y H:i', strtotime($date_received))); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!$is_verified): ?>
                                        <button id="verify-volunteer-btn" class="btn btn-success" data-user-id="<?php echo $user_id; ?>">
                                            <i class="fas fa-check-circle me-2"></i> Verificar Voluntario
                                        </button>
                                    <?php else: ?>
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Voluntario verificado. No es posible editar las observaciones.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>


        </div>
        <div class="mt-4">
            <a href="<?php echo admin_url('admin.php?page=volunteer_profile_editor&user_id=' . $user_id); ?>" class="btn btn-primary">
                Editar Perfil Completo
            </a>
            <a href="<?php echo admin_url('admin.php?page=volunteers'); ?>" class="btn btn-secondary">
                Volver a la lista
            </a>
        </div>

        <script>
            jQuery(document).ready(function($) {
                $('#verify-volunteer-btn').on('click', function() {
                    var button = $(this);
                    var user_id = button.data('user-id');
                    var observations = $('#received_observations').val();

                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Procesando...');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'verify_volunteer',
                            user_id: user_id,
                            observations: observations,
                            security: '<?php echo wp_create_nonce("verify_volunteer_nonce"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Voluntario verificado exitosamente. Código: ' + response.data.code);
                                location.reload();
                            } else {
                                alert('Error: ' + response.data);
                                button.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Verificar Voluntario');
                            }
                        },
                        error: function(xhr, status, error) {
                            // Mostrar detalles del error
                            let errorMsg = 'Error en la comunicación con el servidor';

                            if (xhr.responseJSON && xhr.responseJSON.data) {
                                errorMsg += ': ' + xhr.responseJSON.data;
                            } else if (xhr.responseText) {
                                try {
                                    const jsonResponse = JSON.parse(xhr.responseText);
                                    errorMsg = jsonResponse.data || errorMsg;
                                } catch (e) {
                                    errorMsg += '. Detalles: ' + xhr.responseText;
                                }
                            }

                            alert(errorMsg);
                            button.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Verificar Voluntario');
                        }
                    });
                });

                $('#resend-credentials-btn').on('click', function() {
                    var button = $(this);
                    var user_id = button.data('user-id');
                    var status_div = $('#resend-credentials-status');

                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Enviando...');
                    status_div.removeClass('text-success text-danger').html('');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'resend_credentials',
                            user_id: user_id,
                            security: '<?php echo wp_create_nonce("resend_credentials_nonce"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                status_div.addClass('text-success').html('✔️ Credenciales reenviadas exitosamente');
                            } else {
                                status_div.addClass('text-danger').html('❌ Error: ' + response.data);
                            }
                        },
                        error: function(xhr, status, error) {
                            // Mostrar detalles del error
                            let errorMsg = 'Error en la comunicación con el servidor';

                            if (xhr.responseJSON && xhr.responseJSON.data) {
                                errorMsg += ': ' + xhr.responseJSON.data;
                            } else if (xhr.responseText) {
                                try {
                                    const jsonResponse = JSON.parse(xhr.responseText);
                                    errorMsg = jsonResponse.data || errorMsg;
                                } catch (e) {
                                    errorMsg += '. Detalles: ' + xhr.responseText;
                                }
                            }

                            status_div.addClass('text-danger').html('❌ ' + errorMsg);
                        },
                        complete: function() {
                            button.prop('disabled', false).html('Reenviar credenciales');
                        }
                    });
                });
            });
        </script>

        <?php
    }



    /**
     * Verifica al voluntario y genera código único
     */
    public function verify_volunteer()
    {
        check_ajax_referer('verify_volunteer_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para realizar esta acción');
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $observations = isset($_POST['observations']) ? sanitize_textarea_field($_POST['observations']) : '';

        if (!$user_id) {
            wp_send_json_error('ID de usuario no válido');
        }

        // Verificar que el usuario es un employer
        if (get_user_meta($user_id, '_user_type', true) !== 'employers') {
            wp_send_json_error('Este usuario no es un voluntario (employer)');
        }

        // Verificar que no esté ya verificado
        if (get_user_meta($user_id, '_is_verified', true) === 'yes') {
            wp_send_json_error('Este usuario ya está verificado');
        }

        // Generar código único
        $code = $this->generate_unique_code($user_id);

        if (!$code) {
            wp_send_json_error('Error al generar el código único');
        }

        // Actualizar metadatos
        update_user_meta($user_id, '_is_verified', 'yes');
        //update_user_meta($user_id, 'identity_verified', '1');
        update_user_meta($user_id, 'hv_unique_code', $code);
        update_user_meta($user_id, 'hv_date_received', current_time('mysql'));
        //update_user_meta($user_id, 'hv_received_observations', $observations);

        try {
            // Obtener datos de usuario
            $user_data = [
                'first_name' => get_user_meta($user_id, 'first_name', true),
                'last_name' => get_user_meta($user_id, 'last_name', true),
                'fecha_recepcion' => get_user_meta($user_id, 'hv_date_received', true),
                'code' => $code
            ];

            // Generar PDFs
            $pdf_generator = new PDF_Generator();

            // Generar certificado
            $certificate_path = $pdf_generator->generate_certificate($user_id, $user_data);

            // Generar planilla con QR
            $qr_url = $pdf_generator->generate_qr_code($code);
            $planilla_path = $pdf_generator->generate_planilla($user_id, $user_data, $qr_url);

            // Enviar email con adjuntos
            $email_manager = new Email_Manager();
            $email_sent = $email_manager->send_verification_email($user_id, [
                'certificate_path' => $certificate_path,
                'planilla_path' => $planilla_path
            ]);

            if (!$email_sent) {
                error_log("Falló envío de email para usuario: $user_id");
                // No enviar error aquí para no frustrar el proceso completo
            }

            wp_send_json_success([
                'code' => $code,
                'message' => 'Voluntario verificado exitosamente'
            ]);
        } catch (\Exception $e) {
            error_log("Error en verify_volunteer: " . $e->getMessage());
            wp_send_json_error('Error durante la verificación: ' . $e->getMessage());
        }
    }

    /**
     * Genera un código único para el voluntario
     */
    private function generate_unique_code($user_id): string
    {
        // Formato: VOL-0000ID (ej. VOL-00001 para ID 1)
        $code = 'VOL-' . str_pad($user_id, 5, '0', STR_PAD_LEFT);

        // Verificar que el código no exista (aunque es poco probable con este formato)
        $existing_user = get_users([
            'meta_key' => 'hv_unique_code',
            'meta_value' => $code,
            'exclude' => [$user_id],
            'number' => 1
        ]);

        if (!empty($existing_user)) {
            // Si por alguna razón existe, añadir sufijo
            $code = $code . '-' . uniqid();
        }

        return $code;
    }

    /**
     * Método auxiliar para mostrar documentos
     */
    private function render_document($document_id, $empty_message = 'No se ha subido documento')
    {
        if ($document_id && is_numeric($document_id)) {
            $document_url = wp_get_attachment_url($document_id);
            $file_type = get_post_mime_type($document_id);
            $is_image = strpos($file_type, 'image') !== false;
        ?>

            <?php if ($is_image): ?>
                <img src="<?php echo esc_url($document_url); ?>"
                    class="img-fluid rounded border"
                    style="max-height: 200px;"
                    alt="Documento">
            <?php else: ?>
                <div class="d-flex align-items-center">
                    <i class="fas fa-file-pdf fa-3x text-danger me-3"></i>
                    <div>
                        <a href="<?php echo esc_url($document_url); ?>"
                            target="_blank"
                            class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download me-2"></i>
                            Descargar
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-2">
                <a href="<?php echo esc_url($document_url); ?>"
                    target="_blank"
                    class="btn btn-sm btn-outline-secondary">
                    Ver completo
                </a>
            </div>
        <?php } else { ?>
            <div class="alert alert-warning py-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo esc_html($empty_message); ?>
            </div>
<?php }
    }

    public function resend_credentials()
    {
        // Verificación básica
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'hv_admin_nonce')) {
            wp_send_json_error('Acceso no autorizado (nonce inválido)', 403);
        }

        // Luego verificar capacidades
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos para esta acción', 403);
        }

        // Resto del código...
        $user_id = intval($_POST['user_id'] ?? 0);
        if (!$user_id) {
            wp_send_json_error('ID de usuario inválido', 400);
        }

        $user_id = intval($_POST['user_id'] ?? 0);
        if (!$user_id) {
            wp_send_json_error('ID de usuario inválido', 400);
        }

        // Obtener usuario y rutas de archivos
        $user = get_userdata($user_id);
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/humanitarios-pdfs/';

        $attachments = [];
        $files = [
            "certificado-{$user->first_name}-{$user_id}.pdf",
            "planilla-{$user->first_name}-{$user_id}.pdf"
        ];

        // Buscar archivos existentes
        foreach ($files as $file) {
            $path = $pdf_dir . $file;
            if (file_exists($path)) {
                $attachments[] = $path;
            }
        }

        // Configurar y enviar correo
        $to = $user->user_email;
        $subject = 'Tus credenciales como voluntario';
        $message = 'Hola ' . $user->first_name . ",\n\nAdjuntamos tus credenciales.\n\nGracias por participar.";

        // Intento de envío simple
        $sent = wp_mail($to, $subject, $message, '', $attachments);

        if ($sent) {
            wp_send_json_success('Credenciales enviadas correctamente');
        } else {
            // Verificar si el problema es de configuración SMTP
            if (!function_exists('mail')) {
                wp_send_json_error('El servidor no tiene configurado el envío de correos. Contacta al administrador.');
            } else {
                wp_send_json_error('Error al enviar el correo. Verifica la configuración de correo de WordPress.');
            }
        }
    }
}

if (!class_exists('Volunteer_Profile')) {
    new Volunteer_Profile();
}
