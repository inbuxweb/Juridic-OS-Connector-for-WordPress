<?php
namespace JuridicOS\Core;

class Class_Plugin {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->version = JURIDICOS_VERSION;
        $this->plugin_name = 'juridic-os-connector';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
    }

    private function load_dependencies() {
        $this->loader = new Class_Loader();
    }

    private function define_admin_hooks() {
        $plugin_admin = new \JuridicOS\Admin\Class_Admin($this->get_plugin_name(), $this->get_version());
        $plugin_settings = new \JuridicOS\Admin\Class_Settings();
        $form_manager = new \JuridicOS\Admin\Class_Form();

        // Hooks para el área de administración
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_settings, 'add_settings_page');
        $this->loader->add_action('admin_init', $plugin_settings, 'register_settings');
        
        // Hooks para el gestor de formularios
        $this->loader->add_action('wpcf7_before_send_mail', $form_manager, 'handle_form_submission', 10, 3);
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
}