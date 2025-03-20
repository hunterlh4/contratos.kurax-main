$(function () {

    if (sec_id == 'televentas_devoluciones') {
		$("input:radio[name=tls_devol_consultar_cliente_tipo_doc][value=" + 0 + "]").attr('checked', 'checked');
		$("#tls_devol_cliente_tipo_doc").val(null);
		$('#modal_devol_cuenta').select2();
        // console.log("aaa");

		//Monto
		$("#modal_devolucion_monto").on({
			"focus": function (event) {
				$(event.target).select();
				//console.log('focus');
			},
			"blur": function (event) {
				//console.log('keyup');
				if (parseFloat($(event.target).val().replace(/\,/g, '')) > 0) {
					$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
					$(event.target).val(function (index, value) {
						return value.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
					});
				} else {
					$(event.target).val("0.00");
				}
				//onkeyup_deposito_calcular_total();
			}
		});

		/* $('#tls_devol_cliente_tipo_doc').change(function () {
			var tipo_doc = parseInt( $('#tls_devol_cliente_tipo_doc').val() );
			if ( tipo_doc === 0 ) {
				$('#tlv_devol_buscador_btn_consultar').show();
			}else {
				$('#tlv_devol_buscador_btn_consultar').hide();
			}
		}); */

		$('#tlv_devol_buscador_btn_consultar').on('click', function () {
			sec_tlv_devol_val_cliente();
			return false;
		});

		$('#tls_devol_cliente_btn_habilitar').on('click', function () {
			$('#tls_devol_cliente_btn_habilitar').hide();
			$('#tls_devol_cliente_btn_deshabilitar').show();
			habilitar_edicion();
			return false;
		});

		$('#tls_devol_cliente_btn_deshabilitar').on('click', function () {
			$('#tls_devol_cliente_btn_deshabilitar').hide();
			$('#tls_devol_cliente_btn_habilitar').show();
			deshabilitar_edicion();
			return false;
		});

		$('#tls_devol_cliente_btn_limpiar').on('click', function () {
			limpiar_campos_cliente_devoluciones();
			return false;
		});

		$('#tls_devoluciones_btn').on('click', function () {
			var tipo_doc = parseInt($('#tls_devol_cliente_tipo_doc').val());
			var num_doc = $('#tls_devol_cliente_num_doc').val();
			var celular = $('#tls_devol_cliente_celular').val();
			console.log(tipo_doc);

			if ( isNaN(tipo_doc) ) {
				swal('Aviso', 'Registrar un tipo de documento válido.', 'warning');
				$("#tls_devol_cliente_tipo_doc").focus();
				return false;
			}

			if ( tipo_doc === 0 ) {
				if (num_doc.length !== 8) {
					swal('Aviso', 'El número de DNI debe contener 8 dígitos.', 'warning');
					$("#tls_devol_cliente_num_doc").focus();
					return false;
				}
			}

			if (num_doc.length < 8) {
				swal('Aviso', 'Digitar un número de documento válido.', 'warning');
				$("#tls_devol_cliente_num_doc").focus();
				return false;
			}

			if (celular.length !== 9) {
				swal('Aviso', 'Por favor el número de celular debe contener 9 dígitos.', 'warning');
				$("#tls_devol_cliente_celular").focus();
				return false;
			}

			limpiar_modal_devolucion();
			registrar_devolucion();
			return false;
		});

		$('#modal_devolucion_btn_guardar').click(function (event) {
			if (!event.detail || event.detail == 1) {
				$('#modal_devolucion_btn_guardar').hide();
				guardar_devolucion();
			}
			return false;
		});

		$('#modal_devol_supervisor').select2();
		$('#modal_devol_cajero').select2();

		listar_devoluciones();
        
    }
});

function limpiar_campos_cliente_devoluciones(){
	$('#tls_devol_cliente_tipo_doc').val(null).trigger('change');
	$('#tls_devol_cliente_num_doc').val('');
	$('#tls_devol_cliente_nombre').val('');
	$('#tls_devol_cliente_apepaterno').val('');
	$('#tls_devol_cliente_apematerno').val('');
	$('#tls_devol_cliente_celular').val('');
}

