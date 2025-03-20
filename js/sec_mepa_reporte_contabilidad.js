// INICIO FUNCION DE INICIALIZACION
function sec_mepa_reporte_contabilidad()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_reporte_contabilidad_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	sec_mepa_reporte_contabilidad_inicializar_param_fechas();

	$('.limpiar_input_fecha').click(function() {
		$('#' + $(this).attr("limpiar")).val('');
	});
}
// FIN FUNCION DE INICIALIZACION

function sec_mepa_reporte_contabilidad_inicializar_param_fechas()
{
	// INICIO INICIALIZACION DE DATEPICKER
	$('.mepa_reporte_contabilidad_datepicker')
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

$('#sec_mepa_reporte_contabilidad_select_tipo_solicitud').change(function () 
{
	$("#mepa_reporte_contabilidad_div_param_fecha_inicio_desde").hide();
	$("#mepa_reporte_contabilidad_div_param_fecha_inicio_hasta").hide();
	$("#mepa_reporte_contabilidad_div_param_situacion").hide();
	$("#mepa_reporte_contabilidad_div_param_situacion_gastos_proveedores").hide();
	$("#mepa_reporte_contabilidad_div_param_flag_envio_documento_fisico").hide();
	$("#mepa_reporte_contabilidad_div_param_dias_habiles").hide();
	$(".mepa_reporte_contabilidad_div_param_saldo_caja_chica").hide();

	// INICIO DIV BOTONES EXCEL
	$("#mepa_reporte_contabilidad_div_btn_export").hide();
	
	$("#mepa_reporte_contabilidad_btn_export_caja_chica").hide();
	$("#mepa_reporte_contabilidad_btn_export_asignacion_caja_chica").hide();
	$("#mepa_reporte_contabilidad_btn_export_caja_chica_fisico_virtual").hide();
	$("#mepa_reporte_contabilidad_btn_export_sin_caja_chica_realizada").hide();
	$("#mepa_reporte_contabilidad_btn_export_gastos_proveedores").hide();
	$("#mepa_reporte_contabilidad_btn_export_gastos_ultimas_semanas").hide();
	$("#mepa_reporte_contabilidad_btn_tipo_export").hide();
	// FIN DIV BOTONES EXCEL
	
	// INICIO DIV TABLAS
	$("#mepa_reporte_contabilidad_liquidacion_div_tabla").hide();
	$("#mepa_reporte_contabilidad_asignacion_div_tabla").hide();
	$("#mepa_reporte_contabilidad_caja_chica_fisico_virtual_div_tabla").hide();
	$("#mepa_reporte_contabilidad_sin_caja_chica_realizada_div_tabla").hide();
	$("#mepa_reporte_contabilidad_gastos_proveedores_div_tabla").hide();
	$("#mepa_reporte_contabilidad_gastos_ultimas_semanas_div_tabla").hide();
	$("#mepa_reporte_contabilidad_div_tabla_saldo_caja_chica").hide();
	$("#filtros_dashboard_caja_chica").hide();
	$("#mepa_reporte_contabilidad_myChart_dashboard_caja__chica").hide();
	
	// FIN DIV TABLAS

	$("#sec_mepa_reporte_contabilidad_select_tipo_solicitud option:selected").each(function ()
	{	
		var selectValor = $(this).val();
		
		if(selectValor == 0)
		{
			alertify.error('Seleccione Tipo Reporte',5);
			
			$("#sec_mepa_reporte_contabilidad_select_tipo_solicitud").focus();
			setTimeout(function() {
				$('#sec_mepa_reporte_contabilidad_select_tipo_solicitud').select2('open');
			}, 500);

			return false;
		}
		else if(selectValor == 3)
		{
			// Asignación de caja chica y supervisores / Jefe Comercial
			$("#mepa_reporte_contabilidad_div_param_situacion").hide();
			$("#mepa_reporte_contabilidad_div_param_flag_envio_documento_fisico").hide();
			$("#mepa_reporte_contabilidad_div_param_dias_habiles").hide();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_desde").show();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_hasta").show();
			$("#mepa_reporte_contabilidad_div_param_situacion_gastos_proveedores").hide();
		}
		else if(selectValor == 4)
		{
			// Cajas Chicas
			$("#mepa_reporte_contabilidad_div_param_flag_envio_documento_fisico").hide();
			$("#mepa_reporte_contabilidad_div_param_dias_habiles").hide();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_desde").show();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_hasta").show();
			$("#mepa_reporte_contabilidad_div_param_situacion").show();
			$("#mepa_reporte_contabilidad_div_param_situacion_gastos_proveedores").hide();
		}
		else if(selectValor == 5)
		{
			// Caja chica fisico / virtual
			$("#mepa_reporte_contabilidad_div_param_situacion").hide();
			$("#mepa_reporte_contabilidad_div_param_dias_habiles").hide();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_desde").show();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_hasta").show();
			$("#mepa_reporte_contabilidad_div_param_flag_envio_documento_fisico").show();
			$("#mepa_reporte_contabilidad_div_param_situacion_gastos_proveedores").hide();
		}
		else if(selectValor == 6)
		{
			// Asignación sin cajas chicas realizadas
			$("#mepa_reporte_contabilidad_div_param_situacion").hide();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_desde").hide();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_hasta").hide();
			$("#mepa_reporte_contabilidad_div_param_flag_envio_documento_fisico").hide();
			$("#mepa_reporte_contabilidad_div_param_dias_habiles").show();
			$("#mepa_reporte_contabilidad_div_param_situacion_gastos_proveedores").hide();
		}
		else if(selectValor == 7)
		{
			// Gastos por proveedores
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_desde").show();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_hasta").show();
			$("#mepa_reporte_contabilidad_div_param_situacion").hide();
			$("#mepa_reporte_contabilidad_div_param_flag_envio_documento_fisico").hide();
			$("#mepa_reporte_contabilidad_div_param_dias_habiles").hide();
			$("#mepa_reporte_contabilidad_div_param_situacion_gastos_proveedores").show();
		}
		else if(selectValor == 8)
		{
			
			// Promedio gastos ultimas 5 semanas
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_desde").hide();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_hasta").hide();
			$("#mepa_reporte_contabilidad_div_param_situacion").hide();
			$("#mepa_reporte_contabilidad_div_param_flag_envio_documento_fisico").hide();
			$("#mepa_reporte_contabilidad_div_param_dias_habiles").hide();
			$("#mepa_reporte_contabilidad_div_param_situacion_gastos_proveedores").hide();

		}
		else if(selectValor == 10)
		{
			// Saldo de Cajas Chicas
			$(".mepa_reporte_contabilidad_div_param_saldo_caja_chica").show();
		}
		else if(selectValor == 11)
		{
			
			// Dashboard Caja Chica
			$("#filtros_dashboard_caja_chica").show();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_desde").hide();
			$("#mepa_reporte_contabilidad_div_param_fecha_inicio_hasta").hide();
			$("#mepa_reporte_contabilidad_div_param_situacion").hide();
			$("#mepa_reporte_contabilidad_div_param_flag_envio_documento_fisico").hide();
			$("#mepa_reporte_contabilidad_div_param_dias_habiles").hide();
			$("#mepa_reporte_contabilidad_div_param_situacion_gastos_proveedores").hide();

		}
		else
		{
			alertify.error('Tipo Reporte no encontrado ',5);
			$("#sec_mepa_reporte_contabilidad_select_tipo_solicitud").focus();
			setTimeout(function() {
				$('#sec_mepa_reporte_contabilidad_select_tipo_solicitud').select2('open');
			}, 500);

			return false;
		}
	});


});
// $('#sec_mepa_reporte_contabilidad_select_tipo_solicitud').val('11')
// $('#sec_mepa_reporte_contabilidad_select_tipo_solicitud').trigger('change');

