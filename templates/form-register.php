<?php

// Seguridad: Bloquear acceso directo
defined('ABSPATH') || exit();

// Redirigir si el usuario no está logueado
if (!is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}
// Precargar datos del usuario si está logueado
$current_user = wp_get_current_user();
$user_data = [
    'full_name' => $current_user->exists() ? $current_user->display_name : '',
    'email' => $current_user->exists() ? $current_user->user_email : '',
    'first_name' => $current_user->exists() ? $current_user->first_name : '',
    'last_name' => $current_user->exists() ? $current_user->last_name : '',
];

// Obtener todos los metadatos del usuario si está logueado
$user_meta = [];
if ($current_user->exists()) {
    // Lista de todos los metadatos que hemos guardado
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
        'hv_references'
    ];

    foreach ($meta_keys as $key) {
        $user_meta[$key] = get_user_meta($current_user->ID, $key, true);
    }

    // Obtener documento subido si existe
    $user_meta['hv_identity_document'] = get_user_meta($current_user->ID, 'hv_identity_document', true);
}
?>

<div class="container hv-form-container">
    <form id="volunteer-registration-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="volunteer_submit">
        <?php wp_nonce_field('volunteer_form_action', 'volunteer_nonce'); ?>

        <!-- Información Personal -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Información Personal</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                            value="<?php echo esc_attr($user_data['first_name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Apellido <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                            value="<?php echo esc_attr($user_data['last_name']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_number" class="form-label">Cédula</label>
                        <input type="text" class="form-control" id="id_number" name="id_number"
                            value="<?php echo isset($user_meta['hv_id_number']) ? esc_attr($user_meta['hv_id_number']) : ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="birth_date" class="form-label">Fecha de nacimiento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date"
                            value="<?php echo isset($user_meta['hv_birth_date']) ? esc_attr($user_meta['hv_birth_date']) : ''; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="province" class="form-label">Provincia de residencia <span class="text-danger">*</span></label>
                        <select class="form-select" id="province" name="province" required>
                            <option value="">Seleccionar</option>
                            <?php
                            $provinces = ['San José', 'Alajuela', 'Cartago', 'Heredia', 'Guanacaste', 'Puntarenas', 'Limón'];
                            $selected_province = isset($user_meta['hv_province']) ? $user_meta['hv_province'] : '';

                            foreach ($provinces as $province) {
                                $selected = ($province === $selected_province) ? 'selected' : '';
                                echo '<option value="' . esc_attr($province) . '" ' . $selected . '>' . esc_html($province) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Teléfono / WhatsApp <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                            value="<?php echo isset($user_meta['hv_phone']) ? esc_attr($user_meta['hv_phone']) : ''; ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo esc_attr($user_data['email']); ?>" required>
                </div>
            </div>
        </div>

        <!-- Área de Habilidades / Interés -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Área de Habilidades / Interés</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Selecciona tu área de interés <span class="text-danger">*</span></label>
                    <?php
                    $skills_options = [
                        'Emergencias y desastres',
                        'Logística y distribución',
                        'Salud (médico, enfermería, psicología)',
                        'Tecnología / Soporte',
                        'Otro'
                    ];

                    $selected_skill = isset($user_meta['hv_skills']) ? $user_meta['hv_skills'] : '';
                    ?>

                    <?php foreach ($skills_options as $index => $skill): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="skills"
                                id="skills<?php echo $index + 1; ?>"
                                value="<?php echo esc_attr($skill); ?>"
                                <?php if ($skill === $selected_skill) echo 'checked'; ?>
                                required>
                            <label class="form-check-label" for="skills<?php echo $index + 1; ?>">
                                <?php echo esc_html($skill); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mb-3" id="skills_other_container"
                    style="<?php echo ($selected_skill === 'Otro') ? '' : 'display:none;'; ?>">
                    <label for="skills_other" class="form-label">Especificar otro</label>
                    <input type="text" class="form-control" id="skills_other" name="skills_other"
                        value="<?php echo isset($user_meta['hv_skills_other']) ? esc_attr($user_meta['hv_skills_other']) : ''; ?>"
                        <?php echo ($selected_skill === 'Otro') ? 'required' : ''; ?>>
                </div>
            </div>
        </div>

        <!-- Disponibilidad -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Disponibilidad</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">¿Disponible fines de semana? <span class="text-danger">*</span></label>
                        <?php
                        $weekend_availability = isset($user_meta['hv_weekend_availability']) ? $user_meta['hv_weekend_availability'] : '';
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="weekend_availability"
                                id="weekend_yes" value="Sí"
                                <?php if ($weekend_availability === 'Sí') echo 'checked'; ?> required>
                            <label class="form-check-label" for="weekend_yes">Sí</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="weekend_availability"
                                id="weekend_no" value="No"
                                <?php if ($weekend_availability === 'No') echo 'checked'; ?> required>
                            <label class="form-check-label" for="weekend_no">No</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">¿Puede viajar al interior? <span class="text-danger">*</span></label>
                        <?php
                        $travel_availability = isset($user_meta['hv_travel_availability']) ? $user_meta['hv_travel_availability'] : '';
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="travel_availability"
                                id="travel_yes" value="Sí"
                                <?php if ($travel_availability === 'Sí') echo 'checked'; ?> required>
                            <label class="form-check-label" for="travel_yes">Sí</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="travel_availability"
                                id="travel_no" value="No"
                                <?php if ($travel_availability === 'No') echo 'checked'; ?> required>
                            <label class="form-check-label" for="travel_no">No</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Experiencia en Voluntariado -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Experiencia en Voluntariado</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">¿Tiene experiencia? <span class="text-danger">*</span></label>
                    <?php
                    $has_experience = isset($user_meta['hv_has_experience']) ? $user_meta['hv_has_experience'] : '';
                    ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_experience"
                            id="experience_yes" value="Sí"
                            <?php if ($has_experience === 'Sí') echo 'checked'; ?> required>
                        <label class="form-check-label" for="experience_yes">Sí</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_experience"
                            id="experience_no" value="No"
                            <?php if ($has_experience === 'No') echo 'checked'; ?> required>
                        <label class="form-check-label" for="experience_no">No</label>
                    </div>
                </div>
                <div class="mb-3" id="experience_desc_container"
                    style="<?php echo ($has_experience === 'Sí') ? '' : 'display:none;'; ?>">
                    <label for="experience_desc" class="form-label">Descripción breve</label>
                    <textarea class="form-control" id="experience_desc" name="experience_desc" rows="3"
                        <?php echo ($has_experience === 'Sí') ? 'required' : ''; ?>><?php
                                                                                    echo isset($user_meta['hv_experience_desc']) ? esc_textarea($user_meta['hv_experience_desc']) : '';
                                                                                    ?></textarea>
                </div>
            </div>
        </div>

        <!-- Información Adicional -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Información Adicional (Opcional)</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nationality" class="form-label">Nacionalidad</label>
                        <input type="text" class="form-control" id="nationality" name="nationality"
                            value="<?php echo isset($user_meta['hv_nationality']) ? esc_attr($user_meta['hv_nationality']) : ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label">Género</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="">Seleccionar</option>
                            <?php
                            $genders = ['Masculino', 'Femenino', 'Otro', 'Prefiero no decirlo'];
                            $selected_gender = isset($user_meta['hv_gender']) ? $user_meta['hv_gender'] : '';

                            foreach ($genders as $gender) {
                                $selected = ($gender === $selected_gender) ? 'selected' : '';
                                echo '<option value="' . esc_attr($gender) . '" ' . $selected . '>' . esc_html($gender) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="blood_type" class="form-label">Tipo de sangre</label>
                        <select class="form-select" id="blood_type" name="blood_type">
                            <option value="">Seleccionar</option>
                            <?php
                            $blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                            $selected_blood = isset($user_meta['hv_blood_type']) ? $user_meta['hv_blood_type'] : '';

                            foreach ($blood_types as $type) {
                                $selected = ($type === $selected_blood) ? 'selected' : '';
                                echo '<option value="' . esc_attr($type) . '" ' . $selected . '>' . esc_html($type) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="shirt_size" class="form-label">Talla de camiseta</label>
                        <select class="form-select" id="shirt_size" name="shirt_size">
                            <option value="">Seleccionar</option>
                            <?php
                            $shirt_sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                            $selected_size = isset($user_meta['hv_shirt_size']) ? $user_meta['hv_shirt_size'] : '';

                            foreach ($shirt_sizes as $size) {
                                $selected = ($size === $selected_size) ? 'selected' : '';
                                echo '<option value="' . esc_attr($size) . '" ' . $selected . '>' . esc_html($size) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="profession" class="form-label">Profesión</label>
                        <input type="text" class="form-control" id="profession" name="profession"
                            value="<?php echo isset($user_meta['hv_profession']) ? esc_attr($user_meta['hv_profession']) : ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="medical_condition" class="form-label">Condición médica</label>
                        <input type="text" class="form-control" id="medical_condition" name="medical_condition"
                            value="<?php echo isset($user_meta['hv_medical_condition']) ? esc_attr($user_meta['hv_medical_condition']) : ''; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="references" class="form-label">Referencias</label>
                    <textarea class="form-control" id="references" name="references" rows="3"><?php
                                                                                                echo isset($user_meta['hv_references']) ? esc_textarea($user_meta['hv_references']) : '';
                                                                                                ?></textarea>
                </div>
            </div>
        </div>

        <!-- Documento de identidad -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Documento de identidad</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="identity_document" class="form-label">Subir documento de identidad (foto o escaneo)
                        <?php if (!isset($user_meta['hv_identity_document']) || empty($user_meta['hv_identity_document'])): ?>
                            <span class="text-danger">*</span>
                        <?php endif; ?>
                    </label>
                    <input type="file" class="form-control" id="identity_document" name="identity_document" accept="image/*,.pdf">
                    <div class="form-text">Formatos aceptados: JPG, PNG, PDF. Tamaño máximo: 5MB.</div>
                </div>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="terms_conditions" name="terms_conditions" required>
            <label class="form-check-label" for="terms_conditions">Acepto los términos y condiciones <span class="text-danger">*</span></label>
        </div>

        <button type="submit" class="btn btn-primary" id="submit-btn">
            <?php echo ($current_user->exists()) ? 'Actualizar Perfil' : 'Enviar Registro'; ?>
        </button>

        <!-- Mensaje de respuesta -->
        <div id="form-message" class="mt-3" style="display:none;">
            <div class="alert alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <span class="message-content"></span>
            </div>
        </div>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
        // Mostrar campo "Otro" en habilidades si se selecciona
        $('input[name="skills"]').change(function() {
            if ($(this).val() === 'Otro') {
                $('#skills_other_container').show();
                $('#skills_other').prop('required', true);
            } else {
                $('#skills_other_container').hide();
                $('#skills_other').prop('required', false);
            }
        });

        // Mostrar campo de descripción de experiencia si se selecciona "Sí"
        $('input[name="has_experience"]').change(function() {
            if ($(this).val() === 'Sí' && $(this).is(':checked')) {
                $('#experience_desc_container').show();
                $('#experience_desc').prop('required', true);
            } else {
                $('#experience_desc_container').hide();
                $('#experience_desc').prop('required', false);
            }
        });

        // Manejar envío del formulario
        $('#volunteer-registration-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var formData = new FormData(this);
            var messageContainer = $('#form-message');
            var alertBox = messageContainer.find('.alert');
            var messageContent = messageContainer.find('.message-content');
            var submitBtn = $('#submit-btn');

            // Resetear mensaje
            messageContainer.hide();
            alertBox.removeClass('alert-success alert-danger');
            messageContent.text('');

            // Mostrar estado de envío
            messageContainer.show();
            alertBox.addClass('alert-info');
            messageContent.text('Enviando formulario...');
            submitBtn.prop('disabled', true);

            // Scroll al mensaje
            messageContainer[0].scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            // Timeout para prevenir bloqueos
            var timeout = setTimeout(function() {
                if (!form.data('completed')) {
                    showMessage('Tiempo de espera agotado. Intenta nuevamente.', 'danger');
                    submitBtn.prop('disabled', false);
                }
            }, 15000);

            form.data('completed', false);

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    clearTimeout(timeout);
                    form.data('completed', true);

                    if (response.success) {
                        showMessage(response.message, 'success');

                        // Solo resetear si no es usuario existente
                        if (!<?php echo $current_user->exists() ? 'true' : 'false'; ?>) {
                            form[0].reset();
                        }
                    } else {
                        showMessage(response.message, 'danger');
                    }
                },
                error: function(xhr) {
                    clearTimeout(timeout);
                    form.data('completed', true);

                    var errorMessage = 'Error en el servidor. Intenta nuevamente.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            var json = JSON.parse(xhr.responseText);
                            if (json.message) errorMessage = json.message;
                        } catch (e) {}
                    }

                    showMessage(errorMessage, 'danger');
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                }
            });

            function showMessage(message, type) {
                alertBox.removeClass('alert-info alert-success alert-danger')
                    .addClass('alert-' + type)
                    .find('.message-content').text(message);

                // Scroll al mensaje después de actualizar
                setTimeout(function() {
                    messageContainer[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 100);
            }
        });
    });
</script>

<?php if (is_user_logged_in()): ?>
    <script>
        jQuery(document).ready(function($) {
            // Verificar si el usuario ya tiene documento subido
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'check_user_document',
                    user_id: '<?php echo get_current_user_id(); ?>',
                    security: '<?php echo wp_create_nonce('hv_profile_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.has_document) {
                        $('#identity_document').closest('.mb-3').after(
                            '<div class="alert alert-info mt-3">Ya tienes un documento subido. Sube uno nuevo solo si quieres actualizarlo.</div>'
                        );
                    }
                },
                error: function() {
                    console.log('Error al verificar documento existente');
                }
            });
        });
    </script>
<?php endif; ?>