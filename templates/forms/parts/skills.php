<?php

/**
 * Sección: Área de Habilidades/Interés
 */

use VolunteerForm\Fields as Field;

$interest_areas = [
    'Respuesta en emergencias y desastres' => 'Respuesta en emergencias y desastres',
    'Reconstrucción y desarrollo comunitario' => 'Reconstrucción y desarrollo comunitario',
    'Captación y distribución de donaciones' => 'Captación y distribución de donaciones',
    'Búsqueda y rescate' => 'Búsqueda y rescate',
    'Asistencia médica' => 'Asistencia médica',
    'Asistencia psicológica' => 'Asistencia psicológica',
    'Búsqueda de personas, objetos y animales desaparecidos' => 'Búsqueda de personas, objetos y animales desaparecidos',
    'Promoción en redes sociales' => 'Promoción en redes sociales',
    'Área legal' => 'Área legal',
    'Manejo de fondos' => 'Manejo de fondos',
    'Logística' => 'Logística',
    'Carga y descarga de ayuda humanitaria' => 'Carga y descarga de ayuda humanitaria',
    'Captación de fondos' => 'Captación de fondos',
    'Captación de voluntarios' => 'Captación de voluntarios',
    'Apoyo tecnológico' => 'Apoyo tecnológico',
    'Transportación (chofer)' => 'Transportación (chofer)'
];

$selected_areas = isset($user_meta['hv_interest_areas']) ? maybe_unserialize($user_meta['hv_interest_areas']) : [];
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Área de Habilidades / Interés</h3>
    </div>
    <div class="card-body">
        <?php
        Field::checkbox_group(
            'interest_areas',
            '¿En qué áreas te gustaría colaborar? (Marcar con una X)',
            $interest_areas,
            $selected_areas,
            true,
            'x-checkbox',
        );
        ?>
    </div>
</div>