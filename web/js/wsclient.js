var wsURL;

var WS;
var WSConnectionOpened;

function log(msg) {
    console.log(msg);
}

function initWebSocket(callback) {
    WS = new WebSocket(wsURL);

    WS.onmessage = onMessage;
    WS.onopen = callback;
    WS.onclose = function () {
        if (WSConnectionOpened) {
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
    WSConnectionOpened = true;

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
        case 40:    // Variable Light Level
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
        case 30:    // RGB
            if (typeof value === 'string') {
                $('.item-rgb[data-item-id="' + id + '"]')
                    .find('.rgb-mode[data-mode="' + value + '"]')
                    .addClass('active');
            } else {
                var $colorPicker = $('#colorpicker-' + id);

                $colorPicker.spectrum('set', 'rgb(' + value[0] + ', ' + value[1] + ', ' + value[2] + ')');

                $('.item-rgb .rgb-mode').removeClass('active');
            }

            break;
    }
}

$(document).ready(function () {
    $('.rgb-colorpicker').spectrum({
        showInput: true,
        showButtons: false,
        preferredFormat: 'rgb',
        change: function (color) {
            var item_id = $(this).data('item-id');
            var red = Math.round(color._r);
            var green = Math.round(color._g);
            var blue = Math.round(color._b);

            var fade = ($('.fade-checkbox[data-item-id="' + item_id + '"]:checked').length > 0);

            send({
                'type': 'rgb',
                'item_id': item_id,
                'fade': fade,
                'red': red,
                'green': green,
                'blue': blue
            });
        }
    });

    $('.fade-checkbox').each(function () {
        var localStorageValue = window.localStorage.getItem('fade-checkbox-' + $(this).data('item-id'));

        console.log(localStorageValue);

        this.checked = localStorageValue != null && localStorageValue != 'false';
    });

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

        $('.rgb-mode').click(function (e) {
            e.preventDefault();

            var mode = $(this).data('mode');
            var start = true;
            var item_id = $(this).parents('.item-rgb').data('item-id');

            if ($(this).hasClass('active')) {
                start = false
            }

            send({
                "type": "rgbMode",
                "item_id": item_id,
                "mode": mode,
                "start": start
            });
        });

        $('.fade-checkbox').change(function (e) {
            window.localStorage.setItem('fade-checkbox-' + $(this).data('item-id'), this.checked);
        });
    });
});