function sec_tlv_devol_val_cliente(){

	$("#tls_devol_buscador_lbl_mensaje").html('');
	limpiar_bordes_div_cliente_devolucion();
	limpiar_campos_cliente_devoluciones();
	// limpiar_campos_div_cliente();

	var busc_tipo = $("input:radio[name='tls_devol_consultar_cliente_tipo_doc']:checked").val(); //Obtener valor del radio seleccionado
	var buscador_texto = $.trim($("#tls_devol_consultar_cliente_num_doc").val());

	// DNI
	if (parseInt(busc_tipo) == 0) {
		if (buscador_texto.length !== 8) {
			$("#tls_devol_buscador_lbl_mensaje").html('El número de DNI debe tener 8 dígitos.');
			$("#tls_devol_consultar_cliente_num_doc").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) >= 1 && parseInt(buscador_texto) <= 99999999)) {
			$("#tls_devol_buscador_lbl_mensaje").html('Número de DNI inválido.');
			$("#tls_devol_consultar_cliente_num_doc").focus();
			return false;
		}
	}
	// CE/PTP
	if (parseInt(busc_tipo) == 1) {
		if (buscador_texto.length !== 9) {
			$("#tls_devol_buscador_lbl_mensaje").html('El número de CE/PTP debe tener 9 dígitos.');
			$("#tls_devol_consultar_cliente_num_doc").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) >= 0 && parseInt(buscador_texto) <= 999999999)) {
			$("#tls_devol_buscador_lbl_mensaje").html('Número de CE/PTP inválido.');
			$("#tls_devol_consultar_cliente_num_doc").focus();
			return false;
		}

		$('#tls_devol_cliente_tipo_doc').val(busc_tipo);
		$('#tls_devol_cliente_num_doc').val(buscador_texto);
		swal('Aviso', 'Registre los datos manualmente', 'warning');
		return false
	}

	var data = {
		"accion": "validar_cliente_televentas_devoluciones",
		"tipo": busc_tipo,
		"valor": buscador_texto,
		"hash": tls_devoluciones_hash,
		"timestamp": Date.now()
	}

	$.ajax({
		url: "/sys/set_televentas_devoluciones.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({"proceso": "validar_cliente_televentas_devoluciones", "data": respuesta});
			// console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.mensaje, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {					 
				loading("true");
				$('#tls_devol_cliente_tipo_doc').val(busc_tipo);
				$('#tls_devol_cliente_num_doc').val(buscador_texto);
				$('#tls_devol_cliente_nombre').val(respuesta.val_nombres);
				$('#tls_devol_cliente_apepaterno').val(respuesta.val_apellido_paterno);
				$('#tls_devol_cliente_apematerno').val(respuesta.val_apellido_materno);
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "validar_cliente_televentas_devoluciones_error", "data": result});
			return false;
		}
	});
	

}


function limpiar_bordes_div_cliente_devolucion() {
	$('#tls_devol_cliente_tipo_doc').css('border', '');
	$('#tls_devol_cliente_num_doc').css('border', '');
	$('#tls_devol_cliente_celular').css('border', '');
	$('#tls_devol_cliente_nombre').css('border', '');
	$('#tls_devol_cliente_apepaterno').css('border', '');
	$('#tls_devol_cliente_apematerno').css('border', '');
}

function limpiar_campos_div_cliente() {
	$('#tls_devol_cliente_tipo_doc').val('');
	$('#tls_devol_cliente_num_doc').val('');
	$('#tls_devol_cliente_celular').val('');
	$('#tls_devol_cliente_nombre').val('');
	$('#tls_devol_cliente_apepaterno').val('');
	$('#tls_devol_cliente_apematerno').val('');
}

function habilitar_edicion() {
	$("#tls_devol_cliente_tipo_doc").prop('disabled', false);
	$('#tls_devol_cliente_num_doc').prop('disabled', false);
	$('#tls_devol_cliente_celular').prop('disabled', false);
	$('#tls_devol_cliente_nombre').prop('disabled', false);
	$('#tls_devol_cliente_apepaterno').prop('disabled', false);
	$('#tls_devol_cliente_apematerno').prop('disabled', false);
}

