$(function () {

    $('#SecRepTel_fecha_inicio').datetimepicker({
        format: 'YYYY-MM-DD'
    });
    $('#SecRepTel_fecha_fin').datetimepicker({
        format: 'YYYY-MM-DD'
    });

    $('#SecRepTel_fecha_inicio').val($('#g_fecha_actual').val());
    $('#SecRepTel_fecha_fin').val($('#g_fecha_actual').val());

    $('#SecRepTel_fecha_inicio').change(function () {
        var var_fecha_change = $('#SecRepTel_fecha_inicio').val();
        if (!(parseInt(var_fecha_change.length) > 0)) {
            $("#SecRepTel_fecha_inicio").val($("#g_fecha_actual").val());
        }
    });
    $('#SecRepTel_fecha_fin').change(function () {
        var var_fecha_change = $('#SecRepTel_fecha_fin').val();
        if (!(parseInt(var_fecha_change.length) > 0)) {
            $("#SecRepTel_fecha_fin").val($("#g_fecha_actual").val());
        }
    });
    

    $('#SecRepTelCli_btn_buscar').click(function () {
 
        fncRenderIdSecRepTelCli_tabla_transacciones_v2();
        fncRenderIdSecRepTelCli_tabla_transacciones_get_totales_v2();
        //btn_excel_rpt_tlv_cli();

    });

    $("#SecRepTelCli_btn_exportar").on('click', function () {

        var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
        var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());

        var data = {
            "accion": "listar_resumen_x_cliente_xls",
            "fecha_inicio": SecRepTel_fecha_inicio,
            "fecha_fin": SecRepTel_fecha_fin
        }

       

        $.ajax({
            url: "/sys/get_reportes_televentas.php",
            type: "POST",
            data: data,
            beforeSend: function () {
              loading("true");
            },
            complete: function () {
              loading();
            },
            success: function(resp) {;
                  
                let obj = JSON.parse(resp);
                window.open(obj.path);
                loading(false);
          },
          error: function(resp, status) {
      
          }
          });

    });


});

function sec_reportes_televentas_clientes() {

    if (sec_id == 'reportes' && sub_sec_id == 'televentas_clientes') {
        $('#SecRepTel_fecha_inicio').val($('#g_fecha_actual').val());
        $('#SecRepTel_fecha_fin').val($('#g_fecha_actual').val());
        
    }
}

function btn_excel_rpt_tlv_cli() {

    var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
    var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
 
	document.getElementById('btn_excel_tlv_cli').innerHTML = '<a onclick="btn_excel_rpt_tlv_cli()" href="export.php?export=reporte_tlv_clientes&amp;type=lista&amp;SecRepTel_fecha_inicio='+SecRepTel_fecha_inicio+'&amp;SecRepTel_fecha_fin='+SecRepTel_fecha_fin+'" class="btn btn-success btn-block export_list_btn" download="reporte_tlv_clientes.xls"><span class="glyphicon glyphicon-download-alt"></span> EXPORTAR</a>';

}

function limpiar_fncRenderIdSecRepTelCli_tabla_transacciones_v2() {
 
    $("#SecRepTel_deposito_cant").val("0");
    $("#SecRepTel_bono_cant").val("0");
    $("#SecRepTel_recarga_cant").val("0");
    $("#SecRepTel_monto_total").val("0.00");
    $("#SecRepTel_bono_total").val("0.00");
    $("#SecRepTel_recarga_total").val("0.00");
}

function fncRenderIdSecRepTelCli_tabla_transacciones_v2() { 

    var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
    var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
 
    if (SecRepTel_fecha_inicio.length !== 10) {
        $("#SecRepTel_fecha_inicio").focus();
        return false;
    }
    if (SecRepTel_fecha_fin.length !== 10) {
        $("#SecRepTel_fecha_fin").focus();
        return false;
    }

    var data = {
        "accion": "listar_resumen_x_cliente",
        "fecha_inicio": SecRepTel_fecha_inicio,
        "fecha_fin": SecRepTel_fecha_fin
    }


    auditoria_send({ proceso: "listar_resumen_x_cliente", data: data }); 

    var ftable = $('#SecRepTelCli_tabla_transacciones_v2').DataTable();
    ftable.clear();
    ftable.destroy();
    var ftable = $('#SecRepTelCli_tabla_transacciones_v2').DataTable({
        'destroy': true,
        'scrollX': true,
        "processing": true,
        "serverSide": true,
        "order" : [],
        "ajax": {
            type: "POST",
            async : true,
            "url": "/sys/get_reportes_televentas.php",
            "data": data
        },
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
            { 
                "data": null, 
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
               
                }
            },

            { "data": "fecha_hora_registro"},
            { "data": "usuario_created"},
			{ "data": "fecha_ultimo_movimiento" },
			{ "data": "hora_ultimo_movimiento" },
			{ "data": "local_nombre" },
			{ "data": "web_id" },
			{ "data": "telefono" },
			{ "data": "tipo_doc" },
			{ "data": "num_doc" },
			{ "data": "cliente" },
            { "data": "fecha_nacimiento" },
            { "data": "total_deposito" },
            { "data": "total_bono" },
            { "data": "total_recarga" },
            { "data": "cont_deposito" },
            { "data": "cont_bono" },
            { "data": "cont_recarga" },
            { "data": "balance" } 
        ]
    });
     
}

function fncRenderIdSecRepTelCli_tabla_transacciones_get_totales_v2() {
    limpiar_fncRenderIdSecRepTelCli_tabla_transacciones_v2();
    var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
    var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
    var data = {
        "accion": "listar_resumen_x_cliente_totales",
        "fecha_inicio": SecRepTel_fecha_inicio,
        "fecha_fin": SecRepTel_fecha_fin
    }

    auditoria_send({ "proceso": "listar_resumen_x_cliente", "data": data });
    $.ajax({
        async: true,
        url: "/sys/get_reportes_televentas.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) { //  alert(datat)
            var respuesta = JSON.parse(resp);
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                var monto_total = 0;
                var bono_total = 0;
                var recarga_total = 0;
                var monto_cant = 0;
                var bono_cant = 0;
                var recarga_cant = 0;
                if (respuesta.result.length > 0) {
                    $.each(respuesta.result, function (index, item) {
                        monto_total += parseFloat(item.total_deposito);
                        bono_total += parseFloat(item.total_bono);
                        recarga_total += parseFloat(item.total_recarga);
                        monto_cant += parseInt(item.cont_deposito);
                        bono_cant += parseInt(item.cont_bono);
                        recarga_cant += parseInt(item.cont_recarga);
                    });

                }
                $('#SecRepTel_deposito_cant').val(monto_cant);
                $('#SecRepTel_bono_cant').val(bono_cant);
                $('#SecRepTel_recarga_cant').val(recarga_cant);
                $('#SecRepTel_monto_total').val(parseFloat(monto_total).toFixed(2));
                $('#SecRepTel_bono_total').val(parseFloat(bono_total).toFixed(2));
                $('#SecRepTel_recarga_total').val(parseFloat(recarga_total).toFixed(2));
                //console.log(array_clientes);
                return false;
            }
        },
        error: function () { }
    });

}
