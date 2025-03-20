function sec_cobranzas_estados_de_cuenta() {
	console.log("sec_cobranzas_estados_de_cuenta");
	sec_cobranzas_estados_de_cuenta_events();
	cargar_tabla_locales_server();
}
function sec_cobranzas_estados_de_cuenta_events(){
	console.log("sec_cobranzas_estados_de_cuenta_events");

	$(".validar_numerico").validar_numerico();

	$("#btn_actualizar").off("click").on("click",function(){
		cargar_tabla_locales_server();
	});

	$("#btn_download_excel").off().on("click",function(){
		descarga_tabla_locales();
	});

	$("#btn_download_excel_detalle_total").off().on("click",function(){
		descarga_total_detalle_estado_cuenta_descarga_excel();
	});

	$("#div_estados_de_cuenta").on('show.bs.modal', '.modal', function () {
        if ($(".modal-backdrop").length > 1) {
            $(".modal-backdrop").not(':first').remove();
        }
    });
    // Remove all backdrop on close
    /*$(document).on('hide.bs.modal', '.modal', function () {
        if ($(".modal-backdrop").length > 1) {
            $(".modal-backdrop").remove();
        }
    });*/
    $("#cobranzas_gestionar_estados_de_cuenta_modal").on("hidden.bs.modal",function(){
		//cargar_tabla_locales_server();
    })
    $("#cobranzas_gestionar_estados_de_cuenta_modal").on("shown.bs.modal",function(){
		$("#gest_periodos_list_holder").empty();
		$("#gest_eecc_table_holder").empty();
		$("#gest_locales_list_holder_search").focus();
    })

    $("#cobranzas_generar_estados_de_cuenta_add_deuda_manual_modal").on("shown.bs.modal",function(){
		$("input:visible:text:first",$(this)).focus();
    })


	$(".gestionar_btn")
		.off()
		.click(function(event) {
			sec_cobranzas_estados_de_cuenta_gestionar_modal("show");
		});
	// $(".gestionar_btn").first().click();

	$(".enviar_btn")
		.off()
		.on("click",function(event) {
			sec_cobranzas_estados_de_cuenta_enviar_eventos();
			sec_cobranzas_estados_de_cuenta_vista_previa_eventos();
			sec_cobranzas_estados_de_cuenta_descargar_eventos();

			$("#cobranzas_estados_de_cuenta_enviar").modal("show");
			//sec_cobranzas_estados_de_cuenta_enviar_modal("show");
		});
	$(".cobranzas_generar_estados_de_cuenta_btn")
		.off()
		.click(function(event) {
			cobranzas_generar_estados_de_cuenta_modal(true);
		});
	$("#cobranzas_generar_estados_de_cuenta_modal .generar_cerrar_btn")
		.off()
		.click(function(event) {
			cobranzas_generar_estados_de_cuenta_modal();
		});
	// $('#cobranzas_generar_estados_de_cuenta_modal')
	// 	.on('show.bs.modal', function () {
	// 	})
	// 	.on('hide.bs.modal', function () {					
	// 	});

	$(document).on("click", ".pagos_detalle_td", function(){
		var pago_id=$(this).closest("tr").attr("data-pago_id");
		var tipo_pago=$(this).text();
		pago_detalle_view(pago_id,tipo_pago);
	});


	$("#select_year")
		.off()
		.change(function(event) {
			$("#select_mes").val("01").change();
			/* Act on the event */
		});
	$("#select_mes")
		.off()
		.change(function(event) {
			cobranzas_load_periodos();
			/* Act on the event */
		});
	$("#select_periodo")
		.off()
		.change(function(event) {
			
		});
	$('.make_me_select2').select2();
	$('#periodo_liquidacion_select').select2({
		dropdownParent: $("#cobranzas_estados_de_cuenta_enviar")
	});

	$("#load_list_btn")
		.off()
		.click(function(event) {
			console.log("load_list_btn:click");
			// cobranzas_load_locales_list(203);
			cobranzas_load_locales_list();
		});

	/*$("#table_estados_de_cuenta .ver_btn")
		.off()
		.click(function(event) {
			console.log("ver_btn:click");
			cobranzas_ver_eecc($(this).data());
		})*/

	$(document).on("click", "#table_estados_de_cuenta .ver_btn", function(){
		console.log("ver_btn:click");
		cobranzas_ver_eecc($(this).data());
	});

	$(".list_search_input")
		.off()
		.val("")
		.on('change keyup paste click', function () {
			var searchTerm = force_plain_text($(this).val());
			// console.log(searchTerm);
			var holder_id = $(this).data("holder");
			// console.log(holder_id);
			$("#"+holder_id+" tr").stop().hide();
			$("#"+holder_id+" td").each(function(index, el) {
				var h3_text = force_plain_text($(el).html());
				// console.log(h3_text);
				var n = h3_text.indexOf(searchTerm);
				if(n >= 0){
					$(el).parent("tr").stop().show();
				}
			});
			localStorage.setItem("cobranzas_estados_de_cuenta_list_search_input",$(this).val());
		})
		.val(localStorage.getItem("cobranzas_estados_de_cuenta_list_search_input"))
		.change()
		// .click()
		// .focus()
		;
	// $(".list_search_input");
	$(".list_search_clear_btn")
		.off()
		.click(function(event) {
			$(".list_search_input").val("").click().focus();
		});
	

	$("#cobranzas_eecc_view_modal").on("mouseover", ".tr_deuda", function(){
	//$(".tr_deuda").on("mouseover", function () {
		var data_periodo = $(this).attr("data-periodo");
	   $(".periodo_tr[data-periodo = '"+ data_periodo +"']").css("background-color","#CFF5FF !important");
	    
	});
	$("#cobranzas_eecc_view_modal").on("mouseleave", ".tr_deuda", function(){
		var data_periodo = $(this).attr("data-periodo");
	   $(".periodo_tr[data-periodo = '"+ data_periodo +"']").css("background-color","");
	})

	// $("#table_estados_de_cuenta").DataTable({
		// "order": [[ 1, "desc" ]]
	// });

	// FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE
	// $(".cobranzas_generar_estados_de_cuenta_btn").first().click();
	// $("#select_mes").first().change();
	// setTimeout(function(){
	// 	$("#load_list_btn").first().click();
	// }, 100);	
		// $("#table_estados_de_cuenta #local_140 .ver_btn").first().click();
	// FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE FIRE
}
function cobranzas_generar_estados_de_cuenta_modal(opt){
	console.log("cobranzas_generar_estados_de_cuenta_modal");
	if(opt){
		$("#cobranzas_generar_estados_de_cuenta_modal").modal("show"); // z-index:1010
		$("#select_mes").first().change(); //NO BORRAR ESTE
		
	}else{
		$("#cobranzas_generar_estados_de_cuenta_modal").modal("hide");
	}
}
function cobranzas_load_periodos(){
	console.log("cobranzas_load_periodos");

	var api_data = {};
		api_data.year = $("#select_year").val();
		api_data.mes = $("#select_mes").val();
	loading(true);
	$.post('api/?json', {
		"where": 'cobranzas_periodos',
		"data":api_data
	}, function(r) {
		try{
			var obj = jQuery.parseJSON(r);
			// obj.data[0]={"id":0,"nombre":"Seleccione un Cliente"};
			console.log(obj);
			$("#select_periodo").html("");
			if(obj.data.length){
				$.each(obj.data, function(index, val) {
					$('#select_periodo').append($('<option>', { 
						value: val.periodo_rango,
						text : val.periodo_rango 
					}));
				});
			}else{
				$("#select_periodo").html("<option disabled='disabled'>No hay periodos en este mes.</option>");
			}
			
			console.log("cobranzas_load_periodos:done");
			// console.log(obj.data.length);
			// $("#load_list_btn").first().click();
			// $('#input_local').select2('open');
			// $('#input_local').val(186);
			// $('#input_local').trigger('change');
			// $('#input_local').select2('close');
			loading();
		}catch(err){
			ajax_error(true,r,err);//opt,response,catch-error
		}
		auditoria_send({"proceso":"cobranzas_periodos","data":api_data});
	});
}

function cobranzas_load_locales_list(local_id){
	console.log("cobranzas_load_locales_list");
	loading(true);
	var get_data = {};
		get_data.year = $("#select_year").val();
		get_data.mes = $("#select_mes").val();
		get_data.rango = $("#select_periodo").val();

	$("#periodo_year").val(get_data.year);
	$("#periodo_mes").val(get_data.mes);
	$("#periodo_rango").val(get_data.rango);

	loading(true);
	$.post('/sys/get_cobranzas_estados_de_cuenta.php', {
		"opt": 'cobranzas_load_locales_list',
		"data":get_data
	}, function(r) {
		try{
			// $("#locales_list_holder").accordion("destroy");
			// console.log(r);
			$("#locales_list_holder").html(r);
			if($("#locales_list_holder").hasClass('ui-accordion')){
				$("#locales_list_holder").accordion("destroy");
				console.log("accordion.DESTROY");
				// $("#locales_list_holder").accordion();
			}else{
				// $("#locales_list_holder").accordion();
			}
			$("#locales_list_holder").accordion({
				collapsible: true
			});
			if(local_id){
				$("#locales_list_holder").scrollTop(0);
				var scroll_to_el = $(".local_h3_"+local_id).position();

				$("#locales_list_holder").scrollTop((scroll_to_el.top));

				var locales_indices = Array();

				$(".local_holder").each(function(index, el) {
					locales_indices.push($(el).data("local_id"));
				});
				var open_this = locales_indices.indexOf(local_id);

				// console.log(locales_indices);
				// console.log(local_id);
				// console.log(open_this);
				$("#locales_list_holder").accordion({
					active: open_this
				});

			}
			cobranzas_load_locales_list_events();
			console.log("cobranzas_load_locales_list:done");
			loading();
		}catch(err){
			loading();
			ajax_error(true,r,err);//opt,response,catch-error
		}
		// auditoria_send({"proceso":"cobranzas_load_locales_list","data":get_data});
	});	
}
function cobranzas_load_locales_list_events(){
	console.log("cobranzas_load_locales_list_events");
	
	$(".preview_btn")
		.off()
		.click(function(event) {
			cobranzas_preview(true,$(this).data());
		});
	$(".send_btn")
		.off()
		.click(function(event) {
			var btn_data = $(this).data();
			swal({
				title: '¿Seguro?',
				text: '',
				type: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Si!',
				cancelButtonText: 'No',
				closeOnConfirm: false,
				closeOnCancel: true
			}, function(isConfirm){
				if (isConfirm){ 
					swal.close();
					cobranzas_send_mail(btn_data);
				}
			});
		});	
}