function mepa_reporte_contabilidad_buscar_div_liquidacion_por_parametros()
{
	var tipo_solicitud = $("#sec_mepa_reporte_contabilidad_select_tipo_solicitud").val();

	if(tipo_solicitud == 0)
	{
		alertify.error('Seleccione Tipo Reporte',5);
		debugger
		$("#sec_mepa_reporte_contabilidad_select_tipo_solicitud").focus();
		setTimeout(function() {
			$('#sec_mepa_reporte_contabilidad_select_tipo_solicitud').select2('open');
		}, 500);

		return false;
	}
	else if(tipo_solicitud == 3)
	{
		mepa_reporte_contabilidad_listar_asignacion_Datatable();
	}
	else if(tipo_solicitud == 4)
	{
		mepa_reporte_contabilidad_listar_liquidacion_Datatable();
	}
	else if(tipo_solicitud == 5)
	{
		mepa_reporte_contabilidad_listar_caja_chica_fisico_virtual_Datatable();
	}
	else if(tipo_solicitud == 6)
	{
		mepa_reporte_contabilidad_listar_sin_caja_chica_realizada_Datatable();
	}
	else if(tipo_solicitud == 7)
	{
		mepa_reporte_contabilidad_listar_gastos_proveedores_datatable();
	}
	else if(tipo_solicitud == 8)
	{
		mepa_reporte_contabilidad_listar_gastos_ultimas_semanas_datatable();
	}
	else if(tipo_solicitud == 10)
	{
		mepa_reporte_contabilidad_listar_asignacion_saldos();
	}
	else if(tipo_solicitud == 11)
	{
		mepa_reporte_contabilidad_dashboard_caja_chica_datatable();
	}

	setTimeout(function() {
		$('#sec_mepa_reporte_contabilidad_select_tipo_solicitud').select2('open');
	}, 500);
}

