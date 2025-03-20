
function sec_soporte_retiros(){
	loading(true);
	set_soporte_retiros_local_storage_data();
	filter_soporte_retiros_table(0);
	set_import_interval(true);

	$('#btnSoporteRetirosImport').on('click', function(event) {
		event.preventDefault();

		swal({
			title: "Importar Solicitudes de Retiro",
			text: "El sistema irá bajar todas las solicitudes de retiro con sus clientes y transacciones respectivos. Tan pronto termine será mostrado un mensaje de confirmación.",
			type: "warning",
			confirmButtonText: "Ok Entendi"
		},
		function(){
			swal.close();

			set_import_interval();
			$('#btnSoporteRetirosImport').prop('disabled', true);
			$('#btnSoporteRetirosImport').html('<i class="fa fa-refresh fa-spin d_loading"></i> IMPORTANDO');

			var get_data = {};
			get_data.import = "";

			auditoria_send({"proceso":"soporte_retiros_import_requests","data":get_data});
			$.post('/sys/get_soporte_retiros.php', {"import_requests": get_data}, function(response){
				filter_soporte_retiros_table(0);

				swal("Listo!", "Solicitudes de retiros, clientes y transacciones importados exitosamente", "success");
			});
		});
	});

	$('#btnSoporteDatabaseStatus').on('click', function(event){
		event.preventDefault();

		var data = {};
		data.db_status = "";

		let status_icon = $("#db_status_icon")
		status_icon.removeClass()
		status_icon.addClass("fa fa-refresh fa-spin")

		$.post('/sys/get_soporte_retiros.php', {"check_database_status": data}, function(r){
			response = JSON.parse(r);
			$('#divDatabaseStatus').html(response.message);
			$('#mdDatabaseStatus').modal('show');

			status_icon.removeClass()
			status_icon.addClass("fa fa-database")
		});
	});

	$(document).on('change', '#fileRetiroDNI', function(event) {
		$('#formRetiroDNI').submit();
	});

	$(document).on('submit', "#formRetiroDNI", function(e) {
		e.preventDefault();

		var form_data = (new FormData(this));
		form_data.append("set_file_upload", 1);
		loading(true);
		$.ajax({
			url: "/sys/get_soporte_retiros.php",
			type: "POST",
			data: form_data,
			cache: false,
			contentType: false,
			processData:false,
			success: function() {
				var data = {};
				data.dni = $('#txtRetiroDNI').val();
				show_attachments(data);

				$('.file-info').html("Seleccione el archivo para anexarlo al DNI");
				$('#file_reset').click();
			},
			always: function(data){
				loading();
				//console.log(data);
			}
		});
	});

	$('#txtSoporteRetirosSearch').on('keyup', debounce(function(event) {
		filter_soporte_retiros_table(0);
	}, 600));

	$('#txtSoporteRetirosFromDate').on('blur', function(event) {
		loading(true);
		filter_soporte_retiros_table(0);
	});

	$('#txtSoporteRetirosToDate').on('blur', function(event) {
		loading(true);
		filter_soporte_retiros_table(0);
	});

	$('#cbSoporteRetirosLimit').on('change', function(event) {
		event.preventDefault();
		loading(true);
		filter_soporte_retiros_table(0);
	});

	$('#cbSoporteRetirosEstado').on('change', function(event) {
		event.preventDefault();
		loading(true);
		filter_soporte_retiros_table(0);
	});

	$('#btnSoporteRetiroApplyCuotas').on('click', function(event) {
		event.preventDefault();

		set_cuotas_approval(1);
	});

	$('#btnSoporteRetiroDenyCuotas').on('click', function(event) {
		event.preventDefault();

		set_cuotas_approval(0);
	});

	setClientRequestDNIUploader($('#fileRetiroDNI'));

	$('#clear_filters_btn').on('click', function (event){
		clear_local_storage_data();
		clear_form_inputs();
	});

	$("#btn_columnas_soporte_retiros").off("click").on("click",function(){
		if($("#tblSoporteRetirosAPI tbody tr").length==0){
			swal("No hay datos!", "", "error");
			return false;
		}
		$("#filter_columnas_modal").modal("show");
	})

	$('#btn_soporte_retiros_search').on('click', function (event){
		set_local_storage_data();
		get_retiros();
	});

	$(document).on('change', '.dt-checkboxes', function (){
		$(this).parents("tr").toggleClass('selected-row');
	})

	$(document).on('change', 'th.dt-checkboxes-select-all.sorting_disabled > input', function(){
		if($("th.dt-checkboxes-select-all.sorting_disabled > input").is(':checked')){
			$("#tblSoporteRetirosAPI").children().eq(1).children().addClass("selected-row")
		}else{
			$("#tblSoporteRetirosAPI").children().eq(1).children().removeClass("selected-row")
		}
	})

	$('#boActionProcessText').on('change', function (e){
		$(this).scrollTop($(this).prop('scrollHeight'))
	});
}

function set_cuotas_approval(status){
	var data = {}
	data.request_id = $('#txtSoporteRetiroRequestID').val();
	data.status = status;

	loading(true);
	auditoria_send({"proceso":"soporte_retiros_set_cuotas_approval","data":data});
	$.post('/sys/get_soporte_retiros.php', {"set_cuotas_approval" : data}, function(r){
		filter_soporte_retiros_table(0);

		$('#mdSoporteRetiroValuesModal').modal("hide");
	});

}

function set_import_interval(pulse=false){
	var interval = window.setInterval(function(){
		var data = {};
		data.import_status = "";
		$.post('/sys/get_soporte_retiros.php', {"check_import_status": data}, function(r){
			response = JSON.parse(r);

			if(response.message.status == 0 && $('#btnSoporteRetirosImport').prop('disabled') == false){
				$('#btnSoporteRetirosImport').prop('disabled', true);
				$('#btnSoporteRetirosImport').html('<i class="fa fa-refresh fa-spin d_loading"></i> IMPORTANDO');
			}
			else if(response.message.status == 1){
				if($('#btnSoporteRetirosImport').prop('disabled') == true){
					$('#txtSoporteRetirosImportLastUpdate').text(response.message.updated_at);
					$('#txtSoporteRetirosImportLastTransaction').text(response.message.created_at);

					$('#btnSoporteRetirosImport').prop('disabled', false);
					$('#btnSoporteRetirosImport').html('<i class="fa fa-download"></i> IMPORTAR RETIROS');
					swal("Listo!", "Solicitudes de retiros, clientes y transacciones importados exitosamente", "success");
				}
				clearInterval(interval);
			}
		});
	}, 10000);

	if(pulse){
		var data = {};
		data.import_status = "";
		$.post('/sys/get_soporte_retiros.php', {"check_import_status": data}, function(r){
			response = JSON.parse(r);

			$('#txtSoporteRetirosImportLastUpdate').text(response.message.updated_at);
			$('#txtSoporteRetirosImportLastTransaction').text(response.message.created_at);

			if(response.message.status == 0 && $('#btnSoporteRetirosImport').prop('disabled') == false){
				$('#btnSoporteRetirosImport').prop('disabled', true);
				$('#btnSoporteRetirosImport').html('<i class="fa fa-refresh fa-spin d_loading"></i> IMPORTANDO');
			}
			else if(response.message.status == 1){
				clearInterval(interval);
				if($('#btnSoporteRetirosImport').prop('disabled') == true){
					swal("Listo!", "Solicitudes de retiros, clientes y transacciones importados exitosamente", "success");
					$('#btnSoporteRetirosImport').prop('disabled', false);
					$('#btnSoporteRetirosImport').html('<i class="fa fa-download"></i> IMPORTAR RETIROS');
				}
			}
		});
	}
}

