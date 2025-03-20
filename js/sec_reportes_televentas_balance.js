
$(function () {


    $('#SecRepTel_cajero_balance').select2(); 
    $('#select-cliente_id_balance').select2();

    $('#SecRepTel_fecha_inicio_balance').datetimepicker({
        format: 'YYYY-MM-DD'
    });
    $('#SecRepTel_fecha_fin_balance').datetimepicker({
        format: 'YYYY-MM-DD'
    });

    $('#SecRepTel_fecha_inicio_balance').val($('#g_fecha_actual_balance').val());
    $('#SecRepTel_fecha_fin_balance').val($('#g_fecha_actual_balance').val());

    $('#SecRepTel_fecha_inicio_balance').change(function () {
        var var_fecha_change = $('#SecRepTel_fecha_inicio_balance').val();
        if (!(parseInt(var_fecha_change.length) > 0)) {
            $("#SecRepTel_fecha_inicio_balance").val($("#g_fecha_actual_balance").val());
        }
    });
    $('#SecRepTel_fecha_fin_balance').change(function () {
        var var_fecha_change = $('#SecRepTel_fecha_fin_balance').val();
        if (!(parseInt(var_fecha_change.length) > 0)) {
            $("#SecRepTel_fecha_fin_balance").val($("#g_fecha_actual_balance").val());
        }
    });


    $('#SecRepTel_btn_buscar_balance').click(function() {
        //listar_SecRepTelBal_tabla_transacciones_balance();
        listar_SecRepTelBal_tabla_transacciones_balance_v2();
    });

    $("#SecRepTel_btn_exportar_balance").on('click', function () {

        var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio_balance").val());
        var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin_balance").val());
        var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion_balance").val();
        var SecRepTel_cajero = $("#SecRepTel_cajero_balance").val();
        var SecRepTel_cliente_id = $("#select-cliente_id_balance").val();

        var data = {
            "accion": "listar_transacciones_export_xls",
            "fecha_inicio": SecRepTel_fecha_inicio,
            "fecha_fin": SecRepTel_fecha_fin,
            "tipo_transaccion": SecRepTel_tipo_transaccion,
            "cajero": SecRepTel_cajero,
            "cliente_id": SecRepTel_cliente_id
        }

        $.ajax({
            url: "/sys/get_reportes_televentas_balance.php",
            type: 'POST',
            data: data,
            beforeSend: function() {
                loading("true");
            },
            complete: function() {
                loading();
            },
            success: function(resp) {
                //console.log(respuesta);
                let obj = JSON.parse(resp);
                window.open(obj.path);
                loading(false);
            },
            error: function() {}
        });
    });


});

function sec_reportes_televentas_balance() {
    if (sec_id == 'reportes' && sub_sec_id=='televentas_balance') {
        $('#SecRepTel_fecha_inicio_balance').val($('#g_fecha_actual_balance').val());
        $('#SecRepTel_fecha_fin_balance').val($('#g_fecha_actual_balance').val());
        //listar_SecRepTelBal_tabla_transacciones_balance();
    }
}


