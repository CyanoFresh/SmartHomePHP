$('.schedule-triggers').click(function (e) {
    e.preventDefault();

    $.post($(this).attr('href'));
});