function deshabilitar_edicion() {
	$("#tls_devol_cliente_tipo_doc").prop('disabled', true);
	$('#tls_devol_cliente_num_doc').prop('disabled', true);
	$('#tls_devol_cliente_celular').prop('disabled', true);
	$('#tls_devol_cliente_nombre').prop('disabled', true);
	$('#tls_devol_cliente_apepaterno').prop('disabled', true);
	$('#tls_devol_cliente_apematerno').prop('disabled', true);
}

function registrar_devolucion() {
	// sec_tlv_cargar_cuentas_televentas();
	sec_televentas_devoluciones_obtener_opciones("listar_supervisores_tlv", $("[name='modal_devol_supervisor']"));
	sec_televentas_devoluciones_obtener_opciones("listar_cajeros_tlv", $("[name='modal_devol_cajero']"));
	$("#modal_devolucion").modal();
	$('#modal_devolucion_btn_guardar').show();
	return false;
}

/* function sec_tlv_cargar_cuentas_televentas(){
	var data = {
		"accion": "obtener_televentas_cuentas_deposito_devoluciones"
	}

	$.ajax({
		url: "/sys/set_televentas_devoluciones.php",
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
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.status, 'warning');
				return false;
			}
		 
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_devol_cuenta').html('');
				$('#modal_devol_cuenta').append('<option value="0" bono="0" comision_monto="0" is_yape="0">:: Seleccione ::</option>');
				$.each(respuesta.result, function(index, item){
					$('#modal_devol_cuenta').append(
						'<option value="' + item.id + '" bono="' + item.bono + '" is_yape="' + item.valid_cuenta_yape + '"' +
						'	style="color:' + item.foreground + '; background: ' + item.background + '">' +
						'	<span style="font-size:20px">' + item.cuenta_descripcion + '</span>' +
						'</option>'
					);
				});
				$('#modal_devol_cuenta').select2({
					dropdownCssClass: 'ui-widget ui-jqdialog zclass',
					templateResult: (state) => {
						if (!state?.element?.style?.background) {
							return state.text;
						}
						const background = state.element.style.background;
						const foreground = state.element.style.color;

						const replacement = $(`<span style="color: ${foreground}; background:${background}; display: block; padding: 5px;">${state.text}</span>`);
						return replacement;
					}
				});
				return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_televentas_cuentas_deposito_error", "data": result});
		}
	});
} */

$("#modal_devolucion_voucher").change(function (e) {
    var filePath = this.value;
    var allowedExtensions = /(.jpg|.jpeg|.png|.gif)$/i;
    if(!allowedExtensions.exec(filePath)){
        swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
        this.value = '';
        return false;
    }else{
        var newPath = filePath.replace('jpg','png');
        readImageDep_devolucion(this);
    }

});

