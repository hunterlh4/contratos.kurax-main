
var tabla;

function sec_contrato_reportes_contabilidad()
{
	cargarFechas();
	$("#cont_contrato_contabilidad_div_tabla").hide();
}

function cargarFechas()
{
	// INICIO INICIALIZACION DE DATEPICKER
	$('.sec_contrato_contabilidad_datepicker')
		.datepicker({
			dateFormat:'dd-mm-yy',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
			// localStorage.setItem($(this).atrr("id"),)
		});
}

$("#select_contabilidad_tipo_solicitud").on("change", function()
{
	var selectValor = $(this).val();

	mostrar_div_tipo_solicitud_contabilidad(selectValor);
})


function mostrar_div_tipo_solicitud_contabilidad(param)
{
	$('#div_contabilidad_boton_export').hide();
	$('#div_contabilidad_boton_export_servicios_publicos').hide();
	
	
	if (param == '1') 
	{
		$('#cont_contabilidad_div_parametros_reporte').show();
		$('#cont_contrato_contabilidad_div_tabla').hide();
		
		$('#cont_contabilidad_div_parametros_reporte_servicio_publico').hide();
		$('#cont_contrato_contabilidad_div_tabla_servicio_publico').hide();

		$('#cont_contabilidad_div_parametros_reporte_centro_costos').hide();
		$('#cont_contrato_contabilidad_div_tabla_centro_costos').hide();
	}
	else if(param == '2')
	{
		$('#cont_contabilidad_div_parametros_reporte').hide();
		$('#cont_contrato_contabilidad_div_tabla').hide();

		$('#cont_contabilidad_div_parametros_reporte_servicio_publico').hide();
		$('#cont_contrato_contabilidad_div_tabla_servicio_publico').hide();

		$('#cont_contabilidad_div_parametros_reporte_centro_costos').show();
	}
	else if(param == '3')
	{
		$('#cont_contabilidad_div_parametros_reporte').hide();
		$('#cont_contrato_contabilidad_div_tabla').hide();

		$('#cont_contabilidad_div_parametros_reporte_servicio_publico').show();

		$('#cont_contabilidad_div_parametros_reporte_centro_costos').hide();
		$('#cont_contrato_contabilidad_div_tabla_centro_costos').hide();

		//listarLocalesContabilidadServicioPublicoDatatable();	
	}
}

var claseTipoAlertas = 
{
	alertaSuccess: 1,
	alertaInfo: 2,
	alertaWarning: 3,
	alertaDanger: 4
};

function RecuperarClaseAlerta(valor)
{
	var clase = "";
	switch(valor)
	{
		case 1 : clase = 'alert alert-success alerta-dismissible';
		break;

		case 2 : clase = 'alert alert-info alerta-dismissible';
		break;

		case 3 : clase = 'alert alert-warning alerta-dismissible';
		break;

		case 4 : clase = 'alert alert-danger alerta-dismissible';
		break; 
	}

	return clase;
}

function tipoFont(valor)
{
	var clase = "";
	switch(valor)
	{
		case 1:
		case 2: clase = "<i class='fa fa-info-circle fa-2x'></i>";
		break;

		case 3:
		case 4: clase = "<i class='fa fa-exclamation-triangle fa-2x'></i>";
		break;

	}

	return clase;
}

//ESTE ES PARA LAS ALERTAS

var mensajeAlertaReportes = function (titulo, mensaje, tipoClase, controlDiv)
{
	var clase = RecuperarClaseAlerta(tipoClase);
	var font = tipoFont(tipoClase);
	var control = $(controlDiv);
	var divMensaje = "<div class = '"+ clase +"' role = 'alert'>";
	divMensaje += "<button type = 'button' class = 'close' data-dismiss = 'alert' aria-label = 'close'>";
	divMensaje += "<span aria-hidden = 'true'>&times;</span>";
	divMensaje += "</button>";
	divMensaje += font + "<strong>" + titulo + "</strong><br/>" + mensaje;
	divMensaje += "</div>";
	control.empty();
	control.hide().html(divMensaje.toString()).fadeIn(2000).delay(9000).fadeOut("slow");
}


