<?php
class Volunteers_Admin_Page
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_verify_volunteer', [$this, 'ajax_verify_volunteer']);
        add_action('admin_init', [$this, 'export_volunteers_to_excel']);
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Voluntarios',
            'Voluntarios',
            'manage_options',
            'volunteers',
            [$this, 'render_admin_page'],
            'dashicons-groups',
            25
        );
    }

    public function render_admin_page()
    {
        // Construir argumentos de consulta
        $args = [
            'meta_key' => '_user_type',
            'meta_value' => 'employers',
            'number' => -1
        ];

        // Aplicar filtro de búsqueda
        if (!empty($_GET['s'])) {
            $args['search'] = '*' . sanitize_text_field($_GET['s']) . '*';
        }

        // Aplicar filtro de verificación
        if (!empty($_GET['verification_status'])) {
            $status = $_GET['verification_status'];

            if ($status === 'verified') {
                $args['meta_query'] = [
                    'relation' => 'OR',
                    [
                        'key' => '_is_verified',
                        'value' => 'yes'
                    ],
                    [
                        'key' => 'identity_verified',
                        'value' => '1'
                    ]
                ];
            } else {
                $args['meta_query'] = [
                    'relation' => 'AND',
                    [
                        'relation' => 'OR',
                        ['key' => '_is_verified', 'value' => 'yes', 'compare' => '!='],
                        ['key' => '_is_verified', 'compare' => 'NOT EXISTS']
                    ],
                    [
                        'relation' => 'OR',
                        ['key' => 'identity_verified', 'value' => '1', 'compare' => '!='],
                        ['key' => 'identity_verified', 'compare' => 'NOT EXISTS']
                    ]
                ];
            }
        }

        // Obtener usuarios filtrados
        $users = get_users($args);

?>
        <div class="wrap hv-admin-volunteers">
            <h1 class="wp-heading-inline">Voluntarios</h1>

            <a href="<?php echo esc_url(add_query_arg(['export' => 'excel'])); ?>"
                class="page-title-action">
                <i class="fas fa-file-excel"></i> Exportar a Excel
            </a>

            <div class="filters mb-4">
                <form method="get">
                    <input type="hidden" name="page" value="volunteers">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" name="s" class="form-control" placeholder="Buscar..."
                                    value="<?php echo esc_attr($_GET['s'] ?? ''); ?>">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="verification_status" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="verified" <?php selected($_GET['verification_status'] ?? '', 'verified'); ?>>
                                    Verificados
                                </option>
                                <option value="unverified" <?php selected($_GET['verification_status'] ?? '', 'unverified'); ?>>
                                    No verificados
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-secondary" type="submit">Filtrar</button>
                            <a href="?page=volunteers" class="btn btn-link">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Área de Interés</th>
                            <th>Verificación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) :
                            $is_verified = get_user_meta($user->ID, '_is_verified', true) === 'yes' ||
                                get_user_meta($user->ID, 'identity_verified', true) === '1';
                        ?>
                            <tr data-user-id="<?php echo $user->ID; ?>">
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=volunteer_profile&user_id=' . $user->ID); ?>">
                                        <?php echo $user->display_name; ?>
                                    </a>
                                </td>
                                <td><?php echo $user->user_email; ?></td>
                                <td><?php echo get_user_meta($user->ID, 'hv_phone', true); ?></td>
                                <td><?php echo get_user_meta($user->ID, 'hv_interest_areas', true); ?></td>
                                <td class="verification-status">
                                    <?php if ($is_verified) : ?>
                                        <span class="badge bg-success">✅ Verificado</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">❌ No verificado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=volunteer_profile&user_id=' . $user->ID); ?>"
                                        class="btn btn-sm btn-primary">
                                        Ver Perfil
                                    </a>

                                    <?php if (!$is_verified) : ?>
                                        <button class="btn btn-sm btn-success verify-btn"
                                            data-user-id="<?php echo $user->ID; ?>">
                                            Verificar
                                        </button>
                                    <?php else : ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            Verificado
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (empty($users)) : ?>
                <div class="alert alert-info">
                    No se encontraron voluntarios registrados.
                </div>
            <?php endif; ?>
        </div>


