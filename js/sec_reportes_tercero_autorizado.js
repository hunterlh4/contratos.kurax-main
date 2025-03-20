function sec_reportes_tercero_autorizado() {
	if (sec_id == 'reportes' && sub_sec_id=='tercero_autorizado') {
 
		$('#SecRepTerAut_fecha_inicio_dl').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepTerAut_fecha_fin_dl').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepTerAut_fecha_inicio_dl').val($('#SecRepTerAut_fecha_actual').val());
		$('#SecRepTerAut_fecha_fin_dl').val($('#SecRepTerAut_fecha_actual').val());
		 
		$('#SecRepTerAut_cajero_dl').select2();
		$('#SecRepTerAut_cliente_dl').select2();
	 
		$('#SecRepTerAut_fecha_inicio_dl').change(function () {
			var var_fecha_change = $('#SecRepTerAut_fecha_inicio_dl').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepTerAut_fecha_inicio_dl").val($("#SecRepTerAut_fecha_actual").val());
			}
		});
		$('#SecRepTerAut_fecha_fin_dl').change(function () {
			var var_fecha_change = $('#SecRepTerAut_fecha_fin_dl').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepTerAut_fecha_fin_dl").val($("#SecRepTerAut_fecha_actual").val());
			}
		});

		$('#SecRepTerAut_btn_buscar_dl').click(function() {
			SecRepTerAut_buscar_dl();
		});

		$("#SecRepTerAut_btn_exportar_dl").on('click', function () {
			SecRepTerAut_exportar_excel_dl();
		});
 
	}
}


function SecRepTerAut_buscar_dl(){
 
	 
	var fec_inicio = $('#SecRepTerAut_fecha_inicio_dl').val();
	var fec_fin = $('#SecRepTerAut_fecha_fin_dl').val();	
	var cajero = $('#SecRepTerAut_cajero_dl').val();
	var cliente = $('#SecRepTerAut_cliente_dl').val();
 
	let data = {
		accion:'listar_registros_tercero_autorizado',
		fec_inicio: fec_inicio,
		fec_fin: fec_fin,		
		cajero: cajero,
		cliente: cliente,
	};

	SecRepTerAut_exportar_excel_dl();
	$.ajax({
		url: "/sys/get_reportes_tercero_autorizado.php",
		type: 'POST',
		data: data,
		success: function(resp) {
		 
			var respuesta = JSON.parse(resp);
			//console.log(respuesta);
			if (parseInt(respuesta.status) == 200) {
				$('#reportes_tercero_autorizado_div_tabla').html(respuesta.result);
				sec_reportes_tercero_autorizado_initialize_table('sec_reportes_tercero_autorizado');
				return false;
			}
		},
		error: function() {}
	});
}

function SecRepTerAut_exportar_excel_dl() {
	 
	var fec_inicio = $('#SecRepTerAut_fecha_inicio_dl').val();
	var fec_fin = $('#SecRepTerAut_fecha_fin_dl').val();
	var cajero = $('#SecRepTerAut_cajero_dl').val();
	var cliente = $('#SecRepTerAut_cliente_dl').val();
 
	document.getElementById('SecRepTerAut_btn_exportar_dl').innerHTML = '<a href="export.php?export=reporte_tercero_aut&amp;type=lista&amp;fec_inicio='+fec_inicio+'&amp;fec_fin='+fec_fin+'&amp;cliente='+cliente+'&amp;cajero='+cajero+'" class="btn btn-success" style="width: 100%;" download="reporte_tercero_aut.xls"><span class="glyphicon glyphicon-download-alt"></span> Exportar</a>';

}
 

function sec_reportes_tercero_autorizado_initialize_table(tabla){
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
		,"order": [[ 0, 'asc' ]]
	});  
}


function SecRepTerAut_ver_detalle(id) {
 
	var data = {
		"accion": "listado_detalle_titular_abono",
		"id_cliente": id 
	}

	$.ajax({
		url: "/sys/get_reportes_tercero_autorizado.php",
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
				//console.log(respuesta);

				$('#SecRepTerAut_modal_detalle').modal('show');			 
				$('#modal_det_terceros tbody').html('');
				i = 0;
				$.each(respuesta.result, function(index, item) {
					i ++;
					$('#modal_det_terceros tbody').append(
						'<tr>'+
							'<td style="text-align: center;">' + i + '</td>'+
							'<td style="text-align: center;">' + item.dni_titular + '</td>'+
							'<td style="text-align: center;">' + item.nombre_apellido_titular + '</td>'+
							'<td style="text-align: center;">' + item.cajero + '</td>'+
							'<td style="text-align: center;">' + item.created_at + '</td>'+
						 
						'</tr>'
						 
					);
				});			 
			 
				return false;
			}else{

				$('#SecRepTerAut_modal_detalle').modal('show');			 
				$('#modal_det_terceros tbody').html('');

				$('#modal_det_terceros tbody').append(
					'<tr>' +
					'   <td colspan="4">No hay registros</td>' +
					'</tr>'
					);
					return false;
			}
		},
		error: function (result) {
			auditoria_send({"proceso": "listado_eliminar_titular_abono", "data": result});
		}
	});

	
}