function mepa_reporte_contabilidad_listar_liquidacion_Datatable()
{

	if(sec_id == "mepa" && sub_sec_id == "reporte_contabilidad")
	{
		var mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_desde").val();
		var mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_hasta").val();
		var mepa_reporte_contabilidad_param_liquidacion_situacion = $("#mepa_reporte_contabilidad_param_liquidacion_situacion").val();

		var data = {
			"accion": "mepa_reporte_contabilidad_listar_liquidacion",
			"mepa_reporte_contabilidad_param_liquidacion_fecha_desde" : mepa_reporte_contabilidad_param_liquidacion_fecha_desde,
			"mepa_reporte_contabilidad_param_liquidacion_fecha_hasta" : mepa_reporte_contabilidad_param_liquidacion_fecha_hasta,
			"mepa_reporte_contabilidad_param_liquidacion_situacion" : mepa_reporte_contabilidad_param_liquidacion_situacion
		}

		$("#mepa_reporte_contabilidad_div_btn_export").show();
		$("#mepa_reporte_contabilidad_btn_export_caja_chica").show();
		$("#mepa_reporte_contabilidad_liquidacion_div_tabla").show();
		
		tabla = $("#mepa_reporte_contabilidad_liquidacion_datatable").dataTable(
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
					url : "/sys/set_mepa_reporte_contabilidad.php",
					data : data,
					type : "POST",
					dataType : "json",
					beforeSend: function( xhr ) {
						loading(true);
					},
					complete: function(){
						loading(false);
					},
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();

	}
}

$("#mepa_reporte_contabilidad_btn_export_caja_chica").on('click', function () 
{

	var mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_desde").val();
	var mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_hasta").val();
	var mepa_reporte_contabilidad_param_liquidacion_situacion = $("#mepa_reporte_contabilidad_param_liquidacion_situacion").val();

	var data = {
		"accion": "mepa_reporte_contabilidad_caja_chica_export",
		"mepa_reporte_contabilidad_param_liquidacion_fecha_desde" : mepa_reporte_contabilidad_param_liquidacion_fecha_desde,
		"mepa_reporte_contabilidad_param_liquidacion_fecha_hasta" : mepa_reporte_contabilidad_param_liquidacion_fecha_hasta,
		"mepa_reporte_contabilidad_param_liquidacion_situacion" : mepa_reporte_contabilidad_param_liquidacion_situacion
	}

    $.ajax({
        url: "/sys/set_mepa_reporte_contabilidad.php",
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
			window.open(obj.ruta_archivo);
			loading(false);
        },
        error: function(resp, status) {

        }
    });
});

