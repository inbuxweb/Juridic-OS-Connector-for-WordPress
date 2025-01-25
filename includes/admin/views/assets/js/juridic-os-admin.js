(function($) {
    'use strict';

    $(document).ready(function() {
        $('#mapping-form').on('submit', function(event) {
            event.preventDefault();
            
            const formId = $('#cf7-form-selector').val();
            const mapping = {};
            const $submitButton = $('#save-mapping');

            if (!formId) {
                alert('Please select a form');
                return;
            }

            // Deshabilitar botón y mostrar spinner de WordPress
            $submitButton
                .prop('disabled', true)
                .addClass('spinner-button')
                .html('<span class="spinner is-active" style="float:left; margin-top: 3px; margin-right: 10px;"></span>Saving Changes');

            $('.juridicos-field-input').each(function() {
                const fieldName = $(this).attr('name').replace('mapping[', '').replace(']', '');
                const fieldValue = $(this).val().trim();

                if (!fieldValue) {
                    alert('Please complete all required fields');
                    return false;
                }

                mapping[fieldName] = fieldValue;
            });

            // Enviar mapeo via AJAX
            $.ajax({
                url: juridicosAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'juridicos_save_field_mapping',
                    nonce: juridicosAdmin.nonce,
                    form_id: formId,
                    mapping: mapping
                },
                success: function(response) {
                    if (response.success) {
                        // Guardar el último formulario seleccionado
                        $.ajax({
                            url: juridicosAdmin.ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'juridicos_save_last_form',
                                nonce: juridicosAdmin.nonce,
                                form_id: formId
                            },
                            complete: function() {
                                // Restaurar botón
                                $submitButton
                                    .prop('disabled', false)
                                    .removeClass('spinner-button')
                                    .html('Save Mapping');
                                
                                alert('Mapping saved successfully');
                            }
                        });
                    } else {
                        // Restaurar botón en caso de error
                        $submitButton
                            .prop('disabled', false)
                            .removeClass('spinner-button')
                            .html('Save Mapping');
                        
                        alert('Error saving mapping');
                    }
                },
                error: function() {
                    // Restaurar botón en caso de error
                    $submitButton
                        .prop('disabled', false)
                        .removeClass('spinner-button')
                        .html('Save Mapping');
                    
                    alert('Error saving mapping');
                }
            });
        });
    });
})(jQuery);