<?php
namespace JuridicOS\Admin;

class Class_Settings {
    private $option_name = 'juridicos_settings';
    
    public function add_settings_page() {
        add_menu_page(
            __('Juridic-OS Settings', 'juridic-os-connector'),
            __('Juridic-OS', 'juridic-os-connector'),
            'manage_options',
            'juridicos-settings',
            array($this, 'render_settings_page'),
            plugins_url('admin/views/assets/images/icon.png', dirname(__FILE__))
        );
    }

    public function register_settings() {
        register_setting($this->option_name, $this->option_name, array($this, 'validate_settings'));

        add_settings_section(
            'juridicos_api_settings',
            __('API Configuration', 'juridic-os-connector'),
            array($this, 'render_api_section'),
            'juridicos-settings'
        );

        add_settings_field(
            'api_key',
            __('API Key', 'juridic-os-connector'),
            array($this, 'render_api_key_field'),
            'juridicos-settings',
            'juridicos_api_settings'
        );

        add_settings_field(
            'api_url',
            __('API URL', 'juridic-os-connector'),
            array($this, 'render_api_url_field'),
            'juridicos-settings',
            'juridicos_api_settings'
        );

        add_settings_field(
            'status_default',
            __('Initial status', 'juridic-os-connector'),
            array($this, 'render_status_default_field'),
            'juridicos-settings',
            'juridicos_api_settings'
        );
    }

    public function render_api_key_field() {
        $options = get_option($this->option_name);
        $value = isset($options['api_key']) ? $this->decrypt_api_key($options['api_key']) : '';
        ?>
        <input type="password" 
               id="juridicos_api_key" 
               name="<?php echo $this->option_name; ?>[api_key]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text">
        <p class="description">
            <?php _e('Enter your Juridic-OS API Key', 'juridic-os-connector'); ?>
        </p>
        <?php
    }

    public function render_api_url_field() {
        $options = get_option($this->option_name);
        $value = isset($options['api_url']) ? $options['api_url'] : '';
        ?>
        <input type="url" 
               id="juridicos_api_url" 
               name="<?php echo $this->option_name; ?>[api_url]" 
               value="<?php echo esc_url($value); ?>" 
               class="regular-text">
        <p class="description">
            <?php _e('Enter your Juridic-OS API URL', 'juridic-os-connector'); ?>
        </p>
        <?php
    }

    public function render_status_default_field() {
        $options = get_option($this->option_name);
        $value = isset($options['status_default']) ? $options['status_default'] : '';

        $status_options = array(
            'converted'  => __('Converted', 'juridic-os-connector'),
            'lost'       => __('Lost', 'juridic-os-connector'),
            'qualified'  => __('Qualified', 'juridic-os-connector'),
            'contacted'  => __('Contacted', 'juridic-os-connector'),
            'uncontacted' => __('Uncontacted', 'juridic-os-connector')
        );
        ?>
        <select id="juridicos_status_default" 
                name="<?php echo $this->option_name; ?>[status_default]" 
                class="regular-text">
            <?php foreach ($status_options as $status => $label) : ?>
                <option value="<?php echo esc_attr($status); ?>" <?php selected($value, $status); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php _e('Initial status for new leads', 'juridic-os-connector'); ?>
        </p>
        <?php
    }    

    public function validate_settings($input) {
        $new_input = array();
        
        if(isset($input['api_key'])) {
            $new_input['api_key'] = $this->encrypt_api_key($input['api_key']);
        }
        
        if(isset($input['api_url'])) {
            $new_input['api_url'] = esc_url_raw($input['api_url']);
        }

        if (isset($input['status_default'])) {
            $new_input['status_default'] = sanitize_text_field($input['status_default']);
        }
        
        return $new_input;
    }

    private function encrypt_api_key($api_key) {
        // Usar la función de WordPress para encriptar
        if (!function_exists('wp_encrypt')) {
            return base64_encode($api_key); // Fallback básico
        }
        return wp_encrypt($api_key);
    }

    private function decrypt_api_key($encrypted_api_key) {
        // Comprobar si wp_encrypt existe
        if (!function_exists('wp_decrypt')) {
            return base64_decode($encrypted_api_key); // Fallback básico
        }
        return wp_decrypt($encrypted_api_key);
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        require_once JURIDICOS_PLUGIN_DIR . 'includes/admin/views/form-manager.php';
    }

    public function render_api_section() {
        ?>
        <p class="description">
            <?php _e('To connect to Juridic-OS, you need to generate an API Key from your administration panel.', 'juridic-os-connector'); ?>
        </p>
        <?php
    }
}