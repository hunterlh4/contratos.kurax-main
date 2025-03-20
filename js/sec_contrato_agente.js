var tabla;

function sec_contrato_agente() {
	$(".cont_proveedor_param_ruc").mask("00000000000");
	sec_contrato_agente_cargar_fechas();
	cont_agente_buscar_por_parametros();
	sec_contrato_agente_obtener_departamentos();

	$(".select2").select2({
		width: "100%"
	});

	$('.limpiar_input').click(function() {
		$('#' + $(this).attr("limpiar")).val('');
	});

	$('.limpiar_select2').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val("").trigger("change");
		cont_agente_buscar_por_parametros();
	});

	$('.change-contrato-agentes-firmado').on('select2:select', function (e) {
		cont_agente_buscar_por_parametros();
	});


	$('#btn_limpiar_filtros_de_busqueda').click(function() {
		$('#empresa_id').select2().val('').trigger("change");
		$('#nombre_agente').val('');
		$('#search_centro_costos_agente').val('');
		$('#search_id_departamento').select2().val('').trigger("change");
		$('#search_id_provincia').select2().val('').trigger("change");
		$('#search_id_distrito').select2().val('').trigger("change");
		$('#fecha_inicio_solicitud').val('');
		$('#fecha_fin_solicitud').val('');
		$('#fecha_inicio_inicio').val('');
		$('#fecha_fin_inicio').val('');
	});

	$("#cont_agente_btn_export_agente").on("click", function() {
		var empresa_id = $("#empresa_id").val();
		var nombre_agente = $("#nombre_agente").val();
		var cont_agente_cc_costos = $("#search_centro_costos_agente").val();
		var cont_agente_param_fecha_inicio = $("#cont_agente_param_fecha_inicio").val();
		var cont_agente_param_fecha_fin = $("#cont_agente_param_fecha_fin").val();
		var id_departamento = $("#search_id_departamento").val();
		var id_provincia = $("#search_id_provincia").val();
		var id_distrito = $("#search_id_distrito").val();
		var fecha_inicio_solicitud = $("#fecha_inicio_solicitud").val();
		var fecha_fin_solicitud = $("#fecha_fin_solicitud").val();
		var fecha_inicio_inicio = $("#fecha_inicio_inicio").val();
		var fecha_fin_inicio = $("#fecha_fin_inicio").val();

		var data = {
			accion: "cont_agente_reporte_excel",
			empresa_id: empresa_id,
			nombre_agente: nombre_agente,
			cont_agente_cc_costos: cont_agente_cc_costos,
			fecha_inicio_solicitud: fecha_inicio_solicitud,
			fecha_fin_solicitud: fecha_fin_solicitud,
			fecha_inicio_inicio: fecha_inicio_inicio,
			fecha_fin_inicio: fecha_fin_inicio,
			id_departamento: id_departamento,
			id_provincia: id_provincia,
			id_distrito: id_distrito,
		};
		$.ajax({
			url: "/sys/set_contrato_agente.php",
			type: "POST",
			data: data,
			beforeSend: function() {
				loading("true");
			},
			complete: function() {
				loading();
			},
			success: function(resp) {
				let obj = JSON.parse(resp);

				


				if (parseInt(obj.estado_archivo) == 1) {
					window.open(obj.ruta_archivo);
					loading(false);
				} else if (parseInt(obj.estado_archivo) == 0) {
					swal({
						title: "Error al generar el archivo excel",
						text: obj.ruta_archivo,
						html: true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
					});
					return false;
				} else if (parseInt(obj.estado_archivo) == 2) {
					swal({
						title: "No hay data para generar el archivo excel",
					
						html: true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
					});
					return false;
				}else {
					swal({
						title: "Error",
						text: "Ponerse en contacto con Soporte",
						html: true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
					});
					return false;
				}
			},
			error: function(resp, status) {},
		});
	});
}

function sec_contrato_agente_cargar_fechas() {
	$(".cont_agente_datepicker").datepicker({
		dateFormat: "yy-mm-dd",
		changeMonth: true,
		changeYear: true,
	});
}

