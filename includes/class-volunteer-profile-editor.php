<?php

class Volunteer_Profile_Editor
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_update_volunteer_profile', [$this, 'update_profile']);
    }

    public function add_admin_menu()
    {
        add_submenu_page(
            null,
            'Editar Perfil de Voluntario',
            'Editar Perfil de Voluntario',
            'manage_options',
            'volunteer_profile_editor',
            [$this, 'render_edit_page']
        );
    }

    public function render_edit_page()
    {
        // VERIFICACIÓN MEJORADA DE PERMISOS
        if (!current_user_can('manage_options')) {
            $user = wp_get_current_user();
            $error_message = sprintf(
                'Usuario actual: %s, Capacidades: %s',
                $user->user_login,
                implode(', ', $user->caps)
            );
            wp_die('No tienes permisos suficientes para acceder a esta página.<br>' . $error_message);
        }

        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

        if (!$user_id) {
            wp_die('Usuario no especificado');
        }

        $user = get_userdata($user_id);
        $is_verified = get_user_meta($user_id, '_is_verified', true) === 'yes';
        $is_employer = get_user_meta($user_id, '_user_type', true) === 'employers';

        // Campos de selección múltiple
        $interest_areas = maybe_unserialize(get_user_meta($user_id, 'hv_interest_areas', true));
        $availability_days = maybe_unserialize(get_user_meta($user_id, 'hv_availability_days', true));

        // Opciones para selects
        $marital_status_options = ['Soltero/a', 'Casado/a', 'Divorciado/a', 'Viudo/a', 'Unión Libre'];
        $education_level_options = ['Primaria', 'Secundaria', 'Universidad', 'Postgrado', 'Técnico'];
        $blood_type_options = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $shirt_size_options = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        $gender_options = ['Masculino', 'Femenino', 'Otro'];
        $yes_no_options = ['Sí', 'No'];

        // Áreas de interés disponibles
        $available_interest_areas = [
            'Educación',
            'Salud',
            'Medio Ambiente',
            'Desarrollo Comunitario',
            'Ayuda Humanitaria',
            'Animales',
            'Adultos Mayores',
            'Niños/Jóvenes'
        ];

        // Días de disponibilidad
        $available_days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
?>
        <div class="wrap volunteer-profile-editor">
            <h1 class="wp-heading-inline">Editar Perfil de Voluntario</h1>
            <a href="<?php echo admin_url('admin.php?page=volunteer_profile&user_id=' . $user_id); ?>" class="page-title-action">
                ← Volver al perfil
            </a>

            <hr class="wp-header-end">

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="volunteer-profile-form">
                <input type="hidden" name="action" value="update_volunteer_profile">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <?php wp_nonce_field('update_volunteer_profile_nonce', '_wpnonce'); ?>

                <div class="volunteer-tabs">
                    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">Información Básica</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">Contacto</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="availability-tab" data-bs-toggle="tab" data-bs-target="#availability" type="button" role="tab">Disponibilidad</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="medical-tab" data-bs-toggle="tab" data-bs-target="#medical" type="button" role="tab">Información Médica</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="references-tab" data-bs-toggle="tab" data-bs-target="#references" type="button" role="tab">Referencias</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="verification-tab" data-bs-toggle="tab" data-bs-target="#verification" type="button" role="tab">Verificación</button>
                        </li>
                    </ul>

                    <div class="tab-content hv-profile-editor" id="profileTabsContent">
                        <!-- Pestaña Información Básica -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre completo *</label>
                                                <input type="text" class="form-control" name="display_name" value="<?php echo esc_attr($user->display_name); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Cédula *</label>
                                                <input type="text" class="form-control" name="hv_id_number" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_id_number', true)); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Fecha de Nacimiento *</label>
                                                <input type="date" class="form-control" name="hv_birth_date" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_birth_date', true)); ?>" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Género *</label>
                                                <select class="form-control" name="hv_gender" required>
                                                    <option value="">Seleccionar...</option>
                                                    <?php foreach ($gender_options as $option): ?>
                                                        <option value="<?php echo esc_attr($option); ?>" <?php selected(get_user_meta($user_id, 'hv_gender', true), $option); ?>>
                                                            <?php echo esc_html($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Estado Civil *</label>
                                                <select class="form-control" name="hv_marital_status" required>
                                                    <option value="">Seleccionar...</option>
                                                    <?php foreach ($marital_status_options as $option): ?>
                                                        <option value="<?php echo esc_attr($option); ?>" <?php selected(get_user_meta($user_id, 'hv_marital_status', true), $option); ?>>
                                                            <?php echo esc_html($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Nivel Académico *</label>
                                                <select class="form-control" name="hv_education_level" required>
                                                    <option value="">Seleccionar...</option>
                                                    <?php foreach ($education_level_options as $option): ?>
                                                        <option value="<?php echo esc_attr($option); ?>" <?php selected(get_user_meta($user_id, 'hv_education_level', true), $option); ?>>
                                                            <?php echo esc_html($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pestaña Contacto -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email *</label>
                                                <input type="email" class="form-control" name="user_email" value="<?php echo esc_attr($user->user_email); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Teléfono/WhatsApp *</label>
                                                <input type="text" class="form-control" name="hv_phone" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_phone', true)); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Provincia *</label>
                                                <input type="text" class="form-control" name="hv_province" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_province', true)); ?>" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Dirección *</label>
                                                <textarea class="form-control" name="hv_address" required><?php echo esc_textarea(get_user_meta($user_id, 'hv_address', true)); ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Nacionalidad</label>
                                                <input type="text" class="form-control" name="hv_nationality" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_nationality', true)); ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Profesión/Ocupación</label>
                                                <input type="text" class="form-control" name="hv_profession" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_profession', true)); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pestaña Disponibilidad -->
                        <div class="tab-pane fade" id="availability" role="tabpanel">
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h4>Áreas de Interés</h4>
                                        <div class="row">
                                            <?php foreach ($available_interest_areas as $area): ?>
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="hv_interest_areas[]"
                                                            id="area_<?php echo sanitize_title($area); ?>"
                                                            value="<?php echo esc_attr($area); ?>"
                                                            <?php echo (is_array($interest_areas) && in_array($area, $interest_areas)) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="area_<?php echo sanitize_title($area); ?>">
                                                            <?php echo esc_html($area); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <h4>Disponibilidad de Días</h4>
                                                <?php foreach ($available_days as $day): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="hv_availability_days[]"
                                                            id="day_<?php echo sanitize_title($day); ?>"
                                                            value="<?php echo esc_attr($day); ?>"
                                                            <?php echo (is_array($availability_days) && in_array($day, $availability_days)) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="day_<?php echo sanitize_title($day); ?>">
                                                            <?php echo esc_html($day); ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="hv_availability_hours" class="form-label">
                                                    Horas disponibles por día
                                                </label>
                                                <select class="form-select" id="hv_availability_hours" name="hv_availability_hours">
                                                    <option value="">Seleccionar</option>
                                                    <?php
                                                    $hours = ['2', '4', '6', '8', '12', '24'];
                                                    $selected_hours = get_user_meta($user_id, 'hv_availability_hours', true);
                                                    foreach ($hours as $hour) {
                                                        $selected = ($hour === $selected_hours) ? 'selected' : '';
                                                        echo '<option value="' . esc_attr($hour) . '" ' . $selected . '>' . esc_html($hour) . ' horas</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Disponible fines de semana</label>
                                                <select class="form-control" name="hv_weekend_availability">
                                                    <option value="">Seleccionar...</option>
                                                    <?php foreach ($yes_no_options as $option): ?>
                                                        <option value="<?php echo esc_attr($option); ?>" <?php selected(get_user_meta($user_id, 'hv_weekend_availability', true), $option); ?>>
                                                            <?php echo esc_html($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Disponible para viajar al interior</label>
                                                <select class="form-control" name="hv_travel_availability">
                                                    <option value="">Seleccionar...</option>
                                                    <?php foreach ($yes_no_options as $option): ?>
                                                        <option value="<?php echo esc_attr($option); ?>" <?php selected(get_user_meta($user_id, 'hv_travel_availability', true), $option); ?>>
                                                            <?php echo esc_html($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Disponible para misiones internacionales</label>
                                                <select class="form-control" name="hv_international_availability">
                                                    <option value="">Seleccionar...</option>
                                                    <?php foreach ($yes_no_options as $option): ?>
                                                        <option value="<?php echo esc_attr($option); ?>" <?php selected(get_user_meta($user_id, 'hv_international_availability', true), $option); ?>>
                                                            <?php echo esc_html($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pestaña Información Médica -->
                        <div class="tab-pane fade" id="medical" role="tabpanel">
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Tipo de Sangre</label>
                                                <select class="form-control" name="hv_blood_type">
                                                    <option value="">Seleccionar...</option>
                                                    <?php foreach ($blood_type_options as $option): ?>
                                                        <option value="<?php echo esc_attr($option); ?>" <?php selected(get_user_meta($user_id, 'hv_blood_type', true), $option); ?>>
                                                            <?php echo esc_html($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Talla de Camiseta</label>
                                                <select class="form-control" name="hv_shirt_size">
                                                    <option value="">Seleccionar...</option>
                                                    <?php foreach ($shirt_size_options as $option): ?>
                                                        <option value="<?php echo esc_attr($option); ?>" <?php selected(get_user_meta($user_id, 'hv_shirt_size', true), $option); ?>>
                                                            <?php echo esc_html($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Condición Médica</label>
                                                <textarea class="form-control" name="hv_medical_condition"><?php echo esc_textarea(get_user_meta($user_id, 'hv_medical_condition', true)); ?></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Limitaciones Físicas</label>
                                                <textarea class="form-control" name="hv_physical_limitations"><?php echo esc_textarea(get_user_meta($user_id, 'hv_physical_limitations', true)); ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">¿Tiene experiencia en voluntariado?</label>
                                                <select class="form-control" name="hv_has_experience">
                                                    <option value="">Seleccionar...</option>
                                                    <?php foreach ($yes_no_options as $option): ?>
                                                        <option value="<?php echo esc_attr($option); ?>" <?php selected(get_user_meta($user_id, 'hv_has_experience', true), $option); ?>>
                                                            <?php echo esc_html($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Descripción de experiencia</label>
                                                <textarea class="form-control" name="hv_experience_desc"><?php echo esc_textarea(get_user_meta($user_id, 'hv_experience_desc', true)); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pestaña Referencias -->
                        <div class="tab-pane fade" id="references" role="tabpanel">
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4>Referencia Personal 1</h4>
                                            <div class="mb-3">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" class="form-control" name="hv_reference1_name" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_reference1_name', true)); ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Teléfono</label>
                                                <input type="text" class="form-control" name="hv_reference1_phone" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_reference1_phone', true)); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <h4>Referencia Personal 2</h4>
                                            <div class="mb-3">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" class="form-control" name="hv_reference2_name" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_reference2_name', true)); ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Teléfono</label>
                                                <input type="text" class="form-control" name="hv_reference2_phone" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_reference2_phone', true)); ?>">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Pestaña Verificación -->
                        <div class="tab-pane fade" id="verification" role="tabpanel">
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Estado de verificación</label>
                                                <select class="form-control" name="_is_verified" <?php echo $is_verified ? 'disabled' : ''; ?>>
                                                    <option value="no" <?php selected(!$is_verified); ?>>No verificado</option>
                                                    <option value="yes" <?php selected($is_verified); ?>>Verificado</option>
                                                </select>
                                                <?php if ($is_verified): ?>
                                                    <small class="text-muted">Para cambiar el estado, contacte al administrador del sistema</small>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Código único</label>
                                                <input type="text" class="form-control" name="hv_unique_code" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_unique_code', true)); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Fecha de verificación</label>
                                                <input type="text" class="form-control" name="hv_date_received" value="<?php echo esc_attr(get_user_meta($user_id, 'hv_date_received', true)); ?>" readonly>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Observaciones</label>
                                                <textarea class="form-control" name="hv_received_observations"><?php echo esc_textarea(get_user_meta($user_id, 'hv_received_observations', true)); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!$is_verified && $is_employer): ?>
                                        <div class="alert alert-info">
                                            <p>Para verificar este voluntario, haga clic en el botón "Verificar Voluntario" en la página de perfil.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="<?php echo admin_url('admin.php?page=volunteer_profile&user_id=' . $user_id); ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
<?php
    }

    public function update_profile()
    {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update_volunteer_profile_nonce')) {
            wp_die('Acción no permitida');
        }

        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos para realizar esta acción');
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        if (!$user_id) {
            wp_die('ID de usuario no válido');
        }

        // Actualizar campos básicos del usuario
        if (isset($_POST['display_name']) && isset($_POST['user_email'])) {
            $userdata = [
                'ID' => $user_id,
                'display_name' => sanitize_text_field($_POST['display_name']),
                'user_email' => sanitize_email($_POST['user_email'])
            ];

            $result = wp_update_user($userdata);

            if (is_wp_error($result)) {
                wp_die('Error al actualizar los datos del usuario: ' . $result->get_error_message());
            }
        }

        // Lista de todos los campos de metadatos que podemos actualizar
        $meta_fields = [
            // Información básica
            'hv_id_number',
            'hv_birth_date',
            'hv_gender',
            'hv_marital_status',
            'hv_education_level',

            // Contacto
            'hv_phone',
            'hv_province',
            'hv_address',
            'hv_nationality',
            'hv_profession',

            // Disponibilidad
            'hv_availability_hours',
            'hv_weekend_availability',
            'hv_travel_availability',
            'hv_international_availability',
            'hv_has_experience',
            'hv_experience_desc',

            // Médica
            'hv_blood_type',
            'hv_shirt_size',
            'hv_medical_condition',
            'hv_physical_limitations',

            // Referencias
            'hv_reference1_name',
            'hv_reference1_phone',
            'hv_reference2_name',
            'hv_reference2_phone',

            // Verificación
            'hv_received_observations',
            '_is_verified'
        ];

        // Procesar campos de selección múltiple
        $interest_areas = isset($_POST['hv_interest_areas']) ? array_map('sanitize_text_field', $_POST['hv_interest_areas']) : [];
        update_user_meta($user_id, 'hv_interest_areas', $interest_areas);

        $availability_days = isset($_POST['hv_availability_days']) ? array_map('sanitize_text_field', $_POST['hv_availability_days']) : [];
        update_user_meta($user_id, 'hv_availability_days', $availability_days);

        // Actualizar el resto de metadatos
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                $value = is_array($_POST[$field]) ?
                    array_map('sanitize_text_field', $_POST[$field]) :
                    sanitize_text_field($_POST[$field]);

                update_user_meta($user_id, $field, $value);
            }
        }

        // Si se marca como verificado y no tiene código, generarlo
        if (get_user_meta($user_id, '_is_verified', true) === 'yes' && !get_user_meta($user_id, 'hv_unique_code', true)) {
            $code = 'VOL-' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
            update_user_meta($user_id, 'hv_unique_code', $code);
            update_user_meta($user_id, 'hv_date_received', current_time('mysql'));
        }

        // Redirigir de vuelta al perfil con mensaje de éxito
        wp_redirect(admin_url('admin.php?page=volunteer_profile&user_id=' . $user_id . '&updated=1'));
        exit;
    }
}
