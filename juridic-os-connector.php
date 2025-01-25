<?php
/**
 * Plugin Name: Juridic-OS Connector for WordPress
 * Plugin URI: https://juridic-os.com/wordpress
 * Description: Integración oficial de Juridic-OS para WordPress. Conecta formularios de contacto con el sistema de gestión legal Juridic-OS.
 * Version: 1.0.0
 * Author: Inbux Web
 * Author URI: https://inbuxweb.com
 * Text Domain: juridic-os-connector
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}

// Definir constantes del plugin
define('JURIDICOS_VERSION', '1.0.0');
define('JURIDICOS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JURIDICOS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Cargar el autoloader
require_once JURIDICOS_PLUGIN_DIR . 'includes/class-juridicos-autoloader.php';

function test_translation_verbose() {
    $traduccion = __('Juridic-OS Settings', 'juridic-os-connector');;
    error_log('Traducción completa: ' . $traduccion);
    error_log('Longitud traducción: ' . strlen($traduccion));
}
add_action('init', 'test_translation_verbose');

// Iniciar el plugin
function run_juridicos() {
    $plugin = new JuridicOS\Core\Class_Plugin();
    $plugin->run();
}


run_juridicos();