$(function(){

    $("#select-usuario_id").select2();

    var fechaActual = $('#fecha_actual').val();

    // Establecer valor inicial de hoy en ambos inputs
    $('#SecRepLog_fecha_inicio').val(fechaActual);
    $('#SecRepLog_fecha_fin').val(fechaActual);

    function aplicarValidacionFechas() {
        var fechaInicio = $('#SecRepLog_fecha_inicio').val();
        var fechaFin = $('#SecRepLog_fecha_fin').val();

        // Establecer mínimo de fecha de fin igual a fecha de inicio
        $('#SecRepLog_fecha_fin').attr('min', fechaInicio);

        // Establecer máximo de fecha de inicio igual a fecha de fin
        $('#SecRepLog_fecha_inicio').attr('max', fechaFin);

        // Verificar y ajustar si la fecha de fin es anterior a la fecha de inicio
        if (fechaFin < fechaInicio) {
            $('#SecRepLog_fecha_fin').val(fechaInicio);
        }
    }

    // Llamar a la función al cargar la página
    aplicarValidacionFechas();

    // Escuchar el evento de cambio en la fecha de inicio
    $('#SecRepLog_fecha_inicio').on('change', function() {
        aplicarValidacionFechas();
    });

    // Escuchar el evento de cambio en la fecha de fin
    $('#SecRepLog_fecha_fin').on('change', function() {
        aplicarValidacionFechas();
    });

    function cargarDatos() {

        var SecRepLog_fecha_inicio = $.trim($("#SecRepLog_fecha_inicio").val());
        var SecRepLog_fecha_fin = $.trim($("#SecRepLog_fecha_fin").val());
        var SecRepLog_usuario_id = $("#select-usuario_id").val();

        $("#login_log_div_tabla").show();
    
        var data = {
            accion: "get_reportes_login_log",
            SecRepLog_fecha_inicio: SecRepLog_fecha_inicio,
            SecRepLog_fecha_fin: SecRepLog_fecha_fin,
            SecRepLog_usuario_id: SecRepLog_usuario_id
        };

        var columnDefs = [{
            className: 'text-center',
            targets: [0, 1, 2, 3, 4]
        }];

        var tabla = crearDataTable(
            "#tabla_login_log",
            "/sys/get_reportes_login_log.php",
            data,
            columnDefs
        );

        tabla.on('init.dt', function () {
            $('.dataTables_filter').hide();
        });
        
    }

    cargarDatos();

    $("#SecRepLog_btn_buscar").on('click', function () {
        cargarDatos();
    });

    $("#SecRepLog_btn_exportar").on('click', function () {
        var SecRepLog_fecha_inicio = $.trim($("#SecRepLog_fecha_inicio").val());
        var SecRepLog_fecha_fin = $.trim($("#SecRepLog_fecha_fin").val());
        var SecRepLog_usuario_id = $("#select-usuario_id").val();
    
        var data = {
            "accion": "export_reportes_login_log",
            "fecha_inicio": SecRepLog_fecha_inicio,
            "fecha_fin": SecRepLog_fecha_fin,
            "usuario_id": SecRepLog_usuario_id
        };
    
        $.ajax({
            url: "/sys/get_reportes_login_log.php",
            type: 'POST',
            data: data,
            beforeSend: function() {
                loading("true");
            },
            complete: function() {
                loading();
            },
            success: function(resp) {
                let obj = JSON.parse(resp);
                window.open(obj.path);
                loading(false);
            },
            error: function() {
                console.error("Error al exportar los datos.");
            }
        });
    });

});