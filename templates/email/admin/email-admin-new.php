<?php
// templates/emails/email-admin-new.php
include HV_PLUGIN_PATH . 'templates/email-header.php';

/**
 * Variables disponibles:
 * $user_id - ID del usuario registrado
 * $user_data - Array con datos del usuario
 * $document_url - URL del documento de identidad subido
 */
?>

<div class="header">
    <h2>Nuevo Voluntario Registrado</h2>
</div>

<div class="content">
    <p>Hola equipo,</p>

    <p>Se ha registrado un nuevo voluntario en la plataforma:</p>

    <ul>
        <li><strong>Nombre:</strong> <?php echo $name; ?></li>
        <li><strong>Email:</strong> <?php echo $email; ?></li>
        <li><strong>Teléfono:</strong> <?php echo $phone; ?></li>
        <li><strong>Provincia:</strong> <?php echo $province; ?></li>
        <li><strong>Habilidades:</strong> <?php echo $skills; ?></li>
    </ul>

    <p>Puedes revisar el perfil completo del voluntario aquí:</p>
    <p><a href="<?php echo $profile_link; ?>"><?php echo $profile_link; ?></a></p>

    <p>Por favor verifica la información y procede con la validación del documento de identidad.</p>
</div>

<div class="footer">
    <p>Este es un mensaje automático. Por favor no respondas a este correo.</p>
    <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?></p>
</div>


<?php include HV_PLUGIN_PATH . 'templates/email-footer.php'; ?>