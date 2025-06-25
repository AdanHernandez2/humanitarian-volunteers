<?php
/*
Plugin Name: Humanitarian Volunteers
Description: Sistema de gestión de voluntarios humanitarios
Version: 1.0
Author: Tu Nombre
*/

defined('ABSPATH') || exit;

// Definir constantes
define('HV_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HV_PLUGIN_URL', plugin_dir_url(__FILE__));

// Cargar Composer
require_once HV_PLUGIN_PATH . 'vendor/autoload.php';

// Cargar módulos
add_action('init', function() {
    require_once HV_PLUGIN_PATH . 'includes/class-cpt-volunteers.php';
    require_once HV_PLUGIN_PATH . 'includes/class-form-handler.php';
    require_once HV_PLUGIN_PATH . 'includes/class-email-manager.php';
    require_once HV_PLUGIN_PATH . 'includes/class-pdf-generator.php';
    require_once HV_PLUGIN_PATH . 'includes/class-verification-page.php';
    
    if(is_admin()) {
        require_once HV_PLUGIN_PATH . 'includes/class-admin-interface.php';
    }
});

// Registrar shortcode formulario
add_shortcode('volunteer_registration_form', 'render_volunteer_form');
function render_volunteer_form() {
    ob_start();
    include HV_PLUGIN_PATH . 'templates/form-register.php';
    return ob_get_clean();
}