$(function () {
    if (sec_id === 'reportes' && sub_sec_id === 'saldo_web') {
        $('#SecRepSalWeb_cajero').select2();
        $('#SecRepSalWeb_local').select2();
        $('#SecRepSalWeb_zona').select2();
        
    	$('#SecRepSalWeb_fecha_inicio').datepicker({
    		format: 'YYYY-MM-DD',
            language: 'es'
    	});
        //-------------------------------------------------------------------------------------------------------//
        // RESTRICCION DE 7 DIAS - solo para OPERACIONES (id=21)-CAJERO (5), y OPERACIONES (id=21)-SUPERVISOR (4)
        //-------------------------------------------------------------------------------------------------------//
        if( (anuncios_area_id==21 && anuncios_cargo_id==5) || (anuncios_area_id==21 && anuncios_cargo_id==4) ){
            $('#SecRepSalWeb_fecha_fin').datetimepicker({
                format: 'YYYY-MM-DD'
            });
        }else{
            $('#SecRepSalWeb_fecha_fin').datepicker({
                format: 'YYYY-MM-DD'
            });
        }

    	$('#SecRepSalWeb_fecha_inicio').val($('#g_fecha_actual').val());
    	$('#SecRepSalWeb_fecha_fin').val($('#g_fecha_actual').val());

    	$('#SecRepSalWeb_fecha_inicio').change(function () {
    		var var_fecha_change = $('#SecRepSalWeb_fecha_inicio').val();
    		if (!(parseInt(var_fecha_change.length) > 0)) {
    			$("#SecRepSalWeb_fecha_inicio").val($("#g_fecha_actual").val());
    		}
    	});
    	$('#SecRepSalWeb_fecha_fin').change(function () {
    		var var_fecha_change = $('#SecRepSalWeb_fecha_fin').val();
    		if (!(parseInt(var_fecha_change.length) > 0)) {
    			$("#SecRepSalWeb_fecha_fin").val($("#g_fecha_actual").val());
    		}
    	});
        $('#SecRepSalWeb_estado').val('1');


        $('#SecRepSalWeb_btn_buscar').click(function() {
            listar_SecRepSalWeb_tabla_transacciones();
        });

        $("#SecRepSalWeb_btn_exportar").on('click', function () {

    		var SecRepSalWeb_fecha_inicio = $.trim($("#SecRepSalWeb_fecha_inicio").val());
    	    var SecRepSalWeb_fecha_fin = $.trim($("#SecRepSalWeb_fecha_fin").val());
    	    var SecRepSalWeb_tipo_transaccion = $("#SecRepSalWeb_tipo_transaccion").val();
    	    var SecRepSalWeb_cajero = $("#SecRepSalWeb_cajero").val();
            var SecRepSalWeb_local = $("#SecRepSalWeb_local").val();
            var SecRepSalWeb_zona = $("#SecRepSalWeb_zona").val();
            var SecRepSalWeb_estado = $("#SecRepSalWeb_estado").val();

    	    var data = {
    	        "accion": "listar_transacciones_export_xls",
    	        "fecha_inicio": SecRepSalWeb_fecha_inicio,
    	        "fecha_fin": SecRepSalWeb_fecha_fin,
    	        "tipo_transaccion": SecRepSalWeb_tipo_transaccion,
    	        "cajero": SecRepSalWeb_cajero,
                "local": SecRepSalWeb_local,
                "zona": SecRepSalWeb_zona,
                "estado": SecRepSalWeb_estado
    	    }

    	    $.ajax({
    	        url: "/sys/get_reportes_saldo_web.php",
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

function sec_reportes_torito() {
    if (sec_id == 'reportes' && sub_sec_id=='torito') {
        $('#SecRepSalWeb_fecha_inicio').val($('#g_fecha_actual').val());
        $('#SecRepSalWeb_fecha_fin').val($('#g_fecha_actual').val());
        //listar_SecRepSalWeb_tabla_transacciones();
    }
}



function limpiar_SecRepSalWeb_tabla_transacciones() {
    $('#SecRepSalWeb_tabla_transacciones').html(
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
        '       <th class="text-center" width="5%">ESTADO</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#SecRepSalWeb_cant_depositos').val('0');
    $('#SecRepSalWeb_cant_retiros').val('0');
    $('#SecRepSalWeb_total_ventas').val('0.00');
    $('#SecRepSalWeb_total_retiros').val('0.00');
    $('#SecRepSalWeb_total_depositos_extornos').val('0.00');

}

function listar_SecRepSalWeb_tabla_transacciones() {
    limpiar_SecRepSalWeb_tabla_transacciones();

    var SecRepSalWeb_fecha_inicio = $.trim($("#SecRepSalWeb_fecha_inicio").val());
    var SecRepSalWeb_fecha_fin = $.trim($("#SecRepSalWeb_fecha_fin").val());
    var SecRepSalWeb_tipo_transaccion = $("#SecRepSalWeb_tipo_transaccion").val();
    var SecRepSalWeb_cajero = $("#SecRepSalWeb_cajero").val();
    var SecRepSalWeb_local = $("#SecRepSalWeb_local").val();
    var SecRepSalWeb_zona = $("#SecRepSalWeb_zona").val();
    var SecRepSalWeb_estado = $("#SecRepSalWeb_estado").val();

    if (SecRepSalWeb_fecha_inicio.length !== 10) {
        $("#SecRepSalWeb_fecha_inicio").focus();
        return false;
    }
    if (SecRepSalWeb_fecha_fin.length !== 10) {
        $("#SecRepSalWeb_fecha_fin").focus();
        return false;
    }
    //-------------------------------------------------------------------------------------------------------//
    // RESTRICCION DE 7 DIAS - solo para OPERACIONES (id=21)-CAJERO (5), y OPERACIONES (id=21)-SUPERVISOR (4)
    //-------------------------------------------------------------------------------------------------------//
    if( (anuncios_area_id==21 && anuncios_cargo_id==5) || (anuncios_area_id==21 && anuncios_cargo_id==4) ){
        let dfecha_inicio = new Date(SecRepSalWeb_fecha_inicio);
        let dfecha_fin = new Date(SecRepSalWeb_fecha_fin);
        let dfecha_inicio_limite = new Date(dfecha_fin);
        dfecha_inicio_limite.setDate(dfecha_inicio_limite.getDate() - 7 );
        //console.log(dfecha_inicio_limite);
        if(dfecha_inicio.getTime() < dfecha_inicio_limite.getTime()){
            alert("Se superó el máximo de 7 días");
            return false;
        }
    }
    //-------------------------------------------------------------------------------------------------------//
    var data = {
        "accion": "listar_transacciones",
        "fecha_inicio": SecRepSalWeb_fecha_inicio,
        "fecha_fin": SecRepSalWeb_fecha_fin,
        "tipo_transaccion": SecRepSalWeb_tipo_transaccion,
        "cajero": SecRepSalWeb_cajero,
        "local": SecRepSalWeb_local,
        "zona": SecRepSalWeb_zona,
        "estado": SecRepSalWeb_estado
    }

    auditoria_send({ "proceso": "listar_transacciones", "data": data });
    $.ajax({
        url: "/sys/get_reportes_saldo_web.php",
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
                if ($.fn.DataTable.isDataTable('#SecRepSalWeb_tabla_transacciones')) {
                    $('#SecRepSalWeb_tabla_transacciones').DataTable().destroy();
                }
                $('#SecRepSalWeb_tabla_transacciones').append(
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
                            if(parseInt(item.cod_tipo )=== 3){
                                total_extorno++;
                                total_deposito_extornos += parseFloat(item.monto);
                            }
                        }
                        var estado_color='';
                        if(parseInt(item.cod_estado)===0){
                            estado_color = ' style="color: red;" ';
                        }
                        $('#SecRepSalWeb_tabla_transacciones').append(
                            '<tr '+estado_color+'>' +
                            '<td class="text-center">' + (index+1) + '</td>' +
                            '<td class="text-center">' + item.registro + '</td>' +
                            '<td class="text-center">' + item.zona + '</td>' +
                            '<td class="text-center">' + item.tipo + '</td>' +
                            '<td class="text-center">' + item.monto + '</td>' +
                            '<td class="text-left">'+item.cod_cliente+' - ' + item.cliente + '</td>' +
                            '<td class="text-left" cod_cajero="'+item.cod_cajero+'">' + item.cajero + '</td>' +
                            '<td class="text-left" cod_local="'+item.cod_local+'">['+item.cc_id+'] ' + item.nombre_local + '</td>' +
                            '<td class="text-center">' + item.cod_txn + '</td>' +
                            '<td class="text-center">' + item.estado + '</td>' +
                            '</tr>'
                        );
                    });
                    DATATABLE_FORMATO_SecRepSalWeb_tabla_transacciones('#SecRepSalWeb_tabla_transacciones');
                } else {
                    $('#SecRepSalWeb_tabla_transacciones').append(
                        '<tr>' +
                        '<td class="text-center" colspan="10">No hay transacciones.</td>' +
                        '</tr>'
                    );
                }
                $('#SecRepSalWeb_cant_depositos').val(cant_depositos);
                $('#SecRepSalWeb_cant_retiros').val(cant_retiros);
			    $('#SecRepSalWeb_total_ventas').val(parseFloat(total_deposito).toFixed(2));
                $('#SecRepSalWeb_total_retiros').val(parseFloat(total_retiros).toFixed(2));
                $('#SecRepSalWeb_total_depositos_extornos').val(parseFloat(total_deposito_extornos).toFixed(2));
                //console.log(array_clientes);
                return false;
            }
        },
        error: function() {}
    });
}


function DATATABLE_FORMATO_SecRepSalWeb_tabla_transacciones(id) {
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