function buscarReporteContable()
{
	$('#div_contabilidad_boton_export').hide();
	
	if($("#cont_contabilidad_fecha_mes").val() == "" || $("#tipo_reporte_contable").val() == "0" || $("#tipo_moneda_contable").val() == 0)
	{
		$("#cont_contrato_contabilidad_div_tabla").hide();

		mensajeAlerta("Advertencia:", "Tiene que seleccionarla fecha de consulta, tipo de reporte y tipo de moneda correspondiente.", claseTipoAlertas.alertaWarning, $('#cont_contable_alerta_filtrar'));
		return;
	}
	else if($("#tipo_reporte_contable").val() == "1")
	{
		if($("#cont_contabilidad_fecha_comprobante").val() == "")
		{
			$("#cont_contrato_contabilidad_div_tabla").hide();

			mensajeAlerta("Advertencia:", "Tiene que ingresar la fecha de comprobante.", claseTipoAlertas.alertaWarning, $('#cont_contable_alerta_filtrar'));
			return;
		}

		else if($("#cont_contabilidad_numero_comprobante").val() == "")
		{
			$("#cont_contrato_contabilidad_div_tabla").hide();

			mensajeAlerta("Advertencia:", "Tiene que ingresar el numero de comprobante (para identificar el correlativo).", claseTipoAlertas.alertaWarning, $('#cont_contable_alerta_filtrar'));
			return;
		}

		else if($("#cont_contabilidad_tipo_cambio").val() == "")
		{
			$("#cont_contrato_contabilidad_div_tabla").hide();

			mensajeAlerta("Advertencia:", "Tiene que ingresar el tipo de cambio.", claseTipoAlertas.alertaWarning, $('#cont_contable_alerta_filtrar'));
			return;
		}

		else if($("#cont_contabilidad_tipo_conversion").val() == "")
		{
			$("#cont_contrato_contabilidad_div_tabla").hide();

			mensajeAlerta("Advertencia:", "Tiene que ingresar el tipo de conversion.", claseTipoAlertas.alertaWarning, $('#cont_contable_alerta_filtrar'));
			return;
		}

		else
		{
			listarLocalesContabilidadDatatable();
		}
	}

	else if($("#tipo_reporte_contable").val() == "2")
	{
		if($("#cont_contabilidad_fecha_comprobante").val() == "")
		{
			$("#cont_contrato_contabilidad_div_tabla").hide();

			mensajeAlerta("Advertencia:", "Tiene que ingresar la fecha de comprobante.", claseTipoAlertas.alertaWarning, $('#cont_contable_alerta_filtrar'));
			return;
		}

		else if($("#cont_contabilidad_numero_comprobante").val() == "")
		{
			$("#cont_contrato_contabilidad_div_tabla").hide();

			mensajeAlerta("Advertencia:", "Tiene que ingresar el numero de comprobante (para identificar el correlativo).", claseTipoAlertas.alertaWarning, $('#cont_contable_alerta_filtrar'));
			return;
		}

		else if($("#cont_contabilidad_tipo_cambio").val() == "")
		{
			$("#cont_contrato_contabilidad_div_tabla").hide();

			mensajeAlerta("Advertencia:", "Tiene que ingresar el tipo de cambio.", claseTipoAlertas.alertaWarning, $('#cont_contable_alerta_filtrar'));
			return;
		}

		else if($("#cont_contabilidad_tipo_conversion").val() == "")
		{
			$("#cont_contrato_contabilidad_div_tabla").hide();

			mensajeAlerta("Advertencia:", "Tiene que ingresar el tipo de conversion.", claseTipoAlertas.alertaWarning, $('#cont_contable_alerta_filtrar'));
			return;
		}

		else
		{
			listarLocalesContabilidadDatatable();
		}
	}

	else
	{
		listarLocalesContabilidadDatatable();
	}
}


function listarLocalesContabilidadDatatable()
{
	$('#div_contabilidad_boton_export_servicios_publicos').hide();

	if(sub_sec_id == "contabilidad")
	{
		

		$("#cont_contrato_contabilidad_div_tabla").show();

		var cont_contabilidad_numero_comprobante = $("#cont_contabilidad_numero_comprobante").val();

		var cont_contabilidad_fecha_comprobante = $("#cont_contabilidad_fecha_comprobante").val();

		var cont_contabilidad_tipo_cambio = $("#cont_contabilidad_tipo_cambio").val();

		var cont_contabilidad_tipo_conversion = $("#cont_contabilidad_tipo_conversion").val();

		var cont_tipo_reporte = $("#tipo_reporte_contable").val();

		var cont_tipo_moneda = $("#tipo_moneda_contable").val();

		var cont_contabilidad_fecha_mes = $("#cont_contabilidad_fecha_mes").val();

		var cont_contabilidad_anio = cont_contabilidad_fecha_mes.substring(0, 4);

		var cont_contabilidad_mes = cont_contabilidad_fecha_mes.substring(5, 7);

		var data = {
			"accion": "cont_listar_locales_contabilidad_reporte",
			"cont_contabilidad_numero_comprobante" : cont_contabilidad_numero_comprobante,
			"cont_contabilidad_fecha_comprobante" : cont_contabilidad_fecha_comprobante,
			"cont_contabilidad_tipo_cambio" : cont_contabilidad_tipo_cambio,
			"cont_contabilidad_tipo_conversion" : cont_contabilidad_tipo_conversion,
			"cont_tipo_reporte" : cont_tipo_reporte,
			"cont_tipo_moneda" : cont_tipo_moneda,
			"cont_contabilidad_anio" : cont_contabilidad_anio,
			"cont_contabilidad_mes" : cont_contabilidad_mes
		}

		tabla = $("#cont_locales_contabilidad_datatable").dataTable(
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
				"createdRow": function (row, data) {
				var id = data[0];
					$(row).prop('id', id).data('id', id);
				},
				
				"ajax" :
				{
					url : "/sys/set_contrato_reportes_contabilidad.php",
					data : data,
					type : "POST",
					dataType : "json",
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[20, 30, 40, 50, 100],
				"order" : 
				[
					1, "asc"	
				],
				select: {
					style: 'single'
				}

			}
		).DataTable();


		mostrarReporteExcelBotonConcar();

	}

}

