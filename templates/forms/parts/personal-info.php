<?php

/**
 * Sección: Información Personal
 */

use VolunteerForm\Fields as Field;
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Información Personal</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?php Field::text('first_name', 'Nombre', $user_data['first_name'], true); ?>
            </div>
            <div class="col-md-6">
                <?php Field::text('last_name', 'Apellido', $user_data['last_name'], true); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php Field::text('hv_id_number', 'Cédula', $user_meta['hv_id_number'] ?? ''); ?>
            </div>
            <div class="col-md-6">
                <?php Field::date('hv_birth_date', 'Fecha de nacimiento', $user_meta['hv_birth_date'] ?? '', true); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?php Field::select('hv_education_level', 'Nivel académico', [
                    'Primaria completada' => 'Primaria completada',
                    'Primaria no completada' => 'Primaria no completada',
                    'Secundaria completada' => 'Secundaria completada',
                    'Secundaria no completada' => 'Secundaria no completada',
                    'Licenciatura' => 'Licenciatura',
                    'Maestría' => 'Maestría',
                    'Doctorado' => 'Doctorado'
                ], $user_meta['hv_education_level'] ?? '', true); ?>
            </div>
            <div class="col-md-4">
                <?php Field::select('hv_marital_status', 'Estado civil', [
                    'Soltero/a' => 'Soltero/a',
                    'Casado/a' => 'Casado/a',
                    'Divorciado/a' => 'Divorciado/a',
                    'Viudo/a' => 'Viudo/a',
                    'Unión libre' => 'Unión libre'
                ], $user_meta['hv_marital_status'] ?? ''); ?>
            </div>
            <div class="col-md-4">
                <?php Field::text('hv_address', 'Dirección', $user_meta['hv_address'] ?? '', true); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php Field::select('hv_province', 'Provincia de residencia', [
                    'Distrito Nacional'        => 'Distrito Nacional',
                    'Azua'                     => 'Azua',
                    'Bahoruco'                 => 'Bahoruco',
                    'Barahona'                 => 'Barahona',
                    'Dajabón'                  => 'Dajabón',
                    'Duarte'                   => 'Duarte',
                    'Elías Piña'               => 'Elías Piña',
                    'El Seibo'                 => 'El Seibo',
                    'Espaillat'                => 'Espaillat',
                    'Hato Mayor'               => 'Hato Mayor',
                    'Hermanas Mirabal'         => 'Hermanas Mirabal',
                    'Independencia'           => 'Independencia',
                    'La Altagracia'            => 'La Altagracia',
                    'La Romana'                => 'La Romana',
                    'La Vega'                  => 'La Vega',
                    'María Trinidad Sánchez'  => 'María Trinidad Sánchez',
                    'Monseñor Nouel'           => 'Monseñor Nouel',
                    'Monte Cristi'             => 'Monte Cristi',
                    'Monte Plata'              => 'Monte Plata',
                    'Pedernales'               => 'Pedernales',
                    'Peravia'                  => 'Peravia',
                    'Puerto Plata'             => 'Puerto Plata',
                    'Samaná'                   => 'Samaná',
                    'San Cristóbal'            => 'San Cristóbal',
                    'San José de Ocoa'         => 'San José de Ocoa',
                    'San Juan'                 => 'San Juan',
                    'San Pedro de Macorís'     => 'San Pedro de Macorís',
                    'Sánchez Ramírez'          => 'Sánchez Ramírez',
                    'Santiago'                 => 'Santiago',
                    'Santiago Rodríguez'       => 'Santiago Rodríguez',
                    'Santo Domingo'            => 'Santo Domingo',
                    'Valverde'                 => 'Valverde'
                ], $user_meta['hv_province'] ?? ''); ?>
            </div>
            <div class="col-md-6">
                <?php Field::tel('hv_phone', 'Teléfono / WhatsApp', $user_meta['hv_phone'] ?? '', true); ?>
            </div>
        </div>

        <?php Field::email('email', 'Correo electrónico', $user_data['email'], true); ?>
    </div>
</div>