var importar_csv = false;
function sec_send_data_json(){
}
function sec_recaudacion() {

	if(sec_id=="recaudacion"){
		console.log("sec_recaudacion");		
		sec_recaudacion_events();
		sec_recaudacion_settings();
		if(sub_sec_id=="liquidaciones"){
			sec_recaudaciones_liquidaciones();  
		}
		if(sub_sec_id=="liquidacion_productos"){
			sec_recaudaciones_liquidacion_productos();  
		}
		if(sub_sec_id=="pagos_manuales"){
			sec_recaudacion_pagos_manuales();
		}
		if(sub_sec_id=="transacciones_bancarias"){
			sec_recaudacion_transacciones_bancarias();
		}
		if(sub_sec_id=="fraccionamiento"){
			sec_recaudacion_fraccionamiento();
		}
		if(sub_sec_id=="procesos"){
			sec_recaudacion_procesos();
		}
	}
}
function sec_recaudacion_settings(){
	var pos = Object();
	$(".table_tr_fixed_me").each(function(index, el) {
		pos[index] = {};
		pos[index].top = $(el).offset().top;
		pos[index].height = $(el).height();
	});
	$(document).on('scroll', function(event) {
		$(".table_tr_fixed_me").each(function(index, el) {
			var doc_top = $(window).scrollTop();
			if(doc_top>pos[index].top){
			}else{
			}
		});
	});
}
function liq_pro_action(data){
	console.log("liq_pro_action");
	console.log(data);

	var swal_msg = '';

	if(data.opt=="finalizar"){
		swal_msg = 'Finalizar un proceso no puede ser revertido y la data procesada será tranferida a la zona de cobranzas.';
	}

	swal({
		title: '¿Seguro?',
		text: swal_msg,
		type: 'info',
		showCancelButton: true,
		confirmButtonText: 'Si, proceder!',
		cancelButtonText: 'No, cancelar!',
		closeOnConfirm: false,
		closeOnCancel: true
	}, function(isConfirm){
		if (isConfirm){ 
			loading(true);

			$.post('sys/set_data.php', {
				"opt": 'liq_pro_action'
				,"data":data
			}, function(r) {
				console.log("liq_pro_action:done");
				try{
					var obj = jQuery.parseJSON(r);
					console.log(obj);
					if(obj.exists){
						loading(false);
						swal("Error!", "Ya existe un proceso en el rango de fechas!", "warning");
					}else{
						m_reload();
					}
				}catch(err){
					loading(false);
					swal("Error!", "ERROR!", "warning");
					console.log(r);
				}
				auditoria_send({"proceso":"liq_pro_action","data":data});
			});
		}
	});
}
function sec_recaudacion_events(){
	console.log("sec_recaudacion_events");
	
	
	loading();
	if(sub_sec_id=="procesos"){
		$(document).on('click', ".liq_pro_btn", function() {
			liq_pro_action($(this).data());
		})

		/*$(".liq_pro_btn")
			.off()
			.click(function(event) {
				liq_pro_action($(this).data());
			});*/
		// $(document)
		// 	.off("liq_pro_action_done")
		// 	.on("liq_pro_action_done",function(argument){
		// 		console.log("liq_pro_action_done");
		// 	});
	}
	
	$(".recaudacion_import_btn")
		.off()
		.click(function(event) {
			$("#recaudacion_import_modal")
				.off('shown.bs.modal')
				.on('shown.bs.modal', function (e) {
					sec_recaudacion_events();
					recaudacion_import_modal_events();
				})
				.modal({
					"backdrop":'static',
					"keyboard":false,
					"show":true
				});
			// console.log("recaudacion_import_btn:click");
		});

	$(".recaudacion_import_from_bc_btn")
		.off()
		.click(function(event) {
			console.log("recaudacion_import_from_bc_btn:click");
			$("#recaudacion_import_from_bc_modal")
				.off('shown.bs.modal')
				.on('shown.bs.modal', function (e) {
					sec_recaudacion_events();
					recaudacion_import_modal_events();
				})
				.modal({
					"backdrop":'static',
					"keyboard":false,
					"show":true
				});
		});

	$(".rec_reprocess_btn")
		.off()
		.click(function(event) {
			var boton = $(this);
			rec_reprocess(boton);
		});

	$(".rec_reprocess_all_btn")
		.off()
		.click(function(event) {

		var boton = $(this);
		var data = Object();
			data.filtro = Object();	
			data.where="validar_usuario_permiso_botones";			
			$(".input_text_validacion").each(function(index, el) {
				data.filtro[$(el).attr("data-col")]=$(el).val();
			});	
			data.filtro.text_btn = boton.data("button");
			console.log(data);
			auditoria_send({"proceso":"validar_usuario_permiso_botones","data":data});
			$.ajax({
				data: data,
				type: "POST",
				dataType: "json",
				url: "/api/?json"
			})
			.done(function( dataresponse) {
				try{
					console.log(dataresponse);
					if (dataresponse.permisos==true) {
						rec_reprocess_all(boton);
					}else{
						event.preventDefault();
						swal({
							title: 'No tienes permisos',
							type: "info",
							timer: 2000,
						}, function(){
							swal.close();
							
						});
					}
				}catch(err){
				    swal({
				        title: 'Error en la base de datos',
				        type: "warning",
				        timer: 2000,
				    }, function(){
				        swal.close();
				        loading();
				    }); 
				}
			})
			.fail(function( jqXHR, textStatus, errorThrown ) {
				if ( console && console.log ) {
					console.log( "La solicitud validar permisos importacion a fallado: " +  textStatus);
				}
			})
	});
	$(".rec_hide_all_btn")
		.off()
		.click(function(event) {
			rec_hide_all($(this));
		})
		;
	$(".checkbox_me")
		.off()
		.click(function(event) {
			var checkbox = $(this).find("input[type=checkbox]");
			if(checkbox.prop('checked')){
				checkbox.prop('checked', false);
				$(this).removeClass('checked');
			}else{
				checkbox.prop('checked', true);
				$(this).addClass('checked');
			}
		});
	$(".re_process_checkbox")
		.click(function(event) {
			if($(this).data("id")=="all"){
				if($(this).prop('checked')){
					$(".re_process_checkbox").prop('checked', true);
					$(".checkbox_me").addClass('checked');
				}else{
					$(".re_process_checkbox").prop('checked', false);
					$(".checkbox_me").removeClass('checked');
				}
			}
		});

	$(".recaudacion_generar_liquidaciones_btn")
		.off()
		.click(function(event) {
			$("#recaudacion_generar_liquidacion_modal")
				.off('shown.bs.modal')
				.on('shown.bs.modal', function (e) {
					sec_recaudacion_events();
					recaudacion_import_modal_events();					
				})
				.modal({
					"backdrop":'static',
					"keyboard":false,
					"show":true
				});
			// var text_button = $(this).data("button");
			// sec_recaudacion_validacion_premisos_usuarios_procesos_generar_liquidaciones(text_button);
		});
}
function recaudacion_import_modal_events() {
	console.log("recaudacion_import_modal_events");

	$(".recaudacion_import_from_bc_submit_btn")
		.off()
		.click(function(event) {
			console.log("recaudacion_import_from_bc_submit_btn:CLICK");
			var import_data = {};


			$(".import_bc_data").each(function(index, el) {
				if($(el).attr("type")=="radio"){
					if($(el).prop('checked')){
						import_data[$(el).attr("name")]=$(el).val();
					}
				}else if($(el).attr("type")=="checkbox"){
					if($(el).attr("name") in import_data){

					}else{
						import_data[$(el).attr("name")]={};
					}
					if($(el).prop('checked')){
						import_data[$(el).attr("name")][index]=$(el).val();
					}
				}else{
					import_data[$(el).attr("name")]=$(el).val();
				}
			});

			console.log(import_data);
			import_from_bc_init(import_data);
		});
	$("#recaudacion_import_from_bc_form")
		.off()
		.submit(function(event) {
			event.preventDefault();
			console.log("recaudacion_import_from_bc_form:submit");

			var import_data = {};


			$(".import_bc_data").each(function(index, el) {
				if($(el).attr("type")=="radio"){
					if($(el).prop('checked')){
						import_data[$(el).attr("name")]=$(el).val();
					}
				}else if($(el).attr("type")=="checkbox"){
					if($(el).attr("name") in import_data){

					}else{
						import_data[$(el).attr("name")]={};
					}
					if($(el).prop('checked')){
						import_data[$(el).attr("name")][index]=$(el).val();
					}
				}else{
					import_data[$(el).attr("name")]=$(el).val();
				}
			});

			console.log(import_data);
			import_from_bc_init(import_data);
		});

	$("#recaudacion_import_modal .btn_servicio")
		.off()
		.change(function(event) {
		});

   $(".recaudacion_datepicker")
   		.datepicker("destroy")
		.datepicker({
			dateFormat:'dd-mm-yy',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
		});

	$(".gen_liq_datepicker")
		.datepicker("destroy")
		.datepicker({
			dateFormat:'dd-mm-yy',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
		})
		.on("ready",function(ev){
		});

	$("#recaudacion_import_modal")
		.off('hidden.bs.modal')
		.on('hidden.bs.modal', function (e) {
			console.log("bye");
		});


	var input_file = $("#file");
		if(input_file){
			var upload_btn = $(".upload-btn");
		    var form = upload_btn.data("form");
		    var data = {};
		    	data["tabla"]="tbl_transacciones_repositorio";

			input_file.hide();
			input_file.off();
			var ele = document.getElementById(input_file.attr("id"));
			if(ele){
			    var files = ele.files;
					
					input_file.change(function(e) {
						files = e.target.files 
						$.each(files, function(index, file) {
							console.log(file);
							var new_file_div = $(".file_example").clone();
								new_file_div.removeClass('file_example');
								new_file_div.removeClass('hidden');
								new_file_div.addClass('file_'+index);
								new_file_div.find(".filename .name").html(file.name);
								new_file_div.find(".size").html((file.size/1024).toFixed(2)+"Kb");
							$("#"+form+" div.files_list_holder").append(new_file_div);
						});
					});
				}
		}

	$("#recaudacion_import_form")
		.off()
		.submit(function(event) {
			event.preventDefault();
			console.log("recaudacion_import_form:submit");


		    var upload_btn = $(".upload-btn");
		    var form = upload_btn.data("form");
		    var data = {};
		    	data["tabla"]="tbl_transacciones_repositorio";


			$(".import_data").each(function(index, el) {
				if($(el).attr("type")=="radio"){
					if($(el).prop('checked')){
						data[$(el).attr("name")]=$(el).val();
					}
				}else{
					data[$(el).attr("name")]=$(el).val();
				}
			});
			console.log(data);			
		
			
    		if(files.length){
    			var files_count = 0;
    			input_file.simpleUpload("sys/SimpleUpload.php", {
    				init: function(){

    				},
    				data:data,
    				finish: function(){
    					loading();
						swal({
							title: "Listo!",
							text: "Los archivos subieron exitosamente",
							type: "success",
							timer: 400,
							closeOnConfirm: false
						},
						function(){
							swal.close();
							loading(true);
							m_reload();
						});
    				},
					start: function(file){
						this.progressBar = $(".file_"+files_count+" .progress-bar");
						this.progress_num = $(".file_"+files_count+" .por .num");
						files_count++;
					},

					progress: function(progress){
						this.progressBar.width(progress + "%");
						this.progress_num.html(progress.toFixed(0));
					},

					success: function(data){
						console.log("success"); console.log(data);
					},

					error: function(error){
					}

				});
    		}else{
    			swal("Error!", "Seleccione un archivo", "warning");
    		}
		});
	
	$(".generar_cerrar_btn")
		.off()
		.click(function(event) {
			$("#recaudacion_generar_liquidacion_modal").modal("hide");
		});
	$("#recaudacion_generar_liquidacion_modal .timepicker")
		.timepicker({
	    	"showMeridian":false,
	    	"minuteStep":1
	    });
	$(".generar_btn")
		.off()
		.click(function(event) {
			var servicio_id = $("input[name=gen_servicio]:checked").val();
			var btn = $(this);
			rec_gen_liq(btn);
		});
}
function formatonumeros(x) {
	if (isNaN(x)) {
		return 0;
	}
	else{
		var y =parseFloat(x).toFixed(2);
		if (y) {
			return y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		}else{
			return 0;
		}		
	}
}
function getcurrentdate(){
	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth()+1; //January is 0!
	var yyyy = today.getFullYear();
	if(dd<10) {dd = '0'+dd} 
	if(mm<10) {mm = '0'+mm} 
	today =  dd+ '-'+ mm+ '-'+yyyy ;
	return today;
}	
function gettomorrowdate(){
	var today = new Date();
	var dd = today.getDate()+1;
	var mm = today.getMonth()+1; //January is 0!
	var yyyy = today.getFullYear();
	if(dd<10) {dd = '0'+dd} 
	if(mm<10) {mm = '0'+mm} 
	today =  dd+ '-'+ mm+ '-'+yyyy ;
	return today;
}
var process_all_list = {};
var process_all_progress = 0;
var process_all_list_length = 0;
function rec_reprocess_all(btn){
	console.log("rec_reprocess_all");
	if($('.re_process_checkbox').is(':checked')==true){
			swal({
				title: '¿Seguro?',
				text: 'Esta accion creará un nuevo proceso y procesará toda la informacion nuevamente.',
				type: 'info',
				showCancelButton: true,
				confirmButtonText: 'Si, procesar!',
				cancelButtonText: 'No, cancelar!',
				closeOnConfirm: false,
				closeOnCancel: true
			}, function(isConfirm){
				if (isConfirm){ 
					swal.close();
					loading(true);
					
					process_all_list = {};
					var ndx=0;
					$(".re_process_checkbox").each(function(index, el) {
						if($(el).prop("checked")){
							process_all_list[ndx]=$(el).data("id");
							ndx++;
						}
					});
					process_all_list_length = Object.keys(process_all_list).length;
					loading(true);
					var progress_bar = $('<div class="progress progress-lg"><div class="this-bar progress-bar progress-bar-striped active progress-bar-success"></div></div>');
					$(".loading_box").addClass("loading_box_progress");
					$(".loading_box").append(progress_bar);

					$(".this-bar").html(process_all_progress+"%");	
					$(".this-bar").stop().css({width: process_all_progress+"%"});

					rec_reprocess_all_send(0);

					$(document).on("rec_reprocess_all_"+(ndx-1),function(event) {
						console.log("FIN EVENT: rec_reprocess_all_"+(ndx-1));

						swal({
							title: "Listo!",
							text: "",
							type: "success",
							timer: 400,
							closeOnConfirm: false
						},
						function(){		
							swal.close();
							loading(true);	
							m_reload();
						});
					});
				}
			});
	}else{
			swal({
				title: 'Seleccione un registro',
				type: "info",
				timer: 2000,
			}, function(){
				swal.close();
				
			});
	}
}
function rec_reprocess_all_send(index) {

	var next_index = index+1;

	$(document).on("rec_reprocess_all_"+index,function(event) {
		$(document).off("rec_reprocess_all_"+index);

		process_all_progress = (next_index / process_all_list_length) * 100;
		$(".this-bar").html(next_index+"/"+process_all_list_length+" "+(process_all_progress).toFixed(0)+"%");	
		$(".this-bar").stop().css({width: process_all_progress+"%"});

		if(next_index in process_all_list){
			rec_reprocess_all_send(next_index);
		}
	});
	
	var data = Object();
		data.id = process_all_list[index];
	$.post('sys/set_data.php', {
		"opt": 'rec_reprocess'
		,"data":data
	}, function(r) {
		try{
			console.log(r);
			auditoria_send({"proceso":"rec_reprocess_all_send","data":data});
			$(document).trigger("rec_reprocess_all_"+index);
		}catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function(){
                swal.close();
                loading();
            }); 
		}
	});
}
function rec_reprocess(btn) {
	console.log("rec_reprocess");

	swal({
		title: '¿Seguro?',
		text: 'Esta accion creará un nuevo proceso y procesará toda la informacion nuevamente.',
		type: 'info',
		showCancelButton: true,
		confirmButtonText: 'Si, procesar!',
		cancelButtonText: 'No, cancelar!',
		closeOnConfirm: false,
		closeOnCancel: true
	}, function(isConfirm){
		if (isConfirm){ 
			swal.close();
			loading(true);
			var data = Object();
				data.id = btn.data("id");
	// 		console.log(data);
			$.post('sys/set_data.php', {
				"opt": 'rec_reprocess'
				,"data":data
			}, function(r, textStatus, xhr) {
				try{
					console.log(r);
					loading();
					swal({
						title: "Listo!",
						text: "",
						type: "success",
						timer: 400,
						closeOnConfirm: false
					},
					function(){
						auditoria_send({"proceso":"rec_reprocess","data":data});
						swal.close();
						loading(true);
						m_reload();
					});
				}catch(err){
		            swal({
		                title: 'Error en la base de datos',
		                type: "warning",
		                timer: 2000,
		            }, function(){
		                swal.close();
		                loading();
		            }); 
				}
			});
		}
	});
}
function rec_hide_all(btn){

	swal({
		title: '¿Seguro?',
		text: 'Esta accion ocultará todos los procesos seleccionados.',
		type: 'info',
		showCancelButton: true,
		confirmButtonText: 'Si, archivar!',
		cancelButtonText: 'No, cancelar!',
		closeOnConfirm: false,
		closeOnCancel: true
	}, function(isConfirm){
		if (isConfirm){ 
			swal.close();
			loading(true);

			//var list = {};
			var ndx=0;
			$(".re_process_checkbox").each(function(index, el) {
				if($(el).prop("checked")){
					var data = Object();
						data.table = "tbl_transacciones_procesos";
						data.id = $(el).data("id");
						data.col = "estado";
						data.val = "0";
					auditoria_send({"proceso":"switch_data","data":data});
					$.post('sys/set_data.php', {
						"opt": 'switch_data'
						,"data":data
					}, function(r, textStatus, xhr) {
						$(document).trigger("rec_reprocess_all_"+ndx);
					});
					ndx++;
				}
			});
			$(document).on("rec_reprocess_all_"+(ndx),function(event) {
				loading();
				swal({
					title: "Listo!",
					text: "",
					type: "success",
					timer: 400,
					closeOnConfirm: false
				},
				function(){
					swal.close();
					loading(true);
					m_reload();
				});
				
			});			
		}
	});
}
var liq_days_list = {};
var liq_progress = 0;
var liq_list_length=0;
var liq_proceso_id = false;
function rec_gen_liq(btn){
	console.log("rec_gen_liq");
	var data = Object();
		data.servicio_id = $("input[name=gen_servicio]:checked").val();
		data.inicio_fecha = $("input[name=inicio_fecha]").val();
		data.fin_fecha = $("input[name=fin_fecha]").val();
	
	
	var start = new Date(data.inicio_fecha);
	var end = new Date(data.fin_fecha);
	var diff  = new Date(end - start);
	var dias = diff/1000/60/60/24;

	liq_days_list = {};
	var ndx=0;
	for (d = 0; d < (dias+1); d++) { 
		var pro_date = new Date(data.inicio_fecha);
		var new_d = (d + 1);
		pro_date.setDate(pro_date.getDate() + new_d);
		liq_days_list[ndx]=pro_date;
		ndx++;
	}
	liq_list_length = ndx;

	swal.close();
	loading(true);

	var progress_bar = $('<div class="progress progress-lg"><div class="this-bar progress-bar progress-bar-striped active progress-bar-success"></div></div>');
	$(".loading_box").addClass("loading_box_progress");
	$(".loading_box").append(progress_bar);

	$(".this-bar").html(liq_progress+"%");	
	$(".this-bar").stop().css({width: liq_progress+"%"});

	var send_data = Object();
		send_data.servicio_id = data.servicio_id;
		send_data.proceso_id = liq_proceso_id;
	rec_gen_liq_send(0,send_data);

	$(document).on("rec_gen_liq_"+(ndx-1),function(event) {

		swal({
			title: "Listo!",
			text: "",
			type: "success",
			timer: 400,
			closeOnConfirm: false
		},
		function(){		
			swal.close();
			loading(true);	
			m_reload();
		});
	});
}
function rec_gen_liq_send(index,data){	

	var next_index = index+1;

	$(document).on("rec_gen_liq_"+index,function(event) {

		$(document).off("rec_gen_liq_"+index);

		liq_progress = (next_index / liq_list_length) * 100;
		$(".this-bar").html(next_index+"/"+liq_list_length+" "+(liq_progress).toFixed(0)+"%");	
		$(".this-bar").stop().css({width: liq_progress+"%"});

		if(next_index in liq_days_list){
			data.proceso_id = liq_proceso_id;
			rec_gen_liq_send(next_index,data);
		}
	});
	
		data.fecha = liq_days_list[index];
	$.post('sys/set_data.php', {
		"opt": 'rec_gen_liq'
		,"data":data
	}, function(r) {
		console.log("rec_gen_liq_send:DONE");
		try{
			var obj = jQuery.parseJSON(r);
			console.log(obj);
			liq_proceso_id = obj.proceso_id;
			data.proceso_id = liq_proceso_id;
		}catch(err){
			console.log(r);
		}
		auditoria_send({"proceso":"rec_gen_liq_send","data":data});
		$(document).trigger("rec_gen_liq_"+index);
	});
}
var import_days_list = {};
var import_progress = 0;
var import_progress_day = 0;
var import_list_length = 0;
var import_curr_index = 0;
var import_pages = 1;
var import_curr_page = 1;
var import_num_tickets = 0;
var import_repo_insert = 0;
var import_repo_update = 0;
var import_deta_insert = 0;
var import_deta_update = 0;
var import_no_procesados = {};
var import_total_time = 0;
function import_from_bc_init(data){
	
	var start = new Date(data.inicio_fecha);
	var end = new Date(data.fin_fecha);
	var diff  = new Date(end - start);
	var dias = diff/1000/60/60/24;

	import_days_list = {};
	var ndx=0;
	for (d = 0; d < (dias+1); d++) { 
		var pro_date = new Date(data.inicio_fecha);
		pro_date.setDate(pro_date.getDate() + d);
		import_days_list[ndx]=pro_date.toISOString().slice(0,10);
		ndx++;
	}
	import_list_length = ndx;

	loading(true);

	var progress_bar = $('<div class="progress progress-lg"><div class="this-bar progress-bar progress-bar-striped active progress-bar-success"></div></div>');
	$(".loading_box").addClass("loading_box_progress");
	$(".loading_box").append(progress_bar);

	$(".this-bar").html(import_progress+"%");	
	$(".this-bar").stop().css({width: import_progress+"%"});

	$(document).on("EVENT_import_from_bc"+(ndx-1),function(event) {
		$(document).off("EVENT_import_from_bc"+(ndx-1));
		import_progress = 100;
		import_curr_index = import_list_length;
		import_from_bc_progress_bar();

		
		loading();
		var swal_text = "La importación ha sido un éxito";
			swal_text += "<br>";
			swal_text += "Total Tickets: " + import_num_tickets;
			swal_text += "<br>";
			swal_text += "Repo Insert: " + import_repo_insert;
			swal_text += "<br>";
			swal_text += "Repo Update: " + import_repo_update;
			swal_text += "<br>";
			swal_text += "Deta Insert: " + import_deta_insert;
			swal_text += "<br>";
			swal_text += "Deta Update: " + import_deta_update;
			swal_text += "<br>";
			swal_text += "Tiempo Total: " + import_total_time.toFixed(0) + " Segundos.";
		swal({
			title: "Listo!",
			text: swal_text,
			type: "success",
			html:true,
			closeOnConfirm: true
		},
		function(){		
			swal.close();
			// RESET 
				import_days_list = {};
				import_progress = 0;
				import_progress_day = 0;
				import_list_length = 0;
				import_curr_index = 0;
				import_pages = 1;
				import_curr_page = 1;
				import_num_tickets = 0;
				import_repo_insert = 0;
				import_repo_update = 0;
				import_deta_insert = 0;
				import_deta_update = 0;
				import_no_procesados = {};
			// FIN RESET
		});
	});

	var send_data = $.extend({},data);
		send_data.servicio_id = data.servicio;

	import_from_bc_call_api(send_data);
}
function import_from_bc_call_api(data){
	var next_index = import_curr_index+1;

	var send_data = $.extend({},data);
		send_data.fecha = import_days_list[import_curr_index];
		send_data.page = import_curr_page;


	$(document).on("EVENT_import_from_bc"+import_curr_index,function(event) {
		console.log("EVENT: EVENT_import_from_bc"+import_curr_index);
		$(document).off("EVENT_import_from_bc"+import_curr_index);

		if(next_index in import_days_list){
			import_pages=1;
			import_curr_page=1;
			import_curr_index=next_index;
			import_from_bc_call_api(send_data);
			/**/
		}
		import_progress = import_progress_day = (next_index / import_list_length) * 100;
		import_from_bc_progress_bar();
	});	
	import_from_bc(send_data);
}
function import_from_bc(data){
		$.post('sys/set_data.php', {
			"opt": 'import_from_bc'
			,"data":data
		}, function(r) {
			try{
				var r_obj = {};
					r_obj["API_continue"] = false;
				try{
					r_obj = jQuery.parseJSON(r);
				}catch(err){
					r_obj["API_continue"] = false;
					console.log("HORROR");
				}
				if(r_obj["API_continue"]){
					console.log(r_obj);
					console.log("API_time_to_response: "+r_obj["API_time_to_response"]);
					console.log("time_total: "+r_obj["time_total"]);
					import_pages = r_obj["API_day_pages"];

					import_repo_insert += r_obj["repositorios_insertados"];
					import_repo_update += r_obj["repositorios_updateados"];
					import_deta_insert += r_obj["detalles_insertados"];
					import_deta_update += r_obj["detalles_updateados"];

					import_total_time += r_obj["time_total"];
					if(import_curr_page==1){
						import_num_tickets = import_num_tickets + r_obj["API_tickets_count"];
					}
					if(import_curr_page == import_pages){
						$(document).trigger("EVENT_import_from_bc"+import_curr_index);	
					}else{
						import_progress = import_progress_day + (((import_curr_page / import_pages) * 100) / import_list_length);
						import_from_bc_progress_bar();
						import_curr_page = import_curr_page+1;
						data.page = import_curr_page;
						import_from_bc(data);
					}
				}else{
					console.log("HORROR - ALL PROCESS STOPPED!!!");
					console.log(r_obj);
					console.log(r);
				}
			}catch(err){
	            swal({
	                title: 'Error en la base de datos',
	                type: "warning",
	                timer: 2000,
	            }, function(){
	                swal.close();
	                loading();
	            }); 
			}
		});	
}
function import_from_bc_progress_bar(){

	$(".this-bar").html((import_curr_index)+"/"+import_list_length+" "+(import_progress).toFixed(0)+"%");	
	$(".this-bar").stop().css({width: import_progress+"%"});
}
var API_data = {};
function recaudacion_import_from_bc(){
	console.log("recaudacion_import_from_bc");
	var data = {};

	data.username = "carlosmesta";
	data.password = "Cms2204$";

	data.language = "en";

	API_data.Authentication = localStorage.getItem("API_Authentication");


	$(document).on("API_CheckUserLoginPassword",function(event) {
		$(document).off("API_CheckUserLoginPassword");
		console.log("EVENT: API_CheckUserLoginPassword");
		console.log(event.event_data);
		if(event.event_data.textStatus=="success"){
			API_CheckForLogin(data);
		}else{
			console.log("ERROR");
		}
	});

	$(document).on("API_CheckForLogin",function(event) {
		$(document).off("API_CheckForLogin");
		console.log(event.event_data);
		if(event.event_data.textStatus=="success"){
			if(event.event_data.return_data.Data===true){
				console.log("ESTA LOGEADO");
				console.log(API_data);
				API_GetBetReport();				
			}else{
				console.log("NO ESTA LOGEADO");
				API_Login(data);
			}
		}else{
			console.log("NO ESTA LOGEADO");
		}
	});

	$(document).on("API_Login",function(event) {
		$(document).off("API_Login");
		console.log("EVENT: API_Login");
		console.log(event.event_data);
		if(event.event_data.textStatus=="success"){
			API_data.Authentication = event.event_data.xhr.getResponseHeader("Authentication");
			localStorage.setItem("API_Authentication", API_data.Authentication);

			if(event.event_data.return_data.HasError === true){
				console.log("LOGIN INCORRECTO: "+event.event_data.return_data.AlertMessage);
			}else{
				console.log("LOGIN CORRECTO");
				console.log(event.event_data.xhr.getResponseHeader("Authentication"));
				console.log(API_data);
				console.log(localStorage);
				API_GetBetReport();
			}
		}else{
			console.log("NO LOGIN");
		}
	});

	$(document).on("API_Logout",function(event) {
		$(document).off("API_Logout");
		console.log("EVENT: API_Logout");
		console.log(event.event_data);
		if(event.event_data.textStatus=="success"){
			console.log("LOG OUT");
			localStorage.removeItem("API_Authentication");
			console.log(localStorage);
		}
	});

	$(document).on("API_GetBetReport",function(event) {
		$(document).off("API_GetBetReport");
		console.log("EVENT: API_GetBetReport");
		console.log(event.event_data);
		if(event.event_data.textStatus=="success"){
			console.log("HAY DATA");
			if(event.event_data.return_data.Data != null){
				console.log(event.event_data.return_data.Data.BetData.Count);
			}
			loading();
		}else{
			console.log("NO HAY DATA");
		}
	});

	$(document).on('API_loading', function(event) {
		loading(true,true);
	});
	$(document).on('API_CheckAuthentication', function(event) {
		$(document).off("API_CheckAuthentication");
		console.log("EVENT: API_CheckAuthentication");
		console.log(event.event_data);
		API_GetBetReport();
	});


	if(API_data.Authentication){
		var var_API_CheckAuthentication = API_CheckAuthentication(data);
		if(var_API_CheckAuthentication){
			API_GetBetReport();
		}
	}else{
		API_Login(data);		
	}
}
function API_CheckAuthentication(data){
	console.log("API_CheckAuthentication");
	console.log(API_data.Authentication);
	$.ajax({
		type: "GET",
		url: 'https://backofficewebadmin.betconstruct.com/api/en/Account/CheckAuthentication',
		success: function(return_data, textStatus, xhr) {
			var event_data = {};
			event_data.return_data = return_data;
			event_data.textStatus = textStatus;
			event_data.xhr = xhr;
			console.log(event_data);
			$(document).trigger({
				type:"API_CheckAuthentication",
				"event_data":event_data
			});
		},
		headers: {
			"Authentication": API_data.Authentication,
			"Accept":"application/json, text/plain, */*"
		}
	});	
}
function API_CheckUserLoginPassword(data){
	console.log("API_CheckUserLoginPassword");
	$.post('https://backofficewebadmin.betconstruct.com/api/en/Account/CheckUserLoginPassword'
	,{
		"Username":data.username
		,"Password":data.password
		,"Language":data.language
	}
	, function(return_data, textStatus, xhr) {
		var event_data = {};
		event_data.return_data = return_data;
		event_data.textStatus = textStatus;
		event_data.xhr = xhr;
		$(document).trigger({
			type:"API_CheckUserLoginPassword",
			"event_data":event_data
		});
	});
}
function API_CheckForLogin(data){
	console.log("API_CheckForLogin");
	$.get('https://backofficewebadmin.betconstruct.com/api/en/Account/CheckForLogin'
	,{
		"username":data.username
	}
	, function(return_data, textStatus, xhr) {
		var event_data = {};
		event_data.return_data = return_data;
		event_data.textStatus = textStatus;
		event_data.xhr = xhr;
		$(document).trigger({
			type:"API_CheckForLogin",
			"event_data":event_data
		});
	});
}
function API_Login(data){
	console.log("API_Login");
	$.post('https://backofficewebadmin.betconstruct.com/api/en/Account/Login'
	,{
		"Username":data.username
		,"Password":data.password
		,"Language":data.language
	}
	, function(return_data, textStatus, xhr) {
		var event_data = {};
		event_data.return_data = return_data;
		event_data.textStatus = textStatus;
		event_data.xhr = xhr;
		$(document).trigger({
			type:"API_Login",
			"event_data":event_data
		});
	});
}
function API_Logout(data){
	console.log("API_Logout");
	$.ajax({
		type: "POST",
		url: 'https://backofficewebadmin.betconstruct.com/api/en/Account/Logout',
		success: function(return_data, textStatus, xhr) {
			var event_data = {};
			event_data.return_data = return_data;
			event_data.textStatus = textStatus;
			event_data.xhr = xhr;
			$(document).trigger({
				type:"API_Logout",
				"event_data":event_data
			});
		},
		headers: {
			Authentication: API_data.Authentication
		}
	});
}
function API_GetBetReport(data){
	console.log("API_CheckUserLoginPassword");
	var request = {};
		request.filterBet = {}
		request.filterBet.AmountFrom = ""
		request.filterBet.AmountTo = ""
		request.filterBet.WinningAmountFrom = ""
		request.filterBet.WinningAmountTo = ""
		request.filterBet.TypeName = "All"
		request.filterBet.StateName = "All"
		request.filterBet.CalcStartDateLocal = ""
		request.filterBet.CalcEndDateLocal = ""
		request.filterBet.StartDateLocal = "01-05-17 - 00:00:00"
		request.filterBet.EndDateLocal = "02-05-17 - 00:00:00"
		request.filterBet.Source = ""
		request.filterBet.OrderedItem = 11
		request.filterBet.IsOrderedDesc = ""
		request.filterBet.SportsbookProfileId = ""
		request.filterBet.ClientLoginIp = ""
		request.filterBet.PriceFrom = ""
		request.filterBet.PriceTo = ""
		request.filterBet.IsTest = ""
		request.filterBet.BetshopId = ""
		request.filterBet.InfoBetshopId = ""
		request.filterBet.InfoCashDeskId = ""
		request.filterBet.CurrencyId = ""
		request.filterBet.IsBonusBet = ""
		request.filterBet.BonusTypeId = ""
		request.filterBet.IsCashDeskPaid = ""
		request.filterBet.SkeepRows = 0
		request.filterBet.MaxRows = 500
		request.filterBet.IsWithSelections = true

		request.filterBetSelection = {}
		request.filterBetSelection.SportId = ""
		request.filterBetSelection.RegionId = ""
		request.filterBetSelection.CompetitionId = ""
		request.filterBetSelection.MatchId = ""

		request.matchFilter = {}
		request.matchFilter.currentSport = ""
		request.matchFilter.currentRegion = ""
		request.matchFilter.currentCompetition = ""
		request.matchFilter.currentMatch = ""

		request.filterDate = {}
		request.filterDate.fromDate = "20-05-17"
		request.filterDate.toDate = "21-05-17"
		request.filterDate.currentTimePeriod = 1
		request.filterDate.fromTimeObj = "2017-05-20T05:00:00.602Z"
		request.filterDate.toTimeObj = "2017-05-21T05:00:00.602Z"
		request.filterDate.fromTime = "00:00:00"
		request.filterDate.toTime = "00:00:00"

		request.isCreatedTime = true

		request.filterText = {}
		request.filterText.Text = ""

		request.ToCurrencyId = "PEN"


	console.log(request);

	var event_data = {};
		event_data.function = "API_GetBetReport";
		event_data.data = data;
		$(document).trigger({
			type:"API_loading",
			"event_data":event_data
		});
	$.ajax({
		type: "POST",
		url: 'https://backofficewebadmin.betconstruct.com/api/en/Report/GetBetReport',
		data:request,
		success: function(return_data, textStatus, xhr) {
			var event_data = {};
			event_data.return_data = return_data;
			event_data.textStatus = textStatus;
			event_data.xhr = xhr;
			$(document).trigger({
				type:"API_GetBetReport",
				"event_data":event_data
			});
		},
		headers: {
			"Authentication": API_data.Authentication,
			"Accept":"application/json, text/plain, */*"
		}
	});
}
function sec_recaudacion_validacion_premisos_usuarios_procesos_generar_liquidaciones(btn){
		$(document).on("evento_validar_permiso_usuario",function(event) {
			$(document).off("evento_validar_permiso_usuario");
			console.log("EVENT: evento_validar_permiso_usuario procesos");
			if (event.event_data==true) {
				console.log(event.event_data);
				$("#recaudacion_generar_liquidacion_modal")
				.off('shown.bs.modal')
				.on('shown.bs.modal', function (e) {
					sec_recaudacion_events();
					recaudacion_import_modal_events();					
				})
				.modal({
					"backdrop":'static',
					"keyboard":false,
					"show":true
				});
			}else{
				console.log(event.event_data);				
				event.preventDefault();
				swal({
					title: 'No tienes permisos',
					type: "info",
					timer: 2000,
				}, function(){
					swal.close();
					
				});
			}
		});
		validar_permiso_usuario(btn,sec_id,sub_sec_id);	
}