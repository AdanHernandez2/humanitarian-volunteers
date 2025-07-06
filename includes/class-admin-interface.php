<?php
class Admin_Interface
{
    public function __construct()
    {
        add_action('user_row_actions', [$this, 'add_quick_verify_action'], 10, 2);
    }

    /**
     * Añadir acción rápida en la lista de usuarios
     */
    public function add_quick_verify_action($actions, $user)
    {
        // Solo para usuarios employers
        if (get_user_meta($user->ID, '_user_type', true) === 'employers') {
            $is_verified = get_user_meta($user->ID, '_is_verified', true) === 'yes' ||
                get_user_meta($user->ID, 'identity_verified', true) === '1';

            if (!$is_verified) {
                $verify_url = wp_nonce_url(
                    add_query_arg([
                        'action' => 'verify_volunteer',
                        'user_id' => $user->ID
                    ], admin_url('admin-post.php')),
                    'verify_volunteer_' . $user->ID
                );

                $actions['verify'] = '<a href="' . esc_url($verify_url) . '">Verificar</a>';
            }
        }
        return $actions;
    }
}