function sec_contrato_agente_obtener_departamentos() {
	let select = $("[name='search_id_departamento']");
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: {
			accion: "obtener_departamentos"
		},
		beforeSend: function() {},
		complete: function() {},
		success: function(datos) {
			var respuesta = JSON.parse(datos);
			$(select).find("option").remove().end();
			$(select).append('<option value="">- TODOS -</option>');
			$(respuesta.result).each(function(i, e) {
				opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);
			});
		},
		error: function() {},
	});
}

function sec_contrato_agente_obtener_provincias() {
	$("#search_id_departamento option:selected").each(function() {
		let search_id_departamento = $("#search_id_departamento").val();
		if (search_id_departamento == "") {
			return false;
		}
		var data = {
			accion: "obtener_provincias_segun_departamento",
			departamento_id: search_id_departamento,
		};
		var array_provincias = [];
		$.ajax({
			url: "/sys/set_contrato_nuevo.php",
			type: "POST",
			data: data,
			beforeSend: function() {
				loading("true");
			},
			complete: function() {
				loading();
			},
			success: function(resp) {
				var respuesta = JSON.parse(resp);
				if (parseInt(respuesta.http_code) == 400) {}
				if (parseInt(respuesta.http_code) == 200) {
					array_provincias.push(respuesta.result);
					var html = '<option value="">- TODOS -</option>';
					for (var i = 0; i < array_provincias[0].length; i++) {
						html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
					}
					$("#search_id_provincia").html(html).trigger("change");
					setTimeout(function() {
						$("#search_id_provincia").select2("open");
					}, 500);
					return false;
				}
			},
			error: function() {},
		});
	});
}

function sec_contrato_agente_obtener_distritos() {
	$("#search_id_provincia option:selected").each(function() {
		let search_id_departamento = $("#search_id_departamento").val();
		let search_id_provincia = $("#search_id_provincia").val();
		if (search_id_provincia == "") {
			return false;
		}
		var data = {
			accion: "obtener_distritos_segun_provincia",
			provincia_id: search_id_provincia,
			departamento_id: search_id_departamento,
		};
		var array_distritos = [];
		$.ajax({
			url: "/sys/set_contrato_nuevo.php",
			type: "POST",
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
				if (parseInt(respuesta.http_code) == 400) {}
				if (parseInt(respuesta.http_code) == 200) {
					array_distritos.push(respuesta.result);
					var html = '<option value="">- TODOS -</option>';
					for (var i = 0; i < array_distritos[0].length; i++) {
						html += "<option value=" + array_distritos[0][i].id + ">" + array_distritos[0][i].nombre + "</option>";
					}
					$("#search_id_distrito").html(html).trigger("change");
					setTimeout(function() {
						$("#search_id_distrito").select2("open");
					}, 500);
					return false;
				}
			},
			error: function() {},
		});
	});
}

function cont_agente_buscar_por_parametros() {
	sec_contrato_agente_listar_contratos_Datatable();
}

