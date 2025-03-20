// INICIO FUNCION DE INICIALIZACION
function sec_mepa_reporte_tesoreria()
{
	var tipo_solicitud = 0;
	var param_fecha_desde = "";
	var param_fecha_hasta = "";
	var param_situacion_contabilidad = "";
	var param_situacion_tesoreria = "";

	$("#mepa_reporte_tesoreria_div_btn_export").hide();
	$("#mepa_reporte_tesoreria_listar_asignacion_table").hide();
	$("#mepa_reporte_tesoreria_listar_liquidacion_table").hide();
	
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_reporte_tesoreria_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	sec_mepa_reporte_tesoreria_inicializar_param_fechas();
}
// FIN FUNCION DE INICIALIZACION

function sec_mepa_reporte_tesoreria_inicializar_param_fechas()
{
	// INICIO INICIALIZACION DE DATEPICKER
	$('.mepa_reporte_tesoreria_datepicker')
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

$('#mepa_reporte_tesoreria_param_tipo_solicitud').change(function () 
{
	$("#mepa_reporte_tesoreria_param_tipo_solicitud option:selected").each(function ()
	{	
		$("#mepa_reporte_tesoreria_div_btn_export").hide();
		$("#mepa_reporte_tesoreria_listar_asignacion_table").hide();
		$("#mepa_reporte_tesoreria_listar_liquidacion_table").hide();
		
		var selectValor = $(this).val();

		if(selectValor == 0)
		{
			alertify.error('Seleccione Tipo Reporte',5);
			$("#mepa_reporte_tesoreria_param_tipo_solicitud").focus();
			setTimeout(function() {
				$('#mepa_reporte_tesoreria_param_tipo_solicitud').select2('open');
			}, 500);

			return false;
		}
		else if(selectValor == 1)
		{
			// LIQUIDACION CAJA CHICA
			$("#mepa_reporte_tesoreria_div_param_situacion_contabilidad").show();
		}
		else if(selectValor == 2)
		{
			// ASIGNACION CAJA CHICA
			$("#mepa_reporte_tesoreria_div_param_situacion_contabilidad").hide();
		}
	});
});

function mepa_reporte_tesoreria_btn_buscar_reporte()
{
	tipo_solicitud = $("#mepa_reporte_tesoreria_param_tipo_solicitud").val();
	param_fecha_desde = $("#mepa_reporte_tesoreria_param_fecha_desde").val();
	param_fecha_hasta = $("#mepa_reporte_tesoreria_param_fecha_hasta").val();
	param_situacion_contabilidad = $("#mepa_reporte_tesoreria_param_situacion_contabilidad").val();
	param_situacion_tesoreria = $("#mepa_reporte_tesoreria_param_situacion_tesoreria").val();
	
	if(tipo_solicitud == 0)
	{
		alertify.error('Seleccione Tipo Reporte',5);
		$("#mepa_reporte_tesoreria_param_tipo_solicitud").focus();
		setTimeout(function() {
			$('#mepa_reporte_tesoreria_param_tipo_solicitud').select2('open');
		}, 500);
		return false;
	}
	else if(tipo_solicitud == 1)
	{
		mepa_reporte_tesoreria_listar_liquidacion_datatable();
	}
	else if(tipo_solicitud == 2)
	{
		mepa_reporte_tesoreria_listar_asignacion_datatable();
	}

	setTimeout(function() {
		$('#mepa_reporte_tesoreria_param_tipo_solicitud').select2('open');
	}, 500);
	return false;
}

function mepa_reporte_tesoreria_listar_asignacion_datatable()
{
	
	if(sec_id == "mepa" && sub_sec_id == "reporte_tesoreria")
	{
		var data = {
			"accion": "mepa_reporte_tesoreria_listar_asignacion",
			"param_tipo_solicitud" : tipo_solicitud,
			"param_fecha_desde" : param_fecha_desde,
			"param_fecha_hasta" : param_fecha_hasta,
			"param_situacion_tesoreria" : param_situacion_tesoreria
		}

		$("#mepa_reporte_tesoreria_div_btn_export").show();
		$("#mepa_reporte_tesoreria_asignacion_btn_export").show();
		$("#mepa_reporte_tesoreria_liquidacion_btn_export").hide();
		$("#mepa_reporte_tesoreria_listar_asignacion_table").show();
		$("#mepa_reporte_tesoreria_listar_liquidacion_table").hide();
		
		tabla = $("#mepa_reporte_tesoreria_asignacion_datatable").dataTable(
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
					url : "/sys/set_mepa_reporte_tesoreria.php",
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
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();

	}
}

function mepa_reporte_tesoreria_listar_liquidacion_datatable()
{
	if(sec_id == "mepa" && sub_sec_id == "reporte_tesoreria")
	{
		var data = {
			"accion": "mepa_reporte_tesoreria_listar_liquidacion",
			"param_tipo_solicitud" : tipo_solicitud,
			"param_fecha_desde" : param_fecha_desde,
			"param_fecha_hasta" : param_fecha_hasta,
			"param_situacion_contabilidad" : param_situacion_contabilidad,
			"param_situacion_tesoreria" : param_situacion_tesoreria
		}

		$("#mepa_reporte_tesoreria_div_btn_export").show();
		$("#mepa_reporte_tesoreria_asignacion_btn_export").hide();
		$("#mepa_reporte_tesoreria_liquidacion_btn_export").show();
		$("#mepa_reporte_tesoreria_listar_asignacion_table").hide();
		$("#mepa_reporte_tesoreria_listar_liquidacion_table").show();
		
		tabla = $("#mepa_reporte_tesoreria_liquidacion_datatable").dataTable(
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
					url : "/sys/set_mepa_reporte_tesoreria.php",
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
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();

	}
}

$("#mepa_reporte_tesoreria_asignacion_btn_export").on('click', function () 
{

	var data = {
		"accion": "mepa_reporte_tesoreria_asignacion_pagados_export",
		"param_tipo_solicitud" : tipo_solicitud,
		"param_fecha_desde" : param_fecha_desde,
		"param_fecha_hasta" : param_fecha_hasta,
		"param_situacion_tesoreria" : param_situacion_tesoreria
	}

    $.ajax({
        url: "/sys/set_mepa_reporte_tesoreria.php",
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

$("#mepa_reporte_tesoreria_liquidacion_btn_export").on('click', function () 
{

	var data = {
		"accion": "mepa_reporte_tesoreria_liquidacion_pagados_export",
		"param_tipo_solicitud" : tipo_solicitud,
		"param_fecha_desde" : param_fecha_desde,
		"param_fecha_hasta" : param_fecha_hasta,
		"param_situacion_contabilidad" : param_situacion_contabilidad,
		"param_situacion_tesoreria" : param_situacion_tesoreria
	}

    $.ajax({
        url: "/sys/set_mepa_reporte_tesoreria.php",
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