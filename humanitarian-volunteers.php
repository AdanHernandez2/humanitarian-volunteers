<?php
/*
Plugin Name: Humanitarian Volunteers
Description: Sistema de gestión de voluntarios humanitarios
Version: 2.2
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
    'includes/class-pdf-generator.php',       // Generación de PDFs
    'includes/class-verification-page.php',   // Pública verificación
    'includes/class-volunteers-admin-page.php', // Página de administración
    'includes/class-volunteer-profile.php',   // Nuevo: Perfil de voluntario
    'includes/class-volunteer-profile-editor.php' // Editor de perfil de voluntario
];

if (is_admin()) {
    $includes[] = 'includes/class-admin-interface.php';
}

foreach ($includes as $file) {
    if (file_exists(HV_PLUGIN_PATH . $file)) {
        require_once HV_PLUGIN_PATH . $file;
    }
}

// Inicializar clases
add_action('init', function () {
    new Volunteer_Form_Handler();  // Manejo de formularios
    new Verification_Page();       // Página de verificación
    new Volunteer_Profile();       // Nuevo: Perfil de voluntario
    new Volunteer_Profile_Editor(); // Editor de perfil de voluntario

    if (is_admin()) {
        new Volunteers_Admin_Page(); // Página de administración
        new Admin_Interface();       // Acciones rápidas en usuarios
    }
});

// Cargar estilos y scripts de administración (VERSIÓN CORREGIDA)
add_action('admin_enqueue_scripts', function ($hook) {
    // Verificar páginas de voluntarios
    $is_volunteers_page = ('toplevel_page_volunteers' === $hook);
    $is_profile_page = (isset($_GET['page']) && 'volunteer_profile' === $_GET['page']);
    $is_profile_page_edit = (isset($_GET['page']) && 'volunteer_profile_editor' === $_GET['page']);

    if ($is_volunteers_page || $is_profile_page || $is_profile_page_edit) {
        // Bootstrap CSS (solo si no está cargado)
        if (!wp_style_is('bootstrap')) {
            wp_enqueue_style(
                'hv-bootstrap',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                [],
                '5.3.0'
            );
        }

        // Cargar Bootstrap JS si no está cargado
        if (!wp_script_is('bootstrap-js')) {
            wp_enqueue_script(
                'hv-bootstrap-js',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
                ['jquery'],
                '5.3.0',
                true
            );
        }


        // ESTILOS: Usar plugin_dir_path() para obtener ruta física
        $admin_css = plugin_dir_path(__FILE__) . 'assets/css/admin-volunteers.css';
        if (file_exists(filename: $admin_css)) {
            wp_enqueue_style(
                'hv-admin-styles',
                plugin_dir_url(__FILE__) . 'assets/css/admin-volunteers.css',
                [],
                filemtime($admin_css) // Versionado automático
            );
        }

        // Scripts solo para página principal
        if ($is_volunteers_page) {
            // SCRIPTS: Usar plugin_dir_path() para obtener ruta física
            $admin_js = plugin_dir_path(__FILE__) . 'assets/js/admin-volunteers.js';
            if (file_exists($admin_js)) {
                wp_enqueue_script(
                    'hv-admin-scripts',
                    plugin_dir_url(__FILE__) . 'assets/js/admin-volunteers.js',
                    ['jquery'],
                    filemtime($admin_js),
                    true
                );

                // Datos para AJAX
                wp_localize_script('hv-admin-scripts', 'hv_admin', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hv_admin_nonce')
                ]);
            }
        }

        // Scripts para perfil y edición de voluntario
        if ($is_volunteers_page || $is_profile_page || $is_profile_page_edit) {
            $admin_js = plugin_dir_path(__FILE__) . 'assets/js/admin-volunteers.js';
            if (file_exists($admin_js)) {
                wp_enqueue_script(
                    'hv-admin-scripts',
                    plugin_dir_url(__FILE__) . 'assets/js/admin-volunteers.js',
                    ['jquery'],
                    filemtime($admin_js),
                    true
                );
                wp_localize_script('hv-admin-scripts', 'hv_admin', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hv_admin_nonce')
                ]);
            }
        }
    }
});

// Cargar estilos y scripts en el front-end
add_action('wp_enqueue_scripts', function () {
    // Registrar Bootstrap (pero no cargarlo aún)
    wp_register_style(
        'hv-bootstrap-front',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        [],
        '5.3.0'
    );

    // Registrar estilos personalizados
    $front_css = plugin_dir_path(__FILE__) . 'assets/css/volunteer-styles.css';
    if (file_exists($front_css)) {
        wp_register_style(
            'hv-frontend-styles',
            plugin_dir_url(__FILE__) . 'assets/css/volunteer-styles.css',
            [],
            filemtime($front_css)
        );
    }

    // Registrar scripts personalizados
    $front_js = plugin_dir_path(__FILE__) . 'assets/js/volunteer-scripts.js';
    if (file_exists($front_js)) {
        wp_register_script(
            'hv-frontend-scripts',
            plugin_dir_url(__FILE__) . 'assets/js/volunteer-scripts.js',
            ['jquery'],
            filemtime($front_js),
            true
        );
    }
});

// shortcode para activar los recursos
add_shortcode('volunteer_registration_form', function () {
    // Indicar que se deben cargar los recursos
    global $hv_load_resources;
    $hv_load_resources = true;

    ob_start();
    wp_nonce_field('volunteer_form_action', 'volunteer_nonce');
    include HV_PLUGIN_PATH  . 'templates/forms/form-register.php';
    return ob_get_clean();
});

// Cargar los recursos solo cuando el shortcode esté presente
add_action('wp_footer', function () {
    global $hv_load_resources;

    if (!empty($hv_load_resources)) {
        // Cargar Bootstrap si no está cargado
        if (!wp_style_is('bootstrap')) {
            wp_enqueue_style('hv-bootstrap-front');
        }

        // Cargar estilos personalizados
        if (wp_style_is('hv-frontend-styles', 'registered')) {
            wp_enqueue_style('hv-frontend-styles');
        }

        // Cargar scripts personalizados
        if (wp_script_is('hv-frontend-scripts', 'registered')) {
            wp_enqueue_script('hv-frontend-scripts');

            // Localizar script solo si se carga
            wp_localize_script('hv-frontend-scripts', 'hv_frontend', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('hv_frontend_nonce')
            ]);
        }
    }
});
