// INICIO PARAMETRO DE LOCAL STORAGE
const local_storage_param_tipo_reporte = "mepa_solicitud_rendicion_caja_chica_local_storage_param_tipo_reporte";
const local_storage_param_tipo_red = "mepa_solicitud_rendicion_caja_chica_local_storage_param_tipo_red";
const local_storage_param_zona = "mepa_solicitud_rendicion_caja_chica_local_storage_param_zona";
const local_storage_param_usuario = "mepa_solicitud_rendicion_caja_chica_local_storage_param_tipo_usuario";
const local_storage_param_contabilidad = "mepa_solicitud_rendicion_caja_chica_local_storage_param_tipo_contabilidad";

let storage_param_tipo_reporte = localStorage.getItem(local_storage_param_tipo_reporte);
let storage_param_tipo_red = localStorage.getItem(local_storage_param_tipo_red);
let storage_param_zona = localStorage.getItem(local_storage_param_zona);
let storage_param_usuario = localStorage.getItem(local_storage_param_usuario);
let storage_param_contabilidad = localStorage.getItem(local_storage_param_contabilidad);

// FIN PARAMETRO DE LOCAL STORAGE

function sec_mepa_solicitud_rendicion_caja_chica()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_solicitud_rendicion_caja_chica_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	//INICIO PARAMETROS DE BUSQUEDA EN EL LOCALSTORAGE
	
	if(storage_param_tipo_reporte != null)
	{	
		$("#mepa_rendicion_caja_chica_param_tipo_reporte").val(storage_param_tipo_reporte).change();

		if(storage_param_tipo_reporte == 1)
		{
			if(storage_param_tipo_red != null)
			{
				$("#mepa_rendicion_caja_chica_param_tipo_red").val(storage_param_tipo_red).change();
			}

			if(storage_param_zona != null)
			{
				$("#mepa_rendicion_caja_chica_param_zona").val(storage_param_zona).change();
			}

			if(storage_param_contabilidad != null)
			{
				$("#mepa_rendicion_caja_chica_param_situacion_contabilidad").val(storage_param_contabilidad).change();
			}

			if(storage_param_tipo_reporte != null && storage_param_tipo_reporte == 1 
				&& storage_param_tipo_red != null 
				&& storage_param_zona != null 
				&& storage_param_contabilidad != null 
				&& storage_param_usuario != null)
			{
				mepa_rendicion_caja_chica_listar_liquidacion_datatable();
			}
		}
		else if(storage_param_tipo_reporte == 2)
		{
			if(storage_param_tipo_red != null)
			{
				$("#mepa_rendicion_caja_chica_param_tipo_red").val(storage_param_tipo_red).change();
			}
		}
	}
	
	//FIN PARAMETROS DE BUSQUEDA EN EL LOCALSTORAGE

	mepa_solicitud_rendicion_caja_chica_historial_enviado_a_tesoreria();
}

$('#mepa_rendicion_caja_chica_param_tipo_reporte').change(function ()
{
	var selectValor = $(this).val();

	if(selectValor == 0)
	{
		$("#mepa_rendicion_caja_chica_listar_pendiente_enviar_tesoreria_table").hide();
		$("#mepa_rendicion_caja_chica_listar_liquidacion_table").hide();
		$(".mepa_rendicion_caja_chica_div_param_busqueda").hide();
		$("#mepa_rendicion_caja_chica_buscar_pendientes_enviar_a_etesoreria").hide();

		alertify.error('Seleccione Tipo Reporte',5);
		$("#mepa_rendicion_caja_chica_param_tipo_reporte").focus();
		setTimeout(function() {
			$('#mepa_rendicion_caja_chica_param_tipo_reporte').select2('open');
		}, 500);

		return false;
	}
	else if(selectValor == 1)
	{
		$("#mepa_rendicion_caja_chica_buscar_pendientes_enviar_a_etesoreria").hide();
		$("#mepa_rendicion_caja_chica_listar_pendiente_enviar_tesoreria_table").hide();
		$(".mepa_rendicion_caja_chica_div_param_busqueda").show();
	}
	else if(selectValor == 2)
	{
		$(".mepa_rendicion_caja_chica_div_param_busqueda").hide();
		$("#mepa_rendicion_caja_chica_listar_liquidacion_table").hide();
		$("#mepa_solicitud_rendicion_caja_chica_tipo_reporte_solicitud_liquidacion_btn_export").hide();
		$("#mepa_rendicion_caja_chica_buscar_pendientes_enviar_a_etesoreria").show();
	}

	localStorage.setItem(local_storage_param_tipo_reporte, selectValor);

});