function cobranzas_preview(opt,data) {
	console.log("cobranzas_preview");
	// console.log(data);
	loading(true);

	var request_data = {};
		request_data.where = "data_emailing";
		request_data.filtro = {};
		request_data.filtro.locales = {};
		request_data.filtro.locales[0]=data.local_id;
		request_data.filtro.fecha_inicio = data.periodo_year+"-"+data.periodo_mes+"-"+data.periodo_rango.substring(0, 2);
		request_data.filtro.fecha_fin = data.periodo_year+"-"+data.periodo_mes+"-"+data.periodo_rango.substring(3,5);
	var request_uri = $.param(request_data);
	// console.log(request_data.filtro);
	// console.log(request_uri);
	// $("#cobranzas_preview_mail_modal_iframe").html("");
	$("#cobranzas_preview_mail_modal_iframe")
		.attr("src","about:blank");
	$("#cobranzas_preview_mail_modal_iframe")
		.off()
		.load(function() {
			console.log("iframe:load");
			loading();
		});
	$("#cobranzas_preview_mail_modal_iframe")
		.attr("src","/api/cobranzas_preview_email.php?"+request_uri);
	// $.post('/api/cobranzas_preview_email.php', request_data, function(r) {
	// 	try{
	// 		// console.log(r);
			
	// 		$("#cobranzas_preview_mail_modal_iframe").html(r);
			cobranzas_preview_modal(true,data);

			
	// 		// local_add_deuda();
	// 		// cobranzas_load_locales_list(parseInt(set_data.local_id));
	// 	}catch(err){
	// 		// ajax_error(true,r,err);//opt,response,catch-error
	// 	}
	// 	// auditoria_send({"proceso":"cobranzas_add_deuda","data":set_data});
	// });	
}
function cobranzas_preview_modal(opt,data){
	console.log("cobranzas_preview_modal");
	if(opt){
		$("#cobranzas_preview_mail_modal").modal("show"); // z-index:1010
		// $("#select_mes").first().change();
		cobranzas_preview_modal_events(opt,data);
	}else{
		$("#cobranzas_preview_mail_modal").modal("hide");
		$("#cobranzas_preview_mail_modal_iframe")
			.attr("src","about:blank");
	}
}
function cobranzas_preview_modal_events(opt,data) {
	console.log("cobranzas_preview_modal_events");
	$(".reload_btn")
		.off()
		.click(function(event) {
			console.log("reload_btn:click");
			cobranzas_preview(opt,data);
		});
	$(".preview_send_btn")
		.off()
		.click(function(event) {
			console.log("preview_send_btn:click");
			// console.log(opt);
			// console.log(data);
			cobranzas_send_mail(data);
		});
	$(".preview_cerrar_btn")
		.off()
		.click(function(event) {
			console.log("preview_cerrar_btn:click");
			cobranzas_preview_modal();
		});
}

function cobranzas_send_mail(data) {
	console.log("cobranzas_send_mail");
	loading(true);
	try{
		var send_data = {};
			send_data.where = "data_emailing";
			send_data.send_email = "true";
			send_data.local_id = data.local_id;
			send_data.filtro = {};
			send_data.filtro.locales = {};
			send_data.filtro.locales[0]=data.local_id;
			// send_data.filtro.fecha_inicio = "2017-10-02";
			// send_data.filtro.fecha_fin = "2017-11-13";
			send_data.filtro.fecha_inicio = data.periodo_year+"-"+data.periodo_mes+"-"+data.periodo_rango.substring(0, 2);
			send_data.filtro.fecha_fin = data.periodo_year+"-"+data.periodo_mes+"-"+data.periodo_rango.substring(3,5);

			send_data.periodo_year = data.periodo_year;
			send_data.periodo_mes = data.periodo_mes;
			send_data.periodo_rango = data.periodo_rango;
			
			console.log(send_data);
		$.post('/api/cobranzas_send_email.php', send_data, function(r) {
			console.log("cobranzas_send_mail:done");
			
			loading();
			try{
				var obj = jQuery.parseJSON(r);
				console.log(obj);
				if(obj.email_sent){
					swal({
						title: "Listo!",
						text: "Enviado",
						type: "success",
						timer: 1000,
						closeOnConfirm: false
					},
					function(){							
						swal.close();
						cobranzas_preview_modal();
					});
				}else{
					swal({
						title: 'Error!',
						text:obj.email_error,
						type: "info",
						timer: 2000,
					}, function(){
						swal.close();
						loading();
					}); 
				}

			}catch(err){
				console.log("errrrrrrrrrror");
				console.log(err);
				console.log(r);
				swal({
					title: 'Error!',
					text:'Desconocido!',
					type: "info",
					timer: 3000,
				}, function(){
					swal.close();
					loading();
				}); 
				// ajax_error(true,r,err);//opt,response,catch-error
			}
			// auditoria_send({"proceso":"cobranzas_add_deuda","data":set_data});
		});
	}catch(err){
		console.log("errrrrrrrrrror");
		console.log(err);
		// console.log(r);
		swal({
			title: 'Error!',
			text:'Desconocido!',
			type: "info",
			timer: 3000,
		}, function(){
			swal.close();
			loading();
		}); 
		// ajax_error(true,r,err);//opt,response,catch-error
	}
}

function cobranzas_ver_eecc(data) {
	console.log("cobranzas_ver_eecc");
	console.log(data);
	var get_data = {};
	get_data.local_id = data.local_id;
	if (sub_sec_id=="detalle_estados_cuenta") {
		get_data.tipo = $("#tipo_cobranzas_detalle_ec").val();
		get_data.id_periodo_inicio = $("#periodo_inicio_cobranzas_detalle_ec").val();
		get_data.id_periodo_fin = $("#periodo_fin_cobranzas_detalle_ec").val();
	}
	loading(true);
	$.post('/sys/get_cobranzas_estados_de_cuenta.php', {
		"opt": 'cobranzas_ver_eecc',
		"data":get_data
	}, function(r) {
		try{
			$("#cobranzas_eecc_view_holder").html(r);
			 auditoria_send({"proceso":"cobranzas_ver_eecc","data":get_data});
			console.log("cobranzas_ver_eecc:done");
			cobranzas_ver_eecc_modal(true,data);
			loading();
		}catch(err){
			ajax_error(true,r,err);//opt,response,catch-error
		}
	});
}
function cobranzas_ver_eecc_modal(opt,data){
	console.log("cobranzas_ver_eecc_modal");
	if(opt){
		$("#cobranzas_eecc_view_modal").modal("show"); // z-index:1010
		// $("#select_mes").first().change();
		cobranzas_ver_eecc_modal_events(opt,data);
		
	}else{
		$("#cobranzas_eecc_view_modal").modal("hide");
	}
}
function cobranzas_ver_eecc_modal_events(opt,data){
	console.log("cobranzas_ver_eecc_modal_events");
	$("#cobranzas_eecc_view_modal .cerrar_btn")
		.off()
		.click(function(event) {
			console.log("cerrar_btn:click");
			cobranzas_ver_eecc_modal();
			$("#cobranzas_eecc_view_holder").html("");
		});
	$("#cobranzas_eecc_view_modal .reload_btn")
		.off()
		.click(function(event) {
			console.log("reload_btn:click");
			cobranzas_ver_eecc(data);
		});
	$("#cobranzas_eecc_view_modal .eecc_add_pago_btn")
		.off()
		.click(function(event) {
			console.log("eecc_add_pago_btn:click");
			var btn_data = $(this).data();
			console.log(btn_data);
			eecc_add_pago_modal(true,btn_data);
		});
	$("#cobranzas_eecc_view_modal .cobranzas_eecc_view_detalle_btn")
		.off()
		.click(function(event) {
			console.log("cobranzas_eecc_view_detalle_btn:click");
			var btn = $(this);
			var btn_data = btn.data();
			// console.log(btn_data);
			cobranzas_eecc_view_detalle(true,btn_data,btn);
		});
	$(".expand_collapse_btn")
		.off()
		.click(function(event) {
			console.log("expand_collapse_btn:click");
			// expand_collapse($(this));
		});

	$(".eecc_listar_pagos_periodo_btn")
		.off()
		.on("click",function(event) {
			var data = $(this).data();
			sec_cobranzas_modal_ver_pagos_periodo_eventos(data);
			$("#modal_ver_pagos_periodo").modal("show");
	});
}
function expand_collapse(btn) {
	console.log("expand_collapse");
	console.log("--------------------------------------------------------------------------------------------------");
	var table = btn.closest("table");
	var collapse = btn.data("collapse");
	// var collapse_me = $(".tr_"+collapse+".collapse_me");
		// collapse_me.addClass('hidden');
	console.log(collapse);
	// var tr_ = $(".tr_"+collapse+"").size();
	// console.log(tr_);
	var tr_collapse_me = $(".tr_"+collapse+"_collapse_me");
	// tr_collapse_me.addClass('hidden');

	// console.log(tr_collapse_me.size());
	tr_collapse_me.css('background-color', '#f00');
	tr_collapse_me.attr("data-collapsed","true");
	// var rowspan = (tr_ - tr_collapse_me);
	// var rowspan_me = $(".td_"+collapse+".rowspan_me");
		// rowspan_me.attr("rowspan",rowspan);

	// var closest_rowspan_me = table.find(".rowspan_me");
	// console.log(closest_rowspan_me);


	// var collapsible = table.find(".tr_"+collapse+".collapse_me");
	// console.log(collapsible);


	// var tr_size = table.find(".tr_ ").size();
	// console.log(tr_size);

	// var tr_ = table.find(".tr_"+collapse+"");
	// var tr_size = tr_.size();
	// var collapse_me_hidden_size = table.find(".tr_"+collapse+".hidden").size();

	// console.log(tr_size);
	// console.log("collapse_me_hidden_size: "+collapse_me_hidden_size);

	// var rowspan = (tr_size - collapse_me_hidden_size);
	// $(".tr_"+collapse+" .rowspan_me").attr("rowspan",rowspan);
	rowspan_fucker(table);
}
function rowspan_fucker(table) {
	console.log("rowspan_fucker");
	console.log(table);

	var tr_ = {};
	table
		.find("tr")
		.each(function(index, el) {
			var el = $(el);
			if(el.data("collapsed")){
				var classes = el.attr("class").split(' ');
				console.log(classes);
			}
		});
}
function cobranzas_eecc_view_detalle(opt,data,btn){
	console.log("cobranzas_eecc_view_detalle");
	console.log(data);
	console.log(btn);
	


	var view_detalle_table = $(btn).parent().find('.view_detalle_table');
	var view_detalle_span = $(btn).parent().find('.view_detalle_span');

	if(view_detalle_table.hasClass('hidden')){
		loading(true);
		var get_data = data;
		// get_data.year = $("#select_year").val();
		// get_data.mes = $("#select_mes").val();
		// get_data.rango = $("#select_periodo").val();
		$.post('/sys/get_cobranzas_estados_de_cuenta.php', {
			"opt": 'cobranzas_eecc_view_detalle',
			"data":get_data
		}, function(r) {
			try{
				// console.log(r);
				console.log("cobranzas_eecc_view_detalle:done");
				auditoria_send({"proceso":"cobranzas_eecc_view_detalle","data":get_data});

				// cobranzas_ver_eecc_modal(true,data);
				loading();

				btn.find("label").html("Ocultar");
				btn.find("span").removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
				view_detalle_span.addClass('hidden');
				view_detalle_table.find("tbody").html(r);
				view_detalle_table.removeClass('hidden');

			}catch(err){
				// ajax_error(true,r,err);//opt,response,catch-error
			}
			// auditoria_send({"proceso":"cobranzas_eecc_view_detalle","data":api_data});
		});
	}else{
		loading();
		btn.find("label").html("Ver Más");
		btn.find("span").removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
		view_detalle_span.removeClass('hidden');
		view_detalle_table.addClass('hidden');
	}
}

