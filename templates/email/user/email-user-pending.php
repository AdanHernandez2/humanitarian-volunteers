<?php
// templates/emails/email-user-pending.php
include HV_PLUGIN_PATH . 'templates/email-header.php';

/**
 * Variables disponibles:
 * $user_data - Array con datos del usuario
 */
?>

<div class="header">
    <h2>¡Gracias por Registrarte!</h2>
</div>

<div class="content">
    <p>Hola <?php echo $name; ?>,</p>

    <p>Gracias por registrarte como voluntario en <?php echo $site_name; ?>. Hemos recibido tu solicitud y estamos procesando tu información.</p>

    <p>Nuestro equipo revisará tus datos y documento de identidad. Te notificaremos por correo electrónico una vez que tu registro haya sido verificado.</p>

    <p>Este proceso puede tomar de 1 a 3 días hábiles. Si necesitas información adicional, puedes contactarnos en <?php echo $contact_email; ?>.</p>

    <p>¡Gracias por tu interés en ser parte de nuestro equipo de voluntarios!</p>
</div>

<div class="footer">
    <p>Este es un mensaje automático. Por favor no respondas a este correo.</p>
    <p>&copy; <?php echo date('Y'); ?> <?php echo $site_name; ?></p>
</div>


<?php include HV_PLUGIN_PATH . 'templates/email-footer.php'; ?>