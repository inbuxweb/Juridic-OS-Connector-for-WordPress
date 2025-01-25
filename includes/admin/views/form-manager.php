<?php
if (!defined('WPINC')) {
    die;
}

// Verificar si Contact Form 7 está activo
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
                submit_button(__('Save API Settings', 'juridic-os-connector'));
                ?>
            </form>
            <div class="juridicos-last-submission">
                <?php
                $last_submission_date = get_option('juridicos_last_submission_' . $selected_form_id);
                if ($last_submission_date) {
                    printf(
                        __('Last form submission: %s', 'juridic-os-connector'),
                        esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_submission_date)))
                    );
                }
                ?>
            </div>
        </div>

        <div class="juridicos-admin-sidebar">
            <div class="juridicos-box">
                <h3><?php _e('Form Field Mapping', 'juridic-os-connector'); ?></h3>

                <!-- Selector de formulario CF7 -->
                <div class="form-group">
                    <label for="cf7-form-selector">
                        <?php _e('Select Contact Form 7', 'juridic-os-connector'); ?>
                    </label>
                    <select id="cf7-form-selector" class="regular-text">
                        <option value=""><?php _e('Select a form...', 'juridic-os-connector'); ?></option>
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
                        <h4><?php _e('Required Fields', 'juridic-os-connector'); ?></h4>

                        <!-- Campos requeridos de Juridic-OS -->
                        <?php
                        $required_fields = array(
                            'firstname' => __('First Name', 'juridic-os-connector'),
                            'lastname'  => __('Last Name', 'juridic-os-connector'),
                            'email'     => __('Email', 'juridic-os-connector'),
                            'phone'     => __('Phone', 'juridic-os-connector')
                        );

                        $form_id = $selected_form_id;
                        $saved_mapping = get_option('juridicos_form_mapping_' . $form_id, array());

                        ?>

                        <table class="widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Field', 'juridic-os-connector'); ?></th>
                                    <th><?php _e('Form Tag', 'juridic-os-connector'); ?></th>
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
                            <button type="submit" class="button button-primary" id="save-mapping">
                                <?php _e('Save Mapping', 'juridic-os-connector'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>