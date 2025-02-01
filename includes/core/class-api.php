<?php
namespace JuridicOS\Core;

class Class_Api {
    private $api_url;
    private $api_key;
    private const TRANSIENT_PREFIX = 'juridicos_lead_';
    private const EXPIRATION_TIME = 86400; // 24 horas en segundos

    public function __construct() {
        $options = get_option('juridicos_settings');
        $this->api_url = $options['api_url'] ?? '';
        $this->api_key = $this->decrypt_api_key($options['api_key'] ?? '');
    }

    private function is_duplicate_lead($data) {
        // Crear un hash único basado en los datos críticos del lead
        $critical_data = array(
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'name'  => $data['name'] ?? ''
        );
        
        $lead_hash = hash('sha256', json_encode($critical_data));
        $transient_key = self::TRANSIENT_PREFIX . $lead_hash;
        
        // Verificar si existe un transient con este hash
        if (get_transient($transient_key)) {
            return true;
        }

        // Si no es duplicado, crear un transient
        set_transient($transient_key, '1', self::EXPIRATION_TIME);
        return false;
    }

    public function createLead($data) {
        // Verificar si es un lead duplicado
        if ($this->is_duplicate_lead($data)) {
            return array(
                'success' => false,
                'message' => 'Duplicate lead detected',
                'code' => 'DUPLICATE_LEAD'
            );
        }

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
            
            if (is_wp_error($response)) {
                return array(
                    'success' => false,
                    'message' => $response->get_error_message(),
                    'code' => 'REQUEST_ERROR'
                );
            }
            
            $http_code = wp_remote_retrieve_response_code($response);
            if ($http_code < 200 || $http_code >= 300) {
                return array(
                    'success' => false,
                    'message' => 'HTTP Error: ' . $http_code,
                    'code' => 'HTTP_ERROR'
                );
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return array(
                    'success' => false,
                    'message' => 'Invalid JSON response',
                    'code' => 'INVALID_JSON'
                );
            }
            
            return array_merge(['success' => true], $data);
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage(),
                'code' => 'EXCEPTION'
            );
        }
    }

    private function decrypt_api_key($encrypted_key) {
        if (!function_exists('wp_decrypt')) {
            return base64_decode($encrypted_key);
        }
        return wp_decrypt($encrypted_key);
    }
}