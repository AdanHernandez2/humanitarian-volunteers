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

?>
        <div class="wrap volunteer-profile">
            <h1 class="wp-heading-inline">Perfil de Voluntario</h1>
            <a href="<?php echo admin_url('admin.php?page=volunteers'); ?>" class="page-title-action">
                ← Volver a la lista
            </a>
            <div class="container-cars-volunteer_profiler-grid">
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Información Personal</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre completo:</strong> <?php echo $user->display_name; ?></p>
                                <p><strong>Cédula:</strong> <?php echo get_user_meta($user_id, 'hv_id_number', true); ?></p>
                                <p><strong>Fecha de nacimiento:</strong> <?php echo get_user_meta($user_id, 'hv_birth_date', true); ?></p>
                                <p><strong>Provincia de residencia:</strong> <?php echo get_user_meta($user_id, 'hv_province', true); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Teléfono/WhatsApp:</strong> <?php echo get_user_meta($user_id, 'hv_phone', true); ?></p>
                                <p><strong>Correo electrónico:</strong> <?php echo $user->user_email; ?></p>
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

                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Área de Habilidades/Interés</h2>
                    </div>
                    <div class="card-body">
                        <p><?php echo get_user_meta($user_id, 'hv_skills', true); ?></p>
                        <p><strong>Otro:</strong> <?php echo get_user_meta($user_id, 'hv_skills_other', true); ?></p>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Disponibilidad</h2>
                    </div>
                    <div class="card-body">
                        <p><strong>¿Disponible fines de semana?:</strong> <?php echo get_user_meta($user_id, 'hv_weekend_availability', true); ?></p>
                        <p><strong>¿Puede viajar al interior?:</strong> <?php echo get_user_meta($user_id, 'hv_travel_availability', true); ?></p>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Experiencia en Voluntariado</h2>
                    </div>
                    <div class="card-body">
                        <p><strong>¿Tiene experiencia?:</strong> <?php echo get_user_meta($user_id, 'hv_has_experience', true); ?></p>
                        <p><strong>Descripción breve:</strong> <?php echo get_user_meta($user_id, 'hv_experience_desc', true); ?></p>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Información Adicional</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nacionalidad:</strong> <?php echo get_user_meta($user_id, 'hv_nationality', true); ?></p>
                                <p><strong>Género:</strong> <?php echo get_user_meta($user_id, 'hv_gender', true); ?></p>
                                <p><strong>Tipo de sangre:</strong> <?php echo get_user_meta($user_id, 'hv_blood_type', true); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Talla de camiseta:</strong> <?php echo get_user_meta($user_id, 'hv_shirt_size', true); ?></p>
                                <p><strong>Profesión:</strong> <?php echo get_user_meta($user_id, 'hv_profession', true); ?></p>
                                <p><strong>Condición médica:</strong> <?php echo get_user_meta($user_id, 'hv_medical_condition', true); ?></p>
                            </div>
                        </div>
                        <p><strong>Referencias:</strong> <?php echo get_user_meta($user_id, 'hv_references', true); ?></p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Documento de Identidad</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $user_id = $_GET['user_id'] ?? 0;
                        $document_id = get_user_meta($user_id, 'hv_identity_document', true);

                        if ($document_id && is_numeric($document_id)) {
                            $document_url = wp_get_attachment_url($document_id);
                            $file_type = get_post_mime_type($document_id);
                            $is_image = strpos($file_type, 'image') !== false;
                        ?>

                            <?php if ($is_image): ?>
                                <img src="<?php echo esc_url($document_url); ?>"
                                    class="img-fluid rounded border"
                                    style="max-height: 300px;"
                                    alt="Documento de identidad">
                            <?php else: ?>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf fa-3x text-danger me-3"></i>
                                    <div>
                                        <a href="<?php echo esc_url($document_url); ?>"
                                            target="_blank"
                                            class="btn btn-outline-primary">
                                            <i class="fas fa-download me-2"></i>
                                            Descargar documento (PDF)
                                        </a>
                                        <p class="mt-2 small text-muted">
                                            <?php echo basename(get_attached_file($document_id)); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="mt-3">
                                <a href="<?php echo esc_url($document_url); ?>"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-secondary">
                                    Ver documento completo
                                </a>

                                <?php if (current_user_can('manage_options')): ?>
                                    <a href="<?php echo admin_url("post.php?post=$document_id&action=edit"); ?>"
                                        class="btn btn-sm btn-outline-secondary ms-2">
                                        Administrar documento
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Este usuario no ha subido documento de identidad
                            </div>
                        <?php } ?>
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
}
