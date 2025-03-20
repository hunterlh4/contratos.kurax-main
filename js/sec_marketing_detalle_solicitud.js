

function sec_marketing_detalle_solicitud() {
	$(".select2").select2({ width: "100%" });
	$(".mkt_req_datepicker").datepicker({
		dateFormat: "dd-mm-yy",
		changeMonth: true,
		changeYear: true,
	})
	// sec_mkt_req_obtener_opciones("obtener_estados","[name='mkt_detalle_solicitud_estado']");

	setTimeout(function() {
		sec_mkt_detalle_solicitud_collapse_panel('show');
	}, 500);

	$("#check_collapse").change(function (event) {
		if (event.currentTarget.checked) {
			sec_mkt_detalle_solicitud_collapse_panel('hide');
		} else {
			sec_mkt_detalle_solicitud_collapse_panel('show');
		}
	});

}
function sec_mkt_detalle_solicitud_collapse_panel(tipo) {
	$('.panel-collapse-all').collapse(tipo);
}

function sec_mkt_detalle_solicitud_obtener_opciones(accion,select){
	$.ajax({
	   url: "/sys/get_marketing_nuevo.php",
	   type: 'POST',
	   data:{accion:accion} ,//+data,
	   beforeSend: function () {
	   },
	   complete: function () {
	   },
	   success: function (datos) {//  alert(datat)
		   var respuesta = JSON.parse(datos);
		   $(select).find('option').remove().end();
		   $(select).append('<option value="0">- Seleccione -</option>');
		   $(respuesta.result).each(function(i,e){
			   opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
			   $(select).append(opcion);	
		   })
	   },
	   error: function () {
	   }
   });
}

function sec_mkt_detalle_solicitud_editar_solicitud(nombre_menu_usuario, nombre_tabla, nombre_campo, nombre_campo_usuario, tipo_valor, valor_actual, metodo_select, id_tabla) {
	$('#form_editar_solicitud')[0].reset();
	$('#modal_editar_solicitud').modal({backdrop: 'static', keyboard: false});
	$('#editar_solicitud_nombre_menu_usuario').html(nombre_menu_usuario);
	$('#editar_solicitud_nombre_campo_usuario').html(nombre_campo_usuario);
	$('#editar_solicitud_valor_actual').html(valor_actual);

	$('#editar_solicitud_nombre_tabla').val(nombre_tabla);
	$('#editar_solicitud_nombre_campo').val(nombre_campo);
	$('#editar_solicitud_tipo_valor').val(tipo_valor);
	$('#editar_solicitud_id_tabla').val(id_tabla);

	if (tipo_valor == 'varchar') {
		$('#div_editar_solicitud_valor_varchar').show();
		$('#div_editar_solicitud_valor_textarea').hide();
		$('#div_editar_solicitud_valor_int').hide();
		$('#div_editar_solicitud_valor_date').hide();
		$('#div_editar_solicitud_valor_decimal').hide();
		$('#div_editar_solicitud_valor_select_option').hide();
		setTimeout(function() {
			$('#editar_solicitud_valor_varchar').focus();
		}, 500);
	}

	if (tipo_valor == 'textarea') {
		$('#div_editar_solicitud_valor_varchar').hide();
		$('#div_editar_solicitud_valor_textarea').show();
		$('#div_editar_solicitud_valor_int').hide();
		$('#div_editar_solicitud_valor_date').hide();
		$('#div_editar_solicitud_valor_decimal').hide();
		$('#div_editar_solicitud_valor_select_option').hide();
		setTimeout(function() {
			$('#editar_solicitud_valor_textarea').focus();
		}, 500);
	}

	if (tipo_valor == 'int') {
		$('#div_editar_solicitud_valor_varchar').hide();
		$('#div_editar_solicitud_valor_textarea').hide();
		$('#div_editar_solicitud_valor_int').show();
		$('#div_editar_solicitud_valor_date').hide();
		$('#div_editar_solicitud_valor_decimal').hide();
		$('#div_editar_solicitud_valor_select_option').hide();
		setTimeout(function() {
			$('#editar_solicitud_valor_int').focus();
		}, 500);
	}

	if (tipo_valor == 'date') {
		$('#div_editar_solicitud_valor_varchar').hide();
		$('#div_editar_solicitud_valor_textarea').hide();
		$('#div_editar_solicitud_valor_int').hide();
		$('#div_editar_solicitud_valor_date').show();
		$('#div_editar_solicitud_valor_decimal').hide();
		$('#div_editar_solicitud_valor_select_option').hide();
		setTimeout(function() {
			$('#editar_solicitud_valor_date').focus();
		}, 500);
	}

	if (tipo_valor == 'decimal') {
		$('#div_editar_solicitud_valor_varchar').hide();
		$('#div_editar_solicitud_valor_textarea').hide();
		$('#div_editar_solicitud_valor_int').hide();
		$('#div_editar_solicitud_valor_date').hide();
		$('#div_editar_solicitud_valor_decimal').show();
		$('#div_editar_solicitud_valor_select_option').hide();
		setTimeout(function() {
			$('#editar_solicitud_valor_decimal').focus();
		}, 500);
	}

	if (tipo_valor == 'select_option') {
		$('#div_editar_solicitud_valor_varchar').hide();
		$('#div_editar_solicitud_valor_textarea').hide();
		$('#div_editar_solicitud_valor_int').hide();
		$('#div_editar_solicitud_valor_date').hide();
		$('#div_editar_solicitud_valor_decimal').hide();
	
		$('#div_editar_solicitud_valor_select_option').show();
		sec_mkt_detalle_solicitud_obtener_opciones(metodo_select,$("[name='editar_solicitud_valor_select_option']"));
		setTimeout(function() {
			$('#editar_solicitud_valor_select_option').select2('open');
			$('#editar_solicitud_valor_select_option').focus();
		}, 700);
		       
	}
	
}

