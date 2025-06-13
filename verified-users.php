<?php

/**
 * Plugin Name: Verified Users CPT
 * Description: Plugin para gestionar usuarios verificados
 * Version: 1.0.0
 * Author: Humanitarios
 * License: GPL-2.0+
 */

namespace VerifiedUsers;

// Evitar acceso directo
defined('ABSPATH') || exit;

// Cargar Composer autoloader si Carbon Fields se instaló via Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class VerifiedUsersPlugin
{
    const TEXT_DOMAIN = 'verified-users';
    const CPT_SLUG = 'verified_user';

    public function __construct()
    {
        // Registrar hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // Inicializar el plugin
        add_action('init', [$this, 'init']);
        add_action('carbon_fields_register_fields', [$this, 'register_metaboxes']);
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Cargar Carbon Fields
        add_action('after_setup_theme', [$this, 'load_carbon_fields']);
    }

    public function activate()
    {
        // Lógica de activación si es necesaria
        $this->register_cpt();
        flush_rewrite_rules();
    }

    public function deactivate()
    {
        // Lógica de desactivación si es necesaria
        flush_rewrite_rules();
    }

    public function load_carbon_fields()
    {
        \Carbon_Fields\Carbon_Fields::boot();
    }

    public function init()
    {
        $this->register_cpt();
        $this->register_taxonomies();
        load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function add_admin_menu()
    {
        add_menu_page(
            __('Usuarios Verificados', self::TEXT_DOMAIN),
            __('Usuarios Verificados', self::TEXT_DOMAIN),
            'manage_options',
            'edit.php?post_type=' . self::CPT_SLUG,
            '',
            'dashicons-admin-users',
            30
        );
    }

    public function register_cpt()
    {
        $labels = [
            'name' => __('Usuarios Verificados', self::TEXT_DOMAIN),
            'singular_name' => __('Usuario Verificado', self::TEXT_DOMAIN),
            'menu_name' => __('Usuarios Verificados', self::TEXT_DOMAIN),
            'name_admin_bar' => __('Usuario Verificado', self::TEXT_DOMAIN),
            'add_new' => __('Añadir Nuevo', self::TEXT_DOMAIN),
            'add_new_item' => __('Añadir Nuevo Usuario Verificado', self::TEXT_DOMAIN),
            'new_item' => __('Nuevo Usuario Verificado', self::TEXT_DOMAIN),
            'edit_item' => __('Editar Usuario Verificado', self::TEXT_DOMAIN),
            'view_item' => __('Ver Usuario Verificado', self::TEXT_DOMAIN),
            'all_items' => __('Todos los Usuarios Verificados', self::TEXT_DOMAIN),
            'search_items' => __('Buscar Usuarios Verificados', self::TEXT_DOMAIN),
            'parent_item_colon' => __('Usuario Verificado Padre:', self::TEXT_DOMAIN),
            'not_found' => __('No se encontraron usuarios verificados.', self::TEXT_DOMAIN),
            'not_found_in_trash' => __('No hay usuarios verificados en la papelera.', self::TEXT_DOMAIN),
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'verified-user'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-users',
        ];

        register_post_type(self::CPT_SLUG, $args);
    }

    public function register_taxonomies()
    {
        // Aquí puedes registrar taxonomías si son necesarias
    }

    public function register_metaboxes()
    {
        \Carbon_Fields\Container::make('post_meta', __('Información del Usuario Verificado', self::TEXT_DOMAIN))
            ->where('post_type', '=', self::CPT_SLUG)
            ->add_fields([
                \Carbon_Fields\Field::make('association', 'verified_user', __('Usuario WordPress'))
                    ->set_types([
                        [
                            'type' => 'user',
                            'post_type' => '',
                        ]
                    ])
                    ->set_help_text(__('Selecciona el usuario de WordPress que estás verificando', self::TEXT_DOMAIN)),

                \Carbon_Fields\Field::make('select', 'verification_level', __('Nivel de Verificación'))
                    ->set_options([
                        'basic' => __('Verificación Básica', self::TEXT_DOMAIN),
                        'advanced' => __('Verificación Avanzada', self::TEXT_DOMAIN),
                        'official' => __('Cuenta Oficial', self::TEXT_DOMAIN),
                    ])
                    ->set_help_text(__('Selecciona el nivel de verificación para este usuario', self::TEXT_DOMAIN)),

                \Carbon_Fields\Field::make('date', 'verification_date', __('Fecha de Verificación'))
                    ->set_help_text(__('Fecha en que se verificó al usuario', self::TEXT_DOMAIN)),

                \Carbon_Fields\Field::make('text', 'verification_id', __('ID de Verificación'))
                    ->set_help_text(__('Identificador único de la verificación', self::TEXT_DOMAIN)),

                \Carbon_Fields\Field::make('image', 'verification_badge', __('Insignia de Verificación'))
                    ->set_help_text(__('Insignia que se mostrará junto al nombre del usuario', self::TEXT_DOMAIN)),
            ]);
    }
}

// Inicializar el plugin
new VerifiedUsersPlugin();