function mepa_reporte_contabilidad_listar_asignacion_Datatable()
{

	if(sec_id == "mepa" && sub_sec_id == "reporte_contabilidad")
	{
		var mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_desde").val();
		var mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_hasta").val();

		var data = {
			"accion": "mepa_reporte_contabilidad_listar_asignacion",
			"mepa_reporte_contabilidad_param_fecha_desde" : mepa_reporte_contabilidad_param_liquidacion_fecha_desde,
			"mepa_reporte_contabilidad_param_fecha_hasta" : mepa_reporte_contabilidad_param_liquidacion_fecha_hasta
		}
		
		$("#mepa_reporte_contabilidad_asignacion_div_tabla").show();
		$("#mepa_reporte_contabilidad_div_btn_export").show();
		$("#mepa_reporte_contabilidad_btn_export_asignacion_caja_chica").show();

		tabla = $("#mepa_reporte_contabilidad_asignacion_datatable").dataTable(
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
					url : "/sys/set_mepa_reporte_contabilidad.php",
					data : data,
					type : "POST",
					dataType : "json",
					beforeSend: function( xhr ) {
						loading(true);
					},
					complete: function(){
						loading(false);
					},
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				columnDefs:
				[
					{
						className: 'text-center',
						targets: [0, 1, 2, 3, 4, 5, 6, 7, 8]
					},
					{
						width: "150px", targets: 1
					},
					{
						width: "200px", targets: 2
					},
					{
						width: "200px", targets: 3
					},
					{
						width: "10px", targets: 4
					},
					{
						width: "10px", targets: 5
					},
					{
						width: "10px", targets: 6
					},
					{
						width: "200px", targets: 7
					},
					{
						width: "100px", targets: 8
					}
			    ],
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();

	}
}

$("#mepa_reporte_contabilidad_btn_export_asignacion_caja_chica").on('click', function () 
{
	
	var mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_desde").val();
	var mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_hasta").val();

	var data = {
		"accion": "mepa_reporte_contabilidad_tipo_rpt_cajas_chicas_export",
		"param_fecha_inicio" : mepa_reporte_contabilidad_param_liquidacion_fecha_desde,
		"param_fecha_fin" : mepa_reporte_contabilidad_param_liquidacion_fecha_hasta
	}

    $.ajax({
        url: "/sys/set_mepa_reporte_contabilidad.php",
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
			window.open(obj.ruta_archivo);
			loading(false);
        },
        error: function(resp, status) {

        }
    });
});

function mepa_reporte_contabilidad_listar_caja_chica_fisico_virtual_Datatable()
{
	
	if(sec_id == "mepa" && sub_sec_id == "reporte_contabilidad")
	{
		var mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_desde").val();
		var mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_hasta").val();
		var mepa_reporte_contabilidad_param_flag_envio_documento_fisico = $("#mepa_reporte_contabilidad_param_flag_envio_documento_fisico").val();

		if(mepa_reporte_contabilidad_param_flag_envio_documento_fisico == 0)
		{
			alertify.error('Seleccione documento fisico enviado',5);
			$("#mepa_reporte_contabilidad_param_flag_envio_documento_fisico").focus();
			setTimeout(function() {
				$('#mepa_reporte_contabilidad_param_flag_envio_documento_fisico').select2('open');
			}, 500);
			return false;
		}

		var data = {
			"accion": "mepa_reporte_contabilidad_listar_caja_chica_fisico_virtual",
			"mepa_reporte_contabilidad_param_liquidacion_fecha_desde" : mepa_reporte_contabilidad_param_liquidacion_fecha_desde,
			"mepa_reporte_contabilidad_param_liquidacion_fecha_hasta" : mepa_reporte_contabilidad_param_liquidacion_fecha_hasta,
			"mepa_reporte_contabilidad_param_flag_envio_documento_fisico" : mepa_reporte_contabilidad_param_flag_envio_documento_fisico
		}

		$("#mepa_reporte_contabilidad_div_btn_export").show();
		$("#mepa_reporte_contabilidad_btn_export_caja_chica_fisico_virtual").show();
		$("#mepa_reporte_contabilidad_caja_chica_fisico_virtual_div_tabla").show();
		
		tabla = $("#mepa_reporte_contabilidad_caja_chica_fisico_virtual_datatable").dataTable(
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
					url : "/sys/set_mepa_reporte_contabilidad.php",
					data : data,
					type : "POST",
					dataType : "json",
					beforeSend: function( xhr ) {
						loading(true);
					},
					complete: function(){
						loading(false);
					},
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();

	}
}

$("#mepa_reporte_contabilidad_btn_export_caja_chica_fisico_virtual").on('click', function () 
{

	var mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_desde").val();
	var mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_hasta").val();
	var mepa_reporte_contabilidad_param_flag_envio_documento_fisico = $("#mepa_reporte_contabilidad_param_flag_envio_documento_fisico").val();

	if(mepa_reporte_contabilidad_param_flag_envio_documento_fisico == 0)
	{
		alertify.error('Seleccione Situacion',5);
		$("#mepa_reporte_contabilidad_param_flag_envio_documento_fisico").focus();
		return false;
	}

	var data = {
		"accion": "mepa_reporte_contabilidad_caja_chica_fisico_virtual_export",
		"mepa_reporte_contabilidad_param_liquidacion_fecha_desde" : mepa_reporte_contabilidad_param_liquidacion_fecha_desde,
		"mepa_reporte_contabilidad_param_liquidacion_fecha_hasta" : mepa_reporte_contabilidad_param_liquidacion_fecha_hasta,
		"mepa_reporte_contabilidad_param_flag_envio_documento_fisico" : mepa_reporte_contabilidad_param_flag_envio_documento_fisico
	}

    $.ajax({
        url: "/sys/set_mepa_reporte_contabilidad.php",
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
			window.open(obj.ruta_archivo);
			loading(false);
        },
        error: function(resp, status) {

        }
    });
});

