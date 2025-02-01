<?php
namespace JuridicOS\Admin;

class Class_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            JURIDICOS_PLUGIN_URL . 'includes/admin/views/assets/css/juridic-os-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {

        wp_enqueue_script(
            $this->plugin_name,
            JURIDICOS_PLUGIN_URL . 'includes/admin/views/assets/js/juridic-os-admin.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script(
            $this->plugin_name,
            'juridicosAdmin',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('juridicos-admin-nonce')
            )
        );
    }
}