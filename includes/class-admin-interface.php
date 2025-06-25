<?php
class Admin_Interface {
    public function __construct() {
        add_action('admin_init', [$this, 'handle_quick_verification']);
        add_filter('post_row_actions', [$this, 'add_quick_verify_action'], 10, 2);
    }

    // Añadir acción rápida en la lista
    public function add_quick_verify_action($actions, $post) {
        if ($post->post_type === 'volunteer') {
            $is_verified = get_post_meta($post->ID, '_is_verified', true);
            if ($is_verified !== 'yes') {
                $actions['quick_verify'] = sprintf(
                    '<a href="?post_type=volunteer&quick_verify=%d">Verificar</a>',
                    $post->ID
                );
            }
        }
        return $actions;
    }

    // Manejar la verificación
    public function handle_quick_verification() {
        if (isset($_GET['quick_verify'])) {
            $post_id = intval($_GET['quick_verify']);
            
            // Actualizar metadatos
            update_post_meta($post_id, '_is_verified', 'yes');
            update_post_meta($post_id, 'identity_verified', 1);
            carbon_set_post_meta($post_id, 'hv_status', 'verified');
            
            // Generar código si no existe
            if (!carbon_get_post_meta($post_id, 'hv_unique_code')) {
                $code = 'VOL-' . str_pad($post_id, 8, '0', STR_PAD_LEFT) . '-' . bin2hex(random_bytes(2));
                carbon_set_post_meta($post_id, 'hv_unique_code', $code);
            }
            
            // Disparar email de confirmación
            do_action('volunteer_verified', $post_id);
            
            wp_redirect(admin_url('edit.php?post_type=volunteer'));
            exit;
        }
    }
}
new Admin_Interface();