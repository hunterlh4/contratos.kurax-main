function sec_reportes_cuadre_balance() {
	if (sec_id == 'reportes' && sub_sec_id=='cuadre_balance') {
		$('#SecRepCBal_fecha_inicio').datetimepicker({ format: 'YYYY-MM-DD' });
		$('#SecRepCBal_fecha_inicio').val($('#SecRepCBal_g_fecha_inicio').val());

		$('#SecRepCBal_fecha_fin').datetimepicker({ format: 'YYYY-MM-DD' });
		$('#SecRepCBal_fecha_fin').val($('#SecRepCBal_g_fecha_fin').val());

		$('#SecRepCBal_hora').datetimepicker({ format: 'HH:mm:ss' });
		$('#SecRepCBal_hora').val($('#SecRepCBal_g_hora_actual').val());

		$('#SecRepCBal_btn_buscar').click(function(){
			SecRptCBal_buscar_dt();
			//SecRptCBal_resumen();
		});
		$('#SecRepCBal_btn_exportar').click(function(){
			SecRptCBal_exportar_xls();
		});

		$('#SecRepCBal_fecha_inicio').change(function () {
			var var_fecha_change = $('#SecRepCBal_fecha_inicio').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepCBal_fecha_inicio").val($("#SecRepCBal_g_fecha_inicio").val());
			}
		});

		$('#SecRepCBal_fecha_fin').change(function () {
			var var_fecha_change = $('#SecRepCBal_fecha_fin').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepCBal_fecha_fin").val($("#SecRepCBal_g_fecha_fin").val());
			}
		});

		$('#SecRepCBal_hora').change(function () {
			var var_hora_change = $('#SecRepCBal_hora').val();
			if (!(parseInt(var_hora_change.length) > 0)) {
				$("#SecRepCBal_hora").val($("#SecRepCBal_g_hora_actual").val());
			}
		});

		$('#SecRepCBal_cant_clientes').val('0');
		$('#SecRepCBal_total_balance').val('0.00');
		$('#SecRepCBal_total_retiro').val('0.00');
	}
}

function SecRptCBal_exportar_xls(){

	var fecha_inicio = $.trim($("#SecRepCBal_fecha_inicio").val());
	var fecha_fin = $.trim($("#SecRepCBal_fecha_fin").val());
	var cliente_tipo = $("#SecRepCBal_cliente_tipo").val();
	var cliente_texto = $("#SecRepCBal_cliente_texto").val();

	if(parseInt(cliente_tipo) != 0){
		if(cliente_texto.length == 0){
			swal('Aviso', 'Debe ingresar un cliente a buscar', 'warning');
			return false;
		}
		if(cliente_texto.length < 3){
			swal('Aviso', 'El dato debe contener al menos 3 carácteres', 'warning');
			return false;
		}
	}
	var data = {
		"accion": "SecRptCBal_exportar_xls",
		"fecha_inicio": fecha_inicio,
		"fecha_fin": fecha_fin,
		"cliente_tipo": cliente_tipo,
		"cliente_texto": cliente_texto
	}

	$.ajax({
		url: "/sys/get_reportes_cuadre_balance.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			let obj = JSON.parse(resp);
			window.open(obj.path);
			loading(false);
		},
		error: function() {}
	});
	 
}

