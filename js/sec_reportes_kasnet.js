function sec_reportes_kasnet() {
	/*MAIN TABLE*/
	$("#cbLocalesKasnet").select2({ placeholder: "Elegir Local" });
	$("#cbKasnetHistoryLocalId").select2({ placeholder: "Elegir Local" });
	fncRenderTableKasnetMasiva();
	$('#dateRecarga').datetimepicker({
		format: 'YYYY-MM-DD HH:mm:ss',
		collapse: false,
		sideBySide: true,
		focusOnShow: false,
	});

	var today = new Date();
	var yesterday = new Date();
	yesterday.setDate(today.getDate() - 1);

	$('#start_date').datepicker({
		dateFormat: 'yy-mm-dd',
		maxDate: 'now',
		onSelect: function () {
			filter_kasnet_history_table(0);
		}
	}).datepicker('setDate', yesterday);

	$('#end_date').datepicker({
		dateFormat: 'yy-mm-dd',
		maxDate: 'now',
		onSelect: function () {
			filter_kasnet_history_table(0);
		}
	}).datepicker('setDate', today);

	$("#txtKasnetSearch").on('keyup', function (event) {
		filter_kasnet_table(0);
		/* Act on the event */
	});

	$("#cbKasnetZona").on('change', function (event) {
		filter_kasnet_table(0);
	});

	$("#cbKasnetTipo").on('change', function (event) {
		filter_kasnet_table(0);
	});

	$("#cbKasnetLimit").on('change', function (event) {
		filter_kasnet_table(0);
	});

	$("#btnKasnetSearch").on('click', function (event) {
		event.preventDefault();
		filter_kasnet_table(0);
	});

	$('#btnKasnetClean').on('click', function (event) {
		event.preventDefault();
		$('#cbKasnetZona').prop('selectedIndex', 0);
		$('#cbKasnetTipo').prop('selectedIndex', 0);
		$('#cbKasnetLimit').prop('selectedIndex', 0);
		$('#txtKasnetSearch').val("");
		filter_kasnet_table(0);
	});

	$('#btnKasnetHistoryClean').on('click', function (event) {
		event.preventDefault();
		$('#cbKasnetHistoryLocalId').prop("selectedIndex", 0);
		$('#cbKasnetHistoryTipo').prop("selectedIndex", 0);
		$('#cbKasnetHistoryLimit').prop("selectedIndex", 0);
		$('#start_date').val('');
		$('#end_date').val('');
		filter_kasnet_history_table(0);
	});

	$("#btnExportKasnet").on("click", function (event) {
		event.preventDefault();
		loading(true);
		var get_data = {};
		get_data.zona = $("#cbKasnetZona option:selected").val();
		get_data.tipo = $("#cbKasnetTipo option:selected").val();
		get_data.filter = $("#txtKasnetSearch").val();
		auditoria_send({ "proceso": "export_reporte_kasnet", "data": get_data });
		$.post('/export/reporte_kasnet.php', { "export_reporte_kasnet": get_data }, function (response) {
			console.log(response);
			try { var obj = JSON.parse(response); }
			catch (err) {
				swal("Error!", "Error al tratar de eliminar el archivo. " + err, "warning");
			}
			window.open(obj.path);
			loading();
		});
	});

	$("#btnKasnetHistoryExport").on("click", function (event) {
		event.preventDefault();
		loading(true);
		var get_data = {};
		get_data.local_id = $('#cbKasnetHistoryLocalId option:selected').val();
		get_data.start_date = $('#start_date').val();
		get_data.end_date = $('#end_date').val();
		get_data.tipo = $("#cbKasnetHistoryTipo option:selected").val();
		auditoria_send({ "proceso": "export_reporte_history_kasnet", "data": get_data });
		$.post('/export/reporte_kasnet.php', { "export_reporte_history_kasnet": get_data }, function (response) {
			console.log(response);
			try { var obj = JSON.parse(response); }
			catch (err) {
				swal("Error!", "Error al tratar de eliminar el archivo. " + err, "warning");
			}
			window.open(obj.path);
			loading();
		});
	});

	filter_kasnet_table(0);
	/*END MAIN TABLE*/

	/*INCREMENT SALDO*/
	$('#btnRecargaNew').on('click', function (event) {
		event.preventDefault();
		loading(true);
		resetKasnetModal();
		$('#cbLocalesKasnet').show();
		$('.select2').show();
		$('#titleKasnetRecarga').html("Recargar Locales");
		$("#mdKasnetRecarga").modal("show");
		loading();
	});

	$(document).on('click', '#btnIncrementSaldo', function (event) {
		event.preventDefault();
		loading(true);
		resetKasnetModal();
		$('#cbLocalesKasnet').hide();
		$('.select2').hide();
		$('#txtKasnetLocalId').val($(this).closest('tr').find('.local_id').html());
		$('#titleKasnetRecarga').html('[' + $(this).closest('tr').find('.txt_cc_id').html() + '] ' + $(this).closest('tr').find('.txt_local').html());
		$("#mdKasnetRecarga").modal("show");
		loading();
	});

	$(document).on('click', '#btnRecargaNewMasica', function (event) {
		event.preventDefault();
		loading(true);
		// resetKasnetModal();
		// $('#cbLocalesKasnet').hide();
		// $('.select2').hide();
		// $('#txtKasnetLocalId').val($(this).closest('tr').find('.local_id').html());
		// $('#titleKasnetRecarga').html('['+$(this).closest('tr').find('.txt_cc_id').html()+'] '+$(this).closest('tr').find('.txt_local').html());
		$("#mdKasnetRecargaMasico").modal("show");
		loading();
	});

	$(document).on('click', '#guardar_recargar_masiva_kasnet', function (event) {
		event.preventDefault();
		save_data_kasnet_masivo();
	});

	$('#cbLocalesKasnet').on('change', function (event) {
		event.preventDefault();
		$('#txtKasnetLocalId').val($(this).val());
	});



	$("#formKasnetRecarga").submit(function (e) {
		e.preventDefault();
		if ($("#txtKasnetRecarga").val() != 0) {
			var data = {};
			data.increment = $('#txtKasnetRecarga').val();
			var form_data = (new FormData(this));
			form_data.append("set_increment", 1);
			loading(true);
			auditoria_send({ "proceso": "set_increment_kasnet", "data": data });
			$.ajax({
				url: "/sys/get_saldo_kasnet.php",
				type: "POST",
				data: form_data,
				cache: false,
				contentType: false,
				processData: false,
				success: function (data) {
					filter_kasnet_table($('#pagination_kasnet').pagination('getCurrentPage') - 1);
					loading();
					swal({
						title: "Saldo Actualizado",
						text: "",
						type: "success",
						timer: 1000,
						closeOnConfirm: true
					},
						function () {
							$("#mdKasnetRecarga").modal("hide");
						});
				}
			});
		}
		else swal("Error!", "Ingresar saldo diferente de 0 (zero).", "warning");
	});

	if ($('#tbl_saldo_kasnet').length) setKasnetUploader($('#fileKasnetRecarga'));
	/*END INCREMENT SALDO*/

	/*HISTORY*/
	$('#cbKasnetHistoryLocalId').on('change', function (event) {
		event.preventDefault();
		filter_kasnet_history_table(0);
	});

	$("#cbKasnetHistoryTipo").on('change', function (event) {
		filter_kasnet_history_table(0);
	});

	$("#cbKasnetHistoryLimit").on('change', function (event) {
		filter_kasnet_history_table(0);
	});

	$('#btnShowKasnetFullHistory').on('click', function (event) {
		event.preventDefault();
		loading(true);
		$('#txtKasnetHistoryTitle').html('Histórico de Saldos');
		$('#cbKasnetHistoryLocalId').prop('selectedIndex', 0);
		$("#cbKasnetHistoryTipo").prop('selectedIndex', 0);
		$("#cbKasnetHistoryLimit").prop('selectedIndex', 0);
		$("#mdKasnetHistory").modal("show");
		filter_kasnet_history_table(0);
		loading();
	});

	$(document).on('click', '#btnShowKasnetHistory', function (event) {
		event.preventDefault();
		$('#txtKasnetHistoryTitle').html('[' + $(this).closest('tr').find('.txt_cc_id').html() + '] ' + $(this).closest('tr').find('.txt_local').html());
		$('#cbKasnetHistoryLocalId').val($(this).closest('tr').find('.local_id').html());
		$("#cbKasnetHistoryTipo").prop('selectedIndex', 0);
		$("#cbKasnetHistoryLimit").prop('selectedIndex', 0);
		$('#start_date').val('');
		$('#end_date').val('');
		$("#mdKasnetHistory").modal("show");
		filter_kasnet_history_table(0);
	});

	$(document).on('change', '#fileKasnetArchivo', function (event) {
		$(this).closest('tr').find("#formKasnetArchivo").submit();
	});

	$(document).on('submit', "#formKasnetArchivo", function (e) {
		e.preventDefault();
		var form_data = (new FormData(this));
		form_data.append("set_file_upload", 1);
		form_data.append("id", $(this).closest('tr').find(".kasnet_id").html());
		form_data.append("local_id", $(this).closest('tr').find(".local_id").html());
		loading(true);
		$.ajax({
			url: "/sys/get_saldo_kasnet.php",
			type: "POST",
			data: form_data,
			cache: false,
			contentType: false,
			processData: false,
			success: function (data) {
				filter_kasnet_history_table($('#pagination_history_kasnet').pagination('getCurrentPage') - 1);
				loading();
				swal("Archivo Agregado", "", "success");
			},
			always: function (data) {
				loading();
				console.log(data);
			}
		});
	});

	$(document).on('click', '#btnKasnetArchivo', function (event) {
		event.preventDefault();
		$(this).closest('tr').find('#fileKasnetArchivo').click();
	});
	/*END HISTORY*/
}

