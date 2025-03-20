function sec_contrato_detalle_adenda_contrato_interno() {
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
	});
	sec_con_detalle_aden_cont_interno_obtener_adenda_detalle_contratos();
	sec_con_detalle_aden_cont_interno_obtener_opciones("obtener_agente","[name='sec_con_nuevo_agente']");
	sec_con_detalle_aden_cont_interno_obtener_opciones("obtener_monedas","[name='modal_contr_ade_int_moneda_id']");
	sec_con_detalle_aden_cont_interno_obtener_opciones("obtener_forma_pago","[name='modal_contr_ade_int_forma_pago']");
	sec_con_detalle_aden_cont_interno_obtener_opciones("obtener_tipo_comprobante","[name='modal_contr_ade_int_tipo_comprobante']");
	sec_contrato_nuevo_obtener_opciones("obtener_directores", "[name='director_aprobacion_id']");
}

function sec_con_detalle_aden_cont_interno_obtener_opciones(accion,select){
    loading(true);
	$.ajax({
	   url: "/sys/get_contrato_nuevo_adenda_contrato_interno.php",
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
		   });
           loading(false);
	   },
	   error: function () {
        loading(false);
	   }
   });
}

function sec_con_detalle_aden_cont_interno_obtener_adenda_detalle_contratos() {

	var adenda_id = $('#adenda_id').val();

	let data = {
		accion:'obtener_adenda_detalle_contrato',
		adenda_id:adenda_id,
	};
	$.ajax({
		url: "/sys/set_contrato_detalle_adenda_contrato_interno.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
		},
		complete: function () {
		},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				for (let index = 0; index < respuesta.result.length; index++) {
					sec_con_detalle_aden_cont_interno_asignar_detalle_a_la_adenda(respuesta.result[index],'');
				}
			}
			
		},
		error: function () {
		}
	});
}

