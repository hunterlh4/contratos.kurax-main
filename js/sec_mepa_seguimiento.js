// INICIO FUNCION DE INICIALIZACION
function sec_mepa_seguimiento()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_seguimiento_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA
}
// FIN FUNCION DE INICIALIZACION

function mepa_seguimiento__buscar_solicitud()
{
	$("#mepa_seguimiento_asignacion_div_tabla").hide();
	$("#mepa_seguimiento_liquidacion_div_tabla").hide();
	$("#mepa_seguimiento_movilidad_div_tabla").hide();

	var param_tipo_solicitud = $("#sec_mepa_seguimiento_tipo_solicitud").val();

	if(param_tipo_solicitud == 0)
	{
		alertify.error('Seleccione Tipo Solicitud',5);
		$("#sec_mepa_seguimiento_tipo_solicitud").focus();
		setTimeout(function() {
			$('#sec_mepa_seguimiento_tipo_solicitud').select2('open');
		}, 500);

		return false;
	}
	else if(param_tipo_solicitud == 1)
	{
		mepa_seguimiento_listar_asignacion();
	}
	else if(param_tipo_solicitud == 2)
	{
		mepa_seguimiento_listar_liquidacion();
	}
	else if(param_tipo_solicitud == 3)
	{
		mepa_seguimiento_listar_movilidad();
	}
	else
	{
		alertify.error('No se encontro el Tipo Solicitud',5);
		return false;
	}

	setTimeout(function() {
		$('#sec_mepa_seguimiento_tipo_solicitud').select2('open');
	}, 500);
}

function mepa_seguimiento_listar_asignacion()
{
	if(sec_id == "mepa" && sub_sec_id == "seguimiento")
	{
		var param_tipo_solicitud = $("#sec_mepa_seguimiento_tipo_solicitud").val();
		var param_usuario = $("#sec_mepa_seguimiento_usuario").val();

		if(param_tipo_solicitud == 0)
		{
			alertify.error('Seleccione Tipo Solicitud',5);
			$("#sec_mepa_seguimiento_tipo_solicitud").focus();
			setTimeout(function() {
				$('#sec_mepa_seguimiento_tipo_solicitud').select2('open');
			}, 500);

			return false;
		}

		var data = {
			"accion": "mepa_seguimiento_listar_asignacion",
			"param_usuario" : param_usuario
		}

		$("#mepa_seguimiento_asignacion_div_tabla").show();

		tabla = $("#mepa_seguimiento_asignacion_datatable").dataTable(
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
					url : "/sys/set_mepa_seguimiento.php",
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
				aLengthMenu:[10, 20, 30, 40, 50, 100],
				"order" : 
				[
					1, "desc"	
				]
			}
		).DataTable();
	}
	else
	{
		alertify.error('Ocurrió un error, vuelve a refrescar la página',5);
		return false;
	}
}

function mepa_seguimiento_listar_liquidacion()
{
	if(sec_id == "mepa" && sub_sec_id == "seguimiento")
	{
		var param_tipo_solicitud = $("#sec_mepa_seguimiento_tipo_solicitud").val();
		var param_usuario = $("#sec_mepa_seguimiento_usuario").val();

		if(param_tipo_solicitud == 0)
		{
			alertify.error('Seleccione Tipo Solicitud',5);
			$("#sec_mepa_seguimiento_tipo_solicitud").focus();
			setTimeout(function() {
				$('#sec_mepa_seguimiento_tipo_solicitud').select2('open');
			}, 500);

			return false;
		}

		var data = {
			"accion": "mepa_seguimiento_listar_liquidacion",
			"param_usuario" : param_usuario
		}

		$("#mepa_seguimiento_liquidacion_div_tabla").show();
		
		tabla = $("#mepa_seguimiento_liquidacion_datatable").dataTable(
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
					url : "/sys/set_mepa_seguimiento.php",
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
				aLengthMenu:[10, 20, 30, 40, 50, 100],
				"order" : 
				[
					1, "desc"	
				]
		}).DataTable();
	}
	else
	{
		alertify.error('Ocurrió un error, vuelve a refrescar la página',5);
		return false;
	}
}

function mepa_seguimiento_listar_movilidad()
{
	if(sec_id == "mepa" && sub_sec_id == "seguimiento")
	{
		var param_tipo_solicitud = $("#sec_mepa_seguimiento_tipo_solicitud").val();
		var param_usuario = $("#sec_mepa_seguimiento_usuario").val();

		if(param_tipo_solicitud == 0)
		{
			alertify.error('Seleccione Tipo Solicitud',5);
			$("#sec_mepa_seguimiento_tipo_solicitud").focus();
			setTimeout(function() {
				$('#sec_mepa_seguimiento_tipo_solicitud').select2('open');
			}, 500);

			return false;
		}

		var data = {
			"accion": "mepa_seguimiento_listar_movilidad",
			"param_usuario" : param_usuario
		}

		$("#mepa_seguimiento_movilidad_div_tabla").show();
		
		tabla = $("#mepa_seguimiento_movilidad_datatable").dataTable(
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
					url : "/sys/set_mepa_seguimiento.php",
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
				aLengthMenu:[10, 20, 30, 40, 50, 100],
				"order" : 
				[
					1, "desc"	
				]
		}).DataTable();
	}
}