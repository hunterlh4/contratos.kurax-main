// INICIO DECLARACION DE VARIABLES ARRAY
var array_proveedores_contrato = [];
var array_contraprestacion_contrato = [];
var array_nuevos_files_anexos = [];
var array_adendas_contrato = [];
// FIN DECLARACION DE VARIABLES ARRAY

function sec_contrato_detalle_adenda_proveedor() {
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
	sec_con_detalle_aden_prov_obtener_adenda_detalle_contratos();
	sec_con_detalle_aden_prov_obtener_adenda_archivos_anexos();
	sec_con_detalle_aden_prov_obtener_opciones("obtener_proveedores","[name='sec_con_nuevo_proveedor']");
	// sec_con_detalle_aden_prov_obtener_opciones("obtener_empresa_at","[name='sec_con_nuevo_empresa_grupo_at_2']");
	// sec_con_detalle_aden_prov_obtener_opciones("obtener_bancos","[name='sec_con_nuevo_banco']");
	// sec_con_detalle_aden_prov_obtener_opciones("obtener_periodo","[name='periodo']");
	sec_con_detalle_aden_prov_obtener_opciones("obtener_monedas","[name='modal_contr_ade_int_moneda_id']");
	sec_con_detalle_aden_prov_obtener_opciones("obtener_forma_pago","[name='modal_contr_ade_int_forma_pago']");
	sec_con_detalle_aden_prov_obtener_opciones("obtener_tipo_comprobante","[name='modal_contr_ade_int_tipo_comprobante']");
	//sec_contrato_nuevo_obtener_opciones("obtener_directores", "[name='director_aprobacion_id']");
}

function sec_con_detalle_aden_prov_obtener_opciones(accion,select){
	$.ajax({
	   url: "/sys/get_contrato_nuevo_adenda_proveedor.php",
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

function sec_con_detalle_aden_prov_obtener_adenda_detalle_contratos() {

	var adenda_id = $('#sec_con_nuevo_adenda_id').val();

	let data = {
		accion:'obtener_adenda_detalle_contrato',
		adenda_id:adenda_id,
	};
	$.ajax({
		url: "/sys/set_contrato_detalle_adenda_proveedor.php",
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
					sec_con_detalle_aden_prov_asignar_detalle_a_la_adenda(respuesta.result[index],'');
				}
			}
			
		},
		error: function () {
		}
	});
}


function sec_con_detalle_aden_prov_obtener_adenda_archivos_anexos() {

	var adenda_id = $('#sec_con_nuevo_adenda_id').val();

	let data = {
		accion:'obtener_archivos_adendas',
		adenda_id:adenda_id,
	};
	$.ajax({
		url: "/sys/set_contrato_detalle_adenda_proveedor.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
		},
		complete: function () {
		},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$('#divTablaAdendasArchivosAnexos').html(respuesta.result);
			}
			
		},
		error: function () {
		}
	});
}



function sec_con_detalle_aden_prov_solicitud_editar_campo_adenda(nombre_menu_usuario, nombre_tabla, nombre_campo, nombre_campo_usuario, tipo_valor, valor_actual, metodo_select, id_del_registro) {
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

	if (tipo_valor == 'int') {
		$('#div_adenda_valor_varchar').hide();
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
			$('#div_adenda_valor_int').hide();
			$('#div_adenda_valor_date').hide();
			$('#div_adenda_valor_decimal').hide();
			$('#div_adenda_solicitud_departamento').show();
			$('#div_adenda_solicitud_provincias').show();
			$('#div_adenda_solicitud_distrito').show();

			$('#div_adenda_valor_select_option').hide();
			sec_con_detalle_aden_prov_obtener_opciones(metodo_select,$("[name='adenda_inmueble_id_departamento']"));
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
			$('#div_adenda_valor_int').hide();
			$('#div_adenda_valor_date').hide();
			$('#div_adenda_valor_decimal').hide();
			$('#div_adenda_solicitud_departamento').hide();
			$('#div_adenda_solicitud_provincias').hide();
			$('#div_adenda_solicitud_distrito').hide();


			$('#div_adenda_valor_select_option').show();
			sec_con_detalle_aden_prov_obtener_opciones(metodo_select,$("[name='adenda_valor_select_option']"));
			setTimeout(function() {
				$('#adenda_valor_select_option').focus();
			}, 500);
		}   

		
	}
	
}