$('#mepa_rendicion_caja_chica_param_tipo_red').change(function () 
{
	var selectValor = $(this).val();

	localStorage.setItem(local_storage_param_tipo_red, selectValor);
});

$('#mepa_rendicion_caja_chica_param_zona').change(function () 
{
	var selectValor = $(this).val();

	localStorage.setItem(local_storage_param_zona, selectValor);

	mepa_solicitud_rendicion_caja_chica_obtener_usuarios_asignado(selectValor);
});

$('#mepa_rendicion_caja_chica_param_usuario').change(function () 
{
	var selectValor = $(this).val();

	localStorage.setItem(local_storage_param_usuario, selectValor);
});

$('#mepa_rendicion_caja_chica_param_situacion_contabilidad').change(function () 
{
	var selectValor = $(this).val();

	localStorage.setItem(local_storage_param_contabilidad, selectValor);
});


function mepa_solicitud_rendicion_caja_chica_obtener_usuarios_asignado(zona_id) 
{	
	var data = {
		"accion": "mepa_solicitud_rendicion_caja_chica_obtener_usuarios_asignado",
		"zona_id": zona_id
	}
	
	var array_provincias = [];
	
	$.ajax({
		url: "/sys/set_mepa_solicitud_rendicion_caja_chica.php",
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
				var html = '<option value="0">-- Todos --</option>';
				$("#mepa_rendicion_caja_chica_param_usuario").html(html).trigger("change");

				setTimeout(function() {
					$('#mepa_rendicion_caja_chica_param_usuario').select2('open');
				}, 500);

				return false;
			}
			
			if (parseInt(respuesta.http_code) == 200) 
			{
				array_provincias.push(respuesta.result);
			
				var html = '<option value="0">-- Todos --</option>';

				for (var i = 0; i < array_provincias[0].length; i++) 
				{
					html += '<option value=' + array_provincias[0][i].id  + '>' + array_provincias[0][i].nombre + '</option>';
				}

				$("#mepa_rendicion_caja_chica_param_usuario").html(html).trigger("change");

				setTimeout(function() {
					$('#mepa_rendicion_caja_chica_param_usuario').select2('open');
				}, 500);
				
				if(storage_param_tipo_reporte == 1)
				{
					$("#mepa_rendicion_caja_chica_param_usuario").val(storage_param_usuario).change();	
				}
				
				return false;
			}
		},
		error: function() {}
	});
}

function mepa_rendicion_caja_chica_listar_liquidacion_datatable()
{
	if(sec_id == "mepa" && sub_sec_id == "solicitud_rendicion_caja_chica")
	{
		param_zona = $("#mepa_rendicion_caja_chica_param_zona").val();
		param_usuario = $("#mepa_rendicion_caja_chica_param_usuario").val();
		param_situacion_contabilidad = $("#mepa_rendicion_caja_chica_param_situacion_contabilidad").val();
		param_tipo_red = $("#mepa_rendicion_caja_chica_param_tipo_red").val();

		var data = {
			"accion": "mepa_rendicion_caja_chica_listar_liquidacion_datatable",
			"param_zona" : param_zona,
			"param_usuario" : param_usuario,
			"param_situacion_contabilidad" : param_situacion_contabilidad,
			"param_tipo_red" : param_tipo_red
		}

		$("#mepa_rendicion_caja_chica_listar_pendiente_enviar_tesoreria_table").hide();
		$("#mepa_rendicion_caja_chica_listar_liquidacion_table").show();
		$("#mepa_solicitud_rendicion_caja_chica_tipo_reporte_solicitud_liquidacion_btn_export").show();
		
		tabla = $("#mepa_rendicion_caja_chica_listar_liquidacion_datatable").dataTable(
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
				url : "/sys/set_mepa_solicitud_rendicion_caja_chica.php",
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
				{"aaData" : "10"}
			],
			"bDestroy" : true,
			aLengthMenu:[10, 20, 30, 40, 50, 100],
			scrollX: true,
			scrollCollapse: true
		}).DataTable();

		mepa_rendicion_caja_chica_mostrar_switch();
	}

	setTimeout(function() {
		$('#mepa_rendicion_caja_chica_param_tipo_reporte').select2('open');
	}, 500);
}