function mostrarReporteExcelBotonConcar()
{
	
	$('#div_contabilidad_boton_export').show();

	var cont_contabilidad_fecha_mes = $("#cont_contabilidad_fecha_mes").val();
	
	var cont_param_anio = cont_contabilidad_fecha_mes.substring(0, 4);

	var cont_param_mes = cont_contabilidad_fecha_mes.substring(5, 7);

	var cont_param_tipo_reporte = $("#tipo_reporte_contable").val();

	var cont_param_tipo_moneda = $("#tipo_moneda_contable").val();

	var cont_param_contabilidad_numero_comprobante = $("#cont_contabilidad_numero_comprobante").val();

	var cont_param_contabilidad_fecha_comprobante = $("#cont_contabilidad_fecha_comprobante").val();

	var cont_param_contabilidad_tipo_cambio = $("#cont_contabilidad_tipo_cambio").val();

	var cont_param_contabilidad_tipo_conversion = $("#cont_contabilidad_tipo_conversion").val().toUpperCase();

	//document.getElementById('cont_contabilidad_boton_excel_concar').innerHTML = '<a href="contrato_export_contabilidad_concar.php?export=cont_contrato&amp;type=lista&amp;campo_busqueda='+select_campo_busqueda+'&amp;fecha_inicio='+cont_locales_fecha_inicio+'&amp;fecha_fin='+cont_locales_fecha_fin+'" class="btn btn-success btn-sm export_list_btn" download="contrato_locales.xls"><span class="glyphicon glyphicon-export"></span> Exportar excel</a>';

	//document.getElementById('cont_contabilidad_boton_excel_concar').innerHTML = '<a href="contrato_export_contabilidad_concar.php?export_concar=cont_contabilidad_concar" class="btn btn-success btn-sm export_list_btn" download="contrato_concar.xls"><span class="glyphicon glyphicon-export"></span> Exportar excel</a>';

	if(cont_param_tipo_reporte == 1)
	{
		document.getElementById('cont_contabilidad_boton_excel_concar').innerHTML = '<a href="contrato_export_contabilidad_concar.php?tipo_report_contable='+cont_param_tipo_reporte+'&amp;periodo_mes='+cont_param_mes+'&amp;periodo_anio='+cont_param_anio+'&amp;tipo_moneda='+cont_param_tipo_moneda+'&amp;fecha_comprobante='+cont_param_contabilidad_fecha_comprobante+'&amp;numero_comprobante='+cont_param_contabilidad_numero_comprobante+'&amp;tipo_cambio='+cont_param_contabilidad_tipo_cambio+'&amp;tipo_conversion='+cont_param_contabilidad_tipo_conversion+'" class="btn btn-success btn-sm export_list_btn" download="contrato_concar.xls"><span class="glyphicon glyphicon-export"></span> Exportar CONCAR excel</a>';
	}
	else if(cont_param_tipo_reporte == 2)
	{

		document.getElementById('cont_contabilidad_boton_excel_concar').innerHTML = '<a href="contrato_export_contabilidad_concar.php?tipo_report_contable='+cont_param_tipo_reporte+'&amp;periodo_mes='+cont_param_mes+'&amp;periodo_anio='+cont_param_anio+'&amp;tipo_moneda='+cont_param_tipo_moneda+'&amp;fecha_comprobante='+cont_param_contabilidad_fecha_comprobante+'&amp;numero_comprobante='+cont_param_contabilidad_numero_comprobante+'&amp;tipo_cambio='+cont_param_contabilidad_tipo_cambio+'&amp;tipo_conversion='+cont_param_contabilidad_tipo_conversion+'" class="btn btn-info btn-sm export_list_btn" download="contrato_concar.xls"><span class="glyphicon glyphicon-export"></span> Exportar SISPAG excel</a>';
	}

	

}

