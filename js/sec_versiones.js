function sec_versiones() {
	if (sec_id == "versiones") {
		sec_versiones_obtener_menus_con_versiones();
		sec_versiones_obtener_creadores_de_versiones();
		sec_versiones_listar_versiones_por_menu();
		sec_versiones_listar_historial_de_versiones();
		sec_versiones_obtener_menus();
		$('.limpiar_input').click(function () {
			$('#' + $(this).attr("limpiar")).val('');
			$('#' + $(this).attr("limpiar")).focus();
		});
		$('.limpiar_select2').click(function () {
			$('#' + $(this).attr("limpiar")).select2().val("").trigger("change");
		});
	}
}

function sec_versiones_obtener_menus_con_versiones() {
	let select = $("[name='busqueda_menu_id']");
	$.ajax({
		url: "/sys/get_versiones.php",
		type: 'POST',
		data: {
			accion: 'obtener_menus_con_versiones'
		},
		beforeSend: function () { },
		complete: function () { },
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			$(select).find('option').remove().end();
			$(select).append('<option value="">-- TODOS --</option>');
			$(respuesta.result).each(function (i, e) {
				opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);
			})
		},
		error: function () { }
	});
}

function sec_versiones_obtener_menus() {
	let select = $("[name='modal_menu_id']");
	$.ajax({
		url: "/sys/get_versiones.php",
		type: 'POST',
		data: {
			accion: 'obtener_menus'
		},
		beforeSend: function () { },
		complete: function () { },
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			$(select).find('option').remove().end();
			$(select).append('<option value="">-- Seleccione --</option>');
			$(respuesta.result).each(function (i, e) {
				opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);
			})
		},
		error: function () { }
	});
}

function sec_versiones_obtener_creadores_de_versiones() {
	let select = $("[name='busqueda_creado_por_id']");
	$.ajax({
		url: "/sys/get_versiones.php",
		type: 'POST',
		data: {
			accion: 'obtener_creadores_de_versiones'
		},
		beforeSend: function () { },
		complete: function () { },
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			$(select).find('option').remove().end();
			$(select).append('<option value="">-- TODOS --</option>');
			$(respuesta.result).each(function (i, e) {
				opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);
			})
		},
		error: function () { }
	});
}

function sec_versiones_listar_versiones_por_menu() {
	$.ajax({
		url: "/sys/get_versiones.php",
		type: 'POST',
		data: {
			accion: 'listar_versiones_por_menu'
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				swal(respuesta.status, respuesta.error, 'warning');
				auditoria_send({
					"proceso": "listar_versiones_por_menu",
					"data": respuesta
				});
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#tbody_versiones_por_menu').html(respuesta.result);
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({
				"proceso": "listar_versiones_por_menu_error",
				"data": result
			});
			return false;
		}
	});
	return false;
}

function sec_versiones_listar_historial_de_versiones() {
	$.ajax({
		url: "/sys/get_versiones.php",
		type: 'POST',
		data: {
			accion: 'listar_historial_de_versiones'
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				swal(respuesta.status, respuesta.error, 'warning');
				auditoria_send({
					"proceso": "listar_historial_de_versiones",
					"data": respuesta
				});
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#tbody_historial_de_versiones').html(respuesta.result);
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({
				"proceso": "listar_historial_de_versiones_error",
				"data": result
			});
			return false;
		}
	});
	return false;
}

function sec_versiones_modal_agregar_versiones(tipo_operacion_id, version_detalle_id, nombre_imagen) {
	$('#modal_agregar_versiones_form')[0].reset();
	$('#modal_agregar_versiones').modal({
		backdrop: 'static',
		keyboard: false
	});
	$('#modal_agregar_versiones_tipo_operacion_id').val(tipo_operacion_id);
	if (tipo_operacion_id == 'new') {
		$('#modal_agregar_versiones_titulo').html('Agregar Versión');
	} else if (tipo_operacion_id == 'edit') {
		$('#modal_agregar_versiones_titulo').html('Editar Versión: ' + nombre_version);
		$('#modal_version_detalle_id').val(version_detalle_id);
	}
	setTimeout(function () {
		$('#modal_comentario').focus();
	}, 500);
}

function sec_versiones_agregar_version() {
	$('#modal_agregar_versiones_btn_guardar').hide();
	var tipo_operacion_id = $('#modal_agregar_versiones_tipo_operacion_id').val();
	var version_detalle_id = $('#modal_version_detalle_id').val();
	var menu_id = $('#modal_menu_id').val().trim();
	var comentario = $('#modal_comentario').val();

	if (!(menu_id.length > 0)) {
		swal('Aviso', 'Selecciones un menú.', 'warning');
		$('#modal_agregar_versiones_btn_guardar').show();
		return false;
	}

	if (!(comentario.length > 0)) {
		swal('Aviso', 'Ingrese un comentario.', 'warning');
		$('#modal_agregar_versiones_btn_guardar').show();
		return false;
	}

	var data = {
		"accion": "guardar_version",
		"tipo_operacion_id": tipo_operacion_id,
		"version_detalle_id": version_detalle_id,
		"menu_id": menu_id,
		"comentario": comentario
	}

	$.ajax({
		url: "/sys/set_versiones.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({
				"proceso": "guardar_version",
				"data": respuesta
			});
			if (parseInt(respuesta.http_code) == 400) {
				swal(respuesta.status, respuesta.insert_error, 'warning');
				$('#modal_agregar_versiones_btn_guardar').show();

				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_agregar_versiones').modal('hide');
				swal(respuesta.status, '', 'success');
				$('#modal_agregar_versiones_btn_guardar').show();
				sec_versiones_listar_versiones_por_menu();
				sec_versiones_listar_historial_de_versiones();
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({
				"proceso": "guardar_version_error",
				"data": result
			});
			$('#modal_agregar_versiones_btn_guardar').show();
			return false;
		}
	});
	return false;
}