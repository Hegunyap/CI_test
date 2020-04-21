$(document).on('draw.dt', function(e, settings) {
    if ($('.js-relative-time').length > 0) {
        $('.js-relative-time').each(function(index, time) {
            const $time = $(time);
            const date = $time.data('datetime');

            if (date) {
                const relative = moment(date, 'D MMM YYYY').fromNow();
                $time.text(relative);
            }
        });
    }
});
