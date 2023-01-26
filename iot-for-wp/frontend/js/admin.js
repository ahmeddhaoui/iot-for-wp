jQuery(document).ready(function ($) {

    //Handel Admin Clicks on Settingspage
    $('#iot-settings-actions-btn button').on('click', function () {
        let target = $(this).val();
        if (confirm('Are you sure you want to execute this?')) {
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

    //Handel Admin Test Connection
    $('#broker-test-connection').on('click', function () {

        let protocol = $('#broker_protocol').find(':selected').val();

        let missing_data = false;
        $('input:required').each(function () {
            if ($(this).val() === '') {
                missing_data = true;
            }
        });

        if (missing_data) {
            alert('error please fill all required fields!');
            return;
        }

        let broker_input_data = $('.' + protocol);
        let broker_data = {};

        broker_input_data.each(function (index, element) {
            let key = $(element).data('key');
            broker_data[key] = $(element).val();
        });

        $.ajax({
            type: 'POST',
            url: iot4wp_admin_script.ajaxurl,
            data: {
                action: 'test_connections',
                protocol: protocol,
                broker_data: broker_data,
                nonce: iot4wp_admin_script.test_connections,
            },
            success: function (data, textStatus, XMLHttpRequest) {

                alert(data);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
    });

    //Topics Backend Forms
    function hide_all_data_format_options() {
        $('.data-input-format').each(function (index, element) {
            $(this).hide();
        });
    }

    function show_selected_option() {
        let val = $('#data-input-format-select').find(":selected").val();
        $('.format-config-' + val).show();
    }

    $('#data-input-format-select').on('change', function () {
        hide_all_data_format_options();
        show_selected_option();
    });

    show_selected_option();

    $('#topic_subscribe').on('change', function () {
        $('#topic_subscribe_data').toggle();
    });

    if (!$('#topic_subscribe').is(':checked')) {
        $('#topic_subscribe_data').toggle();
    }

    $('#topic_publish').on('change', function () {
        $('#topic_publish_data').toggle();
    });

    if ($('#topic_publish').is(':checked')) {
        $('#topic_publish_data').toggle();
    }

    // Subscribe Action Table
    function hide_all_subscribe_data_tables() {
        $('#subscribe-data-col table, #publish-data-col table').each(function () {
            $(this).hide()
        });
    }

    function show_select_subscribe_data_tables() {
        let target = $('#subscribe_action_select').find(":selected").val();
        $('#' + target).show();
    }

    function show_select_publish_data_tables() {
        let target = $('#publish_action_select').find(":selected").val();
        $('#' + target).show();
    }

    $('#subscribe_action_select').on('change', function () {
        hide_all_subscribe_data_tables();
        show_select_subscribe_data_tables();
    });

    $('#publish_action_select').on('change', function () {
        hide_all_subscribe_data_tables();
        show_select_publish_data_tables();
    });

    hide_all_subscribe_data_tables();
    show_select_subscribe_data_tables();
    show_select_publish_data_tables();
});