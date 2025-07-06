<?php
// templates/emails/email-user-pending.php
include HV_PLUGIN_PATH . 'templates/emails/email-header.php';

/**
 * Variables disponibles:
 * $user_data - Array con datos del usuario
 */
?>

<h2 style="color: #0d6efd; margin-top: 0;">¡Gracias por registrarte como voluntario!</h2>
<p>Hola <?php echo esc_html($user_data['full_name']); ?>,</p>

<p>Hemos recibido tu solicitud para unirte a nuestro equipo de voluntarios en Humanitarios. Estamos revisando tu información y pronto te notificaremos sobre el estado de tu registro.</p>

<p>Estos son los datos que hemos recibido:</p>

<table>
    <tr>
        <td>Nombre completo:</td>
        <td><?php echo esc_html($user_data['full_name']); ?></td>
    </tr>
    <tr>
        <td>Correo electrónico:</td>
        <td><?php echo esc_html($user_data['email']); ?></td>
    </tr>
    <tr>
        <td>Teléfono:</td>
        <td><?php echo esc_html($user_data['phone']); ?></td>
    </tr>
    <tr>
        <td>Provincia:</td>
        <td><?php echo esc_html($user_data['province']); ?></td>
    </tr>
    <tr>
        <td>Área de interés:</td>
        <td><?php echo esc_html($user_data['skills']); ?></td>
    </tr>
</table>

<p>Si necesitas actualizar tu información, por favor responde a este correo.</p>

<p>¡Gracias por tu interés en ser parte de Humanitarios!</p>

<p style="margin-top: 30px;">
    Atentamente,<br>
    <strong>Equipo Humanitarios</strong>
</p>

<?php include HV_PLUGIN_PATH . 'templates/emails/email-footer.php'; ?>