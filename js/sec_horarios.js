function sec_horarios(){
	if($('#tblHorarios').length > 0){
		loading(true);
		filter_horarios_table(0);
	}

	$('#txtBatchHorariosStartDate').datepicker({
		minDate: new Date(),
		dateFormat: "yy-mm-dd"
	});

	$('#txtBatchHorariosEndDate').datepicker({
		minDate: new Date(),
		dateFormat: "yy-mm-dd"
	});

	$('#btnBatchHorariosSearch').on('click', function(event) {
		event.preventDefault();
		
		filter_batch_table(0);
	});

	//REVISAR ESTO, CAUSA CONFLICTO CON OTROS SWITCHES
	// $(document).on('change', '.switch', function(event) {
	// 	event.preventDefault();
	// 	var obj = $(this);
	// 	var data = {};
	// 	data.id = $(this).data("id");
	// 	data.status = $(this).prop('checked');

	// 	auditoria_send({"proceso":"toggle_horarios","data":data});
	// 	$.post('/sys/get_horarios.php', {"toggle_horarios": data});
	// 	filter_horarios_table(0);
	// });

	$('#btnHorariosNew').on('click', function(event) {
		event.preventDefault();
		if($('#txtHorariosNombre').val() != ""){
			var data = {};
			data.name = $('#txtHorariosNombre').val();
			auditoria_send({"proceso":"new_horario","data":data});
			$.post('/sys/get_horarios.php', {"new_horario": data}, function(response) {
				result = JSON.parse(response);
				if(result.body.length){
					swal("Alerta!", result.body, "warning");	
				}
				else{
					loading(true);
					$('#txtHorariosNombre').val("");
					$('#txtHorariosNombre').focus();
					filter_horarios_table(0);
				}
			});
		}
	});

	$('#txtHorariosFilter').focus();

	$("#txtHorariosFilter").on("keyup", function() {
		$('#icoHorariosSpinner').show();
		filter_horarios_table(0);
	});

	$('#cbHorariosLimit').on('change', function(event) {
		loading(true);
		filter_horarios_table(0);
	});

	$(document).on('click', '#btnHorariosDelete', function(event) {
		event.preventDefault();

		var btn = $(this);
		swal({
			title: "Seguro?",
			text: "Una vez eliminado no se podrá recuperar! Esta operación no afecta historicos pasados que utilizaron este perfil.",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Si, borrar!",
			cancelButtonText:"No",
			closeOnConfirm: true
		},
		function(){
			var data = {};
			data.id = btn.data("id");

			loading(true);
			auditoria_send({"proceso":"delete_horarios","data":data});
			$.post('/sys/get_horarios.php', {"delete_horarios": data}, function(response) {
				filter_horarios_table(0);
			});
		});
	});

	$(document).on('click', '#btnHorariosView', function(event) {
		event.preventDefault();

		get_horario_dias_modal($(this).data("id"));

		$('#mdHorariosDiasTitle').html($(this).data("name"));
		$('#mdHorariosDias').modal("show");
	});

	$(document).on('click', '#btnHorarioDiasCancel', function(event) {
		event.preventDefault();
		
		$('#wrapForm_'+$(this).data("id")).hide();
		$('#wrapInfo_'+$(this).data("id")).show();
	});

	$(document).on('click', '[id^="chkClosedDay_"]', function(event) {
		if (this.checked){
			$('#txtHorariosDiasStartShift_'+$(this).data("id")).prop('disabled', true);
			$('#txtHorariosDiasStartBreak_'+$(this).data("id")).prop('disabled', true);
			$('#txtHorariosDiasEndBreak_'+$(this).data("id")).prop('disabled', true);
			$('#txtHorariosDiasEndShift_'+$(this).data("id")).prop('disabled', true);
		}
		else{
			$('#txtHorariosDiasStartShift_'+$(this).data("id")).prop('disabled', false);
			$('#txtHorariosDiasStartBreak_'+$(this).data("id")).prop('disabled', false);
			$('#txtHorariosDiasEndBreak_'+$(this).data("id")).prop('disabled', false);
			$('#txtHorariosDiasEndShift_'+$(this).data("id")).prop('disabled', false);
		}
	});

	$(document).on('click', '#btnHorarioDiasSend', function(event) {
		event.preventDefault();

		var data = {};
		data.horario_id = $(this).data("horario-id");
		data.massive = $(this).data("massive");
		data.weekday_id = $(this).data("weekday-id");
		data.start_shift= $('#txtHorariosDiasStartShift_'+data.weekday_id).val();
		data.start_break = $('#txtHorariosDiasStartBreak_'+data.weekday_id).val();
		data.end_break = $('#txtHorariosDiasEndBreak_'+data.weekday_id).val();
		data.end_shift = $('#txtHorariosDiasEndShift_'+data.weekday_id).val();
		data.closed = $('#chkClosedDay_'+data.weekday_id).is(':checked') | 0;

		console.log(data);

		if(!data.closed){
			if(data.start_shift== "" || data.end_shift == ""){
				swal("Datos Inválidos!", "Campos de Apertura y Cierre de Tienda son obligatorios", "warning");
				return;
			}
			else if((data.start_break == "") ^ (data.end_break == "")){ //XNOR Operand (O ambos falsos o ambos verdaderos. )
				swal("Datos Inválidos!", "Ambos Campos de Break(Inicio y Fin) deben estar llenados o vacios.", "warning");
				return;
			}
		}

		if(data.massive == 0){
			attach_horarios_dias(data);
		}
		else{
			swal({
				title: "Acción Masiva",
				text: "Estás seguro que deseas aplicar estos horarios para todos los dias del perfil?",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Si, enviar!",
				cancelButtonText:"No",
				closeOnConfirm: true
			},
			function(){
				attach_horarios_dias(data);
			});
		}
	});

	$('#mdHorariosDias').on('hidden.bs.modal', function() {
	    filter_horarios_table(0);
	});

	$(document).on('click', '#btnHorarioDiasEdit', function(event) {
		event.preventDefault();
		
		$('#wrapForm_'+$(this).data("id")).show();
		$('#wrapInfo_'+$(this).data("id")).hide();
	});

	$('#btnBatchHorariosEdit').on('click', function(event) {
		if($('[id*="chkBatchHorario"]:checked').length){
			event.preventDefault();

			$('#txtBatchHorariosStartDate').val();
			$('#txtBatchHorariosEndDate').val();

			$('#chkBatchHorariosRange').prop('checked', false);
			$('#divBatchHorarios').hide();

			var data = {};
			data.status = 1;
			auditoria_send({"proceso":"get_horarios_option","data":data});
			$.post('/sys/get_horarios.php', {"get_horarios_option": data}, function(response) {
				result = JSON.parse(response);

				$('#cbBatchHorarios option').remove();
				var defaultColor = false;
				$.each(result.options, function(index, option) {
					if(!defaultColor){
						defaultColor = option.color;
					}

					$('#cbBatchHorarios').append('<option style="background-color:'+option.color+'" value="'+option.id+'">'+option.name+'</option>');
				});
				$('#cbBatchHorarios').css('background-color', defaultColor);
				$('#mdBatchHorarios').modal("show");

			});

		}
		else swal("Alerta", "Seleccione al menos un local de la lista", "warning");
	});

	$('#cbBatchHorarios').on('change', function(event) {
		event.preventDefault();
		$(this).css('background-color', $('#cbBatchHorarios option:selected').css('background-color'));
	});

	$('#txtBatchHorariosStart').datepicker({
		minDate: new Date(),
		dateFormat: "yy-mm-dd"
	});

	$('#txtBatchHorariosEnd').datepicker({
		minDate: new Date(),
		dateFormat: "yy-mm-dd"
	});

	$('#btnBatchHorariosModalSend').on('click', function(event) {
		event.preventDefault();

		var data = {};
		
		data.locales = [];
		$.each($('[id="chkBatchHorario"]:checked'), function(index, val) {
			data.locales.push($(this).data("id"));
		});

		data.horario_id = $('#cbBatchHorarios option:selected').val();

		data.start_date = $('#txtBatchHorariosStart').val();
		data.daily = !$("#chkBatchHorariosRange").prop("checked");

		auditoria_send({"proceso":"set_batch_horarios","data":data});
		$.post('/sys/get_horarios.php', {"set_batch_horarios": data}, function(response) {
			result = JSON.parse(response);

			$('#mdBatchHorarios').modal("hide");

			swal("Guardado", result.body, "success");

			filter_batch_table();

			// $.each($('[id*="chkBatchHorario"]'), function(index, val) {
			// 	 if($.inArray($(this).data("id"), data.locales) >= 0){
			// 	 	$(this).prop("checked", true);
			// 	 }
			// });
		});
	});
}