function buscarReporteServiciosPublicos()
{
	var cont_contabilidad_servicio_publico_reporte_anio = $("#cont_contabilidad_servicio_publico_anio").val();

	if(cont_contabilidad_servicio_publico_reporte_anio != null)
	{
		listarLocalesContabilidadServicioPublicoDatatable();
	}
	else
	{
		mensajeAlertaReportes("Advertencia:", "Tienes que seleccionar Año.", claseTipoAlertas.alertaWarning, $('#cont_contable_alerta_boton_export_servicios_publicos'));
		return;
	}
	
}

function buscar_contratos_contabilidad_CentroCosto()
{
	listarLocalesContabilidadCentroCostosDatatable();
}

function listarLocalesContabilidadCentroCostosDatatable()
{
	$('#div_contabilidad_boton_export_servicios_publicos').hide();

	if(sub_sec_id == "contabilidad")
	{
        $('#cont_contrato_contabilidad_div_tabla_centro_costos').show();

        var cont_contabilidad_centro_costos_param_tienda = $("#cont_contabilidad_centro_costos_param_tienda").val();
        var cont_contabilidad_centro_costos_param_tipo_moneda = $("#cont_contabilidad_centro_costos_param_tipo_moneda").val();
        var cont_contabilidad_centro_costos_param_fecha_inicio_contrato = $("#cont_contabilidad_centro_costos_param_fecha_inicio").val();
        var cont_contabilidad_centro_costos_param_fecha_fin_contrato = $("#cont_contabilidad_centro_costos_param_fecha_fin").val();

		var data = {
			"accion": "cont_listar_locales_contabilidad_centro_costos",
			"cont_contabilidad_centro_costos_param_tienda": cont_contabilidad_centro_costos_param_tienda,
			"cont_contabilidad_centro_costos_param_tipo_moneda": cont_contabilidad_centro_costos_param_tipo_moneda,
			"cont_contabilidad_centro_costos_param_fecha_inicio_contrato": cont_contabilidad_centro_costos_param_fecha_inicio_contrato,
			"cont_contabilidad_centro_costos_param_fecha_fin_contrato": cont_contabilidad_centro_costos_param_fecha_fin_contrato
		}

		auditoria_send({ "proceso": "cont_listar_locales_contabilidad_centro_costos", "data": data });
		tabla = $("#cont_locales_contabilidad_centro_costos_datatable").dataTable(
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
					url : "/sys/set_contrato_reportes_contabilidad.php",
					data : data,
					type : "POST",
					dataType : "json",
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[20, 30, 40, 50, 100],
				"order" : 
				[
					0, "desc"	
				]

			}
		).DataTable();

	}

}

function listarLocalesContabilidadServicioPublicoDatatable()
{
	
	if(sub_sec_id == "contabilidad")
	{
		//$('#cont_contabilidad_div_parametros_reporte_servicio_publico').show();
		$('#cont_contrato_contabilidad_div_tabla_servicio_publico').show();

		var cont_contabilidad_servicio_publico_anio = $("#cont_contabilidad_servicio_publico_anio").val();

		var data = {
			"accion": "cont_listar_locales_contabilidad_servicio_publico",
			"cont_contabilidad_servicio_publico_anio" : cont_contabilidad_servicio_publico_anio
		}

		auditoria_send({ "proceso": "cont_listar_locales_contabilidad_servicio_publico", "data": data });

		tabla = $("#cont_locales_contabilidad_servicio_publico_datatable").dataTable(
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
					url : "/sys/set_contrato_reportes_contabilidad.php",
					data : data,
					type : "POST",
					dataType : "json",
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[20, 30, 40, 50, 100],
				"order" : 
				[
					0, "desc"	
				]

			}
		).DataTable();

		mostrarReporteExcelBotonServicioPublico();
	}

}