function sec_contrato_agente_listar_contratos_Datatable() {
	if (sec_id == "contrato" && sub_sec_id == "agente") {
		var empresa_id = $("#empresa_id").val();
		var nombre_agente = $("#nombre_agente").val();
		var cont_agente_param_cc_costos = $("#search_centro_costos_agente").val();
		var cont_agente_param_fecha_inicio = $("#cont_agente_param_fecha_inicio").val();
		var cont_agente_param_fecha_fin = $("#cont_agente_param_fecha_fin").val();
		var id_departamento = $("#search_id_departamento").val();
		var id_provincia = $("#search_id_provincia").val();
		var id_distrito = $("#search_id_distrito").val();
		var fecha_inicio_solicitud = $("#fecha_inicio_solicitud").val();
		var fecha_fin_solicitud = $("#fecha_fin_solicitud").val();
		var fecha_inicio_inicio = $("#fecha_inicio_inicio").val();
		var fecha_fin_inicio = $("#fecha_fin_inicio").val();

		if (fecha_inicio_solicitud.length > 0 && fecha_fin_solicitud.length > 0) {
			var fecha_inicio_date = new Date(fecha_inicio_solicitud);
			var fecha_fin_date = new Date(fecha_fin_solicitud);
			if (fecha_inicio_date > fecha_fin_date) {
				alertify.error('La fecha de solicitud desde debe ser menor o igual a la fecha de solicitud hasta ',5);
				return false;
			}
		}

		if (fecha_inicio_inicio.length > 0 && fecha_fin_inicio.length > 0) {
			var fecha_inicio_date = new Date(fecha_inicio_inicio);
			var fecha_fin_date = new Date(fecha_fin_inicio);
			if (fecha_inicio_date > fecha_fin_date) {
				alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta',5);
				return false;
			}
		}

		var data = {
			accion: "cont_listar_agentes",
			empresa_id: empresa_id,
			nombre_agente: nombre_agente,
			cont_agente_param_cc_costos: cont_agente_param_cc_costos,
			fecha_inicio_solicitud: fecha_inicio_solicitud,
			fecha_fin_solicitud: fecha_fin_solicitud,
			fecha_inicio_inicio: fecha_inicio_inicio,
			fecha_fin_inicio: fecha_fin_inicio,
			id_departamento: id_departamento,
			id_provincia: id_provincia,
			id_distrito: id_distrito,
		};
		$("#div_proveedor_boton_export").show();
		$("#cont_contrato_agente_div_tabla").show();

		$('#cont_agente_datatable tfoot th').each(function () {
			var title = $(this).text();
			$(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
		});

		tabla = $("#cont_agente_datatable").dataTable({
			language: {
				decimal: "",
				emptyTable: "No existen registros",
				info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
				infoEmpty: "Mostrando 0 a 0 de 0 entradas",
				infoFiltered: "(filtered from _MAX_ total entradas)",
				infoPostFix: "",
				thousands: ",",
				lengthMenu: "Mostrar _MENU_ entradas",
				loadingRecords: "Cargando...",
				processing: "Procesando...",
				search: "Filtrar:",
				zeroRecords: "Sin resultados",
				paginate: {
					first: "Primero",
					last: "Ultimo",
					next: "Siguiente",
					previous: "Anterior",
				},
				aria: {
					sortAscending: ": activate to sort column ascending",
					sortDescending: ": activate to sort column descending",
				},
			},
			aProcessing: true,
			aServerSide: true,
			rowCallback: function(row, data) {
				if (data[12] == "2") { //estado
					$(row).css('background-color', 'red');
					$(row).css('color', 'white');
				}
				// Puedes agregar más condiciones aquí para otros valores de status
			},
			ajax: {
				url: "/sys/set_contrato_agente.php",
				data: data,
				type: "POST",
				dataType: "json",
				error: function(e) {
					console.log(e.responseText);
				},
			},
			createdRow: function(row, data, dataIndex) {
				if (data[0] === 'error') {
					$('td:eq(0)', row).attr('colspan', 7);
					$('td:eq(0)', row).attr('align', 'center');
					$('td:eq(1)', row).css('display', 'none');
					$('td:eq(2)', row).css('display', 'none');
					$('td:eq(3)', row).css('display', 'none');
					$('td:eq(4)', row).css('display', 'none');
					$('td:eq(5)', row).css('display', 'none');
					$('td:eq(6)', row).css('display', 'none');
					this.api().cell($('td:eq(0)', row)).data(data[1]);
				}
			},
			bDestroy: true,
			aLengthMenu: [10, 20, 30, 40, 50, 100],
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
		}).DataTable();
	}
}
// INICIO FUNCIONES DETALLE SOLICITUD
function sec_contrato_detalle_solicitud_agente_verificar_documentos() {
	var contrato_id = $('#contrato_id_temporal').val();
	var nombre_tienda = $('#contrato_nombre_tienda').val().trim();
	var archivo_contrato = document.getElementById("archivo_contrato");
	var fecha_suscripcion = $('#cont_detalle_contrato_firmado_fecha_suscripcion_param').val();
	var fecha_fin = $('#cont_detalle_contrato_firmado_fecha_vencimiento_param').val();
	var fecha_inicio = $('#cont_detalle_contrato_firmado_fecha_incio_param').val();
	var plazo_id = $('#plazo_id_arr').val();
	var periodo_tipo = $('#cont_detalle_contrato_firmado_periodo').val();
	var periodo_numero = $('#cont_detalle_contrato_firmado_periodo_numero').val();
	var renovacion_automatica = $('#cont_detalle_renovacion_automatica').val();

	if (nombre_tienda.length == 0) {
		alertify.error('Ingrese el nombre del agente', 5);
		$("#contrato_nombre_tienda").focus();
		return false;
	}
	if(nombre_tienda.length>100){
		alertify.error('Cantidad de caracteres del nombre de Agente es mayor al permitido (100 caracteres)', 10);
		$("#contrato_nombre_tienda").focus();
		return false;
	}
	if (fecha_inicio.length == 0) {
		alertify.error('Ingrese una fecha inicio', 5);
		$("#cont_detalle_contrato_firmado_fecha_incio_param").focus();
		return false;
	}
	
	if (plazo_id.length == 0) {
		alertify.error('Selecciona una vigencia ',5);
		$("#plazo_id_arr").select2("open");
		return false;
	}

	if (plazo_id == 1) {
		if (fecha_fin.length == 0) {
			alertify.error('Ingrese una fecha fin',5);
			$("#cont_detalle_contrato_firmado_fecha_vencimiento_param").focus();
			return false;
		}else{
			if(periodo_tipo==1){ ///años
				// Definir las fechas que se van a comparar
				const fecha1 = moment(fecha_inicio,'DD-MM-YYYY'); // fecha base
				const fecha2 = moment(fecha_fin,'DD-MM-YYYY'); // fecha a comparar
				const nuevaFecha = fecha1.clone().add(periodo_numero, 'years');
				const dif_dias = nuevaFecha.diff(fecha2, 'days');
				if (dif_dias > 0) {
					if (dif_dias > 4 ) {
						alertify.error('La fecha fin tiene una diferencia de '+ dif_dias+ ' días y no coincide con el periodo de ' + periodo_numero+' años', 10);
						return false;
					}
				}
				if (dif_dias < 0) {
					if (dif_dias < -4 ) {
						alertify.error('La fecha fin tiene una diferencia de '+ dif_dias+ ' días y no coincide con el periodo de ' + periodo_numero +' años', 10);
						return false;
					}
				}
			}
			if(periodo_tipo==2){  // meses 
				// Definir las fechas que se van a comparar
				const fecha1 = moment(fecha_inicio,'DD-MM-YYYY'); // fecha base
				const fecha2 = moment(fecha_fin,'DD-MM-YYYY'); // fecha a comparar
				const nuevaFecha = fecha1.clone().add(periodo_numero, 'month');
				const dif_dias = nuevaFecha.diff(fecha2, 'days');
				if (dif_dias > 0) {
					if (dif_dias > 4 ) {
						alertify.error('La fecha fin tiene una diferencia de '+ dif_dias+ ' días y no coincide con el periodo de ' + periodo_numero+' meses', 10);
						return false;
					}
				}
				if (dif_dias < 0) {
					if (dif_dias < -4 ) {
						alertify.error('La fecha fin tiene una diferencia de '+ dif_dias+ ' días y no coincide con el periodo de ' + periodo_numero+' mesess', 10);
						return false;
					}
				}
			}
		}
	}else{
		fecha_fin = '';
	}
	if (fecha_suscripcion.length == 0) {
		alertify.error('Ingrese una fecha suscripción', 5);
		$("#cont_detalle_contrato_firmado_fecha_suscripcion_param").focus();
		return false;
	}
	if (archivo_contrato.files.length == 0) {
		alertify.error('Ingrese el contrato firmado', 5);
		$("#archivo_partida_registral").focus();
		return false;
	}
	if (parseInt(renovacion_automatica) == 0) {
		alertify.error('Completar el campo de Renovación Automática', 5);
		$("#cont_detalle_renovacion_automatica").focus();
		$('#cont_detalle_renovacion_automatica').select2('open');
		return false;
	}
	var data = {
		"accion": "obtener_documentos_incompletos",
		"contrato_id": contrato_id
	}
	auditoria_send({
		"proceso": "obtener_documentos_incompletos",
		"data": data
	});
	$.ajax({
		url: "sys/set_contrato_agente.php",
		type: 'POST',
		data: data,
		beforeSend: function(xhr) {
			loading(true);
		},
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({
				"proceso": "obtener_documentos_incompletos",
				"data": respuesta
			});
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.consulta_error, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				if (respuesta.result == '') {
					sec_contrato_detalle_solicitud_agente_guardar_contrato_firmado();
				} else {
					$('#modal_documentos_pendientes_por_subir').modal({
						backdrop: 'static',
						keyboard: false
					});
					$('#div_documentos_pendientes_por_subir').html(respuesta.result);
				}
			}
		},
		complete: function() {
			loading(false);
		}
	});
}