function readImageDep_devolucion (input) {
	$('#modal_devolucion_imagen_preview').html('');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
        	$('#modal_devolucion_imagen_preview').append(
                '<div class="col-md-12">' +
                '   <div align="center" style="height: 100%;width: 100%;">' +
                '       <img  id="sec_tlv_ver_imagen_devolucion" src="' + e.target.result + '" width="500px" />' +
                '   </div>' +
                '</div>'
            );
            $("#sec_tlv_ver_imagen_devolucion").imgViewer2();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function limpiar_modal_devolucion(){
	$('#modal_devolucion_imagen_preview').html('');
	$('#modal_devol_cuenta').val(0).trigger('change');
    $('#modal_devolucion_banco').val(0).trigger('change');
	$('#modal_devolucion_num_cuenta_cliente').val('');
	$('#modal_devolucion_cci_cliente').val('');
	$('#modal_devol_cajero').val('');
	$('#modal_devol_supervisor').val('');
    $('#modal_devolucion_motivo').val(0).trigger('change');
	$('#modal_devolucion_monto').val('');
	$('#modal_devolucion_link_callbell').val('');
	$('#modal_devolucion_observacion').val('');
	$('#modal_devolucion_voucher').removeAttr('src');
	$('#modal_devolucion_voucher').val('');
}

function guardar_devolucion() {
	$('#modal_devolucion_btn_guardar').hide();
	limpiar_bordes_modal_devolucion();

	var cuenta_at = $('#modal_devol_cuenta').val();
	var banco_cliente = $('#modal_devolucion_banco').val();
	var num_cuenta_cliente = $.trim($('#modal_devolucion_num_cuenta_cliente').val());
	var cci_cliente = $.trim($('#modal_devolucion_cci_cliente').val());
	var cajero = $('#modal_devol_cajero').val();
	var supervisor = $('#modal_devol_supervisor').val();
	var motivo = $('#modal_devolucion_motivo').val();
	var monto = $('#modal_devolucion_monto').val().replace(/\,/g, '');
	// var fecha_devolucion = $('#modal_devolucion_fecha').val();
	var link_callbell = $.trim($('textarea#modal_devolucion_link_callbell').val());
	var observacion = $.trim($('textarea#modal_devolucion_observacion').val());

	var tipo_doc = $('#tls_devol_cliente_tipo_doc').val();
	var num_doc = $('#tls_devol_cliente_num_doc').val();
	var celular = $('#tls_devol_cliente_celular').val();
	var cliente_nombre = $('#tls_devol_cliente_nombre').val();
	var cliente_apepaterno = $('#tls_devol_cliente_apepaterno').val();
	var cliente_apematerno = $('#tls_devol_cliente_apematerno').val();

	var imagen = $('#modal_devolucion_voucher').val();
	var f_imagen = $("#modal_devolucion_voucher")[0].files[0];
	var imagen_extension = imagen.substring(imagen.lastIndexOf("."));

	if (!(imagen.length > 0)) {
		$("#modal_devolucion_voucher_div").css("border", "1px solid red");
		swal('Aviso', 'Agregue una imágen.', 'warning');
		$('#modal_devolucion_btn_guardar').show();
		return false;
	}
	if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
			imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
			imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG") {
		$("#modal_devolucion_voucher_div").css("border", "1px solid red");
		swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
		$('#modal_devolucion_btn_guardar').show();
		return false;
	}
	if (!(parseInt(cuenta_at) > 0)) {
		$('#modal_devol_cuenta_div').css('border', '1px solid red');
		$('#modal_devol_cuenta').focus();
		$('#modal_devolucion_btn_guardar').show();
		swal('Aviso', 'Registre una Cuenta AT', 'warning');
		return false;
	}
	if(!parseInt(banco_cliente) > 0){
		$('#modal_devolucion_banco_div').css('border', '1px solid red');
		$('#modal_devolucion_banco').focus();
		$('#modal_devolucion_btn_guardar').show();
		swal('Aviso', 'Registre una Banco del cliente', 'warning');
		return false;
	}
	if (!(num_cuenta_cliente.length > 0)) {
		$("#modal_devolucion_num_cuenta_cliente_div").css("border", "1px solid red");
		swal('Aviso', 'Registre un número de cuenta del cliente que sea válido.', 'warning');
		$('#modal_devolucion_btn_guardar').show();
		return false;
	}
	if (!(cci_cliente.length > 0)) {
		$("#modal_devolucion_cci_cliente_div").css("border", "1px solid red");
		swal('Aviso', 'Registre un número CCI del cliente que sea válido.', 'warning');
		$('#modal_devolucion_btn_guardar').show();
		return false;
	}
	if (!parseInt(cajero) > 0) {
		$("#modal_devol_cajero_div").css("border", "1px solid red");
		swal('Aviso', 'Registre un cajero.', 'warning');
		$('#modal_devolucion_btn_guardar').show();
		return false;
	}
	if (!parseInt(supervisor) > 0) {
		$("#modal_devol_supervisor_div").css("border", "1px solid red");
		swal('Aviso', 'Registre un supervisor.', 'warning');
		$('#modal_devolucion_btn_guardar').show();
		return false;
	}
	if (!(parseFloat(motivo) > 0)) {
		$('#modal_devolucion_motivo_div').css('border', '1px solid red');
		$('#modal_devolucion_motivo').focus();
		swal('Aviso', 'Registre un motivo.', 'warning');
		$('#modal_devolucion_btn_guardar').show();
		return false;
	}
	if (!(parseFloat(monto) > 0)) {
		$('#modal_devolucion_monto_div').css('border', '1px solid red');
		$('#modal_devolucion_monto').focus();
		swal('Aviso', 'Registre un monto válido.', 'warning');
		$('#modal_devolucion_btn_guardar').show();
		return false;
	}
	if (!(link_callbell.length > 0)) {
		$('#modal_devolucion_link_callbell_div').css('border', '1px solid red');
		$('#modal_devolucion_link_callbell').focus();
		swal('Aviso', 'Registre un link válido.', 'warning');
		$('#modal_devolucion_btn_guardar').show();
		return false;
	}

	var data = new FormData();
	data.append('accion', "guardar_transaccion_devolucion");
	data.append('imagen_voucher', f_imagen);
	data.append('cuenta_at', cuenta_at);
	data.append('banco_cliente', banco_cliente);
	data.append('num_cuenta_cliente', num_cuenta_cliente);
	data.append('cci_cliente', cci_cliente);
	data.append('cajero', cajero);
	data.append('supervisor', supervisor);
	data.append('motivo', motivo);
	data.append('monto', monto);
	// data.append('fecha_devolucion', fecha_devolucion);
	data.append('link_callbell', link_callbell);
	data.append('observacion', observacion);

	data.append('tipo_doc', tipo_doc);
	data.append('num_doc', num_doc);
	data.append('celular', celular);
	data.append('cliente_nombre', cliente_nombre);
	data.append('cliente_apepaterno', cliente_apepaterno);
	data.append('cliente_apematerno', cliente_apematerno);
	
	guardar_devolucion_confirmar(data);
	return false;

}
function guardar_devolucion_confirmar(data) {
	$.ajax({
		url: "/sys/set_televentas_devoluciones.php",
		type: 'POST',
		data: data,
		processData: false,
		cache: false,
		contentType: false,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({"proceso": "guardar_transaccion_devolucion", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_devolucion').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				$('#modal_devolucion_btn_guardar').show();
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_devolucion').modal('hide');
				$('#sec_tlv_modal_valid_yape').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_devoluciones();
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "guardar_transaccion_devolucion_error", "data": result});
			return false;
		}
	});
	return false;
}

