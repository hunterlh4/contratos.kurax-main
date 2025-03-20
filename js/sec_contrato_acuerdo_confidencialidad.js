
var tabla;

function sec_contrato_acuerdo_confidencialidad()
{
	$('.cont_proveedor_param_ruc').mask('00000000000');

	$(".select2").select2({ width: "100%" });

	// INICIO INICIALIZACION DE DATEPICKER
	$('.cont_proveedor_datepicker')
		.datepicker({
			dateFormat:'yy-mm-dd',
			changeMonth: true,
			changeYear: true
		});
	sec_contrato_solicitud_obtener_opciones("obtener_directores", $("[name='director_aprobacion_id_confi']"));

	$('.limpiar_input').click(function() {
		$('#' + $(this).attr("limpiar")).val('');
	});

	$('.limpiar_select2').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val("").trigger("change");
		cont_acuerdo_confidencialidad_buscar_por_parametros();
	});

	$('.change-contrato-acuerdo-confidencialidad-firmado').on('select2:select', function (e) {
		cont_acuerdo_confidencialidad_buscar_por_parametros();
	});

	$('#btn_limpiar_filtros_de_busqueda').click(function() {
		$('#cont_proveedor_param_empresa').select2().val('').trigger("change");
		$('#cont_proveedor_param_area_solicitante').select2().val('').trigger("change");
		$('#cont_proveedor_param_ruc').val('');
		$('#cont_proveedor_param_razon_social').val('');
		$('#cont_proveedor_param_moneda').select2().val('').trigger("change");
		$('#cont_proveedor_param_fecha_inicio_solicitud').val('');
		$('#cont_proveedor_param_fecha_fin_solicitud').val('');
		$('#cont_proveedor_param_fecha_inicio').val('');
		$('#cont_proveedor_param_fecha_fin').val('');
	});


	cont_acuerdo_confidencialidad_buscar_por_parametros();
}