$('#mepa_rendicion_caja_chica_listar_liquidacion_datatable').DataTable().on("draw", function()
{
	mepa_rendicion_caja_chica_mostrar_switch();
})

function mepa_rendicion_caja_chica_mostrar_switch()
{
	$(".mepa_switch").bootstrapToggle({
		on:"Ok",
		off:"Pendiente",
		onstyle:"success",
		offstyle:"danger",
		size:"mini"
	});

	$(".toggle").off().on('click', function(event) {
		if(typeof $(this).find('.mepa_switch').data().ignore === 'undefined')
			$(this).find('.mepa_switch').bootstrapToggle('toggle');
	});

	$(".mepa_switch")
	.off()
	.on("change",function(event) {
		switch_data($(event.target));
	});
}

function mepa_rendicion_caja_chica_buscar_listar_pendientes_enviar_a_tesoreria()
{

	if(sec_id == "mepa" && sub_sec_id == "solicitud_rendicion_caja_chica")
	{
		param_tipo_red = $("#mepa_rendicion_caja_chica_param_tipo_red").val();

		var data = {
			"accion": "mepa_rendicion_caja_chica_buscar_listar_pendientes_enviar_a_tesoreria",
			"param_tipo_red": param_tipo_red
		}

		$("#mepa_rendicion_caja_chica_listar_liquidacion_table").hide();
		$("#mepa_rendicion_caja_chica_listar_pendiente_enviar_tesoreria_table").show();
		
		tabla = $("#mepa_rendicion_caja_chica_listar_liquidacion_pendiente_enviar_tesoreria_datatable").dataTable(
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
					url : "/sys/set_mepa_solicitud_rendicion_caja_chica.php",
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
	setTimeout(function() {
		$('#mepa_rendicion_caja_chica_param_tipo_reporte').select2('open');
	}, 500);
}

function mepa_rendicion_caja_chica_liquidacion_enviar_tesoreria_todos()
{

	swal(
	{
		title: '¿Está seguro de enviar a Tesoreria?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{
			param_tipo_red = $("#mepa_rendicion_caja_chica_param_tipo_red").val();

			var data = {
				"accion": "sec_mepa_rendicion_caja_chica_liquidacion_enviar_tesoreria_todos",
				"param_tipo_red": param_tipo_red
			}

			$.ajax({
				url: "sys/set_mepa_solicitud_rendicion_caja_chica.php",
				type: 'POST',
				data: data,
				cache: false,
				//contentType: false,
				//processData: false,
				beforeSend: function( xhr ) {
					loading(true);
				},
				success: function(data){
					
					var respuesta = JSON.parse(data);
					
					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: "Registro exitoso",
							text: "Se envió exitosamente",
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        window.location.href = "?sec_id=mepa&sub_sec_id=solicitud_rendicion_caja_chica";
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=solicitud_rendicion_caja_chica";
						}, 5000);

						return true;
					}
					else if(parseInt(respuesta.http_code) == 201) 
					{
						swal({
							title: "No existen registros.",
							text: "No existen registros para enviar a Tesoreria",
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;

					} 
					else {
						swal({
							title: "Error al guardar Solicitud",
							text: respuesta.error,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
				},
				complete: function(){
					loading(false);
				}
			});
		}
	}
	);
}

function mepa_rendicion_caja_chica_enviado_a_tesoreria()
{
	$("#mepa_modal_enviados_a_tesoreria").modal("show");
}

function mepa_solicitud_rendicion_caja_chica_historial_enviado_a_tesoreria()
{
	
	tabla = $("#mepa_solicitud_rendicion_caja_chica_historial_enviado_a_tesoreria_table").dataTable(
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
		aLengthMenu:[10, 20, 30, 40, 50],
		"order": [[ 1, 'desc' ]]
	}).DataTable();
}

function mepa_rendicion_caja_chica_listar_detalle_historial_enviado_a_tesoreria(id_historial)
{
	if(sec_id == "mepa" && sub_sec_id == "solicitud_rendicion_caja_chica")
	{
		var data = {
			"accion": "mepa_rendicion_caja_chica_listar_detalle_historial_enviado_a_tesoreria",
			"id_historial" : id_historial
		}

		tabla = $("#mepa_rendicion_caja_chica_listar_detalle_historial_enviado_a_tesoreria_datatable").dataTable(
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
				url : "/sys/set_mepa_solicitud_rendicion_caja_chica.php",
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
					targets: [0, 2, 4, 5]
				}
			],
			"bDestroy" : true,
			aLengthMenu:[10, 20, 30, 40, 50, 100],
		}).DataTable();
	}
}

