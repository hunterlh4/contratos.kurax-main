function sec_reportes_tickets(){
	var filtro_fecha_inicio = false;
	var filtro_fecha_fin = false;
	// console.log("sec_reporte_tickets");
	sec_reportes_tickets_settings();
	sec_reportes_tickets_events();
}
function sec_reportes_tickets_events(){
	// console.log("sec_reportes_tickets_events");
	$(".search_btn")
		.off()
		.click(function(event) {
			var btn = $(this);
			var opt = {};
				opt.reset = true;
			sec_reportes_get_tickets(opt);
		});
	// $(".search_btn").first().click();

	$(".clean_btn")
		.off()
		.click(function(event) {
			sec_reportes_tickets_clean_filtros();
		});


	$(".filtro[name=pagina_limit]")
		.off()
		.change(function(event) {
			var opt = {};
				opt.reset = true;
			sec_reportes_get_tickets(opt);
		});

	$(".pagina_ir_btn")
		.off()
		.click(function(event) {
			var opt = {};
				opt.reset = false;
			sec_reportes_get_tickets(opt);
		});

	$(".pagina_nav")
		.off()
		.click(function(event) {
			var btn = $(this);
			var btn_data = btn.data();
			var curr_page = $(".filtro[name=pagina]").val();
			var new_page = parseInt(curr_page);
			if(btn_data.opt == "+1"){
				new_page = new_page + 1;
			}
			if(btn_data.opt == "-1"){
				new_page = new_page - 1;
			}
			if(new_page<0){
				new_page=1;
			}
			$(".filtro[name=pagina]").val(new_page);
			var opt = {};
				opt.reset = false;
			sec_reportes_get_tickets(opt);
			console.log(btn_data.opt);
		});
}
function sec_reportes_tickets_clean_filtros(){
	$.each(localStorage, function(ls_index, val) {
		if(ls_index.indexOf("sec_reportes_tickets_filtro_") >= 0){
			localStorage.removeItem(ls_index);
		}
	});
	m_reload();
}
function sec_reportes_tickets_settings(){
	// console.log("sec_reportes_tickets_settings");
	$.each(localStorage, function(ls_index, val) {
		if(ls_index.indexOf("sec_reportes_tickets_filtro_") >= 0){
			var input_name = ls_index.replace("sec_reportes_tickets_filtro_","");
			// console.log(val);
			$(".filtro[name="+input_name+"]").val(val);
			var real_date = $(".filtro[name="+input_name+"]").data('real-date');
			if(real_date){
				var new_date = moment(val).format("DD-MM-YYYY");
				$("#"+real_date).val(new_date);
				// console.log();
				// console.log(real_date);
			}
		}
	});
	$('.sec_reportes_tickets_datepicker')
		.datepicker({
			dateFormat:'dd-mm-yy',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
			// localStorage.setItem($(this).atrr("id"),)
		});
	$(".select2")
		.select2();
	$(".info_tickets").hide();	
}
function sec_reportes_get_tickets(opt){
	// console.log("sec_reportes_get_tickets");
	// console.log(opt);
	loading(true);
	let dfecha_inicio= document.getElementById('input_text-reporte_tickets_inicio_fecha').value;
	let dfecha_fin = document.getElementById('input_text-reporte_tickets_fin_fecha').value;
	//Dfecha_inicio= new Date(dfecha_inicio);
	let arr_fecha_inicio= dfecha_inicio.split("-");
	let arr_fecha_fin = dfecha_fin.split("-");
	if(Date.parse(arr_fecha_inicio[2]+"-"+arr_fecha_inicio[1]+"-"+arr_fecha_inicio[0] ) < Date.parse(arr_fecha_fin[2]+"-"+arr_fecha_fin[1]+"-"+arr_fecha_fin[0])){
		//solo en caso de que fecha fin sea mayor se procede
		var data = {};
			data.where = 'tickets';
		data.filtro={};
			data.filtro.servicio_id = 1;
			data.filtro.pagina_limit = 50;
			data.filtro.order_by = "created";
			data.filtro.order_by_sort = "ASC";
		$(".filtro").each(function(index, el) {
			var input_type = $(el).attr("type");
			var input_name = $(el).attr("name");
			var input_val = $(el).val();
			if(input_val===""){
				localStorage.removeItem("sec_reportes_tickets_filtro_"+input_name);
			}else{
				if(input_val=="_all_"){
					localStorage.removeItem("sec_reportes_tickets_filtro_"+input_name);
				}else{
					if(input_type=="radio"){
						if($(el).prop('checked')){
							data.filtro[input_name]=input_val;
							localStorage.setItem("sec_reportes_tickets_filtro_"+input_name,input_val);
						}
					}else{
						data.filtro[input_name]=input_val;
						localStorage.setItem("sec_reportes_tickets_filtro_"+input_name,input_val);
					}
				}
			}
		});
		if(opt.reset){
			data.filtro.pagina = 1;	
		}

		var procced = true;
		if(procced){
			auditoria_send({"proceso":"sec_reportes_get_tickets","data":data});
			$.post('/api/?json', data,
			function(r) {
				try{
					var obj = jQuery.parseJSON(r);
					console.log(obj);
					var t_data = {};
					$("#sec_reportes_tickets_table thead th")
						.each(function(index, el) {
							t_data[$(el).data("col")]={};
						});
					// console.log(t_data);
					$("#sec_reportes_tickets_table tbody").html("");
					$.each(obj.data, function(ticket_index, ticket) {
						console.log(ticket.bet_id);
						var tr = $("<tr data-bet_id='"+ticket.bet_id+"'>");
						// tr.data("bet_id",ticket.bet_id);
						$.each(t_data, function(col_id, val) {
							var td = $("<td>");
							if(col_id=="opt"){
								td.html("<i class='glyphicon glyphicon-list-alt more_btn' title='Ver apuestas'></i>");
							}else{
								td.html(ticket[col_id]);
							}
							tr.append(td);
						});
						$("#sec_reportes_tickets_table tbody").append(tr);
					});
					loading();
					// console.log(obj.sql_command);
					// console.log(obj.sql_where);
					// console.log(obj.info.time_total);
					$(".info_server").html(obj.info.time_total);
					$(".info_tickets").show();
					$(".info_tickets i.tickets_from").html(obj.info.tickets_from);
					$(".info_tickets i.tickets_to").html(obj.info.tickets_to);
					// $(".info_tickets i.num_tickets").html(obj.info.num_tickets);

					// $(".info_tickets i.num_tickets").html(obj.info.num_tickets);
					$(".info_tickets i.tickets_total").html('<i class="fa fa-refresh fa-spin" title="Cargando numero de tickets..."></i>');

					$(".download_btn .d_icon").hide();
					$(".download_btn .d_loading").show();
					$(".download_btn i.text").html("Preparando descarga...");
					$("#sec_reportes_tickets_table tfoot th").html('<i class="fa fa-refresh fa-spin" title="Cargando..."></i>');
					
					$("#sec_reportes_tickets_table .sort_btn .icon-theme i").removeClass('glyphicon-sort-by-attributes-alt').addClass('glyphicon-sort-by-attributes');
					if(data.filtro.order_by_sort=="ASC"){
						$("#sec_reportes_tickets_table .sort_btn[data-col='"+data.filtro.order_by+"'] .icon-theme i").removeClass('glyphicon-sort-by-attributes-alt').addClass('glyphicon-sort-by-attributes');
					}else{
						$("#sec_reportes_tickets_table .sort_btn[data-col='"+data.filtro.order_by+"'] .icon-theme i").removeClass('glyphicon-sort-by-attributes').addClass('glyphicon-sort-by-attributes-alt');
					}
					$("#sec_reportes_tickets_table .sort_btn .icon-theme").removeClass('bg-success');
					$("#sec_reportes_tickets_table .sort_btn[data-col='"+data.filtro.order_by+"'] .icon-theme").addClass('bg-success');


					sec_reportes_get_tickets_events(data);
					sec_reportes_get_num_tickets(data);
				}catch(err){
					ajax_error(true,r,err);//opt,response,catch-error
				}
				// console.log(r);
			});
		}else{
			// console.log("procced: "+procced);
			loading();
		}
	}else{
		alert("Fecha incorrecta");
		loading();
	}
}
function sec_reportes_get_tickets_events(data){
	$("#sec_reportes_tickets_table .more_btn")
		.off()
		.click(function(event) {
			var btn = $(this);
			var tr = btn.closest('tr');
			sec_reportes_get_apuestas(tr.data("bet_id"));
			// console.log(tr.data("bet_id"));
		});
	$("#sec_reportes_tickets_table .sort_btn")
		.off()
		.click(function(event) {
			var col = $(this).data("col");
				if(col!="opt"){
				localStorage.setItem("sec_reportes_tickets_filtro_order_by",col);
				// console.log(col);
				$(".filtro[name=order_by]").val(col);

				var new_order_by_sort = "ASC";

				if(data.filtro.order_by == col){
					if(data.filtro.order_by_sort=="ASC"){
						new_order_by_sort="DESC";
					}else{
						new_order_by_sort="ASC";
					}				
				}

				$(".filtro[name=order_by_sort]").val(new_order_by_sort);
				localStorage.setItem("sec_reportes_tickets_filtro_order_by_sort",new_order_by_sort);
				var opt = {};
					opt.reset = true;
				sec_reportes_get_tickets(opt);
			}
		});
}
function sec_reportes_get_num_tickets(data){
	// console.log("sec_reportes_get_num_tickets:loading...");
	// data.num_tickets=true;

	var post_data = jQuery.extend({}, data)
		post_data.num_tickets = true;
	$.post('/api/?json', post_data,
	function(r) {
		console.log(r);
		try{
			var obj = jQuery.parseJSON(r);
			console.log(obj);
			$(".info_tickets i.tickets_total").html(obj.data.fetch.num_tickets);
			$("#sec_reportes_tickets_table tfoot th").html('');
			$("#sec_reportes_tickets_table tfoot th[data-col='total_apostado']").html(obj.data.fetch.total_apostado);
			$("#sec_reportes_tickets_table tfoot th[data-col='total_ganado']").html(obj.data.fetch.total_ganado);

			$(".filtro[name=pagina]").html("");

			// for (var p = 0; p < obj.data.total_paginas; p++){
			// // $.each(obj.data.total_paginas, function(index, val) {
			// 	var opt_pag = $("<option>");
			// 		opt_pag.val(p);
			// 		opt_pag.html(p+1);
			// 	$(".filtro[name=pagina]").append(opt_pag);
			// }
			$(".filtro[name=pagina]").val(obj.filtro.pagina);
			

			$("#download_form")
				.html("");

			// console.log(post_data.filtro);

			// DOWNLOAD
				$(".info_tickets i.download_btn").show();
				if(obj.data.no_download){
					$(".download_btn .d_icon").hide();
					$(".download_btn .d_loading").hide();
					$(".download_btn i.text").html("Descargar");
					$(".download_btn")
						.off()
						.click(function(event) {
							swal({
								title: 'Máxima descarga 200,000 tickets',
								type: "warning",
								timer: 2000,
							}, function(){
								swal.close();								
							});
						});
				}else{
					$(".download_btn .d_icon").show();
					$(".download_btn .d_loading").hide();
					$(".download_btn i.text").html("Descargar");
					var dw_data = jQuery.extend({}, data)
						// dw_data.filtro.where = "tickets";

						var input = $("<input>");
							input.attr("name","where");
							input.val("tickets");
						$("#download_form").append(input);
						var input = $("<input>");
							input.attr("name","export");
							input.val("csv");
						$("#download_form").append(input);

					$.each(dw_data.filtro, function(index, val) {
						var input = $("<input>");
							input.attr("name","filtro["+index+"]");
							input.val(val);
						$("#download_form").append(input);
					});
					$("#download_form")
						.attr("action","/api/")
						.attr("target","_blank")
						.attr("method","post");
						;

					$(".download_btn")
						.off()
						.click(function(event) {
							auditoria_send({"proceso":"sec_reportes_tickets_download","data":dw_data});
							$("#download_form").submit();
						});
				}

			// DOWNLOAD
		}catch(err){
			ajax_error(true,r,err);//opt,response,catch-error
		}
		// console.log(r);
	});
}
function sec_reportes_get_apuestas(bet_id){
	// console.log("sec_reportes_get_apuestas");
	if($(".tk_"+bet_id+"_apuestas").size()){
		$(".tk_"+bet_id+"_apuestas").remove();
	}else{
		// console.log(bet_id);
		var num_cols = $("#sec_reportes_tickets_table thead th").size();
		// console.log(num_cols);
		var new_tr = $("<tr class='tk_"+bet_id+"_apuestas'>");
		var td = $("<td>");
			td.attr('colspan', num_cols);
			// td.html("adadsasdas");
		var data = {};
			data.where = 'ticket_apuestas';
			data.bet_id=bet_id;
		$.post('/api/where_ticket_apuestas.php', data,
			function(r) {
				td.html(r);
			});
		new_tr.append(td);
		var old_tr = $("tr[data-bet_id='"+bet_id+"'");
		old_tr.after(new_tr);
		// console.log(old_tr);

	}
}

// ***************************************************************************
// ***************************************************************************

$(document).ready(function(){
	$('#collapse_ticket_filter').click(function(){
		$("#ticket_filter").toggle();
		$("#collapse_ticket_filter #_collapse").toggle();
	});
});