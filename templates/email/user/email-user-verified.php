<?php
// templates/emails/email-user-verified.php
include HV_PLUGIN_PATH . 'templates/email/email-header.php';

/**
 * Variables disponibles:
 * $user_data - Array con datos del usuario
 * $unique_code - Código único del voluntario
 */
?>

<div class="header">
    <h2>¡Felicidades <?php echo $name; ?>!</h2>
    <p>Tu registro como voluntario ha sido verificado</p>
</div>

<div class="content">
    <p>Estamos encantados de informarte que tu registro como voluntario en <?php echo $site_name; ?> ha sido verificado y aprobado.</p>

    <p>Tu código único de voluntario es:</p>

    <div class="verification-code">
        <?php echo $code; ?>
    </div>

    <p>Este código te identificará como voluntario en nuestras actividades. Por favor guárdalo en un lugar seguro.</p>

    <p>Adjunto a este correo encontrarás tu credencial digital de voluntario. Puedes imprimirla o guardarla en tu dispositivo móvil.</p>

    <p>Pronto te contactaremos con información sobre próximas actividades y oportunidades de voluntariado.</p>

    <p>¡Gracias por unirte a nuestra comunidad de voluntarios!</p>
</div>

<div class="footer">
    <p>Este es un mensaje automático. Por favor no respondas a este correo.</p>
</div>


<?php include HV_PLUGIN_PATH . 'templates/email/email-footer.php'; ?>