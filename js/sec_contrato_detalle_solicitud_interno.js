

function sec_contrato_detalle_solicitud_interno(){
	var midocu = '';
	sec_con_detalle_int_actualizar_div_observaciones();
	sec_con_detalle_int_actualizar_div_observaciones_gerencia();
	sec_con_nuevo_int_obtener_opciones("obtener_bancos","[name='sec_con_det_banco']");
	// setArchivo_requisitos_arrendamiento($('#fileArchivo_requisitos_arrendamiento'));
	setTimeout(function() {
		sec_contrato_detalle_interno_collapse_contrato('show');
	}, 500);
	$(".select2").select2({ width: "100%" });
	$('.sec_contrato_detalle_solicitud_datepicker')
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

	$('.fecha_detalle_arrendemiento_datepicker')
		.datepicker({
			dateFormat:'dd/mm/yy',
			changeMonth: true,
			changeYear: true,
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "dd/mm/yy"));
		});

	// INICIO DECLARACION DE MASK
	$('.area_cuadrada').mask('000');
	$('.num_suministro').mask('000000000', {'translation': {0: {pattern: /[0-9-]/}}});

	$('.money').mask('00,000.00', {reverse: true});
	$('.vigencia_meses').mask('00');

	$('.num_ruc').mask('00000000000');
	// FIN DECLARACION DE MASK

	$("#check_collapse").change(function (event) {
		if (event.currentTarget.checked) {
			sec_contrato_detalle_interno_collapse_contrato('hide');
		} else {
			sec_contrato_detalle_interno_collapse_contrato('show');
		}
	});

	// INICIO OTROS EVENTOS
	$("#editar_solicitud_valor_decimal").on({
		"focus": function (event) {
			$(event.target).select();
		},
		"blur": function (event) {
			if(parseFloat($(event.target).val().replace(/\,/g, ''))>0){
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
				$(event.target).val(function (index, value ) {
					return value.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		}
	});
	// FIN OTROS EVENTOS

	// INICIO OBTENER LAS FORMAS DE PAGO
	setTimeout(function() {
		var tipo_contrato_id_temporal = $('#tipo_contrato_id_temporal').val();
		if (tipo_contrato_id_temporal == "1") {
			sec_contrato_detalle_solicitud_emails_enviados_formato_de_pago();
			sec_con_detalle_int_obtener_opciones("obtener_tipo_mes_adelanto",$("[name='num_periodo_id']"));
		} else if(tipo_contrato_id_temporal == "2") {
			sec_con_detalle_int_obtener_opciones("obtener_monedas",$("[name='moneda_id']"));
			sec_con_detalle_int_obtener_opciones("obtener_forma_pago",$("[name='forma_pago']"));
			sec_con_detalle_int_obtener_opciones("obtener_tipo_comprobante",$("[name='tipo_comprobante']"));
		}
	}, 100);
	// FIN OBTENER LAS FORMAS DE PAGO

	// INICIO CONTRAPRESTACION
	$("#moneda_id").change(function () {
		$("#moneda_id option:selected").each(function () {
			moneda_id = $(this).val();
			if (moneda_id != 0) {
				setTimeout(function() {
					$('#subtotal').focus();
				}, 200);
			}
		});
	});

	$("#forma_pago").change(function () {
		$("#forma_pago option:selected").each(function () {
			forma_pago = $(this).val();
			if (forma_pago != 0) {
				setTimeout(function() {
					$('#tipo_comprobante').focus();
					$('#tipo_comprobante').select2('open');
				}, 200);
			}
		});
	});

	$("#tipo_comprobante").change(function () {
		$("#tipo_comprobante option:selected").each(function () {
			tipo_comprobante = $(this).val();
			if (tipo_comprobante != 0) {
				setTimeout(function() {
					$('#plazo_pago').focus();
				}, 200);
			}
		});
	});
	// FIN CONTRAPRESTACION

	// INICIO EVENTOS CONTRATO DE PROVEEDOR - CONTRAPRESTACION
	$("#subtotal").on({
		"focus": function (event) {
			$(event.target).select();
		},
		"blur": function (event) {
			if(parseFloat($(event.target).val().replace(/\,/g, ''))>0){
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
				$(event.target).val(function (index, value ) {
					return value.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		}
	});

	$("#igv").on({
		"focus": function (event) {
			$(event.target).select();
		},
		"blur": function (event) {
			if(parseFloat($(event.target).val().replace(/\,/g, ''))>0){
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
				$(event.target).val(function (index, value ) {
					return value.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		}
	});

	$("#monto").on({
		"focus": function (event) {
			$(event.target).select();
		},
		"change": function (event) {
			if(parseFloat($(event.target).val().replace(/\,/g, ''))>0){
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
				$(event.target).val(function (index, value ) {
					return value.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		}
	});
	// FIN EVENTOS CONTRATO DE PROVEEDOR - CONTRAPRESTACION

	$("#fecha_vencimiento_indefinida_id").change(function () {
		$("#fecha_vencimiento_indefinida_id option:selected").each(function () {
			fecha_vencimiento_indefinida_id = $(this).val();
			if (fecha_vencimiento_indefinida_id == 1) {
				$('#div_fecha_de_vencimiento').hide();
			} else {
				$('#div_fecha_de_vencimiento').show();
				$('#cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param').focus();
			}
		});
	});

	// INICIO CHANGE INCREMENTOS
	$("#contrato_incrementos_monto_o_porcentaje").on({
		"focus": function (event) {
			$(event.target).select();
		},
		"change": function (event) {
			if(parseFloat($(event.target).val().replace(/\,/g, ''))>0){
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
				$(event.target).val(function (index, value ) {
					return value.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		}
	});

	$("#contrato_incrementos_en").change(function () {
		$("#contrato_incrementos_en option:selected").each(function () {
			incrementos_en = $(this).val();
			if (incrementos_en != 0) {
				setTimeout(function() {
					$('#contrato_incrementos_continuidad').select2('open');
				}, 200);
			}
		});
	});

	$("#contrato_incrementos_continuidad").change(function () {
		$("#contrato_incrementos_continuidad option:selected").each(function () {
			continuidad_id = $(this).val();

			if (continuidad_id == 3){
				$("#titulo_incremento_a_partir").html('');
				$("#titulo_incremento_a_partir").hide();
				$("#td_contrato_incrementos_a_partir_de_año").hide();
			} else {
				if (continuidad_id == 1) {
					$("#titulo_incremento_a_partir").html('El');
				} else if (continuidad_id == 2){
					$("#titulo_incremento_a_partir").html('A partir del');
				} 

				$("#titulo_incremento_a_partir").show();
				$("#td_contrato_incrementos_a_partir_de_año").show();

				setTimeout(function() {
					$('#contrato_incrementos_a_partir_de_año').select2('open');
				}, 200);
			}

		});
	});	
	// FIN CHANGE INCREMENTOS

	

	// INICIO CHANGE BENEFICIARIO
	$("#modal_beneficiario_tipo_persona").change(function () {
		$("#modal_beneficiario_tipo_persona option:selected").each(function () {
			tipo_persona = $(this).val();
			if (tipo_persona == 1 || tipo_persona == 2) {
				setTimeout(function() {
					$('#modal_beneficiario_nombre').focus();
				}, 100);
			} else if (tipo_persona == 0) {
				$('#modal_beneficiario_tipo_persona').focus();
			}
		});
	});

	$("#modal_beneficiario_tipo_docu").change(function () {
		$("#modal_beneficiario_tipo_docu option:selected").each(function () {
			tipo_docu = $(this).val();
			if (tipo_docu == 1 || tipo_docu == 2 || tipo_docu == 3) {
				if (tipo_docu == 1) {
					$('#modal_beneficiario_num_docu').mask('00000000');
				} else if (tipo_docu == 2) {
					$('#modal_beneficiario_num_docu').mask('00000000000');
				}

				setTimeout(function() {
					$('#modal_beneficiario_num_docu').focus();
				}, 100);
			} else if (tipo_docu == 0) {
				$('#modal_beneficiario_tipo_docu').focus();
			}
		});
	});

	$("#modal_beneficiario_id_forma_pago").change(function () {
		$("#modal_beneficiario_id_forma_pago option:selected").each(function () {
			id_forma_pago = $(this).val();
			if (id_forma_pago == 1 || id_forma_pago == 2) {
				$('#div_modal_beneficiario_nombre_banco').show();
				$('#div_modal_beneficiario_numero_cuenta_bancaria').show();
				$('#div_modal_beneficiario_numero_CCI').show();
				setTimeout(function() {
					$('#modal_beneficiario_id_banco').select2('open');
				}, 200);
			} else if (id_forma_pago == 3) {
				$('#div_modal_beneficiario_nombre_banco').hide();
				$('#div_modal_beneficiario_numero_cuenta_bancaria').hide();
				$('#div_modal_beneficiario_numero_CCI').hide();
				setTimeout(function() {
					$('#modal_beneficiario_tipo_monto').select2('open');
				}, 200);
			}
		});
	});

	$("#modal_beneficiario_id_banco").change(function () {
		$("#modal_beneficiario_id_banco option:selected").each(function () {
			id_banco = $(this).val();
			if (id_banco == 0) {
				setTimeout(function() {
					$('#modal_beneficiario_id_banco').select2('open');
				}, 500);
			} else {
				setTimeout(function() {
					$('#modal_beneficiario_num_cuenta_bancaria').focus();
				}, 200);
			}
		});
	});

	$("#modal_beneficiario_tipo_monto").change(function () {
		$("#modal_beneficiario_tipo_monto option:selected").each(function () {
			tipo_monto = $(this).val();
			if (tipo_monto == 1 || tipo_monto == 2) {
				$('#div_modal_beneficiario_monto').show();
				if (tipo_monto == 1) {
					$('#label_beneficiario_tipo_pago').text('Monto (Según la moneda del contrato)');
				} else if (tipo_monto == 2) {
					$('#label_beneficiario_tipo_pago').text('Porcentaje (%)');
				}
				setTimeout(function() {
					$('#modal_beneficiario_monto').focus();
				}, 200);
			} else if (tipo_monto == 3) {
				$('#div_modal_beneficiario_monto').hide();
			}
		});
	});

	$("#modal_beneficiario_monto").on({
		"focus": function (event) {
			$(event.target).select();
		},
		"change": function (event) {
			if(parseFloat($(event.target).val().replace(/\,/g, ''))>0){
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
				$(event.target).val(function (index, value ) {
					return value.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		}
	});
	// FIN CHANGE BENEFICIARIO

	// INICIO CHANGE PROPIETARIOS
	$("#modal_propietario_tipo_persona").change(function () {
		$("#modal_propietario_tipo_persona option:selected").each(function () {
			tipo_persona = $(this).val();
			if (tipo_persona == 1) {
				$('#modal_propietario_tipo_docu').val('1');
				$('#div_modal_propietario_representante_legal').hide();
				$('#div_modal_propietario_num_partida_registral').hide();
			} else if (tipo_persona == 2) {
				$('#modal_propietario_tipo_docu').val('2');
				$('#div_modal_propietario_representante_legal').show();
				$('#div_modal_propietario_num_partida_registral').show();
			}
			$('#modal_propietario_tipo_docu').change();
			setTimeout(function() {
				$('#modal_propietario_nombre').focus();
			}, 200);		
		});
	});

	$("#modal_propietario_tipo_docu").change(function () {
		$("#modal_propietario_tipo_docu option:selected").each(function () {
			propietario_tipo_docu = $(this).val();
			if (propietario_tipo_docu == 1 || propietario_tipo_docu == 3) {
				$('#div_num_docu_propietario').show();

				if(propietario_tipo_docu == 1){
					$('#label_num_docu_propietario').html('Número de DNI del propietario:');
					$('#modal_propietario_num_docu').mask('00000000');
				} else if(propietario_tipo_docu == 3){
					$('#label_num_docu_propietario').html('Número de Pasaporte del propietario:');
					$('#modal_propietario_num_docu').mask('000000000000', {'translation': { 0: {pattern: /[A-Za-z0-9]/}}});
				}

				setTimeout(function() {
					$('#modal_propietario_num_docu').focus();
				}, 200);
			} else if (propietario_tipo_docu == 2) {
				$('#div_num_docu_propietario').hide();

				setTimeout(function() {
					$('#modal_propietario_num_ruc').focus();
				}, 200);
			}
		});
	});

	$("#modal_propietario_tipo_persona_contacto").change(function () {
		$("#modal_propietario_tipo_persona_contacto option:selected").each(function () {
			tipo_persona_contacto = $(this).val();
			if (tipo_persona_contacto == 1) {
				$('#div_modal_propietario_contacto_nombre').hide();
				$('#modal_propietario_contacto_telefono').focus();
			} else if (tipo_persona_contacto == 2) {
				$('#div_modal_propietario_contacto_nombre').show();
				$('#modal_propietario_contacto_nombre').focus();
			}
		});
	});
	// FIN CHANGE PROPIETARIOS
}

$("#estado_solicitud").change(function () {
	$("#estado_solicitud option:selected").each(function () {
		estado_solicitud = $(this).val();
		if (estado_solicitud == 4) {
			$("#divNoAplica").show();
		 
		} else   {
			$("#divNoAplica").hide();
		} 
	});
});

function sec_con_detalle_int_editar_solicitud(nombre_menu_usuario, nombre_tabla, nombre_campo, nombre_campo_usuario, tipo_valor, valor_actual, metodo_select, id_tabla) {
	$('#form_editar_solicitud')[0].reset();
	$('#modal_editar_solicitud').modal({backdrop: 'static', keyboard: false});
	$('#editar_solicitud_nombre_menu_usuario').html(nombre_menu_usuario);
	$('#editar_solicitud_nombre_campo_usuario').html(nombre_campo_usuario);
	$('#editar_solicitud_valor_actual').html(valor_actual);

	$('#editar_solicitud_nombre_tabla').val(nombre_tabla);
	$('#editar_solicitud_nombre_campo').val(nombre_campo);
	$('#editar_solicitud_tipo_valor').val(tipo_valor);
	$('#editar_solicitud_id_tabla').val(id_tabla);

	$('#div_editar_solicitud_departamento').hide();
	$('#div_editar_solicitud_provincias').hide();
	$('#div_editar_solicitud_distrito').hide();

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
			$('#div_editar_solicitud_valor_textarea').focus();
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
		if (nombre_campo == 'ubigeo_id') {
			$('#div_editar_solicitud_departamento').show();
			$('#div_editar_solicitud_provincias').show();
			$('#div_editar_solicitud_distrito').show();
			$('#div_editar_solicitud_valor_select_option').hide();
			sec_con_detalle_int_obtener_opciones(metodo_select,$("[name='inmueble_id_departamento']"));
			setTimeout(function() {
				$('#editar_solicitud_valor_select_option').focus();
			}, 500);
		} else {
			$('#div_editar_solicitud_departamento').hide();
			$('#div_editar_solicitud_provincias').hide();
			$('#div_editar_solicitud_distrito').hide();
			$('#div_editar_solicitud_valor_select_option').show();
			sec_con_detalle_int_obtener_opciones(metodo_select,$("[name='editar_solicitud_valor_select_option']"));
			setTimeout(function() {
				$('#editar_solicitud_valor_select_option').select2('open');
				$('#editar_solicitud_valor_select_option').focus();
			}, 400);
		}        
	}
	
}

function sec_con_detalle_int_editar_campo_solicitud(name_modal_close) {
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
	var ubigeo_id_nuevo = $('#ubigeo_id_nuevo').val();
	var ubigeo_text_nuevo = $('#ubigeo_text_nuevo').val();
	var contrato_id = $('#contrato_id_temporal').val();

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
		"ubigeo_id_nuevo": ubigeo_id_nuevo,
		"ubigeo_text_nuevo": ubigeo_text_nuevo,
		"contrato_id": contrato_id
	}

	auditoria_send({ "proceso": "editar_solicitud", "data": data });

	$.ajax({
		url: "/sys/set_contrato_detalle_solicitud_interno.php",
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


function sec_con_detalle_int_obtener_opciones(accion,select){
	 $.ajax({
		url: "/sys/set_contrato_nuevo.php",
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

function sec_con_detalle_int_agregar_representante(){
	$('#modalSecConDetProvAgregarRepresentante').modal({backdrop: 'static', keyboard: false});
	sec_con_detalle_int_limpiarInputsRL();
}

function sec_con_detalle_int_limpiarInputsRL(){
	$('#sec_con_det_dni_representante').val('');
	$('#sec_con_det_nombre_representante').val('');
	$('#sec_con_det_sec_con_nuev_prov_nro_cuenta').val('');
	$('#sec_con_det_sec_con_nuev_prov_nro_cci').val('');
	$('#sec_con_det_sec_con_nuevo_prov_banco').val('0').trigger('change.select2');
	$('#sec_con_det_prov_file_vigencia_nuevo_rl').val('');
	$('#sec_con_det_prov_file_dni_nuevo_rl').val('');
}


function sec_con_detalle_int_guardar_nuevo_representante_legal(){
	var contrato_id = $("#sec_con_det_prov_id_contrato_modal_nuevo_representante").val();
	var dniRepresentante = $('#sec_con_det_dni_representante').val();
	// if(dniRepresentante.length != 8){		
	// 	alertify.error("DNI debe tener 8 dígitos", 8);
	// 	return false;
	// }
	var nombreRepresentante = $('#sec_con_det_nombre_representante').val();
	var banco = $('#sec_con_det_banco').val();
	// var banco_nombre = $('#sec_con_det_banco').val();
	var nro_cuenta = $('#sec_con_det_nro_cuenta').val();
	var nro_cci = $('#sec_con_det_nro_cci').val();
	var input_vacios = "";
	// if($.trim(dniRepresentante) == "") { input_vacios += " - DNI del Representante"; }
	// if($.trim(nombreRepresentante) == "") { input_vacios += " - Nombre del Representante"; }

	if($.trim(input_vacios) != ""){
		alertify.error("Datos Vacios: " + input_vacios, 8);
		return;
	}

	var form_data = new FormData($("#sec_con_nuevo_agregar_nuevo_representante_legal_form")[0]);
	form_data.append("accion","sec_con_detalle_agregar_representante_legal");
	form_data.append("contrato_id" , contrato_id);
	form_data.append("dniRepresentante", dniRepresentante);
	form_data.append("nombreRepresentante", nombreRepresentante);
	form_data.append("banco", banco);
	form_data.append("nro_cuenta", nro_cuenta);
	form_data.append("nro_cci", nro_cci);
	loading(true);

	auditoria_send({ "proceso": "sec_con_detalle_agregar_representante_legal", "data": form_data });

	$.ajax({
		url: "/sys/set_contrato_detalle_solicitud_interno.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {
			result = JSON.parse(response);
			loading();
			if(result.status)
			{
				m_reload();
				swal(result.message, "", "success");
			}
			else
			{
				swal({ type: "warning", title: "Alerta!", text: result.message, html: true });
			}
		},
		always: function(data){
			loading();
			
		}
	});
}

function sec_con_detalle_int_agregarNuevoAnexoConProv(){
	$('#modalNuevosAnexosConProv').modal({backdrop: 'static', keyboard: false});
	sec_con_detalle_int_cargar_tipos_anexos_con_prov();
}

function sec_con_detalle_int_cargar_tipos_anexos_con_prov(){
	$('#modal_nuevo_anexo_select_tipos_anexos_con_prov').html('');
	$('#modal_nuevo_anexo_select_tipos_anexos_con_prov').append(
		'<option value="0"> - Seleccione - </option>'
	);

	var tipo_contrato_id = 2;
	var data = {
		"accion": "sec_contrato_nuevo_obtener_tipos_de_archivos",
		"tipo_contrato_id": tipo_contrato_id
	}
	auditoria_send({ "proceso": "sec_contrato_nuevo_obtener_tipos_de_archivos", "data": data });
	$.ajax({
		url: "sys/get_contrato_nuevo.php",
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
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$.each(respuesta.result, function(index, item) {
					$('#modal_nuevo_anexo_select_tipos_anexos_con_prov').append(
						'<option value="' + item.tipo_archivo_id + '">' + item.nombre_tipo_archivo + '</option>'
					);
				});
				return false;
			}      
		},
		error: function() {}
	});
}

var mi_ruta_temporal = '';

function sec_con_detalle_int_ver_documento_en_visor(ruta, documento,tipodocumento,titulo)
{   
	mi_ruta_temporal = ruta;
	midocu = documento;
	var tipodocumento = tipodocumento.toLowerCase();
	var html = '';
	var destino = '';

	$('#divDetalleSolicitud').hide();

	if ( $('#divFormatoDePago').length ) {
		$('#divFormatoDePago').hide();
	}
	
	$('#divAnexos').hide();

	if (tipodocumento == 'html') {

		if(titulo == '') {
			$('#divDetalleSolicitud').show();
		} else if (titulo == 'formato_de_pago') {
			if ( $('#divFormatoDePago').length ) {
				$('#divFormatoDePago').show();
			}
		}

	} else if (tipodocumento == 'pdf') {

		$('#divAnexoHeadingValue').html(titulo);
		$('#divDetalleSolicitud').hide();
		$('#divAnexos').show();
		$('#divVerPdfFullPantalla').show();
		$('#divVisorPdfPrincipal').show();
		$('#divVerImagenFullPantalla').hide();
		$('#divDescargarImagen').hide();
		$('#divVisorImagen').hide();

		html = '<iframe src="' + mi_ruta_temporal + midocu + '" class="col-xs-12 col-md-12 col-sm-12" height="580"></iframe>';
		var htmlModal = '<iframe src="' + mi_ruta_temporal + midocu + '" class="col-xs-12 col-md-12 col-sm-12" height="525"></iframe>';
		$('#divVisorPdfPrincipal').html(html);
		$('#divVisorPdfModal').html(htmlModal);

	} else {

		$('#divAnexoHeadingValue').html(titulo);
		$('#divDetalleSolicitud').hide();
		$('#divAnexos').show();
		$('#divVerPdfFullPantalla').hide();
		$('#divVisorPdfPrincipal').hide();
		$('#divVerImagenFullPantalla').show();
		$('#divDescargarImagen').show();
		$('#divVisorImagen').show();

		destino = "sec_contrato_detalle_solicitud_interno_btn_descargar('"+ mi_ruta_temporal.replace("/var/www/html", "") + midocu +"')";
		$("#sec_contrato_detalle_solicitud_descargar_imagen").attr("onClick", destino);

		if (tipodocumento == 'jpg' || tipodocumento == 'png' || tipodocumento == 'jpeg') {
			html = '<img src="' + mi_ruta_temporal + midocu + '" class="img-responsive" style="border: 1px solid;">';
			document.getElementById('sec_contrato_detalle_solicitud_ver_imagen_full_pantalla').removeEventListener('click', sec_contrato_detalle_solicitud_ver_imagen_full_pantalla );
			document.getElementById('sec_contrato_detalle_solicitud_ver_imagen_full_pantalla').addEventListener('click', sec_contrato_detalle_solicitud_ver_imagen_full_pantalla );
			$('#divVerImagenFullPantalla').show();
			$("#divDescargarImagen").removeClass("col-xs-12 col-md-12 col-sm-12");
			$("#divDescargarImagen").addClass("col-xs-3 col-md-3 col-sm-3");
		} else {
			$('#divVerImagenFullPantalla').hide();
			$("#divDescargarImagen").removeClass("col-xs-3 col-md-3 col-sm-3");
			$("#divDescargarImagen").addClass("col-xs-12 col-md-12 col-sm-12");
			html = '<a title="El documento no se puede visualizar en el sistema, clic en descargar para visualizalo en su equipo" onClick="' + destino + '"><img src="/img/document_cant_display.jpg" class="img-responsive" style="border: 1px solid;"></a>';
		}

		$('#divVisorImagen').html(html);

		
	}
}

function sec_contrato_detalle_solicitud_interno_btn_descargar(ruta_archivo)
{
	var extension = "";

	// Obtener el nombre del archivo
	var ultimoPunto = ruta_archivo.lastIndexOf("/");

	if(ultimoPunto !== -1)
	{
	    var extension = ruta_archivo.substring(ultimoPunto + 1);
	}
	
	// Crear un enlace temporal
    var enlace = document.createElement('a');
    enlace.href = ruta_archivo;

    // Darle un nombre al archivo que se descargará
    enlace.download = extension;

    // Simular un clic en el enlace
    document.body.appendChild(enlace);
    enlace.click();

    // Limpiar el enlace temporal
    document.body.removeChild(enlace);
}

function sec_con_detalle_int_guardar_adenda_firmada(adenda_id) {
	var contrato_id = $('#contrato_id_temporal').val();
	var adenda_id = $('#adenda_id_'+adenda_id).val().trim();
	var adenda_firmada = document.getElementById("adenda_firmada_"+adenda_id);

	if(adenda_firmada.files.length == 0 ){
		alertify.error('Ingrese la adenda firmada',5);
		$("#adenda_firmada").focus();
		return false;
	}

	var dataForm = new FormData($("#form_adenda_firmada_"+adenda_id)[0]);

	dataForm.append("accion","guardar_adenda_contrato_interno_firmada");
	dataForm.append("contrato_id", contrato_id);
	dataForm.append("adenda_id", adenda_id);

	auditoria_send({ "proceso": "guardar_adenda_contrato_interno_firmada", "data": dataForm });

	$.ajax({
		url: "sys/set_contrato_detalle_solicitud_interno.php",
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function( xhr ) {
			loading(true);
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({ "respuesta": "guardar_adenda_contrato_interno_firmada", "data": respuesta });
			
		 	// if(parseInt(respuesta.http_code) == 400){
			// 	if (respuesta.campo_incompleto == "abogado") {
			// 		var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el abogado.';
			// 		var msg_error_text = '¿Desea ingresar el abogado?';
			// 		var msg_error_confirmButtonText = 'SI, AGREGAR EL ABOGADO';
			// 	}
				
			// 	swal({
			// 		title: msg_error_titulo,
			// 		text: msg_error_text,
			// 		html: true,
			// 		type: "warning",
			// 		showCancelButton: true,
			// 		confirmButtonColor: '#1cb787',
			// 		cancelButtonColor: '#d56d6d',
			// 		confirmButtonText: msg_error_confirmButtonText,
			// 		cancelButtonText: 'CANCELAR'
			// 	}, function (isConfirm) {
			// 		if(isConfirm){
			// 			if (respuesta.campo_incompleto == 'abogado') {
			// 				$('#btn_editar_adenda_abogado_'+adenda_id).click();
			// 			}
			// 		}
			// 	});
			// }
			if (parseInt(respuesta.http_code) == 200) {
				window.location.href = window.location.href;
				return false;
			}
		},
		complete: function(){
			loading(false);
		}
	});
}

function sec_con_detalle_int_validate_email(email) {
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}

function sec_con_detalle_int_guardar_observaciones()
{
	var contrato_id = $('#contrato_id_temporal').val();
	var observaciones = $('#contrato_observaciones_proveedor').val();
	var correos_adjuntos = $('#correos_adjuntos').val();

	if (observaciones.trim() == "") {
		alertify.error('Ingrese una observación',5);
		$("#contrato_observaciones_proveedor").focus();
		return false;
	}

	var new_correos = [];
	if(correos_adjuntos.length > 0){
		correos_adjuntos = correos_adjuntos.split(',');
		new_correos = correos_adjuntos.map( item => item.trim());
		for (let index = 0; index < new_correos.length; index++) {
			const element = new_correos[index];
			if (element.length == 0) {
				alertify.error('Ingrese un correo',5);
				$("#correos_adjuntos").focus();
				return false;
			}
			if (!sec_con_detalle_int_validate_email(element)) {
				$("#correos_adjuntos").focus();
				alertify.error(element + ' no es correo valido',5);
				return false;
			}
		}
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
				"tipo_observacion" : 'contrato_interno',
				"observaciones": observaciones,
				"correos_adjuntos": new_correos
			}
		
			auditoria_send({ "proceso": "guardar_observaciones_contrato_proveedor", "data": data });
		
			$.ajax({
				url: "/sys/set_contrato_detalle_solicitud_interno.php",
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
						sec_con_detalle_int_actualizar_div_observaciones();
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

function sec_con_detalle_int_guardar_observaciones_gerencia()
{
	var contrato_id = $('#contrato_id_temporal').val();
	var observaciones = $('#contrato_observaciones_int_gerencia').val();

	if (observaciones.trim() == "") {
		alertify.error('Ingrese una observación',5);
		$("#contrato_observaciones_int_gerencia").focus();
		return false;
	}

	var data = {
		"accion": "guardar_observaciones_contrato_gerencia",
		"contrato_id": contrato_id,
		"tipo_observacion" : 'contrato_interno',
		"observaciones": observaciones
	}

	auditoria_send({ "proceso": "guardar_observaciones_contrato_gerencia", "data": data });

	$.ajax({
		url: "/sys/set_contrato_detalle_solicitud_interno.php",
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
			auditoria_send({ "respuesta": "guardar_observaciones_contrato_gerencia", "data": respuesta });
			
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
				sec_con_detalle_int_actualizar_div_observaciones();
				sec_con_detalle_int_actualizar_div_observaciones_gerencia();
				$('#contrato_observaciones_int_gerencia').val('');
				$('#contrato_observaciones_int_gerencia').focus();
				return false;
			}
		},
		error: function() {}
	});
}

function sec_con_detalle_int_actualizar_div_observaciones(){
	var contrato_id = $('#contrato_id_temporal').val();

	var data = {
		"accion": "obtener_observaciones",
		"contrato_id": contrato_id,
	}

	auditoria_send({ "proceso": "obtener_observaciones", "data": data });

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
			
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}
			
			if (parseInt(respuesta.http_code) == 200) {
				$('#div_observaciones').html(respuesta.result);

				if(respuesta.cant_mensaje > 4)
				{
					document.getElementById('div_observaciones').style.height = "40em";
					document.getElementById('div_observaciones').style.overflow = "scroll";
						 
				}

				return false;
			}
		},
		error: function() {}
	});
}

function sec_con_detalle_int_actualizar_div_observaciones_gerencia(){
	var contrato_id = $('#contrato_id_temporal').val();

	var data = {
		"accion": "obtener_observaciones_gerencia",
		"contrato_id": contrato_id,
	}

	auditoria_send({ "proceso": "obtener_observaciones_gerencia", "data": data });

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
			
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}
			
			if (parseInt(respuesta.http_code) == 200) {
				$('#div_observaciones_gerencia').html(respuesta.result);

				if(respuesta.cant_mensaje > 4)
				{
					document.getElementById('div_observaciones_gerencia').style.height = "40em";
					document.getElementById('div_observaciones_gerencia').style.overflow = "scroll";
						 
				}

				return false;
			}
		},
		error: function() {}
	});
}

function sec_con_detalle_int_guardar_contrato_firmado()
{
	var contrato_id = $('#contrato_id_temporal').val();
	var archivo_contrato_proveedor = document.getElementById("archivo_contrato_proveedor");
	var cont_detalle_proveedor_contrato_firmado_categoria_param = $('#cont_detalle_proveedor_contrato_firmado_categoria_param').val();
	var cont_detalle_proveedor_contrato_firmado_tipo_contrato_param = $('#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param').val();
	var cont_detalle_proveedor_contrato_firmado_tipo_firma_param = $('#cont_detalle_proveedor_contrato_firmado_tipo_firma_param').val();
	var cont_detalle_proveedor_contrato_firmado_fecha_incio_param = $('#cont_detalle_proveedor_contrato_firmado_fecha_incio_param').val();
	var cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param = $('#cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param').val();
	var cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = $('#cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param').val();
	var correos_adjuntos = $('#con_firmado_correos_adjuntos').val();
	var fecha_vencimiento_indefinida_id = $('#fecha_vencimiento_indefinida_id').val();
	var cont_detalle_proveedor_renovacion_automatica = $('#cont_detalle_proveedor_renovacion_automatica').val();

	
	if (parseInt(cont_detalle_proveedor_contrato_firmado_categoria_param) == 0) {
		alertify.error('Seleccione la categoría',5);
		$('#cont_detalle_proveedor_contrato_firmado_categoria_param').focus();
		$('#cont_detalle_proveedor_contrato_firmado_categoria_param').select2('open');
		return false;
	}

	if (parseInt(cont_detalle_proveedor_contrato_firmado_tipo_contrato_param) == 0) {
		alertify.error('Seleccione el tipo contrato',5);
		$('#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param').focus();
		$('#cont_detalle_proveedor_contrato_firmado_tipo_contrato_param').select2('open');
		return false;
	}

	if (parseInt(cont_detalle_proveedor_contrato_firmado_tipo_firma_param) == 0) {
		alertify.error('Seleccione el tipo de firma',5);
		$('#cont_detalle_proveedor_contrato_firmado_tipo_firma_param').focus();
		$('#cont_detalle_proveedor_contrato_firmado_tipo_firma_param').select2('open');
		return false;
	}

	if (fecha_vencimiento_indefinida_id == 2 && cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param == '') {
		alertify.error('Seleccione la fecha de vencimiento',5);
		$('#cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param').focus();
		return false;
	}

	if(archivo_contrato_proveedor.files.length == 0 ){
		alertify.error('Ingrese el contrato firmado',5);
		$("#archivo_partida_registral").focus();
		return false;
	}
	var new_correos = [];
	if(correos_adjuntos.length > 0){
		correos_adjuntos = correos_adjuntos.split(',');
		new_correos = correos_adjuntos.map( item => item.trim());
		for (let index = 0; index < new_correos.length; index++) {
			const element = new_correos[index];
			if (element.length == 0) {
				alertify.error('Ingrese un correo',5);
				$("#correos_adjuntos").focus();
				return false;
			}
			if (!sec_con_detalle_int_validate_email(element)) {
				$("#correos_adjuntos").focus();
				alertify.error(element + ' no es correo valido',5);
				return false;
			}
		}
	}

	if (parseInt(cont_detalle_proveedor_renovacion_automatica) == 0) {
		alertify.error('Completar el campo de Renovación Automática', 5);
		$("#cont_detalle_proveedor_renovacion_automatica").focus();
		$('#cont_detalle_proveedor_renovacion_automatica').select2('open');
		return false;
	}


	var dataForm = new FormData($("#form_contrato_proveedor_firmado")[0]);

	dataForm.append("accion","guardar_contrato_interno_firmado");
	dataForm.append("contrato_id", contrato_id);
	dataForm.append("cont_detalle_proveedor_contrato_firmado_categoria_param", cont_detalle_proveedor_contrato_firmado_categoria_param);
	dataForm.append("cont_detalle_proveedor_contrato_firmado_tipo_contrato_param", cont_detalle_proveedor_contrato_firmado_tipo_contrato_param);

	dataForm.append("cont_detalle_proveedor_contrato_firmado_tipo_firma_param", cont_detalle_proveedor_contrato_firmado_tipo_firma_param);
	dataForm.append("cont_detalle_proveedor_contrato_firmado_fecha_incio_param", cont_detalle_proveedor_contrato_firmado_fecha_incio_param);
	dataForm.append("cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param", cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param);
	dataForm.append("cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param", cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param);
	dataForm.append("new_correos", new_correos);
	dataForm.append("fecha_vencimiento_indefinida_id", fecha_vencimiento_indefinida_id);
	dataForm.append("cont_detalle_proveedor_renovacion_automatica", cont_detalle_proveedor_renovacion_automatica);

	auditoria_send({ "proceso": "guardar_contrato_interno_firmado", "data": dataForm });

	$.ajax({
		url: "sys/set_contrato_detalle_solicitud_interno.php",
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function( xhr ) {
			loading(true);
		},
		success: function(resp) { //  alert(datat)
			
			var respuesta = JSON.parse(resp);
			auditoria_send({ "proceso": "set_contrato_detalle_solicitud_interno", "data": respuesta });
			
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
			// }else if(parseInt(respuesta.http_code) == 400){
			// 	if (respuesta.campo_incompleto == "abogado") {
			// 		var msg_error_titulo = 'No se pudo agregar el contrato firmado ya que falta ingresar el abogado.';
			// 		var msg_error_text = '¿Desea ingresar el abogado?';
			// 		var msg_error_confirmButtonText = 'SI, AGREGAR EL ABOGADO';
			// 	}
				
			// 	swal({
			// 		title: msg_error_titulo,
			// 		text: msg_error_text,
			// 		html: true,
			// 		type: "warning",
			// 		showCancelButton: true,
			// 		confirmButtonColor: '#1cb787',
			// 		cancelButtonColor: '#d56d6d',
			// 		confirmButtonText: msg_error_confirmButtonText,
			// 		cancelButtonText: 'CANCELAR'
			// 	}, function (isConfirm) {
			// 		if(isConfirm){
			// 			if (respuesta.campo_incompleto == 'abogado') {
			// 				$('#btn_editar_abogado').click();
			// 			}
			// 		}
			// 	});
			}else if (parseInt(respuesta.http_code) == 200) {
				location.reload(true);
				return false;
			}
		},
		complete: function(){
			loading(false);
		}
	});
}

function sec_con_detalle_int_guardar_estado_solicitud(){
	var contrato_id = $('#contrato_id_temporal').val();
	var estado_solicitud = $('#estado_solicitud').val().trim();
	var motivo_estado_na = ($('#motivo_estado_na').val()).trim();

	if (estado_solicitud.length == 0 | estado_solicitud == '') {
		alertify.error('Seleccione una estado de solicitud',5);
		$("#estado_solicitud").focus();
		return false;
	}

	if (parseInt(estado_solicitud) == 4 && motivo_estado_na.length == 0 ) {
		alertify.error('Ingrese un motivo',5);
		$("#motivo_estado_na").focus();
		return false;
	}
	if(motivo_estado_na.length>=1000){
		alertify.error('Tamaño maximo de caracteres permitidos (1000)',5);
		$("#estado_solicitud").focus();
		return false;
	}
	
	var data = {
		"accion": "guardar_estado_solicitud",
		"contrato_id": contrato_id,
		"estado_solicitud": estado_solicitud,
		"motivo_estado_na": motivo_estado_na,
	}

	auditoria_send({ "proceso": "guardar_estado_solicitud_de_contrato", "data": data });

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
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({ "proceso": "guardar_estado_solicitud_de_contrato", "data": respuesta });
			
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.message, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				location.reload(true);
				
			 
				return false;
			}
			 
		},
		error: function() {}
	});
}

$(".sec_con_detalle_int_btn_guardar_aprobar_gerencia").click(function ()
{
	var contrato_id = $('#contrato_id_temporal').val();
	var cont_detalle_interno_aprobacion_gerencia_param = $(this).val();

	var texto_mensaje_pregunta = "";

	if(cont_detalle_interno_aprobacion_gerencia_param == 1)
	{
		texto_mensaje_pregunta = "¿Está seguro de aprobar la solicitud?";
	}
	else
	{
		texto_mensaje_pregunta = "¿Está seguro de rechazar la solicitud?";
	}

	swal(
	{
		title: texto_mensaje_pregunta,
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
			var data = {
				"accion": "sec_contrato_detalle_interno_aprobar_solicitud_gerencia",
				"contrato_id" : contrato_id,
				"cont_detalle_interno_aprobacion_gerencia_param" : cont_detalle_interno_aprobacion_gerencia_param
			}

			auditoria_send({ "proceso": "sec_contrato_detalle_interno_aprobar_solicitud_gerencia", "data": data });

			$.ajax({
				url : "/sys/set_contrato_nuevo_interno.php",
				type : "POST",
				data : data,
				//contentType : false,
				//processData : false,
				beforeSend: function( xhr ) {
					loading(true);
				},
				success : function(resp)
				{
					var respuesta = JSON.parse(resp);

					auditoria_send({ "proceso": "sec_contrato_detalle_interno_aprobar_solicitud_gerencia", "data": respuesta });

					if (parseInt(respuesta.http_code) == 500) {
						swal({
							title: respuesta.mensaje,
							text: "",
							html:true,
							type: respuesta.status,
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					} else if (respuesta.status == 200) {
						
						swal({
							title: respuesta.message,
							text: '',
							type: "success",
							timer: 5000,
							closeOnConfirm: false
						});

						setTimeout(() => {
							location.reload();
						}, 1000);

					}
				
				},
				complete: function(){
					loading(false);
				}
			});
		}
	}
	);
})


function sec_con_detalle_int_modal_guardar_nuevo_anexo(){
	var contrato_id = $("#sec_det_con_prov_id_contrato").val();
	var id_tipo_archivo = $('#modal_nuevo_anexo_select_tipos_anexos_con_prov').val();

	if (id_tipo_archivo == 0) {
		alertify.error('Seleccione el tipo de anexo',5);
	} else {
		var form_data = new FormData($("#sec_nuevo_form_modal_nuevo_anexo")[0]);
		form_data.append("post_archivo_req_solicitud_arrendamiento", 1);
		form_data.append("id_archivo", "0");
		form_data.append("contrato_id", contrato_id);
		form_data.append("id_tipo_archivo", id_tipo_archivo);
		form_data.append("id_representante_legal", "");
		
		loading(true);

		auditoria_send({ "proceso": "post_archivo_req_solicitud_arrendamiento", "data": form_data });

		$.ajax({
			url: "/sys/set_contrato_detalle_solicitud_interno.php",
			type: "POST",
			data: form_data,
			cache: false,
			contentType: false,
			processData:false,
			success: function(response, status) {
				result = JSON.parse(response);
				loading();
				if(result.status)
				{
					m_reload();
					swal(result.message, "", "success");
				}
				else
				{
					swal({ type: "warning", title: "Alerta!", text: result.message, html: true });
				}
			},
			always: function(data){
				loading();
				
			}
		});
	}
}

function sec_con_detalle_int_reenviar_correo(contrato_id)
{
	var data = {
		'accion' : "reenviar_correo_contrato_interno",
		'contrato_id' : contrato_id
	};
	console.log(data)

	auditoria_send({ "proceso": "reenviar_correo_contrato_interno", "data": data });
	$.ajax({
		url: "sys/set_contrato_nuevo_interno.php",
		type: 'POST',
		data: data,
		success: function(resp) {			
			var respuesta = JSON.parse(resp);
			auditoria_send({ "respuesta": "set_contrato_detalle_solicitud_interno", "data": respuesta });
			if (parseInt(respuesta.http_code) == 200) {
				swal({
					title: "Se ha reenviado el corro exitosamente",
					text: "",
					html:true,
					type: 'success',
					timer: 3000,
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

function sec_con_detalle_int_reenviar_correo_gerencia(contrato_id)
{
	var data = {
		'accion' : "reenviar_correo_contrato_interno_gerencia",
		'contrato_id' : contrato_id
	};

	

	swal({
        html:true,
        title: 'Notificar Observación Corregida',
        text: "¿Desea notificar a Lourdes Brito?",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1cb787',
        cancelButtonColor: '#d56d6d',
        confirmButtonText: 'SI, NOTIFICAR',
        cancelButtonText: 'CANCELAR',
        closeOnConfirm: false,
        //,showLoaderOnConfirm: true
    }, function(){
    	auditoria_send({ "proceso": "reenviar_correo_contrato_interno_gerencia", "data": data });
		$.ajax({
			url: "/sys/set_contrato_nuevo_interno.php",
			type: 'POST',
			data: data,
			beforeSend: function() 
			{
				loading("true");
			},
			complete: function() 
			{
				loading();
			},
			success: function(resp) 
			{ //  alert(datat)
				var respuesta = JSON.parse(resp);
				
				if (parseInt(respuesta.status) == 400) 
				{
					swal({
						title: "Error al enviar Solicitud de Contrato interno",
						text: respuesta.error,
						html:true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
					});
				}
				
				if (parseInt(respuesta.status) == 200) 
				{
					swal({
						title: "Reenvío exitoso",
						text: "La solicitud de Contrato Interno fue enviada exitosamente",
						html:true,
						type: "success",
						timer: 6000,
						closeOnConfirm: false,
						showCancelButton: false
					});
					
					return false;
				}
			},
			error: function() {}
		});
    });

    return false;
		
}

function sec_con_detalle_int_archivo_representante_legal(nombre_documento, id_archivo, id_tipo_archivo, id_representante_legal)
{
	$("#moda_subir_archivo_req_solicitud h4").html("Agregar archivo - " +nombre_documento);

	$("#moda_subir_archivo_req_solicitud #id_archivo").val(+id_archivo);
	$("#moda_subir_archivo_req_solicitud #id_tipo_archivo").val(+id_tipo_archivo);
	$("#moda_subir_archivo_req_solicitud #id_representante_legal").val(+id_representante_legal);
	
	$('#moda_subir_archivo_req_solicitud').modal("show");
}

function sec_con_detalle_int_moda_subir_archivo(nombre_documento, id_archivo, id_tipo_archivo)
{
	$("#moda_subir_archivo_req_solicitud h4").html("Agregar archivo - " +nombre_documento);

	$("#moda_subir_archivo_req_solicitud #id_archivo").val(+id_archivo);
	$("#moda_subir_archivo_req_solicitud #id_tipo_archivo").val(+id_tipo_archivo);
	$("#moda_subir_archivo_req_solicitud #id_representante_legal").val('');
	$('#moda_subir_archivo_req_solicitud').modal("show");
}

function sec_con_detalle_int_moda_reemplazar_archivo(nombre_documento, id_archivo, id_tipo_archivo)
{
	$("#moda_subir_archivo_req_solicitud h4").html("Reemplazar archivo - " +nombre_documento);

	$("#moda_subir_archivo_req_solicitud #id_archivo").val(+id_archivo);
	$("#moda_subir_archivo_req_solicitud #id_tipo_archivo").val(+id_tipo_archivo);
	$("#moda_subir_archivo_req_solicitud #id_representante_legal").val('');
	$('#moda_subir_archivo_req_solicitud').modal("show");
}

function sec_con_detalle_int_cerrar_moda_subir_archivo()
{
	truncated = "";
	$("#txtFile_req_solicitud_arrendamiento").html(truncated);

	$("#moda_subir_archivo_req_solicitud").modal("hide");
}

$(document).on('submit', "#formArchivosModal_req_solicitud_interno", function(e) 
{

	var id_archivo = $("#id_archivo").val();
	var contrato_id = $("#id_contrato_req_file_arrendamiento").val();
	var id_representante_legal = $("#id_representante_legal").val();
	
	//var contrato_id =document.getElementById("id_contrato_req_file_arrendamiento").value;
	
	var id_tipo_archivo = $("#id_tipo_archivo").val();
	e.preventDefault();
	var form_data = (new FormData(this));
	form_data.append("post_archivo_req_solicitud_arrendamiento", 1);
	form_data.append("id_archivo", id_archivo);
	form_data.append("contrato_id", contrato_id);
	form_data.append("id_tipo_archivo", id_tipo_archivo);
	form_data.append("id_representante_legal", id_representante_legal);
	
	loading(true);

	auditoria_send({ "proceso": "post_archivo_req_solicitud_arrendamiento", "data": form_data });

	$.ajax({
		url: "/sys/set_contrato_detalle_solicitud_interno.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {
			result = JSON.parse(response);
			loading();
			if(result.status)
			{
				m_reload();
				swal(result.message, "", "success");

			}
			else
			{
				swal(
				{
					type: "warning",
					title: "Alerta!",
					text: result.message,
					html: true,
				});
			}
			//filter_archivos_table(0);
		},
		always: function(data){
			loading();
			
		}
	});
});

function sec_contrato_detalle_interno_collapse_contrato(tipo) {
	$('.panel-collapse-all').collapse(tipo);
}
