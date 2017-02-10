function showErrorMessage(message) {
    return $.snackbar({
        content: message,
        timeout: 5000
    });
}

function showSuccessMessage(message) {
    return $.snackbar({
        content: message,
        timeout: 5000
    });
}

$('.ajax-call').click(function (e) {
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
