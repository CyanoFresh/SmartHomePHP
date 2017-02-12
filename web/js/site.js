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

    $('.drawer').toggleClass('visible');

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

$(document).on('resize, ready', function () {
    // Add class if screen size equals
    var $window = $(window),
        $html = $('html');

    function resize() {
        $html.removeClass('xs sm md lg');

        if ($window.width() <= 768) {
            return $html.addClass('xs');
        }
        else if ($window.width() >= 768 && $window.width() <= 992) {
            return $html.addClass('sm');
        }
        else if ($window.width() >= 992 && $window.width() <= 1200) {
            return $html.addClass('md');
        }
        else if ($window.width() >= 1200) {
            return $html.addClass('lg');
        }
    }

    $window.resize(resize).trigger('resize');
});

if (screen.width > 1300) {
    document.querySelector(".drawer").className += " visible";
}
