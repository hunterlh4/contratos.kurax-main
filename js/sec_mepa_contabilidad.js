// INICIO FUNCION DE INICIALIZACION
function sec_mepa_contabilidad()
{
	$('#mepa_contabilidad_div_liquidacion').hide();

	$('#sec_mepa_contabilidad_select_tipo_solicitud').change(function () 
	{
		$("#sec_mepa_contabilidad_select_tipo_solicitud option:selected").each(function ()
		{	
			var selectValor = $(this).val();

			if (selectValor == '1') 
			{
				$('#mepa_contabilidad_div_asignacion').show();
				$('#mepa_contabilidad_asignacion_div_tabla').hide();
				$('#mepa_contabilidad_div_liquidacion').hide();
				
			}
			else if (selectValor == '2') 
			{
				$('#mepa_contabilidad_div_asignacion').hide();
				$('#mepa_contabilidad_div_liquidacion').show();
				$('#mepa_contabilidad_liquidacion_div_tabla').hide();
			}
		});
	});

	sec_mepa_contabilidad_inicializar_param_fechas();
}
// FIN FUNCION DE INICIALIZACION

function sec_mepa_contabilidad_inicializar_param_fechas()
{
	// INICIO INICIALIZACION DE DATEPICKER
	$('.mepa_contabilidad_datepicker')
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

function mepa_contabilidad_buscar_div_asignacion_por_parametros()
{
	mepa_contabilidad_listar_asignacion_Datatable();
}

function mepa_contabilidad_listar_asignacion_Datatable()
{
	
	if(sec_id == "mepa" && sub_sec_id == "contabilidad")
	{
		var mepa_contabilidad_param_asignacion_fecha_desde = $("#mepa_contabilidad_param_asignacion_fecha_desde").val();
		var mepa_contabilidad_param_asignacion_fecha_hasta = $("#mepa_contabilidad_param_asignacion_fecha_hasta").val();

		var data = {
			"accion": "mepa_contabilidad_listar_asignacion",
			"mepa_contabilidad_param_asignacion_fecha_desde" : mepa_contabilidad_param_asignacion_fecha_desde,
			"mepa_contabilidad_param_asignacion_fecha_hasta" : mepa_contabilidad_param_asignacion_fecha_hasta
		}

		$("#mepa_contabilidad_asignacion_div_tabla").show();
		
		tabla = $("#mepa_contabilidad_asignacion_datatable").dataTable(
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
					url : "/sys/set_mepa_contabilidad.php",
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

function mepa_contabilidad_buscar_div_liquidacion_por_parametros()
{
	mepa_contabilidad_listar_liquidacion_Datatable();
}

function mepa_contabilidad_listar_liquidacion_Datatable()
{
	
	if(sec_id == "mepa" && sub_sec_id == "contabilidad")
	{
		var mepa_contabilidad_param_liquidacion_fecha_desde = $("#mepa_contabilidad_param_liquidacion_fecha_desde").val();
		var mepa_contabilidad_param_liquidacion_fecha_hasta = $("#mepa_contabilidad_param_liquidacion_fecha_hasta").val();

		var data = {
			"accion": "mepa_contabilidad_listar_liquidacion",
			"mepa_contabilidad_param_liquidacion_fecha_desde" : mepa_contabilidad_param_liquidacion_fecha_desde,
			"mepa_contabilidad_param_liquidacion_fecha_hasta" : mepa_contabilidad_param_liquidacion_fecha_hasta
		}

		$("#mepa_contabilidad_liquidacion_div_tabla").show();
		
		tabla = $("#mepa_contabilidad_liquidacion_datatable").dataTable(
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
					url : "/sys/set_mepa_contabilidad.php",
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