function sec_con_detalle_aden_prov_guardar_detalle_adenda(name_modal_close) {

	var nombre_tabla = $('#adenda_nombre_tabla').val();
	var nombre_campo = $('#adenda_nombre_campo').val();
	var nombre_menu_usuario = $('#adenda_nombre_menu_usuario').html();
	var nombre_campo_usuario = $('#adenda_nombre_campo_usuario').html();
	var valor_actual = $('#adenda_valor_actual').html();
	var tipo_valor = $('#adenda_tipo_valor').val();
	var valor_varchar = $('#adenda_valor_varchar').val();
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
		url: "/sys/set_contrato_detalle_adenda_proveedor.php",
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
				sec_con_detalle_aden_prov_asignar_detalle_a_la_adenda(new_adenda, '');
				$('#modal_adenda').modal('hide');
				document.getElementById('divTablaAdendas').focus();
				return false;
			}
		},
		error: function() {}
	});
}

function sec_con_detalle_aden_prov_asignar_detalle_a_la_adenda(adenda,modal){

	const index = array_adendas_contrato.map(item => item.id).includes(adenda.id);
	if (index == false) {
		array_adendas_contrato.push(adenda)
	}
    // me.data_table.splice(index, 1);
	sec_con_detalle_aden_prov_actualizar_tabla_detalle_adenda();
	
}

function sec_con_detalle_aden_prov_actualizar_tabla_detalle_adenda(){

	
	if(array_adendas_contrato.length > 0) {

		var data = {
			"accion": "obtener_adendas_detalle",
			"adendas": array_adendas_contrato,
		}
		auditoria_send({ "proceso": "obtener_adendas_detalle", "data": data });
		$.ajax({
			url: "/sys/set_contrato_detalle_adenda_proveedor.php",
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

function sec_con_detalle_aden_prov_eliminar_detalle_adenda(id_adenda){
	const index = array_adendas_contrato.map(item => item.id).indexOf(id_adenda);
	array_adendas_contrato[index].state = 0;
	sec_con_detalle_aden_prov_actualizar_tabla_detalle_adenda();
}

function sec_con_detalle_aden_prov_agregar_representante(){
	$('#modalNuevoProveedor').modal({backdrop: 'static', keyboard: false});
	// sec_con_detalle_int_limpiarInputsRL();
}

function sec_con_detalle_aden_prov_guardar_nuevo_representante_legal(){
	var contrato_id = $('#id_registro_contrato_id').val();

	var dniRepresentante = $('#modal_prov_ade_int_dni_representante').val();
	if(dniRepresentante.length != 8){
		alertify.error("DNI debe tener 8 dígitos", 8);
		return false;
	}
	var nombreRepresentante = $('#modal_prov_ade_int_nombre_representante').val();
	var banco = $('#modal_prov_ade_int_prov_banco').val();
	var banco_nombre = $('#modal_prov_ade_int_prov_banco option:selected').text();
	var nro_cuenta = $('#modal_prov_ade_int_nro_cuenta').val();
	var nro_cci = $('#modal_prov_ade_int_nro_cci').val();
	var input_vacios = "";
	if($.trim(dniRepresentante) == "") { input_vacios += " - DNI del Representante"; }
	if($.trim(nombreRepresentante) == "") { input_vacios += " - Nombre del Representante"; }
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
		url: "/sys/set_contrato_detalle_adenda_proveedor.php",
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
				let new_adenda = {
					id:parseFloat(respuesta.result),
					type:'nuevo',
					state:1,
				}
				sec_con_detalle_aden_prov_asignar_detalle_a_la_adenda(new_adenda, 'modalNuevoProveedor');
			}
		},
		always: function(data){
			loading();
			console.log(data);
		}
	});
}



function sec_con_detalle_aden_prov_nuevo_contraprestacion_modal() {
	$('#modalNuevoContraprestacion').modal('show');
	$('#modal_contr_ade_int_moneda_id').focus();
	
}



//INICIO - CONTRAPRESTACIONES

$("#modal_contr_ade_int_moneda_id").change(function () {
	$("#modal_contr_ade_int_moneda_id option:selected").each(function () {
		modal_contr_ade_int_moneda_id = $(this).val();
		if (modal_contr_ade_int_moneda_id != 0) {
			setTimeout(function() {
				$('#modal_contr_ade_int_monto').focus();
			}, 200);
		}
	});
});