// LISTAS TRANSACCIONES
function SecRptCBal_buscar_dt() {
	var SecRepCBal_fecha_inicio = $.trim($("#SecRepCBal_fecha_inicio").val());
	var SecRepCBal_fecha_fin = $.trim($("#SecRepCBal_fecha_fin").val());
	var SecRepCBal_cliente_tipo = $("#SecRepCBal_cliente_tipo").val();
	var SecRepCBal_cliente_texto = $("#SecRepCBal_cliente_texto").val();

	if(parseInt(SecRepCBal_cliente_tipo) != 0){
		if(SecRepCBal_cliente_texto.length == 0){
			swal('Aviso', 'Debe ingresar un cliente a buscar', 'warning');
			return false;
		}
		if(SecRepCBal_cliente_texto.length < 3){
			swal('Aviso', 'El dato debe contener al menos 3 carácteres', 'warning');
			return false;
		}
	}
	var data = {
		"accion": "SecRptCBal_listar_registros",
		"fecha_inicio": SecRepCBal_fecha_inicio,
		"fecha_fin": SecRepCBal_fecha_fin,
		"cliente_tipo": SecRepCBal_cliente_tipo,
		"cliente_texto": SecRepCBal_cliente_texto
	}
	$('#SecRepCBal_tabla_registros').html('');
	$('#SecRepCBal_tabla_diferencia').html('');
	 
	$.ajax({
		url: "/sys/get_reportes_cuadre_balance.php",
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
				$('#SecRepCBal_tabla_registros').append(
					'<tr>' +
					'	<td class="text-center" colspan="2">' + respuesta.status + '</td>' +
					'</tr>' +
					'</tbody>'
				);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				if (respuesta.result.length > 0) {
					var balance_inicial = parseFloat(respuesta.balance_inicial);
					var balance_cierre_teorico = parseFloat(respuesta.balance_inicial);
					var balance_cierre = parseFloat(respuesta.balance_cierre);
					var balance_diferencia = 0;

					respuesta.balance_inicial = respuesta.balance_inicial.replace(/\D/g, "").replace(/([0-9])([0-9]{2})$/, '$1.$2').replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
					$('#SecRepCBal_tabla_registros').append(
						'<tr style="text-align: left; background-color: #c1eaf2; font-weight: bold;">' +
						'	<td> Balance ' + SecRepCBal_fecha_inicio + ' </td>' +
						'	<td style="text-align: center;" colspan="3">' +
						'	S/ ' + respuesta.balance_inicial + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>' +
						'</tr>' +
						'</tbody>'
					);

					$.each(respuesta.result, function (index, item) {
						let bg_signo = "";
						if(item.descripcion == "-"){
							bg_signo = "#DE4F45";
							balance_cierre_teorico = balance_cierre_teorico - parseFloat(item.monto);
						}else if (item.descripcion == "+"){
							bg_signo = "#43ba58";
							balance_cierre_teorico = balance_cierre_teorico + parseFloat(item.monto);
						}
						item.monto = item.monto.replace(/\D/g, "").replace(/([0-9])([0-9]{2})$/, '$1.$2').replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						$('#SecRepCBal_tabla_registros').append(
							'<tr>' +
							'	<td style="text-align: left;">' + item.nombre + '</td>' +
							'	<td class="text-right">' + item.cant + '&nbsp;</td>' +
							'	<td class="text-right">S/ ' + item.monto + '&nbsp;&nbsp;</td>' +
							'	<td class="text-center" style="background-color: ' + bg_signo + '; width: 20px; color: white; font-weight: bold;">(' + item.descripcion + ')</td>' +
							'</tr>' +
							'</tbody>'
						);
					});

					balance_diferencia = balance_cierre_teorico - balance_cierre;
					var balance_diferencia_signo = '';
					if(parseFloat(balance_diferencia)<0){
						balance_diferencia_signo = '-';
					}
					balance_cierre_teorico = balance_cierre_teorico.toFixed(2).replace(/\D/g, "").replace(/([0-9])([0-9]{2})$/, '$1.$2').replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
					respuesta.balance_cierre = respuesta.balance_cierre.replace(/\D/g, "").replace(/([0-9])([0-9]{2})$/, '$1.$2').replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
					balance_diferencia = balance_diferencia.toFixed(2).replace(/\D/g, "").replace(/([0-9])([0-9]{2})$/, '$1.$2').replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
					$('#SecRepCBal_tabla_registros').append(
						'<tr style="text-align: left; background-color: #edb376; font-weight: bold;">' +
						'	<td> Balance Cierre Teórico </td>' +
						'	<td style="text-align: right;" colspan="3">' +
						'	S/ ' + balance_cierre_teorico + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>' +
						'</tr>' +
						'<tr style="text-align: left; background-color: #7ac3e8; font-weight: bold;">' +
						'	<td> Balance Cierre (' + SecRepCBal_fecha_fin + ') </td>' +
						'	<td style="text-align: right;" colspan="3">' +
						'	S/ ' + respuesta.balance_cierre + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>' +
						'</tr>' +
						'<tr style="text-align: left; background-color: #d8db2e; font-weight: bold;">' +
						'	<td> Diferencia </td>' +
						'	<td style="text-align: right;" colspan="3">' +
						'	S/ ' + balance_diferencia_signo + ' ' + balance_diferencia + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>' +
						'</tr>' +
						'</tbody>'
					);
					
					if(parseFloat(balance_diferencia) != 0){

						$('#SecRepCBal_div_diferencias').html('<button class="btn btn-danger" onclick="SecRptCBal_listar_diferencias()"><span class="glyphicon glyphicon-eye-open"></span> Ver listado de diferencias</button>'); 
					}
				}
				return false;
			}
			return false;
		},
		error: function (result) {
			return false;
		}
	});
}

