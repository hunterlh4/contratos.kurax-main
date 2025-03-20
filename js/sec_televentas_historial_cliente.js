var var_fecha_hora = '';
 
 
function sec_televentas_historial_cliente() {
	//$("input:radio[name='buscador_tipo']").val(9);
	
	if (sec_id === 'televentas_historial_cliente') {
		 
		$("input:radio[name=buscador_tipo][value=" + 8 + "]").attr('checked', 'checked');
 
		localStorage.removeItem("listNew");
	 
	}
}

 


 

$(function () {

	if (sec_id === 'televentas_historial_cliente') {


		$('#div_sec_televentas_historial_cliente_fecha_inicio').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#div_sec_televentas_historial_cliente_fecha_fin').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#div_sec_televentas_historial_cliente_fecha_inicio').val($('#g_fecha_hace_7_dias').val());
		$('#div_sec_televentas_historial_cliente_fecha_fin').val($('#g_fecha_actual').val());

		$('#cliente_local').select2();

		$('#div_sec_televentas_historial_cliente_fecha_inicio').change(function () {
			var var_fecha_change = $('#div_sec_televentas_historial_cliente_fecha_inicio').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#div_sec_televentas_historial_cliente_fecha_inicio").val($("#g_fecha_hace_7_dias").val());
			}
		});
		$('#div_sec_televentas_historial_cliente_fecha_fin').change(function () {
			var var_fecha_change = $('#div_sec_televentas_historial_cliente_fecha_fin').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#div_sec_televentas_historial_cliente_fecha_fin").val($("#g_fecha_actual").val());
			}
		});

		$('input:radio[name=buscador_tipo]').on('change', function () {
			setTimeout(function () {
				$("#buscador_lbl_mensaje").html('');
				$("#buscador_texto_historial_cliente").val(''); // Limpiar input
				$("#buscador_texto_historial_cliente").removeAttr('maxLength');

				var busc_tipo = $("input:radio[name='buscador_tipo']:checked").val(); //Obtener valor del radio seleccionado
				// Celular
				if (parseInt(busc_tipo) == 9) {
					$("#buscador_texto_historial_cliente").attr('maxLength', '9');
				}
				// DNI
				if (parseInt(busc_tipo) == 0) {
					$("#buscador_texto_historial_cliente").attr('maxLength', '8');
				}
				// CE/PTP
				if (parseInt(busc_tipo) == 1) {
					$("#buscador_texto_historial_cliente").attr('maxLength', '12');
				}
				// PASAPORTE
				if (parseInt(busc_tipo) == 2) {
					$("#buscador_texto_historial_cliente").attr('maxLength', '12');
				}
				// ID-WEB
				if (parseInt(busc_tipo) == 8) {
					$("#buscador_texto_historial_cliente").attr('maxLength', '12');
				}

				$("#buscador_texto_historial_cliente").focus(); // Dar foco al input
				return false;
			}, 100);
		});

		$('#form_consultar').on('submit', function () {
			buscar_cliente_historial_cliente();
			return false;
			
		});

	 
	 
	}

});


$(function () {

	if (sec_id === 'televentas_historial_cliente') {
 
		$('#btn_actualizar_tabla_transacciones').click(function () {
			listar_transacciones_historial_cliente(gen_cliente_id);
		});

	}

});



//var array_clientes=Array();
var array_clientes = [];

function buscar_cliente_historial_cliente() {
	$("#buscador_lbl_mensaje").html('');

	var busc_tipo = $("input:radio[name='buscador_tipo']:checked").val();
	var buscador_texto = $.trim($("#buscador_texto_historial_cliente").val());

	// Celular
	if (parseInt(busc_tipo) == 9) {
		if (buscador_texto.length !== 9) {
			$("#buscador_lbl_mensaje").html('El número de celular debe tener 9 dígitos.');
			$("#buscador_texto_historial_cliente").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) > 900000000 && parseInt(buscador_texto) <= 999999999)) {
			$("#buscador_lbl_mensaje").html('Número de celular inválido.');
			$("#buscador_texto_historial_cliente").focus();
			return false;
		}
	}
	// DNI
	if (parseInt(busc_tipo) == 0) {
		if (buscador_texto.length !== 8) {
			$("#buscador_lbl_mensaje").html('El número de DNI debe tener 8 dígitos.');
			$("#buscador_texto_historial_cliente").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) >= 1 && parseInt(buscador_texto) <= 99999999)) {
			$("#buscador_lbl_mensaje").html('Número de DNI inválido.');
			$("#buscador_texto_historial_cliente").focus();
			return false;
		}
	}
	// CE/PTP
	if (parseInt(busc_tipo) == 1) {
		if (buscador_texto.length !== 9) {
			$("#buscador_lbl_mensaje").html('El número de CE/PTP debe tener 9 dígitos.');
			$("#buscador_texto_historial_cliente").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) >= 0 && parseInt(buscador_texto) <= 999999999)) {
			$("#buscador_lbl_mensaje").html('Número de CE/PTP inválido.');
			$("#buscador_texto_historial_cliente").focus();
			return false;
		}
	}
	// PASAPORTE
	if (parseInt(busc_tipo) == 2) {
		if (!(buscador_texto.length > 0)) {
			$("#buscador_lbl_mensaje").html('El número de PASAPORTE no debe estar vacío.');
			$("#buscador_texto_historial_cliente").focus();
			return false;
		}
	}
	// ID-WEB
	if (parseInt(busc_tipo) == 8) {
		if (!(buscador_texto.length > 0)) {
			$("#buscador_lbl_mensaje").html('El número de ID-WEB no debe estar vacío.');
			$("#buscador_texto_historial_cliente").focus();
			return false;
		}
		if (!(parseInt(buscador_texto) > 0)) {
			$("#buscador_lbl_mensaje").html('ID-WEB inválido.');
			$("#buscador_texto_historial_cliente").focus();
			return false;
		}
	}

	var data = {
		"accion": "obtener_televentas_cliente",
		"tipo": busc_tipo,
		"valor": buscador_texto,
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
			url: "/sys/set_televentas_historial_cliente.php",
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
					swal('Aviso', respuesta.result, 'warning');
					return false;
				}
			 
				if (parseInt(respuesta.http_code) == 200) {
					$("#buscador_texto_historial_cliente").val('');
					array_clientes.push(respuesta.result);
					//console.log('Nuevo cliente: ' + respuesta.result.id);
					seleccionar_cliente_historial_cliente(respuesta.result.id, true);
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
		$("#buscador_texto_historial_cliente").val('');
		seleccionar_cliente_historial_cliente(buscador_cliente_id, false);
	}
}

