$(document).ready(function() {
    function previewInvoiceNumber() {
        const prefix = $("#invoice-prefix").val();
        const number = $("#invoice-number").val();
        const strpad = $("#invoice-strpad").val();

        $("#js-preview-invoice-number").html(prefix + stringPad(number, strpad));
    }

    function addInvoiceItem() {
        const $row = $($('#js-invoice-item').html());
        const $tableTbody = $('#table-invoice-items tbody');

        $row.find('.js-delete-invoice-item').on('click', function(e) {
            if (confirm('This is not reversible. Are you sure?')) {
                $(e.currentTarget).closest('tr').parent().remove();
            }
        });

        $tableTbody.append($row);
    }

    if ($('#invoice-prefix, #invoice-strpad, #invoice-number').length > 0) {
        $("#invoice-prefix").on('change', function(e) {
            previewInvoiceNumber();
        });

        $("#invoice-strpad").on('change', function(e) {
            previewInvoiceNumber();
        });

        $("#invoice-number").on('change', function(e) {
            previewInvoiceNumber();
        });

        previewInvoiceNumber();
    }

    $('#js-new-invoice-item').on('click', function(e) {
        addInvoiceItem();
    });

    addInvoiceItem();
});