function pago_detalle_view(id,tipo_pago){
	console.log("pago_detalle_view");

	loading(true);
	var get_data = {pago_id:id};
	$.post('/sys/get_cobranzas_estados_de_cuenta.php', {
		"opt": 'pago_detalle_view',
		"data":get_data
	}, function(r) {
		try{
			console.log("pago_detalle_view:done");
			$("#pago_detalle_view_holder").html(r);
			$("#tipo_pago").text(tipo_pago);
			loading();
			$("#pago_detalle_view_modal").off("shown.bs.modal").on("shown.bs.modal",function(){
				loading();
				$("#pagodetalle"+id).imgViewer2();
			});
			$("#pago_detalle_view_modal").off("hidden.bs.modal").on("hidden.bs.modal",function(){
				$("#pagodetalle"+id).imgViewer2("destroy");
			});

			$("#pago_detalle_view_modal").modal("show");

		}catch(err){
			loading();

		}
	});
	//loading();
	
	
}

function eecc_add_pago_modal(opt,data) {
	console.log("eecc_add_pago_modal");
	if(opt){
		$.each(data, function(data_index, data_val) {
			// console.log(data_index+":"+data_val);
			$("#form_add_pago input[data-col="+data_index+"]").val(data_val);
		});
		$("#add_pago_input_abono").val("");
		$("#add_pago_input_descripcion").val("");
		$("#cobranzas_estados_de_cuenta_add_pago_modal").modal("show"); // z-index:1030
		// $("#add_pago_input_abono").focus();
		// $("#select_mes").first().change();
		eecc_add_pago_modal_events(opt,data);
		
	}else{
		$("#cobranzas_estados_de_cuenta_add_pago_modal").modal("hide");
	}
}
function eecc_add_pago_modal_events(opt,data) {
	console.log("eecc_add_pago_modal_events");
	$("#form_add_pago input[type=text]").first().focus();
	$("#cobranzas_estados_de_cuenta_add_pago_modal .cerrar_btn")
		.off()
		.click(function(event) {
			console.log("cerrar_btn:click");
			eecc_add_pago_modal();
			// $("#cobranzas_eecc_view_holder").html("");
		});
	$("#cobranzas_estados_de_cuenta_add_pago_modal .reload_btn")
		.off()
		.click(function(event) {
			console.log("reload_btn:click");
			eecc_add_pago_modal(opt,data);
		});
	$("#cobranzas_estados_de_cuenta_add_pago_modal .add_pago_btn")
		.off()
		.click(function(event) {
			console.log("add_pago_btn:click");
			swal({
				title: '¿Seguro?',
				text: '',
				type: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Si!',
				cancelButtonText: 'No',
				closeOnConfirm: false,
				closeOnCancel: true
			}, function(isConfirm){
				if (isConfirm){ 
					swal.close();
					cobranzas_add_pago(data);
				}
			});
			
		});
}


function sec_cobranzas_estados_de_cuenta_gestionar_modal(opt){
	console.log("sec_cobranzas_estados_de_cuenta_gestionar_modal");
	console.log(opt);
	$("#cobranzas_gestionar_estados_de_cuenta_modal").modal(opt);
	if(opt=="show"){
		sec_cobranzas_estados_de_cuenta_gestionar_modal_events("first");		
	}
}


function sec_cobranzas_modal_ver_pagos_periodo_eventos(data){
	$("#modal_ver_pagos_periodo").off("shown.bs.modal").on("shown.bs.modal",function(){
		cargar_pagos(data);
	});
	$(document).on("click", "#modal_ver_pagos_periodo .btn-eliminar_pago_detalle", function()
	{
		var data = $(this).data();
		swal({
				title: '¿Seguro?',
				text: '',
				type: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Si!',
				cancelButtonText: 'No',
				closeOnConfirm: false,
				closeOnCancel: true
			}, function(isConfirm){
				if (isConfirm){ 
					swal.close();
					eliminar_pago_detalle(data);
				}
			});
	})
	$("#modal_ver_pagos_periodo").off("click").on("click", ".btn-ver_pago_detalle", function()
	{
		var data = $(this).data();
		ver_pago_detalle(data);
	})
	$("#modal_ver_pagos_periodo .reload_btn").off("click").on("click",function(){
		cargar_pagos(data);
	})
}
function cargar_pagos(data){
	set_data = data;
	set_data.opt = "lista_pagos_periodo";
    $.ajax({
        url: "/sys/get_cobranzas_estados_de_cuenta.php",
        data:set_data,
        type: 'POST',
         beforeSend: function() {
			loading(true);
         },
         complete:function(){
			loading();
         },
        success: function (response) {//  alert(datat)
            var resp = JSON.parse(response);
            var data = resp.lista;
			set_data.curr_login = resp.curr_login;
			//$("#modal_ver_pagos_periodo .reload_btn").data(set_data);
			$("#modal_ver_pagos_periodo #periodo_liquidacion_text").text(set_data.periodo_rango);			
			table_pagos_periodo=$('#table_pagos_periodo').DataTable( {
                "bDestroy": true,
					"language": {
	                    "search": "Buscar:",
	                    "lengthMenu": "Mostrar _MENU_ registros por página",
	                    "zeroRecords": "No se encontraron registros",
	                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
	                    "infoEmpty": "No hay registros",
	                    "infoFiltered": "(filtrado de _MAX_ total registros)",
	                    "paginate": {
	                        "first": "Primero",
	                        "last": "Último",
	                        "next": "Siguiente",
	                        "previous": "Anterior"
	                    },
	                    sProcessing: "Procesando..."
	                },
				//sDom:"<'row'<'col-sm-4 div_checkeados'l ><'col-sm-4'><'col-sm-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
				"initComplete": function (settings, json) {
				},
                order:[[ 1, "desc" ]],
                paging:true,
				columns: [
					{
						title: "Pago Tipo",
						data: "pago_tipo_nombre",
					},
					{
						title: "Fecha",
						data: "fecha_ingreso",
					},
					{
						title: "Nro Operación",
						data: "nro_operacion",
						defaultContent: " --- "
					},
					{
						title: "Monto",
						data: "repartir",
						"render":function(data,type,row){
							var pago_tipo_id = row["pago_tipo_id"];
							if(pago_tipo_id == 5 ){
								return row["abono_saldo"];
							}
							return row["repartir"];
						},
					},
					{
						title: "Abono",
						data: "abono",
						"render":function(data,type,row){
							var pago_tipo_id = row["pago_tipo_id"];
							if(pago_tipo_id == 5 && row["pago_detalle_id"]=="null"){
								return row["abono_saldo"];
							}
							return row["abono"];
						},
					},
					{
						title: "Saldo a Favor",
						data: "saldo_favor",
						defaultContent: "0.00",
						"render":function(data,type,row){
							var pago_tipo_id = row["pago_tipo_id"];
							if(pago_tipo_id == 5){
								return "0.00";
							}
							return data;
						}

					},
					{
						title: "Descripción",
						data: "descripcion",
						defaultContent: " --- "
					},
					{
						title: "Acción",
						data: "id",
						"render":function(data,type,row){
							var id = row["id"];
							var pago_id = row["pago_id"];
							var pago_tipo_id = row["pago_tipo_id"];
							var pago_tipo_nombre = row["pago_tipo_nombre"];
							html =  "";
							html += "<button class='btn btn-sm text-success btn-default btn-ver_pago_detalle' data-id='"+id+"' data-pago_detalle_id='"+id+"' data-pago_id ='"+pago_id+"' data-pago_tipo_id='"+pago_tipo_id+"'  data-periodo_liquidacion_id='"+set_data.periodo_liquidacion_id+"'  data-local_id='"+set_data.local_id+"' data-pago_tipo_nombre='"+pago_tipo_nombre+"'>";
							html += "<span class='fa fa-search'></span>";
							html += "</button>";

							html += "<button class='btn btn-sm text-danger btn-default btn-eliminar_pago_detalle' data-pago_tipo_id='"+pago_tipo_id+"'' data-pago_detalle_id='"+id+"' data-pago_id ='"+pago_id+"' >";
							html += "<span class='glyphicon glyphicon-remove'></span>";
							html += "</button>";
							return html;
						},
					}
				],
				data:data,
			} );
        },
		error: function () {
			set_data.error = obj.error;
			set_data.error_msg = obj.error_msg;
			auditoria_send({"proceso":"lista_pagos_periodo","data":set_data});
        }
    });
}
function eliminar_pago_detalle(data){
	set_data = data;
	set_data.opt = "eliminar_pago_periodo";
    $.ajax({
        url: "/sys/get_cobranzas_estados_de_cuenta.php",
        data:set_data,
        type: 'POST',
         beforeSend: function() {
			loading(true);
         },
         complete:function(){
			loading();
         },
        success: function (response) {//  alert(datat)
            var resp = JSON.parse(response);
			set_data.curr_login = resp.curr_login;
			auditoria_send({"proceso":"eliminar_pago_periodo","data":set_data});
			$("#cobranzas_eecc_view_modal .reload_btn").click();
			swal({
					html:true,
					title: 'Pagos',
					text:resp.mensaje,
					type: "error",
					timer: 1500,
				}, function(){
					swal.close();
					loading();
			});
			$("#modal_ver_pagos_periodo .reload_btn").click();			
        },
        error: function () {
			set_data.error = obj.error;
			set_data.error_msg = obj.error_msg;
			auditoria_send({"proceso":"eliminar_pagos_periodo_error","data":set_data});
        }
    });
}
function ver_pago_detalle(data){
	set_data = data;
	set_data.opt = "ver_pago_detalle";
    $.ajax({
        url: "/sys/get_cobranzas_estados_de_cuenta.php",
        data:set_data,
        type: 'POST',
         beforeSend: function() {
			loading(true);
         },
         complete:function(){
			loading();
         },
        success: function (response) {//  alert(datat)
			//set_data.curr_login = resp.curr_login;
			$("#row_detalle_pago").html(response);
			$("#pago_detalle_text").text(set_data.pago_tipo_nombre);
			//$("#pago_detalle_text").text(resp.title_modal);
			$("#modal_ver_pagos_periodo_detalle").modal("show");
			$("#vista_previa_modal #img01").attr("src","files_bucket/pagos_voucher/"+$("input[name='voucher_img']").val());

			$("#vista_previa_pago_detalle_img").off("click").on("click",function(){
				$("#vista_previa_modal").modal("show");
			});
			$("#vista_previa_modal").off("shown.bs.modal").on("shown.bs.modal",function(){				
				$("#img01").imgViewer2();
			});			
        },
        error: function () {
			set_data.error = obj.error;
			set_data.error_msg = obj.error_msg;
			auditoria_send({"proceso":"ver_pago_detalle","data":set_data});
        }
    });
}

