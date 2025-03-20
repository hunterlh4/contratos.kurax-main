
$("input[type='text']").on("click", function() {
    $(this).select();
});
$("input").focus(function() {
    $(this).select();
});
$("input").focusin(function() {
    $(this).select();
});





$('#sec_con_serv_pub_check_cajachica_id_detalle').on('click', function() {
	console.log('ceck')
    if( $(this).is(':checked') ){
        //Mostrar cuadro para ingresar nombre de persona a la que se le está pagando
        $('#block-caja-chica').removeClass('ocultar_div');
        $('#sec_con_serv_pub_nombre_pagar').val('');
		$('#btn_nombre_guardar_modal').html('Caja Chica');

    } else {
        // Ocultar cuadro
        $('#block-caja-chica').addClass('ocultar_div');
        $('#sec_con_serv_pub_nombre_pagar').val('');
		$('#btn_nombre_guardar_modal').html('Validar');
    }
});


$('.monto').mask('00,000.00', {reverse: true});

function sec_contrato_detalle_servicio_publico(){

	obtener_observaciones_servicio_publico();

	$('.servicio_publico_datepicker').datepicker({
		dateFormat:'dd-mm-yy',
		changeMonth: true,
		changeYear: true
	});

	$('.servicio_publico_fecha_emision_datepicker').datepicker({
		dateFormat:'dd-mm-yy',
		changeMonth: true,
		changeYear: true
	}).on("change", function(ev) {
		$(this).datepicker('hide');
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
		limpiar_select_periodo();
	});

}

function sec_contrato_detalle_servicio_publico_guardar_observaciones() {
	
	var observacion = $('#servicio_publico_observaciones').val();
	var id_recibo = $('#sec_con_serv_pub_id_recibo').val();
	if (observacion == "") {
		alertify.error('Información: Ingrese una observación',10);
		$('#servicio_publico_observaciones').focus();
		return false;
	}

	var data = {
		"accion": "guardar_observaciones",
		"id_recibo": id_recibo,
		"observacion": observacion,
	}
	auditoria_send({ "proceso": "guardar_observaciones", "data": data });
    $.ajax({
        url: "sys/set_contrato_servicio_publico.php",
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
			if (respuesta.http_code == 200) {
				 $('#servicio_publico_observaciones').val('');
				obtener_observaciones_servicio_publico();
			}
			
        },
        error: function() {}
    });
}


function obtener_observaciones_servicio_publico() {
	var id_recibo = $('#sec_con_serv_pub_id_recibo').val();
	var data = {
		"accion": "obtener_observaciones_servicio_publico",
		"id_recibo": id_recibo,
	}
	auditoria_send({ "proceso": "obtener_observaciones_servicio_publico", "data": data });
    $.ajax({
        url: "sys/get_contrato_servicio_publico.php",
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
			if (respuesta.http_code == 200) {
				$('#div_observaciones').html(respuesta.result);
			}else{
				$('#div_observaciones').html('');
			}
			
        },
        error: function() {}
    });
}



function sec_contrato_detalle_guardar_recibo(){
	var id_archivo = $('#sec_con_serv_pub_id_recibo').val();
	var monto_total = $('#sec_con_serv_pub_monto_mes_actual').val();
	var num_recibo = $('#sec_con_serv_pub_num_recibo').val();
	var total_pagar = $('#sec_con_serv_pub_total_pagar').val();

	var periodo_consumo = $('#sec_con_serv_pub_periodo_consumo').val();
	var fecha_emision = $('#sec_con_serv_pub_fecha_emision').val();
	var fecha_vencimiento = $('#sec_con_serv_pub_fecha_vencimiento').val();
	
	

	var nombre_persona_pagar = "";
	var aplica_caja_chica = 0;
	var estado = 2;
	var tipo_compromiso = $('#sec_con_serv_pub_id_tipo_compromiso').val();

	if(periodo_consumo.length == 0){
		alertify.error('Debe ingresar el periodo de consumo',5);
		return false;
	}

	if(fecha_emision.length == 0){
		alertify.error('Debe ingresar la fecha de emision',5);
		return false;
	}

	if(fecha_vencimiento.length == 0){
		alertify.error('Debe ingresar la fecha de vencimiento',5);
		return false;
	}



	//Monto del recibo
	if(monto_total == "" || monto_total == 0){
		alertify.error('Debe ingresar el monto correcto',5);
		return false;
	}
	//Monto del recibo
	if(num_recibo == "" || num_recibo == 0){
		alertify.error('Debe ingresar el numero de recibo',5);
		return false;
	}
	//Nombre persona caja chica
	if($('#sec_con_serv_pub_check_cajachica_id_detalle').prop('checked')){
		nombre_persona_pagar = $('#sec_con_serv_pub_nombre_pagar').val();
		if(nombre_persona_pagar == ''){
			alertify.error('Informacion Caja Chica: Debe ingresar el nombre de la persona a la que se le pagará',10);
			return false;
		}else{
			aplica_caja_chica = 1;
		}
	}

	if(aplica_caja_chica == 1){
		estado = 4; // CAJA CHICA
	}
	if(tipo_compromiso == 7){
		estado = 5; // FACTURA
	}

	//Pregunta si desea guardar el monto
	swal({
        title: "Guardar monto de servicio",
        text: "¿Está seguro de guardar el monto ingresado para el servicio?",
        type: "warning",
        showCancelButton: true,
        cancelButtonText: "NO",
        confirmButtonColor: "#529D73",
        confirmButtonText: "SI",
        closeOnConfirm: false
    },
    function (isConfirm) {
        if(isConfirm){
        	// debugger;
        	var data = {
				"accion" : "guardar_monto_servicio_publico",
				"monto_total" : monto_total,
				"num_recibo" : num_recibo,
				"aplica_caja_chica" : aplica_caja_chica,
				"nombre_persona_pagar" : nombre_persona_pagar,
				"total_pagar" : total_pagar,
				"id_archivo" : id_archivo,
				"periodo_consumo" : periodo_consumo,
				"fecha_emision" : fecha_emision,
				"fecha_vencimiento" : fecha_vencimiento,
				"estado": estado,
			}

			auditoria_send({ "proceso": "guardar_monto_servicio_publico", "data": data });
		    $.ajax({
		        url: "sys/set_contrato_servicio_publico.php",
		        type: 'POST',
		        data: data,
		        beforeSend: function() {
		            loading("true");
		        },
		        complete: function() {
		            loading();
		        },
		        success: function(resp) {
		        	// debugger;
		            var respuesta = JSON.parse(resp);

		            if (parseInt(respuesta.http_code) == 400) {
		            	swal({
							title: "Error al guardar el monto del servicio.",
							text: respuesta.error,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
		                return false;
		            }

		            if (parseInt(respuesta.http_code) == 200) {
		                swal({
							title: "Guardar monto de servicio",
							text: "El monto del servicio se guardó correctamente.",
							html:true,
							type: "success",
							timer: 1000,
							closeOnConfirm: false,
							showCancelButton: false
						});
						setTimeout(function() {
							window.location.href = "?sec_id=contrato&sub_sec_id=servicio_publico";
							return false;
						}, 1000);
		            }      
		        },
		        error: function() {}
		    });


        }else{
            //alertify.error('No se guardó el monto',5);
			return false;
        }
    });
}

















//Al seleccionar el local, cargar los jefes comerciales y supervisores
$('#sec_con_ser_pub_select_locales').change(function(){
	var local_id = $('#sec_con_ser_pub_select_locales').val();
	limpiar_selects();
	sec_ser_pub_cargar_jefe_comercial(local_id);
	sec_ser_pub_cargar_supervisor(local_id);
});


$('#sec_con_ser_pub_select_mes').change(function(){
	var periodo = $('#sec_con_ser_pub_select_mes').val();
	if(periodo != 0){
		limpiar_datepicker_fec_vcto();	
	}
});

$('#sec_con_ser_pub_buscar_por').change(function(){
	var buscar_por = $('#sec_con_ser_pub_buscar_por').val();
	if (buscar_por == 1) {
		$('.block-periodo').show();
		$('.block-fecha').hide();
		$("#select2_example").empty();
	}
	if (buscar_por == 2) {
		$('.block-periodo').hide();
		$('.block-fecha').show();
	}
	ObtenerTipoServicio();
});

function limpiar_datepicker_fec_vcto(){
	$('#sec_con_serv_pub_inicio_vcto').val('');
	$('#sec_con_serv_pub_txt_fec_vcto_desde').val('');
	$('#sec_con_serv_pub_fin_vcto').val('');
	$('#sec_con_serv_pub_txt_fec_vcto_hasta').val('');
}

function limpiar_select_periodo(){
	$('#sec_con_ser_pub_select_mes').val('0').trigger('change.select2');
}

function cargarMeses(){
	var date = new Date();
    var meses = [];

    date.setMonth(date.getMonth());
    var fecha_anio_ = date.toISOString().substring(0, 7);
    meses.push(fecha_anio_);

    //date.setMonth(date.getMonth() - 1);
    for (var c = 1; i <= 12; i++) { // Los ultimos 12 meses
        date.setMonth(date.getMonth() - c);
        var fecha_anio = date.toISOString().substring(0, 7);
        meses.push(fecha_anio)
    }

    $('#sec_con_ser_pub_select_mes').append(
  		'<option value="0">- Seleccione -</option>'
  	);

    $.each(meses, function (ind, elem) { 
		
	  $('#sec_con_ser_pub_select_mes').append(
	  		'<option '+(ind == 0 ? 'selected':'' )+' value="' + elem + '">' + obtenerAnioMesLetras(elem) + '</option>'
	  	);
	}); 
}

function ObtenerTipoServicio(){
	let buscar_por = $('#sec_con_ser_pub_buscar_por').val();
	if (buscar_por == "") {
		alertify.error('Seleccione un metodo de busqueda',10);
		return false;
	}

    //Cargar Montos
    var data = {
		"accion": "obtener_tipos_de_servicio",
		"buscar_por": buscar_por,
	}
	auditoria_send({ "proceso": "obtener_tipos_de_servicio", "data": data });
    $.ajax({
        url: "sys/get_contrato_servicio_publico.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
			$('#sec_con_ser_pub_select_tipo_servicio').html(resp)
        },
        error: function() {}
    });
    //Fin de Cargar Montos
}

function cargarChart(num_meses, local_id, id_tipo_recibo, nombre_local){
	var rr_meses = [];
	var rr_montos = [];
	var local = nombre_local;

	//Cargar Meses
	var date = new Date();
	
    date.setMonth(date.getMonth());
    var fecha_anio_ = date.toISOString().substring(0, 7);
    rr_meses.push(fecha_anio_);
    for (var c = 1; i <=12; i++) { // Los ultimos 12 meses
        date.setMonth(date.getMonth() - c);
        var anio = date.toISOString().substring(0, 7);
        var fecha_anio = date.toISOString().substring(0, 4);
        var fecha_mes = date.toISOString().substring(5, 7);
        var nombre_mes = meses_abrev[fecha_mes - 1];
        rr_meses.push(anio);
    }
    //Fin de Cargar Meses

    //Cargar Montos
    var data = {
		"accion": "obtener_montos_meses_anteriores",
		"local_id": local_id,
		"id_tipo_recibo" : id_tipo_recibo,
		"periodo_consumo_rr" : rr_meses
	}

	auditoria_send({ "proceso": "obtener_montos_meses_anteriores", "data": data });
    $.ajax({
        url: "sys/get_contrato_servicio_publico.php",
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
            	alertify.error('Error: ' + respuesta.status,5);
				return false;
            }

            if (parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.result, function(index, item) {
                    //array_datos_montos.push(item);
                    //debugger;
                    rr_montos.push({periodo : item.periodo_consumo, monto_total_agua : item.monto_total_agua, monto_total_luz : item.monto_total_luz});
                });
                chart(rr_meses,rr_montos, local);
                return false;
            }
        },
        error: function() {}
    });
    //Fin de Cargar Montos
}