function sec_contrato_detalle_solicitud_agente_guardar_contrato_firmado() {
	$('#modal_documentos_pendientes_por_subir').modal('hide');
	var contrato_id = $('#contrato_id_temporal').val();
	var nombre_tienda = $('#contrato_nombre_tienda').val().trim();
	var archivo_contrato = document.getElementById("archivo_contrato");
	var fecha_suscripcion = $('#cont_detalle_contrato_firmado_fecha_suscripcion_param').val();
	var fecha_fin = $('#cont_detalle_contrato_firmado_fecha_vencimiento_param').val();
	var fecha_inicio = $('#cont_detalle_contrato_firmado_fecha_incio_param').val();
	var renovacion_automatica = $('#cont_detalle_renovacion_automatica').val();
	var plazo_id = $('#plazo_id_arr').val();

	if (nombre_tienda.length == 0) {
		alertify.error('Ingrese el nombre del agente', 5);
		$("#contrato_nombre_tienda").focus();
		return false;
	}
	if (fecha_inicio.length == 0) {
		alertify.error('Ingrese una fecha inicio', 5);
		$("#cont_detalle_contrato_firmado_fecha_incio_param").focus();
		return false;
	}
	if (plazo_id.length == 0) {
		alertify.error('Selecciona una vigencia ',5);
		$("#plazo_id_arr").select2("open");
		return false;
	}

	if (plazo_id == 1) {
		if (fecha_fin.length == 0) {
			alertify.error('Ingrese una fecha fin',5);
			$("#cont_detalle_contrato_firmado_fecha_vencimiento_param").focus();
			return false;
		}
	}else{
		fecha_fin = '';
	}
	if (archivo_contrato.files.length == 0) {
		alertify.error('Ingrese el contrato firmado', 5);
		$("#archivo_partida_registral").focus();
		return false;
	}
	if (parseInt(renovacion_automatica) == 0) {
		alertify.error('Completar el campo de Renovación Automática', 5);
		$("#cont_detalle_renovacion_automatica").focus();
		$('#cont_detalle_renovacion_automatica').select2('open');
		return false;
	}

	var dataForm = new FormData($("#form_contrato_firmado")[0]);
	dataForm.append("accion", "guardar_contrato_firmado");
	dataForm.append("contrato_id", contrato_id);
	dataForm.append("nombre_tienda", nombre_tienda);
	dataForm.append("fecha_inicio", fecha_inicio);
	dataForm.append("plazo_id", plazo_id);
	dataForm.append("fecha_fin", fecha_fin);
	dataForm.append("fecha_suscripcion", fecha_suscripcion);
	dataForm.append("renovacion_automatica", renovacion_automatica);
	auditoria_send({
		"proceso": "guardar_contrato_firmado",
		"data": dataForm
	});
	$.ajax({
		url: "sys/set_contrato_agente.php",
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function(xhr) {
			loading(true);
		},
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({
				"proceso": "guardar_contrato_firmado",
				"data": respuesta
			});
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 200) {
				swal({
					title: "Registro exitoso",
					text: "El contrato firmado se guardo correctamente",
					html: true,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false
				});
				setTimeout(function() {
					window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
					return false;
				}, 3000);
			} else {
				if (parseInt(respuesta.http_code) == 400) {
					if (respuesta.error == "sin_asignar") {
						if (respuesta.campo_incompleto == 'nombre_de_la_tienda') {
							var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el Nombre del agente.';
							var msg_error_text = '¿Desea ingresar el nombre del agente?';
							var msg_error_confirmButtonText = 'SI, AGREGAR EL NOMBRE DE LA TIENDA';
						} else if (respuesta.campo_incompleto == 'centro_de_costos') {
							var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el Centro de Costos.';
							var msg_error_text = 'El área Contable es responsable de ingresar el Centro de Costos';
							var msg_error_confirmButtonText = 'ENTENDIDO';
						} else if (respuesta.campo_incompleto == 'supervisor') {
							var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el supervisor de la tienda.';
							var msg_error_text = '¿Desea ingresar el supervisor?';
							var msg_error_confirmButtonText = 'SI, AGREGAR EL SUPERVISOR';
						} else if (respuesta.campo_incompleto == 'jefe_comercial') {
							var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el jefe comercial.';
							var msg_error_text = '¿Desea ingresar el jefe comercial?';
							var msg_error_confirmButtonText = 'SI, AGREGAR EL JEFE COMERCIAL';
						// } else if (respuesta.campo_incompleto == 'abogado') {
						// 		var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el abogado.';
						// 		var msg_error_text = '¿Desea ingresar el abogado?';
						// 		var msg_error_confirmButtonText = 'SI, AGREGAR EL ABOGADO';
							}
						swal({
							title: msg_error_titulo,
							text: msg_error_text,
							html: true,
							type: "warning",
							showCancelButton: true,
							confirmButtonColor: '#1cb787',
							cancelButtonColor: '#d56d6d',
							confirmButtonText: msg_error_confirmButtonText,
							cancelButtonText: 'CANCELAR'
						}, function(isConfirm) {
							if (isConfirm) {
								if (respuesta.campo_incompleto == 'nombre_de_la_tienda') {
									$('#btn_editar_nombre_de_la_tienda').click();
								} else if (respuesta.campo_incompleto == 'supervisor') {
									$('#btn_editar_supervisor').click();
								} else if (respuesta.campo_incompleto == 'jefe_comercial') {
									$('#btn_editar_jefe_comercial').click();
								} else if (respuesta.campo_incompleto == 'abogado') {
									$('#btn_editar_jefe_abogado').click();
								}
							}
						});
					} else {
						swal({
							title: "Error al guardar el contrato firmado",
							text: respuesta.error,
							html: true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
					}
				}
			}
		},
		complete: function() {
			loading(false);
		}
	});
}

