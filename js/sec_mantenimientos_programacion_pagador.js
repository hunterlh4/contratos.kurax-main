function sec_mantenimientos_programacion_pagador(){
    if (sec_id == 'mantenimientos' && sub_sec_id=='programacion_pagador'){
        sec_mant_pro_pag_listar_pagadores();
        sec_mant_pro_pag_resetear_fecha_hora_inputs();
        sec_mant_pro_pag_listarProgramaciones();
    }	
}

function sec_mant_pro_pag_listar_pagadores(){
    var data = {
        "accion": "obtener_pagadores"
    }

    auditoria_send({ "proceso": "obtener_pagadores", "data": data });
    $.ajax({
        url: "/sys/set_mantenimiento_programacion_pagador.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            var respuesta = JSON.parse(resp);
            $('#sec_pro_pag_select_pagadores').html('');
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#sec_pro_pag_select_pagadores').append(
                    '<option value="0"> TODOS </option>'
                );
                $.each(respuesta.result, function(index, item) {
                    $('#sec_pro_pag_select_pagadores').append(
                        '<option value="' + item.id + '">' + item.pagador + '</option>'
                    );
                });
                return false;
            }       
        },
        error: function() {}
    });
}

function sec_mant_pro_pag_listarProgramaciones(){
    var pagador = $('#sec_pro_pag_select_pagadores').val();
    var desde = $('#sec_pro_pag_desde_fecha').val();
    var hasta = $('#sec_pro_pag_hasta_fecha').val();
    if(pagador == null || desde == null || hasta == null){
        resetear_fecha_hora_inputs();
        return false;
    }

    var data = {
        "accion": "obtener_programaciones_pagadores",
        "pagador": pagador,
        "desde": desde,
        "hasta": hasta
    }
    auditoria_send({ "proceso": "obtener_programaciones_pagadores", "data": data });
    $.ajax({
        url: "/sys/set_mantenimiento_programacion_pagador.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            formato_datatable_destroy('#sec_pro_pag_table_programaciones');
            $('#sec_pro_pag_table_programaciones tbody').html('');
            var respuesta = JSON.parse(resp);
            rr_data_programaciones = [];
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.result, function(index, item) {
                    var boton_editar = '';

                    var fecha = fecha_hora();
                    if(fecha >= item.desde){
                        boton_editar = '';
                    }else{
                        var onclick = "sec_mant_pro_pag_abrir_modal_edit_programacion(" + item.id + "," + item.user_id + ",'" + item.pagador + "','" + item.desde + "', '" + item.hasta + "')";
                        boton_editar = '<button type="button" class="btn btn-info btn-sm" onclick="' + onclick + '"><i class="fa fa-edit"></i></button>';
                    }
                    $('#sec_pro_pag_table_programaciones tbody').append(
                        '<tr>'
                        + '<td>' + item.id + '</td>'
                        + '<td>' + item.pagador + '</td>'
                        + '<td style="color: #5CA946; font-weight: bold;">' + item.desde + '</td>'
                        + '<td style="color: #DE4F45; font-weight: bold;">' + item.hasta + '</td>'
                        + '<td>' + item.created_at + '</td>'
                        + '<td>' + boton_editar + '</td>'
                        + '</tr>'
                    );
                });
                formato_datatable('#sec_pro_pag_table_programaciones');
                return false;
            }       
        },
        error: function() {}
    });
}

