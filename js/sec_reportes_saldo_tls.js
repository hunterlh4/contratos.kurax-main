
$(function () {

    if (sec_id === 'reportes' && sub_sec_id === 'saldo_tls') {
        $('#SecRepSalTLS_cajero').select2();
        $('#SecRepSalTLS_local').select2();
        $('#SecRepSalTLS_zona').select2();

    	$('#SecRepSalTLS_fecha_inicio').datetimepicker({
    		format: 'YYYY-MM-DD'
    	});
    	$('#SecRepSalTLS_fecha_fin').datetimepicker({
    		format: 'YYYY-MM-DD'
    	});

    	$('#SecRepSalTLS_fecha_inicio').val($('#g_fecha_actual').val());
    	$('#SecRepSalTLS_fecha_fin').val($('#g_fecha_actual').val());

    	$('#SecRepSalTLS_fecha_inicio').change(function () {
    		var var_fecha_change = $('#SecRepSalTLS_fecha_inicio').val();
    		if (!(parseInt(var_fecha_change.length) > 0)) {
    			$("#SecRepSalTLS_fecha_inicio").val($("#g_fecha_actual").val());
    		}
    	});
    	$('#SecRepSalTLS_fecha_fin').change(function () {
    		var var_fecha_change = $('#SecRepSalTLS_fecha_fin').val();
    		if (!(parseInt(var_fecha_change.length) > 0)) {
    			$("#SecRepSalTLS_fecha_fin").val($("#g_fecha_actual").val());
    		}
    	});
        $('#SecRepSalTLS_estado').val('0');


        $('#SecRepSalTLS_btn_buscar').click(function() {
            listar_SecRepSalTLS_tabla_transacciones();
        });

        $("#SecRepSalTLS_btn_exportar").on('click', function () {

    		var SecRepSalTLS_fecha_inicio = $.trim($("#SecRepSalTLS_fecha_inicio").val());
    	    var SecRepSalTLS_fecha_fin = $.trim($("#SecRepSalTLS_fecha_fin").val());
    	    var SecRepSalTLS_tipo_transaccion = $("#SecRepSalTLS_tipo_transaccion").val();
    	    var SecRepSalTLS_cajero = $("#SecRepSalTLS_cajero").val();
            var SecRepSalTLS_local = $("#SecRepSalTLS_local").val();
            var SecRepSalTLS_zona = $("#SecRepSalTLS_zona").val();
            var SecRepSalTLS_estado = $("#SecRepSalTLS_estado").val();

    	    var data = {
    	        "accion": "listar_transacciones_export_xls",
    	        "fecha_inicio": SecRepSalTLS_fecha_inicio,
    	        "fecha_fin": SecRepSalTLS_fecha_fin,
    	        "tipo_transaccion": SecRepSalTLS_tipo_transaccion,
    	        "cajero": SecRepSalTLS_cajero,
                "local": SecRepSalTLS_local,
                "zona": SecRepSalTLS_zona,
                "estado": SecRepSalTLS_estado
    	    }

    	    $.ajax({
    	        url: "/sys/get_reportes_saldo_tls.php",
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
    }

});

function limpiar_SecRepSalTLS_tabla_transacciones() {
    $('#SecRepSalTLS_tabla_transacciones').html(
        '<thead>' +
        '   <tr>' +
        '       <th class="text-center" width="5%">#</th>' +
        '       <th class="text-center" width="15%">REGISTRO</th>' +
        '       <th class="text-center" width="10%">ZONA</th>' +
        '       <th class="text-center" width="10%">TIPO</th>' +
        '       <th class="text-center" width="5%">MONTO</th>' +
        '       <th class="text-center" width="15%">CLIENTE</th>' +
        '       <th class="text-center" width="10%">CAJERO</th>' +
        '       <th class="text-center" width="15%">LOCAL</th>' +
        '       <th class="text-center" width="10%">TRANSACCIÓN-ID</th>' +
        '       <th class="text-center" width="10%">ESTADO</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#SecRepSalTLS_cant_depositos').val('0');
    $('#SecRepSalTLS_cant_retiros').val('0');
    $('#SecRepSalTLS_total_ventas').val('0.00');
    $('#SecRepSalTLS_total_retiros').val('0.00');
}

function listar_SecRepSalTLS_tabla_transacciones() {
    limpiar_SecRepSalTLS_tabla_transacciones();

    var SecRepSalTLS_fecha_inicio = $.trim($("#SecRepSalTLS_fecha_inicio").val());
    var SecRepSalTLS_fecha_fin = $.trim($("#SecRepSalTLS_fecha_fin").val());
    var SecRepSalTLS_tipo_transaccion = $("#SecRepSalTLS_tipo_transaccion").val();
    var SecRepSalTLS_cajero = $("#SecRepSalTLS_cajero").val();
    var SecRepSalTLS_local = $("#SecRepSalTLS_local").val();
    var SecRepSalTLS_zona = $("#SecRepSalTLS_zona").val();
    var SecRepSalTLS_estado = $("#SecRepSalTLS_estado").val();

    if (SecRepSalTLS_fecha_inicio.length !== 10) {
        $("#SecRepSalTLS_fecha_inicio").focus();
        return false;
    }
    if (SecRepSalTLS_fecha_fin.length !== 10) {
        $("#SecRepSalTLS_fecha_fin").focus();
        return false;
    }

    var data = {
        "accion": "listar_transacciones",
        "fecha_inicio": SecRepSalTLS_fecha_inicio,
        "fecha_fin": SecRepSalTLS_fecha_fin,
        "tipo_transaccion": SecRepSalTLS_tipo_transaccion,
        "cajero": SecRepSalTLS_cajero,
        "local": SecRepSalTLS_local,
        "zona": SecRepSalTLS_zona,
        "estado": SecRepSalTLS_estado
    }

    auditoria_send({ "proceso": "listar_transacciones", "data": data });
    $.ajax({
        url: "/sys/get_reportes_saldo_tls.php",
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
                swal({
                    title: "Error",
                    text: respuesta.status,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                    });
                return false;
            }

            if (parseInt(respuesta.http_code) == 300) {
                if ($.fn.DataTable.isDataTable('#SecRepSalTLS_tabla_transacciones')) {
                    $('#SecRepSalTLS_tabla_transacciones').DataTable().destroy();
                }
                $('#SecRepSalTLS_tabla_transacciones').append(
                    '<tr>' +
                    '<td class="text-center" colspan="10">No hay transacciones.</td>' +
                    '</tr>'
                );
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                var cant_depositos=0;
                var cant_retiros=0;
            	var total_deposito=0;
                var total_retiros=0;
                var total_extorno = 0;
                var total_deposito_extornos = 0;
                if (respuesta.result.length > 0) {
                    $.each(respuesta.result, function(index, item) {
                        if(parseInt(item.cod_estado)===1){
                            if(parseInt(item.cod_tipo)===1){
                                cant_depositos++;
                                total_deposito+=parseFloat(item.monto);
                            }
                            if(parseInt(item.cod_tipo)===2){
                                cant_retiros++;
                                total_retiros+=parseFloat(item.monto);
                            }
                        }
                        var estado_color='';
                        if(parseInt(item.cod_estado)===0){
                            estado_color = ' style="color: red;" ';
                        }
                        $('#SecRepSalTLS_tabla_transacciones').append(
                            '<tr '+estado_color+'>' +
                            '<td class="text-center">' + (index+1) + '</td>' +
                            '<td class="text-center">' + item.registro + '</td>' +
                            '<td class="text-center">' + item.zona + '</td>' +
                            '<td class="text-center">' + item.tipo + '</td>' +
                            '<td class="text-center">' + item.monto + '</td>' +
                            '<td class="text-left">' + item.cliente + '</td>' +
                            '<td class="text-left" cod_cajero="'+item.cod_cajero+'">' + item.cajero + '</td>' +
                            '<td class="text-left" cod_local="'+item.cod_local+'">' + item.local + '</td>' +
                            '<td class="text-center">' + item.cod_txn + '</td>' +
                            '<td class="text-center">' + item.estado + '</td>' +
                            '</tr>'
                        );
                    });
                    DATATABLE_FORMATO_SecRepSalTLS_tabla_transacciones('#SecRepSalTLS_tabla_transacciones');
                } else {
                    $('#SecRepSalTLS_tabla_transacciones').append(
                        '<tr>' +
                        '<td class="text-center" colspan="10">No hay transacciones.</td>' +
                        '</tr>'
                    );
                }
                $('#SecRepSalTLS_cant_depositos').val(cant_depositos);
                $('#SecRepSalTLS_cant_retiros').val(cant_retiros);
			    $('#SecRepSalTLS_total_ventas').val(parseFloat(total_deposito).toFixed(2));
                $('#SecRepSalTLS_total_retiros').val(parseFloat(total_retiros).toFixed(2));
                return false;
            }
        },
        error: function() {}
    });
}


function DATATABLE_FORMATO_SecRepSalTLS_tabla_transacciones(id) {
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