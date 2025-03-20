
$(function () {


    $('#SecRepTor_tipo_transaccion').select2();
    $('#SecRepTor_red').select2();
    $('#SecRepTor_cajero').select2();
    $('#SecRepTor_local').select2();
    $('#secBtn_estado').select2();

	$('#SecRepTor_fecha_inicio').datetimepicker({
		format: 'DD-MM-YYYY',
        locale: 'es'
	});
	$('#SecRepTor_fecha_fin').datetimepicker({
		format: 'DD-MM-YYYY',
        locale: 'es'
	});

	$('#SecRepTor_fecha_inicio').val($('#g_fecha_actual').val());
	$('#SecRepTor_fecha_fin').val($('#g_fecha_actual').val());

	$('#SecRepTor_fecha_inicio').change(function () {
		var var_fecha_change = $('#SecRepTor_fecha_inicio').val();
		if (!(parseInt(var_fecha_change.length) > 0)) {
			$("#SecRepTor_fecha_inicio").val($("#g_fecha_actual").val());
		}
	});
	$('#SecRepTor_fecha_fin').change(function () {
		var var_fecha_change = $('#SecRepTor_fecha_fin').val();
		if (!(parseInt(var_fecha_change.length) > 0)) {
			$("#SecRepTor_fecha_fin").val($("#g_fecha_actual").val());
		}
	});


    if (sec_id == 'reportes' && sub_sec_id=='torito') {
        $.each(list_locales, function (index, item) {
            $('#SecRepTor_local').append('<option value="'+item.cc_id+'">['+item.cc_id+'] '+item.local+'</option>');
        });
    }
    $('#SecRepTor_red').change(function () {
        console.log('entre al changer');
        $('#SecRepTor_local').empty();
        $('#SecRepTor_local').append('<option value="0">Todos</option>');
        var cod_red = $('#SecRepTor_red').val();
        console.log('cod_red: '+cod_red);
        if (cod_red > 0) {
            $.each(list_locales, function (index, item) {
                if(parseInt(cod_red) === parseInt(item.cod_red)){
                    $('#SecRepTor_local').append('<option value="'+item.cc_id+'">['+item.cc_id+'] '+item.local+'</option>');
                }
                if(parseInt(cod_red) === 1 && parseInt(item.cod_red) === 9){
                    $('#SecRepTor_local').append('<option value="'+item.cc_id+'">['+item.cc_id+'] '+item.local+'</option>');
                }
            });
        } else {
            $.each(list_locales, function (index, item) {
                $('#SecRepTor_local').append('<option value="'+item.cc_id+'">['+item.cc_id+'] '+item.local+'</option>');
            });
        }
    });
    $('#SecRepTor_red').click(function () {
        $("#SecRepTor_local").val(0);
        $('#SecRepTor_local').select2().trigger('change');
    });

    $('#SecRepTor_btn_buscar').click(function() {
        listar_SecRepTor_tabla_transacciones();
        return false;
    });

    $("#SecRepTor_btn_exportar").on('click', function () {

		var SecRepTor_fecha_inicio = $.trim($("#SecRepTor_fecha_inicio").val());
	    var SecRepTor_fecha_fin = $.trim($("#SecRepTor_fecha_fin").val());
	    var SecRepTor_tipo_transaccion = $("#SecRepTor_tipo_transaccion").val();
	    var SecRepTor_cajero = $("#SecRepTor_cajero").val();
        var SecRepTor_red = $("#SecRepTor_red").val();
        var SecRepTor_local = $("#SecRepTor_local").val();
        var SecRepTor_num_transaccion = $("#SecRepTor_num_transaccion").val();
        var secBtn_estado = $("#secBtn_estado").val();

	    var data = {
	        "accion": "listar_transacciones_export_xls",
	        "fecha_inicio": SecRepTor_fecha_inicio,
	        "fecha_fin": SecRepTor_fecha_fin,
	        "tipo_transaccion": SecRepTor_tipo_transaccion,
	        "cajero": SecRepTor_cajero,
            "red": SecRepTor_red,
            "local": SecRepTor_local,
            "num_transaccion": SecRepTor_num_transaccion,
            "estado": secBtn_estado
	    }

	    $.ajax({
	        url: "/sys/get_reportes_torito.php",
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

function sec_reportes_torito() {
    if (sec_id == 'reportes' && sub_sec_id=='torito') {
        $('#SecRepTor_fecha_inicio').val($('#g_fecha_actual').val());
        $('#SecRepTor_fecha_fin').val($('#g_fecha_actual').val());
    }
}



function limpiar_SecRepTor_tabla_transacciones() {
    var permiso_ver = $("#permiso_ver").val();
    $('#SecRepTor_tabla_transacciones').html(
        '<thead>' +
        '   <tr>' +
        '       <th class="text-center" width="5%">#</th>' +
        '       <th class="text-center" width="10%">FECHA</th>' +
        '       <th class="text-center" width="10%">HORA</th>' +
        '       <th class="text-center" width="5%">TIPO</th>' +
        '       <th class="text-center" width="5%">TRANSACCIÓN-ID</th>' +
        '       <th class="text-center" width="10%">NUM_DOC</th>' +
        '       <th class="text-center" width="15%">CLIENTE</th>' +
        '       <th class="text-center" width="5%">MONTO</th>' +
        '       <th class="text-center" width="10%">USUARIO</th>' +
        '       <th class="text-center" width="10%">RED</th>' +
        '       <th class="text-center" width="10%">LOCAL</th>' +
        '       <th class="text-center" width="10%">ESTADO</th>' +
        (permiso_ver == 1 
            ? '       <th class="text-center" width="10%">ACCIONES</th>' 
            : ''
        ) +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#SecRepTor_cant_depositos').val('0');
    $('#SecRepTor_cant_pagos').val('0');
    $('#SecRepTor_cant_recargas').val('0');
    $('#SecRepTor_total_ventas').val('0.00');
    $('#SecRepTor_total_pagos').val('0.00');
    $('#SecRepTor_total_recargas').val('0.00');
}

function listar_SecRepTor_tabla_transacciones() {
    limpiar_SecRepTor_tabla_transacciones();

    $(document).on('click', '.btn_change_estado_transaccion', function(e) {
        const id = e.currentTarget.dataset.idTransaccion;
        const status = e.currentTarget.dataset.statusTransaccion;
        let new_status = 0;
        let msg_confirm = '';
        let btn_msg_confirm = '';
        
        switch (status) {
            case "0":
                new_status = 1;
                msg_confirm = 'Se activará el estado de esta transacción.';
                btn_msg_confirm = 'Sí, activar';
                break;
            case "1":
                new_status = 0;
                msg_confirm = 'Se inactivará el estado de esta transacción.';
                btn_msg_confirm = 'Sí, desactivar';
                break;
            default:
                new_status = 0;
                break;
        }
        
        swal({
            title: "¿Estás seguro?",
            text: msg_confirm,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: btn_msg_confirm,
            cancelButtonText: "No, cancelar",
            closeOnConfirm: false,
            closeOnCancel: true,
        }, function (isConfirm) {
            if (isConfirm) {
                changeEstadoTransaccion(id, new_status, status);
            } else {
                
            }
        });
    });

    $(document).on('click', '.btn-historico-cambios-transaccion', function(e) {

        const id = e.currentTarget.dataset.idTransaccion;
        $('#modalTransaccionHistoricoCambios').modal('show');

        sec_transaccion_historico_torito(id);
    })

    function sec_transaccion_historico_torito(id){

        var data = {
            accion: "get_historico_transaccion_cambios",
            transaccion_id: id
        };
        $("#transaccion_historico_div_tabla").show();

        var columnDefs = [{
            className: 'text-center',
            targets: [0, 1, 2, 3, 4, 5]
        }];

        var tabla = crearDataTable(
            "#transaccion_historico_datatable",
            "/sys/get_reportes_torito.php",
            data,
            columnDefs
        );

         // Eliminar el campo de búsqueda
         tabla.on('init.dt', function () {
            $('.dataTables_filter').hide();
        });
        
    }
    


    var SecRepTor_fecha_inicio = $.trim($("#SecRepTor_fecha_inicio").val());
    var SecRepTor_fecha_fin = $.trim($("#SecRepTor_fecha_fin").val());
    var SecRepTor_tipo_transaccion = $("#SecRepTor_tipo_transaccion").val();
    var SecRepTor_cajero = $("#SecRepTor_cajero").val();
    var SecRepTor_red = $("#SecRepTor_red").val();
    var SecRepTor_local = $("#SecRepTor_local").val();
    var SecRepTor_num_transaccion = $("#SecRepTor_num_transaccion").val();
    var permiso_ver = $("#permiso_ver").val();
    var secBtn_estado = $("#secBtn_estado").val();

    if (SecRepTor_fecha_inicio.length !== 10) {
        $("#SecRepTor_fecha_inicio").focus();
        return false;
    }
    if (SecRepTor_fecha_fin.length !== 10) {
        $("#SecRepTor_fecha_fin").focus();
        return false;
    }

    var data = {
        "accion": "listar_transacciones",
        "fecha_inicio": SecRepTor_fecha_inicio,
        "fecha_fin": SecRepTor_fecha_fin,
        "tipo_transaccion": SecRepTor_tipo_transaccion,
        "cajero": SecRepTor_cajero,
        "red": SecRepTor_red,
        "local": SecRepTor_local,
        "num_transaccion": SecRepTor_num_transaccion,
        "estado": secBtn_estado,
    }

    auditoria_send({ "proceso": "listar_transacciones", "data": data });
    $.ajax({
        url: "/sys/get_reportes_torito.php",
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
                $('#SecRepTor_tabla_transacciones').append(
                    '<tr>' +
                    '<td class="text-center" colspan="11">No hay transacciones.</td>' +
                    '</tr>'
                );
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                var cant_depositos=0;
                var cant_pagos=0;
                var cant_recargas=0;
                var cant_bonos=0;
            	var total_deposito=0;
                var total_pagos=0;
            	var total_recargas=0;
                var total_bonos=0;
                var classbtn = "";
                var toggle = "";
                var title = "";

                if (respuesta.result.length > 0) {
                    $.each(respuesta.result, function(index, item) {
                        var tipo_torito='';
                        if(parseInt(item.cod_tipo_transaccion)===1){
                            cant_depositos++;
                            total_deposito+=parseFloat(item.monto);
                            tipo_torito='Venta GN';
                        }
                        if(parseInt(item.cod_tipo_transaccion)===2){
                            cant_pagos++;
                            total_pagos+=parseFloat(item.monto);
                            tipo_torito='Pago GN';
                        }
                        if(parseInt(item.cod_tipo_transaccion)===3){
                            cant_recargas++;
                            total_recargas+=parseFloat(item.monto);
                            tipo_torito='Recarga';
                        }
                        if(parseInt(item.cod_tipo_transaccion)===4){
                            cant_depositos++;
                            total_deposito+=parseFloat(item.monto);
                            tipo_torito='Venta MM';
                        }
                        if(parseInt(item.cod_tipo_transaccion)===5){
                            cant_pagos++;
                            total_pagos+=parseFloat(item.monto);
                            tipo_torito='Pago MM';
                        }
                        if(parseInt(item.cod_tipo_transaccion)===6){
                            cant_bonos++;
                            total_bonos+=parseFloat(item.monto);
                            tipo_torito='Promo Torito';
                        }
                        if(parseInt(item.cod_tipo_transaccion)===7){
                            tipo_torito='Promo Torito MM';
                        }
                        if(parseInt(item.cod_tipo_transaccion)===8){
                            cant_bonos++;
                            total_bonos+=parseFloat(item.monto);
                            tipo_torito='Canje Torito';
                        }
                        if(parseInt(item.cod_tipo_transaccion)===9){
                            tipo_torito='Canje Torito MM';
                        }
                        
                        if(item.status == 1){
                            classbtn = "success";
                            toggle = "fa-toggle-on";
                            title = "Inactivar ";
                            spanclass = "bg-success";
                            spantitle = "Activo";
                        }else if(item.status == 0){
                            classbtn = "danger";
                            toggle = "fa-toggle-off";
                            title = "Activar ";
                            spanclass = "bg-danger";
                            spantitle = "Inactivo";
                        }else{
                            classbtn = "info";
                        }

                        $('#SecRepTor_tabla_transacciones').append(
                            '<tr>' +
                            '<td class="text-center">' + (index+1) + '</td>' +
                            '<td class="text-center">' + item.fecha + '</td>' +
                            '<td class="text-center">' + item.hora + '</td>' +
                            '<td class="text-center">' + tipo_torito + '</td>' +
                            '<td class="text-center">' + item.hash + '</td>' +
                            '<td class="text-center">' + item.num_doc + '</td>' +
                            '<td class="text-center">' + item.cliente + '</td>' +
                            '<td class="text-center">' + item.monto + '</td>' +
                            '<td class="text-center" cod_cajero="'+item.cod_cajero+'">' + item.usuario + '</td>' +
                            '<td class="text-center">' + item.nombre_red + '</td>' +
                            '<td class="text-center">['+item.cod_local+'] ' + item.nombre_local + '</td>' +
                            '<td class="text-center"><span class="badge '+spanclass+'">'+spantitle+'</span></td>' +
                            (permiso_ver == 1 
                            ?   '<td class="text-center">' +
                                     '<button type="button" title="'+title+'transacción" class="btn btn-xs text-'+classbtn+' btn_change_estado_transaccion" data-id-transaccion="'+item.id+'"  data-status-transaccion="'+item.status+'" style="font-size: 23px; padding-left:0px; background-color: transparent;"><i class="fa '+toggle+'"></i></button>' +
                                    '<button class="btn btn-xs btn-warning btn-historico-cambios-transaccion" title="Ver historial" href="#" data-id-transaccion="'+item.id+'"><span class="fa fa-history"></span></button>'+
                                '</td>' 
                                    : ''
                            ) +
                            '</tr>'
                        );
                    });
                    DATATABLE_FORMATO_SecRepTor_tabla_transacciones('#SecRepTor_tabla_transacciones');
                } else {
                    $('#SecRepTor_tabla_transacciones').append(
                        '<tr>' +
                        '<td class="text-center" colspan="11">No hay transacciones.</td>' +
                        '</tr>'
                    );
                }
                $('#SecRepTor_cant_depositos').val(cant_depositos);
                $('#SecRepTor_cant_pagos').val(cant_pagos);
                $('#SecRepTor_cant_recargas').val(cant_recargas);
                $('#SecRepTor_cant_bonos').val(cant_bonos);
			    $('#SecRepTor_total_ventas').val(parseFloat(total_deposito).toFixed(2));
                $('#SecRepTor_total_pagos').val(parseFloat(total_pagos).toFixed(2));
			    $('#SecRepTor_total_recargas').val(parseFloat(total_recargas).toFixed(2));
                $('#SecRepTor_total_bonos').val(parseFloat(total_bonos).toFixed(2));
                //console.log(array_clientes);
                return false;
            }
        },
        error: function() {}
    });
}


function DATATABLE_FORMATO_SecRepTor_tabla_transacciones(id) {
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

function changeEstadoTransaccion(id, new_status, old_status) {
    const data = {
        set_status_transaccion: 'set_status_transaccion',
        transaccion_id: id,
        new_status: new_status,
        old_status: old_status,
    };

    $.ajax({
        url: "/sys/get_reportes_torito.php",
        type: "POST",
        data: data,
        success: function (response) {
            const respuesta = JSON.parse(response);
            console.log(respuesta);
            if (respuesta.status === 200) {
                swal("OK", respuesta.message, "success");
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else if (respuesta.status === 500) {
                swal("Error", respuesta.message, "warning");
            }
        },
        error: function () {
            swal("Error", "Ocurrió un problema con la solicitud. Inténtelo de nuevo.", "error");
        },
        complete: function () {
            loading();
        }
    });
}