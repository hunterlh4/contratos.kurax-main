// INICIO DECLARACION DE VARIABLES ARRAY
var array_proveedores_contrato = [];
var array_contraprestacion_contrato = [];
var array_nuevos_files_anexos = [];
var array_adendas_contrato = [];
// FIN DECLARACION DE VARIABLES ARRAY

function sec_contrato_nuevo_adenda_agente() {
	$(".select2").select2({ width: "100%" });
	$('.sec_contrato_nuevo_datepicker')
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
	// sec_con_nuevo_aden_agente_obtener_opciones("obtener_personal_responsable_agente","[name='sec_con_nuevo_supervisor']");
	// sec_con_nuevo_aden_agente_obtener_opciones("obtener_bancos","[name='sec_con_nuevo_banco']");
	// sec_con_nuevo_aden_agente_obtener_opciones("obtener_periodo","[name='periodo']");
	sec_con_nuevo_aden_agente_obtener_opciones("obtener_monedas","[name='modal_contr_ade_int_moneda_id']");
	sec_con_nuevo_aden_agente_obtener_opciones("obtener_forma_pago","[name='modal_contr_ade_int_forma_pago']");
	sec_con_nuevo_aden_agente_obtener_opciones("obtener_tipo_comprobante","[name='modal_contr_ade_int_tipo_comprobante']");
	sec_contrato_nuevo_obtener_opciones("obtener_directores", "[name='director_aprobacion_id']");

	sec_con_nuevo_aden_agente_obtener_contratos();

	$("#modal_propietario_tipo_persona_aa").change(function () {
		$("#modal_propietario_tipo_persona_aa option:selected").each(function () {
		  tipo_persona = $(this).val();
		  if (tipo_persona == 1) {
			$("#modal_propietario_tipo_docu_aa").val("1");
			$("#div_modal_propietario_representante_legal_aa").hide();
			$("#div_modal_propietario_num_partida_registral_aa").hide();
		  } else if (tipo_persona == 2) {
			$("#modal_propietario_tipo_docu_aa").val("2");
			$("#div_modal_propietario_representante_legal_aa").show();
			$("#div_modal_propietario_num_partida_registral_aa").show();
		  }
		  $("#modal_propietario_tipo_docu_aa").change();
		  setTimeout(function () {
			$("#modal_propietario_nombre").focus();
		  }, 200);
		});
	  });
	
	  $("#modal_propietario_tipo_persona_contacto_aa").change(function () {
		$("#modal_propietario_tipo_persona_contacto_aa option:selected").each(function () {
		  tipo_persona_contacto = $(this).val();
		  if (tipo_persona_contacto == 1) {
			$("#div_modal_propietario_contacto_nombre_aa").hide();
			$("#modal_propietario_contacto_telefono_aa").focus();
		  } else if (tipo_persona_contacto == 2) {
			$("#div_modal_propietario_contacto_nombre_aa").show();
			$("#modal_propietario_contacto_nombre_aa").focus();
		  }
		});
	  });
	  $("#modal_propietario_tipo_docu_aa").change(function () {
		$("#modal_propietario_tipo_docu_aa option:selected").each(function () {
			propietario_tipo_docu = $(this).val();
			if (propietario_tipo_docu == 1 || propietario_tipo_docu == 3 || propietario_tipo_docu == 4) {
				$("#div_num_docu_propietario_aa").show();
	
				if (propietario_tipo_docu == 1) {
					$("#label_num_docu_propietario_aa").html("Número de DNI del propietario:");
					$(".mask_dni_agente").mask("00000000");
				} else if (propietario_tipo_docu == 3) {
					$("#label_num_docu_propietario_aa").html("Número de Pasaporte del propietario:");
					$(".mask_dni_agente").mask("000000000000");
				} else if (propietario_tipo_docu == 4) {
					$("#label_num_docu_propietario_aa").html("Número de Carnet de Ext del propietario:");
					$(".mask_dni_agente").mask("000000000000");
				}
	
				setTimeout(function () {
					$("#modal_propietario_num_docu_aa").focus();
				}, 200);
			} else if (propietario_tipo_docu == 2) {
				$("#div_num_docu_propietario_aa").hide();
	
				setTimeout(function () {
					$("#modal_propietario_num_ruc_aa").focus();
				}, 200);
			}
		});
	});
	
	$("#adenda_inmueble_id_departamento").change(function () {
		$("#adenda_inmueble_id_departamento option:selected").each(function () {
			adenda_inmueble_id_departamento = $(this).val();
			var data = {
				accion: "obtener_provincias_segun_departamento",
				departamento_id: adenda_inmueble_id_departamento,
			};
			var array_provincias = [];
			auditoria_send({ proceso: "obtener_provincias_segun_departamento", data: data });
			$.ajax({
				url: "/sys/set_contrato_nuevo.php",
				type: "POST",
				data: data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (resp) {
					//  alert(datat)
					var respuesta = JSON.parse(resp);
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 400) {
					}
	
					if (parseInt(respuesta.http_code) == 200) {
						array_provincias.push(respuesta.result);
	
						var html = '<option value="0">Seleccione la provincia</option>';
						for (var i = 0; i < array_provincias[0].length; i++) {
							html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
						}
	
						console.log(html);
	
						$("#adenda_inmueble_id_provincia").html(html).trigger("change");
	
						setTimeout(function () {
							$("#adenda_inmueble_id_provincia").select2("open");
						}, 500);
	
						return false;
					}
				},
				error: function () {},
			});
		});
	});
	
	$("#adenda_inmueble_id_provincia").change(function () {
		$("#adenda_inmueble_id_provincia option:selected").each(function () {
			adenda_inmueble_id_provincia = $(this).val();
			adenda_inmueble_id_departamento = $("#adenda_inmueble_id_departamento").val();
			var data = {
				accion: "obtener_distritos_segun_provincia",
				provincia_id: adenda_inmueble_id_provincia,
				departamento_id: adenda_inmueble_id_departamento,
			};
			var array_distritos = [];
			auditoria_send({ proceso: "obtener_distritos_segun_provincia", data: data });
			$.ajax({
				url: "/sys/set_contrato_nuevo.php",
				type: "POST",
				data: data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (resp) {
					//  alert(datat)
					var respuesta = JSON.parse(resp);
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 400) {
					}
	
					if (parseInt(respuesta.http_code) == 200) {
						array_distritos.push(respuesta.result);
						console.log("Cantidad de Registro: " + array_distritos.length);
						var html = '<option value="0">Seleccione el distrito</option>';
	
						for (var i = 0; i < array_distritos[0].length; i++) {
							html += "<option value=" + array_distritos[0][i].id + ">" + array_distritos[0][i].nombre + "</option>";
						}
	
						console.log(html);
	
						$("#adenda_inmueble_id_distrito").html(html).trigger("change");
	
						setTimeout(function () {
							$("#adenda_inmueble_id_distrito").select2("open");
						}, 500);
	
						return false;
					}
				},
				error: function () {},
			});
		});
	});
	
	$("#adenda_inmueble_id_distrito").change(function () {
		var departamento_id = $("#adenda_inmueble_id_departamento").val().toString();
		var provincia_id = $("#adenda_inmueble_id_provincia").val().toString();
		var distrito_id = $("#adenda_inmueble_id_distrito").val().toString();
	
		var departamento_text = "";
		var data = $("#adenda_inmueble_id_departamento").select2("data");
		if (data) {
			departamento_text = data[0].text;
		}
	
		var provincia_text = "";
		var data = $("#adenda_inmueble_id_provincia").select2("data");
		if (data) {
			provincia_text = data[0].text;
		}
	
		var distrito_text = "";
		var data = $("#adenda_inmueble_id_distrito").select2("data");
		if (data) {
			distrito_text = data[0].text;
		}
	
		$("#ubigeo_id_nuevo").val(departamento_id + provincia_id + distrito_id);
		$("#ubigeo_text_nuevo").val(departamento_text + "/" + provincia_text + "/" + distrito_text);
	});
	
	
}