function sec_cobranzas_estados_de_cuenta_descargar_eventos(){
	cargar_locales_enviar();
	$(".modal#cobranzas_estados_de_cuenta_enviar").off("click",".btn_descargar").on("click", ".btn_descargar" ,function(){
		var checkeados = $('#table_locales').DataTable().$('input[type="checkbox"]:checked')
		var enviar=true;
		mensaje=[];
		if($("#periodo_liquidacion_select").val()==""){
			mensaje.push("Seleccione Periodo");
			enviar=false;
		}
		if(checkeados.length==0){
			mensaje.push("Seleccione locales");
			enviar=false;
		}
		if(!enviar){
			swal({
				 	html:true,
			 		title: 'Importante',
			 		text:mensaje.join("<br>"),
			 		type: "warning",
			 		timer: 1500,
			 	}, function(){
			 		swal.close();
			 		loading();
			}); 
			return false;
		}
		locales = [];
		checkeados.each(function(i,e){
			var local_id = $(e).attr("data-local_id");
			locales.push( local_id );
		})
		$(locales).each(function(i,e){
			let local = [e] ; 
			set_data = {
				opt : "vista_previa_estado_cuenta",
				periodo : $("#periodo_liquidacion_select option:selected").text(),
				periodo_id : $("#periodo_liquidacion_select").val(),
				locales : local
			};
			$.ajax({
				url: "/sys/get_cobranzas_estados_de_cuenta.php",
				data : set_data,
				type: 'POST',
				beforeSend: function() {
					loading(true);
				},
				complete:function(){
					loading();
				},
				success:function(resp){
					response = JSON.parse(resp);
					loading();
					if(response.errores.length>0){
						var msg = "<p style='text-align:left'><strong>Errores:</strong><br>";
						$(response.errores).each(function(i,e){
							msg += "- "+e+"<br>";
						})
						msg += "</p>";
						swal({
							html : true,
				 			title: "!Error",
							text : msg,
							type : "info",
							timer : 12000,
						}, function(){
							swal.close();
							loading();
						}); 
						return false;
					}
					let file_name = response.pdfs[0].archivo;
					var anchor = document.createElement('a');
					anchor.href = response.pdfs[0].archivo;
					anchor.target = '_blank';
					anchor.download = file_name.substr(file_name.lastIndexOf("/") + 1 );
					anchor.click();
					if( i == locales.length - 1 )
					{
						swal({
							html:true,
							title: 'Descarga!',
							text: "Archivos Descargados",
							type: "success",
							timer: 2500,
						}, function(){
							swal.close();
							loading();
						});
					}


				}
			})
		})
	})
}

function sec_cobranzas_estados_de_cuenta_vista_previa_eventos(){
	cargar_locales_enviar();
	$(".modal#cobranzas_estados_de_cuenta_enviar").off("click").on("click", ".btn_vista_previa" ,function(){
		var checkeados = $('#table_locales').DataTable().$('input[type="checkbox"]:checked')
		var enviar=true;
		mensaje=[];
		if($("#periodo_liquidacion_select").val()==""){
			mensaje.push("Seleccione Periodo");
			enviar=false;
		}
		if(checkeados.length==0){
			mensaje.push("Seleccione locales");
			enviar=false;
		}
		if(!enviar){
			swal({
				 	html:true,
			 		title: 'Importante',
			 		text:mensaje.join("<br>"),
			 		type: "warning",
			 		timer: 1500,
			 	}, function(){
			 		swal.close();
			 		loading();
			}); 
			return false;
		}
		locales = [];
		checkeados.each(function(i,e){
			var local_id = $(e).attr("data-local_id");
			locales.push( local_id );
		})

		if( locales.length > 1 )
		{			
			swal({
				html:true,
				title: 'Importante',
				text: "Sólo se permite visualizar 1 imagen",
				type: "warning",
				timer: 3000,
			}, function(){
				swal.close();
				loading();
			});
			return false;
		}

		$(locales).each(function(i,e){
			let local = [e] ; 
			set_data = {
				opt : "vista_previa_estado_cuenta",
				periodo : $("#periodo_liquidacion_select option:selected").text(),
				periodo_id : $("#periodo_liquidacion_select").val(),
				locales : local
			};
			$.ajax({
				url: "/sys/get_cobranzas_estados_de_cuenta.php",
				data : set_data,
				type: 'POST',
				beforeSend: function() {
					loading(true);
				},
				complete:function(){
					loading();
				},
				success:function(resp){
					response = JSON.parse(resp);
					loading();
					if(response.errores.length>0){
						var msg = "<p style='text-align:left'><strong>Errores:</strong><br>";
						$(response.errores).each(function(i,e){
							msg += "- "+e+"<br>";
						})
						msg += "</p>";
						swal({
							html : true,
				 			title: "!Error",
							text : msg,
							type : "info",
							timer : 12000,
						}, function(){
							swal.close();
							loading();
						}); 
						return false;
					}					
					const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
					if (isMobile /*&& locales.length == 1*/) {
						file_url = window.location.host + response.pdfs[0].archivo ; 
							url = "https://docs.google.com/gview?embedded=true&url=" + file_url;
						window.open(url,"_blank");
					}
					else{
						window.open(response.pdfs[0].archivo, '_blank');
					}
				

				}
			})
		})
	})

}
function sec_cobranzas_estados_de_cuenta_enviar_eventos(){
	$("#cobranzas_estados_de_cuenta_enviar").on("shown.bs.modal",function(){
		$("#periodo_liquidacion_select").val($("#periodo_liquidacion_select option[value!='']:first").attr("value")).change();/*to select 1st*/
		//$("#cobranzas_estados_de_cuenta_enviar").off("click").on("click","#table_locales tbody tr td:not(:nth-child(1))" ,function(e){ $(this).closest("tr").find(":checkbox").click() })
	});
	$(".btn_enviar_periodo").off("click").on("click",function(){
		var checkeados = $('#table_locales').DataTable().$('input[type="checkbox"]:checked')
		var enviar=true;
		mensaje=[];
		if($("#periodo_liquidacion_select").val()==""){
			mensaje.push("Seleccione Periodo");
			enviar=false;
		}
		if(checkeados.length==0){
			mensaje.push("Seleccione locales");
			enviar=false;
		}
		if(!enviar){
			swal({
				 	html:true,
			 		title: 'Error!',
			 		text:mensaje.join("<br>"),
			 		type: "error",
			 		timer: 1500,
			 	}, function(){
			 		swal.close();
			 		loading();
			}); 
			return false;
		}
		locales=[];
		checkeados.each(function(i,e){
			var local_id=$(e).attr("data-local_id");
			locales.push(local_id);
		})
		set_data={
			opt:"enviar_estado_cuenta",
			periodo:$("#periodo_liquidacion_select option:selected").text(),
			periodo_id:$("#periodo_liquidacion_select").val(),
			locales:locales
		};

		/*
		set_data.chk_test = 0;
		if( $('#checkbox_enviar_periodo_test').is(':checked') ) {
			set_data.chk_test = 1;
		}
		*/

		$.ajax({
	        url: "/sys/get_cobranzas_estados_de_cuenta.php",
	        data:set_data,
	        type: 'POST',
	         beforeSend: function() {
	         	loading(true);
	         },
	         complete:function(){
	         	loading();
	         },
	         success:function(resp){
         		response=JSON.parse(resp);
				loading();
				if(response.errores.length>0){
					var msg="<p style='text-align:left'><strong>Errores:</strong><br>";
					$(response.errores).each(function(i,e){
						msg+="- "+e+"<br>";
					})
					msg+="</p>";
					swal({
						html:true,
				 		title: response.mensaje,
				 		text:msg,
				 		type: "info",
				 		timer: 12000,
				 	}, function(){
				 		swal.close();
				 		loading();
					}); 
					return false;
				}
				swal({
						html:true,
				 		title: response.mensaje,
				 		text:'',
				 		type: "success",
				 		timer: 12000,
				 	}, function(){
				 		swal.close();
				 		loading();
				}); 

	         }
    	 })
	})

}

