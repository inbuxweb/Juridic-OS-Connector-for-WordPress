<?php
namespace JuridicOS;

class Autoloader {
    public static function register() {
        spl_autoload_register(array(new self, 'autoload'));
    }

    public function autoload($class) {
        // Solo cargar clases de nuestro namespace
        if (strpos($class, 'JuridicOS\\') !== 0) {
            return;
        }
        
        $class = str_replace('JuridicOS\\', '', $class);
        $class = str_replace('\\', DIRECTORY_SEPARATOR, strtolower($class));
        $class = str_replace('_', '-', $class);
        $file = JURIDICOS_PLUGIN_DIR . 'includes/' . $class . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

Autoloader::register();