function sec_con_nuevo_aden_agente_obtener_opciones(accion,select){
	$.ajax({
	   url: "/sys/get_contrato_nuevo_adenda_agente.php",
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

function sec_con_nuevo_aden_agente_obtener_contratos() {

	var supervisor = $('#sec_con_nuevo_supervisor').val();

	let data = {
		accion:'obtener_contratos',
		supervisor:supervisor,
	};


	var select = "[name='sec_con_nuevo_contrato_id']";
	$.ajax({
		url: "/sys/get_contrato_nuevo_adenda_agente.php",
		type: 'POST',
		data: data,
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

function sec_con_nuevo_aden_agente_obtener_contratos_interno_id() {

	var contrato_id = $('#sec_con_nuevo_contrato_id').val();
	let data = {
		accion:'obtener_contrato_por_id',
		contrato_id:contrato_id,
	};
	if (contrato_id == "" || contrato_id == 0) {
		$('#div_contrato_interno').html('');
		$('#div_detalle_solicitud_derecha').hide();
		return false;
	}
	
	$.ajax({
		url: "/sys/get_contrato_nuevo_adenda_agente.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
		},
		complete: function () {
		},
		success: function (datos) {//  alert(datat)
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$('#div_contrato_interno').html(respuesta.result);
				$('#div_detalle_solicitud_derecha').show();
			}
		},
		error: function () {
		}
	});
}

function sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(nombre_menu_usuario, nombre_tabla, nombre_campo, nombre_campo_usuario, tipo_valor, valor_actual, metodo_select, id_del_registro) {
	$("#div_modal_adenda_mensaje").hide();
	$('#form_adenda')[0].reset();
	$('#modal_adenda').modal({backdrop: 'static', keyboard: false});
	$('#adenda_valor_select_option').select2({
        dropdownParent: $('#modal_adenda'),
		width: '100%'
    });
	$('#adenda_nombre_menu_usuario').html(nombre_menu_usuario);
	$('#adenda_nombre_campo_usuario').html(nombre_campo_usuario);
	$('#adenda_valor_actual').html(valor_actual);

	$('#adenda_id_del_registro').val(id_del_registro);
	$('#adenda_nombre_tabla').val(nombre_tabla);
	$('#adenda_nombre_campo').val(nombre_campo);
	$('#adenda_tipo_valor').val(tipo_valor);

	if (tipo_valor == 'varchar') {
		$('#div_adenda_valor_varchar').show();
		$('#div_adenda_valor_textarea').hide();
		$('#div_adenda_valor_int').hide();
		$('#div_adenda_valor_date').hide();
		$('#div_adenda_valor_decimal').hide();
		$('#div_adenda_valor_select_option').hide();
		$('#div_adenda_solicitud_departamento').hide();
		$('#div_adenda_solicitud_provincias').hide();
		$('#div_adenda_solicitud_distrito').hide();
		setTimeout(function() {
			$('#adenda_valor_varchar').focus();
		}, 500);
	}

	if (tipo_valor == 'textarea') {
		$('#div_adenda_valor_varchar').hide();
		$('#div_adenda_valor_textarea').show();
		$('#div_adenda_valor_int').hide();
		$('#div_adenda_valor_date').hide();
		$('#div_adenda_valor_decimal').hide();
		$('#div_adenda_valor_select_option').hide();
		$('#div_adenda_solicitud_departamento').hide();
		$('#div_adenda_solicitud_provincias').hide();
		$('#div_adenda_solicitud_distrito').hide();
		setTimeout(function() {
			$('#div_adenda_valor_textarea').focus();
		}, 500);
	}

	if (tipo_valor == 'int') {
		$('#div_adenda_valor_varchar').hide();
		$('#div_adenda_valor_textarea').hide()
		$('#div_adenda_valor_int').show();
		$('#div_adenda_valor_date').hide();
		$('#div_adenda_valor_decimal').hide();
		$('#div_adenda_valor_select_option').hide();
		$('#div_adenda_solicitud_departamento').hide();
		$('#div_adenda_solicitud_provincias').hide();
		$('#div_adenda_solicitud_distrito').hide();
		setTimeout(function() {
			$('#adenda_valor_int').focus();
		}, 500);
	}

	if (tipo_valor == 'date') {
		$('#div_adenda_valor_varchar').hide();
		$('#div_adenda_valor_textarea').hide()
		$('#div_adenda_valor_int').hide();
		$('#div_adenda_valor_date').show();
		$('#div_adenda_valor_decimal').hide();
		$('#div_adenda_valor_select_option').hide();
		$('#div_adenda_solicitud_departamento').hide();
		$('#div_adenda_solicitud_provincias').hide();
		$('#div_adenda_solicitud_distrito').hide();
		setTimeout(function() {
			$('#adenda_valor_date').focus();
		}, 500);
	}

	if (tipo_valor == 'decimal') {
		$('#div_adenda_valor_varchar').hide();
		$('#div_adenda_valor_textarea').hide()
		$('#div_adenda_valor_int').hide();
		$('#div_adenda_valor_date').hide();
		$('#div_adenda_valor_decimal').show();
		$('#div_adenda_valor_select_option').hide();
		$('#div_adenda_solicitud_departamento').hide();
		$('#div_adenda_solicitud_provincias').hide();
		$('#div_adenda_solicitud_distrito').hide();
		setTimeout(function() {
			$('#adenda_valor_decimal').focus();
		}, 500);
	}

	if (tipo_valor == 'select_option') {


		if (nombre_campo == 'ubigeo_id') {

			$('#div_adenda_valor_varchar').hide();
			$('#div_adenda_valor_textarea').hide()
			$('#div_adenda_valor_int').hide();
			$('#div_adenda_valor_date').hide();
			$('#div_adenda_valor_decimal').hide();
			$('#div_adenda_solicitud_departamento').show();
			$('#div_adenda_solicitud_provincias').show();
			$('#div_adenda_solicitud_distrito').show();

			$('#div_adenda_valor_select_option').hide();
			sec_con_nuevo_aden_agente_obtener_opciones(metodo_select,$("[name='adenda_inmueble_id_departamento']"));
			$('#adenda_inmueble_id_departamento').select2({
				dropdownParent: $('#modal_adenda'),
				width: '100%'
			});
			$('#adenda_inmueble_id_provincia').select2({
				dropdownParent: $('#modal_adenda'),
				width: '100%'
			});
			$('#adenda_inmueble_id_distrito').select2({
				dropdownParent: $('#modal_adenda'),
				width: '100%'
			});
			setTimeout(function() {
				$('#adenda_inmueble_id_departamento').focus();
			}, 500);
		} else {
			$('#div_adenda_valor_varchar').hide();
			$('#div_adenda_valor_textarea').hide()
			$('#div_adenda_valor_int').hide();
			$('#div_adenda_valor_date').hide();
			$('#div_adenda_valor_decimal').hide();
			$('#div_adenda_solicitud_departamento').hide();
			$('#div_adenda_solicitud_provincias').hide();
			$('#div_adenda_solicitud_distrito').hide();


			$('#div_adenda_valor_select_option').show();
			sec_con_nuevo_aden_agente_obtener_opciones(metodo_select,$("[name='adenda_valor_select_option']"));
			setTimeout(function() {
				$('#adenda_valor_select_option').focus();
			}, 500);
		}   

		
	}
	
}

function sec_con_nuevo_aden_agente_guardar_detalle_adenda(name_modal_close) {

	var nombre_tabla = $('#adenda_nombre_tabla').val();
	var nombre_campo = $('#adenda_nombre_campo').val();
	var nombre_menu_usuario = $('#adenda_nombre_menu_usuario').html();
	var nombre_campo_usuario = $('#adenda_nombre_campo_usuario').html();
	var valor_actual = $('#adenda_valor_actual').html();
	var tipo_valor = $('#adenda_tipo_valor').val();
	var valor_varchar = $('#adenda_valor_varchar').val();
	var valor_textarea = $('#adenda_valor_textarea').val();
	var valor_int = $('#adenda_valor_int').val();
	var valor_date = $('#adenda_valor_date').val();
	var valor_decimal = $('#adenda_valor_decimal').val();
	var valor_select_option = $("#adenda_valor_select_option option:selected").text();
	var valor_select_option_id = $('#adenda_valor_select_option').val();
	var id_del_registro = $('#adenda_id_del_registro').val();

	var ubigeo_id_nuevo = $('#ubigeo_id_nuevo').val();
	var ubigeo_text_nuevo = $('#ubigeo_text_nuevo').val();

	$("#div_modal_adenda_mensaje").hide();

	if (tipo_valor == 'varchar' && valor_varchar == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('Ingrese el nuevo valor');
		$("#adenda_valor_varchar").focus();
		return;
	}

	if (tipo_valor == 'textarea' && valor_textarea == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('Ingrese el nuevo valor');
		$("#adenda_valor_textarea").focus();
		return;
	}

	if (tipo_valor == 'int' && valor_int == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('Ingrese el nuevo valor');
		$("#adenda_valor_int").focus();
		return;
	}

	if (tipo_valor == 'date' && valor_date == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('Ingrese el nuevo valor');
		$("#adenda_valor_date").focus();
		return;
	}

	if (tipo_valor == 'decimal' && valor_decimal == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('Ingrese el nuevo valor');
		$("#adenda_valor_decimal").focus();
		return;
	}

	if (tipo_valor == 'select_option' && nombre_campo == "ubigeo_id" ) {
		if (ubigeo_id_nuevo.length != 6) {
			$("#div_modal_adenda_mensaje").show();
			$("#modal_adenda_mensaje").html('Seleccione una Departamento/Provincia/Distrito');
			return;
		}
	}else{
		if (tipo_valor == 'select_option' && valor_select_option_id == 0) {
			$("#div_modal_adenda_mensaje").show();
			$("#modal_adenda_mensaje").html('Seleccione una opcion');
			$("#adenda_valor_select_option").focus();
			return;
		}
	}
	
	

	if (tipo_valor == 'select_option') {
		valor_int = valor_select_option_id;
	}

	var data = {
		"accion": "guardar_adenda_detalle",
		"nombre_tabla": nombre_tabla,
		"nombre_campo": nombre_campo,
		"nombre_menu_usuario": nombre_menu_usuario,
		"nombre_campo_usuario": nombre_campo_usuario,
		"valor_original": valor_actual,
		"tipo_valor": tipo_valor,
		"valor_varchar": valor_varchar,
		"valor_textarea": valor_textarea,
		"valor_int": valor_int,
		"valor_date": valor_date,
		"valor_decimal": valor_decimal,
		"valor_select_option": valor_select_option,
		"ubigeo_id_nuevo": ubigeo_id_nuevo,
		"ubigeo_text_nuevo": ubigeo_text_nuevo,
		"id_del_registro": id_del_registro
	}

	auditoria_send({ "proceso": "guardar_adenda_detalle", "data": data });

	$.ajax({
		url: "/sys/set_contrato_nuevo_adenda_agente.php",
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
			auditoria_send({ "respuesta": "guardar_adenda_detalle", "data": respuesta });
			console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				// $('#frm_incremento')[0].reset();
				sec_con_nuevo_aden_agente_asignar_detalle_a_la_adenda(respuesta.result, name_modal_close);
				$('#modal_adenda').modal('hide');
				document.getElementById('divTablaAdendas').focus();
				return false;
			}
		},
		error: function() {}
	});
}

function sec_con_nuevo_aden_agente_asignar_detalle_a_la_adenda(id_adenda,modal){
	if (array_adendas_contrato.includes(id_adenda) === false){
		array_adendas_contrato.push(id_adenda)
	}
	sec_con_nuevo_aden_agente_actualizar_tabla_detalle_adenda();
}

function sec_con_nuevo_aden_agente_actualizar_tabla_detalle_adenda(){

	if(array_adendas_contrato.length > 0) {

		var data = {
			"accion": "obtener_adendas_detalle",
			"id_adendas": JSON.stringify(array_adendas_contrato),
		}

		var array_adendas = [];

		auditoria_send({ "proceso": "obtener_adendas_detalle", "data": data });
		$.ajax({
			url: "/sys/set_contrato_nuevo_adenda_agente.php",
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
				console.log(respuesta);
				if (parseInt(respuesta.http_code) == 400) {
					return false;
				}
				
				if (parseInt(respuesta.http_code) == 200) {
					$('#divTablaAdendas').html(respuesta.result);
					return false;
				}
			},
			error: function() {}
		});
	} else {
		$('#divTablaAdendas').html('');
	}
}

function sec_con_nuevo_aden_agente_eliminar_detalle_adenda(id_adenda){
	const index = array_adendas_contrato.indexOf(id_adenda);
	if (index > -1) {
		array_adendas_contrato.splice(index, 1);
	}
	sec_con_nuevo_aden_agente_actualizar_tabla_detalle_adenda();
}

function sec_con_nuevo_aden_agente_agregar_representante(){
	$('#modalNuevoProveedor').modal({backdrop: 'static', keyboard: false});
	// sec_con_detalle_int_limpiarInputsRL();
}

function sec_con_nuevo_aden_agente_guardar_nuevo_representante_legal(){
	var contrato_id = $('#id_registro_contrato_id').val();

	var dniRepresentante = $('#modal_prov_ade_int_dni_representante').val();
	// if(dniRepresentante.length != 8){
	// 	alertify.error("DNI debe tener 8 dígitos", 8);
	// 	return false;
	// }
	var nombreRepresentante = $('#modal_prov_ade_int_nombre_representante').val();
	var banco = $('#modal_prov_ade_int_prov_banco').val();
	var banco_nombre = $('#modal_prov_ade_int_prov_banco option:selected').text();
	var nro_cuenta = $('#modal_prov_ade_int_nro_cuenta').val();
	var nro_cci = $('#modal_prov_ade_int_nro_cci').val();
	var input_vacios = "";
	// if($.trim(dniRepresentante) == "") { input_vacios += " - DNI del Representante"; }
	// if($.trim(nombreRepresentante) == "") { input_vacios += " - Nombre del Representante"; }
	if($.trim(banco) == 0) { input_vacios += " - Banco"; }
	if($.trim(nro_cuenta) == "" && $.trim(nro_cci) == "") { input_vacios += " - Nro Cuenta o CCI"; }

	if($.trim(input_vacios) != ""){
		alertify.error("Datos Vacios: " + input_vacios, 8);
		return;
	}

	var form_data = new FormData($("#frm_adenda_nuevo_proveedor")[0]);
	form_data.append("accion","guardar_adenda_detalle_nuevos_registros");
	form_data.append("tabla","representante_legal");
	form_data.append("contrato_id" , contrato_id);
	form_data.append("dniRepresentante", dniRepresentante);
	form_data.append("nombreRepresentante", nombreRepresentante);
	form_data.append("banco", banco);
	form_data.append("nro_cuenta", nro_cuenta);
	form_data.append("nro_cci", nro_cci);
	loading(true);
	auditoria_send({ "proceso": "guardar_adenda_detalle_nuevos_registros", "data": form_data });
	$.ajax({
		url: "/sys/set_contrato_nuevo_adenda_agente.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {
			var respuesta = JSON.parse(response);
			auditoria_send({ "respuesta": "guardar_adenda_detalle_nuevos_registros", "data": respuesta });

			if (parseInt(respuesta.http_code) == 200) {
				$('#frm_adenda_nuevo_proveedor')[0].reset();
				sec_con_nuevo_aden_agente_asignar_detalle_a_la_adenda(respuesta.result, 'modalNuevoProveedor');
			}
		},
		always: function(data){
			loading();
			console.log(data);
		}
	});
}

