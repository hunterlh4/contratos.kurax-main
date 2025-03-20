let meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
let meses_abrev = ["En", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

$("input[type='text']").on("click", function() {
    $(this).select();
});
$("input").focus(function() {
    $(this).select();
});
$("input").focusin(function() {
    $(this).select();
});



function sec_contrato_servicio_publico()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_contrato_servicio_publico_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	//sec_contrato_servicio_cargar_meses();
	//cargarMeses() 2;
	//sec_ser_pub_cargar_jefe_comercial(0) 2;
	//sec_ser_pub_cargar_supervisor2(0) 2;
	$('#sec_con_serv_pub_div_imagen_recibo').html('');
	sec_contrato_get_razon_social();
	$('.servicio_publico_datepicker')
		.datepicker({
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
	});

	$('.monto').mask('00,000.00', {reverse: true});
}

function sec_contrato_servicio_cargar_meses()
{
	
	var date = new Date();
    var meses = [];

    date.setMonth(date.getMonth());
    var fecha_anio_ = date.toISOString().substring(0, 7);
    meses.push(fecha_anio_);

    //date.setMonth(date.getMonth() - 1);
    // Los ultimos 12 meses
    for (var c = 1; i <= 12; i++)
    {
        date.setMonth(date.getMonth() - c);
        var fecha_anio = date.toISOString().substring(0, 7);
        meses.push(fecha_anio)
    }

    $('#contrato_servicio_publico_param_periodo').append(
  		'<option value="0">Todos</option>'
  	);

    $.each(meses, function (ind, elem)
    { 
		
		$('#contrato_servicio_publico_param_periodo').append(
	  		'<option '+(ind == 0 ? 'selected':'' )+' value="' + elem + '">' + obtenerAnioMesLetras(elem) + '</option>'
	  	);
	}); 
}

$('#contrato_servicio_publico_param_tipo_solicitud').change(function () 
{
	$("#contrato_servicio_publico_listar_servicios_publicos_tabla").hide();
	$("#contrato_servicio_publico_reporte_btn_listar_servicios_publicos").hide();
	$("#contrato_servicio_publico_listar_servicios_publicos_tabla_pre_concar").hide();
	$("#contrato_servicio_publico_exportar_servicios_publicos_plantilla_concar").hide();

	$(".tipo_recibo").hide();
	$(".contrato_servicio_publico_div_param_fechas").hide();
	
	var selectValor = $(this).val();

	if(selectValor == 3)
	{
		// RECIBOS SERVICIO PUBLICOS
		$("#contrato_servicio_publico_param_buscar_por").val("0").trigger("change.select2");
		sec_contrato_servicio_publico_obtener_supervisores(0, 0);
		$("#contrato_servicio_publico_param_label_fecha_inicio").html("Fecha Inicio Vencimiento");
		$("#contrato_servicio_publico_param_label_fecha_fin").html("Fecha Fin Vencimiento");
		$(".tipo_recibo").show();
		$(".contrato_servicio_publico_div_param_fechas").hide();
		$("#contrato_servicio_publico_div_param_periodo").hide();
	}
	else if(selectValor == 4)
	{
		// PLANTILLA SERVICIO PUBLICOS
		$("#contrato_servicio_publico_param_label_fecha_inicio").html("Fecha Inicio Validacion");
		$("#contrato_servicio_publico_param_label_fecha_fin").html("Fecha Fin Validacion");
		$(".contrato_servicio_publico_div_param_fechas").show();
	}
	else
	{
		alertify.error('Seleccione Tipo:',5);
		$("#contrato_servicio_publico_param_tipo_solicitud").focus();
		setTimeout(function() {
			$('#contrato_servicio_publico_param_tipo_solicitud').select2('open');
		}, 500);

		return false;
	}
});

$('#contrato_servicio_publico_param_buscar_por').change(function () 
{
	$("#contrato_servicio_publico_div_param_periodo").hide();
	$(".contrato_servicio_publico_div_param_fechas").hide();

	var selectValor = $(this).val();

	if(selectValor == 1)
	{
		$("#contrato_servicio_publico_div_param_periodo").show();
		sec_contrato_servicio_cargar_meses();
	}
	else if(selectValor == 2)
	{
		$(".contrato_servicio_publico_div_param_fechas").show();
	}
	else
	{
		alertify.error('Seleccione Buscar Por:',5);
		$("#contrato_servicio_publico_param_buscar_por").focus();
		setTimeout(function() {
			$('#contrato_servicio_publico_param_buscar_por').select2('open');
		}, 500);

		return false;
	}
});

$('#contrato_servicio_publico_param_zona').change(function () 
{
	var selectValor = $(this).val();

	sec_contrato_servicio_publico_obtener_supervisores(selectValor, 1);
});

$('#contrato_servicio_publico_param_supervisor').change(function () 
{
	var selectValor = $(this).val();

	sec_contrato_servicio_publico_obtener_locales(selectValor, 1);
});