function attach_horarios_dias(data){
	loading(true);
	auditoria_send({"proceso":"attach_horarios_dias","data":data});
	$.post('/sys/get_horarios.php', {"attach_horarios_dias": data}, function(response) {
		result = JSON.parse(response);
		swal("Guardado", result.body, "success");

		get_horario_dias_modal(data.horario_id);
	});
}

function filter_horarios_table(page) {
	var data 	= {};
	var limit 		= $("#cbHorariosLimit option:selected").val();
	data.page 	= page;
	data.limit 	= limit;
	data.filter = $("#txtHorariosFilter").val();
	auditoria_send({"proceso":"get_horarios","data":data});
	$.post('/sys/get_horarios.php', {"get_horarios": data}, function(response) {
		try{
			result = JSON.parse(response);
			$("#tblHorarios > tbody").html(result.body);

			$("#paginationHorarios").pagination({
				items: result.num_rows / limit,
				currentPage: page+1,
				itemsOnPage: limit,
				cssStyle: 'light-theme',
				onPageClick: function(pageNumber, event){
					event.preventDefault();
					loading(true);
					filter_horarios_table(pageNumber-1);
				}
			});

			applySwitcher();
			applyTableChange();
			
		}
		catch(error){
			console.log(error);
		}
		$('#icoHorariosSpinner').hide();
		loading();
	});
}