function listar_SecRepTelBal_tabla_transacciones_balance() {
    limpiar_SecRepTelBal_tabla_transacciones_balance();

    var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio_balance").val());
    var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin_balance").val());
    var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion_balance").val();
    var SecRepTel_cajero = $("#SecRepTel_cajero_balance").val();
    var SecRepTel_cliente = $("#select-cliente_id_balance").val();

    if (SecRepTel_fecha_inicio.length !== 10) {
        $("#SecRepTel_fecha_inicio_balance").focus();
        return false;
    }
    if (SecRepTel_fecha_fin.length !== 10) {
        $("#buscador_texto_balance").focus();
        return false;
    }

    var data = {
        "accion": "listar_transacciones_balance",
        "fecha_inicio": SecRepTel_fecha_inicio,
        "fecha_fin": SecRepTel_fecha_fin,
        "tipo_transaccion": SecRepTel_tipo_transaccion,
        "cajero": SecRepTel_cajero,
        "cliente": SecRepTel_cliente
    }

    auditoria_send({ "proceso": "listar_transacciones_balance", "data": data });
    $.ajax({
        url: "/sys/get_reportes_televentas_balance.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
                loading("true");
        },
        complete: function() {
                loading();
        },
        success: function(resp) { //  alert(datat)
            var respuesta = JSON.parse(resp);
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                var monto_cant=0;
                var bono_cant=0;
                var recarga_cant=0;
                var monto_total=0;
                var bono_total=0;
                var recarga_total=0;
                if (respuesta.result.length > 0) {
                    $.each(respuesta.result, function(index, item) {
                        if(parseInt(item.cod_tipo_transaccion)===1){
                            monto_cant++;
                            monto_total+=parseFloat(item.monto);
                        }
                        if(parseInt(item.cod_tipo_transaccion)===2){
                            recarga_cant++;
                            recarga_total+=parseFloat(item.monto);
                            if(parseFloat(item.bono_monto)>0){
                                bono_cant++;
                                bono_total+=parseFloat(item.bono_monto);
                            }
                        }
                        $('#SecRepTel_tabla_transacciones_balance').append(
                            '<tr>' +
                            '<td class="text-center">' + (index+1) + '</td>' +
                            '<td class="text-center">' + item.tipo_transaccion + '</td>' +
                            '<td class="text-center">' + item.telefono + '</td>' +
                            '<td class="text-center">' + item.tipo_doc + '</td>' +
                            '<td class="text-center">' + item.num_doc + '</td>' +
                            '<td class="text-center">' + item.web_id + '</td>' +
                            '<td class="text-center">' + item.cliente + '</td>' +
                            '<td class="text-center">' + item.cuenta + '</td>' +
                            '<td class="text-right">' + item.monto + '</td>' +
                            '<td class="text-right">' + item.bono_monto + '</td>' +
                            '<td class="text-right">' + item.total_recarga + '</td>' +
                            '<td class="text-left">' + item.cajero + '</td>' +
                            '<td class="text-left">' + item.supervisor + '</td>' +
                            '<td class="text-right">' + item.fecha_hora_registro + '</td>' +
                            '<td class="text-left">' + item.update_user_at + '</td>' +
                            '<td class="text-left">' + item.observacion_cajero + '</td>' +
                            '<td class="text-left">' + item.observacion_validador + '</td>' +
                            '<td class="text-left">' + item.observacion_supervisor + '</td>' +
                            '</tr>'
                        );
                    });
                    DATATABLE_FORMATO_SecRepTelBal_tabla_transacciones('#SecRepTel_tabla_transacciones_balance');
                } else {
                    $('#SecRepTel_tabla_transacciones_balance').append(
                        '<tr>' +
                        '<td class="text-center" colspan="13">No hay transacciones.</td>' +
                        '</tr>'
                    );
                }
                $('#SecRepTel_deposito_cant_balance').val(monto_cant);
                $('#SecRepTel_bono_cant_balance').val(bono_cant);
                $('#SecRepTel_recarga_cant_balance').val(recarga_cant);
                $('#SecRepTel_monto_total_balance').val(parseFloat(monto_total).toFixed(2));
                $('#SecRepTel_bono_total_balance').val(parseFloat(bono_total).toFixed(2));
                $('#SecRepTel_recarga_total_balance').val(parseFloat(recarga_total).toFixed(2));
                $('#SecRepTel_total_recargas_bonos_balance').val((parseFloat(bono_total)+parseFloat(recarga_total)).toFixed(2));
                //console.log(array_clientes);
                return false;
            }
        },
        error: function() {}
    });
}


