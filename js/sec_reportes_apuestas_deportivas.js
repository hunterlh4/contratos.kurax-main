function sec_reportes_apuestas_deportivas(){

	sec_reportes_apuestas_deportivas_listar_tipo_apuesta();

	setTimeout(function() {
		$(".select2").select2({ width: "100%" });
	}, 500);

	$(".sec_contrato_solicitud_datepicker").datepicker({
		dateFormat: "yy-mm-dd",
		changeMonth: true,
		changeYear: true,
	});
	$('.limpiar_input').click(function() {
		$('#' + $(this).attr("limpiar")).val('');
	});
	$('.limpiar_select2').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val("").trigger("change");
	});
	$('#btn_limpiar_filtros_de_busqueda').click(function() {
		$('#search_apuesta_generada_fecha_inicio').val('');
		$('#search_apuesta_generada_fecha_fin').val('');
		$('#search_apuesta_calculada_fecha_inicio').val('');
		$('#search_apuesta_calculada_fecha_fin').val('');
		$('#search_tipo_estado_id').select2().val('').trigger("change");
		$('#search_tipo_apuesta_id').select2().val('').trigger("change");
		setTimeout(function() {
			$(".select2").select2({
				width: "100%"
			});
		}, 200);
	});
}

function sec_reportes_apuestas_deportivas_listar_tipo_apuesta() {
	const data = { accion: 'obtener_tipo_apuesta' };

	$.ajax({
		url: "/sys/get_reportes_apuestas_deportivas.php",
		type: 'POST',
		data: data,
		success: function (response) {
			const respuesta = JSON.parse(response);
			if (respuesta.http_code === 200) {
				const select = $('#search_tipo_apuesta_id');
				select.empty().append('<option value="">-- TODOS --</option>');

				$.each(respuesta.result, function (i, e) {
					select.append($('<option>').val(e.id).text(e.nombre));
				});
			}
		},
		error: function () {
			console.error("Ha ocurrido un error al obtener los tipos de apuesta.");
		}
	});
}

function sec_reportes_apuestas_deportivas_cambiar_de_pagina(pagina) {
	$('#currentPage').val(pagina);
	sec_reportes_apuestas_deportivas_listar();
}