function sec_contrato_servicio_publico_obtener_supervisores(zona_id, mostrar) 
{	
	var data = {
		"accion": "sec_contrato_servicio_publico_obtener_supervisores",
		"zona_id": zona_id
	}
	
	var array_resultado = [];
	
	$.ajax({
		url: "/sys/get_contrato_servicio_publico.php",
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
			
			if (parseInt(respuesta.http_code) == 200) 
			{
				array_resultado.push(respuesta.result);
			
				var html = '<option value="0">Todos</option>';

				for (var i = 0; i < array_resultado[0].length; i++) 
				{
					html += '<option value=' + array_resultado[0][i].id  + '>' + array_resultado[0][i].nombre + '</option>';
				}

				$("#contrato_servicio_publico_param_supervisor").html(html).trigger("change");

				if(mostrar == 1)
				{
					setTimeout(function() {
						$('#contrato_servicio_publico_param_supervisor').select2('open');
					}, 500);	
				}

				return false;
			}
			else(parseInt(respuesta.http_code) == 400) 
			{
				swal({
					title: respuesta.status,
					text: respuesta.result,
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
				return false;
			}
		},
		error: function() {}
	});
}

function sec_contrato_servicio_publico_obtener_locales(param_supervisor, mostrar) 
{
	param_zona = $("#contrato_servicio_publico_param_zona").val();

	var data = {
		"accion": "sec_contrato_servicio_publico_obtener_locales",
		"param_zona": param_zona,
		"param_supervisor": param_supervisor
	}
	
	var array_resultado = [];
	
	$.ajax({
		url: "/sys/get_contrato_servicio_publico.php",
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
			
			if (parseInt(respuesta.http_code) == 200) 
			{
				array_resultado.push(respuesta.result);
			
				var html = '<option value="0">Todos</option>';

				for (var i = 0; i < array_resultado[0].length; i++) 
				{
					html += '<option value=' + array_resultado[0][i].id  + '>' + array_resultado[0][i].nombre + '</option>';
				}

				$("#contrato_servicio_publico_param_local").html(html).trigger("change");

				if(mostrar == 1)
				{
					setTimeout(function() {
						$('#contrato_servicio_publico_param_local').select2('open');
					}, 500);	
				}

				return false;
			}
			else(parseInt(respuesta.http_code) == 400) 
			{
				swal({
					title: respuesta.status,
					text: respuesta.result,
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
				return false;
			}
		},
		error: function() {}
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

function sec_serv_pub_buscar_registros(){
	limpiarTabla();
	//permisos
	
	var permiso_monto = $('#sec_con_serv_pub_permiso_agregar_monto').html()
	let buscar_por = $('#sec_con_ser_pub_buscar_por').val();

	let tipo_servicio = $('#sec_con_ser_pub_select_tipo_servicio').val();
	let estado = $('#sec_con_ser_pub_select_estado').val();
	var pendientes = "0";
	var id_local = $('#sec_con_ser_pub_select_locales').val();
	var id_empresa = $('#sec_con_ser_pub_select_empresa').val();
	var id_jefe_comercial = $('#sec_con_ser_pub_select_jefe_comercial').val();
	var id_supervisor = $('#sec_con_ser_pub_select_supervisor').val();
	var periodo = $('#sec_con_ser_pub_select_mes').val();
	var fec_vcto_desde = $('#sec_con_serv_pub_inicio_vcto').val();
	var fec_vcto_hasta = $('#sec_con_serv_pub_fin_vcto').val();
	if(periodo == 0 && (fec_vcto_desde == "" || fec_vcto_hasta == "")){
		alertify.error('Información: Seleccione el Mes de Periodo o Seleccione el Rango de Fecha de Vencimiento',10);
		return false;
	}

	var action = "";
	if(buscar_por == 1){
		action = "obtener_registros_locales_periodo";
		if (periodo == "" || periodo == '0') {
			alertify.error('Información: Seleccione el Mes de Periodo',10);
			return false;
		}
		
		
	}
	else if(buscar_por == 2 ){
		action = "obtener_registros_locales_fechas";
		if(fec_vcto_desde == "" || fec_vcto_hasta == ""){
			alertify.error('Información: Seleccione el Rango de Fecha de Vencimiento',10);
			return false;
		}
	}

	var data = {
		"accion": action,
		"buscar_por" : buscar_por,
		"local_id": id_local,
		"id_empresa": id_empresa,
		"id_jefe_comercial" : id_jefe_comercial,
		"id_supervisor" : id_supervisor,
		"permiso_monto" : permiso_monto,
		"periodo" : periodo,
		"fec_vcto_desde" : fec_vcto_desde,
		"fec_vcto_hasta" : fec_vcto_hasta,
		"btn_pendientes" : pendientes,
		"estado" : estado,
		"tipo_servicio" : tipo_servicio,
	}

	var array_jefes_comerciales = [];
	auditoria_send({ "proceso": "obtener_registros_locales", "data": data });
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
			if (parseInt(respuesta.http_code) == 200) {
				$('#container_table_servicio_publico').html(respuesta.table);
			}
			initialize_table('sec_con_tabla_registros');
			return false;
        },
        error: function() {}
    });
}

function contrato_servicio_publico_btn_buscar()
{
	var param_tipo_solicitud = $("#contrato_servicio_publico_param_tipo_solicitud").val();

	$("#contrato_servicio_publico_listar_servicios_publicos_tabla").hide();
	$("#contrato_servicio_publico_reporte_btn_listar_servicios_publicos").hide();

	$("#contrato_servicio_publico_listar_servicios_publicos_tabla_pre_concar").hide();
	$("#contrato_servicio_publico_exportar_servicios_publicos_plantilla_concar").hide();


	if(param_tipo_solicitud == 3)
	{
		contrato_servicio_publico_buscar_servicio_publico();
	}
	else if(param_tipo_solicitud == 4)
	{
		contrato_servicio_publico_buscar_servicio_publico_pre_concar();
	}
	else
	{
		alertify.error('Seleccione Tipo:',5);
		$("#contrato_servicio_publico_param_tipo_solicitud").focus();
		setTimeout(function() {
			$('#contrato_servicio_publico_param_tipo_solicitud').select2('open');
		}, 500);

		return false;
	}
}

function contrato_servicio_publico_buscar_servicio_publico()
{
	
	if(sec_id == "contrato" && sub_sec_id == "servicio_publico")
	{
		param_buscar_por = $("#contrato_servicio_publico_param_buscar_por").val();
		param_tipo_servicio = $("#contrato_servicio_publico_param_tipo_servicio").val();
		param_empresa_arrendataria = $("#contrato_servicio_publico_param_empresa_arrendataria").val();
		param_zona = $("#contrato_servicio_publico_param_zona").val();
		param_supervisor = $("#contrato_servicio_publico_param_supervisor").val();
		param_local = $("#contrato_servicio_publico_param_local").val();
		param_fecha_incio = $("#contrato_servicio_publico_param_fecha_inicio").val();
		param_fecha_fin = $("#contrato_servicio_publico_param_fecha_fin").val();
		param_periodo = $("#contrato_servicio_publico_param_periodo").val();
		param_estado = $("#contrato_servicio_publico_param_estado").val();

		if(param_buscar_por == 0)
		{
			alertify.error('Seleccione Buscar Por:',5);
			$("#contrato_servicio_publico_param_buscar_por").focus();
			setTimeout(function() {
				$('#contrato_servicio_publico_param_buscar_por').select2('open');
			}, 500);

			return false;
		}
		else if(param_buscar_por == 2)
		{
			if(param_fecha_incio == "")
			{
				alertify.error('Seleccione Fecha Inicio:',5);
				$("#contrato_servicio_publico_param_fecha_inicio").focus();

				return false;
			}

			if(param_fecha_fin == "")
			{
				alertify.error('Seleccione Fecha Fin:',5);
				$("#contrato_servicio_publico_param_fecha_fin").focus();

				return false;
			}
		}

		var data = {
			"accion": "contrato_servicio_publico_buscar_servicio_publico",
			"param_buscar_por" : param_buscar_por,
			"param_tipo_servicio" : param_tipo_servicio,
			"param_empresa_arrendataria" : param_empresa_arrendataria,
			"param_zona" : param_zona,
			"param_supervisor" : param_supervisor,
			"param_local" : param_local,
			"param_fecha_incio" : param_fecha_incio,
			"param_fecha_fin" : param_fecha_fin,
			"param_periodo" : param_periodo,
			"param_estado" : param_estado
		}

		$("#contrato_servicio_publico_listar_servicios_publicos_tabla").show();
		$("#contrato_servicio_publico_reporte_btn_listar_servicios_publicos").show();
		
		tabla = $("#contrato_servicio_publico_listar_servicios_publicos_datatable").dataTable(
		{
			language:{
				"decimal":        "",
				"emptyTable":     "No existen registros",
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
			},
			"aProcessing" : true,
			"aServerSide" : true,

			"ajax" :
			{
				url : "/sys/get_contrato_servicio_publico.php",
				data : data,
				type : "POST",
				dataType : "json",
				beforeSend: function() {
					loading("true");
				},
				complete: function() {
					loading();
				},
				error : function(e)
				{
					console.log(e.responseText);
				}
			},
			columnDefs: [
				{
					className: 'text-center',
					targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
				}
			],
			"columns" : [
				{"aaData" : "0"},
				{"aaData" : "1"},
				{"aaData" : "2"},
				{"aaData" : "3"},
				{"aaData" : "4"},
				{"aaData" : "5"},
				{"aaData" : "6"},
				{"aaData" : "7"},
				{"aaData" : "8"},
				{"aaData" : "9"},
				{"aaData" : "10"},
				{"aaData" : "11"}
			],
			"bDestroy" : true,
			aLengthMenu:[10, 20, 30, 40, 50, 100],
			scrollX: true,
			scrollCollapse: true
		}).DataTable();

		setTimeout(function(){
			$('#contrato_servicio_publico_param_tipo_solicitud').select2('open');
		}, 500);
	}
	else
	{
		alertify.error('No se encuentra en la vista correspondiente a la búsqueda.',5);
		return false;
	}
}

$("#contrato_servicio_publico_reporte_btn_listar_servicios_publicos").on('click', function () 
{
    
    param_buscar_por = $("#contrato_servicio_publico_param_buscar_por").val();
	param_tipo_servicio = $("#contrato_servicio_publico_param_tipo_servicio").val();
	param_empresa_arrendataria = $("#contrato_servicio_publico_param_empresa_arrendataria").val();
	param_zona = $("#contrato_servicio_publico_param_zona").val();
	param_supervisor = $("#contrato_servicio_publico_param_supervisor").val();
	param_local = $("#contrato_servicio_publico_param_local").val();
	param_fecha_incio = $("#contrato_servicio_publico_param_fecha_inicio").val();
	param_fecha_fin = $("#contrato_servicio_publico_param_fecha_fin").val();
	param_periodo = $("#contrato_servicio_publico_param_periodo").val();
	param_estado = $("#contrato_servicio_publico_param_estado").val();

	if(param_buscar_por == 0)
	{
		alertify.error('Seleccione Buscar Por:',5);
		$("#contrato_servicio_publico_param_buscar_por").focus();
		setTimeout(function() {
			$('#contrato_servicio_publico_param_buscar_por').select2('open');
		}, 500);

		return false;
	}
	else if(param_buscar_por == 2)
	{
		if(param_fecha_incio == "")
		{
			alertify.error('Seleccione Fecha Inicio:',5);
			$("#contrato_servicio_publico_param_fecha_inicio").focus();

			return false;
		}

		if(param_fecha_fin == "")
		{
			alertify.error('Seleccione Fecha Fin:',5);
			$("#contrato_servicio_publico_param_fecha_fin").focus();

			return false;
		}
	}

	var data = {
		"accion": "contrato_servicio_publico_reporte_btn_listar_servicios_publicos",
		"param_buscar_por" : param_buscar_por,
		"param_tipo_servicio" : param_tipo_servicio,
		"param_empresa_arrendataria" : param_empresa_arrendataria,
		"param_zona" : param_zona,
		"param_supervisor" : param_supervisor,
		"param_local" : param_local,
		"param_fecha_incio" : param_fecha_incio,
		"param_fecha_fin" : param_fecha_fin,
		"param_periodo" : param_periodo,
		"param_estado" : param_estado
	}

    $.ajax({
        url: "/sys/get_contrato_servicio_publico.php",
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
            auditoria_send({ "respuesta": "contrato_servicio_publico_reporte_btn_listar_servicios_publicos", "data": obj });

            window.open(obj.ruta_archivo);
            loading(false);
        },
        error: function(resp, status) {

        }
    });
});

function contrato_servicio_publico_buscar_servicio_publico_pre_concar()
{
	
	if(sec_id == "contrato" && sub_sec_id == "servicio_publico")
	{
		var param_tipo_empresa = $("#contrato_servicio_publico_param_tipo_empresa").val();
		var param_fecha_incio = $("#contrato_servicio_publico_param_fecha_inicio").val();
		var param_fecha_fin = $("#contrato_servicio_publico_param_fecha_fin").val();
		
		if(param_tipo_empresa == 0)
		{
			alertify.error('Seleccione Empresa:',5);
			$("#contrato_servicio_publico_param_tipo_empresa").focus();
			setTimeout(function() {
				$('#contrato_servicio_publico_param_tipo_empresa').select2('open');
			}, 500);

			return false;
		}

		if(param_fecha_incio == "")
		{
			alertify.error('Seleccione Fecha Inicio:',5);
			$("#contrato_servicio_publico_param_fecha_inicio").focus();

			return false;
		}

		if(param_fecha_fin == "")
		{
			alertify.error('Seleccione Fecha Fin:',5);
			$("#contrato_servicio_publico_param_fecha_fin").focus();

			return false;
		}

		var data = {
			"accion": "contrato_servicio_publico_buscar_servicio_publico_pre_concar",
			"param_tipo_empresa" : param_tipo_empresa,
			"param_fecha_incio" : param_fecha_incio,
			"param_fecha_fin" : param_fecha_fin
		}

		$("#contrato_servicio_publico_listar_servicios_publicos_tabla_pre_concar").show();
		$("#contrato_servicio_publico_exportar_servicios_publicos_plantilla_concar").show();
		
		tabla = $("#contrato_servicio_publico_listar_servicios_publicos_pre_concar_datatable").dataTable(
		{
			language:{
				"decimal":        "",
				"emptyTable":     "No existen registros",
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
			},
			"aProcessing" : true,
			"aServerSide" : true,

			"ajax" :
			{
				url : "/sys/get_contrato_servicio_publico.php",
				data : data,
				type : "POST",
				dataType : "json",
				beforeSend: function() {
					loading("true");
				},
				complete: function() {
					loading();
				},
				error : function(e)
				{
					console.log(e.responseText);
				}
			},
			columnDefs: [
				{
					className: 'text-center',
					targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
				}
			],
			"columns" : [
				{"aaData" : "0"},
				{"aaData" : "1"},
				{"aaData" : "2"},
				{"aaData" : "3"},
				{"aaData" : "4"},
				{"aaData" : "5"},
				{"aaData" : "6"},
				{"aaData" : "7"},
				{"aaData" : "8"},
				{"aaData" : "9"},
				{"aaData" : "10"},
				{"aaData" : "11"}
			],
			"bDestroy" : true,
			aLengthMenu:[30, 50, 100],
			scrollX: true,
			scrollCollapse: true
		}).DataTable();

		setTimeout(function(){
			$('#contrato_servicio_publico_param_tipo_solicitud').select2('open');
		}, 500);
	}
	else
	{
		alertify.error('No se encuentra en la vista correspondientea la búsqueda.',5);
		return false;
	}
}

$("#contrato_servicio_publico_exportar_servicios_publicos_plantilla_concar").off("click").on("click",function(){
    
    $("#contrato_servicio_publico_modal_parametro_plantilla_concar").modal("show");
})

$("#contrato_servicio_publico_modal_parametro_plantilla_concar .btn_guardar").off("click").on("click",function(){
    
    var param_tipo_empresa = $("#contrato_servicio_publico_param_tipo_empresa").val();
    var param_fecha_incio = $("#contrato_servicio_publico_param_fecha_inicio").val();
	var param_fecha_fin = $("#contrato_servicio_publico_param_fecha_fin").val();
    var modal_param_num_correlativo = $('#contrato_servicio_publico_modal_param_num_correlativo').val();
    var modal_param_fecha_comprobante = $('#contrato_servicio_publico_modal_param_fecha_comprobante').val();

    if(modal_param_num_correlativo == "")
    {
        alertify.error('Seleccione Número Correlativo',5);
        $("#contrato_servicio_publico_modal_param_num_correlativo").focus();
        
        return false;
    }

    if(modal_param_fecha_comprobante == "")
	{
		alertify.error('Seleccione Fecha de Comprobante:',5);
		$("#contrato_servicio_publico_modal_param_fecha_comprobante").focus();

		return false;
	}

	if(param_tipo_empresa == 0)
	{
		alertify.error('Seleccione Empresa:',5);
		$("#contrato_servicio_publico_param_tipo_empresa").focus();
		setTimeout(function() {
			$('#contrato_servicio_publico_param_tipo_empresa').select2('open');
		}, 500);

		return false;
	}

	if(param_fecha_incio == "")
	{
		alertify.error('Seleccione Fecha Inicio:',5);
		$("#contrato_servicio_publico_param_fecha_inicio").focus();

		return false;
	}

	if(param_fecha_fin == "")
	{
		alertify.error('Seleccione Fecha Fin:',5);
		$("#contrato_servicio_publico_param_fecha_fin").focus();

		return false;
	}

	var data = {
        "accion": "contrato_servicio_publico_modal_parametro_plantilla_concar",
        "param_tipo_empresa" : param_tipo_empresa,
        "param_fecha_incio" : param_fecha_incio,
        "param_fecha_fin" : param_fecha_fin,
        "modal_param_num_correlativo" : modal_param_num_correlativo,
        "modal_param_fecha_comprobante" : modal_param_fecha_comprobante
    }
		
	$.ajax({
        url: "/sys/get_contrato_servicio_publico.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
			console.log(resp)
            
            let obj = JSON.parse(resp);
            if(parseInt(obj.estado_archivo) == 1)
            {
                window.open(obj.ruta_archivo);
                loading(false);    
            }
            else if(parseInt(obj.estado_archivo) == 0)
            {
                swal({
                    title: "Error al Generar el Concar",
                    text: obj.ruta_archivo,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
            else
            {
                swal({
                    title: "Error",
                    text: "Ponerse en contacto con Soporte",
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
            
        },
        error: function(resp, status) {

        }
    });
})

function sec_serv_pub_buscar_registros_tesoreria(){
	limpiarTabla();
	//permisos
	
	var permiso_monto = $('#sec_con_serv_pub_permiso_agregar_monto').html()
	let buscar_por = $('#sec_con_ser_pub_buscar_por').val();

	let tipo_servicio = $('#sec_con_ser_pub_select_tipo_servicio').val();
	let estado = $('#sec_con_ser_pub_select_estado').val();
	var id_local = $('#sec_con_ser_pub_select_locales').val();
	var id_empresa = $('#sec_con_ser_pub_select_empresa').val();
	var id_jefe_comercial = $('#sec_con_ser_pub_select_jefe_comercial').val();
	var id_supervisor = $('#sec_con_ser_pub_select_supervisor').val();
	var periodo = $('#sec_con_ser_pub_select_mes').val();
	var fec_vcto_desde = $('#sec_con_serv_pub_inicio_vcto').val();
	var fec_vcto_hasta = $('#sec_con_serv_pub_fin_vcto').val();

	if(buscar_por == 1){
		if (periodo == "" || periodo == '0') {
			alertify.error('Información: Seleccione el Mes de Periodo',10);
			return false;
		}
		
	}
	else if(buscar_por == 2 ){
		if(fec_vcto_desde == "" || fec_vcto_hasta == ""){
			alertify.error('Información: Seleccione el Rango de Fecha de Vencimiento',10);
			return false;
		}
	}
	var data = {
		"accion":  "obtener_registros_locales_tesoreria",
		"buscar_por" : buscar_por,
		"local_id": id_local,
		"id_empresa": id_empresa,
		"id_jefe_comercial" : id_jefe_comercial,
		"id_supervisor" : id_supervisor,
		"permiso_monto" : permiso_monto,
		"periodo" : periodo,
		"fec_vcto_desde" : fec_vcto_desde,
		"fec_vcto_hasta" : fec_vcto_hasta,
		"estado" : estado,
		"tipo_servicio" : tipo_servicio,
	}

	var array_jefes_comerciales = [];
	auditoria_send({ "proceso": "obtener_registros_locales", "data": data });
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
			if (parseInt(respuesta.http_code) == 200) {
				$('#container_table_servicio_publico').html(respuesta.table);
			}
			initialize_table('sec_con_tabla_registros');
        },
        error: function() {}
    });
}

function contrato_servicio_publico_buscar_servicio_publico_tesoreria()
{
	
	if(sec_id == "contrato" && sub_sec_id == "servicio_publico_tesoreria")
	{
		param_buscar_por = $("#contrato_servicio_publico_param_buscar_por").val();
		param_tipo_servicio = $("#contrato_servicio_publico_param_tipo_servicio").val();
		param_empresa_arrendataria = $("#contrato_servicio_publico_param_empresa_arrendataria").val();
		param_zona = $("#contrato_servicio_publico_param_zona").val();
		param_supervisor = $("#contrato_servicio_publico_param_supervisor").val();
		param_local = $("#contrato_servicio_publico_param_local").val();
		param_fecha_incio = $("#contrato_servicio_publico_param_fecha_inicio").val();
		param_fecha_fin = $("#contrato_servicio_publico_param_fecha_fin").val();
		param_periodo = $("#contrato_servicio_publico_param_periodo").val();
		param_estado = $("#contrato_servicio_publico_param_estado").val();

		if(param_buscar_por == 0)
		{
			alertify.error('Seleccione Buscar Por:',5);
			$("#contrato_servicio_publico_param_buscar_por").focus();
			setTimeout(function() {
				$('#contrato_servicio_publico_param_buscar_por').select2('open');
			}, 500);

			return false;
		}
		else if(param_buscar_por == 2)
		{
			if(param_fecha_incio == "")
			{
				alertify.error('Seleccione Fecha Inicio:',5);
				$("#contrato_servicio_publico_param_fecha_inicio").focus();

				return false;
			}

			if(param_fecha_fin == "")
			{
				alertify.error('Seleccione Fecha Fin:',5);
				$("#contrato_servicio_publico_param_fecha_fin").focus();

				return false;
			}
		}

		var data = {
			"accion": "contrato_servicio_publico_buscar_servicio_publico_tesoreria",
			"param_buscar_por" : param_buscar_por,
			"param_tipo_servicio" : param_tipo_servicio,
			"param_empresa_arrendataria" : param_empresa_arrendataria,
			"param_zona" : param_zona,
			"param_supervisor" : param_supervisor,
			"param_local" : param_local,
			"param_fecha_incio" : param_fecha_incio,
			"param_fecha_fin" : param_fecha_fin,
			"param_periodo" : param_periodo,
			"param_estado" : param_estado
		}

		$("#contrato_servicio_publico_listar_servicios_publicos_tesoreria_tabla").show();
		$("#contrato_servicio_publico_reporte_btn_listar_servicios_publicos_tesoreria").show();
		
		tabla = $("#contrato_servicio_publico_listar_servicios_publicos_tesoreria_datatable").dataTable(
		{
			language:{
				"decimal":        "",
				"emptyTable":     "No existen registros",
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
			},
			"aProcessing" : true,
			"aServerSide" : true,

			"ajax" :
			{
				url : "/sys/get_contrato_servicio_publico.php",
				data : data,
				type : "POST",
				dataType : "json",
				beforeSend: function() {
					loading("true");
				},
				complete: function() {
					loading();
				},
				error : function(e)
				{
					console.log(e.responseText);
				}
			},
			columnDefs: [
				{
					className: 'text-center',
					targets: [0, 4, 5]
				}
			],
			"columns" : [
				{"aaData" : "0"},
				{"aaData" : "1"},
				{"aaData" : "2"},
				{"aaData" : "3"},
				{"aaData" : "4"},
				{"aaData" : "5"},
				{"aaData" : "6"},
				{"aaData" : "7"},
				{"aaData" : "8"},
				{"aaData" : "9"},
				{"aaData" : "10"},
				{"aaData" : "11"},
				{"aaData" : "11"},
				{"aaData" : "11"}
			],
			"bDestroy" : true,
			aLengthMenu:[10, 20, 30, 40, 50, 100],
			scrollX: true,
			scrollCollapse: true
		}).DataTable();
	}
	else
	{
		alertify.error('No se encuentra en la vista correspondientea la búsqueda.',5);
		return false;
	}
}

$("#contrato_servicio_publico_reporte_btn_listar_servicios_publicos_tesoreria").on('click', function () 
{
    
    param_buscar_por = $("#contrato_servicio_publico_param_buscar_por").val();
	param_tipo_servicio = $("#contrato_servicio_publico_param_tipo_servicio").val();
	param_empresa_arrendataria = $("#contrato_servicio_publico_param_empresa_arrendataria").val();
	param_zona = $("#contrato_servicio_publico_param_zona").val();
	param_supervisor = $("#contrato_servicio_publico_param_supervisor").val();
	param_local = $("#contrato_servicio_publico_param_local").val();
	param_fecha_incio = $("#contrato_servicio_publico_param_fecha_inicio").val();
	param_fecha_fin = $("#contrato_servicio_publico_param_fecha_fin").val();
	param_periodo = $("#contrato_servicio_publico_param_periodo").val();
	param_estado = $("#contrato_servicio_publico_param_estado").val();

	if(param_buscar_por == 0)
	{
		alertify.error('Seleccione Buscar Por:',5);
		$("#contrato_servicio_publico_param_buscar_por").focus();
		setTimeout(function() {
			$('#contrato_servicio_publico_param_buscar_por').select2('open');
		}, 500);

		return false;
	}
	else if(param_buscar_por == 2)
	{
		if(param_fecha_incio == "")
		{
			alertify.error('Seleccione Fecha Inicio:',5);
			$("#contrato_servicio_publico_param_fecha_inicio").focus();

			return false;
		}

		if(param_fecha_fin == "")
		{
			alertify.error('Seleccione Fecha Fin:',5);
			$("#contrato_servicio_publico_param_fecha_fin").focus();

			return false;
		}
	}

	var data = {
		"accion": "contrato_servicio_publico_reporte_btn_listar_servicios_publicos_tesoreria",
		"param_buscar_por" : param_buscar_por,
		"param_tipo_servicio" : param_tipo_servicio,
		"param_empresa_arrendataria" : param_empresa_arrendataria,
		"param_zona" : param_zona,
		"param_supervisor" : param_supervisor,
		"param_local" : param_local,
		"param_fecha_incio" : param_fecha_incio,
		"param_fecha_fin" : param_fecha_fin,
		"param_periodo" : param_periodo,
		"param_estado" : param_estado
	}

    $.ajax({
        url: "/sys/get_contrato_servicio_publico.php",
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
            auditoria_send({ "respuesta": "contrato_servicio_publico_reporte_btn_listar_servicios_publicos_tesoreria", "data": obj });

            window.open(obj.ruta_archivo);
            loading(false);
        },
        error: function(resp, status) {

        }
    });
});

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
	$('#sec_con_serv_pub_serie').val('');
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

function agregarMonto(id_tipo_recibo, id_local , consumo_per, nombre_local, id_recibo)
{
	
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

function sec_contrato_servicio_publico_guardar_nuevo_monto_recibo(accion)
{
	
	var num_recibo = $('#sec_con_serv_pub_num_recibo').val();
	var id_archivo = $('#sec_con_serv_pub_id_archivo').val();

	//Monto del recibo
	if(num_recibo == "" || num_recibo == 0){
		alertify.error('Debe ingresar el número de recibo',5);
		return false;
	}else {

		var data = {
			'accion': 'verificar_num_recibo',
			'num_recibo': num_recibo,
			'id_archivo': id_archivo
		};

		$.ajax({
			url: "sys/get_contrato_servicio_publico.php",
			data : data,
			type : "POST",
			beforeSend: function() {
				loading("true");
			},
			complete: function() {
				loading();
			},
			success: function(resp){
				var respuesta = JSON.parse(resp);
		        if (parseInt(respuesta.http_code) == 400) {
					continuValidate(accion);
		            }

		         if (parseInt(respuesta.http_code) == 200) {
					alertify.error(respuesta.titulo,5);
					return false;
		            }   		
			},
			error: function(){
				alert('failure');
			  }
		});
	
	}
	
}

function continuValidate(accion)
{
	
	var id_archivo = $('#sec_con_serv_pub_id_archivo').val();
	var monto_total = $('#sec_con_serv_pub_monto_mes_actual').val();
	var num_recibo = $('#sec_con_serv_pub_num_recibo').val();
	var serie = $('#sec_con_serv_pub_serie').val();
	var total_pagar = $('#sec_con_serv_pub_total_pagar').val().trim();

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

	if(serie.length == 0){
		alertify.error('Debe ingresar un número de serie',5);
		return false;
	}

	if(fecha_vencimiento.length == 0){
		alertify.error('Debe ingresar la fecha de vencimiento',5);
		return false;
	}

	if(monto_total == "" || monto_total == 0){
		alertify.error('Debe ingresar el monto correcto',5);
		return false;
	}

	if(total_pagar == "" || total_pagar == 0)
	{
		alertify.error('Debe ingresar el Total a Pagar',5);
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

	if(accion != 'editar'){
		titulo= "Validar";
		aviso = "¿Está seguro de validar los datos para el servicio?";
		if(aplica_caja_chica == 1)
		{
			// CAJA CHICA
			estado = 4;
		}

		if(tipo_compromiso == 7)
		{
			// FACTURA
			estado = 5;
		}
	}else{
		estado = 1;
		titulo= "Guardar";
		aviso = "¿Está seguro de guardar los datos para el servicio?";
	}
	//Pregunta si desea guardar el monto
	swal({
			title: titulo,
			text: aviso,
			type: "warning",
			showCancelButton: true,
			cancelButtonText: "NO",
			confirmButtonColor: "#529D73",
			confirmButtonText: "SI",
			closeOnConfirm: false
		},
		function (isConfirm) {
			if(isConfirm){
				
				var data = {
					"accion" : "guardar_monto_servicio_publico",
					"monto_total" : monto_total,
					"num_recibo" : num_recibo,
					"serie" : serie,
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
							$('#sec_con_serv_pub_modal_agregar_monto').modal('hide');
							$('#sec_con_ser_pub_agregar_monto_recibo')[0].reset();
							contrato_servicio_publico_btn_buscar();
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
	
	var id_tipo_recibo = $('#sec_con_serv_pub_id_tipo_recibo').val();
	var id_local = $('#sec_con_serv_pub_id_local').val();
	var periodo_actual = $('#sec_con_serv_pub_periodo_busqueda_data').val();

	var actual = new Date(parseInt(periodo_actual.substring(0, 4)), parseInt(periodo_actual.substring(5, 7)) - 1, 1);
	var nav = new Date(actual.setMonth(actual.getMonth() + numero));
	var mes = (nav.getMonth() + 1);
	if(mes < 10){ mes = "0" + mes;}
	
	var periodo = nav.getFullYear() + '-' + mes + '-01';


	if(id_tipo_recibo == ""){ alertify.error('Información: id_tipo_recibo vacio',5); return;}
	if(id_local == ""){ alertify.error('Información: id_tipo_recibo vacio',5); return;}

	obtenerDatosRecibo(0, id_local, periodo, id_tipo_recibo);
}

function obtenerDatosRecibo(id_recibo, id_local, periodo, id_tipo_recibo){
	
	var data = {
		"accion": "obtener_datos_recibo",
		"id_recibo": id_recibo,
		"id_local": id_local,
		"periodo": periodo,
		"id_tipo_recibo": id_tipo_recibo
	}
	var array_datos_recibo = [];
	auditoria_send({ "proceso": "obtener_datos_recibo", "data": data });
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
            if (parseInt(respuesta.http_code) == 400)
            {
            	alertify.error('Información: No hay datos del periodo ' + respuesta.status,5);
            	/*$('#sec_con_serv_pub_div_datos_recibo').addClass('diabled_all_content');
            	$('#sec_nuevo_modal_guardar_archivo').addClass('diabled_all_content');

            	$('#sec_con_serv_pub_id_tipo_recibo').val('');
				$('#sec_con_serv_pub_id_local').val('');

				$('#sec_con_serv_pub_title_modal_agregar_monto').html('');	
				$('#sec_con_serv_pub_title_modal_agregar_monto_local').html('');	*/
				return false;
            }
            else if(parseInt(respuesta.http_code) == 200)
            {
                $.each(respuesta.result, function(index, item)
                {
                    array_datos_recibo.push(item);
                    abrirModalServicioPublico(item.periodo_consumo);
                	cargarChart(12, item.id_local, item.id_tipo_servicio_publico, item.local);
                	$('#sec_con_serv_pub_mensaje_imagen').html('');
	            	$('#sec_con_serv_pub_div_datos_recibo').removeClass('diabled_all_content');
	            	$('#sec_nuevo_modal_guardar_archivo').removeClass('diabled_all_content');
					$('#sec_nuevo_modal_editar_archivo').removeClass('diabled_all_content');
	            	$('#sec_con_serv_pub_ver_full_pantalla').prop('disabled', false);
				  	$('#sec_con_serv_pub_descargar_imagen_a').prop('disabled', false);
					$('#div_con_serv_pub_comentario').hide();
					if (item.comentario != "")
					{
						$('#div_con_serv_pub_comentario').show();
					}
                    array_datos_recibo.push(item);
					$('#sec_con_serv_pub_id_archivo').val(item.id);
					$('#sec_con_serv_pub_num_suministro').val(item.numero_suministro);
					$('#sec_con_serv_pub_num_recibo').val(item.numero_recibo);
					$('#sec_con_serv_pub_serie').val(item.serie);
					// $('#sec_con_serv_pub_periodo_consumo').val(obtenerMesAnioLetras(item.periodo_consumo));
					$('#sec_con_serv_pub_periodo_consumo').val(item.periodo_consumo);
					$('#sec_con_serv_pub_fecha_emision').val(item.fecha_emision);
					$('#sec_con_serv_pub_comentario').val(item.comentario);
					$('#sec_con_serv_pub_fecha_vencimiento').val(item.fecha_vencimiento);
                	$('#sec_con_serv_pub_id_tipo_compromiso_nombre').val(item.tipo_compromiso_nombre);
					$('#sec_con_serv_pub_id_monto_pct').val(item.monto_pct);
					$('#sec_con_serv_pub_monto_mes_actual').val(item.monto_total);
					$('#sec_con_serv_pub_total_pagar').val(item.total_pagar);
					if(item.estado_recibo == 4){
						//Mostrar cuadro para ingresar nombre de persona a la que se le está pagando
						$('#sec_con_serv_pub_div_nombre_pagar_hide').removeClass('ocultar_div');
						console.log(item.nombre_paga_caja_chica);
						$('#sec_con_serv_pub_nombre_pagar').val(item.nombre_paga_caja_chica);
						var checkbox = document.getElementById('sec_con_serv_pub_check_cajachica_id');
						checkbox.checked = true;

						$('#btn_nombre_guardar_modal').html('Caja Chica');
					}else{
						// Ocultar cuadro
						$('#sec_con_serv_pub_div_nombre_pagar_hide').addClass('ocultar_div');
						$('#sec_con_serv_pub_nombre_pagar').val('');
						var checkbox = document.getElementById('sec_con_serv_pub_check_cajachica_id');
						checkbox.checked = false;
						$('#btn_nombre_guardar_modal').html('Validar');
					}
					//ocultos
					$('#sec_con_serv_pub_periodo_consumo_oculto').val(item.periodo_consumo);
					$('#sec_con_serv_pub_id_tipo_compromiso').val(item.tipo_compromiso);
					
					var path_img = "files_bucket/contratos/servicios_publicos/";
					
					if(item.id_tipo_servicio_publico == 1)
					{ 
						//Luz
						path_img += "luz/";
					}
					else
					{
						path_img += "agua/";
					}

					path_img += item.nombre_file;

					$('#sec_con_serv_pub_div_imagen_recibo').html('');
					
					//Validar si la imagen de la BD existe en la carpeta
					var nuevo_id = "sec_serv_pub_img_servicio_publico"+item.id+"_"+item.numero_suministro;
					
					if (item.extension == "pdf")
					{
						$('#sec_con_serv_pub_div_imagen_recibo').append(
							'<iframe src="' + path_img + '" class="col-xs-12 col-md-12 col-sm-12" width="100%" height="400"></iframe>'
						);

						$('#sec_con_serv_pub_btn_descargar_imagen_recibo').hide();
						$('#sec_con_serv_pub_div_VerImagenFullPantalla').hide();
						
					}
					else if (item.extension == 'jpg' || item.extension == 'png' || item.extension == 'jpeg')
					{
						$('#sec_con_serv_pub_div_imagen_recibo').append(
							'<div class="col-md-12">' +
							'   <div align="center" style="height: 100%; width: 100%;">' +
							'       <img  id="' + nuevo_id + '" src="' + path_img + '" width="300px" height="350px" />' +
							'   </div>' +
							'</div>'
						);

						$('#sec_con_serv_pub_ver_full_pantalla').attr('onClick', 'sec_contrato_servicio_publico_ver_imagen_full_pantalla("' + path_img + '");');
						$('#sec_con_serv_pub_btn_descargar_imagen_recibo').show();
						$('#sec_con_serv_pub_div_VerImagenFullPantalla').show();
						
						var ruta = "sec_contrato_servicio_publico_btn_descargar('"+item.ruta_download_file+"');";
						
						$('#sec_con_serv_pub_descargar_imagen_a').attr('onClick', ruta);
						$("#" + nuevo_id).error(function(){
						  $(this).hide();
						  $('#sec_con_serv_pub_mensaje_imagen').html('La imagen no existe en la carpeta');
						  $('#sec_con_serv_pub_ver_full_pantalla').prop('disabled', true);
						  $('#sec_con_serv_pub_descargar_imagen_a').prop('disabled', true);
						  
						});
						//Fin de Validar si la imagen de la BD existe en la carpeta
					}

					// INICIO: SI ES CONTOMETRO
					if(item.tipo_compromiso == 4)
					{
						$("#sec_contrato_servicio_publico_modal_agregar_monto_div_file_contometro").show();

						$('#sec_con_serv_pub_div_imagen_recibo_contometro').html('');
						
						//Validar si la imagen de la BD existe en la carpeta
						var nuevo_id = "sec_serv_pub_img_contometro_servicio_publico"+item.id+"_"+item.numero_suministro;
						
						if (item.extension_contometro == "pdf")
						{
							$('#sec_con_serv_pub_div_imagen_recibo_contometro').append(
								'<iframe src="' + item.ruta_download_file_contometro + '" class="col-xs-12 col-md-12 col-sm-12" width="100%" height="400"></iframe>'
							);

							$('#sec_con_serv_pub_btn_descargar_imagen_recibo_contometro').hide();
							$('#sec_con_serv_pub_div_VerImagenFullPantalla_contometro').hide();
							
						}
						else if (item.extension_contometro == 'jpg' || item.extension_contometro == 'png' || item.extension_contometro == 'jpeg')
						{
							$('#sec_con_serv_pub_div_imagen_recibo_contometro').append(
								'<div class="col-md-12">' +
								'   <div align="center" style="height: 100%; width: 100%;">' +
								'       <img  id="' + nuevo_id + '" src="' + item.ruta_download_file_contometro + '" width="300px" height="350px" />' +
								'   </div>' +
								'</div>'
							);

							$('#sec_con_serv_pub_ver_full_pantalla_contometro').attr('onClick', 'sec_contrato_servicio_publico_ver_imagen_full_pantalla("' + item.ruta_download_file_contometro + '");');
							$('#sec_con_serv_pub_btn_descargar_imagen_recibo_contometro').show();
							$('#sec_con_serv_pub_div_VerImagenFullPantalla_contometro').show();
							
							var ruta = "sec_contrato_servicio_publico_btn_descargar('"+ item.ruta_download_file_contometro +"');";
							
							$('#sec_con_serv_pub_descargar_imagen_a_contometro').attr('onClick', ruta);
							$("#" + nuevo_id).error(function(){
							  $(this).hide();
							  $('#sec_con_serv_pub_mensaje_imagen_contometro').html('La imagen no existe en la carpeta');
							  $('#sec_con_serv_pub_ver_full_pantalla_contometro').prop('disabled', true);
							  $('#sec_con_serv_pub_descargar_imagen_a_contometro').prop('disabled', true);
							  
							});
							//Fin de Validar si la imagen de la BD existe en la carpeta
						}

					}
					else
					{
						$("#sec_contrato_servicio_publico_modal_agregar_monto_div_file_contometro").hide();
					}
					// FIN: SI ES CONTOMETRO

					// tipo_compromiso:
					// 1: Porcentaje (%)
					// 2: Monto fijo (S/.)
					// 3: Medidor propio (Totalidad del servicio)
					// 4: Contometro
					// 5: Compartido
					// 6: Excedente - Monto Base
					// 7: Factura
					// 8: No se paga

                    //Inicio compromiso monto fijo
                    var tipo_compromiso = $('#sec_con_serv_pub_id_tipo_compromiso').val();
					var monto_pct = $('#sec_con_serv_pub_id_monto_pct').val();
					var total_pagar = $('#sec_con_serv_pub_total_pagar').val().trim();

					if(tipo_compromiso == 2)
					{
						//monto fijo
						$("#sec_con_serv_pub_total_pagar").val(monto_pct);
					}
					else if(tipo_compromiso == 1 || tipo_compromiso == 5 || tipo_compromiso == 6)
					{
						$('#sec_con_serv_pub_total_pagar').prop('disabled', false);
					}
					else
					{
						if(total_pagar !=0 )
						{
							$("#sec_con_serv_pub_total_pagar").val(total_pagar);
						}else{
							$("#sec_con_serv_pub_total_pagar").val(0.00);
						}
						$('#sec_con_serv_pub_total_pagar').prop('disabled', true);
					}
					if(item.estado_recibo == 1 || item.estado_recibo == 6){ //PENDIENTE U OBSERVADO
						$('#sec_con_serv_pub_total_pagar').prop('disabled', false);
						$('#sec_con_serv_pub_num_recibo').prop('disabled', false);
						$('#sec_con_serv_pub_monto_mes_actual').prop('disabled', false);
						$('#sec_nuevo_modal_observar_archivo').prop('disabled', false);
						$('#sec_nuevo_modal_guardar_archivo').prop('disabled', false);
						$('#sec_nuevo_modal_editar_archivo').prop('disabled', false);
						$('#sec_con_serv_pub_check_cajachica_id').prop('disabled', false);
					}
					if(item.estado_recibo == 2 || item.estado_recibo == 3 || item.estado_recibo == 4 || item.estado_recibo == 5){ //VALIDADO|PAGADO|CAJA CHICA|FACTURA
						$('#sec_nuevo_modal_observar_archivo').prop('disabled', true);
						$('#sec_nuevo_modal_guardar_archivo').prop('disabled', true);
						if(item.estado_recibo == 2 ){
							$('#sec_nuevo_modal_editar_archivo').prop('disabled', false);
							$('#sec_con_serv_pub_num_recibo').prop('disabled', false);
							$('#sec_con_serv_pub_total_pagar').prop('disabled', false);
							$('#sec_con_serv_pub_monto_mes_actual').prop('disabled', false);
						}else{
							$('#sec_nuevo_modal_editar_archivo').prop('disabled', true);
							$('#sec_con_serv_pub_num_recibo').prop('disabled', true);
							$('#sec_con_serv_pub_total_pagar').prop('disabled', true);
							$('#sec_con_serv_pub_monto_mes_actual').prop('disabled', true);
						}
						$('#sec_con_serv_pub_check_caja_chica_div').prop('disabled', true);
						$('#sec_con_serv_pub_div_nombre_pagar_hide').prop('disabled', true);
						$('#sec_con_serv_pub_check_cajachica_id').prop('disabled', true);
					}
                    //Fin
                });
                return false;
            }      
        },
        error: function() {}
    });
	///////////FIN DE OBTENER DATOS/////////////////////////
}

function sec_contrato_servicio_publico_ver_imagen_full_pantalla(ruta) {
	
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



$('#sec_con_serv_pub_check_cajachica_id').on('click', function() {
	
    if( $(this).is(':checked') ){
        //Mostrar cuadro para ingresar nombre de persona a la que se le está pagando
        $('#sec_con_serv_pub_div_nombre_pagar_hide').removeClass('ocultar_div');
        $('#sec_con_serv_pub_nombre_pagar').val('');
		$('#btn_nombre_guardar_modal').html('Caja Chica');

    } else {
        // Ocultar cuadro
        $('#sec_con_serv_pub_div_nombre_pagar_hide').addClass('ocultar_div');
        $('#sec_con_serv_pub_nombre_pagar').val('');
		$('#btn_nombre_guardar_modal').html('Validar');
    }
});

function sec_contrato_servicio_publico_abrir_modal_observacion(){
	
	var id_archivo = $('#sec_con_serv_pub_id_archivo').val();

	var data = {
		"accion" : "obtener_correos_observacion",
		"id_archivo" : id_archivo
	}
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
			if (parseInt(respuesta.http_code) == 200) {
				$("#sec_con_serv_pub_observacion_correos").val(respuesta.correos);
				$("#sec_con_serv_pub_observacion").val(respuesta.observacion);

				
			}
		},
		error: function() {}
	});

	$('#sec_con_serv_pub_modal_observar_servicio').modal({backdrop: 'static', keyboard: false});
}

function sec_contrato_servicio_publico_observar_servicio_publico()
{

	var id_archivo = $('#sec_con_serv_pub_id_archivo').val();
	var observacion = $('#sec_con_serv_pub_observacion').val().trim();
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
						$('#sec_con_serv_pub_modal_observar_servicio').modal('hide');
						//$('#sec_con_serv_pub_observacion').val('');
						//$('#sec_con_serv_pub_observacion_correos').val('');
						//$('#sec_con_ser_pub_agregar_monto_recibo')[0].reset();
						contrato_servicio_publico_btn_buscar();
		            }      
		        },
		        error: function() {}
		    });
        }else{
			return false;
        }
    });
}