function sec_con_nuevo_aden_agente_asignar_detalle_a_la_adenda(id_adenda,modal){
	if (array_adendas_contrato.includes(id_adenda) === false){
		array_adendas_contrato.push(id_adenda)
	}
	$('#'.concat(modal)).modal('hide');
	
	sec_con_nuevo_aden_agente_actualizar_tabla_detalle_adenda();
}


function sec_con_nuevo_aden_agente_guardar_adenda() {

	var contrato_id = $('#id_registro_contrato_id').val();
	var tipo_contrato_id = $('#id_tipo_contrato').val();
	var aprobacion_obligatoria_id = $("#aprobacion_obligatoria_id").val().trim();
	var director_aprobacion_id = $("#director_aprobacion_id").val().trim();

	$("#div_modal_adenda_mensaje").hide();

	if (contrato_id == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('No se puede guardar la adenda');
	}

	
	if(array_adendas_contrato.length == 0) {
		alertify.error('No hay solicitud de cambio de adenda',5);
		return false;
	}

	if (aprobacion_obligatoria_id == 1 && director_aprobacion_id == 0) {
		alertify.error("Seleccione el director que va aprobar la solicitud.", 5);
		setTimeout(function() {
			$("#director_aprobacion_id").focus();
			$("#director_aprobacion_id").select2("open");
		}, 200);
		return false;
	}

	var archivos = $('input[name="miarchivo[]"]');

	// Crea un objeto FormData
	var formData = new FormData();

	// Itera sobre todos los elementos de entrada de archivos
	archivos.each(function(index, input) {
		// Obtén los archivos del campo de entrada de archivos actual
		var archivosInput = input.files;

		// Agrega cada archivo al objeto FormData
		for (var i = 0; i < archivosInput.length; i++) {
			formData.append('miarchivo[]', archivosInput[i]);
		}
	});
	var data = {
		"accion": "guardar_adenda",
		"contrato_id": contrato_id,
		"tipo_contrato_id": tipo_contrato_id,
		"id_adendas": JSON.stringify(array_adendas_contrato),
		"aprobacion_obligatoria_id": aprobacion_obligatoria_id,
		"director_aprobacion_id": director_aprobacion_id
	}
	var arrayJSON = JSON.stringify(array_nuevos_files_anexos_agente);

	formData.append('accion', 'guardar_adenda');
	formData.append('contrato_id', contrato_id);
	formData.append('tipo_contrato_id', tipo_contrato_id);
	formData.append('id_adendas', JSON.stringify(array_adendas_contrato));
	formData.append('aprobacion_obligatoria_id', aprobacion_obligatoria_id);
	formData.append('director_aprobacion_id', director_aprobacion_id);
	formData.append('array_nuevos_files_anexos', arrayJSON);
	console.log([...formData.entries()]);
	auditoria_send({ "proceso": "guardar_adenda", "data": data });

	$.ajax({
		url: "/sys/set_contrato_nuevo_adenda_agente.php",
		type: 'POST',
		data: formData,
		processData: false,  // No procesar los datos
		contentType: false,  // No establecer el tipo de contenido
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) { 
			var respuesta = JSON.parse(resp);
			auditoria_send({ "respuesta": "guardar_adenda", "data": respuesta });
			if (parseInt(respuesta.http_code) == 400) {
				swal({
					title: respuesta.message,
					text: "",
					html:true,
					type: 'warning',
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false
				});
				return false;
			}
			swal({
				title: respuesta.message,
				text: "",
				html:true,
				type: respuesta.status == 200 ? 'success':'warning',
				timer: 3000,
				closeOnConfirm: false,
				showCancelButton: false
			});
			if (parseInt(respuesta.status) == 200) {
				setTimeout(function() {
					window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
					return false;
				}, 3000);
			}
		},
		error: function() {}
	});
}