function sec_mkt_detalle_solicitud_editar_campo_solicitud(name_modal_close) {
	var nombre_tabla = $('#editar_solicitud_nombre_tabla').val();
	var nombre_campo = $('#editar_solicitud_nombre_campo').val();
	var nombre_menu_usuario = $('#editar_solicitud_nombre_menu_usuario').html();
	var nombre_campo_usuario = $('#editar_solicitud_nombre_campo_usuario').html();
	var valor_actual = $('#editar_solicitud_valor_actual').html();
	var tipo_valor = $('#editar_solicitud_tipo_valor').val();
	var id_tabla = $('#editar_solicitud_id_tabla').val();
	var valor_varchar = $('#editar_solicitud_valor_varchar').val();
	var valor_textarea = $('#editar_solicitud_valor_textarea').val();
	var valor_int = $('#editar_solicitud_valor_int').val();
	var valor_date = $('#editar_solicitud_valor_date').val();
	var valor_decimal = $('#editar_solicitud_valor_decimal').val();
	var valor_select_option = $("#editar_solicitud_valor_select_option option:selected").text();
	var valor_select_option_id = $('#editar_solicitud_valor_select_option').val();
	var solicitud_id = $('#solicitud_id_temporal').val();

 

	var data = {
		"accion": "editar_solicitud",
		"nombre_tabla": nombre_tabla,
		"nombre_campo": nombre_campo,
		"nombre_menu_usuario": nombre_menu_usuario,
		"nombre_campo_usuario": nombre_campo_usuario,
		"valor_original": valor_actual,
		"tipo_valor": tipo_valor,
		"id_tabla": id_tabla,
		"valor_varchar": valor_varchar,
		"valor_textarea": valor_textarea,
		"valor_int": valor_int,
		"valor_date": valor_date,
		"valor_decimal": valor_decimal,
		"valor_select_option": valor_select_option,
		"valor_select_option_id": valor_select_option_id,
		"solicitud_id": solicitud_id
	}

	auditoria_send({ "proceso": "editar_solicitud", "data": data });

	$.ajax({
		url: "/sys/set_marketing_detalle_solicitud.php",
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
			auditoria_send({ "respuesta": "editar_solicitud", "data": respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.error, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				if (typeof respuesta.insert_error !== 'undefined') {
					swal('Aviso', respuesta.insert_error, 'warning');
				} else if (typeof respuesta.update_error !== 'undefined') {
					swal('Aviso', respuesta.update_error, 'warning');
				} else if (respuesta.result == 'ok') {
					swal({
						title: 'Se actualizó el campo con éxito.',
						text: '',
						type: "success",
						timer: 5000,
						closeOnConfirm: false
					});

					setTimeout(() => {
						location.reload();
					}, 1000);
				}
				return false;
			}
		},
		error: function() {}
	});
}

// INICIO GUARDAR CAMBIO DE ESTADO DE SOLICITUD DE REQUERIMIENTO
$("#frm_mkt_cambio_estado").submit(function (e) {
	e.preventDefault();

	var solicitud_id = $("#solicitud_id_temporal").val();
	var estado = $("#mkt_detalle_solicitud_estado").val();
	var fecha_entrega = $("#mkt_detalle_solicitud_fecha_entrega").val();
	var respuesta = $("#mkt_detalle_solicitud_respuesta").val();
	
	if (estado.length == 0) {
		alertify.error("Seleccione un estado", 5);
		$("#mkt_detalle_solicitud_estado").focus();
		return false;
	}
	if (fecha_entrega.length == 0) {
		alertify.error("Ingrese una fecha", 5);
		$("#mkt_detalle_solicitud_fecha_entrega").focus();
		return false;
	}
	// if (respuesta.length == 0) {
	// 	alertify.error("Ingrese una respuesta", 5);
	// 	$("#mkt_detalle_solicitud_respuesta").focus();
	// 	return false;
	// }
	var dataform = {
		"accion" : "guardar_estado_solicitud_marketing",
		"solicitud_id" : solicitud_id,
		"estado" : estado,
		"fecha_entrega" : fecha_entrega,
		"respuesta" : respuesta,
	}

	auditoria_send({ proceso: "guardar_estado_solicitud_marketing", data: dataform });

	$.ajax({
		url: "sys/set_marketing_detalle_solicitud.php",
		type: "POST",
		data: dataform,
		// cache: false,
		// contentType: false,
		// processData: false,
		beforeSend: function (xhr) {
			loading(true);
		},
		success: function (data) {
			var respuesta = JSON.parse(data);

			auditoria_send({ respuesta: "guardar_estado_solicitud_marketing", data: respuesta });

		
			if (parseInt(respuesta.status) == 200) {
				swal({
					title: respuesta.message,
					text: "",
					html: true,
					type: "success",
					timer: 4000,
					closeOnConfirm: false,
					showCancelButton: false,
				},
					function (isConfirm) {
						location.reload(true);
					}
				);
			}else{
				swal({
					title: respuesta.message,
					text: "",
					html: true,
					type: "error",
					timer: 4000,
					closeOnConfirm: false,
					showCancelButton: false,
				},
					function (isConfirm) {
						location.reload(true);
					}
				);
			}
		},
		complete: function () {
			loading(false);
		},
	});
});
// FIN GUARDAR CAMBIO DE ESTADO DE SOLICITUD DE REQUERIMIENTO