function sec_mant_pro_pag_agregar_programacion(){
    var pagador = $('#sec_pro_pag_select_pagadores').val();
    var desde = $('#sec_pro_pag_desde_fecha').val();
    var hasta = $('#sec_pro_pag_hasta_fecha').val();
    var fecha = fecha_hora();
    if(pagador == 0){
        swal('Aviso', 'Seleccione un pagador.', 'warning');
        return false;
    }
    if(desde > hasta){
        swal('Aviso', 'La fecha y hora de inicio no puede ser mayor a la fecha y hora final.', 'warning');
        return false;
    }else if(desde == hasta){
        swal('Aviso', 'Las fechas y horas no pueden ser iguales', 'warning');
        return false;
    }else if(desde.replace('T',' ') < fecha || hasta.replace('T',' ') < fecha){
        swal('Aviso', 'Las fechas y horas no pueden ser menores a la fecha y hora actual', 'warning');
        return false;
    }
    /***********************VALIDACION HORARIO****************************/
    var data = {
        "accion": "obtener_programaciones_pagadores_guardadas",
        "desde": desde,
        "hasta": hasta
    }
    auditoria_send({ "proceso": "obtener_programaciones_pagadores_guardadas", "data": data });
    $.ajax({
        url: "/sys/set_mantenimiento_programacion_pagador.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                sec_mant_pro_pag_guardar_nueva_programacion();
            }
            if (parseInt(respuesta.http_code) == 200) {
                
                $.each(respuesta.result, function(index, item) {
                    swal('Aviso', "El pagador " + item.pagador + " tiene un turno \n desde : " + item.desde + " hasta : " + item.hasta, 'warning');
                    return false;
                });
                return false;
            }       
        },
        error: function() {}
    });
}

function sec_mant_pro_pag_guardar_nueva_programacion(){
    var pagador = $('#sec_pro_pag_select_pagadores').val();
    var desde = $('#sec_pro_pag_desde_fecha').val();
    var hasta = $('#sec_pro_pag_hasta_fecha').val();
    var data = {
        "accion": "guardar_programacion_horario_pagador",
        "pagador": pagador,
        "desde": desde,
        "hasta": hasta
    }

    auditoria_send({ "proceso": "guardar_programacion_horario_pagador", "data": data });
    $.ajax({
        url: "/sys/set_mantenimiento_programacion_pagador.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                //swal('Aviso', 'Se agregó la programación exitosamente.', 'success');
                sec_mant_pro_pag_resetear_fecha_hora_inputs();
                sec_mant_pro_pag_listarProgramaciones();
                return false;
            }       
        },
        error: function() {}
    });
}

function sec_mant_pro_pag_resetear_fecha_hora_inputs(){
	$('#sec_pro_pag_select_pagadores').val('0').trigger('change.select2');
    var dt = new Date();
    var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
    var hora_actual = dt.getHours();

    var sec_mant_pro_pag_fecha_input_desde = new Date().toJSON().slice(0,11) + '00:00';
    console.log(sec_mant_pro_pag_fecha_input_desde);
    var sec_mant_pro_pag_fecha_input_hasta = new Date().toJSON().slice(0,11) + '23:59';

    $("#sec_pro_pag_desde_fecha").val(sec_mant_pro_pag_fecha_input_desde);
    $("#sec_pro_pag_hasta_fecha").val(sec_mant_pro_pag_fecha_input_hasta);

    $("#sec_pro_pag_desde_fecha").attr("min", sec_mant_pro_pag_fecha_input_desde);
    $("#sec_pro_pag_hasta_fecha").attr("min", sec_mant_pro_pag_fecha_input_hasta);
}

function sec_mant_pro_pag_fecha_hora(){
    var dateN = new Date(); 
    var fecha = dateN.getFullYear() + '-' +
                String('0' + (dateN.getMonth() + 1)).slice(-2) + '-' +
                String('0' + dateN.getDate()).slice(-2) + ' ' +
                String('0' + dateN.getHours()).slice(-2) + ':' +
                String('0' + dateN.getMinutes()).slice(-2) + ':' +
                String('0' + dateN.getSeconds()).slice(-2);
    return fecha;
}

function sec_mant_pro_pag_cancelar_edicion_programacion(){
    $('#sec_pro_pag_input_id_pagador_edit').val('');
    $('#sec_pro_pag_input_id_programacion_edit').val('');

    $('#sec_pro_pag_input_pagador_edit').val('');
    $('#sec_pro_pag_input_desde_edit').val('');
    $('#sec_pro_pag_input_hasta_edit').val('');
    $('#sec_pro_pag_modal_edit_programacion_pago').modal('hide')
}