function sec_contrato_detalle_solicitud_guardar_observaciones_acuerdo_confidencialidad()
{
	var contrato_id = $('#contrato_id_temporal').val();
	var observaciones = $('#contrato_observaciones_proveedor').val();
	var correos_adjuntos = "";

	if (observaciones == "") {
		alertify.error('Ingrese la observación',5);
		$('#contrato_observaciones_proveedor').focus();
		return false;
	}
	swal({
		title: '¿Está seguro de agregar y notificar la observación?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: true,
		closeOnCancel: true
	}, function (isConfirm) {
		if (isConfirm) {
			
			var data = {
				"accion": "guardar_observaciones_contrato",
				"contrato_id": contrato_id,
				"tipo_observacion" : 'acuerdo_confidencialidad',
				"observaciones": observaciones,
				"correos_adjuntos": correos_adjuntos
			}
		
			auditoria_send({ "proceso": "guardar_observaciones_contrato_proveedor", "data": data });
		
			$.ajax({
				url: "/sys/set_contrato_detalle_solicitud.php",
				type: 'POST',
				data: data,
				beforeSend: function() {
					loading("true");
				},
				complete: function() {
					loading();
				},
				success: function(resp) { //  alert(datat)
					var respuesta = JSON.parse(resp);
					auditoria_send({ "respuesta": "guardar_observaciones_contrato", "data": respuesta });
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 500) {
						swal({
							title: respuesta.mensaje,
							text: "",
							html:true,
							type: respuesta.status,
							timer: 3000,
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
					if (parseInt(respuesta.http_code) == 400) {
						// swal('Aviso', respuesta.status, 'warning');
						// listar_transacciones(gen_cliente_id);
						return false;
					}
					if (parseInt(respuesta.http_code) == 200) {
						// window.location.href = "http://localhost/?sec_id=contrato&sub_sec_id=locales";
						sec_contrato_detalle_solicitud_actualizar_div_observaciones();
						$('#contrato_observaciones_proveedor').val('');
						$('#contrato_observaciones_proveedor').focus();
						return false;
					}
				},
				error: function() {}
			});

		}
	});
	
}

function cont_acuerdo_confidencialidad_buscar_por_parametros()
{
	sec_contrato_acuerdo_confidencialidad_listar_contratos_Datatable();
}

function sec_contrato_acuerdo_confidencialidad_listar_contratos_Datatable()
{
	
	if(sec_id == "contrato" && sub_sec_id == "acuerdo_confidencialidad")
	{
		var cont_proveedor_param_empresa = $("#cont_proveedor_param_empresa").val();
		var cont_proveedor_param_area_solicitante = $("#cont_proveedor_param_area_solicitante").val();
		var cont_proveedor_param_ruc = $("#cont_proveedor_param_ruc").val();
		var cont_proveedor_param_razon_social = $("#cont_proveedor_param_razon_social").val();
		var cont_proveedor_param_moneda = $("#cont_proveedor_param_moneda").val();
		var fecha_inicio_solicitud = $("#cont_proveedor_param_fecha_inicio_solicitud").val();
		var fecha_fin_solicitud = $("#cont_proveedor_param_fecha_fin_solicitud").val();
		var cont_proveedor_param_fecha_inicio = $("#cont_proveedor_param_fecha_inicio").val();
		var cont_proveedor_param_fecha_fin = $("#cont_proveedor_param_fecha_fin").val();

		var aprobante	=	$('#director_aprobacion_id_confi').val();

		var search_fecha_inicio_aprobacion_firmado	= $('#search_fecha_inicio_aprobacion_firmado_confi').val();
		var search_fecha_fin_aprobacion_firmado	= $('#search_fecha_fin_aprobacion_firmado_confi').val();


		if (fecha_inicio_solicitud.length > 0 && fecha_fin_solicitud.length > 0) {
			var fecha_inicio_date = new Date(fecha_inicio_solicitud);
			var fecha_fin_date = new Date(fecha_fin_solicitud);
			if (fecha_inicio_date > fecha_fin_date) {
				alertify.error('La fecha de solicitud desde debe ser menor o igual a la fecha de solicitud hasta ',5);
				return false;
			}
		}

		if (cont_proveedor_param_fecha_inicio.length > 0 && cont_proveedor_param_fecha_fin.length > 0) {
			var fecha_inicio_date = new Date(cont_proveedor_param_fecha_inicio);
			var fecha_fin_date = new Date(cont_proveedor_param_fecha_fin);
			if (fecha_inicio_date > fecha_fin_date) {
				alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta',5);
				return false;
			}
		}

		var data = {
			"accion": "cont_listar_acuerdo_confidencialidad",
			"cont_proveedor_param_empresa" : cont_proveedor_param_empresa,
			"cont_proveedor_param_area_solicitante" : cont_proveedor_param_area_solicitante,
			"cont_proveedor_param_ruc" : cont_proveedor_param_ruc,
			"cont_proveedor_param_razon_social" : cont_proveedor_param_razon_social,
			"cont_proveedor_param_moneda" : cont_proveedor_param_moneda,
			"fecha_inicio_solicitud" : fecha_inicio_solicitud,
			"fecha_fin_solicitud" : fecha_fin_solicitud,
			"cont_proveedor_param_fecha_inicio" : cont_proveedor_param_fecha_inicio,
			"cont_proveedor_param_fecha_fin" : cont_proveedor_param_fecha_fin,
			"search_fecha_inicio_aprobacion_firmado" : search_fecha_inicio_aprobacion_firmado,
			"search_fecha_fin_aprobacion_firmado" : search_fecha_fin_aprobacion_firmado,
			"aprobante"	:	aprobante,

		}

		$("#div_proveedor_boton_export").show();		
		$("#cont_contrato_proveedor_div_tabla").show();

		$('#cont_interno_datatable tfoot th').each(function () {
			var title = $(this).text();
			$(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
		});
		
		tabla = $("#cont_locales_proveedor_datatable").dataTable(
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
					url : "/sys/set_contrato_acuerdo_confidencialidad.php",
					data : data,
					type : "POST",
					dataType : "json",
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100],
				initComplete: function () {
					this.api()
					.columns()
					.every(function () {
						var that = this;

						$('input', this.footer()).on('keyup change clear', function () {
							if (that.search() !== this.value) {
								that.search(this.value).draw();
							}
						});
					});
				},
			}
		).DataTable();

	}
}

$("#cont_acuerdo_btn_export_confidencialidad").on('click', function () 
{
	var cont_proveedor_param_empresa = $("#cont_proveedor_param_empresa").val();

	var cont_proveedor_param_area_solicitante = $("#cont_proveedor_param_area_solicitante").val();

	var cont_proveedor_param_ruc = $("#cont_proveedor_param_ruc").val();

	var cont_proveedor_param_razon_social = $("#cont_proveedor_param_razon_social").val();

	var fecha_inicio_solicitud = $("#cont_proveedor_param_fecha_inicio_solicitud").val();
	var fecha_fin_solicitud = $("#cont_proveedor_param_fecha_fin_solicitud").val();
	var cont_proveedor_param_fecha_inicio = $("#cont_proveedor_param_fecha_inicio").val();
	var cont_proveedor_param_fecha_fin = $("#cont_proveedor_param_fecha_fin").val();

	var aprobante	=	$('#director_aprobacion_id_confi').val();

	var search_fecha_inicio_aprobacion_firmado	= $('#search_fecha_inicio_aprobacion_firmado_confi').val();
	var search_fecha_fin_aprobacion_firmado	= $('#search_fecha_fin_aprobacion_firmado_confi').val();

	if (fecha_inicio_solicitud.length > 0 && fecha_fin_solicitud.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio_solicitud);
		var fecha_fin_date = new Date(fecha_fin_solicitud);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de solicitud desde debe ser menor o igual a la fecha de solicitud hasta ',5);
			return false;
		}
	}

	if (cont_proveedor_param_fecha_inicio.length > 0 && cont_proveedor_param_fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(cont_proveedor_param_fecha_inicio);
		var fecha_fin_date = new Date(cont_proveedor_param_fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta',5);
			return false;
		}
	}

	var data = {
		"accion": "cont_acuerdo_reporte_confidencialidad_excel",
		"cont_proveedor_param_empresa" : cont_proveedor_param_empresa,
		"cont_proveedor_param_area_solicitante" : cont_proveedor_param_area_solicitante,
		"cont_proveedor_param_ruc" : cont_proveedor_param_ruc,
		"cont_proveedor_param_razon_social" : cont_proveedor_param_razon_social,
		"fecha_inicio_solicitud" : fecha_inicio_solicitud,
		"fecha_fin_solicitud" : fecha_fin_solicitud,
		"cont_proveedor_param_fecha_inicio" : cont_proveedor_param_fecha_inicio,
		"cont_proveedor_param_fecha_fin" : cont_proveedor_param_fecha_fin,

		"search_fecha_inicio_aprobacion_firmado" : search_fecha_inicio_aprobacion_firmado,
		"search_fecha_fin_aprobacion_firmado" : search_fecha_fin_aprobacion_firmado,
		"aprobante"	:	aprobante,

	}

	$.ajax({
		url: "/sys/set_contrato_acuerdo_confidencialidad.php",
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

function sec_contrato_acuerdo_validar_solo_numeros(e) {
	var val = document.all;
	var key = val ? e.keyCode : e.which;
	if (key > 31 && (key < 48 || key > 57)) {
		if (val) {
			window.event.keyCode = 0;
		} else {
			e.stopPropagation();
			e.preventDefault();
		}
	}
}

function sec_contrato_acuerdo_alerta(contrato_id) {
	$("#contrato_id_temp").val(contrato_id);
	$("#contenido_modal_alerta").html('');
	$("#modal_alertas").modal("show");
	var data = {
		accion: "obtener_info_alerta",
		contrato_id: contrato_id,
	};
	loading(true);
	$.ajax({
		url: "/sys/set_contrato_acuerdo_confidencialidad.php",
		type: "POST",
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 200) {
				var datos_alerta = respuesta.result[0];
				$("#contrato_id_temp").val(contrato_id);
				var num_dias_alerta = $.trim(datos_alerta.num_dias_para_alertar_vencimiento);
				var txt_num_dias_alerta = "";
				if (num_dias_alerta == 0 || num_dias_alerta == "") {
					txt_num_dias_alerta = "No existe numero de dias para la alerta";
				} else {
					txt_num_dias_alerta = num_dias_alerta + " dias antes de la fecha de vencimiento";
				}
				var body = '';
				body += '<tr>';
				body += '<td>' + datos_alerta.razon_social + '</td>';
				body += '<td class="text-center">' + datos_alerta.fecha_inicio + '</td>';
				body += '<td class="text-center">' + datos_alerta.fecha_vencimiento_proveedor + '</td>';
				body += '<td class="text-center">' + txt_num_dias_alerta + '</td>';
				body += '</tr>';
				loading();
				$("#contenido_modal_alerta").html(body);
				$("#num_dias").val('');
				setTimeout(function() {
					$("#num_dias").focus();
				}, 500);
				return true;
			}
		},
		error: function(textStatus) {
			console.log("La solicitud obtener datos para alerta a fallado: " + textStatus);
		},
	});
}

function sec_contrato_acuerdo_registrar_alerta() {
	var contrato_id = $("#contrato_id_temp").val();
	var num_dias = $("#num_dias").val().trim();
	if (parseInt(num_dias) === 0 || num_dias === "") {
		mensajeAlerta("Advertencia:", "Tiene que ingresar la cantidad de días para la alerta a este contrato.", claseTipoAlertas.alertaWarning, $("#divMensajeAlerta"));
		return;
	}
	var data = {
		accion: "actualizar_alerta_contrato",
		contrato_id: contrato_id,
		num_dias: num_dias,
	};
	loading(true);
	$.ajax({
		url: "/sys/set_contrato_acuerdo_confidencialidad.php",
		type: "POST",
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 200) {
				$("#modal_alertas").modal("hide");
				swal({
					title: "Registro exitoso",
					text: "La alerta se guardo correctamente",
					html: true,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false
				});

				$("#cont_acuerdo_confidencialidad_btn_buscar").click();
			} else {
				swal('Aviso', respuesta.error, 'warning');
				return;
			}
			loading();
			return true;
		},
		error: function(textStatus) {
			console.log("La solicitud para actualizar alerta contrato a fallado: " + textStatus);
		},
	});
}