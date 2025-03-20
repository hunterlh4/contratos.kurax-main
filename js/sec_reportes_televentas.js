
var SecRepTel_resultado = 2;
function sec_reportes_televentas() {
	if (sec_id == 'reportes' && sub_sec_id=='televentas') {

		$('#SecRepTel_fecha_inicio').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepTel_fecha_fin').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepTel_fecha_inicio').val($('#g_fecha_actual').val());
		$('#SecRepTel_fecha_fin').val($('#g_fecha_actual').val());

		$('#SecRepTel_tipo_busqueda').select2();
		$('#SecRepTel_tipo_transaccion').select2();
		$('#SecRepTel_local').select2();
		$('#SecRepTel_estado_cierre').select2();

		$('#SecRepTel_tipo_balance').select2();
		$('#SecRepTel_motivo_balance').select2();
		$('#SecRepTel_juego_balance').select2();
		$('#sec_rpt_tlv_select_proveedor').select2();

		document.getElementById('SecRepTel_div_btn_exportar').innerHTML = '<button class="btn btn-success" onclick="sec_rpt_tlv_exportar()" style="width: 100%;"><span class="glyphicon glyphicon-download-alt"></span> Exportar</button>';

		//$('#SecRepTel_cuenta').multiSelect();

		$('#SecRepTel_cuenta').multiselect({
			buttonClass:'form-control',
			buttonWidth: '100%',
			includeSelectAllOption: true, 
			onSelectAll: function(options) {
				$.each(options, function(index, item) {
					//console.log(item[0].value);
				});
			},
			onDeselectAll: function(options) {
				$.each(options, function(index, item) {
					//console.log(item[0].value);
				});
			},
			onChange: function(element, checked) {

				var activar = 0;
				var id_cuenta =0;
				if (checked === true) {
					activar =1;
					id_cuenta=element.val();
					
				}
				else if (checked === false) {
					activar =0;
					id_cuenta=element.val();          
				}
			}
		});

		$('#SecRepTel_fecha_inicio').change(function () {
			var var_fecha_change = $('#SecRepTel_fecha_inicio').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepTel_fecha_inicio").val($("#g_fecha_actual").val());
			}
		});
		$('#SecRepTel_fecha_fin').change(function () {
			var var_fecha_change = $('#SecRepTel_fecha_fin').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepTel_fecha_fin").val($("#g_fecha_actual").val());
			}
		});

		$('#SecRepTel_btn_buscar').click(function() {
			SecRepTel_resultado = 1;
			document.getElementById('SecRepTel_div_btn_exportar').innerHTML = '<button class="btn btn-success" onclick="sec_rpt_tlv_exportar()" style="width: 100%;"><span class="glyphicon glyphicon-download-alt"></span> Exportar</button>';
			SecRepTel_buscar();
		});

		$("#SecRepTel_btn_exportar").click(function(){
			SecRepTel_resultado = 1;
		 
			SecRepTel_exportar_excel();
		});

		$('#SecRepTel_btn_ver_cuadre').click(function() {
			SecRepTel_resultado = 2;
			SecRepTel_buscar();
		});

		$('#SecRepTel_btn_ver_prevencion').click(function() {
			SecRepTel_resultado = 3;
			SecRepTel_buscar();
		});

		$('#SecRepTel_btn_ver_tipo_balance').click(function() {
			SecRepTel_resultado = 4;
		 

			document.getElementById('SecRepTel_div_btn_exportar').innerHTML = '<a  class="btn btn-primary " style="width: 100%;"  download="solicitud_derivados_at.xls"> Exportar</a>';
			SecRepTel_buscar();
			SecRepTel_exportar_excel_tipo_bl();
		});

		$('#SecRepTel_cajero').autocomplete({
            source: '/sys/get_reportes_televentas.php?accion=SecRepTel_listar_cajeros',
            minLength: 2,
            select: function (event, ui)
            {
                gen_cajero_seleccionado=ui.item.codigo;
                if(gen_cajero_seleccionado == undefined){
                    gen_cajero_seleccionado = 0;
                }
            }
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            $('ul.ui-menu').css('display', 'block !important');
            $('ul.ui-menu').css('z-index', '100000');
            return $( "<li>" )
            .append( item.label)
            .appendTo( ul );
        };

	}
}
var gen_cajero_seleccionado = 0;

$("#SecRepTel_tipo_transaccion").change(function () {
	$("#SecRepTel_tipo_transaccion option:selected").each(function () {
		tipo_tr = $(this).val();
		if (tipo_tr == 17  ) {
			$("#sec_tlv_filtros_balance").show();
			sec_rep_tlv_obtener_opciones("listar_motivos_balance_sube", $("[name='SecRepTel_motivo_balance']"));
		 
		} else if(tipo_tr == 18)  {
			$("#sec_tlv_filtros_balance").show();
			sec_rep_tlv_obtener_opciones("listar_motivos_balance_baja", $("[name='SecRepTel_motivo_balance']"));
		} else  {
			$("#sec_tlv_filtros_balance").hide();
		} 
	});
});



function sec_rep_tlv_obtener_opciones(accion, select) {
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
		$(select).append('<option value="0">- TODOS -</option>');
		$(respuesta.result).each(function (i, e) {
		  opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
		  $(select).append(opcion);
		});
	  },
	  error: function () {},
	});
  }

