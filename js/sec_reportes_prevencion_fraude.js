function sec_reportes_prevencion_fraude() {
	if (sub_sec_id === 'prevencion_fraude') {
		$('#sec_rpt_pf_buscador_fecha_inicio').datetimepicker({ format: 'YYYY-MM-DD' });
		$('#sec_rpt_pf_buscador_fecha_fin').datetimepicker({ format: 'YYYY-MM-DD' });

		$('#sec_rpt_pf_buscador_fecha_inicio').val($('#sec_rpt_pf_g_fecha_actual').val());
        $('#sec_rpt_pf_buscador_fecha_fin').val($('#sec_rpt_pf_g_fecha_actual').val());

        $('#sec_rpt_pf_buscador_fecha_inicio').change(function() {
            var var_fecha_change = $('#sec_rpt_pf_buscador_fecha_inicio').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#sec_rpt_pf_buscador_fecha_inicio").val($("#sec_rpt_pf_g_fecha_actual").val());
            }
        });
        $('#sec_rpt_pf_buscador_fecha_fin').change(function() {
            var var_fecha_change = $('#sec_rpt_pf_buscador_fecha_fin').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#sec_rpt_pf_buscador_fecha_fin").val($("#sec_rpt_pf_g_fecha_actual").val());
            }
        });
        sec_rpt_pf_listar_cuentas(0);
        sec_rpt_pf_listar_movimientos(0);
        $('#sec_rpt_pf_buscador_tipo').select2();
        $('#sec_rpt_pf_buscador_usuario').select2();
        $('#sec_rpt_pf_buscador_cuenta').select2();
        $('#sec_rpt_pf_buscador_movimiento').select2();
	}
}

