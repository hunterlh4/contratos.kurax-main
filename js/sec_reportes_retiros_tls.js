
$(function () {

    if (sec_id === 'reportes' && sub_sec_id === 'retiros_tls') {
        $('#SecRepRetTLS_cajero').select2();
        $('#SecRepRetTLS_local').select2();
        $('#SecRepRetTLS_zona').select2();

    	$('#SecRepRetTLS_fecha_inicio').datetimepicker({
    		format: 'YYYY-MM-DD'
    	});
    	$('#SecRepRetTLS_fecha_fin').datetimepicker({
    		format: 'YYYY-MM-DD'
    	});

    	$('#SecRepRetTLS_fecha_inicio').val($('#g_fecha_actual').val());
    	$('#SecRepRetTLS_fecha_fin').val($('#g_fecha_actual').val());

    	$('#SecRepRetTLS_fecha_inicio').change(function () {
    		var var_fecha_change = $('#SecRepRetTLS_fecha_inicio').val();
    		if (!(parseInt(var_fecha_change.length) > 0)) {
    			$("#SecRepRetTLS_fecha_inicio").val($("#g_fecha_actual").val());
    		}
    	});
    	$('#SecRepRetTLS_fecha_fin').change(function () {
    		var var_fecha_change = $('#SecRepRetTLS_fecha_fin').val();
    		if (!(parseInt(var_fecha_change.length) > 0)) {
    			$("#SecRepRetTLS_fecha_fin").val($("#g_fecha_actual").val());
    		}
    	});
        $('#SecRepRetTLS_estado').val('0');


        $('#SecRepRetTLS_btn_buscar').click(function() {
            listar_SecRepRetTLS_tabla_transacciones();
        });

        $("#SecRepRetTLS_btn_exportar").on('click', function () {

    		var SecRepRetTLS_fecha_inicio = $.trim($("#SecRepRetTLS_fecha_inicio").val());
    	    var SecRepRetTLS_fecha_fin = $.trim($("#SecRepRetTLS_fecha_fin").val());
            var SecRepRetTLS_local = $("#SecRepRetTLS_local").val();
            var SecRepRetTLS_zona = $("#SecRepRetTLS_zona").val();
            var SecRepRetTLS_estado = $("#SecRepRetTLS_estado").val();
            var SecRepRetTLS_ingresado = $('#SecRepRetTLS_ingreso_doc').val();

    	    var data = {
    	        "accion": "listar_transacciones_export_xls",
    	        "fecha_inicio": SecRepRetTLS_fecha_inicio,
    	        "fecha_fin": SecRepRetTLS_fecha_fin,
                "local": SecRepRetTLS_local,
                "zona": SecRepRetTLS_zona,
                "estado": SecRepRetTLS_estado,
                "ingreso" : SecRepRetTLS_ingresado
    	    }

    	    $.ajax({
    	        url: "/sys/get_reportes_retiros_tls.php",
    	        type: 'POST',
    	        data: data,
    	        beforeSend: function() {
    	            loading("true");
    	        },
    	        complete: function() {
    	            loading();
    	        },
    	        success: function(resp) {
    	            //console.log(resp);
    				let obj = JSON.parse(resp);
    				window.open(obj.path);
    				loading(false);
    	        },
    	        error: function() {}
    	    });
    	});
    }

});

function limpiar_SecRepRetTLS_tabla_transacciones() {
    $('#SecRepRetTLS_tabla_transacciones').html(
        '<thead>' +
        '   <tr>' +
        '       <th class="text-center" width="5%">#</th>' +
        '       <th class="text-center" width="15%">ID</th>' +
        '       <th class="text-center" width="10%">REGISTRO</th>' +
        '       <th class="text-center" width="10%">NRO DOCUMENTO</th>' +
        '       <th class="text-center" width="5%">TRANSACCION</th>' +
        '       <th class="text-center" width="15%">MONTO</th>' +
        '       <th class="text-center" width="10%">ESTADO</th>' +
        '       <th class="text-center" width="15%">DNI INGRESADO</th>' +
        '       <th class="text-center" width="10%">CECO</th>' +
        '       <th class="text-center" width="10%">ZONA</th>' +
        '       <th class="text-center" width="10%">LOCAL</th>' +
        '       <th class="text-center" width="10%">OBSERVACION</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#SecRepRetTLS_cant_depositos').val('0');
    $('#SecRepRetTLS_cant_retiros').val('0');
    $('#SecRepRetTLS_total_ventas').val('0.00');
    $('#SecRepRetTLS_total_retiros').val('0.00');
}

