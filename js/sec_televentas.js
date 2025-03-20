var var_fecha_hora = '';
var countdownTimer_sec_televentas;
var list_cli_dup = [];
var cli_dup_select = '';
var limite_cli_total = 0;
var max_aten_total = 0;
var permiso_fusion =0;
var tls_version = 2.91;
var fec_nac_f = '';
var cookie_session ='';
let isTabActive = true;
let isUserActive = true;
let inactivityTimeout;

function sec_televentas() {

	
	//$("input:radio[name='buscador_tipo']").val(9);
	if (sec_id === 'televentas' || sec_id === 'retail_altenar') {

		if(parseFloat(tls_version)<parseFloat(tls_version_actual)){
			alertify.error('El sistema ha sido actualizado. Ctrl+F5 (Si estás en PC) o Ctrl+Tecla Función +F5 (Si estás en laptop) o contactar con el área de soporte..', 180);
		}
		if(sec_id === 'retail_altenar'){
			$("input:radio[name=buscador_tipo][value=" + 1 + "]").attr('checked', 'checked');
		}else{
			$("input:radio[name=buscador_tipo][value=" + 0 + "]").attr('checked', 'checked');
		}
		
		obtener_fecha_hora();


		function startTimer() {
            if (isTabActive && isUserActive) {
                if (countdownTimer_sec_televentas) {
                    clearInterval(countdownTimer_sec_televentas);
                }
				countdownTimer_sec_televentas = setInterval(obtener_transacciones_nuevas, 3000);
            }
        }
        
        function stopTimer() {
            if (countdownTimer_sec_televentas) {
                clearInterval(countdownTimer_sec_televentas);
                countdownTimer_sec_televentas = null;
            }
        }
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimeout);
            isUserActive = true;
            startTimer();
            
            inactivityTimeout = setTimeout(() => {
                isUserActive = false;
                stopTimer();
            }, 30000);
        }
        
        document.addEventListener("visibilitychange", function () {
            if (document.hidden) {
                isTabActive = false;
                stopTimer();
            } else {
                console.log("activo")
                isTabActive = true;
                startTimer()
                resetInactivityTimer();
            }
        });
        
        document.addEventListener("mousemove", resetInactivityTimer);
        document.addEventListener("keydown", resetInactivityTimer);
        
        if (isTabActive && isUserActive) {
            startTimer();
        }  

		
		//console.log(permiso_eliminar);
		if (window.Notification && Notification.permission !== "denied") {
			Notification.requestPermission((status) => {
			});
		}
		localStorage.removeItem("listNew");
		$('#motivo_del_dep').select2();

		$('#modal_editBal_motivo_balance_id').select2();
		$('#modal_editBal_juego_motivo_balance').select2();
		$('#modal_editBal_supervisor_motivo_balance').select2();
		$('#modal_editBal_cajero_motivo_balance').select2();
		$('#modal_etiqueta_agregar_select_tipo').select2();


		$('#sec_tlv_cliente_nacionalidad').select2();
		$('#sec_tlv_cliente_departamento').select2();
		$('#sec_tlv_cliente_provincia').select2();
		$('#sec_tlv_cliente_distrito').select2();

		
		//buscar_limite_cajero();
		var fecha_actual = new Date();
		var year_actual = fecha_actual. getFullYear();
		var year_limite = '1900:';
 		var rango_year = year_limite + year_actual;
		
		$(".fecha_nac_datepicker_tlv")
		.datepicker({
		dateFormat: "yy-mm-dd",
		changeMonth: true,
		changeYear: true,
		yearRange: rango_year
		})
		.on("change", function (ev) {
		$(this).datepicker("hide");
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
		// localStorage.setItem($(this).atrr("id"),)
		});

		setTimeout(function() {
			let consultar_voucher_sin_envio_cada = parseInt(num_minutos_consultar_voucher_sin_envio) * 60 * 1000;
			if(!(consultar_voucher_sin_envio_cada > 1000)) {
				consultar_voucher_sin_envio_cada = 10 * 60 * 1000;
			}
			if(anuncios_cargo_id == 5 || administrar_vouchers_sin_enviar == 1){
				sec_tlv_consultar_numero_comprobantes_de_pago_sin_notificar();
				setInterval(sec_tlv_consultar_numero_comprobantes_de_pago_sin_notificar, consultar_voucher_sin_envio_cada);
			}
			sec_tlv_consultar_retiros_pagados_solicitados_por_mi();
			setInterval(sec_tlv_consultar_retiros_pagados_solicitados_por_mi, consultar_voucher_sin_envio_cada);
		}, 500);
	}
}

function invertColor(hex) {
	if (hex.indexOf('#') === 0) {
		hex = hex.slice(1);
	}
	// convert 3-digit hex to 6-digits.
	if (hex.length === 3) {
		hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
	}
	if (hex.length !== 6) {
		throw new Error('Invalid HEX color.');
	}
	var r = parseInt(hex.slice(0, 2), 16),
			g = parseInt(hex.slice(2, 4), 16),
			b = parseInt(hex.slice(4, 6), 16);

	// http://stackoverflow.com/a/3943023/112731
	return (r * 0.299 + g * 0.587 + b * 0.114) > 186
			? '#000000'
			: '#FFFFFF';
}


window.addEventListener("message",(msg)=>{
    if (msg.data!==undefined && msg.data.type!==undefined && msg.data.type==='placebet'){
        $.each((msg.data.placeBet), function(index, item) {
        	// portal_calimaco_guardar_apuesta_2(item.betId, item.totalStake, JSON.stringify(msg.data.placeBet));
        	portal_calimaco_guardar_apuesta_log(item.betId, item.totalStake, JSON.stringify(msg.data.placeBet));
        });
    }
    if (msg.data!==undefined && msg.data.type!==undefined && msg.data.type==='cancelBet'){
        $('textarea#modal_os_observacion').val('Eliminado desde el Sportsbook');
        eliminar_transaccion(msg.data.cancelBetData.BetId, 4, 5);
    }
});


$(function () {

	if (sec_id === 'televentas' || sec_id === 'retail_altenar') {


		$('#div_sec_televentas_fecha_inicio').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#div_sec_televentas_fecha_fin').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#div_sec_televentas_fecha_inicio').val($('#g_fecha_hace_7_dias').val());
		$('#div_sec_televentas_fecha_fin').val($('#g_fecha_actual').val());

		$('#cliente_local').select2();
	
        //$('.tls_balance_dinero_at').hide();
        //$('.tls_balance_real').show();

		$('#div_sec_televentas_fecha_inicio').change(function () {
			var var_fecha_change = $('#div_sec_televentas_fecha_inicio').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#div_sec_televentas_fecha_inicio").val($("#g_fecha_hace_7_dias").val());
			}
		});
		$('#div_sec_televentas_fecha_fin').change(function () {
			var var_fecha_change = $('#div_sec_televentas_fecha_fin').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#div_sec_televentas_fecha_fin").val($("#g_fecha_actual").val());
			}
		});

		$('input:radio[name=buscador_tipo]').on('change', function () {
			setTimeout(function () {
				$("#buscador_lbl_mensaje").html('');
				$("#buscador_texto").val(''); // Limpiar input
				$("#buscador_texto").removeAttr('maxLength');

				var busc_tipo = $("input:radio[name='buscador_tipo']:checked").val(); //Obtener valor del radio seleccionado
				// Celular
				if (parseInt(busc_tipo) == 9) {
					$("#buscador_texto").attr('maxLength', '9');
				}
				// DNI
				if (parseInt(busc_tipo) == 0) {
					$("#buscador_texto").attr('maxLength', '8');
				}
				// CE/PTP
				if (parseInt(busc_tipo) == 1) {
					if(sec_id === 'retail_altenar'){
						$("#buscador_texto").attr('minLength', '4');
						$("#buscador_texto").attr('maxLength', '4');
					}else{
						$("#buscador_texto").attr('maxLength', '9');
					}
					
				}
				// PASAPORTE
				if (parseInt(busc_tipo) == 2) {
					$("#buscador_texto").attr('maxLength', '10');
				}
				// ID-WEB
				if (parseInt(busc_tipo) == 8) {
					$("#buscador_texto").attr('maxLength', '12');
				}

				$("#buscador_texto").focus(); // Dar foco al input
				return false;
			}, 100);
		});

		$('#form_consultar').on('submit', function () {
			//verificar_limite_aten();
			sec_tlv_val_cliente();
			return false;
		});

		$('#form_fusionar').on('submit', function () {
			sec_tlv_verificar_datos_fusion_clientes();
			return false;
		});


		$('#cliente_btn_habilitar').click(function () {
			desbloquear_div_cliente();
		});

		$('#div_cliente_btn_cancelar').click(function () {
			bloquear_div_cliente();
		});

		$('#btn_rollover_completo').click(function () {
			transferir_saldo_promocional_consultar();
		});

		modal_propina_obtener_cuentas();

		$('#modal_apuesta_registrar_idbet,#modal_apuesta_pagar_idbet').keyup(function() {
			this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
			this.value = this.value.replace(/\s+/g, '');
		});

		$('#modal_pago_kurax_ticket').keyup(function() {
			this.value = this.value.replace(/[^0-9.]/g, '');
			this.value = this.value.replace(/\s+/g, '');
		});

		$('#modal_imagen_perfil_imagen_btn_guardar').click(function() {
			sec_tlv_guardar_imagen_perfil();
			return false;
		});

		var sec_tlv_cliente_imagen_nombre_buscar_typing_timer;
		var sec_tlv_cliente_imagen_nombre_buscar_busqueda_anterior = '';
		var sec_tlv_cliente_imagen_nombre_buscar_nueva_busqueda = '';

		$("#cliente_imagen_nombre_buscar").on("input", function() {
			clearTimeout(sec_tlv_cliente_imagen_nombre_buscar_typing_timer);
			sec_tlv_cliente_imagen_nombre_buscar_typing_timer = setTimeout(function() {
				sec_tlv_cliente_imagen_nombre_buscar_nueva_busqueda = $("#cliente_imagen_nombre_buscar").val().trim();
				if (sec_tlv_cliente_imagen_nombre_buscar_nueva_busqueda !== sec_tlv_cliente_imagen_nombre_buscar_busqueda_anterior) {
					sec_tlv_listar_imagenes_cliente($("#cliente_imagen_nombre_buscar").val());
					sec_tlv_cliente_imagen_nombre_buscar_busqueda_anterior = sec_tlv_cliente_imagen_nombre_buscar_nueva_busqueda;
				}
			}, 800);
		});
	}

});

//var array_clientes=Array();
var array_clientes = [];

function sec_tlv_val_cliente(){
	$("#buscador_lbl_mensaje").html('');

	var busc_tipo = $("input:radio[name='buscador_tipo']:checked").val();
	var buscador_texto = $.trim($("#buscador_texto").val());

	var bca = "0_" + buscador_texto;
	$('#cliente_tercero_titular').empty();
	$('#cliente_tercero_titular').attr('disabled', 'disabled');
	remover_cliente_reabierto(bca);

	// Celular
	if (parseInt(busc_tipo) == 9) {
		if (buscador_texto.length !== 9) {
			$("#buscador_lbl_mensaje").html('El número de celular debe tener 9 dígitos.');
			$("#buscador_texto").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) > 900000000 && parseInt(buscador_texto) <= 999999999)) {
			$("#buscador_lbl_mensaje").html('Número de celular inválido.');
			$("#buscador_texto").focus();
			return false;
		}
	}
	// DNI
	if (parseInt(busc_tipo) == 0) {
		if (buscador_texto.length !== 8) {
			$("#buscador_lbl_mensaje").html('El número de DNI debe tener 8 dígitos.');
			$("#buscador_texto").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) >= 1 && parseInt(buscador_texto) <= 99999999)) {
			$("#buscador_lbl_mensaje").html('Número de DNI inválido.');
			$("#buscador_texto").focus();
			return false;
		}
	}
	// CE/PTP
	if (parseInt(busc_tipo) == 1) {
		if(sec_id === 'retail_altenar'){
			if (buscador_texto.length != 4) {
				$("#buscador_lbl_mensaje").html('El número de Centro de Costos debe tener 4 dígitos.');
				$("#buscador_texto").focus();
				return false;
			}
		}else{
			if (buscador_texto.length < 9) {
				$("#buscador_lbl_mensaje").html('El número de CE/PTP debe al menos 9 dígitos.');
				$("#buscador_texto").focus();
				return false;
			}
			if (!(parseInt(buscador_texto) >= 0 && parseInt(buscador_texto) <= 999999999)) {
				$("#buscador_lbl_mensaje").html('Número de CE/PTP inválido.');
				$("#buscador_texto").focus();
				return false;
			}
		}
	}
	// PASAPORTE
	if (parseInt(busc_tipo) == 2) {
		if (!(buscador_texto.length > 0)) {
			$("#buscador_lbl_mensaje").html('El número de PASAPORTE no debe estar vacío.');
			$("#buscador_texto").focus();
			return false;
		}
	}
	// ID-WEB
	if (parseInt(busc_tipo) == 8) {
		if (!(buscador_texto.length > 0)) {
			$("#buscador_lbl_mensaje").html('El número de ID-WEB no debe estar vacío.');
			$("#buscador_texto").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) > 0)) {
			$("#buscador_lbl_mensaje").html('ID-WEB inválido.');
			$("#buscador_texto").focus();
			return false;
		}
	}

	var data = {
		"accion": "validar_cliente_televentas",
		"tipo": busc_tipo,
		"valor": buscador_texto,
		"hash": tls_hash,
		"timestamp": Date.now()
	}

	var buscador_cliente_id = 0;

	if (array_clientes.length > 0) {
		$.each(array_clientes, function (index, item) {
			//CELULAR
			if (parseInt(busc_tipo) === 9 && parseInt(item.telefono) === parseInt(buscador_texto)) {
				buscador_cliente_id = parseInt(item.id);
			}
			//WEB-ID
			if (parseInt(busc_tipo) === 8 && parseInt(item.web_id) === parseInt(buscador_texto)) {
				buscador_cliente_id = parseInt(item.id);
			}
			//DOC.IDENTIDAD
			if (parseInt(busc_tipo) >= 0 && parseInt(busc_tipo) <= 2) {
				if (parseInt(item.tipo_doc) === parseInt(busc_tipo) && parseInt(item.num_doc) === parseInt(buscador_texto)) {
					buscador_cliente_id = parseInt(item.id);
				}
			}
		});
	}

	if (parseInt(buscador_cliente_id) === 0) {
		$.ajax({
			url: "/sys/set_televentas.php",
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
				auditoria_send({"proceso": "validar_cliente_televentas", "data": respuesta});
				if (parseInt(respuesta.http_code) == 400) {
					if (parseInt(busc_tipo) == 0) {
						cargar_img_captcha(buscador_texto, 0);
					}else if (parseInt(busc_tipo) == 9) {
						swal({
							title: "Cliente no encontrado con número de celular ingresado. Intente con otra opción.",
							type: 'info',
							timer: 5000,
							showConfirmButton: true
							});
							return false; 
					}else{
						sec_tlv_reg_cliente();
					}

				}
				if (parseInt(respuesta.http_code) == 405) {
					//swal('Aviso', respuesta.result, 'warning');
					//console.log(respuesta.result2);
					//console.log(respuesta.result);
					swal({
						html: true,
						title: 'El cliente ' + respuesta.result.nombre + ' esta siendo atendido por el cajero ' + respuesta.result2.usuario_nombres +
								'<br><span style="font-size:20px; font-weight:bold;" >Última consulta: ' + respuesta.result.updated_at + '.</span>',
						text: "¿Desea atender al cliente?",
						type: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#0336FF',
						cancelButtonColor: '#d33',
						confirmButtonText: 'SI, ATENDERLO',
						cancelButtonText: 'CANCELAR',
						closeOnConfirm: false,
						//,showLoaderOnConfirm: true
					}, function () {

						$.post("/sys/set_televentas.php", {
							accion: "bloquear_cliente",
							cliente_id: respuesta.result.id,
							hash: tls_hash
						})
								.done(function (data2) {
									try {
										var respuesta2 = JSON.parse(data2);
										if (parseInt(respuesta2.http_code) == 400) {
											swal({
												title: "Error",
												text: respuesta2.status,
												type: 'danger',
												timer: 1500,
												showConfirmButton: false
											});
											return false;
										}
										if (parseInt(respuesta2.http_code) == 200) {
											swal({
												title: "Cliente atendido por usted ahora.",
												text: false,
												type: 'success',
												timer: 1500,
												showConfirmButton: false
											});
											$("#buscador_texto").val('');
											
											array_clientes.push(respuesta.result);
											//console.log('Nuevo cliente: ' + respuesta.result.id);
											seleccionar_cliente(respuesta.result.id, true);
											//console.log(array_clientes);
											return false;
										}
										return false;
									} catch (e) {
										swal('¡Error!', e, 'error');
									//	console.log("Error de TRY-CATCH --> Error: " + e);
										return false;
									}
								})
								.fail(function (xhr, status, error) {
									swal('¡Error!', error, 'error');
								//	console.log("Error de .FAIL -- Error: " + error);
									return false;
								});
						return false;
					});
					return false;
				}
				if (parseInt(respuesta.http_code) == 200) {					 

					$("#buscador_texto").val('');
					if(respuesta.mensaje != ""){
						swal('Aviso', respuesta.mensaje, 'info');
					}
					
					array_clientes.push(respuesta.result);
					//console.log('Nuevo cliente: ' + respuesta.result.id);
					seleccionar_cliente(respuesta.result.id, true);
					//console.log(array_clientes);
					return false;
				}
				return false;
			},
			error: function (result) {
				auditoria_send({"proceso": "validar_cliente_televentas_error", "data": result});
				return false;
			}
		});
	} else {
		//console.log('Ya existe: ' + buscador_cliente_id);
		$("#buscador_texto").val('');
		seleccionar_cliente(buscador_cliente_id, false);
	}

}

function sec_tlv_reg_cliente() {

	var busc_tipo = $("input:radio[name='buscador_tipo']:checked").val();
	var buscador_texto = $.trim($("#buscador_texto").val());

	var data = {
		"accion": "busqueda_api_cliente_televentas",
		"tipo": busc_tipo,
		"valor": buscador_texto
	}

	var buscador_cliente_id = 0;
	//console.log(array_clientes);

	if (array_clientes.length > 0) {
		$.each(array_clientes, function (index, item) {

			//CELULAR
			if (parseInt(busc_tipo) === 9 && parseInt(item.telefono) === parseInt(buscador_texto)) {
				buscador_cliente_id = parseInt(item.id);
			}
			//WEB-ID
			if (parseInt(busc_tipo) === 8 && parseInt(item.web_id) === parseInt(buscador_texto)) {
				buscador_cliente_id = parseInt(item.id);
			}
			//DOC.IDENTIDAD
			if (parseInt(busc_tipo) >= 0 && parseInt(busc_tipo) <= 2) {
				if (parseInt(item.tipo_doc) === parseInt(busc_tipo) && parseInt(item.num_doc) === parseInt(buscador_texto)) {
					buscador_cliente_id = parseInt(item.id);
				}
			}

		});
	}

	if (parseInt(buscador_cliente_id) === 0) {
		$.ajax({
			url: "/sys/set_televentas.php",
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
				auditoria_send({"proceso": "busqueda_api_cliente_televentas", "data": respuesta});
				//console.log(respuesta);
				if (parseInt(respuesta.http_code) == 400) {
					swal('Aviso', respuesta.status, 'warning');
					return false;
				}			 
				if (parseInt(respuesta.http_code) == 200) {
					if(respuesta.result.id == 0){
						respuesta.result.id = "0_" + buscador_texto;
					}

					if(respuesta.status != ""){
						swal('Aviso', respuesta.status, 'warning');
					}

					$("#buscador_texto").val('');
					if(respuesta.mensaje != ""){
						swal('Aviso', respuesta.mensaje, 'info');
					}
					
					array_clientes.push(respuesta.result);
					//console.log('Nuevo cliente: ' + respuesta.result.id);
					seleccionar_cliente(respuesta.result.id, true);
					//console.log(array_clientes);
					return false;
				}
				return false;
			},
			error: function (result) {
				auditoria_send({"proceso": "busqueda_api_cliente_televentas_error", "data": result});
				return false;
			}
		});
	} else {
		//console.log('Ya existe: ' + buscador_cliente_id);
		$("#buscador_texto").val('');
		seleccionar_cliente(buscador_cliente_id, false);
	}

}

/*

function buscar_cliente() {
	$("#buscador_lbl_mensaje").html('');

	var busc_tipo = $("input:radio[name='buscador_tipo']:checked").val();
	var buscador_texto = $.trim($("#buscador_texto").val());

	var bca = "0_" + buscador_texto;
	remover_cliente_reabierto(bca);

	// Celular
	if (parseInt(busc_tipo) == 9) {
		if (buscador_texto.length !== 9) {
			$("#buscador_lbl_mensaje").html('El número de celular debe tener 9 dígitos.');
			$("#buscador_texto").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) > 900000000 && parseInt(buscador_texto) <= 999999999)) {
			$("#buscador_lbl_mensaje").html('Número de celular inválido.');
			$("#buscador_texto").focus();
			return false;
		}
	}
	// DNI
	if (parseInt(busc_tipo) == 0) {
		if (buscador_texto.length !== 8) {
			$("#buscador_lbl_mensaje").html('El número de DNI debe tener 8 dígitos.');
			$("#buscador_texto").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) >= 1 && parseInt(buscador_texto) <= 99999999)) {
			$("#buscador_lbl_mensaje").html('Número de DNI inválido.');
			$("#buscador_texto").focus();
			return false;
		}
	}
	// CE/PTP
	if (parseInt(busc_tipo) == 1) {
		if (buscador_texto.length !== 9) {
			$("#buscador_lbl_mensaje").html('El número de CE/PTP debe tener 9 dígitos.');
			$("#buscador_texto").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) >= 0 && parseInt(buscador_texto) <= 999999999)) {
			$("#buscador_lbl_mensaje").html('Número de CE/PTP inválido.');
			$("#buscador_texto").focus();
			return false;
		}
	}
	// PASAPORTE
	if (parseInt(busc_tipo) == 2) {
		if (!(buscador_texto.length > 0)) {
			$("#buscador_lbl_mensaje").html('El número de PASAPORTE no debe estar vacío.');
			$("#buscador_texto").focus();
			return false;
		}
	}
	// ID-WEB
	if (parseInt(busc_tipo) == 8) {
		if (!(buscador_texto.length > 0)) {
			$("#buscador_lbl_mensaje").html('El número de ID-WEB no debe estar vacío.');
			$("#buscador_texto").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) > 0)) {
			$("#buscador_lbl_mensaje").html('ID-WEB inválido.');
			$("#buscador_texto").focus();
			return false;
		}
	}

	var data = {
		"accion": "obtener_televentas_cliente",
		"tipo": busc_tipo,
		"valor": buscador_texto,
		"hash": tls_hash,
		"timestamp": Date.now()
	}

	var buscador_cliente_id = 0;
	//console.log(array_clientes);

	if (array_clientes.length > 0) {
		$.each(array_clientes, function (index, item) {
			 
			//CELULAR
			if (parseInt(busc_tipo) === 9 && parseInt(item.telefono) === parseInt(buscador_texto)) {
				buscador_cliente_id = parseInt(item.id);
			}
			//WEB-ID
			if (parseInt(busc_tipo) === 8 && parseInt(item.web_id) === parseInt(buscador_texto)) {
				buscador_cliente_id = parseInt(item.id);
			}
			//DOC.IDENTIDAD
			if (parseInt(busc_tipo) >= 0 && parseInt(busc_tipo) <= 2) {
				if (parseInt(item.tipo_doc) === parseInt(busc_tipo) && parseInt(item.num_doc) === parseInt(buscador_texto)) {
					buscador_cliente_id = parseInt(item.id);
				}
			}

		});
	}

	if (parseInt(buscador_cliente_id) === 0) {
		$.ajax({
			url: "/sys/set_televentas.php",
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
				auditoria_send({"proceso": "obtener_televentas_cliente", "data": respuesta});
				//console.log(respuesta);
				if (parseInt(respuesta.http_code) == 400) {
					swal('Aviso', respuesta.status, 'warning');
					return false;
				}
				if (parseInt(respuesta.http_code) == 405) {
					//swal('Aviso', respuesta.result, 'warning');
					//console.log(respuesta.result2);
					//console.log(respuesta.result);
					swal({
						html: true,
						title: 'El cliente ' + respuesta.result.nombre + ' esta siendo atendido por el cajero ' + respuesta.result2.usuario_nombres +
								'<br><span style="font-size:20px; font-weight:bold;" >Última consulta: ' + respuesta.result.updated_at + '.</span>',
						text: "¿Desea atender al cliente?",
						type: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#0336FF',
						cancelButtonColor: '#d33',
						confirmButtonText: 'SI, ATENDERLO',
						cancelButtonText: 'CANCELAR',
						closeOnConfirm: false,
						//,showLoaderOnConfirm: true
					}, function () {

						$.post("/sys/set_televentas.php", {
							accion: "bloquear_cliente",
							cliente_id: respuesta.result.id,
							hash: tls_hash
						})
								.done(function (data2) {
									try {
										var respuesta2 = JSON.parse(data2);
										if (parseInt(respuesta2.http_code) == 400) {
											swal({
												title: "Error",
												text: respuesta2.status,
												type: 'danger',
												timer: 1500,
												showConfirmButton: false
											});
											return false;
										}
										if (parseInt(respuesta2.http_code) == 200) {
											swal({
												title: "Cliente atendido por usted ahora.",
												text: false,
												type: 'success',
												timer: 1500,
												showConfirmButton: false
											});
											$("#buscador_texto").val('');
											array_clientes.push(respuesta.result);
											//console.log('Nuevo cliente: ' + respuesta.result.id);
											seleccionar_cliente(respuesta.result.id, true);
											//console.log(array_clientes);
											return false;
										}
										return false;
									} catch (e) {
										swal('¡Error!', e, 'error');
									//	console.log("Error de TRY-CATCH --> Error: " + e);
										return false;
									}
								})
								.fail(function (xhr, status, error) {
									swal('¡Error!', error, 'error');
								//	console.log("Error de .FAIL -- Error: " + error);
									return false;
								});
						return false;
					});
					return false;
				}
				if (parseInt(respuesta.http_code) == 200) {
					if(respuesta.result.id == 0){
						respuesta.result.id = "0_" + buscador_texto;
					}
					$("#buscador_texto").val('');
					if(respuesta.mensaje != ""){
						swal('Aviso', respuesta.mensaje, 'info');
					}
					array_clientes.push(respuesta.result);
					//console.log('Nuevo cliente: ' + respuesta.result.id);
					seleccionar_cliente(respuesta.result.id, true);
					//console.log(array_clientes);
					return false;
				}
				return false;
			},
			error: function (result) {
				auditoria_send({"proceso": "obtener_televentas_cliente_error", "data": result});
				return false;
			}
		});
	} else {
		//console.log('Ya existe: ' + buscador_cliente_id);
		$("#buscador_texto").val('');
		seleccionar_cliente(buscador_cliente_id, false);
	}
}
*/

function verificar_limite_aten() {

	var total_cli = array_clientes.length;
	 
	if(total_cli < max_aten_total){
		sec_tlv_val_cliente();						 
	} else { 
		swal({
		title: "Máximo de atención alcanzado (" + max_aten_total + ")",
		type: 'info',
		timer: 5000,
		showConfirmButton: true
		});
		return false; 

	} 
}


var gen_cliente_id = 0;
var gen_cliente_nombres = '';
var gen_calimaco_id = '';
var gen_validate_web_id = 0;
var gen_balance_total = 0;
var gen_balance_bono_disponible = 0;
var gen_balance_bono_utilizado = 0;
var gen_balance_retiro_disponible = 0;
var gen_balance_no_retirable_disponible = 0;
var gen_balance_dinero_at = 0;
var gen_etiqueta_bloqueo = 0;
var gen_etiqueta_local_test = 0;
var gen_cliente_mincetur = 0;
function seleccionar_cliente(id_cliente, nuevo) {
	//obtener_fecha_hora();
	limpiar_bordes_div_cliente();

	loading("true");

	//$('#' + id_cliente).find('.div_tab_cliente').removeClass('naranja');
	$('#' + id_cliente).find('.div_tab_cliente').removeClass('morado');
	$('#' + id_cliente).find('.div_tab_cliente').removeClass('verde');
	$('#' + id_cliente).find('.div_tab_cliente').removeClass('rojo');
	$('#' + id_cliente).find('.div_tab_cliente_transaccion_nueva').html('');
	$('#' + id_cliente).find('.div_tab_cliente_transaccion_nueva').removeClass('div_tab_cliente_transaccion_nueva_add');

	gen_cliente_id = id_cliente;
	if (array_clientes.length > 0) {
		$('#div_tabs').show();
		$('#div_resultado').show();
	}
	var cliente_tipo_doc = 0;
	var cliente_num_doc = '';
	var cliente_celular = '';
	var cliente_fec_nac = '';
	var cliente_idweb = '';
	var cliente_web_full_name = '';
	var cliente_player_id = '';
	var cliente_idweb_c = '';
	var cliente_ape_paterno = '';
	var cliente_ape_materno = '';
	var cliente_correo = '';
	var cliente_nombres = '';
	var bono_limite = '';
	var cliente_local = 3900;
	var cliente_nacionalidad ='';
	var cliente_ubigeo = '';
	var cliente_direccion = '';
	var cliente_es_pep ='';
	var cliente_tyc=false;
	var cliente_existente = false;
	var cliente_nuevo = 0;
	
	var titulo_tab = '';
	gen_cliente_nombres = '';
	gen_calimaco_id = '';
	gen_validate_web_id = 0;
	gen_cliente_mincetur = 0;

	$('.sec_tlv_div_etiquetas').show();
	$('.sec_tlv_div_cliente').show();
	$('.sec_tlv_div_transacciones').show();
	$('.sec_tlv_div_portal_altenar').hide();
	$('.sec_tlv_div_portal_torito').hide();
	$('.sec_tlv_div_bingo_total').hide();

	$.each(array_clientes, function (index, item) {
		if (item.id === id_cliente) {
			if (!(parseInt(item.tipo_doc) >= 0 && parseInt(item.tipo_doc) <= 2)) {
				item.tipo_doc = '';
			}

			gen_calimaco_id = item.calimaco_id;
			cliente_tipo_doc = (item.tipo_doc !== null ? item.tipo_doc : '0').toString();
			cliente_num_doc = (item.num_doc !== null ? item.num_doc : '0').toString();
			cliente_celular = (item.telefono !== null ? item.telefono : '').toString();
			cliente_fec_nac = (item.fec_nac !== null ? item.fec_nac : '').toString();
			if(parseInt(id_cliente) > 0){
				cargar_titular_abono_cliente();
			}
			
			cliente_idweb = ((item.web_id !== null && item.web_id !== '0') ? item.web_id : '').toString();
			cliente_web_full_name = item.web_full_name;
			cliente_player_id = item.player_id;
			cliente_ape_paterno = (item.apellido_paterno !== null ? item.apellido_paterno : '').toString();
			cliente_ape_materno = (item.apellido_materno !== null ? item.apellido_materno : '').toString();
			cliente_correo = item.correo;
			cliente_nombres = (item.nombre !== null ? item.nombre : '').toString();

			cliente_nacionalidad = (item.nacionalidad !== null ? item.nacionalidad : '').toString();
			cliente_ubigeo = (item.ubigeo !== null ? item.ubigeo : '').toString();
			cliente_direccion = (item.direccion !== null ? item.direccion : '').toString();
			cliente_es_pep = (item.es_pep !== null && item.es_pep !== undefined ? item.es_pep : "");
			cliente_tyc = (item.tyc !== null && item.tyc !== undefined ? item.tyc : 0);
			cliente_existente = (item.clienteExistente !== null && item.clienteExistente !== undefined ? item.clienteExistente : 0);
			gen_cliente_mincetur = item.is_disabled_mincetur;
			cliente_nuevo = item.is_new_client;

			// cliente_es_pep = (cliente_es_pep === "1" ? true : false);
			cliente_tyc = (cliente_tyc === "1" ? true : false);
			cliente_existente = (cliente_existente === "1" ? true : false);


			cliente_local = (item.cc_id !== null ? item.cc_id : '3900').toString();
			bono_limite = (item.bono_limite == "" ? '10000.00' : item.bono_limite).toString();
			if (cliente_nombres.length > 0) {
				titulo_tab = cliente_nombres + ' ' + cliente_ape_paterno + ' ' + cliente_ape_materno;
				gen_cliente_nombres = cliente_nombres + ' ' + cliente_ape_paterno + ' ' + cliente_ape_materno;
			} else if (cliente_celular.length > 0) {
				titulo_tab = 'CEL: ' + cliente_celular;
			} else if (cliente_num_doc.length > 0) {
				titulo_tab = 'Nº DOC: ' + cliente_num_doc;
			} else if (cliente_idweb.length > 0) {
				titulo_tab = 'ID-WEB: ' + cliente_idweb;
			}
			gen_validate_web_id = item.validate_web_id;

			if(parseInt(item.tipo_balance_id) === 1){
				//$('.tls_balance_dinero_at').hide();
				//$('.tls_balance_real').show();
				$("#evento_dineroat_id").val(0);
				$('.btn_dinero_at').hide();
				$('.btn_agregar_transaccion').show();
			} else if(parseInt(item.tipo_balance_id) === 6) {
				//$('.tls_balance_real').hide();
				//$('.tls_balance_dinero_at').show();
				$('.btn_agregar_transaccion').hide();
				verificar_transacciones_activas_dinero_at(gen_cliente_id);
			}
			$('#sec_tlv_cbx_tipo_balance').val(item.tipo_balance_id);

		}
	});

	$('.div_tab_cliente').removeClass('active');
	var id_div_cliente = "'" + id_cliente + "'";
	/*if(id_cliente == 0){
		id_div_cliente = "'" + id_cliente + "_" + cliente_num_doc + "'";
	}*/
	if (nuevo === true) {
		$('#div_tabs_contenedor').append(
				'<div class="col-md-4 div_tab" id="' + id_cliente + '">' +
				'	<div class="row div_tab_cliente active">' +
				'		<div class="col-md-11 col-xs-11" onclick="seleccionar_cliente(' + id_div_cliente + ', false)" style="display: flex; align-items: center;">' +
				'			<div class="col-md-10 col-xs-10 div_tab_cliente_texto">' + titulo_tab + '</div>' +
				'			<div class="col-md-2 col-xs-2 div_tab_cliente_transaccion_nueva"></div>' +
				'		</div>' +
				'		<div class="col-md-1 col-xs-1 div_tab_cliente_close" onclick="remover_cliente_btn_x(' + id_div_cliente + ')">' +
				'			<div class="chrome-tab-close"></div>' +
				'		</div>' +
				'	</div>' +
				'</div>'
				);
	} else {
		$('#' + id_cliente).find('.div_tab_cliente').addClass('active');
	}

	limpiar_campos_div_cliente();

	$('#cliente_webfullname').css({'color': '','font-weight': '', 'font-size': ''});
	if(($.trim(cliente_web_full_name)).length>0){
		if($.trim(cliente_nombres)+' '+$.trim(cliente_ape_paterno)+' '+$.trim(cliente_ape_materno)!==$.trim(cliente_web_full_name)){
			$('#cliente_webfullname').css({'color': 'red','font-weight': 'bold', 'font-size': '12px'});
		}
	}

	$('#cliente_tipo_doc').val(cliente_tipo_doc);
	$('#cliente_num_doc').val(cliente_num_doc);
	$('#cliente_celular').val(cliente_celular);
	$('#cliente_fec_nac').val(cliente_fec_nac);
	$('#cliente_idweb').val(cliente_idweb);
	$('#cliente_webfullname').val(cliente_web_full_name);
	$('#cliente_idjugador').val(cliente_player_id);
	$('#cliente_idwebc').val(cliente_idweb_c);
	$('#cliente_nombre').val(cliente_nombres);
	$('#cliente_apepaterno').val(cliente_ape_paterno);
	$('#cliente_apematerno').val(cliente_ape_materno);
	$('#cliente_correo').val(cliente_correo);
	$('#cliente_direccion').val(cliente_direccion);
	// $('#cliente_pep').prop('checked', cliente_es_pep);
	$('#cliente_pep2').val(cliente_es_pep);


	if(cliente_existente)
	{
		$('#cliente_tyc').prop('checked', cliente_tyc);
		sect_tvl_llenarUbigeo(cliente_ubigeo, true);
	}else{
		$("#cliente_btn_cancelar").hide();
		sect_tlv_obtenerClienteSIC(cliente_tipo_doc, cliente_num_doc)
		.then((clienteSIC) => {
			var code = "100,105,107,109";
			var codeArray = code.split(",");
			
			if((clienteSIC.http_code==200 && clienteSIC.status=="true" && codeArray.includes(clienteSIC.origin) )){
				var result = clienteSIC.result;
				var esPep = (result.uni_politicamente_expuesto !== null && result.uni_politicamente_expuesto !== undefined ? result.uni_politicamente_expuesto : "");
				var esTyc = (result.uni_terminos_condiciones !== null && result.uni_terminos_condiciones !== undefined ? result.uni_terminos_condiciones : 0);
				// esPep = (esPep === 1 ? true : false);
				esTyc = ((esTyc === 1 || esTyc === "1") ? true : false);

				if(result.uni_telefono){
					$('#cliente_celular').val(result.uni_telefono);
					$('#cliente_celular').attr('disabled', 'disabled');
				}

				$('#cliente_num_doc').attr('disabled', 'disabled');
				if(!result.uni_email.includes("no-user.at")){
					$('#cliente_correo').val(result.uni_email);
					$('#cliente_correo').attr('disabled', 'disabled');
					
				}
				if(result.uni_direccion){
					$('#cliente_direccion').val(result.uni_direccion);
					$('#cliente_direccion').attr('disabled', 'disabled');

				}
				if(result.uni_nombres){
					$('#cliente_nombre').val(result.uni_nombres);
					$('#cliente_nombre').attr('disabled', 'disabled');
				}
				
				if(result.uni_apellido_paterno){
					$('#cliente_apepaterno').val(result.uni_apellido_paterno);
					$('#cliente_apepaterno').attr('disabled', 'disabled');
				}
				
				if(result.uni_apellido_materno){
					$('#cliente_apematerno').val(result.uni_apellido_materno);
					$('#cliente_apematerno').attr('disabled', 'disabled');
				}
				

				if(result.uni_fecha_nacimiento){
					$('#cliente_fec_nac').val(result.uni_fecha_nacimiento);
					$('#cliente_fec_nac').attr('disabled', 'disabled');
				}
				
				// $('#cliente_pep').prop('checked', esPep);
				if(esPep==1 ||esPep==true || esPep==false || esPep==0){
					esPep = esPep == true ? 1: 0;
					$('#cliente_pep2').val(esPep);
					$('#cliente_pep2').attr('disabled', 'disabled');
				}else{
					$('#cliente_pep2').removeAttr('disabled');
				}
				

				$('#cliente_tyc').prop('checked', esTyc);

				if(result.uni_terminos_condiciones!=undefined){
					$('#cliente_tyc').attr('disabled', 'disabled');
				}

				if(result.uni_politicamente_expuesto!=undefined){
					$('#cliente_pep2').attr('disabled', 'disabled');
				}

				if (String(result.uni_ubigeo).length == 6) {
					sect_tvl_llenarUbigeo(String(result.uni_ubigeo), true);
				}
				
			}else{
				$('#cliente_tyc').prop('checked', true);
				sect_tvl_llenarUbigeo(cliente_ubigeo, false);
			}
			
		});
	}
	
	//console.log('cliente_local: '+cliente_local);
	if(cliente_local == ""){
		cliente_local = "3900";
	}

	 // llenar combo nacionalidad
	sect_tlv_obtenerNacionalidad()
		.then((nacionalidad) => {
			var selectElementNac = document.getElementById("sec_tlv_cliente_nacionalidad");
			selectElementNac.innerHTML = '<option value="">:: Seleccione ::</option>';
			nacionalidad.forEach(function(nac) {
				var option = document.createElement("option");
				option.value = nac.codigo;
				option.textContent = nac.nombre;
				selectElementNac.appendChild(option);
			});
			
			if(cliente_nacionalidad.length > 0){
				$('#sec_tlv_cliente_nacionalidad').val(cliente_nacionalidad).trigger('change');
			}else{
				$('#sec_tlv_cliente_nacionalidad').val('PER').trigger('change');
			}
		});


	$('#cliente_local').val(cliente_local);
	$('#cliente_local').select2().trigger('change');
	$('#bono_limite').val(bono_limite);
	
	if(gen_cliente_mincetur == 1 && cliente_nuevo != 1){
		swal({
			html: true,
			title: 'CUENTA CERRADA MINCETUR',
			text: '¿Desea atender al cliente?',
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#0336FF',
			cancelButtonColor: '#d33',
			confirmButtonText: 'SI, ATENDER',
			cancelButtonText: 'CANCELAR',
			closeOnConfirm: false,
			//,showLoaderOnConfirm: true
		}, function (isConfirmed) {
			
			if (!isConfirmed){
				//if(parseInt(id_cliente) == 0){
					remover_cliente_btn_x(id_cliente);
				//}
			}else{
				if(parseInt(id_cliente) == 0){
					
					sec_tlv_new_client_show();	
					sec_tlv_min_nuevo_btn();
					sec_tlv_deshabilitar_botoneria(true);
				}else{
					
					sec_tlv_deshabilitar_botoneria(true);
					sec_tlv_min_antiguo_btn();
					bloquear_div_cliente();
					listar_transacciones(id_cliente);
					var tipo_balance_id = $("#sec_tlv_cbx_tipo_balance").val();
					insertar_cliente_tipo_balance(id_cliente,tipo_balance_id);
				}
				swal.close();
			}
			swal.close();
		});
	}else{
		if(parseInt(id_cliente) == 0){
			sec_tlv_deshabilitar_botoneria(true);
			sec_tlv_new_client_show();
		}else{
			sec_tlv_deshabilitar_botoneria(false);
			var tipo_balance_id = $("#sec_tlv_cbx_tipo_balance").val();
			insertar_cliente_tipo_balance(id_cliente,tipo_balance_id);

		}
		if(cliente_nuevo == 1){
			sec_tlv_deshabilitar_botoneria(true);
			sec_tlv_min_nuevo_btn();
		}
		bloquear_div_cliente();
		listar_transacciones(id_cliente);
		
	}
	
	
	// if(parseInt(id_cliente) == 0){
	// 	sec_tlv_deshabilitar_botoneria(true);
	// 	if(gen_cliente_mincetur == 1){
	// 		desbloquear_div_cliente();
	// 		$('#tabla_transacciones').html(
	// 			'<tr>' +
	// 			'	<td colspan="10" class="text-center">NO HAY DATOS</td>' +
	// 			'</tr>'
	// 		);
	// 		$('#span_dinero_at').html('0.00');
	// 		$('#span_balance').html('0.00');
	// 		$('#span_balance_bono_disponible').html('0.00');
	// 		$('#span_balance_retiro_disponible').html('0.00');
	// 		$('#span_bonos').html('0.00');
	// 		swal.close();
	// 	}else{
	// 		desbloquear_div_cliente();
	// 		$('#tabla_transacciones').html(
	// 			'<tr>' +
	// 			'	<td colspan="10" class="text-center">NO HAY DATOS</td>' +
	// 			'</tr>'
	// 		);
	// 		$('#span_dinero_at').html('0.00');
	// 		$('#span_balance').html('0.00');
	// 		$('#span_balance_bono_disponible').html('0.00');
	// 		$('#span_balance_retiro_disponible').html('0.00');
	// 		$('#span_bonos').html('0.00');
	// 		swal.close();
	// 	}
	// } else {
	// 	if(gen_cliente_mincetur == 1 && (cliente_nuevo == undefined || cliente_nuevo == 0)){
	// 		sec_tlv_deshabilitar_botoneria(true);
	// 		sec_tlv_min_antiguo_btn();
	// 	}else if(gen_cliente_mincetur == 1 && cliente_nuevo == 1){
	// 		sec_tlv_deshabilitar_botoneria(true);
	// 		sec_tlv_min_nuevo_btn();
	// 	}else{
	// 		sec_tlv_deshabilitar_botoneria(false);
	// 	}
	// 	bloquear_div_cliente();
	// 	listar_transacciones(id_cliente);
	// 	var tipo_balance_id = $("#sec_tlv_cbx_tipo_balance").val();
	// 	insertar_cliente_tipo_balance(id_cliente,tipo_balance_id);
	// }

}
function sec_tlv_new_client_show(){
	desbloquear_div_cliente();
	$('#tabla_transacciones').html(
		'<tr>' +
		'	<td colspan="10" class="text-center">NO HAY DATOS</td>' +
		'</tr>'
	);
	$('#span_dinero_at').html('0.00');
	$('#span_balance').html('0.00');
	$('#span_balance_bono_disponible').html('0.00');
	$('#span_balance_retiro_disponible').html('0.00');
	$('#span_bonos').html('0.00');
}

function sect_tvl_llenarUbigeo(cliente_ubigeo, disabled){
	// llenar combo departamento

	sect_tlv_obtenerUbigeo("")
		.then((ubigeo) => {
			var selectElementDep = document.getElementById("sec_tlv_cliente_departamento");
			selectElementDep.innerHTML = '<option value="">:: Seleccione ::</option>';
			ubigeo.forEach(function(state) {
				var option = document.createElement("option");
				option.value = state.cod_depa;
				option.textContent = state.nombre;
				selectElementDep.appendChild(option);
			});

			if(cliente_ubigeo.length === 6){

				let codDepartUbigeo = cliente_ubigeo.substring(0, 2); 
				let codprovUbigeo = cliente_ubigeo.substring(0, 4); 
				let codprov = cliente_ubigeo.substring(2, 4); 
				let codDist = cliente_ubigeo.substring(4, 6); 

				$('#sec_tlv_cliente_departamento').val(codDepartUbigeo);
				
				sect_tlv_obtenerUbigeo(codDepartUbigeo)
				.then((ubigeoProv) => {

					var selectElementProv = document.getElementById("sec_tlv_cliente_provincia");
					selectElementProv.innerHTML = '<option value="">:: Seleccione ::</option>';
					ubigeoProv.forEach(function(province) {
						var option = document.createElement("option");
						option.value = province.cod_prov;
						option.textContent = province.nombre;
						selectElementProv.appendChild(option);
					});

					$('#sec_tlv_cliente_provincia').val(codprov);

						sect_tlv_obtenerUbigeo(codprovUbigeo)
						.then((ubigeoDist) => {

							var selectElementDist = document.getElementById("sec_tlv_cliente_distrito");
							selectElementDist.innerHTML = '<option value="">:: Seleccione ::</option>';
							ubigeoDist.forEach(function(city) {
								var option = document.createElement("option");
								option.value = city.cod_dist;
								option.textContent = city.nombre;
								selectElementDist.appendChild(option);
							});

							$('#sec_tlv_cliente_distrito').val(codDist);

							$('#sec_tlv_cliente_nacionalidad').attr('disabled', disabled);
							$('#sec_tlv_cliente_departamento').attr('disabled', disabled);
							$('#sec_tlv_cliente_provincia').attr('disabled', disabled);
							$('#sec_tlv_cliente_distrito').attr('disabled', disabled);
							$('#cliente_pep2').attr('disabled', 'disabled');

						});
				});

			}else{
				
				var selectElement = document.getElementById("sec_tlv_cliente_provincia");
				selectElement.innerHTML = '<option value="">:: Seleccione ::</option>';

				var selectElement2 = document.getElementById("sec_tlv_cliente_distrito");
				selectElement2.innerHTML = '<option value="">:: Seleccione ::</option>';

				setTimeout(() => {
					
					if(disabled){
						$('#sec_tlv_cliente_nacionalidad').attr('disabled', 'disabled');
						$('#sec_tlv_cliente_departamento').attr('disabled', 'disabled');
						$('#sec_tlv_cliente_provincia').attr('disabled', 'disabled');
						$('#sec_tlv_cliente_distrito').attr('disabled', 'disabled');
						// $('#cliente_pep2').attr('disabled', 'disabled');

	
					}else{
	
						$('#sec_tlv_cliente_nacionalidad').removeAttr('disabled');
						$('#sec_tlv_cliente_departamento').removeAttr('disabled');
						$('#sec_tlv_cliente_provincia').removeAttr('disabled');
						$('#sec_tlv_cliente_distrito').removeAttr('disabled');
						// $('#cliente_pep2').removeAttr('disabled');
	
					}
					
				}, 500);

			}

		});
}

function sect_tlv_obtenerUbigeo($ubigeo) {
	return new Promise(function(resolve, reject) {
		var data = {
			"accion": "consultar_ubigeo",
			"ubigeo": $ubigeo
		}

		$.ajax({
			url: "/sys/set_televentas.php",
			type: 'POST',
			data: data,
			beforeSend: function () {
				//loading("true");
			},
			complete: function () {
				loading();
			},
			success: function (data) {
				try {
					var parsedData = JSON.parse(data);
					auditoria_send({"proceso": "consultar_ubigeo", "data": parsedData});

						resolve(parsedData);
					
				} catch (e) {
					auditoria_send({"proceso": "consultar_ubigeo", "data": data});
					reject(e);
				}
				loading();
			},
			error: function (result) {
				auditoria_send({"proceso": "consultar_ubigeo", "data": result});
				reject(result);
			}
		});
	});
}


function sect_tlv_obtenerNacionalidad() {
	return new Promise(function(resolve, reject) {
		var data = {
			"accion": "consultar_nacionalidad"
		}

		$.ajax({
			url: "/sys/set_televentas.php",
			type: 'POST',
			data: data,
			beforeSend: function () {
				//loading("true");
			},
			complete: function () {
				loading();
			},
			success: function (data) {
				try {
					var parsedData = JSON.parse(data);
					auditoria_send({"proceso": "consultar_nacionalidad", "data": parsedData});

					resolve(parsedData);
					
				} catch (e) {
					auditoria_send({"proceso": "consultar_nacionalidad", "data": data});
					reject(e);
				}
				loading();
			},
			error: function (result) {
				auditoria_send({"proceso": "consultar_nacionalidad", "data": result});
				reject(result);
			}
		});
	});
}

function sect_tlv_obtenerClienteSIC(cliente_tipo_doc, cliente_num_doc) {
	return new Promise(function(resolve, reject) {
		var data = {
			"accion": "consultar_api_SIC_cliente_televentas",
			"tipoDocumento": cliente_tipo_doc,
			"numeroDocumento": cliente_num_doc
		}

		$.ajax({
			url: "/sys/set_televentas.php",
			type: 'POST',
			data: data,
			beforeSend: function () {
				//loading("true");
			},
			complete: function () {
				loading();
			},
			success: function (data) {
				try {
					
					var parsedData = JSON.parse(data);
					auditoria_send({"proceso": "consultar_api_SIC_cliente_televentas", "data": parsedData});

					resolve(parsedData);
					
				} catch (e) {
					auditoria_send({"proceso": "consultar_api_SIC_cliente_televentas", "data": data});
					reject(e);
				}
				loading();
			},
			error: function (result) {
				auditoria_send({"proceso": "consultar_api_SIC_cliente_televentas", "data": result});
				reject(result);
			}
		});
	});
}


$("#sec_tlv_cliente_departamento").on("change", function(e){
	e.preventDefault();

	let departamento = $(this).val();

	var selectElement = document.getElementById("sec_tlv_cliente_provincia");
	selectElement.innerHTML = '<option value="">:: Seleccione ::</option>';

	var selectElement2 = document.getElementById("sec_tlv_cliente_distrito");
		selectElement2.innerHTML = '<option value="">:: Seleccione ::</option>';


	sect_tlv_obtenerUbigeo(departamento)
		.then((ubigeo) => {

			var selectElement = document.getElementById("sec_tlv_cliente_provincia");
			selectElement.innerHTML = '<option value="">:: Seleccione ::</option>';
			ubigeo.forEach(function(province) {
				var option = document.createElement("option");
				option.value = province.cod_prov;
				option.textContent = province.nombre;
				selectElement.appendChild(option);
			});

		});

});

$("#sec_tlv_cliente_provincia").on("change", function(e){
	e.preventDefault();

	let departamento = $("#sec_tlv_cliente_departamento").val();
	let provincia = $(this).val();
	let ubigeo = departamento + provincia;
	
	var selectElement = document.getElementById("sec_tlv_cliente_distrito");
		selectElement.innerHTML = '<option value="">:: Seleccione ::</option>';

	sect_tlv_obtenerUbigeo(ubigeo)
		.then((ubigeo) => {

			var selectElement = document.getElementById("sec_tlv_cliente_distrito");
			selectElement.innerHTML = '<option value="">:: Seleccione ::</option>';
			ubigeo.forEach(function(city) {
				var option = document.createElement("option");
				option.value = city.cod_dist;
				option.textContent = city.nombre;
				selectElement.appendChild(option);
			});

		});

});


function sec_televentas_obtener_opciones(accion, select) {
	$.ajax({
	  url: "/sys/set_televentas.php",
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
				$(select).append('<option value="0">- Seleccione -</option>');
			}else if(respuesta.supervisor == 1){
				$(select).attr('disabled', true);
			}
		}else{
			$(select).append('<option value="0">- Seleccione -</option>');
		}
		$(respuesta.result).each(function (i, e) {
		  opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
		  $(select).append(opcion);
		});
	  },
	  error: function () {},
	});
  }

function remover_cliente_btn_x(id_cliente){
	$('#' + id_cliente).remove();
	if (parseInt(gen_cliente_id) === parseInt(id_cliente)) {
		$('#div_resultado').hide();
	}
	//console.log('array_clientes completo');
	//console.log(array_clientes);
	$.each(array_clientes, function (index, item) {
		//console.log('id: '+item.id);
		if (parseInt(item.id) === parseInt(id_cliente)) {
			array_clientes.splice(index, 1);
			//console.log('array_clientes INcompleto');
			//console.log(array_clientes);
			$.post("/sys/set_televentas.php", {
				accion: "desbloquear_cliente",
				cliente_id: id_cliente
			})
					.done(function (data) {
						try {
							return false;
						} catch (e) {
							swal('¡Error!', e, 'error');
						//	console.log("Error de TRY-CATCH --> Error: " + e);
							return false;
						}
					})
					.fail(function (xhr, status, error) {
						swal('¡Error!', error, 'error');
					//	console.log("Error de .FAIL -- Error: " + error);
						return false;
					});
			return false;
		}
	});
	//sec_tlv_add_sportsbook_client_open(id_cliente, 0);
	sec_tlv_module_client_open('sportsbook', id_cliente, 0);
	sec_tlv_module_client_open('torito', id_cliente, 0);
}

function remover_cliente(id_cliente) {
  
	$('#' + id_cliente).remove();
	if (parseInt(gen_cliente_id) === parseInt(id_cliente)) {
		$('#div_resultado').hide();
	}
	//console.log('array_clientes completo');
	//console.log(array_clientes);
	$.each(array_clientes, function (index, item) {
		//console.log('id: '+item.id);
		if (parseInt(item.id) === parseInt(id_cliente)) {
			alertify.warning('El cliente '+item.nombre+' esta siendo atendido por otro promotor.', 8);
			array_clientes.splice(index, 1);
			//console.log('array_clientes INcompleto');
			//console.log(array_clientes);
			$.post("/sys/set_televentas.php", {
				accion: "desbloquear_cliente",
				cliente_id: id_cliente
			})
					.done(function (data) {
						try {
							return false;
						} catch (e) {
							swal('¡Error!', e, 'error');
						//	console.log("Error de TRY-CATCH --> Error: " + e);
							return false;
						}
					})
					.fail(function (xhr, status, error) {
						swal('¡Error!', error, 'error');
					//	console.log("Error de .FAIL -- Error: " + error);
						return false;
					});
			return false;
		}
	});
}

function remover_cliente_reabierto(id_cliente) {
  
	$('#' + id_cliente).remove();
	if (parseInt(gen_cliente_id) === parseInt(id_cliente)) {
		$('#div_resultado').hide();
	}
	//console.log('array_clientes completo');
	//console.log(array_clientes);
	$.each(array_clientes, function (index, item) {
		//console.log('id: '+item.id);
		if (parseInt(item.id) === parseInt(id_cliente)) {
			alertify.warning('El cliente '+item.nombre+' ha sido abierto en otra pestaña.', 8);
			array_clientes.splice(index, 1);
		}
	});
}

function listar_transacciones(id_cliente) {

	if( parseInt($("#sec_tlv_cbx_tipo_balance").val()) == 1 ){
		$( ".class_dinero_promocional" ).css( "background-color","#fff" );
	} else if ( parseInt($("#sec_tlv_cbx_tipo_balance").val()) == 6 ){
		$( ".class_dinero_promocional" ).css( "background-color","#e0f4ff" );
	} else{
		$( ".class_dinero_promocional" ).css( "background-color","#fff" );
	}

	var div_sec_televentas_fecha_inicio = $('#div_sec_televentas_fecha_inicio').val();
	var div_sec_televentas_fecha_fin = $('#div_sec_televentas_fecha_fin').val();
	var tipo_balance = $("#sec_tlv_cbx_tipo_balance").val();
	gen_etiqueta_bloqueo = 0;
	gen_etiqueta_local_test = 0;
	if (div_sec_televentas_fecha_inicio.length !== 10) {
		$("#div_sec_televentas_fecha_inicio").focus();
		return false;
	}
	if (div_sec_televentas_fecha_fin.length !== 10) {
		$("#div_sec_televentas_fecha_fin").focus();
		return false;
	}

	limpiar_tabla_transacciones();
	$.post("/sys/set_televentas.php", {
		accion: "obtener_transacciones_por_cliente",
		id_cliente: id_cliente,
		fecha_inicio: div_sec_televentas_fecha_inicio,
		fecha_fin: div_sec_televentas_fecha_fin,
		tipo_balance: tipo_balance
	})
			.done(function (data) {
				try {
					var respuesta = JSON.parse(data);
					if (parseInt(respuesta.http_code) == 400) {
						$('#tabla_transacciones').append(
								'<tr>' +
								'<td colspan="10" class="text-center">' + respuesta.status + '</td>' +
								'</tr>'
								);
						return false;
					}
					if (parseInt(respuesta.http_code) == 200) {
						var anterior_balance_dinero_at = gen_balance_dinero_at;
						gen_balance_dinero_at = respuesta.result_balance_dinero_at;
						respuesta.result_balance_dinero_at = respuesta.result_balance_dinero_at.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						$('#span_dinero_at').html(respuesta.result_balance_dinero_at);

						if( respuesta.resp_asignar_bonoAT_alert ) {
							swal('Bono AT!', respuesta.resp_asignar_bonoAT_alert, 'success');
						}

						if(parseFloat(respuesta.result_balance)<0){
							//alertify.error('El monto vendido es mayor al balance del cliente, por favor contacta a tu supervisor.', 5);
							swal({
								title: "El monto vendido es mayor al balance del cliente, por favor contacta a tu supervisor.",
								type: 'error',
								timer: 10000,
								showConfirmButton: true
							});
							gen_balance_total = respuesta.result_balance;
							respuesta.result_balance = respuesta.result_balance.replace(/\D/g, "")
									.replace(/([0-9])([0-9]{2})$/, '$1.$2')
									.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
							$('#span_balance').html('-' + respuesta.result_balance);
							$('#span_balance').css('color', 'red');
						} else {
							gen_balance_total = respuesta.result_balance;
							respuesta.result_balance = respuesta.result_balance.replace(/\D/g, "")
									.replace(/([0-9])([0-9]{2})$/, '$1.$2')
									.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
							$('#span_balance').html(respuesta.result_balance);
							$('#span_balance').css('color', '');
						}

						gen_balance_bono_disponible = respuesta.result_balance_bono_disponible;
						respuesta.result_balance_bono_disponible = respuesta.result_balance_bono_disponible.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						$('#span_balance_bono_disponible').html(respuesta.result_balance_bono_disponible);

						gen_balance_retiro_disponible = respuesta.result_balance_retiro_disponible;
						respuesta.result_balance_retiro_disponible = respuesta.result_balance_retiro_disponible.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						$('#span_balance_retiro_disponible').html(respuesta.result_balance_retiro_disponible);

						respuesta.result_bono_usado_mes_actual = respuesta.result_bono_usado_mes_actual.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						$('#span_bonos').html(respuesta.result_bono_usado_mes_actual);

						gen_balance_no_retirable_disponible = respuesta.result_balance_no_retirable_disponible;

						// ***********************************************************************************
						// ETIQUETAS *************************************************************************
						var etiquetas_bloqueo = '';
						if (respuesta.result_labels.length > 0) {
							$.each(respuesta.result_labels, function (index2, item2) {
								var eliminar_etiqueta = '';
								if(item2.tipo_etiqueta == 4){
									if (permiso_etq_fraude) {
										eliminar_etiqueta = '<span class="fa fa-close" onclick="eliminar_etiqueta(' + item2.id + ')"></span>';
									}
									gen_etiqueta_bloqueo = 1;
									if(etiquetas_bloqueo.length == 0){
										etiquetas_bloqueo = item2.etiqueta;
									}else{
										etiquetas_bloqueo += ', ' + item2.etiqueta;
									}
									sec_tlv_bloqueo_botones_cliente(true);
								}
								if (permiso_eliminar_etiqueta) {
									if(item2.tipo_etiqueta == 1){
										eliminar_etiqueta = '<span class="fa fa-close" onclick="eliminar_etiqueta(' + item2.id + ')"></span>';
									}
								}
								$('#div_labels').append(
										'<button type="button" class="btn btn-default" id="' + item2.id + '" ' +
										'   style="color: ' + invertColor(item2.color) + ';background-color: ' + item2.color + ';font-weight: bold;">' +
										item2.etiqueta + '&nbsp; &nbsp;' +
										eliminar_etiqueta +
										'</button>'
										);
								
								if(item2.id_etiqueta == 44){ // saldo no retirable
									gen_etiqueta_local_test = 1;
								}
							});
						}
						var clase_eliminar_bloqueo = '';
						if(gen_etiqueta_bloqueo  == 1){
							clase_eliminar_bloqueo = 'disabled';
							swal({
					            html:true,
					            title: 'Este cliente cuenta con la etiqueta: ' + etiquetas_bloqueo + 
					            		'<br> Por lo que no podrá ser atendido.',
					            type: 'warning',
					            showCancelButton: false,
					            confirmButtonColor: '#0336FF',
					            confirmButtonText: 'De acuerdo.',
					            closeOnConfirm: true,
					            showLoaderOnConfirm: true
					        }, function(){
					             
					        });
						}else{
							if(!gen_cliente_mincetur){
								sec_tlv_bloqueo_botones_cliente(false);
							}
						}

						// ***********************************************************************************
						// TRANSACCIONES *********************************************************************
						if (respuesta.result.length > 0) {
							$.each(respuesta.result, function (index, item) {
								var total = '0.00';
								if (parseFloat(item.total_recarga) > 0) {
									total = item.total_recarga;
								}
								var web_id = 0;
								if (parseInt(item.web_id) > 0) {
									web_id = item.web_id;
								}
								var color = '';
								var btn_eliminar = '';
								var tipo = item.tipo_transaccion;

								var d = new Date();
								var date_now = d.getFullYear() + "-" +
										(d.getMonth() + 1).toString().padStart(2, '0') + "-" +
										d.getDate().toString().padStart(2, '0') + " " +
										d.getHours().toString().padStart(2, '0') + ":" +
										d.getMinutes().toString().padStart(2, '0') + ":" +
										d.getSeconds().toString().padStart(2, '0');
								var f1 = new Date(date_now) - new Date(item.fecha_creacion);
								var horas_atras = (f1 / (1000 * 60 * 60));

								if (parseInt(item.tipo_id) === 1) {
									if (parseInt(item.estado) === 0) {
										color = 'color: #595F64;';
										tipo = item.tipo_transaccion + ' - PENDIENTE';
									}
									if (parseInt(item.estado) === 1) {
										tipo = item.tipo_transaccion + ' - APROBADO';
										//color = 'color: #214483;';//AZUL
									}
									if (parseInt(item.estado) === 2) {
										tipo = item.tipo_transaccion + ' - RECHAZADO';
										//color = 'color: red;';
									}
									if (parseInt(item.estado) === 3) {
										tipo = item.tipo_transaccion + ' - ELIMINADO';
										//color = 'color: #EEA032;';//AMARILLO
									}
								}
								if (parseInt(item.tipo_id) === 26) {
									color = 'color: #214483;';//AZUL
								}
								if (parseInt(item.tipo_id) === 27) {
									color = 'color: red;';
								}
								if (parseInt(item.tipo_id) === 6) {
									color = 'color: red;';
								}
								if (parseInt(item.tipo_id) === 2) {
									color = 'color: green;';
								}
								if (parseInt(item.tipo_id) === 3) {
									color = 'color: red;';
								}
								
								if (parseInt(item.tipo_id) === 4) {
									if(parseInt(item.proveedor_id)===3){ // GOLDEN RACE
										tipo = 'VENTA GOLDEN';
										if (parseInt(item.estado) === 3) {
											tipo = 'VENTA GOLDEN - ANULADA';
											color = 'color: red;';
										}
									} else if(parseInt(item.proveedor_id)===2){ // ALTENAR
										if (parseInt(item.estado) === 3) {
											color = '';
										}
									} else if(parseInt(item.proveedor_id) === 4){ // BINGO
                                        tipo = 'VENTA BINGO';
                                        color = 'color: green;';
                                    } else if(parseInt(item.proveedor_id) === 8){ // GR VIRTUAL
                                        tipo = 'VENTA VIRTUAL GOLDEN';
                                    } else if(parseInt(item.proveedor_id) === 9){ // TORITO
                                        tipo = item.observacion_cajero;
                                    } else {
										if (parseInt(item.estado) === 3) {
											tipo = 'APUESTA ANULADA';
											color = 'color: red;';
										}
									}
									if(parseInt(item.is_bonus)===1){
										tipo += ' GRATIS';
									}
									if(sec_id === "televentas"){
										tipo += '<br>' + item.txn_id;
									}
								}

								if (parseInt(item.tipo_id) === 5) {
									if(parseInt(item.proveedor_id)===3){ // GOLDEN RACE
										tipo = 'PAGO GOLDEN';
										if (parseInt(item.estado) === 3) {
											tipo = tipo + ' - ANULADA';
											color = 'color: red;';
										}
									} else if(parseInt(item.proveedor_id) === 4){ // BINGO
                                        tipo = 'PAGO BINGO';
                                    } else if(parseInt(item.proveedor_id) === 8){ // GR VIRTUAL
                                        tipo = 'PAGO VIRTUAL GOLDEN';
                                    } else if(parseInt(item.proveedor_id) === 9){ // TORITO
                                        tipo = item.observacion_cajero;
                                    } else {
										if (parseInt(item.estado) === 3) {
											tipo = item.tipo_transaccion + ' - ANULADA';
											color = 'color: red;';
										}
									}
									if(sec_id === "televentas"){
										tipo += '<br>' + item.txn_id;
									}
								}

								if (parseInt(item.tipo_id) === 20) {
									if(parseInt(item.proveedor_id) == 4){
										tipo = 'PAGO JACKPOT BINGO';
									}else{
										tipo = 'PAGO JACKPOT GOLDEN';
									}
									if(sec_id === "televentas"){
										tipo += '<br>' + item.txn_id;
									}
								}

								// Apuesta Retornada
								if (parseInt(item.tipo_id) === 19) {
									if(sec_id === "televentas"){
										tipo += '<br>' + item.txn_id;
									}
								}

								// Apuesta Cancelada
								if (parseInt(item.tipo_id) === 34) {
									if(sec_id === "televentas"){
										tipo += '<br>' + item.txn_id;
									}
								}

								// Apuesta Cancelada
								if (parseInt(item.tipo_id) === 35 && parseInt(item.estado) === 1) {
									tipo += ' - ATENDIDA';
								}

								// Pago de dinero AT
								if (parseInt(item.tipo_id) === 36 ) {
									color = 'color: blue;';
								}

								// Transferir el dinero AT
								if (parseInt(item.tipo_id) === 37 ) {
									color = 'color: red;';
								}

								if (parseInt(item.tipo_id) === 7) {
									tipo = 'APUESTA GENERADA ANULADA';
									color = 'color: red;';
									if(parseInt(item.proveedor_id) == 4){
										if(parseInt(item.estado) == 4){
											tipo = 'BINGO CANCELADO';
										}else if(parseInt(item.estado) == 5){
											tipo = 'BINGO REEMBOLSADO';
										}
										if(sec_id === "televentas"){
											tipo += '<br>' + item.txn_id;
										}
										
									}
								}

								if (permiso_eliminar) {
									if([26,4,5,14,15,35].includes(parseInt(item.tipo_id))){
                                        if (	(parseInt(item.estado) === 1 && parseInt(item.proveedor_id) !== 2  && parseInt(item.proveedor_id) !== 8 && parseInt(item.proveedor_id) !== 10) ||
                                                (parseInt(item.tipo_id) == 14 && parseInt(item.estado) !== 3) ||
                                                (parseInt(item.tipo_id) == 35 && parseInt(item.estado) == 0)
                                            ) {

											if(![5,9].includes(parseInt(item.proveedor_id))){

												if ( parseInt(item.rollback_disponible) == 1 ) {

													if(item.caja_vip != 3){

													var fecha = "'" + item.fecha_creacion + "'";
													//if (parseInt(tls_login_area_id) !== 6 && horas_atras <= 24) {
														btn_eliminar = '<button type="button" class="btn btn-danger btn_eliminar_transaccion_class" style="padding: 2px 5px;"' +
																'    onclick="abrir_modal_eliminar(' + item.trans_id + ',' + item.tipo_id + ',' + item.proveedor_id + ',' + fecha + ')" ' + clase_eliminar_bloqueo + '>' +
																'<span class="fa fa-trash"></span>' +
																'</button>';
													//}
													/*
													if (parseInt(tls_login_area_id) === 6) {
														btn_eliminar = '<button type="button" class="btn btn-danger" style="padding: 2px 5px;"' +
																'    onclick="abrir_modal_eliminar(' + item.trans_id + ',' + item.tipo_id + ',' + item.proveedor_id + ',' + fecha + ')">' +
																'<span class="fa fa-trash"></span>' +
																'</button>';
													}
													*/

													}
												}
											}
										}
									}
								}

								if (item.trans_revert > 0 && (parseInt(item.tipo_id) === 4 || parseInt(item.tipo_id) === 5)) {
									tipo = item.tipo_transaccion + ' - ELIMINADO';
									color = 'color: #EEA032;';//AMARILLO
								}
								var nombre_operacion = '';
								if(item.tipo_operacion == 1){
									nombre_operacion = 'RETIRO';
								}else{

									nombre_operacion = 'DEVOLUCIÓN';
								}
								switch (parseInt(item.tipo_id)) {
									case 9:
										color = 'color: #595F64;';
										tipo = 'SOLICITUD ' + nombre_operacion;
										if (parseInt(item.estado) === 1) {

											if(item.caja_vip != 3){

											btn_eliminar = '<button type="button" class="btn btn-danger btn_eliminar_transaccion_class" style="padding: 2px 5px;"' +
													'    onclick="abrir_modal_cancelar_retiro(' + item.trans_id + ', ' + item.tipo_operacion + ')" ' + clase_eliminar_bloqueo + '>' +
													'<span class="fa fa-close"></span>' +
													'</button>';
											}
										} else if (parseInt(item.estado) === 5) { // En proceso
											color = 'color: #EEA032;';
											tipo = tipo + ' - EN PROCESO';
										} else if (parseInt(item.estado) === 6) { // En proceso
											color = 'color: #2111d6;';
											tipo = tipo + ' - VERIFICADO';
										}
										break;
									case 11:
										tipo = nombre_operacion + ' - PAGADO';
										color = 'color: #1b8e19;';
										if(item.caja_vip == 1){
											tipo = nombre_operacion + ' - PAGADO C7';
										}
										break;
									case 12:
										tipo = nombre_operacion + ' - RECHAZADO';
										color = 'color: red;';
										break;
									case 13:
										tipo = nombre_operacion + ' - CANCELADO';
										break;
									case 21:
										color = 'color: #595F64;';
										nombre_operacion = 'PROPINA';
										tipo = 'SOLICITUD ' + nombre_operacion;
										if (parseInt(item.estado) === 1) {
											btn_eliminar = '<button type="button" class="btn btn-danger btn_eliminar_transaccion_class" style="padding: 2px 5px;"' +
													'    onclick="abrir_modal_cancelar_propina(' + item.trans_id + ', ' + item.tipo_operacion + ')" ' + clase_eliminar_bloqueo + '>' +
													'<span class="fa fa-close"></span>' +
													'</button>';
										} else if (parseInt(item.estado) === 5) { // En proceso
											color = 'color: #EEA032;';
											tipo = tipo + ' - EN PROCESO';
										} else if (parseInt(item.estado) === 6) { // En proceso
											color = 'color: #2111d6;';
											tipo = tipo + ' - VERIFICADO';
										}
										break;
									case 22:
										nombre_operacion = 'PROPINA';
										tipo = nombre_operacion + ' - CANCELADO';
										break;
									case 28:
										color = 'color: #595F64;';
										tipo = 'SOLICITUD ' + nombre_operacion;
										if (parseInt(item.estado) === 1) {

											if(item.caja_vip != 3){

											btn_eliminar = '<button type="button" class="btn btn-danger btn_eliminar_transaccion_class" style="padding: 2px 5px;"' +
													'    onclick="abrir_modal_cancelar_retiro(' + item.trans_id + ', ' + item.tipo_operacion + ')" ' + clase_eliminar_bloqueo + '>' +
													'<span class="fa fa-close"></span>' +
													'</button>';
											}
										} else if (parseInt(item.estado) === 5) { // En proceso
											color = 'color: #EEA032;';
											tipo = tipo + ' - EN PROCESO';
										} else if (parseInt(item.estado) === 6) { // En proceso
											color = 'color: #2111d6;';
											tipo = tipo + ' - VERIFICADO';
										}
										break;
									case 29:
										tipo = nombre_operacion + ' - PAGADO';
										color = 'color: #1b8e19;';
										if(item.caja_vip == 1){
											tipo = nombre_operacion + ' - PAGADO C7';
										}
										break;
									case 30:
										tipo = nombre_operacion + ' - RECHAZADO';
										color = 'color: red;';
										break;
									case 31:
										tipo = nombre_operacion + ' - CANCELADO';
										break;
								}

								// Donacion Cancer
								var btn_img = '';
								if (parseInt(item.tipo_id) === 15) {
									btn_img = '<button type="button" class="btn btn-primary" style="padding: 2px 3px;" onclick="ver_img(';
									btn_img += "'" + item.archivo + "'";
									btn_img += ')"><span class="fa fa-photo"></span></button>';
									if(parseInt(item.estado)===3){
										tipo = tipo + ' - CANCELADO';
									}
								}
								var btn_img_propina = '';
								if(parseInt(item.tipo_id) === 21){
									btn_img_propina = '<button type="button" class="btn btn-primary" style="padding: 2px 3px;" onclick="ver_img_propina(';
									btn_img_propina += "'" + item.archivo + "'";
									btn_img_propina += ')"><span class="fa fa-photo"></span></button>';
								}
								if (parseInt(item.tipo_id) === 16) {
									color = 'color: red;';
								}

								if (parseInt(item.tipo_id) === 4 && parseInt(item.proveedor_id)===9) {
									btn_eliminar = '';
								}

								item.monto_deposito = item.monto_deposito.replace(/\D/g, "")
										.replace(/([0-9])([0-9]{2})$/, '$1.$2')
										.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
								item.monto = item.monto.replace(/\D/g, "")
										.replace(/([0-9])([0-9]{2})$/, '$1.$2')
										.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
								item.comision_monto = item.comision_monto.replace(/\D/g, "")
										.replace(/([0-9])([0-9]{2})$/, '$1.$2')
										.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
								item.bono_monto = item.bono_monto.replace(/\D/g, "")
										.replace(/([0-9])([0-9]{2})$/, '$1.$2')
										.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
								total = total.replace(/\D/g, "")
										.replace(/([0-9])([0-9]{2})$/, '$1.$2')
										.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

								if(parseFloat(item.nuevo_balance)<0){
									item.nuevo_balance = '-' + item.nuevo_balance.replace(/\D/g, "")
											.replace(/([0-9])([0-9]{2})$/, '$1.$2')
											.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
								} else {
									item.nuevo_balance = item.nuevo_balance.replace(/\D/g, "")
											.replace(/([0-9])([0-9]{2})$/, '$1.$2')
											.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
								}
								var obs_val = item.observacion_validador.replace(/(\r\n|\n|\r)/gm, " ");
								var obs_sup = item.observacion_supervisor.replace(/(\r\n|\n|\r)/gm, " ");
								var variables = "'" + item.trans_id + "','" + item.txn_id + "','" + item.tipo_id + "','" + web_id + "','" +
										gen_cliente_nombres + "','" +
										item.monto_deposito + "','" + item.comision_monto + "','" + item.monto + "','" + item.bono_nombre + "','" +
										item.bono_monto + "','" + item.total_recarga + "','" +
										item.fecha_creacion + "','" + item.estado + "','" + item.local + "','" + item.observacion_cajero + "','" +
										obs_val + "','" + obs_sup + "','" +
										item.tipo_rechazo + "','" + item.registro_deposito + "','" + item.num_operacion + "','" + 
										item.banco_pago + "','" + item.proveedor_id + "','" + item.proveedor_name + "','" + item.usuario + "','" + 
										item.cajero + "','" + item.tipo_operacion + "','" + item.motivo_dev + "', '" + item.archivo_comision + "', '" + item.tipo_jugada + "'";
								var btn_ver = '';
								if (parseInt(item.tipo_id) === 21 || parseInt(item.tipo_id) === 22 || parseInt(item.tipo_id) === 24 || parseInt(item.tipo_id) === 25){
									btn_ver = '<button type="button" class="btn btn-primary" style="padding: 2px 3px;"' +
											'    onclick="ver_voucher(' + variables + ')">' +
											'<i class="icon icon-inline glyphicon glyphicon-piggy-bank"></i>' +
											'</button>';
								}else if(parseInt(item.tipo_id) === 17 || parseInt(item.tipo_id) === 18 || parseInt(item.tipo_id) === 36|| parseInt(item.tipo_id) === 37 ){
									btn_ver = '';
									
								}else{
									if(parseInt(item.tipo_id) != 4 && parseInt(item.proveedor_id) != 5){
										btn_ver = '<button type="button" class="btn btn-primary" style="padding: 2px 3px;"' +
												'    onclick="ver_voucher(' + variables + ')">' +
												'<span class="fa fa-eye"></span>' +
												'</button>';
									}
								}
								if(parseInt(item.proveedor_id) === 9 && (parseInt(item.tipo_id) === 4 || parseInt(item.proveedor_id) === 5)){
									btn_ver = '<button type="button" class="btn btn-primary" style="padding: 2px 3px;"' +
											'    onclick="ver_voucher(' + variables + ')">' +
											'<span class="fa fa-eye"></span>' +
											'</button>';
								}

								if (parseInt(item.tipo_id) === 32) {
									tipo = item.tipo_transaccion + ' - PAGADO';
									color = 'color: green;';
								}

								if(parseInt(item.tipo_id) === 35 && parseInt(item.estado) === 0){
									//Reenviar solicitud recarga
									btn_ver = '<button type="button" class="btn btn-primary" style="padding: 2px 3px;"' +
											'    onclick="sec_tlv_reenviar_solicitud_recarga(' + item.trans_id + ')">' +
											'	<span class="fa fa-cloud-upload"></span>' +
											'</button>';
								}

								var column_nuevo_balance = '';
								if(sec_id === "televentas"){
									column_nuevo_balance = '<td class="text-right" width="6%">' + item.nuevo_balance + '</td>';
								}

								var column_txn_id = '';
								if(sec_id === "retail_altenar"){
									column_txn_id = '<td class="text-center" width="10%">' + item.txn_id + '</td>';
								}

								var local_tr = '';
								if(item.caja_vip == 3){
									local_tr = item.observacion_validador;
								}else{
									local_tr = item.local;
								}
								
								$('#tabla_transacciones').append(
										'<tr>' +
										'<td class="text-center" width="10%">' + item.fecha_creacion + '</td>' +
										'<td class="text-center" width="10%" style="' + color + '">' + tipo + '</td>' +
										column_txn_id +
										'<td class="text-center" width="10%">' + local_tr + '</td>' +
										'<td class="text-center" width="10%">' + item.cuenta + '</td>' +
										'<td class="text-right" width="7%">' + item.monto_deposito + '</td>' +
										'<td class="text-right" width="5%">' + item.comision_monto + '</td>' +
										'<td class="text-right" width="7%">' + item.monto + '</td>' +
										'<td class="text-right" width="5%">' + item.bono_monto + '</td>' +
										'<td class="text-right" width="8%">' + total + '</td>' +
										column_nuevo_balance +
										'<td class="text-right" width="6%">' + item.saldo + '</td>' +
										'<td class="text-center" width="16%">' +
										'<div class="btn-group" role="group" aria-label="Basic example">' +
										btn_ver +
										btn_eliminar +
										btn_img +
										btn_img_propina+
										'</div>' +
										'</td>' +
										'</tr>'
										);
							});
							tabla_transacciones_datatable_formato('#tabla_transacciones');
						} else {
							$('#tabla_transacciones').append(
									'<tr>' +
									'<td colspan="10" class="text-center">NO HAY DATOS</td>' +
									'</tr>'
									);
									
							if (respuesta.result_tr_cli == 0){
								$('#modal_fecha_nac').modal({backdrop: 'static', keyboard: false});
							}
							if (respuesta.result_tr_cli == 1){
								//$('#modal_imagen_perfil_lista').modal({backdrop: 'static', keyboard: false});
								sec_tlv_modal_cliente_imagenes(0);
							}
							//console.log(respuesta.result_tr_cli);
						}
						if (sec_tlv_module_client_is_open('sportsbook', (id_cliente))) {
							//console.log('aqui sportsbook: '+gen_cliente_id);
							//console.log('aqui sportsbook: '+id_cliente);
							setTimeout(function() {
								nuevo_portal_calimaco();
							}, 100);
						}
						if (sec_tlv_module_client_is_open('torito', (id_cliente))) {
							//console.log('aqui torito: '+gen_cliente_id);
							//console.log('aqui torito: '+id_cliente);
							setTimeout(function() {
								abrir_torito();
							}, 100);
						}

						if($('#sec_tlv_div_portal_altenar_individual_head_' + gen_cliente_id).length > 0) {
							var temp_balance_texto = 'Nombre: ' + $('#cliente_nombre').val() + ' ' + $('#cliente_apepaterno').val();
							if(parseInt($("#sec_tlv_cbx_tipo_balance").val())===1) {
								temp_balance_texto += ' - Balance actual: S/ ' + gen_balance_total;
							} else if (parseInt($("#sec_tlv_cbx_tipo_balance").val())===6) {
								temp_balance_texto += ' - Bono AT: S/ ' + gen_balance_dinero_at;

								if(parseFloat(anterior_balance_dinero_at)!==parseFloat(gen_balance_dinero_at))//quiere decir que el balance_dinero_at ha cambiado
								{	
									//eliminar sportbook
									//mandar mensaje
									//if (sec_tlv_sportsbook_client_is_open(gen_cliente_id)){
									if (sec_tlv_module_client_is_open('sportsbook', gen_cliente_id)){
										if ( parseFloat(gen_balance_dinero_at) <= 0 ){
											sec_tlv_div_portal_altenar_close();
										}
									}
								}
							}
							$('#sec_tlv_div_portal_altenar_individual_head_' + gen_cliente_id).html(temp_balance_texto);
						}

						if ( respuesta.rollover_status && respuesta.rollover_status == "SI" ) {
							var rollover_acumulado = parseFloat(respuesta.rollover_acumulado).toFixed(2);
							var rollover_meta = parseFloat(respuesta.rollover_meta).toFixed(2);
							var rollover_porcentaje = Math.floor( (rollover_acumulado*100)/rollover_meta );
							var rollover_conversion_maxima = parseFloat( respuesta.rollover_conversion_maxima ).toFixed(2);
							
							rollover_porcentaje > 100 ? rollover_porcentaje=100 : rollover_porcentaje;

							$("#rollover_calculo").html( "S/ " + rollover_acumulado + " de S/ " + rollover_meta );
							$("#dineroat_conversion_maxima").html( "S/ " + rollover_conversion_maxima );

							$("#progress_bar_rollover").css( "width",rollover_porcentaje+"%" );
							$("#progress_bar_rollover").html( rollover_porcentaje+"%" );

							if ( parseInt(rollover_porcentaje) === 100 ) {
								$("#btn_rollover_completo").css( "display","block" );
							} else {
								$("#btn_rollover_completo").css( "display","none" );
							}
							$("#dinero_at_rollover_on").css("display","block");
						} else {
							$("#dinero_at_rollover_on").css("display","none");
						}
						return false;
					}
				} catch (e) {
				//	console.log("Error de TRY-CATCH -- Error: " + e);
				}
			})
			.fail(function (xhr, status, error) {
			//	console.log("Error de .FAIL -- Error: " + error);
			});
}

function transferir_saldo_promocional_consultar() {

	$('#btn_rollover_completo').hide();
	
	swal({
		title: `<h3>¿Estás seguro de realizar el cobro? el monto restante a su conversión máxima se quedará en S/0.</h3>`,
		text: 'No hay vuelta atrás',
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Si, pagar",
		cancelButtonText: "No",
		closeOnConfirm: false,
		closeOnCancel: false,
		html: true,
	},
	function (opt) {
		if (opt) {
			// swal.close();
			loading(false);
			transferir_saldo_promocional();
			return false;
		} else {
			swal({
					title: "Estuvo cerca!",
					text: "¡Favor de revisar su conversión máxima en los T&C!",
					type: "success",
					timer: 2000,
					closeOnConfirm: true
				},
				function (opt) {
					$('#btn_rollover_completo').show();
					swal.close();
				});
		}
	});
	return false;
}

function transferir_saldo_promocional(){
	var	evento_dineroat_id = $("#evento_dineroat_id").val();

	if ( parseInt(evento_dineroat_id) === 0 ) {
		swal('Aviso', 'El pago de Dinero AT, es desde la opción de Saldo Promocional.', 'warning');
		$('#btn_rollover_completo').show();
		return false;
	}
	
	var data = new FormData();
	data.append('accion', "transferir_saldo_promocional");
	data.append('id_cliente', gen_cliente_id);
	data.append('evento_dineroat_id', evento_dineroat_id);

	auditoria_send({"proceso": "transferir_saldo_promocional_ajax", "data": (Object.fromEntries(data.entries()))});

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "transferir_saldo_promocional", "data": respuesta});
			console.log(respuesta);
			if (parseInt(respuesta.http_code) === 200) {
				$('#btn_rollover_completo').hide();
				swal('Aviso', respuesta.status, 'success');
				listar_transacciones(gen_cliente_id);
				// Luego de transferir el Saldo promocional, ya no deberia poder ver los btns
				$('.btn_dinero_at_virtuales').hide();
				$('.btn_dinero_at_bingo').hide();
				$('.btn_dinero_at_sportbook').hide();
				// FIN - Luego de transferir el Saldo promocional, ya no deberia poder ver los btns
				return false;
			} else if (parseInt(respuesta.http_code) === 400) {
				$('#btn_rollover_completo').show();
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			} else {
				swal('Aviso', 'Sin respuesta.', 'warning');
				return false;
			}
			return false;
		},
		error: function (result) {
			$('#btn_rollover_completo').show();
			swal('Aviso', result, 'warning');
			auditoria_send({"proceso": "transferir_saldo_promocional_error", "data": result});
			return false;
		}
	});
	return false;
}

function ver_img_propina(img){
	$('#modal_voucher_propina_imagen_referencia').removeAttr('src');
	$('#modal_voucher_propina_imagen_referencia').attr('src', 'files_bucket/propinas/' + img);
	$('#modal_voucher_propina_imagen').modal();
}

function ver_img(img){
	$('#modal_img_cancer_logo').removeAttr('src');
	$('#modal_img_cancer_logo').attr('src', 'files_bucket/depositos/' + img);
	$('#modal_img_cancer').modal();
}

function ver_voucher(trans_id, txn_id, tipo_id, web_id, cliente, monto_deposito, monto_comision, monto, bono_nombre, bono_monto, total,
		fecha_hora, estado, local, obs_cajero, obs_validador, obs_supervisor, tipo_rechazo, registro_deposito, num_operacion, banco_pago,
		proveedor_id, proveedor_name, usuario, cajero, tipo_operacion, motivo_dev, img_comision, tipo_jugada) {

	$('#modal_voucher_apuesta_logo').removeAttr('src');
	$('#modal_voucher_apuesta_logo').attr('src',"img/logo_at_voucher.jpeg");

	$('#modal_voucher_apuesta_tipo_torito_div').hide();
	$('#modal_voucher_apuesta_estado_div').show();

	if ([1,26].includes(parseInt(tipo_id))) { //Deposito

		$('#modal_ov_deposito_aprobado').hide();
		$('#modal_ov_obs_cajero_div').hide();
		$('#modal_ov_rechazo_div').hide();
		$('#modal_ov_obs_validador_div').hide();
		$('#modal_ov_obs_supervisor_div').hide();
		$('#sec_tlv_comision_comprobante_div').hide();

		$('#modal_ov_caja').html('');
		$('#modal_ov_fecha_hora').html('');
		$('#modal_ov_num_operacion').html('');
		$('#modal_ov_id_web').html('');
		$('#modal_ov_cliente').html('');
		$('#modal_ov_monto_deposito').html('');
		$('#modal_ov_monto_comision').html('');
		$('#modal_ov_monto_real').html('');
		$('#modal_ov_nombre_bono').html('');
		$('#modal_ov_monto_bono').html('');
		$('#modal_ov_tipo_jugada').html('');
		$('#modal_ov_monto_recarga').html('');

		$('#modal_ov_obs_cajero').html('');
		$('#modal_ov_rechazo').html('');
		$('#modal_ov_obs_validador').html('');
		$('#modal_ov_obs_supervisor').html('');


		$('#modal_ov_caja').html(local);
		$('#modal_ov_fecha_hora').html(registro_deposito.substr(0, 16));
		$('#modal_ov_num_operacion').html(num_operacion);
		$('#modal_ov_id_web').html(web_id);
		$('#modal_ov_cliente').html(cliente);
		$('#modal_ov_monto_deposito').html(monto_deposito);
		$('#modal_ov_monto_comision').html(monto_comision);
		$('#modal_ov_monto_real').html(monto);
		$('#modal_ov_nombre_bono').html(bono_nombre);
		$('#modal_ov_monto_bono').html(bono_monto);
		$('#modal_ov_tipo_jugada').html(tipo_jugada);
		$('#modal_ov_monto_recarga').html(total);

		$('#modal_ov_obs_cajero').html(obs_cajero);
		$('#modal_ov_rechazo').html(tipo_rechazo);
		$('#modal_ov_obs_validador').html(obs_validador);
		$('#modal_ov_obs_supervisor').html(obs_supervisor);

		if (parseInt(estado) === 0) {
			$('#modal_ov_obs_cajero_div').show();
			$('#modal_ov_deposito_aprobado').show();
		}
		if (parseInt(estado) === 1) {
			$('#modal_ov_deposito_aprobado').show();
			$('#modal_ov_obs_validador_div').show();
			if(img_comision.length > 0){
				$('#sec_tlv_img_comprobante_comision').removeAttr('src');
				$('#sec_tlv_img_comprobante_comision').attr('src', 'files_bucket/depositos/' + img_comision);
				$("#sec_tlv_img_comprobante_comision").imgViewer2();
				$('#sec_tlv_comision_comprobante_div').show();
			}else{
				$('#sec_tlv_comision_comprobante_div').hide();
			}
		}
		if (parseInt(estado) === 2) {
			$('#modal_ov_obs_cajero_div').show();
			$('#modal_ov_obs_validador_div').show();
			$('#modal_ov_rechazo_div').show();
		}
		if (parseInt(estado) === 3) {
			$('#modal_ov_obs_cajero_div').show();
			$('#modal_ov_obs_validador_div').show();
			$('#modal_ov_obs_supervisor_div').show();
		}

		$('#modal_observacion_validador').modal();
	}

	if (parseInt(tipo_id) === 2 || parseInt(tipo_id) === 3) { //Recarga web y su rollback
		$('#modal_voucher_caja').html('');
		$('#modal_voucher_idtransaccion').html('');
		$('#modal_voucher_fechahora').html('');
		$('#modal_voucher_idweb').html('');
		$('#modal_voucher_cliente').html('');
		$('#modal_voucher_monto').html('');
		$('#modal_voucher_bono_nombre').html('');
		$('#modal_voucher_bono').html('');
		$('#modal_voucher_total').html('');



		$('#modal_voucher_caja').html(local);
		$('#modal_voucher_idtransaccion').html(txn_id);
		$('#modal_voucher_fechahora').html(fecha_hora);
		$('#modal_voucher_idweb').html(web_id);
		$('#modal_voucher_cliente').html(cliente);
		$('#modal_voucher_monto').html(monto);
		$('#modal_voucher_total').html(total);

		$('#modal_voucher').modal();

		$.post("/sys/set_televentas.php", {
			accion: "obtener_recarga_x_bono",
			recarga_id: trans_id
		})
				.done(function (data) {
					try {
						//console.log(data);
						var respuesta = JSON.parse(data);
						if (parseInt(respuesta.http_code) == 200) {
							$('#modal_voucher_bono_nombre').html(respuesta.bono_nombre);
							$('#modal_voucher_bono').html(respuesta.bono_monto);
						} else {
							$('#modal_voucher_bono_nombre').html('Ninguno');
							$('#modal_voucher_bono').html('0.00');
						}
					} catch (e) {
						swal('¡Error!', e, 'error');
					//	console.log("Error de TRY-CATCH --> Error: " + e);
					}
				})
				.fail(function (xhr, status, error) {
					swal('¡Error!', error, 'error');
				//	console.log("Error de .FAIL -- Error: " + error);
				});

	}

	if (parseInt(tipo_id) === 4) { //Apuesta
		if (parseInt(proveedor_id) === 5) {
			var temp_fecha = fecha_hora.substr(0, 10);
			var temp_fecha_format = temp_fecha.substr(-2) + '/' + temp_fecha.substr(5, 2) + '/' + temp_fecha.substr(0, 4);
			$('#modal_voucher_apuesta_altenar_fecha').html('');
			$('#modal_voucher_apuesta_altenar_hora').html('');
			$('#modal_voucher_apuesta_altenar_id_bet').html('');
			$('#modal_voucher_apuesta_altenar_caja').html('');
			$('#modal_voucher_apuesta_altenar_monto').html('');
			$('#modal_voucher_apuesta_altenar_qr_id_bet').html('');
			$('#modal_voucher_apuesta_altenar_tabla').html('');
			$('#modal_voucher_apuesta_altenar_qr').html('');
			$('#modal_voucher_apuesta_altenar_estado').html('');


			$('#modal_voucher_apuesta_altenar_fecha').html('Fecha: ' + temp_fecha_format);
			$('#modal_voucher_apuesta_altenar_hora').html('Hora: ' + fecha_hora.substr(-8));
			$('#modal_voucher_apuesta_altenar_id_bet').html('ID de apuesta: ' + txn_id);
			$('#modal_voucher_apuesta_altenar_cliente_name').html('Cliente: ' + cliente);
			$('#modal_voucher_apuesta_altenar_caja').html(local);
			$('#modal_voucher_apuesta_altenar_monto').html(monto + ' PEN');
			$('#modal_voucher_apuesta_altenar_qr_id_bet').html(txn_id);

			$('#modal_voucher_apuesta_altenar_qr').html('<img id="modal_voucher_apuesta_altenar_qr_img" height="100" />');
			JsBarcode("#modal_voucher_apuesta_altenar_qr_img", txn_id);

			var data = {
				"accion": "obtener_apuesta_altenar",
				"id_cliente": gen_cliente_id,
				"id_bet": txn_id
			}
			$.ajax({
				url: "/sys/set_televentas.php",
				type: 'POST',
				data: data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (data) { //  alert(datat)
					var respuesta = JSON.parse(data);
					auditoria_send({"proceso": "obtener_apuesta_altenar", "data": respuesta});
					if (parseInt(respuesta.http_code) == 200) {
						var tipo_apuesta_texto = 'Simple';
						var tipo_apuesta = (respuesta.result_calimaco).length;
						if (parseInt(tipo_apuesta) > 1) {
							tipo_apuesta_texto = 'Múltiple';
						}
						$('#modal_voucher_apuesta_altenar_tabla').append(
								'<tr class="modal_voucher_apuesta_altenar_tabla_tr" style="text-align:center;">' +
								'<td><b>' + tipo_apuesta_texto + '</b></td>' +
								'</tr>'
								);
						$.each(respuesta.result_calimaco, function (index, item) {
							$('#modal_voucher_apuesta_altenar_tabla').append(
									'<tr class="modal_voucher_apuesta_altenar_tabla_tr">' +
									'<td>' +
									item.category_name + ', ' + item.championship_name +
									'<br>' +
									item.event_date +
									'<br>' +
									'<b>' + item.event_name + '</b>' +
									'<hr class="modal_voucher_apuesta_altenar_tabla_hr">' +
									'<div class="row">' +
									'<div class="form-group">' +
									'<label class="col-sm-8 control-label" style="text-align: left;color: black;font-weight: 100;">' +
									'<b>Mercado:</b> ' + item.market_name + ' - ' + item.selection +
									'</label>' +
									'<label class="col-sm-4 control-label" style="text-align: left;color: black;font-weight: 100;">' +
									'<b>Cuota:</b> ' + item.odds +
									'</label>' +
									'</div>' +
									'</div>' +
									'</td>' +
									'</tr>'
									);
						});
						$('#modal_voucher_apuesta_altenar_tabla').append(
								'<tr class="modal_voucher_apuesta_altenar_tabla_tr" style="text-align:center;">' +
								'<td>' +
								'<div class="row">' +
								'<div class="form-group">' +
								'<label class="col-sm-8 control-label" style="text-align: left;color: black;font-weight: 100;">Cuota:</label>' +
								'<label class="col-sm-4 control-label" style="text-align: right;color: black;font-weight: 100;"><b>' + (parseFloat(respuesta.total_cuota)).toFixed(3) + '</b></label>' +
								'</div>' +
								'</div>' +
								'<div class="row">' +
								'<div class="form-group">' +
								'<label class="col-sm-8 control-label" style="text-align: left;color: black;font-weight: 100;"><b>Ganacia estimada:</b></label>' +
								'<label class="col-sm-4 control-label" style="text-align: right;color: black;font-weight: 100;"><b>' + respuesta.ganacia_estimada + ' PEN</b></label>' +
								'</div>' +
								'</div>' +
								'</td>' +
								'</tr>'
								);
						$('#modal_voucher_apuesta_altenar_estado').css('color', respuesta.status_color);
						$('#modal_voucher_apuesta_altenar_estado').html(respuesta.status);
						if (parseInt(estado) === 3) {
							$('#modal_voucher_apuesta_altenar_estado').css('color', 'red');
							$('#modal_voucher_apuesta_altenar_estado').html('ANULADO');
						}
						$('#modal_voucher_apuesta_altenar').modal();
					} else {
						$('#modal_voucher_apuesta_altenar').modal();
					}
					return false;
				},
				error: function (result) {
					auditoria_send({"proceso": "obtener_apuesta_altenar_error", "data": result});
					return false;
				}
			});
			return false;
		}else if(parseInt(proveedor_id) == 4){
			sec_tlv_bingo_get_voucher(trans_id,estado);
		} else if(parseInt(proveedor_id) == 9){
			$('#modal_voucher_apuesta_logo').removeAttr("src");
			$('#modal_voucher_apuesta_logo').attr('src',"img/logo_torito.png");

			$('#modal_voucher_apuesta_caja').html('');
			$('#modal_voucher_apuesta_proveedor').html('');
			$('#modal_voucher_apuesta_tipo_torito').html('');
			$('#modal_voucher_apuesta_idtransaccion').html('');
			$('#modal_voucher_apuesta_fechahora').html('');
			$('#modal_voucher_apuesta_estado').html('');
			$('#modal_voucher_apuesta_monto').html('');

			$('#modal_voucher_apuesta_tipo_torito_div').show();
			$('#modal_voucher_apuesta_estado_div').hide();

			$('#modal_voucher_apuesta_caja').html(local);
			$('#modal_voucher_apuesta_proveedor').html(proveedor_name);
			$('#modal_voucher_apuesta_tipo_torito').html(obs_cajero);
			$('#modal_voucher_apuesta_idtransaccion').html(txn_id);
			$('#modal_voucher_apuesta_fechahora').html(fecha_hora);
			$('#modal_voucher_apuesta_monto').html(monto);

			$('#modal_voucher_apuesta').modal();
		} else {
			$('#modal_voucher_apuesta_caja').html('');
			$('#modal_voucher_apuesta_proveedor').html('');
			$('#modal_voucher_apuesta_idtransaccion').html('');
			$('#modal_voucher_apuesta_fechahora').html('');
			$('#modal_voucher_apuesta_estado').html('');
			$('#modal_voucher_apuesta_monto').html('');

			$('#modal_voucher_apuesta_estado').html('Apuesta');

			$('#modal_voucher_apuesta_caja').html(local);
			$('#modal_voucher_apuesta_proveedor').html(proveedor_name);
			$('#modal_voucher_apuesta_idtransaccion').html(txn_id);
			$('#modal_voucher_apuesta_fechahora').html(fecha_hora);
			$('#modal_voucher_apuesta_monto').html(monto);

			$('#modal_voucher_apuesta').modal();
		}
	}

	if (parseInt(tipo_id) === 7 && parseInt(proveedor_id)===2) { // Apuesta Altenar ANULADA
		$('#modal_voucher_ap_anulada_caja').html('');
		$('#modal_voucher_ap_anulada_promotor').html('');
		$('#modal_voucher_ap_anulada_proveedor').html('');
		$('#modal_voucher_ap_anulada_idtransaccion').html('');
		$('#modal_voucher_ap_anulada_fechahora').html('');
		$('#modal_voucher_ap_anulada_estado').html('');
		$('#modal_voucher_ap_anulada_monto').html('');

		$('#modal_voucher_ap_anulada_estado').html('Apuesta');

		$('#modal_voucher_ap_anulada_caja').html(local);
		$('#modal_voucher_ap_anulada_promotor').html(cajero);
		$('#modal_voucher_ap_anulada_proveedor').html(proveedor_name);
		$('#modal_voucher_ap_anulada_idtransaccion').html(txn_id);
		$('#modal_voucher_ap_anulada_fechahora').html(fecha_hora);
		$('#modal_voucher_ap_anulada_monto').html(monto);

		$('#modal_voucher_ap_anulada').modal();
	}else if(parseInt(tipo_id) === 7 && parseInt(proveedor_id) === 4){
		sec_tlv_bingo_get_voucher(trans_id, estado);
	}

	if ([5, 19, 20].includes(parseInt(tipo_id))) { //Apuesta Pagada, Retornada, Jackpot
		$('#modal_voucher_apuesta_caja').html('');
		$('#modal_voucher_apuesta_proveedor').html('');
		$('#modal_voucher_apuesta_idtransaccion').html('');
		$('#modal_voucher_apuesta_fechahora').html('');
		$('#modal_voucher_apuesta_estado').html('');
		$('#modal_voucher_apuesta_monto').html('');

		if (parseInt(tipo_id) === 5) {
			$('#modal_voucher_apuesta_estado').html('Apuesta Pagada');
		}
		if (parseInt(tipo_id)===19) {
			$('#modal_voucher_apuesta_estado').html('Apuesta Retornada');
		}
		if (parseInt(tipo_id)===20) {
			$('#modal_voucher_apuesta_estado').html('Apuesta Pagada Jackpot');
		}

		$('#modal_voucher_apuesta_caja').html(local);
		$('#modal_voucher_apuesta_proveedor').html(proveedor_name);
		$('#modal_voucher_apuesta_idtransaccion').html(txn_id);
		$('#modal_voucher_apuesta_fechahora').html(fecha_hora);
		$('#modal_voucher_apuesta_monto').html(monto);

		$('#modal_voucher_apuesta').modal();
	}

	if ([9, 11, 12, 13].includes(parseInt(tipo_id))) {
		$('#sec_tlv_title_solicitud_retiro').html('SOLICITUD DE RETIRO');
		$("#sec_tlv_id_trans_retiro").val(trans_id);
		$('#sec_tlv_modal_solicitud_de_retiro_caja').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_fechahora').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_estado').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_monto').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_div_motivo_rechazo').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_motivo_rechazo').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_obs_pagador').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_div_obs_supervisor').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_obs_supervisor').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_voucher').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_div_motivo_cancelacion').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_motivo_cancelacion').html('');
		$('#sec_tlv_btn_copiar_imagen').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_banco_pago_div').css({"display": "none"});
		var estado_nombre = "";

		if (parseInt(tipo_id) == 9) {
			switch (parseInt(estado)) {
				case 1:
					estado_nombre = "PENDIENTE";
					break;
				case 2:
					estado_nombre = "PAGADO";
					break;
				case 3:
					estado_nombre = "RECHAZADA";
					break;
				case 4:
					estado_nombre = "CANCELADA";
					break;
				case 5:
					estado_nombre = "EN PROCESO";
					break;
				case 6:
					estado_nombre = "VERIFICADO";
					break;
			}
		} else if (parseInt(tipo_id) == 11 && parseInt(estado) == 2) {
			estado_nombre = "PAGADO";
			$('#sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_voucher').css({"display": "block"});
			$('#sec_tlv_btn_copiar_imagen').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_banco_pago_div').css({"display": "block"});
			cargar_voucher_retiro_pagado(trans_id);
		} else if (parseInt(tipo_id) == 12 && parseInt(estado) == 3) {
			estado_nombre = "RECHAZADA";
			$('#sec_tlv_modal_solicitud_de_retiro_div_motivo_rechazo').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_motivo_rechazo').html(tipo_rechazo);
			$('#sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "block"});
		} else if (parseInt(tipo_id) == 13 && parseInt(estado) == 4) {
			estado_nombre = "CANCELADA";
			$('#sec_tlv_modal_solicitud_de_retiro_div_obs_supervisor').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_div_motivo_cancelacion').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_obs_supervisor').html(obs_supervisor);
			$('#sec_tlv_modal_solicitud_de_retiro_motivo_cancelacion').html(tipo_rechazo);
		} else if (parseInt(estado) == 5) {
			estado_nombre = "EN PROCESO";
		}

		$('#sec_tlv_modal_solicitud_de_retiro_estado').html(estado_nombre);
		$('#sec_tlv_modal_solicitud_de_retiro_caja').html(local);
		$('#sec_tlv_modal_solicitud_de_retiro_fechahora').html(fecha_hora);
		$('#sec_tlv_modal_solicitud_de_retiro_monto').html(monto);
		$('#sec_tlv_modal_solicitud_de_retiro_obs_pagador').html(obs_validador);
		$('#sec_tlv_modal_solicitud_de_retiro_banco_pago').html(banco_pago);
		var operacion_name = '';		
		if(tipo_operacion == 0 || tipo_operacion == 1){
			operacion_name = 'PAGO';
		}else if(tipo_operacion == 2){
			operacion_name = 'DEVOLUCIÓN';
		}
		$('#sec_tlv_modal_solicitud_de_retiro_tipo_operacion').html(operacion_name);		
		$('#sec_tlv_modal_solicitud_de_retiro_motivo_devolucion').html(motivo_dev);

		$('#sec_tlv_modal_solicitud_de_retiro').modal();
	}

	if (parseInt(tipo_id) === 14) {
		$('#modal_voucher_td_caja').html('');
		$('#modal_voucher_td_proveedor').html('');
		$('#modal_voucher_td_idtransaccion').html('');
		$('#modal_voucher_td_fechahora').html('');
		$('#modal_voucher_td_estado').html('');
		$('#modal_voucher_td_monto').html('');

		$('#modal_voucher_td_estado').html('Terminal Deposit - In');

		$('#modal_voucher_td_caja').html(local);
		$('#modal_voucher_td_proveedor').html(proveedor_name);
		$('#modal_voucher_td_idtransaccion').html(txn_id);
		$('#modal_voucher_td_fechahora').html(fecha_hora);
		$('#modal_voucher_td_monto').html(monto);

		$('#modal_voucher_terminal_deposit').modal();
	}

    if ([15,16].includes(parseInt(tipo_id))){
        $('#modal_voucher_cancer_caja').html('');
        $('#modal_voucher_cancer_proveedor').html('');
        $('#modal_voucher_cancer_txn').html('');
        $('#modal_voucher_cancer_fechahora').html('');
        $('#modal_voucher_cancer_estado').html('');
        $('#modal_voucher_cancer_monto').html('');

        var estado_nombre = 'Activo';
        if(parseInt(estado)===3){
        	estado_nombre = 'Anulado';
        }

        $('#modal_voucher_cancer_titulo').html('¡Gracias ' + $('#cliente_nombre').val().split(" ")[0] + '!');
        $('#modal_voucher_cancer_caja').html(local);
        $('#modal_voucher_cancer_proveedor').html(proveedor_name);
        $('#modal_voucher_cancer_txn').html(pad(txn_id, 10));
        $('#modal_voucher_cancer_fechahora').html(fecha_hora);
        $('#modal_voucher_cancer_estado').html(estado_nombre);
        $('#modal_voucher_cancer_monto').html(monto);

        $('#modal_voucher_cancer').modal();
    }

	if ([21, 22, 24, 25].includes(parseInt(tipo_id)) /* parseInt(tipo_id) === 21 */){ // Propinas
		$("#sec_tlv_id_trans_propina").val(trans_id);
        $('#modal_voucher_propina_caja').html('');
        $('#modal_voucher_propina_proveedor').html('');
        $('#modal_voucher_propina_txn').html('');
        $('#modal_voucher_propina_fechahora').html('');
        $('#modal_voucher_propina_estado').html('');
        $('#modal_voucher_propina_monto').html('');
		$('#sec_tlv_modal_solicitud_de_propina_obs_cajero').html('');

		$('#sec_tlv_modal_solicitud_de_propina_div_obs_pagador').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_propina_voucher').css({"display": "none"});
		$('#sec_tlv_btn_copiar_imagen_propina').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_propina_banco_pago_div').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_propina_div_motivo_rechazo').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_propina_motivo_rechazo').html('');
		var estado_nombre = "";

		// $('#modal_voucher_propina_titulo').html('¡Gracias ' + $('#cliente_nombre').val().split(" ")[0] + '!');
		$('#modal_voucher_propina_titulo').html('SOLICITUD DE PROPINA');
        $('#modal_voucher_propina_caja').html(local);
        $('#modal_voucher_propina_proveedor').html(proveedor_name);
        $('#modal_voucher_propina_txn').html(pad(txn_id, 10));
        $('#modal_voucher_propina_fechahora').html(fecha_hora);
        $('#modal_voucher_propina_estado').html(estado_nombre);
        $('#modal_voucher_propina_monto').html(monto);
		$('#sec_tlv_modal_solicitud_de_propina_estado').html(estado_nombre);
		$('#sec_tlv_modal_solicitud_de_propina_obs_cajero').html(obs_cajero);
		$('#sec_tlv_modal_solicitud_de_propina_obs_pagador').html(obs_validador);
		$('#sec_tlv_modal_solicitud_de_propina_banco_pago').html(banco_pago);
		var operacion_name = '';		
		if(tipo_operacion == 0 || tipo_operacion == 1){
			operacion_name = 'PAGO';
		}else if(tipo_operacion == 2){
			operacion_name = 'DEVOLUCIÓN';
		}
		$('#sec_tlv_modal_solicitud_de_propina_tipo_operacion').html(operacion_name);		
		$('#sec_tlv_modal_solicitud_de_propina_motivo_devolucion').html(motivo_dev);

		if (parseInt(tipo_id) == 21) {
			switch (parseInt(estado)) {
				case 1:
					estado_nombre = "PENDIENTE";
					break;
				case 2:
					estado_nombre = "PAGADO";
					break;
				case 3:
					estado_nombre = "RECHAZADA";
					break;
				case 4:
					estado_nombre = "CANCELADA";
					break;
				case 5:
					estado_nombre = "EN PROCESO";
					break;
				case 6:
					estado_nombre = "VERIFICADO";
					break;
			}
		} else if (parseInt(tipo_id) == 24 && parseInt(estado) == 2) {
			estado_nombre = "PAGADO";
			$('#modal_voucher_propina_txn_div').hide();
			$('#sec_tlv_modal_solicitud_de_propina_div_obs_cajero').css({"display": "none"});
			$('#sec_tlv_modal_solicitud_de_propina_div_obs_pagador').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_propina_voucher').css({"display": "block"});
			$('#sec_tlv_btn_copiar_imagen_propina').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_propina_banco_pago_div').css({"display": "block"});
			cargar_voucher_de_propina_pagada(trans_id); // Trae el voucher del validador, tanto de retiro como propina
		} else if (parseInt(tipo_id) == 25 && parseInt(estado) == 3) {
			estado_nombre = "RECHAZADA";
			$('#modal_voucher_propina_txn_div').hide();
			$('#sec_tlv_modal_solicitud_de_propina_div_motivo_rechazo').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_propina_motivo_rechazo').html(tipo_rechazo);
			// $('#sec_tlv_modal_solicitud_de_propina_div_obs_cajero').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_propina_div_obs_pagador').css({"display": "block"});
		/* } else if (parseInt(tipo_id) == 13 && parseInt(estado) == 4) {
			estado_nombre = "CANCELADA";
			$('#sec_tlv_modal_solicitud_de_retiro_div_obs_supervisor').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_div_motivo_cancelacion').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_obs_supervisor').html(obs_supervisor);
			$('#sec_tlv_modal_solicitud_de_retiro_motivo_cancelacion').html(tipo_rechazo); */
		/* } else if (parseInt(tipo_id) == 24 && parseInt(estado) == 2) {
			estado_nombre = "PAGADO";
			$('#sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_voucher').css({"display": "block"});
			$('#sec_tlv_btn_copiar_imagen').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_banco_pago_div').css({"display": "block"});
			cargar_voucher_propina_pagado(trans_id);
		} else if (parseInt(estado) == 5) { */
			estado_nombre = "EN PROCESO";
		}

        

        $('#modal_voucher_propina').modal();
	}

	if ([28,29,30,31].includes(parseInt(tipo_id))) {
		$('#sec_tlv_title_solicitud_retiro').html('SOLICITUD DE DEVOLUCIÓN');
		$("#sec_tlv_id_trans_retiro").val(trans_id);
		$('#sec_tlv_modal_solicitud_de_retiro_caja').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_fechahora').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_estado').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_monto').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_div_motivo_rechazo').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_motivo_rechazo').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_obs_pagador').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_div_obs_supervisor').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_obs_supervisor').html('');
		$('#sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_voucher').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_div_motivo_cancelacion').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_motivo_cancelacion').html('');
		$('#sec_tlv_btn_copiar_imagen').css({"display": "none"});
		$('#sec_tlv_modal_solicitud_de_retiro_banco_pago_div').css({"display": "none"});
		var estado_nombre = "";

		if (parseInt(tipo_id) == 28) {
			switch (parseInt(estado)) {
				case 1:
					estado_nombre = "PENDIENTE";
					break;
				case 2:
					estado_nombre = "PAGADO";
					break;
				case 3:
					estado_nombre = "RECHAZADA";
					break;
				case 4:
					estado_nombre = "CANCELADA";
					break;
				case 5:
					estado_nombre = "EN PROCESO";
					break;
				case 6:
					estado_nombre = "VERIFICADO";
					break;
			}
		} else if (parseInt(tipo_id) == 29 && parseInt(estado) == 2) {
			estado_nombre = "PAGADO";
			$('#sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_voucher').css({"display": "block"});
			$('#sec_tlv_btn_copiar_imagen').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_banco_pago_div').css({"display": "block"});
			cargar_voucher_retiro_pagado(trans_id);
		} else if (parseInt(tipo_id) == 30 && parseInt(estado) == 3) {
			estado_nombre = "RECHAZADA";
			$('#sec_tlv_modal_solicitud_de_retiro_div_motivo_rechazo').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_motivo_rechazo').html(tipo_rechazo);
			$('#sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "block"});
		} else if (parseInt(tipo_id) == 31 && parseInt(estado) == 4) {
			estado_nombre = "CANCELADA";
			$('#sec_tlv_modal_solicitud_de_retiro_div_obs_supervisor').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_div_motivo_cancelacion').css({"display": "block"});
			$('#sec_tlv_modal_solicitud_de_retiro_obs_supervisor').html(obs_supervisor);
			$('#sec_tlv_modal_solicitud_de_retiro_motivo_cancelacion').html(tipo_rechazo);
		} else if (parseInt(estado) == 5) {
			estado_nombre = "EN PROCESO";
		}

		$('#sec_tlv_modal_solicitud_de_retiro_estado').html(estado_nombre);
		$('#sec_tlv_modal_solicitud_de_retiro_caja').html(local);
		$('#sec_tlv_modal_solicitud_de_retiro_fechahora').html(fecha_hora);
		$('#sec_tlv_modal_solicitud_de_retiro_monto').html(monto);
		$('#sec_tlv_modal_solicitud_de_retiro_obs_pagador').html(obs_validador);
		$('#sec_tlv_modal_solicitud_de_retiro_banco_pago').html(banco_pago);
		var operacion_name = '';		
		if(tipo_operacion == 0 || tipo_operacion == 1){
			operacion_name = 'PAGO';
		}else if(tipo_operacion == 2){
			operacion_name = 'DEVOLUCIÓN';
		}
		$('#sec_tlv_modal_solicitud_de_retiro_tipo_operacion').html(operacion_name);		
		$('#sec_tlv_modal_solicitud_de_retiro_motivo_devolucion').html(motivo_dev);

		$('#sec_tlv_modal_solicitud_de_retiro').modal();
	}

	if (parseInt(tipo_id) === 32){ // Pago Sorteo del Mundial
        $('#modal_voucher_sorteo_mundial_cliente').html('');
        $('#modal_voucher_sorteo_mundial_caja').html('');
        $('#modal_voucher_sorteo_mundial_proveedor').html('');
        $('#modal_voucher_sorteo_mundial_txn').html('');
        $('#modal_voucher_sorteo_mundial_fechahora').html('');
        $('#modal_voucher_sorteo_mundial_estado').html('');
        $('#modal_voucher_sorteo_mundial_monto').html('');

        $('#modal_voucher_sorteo_mundial_titulo').html('¡Sorteo del Mundial Qatar 2022!');
        $('#modal_voucher_sorteo_mundial_cliente').html($('#cliente_nombre').val() + ' ' + $('#cliente_apepaterno').val() + ' ' + $('#cliente_apematerno').val() );
        $('#modal_voucher_sorteo_mundial_caja').html(local);
        $('#modal_voucher_sorteo_mundial_proveedor').html(proveedor_name);
        $('#modal_voucher_sorteo_mundial_txn').html(pad(txn_id, 10));
        $('#modal_voucher_sorteo_mundial_fechahora').html(fecha_hora);
        $('#modal_voucher_sorteo_mundial_monto').html(monto);

        $('#modal_voucher_sorteo_mundial').modal();
    }

    if (parseInt(tipo_id) === 33){ // Terminal Tambo
    	var dia_tb = fecha_hora.substring(8,10) + '/' + fecha_hora.substring(5,7) + '/' + fecha_hora.substring(0,4);
    	$('#sec_tlv_modal_tambo_fecha').html(dia_tb);
    	$('#sec_tlv_modal_tambo_hora').html(fecha_hora.substring(11, 19));
    	$('#sec_tlv_modal_tambo_cliente').html($('#cliente_nombre').val() + ' ' + $('#cliente_apepaterno').val() + ' ' + $('#cliente_apematerno').val());
    	$('#sec_tlv_modal_tambo_monto').html(parseFloat(monto) + ' PEN');
    	$('#sec_tlv_modal_tambo_id_barcode').val(num_operacion);
    	JsBarcode("#sec_tlv_img_barcode_tambo_voucher", num_operacion);
        $('#sec_tlv_modal_voucher_terminal_tambo').modal();
    }
}

function cargar_voucher_retiro_pagado(id_transaccion) {
	var data = {
		"accion": "obtener_imagenes_x_transaccion_retiro",
		"id_transaccion": id_transaccion
	}
	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) { //  alert(datat)
			$('#sec_tlv_div_dp_voucher_retiro').html('');
			var respuesta = JSON.parse(resp);
			auditoria_send({"proceso": "obtener_imagenes_x_transaccion_retiro", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {

				//swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$.each(respuesta.result, function (index, item) {
					var nuevo_id = (respuesta.fecha_hora).toString() + '_' + (item.id).toString();
					$('#sec_tlv_div_dp_voucher_retiro').append(
							'<div class="col-md-12">' +
							'   <div align="center">' +
							'       <img  id="sec_tlv_imagen_comprobante_pago" src="files_bucket/retiros/' + item.archivo + '" style="max-width: 450px;"/>' +
							'   </div>' +
							'</div>'
							);
					$("#sec_tlv_imagen_comprobante_pago").imgViewer2();
				});
				return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_imagenes_x_transaccion_retiro_error", "data": result});
		}
	});
}

function cargar_voucher_de_propina_pagada(id_transaccion) {
	var data = {
		"accion": "obtener_imagenes_x_transaccion_propina",
		"id_transaccion": id_transaccion
	}
	auditoria_send({"proceso": "obtener_imagenes_x_transaccion_retiro", "data": data});
	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) { //  alert(datat)
			$('#sec_tlv_div_dp_voucher_propina').html('');
			var respuesta = JSON.parse(resp);
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {

								//swal('Aviso', respuesta.status, 'warning');
								return false;
							}
							if (parseInt(respuesta.http_code) == 200) {
								$.each(respuesta.result, function (index, item) {
									var nuevo_id = (respuesta.fecha_hora).toString() + '_' + (item.id).toString();
									$('#sec_tlv_div_dp_voucher_propina').append(
											'<div class="col-md-12">' +
											'   <div align="center">' +
											'       <img  id="sec_tlv_imagen_comprobante_pago_propina" src="files_bucket/retiros/' + item.archivo + '" style="max-width: 350px;"/>' +
											'   </div>' +
											'</div>'
											);
									$("#sec_tlv_imagen_comprobante_pago_propina").imgViewer2();
								});
								return false;
							}
						},
						error: function () {}
					});
				}

function limpiar_campos_div_cliente() {
	$('#cliente_tipo_doc').val('');
	$('#cliente_num_doc').val('');
	$('#cliente_celular').val('');
	$('#cliente_fec_nac').val('');
	$('#cliente_idweb').val('');
	$('#cliente_webfullname').val('');
	$('#cliente_idjugador').val('');
	$('#cliente_nombre').val('');
	$('#cliente_apepaterno').val('');
	$('#cliente_apematerno').val('');
	$('#cliente_correo').val('');
	$('#bono_limite').val('');
	$('#cliente_local').val('3900');
	$('#cliente_tercero_titular').empty();
	$('#cliente_local').select2().trigger('change');
}

function limpiar_tabla_transacciones() {
	var html_eliminar = '';
	if (permiso_eliminar) {
		html_eliminar = '<td class="text-center">Acción</td>';
	}
	var column_nuevo_balance = '';
	if(sec_id === "televentas"){
		column_nuevo_balance = '<td class="text-right" width="6%">Nuevo Balance</td>';
	}

	var column_txn_id = '';
	if(sec_id === "retail_altenar"){
		column_txn_id = '<td class="text-center" width="10%">Ticket Id</td>';
	}

	$('#tabla_transacciones').html(
			'<thead>' +
			'<tr>' +
			'<td class="text-center" width="10%">Fecha</td>' +
			'<td class="text-center" width="10%">Tipo</td>' +
			column_txn_id +
			'<td class="text-center" width="10%">Caja</td>' +
			'<td class="text-center" width="10%">Cuenta</td>' +
			'<td class="text-right" width="7%">Depósito</td>' +
			'<td class="text-right" width="5%">Comisión</td>' +
			'<td class="text-right" width="7%">Monto</td>' +
			'<td class="text-right" width="5%">Bono</td>' +
			'<td class="text-right" width="8%">Total</td>' +
			column_nuevo_balance +
			'<td class="text-right" width="6%">Tipo Saldo</td>' +
			'<td class="text-center" width="16%">Acción</td>' +
			'</tr>' +
			'</thead>' +
			'<tbody>'
			);
	$('#span_balance').html('0.00');
	$('#span_balance_bono_disponible').html('0.00');
	$('#span_bonos').html('0.00');
	$('#div_labels').html('');
	gen_balance_total = 0;
	gen_balance_bono_disponible = 0;
	gen_balance_retiro_disponible = 0;
	gen_balance_bono_utilizado = 0;
}


function abrir_modal_eliminar(trans_id, tipo_id, proveedor_id, fecha_transaccion) {
    var fecha = "'" + fecha_transaccion + "'";
	$('textarea#modal_os_observacion').val('');
	$('#motivo_del_dep').val('0');
	$('#modal_os_btn_guardar').show();
	$('#modal_os_btn_guardar').removeAttr("onclick");
	$('#modal_os_btn_guardar').attr("onclick", 'valid_fecha_hora("' + trans_id + '",' + tipo_id + ',' + proveedor_id + ',' + fecha + ')');
	$('#modal_observacion_supervisor').modal('show');
}

function valid_fecha_hora(trans_id, tipo_id, proveedor_id, fecha_transaccion){

    var d = new Date();
    var date_now = d.getFullYear() + "-" +
            (d.getMonth() + 1).toString().padStart(2, '0') + "-" +
            d.getDate().toString().padStart(2, '0') + " " +
            d.getHours().toString().padStart(2, '0') + ":" +
            d.getMinutes().toString().padStart(2, '0') + ":" +
            d.getSeconds().toString().padStart(2, '0');
    var f1 = new Date(date_now) - new Date(fecha_transaccion);
    var horas_atras = (f1 / (1000 * 60 * 60));
    if(horas_atras > 24){
        swal({
            html:true,
            title: '¿Está seguro de eliminar este balance fuera de los límites permitidos?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0336FF',
            cancelButtonColor: '#d33',
            confirmButtonText: 'SI, CONFIRMAR',
            cancelButtonText: 'CANCELAR',
            closeOnConfirm: false,
            showLoaderOnConfirm: true
        }, function(){
            eliminar_transaccion(trans_id, tipo_id, proveedor_id);    
        });
    }else{
        eliminar_transaccion(trans_id, tipo_id, proveedor_id);    
    }
    
}

function eliminar_transaccion(trans_id, tipo_id, proveedor_id) {
	$('#modal_os_btn_guardar').hide();
	var motivo_del_dep = $('#motivo_del_dep').val();
	var modal_os_observacion = $('textarea#modal_os_observacion').val();
	var tipo_balance_id = $("#sec_tlv_cbx_tipo_balance").val();

	if (parseInt(motivo_del_dep) == 0) {
		$('#modal_os_btn_guardar').show();
		$("#motivo_del_dep").focus();
		swal({
			title: "Seleccione un motivo",
			text: "Debe seleccionar un motivo para confirmar la reversión de la transacción.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		});
		return false; 
	  }

	if ($.trim(modal_os_observacion) == "") {
		$('#modal_os_btn_guardar').show();
		swal({
			title: "Agregar Observación",
			text: "Debe agregar una observación para confirmar la reversión de la transacción.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		});
		return false;
	}

	var web_id=0;
    $.each(array_clientes, function(index, item) {
        if (parseInt(item.id) === parseInt(gen_cliente_id)) {
            if (parseInt(item.web_id) > 0) {
                web_id = item.web_id;
            }
        }
    });

    var accion = "";
    var mensaje = "";
    if (tipo_id == 26) {
        accion = "eliminar_transaccion_deposito";
        mensaje = "Reversión de Depósito Exitoso."
    } else if (tipo_id == 4) {
        accion = "eliminar_transaccion_apuesta";
        mensaje = "Reversión de Apuesta Exitoso."
    } else if (tipo_id == 5) {
        accion = "eliminar_transaccion_pago_apuesta";
        mensaje = "Reversión de Pago de Apuesta Exitoso."
    } else if (tipo_id == 14) {
        accion = "eliminar_transaccion_terminal";
        mensaje = "Reversión de Terminal Deposit Exitoso."
    } else if (tipo_id == 15) {
        accion = "eliminar_transaccion_donacion_cancer";
        mensaje = "Reversión de Donación de Cancer Exitoso."
    } else if (tipo_id == 35) {
        accion = "eliminar_transaccion_solicitud_recarga";
        mensaje = "Se canceló la solicitud de recarga exitosamente."
    }
    $.post("/sys/set_televentas.php", {
        accion: accion,
        cliente_id: gen_cliente_id,
        web_id: web_id,
        trans_id: trans_id,
        tipo_id: tipo_id,
        proveedor_id: proveedor_id,
		motivo_del_dep: motivo_del_dep,
        observacion: modal_os_observacion,
		tipo_balance_id: tipo_balance_id
    }).done(function (data) {
        try {
            var respuesta = JSON.parse(data);
			$('#modal_os_btn_guardar').show();
            if (parseInt(respuesta.http_code) == 400) {
                $('#modal_observacion_supervisor').modal('hide');
                swal({
                    title: 'Error',
                    text: respuesta.status,
                    type: 'error',
                    timer: 1500,
                    showConfirmButton: false
                });
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#modal_observacion_supervisor').modal('hide');
                swal({
                    title: mensaje,
                    text: '',
                    type: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                if (parseInt(tipo_id) === 4 && parseInt(proveedor_id) === 5) {
                    sec_tlv_div_portal_altenar_close();
                    if ($('#sec_tlv_div_portal_altenar_individual_' + gen_cliente_id).length > 0) {
                        $('#sec_tlv_div_portal_altenar_individual_' + gen_cliente_id).remove();
                    }
                }
                listar_transacciones(gen_cliente_id);
                return false;
            }
            return false;
        } catch (e) {
            swal('¡Error!', e, 'error');
           // console.log("Error de TRY-CATCH --> Error: " + e);
            return false;
        }
    })
    .fail(function (xhr, status, error) {
        $('#modal_observacion_supervisor').modal('hide');
        swal('¡Error!', error, 'error');
       // console.log("Error de .FAIL -- Error: " + error);
        return false;
    });
    return false;
}


function bloquear_div_cliente() {
	 
	$('#div_cliente_btn_habilitar').show();
	$('#div_cliente_btn_guardar').hide();
	$('#div_cliente_btn_cancelar').hide();
 
	$.each(array_clientes, function (index, item) {
		if (parseInt(item.id) === parseInt(gen_cliente_id)) {
			cliente_tipo_doc = (item.tipo_doc !== null ? item.tipo_doc : '0').toString();
			cliente_num_doc = (item.num_doc !== null ? item.num_doc : '0').toString();
			cliente_celular = (item.telefono !== null ? item.telefono : '').toString();
			cliente_fec_nac = (item.fec_nac !== null ? item.fec_nac : '').toString();
			 
			//cargar_titular_abono_cliente();
			
			cliente_idweb = ((item.web_id !== null && item.web_id !== '0') ? item.web_id : '').toString();
			cliente_web_full_name = item.web_full_name;
			cliente_player_id = item.player_id;
			cliente_ape_paterno = (item.apellido_paterno !== null ? item.apellido_paterno : '').toString();
			cliente_ape_materno = (item.apellido_materno !== null ? item.apellido_materno : '').toString();
			cliente_correo = item.correo;
			cliente_nombres = (item.nombre !== null ? item.nombre : '').toString();
			cliente_local = (item.cc_id !== null ? item.cc_id : '3900').toString();
			bono_limite = (item.bono_limite == "" ? '10000.00' : item.bono_limite).toString();
	
		}
	
	});

	$('#cliente_tipo_doc').val(cliente_tipo_doc);
	$('#cliente_num_doc').val(cliente_num_doc);
	$('#cliente_celular').val(cliente_celular);
	$('#cliente_fec_nac').val(cliente_fec_nac);
	$('#cliente_idweb').val(cliente_idweb);
	$('#cliente_webfullname').val(cliente_web_full_name);
	$('#cliente_idjugador').val(cliente_player_id);
	$('#cliente_nombre').val(cliente_nombres);
	$('#cliente_apepaterno').val(cliente_ape_paterno);
	$('#cliente_apematerno').val(cliente_ape_materno);
	$('#cliente_correo').val(cliente_correo);
//	cargar_titular_abono_cliente();

	$('#cliente_tipo_doc').attr('disabled', 'disabled');
	$('#cliente_num_doc').attr('disabled', 'disabled');
	$('#cliente_celular').attr('disabled', 'disabled');
	$('#cliente_fec_nac').attr('disabled', 'disabled');
	$('#cliente_tercero_titular').attr('disabled', 'disabled');
	$('#cliente_local').attr('disabled', 'disabled');
	$('#cliente_idweb').attr('disabled', 'disabled');
	$('#cliente_idjugador').attr('disabled', 'disabled');
    $('#cliente_idwebc').attr('disabled', 'disabled');
	$('#cliente_nombre').attr('disabled', 'disabled');
	$('#cliente_apepaterno').attr('disabled', 'disabled');
	$('#cliente_apematerno').attr('disabled', 'disabled');
	$('#cliente_correo').attr('disabled', 'disabled');
	$('#bono_limite').attr('disabled', 'disabled');
	$('#cliente_direccion').attr('disabled', 'disabled');

	$('#sec_tlv_cliente_nacionalidad').attr('disabled', 'disabled');
	$('#sec_tlv_cliente_departamento').attr('disabled', 'disabled');
	$('#sec_tlv_cliente_provincia').attr('disabled', 'disabled');
	$('#sec_tlv_cliente_distrito').attr('disabled', 'disabled');
	
	$('#cliente_pep2').attr('disabled', 'disabled');
	$('#cliente_tyc').attr('disabled', 'disabled');
}
function desbloquear_div_cliente() {
	
	$('#div_cliente_btn_habilitar').hide();
	$('#div_cliente_btn_guardar').show();
	$('#div_cliente_btn_cancelar').show();

	//$('#cliente_tipo_doc').removeAttr('disabled');
	//$('#cliente_num_doc').removeAttr('disabled');
	$('#cliente_celular').removeAttr('disabled');
	$('#cliente_fec_nac').removeAttr('disabled');

	if (parseInt(gen_validate_web_id)===0) {
		$('#cliente_idweb').removeAttr('disabled');
	}

	$('#cliente_idjugador').removeAttr('disabled');
    $('#cliente_idwebc').removeAttr('disabled');
	$('#cliente_nombre').removeAttr('disabled');
	$('#cliente_apepaterno').removeAttr('disabled');
	$('#cliente_apematerno').removeAttr('disabled');
	$('#cliente_local').removeAttr('disabled');
	$('#cliente_correo').removeAttr('disabled');
	$('#cliente_direccion').removeAttr('disabled');

	$('#sec_tlv_cliente_nacionalidad').removeAttr('disabled');
	$('#sec_tlv_cliente_departamento').removeAttr('disabled');
	$('#sec_tlv_cliente_provincia').removeAttr('disabled');
	$('#sec_tlv_cliente_distrito').removeAttr('disabled');
	
	$('#cliente_pep2').removeAttr('disabled');
	$('#cliente_tyc').removeAttr('disabled');
	
	if (permiso_editar_bono_limite) {
		$('#bono_limite').removeAttr('disabled');
	}
	if (permiso_editar_titular_abono_tlv) {
		$('#cliente_tercero_titular').removeAttr('disabled');
	}
}




$(function () {

	if (sec_id === 'televentas' || sec_id === 'retail_altenar') {

		$('.btn_agregar_transaccion').click(function () {
			/*
			var valid_n = sec_tlv_valid_number_phone();
			if(valid_n == false) {
				return;
			}
			*/
			var tipo_transaccion = $(this).attr('tipo_transaccion');

			if (parseInt(tipo_transaccion) === 1) {//Deposito
				 
				validar_limite_dep();
			}
			if (parseInt(tipo_transaccion) === 2) {//Recarga
				nueva_recargaweb();
			}
			if (parseInt(tipo_transaccion) === 4) {//Apuesta
				var modal = $(this).attr('modal');
				nuevo_modal_apuesta_registrar(modal);
			}
			if (parseInt(tipo_transaccion) === 5) {//Pagar apuesta
				var modal = $(this).attr('modal');
				nuevo_modal_apuesta_pagar(modal);
			}
			if (parseInt(tipo_transaccion) === 9) {//Retiro
				nuevo_modal_retiro(1);
			}
			if (tipo_transaccion === "add_devolucion") {//Retiro
				nuevo_modal_retiro(2);
			}
			if (tipo_transaccion === 'web_calimaco') {//Web calimaco
				nuevo_portal_calimaco();
				/*
				if (parseFloat(gen_balance_total) > 0) {
					nuevo_portal_calimaco();
				} else {
					swal('Aviso', 'El balance debe ser mayor a 0.', 'warning');
				}
				*/
			}
			if (tipo_transaccion === 'add_recarga_2') { // Recarga Web 2
				nueva_recargaweb_2();
			}
			if (tipo_transaccion === 'terminal_deposit') { // Recarga Web 2
				terminal_deposit();
			}
            if (tipo_transaccion === 'add_bingo_venta') { // Venta Bingo
                add_bingo_venta();
            }
            if (tipo_transaccion === 'add_bingo_pago') { // Pago Bingo
                add_bingo_pago();
            }
            if (tipo_transaccion === 'up_balance') { // Subir Balance
                nuevo_modal_editBal(tipo_transaccion);
            }
            if (tipo_transaccion === 'down_balance') { // Bajar Balance
                nuevo_modal_editBal(tipo_transaccion);
            }
            if (tipo_transaccion === 'add_deposito_c7') {//Deposito
                nuevo_deposito_c7();
            }
			if (parseInt(tipo_transaccion) === 15) {// Donación Cancer
				nuevo_modal_cancer();
			}
			if (tipo_transaccion === 'propina') {// Propina
				nuevo_modal_propina();
			}
			if (tipo_transaccion === 'add_retiro_c7') {// Retiro Caja 7
				nuevo_retiro_c7();
			}
			if (tipo_transaccion === 'add_tambo') { // Tambo
				add_modal_tambo();
			}
			if (tipo_transaccion === "add_devolucion_c7") {//Devolución C7
				nueva_devolucion_c7();
			}
			if (tipo_transaccion === 'golden_race_virtual') {// Golden Race Juevos Virtuales
				nuevo_porta_golden_race();
			}
			if (tipo_transaccion === 'add_bingo_total') { // Bingo Total
                add_bingo_total();
            }
			if (tipo_transaccion === 'abrir_torito') { // Torito
                abrir_torito();
            }
			 
		});

		$('#btn_actualizar_tabla_transacciones').click(function () {
			listar_transacciones(gen_cliente_id);
		});

	}

});

function verificar_transacciones_activas_dinero_at(cliente_id){
	var data = new FormData();
	data.append('accion', "consultar_transacciones_activas_evento");
	data.append('cliente_id', cliente_id);

	auditoria_send({"proceso": "consultar_transacciones_activas_evento_SEND", "data": (Object.fromEntries(data.entries()))});

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"respuesta": "consultar_transacciones_activas_evento", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$.each(respuesta.result, function(index, item){

					$("#evento_dineroat_id").val(item.evento_dineroat_id);

					if( parseInt(item.juegos_virtuales) === 1 ){
						$('.btn_dinero_at_virtuales').show();
					}
					if( parseInt(item.bingo) === 1 ){
						$('.btn_dinero_at_bingo').show();
					}
					if( parseInt(item.sportbook) === 1 ){
						$('.btn_dinero_at_sportbook').show();
					}

					/* var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
					if ( parseInt(temp_balance_tipo)===6 ) {
						if ( parseInt(item.rollover_estado)===1 ) {
							$("#dinero_at_rollover_on").css("display","block");
						}
					} else {
						$("#dinero_at_rollover_on").css("display","none");
					} */
				});
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"respuesta": "consultar_transacciones_activas_evento_ERROR", "data": result});
			return false;
		}
	});
}


function insertar_cliente_tipo_balance(cliente_id, tipo_balance_id) {
	var data = new FormData();
	data.append('accion', "enviar_tipo_balance_calimaco");
	data.append('id_cliente', cliente_id);
	data.append('tipo_balance_id', tipo_balance_id);

	//auditoria_send({"proceso": "enviar_tipo_balance_calimaco_SEND", "data": (Object.fromEntries(data.entries()))});

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"respuesta": "enviar_tipo_balance_calimaco", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				// swal('Aviso', respuesta.status, 'warning');
				//console.log("Se registro el cliente y tipo de balance.");
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"respuesta": "enviar_tipo_balance_calimaco_ERROR", "data": result});
			return false;
		}
	});
	
}

$("#sec_tlv_cbx_tipo_balance").on("change", function(){
	var tls_tipo_balance_id_temp = $("#sec_tlv_cbx_tipo_balance").val();

	insertar_cliente_tipo_balance(gen_cliente_id,tls_tipo_balance_id_temp);

	if(parseInt(tls_tipo_balance_id_temp)===1) {
		$("#evento_dineroat_id").val(0);
		$('.btn_dinero_at').hide();
		$('.btn_agregar_transaccion').show();
		$( ".class_dinero_promocional" ).css( "background-color","#fff" );
		$("#dinero_at_rollover_on").css("display","none");
		listar_transacciones(gen_cliente_id);
	} else if(parseInt(tls_tipo_balance_id_temp)===999) {
		$("#evento_dineroat_id").val(0);
		$('.btn_dinero_at').hide();
		$('.btn_agregar_transaccion').show();
		$( ".class_dinero_promocional" ).css( "background-color","#fff" );
		$("#dinero_at_rollover_on").css("display","none");
		listar_transacciones(gen_cliente_id);
	} else if(parseInt(tls_tipo_balance_id_temp)===6) {
		$('.btn_agregar_transaccion').hide();
		$( ".class_dinero_promocional" ).css( "background-color","#e0f4ff" );/* #CFE8EB */
		verificar_transacciones_activas_dinero_at(gen_cliente_id);
		loading("true");
		listar_transacciones(gen_cliente_id);
	}

	$.each(array_clientes, function (index, item) {
		if (parseInt(item.id) === parseInt(gen_cliente_id)) {
			//console.log('item: ');
			//console.log(item);
			item.tipo_balance_id = tls_tipo_balance_id_temp;
			array_clientes.splice(index, 1, item);
			//console.log('item 2: ');
			//console.log(item);
		}
	});
})





//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// PROPINAS
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

$(function () {

	if (sec_id === 'televentas' || sec_id === 'retail_altenar') {
		//Monto
		$("#modal_propina_monto").on({
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
			}
		});

		//Button guardar
		/* $('#modal_cancer_btn_registrar').click(function () {
			guardar_modal_cancer();
			return false;
		}); */
	}

});

//Button guardar propina
$('#modal_propina_btn_registrar').click(function () {
	guardar_modal_propina();
	return false;
});

function guardar_modal_propina() {

	$('#modal_propina_voucher_div').css('border', '');
	$('#modal_propina_monto').css('border', '');
	$('#modal_propina_btn_registrar').hide();

	var id_cuenta_usar_propina = 0;
	var nom_cuenta_usar_propina = "";
	var num_cuenta_usar_propina = "";
	var num_cci_cuenta_usar_propina = "";
	var id_banco_cuenta_usar_propina = 0;
	// var monto_minimo_retiro = 0;
	var valor;
	$('.sec_tlv_pag_sec_cuentas_cajero:checked').each(function (indice, elemento) {
		var fila = $(this).parents(".listado_cuentas_bancarias_cajeros");
		id_cuenta_usar_propina = fila.find(".sec_tlv_id_cuenta_cajero").val();
		nom_cuenta_usar_propina = fila.find(".sec_tlv_nom_cuenta_cajero").html();
		num_cuenta_usar_propina = fila.find(".sec_tlv_num_cuenta_cajero").html();
		num_cci_cuenta_usar_propina = fila.find(".sec_tlv_num_cci_cuenta_cajero").html();
		id_banco_cuenta_usar_propina = fila.find(".sec_tlv_id_banco_cuenta_cajero").val();
		valor = $(this).val();
		/*
		 console.log("cuenta: " + id_cuenta_usar_propina 
		 + " nom cuenta: " + nom_cuenta_usar_propina 
		 + " num cta: " + num_cuenta_usar_propina 
		 + " num cci: " + num_cci_cuenta_usar_propina);
		 */
	});

	var observacion = $.trim($('textarea#modal_propina_observacion').val()); 

	var imagen = $('#modal_propina_voucher').val();
	var f_imagen = $("#modal_propina_voucher")[0].files[0];
	var imagen_extension = imagen.substring(imagen.lastIndexOf("."));

	if (!(imagen.length > 0)) {
		$("#modal_propina_voucher_div").css("border", "1px solid red");
		swal('Aviso', 'Agregue una imágen.', 'warning');
		$('#modal_propina_btn_registrar').show();
		return false;
	}
	if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
			imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
			imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG") {
		$("#modal_propina_voucher_div").css("border", "1px solid red");
		swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
		$('#modal_propina_btn_registrar').show();
		return false;
	}

	var monto = $('#modal_propina_monto').val().replace(/\,/g, '');
	if (!(parseFloat(monto) > 0)) {
		$('#modal_propina_monto').css('border', '1px solid red');
		$('#modal_propina_monto').focus();
		$('#modal_propina_btn_registrar').show();
		return false;
	}
	if (parseFloat(monto) > parseFloat(gen_balance_retiro_disponible)) {
		$('#modal_propina_monto').css('border', '1px solid red');
		swal('Aviso', 'El monto debe ser menor o igual al balance disponible.', 'warning');
		$('#modal_propina_monto').focus();
		$('#modal_propina_btn_registrar').show();
		return false;
	}

	if (valor == undefined || $.trim(valor) == "") {
		swal('Aviso', 'Debe seleccionar la cuenta a utilizar', 'warning');
		$('#modal_propina_btn_registrar').show();
		return false;
	}

	var link_atencion = $('#modal_propina_link_atencion').val();
	if($.trim(link_atencion) == ""){
		swal('Aviso', 'Ingrese el link de atención', 'warning');
		$('#modal_propina_btn_registrar').show();
		return false;
	}

	var data = new FormData();
	data.append('accion', "guardar_propina_cajero");
	data.append('id_cliente', gen_cliente_id);
	data.append('imagen_voucher', f_imagen);
	data.append('monto', monto);
	data.append('observacion', observacion);
	data.append('id_banco_cuenta_usar_propina', id_banco_cuenta_usar_propina);
	data.append('id_cuenta_usar_propina', id_cuenta_usar_propina);
	data.append('link_atencion', link_atencion);

	$.ajax({
		url: "/sys/set_televentas.php",
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
			// auditoria_send({"respuesta": "guardar_propina_cajero", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_propina').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_propina').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			// auditoria_send({"respuesta": "guardar_propina_cajero", "data": result});
			return false;
		}
	});
	return false;
}


function nuevo_modal_propina() {
	$('.sec_tlv_pag_sec_cuentas_cajero').removeAttr("checked");
	//var monto_disponible_retiro_cliente = parseFloat($('#sec_tlv_retiro_disponible').html().replace(/\,/g, '')).toFixed(2);
	$('#modal_propina_titulo').html('<b>Propinas - Saldo Disponible: S/ '+gen_balance_retiro_disponible+'</b>');
	// $('#modal_propina_cuenta_div label').html('<b>Cuentas del cajero: S/ '+gen_balance_total+'</b>');
	$('#modal_propina_voucher').val('');
	$('#modal_propina_monto').val(0.00);
	$('#modal_propina_observacion').val('');
	$('#modal_propina_observacion').val('');
	$('#modal_propina_link_atencion').show();
	$('#modal_propina').modal();
	return false;
}
$('#modal_propina_btn_agregar_cuenta_cajero').click(function () {
	// $('.modal_cuentas_cajero_listar').hide();
	$('.modal_cuentas_cajero_registrar').show();
	$('#modal_cuentas_cajero').modal();
	$('#modal_cuentas_cajero_banco').val(0).trigger('change');
	$('#modal_cuentas_cajero_cuenta_num').val('');
	$('#modal_cuentas_cajero_cci').val('');
	return false;
});
$('#modal_cuentas_cajero_btn_guardar').click(function () {
	guardar_cuenta_x_cajero();
	return false;
});
function limpiar_campos_modal_propina() {
	$('#modal_propina_cuenta').empty();
	$('#modal_propina_cuenta_tabla tbody').html('');
	$('#modal_propina_monto').val('0.00');
	// $('.modal_cuentas_listar').hide();
	$('.modal_cuentas_cajero_registrar').hide();
	limpiar_bordes_modal_propina();
}

function limpiar_bordes_modal_propina() {
	$('#modal_propina_cuenta_div').css('border', '');
	$('#modal_propina_monto').css('border', '');
	$('#modal_cuentas_cajero_banco').css('border', '');
	$('#modal_cuentas_cajero_cuenta_num').css('border', '');
	$('#modal_cuentas_cajero_cci').css('border', '');
}

function modal_propina_obtener_cuentas() {
	var data = {
		accion: "obtener_cuentas_x_cajero",
		cliente_id: gen_cliente_id,
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
			// auditoria_send({"proceso": "obtener_cuentas_x_cliente", "data": respuesta});
			// console.log(respuesta);
			if (parseInt(respuesta.http_code) == 200) {
				// $('#modal_retiro_cuenta').append('<option value="0">:: Seleccione ::</option>');
				// $('#modal_retiro_cuenta').val('0');

				$.each(respuesta.result, function (index, item) {
					$('#modal_propina_cuenta_tabla tbody').append(
							'<tr class="listado_cuentas_bancarias_cajeros" for="' + item.cod + '_' + item.cuenta_num_cliente + '">' +
							'   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_id_cuenta_cajero" value="' + item.cod + '" /></td>' +
							'   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_id_banco_cuenta_cajero" value="' + item.banco_id + '" /></td>' +
							'   <td style="font-weight: bold; text-align: center;" >' +
							'       <label for="' + item.cod + '_' + item.cuenta_num_cliente + '" class="sec_tlv_nom_cuenta_cajero">' + item.banco + '</label>' +
							'   </td>' +
							'   <td style="font-weight: bold; text-align: center;">' +
							'       <label for="' + item.cod + '_' + item.cuenta_num_cliente + '" class="sec_tlv_num_cuenta_cajero">' + item.cuenta_num + '</label>' +
							'   </td>' +
							'   <td style="font-weight: bold; text-align: center;">' +
							'       <label for="' + item.cod + '_' + item.cuenta_num_cliente + '" class="sec_tlv_num_cci_cuenta_cajero">' + item.cci + '</label>' +
							'   </td>' +
							'   <td style="text-align: center;"><input type="radio" name="sec_tlv_pag_sec_cuentas_cajero" id="' + item.cod + '_' + item.cuenta_num_cliente + '" class="sec_tlv_pag_sec_cuentas_cajero"/></td>' +
							'</tr>'
							);
				});
			} else {
				// $('#modal_retiro_cuenta').append('<option value="0">:: No existen cuentas ::</option>');
				$('#modal_propina_cuenta_tabla tbody').append(
						'<tr>' +
						'   <td colspan="2">No hay cuentas registradas</td>' +
						'</tr>'
						);
				// $('#modal_retiro_cuenta').val('0');
			}
			return false;
		},
		error: function (result) {
			// auditoria_send({"proceso": "obtener_cuentas_x_cliente_error", "data": result});
			return false;
		}
	});
}

function guardar_cuenta_x_cajero() {

	$('#modal_cuentas_cajero_btn_guardar').hide();
	limpiar_bordes_modal_propina();

	var banco = $('#modal_cuentas_cajero_banco').val();
	var cuenta_num = $('#modal_cuentas_cajero_cuenta_num').val();
	var cci = $('#modal_cuentas_cajero_cci').val();

	if (!(parseInt(banco) > 0)) {
		$('#modal_cuentas_cajero_banco').css('border', '1px solid red');
		$('#modal_cuentas_cajero_banco').focus();
		$('#modal_cuentas_cajero_btn_guardar').show();
		return false;
	}

	if (parseInt(banco) != 53) { //bn
		if ($.trim(cuenta_num) == "" && $.trim(cci) == "") {
			swal('Aviso', "Debe Ingresar el Número de Cuenta o CCI", 'warning');
			$('#modal_cuentas_cajero_btn_guardar').show();
			return false;
		}
	} else {
		if (!(cuenta_num.length > 0)) {
			$('#modal_cuentas_cajero_cuenta_num').css('border', '1px solid red');
			$('#modal_cuentas_cajero_cuenta_num').focus();
			$('#modal_cuentas_cajero_btn_guardar').show();
			return false;
		}
		if (!(cci.length > 0)) {
			$('#modal_cuentas_cajero_cci').css('border', '1px solid red');
			$('#modal_cuentas_cajero_cci').focus();
			$('#modal_cuentas_cajero_btn_guardar').show();
			return false;
		}
	}



	var data = {
		"accion": "guardar_cuenta_x_cajero",
		"id_cliente": gen_cliente_id,
		"id_banco": banco,
		"cuenta_num": cuenta_num,
		"cci": cci
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
			// auditoria_send({"respuesta": "guardar_cuenta_x_cliente", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_cuentas_cajero').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				limpiar_campos_modal_propina();
				modal_propina_obtener_cuentas();
				$('#modal_cuentas_cajero').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				return false;
			}
			return false;
		},
		error: function (result) {
			// auditoria_send({"proceso": "guardar_cuenta_x_cliente_error", "data": result});
			return false;
		}
	});
	return false;
}





//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// DONACIÓN CANCER
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

$(function () {

	if (sec_id === 'televentas' || sec_id === 'retail_altenar') {
		//Monto
		$("#modal_cancer_monto").on({
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
			}
		});

		//Button guardar
		$('#modal_cancer_btn_registrar').click(function () {
			guardar_modal_cancer();
			return false;
		});
	}

});
function nuevo_modal_cancer() {
	$('#modal_cancer_titulo').html('<b>Donación Cancer - Balance Actual: S/ '+gen_balance_total+'</b>');
	$('#modal_cancer_voucher').val('');
	$('#modal_cancer_monto').val(0.00);
	$('#modal_cancer_btn_registrar').show();
	$('#modal_cancer').modal();
	return false;
}
function guardar_modal_cancer() {

	$('#modal_deposito_voucher_div').css('border', '');
	$('#modal_cancer_monto').css('border', '');
	$('#modal_cancer_btn_registrar').hide();

	var imagen = $('#modal_cancer_voucher').val();
	var f_imagen = $("#modal_cancer_voucher")[0].files[0];
	var imagen_extension = imagen.substring(imagen.lastIndexOf("."));
	if (!(imagen.length > 0)) {
		$("#modal_cancer_voucher_div").css("border", "1px solid red");
		swal('Aviso', 'Agregue una imágen.', 'warning');
		$('#modal_cancer_btn_registrar').show();
		return false;
	}
	if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
			imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
			imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG") {
		$("#modal_cancer_voucher_div").css("border", "1px solid red");
		swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
		$('#modal_cancer_btn_registrar').show();
		return false;
	}

	var monto = $('#modal_cancer_monto').val().replace(/\,/g, '');
	if (!(parseFloat(monto) > 0)) {
		$('#modal_cancer_monto').css('border', '1px solid red');
		$('#modal_cancer_monto').focus();
		$('#modal_cancer_btn_registrar').show();
		return false;
	}
	if (parseFloat(monto) > parseFloat(gen_balance_total)) {
		$('#modal_cancer_monto').css('border', '1px solid red');
		swal('Aviso', 'El monto debe ser menor o igual al balance total.', 'warning');
		$('#modal_cancer_monto').focus();
		$('#modal_cancer_btn_registrar').show();
		return false;
	}

	var data = new FormData();
	data.append('accion', "guardar_donacion_cancer");
	data.append('id_cliente', gen_cliente_id);
	data.append('imagen_voucher', f_imagen);
	data.append('monto', monto);

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "guardar_donacion_cancer", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_cancer').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_cancer').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "guardar_donacion_cancer_error", "data": result});
			return false;
		}
	});
	return false;
}






//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// BALANCE
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
var modal_editBal_tipo_transaccion='';
$(function () {

	if (sec_id === 'televentas' || sec_id === 'retail_altenar') {
		//Monto
		$("#modal_editBal_balance_tipo").on({
			"change": function (event) {
				$('#modal_editBal_balance_tipo_actual_div').hide();
				$('#modal_editBal_balance_tipo_nuevo_div').hide();
				var temp_tipo_balance = $(event.target).val();
				if(parseInt(temp_tipo_balance)>0){
					if(parseInt(temp_tipo_balance)===4){
						$('#modal_editBal_balance_tipo_actual_texto').html('Balance No Retirable Actual S/');
						$('#modal_editBal_balance_tipo_nuevo_texto').html('Nuevo Balance No Retirable S/');
					}
					if(parseInt(temp_tipo_balance)===5){
						$('#modal_editBal_balance_tipo_actual_texto').html('Balance Retirable Actual S/');
						$('#modal_editBal_balance_tipo_nuevo_texto').html('Nuevo Balance Retirable S/');
					}
					if(parseInt(temp_tipo_balance)===6){
						$('#modal_editBal_balance_tipo_actual_texto').html('Dinero AT Actual S/');
						$('#modal_editBal_balance_tipo_nuevo_texto').html('Nuevo Dinero AT S/');
					}
					$('#modal_editBal_balance_tipo_actual_div').show();
					$('#modal_editBal_balance_tipo_nuevo_div').show();
				}
				modal_editBal_monto_onchange();
				//console.log('focus');
			}
		});
		$("#modal_editBal_monto").on({
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
					modal_editBal_monto_onchange();
				} else {
					$(event.target).val("0.00");
					modal_editBal_monto_onchange();
				}
			}
		});
	}

});

function nuevo_modal_editBal(tipo_transaccion) {
	modal_editBal_tipo_transaccion = tipo_transaccion;
	limpiar_bordes_modal_editBal();
	limpiar_campos_modal_editBal();
	$('#modal_editBal_balance_tipo_actual_div').hide();
	$('#modal_editBal_balance_tipo_nuevo_div').hide();
	$('#modal_editBal_btn_guardar').show();
	$('#modal_editBal').modal();
	if(modal_editBal_tipo_transaccion==='up_balance'){
		$("#modal_editBal_btn_guardar_texto").html('SUBIR BALANCE');
		$("#modal_editBal_monto_texto").html('<spam style="color: red; font-weight: bold;">(*)</spam> Balance a Sumar S/');
		$('#modal_editBal_btn_guardar').css('background','#00c770');
		$('#modal_editBal_btn_guardar').css('border-color','#00c770');
		sec_televentas_obtener_opciones("listar_motivos_balance_sube", $("[name='modal_editBal_motivo_balance_id']"));
		sec_televentas_obtener_opciones("listar_juegos_balance", $("[name='modal_editBal_juego_motivo_balance']"));
		sec_televentas_obtener_opciones("listar_supervisores_tlv", $("[name='modal_editBal_supervisor_motivo_balance']"));
		sec_televentas_obtener_opciones("listar_cajeros_tlv", $("[name='modal_editBal_cajero_motivo_balance']"));
	}
	if(modal_editBal_tipo_transaccion==='down_balance'){
		$("#modal_editBal_btn_guardar_texto").html('BAJAR BALANCE');
		$("#modal_editBal_monto_texto").html('<spam style="color: red; font-weight: bold;">(*)</spam> Balance a Restar S/');
		$('#modal_editBal_btn_guardar').css('background','#ff0018');
		$('#modal_editBal_btn_guardar').css('border-color','#ff0018');
		sec_televentas_obtener_opciones("listar_motivos_balance_baja", $("[name='modal_editBal_motivo_balance_id']"));
		sec_televentas_obtener_opciones("listar_juegos_balance", $("[name='modal_editBal_juego_motivo_balance']"));
		sec_televentas_obtener_opciones("listar_supervisores_tlv", $("[name='modal_editBal_supervisor_motivo_balance']"));
		sec_televentas_obtener_opciones("listar_cajeros_tlv", $("[name='modal_editBal_cajero_motivo_balance']"));
	}
}
function limpiar_campos_modal_editBal() {
	$("#modal_editBal_balance_tipo").val('0');
	$("#modal_editBal_motivo_balance_id").val('0');
	$("#modal_editBal_juego_motivo_balance").val('0');
	$("#modal_editBal_idtrans").val('0');
	$("#modal_editBal_supervisor_motivo_balance").val('0');
	$("#modal_editBal_cajero_motivo_balance").val('0');
	$("#modal_editBal_balance_actual").val(gen_balance_total);
	$("#modal_editBal_monto").val('0');
	$("#modal_editBal_balance_nuevo").val('0');
    $("#modal_editBal_observacion").val('');
}
function limpiar_bordes_modal_editBal() {
    $('#modal_editBal_monto').css('border', '');
	$('#modal_editBal_idtrans').css('border', '');
    $('#modal_editBal_balance_tipo').css('border', '');
    $('#modal_editBal_observacion').css('border', '');
	
}
function modal_editBal_monto_onchange() {
	var monto = $('#modal_editBal_monto').val().replace(/\,/g, '');
	if(!(parseFloat(monto)>0)){
		monto = 0;
	}
	var temp_tipo_balance = $('#modal_editBal_balance_tipo').val();
	if(parseInt(temp_tipo_balance)===4){
		$("#modal_editBal_balance_tipo_actual").val(gen_balance_no_retirable_disponible);
	}
	if(parseInt(temp_tipo_balance)===5){
		$("#modal_editBal_balance_tipo_actual").val(gen_balance_retiro_disponible);
	}
	if(parseInt(temp_tipo_balance)===6){
		$("#modal_editBal_balance_tipo_actual").val(gen_balance_dinero_at);
	}
	if(modal_editBal_tipo_transaccion==='up_balance'){
		if(parseInt(temp_tipo_balance)===4){
			$("#modal_editBal_balance_tipo_nuevo").val((parseFloat(gen_balance_no_retirable_disponible)+parseFloat(monto)).toFixed(2));
		}
		if(parseInt(temp_tipo_balance)===5){
			$("#modal_editBal_balance_tipo_nuevo").val((parseFloat(gen_balance_retiro_disponible)+parseFloat(monto)).toFixed(2));
		}
		if(parseInt(temp_tipo_balance)===6){
			$("#modal_editBal_balance_tipo_nuevo").val((parseFloat(gen_balance_dinero_at)+parseFloat(monto)).toFixed(2));
			monto = parseFloat(gen_balance_total);
		} else {
			monto = parseFloat(gen_balance_total)+parseFloat(monto);
		}
	}
	if(modal_editBal_tipo_transaccion==='down_balance'){
		if(parseInt(temp_tipo_balance)===4){
			$("#modal_editBal_balance_tipo_nuevo").val((parseFloat(gen_balance_no_retirable_disponible)-parseFloat(monto)).toFixed(2));
		}
		if(parseInt(temp_tipo_balance)===5){
			$("#modal_editBal_balance_tipo_nuevo").val((parseFloat(gen_balance_retiro_disponible)-parseFloat(monto)).toFixed(2));
		}
		if(parseInt(temp_tipo_balance)===6){
			$("#modal_editBal_balance_tipo_nuevo").val((parseFloat(gen_balance_dinero_at)-parseFloat(monto)).toFixed(2));
			monto = parseFloat(gen_balance_total);
		} else {
			monto = parseFloat(gen_balance_total)-parseFloat(monto);
		}
	}
	monto=parseFloat(monto).toFixed(2);
	$("#modal_editBal_balance_nuevo").val(monto);
}
function editar_balance() {

	$('#modal_editBal_btn_guardar').hide();
	limpiar_bordes_modal_editBal();

	var balance_tipo = $('#modal_editBal_balance_tipo').val();

	var motivo_balance = $('#modal_editBal_motivo_balance_id').val();
	var juego_balance = $('#modal_editBal_juego_motivo_balance').val();
	var id_transaccion_juego = $('#modal_editBal_idtrans').val().trim();
	var supervisor_balance = $('#modal_editBal_supervisor_motivo_balance').val();
	var cajero_balance = $('#modal_editBal_cajero_motivo_balance').val();


	var monto = $('#modal_editBal_monto').val().replace(/\,/g, '');
	var balance_nuevo = $('#modal_editBal_balance_nuevo').val().replace(/\,/g, '');
    var observacion = $('#modal_editBal_observacion').val();
    var balance_actual_disponible = $('#modal_editBal_balance_tipo_actual').val();
    var nuevo_balance_disponible = $('#modal_editBal_balance_tipo_nuevo').val();
    var texto_balance_disponible = $('#modal_editBal_balance_tipo_nuevo_texto').html()

	if (!(parseInt(balance_tipo) > 0)) {
		$('#modal_editBal_balance_tipo').css('border', '1px solid red');
		$('#modal_editBal_balance_tipo').focus();
		$('#modal_editBal_btn_guardar').show();
		swal({
			title: "Seleccione un Tipo Balance",
			text: "Debe seleccionar un tipo balance para registrar la transacción.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		});
		return false; 
	}

	if (parseInt(motivo_balance) == 0) {
		$('#modal_editBal_btn_guardar').show();	 
		
		swal({
			title: "Seleccione un Motivo",
			text: "Debe seleccionar un motivo para registrar la transacción.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		},function () {
			$("#modal_editBal_motivo_balance_id").focus();
    		$("#modal_editBal_motivo_balance_id").select2("open");
		});

		return false; 
	}


	if (parseInt(juego_balance) == 0) {
		$('#modal_editBal_btn_guardar').show();	 
		
		swal({
			title: "Seleccione un Juego",
			text: "Debe seleccionar un juego para registrar la transacción.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		},function () {
			$("#modal_editBal_juego_motivo_balance").focus();
    		$("#modal_editBal_juego_motivo_balance").select2("open");
		});
		return false; 
	}

	if (id_transaccion_juego.length < 2 || id_transaccion_juego.length > 20	) {
		$('#modal_editBal_btn_guardar').show();	 
		
		swal({
			title: "Ingrese un ID de transacción",
			text: "Debe ingresar un id de transacción (max 20 dígitos).",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		},function () {
			$("#modal_editBal_idtrans").focus();
    		 
		});
		return false; 
	}

	if (parseInt(cajero_balance) == 0) {
		$('#modal_editBal_btn_guardar').show();	 
		
		swal({
			title: "Seleccione un Cajero",
			text: "Debe seleccionar un cajero para registrar la transacción.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		},function () {
			$("#modal_editBal_cajero_motivo_balance").focus();
    		$("#modal_editBal_cajero_motivo_balance").select2("open");
		});
		return false; 
	}


	if (parseInt(supervisor_balance) == 0) {
		$('#modal_editBal_btn_guardar').show();	 
		
		swal({
			title: "Seleccione un Supervisor",
			text: "Debe seleccionar un supervisor para registrar la transacción.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		},function () {
			$("#modal_editBal_supervisor_motivo_balance").focus();
    		$("#modal_editBal_supervisor_motivo_balance").select2("open");
		});
		return false; 
	}


	if (!(parseFloat(monto) > 0)) {
		$('#modal_editBal_monto').css('border', '1px solid red');
		$('#modal_editBal_monto').focus();
		$('#modal_editBal_btn_guardar').show();
		swal({
			title: "Ingrese un monto",
			text: "Debe ingresar un monto para registrar la transacción.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		});
		return false;
	}

	if(modal_editBal_tipo_transaccion==='down_balance'){
		if(!(parseFloat(monto)<=parseFloat(gen_balance_total))){
			$('#modal_editBal_monto').css('border', '1px solid red');
			$('#modal_editBal_monto').focus();
			$('#modal_editBal_btn_guardar').show();
			swal({
				title: "Monto inválido",
				text: "No puede ingresar un monto mayor al balance actual.",
				type: 'info',
				timer: 3500,
				showConfirmButton: true
			});
			return false;
		}
		if(!(parseFloat(balance_nuevo)>=0)){
			$('#modal_editBal_monto').css('border', '1px solid red');
			$('#modal_editBal_monto').focus();
			$('#modal_editBal_btn_guardar').show();
			return false;
		}

		if(nuevo_balance_disponible < 0){
			$('#modal_editBal_btn_guardar').show();
			swal({
				title: "Verificar Balance",
				text: "El " + texto_balance_disponible.replace("S/","") + ' no puede ser menor a 0',
				type: 'info',
				timer: 3500,
				showConfirmButton: true
			});
			return false;
		}
	}

    if($.trim(observacion) === ''){
        $('#modal_editBal_observacion').css('border', '1px solid red');
        $('#modal_editBal_observacion').focus();
        $('#modal_editBal_btn_guardar').show();
		swal({
			title: "Ingrese Observación",
			text: "Debe ingresar una observación para registrar la transacción.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		});
        return false;
    }



	var data = new FormData();
	data.append('accion', "editar_balance");
	data.append('id_cliente', gen_cliente_id);
	data.append('tipo_transaccion', modal_editBal_tipo_transaccion);
	data.append('tipo_balance', balance_tipo);

	data.append('motivo_balance', motivo_balance);
	data.append('juego_balance', juego_balance);
	data.append('id_transaccion_juego', id_transaccion_juego);	
	data.append('supervisor_balance', supervisor_balance);
	data.append('cajero_balance', cajero_balance);


	data.append('monto', monto);
   data.append('observacion', observacion);

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "editar_balance", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_editBal').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_editBal').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "editar_balance_error", "data": result});
			return false;
		}
	});
	return false;
}












//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// PORTAL TORITO
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************




function abrir_torito() {
	//console.log('entro torito: ' +gen_cliente_id);
	var temp_balance_monto = 0;
	var temp_balance_texto = 'Nombre: ' + $('#cliente_nombre').val() + ' ' + $('#cliente_apepaterno').val();
	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	if(parseInt(temp_balance_tipo)===1) {
		temp_balance_monto = gen_balance_total;
		temp_balance_texto += ' - Balance actual: S/ ' + temp_balance_monto;
	} else if ( parseInt(temp_balance_tipo)===6 ) {
		temp_balance_monto = gen_balance_dinero_at;
		temp_balance_texto += ' - Bono AT: S/ ' + temp_balance_monto;
	} else {
		swal('Aviso', 'Por favor, seleccione una opción válida, Saldo Real ó Saldo Promocional', 'warning');
		return false;
	}

	if ($('#sec_tlv_div_portal_torito_individual_' + gen_cliente_id).length > 0) {
		$('.sec_tlv_div_portal_torito_individual').hide();
		$('.sec_tlv_div_etiquetas').hide();
		$('.sec_tlv_div_cliente').hide();
		$('.sec_tlv_div_transacciones').hide();
		$('.sec_tlv_div_portal_altenar_individual').hide();
		$('.sec_tlv_div_portal_torito').show();
		
		$('#sec_tlv_div_portal_torito_individual_' + gen_cliente_id).show();
		$('#sec_tlv_div_portal_torito_individual_head_' + gen_cliente_id).html(temp_balance_texto);
	} else {

	var data = new FormData();
	data.append('accion', "generar_url_torito");
	data.append('id_cliente', gen_cliente_id);
	data.append('balance_tipo', temp_balance_tipo);
	data.append('balance_monto', temp_balance_monto);

	$.ajax({
		url: "/sys/set_televentas.php",
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
			if (parseInt(respuesta.http_code) == 200) {
				//console.log(respuesta.result);
				$('.sec_tlv_div_portal_torito_individual').hide();
				$('.sec_tlv_div_etiquetas').hide();
				$('.sec_tlv_div_cliente').hide();
				$('.sec_tlv_div_transacciones').hide();
				$('.sec_tlv_div_portal_altenar_individual').hide();
				$('.sec_tlv_div_portal_torito').show();

				if ($('#sec_tlv_div_portal_torito_individual_' + gen_cliente_id).length > 0) {
					$('#sec_tlv_div_portal_torito_individual_' + gen_cliente_id).show();
					$('#sec_tlv_div_portal_torito_individual_head_' + gen_cliente_id).html(temp_balance_texto);
				} else {
					$('.sec_tlv_div_portal_torito').append(
						'<div class="panel sec_tlv_div_portal_torito_individual" id="sec_tlv_div_portal_torito_individual_' + gen_cliente_id + '" ' +
						'		style="border-color: transparent; margin: 0px;">' +
						'	<div class="panel-heading sec_tlv_div_portal_torito_individual_heading" '+
						'		style="border-color: #01579b;background: #fff; padding: 2px 10px 5px 0px;">' +
						'		<div class="row" style="color: #000;">' +
						'			<div class="col-md-10" style="font-size: 14px;" id="sec_tlv_div_portal_torito_individual_head_' + gen_cliente_id + '">' +
										temp_balance_texto +
						'			</div>' +
						'			<div class="col-md-2" style="padding: 0px;">' +
						'				<button type="button" title="Regresar" ' +
						'                   style="font-size: 10px; color: black; font-weight: bold; background-color: red;" ' +
						'					id="sec_tlv_div_portal_torito_btn_close_' + gen_cliente_id + '" ' +
						'					class="btn pull-right" onclick="sec_tlv_div_portal_torito_close()">' +
						'					<span aria-hidden="true" style="color: white; font-weight: bold;">&#x2716;</span>' +
						'				</button>' +
						'				<button type="button" title="Maximizar Torito" ' +
						'                   style="font-size:10px; color:black; font-weight:bold; background-color:blue; margin-right:10px;" ' +
						'					id="sec_tlv_div_portal_torito_btn_maximizar_' + gen_cliente_id + '" ' +
						'					class="btn pull-right" onclick="sec_tlv_div_portal_torito_maximizar()">' +
						'					<i class="icon arrow_expand" style="color: white; font-weight: bold;"></i>' +
						'				</button>' +
						'			</div>' +
						'		</div>' +
						'	</div>' +
						'	<div class="panel-body" style="text-align: center; padding: 0px;">' +
						'		<label style="color: red;">' +
						'			Las ventas mayores de S/2.00 SOLES no son contrastadas con el saldo del cliente. ' +
						'			PROCURAR QUE LA VENTA SEA LA CORRECTA.' +
						'		</label>' +
						'		<iframe src="' + respuesta.result + '" width="100%" height="820px" frameborder="0" ' +
						'			allowtransparency="true" class="sec_tlv_iframe_portal_torito"></iframe>' +
						'	</div>' +
						'</div>');
				}
				//sec_tlv_add_sportsbook_client_open(gen_cliente_id, 1);
				sec_tlv_module_client_open('torito', gen_cliente_id, 1);
				swal('El saldo disponible del cliente es: S/ '+temp_balance_monto, '', 'warning');
				return false;
			} else {
				auditoria_send({"respuesta": "generar_url_torito_error", "data": respuesta});
				swal('Aviso', respuesta.status, 'warning');
				//listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			return false;
		}
	});
	return false;

	}
	return false;
}







//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// PORTAL CALIMACO
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

$(function () {

	if (sec_id === 'televentas' || sec_id === 'retail_altenar') {

	}

});
function sec_tlv_div_portal_altenar_close() {
	$('.sec_tlv_div_portal_altenar').hide();
	$('#sec_tlv_div_portal_altenar_individual_' + gen_cliente_id).hide();
	$('.sec_tlv_div_etiquetas').show();
	$('.sec_tlv_div_cliente').show();
	$('.sec_tlv_div_transacciones').show();
	if ($('#sec_tlv_div_portal_altenar_individual_' + gen_cliente_id).length > 0) {
		$('#sec_tlv_div_portal_altenar_individual_' + gen_cliente_id).remove();
	}
	//sec_tlv_add_sportsbook_client_open(gen_cliente_id, 0);
	sec_tlv_module_client_open('sportsbook', gen_cliente_id, 0);
	return false;
}
function sec_tlv_div_portal_torito_close() {
	$('.sec_tlv_div_portal_torito').hide();
	$('#sec_tlv_div_portal_torito_individual_' + gen_cliente_id).hide();
	$('.sec_tlv_div_etiquetas').show();
	$('.sec_tlv_div_cliente').show();
	$('.sec_tlv_div_transacciones').show();
	if ($('#sec_tlv_div_portal_torito_individual_' + gen_cliente_id).length > 0) {
		$('#sec_tlv_div_portal_torito_individual_' + gen_cliente_id).remove();
	}
	sec_tlv_module_client_open('torito', gen_cliente_id, 0);
	return false;
}
function nuevo_portal_calimaco() {
	//console.log('entro calimaco: '+gen_cliente_id);
	var temp_balance_monto = 0;
	var temp_balance_texto = 'Nombre: ' + $('#cliente_nombre').val() + ' ' + $('#cliente_apepaterno').val();
	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	if(parseInt(temp_balance_tipo)===1) {
		temp_balance_monto = gen_balance_total;
		temp_balance_texto += ' - Balance actual: S/ ' + temp_balance_monto;
	} else if ( parseInt(temp_balance_tipo)===6 ) {
		temp_balance_monto = gen_balance_dinero_at;
		temp_balance_texto += ' - Bono AT: S/ ' + temp_balance_monto;
	} else {
		swal('Aviso', 'Por favor, seleccione una opción válida, Saldo Real ó Saldo Promocional', 'warning');
		return false;
	}
	if(sec_id === 'retail_altenar'){
		temp_balance_texto = '';
	}

	var data = new FormData();
	data.append('accion', "generar_url_calimaco");
	data.append('id_cliente', gen_cliente_id);
	data.append('calimaco_id', gen_calimaco_id);
	data.append('balance_tipo', temp_balance_tipo);
	data.append('balance_monto', temp_balance_monto);
	data.append('timestamp', Date.now());

	$.ajax({
		url: "/sys/set_televentas.php",
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
			if (parseInt(respuesta.http_code) == 200) {
				//console.log(respuesta.result);
				$('.sec_tlv_div_portal_altenar_individual').hide();
				$('.sec_tlv_div_etiquetas').hide();
				$('.sec_tlv_div_cliente').hide();
				$('.sec_tlv_div_transacciones').hide();
				$('.sec_tlv_div_portal_torito_individual').hide();
				$('.sec_tlv_div_portal_altenar').show();

				if ($('#sec_tlv_div_portal_altenar_individual_' + gen_cliente_id).length > 0) {
					$('#sec_tlv_div_portal_altenar_individual_' + gen_cliente_id).show();
					$('#sec_tlv_div_portal_altenar_individual_head_' + gen_cliente_id).html(temp_balance_texto);
				} else {
					/*
					$('.sec_tlv_div_portal_altenar').append(
						'<div class="panel sec_tlv_div_portal_altenar_individual" id="sec_tlv_div_portal_altenar_individual_' + gen_cliente_id + '" style="border-color: transparent;">' +
						'<div class="panel-heading" style="border-color: #01579b;background: #fff;">' +
						'<div class="row" style="color: #000;">' +
						'<div class="col-md-11" style="font-size: 14px;" id="sec_tlv_div_portal_altenar_individual_head_' + gen_cliente_id + '">' +
							temp_balance_texto +
						'</div>' +
						'<div class="col-md-1" style="padding: 0px;">' +
						'<button type="button" style="font-size: 30px; color: black; font-weight: bold; background-color: red;" class="btn pull-right" onclick="sec_tlv_div_portal_altenar_close()">' +
						'<span aria-hidden="true" style="color: white; font-weight: bold;">&times;</span>' +
						'</button>' +
						'</div>' +
						'</div>' +
						'</div>' +
						'<div class="panel-body">' +
						'<iframe src="' + respuesta.result + '" width="100%" height="820px" frameborder="0" allowtransparency="true"></iframe>' +
						'</div>' +
						'</div>');
					*/
					$('.sec_tlv_div_portal_altenar').append(
						'<div class="panel sec_tlv_div_portal_altenar_individual" id="sec_tlv_div_portal_altenar_individual_' + gen_cliente_id + '" ' +
						'		style="border-color: transparent; margin: 0px;">' +
						'	<div class="panel-heading sec_tlv_div_portal_altenar_individual_heading" style="border-color: #01579b;background: #fff; padding: 2px 10px 5px 0px;">' +
						'		<div class="row" style="color: #000;">' +
						'			<div class="col-md-10" style="font-size: 14px;" id="sec_tlv_div_portal_altenar_individual_head_' + gen_cliente_id + '">' +
										temp_balance_texto +
						'			</div>' +
						'			<div class="col-md-2" style="padding: 0px;">' +
						'				<button type="button" style="font-size: 10px; color: black; font-weight: bold; background-color: red;" title="Regresar" ' +
						'					id="sec_tlv_div_portal_altenar_btn_close_' + gen_cliente_id + '" ' +
						'					class="btn pull-right" onclick="sec_tlv_div_portal_altenar_close()">' +
						'					<span aria-hidden="true" style="color: white; font-weight: bold;">&#x2716;</span>' +
						'				</button>' +
						'				<button type="button" style="font-size: 10px; color: black; font-weight: bold; background-color: blue; margin-right: 10px;" title="Maximizar Sportsbook" ' +
						'					id="sec_tlv_div_portal_altenar_btn_maximizar_' + gen_cliente_id + '" ' +
						'					class="btn pull-right" onclick="sec_tlv_div_portal_altenar_maximizar()">' +
						'					<i class="icon arrow_expand" style="color: white; font-weight: bold;"></i>' +
						'				</button>' +
						'			</div>' +
						'		</div>' +
						'	</div>' +
						'	<div class="panel-body" style="text-align: center; padding: 0px;">' +
						'		<iframe src="' + respuesta.result + '" width="100%" height="820px" frameborder="0" '+
						'			allowtransparency="true" class="sec_tlv_iframe_portal_altenar"></iframe>' +
						'	</div>' +
						'</div>');
				}
				//sec_tlv_add_sportsbook_client_open(gen_cliente_id, 1);
				sec_tlv_module_client_open('sportsbook', gen_cliente_id, 1);
				/*
				 $('#modal_web_calimaco_iframe').html('');
				 $('#modal_web_calimaco_iframe').removeAttr('src');
				 $('#modal_web_calimaco_iframe').attr('src', respuesta.result);
				 $('#modal_web_calimaco_titulo').html('Nombre: '+$('#cliente_nombre').val()+' '+$('#cliente_apepaterno').val()+' - Balance actual: S/ '+gen_balance_total);
				 $('#modal_web_calimaco').modal('show');
				 */
				return false;
			} else {
				auditoria_send({"respuesta": "generar_url_calimaco_error", "data": respuesta});
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			return false;
		}
	});
	return false;
}
function sec_tlv_div_portal_torito_maximizar() {
	$('.navbar').css('display','none');
	$('.main-container').css('padding-top','0px');

	$('.sidebar').css('display','none');
	$('.main-container').css('padding-left','0px');

	$('.main-container').css('padding-bottom','0px');
	$('.sec_tlv_div_resultado').css('padding','0px');
	$('.sec_tlv_iframe_portal_torito').css('height','calc(100vh - 60px)');
	$('.sec_tlv_div_portal_torito_individual_heading').css('padding', '2px 0px 5px 0px');
	$('#sec_tlv_div_portal_torito_btn_maximizar_' + gen_cliente_id).html('<i class="icon arrow_condense" style="color: white; font-weight: bold;"></i>');
	$('#sec_tlv_div_portal_torito_btn_maximizar_' + gen_cliente_id).prop('title', 'Minimizar Torito');

	$('#tls_div_datos_caja').css('display','none');
	$('#tls_div_buscador').css('display','none');
	$('#div_tabs').css('display','none');

	$('#sec_tlv_div_portal_torito_btn_close_'+gen_cliente_id).css('display','none');
	$('#sec_tlv_div_portal_torito_btn_maximizar_'+gen_cliente_id).removeAttr("sec_tlv_div_portal_torito_maximizar");
	$('#sec_tlv_div_portal_torito_btn_maximizar_'+gen_cliente_id).attr("onclick", 'sec_tlv_div_portal_torito_minimizar()');

	return false;
}
function sec_tlv_div_portal_torito_minimizar() {
	$('.navbar').css('display','');
	$('.main-container').css('padding-top','70px');

	$('.sidebar').css('display','');
	if($('#contSearch').css('display')==='block'){
		$('.main-container').css('padding-left','200px');
	}
	if($('#contSearch').css('display')==='none'){
		$('.main-container').css('padding-left','50px');
	}

	$('.main-container').css('padding-bottom','55px');
	$('.sec_tlv_div_resultado').css('padding','0px 15px 0px 15px');
	$('.sec_tlv_iframe_portal_torito').css('height','820px');
	$('.sec_tlv_div_portal_torito_individual_heading').css('padding', '2px 10px 5px 0px');
	$('#sec_tlv_div_portal_torito_btn_maximizar_' + gen_cliente_id).html('<i class="icon arrow_expand" style="color: white; font-weight: bold;"></i>');
	$('#sec_tlv_div_portal_torito_btn_maximizar_' + gen_cliente_id).prop('title', 'Maximizar Torito');

	$('#tls_div_datos_caja').css('display','');
	$('#tls_div_buscador').css('display','');
	$('#div_tabs').show();
	$('#div_tabs').css('display','');

	$('#sec_tlv_div_portal_torito_btn_close_'+gen_cliente_id).css('display','');
	$('#sec_tlv_div_portal_torito_btn_maximizar_'+gen_cliente_id).removeAttr("sec_tlv_div_portal_torito_minimizar");
	$('#sec_tlv_div_portal_torito_btn_maximizar_'+gen_cliente_id).attr("onclick", 'sec_tlv_div_portal_torito_maximizar()');

	return false;
}
function sec_tlv_div_portal_altenar_maximizar() {
	$('.navbar').css('display','none');
	$('.main-container').css('padding-top','0px');

	$('.sidebar').css('display','none');
	$('.main-container').css('padding-left','0px');

	$('.main-container').css('padding-bottom','0px');
	$('.sec_tlv_div_resultado').css('padding','0px');
	$('.sec_tlv_iframe_portal_altenar').css('height','calc(100vh - 60px)');
	$('.sec_tlv_div_portal_altenar_individual_heading').css('padding', '2px 0px 5px 0px');
	$('#sec_tlv_div_portal_altenar_btn_maximizar_' + gen_cliente_id).html('<i class="icon arrow_condense" style="color: white; font-weight: bold;"></i>');
	$('#sec_tlv_div_portal_altenar_btn_maximizar_' + gen_cliente_id).prop('title', 'Minimizar Sportsbook');

	$('#tls_div_datos_caja').css('display','none');
	$('#tls_div_buscador').css('display','none');
	$('#div_tabs').css('display','none');

	$('#sec_tlv_div_portal_altenar_btn_close_'+gen_cliente_id).css('display','none');
	$('#sec_tlv_div_portal_altenar_btn_maximizar_'+gen_cliente_id).removeAttr("sec_tlv_div_portal_altenar_maximizar");
	$('#sec_tlv_div_portal_altenar_btn_maximizar_'+gen_cliente_id).attr("onclick", 'sec_tlv_div_portal_altenar_minimizar()');
	
	return false;
}
function sec_tlv_div_portal_altenar_minimizar() {
	$('.navbar').css('display','');
	$('.main-container').css('padding-top','70px');

	$('.sidebar').css('display','');
	if($('#contSearch').css('display')==='block'){
		$('.main-container').css('padding-left','200px');
	}
	if($('#contSearch').css('display')==='none'){
		$('.main-container').css('padding-left','50px');
	}

	$('.main-container').css('padding-bottom','55px');
	$('.sec_tlv_div_resultado').css('padding','0px 15px 0px 15px');
	$('.sec_tlv_iframe_portal_altenar').css('height','820px');
	$('.sec_tlv_div_portal_altenar_individual_heading').css('padding', '2px 10px 5px 0px');
	$('#sec_tlv_div_portal_altenar_btn_maximizar_' + gen_cliente_id).html('<i class="icon arrow_expand" style="color: white; font-weight: bold;"></i>');
	$('#sec_tlv_div_portal_altenar_btn_maximizar_' + gen_cliente_id).prop('title', 'Maximizar Sportsbook');

	$('#tls_div_datos_caja').css('display','');
	$('#tls_div_buscador').css('display','');
	$('#div_tabs').show();
	$('#div_tabs').css('display','');
	
	$('#sec_tlv_div_portal_altenar_btn_close_'+gen_cliente_id).css('display','');
	$('#sec_tlv_div_portal_altenar_btn_maximizar_'+gen_cliente_id).removeAttr("sec_tlv_div_portal_altenar_minimizar");
	$('#sec_tlv_div_portal_altenar_btn_maximizar_'+gen_cliente_id).attr("onclick", 'sec_tlv_div_portal_altenar_maximizar()');
	
	return false;
}


function portal_calimaco_guardar_apuesta(id_bet, monto) {
	swal('Aviso', 'Por favor realice un CTRL + F5', 'warning');
	return false;
}
function portal_calimaco_guardar_apuesta_2(id_bet, monto, place_bet) {
	
	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	var	evento_dineroat_id = $("#evento_dineroat_id").val();
	
	if (!(parseInt(monto) > 0)) {
		swal('Aviso', 'El monto obteniedo de la apuesta es inválido.', 'warning');
		return false;
	}
	if (!parseInt(id_bet) > 0) {
		swal('Aviso', 'El ID-BET obtenido es inválido.', 'warning');
		return false;
	}

	var data = new FormData();
	data.append('accion', "portal_calimaco_registrar_apuesta");
	data.append('id_cliente', gen_cliente_id);
	data.append('balance_tipo', temp_balance_tipo);
	data.append('evento_dineroat_id', evento_dineroat_id);
	data.append('id_bet', id_bet);
	data.append('monto', monto);
	data.append('place_bet', place_bet);

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "portal_calimaco_registrar_apuesta", "data": respuesta});
			//console.log(respuesta);
			/*
			 $('#modal_web_calimaco_iframe').removeAttr('src');
			 $('#modal_web_calimaco_iframe').html('');
			 $('#modal_web_calimaco').modal('hide');
			 */
			sec_tlv_div_portal_altenar_close();
			if ($('#sec_tlv_div_portal_altenar_individual_' + gen_cliente_id).length > 0) {
				$('#sec_tlv_div_portal_altenar_individual_' + gen_cliente_id).remove();
			}
			if (parseInt(respuesta.http_code) === 200) {
				auditoria_send({"respuesta": "registrar_apuesta", "data": respuesta});
				//swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				/*$.each(respuesta.list, function (index, item) {
					ver_voucher(item.id, item.txn_id, item.tipo_id, item.web_id, '', item.monto_deposito, item.comision_monto, item.monto, '',
							item.bono_monto, item.total_recarga, item.created_at, item.estado, login_tls_local, '', '', '', 0, '', '', '', 5, 'Altenar',
							login_tls_usuario, login_tls_usuario_nombre, '', '');
				});*/
				return false;
			} else if (parseInt(respuesta.http_code) === 400) {
				auditoria_send({"respuesta": "registrar_apuesta_error", "data": respuesta});
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			} else {
				swal('Aviso', 'Sin respuesta.', 'warning');
				return false;
			}
			return false;
		},
		error: function (result) {
			swal('Aviso', result, 'warning');
			sec_tlv_div_portal_altenar_close();
			if ($('#sec_tlv_div_portal_altenar_individual_' + gen_cliente_id).length > 0) {
				$('#sec_tlv_div_portal_altenar_individual_' + gen_cliente_id).remove();
			}
			/*
			 $('#modal_web_calimaco_iframe').removeAttr('src');
			 $('#modal_web_calimaco_iframe').html('');
			 $('#modal_web_calimaco').modal('hide');
			 */
			auditoria_send({"proceso": "portal_calimaco_registrar_apuesta_error", "data": result});
			return false;
		}
	});
	return false;
}

function portal_calimaco_guardar_apuesta_log(id_bet, monto, place_bet) {
	
	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	var data = new FormData();
	data.append('accion', "portal_calimaco_registrar_apuesta_log");
	data.append('id_cliente', gen_cliente_id);
	data.append('balance_tipo', temp_balance_tipo);
	data.append('id_bet', id_bet);
	data.append('monto', monto);
	data.append('place_bet', place_bet);

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "portal_calimaco_registrar_apuesta", "data": respuesta});
			//console.log(respuesta);
			return false;
		},
		error: function (result) {
			swal('Aviso', result, 'warning');
			auditoria_send({"proceso": "portal_calimaco_registrar_apuesta_error", "data": result});
			return false;
		}
	});
	return false;
}

















//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// DEPOSITO
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

var gen_modal_deposito_bono_lunes = 0;
var gen_modal_deposito_bono_limite = 0;
var gen_modal_deposito_bono_bloqueo_web = 0;
var gen_modal_deposito_bono_contacto = 0;
var gen_modal_deposito_bono_cuenta = 0;

$(function () {

	if (sec_id === 'televentas' || sec_id === 'retail_altenar') {
		$("#modal_deposito_cuenta").val('0').trigger('change'); 
		$("#modal_tipo_constancia").val('0').trigger('change'); 
		$('#modal_deposito_titular_nombre').select2();
		//Select
		/*$('#modal_deposito_tipo_contacto').change(function () {
			onchange_bono();
		});*/
		$('#modal_deposito_cuenta').change(function () {
			onchange_bono();
		});


		//Monto
		$("#modal_deposito_monto").on({
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
					onchange_bono();
				} else {
					$(event.target).val("0.00");
					onchange_bono();
				}
				//onkeyup_deposito_calcular_total();
			}
		});

		//Button guardar
		$('#modal_deposito_btn_guardar').click(function (event) {
			if (!event.detail || event.detail == 1) {
				$('#modal_deposito_btn_guardar').hide();
				guardar_deposito();
			}
			return false;
		});


		//Button buscar titular
		$('#modal_deposito_btn_buscar_dni').click(function () {
			buscar_limite_terceros();			

		});

		//Button guardar nombre titular
		$('#modal_deposito_btn_guardar_titular').click(function () {
			guardar_nomb_titular_abono();
			return false;
		});
	}

});

function guardar_nomb_titular_abono() {
	$('#modal_deposito_btn_guardar_titular').hide();
	var dni_titular = $('#modal_deposito_dni_titular_abono').val();
	var nombre_titular = $('#modal_nombre_titular_deposito').val();

	if (nombre_titular.length == 0 ) {
		swal('Aviso', 'Debe completar el campo.', 'warning');
		$("#modal_nombre_titular_deposito").focus();
		return false;
	}
	
	var data = {
		"accion": "guardar_titular_abono",
		"dni_titular": dni_titular,
		"nombre_titular": nombre_titular,
		"id_cliente": gen_cliente_id 
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
				//console.log(respuesta);
				return false;
			}
		 
			if (parseInt(respuesta.http_code) == 200) {
				//console.log(respuesta);
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				$('#sec_tlv_modal_reg_titular_ab').modal('hide');
				cargar_titular_abono_cliente();

				return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "guardar_titular_abono", "data": result});
		}
	});
	 
}

function sec_tlv_eliminar_tit_ab(id) {

	var data = {
		"accion": "eliminar_titular_abono",
		"id_cliente": gen_cliente_id ,
		"id_tit": id ,
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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

			if (parseInt(respuesta.http_code) == 200) {
				//console.log(respuesta);

				swal('Aviso', respuesta.status, 'info');
				$('#sec_tlv_modal_del_titular_ab').modal('hide');
				$('#modal_deposito').modal('hide');

			}else{

				swal('Aviso', respuesta.status, 'warning');
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "eliminar_titular_abono", "data": result});
		}
	});

}

function sec_tlv_modal_del_terceros() {
	
	var data = {
		"accion": "listado_eliminar_titular_abono",
		"id_cliente": gen_cliente_id 
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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

			if (parseInt(respuesta.http_code) == 200) {
				//console.log(respuesta);

				if ($.fn.dataTable.isDataTable('#modal_listado_terceros')) {
					$('#modal_listado_terceros').DataTable().destroy();
				}

				$('#sec_tlv_modal_del_titular_ab').modal('show');			 
				$('#sec_tlv_modal_del_titular_ab_table_tbody').html('');			
				i=0;
			
				$.each(respuesta.result, function(index, item) {
					i ++;

					$('#sec_tlv_modal_del_titular_ab_table_tbody').append(
						'<tr>'+
							'<td style="text-align: center;">' +  i + '</td>'+
							'<td style="text-align: center;">' + item.dni_titular + '</td>'+
							'<td style="text-align: center;">' + item.nombre_apellido_titular + '</td>'+
							'<td style="text-align: center;"><button type="button" class="btn btn-danger" onclick="sec_tlv_eliminar_tit_ab(' + item.id + ')">' +
							'<span class="fa fa-trash"></span>' +
							'</button></td>'+
						'</tr>'

					);
				});			 
				sec_tlv_tabla_datatable_formato('#modal_listado_terceros');
				return false;
			}else{

				$('#sec_tlv_modal_del_titular_ab').modal('show');			 
				$('#modal_listado_terceros tbody').html('');

				$('#modal_listado_terceros tbody').append(
					'<tr>' +
					'   <td colspan="4">No hay registros</td>' +
					'</tr>'
					);
					return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "listado_eliminar_titular_abono", "data": result});
		}
	});


}

function conteo_terceros(limite) {

	var data = {
		"accion": "obtener_televentas_cant_terceros",
		"id_cliente": gen_cliente_id 
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
				//console.log(respuesta);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {		

				var cant_terceros = 0;

				respuesta.result.forEach((elemento) => {	

					cant_terceros = parseInt(elemento.cant);					 
				}); 

				validar_limite_tercero(cant_terceros,limite);
				return false;

			}
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_televentas_cant_terceros", "data": result});
		}
	});
}

function validar_limite_tercero(cant,limite) {

	if (cant >= limite  ){

		swal({
			title: "Máximo de terceros alcanzado (" + limite + ")",			 
			type: 'warning',
			showConfirmButton: true
		});
		return false; 

	}else{
		buscar_titular_abono();
		return false;
	}
}


function buscar_limite_terceros() { 
	var data = {
		"accion": "obtener_televentas_limite_terceros",
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
				//swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {

				var limite_terceros = 0;

				respuesta.result.forEach((elemento) => {

					limite_terceros = parseInt(elemento.limite_terc);
				});

				conteo_terceros(limite_terceros); 

				return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_televentas_limite_terceros", "data": result});
		}
	});


}

function sec_tlv_modal_deposito_habilitar(btn) {

	if(btn.checked == 1){
		$('#modal_deposito_dni_titular_abono').removeAttr('disabled');
		$('#modal_deposito_btn_buscar_dni').removeAttr('disabled');
		$('#modal_deposito_titular_nombre').removeAttr('disabled');
		$('#modal_deposito_titular_div').css({"display": "block"});
	}else{
		$('#modal_deposito_dni_titular_abono').attr('disabled', 'disabled');
		$('#modal_deposito_btn_buscar_dni').attr('disabled', 'disabled');
		$('#modal_deposito_dni_titular_abono').val('');
	 
		$('#modal_deposito_titular_nombre').attr('disabled', 'disabled');
		$('#modal_deposito_titular_div').css({"display": "none"});
	}
}

function cargar_titular_abono_cliente() {

	$('#modal_deposito_titular_nombre').empty();	
	$('#cliente_tercero_titular').empty();

	var data = {
		"accion": "obtener_televentas_titular_abono_reg",
		"id_cliente": gen_cliente_id 
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
				//console.log(respuesta);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				//console.log(respuesta);

				$.each(respuesta.result, function(index, item) {
					$('#modal_deposito_titular_nombre').append(
						'<option value="' + item.id + '" '+(item.estado == 1 ? 'selected' : '') +' >' + item.nombre_apellido_titular + ' - ' + item.dni_titular + '</option>'
					);
				});

				$.each(respuesta.result, function(index, item) {
					$('#cliente_tercero_titular').append(
						'<option value="' + item.id + '" '+(item.estado == 1 ? 'selected' : '') +' >' + item.nombre_apellido_titular + '</option>'
					);
				});
			 
				return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_televentas_titular_abono_reg", "data": result});
		}
	});
}

function buscar_titular_abono() {

	var dni_titular = $('#modal_deposito_dni_titular_abono').val();
	//console.log(dni_titular);

	if (dni_titular.length < 8 ) {
		swal('Aviso', 'Debe ingresar un número de documento valido.', 'warning');
		$("#modal_deposito_dni_titular_abono").focus();
		return false;
	}
	
	var data = {
		"accion": "busqueda_titular_abono_tbl_clientes",
		"dni_titular": dni_titular,
		"id_cliente": gen_cliente_id 
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "busqueda_titular_abono_tbl_clientes", "data": respuesta});				 
			if (parseInt(respuesta.http_code) == 400) {
				console.log(respuesta.http_code);
				swal('Aviso', respuesta.status, 'warning');
				//console.log(respuesta);
				return false;
			}
			if (parseInt(respuesta.http_code) == 300) {
				console.log(respuesta.http_code);
				console.log(dni_titular.length);

				if (dni_titular.length > 8 ) {
					swal('Aviso', respuesta.status, 'info');
					$('#sec_tlv_modal_reg_titular_ab').modal('show');
					$('#modal_nombre_titular_deposito').val('');
					$('#modal_deposito_btn_guardar_titular').show();
					//console.log(respuesta);
					return false;

				}else{
					
					cargar_img_captcha(dni_titular, 1);
				}
			}
			if (parseInt(respuesta.http_code) == 301) {
				console.log(respuesta.http_code);

				swal({
					html: true,
					title: respuesta.status,
					type: 'warning',
					showCancelButton: true,				
					confirmButtonText: 'SI',
					cancelButtonText: 'NO',
					closeOnConfirm: true,
					//,showLoaderOnConfirm: true
				}, function () {
					editar_titular_abono_eliminado(respuesta.result);
					return false;
				});

			 return false;

			}
			if (parseInt(respuesta.http_code) == 200) {
				console.log(respuesta.http_code);
				//console.log(respuesta);
				cargar_titular_abono_cliente();

				return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "busqueda_titular_abono_tbl_clientes_error", "data": result});
		}
	});
	
}


function sec_tlv_reg_tercero_titular(dni_titular) {

	var data = {
		"accion": "busqueda_titular_abono_api",
		"dni_titular": dni_titular,
		"id_cliente": gen_cliente_id 
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "busqueda_titular_abono_api", "data": respuesta});	 
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.status, 'warning');
				//console.log(respuesta);
				return false;
			}
			
			if (parseInt(respuesta.http_code) == 401) {
				$('#sec_tlv_modal_reg_titular_ab').modal('show');
				$('#modal_nombre_titular_deposito').val('');
				$('#modal_deposito_btn_guardar_titular').show()
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				//console.log(respuesta);
				cargar_titular_abono_cliente();

				return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "busqueda_titular_abono_api_error", "data": result});
		}
	});
}


function editar_titular_abono_eliminado(datos) {
 
	$('#sec_tlv_modal_reg_titular_ab').modal('show');

	$.each(datos, function(index, item) {		 
		$('#modal_nombre_titular_deposito').val(item.nombre_apellido_titular);
	});

	$('#modal_deposito_btn_guardar_titular').show();
}


function validar_limite_dep() {
	 
	var data = {
		"accion": "obtener_televentas_cont_cajero",
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
				//swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				 
				respuesta.result.forEach((elemento) => {
 
					//console.log(elemento.cont_temp);
					let limit_cli = 0 ;
					limit_cli = elemento.cont_temp;

					/*if(limit_cli < limite_cli_total){
						nuevo_deposito();
					}else{
						swal({
							title: "Límite de atención alcanzado (" + limite_cli_total + ")",
							 
							type: 'info',
							timer: 5000,
							showConfirmButton: true
						});
						return false; 
					}*/

					nuevo_deposito();
				});
			 
				return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_televentas_limite_cajero", "data": result});
		}
	});
	


	
}

function nuevo_deposito() {
	$('#cliente_idweb_div').css('border', '');
	$('#modal_deposito_imagen_preview').html('');
	var total_bono_mes = $('#span_bonos').html();
	var bono_limite_actual = $('#bono_limite').val();
	var web_id = 0;
	$.each(array_clientes, function (index, item) {
		if (parseInt(item.id) === parseInt(gen_cliente_id)) {
			if (parseInt(item.web_id) > 0) {
				web_id = item.web_id;
				//console.log('web_id: '+web_id);
			}
		}
	});

	gen_modal_deposito_bono_limite = 0;
	gen_modal_deposito_bono_bloqueo_web = 0;
	gen_modal_deposito_bono_contacto = 0;
	gen_modal_deposito_bono_cuenta = 0;
	sec_tlv_cargar_cuentas_televentas();
	sec_tlv_cargar_tipo_constancias();
	limpiar_bordes_modal_deposito();
	limpiar_campos_modal_deposito();
	cargar_titular_abono_cliente();
	$('#modal_deposito_idweb').val(web_id);
	$('#modal_deposito_bono_div_select').show();
	$('#modal_deposito_bono_div_texto').hide();
	onchange_bono();


	$('#modal_deposito_bono_select').html('');
	$('#modal_deposito_bono_select').append('<option value="0">Ninguno</option>');
	$('#modal_deposito_bono_select').val('0');
	$.post("/sys/set_televentas.php", {
		accion: "obtener_lista_bonos_disponibles"
	})
			.done(function (data) {
				try {
					//console.log(data);
					var respuesta = JSON.parse(data);
					if (parseInt(respuesta.http_code) == 200) {
						// BONOS
						if (respuesta.result_bonos_disponibles.length > 0) {
							$.each(respuesta.result_bonos_disponibles, function (index3, item3) {
								$('#modal_deposito_bono_select').append('<option value="'+item3.id+'">'+item3.bono+'</option>');
							});
						}
					}
				} catch (e) {
					swal('¡Error!', e, 'error');
				//	console.log("Error de TRY-CATCH --> Error: " + e);
				}
			})
			.fail(function (xhr, status, error) {
				swal('¡Error!', error, 'error');
			//	console.log("Error de .FAIL -- Error: " + error);
			});

	if (parseInt(web_id) > 0) {
		/*
		 if(parseInt(gen_dia_semana)!==1){ //Para dias diferentes a Lunes
		 */
		if (parseFloat(total_bono_mes) > parseFloat(bono_limite_actual)) {
			//console.log("bono_limite_actual: "+bono_limite_actual);
			//console.log("total_bono_mes: "+total_bono_mes);
			gen_modal_deposito_bono_limite = 1;
			$('#modal_deposito_bono_div_select').hide();
			$('#modal_deposito_bono_div_texto').show();
			$('#modal_deposito_bono_texto').html('El bono ha sido deshabilitado por super el límite de bono.');
		} else {

			$.post("/sys/set_televentas.php", {
				accion: "obtener_bloqueo_bono",
				client_id: gen_cliente_id,
				web_id: web_id
			})
					.done(function (data) {
						try {
							//console.log(data);
							var respuesta = JSON.parse(data);
							if (parseInt(respuesta.http_code) == 200) {
								if (parseInt(respuesta.result_IsNoBonus) === 1) {
									gen_modal_deposito_bono_bloqueo_web = 1;
									$('#modal_deposito_bono_div_select').hide();
									$('#modal_deposito_bono_div_texto').show();
									$('#modal_deposito_bono_texto').html('El cliente ha sido excluido de nuestras promociones por el área de riesgos hasta nuevo aviso.');
								}
							}
						} catch (e) {
							swal('¡Error!', e, 'error');
							// console.log("Error de TRY-CATCH --> Error: " + e);
						}
					})
					.fail(function (xhr, status, error) {
						swal('¡Error!', error, 'error');
					//	console.log("Error de .FAIL -- Error: " + error);
					});
		}
		/*
		 } else { // Día Lunes
		 gen_modal_deposito_bono_lunes=1;
		 $('#modal_deposito_bono_div_select').hide();
		 $('#modal_deposito_bono_div_texto').show();
		 $('#modal_deposito_bono_texto').html('El bono ha sido deshabilitado por ser Lunes.');
		 }
		*/

		$('#modal_deposito_btn_guardar').show();
		$('#modal_deposito').modal();
		return false;
	} else {
		/*
		$('#cliente_idweb_div').css('border', '1px solid red');
		$('#cliente_idweb').focus();
		swal('Aviso', 'Debe guardar el ID-WEB en la sección de información del cliente.', 'warning');
		return false;
		*/
		gen_modal_deposito_bono_bloqueo_web = 1;
		$('#modal_deposito_bono_div_select').hide();
		$('#modal_deposito_bono_div_texto').show();
		$('#modal_deposito_bono_texto').html('El bono ha sido deshabilitado por no tener ID-WEB.');
		
		$('#modal_deposito_btn_guardar').show();
		$('#modal_deposito').modal();
		return false;
	}
}
function limpiar_campos_modal_deposito() {
	$('#modal_deposito_voucher').val('');
	$("#modal_deposito_cuenta").val('0');
	$('#modal_deposito_cuenta').select2().trigger('change');
	$('#modal_tipo_constancia').select2().trigger('change');
	$("#modal_tipo_constancia").val('0');
	$('#sec_tlv_modal_deposito_tipo_constancia_div').hide();
	$('#modal_deposito_idweb').val('');
	$('#modal_deposito_monto').val('');
	$('#modal_deposito_comision').val('');
	$('#modal_deposito_monto_real').val('');
	$('#modal_deposito_bono_select').val(0);
	$('#modal_deposito_bono_texto').html('');
	$('#modal_deposito_bono').val('');
	$('#modal_deposito_total').val('');
	$('textarea#modal_deposito_observacion').val('');
	$('#modal_deposito_titular_abono').val('');
	$('#modal_deposito_tipo_apuesta').val('0').trigger('change');

 
	$('#modal_deposito_habilitar_dni').removeAttr("checked");
	$('#modal_deposito_dni_titular_abono').val('');
	$('#modal_deposito_dni_titular_abono').attr('disabled', 'disabled');
	$('#modal_deposito_btn_buscar_dni').attr('disabled', 'disabled');
 
	$('#modal_deposito_titular_nombre').val('');
	$('#modal_deposito_titular_nombre').attr('disabled', 'disabled');
	$('#modal_deposito_titular_div').css({"display": "none"});

	$('#sec_tlv_modal_deposito_fecha_abono_div').hide();
	
	const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    var temp_fecha_hora_actual = now.toISOString().slice(0, -5);
	$("#modal_deposito_fecha").val(temp_fecha_hora_actual.substr(0,16));
}
function limpiar_bordes_modal_deposito() {
	$('#modal_deposito_voucher_div').css('border', '');
	$('#modal_deposito_cuenta_div').css('border', '');
	$('#modal_tipo_constancia_div').css('border', '');
	$('#modal_deposito_monto').css('border', '');
	$('#modal_deposito_tipo_apuesta_div').css('border', '');
}
function onchange_bono() {
	var tipo_contacto = $('#modal_deposito_tipo_contacto').val();
	//if (parseInt(gen_modal_deposito_bono_lunes) === 0 && parseInt(gen_modal_deposito_bono_limite) === 0 &&
	if (parseInt(gen_modal_deposito_bono_limite) === 0 &&
			parseInt(gen_modal_deposito_bono_bloqueo_web) === 0 &&
			parseInt(gen_modal_deposito_bono_cuenta) === 0) {
		if (parseInt(tipo_contacto) === 2) {
			$('#modal_deposito_bono_texto').html('');
			cuenta_bono = $('#modal_deposito_cuenta option:selected').attr("bono");
			if (parseInt(cuenta_bono) === 1) {
				gen_modal_deposito_bono_contacto = 0;
				var deposito_monto = $("#modal_deposito_monto").val().replace(/\,/g, '');
				if (parseFloat(deposito_monto) >= 40) {
					$('#modal_deposito_bono_div_select').show();
					$('#modal_deposito_bono_div_texto').hide();
				} else {
					$('#modal_deposito_bono_div_select').hide();
					$('#modal_deposito_bono_select').val(0);
					$('#modal_deposito_bono_div_texto').show();
					$('#modal_deposito_bono_texto').html('El bono solo es valido para un deposito mayor o igual a 40 soles.');
				}
			} else {
				gen_modal_deposito_bono_contacto = 1;
				$('#modal_deposito_bono_div_select').hide();
				$('#modal_deposito_bono_select').val(0);
				$('#modal_deposito_bono_div_texto').show();
				$('#modal_deposito_bono_texto').html('La cuenta bancaria no permite bono.');
			}
			//onkeyup_deposito_calcular_total();
		} else {
			gen_modal_deposito_bono_contacto = 1;
			$('#modal_deposito_bono_select').val(0);
			$('#modal_deposito_bono_div_select').hide();
			$('#modal_deposito_bono_div_texto').show();
			$('#modal_deposito_bono_texto').html('El bono solo es para clientes contactados en Telegram.');
		}
	}
}
 
function sec_tlv_cargar_tipo_constancias(){
	var data = {
		"accion": "obtener_televentas_tipo_constancias"
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
				$('#modal_tipo_constancia').html('');
				$('#modal_tipo_constancia').append('<option value="0">:: Seleccione ::</option>');
				$.each(respuesta.result, function(index, item){
					$('#modal_tipo_constancia').append(
						'<option value="' + item.id + '">' + item.descripcion +  
						'</option>'
					);
				});
			 
				return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_televentas_tipo_constancias_error", "data": result});
		}
	});
}

function guardar_deposito() {
	$('#modal_deposito_btn_guardar').hide();
	limpiar_bordes_modal_deposito();

	var tipo_apuesta = $('#modal_deposito_tipo_apuesta').val();
	var cuenta = $('#modal_deposito_cuenta').val();
	var tipo_constancia = $('#modal_tipo_constancia').val();
	var idweb = $('#modal_deposito_idweb').val();
	var bono_select = 0; /* $('#modal_deposito_bono_select').val(); */
	var bono_nombre = $('#modal_deposito_bono_select option:selected').text();
	var monto = $('#modal_deposito_monto').val().replace(/\,/g, '');
	var titular_abono_nombre = '';
	
	if (document.getElementById('modal_deposito_habilitar_dni').checked){
		habilitar_dni = 1;
		var titular_abono = $('#modal_deposito_titular_nombre').val();
	 	titular_abono_nombre = $('#modal_deposito_titular_nombre option:selected').text();
	}
	else{
		habilitar_dni = 0;
		var titular_abono = '0';
	}

	var observacion = $.trim(/* $('#modal_deposito_bono_texto').html() + " " +  */$('textarea#modal_deposito_observacion').val());

	var imagen = $('#modal_deposito_voucher').val();
	var f_imagen = $("#modal_deposito_voucher")[0].files[0];
	var imagen_extension = imagen.substring(imagen.lastIndexOf("."));
	var id_validacion_yape = 0;

	if (!(imagen.length > 0)) {
		$("#modal_deposito_voucher_div").css("border", "1px solid red");
		swal('Aviso', 'Agregue una imágen.', 'warning');
		$('#modal_deposito_btn_guardar').show();
		return false;
	}
	if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
			imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
			imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG") {
		$("#modal_deposito_voucher_div").css("border", "1px solid red");
		swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
		$('#modal_deposito_btn_guardar').show();
		return false;
	}

	/*if (!(parseInt(tipo_contacto) > 0)) {
		$('#modal_deposito_tipo_contacto_div').css('border', '1px solid red');
		$('#modal_deposito_tipo_contacto').focus();
		$('#modal_deposito_btn_guardar').show();
		return false;
	}*/
	if (!(parseInt(cuenta) > 0)) {
		$('#modal_deposito_cuenta_div').css('border', '1px solid red');
		$('#modal_deposito_cuenta').focus();
		$('#modal_deposito_btn_guardar').show();
		return false;
	}
	/*
	if (!(parseFloat(idweb) > 0)) {
		$('#modal_deposito_idweb').css('border', '1px solid red');
		$('#modal_deposito_idweb').focus();
		$('#modal_deposito_btn_guardar').show();
		return false;
	}
	*/
	if (!(parseFloat(monto) > 0)) {
		$('#modal_deposito_monto').css('border', '1px solid red');
		$('#modal_deposito_monto').focus();
		$('#modal_deposito_btn_guardar').show();
		return false;
	}


	var total_bono_mes = $('#span_bonos').html().replace(/\,/g, '');
	var bono_limite_actual = $('#bono_limite').val().replace(/\,/g, '');

	if (bono_select > 0) {
		var bono = parseFloat(monto) * 0.05;
		if (parseFloat(monto) < 40) {
			swal('Aviso', 'El monto del bono debe ser mayor o igual a 40 soles.', 'warning');
			$('#modal_deposito_monto').css('border', '1px solid red');
			$('#modal_deposito_monto').focus();
			$('#modal_deposito_btn_guardar').show();
			return false;
		}
		if (parseFloat(total_bono_mes) > parseFloat(bono_limite_actual)) {
			swal('Aviso', 'El monto del bono del mes supera el limite del bono mensual permitido.', 'warning');
			$('#modal_deposito_btn_guardar').show();
			return false
		}
		if ((parseFloat(total_bono_mes) + parseFloat(bono)) > parseFloat(bono_limite_actual)) {
			swal('Aviso', 'El monto del bono del mes sumado al nuevo bono, supera el limite del bono mensual permitido.', 'warning');
			$('#modal_deposito_btn_guardar').show();
			return false
		}
	}

	if(!parseInt(tipo_apuesta) > 0){
		$('#modal_deposito_tipo_apuesta_div').css('border', '1px solid red');
		$('#modal_deposito_tipo_apuesta').focus();
		$('#modal_deposito_btn_guardar').show();
		return false;
	}
 
	var is_yape = $('#modal_deposito_cuenta option:selected').attr("is_yape");
	if(is_yape == 1){
		if(!parseInt(tipo_constancia) > 0){
			$('#modal_tipo_constancia_div').css('border', '1px solid red');
			$('#modal_tipo_constancia').focus();
			$('#modal_deposito_btn_guardar').show();
			return false;
		}
	}else{
		var tipo_constancia = 0;
	}

	var data = new FormData();
	data.append('accion', "guardar_transaccion_deposito");
	data.append('id_cliente', gen_cliente_id);
	data.append('idweb', idweb);
	data.append('imagen_voucher', f_imagen);
	data.append('cuenta', cuenta);
	data.append('tipo_constancia', tipo_constancia);
	data.append('bono_id', bono_select);
	data.append('monto', monto);
	data.append('observacion', observacion);
	data.append('total_bono_mes', total_bono_mes);
	data.append('titular_abono', titular_abono);
	data.append('tipo_apuesta', tipo_apuesta);

	
	if(is_yape == 1){
	 
		$('#sec_tlv_modal_valid_yape_btn_guardar').show();
		var cliente_nombre = $('#cliente_nombre').val();
		var cliente_apepa  = $('#cliente_apepaterno').val();
		var cliente_apema  = $('#cliente_apematerno').val();
		var amount         = $('#modal_deposito_monto').val().replace(/\,/g, '');
		var fecha          = $('#modal_deposito_fecha').val();
		var banco          = $('#modal_deposito_cuenta option:selected').text();
		var nombre_titular_abono = '';
		
		if(habilitar_dni == 1){
			const posicionCaracter = titular_abono_nombre.indexOf('-');
			if (posicionCaracter !== -1) {
				nombre_titular_abono = titular_abono_nombre.slice(0, (posicionCaracter - 1));
			} else {
			    nombre_titular_abono = titular_abono_nombre;
			}
		}
		
		var data_valid = {
			accion: "sec_tlv_obtener_yapes_pendientes",
			nombre: cliente_nombre,
			apepa: cliente_apepa,
			apema: cliente_apema,
			monto: amount,
			fecha: fecha,
			banco: banco,
			nombre_t: nombre_titular_abono
		}

		auditoria_send({"proceso": "sec_tlv_obtener_yapes_pendientes", "data": data_valid});
		$.ajax({
			url: "/sys/set_televentas.php",
			type: 'POST',
			data: data_valid,
			beforeSend: function () {
				loading("true");
			},
			complete: function () {
				loading();
			},
			success: function (resp) {
				try{
					var respuesta = JSON.parse(resp);
					$('#sec_tlv_modal_valid_yape_table tbody').html('');
					if (parseInt(respuesta.http_code) == 400){
						swal({
							html: true,
							title: 'No se encontró yapes pendientes <br> <span style="color: red; font-weight:bold">Esto puede generar una duplicidad en el cliente</span> <br> ¿Desea enviar la solicitud de depósito a los validadores?',
							type: 'warning',
							showCancelButton: true,
							confirmButtonColor: '#0336FF',
							cancelButtonColor: '#d33',
							confirmButtonText: 'SI, ENVIAR',
							cancelButtonText: 'NO',
							closeOnConfirm: false
						}, function (isConfirm) {
							if (isConfirm) {
						    	data.append('id_validacion_yape', 0);
								data.append('fecha_abono', '');
								guardar_deposito_confirmar(data);
							} else {
								$('#modal_deposito_btn_guardar').show();
							}
							return false;
						});
					}
					if (parseInt(respuesta.http_code) == 200) {
						$('#sec_tlv_modal_valid_yape').modal('show');
						$('#sec_tlv_modal_valid_yape').css('display', 'flex');
						$('#sec_tlv_modal_valid_yape').css('align-items', 'center');
						var c = 1;
						$.each(respuesta.result, function (index, item) {
							$('#sec_tlv_modal_valid_yape_table tbody').append(
								'<tr class="sec_modal_deposito_listado_yapes">' +
								'	<td>' + c + '</td>' +
								'	<td style="text-align: center;"><input type="radio" name="sec_modal_valid_yape_radio" id="sec_modal_valid_yape_radio' + item.id + '" class="sec_modal_valid_yape_radio"/></td>' +
								'	<td style="display: none;"><input class="sec_tlv_modal_valid_yape_id_trans" type="hidden" value="' + item.id + '"/></td>' +
								'	<td><label for="sec_modal_valid_yape_radio' + item.id + '">' + item.yape + '</label></td>' +
								'	<td><label for="sec_modal_valid_yape_radio' + item.id + '">' + item.registrado + '</label></td>' +
								'	<td><label for="sec_modal_valid_yape_radio' + item.id + '">' + item.persona + '</label></td>' +
								'	<td style="font-weight: bold; color: black;"><label for="sec_modal_valid_yape_radio' + item.id + '">' + item.monto + '</label></td>' +
								'</tr>'
							);
							c++;
						});
					}
				}catch (e) {
					swal({
						html: true,
						title: 'Ocurrió un error al obtener las validaciones de yape. <br> ¿Desea enviar la solicitud de depósito a los validadores?',
						type: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#0336FF',
						cancelButtonColor: '#d33',
						confirmButtonText: 'SI, ENVIAR',
						cancelButtonText: 'NO',
						closeOnConfirm: false
					}, function () {
						data.append('id_validacion_yape', 0);
						data.append('fecha_abono', '');
						guardar_deposito_confirmar(data);
						return false;
					});
				}
				return false;
			},
			error: function (result) {
				//auditoria_send({"proceso": "sec_tlv_obtener_yapes_pendientes_error", "data": result});
				return false;
			}
		});
	}else{
		data.append('id_validacion_yape', 0);
		data.append('fecha_abono', '');
		if (parseInt(bono_select) > 0) {
			swal({
				html: true,
				title: '¿Está seguro de realizar el depósito con el <span style="font-weight: 900;color: black;text-transform: uppercase;">' +
						bono_nombre + '</span>?',
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#0336FF',
				cancelButtonColor: '#d33',
				confirmButtonText: 'SI, CONFIRMAR',
				cancelButtonText: 'CANCELAR',
				closeOnConfirm: false,
				//,showLoaderOnConfirm: true
			}, function () {
				guardar_deposito_confirmar(data);
				return false;
			});
			$('#modal_deposito_btn_guardar').show();
		} else {
			guardar_deposito_confirmar(data);
			return false;
		}
		return false;
	}
}
function guardar_deposito_confirmar(data) {
	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "guardar_transaccion_deposito", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 402) {
				$('#sec_tlv_modal_valid_yape').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				$('#modal_deposito_btn_guardar').show();
				return false;
			}
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_deposito').modal('hide');
				$('#sec_tlv_modal_valid_yape').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 201){
				$('#sec_tlv_modal_valid_yape').modal('hide');
				$('#modal_deposito_btn_guardar').show();
				swal({
					html: true,
					title: respuesta.status,
					type: 'warning',
					confirmButtonColor: '#0336FF',
					confirmButtonText: 'De acuerdo.',
					closeOnConfirm: true
				});
				return false;
			} 
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_deposito').modal('hide');
				$('#sec_tlv_modal_valid_yape').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "guardar_transaccion_deposito_error", "data": result});
			return false;
		}
	});
	return false;
}









//************************************************************************************************
//************************************************************************************************
// RECARGA WEB
//************************************************************************************************
//************************************************************************************************
$(function () {
	if (sec_id === 'televentas' || sec_id === 'retail_altenar') {

		$("#modal_recargaweb_deposito_list").on({
			"change": function (event) {
				if (parseInt($('option:selected', event.target).val()) > 0) {
					$("#modal_recargaweb_monto").val($('option:selected', event.target).attr('monto'));
					//$("#modal_recargaweb_bono").val($('option:selected', event.target).attr('bono_monto'));
				} else {
					$("#modal_recargaweb_monto").val('0.00');
					$("#modal_recargaweb_bono").val('0.00');
				}
				calcular_total_modal_recargaweb();
			}
		});
		//Monto
		$("#modal_recargaweb_monto").on({
			"focus": function (event) {
				$(event.target).select();
				//console.log('focus');
			},
			"blur": function (event) {
				//console.log('blur');
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
				calcular_total_modal_recargaweb();
			}
		});

		/*
		 $("#modal_recargaweb_bono").on({
		 "focus": function (event) {
		 $(event.target).select();
		 //console.log('focus');
		 },
		 "blur": function (event) {
		 //console.log('blur');
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
		 calcular_total_modal_recargaweb();
		 }
		 });
		 */

		//Button guardar
		$('#modal_recargaweb_btn_guardar').click(function () {
			guardar_recargaweb();
			return false;
		});
		$('#modal_recargaweb2_btn_guardar').click(function () {
			guardar_recargaweb2();
			return false;
		});

        $("#sec_tlv_modal_recargaweb2_deposito_list").on({"change": function (event) {
                if (parseInt($('option:selected', event.target).val()) > 0) {
                    $("#sec_tlv_modal_recargaweb2_monto").val($('option:selected', event.target).attr('monto'));
                } else {
                    $("#sec_tlv_modal_recargaweb2_monto").val('0.00');
                    $("#sec_tlv_modal_recargaweb2_bono").val('0.00');
                }
                calcular_total_modal_recargaweb2();
            }
        });
	}
});
function nueva_recargaweb() {
	$('#cliente_idweb_div').css('border', '');
	limpiar_campos_modal_recargaweb();
	$('#modal_recargaweb_deposito_list_error').hide();
	$('#modal_recargaweb_deposito_list_error_texto').html('');

	var web_id = 0;
	$.each(array_clientes, function (index, item) {
		if (parseInt(item.id) === parseInt(gen_cliente_id)) {
			if (parseInt(item.web_id) > 0) {
				web_id = item.web_id;
				$('#modal_recargaweb_idweb').val(item.web_id);
			}
		}
	});
	if (!(parseInt(web_id) > 0)) {
		$('#cliente_idweb_div').css('border', '1px solid red');
		$('#cliente_idweb').focus();
		$('#modal_recargaweb').modal('hide');
		swal('Aviso', 'Debe guardar el ID-WEB en la sección de información del cliente.', 'warning');
		return false;
	}

	var temp_gen_balance_total = gen_balance_total.replace(/\D/g, "")
			.replace(/([0-9])([0-9]{2})$/, '$1.$2')
			.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
	$('#modal_recargaweb_balance').val(temp_gen_balance_total);
	//$('#modal_recargaweb_monto').val(temp_gen_balance_total);
	var temp_gen_balance_bono_disponible = gen_balance_bono_disponible.replace(/\D/g, "")
			.replace(/([0-9])([0-9]{2})$/, '$1.$2')
			.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
	$('#modal_recargaweb_balance_bono_disponible').val(temp_gen_balance_bono_disponible);
	//$('#modal_recargaweb_bono').val(temp_gen_balance_bono_disponible);


	$.post("/sys/set_televentas.php", {
		accion: "obtener_depositos_disponibles",
		id_cliente: gen_cliente_id
	})
			.done(function (data) {
				try {
					//console.log(data);
					var respuesta = JSON.parse(data);
					if (parseInt(respuesta.http_code) == 200) {
						if (respuesta.result.length > 0) {
							$('#modal_recargaweb_deposito_list').append('<option value="0">Seleccione</option>');
							$.each(respuesta.result, function (index, item) {
								if (parseFloat(item.bono_monto) > 0) {
									$('#modal_recargaweb_deposito_list').append('<option ' +
											'value="' + item.codigo + '"' +
											'monto="' + item.monto + '"' +
											'bono_monto="' + item.bono_monto + '"' +
											'>' +
											'S/. ' + item.monto + ' | ' + item.bono_nombre + ' - Máx. S/. ' + item.bono_monto +
											'</option>');
								} else {
									$('#modal_recargaweb_deposito_list').append('<option value="' + item.codigo + '">' +
											'S/. ' + item.monto + ' - Sin Bono' +
											'</option>');
									$('#modal_recargaweb_monto').val(temp_gen_balance_total);
								}
							});
						} else {
							$('#modal_recargaweb_deposito_list').append('<option value="0">Sin depósitos pendientes</option>');
							$('#modal_recargaweb_monto').val(temp_gen_balance_total);
						}
					} else {
						$('#modal_recargaweb_deposito_list').append('<option value="0">Sin depósitos pendientes</option>');
						$('#modal_recargaweb_monto').val(temp_gen_balance_total);
					}
					$("#modal_recargaweb_deposito_list").val('0');
				} catch (e) {
					swal('¡Error!', e, 'error');
				//	console.log("Error de TRY-CATCH --> Error: " + e);
				}
			})
			.fail(function (xhr, status, error) {
				swal('¡Error!', error, 'error');
			//	console.log("Error de .FAIL -- Error: " + error);
			});
	$("#modal_recargaweb_deposito_list").val('0');

	calcular_total_modal_recargaweb();

	$('#modal_recargaweb_btn_guardar').show();
	$('#modal_recargaweb').modal();
	return false;
}
function calcular_total_modal_recargaweb() {
	$('#modal_recargaweb_deposito_list_error').hide();
	$('#modal_recargaweb_deposito_list_error_texto').html('');
	var temp_monto = parseFloat($('#modal_recargaweb_monto').val().replace(/\,/g, '')).toFixed(2);
	var temp_bono_id = $("#modal_recargaweb_deposito_list").val();
	var temp_bono_maximo = $('option:selected', '#modal_recargaweb_deposito_list').attr('bono_monto');
	var temp_bono = 0;

	if (!(parseFloat(temp_monto) >= 0)) {
		temp_monto = 0;
	}
	if (parseFloat(temp_monto) >= 40 && parseInt(temp_bono_id) > 0) {
		temp_bono = (parseFloat(temp_monto) * 0.05).toFixed(2);
	}
	if (parseFloat(temp_bono) > parseFloat(temp_bono_maximo)) {
		temp_bono = parseFloat(temp_bono_maximo).toFixed(2);
	}
	//console.log('temp_bono_id: '+temp_bono_id);
	//console.log('temp_monto: '+temp_monto);
	if (parseFloat(temp_monto) < 40 && parseInt(temp_bono_id) > 0) {
		$('#modal_recargaweb_deposito_list_error').show();
		$('#modal_recargaweb_deposito_list_error_texto').html('El monto de la recarga debe ser mayor o igual a 40 soles para asignar un bono.');
	}

	var total = (parseFloat(temp_monto) + parseFloat(temp_bono)).toFixed(2);
	total = total.toString().replace(/\D/g, "")
			.replace(/([0-9])([0-9]{2})$/, '$1.$2')
			.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
	$('#modal_recargaweb_total').val(total);

	temp_bono = temp_bono.toString().replace(/\D/g, "")
			.replace(/([0-9])([0-9]{2})$/, '$1.$2')
			.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
	$('#modal_recargaweb_bono').val(temp_bono);
	return false;
}
function limpiar_campos_modal_recargaweb() {
	$('#modal_recargaweb_idweb').val('');
	$('#modal_recargaweb_balance').val('');
	$('#modal_recargaweb_balance_bono_disponible').val('');
	$('#modal_recargaweb_monto').val('0.00');
	$('#modal_recargaweb_bono').val('0.00');
	$('#modal_recargaweb_total').val('0.00');
	$('#modal_recargaweb_deposito_list').html('');
	$('#modal_recargaweb_deposito_list_error').hide();
	$('#modal_recargaweb_deposito_list_error_texto').html('');
	limpiar_bordes_modal_recargaweb();
}
function limpiar_bordes_modal_recargaweb() {
	$('#modal_recargaweb_idweb').css('border', '');
	$('#modal_recargaweb_monto').css('border', '');
}
function guardar_recargaweb() {
	calcular_total_modal_recargaweb();

	$('#modal_recargaweb_btn_guardar').hide();
	limpiar_bordes_modal_recargaweb();

	var idweb = $('#modal_recargaweb_idweb').val();
	var monto = $('#modal_recargaweb_monto').val().replace(/\,/g, '');
	var bono = $('#modal_recargaweb_bono').val().replace(/\,/g, '');
	var total = $('#modal_recargaweb_total').val().replace(/\,/g, '');
	var id_deposito = $('option:selected', '#modal_recargaweb_deposito_list').val();

	if (!(parseInt(idweb) > 0)) {
		$('#modal_recargaweb_idweb').css('border', '1px solid red');
		$('#modal_recargaweb_idweb').focus();
		$('#modal_recargaweb_btn_guardar').show();
		return false;
	}
	if (!(parseFloat(monto) > 0)) {
		$('#modal_recargaweb_monto').css('border', '1px solid red');
		$('#modal_recargaweb_monto').focus();
		$('#modal_recargaweb_btn_guardar').show();
		return false;
	}
	if (!(parseFloat(bono) >= 0)) {
		bono = 0;
	}
	if (parseFloat(bono) > parseFloat(monto)) {
		swal('Aviso', 'El bono no puede ser mayor que el balance.', 'warning');
		$('#modal_recargaweb_btn_guardar').show();
		return false;
	}
	if (parseFloat(gen_balance_total) < parseFloat(monto)) {
		swal('Aviso', 'El balance actual es menor al monto a recargar.', 'warning');
		$('#modal_recargaweb_btn_guardar').show();
		return false;
	}
	if (parseFloat(gen_balance_bono_disponible) < parseFloat(bono)) {
		swal('Aviso', 'El balance del bono disponible actual es menor al bono a recargar.', 'warning');
		$('#modal_recargaweb_btn_guardar').show();
		return false;
	}

	var data = {
		"accion": "guardar_transaccion_recarga_web",
		"id_cliente": gen_cliente_id,
		"idweb": idweb,
		"monto": monto,
		"bono": bono,
		"id_deposito": id_deposito
	}

	if (parseFloat(gen_balance_total) > parseFloat(monto)) {
		swal({
			html: true,
			title: '¿Está seguro de realizar la recarga con un <span style="font-weight: 900;color: black;text-transform: uppercase;">MONTO MENOR AL BALANCE ACTUAL</span>?',
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#0336FF',
			cancelButtonColor: '#d33',
			confirmButtonText: 'SI, CONFIRMAR',
			cancelButtonText: 'CANCELAR',
			closeOnConfirm: false,
			//,showLoaderOnConfirm: true
		}, function () {
			guardar_recarga_confirmar(data);
			return false;
		});
		$('#modal_recargaweb_btn_guardar').show();
	} else {
		guardar_recarga_confirmar(data);
		return false;
	}
	return false;
}
function guardar_recarga_confirmar(data) {
	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) { //  alert(datat)
			//console.log(resp);
			var respuesta = JSON.parse(resp);
			auditoria_send({"proceso": "guardar_transaccion_recarga_web", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_recargaweb').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				//listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_recargaweb').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "guardar_transaccion_recarga_web_error", "data": result});
			return false;
		}
	});
	return false;
}











//************************************************************************************************
//************************************************************************************************
// RETIRO
//************************************************************************************************
//************************************************************************************************
$(function () {
	if (sec_id === 'televentas' || sec_id === 'retail_altenar') {
		//Monto
		$("#modal_retiro_monto").on({
			"focus": function (event) {
				$(event.target).select();
				//console.log('focus');
			},
			"blur": function (event) {
				//console.log('blur');
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
			}
		});

		//Monto Caja 7
		$("#SecRetC7_monto").on({
			"focus": function (event) {
				$(event.target).select();
				//console.log('focus');
			},
			"blur": function (event) {
				//console.log('blur');
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
			}
		});

		//Button guardar
		$('#modal_retiro_btn_guardar').click(function () {
			guardar_retiro();
			return false;
		});
		$('#modal_cuentas_btn_guardar').click(function () {
			guardar_cuenta_x_cliente();
			return false;
		});
		$('#modal_retiro_btn_listar_cuenta').click(function () {
			$('.modal_cuentas_listar').show();
			$('.modal_cuentas_registrar').hide();
			$('#modal_cuentas').modal();
			return false;
		});
		$('#modal_retiro_btn_agregar_cuenta').click(function () {
			$('.modal_cuentas_listar').hide();
			$('.modal_cuentas_registrar').show();
			$('#modal_cuentas').modal();
			$('#modal_cuentas_banco').val(0).trigger('change');
			$('#modal_cuentas_cuenta_num').val('');
			$('#modal_cuentas_cci').val('');
			return false;
		});
	}
});
function nuevo_modal_retiro(tipo) {
	$('#sec_tlv_modal_retiro_tipo_transaccion').val(tipo);

	$('#cliente_idweb_div').css('border', '');
	limpiar_campos_modal_retiro();
	modal_retiro_obtener_cuentas();
	$('#sec_tlv_select_operacion').val(1).trigger('change.select2');
	$('#sec_tlv_select_motivo_devolucion').val(0).trigger('change.select2');
	var retiro_disponible_cliente = $('#span_balance_retiro_disponible').html();
	if(tipo == 1){
		$('#sec_modal_retiro_tittle').html('Solicitud de Retiro');
		$('#sec_tlv_retiro_disponible').html(retiro_disponible_cliente);
		$('#sec_tlv_modal_retiro_div_motivo_devolucion').hide();

	}else if(tipo == 2){
		$('#sec_modal_retiro_tittle').html('Solicitud de Devolución');
		$('#sec_tlv_retiro_disponible').html(gen_balance_no_retirable_disponible);
		$('#sec_tlv_modal_retiro_div_motivo_devolucion').show();
	}

	$('#sec_tlv_input_link_atencion').val('');

	$('#modal_retiro_btn_guardar').show();
	$('#modal_retiro').modal();
	return false;
}
function limpiar_campos_modal_retiro() {
	$('#modal_retiro_cuenta').empty();
	$('#modal_cuentas_tabla tbody').html('');
	$('#modal_retiro_monto').val('0.00');
	$('.modal_cuentas_listar').hide();
	$('.modal_cuentas_registrar').hide();
	limpiar_bordes_modal_retiro();
}
function limpiar_bordes_modal_retiro() {
	$('#modal_retiro_cuenta_div').css('border', '');
	$('#modal_retiro_monto').css('border', '');
	$('#modal_cuentas_banco').css('border', '');
	$('#modal_cuentas_cuenta_num').css('border', '');
	$('#modal_cuentas_cci').css('border', '');
}
function modal_retiro_obtener_cuentas() {
	var data = {
		accion: "obtener_cuentas_x_cliente",
		cliente_id: gen_cliente_id
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "obtener_cuentas_x_cliente", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_retiro_cuenta').append('<option value="0">:: Seleccione ::</option>');
				$('#modal_retiro_cuenta').val('0');

				$.each(respuesta.result, function (index, item) {
					$('#modal_cuentas_tabla tbody').append(
							'<tr class="listado_cuentas_bancarias_clientes" for="' + item.cod + '_' + item.cuenta_num_cliente + '">' +
							'   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_id_cuenta_cliente" value="' + item.cod + '" /></td>' +
							'   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_id_banco_cuenta_cliente" value="' + item.banco_id + '" /></td>' +
							'   <td style="font-weight: bold; text-align: center;" >' +
							'       <label for="' + item.cod + '_' + item.cuenta_num_cliente + '" class="sec_tlv_nom_cuenta_cliente">' + item.banco + '</label>' +
							'   </td>' +
							'   <td style="font-weight: bold; text-align: center;">' +
							'       <label for="' + item.cod + '_' + item.cuenta_num_cliente + '" class="sec_tlv_num_cuenta_cliente">' + item.cuenta_num + '</label>' +
							'   </td>' +
							'   <td style="font-weight: bold; text-align: center;">' +
							'       <label for="' + item.cod + '_' + item.cuenta_num_cliente + '" class="sec_tlv_num_cci_cuenta_cliente">' + item.cci + '</label>' +
							'   </td>' +
							'   <td style="text-align: center;"><input type="radio" name="sec_tlv_pag_sec_cuentas_cliente" id="' + item.cod + '_' + item.cuenta_num_cliente + '" class="sec_tlv_pag_sec_cuentas_cliente"/></td>' +
							'</tr>'
							);
				});
			} else {
				$('#modal_retiro_cuenta').append('<option value="0">:: No existen cuentas ::</option>');
				$('#modal_cuentas_tabla tbody').append(
						'<tr>' +
						'   <td colspan="2">No hay cuentas registradas</td>' +
						'</tr>'
						);
				$('#modal_retiro_cuenta').val('0');
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_cuentas_x_cliente_error", "data": result});
			return false;
		}
	});
}
function guardar_cuenta_x_cliente() {

	$('#modal_cuentas_btn_guardar').hide();
	limpiar_bordes_modal_retiro();

	var banco = $('#modal_cuentas_banco').val();
	var cuenta_num = $('#modal_cuentas_cuenta_num').val();
	var cci = $('#modal_cuentas_cci').val();

	if (!(parseInt(banco) > 0)) {
		$('#modal_cuentas_banco').css('border', '1px solid red');
		$('#modal_cuentas_banco').focus();
		$('#modal_cuentas_btn_guardar').show();
		return false;
	}

	if (parseInt(banco) != 53) { //bn
		if ($.trim(cuenta_num) == "" && $.trim(cci) == "") {
			swal('Aviso', "Debe Ingresar el Número de Cuenta o CCI", 'warning');
			$('#modal_cuentas_btn_guardar').show();
			return false;
		}
	} else {
		if (!(cuenta_num.length > 0)) {
			$('#modal_cuentas_cuenta_num').css('border', '1px solid red');
			$('#modal_cuentas_cuenta_num').focus();
			$('#modal_cuentas_btn_guardar').show();
			return false;
		}
		if (!(cci.length > 0)) {
			$('#modal_cuentas_cci').css('border', '1px solid red');
			$('#modal_cuentas_cci').focus();
			$('#modal_cuentas_btn_guardar').show();
			return false;
		}
	}



	var data = {
		"accion": "guardar_cuenta_x_cliente",
		"id_cliente": gen_cliente_id,
		"id_banco": banco,
		"cuenta_num": cuenta_num,
		"cci": cci
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "guardar_cuenta_x_cliente", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_cuentas').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				limpiar_campos_modal_retiro();
				modal_retiro_obtener_cuentas();
				SecRetC7_obtener_cuentas();
				SecDevC7_obtener_cuentas();
				$('#modal_cuentas').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "guardar_cuenta_x_cliente_error", "data": result});
			return false;
		}
	});
	return false;
}









//************************************************************************************************
//************************************************************************************************
// REGISTRAR APUESTA
//************************************************************************************************
//************************************************************************************************
var modal_apuesta_registrar_modal = '';
$(function () {

	$('#modal_apuesta_registrar_btn_agregar_detalle').click(function () {
		modal_apuesta_registrar_btn_agregar_detalle();
		return false;
	});
	$('#modal_apuesta_registrar_form_agregar_detalle').on('submit', function () {
		modal_apuesta_registrar_btn_agregar_detalle();
		return false;
	});

	//Button guardar
	$('#modal_apuesta_registrar_btn_guardar').click(function () {
		modal_apuesta_registrar_btn_guardar();
		return false;
	});
});

function nuevo_modal_apuesta_registrar(modal) {

	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	var temp_balance_monto = 0;
	var temp_balance_texto = '';

	if(parseInt(temp_balance_tipo)===1){
		temp_balance_monto = gen_balance_total;
		temp_balance_texto = 'Balance Actual';
	}else if(parseInt(temp_balance_tipo)===6){
		temp_balance_monto = gen_balance_dinero_at;
		temp_balance_texto = 'Bono AT';
	} else {
		swal('Aviso', 'Por favor, seleccione una opción válida, Saldo Real ó Saldo Promocional', 'warning');
		return false;
	}

	modal_apuesta_registrar_modal = modal;
	var texto_titulo = 'Apuesta';
	$('#modal_apuesta_registrar_proveedor_div').show();
	if(modal_apuesta_registrar_modal==='apuesta_gr'){
		texto_titulo = 'Venta Virtuales';
		$('#modal_apuesta_registrar_proveedor_div').hide();
	}
	if(modal_apuesta_registrar_modal==='apuesta_bingo'){
		texto_titulo = 'Venta Bingo';
		$('#modal_apuesta_registrar_proveedor_div').hide();
	}
	$('#modal_apuesta_registrar').modal();
	$('#modal_apuesta_registrar_titulo').html('<b>'+texto_titulo+' - '+temp_balance_texto+': S/ ' + parseFloat(temp_balance_monto).toFixed(2) + '</b>');
	$('#modal_apuesta_registrar_idbet').css('border', '');
	$('#modal_apuesta_registrar_idbet').val('');
	$('#modal_apuesta_registrar_tbl').html(
			'<thead>' +
			'<tr>' +
			'<td class="text-center" width="5%">#</td>' +
			'<td class="text-center" width="10%">Proveedor</td>' +
			'<td class="text-center" width="20%">Ticket</td>' +
			'<td class="text-center" width="20%">Monto</td>' +
			'<td class="text-center" width="40%">Estado</td>' +
			'<td class="text-center" width="5%">X</td>' +
			'</tr>' +
			'</thead>' +
			'<tbody>'
			);
	$('#modal_apuesta_registrar_total').val('0.00');
	$('#modal_apuesta_registrar_btn_guardar').show();
	setTimeout(function () {
		$('#modal_apuesta_registrar_idbet').focus()
	}, 500);
	return false;
}
function modal_apuesta_registrar_tbl_eliminar_tr(idbet) {
	$('#modal_apuesta_registrar_tbl tbody tr[id="' + idbet + '"]').remove();
	var modal_apuesta_registrar_tbl_cant = 0;
	$('#modal_apuesta_registrar_tbl tbody tr').each(function () {
		modal_apuesta_registrar_tbl_cant++;
		$(this).find("td").eq(0).html(modal_apuesta_registrar_tbl_cant);
	});
	modal_apuesta_registrar_tbl_calcular_total();
}
function modal_apuesta_registrar_tbl_calcular_total() {
	var modal_apuesta_registrar_tbl_total = 0;
	$('#modal_apuesta_registrar_tbl tbody tr').each(function () {
		modal_apuesta_registrar_tbl_total += parseFloat($(this).find("td").eq(3).html());
	});
	$('#modal_apuesta_registrar_total').val(parseFloat(modal_apuesta_registrar_tbl_total).toFixed(2));
}
function modal_apuesta_registrar_btn_agregar_detalle() {
	$('#modal_apuesta_registrar_idbet').css('border', '');

	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	var	evento_dineroat_id = $("#evento_dineroat_id").val();

	var modal_apuesta_registrar_proveedor = $(':radio[name=modal_apuesta_registrar_proveedor]:checked').val();

	if(modal_apuesta_registrar_modal==='apuesta_gr'){
		modal_apuesta_registrar_proveedor = 3;
	}
	if(modal_apuesta_registrar_modal==='apuesta_bingo'){
		modal_apuesta_registrar_proveedor = 4;
	}

	if (!( parseInt(modal_apuesta_registrar_proveedor) === 1 || 
		parseInt(modal_apuesta_registrar_proveedor) === 3 || 
		parseInt(modal_apuesta_registrar_proveedor) === 4 || 
		parseInt(modal_apuesta_registrar_proveedor) === 5 )
		) {
		swal('Aviso', 'Debe seleccionar un proveedor.', 'warning');
		$('#modal_apuesta_pagar_idbet').focus();
		return false;
	}
	
	var modal_apuesta_registrar_total = $('#modal_apuesta_registrar_total').val();
	if(parseInt(temp_balance_tipo)===1) {
		if (parseFloat(gen_balance_total) < parseFloat(modal_apuesta_registrar_total)) {
			swal('Aviso', 'El balance actual es menor al monto de la apuesta total.', 'warning');
			return false;
		}
	}
	if(parseInt(temp_balance_tipo)===6) {
		if (parseFloat(gen_balance_dinero_at) < parseFloat(modal_apuesta_registrar_total)) {
			swal('Aviso', 'El balance promocional es menor al monto de la apuesta total.', 'warning');
			return false;
		}
	}


	var idbet = $('#modal_apuesta_registrar_idbet').val();
	if(parseInt(modal_apuesta_registrar_proveedor) !== 4){
		if (!(parseInt(idbet) > 0)) {
			$('#modal_apuesta_registrar_idbet').css('border', '1px solid red');
			$('#modal_apuesta_registrar_idbet').focus();
			return false;
		}
		if (idbet.length === 20) {
			idbet = idbet.substr(3, 10);
		}
	}

	var modal_apuesta_registrar_tbl_validacion = 0;
	var modal_apuesta_registrar_tbl_cant = 1;
	$('#modal_apuesta_registrar_tbl tbody tr').each(function () {
		modal_apuesta_registrar_tbl_cant++;
		var ticket_temp = $(this).find("td").eq(2).html();
		if(parseInt(modal_apuesta_registrar_proveedor) === 4){
			if (ticket_temp === idbet) {
				modal_apuesta_registrar_tbl_validacion++;
			}
		} else {
			if (parseInt(ticket_temp) === parseInt(idbet)) {
				modal_apuesta_registrar_tbl_validacion++;
			}
		}
	});
	if (parseInt(modal_apuesta_registrar_tbl_validacion) > 0) {
		swal('Aviso', 'El ticket ya se encuentra en la lista de tickets agregados.', 'warning');
		$('#modal_apuesta_registrar_idbet').css('border', '1px solid red');
		$('#modal_apuesta_registrar_idbet').focus();
		return false;
	}

	$('#modal_apuesta_registrar_idbet').val('');
	var idbet_text = "'"+idbet+"'";
	$('#modal_apuesta_registrar_tbl').append(
			'<tr id="' + idbet + '">' +
			'<td class="text-center" width="5%">' + modal_apuesta_registrar_tbl_cant + '</td>' +
			'<td class="text-center" width="10%" id="modal_apuesta_registrar_tbl_tr_proveedor_' + idbet + '"></td>' +
			'<td class="text-center" width="20%">' + idbet + '</td>' +
			'<td class="text-right" width="20%" id="modal_apuesta_registrar_tbl_tr_monto_' + idbet + '">0.00</td>' +
			'<td class="text-center" width="40%" id="modal_apuesta_registrar_tbl_tr_estado_' + idbet + '">Consultando...</td>' +
			'<td class="text-center" width="5%">' +
			'<button type="button" class="btn btn-danger" style="padding: 2px 5px;"' +
			'    onclick="modal_apuesta_registrar_tbl_eliminar_tr(' + idbet_text + ')">' +
			'<span class="fa fa-trash"></span>' +
			'</button>' +
			'</td>' +
			'</tr>'
			);

	var data = new FormData();
	data.append('accion', "registrar_apuesta_detalle");
	data.append('proveedor', modal_apuesta_registrar_proveedor);
	data.append('id_cliente', gen_cliente_id);
	data.append('balance_tipo', temp_balance_tipo);
	data.append('id_bet', idbet);
	data.append('evento_dineroat_id', evento_dineroat_id);

	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,
		processData: false,
		cache: false,
		contentType: false,
		beforeSend: function () {
		},
		complete: function () {
		},
		success: function (resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) === 200) {
				$('#modal_apuesta_registrar_tbl_tr_proveedor_' + (respuesta.id_bet.toString())).html(respuesta.proveedor_name);
				$('#modal_apuesta_registrar_tbl_tr_monto_' + (respuesta.id_bet.toString())).html(parseFloat(respuesta.amount).toFixed(2));
				$('#modal_apuesta_registrar_tbl_tr_estado_' + (respuesta.id_bet.toString())).html(respuesta.status);
				modal_apuesta_registrar_tbl_calcular_total();
				return false;
			} else if (parseInt(respuesta.http_code) === 400) {
				$('#modal_apuesta_registrar_tbl_tr_proveedor_' + (respuesta.id_bet.toString())).html(respuesta.proveedor_name);
				$('#modal_apuesta_registrar_tbl_tr_monto_' + (respuesta.id_bet.toString())).html(parseFloat(respuesta.amount).toFixed(2));
				$('#modal_apuesta_registrar_tbl_tr_estado_' + (respuesta.id_bet.toString())).html('<span style="color:'+respuesta.color_status+'">'+respuesta.status+'</span>');
				modal_apuesta_registrar_tbl_calcular_total();
				return false;
			} else {
				return false;
			}
			return false;
		},
		error: function (result) {
			return false;
		}
	});
	return false;
}
function modal_apuesta_registrar_btn_guardar() {
	$('#modal_apuesta_registrar_btn_guardar').hide();
	$('#modal_apuesta_registrar_idbet').css('border', '');

	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	var modal_apuesta_registrar_total = $('#modal_apuesta_registrar_total').val();
	/*if(parseFloat(gen_balance_total) < parseFloat(modal_apuesta_registrar_total)){
	 swal('Aviso', 'El balance actual es menor al monto de la apuesta total.', 'warning');
	 $('#modal_apuesta_registrar_btn_guardar').show();
	 return false;
	 }*/

	var modal_apuesta_registrar_tbl_array = '';
	var modal_apuesta_registrar_tbl_array_montos = '';
	var modal_apuesta_registrar_tbl_cant = 0;
	var modal_apuesta_registrar_tbl_validacion_estado = 0;
	$('#modal_apuesta_registrar_tbl tbody tr').each(function () {
		modal_apuesta_registrar_tbl_cant++;
		var ticket_temp = $.trim($(this).find("td").eq(2).html());
		var monto_temp = $.trim($(this).find("td").eq(3).html());
		var estado_temp = $.trim($(this).find("td").eq(4).html()).toUpperCase();
		//console.log('ticket_temp: '+ticket_temp);
		//console.log('estado_temp: '+estado_temp);
		if (modal_apuesta_registrar_tbl_array.length > 0) {
			modal_apuesta_registrar_tbl_array += ",";
			modal_apuesta_registrar_tbl_array_montos += ",";
		}
		modal_apuesta_registrar_tbl_array += "'" + ticket_temp.toString() + "'";
		modal_apuesta_registrar_tbl_array_montos += monto_temp.toString();
		if (estado_temp !== 'OK') {
			//console.log('DIF. DE OK: '+estado_temp);
			modal_apuesta_registrar_tbl_validacion_estado++;
		}
	});

	if (!(parseInt(modal_apuesta_registrar_tbl_cant) > 0)) {
		swal('Aviso', 'Debe agregar al menos un ticket.', 'warning');
		$('#modal_apuesta_registrar_idbet').css('border', '1px solid red');
		$('#modal_apuesta_registrar_idbet').focus();
		$('#modal_apuesta_registrar_btn_guardar').show();
		return false;
	}
	if (parseInt(modal_apuesta_registrar_tbl_validacion_estado) > 0) {
		swal('Aviso', 'Debe eliminar los tickets con estado diferente a "OK" para continuar.', 'warning');
		$('#modal_apuesta_registrar_btn_guardar').show();
		return false;
	}

	var data = new FormData();
	data.append('accion', "registrar_apuesta");
	data.append('id_cliente', gen_cliente_id);
	data.append('balance_tipo', temp_balance_tipo);
	data.append('cant_bet', modal_apuesta_registrar_tbl_cant);
	data.append('array_bet', modal_apuesta_registrar_tbl_array);
	data.append('array_monto', modal_apuesta_registrar_tbl_array_montos);
	data.append('total_bet', modal_apuesta_registrar_total);

	/*
	 console.log('cant_bet: ' + modal_apuesta_registrar_tbl_cant);
	 console.log('array_bet: ' + modal_apuesta_registrar_tbl_array);
	 console.log('array_bet: ' + modal_apuesta_registrar_tbl_array_montos);
	 return false;
	 */
	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "registrar_apuesta", "data": respuesta});
			//console.log(respuesta);
			$('#modal_apuesta_registrar').modal('hide');
			if (parseInt(respuesta.http_code) === 200) {
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			} else if (parseInt(respuesta.http_code) === 400) {
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			} else {
				swal('Aviso', 'Sin respuesta.', 'warning');
				return false;
			}
			return false;
		},
		error: function (result) {
			$('#modal_apuesta_registrar_btn_guardar').show();
			swal('Aviso', result, 'warning');
			auditoria_send({"proceso": "registrar_apuesta_error", "data": result});
			return false;
		}
	});
	return false;
}

















//************************************************************************************************
//************************************************************************************************
// PAGAR APUESTA
//************************************************************************************************
//************************************************************************************************
var modal_apuesta_pagar_modal = '';
$(function () {

	$('#modal_apuesta_pagar_btn_agregar_detalle').click(function () {
		modal_apuesta_pagar_btn_agregar_detalle();
		return false;
	});
	$('#modal_apuesta_pagar_form_agregar_detalle').on('submit', function () {
		modal_apuesta_pagar_btn_agregar_detalle();
		return false;
	});

	//Button guardar
	$('#modal_apuesta_pagar_btn_guardar').click(function () {
		modal_apuesta_pagar_btn_guardar();
		return false;
	});

});

var modal_apuesta_total_monto = 0;
var modal_apuesta_total_jackpot = 0;

function nuevo_modal_apuesta_pagar(modal) {
	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	var temp_balance_monto = 0;
	var temp_balance_texto = '';

	if(parseInt(temp_balance_tipo)===1){
		temp_balance_monto = gen_balance_total;
		temp_balance_texto = 'Balance Actual';
	}else if(parseInt(temp_balance_tipo)===6){
		temp_balance_monto = gen_balance_dinero_at;
		temp_balance_texto = 'Bono AT';
	} else {
		swal('Aviso', 'Por favor, seleccione una opción válida, Saldo Real ó Saldo Promocional', 'warning');
		return false;
	}

	modal_apuesta_pagar_modal = modal;
	var texto_titulo = 'Pagar Apuesta';
	$('#modal_apuesta_pagar_proveedor_div').show();
	if(modal_apuesta_pagar_modal==='pagar_apuesta_gr'){
		texto_titulo = 'Pagar Virtuales';
		$('#modal_apuesta_pagar_proveedor_div').hide();
	}
	if(modal_apuesta_pagar_modal==='pagar_apuesta_bingo'){
		texto_titulo = 'Pagar Bingo';
		$('#modal_apuesta_pagar_proveedor_div').hide();
	}
	$('#modal_apuesta_pagar').modal();
	$('#modal_apuesta_pagar_titulo').html('<b>'+texto_titulo+' - '+temp_balance_texto+': S/ ' + parseFloat(temp_balance_monto).toFixed(2) + '</b>');
	$('input:radio[name=modal_apuesta_pagar_proveedor]').filter('[value=2]').prop('checked', true);
	$('#modal_apuesta_pagar_idbet').css('border', '');
	$('#modal_apuesta_pagar_idbet').val('');
	$('#modal_apuesta_pagar_tbl').html(
			'<thead>' +
			'<tr>' +
			'<td class="text-center" width="5%">#</td>' +
			'<td class="text-center" width="10%">Proveedor</td>' +
			'<td class="text-center" width="15%">Ticket</td>' +
			'<td class="text-center" width="15%">Monto</td>' +
			'<td class="text-center" width="15%">Jackpot</td>' +
			'<td class="text-center" width="35%">Estado</td>' +
			'<td class="text-center" width="5%">X</td>' +
			'</tr>' +
			'</thead>' +
			'<tbody>'
			);
	$('#modal_apuesta_pagar_total').val('0.00');
	modal_apuesta_total_monto = 0;
	modal_apuesta_total_jackpot = 0;
	$('#modal_apuesta_pagar_btn_guardar').show();
	setTimeout(function () {
		$('#modal_apuesta_pagar_idbet').focus()
	}, 500);
	return false;
}
function modal_apuesta_pagar_tbl_eliminar_tr(idbet) {
	$('#modal_apuesta_pagar_tbl tbody tr[id="' + idbet + '"]').remove();
	var modal_apuesta_pagar_tbl_cant = 0;
	$('#modal_apuesta_pagar_tbl tbody tr').each(function () {
		modal_apuesta_pagar_tbl_cant++;
		$(this).find("td").eq(0).html(modal_apuesta_pagar_tbl_cant);
	});
	modal_apuesta_pagar_tbl_calcular_total();
}
function modal_apuesta_pagar_tbl_calcular_total() {
	var modal_apuesta_pagar_tbl_total_monto = 0;
	var modal_apuesta_pagar_tbl_total_jackpot = 0;
	$('#modal_apuesta_pagar_tbl tbody tr').each(function () {
		modal_apuesta_pagar_tbl_total_monto = (parseFloat(modal_apuesta_pagar_tbl_total_monto) + parseFloat($(this).find("td").eq(3).html())).toFixed(2);
		modal_apuesta_pagar_tbl_total_jackpot = (parseFloat(modal_apuesta_pagar_tbl_total_jackpot) + parseFloat($(this).find("td").eq(4).html())).toFixed(2);
	});
	modal_apuesta_total_monto = parseFloat(modal_apuesta_pagar_tbl_total_monto).toFixed(2);
	modal_apuesta_total_jackpot = parseFloat(modal_apuesta_pagar_tbl_total_jackpot).toFixed(2);
	$('#modal_apuesta_pagar_total').val((parseFloat(modal_apuesta_total_monto)+parseFloat(modal_apuesta_total_jackpot)).toFixed(2));
}
function modal_apuesta_pagar_btn_agregar_detalle() {
	$('#modal_apuesta_pagar_idbet').css('border', '');

	var modal_apuesta_pagar_total = $('#modal_apuesta_pagar_total').val();
	var modal_apuesta_pagar_proveedor = $(':radio[name=modal_apuesta_pagar_proveedor]:checked').val();
	
	if(modal_apuesta_pagar_modal==='pagar_apuesta_gr'){
		modal_apuesta_pagar_proveedor = 3;
	}
	if(modal_apuesta_pagar_modal==='pagar_apuesta_bingo'){
		modal_apuesta_pagar_proveedor = 4;
	}

	if (!( parseInt(modal_apuesta_pagar_proveedor) === 1 || 
		parseInt(modal_apuesta_pagar_proveedor) === 3 || 
		parseInt(modal_apuesta_pagar_proveedor) === 4 || 
		parseInt(modal_apuesta_pagar_proveedor) === 5 )
		) {
		swal('Aviso', 'Debe seleccionar un proveedor.', 'warning');
		$('#modal_apuesta_pagar_idbet').focus();
		return false;
	}

	var idbet = $('#modal_apuesta_pagar_idbet').val();
	if(parseInt(modal_apuesta_pagar_proveedor) !== 4) {
		if (!(parseInt(idbet) > 0)) {
			$('#modal_apuesta_pagar_idbet').css('border', '1px solid red');
			$('#modal_apuesta_pagar_idbet').focus();
			return false;
		}
		if (idbet.length === 20) {
			idbet = idbet.substr(3, 10);
		}
	}

	var modal_apuesta_pagar_tbl_validacion = 0;
	var modal_apuesta_pagar_tbl_cant = 1;
	$('#modal_apuesta_pagar_tbl tbody tr').each(function () {
		modal_apuesta_pagar_tbl_cant++;
		var ticket_temp = $(this).find("td").eq(2).html();
		if(parseInt(modal_apuesta_pagar_proveedor) === 4) {
			if (ticket_temp === idbet) {
				modal_apuesta_pagar_tbl_validacion++;
			}
		} else {
			if (parseInt(ticket_temp) === parseInt(idbet)) {
				modal_apuesta_pagar_tbl_validacion++;
			}
		}
	});
	if (parseInt(modal_apuesta_pagar_tbl_validacion) > 0) {
		swal('Aviso', 'El ticket ya se encuentra en la lista de tickets agregados.', 'warning');
		$('#modal_apuesta_pagar_idbet').css('border', '1px solid red');
		$('#modal_apuesta_pagar_idbet').focus();
		return false;
	}

	$('#modal_apuesta_pagar_idbet').val('');
	var idbet_text = "'"+idbet+"'";
	$('#modal_apuesta_pagar_tbl').append(
			'<tr id="' + idbet + '">' +
			'<td class="text-center" width="5%">' + modal_apuesta_pagar_tbl_cant + '</td>' +
			'<td class="text-center" width="10%" id="modal_apuesta_pagar_tbl_tr_proveedor_' + idbet + '"></td>' +
			'<td class="text-center" width="15%">' + idbet + '</td>' +
			'<td class="text-right" width="15%" id="modal_apuesta_pagar_tbl_tr_monto_' + idbet + '">0.00</td>' +
			'<td class="text-right" width="15%" id="modal_apuesta_pagar_tbl_tr_jackpot_' + idbet + '">0.00</td>' +
			'<td class="text-center" width="35%" id="modal_apuesta_pagar_tbl_tr_estado_' + idbet + '">Consultando...</td>' +
			'<td class="text-center" width="5%">' +
			'<button type="button" class="btn btn-danger" style="padding: 2px 5px;"' +
			'    onclick="modal_apuesta_pagar_tbl_eliminar_tr(' + idbet_text + ')">' +
			'<span class="fa fa-trash"></span>' +
			'</button>' +
			'</td>' +
			'</tr>'
			);
	
	var	evento_dineroat_id = $("#evento_dineroat_id").val();

	var data = new FormData();
	data.append('accion', "pagar_apuesta_detalle");
	data.append('proveedor', modal_apuesta_pagar_proveedor);
	data.append('id_cliente', gen_cliente_id);
	data.append('id_bet', idbet);
	data.append('evento_dineroat_id', evento_dineroat_id);

	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,
		processData: false,
		cache: false,
		contentType: false,
		beforeSend: function () {
		},
		complete: function () {
		},
		success: function (resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) === 200) {
				$('#modal_apuesta_pagar_tbl_tr_proveedor_' + (respuesta.id_bet.toString())).html(respuesta.proveedor_name);
				$('#modal_apuesta_pagar_tbl_tr_monto_' + (respuesta.id_bet.toString())).html(parseFloat(respuesta.amount).toFixed(2));
				$('#modal_apuesta_pagar_tbl_tr_jackpot_' + (respuesta.id_bet.toString())).html(parseFloat(respuesta.jackpot).toFixed(2));
				$('#modal_apuesta_pagar_tbl_tr_estado_' + (respuesta.id_bet.toString())).html(respuesta.status);
				modal_apuesta_pagar_tbl_calcular_total();
				return false;
			} else if (parseInt(respuesta.http_code) === 400) {
				$('#modal_apuesta_pagar_tbl_tr_proveedor_' + (respuesta.id_bet.toString())).html(respuesta.proveedor_name);
				$('#modal_apuesta_pagar_tbl_tr_monto_' + (respuesta.id_bet.toString())).html(parseFloat(respuesta.amount).toFixed(2));
				$('#modal_apuesta_pagar_tbl_tr_jackpot_' + (respuesta.id_bet.toString())).html(parseFloat(respuesta.jackpot).toFixed(2));
				$('#modal_apuesta_pagar_tbl_tr_estado_' + (respuesta.id_bet.toString())).html(respuesta.status);
				modal_apuesta_pagar_tbl_calcular_total();
				return false;
			} else {
				return false;
			}
			return false;
		},
		error: function (result) {
			return false;
		}
	});
	return false;
}
function modal_apuesta_pagar_btn_guardar() {
	$('#modal_apuesta_pagar_btn_guardar').hide();
	$('#modal_apuesta_pagar_idbet').css('border', '');

	var modal_apuesta_pagar_tbl_array = '';
	var modal_apuesta_pagar_tbl_cant = 0;
	var modal_apuesta_pagar_tbl_validacion_estado = 0;
	$('#modal_apuesta_pagar_tbl tbody tr').each(function () {
		modal_apuesta_pagar_tbl_cant++;
		var ticket_temp = $.trim($(this).find("td").eq(2).html());
		var monto_temp = $.trim($(this).find("td").eq(3).html());
		var estado_temp = $.trim($(this).find("td").eq(5).html()).toUpperCase();
		//console.log('ticket_temp: '+ticket_temp);
		//console.log('estado_temp: '+estado_temp);
		if (modal_apuesta_pagar_tbl_array.length > 0) {
			modal_apuesta_pagar_tbl_array += ",";
		}
		modal_apuesta_pagar_tbl_array += "'" + ticket_temp.toString() + "'";
		if (estado_temp !== 'OK') {
			//console.log('DIF. DE OK: '+estado_temp);
			modal_apuesta_pagar_tbl_validacion_estado++;
		}
	});

	if (!(parseInt(modal_apuesta_pagar_tbl_cant) > 0)) {
		swal('Aviso', 'Debe agregar al menos un ticket.', 'warning');
		$('#modal_apuesta_pagar_idbet').css('border', '1px solid red');
		$('#modal_apuesta_pagar_idbet').focus();
		$('#modal_apuesta_pagar_btn_guardar').show();
		return false;
	}
	if (parseInt(modal_apuesta_pagar_tbl_validacion_estado) > 0) {
		swal('Aviso', 'Debe eliminar los tickets con estado diferente a "OK" para continuar.', 'warning');
		$('#modal_apuesta_pagar_btn_guardar').show();
		return false;
	}

	var	evento_dineroat_id = $("#evento_dineroat_id").val();

	var data = new FormData();
	data.append('accion', "pagar_apuesta");
	data.append('id_cliente', gen_cliente_id);
	data.append('cant_bet', modal_apuesta_pagar_tbl_cant);
	data.append('array_bet', modal_apuesta_pagar_tbl_array);
	data.append('total_bet', modal_apuesta_total_monto);
	data.append('evento_dineroat_id', evento_dineroat_id);

	auditoria_send({"proceso": "pagar_apuesta_ajax", "data": (Object.fromEntries(data.entries()))});

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "pagar_apuesta", "data": respuesta});
			//console.log(respuesta);
			$('#modal_apuesta_pagar').modal('hide');
			if (parseInt(respuesta.http_code) === 200) {
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			} else if (parseInt(respuesta.http_code) === 400) {
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			} else {
				swal('Aviso', 'Sin respuesta.', 'warning');
				return false;
			}
			return false;
		},
		error: function (result) {
			$('#modal_apuesta_pagar_btn_guardar').show();
			swal('Aviso', result, 'warning');
			auditoria_send({"proceso": "pagar_apuesta_error", "data": result});
			return false;
		}
	});
	return false;
}





//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// MODAL FECHA NACIMIENTO
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
$(function () {

	$('#modal_fec_nac_agregar_btn_guardar').click(function () {
		registrar_fec_nac();
		return false;
	});
 
});


function registrar_fec_nac() {
	$('#modal_fec_nac_agregar_btn_guardar').hide();
	$('#modal_fecha_nac_tlv').css('border', '');
	var fec_nac = $('#modal_fecha_nac_tlv').val();

	var data = {
		"accion": "guardar_fec_nac",
		"id_cliente": gen_cliente_id,
		"fec_nac": fec_nac
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "guardar_fec_nac", "data": respuesta});
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_fecha_nac_tlv').css('border', '1px solid red');
				$('#modal_fecha_nac_tlv').focus();
				$('#modal_fec_nac_agregar_btn_guardar').show();
				swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {

				$('#modal_fecha_nac').modal('hide');
				$('#cliente_fec_nac').val(fec_nac);
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			$('#modal_fecha_nac').modal('hide');
			swal('Aviso', result, 'warning');
			auditoria_send({"proceso": "guardar_fec_nac_error", "data": result});
			return false;
		}
	});
	return false;
}


//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// MODAL CAPTCHAP DNI
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
$(function () {

	$('#modal_captcha_btn_guardar').click(function () {
		verificar_captcha();
		return false;
	});

});

function verificar_captcha() {

	$('#modal_captcha_btn_guardar').hide();
	$('#modal_captcha').css('border', '');
	var captcha = $('#modal_captcha').val();
	var dni_cliente = $('#modal_dni_captcha').val();
	var tipo_cli = $('#modal_tercero_captcha').val();

	var data = {
		"accion": "verificar_captcha_api",
		"dni_cliente": dni_cliente,
		"captcha": captcha,
		"cookie_session": cookie_session
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
			if (parseInt(respuesta.http_code) === 200) {
				var result_verif =  JSON.parse(respuesta.result);
				var messageCode = (result_verif.messageCode); 

				if(messageCode == 9006){
					if (tipo_cli == 0){
						$('#modal_captcha_verf_dni').modal('hide');
					setTimeout(function() {
						swal('Aviso', 'El DNI consultado corresponde a un fallecido', 'warning');														
					}, 2000);
					registrar_cliente_idmf(dni_cliente, messageCode);
					return false;

					}else{
						$('#modal_captcha_verf_dni').modal('hide');
						swal('Aviso', 'El DNI consultado corresponde a un fallecido', 'warning');
						$("#modal_deposito_dni_titular_abono").focus();
						return false;
					}

				}else if(messageCode == 9002){
					
					if (tipo_cli == 0){
						$('#modal_captcha_verf_dni').modal('hide');	
						setTimeout(function() {
							swal('Aviso', 'El DNI consultado corresponde a un menor de edad', 'warning');										
						}, 2000);
						registrar_cliente_idmf(dni_cliente, messageCode);
						return false;
					}else{
						$('#modal_captcha_verf_dni').modal('hide');
						swal('Aviso', 'El DNI consultado corresponde a un menor de edad', 'warning');
						$("#modal_deposito_dni_titular_abono").focus();
						return false;
					}
			
				}else if(messageCode == 9004){
					if (tipo_cli == 0){
						swal('Aviso', 'Error en la cantidad de dígitos', 'warning');
						$('#modal_captcha_verf_dni').modal('hide');
						return false;
					}else{
						$('#modal_captcha_verf_dni').modal('hide');
						swal('Aviso', 'Error en la cantidad de dígitos', 'warning');
						$("#modal_deposito_dni_titular_abono").focus();
						return false;
					}
				}else if(messageCode == 8000){

					if (tipo_cli == 0){
						swal('Aviso', 'DNI sin observaciones', 'success');
						sec_tlv_reg_cliente();					
						$('#modal_captcha_verf_dni').modal('hide');
						return false;
					}else{
						swal('Aviso', 'DNI sin observaciones', 'success');					 				
						$('#modal_captcha_verf_dni').modal('hide');
						sec_tlv_reg_tercero_titular(dni_cliente);
					}

				}else if(messageCode == 9001){
					if (tipo_cli == 0){
						swal('Aviso', 'El DNI consultado no existe', 'warning');
					   $('#modal_captcha_verf_dni').modal('hide');
					   return false;
				   }else{
						$('#modal_captcha_verf_dni').modal('hide');
					   swal('Aviso', 'El DNI consultado no existe', 'warning');
					   $("#modal_deposito_dni_titular_abono").focus();
					   return false;
				   }
				}else{
					if (tipo_cli == 0){
						swal('Aviso', 'No se encontraron resultados', 'warning');
						$('#modal_captcha_verf_dni').modal('hide');
						sec_tlv_reg_cliente();
						return false;
					}else{
						$('#modal_captcha_verf_dni').modal('hide');
						swal('Aviso', 'No se encontraron resultados', 'warning');
						$("#modal_deposito_dni_titular_abono").focus();
						sec_tlv_reg_tercero_titular(dni_cliente);
						return false;
					}

				}
			} else if (parseInt(respuesta.http_code) === 400) {
				swal('Aviso', respuesta.result.message, 'warning');
				$('#modal_captcha_btn_guardar').show();

				return false;
			}


		},
		error: function (result) {
			$('#modal_captcha_verf_dni').modal('hide');
			swal('Aviso', result, 'warning');
			auditoria_send({"proceso": "verificar_captcha_api_error", "data": result});
			return false;
		}
	});
	return false;

}


function cargar_img_captcha(dni_cliente2, tipo_cli) {

	$('#modal_captcha').val('');	
	$('#modal_dni_captcha').val(dni_cliente2);
	$('#modal_tercero_captcha').val(tipo_cli);
	$('#modal_captcha_btn_guardar').show();
	

	var data = {
		"accion": "cargar_img_captcha_api",
		"dni_cliente": dni_cliente2
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "cargar_img_captcha_api", "data": respuesta});

			if (parseInt(respuesta.http_code) == 400) {	
				
				if (tipo_cli == 0){

					$('#modal_captcha_verf_dni').modal('hide');

					swal({
						html:true,
						title: '<span style="font-weight: 900;color: black;text-transform: uppercase;">Error API RENIEC </span> <br>¿Deseas continuar con el registro sin verificación?',
						type: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#0336FF',
						cancelButtonColor: '#d33',
						confirmButtonText: 'SI, CONFIRMAR',
						cancelButtonText: 'CANCELAR',
						closeOnConfirm: true,
						//,showLoaderOnConfirm: true
					}, function(){
						sec_tlv_reg_cliente();
						return false;
					});

				}else{

					$('#modal_captcha_verf_dni').modal('hide');

					swal({
						html:true,
						title: '<span style="font-weight: 900;color: black;text-transform: uppercase;">Error API RENIEC </span> <br>¿Deseas continuar con el registro sin verificación?',
						type: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#0336FF',
						cancelButtonColor: '#d33',
						confirmButtonText: 'SI, CONFIRMAR',
						cancelButtonText: 'CANCELAR',
						closeOnConfirm: true,
						//,showLoaderOnConfirm: true
					}, function(){
						sec_tlv_reg_tercero_titular(dni_cliente2);
						return false;
					});


				}
				
			}

			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_captcha_verf_dni').modal({backdrop: 'static', keyboard: false});
				var nomb_img_captcha =respuesta.nom_img;

				$.each(respuesta.result, function (index, item) {
					cookie_session = item; 
				});
	
				$("#modal_captcha_img").html(
					'<img style="display: block;-webkit-user-select: none;margin: auto;'+
					'	background-color: hsl(0, 0%, 90%);transition: background-color 300ms;"'+
					'	src="files_bucket/tls_captcha/'+nomb_img_captcha+'">');
	
				$('#modal_captcha').focus();

				return false;
			}
			return false;
			
		},
		error: function (result) {
			auditoria_send({"proceso": "cargar_img_captcha_error", "data": result});
		}
	});
}


function registrar_cliente_idmf(dni_cliente, messageCode) {

	var data = {
		"accion": "registrar_cliente_id_menor",
		"dni_cliente": dni_cliente,
		"messageCode": messageCode,
		"hash": tls_hash
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "registrar_cliente_id_menor", "data": respuesta});
			if (parseInt(respuesta.http_code) == 400) {			 
				swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				setTimeout(function() {
					swal('Aviso', 'Cliente registrado.', 'success'); 
				}, 1000);
				return false;
			}
			return false;
		},
		error: function (result) {			 
			swal('Aviso', result, 'warning');
			auditoria_send({"proceso": "registrar_cliente_id_menor_error", "data": result});
			return false;
		}
	});
	return false;

}



//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// MODAL ETIQUETA
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
$(function () {

	$('#modal_etiqueta_elegir_select').select2();

	//Button ELEGIR modal
	$('#btn_elejir_etiqueta').click(function () {
		$('#modal_etiqueta_elegir_select').val('0');
		$('#modal_etiqueta_elegir_select').select2().trigger('change');
		$('#modal_etiqueta_elegir_btn_guardar').show();
		$('#modal_etiqueta_elegir').modal();
		return false;
	});
	$('#modal_etiqueta_elegir_btn_guardar').click(function () {
		elegir_etiqueta();
		return false;
	});
	$('#modal_etiqueta_elegir_btn_actualizar').click(function () {
		actualizar_lista_etiqueta();
		return false;
	});

	//Button AGREGAR modal
	$('#btn_agregar_etiqueta').click(function () {
		$('#modal_etiqueta_agregar_i_etiqueta').val('');
		$('textarea#modal_etiqueta_agregar_txa_observacion').val('');
		$('#modal_etiqueta_agregar_i_color').val('');
		$('#modal_etiqueta_agregar_i_color').val('#0000ff');
		$('#modal_etiqueta_agregar_div_pintar').css('background', '#0000ff');
		$('#modal_etiqueta_agregar_div_tipo').css('border', '');
		$('#modal_etiqueta_agregar_select_tipo').val(1).trigger('change');
		$('#modal_etiqueta_agregar_btn_guardar').show();
		$('#modal_etiqueta_agregar').modal();
		return false;
	});
	$('#modal_etiqueta_agregar_btn_guardar').click(function () {
		registrar_etiqueta();
		return false;
	});
	$('#modal_etiqueta_agregar_i_color').ColorPicker({
		color: '#0000ff',
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('#modal_etiqueta_agregar_div_pintar').css('background', '#' + hex);
			$("#modal_etiqueta_agregar_i_color").val('#' + hex);
		}
	});
});
function actualizar_lista_etiqueta() {
	$('#modal_etiqueta_elegir_select').empty();
	$('#modal_etiqueta_elegir_select').append('<option value="0">:: Seleccione ::</option>');
	$('#modal_etiqueta_elegir_select').val('0');
	$('#modal_etiqueta_elegir_select').select2().trigger('change');
	var data = {
		"accion": "actualizar_etiqueta"
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "actualizar_etiqueta", "data": respuesta});
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
			//	console.log(respuesta.result);
				//var dato = JSON.parse(respuesta.result);
				//console.log(dato);
				/*
				 if (dato.length > 0) {
				 $.each(dato, function (index, item) {
				 $('#cbx_calle').append('<option value="' + item.id + '" color="'+item.color+'" texto="'+item.label+'">' + item.label + '</option>');
				 });
				 }
				 */
				swal('Aviso', 'Actualizar etiquetas.', 'success');
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "actualizar_etiqueta_error", "data": result});
			return false;
		}
	});
	return false;
}
function registrar_etiqueta() {
	$('#modal_etiqueta_agregar_btn_guardar').hide();

	$('#modal_etiqueta_agregar_i_etiqueta').css('border', '');
	$('#modal_etiqueta_agregar_txa_observacion').css('border', '');
	$('#modal_etiqueta_agregar_i_color').css('border', '');
	$('#modal_etiqueta_agregar_div_tipo').css('border', '');

	var i_etiqueta = $('#modal_etiqueta_agregar_i_etiqueta').val();
	var txa_observacion = $('textarea#modal_etiqueta_agregar_txa_observacion').val();
	var i_color = $('#modal_etiqueta_agregar_i_color').val();
	var tipo = $('#modal_etiqueta_agregar_select_tipo').val();
	if (!(i_etiqueta.length > 0)) {
		$('#modal_etiqueta_agregar_i_etiqueta').css('border', '1px solid red');
		$('#modal_etiqueta_agregar_i_etiqueta').focus();
		$('#modal_etiqueta_agregar_btn_guardar').show();
		return false;
	}
	if (!(txa_observacion.length > 0)) {
		$('#modal_etiqueta_agregar_txa_observacion').css('border', '1px solid red');
		$('#modal_etiqueta_agregar_txa_observacion').focus();
		$('#modal_etiqueta_agregar_btn_guardar').show();
		return false;
	}
	if (!(i_color.length > 0)) {
		$('#modal_etiqueta_agregar_i_color').css('border', '1px solid red');
		$('#modal_etiqueta_agregar_i_color').focus();
		$('#modal_etiqueta_agregar_btn_guardar').show();
		return false;
	}
	if(tipo == 0){
		$('#modal_etiqueta_agregar_div_tipo').css('border', '1px solid red');
		$('#modal_etiqueta_agregar_div_tipo').focus();
		$('#modal_etiqueta_agregar_btn_guardar').show();
		return false;
	}
	var data = {
		"accion": "guardar_etiqueta",
		"id_cliente": gen_cliente_id,
		"i_etiqueta": i_etiqueta,
		"txa_observacion": txa_observacion,
		"i_color": i_color,
		"tipo" : tipo
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "guardar_etiqueta", "data": respuesta});
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_etiqueta_agregar').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_etiqueta_agregar').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			$('#modal_etiqueta_agregar').modal('hide');
			swal('Aviso', result, 'warning');
			auditoria_send({"proceso": "guardar_etiqueta_error", "data": result});
			return false;
		}
	});
	return false;
}
function elegir_etiqueta() {
	$('#modal_etiqueta_elegir_btn_guardar').hide();
	var id_etiqueta = $('#modal_etiqueta_elegir_select').val();
	if (!(parseInt(id_etiqueta) > 0)) {
		$('#modal_etiqueta_elegir_select_div').css('border', '1px solid red');
		$('#modal_etiqueta_elegir_select').focus();
		$('#modal_etiqueta_elegir_btn_guardar').show();
		return false;
	}
	var data = {
		"accion": "elegir_etiqueta",
		"id_cliente": gen_cliente_id,
		"id_etiqueta": id_etiqueta
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "elegir_etiqueta", "data": respuesta});
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_etiqueta_elegir').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_etiqueta_elegir').modal('hide');
				swal('Aviso', 'Etiqueta asignada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			$('#modal_etiqueta_elegir').modal('hide');
			swal('Aviso', result, 'warning');
			auditoria_send({"proceso": "elegir_etiqueta_error", "data": result});
			return false;
		}
	});
	return false;
}
function eliminar_etiqueta(id_cliente_etiqueta) {
	swal({
		html: true,
		title: '¿Está seguro de quitar la etiqueta?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#0336FF',
		cancelButtonColor: '#d33',
		confirmButtonText: 'SI, QUITAR',
		cancelButtonText: 'CANCELAR',
		closeOnConfirm: false,
	}, function () {
		var data = {
			"accion": "eliminar_etiqueta",
			"id_cliente": gen_cliente_id,
			"id_cliente_etiqueta": id_cliente_etiqueta
		}
		$.ajax({
			url: "/sys/set_televentas.php",
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
				auditoria_send({"proceso": "eliminar_etiqueta", "data": respuesta});
				if (parseInt(respuesta.http_code) == 400) {
					swal('Aviso', respuesta.status, 'warning');
					return false;
				}
				if (parseInt(respuesta.http_code) == 200) {
					swal('Aviso', 'Se quitó la etiqueta correctamente.', 'success');
					listar_transacciones(gen_cliente_id);
					return false;
				}
				return false;
			},
			error: function (result) {
				swal('Aviso', result, 'warning');
				auditoria_send({"proceso": "eliminar_etiqueta_error", "data": result});
				return false;
			}
		});
	});
	return false;
}










//************************************************************************************************
//************************************************************************************************
// SIMULACION TIEMPO REAL
//************************************************************************************************
//************************************************************************************************
function obtener_transacciones_nuevas() {
	//console.log('obtener_transacciones_nuevas');
	//console.log(array_clientes);
	var cantidad_clientes = 0;
	var clientes = '';

	if (array_clientes.length > 0) {
		cantidad_clientes = array_clientes.length;
		$.each(array_clientes, function (index, item) {
			clientes += (item.id).toString();
			cantidad_clientes--;
			if (parseInt(cantidad_clientes) > 0) {
				clientes += ", ";
			}
		});
		$.post("/sys/set_televentas.php", {
			accion: "obtener_transacciones_nuevas",
			fecha_hora: var_fecha_hora,
			clientes: clientes,
			hash: tls_hash
		})
				.done(function (data) {
					try {
						var respuesta = JSON.parse(data);
						//console.log(respuesta);
						//console.log('obtener_transacciones_nuevas');
						if (parseInt(respuesta.http_code) == 200) {
							//console.log('result: ' + respuesta.result);
							var nuevos = [];
							$.each(respuesta.result, function (index2, item2) {
								//console.log('item2: ' + item2);
								//console.log('div id: ' + item2.cliente_id); 
								if(item2.proveedor != 8){ //Color si no es juegos virtuales de golden race
									//$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').addClass('naranja');
									$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente_transaccion_nueva').html('');
									$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente_transaccion_nueva').removeClass('div_tab_cliente_transaccion_nueva_add');
									$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').removeClass('morado');
									$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').removeClass('rojo');
									$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').removeClass('verde');

									if ([9,11].includes(parseInt(item2.tipo_id))){
										if(parseInt(item2.estado) === 2){ //aprobado
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').addClass('morado');
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente_transaccion_nueva').html('Pago realizado');
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente_transaccion_nueva').addClass('div_tab_cliente_transaccion_nueva_add');
											sec_tlv_consultar_retiros_pagados_solicitados_por_mi();
										}else if(parseInt(item2.estado) === 3){
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').addClass('rojo');
										}
									}else if ([1,26].includes(parseInt(item2.tipo_id))){
										if(parseInt(item2.estado) === 1){
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').addClass('verde');
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente_transaccion_nueva').html(item2.tipo_jugada);
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente_transaccion_nueva').addClass('div_tab_cliente_transaccion_nueva_add');
										}else{
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').addClass('rojo');
										}
									}else{
										if(parseInt(item2.estado) === 1){
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').addClass('verde');
										}else{
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').addClass('rojo');
										}
									}
									/*if(parseInt(item2.estado) ===1){
										if ([9,11].includes(parseInt(item2.tipo_id))){
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').addClass('morado');
										} else {
											$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').addClass('verde');
										}
									} else {
										$('#' + parseInt(item2.cliente_id)).find('.div_tab_cliente').addClass('rojo');
									}*/
								}
								
								if (parseInt(gen_cliente_id) === parseInt(item2.cliente_id)) {
									listar_transacciones(gen_cliente_id);
								}
								var notificacionOld = JSON.parse(localStorage.getItem("listNew"));
								var filteredArray = null;
								if (notificacionOld !== null) {
									filteredArray = notificacionOld.filter(function (ele, pos) {
										return notificacionOld.indexOf(ele) == pos;
									});
									notificacionOld = filteredArray;
								}

								nuevos = notificacionOld == null ? [] : notificacionOld;
								var title = item2.cuenta_descripcion + ' - ' + item2.usuario_nombre;

								var message = '';
								var icono = '';

								if (item2.estado == 2 && item2.tipo_id == 1) {
									message = 'DEPOSITO RECHAZADO';
									icono = '../images/foco-rojo.png';
								} else if (item2.estado == 1 && item2.tipo_id == 1) {
									message = 'DEPOSITO APROBADO';
									icono = '../images/foco-azul.png';
								}
								if (item2.tipo_id == 12) {
									title = item2.usuario_nombre;
									message = 'SOLICITUD DE RETIRO RECHAZADA';
									icono = '../images/foco-rojo.png';
								} else if (item2.tipo_id == 11) {
									title = item2.usuario_nombre;
									message = 'SOLICITUD DE RETIRO PAGADA';
									icono = '../images/foco-azul.png';
								} else if (item2.tipo_id == 9 && item2.estado == 5) {
									title = item2.usuario_nombre;
									message = 'SOLICITUD DE RETIRO EN PROCESO';
									icono = '../images/foco-azul.png';
								} else if (item2.tipo_id == 9 && item2.estado == 6) {
									title = item2.usuario_nombre;
									message = 'SOLICITUD DE RETIRO VERIFICADA';
									icono = '../images/foco-azul.png';
								} else if (item2.tipo_id == 9 && item2.estado == 2) {
									title = item2.usuario_nombre;
									message = 'SOLICITUD DE RETIRO PAGADA';
									icono = '../images/foco-azul.png';
								} else if (item2.tipo_id == 9 && item2.estado == 3) {
									title = item2.usuario_nombre;
									message = 'SOLICITUD DE RETIRO RECHAZADA';
									icono = '../images/foco-rojo.png';
								}else if (item2.tipo_id == 4 && item2.proveedor == 8 && item2.estado == 1) {
									title = '';
									message = '';
									icono = '../images/foco-azul.png';
								}else if (item2.tipo_id == 5 && item2.proveedor == 8 && item2.estado == 1) {
									title = '';
									message = '';
									icono = '../images/foco-azul.png';
								}

								//var message = item2.estado == 2 ? 'DEPOSITO RECHAZADO' : 'DEPOSITO APROBADO' ;
								//var icono = item2.estado == 2 ? '../images/foco-rojo.png' : '../images/foco-azul.png' ;

								if (notificacionOld != null) {
									let nuevaNotificacion = notificacionOld.includes(item2.id);
									if (!nuevaNotificacion) {
										nuevos.push(item2.id);
										if(message != ''){
											notification(title, message + icono);
										}
									} else if (item2.tipo_id == 9 && item2.estado == 2) {
										nuevos.push(item2.id);
										if(message != ''){
											notification(title, message + icono);
										}
									}
								} else {
									nuevos.push(item2.id);
									if(message != ''){
										notification(title, message, icono);	
									}
								}
								nuevos = nuevos.filter(function (ele, pos) {
									return nuevos.indexOf(ele) == pos;
								});
							});

							localStorage.setItem("listNew", JSON.stringify(nuevos));
							obtener_fecha_hora();
						}

						//console.log(respuesta.result_2);
						$.each(respuesta.result_2, function (index3, item3) {
							remover_cliente(item3.client_id);
						});
						$.each(respuesta.result_otra_pestana, function (index3, item3) {
							remover_cliente_reabierto(item3.client_id);
						});
						if(parseFloat(respuesta.result_tls_ultima_version)>0){
							if(parseFloat(tls_version)<parseFloat(respuesta.result_tls_ultima_version)){
								alertify.error('El sistema ha sido actualizado. '+
									'Ctrl+F5 (Si estás en PC) o Ctrl+Tecla Función +F5 (Si estás en laptop) o contactar con el área de soporte..', 5);
							}
						}
					} catch (e) {
					//	console.log("Error de TRY-CATCH -- Error: " + e);
					}
				})
				.fail(function (xhr, status, error) {
				//	console.log("Error de .FAIL -- Error: " + error);
				});
	}
}

function notification(titulo, mensaje, icono) {
	if (window.Notification && Notification.permission !== "denied") {
		Notification.requestPermission(function (status) {  // status is "granted", if accepted by user
			var n = new Notification(titulo, {
				body: mensaje,
				icon: icono, // optional 
			});
			n.onclick = function (evt) {
				try {
					parent.focus();
				} catch (ex) {
				}
			};
		});
	}
}


















//************************************************************************************************
//************************************************************************************************
// DIV CLIENTE
//************************************************************************************************
//************************************************************************************************

$(function () {

	//Button guardar
	$('#cliente_btn_guardar').click(function () {
		guardar_cliente();
	});

});

function limpiar_bordes_div_cliente() {
	$('#cliente_tipo_doc').css('border', '');
	$('#cliente_num_doc').css('border', '');
	$('#cliente_idweb_div').css('border', '');
	$('#cliente_idweb').css('border', '');
    $('#cliente_idwebc').css('border', '');
	$('#cliente_local').css('border', '');
	$('#cliente_celular').css('border', '');
	$('#cliente_fec_nac').css('border', '');
	$('#cliente_apepaterno').css('border', '');
	$('#cliente_apematerno').css('border', '');
	$('#cliente_correo').css('border', '');
	$('#cliente_tercero_titular').css('border', '');
	$('#cliente_direccion').css('border', '');
	$('#div_labels').html('');
}

function guardar_cliente() {
	limpiar_bordes_div_cliente();
	var tipo_doc = $('#cliente_tipo_doc').val();
	var num_doc = $('#cliente_num_doc').val().toString();
	var celular = $('#cliente_celular').val().replace(/\D/g, '');
	var fec_nac = $('#cliente_fec_nac').val();
	 
	var idweb = $('#cliente_idweb').val();
	var id_jugador = $('#cliente_idjugador').val();
	var nombre = $('#cliente_nombre').val();
	var apepaterno = $('#cliente_apepaterno').val();
	var apematerno = $('#cliente_apematerno').val();
	var correo = $('#cliente_correo').val();
	var bono_limite = $('#bono_limite').val();
	var local = $('#cliente_local').val();

	var direccion = $('#cliente_direccion').val();
	var nacionalidad = $('#sec_tlv_cliente_nacionalidad').val();
	var departamento = $('#sec_tlv_cliente_departamento').val();
	var provincia = $('#sec_tlv_cliente_provincia').val();
	var distrito = $('#sec_tlv_cliente_distrito').val();
	var ubigeo = departamento + provincia + distrito;

	var tyc = $('#cliente_tyc').is(':checked') ? true : false;
	var pep = $('#cliente_pep2').val(); //$('#cliente_pep').is(':checked') ? true : false;
	pep = pep =="" ? null : pep;


	var cliente_tercero_titular = $('#cliente_tercero_titular').val();
	fec_nac_f = fec_nac;
	
	if (!(parseInt(tipo_doc) >= 0 && parseInt(tipo_doc) <= 2)) {
		$('#cliente_tipo_doc').css('border', '1px solid red');
		$('#cliente_tipo_doc').focus();
		return false;
	}


	// if(departamento.length == 0){
	// 	alertify.error('Seleccione un departamento ',3);
	// 	return false;
	// }

	// if(provincia.length == 0){
	// 	alertify.error('Seleccione una provincia ',3);
	// 	return false;
	// }

	// if(distrito.length == 0){
	// 	alertify.error('Seleccione un distrito ',3);
	// 	return false;
	// }


	if (parseInt(tipo_doc) === 0) { //DNI
		if (!(num_doc.length === 8)) {
			$('#cliente_num_doc').css('border', '1px solid red');
			$('#cliente_num_doc').focus();
			swal('Aviso', 'El DNI debe tener 8 digitos', 'warning');
			return false;
		}
	}else if(parseInt(tipo_doc) === 1){ //CE/PTP
		if (num_doc.length > 9) {
			$('#cliente_num_doc').css('border', '1px solid red');
			$('#cliente_num_doc').focus();
			swal('Aviso', 'El CE/PTP debe tener más de 9 digitos', 'warning');
			return false;
		}
	}else if(parseInt(tipo_doc) === 2){ //PASAPORTE
		if (num_doc.length > 12) {
			$('#cliente_num_doc').css('border', '1px solid red');
			$('#cliente_num_doc').focus();
			swal('Aviso', 'El Pasaporte debe tener más de 12 digitos', 'warning');
			return false;
		}
	}
	//if (celular.length > 0) {
		if (!(celular.length === 9)) {
			$('#cliente_celular').css('border', '1px solid red');
			$('#cliente_celular').focus();
			alertify.error('Ingrese un número de telefono válido.',3);
			return false;
		}
	//}

	//if (fec_nac.length > 0) {
		if (!(fec_nac.length == 10)) {
			$('#cliente_fec_nac').css('border', '1px solid red');
			$('#cliente_fec_nac').focus();
			alertify.error('Ingrese la fecha de nacimiento del cliente.',3);
			return false;
		}
	//}


	if ((apepaterno.length === 0)) {
		$('#cliente_apepaterno').css('border', '1px solid red');
		$('#cliente_apepaterno').focus();
		alertify.error('Debe ingresar el apellido paterno.',3);
		return false;
	}

	if ((apematerno.length === 0)) {
		$('#cliente_apematerno').css('border', '1px solid red');
		$('#cliente_apematerno').focus();
		alertify.error('Debe ingresar el apellido materno.',3);
		return false;
	}


	if (correo.length > 0) {
		if (correo.length < 150) {
			if (correo.includes('@') == false) {
				$('#cliente_correo').css('border', '1px solid red');
				$('#cliente_correo').focus();
				alertify.error('Debe ingresar un correo válido. (Falta @).',3);
				return false;
			}
		}else{
			$('#cliente_correo').css('border', '1px solid red');
			$('#cliente_correo').focus();
			alertify.error('El tamaño del correo a excedido la cantidad de caracteres permitidas (150)',3);
			return false;
		}
	}

	ubigeo = ubigeo.length<6 ? null : ubigeo;

	/*
	if (!(idweb.length > 2)) {
		$('#cliente_idweb').css('border', '1px solid red');
		$('#cliente_idweb').focus();
		return false;
	}
	*/
	if (!(parseInt(local) > 0)) {
		$('#cliente_local').css('border', '1px solid red');
		$('#cliente_local').focus();
		alertify.error('Debe seleccionar el local al que pertenece.',3);
		return false;
	}

	var data = {
		"accion": "editar_cliente",
		"id_cliente": gen_cliente_id,
		"tipo_doc": tipo_doc,
		"num_doc": num_doc,
		"celular": celular,
		"fec_nac": fec_nac,
		"idweb": idweb,
		"id_jugador": id_jugador,
		"nombre": nombre,
		"apepaterno": apepaterno,
		"apematerno": apematerno,
		"correo": correo,
		"cc_id": local,
		"cliente_tercero_titular": cliente_tercero_titular,		
		"bono_limite": bono_limite,
		"block_hash": tls_hash,
		"direccion": direccion,
		"nacionalidad": nacionalidad,
		"tyc": tyc,
		"pep": pep,
		"ubigeo": ubigeo
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "editar_cliente", "data": respuesta});
			//console.log(respuesta);
			$('#sec_tlv_modal_url_sorteo_txt').val('');
			if (parseInt(respuesta.http_code) == 400) {
				if (typeof respuesta.datos_cliente != "undefined") {
					$('#cliente_num_doc').val(respuesta.datos_cliente[0].num_doc);
					$('#cliente_celular').val(respuesta.datos_cliente[0].telefono);
					$('#cliente_idweb').val(respuesta.datos_cliente[0].web_id);
				}
				
				if((respuesta.result_dupli.length === 0) || (respuesta.result_dupli.length === 1 && parseInt(gen_cliente_id) != 0)){
					swal('Aviso', respuesta.status, 'warning');
					return false;

				}
			 
				else{
 
					permiso_fusion = $('#sec_tlv_btn_fusion').val();

					if(permiso_fusion == '1'){
						swal('Aviso', respuesta.status, 'warning');
						return false;
						
					}else{
						$('#modal_clientes_duplicados').modal('show');
						
						
						//console.log(respuesta.result_dupli);
						list_cli_dup =  respuesta.result_dupli;

						sec_tlv_limpiar_tabla_duplicados();
						var estilo = ' style=" color: #6EC971; font-weight: bold;"';
						$.each(respuesta.result_dupli, function (index, item) {
							$('#modal_tabla_clientes_duplicados tbody').append(
								'<tr>' +
								'	<td align="center" >' +
								'		<button type="button" class="btn btn-primary" ' +
								'	       onclick="sec_tlv_fusionar_clientes(' + item.id + ')">' +
								'	       <span class="fa fa-check-square-o"></span>' +
								'	   </button>' +
								'	</td>' +
								'	<td>' + item.tipo_doc_nomb + '</td>' +
								'	<td ' + ((sec_tlv_comparar_resaltar(num_doc, item.num_doc) == true) ? estilo : '') + '>' + item.num_doc + '</td>' +
								'	<td ' + ((sec_tlv_comparar_resaltar(celular, item.telefono) == true) ? estilo : '') + '>' + item.telefono + '</td>' +
								'	<td>' + item.cliente + '</td>' +
								'	<td>' + item.correo + '</td>' +
								'	<td>' + item.player_id + '</td>' +
								'	<td ' + ((sec_tlv_comparar_resaltar(idweb, item.web_id) == true) ? estilo : '') + '>' + item.web_id + '</td>' +
								'	<td>' + item.web_full_name + '</td>' +				
								'	<td>' + item.total_tran + '</td>' +
								'	<td>' + item.total_balance + '</td>' +
								'	<td align="center" >' +
								'		<button type="button" class="btn btn-warning" onclick="sec_tlv_quitar_cli_dup(' + item.id + ')">' +
								'	       <span class="fa fa-minus"></span>' +
								'	   </button>' +
								'	</td>' +
								'</tr>'
							);
						});
					}
				}
			}

			if (parseInt(respuesta.http_code) == 200) {
				var id_seleccionado = respuesta.result.id;
				if(parseInt(gen_cliente_id) == 0){
					id_seleccionado = gen_cliente_id;
				}else{
					id_seleccionado = respuesta.result.id;
				}
				$('#' + id_seleccionado).remove();
				$.each(array_clientes, function (index, item) {
					if (item.id === id_seleccionado) {
						array_clientes.splice(index, 1);
						array_clientes.push(respuesta.result);
						seleccionar_cliente(respuesta.result.id, true);
						alertify.success('Acción realizada con éxito', 3);
						if(respuesta.url_sorteo != ""){
							//$('#sec_tlv_modal_url_sorteo_cliente').modal('show');
							//$('#sec_tlv_modal_url_sorteo_txt').val(respuesta.url_sorteo);
							sec_tlv_get_premio_sorteo(respuesta.result.id, respuesta.url_sorteo)
						}
						return false;
					}
				});
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "editar_cliente_error", "data": result});
			return false;
		}
	});

}






function sec_tlv_limpiar_tabla_duplicados2(){
	$('#modal_tabla_clientes_duplicados').empty();	
	$('#modal_tabla_clientes_duplicados').append(
		'<thead>'+
		'<tr style="background: #323b42;">'+		 
			'<td>TIPO DOC</td>'+
			'<td >NUM DOC</td>'+							
			'<td >CELULAR</td>'+
			'<td >CLIENTE</td>'+
			'<td >CORREO</td>'+
			'<td >PLAYER ID</td>'+
			'<td >WEB ID</td>'+
			'<td >WEB FULL NAME</td>'+
			'<td >NUM TRANSAC</td>'+
			'<td >BALANCE</td>'+		 
		'</tr>'+
		'</thead>' +
		'<tbody>' +
		'</tbody'
	);
}

function sec_tlv_limpiar_tabla_duplicados(){
	$('#modal_tabla_clientes_duplicados').empty();	
	$('#modal_tabla_clientes_duplicados').append(
		'<thead>'+
		'<tr>'+
			'<td>SELECCIONAR</td>'+
			'<td>TIPO DOC</td>'+
			'<td >NUM DOC</td>'+							
			'<td >CELULAR</td>'+
			'<td >CLIENTE</td>'+
			'<td >CORREO</td>'+
			'<td >PLAYER ID</td>'+
			'<td >WEB ID</td>'+
			'<td >WEB FULL NAME</td>'+
			'<td >NUM TRANSAC</td>'+
			'<td >BALANCE</td>'+
			'<td >IGNORAR</td>'+
		'</tr>'+
		'</thead>' +
		'<tbody>' +
		'</tbody'
	);
}

function sec_tlv_comparar_resaltar(text_base, text_new){
	if(text_base == text_new){
		return true;
	}else{
		return false;
	}
}











//************************************************************************************************
//************************************************************************************************
// DIV CLIENTE
//************************************************************************************************
//************************************************************************************************

$(function () {

	//Button guardar
	$('#cliente_btn_consultar_dni').click(function () {
		consultar_dni();
	});

});

function consultar_dni() {
	$('#cliente_tipo_doc').css('border', '');
	$('#cliente_num_doc').css('border', '');

	var tipo_doc = $('#cliente_tipo_doc').val();
	var num_doc = $('#cliente_num_doc').val().toString();

	if (parseInt(tipo_doc) !== 0) {
		$('#cliente_tipo_doc').css('border', '1px solid red');
		$('#cliente_tipo_doc').focus();
		return false;
	}
	if (num_doc.length !== 8) {
		$('#cliente_num_doc').css('border', '1px solid red');
		$('#cliente_num_doc').focus();
		return false;
	}

	var data = {
		"accion": "consultar_dni",
		"num_doc": num_doc
	}


	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "consultar_dni", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				//console.log(respuesta.result);
				$('#cliente_nombre').val(respuesta.result.nombres);
				$('#cliente_apepaterno').val(respuesta.result.apellido_paterno);
				$('#cliente_apematerno').val(respuesta.result.apellido_materno);
				//console.log(array_clientes); 
				let bono_limit = respuesta.result.bono_limite == '' ? '10000.00' : respuesta.result.bono_limite
				$('#bono_limite').val(bono_limit);
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "consultar_dni_error", "data": result});
			return false;
		}
	});
}





function sec_tlv_verificar_datos_fusion_clientes() {

	var tipo_doc_s = $.trim($("#tipo_doc_s").val());
	var num_doc_s = $.trim($("#num_doc_s").val());
	var telefono_s = $.trim($("#telefono_s").val());
	var cliente_s = $.trim($("#cliente_s").val());
	var correo_s = $.trim($("#correo_s").val());
	var player_id_s = $.trim($("#player_id_s").val());
	var web_id_s = $.trim($("#web_id_s").val());
	var web_name_s = $.trim($("#web_name_s").val());


	if (parseInt(tipo_doc_s) === 0) { //DNI
		if (!(num_doc_s.length === 8)) {
			$('#num_doc_s').css('border', '1px solid red');
			$('#num_doc_s').focus();
			swal('Aviso', 'El DNI debe tener 8 dígitos', 'warning');
			return false;
		}
	}
	if(parseInt(tipo_doc_s) === 1){ //CE/PTP
		if (num_doc_s.length < 9) {
			$('#num_doc_s').css('border', '1px solid red');
			$('#num_doc_s').focus();
			swal('Aviso', 'El CE/PTP debe tener más de 9 dígitos', 'warning');
			return false;
		}
	} 
	
	if(parseInt(tipo_doc_s) === 2){ //PASAPORTE
		if (num_doc_s.length < 12) {
			$('#num_doc_s').css('border', '1px solid red');
			$('#num_doc_s').focus();
			swal('Aviso', 'El Pasaporte debe tener más de 12 dígitos', 'warning');
			return false;
		}
	}
	if (telefono_s.length > 0) {
		if ((telefono_s.length < 9)) {
			$('#telefono_s').css('border', '1px solid red');
			swal('Aviso', 'El teléfono debe tener 9 dígitos', 'warning');
			$('#telefono_s').focus();
			return false;
		}
	}


	if (player_id_s.length > 0) {
		if ((player_id_s.length <= 2)) {
			$('#player_id_s').css('border', '1px solid red');
			swal('Aviso', 'El player id debe tener al menos 2 dígitos', 'warning');
			$('#player_id_s').focus();
			return false;
		}
	}

	if (web_id_s.length > 0) {
		if ((web_id_s.length <= 2)) {
			$('#web_id_s').css('border', '1px solid red');
			swal('Aviso', 'El web id debe tener al menos 2 dígitos', 'warning');
			$('#web_id_s').focus();
			return false;
		}
	}

	if (correo_s.length > 0) {
		if (correo_s.length < 150) {
			if (correo_s.includes('@') == false) {
				$('#correo_s').css('border', '1px solid red');
				$('#correo_s').focus();
				swal('Aviso', 'Debe ingresar un correo válido. (Falta @).', 'warning');				 
				return false;
			}
		}else{
			$('#correo_s').css('border', '1px solid red');
			$('#correo_s').focus();
			swal('Aviso', 'El tamaño del correo a excedido la cantidad de caracteres permitidas (150)', 'warning');
			return false;
		}
	}
 
	var data = {
		"accion": "verificar_datos_fusion_clientes",
		"list_cli_dup": list_cli_dup,
		"cli_dup_select": cli_dup_select,
		"tipo_doc_s": tipo_doc_s,
		"num_doc_s": num_doc_s,
		"telefono_s": telefono_s,
		"cliente_s": cliente_s,
		"correo_s": correo_s,
		"player_id_s": player_id_s,
		"web_id_s": web_id_s,
		"web_name_s": web_name_s
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "verificar_datos_fusion_clientes", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', 'Verificar los datos ingresados en CELULAR, PLAYER ID,  WEB ID o CORREO, pertenecen a otro cliente', 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				//	console.log(respuesta.result);			

				swal({
					html:true,
					title: '<span style="font-weight: 900;color: black;text-transform: uppercase;">ADVERTENCIA</span> <br>¿Está seguro de continuar? (Se enviará un correo con los datos fusionados a los supervisores)',
					type: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#0336FF',
					cancelButtonColor: '#d33',
					confirmButtonText: 'SI, CONFIRMAR',
					cancelButtonText: 'CANCELAR',
					closeOnConfirm: true,
					//,showLoaderOnConfirm: true
				}, function(){
					sec_tlv_guardar_fusion_clientes();
					$('#btn_fusionar').hide();
				});

				
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "verificar_datos_fusion_clientes_error", "data": result});
			return false;
		}
	});

}

 
function sec_tlv_guardar_fusion_clientes() {

	var tipo_doc_s  = $.trim($("#tipo_doc_s").val());
	var num_doc_s   = $.trim($("#num_doc_s").val());
	var telefono_s  = $.trim($("#telefono_s").val());
	var cliente_s   = $.trim($("#cliente_s").val());
	var correo_s    = $.trim($("#correo_s").val());
	var player_id_s = $.trim($("#player_id_s").val());
	var web_id_s    = $.trim($("#web_id_s").val());
	var web_name_s  = $.trim($("#web_name_s").val());
 
	var data = {
		"accion": "guardar_fusion_clientes",
		"list_cli_dup": list_cli_dup,
		"cli_dup_select": cli_dup_select,
		"tipo_doc_s": tipo_doc_s,
		"num_doc_s": num_doc_s,
		"telefono_s": telefono_s,
		"cliente_s": cliente_s,
		"correo_s": correo_s,
		"player_id_s": player_id_s,
		"web_id_s": web_id_s,
		"web_name_s": web_name_s,
		"fec_nac_s": fec_nac_f
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "guardar_fusion_clientes", "data": respuesta});
		//	console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
		//		console.log(respuesta.result);			
				swal('Aviso', 'Fusión realizada con éxito.', 'success');
				$('#modal_clientes_duplicados').modal('hide');
				remover_cliente_btn_x(gen_cliente_id);
				 
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "guardar_fusion_clientes_error", "data": result});
			return false;
		}
	});

}
function sec_tlv_fusionar_clientes(id) {

	if (list_cli_dup.length > 1 ){

		var data = {
			"accion": "fusionar_clientes",
			"clientes": list_cli_dup,
			"id_cliente": id,
		}

		$.ajax({
			url: "/sys/set_televentas.php",
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
				auditoria_send({"proceso": "fusionar_clientes", "data": respuesta});
				//console.log(respuesta);
				if (parseInt(respuesta.http_code) == 400) {
					swal('Aviso', respuesta.result, 'warning');
					return false;
				}
				if (parseInt(respuesta.http_code) == 200) {
					
				//	console.log(respuesta.result);
					cli_dup_select = respuesta.result;
				//	console.log('seleccionado');
					console.log(cli_dup_select);
					var tipo_doc_rf = 0;
					if(respuesta.result[1] == ''){
						tipo_doc_rf = $('#cliente_tipo_doc').val();
					}else{
						tipo_doc_rf = respuesta.result[1];
					}
					var num_doc_rf = '';
					if(respuesta.result[2] == ''){
						num_doc_rf = $('#cliente_num_doc').val();
					}else{
						num_doc_rf = respuesta.result[2];
					}
					//swal('Aviso', 'Fusión realizada con éxito.', 'success');
					sec_tlv_limpiar_tabla_duplicados2();
					$('#btn_fusionar').show();
					$.each(list_cli_dup, function (index, item) { 
					
						$('#modal_tabla_clientes_duplicados').append(
							'<tr  style="background: #393c398c;">' +				
							'<td>'+item.tipo_doc_nomb+ '</td>' +
							'<td>'+item.num_doc+'</td>' + 	
							'<td>'+item.telefono+'</td>' +  
							'<td>'+item.cliente+ '</td>' +  
							'<td>'+item.correo+ '</td>' +  
							'<td>'+item.player_id+ '</td>' +  
							'<td>'+item.web_id+'</td>' +  
							'<td>'+item.web_full_name+ '</td>' +
							'<td>' + item.total_tran + '</td>' +
							'<td>' + item.total_balance + '</td>' +				
						'</tr>'						 
						);
					});
					$('#modal_tabla_clientes_duplicados').append(
						'<thead>'+
						'<tr style="background: #337ab7;">'+
						'<td align="center" colspan="10" >RESULTADO FUSIÓN</td>'+
						'</tr>'+					
						'<tr style="background: #337ab700;">'+
							'<td >TIPO DOC</td>'+
							'<td >NUM DOC</td>'+							
							'<td >CELULAR</td>'+
							'<td >CLIENTE</td>'+
							'<td >CORREO</td>'+
							'<td >PLAYER ID</td>'+
							'<td >WEB ID</td>'+
							'<td >WEB FULL NAME</td>'+
							'<td >NUM TRANSAC</td>'+
							'<td >BALANCE</td>'+
						'</tr>'+
						'</thead>' +
						'<tr style="background: #337ab747;">' +
					
						'<td><select disabled class="form-control form-control-sm select2" id="tipo_doc_s"><option value="0" '+((tipo_doc_rf == 0) ? 'selected' : '' )+'>DNI</option><option value="1" '+((tipo_doc_rf == 1) ? 'selected' : '' )+'>CE/PTP</option><option value="2" '+((tipo_doc_rf == 2) ? 'selected' : '' )+'>PASAPORTE</option></select></td>' +	
								
						'<td><input disabled id="num_doc_s" maxlength="12" type="number"  value="'+num_doc_rf+'"/></td>' + 	
						'<td><input id="telefono_s" maxlength="9" type="number" value="'+respuesta.result[3]+'"/></td>' +  
						'<td><input id="cliente_s" type="text" value="'+respuesta.result[4]+'"/></td>' +  
						'<td><input id="correo_s" type="text" value="'+respuesta.result[10]+'"/></td>' + 
						'<td><input id="player_id_s" type="number" value="'+respuesta.result[5]+'" /></td>' +  
						'<td><input id="web_id_s" type="number" value="'+respuesta.result[6]+'"/></td>' +  
						'<td><input id="web_name_s" type="text" value="'+respuesta.result[7]+'" /></td>' + 				  			
						'<td>' + respuesta.result[8] + '</td>' +
						'<td>' + respuesta.result[9] + '</td>' +
								
						'</tr>'+
						'<th>'+
						'<td align="center" colspan="9" ><br><button type="submit" id="btn_fusionar" class="btn btn-success btn-lg">'+
						'<span style="color: #ff0000b5;" class="fa fa-compress"></span> FUSIONAR'+
						'   </button></td>'+
						'</th>'+	
						'<tbody> ' +
						'</tbody></form>'
					);
					
					return false;
				}
				return false;
			},
			error: function (result) {
				auditoria_send({"proceso": "fusionar_clientes_error", "data": result});
				return false;
			}
		});

	}else{
		swal('Aviso', 'Para realizar la fusión debe haber más de un cliente en el listado. Si el cliente a fusionar es nuevo, debe concluir el registro, más info en el botón de ayuda.', 'warning');
		return false;
	}
}

function sec_tlv_quitar_cli_dup(id_cli) {
 	list_cli_dup = list_cli_dup.filter(x=> x.id != id_cli);
	 
	//console.log(list_cli_dup);
 	actualizar_tabla_cli_dup();
}


function actualizar_tabla_cli_dup() {
	sec_tlv_limpiar_tabla_duplicados();
	$.each(list_cli_dup, function (index, item) { 
		$('#modal_tabla_clientes_duplicados').append(
			'<tr>' +
			'<td align="center">' +
			'	<button type="button" class="btn btn-primary" '+
			'       onclick="sec_tlv_fusionar_clientes(' + item.id + ')">'+
			'       <span class="fa fa-check-square-o"></span>'+
			'   </button></td>' +
			'<td>' + item.tipo_doc_nomb + '</td>' +
			'<td>' + item.num_doc + '</td>' +						
			'<td>' + item.telefono + '</td>' +
			'<td>' + item.cliente + '</td>' +
			'<td>' + item.correo + '</td>' +
			'<td>' + item.player_id + '</td>' +
			'<td>' + item.web_id + '</td>' +
			'<td>' + item.web_full_name + '</td>' +				
			'<td>' + item.total_tran + '</td>' +
			'<td>' + item.total_balance + '</td>' +
			'<td align="center">' +
			'	<button type="button" class="btn btn-warning" onclick="sec_tlv_quitar_cli_dup(' + item.id + ')">'+
			'       <span class="fa fa-minus"></span>'+
			'   </button></td>' +
		  '</tr>'
			 
		);
	});
}





















function obtener_fecha_hora() {
	$.post("/sys/set_televentas.php", {
		accion: "obtener_fecha_hora"
	})
			.done(function (data) {
				try {
					var respuesta = JSON.parse(data);
					if (parseInt(respuesta.http_code) == 200) {
						var_fecha_hora = respuesta.result;
					}
				} catch (e) {
				//	console.log("Error de TRY-CATCH -- Error: " + e);
				}
			})
			.fail(function (xhr, status, error) {
			//	console.log("Error de .FAIL -- Error: " + error);
			});

}




$(function () {



});

function filterFloat_2(evt, input) {
	// Backspace = 8, Enter = 13, '0′ = 48, '9′ = 57, '.' = 46, '-' = 43
	var key = window.Event ? evt.which : evt.keyCode;
	var chark = String.fromCharCode(key);
	var tempValue = input.value + chark;
	if (key >= 48 && key <= 57) {
		if (filter_2(tempValue) === false) {
			return false;
		} else {
			return true;
		}
	} else {
		if (key == 8 || key == 13 || key == 0) {
			return true;
		} else if (key == 46) {
			if (filter_2(tempValue) === false) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
}

function filter_2(__val__) {
	var preg = /^([0-9]+\.?[0-9]{0,2})$/;
	if (preg.test(__val__) === true) {
		return true;
	} else {
		return false;
	}
}






function tabla_transacciones_datatable_formato(id) {
	if ($.fn.dataTable.isDataTable(id)) {
		$(id).DataTable().destroy();
	}
	$(id).DataTable({
		'paging': true,
		'lengthChange': true,
		'searching': true,
		'ordering': true,
		'order': [[0, "desc"]],
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









function retrieveImageFromClipboardAsBlob(pasteEvent, callback) {
	if (pasteEvent.clipboardData == false) {
		if (typeof (callback) == "function") {
			callback(undefined);
		}
	}
	;
	var items = pasteEvent.clipboardData.items;
	if (items == undefined) {
		if (typeof (callback) == "function") {
			callback(undefined);
		}
	}
	;
	for (var i = 0; i < items.length; i++) {
		// Skip content if not image
		if (items[i].type.indexOf("image") == -1)
			continue;
		// Retrieve image on clipboard as blob
		var blob = items[i].getAsFile();

		if (typeof (callback) == "function") {
			callback(blob);
		}
	}
}
window.addEventListener("paste", function (e) {
	// Handle the event
	retrieveImageFromClipboardAsBlob(e, function (imageBlob) {
		// If there's an image, display it in the canvas
		if (imageBlob) {
			let fileInputElement = undefined;
			if($('#modal_deposito').is(':visible')){
			//	console.log('modal_deposito');
				//fileInputElement = $('#modal_deposito_voucher');
				fileInputElement = document.getElementById('modal_deposito_voucher');
			//	console.log(fileInputElement);
			}
			if($('#modal_cancer').is(':visible')){
			//	console.log('modal_cancer');
				//fileInputElement = $('#modal_cancer_voucher');
				fileInputElement = document.getElementById('modal_cancer_voucher');
			}
			if($('#modal_propina').is(':visible')){
			//	console.log('modal_propina');
				//fileInputElement = $('#modal_cancer_voucher');
				fileInputElement = document.getElementById('modal_propina_voucher');
			}
			if($('#sec_tlv_modal_devolucion_c7').is(':visible')){
			//	console.log('modal_propina');
				//fileInputElement = $('#modal_cancer_voucher');
				fileInputElement = document.getElementById('SecDevC7_input_file_voucher');
			}
			if(typeof fileInputElement !== 'undefined'){
				let container = new DataTransfer();
				let data = imageBlob;
				let img_nombre = new Date().getTime();
				let file = new File([data], img_nombre + ".jpg", {type: "image/jpeg", lastModified: img_nombre});
				container.items.add(file);
				fileInputElement.files = container.files;
				if($('#modal_deposito').is(':visible')){
					readImageDep(fileInputElement);
				}
			}
		}
	});
}, false);

function guardar_retiro() {
	$('#modal_retiro_btn_guardar').hide();
	var id_cuenta_usar_retiro = 0;
	var nom_cuenta_usar_retiro = "";
	var num_cuenta_usar_retiro = "";
	var num_cci_cuenta_usar_retiro = "";
	var id_banco_cuenta_usar_retiro = 0;
	var monto_minimo_retiro = 0;
	var valor;
	$('.sec_tlv_pag_sec_cuentas_cliente:checked').each(function (indice, elemento) {
		var fila = $(this).parents(".listado_cuentas_bancarias_clientes");
		id_cuenta_usar_retiro = fila.find(".sec_tlv_id_cuenta_cliente").val();
		nom_cuenta_usar_retiro = fila.find(".sec_tlv_nom_cuenta_cliente").html();
		num_cuenta_usar_retiro = fila.find(".sec_tlv_num_cuenta_cliente").html();
		num_cci_cuenta_usar_retiro = fila.find(".sec_tlv_num_cci_cuenta_cliente").html();
		id_banco_cuenta_usar_retiro = fila.find(".sec_tlv_id_banco_cuenta_cliente").val();
		valor = $(this).val();
	});

	var monto_disponible_retiro_cliente = parseFloat($('#sec_tlv_retiro_disponible').html().replace(/\,/g, '')).toFixed(2);
	var monto_solicitud = parseFloat($('#modal_retiro_monto').val().replace(/\,/g, '')).toFixed(2);
	var razon = $('#sec_tlv_select_operacion').val();
	var tipo_operacion = $('#sec_tlv_modal_retiro_tipo_transaccion').val();
	var motivo_devolucion = $('#sec_tlv_select_motivo_devolucion').val();

	if (parseFloat(monto_disponible_retiro_cliente) <= 0) {
		swal('Aviso', 'No tiene saldo disponible para realizar la operación.', 'warning');
		$('#modal_retiro_btn_guardar').show();
		return false;
	}

	if (parseFloat(monto_solicitud) > parseFloat(monto_disponible_retiro_cliente)) {
		swal('Aviso', 'El monto no puede ser mayor al monto disponible.', 'warning');
		$('#modal_retiro_btn_guardar').show();
		return false;
	}

	if (valor == undefined || $.trim(valor) == "") {
		swal('Aviso', 'Debe seleccionar la cuenta a utilizar', 'warning');
		$('#modal_retiro_btn_guardar').show();
		return false;
	}

	if (parseFloat(monto_solicitud) == 0) {
		swal('Aviso', 'Debe ingresar el monto de la solicitud.', 'warning');
		$('#modal_retiro_btn_guardar').show();
		return false;
	}

	if (monto_solicitud < monto_minimo_retiro) {
		swal('Aviso', 'El monto mínimo es: ' + monto_minimo_retiro.toFixed(2) + ' Soles.', 'warning');
		$('#modal_retiro_btn_guardar').show();
		return false;
	}

	if(tipo_operacion == 2){
		if(motivo_devolucion == 0){
			swal('Aviso', 'Debe seleccionar el motivo de devolución.', 'warning');
			$('#modal_retiro_btn_guardar').show();
			return false;
		}
	}

	var link_atencion = $('#sec_tlv_input_link_atencion').val();
	if($.trim(link_atencion) == ""){
		swal('Aviso', 'Ingrese el link de atención', 'warning');
		$('#modal_retiro_btn_guardar').show();
		return false;
	}


	//************************
	// GUARDAR SOLICITUD RETIRO
	//************************
	var data = new FormData();
	data.append('accion', "guardar_transaccion_solicitud_retiro");
	data.append('id_banco_cuenta_usar_retiro', id_banco_cuenta_usar_retiro);
	data.append('id_cuenta_usar_retiro', id_cuenta_usar_retiro);
	data.append('cliente_id', gen_cliente_id);
	data.append('monto_solicitud', monto_solicitud);
	data.append('razon', razon);
	data.append('tipo', tipo_operacion);
	data.append('motivo_devolucion', motivo_devolucion);
	data.append('local_test', gen_etiqueta_local_test);
	data.append('link_atencion', link_atencion);

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "guardar_transaccion_solicitud_retiro", "data": respuesta});
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_retiro').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_retiro').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "guardar_transaccion_solicitud_retiro_error", "data": result});
			return false;
		}
	});
	return false;
}

function abrir_modal_cancelar_retiro(trans_id, tipo_operacion) {
	$("#sec_tlv_select_motivo_cancelacion").val('0');
	$('textarea#sec_tlv_text_observacion').val('');
	$('#sec_tlv_modal_btn_cancelar_solicitud').removeAttr("onclick");
	$('#sec_tlv_modal_btn_cancelar_solicitud').attr("onclick", 'sec_tlv_cancelar_retiro("' + trans_id + '", "' + tipo_operacion + '")');
	$('#sec_tlv_modal_btn_cancelar_solicitud').show();
	$('#sec_tlv_modal_cancelar_retiro').modal('show');
}

function sec_tlv_cancelar_retiro(trans_id, tipo_operacion) {
	console.log('retiro cancelado');
	$('#sec_tlv_modal_btn_cancelar_solicitud').hide();
	var motivo_cancelacion = $('#sec_tlv_select_motivo_cancelacion').val();
	var observacion_supervisor = $('textarea#sec_tlv_text_observacion').val();
	if (motivo_cancelacion == 0) {
		swal({
			title: "Seleccionar Motivo",
			text: "Debe seleccionar el motivo de la cancelación.",
			type: 'info',
			timer: 5000,
			showConfirmButton: true
		});
		$('#sec_tlv_modal_btn_cancelar_solicitud').show();
		return false;
	}
	var mensaje = "";
	mensaje = "Se canceló la solicitud exitosamente."
	$.post("/sys/set_televentas.php", {
		accion: "cancelar_solicitud_retiro",
		cliente_id: gen_cliente_id,
		trans_id: trans_id,
		observacion: observacion_supervisor,
		motivo_cancelacion: motivo_cancelacion,
		tipo_operacion : tipo_operacion
	})
			.done(function (data) {
				try {
					var respuesta = JSON.parse(data);
					//console.log(respuesta.query_update);
					$('#sec_tlv_modal_cancelar_retiro').modal('hide');
					if (parseInt(respuesta.http_code) == 400) {
						swal({
							title: 'Error',
							text: respuesta.status,
							type: 'error',
							timer: 1500,
							showConfirmButton: false
						});
						return false;
					}
					if (parseInt(respuesta.http_code) == 200) {
						swal({
							title: mensaje,
							text: '',
							type: 'success',
							timer: 1500,
							showConfirmButton: false
						});
						listar_transacciones(gen_cliente_id);
						return false;
					}
					return false;
				} catch (e) {
					swal('¡Error!', e, 'error');
					// console.log("Error de TRY-CATCH --> Error: " + e);
					return false;
				}
			})
			.fail(function (xhr, status, error) {
				$('#sec_tlv_modal_cancelar_retiro').modal('hide');
				swal('¡Error!', error, 'error');
				// console.log("Error de .FAIL -- Error: " + error);
				return false;
			});
	return false;
}

function abrir_modal_cancelar_propina(trans_id, tipo_operacion) {
	$("#sec_tlv_select_motivo_cancelacion_propina").val('0');
	$('textarea#sec_tlv_text_observacion_propina').val('');
	$('#sec_tlv_modal_btn_cancelar_solicitud_propina').removeAttr("onclick");
	$('#sec_tlv_modal_btn_cancelar_solicitud_propina').attr("onclick", 'sec_tlv_cancelar_propina("' + trans_id + '", "' + tipo_operacion + '")');
	$('#sec_tlv_modal_btn_cancelar_solicitud_propina').show();
	$('#sec_tlv_modal_cancelar_propina').modal('show');
}

function sec_tlv_cancelar_propina(trans_id, tipo_operacion) {
	$('#sec_tlv_modal_btn_cancelar_solicitud_propina').hide();
	var motivo_cancelacion = $('#sec_tlv_select_motivo_cancelacion_propina').val();
	var observacion_supervisor = $('textarea#sec_tlv_text_observacion_propina').val();
	if (motivo_cancelacion == 0) {
		swal({
			title: "Seleccionar Motivo",
			text: "Debe seleccionar el motivo de la cancelación.",
			type: 'info',
			timer: 5000,
			showConfirmButton: true
		});
		$('#sec_tlv_modal_btn_cancelar_solicitud_propina').show();
		return false;
	}
	var mensaje = "";
	mensaje = "Se canceló la solicitud exitosamente."
	$.post("/sys/set_televentas.php", {
		accion: "cancelar_solicitud_propina",
		cliente_id: gen_cliente_id,
		trans_id: trans_id,
		observacion: observacion_supervisor,
		motivo_cancelacion: motivo_cancelacion,
		tipo_operacion : tipo_operacion
	})
			.done(function (data) {
				try {
					var respuesta = JSON.parse(data);
					$('#sec_tlv_modal_cancelar_propina').modal('hide');
					//console.log(respuesta.query_update);
					if (parseInt(respuesta.http_code) == 400) {
						swal({
							title: 'Error',
							text: respuesta.status,
							type: 'error',
							timer: 1500,
							showConfirmButton: false
						});
						return false;
					}
					if (parseInt(respuesta.http_code) == 200) {
						swal({
							title: mensaje,
							text: '',
							type: 'success',
							timer: 1500,
							showConfirmButton: false
						});
						listar_transacciones(gen_cliente_id);
						return false;
					}
					return false;
				} catch (e) {
					swal('¡Error!', e, 'error');
				//	console.log("Error de TRY-CATCH --> Error: " + e);
					return false;
				}
			})
			.fail(function (xhr, status, error) {
				$('#sec_tlv_modal_cancelar_propina').modal('hide');
				swal('¡Error!', error, 'error');
				// console.log("Error de .FAIL -- Error: " + error);
				return false;
			});
	return false;
}



$(document).ready(function () {
	$("#modal_cuentas_banco").change(function () {
		$("#modal_cuentas_cuenta_num").val('');
		var id = $(this).val();
		var maxl = 25;

		switch (parseInt(id)) {
			case 11:
				maxl = 14;
				break;

			case 12:
				maxl = 19;
				break;

			case 13:
				maxl = 13;
				break;

			case 14:
				maxl = 10;
				break;

			case 53:
				maxl = 20;
				break;
			default:
				maxl = 25;
				break;
		}
		$("#modal_cuentas_cuenta_num").attr('maxlength', maxl);
	});
});

function sec_tlv_estado_cuenta() {
	$('#sec_tlv_modal_estado_cuenta').modal('show');
	var balance_total = $('#span_balance').html();
	var retiro_disponible = $('#span_balance_retiro_disponible').html();
	$('#sec_tlv_ec_cliente_name').html(gen_cliente_nombres);
	$('#sec_tlv_ec_balance_total').html('S/ ' + balance_total);
	$('#sec_tlv_ec_retiro_disponible').html('S/ ' + retiro_disponible);
	var data = {
		accion: "obtener_ultimas_transacciones",
		cliente_id: gen_cliente_id,
		limit: 3
	}
	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) { //  alert(datat)
			//console.log(resp);
			$('#sec_tlv_ec_div_ultimas_transacciones').html('');
			var respuesta = JSON.parse(resp);
			auditoria_send({"proceso": "obtener_ultimas_transacciones", "data": respuesta});
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_retiro_cuenta').append('<option value="0">:: Seleccione ::</option>');
				$('#modal_retiro_cuenta').val('0');

				$.each(respuesta.result, function (index, item) {
					$('#sec_tlv_ec_div_ultimas_transacciones').append(
							'<label style="cursor: text;">' + item.fecha_hora + ' - ' + item.nombre + ' - S/ ' + item.monto + '</label><br>'
							);
				});
			} else {
				$('#sec_tlv_ec_div_ultimas_transacciones').append(
						'<label>No tiene transacciones</label><br>'
						);
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_ultimas_transacciones_error", "data": result});
			return false;
		}
	});
}

if (sec_id === 'televentas' || sec_id === 'retail_altenar') {
	/*let copyToClipboardButton = document.getElementById('sec_tlv_btn_copiar_imagen');
	 copyToClipboardButton.addEventListener('click', () => {
	 let imgToCopy = document.getElementById('sec_tlv_imagen_comprobante_pago');
	 let canvas = document.createElement('canvas');
	 canvas.width = imgToCopy.clientWidth;
	 canvas.height = imgToCopy.clientHeight;
	 let context = canvas.getContext('2d');
	 context.drawImage(imgToCopy, 0, 0);
	 
	 canvas.toBlob(function (blob) {
	 
	 let data = [new ClipboardItem({ [blob.type]: blob })];
	 
	 if (navigator.clipboard) {
	 navigator.clipboard.write(data).then(function () {
	 console.log('done')
	 }, function (err) {
	 console.log('error')
	 });
	 } else {
	 console.log('Browser do not support Clipboard API')
	 }
	 
	 }, 'image/png');
	 
	 sec_tlv_estado_enviado_comprobante();
	 
	 })*/
}

$('#sec_tlv_btn_copiar_imagen').click(function () {
	writeClipImg($('#sec_tlv_btn_copiar_imagen'));
	sec_tlv_estado_enviado_comprobante();
});

$('#sec_tlv_btn_copiar_imagen_propina').click(function () {
	writeClipImg_propina($('#sec_tlv_btn_copiar_imagen_propina'));
	sec_tlv_estado_enviado_comprobante_propina();
});

async function writeClipImg(element) {
	/*try {
	 //const imgURL = 'http://localhost/files_bucket/retiros/1282_20220624192355.jpeg';
	 const imgURL = $('#sec_tlv_imagen_comprobante_pago').attr('src');
	 console.log(imgURL);
	 const data = await fetch(imgURL);
	 const blob = await data.blob();
	 console.log(blob.type);
	 
	 await navigator.clipboard.write([
	 new ClipboardItem({
	 [blob.type]: blob
	 })
	 ]);
	 console.log('Fetched image copied.');
	 } catch(err) {
	 console.error(err.name, err.message);
	 }*/

	/*window.getSelection().removeAllRanges();
	 let range = document.createRange();
	 range.selectNode(typeof element);
	 window.getSelection().addRange(range);
	 document.execCommand('copy');
	 window.getSelection().removeAllRanges();*/

	if (!('ClipboardItem' in window)) {
		return alert(
				"Your browser doesn't support copying images into the clipboard."
				+ " If you use Firefox you can enable it"
				+ " by setting dom.events.asyncClipboard.clipboardItem to true."
				)
	}
	try {
		const imgURL = document.getElementById('sec_tlv_imagen_comprobante_pago').src;
		const data = await fetch(imgURL);
		const blob = await data.blob();
		await navigator.clipboard.write([
			new ClipboardItem({
				[blob.type]: blob
			})
		]);
		// console.log('Fetched image copied.');
	} catch (err) {
	//	console.error(err.name, err.message);
	}
}

function sec_tlv_estado_enviado_comprobante() {
	var id_trans = $("#sec_tlv_id_trans_retiro").val();
	var data = {
		"accion": "sec_tlv_cambiar_estado_enviar_comprobante",
		"id_transaccion": id_trans
	}
	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,

		success: function (resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({"proceso": "sec_tlv_cambiar_estado_enviar_comprobante", "data": respuesta});
			if (parseInt(respuesta.http_code) == 200) {
			}
		},
		error: function () {
			auditoria_send({"proceso": "sec_tlv_cambiar_estado_enviar_comprobante_error", "data": respuesta});
		}
	});
}

async function writeClipImg_propina(element) {

	if (!('ClipboardItem' in window)) {
		return alert(
				"Your browser doesn't support copying images into the clipboard."
				+ " If you use Firefox you can enable it"
				+ " by setting dom.events.asyncClipboard.clipboardItem to true."
				)
	}
	try {
		const imgURL = document.getElementById('sec_tlv_imagen_comprobante_pago_propina').src;
		const data = await fetch(imgURL);
		const blob = await data.blob();
		await navigator.clipboard.write([
			new ClipboardItem({
				[blob.type]: blob
			})
		]);
	//	console.log('Fetched image copied.');
	} catch (err) {
		console.error(err.name, err.message);
	}
}

function sec_tlv_estado_enviado_comprobante_propina() {
	var id_trans = $("#sec_tlv_id_trans_propina").val();
	var data = {
		"accion": "sec_tlv_cambiar_estado_enviar_comprobante",
		"id_transaccion": id_trans
	}
	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,

		success: function (resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({"proceso": "sec_tlv_cambiar_estado_enviar_comprobante", "data": respuesta});
			if (parseInt(respuesta.http_code) == 200) {
			}
		},
		error: function () {
			auditoria_send({"proceso": "sec_tlv_cambiar_estado_enviar_comprobante_error", "data": respuesta});
		}
	});
}

$('#sec_tlv_copiar_voucher_apuesta_pagada').click(function () {
	html2canvas(document.querySelector("#sec_tlv_content_modal_voucher_apuesta_altenar"), {allowTaint: true, useCORS: true}).then(canvas => {
		//document.querySelector("#modal_voucher_apuesta_altenar").appendChild(canvas);
		$("#sec_tlv_div_img_voucher_apuesta").remove();

		var img = document.createElement('img');
		img.setAttribute("id", "sec_tlv_img_voucher_apuesta");
		img.setAttribute("crossorigin", "anonymous");
		img.style.display = "none";
		img.src = canvas.toDataURL();

		var div = document.createElement('div');
		div.setAttribute("id", "sec_tlv_div_img_voucher_apuesta");
		div.contentEditable = true;
		div.appendChild(img);
		div.style.display = "none";
		document.body.appendChild(div);

		writeClipImgApuesta();
	});
});

$('#sec_tlv_pdf_voucher_apuesta_pagada').click(function () {

	var date = new Date(); 
    var mes = date.getMonth() + 1;
    var dia = date.getDate();

    var txn_id = $('#modal_voucher_apuesta_altenar_qr_id_bet').html();
    var name_file = txn_id + '_' + dia + mes;

    let body = document.body;
    let html = document.documentElement;    
    let height = Math.max(body.scrollHeight, body.offsetHeight,html.clientHeight, html.scrollHeight, html.offsetHeight);
    let element = document.querySelector('#sec_tlv_content_modal_voucher_apuesta_altenar');
    let heightCM = height / 50.35;
    let heightE = Math.max(element.scrollHeight, element.offsetHeight,element.clientHeight, element.scrollHeight, element.offsetHeight) / 35.35;
    html2pdf(element, {
        margin: 1,
        filename: name_file,
        html2canvas: { dpi: 192, letterRendering: true },
        jsPDF: {
            orientation: 'portrait',
            unit: 'cm',
            format: [heightCM, heightE + 5]
        }
    });
});

async function writeClipImgApuesta() {
	if (!('ClipboardItem' in window)) {
		return alert(
				"Your browser doesn't support copying images into the clipboard."
				+ " If you use Firefox you can enable it"
				+ " by setting dom.events.asyncClipboard.clipboardItem to true."
				)
	}

	try {
		const imgURL = document.getElementById('sec_tlv_img_voucher_apuesta').src;
		const data = await fetch(imgURL);
		const blob = await data.blob();
		await navigator.clipboard.write([
			new ClipboardItem({
				[blob.type]: blob
			})
		]);
		//console.log('Fetched image copied.');
		alertify.success('Voucher copiado correctamente', 3);
	} catch (err) {
		console.error(err.name, err.message);
	}
}

function terminal_deposit() {
	$('#sec_tlv_modal_td_num_documento').val('');
	$('#modal_terminal_deposit').modal();
}

$('#modal_termdep_btn_consultar').click(function () {
	try {
		var cod = $('#sec_tlv_modal_td_num_documento').val();
		if ($.trim(cod) == '') {
			swal('Aviso', 'Debe ingresar el número de documento', 'warning');
			return false;
		}
		var web_id = 0;
		$.each(array_clientes, function (index, item) {
			if (parseInt(item.id) === parseInt(gen_cliente_id)) {
				if (parseInt(item.web_id) > 0) {
					web_id = item.web_id;
				}
			}
		});

		if (gen_cliente_id != 0) {
			swal({
				html: true,
				title: '¿Desea subir el saldo al billetero del cliente? Esta acción es irreversible.',
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#0336FF',
				cancelButtonColor: '#d33',
				confirmButtonText: 'SI, CONSULTAR',
				cancelButtonText: 'CANCELAR',
				closeOnConfirm: false,
				//,showLoaderOnConfirm: true
			}, function () {
				var data = {
					"accion": "consultar_terminal_deposit",
					"cod": cod,
					"id_cliente": gen_cliente_id,
					"web_id": web_id
				}
				$.ajax({
					url: "/sys/set_televentas.php",
					type: 'POST',
					data: data,
					beforeSend: function () {
						loading("true");
					},
					complete: function () {
						loading();
					},
					success: function (resp) {
						//console.log(resp);
						var respuesta = JSON.parse(resp);
						auditoria_send({"proceso": "consultar_terminal_deposit", "data": respuesta});
						//console.log(respuesta);
						if (parseInt(respuesta.http_code) == 400) {
							$('#modal_terminal_deposit').modal('hide');
							swal('Aviso', respuesta.status, 'warning');
							listar_transacciones(gen_cliente_id);
							return false;
						}
						if (parseInt(respuesta.http_code) == 200) {
							$('#modal_terminal_deposit').modal('hide');
							swal('Aviso', 'Acción realizada con éxito.', 'success');
							listar_transacciones(gen_cliente_id);
							return false;
						}
						return false;
					},
					error: function (result) {
						auditoria_send({"proceso": "consultar_terminal_deposit_error", "data": result});
						return false;
					}
				});
				return false;
			});
		} else {

		}
	} catch (e) {
		swal('¡Error!', e, 'error');
	//	console.log("Error de TRY-CATCH --> Error: " + e);
		return false;
	}
});

function nueva_recargaweb_2() {
    $('#cliente_idweb_div').css('border', '');
    limpiar_campos_modal_recargaweb2();
    $('#sec_tlv_modal_recargaweb2_deposito_list_error').hide();
    $('#sec_tlv_modal_recargaweb2_deposito_list_error_texto').html('');

    var web_id=0;
    $.each(array_clientes, function(index, item) {
        if (parseInt(item.id) === parseInt(gen_cliente_id)) {
            if (parseInt(item.web_id) > 0) {
                web_id=item.web_id;
                $('#sec_tlv_modal_recargaweb2_idweb').val(item.web_id);
            }
        }
    });
    if(!(parseInt(web_id)>0)){
        $('#cliente_idweb_div').css('border', '1px solid red');
        $('#cliente_idweb').focus();
        $('#sec_tlv_modal_recarga_web_calimaco').modal('hide');
        swal('Aviso', 'Debe guardar el ID-WEB en la sección de información del cliente.', 'warning');
        return false;
    }

    var dt_ae = {
		"accion" : "sec_tlv_get_info_autoexclusion_by_webid",
		"web_id" : web_id
	}

	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: dt_ae,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({"proceso": "sec_tlv_get_info_autoexclusion_by_webid", "data": respuesta});
			if (parseInt(respuesta.result.http_code) == 400) {
				var temp_gen_balance_total=gen_balance_total.replace(/\D/g, "")
                                    .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
			    $('#sec_tlv_modal_recargaweb2_balance').val(temp_gen_balance_total);
			    //$('#modal_recargaweb_monto').val(temp_gen_balance_total);
			    var temp_gen_balance_bono_disponible=gen_balance_bono_disponible.replace(/\D/g, "")
			                                    .replace(/([0-9])([0-9]{2})$/, '$1.$2')
			                                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
			    $('#sec_tlv_modal_recargaweb2_balance_bono_disponible').val(temp_gen_balance_bono_disponible);
			    //$('#modal_recargaweb_bono').val(temp_gen_balance_bono_disponible);


			    $.post("/sys/set_televentas.php", {
			            accion: "obtener_depositos_disponibles",
			            id_cliente: gen_cliente_id
			        })
			    .done(function (data) {
			        try {
			            //console.log(data);
			            var respuesta = JSON.parse(data);
			            if (parseInt(respuesta.http_code) == 200) {
			                if (respuesta.result.length > 0) {
			                    $('#sec_tlv_modal_recargaweb2_deposito_list').append('<option value="0">Seleccione</option>');
			                    $.each(respuesta.result, function(index, item) {
			                        if(parseFloat(item.bono_monto)>0) {
			                            $('#sec_tlv_modal_recargaweb2_deposito_list').append('<option '+
			                                'value="'+item.codigo+'"'+
			                                'monto="'+item.monto+'"'+
			                                'bono_monto="'+item.bono_monto+'"'+
			                                '>'+
			                                'S/. '+item.monto+' | '+item.bono_nombre+' - Máx. S/. '+item.bono_monto+
			                                '</option>');
			                        } else {
			                            $('#sec_tlv_modal_recargaweb2_deposito_list').append('<option value="'+item.codigo+'">'+
			                                'S/. '+item.monto+' - Sin Bono'+
			                                '</option>');
			                            $('#sec_tlv_modal_recargaweb2_monto').val(temp_gen_balance_total);
			                        }
			                    });
			                } else {
			                    $('#sec_tlv_modal_recargaweb2_deposito_list').append('<option value="0">Sin depósitos pendientes</option>');
			                    $('#sec_tlv_modal_recargaweb2_monto').val(temp_gen_balance_total);
			                }
			            } else {
			                $('#sec_tlv_modal_recargaweb2_deposito_list').append('<option value="0">Sin depósitos pendientes</option>');
			                $('#sec_tlv_modal_recargaweb2_monto').val(temp_gen_balance_total);
			            }
			            $("#sec_tlv_modal_recargaweb2_deposito_list").val('0');
			        } catch (e) {
			            swal('¡Error!', e, 'error');
			          //  console.log("Error de TRY-CATCH --> Error: " + e);
			        }
			    })
			    .fail(function (xhr, status, error) {
			        swal('¡Error!', error, 'error');
			    });
			    $("#sec_tlv_modal_recargaweb2_deposito_list").val('0');

			    calcular_total_modal_recargaweb2();
			    $('#modal_recargaweb2_btn_guardar').show();
			    $('#sec_tlv_modal_recarga_web_calimaco').modal();
			    return false;
			}
			if (parseInt(respuesta.result.http_code) == 200) {
				var init_date = "";
				var end_date = "";
				$.each(respuesta.result.result, function(index, item){
					init_date = item.init_date;
					end_date = item.end_date;
				});
				swal('Cliente autoexcluído', 'El cliente está autoexcluído hasta ' + end_date, 'warning');
				return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "sec_tlv_get_info_autoexclusion_by_webid_error", "data": result});
			return false;
		}
	});
}

function limpiar_campos_modal_recargaweb2() {
    $('#sec_tlv_modal_recargaweb2_idweb').val('');
    $('#sec_tlv_modal_recargaweb2_idweb_c').val('');
    $('#sec_tlv_modal_recargaweb2_balance').val('');
    $('#sec_tlv_modal_recargaweb2_balance_bono_disponible').val('');
    $('#sec_tlv_modal_recargaweb2_monto').val('0.00');
    $('#sec_tlv_modal_recargaweb2_bono').val('0.00');
    $('#sec_tlv_modal_recargaweb2_total').val('0.00');
    $('#sec_tlv_modal_recargaweb2_deposito_list').html('');
    $('#sec_tlv_modal_recargaweb2_deposito_list_error').hide();
    $('#sec_tlv_modal_recargaweb2_deposito_list_error_texto').html('');
    limpiar_bordes_modal_recargaweb2();
}
function limpiar_bordes_modal_recargaweb2() {
    $('#sec_tlv_modal_recargaweb2_idweb_c').css('border', '');
    $('#sec_tlv_modal_recargaweb2_monto').css('border', '');
}

function calcular_total_modal_recargaweb2(){
    $('#sec_tlv_modal_recargaweb2_deposito_list_error').hide();
    $('#sec_tlv_modal_recargaweb2_deposito_list_error_texto').html('');
    var temp_monto = parseFloat($('#sec_tlv_modal_recargaweb2_monto').val().replace(/\,/g, '')).toFixed(2);
    var temp_bono_id = $("#sec_tlv_modal_recargaweb2_deposito_list").val();
    var temp_bono_maximo = $('option:selected', '#sec_tlv_modal_recargaweb2_deposito_list').attr('bono_monto');
    var temp_bono = 0;

    if (!(parseFloat(temp_monto)>=0)){
        temp_monto=0;
    }
    if (parseFloat(temp_monto)>=40 && parseInt(temp_bono_id)>0){
        temp_bono = (parseFloat(temp_monto) * 0.05).toFixed(2);
    }
    if (parseFloat(temp_bono)>parseFloat(temp_bono_maximo)){
        temp_bono = parseFloat(temp_bono_maximo).toFixed(2);
    }
    //console.log('temp_bono_id: '+temp_bono_id);
    //console.log('temp_monto: '+temp_monto);
    if(parseFloat(temp_monto)<40 && parseInt(temp_bono_id)>0){
        $('#sec_tlv_modal_recargaweb2_deposito_list_error').show();
        $('#sec_tlv_modal_recargaweb2_deposito_list_error_texto').html('El monto de la recarga debe ser mayor o igual a 40 soles para asignar un bono.');
    }

    var total=(parseFloat(temp_monto) + parseFloat(temp_bono)).toFixed(2);
    total=total.toString().replace(/\D/g, "")
            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    $('#sec_tlv_modal_recargaweb2_total').val(total);

    temp_bono=temp_bono.toString().replace(/\D/g, "")
            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    $('#sec_tlv_modal_recargaweb2_bono').val(temp_bono);
    return false;
}

function guardar_recargaweb2() {
    $('#modal_recargaweb2_btn_guardar').hide();
    calcular_total_modal_recargaweb2();
    
    limpiar_bordes_modal_recargaweb2();

    var idweb_c = $('#sec_tlv_modal_recargaweb2_idweb_c').val();
    var idweb = $('#sec_tlv_modal_recargaweb2_idweb').val();
    var monto = $('#sec_tlv_modal_recargaweb2_monto').val().replace(/\,/g, '');
    var bono = $('#sec_tlv_modal_recargaweb2_bono').val().replace(/\,/g, '');
    var total = $('#sec_tlv_modal_recargaweb2_total').val().replace(/\,/g, '');
    var id_deposito = $('option:selected', '#sec_tlv_modal_recargaweb2_deposito_list').val();

    if (!(parseInt(idweb) > 0)) {
        $('#sec_tlv_modal_recargaweb2_idweb_c').css('border', '1px solid red');
        $('#sec_tlv_modal_recargaweb2_idweb_c').focus();
        $('#modal_recargaweb2_btn_guardar').show();
        return false;
    }
    if (!(parseFloat(monto) > 0)) {
        $('#sec_tlv_modal_recargaweb2_monto').css('border', '1px solid red');
        $('#sec_tlv_modal_recargaweb2_monto').focus();
        $('#modal_recargaweb2_btn_guardar').show();
        return false;
    }
    if (!(parseFloat(monto) >= 2.00 && parseFloat(monto) <= 50000.00)) {
        $('#sec_tlv_modal_recargaweb2_monto').css('border', '1px solid red');
        $('#sec_tlv_modal_recargaweb2_monto').focus();
        $('#modal_recargaweb2_btn_guardar').show();
        swal('Aviso', 'El monto debe ser mínimo de 2.00 y máximo de 50,000.00.', 'warning');
        return false;
    }
    if (!(parseFloat(bono) >= 0)) {
        bono=0;
    }
    if(parseFloat(bono) > parseFloat(monto)){
        swal('Aviso', 'El bono no puede ser mayor que el balance.', 'warning');
        $('#modal_recargaweb2_btn_guardar').show();
        return false;
    }
    if(parseFloat(gen_balance_total) < parseFloat(monto)){
        swal('Aviso', 'El balance actual es menor al monto a recargar.', 'warning');
        $('#modal_recargaweb2_btn_guardar').show();
        return false;
    }
    if(parseFloat(gen_balance_bono_disponible) < parseFloat(bono)){
        swal('Aviso', 'El balance del bono disponible actual es menor al bono a recargar.', 'warning');
        $('#modal_recargaweb2_btn_guardar').show();
        return false;
    }

    var data = {
        "accion": "guardar_transaccion_recarga_web2",
        "id_cliente": gen_cliente_id,
        "idweb": idweb,
        "idweb_c": idweb_c,
        "monto": monto,
        "bono": bono,
        "id_deposito": id_deposito,
        "timestamp" : Date.now()
    }

    if(parseFloat(gen_balance_total) > parseFloat(monto)){
        swal({
            html:true,
            title: '¿Está seguro de realizar la recarga con un <span style="font-weight: 900;color: black;text-transform: uppercase;">MONTO MENOR AL BALANCE ACTUAL</span>?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0336FF',
            cancelButtonColor: '#d33',
            confirmButtonText: 'SI, CONFIRMAR',
            cancelButtonText: 'CANCELAR',
            closeOnConfirm: false,
            //,showLoaderOnConfirm: true
        }, function(){
            guardar_recarga_confirmar_calimaco(data);
            return false;
        });
        $('#modal_recargaweb2_btn_guardar').show();
    } else {
        guardar_recarga_confirmar_calimaco(data);
        return false;
    }
    return false;
}

function guardar_recarga_confirmar_calimaco(data) {
    $.ajax({
        url: "/sys/set_televentas.php",
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
            $('#sec_tlv_modal_recarga_web_calimaco').modal('hide');
            auditoria_send({"proceso": "guardar_transaccion_recarga_web2", "data": respuesta});
            if (parseInt(respuesta.http_code) == 200) {
                swal('Aviso', 'Recarga realizada con éxito.', 'success');
            }else{
				var mensaje = '';
                if (respuesta.calimaco && respuesta.calimaco.description == 'Not privileged') {
					mensaje = 'Cuenta cerrada, el cliente debe contactarse con atencion al cliente.';
				} else if (respuesta.calimaco && respuesta.calimaco.description) {
					mensaje = respuesta.calimaco.description;
				} else {
					mensaje = 'No se pudo realizar la recarga';
				}
				swal(respuesta.result, mensaje, 'warning');
            }
            listar_transacciones(gen_cliente_id);
            return false;
        },
        error: function (result) {
            $('#sec_tlv_modal_recarga_web_calimaco').modal('hide');
            auditoria_send({"proceso": "guardar_transaccion_recarga_web2_error", "data": result});
            swal('Aviso', 'Error, por favor comunicarse con Informática.', 'warning');
            listar_transacciones(gen_cliente_id);
            return false;
        }
    });
    return false;
}

function add_bingo_venta(){
    $('#sec_tlv_modal_bingo_venta_num_ticket').val('');
    $('#sec_tlv_modal_bingo_venta_balance').html('Balance Actual: S/ ' + gen_balance_total);
    $('#sec_tlv_modal_bingo_venta_table').html(
        '<thead>' +
        '<tr>' +
        '<td class="text-center" >#</td>' +
        '<td class="text-center" >Ticket</td>' +
        '<td class="text-center" >Monto</td>' +
        '<td class="text-center" >Estado</td>' +
        '<td class="text-center" >X</td>' +
        '</tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#sec_tlv_modal_bingo_venta_guardar').show();
    $('#sec_tlv_modal_bingo_venta').modal('show');
}

function add_bingo_pago(){
    $('#sec_tlv_modal_bingo_pago_num_ticket').val('');
    $('#sec_tlv_modal_bingo_pago_balance').html('Balance Actual: S/ ' + gen_balance_total);
    $('#sec_tlv_modal_bingo_pago_table').html(
        '<thead>' +
        '<tr>' +
        '<td class="text-center" >#</td>' +
        '<td class="text-center" >Ticket</td>' +
        '<td class="text-center" >Monto</td>' +
        '<td class="text-center" >Estado</td>' +
        '<td class="text-center" >Jackpot</td>' +
        '<td class="text-center" >X</td>' +
        '</tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#sec_tlv_modal_bingo_pago_guardar').show();
    $('#sec_tlv_modal_bingo_pago').modal('show');
}
/**********************************************/
/**********************************************/
/**************VENTA DE BINGO******************/
/**********************************************/
/**********************************************/
$('#sec_tlv_modal_bingo_venta_btn_agregar').click(function () {
    sec_tlv_modal_registrar_bingo_venta();
    return false;
});
$('#sec_tlv_modal_bingo_venta').on('submit', function () {
    sec_tlv_modal_registrar_bingo_venta();
    return false;
});
function sec_tlv_modal_registrar_bingo_venta(){
    try {

		var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
        var id_ticket = $('#sec_tlv_modal_bingo_venta_num_ticket').val();
        if($.trim(id_ticket) == ''){
            //swal({ title: "Aviso", text: "Debe ingresar el número de ticket", type: 'info', timer: 1500, showConfirmButton: false });
            $('#sec_tlv_modal_bingo_venta_num_ticket').css('border', '1px solid red');
            $('#sec_tlv_modal_bingo_venta_num_ticket').focus();
            return false;
        }

        var data = new FormData();
        data.append('accion', "sec_tlv_registrar_bingo_venta_detalle");
        data.append('id_cliente', gen_cliente_id);
        data.append('balance_tipo', temp_balance_tipo);
        data.append('id_ticket', id_ticket);

        $.ajax({
            url: "/sys/set_televentas.php",
            type: 'POST',
            data: data,
            processData: false,
            cache: false,
            contentType: false,
            beforeSend: function() {
            },
            complete: function() {
            },
            success: function(resp) {
                var respuesta = JSON.parse(resp);
                if (parseInt(respuesta.http_code) === 200) {
                    var c = 1;
                    $.each(respuesta.result, function(index, item) {
                        var onclick_eliminar = "sec_tlv_modal_bingo_venta_eliminar_tr('" + item.ticket_id + "')";
                        $('#sec_tlv_modal_bingo_venta_num_ticket').val('');
                        $('#sec_tlv_modal_bingo_venta_table tbody').append(
                            '<tr id="' + item.ticket_id + '">' +
                            '   <td class="text-center">' + c + '</td>' +
                            '   <td class="text-center">' + item.ticket_id + '</td>' +
                            '   <td class="text-center">' + item.monto + '</td>' +
                            '   <td class="text-center">' + item.estado + '</td>' +
                            '   <td class="text-center">' +
                                '   <button type="button" class="btn btn-danger" style="padding: 2px 5px;"'+
                                '       onclick="' + onclick_eliminar + '">'+
                                '       <span class="fa fa-trash"></span>'+
                                '   </button>'+
                            '   </td>' +
                            '</tr>'
                        );
                        c = c + 1;
                    });
                    $('#sec_tlv_modal_bingo_venta_guardar').show();
                    sec_tlv_modal_bingo_venta_calcular_total();
                    return false;
                } else if (parseInt(respuesta.http_code) === 400) {
                	if(respuesta.soporte == 1){
                		swal({ html: true, title: "Aviso", text: respuesta.status + "<br><b> Contactar con SOPORTE</b>", type: 'info', timer: 5000, showConfirmButton: false });
                	}else{
                		swal({ title: "Aviso", text: respuesta.status, type: 'info', timer: 1500, showConfirmButton: false });	
                	}
                    return false;
                } else {
                    return false;
                }
                return false;
            },
            error: function(result) {
                return false;
            }
        });
        return false;
    } catch (e) {
        swal('¡Error!', e, 'error');
     //   console.log("Error de TRY-CATCH --> Error: " + e);
        return false;
    }
}

function sec_tlv_modal_bingo_venta_eliminar_tr(id_ticket){
    $('#sec_tlv_modal_bingo_venta_table tbody tr[id="' + id_ticket + '"]').remove();
    var sec_tlv_modal_bingo_tbl_cant = 0;
    $('#sec_tlv_modal_bingo_venta_table tbody tr').each(function () {
        sec_tlv_modal_bingo_tbl_cant++;
        $(this).find("td").eq(0).html(sec_tlv_modal_bingo_tbl_cant);
    });
    sec_tlv_modal_bingo_venta_calcular_total();
}

function sec_tlv_modal_bingo_venta_calcular_total(){
    var sec_tlv_modal_total_bingo_venta = 0;
    $('#sec_tlv_modal_bingo_venta_table tbody tr').each(function () {
        sec_tlv_modal_total_bingo_venta += parseFloat($(this).find("td").eq(2).html());
    });
    $('#sec_tlv_modal_bingo_venta_total').val(parseFloat(sec_tlv_modal_total_bingo_venta).toFixed(2));
}

$('#sec_tlv_modal_bingo_venta_guardar').click(function(){
    var tipo = $('#sec_tlv_modal_bingo_venta_id_tipo').html();
    sec_tlv_modal_bingo_venta_guardar(tipo);
});

function sec_tlv_modal_bingo_venta_guardar(tipo) {
    $('#sec_tlv_modal_bingo_venta_guardar').hide();
    var sec_tlv_modal_bingo_venta_total = $('#sec_tlv_modal_bingo_venta_total').val();

    var sec_tlv_modal_bingo_venta_tbl_array = '';
    var sec_tlv_modal_bingo_venta_tbl_array_montos = '';

    var sec_tlv_modal_bingo_venta_tbl_cant = 0;
    var sec_tlv_modal_bingo_venta_tbl_validacion_estado = 0;

    $('#sec_tlv_modal_bingo_venta_table tbody tr').each(function () {
        sec_tlv_modal_bingo_venta_tbl_cant++;

        var ticket_temp = $.trim($(this).find("td").eq(1).html());
        var monto_temp = $.trim($(this).find("td").eq(2).html());
        var estado_temp = $.trim($(this).find("td").eq(3).html()).toUpperCase();

        if(sec_tlv_modal_bingo_venta_tbl_array.length > 0) {
            sec_tlv_modal_bingo_venta_tbl_array += ",";
            sec_tlv_modal_bingo_venta_tbl_array_montos += ",";
        }
        sec_tlv_modal_bingo_venta_tbl_array += "'" + ticket_temp.toString() + "'";
        sec_tlv_modal_bingo_venta_tbl_array_montos += monto_temp.toString();

        if(estado_temp!=='PENDING'){
            //console.log('DIF. DE OK: '+estado_temp);
            sec_tlv_modal_bingo_venta_tbl_validacion_estado++;
        }
    });
    
    if (!(parseInt(sec_tlv_modal_bingo_venta_tbl_cant) > 0)) {
        swal('Aviso', 'Debe agregar al menos un ticket.', 'warning');
        $('#sec_tlv_modal_bingo_venta_num_ticket').css('border', '1px solid red');
        $('#sec_tlv_modal_bingo_venta_num_ticket').focus();
        $('#sec_tlv_modal_bingo_venta_guardar').show();
        return false;
    }
    /*if (parseInt(sec_tlv_modal_bingo_venta_tbl_validacion_estado) > 0) {
        swal('Aviso', 'Debe eliminar los tickets con estado diferente a PENDING para continuar.', 'warning');
        $('#sec_tlv_modal_bingo_venta_guardar').show();
        return false;
    }*/

    var data = new FormData();
    data.append('accion', "sec_tlv_registrar_bingo_venta");
    data.append('tipo', tipo);
    data.append('id_cliente', gen_cliente_id);
    data.append('cant_bet', sec_tlv_modal_bingo_venta_tbl_cant);
    data.append('array_ticket', sec_tlv_modal_bingo_venta_tbl_array);
    data.append('array_monto', sec_tlv_modal_bingo_venta_tbl_array_montos);
    data.append('total_bet', sec_tlv_modal_bingo_venta_total);

    $.ajax({
        url: "/sys/set_televentas.php",
        type: 'POST',
        data: data,
        processData: false,
        cache: false,
        contentType: false,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            var respuesta = JSON.parse(resp);
            auditoria_send({ "proceso": "sec_tlv_registrar_bingo_venta", "data": respuesta });
            $('#sec_tlv_modal_bingo_venta').modal('hide');
            if (parseInt(respuesta.http_code) === 200) {
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                listar_transacciones(gen_cliente_id);
                return false;
            } else if (parseInt(respuesta.http_code) === 400) {
                swal('Aviso', respuesta.status, 'warning');
                listar_transacciones(gen_cliente_id);
                return false;
            } else {
                swal('Aviso', 'Sin respuesta.', 'warning');
                return false;
            }
            return false;
        },
        error: function(result) {
            $('#sec_tlv_modal_bingo_venta_guardar').show();
            swal('Aviso', result, 'warning');
            auditoria_send({ "proceso": "sec_tlv_registrar_bingo_venta_error", "data": result });
            return false;
        }
    });
    return false;
}


/**********************************************/
/**********************************************/
/**************PAGO DE BINGO******************/
/**********************************************/
/**********************************************/
$('#sec_tlv_modal_bingo_pago_btn_agregar').click(function () {
    sec_tlv_modal_registrar_bingo_pago();
    return false;
});
$('#sec_tlv_modal_bingo_pago').on('submit', function () {
    sec_tlv_modal_registrar_bingo_pago();
    return false;
});
function sec_tlv_modal_registrar_bingo_pago(){
    try {
        var id_ticket = $('#sec_tlv_modal_bingo_pago_num_ticket').val();
        if($.trim(id_ticket) == ''){
            //swal({ title: "Aviso", text: "Debe ingresar el número de ticket", type: 'info', timer: 1500, showConfirmButton: false });
            $('#sec_tlv_modal_bingo_pago_num_ticket').css('border', '1px solid red');
            $('#sec_tlv_modal_bingo_pago_num_ticket').focus();
            return false;
        }

        var data = new FormData();
        data.append('accion', "sec_tlv_registrar_bingo_pago_detalle");
        data.append('id_cliente', gen_cliente_id);
        data.append('id_ticket', id_ticket);

        $.ajax({
            url: "/sys/set_televentas.php",
            type: 'POST',
            data: data,
            processData: false,
            cache: false,
            contentType: false,
            beforeSend: function() {
            },
            complete: function() {
            },
            success: function(resp) {
                var respuesta = JSON.parse(resp);
                if (parseInt(respuesta.http_code) === 200) {
                    var c = 1;
                    $.each(respuesta.result, function(index, item) {
                        var onclick_eliminar = "sec_tlv_modal_bingo_pago_eliminar_tr('" + item.ticket_id + "')";
                        $('#sec_tlv_modal_bingo_pago_num_ticket').val('');
                        $('#sec_tlv_modal_bingo_pago_table tbody').append(
                            '<tr id="' + item.ticket_id + '">' +
                            '   <td class="text-center">' + c + '</td>' +
                            '   <td class="text-center">' + item.ticket_id + '</td>' +
                            '   <td class="text-center">' + item.monto + '</td>' +
                            '   <td class="text-center">' + item.estado + '</td>' +
                            '   <td class="text-center">' + item.jackpot_amount + '</td>' +
                            '   <td class="text-center">' +
                                '   <button type="button" class="btn btn-danger" style="padding: 2px 5px;"'+
                                '       onclick="' + onclick_eliminar + '">'+
                                '       <span class="fa fa-trash"></span>'+
                                '   </button>'+
                            '   </td>' +
                            '</tr>'
                        );
                        c = c + 1;
                    });
                    sec_tlv_modal_bingo_pago_calcular_total();
                    return false;
                } else if (parseInt(respuesta.http_code) === 400) {
                    swal({ title: "Aviso", text: respuesta.status, type: 'info', timer: 1500, showConfirmButton: false });
                    return false;
                } else {
                    return false;
                }
                return false;
            },
            error: function(result) {
                return false;
            }
        });
        return false;
    } catch (e) {
        swal('¡Error!', e, 'error');
     //   console.log("Error de TRY-CATCH --> Error: " + e);
        return false;
    }
}

function sec_tlv_modal_bingo_pago_eliminar_tr(id_ticket){
    $('#sec_tlv_modal_bingo_pago_table tbody tr[id="' + id_ticket + '"]').remove();
    var sec_tlv_modal_bingo_tbl_cant = 0;
    $('#sec_tlv_modal_bingo_pago_table tbody tr').each(function () {
        sec_tlv_modal_bingo_tbl_cant++;
        $(this).find("td").eq(0).html(sec_tlv_modal_bingo_tbl_cant);
    });
    sec_tlv_modal_bingo_pago_calcular_total();
}

function sec_tlv_modal_bingo_pago_calcular_total(){
    var sec_tlv_modal_total_bingo_venta = 0;
    $('#sec_tlv_modal_bingo_pago_table tbody tr').each(function () {
        sec_tlv_modal_total_bingo_venta += parseFloat($(this).find("td").eq(2).html());
    });
    $('#sec_tlv_modal_bingo_pago_total').val(parseFloat(sec_tlv_modal_total_bingo_venta).toFixed(2));
}

$('#sec_tlv_modal_bingo_pago_guardar').click(function(){
    //var tipo = $('#sec_tlv_modal_bingo_pago_id_tipo').html();
    sec_tlv_modal_bingo_pago_guardar();
});

function sec_tlv_modal_bingo_pago_guardar() {
    $('#sec_tlv_modal_bingo_pago_guardar').hide();
    $('#sec_tlv_modal_bingo_pago_num_ticket').css('border', '');

    var sec_tlv_modal_bingo_pago_total = $('#sec_tlv_modal_bingo_pago_total').val();

    var sec_tlv_modal_bingo_pago_tbl_array = '';
    var sec_tlv_modal_bingo_pago_tbl_array_montos = '';

    var sec_tlv_modal_bingo_pago_tbl_cant = 0;
    var sec_tlv_modal_bingo_pago_tbl_validacion_estado = 0;

    $('#sec_tlv_modal_bingo_pago_table tbody tr').each(function () {
        sec_tlv_modal_bingo_pago_tbl_cant++;
        var ticket_temp = $.trim($(this).find("td").eq(1).html());
        var monto_temp = $.trim($(this).find("td").eq(2).html());
        var estado_temp = $.trim($(this).find("td").eq(3).html()).toUpperCase();

        if (sec_tlv_modal_bingo_pago_tbl_array.length > 0) {
            sec_tlv_modal_bingo_pago_tbl_array += ",";
            sec_tlv_modal_bingo_pago_tbl_array_montos += ",";
        }
        sec_tlv_modal_bingo_pago_tbl_array += "'" + ticket_temp.toString() + "'";
        sec_tlv_modal_bingo_pago_tbl_array_montos += monto_temp.toString();
        if (estado_temp !== 'PAGADO') {
            //console.log('DIF. DE OK: '+estado_temp);
            sec_tlv_modal_bingo_pago_tbl_validacion_estado++;
        }
    });

    if (!(parseInt(sec_tlv_modal_bingo_pago_tbl_cant) > 0)) {
        swal('Aviso', 'Debe agregar al menos un ticket.', 'warning');
        $('#sec_tlv_modal_bingo_pago_idbet').css('border', '1px solid red');
        $('#sec_tlv_modal_bingo_pago_idbet').focus();
        $('#sec_tlv_modal_bingo_pago_guardar').show();
        return false;
    }
    if (parseInt(sec_tlv_modal_bingo_pago_tbl_validacion_estado) > 0) {
        swal('Aviso', 'Solo se permiten tickets con estado GANADO', 'warning');
        $('#sec_tlv_modal_bingo_pago_guardar').show();
        return false;
    }
    
    var data = new FormData();
    data.append('accion', "sec_tlv_registrar_bingo_pago");
    data.append('id_cliente', gen_cliente_id);
    data.append('cant_bet', sec_tlv_modal_bingo_pago_tbl_cant);
    data.append('array_bet', sec_tlv_modal_bingo_pago_tbl_array);
    data.append('array_monto', sec_tlv_modal_bingo_pago_tbl_array_montos);
    data.append('total_bet', sec_tlv_modal_bingo_pago_total);

    $.ajax({
        url: "/sys/set_televentas.php",
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
            auditoria_send({"proceso": "sec_tlv_registrar_bingo_pago", "data": respuesta});
            $('#sec_tlv_modal_bingo_pago').modal('hide');
            if (parseInt(respuesta.http_code) === 200) {
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                listar_transacciones(gen_cliente_id);
                return false;
            } else if (parseInt(respuesta.http_code) === 400) {
                swal('Aviso', respuesta.status, 'warning');
                listar_transacciones(gen_cliente_id);
                return false;
            } else {
                swal('Aviso', 'Sin respuesta.', 'warning');
                return false;
            }
            return false;
        },
        error: function (result) {
            $('#sec_tlv_modal_bingo_pago_guardar').show();
            swal('Aviso', result, 'warning');
            auditoria_send({"proceso": "sec_tlv_registrar_bingo_pago_error", "data": result});
            return false;
        }
    });
    return false;
}

function limpiar_bordes_modal_deposito_c7() {
    $('#modal_deposito_c7_voucher_div').css('border', '');
    $('#modal_deposito_c7_tipo_contacto_div').css('border', '');
    $('#modal_deposito_c7_cuenta_div').css('border', '');
    $('#modal_deposito_c7_monto').css('border', '');
    $('#modal_deposito_c7_cuenta_div').css('border', '');
}
function limpiar_campos_modal_deposito_c7() {
    $('#modal_deposito_c7_voucher').val('');
    $('#modal_deposito_c7_tipo_contacto').val(1);
    $('#modal_deposito_c7_idweb').val('');
    $('#modal_deposito_c7_monto').val('');
    $('#modal_deposito_c7_monto_real').val('');
    $('#modal_deposito_c7_total').val('');
    $('textarea#modal_deposito_c7_observacion').val('');
    $('#modal_deposito_c7_cuenta').val(0).trigger('change.select2');
}

function nuevo_deposito_c7() {
    $('#cliente_idweb_div').css('border', '');

    var total_bono_mes = $('#span_bonos').html();
    var bono_limite_actual = $('#bono_limite').val();
    var web_id = 0;

    $.each(array_clientes, function (index, item) {
        if (parseInt(item.id) === parseInt(gen_cliente_id)) {
            if (parseInt(item.web_id) > 0) {
                web_id = item.web_id;
            }
        }
    });

    gen_modal_deposito_bono_limite = 0;
    gen_modal_deposito_bono_bloqueo_web = 0;
    gen_modal_deposito_bono_contacto = 0;
    gen_modal_deposito_bono_cuenta = 0;

    limpiar_bordes_modal_deposito_c7();
    limpiar_campos_modal_deposito_c7();

    $('#modal_deposito_c7_idweb').val(web_id);

    gen_modal_deposito_bono_bloqueo_web = 1;
    
    $('#modal_deposito_c7_btn_guardar').show();
    $('#modal_deposito_c7').modal();
    return false;
}

function guardar_deposito_c7() {
    $('#modal_deposito_c7_btn_guardar').hide();
    limpiar_bordes_modal_deposito_c7();

    var tipo_contacto = $('#modal_deposito_c7_tipo_contacto').val();
    var idweb = $('#modal_deposito_c7_idweb').val();
    var monto = $('#modal_deposito_c7_monto').val().replace(/\,/g, '');
    var observacion = $('textarea#modal_deposito_c7_observacion').val();
    var cuenta = $('#modal_deposito_c7_cuenta').val();

    var imagen = $('#modal_deposito_c7_voucher').val();
    var f_imagen = $("#modal_deposito_c7_voucher")[0].files[0];
    var imagen_extension = imagen.substring(imagen.lastIndexOf("."));

    if (!(imagen.length > 0)) {
        $("#modal_deposito_c7_voucher_div").css("border", "1px solid red");
        swal('Aviso', 'Agregue una imágen.', 'warning');
        $('#modal_deposito_c7_btn_guardar').show();
        return false;
    }
    if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
            imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
            imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG") {
        $("#modal_deposito_c7_voucher_div").css("border", "1px solid red");
        swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
        $('#modal_deposito_c7_btn_guardar').show();
        return false;
    }
    if (!(parseInt(tipo_contacto) > 0)) {
        $('#modal_deposito_c7_tipo_contacto_div').css('border', '1px solid red');
        $('#modal_deposito_c7_tipo_contacto').focus();
        $('#modal_deposito_c7_btn_guardar').show();
        return false;
    }
    if (!(parseFloat(monto) > 0)) {
        $('#modal_deposito_c7_monto').css('border', '1px solid red');
        $('#modal_deposito_c7_monto').focus();
        $('#modal_deposito_c7_btn_guardar').show();
        return false;
    }

    if(parseInt(cuenta) == 0){
    	$("#modal_deposito_c7_cuenta_div").css("border", "1px solid red");
        swal('Aviso', 'Elija la cuenta a utilizar.', 'warning');
        $('#modal_deposito_c7_btn_guardar').show();
        return false;
    }

    var total_bono_mes = $('#span_bonos').html().replace(/\,/g, '');
    var bono_limite_actual = $('#bono_limite').val().replace(/\,/g, '');

    var data = new FormData();
    data.append('accion', "guardar_transaccion_deposito_c7");
    data.append('id_cliente', gen_cliente_id);
    data.append('idweb', idweb);
    data.append('imagen_voucher', f_imagen);
    data.append('tipo_contacto', tipo_contacto);
    data.append('monto', monto);
    data.append('observacion', observacion);
    data.append('total_bono_mes', total_bono_mes);
    data.append('cuenta', cuenta);

    $.ajax({
        url: "/sys/set_televentas.php",
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
            auditoria_send({"proceso": "guardar_transaccion_deposito_c7", "data": respuesta});
            if (parseInt(respuesta.http_code) == 400) {
                $('#modal_deposito_c7').modal('hide');
                swal('Aviso', respuesta.status, 'warning');
                listar_transacciones(gen_cliente_id);
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#modal_deposito_c7').modal('hide');
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                listar_transacciones(gen_cliente_id);
                return false;
            }
            return false;
        },
        error: function (result) {
            auditoria_send({"proceso": "guardar_transaccion_deposito_c7_error", "data": result});
            return false;
        }
    });
    return false;
}




function pad(n, width, z) { z = z || '0'; n = n + ''; return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n; }

function nuevo_retiro_c7(){
	SecRetC7_limpiar_campos();
	SecRetC7_obtener_cuentas();
	const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    var temp_fecha_hora_actual = now.toISOString().slice(0, -5);
	$("#SecRetC7_fecha_pago").val(temp_fecha_hora_actual.substr(0,16));

	$('#SecRetC7_select_operacion').val(1).trigger('change.select2');
	$('#SecRetC7_select_motivo_devolucion').val(0).trigger('change.select2');
	var retiro_disponible_cliente = $('#span_balance_retiro_disponible').html();
	$('#SecRetC7_tittle').html('Retiro C7');
	$('#SecRetC7_saldo_disponible').html(retiro_disponible_cliente);
	$('#sec_tlv_modal_retiro_div_motivo_devolucion').hide();

	$('#SecRetC7_btn_guardar').show();
	$('#modal_retiro_c7').modal();
	return false;
}

function SecRetC7_limpiar_campos(){
	//$('#modal_retiro_cuenta').empty();
	$('#SecRetC7_cuentas_tabla tbody').html('');
	$('#SecRetC7_monto').val('0.00');
	$('.modal_cuentas_listar').hide();
	$('.modal_cuentas_registrar').hide();
    $('#SecRetC7_input_file_voucher').val('');
    $('#SecRetC7_select_banco_pago').val(0).trigger('change.select2');
    $('#SecRetC7_select_comision').val('0.00').trigger('change.select2');
    $('#SecRetC7_nro_operacion').val('');
    $('#SecRetC7_obs_pagador').val('');
	SecRetC7_limpiar_bordes_modal_retiro();
}

function SecRetC7_limpiar_bordes_modal_retiro() {
	$('#SecRetC7_cuenta_div').css('border', '');
	$('#SecRetC7_monto').css('border', '');
	$('#SecRetC7_cuentas_banco').css('border', '');
	$('#SecRetC7_cuentas_cuenta_num').css('border', '');
	$('#SecRetC7_cuentas_cci').css('border', '');
}

function SecRetC7_obtener_cuentas(){
	var data = {
		accion: "obtener_cuentas_x_cliente",
		cliente_id: gen_cliente_id
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "obtener_cuentas_x_cliente", "data": respuesta});
			//console.log(respuesta);
			$('#SecRetC7_cuentas_tabla tbody').html('');
			if (parseInt(respuesta.http_code) == 200) {
				$('#SecRetC7_modal_retiro_cuenta').append('<option value="0">:: Seleccione ::</option>');
				$('#SecRetC7_modal_retiro_cuenta').val('0');

				$.each(respuesta.result, function (index, item) {
					$('#SecRetC7_cuentas_tabla tbody').append(
							'<tr class="SecRetC7_listado_cuentas_bancarias_clientes" for="SecRetC7_' + item.cod + '_' + item.cuenta_num_cliente + '">' +
							'   <td style="font-weight: bold; text-align: center; display: none;"><input class="SecRetC7_id_cuenta_cliente" value="' + item.cod + '" /></td>' +
							'   <td style="font-weight: bold; text-align: center; display: none;"><input class="SecRetC7_id_banco_cuenta_cliente" value="' + item.banco_id + '" /></td>' +
							'   <td style="font-weight: bold; text-align: center;" >' +
							'       <label for="SecRetC7_' + item.cod + '_' + item.cuenta_num_cliente + '" class="SecRetC7_nom_cuenta_cliente">' + item.banco + '</label>' +
							'   </td>' +
							'   <td style="font-weight: bold; text-align: center;">' +
							'       <label for="SecRetC7_' + item.cod + '_' + item.cuenta_num_cliente + '" class="SecRetC7_num_cuenta_cliente">' + item.cuenta_num + '</label>' +
							'   </td>' +
							'   <td style="font-weight: bold; text-align: center;">' +
							'       <label for="SecRetC7_' + item.cod + '_' + item.cuenta_num_cliente + '" class="SecRetC7_num_cci_cuenta_cliente">' + item.cci + '</label>' +
							'   </td>' +
							'   <td style="text-align: center;"><input type="radio" name="SecRetC7_sec_cuentas_cliente" id="SecRetC7_' + item.cod + '_' + item.cuenta_num_cliente + '" class="SecRetC7_sec_cuentas_cliente"/></td>' +
							'</tr>'
							);
				});
			} else {
				$('#SecRetC7_modal_retiro_cuenta').append('<option value="0">:: No existen cuentas ::</option>');
				$('#SecRetC7_cuentas_tabla tbody').append(
						'<tr>' +
						'   <td colspan="2">No hay cuentas registradas</td>' +
						'</tr>'
						);
				$('#SecRetC7_modal_retiro_cuenta').val('0');
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_cuentas_x_cliente_error", "data": result});
			return false;
		}
	});
}

$('#SecRetC7_btn_guardar').click(function(){
	guardar_pago_retiro_c7();
});

function guardar_pago_retiro_c7() {
	var id_cuenta_usar_retiro = 0;
	var nom_cuenta_usar_retiro = "";
	var num_cuenta_usar_retiro = "";
	var num_cci_cuenta_usar_retiro = "";
	var id_banco_cuenta_usar_retiro = 0;
	var monto_minimo_retiro = 0;
	var valor;
	var id_cuenta_pago = $('#SecRetC7_select_banco_pago').val();
	var web_id = $('#cliente_idweb').val();
	var num_operacion = $('#SecRetC7_nro_operacion').val();
	var registro = $.trim($('#SecRetC7_fecha_pago').val()) + ":00";
	var monto_comision = $("#SecRetC7_select_comision").val().replace(/\,/g, '');
	var observacion = $('#SecRetC7_obs_pagador').html();
	var imagen = $('#SecRetC7_input_file_voucher').val();
	var f_imagen = $("#SecRetC7_input_file_voucher")[0].files[0];
	var imagen_extension = imagen.substring(imagen.lastIndexOf("."));

	$('.SecRetC7_sec_cuentas_cliente:checked').each(function (indice, elemento) {
		var fila = $(this).parents(".SecRetC7_listado_cuentas_bancarias_clientes");
		id_cuenta_usar_retiro = fila.find(".SecRetC7_id_cuenta_cliente").val();
		nom_cuenta_usar_retiro = fila.find(".SecRetC7_nom_cuenta_cliente").html();
		num_cuenta_usar_retiro = fila.find(".SecRetC7_num_cuenta_cliente").html();
		num_cci_cuenta_usar_retiro = fila.find(".SecRetC7_num_cci_cuenta_cliente").html();
		id_banco_cuenta_usar_retiro = fila.find(".SecRetC7_id_banco_cuenta_cliente").val();
		valor = $(this).val();
	});

	var monto_disponible_retiro_cliente = parseFloat($('#SecRetC7_saldo_disponible').html().replace(/\,/g, '')).toFixed(2);
	var monto_solicitud = parseFloat($('#SecRetC7_monto').val().replace(/\,/g, '')).toFixed(2);
	var razon = $('#SecRetC7_select_operacion').val();
	var tipo_operacion = 1;

	if (valor == undefined || $.trim(valor) == "") {
		swal('Aviso', 'Debe seleccionar la cuenta a utilizar', 'warning');
		return false;
	}

	if(imagen == ""){
        swal('Aviso', "Debe agregar el comprobante de pago.", 'warning');
        return false;
    }

    if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
        imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
        imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG" && id_estado == 2) {
        swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
        return false;
    }

	if (parseFloat(monto_disponible_retiro_cliente) <= 0) {
		swal('Aviso', 'No tiene saldo disponible para realizar la operación.', 'warning');
		return false;
	}

	if (parseFloat(monto_solicitud) > parseFloat(monto_disponible_retiro_cliente)) {
		swal('Aviso', 'El monto no puede ser mayor al monto disponible.', 'warning');
		return false;
	}
	if (parseFloat(monto_solicitud) == 0) {
		swal('Aviso', 'Debe ingresar el monto de la solicitud.', 'warning');
		return false;
	}

	if (monto_solicitud < monto_minimo_retiro) {
		swal('Aviso', 'El monto mínimo es: ' + monto_minimo_retiro.toFixed(2) + ' Soles.', 'warning');
		return false;
	}

	if(parseFloat(monto_comision) == 0){
		swal('Aviso', 'Debe seleccionar la comisión.', 'warning');
		return false;
	}

	if(parseInt(id_cuenta_pago) == 0){
		swal('Aviso', 'Debe seleccionar un banco de pago.', 'warning');
		return false;
	}

	if(num_operacion == ''){
		swal('Aviso', 'Debe ingresar un número de operación.', 'warning');
		return false;
	}

	var data = new FormData();
	data.append('accion', "guardar_transaccion_pago_retiro_c7");
	data.append('id_banco_cuenta_usar_retiro', id_banco_cuenta_usar_retiro);
	data.append('id_cuenta_usar_retiro', id_cuenta_usar_retiro);
	data.append('cliente_id', gen_cliente_id);
	data.append('monto_solicitud', monto_solicitud);
	data.append('razon', razon);
	data.append('tipo', tipo_operacion);
	data.append('id_cuenta_pago', id_cuenta_pago);
	data.append('web_id', web_id);
	data.append('num_operacion', num_operacion);
	data.append('registro', registro);
	data.append('monto_comision', monto_comision);
	data.append('observacion', observacion);
	data.append('SecRetC7_input_file_voucher', f_imagen);
	//console.log(data);
	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "guardar_transaccion_pago_retiro_c7", "data": respuesta});
			if (parseInt(respuesta.http_code) == 400) {
				$('#modal_retiro_c7').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_retiro_c7').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "guardar_transaccion_pago_retiro_c7_error", "data": result});
			return false;
		}
	});
	return false;
}

$("#SecRetC7_input_file_voucher").change(function (e) {
    var filePath = this.value;
    var allowedExtensions = /(.jpg|.jpeg|.png|.gif)$/i;
    if(!allowedExtensions.exec(filePath)){
        swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
        this.value = '';
        return false;
    }else{
        var newPath = filePath.replace('jpg','png');
    }
});

window.addEventListener("paste", function(e){
    if (sec_id === 'televentas' || sec_id === 'retail_altenar'){
    	var existModalRetiro = $("#modal_retiro_c7").data('bs.modal');
    	if(existModalRetiro){
    		var isShowModalRetiro = $("#modal_retiro_c7").data('bs.modal').isShown;
    		if(isShowModalRetiro == true){
    			retrieveImageFromClipboardAsBlob(e, function(imageBlob){
		            var allowedExtensions = /(.jpg|.jpeg|.png|.gif)$/i;
		            if(imageBlob){
		                let fileInputElement = document.getElementById('SecRetC7_input_file_voucher');
		                let container = new DataTransfer();
		                let data = imageBlob;
		                
		                if(!allowedExtensions.exec(imageBlob.name)){
		                    swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
		                    fileInputElement.value = '';
		                    return false;
		                }
		                let img_nombre=new Date().getTime();
		                let file = new File([data], img_nombre+".png",{type:"image/jpeg", lastModified:img_nombre});
		                container.items.add(file);
		                fileInputElement.files = container.files;

		                var filePath = fileInputElement.value;
		                if(!allowedExtensions.exec(filePath)){
		                    swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
		                    fileInputElement.value = '';
		                    return false;
		                }else{
		                    readImage(fileInputElement);
		                }
		            }
		        });
    		}
    	}
    }
}, false);

$('#SecRetC7_btn_agregar_cuenta').click(function () {
	$('.modal_cuentas_listar').hide();
	$('.modal_cuentas_registrar').show();
	$('#modal_cuentas').modal();
	$('#modal_cuentas_banco').val(0).trigger('change');
	$('#modal_cuentas_cuenta_num').val('');
	$('#modal_cuentas_cci').val('');
	return false;
});

function sec_tlv_valid_number_phone(){
	var number_phone_cliente = $('#cliente_celular').val();
	if($.trim(number_phone_cliente) == ""){
		swal('Aviso', 'No puede realizar transacciones si el cliente no cuenta con número telefónico registrado.', 'warning');
		return false;
	}else{
		return true;
	}
}



function buscar_cliente_cajero() {
	var id_cajero_tlv = $.trim($("#id_cajero_tlv").val());
	var data = {
		"accion": "obtener_televentas_cliente_cajero",
		"id_cajero_tlv": id_cajero_tlv,
		"hash": tls_hash,
		"timestamp": Date.now()
	}
		$.ajax({
			url: "/sys/set_televentas.php",
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
				auditoria_send({"proceso": "obtener_televentas_cliente_cajero", "data": respuesta});	 
 
				if (parseInt(respuesta.http_code) == 200) { 
				
					
					respuesta.result.forEach(element => array_clientes.push(element));
					var i =0;
					array_clientes.forEach((elemento) => {
						if(i < max_aten_total){
							seleccionar_cliente(elemento.id, true);
							i = i +1;
						}else{

							swal({
								title: "Máximo de atención alcanzado (" + max_aten_total + ")",
								 
								type: 'info',
								timer: 5000,
								showConfirmButton: true
							});
							return false; 

						}

					});
 
					return false;
				}
				return false;						  						
			},
			error: function (result) {
				auditoria_send({"proceso": "obtener_televentas_cliente_cajero_error", "data": result});
				return false;
			}
		});
 
}


function buscar_limite_cajero() { 
	var data = {
		"accion": "obtener_televentas_limite_cajero",
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
				//swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				 
				respuesta.result.forEach((elemento) => {
					//console.log(elemento.limite);
					limite_cli_total = elemento.limite;
					//max_aten_total = elemento.max_aten;
				//	buscar_cliente_cajero();
				});
			 
				return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_televentas_limite_cajero", "data": result});
		}
	});
	
 
}

function add_modal_tambo() {
	$('#sec_tlv_modal_tamb_num_documento').val('');
	$('#sec_tlv_modal_tambo').modal();
}

function sec_tlv_ingresar_tambo(){
	try {
		var monto = $('#sec_tlv_modal_tamb_num_documento').val();
		if ($.trim(monto) == '') {
			swal('Aviso', 'Debe ingresar el monto', 'warning');
			return false;
		}

		var data = {
			"accion": "sec_tlv_ingresar_tambo",
			"monto": monto,
			"id_cliente": gen_cliente_id
		}
		$.ajax({
			url: "/sys/set_televentas.php",
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
				auditoria_send({"proceso": "sec_tlv_ingresar_tambo", "data": respuesta});
				if (parseInt(respuesta.http_code) == 400) {
					$('#sec_tlv_modal_tambo').modal('hide');
					swal('Aviso', respuesta.status, 'warning');
					return false;
				}
				if (parseInt(respuesta.http_code) == 200) {
					$('#sec_tlv_modal_tambo').modal('hide');
					listar_transacciones(gen_cliente_id);
					//ver_voucher();
					//sec_tlv_generar_ticket(respuesta.data_barcode);
					return false;
				}
				return false;
			},
			error: function (result) {
				auditoria_send({"proceso": "sec_tlv_ingresar_tambo_error", "data": result});
				return false;
			}
		});
		return false;





	} catch (e) {
		swal('¡Error!', e, 'error');
		return false;
	}
}

function sec_tlv_generar_ticket(codigo){
	var text = codigo;
	JsBarcode("#sec_tlv_img_barcode_tambo", text);
}

$("#sec_tlv_modal_tamb_num_documento").on({
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
			onchange_bono();
		} else {
			$(event.target).val("0.00");
			onchange_bono();
		}
		//onkeyup_deposito_calcular_total();
	}
});


$('#sec_tlv_copiar_voucher_terminal_tambo').click(function () {
	html2canvas(document.querySelector("#sec_tlv_modal_terminal_tambo_body"), {allowTaint: true, useCORS: true}).then(canvas => {
		$("#sec_tlv_div_img_voucher_terminal_tambo").remove();

		var img = document.createElement('img');
		img.setAttribute("id", "sec_tlv_img_voucher_terminal_tambo");
		img.setAttribute("crossorigin", "anonymous");
		img.style.display = "none";
		img.src = canvas.toDataURL();

		var div = document.createElement('div');
		div.setAttribute("id", "sec_tlv_div_img_voucher_terminal_tambo");
		div.contentEditable = true;
		div.appendChild(img);
		div.style.display = "none";
		document.body.appendChild(div);

		writeClipImgTerminalTambo();
	});
});

$('#sec_tlv_pdf_voucher_terminal_tambo').click(function () {

	var date = new Date(); 
    var mes = date.getMonth() + 1;
    var dia = date.getDate();

    var txn_id = $('#sec_tlv_modal_tambo_id_barcode').val();
    var name_file = txn_id + '_' + dia + mes;

    let body = document.body;
    let html = document.documentElement;    
    let height = Math.max(body.scrollHeight, body.offsetHeight,html.clientHeight, html.scrollHeight, html.offsetHeight);
    let element = document.querySelector('#sec_tlv_modal_terminal_tambo_body');
    let heightCM = height / 50.35;
    let heightE = Math.max(element.scrollHeight, element.offsetHeight,element.clientHeight, element.scrollHeight, element.offsetHeight) / 35.35;
    html2pdf(element, {
        margin: 1,
        filename: name_file,
        html2canvas: { dpi: 192, letterRendering: true },
        jsPDF: {
            orientation: 'portrait',
            unit: 'cm',
            format: [heightCM, heightE + 5]
        }
    });
});

async function writeClipImgTerminalTambo() {
	if (!('ClipboardItem' in window)) {
		return alert(
				"Your browser doesn't support copying images into the clipboard."
				+ " If you use Firefox you can enable it"
				+ " by setting dom.events.asyncClipboard.clipboardItem to true."
				)
	}

	try {
		const imgURL = document.getElementById('sec_tlv_img_voucher_terminal_tambo').src;
		const data = await fetch(imgURL);
		const blob = await data.blob();
		await navigator.clipboard.write([
			new ClipboardItem({
				[blob.type]: blob
			})
		]);
		alertify.success('Voucher copiado correctamente', 3);
	} catch (err) {
		console.error(err.name, err.message);
	}
}

function nueva_devolucion_c7(){
	SecDevC7_limpiar_campos();
	SecDevC7_obtener_cuentas();
	$('#sec_tlv_devolucion_disponible').html(gen_balance_no_retirable_disponible);
	$('#sec_tlv_btn_guardar_devolucion_c7').show();
	$('#sec_tlv_modal_devolucion_c7').modal();
	return false;
}

function SecDevC7_limpiar_campos(){
	$('#sec_tlv_monto_dev_c7').val('0.00');
	$('#sec_tlv_select_motivo_devolucion_c7').val(0).trigger('change.select2');
	$('#sec_tlv_select_operacion_c7').val(1).trigger('change.select2');
	$('#SecDevC7_select_banco_pago').val(0).trigger('change.select2');
	$('#SecDevC7_select_comision').val('0.00').trigger('change.select2');
	$('#SecDevC7_nro_operacion').val('');
	$('#SecDevC7_obs_pagador').val('');
	$('#SecDevC7_input_file_voucher').val('');
	const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    var temp_fecha_hora_actual = now.toISOString().slice(0, -5);
	$("#SecDevC7_fecha_pago").val(temp_fecha_hora_actual.substr(0,16));
}

function SecDevC7_obtener_cuentas(){
	var data = {
		accion: "obtener_cuentas_x_cliente",
		cliente_id: gen_cliente_id
	}
	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "obtener_cuentas_x_cliente", "data": respuesta});
			$('#SecDevC7_cuentas_tabla tbody').html('');
			if (parseInt(respuesta.http_code) == 200) {
				$('#SecDevC7_modal_retiro_cuenta').append('<option value="0">:: Seleccione ::</option>');
				$('#SecDevC7_modal_retiro_cuenta').val('0');

				$.each(respuesta.result, function (index, item) {
					$('#SecDevC7_cuentas_tabla tbody').append(
							'<tr class="SecDevC7_listado_cuentas_bancarias_clientes" for="SecDevC7_' + item.cod + '_' + item.cuenta_num_cliente + '">' +
							'   <td style="font-weight: bold; text-align: center; display: none;"><input class="SecDevC7_id_cuenta_cliente" value="' + item.cod + '" /></td>' +
							'   <td style="font-weight: bold; text-align: center; display: none;"><input class="SecDevC7_id_banco_cuenta_cliente" value="' + item.banco_id + '" /></td>' +
							'   <td style="font-weight: bold; text-align: center;" >' +
							'       <label for="SecDevC7_' + item.cod + '_' + item.cuenta_num_cliente + '" class="SecDevC7_nom_cuenta_cliente">' + item.banco + '</label>' +
							'   </td>' +
							'   <td style="font-weight: bold; text-align: center;">' +
							'       <label for="SecDevC7_' + item.cod + '_' + item.cuenta_num_cliente + '" class="SecDevC7_num_cuenta_cliente">' + item.cuenta_num + '</label>' +
							'   </td>' +
							'   <td style="font-weight: bold; text-align: center;">' +
							'       <label for="SecDevC7_' + item.cod + '_' + item.cuenta_num_cliente + '" class="SecDevC7_num_cci_cuenta_cliente">' + item.cci + '</label>' +
							'   </td>' +
							'   <td style="text-align: center;"><input type="radio" name="SecDevC7_sec_cuentas_cliente" id="SecDevC7_' + item.cod + '_' + item.cuenta_num_cliente + '" class="SecDevC7_sec_cuentas_cliente"/></td>' +
							'</tr>'
							);
				});
			} else {
				$('#SecDevC7_modal_retiro_cuenta').append('<option value="0">:: No existen cuentas ::</option>');
				$('#SecDevC7_cuentas_tabla tbody').append(
						'<tr>' +
						'   <td colspan="2">No hay cuentas registradas</td>' +
						'</tr>'
						);
				$('#SecDevC7_modal_retiro_cuenta').val('0');
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "obtener_cuentas_x_cliente_error", "data": result});
			return false;
		}
	});
}

$('#sec_tlv_btn_guardar_devolucion_c7').click(function(){
	var SecDev_cuenta_id_usar = 0;
	var SecDev_nombre_banco_cliente = 0;
	var monto_minimo_devolucion = 0;
	var valor;
	var id_cuenta_pago = $('#SecDevC7_select_banco_pago').val();
	var web_id = $('#cliente_idweb').val();
	var num_operacion = $('#SecDevC7_nro_operacion').val();
	var registro = $.trim($('#SecDevC7_fecha_pago').val()) + ":00";
	var monto_comision = $("#SecDevC7_select_comision").val().replace(/\,/g, '');
	var observacion = $('#SecDevC7_obs_pagador').html();
	var imagen = $('#SecDevC7_input_file_voucher').val();
	var f_imagen = $("#SecDevC7_input_file_voucher")[0].files[0];
	var imagen_extension = imagen.substring(imagen.lastIndexOf("."));
	
	$('.SecDevC7_sec_cuentas_cliente:checked').each(function (indice, elemento) {
		var fila = $(this).parents(".SecDevC7_listado_cuentas_bancarias_clientes");
		SecDev_cuenta_id_usar = fila.find(".SecDevC7_id_cuenta_cliente").val();
		SecDev_nombre_banco_cliente = fila.find(".SecDevC7_id_banco_cuenta_cliente").val();
		valor = $(this).val();
	});
	var monto_disponible_devolucion_cliente = parseFloat($('#sec_tlv_devolucion_disponible').html().replace(/\,/g, '')).toFixed(2);
	var monto_solicitud = parseFloat($('#sec_tlv_monto_dev_c7').val().replace(/\,/g, '')).toFixed(2);
	var razon = $('#sec_tlv_select_operacion_c7').val();
	var motivo_devolucion = $('#sec_tlv_select_motivo_devolucion_c7').val();
	var tipo_operacion = 2;

	if (valor == undefined || $.trim(valor) == "") {
		swal('Aviso', 'Debe seleccionar la cuenta a utilizar', 'warning');
		return false;
	}

	if(imagen == ""){
        swal('Aviso', "Debe agregar el comprobante de pago.", 'warning');
        return false;
    }

    if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
        imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
        imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG" && id_estado == 2) {
        swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
        return false;
    }

	if(motivo_devolucion == 0){
		swal('Aviso', 'Seleccione el motivo de la devolución.', 'warning');
		return false;
	}

	if (parseFloat(monto_disponible_devolucion_cliente) <= 0) {
		swal('Aviso', 'No tiene saldo disponible para realizar la operación.', 'warning');
		return false;
	}

	if (parseFloat(monto_solicitud) > parseFloat(monto_disponible_devolucion_cliente)) {
		swal('Aviso', 'El monto no puede ser mayor al monto disponible.', 'warning');
		return false;
	}
	if (parseFloat(monto_solicitud) == 0) {
		swal('Aviso', 'Debe ingresar el monto de la solicitud.', 'warning');
		return false;
	}

	if (monto_solicitud < monto_minimo_devolucion) {
		swal('Aviso', 'El monto mínimo es: ' + monto_minimo_devolucion.toFixed(2) + ' Soles.', 'warning');
		return false;
	}

	/*if(parseFloat(monto_comision) == 0){
		swal('Aviso', 'Debe seleccionar la comisión.', 'warning');
		return false;
	}*/

	if(parseInt(id_cuenta_pago) == 0){
		swal('Aviso', 'Debe seleccionar un banco de pago.', 'warning');
		return false;
	}

	if(num_operacion == ''){
		swal('Aviso', 'Debe ingresar un número de operación.', 'warning');
		return false;
	}

	var data = new FormData();
	data.append('accion', "guardar_transaccion_devolucion_c7");
	data.append('id_banco_cuenta_usar_dev', SecDev_nombre_banco_cliente);
	data.append('id_cuenta_usar_dev', SecDev_cuenta_id_usar);
	data.append('cliente_id', gen_cliente_id);
	data.append('monto_solicitud', monto_solicitud);
	data.append('razon', razon);
	data.append('tipo', tipo_operacion);
	data.append('id_cuenta_pago', id_cuenta_pago);
	data.append('web_id', web_id);
	data.append('num_operacion', num_operacion);
	data.append('registro', registro);
	data.append('monto_comision', monto_comision);
	data.append('observacion', observacion);
	data.append('motivo_devolucion', motivo_devolucion);
	data.append('SecDevC7_input_file_voucher', f_imagen);

	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({"proceso": "guardar_transaccion_devolucion_c7", "data": respuesta});
			if (parseInt(respuesta.http_code) == 400) {
				$('#sec_tlv_modal_devolucion_c7').modal('hide');
				swal('Aviso', respuesta.status, 'warning');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#sec_tlv_modal_devolucion_c7').modal('hide');
				swal('Aviso', 'Acción realizada con éxito.', 'success');
				listar_transacciones(gen_cliente_id);
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "guardar_transaccion_devolucion_c7_error", "data": result});
			return false;
		}
	});
	return false;
});

$('#SecDevC7_btn_agregar_cuenta').click(function () {
	$('.modal_cuentas_listar').hide();
	$('.modal_cuentas_registrar').show();
	$('#modal_cuentas').modal();
	$('#modal_cuentas_banco').val(0).trigger('change');
	$('#modal_cuentas_cuenta_num').val('');
	$('#modal_cuentas_cci').val('');
	return false;
});


function checkSoloLetras(e) {
    tecla = (document.all) ? e.keyCode : e.which;

    //Tecla de retroceso para borrar, siempre la permite
    if (tecla == 8) {
        return true;
    }

    // Patrón de entrada, en este caso solo acepta numeros y letras
    patron = /[A-Za-z ]/;
    tecla_final = String.fromCharCode(tecla);
    return patron.test(tecla_final);
}

var SecTlvGen_gr_onlinehash_client = "";
function nuevo_porta_golden_race(){
	var $bridge = $("<input>")
    $("body").append($bridge);
    $bridge.val('https://teleservicios.at/mibilletera').select();
    document.execCommand("copy");
    $bridge.remove();
    alertify.success('Enlace copiado correctamente', 3);
}

/************************************************************************************************************
*************************************************************************************************************
***************************************************BINGO*****************************************************
*************************************************************************************************************
*************************************************************************************************************/
var gen_bingo_user = "";
var gen_bingo_pass = "";
function add_bingo_total(){
	gen_bingo_user = "";
	gen_bingo_pass = "";
	sec_tlv_bingo_total_valid_credenciales();
}

function sec_tlv_open_bingo_total(){

	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	var temp_balance_monto = 0;
	var temp_balance_texto = '';

	if(parseInt(temp_balance_tipo)===1){
		temp_balance_monto = gen_balance_total;
		temp_balance_texto = 'Balance Actual';
	}else if(parseInt(temp_balance_tipo)===6){
		temp_balance_monto = gen_balance_dinero_at;
		temp_balance_texto = 'Bono AT';
	}else{
		swal('Aviso', 'Por favor, seleccione una opción válida, Saldo Real ó Saldo Promocional', 'warning');
		return false;
	}

	$('#sec_tlv_bingo_total_name_cliente').html($('#cliente_nombre').val() + ' ' + $('#cliente_apepaterno').val());
	$('#sec_tlv_bingo_total_balance_texto').html(temp_balance_texto);
	$('#sec_tlv_bingo_total_balance_cliente').html( ': S/ ' + parseFloat(temp_balance_monto).toFixed(2) );


	$('.sec_tlv_div_portal_altenar_individual').hide();
	$('.sec_tlv_div_etiquetas').hide();
	$('.sec_tlv_div_cliente').hide();
	$('.sec_tlv_div_transacciones').hide();
	$('.sec_tlv_div_portal_altenar').hide();

	$('.pest_bingo_total').hide();
	$('.sec_tlv_div_bingo_total').show();
	$('#sec_tlv_bingo_total_div_menu').show();
	$('#sec_tlv_bingo_total_cabecera').show();
}

function sec_tlv_volver_menu_bingo(element){
	$('.pest_bingo_total').hide();
	$('#sec_tlv_bingo_total_div_menu').show();
	$('#sec_tlv_bingo_total_cabecera').show();
}

function sec_tlv_cambiar_pestaña_bingo(element){
	sec_tlv_bingo_limpiarDatos();

	$('.pest_bingo_total').hide();
	$('#' + element).show();
}

function sec_tlv_bingo_limpiarDatos(){
	$('#sec_tlv_cbingo_ticket_id').val('');
}

$('#sec_tlv_bingo_total_btn_comprar').click(function(){
	sec_tlv_cambiar_pestaña_bingo('sec_tlv_bingo_total_div_comprar');
	sec_tlv_bingo_total_cargar_salas();
});

$('#sec_tlv_bingo_total_btn_consultar').click(function(){
	sec_tlv_cambiar_pestaña_bingo('sec_tlv_bingo_total_div_consultar_cartones');

});

var gen_rr_rooms_games = [];
function sec_tlv_bingo_total_cargar_salas(){
	var data = {
		accion: "sec_tlv_listar_salas_bingo",
		id_cliente : gen_cliente_id
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
			if (parseInt(respuesta.http_code) === 200) {
				$('#sec_tlv_binto_total_lista_salas').html('');
				gen_rr_rooms_games = respuesta.array_games_info;
				const map = new Map(respuesta.array_games_info.map(({room_id}) => [room_id, { room_id }]));
				respuesta.array_games_info.forEach(o => map.get(o.room_id)["room_name"] = o.room_name);
				const result = [...map.values()];
				//console.log(result);
				$.each(result, function(index, item) {
					var variables_onclick = item.room_id + ", '" + item.room_name + "'";
		        	$('#sec_tlv_binto_total_lista_salas').append(
		        		'<li class="list-group-item d-flex justify-content-between align-items-center" ' + 
		        		' style="font-size:22px; font-weight: bold; cursor: pointer;" onclick="sec_tlv_bingo_total_seleccion_sala(' + variables_onclick + ')">' + 
		        			item.room_name +
		        			'<button type="button" class="btn btn-success btn-md pull-right" onclick="sec_tlv_bingo_total_seleccion_sala(' + variables_onclick + ')">' +
		        				'Comprar' +
		        			'</button>' +
		        		'</li>'
		        	);
		        });
				auditoria_send({"respuesta": "sec_tlv_listar_salas_bingo", "data": respuesta});
				return false;
			} else if (parseInt(respuesta.http_code) === 400) {
				swal('Aviso', respuesta.status, 'warning');
				auditoria_send({"respuesta": "sec_tlv_listar_salas_bingo_error", "data": respuesta});
				return false;
			} else {
				swal('Aviso', 'Sin respuesta.', 'warning');
				return false;
			}
			return false;
		},
		error: function (result) {
			swal('Aviso', result, 'warning');
			auditoria_send({"respuesta": "sec_tlv_listar_salas_bingo_error_function", "data": result});
			return false;
		}
	});
	return false;
}

function sec_tlv_bingo_total_seleccion_sala(room_id, room_name){
	$('#sec_tlv_br_title').html('<b>' + room_name + '</b>');
	$('#sec_tlv_br_list_rooms').html('');
	if(gen_rr_rooms_games.length > 0){
		$.each(gen_rr_rooms_games, function(index, item) {
			if(item.room_id == room_id){


				var d = new Date(item.start_timestamp * 1000).getDate();
				var m = new Date(item.start_timestamp * 1000).toLocaleString('default', { month: 'short' });
				var y = new Date(item.start_timestamp * 1000).getFullYear();

				var h = new Date(item.start_timestamp * 1000).getHours();
				var i = new Date(item.start_timestamp * 1000).getMinutes();

				var fecha = d.toString().padStart(2,0) + ' ' + m + ' ' + y + ' ' + h.toString().padStart(2,0) + ':' + i.toString().padStart(2,0);
				var variables_onclick = item.room_id + ", '" + item.room_name + "', '" + item.game_id + "', '" + item.card_price + "'," 
										+ item.min_cards + ",'" + item.game_type + "','" + fecha + "'," + item.max_cards + ",'" 
										+ item.start_timestamp + "'";
		    	$('#sec_tlv_br_list_rooms').append(
		    		'<li class="list-group-item d-flex justify-content-between align-items-center" ' + 
		    		' style="font-size:20px; font-weight: bold; cursor: pointer;" onclick="sec_tlv_bingo_total_comprar_bingo(' + variables_onclick + ')">' + 
		    		'	<span class="p_title_room"> Juega: ' + fecha + '</span>' +
		    		'	<div class="pull-right">' +
		    		'		<span class="badge_type_game">' + item.game_type + '</span>' +
		    		'		<button type="button" style="font-weight: bold;" class="btn btn-success" ' +
		    		'			onclick="sec_tlv_bingo_total_comprar_bingo(' + variables_onclick + ')">' +
		    					item.card_price +
		    		'		</button>' +
		    		'	</div>' +
		    		'</li>'
		    	);
			}
	    });
	}else{
		$('#sec_tlv_br_list_rooms').append(
    		'<li class="list-group-item d-flex justify-content-between align-items-center" ' + 
    		' style="font-size:20px; font-weight: bold;"> ' +
    		' No hay datos' +
    		'</li>'
    	);
	}
	$('#sec_tlv_modal_bingos_room').modal('show');
}

function sec_tlv_bingo_total_comprar_bingo(room_id, room_name, game_id, card_price, min_cards, game_type, start_game, max_cards, start_timestamp){
	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();

	if(parseInt(temp_balance_tipo)===1){
		temp_balance_monto = gen_balance_total;
	}else if(parseInt(temp_balance_tipo)===6){
		temp_balance_monto = gen_balance_dinero_at;
	}else{
		swal('Aviso', 'Por favor, seleccione una opción válida, Saldo Real ó Saldo Promocional', 'warning');
		return false;
	}

	$('#sec_tlv_cbingo_title').html(room_name);
	$('#sec_tlv_cbingo_balance_disponible').html(temp_balance_monto);
	$('#sec_tlv_cbingo_subtitle').html(start_game);
	card_price = card_price.replace(/\D/g, "").replace(/([0-9])([0-9]{2})$/, '$1.$2').replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
	$('#sec_tlv_cbingo_valor').html(card_price);
	$('#sec_tlv_cbingo_idGame').html(game_id);
	$('#sec_tlv_cbingo_game_type').html(game_type);
	sec_tlv_cbingo_select_cant(1);
	$('#sec_tlv_cbingo_min_card').html(min_cards);
	$('#sec_tlv_cbingo_max_card').html(max_cards);
	$('#sec_tlv_cbingo_start_timestamp').val(start_timestamp);
	$('#sec_tlv_cbingo_room_id').val(room_id);
	$('#sec_tlv_cbingo_comprar_bingo').show();
	$('#sec_tlv_modal_comprar_bingo').modal('show');

}

function sec_tlv_cbingo_select_cant(cant){
	$('#sec_tlv_cbingo_cant_comprar').val(cant);
	$(".is-active-cant").removeClass("is-active-cant");
	$('.page_item_' + cant).addClass('is-active-cant');
	sec_tlv_cbingo_calcularTotal();
}
function sec_tlv_cbingo_calcularTotal(){
	var cant = $('#sec_tlv_cbingo_cant_comprar').val();
	var precio = $('#sec_tlv_cbingo_valor').html();
	var total = (cant * precio).toFixed(2);
	total = total.replace(/\D/g, "").replace(/([0-9])([0-9]{2})$/, '$1.$2').replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
	$('#sec_tlv_cbingo_total_cobrar').html(total);
}

$('#sec_tlv_cbingo_cant_comprar').on('keyup',function(){
	var cant = parseInt($('#sec_tlv_cbingo_cant_comprar').val());
	if(cant.toString() !== 'NaN'){
		$('#sec_tlv_cbingo_cant_comprar').val(cant);
		if($.trim(cant) == ''){
			$('#sec_tlv_cbingo_cant_comprar').val(0);
		}
		if(![1,2,5,10,20].includes(parseInt(cant))){
			$(".is-active-cant").removeClass("is-active-cant");
		}else{
			$(".is-active-cant").removeClass("is-active-cant");
			$('.page_item_' + cant).addClass('is-active-cant');
		}
	}else{
		$('#sec_tlv_cbingo_cant_comprar').val(0);
	}
	sec_tlv_cbingo_calcularTotal();
});

$('#sec_tlv_cbingo_comprar_bingo').click(function(){
	sec_tlv_cbingo_comprar_bingo();
});

function sec_tlv_cbingo_comprar_bingo(){
	$('#sec_tlv_cbingo_comprar_bingo').hide();
	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	var	evento_dineroat_id = $("#evento_dineroat_id").val();

	var cant = parseInt($('#sec_tlv_cbingo_cant_comprar').val());
	var max_cards = $('#sec_tlv_cbingo_max_card').html();
	var min_cards = $('#sec_tlv_cbingo_min_card').html();

	var card_price = $('#sec_tlv_cbingo_valor').html().replace(/\,/g, '');
	var total = $('#sec_tlv_cbingo_total_cobrar').html().replace(/\,/g, '');


	var gameId = $('#sec_tlv_cbingo_idGame').html();
	var game_type = $('#sec_tlv_cbingo_game_type').html();
	var roomId = $('#sec_tlv_cbingo_room_id').val();

	var roomName = $('#sec_tlv_cbingo_title').html();

	if(cant > max_cards || cant < min_cards){
		swal('Aviso', 'Debe respetar la cantidad mínima y máxima de cartones a comprar.', 'warning');
		return false;
	}
	var text_question = "";
	if(cant == 1){
		text_question = cant + " cartón";
	}else{
		text_question = cant + " cartones";
	}
	swal({
		html: true,
		title: '¿Está seguro de comprar <br><span style="color: green; font-weight: bold;">' 
				+ text_question 
				+ '</span>? <br> Con un total de: <span style="color: blue; font-weight: bold;">S/ ' + total + '</span>',
		text: '',
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#0336FF',
		cancelButtonColor: '#d33',
		confirmButtonText: 'SI, COMPRAR',
		cancelButtonText: 'CANCELAR',
		closeOnConfirm: false,
		//,showLoaderOnConfirm: true
	}, function (isConfirmed) {
		if (isConfirmed){
			var data = {
				accion : 'sec_tlv_comprar_bingo',
				room_id : roomId,
				gameId : gameId,
				quantity : cant,
				cardPrice : card_price,
				cardHolderName : gen_cliente_nombres,
				total_cobrar : total,
				cliente_id : gen_cliente_id,
				roomName : roomName,
				game_type : game_type,
				temp_balance_tipo : temp_balance_tipo,
				evento_dineroat_id: evento_dineroat_id
			};

			$.ajax({
		        url: "/sys/set_televentas.php",
		        type: 'POST',
		        data: data,
		        beforeSend: function () {
		            loading("true");
		        },
		        complete: function () {
		            loading();
		        },
		        success: function (resp) {
		        	//console.log(resp);
		            var respuesta = JSON.parse(resp);
		            auditoria_send({"respuesta": "sec_tlv_comprar_bingo", "data": respuesta});
		            if (parseInt(respuesta.http_code) == 200) {
		        		$.each(respuesta.result, function (index, item) {
		        			$('#sec_tlv_pdfbingo_tablas_cartones').html('');
		        			var d = new Date(item.startsAt * 1000).getDate();
							var m = new Date(item.startsAt * 1000).toLocaleString('default', { month: 'short' });
							var y = new Date(item.startsAt * 1000).getFullYear();

							var h = new Date(item.startsAt * 1000).getHours();
							var i = new Date(item.startsAt * 1000).getMinutes();

							var fecha = d.toString().padStart(2,0) + ' ' + m + ' ' + y + ' ' + h.toString().padStart(2,0) + ':' + i.toString().padStart(2,0);
			            	$('#sec_tlv_pdfbingo_fecha').html(respuesta.fecha_hora_actual);
							$('#sec_tlv_pdfbingo_id_jugada').html(gameId);
							$('#sec_tlv_pdfbingo_nombre_sala').html(roomName);
							$('#sec_tlv_pdfbingo_estilo_juego').html(game_type);
							$('#sec_tlv_pdfbingo_fecha_juego').html(fecha);
							$('#sec_tlv_pdfbingo_lugar_compra').html(respuesta.list_local);
							$('#sec_tlv_pdfbingo_id_visitante').html(item.guestResponse.id);
							var cards = item.cards;
							var cant_cartones = 0;

							$.each(cards, function(index, i_c){
								var c = 1;
								var max_columns = 9;
								var linea = "";
								$('#sec_tlv_pdfbingo_tablas_cartones').append(
									'<div class="sec_tlv_cls_div_tabla_carton">' +
									'	<label style="width: 100%; text-align: center;">ID Cartón: ' + i_c.id + '</label>' +
									'	<table class="table table-sm tabla_carton" id="sec_tlv_pdf_bingo_' + i_c.id + '">' +
									'	</table>' +
									'</div>'
								);
								$.each(i_c.numbers, function(index, i){
									if(i == '0'){
										i = '';
									}
									if(c == 1){
										linea += '<tr class="tabla_carton"><td class="tabla_carton">' + i + '</td>';
										c++;
									}else if(c < max_columns && c > 1){
										linea += '<td class="tabla_carton">' + i + '</td>';
										c++;
									}else if(c == max_columns){
										linea += '<td class="tabla_carton">' + i + '</td></tr>';
										c = 1;
									}
								});
								$('#sec_tlv_pdf_bingo_' + i_c.id).append(linea);
								cant_cartones ++;
							});

							$('#sec_tlv_pdfbingo_qr').html('');
							var qrcode = new QRCode(document.getElementById("sec_tlv_pdfbingo_qr"), {
					            text: 'http://bingo.apuestatotal.com/w?token=' + respuesta.token,
					            width: 128,
					            height: 128,
					            colorDark: "#000000",
					            colorLight: "#ffffff",
					            correctLevel: QRCode.CorrectLevel.H
					        });

							$('#sec_tlv_pdfbingo_cartones').html(cant_cartones);
							
							$('#sec_tlv_div_bingo_barcode').html('');
							$('#sec_tlv_div_bingo_barcode').html('<br><br><img id="sec_tlv_bingo_total_img_barcode" height="100" />');
							JsBarcode("#sec_tlv_bingo_total_img_barcode", item.guestResponse.id);

							$('#sec_tlv_div_bingo_texto_consulta').html('');
							$('#sec_tlv_div_bingo_texto_consulta').html('<br><br><span style="font-size: 18px; font-weight: 600; color: red;">Escanea el código QR 🤳🏻 o dale click en el link  👇 y podrás ver la trasmisión en vivo o ver el resultado de tus jugadas de bingo.</span>' );

							$('#sec_tlv_div_bingo_consulta').html('');
							$('#sec_tlv_div_bingo_consulta').html('<br><br><b><a style="color:Blue; font-size: 22px;"  href="https://bingo.apuestatotal.com/?guest=1&token='+item.guestResponse.token+'" target="_blank"><span style="padding: 20px;">https://bingo.apuestatotal.com/'+item.guestResponse.token.substr(-15)+'</span></a></b>' ); 

			            	sec_tlv_bingo_total_pdf_compra(item.guestResponse.id);
			            	sec_tlv_bingo_mostrar_principal();
			            	listar_transacciones(gen_cliente_id);

			                swal('Aviso', 'Acción realizada con éxito.', 'success');
			                return false;
		        		});
		            }else if(parseInt(respuesta.http_code) == 400 || parseInt(respuesta.http_code) == 502){

						$('#sec_tlv_cbingo_comprar_bingo').show();
		            	swal({ 
		            		html: true, 
		            		title: "Ocurrió un problema con el servicio del proveedor", 
		            		text: "Intentar nuevamente <br> Error: <b>" + respuesta.http_code + "</b>", 
		            		type: 'info',
		            		showConfirmButton: true 
		            	});
		            	sec_tlv_bingo_mostrar_principal();
			            listar_transacciones(gen_cliente_id);
		            }else{
		            	swal({ 
		            		html: true, 
		            		title: "Ocurrió un problema con el servicio del proveedor", 
		            		text: "Intentar nuevamente, si el problema persiste contactarse con soporte@testtest.apuestatotal.com <br> Error: <b>" + respuesta.http_code + "</b>", 
		            		type: 'info',
		            		showConfirmButton: true 
		            	});
		            	sec_tlv_bingo_mostrar_principal();
			            listar_transacciones(gen_cliente_id);
		            }
		            return false;
		        },
		        error: function (result) {
		            auditoria_send({"proceso": "sec_tlv_comprar_bingo_error", "data": result});
		            return false;
		        }
		    });
		    return false;
		}else{
			$('#sec_tlv_cbingo_comprar_bingo').show();
		}
		
	});
}


function sec_tlv_consultar_ticket(txn_id){
	let id_ticket = '';
	if(txn_id == ''){
		id_ticket = $('#sec_tlv_cbingo_ticket_id').val();
	}else{
		id_ticket = txn_id;
	}
	var	evento_dineroat_id = $("#evento_dineroat_id").val();
	var data = {
		accion : "sec_tlv_consultar_bingo",
		cliente_id : gen_cliente_id,
		id_ticket : $.trim(id_ticket),
		evento_dineroat_id : $.trim(evento_dineroat_id)
	};

	$.ajax({
		url : '/sys/set_televentas.php',
		type: 'POST',
		data: data,
		beforeSend: function(){ loading("true"); },
		complete: function(){ loading(); },
		success: function(resp){ 
			var respuesta = JSON.parse(resp);
			if(parseInt(respuesta.http_code) == 400){
				swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if(parseInt(respuesta.http_code) == 200){
				$.each(respuesta.result, function (index, item) {
					var d = new Date(item.start_timestamp * 1000).getDate();
					var m = new Date(item.start_timestamp * 1000).toLocaleString('default', { month: 'short' });
					var y = new Date(item.start_timestamp * 1000).getFullYear();

					var h = new Date(item.start_timestamp * 1000).getHours();
					var i = new Date(item.start_timestamp * 1000).getMinutes();

					var fecha = d.toString().padStart(2,0) + ' ' + m + ' ' + y + ' ' + h.toString().padStart(2,0) + ':' + i.toString().padStart(2,0);

					var cards = '';
					var cant_cards = 0;
					if(respuesta.status == 'array_infoByTokenId'){
						cards = item.cards;
						if($.trim(cards) != ""){
							cards = cards.split(",");
							cant_cards = cards.length;
						}
					}else{
						if(respuesta.cards != ''){
							cards = [];
							$.each(respuesta.cards, function(index, item){
								cards.push(item.card_id);
							});
							cant_cards = respuesta.cards.length;
						}
					}

					var ticket_status = item.ticket_status;
					var estado_ticket = "";
					$('#sec_tlv_checkbingo_button_state').html('');
					$('#sec_tlv_checkbingo_estado_div').removeClass('bg-success');
					$('#sec_tlv_checkbingo_estado_div').removeClass('bg-danger');
					var class_bg_estado = 'bg-success';
					var ticket = "'" + id_ticket + "'";
					switch(parseInt(ticket_status)){
						case 1: 
							estado_ticket = "El boleto está listo para jugar.";
							if(parseInt(item.can_cancel_status) == 1){
								$('#sec_tlv_checkbingo_button_state').append(
									'<button id="sec_tlv_bingo_cancelar_bingo" class="btn btn-info" onclick="sec_tlv_checkbingo_cancelar_carton(' + ticket + ', ' + item.card_price + ')" ' +
									'style="width: 100%; border-radius: 10px; font-size: 20px;">' +
										'Cancelar ticket' +
									'</button>'
								);
							}
							break;
						case 2: 
							estado_ticket = "El boleto está en el juego.";
							break;
						case 3: 
							estado_ticket = "El juego ha sido cancelado, boleto listo para ser reintegrado";
							var total_reembolso = cant_cards * item.card_price;
							$('#sec_tlv_checkbingo_button_state').append(
								'<button class="btn btn-warning" onclick="sec_tlv_checkbingo_reembolsar_carton(' + ticket + ', ' + parseFloat(item.card_price).toFixed(2) + ')" ' +
								'style="width: 100%; border-radius: 10px; font-size: 20px;">' +
									'Reembolsar (S/ ' + convert_amount(parseFloat(total_reembolso).toFixed(2)) + ')' +
								'</button>'
							);
							break;
						case 4: 
							estado_ticket = "Boleto reembolsado";
							break;
						case 5: 
							estado_ticket = "No ha ganado premio.";
							class_bg_estado = 'bg-danger';
							break;
						case 6: 
							estado_ticket = "Ha ganado premio.";
							var win_amount = parseFloat(item.win_amount).toFixed(2);
							var jackpot_amount = 0;
							$('#sec_tlv_checkbingo_button_state').append(
								'<button class="btn btn-info" onclick="sec_tlv_checkbingo_marcar_bingo_pago(' + ticket + ',' + win_amount + ', ' + jackpot_amount + ')" ' +
								'style="width: 100%; border-radius: 10px; font-size: 20px;">' +
									'Marcar cartón como pago (S/ ' + convert_amount(parseFloat(item.win_amount).toFixed(2)) + ')' +
								'</button>'
							);
							class_bg_estado = 'bg-success';
							break;
						case 7: 
							estado_ticket = "Boleto pagado.";
							class_bg_estado = 'bg-success';
							break;
					}
					
					if(item.can_cancel_status == 3){
						estado_ticket = "Boleto cancelado";
						class_bg_estado = 'bg-danger';
						$('#sec_tlv_checkbingo_button_state').html('');
					}

					$('#sec_tlv_checkbingo_title').html(item.room_name);
					$('#sec_tlv_checkbingo_fecha_juego').html(fecha);
					$('#sec_tlv_checkbingo_valor').html(convert_amount(parseFloat(item.card_price).toFixed(2)));
					$('#sec_tlv_checkbingo_id_juego').html(item.game_id);
					$('#sec_tlv_checkbingo_linea').html(convert_amount(parseFloat(item.one_line_jp).toFixed(2)));
					$('#sec_tlv_checkbingo_two_lineas').html(convert_amount(parseFloat(item.two_lines_jp).toFixed(2)));
					$('#sec_tlv_checkbingo_valor_bingo').html(convert_amount(parseFloat(item.three_lines_jp).toFixed(2)));
					$('#sec_tlv_checkbingo_cant_cartones').html(cant_cards);
					$('#sec_tlv_checkbingo_type_game').html(item.game_type);
					$('#sec_tlv_checkbingo_estado_div').addClass(class_bg_estado);
					$('#sec_tlv_checkbingo_estado').html(estado_ticket);
					$('#sec_tlv_checkbingo_winning_amount').html(convert_amount(parseFloat(item.win_amount).toFixed(2)));

					$('#sec_tlv_checkbingo_cartones_listado').html('');
					//Mostrar cartones comprados y ganados
					var gameWinners = (respuesta.status == 'array_infoByTokenId') ? item.gameWinners : respuesta.gameWinners;
					var gm = [];
					var c = 1;
					var rr_win = [];
					if(gameWinners != ''){
						if(respuesta.status == 'array_infoByTokenId'){
							$.each(gameWinners, function(index,item){
								gw = item;

								var won_data_rr = gw["won_data"];

								var rr_won_data_row = won_data_rr.split(",");

								var realSum = parseFloat(gw["realSum"]);

								$.each(rr_won_data_row, function(index, i_wondatarr){
									var won_data = i_wondatarr
												.replace(/-1$/, "-0").replace(/-2$/, "-1")
												.replace(/-4$/, "-2").replace(/-8$/, "-3")
												.replace(/-16$/, "-4").replace(/-32$/, "-5");;
									$.each(cards, function(index, i_c){
										if(i_c == won_data){
											var text_line = "";
											if(!$('td.sec_tlv_td_bingo_' + i_c)[0]){
												$('#sec_tlv_checkbingo_cartones_listado').append(
													'<tr>' +
													'	<td style="font-weight: bold; color: black; background-color: #2FA745" class="sec_tlv_td_bingo_' + i_c + '">' + 
															i_c + ' <br>' +
														'</td>' +
													'</tr>'
												);
											}

											if(c == 1){
												text_line = "Línea: S/";
											}else if(c == 2){
												text_line = "Dos Líneas: S/";
											}else if(c == 3){
												text_line = "Bingo: S/";
												c = 1;
											}

											$('.sec_tlv_td_bingo_' + i_c).append(
												'<label style="padding-left: 10px; padding-right: 10px; color: black; font-weight: bold; background: #FCC107; border-radius: 10px; margin-bottom: 5px;">' + 
													text_line + ' ' + convert_amount(realSum.toFixed(2)) + 
												'</label><br>'
											);
											rr_win.push(i_c);
										}
									});
								});


								c++;
							});
						}
					}

					if(cards != ''){
						$.each(cards.filter(x=> !rr_win.includes(x)), function(index, i_c){
							$('#sec_tlv_checkbingo_cartones_listado').append(
								'<tr>' +
								'	<td style="font-weight: bold; color: black;">' + i_c + '</td>' +
								'</tr>'
							);
						});
					}
					$('#sec_tlv_modal_consultar_bingo').modal('show');
				});
			}
			return false;
		},
		error: function(result){

		}
	});
}

function convert_amount(amount){
	amount = parseFloat(amount).toFixed(2);
	var new_m = amount.toString().replace(/\D/g, "").replace(/([0-9])([0-9]{2})$/, '$1.$2').replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
	return new_m;
}

function sec_tlv_checkbingo_marcar_bingo_pago(id_ticket, win_amount, jackpot_amount){
	var	dinero_at_id_temp = $("#evento_dineroat_id").val();
	var balance_text_pago_bingo = "";
	if ( parseInt(dinero_at_id_temp)===0){
		balance_text_pago_bingo = "Balance.";
	} else {
		balance_text_pago_bingo = "Bono AT.";
	}
	swal({
		html: true,
		title: '¿Desea marcar el bingo como pagado?'
				+ '<br> Se le sumará <span style="color: blue; font-weight: bold;">S/ ' + parseFloat(win_amount).toFixed(2) + '</span> al ' + balance_text_pago_bingo,
		text: '',
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#0336FF',
		cancelButtonColor: '#d33',
		confirmButtonText: 'SI, PAGAR',
		cancelButtonText: 'CANCELAR',
		closeOnConfirm: false,
	}, function () {
		var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
		var	evento_dineroat_id = $("#evento_dineroat_id").val();
		var data = {
			accion : 'sec_tlv_marcar_pago_bingo',
			id_ticket : id_ticket,
			id_cliente : gen_cliente_id,
			monto : win_amount,
			jackpot : jackpot_amount,
			balance_tipo : temp_balance_tipo,
			evento_dineroat_id : evento_dineroat_id
		};

		$.ajax({
			url : '/sys/set_televentas.php',
			type: 'POST',
			data: data,
			beforeSend: function(){ loading("true"); },
			complete: function(){ loading(); },
			success: function(resp){ 
				var respuesta = JSON.parse(resp);
				if(parseInt(respuesta.http_code) == 400){
					swal('Aviso', respuesta.result, 'warning');
				}
				if(parseInt(respuesta.http_code) == 200){
					swal('Aviso', 'El pago se realizó correctamente.', 'success');
					$('#sec_tlv_modal_consultar_bingo').hide();
					sec_tlv_bingo_mostrar_principal();
					listar_transacciones(gen_cliente_id);
				}else if(parseInt(respuesta.http_code) == 400 || parseInt(respuesta.http_code) == 502){
	            	swal({ 
	            		html: true, 
	            		title: "Ocurrió un problema con el servicio del proveedor", 
	            		text: "Intentar nuevamente <br> Error: <b>" + respuesta.http_code + "</b>", 
	            		type: 'info',
	            		showConfirmButton: true 
	            	});
	            	sec_tlv_bingo_mostrar_principal();
		            listar_transacciones(gen_cliente_id);
	            }else{
					swal({ 
	            		html: true, 
	            		title: "Ocurrió un problema con el servicio del proveedor", 
	            		text: "Intentar nuevamente, si el problema persiste contactarse con soporte@testtest.apuestatotal.com <br> Error: <b>" + respuesta.http_code + "</b>", 
	            		type: 'info',
	            		showConfirmButton: true 
	            	});
	            	sec_tlv_bingo_mostrar_principal();
		            listar_transacciones(gen_cliente_id);
				}
				return false;
			},
			error: function(result){

			}
		});
	});
}


function sec_tlv_bingo_total_pdf_compra(guestResponseId){
	var date = new Date(); 
    var mes = date.getMonth() + 1;
    var dia = date.getDate();

    var txn_id = guestResponseId;
    var name_file = 'Bingo_' + txn_id + '_' + dia + mes;

    let body = document.body;
    let html = document.documentElement;    
    let height = Math.max(body.scrollHeight, body.offsetHeight,html.clientHeight, html.scrollHeight, html.offsetHeight);
    let element = document.querySelector('#sec_tlv_cbingo_modal_pdf_body');
    let heightCM = height / 50.35;
    let heightE = Math.max(element.scrollHeight, element.offsetHeight,element.clientHeight, element.scrollHeight, element.offsetHeight) / 35.35;
    html2pdf(element, {
        margin: 1,
        filename: name_file,

        pagebreak: { avoid: '.sec_tlv_cls_div_tabla_carton' },
        html2canvas: { scale: 1, letterRendering: true },
        jsPDF: {
            unit: "mm",
            format: "A4",
            orientation: 'portrait'
        }
    });
}

function sec_tlv_bingo_mostrar_principal(){
	$('#sec_tlv_modal_comprar_bingo').modal('hide');
    $('#sec_tlv_modal_bingos_room').modal('hide');
    $('#sec_tlv_modal_consultar_bingo').modal('hide');
	$('.sec_tlv_div_bingo_total').hide();
    $('.sec_tlv_div_etiquetas').show();
	$('.sec_tlv_div_cliente').show();
	$('.sec_tlv_div_transacciones').show();
}

function sec_tlv_checkbingo_reembolsar_carton(id_ticket, win_amount){
	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	var	evento_dineroat_id = $("#evento_dineroat_id").val();
	var data = {
		accion : 'sec_tlv_reembolsar_bingo',
		id_ticket : id_ticket,
		id_cliente : gen_cliente_id,
		monto : win_amount,
		balance_tipo : temp_balance_tipo,
		evento_dineroat_id : evento_dineroat_id
	};

	$.ajax({
		url : '/sys/set_televentas.php',
		type: 'POST',
		data: data,
		beforeSend: function(){ loading("true"); },
		complete: function(){ loading(); },
		success: function(resp){ 
			var respuesta = JSON.parse(resp);
			if(parseInt(respuesta.http_code) == 400){
				swal('Aviso', respuesta.result, 'warning');
			}
			if(parseInt(respuesta.http_code) == 200){
				swal('Aviso', 'El reembolso se realizó correctamente.', 'success');
				listar_transacciones(gen_cliente_id);
				sec_tlv_bingo_mostrar_principal();
			}else if(parseInt(respuesta.http_code) == 400 || parseInt(respuesta.http_code) == 502){
            	swal({ 
            		html: true, 
            		title: "Ocurrió un problema con el servicio del proveedor", 
            		text: "Intentar nuevamente <br> Error: <b>" + respuesta.http_code + "</b>", 
            		type: 'info',
            		showConfirmButton: true 
            	});
            	sec_tlv_bingo_mostrar_principal();
	            listar_transacciones(gen_cliente_id);
            }else{
				swal({ 
            		html: true, 
            		title: "Ocurrió un problema con el servicio del proveedor", 
            		text: "Intentar nuevamente, si el problema persiste contactarse con soporte@testtest.apuestatotal.com <br> Error: <b>" + respuesta.http_code + "</b>", 
            		type: 'info',
            		showConfirmButton: true 
            	});
            	sec_tlv_bingo_mostrar_principal();
	            listar_transacciones(gen_cliente_id);
			}
			return false;
		},
		error: function(result){

		}
	});
}


function sec_tlv_checkbingo_cancelar_carton(id_ticket, win_amount){
	$('#sec_tlv_bingo_cancelar_bingo').hide();
	var tipo_balance = $('#sec_tlv_cbx_tipo_balance').val();
	var data = {
		accion : 'sec_tlv_cancelar_bingo',
		id_ticket : id_ticket,
		id_cliente : gen_cliente_id,
		tipo_balance : tipo_balance,
		monto : win_amount
	};

	$.ajax({
		url : '/sys/set_televentas.php',
		type: 'POST',
		data: data,
		beforeSend: function(){ loading("true"); },
		complete: function(){ loading(); },
		success: function(resp){ 
			var respuesta = JSON.parse(resp);
			if(parseInt(respuesta.http_code) == 400){
				$('#sec_tlv_bingo_cancelar_bingo').show();
				swal('Aviso', respuesta.result, 'warning');
			}
			if(parseInt(respuesta.http_code) == 200){
				swal('Aviso', 'El ticket se canceló correctamente', 'success');
				sec_tlv_bingo_mostrar_principal();
				listar_transacciones(gen_cliente_id);
			}
			return false;
		},
		error: function(result){

		}
	});
}

function sec_tlv_bingo_get_voucher(trans_id, estado){
	var data = {
		accion : "sec_tlv_consultar_bingo_comprado",
		trans_id : trans_id
	};

	$.ajax({
		url : '/sys/set_televentas.php',
		type: 'POST',
		data: data,
		beforeSend: function(){ loading("true"); },
		complete: function(){ loading(); },
		success: function(resp){ 
			//console.log(resp);
			var respuesta = JSON.parse(resp);
			if(parseInt(respuesta.http_code) == 400){

			}
			if(parseInt(respuesta.http_code) == 200){
				$.each(respuesta.list_ticket, function (index, item) {
	            	$('#sec_tlv_pdfbingo_fecha').html(item.fecha_compra);
					$('#sec_tlv_pdfbingo_id_jugada').html(item.id_jugada);
					$('#sec_tlv_pdfbingo_nombre_sala').html(item.roomName);
					$('#sec_tlv_pdfbingo_estilo_juego').html(item.game_type);
					$('#sec_tlv_pdfbingo_fecha_juego').html(sec_get_hour_timestamp(item.startsAt));
					$('#sec_tlv_pdfbingo_lugar_compra').html(item.nombre);
					var id_tk_show = "";
					if(estado == 1){
						id_tk_show =  item.ticket_id;
					}else if(estado == 4){
						id_tk_show =  item.ticket_id + " - Cancelado";
					}else if(estado == 5){
						id_tk_show =  item.ticket_id + " - Reembolsado";
					}else{
						id_tk_show =  item.ticket_id;
					}
					$('#sec_tlv_pdfbingo_id_visitante').html(id_tk_show);
					$('#sec_tlv_pdfbingo_cartones').html(item.cant);
					$('#sec_tlv_pdfbingo_tablas_cartones').html('');
					$.each(respuesta.list_cards, function(index, it){
						var max_columns = 9;
						var cards = it.numbers.split(",");
						var c = 1;
						var linea = "";
						$('#sec_tlv_pdfbingo_tablas_cartones').append(
							'<div class="sec_tlv_cls_div_tabla_carton">' +
							'	<label style="width: 100%; text-align: center;">ID Cartón: ' + it.card_id + '</label>' +
							'	<table class="table table-sm tabla_carton" id="sec_tlv_pdf_bingo_' + it.card_id + '">' +
							'	</table>' +
							'</div>'
						);
						$.each(cards, function(index, ic){
							if(ic == '0'){
								ic = '';
							}
							if(c == 1){
								linea += '<tr class="tabla_carton"><td class="tabla_carton">' + ic + '</td>';
								c++;
							}else if(c < max_columns && c > 1){
								linea += '<td class="tabla_carton">' + ic + '</td>';
								c++;
							}else if(c == max_columns){
								linea += '<td class="tabla_carton">' + ic + '</td></tr>';
								c = 1;
							}
						});
						$('#sec_tlv_pdf_bingo_' + it.card_id).append(linea);
					});

					$('#sec_tlv_pdfbingo_qr').html('');
					var qrcode = new QRCode(document.getElementById("sec_tlv_pdfbingo_qr"), {
			            text: 'http://bingo.apuestatotal.com/w?token=' + item.guestResponseToken,
			            width: 128,
			            height: 128,
			            colorDark: "#000000",
			            colorLight: "#ffffff",
			            correctLevel: QRCode.CorrectLevel.H
			        });
			        $('#sec_tlv_div_bingo_barcode').html('');
					$('#sec_tlv_div_bingo_barcode').html('<br><br><img id="sec_tlv_bingo_total_img_barcode" height="100" />');
					JsBarcode("#sec_tlv_bingo_total_img_barcode", item.ticket_id);

					$('#sec_tlv_div_bingo_texto_consulta').html('');
						$('#sec_tlv_div_bingo_texto_consulta').html('<br><br><span style="font-size: 18px; font-weight: 600; color: red;">Escanea el código QR 🤳🏻 o dale click en el link  👇 y podrás ver la trasmisión en vivo o ver el resultado de tus jugadas de bingo.</span>' );

					$('#sec_tlv_div_bingo_consulta').html('');
					$('#sec_tlv_div_bingo_consulta').html('<br><br><b><a style="color:blue; font-size: 22px;" href="https://bingo.apuestatotal.com/?guest=1&token='+item.guestResponseToken+'" target="_blank"><span style="padding: 20px;">https://bingo.apuestatotal.com/'+item.guestResponseToken.substr(-15)+'</span></a></b>' );
					
					$('#sec_tlv_cbingo_modal_pdf_compra').modal('show');
				});
			}
			return false;
		},
		error: function(result){

		}
	});
}

function sec_get_hour_timestamp(tiempo){
	var d = new Date(tiempo * 1000).getDate();
	var m = new Date(tiempo * 1000).toLocaleString('default', { month: 'short' });
	var y = new Date(tiempo * 1000).getFullYear();

	var h = new Date(tiempo * 1000).getHours();
	var i = new Date(tiempo * 1000).getMinutes();

	var fecha = d.toString().padStart(2,0) + ' ' + m + ' ' + y + ' ' + h.toString().padStart(2,0) + ':' + i.toString().padStart(2,0);
	return fecha;
}

$('#sec_tlv_bingo_total_btn_historial').click(function(){
	sec_tlv_cambiar_pestaña_bingo('sec_tlv_bingo_total_div_historial_reimpresion');
	$('#sec_tlv_hbingo_fecha_inicio').datetimepicker({ format: 'YYYY-MM-DD' });
	$('#sec_tlv_hbingo_fecha_inicio').val($('#g_fecha_actual').val());
	$('#sec_tlv_hbingo_fecha_inicio').change(function () {
		var var_fecha_change = $('#sec_tlv_hbingo_fecha_inicio').val();
		if (!(parseInt(var_fecha_change.length) > 0)) {
			$("#sec_tlv_hbingo_fecha_inicio").val($("#g_fecha_actual").val());
		}
	});

	$('#sec_tlv_hbingo_fecha_fin').datetimepicker({ format: 'YYYY-MM-DD' });
	$('#sec_tlv_hbingo_fecha_fin').val($('#g_fecha_actual').val());
	$('#sec_tlv_hbingo_fecha_fin').change(function () {
		var var_fecha_change = $('#sec_tlv_hbingo_fecha_fin').val();
		if (!(parseInt(var_fecha_change.length) > 0)) {
			$("#sec_tlv_hbingo_fecha_fin").val($("#g_fecha_actual").val());
		}
	});
});

$('#sec_tlv_hbingo_btn_buscar').click(function(){
	var fecha_inicio = $('#sec_tlv_hbingo_fecha_inicio').val();
	var fecha_fin = $('#sec_tlv_hbingo_fecha_fin').val();

	var data = {
		accion: 'sec_tlv_bingo_historial',
		fecha_inicio : fecha_inicio,
		fecha_fin : fecha_fin
	};

	$.ajax({
		url: '/sys/set_televentas.php',
		type: 'POST',
		data: data,
		beforeSend: function(){ loading("true"); },
		complete: function(){ loading(); },
		success: function(resp){
			var respuesta = JSON.parse(resp);
			if(parseInt(respuesta.http_code) == 400){
				swal('Aviso',respuesta.status,'warning');
			}
			if(parseInt(respuesta.http_code) == 200){
				var c = 1;
				$('#sec_tlv_hbingo_table_transacciones').html(
					'<thead>' +
					'	<th>#</th>' +
					'	<th></th>' +
					'</thead>'
				);
				$.each(respuesta.result,function(index,item){
					var variables_onclick = "'" + item.id + "',2";
					var variable_ver = "'" + item.txn_id + "'";
					$('#sec_tlv_hbingo_table_transacciones').append(
						'<tr>' +
						'	<td>' + c + '</td>' +
						'	<td style="cursor: pointer;">' +
						'		<div class="row">' +
						'			<div class="col-md-10" onclick="sec_tlv_hbingo_ver_bingo(' + variable_ver + ')">' +
						'				<div class="row" style="font-size: 15px;">' +
						'					<span class="text-success" style="font-weight: bold;">' + sec_get_hour_timestamp(item.startsAt) + ' |</span>' +
						'					<span>' + item.roomName + '</span> <span>' + item.game_type + '</span>' +
						'				</div>' + 
						'				<div class="row" style="font-size: 11px;">' +
						'					<span style="font-weight: bold; color: black;">ID Jugada </span><span>' + item.game_id + '</span>' +
						'					<span style="font-weight: bold; color: black;"> | Cartones </span><span>' + item.num_selections + '</span>' +
						'				</div>' +
						'			</div>' +
						'			<div class="col-md-2">' +
						'				<button class="btn btn-success pull-right" onclick="sec_tlv_bingo_get_voucher(' + variables_onclick + ')">Reimprimir</button>' +
						'			</div>' +
						'		</div>' +
						'	</td>' +
						'</tr>'
					);
					c++;
				});
			}
			sec_tlv_tabla_datatable_formato('#sec_tlv_hbingo_table_transacciones');
			return false;
		},
		error: function(result){

		}
	});
});

function sec_tlv_cbingo_reimprimir_pdf(){
	var txn_id = $('#sec_tlv_pdfbingo_id_visitante').html();
	sec_tlv_bingo_total_pdf_compra(txn_id + '_reimpresion');
}

function sec_tlv_hbingo_ver_bingo(id_ticket){
	sec_tlv_consultar_ticket(id_ticket);
}

$('#sec_tlv_bingo_total_btn_informe').click(function(){
	sec_tlv_cambiar_pestaña_bingo('sec_tlv_bingo_total_div_informe');
	$('#sec_tlv_ibingo_fecha_inicio').datetimepicker({ format: 'YYYY-MM-DD' });
	$('#sec_tlv_ibingo_fecha_inicio').val($('#g_fecha_actual').val());
	$('#sec_tlv_ibingo_fecha_inicio').change(function () {
		var var_fecha_change = $('#sec_tlv_ibingo_fecha_inicio').val();
		if (!(parseInt(var_fecha_change.length) > 0)) {
			$("#sec_tlv_ibingo_fecha_inicio").val($("#g_fecha_actual").val());
		}
	});

	$('#sec_tlv_ibingo_fecha_fin').datetimepicker({ format: 'YYYY-MM-DD' });
	$('#sec_tlv_ibingo_fecha_fin').val($('#g_fecha_actual').val());
	$('#sec_tlv_ibingo_fecha_fin').change(function () {
		var var_fecha_change = $('#sec_tlv_ibingo_fecha_fin').val();
		if (!(parseInt(var_fecha_change.length) > 0)) {
			$("#sec_tlv_ibingo_fecha_fin").val($("#g_fecha_actual").val());
		}
	});
});

$('#sec_tlv_ibingo_btn_obtener_informe').click(function(){
	var fecha_inicio = $('#sec_tlv_ibingo_fecha_inicio').val();
	var fecha_fin = $('#sec_tlv_ibingo_fecha_fin').val();

	var data = {
		accion: 'sec_tlv_bingo_get_informe',
		fecha_inicio : fecha_inicio,
		fecha_fin : fecha_fin
	};

	$.ajax({
		url: '/sys/set_televentas.php',
		type: 'POST',
		data: data,
		beforeSend: function(){ loading("true"); },
		complete: function(){ loading(); },
		success: function(resp){
			var respuesta = JSON.parse(resp);
			if(parseInt(respuesta.http_code) == 400){
				swal('Aviso',respuesta.status,'warning');
			}
			if(parseInt(respuesta.http_code) == 200){
				$.each(respuesta.result,function(index,item){
					$('#sec_tlv_ibingo_txt_cartones').val(item.cartones);
					$('#sec_tlv_ibingo_txt_cartones_devueltos').val(item.cartones_devueltos);
					$('#sec_tlv_ibingo_txt_apuestas').val(convert_amount(item.apuestas));
					$('#sec_tlv_ibingo_txt_devuelto').val(convert_amount(item.devuelto));
					$('#sec_tlv_ibingo_txt_pagado').val(convert_amount(item.pagado));

					var pagado_neto = parseFloat(item.apuestas) - (parseFloat(item.devuelto) + parseFloat(item.pagado));
					$('#sec_tlv_ibingo_txt_pagado_neto').val(convert_amount(pagado_neto.toFixed(2)));
				});
			}
			return false;
		},
		error: function(result){

		}
	});
});

function sec_tlv_tabla_datatable_formato(id) {
	if ($.fn.dataTable.isDataTable(id)) {
		$(id).DataTable().destroy();
	}
	$(id).DataTable({
		'paging': true,
		'lengthChange': true,
		'searching': true,
		'ordering': true,
		'order': [[0, "asc"]],
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

$('#sec_tlv_ibingo_btn_imprimir_resultados').click(function(){
	var date = new Date(); 
    var mes = date.getMonth() + 1;
    var dia = date.getDate();
    var name_file = 'Informe_bingo_' + dia + mes;

    var pos = login_tls_usuario;
    var impreso_el = get_datetime_now();
    var cartones = $('#sec_tlv_ibingo_txt_cartones').val();
    var cartones_devueltos = $('#sec_tlv_ibingo_txt_cartones_devueltos').val();
    var apuestas = $('#sec_tlv_ibingo_txt_apuestas').val();
    var devuelto = $('#sec_tlv_ibingo_txt_devuelto').val();
    var ganado = 0;
    var neto = 0;
    var cartones_pagados = 0;
    var cantidad_pagada = $('#sec_tlv_ibingo_txt_pagado').val();
    var pagado_neto = $('#sec_tlv_ibingo_txt_pagado_neto').val();

    $('#sec_tlv_ibingo_data_informe_pdf').append(
    	'<div class="row" style="text-align: center;">' +
    	'	<img src="img/BingoTotal.jpg" height="200">' +
    	'</div>' +
    	'<div class="row" style="text-align: center">' +
    	'	<h4>Informe actividad del POS</h4>' +
    	'</div>' +
    	'<div class="row">' +
    	'	<span>Desde a </span>' +
    	'</div>' +
    	'<div class="row">' +
    	'	<b>POS: </b>' + pos + 
    	'	<br><b>Impreso el: </b>' + impreso_el +
    	'	<br><b>Cartones: </b>' + cartones +
    	'	<br><b>Cartones devueltos: </b>' + cartones_devueltos +
    	'	<br><b>Apuestas: </b> S/ ' + apuestas +
    	'	<br><b>Devuelto: </b> S/ ' + devuelto +
    	'	<br><b>Ganado: </b> S/ ' + ganado +
    	'	<br><b>Neto: </b> S/ ' + neto +
    	'	<br><b>Cartones pagados: </b>' + cartones_pagados +
    	'	<br><b>Cantidad pagada: </b> S/ ' + cantidad_pagada +
    	'	<br><b>Pagado neto: </b> S/' + pagado_neto +
    	'</div>'
    );
    let body = document.body;
    let html = document.documentElement;    
    let height = Math.max(body.scrollHeight, body.offsetHeight,html.clientHeight, html.scrollHeight, html.offsetHeight);
    let element = document.querySelector('#sec_tlv_ibingo_data_informe_pdf');

    let heightCM = height / 50.35;
    let heightE = Math.max(element.scrollHeight, element.offsetHeight,element.clientHeight, element.scrollHeight, element.offsetHeight);
    html2pdf(element, {
        margin: 1,
        filename: name_file,
        html2canvas: { dpi: 192, letterRendering: true },
        jsPDF: {
            orientation: 'portrait',
            unit: 'cm',
            format: [heightCM, heightE + 20]
        }
    });
});

function get_datetime_now() {
	var_fecha_hora = '';
	$.post("/sys/set_televentas.php", {
		accion: "obtener_fecha_hora"
	})
	.done(function (data) {
		try {
			var respuesta = JSON.parse(data);
			if (parseInt(respuesta.http_code) == 200) {
				var_fecha_hora = respuesta.result;
			}else{
				var_fecha_hora = '';
			}
		} catch (e) {
			console.log("Error de TRY-CATCH -- Error: " + e);
		}
	})
	.fail(function (xhr, status, error) {
		console.log("Error de .FAIL -- Error: " + error);
	});
	return var_fecha_hora;
}

function sec_tlv_bingo_total_cerrar_portal_bingo(){
	seleccionar_cliente(gen_cliente_id, false);
}

function sec_tlv_bingo_total_valid_credenciales(){
	$('#sec_tlv_bt_user').val('');
	$('#sec_tlv_bt_pass').val('');
	gen_bingo_user = "";
	gen_bingo_pass = "";
	$.post("/sys/set_televentas.php", {
		accion: "sec_tlv_get_credenciales_bingo",
		cliente_id: gen_cliente_id
	})
	.done(function (resp) {
		var respuesta = JSON.parse(resp);
		if(parseInt(respuesta.http_code) == 400){
			gen_bingo_user = "";
			gen_bingo_pass = "";
			$('#sec_tlv_cbingo_modal_credenciales').modal('show');
		} else if(parseInt(respuesta.http_code) == 200){
			$.each(respuesta.result, function(item, index){
				gen_bingo_user = item.user;
				gen_bingo_pass = item.password;
			});
			sec_tlv_open_bingo_total();
		}else{
			return false;
		}
	})
	.fail(function (xhr, status, error) {
		console.log("Error de .FAIL -- Error: " + error);
	});
}

$('#sec_tlv_bt_ingresar_c').click(function(){
	var user = $('#sec_tlv_bt_user').val();
	var pass = $('#sec_tlv_bt_pass').val();
	if($.trim(user) == "" || $.trim(pass) == ""){
		swal('Aviso','Ingrese los datos correctos.','warning');
		return false;
	}
	$.post("/sys/set_televentas.php", {
		accion: "sec_tlv_set_credenciales_bingo",
		user: $.trim(user),
		pass: $.trim(pass)
	})
	.done(function (resp) {
		var respuesta = JSON.parse(resp);
		if(parseInt(respuesta.http_code) == 400){
			gen_bingo_user = "";
			gen_bingo_pass = "";
		} else if(parseInt(respuesta.http_code) == 200){
			$.each(respuesta.result, function(index, item){
				gen_bingo_user = item.user;
				gen_bingo_pass = item.password;
				$('#sec_tlv_cbingo_modal_credenciales').modal('hide');
				sec_tlv_open_bingo_total();
			});
			return true;			
		}else{
			return false;
		}
	})
	.fail(function (xhr, status, error) {
		console.log("Error de .FAIL -- Error: " + error);
	});
});


$("#modal_deposito_voucher").change(function (e) {
    var filePath = this.value;
    var allowedExtensions = /(.jpg|.jpeg|.png|.gif)$/i;
    if(!allowedExtensions.exec(filePath)){
        swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
        this.value = '';
        return false;
    }else{
        var newPath = filePath.replace('jpg','png');
        readImageDep(this);
    }

});

function readImageDep (input) {
	$('#modal_deposito_imagen_preview').html('');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
        	$('#modal_deposito_imagen_preview').append(
                '<div class="col-md-12">' +
                '   <div align="center" style="height: 100%;width: 100%;">' +
                '       <img  id="sec_tlv_ver_imagen_deposito" src="' + e.target.result + '" width="500px" />' +
                '   </div>' +
                '</div>'
            );
            $("#sec_tlv_ver_imagen_deposito").imgViewer2();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

$('#sec_tlv_bt_show_pass').click(function(){
	if($('#sec_tlv_bt_pass').hasClass('hide_pass')){
		$('#sec_tlv_bt_pass').removeClass('hide_pass');
		$('#btn_button_show_pass').removeClass('fa-eye');
		$('#btn_button_show_pass').addClass('fa-eye-slash');
	}else{
		$('#sec_tlv_bt_pass').addClass('hide_pass');
		$('#btn_button_show_pass').removeClass('fa-eye-slash');
		$('#btn_button_show_pass').addClass('fa-eye');
	}
});

function sec_tlv_deshabilitar_botoneria(b){
	$('#sec_tlv_div_botones_cliente button').attr('disabled', b);
	$('#sec_tlv_div_botones_cliente select').attr('disabled', b);
	$('#sec_tlv_div_botones_cliente input').attr('disabled', b);
}

function sec_tlv_min_nuevo_btn(){
	$('#sec_tlv_div_botones_cliente button').css('display', 'none');

	$('#btn_trans_deposito').css('display', 'inline');
	$('#btn_trans_devolucion').css('display', 'inline');

	$('#btn_trans_deposito').attr('disabled', false);
	$('#btn_trans_devolucion').attr('disabled', false);
}

function sec_tlv_min_antiguo_btn(){
	$('#sec_tlv_div_botones_cliente button').css('display', 'none');

	$('#btn_trans_deposito').css('display', 'inline');
	$('#btn_trans_apuesta').css('display', 'inline');
	$('#btn_trans_retiro').css('display', 'inline');
	$('#btn_trans_devolucion').css('display', 'inline');
	$('#btn_trans_pago_bingo').css('display', 'inline');
	$('#btn_trans_pago_virtuales').css('display', 'inline');
	$('#btn_trans_pago_atrax').css('display', 'inline');


	$('#btn_trans_deposito').attr('disabled', false);
	$('#btn_trans_apuesta').attr('disabled', false);
	$('#btn_trans_retiro').attr('disabled', false);
	$('#btn_trans_devolucion').attr('disabled', false);
	$('#btn_trans_pago_bingo').attr('disabled', false);
	$('#btn_trans_pago_virtuales').attr('disabled', false);
	$('#btn_trans_pago_atrax').attr('disabled', false);
}

function sec_tlv_bloqueo_botones_cliente(b){
	$('.sec_tlv_botones_funciones button').attr('disabled', b);
	$('.sec_tlv_botones_funciones select').attr('disabled', b);
	$('.sec_tlv_div_cliente').attr('disabled', b);
	$('.sec_tlv_div_cliente button').attr('disabled', b);
	$('.sec_tlv_div_cliente select').attr('disabled', b);
	$('.btn_eliminar_transaccion_class').attr('disabled', b);
	//$('.sec_tlv_div_etiquetas button').attr('disabled', b);
	if(b == true){
		$("#sec_tlv_btn_ver_img").css("display","none");	
		$('.sec_tlv_div_etiquetas span').attr('onclick', '');	
	}else{
		$("#sec_tlv_btn_ver_img").css("display","block");
	}
	
	$('.sec_tlv_div_etiquetas span').attr('disabled', b);
	$('#sec_tlv_cbx_tipo_balance').attr('disabled', b);
	 

	$('#sec_tlv_btn_estado_cuenta').attr('disabled', b);
	$('#div_sec_televentas_fecha_inicio').attr('disabled', b);
	$('#div_sec_televentas_fecha_fin').attr('disabled', b);
	$('#btn_actualizar_tabla_transacciones').attr('disabled', b);
}

//var array_sportsbook_client_open = [];
var array_module_client_open = [];

function sec_tlv_module_client_open(module_id, client_id, is_open) {
	var exists = false;
	for (var i = 0; i < array_module_client_open.length; i++) {
		var val = array_module_client_open[i];
		if ((val['module_id']).trim() === module_id.trim() && 
			parseInt(val['client_id']) === parseInt(client_id)) {
			exists = true;
			array_module_client_open[i]['is_open'] = is_open;
			break;
		}
	}
	if (!exists) {
		var new_sportsbook_client_open = {
			'module_id': module_id,
			'client_id': client_id,
			'is_open': is_open
		};
		array_module_client_open.push(new_sportsbook_client_open);
	}
}

function sec_tlv_module_client_is_open(module_id, client_id) {
	for (var i = 0; i < array_module_client_open.length; i++) {
		var val = array_module_client_open[i];
		if (((val['module_id']).trim() === module_id.trim()) && 
			(parseInt(val['client_id']) === parseInt(client_id)) && 
			(parseInt(val['is_open']) === 1)) {
			return true;
		}
	}
	return false;
}

function sec_tlv_cargar_cuentas_televentas(){
	var data = {
		"accion": "obtener_televentas_cuentas_deposito"
	}

	$.ajax({
		url: "/sys/set_televentas.php",
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
				$('#modal_deposito_cuenta').html('');
				$('#modal_deposito_cuenta').append('<option value="0" bono="0" comision_monto="0" is_yape="0">:: Seleccione ::</option>');
				$.each(respuesta.result, function(index, item){
					$('#modal_deposito_cuenta').append(
						'<option value="' + item.id + '" bono="' + item.bono + '" is_yape="' + item.valid_cuenta_yape + '"' +
						'	style="color:' + item.foreground + '; background: ' + item.background + '">' +
						'	<span style="font-size:20px">' + item.cuenta_descripcion + '</span>' +
						'</option>'
					);
				});
				$('#modal_deposito_cuenta').select2({
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
}

function sec_tlv_modal_cliente_imagenes(tipo) {
	if(tipo == 0){
		$('#sec_tlv_btn_cerrar_modal_imagenes').hide();
	}else if(tipo == 1){
		$('#sec_tlv_btn_cerrar_modal_imagenes').show();
	}
	if (parseInt(gen_cliente_id) > 0) {
		$('#modal_imagen_perfil_lista').modal({
			backdrop: 'static',
			keyboard: false
		});
		$('#cliente_imagen_nombre_buscar').val('');
		sec_tlv_listar_imagenes_cliente('');
		$('#modal_imagen_perfil_lista_titulo').html('Imagenes del Cliente: ' + $('#cliente_nombre').val() + ' ' + $('#cliente_apepaterno').val() + ' ' + $('#cliente_apematerno').val());
		setTimeout(function() {
			$('#cliente_imagen_nombre_buscar').focus();
		}, 1000);
	} else {
		swal({
			html: true,
			title: 'No es posible ver las imagenes del cliente',
			text: "Guarde al cliente y vuelva a intentar. ID cliente: " + gen_cliente_id,
			type: 'warning'
		});
	}
}

var sec_tlv_listar_imagenes_cliente_visor = '';

function sec_tlv_listar_imagenes_cliente(nombre_imagen) {
	if (!(parseInt(gen_cliente_id) > 0)) {
		swal('Aviso', 'cliente_id invalido: ' + gen_cliente_id, 'warning');
		return false;
	}
	var data = new FormData();
	data.append('accion', "listar_imagen_perfil_cliente");
	data.append('cliente_id', gen_cliente_id);
	data.append('nombre_imagen', nombre_imagen);
	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,
		processData: false,
		cache: false,
		contentType: false,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				swal(respuesta.status, respuesta.error, 'warning');
				auditoria_send({
					"proceso": "listar_imagen_perfil_cliente",
					"data": respuesta
				});
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#tbody_imagenes_cliente_tls').html(respuesta.result);
				var galley = document.getElementById('tbody_imagenes_cliente_tls');
				if (sec_tlv_listar_imagenes_cliente_visor != '') {
					sec_tlv_listar_imagenes_cliente_visor.destroy();
				}
				sec_tlv_listar_imagenes_cliente_visor = new Viewer(galley, {
					url: 'data-original',
					title: function(image) {
						return image.alt + ' (' + (this.index + 1) + '/' + this.length + ')';
					},
				});
				return false;
			}
			return false;
		},
		error: function(result) {
			auditoria_send({
				"proceso": "listar_imagen_perfil_cliente_error",
				"data": result
			});
			return false;
		}
	});
	return false;
}

function sec_tlv_modal_perfil_cliente_agregar_imagen(tipo_operacion_id, imagen_id, nombre_imagen) {
	$('#modal_imagen_perfil_form')[0].reset();
	$('#modal_imagen_perfil').modal({
		backdrop: 'static',
		keyboard: false
	});
	$('#modal_imagen_perfil_tipo_operacion_id').val(tipo_operacion_id);
	if (tipo_operacion_id == 'new') {
		$('#modal_imagen_perfil_titulo').html('Agregar Imagen');
		$('#modal_imagen_perfil_label_file_imagen').html('Imagen:');
	} else if (tipo_operacion_id == 'edit') {
		$('#modal_imagen_perfil_titulo').html('Editar imagen: ' + nombre_imagen);
		$('#modal_imagen_perfil_label_file_imagen').html('Imagen (opcional):');
		$('#modal_imagen_perfil_imagen_nombre').val(nombre_imagen);
		$('#modal_imagen_perfil_imagen_id').val(imagen_id);
	}
	setTimeout(function() {
		$('#modal_imagen_perfil_imagen_nombre').focus();
	}, 500);
}

function sec_tlv_guardar_imagen_perfil() {
	$('#modal_imagen_perfil_imagen_btn_guardar').hide();
	var cliente_id = gen_cliente_id;
	var tipo_operacion_id = $('#modal_imagen_perfil_tipo_operacion_id').val();
	var imagen_id = $('#modal_imagen_perfil_imagen_id').val();
	var imagen_nombre = $('#modal_imagen_perfil_imagen_nombre').val().trim();
	var imagen = $('#modal_imagen_perfil_file_imagen').val();
	var file_imagen = $("#modal_imagen_perfil_file_imagen")[0].files[0];
	var imagen_extension = imagen.substring(imagen.lastIndexOf("."));
	if (!(imagen_nombre.length > 0)) {
		swal('Aviso', 'Agregue un titulo a la imágen.', 'warning');
		$('#modal_imagen_perfil_imagen_btn_guardar').show();
		return false;
	}
	if (tipo_operacion_id == 'new' && (!(imagen.length > 0))) {
		swal('Aviso', 'Agregue una imagen.', 'warning');
		$('#modal_imagen_perfil_imagen_btn_guardar').show();
		return false;
	}
	if (imagen.length > 0 && imagen_extension !== ".png" && imagen_extension !== ".PNG" && imagen_extension !== ".jpg" && imagen_extension !== ".JPG" && imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG") {
		swal('Aviso', 'El archivo debe ser PNG, JPG o JPEG.', 'warning');
		$('#modal_imagen_perfil_imagen_btn_guardar').show();
		return false;
	}
	var data = new FormData();
	data.append('accion', "guardar_imagen_perfil_cliente");
	data.append('cliente_id', cliente_id);
	data.append('tipo_operacion_id', tipo_operacion_id);
	data.append('imagen_id', imagen_id);
	data.append('imagen_nombre', imagen_nombre);
	if (imagen.length > 0) {
		data.append('imagen_del_cliente', file_imagen);
	} else {
		data.append('imagen_del_cliente', '');
	}
	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,
		processData: false,
		cache: false,
		contentType: false,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({
				"proceso": "guardar_imagen_perfil_cliente",
				"data": respuesta
			});
			if (parseInt(respuesta.http_code) == 400) {
				swal(respuesta.status, respuesta.insert_error, 'warning');
				$('#modal_imagen_perfil_imagen_btn_guardar').show();

				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				$('#modal_imagen_perfil').modal('hide');
				swal(respuesta.status, '', 'success');
				$('#sec_tlv_btn_cerrar_modal_imagenes').show();
				$('#modal_imagen_perfil_imagen_btn_guardar').show();
				$("#cliente_imagen_nombre_buscar").val('');
				sec_tlv_listar_imagenes_cliente('');
				return false;
			}
			return false;
		},
		error: function(result) {
			auditoria_send({
				"proceso": "guardar_imagen_perfil_cliente_error",
				"data": result
			});
			$('#modal_imagen_perfil_imagen_btn_guardar').show();
			return false;
		}
	});
	return false;
}

function sec_tlv_perfil_cliente_eliminar_imagen(cliente_id, imagen_id) {
	swal({
		html: true,
		title: '¿Desea eliminar la imagen?',
		text: '',
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#0336FF',
		cancelButtonColor: '#d33',
		confirmButtonText: 'SI, ELIMINAR LA IMAGEN',
		cancelButtonText: 'CANCELAR',
		closeOnConfirm: false,
	}, function() {
		var data = {
			"accion": "eliminar_imagen_perfil_cliente",
			"cliente_id": cliente_id,
			"imagen_id": imagen_id
		}
		auditoria_send({"proceso": "eliminar_imagen_perfil_cliente", "data": data});
		$.ajax({
			url: "/sys/set_televentas.php",
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
				auditoria_send({
					"proceso": "eliminar_imagen_perfil_cliente",
					"data": respuesta
				});
				if (parseInt(respuesta.http_code) == 400) {
					swal('Aviso', respuesta.status, 'warning');
					return false;
				}
				if (parseInt(respuesta.http_code) == 200) {
					swal('Se eliminó la imagen', '', 'success');
					sec_tlv_listar_imagenes_cliente('');
					return false;
				}
				return false;
			},
			error: function(result) {
				auditoria_send({
					"proceso": "eliminar_imagen_perfil_cliente_error",
					"data": result
				});
				return false;
			}
		});
	});
	return false;
}

function sec_tlv_limpiar_cliente_imagen_nombre_buscar() {
	$("#cliente_imagen_nombre_buscar").val('');
	sec_tlv_listar_imagenes_cliente('');
	$("#cliente_imagen_nombre_buscar").focus();
}

setTimeout(function() {
	if (typeof gestion_ver_solo_una_pestana !== 'undefined') {
		if (parseInt(gestion_ver_solo_una_pestana) === 1) {

			// INICIO VERIFICAR SI SOLO EXISTE UNA PESTAÑA DE GESTIÓN ABIERTA
			localStorage.setItem('validador_una_pestana_abierta', '1');
			localStorage.open_tab = Date.now();

			var onLocalStorageEvent = function(e) {
				if (e.key === "open_tab") {
					localStorage.tab_available = Date.now();
					localStorage.setItem('validador_una_pestana_abierta', '2');
				}
				if (e.key === "tab_available") {
					localStorage.setItem('validador_una_pestana_abierta', '3');
				}
			};

			window.addEventListener('storage', onLocalStorageEvent, false);
			// FIN VERIFICAR SI SOLO EXISTE UNA PESTAÑA DE GESTIÓN ABIERTA 

			// INICIO VERIFICAR EL NÚMERO DE PESTAÑAS ABIERTAS E INCREMETAR EN EL CONTADOR DE PESTAÑAS ABIERTAS
			let num_pestanas_abiertas = localStorage.getItem('num_pestanas_abiertas');

			if (!num_pestanas_abiertas || parseInt(num_pestanas_abiertas) < 0) {
				localStorage.setItem('num_pestanas_abiertas', '1');
			} else {
				setTimeout(function() {
					let validador_una_pestana_abierta = parseInt(localStorage.getItem('validador_una_pestana_abierta'));
					if (parseInt(num_pestanas_abiertas) >= 2) {
						if ((validador_una_pestana_abierta === 1 || validador_una_pestana_abierta === 2)) {
							num_pestanas_abiertas = 0;
						} else {
							swal({
								html: true,
								title: 'No es posible tener más de dos pestañas abierta en gestión.',
								text: "",
								type: 'error',
								showCancelButton: false,
								showConfirmButton: false,
								allowEscapeKey: false,
								allowOutsideClick: false
							});
						}
					} else if (validador_una_pestana_abierta === 1 && parseInt(num_pestanas_abiertas) === 1) {
						num_pestanas_abiertas = 0;
					}
					localStorage.setItem('num_pestanas_abiertas', (parseInt(num_pestanas_abiertas) + 1).toString());
				}, 200);
			}
			// FIN VERIFICAR EL NÚMERO DE PESTAÑAS ABIERTAS E INCREMETAR EN EL CONTADOR DE PESTAÑAS ABIERTAS

			// INICIO AL CERRAR UNA PESTAÑA DE GESTIÓN, DESCONTAR EN EL CONTADOR DE PESTAÑAS ABIERTAS
			window.addEventListener('beforeunload', function() {
				let num_pestanas_abiertas = localStorage.getItem('num_pestanas_abiertas');
				if (num_pestanas_abiertas) {
					localStorage.setItem('num_pestanas_abiertas', (parseInt(num_pestanas_abiertas) - 1).toString());
				}
			});
			// FIN AL CERRAR UNA PESTAÑA DE GESTIÓN, DESCONTAR EN EL CONTADOR DE PESTAÑAS ABIERTAS
		}
	}
}, 1000);

$('#sec_tlv_modal_valid_yape_btn_guardar').click(function(){
	$('#sec_tlv_modal_valid_yape_btn_guardar').hide();
	var id_transaccion = 0;
	var valor;
	//obtener id seleccionado
	$('.sec_modal_valid_yape_radio:checked').each(function (indice, elemento) {
		var fila = $(this).parents(".sec_modal_deposito_listado_yapes");
		id_transaccion = fila.find(".sec_tlv_modal_valid_yape_id_trans").val();
		valor = $(this).val();
	});
	if(id_transaccion == 0){
		swal('Aviso', 'Seleccione yape correspondiente al cliente.', 'warning');
		$('#sec_tlv_modal_valid_yape_btn_guardar').show();
		return false;
	}

	var tipo_apuesta = $('#modal_deposito_tipo_apuesta').val();
	var cuenta = $('#modal_deposito_cuenta').val();
	var idweb = $('#modal_deposito_idweb').val();
	var bono_select = 0;
	var bono_nombre = $('#modal_deposito_bono_select option:selected').text();
	var monto = $('#modal_deposito_monto').val().replace(/\,/g, '');

	if (document.getElementById('modal_deposito_habilitar_dni').checked){
		habilitar_dni = 1;
		var titular_abono = $('#modal_deposito_titular_nombre').val();
	}
	else{
		habilitar_dni = 0;
		var titular_abono = '0';
	}

	var observacion = $.trim($('textarea#modal_deposito_observacion').val());
	var imagen = $('#modal_deposito_voucher').val();
	var f_imagen = $("#modal_deposito_voucher")[0].files[0];
	var imagen_extension = imagen.substring(imagen.lastIndexOf("."));
	var id_validacion_yape = id_transaccion;
	var total_bono_mes = $('#span_bonos').html().replace(/\,/g, '');
	var bono_limite_actual = $('#bono_limite').val().replace(/\,/g, '');
	var fecha_abono = $('#modal_deposito_fecha').val();
	var tipo_constancia = $('#modal_tipo_constancia').val();

	var data = new FormData();
	data.append('accion', "guardar_transaccion_deposito");
	data.append('id_cliente', gen_cliente_id);
	data.append('idweb', idweb);
	data.append('imagen_voucher', f_imagen);
	data.append('tipo_constancia', tipo_constancia);
	data.append('cuenta', cuenta);
	data.append('bono_id', bono_select);
	data.append('monto', monto);
	data.append('observacion', observacion);
	data.append('total_bono_mes', total_bono_mes);
	data.append('titular_abono', titular_abono);
	data.append('tipo_apuesta', tipo_apuesta);
	data.append('id_validacion_yape', id_validacion_yape);
	data.append('fecha_abono', fecha_abono);

	guardar_deposito_confirmar(data);
});

function sec_tlv_cerrar_modal_valid_yape(){
	$('#sec_tlv_modal_valid_yape_table tbody').html('');
	$('#modal_deposito_btn_guardar').show();
	$('#sec_tlv_modal_valid_yape').modal('hide');
};

$('#modal_deposito_cuenta').on('change', function(){
	var is_yape = $('#modal_deposito_cuenta option:selected').attr("is_yape");
	if(is_yape == 1){
		$('#sec_tlv_modal_deposito_fecha_abono_div').show();
		$('#sec_tlv_modal_deposito_tipo_constancia_div').show();
	}else{
		$('#sec_tlv_modal_deposito_fecha_abono_div').hide();
		$('#sec_tlv_modal_deposito_tipo_constancia_div').hide();
	}
});


function sec_tlv_modal_comprobantes_pago_sin_notificar() {
	$('#modal_comprobantes_de_pago_sin_notificar').modal({
		backdrop: 'static',
		keyboard: false
	});
	sec_tlv_listar_comprobantes_de_pago_sin_notificar();
}

function sec_tlv_listar_comprobantes_de_pago_sin_notificar() {
	var data = {
		"accion": "listar_comprobantes_de_pago_sin_notificar",
		"cargo_id": anuncios_cargo_id,
		"administrar_vouchers_sin_enviar": administrar_vouchers_sin_enviar,
		"rango_dias_consultar_voucher_sin_envio": rango_dias_consultar_voucher_sin_envio
	}
	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				swal(respuesta.status, respuesta.error, 'warning');
				auditoria_send({
					"proceso": "listar_comprobantes_de_pago_sin_notificar",
					"data": respuesta
				});
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				if ($.fn.dataTable.isDataTable('#table_comprobantes_de_pago_sin_notificar')) {
					$('#table_comprobantes_de_pago_sin_notificar').DataTable().destroy();
				}
				$('#tbody_comprobantes_de_pago_sin_notificar').html(respuesta.result);
				sec_tlv_datatable_comprobantes_pago_sin_notificar('#table_comprobantes_de_pago_sin_notificar');

				setTimeout(function() {
					$('.sec_tlv_select2_promotores_asignar').select2();
				}, 200);
				return false;
			}
			return false;
		},
		error: function(result) {
			auditoria_send({
				"proceso": "listar_comprobantes_de_pago_sin_notificar_error",
				"data": result
			});
			return false;
		}
	});
	return false;
}

function sec_tlv_ver_cliente_comprobante_de_pago_sin_notificar(tipo_doc,num_docu_cliente) {
	if(tipo_doc == "0" || tipo_doc == "1" || tipo_doc == "2") {
		$('input[name="buscador_tipo"][value="' + tipo_doc + '"]').click();
	} else {
		$('input[name="buscador_tipo"][value="0"]').click();
	}

	$('#modal_comprobantes_de_pago_sin_notificar').modal('hide');
	$("#buscador_texto").val(num_docu_cliente);
	sec_tlv_val_cliente();
}

var sec_tlv_numero_comprobantes_de_pago_sin_notificar = 0;

function sec_tlv_consultar_numero_comprobantes_de_pago_sin_notificar() {
	var data = {
		"accion": "consultar_numero_de_comprobantes_de_pago_sin_notificar",
		"cargo_id": anuncios_cargo_id,
		"administrar_vouchers_sin_enviar": administrar_vouchers_sin_enviar,
		"rango_dias_consultar_voucher_sin_envio": rango_dias_consultar_voucher_sin_envio
	}
	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				swal(respuesta.status, respuesta.error, 'warning');
				auditoria_send({
					"proceso": "consultar_numero_de_comprobantes_de_pago_sin_notificar",
					"data": respuesta
				});
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				let num_comprobantes_sin_notificar = parseInt(respuesta.result);
				if (parseInt(sec_tlv_numero_comprobantes_de_pago_sin_notificar) < num_comprobantes_sin_notificar) {
					let mensaje = "1 comprobante pendiente";
					if (num_comprobantes_sin_notificar > 0) {
						mensaje = num_comprobantes_sin_notificar + " comprobante(s) pendientes";
					}
					if (administrar_vouchers_sin_enviar == 1) {
						alertify.warning("Tiene " + mensaje + "  de subir. Clic en la campana azul para más detalle.", 0);
					} else {
						alertify.warning("Tiene asignado " + mensaje + "  de subir. Clic en la campana azul para más detalle.", 0);
					}
				}
				if (parseInt(sec_tlv_numero_comprobantes_de_pago_sin_notificar) != num_comprobantes_sin_notificar) {
					sec_tlv_numero_comprobantes_de_pago_sin_notificar = num_comprobantes_sin_notificar;
					$('#num_comprobantes_sin_notificar').html(sec_tlv_numero_comprobantes_de_pago_sin_notificar);
					if ($('#modal_comprobantes_de_pago_sin_notificar').is(':visible')) {
						sec_tlv_listar_comprobantes_de_pago_sin_notificar();
					}
				}
				$('#tlv_a_num_comprobantes_sin_notificar').hide();
				if (parseInt(sec_tlv_numero_comprobantes_de_pago_sin_notificar) > 0) {
					$('#tlv_a_num_comprobantes_sin_notificar').show();
				}
				return false;
			}
			return false;
		},
		error: function(result) {
			auditoria_send({
				"proceso": "consultar_numero_de_comprobantes_de_pago_sin_notificar_error",
				"data": result
			});
			return false;
		}
	});
	return false;
}

var array_clientes_id_sin_notificar_comprobantes_de_pago = [];

function sec_tlv_consultar_retiros_pagados_solicitados_por_mi() {
	var data = {
		"accion": "consultar_retiros_pagados_solicitados_por_mi"
	}
	$.ajax({
		url: "/sys/set_televentas.php",
		type: 'POST',
		data: data,
		success: function (resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				swal(respuesta.status, respuesta.error, 'warning');
				auditoria_send({
					"proceso": "consultar_retiros_pagados_solicitados_por_mi",
					"data": respuesta
				});
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				respuesta.result.forEach((elemento) => {
					const existe_en_array = array_clientes_id_sin_notificar_comprobantes_de_pago.includes(elemento.id);

					if (!existe_en_array) {
						alertify.warning('Comprobante subido ' + elemento.created_at.substring(0, 16) + '<br>' +
							'<button class="btn btn-sm btn-primary btn-block" onclick="sec_tlv_ver_cliente_comprobante_de_pago_sin_notificar(\'' + elemento.tipo_doc + '\',\'' + elemento.num_doc + '\');">' +
							'<span class="fa fa-eye"></span>  ' + elemento.cliente +
							'</button>', 0);
						array_clientes_id_sin_notificar_comprobantes_de_pago.push(elemento.id)
					}

				});
				return false;
			}
			return false;
		},
		error: function (result) {
			auditoria_send({
				"proceso": "consultar_retiros_pagados_solicitados_por_mi_error",
				"data": result
			});
			return false;
		}
	});
	return false;
}

function sec_tlv_asignar_transaccion_a_usuario(trans_id) {
	$("#trans_btn_" + trans_id).hide();
	var user_id_asignado = $("#trans_" + trans_id).val().trim();
	if(user_id_asignado == "") {
		alertify.warning("Seleccione a un cajero.", 5);
		$("#trans_btn_" + trans_id).show();
		return;
	}
	var data = {
		"accion": "asignar_transaccion_a_usuario",
		"transaccion_id": trans_id,
		"usuario_id": user_id_asignado
	}
	auditoria_send({
		"proceso": "asignar_transaccion_a_usuario",
		"data": data
	});
	$.ajax({
		url: "/sys/set_televentas.php",
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
			auditoria_send({
				"proceso": "asignar_transaccion_a_usuario",
				"data": respuesta
			});
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.status, 'warning');
				$("#trans_btn_" + trans_id).show();
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				sec_tlv_listar_comprobantes_de_pago_sin_notificar();
				alertify.success('Se asigno correctamente', 10);
				$("#trans_btn_" + trans_id).show();
				return false;
			}
			return false;
		},
		error: function(result) {
			auditoria_send({
				"proceso": "asignar_transaccion_a_usuario_error",
				"data": result
			});
			$("#trans_btn_" + trans_id).show();
			return false;
		}
	});
}

function sec_tlv_datatable_comprobantes_pago_sin_notificar(id) {
	$(id + ' tfoot th').each( function (i) {
		var title = $(id + ' thead th').eq( $(this).index() ).text();
		$(this).html( '<input type="text" placeholder="'+title+'" data-index="'+i+'" style="width: 60px;"/>' );
	} );

	var table = $(id).DataTable({
		'paging': true,
		'lengthChange': true,
		'searching': true,
		'ordering': true,
		'order': [[0, "asc"]],
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
		},
		drawCallback: function() {
			$('.sec_tlv_select2_promotores_asignar').select2();
		}
	});

	$( table.table().container() ).on( 'keyup', 'tfoot input', function () {
		table
		.column( $(this).data('index') )
		.search( this.value )
		.draw();
	} );
}

function sec_tlv_reenviar_solicitud_recarga(transaccion_id){
	var idweb = $('#cliente_idweb').val();
	 swal({
        html:true,
        title: '¿Está seguro de reenviar la solicitud de recarga?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0336FF',
        cancelButtonColor: '#d33',
        confirmButtonText: 'SI, REENVIAR',
        cancelButtonText: 'CANCELAR',
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function(){
		$.post("/sys/set_televentas.php", {
	        accion: "sec_tlv_reenvio_recarga_web",
	        cliente_id: gen_cliente_id,
	        idweb: idweb,
	        trans_id: transaccion_id,
	        timestamp : Date.now()
	    }).done(function (resp) {
	        try {
	            var respuesta = JSON.parse(resp);
	            if (parseInt(respuesta.http_code) == 200) {
	                swal({
	                    title: 'Recarga Exitosa',
	                    text: '',
	                    type: 'success',
	                    timer: 1500,
	                    showConfirmButton: false
	                });

	                listar_transacciones(gen_cliente_id);
	                return false;
	            }else{
	            	swal({
	                    title: "Aviso",
	                    text: respuesta.status,
	                    type: 'warning',
	                    showConfirmButton: true
	                });
	            }
	            return false;
	        } catch (e) {
	            swal('¡Error!', e, 'error');
	            return false;
	        }
	    })
	    .fail(function (xhr, status, error) {
	        $('#modal_observacion_supervisor').modal('hide');
	        swal('¡Error!', error, 'error');
	       // console.log("Error de .FAIL -- Error: " + error);
	        return false;
	    });
	    return false;
    });
}

$('#sec_tlv_modal_url_sorteo_cliente_copiar').click(function () {
	var url = $('#sec_tlv_modal_url_sorteo_txt').val();
	var $bridge = $("<input>")
    $("body").append($bridge);
    $bridge.val(url).select();
    document.execCommand("copy");
    $bridge.remove();
    alertify.success('Enlace copiado correctamente', 3);
});

function sec_tlv_get_premio_sorteo(cliente_id, url_codigo){
	swal({
		html: true,
		title: 'El cliente tiene un premio',
		text: '',
		type: 'success',
		showCancelButton: false,
		confirmButtonColor: '#198754',
		cancelButtonColor: '#d33',
		confirmButtonText: 'COPIAR ENLACE',
		cancelButtonText: '',
		closeOnConfirm: true,
	}, function(data) {
		if(data){
			var data = {
				"accion": "marcar_premio_copiado",
				"cliente_id": cliente_id
			}
			auditoria_send({"proceso": "marcar_premio_copiado", "data": data});
			$.ajax({
				url: "/sys/set_televentas.php",
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
					auditoria_send({"proceso": "marcar_premio_copiado", "data": respuesta });
					if (parseInt(respuesta.http_code) == 400) {
						swal('Aviso', respuesta.status, 'warning');
						return false;
					}
					if (parseInt(respuesta.http_code) == 200) {
						var url = url_codigo;
						var $bridge = $("<input>")
					    $("body").append($bridge);
					    $bridge.val(url).select();
					    document.execCommand("copy");
					    $bridge.remove();
					    alertify.success('Enlace copiado correctamente', 3);
						return false;
					}
					return false;
				},
				error: function(result) {
					auditoria_send({ "proceso": "marcar_premio_copiado_error", "data": result });
					return false;
				}
			});
		}
	});
}



//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// PAGO KURAX
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************


function abrir_pago_kurax() {
	$('#modal_pago_kurax_ticket').val('')
	$('#modal_pago_kurax_btn_guardar').show();
	var temp_balance_tipo = $("#sec_tlv_cbx_tipo_balance").val();
	var temp_balance_monto = 0;
	var temp_balance_texto = '';

	if(parseInt(temp_balance_tipo)===1){
		temp_balance_monto = gen_balance_total;
		temp_balance_texto = 'Balance Actual';
	}else if(parseInt(temp_balance_tipo)===6){
		temp_balance_monto = gen_balance_dinero_at;
		temp_balance_texto = 'Bono AT';
	} else {
		swal('Aviso', 'Por favor, seleccione una opción válida, Saldo Real ó Saldo Promocional', 'warning');
		return false;
	}

	var texto_titulo = 'Pago Ticket Kurax';

	$('#sec_tlv_modal_pago_kurax_title').html('<b>'+texto_titulo+' - '+temp_balance_texto+': S/ ' + parseFloat(temp_balance_monto).toFixed(2) + '</b>');
	$('#sec_tlv_modal_pago_kurax_anonimo').attr('checked', false);
	$('#sec_tlv_modal_pago_kurax').modal();
}

$(function () {
	$('#modal_pago_kurax_btn_guardar').click(function () {
		var is_anonimo = $('#sec_tlv_modal_pago_kurax_anonimo').is(':checked');
		var continuar = 0;
		if(is_anonimo){
			swal({
				html: true,
				title: '',
				text: "¿Desea pagar el ticket de un cliente anónimo?",
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#0336FF',
				cancelButtonColor: '#d33',
				confirmButtonText: 'SI, PAGAR',
				cancelButtonText: 'CANCELAR',
				closeOnConfirm: false
			}, function () {
				modal_pago_kurax_btn_guardar();
			});
		}else{
			modal_pago_kurax_btn_guardar();
		}
		
		return false;
	});

});


function modal_pago_kurax_btn_guardar() {
	try{
		$('#modal_pago_kurax_btn_guardar').hide();
		$('#modal_pago_kurax_ticket').css('border', '');
	
		var ticket = $('#modal_pago_kurax_ticket').val();
		 
		if (ticket.length == 0) {
			$('#modal_pago_kurax_ticket').css('border', '1px solid red');
			$('#modal_pago_kurax_ticket').focus();
			$('#modal_pago_kurax_btn_guardar').show();
			return false;
		}
		
		var is_anonimo = $('#sec_tlv_modal_pago_kurax_anonimo').is(':checked');
	
		//let ticket_id = ticket.substr(2, 4);
		let autenticacao = ticket; //.substr(11, 10);

		var data = new FormData();
		data.append('accion', "pagar_ticket_kurax");
		data.append('id_cliente', gen_cliente_id);
		//data.append('ticket_id', ticket_id); 
		data.append('autenticacao', autenticacao); 
		data.append('is_anonimo', (is_anonimo ? 1 : 0)); 

		$.ajax({
			url: "/sys/set_televentas.php",
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

				if (parseInt(respuesta.http_code) == 200) {
					swal(respuesta.status, '', 'success');
					$('#sec_tlv_modal_pago_kurax').modal('hide');
					listar_transacciones(gen_cliente_id);
					return false;
				} else {
					swal('Aviso', respuesta.status, 'warning');
					$('#sec_tlv_modal_pago_kurax').modal('hide');
					listar_transacciones(gen_cliente_id);
					return false;
				}
				
			},
			error: function (result) {
				return false;
			}
		});
	}catch (e) {
		console.log("Error de TRY-CATCH --> Error: " + e);
		return false;
	}
}