function limpiar_SecRepTelBal_tabla_transacciones_balance() {
    $('#SecRepTel_tabla_transacciones_balance').html(
        '<thead>' +
        '   <tr>' +
        '       <th>#</th>' +
        '       <th>TIPO</th>' +
        '       <th>TELÉFONO</th>' +
        '       <th>TIPO DOC.</th>' +
        '       <th>NÚM. DOC.</th>' +
        '       <th>WEB-ID</th>' +
        '       <th>CLIENTE</th>' +
        '       <th>CUENTA</th>' +
        '       <th>MONTO</th>' +
        '       <th>BONO</th>' +
        '       <th>RECARGA</th>' +
        '       <th>PROMOTOR</th>' +
        '       <th>SUPERVISOR ELIMINA</th>' +
        '       <th>REGISTRO</th>' +
        '       <th>FECHA ELIMINA</th>' +
        '       <th>OBSERVACION CAJERO</th>' +
        '       <th>OBSERVACION VALIDADOR</th>' +
        '       <th>OBSERVACION SUPERVISOR</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#SecRepTel_deposito_cant_balance').val('0');
    $('#SecRepTel_bono_cant_balance').val('0');
    $('#SecRepTel_recarga_cant_balance').val('0');
    $('#SecRepTel_monto_total_balance').val('0.00');
    $('#SecRepTel_bono_total_balance').val('0.00');
    $('#SecRepTel_recarga_total_balance').val('0.00');
    $('#SecRepTel_total_recargas_bonos_balance').val('0.00');
}


function DATATABLE_FORMATO_SecRepTelBal_tabla_transacciones(id) {
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
    }
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

function get_SecRepTelBal_tabla_transacciones_balance_v2() {
    var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio_balance").val());
    var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin_balance").val());
    var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion_balance").val();
    var SecRepTel_cajero = $("#SecRepTel_cajero_balance").val();
    var SecRepTel_cliente = $("#select-cliente_id_balance").val();

    if (SecRepTel_fecha_inicio.length !== 10) {
        $("#SecRepTel_fecha_inicio_balance").focus();
        return false;
    }
    if (SecRepTel_fecha_fin.length !== 10) {
        $("#buscador_texto_balance").focus();
        return false;
    }

    var data = {
        "accion": "listar_transacciones_balance_v2",
        "fecha_inicio": SecRepTel_fecha_inicio,
        "fecha_fin": SecRepTel_fecha_fin,
        "tipo_transaccion": SecRepTel_tipo_transaccion,
        "cajero": SecRepTel_cajero,
        "cliente": SecRepTel_cliente
    }
    return data;
}

function listar_SecRepTelBal_tabla_transacciones_balance_v2() {
    var table = $('#SecRepTel_tabla_transacciones_balance_v2').DataTable();
    table.clear();
    table.destroy();
    var table = $('#SecRepTel_tabla_transacciones_balance_v2').DataTable({
         'destroy': true,
        'scrollX': true,
        "processing": true,
        "serverSide": true,
        'ordering': false,
        "ajax": {
            type: "POST",
            async : true,
            "url": "/sys/get_reportes_televentas_balance.php",
            "data": get_SecRepTelBal_tabla_transacciones_balance_v2()
        },
        // "dataSrc": function (json) {
        //     var result = JSON.parse(json);
        //     return result.result;
        // },
        "order": [],
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
        },
        "columns": [
            { "data": null,
            render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
            },            
            {"data": "tipo_transaccion"},
            { "data": "telefono" },
            { "data": "tipo_doc" },
            { "data": "num_doc" },
            { "data": "web_id" },
            { "data": "cliente" },
            { "data": "cuenta" },
            { "data": "monto" },
            { "data": "bono_monto" },
            { "data": "total_recarga" },
            { "data": "cajero" },
            { "data": "supervisor" },
            { "data": "fecha_hora_registro" },
            { "data": "update_user_at" },
            { "data": "observacion_cajero" },
            { "data": "observacion_validador" },
            { "data": "observacion_supervisor" }
        ]
        
    });
    
    $(".transacciones_balance_v2").css("display", "");
    $('#SecRepTel_tabla_transacciones_balance_v2').on('init.dt', function () {
        var dtable = $("#SecRepTel_tabla_transacciones_balance_v2").dataTable().api();
        $(".dataTables_filter input")
            .unbind()
            .bind("input", function (e) {
                if (this.value.length >= 4 || e.keyCode == 13) {
                    dtable.search(this.value).draw();
                }
                if (this.value == "") {
                    dtable.search("").draw();
                }
                return;
            });
    });
    $('a.toggle-vis').on( 'click', function (e) {
        e.preventDefault();
    
        // Get the column API object
        var column = table.column( $(this).attr('data-column') );
    
        // Toggle the visibility
        column.visible( ! column.visible() );
    } );
   
}