/*FUNCTIONS*/

function fncGetFileSecRecargaKasnetMasivo() {
	// debugger
	var formData = new FormData();
	formData.append('get_data_recarga_by_file', 'true');
	//var filesDisashop = document.querySelector('[data-disashopId="' + idDisashop + '"]').files.length;
	var files = document.getElementById('file-input-kasnet-masivo').files.length;
	if (files > 0) {
		// const file_backup = document.getElementById('file-input-kasnet-masivo').files;
		var file = document.getElementById('file-input-kasnet-masivo').files[0];
		formData.append("file", file);
		
		var nuevoFileList = new DataTransfer();
		nuevoFileList.items.add(new File([file], file.name, { type: file.type }));
		// Asignar el nuevo FileList al segundo input
		document.getElementById('file-input-kasnet-masivo-backup').files = nuevoFileList.files;
		
		//limpiamos el input original
		document.getElementById('file-input-kasnet-masivo').value = "";
		$.ajax({
			type: "POST",
			data: formData,
			url: 'sys/get_saldo_kasnet.php',
			contentType: false,
			processData: false,
			cache: false,
			success: function (response) {
				var jsonData = JSON.parse(response);
				if (jsonData.error == true) {
					swal("Error", jsonData.message, "error");
					loading();
					
				}else{
					fncRenderTableKasnetMasiva(jsonData.data);
					
				} 
				

			}, beforeSend: function () {
				//loading(true);
			}
		});
	}

}

