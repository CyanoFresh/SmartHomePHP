function showErrorMessage(message) {
    return noty({
        layout: 'bottomRight',
        text: message,
        timeout: 5000,
        type: 'error',
        theme: 'relax'
    });
}

function showSuccessMessage(message) {
    return noty({
        layout: 'bottomRight',
        text: message,
        timeout: 5000,
        type: 'success',
        theme: 'relax'
    });
}

$('.schedule-triggers').click(function (e) {
    e.preventDefault();

    $.post($(this).attr('href'), function (data) {
        if (data.success) {
            showSuccessMessage('Успешно обновлено');
        } else {
            showErrorMessage('Не удалось обновить');
        }
    }).fail(function () {
        showErrorMessage('Не удалось обновить');
    });
});