$("#modal_contr_ade_int_tipo_igv_id").change(function () {
	$("#modal_contr_ade_int_tipo_igv_id option:selected").each(function () {
		modal_contr_ade_int_tipo_igv_id = $(this).val();
		if (modal_contr_ade_int_tipo_igv_id != 0) {
			sec_con_detalle_aden_prov_calcular_subtotal_y_igv(modal_contr_ade_int_tipo_igv_id);
			setTimeout(function() {
				if($('#modal_contr_ade_int_tipo_comprobante').val() == "0"){
					$('#modal_contr_ade_int_tipo_comprobante').focus();
					$('#modal_contr_ade_int_tipo_comprobante').select2('open');
				}
			}, 200);
		}
	});
});

$("#sec_con_nuevo_forma_pago").change(function () {
	$("#sec_con_nuevo_forma_pago option:selected").each(function () {
		sec_con_nuevo_forma_pago = $(this).val();
		if (sec_con_nuevo_forma_pago != 0) {
			setTimeout(function() {
				$('#modal_contr_ade_int_tipo_comprobante').focus();
				$('#modal_contr_ade_int_tipo_comprobante').select2('open');
			}, 200);
		}
	});
});

$("#modal_contr_ade_int_tipo_comprobante").change(function () {
	$("#modal_contr_ade_int_tipo_comprobante option:selected").each(function () {
		modal_contr_ade_int_tipo_comprobante = $(this).val();
		if (modal_contr_ade_int_tipo_comprobante != 0) {
			setTimeout(function() {
				$('#modal_contr_ade_int_plazo_pago').focus();
			}, 200);
		}
	});
});