function filter_batch_table(page){
	var data = {};
	data.zona = $('#cbBatchHorariosZonas option:selected').val();
	data.start_date = $('#txtBatchHorariosStartDate').val();
	data.end_date = $('#txtBatchHorariosEndDate').val();

	loading(true);
	auditoria_send({"proceso":"get_batch_horarios","data":data});
	$.post('/sys/get_horarios.php', {"get_batch_horarios": data}, function(response) {
		try{
			result = JSON.parse(response);
			$('.fixed').remove(); //avoiding conflicts with fixme
			$("#tblBatchHorarios").html(result.body);
			applyCheckbox();
			$("#tblBatchHorarios").fixMe({"columns": 4, "footer": false, "marginTop":50, "zIndex": 1, "bgColor": "white", "bgHeaderColor": "#659BE0"});
			loading();
		}
		catch(error){
			console.log(error);
		}
		$('#icoHorariosSpinner').hide();
		loading();
	});
}

function get_horario_dias_modal(id){
	var data = {};
	data.id = id;
	loading(true);
	auditoria_send({"proceso":"get_horario_dias_modal","data":data});
	$.post('/sys/get_horarios.php', {"get_horario_dias_modal": data}, function(response) {
		result = JSON.parse(response);
		$('#tblHorariosDias').html(result.body);
		setTimeout(applyPicker(), 1000);
		loading();
	});
}