var gen_cliente_id = 0;
var gen_cliente_nombres = '';
var gen_calimaco_id = '';
var gen_balance_total = 0;
var gen_balance_bono_disponible = 0;
var gen_balance_bono_utilizado = 0;
var gen_balance_retiro_disponible = 0;
var gen_balance_no_retirable_disponible = 0;

function seleccionar_cliente_historial_cliente(id_cliente, nuevo) {
	$('#' + id_cliente).find('.div_tab_cliente_historial_cliente').removeClass('naranja');
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
	var cliente_nombres = '';
	var bono_limite = '';
	var cliente_local = 3900;
	var titulo_tab = '';
	gen_cliente_nombres = '';
	gen_calimaco_id = '';

	$('.sec_tlv_div_etiquetas').show();
	$('.sec_tlv_div_cliente').show();
	$('.sec_tlv_div_transacciones').show();
	$('.sec_tlv_div_portal_altenar').hide();

	$.each(array_clientes, function (index, item) {
		if (parseInt(item.id) === parseInt(id_cliente)) {
			if (!(parseInt(item.tipo_doc) >= 0 && parseInt(item.tipo_doc) <= 2)) {
				item.tipo_doc = '';
			}
			sec_tlv_hist_cargar_titular_abono_cliente();
			gen_calimaco_id = item.calimaco_id;
			cliente_tipo_doc = (item.tipo_doc !== null ? item.tipo_doc : '0').toString();
			cliente_num_doc = (item.num_doc !== null ? item.num_doc : '0').toString();
			cliente_fecha_creacion_web = (item.fecha_creacion_web !== null ? item.fecha_creacion_web : '0').toString();
			cliente_celular = (item.telefono !== null ? item.telefono : '').toString();
			cliente_fec_nac = (item.fec_nac !== null ? item.fec_nac : '').toString();
			cliente_idweb = ((item.web_id !== null && item.web_id !== '0') ? item.web_id : '').toString();
			cliente_web_full_name = item.web_full_name;
			cliente_player_id = item.player_id;
			cliente_ape_paterno = (item.apellido_paterno !== null ? item.apellido_paterno : '').toString();
			cliente_ape_materno = (item.apellido_materno !== null ? item.apellido_materno : '').toString();
			cliente_nombres = (item.nombre !== null ? item.nombre : '').toString();
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
		}
	});
	$('.div_tab_cliente_historial_cliente').removeClass('active');
	if (nuevo === true) {
		$('#div_tabs_contenedor_historial_cliente').append(
				'<div class="col-md-4 div_tab" id="' + id_cliente + '">' +
				'<div class="row div_tab_cliente_historial_cliente active">' +
				'<div class="col-md-10 col-xs-10 div_tab_cliente_historial_cliente_texto" onclick="seleccionar_cliente_historial_cliente(' + id_cliente + ', false)">' + titulo_tab + '</div>' +
				'<div class="col-md-2 col-xs-2 div_tab_cliente_historial_cliente_close" onclick="remover_cliente_historial_cliente(' + id_cliente + ')">' +
				'<div class="chrome-tab-close"></div>' +
				'</div>' +
				'</div>' +
				'</div>'
				);
	} else {
		$('#' + id_cliente).find('.div_tab_cliente_historial_cliente').addClass('active');
	}

	limpiar_campos_div_cliente_historial_cliente();
	$('#cliente_tipo_doc').val(cliente_tipo_doc);
	$('#cliente_num_doc').val(cliente_num_doc);
	$('#sec_tlv_hist_fecha_creacion_web').val(cliente_fecha_creacion_web);
	$('#cliente_celular').val(cliente_celular);
	$('#cliente_fec_nac').val(cliente_fec_nac);
	$('#cliente_idweb').val(cliente_idweb);
	$('#cliente_webfullname').val(cliente_web_full_name);
	$('#cliente_idjugador').val(cliente_player_id);
	$('#cliente_idwebc').val(cliente_idweb_c);
	$('#cliente_nombre').val(cliente_nombres);
	$('#cliente_apepaterno').val(cliente_ape_paterno);
	$('#cliente_apematerno').val(cliente_ape_materno);
	//console.log('cliente_local: '+cliente_local);
	$('#cliente_local').val(cliente_local);
	$('#cliente_local').select2().trigger('change');
	$('#bono_limite').val(bono_limite);
	bloquear_div_cliente_historial_cliente();

	listar_transacciones_historial_cliente(id_cliente);

}

 

function remover_cliente_historial_cliente(id_cliente) {
  
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
		
			return false;
		}
	});
}

