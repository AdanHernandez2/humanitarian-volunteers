<?php
class Volunteers_Admin_Page
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_verify_volunteer', [$this, 'ajax_verify_volunteer']);
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Voluntarios',
            'Voluntarios',
            'manage_options',
            'volunteers',
            [$this, 'render_admin_page'],
            'dashicons-groups',
            25
        );
    }

    public function render_admin_page()
    {
        // Construir argumentos de consulta
        $args = [
            'meta_key' => '_user_type',
            'meta_value' => 'employers',
            'number' => -1
        ];

        // Aplicar filtro de búsqueda
        if (!empty($_GET['s'])) {
            $args['search'] = '*' . sanitize_text_field($_GET['s']) . '*';
        }

        // Aplicar filtro de verificación
        if (!empty($_GET['verification_status'])) {
            $status = $_GET['verification_status'];

            if ($status === 'verified') {
                $args['meta_query'] = [
                    'relation' => 'OR',
                    [
                        'key' => '_is_verified',
                        'value' => 'yes'
                    ],
                    [
                        'key' => 'identity_verified',
                        'value' => '1'
                    ]
                ];
            } else {
                $args['meta_query'] = [
                    'relation' => 'AND',
                    [
                        'relation' => 'OR',
                        ['key' => '_is_verified', 'value' => 'yes', 'compare' => '!='],
                        ['key' => '_is_verified', 'compare' => 'NOT EXISTS']
                    ],
                    [
                        'relation' => 'OR',
                        ['key' => 'identity_verified', 'value' => '1', 'compare' => '!='],
                        ['key' => 'identity_verified', 'compare' => 'NOT EXISTS']
                    ]
                ];
            }
        }

        // Obtener usuarios filtrados
        $users = get_users($args);

?>
        <div class="wrap hv-admin-volunteers">
            <h1 class="wp-heading-inline">Voluntarios</h1>

            <div class="filters mb-4">
                <form method="get">
                    <input type="hidden" name="page" value="volunteers">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" name="s" class="form-control" placeholder="Buscar..."
                                    value="<?php echo esc_attr($_GET['s'] ?? ''); ?>">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="verification_status" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="verified" <?php selected($_GET['verification_status'] ?? '', 'verified'); ?>>
                                    Verificados
                                </option>
                                <option value="unverified" <?php selected($_GET['verification_status'] ?? '', 'unverified'); ?>>
                                    No verificados
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-secondary" type="submit">Filtrar</button>
                            <a href="?page=volunteers" class="btn btn-link">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Área de Interés</th>
                            <th>Verificación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) :
                            $is_verified = get_user_meta($user->ID, '_is_verified', true) === 'yes' ||
                                get_user_meta($user->ID, 'identity_verified', true) === '1';
                        ?>
                            <tr data-user-id="<?php echo $user->ID; ?>">
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=volunteer_profile&user_id=' . $user->ID); ?>">
                                        <?php echo $user->display_name; ?>
                                    </a>
                                </td>
                                <td><?php echo $user->user_email; ?></td>
                                <td><?php echo get_user_meta($user->ID, 'hv_phone', true); ?></td>
                                <td><?php echo get_user_meta($user->ID, 'hv_skills', true); ?></td>
                                <td class="verification-status">
                                    <?php if ($is_verified) : ?>
                                        <span class="badge bg-success">✅ Verificado</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">❌ No verificado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=volunteer_profile&user_id=' . $user->ID); ?>"
                                        class="btn btn-sm btn-primary">
                                        Ver Perfil
                                    </a>

                                    <?php if (!$is_verified) : ?>
                                        <button class="btn btn-sm btn-success verify-btn"
                                            data-user-id="<?php echo $user->ID; ?>">
                                            Verificar
                                        </button>
                                    <?php else : ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            Verificado
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (empty($users)) : ?>
                <div class="alert alert-info">
                    No se encontraron voluntarios registrados.
                </div>
            <?php endif; ?>
        </div>
<?php
    }
    public function ajax_verify_volunteer()
    {
        check_ajax_referer('hv_admin_nonce', 'nonce');

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

        if (!$user_id) {
            wp_send_json_error(['message' => 'ID de usuario inválido']);
        }

        // Actualizar metadatos
        update_user_meta($user_id, '_is_verified', 'yes');
        update_user_meta($user_id, 'identity_verified', '1');

        // Actualizar campos con Carbon Fields
        if (function_exists('carbon_set_user_meta')) {
            carbon_set_user_meta($user_id, 'hv_status', 'verified');

            // Generar código único si no existe
            if (!carbon_get_user_meta($user_id, 'hv_unique_code')) {
                $code = 'VOL-' . str_pad($user_id, 8, '0', STR_PAD_LEFT) . '-' . bin2hex(random_bytes(2));
                carbon_set_user_meta($user_id, 'hv_unique_code', $code);
            }
        }

        // Disparar email de confirmación
        do_action('volunteer_verified', $user_id);

        wp_send_json_success([
            'message' => 'Usuario verificado con éxito',
            'new_status' => '<span class="badge bg-success">✅ Verificado</span>',
            'new_button' => '<button class="btn btn-sm btn-secondary" disabled>Verificado</button>'
        ]);
    }
}