function listar_SecRepRetTLS_tabla_transacciones() {
    limpiar_SecRepRetTLS_tabla_transacciones();

    var SecRepRetTLS_fecha_inicio = $.trim($("#SecRepRetTLS_fecha_inicio").val());
    var SecRepRetTLS_fecha_fin = $.trim($("#SecRepRetTLS_fecha_fin").val());
    var SecRepRetTLS_local = $("#SecRepRetTLS_local").val();
    var SecRepRetTLS_zona = $("#SecRepRetTLS_zona").val();
    var SecRepRetTLS_estado = $("#SecRepRetTLS_estado").val();
    var SecRepRetTLS_ingresado = $('#SecRepRetTLS_ingreso_doc').val();

    if (SecRepRetTLS_fecha_inicio.length !== 10) {
        $("#SecRepRetTLS_fecha_inicio").focus();
        return false;
    }
    if (SecRepRetTLS_fecha_fin.length !== 10) {
        $("#SecRepRetTLS_fecha_fin").focus();
        return false;
    }

    var data = {
        "accion": "listar_transacciones",
        "fecha_inicio": SecRepRetTLS_fecha_inicio,
        "fecha_fin": SecRepRetTLS_fecha_fin,
        "local": SecRepRetTLS_local,
        "zona": SecRepRetTLS_zona,
        "estado": SecRepRetTLS_estado,
        "ingreso": SecRepRetTLS_ingresado
    }

    auditoria_send({ "proceso": "listar_transacciones", "data": data });
    $.ajax({
        url: "/sys/get_reportes_retiros_tls.php",
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
                if ($.fn.DataTable.isDataTable('#SecRepRetTLS_tabla_transacciones')) {
                    $('#SecRepRetTLS_tabla_transacciones').DataTable().destroy();
                }
                $('#SecRepRetTLS_tabla_transacciones').append(
                    '<tr>' +
                    '<td class="text-center" colspan="12">No hay transacciones.</td>' +
                    '</tr>'
                );
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                if (respuesta.result.length > 0) {
                    $.each(respuesta.result, function(index, item) {
                        $('#SecRepRetTLS_tabla_transacciones').append(
                            '<tr>' +
                            '<td class="text-center">' + (index+1) + '</td>' +
                            '<td class="text-center">' + item.id + '</td>' +
                            '<td class="text-center">' + item.registro + '</td>' +
                            '<td class="text-center">' + item.num_doc + '</td>' +
                            '<td class="text-center">' + item.cod_transaccion + '</td>' +
                            '<td class="text-left">' + item.monto + '</td>' +
                            '<td class="text-left">' + item.estado + '</td>' +
                            '<td class="text-left">' + item.dni_ingreso + '</td>' +
                            '<td class="text-center">' + item.cc_id + '</td>' +
                            '<td class="text-center">' + item.zona + '</td>' +
                            '<td class="text-center">' + item.local + '</td>' +
                            '<td class="text-center">' + item.observacion_scan_doc + '</td>' +
                            '</tr>'
                        );
                    });
                    DATATABLE_FORMATO_SecRepRetTLS_tabla_transacciones('#SecRepRetTLS_tabla_transacciones');
                } else {
                    $('#SecRepRetTLS_tabla_transacciones').append(
                        '<tr>' +
                        '<td class="text-center" colspan="12">No hay transacciones.</td>' +
                        '</tr>'
                    );
                }
                return false;
            }
        },
        error: function() {}
    });
}


function DATATABLE_FORMATO_SecRepRetTLS_tabla_transacciones(id) {
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