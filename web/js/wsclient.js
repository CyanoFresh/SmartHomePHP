var wsURL;

var WS;
var WSopened;

function log(msg) {
    console.log(msg);
}

function showErrorMessage(message, title) {
    log(message);

    var stack_bottomright = {"dir1": "up", "dir2": "left", "firstpos1": 25, "firstpos2": 25};

    new PNotify({
        title: 'Ошибка',
        text: message,
        type: 'error',
        addclass: "stack-bottomright",
        stack: stack_bottomright
    });
}

function initWebSocket(callback) {
    WS = new WebSocket(wsURL);

    WS.onmessage = onMessage;
    WS.onopen = callback;
    WS.onclose = function () {
        if (WSopened) {
            $('.loader-backdrop').fadeIn();
            showErrorMessage('Потеряно соединение');
        } else {
            $('.loader').fadeOut(function () {
                $(this).html('Не удается подключиться к серверу').fadeIn();
            });
        }
    };
}

function onMessage(e) {
    try {
        var data = JSON.parse(e.data);

        log(data);

        switch (data.type) {
            case 'init':

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
    switch (data.item_type) {
        case 10:    // Switch
            $('input[type="checkbox"][data-item-id="' + data.item_id + '"]').change().prop('checked', data.value);
            break;
        case 20:    // Variable
            $('.item-variable[data-item-id="' + data.item_id + '"]').find('.item-value').html(data.value);
            break;
        case 25:    // Variable boolean
            var $div = $('.item-variable[data-item-id="' + data.item_id + '"]');
            var $item_value = $div.find('.item-value');

            if (data.value == 1) {
                $item_value.html('ДА');
            } else {
                $item_value.html('НЕТ');
            }

            break;
        case 26:    // Variable boolean
            var $div = $('.item-variable[data-item-id="' + data.item_id + '"]');
            var $item_value = $div.find('.item-value');

            if (data.value == 1) {
                $item_value.html('ОТКРЫТО');
            } else {
                $item_value.html('ЗАКРЫТО');
            }

            break;
    }
}

$(document).ready(function () {
    PNotify.prototype.options.styling = "fontawesome";

    initWebSocket(function () {
        WSopened = true;

        $('.loader-backdrop').fadeOut();

        $('input[type="checkbox"]').click(function (e) {
            e.preventDefault();

            send({
                "type": $(this).prop('checked') ? 'turnON' : 'turnOFF',
                "item_id": $(this).data('item-id')
            });
        });

        // Delegate click on block to checkbox
        /*$('.item-switch .info-box').click(function (e) {
         var $target = $(e.target);

         if ($target.is('input[type="checkbox"]')) {
         return false;
         }

         var $checkbox = $(this).find('input[type="checkbox"]');
         $checkbox.click();
         });*/
    });
});