function sec_reportes_apuestas_deportivas_listar() {

	let currentPage = $('#currentPage').val();
	let apuesta_generada_fecha_inicio = $('#search_apuesta_generada_fecha_inicio').val().trim();
	let apuesta_generada_fecha_fin = $('#search_apuesta_generada_fecha_fin').val().trim();
	let apuesta_calculada_fecha_inicio = $('#search_apuesta_calculada_fecha_inicio').val();
	let apuesta_calculada_fecha_fin = $('#search_apuesta_calculada_fecha_fin').val();
	let tipo_estado_id = $('#search_tipo_estado_id').val();
	let tipo_apuesta_id = $('#search_tipo_apuesta_id').val();

	if (apuesta_generada_fecha_inicio == ''){
		alertify.error('Ingrese la fecha inicio de la apuesta generada.', 5);
		return false;
	}

	if (apuesta_generada_fecha_fin == ''){
		alertify.error('Ingrese la fecha fin de la apuesta generada.', 5);
		return false;
	}

	if (apuesta_generada_fecha_inicio.length > 0 && apuesta_generada_fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(apuesta_generada_fecha_inicio);
		var fecha_fin_date = new Date(apuesta_generada_fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha inicio de apuesta generada debe ser menor o igual a la fecha final', 5);
			return false;
		} else {
			let diferencia_en_dias = (fecha_fin_date - fecha_inicio_date) / (1000 * 60 * 60 * 24);

			if (diferencia_en_dias > 10) {
				alertify.error('La diferencia entre las fechas de inicio y fin de apuesta generada no debe ser mayor a 10 días', 10);
				return false;
			}
		}
	}

	if (apuesta_calculada_fecha_inicio.length > 0 && apuesta_calculada_fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(apuesta_calculada_fecha_inicio);
		var fecha_fin_date = new Date(apuesta_calculada_fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha inicio de apuesta calculada debe ser menor o igual a la fecha final', 5);
			return false;
		} else {
			let diferencia_en_dias = (fecha_fin_date - fecha_inicio_date) / (1000 * 60 * 60 * 24);

			if (diferencia_en_dias > 10) {
				alertify.error('La diferencia entre las fechas de inicio y fin de apuesta calculada no debe ser mayor a 10 días', 10);
				return false;
			}
		}
	}

	currentPage = currentPage.length == 0 ? 1 : currentPage;

	let data = {
		accion: 'listar_apuestas_deportivas',
		page: currentPage,
		apuesta_generada_fecha_inicio: apuesta_generada_fecha_inicio,
		apuesta_generada_fecha_fin: apuesta_generada_fecha_fin,
		apuesta_calculada_fecha_inicio: apuesta_calculada_fecha_inicio,
		apuesta_calculada_fecha_fin: apuesta_calculada_fecha_fin,
		tipo_estado_id: tipo_estado_id,
		tipo_apuesta_id: tipo_apuesta_id,
	};

	$.ajax({
		url: "sys/get_reportes_apuestas_deportivas.php",
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
			var $tablaApuestasDeportivas = $('#tabla_apuestas_deportivas');
			var $tbodyTablaApuestasDeportivas = $('#tbody_tabla_apuestas_deportivas');
			var $divTablaApuestasDeportivasFooter = $('#div_tabla_apuestas_deportivas_footer');

			if ($.fn.dataTable.isDataTable($tablaApuestasDeportivas)) {
				$tablaApuestasDeportivas.DataTable().destroy();
			}

			var tabla = $tablaApuestasDeportivas[0];

			switch (respuesta.http_code) {
				case 200:
					$tbodyTablaApuestasDeportivas.html(respuesta.result);
					$divTablaApuestasDeportivasFooter.html(respuesta.result_footer);

					/**

					if (tabla.tHead && tabla.tHead.rows.length > 1) {
						tabla.tHead.deleteRow(1);
					}

					$tablaApuestasDeportivas.find('thead tr').clone(true).addClass('filters').appendTo(tabla.tHead);

					$tablaApuestasDeportivas.dataTable({
						language: {
							decimal: "",
							emptyTable: "No existen registros",
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
						},
						ordering: true,
						orderCellsTop: true,
						fixedHeader: true,
						initComplete: function () {
							var api = this.api();
							api.columns().eq(0).each(function(colIdx) {
								var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
								var title = $(cell).text();
								$(cell).html('<input type="text" placeholder="' + title + '" style="width: 100px;" />');
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
					}); **/
					break;

				case 400:
					$tbodyTablaApuestasDeportivas.html(respuesta.result);

					if (tabla.tHead && tabla.tHead.rows.length > 1) {
						tabla.tHead.deleteRow(1);
					}
					break;

				default:
					swal({
						title: respuesta.consulta_error,
						type: 'info',
						timer: 5000,
						showConfirmButton: true
					});
			}
		},
		error: function() {}
	});
}

function sec_reportes_apuestas_deportivas_mostrar_excel(){

	let apuesta_generada_fecha_inicio = $('#search_apuesta_generada_fecha_inicio').val();
	let apuesta_generada_fecha_fin = $('#search_apuesta_generada_fecha_fin').val();
	let apuesta_calculada_fecha_inicio = $('#search_apuesta_calculada_fecha_inicio').val();
	let apuesta_calculada_fecha_fin = $('#search_apuesta_calculada_fecha_fin').val();
	let tipo_estado_id = $('#search_tipo_estado_id').val();
	let tipo_apuesta_id = $('#search_tipo_apuesta_id').val();

	if (apuesta_generada_fecha_inicio.length > 0 && apuesta_generada_fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(apuesta_generada_fecha_inicio);
		var fecha_fin_date = new Date(apuesta_generada_fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha inicio de apuesta generada debe ser menor o igual a la fecha final', 5);
			return false;
		}
	}

	if (apuesta_calculada_fecha_inicio.length > 0 && apuesta_calculada_fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(apuesta_calculada_fecha_inicio);
		var fecha_fin_date = new Date(apuesta_calculada_fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha inicio de apuesta calculada debe ser menor o igual a la fecha final', 5);
			return false;
		}
	}

	var data = {
		"accion": "SecRptApuestasDeportivas_exportar_xls",
		"apuesta_generada_fecha_inicio": apuesta_generada_fecha_inicio,
		"apuesta_generada_fecha_fin": apuesta_generada_fecha_fin,
		"apuesta_calculada_fecha_inicio": apuesta_calculada_fecha_inicio,
		"apuesta_calculada_fecha_fin": apuesta_calculada_fecha_fin,
		"tipo_estado_id": tipo_estado_id,
		"tipo_apuesta_id": tipo_apuesta_id
	}

	$.ajax({
		url: "/sys/get_reportes_apuestas_deportivas.php",
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
			if (obj.error !== undefined) {
				swal({
					title: obj.error,
					type: 'info',
					timer: 5000,
					showConfirmButton: true
				});
			} else {
				window.open(obj.path);
			}
			loading(false);
		},
		error: function() {}
	});
	 
}