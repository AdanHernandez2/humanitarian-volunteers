<?php
// templates/emails/email-user-verified.php
include HV_PLUGIN_PATH . 'templates/emails/email-header.php';

/**
 * Variables disponibles:
 * $user_data - Array con datos del usuario
 * $unique_code - Código único del voluntario
 */
?>

<h2 style="color: #0d6efd; margin-top: 0;">¡Felicidades, eres ahora un voluntario verificado!</h2>
<p>Hola <?php echo esc_html($user_data['full_name']); ?>,</p>

<p>Nos complace informarte que tu registro como voluntario ha sido aprobado. Ahora formas parte de nuestra red de voluntarios humanitarios.</p>

<div style="background-color: #f0f8ff; border-left: 4px solid #0d6efd; padding: 15px; margin: 20px 0;">
    <h3 style="margin-top: 0; color: #0d6efd;">Tu código único de voluntario</h3>
    <p style="font-size: 24px; font-weight: bold; text-align: center; margin: 15px 0;">
        <?php echo esc_html($unique_code); ?>
    </p>
    <p>Este código te identificará en nuestras actividades y eventos. Te recomendamos guardarlo.</p>
</div>

<p>Puedes acceder a tu perfil en cualquier momento para actualizar tu información o ver las actividades disponibles.</p>

<p style="text-align: center; margin-top: 30px;">
    <a href="<?php echo home_url('/mi-perfil'); ?>" class="btn">
        Acceder a mi perfil
    </a>
</p>

<p>¡Gracias por unirte a esta causa humanitaria!</p>

<p style="margin-top: 30px;">
    Atentamente,<br>
    <strong>Equipo Humanitarios</strong>
</p>

<?php include HV_PLUGIN_PATH . 'templates/emails/email-footer.php'; ?>