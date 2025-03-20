function sec_reportes_centro_de_costos() {
	$(".select2").select2({
		width: "100%"
	});
	sec_rep_cdc_obtener_opciones("obtener_redes", "[name='sec_rep_local']");
	sec_rep_cdc_obtener_opciones("obtener_arrendatario", "[name='sec_rep_arrendatario']");
	sec_rep_cdc_obtener_opciones("obtener_departamentos", "[name='sec_rep_departamento']");

	$("#sec_rep_departamento").change(function() {
		$("#sec_rep_departamento option:selected").each(function() {
			sec_rep_departamento = $(this).val();
			var data = {
				"accion": "obtener_provincias",
				"departamento_id": sec_rep_departamento
			}
			var array_provincias = [];
			$.ajax({
				url: "/sys/get_reportes_centro_de_costos.php",
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
				url: "/sys/get_reportes_centro_de_costos.php",
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

function sec_rep_cdc_obtener_opciones(accion, select) {
	$.ajax({
		url: "/sys/get_reportes_centro_de_costos.php",
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

function sec_rep_cdc_buscar() {
	var local = $('#sec_rep_local').val();
	var centro_costo = $('#sec_rep_centro_costo').val();
	var arrendatario = $('#sec_rep_arrendatario').val();
	var nombre_tienda = $('#sec_rep_nombre_tienda').val();
	var departamento = $('#sec_rep_departamento').val();
	var provincia = $('#sec_rep_provincia').val();
	var distrito = $('#sec_rep_distrito').val();
	var direccion = $('#sec_rep_direccion').val();
	let data = {
		accion: 'reporte_locales',
		local: local,
		centro_costo: centro_costo,
		arrendatario: arrendatario,
		nombre_tienda: nombre_tienda,
		departamento: departamento,
		provincia: provincia,
		distrito: distrito,
		direccion: direccion,
	};
	sec_rep_cdc_mostrar_excel();
	$.ajax({
		url: "/sys/get_reportes_centro_de_costos.php",
		type: 'POST',
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.status) == 200) {
				$('#resportes_locales_por_centro_de_costos_div_tabla').html(respuesta.result);
				initialize_table('sec_rep_locales_por_costos');
				return false;
			}
		},
		error: function() {}
	});
}

function sec_rep_cdc_mostrar_excel() {
	var local = $('#sec_rep_local').val();
	var centro_costo = $('#sec_rep_centro_costo').val();
	var arrendatario = $('#sec_rep_arrendatario').val();
	var nombre_tienda = $('#sec_rep_nombre_tienda').val();
	var departamento = $('#sec_rep_departamento').val();
	var provincia = $('#sec_rep_provincia').val();
	var distrito = $('#sec_rep_distrito').val();
	var direccion = $('#sec_rep_direccion').val();
	document.getElementById('sec_rep_cdc_btn_excel').innerHTML = '<a href="export.php?export=reporte_por_centro_de_costos&amp;type=lista&amp;local=' + local + '&amp;centro_costo=' + centro_costo + '&amp;arrendatario=' + arrendatario + '&amp;nombre_tienda=' + nombre_tienda + '&amp;departamento=' + departamento + '&amp;provincia=' + provincia + '&amp;distrito=' + distrito + '&amp;direccion=' + direccion + '" class="btn btn-success form-control export_list_btn" download="reporte_por_centro_de_costos.xls"><span class="glyphicon glyphicon-export"></span> Exportar excel</a>';
}