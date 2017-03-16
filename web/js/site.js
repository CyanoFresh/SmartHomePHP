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

$('.navbar-toggle-drawer').click(function (e) {
    e.preventDefault();

    $('body').toggleClass('drawer-open');

    return false;
});

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

$('.show-on-click').click(function (e) {
    e.preventDefault();

    $(this).text($(this).data('text')).addClass('open');

    return false;
});