function mepa_rendicion_caja_chica_reporte_detalle_historial_enviado_a_tesoreria(id_historial)
{
	var data = {
		"accion": "mepa_rendicion_caja_chica_reporte_detalle_historial_enviado_a_tesoreria",
		"id_historial" : id_historial
	}

    $.ajax({
        url: "/sys/set_mepa_solicitud_rendicion_caja_chica.php",
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
}

function mepa_rendicion_caja_chica_reporte_enviado_a_tesoreria_todos()
{
	
	param_tipo_red = $("#mepa_rendicion_caja_chica_param_tipo_red").val();

	var data = {
		"accion": "mepa_rendicion_caja_chica_reporte_enviado_a_tesoreria_todos",
		"param_tipo_red" : param_tipo_red
	}

    $.ajax({
        url: "/sys/set_mepa_solicitud_rendicion_caja_chica.php",
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
}

$("#mepa_solicitud_rendicion_caja_chica_tipo_reporte_solicitud_liquidacion_btn_export").on('click', function () 
{
	param_tipo_red = $("#mepa_rendicion_caja_chica_param_tipo_red").val();
	param_zona = $("#mepa_rendicion_caja_chica_param_zona").val();
	param_usuario = $("#mepa_rendicion_caja_chica_param_usuario").val();
	param_situacion_contabilidad = $("#mepa_rendicion_caja_chica_param_situacion_contabilidad").val();

	var data = {
		"accion": "mepa_solicitud_rendicion_caja_chica_tipo_reporte_solicitud_liquidacion_btn_export",
		"param_tipo_red" : param_tipo_red,
		"param_zona" : param_zona,
		"param_usuario" : param_usuario,
		"param_situacion_contabilidad" : param_situacion_contabilidad
	}

	$.ajax({
        url: "/sys/set_mepa_solicitud_rendicion_caja_chica.php",
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

function exportarDetalleSolicitudLiquidacionExcel(liquidacionId) {

    var data = {
        "accion": "mepa_detalle_solicitud_liquidacion_btn_export",
        "liquidacion_id": liquidacionId
    };

    $.ajax({
        url: "/sys/set_mepa_solicitud_rendicion_caja_chica.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            let obj = JSON.parse(resp);
            window.open(obj.ruta_archivo);
            loading(false);
        },
        error: function (resp, status) {
        }
    });
}

function descargarComprobantesSolicitudLiquidacionExcel(liquidacionId) {

    var data = {
        "accion": "mepa_detalle_solicitud_liquidacion_btn_export_comprobantes",
        "liquidacion_id": liquidacionId
    };

    $.ajax({
        url: "/sys/set_mepa_solicitud_rendicion_caja_chica.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            let obj = JSON.parse(resp);
            window.open(obj.ruta_archivo);
            loading(false);
        },
        error: function (resp, status) {
        }
    });
}