function sec_cobranzas_estados_de_cuenta_gestionar_modal_events(modal){
	console.log("sec_cobranzas_estados_de_cuenta_gestionar_modal_events");
	$("#cobranzas_gestionar_estados_de_cuenta_modal .generar_cerrar_btn")
		.off()
		.click(function(event) {
			sec_cobranzas_estados_de_cuenta_gestionar_modal("hide");
		});
	
	$("#gest_locales_list_holder .move_btn")
		.off()
		.click(function(event) {
			$(".itm_local").removeClass("bg-primary");
			$(this).closest('.itm_local').addClass('bg-primary');
			var local_id = $(this).closest('.itm_local').data("local_id");
			scedcg_get_periodos(local_id);
		});
	$("#gest_periodos_list_holder .move_btn")
		.off()
		.click(function(event) {
			// console.log("#gest_periodos_list_holder .move_btn:clickkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkk");
			var this_btn = $(this);
			console.log(this_btn);
			$(".itm_periodo").removeClass("bg-primary");
			$(this).closest('.itm_periodo').addClass('bg-primary');
			var get_data = $(this).closest('.itm_periodo').data();
			// var get_data = {};
				// get_data.local_id = local_id;
			scedcg_get_eecc(get_data,this_btn);
		});


	$(".single_searcher")
		.each(function(index, el) {
			var search_input_id = $(el).attr("id");
			var search_input_localstorage = localStorage.getItem("single_searcher_"+search_input_id);
			console.log(search_input_localstorage);
			var search_input = $(el);
				search_input.val(search_input_localstorage);
			var holder_id = $(el).data("holder_id");
			var item_class = $(el).data("item_class");
			var item_where = $(el).data("where");
			var search_clear_btn = $(el).parent().find('.search_clear_btn');
				search_clear_btn
					.off()
					.click(function(event) {
						search_input.val("").change().focus();
					});
			search_input
				.off()
				.on('change keyup paste click', function () {
					var searchTerm = force_plain_text(search_input.val());
					localStorage.setItem("single_searcher_"+search_input_id,searchTerm);
					$("#"+holder_id+" ."+item_class).each(function(index, itm) {
						$(itm).stop().hide();
						var item_text = force_plain_text($(itm).find("."+item_where).html());
						var n = item_text.indexOf(searchTerm);
						if(n >= 0){
							$(itm).stop().show();
						}
					});
				})
				.click()
				;
		});



	$(".add_deuda_modal_btn")
		.off()
		.click(function(event) {
			console.log("add_deuda_modal_btn:click");
			cobranzas_add_deuda_modal(true,$(this));
		});
	$("#cobranzas_generar_estados_de_cuenta_add_deuda_manual_modal .add_deuda_btn")
		.off()
		.click(function(event) {
			cobranzas_add_deuda();
		});
	$("#cobranzas_generar_estados_de_cuenta_add_deuda_manual_modal .add_deuda_cerrar_btn")
		.off()
		.click(function(event) {
			cobranzas_add_deuda_modal();
		});


	$(".add_pago_modal_btn")
		.off()
		.click(function(event) {
			console.log("add_deuda_modal_btn:click");
			cobranzas_add_pago_modal(true,$(this));
		});



	// TESSSSTING
	if(modal=="first"){
		// $("#gest_locales_list_holder .itm_local[data-local_id=170] .move_btn").click();
	}
	if(modal=="second"){
		// $("#gest_periodos_list_holder .itm_periodo[data-periodo_rango='12-18'] .move_btn").click();
	}
	if(modal=="third"){
		// $(".add_deuda_modal_btn").first().click();
		// $(".add_pago_modal_btn").first().click();
	}
	// FIN TESSSSTING
}
function scedcg_get_periodos(local_id){
	console.log("scedcg_get_periodos");
	console.log(local_id);
	var get_data = {};
		get_data.local_id = local_id;
	loading(true);
	$.post('/sys/get_cobranzas_estados_de_cuenta.php', {
		"opt": 'cobranzas_load_local_periodos',
		"data":get_data
	}, function(r) {
		$("#gest_eecc_table_holder").empty();
		try{
			if(r=="0"){
				resp=JSON.parse(r);
				loading();
				swal({
				 		title: 'Error!',
				 		text:resp.mensaje,
				 		type: "info",
				 		timer: 1000,
				 	}, function(){
				 		swal.close();
				 		loading();
				}); 
				return;
			}
			loading();
			auditoria_send({"proceso":"cobranzas_load_local_periodos","data":get_data});

			$("#gest_periodos_list_holder").html(r);
			sec_cobranzas_estados_de_cuenta_gestionar_modal_events("second");
			$("#gest_periodos_list_holder_search").focus();
			loading();
		}catch(err){
			loading();
			ajax_error(true,r,err);//opt,response,catch-error
		}
		// auditoria_send({"proceso":"cobranzas_ver_eecc","data":api_data});
	});
}
function scedcg_get_eecc(get_data,btn){
	console.log("scedcg_get_eecc");
	console.log(get_data);
	loading(true);
	$.post('/sys/get_cobranzas_estados_de_cuenta.php', {
		"opt": 'cobranzas_load_eecc',
		"data":get_data
	}, function(r) {
		try{
			loading();
			$("#gest_eecc_table_holder").html(r);
			auditoria_send({"proceso":"cobranzas_load_eecc","data":get_data});
			sec_cobranzas_estados_de_cuenta_gestionar_modal_events("third");
			loading();
		}catch(err){
			loading();
			ajax_error(true,r,err);//opt,response,catch-error
		}
		// auditoria_send({"proceso":"cobranzas_ver_eecc","data":api_data});
	});
}

function cobranzas_add_deuda_modal(opt,btn){
	console.log("cobranzas_add_deuda_modal");
	if(opt){
		var btn_data = btn.data();
			btn_data.tipo_id = 4;
		$.each(btn_data, function(index, val) {
			$("#form_add_deuda .add_col[data-col="+index+"]").val(val);
		});
		console.log(btn_data);
		$("#cobranzas_generar_estados_de_cuenta_add_deuda_manual_modal").modal("show"); // z-index:1020
		// $(".add_deuda_btn")
		// 	.off()
		// 	.click(function(event) {
		// 		cobranzas_add_deuda();
		// 	});
		// $(".add_deuda_btn").first().click();
	}else{
		$("#cobranzas_generar_estados_de_cuenta_add_deuda_manual_modal").modal("hide");
		$("#form_add_deuda .add_col").val("");
	}
	sec_cobranzas_estados_de_cuenta_gestionar_modal_events();
}
function cobranzas_add_deuda(){
	console.log("cobranzas_add_deuda");
	var set_data = {};
	$("#form_add_deuda .add_col").each(function(index, el) {
		var col = $(el).data("col");
		var val = $(el).val();
		if(val==""){
			$(el).parents(".form-group").addClass('has-error').removeClass('has-success');
			$(el).focus();
			set_data=false;
			return false;
		}else{
			$(el).parents(".form-group").removeClass('has-error').addClass('has-success');
		}
		set_data[col]=val;
	});
	console.log(set_data);
	if(set_data){
		loading(true);
		$.post('/sys/set_cobranzas.php', {
			"opt": 'cobranzas_add_deuda',
			"data":set_data
		}, function(r) {
			try{
				loading();
				var resp=JSON.parse(r);
            	auditoria_send({ "proceso": "cobranzas_add_deuda", "data": set_data });
				console.log(resp);
				if(resp.error){
					swal({
						title: 'Error!',
						text:resp.error,
						type: "warning",
						timer: 2500,
					}, function(){
						swal.close();
						loading();
					});
					return false;
				}

				cobranzas_add_deuda_modal();
				scedcg_get_eecc(set_data)
				// cobranzas_load_locales_list(parseInt(set_data.local_id));
			}catch(err){
				loading();
				ajax_error(true,r,err);//opt,response,catch-error
			}
			// auditoria_send({"proceso":"cobranzas_add_deuda","data":set_data});
		});
	}
}

