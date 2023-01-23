jQuery(document).ready(function ($) {

    //Handel Admin Clicks on Settingspage
    $('#iot-settings-actions-btn button').on('click', function () {
        let target = $(this).val();
        if(confirm('Are you sure you want to execute this?')){
            $.ajax({
                type: 'POST',
                url: iot4wp_admin_script.ajaxurl,
                data: {
                    action: 'handel_settings_action',
                    target_function: target,
                    nonce: iot4wp_admin_script.handel_settings_action_nonce,
                },
                success: function (data, textStatus, XMLHttpRequest) {
                    alert(data);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(errorThrown);
                }
            });
        }
    });
});