function limpiar_bordes_modal_devolucion() {
	$('#modal_devolucion_voucher_div').css('border', '');
	$('#modal_devol_cuenta_div').css('border', '');
	$('#modal_devolucion_banco_div').css('border', '');
	$('#modal_devolucion_num_cuenta_cliente_div').css('border', '');
	$('#modal_devolucion_cci_cliente_div').css('border', '');
	$('#modal_devol_cajero_div').css('border', '');
	$('#modal_devol_supervisor_div').css('border', '');
	$('#modal_devolucion_motivo_div').css('border', '');
	$('#modal_devolucion_monto_div').css('border', '');
	$('#modal_devolucion_link_callbell_div').css('border', '');
	$('#modal_devolucion_cci_cliente_div').css('border', '');
}

function limpiar_tabla_devoluciones() {
    $('#tabla_devoluciones').html(
        '<thead>' +
    	'   <tr>' +
        '       <th class="text-center" style="display:none;">Id</th>' +
        '       <th class="text-center">Fecha Devolución</th>' +
        '       <th class="text-center">N° Documento</th>' +
        '       <th class="text-center">Nombres</th>' +
        '       <th class="text-center">Apellidos</th>' +
        '       <th class="text-center">Celular</th>' +
        '       <th class="text-center">Cuenta AT</th>' +
        '       <th class="text-center">Monto</th>' +
        '       <th class="text-center">Usuario</th>' +
        '       <th class="text-center">Acciones</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
}

function listar_devoluciones() {

	var data = new FormData();
	data.append('accion', "listar_devoluciones");
	limpiar_tabla_devoluciones();

	$.ajax({
		url: "/sys/set_televentas_devoluciones.php",
		type: 'POST',
		data: data,
		processData: false,
		cache: false,
		contentType: false,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({"proceso": "guardar_transaccion_devolucion", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
                $('#tabla_devoluciones tbody').append(
                    '<tr>' +
                    '<td class="text-center" colspan="9">'+ respuesta.status +'</td>' +
                    '</tr>'
                );
                return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
                limpiar_tabla_devoluciones();
                $.each(respuesta.result, function(index, item) {
					
					if ( parseInt(item.tipo_doc)===0 ) {
						item.tipo_doc = "DNI";
					} else {
						item.tipo_doc = "CE/PTP";
					}

					var variables = "'" + item.id + "','" + item.tipo_doc + "','" + item.num_doc + "','" + item.nombres + "','" + item.apellidos + "','" +
										item.celular + "','" + item.banco_at + "','" + item.cuenta_at + "','" + item.banco_cliente + "','" +
										item.cuenta_cliente + "','" + item.cci + "','" +  item.name_file + "','" +
										item.monto + "','" + item.motivo_id + "','" + item.id_cajero + "','" + item.supervisor + "','" +
										item.link_call_bell + "','" + item.observacion + "','" + item.created_at + "'";
					var btn_ver = "";
					var btn_eliminar = "";

                    var btn_ver = '<button type="button" class="btn btn-primary" style="padding: 2px 3px;"' +
					'    onclick="sec_tlv_devolu_ver_detalle(' + variables + ')">' +
					'<span class="fa fa-eye"></span>' +
					'</button>';
					btn_eliminar = ' <button type="button" class="btn btn-danger" style="padding: 2px 3px;" onclick="eliminar_devolucion( ';
					btn_eliminar += " '" + item.id + "' ";
					btn_eliminar += ' )"><span class="fa fa-times"></span></button> ';

                    item.monto = item.monto.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                    $('#tabla_devoluciones tbody').append(
                        '<tr id="'+item.id+'" class="sec_tlv_pag_listado_transaccion">' +
                            '<td style="display:none;" class="sec_tlv_pag_listado_transaccion_id_transaccion">' + item.id + '</td>' +
                            '<td class="text-center">' + item.created_at + '</td>' +
                            '<td class="text-center">' + item.num_doc + '</td>' +
                            '<td class="text-center">' + item.nombres + '</td>' +
                            '<td class="text-center">' + item.apellidos + '</td>' +
                            '<td class="text-center">' + item.celular + '</td>' +
                            '<td class="text-center">' + item.cuenta_at + '</td>' +
                            '<td class="text-center"> S/ ' + item.monto + '</td>' +
                            '<td class="text-center">' + item.usuario + '</td>' +
                            '<td class="text-center">' + btn_ver + btn_eliminar + '</td>' +
                        '</tr>'
                    );
                });
				
				tabla_devoluciones_datatable_formato_tlv_pag('#tabla_devoluciones');
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "guardar_transaccion_devolucion_error", "data": result});
			return false;
		}
	});
	return false;
}