function sec_contrato_detalle_solicitud_agente_guardar_adenda_firmada(adenda_id) {
	var contrato_id = $('#contrato_id_temporal').val();
	var adenda_id = $('#adenda_id_' + adenda_id).val().trim();
	var adenda_firmada = document.getElementById("adenda_firmada_" + adenda_id);
	if (adenda_firmada.files.length == 0) {
		alertify.error('Ingrese la adenda firmada', 5);
		$("#adenda_firmada").focus();
		return false;
	}
	var dataForm = new FormData($("#form_adenda_firmada_" + adenda_id)[0]);
	dataForm.append("accion", "guardar_adenda_contrato_agente_firmada");
	dataForm.append("contrato_id", contrato_id);
	dataForm.append("adenda_id", adenda_id);
	auditoria_send({
		"proceso": "guardar_adenda_contrato_agente_firmada",
		"data": dataForm
	});
	$.ajax({
		url: "sys/set_contrato_agente.php",
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function(xhr) {
			loading(true);
		},
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({
				"proceso": "guardar_adenda_contrato_agente_firmada",
				"data": respuesta
			});
			console.log(respuesta);
			// if (parseInt(respuesta.http_code) == 400) {
			// 		if (respuesta.campo_incompleto == "abogado") {
			// 			var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el abogado.';
			// 			var msg_error_text = '¿Desea ingresar el abogado?';
			// 			var msg_error_confirmButtonText = 'SI, AGREGAR EL ABOGADO';

			// 			swal({
			// 				title: msg_error_titulo,
			// 				text: msg_error_text,
			// 				html: true,
			// 				type: "warning",
			// 				showCancelButton: true,
			// 				confirmButtonColor: '#1cb787',
			// 				cancelButtonColor: '#d56d6d',
			// 				confirmButtonText: msg_error_confirmButtonText,
			// 				cancelButtonText: 'CANCELAR'
			// 			}, function (isConfirm) {
			// 				if(isConfirm){
			// 					if (respuesta.campo_incompleto == 'abogado') {
			// 						$('#btn_editar_adenda_abogado_'+adenda_id).click();
			// 					}
			// 				}
			// 			});
			// 		}else{
			// 			swal('Aviso', respuesta.status, 'warning');
			// 			return false;
			// 		}
			// }
			if (parseInt(respuesta.http_code) == 200) {
				window.location.href = window.location.href;
				return false;
			}
		},
		complete: function() {
			loading(false);
		}
	});
}