function applyTableChange(){
	$(document).on('click', '#cellHorarioName', function(event) {
		$(this).closest('tr').find('#spanHorarioName').hide();
		$(this).closest('tr').find('#txtHorarioName').show();
	});

	$(document).on('submit', '#formHorarioName', function(event) {
		event.preventDefault();

		var txt = $(this).closest('tr').find('#txtHorarioName');
		var span = $(this).closest('tr').find('#spanHorarioName');

		if(txt.val().length){			

			var data = {};
			data.id = $(this).closest('tr').find('#cellHorarioId').html();
			data.name = txt.val();

			auditoria_send({"proceso":"update_horario_name","data":data});
			$.post('/sys/get_horarios.php', {"update_horario_name": data}, function(response) {
				span.html(txt.val());
				txt.hide();
				span.show();
			});
		}
		else swal("Datos Inválidos!", "Por favor definir un nombre para el perfil de Horario", "warning");
	});

	$(document).on('click', '#cellHorarioColor', function(event) {
		$(this).closest('tr').find('#spanHorarioColor').hide();
		$(this).closest('tr').find('#txtHorarioColor').show();
	});

	$(document).on('submit', '#formHorarioColor', function(event) {
		event.preventDefault();

		var txt = $(this).closest('tr').find('#txtHorarioColor');
		var span = $(this).closest('tr').find('#spanHorarioColor');		

		var data = {};
		data.id = $(this).closest('tr').find('#cellHorarioId').html();
		data.color = txt.val();

		auditoria_send({"proceso":"update_horario_color","data":data});
		$.post('/sys/get_horarios.php', {"update_horario_color": data}, function(response) {
			span.html(txt.val());
			txt.closest('tr').css('background-color', txt.val()+" !important");
			txt.hide();
			span.show();
		});
	});
}

function applyPicker(){
	$('.timepicker').timepicker({
		change: function(time) {
			setTimepickerRules($(this));
		},
		timeFormat: 'HH:mm',
		interval: 15,
		dynamic: false,
		dropdown: true,
		scrollbar: true,
		zindex: 99999
	});

	for (var i = 0; i < 7; i++) {
		setTimepickerRules($("#txtHorariosDiasStartShift_"+i));
		setTimepickerRules($("#txtHorariosDiasStartBreak_"+i));
		setTimepickerRules($("#txtHorariosDiasEndBreak_"+i));
		setTimepickerRules($("#txtHorariosDiasEndShift_"+i));
	}
}

function applySwitcher(){
	$(".switch").bootstrapToggle({
		on:"activo",
		off:"inactivo",
		onstyle:"success",
		offstyle:"danger",
		size:"mini"
	});
}

function applyCheckbox(){
	$('[id="chkBatchHorario"]').on('change', function(event) {
		if(!$(this).prop("checked")){
			$('[id="chkBatchHorarioAll"]').prop("checked", false);
		}
		else if($('#tblBatchHorarios tbody tr').length == $('[id="chkBatchHorario"]:checked').length){
			$('[id="chkBatchHorarioAll"]').prop("checked", true);
		}
	});

	$('[id=chkBatchHorarioAll]').on('click', function(event) {
		$('[id="chkBatchHorario"]').prop("checked", $(this).prop("checked"));
	});
}

function setTimepickerRules(obj){
	if(obj.val() != obj.data("pre") && obj.val() != ""){
		obj.data("pre", obj.val());
		var time = new Date();
		time.setHours(obj.val().split(':')[0], obj.val().split(':')[1]);

		var match = obj.prop('id').match(/(StartShift|StartBreak|EndBreak|EndShift)_([0-6])/);
		var field = match[1];
		var weekday = match[2];
		if(field == "StartShift"){
			$('#txtHorariosDiasStartBreak_'+weekday).timepicker('option', 'minTime', time);
			if($('#txtHorariosDiasEndBreak_'+weekday).val() == ""){
				$('#txtHorariosDiasEndShift_'+weekday).timepicker('option', 'minTime', time);
			}
		}
		else if(field == "StartBreak"){
			$('#txtHorariosDiasStartShift_'+weekday).timepicker('option', 'maxTime', time);
			$('#txtHorariosDiasEndBreak_'+weekday).timepicker('option', 'minTime', time);
		}
		else if(field == "EndBreak"){
			$('#txtHorariosDiasStartBreak_'+weekday).timepicker('option', 'maxTime', time);
			$('#txtHorariosDiasEndShift_'+weekday).timepicker('option', 'minTime', time);
		}
		else if(field == "EndShift"){
			$('#txtHorariosDiasEndBreak_'+weekday).timepicker('option', 'maxTime', time);
			if($('#txtHorariosDiasStartBreak_'+weekday).val() == ""){
				$('#txtHorariosDiasStartShift_'+weekday).timepicker('option', 'maxTime', time);
			}
		}
	}
}