function sec_rpt_fv_limpiar_tabla_registros(){
	$('#sec_rpt_pf_tabla_registros').html(
        '<thead>' +
        '   <tr>' +
        '       <th>Fecha</th>' +
        '       <th>Hora</th>' +
        '       <th>Fecha Abono</th>' +
        '       <th>Hora Abono</th>' +
        '       <th>Fecha Filtro</th>' +
        '       <th>Hora Filtro</th>' +
        '       <th>Caja</th>' +
        '       <th>Promotor</th>' +
        '       <th>Validador</th>' +
        '       <th>DNI</th>' +
        '       <th>Cliente</th>' +
        '       <th>Titular Abono</th>' +
        '       <th>Tipo</th>' +
        '       <th>Movimiento</th>' +
        '       <th>Banco de Pago</th>' +
        '       <th>Banco</th>' +
        '       <th>Tipo Constancia</th>' +
        '       <th>Banco TS</th>' +
        '       <th>New OP</th>' +
        '       <th>Cuenta</th>' +
        '       <th>CCI</th>' +
        '       <th>Estado</th>' +
        '       <th>Nro Operación</th>' +
        '       <th>Cod Operación</th>' +
        '       <th>Depósito</th>' +
        '       <th>Importe</th>' +
        '       <th>Comisión</th>' +
        '       <th>Observación</th>' +
        '       <th>Monto > S/ 500</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
}

function sec_rpt_pf_listar_registros(){
    sec_rpt_fv_limpiar_tabla_registros();

    var fecha_inicio = $("#sec_rpt_pf_buscador_fecha_inicio").val();
    var fecha_fin = $("#sec_rpt_pf_buscador_fecha_fin").val();
    var tipo = $("#sec_rpt_pf_buscador_tipo").val();
    var cuenta = $("#sec_rpt_pf_buscador_cuenta").val();
    var usuario = $("#sec_rpt_pf_buscador_usuario").val();
    var movimiento = $("#sec_rpt_pf_buscador_movimiento").val();

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menor a la fecha fin.', 'warning');
        return false;
    }
    var data = {
        "accion": "sec_rpt_fv_listar_registros",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "tipo": tipo,
        "usuario" : usuario,
        "cuenta" : cuenta,
        "movimiento" : movimiento
    }
    auditoria_send({ "proceso": "sec_rpt_fv_listar_registros", "data": data });
    $.ajax({
        url: "/sys/get_reportes_prevencion_fraude.php",
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
                //swal('Aviso', respuesta.status, 'warning');
                $('#sec_rpt_pf_tabla_registros').append(
                    '<tr>' +
                    '<td class="text-center" colspan="19">No hay registros</td>' +
                    '</tr>'
                );
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.result, function(index, item) {
                    var signo_retiro = "";
                    if ( (item.tipo_id) == 9 || (item.tipo_id) == 21 ){
                        signo_retiro = ' - ';
                    }
                    $('#sec_rpt_pf_tabla_registros').append(
                    	'<tr>' +
                        '<td class="text-center">' + item.fecha_registro + '</td>' +
                        '<td class="text-center">' + item.hora_registro + '</td>' +
                        '<td class="text-center">' + item.fecha_abono + '</td>' +
                        '<td class="text-center">' + item.hora_abono + '</td>' +
                        '<td class="text-center">' + item.fecha_filtro + '</td>' +
                        '<td class="text-center">' + item.hora_filtro + '</td>' +
                        '<td class="text-center">' + item.turno_local + '</td>' +
                        '<td class="text-center">' + item.cajero + '</td>' +
                        '<td class="text-center">' + item.validador_nombre + '</td>' +
                        '<td class="text-center">' + item.num_doc + '</td>' +
                        '<td class="text-right">' + item.cliente + '</td>' +
                        '<td class="text-right">' + item.titular_abono + '</td>' +
                        '<td class="text-center">' + item.tipo_transaccion + '</td>' +
                        '<td class="text-center">' + item.movimiento + '</td>' +
                        '<td class="text-center">' + item.Banco_pago + '</td>' +
                        '<td class="text-center">' + item.Banco + '</td>' +
                        '<td class="text-center">' + item.tipo_constancia + '</td>' +
                        '<td class="text-center">' + item.banco_ts + '</td>' +
                        '<td class="text-center">' + item.sigla_cuenta + '</td>' +
                        '<td class="text-center">' + item.cuenta + '</td>' +
                        '<td class="text-right">' + item.cci + '</td>' +
                        '<td class="text-right">' + item.estado + '</td>' +
                        '<td class="text-right">' + item.num_operacion + '</td>' +
                        '<td class="text-right">' + item.cod_operacion + '</td>' +
                        '<td class="text-right">' + item.monto_deposito + '</td>' +
                        '<td class="text-right"><b>' + signo_retiro + item.monto + '<b></td>' +
                        '<td class="text-right">' + item.comision_monto + '</td>' +
                        '<td class="text-right">' + item.observacion_validador + '</td>' +
                        '<td class="text-center">' + item.si_monto_mayor + '</td>' +
                        '</tr>'
                    );
                });

                sec_rpt_pf_tabla_registros_datatable_formato('#sec_rpt_pf_tabla_registros');
                sec_rpt_pf_listar_totales_registros();
                //return false;
            }         
        },
        error: function() {}
    });
}

function sec_rpt_pf_listar_totales_registros(){
    var fecha_inicio = $("#sec_rpt_pf_buscador_fecha_inicio").val();
    var fecha_fin = $("#sec_rpt_pf_buscador_fecha_fin").val();
    var tipo = $("#sec_rpt_pf_buscador_tipo").val();
    var cuenta = $("#sec_rpt_pf_buscador_cuenta").val();
    var usuario = $("#sec_rpt_pf_buscador_usuario").val();
    var movimiento = $("#sec_rpt_pf_buscador_movimiento").val();

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menor a la fecha fin.', 'warning');
        return false;
    }
    var data = {
        "accion": "sec_rpt_fv_listar_totales_registros",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "tipo": tipo,
        "usuario" : usuario,
        "cuenta" : cuenta,
        "movimiento" : movimiento
    }
    auditoria_send({ "proceso": "sec_rpt_fv_listar_totales_registros", "data": data });
    $.ajax({
        url: "/sys/get_reportes_prevencion_fraude.php",
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
                $('#sec_rpt_pf_cant_ingresos').val('0.00');
                $('#sec_rpt_pf_total_ingresos').val('0.00');
                $('#sec_rpt_pf_cant_salidas').val('0.00');
                $('#sec_rpt_pf_total_salidas').val('0.00');
                $('#sec_rpt_pf_total').val('0.00');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.result, function(index, item) {
                    item.total_ingresos = item.total_ingresos.replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    item.total_salidas = item.total_salidas.toString().replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    item.total = item.total.toString().replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                    $('#sec_rpt_pf_cant_ingresos').val(item.cantidad_ingresos);
                    $('#sec_rpt_pf_total_ingresos').val(item.total_ingresos);
                    $('#sec_rpt_pf_cant_salidas').val(item.cantidad_salidas);
                    $('#sec_rpt_pf_total_salidas').val(item.total_salidas);
                    $('#sec_rpt_pf_total').val(item.total);
                });
            }         
        },
        error: function() {}
    });
}

