<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class CPT_Volunteers {
    public function __construct() {
        add_action('init', [$this, 'register_cpt']);
        add_action('carbon_fields_register_fields', [$this, 'register_fields']);
        add_filter('manage_volunteer_posts_columns', [$this, 'custom_columns']);
        add_action('manage_volunteer_posts_custom_column', [$this, 'fill_custom_columns'], 10, 2);
        add_action('restrict_manage_posts', [$this, 'add_status_filter']);
        add_filter('parse_query', [$this, 'apply_status_filter']);
    }

    public function register_cpt() {
        register_post_type('volunteer', [
            'labels' => [
                'name' => 'Voluntarios',
                'singular_name' => 'Voluntario',
                'menu_icon' => 'dashicons-groups', // Icono personalizado
            ],
            'public' => true, // Cambiado a true para mostrar en admin
            'show_ui' => true,
            'show_in_menu' => true, // Asegura que aparezca en el menú
            'capability_type' => 'post',
            'supports' => ['title'],
            'rewrite' => false, // Desactiva las URLs públicas
            'show_in_rest' => false, // Desactiva Gutenberg
        ]);
    }

    // ... [resto de los campos] ...

    // Columnas personalizadas en la lista
    public function custom_columns($columns) {
        unset($columns['date']);
        $columns['email'] = 'Email';
        $columns['phone'] = 'Teléfono';
        $columns['status'] = 'Estado';
        $columns['verification'] = 'Verificación';
        return $columns;
    }

    public function fill_custom_columns($column, $post_id) {
        switch ($column) {
            case 'email':
                echo carbon_get_post_meta($post_id, 'hv_email');
                break;
            case 'phone':
                echo carbon_get_post_meta($post_id, 'hv_phone');
                break;
            case 'status':
                $status = carbon_get_post_meta($post_id, 'hv_status');
                echo ($status === 'verified') ? '✅ Verificado' : '⏳ Pendiente';
                break;
            case 'verification':
                $is_verified = get_post_meta($post_id, '_is_verified', true);
                $identity_verified = get_post_meta($post_id, 'identity_verified', true);
                echo ($is_verified === 'yes' || $identity_verified === '1') 
                    ? '✅ Verificado' 
                    : '❌ No verificado';
                break;
        }
    }

    // Filtro por estado de verificación
    public function add_status_filter() {
        global $typenow;
        if ('volunteer' === $typenow) {
            $current = isset($_GET['verification_status']) ? $_GET['verification_status'] : '';
            ?>
            <select name="verification_status">
                <option value="">Todos los estados</option>
                <option value="verified" <?php selected($current, 'verified'); ?>>Verificados</option>
                <option value="unverified" <?php selected($current, 'unverified'); ?>>No verificados</option>
            </select>
            <?php
        }
    }

    public function apply_status_filter($query) {
        global $pagenow;
        if (
            is_admin() && 
            $pagenow === 'edit.php' && 
            isset($_GET['post_type']) && 
            $_GET['post_type'] === 'volunteer' &&
            !empty($_GET['verification_status'])
        ) {
            $meta_query = ['relation' => 'OR'];
            
            if ($_GET['verification_status'] === 'verified') {
                $meta_query[] = ['key' => '_is_verified', 'value' => 'yes'];
                $meta_query[] = ['key' => 'identity_verified', 'value' => '1'];
            } else {
                $meta_query[] = ['key' => '_is_verified', 'value' => 'yes', 'compare' => '!='];
                $meta_query[] = ['key' => 'identity_verified', 'value' => '1', 'compare' => '!='];
            }

            $query->set('meta_query', $meta_query);
        }
    }
}
new CPT_Volunteers();