function sec_con_nuevo_aden_agente_buscar_propietario_modal(tipo_solicitud) {
	var tipo_solicitud = tipo_solicitud;
  
	$("#modal_buscar_propietario_titulo").html("Adenda - Buscar Nuevo Propietario");
	$("#modal_buscar_propietario_tipo_solicitud").val("adenda");
	$("#modal_buscar_propietario_tipo_solicitud_ca").val("adenda");
  
	$("#modalBuscarPropietario_ca").modal({ backdrop: "static", keyboard: false });
	$("#tlbPropietariosxBusqueda_ca").html("");
	$("#divNoSeEncontroPropietario_ca").hide();
	$("#divRegistrarNuevoPropietario_ca").hide();
	$("#modal_propietario_nombre_o_numdocu_ca").val("");
	$("#modal_propietario_nombre_o_numdocu_ca").focus();
  }


  function  sec_con_nuevo_aden_agente_buscar_propietario() {
	var array_propietarios = [];
	var nombre_o_numdocu = $.trim($("#modal_propietario_nombre_o_numdocu_ca").val());
	var tipo_busqueda = parseInt($.trim($("#modal_propietario_tipo_busqueda_ca").val()));
	var tipo_solicitud = $("#modal_buscar_propietario_tipo_solicitud_ca").val();
  
	if (nombre_o_numdocu.length < 3) {
	  var busqueda_por = "";
	  if (tipo_busqueda == 1) {
		busqueda_por = "Nombre del Propietario";
	  } else if (tipo_busqueda == 2) {
		busqueda_por = "Número de Documento de Identidad";
	  }
	  alertify.error("El " + busqueda_por + " debe de tener más de dos dígitos", 5);
	  $("#modal_propietario_nombre_o_numdocu_ca").focus();
	  return;
	}
  
	var data = {
	  accion: "obtener_propietario",
	  nombre_o_numdocu: nombre_o_numdocu,
	  tipo_busqueda: tipo_busqueda,
	  tipo_solicitud: tipo_solicitud,
	};
  
	auditoria_send({ proceso: "obtener_propietario", data: data });
	$.ajax({
	  url: "/sys/get_contrato_nuevo_adenda_agente.php",
	  type: "POST",
	  data: data,
	  beforeSend: function () {
		loading("true");
	  },
	  complete: function () {
		loading();
	  },
	  success: function (resp) {
		//  alert(datat)
		var respuesta = JSON.parse(resp);
		
		if (parseInt(respuesta.http_code) == 400) {
		  $("#tlbPropietariosxBusqueda_ca").html("");
		  $("#divNoSeEncontroPropietario_ca").show();
		  $("#divRegistrarNuevoPropietario_ca").show();
		  var msg = "";
		  if (tipo_busqueda == "1") {
			msg = "nombre";
		  } else {
			msg = "número de documento";
		  }
		  $("#valoresDeBusqueda_ca").text(msg + " " + nombre_o_numdocu);
		  return false;
		}
  
		if (parseInt(respuesta.http_code) == 200) {
		  $("#tlbPropietariosxBusqueda_ca").html(respuesta.result);
		  $("#divNoSeEncontroPropietario_ca").hide();
		  $("#divRegistrarNuevoPropietario_ca").show();
  
		  return false;
		}
	  },
	  error: function () {},
	});
  }