function cobranzas_add_pago_modal(opt,btn){
	console.log("cobranzas_add_pago_modal");
	if(opt){
		var btn_data = btn.data();
		$.each(btn_data, function(index, val) {
			$("#form_add_pago .add_col[data-col="+index+"]").val(val);
		});
		cobranzas_add_pago_load_deuda(btn,btn_data);
		$("#cobranzas_estados_de_cuenta_add_pago_modal").modal("show");
		$("#cobranzas_estados_de_cuenta_add_pago_modal .modal-title").html("Agregar pago - Local: ["+btn_data.local_id+"] "+btn_data.local_nombre+" -  Periodo:  "+btn_data.periodo);
		console.log("________________________________________________________________________________");
		console.log(btn_data);
	}else{
		$("#cobranzas_estados_de_cuenta_add_pago_modal").modal("hide");
		$("#form_add_pago .add_col").val("");
		sec_cobranzas_estados_de_cuenta_gestionar_modal_events();
	}
}
function cobranzas_add_pago_load_deuda(btn,get_data){
	console.log("cobranzas_add_pago_load_deuda");
	console.log(get_data);
	loading(true);
	$.post('/sys/get_cobranzas_estados_de_cuenta.php', {
		"opt": 'cobranzas_add_pago_load_deuda',
		"data":get_data
	}, function(r) {
		try{
			loading();
			// console.log(r);
			$("#deuda_holder").html(r);
			cobranzas_add_pago_modal_events("modal",btn);
			loading();
		}catch(err){
			loading();
			ajax_error(true,r,err);//opt,response,catch-error
		}
		// auditoria_send({"proceso":"cobranzas_ver_eecc","data":api_data});
	});
}
function cobranzas_add_pago_modal_events(opt,btn){
	console.log("cobranzas_add_pago_modal_events");
	console.log(opt);
	console.log(btn);

	$("#vista_previa_img").off("click").on("click",function(){
	  	var thefile =  $("#voucher")[0].files[0];

		if($("#voucher").val()==""){
			swal({
					 		title: 'Error!',
					 		text:'Ingresar Imagen',
					 		type: "error",
					 		timer: 1500,
					 	}, function(){
					 		swal.close();
					}); 
			return false;
		}
	  	var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];
        if ($.inArray($("#voucher").val().split('.').pop().toLowerCase(), fileExtension) != -1) {
		  	if(typeof $("#voucher")[0].files[0] !="undefined"){
				var reader  = new FileReader();
				reader.onload = function() {
				   	var modal = document.getElementById("vista_previa_modal");
					var modalImg = document.getElementById("img01");
					modal.style.display = "block";
					modalImg.src=reader.result
					$("#vista_previa_modal").modal("show");
				}
				reader.readAsDataURL(thefile);
			}
        }
        else{
        	swal({
				 		title: 'Error!',
				 		text:'Archivo debe tener extensión .jpg .jpeg .png ',
				 		type: "error",
				 		timer: 1500,
				 	}, function(){
				 		swal.close();
				}); 
        }

	});

	$("#vista_previa_modal").off("shown.bs.modal").on("shown.bs.modal",function(){		
		$("#img01").imgViewer2();
	});
	$("#vista_previa_modal").off("hide.bs.modal").on("hide.bs.modal",function(){
		$("#img01").imgViewer2("destroy");
	});

	$("#cobranzas_estados_de_cuenta_add_pago_modal .add_pago_btn")
		.off()
		.click(function(event) {
			cobranzas_add_pago();
		});
	$("#cobranzas_estados_de_cuenta_add_pago_modal .cerrar_btn")
		.off()
		.click(function(event) {
			cobranzas_add_pago_modal();
		});
	$("#pago_source .checkbox_me")
		.off()
		.click(function(event) {
			var checkbox = $(this).find("input[type=checkbox]");
			// console.log(checkbox);
			var checkbox_icon = $(this).find(".checkbox_icon");
			if(checkbox.is(':checked')){
				checkbox.prop('checked', false);
				checkbox_icon.removeClass('glyphicon-check').addClass('glyphicon-unchecked');
				checkbox.closest(".trans_item").removeClass('bg-success');
			}else{
				checkbox.prop('checked', true);
				checkbox_icon.removeClass('glyphicon-unchecked').addClass('glyphicon-check');
				checkbox.closest(".trans_item").addClass('bg-success');
			}
			cobranzas_add_pago_modal_sum_trans();
		});
	$("#cobranzas_estados_de_cuenta_add_pago_modal .reload_btn")
		.off()
		.click(function(event) {
			cobranzas_add_pago_modal(opt,btn);
		});
	$("#cobranzas_estados_de_cuenta_add_pago_modal select[data-col=pago_tipo_id]")
		.off()
		.change(function(event) {
			// var sel = $(this);
			var sel_val = $(this).val();
			localStorage.setItem("ls_cobranzas_estados_de_cuenta_add_pago_tipo_id",sel_val);
			// console.log(sel.val());
			var get_data = jQuery.extend({}, btn.data());
				get_data.pago_tipo_id = sel_val;
			loading(true);
			$.post('/sys/get_cobranzas_estados_de_cuenta.php', {
				"opt": 'cobranzas_load_pago_source',
				"data":get_data
			}, function(r) {
				try{
					$("#pago_source").html(r);
					$(".repartir_monto").validar_numerico();
					cobranzas_add_pago_modal_events("post",btn);
					// $("#pago_source .checkbox_me").first().click();
					// $("#pago_source .checkbox_me").last().click();
					// sec_cobranzas_estados_de_cuenta_gestionar_modal_events("third");
					loading();
					$("input:visible[data-col='abono']").off("keypress").on("keypress",function(e){
						if(e.which == 13) {
							$("#cobranzas_estados_de_cuenta_add_pago_modal .add_pago_repartir_btn").click();
						}
					})
					$("input:visible[data-col='nro_operacion']").validar_numerico_decimales({decimales : 0});
					$("input:visible[data-col='nro_operacion']").focus();

				}catch(err){
					loading();
					ajax_error(true,r,err);//opt,response,catch-error
				}
				// auditoria_send({"proceso":"cobranzas_ver_eecc","data":api_data});
			});
		});
	$("#cobranzas_estados_de_cuenta_add_pago_modal .add_pago_repartir_btn")
		.off()
		.click(function(event) {
			cobranzas_add_pago_repartir();
		});
	$("#deuda_holder .deuda_abonar input")
		.off()
		.on('change keyup paste click',function(event){
			cobranzas_add_pago_repartir_calc($(this));	
		});
	if(opt=="modal"){
		var ls_cobranzas_estados_de_cuenta_add_pago_tipo_id = localStorage.getItem("ls_cobranzas_estados_de_cuenta_add_pago_tipo_id");
		if(ls_cobranzas_estados_de_cuenta_add_pago_tipo_id){
			$("#cobranzas_estados_de_cuenta_add_pago_modal select[data-col=pago_tipo_id]")
				.val(ls_cobranzas_estados_de_cuenta_add_pago_tipo_id)
				.change()
				;
		}
	}
}
function cobranzas_add_pago_modal_sum_trans(){
	console.log("cobranzas_add_pago_modal_sum_trans");
	var abono = 0;
	$("#cobranzas_estados_de_cuenta_add_pago_modal #pago_source .trans_item .checkbox_me input").each(function(index, el) {
		if($(el).is(':checked')){
			var val = Number($(el).data("val"));
			abono+=val;
			console.log(val);
		}
	});
	$("#cobranzas_estados_de_cuenta_add_pago_modal #pago_source .repartir_monto").val(abono.toFixed(2));
	cobranzas_add_pago_repartir();
}
function cobranzas_add_pago_repartir(){
	console.log("cobranzas_add_pago_repartir");
	var repartir_monto = Number($(".repartir_monto").val());
	// if(repartir_monto>0){
		console.log(repartir_monto);
		if($("#deuda_holder .deuda_repartir").length==0){
			swal({
				 		title: 'No hay deudas!',
				 		text:'',
				 		type: "info",
				 		timer: 1500,
				 	}, function(){
				 		swal.close();
				 		loading();
				}); 
			return;
		}
		var negativo_valor = 0;
		$("#deuda_holder .deuda_repartir").each(function(index2, el2) {
			if(Number($(".deuda_monto",$(el2)).text()) < 0){
				negativo_valor += Number($(".deuda_monto",$(el2)).text());
			}
		})
		repartir_monto -= negativo_valor;
		$("#deuda_holder .deuda_repartir").each(function(index, el) {
			var deuda_monto = Number($(el).find(".deuda_monto").data("val"));
			var deuda_abonar = 0;
			if(deuda_monto>0){
				if(repartir_monto>0){
					if(deuda_monto >= repartir_monto){
						deuda_abonar = repartir_monto;
					}else{
						deuda_abonar = deuda_monto;
					}		
					repartir_monto = repartir_monto - deuda_abonar;		
				}
			}else{
				deuda_abonar = deuda_monto;
			}
			deuda_abonar = Number(deuda_abonar).toFixed(2);
			var deuda_saldo = Number(deuda_monto - deuda_abonar).toFixed(2);
			$(el).find(".deuda_abonar input").val(deuda_abonar);
			$(el).find(".deuda_saldo").html(deuda_saldo);
			cobranzas_add_pago_repartir_calc();
		});
		var excedente= repartir_monto;
		$(".total_excedente").text(excedente.toFixed(2));
	// }else{
	// 	swal({
	// 		title: 'Error!',
	// 		text:'Ingrese un monto',
	// 		type: "info",
	// 		timer: 1000,
	// 	}, function(){
	// 		swal.close();
	// 		loading();
	// 	}); 
	// }
}
function cobranzas_add_pago_repartir_calc(input){
	if(input){
		var parent = $(input).closest('.deuda_repartir');
		var deuda_monto = parent.find(".deuda_monto").data("val");
		var val = $(input).val();
		var deuda_saldo = Number(0).toFixed(2);
		if($.isNumeric(val) || val == '-'){
			if($.isNumeric(val)){
				deuda_saldo = Number( deuda_monto - val).toFixed(2);
			}
		}else{
			$(input).val(val.slice(0, -1));
		}
		parent.find(".deuda_saldo").html(deuda_saldo);
	}
	var total_abonar = 0;
	$("#deudas_holder_table .deuda_abonar input").each(function(index, el) {
		total_abonar = total_abonar + Number($(this).val());
	});
	$("#deudas_holder_table .total_abonar").html(total_abonar.toFixed(2));

	var total_deuda= $("#deudas_holder_table .total_deuda").text();
	if(total_abonar >total_deuda){
		//$("#deudas_holder_table .total_abonar").html(total_deuda);
		$("#deudas_holder_table .total_excedente").html((total_abonar-total_deuda).toFixed(2));
	}
	else{
		$("#deudas_holder_table .total_excedente").html("0.00");

	}

	var total_saldo = 0;
	$("#deudas_holder_table .deuda_saldo").each(function(index, el) {
		total_saldo = total_saldo + Number($(el).html());
	});
	$("#deudas_holder_table .total_saldo").html(total_saldo.toFixed(2));

	//$("input[data-col='abono']").val(total_abonar.toFixed(2));
}
function cobranzas_add_pago(data){
	console.log("cobranzas_add_pago");

	var continuar = true;

	var monto_input=$("#cobranzas_estados_de_cuenta_add_pago_modal #pago_source .repartir_monto");
	var repartir_monto = Number(monto_input.val());
	console.log(repartir_monto);
	if(repartir_monto==0){
		monto_input.parents(".form-group").addClass('has-error').removeClass('has-success');			
		swal({
				title: 'Error!',
				text:"Ingrese Abono",
				type: "warning",
				timer: 2500,
			}, function(){
				swal.close();
				loading();
				setTimeout(function(){monto_input.focus()},500);
		});
		return false
	}
	var total_abonar = 0;
	$("#deudas_holder_table .deuda_abonar input").each(function(index, el) {
		total_abonar = total_abonar + Number($(this).val());
	});
	if(repartir_monto<0){
		if(total_abonar>=repartir_monto){
			// alert("1");
		}else{
			continuar=false;
			swal({
				title: 'Error!',
				text:'El monto total a abonar no puede ser menor al monto ingresado',
				type: "warning",
				timer: 2000,
			}, function(){
				swal.close();
				loading();
			});
		}			
	}else{
		if(total_abonar<=repartir_monto){
			// alert("2");
		}else{
			//alert("3");
		}
	}
	console.log(total_abonar);
	if(continuar){
		console.log("continuar");

		var set_data = {};
			set_data.pagos = {};
			set_data.trans = [];

		col="";
		$("#form_add_pago .add_col").each(function(index, el) {
			col = $(el).data("col");
			var val = $(el).val();
			if(val==""){
				$(el).parents(".form-group").addClass('has-error').removeClass('has-success');
				$(el).focus();
				set_data=false;
				return false;
			}else{
				$(el).parents(".form-group").removeClass('has-error').addClass('has-success');
			}
			set_data[col]=val;
		});
		if(!set_data){
			var label= $("div.form-group.has-error:visible label").eq(0).text();
			mensaje ="Ingrese "+ label;
			swal({
					title: mensaje,
					text:'',
					type: "warning",
					timer: 2000,
				}, function(){
					swal.close();
				});
			return false;
		}
		abonar_suma=0;					
		$(".deuda_abonar input").each(function(i,e){
			abonar_suma+=parseFloat(($(e).val())?$(e).val():0);
		})
		abonar_suma = parseFloat(abonar_suma.toFixed(2));

		if(isNaN(abonar_suma) ){
			swal({
					title: "Error!",
					text:"Ingrese valores numéricos",
					type: "warning",
					timer: 2500,
				}, function(){
					swal.close();
				});
			return false;
		}

		if(abonar_suma <= 0 ){
			swal({
					title: "Error!",
					text:"Ingrese Pagos!",
					type: "warning",
					timer: 2500,
				}, function(){
					swal.close();
				});
			return false;
		}
		var total_abono_excedente = (abonar_suma+ parseFloat($(".total_excedente").text()));
		total_abono_excedente = parseFloat(total_abono_excedente.toFixed(2));
		//var total_abono_excedente = abonar_suma;
		if(total_abono_excedente != parseFloat($("input[data-col='abono']").val())){
			$(".repartir_monto").parents(".form-group").addClass('has-error').removeClass('has-success');			
			swal({
					title: "Error!",
					text:"Abono Total "+parseFloat(abonar_suma).toFixed(2)+"  es diferente a Abono",
					type: "warning",
					timer: 2500,
				}, function(){
					swal.close();
				});
			return false;
		}
		else{
			var saldoafavor=($("input[data-col='abono']").val())- abonar_suma;
			$(".total_excedente").text(saldoafavor.toFixed(2));
		}

		$("#deuda_holder .deuda_repartir").each(function(index, el) {
			var pago = {};
				pago.deuda_tipo_id = $(el).data("deuda_tipo_id");
				pago.deuda_abonar = Number($(el).find(".deuda_abonar input").val());
			if(pago.deuda_abonar || pago.deuda_abonar > 0){
				set_data.pagos[index]=pago;
			}
		});
		///excedente
			var pago = {};
				pago.deuda_tipo_id = null;
				pago.deuda_abonar = Number($("#deudas_holder_table .total_excedente").text());
			if(pago.deuda_abonar || pago.deuda_abonar > 0){
				set_data.pagos[Object.keys(set_data.pagos).lenth] = pago;
			}
		////////////
		$("#cobranzas_estados_de_cuenta_add_pago_modal #pago_source .trans_item .checkbox_me input").each(function(index, el) {
			if($(el).is(':checked')){
				var t = $(el).data("id");
				set_data.trans.push(t);
			}
		});

		console.log(set_data);
		// set_data=false;
		// if(set_data){
		loading(true);
		var form_data = new FormData();
		    form_data.append("opt","cobranzas_add_pago");
		    form_data.append("data",JSON.stringify(set_data));
		//if($("[data-col=pago_tipo_id]").val()==1){//transaccion bancaria
		if($("#voucher")[0].files.length>0){
   			form_data.append("voucher",$("#voucher")[0].files[0]);
   			form_data.append("voucher_name",$("#voucher")[0].files[0].name);
		}
		//}
		$.ajax({
            type:'POST',
            url: '/sys/set_cobranzas.php',
            data:form_data,
            cache:false,
            contentType: false,
            processData: false,
            success:function(r){
					try{
						var ret = jQuery.parseJSON(r);		
						loading();
						if(ret.error){
					    	auditoria_send({"proceso":"cobranzas_add_pago_error","data":set_data});
							swal({
								title: 'Error!',
								html: true,
								text:ret.error,
								type: "warning",
								timer: 2500,
							}, function(){
								swal.close();
								loading();
							});
						}else{
							console.log(ret);
					    	auditoria_send({"proceso":"cobranzas_add_pago","data":set_data});
							swal({
								title: "Listo!",
								text: "Pago agregado.",
								type: "success",
								timer: 1000,
								closeOnConfirm: false
							},
							function(){							
								swal.close();
								cobranzas_add_pago_modal();
								console.log(set_data);
								$("#gest_periodos_list_holder .itm_periodo[data-btn_id='btn_unique_id_"+set_data.local_id+"_"+set_data.periodo_year+"_"+set_data.periodo_mes+"_"+set_data.periodo_rango+"'] .move_btn").click();
							});
							loading();
						}
					}catch(err){
						loading();
						ajax_error(true,r,err);//opt,response,catch-error
					}
		    },
            error: function(data){
				auditoria_send({"proceso":"cobranzas_add_pago_error","data":set_data});
				loading();
                
            }
  		 });
			/*$.post('/sys/set_cobranzas.php', {
				"opt": 'cobranzas_add_pago',
				"data":set_data
			}, function(r) {
				try{
					var ret = jQuery.parseJSON(r);
					console.log(ret);
					loading();

					if(ret.error){
						swal({
							title: 'Error!',
							text:ret.error,
							type: "warning",
							timer: 2000,
						}, function(){
							swal.close();
							loading();
						});
					}else{
						console.log(ret);
						swal({
							title: "Listo!",
							text: "Pago agregado.",
							type: "success",
							timer: 1000,
							closeOnConfirm: false
						},
						function(){							
							swal.close();
							cobranzas_add_pago_modal();
							console.log(set_data);
							$("#gest_periodos_list_holder .itm_periodo[data-btn_id='btn_unique_id_"+set_data.local_id+"_"+set_data.periodo_year+"_"+set_data.periodo_mes+"_"+set_data.periodo_rango+"'] .move_btn").click();
							// eecc_add_pago_modal();
							// cobranzas_ver_eecc(data);
						});
						loading();
						// local_add_deuda();
						// cobranzas_load_locales_list(parseInt(set_data.local_id));
					}
				}catch(err){
					loading();
					ajax_error(true,r,err);//opt,response,catch-error
				}
				// auditoria_send({"proceso":"cobranzas_add_pago","data":set_data});
			});*/
		
	}
}
function cargar_locales_enviar(){
	set_data={opt:"lista_locales_enviar"};	
    $.ajax({
        url: "/sys/get_cobranzas_estados_de_cuenta.php",
        data:set_data,
        type: 'POST',
         beforeSend: function() {
         	loading(true);
         },
         complete:function(){
         	loading();
         },
        success: function (response) {//  alert(datat)
            var resp=JSON.parse(response);
            var data=resp.lista;
			set_data.curr_login = resp.curr_login;
   			table_locales=$('#table_locales').DataTable( {
                "bDestroy": true,
	    	   		"language": {
	                    "search": "Buscar:",
	                    "lengthMenu": "Mostrar _MENU_ registros por página",
	                    "zeroRecords": "No se encontraron registros",
	                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
	                    "infoEmpty": "No hay registros",
	                    "infoFiltered": "(filtrado de _MAX_ total registros)",
	                    "paginate": {
	                        "first": "Primero",
	                        "last": "Último",
	                        "next": "Siguiente",
	                        "previous": "Anterior"
	                    },
	                    sProcessing: "Procesando..."
	                },
				sDom: "<'row'<'col-sm-4 div_checkeados'l ><'col-sm-4'><'col-sm-4'f>>"+
					  "<'row'<'col-sm-12 div_btn_vista_previa'>>"+
					  "<'row'<'col-sm-12'tr>>" +
					  "<'row'<'col-sm-5'i><'col-sm-7'p>>"
					  ,
    		    "initComplete": function (settings, json) {
					$(".div_btn_vista_previa").html($('<button class="btn btn-info btn-sm btn-rounded btn_descargar pull-right mt-1">Descargar</button>&nbsp;&nbsp;'));
					$(".div_btn_vista_previa").append(" ").append($('<button class="btn btn-warning btn-sm btn-rounded btn_vista_previa pull-right mt-1 mr-1">Vista Previa</button>'));

    		    	$(".div_checkeados").html("<h6 id='text_checkeados' ></h6>");
    		    	$(".seleccionar_todo").on("click",function(e) {
    		    		 e.stopPropagation();
					    if(this.checked) {
					    	//$("#table_locales tbody :checkbox").prop("checked",true);
					    	var all_rows=$('#table_locales').DataTable().$(".checkbox_local", {"page": "all"})
					    	$(all_rows).each(function(ii,ee){
					    		$(ee).prop("checked",true);
					    	})

					    }
					    else{
					    	//$("#table_locales tbody :checkbox").prop("checked",false);
					    	var all_rows=$('#table_locales').DataTable().$(".checkbox_local", {"page": "all"})
					    	$(all_rows).each(function(ii,ee){
					    		$(ee).prop("checked",false);
					    	})
					    }
				       	//var checkeados = $('#table_locales').DataTable().$('input[type="checkbox"]:checked');
				    	var checkeados = $('#table_locales').DataTable().$(".checkbox_local:checked", {"page": "all"})
				    	var texto=checkeados.length==1?" Local Seleccionado":" Locales Seleccionados";
					    $("#text_checkeados").text(checkeados.length+texto);
					});

    		    	$(document).on('change', '.checkbox_local', function() {
				    	//var checkeados = $('#table_locales').DataTable().$('input[type="checkbox"]:checked');
				    	var checkeados = $('#table_locales').DataTable().$(".checkbox_local:checked", {"page": "all"})
				    	var texto=checkeados.length==1?" Local Seleccionado":" Locales Seleccionados";
					    $("#text_checkeados").text(checkeados.length+texto);	
					});
		      	},
                order:[],
                paging:true  ,              
				/*"aoColumnDefs": [
				        { 
				          "bSortable": false, 
				          "aTargets": [ 0 ] 
				         } 
				     ],*/
		       "columnDefs": [
	                {
	            		"targets": 0,
	                    "orderDataType": "dom-checkbox"
	                }],
    			columns: [
    				{
						title: '<input type="checkbox" class="seleccionar_todo ">',
						data: "operativo",
						"render":function(data,type,row){
							var id=row["id"];
							var html="";
							html="";
							html+="<input type='checkbox' class=' checkbox_local' data-local_id="+id+">";
							return html;
						},
					},
    				{
						title: "Local ID",
						data: "id",
					},
					{
						title: "Local Nombre",
						data: "local_nombre",
					}
					
				],
	      		data:data,
    		} );
        },
        error: function () {
    		set_data.error = obj.error;
			set_data.error_msg = obj.error_msg;
			auditoria_send({"proceso":"lista_locales_enviar","data":set_data});
        }
    });
	$.fn.dataTable.ext.order['dom-checkbox'] = function (settings, col) {
	    return this.api().column(col, { order: 'index' }).nodes().map(function (td, i) {
	        return $('input', td)[0].checked ? '1' : '0';
	    });
	}

}

