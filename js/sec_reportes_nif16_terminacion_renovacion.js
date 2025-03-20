function sec_reportes_nif16_terminacion_renovacion() {
	$(".mask_centro_costos").mask("0000");
	$(".select2").select2({
		width: "100%"
	});
	sec_rep_niif16_terminacion_renovacion_obtener_opciones("obtener_arrendatario_v", "[name='sec_rep_v_empresa']");
}

function sec_rep_niif16_terminacion_renovacion_obtener_opciones(accion, select) {
	$.ajax({
		url: "/sys/get_reportes_nif16_terminacion_renovacion.php",
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

function sec_rep_niif16_terminacion_renovacion_btn_buscar() {
	var tipo = $('#sec_rep_v_tipo').val();
	var empresa = $('#sec_rep_v_empresa').val();
	var centro_costo = $('#sec_rep_v_cc').val();
	var nomb_tienda = $('#sec_rep_v_n_tienda').val();
	let data = {
		accion: 'reporte_nif16_terminacion_renovacion',
		tipo: tipo,
		empresa: empresa,
		centro_costo: centro_costo,
		nomb_tienda: nomb_tienda,
	};
	sec_rep_niif16_terminacion_renovacion_mostrar_excel();
	$.ajax({
		url: "/sys/get_reportes_nif16_terminacion_renovacion.php",
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
				$('#sec_rep_vigencia').DataTable({
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
				sec_rep_niif16_terminacion_renovacion_mostrar_excel();
				return false;
			}
		},
		error: function() {}
	});
}

function sec_rep_niif16_terminacion_renovacion_mostrar_excel() {
	var tipo = $('#sec_rep_v_tipo').val();
	var empresa = $('#sec_rep_v_empresa').val();
	var centro_costo = $('#sec_rep_v_cc').val();
	var nomb_tienda = $('#sec_rep_v_n_tienda').val();
	document.getElementById('sec_rep_vg_btn_excel').innerHTML = '<a href="export.php?export=reporte_nif16_terminacion_renovacion&amp;type=lista&amp;tipo=' + tipo + '&amp;empresa=' + empresa + '&amp;centro_costo=' + centro_costo + '&amp;nomb_tienda=' + nomb_tienda + '" class="btn btn-success form-control export_list_btn" download="reporte_nif16_terminacion_renovacion.xls"><span class="glyphicon glyphicon-export"></span> Exportar excel</a>';
}