<?php

/**
 * Sección: Disponibilidad
 */

use VolunteerForm\Fields as Field;
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Disponibilidad</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Días de disponibilidad -->
            <div class="col-md-6 mb-3">
                <?php
                $days = [
                    'Lunes' => 'Lunes',
                    'Martes' => 'Martes',
                    'Miércoles' => 'Miércoles',
                    'Jueves' => 'Jueves',
                    'Viernes' => 'Viernes',
                    'Sábado' => 'Sábado',
                    'Domingo' => 'Domingo'
                ];
                $selected_days = isset($user_meta['hv_availability_days']) ? maybe_unserialize($user_meta['hv_availability_days']) : [];

                Field::checkbox_group(
                    'availability_days',
                    'Días de disponibilidad',
                    $days,
                    $selected_days,
                    true
                );
                ?>
                <small class="form-text text-muted">Selecciona todos los días en los que puedes colaborar.</small>
            </div>

            <!-- Horas disponibles por día -->
            <div class="col-md-6 mb-3">
                <?php
                Field::select('availability_hours', 'Horas disponibles por día ', [
                    '2' => '2 horas',
                    '4' => '4 horas',
                    '6' => '6 horas',
                    '8' => '8 horas',
                    '12' => '12 horas',
                    '24' => '24 horas'
                ], $user_meta['hv_availability_hours'] ?? '', true);
                ?>
            </div>
        </div>

        <div class="row">
            <!-- Disponibilidad internacional -->
            <div class="col-md-4 mb-3">
                <?php
                Field::radio(
                    'international_availability',
                    '¿Disponibilidad para salir del país en misiones? ',
                    ['Sí' => 'Sí', 'No' => 'No'],
                    $user_meta['hv_international_availability'] ?? '',
                    true
                );
                ?>
            </div>

            <!-- Disponibilidad fines de semana -->
            <div class="col-md-4 mb-3">
                <?php
                Field::radio(
                    'weekend_availability',
                    '¿Disponible fines de semana? ',
                    ['Sí' => 'Sí', 'No' => 'No'],
                    $user_meta['hv_weekend_availability'] ?? '',
                    true
                );
                ?>
            </div>

            <!-- Puede viajar al interior -->
            <div class="col-md-4 mb-3">
                <?php
                Field::radio(
                    'travel_availability',
                    '¿Puede viajar al interior? ',
                    ['Sí' => 'Sí', 'No' => 'No'],
                    $user_meta['hv_travel_availability'] ?? '',
                    true
                );
                ?>
            </div>
        </div>
    </div>
</div>