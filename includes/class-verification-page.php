<?php
class Verification_Page {
    public function __construct() {
        add_action('init', [$this, 'add_endpoint']);
        add_filter('template_include', [$this, 'load_template']);
    }

    public function add_endpoint() {
        add_rewrite_rule(
            '^verificar-voluntario/?$',
            'index.php?volunteer_verification=1',
            'top'
        );
        add_rewrite_tag('%volunteer_verification%', '1');
    }

    public function load_template($template) {
        if(get_query_var('volunteer_verification')) {
            return HV_PLUGIN_PATH . 'templates/verification-template.php';
        }
        return $template;
    }
}
new Verification_Page();