function mepa_reporte_contabilidad_listar_sin_caja_chica_realizada_Datatable()
{
	
	if(sec_id == "mepa" && sub_sec_id == "reporte_contabilidad")
	{
		var mepa_reporte_contabilidad_param_dias_habiles = $("#mepa_reporte_contabilidad_param_dias_habiles").val();
		
		if(mepa_reporte_contabilidad_param_dias_habiles == 0)
		{
			alertify.error('Error de valor cero',5);
			$("#mepa_reporte_contabilidad_param_dias_habiles").focus();
			return false;
		}

		var data = {
			"accion": "mepa_reporte_contabilidad_listar_sin_caja_chica_realizada",
			"mepa_reporte_contabilidad_param_dias_habiles" : mepa_reporte_contabilidad_param_dias_habiles
		}

		

		$("#mepa_reporte_contabilidad_div_btn_export").show();
		$("#mepa_reporte_contabilidad_btn_export_sin_caja_chica_realizada").show();
		$("#mepa_reporte_contabilidad_sin_caja_chica_realizada_div_tabla").show();
		
		tabla = $("#mepa_reporte_contabilidad_sin_caja_chica_realizada_datatable").dataTable(
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
					url : "/sys/set_mepa_reporte_contabilidad.php",
					data : data,
					type : "POST",
					dataType : "json",
					beforeSend: function( xhr ) {
						loading(true);
					},
					complete: function(){
						loading(false);
					},
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();

	}
}

$("#mepa_reporte_contabilidad_btn_export_sin_caja_chica_realizada").on('click', function () 
{

	var mepa_reporte_contabilidad_param_dias_habiles = $("#mepa_reporte_contabilidad_param_dias_habiles").val();

	if(mepa_reporte_contabilidad_param_dias_habiles == 0)
	{
		alertify.error('Error de valor cero',5);
		$("#mepa_reporte_contabilidad_param_dias_habiles").focus();
		return false;
	}

	var data = {
		"accion": "mepa_reporte_contabilidad_sin_caja_chica_realizada_export",
		"mepa_reporte_contabilidad_param_dias_habiles" : mepa_reporte_contabilidad_param_dias_habiles
	}
	

    $.ajax({
        url: "/sys/set_mepa_reporte_contabilidad.php",
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
			window.open(obj.ruta_archivo);
			loading(false);
        },
        error: function(resp, status) {

        }
    });
});

function mepa_reporte_contabilidad_listar_gastos_proveedores_datatable()
{
	
	if(sec_id == "mepa" && sub_sec_id == "reporte_contabilidad")
	{
		var mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_desde").val();
		var mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_hasta").val();
		var mepa_reporte_contabilidad_param_liquidacion_situacion_gastos_proveedor = $("#mepa_reporte_contabilidad_param_liquidacion_situacion_gastos_proveedor").val();

		var data = {
			"accion": "mepa_reporte_contabilidad_listar_gastos_proveedores",
			"mepa_reporte_contabilidad_param_liquidacion_fecha_desde" : mepa_reporte_contabilidad_param_liquidacion_fecha_desde,
			"mepa_reporte_contabilidad_param_liquidacion_fecha_hasta" : mepa_reporte_contabilidad_param_liquidacion_fecha_hasta,
			"mepa_reporte_contabilidad_param_liquidacion_situacion_gastos_proveedor" : mepa_reporte_contabilidad_param_liquidacion_situacion_gastos_proveedor
		
		}
		
		$("#mepa_reporte_contabilidad_div_btn_export").show();
		$("#mepa_reporte_contabilidad_btn_export_gastos_proveedores").show();
		$("#mepa_reporte_contabilidad_gastos_proveedores_div_tabla").show();
		
		tabla = $("#mepa_reporte_contabilidad_gastos_proveedores_datatable").dataTable(
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
					url : "/sys/set_mepa_reporte_contabilidad.php",
					data : data,
					type : "POST",
					dataType : "json",
					beforeSend: function( xhr ) {
						loading(true);
					},
					complete: function(){
						loading(false);
					},
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();

	}
}

$("#mepa_reporte_contabilidad_btn_export_gastos_proveedores").on('click', function () 
{
	var mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_desde").val();
	var mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $("#mepa_reporte_contabilidad_param_liquidacion_fecha_hasta").val();
	var mepa_reporte_contabilidad_param_liquidacion_situacion_gastos_proveedor = $("#mepa_reporte_contabilidad_param_liquidacion_situacion_gastos_proveedor").val();

	var data = {
		"accion": "mepa_reporte_contabilidad_listar_gastos_proveedores_export",
		"mepa_reporte_contabilidad_param_liquidacion_fecha_desde" : mepa_reporte_contabilidad_param_liquidacion_fecha_desde,
		"mepa_reporte_contabilidad_param_liquidacion_fecha_hasta" : mepa_reporte_contabilidad_param_liquidacion_fecha_hasta,
		"mepa_reporte_contabilidad_param_liquidacion_situacion_gastos_proveedor" : mepa_reporte_contabilidad_param_liquidacion_situacion_gastos_proveedor
	}
	
	$.ajax({
        url: "/sys/set_mepa_reporte_contabilidad.php",
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
			window.open(obj.ruta_archivo);
			loading(false);
        },
        error: function(resp, status) {

        }
    });
});