function sec_con_nuevo_aden_agente_agignar_propietario(id_persona){
	var contrato_id = $('#id_registro_contrato_id').val();

	var data = {
		"accion" : 'guardar_adenda_detalle_nuevos_registros',
		"tabla": 'propietario',
		"contrato_id": contrato_id,
		"id_persona": id_persona,
	}
	console.log(data);
	loading(true);
	auditoria_send({ "proceso": "guardar_adenda_detalle_nuevos_registros", "data": data });
	$.ajax({
		url: "/sys/set_contrato_nuevo_adenda_agente.php",
		type: "POST",
		data: data,
		success: function(response, status) {
			var respuesta = JSON.parse(response);
			auditoria_send({ "respuesta": "guardar_adenda_detalle_nuevos_registros", "data": respuesta });
			if (parseInt(respuesta.http_code) == 200) {			
				sec_con_nuevo_aden_agente_asignar_detalle_a_la_adenda(respuesta.result, 'modalBuscarPropietario_ca');
				$('#modalBuscarPropietario_ca').modal('hide');
			}
		},
		always: function(data){
			loading();
		}
	});
}


function sec_con_nuevo_aden_agente_guardar_nuevo_representante_legalaaaaaa(){
	var contrato_id = $('#id_registro_contrato_id').val();

	var dniRepresentante = $('#modal_prov_ade_int_dni_representante').val();
	// if(dniRepresentante.length != 8){
	// 	alertify.error("DNI debe tener 8 dígitos", 8);
	// 	return false;
	// }
	var nombreRepresentante = $('#modal_prov_ade_int_nombre_representante').val();
	var banco = $('#modal_prov_ade_int_prov_banco').val();
	var banco_nombre = $('#modal_prov_ade_int_prov_banco option:selected').text();
	var nro_cuenta = $('#modal_prov_ade_int_nro_cuenta').val();
	var nro_cci = $('#modal_prov_ade_int_nro_cci').val();
	var input_vacios = "";
	// if($.trim(dniRepresentante) == "") { input_vacios += " - DNI del Representante"; }
	// if($.trim(nombreRepresentante) == "") { input_vacios += " - Nombre del Representante"; }
	if($.trim(banco) == 0) { input_vacios += " - Banco"; }
	if($.trim(nro_cuenta) == "" && $.trim(nro_cci) == "") { input_vacios += " - Nro Cuenta o CCI"; }

	if($.trim(input_vacios) != ""){
		alertify.error("Datos Vacios: " + input_vacios, 8);
		return;
	}

	var form_data = new FormData($("#frm_adenda_nuevo_proveedor")[0]);
	form_data.append("accion","guardar_adenda_detalle_nuevos_registros");
	form_data.append("tabla","representante_legal");
	form_data.append("contrato_id" , contrato_id);
	form_data.append("dniRepresentante", dniRepresentante);
	form_data.append("nombreRepresentante", nombreRepresentante);
	form_data.append("banco", banco);
	form_data.append("nro_cuenta", nro_cuenta);
	form_data.append("nro_cci", nro_cci);
	loading(true);
	auditoria_send({ "proceso": "guardar_adenda_detalle_nuevos_registros", "data": form_data });
	$.ajax({
		url: "/sys/set_contrato_nuevo_adenda_agente.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {
			var respuesta = JSON.parse(response);
			auditoria_send({ "respuesta": "guardar_adenda_detalle_nuevos_registros", "data": respuesta });

			if (parseInt(respuesta.http_code) == 200) {
				$('#frm_adenda_nuevo_proveedor')[0].reset();
				sec_con_nuevo_aden_agente_asignar_detalle_a_la_adenda(respuesta.result, 'modalNuevoProveedor');
			}
		},
		always: function(data){
			loading();
			console.log(data);
		}
	});
}

