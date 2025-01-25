<?php
if (!defined('WPINC')) {
    die;
}

// Verificar si Contact Form 7 está activo
// Translators: This message appears when Contact Form 7 plugin is not active and explains why the feature cannot be used
if (! class_exists('WPCF7_ContactForm')) {
    echo '<p>' . esc_html__('Contact Form 7 is not active. Please activate the plugin to use this feature.', 'juridic-os-connector') . '</p>';
    return;
}

$selected_form_id = get_option('juridicos_form', '');

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="juridicos-admin-container">
        <div class="juridicos-admin-main">
            <!-- Configuración de API -->
            <form method="post" action="options.php">
                <?php
                settings_fields('juridicos_settings');
                do_settings_sections('juridicos-settings');
                // Translators: Button text for saving API configuration settings
                submit_button(esc_html__('Save API Settings', 'juridic-os-connector'));
                ?>
            </form>
            <div class="juridicos-last-submission">
                <?php
                $last_submission_date = get_option('juridicos_last_submission_' . $selected_form_id);
                if ($last_submission_date) {
                    // Translators: Displays the date and time of the last form submission with a formatted date
                    printf(
                        esc_html__('Last form submission: %s', 'juridic-os-connector'),
                        esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_submission_date)))
                    );
                }
                ?>
            </div>
        </div>

        <div class="juridicos-admin-sidebar">
            <div class="juridicos-box">
                <h3><?php esc_html_e('Form Field Mapping', 'juridic-os-connector'); ?></h3>

                <!-- Selector de formulario CF7 -->
                <div class="form-group">
                    <label for="cf7-form-selector">
                        <?php esc_html_e('Select Contact Form 7', 'juridic-os-connector'); ?>
                    </label>
                    <select id="cf7-form-selector" class="regular-text">
                        <!-- Translators: Default option in form selection dropdown -->
                        <option value=""><?php esc_html_e('Select a form...', 'juridic-os-connector'); ?></option>
                        <?php
                        $forms = WPCF7_ContactForm::find();
                        foreach ($forms as $form) {
                            printf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr($form->id()),
                                selected($form->id(), $selected_form_id, false),
                                esc_html($form->title())
                            );
                        }
                        ?>
                    </select>
                </div>

                <!-- Mapeo de campos -->
                <div id="juridicos-field-mapping">
                    <form id="mapping-form">
                        <!-- Translators: Heading for section where required fields are mapped -->
                        <h4><?php esc_html_e('Required Fields', 'juridic-os-connector'); ?></h4>

                        <!-- Translators: Labels for mapping required form fields to Juridic-OS system -->
                        <?php
                        $required_fields = array(
                            'firstname' => esc_html__('First Name', 'juridic-os-connector'),
                            'lastname'  => esc_html__('Last Name', 'juridic-os-connector'),
                            'email'     => esc_html__('Email', 'juridic-os-connector'),
                            'phone'     => esc_html__('Phone', 'juridic-os-connector')
                        );

                        $form_id = $selected_form_id;
                        $saved_mapping = get_option('juridicos_form_mapping_' . $form_id, array());

                        ?>

                        <table class="widefat fixed striped">
                            <thead>
                                <tr>
                                    <!-- Translators: Table header for form field column -->
                                    <th><?php esc_html_e('Field', 'juridic-os-connector'); ?></th>
                                    <!-- Translators: Table header for form tag/input column -->
                                    <th><?php esc_html_e('Form Tag', 'juridic-os-connector'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($required_fields as $field_key => $field_label) : ?>
                                    <tr>
                                        <td>
                                            <label for="<?php echo esc_attr($field_key); ?>">
                                                <?php echo esc_html($field_label); ?>
                                                <span class="required">*</span>
                                            </label>
                                        </td>
                                        <td>
                                            <!-- Translators: Placeholder text for input where field ID should be entered -->
                                            <input type="text"
                                                id="<?php echo esc_attr($field_key); ?>"
                                                name="mapping[<?php echo esc_attr($field_key); ?>]"
                                                class="juridicos-field-input"
                                                value="<?php echo esc_attr($saved_mapping[$field_key] ?? ''); ?>"
                                                placeholder="<?php esc_attr_e('Enter field ID', 'juridic-os-connector'); ?>"
                                                required>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="submit-wrapper" style="margin-top: 15px;">
                            <!-- Translators: Button text for saving field mapping configuration -->
                            <button type="submit" class="button button-primary" id="save-mapping">
                                <?php esc_html_e('Save Mapping', 'juridic-os-connector'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>