function sec_con_serv_pub_abrir_modal_observacion_tesoreria(){

	var id_archivo = $('#sec_con_serv_pub_id_archivo').val();
	var data = {
		"accion" : "obtener_correos_observacion_tesoreria",
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

function observarServicioPublicoTesoreria(){
	
	var id_archivo = $('#sec_con_serv_pub_id_archivo').val();
	var observacion = $('#sec_con_serv_pub_observacion').val().trim();
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
        	
        	var data = {
				"accion" : "observar_servicio_publico_tesoreria",
				"observacion" : observacion,
				"id_archivo" : id_archivo,
				"correos": new_correos,
			}
			auditoria_send({ "proceso": "observar_servicio_publico_tesoreria", "data": data });
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
							//window.location.href = "?sec_id=contrato&sub_sec_id=servicio_publico_tesoreria";
							location.reload();
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


/*function sec_contrato_servicio_publico_mostrar_reporte_excel() NO VAAA
{

	let buscar_por = $('#sec_con_ser_pub_buscar_por').val();
	let tipo_servicio = $('#sec_con_ser_pub_select_tipo_servicio').val();
	let estado = $('#sec_con_ser_pub_select_estado').val();
	var pendientes = 0;
	var id_local = $('#sec_con_ser_pub_select_locales').val();
	var id_empresa = $('#sec_con_ser_pub_select_empresa').val();
	var id_jefe_comercial = $('#sec_con_ser_pub_select_jefe_comercial').val();
	var id_supervisor = $('#sec_con_ser_pub_select_supervisor').val();
	var periodo = $('#sec_con_ser_pub_select_mes').val();
	var fec_vcto_desde = $('#sec_con_serv_pub_inicio_vcto').val();
	var fec_vcto_hasta = $('#sec_con_serv_pub_fin_vcto').val();
	if(periodo == 0 && (fec_vcto_desde == "" || fec_vcto_hasta == "")){
		alertify.error('Información: Seleccione el Mes de Periodo o Seleccione el Rango de Fecha de Vencimiento',10);
		return false;
	}

	//document.getElementById('cont_contrato_servicio_publico_excel').innerHTML = '<a href="export.php?export=cont_contrato_servicio_publico&amp;type=lista&amp;buscar_por='+buscar_por+'&amp;tipo_servicio='+tipo_servicio+'&amp;estado='+estado+'&amp;pendientes='+pendientes+'&amp;id_local='+id_local+'&amp;id_empresa='+id_empresa+'&amp;id_jefe_comercial='+id_jefe_comercial+'&amp;id_supervisor='+id_supervisor+'&amp;periodo='+periodo+'&amp;fec_vcto_desde='+fec_vcto_desde+'&amp;fec_vcto_hasta='+fec_vcto_hasta+'" class="btn btn-success export_list_btn" download="contrato_servicio_publico.xls"><span class="glyphicon glyphicon-export"></span> Exportar excel</a>';

}*/


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




function ModalCancelar(id_tipo_recibo, id_local , consumo_per, nombre_local, id_recibo){
	
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

	$('#sec_con_serv_pub_title_modal_agregar_monto').html("Pagar " + nombre_recibo);	
	$('#sec_con_serv_pub_title_modal_agregar_monto_local').html("Local: " + nombre_local);	

	obtenerDatosReciboTesoreria(id_recibo,id_local,consumo_per,id_tipo_recibo);
}


function obtenerDatosReciboTesoreria(id_recibo, id_local, periodo, id_tipo_recibo){
	
	var data = {
		"accion": "obtener_datos_recibo",
		"id_recibo": id_recibo,
		"id_local": id_local,
		"periodo": periodo,
		"id_tipo_recibo": id_tipo_recibo
	}
	var array_datos_recibo = [];
	auditoria_send({ "proceso": "obtener_datos_recibo", "data": data });
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
            	alertify.error('Información: No hay datos del periodo ' + respuesta.status,5);
            	/*$('#sec_con_serv_pub_div_datos_recibo').addClass('diabled_all_content');
            	$('#sec_nuevo_modal_guardar_archivo').addClass('diabled_all_content');

            	$('#sec_con_serv_pub_id_tipo_recibo').val('');
				$('#sec_con_serv_pub_id_local').val('');

				$('#sec_con_serv_pub_title_modal_agregar_monto').html('');	
				$('#sec_con_serv_pub_title_modal_agregar_monto_local').html('');	*/
				return false;
            }

            if (parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.result, function(index, item) {
                    array_datos_recibo.push(item);
                    abrirModalServicioPublico(item.periodo_consumo);
                	// cargarChart(12, item.id_local, item.id_tipo_servicio_publico, item.local);
                	$('#sec_con_serv_pub_mensaje_imagen').html('');
	            	$('#sec_con_serv_pub_div_datos_recibo').removeClass('diabled_all_content');
	            	$('#sec_nuevo_modal_guardar_archivo').removeClass('diabled_all_content');
	            	$('#sec_nuevo_modal_editar_archivo').removeClass('diabled_all_content');
	            	$('#sec_con_serv_pub_ver_full_pantalla').prop('disabled', false);
				  	$('#sec_con_serv_pub_descargar_imagen_a').prop('disabled', false);
                    array_datos_recibo.push(item);
					$('#sec_con_serv_pub_id_archivo').val(item.id);
					$('#sec_con_serv_pub_num_suministro').val(item.numero_suministro);
					$('#sec_con_serv_pub_num_recibo').val(item.numero_recibo);
					$('#sec_con_serv_pub_ruc_servicio').val(item.ruc_servicio);
					// $('#sec_con_serv_pub_periodo_consumo').val(obtenerMesAnioLetras(item.periodo_consumo));
					$('#sec_con_serv_pub_periodo_consumo').val(item.periodo_consumo);
					$('#sec_con_serv_pub_fecha_emision').val(item.fecha_emision);
					$('#sec_con_serv_pub_fecha_vencimiento').val(item.fecha_vencimiento);
                	$('#sec_con_serv_pub_id_tipo_compromiso_nombre').val(item.tipo_compromiso_nombre);
					$('#sec_con_serv_pub_id_monto_pct').val(item.monto_pct);
					$('#sec_con_serv_pub_monto_mes_actual').val(item.monto_total);
					$('#sec_con_serv_pub_total_pagar').val(item.total_pagar);
					console.log(item.total_pagar);
					//ocultos
					$('#sec_con_serv_pub_periodo_consumo_oculto').val(item.periodo_consumo);
					$('#sec_con_serv_pub_id_tipo_compromiso').val(item.tipo_compromiso);
					var path_img = "files_bucket/contratos/servicios_publicos/";
					if(item.id_tipo_servicio_publico == 1){ //Luz
						path_img += "luz/";
						nombre_recibo = "Recibo de Luz";
					}else{
						path_img += "agua/";
						nombre_recibo = "Recibo de Agua";
					}
					path_img += item.nombre_file;
					$('#sec_con_serv_pub_div_imagen_recibo').html('');
					//Validar si la imagen de la BD existe en la carpeta
					var nuevo_id = "sec_serv_pub_img_servicio_publico"+item.id+"_"+item.numero_suministro;
					if (item.extension == "pdf") {
						$('#sec_con_serv_pub_div_imagen_recibo').append(
							'<iframe src="' + path_img + '" class="col-xs-12 col-md-12 col-sm-12" width="100%" height="400"></iframe>'
						);

						$('#sec_con_serv_pub_btn_descargar_imagen_recibo').hide();
						$('#sec_con_serv_pub_div_VerImagenFullPantalla').hide();
						
					}else if (item.extension == 'jpg' || item.extension == 'png' || item.extension == 'jpeg') {
						$('#sec_con_serv_pub_div_imagen_recibo').append(
							'<div class="col-md-12">' +
							'   <div align="center" style="height: 100%; width: 100%;">' +
							'       <img  id="' + nuevo_id + '" src="' + path_img + '" width="300px" height="350px" />' +
							'   </div>' +
							'</div>'
						);
						$('#sec_con_serv_pub_ver_full_pantalla').attr('onClick', 'sec_contrato_servicio_publico_ver_imagen_full_pantalla("' + path_img + '");');
						$('#sec_con_serv_pub_btn_descargar_imagen_recibo').show();
						$('#sec_con_serv_pub_div_VerImagenFullPantalla').show();
						var ruta = "sec_contrato_servicio_publico_btn_descargar('"+ item.ruta_download_file +"');";
						$('#sec_con_serv_pub_descargar_imagen_a').attr('onClick', ruta);
						
						$("#" + nuevo_id).error(function(){
						  $(this).hide();
						  $('#sec_con_serv_pub_mensaje_imagen').html('La imagen no existe en la carpeta');
						  $('#sec_con_serv_pub_ver_full_pantalla').prop('disabled', true);
						  $('#sec_con_serv_pub_descargar_imagen_a').prop('disabled', true);
						  
						});
						//Fin de Validar si la imagen de la BD existe en la carpeta
					}
					$('#sec_con_serv_pub_title_modal_agregar_monto').html("Pagar " + nombre_recibo);	
					$('#sec_con_serv_pub_title_modal_agregar_monto_local').html("Local: " + item.local_nombre);	

                    // //Inicio compromiso monto fijo
                    // var tipo_compromiso = $('#sec_con_serv_pub_id_tipo_compromiso').val();
					// var monto_pct = $('#sec_con_serv_pub_id_monto_pct').val();
					// if(tipo_compromiso == 2){ //monto fijo
					// 	$("#sec_con_serv_pub_total_pagar").val(monto_pct);
					// }

					// if(tipo_compromiso == 1 || tipo_compromiso == 5 || tipo_compromiso == 6){
					// 	$('#sec_con_serv_pub_total_pagar').prop('disabled', false);
					// }else{
					// 	$('#sec_con_serv_pub_total_pagar').prop('disabled', true);
					// }
					// if(item.estado_recibo == 1 || item.estado_recibo == 6){ //PENDIENTE U OBSERVADO
					// 	$('#sec_con_serv_pub_total_pagar').prop('disabled', false);
					// 	$('#sec_con_serv_pub_num_recibo').prop('disabled', false);
					// 	$('#sec_con_serv_pub_monto_mes_actual').prop('disabled', false);
					// 	$('#sec_nuevo_modal_observar_archivo').prop('disabled', false);
					// 	$('#sec_nuevo_modal_guardar_archivo').prop('disabled', false);
					// }
					// if(item.estado_recibo == 2 || item.estado_recibo == 3 || item.estado_recibo == 4 || item.estado_recibo == 5){ //VALIDADO|PAGADO|CAJA CHICA|FACTURA
					// 	$('#sec_con_serv_pub_total_pagar').prop('disabled', true);
					// 	$('#sec_con_serv_pub_num_recibo').prop('disabled', true);
					// 	$('#sec_con_serv_pub_monto_mes_actual').prop('disabled', true);
					// 	$('#sec_nuevo_modal_observar_archivo').prop('disabled', true);
					// 	$('#sec_nuevo_modal_guardar_archivo').prop('disabled', true);
					// 	$('#sec_con_serv_pub_check_caja_chica_div').prop('disabled', true);
					// 	$('#sec_con_serv_pub_div_nombre_pagar_hide').prop('disabled', true);
					// 	$('#sec_con_serv_pub_check_cajachica_id').prop('disabled', true);
					// }
                    //Fin
                });
                return false;
            }      
        },
        error: function() {}
    });
	///////////FIN DE OBTENER DATOS/////////////////////////
}


