function uid() {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }

    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
        s4() + '-' + s4() + s4() + s4();
}

$(document).ready(function () {
    $('.withripple, .btn, .navbar a').ripples();

    $('select.md-select').each(function () {
        var $select = $(this);
        var selectId = uid();
        var widget = '<div class="mad-select" data-select-id="' + selectId + '">' +
            '<div class="md-select-selected-value">' + $select.find('option:selected').text() + '</div>' +
            '<ul class="mad-select-drop">';

        $select.data('id', selectId);
        $select.css({display: 'none'});

        $select.find('option').each(function () {
            var $option = $(this);

            widget += '<li data-value="' + $option.attr('value') + '">' + $option.html() + '</li>';
        });

        widget += '</ul><i class="fa fa-chevron-down"></i></div>';

        var $widget = $(widget);

        $widget.insertAfter($select);

        var $ulDrop = $widget.find('ul');

        $widget.click(function (e) {
            e.stopPropagation();
            $ulDrop.toggleClass('show');
        });

        // PRESELECT
        $ulDrop.find("li[data-value='" + $select.val() + "']").addClass('selected');

        // MAKE SELECTED
        $ulDrop.on('click', 'li', function (e) {
            e.preventDefault();

            var $li = $(this);

            $select.val($li.data('value'));
            $widget.find('.md-select-selected-value').html($li.text());

            $li.addClass('selected')
                .siblings('li').removeClass('selected');

            $ulDrop.removeClass('show');

            return false;
        });

        // UPDATE LIST SCROLL POSITION
        $ulDrop.on('click', function () {
            var liTop = $ulDrop.find('li.selected').position().top;
            $ulDrop.scrollTop(liTop + $ulDrop[0].scrollTop);
        });

        $('body').click(function (e) {
            $ulDrop.removeClass('show');
        });
    });
});