function chart(rr_meses, rr_montos, nombre_local){
	var r_l = rr_montos.map(z=> parseFloat(z.monto_total_luz));
	var r_a = rr_montos.map(z=> parseFloat(z.monto_total_agua));
	Highcharts.chart('container', {
	    chart: {
	        type: 'column'
	    },
	    title: {
	        text: 'Montos de Meses Anteriores - ' + nombre_local
	    },
	    subtitle: {
	        text: 'Ultimos 12 Meses'
	    },
	    xAxis: {
	        categories: rr_meses,
	        crosshair: true
	    },
	    yAxis: {
	        min: 0,
	        title: {
	            text: 'Montos'
	        }
	    },
	    tooltip: {
	        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
	        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
	            '<td style="padding:0"><b>{point.y:.1f} mm</b></td></tr>',
	        footerFormat: '</table>',
	        shared: true,
	        useHTML: true
	    },
	    plotOptions: {
	        column: {
	            pointPadding: 0.2,
	            borderWidth: 0
	        }
	    },
	    series: [{
	        name: 'Luz',
	        data: r_l

	    }, {
	        name: 'Agua',
	        data: r_a

	    }]
	});
	$('.highcharts-credits').css('display', "none");
}

function sec_ser_pub_cargar_jefe_comercial(id_local){
	var local_id = id_local;
	var data = {
		"accion": "obtener_jefes_comerciales",
		"local_id": local_id
	}
	var array_jefes_comerciales = [];
	auditoria_send({ "proceso": "obtener_jefes_comerciales", "data": data });
    $.ajax({
        url: "sys/get_contrato_servicio_publico.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
        	//debugger;
            var respuesta = JSON.parse(resp);

            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }

            if (parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.result, function(index, item) {
                    array_jefes_comerciales.push(item);
                    $('#sec_con_ser_pub_select_jefe_comercial').append(
                        '<option value = "' + item.id + '">' + item.jefe_comercial + '</option>'
                    );
                });
                return false;
            }      
        },
        error: function() {}
    });
}

function sec_ser_pub_cargar_supervisor(id_local){
	var local_id = id_local;
	var data = {
		"accion": "obtener_supervisores",
		"local_id": local_id
	}
	var array_jefes_comerciales = [];

	auditoria_send({ "proceso": "obtener_supervisores", "data": data });
    $.ajax({
        url: "sys/get_contrato_servicio_publico.php",
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
                $.each(respuesta.result, function(index, item) {
                    array_jefes_comerciales.push(item);
                    $('#sec_con_ser_pub_select_supervisor').append(
                        '<option value = "' + item.id + '">' + item.supervisor + '</option>'
                    );
                });
                return false;
            }
        },
        error: function() {}
    });
}

function limpiar_selects(){
	$('#sec_con_ser_pub_select_jefe_comercial').html('');
	$('#sec_con_ser_pub_select_jefe_comercial').append('<option value="0">TODOS</option>');
	$('#sec_con_ser_pub_select_supervisor').html('');
	$('#sec_con_ser_pub_select_supervisor').append('<option value="0">TODOS</option>');
}

// function sec_serv_pub_buscar_registros(){
// 	limpiarTabla();
// 	//permisos
// 	// debugger;
// 	var permiso_monto = $('#sec_con_serv_pub_permiso_agregar_monto').html()
// 	let buscar_por = $('#sec_con_ser_pub_buscar_por').val();

// 	let tipo_servicio = $('#sec_con_ser_pub_select_tipo_servicio').val();
// 	let estado = $('#sec_con_ser_pub_select_estado').val();
// 	var pendientes = "0";
// 	var id_local = $('#sec_con_ser_pub_select_locales').val();
// 	var id_jefe_comercial = $('#sec_con_ser_pub_select_jefe_comercial').val();
// 	var id_supervisor = $('#sec_con_ser_pub_select_supervisor').val();
// 	var periodo = $('#sec_con_ser_pub_select_mes').val();
// 	var fec_vcto_desde = $('#sec_con_serv_pub_inicio_vcto').val();
// 	var fec_vcto_hasta = $('#sec_con_serv_pub_fin_vcto').val();
// 	if(periodo == 0 && (fec_vcto_desde == "" || fec_vcto_hasta == "")){
// 		alertify.error('Información: Seleccione el Mes de Periodo o Seleccione el Rango de Fecha de Vencimiento',10);
// 		return false;
// 	}