function eliminar_devolucion(devolucion_id){

    swal({
        title: `<h3>¿Estás seguro de eliminar la devolución?</h3>`,
        text: 'Esta acción es irreversible',
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: false,
        closeOnCancel: false,
        html: true,
        // customClass: "sweet_alert_wide",
    },
    function (opt) {
        if (opt) {
            // console.log("SISISI");
            // Llamado AJAX
            var data = new FormData();
            data.append('accion', "eliminar_devolucion");
            data.append('devolucion_id', devolucion_id);

            $.ajax({
                url: "/sys/set_televentas_devoluciones.php",
                type: 'POST',
                data: data,
                processData: false,
                cache: false,
                contentType: false,
                beforeSend: function () {
                    loading("true");
                },
                complete: function () {
                    loading();
                },
                success: function (resp) { //  alert(datat)
                    var respuesta = JSON.parse(resp);
                    auditoria_send({"respuesta": "eliminar_devolucion", "data": respuesta});
                    console.log(respuesta);
                    if (parseInt(respuesta.http_code) == 400) {
                        swal('Aviso', respuesta.status, 'warning');
						listar_devoluciones();
                        return false;
                    }
                    if (parseInt(respuesta.http_code) == 200) {
                        swal('Aviso', 'Su transacción de devolución a sido eliminada.', 'success');
						listar_devoluciones();
						return false;
                    }
                    return false;
                },
                error: function (result) {
                    auditoria_send({"respuesta": "eliminar_devolucion_ERROR", "data": result});
                    return false;
                }
            });
    
            swal.close();
            loading(false);
            return false;

        } else {
            swal({
                    title: "Estuvo cerca!",
                    text: "Tu registro está a salvo!",
                    type: "success",
                    timer: 1000,
                    closeOnConfirm: true
                },
                function (opt) {
                    swal.close();
                });
        }
    });
}