function CancelarReciboServicioPublico() {
	
	var sec_con_serv_pub_fecha_pago = $('#sec_con_serv_pub_fecha_pago').val();
	var sec_con_serv_pub_voucher_pago = $('#sec_con_serv_pub_voucher_pago').val();
	var sec_con_serv_pub_numero_operacion = $('#sec_con_serv_pub_numero_operacion').val();

	if (sec_con_serv_pub_numero_operacion == "") {
		alertify.error('Información: Ingrese el numero de operación',5);
		$('#sec_con_serv_pub_numero_operacion').focus();
		return false;
	}

	if (sec_con_serv_pub_fecha_pago == "") {
		alertify.error('Información: Ingrese la fecha de pago',5);
		$('#sec_con_serv_pub_fecha_pago').focus();
		return false;
	}

	if (sec_con_serv_pub_voucher_pago == "") {
		alertify.error('Información: Seleccione un documento de pago',5);
		$('#sec_con_serv_pub_voucher_pago').focus();
		return false;
	}
	

	var dataForm = new FormData($("#Formulario_con_ser_pub_cancelar_recibo")[0]);
	dataForm.append("accion","pagar_servicio_publico");

	loading(true);

	$.ajax({
		url: "/sys/set_contrato_servicio_publico.php",
		type: "POST",
		data: dataForm,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response) {
			
			var respuesta = JSON.parse(response);
			loading(false);
			if (respuesta.status) {
				swal({
					title: "Pago Realizado",
					text: "El pago del recibo se guardo correctamente",
					html:true,
					type: "success",
					timer: 1000,
					closeOnConfirm: false,
					showCancelButton: false
				});
				$('#sec_con_serv_pub_modal_agregar_monto').modal('hide');
				$('#Formulario_con_ser_pub_cancelar_recibo')[0].reset();
				sec_serv_pub_buscar_registros_tesoreria();
			} else{
				swal({
					title: "Error al pagar el recibo.",
					text: respuesta.message,
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
				return false;
			}  
	
		},
		always: function(data){
			loading(false);
			console.log(data);

		}
	});
}

function sec_contrato_get_razon_social() {
	let select = $("[name='contrato_servicio_publico_param_empresa_arrendataria']");
	let valorSeleccionado = $("#contrato_servicio_publico_param_empresa_arrendataria").val();

	$.ajax({
		url: "/sys/get_contrato_servicio_publico.php",
		type: "POST",
		data: {
			accion: "obtener_razones_sociales"
		},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			$(select).empty();
			if (!valorSeleccionado) {
				let opcionDefault = $('<option value="0">Todos</option>');
				$(select).append(opcionDefault);
			}
			$(respuesta.result).each(function (i, e) {
				let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);
			});

			// Seleccionar el primer local por defecto
			$(select).prop('selectedIndex', 0);

			if (valorSeleccionado != null) {
				$(select).val(valorSeleccionado);
			}
		},
		error: function () {
			// Manejar el error si es necesario
		}
	});
}

function sec_contrato_servicio_publico_btn_descargar(ruta_archivo)
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