$("#modal_contr_ade_int_subtotal").on({
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

$("#modal_contr_ade_int_igv").on({
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

$("#modal_contr_ade_int_monto").on({
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
		$("#modal_contr_ade_int_tipo_igv_id").change();
	}
});

function sec_con_detalle_aden_prov_calcular_subtotal_y_igv(tipo)
{
	var monto = $('#modal_contr_ade_int_monto').val().trim().replace(',', '');
	var subtotal = 0;
	var igv = 0;

	if (monto != '') {
		monto = parseFloat(monto);
		subtotal = monto;

		if (tipo == "1") {
			subtotal = monto / 1.18;
			igv = monto - subtotal;
		}
	}

	$('#modal_contr_ade_int_subtotal').val(subtotal.toFixed(2));
	$('#modal_contr_ade_int_igv').val(igv.toFixed(2));

	$('#modal_contr_ade_int_subtotal').blur();
	$('#modal_contr_ade_int_igv').blur();
}

function sec_con_detalle_aden_prov_nuevo_contraprestacion() {
	var contrato_id = $('#id_registro_contrato_id').val();
	var moneda_id = $('#modal_contr_ade_int_moneda_id').val();
	var monto = $('#modal_contr_ade_int_monto').val().trim();
	var tipo_igv_id = $('#modal_contr_ade_int_tipo_igv_id').val();
	var subtotal = $('#modal_contr_ade_int_subtotal').val().trim();
	var igv = $('#modal_contr_ade_int_igv').val().trim();
	var forma_pago = $('#modal_contr_ade_int_forma_pago').val();
	var tipo_comprobante = $('#modal_contr_ade_int_tipo_comprobante').val().trim();
	var plazo_pago = $('#modal_contr_ade_int_plazo_pago').val();
	var forma_pago_detallado = $('#modal_contr_ade_int_forma_pago_detallado').val();

	if (parseInt(moneda_id) == 0) {
		alertify.error('Seleccione un tipo de moneda',5);
		$("#modal_contr_ade_int_moneda_id").focus();
		$('#modal_contr_ade_int_moneda_id').select2('open');
		return false;
	}

	if (monto == "") {
		alertify.error('Ingrese un monto',5);
		$("#modal_contr_ade_int_monto").focus();
		return false;
	}

	if (parseInt(tipo_igv_id) == 0) {
		alertify.error('Seleccione el IGV',5);
		$("#modal_contr_ade_int_tipo_igv_id").focus();
		$('#modal_contr_ade_int_tipo_igv_id').select2('open');
		return false;
	}

	if (subtotal == "") {
		alertify.error('Ingrese un subtotal',5);
		$("#modal_contr_ade_int_subtotal").focus();
		return false;
	}

	if (igv == "") {
		alertify.error('Ingrese un IGV',5);
		$("#modal_contr_ade_int_igv").focus();
		return false;
	}

	if (parseInt(tipo_comprobante) == 0) {
		alertify.error('Seleccione el tipo de comprobante',5);
		$("#modal_contr_ade_int_tipo_comprobante").focus();
		$('#modal_contr_ade_int_tipo_comprobante').select2('open');
		return false;
	}
	
	if (plazo_pago == "") {
		alertify.error('Ingrese un plazo de pago',5);
		$("#modal_contr_ade_int_plazo_pago").focus();
		return false;
	}

	if (forma_pago_detallado == "") {
		alertify.error('Ingrese una forma de pago',5);
		$("#modal_contr_ade_int_forma_pago_detallado").focus();
		return false;
	}


	var accion = 'guardar_adenda_detalle_nuevos_registros';
	
	var data = {
		"accion": accion,
		"tabla": "contraprestacion",
		"contrato_id": contrato_id,
		"moneda_id" : moneda_id,
		"monto" : monto,
		"tipo_igv_id" : tipo_igv_id,
		"subtotal" : subtotal,
		"igv" : igv,
		"forma_pago" : forma_pago,
		"tipo_comprobante" : tipo_comprobante,
		"plazo_pago" : plazo_pago,
		"forma_pago_detallado" : forma_pago_detallado,
	}

	auditoria_send({ "proceso": "guardar_adenda_detalle_nuevos_registros", "data": data });

	$.ajax({
		url: "/sys/set_contrato_detalle_adenda_proveedor.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(response) { //  alert(datat)
			var respuesta = JSON.parse(response);
			auditoria_send({ "respuesta": "guardar_adenda_detalle_nuevos_registros", "data": respuesta });

			if (parseInt(respuesta.http_code) == 200) {
				$('#frm_adenda_nuevo_proveedor')[0].reset();
				let new_adenda = {
					id:parseFloat(respuesta.result),
					type:'nuevo',
					state:1,
				}
				sec_con_detalle_aden_prov_asignar_detalle_a_la_adenda(new_adenda, 'modalNuevoContraprestacion');
			}
			
		},
		error: function() {}
	});
}




function sec_con_detalle_aden_prov_guardar_adenda() {

	var contrato_id = $('#id_registro_contrato_id').val();
	var adenda_id = $('#sec_con_nuevo_adenda_id').val();
	
	var tipo_contrato_id = $('#id_tipo_contrato').val();

	$("#div_modal_adenda_mensaje").hide();

	if (contrato_id == '') {
		$("#div_modal_adenda_mensaje").show();
		$("#modal_adenda_mensaje").html('No se puede guardar la adenda');
	}

	
	if(array_adendas_contrato.length == 0) {
		alertify.error('No hay solicitud de cambio de adenda',5);
		return false;
	}

	var formData = new FormData();

	var archivos = $('input[type="file"]');
	archivos.each(function(index, element) {
		var nombreCampo = $(element).attr('name');
		var archivos = $(element)[0].files;
		for (var i = 0; i < archivos.length; i++) {
			formData.append(nombreCampo + '_' + i, archivos[i]);
		}
	});

	var data = {
		"accion": "guardar_adenda",
		"adenda_id": adenda_id,
		"contrato_id": contrato_id,
		"tipo_contrato_id": tipo_contrato_id,
		"adendas":array_adendas_contrato
	};

	formData.append('accion', 'guardar_adenda');
	formData.append('adenda_id', adenda_id);
	formData.append('contrato_id', contrato_id);
	formData.append('tipo_contrato_id', tipo_contrato_id);
	formData.append('adendas', JSON.stringify(array_adendas_contrato));
	
	auditoria_send({ "proceso": "guardar_adenda", "data": data });
	
	$.ajax({
		url: "/sys/set_contrato_detalle_adenda_proveedor.php",
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






function sec_con_detalle_aden_prov_eliminar_archivo_anexo(archivo_id) {

	var data = {
		"accion": "eliminar_archivo_anexo",
		"archivo_id": archivo_id,
	}
	
	auditoria_send({ "proceso": "eliminar_archivo_anexo", "data": data });

	$.ajax({
		url: "/sys/set_contrato_detalle_adenda_proveedor.php",
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
			auditoria_send({ "respuesta": "eliminar_archivo_anexo", "data": respuesta });
			if (parseInt(respuesta.status) == 200) {
				sec_con_detalle_aden_prov_obtener_adenda_archivos_anexos();
				alertify.success(respuesta.message,5);
				return false;
			}else{
				alertify.error(respuesta.message,5);
				return false;
			}
			
		},
		error: function() {}
	});
}
























