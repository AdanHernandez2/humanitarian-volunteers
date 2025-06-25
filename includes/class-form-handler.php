<?php
class Volunteer_Form_Handler {
    public function __construct() {
        add_action('admin_post_nopriv_volunteer_submit', [$this, 'handle_submission']);
        add_action('admin_post_volunteer_submit', [$this, 'handle_submission']);
    }

    public function handle_submission() {
        // Validar nonce y campos
        $data = [
            'full_name' => sanitize_text_field($_POST['full_name']),
            'email' => sanitize_email($_POST['email']),
            // ... otros campos
        ];

        // Crear post
        $post_id = wp_insert_post([
            'post_type' => 'volunteer',
            'post_status' => 'publish',
            'post_title' => $data['full_name']
        ]);

        // Guardar campos con Carbon Fields
        update_post_meta($post_id, '_hv_email', $data['email']);
        // ... otros campos

        // Disparar emails
        do_action('volunteer_registered', $post_id, $data);

        // Redirigir
        wp_redirect(home_url('/gracias'));
        exit;
    }
}
new Volunteer_Form_Handler();