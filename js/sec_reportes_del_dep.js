function sec_reportes_del_dep() {
	if (sec_id == 'reportes' && sub_sec_id=='del_dep') {
 
		$('#SecRepTel_fecha_inicio_dl').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepTel_fecha_fin_dl').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepTel_fecha_inicio_dl').val($('#g_fecha_actual').val());
		$('#SecRepTel_fecha_fin_dl').val($('#g_fecha_actual').val());

		$('#SecRepTel_caja_dl').select2();
		$('#SecRepTel_motivo_dl').select2();
		$('#SecRepTel_cajero_dl').select2();
	 
		$('#SecRepTel_fecha_inicio_dl').change(function () {
			var var_fecha_change = $('#SecRepTel_fecha_inicio_dl').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepTel_fecha_inicio_dl").val($("#g_fecha_actual").val());
			}
		});
		$('#SecRepTel_fecha_fin_dl').change(function () {
			var var_fecha_change = $('#SecRepTel_fecha_fin_dl').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepTel_fecha_fin_dl").val($("#g_fecha_actual").val());
			}
		});

		$('#SecRepTel_btn_buscar_dl').click(function() {
			SecRepTel_buscar_dl();
		});

		$("#SecRepTel_btn_exportar_dl").on('click', function () {
			SecRepTel_exportar_excel_dl();
		});
 
	}
}


function SecRepTel_buscar_dl(){
 
	var caja = $('#SecRepTel_caja_dl').val();
	var fec_inicio = $('#SecRepTel_fecha_inicio_dl').val();
	var fec_fin = $('#SecRepTel_fecha_fin_dl').val();
	var motivo = $('#SecRepTel_motivo_dl').val();
	var cajero = $('#SecRepTel_cajero_dl').val();

	let data = {
		accion:'listar_transacciones_del_dep',
		caja: caja,
		fec_inicio: fec_inicio,
		fec_fin: fec_fin,
		motivo: motivo,
		cajero: cajero,
	};

	SecRepTel_exportar_excel_dl();
	$.ajax({
		url: "/sys/get_reportes_del_dep.php",
		type: 'POST',
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.status) == 200) {
				$('#reportes_del_dep_div_tabla').html(respuesta.result);
				sec_reportes_del_dep_initialize_table('sec_reportes_del_dep');
				return false;
			}
		},
		error: function() {}
	});
}

function SecRepTel_exportar_excel_dl() {

	var caja = $('#SecRepTel_caja_dl').val();
	var fec_inicio = $('#SecRepTel_fecha_inicio_dl').val();
	var fec_fin = $('#SecRepTel_fecha_fin_dl').val();
	var motivo = $('#SecRepTel_motivo_dl').val();
	var cajero = $('#SecRepTel_cajero_dl').val();

 
	document.getElementById('SecRepTel_btn_exportar_dl').innerHTML = '<a href="export.php?export=reporte_del_dep&amp;type=lista&amp;caja='+caja+'&amp;fec_inicio='+fec_inicio+'&amp;fec_fin='+fec_fin+'&amp;motivo='+motivo+'&amp;cajero='+cajero+'" class="btn btn-success" style="width: 100%;" download="reporte_del_dep.xls"><span class="glyphicon glyphicon-download-alt"></span> Exportar</a>';

}
 

function sec_reportes_del_dep_initialize_table(tabla){
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
 