<?php
    }

    public function export_volunteers_to_excel()
    {
        if (!isset($_GET['export']) || $_GET['export'] !== 'excel' || !current_user_can('manage_options')) {
            return;
        }

        // 1. Intenta con PHPSpreadsheet si está disponible
        if ($this->is_phpspreadsheet_available()) {
            try {
                $this->export_with_phpspreadsheet();
                return;
            } catch (Exception $e) {
                error_log('Error al exportar con PHPSpreadsheet: ' . $e->getMessage());
                // Continúa con el método simple si falla
            }
        }

        // 2. Método simple como fallback
        $this->export_with_simple_excel();
    }

    private function is_phpspreadsheet_available()
    {
        // Primero intenta con el autoloader de Composer estándar
        if (file_exists(ABSPATH . 'vendor/autoload.php')) {
            require_once ABSPATH . 'vendor/autoload.php';
        }

        // Si no, intenta con la ruta del plugin
        if (
            !class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet') &&
            file_exists(plugin_dir_path(__FILE__) . 'vendor/autoload.php')
        ) {
            require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
        }

        return class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet');
    }

    private function export_with_phpspreadsheet()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configuración del documento
        $spreadsheet->getProperties()
            ->setCreator('Sistema de Voluntarios')
            ->setTitle('Voluntarios Registrados')
            ->setDescription('Listado completo de voluntarios');

        if (ob_get_level()) {
            ob_end_clean();
        }

        // Encabezados
        $headers = [
            'ID',
            'Nombre Completo',
            'Email',
            'Teléfono',
            'Cédula',
            'Fecha Nacimiento',
            'Provincia',
            'Dirección',
            'Estado Civil',
            'Nivel Académico',
            'Áreas de Interés',
            'Disponibilidad (Días)',
            'Horas por Día',
            'Fines de Semana',
            'Viaje Interior',
            'Misiones Internacionales',
            'Experiencia',
            'Descripción Experiencia',
            'Nacionalidad',
            'Género',
            'Tipo Sangre',
            'Talla Camiseta',
            'Profesión',
            'Condición Médica',
            'Limitaciones Físicas',
            'Referencia 1 Nombre',
            'Referencia 1 Teléfono',
            'Referencia 2 Nombre',
            'Referencia 2 Teléfono',
            'Estado',
            'Fecha Registro'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Estilo para encabezados
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A1:AF1')->applyFromArray($headerStyle);

        // Obtener datos
        $users = get_users([
            'meta_key' => '_user_type',
            'meta_value' => 'employers',
            'number' => -1
        ]);

        $row = 2;
        foreach ($users as $user) {
            $is_verified = get_user_meta($user->ID, '_is_verified', true) === 'yes' ||
                get_user_meta($user->ID, 'identity_verified', true) === '1';

            $interest_areas = maybe_unserialize(get_user_meta($user->ID, 'hv_interest_areas', true));
            $availability_days = maybe_unserialize(get_user_meta($user->ID, 'hv_availability_days', true));

            $data = [
                $user->ID,
                $user->display_name,
                $user->user_email,
                get_user_meta($user->ID, 'hv_phone', true),
                get_user_meta($user->ID, 'hv_id_number', true),
                get_user_meta($user->ID, 'hv_birth_date', true),
                get_user_meta($user->ID, 'hv_province', true),
                get_user_meta($user->ID, 'hv_address', true),
                get_user_meta($user->ID, 'hv_marital_status', true),
                get_user_meta($user->ID, 'hv_education_level', true),
                is_array($interest_areas) ? implode(', ', $interest_areas) : $interest_areas,
                is_array($availability_days) ? implode(', ', $availability_days) : $availability_days,
                get_user_meta($user->ID, 'hv_availability_hours', true),
                get_user_meta($user->ID, 'hv_weekend_availability', true),
                get_user_meta($user->ID, 'hv_travel_availability', true),
                get_user_meta($user->ID, 'hv_international_availability', true),
                get_user_meta($user->ID, 'hv_has_experience', true),
                get_user_meta($user->ID, 'hv_experience_desc', true),
                get_user_meta($user->ID, 'hv_nationality', true),
                get_user_meta($user->ID, 'hv_gender', true),
                get_user_meta($user->ID, 'hv_blood_type', true),
                get_user_meta($user->ID, 'hv_shirt_size', true),
                get_user_meta($user->ID, 'hv_profession', true),
                get_user_meta($user->ID, 'hv_medical_condition', true),
                get_user_meta($user->ID, 'hv_physical_limitations', true),
                get_user_meta($user->ID, 'hv_reference1_name', true),
                get_user_meta($user->ID, 'hv_reference1_phone', true),
                get_user_meta($user->ID, 'hv_reference2_name', true),
                get_user_meta($user->ID, 'hv_reference2_phone', true),
                $is_verified ? 'Verificado' : 'No verificado',
                $user->user_registered
            ];

            $sheet->fromArray($data, null, "A{$row}");
            $row++;
        }

        // Definir las columnas de la A a la AF
        $columns = [];
        for ($col = 'B'; $col !== 'AG'; $col++) {
            $columns[] = $col;
        }
        // Asignar ancho fijo de 35 a cada columna
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setWidth(25);
        }

        // Formato de fechas
        $sheet->getStyle('F2:F' . $row)
            ->getNumberFormat()
            ->setFormatCode('yyyy-mm-dd');

        $sheet->getStyle('AF2:AF' . $row)
            ->getNumberFormat()
            ->setFormatCode('yyyy-mm-dd hh:mm:ss');

        // Descargar archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="voluntarios_' . date('Y-m-d_His') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function export_with_simple_excel()
    {
        $users = get_users([
            'meta_key' => '_user_type',
            'meta_value' => 'employers',
            'number' => -1
        ]);

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment;filename="voluntarios_' . date('Y-m-d_His') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF"; // BOM para UTF-8

        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Cédula</th>";
        echo "<th>Fecha Nac.</th><th>Provincia</th><th>Dirección</th><th>Estado Civil</th>";
        echo "<th>Nivel Acad.</th><th>Áreas Interés</th><th>Días Disp.</th><th>Horas/Día</th>";
        echo "<th>Fines Semana</th><th>Viaja Interior</th><th>Misiones Int.</th>";
        echo "<th>Experiencia</th><th>Desc. Experiencia</th><th>Nacionalidad</th>";
        echo "<th>Género</th><th>Tipo Sangre</th><th>Talla</th><th>Profesión</th>";
        echo "<th>Cond. Médica</th><th>Limitaciones</th><th>Ref. 1 Nombre</th>";
        echo "<th>Ref. 1 Teléfono</th><th>Ref. 2 Nombre</th><th>Ref. 2 Teléfono</th>";
        echo "<th>Otras Refs.</th><th>Estado</th><th>Fecha Reg.</th>";
        echo "</tr>";

        foreach ($users as $user) {
            $is_verified = get_user_meta($user->ID, '_is_verified', true) === 'yes' ||
                get_user_meta($user->ID, 'identity_verified', true) === '1';

            $interest_areas = maybe_unserialize(get_user_meta($user->ID, 'hv_interest_areas', true));
            $availability_days = maybe_unserialize(get_user_meta($user->ID, 'hv_availability_days', true));

            echo "<tr>";
            echo "<td>" . $user->ID . "</td>";
            echo "<td>" . htmlspecialchars($user->display_name, ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars($user->user_email, ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_phone', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_id_number', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_birth_date', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_province', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_address', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_marital_status', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_education_level', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(is_array($interest_areas) ? implode(', ', $interest_areas) : $interest_areas, ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(is_array($availability_days) ? implode(', ', $availability_days) : $availability_days, ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_availability_hours', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_weekend_availability', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_travel_availability', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_international_availability', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_has_experience', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_experience_desc', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_nationality', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_gender', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_blood_type', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_shirt_size', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_profession', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_medical_condition', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_physical_limitations', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_reference1_name', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_reference1_phone', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_reference2_name', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars(get_user_meta($user->ID, 'hv_reference2_phone', true), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . ($is_verified ? 'Verificado' : 'No verificado') . "</td>";
            echo "<td>" . $user->user_registered . "</td>";
            echo "</tr>";
        }

        echo "</table>";
        exit;
    }

    public function ajax_verify_volunteer()
    {
        check_ajax_referer('hv_admin_nonce', 'nonce');

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

        if (!$user_id) {
            wp_send_json_error(['message' => 'ID de usuario inválido']);
        }

        // Actualizar metadatos
        update_user_meta($user_id, '_is_verified', 'yes');
        update_user_meta($user_id, 'identity_verified', '1');

        // Actualizar campos con Carbon Fields
        if (function_exists('carbon_set_user_meta')) {
            carbon_set_user_meta($user_id, 'hv_status', 'verified');

            // Generar código único si no existe
            if (!carbon_get_user_meta($user_id, 'hv_unique_code')) {
                $code = 'VOL-' . str_pad($user_id, 8, '0', STR_PAD_LEFT) . '-' . bin2hex(random_bytes(2));
                carbon_set_user_meta($user_id, 'hv_unique_code', $code);
            }
        }

        // Disparar email de confirmación
        do_action('volunteer_verified', $user_id);

        wp_send_json_success([
            'message' => 'Usuario verificado con éxito',
            'new_status' => '<span class="badge bg-success">✅ Verificado</span>',
            'new_button' => '<button class="btn btn-sm btn-secondary" disabled>Verificado</button>'
        ]);
    }
}