function cargar_tabla_locales_server(){
	tablaserver = 
		$("#table_estados_de_cuenta")
		.on('order.dt', function () {
            $('table').css('width', '100%');
            $('.dataTables_scrollHeadInner').css('width', '100%');
            $('.dataTables_scrollFootInner').css('width', '100%');
        })
        .on('search.dt', function () {
            $('table').css('width', '100%');
            $('.dataTables_scrollHeadInner').css('width', '100%');
            $('.dataTables_scrollFootInner').css('width', '100%');
        })
        .on('page.dt', function () {
            $('table').css('width', '100%');
            $('.dataTables_scrollHeadInner').css('width', '100%');
            $('.dataTables_scrollFootInner').css('width', '100%');
        })
		.DataTable({
            "paging": true,
            "scrollX": true,
            "sScrollX": "100%",
            "bProcessing": true,
            'processing': true,
            "language": {
                "search": "Buscar:",
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron registros",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "No hay registros",
                "infoFiltered": "(filtrado de _MAX_ total registros)",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                sProcessing: "Procesando..."
            },
            "deferLoading": 0, // here
            "bDeferRender": false,
            "autoWidth": true,
            pageResize:true,
            "bAutoWidth": true,
            serverSide: true,
            "bDestroy": true,
            colReorder: true,
            "lengthMenu": [[10, 20,50, 200, -1], [10,20, 50, 200, "Todo"]],
            "iDisplayLength":20,
            "order": [[ 2, "asc" ]],
		    buttons: [
		        {
		            text: '<span class="glyphicon glyphicon-refresh"></span>',
		            action: function ( e, dt, node, config ) {
		                tablaserver.ajax.reload(null,false);
		            }
		        }
		    ],
            ajax: function (datat, callback, settings) {////AJAX DE CONSULTA
				datat.opt = "lista_locales_estado_de_cuenta_server";
				if (sub_sec_id=="detalle_estados_cuenta") {
					datat.tipo = $("#tipo_cobranzas_detalle_ec").val();
					datat.local_id = $("#local_cobranzas_detalle_ec").val();
					datat.periodo_inicio = $("#periodo_inicio_cobranzas_detalle_ec").val();
					datat.periodo_fin = $("#periodo_fin_cobranzas_detalle_ec").val();
				}
                ajaxrepitiendo = $.ajax({
                    //global : false,
                    url : "/sys/get_cobranzas_estados_de_cuenta.php",
                    type : 'POST',
                    data : datat,
                    beforeSend: function () {
                    	loading(true);
						tablaserver.columns.adjust();
                    },
                    complete: function () {
                    	loading();
						tablaserver.columns.adjust();
                    },
                    success: function (datos) {
                        var respuesta = JSON.parse(datos);
                        callback(respuesta);
                    },
                    error: function () {
                    	loading();
                    }
                });
            },
            columns:  [
				{
					title: "",
					data: "operativo",
					visible: false
				},
				{
					title: "Local ID",
					data: "id",
				},
				{
					title: "Local Nombre",
					data: "local_nombre",

				},
				{
					title: "Deuda",
					data: "debe",
					class:"text-right"

				},
				{
					title: "Pagado",
					data: "haber",
					class:"text-right"
				},
				{
					title:"Saldo",
					"render":function(data,type,row){
						var clase_td = "";
						if( row["local_deuda"] > 0){ 
							clase_td = "text-danger" ;
						}else{
						  clase_td = "text-success" ;
						}  
						var html =  "<div class='";
							html += clase_td;
							html += "'>";
							html += row["local_deuda"];
							html += '</div>';
						return html;
					},
					class:"text-right"
				},
				{
					title: "Ver",
					width:"150px",
					class:"text-right",
					"render": function(data,type,row){
						var id = row["id"];
						var html = "";
						html =  "";
						html += "<button class='btn btn-primary btn-xs ver_btn' data-local_id="+id+">";
						html += "Ver";
						html += '</button>';
						if (sub_sec_id=="detalle_estados_cuenta") {
							html +=  "  ";
							html += "<button class='btn btn-info btn-xs descargar_excel_detalle' data-local_id="+id+">";
							html += "Descargar Detallado";
							html += '</button>';
						}

						// Botón condicional
						if(sub_sec_id=="estados_de_cuenta"){
							if (row['diferencia_estado'] == '1') {
								html += "  ";
								html += "<a href='/?sec_id=cobranzas&sub_sec_id=estados_de_cuenta_diferencia&local_id=" + encodeURIComponent(id) + "' target='_blank' class='btn btn-warning btn-xs'>";
								html += "Diferencia";
								html += "</a>";
							}
						}

						return html;
					}
				},
				{
					title: "Diferencia",
					data: "diferencia",
					class:"text-right"
				}
			],
			sDom:"<'row'<'col-sm-4'l ><'col-sm-4 div_select'><'col-sm-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
		    "initComplete": function (settings, json) {
				var select_estado = $('<select name="estado_select" id="estado_select" class="form-control input-sm" style="width:60%"></select>')
					.append($('<option value = "" >Todos</option>'))
					.append($('<option value = 1  >Activos</option>'))
					.append($('<option value = 2  >Inactivos</option>'))
				$(".div_select").append(select_estado);

				filtrar_estado_ec_datatable(settings,json);
				$("input[type='search']:visible").focus();
            },
        });
	return tablaserver;
}