function listar_transacciones_historial_cliente(id_cliente) {

	var div_sec_televentas_fecha_inicio = $('#div_sec_televentas_historial_cliente_fecha_inicio').val();
	var div_sec_televentas_fecha_fin = $('#div_sec_televentas_historial_cliente_fecha_fin').val();
	var tipo_balance = $("#sec_tlv_hist_cbx_tipo_balance").val();
	if (div_sec_televentas_fecha_inicio.length !== 10) {
		$("#div_sec_televentas_historial_cliente_fecha_inicio").focus();
		return false;
	}
	if (div_sec_televentas_fecha_fin.length !== 10) {
		$("#div_sec_televentas_historial_cliente_fecha_fin").focus();
		return false;
	}

	limpiar_tabla_transacciones_historial_cliente();
	$.post("/sys/set_televentas_historial_cliente.php", {
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
						$('#tabla_transacciones_historial_cliente').append(
								'<tr>' +
								'<td colspan="10" class="text-center">' + respuesta.status + '</td>' +
								'</tr>'
								);
						return false;
					}
					if (parseInt(respuesta.http_code) == 200) {
						gen_balance_total = respuesta.result_balance;
						gen_balance_dinero_at = respuesta.result_balance_dinero_at;
						respuesta.result_balance_dinero_at = respuesta.result_balance_dinero_at.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						$('#sec_tlv_his_span_dinero_at').html(respuesta.result_balance_dinero_at);

						respuesta.result_balance = respuesta.result_balance.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						$('#span_balance').html(respuesta.result_balance);

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
						 
					 
						if (respuesta.result_labels.length > 0) {
							$.each(respuesta.result_labels, function (index2, item2) {
							
								
								$('#div_labels').append(
										'<button type="button" class="btn btn-default" id="' + item2.id + '" ' +
										'   style="color: ' + invertColor(item2.color) + ';background-color: ' + item2.color + ';font-weight: bold;">' +
										item2.etiqueta + '&nbsp; &nbsp;</button>'
										);
								
								
							});
						}				 

						// TRANSACCIONES
						//var total_bonos=0;
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
									tipo += '<br>'+item.txn_id;
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
									tipo += '<br>'+item.txn_id;
								}

								if (parseInt(item.tipo_id) === 20) {
									if(parseInt(item.proveedor_id) == 4){
										tipo = 'PAGO JACKPOT BINGO';
										tipo += '<br>'+item.txn_id;
									}else{
										tipo = 'PAGO JACKPOT GOLDEN';
										tipo += '<br>'+item.txn_id;
									}
								}

								// Apuesta Retornada
								if (parseInt(item.tipo_id) === 19) {
									tipo += '<br>'+item.txn_id;
								}

								// Apuesta Cancelada
								if (parseInt(item.tipo_id) === 34) {
									tipo += '<br>'+item.txn_id;
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
										tipo += '<br>' + item.txn_id;
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
											btn_eliminar = '<button type="button" class="btn btn-danger btn_eliminar_transaccion_class" style="padding: 2px 5px;"' +
													'    onclick="abrir_modal_cancelar_retiro(' + item.trans_id + ', ' + item.tipo_operacion + ')" ' + clase_eliminar_bloqueo + '>' +
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
											btn_eliminar = '<button type="button" class="btn btn-danger btn_eliminar_transaccion_class" style="padding: 2px 5px;"' +
													'    onclick="abrir_modal_cancelar_retiro(' + item.trans_id + ', ' + item.tipo_operacion + ')" ' + clase_eliminar_bloqueo + '>' +
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
								item.nuevo_balance = item.nuevo_balance.replace(/\D/g, "")
										.replace(/([0-9])([0-9]{2})$/, '$1.$2')
										.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
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
											'    onclick="ver_voucher_historial_cliente(' + variables + ')">' +
											'<i class="icon icon-inline glyphicon glyphicon-piggy-bank"></i>' +
											'</button>';
								}else if(parseInt(item.tipo_id) === 17 || parseInt(item.tipo_id) === 18 || parseInt(item.tipo_id) === 36|| parseInt(item.tipo_id) === 37 ){
									btn_ver = '';
									
								}else{
									if(parseInt(item.tipo_id) != 4 && parseInt(item.proveedor_id) != 5){
										btn_ver = '<button type="button" class="btn btn-primary" style="padding: 2px 3px;"' +
												'    onclick="ver_voucher_historial_cliente(' + variables + ')">' +
												'<span class="fa fa-eye"></span>' +
												'</button>';
									}
								}

								if(parseInt(item.proveedor_id) === 9 && (parseInt(item.tipo_id) === 4 || parseInt(item.proveedor_id) === 5)){
									btn_ver = '<button type="button" class="btn btn-primary" style="padding: 2px 3px;"' +
											'    onclick="ver_voucher_historial_cliente(' + variables + ')">' +
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
								
								$('#tabla_transacciones_historial_cliente').append(
										'<tr>' +
										'<td class="text-center" width="10%">' + item.fecha_creacion + '</td>' +
										'<td class="text-center" width="10%" style="' + color + '">' + tipo + '</td>' +
										'<td class="text-center" width="10%">' + item.local + '</td>' +
										'<td class="text-center" width="10%">' + item.cuenta + '</td>' +
										'<td class="text-right" width="7%">' + item.monto_deposito + '</td>' +
										'<td class="text-right" width="5%">' + item.comision_monto + '</td>' +
										'<td class="text-right" width="7%">' + item.monto + '</td>' +
										'<td class="text-right" width="5%">' + item.bono_monto + '</td>' +
										'<td class="text-right" width="8%">' + total + '</td>' +
										'<td class="text-right" width="8%">' + item.nuevo_balance + '</td>' +
										'<td class="text-right" width="6%">' + item.saldo + '</td>' +
										'<td class="text-center" width="16%">' +
										'<div class="btn-group" role="group" aria-label="Basic example">' +
										btn_ver +
										btn_img +
										btn_img_propina+
										'</div>' +
										'</td>' +
										'</tr>'
										);
							});
							tabla_transacciones_datatable_formato('#tabla_transacciones_historial_cliente');
							$('#tabla_transacciones_historial_cliente').append(
								'<button class="btn btn-success" onclick="exportar_tabla_historial_cliente('+id_cliente+')" style="width: 100%;">' +
								'<span class="glyphicon glyphicon-download-alt"></span> Excel' +
								'</button>'
								);
						} else {
							$('#tabla_transacciones_historial_cliente').append(
									'<tr>' +
									'<td colspan="10" class="text-center">NO HAY DATOS</td>' +
									'</tr>'
									);
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

function exportar_tabla_historial_cliente(id_cliente) {
	var div_sec_televentas_fecha_inicio = $('#div_sec_televentas_historial_cliente_fecha_inicio').val();
	var div_sec_televentas_fecha_fin = $('#div_sec_televentas_historial_cliente_fecha_fin').val();
	var tipo_balance = $("#sec_tlv_hist_cbx_tipo_balance").val();
	if (div_sec_televentas_fecha_inicio.length !== 10) {
		$("#div_sec_televentas_historial_cliente_fecha_inicio").focus();
		return false;
	}
	if (div_sec_televentas_fecha_fin.length !== 10) {
		$("#div_sec_televentas_historial_cliente_fecha_fin").focus();
		return false;
	}

	var data = {
        "accion": "tabla_historial_cliente_export_xls",
        "id_cliente": id_cliente,
        "fecha_inicio": div_sec_televentas_fecha_inicio,
        "fecha_fin": div_sec_televentas_fecha_fin,
        "tipo_balance": tipo_balance
    }
	$.ajax({
        url: "/sys/set_televentas_historial_cliente.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            //console.log(respuesta);
            let obj = JSON.parse(resp);
            window.open(obj.path);
            loading(false);
        },
        error: function() {}
    });

}
 
