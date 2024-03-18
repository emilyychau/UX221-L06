/*!
 impleCode Shopping Cart
 (c) 2020 Norbert Dreszer - http://implecode.com
 */
jQuery(document).ready(function () {
    /* globals product_object */
    jQuery('#customer_panel_tabs').tabs();
    var password = jQuery('#customer_panel_tabs-password');
    password.on('click', '.new-password-button button', function () {
        password.find('.spinner img').show();
        var first = password.find('#new_password_1').val();
        var second = password.find('#new_password_2').val();
        if (first !== '' && second !== '' && first !== second) {
            password.find('.spinner img').hide();
            password.find('.password-reset-result').html('<div class="al-box warning">The passwords do not match!</div>');
        } else if (first === '' || second === '') {
            password.find('.spinner img').hide();
            password.find('.password-reset-result').html('<div class="al-box warning">Please fill both fields.</div>');
        } else {
            var data = {
                'action': 'customer_panel_password_reset',
                'new_password': first,
                'repeat_new_password': second
            };
            jQuery.post(product_object.ajaxurl, data, function () {
                password.find('.spinner img').hide();
                password.find('.password-reset-result').html('<div class="al-box success">The password has been changed! Auto refresh in <span class="time">3</span> seconds.</div>');
                var time = 3;
                setInterval(function () {
                    time--;
                    jQuery('span.time').html(time);
                    if (time === 0) {
                        location.reload();
                    }
                }, 1000);
            });
        }
    });

    jQuery('#customer_orders_table').on('click', '.table-row .table-cell span.dashicons', function () {
        var row = jQuery(this).closest('.table-row');
        var order_id = row.data('order_id');
        if (order_id !== '') {
            var order_name_span = jQuery(this).next('.order-name');
            var old_order_name = order_name_span.text();
            order_name_span.html('<input class="order-name-edit" type="text" value=""><span class="dashicons dashicons-yes"></span><span class="dashicons dashicons-no"></span>');
        }
        jQuery('#customer_orders_table').unbind('click');
        jQuery('#customer_orders_table').on('click', '.order-name .dashicons-no', function () {
            order_name_span.html(old_order_name);
        });
        jQuery('#customer_orders_table').on('click', '.order-name .dashicons-yes', function () {
            var order_name_edit = jQuery(order_name_span).find('.order-name-edit');
            var new_order_name = order_name_edit.val();
            var data = {
                'action': 'change_order_name',
                'order_id': order_id,
                'new_name': new_order_name
            };
            order_name_edit.attr('disabled', true);
            jQuery.post(product_object.ajaxurl, data, function (response) {
                if (response === 'done') {
                    order_name_span.html(new_order_name);
                } else {
                    order_name_edit.attr('disabled', false);
                }
            });
        });
    });
});