function sec_contrato_provision() {
	$('.sec_contrato_contabilidad_datepicker').datepicker({
		dateFormat: 'dd-mm-yy',
		changeMonth: true,
		changeYear: true
	}).on("change", function(ev) {
		$(this).datepicker('hide');
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
	});
	$("#cont_contrato_contabilidad_div_tabla").hide();
	sec_contrato_provision_obtener_opciones("obtener_razones_sociales", "[name='razon_social_id']");

	setTimeout(function () {
		$("#razon_social_id").focus();
		$("#razon_social_id").select2("open");
	}, 1000);

	$("#razon_social_id").change(function () {
		$("#razon_social_id option:selected").each(function () {
			razon_social_id = $(this).val();
			if (razon_social_id != 0) {
				setTimeout(function () {
					$("#cont_contabilidad_fecha_mes").select2("open");
				}, 200);
			}
		});
	});

	$("#cont_contabilidad_fecha_mes").change(function () {
		$("#cont_contabilidad_fecha_mes option:selected").each(function () {
			fecha_mes = $(this).val();
			if (fecha_mes != 0) {
				setTimeout(function () {
					$("#tipo_moneda_contable").select2("open");
				}, 200);
			}
		});
	});

	$("#tipo_moneda_contable").change(function () {
		$("#tipo_moneda_contable option:selected").each(function () {
			tipo_moneda_contable = $(this).val();
			if (tipo_moneda_contable != 0) {
				setTimeout(function () {
					$("#cont_contabilidad_fecha_comprobante").focus();
				}, 200);
			}
		});
	});

	$("#cont_contabilidad_fecha_comprobante").change(function () {
		setTimeout(function () {
			$("#cont_contabilidad_numero_comprobante").focus();
		}, 200);
	});

	$(".select2").select2({ width: "100%" });
}

function sec_contrato_provision_obtener_opciones(accion, select) {
	$.ajax({
		url: "/sys/get_contrato_provision.php",
		type: 'POST',
		data: {
			accion: accion
		},
		beforeSend: function() {},
		complete: function() {},
		success: function(datos) {
			var respuesta = JSON.parse(datos);
			$(select).find('option').remove().end();
			$(select).append('<option value="0">- Seleccione -</option>');
			$(respuesta.result).each(function(i, e) {
				opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);
			})
		},
		error: function() {}
	});
}

function sec_contrato_provision_buscar() {
	$('#div_contabilidad_boton_export').hide();
	if ($("#razon_social_id").val() == 0) {
		$("#cont_contrato_contabilidad_div_tabla").hide();
		alertify.error("Tiene que seleccionar la empresa.", 5);
		setTimeout(function () {
			$("#razon_social_id").select2("open");
		}, 200);
		return;
	} else if ($("#cont_contabilidad_fecha_mes").val() == 0) {
		$("#cont_contrato_contabilidad_div_tabla").hide();
		alertify.error("Tiene que seleccionar el periodo.", 5);
		setTimeout(function () {
			$("#cont_contabilidad_fecha_mes").select2("open");
		}, 200);
		return;
	} else if ($("#tipo_moneda_contable").val() == 0) {
		$("#cont_contrato_contabilidad_div_tabla").hide();
		alertify.error("Tiene que seleccionar el tipo de moneda.", 5);
		setTimeout(function () {
			$("#tipo_moneda_contable").select2("open");
		}, 200);
		return;
	} else if ($("#cont_contabilidad_fecha_comprobante").val() == "") {
		$("#cont_contrato_contabilidad_div_tabla").hide();
		alertify.error("Tiene que ingresar la fecha de comprobante.", 5);
		setTimeout(function () {
			$("#cont_contabilidad_fecha_comprobante").focus();
		}, 200);
		return;
	} else if ($("#cont_contabilidad_numero_comprobante").val() == "") {
		$("#cont_contrato_contabilidad_div_tabla").hide();
		alertify.error("Tiene que ingresar el numero de comprobante (para identificar el correlativo).", 5);
		setTimeout(function () {
			$("#cont_contabilidad_numero_comprobante").focus();
		}, 200);
		return;
	} else {
		sec_contrato_provision_listar_locales();
	}
}