function mepa_reporte_contabilidad_listar_gastos_ultimas_semanas_datatable()
{
	
	if(sec_id == "mepa" && sub_sec_id == "reporte_contabilidad")
	{
		var data = {
			"accion": "mepa_reporte_contabilidad_listar_gastos_ultimas_semanas"
		}

		$("#mepa_reporte_contabilidad_gastos_ultimas_semanas_div_tabla").show();
		$("#mepa_reporte_contabilidad_div_btn_export").show();
		$("#mepa_reporte_contabilidad_btn_export_gastos_ultimas_semanas").show();
		
		tabla = $("#mepa_reporte_contabilidad_gastos_ultimas_semanas_datatable").dataTable(
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
					url : "/sys/set_mepa_reporte_contabilidad.php",
					data : data,
					type : "POST",
					dataType : "json",
					beforeSend: function( xhr ) {
						loading(true);
					},
					complete: function(){
						loading(false);
					},
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();

	}
}

$("#mepa_reporte_contabilidad_btn_export_gastos_ultimas_semanas").on('click', function () 
{
	
	var data = {
		"accion": "mepa_reporte_contabilidad_listar_gastos_ultimas_semanas_export"
	}
	
	$.ajax({
        url: "/sys/set_mepa_reporte_contabilidad.php",
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
			window.open(obj.ruta_archivo);
			loading(false);
        },
        error: function(resp, status) {

        }
    });
});

