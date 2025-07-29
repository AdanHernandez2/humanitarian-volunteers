<?php
// Seguridad: Bloquear acceso directo
defined('ABSPATH') || exit();

// Redirigir si el usuario no está logueado
if (!is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

// Precargar datos del usuario
$current_user = wp_get_current_user();
$user_data = [
    'full_name' => $current_user->exists() ? $current_user->display_name : '',
    'email' => $current_user->exists() ? $current_user->user_email : '',
    'first_name' => $current_user->exists() ? $current_user->first_name : '',
    'last_name' => $current_user->exists() ? $current_user->last_name : '',
];

// Obtener metadatos del usuario
$user_meta = [];
if ($current_user->exists()) {
    $meta_keys = [
        'hv_id_number',
        'hv_birth_date',
        'hv_province',
        'hv_phone',
        'hv_skills',
        'hv_skills_other',
        'hv_weekend_availability',
        'hv_travel_availability',
        'hv_has_experience',
        'hv_experience_desc',
        'hv_nationality',
        'hv_gender',
        'hv_blood_type',
        'hv_shirt_size',
        'hv_profession',
        'hv_medical_condition',
        'hv_references',
        'hv_marital_status',
        'hv_address',
        'hv_education_level',
        'hv_interest_areas',
        'hv_availability_days',
        'hv_availability_hours',
        'hv_international_availability',
        'hv_physical_limitations',
        'hv_reference1_name',
        'hv_reference1_phone',
        'hv_reference2_name',
        'hv_reference2_phone',
        'hv_signature',
        'hv_accept_terms'
    ];

    foreach ($meta_keys as $key) {
        $user_meta[$key] = get_user_meta($current_user->ID, $key, true);
    }
    $user_meta['hv_identity_document'] = get_user_meta($current_user->ID, 'hv_identity_document', true);
}
?>

<div class="container hv-form-container">
    <form id="volunteer-registration-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="volunteer_submit">
        <?php wp_nonce_field('volunteer_form_action', 'volunteer_nonce'); ?>