// LISTAS DIFRENCIAS 
function SecRptCBal_listar_diferencias() {
	var SecRepCBal_fecha_inicio = $.trim($("#SecRepCBal_fecha_inicio").val());
	var SecRepCBal_fecha_fin = $.trim($("#SecRepCBal_fecha_fin").val());
	var SecRepCBal_cliente_tipo = $("#SecRepCBal_cliente_tipo").val();
	var SecRepCBal_cliente_texto = $("#SecRepCBal_cliente_texto").val();

	if(parseInt(SecRepCBal_cliente_tipo) != 0){
		if(SecRepCBal_cliente_texto.length == 0){
			swal('Aviso', 'Debe ingresar un cliente a buscar', 'warning');
			return false;
		}
		if(SecRepCBal_cliente_texto.length < 3){
			swal('Aviso', 'El dato debe contener al menos 3 carácteres', 'warning');
			return false;
		}
	}
	var data = {
		"accion": "SecRptCBal_listar_diferencias_registros",
		"fecha_inicio": SecRepCBal_fecha_inicio,
		"fecha_fin": SecRepCBal_fecha_fin,
		"cliente_tipo": SecRepCBal_cliente_tipo,
		"cliente_texto": SecRepCBal_cliente_texto
	}

	$('#SecRepCBal_tabla_diferencia').html(
		'<thead>'+
			'<tr>'+
			'<th style="text-align: center;" colspan="9">LISTA DE DIFERENCIAS POR CLIENTE</th>'+
			'</tr>'+
			'<tr>'+
			'<th style="text-align: center;">#</th>'+
			'<th style="text-align: center;">NºDOC.</th>'+
			'<th style="text-align: center;">WEB-ID</th>'+
			'<th style="text-align: center;">CLIENTE</th>'+
			'<th style="text-align: center;">BAL.INICIO</th>'+
			'<th style="text-align: center;">MONTO DIF.</th>'+
			'<th style="text-align: center;">BAL.CIERRE TEO.</th>'+
			'<th style="text-align: center;">BAL.CIERRE</th>'+
			'<th style="text-align: center;">DIFERENCIA</th>'+
			'</tr>'+
		'</thead>'+
		'<tbody>'+
		'</tbody>'
		);
	$.ajax({
		url: "/sys/get_reportes_cuadre_balance.php",
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
				if (respuesta.list_balance_diferencia.length > 0) {
					$.each(respuesta.list_balance_diferencia, function (index, item) {
						$('#SecRepCBal_tabla_diferencia tbody').append(
							'<tr>' +
							'	<td style="text-align: center;">' + (index+1) + '</td>' +
							'	<td style="text-align: center;">' + item.num_doc + '</td>' +
							'	<td style="text-align: center;">' + item.web_id + '</td>' +
							'	<td style="text-align: center;">' + item.nombres + ' ' + item.ape_paterno + ' ' + item.ape_materno + '</td>' +
							'	<td style="text-align: right;">' + item.balance_antes + '</td>' +
							'	<td style="text-align: right;">' + item.monto + '</td>' +
							'	<td style="text-align: right;">' + item.balance_despues_2.toFixed(2) + '</td>' +
							'	<td style="text-align: right;">' + item.balance_despues + '</td>' +
							'	<td style="text-align: right;">' + item.balance_diferencia.toFixed(2) + '</td>' +
							'</tr>' +
							'</tbody>'
						);
					});
				} 
				return false;
			}else{
				$('#SecRepCBal_tabla_diferencia').html('<tbody><tr>	<td class="text-center" colspan="2">No hay diferencias.</td></tr></tbody>');
			}
			return false;
		},
		error: function (result) {
			return false;
		}
	});
}

function SecRptCBal_resumen(){
	var SecRepCBal_fecha = $.trim($("#SecRepCBal_fecha").val());
	var SecRepCBal_hora = $.trim($("#SecRepCBal_hora").val());
	var SecRepCBal_cliente_tipo = $("#SecRepCBal_cliente_tipo").val();
	var SecRepCBal_cliente_texto = $("#SecRepCBal_cliente_texto").val();

	var data = {
		"accion": "SecRptCBal_listar_resumen",
		"fecha": SecRepCBal_fecha,
		"hora": SecRepCBal_hora,
		"cliente_tipo": SecRepCBal_cliente_tipo,
		"cliente_texto": SecRepCBal_cliente_texto
	}

	$.ajax({
		url: "/sys/get_reportes_cuadre_balance.php",
		type: 'POST',
		async : true,
		data: data,
		beforeSend: function() {
				loading("true");
		},
		complete: function() {
				loading();
		},
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) === 200) {
				if (respuesta.resumen.length > 0) {
					$.each(respuesta.resumen, function(index, item) {
						$('#SecRepCBal_cant_clientes').val(item.cant_clientes);

						item.total_balance = item.total_balance.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_retiro = item.total_retiro.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

						$('#SecRepCBal_total_balance').val(item.total_balance);
						$('#SecRepCBal_total_retiro').val(item.total_retiro);
					});
				}
				return false;
			}
		},
		error: function() {}
	});
}