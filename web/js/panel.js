var wsURL;
var itemValueChartUrl;

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

        $('.masonry-container').masonry({
            columnWidth: '.masonry-item',
            itemSelector: '.masonry-item',
            percentPosition: true
        });
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
                            saveItemValue(value.id, value.value);
                        }

                        updateItemValue(value.id, value.type, value.value, value.value_type);
                    }
                });

                afterConnected();

                break;
            case 'value':
                updateValue(data);
                break;
            case 'rgb':
                updateRGB(data.item_id, data);
                break;
            case 'soil_moisture':
                itemValues[data.item_id] = data.value;

                $('.panel-item-plant[data-item-id="' + data.item_id + '"]').find('.panel-item-plant-soil-moisture').html(data.value + ' %');

                break;
            case 'watered':
                showSuccessMessage('Растение полито');
                break;
            case 'error':
                showErrorMessage(data.message);
                break;
            case 'debug_message':
                var d = new Date();
                var strDate = d.getFullYear() + "." + (d.getMonth() + 1) + "." + d.getDate() + " " + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();

                $('#debug-messages').prepend('<b>[' + strDate + ']</b> ' + data.message + "<br>");

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
    updateItemValue(data.item_id, data.item_type, data.value, data.value_type);
}

function updateRGB(itemId, data) {
    itemValues[itemId] = data;

    if (data.mode == 'static' || data.mode == 'fade') {
        var $panelItem = $('.panel-item-rgb[data-item-id="' + itemId + '"]');

        $panelItem.css('background-color', 'rgb(' + data.red + ',' + data.green + ',' + data.blue + ')');
        $panelItem.css('color', textColorDepOnBackground(data.red, data.green, data.blue));
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

function itemVariableSetValue(itemId, value) {
    $('.panel-item-variable[data-item-id="' + itemId + '"] > .item-variable-value').html(value);
}

function saveItemValue(itemId, value) {
    itemValues[itemId] = value;
}

function getSavedItemValue(itemId) {
    return itemValues[itemId];
}

function updateItemValue(id, type, value, value_type) {
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
            if (!value_type || value_type == null) {
                return itemVariableSetValue(id, value);
            }

            switch (value_type) {
                case 10:    // Boolean
                    if (Boolean(value)) {
                        if (value != 'N/A') {
                            value = 'да';
                        }
                    } else {
                        value = 'нет';
                    }

                    break;
                case 20:    // Variable boolean door
                    if (value) {
                        if (value != 'N/A') {
                            value = 'открыто';
                        }
                    } else {
                        value = 'закрыто';
                    }

                    break;
                case 30:    // Celsius
                    value += ' °C';
                    break;
                case 40:    // Percent
                    value += '%';
                    break;
            }

            itemVariableSetValue(id, value);

            break;
        case 40:    // Plant moisture
            $('.panel-item-plant[data-item-id="' + id + '"] .panel-item-plant-soil-moisture').html(value);
            break;
    }
}

function textColorDepOnBackground(red, green, blue) {
    var brightness = Math.round((red * 299 + green * 587 + blue * 114) / 1000);
    var textColor;

    if (brightness > 125) {
        textColor = 'black';
    } else {
        textColor = 'white';
    }

    return textColor;
}

$(document).ready(function () {
    $('.panel-item-switch').click(function (e) {
        e.preventDefault();

        var $this = $(this);

        var item_id = $this.data('item-id');

        send({
            "type": $this.hasClass('off') ? 'turn_on' : 'turn_off',
            "item_id": item_id
        });
    });

    $('.panel-item-variable').tooltip();

    // Value widget
    $('body').on('click', '.panel-item-variable', function () {
        var $modal = $('#item-chart-modal');
        var $this = $(this);

        var type = parseInt($this.data('item-type'));
        var title = $this.data('original-title');
        var itemId = $this.data('item-id');

        var data;

        if (type !== 20 && type !== 21 && type !== 22 && type !== 23) {
            return;
        }

        $.ajax({
            url: itemValueChartUrl + '&item_id=' + itemId,
            dataType: 'json'
        }).success(function (result) {
            $modal.find('.item-chart-name').html(title);

            var $canvas = $modal.find('#item-chart');

            Date.prototype.formatMMDDYYYY = function () {
                return (this.getHours() < 10 ? '0' : '') + this.getHours() +
                    ':' + (this.getMinutes() < 10 ? '0' : '') + this.getMinutes();
            };

            // Split timestamp and data into separate arrays
            var labels = [],
                data = [];

            $.each(result.data, function (key, value) {
                labels.push(new Date(key * 1000).formatMMDDYYYY());
                data.push(parseFloat(value));
            });

            // Create the chart.js data structure using 'labels' and 'data'
            var tempData = {
                labels: labels,
                datasets: [{
                    label: title,
                    data: data,
                    lineTension: 0,
                    backgroundColor: 'transparent',
                    borderColor: '#009688'
                }]
            };

            // Get the context of the canvas element we want to select
            var ctx = $canvas.get(0).getContext("2d");

            // Instantiate a new chart
            var myLineChart = new Chart(ctx, {
                type: 'line',
                data: tempData
            });

            $modal.modal('show').on('hidden.bs.modal', function (e) {
                myLineChart.destroy();
                $modal.find('canvas, iframe').remove();
                $modal.find('.modal-body').append('<canvas id="item-chart"></canvas>');
            })
        }).fail(function () {
            showErrorMessage('Не удалось получить данные');
        });
    }).on('click', '#send-board', function (e) {
        e.preventDefault();

        send({
            type: 'debug_send_to_board',
            board_id: parseInt($('#send-board-board_id').val()),
            message: $('#send-board-message').val()
        });
    }).on('click', '.btn-plant-do-watering', function (e) {
        e.preventDefault();

        var item_id = $(this).parents('.panel-item-plant').data('item-id');

        send({
            type: 'do_watering',
            item_id: item_id
        });
    });

    // RGB Widget
    $('.panel-item-rgb')
        .popover({
            html: true,
            placement: 'bottom',
            trigger: 'click',
            // container: 'body',
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

            // Open mode tabs and set variables
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

                var $panelItem = $('.panel-item-rgb[data-item-id="' + itemId + '"]');

                // Update text color
                $panelItem.css('background-color', 'rgb(' + red + ',' + green + ',' + blue + ')');
                $panelItem.css('color', textColorDepOnBackground(red, green, blue));

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
                        "color_time": parseInt($('#rgb-widget-fade-color-time').val()),
                        "red": red,
                        "green": green,
                        "blue": blue
                    });
                }
            });
        });
});