function descarga_tabla_locales(){
	console.log("Iniciando: Descarga de estados de cuenta");
	set_data={opt:"descarga_tabla_locales_excel"};
	loading(true);
	
	$.ajax({
		type: "POST",
		url: "sys/get_cobranzas_estados_de_cuenta.php",
		data: set_data,
		xhrFields: {
			responseType: "blob"
		},
	}).done(function(res) {
		loading(false);

		swal({
			title: '¡Éxito!',
			text: 'El archivo se ha descargado correctamente',
			type: "success"
		});
		
		let today = new Date();
		let dd = String(today.getDate()).padStart(2, '0');
		let mm = String(today.getMonth() + 1).padStart(2, '0');
		let yyyy = today.getFullYear();
		today = `${mm}_${dd}_${yyyy}`;
		var blob = res;
		var downloadUrl = URL.createObjectURL(blob);
		var a = document.createElement("a");
		a.href = downloadUrl;
		a.download = `estados_de_cuenta-${today}.xls`;
		a.target = '_blank';
		a.click();
	});
}

function descarga_total_detalle_estado_cuenta_descarga_excel(){
	console.log("Iniciando: Descarga total detalle de estados de cuenta");
	set_data={opt:"total_detalle_estado_cuenta_descarga_excel"};
	set_data.tipo = $("#tipo_cobranzas_detalle_ec").val();
	set_data.local_id = $("#local_cobranzas_detalle_ec").val();
	set_data.periodo_inicio = $("#periodo_inicio_cobranzas_detalle_ec").val();
	set_data.periodo_fin = $("#periodo_fin_cobranzas_detalle_ec").val();
	set_data.estado_select = $("#estado_select").val();
	loading(true);
	
	$.ajax({
		type: "POST",
		url: "sys/get_cobranzas_estados_de_cuenta.php",
		data: set_data,
		xhrFields: {
			responseType: "blob"
		},
	}).done(function(res) {
		loading(false);

		swal({
			title: '¡Éxito!',
			text: 'El archivo se ha descargado correctamente',
			type: "success"
		});
		
		let today = new Date();
		let dd = String(today.getDate()).padStart(2, '0');
		let mm = String(today.getMonth() + 1).padStart(2, '0');
		let yyyy = today.getFullYear();
		today = `${mm}_${dd}_${yyyy}`;
		var blob = res;
		var downloadUrl = URL.createObjectURL(blob);
		var a = document.createElement("a");
		a.href = downloadUrl;
		a.download = `total_detalle_estados_de_cuenta-${today}.xls`;
		a.target = '_blank';
		a.click();
	});
}

function filtrar_estado_ec_datatable(settings,json){
	var localStorage_estado_var="estado_select_sec_estados_de_cuenta";
	var datatable = settings.oInstance.api();

	$("#estado_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(0).search(val).draw();
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	//$("#estado_select").select2();

	if(localStorage.getItem(localStorage_estado_var) && localStorage.getItem(localStorage_estado_var)!="null"){
		setTimeout(function(){
			var valor = localStorage.getItem(localStorage_estado_var).split(',');
			$("#estado_select").val(valor).change();
		},200);
	}
	else{
		setTimeout(function(){
			$("#estado_select").val("").change();//nuevos,asignados
		},200);
	}
}

function cargar_tabla_locales(){
	datatable_locales=false;
	set_data={opt:"lista_locales_estado_de_cuenta"};	
    $.ajax({
        url: "/sys/get_cobranzas_estados_de_cuenta.php",
        data:set_data,
        type: 'POST',
         beforeSend: function() {
         	loading(true);
         },
         complete:function(){
         	loading();
         },
        success: function (response) {
            var resp=JSON.parse(response);
            var data=resp.lista;
			set_data.curr_login = resp.curr_login;
			auditoria_send({"proceso":"lista_locales_estado_de_cuenta","data":set_data});

   			datatable_locales=$('#table_estados_de_cuenta').DataTable( {
                "bDestroy": true,
	    	   		"language": {
	                    "search": "Buscar:",
	                    "lengthMenu": "Mostrar _MENU_ registros por página",
	                    "zeroRecords": "No se encontraron registros",
	                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
	                    "infoEmpty": "No hay registros",
	                    "infoFiltered": "(filtrado de _MAX_ total registros)",
	                    "paginate": {
	                        "first": "Primero",
	                        "last": "Último",
	                        "next": "Siguiente",
	                        "previous": "Anterior"
	                    },
	                    sProcessing: "Procesando..."
	                },
				sDom:"<'row'<'col-sm-4 div_select'l ><'col-sm-4'><'col-sm-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
    		    "initComplete": function (settings, json) {
		    		var select_estado=$('<select name="estado_select" id="estado_select" class="form-control input-sm" style="width:60%"></select>')
						.append($('<option value="">Todos</option>'))
						.append($('<option value=1>Activos</option>'))
						.append($('<option value=2>Inactivos</option>'))
					$(".div_select").append(select_estado);

					$("#estado_select").off("change").on("change",function(){
						var val=$(this).val();
						datatable_locales.column(0).search(val).draw();
						datatable_locales.columns.adjust();
						localStorage.setItem("estado_select_valor",val);
					})

					if(localStorage.getItem("estado_select_valor")){
						setTimeout(function(){
							$("#estado_select").val(localStorage.getItem("estado_select_valor")).change();
						},200);
						
					}
					$("input[type='search']:visible").focus();
                },
                order:[],
                paging:false  ,              

    			columns: [
    				{
						title: "",
						data: "operativo",
						visible:false
					},
    				{
						title: "Local ID",
						data: "id",
					},
					{
						title: "Local Nombre",
						data: "local_nombre",

					},
					{
						title: "Deuda",
						data: "debe",
						class:"text-right"

					},
					{
						title: "Pagado",
						data: "haber",
						class:"text-right"
					},
					{
						title:"Saldo",
						"render":function(data,type,row){
							var clase_td="";
							if(row["local_deuda"]>0){ 
								clase_td="text-danger" ;
							}else{
							  clase_td="text-success" ;
							}  
							var html="<div class='";
								html+=clase_td;
								html+="'>";
								html+=row["local_deuda"];
								html+='</div>';
								
							return html;
						},
						class:"text-right"

					},
					{
						title: "Ver",
						width:"150px",
						"render":function(data,type,row){
							var id=row["id"];
							var html="";
							html="";
							html+="<button class='btn btn-primary btn-xs ver_btn' data-local_id="+id+">";
							html+="Ver";	
							html+='</button>';
							
							return html;
						}
					}
				],
	      		data:data,
    		} );
        },
        error: function () {
    		set_data.error = obj.error;
			set_data.error_msg = obj.error_msg;
			auditoria_send({"proceso":"lista_locales_estado_de_cuenta","data":set_data});
        }
    });
}

function filtro_numerico(__val__,decimales=2) {
   // var preg = /^([0-9]+\.?[0-9]{0,2})$/;
	var punto="\.";
	if(decimales == 0){
		punto="";
	}
	var preg = new RegExp("^-?([0-9]+"+punto+"?[0-9]{0,"+decimales+"})$","i") ;
	if (preg.test(__val__) === true) {
		return true;
	} else {
		return false;
	}
}
function validar_input_float(evt, input) {
    // Backspace = 8, Enter = 13, '0′ = 48, '9′ = 57, '.' = 46, '-' = 43
    var key = window.Event ? evt.which : evt.keyCode;
    /*var ctrl = evt.ctrlKey ? evt.ctrlKey : ((key === 17)? true : false);
    if (key == 86 && ctrl) {
        console.log("Ctrl+V is pressed.");
    }*/
	var chark = String.fromCharCode(key);
    //var tempValueant = input.value + chark;
    var posicion=input.selectionStart;
    var tempValue=[(input.value).slice(0, posicion), chark, (input.value).slice(posicion)].join('');

    if (key >= 48 && key <= 57 || key ==45 ) {
    	if(key==45){
			if(posicion==0){
    			return true;
    		}
    		else{return false;}
    	}
        if (filtro_numerico(tempValue) === false) {
            return false;
        } else {
            return true;
        }
    } else {
        if (key == 8 || key == 13 || key == 0) {
            return true;
        } else if (key == 46) {
            if (filtro_numerico(tempValue) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
}

(function( $ ) {
    $.fn.validar_numerico = function() {
        return this.each(function() {
    	  	var $this = $(this);
			/*$(document).on("keypress", $this, function(event,ele){
  				return validar_input_float(event, this.activeElement);
			})*/
		    $this.on('keypress', function () {
  				return validar_input_float(event, this);
			});
		    $this.on('paste', function (e) {
				var data = e.originalEvent.clipboardData.getData('Text');
			    var input =$(this)[0];
			    var posicion=input.selectionStart;
			    
			    var tempValue=[(input.value).slice(0, posicion), data, (input.value).slice(posicion)].join('');
			    if(input.selectionStart == 0 && input.selectionEnd == input.value.length){//si todo seleccionado
			    	tempValue=data;
			    }
				if (filtro_numerico(tempValue) === false) {
					e.preventDefault();//no pegar
			     } 
			})
		});
    };
}( jQuery ));

/*
$(".input_numerico").on('paste', function (e) {
	var data = e.originalEvent.clipboardData.getData('Text');
    var input =$(this)[0];
    var posicion=input.selectionStart;
    
    var tempValue=[(input.value).slice(0, posicion), data, (input.value).slice(posicion)].join('');
    if(input.selectionStart == 0 && input.selectionEnd == input.value.length){//si todo seleccionado
    	tempValue=data;
    }
	if (filtro_numerico(tempValue) === false) {
		e.preventDefault();//no pegar
     } 
})*/

