var wsURL;

var WS;

function log(msg) {
    console.log(msg);
}

function initWebSocket() {
    WS = new WebSocket(wsURL);

    WS.onmessage = onMessage;
    WS.close(function () {
        showErrorMessage('Невозможно подключиться к серверу');
    });
}

function onMessage(e) {
    try {
        var data = JSON.parse(e.data);

        log(data);

        switch (data.type) {
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

function showErrorMessage(message) {
    log(message);
}

function updateValue(data) {

}

$(document).ready(function () {
    initWebSocket();
});