// 	var action = "";
// 	if(buscar_por == 1){
// 		action = "obtener_registros_locales_periodo";
// 		if (periodo == "" || periodo == '0') {
// 			alertify.error('Información: Seleccione el Mes de Periodo',10);
// 			return false;
// 		}
		
		
// 	}
// 	if(buscar_por == 2 ){
// 		action = "obtener_registros_locales_fechas";
// 		if(fec_vcto_desde == "" || fec_vcto_hasta == ""){
// 			alertify.error('Información: Seleccione el Rango de Fecha de Vencimiento',10);
// 			return false;
// 		}
// 	}
// 	var data = {
// 		"accion": action,
// 		"buscar_por" : buscar_por,
// 		"local_id": id_local,
// 		"id_jefe_comercial" : id_jefe_comercial,
// 		"id_supervisor" : id_supervisor,
// 		"permiso_monto" : permiso_monto,
// 		"periodo" : periodo,
// 		"fec_vcto_desde" : fec_vcto_desde,
// 		"fec_vcto_hasta" : fec_vcto_hasta,
// 		"btn_pendientes" : pendientes,
// 		"estado" : estado,
// 		"tipo_servicio" : tipo_servicio,
// 	}
// 	mostrarReporteExcel();

// 	var array_jefes_comerciales = [];
// 	auditoria_send({ "proceso": "obtener_registros_locales", "data": data });
//     $.ajax({
//         url: "sys/get_contrato_servicio_publico.php",
//         type: 'POST',
//         data: data,
//         beforeSend: function() {
//             loading("true");
//         },
//         complete: function() {
//             loading();
//         },
//         success: function(resp) {
// 			var respuesta = JSON.parse(resp);
// 			if (parseInt(respuesta.http_code) == 200) {
// 				$('#container_table_servicio_publico').html(respuesta.table);
// 			}
// 			initialize_table('sec_con_tabla_registros');
// 			return false;
//         },
//         error: function() {}
//     });
// }

function HeadTablaServicioPublico(code) {
	
}

function limpiarTabla(){
	$('#sec_con_tabla_registros').html('');
	$('#sec_con_tabla_registros').append(
		'<thead>' +
		'<tr role="row">' +
		'<th style="max-width: 300px; text-align: center;">Local</th>' +
		'<th style="text-align: center;">Jefe Comercial</th>' +
		'<th style="text-align: center;">Supervisor</th>' +
		'<th style="text-align: center;">Periodo</th>' +
		'<th style="text-align: center;">Servicio de Luz</th>' +
		'<th style="text-align: center;">Fec. Vcto. Luz</th>' +
		'<th style="text-align: center;">Estado Luz</th>' +
		'<th style="text-align: center;">Servicio de Agua</th>' +
		'<th style="text-align: center;">Fec. Vcto. Agua</th>' +
		'<th style="text-align: center;">Estado Agua</th>' +
		'</tr>' +
		'</thead>'
	);
}

function cargarCompromisosPago(){
	limpiarSelectCompromisos();
	var data = {
		"accion": "obtener_listado_compromisos_de_pago"
	}

	var array_datos_compromisos = [];
	auditoria_send({ "proceso": "obtener_listado_compromisos_de_pago", "data": data });
    $.ajax({
        url: "sys/get_contrato_servicio_publico.php",
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
                $.each(respuesta.result, function(index, item) {
                    array_datos_compromisos.push(item);
					var nuevo_id = 1;
					$('#sec_con_ser_pub_select_compromiso_pago').append(
                       '<option value="' + item.id + '"> ' + item.nombre +  ' </option>'
                    );
                });
                return false;
            }      
        },
        error: function() {}
    });
}

function limpiarCasilleros(){
	$('#sec_con_serv_pub_num_suministro').val('');
	$('#sec_con_serv_pub_periodo_consumo').val('');
	$('#sec_con_serv_pub_fecha_emision').val('');
	$('#sec_con_serv_pub_fecha_vencimiento').val('');
	$('#sec_con_serv_pub_id_tipo_compromiso').val('');
	$('#sec_con_serv_pub_id_monto_pct').val('');
	$('#sec_con_serv_pub_monto_mes_actual').val('');
	$('#sec_con_serv_pub_total_pagar').val('');
	$('#sec_con_serv_pub_div_imagen_recibo').html('');
	$('#sec_con_serv_pub_id_archivo').val('');
	$('#sec_con_serv_pub_id_tipo_compromiso_nombre').val('');
	$('#sec_con_serv_pub_id_tipo_recibo').val('');
	$('#sec_con_serv_pub_id_local').val('');
}

function limpiarSelectCompromisos(){
	$('#sec_con_ser_pub_select_compromiso_pago').html('');
}

function abrirModalServicioPublico(consumo_per){
	$('#sec_con_serv_pub_modal_agregar_monto').modal({backdrop: 'static', keyboard: false});
	$('#sec_con_serv_pub_periodo_busqueda').html(obtenerMesAnioLetras(consumo_per));
	$('#sec_con_serv_pub_periodo_busqueda_data').val(consumo_per);
}

function sec_contrato_detalle_servicio_publico_agregar_monto(id_tipo_recibo, id_local , consumo_per, nombre_local, id_recibo)
{
	debugger;
	limpiarCasilleros();
	$('#sec_con_serv_pub_div_imagen_recibo').html('');
	$('#sec_con_serv_pub_id_tipo_recibo').val(id_tipo_recibo);
	$('#sec_con_serv_pub_id_local').val(id_local);
	$('#sec_con_serv_pub_id_recibo').val(id_recibo);
	var nombre_recibo = "";
	if(id_tipo_recibo == 1){
		nombre_recibo = "Recibo de Luz";
	} else if (id_tipo_recibo == 2){
		nombre_recibo = "Recibo de Agua";
	}

	$('#sec_con_serv_pub_title_modal_agregar_monto').html("Agregar Monto - " + nombre_recibo);	
	$('#sec_con_serv_pub_title_modal_agregar_monto_local').html("Local: " + nombre_local);	

	obtenerDatosRecibo(id_recibo, 0,'',0);
}

$('#sec_con_serv_pub_monto_mes_actual').on('keyup', function(){
	//debugger;
	var tipo_compromiso = $('#sec_con_serv_pub_id_tipo_compromiso').val();
	var monto_pct = $('#sec_con_serv_pub_id_monto_pct').val();

	if(tipo_compromiso == 1){ //porcentaje
		monto_pct = monto_pct / 100;
	}

    var value = $(this).val();
    value = parseInt(value);

    if(tipo_compromiso == 1){ //Porcentaje
    	value = value * monto_pct;
    }else if (tipo_compromiso == 2){ // Monto fijo
    	value = monto_pct;
    }else if (tipo_compromiso == 3){ // Total del servicio 
		value = value;
    }else if(tipo_compromiso == 4){ // contometro
    	value = value;
    }else if(tipo_compromiso == 5){ // compartido
    	value = value;
    }else if(tipo_compromiso == 6){ // excedente - monto base
    	if(value > monto_pct){
    		value = parseFloat(value) - parseFloat(monto_pct);
    	}else{
    		value = 0;
    	}
    	value = value;
    }else if(tipo_compromiso == 7){ // factura
    	value = 0;
    }else if(tipo_compromiso == 8){ // no se paga
    	value = 0;
    }
    $("#sec_con_serv_pub_total_pagar").val(value);
}).keyup();