function sec_con_detalle_aden_cont_interno_solicitud_editar_campo_adenda(nombre_menu_usuario, nombre_tabla, nombre_campo, nombre_campo_usuario, tipo_valor, valor_actual, metodo_select, id_del_registro) {
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
	else if (tipo_valor == 'textarea') {
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

	else if (tipo_valor == 'int') {
		$('#div_adenda_valor_varchar').hide();
		$('#div_adenda_valor_textarea').hide();
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

	else if (tipo_valor == 'date') {
		$('#div_adenda_valor_varchar').hide();
		$('#div_adenda_valor_textarea').hide();
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

	else if (tipo_valor == 'decimal') {
		$('#div_adenda_valor_varchar').hide();
		$('#div_adenda_valor_textarea').hide();
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

	else if (tipo_valor == 'select_option') {


		if (nombre_campo == 'ubigeo_id') {

			$('#div_adenda_valor_varchar').hide();
			$('#div_adenda_valor_textarea').hide();
			$('#div_adenda_valor_int').hide();
			$('#div_adenda_valor_date').hide();
			$('#div_adenda_valor_decimal').hide();
			$('#div_adenda_solicitud_departamento').show();
			$('#div_adenda_solicitud_provincias').show();
			$('#div_adenda_solicitud_distrito').show();

			$('#div_adenda_valor_select_option').hide();
			sec_con_detalle_aden_cont_interno_obtener_opciones(metodo_select,$("[name='adenda_inmueble_id_departamento']"));
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
			$('#div_adenda_valor_textarea').hide();
			$('#div_adenda_valor_int').hide();
			$('#div_adenda_valor_date').hide();
			$('#div_adenda_valor_decimal').hide();
			$('#div_adenda_solicitud_departamento').hide();
			$('#div_adenda_solicitud_provincias').hide();
			$('#div_adenda_solicitud_distrito').hide();


			$('#div_adenda_valor_select_option').show();
			sec_con_detalle_aden_cont_interno_obtener_opciones(metodo_select,$("[name='adenda_valor_select_option']"));
			setTimeout(function() {
				$('#adenda_valor_select_option').focus();
			}, 500);
		}   

		
	}
}

function sec_con_detalle_aden_cont_interno_guardar_detalle_adenda(name_modal_close) {

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
	var id_del_registro = $('#adenda_id').val();

	var ubigeo_id_nuevo = $('#ubigeo_id_nuevo').val();
	var ubigeo_text_nuevo = $('#ubigeo_text_nuevo').val();

	$("#div_modal_adenda_mensaje").hide();

	if (tipo_valor == 'varchar' && valor_varchar == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('Ingrese el nuevo valor');
		$("#adenda_valor_varchar").focus();
		return;
	}

	else if (tipo_valor == 'textarea' && valor_textarea == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('Ingrese el nuevo valor');
		$("#adenda_valor_textarea").focus();
		return;
	}

	else if (tipo_valor == 'int' && valor_int == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('Ingrese el nuevo valor');
		$("#adenda_valor_int").focus();
		return;
	}

	else if (tipo_valor == 'date' && valor_date == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('Ingrese el nuevo valor');
		$("#adenda_valor_date").focus();
		return;
	}

	else if (tipo_valor == 'decimal' && valor_decimal == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('Ingrese el nuevo valor');
		$("#adenda_valor_decimal").focus();
		return;
	}

	else if (tipo_valor == 'select_option' && nombre_campo == "ubigeo_id" ) {
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
		url: "/sys/set_contrato_detalle_adenda_contrato_interno.php",
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
				let new_adenda = {
					id:parseFloat(respuesta.result),
					type:'nuevo',
					state:1,
				}
				sec_con_detalle_aden_cont_interno_asignar_detalle_a_la_adenda(new_adenda, '');
				$('#modal_adenda').modal('hide');
				document.getElementById('divTablaAdendas').focus();
				return false;
			}
		},
		error: function() {}
	});
}

function sec_con_detalle_aden_cont_interno_asignar_detalle_a_la_adenda(adenda,modal){

	const index = array_adendas_contrato.map(item => item.id).includes(adenda.id);
	if (index == false) {
		array_adendas_contrato.push(adenda)
	}
	sec_con_detalle_aden_cont_interno_actualizar_tabla_detalle_adenda();
	
}

function sec_con_detalle_aden_cont_interno_actualizar_tabla_detalle_adenda(){

	
	if(array_adendas_contrato.length > 0) {

		var data = {
			"accion": "obtener_adendas_detalle",
			"adendas": array_adendas_contrato,
		}
		auditoria_send({ "proceso": "obtener_adendas_detalle", "data": data });
		$.ajax({
			url: "/sys/set_contrato_detalle_adenda_contrato_interno.php",
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

function sec_con_detalle_aden_cont_interno_eliminar_detalle_adenda(id_adenda){
	const index = array_adendas_contrato.map(item => item.id).indexOf(id_adenda);
	array_adendas_contrato[index].state = 0;
	console.log(array_adendas_contrato);
	sec_con_detalle_aden_cont_interno_actualizar_tabla_detalle_adenda();
}

function sec_con_detalle_aden_cont_interno_agregar_representante(){
	$('#modalNuevaCuentaBancaria').modal({backdrop: 'static', keyboard: false});
}

function sec_con_detalle_aden_cont_interno_guardar_nuevo_representante_legal(){
	var contrato_id = $('#id_registro_contrato_id').val();
	var numero_dni = $('#modal_ade_cont_interno_numero_dni').val();
	var nombre_representante = $('#modal_ade_cont_interno_representante_legal').val();
	var id_banco = $('#modal_ade_cont_interno_banco').val();
	var numero_cuenta_detraccion = $('#modal_ade_cont_interno_numero_cuenta_detraccion').val();
	var numero_cuenta = $('#modal_ade_cont_interno_n_cuenta').val();
	var numero_cci = $('#modal_ade_cont_interno_n_cci').val();
	if(numero_dni.length != 8){
		alertify.set('notifier','position', 'top-center');
		alertify.error("DNI debe tener 8 dígitos", 8);
		return false;
	}
	var input_vacios = "";
	if($.trim(numero_dni) == "") { input_vacios += " - DNI del nuevo Representante Legal"; }
	if($.trim(nombre_representante) == "") { input_vacios += " - Nombre del nuevo Representante Legal"; }
	if($.trim(numero_cuenta) == "") { input_vacios += " - N° de Cuenta"; }
	if($.trim(input_vacios) != ""){
		alertify.set('notifier','position', 'top-center');
		alertify.error("Datos Vacios: " + input_vacios, 8);
		return;
	}

	var form_data = new FormData($("#frm_adenda_nuevo_representante_legal")[0]);
	form_data.append("accion","guardar_adenda_detalle_nuevos_registros");
	form_data.append("tabla","nuevo_representante_legal");
	form_data.append("contrato_id" , contrato_id);
	form_data.append("numero_dni" , numero_dni);
	form_data.append("nombre_representante" , nombre_representante);
	form_data.append("id_banco" , id_banco);
	form_data.append("numero_cuenta_detraccion" , numero_cuenta_detraccion);
	form_data.append("numero_cuenta" , numero_cuenta);
	form_data.append("numero_cci" , numero_cci);
	loading(true);
	auditoria_send({ "proceso": "guardar_adenda_detalle_nuevos_registros", "data": form_data });
	$.ajax({
		url: "/sys/set_contrato_detalle_adenda_contrato_interno.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {
			var respuesta = JSON.parse(response);
			auditoria_send({ "respuesta": "guardar_adenda_detalle_nuevos_registros", "data": respuesta });

			if (parseInt(respuesta.http_code) == 200) {
				$('#frm_adenda_nuevo_representante_legal')[0].reset();
				let new_adenda = {
					id:parseFloat(respuesta.result),
					type:'nuevo',
					state:1,
				}
				loading(false);
				swal({
					title: 'Guardado completado',
					text: 'Nuevo Representante Legal guardado exitosamente, recargue página para visualizar cambios',
					type: "success",
					timer: 4000,
					confirmButtonText: 'Recargar página',
				}, function(){
					location.reload();
				});
			}
		},
		always: function(data){
			console.log(data);
		}
	});
}


function sec_con_detalle_aden_cont_interno_guardar_adenda() {

	var contrato_id = $('#id_registro_contrato_id').val();
	var adenda_id = $('#adenda_id').val();
	
	var tipo_contrato_id = $('#id_tipo_contrato').val();

	$("#div_modal_adenda_mensaje").hide();

	if (contrato_id == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('No se puede guardar la adenda');
	}

	
	if(array_adendas_contrato.length == 0) {
		alertify.set('notifier','position', 'top-center');
		alertify.error('No hay solicitud de cambio de adenda',5);
		return false;
	}

	var data = {
		"accion": "guardar_adenda",
		"adenda_id": adenda_id,
		"contrato_id": contrato_id,
		"tipo_contrato_id": tipo_contrato_id,
		"adendas":array_adendas_contrato
	}
	
	auditoria_send({ "proceso": "guardar_adenda", "data": data });

	loading("true");
	$.ajax({
		url: "/sys/set_contrato_detalle_adenda_contrato_interno.php",
		type: 'POST',
		data: data,
		success: function(resp) {
			loading("false");
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