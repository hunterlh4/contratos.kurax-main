function sec_reportes_nif16_bdt() {
	$(".mask_centro_costos").mask("0000");
	$(".select2").select2({
		width: "100%"
	});
	sec_rep_nif16_bdt_ObtenerDepartamentos();
	$('.cont_reporte_datepicker').datepicker({
		dateFormat: 'dd-mm-yy',
		changeMonth: true,
		changeYear: true,
	}).on("change", function(ev) {
		$(this).datepicker('hide');
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
	});
	sec_rep_nif16_bdt_vigencia_obtener_opciones("obtener_arrendatario_v", "[name='sec_rep_v_empresa']");
}

function sec_rep_nif16_bdt_vigencia_obtener_opciones(accion, select) {
	$.ajax({
		url: "/sys/get_reportes_nif16_bdt.php",
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

function sec_rep_nif16_bdt_ObtenerDepartamentos() {
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

function sec_rep_nif16_bdt_ObtenerProvincias() {
	$("#search_id_departamento option:selected").each(function() {
		let search_id_departamento = $("#search_id_departamento").val();
		if (search_id_departamento == "") {
			return false;
		}
		var data = {
			accion: "obtener_provincias_segun_departamento",
			departamento_id: search_id_departamento,
		};
		var array_provincias = [];
		$.ajax({
			url: "/sys/set_contrato_nuevo.php",
			type: "POST",
			data: data,
			beforeSend: function() {
				loading("true");
			},
			complete: function() {
				loading();
			},
			success: function(resp) {
				var respuesta = JSON.parse(resp);
				if (parseInt(respuesta.http_code) == 400) {}
				if (parseInt(respuesta.http_code) == 200) {
					array_provincias.push(respuesta.result);
					var html = '<option value="">- TODOS -</option>';
					for (var i = 0; i < array_provincias[0].length; i++) {
						html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
					}
					$("#search_id_provincia").html(html).trigger("change");
					setTimeout(function() {
						$("#search_id_provincia").select2("open");
					}, 500);
					return false;
				}
			},
			error: function() {},
		});
	});
}

function sec_rep_nif16_bdt_ObtenerDistritos() {
	$("#search_id_provincia option:selected").each(function() {
		let search_id_departamento = $("#search_id_departamento").val();
		let search_id_provincia = $("#search_id_provincia").val();
		if (search_id_provincia == "") {
			return false;
		}
		var data = {
			accion: "obtener_distritos_segun_provincia",
			provincia_id: search_id_provincia,
			departamento_id: search_id_departamento,
		};
		var array_distritos = [];
		$.ajax({
			url: "/sys/set_contrato_nuevo.php",
			type: "POST",
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
				if (parseInt(respuesta.http_code) == 400) {}
				if (parseInt(respuesta.http_code) == 200) {
					array_distritos.push(respuesta.result);
					var html = '<option value="">- TODOS -</option>';
					for (var i = 0; i < array_distritos[0].length; i++) {
						html += "<option value=" + array_distritos[0][i].id + ">" + array_distritos[0][i].nombre + "</option>";
					}
					$("#search_id_distrito").html(html).trigger("change");
					setTimeout(function() {
						$("#search_id_distrito").select2("open");
					}, 500);
					return false;
				}
			},
			error: function() {},
		});
	});
}

function sec_rep_nif16_bdt_obtener_opciones_v(accion, select) {
	$.ajax({
		url: "/sys/get_reportes_vigencia.php",
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

function sec_rep_nif16_bdt_btn_buscar() {
	var empresa = $('#sec_rep_v_empresa').val();
	var centro_costo = $('#sec_rep_v_cc').val();
	var nomb_tienda = $('#sec_rep_v_n_tienda').val();
	var departamento = $('#search_id_departamento').val();
	var provincia = $('#search_id_provincia').val();
	var distrito = $('#search_id_distrito').val();
	var direccion = $('#sec_rep_v_direccion').val();
	var fec_suscrip = $('#sec_rep_v_fecha_suscrip').val();
	var fec_inicio = $('#sec_rep_v_fecha_inicio').val();
	var fec_fin = $('#sec_rep_v_fecha_fin').val();
	let data = {
		accion: 'reporte_nif16_bdt',
		empresa: empresa,
		centro_costo: centro_costo,
		nomb_tienda: nomb_tienda,
		departamento: departamento,
		provincia: provincia,
		distrito: distrito,
		direccion: direccion,
		fec_suscrip: fec_suscrip,
		fec_inicio: fec_inicio,
		fec_fin: fec_fin,
	};
	sec_rep_nif16_bdt_mostrar_excel();
	$.ajax({
		url: "/sys/get_reportes_nif16_bdt.php",
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
				$('#sec_rep_vigencia').dataTable({
					"sScrollX": "100%",
					"sScrollXInner": "110%",
					"bScrollCollapse": true,
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

function sec_rep_nif16_bdt_mostrar_excel() {
	var empresa = $('#sec_rep_v_empresa').val();
	var centro_costo = $('#sec_rep_v_cc').val();
	var nomb_tienda = $('#sec_rep_v_n_tienda').val();
	var departamento = $('#search_id_departamento').val();
	var provincia = $('#search_id_provincia').val();
	var distrito = $('#search_id_distrito').val();
	var direccion = $('#sec_rep_v_direccion').val();
	var fec_suscrip = $('#sec_rep_v_fecha_suscrip').val();
	var fec_inicio = $('#sec_rep_v_fecha_inicio').val();
	var fec_fin = $('#sec_rep_v_fecha_fin').val();
	document.getElementById('sec_rep_vg_btn_excel').innerHTML = '<a href="export.php?export=reporte_nif16_bdt&amp;type=lista&amp;empresa=' + empresa + '&amp;centro_costo=' + centro_costo + '&amp;nomb_tienda=' + nomb_tienda + '&amp;departamento=' + departamento + '&amp;provincia=' + provincia + '&amp;distrito=' + distrito + '&amp;direccion=' + direccion + '&amp;fec_suscrip=' + fec_suscrip + '&amp;fec_inicio=' + fec_inicio + '&amp;fec_fin=' + fec_fin + '" class="btn btn-success form-control export_list_btn" download="reporte_niif_16_bdt.xls"><span class="glyphicon glyphicon-export"></span> Exportar excel</a>';
}