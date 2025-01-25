<?php
namespace JuridicOS\Admin;

class Class_Form {
    private $api_client;
    
    public function __construct() {
        $this->api_client = new \JuridicOS\Core\Class_Api();
        
        // Registrar acciones AJAX
        add_action('wp_ajax_juridicos_get_form_fields', array($this, 'ajax_get_form_fields'));
        add_action('wp_ajax_juridicos_save_field_mapping', array($this, 'ajax_save_field_mapping'));
    }
    
    /**
     * Maneja la solicitud AJAX para obtener campos del formulario
     */
    public function ajax_get_form_fields() {
        // Verificar nonce
        if (!check_ajax_referer('juridicos-admin-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token'));
        }

        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        if (!$form_id) {
            wp_send_json_error(array('message' => 'Invalid form ID'));
        }

        $fields = $this->get_form_fields($form_id);
        
        wp_send_json_success(array(
            'fields' => $this->format_fields_for_response($fields)
        ));
    }

    /**
     * Maneja la solicitud AJAX para guardar el mapeo de campos
     */
    public function ajax_save_field_mapping() {
        // Verificar nonce
        if (!check_ajax_referer('juridicos-admin-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token'));
        }

        $form_id = isset($_POST['form_id']) ? intval(wp_unslash($_POST['form_id'])) : 0;
        $mapping = isset($_POST['mapping']) ? $this->sanitize_mapping(wp_unslash($_POST['mapping'])) : array();

        if (!$form_id || empty($mapping)) {
            wp_send_json_error(array('message' => 'Invalid data provided'));
        }

        // Guardar el formulario en las opciones de WordPress
        $option_name = 'juridicos_form';
        update_option($option_name, $form_id);
        // Guardar el mapeo en las opciones de WordPress
        $option_name = 'juridicos_form_mapping_' . $form_id;
        update_option($option_name, $mapping);

        wp_send_json_success(array(
            'message' => 'Mapping saved successfully',
            'mapping' => $mapping
        ));
    }

    /**
     * Obtiene los campos de un formulario CF7
     */
    public function get_form_fields($form_id) {
        $form = \WPCF7_ContactForm::get_instance($form_id);
        if (!$form) {
            return array();
        }

        $fields = array();
        $tags = $form->scan_form_tags();
        
        foreach ($tags as $tag) {
            if (!empty($tag['name']) && $this->is_valid_field_type($tag['type'])) {
                $fields[$tag['name']] = array(
                    'name' => $tag['name'],
                    'type' => $tag['type'],
                    'label' => $this->get_field_label($tag)
                );
            }
        }

        return $fields;
    }

    /**
     * Formatea los campos para la respuesta JSON
     */
    private function format_fields_for_response($fields) {
        $formatted = array();
        foreach ($fields as $field) {
            $formatted[] = array(
                'name' => $field['name'],
                'label' => $field['label'] ?: $field['name'],
                'type' => $field['type']
            );
        }
        return $formatted;
    }

    /**
     * Sanitiza el array de mapeo
     */
    private function sanitize_mapping($mapping) {
        $sanitized = array();
        if (is_array($mapping)) {
            foreach ($mapping as $key => $value) {
                $sanitized[sanitize_key($key)] = sanitize_text_field($value);
            }
        }
        return $sanitized;
    }

    /**
     * Verifica si el tipo de campo es válido para mapeo
     */
    private function is_valid_field_type($type) {
        $valid_types = array('text', 'email', 'tel', 'textarea', 'select', 'radio', 'checkbox');
        return in_array($type, $valid_types);
    }

    /**
     * Intenta obtener la etiqueta del campo desde el formulario
     */
    private function get_field_label($tag) {
        // Primero intentamos obtener una etiqueta del título del campo si existe
        if (!empty($tag['options'])) {
            foreach ($tag['options'] as $option) {
                if (strpos($option, 'placeholder') === 0) {
                    return trim(str_replace('placeholder:', '', $option));
                }
                if (strpos($option, 'label') === 0) {
                    return trim(str_replace('label:', '', $option));
                }
            }
        }
        
        // Si no hay etiqueta, usamos el nombre del campo
        return $tag['name'];
    }

    /**
     * Maneja el envío del formulario (código existente)
     */
    public function handle_form_submission($contact_form, &$abort, $submission) {
        if ($abort) {
            return;
        }

        // Obtener la configuración de mapeo para este formulario
        $form_id = $contact_form->id();
       
        $field_mapping = get_option('juridicos_form_mapping_' . $form_id, array());
        $field_status = get_option('juridicos_status_default', 'uncontacted');
        
        if (empty($field_mapping)) {
            return; // No hay mapeo configurado para este formulario
        }

        // Preparar los datos según el mapeo
        $data = array();
        foreach ($field_mapping as $juridicos_field => $form_field) {
            $data[$juridicos_field] = $submission->get_posted_data($form_field);
        }
     
        // Validar datos requeridos
        $required_fields = array('firstname', 'lastname', 'email', 'phone');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return; // No procesar si faltan campos requeridos
            }
        }
        $data['status'] = $field_status;
        $response = $this->api_client->createLead($data);

        // Enviar a la API
        try {
            $response = $this->api_client->createLead($data);
            // Guardar el ID de respuesta y timestamp para referencia
            update_option('juridicos_last_submission_' . $form_id, current_time('mysql'));
        } catch (\Exception $e) {
            // Log error
        }
    }
}