function sec_con_nuevo_aden_agente_propietario_modal() {
	var tipo_solicitud = 'adenda';

	$("#modal_nuevo_propietario_tipo_solicitud_aa").val(tipo_solicitud);
	$("#modal_nuevo_propietario_titulo_aa").val("Adenda - Nuevo Propietario");
	sec_con_nuevo_aden_agente_resetear_formulario_nuevo_propietario_agente();
	
	$("#div_modal_propietario_mensaje_aa").hide();
	$("#modalBuscarPropietario_ca").modal("hide");
	$("#modalNuevoPropietario_aa").modal({ backdrop: "static", keyboard: false });
  
	var tipo_busqueda = $("#modal_propietario_tipo_busqueda_aa").val();
	var nombre_o_numdocu = $("#modal_propietario_nombre_o_numdocu_aa").val();
	if (tipo_busqueda == 1) {
	  $("#modal_propietario_nombre_aa").val(nombre_o_numdocu);
	} else if (tipo_busqueda == 2) {
	  $("#modal_propietario_num_docu_aa").val(nombre_o_numdocu);
	}
	setTimeout(function () {
	  $("#modal_propietario_tipo_persona_aa").select2("open");
	}, 500);
}


function sec_con_nuevo_aden_agente_resetear_formulario_nuevo_propietario_agente() {
	$("#frm_nuevo_propietario_aa")[0].reset();
	$("#div_modal_propietario_representante_legal_aa").hide();
	$("#div_modal_propietario_num_partida_registral_aa").hide();
  
	$("#modal_nuevo_propietario_titulo_aa").html("Registrar Propietario");
	$("#btn_agregar_propietario_aa").show();
	$("#btn_guardar_cambios_propietario_aa").hide();
	$("#btn_agregar_propietario_a_la_adenda_aa").hide();

	$("#div_modal_propietario_contacto_nombre_aa").hide();
	$("#div_modal_propietario_persona_contacto_aa").show();
	
  }

