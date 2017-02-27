var wsURL;

var WS;
var WSConnectionOpened;

var itemValues = {};

function initWebSocket(onOpenCallback, onMessageCallback, onCloseCallback) {
    WS = new WebSocket(wsURL);

    WS.onmessage = onMessageCallback;
    WS.onopen = onOpenCallback;
    WS.onclose = onCloseCallback;
}

initWebSocket(function () {
    WSConnectionOpened = true;

    $('#loader, .linear-loader').fadeOut(function () {
        $('.control-panel').fadeIn();
    });
}, onMessage, function () {
    if (WSConnectionOpened) {
        $('.control-panel').fadeOut(function () {
            $('#loader').fadeIn().addClass('error');
        });
    } else {
        $('#loader').addClass('error');
        $('.linear-loader').fadeOut();
    }

    WSConnectionOpened = false;
});

function onMessage(e) {
    try {
        var data = JSON.parse(e.data);

        console.log(data);

        switch (data.type) {
            case 'init':
                $.each(data.items, function (key, value) {
                    if (value.type == 30) {
                        updateRGB(value.id, value.value);
                    } else {
                        if (value.value != 'N/A') {
                            saveItemValue(id, value.value);
                        }

                        updateItemValue(value.id, value.type, value.value);
                    }
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

function send(data) {
    console.log(data);

    if (typeof data != "string") {
        data = JSON.stringify(data);
    }

    if (WS && WS.readyState == 1) {
        WS.send(data);
    }
}

function updateValue(data) {
    updateItemValue(data.item_id, data.item_type, data.value);
}

function updateRGB(itemId, data) {
    itemValues[itemId] = data;

    if (data.mode == 'static' || data.mode == 'fade') {
        $('.panel-item-rgb[data-item-id="' + itemId + '"]').attr('style', 'background: rgb(' + data.red + ',' + data.green + ',' + data.blue + ')');
    }

    $('#rgb-widget-wave-fade-time').val(data.fade_time);

    if (data.mode == 'fade' || data.mode == 'wave') {
        $('#rgb-widget-wave-color-time').val(data.color_time);
    }
}

function itemSwitchOn(itemId) {
    var $item = $('.panel-item-switch[data-item-id="' + itemId + '"]');
    $item.removeClass('off');
}

function itemSwitchOff(itemId) {
    var $item = $('.panel-item-switch[data-item-id="' + itemId + '"]');
    $item.addClass('off');
}

function itemSetValue(itemId, value) {
    $('.panel-item-variable[data-item-id="' + itemId + '"] > .item-variable-value').html(value);
}

function saveItemValue(itemId, value) {
    itemValues[itemId] = value;
}

function getSavedItemValue(itemId) {
    return itemValues[itemId];
}

function updateItemValue(id, type, value) {
    type = parseInt(type);

    switch (type) {
        case 10:    // Switch
            value = Boolean(value);

            if (value) {
                itemSwitchOn(id);
            } else {
                itemSwitchOff(id);
            }

            break;
        case 20:    // Variable
            itemSetValue(id, value);
            break;
        case 21:    // Variable Temperature
            itemSetValue(id, value + ' °C');
            break;
        case 22:    // Variable Humidity
            itemSetValue(id, value + '%');
            break;
        case 25:    // Variable boolean
            if (Boolean(value)) {
                if (value != 'N/A') {
                    value = 'да';
                }
            } else {
                value = 'нет';
            }

            itemSetValue(id, value);

            break;
        case 26:    // Variable boolean door
            if (value) {
                if (value != 'N/A') {
                    value = 'открыто';
                }
            } else {
                value = 'закрыто';
            }

            itemSetValue(id, value);

            break;
    }
}

$(document).ready(function () {
    $('.panel-item-switch').click(function (e) {
        e.preventDefault();

        var $this = $(this);

        var item_id = $this.data('item-id');

        send({
            "type": $this.hasClass('off') ? 'turnON' : 'turnOFF',
            "item_id": item_id
        });
    });

    $('.panel-item-variable').tooltip();

    // RGB Widget
    $('.panel-item-rgb')
        .popover({
            html: true,
            placement: 'bottom',
            trigger: 'click',
            container: 'body',
            content: function () {
                var source = $("#rgb-item-widget-popover-content").html();
                var template = Handlebars.compile(source);

                return template({
                    item_id: $(this).data('item-id')
                });
            }
        })
        // On popover init
        .on('inserted.bs.popover', function () {
            var item_id = $(this).data('item-id');

            // Init colorpicker
            var $colorPicker = $('body').find('.rgb-widget-colorpicker[data-item-id="' + item_id + '"]').spectrum({
                flat: true,
                showInput: false,
                showButtons: false,
                preferredFormat: 'rgb'
            });

            var savedItemValue = getSavedItemValue(item_id);

            // Set colorpicker value
            if (savedItemValue != null) {
                $colorPicker.spectrum('set', 'rgb(' + savedItemValue['red'] + ',' + savedItemValue['green'] + ',' + savedItemValue['blue'] + ')');
            }

            // Set mode and variables
            if (savedItemValue.mode == 'static') {
                $('.rgb-widget-mode-static').tab('show');
            } else if (savedItemValue.mode == 'wave') {
                $('#rgb-widget-wave-color-time').val(savedItemValue.color_time);
                $('#rgb-widget-fade-color-time').val(savedItemValue.color_time);

                $('.rgb-widget-mode-wave').tab('show');
            } else if (savedItemValue.mode == 'fade') {
                $('#rgb-widget-wave-color-time').val(savedItemValue.color_time);
                $('#rgb-widget-fade-color-time').val(savedItemValue.color_time);

                $('.rgb-widget-mode-fade').tab('show');
            }

            $('#rgb-widget-static-fade-time').val(savedItemValue.fade_time);
            $('#rgb-widget-wave-fade-time').val(savedItemValue.fade_time);
            $('#rgb-widget-fade-fade-time').val(savedItemValue.fade_time);

            // On color select
            $colorPicker.on("dragstop.spectrum", function (e, color) {
                var red = Math.round(color._r);
                var green = Math.round(color._g);
                var blue = Math.round(color._b);

                var $this = $(this);
                var itemId = $this.data('item-id');

                $('.panel-item-rgb[data-item-id="' + itemId + '"]')
                    .attr('style', 'background-color: rgb(' + red + ',' + green + ',' + blue + ')');

                var modeId = $this.parents('.tab-pane').attr('id');

                if (modeId == 'rgb-widget-static') {
                    send({
                        "type": "rgb",
                        "item_id": item_id,
                        "fade_time": parseInt($('#rgb-widget-static-fade-time').val()),
                        "mode": "static",
                        "red": red,
                        "green": green,
                        "blue": blue
                    });
                } else if (modeId == 'rgb-widget-fade') {
                    send({
                        "type": "rgb",
                        "item_id": item_id,
                        "fade_time": parseInt($('#rgb-widget-fade-fade-time').val()),
                        "color_time": parseInt($('#rgb-widget-fade-color-time').val()),
                        "mode": "fade",
                        "red": red,
                        "green": green,
                        "blue": blue
                    });
                }
            });

            $('.btn-save-times').click(function (e) {
                e.preventDefault();

                var mode = $(this).data('mode');

                if (mode == 'wave') {
                    send({
                        "type": "rgb",
                        "item_id": item_id,
                        "mode": "wave",
                        "fade_time": parseInt($('#rgb-widget-wave-fade-time').val()),
                        "color_time": parseInt($('#rgb-widget-wave-color-time').val())
                    });
                } else if (mode == 'fade') {
                    var color = $('.rgb-widget-colorpicker-fade').spectrum('get');
                    var red = Math.round(color._r);
                    var green = Math.round(color._g);
                    var blue = Math.round(color._b);

                    if (savedItemValue != null) {
                        send({
                            "type": "rgb",
                            "item_id": item_id,
                            "mode": "fade",
                            "fade_time": parseInt($('#rgb-widget-fade-fade-time').val()),
                            "color_time": parseInt($('#rgb-widget-fade-color-time').val()),
                            "red": red,
                            "green": green,
                            "blue": blue
                        });
                    } else {
                        send({
                            "type": "rgb",
                            "item_id": item_id,
                            "mode": "fade",
                            "fade_time": parseInt($('#rgb-widget-fade-fade-time').val()),
                            "color_time": parseInt($('#rgb-widget-fade-color-time').val())
                        });
                    }
                }
            });

            $('.rgb-widget-popover-content[data-item-id="' + item_id + '"]').on('click', '.rgb-widget-mode', function (e) {
                var $this = $(this);

                if ($this.attr('aria-expanded') == 'true') {
                    return;
                }

                if ($this.hasClass('rgb-widget-mode-static')) {
                    var color = $('.rgb-widget-colorpicker-static').spectrum('get');
                    var red = Math.round(color._r);
                    var green = Math.round(color._g);
                    var blue = Math.round(color._b);

                    send({
                        "type": "rgb",
                        "item_id": item_id,
                        "fade_time": parseInt($('#rgb-widget-static-fade-time').val()),
                        "mode": "static",
                        "red": red,
                        "green": green,
                        "blue": blue
                    });
                } else if ($this.hasClass('rgb-widget-mode-wave')) {
                    send({
                        "type": "rgb",
                        "item_id": item_id,
                        "mode": "wave",
                        "fade_time": parseInt($('#rgb-widget-wave-fade-time').val()),
                        "color_time": parseInt($('#rgb-widget-wave-color-time').val())
                    });
                } else if ($this.hasClass('rgb-widget-mode-fade')) {
                    var color = $('.rgb-widget-colorpicker-fade').spectrum('get');
                    var red = Math.round(color._r);
                    var green = Math.round(color._g);
                    var blue = Math.round(color._b);

                    send({
                        "type": "rgb",
                        "item_id": item_id,
                        "mode": "fade",
                        "fade_time": parseInt($('#rgb-widget-fade-fade-time').val()),
                        "color_time": parseInt($('#rgb-widget-fade-color-time').val())
                    });
                }
            });
        });
});