function sec_contrato_agente_validar_solo_numeros(e) {
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

function sec_contrato_agente_alerta(contrato_id) {
	$("#contrato_id_temp").val(contrato_id);
	$("#contenido_modal_alerta").html('');
	$("#modal_alertas").modal("show");
	var data = {
		accion: "obtener_info_alerta",
		contrato_id: contrato_id,
	};
	loading(true);
	$.ajax({
		url: "/sys/get_contrato_agente.php",
		type: "POST",
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 200) {
				var datos_alerta = respuesta.result[0];
				$("#contrato_id_temp").val(contrato_id);
				var num_dias_alerta = $.trim(datos_alerta.num_dias_para_alertar_vencimiento);
				var txt_num_dias_alerta = "";
				if ( num_dias_alerta == 0 || num_dias_alerta == "") {
					txt_num_dias_alerta = "No existe numero de dias para la alerta";
				} else {
					txt_num_dias_alerta = num_dias_alerta + " dias antes de la fecha de vencimiento";
				}
				var body = '';
				body += '<tr>';
				body += '<td>' + datos_alerta.nombre_agente + '</td>';
				body += '<td class="text-center">' + datos_alerta.fecha_inicio_agente + '</td>';
				body += '<td class="text-center">' + datos_alerta.fecha_fin_agente + '</td>';
				body += '<td class="text-center">' + txt_num_dias_alerta + '</td>';
				body += '</tr>';
				loading();
				$("#contenido_modal_alerta").html(body);
				$("#num_dias").val('');
				$("#num_dias").focus();
				return true;
			}
		},
		error: function(textStatus) {
			console.log("La solicitud obtener datos para alerta a fallado: " + textStatus);
		},
	});
}