function fncRenderTableKasnetMasiva(data = {}) {
	var table = $('#tbl_sec_saldo_kasnet_masivo').DataTable();
	table.clear();
	table.destroy();
	//loading(true);
	var table = $('#tbl_sec_saldo_kasnet_masivo').DataTable({
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
	$('#tbl_sec_saldo_kasnet_masivo tbody').off('click');


}

function save_data_kasnet_masivo() {
	myTable = $('#tbl_sec_saldo_kasnet_masivo').DataTable();
	var form_data = myTable.rows().data();
	let new_data = new Array();
	$.each(form_data, function (key, value) {
		if (value.error_cc == false && value.error_proveedor_id == false) {
			new_data.push(value);
		}
	});

	swal({
		title: "¿Estás seguro?",
		text: 'Se insertaran datos para KASNET',
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
				'save_data_recarga_by_file_kasnet': 'save_data_recarga_by_file_kasnet',
				'data_save': JSON.stringify(new_data),
				'date': $("#dtKasnetHistoryRecargaMasivaDesde").val(),
				'time': $("#dtKasnetHistoryRecargaMasivaDesdeTime").val(),
			}
			var formData = new FormData();
			formData.append('save_data_recarga_by_file_kasnet', 'save_data_recarga_by_file_kasnet');
			var file = document.getElementById('file-input-kasnet-masivo-backup').files.length;
			if (file > 0) {
				formData.append("file", document.getElementById('file-input-kasnet-masivo-backup').files[0]);
			}
			formData.append("data_save", JSON.stringify(new_data));
			formData.append("date", $("#dtKasnetHistoryRecargaMasivaDesde").val());
			formData.append("time", $("#dtKasnetHistoryRecargaMasivaDesdeTime").val());
			if (isConfirm) {
				$.ajax({
					type: "POST",
					data: formData,
					url: 'sys/get_saldo_kasnet.php',
					contentType: false,
					processData: false,
					cache: false,
					success: function (response) {

						var jsonData = JSON.parse(response);
						if (jsonData.error == false) {
							swal("OK", jsonData.message, "success");
							loading();
							fncRenderTableKasnetMasiva();
							$("#dtKasnetHistoryRecargaMasivaDesdeTime").val('');
							$("#file-input-kasnet-masivo-backup").val('');
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

function setKasnetUploader(object) {
	$(document).on('click', '.browse-btn', function (event) {
		event.preventDefault();
		object.click();
	});

	object.on('change', function (event) {
		const name = $(this).val().split(/\\|\//).pop();
		const truncated = name.length > 1720 ? name.substr(name.length - 17) + '...' : name;

		$(".file-info").html(truncated);
	});
}

function filter_kasnet_table(page) {

	var get_data = {};
	var limit = $("#cbKasnetLimit option:selected").val();
	get_data.start_date = $('#start_date').val();
	get_data.end_date = $('#end_date').val();
	get_data.page = page;
	get_data.limit = limit;
	get_data.zona = $("#cbKasnetZona option:selected").val();
	get_data.tipo = $("#cbKasnetTipo option:selected").val();
	get_data.filter = $("#txtKasnetSearch").val();

	auditoria_send({ "proceso": "get_tabla_saldo_kasnet", "data": get_data });
	$.post('/sys/get_saldo_kasnet.php', { "get_tabla_saldo_kasnet": get_data }, function (response) {
		try { result = JSON.parse(response); }
		catch (err) {
			swal("Error!", "Error al filtrar los saldos " + err, "warning");
		}
		$("#tbl_saldo_kasnet").html(result.body);

		$("#pagination_kasnet").pagination({
			items: result.num_rows,
			currentPage: page + 1,
			itemsOnPage: limit,
			cssStyle: 'light-theme',
			onPageClick: function (pageNumber, event) {
				event.preventDefault();
				filter_kasnet_table(pageNumber - 1);
			}
		});
	});
}

function filter_kasnet_history_table(page) {
	console.log("filter_kasnet_history_table");
	loading(true);

	var get_data = {};
	var limit = $("#cbKasnetHistoryLimit option:selected").val();
	get_data.start_date = $('#start_date').val();
	get_data.end_date = $('#end_date').val();
	get_data.page = page;
	get_data.limit = limit;
	get_data.local_id = $("#cbKasnetHistoryLocalId option:selected").val();
	get_data.tipo = $("#cbKasnetHistoryTipo option:selected").val();

	if (get_data.local_id !== undefined) {
		auditoria_send({ "proceso": "get_tabla_saldo_kasnet_history", "data": get_data });
		$("#tbl_saldo_history_kasnet").html("");
		$.post('/sys/get_saldo_kasnet.php', { "get_tabla_saldo_kasnet_history": get_data }, function (response) {
			try {
				result = JSON.parse(response);
			}
			catch (err) {
				swal("Error!", "Error al tratar de eliminar el archivo. " + err, "warning");
			}
			$("#tbl_saldo_history_kasnet").html(result.body);

			$("#pagination_history_kasnet").pagination({
				items: result.num_rows,
				currentPage: page + 1,
				itemsOnPage: limit,
				cssStyle: 'light-theme',
				onPageClick: function (pageNumber, event) {
					event.preventDefault();
					filter_kasnet_history_table(pageNumber - 1);
				}
			});
			loading();
		});
	} else {
		console.log(get_data);
	}
}

function resetKasnetModal() {
	$('#cbLocalesKasnet').prop('selectedIndex', 0);
	$('.select2').val([]).trigger('change');
	$('#txtKasnetLocalId').val("");
	$('.file-info').html("Ningun Archivo Seleccionado...");
	$('#file_reset').click();
	$('#employee_id').select2('val', '');
	$('#txtKasnetRecarga').val("");
}

function sec_rep_kasnet_hitorico_recarga_masiva() {
	$('#mdKasnetHistoricoRecargaMasivo').modal('show');

	
	var data = {
		get_data_historica_recarga_masiva : 'get_data_historica_recarga_masiva'
	}
	$.ajax({
		url: "/sys/get_saldo_kasnet.php",
		type: "POST",
		data: data,
		success: function (data) {
			var respuesta = JSON.parse(data);
			console.log(respuesta)
			if (respuesta.status == 200) {
				
				sec_rep_kasnet_render_table_historico_carga_masiva(respuesta.result);
			}
		},
		always: function (data) {
			loading();
		}
	});
}

function sec_rep_kasnet_render_table_historico_carga_masiva(data = []) {
	console.log(data)
	$("#tbl_historico_carga_masiva_kasnet")
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

	  fn_botones();

	 
}

/*END FUNCTIONS*/
function fn_botones(){

	$(".btn_change_estado_recarga_masiva").click(function(e){
		var id = e.currentTarget.dataset.idRecarga
		var state = e.currentTarget.dataset.stateRecarga
		var new_state = 0;
		var msg_confirm = '';
		var btn_msg_confirm = '';
		switch (state) {
			case "0":
				new_state = 1;
				msg_confirm = 'Se activará esta recarga masiva'
				btn_msg_confirm = 'Sí, activar';
				break;
			case "1":
				new_state = 0;
				msg_confirm = 'Se desactivará esta recarga masiva'
				btn_msg_confirm = 'Sí, desactivar';
				break;
			default:
				new_state = 0;
				break;
		}
		console.log(id);

		swal({
			title: "¿Estás seguro?",
			text: msg_confirm,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: btn_msg_confirm,
			cancelButtonText: "No, cancelar",
			closeOnConfirm: false,
			closeOnCancel: false,

		},
			function (isConfirm) {
				// var data = {
				// 	'save_data_recarga_by_file_kasnet': 'save_data_recarga_by_file_kasnet',
				// 	'data_save': JSON.stringify(new_data),
				// }
				if (isConfirm) {
					console.log('confirmooo')

					changeEstadoRecargaMasiva(id, new_state);

				} else {
					console.log('nooooo')
				}
			});
	})

}

function changeEstadoRecargaMasiva(id, new_state){
	var data = {
		set_state_recarga_masiva : 'set_state_recarga_masiva',
		recarga_masiva_id : id,
		new_state : new_state,
	}
	
	$.ajax({
		url: "/sys/get_saldo_kasnet.php",
		type: "POST",
		data: data,
		success: function (data) {
			var respuesta = JSON.parse(data);
			console.log(respuesta)
			if (respuesta.status == 200) {
				swal("OK", respuesta.message, "success");
				setTimeout(() => {
					window.location.reload();
				}, 2000);

			} else if (respuesta.status == 500) {
				swal("Error", respuesta.message, "warning");
			}
		},
		always: function (data) {
			loading();
		}
	});
}