function sec_contrato_provision_listar_locales() {
	$("#cont_contrato_contabilidad_div_tabla").show();
	var cont_contabilidad_razon_social_id = $("#razon_social_id").val();
	var cont_contabilidad_numero_comprobante = $("#cont_contabilidad_numero_comprobante").val();
	var cont_contabilidad_fecha_comprobante = $("#cont_contabilidad_fecha_comprobante").val();
	var cont_contabilidad_tipo_cambio = $("#cont_contabilidad_tipo_cambio").val();
	var cont_contabilidad_tipo_conversion = $("#cont_contabilidad_tipo_conversion").val();
	var cont_tipo_reporte = $("#tipo_reporte_contable").val();
	var cont_tipo_moneda = $("#tipo_moneda_contable").val();
	var cont_contabilidad_fecha_mes = $("#cont_contabilidad_fecha_mes").val();
	var cont_contabilidad_anio = cont_contabilidad_fecha_mes.substring(0, 4);
	var cont_contabilidad_mes = cont_contabilidad_fecha_mes.substring(5, 7);
	var data = {
		"accion": "cont_listar_locales_contabilidad_reporte",
		"cont_contabilidad_razon_social_id": cont_contabilidad_razon_social_id,
		"cont_contabilidad_numero_comprobante": cont_contabilidad_numero_comprobante,
		"cont_contabilidad_fecha_comprobante": cont_contabilidad_fecha_comprobante,
		"cont_contabilidad_tipo_cambio": cont_contabilidad_tipo_cambio,
		"cont_contabilidad_tipo_conversion": cont_contabilidad_tipo_conversion,
		"cont_tipo_reporte": cont_tipo_reporte,
		"cont_tipo_moneda": cont_tipo_moneda,
		"cont_contabilidad_anio": cont_contabilidad_anio,
		"cont_contabilidad_mes": cont_contabilidad_mes
	}
	tabla = $("#cont_locales_contabilidad_datatable").dataTable({
		language: {
			"decimal": "",
			"emptyTable": "No existen registros",
			"info": "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
			"infoEmpty": "Mostrando 0 a 0 de 0 entradas",
			"infoFiltered": "(filtered from _MAX_ total entradas)",
			"infoPostFix": "",
			"thousands": ",",
			"lengthMenu": "Mostrar _MENU_ entradas",
			"loadingRecords": "Cargando...",
			"processing": "Procesando...",
			"search": "Filtrar:",
			"zeroRecords": "Sin resultados",
			"paginate": {
				"first": "Primero",
				"last": "Ultimo",
				"next": "Siguiente",
				"previous": "Anterior"
			},
			"aria": {
				"sortAscending": ": activate to sort column ascending",
				"sortDescending": ": activate to sort column descending"
			}
		},
		"aProcessing": true,
		"aServerSide": true,
		"createdRow": function(row, data) {
			var id = data[0];
			$(row).prop('id', id).data('id', id);
		},
		"ajax": {
			url: "/sys/get_contrato_provision.php",
			data: data,
			type: "POST",
			dataType: "json",
			error: function(e) {
				console.log(e.responseText);
			}
		},
		"bDestroy": true,
		aLengthMenu: [20, 30, 40, 50, 100],
		"order": [
			1, "asc"
		],
		select: {
			style: 'single'
		}
	}).DataTable();
	sec_contrato_provision_mostrar_boton_excel_concar();
}

function sec_contrato_provision_mostrar_boton_excel_concar() {
	$('#div_contabilidad_boton_export').show();
	var cont_contabilidad_razon_social_id = $("#razon_social_id").val();
	var cont_contabilidad_fecha_mes = $("#cont_contabilidad_fecha_mes").val();
	var cont_param_anio = cont_contabilidad_fecha_mes.substring(0, 4);
	var cont_param_mes = cont_contabilidad_fecha_mes.substring(5, 7);
	var cont_param_tipo_moneda = $("#tipo_moneda_contable").val();
	var cont_param_contabilidad_numero_comprobante = $("#cont_contabilidad_numero_comprobante").val();
	var cont_param_contabilidad_fecha_comprobante = $("#cont_contabilidad_fecha_comprobante").val();
	var cont_contabilidad_tipo_de_cambio = $("#cont_contabilidad_tipo_de_cambio").val();
	document.getElementById('cont_contabilidad_boton_excel_concar').innerHTML = '<a href="contrato_export_provision_concar.php?' + 'periodo_mes=' + cont_param_mes + '&amp;periodo_anio=' + cont_param_anio + '&amp;tipo_moneda=' + cont_param_tipo_moneda + '&amp;fecha_comprobante=' + cont_param_contabilidad_fecha_comprobante + '&amp;numero_comprobante=' + cont_param_contabilidad_numero_comprobante + '&amp;razon_social_id=' + cont_contabilidad_razon_social_id + '&amp;tipo_de_cambio=' + cont_contabilidad_tipo_de_cambio + '" class="btn btn-success btn-sm export_list_btn" download="contrato_concar.xls"><span class="glyphicon glyphicon-export"></span> Exportar CONCAR excel</a>';
}