function sec_rpt_pf_tabla_registros_datatable_formato(id) {
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
    }
    $(id).DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'order': [[0, "desc"]],
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

function sec_rpt_pf_exportar_registros(){
    var fecha_inicio = $("#sec_rpt_pf_buscador_fecha_inicio").val();
    var fecha_fin = $("#sec_rpt_pf_buscador_fecha_fin").val();
    var tipo = $("#sec_rpt_pf_buscador_tipo").val();
    var cuenta = $("#sec_rpt_pf_buscador_cuenta").val();
    var usuario = $("#sec_rpt_pf_buscador_usuario").val();
    var movimiento = $("#sec_rpt_pf_buscador_movimiento").val();

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menor a la fecha fin.', 'warning');
        return false;
    }

    var data = {
        "accion": "sec_rpt_fv_exportar_registros_xls",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "tipo": tipo,
        "usuario" : usuario,
        "cuenta" : cuenta,
        "movimiento" : movimiento
    }
    $.ajax({
        url: "/sys/get_reportes_prevencion_fraude.php",
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
        error: function() {}
    });
}

function sec_rpt_fv_limpiar_select_usuario(){
	$('#sec_rpt_pf_buscador_usuario').html("");
	$('#sec_rpt_pf_buscador_usuario').append('<option value="0">TODOS</option>');
}

$("#sec_rpt_pf_buscador_tipo").change(function () {
	sec_rpt_fv_limpiar_select_usuario();
	var tipo = $("#sec_rpt_pf_buscador_tipo").val();
	var accion = '';
    sec_rpt_pf_listar_cuentas(tipo);
    sec_rpt_pf_listar_movimientos(tipo);

	if(tipo == 1){
		$('#sec_rpt_pf_lbl_usuario').html('Validador');
        $('#sec_rpt_pf_lbl_cuenta').html('Cuenta');
		accion = 'sec_rpt_fv_listar_usuarios_validador';
	}else if(tipo == 9){
		$('#sec_rpt_pf_lbl_usuario').html('Pagador');
        $('#sec_rpt_pf_lbl_cuenta').html('Banco');
		accion = 'sec_rpt_fv_listar_usuarios_pagador';
	}else{	
		$('#sec_rpt_pf_lbl_usuario').html('Usuario');
	}

	if(accion != ''){
	    var data = {
	        "accion": accion
	    }
	    auditoria_send({ "proceso": accion, "data": data });
	    $.ajax({
	        url: "/sys/get_reportes_prevencion_fraude.php",
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
	                swal('Aviso', respuesta.status, 'warning');
	                sec_rpt_fv_limpiar_select_usuario();
	                return false;
	            }
	            if (parseInt(respuesta.http_code) == 200) {
	                $.each(respuesta.result, function(index, item) {
	                    $('#sec_rpt_pf_buscador_usuario').append(
	                    	'<option value="' + item.id + '">[' + item.usuario + '] ' + item.nombre_cajero + '</option>'
	                    );
	                });
	                return false;
	            }         
	        },
	        error: function() {}
	    });
	}
});


