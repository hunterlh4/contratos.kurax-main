function sec_resumen_apuestas_aterax() {
    if (sec_id=="resumen_apuestas_aterax") {
        console.log("sec:resumen_apuestas_aterax");
        sec_resumen_apuestas_aterax_events();
    }
}

function sec_resumen_apuestas_aterax_events() {
    sec_resumen_apuestas_aterax_get_local_creacion();
    sec_resumen_apuestas_aterax_get_tipo();
    sec_resumen_apuestas_aterax_get_estado();

    $("#btn_consultar_resumen_apuesta_aterax").on("click",function(){
		sec_resumen_apuestas_aterax_get_data();
	});

    function sec_resumen_apuestas_aterax_get_data() {
        loading(true);
        var resumen_apuestas_aterax_data = {};
        resumen_apuestas_aterax_data.action = "get_data_table";

        resumen_apuestas_aterax_data.filtro_local_creacion = $("#filtro_local_creacion").val();

        resumen_apuestas_aterax_data.filtro_cuota_menor = $(".filtro_cuota_menor").val();
        resumen_apuestas_aterax_data.filtro_cuota_mayor = $(".filtro_cuota_mayor").val();
        resumen_apuestas_aterax_data.filtro_monto_menor = $(".filtro_monto_menor").val();
        resumen_apuestas_aterax_data.filtro_monto_mayor = $(".filtro_monto_mayor").val();

        resumen_apuestas_aterax_data.filtro_fecha_creacion_inicio = $("#filtro_fecha_creacion_inicio").val();
        resumen_apuestas_aterax_data.filtro_fecha_creacion_fin = $("#filtro_fecha_creacion_fin").val();

        resumen_apuestas_aterax_data.filtro_canal = $("#filtro_canal").val();
        resumen_apuestas_aterax_data.filtro_tipo = $("#filtro_tipo").val();
        resumen_apuestas_aterax_data.filtro_vivo = $("#filtro_vivo").val();
        resumen_apuestas_aterax_data.filtro_estado = $("#filtro_estado").val();
        resumen_apuestas_aterax_data.filtro_pagado = $("#filtro_pagado").val();

        $.ajax({
            data: resumen_apuestas_aterax_data,
            type: "POST",
            url: "sys/set_resumen_apuestas_aterax.php",
            async: "false"
        })
        .done(function(responsedata, textStatus, jqXHR ) {
            try{
                var obj = jQuery.parseJSON(responsedata);
                sec_resumen_apuestas_aterax_datatable(obj.data);

                $("#text_apostado_resumen").text(obj.total_apostado);
                $("#text_pagado_resumen").text(obj.total_ganado);
            }catch(err){
                console.log(err);
                swal({
                    title: 'Error en la base de datos',
                    type: "warning",
                    timer: 2000,
                }, function(){
                    swal.close();
                }); 
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            if ( console && console.log ) {
                console.log( "La solicitud a fallado: " +  textStatus);
            }
        });
    }

    function sec_resumen_apuestas_aterax_datatable(data) {
        $("#table_resumen_apuestas")
        .dataTable({
            bDestroy: true,
            data: data,
            responsive: true,
            order: [[0, 'desc']],
            pageLength: 10,
            bRetrieve: true,
            lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],
            paging: true,
		    searching: true,
            sPaginationType: "full_numbers",
            bSort: false,
            columns: [
                { data: "id_billete", className: "text-center" },
                { data: "local_creacion", className: "text-left" }, 
                { data: "canal", className: "text-left" },
                { data: "cashdesk", className: "text-left" },
                { data: "tipo", className: "text-left" },
                { data: "vivo", className: "text-left" },
                { data: "apostado", className: "text-left" },
                { data: "cuota", className: "text-left" },
                { data: "estado", className: "text-left" },
                { data: "ganado", className: "text-left" },
                { data: "fecha_creacion", className: "text-left" },
                { data: "fecha_calculo", className: "text-left" },
                { data: "fecha_pago", className: "text-left" },
                { data: "caja_pago", className: "text-left" },
            ],
            language: {
                decimal: "",
                emptyTable: "Tabla vacia",
                info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                infoEmpty: "Mostrando 0 a 0 de 0 entradas",
                infoFiltered: "(filtered from _MAX_ total entradas)",
                infoPostFix: "",
                thousands: ",",
                lengthMenu: "Mostrar _MENU_ entradas",
                loadingRecords: "Cargando...",
                processing: "Procesando...",
                search: "Buscar:",
                zeroRecords: "Sin resultados",
                paginate: {
                    first: "Primero",
                    last: "Ultimo",
                    next: "Siguiente",
                    previous: "Anterior",
                },
                aria: {
                    sortAscending: ": activate to sort column ascending",
                    sortDescending: ": activate to sort column descending",
                },
                buttons: {
                    pageLength: {
                        _: "Mostrando %d Resultados",
                        '-1': "Mostrando todos los resultados"
                    }
                }
            },
            scrollY: true,
            scrollX: true,
            dom: 'Blrftip',
            buttons: [
                { 
                    extend: 'copy',
                    text:'Copiar',
                    footer: true,
                    className: 'copiarButton'
                 },
                { 
                    extend: 'csv',
                    text:'CSV',
                    footer: true,
                    className: 'csvButton' 
                    ,filename: $(".export_filename").val()
                },
                {   extend: 'excelHtml5',
                    text:'Excel',
                    footer: true,
                    className: 'excelButton'
                    ,filename: $(".export_filename").val()
                }, 
                {
                    extend: 'colvis',
                    text:'Visibilidad',
                    className:'visibilidadButton',
                    postfixButtons: [ 'colvisRestore' ]
                }
            ]
        })
        .DataTable();
        loading(false);
    }

    function sec_resumen_apuestas_aterax_get_tipo() {
        var data_tipo = {};
        data_tipo.action = "get_tipo";

        $.ajax({
            data: data_tipo,
            type: "POST",
            url: "sys/set_resumen_apuestas_aterax.php",
            async: "false"
        })
        .done(function(responsedata, textStatus, jqXHR ) {
            try{
                var obj = jQuery.parseJSON(responsedata);
                $.each(obj.data_tipo, function(index, option) {
                    $('#filtro_tipo').append('<option value="' + option.id + '">' + option.nome + '</option>');
                });
            }catch(err){
                console.log(err);
                swal({
                    title: 'Error en la base de datos',
                    type: "warning",
                    timer: 2000,
                }, function(){
                    swal.close();
                });
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            if ( console && console.log ) {
                console.log( "La solicitud a fallado: " +  textStatus);
            }
        });
    }

    function sec_resumen_apuestas_aterax_get_estado() {
        var data_estado = {};
        data_estado.action = "get_estado";

        $.ajax({
            data: data_estado,
            type: "POST",
            url: "sys/set_resumen_apuestas_aterax.php",
            async: "false"
        })
        .done(function(responsedata, textStatus, jqXHR ) {
            try{
                var obj = jQuery.parseJSON(responsedata);
                $.each(obj.data_estado, function(index, option) {
                    $('#filtro_estado').append('<option value="' + option.id + '">' + option.status + '</option>');
                });
            }catch(err){
                console.log(err);
                swal({
                    title: 'Error en la base de datos',
                    type: "warning",
                    timer: 2000,
                }, function(){
                    swal.close();
                });
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            if ( console && console.log ) {
                console.log( "La solicitud a fallado: " +  textStatus);
            }
        });
    }

    function sec_resumen_apuestas_aterax_get_local_creacion() {
        var data_local_creacion = {};
        data_local_creacion.action = "get_local_creacion";

        $.ajax({
            data: data_local_creacion,
            type: "POST",
            url: "sys/set_resumen_apuestas_aterax.php",
            async: "false"
        })
        .done(function(responsedata, textStatus, jqXHR ) {
            try{
                var obj = jQuery.parseJSON(responsedata);
                $.each(obj.data_local_creacion, function(index, option) {
                    $('#filtro_local_creacion').append('<option value="' + option.id + '">' + option.nome + '</option>');
                });
            }catch(err){
                console.log(err);
                swal({
                    title: 'Error en la base de datos',
                    type: "warning",
                    timer: 2000,
                }, function(){
                    swal.close();
                });
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            if ( console && console.log ) {
                console.log( "La solicitud a fallado: " +  textStatus);
            }
        });
    }
}