function guardarNuevoMontoRecibo(){
	var id_archivo = $('#sec_con_serv_pub_id_archivo').val();
	var monto_total = $('#sec_con_serv_pub_monto_mes_actual').val();
	var num_recibo = $('#sec_con_serv_pub_num_recibo').val();
	var total_pagar = $('#sec_con_serv_pub_total_pagar').val();

	var periodo_consumo = $('#sec_con_serv_pub_periodo_consumo').val();
	var fecha_emision = $('#sec_con_serv_pub_fecha_emision').val();
	var fecha_vencimiento = $('#sec_con_serv_pub_fecha_vencimiento').val();
	
	

	var nombre_persona_pagar = "";
	var aplica_caja_chica = 0;
	var estado = 2;
	var tipo_compromiso = $('#sec_con_serv_pub_id_tipo_compromiso').val();

	if(periodo_consumo.length == 0){
		alertify.error('Debe ingresar el periodo de consumo',5);
		return false;
	}

	if(fecha_emision.length == 0){
		alertify.error('Debe ingresar la fecha de emision',5);
		return false;
	}

	if(fecha_vencimiento.length == 0){
		alertify.error('Debe ingresar la fecha de vencimiento',5);
		return false;
	}



	//Monto del recibo
	if(monto_total == "" || monto_total == 0){
		alertify.error('Debe ingresar el monto correcto',5);
		return false;
	}
	//Monto del recibo
	if(num_recibo == "" || num_recibo == 0){
		alertify.error('Debe ingresar el numero de recibo',5);
		return false;
	}
	//Nombre persona caja chica
	if($('#sec_con_serv_pub_check_cajachica_id').prop('checked')){
		nombre_persona_pagar = $('#sec_con_serv_pub_nombre_pagar').val();
		if(nombre_persona_pagar == ''){
			alertify.error('Informacion Caja Chica: Debe ingresar el nombre de la persona a la que se le pagará',10);
			return false;
		}else{
			aplica_caja_chica = 1;
		}
	}

	if(aplica_caja_chica == 1){
		estado = 4; // CAJA CHICA
	}
	if(tipo_compromiso == 7){
		estado = 5; // FACTURA
	}

	//Pregunta si desea guardar el monto
	swal({
        title: "Guardar monto de servicio",
        text: "¿Está seguro de guardar el monto ingresado para el servicio?",
        type: "warning",
        showCancelButton: true,
        cancelButtonText: "NO",
        confirmButtonColor: "#529D73",
        confirmButtonText: "SI",
        closeOnConfirm: false
    },
    function (isConfirm) {
        if(isConfirm){
        	// debugger;
        	var data = {
				"accion" : "guardar_monto_servicio_publico",
				"monto_total" : monto_total,
				"num_recibo" : num_recibo,
				"aplica_caja_chica" : aplica_caja_chica,
				"nombre_persona_pagar" : nombre_persona_pagar,
				"total_pagar" : total_pagar,
				"id_archivo" : id_archivo,
				"periodo_consumo" : periodo_consumo,
				"fecha_emision" : fecha_emision,
				"fecha_vencimiento" : fecha_vencimiento,
				"estado": estado,
			}

			auditoria_send({ "proceso": "guardar_monto_servicio_publico", "data": data });
		    $.ajax({
		        url: "sys/set_contrato_servicio_publico.php",
		        type: 'POST',
		        data: data,
		        beforeSend: function() {
		            loading("true");
		        },
		        complete: function() {
		            loading();
		        },
		        success: function(resp) {
		        	// debugger;
		            var respuesta = JSON.parse(resp);

		            if (parseInt(respuesta.http_code) == 400) {
		            	swal({
							title: "Error al guardar el monto del servicio.",
							text: respuesta.error,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
		                return false;
		            }

		            if (parseInt(respuesta.http_code) == 200) {
		                swal({
							title: "Guardar monto de servicio",
							text: "El monto del servicio se guardó correctamente.",
							html:true,
							type: "success",
							timer: 1000,
							closeOnConfirm: false,
							showCancelButton: false
						});
						setTimeout(function() {
							window.location.href = "?sec_id=contrato&sub_sec_id=servicio_publico";
							return false;
						}, 1000);
		            }      
		        },
		        error: function() {}
		    });


        }else{
            //alertify.error('No se guardó el monto',5);
			return false;
        }
    });
}





function sec_con_serv_pub_navegar_periodo(numero){
	
	//debugger;
	
	var id_tipo_recibo = $('#sec_con_serv_pub_id_tipo_recibo').val();
	var id_local = $('#sec_con_serv_pub_id_local').val();
	var periodo_actual = $('#sec_con_serv_pub_periodo_busqueda_data').val();

	var actual = new Date(parseInt(periodo_actual.substring(0, 4)), parseInt(periodo_actual.substring(5, 7)) - 1, 1);
	var nav = new Date(actual.setMonth(actual.getMonth() + numero));
	var mes = (nav.getMonth() + 1);
	if(mes < 10){ mes = "0" + mes;}
	//debugger;
	var periodo = nav.getFullYear() + '-' + mes + '-01';


	if(id_tipo_recibo == ""){ alertify.error('Información: id_tipo_recibo vacio',5); return;}
	if(id_local == ""){ alertify.error('Información: id_tipo_recibo vacio',5); return;}

	obtenerDatosRecibo(0, id_local, periodo, id_tipo_recibo);
}

// function obtenerDatosRecibo(id_recibo, id_local, periodo, id_tipo_recibo){
// 	var data = {
// 		"accion": "obtener_datos_recibo",
// 		"id_recibo": id_recibo,
// 		"id_local": id_local,
// 		"periodo": periodo,
// 		"id_tipo_recibo": id_tipo_recibo
// 	}
// 	var array_datos_recibo = [];
// 	auditoria_send({ "proceso": "obtener_datos_recibo", "data": data });
//     $.ajax({
//         url: "sys/get_contrato_servicio_publico.php",
//         type: 'POST',
//         data: data,
//         beforeSend: function() {
//             loading("true");
//         },
//         complete: function() {
//             loading();
//         },
//         success: function(resp) {
//         	// debugger;
//         	//console.log(resp);
//             var respuesta = JSON.parse(resp);
//             if (parseInt(respuesta.http_code) == 400) {
//             	alertify.error('Información: No hay datos del periodo ' + respuesta.status,5);
//             	/*$('#sec_con_serv_pub_div_datos_recibo').addClass('diabled_all_content');
//             	$('#sec_nuevo_modal_guardar_archivo').addClass('diabled_all_content');

//             	$('#sec_con_serv_pub_id_tipo_recibo').val('');
// 				$('#sec_con_serv_pub_id_local').val('');

// 				$('#sec_con_serv_pub_title_modal_agregar_monto').html('');	
// 				$('#sec_con_serv_pub_title_modal_agregar_monto_local').html('');	*/
// 				return false;
//             }

//             if (parseInt(respuesta.http_code) == 200) {
//                 $.each(respuesta.result, function(index, item) {
//                     array_datos_recibo.push(item);
//                     abrirModalServicioPublico(item.periodo_consumo);
//                 	cargarChart(12, item.id_local, item.id_tipo_servicio_publico, item.local);
//                 	$('#sec_con_serv_pub_mensaje_imagen').html('');
// 	            	$('#sec_con_serv_pub_div_datos_recibo').removeClass('diabled_all_content');
// 	            	$('#sec_nuevo_modal_guardar_archivo').removeClass('diabled_all_content');
// 	            	$('#sec_con_serv_pub_ver_full_pantalla').prop('disabled', false);
// 				  	$('#sec_con_serv_pub_descargar_imagen_a').prop('disabled', false);
//                     array_datos_recibo.push(item);
// 					$('#sec_con_serv_pub_id_archivo').val(item.id);
// 					$('#sec_con_serv_pub_num_suministro').val(item.numero_suministro);
// 					$('#sec_con_serv_pub_num_recibo').val(item.numero_recibo);
// 					// $('#sec_con_serv_pub_periodo_consumo').val(obtenerMesAnioLetras(item.periodo_consumo));
// 					$('#sec_con_serv_pub_periodo_consumo').val(item.periodo_consumo);
// 					$('#sec_con_serv_pub_fecha_emision').val(item.fecha_emision);
// 					$('#sec_con_serv_pub_fecha_vencimiento').val(item.fecha_vencimiento);
//                 	$('#sec_con_serv_pub_id_tipo_compromiso_nombre').val(item.tipo_compromiso_nombre);
// 					$('#sec_con_serv_pub_id_monto_pct').val(item.monto_pct);
// 					$('#sec_con_serv_pub_monto_mes_actual').val(item.monto_total);
// 					$('#ocultossec_con_serv_pub_total_pagar').val(item.total_pagar);
// 					//
// 					$('#sec_con_serv_pub_periodo_consumo_oculto').val(item.periodo_consumo);
// 					$('#sec_con_serv_pub_id_tipo_compromiso').val(item.tipo_compromiso);
// 					var path_img = "contratos/servicios_publicos/";
// 					if(item.id_tipo_servicio_publico == 1){ //Luz
// 						path_img += "luz/";
// 					}else{
// 						path_img += "agua/";
// 					}
// 					path_img += item.nombre_file;
// 					$('#sec_con_serv_pub_div_imagen_recibo').html('');
// 					//Validar si la imagen de la BD existe en la carpeta
// 					var nuevo_id = "sec_serv_pub_img_servicio_publico"+item.id+"_"+item.numero_suministro;
// 					if (item.extension == "pdf") {
// 						$('#sec_con_serv_pub_div_imagen_recibo').append(
// 							'<iframe src="' + path_img + '" class="col-xs-12 col-md-12 col-sm-12" width="100%" height="400"></iframe>'
// 						);

// 						$('#sec_con_serv_pub_btn_descargar_imagen_recibo').hide();
// 						$('#sec_con_serv_pub_div_VerImagenFullPantalla').hide();
						
// 					}else if (item.extension == 'jpg' || item.extension == 'png' || item.extension == 'jpeg') {
// 						$('#sec_con_serv_pub_div_imagen_recibo').append(
// 							'<div class="col-md-12">' +
// 							'   <div align="center" style="height: 100%; width: 100%;">' +
// 							'       <img  id="' + nuevo_id + '" src="' + path_img + '" width="300px" height="350px" />' +
// 							'   </div>' +
// 							'</div>'
// 						);
// 						$('#sec_con_serv_pub_ver_full_pantalla').attr('onClick', 'sec_contrato_servicio_publico_ver_imagen_full_pantalla("' + path_img + '");');
// 						$('#sec_con_serv_pub_btn_descargar_imagen_recibo').show();
// 						$('#sec_con_serv_pub_div_VerImagenFullPantalla').show();
// 						var ruta = "sec_contrato_detalle_servicio_publico_btn_descargar('"+ item.ruta_download_file +"');";
// 						$('#sec_con_serv_pub_descargar_imagen_a').attr('onClick', ruta);
// 						$("#" + nuevo_id).error(function(){
// 						  $(this).hide();
// 						  $('#sec_con_serv_pub_mensaje_imagen').html('La imagen no existe en la carpeta');
// 						  $('#sec_con_serv_pub_ver_full_pantalla').prop('disabled', true);
// 						  $('#sec_con_serv_pub_descargar_imagen_a').prop('disabled', true);
						  
// 						});
// 						//Fin de Validar si la imagen de la BD existe en la carpeta
// 					}
					

//                     //Inicio compromiso monto fijo
//                     var tipo_compromiso = $('#sec_con_serv_pub_id_tipo_compromiso').val();
// 					var monto_pct = $('#sec_con_serv_pub_id_monto_pct').val();
// 					if(tipo_compromiso == 2){ //monto fijo
// 						$("#sec_con_serv_pub_total_pagar").val(monto_pct);
// 					}

// 					if(tipo_compromiso == 1 || tipo_compromiso == 5 || tipo_compromiso == 6){
// 						$('#sec_con_serv_pub_total_pagar').prop('disabled', false);
// 					}else{
// 						$('#sec_con_serv_pub_total_pagar').prop('disabled', true);
// 					}
// 					if(item.estado_recibo == 1 || item.estado_recibo == 6){ //PENDIENTE U OBSERVADO
// 						$('#sec_con_serv_pub_total_pagar').prop('disabled', false);
// 						$('#sec_con_serv_pub_num_recibo').prop('disabled', false);
// 						$('#sec_con_serv_pub_monto_mes_actual').prop('disabled', false);
// 						$('#sec_nuevo_modal_observar_archivo').prop('disabled', false);
// 						$('#sec_nuevo_modal_guardar_archivo').prop('disabled', false);
// 					}
// 					if(item.estado_recibo == 2 || item.estado_recibo == 3 || item.estado_recibo == 4 || item.estado_recibo == 5){ //VALIDADO|PAGADO|CAJA CHICA|FACTURA
// 						$('#sec_con_serv_pub_total_pagar').prop('disabled', true);
// 						$('#sec_con_serv_pub_num_recibo').prop('disabled', true);
// 						$('#sec_con_serv_pub_monto_mes_actual').prop('disabled', true);
// 						$('#sec_nuevo_modal_observar_archivo').prop('disabled', true);
// 						$('#sec_nuevo_modal_guardar_archivo').prop('disabled', true);
// 						$('#sec_con_serv_pub_check_caja_chica_div').prop('disabled', true);
// 						$('#sec_con_serv_pub_div_nombre_pagar_hide').prop('disabled', true);
// 						$('#sec_con_serv_pub_check_cajachica_id').prop('disabled', true);
// 					}
//                     //Fin
//                 });
//                 return false;
//             }      
//         },
//         error: function() {}
//     });
// 	///////////FIN DE OBTENER DATOS/////////////////////////
// }

function sec_contrato_servicio_publico_ver_imagen_full_pantalla(ruta) {
	// debugger;
	var image = new Image();
	image.src = ruta;
	var viewer = new Viewer(image, {
		hidden: function () {
			viewer.destroy();
		},
	});
	// image.click();
	viewer.show();
}

function obtenerMesAnioLetras(fecha){
	if (fecha.length > 0) {
		var anio = fecha.substring(0,4);
		var mes = fecha.substring(5,7);
		mes = meses[mes - 1];
		var fecha_mes_anio = mes + " - " + anio;
	
		return fecha_mes_anio;
	}
	return '';
}

function obtenerAnioMesLetras(fecha){

	if (fecha.length > 0) {
		var anio = fecha.substring(0,4);
		var mes = fecha.substring(5,7);
		mes = meses[mes - 1];
		var fecha_mes_anio = anio + " - " + mes;

		return fecha_mes_anio;
	}
	return '';
	
}

function sec_con_serv_pub_abrir_modal_observacion(){

	var id_archivo = $('#sec_con_serv_pub_id_archivo').val();

	var data = {
		"accion" : "obtener_correos_observacion",
		"id_archivo" : id_archivo
	}
	$.ajax({
		url: "sys/set_contrato_servicio_publico.php",
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
			if (parseInt(respuesta.http_code) == 200) {
				$("#sec_con_serv_pub_observacion_correos").val(respuesta.result);
				
			}
		},
		error: function() {}
	});

	$('#sec_con_serv_pub_modal_observar_servicio').modal({backdrop: 'static', keyboard: false});
}

function observarServicioPublico(){
	//debugger;
	var id_archivo = $('#sec_con_serv_pub_id_archivo').val();
	var observacion = $('#sec_con_serv_pub_observacion').val();
	var correos = $('#sec_con_serv_pub_observacion_correos').val();
	correos = correos.split(',');

	if(observacion == ''){
		alertify.error('Informacion: Ingrese la observacion para el servicio',5);
		return false;
	}
	
	let new_correos = correos.map( item => item.trim());

	for (let index = 0; index < new_correos.length; index++) {
		const element = new_correos[index];
		if (element.length == 0) {
			alertify.error(' Ingrese un correo',5);
			return false;
		}
		if (!ValidateEmail(element)) {
			alertify.error(element + ' no es correo valido',5);
			return false;
		}
	}
	

	swal({
        title: "Observar servicio",
        text: "¿Desea observar el servicio?",
        type: "warning",
        showCancelButton: true,
        cancelButtonText: "NO",
        confirmButtonColor: "#529D73",
        confirmButtonText: "SI",
        closeOnConfirm: false
    },
    function (isConfirm) {
        if(isConfirm){
        	//debugger;
        	var data = {
				"accion" : "observar_servicio_publico",
				"observacion" : observacion,
				"id_archivo" : id_archivo,
				"correos": new_correos,
			}
			auditoria_send({ "proceso": "observar_servicio_publico", "data": data });
		    $.ajax({
		        url: "sys/set_contrato_servicio_publico.php",
		        type: 'POST',
		        data: data,
		        beforeSend: function() {
		            loading("true");
		        },
		        complete: function() {
		            loading();
		        },
		        success: function(resp) {
		        	//debugger;
		            var respuesta = JSON.parse(resp);

		            if (parseInt(respuesta.http_code) == 400) {
		            	swal({
							title: "Error al observar el servicio.",
							text: respuesta.error,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
		                return false;
		            }

		            if (parseInt(respuesta.http_code) == 200) {
		                swal({
							title: "Servicio Observado",
							text: "Observación guardada correctamente",
							html:true,
							type: "success",
							timer: 1000,
							closeOnConfirm: false,
							showCancelButton: false
						});
						setTimeout(function() {
							window.location.href = "?sec_id=contrato&sub_sec_id=servicio_publico";
							return false;
						}, 1000);
		            }      
		        },
		        error: function() {}
		    });
        }else{
			return false;
        }
    });
}

function ValidateEmail(email) {
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
  }

function enviarCorreoObservacionServicio(){
	//obtener correos

}

function table_destroy(tabla){
	$('#' + tabla).DataTable().clear().destroy();
}

function initialize_table(tabla){
	$('#' + tabla).DataTable({
		"bDestroy": true,
		scrollX: true,
		language:{
			"decimal":        "",
			"emptyTable":     "Tabla vacia",
			"info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
			"infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
			"infoFiltered":   "(filtered from _MAX_ total entradas)",
			"infoPostFix":    "",
			"thousands":      ",",
			"lengthMenu":     "Mostrar _MENU_ entradas",
			"loadingRecords": "Cargando...",
			"processing":     "Procesando...",
			"search":         "Filtrar:",
			"zeroRecords":    "Sin resultados",
			"paginate": {
				"first":      "Primero",
				"last":       "Ultimo",
				"next":       "Siguiente",
				"previous":   "Anterior"
			},
			"aria": {
				"sortAscending":  ": activate to sort column ascending",
				"sortDescending": ": activate to sort column descending"
			}
		}
		,aLengthMenu:[10, 20, 30, 40, 50]
		,"order": [[ 0, 'desc' ]]
	});  
}

function sec_serv_pub_buscar_sin_recibo(){
	//permisos
	var permiso_recibo = $('#sec_con_serv_pub_permiso_agregar_recibo').html();
	//fin permiso
	var id_local = $('#sec_con_ser_pub_select_locales').val();
	var id_jefe_comercial = $('#sec_con_ser_pub_select_jefe_comercial').val();
	var id_supervisor = $('#sec_con_ser_pub_select_supervisor').val();
	var periodo = $('#sec_con_ser_pub_select_mes').val();
	
	if(periodo == 0){
		alertify.error('Información: Seleccione el Mes de Periodo',5);
		return false;
	}

	limpiarTabla();
	var data = {
		"accion": "obtener_registros_locales_sin_recibo",
		"local_id": id_local,
		"id_jefe_comercial" : id_jefe_comercial,
		"id_supervisor" : id_supervisor,
		"periodo" : periodo
	}

	var array_jefes_comerciales = [];

	auditoria_send({ "proceso": "obtener_registros_locales_sin_recibo", "data": data });
    $.ajax({
        url: "sys/get_contrato_servicio_publico.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
        	//debugger;
            var respuesta = JSON.parse(resp);

            if (parseInt(respuesta.http_code) == 400) {
            	$('#sec_con_tabla_registros').append(
                    '<tr>' +
                    '<td colspan="8" style="text-align: center;">No hay registros</td>' +
                    '</tr>'
                );
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
            	table_destroy('sec_con_tabla_registros');
                $.each(respuesta.result, function(index, item) {
                    array_jefes_comerciales.push(item);
                	//luz

                	var onclick_luz = "agregarRecibo(1," + item.local_id + ",'" + periodo + "','" + item.local_nombre + "')";
                	if(permiso_recibo == 'true'){
                		item.recibo_luz = '<button type="button" class="btn btn-sm btn-warning" onclick="' + onclick_luz + '")">Agregar Recibo</button>';
                	}else{
                		item.recibo_luz = 'Sin Recibo';
                	}
                	

                	//agua
                	var onclick_agua = "agregarRecibo(2," + item.local_id + ",'" + periodo + "','" + item.local_nombre + "')";
                	if(permiso_recibo == 'true'){
                		item.recibo_agua = '<button type="button" class="btn btn-sm btn-info" onclick="' + onclick_agua + '")">Agregar Recibo</button>';
                	}else{
                		item.recibo_agua = 'Sin Recibo';
                	}
                	

                	if(item.local == 'null' || item.local == null){
                		//debugger;
                	}

                    $('#sec_con_tabla_registros').append(
                        '<tr>' +
                        '<td>' + item.local + '</td>' +
                        '<td>' + item.jefe_comercial + '</td>' +
                        '<td>' + item.supervisor + '</td>' +
                        '<td>' + obtenerAnioMesLetras(periodo) + '</td>' +
                        '<td style="text-align: center;">' + item.recibo_luz + '</td>' +
                        '<td style="text-align: center;">' + item.fec_vcto_recibo_luz + '</td>' +
                        '<td style="text-align: center;">' + item.estado_recibo_luz + '</td>' +
                        '<td style="text-align: center;">' + item.recibo_agua + '</td>' +
                        '<td style="text-align: center;">' + item.fec_vcto_recibo_agua + '</td>' +
                        '<td style="text-align: center;">' + item.estado_recibo_agua + '</td>' +
                        '</tr>'
                    );
                });
                initialize_table('sec_con_tabla_registros');
                return false;
            }
        },
        error: function() {}
    });
}

setArchivoRecibo($('#sec_serv_pub_file_archivo_recibo'));
$("#sec_con_serv_pub_comentario_recibo").keyup(function ()
{
	$("#sec_con_serv_pub_caracteres_comentario").text(255 - $(this).val().length)
});

function setArchivoRecibo(object){

	$(document).on('click', '#sec_serv_pub_btn_buscar_archivo', function(event) {

		event.preventDefault();
		object.click();
	});

	object.on('change', function(event) {

		//let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
		if($(this)[0].files.length <= 1)
		{
			const name = $(this).val().split(/\\|\//).pop();
			//truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
			truncated = name;
		}
		else
		{
			truncated = "";
		}

		$("#sec_serv_pub_file_info").html(truncated);

	});
}

function agregarRecibo(tipo_recibo, id_local, periodo, local){
	//Titulo

	if(tipo_recibo == 1){
		$('#sec_serv_pub_modal_title_agregar_recibo').html('Agregar Recibo de Luz');
	}else if(tipo_recibo == 2){
		$('#sec_serv_pub_modal_title_agregar_recibo').html('Agregar Recibo de Agua');
	}
	$("#sec_serv_pub_file_info").html('');
	$("#txt_locales_servicio_publico_monto_total").html('');
	"guardarRecibo(" + tipo_recibo + ",'" + periodo + "'," + id_local + ", '" + local + "');"
	$('#sec_nuevo_modal_guardar_recibo').attr("onClick", "guardarRecibo(" + tipo_recibo + ",'" + periodo + "'," + id_local + ", '" + local + "');");
	
	//Abrir modal
	$('#sec_con_serv_pub_modal_agregar_recibo').modal({backdrop: 'static', keyboard: false});
}

function guardarRecibo(tipo_recibo, periodo, id_local, local){
	var fecha_emision_recibo = $("#sec_con_serv_pub_fecha_emision_recibo").val();
	var fecha_vencimiento_recibo = $("#sec_con_serv_pub_fecha_vencimiento_recibo").val();
	var comentario = $("#sec_con_serv_pub_comentario_recibo").val();
	var monto = $("#sec_con_serv_pub_monto_total_recibo").val();
	var recibo = $("#sec_con_serv_pub_num_recibo_nuevo").val();

	if(monto == '' || parseFloat(monto) == 0){
		alertify.error('Informacion: Ingrese el monto del recibo',5);
		return false;
	}

	if($.trim(recibo) == ""){
		alertify.error('Informacion: Ingrese el número del recibo',5);
		return false;
	}

	swal({
        title: "Agregar Recibo",
        text: "¿Desea agregar el recibo? \n Local: " + local 
    			+ "\n Periodo: " + periodo 
    			+ "\n Monto: " + monto 
    			+ "\n N° Recibo: " + recibo 
    			+ "\n Comentario: " + comentario.toUpperCase(),
        type: "warning",
        showCancelButton: true,
        cancelButtonText: "NO",
        confirmButtonColor: "#529D73",
        confirmButtonText: "SI",
        closeOnConfirm: false
    },
    function (isConfirm) {
        if(isConfirm){
        	var form_data = new FormData($("#sec_con_serv_pub_form_modal_agregar_recibo")[0]);
			form_data.append("accion", "guardar_archivo_servicio_publico");
			form_data.append("id_recibo", tipo_recibo);
			form_data.append("id_local", id_local);
			form_data.append("periodo", periodo);
			form_data.append("local", local);
			form_data.append("fecha_emision", fecha_emision_recibo);
			form_data.append("fecha_vencimiento", fecha_vencimiento_recibo);
			form_data.append("comentario", comentario);
			form_data.append("monto", monto);
			form_data.append("recibo", recibo);

			loading(true);
			$.ajax({
				url: "sys/set_contrato_servicio_publico.php",
				type: "POST",
				data: form_data,
				cache: false,
				contentType: false,
				processData:false,
				success: function(response) {
					respuesta = JSON.parse(response);
					loading(false);
					if (parseInt(respuesta.http_code) == 400) {
		            	swal({
							title: "Error al guardar el recibo.",
							text: respuesta.error,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
		                return false;
		            }

		            if (parseInt(respuesta.http_code) == 200) {
		                swal({
							title: "Recibo Guardado",
							text: "Recibo guardado correctamente",
							html:true,
							type: "success",
							timer: 1000,
							closeOnConfirm: false,
							showCancelButton: false
						});
						setTimeout(function() {
							window.location.href = "?sec_id=contrato&sub_sec_id=servicio_publico";
							return false;
						}, 1000);
		            }    
				},
				always: function(data){
					console.log(data);
				}
			});
        }else{
			return false;
        }
    });
}


// function mostrarReporteExcel()
// {

// 	let buscar_por = $('#sec_con_ser_pub_buscar_por').val();
// 	let tipo_servicio = $('#sec_con_ser_pub_select_tipo_servicio').val();
// 	let estado = $('#sec_con_ser_pub_select_estado').val();
// 	var pendientes = 0;
// 	var id_local = $('#sec_con_ser_pub_select_locales').val();
// 	var id_jefe_comercial = $('#sec_con_ser_pub_select_jefe_comercial').val();
// 	var id_supervisor = $('#sec_con_ser_pub_select_supervisor').val();
// 	var periodo = $('#sec_con_ser_pub_select_mes').val();
// 	var fec_vcto_desde = $('#sec_con_serv_pub_inicio_vcto').val();
// 	var fec_vcto_hasta = $('#sec_con_serv_pub_fin_vcto').val();
// 	if(periodo == 0 && (fec_vcto_desde == "" || fec_vcto_hasta == "")){
// 		alertify.error('Información: Seleccione el Mes de Periodo o Seleccione el Rango de Fecha de Vencimiento',10);
// 		return false;
// 	}

// 	document.getElementById('cont_contrato_servicio_publico_excel').innerHTML = '<a href="export.php?export=cont_contrato_servicio_publico&amp;type=lista&amp;buscar_por='+buscar_por+'&amp;tipo_servicio='+tipo_servicio+'&amp;estado='+estado+'&amp;pendientes='+pendientes+'&amp;id_local='+id_local+'&amp;id_jefe_comercial='+id_jefe_comercial+'&amp;id_supervisor='+id_supervisor+'&amp;periodo='+periodo+'&amp;fec_vcto_desde='+fec_vcto_desde+'&amp;fec_vcto_hasta='+fec_vcto_hasta+'" class="btn btn-success export_list_btn" download="contrato_servicio_publico.xls"><span class="glyphicon glyphicon-export"></span> Exportar excel</a>';

// }


function verFileServicioPublicoEnVisor(tipo_documento, ruta_file) 
{
	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf') 
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" height="525"></iframe>';
		$('#divVisorPdfModal').html(htmlModal);

		$('#exampleModalPreviewServicio').modal('show');

	} 
	else if (tipodocumento == 'jpg' || tipodocumento == 'png' || tipodocumento == 'jpeg') 
	{
		var image = new Image();
		image.src = ruta;
		var viewer = new Viewer(image, 
		{
			hidden: function () {
				viewer.destroy();
			},
		});
		// image.click();
		viewer.show();
	}
}




// function ModalCancelar(id_tipo_recibo, id_local , consumo_per, nombre_local, id_recibo){
// 	limpiarCasilleros();
// 	$('#sec_con_serv_pub_div_imagen_recibo').html('');
// 	$('#sec_con_serv_pub_id_tipo_recibo').val(id_tipo_recibo);
// 	$('#sec_con_serv_pub_id_local').val(id_local);
// 	$('#sec_con_serv_pub_id_recibo').val(id_recibo);
// 	var nombre_recibo = "";
// 	if(id_tipo_recibo == 1){
// 		nombre_recibo = "Recibo de Luz";
// 	} else if (id_tipo_recibo == 2){
// 		nombre_recibo = "Recibo de Agua";
// 	}

// 	$('#sec_con_serv_pub_title_modal_agregar_monto').html("Agregar Monto - " + nombre_recibo);	
// 	$('#sec_con_serv_pub_title_modal_agregar_monto_local').html("Local: " + nombre_local);	

// 	obtenerDatosReciboTesoreria(id_recibo, 0,'',0);
// }


// function obtenerDatosReciboTesoreria(id_recibo, id_local, periodo, id_tipo_recibo){
// 	var data = {
// 		"accion": "obtener_datos_recibo",
// 		"id_recibo": id_recibo,
// 		"id_local": id_local,
// 		"periodo": periodo,
// 		"id_tipo_recibo": id_tipo_recibo
// 	}
// 	var array_datos_recibo = [];
// 	auditoria_send({ "proceso": "obtener_datos_recibo", "data": data });
//     $.ajax({
//         url: "sys/get_contrato_servicio_publico.php",
//         type: 'POST',
//         data: data,
//         beforeSend: function() {
//             loading("true");
//         },
//         complete: function() {
//             loading();
//         },
//         success: function(resp) {
//         	// debugger;
//         	//console.log(resp);
//             var respuesta = JSON.parse(resp);
//             if (parseInt(respuesta.http_code) == 400) {
//             	alertify.error('Información: No hay datos del periodo ' + respuesta.status,5);
//             	/*$('#sec_con_serv_pub_div_datos_recibo').addClass('diabled_all_content');
//             	$('#sec_nuevo_modal_guardar_archivo').addClass('diabled_all_content');

//             	$('#sec_con_serv_pub_id_tipo_recibo').val('');
// 				$('#sec_con_serv_pub_id_local').val('');

// 				$('#sec_con_serv_pub_title_modal_agregar_monto').html('');	
// 				$('#sec_con_serv_pub_title_modal_agregar_monto_local').html('');	*/
// 				return false;
//             }

//             if (parseInt(respuesta.http_code) == 200) {
//                 $.each(respuesta.result, function(index, item) {
//                     array_datos_recibo.push(item);
//                     abrirModalServicioPublico(item.periodo_consumo);
//                 	// cargarChart(12, item.id_local, item.id_tipo_servicio_publico, item.local);
//                 	$('#sec_con_serv_pub_mensaje_imagen').html('');
// 	            	$('#sec_con_serv_pub_div_datos_recibo').removeClass('diabled_all_content');
// 	            	$('#sec_nuevo_modal_guardar_archivo').removeClass('diabled_all_content');
// 	            	$('#sec_con_serv_pub_ver_full_pantalla').prop('disabled', false);
// 				  	$('#sec_con_serv_pub_descargar_imagen_a').prop('disabled', false);
//                     array_datos_recibo.push(item);
// 					$('#sec_con_serv_pub_id_archivo').val(item.id);
// 					$('#sec_con_serv_pub_num_suministro').val(item.numero_suministro);
// 					$('#sec_con_serv_pub_num_recibo').val(item.numero_recibo);
// 					$('#sec_con_serv_pub_ruc_servicio').val(item.ruc_servicio);
// 					// $('#sec_con_serv_pub_periodo_consumo').val(obtenerMesAnioLetras(item.periodo_consumo));
// 					$('#sec_con_serv_pub_periodo_consumo').val(item.periodo_consumo);
// 					$('#sec_con_serv_pub_fecha_emision').val(item.fecha_emision);
// 					$('#sec_con_serv_pub_fecha_vencimiento').val(item.fecha_vencimiento);
//                 	$('#sec_con_serv_pub_id_tipo_compromiso_nombre').val(item.tipo_compromiso_nombre);
// 					$('#sec_con_serv_pub_id_monto_pct').val(item.monto_pct);
// 					$('#sec_con_serv_pub_monto_mes_actual').val(item.monto_total);
// 					$('#sec_con_serv_pub_total_pagar').val(item.total_pagar);
// 					//ocultos
// 					$('#sec_con_serv_pub_periodo_consumo_oculto').val(item.periodo_consumo);
// 					$('#sec_con_serv_pub_id_tipo_compromiso').val(item.tipo_compromiso);
// 					var path_img = "contratos/servicios_publicos/";
// 					if(item.id_tipo_servicio_publico == 1){ //Luz
// 						path_img += "luz/";
// 					}else{
// 						path_img += "agua/";
// 					}
// 					path_img += item.nombre_file;
// 					$('#sec_con_serv_pub_div_imagen_recibo').html('');
// 					//Validar si la imagen de la BD existe en la carpeta
// 					var nuevo_id = "sec_serv_pub_img_servicio_publico"+item.id+"_"+item.numero_suministro;
// 					if (item.extension == "pdf") {
// 						$('#sec_con_serv_pub_div_imagen_recibo').append(
// 							'<iframe src="' + path_img + '" class="col-xs-12 col-md-12 col-sm-12" width="100%" height="400"></iframe>'
// 						);

// 						$('#sec_con_serv_pub_btn_descargar_imagen_recibo').hide();
// 						$('#sec_con_serv_pub_div_VerImagenFullPantalla').hide();
						
// 					}else if (item.extension == 'jpg' || item.extension == 'png' || item.extension == 'jpeg') {
// 						$('#sec_con_serv_pub_div_imagen_recibo').append(
// 							'<div class="col-md-12">' +
// 							'   <div align="center" style="height: 100%; width: 100%;">' +
// 							'       <img  id="' + nuevo_id + '" src="' + path_img + '" width="300px" height="350px" />' +
// 							'   </div>' +
// 							'</div>'
// 						);
// 						$('#sec_con_serv_pub_ver_full_pantalla').attr('onClick', 'sec_contrato_servicio_publico_ver_imagen_full_pantalla("' + path_img + '");');
// 						$('#sec_con_serv_pub_btn_descargar_imagen_recibo').show();
// 						$('#sec_con_serv_pub_div_VerImagenFullPantalla').show();

// 						var ruta = "sec_contrato_detalle_servicio_publico_btn_descargar('"+ item.ruta_download_file +"');";

// 						$('#sec_con_serv_pub_descargar_imagen_a').attr('onClick', ruta);
// 						$("#" + nuevo_id).error(function(){
// 						  $(this).hide();
// 						  $('#sec_con_serv_pub_mensaje_imagen').html('La imagen no existe en la carpeta');
// 						  $('#sec_con_serv_pub_ver_full_pantalla').prop('disabled', true);
// 						  $('#sec_con_serv_pub_descargar_imagen_a').prop('disabled', true);
						  
// 						});
// 						//Fin de Validar si la imagen de la BD existe en la carpeta
// 					}
					

                   
//                     //Fin
//                 });
//                 return false;
//             }      
//         },
//         error: function() {}
//     });
// 	///////////FIN DE OBTENER DATOS/////////////////////////
// }


// function CancelarReciboServicioPublico() {
	
// 	var sec_con_serv_pub_fecha_pago = $('#sec_con_serv_pub_fecha_pago').val();
// 	var sec_con_serv_pub_voucher_pago = $('#sec_con_serv_pub_voucher_pago').val();

// 	if (sec_con_serv_pub_fecha_pago == "") {
// 		alertify.error('Información: Ingrese la fecha de pago',5);
// 		$('#sec_con_serv_pub_fecha_pago').focus();
// 		return false;
// 	}

// 	if (sec_con_serv_pub_voucher_pago == "") {
// 		alertify.error('Información: Seleccione un documento de pago',5);
// 		$('#sec_con_serv_pub_voucher_pago').focus();
// 		return false;
// 	}
	

// 	var dataForm = new FormData($("#Formulario_con_ser_pub_cancelar_recibo")[0]);
// 	dataForm.append("accion","pagar_servicio_publico");

// 	loading(true);

// 	$.ajax({
// 		url: "/sys/set_contrato_servicio_publico.php",
// 		type: "POST",
// 		data: dataForm,
// 		cache: false,
// 		contentType: false,
// 		processData:false,
// 		success: function(response) {
// 			var respuesta = JSON.parse(response);
// 			loading(false);
// 			if (respuesta.status) {
// 				swal({
// 					title: "Pago Realizado",
// 					text: "El pago del recibo se guardo correctamente",
// 					html:true,
// 					type: "success",
// 					timer: 1000,
// 					closeOnConfirm: false,
// 					showCancelButton: false
// 				});
// 				setTimeout(function() {
// 					window.location.href = "?sec_id=contrato&sub_sec_id=servicio_publico_tesoreria";
// 					return false;
// 				}, 1000);
// 			} else{
// 				swal({
// 					title: "Error al pagar el recibo.",
// 					text: respuesta.message,
// 					html:true,
// 					type: "warning",
// 					closeOnConfirm: false,
// 					showCancelButton: false
// 				});
// 				return false;
// 			}  
	
// 		},
// 		always: function(data){
// 			loading(false);
// 			console.log(data);

// 		}
// 	});
// }

function sec_contrato_detalle_servicio_publico_btn_descargar(ruta_archivo)
{
	var extension = "";

	// Obtener el nombre del archivo
	var ultimoPunto = ruta_archivo.lastIndexOf("/");

	if(ultimoPunto !== -1)
	{
	    var extension = ruta_archivo.substring(ultimoPunto + 1);
	}
	
	// Crear un enlace temporal
    var enlace = document.createElement('a');
    enlace.href = ruta_archivo;

    // Darle un nombre al archivo que se descargará
    enlace.download = extension;

    // Simular un clic en el enlace
    document.body.appendChild(enlace);
    enlace.click();

    // Limpiar el enlace temporal
    document.body.removeChild(enlace);
}
