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

        <!-- Términos y condiciones -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Autorización y Compromiso</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p>Declaro que la información proporcionada es verídica y me comprometo a cumplir con las normas y valores de la institución. Entiendo que mi participación como voluntario es de carácter altruista y sin remuneración económica.</p>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="terms_conditions" name="terms_conditions" required
                            <?php echo isset($user_meta['hv_accept_terms']) && $user_meta['hv_accept_terms'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="terms_conditions">Acepto los términos y condiciones <span class="text-danger">*</span></label>
                    </div>
                </div>
            </div>
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

                // Mostrar campo de experiencia si se selecciona "Sí"
                $('input[name="has_experience"]').change(function() {
                    const show = ($(this).val() === 'Sí' && $(this).is(':checked'));
                    $('#experience_desc_container').toggle(show);
                    $('#experience_desc').prop('required', show);
                });

                // Manejar envío del formulario
                $('#volunteer-registration-form').on('submit', function(e) {
                    e.preventDefault();
                    const form = $(this);
                    const formData = new FormData(this);
                    const messageContainer = $('#form-message');
                    const alertBox = messageContainer.find('.alert');
                    const messageContent = messageContainer.find('.message-content');
                    const submitBtn = $('#submit-btn');

                    // Resetear mensaje
                    messageContainer.hide().removeClass('d-block');
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
                    const timeout = setTimeout(() => {
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
                        success: (response) => {
                            clearTimeout(timeout);
                            form.data('completed', true);
                            response.success ?
                                showMessage(response.message, 'success') :
                                showMessage(response.message, 'danger');
                        },
                        error: (xhr) => {
                            clearTimeout(timeout);
                            form.data('completed', true);
                            let errorMessage = 'Error en el servidor. Intenta nuevamente.';

                            try {
                                const json = JSON.parse(xhr.responseText);
                                if (json.message) errorMessage = json.message;
                            } catch (e) {
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                            }

                            showMessage(errorMessage, 'danger');
                        },
                        complete: () => submitBtn.prop('disabled', false)
                    });

                    function showMessage(message, type) {
                        alertBox.removeClass('alert-info alert-success alert-danger')
                            .addClass('alert-' + type)
                            .find('.message-content').text(message);

                        setTimeout(() => {
                            messageContainer[0].scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }, 100);
                    }
                });

                <?php if (is_user_logged_in()): ?>
                    // Verificar documento existente
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'check_user_document',
                            user_id: '<?php echo get_current_user_id(); ?>',
                            security: '<?php echo wp_create_nonce('hv_profile_nonce'); ?>'
                        },
                        success: (response) => {
                            if (response.success && response.data.has_document) {
                                $('#identity_document').closest('.mb-3').after(
                                    '<div class="alert alert-info mt-3">Ya tienes un documento subido. Sube uno nuevo solo si quieres actualizarlo.</div>'
                                );
                            }
                        },
                        error: () => console.log('Error al verificar documento')
                    });
                <?php endif; ?>
            });
        </script>