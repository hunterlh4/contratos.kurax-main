$(document).ready(function () {
	if ($('#id_sec_reporte_saldo_disashop').length == 0) {
	} else {
		fncGetaDataFiltros();
		fncRenderizarDataTableListadoSaldoDisashop();
		$("#txtDisashopSearch").bind("input propertychange", function (evt) {
			// If it's the propertychange event, make sure it's the value that changed.
			if (window.event && event.type == "propertychange" && event.propertyName != "value")
				return;

			// Clear any previously set timer before setting a fresh one
			window.clearTimeout($(this).data("timeout"));
			$(this).data("timeout", setTimeout(function () {
				$('#tbl_saldo_disashop').DataTable().search(fncListadoSaldoDisashopSearchValue()).draw();
			}, 2000));
		});
		$('#cbLocalesDisashop').select2();

		$('#cbDisashopZona').on('change', function () {
			$('#tbl_saldo_disashop').DataTable().search(fncListadoSaldoDisashopSearchValue()).draw();
		});
		$('#cbDisashopTipo').on('change', function () {
			$('#tbl_saldo_disashop').DataTable().search(fncListadoSaldoDisashopSearchValue()).draw();
		});

		/*$('#cbDisashopTipoHistoryLocalId, #cbLocalesDisashop').on('change', function () {
			if ($.fn.DataTable.isDataTable('#tbl_saldo_history_Disashop')) {				
					$('#tbl_saldo_history_Disashop').DataTable().search(fncListadoSaldoDisashopHistorySearchValue()).draw();				
			}
		});*/

		$('#cbLocalesDisashop').on('select2:select', function (e) {
			var data = e.params.data;
			$("#hdDisashopHistoryLocalId").val(data.id);
			//$("#cbDisashopTipoHistoryLocalId").val(0).change();
			$("#cbDisashopTipoHistoryLocalId").val(0);
			$("#dtDisashopHistoryHasta").val("");
			$("#dtDisashopHistoryDesde").val("");
			fncRenderTableHistoryLocalId();
		});


		$('#dtDisashopHistoryDesde,#dtDisashopHistoryHasta').change(function (e) {
			e.preventDefault();
			if ($.fn.DataTable.isDataTable('#tbl_saldo_history_Disashop')) {
				var fechaInicio = $("#dtDisashopHistoryDesde").val();
				var fechaFin = $("#dtDisashopHistoryHasta").val();
				if (fechaInicio != '' & fechaFin != '') {
					if (new Date(fechaInicio) > new Date(fechaFin)) {
					}
					else {
						$('#tbl_saldo_history_Disashop').DataTable().search(fncListadoSaldoDisashopHistorySearchValue()).draw();
					}
				}
			}
		});

		$("#btnShowDisashopFullHistory").click(function (e) {
			e.preventDefault();

			$("#hdDisashopHistoryLocalId").val(0);
			$("#mdDisashopHistory").modal("show");
			$('#txtDisashopHistoryTitle').css('display', 'none');
			$('#divCbLocalesDisashop').css('display', '');

			/* -- filtros -- */
			//$("#cbDisashopTipoHistoryLocalId").val(0).change();
			$("#cbDisashopTipoHistoryLocalId").val(0);
			$('#cbLocalesDisashop').val(0).change();
			$("#dtDisashopHistoryHasta").val("");
			$("#dtDisashopHistoryDesde").val("");
			/* -- /filtros -- */
			var table = $('#tbl_saldo_history_Disashop').DataTable();
			table.clear();
			table.destroy();
			$('#tbl_saldo_history_Disashop').DataTable({
				dom:
					"<'row'<'col-sm-12'tr>>" +
					"<'row'<'col-sm-4'i><'col-sm-4 text-center'l><'col-sm-4'p>>",
			});


		});

		$("#cbLocalesDisashopRecarga").select2({
			placeholder: "Selecciones un Local",
			allowClear: true
		});
		$('#btnRecargaNew').on('click', function (event) {
			$('#titleDisashopRecarga').html("Recargar Locales");
			$('#cbLocalesDisashopRecarga').val([]).trigger('change');
			$("#cbLocalesDisashopRecarga").prop("disabled", false);
			$("#mdDisashopRecarga").modal("show");
			$("#formDisashopRecarga").validate().resetForm();

		});

		$('#idBtnRecargarSaldoDisashop').on('click', (e) => {
			loading(true);
			$("#idBtnRecargarSaldoDisashop").prop('disabled', true);
			e.preventDefault();

			$('#formDisashopRecarga').submit();
			//alert("Check");
			return false;
		});

		$('#formDisashopRecarga').validate({
			rules: {
				cbLocalesDisashopRecarga: {
					required: true,
				},
				txtDisashopRecarga: {
					required: true,
				},
				dtDisashopHistoryRecargaDesde: {
					required: true,
				},
				dtDisashopHistoryRecargaDesdeTime: {
					required: true,
				}

			},
			messages: {
				cbLocalesDisashopRecarga: {
					required: "Por favor, selecciones un Local",
				},
				txtDisashopRecarga: {
					required: "Por favor, un monto es necesario",
				},
				dtDisashopHistoryRecargaDesde: {
					required: "Por favor, una fecha es requerida",
				}
				,
				dtDisashopHistoryRecargaDesdeTime: {
					required: "Por favor, una Hora es requerida",
				}
			},
			submitHandler: function (form) {
				var formData = new FormData();
				formData.append('accion', 'increment_disashop');
				formData.append('idLocal', $("#cbLocalesDisashopRecarga").val());
				formData.append('monto', $("#txtDisashopRecarga").val());
				formData.append('fecha', $("#dtDisashopHistoryRecargaDesde").val());
				formData.append('hora', $("#dtDisashopHistoryRecargaDesdeTime").val());				
				var filesMarketing = document.getElementById('idFileDisashopRecarga').files.length;
				for (var x = 0; x < filesMarketing; x++) {
					formData.append("filesDisashopRecarga[]", document.getElementById('idFileDisashopRecarga').files[x]);
				}
				$.ajax({
					type: "POST",
					data: formData,
					url: 'sys/get_saldo_disashop.php',
					contentType: false,
					processData: false,
					cache: false,
					success: function (response) {
						var jsonData = JSON.parse(response);
						$("#mdDisashopRecarga").modal("hide");
						loading(false);
						$("#idBtnRecargarSaldoDisashop").prop('disabled', false);
						swal({
							title: "Recarga actualizada",
							text: "El saldo se ha actualizado correctamente",
							type: "success",
							timer: 1000,
							closeOnConfirm: true
						});
						$("#txtDisashopRecarga").val('');
						$("#dtDisashopHistoryRecargaDesde").val('');
						$("#dtDisashopHistoryRecargaDesdeTime").val('');
						$("#idFileDisashopRecarga").val('');		
					}
				});
				return false;
			}
		});

		$("#btnExportDisashop").on("click", function(event){
			event.preventDefault();
			loading(true);
			var get_data 	= {};
			get_data.accion = 'export_reporte_disashop';
			get_data.zona 	= $("#cbDisashopZona option:selected").val();
			get_data.tipo 	= $("#cbDisashopTipo option:selected").val();
			get_data.filter = $("#txtDisashopSearch").val();
			$.ajax({
				type: "POST",
				url: "sys/get_saldo_disashop.php",
				data: get_data,
				success: function (response) {
					var jsnoData = JSON.parse(response);
					loading();
					window.open(jsnoData.path);				
				}
			});
		});

		$("#btnDisashopHistoryExport").on("click", function(event){
			event.preventDefault();
			//loading(true);
			var get_data 	= {};
			get_data.accion = 'export_reporte_disashop_history';
			get_data.desde 	= $("#dtDisashopHistoryDesde").val();
			get_data.hasta 	= $("#dtDisashopHistoryHasta").val();
			get_data.tipo 	= $("#cbDisashopTipoHistoryLocalId option:selected").val();
			get_data.localId 	= $("#hdDisashopHistoryLocalId").val();
			
			$.ajax({
				type: "POST",
				url: "sys/get_saldo_disashop.php",
				data: get_data,
				success: function (response) {
					var jsnoData = JSON.parse(response);
					window.open(jsnoData.path);				
				}
			});
		});


		$(document).on('click', '#btnRecargaNewMasicaDisashop', function (event) {
			event.preventDefault();
			loading(true);
			
			$("#mdDisashopRecargaMasico").modal("show");
			loading();
		});

		$(document).on('click', '#guardar_recargar_masiva_disashop', function (event) {
			event.preventDefault();
			save_data_disashop_masivo();
		});
		
	}
});