function sec_con_nuevo_aden_agente_nuevo_propietario() {

	var id_propietario_para_cambios = $("#modal_propietaria_id_persona_para_cambios_aa").val();
	var tipo_persona = $("#modal_propietario_tipo_persona_aa").val();
	var nombre = $("#modal_propietario_nombre_aa").val().trim();
	var tipo_docu = $("#modal_propietario_tipo_docu_aa").val();
	var num_docu = $("#modal_propietario_num_docu_aa").val().trim();
	var num_ruc = $("#modal_propietario_num_ruc_aa").val().trim();
	var direccion = $("#modal_propietario_direccion_aa").val().trim();
	var representante_legal = $("#modal_propietario_representante_legal_aa").val().trim();
	var num_partida_registral = $("#modal_propietario_num_partida_registral_aa").val();
	var tipo_persona_contacto = $("#modal_propietario_tipo_persona_contacto_aa").val();
	var contacto_nombre = $("#modal_propietario_contacto_nombre_aa").val().trim();
	var contacto_telefono = $("#modal_propietario_contacto_telefono_aa").val().trim();
	var contacto_email = $("#modal_propietario_contacto_email_aa").val().trim();

	if (parseInt(tipo_persona) == 0) {
		alertify.error("Seleccione el tipo de persona", 5);
		$("#modal_propietario_tipo_persona_ca").focus();
		$("#modal_propietario_tipo_persona_ca").select2("open");
		return false;
	}

	if (nombre.length < 6) {
		alertify.error("Ingrese el nombre completo del propietario", 5);
		$("#modal_propietario_nombre_ca").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 0) {
		alertify.error("Seleccione el tipo de documento de identidad", 5);
		$("#modal_propietario_tipo_docu_ca").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 1 && num_docu.length != 8) {
		alertify.error("El número de DNI debe tener 8 dígitos, no " + num_docu.length + " dígitos", 5);
		$("#modal_propietario_num_docu_ca").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 3 && num_docu.length != 12) {
		alertify.error("El número de Pasaporte debe tener 12 dígitos, no " + num_docu.length + " dígitos", 5);
		$("#modal_propietario_num_docu_ca").focus();
		return false;
	}

	if (parseInt(tipo_docu) == 4 && num_docu.length != 12) {
		alertify.error("El número de Carnet de Ext debe tener 12 dígitos, no " + num_docu.length + " dígitos", 5);
		$("#modal_propietario_num_docu_ca").focus();
		return false;
	}

	if (num_ruc.length != 11) {
		alertify.error("El número de RUC debe tener 11 dígitos, no " + num_ruc.length + " dígitos", 5);
		$("#modal_propietario_num_ruc_ca").focus();
		return false;
	}

	if (direccion.length < 10) {
		alertify.error("Ingrese el dirección completa del propietario", 5);
		$("#modal_propietario_direccion_ca").focus();
		return false;
	}

	if (parseInt(tipo_persona) == 2 && representante_legal.length == 0) {
		alertify.error("Ingrese el representante legal", 5);
		$("#modal_propietario_representante_legal_ca").focus();
		return false;
	}

	if (parseInt(tipo_persona) == 2 && num_partida_registral.length == 0) {
		alertify.error("Ingrese el número de la Partida Registral de la empresa", 5);
		$("#modal_propietario_num_partida_registral_ca").focus();
		return false;
	}

	
	if (parseInt(tipo_persona_contacto) == 0) {
		alertify.error("Seleccione el tipo de persona contacto", 5);
		$("#modal_propietario_tipo_persona_contacto_ca").focus();
		return false;
	}

	if (parseInt(tipo_persona_contacto) == 2 && contacto_nombre.length < 1) {
		alertify.error("Ingrese el nombre del contacto", 5);
		$("#modal_propietario_contacto_nombre_ca").focus();
		return false;
	}
	

	if (contacto_telefono.length < 8) {
		alertify.error("Ingrese el número telefónico del contacto", 5);
		$("#modal_propietario_contacto_telefono_ca").focus();
		return false;
	}

	if (contacto_email.length > 0 && !sec_contrato_nuevo_es_email_valido(contacto_email)) {
		alertify.error("El formato del correo electrónico es incorrecto", 5);
		$("#modal_propietario_contacto_email_ca").focus();
		return false;
	}

	var data = {
		accion: "guardar_propietario",
		id_propietario_para_cambios: id_propietario_para_cambios,
		tipo_persona: tipo_persona,
		nombre: nombre,
		tipo_docu: tipo_docu,
		num_docu: num_docu,
		num_ruc: num_ruc,
		direccion: direccion,
		representante_legal: representante_legal,
		num_partida_registral: num_partida_registral,
		tipo_persona_contacto: tipo_persona_contacto,
		contacto_nombre: contacto_nombre,
		contacto_telefono: contacto_telefono,
		contacto_email: contacto_email,
	};

	
	auditoria_send({ proceso: "guardar_propietario", data: data });

	$.ajax({
		url: "/sys/set_contrato_nuevo_adenda_agente.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({ "respuesta": "guardar_adenda_detalle_nuevos_registros", "data": respuesta });

			
			if (parseInt(respuesta.http_code) == 400) {
				alertify.error(respuesta.status, 5);
				return false;
			}
			
			if (parseInt(respuesta.http_code) == 200) {
				$('#frm_adenda_nuevo_proveedor')[0].reset();
				sec_con_nuevo_aden_agente_asignar_detalle_a_la_adenda(respuesta.result, 'modalNuevoPropietario_aa');
			}
		},
		error: function () {},
	});
}