function mepa_reporte_contabilidad_listar_asignacion_saldos()
{
	
	if(sec_id == "mepa" && sub_sec_id == "reporte_contabilidad")
	{
		var param_fecha_desde = $("#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_desde").val().trim();
		var param_fecha_hasta = $("#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_hasta").val().trim();
		var param_tipo_red = $("#mepa_reporte_contabilidad_param_saldos_caja_chica_tipo_red").val();

		if(param_fecha_desde == "")
		{
			alertify.error('Seleccione Fecha Desde',5);
			$("#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_desde").focus();
			setTimeout(function() {
				$('#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_desde').select2('open');
			}, 500);

			return false;
		}

		if(param_fecha_hasta == "")
		{
			alertify.error('Seleccione Fecha Hasta',5);
			$("#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_hasta").focus();
			setTimeout(function() {
				$('#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_hasta').select2('open');
			}, 500);
			
			return false;
		}
		
		if(param_tipo_red == "0")
		{
			alertify.error('Seleccione Tipo Red',5);
			$("#mepa_reporte_contabilidad_param_saldos_caja_chica_tipo_red").focus();
			setTimeout(function() {
				$('#mepa_reporte_contabilidad_param_saldos_caja_chica_tipo_red').select2('open');
			}, 500);
			
			return false;
		}

		var data = {
			"accion": "mepa_reporte_contabilidad_listar_asignacion_saldos",
			"param_fecha_desde" : param_fecha_desde,
			"param_fecha_hasta" : param_fecha_hasta,
			"param_tipo_red" : param_tipo_red
		}
		
		$("#mepa_reporte_contabilidad_div_tabla_saldo_caja_chica").show();
		$("#mepa_reporte_contabilidad_div_btn_export").show();
		$("#mepa_reporte_contabilidad_btn_tipo_export").show();

		tabla = $("#mepa_reporte_contabilidad_datatable_saldo_caja_chica").dataTable(
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
					url : "/sys/set_mepa_reporte_contabilidad.php",
					data : data,
					type : "POST",
					dataType : "json",
					beforeSend: function( xhr ) {
						loading(true);
					},
					complete: function(){
						loading(false);
					},
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				columnDefs:
				[
					{
						className: 'text-center',
						targets: [0, 1, 2, 3, 4, 5, 6, 7, 8]
					},
					{
						width: "10px", targets: 0
					},
					{
						width: "200px", targets: 1
					},
					{
						width: "200px", targets: 2
					},
					{
						width: "100px", targets: 3
					},
					{
						width: "200px", targets: 4
					},
					{
						width: "100px", targets: 5
					},
					{
						width: "100px", targets: 6
					},
					{
						width: "100px", targets: 7
					},
					{
						width: "100px", targets: 8
					}
			    ],
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();

	}
}

$("#mepa_reporte_contabilidad_btn_tipo_export").on('click', function () 
{
	var param_tipo_solicitud = $("#sec_mepa_reporte_contabilidad_select_tipo_solicitud").val();

	if(param_tipo_solicitud == 0)
	{
		alertify.error('Seleccione Tipo Reporte',5);
		debugger
		$("#sec_mepa_reporte_contabilidad_select_tipo_solicitud").focus();
		setTimeout(function() {
			$('#sec_mepa_reporte_contabilidad_select_tipo_solicitud').select2('open');
		}, 500);
		
		return false;
	}
	else if(param_tipo_solicitud == 10)
	{
		var param_fecha_desde = $("#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_desde").val().trim();
		var param_fecha_hasta = $("#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_hasta").val().trim();
		var param_tipo_red = $("#mepa_reporte_contabilidad_param_saldos_caja_chica_tipo_red").val();

		if(param_fecha_desde == "")
		{
			alertify.error('Seleccione Fecha Desde',5);
			$("#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_desde").focus();
			setTimeout(function() {
				$('#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_desde').select2('open');
			}, 500);

			return false;
		}

		if(param_fecha_hasta == "")
		{
			alertify.error('Seleccione Fecha Hasta',5);
			$("#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_hasta").focus();
			setTimeout(function() {
				$('#mepa_reporte_contabilidad_param_saldos_caja_chica_fecha_hasta').select2('open');
			}, 500);
			
			return false;
		}
		
		if(param_tipo_red == "0")
		{
			alertify.error('Seleccione Tipo Red',5);
			$("#mepa_reporte_contabilidad_param_saldos_caja_chica_tipo_red").focus();
			setTimeout(function() {
				$('#mepa_reporte_contabilidad_param_saldos_caja_chica_tipo_red').select2('open');
			}, 500);
			
			return false;
		}

		var data = {
			"accion": "mepa_reporte_contabilidad_asignacion_saldos_export",
			"param_fecha_desde" : param_fecha_desde,
			"param_fecha_hasta" : param_fecha_hasta,
			"param_tipo_red" : param_tipo_red
		}
	}
	else
	{
		alertify.error('No se encontro el tipo de reporte',5);
		return false;
	}

    $.ajax({
        url: "/sys/set_mepa_reporte_contabilidad.php",
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
			window.open(obj.ruta_archivo);
			loading(false);
        },
        error: function(resp, status) {

        }
    });
});

var ctx = document.getElementById('myChart_dashboard_caja__chica');
let myChart;

function mepa_reporte_contabilidad_dashboard_caja_chica_datatable(){
	if(sec_id == "mepa" && sub_sec_id == "reporte_contabilidad")
	{
		var mes = $("#sec_mepa_reporte_contabilidad_select_mes").val();
		var year = $("#sec_mepa_reporte_contabilidad_select_year").val();
		var razon_social = $("#sec_mepa_reporte_contabilidad_select_razon_social").val();
		var usuarios = $("#sec_mepa_reporte_contabilidad_select_usuarios").val();
		var descripcion = $("#sec_mepa_reporte_contabilidad_select_descripcion").val();
		var concepto = $("#sec_mepa_reporte_contabilidad_select_concepto").val();
		
		var data = {
			"accion": "mepa_reporte_contabilidad_dashboard_caja_chica",
			"mes" : mes,
			"year" : year,
			'razon_social' : razon_social,
			'usuarios' : usuarios,
			'descripcion' : descripcion,
			'concepto' : concepto,	
		}
		
		$("#mepa_reporte_contabilidad_myChart_dashboard_caja__chica").show();
		
		console.log(data)
		$.ajax({
			url : "/sys/set_mepa_reporte_contabilidad.php",
			data : data,
			type : "POST",
			dataType : "json",
			success: function(response){
				// console.log(response.query)
				var result = response.result
				if(response.status == 200){
					cargarGraficoDashboard(ctx, result)
				} else if(response.status == 500){
					swal({
						title: "Ocurrió un error",
						text: response.msg,
						type: "error",
						closeOnConfirm: true
					});
				}
			},
			beforeSend: function( xhr ) {
				loading(true);
			},
			complete: function(){
				loading(false);
			},
			error : function(e)
			{
				console.log(e.responseText);
			}
		})	 
		
		
	}
}