function save_data_disashop_masivo() {
	myTable = $('#tbl_sec_saldo_disashop_masivo').DataTable();
	var form_data = myTable.rows().data();
	let new_data = new Array();
	$.each(form_data, function (key, value) {
		if (value.error_cc == false && value.error_proveedor_id == false) {
			new_data.push(value);
		}
	});

	swal({
		title: "¿Estás seguro?",
		text: 'Se insertaran datos para DISASHOP',
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Si, proceder",
		cancelButtonText: "No, cancelar",
		closeOnConfirm: false,
		closeOnCancel: false,

	},
		function (isConfirm) {
			var data = {
				'accion': 'save_data_recarga_by_file_disashop',
				'data_save': JSON.stringify(new_data),
				'date': $("#dtDisashopHistoryRecargaMasivaDesde").val(),
				'time': $("#dtDisashopHistoryRecargaMasivaDesdeTime").val(),
			}
			var formData = new FormData();
			formData.append('accion', 'save_data_recarga_by_file_disashop');
			var file = document.getElementById('file-input-disashop-masivo-backup').files.length;
			if (file > 0) {
				formData.append("file", document.getElementById('file-input-disashop-masivo-backup').files[0]);
			}
			formData.append("data_save", JSON.stringify(new_data));
			formData.append("date", $("#dtDisashopHistoryRecargaMasivaDesde").val());
			formData.append("time", $("#dtDisashopHistoryRecargaMasivaDesdeTime").val());
			if (isConfirm) {
				$.ajax({
					type: "POST",
					data: formData,
					url: 'sys/get_saldo_disashop.php',
					contentType: false,
					processData: false,
					cache: false,
					success: function (response) {

						var jsonData = JSON.parse(response);
						if (jsonData.error == false) {
							swal("OK", jsonData.message, "success");
							loading();
							fncRenderTableDisashopMasiva();
							$("#dtDisashopHistoryRecargaMasivaDesdeTime").val('');
						} else {
							swal("Error", jsonData.message, "error");
							loading();
						}
					}, beforeSend: function () {
						loading(true);
					}
				});
			} else {
				swal("Cancelado", "Los datos no se enviaron", "error");
			}
		});
}