function mostrarReporteExcelBotonServicioPublico()
{
	var cont_param_tipo_reporte = "reportes_servicio_publico_contabilidad";
	var cont_contabilidad_servicio_publico_reporte_anio = $("#cont_contabilidad_servicio_publico_anio").val();

	/*if(cont_param_tipo_reporte == 1)
	{
		document.getElementById('cont_contabilidad_boton_excel_concar').innerHTML = '<a href="contrato_export_contabilidad_concar.php?tipo_report_contable='+cont_param_tipo_reporte+'&amp;periodo_mes='+cont_param_mes+'&amp;periodo_anio='+cont_param_anio+'&amp;tipo_moneda='+cont_param_tipo_moneda+'&amp;fecha_comprobante='+cont_param_contabilidad_fecha_comprobante+'&amp;numero_comprobante='+cont_param_contabilidad_numero_comprobante+'&amp;tipo_cambio='+cont_param_contabilidad_tipo_cambio+'&amp;tipo_conversion='+cont_param_contabilidad_tipo_conversion+'" class="btn btn-success btn-sm export_list_btn" download="contrato_concar.xls"><span class="glyphicon glyphicon-export"></span> Exportar CONCAR excel</a>';
	}*/

	document.getElementById('cont_contabilidad_boton_excel_concar_servicios_publicos').innerHTML = '<a href="contrato_export_contabilidad_concar.php?reportes_servicio_publico_contabilidad='+cont_param_tipo_reporte+'&amp;servicio_publico_param_report_anio='+cont_contabilidad_servicio_publico_reporte_anio+'" class="btn btn-success btn-sm export_list_btn" download="contrato_concar.xls"><span class="glyphicon glyphicon-export"></span> Exportar Servicio Público</a>';
	$('#div_contabilidad_boton_export_servicios_publicos').show();
}

function obtener_contrato_contabilidad_centrocosto(id)
{
	limpiar_tr_modal_alerta();
	$('#configurarCentroCostos').modal('show');

	var data = {
		"accion": "obtener_dato_contrato_contabilidad",
		"parametro" : id
	}

	$.ajax(
	{
		url : "/sys/set_contrato_reportes_contabilidad.php",
		type : "POST",
		data : data,
		success : function(resp)
		{
			var respuesta = JSON.parse(resp);
			
			$("#contrato_id").val(respuesta.contrato_id);

			contenido_modal_alerta.innerHTML = '<tr><td class="text-center">'+ respuesta.contrato_id + '</td><td>'+ respuesta.nombre_tienda + '</td><td>'+ respuesta.ubicacion + '</td><td class="text-center">'+ respuesta.fecha_inicio + '</td><td class="text-center">'+ respuesta.fecha_fin + '</td></tr>';

			return true;

		},
		error : function(textStatus)
		{
			console.log( "La solicitud obtener datos a fallado: " +  textStatus);
		}
	});
}

function limpiar_tr_modal_alerta()
{
	$("#contrato_id").val("");
	contenido_modal_alerta.innerHTML = '<tr><td class="text-center" id="modal_id_contrato"></td><td id="modal_nombre_tienda"></td><td id="modal_ubicaion_inmueble"></td><td class="text-center" id="modal_fecha_inicio"></td><td class="text-center" id="modal_fecha_fin"></td></tr>';
}


function registrar_centro_costos()
{
	
	var contrato_id = $("#contrato_id").val();
	var codigoCentroCostos = $("#codigoCentroCostos").val();

	if(codigoCentroCostos == 0 || codigoCentroCostos == "")
	{
		mensajeAlerta("Advertencia:", "Tiene que ingresar el centro de costo respectivamente.", claseTipoAlertas.alertaWarning, $('#divMensajeAlerta'));
		return;
	}

	var data = {
		"accion": "actualizar_local_centro_costos",
		"contrato_id" : contrato_id,
		"codigoCentroCostos" : codigoCentroCostos
	}

	auditoria_send({ "proceso": "actualizar_local_centro_costos", "data": data });
	
	$.ajax(
	{
		url : "/sys/set_contrato_reportes_contabilidad.php",
		type : "POST",
		data : data,
		success : function(resp)
		{
			
			var respuesta = JSON.parse(resp);
			
			if(respuesta == "1") 
			{
				$('#configurarCentroCostos').modal('hide');
				limpiar_tr_modal_alerta();

				listarLocalesContabilidadCentroCostosDatatable();
			}
			else if(respuesta == "2") 
			{
				mensajeAlerta("Advertencia:", "El centro de costos que acaba de ingresar ya existe en otro Local.", claseTipoAlertas.alertaWarning, $('#divMensajeAlerta'));
				return;
			}
			else
			{
				mensajeAlerta("Advertencia:", "Ocurrio un error, vuelva a registrar, si el problema persiste consulte con SOPORTE TI.", claseTipoAlertas.alertaWarning, $('#divMensajeAlerta'));
				return;
			}
			

			return true;

		},
		error : function(textStatus)
		{
			console.log( "La solicitud para ingresar el centro de costos a fallado: " +  textStatus);
		}
	});

}