function cargarGraficoDashboard(ctx, result){ 
	if (myChart) {
        myChart.destroy();
    }

	var values_asignacion = [];
	var values_liquidaciones = [];
	var values_meses = [];

	result.forEach(element => {
		var usuarios = $("#sec_mepa_reporte_contabilidad_select_usuarios").val();
		debugger
		if(usuarios != null && usuarios.length > 0){
			values_asignacion.push(element['fondo_asignado'])
		}
		values_liquidaciones.push(element['importe_mes'])
		values_meses.push(element['mes_text'])
	});

    myChart = new Chart(ctx, {
		data: {
			labels: values_meses,
			datasets: [{
					type: 'bar',
					label: 'Liquidación',
					data: values_liquidaciones,
					borderColor: 'rgb(255, 99, 132)',
					backgroundColor: 'rgba(255, 99, 132, 0.8)'
				}, {
					type: 'line',
					label: 'Asignación',
					data: values_asignacion,
					fill: false,
					borderColor: 'rgb(54, 162, 235)',
					backgroundColor: 'rgba(155, 200, 132, 0.8)'
				}]
		}
	});
}

$('#sec_mepa_reporte_contabilidad_select_razon_social').change(function(){
	var val = $("#sec_mepa_reporte_contabilidad_select_razon_social").val();
	if (val) {
		getUsuariosDashboard(true, false)
	} else {
		$('#sec_mepa_reporte_contabilidad_select_descripcion').html('');
		$('#sec_mepa_reporte_contabilidad_select_usuarios').html('');
	}
})
let zonas_prev = [];
$('#sec_mepa_reporte_contabilidad_select_descripcion').change(function(){
	var val = $("#sec_mepa_reporte_contabilidad_select_descripcion").val();
	if (val) {
		getUsuariosDashboard(false, true)
	} else {
		$('#sec_mepa_reporte_contabilidad_select_usuarios').html('');
	}
})

function getUsuariosDashboard(cargar_zonas, cargar_usuarios){

	var descripcion = null;
	var razon_social = null;

	if (cargar_usuarios) {
		descripcion = $("#sec_mepa_reporte_contabilidad_select_descripcion").val();
	}	
	if (cargar_zonas) {
		razon_social = $("#sec_mepa_reporte_contabilidad_select_razon_social").val();
	}

	var data = {
		"accion": "mepa_reporte_contabilidad_dashboard_get_usuarios",
		"razon_social" : razon_social,
		"descripcion" : descripcion,
	}
	
	console.log(data)
	$.ajax({
		url : "/sys/set_mepa_reporte_contabilidad.php",
		data : data,
		type : "POST",
		dataType : "json",
		success: function(response){
			debugger
			console.log(response)
			if(response.status == 200){
				
				if(response.result.usuarios.length > 0){
					var result = response.result.usuarios
					$('#sec_mepa_reporte_contabilidad_select_usuarios').html('');
					result.forEach(data_option => {
						var newOption = new Option(data_option.nombre_completo, data_option.id, false, false);
						$('#sec_mepa_reporte_contabilidad_select_usuarios').append(newOption);
					});

				}


				if(response.result.zonas.length > 0){
					var result = response.result.zonas
					zonas_prev = $('#sec_mepa_reporte_contabilidad_select_descripcion').val(); 
					$('#sec_mepa_reporte_contabilidad_select_descripcion').html('');
					result.forEach(data_option => {
						var newOption = new Option(data_option.nombre, data_option.id, false, false);
						$('#sec_mepa_reporte_contabilidad_select_descripcion').append(newOption);
					});
					
				}

			} else if(response.status == 500){
				swal({
					title: "Ocurrió un error",
					text: response.msg,
					type: "error",
					closeOnConfirm: true
				});
			}



		},
		beforeSend: function( xhr ) {
			loading(true);
		},
		complete: function(){
			loading(false);
		},
		error : function(e)
		{
			console.log(e.responseText);
		}
	})	 
		
}