function sec_mant_pro_pag_abrir_modal_edit_programacion(id_programacion, id_pagador, pagador, desde, hasta){
    $('#sec_pro_pag_modal_edit_programacion_pago').modal();  

    $('#sec_pro_pag_input_id_pagador_edit').val(id_pagador);
    $('#sec_pro_pag_input_id_programacion_edit').val(id_programacion);

    $('#sec_pro_pag_input_pagador_edit').val(pagador);
    $('#sec_pro_pag_input_desde_edit').val(desde.replace(' ','T'));
    $('#sec_pro_pag_input_desde_edit').attr("min", desde.replace(' ','T'));
    $('#sec_pro_pag_input_hasta_edit').val(hasta.replace(' ','T'));

}

function sec_mant_pro_pag_validar_actualizacion_programacion(){
    var pagador = $('#sec_pro_pag_input_pagador_edit').val();
    var programacion = $('#sec_pro_pag_input_id_programacion_edit').val();
    var desde = $('#sec_pro_pag_input_desde_edit').val();
    var hasta = $('#sec_pro_pag_input_hasta_edit').val();
    var fecha = fecha_hora();
    if(desde > hasta){
        swal('Aviso', 'La fecha y hora de inicio no puede ser mayor a la fecha y hora final.', 'warning');
        return false;
    }else if(desde == hasta){
        swal('Aviso', 'Las fechas y horas no pueden ser iguales', 'warning');
        return false;
    }else if(desde.replace('T',' ') < fecha || hasta.replace('T',' ') < fecha){
        swal('Aviso', 'Las fechas y horas no pueden ser menores a la fecha y hora actual', 'warning');
        return false;
    }
    /***********************VALIDACION PROGRAMACION****************************/
    var data = {
        "accion": "obtener_programaciones_pagadores_edicion",
        "programacion": programacion,
        "desde": desde,
        "hasta": hasta
    }
    auditoria_send({ "proceso": "obtener_programaciones_pagadores_edicion", "data": data });
    $.ajax({
        url: "/sys/set_mantenimiento_programacion_pagador.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            console.log(resp);
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                sec_mant_actualizar_programacion();
            }
            if (parseInt(respuesta.http_code) == 200) {
                
                $.each(respuesta.result, function(index, item) {
                    swal('Aviso', "El pagador " + item.pagador + " tiene un turno \n desde : " + item.desde + " hasta : " + item.hasta, 'warning');
                    return false;
                });
                return false;
            }       
        },
        error: function() {}
    });
}

function sec_mant_pro_pag_actualizar_programacion(){
    var programacion = $('#sec_pro_pag_input_id_programacion_edit').val();
    var pagador = $('#sec_pro_pag_input_id_pagador_edit').val();
    var desde = $('#sec_pro_pag_input_desde_edit').val();
    var hasta = $('#sec_pro_pag_input_hasta_edit').val();
    var data = {
        "accion": "actualizar_programacion_horario_pagador",
        "programacion": programacion,
        "pagador": pagador,
        "desde": desde,
        "hasta": hasta
    }

    auditoria_send({ "proceso": "actualizar_programacion_horario_pagador", "data": data });
    $.ajax({
        url: "/sys/set_mantenimiento_programacion_pagador.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            console.log(resp);
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                //swal('Aviso', 'Se agregó la programación exitosamente.', 'success');
                sec_mant_pro_pag_cancelar_edicion_programacion();
                sec_mant_pro_pag_listarProgramaciones();
                return false;
            }       
        },
        error: function() {}
    });
}

function sec_mant_pro_pag_formato_datatable_destroy(id){
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
    }
}

function sec_mant_pro_pag_formato_datatable(id) {
    $(id).DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'order': [[ 0, "asc" ]],
        'info': true,
        'autoWidth': false,
        "language": {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "infoPostFix": "",
            "search": "Buscar: ",
            "url": "",
            "infoThousands": ",",
            "loadingRecords": "Cargando...",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
}