function fncGetFileSecRecargaDisashopMasivo() {

	var formData = new FormData();
	formData.append('accion', 'get_data_recarga_by_file');
	//var filesDisashop = document.querySelector('[data-disashopId="' + idDisashop + '"]').files.length;
	var files = document.getElementById('file-input-disashop-masivo').files.length;
	if (files > 0) {
		var file = document.getElementById('file-input-disashop-masivo').files[0];
		formData.append("file", file);
		var nuevoFileList = new DataTransfer();
		nuevoFileList.items.add(new File([file], file.name, { type: file.type }));
		// Asignar el nuevo FileList al segundo input
		document.getElementById('file-input-disashop-masivo-backup').files = nuevoFileList.files;
		//limpiamos el input original
		document.getElementById('file-input-disashop-masivo').value = "";

		$.ajax({
			type: "POST",
			data: formData,
			url: 'sys/get_saldo_disashop.php',
			contentType: false,
			processData: false,
			cache: false,
			success: function (response) {
				var jsonData = JSON.parse(response);
				if (jsonData.error == true) {
					swal("Error", jsonData.message, "error");
					loading();
					
				}else{
					fncRenderTableDisashopMasiva(jsonData.data);
				} 
				

			}, beforeSend: function () {
				//loading(true);
			}
		});
	}

}

