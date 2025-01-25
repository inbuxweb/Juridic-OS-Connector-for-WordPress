<?php
namespace JuridicOS\Core;

class Class_Api {
    private $api_url;
    private $api_key;

    public function __construct() {
        $options = get_option('juridicos_settings');
        $this->api_url = $options['api_url'] ?? '';
        $this->api_key = $this->decrypt_api_key($options['api_key'] ?? '');
    }

    public function createLead($data) {
        return $this->makeRequest('POST', '/api/v1/third-party/leads', $data);
    }

    private function makeRequest($method, $endpoint, $data = null) {
        try {
            $url = rtrim($this->api_url, '/') . '/' . ltrim($endpoint, '/');

            $args = array(
                'method'    => $method,
                'headers'   => array(
                    'X-API-Token'   => $this->api_key,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ),
                'timeout'   => 30,
            );
            if ($data !== null) {
                $args['body'] = wp_json_encode($data);
            }
            
            $response = wp_remote_request($url, $args);
            
            // Verificar errores de conexión
            if (is_wp_error($response)) {
                return null;
            }
            
            // Verificar código de respuesta HTTP
            $http_code = wp_remote_retrieve_response_code($response);
            if ($http_code < 200 || $http_code >= 300) {
                return null;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }
            
            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function decrypt_api_key($encrypted_key) {
        if (!function_exists('wp_decrypt')) {
            return base64_decode($encrypted_key); // Fallback básico
        }
        return wp_decrypt($encrypted_key);
    }
}