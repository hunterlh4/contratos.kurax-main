function sec_reportes_nif16_cam_moneda() {
	$(".mask_centro_costos").mask("0000");
	$(".select2").select2({
		width: "100%"
	});
	sec_reportes_nif16_cambio_moneda_obtener_departamentos();
	sec_reportes_nif16_cambio_moneda_obtener_opciones("obtener_arrendatario_v", "[name='sec_rep_v_empresa']");

	$("#sec_rep_departamento").change(function() {
		$("#sec_rep_departamento option:selected").each(function() {
			sec_rep_departamento = $(this).val();
			var data = {
				"accion": "obtener_provincias",
				"departamento_id": sec_rep_departamento
			}
			var array_provincias = [];
			$.ajax({
				url: "/sys/get_reportes_vigencia.php",
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
					console.log(respuesta);
					if (parseInt(respuesta.status) == 200) {
						array_provincias.push(respuesta.result);
						var html = '<option value="0">Seleccione la provincia</option>';
						for (var i = 0; i < array_provincias[0].length; i++) {
							html += '<option value=' + array_provincias[0][i].id + '>' + array_provincias[0][i].nombre + '</option>';
						}
						$("#sec_rep_provincia").html(html).trigger("change");
						setTimeout(function() {
							$('#sec_rep_provincia').select2('open');
						}, 500);
						return false;
					}
				},
				error: function() {}
			});
		});
	});

	$("#sec_rep_provincia").change(function() {
		$("#sec_rep_provincia option:selected").each(function() {
			sec_rep_provincia = $(this).val();
			sec_rep_departamento = $("#sec_rep_departamento").val();
			var data = {
				"accion": "obtener_distritos",
				"departamento_id": sec_rep_departamento,
				"provincia_id": sec_rep_provincia,
			}
			var array_distritos = [];
			$.ajax({
				url: "/sys/get_reportes_vigencia.php",
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
					console.log(respuesta);
					if (parseInt(respuesta.status) == 200) {
						array_distritos.push(respuesta.result);
						var html = '<option value="0">Seleccione el distrito</option>';
						for (var i = 0; i < array_distritos[0].length; i++) {
							html += '<option value=' + array_distritos[0][i].id + '>' + array_distritos[0][i].nombre + '</option>';
						}
						$("#sec_rep_distrito").html(html).trigger("change");
						setTimeout(function() {
							$('#sec_rep_distrito').select2('open');
						}, 500);
						return false;
					}
				},
				error: function() {}
			});
		});
	});
}

function sec_reportes_nif16_cambio_moneda_obtener_opciones(accion, select) {
	$.ajax({
		url: "/sys/get_reportes_nif16_cambio_moneda.php",
		type: 'POST',
		data: {
			accion: accion
		},
		beforeSend: function() {},
		complete: function() {},
		success: function(datos) {
			var respuesta = JSON.parse(datos);
			$(select).find('option').remove().end();
			$(select).append('<option value="0">- TODOS -</option>');
			$(respuesta.result).each(function(i, e) {
				opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);
			})
		},
		error: function() {}
	});
}

function sec_reportes_nif16_cambio_moneda_obtener_departamentos() {
	let select = $("[name='search_id_departamento']");
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: {
			accion: "obtener_departamentos"
		},
		beforeSend: function() {},
		complete: function() {},
		success: function(datos) {
			var respuesta = JSON.parse(datos);
			$(select).find("option").remove().end();
			$(select).append('<option value="">- TODOS -</option>');
			$(respuesta.result).each(function(i, e) {
				opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);
			});
		},
		error: function() {},
	});
}

function sec_reportes_nif16_cambio_moneda_btn_buscar() {
	var empresa = $('#sec_rep_v_empresa').val();
	var centro_costo = $('#sec_rep_v_cc').val();
	var renta_m = $('#sec_rep_v_renta_m').val();
	var nomb_tienda = $('#sec_rep_v_n_tienda').val();
	var departamento = $('#search_id_departamento').val();
	var provincia = $('#search_id_provincia').val();
	var distrito = $('#search_id_distrito').val();
	var direccion = $('#sec_rep_v_direccion').val();
	var fec_suscrip = $('#sec_rep_v_fecha_suscrip').val();
	var vigencia_contrato = $('#sec_rep_v_contrato').val();
	var fec_inicio = $('#sec_rep_v_fecha_inicio').val();
	var fec_fin = $('#sec_rep_v_fecha_fin').val();
	var n_adendas = $('#sec_rep_v_n_adendas').val();
	var estado = $('#sec_rep_v_estado').val();
	let data = {
		accion: 'reporte_nif16_cambio_moneda',
		empresa: empresa,
		centro_costo: centro_costo,
		renta_m: renta_m,
		nomb_tienda: nomb_tienda,
		departamento: departamento,
		provincia: provincia,
		distrito: distrito,
		direccion: direccion,
		fec_suscrip: fec_suscrip,
		vigencia_contrato: vigencia_contrato,
		fec_inicio: fec_inicio,
		fec_fin: fec_fin,
		n_adendas: n_adendas,
		estado: estado,
	};
	sec_rep_cdc_mostrar_excel();
	$.ajax({
		url: "/sys/get_reportes_nif16_cambio_moneda.php",
		type: 'POST',
		data: data,
		success: function(resp) {
			$('#reportes_vigencia_div_tabla').html(resp);
			return false;
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.status) == 200) {
				$('#reportes_vigencia_div_tabla').html(respuesta.result);
				return false;
			}
		},
		error: function() {}
	});
}