function sec_contrato_agente_registrar_alerta() {
	var contrato_id = $("#contrato_id_temp").val();
	var num_dias = $("#num_dias").val().trim();
	if ( parseInt(num_dias) === 0 || num_dias === "") {
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
		url: "/sys/set_contrato_agente.php",
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

function reenviar_solicitud_contrato_agente(contrato_id) {

	var data = {
		contrato_id : contrato_id,
		accion: 'reenviar_email_solicitud_agente',
	};
  swal({
    html:true,
    title: 'Reenviar Email',
    text: "¿Desea reenviar el email?",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#1cb787',
    cancelButtonColor: '#d56d6d',
    confirmButtonText: 'SÍ, REENVIAR EMAIL',
    cancelButtonText: 'CANCELAR',
    closeOnConfirm: false,
    //,showLoaderOnConfirm: true
  }, function(){
    

	loading(true);
	$.ajax({
		url: "/sys/set_contrato_nuevo_agente.php",
		type: "POST",
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.status) == 200) {
				swal({
				  title: "Reenvío exitoso",
				  text: respuesta.message,
				  html:true,
				  type: "success",
				  timer: 6000,
				  closeOnConfirm: false,
				  showCancelButton: false
				});
				loading();
				return false;
			} else {
				swal({
					title: "Error al enviar Solicitud de Arrendamiento",
					text: response.message,
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
				loading();
				return false;
			}
		},
		error: function(textStatus) {
			console.log("La solicitud para actualizar alerta contrato a fallado: " + textStatus);
		},
	});

  });
}