function SecRepTel_buscar(){
	$('#SecRepTel_RES').hide();
	$('#SecRepTel_CAJA').hide();
	$('#SecRepTel_PREV').hide();
	$('#SecRepTel_tabla_tipo_balance').hide();

	if(parseInt(SecRepTel_resultado)===1){
		SecRepTel_RES_listar_tabla_transacciones_v2();
		SecRepTel_RES_listar_tabla_transacciones_totales_v2();
		SecRepTel_RES_tabla_transacciones_resumen_v2();
	}
	if(parseInt(SecRepTel_resultado)===2){
		SecRepTel_CAJA_listar_tbl_reporte_venta();
	}
	if(parseInt(SecRepTel_resultado)===3){
		SecRepTel_PREV_tbl_rep_ingresos_salidas_listar();
	}

	if(parseInt(SecRepTel_resultado)===4){
		SecRepTel_reporte_tipo_balance();
	}
}
function SecRepTel_exportar_excel(){
	if(parseInt(SecRepTel_resultado)===1){
		SecRepTel_RES_exportar_excel();
	}
}


// ***************************************************************************************************
// ***************************************************************************************************
// RES
// ***************************************************************************************************
// ***************************************************************************************************

// LISTAS TRANSACCIONES
function SecRepTel_RES_listar_tabla_transacciones_v2() {
	var ftable = $('#SecRepTelCli_tabla_transacciones_v2').DataTable();
	ftable.clear();
	ftable.destroy();
	var ftable = $('#SecRepTelCli_tabla_transacciones_v2').DataTable({
		'destroy': true,
		'scrollX': true,
		"processing": true,
		"serverSide": true,
		"order" : [],
		"ajax": {
			type: "POST",
			async : true,
			"url": "/sys/get_reportes_televentas.php",
			"data": SecRepTel_RES_listar_tabla_transacciones_v2_get_data()
		},
		"order": [],
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
		"columns": [
			{
				"data": null,
				"sortable": false,
				render: function (data, type, row, meta) {
					return meta.row + meta.settings._iDisplayStart + 1;
				}
			},
            { "data": "cliente_id" },
			{
				"data": "fecha_hora_registro",
				render: function (data, type,rowData,row) {
					if ( rowData.local_cierre && rowData.turno_cierre ) {
						return rowData.local_cierre +' - Turno ' + rowData.turno_cierre;
					} else {
						return '';
					}
				},
				createdCell: function (td, cellData, rowData, row, col) {
					var color = (rowData.validacion_cierre > 0 ) ? '#636363' : '#ff5757';
					$(td).css('color', color + ' solid 3px');
				}
			},
			{ "data": "fecha_hora_registro" },
			{ "data": "registro_deposito" },
			{ "data": "tipo_transaccion" },
			{ "data": "tipo_saldo" },
			{ "data": "proveedor_nombre" },
			{ "data": "bono_nombre" },
			{ "data": "tipo_contacto" },
			{ "data": "telefono" },
            { "data": "txn_id" },
            { "data": "operation_id" },
			{ "data": "tipo_doc" },
			{ "data": "num_doc" },
			{ "data": "web_id" },
			{ "data": "cliente" },
			{ "data": "cuenta" },
			{ "data": "deposito" },
			{ "data": "comision_monto" },
			{ "data": "monto" },
			{ "data": "bono_monto" },
			{ "data": "total_recarga" },
			{ "data": "cajero" },
			{ "data": "validador" },
			{ "data": "observacion_cajero" },
			{ "data": "validado_por" }
		]
	});
}
function SecRepTel_RES_listar_tabla_transacciones_v2_get_data() {
	var SecRepTel_tipo_busqueda = $("#SecRepTel_tipo_busqueda").val();
	var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
	var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
	var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion").val();
	var SecRepTel_local = $("#SecRepTel_local").val();
	var SecRepTel_cajero = $('#SecRepTel_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepTel_cajero").val();
	var SecRepTel_estado_cierre = $("#SecRepTel_estado_cierre").val();
	var SecRepTel_cuenta = $("#SecRepTel_cuenta").val();
	var SecRepTel_cliente_tipo = $("#SecRepTel_cliente_tipo").val();
	var SecRepTel_cliente_texto = $("#SecRepTel_cliente_texto").val();
	var SecRepTel_tipo_bono = $("#sec_rpt_tlv_select_bono").val();
	var SecRepTel_proveedor = $("#sec_rpt_tlv_select_proveedor").val();
	var SecRepTel_num_transaccion = $("#SecRepTel_num_transaccion").val();
	var SecRepTel_tipo_saldo = $("#SecRepTel_tipo_saldo").val();
    var SecRepTel_Caja7 = $("#sec_rpt_tlv_select_caja").val();
	var SecRepTel_lugar = $("#SecRepTel_lugar").val();

    if(SecRepTel_Caja7 === undefined){
        SecRepTel_Caja7 = 0;
    }

	$('#SecRepTel_subir_balance_div').hide();
	$('#SecRepTel_bajar_balance_div').hide();

    if(SecRepTel_tipo_transaccion == 17){
    	$('#SecRepTel_subir_balance_div').show();
    }else if(SecRepTel_tipo_transaccion == 18){
    	$('#SecRepTel_bajar_balance_div').show();
    }else{
    	$('#SecRepTel_subir_balance_div').hide();
    	$('#SecRepTel_bajar_balance_div').hide();
    }

	if (SecRepTel_fecha_inicio.length !== 10) {
		$("#SecRepTel_fecha_inicio").focus();
		return false;
	}
	if (SecRepTel_fecha_fin.length !== 10) {
		$("#SecRepTel_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "listar_transacciones_v2",
		"tipo_busqueda": SecRepTel_tipo_busqueda,
		"fecha_inicio": SecRepTel_fecha_inicio,
		"fecha_fin": SecRepTel_fecha_fin,
		"tipo_transaccion": SecRepTel_tipo_transaccion,
		"local": SecRepTel_local,
		"cajero": SecRepTel_cajero,
		"estado_cierre": SecRepTel_estado_cierre,
		"cuenta": SecRepTel_cuenta,
		"cliente_tipo": SecRepTel_cliente_tipo,
		"cliente_texto": SecRepTel_cliente_texto,
		"bono": SecRepTel_tipo_bono,
		"proveedor": SecRepTel_proveedor,
		"num_transaccion": SecRepTel_num_transaccion,
		"tipo_saldo": SecRepTel_tipo_saldo,
        "caja_vip": SecRepTel_Caja7,
		"lugar": SecRepTel_lugar
	}
	return data;
}



// LISTAR TOTALES
function SecRepTel_RES_listar_tabla_transacciones_totales_v2() {

	$('#SecRepTel_RES').show();

	$('#SecRepTel_deposito_cant').val('0');
	$('#SecRepTel_bono_cant').val('0');
	$('#SecRepTel_recarga_cant').val('0');
	$('#SecRepTel_monto_total').val('0.00');
	$('#SecRepTel_bono_total').val('0.00');
	$('#SecRepTel_recarga_total').val('0.00');
	$('#SecRepTel_total_recargas_bonos').val('0.00');

	$('#SecRepTel_apuesta_cant').val('0');
	$('#SecRepTel_apuestapagada_cant').val('0');
	$('#SecRepTel_apuesta_total').val('0.00');
	$('#SecRepTel_apuestapagada_total').val('0.00');


	$('#SecRepTel_total_bono_apuestas_5_pct').val('0.00');
	$('#SecRepTel_total_bono_apuestas_deportivas').val('0.00');
	$('#SecRepTel_total_bono_casino').val('0.00');
	$('#SecRepTel_total_retiros').val('0.00');
	$('#SecRepTel_total_terminal_deposit').val('0.00');
	$('#SecRepTel_total_venta_bingo').val('0.00');
	$('#SecRepTel_total_pago_bingo').val('0.00');
	$('#SecRepTel_total_subir_balance').val('0.00');
	$('#SecRepTel_total_bajar_balance').val('0.00');
	$('#SecRepTel_cant_juegos_virtuales').val('0');
	$('#SecRepTel_cant_juegos_virtuales_pagadas').val('0');
	$('#SecRepTel_total_juegos_virtuales').val('0.00');
	$('#SecRepTel_total_juegos_virtuales_pagadas').val('0.00');
	$('#SecRepTel_num_clientes_antiguos').val('0');
	$('#SecRepTel_num_terminal_deposit_tambo').val('0');
	$('#SecRepTel_total_terminal_deposit_tambo').val('0.00');
	$('#SecRepTel_num_saldo_real').val('0');
	$('#SecRepTel_num_saldo_promocional').val('0');
	$('#SecRepTel_num_clientes_nuevos').val('0');

	var SecRepTel_tipo_busqueda = $("#SecRepTel_tipo_busqueda").val();
	var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
	var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
	var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion").val();
	var SecRepTel_local = $("#SecRepTel_local").val();
	var SecRepTel_cajero = $('#SecRepTel_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepTel_cajero").val();
	var SecRepTel_estado_cierre = $("#SecRepTel_estado_cierre").val();
	var SecRepTel_cuenta = $("#SecRepTel_cuenta").val();
	var SecRepTel_cliente_tipo = $("#SecRepTel_cliente_tipo").val();
	var SecRepTel_cliente_texto = $("#SecRepTel_cliente_texto").val();
	var SecRepTel_bono = $("#sec_rpt_tlv_select_bono").val();
	var SecRepTel_proveedor = $("#sec_rpt_tlv_select_proveedor").val();
	var SecRepTel_num_transaccion = $("#SecRepTel_num_transaccion").val();
	var SecRepTel_tipo_saldo = $("#SecRepTel_tipo_saldo").val();
    var SecRepTel_Caja7 = $("#sec_rpt_tlv_select_caja").val();
	var SecRepTel_lugar = $("#SecRepTel_lugar").val();
    
    if(SecRepTel_Caja7 === undefined){
        SecRepTel_Caja7 = 0;
    }

	if (SecRepTel_fecha_inicio.length !== 10) {
		$("#SecRepTel_fecha_inicio").focus();
		return false;
	}
	if (SecRepTel_fecha_fin.length !== 10) {
		$("#buscador_texto").focus();
		return false;
	}

	var data = {
		"accion": "listar_transacciones_resumen_v2",
		"tipo_busqueda": SecRepTel_tipo_busqueda,
		"fecha_inicio": SecRepTel_fecha_inicio,
		"fecha_fin": SecRepTel_fecha_fin,
		"tipo_transaccion": SecRepTel_tipo_transaccion,
		"local": SecRepTel_local,
		"cajero": SecRepTel_cajero,
		"estado_cierre": SecRepTel_estado_cierre,
		"cuenta": SecRepTel_cuenta,
		"cliente_tipo": SecRepTel_cliente_tipo,
		"cliente_texto": SecRepTel_cliente_texto,
		"bono": SecRepTel_bono,
		"proveedor": SecRepTel_proveedor,
		"num_transaccion": SecRepTel_num_transaccion,
		"tipo_saldo": SecRepTel_tipo_saldo,
        "caja_vip": SecRepTel_Caja7,
		"lugar": SecRepTel_lugar
	}

	$.ajax({
		url: "/sys/get_reportes_televentas.php",
		type: 'POST',
		async : true,
		data: data,
		beforeSend: function() {
				loading("true");
		},
		complete: function() {
				loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			//console.log(respuesta);
		  
			if (parseInt(respuesta.http_code) === 200) {
				var monto_cant=0;
				var bono_cant=0;
				var recarga_cant=0;
				var monto_total=0;
				var bono_total=0;
				var recarga_total=0;
				var apuesta_cant=0;
				var apuesta_total=0;
				var apuestapagada_cant=0;
				var apuestapagada_total=0;
						  
				if (respuesta.resumen.length > 0) {
					$.each(respuesta.resumen, function(index, item) {
						$('#SecRepTel_deposito_cant').val(item.num_deposito);
						$('#SecRepTel_bono_cant').val(item.num_bono);
						$('#SecRepTel_recarga_cant').val(item.num_recarga);
						$('#SecRepTel_cant_juegos_virtuales').val(item.num_juegos_virtuales);
						$('#SecRepTel_cant_juegos_virtuales_pagadas').val(item.num_juegos_virtuales_pagadas);
						//$('#SecRepTel_donacion_cant').val(item.num_donacion_cancer);
						$('#SecRepTel_pago_sorteo_mundial_cant').val(item.num_pago_sorteo_mundial);
						$('#SecRepTel_num_terminal_deposit_tambo').val(item.num_tambo);
						$('#SecRepTel_num_saldo_real').val(item.num_saldo_real);
						$('#SecRepTel_num_saldo_promocional').val(item.num_saldo_promocional);

						var total_recarga_bono = (parseFloat(item.total_recarga) + parseFloat(item.total_bono)).toFixed(2);

						item.total_deposito = item.total_deposito.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_bono = item.total_bono.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_recarga = item.total_recarga.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						total_recarga_bono = total_recarga_bono.toString().replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

						item.total_bono_5pct = item.total_bono_5pct.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_bono_apuesta_deportiva = item.total_bono_apuesta_deportiva.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_bono_casino = item.total_bono_casino.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_retiro = item.total_retiro.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_terminal_deposit = item.total_terminal_deposit.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_venta_bingo = item.total_venta_bingo.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_pago_bingo = item.total_pago_bingo.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_subir_balance = item.total_subir_balance.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_bajar_balance = item.total_bajar_balance.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_juegos_virtuales = item.total_juegos_virtuales.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_juegos_virtuales_pagadas = item.total_juegos_virtuales_pagadas.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_donacion_cancer = item.total_donacion_cancer.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

						item.total_saldo_real = item.total_saldo_real.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_saldo_promocional = item.total_saldo_promocional.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

						$('#SecRepTel_monto_total').val(item.total_deposito);
						$('#SecRepTel_bono_total').val(item.total_bono);
						$('#SecRepTel_recarga_total').val(item.total_recarga);
						$('#SecRepTel_total_recargas_bonos').val(total_recarga_bono);

						$('#SecRepTel_apuesta_cant').val(item.num_apuesta_generada);
						$('#SecRepTel_apuestapagada_cant').val(item.num_apuesta_pagada);

						item.total_apuesta_generada = item.total_apuesta_generada.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_apuesta_pagada = item.total_apuesta_pagada.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

						$('#SecRepTel_apuesta_total').val(item.total_apuesta_generada);
						$('#SecRepTel_apuestapagada_total').val(item.total_apuesta_pagada);

						$('#SecRepTel_total_bono_apuestas_5_pct').val(item.total_bono_5pct);
						$('#SecRepTel_total_bono_apuestas_deportivas').val(item.total_bono_apuesta_deportiva);
						$('#SecRepTel_total_bono_casino').val(item.total_bono_casino);
						$('#SecRepTel_total_retiros').val(item.total_retiro);
						$('#SecRepTel_total_terminal_deposit').val(item.total_terminal_deposit);
						$('#SecRepTel_total_venta_bingo').val(item.total_venta_bingo);
						$('#SecRepTel_total_pago_bingo').val(item.total_pago_bingo);
						$('#SecRepTel_total_subir_balance').val(item.total_subir_balance);
						$('#SecRepTel_total_bajar_balance').val(item.total_bajar_balance);
						$('#SecRepTel_total_juegos_virtuales').val(item.total_juegos_virtuales);
						$('#SecRepTel_total_juegos_virtuales_pagadas').val(item.total_juegos_virtuales_pagadas);
						//$('#SecRepTel_total_donacion').val(item.total_donacion_cancer);
						$('#SecRepTel_total_pago_sorteo_mundial').val(item.total_sorteo_mundial);
						$('#SecRepTel_total_terminal_deposit_tambo').val(item.total_tambo);
						$('#SecRepTel_total_saldo_real').val(item.total_saldo_real);
						$('#SecRepTel_total_saldo_promocional').val(item.total_saldo_promocional);

						$('#SecRepTel_cant_v_torito_g').val(item.num_v_torito_g);
						$('#SecRepTel_cant_v_torito_mm').val(item.num_v_torito_mm);
						$('#SecRepTel_total_v_torito_g').val(item.total_v_torito_g);						
						$('#SecRepTel_total_v_torito_mm').val(item.total_v_torito_mm);


						$('#SecRepTel_cant_p_torito_g').val(item.num_p_torito_g);
						$('#SecRepTel_cant_p_torito_mm').val(item.num_p_torito_mm);	

						$('#SecRepTel_total_p_torito_g').val(item.total_p_torito_g);											
						$('#SecRepTel_total_p_torito_mm').val(item.total_p_torito_mm);


					});
				}
				if (respuesta.client_resumen.length > 0){
					$.each(respuesta.client_resumen, function(index, item) {
						$('#SecRepTel_clientes_unicos_cant').val(item.cant);
					});
				}
				if (respuesta.client_resumen_nuevo.length > 0){
					$.each(respuesta.client_resumen_nuevo, function(index, item) {
						if(item.is_new == 1){
							//$('#SecRepTel_num_clientes_nuevos').val(item.cant);	
						}else if(item.is_new == 0){
							$('#SecRepTel_num_clientes_antiguos').val(item.cant);	
						}
					});
				}
				$('#SecRepTel_num_clientes_nuevos').val(respuesta.num_clientes_nuevos);	
				return false;
			}
		},
		error: function() {}
	});
}


// LISTAR CIERRES
function SecRepTel_RES_tabla_transacciones_resumen_v2() {
	var dtable = $('#SecRepTelCli_tabla_transacciones_resumen_v2').DataTable();
	dtable.clear();
	dtable.destroy();
	var dtable = $('#SecRepTelCli_tabla_transacciones_resumen_v2').DataTable({
		 'destroy': true,
		'scrollX': true,
		"processing": true,
		"serverSide": true,
		'ordering': false,
		"ajax": {
			type: "POST",
			async : true,
			"url": "/sys/get_reportes_televentas.php",
			"data": SecRepTel_RES_tabla_transacciones_resumen_v2_get_data()
		},
		// "dataSrc": function (json) {
		//     var result = JSON.parse(json);
		//     return result.result;
		// },
		"order": [],
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
		"columns": [
			{ "data": "turno_fecha" ,
			render: function (data, type, row, meta) {
				return meta.row + meta.settings._iDisplayStart + 1;
			} 
		   
			 },            
			{ "data": "turno_fecha"},
			{ "data": "cajero" },
			{ "data": "turno_local" },
			{ "data": "turno_cierre" },
			{ "data": "fecha_registro" },
			{ "data": "cant_deposito" },
			{ "data": "total_deposito" },
			{ "data": "cant_recarga" },
			{ "data": "cant_bono" },
			{ "data": "total_recarga",
			 render: function (data, type,rowData,row) {                    
				 return (parseFloat(rowData.total_recarga)-parseFloat(rowData.total_bono)).toFixed(2);
			 },
			},
			{ "data": "total_bono" },
			{ "data": "total_recarga" },
			{ "data": "cant_apuesta" },
			{ "data": "total_apuesta" },
			{ "data": "cant_apuesta_pagada" },
			{ "data": "total_apuesta_pagada" },
			{ "data": "total_bono_casino" },
			{ "data": "total_bono_apuesta_deportiva" }
		], 
		"createdRow": function( row, rowData, dataIndex ) {
			var color = (parseInt(rowData.turno_validacion)===2) ? '#ff5757' : '#120e0e';
			$(row).css("color", color);
		  
		}
		
	});
}
function SecRepTel_RES_tabla_transacciones_resumen_v2_get_data() {
	var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
	var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
	var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion").val();
	var SecRepTel_local = $("#SecRepTel_local").val();
	var SecRepTel_cajero = $('#SecRepTel_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepTel_cajero").val();
	var SecRepTel_estado_cierre = $("#SecRepTel_estado_cierre").val();
	var SecRepTel_tipo_saldo = $("#SecRepTel_tipo_saldo").val();
	var SecRepTel_lugar = $("#SecRepTel_lugar").val();

	if (SecRepTel_fecha_inicio.length !== 10) {
		$("#SecRepTel_fecha_inicio").focus();
		return false;
	}
	if (SecRepTel_fecha_fin.length !== 10) {
		$("#buscador_texto").focus();
		return false;
	}

	var data = {
		"accion": "listar_transacciones_resumen_por_cierre_v2",
		"fecha_inicio": SecRepTel_fecha_inicio,
		"fecha_fin": SecRepTel_fecha_fin,
		"tipo_transaccion": SecRepTel_tipo_transaccion,
		"local": SecRepTel_local,
		"cajero": SecRepTel_cajero,
		"tipo_saldo": SecRepTel_tipo_saldo,
		"estado_cierre": SecRepTel_estado_cierre,
		"lugar": SecRepTel_lugar
	}
	return data;
}



// EXCEL
function SecRepTel_RES_exportar_excel() {

	var SecRepTel_tipo_busqueda = $("#SecRepTel_tipo_busqueda").val();
	var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
	var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
	var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion").val();
	var SecRepTel_local = $("#SecRepTel_local").val();
	var SecRepTel_cajero = $('#SecRepTel_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepTel_cajero").val();
	var SecRepTel_estado_cierre = $("#SecRepTel_estado_cierre").val();
	var SecRepTel_cuenta = $("#SecRepTel_cuenta").val();
	var SecRepTel_cliente_tipo = $("#SecRepTel_cliente_tipo").val();
	var SecRepTel_cliente_texto = $("#SecRepTel_cliente_texto").val();
	var SecRepTel_tipo_bono = $("#sec_rpt_tlv_select_bono").val();
	var SecRepTel_proveedor = $("#sec_rpt_tlv_select_proveedor").val();
	var SecRepTel_num_transaccion = $("#SecRepTel_num_transaccion").val();
	var SecRepTel_tipo_saldo = $("#SecRepTel_tipo_saldo").val();
    var SecRepTel_Caja7 = $("#sec_rpt_tlv_select_caja").val();
	var SecRepTel_lugar = $("#SecRepTel_lugar").val();
    
    if(SecRepTel_Caja7 === undefined){
        SecRepTel_Caja7 = 0;
    }

	if (SecRepTel_fecha_inicio.length !== 10) {
		$("#SecRepTel_fecha_inicio").focus();
		return false;
	}
	if (SecRepTel_fecha_fin.length !== 10) {
		$("#buscador_texto").focus();
		return false;
	}

	var data = {
		"accion": "listar_transacciones_export_xls",
		"tipo_busqueda": SecRepTel_tipo_busqueda,
		"fecha_inicio": SecRepTel_fecha_inicio,
		"fecha_fin": SecRepTel_fecha_fin,
		"tipo_transaccion": SecRepTel_tipo_transaccion,
		"local": SecRepTel_local,
		"cajero": SecRepTel_cajero,
		"estado_cierre": SecRepTel_estado_cierre,
		"cuenta": SecRepTel_cuenta,
		"cliente_tipo": SecRepTel_cliente_tipo,
		"cliente_texto": SecRepTel_cliente_texto,
		"bono": SecRepTel_tipo_bono,
		"proveedor": SecRepTel_proveedor,
		"num_transaccion": SecRepTel_num_transaccion,
		"tipo_saldo": SecRepTel_tipo_saldo,
        "caja_vip": SecRepTel_Caja7,
		"lugar": SecRepTel_lugar
	}

	//console.log(data);return false;

	$.ajax({
		url: "/sys/get_reportes_televentas.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			//debugger;
			//console.log(respuesta);
			let obj = JSON.parse(resp);
			window.open(obj.path);
			loading(false);
		},
		error: function() {}
	});
}





// ***************************************************************************************************
// ***************************************************************************************************
// CAJA
// ***************************************************************************************************
// ***************************************************************************************************

function SecRepTel_reporte_tipo_balance() {

	$('#SecRepTel_tabla_tipo_balance').show();
 
	var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
	var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
	var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion").val();
	var SecRepTel_local = $("#SecRepTel_local").val();
	var SecRepTel_cajero = $('#SecRepTel_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepTel_cajero").val(); 
	var SecRepTel_tipo_balance = $("#SecRepTel_tipo_balance").val();
	var SecRepTel_motivo_balance = $("#SecRepTel_motivo_balance").val();
	var SecRepTel_tipo_saldo = $("#SecRepTel_tipo_saldo").val();
	var SecRepTel_juego_balance = $("#SecRepTel_juego_balance").val();
 
	if (SecRepTel_fecha_inicio.length !== 10) {
		$("#SecRepTel_fecha_inicio").focus();
		return false;
	}
	if (SecRepTel_fecha_fin.length !== 10) {
		$("#SecRepTel_fecha_fin").focus();
		return false;
	}

	let data = {
		accion:'listar_tbl_reporte_tipo_balance',	 
		fecha_inicio: SecRepTel_fecha_inicio,
		fecha_fin: SecRepTel_fecha_fin,
		tipo_transaccion: SecRepTel_tipo_transaccion,
		local: SecRepTel_local,
		cajero: SecRepTel_cajero, 
		tipo_balance: SecRepTel_tipo_balance,
		motivo_balance: SecRepTel_motivo_balance,
		juego_balance: SecRepTel_juego_balance,
		tipo_saldo: SecRepTel_tipo_saldo,
 
	};

	SecRepTel_exportar_excel_tipo_bl();
	$.ajax({
		url: "/sys/get_reportes_televentas.php",
		type: 'POST',
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.status) == 200) {
				$('#SecRepTel_tabla_tipo_balance').html(respuesta.result);
				sec_rep_tlv_initialize_table('SecRepTel_rep_tbl_tipo_balance');
				return false;
			}
		},
		error: function() {}
	});

}


function SecRepTel_exportar_excel_tipo_bl() {

	 
	var fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
	var fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
	var tipo_transaccion = $("#SecRepTel_tipo_transaccion").val();
	var local = $("#SecRepTel_local").val();
	var cajero = $('#SecRepTel_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepTel_cajero").val(); 

	var tipo_balance = $("#SecRepTel_tipo_balance").val();
	var motivo_balance = $("#SecRepTel_motivo_balance").val();
	var juego_balance = $("#SecRepTel_juego_balance").val();

 
	document.getElementById('SecRepTel_div_btn_exportar').innerHTML = '<a href="export.php?export=reporte_tipo_balance&amp;type=lista&amp;fecha_inicio='+fecha_inicio+'&amp;fecha_fin='+fecha_fin+'&amp;tipo_transaccion='+tipo_transaccion+'&amp;local='+local+'&amp;cajero='+cajero+'&amp;tipo_balance='+tipo_balance+'&amp;motivo_balance='+motivo_balance+'&amp;juego_balance='+juego_balance+'" class="btn btn-success" style="width: 100%;"  download="reporte_tipo_balance.xls"><span class="glyphicon glyphicon-download-alt"></span> Exportar </a>';
  

}

function sec_rep_tlv_initialize_table(tabla){
	$('#' + tabla).DataTable({
		"bDestroy": true,
		scrollX: true,
		language:{
			"decimal":        "",
			"emptyTable":     "Tabla vacia",
			"info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
			"infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
			"infoFiltered":   "(filtered from _MAX_ total entradas)",
			"infoPostFix":    "",
			"thousands":      ",",
			"lengthMenu":     "Mostrar _MENU_ entradas",
			"loadingRecords": "Cargando...",
			"processing":     "Procesando...",
			"search":         "Filtrar:",
			"zeroRecords":    "Sin resultados",
			"paginate": {
				"first":      "Primero",
				"last":       "Ultimo",
				"next":       "Siguiente",
				"previous":   "Anterior"
			},
			"aria": {
				"sortAscending":  ": activate to sort column ascending",
				"sortDescending": ": activate to sort column descending"
			}
		}
		,aLengthMenu:[10, 20, 30, 40, 50]
		,"order": [[ 0, 'desc' ]]
	});  
}
 

function SecRepTel_CAJA_listar_tbl_reporte_venta() {

	$('#SecRepTel_CAJA').show();

	var SecRepTel_tipo_busqueda = $("#SecRepTel_tipo_busqueda").val();
	var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
	var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
	var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion").val();
	var SecRepTel_local = $("#SecRepTel_local").val();
	var SecRepTel_cajero = $('#SecRepTel_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepTel_cajero").val();
	var SecRepTel_estado_cierre = $("#SecRepTel_estado_cierre").val();
	var SecRepTel_cuenta = $("#SecRepTel_cuenta").val();
	var SecRepTel_cliente_tipo = $("#SecRepTel_cliente_tipo").val();
	var SecRepTel_cliente_texto = $("#SecRepTel_cliente_texto").val();
	var SecRepTel_tipo_bono = $("#sec_rpt_tlv_select_bono").val();
	var SecRepTel_proveedor = $("#sec_rpt_tlv_select_proveedor").val();
	var SecRepTel_num_transaccion = $("#SecRepTel_num_transaccion").val();
	var SecRepTel_tipo_saldo = $("#SecRepTel_tipo_saldo").val();
    var SecRepTel_Caja7 = $("#sec_rpt_tlv_select_caja").val();
    
    if(SecRepTel_Caja7 === undefined){
        SecRepTel_Caja7 = 0;
    }

	if (SecRepTel_fecha_inicio.length !== 10) {
		$("#SecRepTel_fecha_inicio").focus();
		return false;
	}
	if (SecRepTel_fecha_fin.length !== 10) {
		$("#SecRepTel_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "listar_tbl_reporte_venta",
		"tipo_busqueda": SecRepTel_tipo_busqueda,
		"fecha_inicio": SecRepTel_fecha_inicio,
		"fecha_fin": SecRepTel_fecha_fin,
		"tipo_transaccion": SecRepTel_tipo_transaccion,
		"local": SecRepTel_local,
		"cajero": SecRepTel_cajero,
		"estado_cierre": SecRepTel_estado_cierre,
		"cuenta": SecRepTel_cuenta,
		"cliente_tipo": SecRepTel_cliente_tipo,
		"cliente_texto": SecRepTel_cliente_texto,
		"bono": SecRepTel_tipo_bono,
		"proveedor": SecRepTel_proveedor,
		"num_transaccion": SecRepTel_num_transaccion,
		"tipo_saldo": SecRepTel_tipo_saldo,
        "caja_vip": SecRepTel_Caja7
	}

	$.ajax({
		url: "/sys/get_reportes_televentas.php",
		type: 'POST',
		async : true,
		data: data,
		beforeSend: function() {
				loading("true");
		},
		complete: function() {
				loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			//console.log(respuesta);
		  
			if (parseInt(respuesta.http_code) === 200) {
				$('#SecRepTel_CAJA_tbl_reporte_venta').html(respuesta.res_reporte_venta);
				$('#SecRepTel_CAJA_tbl_rep_ingresos_salidas').html(respuesta.res_ingresos_salidas);
				return false;
			}
		},
		error: function() {}
	});

}

function SecRepTel_CAJA_tbl_rep_ingresos_salidas_listar() {

	$('#SecRepTel_CAJA_tbl_rep_ingresos_salidas').html('');

	var SecRepTel_tipo_busqueda = $("#SecRepTel_tipo_busqueda").val();
	var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
	var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
	var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion").val();
	var SecRepTel_local = $("#SecRepTel_local").val();
	var SecRepTel_cajero = $('#SecRepTel_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepTel_cajero").val();
	var SecRepTel_estado_cierre = $("#SecRepTel_estado_cierre").val();
	var SecRepTel_cuenta = $("#SecRepTel_cuenta").val();
	var SecRepTel_cliente_tipo = $("#SecRepTel_cliente_tipo").val();
	var SecRepTel_cliente_texto = $("#SecRepTel_cliente_texto").val();
	var SecRepTel_tipo_bono = $("#sec_rpt_tlv_select_bono").val();
	var SecRepTel_proveedor = $("#sec_rpt_tlv_select_proveedor").val();
	var SecRepTel_num_transaccion = $("#SecRepTel_num_transaccion").val();
    var SecRepTel_Caja7 = $("#sec_rpt_tlv_select_caja").val();
    
    if(SecRepTel_Caja7 === undefined){
        SecRepTel_Caja7 = 0;
    }

	if (SecRepTel_fecha_inicio.length !== 10) {
		$("#SecRepTel_fecha_inicio").focus();
		return false;
	}
	if (SecRepTel_fecha_fin.length !== 10) {
		$("#SecRepTel_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "listar_tbl_rep_ingresos_salidas",
		"tipo_busqueda": SecRepTel_tipo_busqueda,
		"fecha_inicio": SecRepTel_fecha_inicio,
		"fecha_fin": SecRepTel_fecha_fin,
		"tipo_transaccion": SecRepTel_tipo_transaccion,
		"local": SecRepTel_local,
		"cajero": SecRepTel_cajero,
		"estado_cierre": SecRepTel_estado_cierre,
		"cuenta": SecRepTel_cuenta,
		"cliente_tipo": SecRepTel_cliente_tipo,
		"cliente_texto": SecRepTel_cliente_texto,
		"bono": SecRepTel_tipo_bono,
		"proveedor": SecRepTel_proveedor,
		"num_transaccion": SecRepTel_num_transaccion,
        "caja_vip": SecRepTel_Caja7
	}

	$.ajax({
		url: "/sys/get_reportes_televentas.php",
		type: 'POST',
		async : true,
		data: data,
		beforeSend: function() {
				loading("true");
		},
		complete: function() {
				loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			//console.log(respuesta);
		  
			if (parseInt(respuesta.http_code) === 200) {
				$('#SecRepTel_CAJA_tbl_rep_ingresos_salidas').html(respuesta.res_ingresos_salidas);
				return false;
			}
		},
		error: function() {}
	});
}







// ***************************************************************************************************
// ***************************************************************************************************
// PREVENCIÓN DE FRAUDE
// ***************************************************************************************************
// ***************************************************************************************************

function SecRepTel_PREV_tbl_rep_ingresos_salidas_listar() {

	$('#SecRepTel_PREV').show();

	$('#SecRepTel_PREV_tbl_rep_ingresos_salidas').html('');

	var SecRepTel_tipo_busqueda = $("#SecRepTel_tipo_busqueda").val();
	var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
	var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
	var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion").val();
	var SecRepTel_local = $("#SecRepTel_local").val();
	var SecRepTel_cajero = $('#SecRepTel_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepTel_cajero").val();
	var SecRepTel_estado_cierre = $("#SecRepTel_estado_cierre").val();
	var SecRepTel_cuenta = $("#SecRepTel_cuenta").val();
	var SecRepTel_cliente_tipo = $("#SecRepTel_cliente_tipo").val();
	var SecRepTel_cliente_texto = $("#SecRepTel_cliente_texto").val();
	var SecRepTel_tipo_bono = $("#sec_rpt_tlv_select_bono").val();
	var SecRepTel_proveedor = $("#sec_rpt_tlv_select_proveedor").val();
	var SecRepTel_num_transaccion = $("#SecRepTel_num_transaccion").val();
	var SecRepTel_tipo_saldo = $("#SecRepTel_tipo_saldo").val();
    var SecRepTel_Caja7 = $("#sec_rpt_tlv_select_caja").val();
    
    if(SecRepTel_Caja7 === undefined){
        SecRepTel_Caja7 = 0;
    }

	if (SecRepTel_fecha_inicio.length !== 10) {
		$("#SecRepTel_fecha_inicio").focus();
		return false;
	}
	if (SecRepTel_fecha_fin.length !== 10) {
		$("#SecRepTel_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "listar_res_prevencion",
		"tipo_busqueda": SecRepTel_tipo_busqueda,
		"fecha_inicio": SecRepTel_fecha_inicio,
		"fecha_fin": SecRepTel_fecha_fin,
		"tipo_transaccion": SecRepTel_tipo_transaccion,
		"local": SecRepTel_local,
		"cajero": SecRepTel_cajero,
		"estado_cierre": SecRepTel_estado_cierre,
		"cuenta": SecRepTel_cuenta,
		"cliente_tipo": SecRepTel_cliente_tipo,
		"cliente_texto": SecRepTel_cliente_texto,
		"bono": SecRepTel_tipo_bono,
		"proveedor": SecRepTel_proveedor,
		"num_transaccion": SecRepTel_num_transaccion,
		"tipo_saldo": SecRepTel_tipo_saldo,
        "caja_vip": SecRepTel_Caja7
	}

	$.ajax({
		url: "/sys/get_reportes_televentas.php",
		type: 'POST',
		async : true,
		data: data,
		beforeSend: function() {
				loading("true");
		},
		complete: function() {
				loading();
		},
		success: function(resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			//console.log(respuesta);
		  
			if (parseInt(respuesta.http_code) === 200) {
				$('#SecRepTel_PREV_tbl_rep_ingresos_salidas').html(respuesta.res_ingresos_salidas);
				$('#SecRepTel_PREV_tbl_rep_depositos').html(respuesta.res_depositos);
				$('#SecRepTel_PREV_tbl_rep_retiros').html(respuesta.res_retiros);
				$('#SecRepTel_PREV_tbl_rep_retiros_terminal').html(respuesta.res_retiros_terminal);
				return false;
			}
		},
		error: function() {}
	});
}

function sec_rpt_tlv_exportar(){
	SecRepTel_resultado = 1;	 
	SecRepTel_exportar_excel();
}