<?php
// templates/emails/email-admin-new.php
include HV_PLUGIN_PATH . 'templates/emails/email-header.php';

/**
 * Variables disponibles:
 * $user_id - ID del usuario registrado
 * $user_data - Array con datos del usuario
 * $document_url - URL del documento de identidad subido
 */
?>

<h2 style="color: #0d6efd; margin-top: 0;">Nuevo Voluntario Registrado</h2>
<p>Se ha registrado un nuevo voluntario en la plataforma. Aquí los detalles:</p>

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
        <td>
            <?php echo esc_html($user_data['skills']); ?>
            <?php if (!empty($user_data['skills_other'])) : ?>
                <br><small>Especificación: <?php echo esc_html($user_data['skills_other']); ?></small>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td>Documento de identidad:</td>
        <td>
            <?php if ($document_url) : ?>
                <a href="<?php echo esc_url($document_url); ?>" target="_blank">
                    Ver documento de identidad
                </a>
            <?php else : ?>
                No se subió documento
            <?php endif; ?>
        </td>
    </tr>
</table>

<h3>Disponibilidad</h3>
<p>
    <strong>Fines de semana:</strong> <?php echo esc_html($user_data['weekend_availability']); ?><br>
    <strong>Viajar al interior:</strong> <?php echo esc_html($user_data['travel_availability']); ?>
</p>

<h3>Experiencia en Voluntariado</h3>
<p>
    <strong>¿Tiene experiencia?:</strong> <?php echo esc_html($user_data['has_experience']); ?><br>
    <?php if (!empty($user_data['experience_desc'])) : ?>
        <strong>Descripción:</strong> <?php echo esc_html($user_data['experience_desc']); ?>
    <?php endif; ?>
</p>

<p style="text-align: center; margin-top: 30px;">
    <a href="<?php echo admin_url('admin.php?page=volunteers'); ?>" class="btn">
        Ver perfil completo en el panel
    </a>
</p>

<?php include HV_PLUGIN_PATH . 'templates/emails/email-footer.php'; ?>