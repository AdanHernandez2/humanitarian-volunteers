<?php

/**
 * Sección: Referencias Personales
 */

use VolunteerForm\Fields as Field;
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Referencias Personales</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <?php Field::text(
                    'reference1_name',
                    'Nombre Referencia 1 ',
                    $user_meta['hv_reference1_name'] ?? '',
                    true
                ); ?>
            </div>
            <div class="col-md-6 mb-3">
                <?php Field::tel(
                    'reference1_phone',
                    'Teléfono Referencia 1 ',
                    $user_meta['hv_reference1_phone'] ?? '',
                    true
                ); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <?php Field::text(
                    'reference2_name',
                    'Nombre Referencia 2 ',
                    $user_meta['hv_reference2_name'] ?? '',
                    true
                ); ?>
            </div>
            <div class="col-md-6 mb-3">
                <?php Field::tel(
                    'reference2_phone',
                    'Teléfono Referencia 2 ',
                    $user_meta['hv_reference2_phone'] ?? '',
                    true
                ); ?>
            </div>
        </div>
    </div>
</div>