function limpiar_campos_div_cliente_historial_cliente() {
	$('#cliente_tipo_doc').val('');
	$('#cliente_num_doc').val('');
	$('#sec_tlv_hist_fecha_creacion_web').val('');
	$('#cliente_celular').val('');
	$('#cliente_fec_nac').val('');
	$('#cliente_idweb').val('');
	$('#cliente_webfullname').val('');
	$('#cliente_idjugador').val('');
	$('#cliente_nombre').val('');
	$('#cliente_apepaterno').val('');
	$('#cliente_apematerno').val('');
	$('#bono_limite').val('');
	$('#cliente_local').val('3900');
	$('#cliente_local').select2().trigger('change');
	$('#sec_tlv_hist_cliente_tercero_titular').empty();
}

function limpiar_tabla_transacciones_historial_cliente() {
	 
	$('#tabla_transacciones_historial_cliente').html(
			'<thead>' +
			'<tr>' +
			'<td class="text-center" width="10%">Fecha</td>' +
			'<td class="text-center" width="10%">Tipo</td>' +
			'<td class="text-center" width="10%">Caja</td>' +
			'<td class="text-center" width="10%">Cuenta</td>' +
			'<td class="text-right" width="7%">Depósito</td>' +
			'<td class="text-right" width="5%">Comisión</td>' +
			'<td class="text-right" width="7%">Monto</td>' +
			'<td class="text-right" width="5%">Bono</td>' +
			'<td class="text-right" width="8%">Total</td>' +
			'<td class="text-right" width="8%">Nuevo Balance</td>' +
			'<td class="text-right" width="6%">Tipo Saldo</td>' +
			'<td class="text-right" width="6%">ACCIÓN</td>' +
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

  

function bloquear_div_cliente_historial_cliente() {
	$('#div_cliente_btn_habilitar').show();
	$('#div_cliente_btn_guardar').hide();
	$('#div_cliente_btn_cancelar').hide();

	$('#cliente_tipo_doc').attr('disabled', 'disabled');
	$('#cliente_num_doc').attr('disabled', 'disabled');
	$('#sec_tlv_hist_fecha_creacion_web').attr('disabled', 'disabled');
	$('#cliente_celular').attr('disabled', 'disabled');
	$('#cliente_fec_nac').attr('disabled', 'disabled');
	$('#cliente_idweb').attr('disabled', 'disabled');
	$('#cliente_idjugador').attr('disabled', 'disabled');
    $('#cliente_idwebc').attr('disabled', 'disabled');
	$('#cliente_nombre').attr('disabled', 'disabled');
	$('#cliente_apepaterno').attr('disabled', 'disabled');
	$('#cliente_apematerno').attr('disabled', 'disabled');
	$('#bono_limite').attr('disabled', 'disabled');
	$('#sec_tlv_hist_cliente_tercero_titular').attr('disabled', 'disabled');
}
 
 

function tabla_transacciones_historial_cliente_datatable_formato(id) {
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
  
 
function sec_tlv_estado_cuenta_historial_cliente() {
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
		url: "/sys/set_televentas_historial_cliente.php",
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

function pad(n, width, z) { z = z || '0'; n = n + ''; return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n; }

$("#sec_tlv_hist_cbx_tipo_balance").on("change", function(){
	var tls_tipo_balance_id_temp = $("#sec_tlv_hist_cbx_tipo_balance").val();
	listar_transacciones_historial_cliente(gen_cliente_id);
})

function sec_tlv_hist_cargar_titular_abono_cliente() {
	$('#sec_tlv_hist_cliente_tercero_titular').empty();

	var data = {
		"accion": "sec_tlv_hist_obtener_televentas_titular_abono_reg",
		"id_cliente": gen_cliente_id 
	}

	$.ajax({
		url: "/sys/set_televentas_historial_cliente.php",
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
				$.each(respuesta.result, function(index, item) {
					$('#sec_tlv_hist_cliente_tercero_titular').append(
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

function ver_voucher_historial_cliente(trans_id, txn_id, tipo_id, web_id, cliente, monto_deposito, monto_comision, monto, bono_nombre, bono_monto, total,
		fecha_hora, estado, local, obs_cajero, obs_validador, obs_supervisor, tipo_rechazo, registro_deposito, num_operacion, banco_pago,
		proveedor_id, proveedor_name, usuario, cajero, tipo_operacion, motivo_dev, img_comision, tipo_jugada) {

	$('#HisCli_modal_voucher_apuesta_logo').removeAttr('src');
	$('#HisCli_modal_voucher_apuesta_logo').attr('src',"img/logo_at_voucher.jpeg");

	$('#HisCli_modal_voucher_apuesta_tipo_torito_div').hide();
	$('#HisCli_modal_voucher_apuesta_estado_div').show();

	if ([1,26].includes(parseInt(tipo_id))) { //Deposito

		$('#HisCli_modal_ov_deposito_aprobado').hide();
		$('#HisCli_modal_ov_obs_cajero_div').hide();
		$('#HisCli_modal_ov_rechazo_div').hide();
		$('#HisCli_modal_ov_obs_validador_div').hide();
		$('#HisCli_modal_ov_obs_supervisor_div').hide();
		$('#HisCli_sec_tlv_comision_comprobante_div').hide();

		$('#HisCli_modal_ov_caja').html('');
		$('#HisCli_modal_ov_fecha_hora').html('');
		$('#HisCli_modal_ov_num_operacion').html('');
		$('#HisCli_modal_ov_id_web').html('');
		$('#HisCli_modal_ov_cliente').html('');
		$('#HisCli_modal_ov_monto_deposito').html('');
		$('#HisCli_modal_ov_monto_comision').html('');
		$('#HisCli_modal_ov_monto_real').html('');
		$('#HisCli_modal_ov_nombre_bono').html('');
		$('#HisCli_modal_ov_monto_bono').html('');
		$('#HisCli_modal_ov_tipo_jugada').html('');
		$('#HisCli_modal_ov_monto_recarga').html('');

		$('#HisCli_modal_ov_obs_cajero').html('');
		$('#HisCli_modal_ov_rechazo').html('');
		$('#HisCli_modal_ov_obs_validador').html('');
		$('#HisCli_modal_ov_obs_supervisor').html('');


		$('#HisCli_modal_ov_caja').html(local);
		$('#HisCli_modal_ov_fecha_hora').html(registro_deposito.substr(0, 16));
		$('#HisCli_modal_ov_num_operacion').html(num_operacion);
		$('#HisCli_modal_ov_id_web').html(web_id);
		$('#HisCli_modal_ov_cliente').html(cliente);
		$('#HisCli_modal_ov_monto_deposito').html(monto_deposito);
		$('#HisCli_modal_ov_monto_comision').html(monto_comision);
		$('#HisCli_modal_ov_monto_real').html(monto);
		$('#HisCli_modal_ov_nombre_bono').html(bono_nombre);
		$('#HisCli_modal_ov_monto_bono').html(bono_monto);
		$('#HisCli_modal_ov_tipo_jugada').html(tipo_jugada);
		$('#HisCli_modal_ov_monto_recarga').html(total);

		$('#HisCli_modal_ov_obs_cajero').html(obs_cajero);
		$('#HisCli_modal_ov_rechazo').html(tipo_rechazo);
		$('#HisCli_modal_ov_obs_validador').html(obs_validador);
		$('#HisCli_modal_ov_obs_supervisor').html(obs_supervisor);

		if (parseInt(estado) === 0) {
			$('#HisCli_modal_ov_obs_cajero_div').show();
			$('#HisCli_modal_ov_deposito_aprobado').show();
		}
		if (parseInt(estado) === 1) {
			$('#HisCli_modal_ov_deposito_aprobado').show();
			$('#HisCli_modal_ov_obs_validador_div').show();
			if(img_comision.length > 0){
				$('#HisCli_sec_tlv_img_comprobante_comision').removeAttr('src');
				$('#HisCli_sec_tlv_img_comprobante_comision').attr('src', 'files_bucket/depositos/' + img_comision);
				$("#HisCli_sec_tlv_img_comprobante_comision").imgViewer2();
				$('#HisCli_sec_tlv_comision_comprobante_div').show();
			}else{
				$('#HisCli_sec_tlv_comision_comprobante_div').hide();
			}
		}
		if (parseInt(estado) === 2) {
			$('#HisCli_modal_ov_obs_cajero_div').show();
			$('#HisCli_modal_ov_obs_validador_div').show();
			$('#HisCli_modal_ov_rechazo_div').show();
		}
		if (parseInt(estado) === 3) {
			$('#HisCli_modal_ov_obs_cajero_div').show();
			$('#HisCli_modal_ov_obs_validador_div').show();
			$('#HisCli_modal_ov_obs_supervisor_div').show();
		}

		$('#HisCli_modal_observacion_validador').modal();
	}

	if (parseInt(tipo_id) === 2 || parseInt(tipo_id) === 3) { //Recarga web y su rollback
		$('#HisCli_modal_voucher_caja').html('');
		$('#HisCli_modal_voucher_idtransaccion').html('');
		$('#HisCli_modal_voucher_fechahora').html('');
		$('#HisCli_modal_voucher_idweb').html('');
		$('#HisCli_modal_voucher_cliente').html('');
		$('#HisCli_modal_voucher_monto').html('');
		$('#HisCli_modal_voucher_bono_nombre').html('');
		$('#HisCli_modal_voucher_bono').html('');
		$('#HisCli_modal_voucher_total').html('');



		$('#HisCli_modal_voucher_caja').html(local);
		$('#HisCli_modal_voucher_idtransaccion').html(txn_id);
		$('#HisCli_modal_voucher_fechahora').html(fecha_hora);
		$('#HisCli_modal_voucher_idweb').html(web_id);
		$('#HisCli_modal_voucher_cliente').html(cliente);
		$('#HisCli_modal_voucher_monto').html(monto);
		$('#HisCli_modal_voucher_total').html(total);

		$('#HisCli_modal_voucher').modal();

		$.post("/sys/set_televentas.php", {
			accion: "obtener_recarga_x_bono",
			recarga_id: trans_id
		})
				.done(function (data) {
					try {
						//console.log(data);
						var respuesta = JSON.parse(data);
						if (parseInt(respuesta.http_code) == 200) {
							$('#HisCli_modal_voucher_bono_nombre').html(respuesta.bono_nombre);
							$('#HisCli_modal_voucher_bono').html(respuesta.bono_monto);
						} else {
							$('#HisCli_modal_voucher_bono_nombre').html('Ninguno');
							$('#HisCli_modal_voucher_bono').html('0.00');
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
			$('#HisCli_modal_voucher_apuesta_altenar_fecha').html('');
			$('#HisCli_modal_voucher_apuesta_altenar_hora').html('');
			$('#HisCli_modal_voucher_apuesta_altenar_id_bet').html('');
			$('#HisCli_modal_voucher_apuesta_altenar_caja').html('');
			$('#HisCli_modal_voucher_apuesta_altenar_monto').html('');
			$('#HisCli_modal_voucher_apuesta_altenar_qr_id_bet').html('');
			$('#HisCli_modal_voucher_apuesta_altenar_tabla').html('');
			$('#HisCli_modal_voucher_apuesta_altenar_qr').html('');
			$('#HisCli_modal_voucher_apuesta_altenar_estado').html('');


			$('#HisCli_modal_voucher_apuesta_altenar_fecha').html('Fecha: ' + temp_fecha_format);
			$('#HisCli_modal_voucher_apuesta_altenar_hora').html('Hora: ' + fecha_hora.substr(-8));
			$('#HisCli_modal_voucher_apuesta_altenar_id_bet').html('ID de apuesta: ' + txn_id);
			$('#HisCli_modal_voucher_apuesta_altenar_cliente_name').html('Cliente: ' + cliente);
			$('#HisCli_modal_voucher_apuesta_altenar_caja').html(local);
			$('#HisCli_modal_voucher_apuesta_altenar_monto').html(monto + ' PEN');
			$('#HisCli_modal_voucher_apuesta_altenar_qr_id_bet').html(txn_id);

			$('#HisCli_modal_voucher_apuesta_altenar_qr').html('<img id="HisCli_modal_voucher_apuesta_altenar_qr_img" height="100" />');
			JsBarcode("#HisCli_modal_voucher_apuesta_altenar_qr_img", txn_id);

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
						$('#HisCli_modal_voucher_apuesta_altenar_tabla').append(
								'<tr class="modal_voucher_apuesta_altenar_tabla_tr" style="text-align:center;">' +
								'<td><b>' + tipo_apuesta_texto + '</b></td>' +
								'</tr>'
								);
						$.each(respuesta.result_calimaco, function (index, item) {
							$('#HisCli_modal_voucher_apuesta_altenar_tabla').append(
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
						$('#HisCli_modal_voucher_apuesta_altenar_tabla').append(
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
						$('#HisCli_modal_voucher_apuesta_altenar_estado').css('color', respuesta.status_color);
						$('#HisCli_modal_voucher_apuesta_altenar_estado').html(respuesta.status);
						if (parseInt(estado) === 3) {
							$('#HisCli_modal_voucher_apuesta_altenar_estado').css('color', 'red');
							$('#HisCli_modal_voucher_apuesta_altenar_estado').html('ANULADO');
						}
						$('#HisCli_modal_voucher_apuesta_altenar').modal();
					} else {
						$('#HisCli_modal_voucher_apuesta_altenar').modal();
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
			$('#HisCli_modal_voucher_apuesta_logo').removeAttr("src");
			$('#HisCli_modal_voucher_apuesta_logo').attr('src',"img/logo_torito.png");

			$('#HisCli_modal_voucher_apuesta_caja').html('');
			$('#HisCli_modal_voucher_apuesta_proveedor').html('');
			$('#HisCli_modal_voucher_apuesta_tipo_torito').html('');
			$('#HisCli_modal_voucher_apuesta_idtransaccion').html('');
			$('#HisCli_modal_voucher_apuesta_fechahora').html('');
			$('#HisCli_modal_voucher_apuesta_estado').html('');
			$('#HisCli_modal_voucher_apuesta_monto').html('');

			$('#HisCli_modal_voucher_apuesta_tipo_torito_div').show();
			$('#HisCli_modal_voucher_apuesta_estado_div').hide();

			$('#HisCli_modal_voucher_apuesta_caja').html(local);
			$('#HisCli_modal_voucher_apuesta_proveedor').html(proveedor_name);
			$('#HisCli_modal_voucher_apuesta_tipo_torito').html(obs_cajero);
			$('#HisCli_modal_voucher_apuesta_idtransaccion').html(txn_id);
			$('#HisCli_modal_voucher_apuesta_fechahora').html(fecha_hora);
			$('#HisCli_modal_voucher_apuesta_monto').html(monto);

			$('#HisCli_modal_voucher_apuesta').modal();
		} else {
			$('#HisCli_modal_voucher_apuesta_caja').html('');
			$('#HisCli_modal_voucher_apuesta_proveedor').html('');
			$('#HisCli_modal_voucher_apuesta_idtransaccion').html('');
			$('#HisCli_modal_voucher_apuesta_fechahora').html('');
			$('#HisCli_modal_voucher_apuesta_estado').html('');
			$('#HisCli_modal_voucher_apuesta_monto').html('');

			$('#HisCli_modal_voucher_apuesta_estado').html('Apuesta');

			$('#HisCli_modal_voucher_apuesta_caja').html(local);
			$('#HisCli_modal_voucher_apuesta_proveedor').html(proveedor_name);
			$('#HisCli_modal_voucher_apuesta_idtransaccion').html(txn_id);
			$('#HisCli_modal_voucher_apuesta_fechahora').html(fecha_hora);
			$('#HisCli_modal_voucher_apuesta_monto').html(monto);

			$('#HisCli_modal_voucher_apuesta').modal();
		}
	}

	if (parseInt(tipo_id) === 7 && parseInt(proveedor_id)===2) { // Apuesta Altenar ANULADA
		$('#HisCli_modal_voucher_ap_anulada_caja').html('');
		$('#HisCli_modal_voucher_ap_anulada_promotor').html('');
		$('#HisCli_modal_voucher_ap_anulada_proveedor').html('');
		$('#HisCli_modal_voucher_ap_anulada_idtransaccion').html('');
		$('#HisCli_modal_voucher_ap_anulada_fechahora').html('');
		$('#HisCli_modal_voucher_ap_anulada_estado').html('');
		$('#HisCli_modal_voucher_ap_anulada_monto').html('');

		$('#HisCli_modal_voucher_ap_anulada_estado').html('Apuesta');

		$('#HisCli_modal_voucher_ap_anulada_caja').html(local);
		$('#HisCli_modal_voucher_ap_anulada_promotor').html(cajero);
		$('#HisCli_modal_voucher_ap_anulada_proveedor').html(proveedor_name);
		$('#HisCli_modal_voucher_ap_anulada_idtransaccion').html(txn_id);
		$('#HisCli_modal_voucher_ap_anulada_fechahora').html(fecha_hora);
		$('#HisCli_modal_voucher_ap_anulada_monto').html(monto);

		$('#HisCli_modal_voucher_ap_anulada').modal();
	}else if(parseInt(tipo_id) === 7 && parseInt(proveedor_id) === 4){
		sec_tlv_bingo_get_voucher(trans_id, estado);
	}

	if ([5, 19, 20].includes(parseInt(tipo_id))) { //Apuesta Pagada, Retornada, Jackpot
		$('#HisCli_modal_voucher_apuesta_caja').html('');
		$('#HisCli_modal_voucher_apuesta_proveedor').html('');
		$('#HisCli_modal_voucher_apuesta_idtransaccion').html('');
		$('#HisCli_modal_voucher_apuesta_fechahora').html('');
		$('#HisCli_modal_voucher_apuesta_estado').html('');
		$('#HisCli_modal_voucher_apuesta_monto').html('');

		if (parseInt(tipo_id) === 5) {
			$('#HisCli_modal_voucher_apuesta_estado').html('Apuesta Pagada');
		}
		if (parseInt(tipo_id)===19) {
			$('#HisCli_modal_voucher_apuesta_estado').html('Apuesta Retornada');
		}
		if (parseInt(tipo_id)===20) {
			$('#HisCli_modal_voucher_apuesta_estado').html('Apuesta Pagada Jackpot');
		}

		$('#HisCli_modal_voucher_apuesta_caja').html(local);
		$('#HisCli_modal_voucher_apuesta_proveedor').html(proveedor_name);
		$('#HisCli_modal_voucher_apuesta_idtransaccion').html(txn_id);
		$('#HisCli_modal_voucher_apuesta_fechahora').html(fecha_hora);
		$('#HisCli_modal_voucher_apuesta_monto').html(monto);

		$('#HisCli_modal_voucher_apuesta').modal();
	}

	if ([9, 11, 12, 13].includes(parseInt(tipo_id))) {
		$('#HisCli_sec_tlv_title_solicitud_retiro').html('SOLICITUD DE RETIRO');
		$("#HisCli_sec_tlv_id_trans_retiro").val(trans_id);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_caja').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_fechahora').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_estado').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_monto').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_motivo_rechazo').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_rechazo').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_obs_pagador').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_supervisor').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_obs_supervisor').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_voucher').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_motivo_cancelacion').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_cancelacion').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_banco_pago_div').css({"display": "none"});
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
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_voucher').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_banco_pago_div').css({"display": "block"});
			cargar_voucher_retiro_pagado(trans_id);
		} else if (parseInt(tipo_id) == 12 && parseInt(estado) == 3) {
			estado_nombre = "RECHAZADA";
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_motivo_rechazo').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_rechazo').html(tipo_rechazo);
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "block"});
		} else if (parseInt(tipo_id) == 13 && parseInt(estado) == 4) {
			estado_nombre = "CANCELADA";
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_supervisor').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_motivo_cancelacion').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_obs_supervisor').html(obs_supervisor);
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_cancelacion').html(tipo_rechazo);
		} else if (parseInt(estado) == 5) {
			estado_nombre = "EN PROCESO";
		}

		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_estado').html(estado_nombre);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_caja').html(local);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_fechahora').html(fecha_hora);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_monto').html(monto);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_obs_pagador').html(obs_validador);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_banco_pago').html(banco_pago);
		var operacion_name = '';		
		if(tipo_operacion == 0 || tipo_operacion == 1){
			operacion_name = 'PAGO';
		}else if(tipo_operacion == 2){
			operacion_name = 'DEVOLUCIÓN';
		}
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_tipo_operacion').html(operacion_name);		
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_devolucion').html(motivo_dev);

		$('#HisCli_sec_tlv_modal_solicitud_de_retiro').modal();
	}

	if (parseInt(tipo_id) === 14) {
		$('#HisCli_modal_voucher_td_caja').html('');
		$('#HisCli_modal_voucher_td_proveedor').html('');
		$('#HisCli_modal_voucher_td_idtransaccion').html('');
		$('#HisCli_modal_voucher_td_fechahora').html('');
		$('#HisCli_modal_voucher_td_estado').html('');
		$('#HisCli_modal_voucher_td_monto').html('');

		$('#HisCli_modal_voucher_td_estado').html('Terminal Deposit - In');

		$('#HisCli_modal_voucher_td_caja').html(local);
		$('#HisCli_modal_voucher_td_proveedor').html(proveedor_name);
		$('#HisCli_modal_voucher_td_idtransaccion').html(txn_id);
		$('#HisCli_modal_voucher_td_fechahora').html(fecha_hora);
		$('#HisCli_modal_voucher_td_monto').html(monto);

		$('#HisCli_modal_voucher_terminal_deposit').modal();
	}

    if ([15,16].includes(parseInt(tipo_id))){
        $('#HisCli_modal_voucher_cancer_caja').html('');
        $('#HisCli_modal_voucher_cancer_proveedor').html('');
        $('#HisCli_modal_voucher_cancer_txn').html('');
        $('#HisCli_modal_voucher_cancer_fechahora').html('');
        $('#HisCli_modal_voucher_cancer_estado').html('');
        $('#HisCli_modal_voucher_cancer_monto').html('');

        var estado_nombre = 'Activo';
        if(parseInt(estado)===3){
        	estado_nombre = 'Anulado';
        }

        $('#HisCli_modal_voucher_cancer_titulo').html('¡Gracias ' + $('#cliente_nombre').val().split(" ")[0] + '!');
        $('#HisCli_modal_voucher_cancer_caja').html(local);
        $('#HisCli_modal_voucher_cancer_proveedor').html(proveedor_name);
        $('#HisCli_modal_voucher_cancer_txn').html(pad(txn_id, 10));
        $('#HisCli_modal_voucher_cancer_fechahora').html(fecha_hora);
        $('#HisCli_modal_voucher_cancer_estado').html(estado_nombre);
        $('#HisCli_modal_voucher_cancer_monto').html(monto);

        $('#HisCli_modal_voucher_cancer').modal();
    }

	if ([21, 22, 24, 25].includes(parseInt(tipo_id)) /* parseInt(tipo_id) === 21 */){ // Propinas
		$("#HisCli_sec_tlv_id_trans_propina").val(trans_id);
        $('#HisCli_modal_voucher_propina_caja').html('');
        $('#HisCli_modal_voucher_propina_proveedor').html('');
        $('#HisCli_modal_voucher_propina_txn').html('');
        $('#HisCli_modal_voucher_propina_fechahora').html('');
        $('#HisCli_modal_voucher_propina_estado').html('');
        $('#HisCli_modal_voucher_propina_monto').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_propina_obs_cajero').html('');

		$('#HisCli_sec_tlv_modal_solicitud_de_propina_div_obs_pagador').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_propina_voucher').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_propina_banco_pago_div').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_propina_div_motivo_rechazo').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_propina_motivo_rechazo').html('');
		var estado_nombre = "";

		// $('#modal_voucher_propina_titulo').html('¡Gracias ' + $('#cliente_nombre').val().split(" ")[0] + '!');
		$('#HisCli_modal_voucher_propina_titulo').html('SOLICITUD DE PROPINA');
        $('#HisCli_modal_voucher_propina_caja').html(local);
        $('#HisCli_modal_voucher_propina_proveedor').html(proveedor_name);
        $('#HisCli_modal_voucher_propina_txn').html(pad(txn_id, 10));
        $('#HisCli_modal_voucher_propina_fechahora').html(fecha_hora);
        $('#HisCli_modal_voucher_propina_estado').html(estado_nombre);
        $('#HisCli_modal_voucher_propina_monto').html(monto);
		$('#HisCli_sec_tlv_modal_solicitud_de_propina_estado').html(estado_nombre);
		$('#HisCli_sec_tlv_modal_solicitud_de_propina_obs_cajero').html(obs_cajero);
		$('#HisCli_sec_tlv_modal_solicitud_de_propina_obs_pagador').html(obs_validador);
		$('#HisCli_sec_tlv_modal_solicitud_de_propina_banco_pago').html(banco_pago);
		var operacion_name = '';		
		if(tipo_operacion == 0 || tipo_operacion == 1){
			operacion_name = 'PAGO';
		}else if(tipo_operacion == 2){
			operacion_name = 'DEVOLUCIÓN';
		}
		$('#HisCli_sec_tlv_modal_solicitud_de_propina_tipo_operacion').html(operacion_name);		
		$('#HisCli_sec_tlv_modal_solicitud_de_propina_motivo_devolucion').html(motivo_dev);

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
			$('#HisCli_modal_voucher_propina_txn_div').hide();
			$('#HisCli_sec_tlv_modal_solicitud_de_propina_div_obs_cajero').css({"display": "none"});
			$('#HisCli_sec_tlv_modal_solicitud_de_propina_div_obs_pagador').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_propina_voucher').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_propina_banco_pago_div').css({"display": "block"});
			cargar_voucher_de_propina_pagada(trans_id); // Trae el voucher del validador, tanto de retiro como propina
		} else if (parseInt(tipo_id) == 25 && parseInt(estado) == 3) {
			estado_nombre = "RECHAZADA";
			$('#HisCli_modal_voucher_propina_txn_div').hide();
			$('#HisCli_sec_tlv_modal_solicitud_de_propina_div_motivo_rechazo').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_propina_motivo_rechazo').html(tipo_rechazo);
			// $('#sec_tlv_modal_solicitud_de_propina_div_obs_cajero').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_propina_div_obs_pagador').css({"display": "block"});
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

        

        $('#HisCli_modal_voucher_propina').modal();
	}

	if ([28,29,30,31].includes(parseInt(tipo_id))) {
		$('#HisCli_sec_tlv_title_solicitud_retiro').html('SOLICITUD DE DEVOLUCIÓN');
		$("#HisCli_sec_tlv_id_trans_retiro").val(trans_id);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_caja').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_fechahora').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_estado').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_monto').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_motivo_rechazo').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_rechazo').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_obs_pagador').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_supervisor').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_obs_supervisor').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_voucher').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_motivo_cancelacion').css({"display": "none"});
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_cancelacion').html('');
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_banco_pago_div').css({"display": "none"});
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
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_voucher').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_banco_pago_div').css({"display": "block"});
			cargar_voucher_retiro_pagado(trans_id);
		} else if (parseInt(tipo_id) == 30 && parseInt(estado) == 3) {
			estado_nombre = "RECHAZADA";
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_motivo_rechazo').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_rechazo').html(tipo_rechazo);
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_pagador').css({"display": "block"});
		} else if (parseInt(tipo_id) == 31 && parseInt(estado) == 4) {
			estado_nombre = "CANCELADA";
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_obs_supervisor').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_div_motivo_cancelacion').css({"display": "block"});
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_obs_supervisor').html(obs_supervisor);
			$('#HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_cancelacion').html(tipo_rechazo);
		} else if (parseInt(estado) == 5) {
			estado_nombre = "EN PROCESO";
		}

		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_estado').html(estado_nombre);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_caja').html(local);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_fechahora').html(fecha_hora);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_monto').html(monto);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_obs_pagador').html(obs_validador);
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_banco_pago').html(banco_pago);
		var operacion_name = '';		
		if(tipo_operacion == 0 || tipo_operacion == 1){
			operacion_name = 'PAGO';
		}else if(tipo_operacion == 2){
			operacion_name = 'DEVOLUCIÓN';
		}
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_tipo_operacion').html(operacion_name);		
		$('#HisCli_sec_tlv_modal_solicitud_de_retiro_motivo_devolucion').html(motivo_dev);

		$('#HisCli_sec_tlv_modal_solicitud_de_retiro').modal();
	}

	if (parseInt(tipo_id) === 32){ // Pago Sorteo del Mundial
        $('#HisCli_modal_voucher_sorteo_mundial_cliente').html('');
        $('#HisCli_modal_voucher_sorteo_mundial_caja').html('');
        $('#HisCli_modal_voucher_sorteo_mundial_proveedor').html('');
        $('#HisCli_modal_voucher_sorteo_mundial_txn').html('');
        $('#HisCli_modal_voucher_sorteo_mundial_fechahora').html('');
        $('#HisCli_modal_voucher_sorteo_mundial_estado').html('');
        $('#HisCli_modal_voucher_sorteo_mundial_monto').html('');

        $('#HisCli_modal_voucher_sorteo_mundial_titulo').html('¡Sorteo del Mundial Qatar 2022!');
        $('#HisCli_modal_voucher_sorteo_mundial_cliente').html($('#cliente_nombre').val() + ' ' + $('#cliente_apepaterno').val() + ' ' + $('#cliente_apematerno').val() );
        $('#HisCli_modal_voucher_sorteo_mundial_caja').html(local);
        $('#HisCli_modal_voucher_sorteo_mundial_proveedor').html(proveedor_name);
        $('#HisCli_modal_voucher_sorteo_mundial_txn').html(pad(txn_id, 10));
        $('#HisCli_modal_voucher_sorteo_mundial_fechahora').html(fecha_hora);
        $('#HisCli_modal_voucher_sorteo_mundial_monto').html(monto);

        $('#HisCli_modal_voucher_sorteo_mundial').modal();
    }

    if (parseInt(tipo_id) === 33){ // Terminal Tambo
    	var dia_tb = fecha_hora.substring(8,10) + '/' + fecha_hora.substring(5,7) + '/' + fecha_hora.substring(0,4);
    	$('#HisCli_sec_tlv_modal_tambo_fecha').html(dia_tb);
    	$('#HisCli_sec_tlv_modal_tambo_hora').html(fecha_hora.substring(11, 19));
    	$('#HisCli_sec_tlv_modal_tambo_cliente').html($('#cliente_nombre').val() + ' ' + $('#cliente_apepaterno').val() + ' ' + $('#cliente_apematerno').val());
    	$('#HisCli_sec_tlv_modal_tambo_monto').html(parseFloat(monto) + ' PEN');
    	$('#HisCli_sec_tlv_modal_tambo_id_barcode').val(num_operacion);
    	JsBarcode("#HisCli_sec_tlv_img_barcode_tambo_voucher", num_operacion);
        $('#HisCli_sec_tlv_modal_voucher_terminal_tambo').modal();
    }
}