function tabla_devoluciones_datatable_formato_tlv_pag(id) {
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
        
    }
    $(id).DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'info': true,
        'autoWidth': false,
        "language": {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "infoPostFix": "",
            "search": "Buscar: ",
            "url": "",
            "infoThousands": ",",
            "loadingRecords": "Cargando...",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
}

function sec_televentas_devoluciones_obtener_opciones(accion, select) {
	$.ajax({
	  url: "/sys/set_televentas_devoluciones.php",
	  type: "POST",
	  data: { accion: accion }, //+data,
	  beforeSend: function () {},
	  complete: function () {},
	  success: function (datos) {
		//  alert(datat)
		var respuesta = JSON.parse(datos);
		$(select).find("option").remove().end();
		$(select).attr('disabled', false);
		if(accion == 'listar_supervisores_tlv'){
			if(respuesta.supervisor == 0){
				$(select).append('<option value="0">:: Seleccione ::</option>');
			}else if(respuesta.supervisor == 1){
				$(select).attr('disabled', true);
			}
		}else if ( accion == 'listar_cajeros_tlv' ) {
			$(select).append('<option value="0">:: Seleccione ::</option>');
		}
		$(respuesta.result).each(function (i, e) {
		  opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
		  $(select).append(opcion);
		});
	  },
	  error: function () {},
	});
}

function limpiar_modal_detalle() {
	$('#modal_devol_tipo_doc').val('');
	$('#modal_devol_num_doc').val('');
	$('#modal_devol_nombres').val('');
	$('#modal_devol_apellidos').val('');
	$('#modal_devol_cel').val('');
	$('#modal_devol_bancoat').val('');
	$('#modal_devol_cuentaat').val('');
	$('#modal_devol_bancocliente').val('');
	$('#modal_devol_cuentacliente').val('');
	$('#modal_devol_cci').val('');
	$('#modal_devol_monto').val('');
	$('#modal_devol_motivo').val('');
	$('#modal_devol_cajero').val('');
	$('#modal_devol_supervisor').val('');
	$('#modal_devol_link').val('');
	$('#modal_devol_obs').val('');
	$('#modal_devol_fecha').val('');
	$('#modal_devol_img').removeAttr('src');
}

function sec_tlv_devolu_ver_detalle(id, tipo_doc, num_doc, nombres, apellidos, celular, banco_at, cuenta_at, banco_cliente, cuenta_cliente, cci, name_file, monto,
	motivo_id, id_cajero, supervisor, link_call_bell, observacion, created_at){

	limpiar_modal_detalle();

	$('#modal_devol_tipo_doc').html(tipo_doc);
	$('#modal_devol_num_doc').html(num_doc);
	$('#modal_devol_nombres').html(nombres);
	$('#modal_devol_apellidos').html(apellidos);
	$('#modal_devol_cel').html(celular);
	$('#modal_devol_bancoat').html(banco_at);
	$('#modal_devol_cuentaat').html(cuenta_at);
	$('#modal_devol_bancocliente').html(banco_cliente);
	$('#modal_devol_cuentacliente').html(cuenta_cliente);
	$('#modal_devol_cci').html(cci);
	$('#modal_devol_monto').html("S/. "+monto);
	$('#modal_devol_motivo').html(motivo_id);
	$('#modal_devol_cajero_detalle').html(id_cajero);
	$('#modal_devol_supervisor_detalle').html(supervisor);
	$('#modal_devol_link').val(link_call_bell);
	$('#modal_devol_obs').val(observacion);
	$('#modal_devol_fecha').html(created_at);
    $('#modal_devol_img').attr('src', 'files_bucket/depositos/' + name_file);

    $('#modal_devolucion_detalle').modal();

}