<?php
class Volunteer_Profile
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        // Acción para verificar documentos
        add_action('wp_ajax_check_user_document', [$this, 'check_user_document']);
        add_action('wp_ajax_nopriv_check_user_document', [$this, 'check_user_document']);
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

        // Obtener campos de selección múltiple
        $interest_areas = maybe_unserialize(get_user_meta($user_id, 'hv_interest_areas', true));
        $availability_days = maybe_unserialize(get_user_meta($user_id, 'hv_availability_days', true));
?>
        <div class="wrap volunteer-profile">
            <h1 class="wp-heading-inline">Perfil de Voluntario</h1>
            <a href="<?php echo admin_url('admin.php?page=volunteers'); ?>" class="page-title-action">
                ← Volver a la lista
            </a>
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
                                    <?php else : ?>
                                        <span class="badge bg-danger">❌ No verificado</span>
                                    <?php endif; ?>
                                </p>
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
                            <div class="col-md-6">
                                <h4>Firma del Voluntario</h4>
                                <?php
                                $signature_id = get_user_meta($user_id, 'hv_signature', true);
                                $this->render_document($signature_id, 'No se ha subido firma');
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

            </div>
            <div class="mt-4">
                <a href="<?php echo admin_url('user-edit.php?user_id=' . $user_id); ?>" class="btn btn-primary">
                    Editar Perfil Completo
                </a>
                <a href="<?php echo admin_url('admin.php?page=volunteers'); ?>" class="btn btn-secondary">
                    Volver a la lista
                </a>
            </div>
        </div>
        <?php
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
}

if (!class_exists('Volunteer_Profile')) {
    new Volunteer_Profile();
}
