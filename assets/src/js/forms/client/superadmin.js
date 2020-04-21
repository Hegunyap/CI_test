$(document).ready(function() {
    /*
    const $datetimeStart = $('.datetimepicker-duedate').datetimepicker({
        showClear: true,
        useCurrent: false,
        format: "D MMM YYYY",
        ignoreReadonly: true,
    });
    */
    const $country = $(".select2-country").select2({
        placeholder: "Select a Country",
        minimumResultsForSearch: 5,
    }).on('change', function() {
        $(".select2-tax").val(null).trigger("change");
    });

    const $reseller = $(".select2-reseller").select2({
        placeholder: "Select a Reseller",
        minimumResultsForSearch: 5,
        allowClear: true,
    });

    const $industry = $(".select2-industry").select2({
        placeholder: "Select an Industry",
        minimumResultsForSearch: 5,
        allowClear: true,
    });

    function getSelectedCountry() {
        var country_id = $('.select2-country').val();

        if (country_id > 0) {
            return country_id;
        } else {
            alert('Please select a billing country.');
        }
    }

    if ($(".select2-tax").length > 0) {
        const taxUrl = $(".select2-tax").data('url');
        const taxCsrf = $(".select2-tax").data('csrf');

        const $tax = $(".select2-tax").select2({
            placeholder: "Select a Tax",
            minimumResultsForSearch: 5,
            allowClear: true,
            ajax: {
                url: taxUrl,
                dataType: "json",
                cache: false,
                delay: 1000,
                type: 'POST',
                data: function(params) {
                    return {
                        [taxCsrf.name]: taxCsrf.value,
                        id: params.term, // search term
                        country_id: getSelectedCountry(),
                    };
                },
            },
            processResult: function(data, page) {
                return {
                    results: [{
                        id: 1,
                        text: 'GST',
                    }, ],
                };
            },
        });
    }




    // modules
    function getModulePrice(checkboxElem) {
        var row = $(checkboxElem).closest('tr').find('.module-price');

        return parseFloat(row.val()) || 0;
    }

    function calculateModuleTotal() {
        const additional = 0;
        const checkboxes = $('#table-modules').find('tbody input[type="checkbox"]:checked');
        var total = 0;

        $.each(checkboxes, function(index, checkbox) {
            var additional = getModulePrice(checkbox);
            total += additional;
        });

        $('#modules-total').text(convertToCurrency(total, false));
    }

    $('#table-modules').on('select-all', function(e, $checkboxes) {
        calculateModuleTotal();
    });

    $('#table-modules input[type="checkbox"], #table-modules .module-price').on('change', function(e) {
        calculateModuleTotal();
    });

    calculateModuleTotal();




    // additional charges

    function formatChargeAmountInput(elem) {
        const dataType = $(elem).find(':selected').data('type');

        const $addons = $(elem).closest('.row')
            .find('input[type="number"]')
            .closest('.input-group')
            .find('.input-group-addon');

        switch (dataType) {
            case 'percent':
                $addons.first().hide();
                $addons.last().show();
                break;
            case 'decimal':
                $addons.first().show();
                $addons.last().hide();
                break;
        }
    }

    function initChargeOptions(target) {
        const chargeFields = $('#js-option-charge').html();

        const $target = $(target);
        const $fields = $(chargeFields);

        const $select = $fields.find('.select-charge');
        const $btnDelete = $fields.find('.btn-delete');

        $select.on('change', function(e) {
            formatChargeAmountInput(this);
        });

        $btnDelete.on('click', function(e) {
            $(this).closest('.row').remove();
        });

        $target.append($fields);

        $select.trigger('change');
    }

    document.appendChargeOptions = function(target) {
        initChargeOptions(target);
    };

    $('#outlet-address-checkbox').on('click', function(e) {
        if ($('#outlet-address-checkbox').get(0).checked) {
            $('#outlet-address-fieldset').hide();
        } else {
            $('#outlet-address-fieldset').show();
        }
    });
});
