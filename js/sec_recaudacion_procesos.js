function sec_recaudacion_procesos(){
    recaudacion_procesos_events()
}

/*$("#datatable_liquidaciones").DataTable({
        serverSide: true,
        processing: true,
        dom: 'tp',
        pageLength: lastDaysCount
    })*/

const recaudacion_procesos_events = () => {
    let lastDaysCount = parseInt($("#recaudacion_last_days_count").val())
    if (isNaN(lastDaysCount)) lastDaysCount = 0
    if (lastDaysCount < 10) lastDaysCount = 10

    get_procesos(0)

    function get_procesos (page) {
        loading(true)
        auditoria_send({"proceso":"recaudacion_procesos_get","data":{page : page}});
        $.post('/sys/get_recaudacion_procesos.php', {"get_procesos": {page : page}}, function (response) {
            let result = JSON.parse(response);

            $("#procesos_tbody").html(result.tabla);
            let num_row = result.num_rows;
            $("#pagination_recaudacion_procesos").pagination({
                items: num_row,
                currentPage: page + 1,
                itemsOnPage: result.last_days_count, //lastDaysCount
                cssStyle: 'light-theme',
                onPageClick: function (pageNumber, event) {
                    event.preventDefault();
                    get_procesos(pageNumber - 1);
                }
            });

            loading(false);
        });
        $("#procesos_tbl_body")
    }
}