var array_nuevos_files_anexos_agente = [];
function anadirArchivo_adenda_agente() {
	console.log('agente')
	

		var tipo_contrato_id = 1;//$("#modal_nuevo_anexo_tipo_contrato_id").val();

		//Sumamos a la variable el número de archivos.
		contArchivos = contArchivos + 1;
		//Agregamos el componente de tipo input
		var div = document.createElement("div");
		var input = document.createElement("input");
		var a = document.createElement("a");

		//Añadimos los atributos de div
		div.id = "archivo" + contArchivos;

		//Añadimos los atributos de input
		input.type = "file";
		input.name = "newAnexoPrueba[]";

		//Añadimos los atributos del enlace a eliminar
		a.href = "#";
		a.id = "archivo" + contArchivos;
		a.onclick = function () {
			borrarArchivo(a.id);
		};
		a.text = "X Eliminar archivo";

		//TIPO DE ARCHIVO SELECCIONADO
	

		var hoy = new Date();
		var fecha = hoy.getDate() + "" + (hoy.getMonth() + 1) + "" + hoy.getFullYear();
		var hora = hoy.getHours() + "" + hoy.getMinutes() + "" + hoy.getSeconds();
		var milisegundos = hoy.getMilliseconds();
		var Tiempo = fecha + "" + hora+ "" + milisegundos;
		id_nuevo_objeto_nuevo_anexo = "sec_contrato_id_" +  + Tiempo;
		//var onclick = "sec_nuevo_modal_eliminar_nuevo_anexo('" + id_nuevo_objeto_nuevo_anexo + "')";

		var onclick = "borrarArchivo_agente('" + id_nuevo_objeto_nuevo_anexo + "')";

		var html = "";
		html +=
			'<div class="col-xs-12 col-md-6 col-lg-6" style="padding: 0px; margin-bottom: 10px; font-size: 12px;" name="' +
			id_nuevo_objeto_nuevo_anexo +
			'">';
		html += '<div class="form-group">';
		html += '<div class="control-label">';
		// html += tipo_documento_seleccionado_nombre + ": ";
		html += "</div>";
		var onchange =
			"file_agente(event,'" +
			 
			id_nuevo_objeto_nuevo_anexo +
		
			
			"')";
		html += '<div style="margin-top:10px;">';
		html +=
			'<input name="miarchivo[]" type="file" id="' +
		 
			id_nuevo_objeto_nuevo_anexo +
			'" class="col-md-11" onchange="' +
			onchange +
			'" style="padding: 0px 0px;"/>';
		html +=
			'<button class="btn btn-xs btn-danger col-md-1" style="width: 22px;" onclick="' +
			onclick +
			'"><i class="fa fa-trash-o"></i></button>';
		html += "</div>";
		html += "</div>";
		html += "</div>";
		if (tipo_contrato_id == "1") {
			$("#sec_nuevo_nuevos_anexos_listado").append(html); // cargar el nuevo item
		} else if (tipo_contrato_id == "2") {
			$("#sec_nuevo_nuevos_anexos_listado_proveedor").append(html); // cargar el nuevo item
		}

		// $("#modaltiposanexos").modal("hide");

}

function borrarArchivo_agente(id_anexo) {
	//Restamos el número de archivos
	contArchivos = contArchivos - 1;

	array_nuevos_files_anexos_agente = array_nuevos_files_anexos_agente.filter((item) => item.id_objeto !== id_anexo);
	$("div[name=" + id_anexo + "]").remove();
}

function file_agente(event, id) {
	var id_ = "#" + id;
	// var id_tip_documento = idtd;
	let file = $(id_)[0].files[0];
	var nombre_archivo = file.name;
	var tamano_archivo = file.size;
	var extension = $(id_).val().replace(/^.*\./, "");

	var objeto = {
		id_objeto: id,
		nombre_archivo: nombre_archivo,
		tamano_archivo: tamano_archivo,
		extension: extension,
		// id_tip_documento: id_tip_documento,
		// tip_doc_nombre: tdnombre,
	};

	array_nuevos_files_anexos_agente.push(objeto);

	console.log(array_nuevos_files_anexos_agente);
}