function setClientRequestDNIUploader(object){
	$(document).on('click', '.browse-btn', function(event) {
		event.preventDefault();
		object.click();
	});

	object.on('change', function(event) {
		const name = $(this).val().split(/\\|\//).pop();
		const truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;

		$(".file-info").html(truncated);
	});
}

function filter_soporte_retiros_table(page) {
	var get_data 	= {};
	var limit 		= $("#cbSoporteRetirosLimit option:selected").val();
	get_data.state 	= $("#cbSoporteRetirosEstado option:selected").val();
	get_data.page 	= page;
	get_data.limit 	= limit;
	get_data.filter = $("#txtSoporteRetirosSearch").val();
	get_data.from_date = $('#txtSoporteRetirosFromDate').val();
	get_data.to_date = $('#txtSoporteRetirosToDate').val();

	localStorage.setItem('soporte_retiros_search', $("#txtSoporteRetirosSearch").val());
	localStorage.setItem('soporte_retiros_state', $("#cbSoporteRetirosEstado option:selected").val());
	localStorage.setItem('soporte_retiros_from_date', $("#txtSoporteRetirosFromDate").val());
	localStorage.setItem('soporte_retiros_to_date', $("#txtSoporteRetirosToDate").val());

	auditoria_send({"proceso":"get_tabla_saldo_SoporteRetiros","data":get_data});
	$.post('/sys/get_soporte_retiros.php', {"get_soporte_retiros": get_data}, function(response) {

		try{result = JSON.parse(response);}
		catch(err){
			swal("Error!", "Error al filtrar los retiros "+err, "warning");
		}
		$("#tblSoporteRetiros").html(result.body);
		$("#tblSoporteRetiros").fixMe({"columns": 0, "footer": false, "marginTop":50, "zIndex": 999, "bgColor": "white", "bgHeaderColor": "white"});

		$("#pagination_soporte_retiros").pagination({
			items: result.num_rows,
			currentPage: page+1,
			itemsOnPage: limit,
			cssStyle: 'light-theme',
			onPageClick: function(pageNumber, event){
				event.preventDefault();
				filter_soporte_retiros_table(pageNumber-1);
			}
		});

		start_soporte_retiro_dni_modal();
		start_soporte_retiro_values_modal();
		start_soporte_retiro_final_action_modal();
		start_soporte_retiro_bonus_modal();
		start_refresh_date_fields();
		start_refresh_request();
		start_final_state_button();
		loading();
	});
}

function start_final_state_button(){
	$('[id*=btnRetiroEstado]').off().on('click', function(event) {
		event.preventDefault();

		var data = {}
		data.request_id = $(this).data("id");

		loading(true);
		auditoria_send({"proceso":"soporte_retiros_set_final_status","data":data});
		$.post('/sys/get_soporte_retiros.php', {"set_final_status": data}, function(r){
			console.log(r);
			filter_soporte_retiros_table(0);

		});
	});

	$(document).on('click', '[id*=btnClientStatistics]', function(event) {
		event.preventDefault()

		var data = {};
		data.id = $(this).data("client-id");

		loading(true);
		auditoria_send({"proceso":"soporte_retiros_get_player_statistics","data":data});
		$.post('/sys/get_soporte_retiros.php', {"get_player_statistics" : data}, function(r){
			response = JSON.parse(r);

			$('#divClientStatistics').html(response.message)
			$('#mdClientStatistics').modal("show");
			loading();
		});
	});

	$('[id*=btnClientStatistics]').off().on('click', function(event){
		event.preventDefault()

		var data = {};
		data.id = $(this).data("client-id");
		
		loading(true);
		auditoria_send({"proceso":"soporte_retiros_get_player_statistics","data":data});
		$.post('/sys/get_soporte_retiros.php', {"get_player_statistics" : data}, function(r){
			response = JSON.parse(r);
			
			$('#divClientStatistics').html(response.message)
			$('#mdClientStatistics').modal("show");
			loading();
		});
	});

	$('[id*=btnRetiroEstadoDeny]').off().on('click', function(event) {
		event.preventDefault();

		var data = {}
		data.request_id = $(this).data("id");
		data.state = 'deny';

		loading(true);
		auditoria_send({"proceso":"soporte_retiros_set_final_status","data":data});
		$.post('/sys/get_soporte_retiros.php', {"set_final_status": data}, function(r){
			console.log(r);
			filter_soporte_retiros_table(0);

		});
	});
}

function start_refresh_date_fields(){
	$('#start_date_filter').datetimepicker({
		format:'DD-MM-YY'
	});

	$('#end_date_filter').datetimepicker({
		format:'DD-MM-YY'
	});

	$('#start_time_filter').datetimepicker({
		format:'HH:mm:ss'
	});

	$('#end_time_filter').datetimepicker({
		format:'HH:mm:ss'
	});

	$('#txtSoporteRetirosFromDate').datetimepicker({
		format:'Y-MM-DD HH:mm:ss'
	});

	$('#txtSoporteRetirosToDate').datetimepicker({
		format:'Y-MM-DD HH:mm:ss'
	});

	$('#state_filter').select2();
}

function start_refresh_request(){
	$('[id*=btnRetiroRefresh]').off().on('click', function(event) {
		event.preventDefault();

		var data = {};
		data.request_id = $(this).data("id");

		loading(true);
		auditoria_send({"proceso":"soporte_retiros_refresh_request","data":data});
		$.post('sys/get_soporte_retiros.php', {'refresh_request': data}, function(){
			filter_soporte_retiros_table(0);
			loading();
		});
	});
}

function start_soporte_retiro_dni_modal(){
	$('[id*=btnRetiroDNIModal]').on('click', function(event) {
		event.preventDefault();
		loading(true);

		$('#txtRetiroDNI').val($(this).data('dni'));
		$('#txtRetiroDNIClientId').val($(this).data('client-id'));

		var data = {};
		data.dni = $(this).data("dni");

		show_attachments(data);

		auditoria_send({"proceso":"soporte_retiros_show_dni","data":data});
		$.post('sys/get_soporte_retiros.php', {"show_dni":data}, function(r) {
			response = JSON.parse(r);

			if(response.code == "200"){
				$('#dniContent').html(response.message);
				filter_soporte_retiros_table(0);
				$('#mdRetiroDNIModal').modal("show");
			}
			else{
				swal("Alerta", response.message, "warning");
			}

			loading();
		});
	});
}

function show_attachments(data){
	loading(true);
	auditoria_send({"proceso":"soporte_retiros_show_attachments","data":data});
	$.post('sys/get_soporte_retiros.php', {"show_attachments":data}, function(r) {
		response = JSON.parse(r);
		if(response.code == "200"){
			$('#dniAttachments').html(response.message);
		}
		else{
			swal("Alerta", response.message, "warning");
		}
		filter_soporte_retiros_table(0);
		loading();
	});
}

function start_soporte_retiro_bonus_modal() {
	const BonusTypesEnum = {
		2: 'WageringBonus',
		5: 'FreeSpin',
		6: 'FreeBet',
		8: 'SkillGamesBonus',
	}

	const BonusAcceptanceTypeEnum = {
		0: 'None',
		1: 'Accepted',
		2: 'Activated',
		3: 'Rejected',
		4: 'Expired'
	}

	const BonusResultTypeEnum = {
		0: 'None',
		1: 'Paid',
		2: 'Lost',
		3: 'Canceled',
		4: 'Expired',
		5: 'Completed',
		6: 'Converted'
	}

	const ProductTypes = {
		1: 'Casino',
		2: 'Sportsbook'
	}

	$(document).on('click', '.bonus_player_button', function(){
		loading(true)

		let data = {};
		data.client_id = $(this).data("client_id");

		auditoria_send({"proceso": "soporte_retiros_retiro_get_player_bonus_data","data": data});

		$.post(
			'sys/get_soporte_retiros.php',
			{
				get_player_bonus_data: data
			},
			function(r){
				loading(false)
				let response = JSON.parse(r);
				if (response != null){
					$('#bonus_modal').modal("show");
					let table = $("#tbl_bonus_modal");

					if ($.fn.DataTable.isDataTable("#tbl_bonus_modal")) {
						table.DataTable().clear().destroy();
					}
					if (response.data.length > 0){
						table.dataTable({
							data : response.data,
							scrollY: "300px",
							scrollX: true,
							scrollCollapse: true,
							columns: [
								{
									title: "Id",
									data: "Id",
								},
								{
									title: "Bono",
									data: "Name",
								},
								{
									title: "Fecha de creación",
									data: "CreatedLocal",
								},
								{
									title: "Tipo",
									data: "BonusType",
									render: function ( data, type, row, meta ) {
										return BonusTypesEnum[data] ?? ""
									}
								},
								{
									title: "Estado",
									data: "AcceptanceType",
									render: function ( data, type, row, meta ) {
										return BonusAcceptanceTypeEnum[data] ?? ""
									}
								},
								{
									title: "Monto",
									data: "Amount",
								},
								{
									title: "Monto Ganado",
									data: "PaidAmount",
								},
								{
									title: "Cantidad Convertida",
									data: "PaymentDocumentAmount",
								},
								{
									title: "Resultado",
									data: "ResultType",
									render: function ( data, type, row, meta ) {
										return BonusResultTypeEnum[data] ?? ""
									}
								},
								{
									title: "Fecha Resultado",
									data: "ResultDateLocal",
								},
								{
									title: "Fecha de aceptación",
									data: "AcceptanceDateLocal",
								},
								{
									title: "Fecha de inicio",
									data: "StartDateLocal",
								},
								{
									title: "Fecha de expiración",
									data: "ClientBonusExpirationDateLocal",
								},
								{
									title: "Modificado Por",
									data: "ModifiedByUserName",
								},
								{
									title: "Última modificación",
									data: "ModifiedLocal",
								},
								{
									title: "Creado por",
									data: "CreatedByUserName",
								},
								{
									title: "Motivo de cancelación",
									data: "CancellationNote",
								},
								{
									title: "Producto",
									data: "Source",
									render: function ( data, type, row, meta ) {
										if (data === null) return ''
										return ProductTypes[data] ?? ""
									},
									defaultContent: ""
								},
								{
									title: "Campaña Id",
									data: "CampainId",
								}
							]
						});
						$("#message_bonus_modal").html("");
					}else{
						$("#message_bonus_modal").html("No hay registros");
					}
					//console.log(response.data);
				}
			}
		);
	})
}

function start_soporte_retiro_values_modal(){

	$(document).on('click', '.btn_retiro_cuota', function(){
		event.preventDefault();
		loading(true)

		var data = {};
		data.request_id = $(this).data("request_id");
		data.client_id = $(this).data("client_id");
		data.request_time = $(this).data("request_time");

		auditoria_send({"proceso": "soporte_retiros_retiro_values_modal_retiro_cuota","data": data});

		$.post(
			'sys/get_soporte_retiros.php',
			{
				get_values_quota: data
			},
			function(r){
				loading(false)
				let response = JSON.parse(r);
				if (response != null){
					$('#quota_modal').modal("show");
					let table = $("#tbl_quota_modal");

					if ($.fn.DataTable.isDataTable("#tbl_quota_modal")) {
						table.DataTable().clear().destroy();
					}
					if (response.data.length > 0){
						table.dataTable({
							data : response.data,
							createdRow: function (row, data, dataIndex){
								if (data.odds){
									if( data.odds <  1.2 && data.ganado >= 100){
										$(row).addClass('bg-danger');
									}
								}
							},
							columns: [
								{
									title: "Ticket Id",
									data: "ticket_id",
								},
								{
									title: "Apostado",
									data: "apostado",
								},
								{
									title: "Fecha de creación",
									data: "created",
								},
								{
									title: "Cuota",
									data: "odds",
								},
								{
									title: "Estado",
									data: "state",
								},
								{
									title: "Ganado",
									data: "ganado",
								}
							]
						});
						$("#message_quota_modal").html("");
					}else{
						$("#message_quota_modal").html("No hay registros");
					}
					//console.log(response.data);
				}
			}
		);
	});

	$(document).on('click', '.btn_depositado', function(){
		event.preventDefault();
		loading(true)

		var data = {};
		data.request_id = $(this).data("request_id");
		data.client_id = $(this).data("client_id");
		data.request_time = $(this).data("request_time");

		auditoria_send({"proceso": "soporte_retiros_retiro_values_modal_depositado","data": data});

		$.post(
			'sys/get_soporte_retiros.php',
			{
				get_values_deposits: data
			},
			function(r){
				loading(false)
				let response = JSON.parse(r);
				if (response != null){
					$('#deposits_modal').modal("show");
					let table = $("#tbl_deposits_modal");

					if ($.fn.DataTable.isDataTable("#tbl_deposits_modal")) {
						table.DataTable().clear().destroy();
					}
					if (response.data.length > 0){
						table.dataTable({
							data : response.data,
							columns: [
								{
									title: "Fecha de creación",
									data: "Created",
								},
								{
									title: "Depósito ID",
									data: "Id",
								},
								{
									title: "Monto",
									data: "Amount",
								},
								{
									title: "Método de Pago",
									data: "PaymentSystemName",
								},
								{
									title: "Nombre de Caja",
									defaultContent: "-",
									data: "CashDeskName",
								}
							],
							fnInitComplete: function(){
								$('#deposits_modal_total').html(this.api().columns(2).data().sum())
							}
						});
						$("#message_deposits_modal").html("");
					}else{
						$("#message_deposits_modal").html("No hay registros");
					}
					//console.log(response.data);
				}
			}
		);
	});

	$(document).on('click', '.btn_depositado_tv', function(){
		event.preventDefault();
		$('#deposits_tv_modal_total').html("0.00")
		loading(true)

		var data = {};
		data.client_id = $(this).data("client_id");
		data.from_date = $(this).data("from_date");

		auditoria_send({"proceso": "soporte_retiros_values_depositado_tv","data": data});

		$.post(
			'sys/get_soporte_retiros.php',
			{
				get_values_deposits_tv: data
			},
			function(r){
				loading(false)
				let response = JSON.parse(r);
				if (response != null){
					$('#deposits_tv_modal').modal("show");
					let table = $("#tbl_deposits_tv_modal");

					if ($.fn.DataTable.isDataTable("#tbl_deposits_tv_modal")) {
						table.DataTable().clear().destroy();
					}
					if (response.data.length > 0){
						table.dataTable({
							data : response.data,
							columns: [
								{
									title: "Fecha de creación",
									data: "transaction_date",
								},
								{
									title: "Depósito ID",
									data: "depositos_id",
								},
								{
									title: "Monto",
									data: "amount_2",
								},
								{
									title: "Bono Monto",
									data: "bono_monto",
								},
								{
									title: "Total Recarga",
									data: "total_recarga",
								},
								{
									title: "Nombre de Caja",
									defaultContent: "-",
									data: "cashdesk_name",
								}
							],
							fnInitComplete: function(){
								$('#deposits_tv_modal_total').html(this.api().columns(4).data().sum())
							}
						});
						$("#message_deposits_tv_modal").html("");
					}else{
						$("#message_deposits_tv_modal").html("No hay registros");
					}
					//console.log(response.data);
				}
			}
		);
	});


	$(document).on('click', '.btn_unique_account', function(){
		event.preventDefault();
		loading(true)

		let data = {
			client_id : $(this).data("client_id"),
			bank_account : $(this).data("bank_account"),
			bank_cci : $(this).data("bank_cci"),
		};
		auditoria_send({"proceso": "soporte_retiros_retiro_values_modal_unique_account","data": data});

		$.post(
			'sys/get_soporte_retiros.php',
			{
				get_duplicated_bank_accounts: data
			},
			function(r){
				loading(false)
				let response = JSON.parse(r);
				if (response != null){
					$('#duplicate_bank_modal').modal("show");
					let table = $("#tbl_duplicate_bank_modal");

					if ($.fn.DataTable.isDataTable("#tbl_duplicate_bank_modal")) {
						table.DataTable().clear().destroy();
					}
					if (response.data.length > 0){
						table.dataTable({
							data : response.data,
							columns: [
								{
									title: "ID Cliente",
									data: "col_Id",
								},
								{
									title: "Cliente",
									data: "col_Name",
								},
								{
									title: "Email",
									data: "col_Email",
								},
								{
									title: "Cuenta",
									data: "cuenta",
								},
								{
									title: "CCI",
									data: "cci",
								}
							]
						});
						$("#message_duplicate_bank_modal").html("");
					}else{
						$("#message_duplicate_bank_modal").html("No hay registros");
					}
					//console.log(response.data);
				}
			}
		);
	});

	$('[id*=btnRetiroCuotaModal]').off().on('click', function(event) {
		event.preventDefault();

		var data = {};
		data.request_id = $(this).data("request");
		data.monto = $(this).data("monto");
		data.apostado = $(this).data("apostado");
		data.type = "cuota";

		get_retiro_values_table_modal(data, "Cuotas de Apuestas");
	});

	$('[id*=btnRetiroBetsModal]').off().on('click', function(event){
		event.preventDefault();

		data={};
		data.request_id = $(this).data("request");
		data.monto = $(this).data("monto");
		data.apostado = $(this).data("apostado");
		data.type="bets";

		get_retiro_values_table_modal(data, "Apuestas");
	});

	$('[id*=btnRetiroPercentageModal]').off().on('click', function(event){
		event.preventDefault();

		data={};
		data.request_id = $(this).data("request");
		data.type="percentage";

		get_retiro_values_table_modal(data, "Porcentage", 0, 'desc');
	});

	$('[id*=btnRetiroGamingsModal]').off().on('click', function(event){
		event.preventDefault();

		data={};
		data.request_id = $(this).data("request");
		data.monto = $(this).data("monto");
		data.apostado = $(this).data("apostado");
		data.type="gamings";

		get_retiro_values_table_modal(data, "Apuestas de Casino");
	});

	$('[id*=btnRetiroDeposModal]').off().on('click', function(event){
		event.preventDefault();

		data={};
		data.request_id = $(this).data("request");
		data.value = $(this).data("monto");
		data.monto = $(this).data("monto");
		data.apostado = $(this).data("apostado");
		data.type="depos";

		get_retiro_values_table_modal(data, "Depósitos");
	});

	$('[id*=btnRetiroTransactionsModal]').off().on('click', function(event){
		event.preventDefault();

		data={};
		data.request_id = $(this).data("request");
		data.value = $(this).data("monto");
		data.monto = $(this).data("monto");
		data.apostado = $(this).data("apostado");
		data.type="transactions";

		get_retiro_values_table_modal(data, "Transacciones");
	});

	$('#boActionModalCloseButton').off().on('click', function(event) {
		m_reload()
	})
}

function get_retiro_values_table_modal(data, title, orderColumn=0, orderType='desc'){
	loading(true);

	$('#txtSoporteRetiroValuesTitle').html(title);
	$('#txtSoporteRetiroRequestID').val(data.request_id);

	auditoria_send({"proceso":"soporte_retiros_get_"+data.type,"data":data});
	$.post('sys/get_soporte_retiros.php', {"get_values_table":data}, function(r) {
		response = JSON.parse(r);

		if(response.code == "200"){
			$('#valuesContent').html(response.message);

			//console.log($('#tblSoporteRetirosValue').length);

			if($('#tblSoporteRetirosValue > tbody').length > 0){
				$('#tblSoporteRetirosValue').DataTable({
			        scrollY:        "400px",
			        scrollY:        "400px",
			        scrollCollapse: true,
					dom: 'TBlfrtip',
					lengthMenu: [10, 25, 50, 75, 100, 500],
					order: [[orderColumn, orderType]],
					buttons: [
						'copyHtml5',
						'excelHtml5',
						'csvHtml5'
					],
					language: {
						url: "/locales/Datatable/es.json"
					}
				});

				setTimeout(function(){
			        $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
			    }, 200);
			}

			$('#txtSoporteRetiroMonto').html(data.monto);
			$('#txtSoporteRetiroTotal').html(data.apostado);

			if(data.type == "bets"){
				$('#divRetiroValuesActions').show();
			}
			else{
				$('#divRetiroValuesActions').hide();
				$('#txtSoporteRetiroRequestID').val("");
			}
			
			$('#mdSoporteRetiroValuesModal').modal("show");
		}
		else{
			swal("Alerta", response.message, "warning");
		}
		loading();
	});
}

function start_soporte_retiro_final_action_modal(){
	$('[id*=btnSoporteRetirosFinalAction]').on('click', function(event) {
		$(this).closest('tr').find('td').addClass("alert-warning");
		$('#txtSoporteRetirosRejectReason').val("");
		$('#txtSoporteRetirosFinalMessage').val("");
		$('#txtSoporteRetirosRequestId').val($(this).data("request"));
		$('#mdSoporteRetirosFinalAction').modal();
	});

	$('#mdSoporteRetirosFinalAction').on('hidden.bs.modal', function () {
		$("#tblSoporteRetiros").find('td.alert-warning').removeClass("alert-warning");
	});



	$('#btnSoporteRetirosFinalAceptar').on('click', function(event) {
		event.preventDefault();

		swal({
			title: "Aceptar Solicitud de Retiro",
			text: "Estás seguro que deseas aceptar la solicitud de retiro al cliente?",
			type: "warning",
			showCancelButton: true,
			confirmButtonText: "Si",
			cancelButtonText:"No sé que estoy haciendo!!!"
		},
		function(){
			swal.close();

			var data = {};
			data.request_id = $('#txtSoporteRetirosRequestId').val();

			//console.log(data);

			$.post('sys/get_soporte_retiros.php', {"accept_request":data}, function(r) {
				response = JSON.parse(r);
				if(response.code == "200"){
					$('#cuotaContent').html(response.message);

					$('#txtSoporteRetiroMonto').html(monto);
					$('#txtSoporteRetiroTotal').html(apostado);

					$('#mdSoporteRetiroCuotaModal').modal("show");
				}
				else{
					swal("Alerta", response.message, "warning");
				}
				loading();
			});
		});

	});
	$('#btnSoporteRetirosFinalRechazar').on('click', function(event) {
		event.preventDefault();
		swal({
			title: "Rechazar Solicitud de Retiro",
			text: "Estás seguro que deseas rechazar la solicitud de retiro al cliente?",
			type: "warning",
			showCancelButton: true,
			confirmButtonText: "Si",
			cancelButtonText:"No sé que estoy haciendo!!!"
		},
		function(){
			swal.close();

			var data = {};
			data.request_id = $('#txtSoporteRetirosRequestId').val();
			data.client_notes = $('#txtSoporteRetirosRejectReason').val();
			data.reject_reason = $('#txtSoporteRetirosFinalMessage').val();

			//console.log(data);

			$.post('sys/get_soporte_retiros.php', {"decline_request":data}, function(r) {
				response = JSON.parse(r);
				if(response.code == "200"){
					$('#cuotaContent').html(response.message);

					$('#txtSoporteRetiroMonto').html(monto);
					$('#txtSoporteRetiroTotal').html(apostado);

					$('#mdSoporteRetiroCuotaModal').modal("show");
				}
				else{
					swal("Alerta", response.message, "warning");
				}
				loading();
			});
		});
	});
}

function debounce(callback, wait) {
	let timeout;
	return (...args) => {
		clearTimeout(timeout);
		timeout = setTimeout(function () { callback.apply(this, args) }, wait);
	};
}

function set_local_storage_data(){
	localStorage.setItem("soporte_retiros_start_date_filter", $("#start_date_filter").val());
	localStorage.setItem("soporte_retiros_start_time_filter", $("#start_time_filter").val());
	localStorage.setItem("soporte_retiros_end_date_filter", $("#end_date_filter").val());
	localStorage.setItem("soporte_retiros_end_time_filter", $("#end_time_filter").val());
	localStorage.setItem("soporte_retiros_state_filter_filter", $("#state_filter").val());
	localStorage.setItem("soporte_retiros_is_verified_filter", $("#is_verified_filter").val());
	localStorage.setItem("soporte_retiros_is_bonus_filter", $("#bonus_filter").val());
	localStorage.setItem("soporte_retiros_channel_filter", $("#channel_filter").val());
	localStorage.setItem("soporte_retiros_payment_type_filter", $("#payment_type_filter").val());
	localStorage.setItem("soporte_retiros_client_gladcon_at_filter", $("#client_gladcon_at_filter").val());
}

function clear_local_storage_data(){
	localStorage.removeItem("soporte_retiros_start_date_filter");
	localStorage.removeItem("soporte_retiros_start_time_filter");
	localStorage.removeItem("soporte_retiros_end_date_filter");
	localStorage.removeItem("soporte_retiros_end_time_filter");
	localStorage.removeItem("soporte_retiros_state_filter_filter");
	localStorage.removeItem("soporte_retiros_is_verified_filter");
	localStorage.removeItem("soporte_retiros_is_bonus_player_filter");
	localStorage.removeItem("soporte_retiros_channel_filter");
	localStorage.removeItem("soporte_retiros_payment_type_filter");
	localStorage.removeItem("soporte_retiros_client_gladcon_at_filter");
}

function set_soporte_retiros_local_storage_data(){
	if(localStorage.getItem('soporte_retiros_start_date_filter') !== 'undefined' && localStorage.getItem('soporte_retiros_start_date_filter') !== null){
		$("#start_date_filter").val(localStorage.getItem('soporte_retiros_start_date_filter'));
	}else{
		$("#start_date_filter").val(moment().format("DD-MM-YY"));
	}
	if(localStorage.getItem('soporte_retiros_start_time_filter') !== 'undefined' && localStorage.getItem('soporte_retiros_start_time_filter') !== null){
		$("#start_time_filter").val(localStorage.getItem('soporte_retiros_start_time_filter'));
	}else{
		$("#start_time_filter").val("00:00:00");
	}
	if(localStorage.getItem('soporte_retiros_end_date_filter') !== 'undefined' && localStorage.getItem('soporte_retiros_end_date_filter') !== null){
		$("#end_date_filter").val(localStorage.getItem('soporte_retiros_end_date_filter'));
	}else{
		$("#end_date_filter").val(moment().add(1,'days').format("DD-MM-YY"));
	}
	if(localStorage.getItem('soporte_retiros_end_time_filter') !== 'undefined' && localStorage.getItem('soporte_retiros_end_time_filter') !== null){
		$("#end_time_filter").val(localStorage.getItem('soporte_retiros_end_time_filter'));
	}else{
		$("#end_time_filter").val("00:00:00");
	}
	if(localStorage.getItem('soporte_retiros_state_filter_filter') !== 'undefined' && localStorage.getItem('soporte_retiros_state_filter_filter') !== null){
		let state = localStorage.getItem('soporte_retiros_state_filter_filter');
		let state_array = state.split(',');
		$("#state_filter").val(state_array).change();
	}
	if(localStorage.getItem('soporte_retiros_is_verified_filter') !== 'undefined' && localStorage.getItem('soporte_retiros_is_verified_filter') !== null){
		$("#is_verified_filter").val(localStorage.getItem('soporte_retiros_is_verified_filter'));
	}
	if(localStorage.getItem('soporte_retiros_is_bonus_player_filter') !== 'undefined' && localStorage.getItem('soporte_retiros_is_bonus_player_filter') !== null){
		$("#bonus_player_filter").val(localStorage.getItem('soporte_retiros_is_bonus_player_filter'));
	}
	if(localStorage.getItem('soporte_retiros_channel_filter') !== 'undefined' && localStorage.getItem('soporte_retiros_channel_filter') !== null){
		$("#channel_filter").val(localStorage.getItem('soporte_retiros_channel_filter'));
	}
	if(localStorage.getItem('soporte_retiros_payment_type_filter') !== 'undefined' && localStorage.getItem('soporte_retiros_payment_type_filter') !== null){
		$("#payment_type_filter").val(localStorage.getItem('soporte_retiros_payment_type_filter'));
	}
	if(localStorage.getItem('soporte_retiros_client_gladcon_at_filter') !== 'undefined' && localStorage.getItem('soporte_retiros_client_gladcon_at_filter') !== null){
		$("#client_gladcon_at_filter").val(localStorage.getItem('soporte_retiros_client_gladcon_at_filter'));
	}
}

function clear_form_inputs(){
	$("input[id$='filter']").each(function () {
		$(this).val("");
	});

	$("#start_date_filter").val(moment().format("DD-MM-YY"));
	$("#start_time_filter").val("00:00:00");
	$("#end_date_filter").val(moment().add(1,'days').format("DD-MM-YY"));
	$("#end_time_filter").val("00:00:00");

	$("select[id$='filter']").each(function () {
		if ($(this).attr('id') === 'limit_filter'){
			$(this).val("100");
		} else if($(this).attr('id') === 'state_filter'){
			$(this).val(null).trigger('change')
		} else{
			$(this).val("-1");
		}
	});
}

function get_retiros(){

	loading(true);
	let data = {
		search : $("#search_filter").val(),
		start_date : $("#start_date_filter").val(),
		start_time : $("#start_time_filter").val(),
		end_date : $("#end_date_filter").val(),
		end_time : $("#end_time_filter").val(),
		amount_greater_than : $("#amount_greater_than_filter").val(),
		amount_less_than : $("#amount_less_than_filter").val(),
		state_list : $("#state_filter").val(),
		is_verified : $("#is_verified_filter").val(),
		is_bonus_player : $("#bonus_player_filter").val(),
		channel : $("#channel_filter").val(),
		payment_type : $("#payment_type_filter").val(),
		btag : $("#btag_filter").val(),
		client_gladcon_at : $("#client_gladcon_at_filter").val(),
		limit : $("#limit_filter").val(),
		id : $("#id_filter").val(),
	};

	auditoria_send({"proceso": "soporte_retiros_get_retiros","data": data});

	$.post(
		'sys/get_soporte_retiros.php',
		{
			"get_soporte_retiros_api" : data
		},
		function(r){
			loading(false);
			let response = JSON.parse(r);
			let data = response.data;
			let tblSoporteRetiros = $('#tblSoporteRetirosAPI');

			if ($.fn.DataTable.isDataTable("#tblSoporteRetirosAPI")) {
				tblSoporteRetiros.DataTable().clear().destroy();
			}
			dt_tblSoporteRetiros =  tblSoporteRetiros.DataTable({
				data : data,
				scrollY: "300px",
				scrollX: true,
				scrollCollapse: true,
				/*dom: 'lfBtip',*/
				//dom: "<'row'<'col-sm-6'l><'col-sm-6'Bf>>" +
				dom: "<'row'<'col-sm-6'l><'col-sm-6'Bf>>" +
					"<'row'<'col-sm-12't>>" +
					"<'row'<'col-sm-6'i><'col-sm-6'p>>"+
					"<'row'<'col-sm-12 seleccionados_text'>>"
					,
				colReorder: {
					 fixedColumnsLeft: 1
				},
				//stateSave: true,
				columnDefs:[
					{
						'targets': 0,
						'checkboxes': {
							'selectRow': true,
							selectAllPages: false
						}
					},
					{
						'targets': Columns.UNIQUE_ACCOUNT,
						'createdCell': function(td, rowData, row, col){
							switch (row.duplicated_bank_status){
								case duplicatedStatus.CLIENT_UNIQUE:
									td.setAttribute('data-search', 1)
									break
								case duplicatedStatus.CLIENT_MULTIPLE:
									td.setAttribute('data-search', 0.5)
									break
								case duplicatedStatus.DUPLICATED:
									td.setAttribute('data-search', 0)
									break
							}
						}
					}
				],
				'select': {
					'style': 'multi'
				},
				'order': [[1, 'asc']],
				rowId: function(row){
					return 'retiro-' + row.id
				},
				"lengthMenu": [[10, 25, 50, 100, 200, 400, -1], [10, 25, 50, 100, 200, 400, "Todos"]],
				buttons: {
					buttons: [
						{
							extend: "csvHtml5",
							exportOptions : {
								orthogonal: "exportcsv",
							}
						},
						{
							text: 'Aprobar',
							className: 'btn-primary-border',
							action: function (e, dt, node, config) {
								withdrawal_action("approve")
							}
						},
						{
							text: 'Pagar',
							className: 'btn-info-border hidden',
							action: function (e, dt, node, config) {
								withdrawal_action("pay")
							}
						},
						{
							text: 'Rechazar',
							className: 'btn-danger-border hidden',
							action: function (e, dt, node, config) {
								withdrawal_action("cancel")
							}
						}
					],
					dom: {
						button: {
							className: 'btn'
						},
						buttonLiner: {
							tag: null
						}
					}
				},
				columns: [
					{
						title: "",
						data: "id",
						defaultContent: "-"
					},
					{
						title: "Id",
						data: "id",
						defaultContent: "-"
					},
					{
						title: "#ID Cliente",
						data: "client_id",
						defaultContent: "-",
						render: function(data, type, row, meta){
							return `<a id="btnClientStatistics" data-client-id="${row.client_id}">${row.client_id}</a>`
						}
					},
					{
						title: "Nombres",
						data: "client_name",
						defaultContent: "-",
					},
					{
						title: "Monto",
						data: "amount",
						defaultContent: "-",
						render: function ( data, type, row, meta ) {
							let formatter = new Intl.NumberFormat('en-US', {
								maximumFractionDigits: 3,
								minimumFractionDigits: 2
							});
							return formatter.format(data);
						}
					},
					{
						title: "Estado",
						data: "state_name",
						defaultContent: "-",
					},
					{
						title: "Cuota < 1.20",
						data: "quota_status",
						defaultContent: "-",
						render: {
							_: function(data, type, row, meta) {
								let btnClass;
								if (row.quota_status == 1) btnClass = "btn-success"
								else if (row.quota_status == 0) btnClass = "btn-warning"
								else btnClass = "btn-info"

								if (type === "exportcsv") {
									if (row.quota_status == 1) return "Sin retiros de cuotas menores a 1.20"
									else if (row.quota_status == 0) return "Con retiros de cuotas menores a 1.20"
									else return "Error"
								}

								return `<button
										class="btn btn-sm btn-block btn-primary btn_retiro_cuota compact-padding ${btnClass}"
										data-request_id="${row.id}"
										data-client_id="${row.client_id}"
										data-request_time="${row.request_time}">
										<i class="fa fa-money"></i> Detalle
									</button>`;
							},
							sort: function(data, type, row, meta) {
								if (isNaN(row.quota_status) || row.quota_status === null) return "-1"
								return row.quota_status.toString()
							},
							filter: function(data, type, row, meta) {
								if (isNaN(row.quota_status) || row.quota_status === null) return "-1"
								return row.quota_status.toString()
							}
						}
					},
					{
						title: "Ratio Mágico",
						data: "ratio_magico",
						defaultContent: "-",
						render: function ( data, type, row, meta ) {
							let formatter = new Intl.NumberFormat('en-US', {
								maximumFractionDigits: 3,
								minimumFractionDigits: 2
							});
							if(data <= 1.10 && data >=0.90){// en el rango 0.90-1.1 , resaltar de color amarillo
								return "<div style='background-color:yellow'>"+formatter.format(data)+"</div>";
							}
							return formatter.format(data);
						}

					},
					{
						title: "Depositado",
						data: "deposits_total",
						defaultContent: "-",
						render: {
							_: function(data, type, row, meta) {
								return `<button
										class="btn btn-sm btn-block btn-secondary btn_depositado compact-padding btn-success"
										data-request_id="${row.id}"
										data-client_id="${row.client_id}"
										data-request_time="${row.request_time}">
										S/. ${row.deposits_total}
									</button>`;
							},
							sort: function(data, type, row, meta) {
								if (isNaN(row.deposits_total) || row.deposits_total === null) return ""
								return row.deposits_total.toString()
							},
							filter: function(data, type, row, meta) {
								if(row.deposits_total == "-.--"){
									return "0";
								}
								if (isNaN(row.deposits_total) || row.deposits_total === null) return ""
								if(row.deposits_total == 0){
									return "0"; // =0
								}
								if(row.deposits_total < 10){
									return "1"; // <10
								}
								else{
									return "2"; //>10
								}
								//return row.deposits_total.toString()
							},
						}
					},
					{
						title: "Depo TV",
						data: "depositos_televentas_total",
						defaultContent: "-",
						render: {
							_: function(data, type, row, meta) {
								let depo_tv_class = row.depositos_televentas_total === null || row.depositos_televentas_total === "-.--" ? "btn-success" : "btn-warning";
								return `<button
										class="btn btn-sm btn-block btn-secondary btn_depositado_tv compact-padding ${depo_tv_class}"
										data-client_id="${row.client_id}"
										data-from_date="${row.from_date}">
										S/. ${row.depositos_televentas_total}
									</button>`;
							},
							sort: function(data, type, row, meta) {
								if (isNaN(row.depositos_televentas_total) || row.depositos_televentas_total === null) return ""
								return row.depositos_televentas_total.toString()
							},
							filter: function(data, type, row, meta) {
								if(row.depositos_televentas_total == "-.--"){
									return "0"; //no
								}
								if (isNaN(row.depositos_televentas_total) || row.depositos_televentas_total === null) return ""
								return "1";//si
								//return row.depositos_televentas_total.toString()
							},
						}
					},
					{
						title: "Categorización",
						data: "client_sportsbook_profile_id",
						defaultContent: "-",
						createdCell:  function (td, cellData, rowData, row, col) {
							let playerCategory = playerCategorization.find(e => e.Id === rowData.client_sportsbook_profile_id)
							if (!playerCategory) return
							$(td).attr('style', `background-color: ${playerCategory.ColorHex}`);
						},
						render: {
							_: function(data, type, row, meta) {
								let playerCategory = playerCategorization.find(e => e.Id === data)
								if (!playerCategory) return ``
								return `
									<div>
										${playerCategory.Name}
									</div>
								`
							},
							sort: function(data, type, row, meta) {
								if (isNaN(data)) return data
								else if ( data === null ) return ""
								return data.toString()
							},
							filter: function(data, type, row, meta) {
								if (isNaN(data)) return data
								else if ( data === null ) return ""
								return data.toString()
							},
						}
					},
					{
						title: "Cuenta Única",
						data: null,
						defaultContent: "-",
						render:
							{
								_: function (data, type, row, meta) {
									if (row.channel === "Retail") return ``
									let uniqueAccountOption = getUniqueAccountOption (row.duplicated_bank_status)

									return `									
									<button 
										class="btn btn-sm btn-block btn-primary btn_unique_account compact-padding ${uniqueAccountOption.buttonClass}" 
										data-request_id="${row.id}"
										data-client_id="${row.client_id}"
										data-bank_account="${row.bank_account}"
										data-bank_cci="${row.bank_cci}"
										>
										${uniqueAccountOption.buttonMessage}										
									</button>`;
								},
								sort: function (data, type, row, meta) {
									if (row.channel === "Retail") return ``
									let uniqueAccountOption = getUniqueAccountOption (row.duplicated_bank_status)
									return uniqueAccountOption.sortableNumber
								},
								filter: function (data, type, row, meta) {
									if (row.channel === "Retail") return
									let uniqueAccountOption = getUniqueAccountOption (row.duplicated_bank_status)
									return uniqueAccountOption.sortableNumber
								}
						}
					},
					{
						title: "Canal",
						data: "channel",
						defaultContent: "-",
					},
					{
						title: "Banco/Tienda",
						data: "bank_bet_shop_name",
						defaultContent: "-",
						render: function(data, type, row, meta){
							if(row.bank === false){
								return `No especificado`;
							}else{
								return row.bank_bet_shop_name;
							}
						},
					},
					{
						title: "Fecha de Solicitud",
						data: "request_time",
						defaultContent: "-",
					},
					{
						title: "Fecha de Aprobación",
						data: "allow_time",
						defaultContent: "-",
					},
					{
						title: "Fecha de Pago",
						data: "payment_created",
						defaultContent: "-",
					},
					{
						title: "Bonus",
						data: "is_bonus_player",
						defaultContent: "-",
						render: {
							_: function(data, type, row, meta) {
								if (type === "exportcsv") {
									if (row.is_bonus_player === false) return "No es bonero"
									return "Es bonero"
								}

								if (row.is_bonus_player === false) {
									return `<div class="text-center text-success bonus_player_button cursor-pointer" data-client_id="${row.client_id}"><i class="fa fa-check fa-1x-2x"></i></div>`;
								} else {
									return `<div class="text-center text-danger bonus_player_button cursor-pointer" data-client_id="${row.client_id}"><i class="fa fa-times fa-1x-2x"></i></div>`;
								}
							},
							sort: function(data, type, row, meta) {
								if (row.is_bonus_player === false) return "0"
								return "1"
							},
							filter: function(data, type, row, meta) {
								if (row.is_bonus_player === false) return "0"
								return "1"
							}
						},
					},
					{
						title: "Verificado",
						data: "is_verified",
						defaultContent: "-",
                        render: {
							_: function(data, type, row, meta) {
								if (type === "exportcsv") {
									if (row.is_verified === true) return "Verificado"
									return "No verificado"
								}

								if (row.is_verified === true) {
									return `<span class="ui-helper-hidden">1</span><div class="text-center text-success"><i class="fa fa-check fa-1x-2x"></i></div>`;
								} else {
									return `<span class="ui-helper-hidden">0</span><div class="text-center text-danger"><i class="fa fa-times fa-1x-2x"></i></div>`;
								}
							},
							sort: function(data, type, row, meta) {
								if (row.is_verified === true) return "1"
								return "0"
							},
							filter: function(data, type, row, meta) {
								if (row.is_verified === true) return "1"
								return "0"
							}
                        },
					},
					{
						title: "Account Holder",
						data: "account_holder",
						defaultContent: "-",
						className: 'dt-body-center'
					},
					{
						title: "BTag",
						data: "btag",
						defaultContent: "-",
					},
					{
						title: "Fecha de Registro Cliente",
						data: "client_created",
						defaultContent: "-"
					},
					{
						title: "Tipo de Pago",
						data: "payment_type",
						defaultContent: "-",
					},

				],
				fnInitComplete: function(settings,json) {
					//load_deposits_total();
					//load_quotas();
					//load_duplicated_status();
					var api = this.api();
					api.columns(Columns.SORTABLE).every( function (i) {
						let column = this;
						let select = $('<select style="width:100%"><option value="">All</option></select>')

							.appendTo( $(column.footer()).empty())
							.on( 'change', function () {
								let val = $.fn.dataTable.util.escapeRegex(
									$(this).val()
								);
								i = $(this).parent().index();
								var column = api.column(i);
								column
									.search( val ? '^'+val+'$' : '', true, false )
									.draw();
							} );

						switch (column.index()){
							case Columns.CATEGORIZATION:
								playerCategorization.forEach( x => {
									select.append( `<option value="${x.Id}">${x.Name}</option>` )
								})
								break
							case Columns.QUOTA:
								select.append( '<option value="1">Sin retiros < 1.2</option>' )
								select.append( '<option value="0">Con retiros < 1.2</option>' )
								select.append( '<option value="-1">Error</option>' )
								break
							case Columns.IS_BONUS:
								select.append( '<option value="1">Es Bonero</option>' )
								select.append( '<option value="0">No es Bonero</option>' )
								break
							case Columns.IS_VERIFIED:
								select.append( '<option value="1">Verificado</option>' )
								select.append( '<option value="0">No Verificado</option>' )
								break
							case Columns.UNIQUE_ACCOUNT:
								select.append( '<option value="1">Cuenta Única</option>' )
								select.append( '<option value="0.5">Cuentas Únicas</option>' )
								select.append( '<option value="0">Cuenta Repetida</option>' )
								break

							case Columns.DEPOSIT:/*=0 | <10 | >10*/
								select.append( '<option value="0">=0</option>' )
								select.append( '<option value="1"><10</option>' )
								select.append( '<option value="2">>10</option>' )
								break
							case Columns.TOTAL_TV:/* Si y No*/
								select.append( '<option value="1">Si</option>' )
								select.append( '<option value="0">No</option>' )
								break

							default:
								column.data().unique().sort().each( function ( d, j ) {
									if (d === null) select.append( '<option value="-">-</option>' )
									else select.append( '<option value="' + d +'">'+ d +'</option>' )
								})
								break
						}


					} );

					////show/hide colummns modal
					sec_soporte_retiros_mostrar_ocultar_columnas(settings,json);
					////
					var datatable = settings.oInstance.api();
					$(document).on("change", ".table-responsive :checkbox", function(){
						var chequeados_length = datatable.column(0).checkboxes.selected().length;
						var txt = chequeados_length == 1 ? "Seleccionado":"Seleccionados";
						$(".seleccionados_text").text(chequeados_length + " " + txt);
					})

				},
				drawCallback: function(){
					//load_deposits_total();
					//load_quotas();
					//load_duplicated_status();
				}
			})
			dt_tblSoporteRetiros.on( 'column-reorder', function ( e, settings, details ) {
				var datatable = settings.oInstance.api();
				datatable.column(0).checkboxes.deselect();
				$(".seleccionados_text").text("");
			});

		}
	);
}

function sec_soporte_retiros_mostrar_ocultar_columnas(settings,json){
	var datatable = settings.oInstance.api();
	var localStorage_variable = "sec_soporte_retiros_columnas_visibles";
	col_visibles_array=[];
	col_visibles = localStorage.getItem(localStorage_variable);
	if(col_visibles != null){
		col_visibles_array = JSON.parse(col_visibles);
	}

	$("#filter_columnas_modal #col_select_list").empty();
    $(datatable.init().columns).each( function (i,e) {
		if(e.title == ""){
			return;
		}
		var chequeado = "checked";
		if(col_visibles){
			datatable.column(i).visible(false);
			chequeado = "";
			$(col_visibles_array).each(function(ii,ee){
				if(ee.i==i){
					chequeado = "checked";
					datatable.column(i).visible(true);
				}
			})
		}
		var li_html = "<li class='checkbox visible_input'>";
			li_html+= "<label>";
			li_html+= "<input type='checkbox' "+chequeado+" value="+i+" name='"+e.title+"'>";
			li_html+= e.title
			li_html+= "</label>";
			li_html+= "</li>";
		$("#filter_columnas_modal #col_select_list").append(li_html);
	});
	$("#filter_columnas_modal #col_select_list :checkbox").off("change").on("change",function(){
		var chequeado =  $(this).prop("checked");
		//var index_column= $(this).attr("value");
		var title_column_check= $(this).attr("name");
		var index_column = "";
		dt_tblSoporteRetiros.columns().every( function () {
		    var col_title = $(this.header()).text();
			if(col_title == title_column_check){
				index_column = i;
				if(chequeado){
					this.visible(true);
				}
				else{
					this.visible(false);
				}
				return;
			}
		})

		/*if($(this).prop("checked")){
			datatable.column(index_column).visible(true);
		}
		else{
			datatable.column(index_column).visible(false);
		}*/

		visibles_array=[];
		$("#filter_columnas_modal #col_select_list :checkbox:checked").each(function(i,e){
			visibles_array.push({i:parseInt($(e).val()),title:$(e).attr("name")});
		})
		localStorage.setItem(localStorage_variable, JSON.stringify(visibles_array));
	})
}

function load_quotas(){
	let tblSoporteRetiros = $('#tblSoporteRetirosAPI');

	if ($.fn.DataTable.isDataTable("#tblSoporteRetirosAPI")) {
		let rows = tblSoporteRetiros.DataTable().rows( { page: 'current' } ).data().toArray();
		for (let i = 0; i < rows.length; i++) {
			let row_id = rows[i].id;
			let data = {
				client_id : rows[i].client_id,
				request_time : rows[i].request_time,
				request_id : rows[i].id
			}

			$.post(
				'sys/get_soporte_retiros.php',
				{
					"get_retiros_quota_status" : data
				},
				function (r) {
					let response = JSON.parse(r);
					let retiro_cuota_btn = $(`.btn_retiro_cuota[data-request_id='${row_id}']`);

					if (response.data == 1){
						retiro_cuota_btn.html(`<i class="fa fa-money"></i> Detalle`);
						retiro_cuota_btn.addClass("btn-success");
					}
					else if (response.data == 0){
						retiro_cuota_btn.html(`<i class="fa fa-money"></i> Detalle`);
						retiro_cuota_btn.addClass("btn-warning");
					}
					else {
						retiro_cuota_btn.html(`<i class="fa fa-warning"></i> Error`);
					}
				}
			);
		}
	}
}

function load_deposits_total(){
	let tblSoporteRetiros = $('#tblSoporteRetirosAPI');


	if ($.fn.DataTable.isDataTable("#tblSoporteRetirosAPI")) {
		let rows = tblSoporteRetiros.DataTable().rows( { page: 'current' } ).data().toArray();
		for (let i = 0; i < rows.length; i++) {
			let row_id = rows[i].id;
			let data = {
				client_id : rows[i].client_id,
				request_time : rows[i].request_time,
				request_id : rows[i].id
			}

			auditoria_send({"proceso": "soporte_retiros_load_deposits_total","data": data});
			$.post(
				'sys/get_soporte_retiros.php',
				{
					"get_deposits_total" : data
				},
				function (r) {
					let response = JSON.parse(r);
					let retiro_cuota_btn = $(`.btn_depositado[data-request_id='${row_id}']`);

					if (response){
						retiro_cuota_btn.html(`S/. ${response.data}`);
						retiro_cuota_btn.addClass("btn-success");
					}
					else {
						retiro_cuota_btn.html(`<i class="fa fa-warning"></i> Error`);
					}
				}
			);
		}
	}
}

function load_duplicated_status(){
	const duplicatedStatus = Object.freeze({
		DUPLICATED: "duplicated_accounts",
		CLIENT_MULTIPLE: "client_multiple_accounts",
		CLIENT_UNIQUE: "client_unique_account",
		NO_ACCOUNTS: "no_client_accounts",
		QUERY_ERROR: "error",

	})
	let tblSoporteRetiros = $('#tblSoporteRetirosAPI');

	if ($.fn.DataTable.isDataTable("#tblSoporteRetirosAPI")) {
		let rows = tblSoporteRetiros.DataTable().rows( { page: 'current' } ).data().toArray();
		for (let i = 0; i < rows.length; i++) {
			let row_id = rows[i].id;
			let data = {
				client_id : rows[i].client_id,
				bank_account : rows[i].bank_account,
				bank_cci : rows[i].bank_cci
			}

			$.post(
				'sys/get_soporte_retiros.php',
				{
					"get_duplicated_bank_accounts_status" : data
				},
				function (r) {
					let response = JSON.parse(r);
					let btn_unique_account = $(`.btn_unique_account[data-request_id='${row_id}']`);
					let btn_unique_account_sort = $(`#btn_unique_account_sort[data-request_id='${row_id}']`);

					if (response){
						if (response.status === duplicatedStatus.CLIENT_UNIQUE){
							btn_unique_account.html(`Cuenta Única <span class="hidden"></span>`);
							btn_unique_account.addClass("btn-success");
							btn_unique_account_sort.data("sort")
						}
						else if (response.status === duplicatedStatus.CLIENT_MULTIPLE){
							btn_unique_account.html(`Cuenta Única <span class="hidden"></span>`);
							btn_unique_account.addClass("btn-warning");
							btn_unique_account_sort.text("0.5")
						}
						else if (response.status === duplicatedStatus.DUPLICATED){
							btn_unique_account.html(`Cuenta Repetida <span class="hidden"></span>`);
							btn_unique_account.addClass("btn-danger");
							btn_unique_account_sort.text("1")
						}
						else {
							//console.log(response);
						}
					}
					else {
						btn_unique_account.html(`<i class="fa fa-warning"></i> Error`);
					}
				}
			);
		}
	}
}

/**
 * @action: can be: "approve", "pay", "cancel"
 */
const withdrawal_action = (action) => {
	if (!$.fn.DataTable.isDataTable("#tblSoporteRetirosAPI")) return

	// Reset Back Office Action Modal Button and ProgressBar
	$("#boActionModalCloseButton").prop("disabled", true)
	$('#boActionProgressBar').css("width", "0")
	$('#boActionProgressBar').find("span").text("0%")

	let soporte_retiros_table = $("#tblSoporteRetirosAPI").DataTable()
	let selected_rows = soporte_retiros_table.column(0).checkboxes.selected()
	let selected_rows_formatted = []
	$.each(selected_rows, function(index, rowId){
		selected_rows_formatted.push('#retiro-' + rowId)
	})
	let rows = soporte_retiros_table.rows(selected_rows_formatted).data()
	if (!rows) return

	let selected_rows_length = {
		total : rows.length,
		rejected : rows.filter( item => item.state_name === "Rejected").length,
		cancelled : rows.filter( item => item.state_name === "Cancelled").length,
		new : rows.filter( item => item.state_name === "New").length,
		allowed : rows.filter( item => item.state_name === "Allowed").length,
		pending : rows.filter( item => item.state_name === "Pending").length,
		paid : rows.filter( item => item.state_name === "Paid").length,
		roll_backed : rows.filter( item => item.state_name === "RollBacked").length
	}

	if (action === "approve"){
		if (selected_rows_length.total === selected_rows_length.new){
			swal({
					title: "Confirmación de Aprobación",
					text: `¿Está seguro que desea aprobar estos ${selected_rows_length.new} retiros?`,
					type: "info",
					showCancelButton: true,
					confirmButtonText: "Sí",
					cancelButtonText:"Regresar"
				},
				function (){
					swal.close()
					execute_withdrawal_action(selected_rows, "allow")				}
			)
		} else {
			swal({
				title: "No se pudo aprobar",
				text: "Ha seleccionado retiros con estado diferente a: New",
				type: "warning",
				showCancelButton: true
			})
		}
		return
	}

	if (action === "cancel"){
		if (selected_rows_length.total === selected_rows_length.new + selected_rows_length.allowed){
			swal({
					title: "Confirmación de Rechazo",
					text: "¿Está seguro que desea rechazar estos retiros?",
					type: "info",
					showCancelButton: true,
					confirmButtonText: "Sí",
					cancelButtonText:"Regresar"
				},
				function (){
					alert("¡Acción realizada!")
				}
			)
		} else {
			swal({
				title: "No se pudo cancelar",
				text: "Ha seleccionado retiros con estado diferente a: New, Allowed",
				type: "warning",
				showCancelButton: true
			})
		}
		return
	}

	if (action === "pay"){
		if (selected_rows_length.total === selected_rows_length.allowed){
			swal({
					title: "Confirmación de Pago",
					text: "¿Está seguro que desea pagar estos retiros?",
					type: "info",
					showCancelButton: true,
					confirmButtonText: "Sí",
					cancelButtonText:"Regresar"
				},
				function (){
					alert("¡Acción realizada!")
				}
			)
		} else {
			swal({
				title: "No se pudo pagar",
				text: "Ha seleccionado retiros con estado diferente a: Allowed",
				type: "warning",
				showCancelButton: true
			})
		}
		return
	}

	swal({
		title: "No se pudo continuar",
		text: "Ha seleccionado retiros con un estado diferente al requerido",
		type: "warning",
		showCancelButton: true
	})
}

const execute_withdrawal_action = async (ids, action) => {
	if (ids.length === 0) return
	$("#boActionModal").modal("show")

	let progressBar = $('#boActionProgressBar')
	let closeButton = $("#boActionModalCloseButton")
	let boActionProcessInput = $("#boActionProcessText")
	boActionProcessInput.val("INICIANDO PROCESO: " + action.toUpperCase())
	boActionProcessInput.val(boActionProcessInput.val() + "\n Retiros por procesar: " + ids.length)

	let errors = false
	for (let i = 0; i < ids.length; i++) {
		boActionProcessInput.val(boActionProcessInput.val() + `\n Ejecutando proceso: ${i + 1}/${ids.length}`).trigger('change');

		let data = {
			id: ids[i],
			action: action
		}
		auditoria_send({"proceso": "soporte_retiros_withdrawal_action","data": data});

		const postPromise = new Promise((resolve,reject) => {
			$.post(
				'sys/get_soporte_retiros.php',
				{
					"execute_withdrawal_action": data
				},
				function (r) {
					const responseCheck = (r) => {
						try { return JSON.parse(r) }
						catch (e) { return false }
					};

					const response = responseCheck(r);

					if (!response || response.data === null){
						boActionProcessInput.val(boActionProcessInput.val() + `\n ERROR: No se pudo conectar con el API`).trigger('change');

						swal({
							title: "Error",
							text: "No se pudo conectar con el API",
							type: "warning",
							showCancelButton: true
						})
						closeButton.prop("disabled", false)
						return resolve(false)
					}

					else if (response.data.http_code !== 200){
						boActionProcessInput.val(boActionProcessInput.val() + `\n ERROR: ${JSON.stringify(response.data)}`).trigger('change');

						swal({
							title: "Error",
							text: "Respuesta inadecuada del API, revise los logs",
							type: "warning",
							showCancelButton: true
						})
						closeButton.prop("disabled", false)
						return resolve(false)
					}
					boActionProcessInput.val(boActionProcessInput.val() + `\n Proceso parcial finalizado`)
					let percentageProgress = Math.round(100 * (i + 1) / ids.length)
					progressBar.css("width", percentageProgress + "%")
					progressBar.find("span").text(percentageProgress + "%")
					return resolve(true)
				})
		})

		let result = await postPromise.then(success => {
			return success
		})

		if (!result) {
			errors = true
			break
		}
	}
	if (!errors) boActionProcessInput.val(boActionProcessInput.val() + "\nPROCESO FINALIZADO CON ÉXITO").trigger('change');
	else boActionProcessInput.val(boActionProcessInput.val() + "\nPROCESO FINALIZADO CON ERRORES").trigger('change');
	closeButton.prop("disabled", false)
}

function getUniqueAccountOption(duplicated_bank_status) {
	let uniqueAccountOption = {}

	switch (duplicated_bank_status) {
		case duplicatedStatus.CLIENT_UNIQUE:
			uniqueAccountOption.sortableNumber = "1"
			uniqueAccountOption.buttonMessage = "Cuenta Única"
			uniqueAccountOption.buttonClass = "btn-success"
			break
		case duplicatedStatus.CLIENT_MULTIPLE:
			uniqueAccountOption.sortableNumber = "0.5"
			uniqueAccountOption.buttonMessage = "Cuenta Única"
			uniqueAccountOption.buttonClass = "btn-warning"
			break
		case duplicatedStatus.DUPLICATED:
			uniqueAccountOption.sortableNumber = "0"
			uniqueAccountOption.buttonMessage = "Cuenta Repetida"
			uniqueAccountOption.buttonClass = "btn-danger"
			break
		default:
			uniqueAccountOption.sortableNumber = -1
			uniqueAccountOption.buttonMessage = "Error"
			uniqueAccountOption.buttonClass = "btn-info"
	}

	return uniqueAccountOption
}

/*
const Columns = {
	SELECTOR : 0,
	REQUEST_ID : 1,
	CLIENT_ID : 2,
	CLIENT_NAME : 3,
	CLIENT_CREATED : 4,
	CATEGORIZATION : 5,
	AMOUNT : 6,
	STATE_NAME : 7,
	REQUEST_TIME : 8,
	ALLOW_TIME : 9,
	AYMENT_CREATED : 10,
	IS_VERIFIED : 11,
	IS_BONUS : 12,
	CHANNEL : 13,
	PAYMENT_TYPE : 14,
	BANK_BET_SHOP_NAME : 15,
	UNIQUE_ACCOUNT : 16,
	DEPOSIT : 17,
	TOTAL_TV: 18,
	QUOTA : 19,
	ACCOUNT_HOLDER : 20,
	BTAG: 21,
	SORTABLE: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21]
}*/
const Columns = {
	SELECTOR : 0,
	REQUEST_ID : 1,
	CLIENT_ID : 2,
	CLIENT_NAME : 3,
	AMOUNT : 4,
	STATE_NAME : 5,
	QUOTA : 6,
	RATIOMAGICO: 7,
	DEPOSIT : 8,
	TOTAL_TV: 9,
	CATEGORIZATION : 10,
	UNIQUE_ACCOUNT : 11,
	CHANNEL : 12,
	BANK_BET_SHOP_NAME : 13,
	REQUEST_TIME : 14,
	ALLOW_TIME : 15,
	AYMENT_CREATED : 16,
	IS_BONUS : 17,
	IS_VERIFIED : 18,
	ACCOUNT_HOLDER : 19,
	BTAG: 20,
	CLIENT_CREATED : 21,
	PAYMENT_TYPE : 22,

	SORTABLE: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21]
}
const duplicatedStatus = Object.freeze({
	DUPLICATED: "duplicated_accounts",
	CLIENT_MULTIPLE: "client_multiple_accounts",
	CLIENT_UNIQUE: "client_unique_account",
	NO_ACCOUNTS: "no_client_accounts",
	QUERY_ERROR: "error",

})
const playerCategorization = [
	{
		"Id": 1,
		"Name": "New User (N)",
		"IsDefault": true,
		"Color": -256,
		"ColorHex": "#FFFF00"
	},
	{
		"Id": 2,
		"Name": "Low Risk (LR)",
		"IsDefault": null,
		"Color": -7278960,
		"ColorHex": "#90EE90"
	},
	{
		"Id": 3,
		"Name": "Negative",
		"IsDefault": null,
		"Color": -23296,
		"ColorHex": "#FFA500"
	},
	{
		"Id": 4,
		"Name": "High Risk (HR)",
		"IsDefault": null,
		"Color": -65536,
		"ColorHex": "#FF0000"
	},
	{
		"Id": 5,
		"Name": "VIP (V)",
		"IsDefault": null,
		"Color": -16744448,
		"ColorHex": "#008000"
	},
	{
		"Id": 6,
		"Name": "Not Playing (NP)",
		"IsDefault": null,
		"Color": -6632193,
		"ColorHex": "#9ACCFF"
	},
	{
		"Id": 7,
		"Name": "No Bonus User (NBU)",
		"IsDefault": null,
		"Color": -25704,
		"ColorHex": "#FF9B98"
	},
	{
		"Id": 8,
		"Name": "Casino (CA)",
		"IsDefault": null,
		"Color": -9531860,
		"ColorHex": "#6E8E2C"
	},
	{
		"Id": 9,
		"Name": "Agent (A)",
		"IsDefault": null,
		"Color": -11566659,
		"ColorHex": "#4F81BD"
	},
	{
		"Id": 10,
		"Name": "Test User (TU)",
		"IsDefault": true,
		"Color": -2566453,
		"ColorHex": "#D8D6CB"
	},
	{
		"Id": 11,
		"Name": "Arbitrage Betting (AB)",
		"IsDefault": null,
		"Color": -65536,
		"ColorHex": "#FF0000"
	},
	{
		"Id": 12,
		"Name": "SFM",
		"IsDefault": null,
		"Color": -261140,
		"ColorHex": "#FC03EC"
	},
	{
		"Id": 25,
		"Name": "Corridor (C)",
		"IsDefault": null,
		"Color": -3512039,
		"ColorHex": "#CA6919"
	},
	{
		"Id": 26,
		"Name": "Late Betting (LB)",
		"IsDefault": null,
		"Color": -65536,
		"ColorHex": "#FF0000"
	},
	{
		"Id": 27,
		"Name": "Strong Opinion (SO)",
		"IsDefault": null,
		"Color": -5621193,
		"ColorHex": "#AA3A37"
	},
	{
		"Id": 29,
		"Name": "1/2SFM",
		"IsDefault": null,
		"Color": -3428371,
		"ColorHex": "#CBAFED"
	},
	{
		"Id": 30,
		"Name": "Strong",
		"IsDefault": null,
		"Color": -16776961,
		"ColorHex": "#0000FF"
	},
	{
		"Id": 31,
		"Name": "BeforeVIP",
		"IsDefault": null,
		"Color": -3153873,
		"ColorHex": "#CFE02F"
	},
	{
		"Id": 33,
		"Name": "Review",
		"IsDefault": null,
		"Color": -9810292,
		"ColorHex": "#6A4E8C"
	},
	{
		"Id": 34,
		"Name": "BOT ARB",
		"IsDefault": null,
		"Color": -3397849,
		"ColorHex": "#CC2727"
	},
	{
		"Id": 35,
		"Name": "Bonus hunter",
		"IsDefault": null,
		"Color": -16225917,
		"ColorHex": "#086983"
	},
	{
		"Id": 36,
		"Name": "Value Bet (VB)",
		"IsDefault": null,
		"Color": -15231079,
		"ColorHex": "#179799"
	},
	{
		"Id": 37,
		"Name": "Very Negative",
		"IsDefault": null,
		"Color": -569270,
		"ColorHex": "#F7504A"
	},
	{
		"Id": 38,
		"Name": "Neutral",
		"IsDefault": null,
		"Color": -4854443,
		"ColorHex": "#B5ED55"
	},
	{
		"Id": 39,
		"Name": "Betshop Agent (BA)",
		"IsDefault": null,
		"Color": -8421505,
		"ColorHex": "#7F7F7F"
	},
	{
		"Id": 40,
		"Name": "Additional",
		"IsDefault": null,
		"Color": -1722738,
		"ColorHex": "#E5B68E"
	}
]