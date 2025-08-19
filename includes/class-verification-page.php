<?php
class Verification_Page
{

    public function __construct()
    {
        add_shortcode('verificacion_voluntario', [$this, 'render_verification_page']);
    }

    public function render_verification_page()
    {
        ob_start();

        if (isset($_GET['hv_unique_code'])) {
            $this->handle_verification();
        } else {
            $this->render_search_form();
        }

        return ob_get_clean();
    }

    private function render_search_form()
    {
?>
        <form method="GET" class="verification-form">
            <input type="text" name="hv_unique_code" placeholder="Ingrese su código único" required>
            <button type="submit" class="btn btn-primary">Verificar</button>
        </form>
<?php
    }

    private function handle_verification()
    {
        $code = sanitize_text_field($_GET['hv_unique_code']);
        $user = $this->get_user_by_code($code);

        if (!$user) {
            echo '<div class="error">Código inválido o expirado</div>';
            return;
        }

        $this->display_certificate($user->ID);
    }

    private function get_user_by_code($code)
    {
        $users = get_users([
            'meta_key' => 'hv_unique_code',
            'meta_value' => $code,
            'number' => 1
        ]);

        return $users ? $users[0] : false;
    }

    private function display_certificate($user_id)
    {
        $user_data = [
            'first_name' => get_user_meta($user_id, 'first_name', true),
            'last_name' => get_user_meta($user_id, 'last_name', true)
        ];

        $filename = "certificado-{$user_data['first_name']}-{$user_id}.pdf";

        // Obtener rutas absolutas
        $upload_dir = wp_upload_dir();
        $pdf_path = $upload_dir['basedir'] . '/humanitarios-pdfs/' . $filename;
        $file_url = $upload_dir['baseurl'] . '/humanitarios-pdfs/' . $filename;

        // Verificar si el archivo existe
        if (!file_exists($pdf_path)) {
            echo '<div class="error">Certificado no encontrado: ' . esc_html($filename) . '</div>';
            return;
        }

        echo '<embed src="' . esc_url($file_url) . '" type="application/pdf" width="100%" height="800px">';
    }
}
