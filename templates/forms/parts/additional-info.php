<?php

/**
 * Sección: Información Adicional
 */

use VolunteerForm\Fields as Field;
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Información Adicional (Opcional)</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <?php Field::text('hv_nationality', 'Nacionalidad', $user_meta['hv_nationality'] ?? ''); ?>
            </div>
            <div class="col-md-6 mb-3">
                <?php
                Field::select('hv_gender', 'Género', [
                    'Masculino' => 'Masculino',
                    'Femenino' => 'Femenino',
                    'Otro' => 'Otro',
                    'Prefiero no decirlo' => 'Prefiero no decirlo'
                ], $user_meta['hv_gender'] ?? '');
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <?php
                Field::select('hv_blood_type', 'Tipo de sangre', [
                    'A+' => 'A+',
                    'A-' => 'A-',
                    'B+' => 'B+',
                    'B-' => 'B-',
                    'AB+' => 'AB+',
                    'AB-' => 'AB-',
                    'O+' => 'O+',
                    'O-' => 'O-'
                ], $user_meta['hv_blood_type'] ?? '');
                ?>
            </div>
            <div class="col-md-6 mb-3">
                <?php Field::textarea('hv_physical_limitations', '¿Tienes algún tipo de limitación física o de salud que debamos considerar?', $user_meta['hv_physical_limitations'] ?? '', false, 2); ?>
            </div>
            <div class="col-md-6 mb-3">
                <?php
                Field::select('hv_shirt_size', 'Talla de camiseta', [
                    'XS' => 'XS',
                    'S' => 'S',
                    'M' => 'M',
                    'L' => 'L',
                    'XL' => 'XL',
                    'XXL' => 'XXL'
                ], $user_meta['hv_shirt_size'] ?? '');
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <?php Field::text('hv_profession', 'Profesión', $user_meta['hv_profession'] ?? ''); ?>
            </div>
            <div class="col-md-6 mb-3">
                <?php Field::text('hv_medical_condition', 'Condición médica', $user_meta['hv_medical_condition'] ?? ''); ?>
            </div>
        </div>
    </div>
</div>