function sec_reportes_nif16_cambio_moneda_mostrar_excel() {
	var empresa = $('#sec_rep_v_empresa').val();
	var centro_costo = $('#sec_rep_v_cc').val();
	var nomb_tienda = $('#sec_rep_v_n_tienda').val();
	var departamento = $('#search_id_departamento').val();
	var provincia = $('#search_id_provincia').val();
	var distrito = $('#search_id_distrito').val();
	var direccion = $('#sec_rep_v_direccion').val();
	document.getElementById('sec_rep_vg_btn_excel').innerHTML = '<a href="export.php?export=reporte_por_cambio_cuota_moneda&amp;type=lista&amp;empresa=' + empresa + '&amp;centro_costo=' + centro_costo + '&amp;nomb_tienda=' + nomb_tienda + '&amp;departamento=' + departamento + '&amp;provincia=' + provincia + '&amp;distrito=' + distrito + '&amp;direccion=' + direccion + '" class="btn btn-success form-control export_list_btn" download="reporte_niif_16_cambio_cuota_moneda.xls"><span class="glyphicon glyphicon-export"></span> Exportar excel</a>';
}

function sec_reportes_nif16_cambio_moneda_initialize_table(tabla) {
	$('#' + tabla + ' thead tr').clone(true).addClass('filters').appendTo('#' + tabla + ' thead');
	var table = $('#' + tabla).DataTable({
		"bDestroy": true,
		language: {
			"decimal": "",
			"emptyTable": "Tabla vacia",
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
		"order": [
			[0, 'desc']
		],
		orderCellsTop: true,
		fixedHeader: true,
		initComplete: function() {
			var api = this.api();
			api.columns().eq(0).each(function(colIdx) {
				var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
				var title = $(cell).text();
				$(cell).html('<input type="text" placeholder="' + title + '" />');
				$('input', $('.filters th').eq($(api.column(colIdx).header()).index())).off('keyup change').on('change', function(e) {
					$(this).attr('title', $(this).val());
					var regexr = '({search})';
					var cursorPosition = this.selectionStart;
					api.column(colIdx).search(this.value != '' ? regexr.replace('{search}', '(((' + this.value + ')))') : '', this.value != '', this.value == '').draw();
				}).on('keyup', function(e) {
					e.stopPropagation();
					$(this).trigger('change');
					$(this).focus()[0].setSelectionRange(cursorPosition, cursorPosition);
				});
			});
		},
	});
}

function sec_rep_nif16_cambio_moneda_cuota_btn_buscar() {
	var empresa = $('#sec_rep_v_empresa').val();
	var centro_costo = $('#sec_rep_v_cc').val();
	var nomb_tienda = $('#sec_rep_v_n_tienda').val();
	var departamento = $('#search_id_departamento').val();
	var provincia = $('#search_id_provincia').val();
	var distrito = $('#search_id_distrito').val();
	var direccion = $('#sec_rep_v_direccion').val();
	let data = {
		accion: 'reporte_nif16_cambio_cuota_moneda',
		empresa: empresa,
		centro_costo: centro_costo,
		nomb_tienda: nomb_tienda,
		departamento: departamento,
		provincia: provincia,
		distrito: distrito,
		direccion: direccion,
	};
	sec_reportes_nif16_cambio_moneda_mostrar_excel();
	$.ajax({
		url: "/sys/get_reportes_nif16_cambio_moneda.php",
		type: 'POST',
		data: data,
		beforeSend: function(xhr) {
			loading(true);
		},
		complete: function() {
			loading(false);
		},
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.status) == 200) {
				$('#reportes_vigencia_div_tabla').html(respuesta.result);
				$('#sec_rep_nif16_cambio_moneda').DataTable({
					language: {
						decimal: "",
						emptyTable: "Tabla vacia",
						info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
						infoEmpty: "Mostrando 0 a 0 de 0 entradas",
						infoFiltered: "(filtered from _MAX_ total entradas)",
						infoPostFix: "",
						thousands: ",",
						lengthMenu: "Mostrar _MENU_ entradas",
						loadingRecords: "Cargando...",
						processing: "Procesando...",
						search: "Filtrar:",
						zeroRecords: "Sin resultados",
						paginate: {
						  first: "Primero",
						  last: "Ultimo",
						  next: "Siguiente",
						  previous: "Anterior",
						},
						aria: {
						  sortAscending: ": activate to sort column ascending",
						  sortDescending: ": activate to sort column descending",
						},
						buttons: {
						  pageLength: {
							  _: "Mostrar %d Resultados",
							  '-1': "Tout afficher"
						  }
						}
						
					  },
				});
				return false;
			}
		},
		error: function() {}
	});
}

function sec_rep_nif16_generar_data_historica_reporte_nif16_cambio_cuota_moneda() {
	let data = {
		accion: 'generar_data_historica_reporte_nif16_cambio_cuota_moneda',
	};
	$.ajax({
		url: "/sys/get_reportes_nif16_cambio_moneda.php",
		type: 'POST',
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.status) == 200) {
				swal({
					title: "Good",
					text: "Se generó los cambios de cuotas históricos",
					html: true,
					type: "success",
					timer: 10000,
					closeOnConfirm: false,
					showCancelButton: false,
				});
				return false;
			}
		},
		error: function() {}
	});
}