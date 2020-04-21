$(document).ready(function() {
    // select all
    $('.table-select-all').on('change', function(e) {
        var $table = $($(this).data('target'));

        var $childCheckboxes = $table.find('input[data-table="#table-modules"]').not('[disabled]');

        if ($(this).is(':checked')) {
            $childCheckboxes.prop('checked', true);
        } else {
            $childCheckboxes.prop('checked', false);
        }

        $table.trigger('select-all', [$childCheckboxes]);
    });

    // select2
    $('.js-select2').each(function(index, select2) {
        $(select2).select2({
            minimumResultsForSearch: 10,
        });
    });

    // switchery
    $('.js-switchery').each(function(index, elemSwitchery) {
        new Switchery(elemSwitchery);
    });

    $('.js-datetime-picker').each(function(index, elemInput) {
        const $elem = $(elemInput);

        $elem.prop('readOnly', true);

        $elem.datetimepicker({
            showClear: true,
            format: 'D MMM YYYY',
            ignoreReadonly: true,
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: "fa fa-arrow-left",
                next: "fa fa-arrow-right",
                clear: "fa fa-trash-o",
                close: "fa fa-times",
            }
        });
    });
});
