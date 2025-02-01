<?php
/**
 * Plugin Name: Juridic-OS Connector
 * Plugin URI: https://addasoft.co/plataforma-de-gestion-legal/
 * Description: Integración oficial de Juridic-OS para WordPress. Conecta formularios de contacto con el sistema de gestión legal Juridic-OS.
 * Version: 1.0.3
 * Author: Inbux Web
 * Author URI: https://inbuxweb.com
 * Text Domain: juridic-os-connector
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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

// Iniciar el plugin
function run_juridicos() {
    $plugin = new JuridicOS\Core\Class_Plugin();
    $plugin->run();
}


run_juridicos();