function fncRenderTableDisashopMasiva(data = {}) {
	var table = $('#tbl_sec_saldo_disashop_masivo').DataTable();
	table.clear();
	table.destroy();
	//loading(true);
	var table = $('#tbl_sec_saldo_disashop_masivo').DataTable({
		'destroy': true,
		"autoWidth": false,
		scrollX: true,
		"lengthChange": false,
		"dom": 'Bfrtip',
		"fnDrawCallback": function (oSettings) {
			$(function () {
				//$('[data-toggle="popover"]').popover()
			})
		},
		buttons: {
			buttons: [

			]
		},
		"language": {
			processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> '
		},
		"data": data,
		"ordering": true,
		"language": {
			url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
		},
		"columns": [

			{
				"data": "cc",
				render: function (data, type, row) {
					var codigo = '[' + data + ']';
					return codigo;
				}
			},

			{
				"data": "monto",
			},
			{
				"data": "proveedor_id"
			},
			{
				"data": "nombre"
			}

		],
		"createdRow": function (row, data, dataIndex, cells) {
			var code_color = '';
			if (data.error_proveedor_id == false && data.error_cc == false) {
				code_color = '#93ebd0';
			} else {
				code_color = '#eaa1a7';
			}
			$(row).css("background-color", code_color);
		}
	});
	$('#tbl_sec_saldo_disashop_masivo tbody').off('click');


}
function fncListadoSaldoDisashopSearchValue() {
	var txtDisashopSearch = $("#txtDisashopSearch").val();
	var cbDisashopZona = $("#cbDisashopZona").val();
	var cbDisashopTipo = $("#cbDisashopTipo").val();
	var data = {
		"local": txtDisashopSearch,
		"zona": cbDisashopZona,
		"tipo": cbDisashopTipo,
	}
	return JSON.stringify(data);
}
function fncListadoSaldoDisashopHistorySearchValue() {
	var cbLocalesDisashop = $("#cbLocalesDisashop").val();
	var cbDisashopTipo = $("#cbDisashopTipoHistoryLocalId").val();
	var to_date = $("#dtDisashopHistoryDesde").val();
	var from_date = $("#dtDisashopHistoryHasta").val();
	var data = {
		"local_id": cbLocalesDisashop,
		"tipo": cbDisashopTipo,
		"to_date": to_date,
		"from_date": from_date

	}
	return JSON.stringify(data);
}
function fncRenderizarDataTableListadoSaldoDisashop() {

	var table = $('#tbl_saldo_disashop').DataTable();
	table.clear();
	table.destroy();
	var table = $('#tbl_saldo_disashop').DataTable({
		'destroy': true,
		"autoWidth": false,
		'processing': true,
		'serverSide': true,
		"ajax": {
			type: "POST",
			async: true,
			"url": "sys/get_saldo_disashop.php",
			"data": { accion: 'listar_saldo_disashop' },
			dataSrc: function (json) {
				$('#td_sum_total_records').html((json.sum_records));
				return json.data;
			}
		},
		"ordering": false,
		"language": {
			url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
		},
		columnDefs: [
			{
				className: 'text-right',
				targets: [4,7]
			},
			{ "width": "5%", "targets": 0 },
			{ "width": "20%", "targets": 1 },
			{ "width": "8%", "targets": 3 },
			{ "width": "8%", "targets": 4 },
			{ "width": "10%", "targets": 7 },
			{
				"targets": 4,
				"createdCell": function (td, cellData, rowData, row, col) {
					var saldo_final = parseFloat(rowData.saldo_final);
					var monto_disashop = parseFloat(rowData.monto_disashop);
					var max = monto_disashop * 1.5 ? monto_disashop * 1.5 : 1500;
					var min = monto_disashop * 0.5 ? monto_disashop * 0.2 : 200;
					var medium = (monto_disashop != 0) ? monto_disashop : 1000;
					var class_bg = "bg-success text-white text-bold";
					if (saldo_final > max) class_bg = "bg-warning text-bold";
					if (saldo_final < min) class_bg = "bg-danger text-white text-bold";
					$(td).addClass(class_bg);
				}
			}
		],
		dom:
			"<'row'<'col-sm-12'tr>>" +
			"<'row'<'col-sm-4'i><'col-sm-4 text-center'l><'col-sm-4'p>>",
		"columns": [

			{
				"data": "cc_id",
				render: function (data, type, row) {
					var codigo = '[' + data + ']';
					return codigo;
				}
			},
			{
				"data": "nombre_local"
			},
			{
				"data": "terminal"
			},
			{
				"data": "nombre_zona"

			},
			{
				"data": "saldo_final"
			},
			{
				"data": "saldo_tipo"
			},
			{
				"data": "created_at"
			},
			{
				"data": "created_at",
				render: function (data, type, row) {
					var ver_caja = "";
					if (row.caja_id != null || row.caja_id == '') {
						ver_caja = '<a title="Ver Caja" target="_blank" href="./?sec_id=caja&amp;item_id=' + row.caja_id + '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>';
					}

					var btn_return = ver_caja + ' <button title="Modificar Saldo" id="" class="btn btn-xs btn-success btnIncrementSaldo"><i class="fa fa-money"></i></button> <button title="Ver Historial"  class="btn btn-xs btn-warning btnShowDisashopHistory"><i class="fa fa-list"></i></button>';
					return btn_return;
				}
			}

		]


	});
	$('#tbl_saldo_disashop tbody').off('click');

	$('#tbl_saldo_disashop tbody').on('click', '.btnShowDisashopHistory', function () {
		var data = table.row($(this).parents('tr')).data();
		var rowColor = $(this).parents('tr');
		var idRow = table.row($(this).parents('tr'));

		var rowData = null;
		if (data == undefined) {
			var selected_row = $(this).parents('tr');
			if (selected_row.hasClass('child')) {
				selected_row = selected_row.prev();
			}
			rowData = $('#tbl_saldo_disashop').DataTable().row(selected_row).data();
		} else {
			rowData = data;
		}

		//$("#mdDisashopHistory").modal("show");
		$("#txtDisashopHistoryTitle").html(rowData.nombre_local);
		$("#hdDisashopHistoryLocalId").val(rowData.local_id);

		fncRenderTableHistoryLocalId();

		if ($.fn.DataTable.isDataTable('#tbl_saldo_history_Disashop')) {
			$('#txtDisashopHistoryTitle').css('display', '');
			$('#divCbLocalesDisashop').css('display', 'none');
			$("#mdDisashopHistory").modal("show");

			/* -- filtros -- */
			//$("#cbDisashopTipoHistoryLocalId").val(0).change();
			$("#cbDisashopTipoHistoryLocalId").val(0);
			$("#dtDisashopHistoryHasta").val(moment().format('YYYY-MM-DD'));
			$("#dtDisashopHistoryDesde").val(moment().format('YYYY-MM-DD'));
			/* -- /filtros -- */
		}

		/*
		var data = new FormData();
		data.append('accion', 'sec_soporte_alerta_deposit_web_cambiar_estado');
		data.append('id', rowData.id);
		var checked_estado = '';
		$.ajax({
			type: "POST",
			data: data,
			url: 'sys/get_soporte_alerta_deposit_web.php',
			contentType: false,
			processData: false,
			cache: false,
			success: function (response) {
				var jsonData = JSON.parse(response);
				if (jsonData.error == false) {
					swal("Apagado", jsonData.message, "success");
					var idSpan_elemente = '#idSpan' + rowData.id;
					$(idSpan_elemente).text('Apagado');
				} else {
					swal("Error", jsonData.message, "error");
				}
				var switch_elemente = '#' + rowData.id
				$(switch_elemente).css('display', 'none');
			}
		});
		*/


	});

	$('#tbl_saldo_disashop tbody').on('click', '.btnIncrementSaldo', function () {

		var data = table.row($(this).parents('tr')).data();
		var rowColor = $(this).parents('tr');
		var idRow = table.row($(this).parents('tr'));

		var rowData = null;
		if (data == undefined) {
			var selected_row = $(this).parents('tr');
			if (selected_row.hasClass('child')) {
				selected_row = selected_row.prev();
			}
			rowData = $('#tbl_saldo_disashop').DataTable().row(selected_row).data();
		} else {
			rowData = data;
		}
		$("#cbLocalesDisashopRecarga").val(rowData.local_id).trigger("change");
		$('#titleDisashopRecarga').html(rowData.nombre_local);
		$("#mdDisashopRecarga").modal("show");
		$("#formDisashopRecarga").validate().resetForm();
		$("#txtDisashopLocalId").val(rowData.id);
		$("#cbLocalesDisashopRecarga").prop("disabled", true);

	});



}
function fncRenderTableHistoryLocalId() {
	var table = $('#tbl_saldo_history_Disashop').DataTable();
	table.clear();
	table.destroy();
	loading(true);
	let data =  { accion: 'listar_saldo_disashop_history', local_id: $("#hdDisashopHistoryLocalId").val() };
	var table = $('#tbl_saldo_history_Disashop').DataTable({
		'destroy': true,
		"autoWidth": false,
		'processing': true,
		"language": {
			processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> '
		},
		'serverSide': true,
		"ajax": {
			type: "POST",
			async: false,
			"url": "sys/get_saldo_disashop.php",
			"data": data,
			dataSrc: function (json) {
				loading();
				return json.data;
			}
		},
		"ordering": false,
		"language": {
			url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
		},
		columnDefs: [
			{
				className: 'text-right',
				targets: [10]
			},
			{ "width": "5%", "targets": 0 },
			{ "width": "20%", "targets": 1 },
			{ "width": "8%", "targets": 4 },
			{
				"targets": 7,
				"createdCell": function (td, cellData, rowData, row, col) {
					var saldo_final = parseFloat(rowData.saldo_final);
					var monto_disashop = parseFloat(rowData.monto_disashop);
					var max = monto_disashop * 1.5 ? monto_disashop * 1.5 : 1500;
					var min = monto_disashop * 0.5 ? monto_disashop * 0.2 : 200;
					var medium = (monto_disashop != 0) ? monto_disashop : 1000;
					var class_bg = "bg-success text-white text-bold";
					if (saldo_final > max) class_bg = "bg-warning text-bold";
					if (saldo_final < min) class_bg = "bg-danger text-white text-bold";
					$(td).addClass(class_bg);
				}
			}
		],
		dom:
			"<'row'<'col-sm-12'tr>>" +
			"<'row'<'col-sm-4'i><'col-sm-4 text-center'l><'col-sm-4'p>>",
		"columns": [

			{
				"data": "cc_id",
				render: function (data, type, row) {
					var codigo = '[' + data + ']';
					return codigo;
				}
			},
			{
				"data": "nombre_local"
			},
			{
				"data": "created_at"
			},
			{
				"data": "turno_id"

			},
			{
				"data": "disashop_tipo"
			},
			{
				"data": "saldo_anterior"
			},
			{
				"data": "saldo_incremento"
			},
			{
				"data": "saldo_final"
			},
			{
				"data": "personal_nombre"
			},
			{
				"data": "files",
				render: function (data, type, row) {

					var ver_caja = "<div id='td" + row.id + "'>";
					if (data.length > 0) {
						$.each(data, function (indexInArray, value) {
							var pathFile = '';
							if (value.tabla == 'tbl_saldo_disashop') {
								pathFile = '/files_bucket/';
							} else {
								pathFile = '/files_bucket/cajas/';
							}
							ver_caja += ' <a title="' + value.filepath + '" target="_blank" href="' + pathFile + value.filepath + '">' +
								'<i class="fa fa-file"></i></a>';
						});

					}
					ver_caja += '</div>'
					var btn_return = ver_caja;
					return btn_return;
				}
			},
			{
				"data": "created_at",
				render: function (data, type, row) {
					var file_caja = "<button class='btn btn-xs btn-secondary'><label for='file-input" + row.id + "' ";
					file_caja += "<i class='fa fa-file'></i>";
					file_caja += "</label></button>";
					file_caja += "<input data-disashopId='" + row.id + "'  data-localId='" + row.local_id + "' id='file-input" + row.id + "' onchange='fncGetFileSisashop(this)' type='file' style='display:none;'/>";

					var ver_caja = '';
					if (row.caja_id != null || row.caja_id == '') {
						ver_caja = '<a title="Ver Caja" target="_blank" href="./?sec_id=caja&amp;item_id=' + row.caja_id + '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>';
					}

					var btn_return = file_caja + ver_caja;
					return btn_return;
				}
			}

		]
	});
	$('#tbl_saldo_history_Disashop tbody').off('click');

}
function fncGetFileSisashop(element) {

	var idDisashop = ($(element).attr("data-disashopid"));

	var localId = ($(element).attr("data-localId"));
	var formData = new FormData();
	formData.append('accion', 'saveFileDisashop');
	formData.append('id', idDisashop);
	formData.append('localId', localId);
	var filesDisashop = document.querySelector('[data-disashopId="' + idDisashop + '"]').files.length;
	if (filesDisashop > 0) {
		for (var x = 0; x < filesDisashop; x++) {
			formData.append("filesDisashop[]", document.querySelector('[data-disashopId="' + idDisashop + '"]').files[x]);
		}
		document.querySelector('[data-disashopId="' + idDisashop + '"]').value = "";
		$.ajax({
			type: "POST",
			data: formData,
			url: 'sys/get_saldo_disashop.php',
			contentType: false,
			processData: false,
			cache: false,
			success: function (response) {
				var jsonData = JSON.parse(response);
				if (!jsonData.error) {
					swal("Archivo Subido", jsonData.message, "success");
					var ver_caja = '';
					$.each(jsonData.data, function (indexInArray, value) {
						ver_caja += ' <a title="' + value.path + '" target="_blank" href="/files_bucket/' + value.path + '">' +
							'<i class="fa fa-file"></i></a>';
					});
					$("#td" + idDisashop).append(ver_caja);
				} else {
					swal("Algo paso durante la transacción", '', "error");
				}

			}
		});
	}

}
function fncGetaDataFiltros() {
	$.ajax({
		type: "POST",
		data: { accion: 'listar_zonas_tipos' },
		url: 'sys/get_saldo_disashop.php',
		cache: false,
		success: function (response) {
			var jsonData = JSON.parse(response);

			$.each(jsonData.data.types, function (i, item) {
				$('#cbDisashopTipo,#cbDisashopTipoHistoryLocalId').append($('<option>', {
					value: item.id,
					text: item.nombre
				}));
			});
			$.each(jsonData.data.zones, function (i, item) {
				$('#cbDisashopZona').append($('<option>', {
					value: item.id,
					text: item.nombre
				}));
			});

		}
	});
}

function sec_rep_disashop_hitorico_recarga_masiva() {
	$('#mdDisashopHistoricoRecargaMasivo').modal('show');

	var data = {
		accion : 'get_data_historica_recarga_masiva'
	}
	$.ajax({
		url: "/sys/get_saldo_disashop.php",
		type: "POST",
		data: data,
		success: function (data) {
			var respuesta = JSON.parse(data);
			if (respuesta.status == 200) {
				
				sec_rep_disashop_render_table_historico_carga_masiva(respuesta.result);
			}
		},
		always: function (data) {
			loading();
		}
	});
}

function sec_rep_disashop_render_table_historico_carga_masiva(data = []) {
	$("#tbl_historico_carga_masiva_disashop")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'desc']],
		pageLength: 25,
		columns: [
		  { data: "id", className: "text-center" },
		  { data: "usuario", className: "text-left" }, 
		  { data: "nombre", className: "text-left" },
		  { data: "created_at", className: "text-left" },
		  { data: "archivo", className: "text-center" },
		],
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
	
	  })
	  .DataTable();



	 
}