function sec_rpt_pf_activar_desactivar_usarios_cuentas_apt(params) {
    $.ajax({
        url: "/sys/get_usuario_cuenta_apt.php",
        type: 'POST',
        data: params,
        async: true,
        success: function(resp) { //  alert(datat)
            console.log(resp);
            var respuesta = JSON.parse(resp);
           // console.log(respuesta);
                 
        },        
        error: function() {}
    });
}

function sec_rpt_pf_cargar_cargar_multiple_select(){
    $('#sec_rpt_pf_buscador_cuenta').multiselect({
           
        buttonClass:'form-control',
        buttonWidth: '100%',
        includeSelectAllOption: true, 
        onSelectAll: function(options) {
            $.each(options, function(index, item) {
                //console.log(item[0].value);
                /*data = {
                    'accion': 'crear_usuario_cuentas_apt',
                    'id_cuenta_apt': item[0].value,
                    'activar':1
                } 
                sec_rpt_pf_activar_desactivar_usarios_cuentas_apt(data);*/
            });
        }, 
        onDeselectAll: function(options) {
            $.each(options, function(index, item) {
                //console.log(item[0].value);
                /*data = {
                    'accion': 'crear_usuario_cuentas_apt',
                    'id_cuenta_apt': item[0].value,
                    'activar':0
                } 
                sec_rpt_pf_activar_desactivar_usarios_cuentas_apt(data);*/
            });
        },            
        onChange: function(element, checked) {

            var activar = 0;
            var id_cuenta =0;
            if (checked === true) {
                activar =1;
                id_cuenta=element.val();
                
            }
            else if (checked === false) {
                activar =0;
                id_cuenta=element.val();          
            }
            /*data = {
                'accion': 'crear_usuario_cuentas_apt',
                'id_cuenta_apt': id_cuenta,
                'activar':activar
            }               
            
            sec_rpt_pf_activar_desactivar_usarios_cuentas_apt(data);*/
        }
    });
}

function sec_rpt_pf_listar_cuentas(tipo){
    var accion = '';
    if(tipo == 1){
        accion = 'sec_rpt_pf_obtener_listado_cuentas_validador';
    }else if (tipo == 9){
        accion = 'sec_rpt_pf_obtener_listado_cuentas_pagador';
    }
    $('#sec_rpt_pf_buscador_cuenta').html('');
    if(accion != ''){

        var data ={
            "accion": accion
        }
        $.ajax({
            url: "/sys/get_reportes_prevencion_fraude.php",
            type: 'POST',
            data: data,
            success: function(resp) {
                var respuesta = JSON.parse(resp);
                $('#sec_rpt_pf_buscador_cuenta').append( '<option value="0" selected>TODOS</option>' );
                $.each(respuesta.result, function(index, item) {
                    var seleccionado ="";
                    if (item.activos!=0){seleccionado = "selected";}
                    if(item.id != 1){
                        $('#sec_rpt_pf_buscador_cuenta').append(                    
                            '<option value="' + item.id +'">' +item.cuenta_descripcion+'</option>'                                 
                        );
                    } 
                });         
            },
            complete: function() {
                //sec_rpt_pf_cargar_cargar_multiple_select();
            },
            error: function() {}
        });
    }else{
        $('#sec_rpt_pf_buscador_cuenta').append( '<option value="0" selected>TODOS</option>' );
        //sec_rpt_pf_cargar_cargar_multiple_select();
    }
}

function sec_rpt_pf_listar_movimientos(tipo){
    var data ={
        "accion": "sec_rpt_pf_obtener_listado_movimientos",
        "tipo" : tipo
    }
    $.ajax({
        url: "/sys/get_reportes_prevencion_fraude.php",
        type: 'POST',
        data: data,
        success: function(resp) {
            $('#sec_rpt_pf_buscador_movimiento').html('');
            var respuesta = JSON.parse(resp);
            $('#sec_rpt_pf_buscador_movimiento').append( '<option value="0" selected>TODOS</option>' );
            $.each(respuesta.result, function(index, item) {
                $('#sec_rpt_pf_buscador_movimiento').append(                    
                    '<option value="' + item.id +'">' +item.nombre+'</option>'                                 
                );
            });         
        },
        complete: function() {
        },
        error: function() {}
    });
}