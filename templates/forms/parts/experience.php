<?php

/**
 * Sección: Experiencia en Voluntariado
 */

use VolunteerForm\Fields as Field;

$has_experience = $user_meta['hv_has_experience'] ?? '';
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Experiencia en Voluntariado</h3>
    </div>
    <div class="card-body">
        <?php
        Field::radio(
            'has_experience',
            '¿Tiene experiencia? ',
            ['Sí' => 'Sí', 'No' => 'No'],
            $has_experience,
            true
        );
        ?>

        <div class="mb-3" id="experience_desc_container" style="<?php echo ($has_experience === 'Sí') ? '' : 'display:none;'; ?>">
            <?php
            Field::textarea(
                'experience_desc',
                'Descripción breve',
                $user_meta['hv_experience_desc'] ?? '',
                $has_experience === 'Sí', // Required solo si tiene experiencia
                3
            );
            ?>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Mostrar/ocultar campo de descripción basado en selección
        $('input[name="has_experience"]').change(function() {
            if ($(this).val() === 'Sí') {
                $('#experience_desc_container').show();
                $('#experience_desc').prop('required', true);
            } else {
                $('#experience_desc_container').hide();
                $('#experience_desc').prop('required', false);
            }
        });
    });
</script>