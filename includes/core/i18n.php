<?php
namespace JuridicOS\Core;

/**
 * Define the internationalization functionality
 */
class I18n {
    public function __construct() {
        add_action('plugins_loaded', [$this, 'load_plugin_textdomain']);
    }

    public function load_plugin_textdomain() {
        $locale = apply_filters('plugin_locale', get_locale(), 'juridic-os-connector');
        $mofile = WP_LANG_DIR . '/plugins/juridic-os-connector-' . $locale . '.mo';
        
        // Intento de carga desde WP_LANG_DIR
        load_textdomain('juridic-os-connector', $mofile);
        
        // Respaldo: carga desde directorio del plugin
        load_plugin_textdomain(
            'juridic-os-connector', 
            false, 
            dirname(plugin_basename(JURIDICOS_PLUGIN_DIR)) . '/languages/'
        );
        
        error_log('Archivo MO intentado: ' . $mofile);
        error_log('Locale actual: ' . $locale);
    }
}