<?php
// templates/emails/email-user-verified.php
include HV_PLUGIN_PATH . 'templates/email/email-header.php';

?>

<div class="header">
    <h2>Perfil de Voluntario Actualizado</h2>
</div>

<div class="content">
    <p>Hola equipo,</p>

    <p>El voluntario <?php echo $name; ?> ha actualizado su perfil:</p>

    <div class="changes">
        <p><strong>Cambios realizados:</strong></p>
        <ul>
            <?php foreach ($changes as $field => $value): ?>
                <?php if (!empty($value)): ?>
                    <li><strong><?php echo ucfirst(str_replace('_', ' ', $field)); ?>:</strong> <?php echo $value; ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>

    <p>Puedes revisar el perfil completo del voluntario aquí:</p>
    <p><a href="<?php echo $profile_link; ?>"><?php echo $profile_link; ?></a></p>

    <p>Por favor verifica si los cambios requieren alguna acción adicional.</p>
</div>

<div class="footer">
    <p>Este es un mensaje automático. Por favor no respondas a este correo.</p>
    <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?></p>
</div>


<?php include HV_PLUGIN_PATH . 'templates/email/email-footer.php'; ?>