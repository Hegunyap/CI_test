$(document).ready(function() {
    if ($('.js-datatable').length > 0) {
        $('.js-datatable').each(function(index, table) {
            const $table = $(table);

            $table.DataTable({
                processing: true,
                responsive: true,
                order: [0,'asc'],
                pageLength: 25,
                lengthMenu: [[25,50,100,200], [25,50,100,200]],
            })
            .draw();
        });
    }
});
