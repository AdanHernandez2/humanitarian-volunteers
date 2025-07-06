<?php
/*
Plugin Name: Humanitarian Volunteers
Description: Sistema de gestión de voluntarios humanitarios
Version: 2.0
Author: Humanitarios
*/

defined('ABSPATH') || exit;

// Definir constantes
define('HV_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HV_PLUGIN_URL', plugin_dir_url(__FILE__));

// Cargar Composer
if (file_exists(HV_PLUGIN_PATH . 'vendor/autoload.php')) {
    require_once HV_PLUGIN_PATH . 'vendor/autoload.php';
}

// Cargar dependencias
$includes = [
    'includes/class-form-handler.php',        // Procesamiento de formularios
    'includes/class-email-manager.php',       // Gestión de emails
    // 'includes/class-pdf-generator.php',       // Generación de PDFs
    'includes/class-verification-page.php',   // Pública verificación
    'includes/class-volunteers-admin-page.php', // Página de administración
    'includes/class-volunteer-profile.php',   // Nuevo: Perfil de voluntario
];

if (is_admin()) {
    $includes[] = 'includes/class-admin-interface.php';
}

foreach ($includes as $file) {
    if (file_exists(HV_PLUGIN_PATH . $file)) {
        require_once HV_PLUGIN_PATH . $file;
    }
}

// Registrar shortcode
add_shortcode('volunteer_registration_form', 'render_volunteer_form');
function render_volunteer_form()
{
    ob_start();
    wp_nonce_field('volunteer_form_action', 'volunteer_nonce');
    include HV_PLUGIN_PATH . 'templates/form-register.php';
    return ob_get_clean();
}

// Inicializar clases
add_action('init', function () {
    new Volunteer_Form_Handler();  // Manejo de formularios
    new Verification_Page();       // Página de verificación
    new Volunteer_Profile();       // Nuevo: Perfil de voluntario

    if (is_admin()) {
        new Volunteers_Admin_Page(); // Página de administración
        new Admin_Interface();       // Acciones rápidas en usuarios
    }
});

// Cargar estilos y scripts de administración
add_action('admin_enqueue_scripts', function ($hook) {
    // Estilos para la página de voluntarios
    if (
        'toplevel_page_volunteers' === $hook ||
        (isset($_GET['page']) && 'volunteer_profile' === $_GET['page'])
    ) {

        // Bootstrap CSS
        wp_enqueue_style('hv-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');

        // Estilos personalizados
        wp_enqueue_style('hv-admin-styles', HV_PLUGIN_URL . 'assets/css/admin-volunteers.css');

        // Scripts solo para la página principal
        if ('toplevel_page_volunteers' === $hook) {
            wp_enqueue_script('hv-admin-scripts', HV_PLUGIN_URL . 'assets/js/admin-volunteers.js', ['jquery'], null, true);

            // Datos para AJAX
            wp_localize_script('hv-admin-scripts', 'hv_admin', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hv_admin_nonce')
            ]);
        }
    }
});
