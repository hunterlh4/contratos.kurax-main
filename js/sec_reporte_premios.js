function sec_reporte_premios() {
    if (sec_id == "reportes" && sub_sec_id == "premios") {
        sec_reporte_premios_events();
    }
}

function sec_reporte_premios_events() {
    $('#fecha_fin').datetimepicker({
        format:'YYYY-MM-DD'
    }).on('dp.change', function () {
        $('.report_filter').trigger('change')
    });

    $('#fecha_inicio').datetimepicker({
        format:'YYYY-MM-DD'
    }).on('dp.change', function () {
        $('.report_filter').trigger('change')
    });

    $('.close_fixed').on('click', function () {
        $(this).parent().parent().removeClass('active');
    });

    $('body').on('click', '.img-galery', function () {
        let imgs = $(this).find('img');
        let urls = imgs.attr('alt');
        console.log(urls);
        $('.fondo_fixed').find('img').attr('src', '../files_bucket/registros/premios/' + urls);
        $('.fondo_fixed').addClass('active');
    });

    function customDataSuccess(data) {
        var content = "";
        for (var i in data['items']) {
            var img = data['items'][i]['img'];
            content += "<div class='img-galery'><img src='../files_bucket/registros/premios/min_" + img + "' alt=" + img + " ></div>";
        }
        $(".gallery").html('');
        $(".gallery").append(content);
        $('#imgsModal').modal('show');
    }


    $('body').on('click', '.showImgs', function () {
        //$('#imgsModal').modal('show');
        var id = $(this).attr('data-id');
        var data = {};
        data.id = id;
        data.type = $(this).attr('data-type');
        $.post('/sys/get_reporte_premios.php', {"open_modal_premios": data}, function (response) {
            let result = JSON.parse(response);
            console.log(result);
            customDataSuccess(result);
        });
    });

    $("#btn_export_reportes_premios").on('click', function () {
        loading(true);
        let data = {
            texto : $('#filtro_search').val(),
            tipo : $('#cbTipo').val(),
            colName : $("#colName").val(),
            order : $("#colOrder").val(),
            fecha_inicio : $("#fecha_inicio").val(),
            fecha_fin : $("#fecha_fin").val(),
            local : $("#local").val(),
        }
        $.post('/sys/get_reporte_premios.php', {"get_tabla_reporte_premios_export_xls": data}, function (response) {
            let obj = JSON.parse(response);
            window.open(obj.path);
            loading(false);
        }).always(function (){
            loading(false)
        });
    })

    $("#local").select2();
    filtro_tabla(0);

    $('#filtro_search').on("keyup", function () {
        clean()
        filtro_tabla(0, false);
    });

    $('.report_filter').on('change', function () {
        clean()
        filtro_tabla(0);
    });

    $('.reporte-thead').on('click', function () {
        $(".reporte-thead").children().removeClass().addClass("order-icon glyphicon glyphicon-sort");
        
        let colName = $(this).data("colName");
        let order = $(this).data("order");

        let newOrder = order === "asc" ? "desc" : "asc";
        $(this).data("order", newOrder);

        $("#colName").val(colName);
        $("#colOrder").val(order === "default" ? "desc" : order);

        $(this).children().removeClass().addClass(newOrder === "asc" ? "order-icon glyphicon glyphicon-sort-by-attributes-alt" : "order-icon glyphicon glyphicon-sort-by-attributes");

        filtro_tabla(0);
    });


    function filtro_tabla(page, showLoad=true) {
        let data = {};
        data.limit = $('#cbMostrar').val();
        data.page = page;
        data.texto = $('#filtro_search').val();
        data.tipo = $('#cbTipo').val();
        data.colName = $("#colName").val();
        data.order = $("#colOrder").val();
        data.fecha_inicio = $("#fecha_inicio").val();
        data.fecha_fin = $("#fecha_fin").val();
        data.local = $("#local").val();

        let limite = data.limit;
        loading(showLoad);
        auditoria_send({"proceso":"get_tabla_reporte_premios","data":data});
        $.post('/sys/get_reporte_premios.php', {"get_tabla_reporte_premios": data}, function (response) {
            let result = JSON.parse(response);

            $("#tbody_premios").html(result.tabla);

            let num_row = result.num_rows;

            $("#pagination_history_premios").pagination({
                items: num_row,
                currentPage: page + 1,
                itemsOnPage: data.limit,
                cssStyle: 'light-theme',
                onPageClick: function (pageNumber, event) {
                    event.preventDefault();
                    filtro_tabla(pageNumber - 1);
                }
            });

            loading(false);
        });
    }

    function clean() {
        $(".reporte-thead").children().removeClass().addClass("order-icon glyphicon glyphicon-sort");
        $("#colName").val("default");
        $("#colOrder").val("default");
    }
}
