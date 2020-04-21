function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode;

    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }

    return true;
}

$(document).ready(function() {
    if ($('.js-number-counter').length > 0) {
        $('.js-number-counter').each(function(index, counter) {
            const $counter = $(counter);

            $counter.on('animate-number', function(e) {
                const previousValue = parseFloat($counter.data('previous-value')) || 0;
                const value         = parseFloat($counter.data('value')) || 0;
                const shorten       = $counter.data('shorten') || false;
                const prefix        = $counter.data('prefix') || '';

                $counter.prop('amount', previousValue)
                        .animate({
                            amount: value
                        }, {
                            duration: 1000,
                            step: function(now) {
                                $(this).text(convertToCurrency(now.toFixed(0), shorten, prefix));
                            }
                        });
            });
        });

        $('.js-number-counter').trigger('before-animate-number');
        $('.js-number-counter').trigger('animate-number');
        $('.js-number-counter').trigger('after-animate-number');
    }
});
