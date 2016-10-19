var wsURL;

var WS;
var WSopened;

function log(msg) {
    console.log(msg);
}

function showErrorMessage(message) {
    return noty({
        layout: 'bottomRight',
        text: message,
        type: 'error',
        theme: 'relax'
    });
}

function showSuccessMessage(message) {
    return noty({
        layout: 'bottomRight',
        text: message,
        type: 'success',
        theme: 'relax'
    });
}

function initWebSocket(callback) {
    WS = new WebSocket(wsURL);

    WS.onmessage = onMessage;
    WS.onopen = callback;
    WS.onclose = function () {
        if (WSopened) {
            log(1);
            $('.loader-text').html('Отключен от сервера').fadeIn();

            $('.control-panel').fadeOut(function () {
                $('#loader').addClass('error').fadeIn();
            });
        } else {
            $('#loader').addClass('error');
            $('.loader-text').html('Не удалось подключиться к серверу').fadeIn();
        }
    };
}

function onMessage(e) {
    try {
        var data = JSON.parse(e.data);

        log(data);

        switch (data.type) {
            case 'init':
                $.each(data.items, function (key, value) {
                    updateItemValue(value.id, value.type, value.value)
                });

                afterConnected();

                break;
            case 'value':
                updateValue(data);
                break;
            case 'error':
                showErrorMessage(data.message);
                break;
        }
    } catch (e) {
        showErrorMessage('Ошибка обработки ответа от сервера');
        console.log("Error: " + e);
    }
}

function afterConnected() {
    WSopened = true;

    $('#loader').fadeOut(function () {
        $('.control-panel').fadeIn();
    });
}

function send(msg) {
    if (typeof msg != "string") {
        msg = JSON.stringify(msg);
    }

    log(msg);

    if (WS && WS.readyState == 1) {
        WS.send(msg);
    }
}

function updateValue(data) {
    updateItemValue(data.item_id, data.item_type, data.value);
}

function updateItemValue(id, type, value) {
    type = parseInt(type);

    switch (type) {
        case 10:    // Switch
            $('.item-switch-checkbox[data-item-id="' + id + '"]').prop('checked', value);
            break;
        case 20:    // Variable
        case 21:    // Variable Temperature
            $('.item-variable[data-item-id="' + id + '"]').find('.item-value').html(value + '°C');
            break;
        case 22:    // Variable Humidity
            $('.item-variable[data-item-id="' + id + '"]').find('.item-value').html(value + "%");
            break;
        case 25:    // Variable boolean
            var $item_value = $('.item-variable[data-item-id="' + id + '"]').find('.item-value');

            if (value) {
                $item_value.html('ДА');
            } else {
                $item_value.html('НЕТ');
            }

            break;
        case 26:    // Variable boolean door
            var $item_value = $('.item-variable[data-item-id="' + id + '"]').find('.item-value');

            if (value) {
                $item_value.html('ОТКРЫТО');
            } else {
                $item_value.html('ЗАКРЫТО');
            }

            break;
    }
}

$(document).ready(function () {
    initWebSocket(function () {
        $('input[type="checkbox"].item-switch-checkbox').click(function (e) {
            e.preventDefault();

            var item_id = $(this).data('item-id');
            var action = $(this).prop('checked') ? 'turnON' : 'turnOFF';

            send({
                "type": action,
                "item_id": item_id
            });
        });

        // Delegate click on block to checkbox
        $('.item-switch .info-box').click(function (e) {
            e.preventDefault();

            if ($(e.target).is('.item-switch-checkbox')) {
                return false;
            }

            $(this).find('.item-switch-checkbox').click();
        });
    });
});
