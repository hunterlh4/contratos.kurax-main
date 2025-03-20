function sec_mepa_cajas_chicas_rechazadas()
{
	sec_mepa_cajas_chicas_rechazadas_inicializar_param_fechas();
}
// FIN FUNCION DE INICIALIZACION

function sec_mepa_cajas_chicas_rechazadas_inicializar_param_fechas()
{
	// INICIO INICIALIZACION DE DATEPICKER
	$('.mepa_cajas_chicas_rechazadas_datepicker')
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

function mepa_cajas_chicas_rechazadas_buscar()
{
	$('#mepa_cajas_chicas_rechazadas_div_detalle').hide();
	$("#mepa_cajas_chicas_rechazadas_div_tabla").hide();
	
	mepa_cajas_chicas_rechazadas_listar_liquidacion_datatable();
}

function mepa_cajas_chicas_rechazadas_listar_liquidacion_datatable()
{
	if(sec_id == "mepa" && sub_sec_id == "cajas_chicas_rechazadas")
	{
		var param_fecha_desde = $("#mepa_cajas_chicas_rechazadas_param_fecha_desde").val();
		var param_fecha_hasta = $("#mepa_cajas_chicas_rechazadas_param_fecha_hasta").val();

		var data = {
			"accion": "mepa_cajas_chicas_rechazadas_listar_liquidacion",
			"param_fecha_desde" : param_fecha_desde,
			"param_fecha_hasta" : param_fecha_hasta
		}
		
		$("#mepa_cajas_chicas_rechazadas_div_tabla").show();
		
		tabla = $("#mepa_cajas_chicas_rechazadas_datatable").dataTable(
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
					url : "/sys/set_mepa_cajas_chicas_rechazadas.php",
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

function mepa_cajas_rechazadas_ver_detalle(id_liquidacion, usuario, empresa, zona, num_caja)
{
	$('#mepa_cajas_chicas_rechazadas_div_detalle').hide();

	var data = {
		"accion": "mepa_cajas_rechazadas_ver_detalle",
		"id_liquidacion": id_liquidacion
	}

	auditoria_send({ "proceso": "mepa_cajas_rechazadas_ver_detalle", "data": data });

	$.ajax({
		url: "/sys/set_mepa_cajas_chicas_rechazadas.php",
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
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400)
			{
				return false;
			}
			
			if (parseInt(respuesta.http_code) == 200) 
			{
				$("#detalle_usuario").html(usuario);
				$("#detalle_num_caja").html(num_caja);
				$("#detalle_empresa").html(empresa);
				$("#detalle_zona").html(zona);
				$('#mepa_cajas_chicas_rechazadas_cuerpo_detalle').html(respuesta.result);
				$('#mepa_cajas_chicas_rechazadas_div_detalle').show();

				return false;
			}
		},
		error: function() {}
	});
	
}
