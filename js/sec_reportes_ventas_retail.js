
var SecRepVenRet_resultado = 2;
function sec_reportes_ventas_retail() {
	if (sec_id == 'reportes' && sub_sec_id=='ventas_retail') {

		$('#SecRepVenRet_fecha_inicio').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepVenRet_fecha_fin').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepVenRet_fecha_inicio').val($('#g_fecha_actual').val());
		$('#SecRepVenRet_fecha_fin').val($('#g_fecha_actual').val());

		$('#SecRepVenRet_tipo_busqueda').select2();
		$('#SecRepVenRet_tipo_transaccion').select2();
		$('#SecRepVenRet_local').select2();
		$('#SecRepVenRet_estado_cierre').select2();

		$('#SecRepVenRet_tipo_balance').select2();
		$('#SecRepVenRet_motivo_balance').select2();
		$('#SecRepVenRet_juego_balance').select2();
		$('#sec_rpt_vent_retselect_proveedor').select2();

		document.getElementById('SecRepVenRet_div_btn_exportar').innerHTML = '<button class="btn btn-success" onclick="sec_rpt_vent_retexportar()" style="width: 100%;"><span class="glyphicon glyphicon-download-alt"></span> Exportar</button>';

		$('#SecRepVenRet_cuenta').multiselect({
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

		$('#SecRepVenRet_fecha_inicio').change(function () {
			var var_fecha_change = $('#SecRepVenRet_fecha_inicio').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepVenRet_fecha_inicio").val($("#g_fecha_actual").val());
			}
		});
		$('#SecRepVenRet_fecha_fin').change(function () {
			var var_fecha_change = $('#SecRepVenRet_fecha_fin').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepVenRet_fecha_fin").val($("#g_fecha_actual").val());
			}
		});

		$('#SecRepVenRet_btn_buscar').click(function() {
			SecRepVenRet_resultado = 1;
			document.getElementById('SecRepVenRet_div_btn_exportar').innerHTML = '<button class="btn btn-success" onclick="sec_rpt_vent_retexportar()" style="width: 100%;"><span class="glyphicon glyphicon-download-alt"></span> Exportar</button>';
			SecRepVenRet_buscar();
		});

		$("#SecRepVenRet_btn_exportar").click(function(){
			SecRepVenRet_resultado = 1;
		 
			SecRepVenRet_exportar_excel();
		});

		$('#SecRepVenRet_btn_ver_tipo_balance').click(function() {
			SecRepVenRet_resultado = 4;
		 

			document.getElementById('SecRepVenRet_div_btn_exportar').innerHTML = '<a  class="btn btn-primary " style="width: 100%;"  download="solicitud_derivados_at.xls"> Exportar</a>';
			SecRepVenRet_buscar();
			SecRepVenRet_exportar_excel_tipo_bl();
		});

		$('#SecRepVenRet_cajero').autocomplete({
            source: '/sys/get_reportes_ventas_retail.php?accion=SecRepVenRet_listar_cajeros',
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

$("#SecRepVenRet_tipo_transaccion").change(function () {
	$("#SecRepVenRet_tipo_transaccion option:selected").each(function () {
		tipo_tr = $(this).val();
		if (tipo_tr == 17  ) {
			$("#sec_tlv_filtros_balance").show();
			sec_rep_tlv_obtener_opciones("listar_motivos_balance_sube", $("[name='SecRepVenRet_motivo_balance']"));
		 
		} else if(tipo_tr == 18)  {
			$("#sec_tlv_filtros_balance").show();
			sec_rep_tlv_obtener_opciones("listar_motivos_balance_baja", $("[name='SecRepVenRet_motivo_balance']"));
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

function SecRepVenRet_buscar(){
	$('#SecRepVenRet_RES').hide();
	$('#SecRepVenRet_CAJA').hide();
	$('#SecRepVenRet_PREV').hide();
	$('#SecRepVenRet_tabla_tipo_balance').hide();

	if(parseInt(SecRepVenRet_resultado)===1){
		SecRepVenRet_RES_listar_tabla_transacciones_v2();
		SecRepVenRet_RES_listar_tabla_transacciones_totales_v2();
	}

	if(parseInt(SecRepVenRet_resultado)===4){
		SecRepVenRet_reporte_tipo_balance();
	}
}
function SecRepVenRet_exportar_excel(){
	if(parseInt(SecRepVenRet_resultado)===1){
		SecRepVenRet_RES_exportar_excel();
	}
}


// ***************************************************************************************************
// ***************************************************************************************************
// RES
// ***************************************************************************************************
// ***************************************************************************************************

// LISTAS TRANSACCIONES
function SecRepVenRet_RES_listar_tabla_transacciones_v2() {
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
			"url": "/sys/get_reportes_ventas_retail.php",
			"data": SecRepVenRet_RES_listar_tabla_transacciones_v2_get_data()
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
function SecRepVenRet_RES_listar_tabla_transacciones_v2_get_data() {
	var SecRepVenRet_tipo_busqueda = $("#SecRepVenRet_tipo_busqueda").val();
	var SecRepVenRet_fecha_inicio = $.trim($("#SecRepVenRet_fecha_inicio").val());
	var SecRepVenRet_fecha_fin = $.trim($("#SecRepVenRet_fecha_fin").val());
	var SecRepVenRet_tipo_transaccion = $("#SecRepVenRet_tipo_transaccion").val();
	var SecRepVenRet_local = $("#SecRepVenRet_local").val();
	var SecRepVenRet_cajero = $('#SecRepVenRet_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepVenRet_cajero").val();
	var SecRepVenRet_estado_cierre = $("#SecRepVenRet_estado_cierre").val();
	var SecRepVenRet_cuenta = $("#SecRepVenRet_cuenta").val();
	var SecRepVenRet_cliente_tipo = $("#SecRepVenRet_cliente_tipo").val();
	var SecRepVenRet_cliente_texto = $("#SecRepVenRet_cliente_texto").val();
	var SecRepVenRet_tipo_bono = $("#sec_rpt_vent_retselect_bono").val();
	var SecRepVenRet_proveedor = $("#sec_rpt_vent_retselect_proveedor").val();
	var SecRepVenRet_num_transaccion = $("#SecRepVenRet_num_transaccion").val();
	var SecRepVenRet_tipo_saldo = $("#SecRepVenRet_tipo_saldo").val();
    var SecRepVenRet_Caja7 = $("#sec_rpt_vent_retselect_caja").val();

    if(SecRepVenRet_Caja7 === undefined){
        SecRepVenRet_Caja7 = 0;
    }

	$('#SecRepVenRet_subir_balance_div').hide();
	$('#SecRepVenRet_bajar_balance_div').hide();

    if(SecRepVenRet_tipo_transaccion == 17){
    	$('#SecRepVenRet_subir_balance_div').show();
    }else if(SecRepVenRet_tipo_transaccion == 18){
    	$('#SecRepVenRet_bajar_balance_div').show();
    }else{
    	$('#SecRepVenRet_subir_balance_div').hide();
    	$('#SecRepVenRet_bajar_balance_div').hide();
    }

	if (SecRepVenRet_fecha_inicio.length !== 10) {
		$("#SecRepVenRet_fecha_inicio").focus();
		return false;
	}
	if (SecRepVenRet_fecha_fin.length !== 10) {
		$("#SecRepVenRet_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "listar_transacciones_v2",
		"tipo_busqueda": SecRepVenRet_tipo_busqueda,
		"fecha_inicio": SecRepVenRet_fecha_inicio,
		"fecha_fin": SecRepVenRet_fecha_fin,
		"tipo_transaccion": SecRepVenRet_tipo_transaccion,
		"local": SecRepVenRet_local,
		"cajero": SecRepVenRet_cajero,
		"estado_cierre": SecRepVenRet_estado_cierre,
		"cuenta": SecRepVenRet_cuenta,
		"cliente_tipo": SecRepVenRet_cliente_tipo,
		"cliente_texto": SecRepVenRet_cliente_texto,
		"bono": SecRepVenRet_tipo_bono,
		"proveedor": SecRepVenRet_proveedor,
		"num_transaccion": SecRepVenRet_num_transaccion,
		"tipo_saldo": SecRepVenRet_tipo_saldo,
        "caja_vip": SecRepVenRet_Caja7
	}
	return data;
}



// LISTAR TOTALES
function SecRepVenRet_RES_listar_tabla_transacciones_totales_v2() {

	$('#SecRepVenRet_RES').show();

	$('#SecRepVenRet_deposito_cant').val('0');
	$('#SecRepVenRet_bono_cant').val('0');
	$('#SecRepVenRet_recarga_cant').val('0');
	$('#SecRepVenRet_monto_total').val('0.00');
	$('#SecRepVenRet_bono_total').val('0.00');
	$('#SecRepVenRet_recarga_total').val('0.00');
	$('#SecRepVenRet_total_recargas_bonos').val('0.00');

	$('#SecRepVenRet_apuesta_cant').val('0');
	$('#SecRepVenRet_apuestapagada_cant').val('0');
	$('#SecRepVenRet_apuesta_total').val('0.00');
	$('#SecRepVenRet_apuestapagada_total').val('0.00');


	$('#SecRepVenRet_total_bono_apuestas_5_pct').val('0.00');
	$('#SecRepVenRet_total_bono_apuestas_deportivas').val('0.00');
	$('#SecRepVenRet_total_bono_casino').val('0.00');
	$('#SecRepVenRet_total_retiros').val('0.00');
	$('#SecRepVenRet_total_terminal_deposit').val('0.00');
	$('#SecRepVenRet_total_venta_bingo').val('0.00');
	$('#SecRepVenRet_total_pago_bingo').val('0.00');
	$('#SecRepVenRet_total_subir_balance').val('0.00');
	$('#SecRepVenRet_total_bajar_balance').val('0.00');
	$('#SecRepVenRet_cant_juegos_virtuales').val('0');
	$('#SecRepVenRet_cant_juegos_virtuales_pagadas').val('0');
	$('#SecRepVenRet_total_juegos_virtuales').val('0.00');
	$('#SecRepVenRet_total_juegos_virtuales_pagadas').val('0.00');
	$('#SecRepVenRet_num_clientes_antiguos').val('0');
	$('#SecRepVenRet_num_terminal_deposit_tambo').val('0');
	$('#SecRepVenRet_total_terminal_deposit_tambo').val('0.00');
	$('#SecRepVenRet_num_saldo_real').val('0');
	$('#SecRepVenRet_num_saldo_promocional').val('0');
	$('#SecRepVenRet_num_clientes_nuevos').val('0');

	var SecRepVenRet_tipo_busqueda = $("#SecRepVenRet_tipo_busqueda").val();
	var SecRepVenRet_fecha_inicio = $.trim($("#SecRepVenRet_fecha_inicio").val());
	var SecRepVenRet_fecha_fin = $.trim($("#SecRepVenRet_fecha_fin").val());
	var SecRepVenRet_tipo_transaccion = $("#SecRepVenRet_tipo_transaccion").val();
	var SecRepVenRet_local = $("#SecRepVenRet_local").val();
	var SecRepVenRet_cajero = $('#SecRepVenRet_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepVenRet_cajero").val();
	var SecRepVenRet_estado_cierre = $("#SecRepVenRet_estado_cierre").val();
	var SecRepVenRet_cuenta = $("#SecRepVenRet_cuenta").val();
	var SecRepVenRet_cliente_tipo = $("#SecRepVenRet_cliente_tipo").val();
	var SecRepVenRet_cliente_texto = $("#SecRepVenRet_cliente_texto").val();
	var SecRepVenRet_bono = $("#sec_rpt_vent_retselect_bono").val();
	var SecRepVenRet_proveedor = $("#sec_rpt_vent_retselect_proveedor").val();
	var SecRepVenRet_num_transaccion = $("#SecRepVenRet_num_transaccion").val();
	var SecRepVenRet_tipo_saldo = $("#SecRepVenRet_tipo_saldo").val();
    var SecRepVenRet_Caja7 = $("#sec_rpt_vent_retselect_caja").val();
    
    if(SecRepVenRet_Caja7 === undefined){
        SecRepVenRet_Caja7 = 0;
    }

	if (SecRepVenRet_fecha_inicio.length !== 10) {
		$("#SecRepVenRet_fecha_inicio").focus();
		return false;
	}
	if (SecRepVenRet_fecha_fin.length !== 10) {
		$("#buscador_texto").focus();
		return false;
	}

	var data = {
		"accion": "listar_transacciones_resumen_v2",
		"tipo_busqueda": SecRepVenRet_tipo_busqueda,
		"fecha_inicio": SecRepVenRet_fecha_inicio,
		"fecha_fin": SecRepVenRet_fecha_fin,
		"tipo_transaccion": SecRepVenRet_tipo_transaccion,
		"local": SecRepVenRet_local,
		"cajero": SecRepVenRet_cajero,
		"estado_cierre": SecRepVenRet_estado_cierre,
		"cuenta": SecRepVenRet_cuenta,
		"cliente_tipo": SecRepVenRet_cliente_tipo,
		"cliente_texto": SecRepVenRet_cliente_texto,
		"bono": SecRepVenRet_bono,
		"proveedor": SecRepVenRet_proveedor,
		"num_transaccion": SecRepVenRet_num_transaccion,
		"tipo_saldo": SecRepVenRet_tipo_saldo,
        "caja_vip": SecRepVenRet_Caja7
	}

	$.ajax({
		url: "/sys/get_reportes_ventas_retail.php",
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
						$('#SecRepVenRet_deposito_cant').val(item.num_deposito);
						$('#SecRepVenRet_bono_cant').val(item.num_bono);
						$('#SecRepVenRet_recarga_cant').val(item.num_recarga);
						$('#SecRepVenRet_cant_juegos_virtuales').val(item.num_juegos_virtuales);
						$('#SecRepVenRet_cant_juegos_virtuales_pagadas').val(item.num_juegos_virtuales_pagadas);
						//$('#SecRepVenRet_donacion_cant').val(item.num_donacion_cancer);
						$('#SecRepVenRet_pago_sorteo_mundial_cant').val(item.num_pago_sorteo_mundial);
						$('#SecRepVenRet_num_terminal_deposit_tambo').val(item.num_tambo);
						$('#SecRepVenRet_num_saldo_real').val(item.num_saldo_real);
						$('#SecRepVenRet_num_saldo_promocional').val(item.num_saldo_promocional);

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

						$('#SecRepVenRet_monto_total').val(item.total_deposito);
						$('#SecRepVenRet_bono_total').val(item.total_bono);
						$('#SecRepVenRet_recarga_total').val(item.total_recarga);
						$('#SecRepVenRet_total_recargas_bonos').val(total_recarga_bono);

						$('#SecRepVenRet_apuesta_cant').val(item.num_apuesta_generada - item.num_apuesta_cancelada);
						$('#SecRepVenRet_apuestapagada_cant').val(item.num_apuesta_pagada);

						var total_apuesta_pagada = parseFloat(item.total_apuesta_generada - item.total_apuesta_cancelada);
						$('#SecRepVenRet_apuesta_total').val(total_apuesta_pagada);
						
						item.total_apuesta_generada = item.total_apuesta_generada.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_apuesta_pagada = item.total_apuesta_pagada.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

						$('#SecRepVenRet_apuestapagada_total').val(item.total_apuesta_pagada);

						$('#SecRepVenRet_total_bono_apuestas_5_pct').val(item.total_bono_5pct);
						$('#SecRepVenRet_total_bono_apuestas_deportivas').val(item.total_bono_apuesta_deportiva);
						$('#SecRepVenRet_total_bono_casino').val(item.total_bono_casino);
						$('#SecRepVenRet_total_retiros').val(item.total_retiro);
						$('#SecRepVenRet_total_terminal_deposit').val(item.total_terminal_deposit);
						$('#SecRepVenRet_total_venta_bingo').val(item.total_venta_bingo);
						$('#SecRepVenRet_total_pago_bingo').val(item.total_pago_bingo);
						$('#SecRepVenRet_total_subir_balance').val(item.total_subir_balance);
						$('#SecRepVenRet_total_bajar_balance').val(item.total_bajar_balance);
						$('#SecRepVenRet_total_juegos_virtuales').val(item.total_juegos_virtuales);
						$('#SecRepVenRet_total_juegos_virtuales_pagadas').val(item.total_juegos_virtuales_pagadas);
						//$('#SecRepVenRet_total_donacion').val(item.total_donacion_cancer);
						$('#SecRepVenRet_total_pago_sorteo_mundial').val(item.total_sorteo_mundial);
						$('#SecRepVenRet_total_terminal_deposit_tambo').val(item.total_tambo);
						$('#SecRepVenRet_total_saldo_real').val(item.total_saldo_real);
						$('#SecRepVenRet_total_saldo_promocional').val(item.total_saldo_promocional);

						$('#SecRepVenRet_cant_v_torito_g').val(item.num_v_torito_g);
						$('#SecRepVenRet_cant_v_torito_mm').val(item.num_v_torito_mm);
						$('#SecRepVenRet_total_v_torito_g').val(item.total_v_torito_g);						
						$('#SecRepVenRet_total_v_torito_mm').val(item.total_v_torito_mm);


						$('#SecRepVenRet_cant_p_torito_g').val(item.num_p_torito_g);
						$('#SecRepVenRet_cant_p_torito_mm').val(item.num_p_torito_mm);	

						$('#SecRepVenRet_total_p_torito_g').val(item.total_p_torito_g);											
						$('#SecRepVenRet_total_p_torito_mm').val(item.total_p_torito_mm);


					});
				}
				if (respuesta.client_resumen.length > 0){
					$.each(respuesta.client_resumen, function(index, item) {
						$('#SecRepVenRet_clientes_unicos_cant').val(item.cant);
					});
				}
				if (respuesta.client_resumen_nuevo.length > 0){
					$.each(respuesta.client_resumen_nuevo, function(index, item) {
						if(item.is_new == 1){
							//$('#SecRepVenRet_num_clientes_nuevos').val(item.cant);	
						}else if(item.is_new == 0){
							$('#SecRepVenRet_num_clientes_antiguos').val(item.cant);	
						}
					});
				}
				$('#SecRepVenRet_num_clientes_nuevos').val(respuesta.num_clientes_nuevos);	
				return false;
			}
		},
		error: function() {}
	});
}

// EXCEL
function SecRepVenRet_RES_exportar_excel() {

	var SecRepVenRet_tipo_busqueda = $("#SecRepVenRet_tipo_busqueda").val();
	var SecRepVenRet_fecha_inicio = $.trim($("#SecRepVenRet_fecha_inicio").val());
	var SecRepVenRet_fecha_fin = $.trim($("#SecRepVenRet_fecha_fin").val());
	var SecRepVenRet_tipo_transaccion = $("#SecRepVenRet_tipo_transaccion").val();
	var SecRepVenRet_local = $("#SecRepVenRet_local").val();
	var SecRepVenRet_cajero = $('#SecRepVenRet_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepVenRet_cajero").val();
	var SecRepVenRet_estado_cierre = $("#SecRepVenRet_estado_cierre").val();
	var SecRepVenRet_cuenta = $("#SecRepVenRet_cuenta").val();
	var SecRepVenRet_cliente_tipo = $("#SecRepVenRet_cliente_tipo").val();
	var SecRepVenRet_cliente_texto = $("#SecRepVenRet_cliente_texto").val();
	var SecRepVenRet_tipo_bono = $("#sec_rpt_vent_retselect_bono").val();
	var SecRepVenRet_proveedor = $("#sec_rpt_vent_retselect_proveedor").val();
	var SecRepVenRet_num_transaccion = $("#SecRepVenRet_num_transaccion").val();
	var SecRepVenRet_tipo_saldo = $("#SecRepVenRet_tipo_saldo").val();
    var SecRepVenRet_Caja7 = $("#sec_rpt_vent_retselect_caja").val();
    
    if(SecRepVenRet_Caja7 === undefined){
        SecRepVenRet_Caja7 = 0;
    }

	if (SecRepVenRet_fecha_inicio.length !== 10) {
		$("#SecRepVenRet_fecha_inicio").focus();
		return false;
	}
	if (SecRepVenRet_fecha_fin.length !== 10) {
		$("#buscador_texto").focus();
		return false;
	}

	var data = {
		"accion": "listar_transacciones_export_xls",
		"tipo_busqueda": SecRepVenRet_tipo_busqueda,
		"fecha_inicio": SecRepVenRet_fecha_inicio,
		"fecha_fin": SecRepVenRet_fecha_fin,
		"tipo_transaccion": SecRepVenRet_tipo_transaccion,
		"local": SecRepVenRet_local,
		"cajero": SecRepVenRet_cajero,
		"estado_cierre": SecRepVenRet_estado_cierre,
		"cuenta": SecRepVenRet_cuenta,
		"cliente_tipo": SecRepVenRet_cliente_tipo,
		"cliente_texto": SecRepVenRet_cliente_texto,
		"bono": SecRepVenRet_tipo_bono,
		"proveedor": SecRepVenRet_proveedor,
		"num_transaccion": SecRepVenRet_num_transaccion,
		"tipo_saldo": SecRepVenRet_tipo_saldo,
        "caja_vip": SecRepVenRet_Caja7
	}

	//console.log(data);return false;

	$.ajax({
		url: "/sys/get_reportes_ventas_retail.php",
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





// ***************************************************************************************************
// ***************************************************************************************************
// CAJA
// ***************************************************************************************************
// ***************************************************************************************************

function SecRepVenRet_reporte_tipo_balance() {

	$('#SecRepVenRet_tabla_tipo_balance').show();
 
	var SecRepVenRet_fecha_inicio = $.trim($("#SecRepVenRet_fecha_inicio").val());
	var SecRepVenRet_fecha_fin = $.trim($("#SecRepVenRet_fecha_fin").val());
	var SecRepVenRet_tipo_transaccion = $("#SecRepVenRet_tipo_transaccion").val();
	var SecRepVenRet_local = $("#SecRepVenRet_local").val();
	var SecRepVenRet_cajero = $('#SecRepVenRet_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepVenRet_cajero").val(); 
	var SecRepVenRet_tipo_balance = $("#SecRepVenRet_tipo_balance").val();
	var SecRepVenRet_motivo_balance = $("#SecRepVenRet_motivo_balance").val();
	var SecRepVenRet_tipo_saldo = $("#SecRepVenRet_tipo_saldo").val();
	var SecRepVenRet_juego_balance = $("#SecRepVenRet_juego_balance").val();
 
	if (SecRepVenRet_fecha_inicio.length !== 10) {
		$("#SecRepVenRet_fecha_inicio").focus();
		return false;
	}
	if (SecRepVenRet_fecha_fin.length !== 10) {
		$("#SecRepVenRet_fecha_fin").focus();
		return false;
	}

	let data = {
		accion:'listar_tbl_reporte_tipo_balance',	 
		fecha_inicio: SecRepVenRet_fecha_inicio,
		fecha_fin: SecRepVenRet_fecha_fin,
		tipo_transaccion: SecRepVenRet_tipo_transaccion,
		local: SecRepVenRet_local,
		cajero: SecRepVenRet_cajero, 
		tipo_balance: SecRepVenRet_tipo_balance,
		motivo_balance: SecRepVenRet_motivo_balance,
		juego_balance: SecRepVenRet_juego_balance,
		tipo_saldo: SecRepVenRet_tipo_saldo,
 
	};

	SecRepVenRet_exportar_excel_tipo_bl();
	$.ajax({
		url: "/sys/get_reportes_ventas_retail.php",
		type: 'POST',
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.status) == 200) {
				$('#SecRepVenRet_tabla_tipo_balance').html(respuesta.result);
				sec_rep_tlv_initialize_table('SecRepVenRet_rep_tbl_tipo_balance');
				return false;
			}
		},
		error: function() {}
	});

}


function SecRepVenRet_exportar_excel_tipo_bl() {

	 
	var fecha_inicio = $.trim($("#SecRepVenRet_fecha_inicio").val());
	var fecha_fin = $.trim($("#SecRepVenRet_fecha_fin").val());
	var tipo_transaccion = $("#SecRepVenRet_tipo_transaccion").val();
	var local = $("#SecRepVenRet_local").val();
	var cajero = $('#SecRepVenRet_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepVenRet_cajero").val(); 

	var tipo_balance = $("#SecRepVenRet_tipo_balance").val();
	var motivo_balance = $("#SecRepVenRet_motivo_balance").val();
	var juego_balance = $("#SecRepVenRet_juego_balance").val();

 
	document.getElementById('SecRepVenRet_div_btn_exportar').innerHTML = '<a href="export.php?export=reporte_tipo_balance&amp;type=lista&amp;fecha_inicio='+fecha_inicio+'&amp;fecha_fin='+fecha_fin+'&amp;tipo_transaccion='+tipo_transaccion+'&amp;local='+local+'&amp;cajero='+cajero+'&amp;tipo_balance='+tipo_balance+'&amp;motivo_balance='+motivo_balance+'&amp;juego_balance='+juego_balance+'" class="btn btn-success" style="width: 100%;"  download="reporte_tipo_balance.xls"><span class="glyphicon glyphicon-download-alt"></span> Exportar </a>';
  

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

function SecRepVenRet_CAJA_tbl_rep_ingresos_salidas_listar() {

	$('#SecRepVenRet_CAJA_tbl_rep_ingresos_salidas').html('');

	var SecRepVenRet_tipo_busqueda = $("#SecRepVenRet_tipo_busqueda").val();
	var SecRepVenRet_fecha_inicio = $.trim($("#SecRepVenRet_fecha_inicio").val());
	var SecRepVenRet_fecha_fin = $.trim($("#SecRepVenRet_fecha_fin").val());
	var SecRepVenRet_tipo_transaccion = $("#SecRepVenRet_tipo_transaccion").val();
	var SecRepVenRet_local = $("#SecRepVenRet_local").val();
	var SecRepVenRet_cajero = $('#SecRepVenRet_cajero').val()!= '' ? gen_cajero_seleccionado : '0'; //$("#SecRepVenRet_cajero").val();
	var SecRepVenRet_estado_cierre = $("#SecRepVenRet_estado_cierre").val();
	var SecRepVenRet_cuenta = $("#SecRepVenRet_cuenta").val();
	var SecRepVenRet_cliente_tipo = $("#SecRepVenRet_cliente_tipo").val();
	var SecRepVenRet_cliente_texto = $("#SecRepVenRet_cliente_texto").val();
	var SecRepVenRet_tipo_bono = $("#sec_rpt_vent_retselect_bono").val();
	var SecRepVenRet_proveedor = $("#sec_rpt_vent_retselect_proveedor").val();
	var SecRepVenRet_num_transaccion = $("#SecRepVenRet_num_transaccion").val();
    var SecRepVenRet_Caja7 = $("#sec_rpt_vent_retselect_caja").val();
    
    if(SecRepVenRet_Caja7 === undefined){
        SecRepVenRet_Caja7 = 0;
    }

	if (SecRepVenRet_fecha_inicio.length !== 10) {
		$("#SecRepVenRet_fecha_inicio").focus();
		return false;
	}
	if (SecRepVenRet_fecha_fin.length !== 10) {
		$("#SecRepVenRet_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "listar_tbl_rep_ingresos_salidas",
		"tipo_busqueda": SecRepVenRet_tipo_busqueda,
		"fecha_inicio": SecRepVenRet_fecha_inicio,
		"fecha_fin": SecRepVenRet_fecha_fin,
		"tipo_transaccion": SecRepVenRet_tipo_transaccion,
		"local": SecRepVenRet_local,
		"cajero": SecRepVenRet_cajero,
		"estado_cierre": SecRepVenRet_estado_cierre,
		"cuenta": SecRepVenRet_cuenta,
		"cliente_tipo": SecRepVenRet_cliente_tipo,
		"cliente_texto": SecRepVenRet_cliente_texto,
		"bono": SecRepVenRet_tipo_bono,
		"proveedor": SecRepVenRet_proveedor,
		"num_transaccion": SecRepVenRet_num_transaccion,
        "caja_vip": SecRepVenRet_Caja7
	}

	$.ajax({
		url: "/sys/get_reportes_ventas_retail.php",
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
				$('#SecRepVenRet_CAJA_tbl_rep_ingresos_salidas').html(respuesta.res_ingresos_salidas);
				return false;
			}
		},
		error: function() {}
	});
}

function sec_rpt_vent_retexportar(){
	SecRepVenRet_RES_exportar_excel();
}