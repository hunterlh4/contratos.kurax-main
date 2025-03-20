function sec_tickets_por_pagar(){
	if(sub_sec_id=="tickets_por_pagar"){

		item_config = {};
		$(".item_config").each(function(index, el) {
			item_config[$(el).attr("name")]=$(el).val();
		});
		// var ls_obj = {};
		$.each(item_config, function(index, val) {
			var ls_index = "sec_"+sec_id+"_"+sub_sec_id+"_"+index;
			// console.log(ls_index);
			// var input_name = ls_index.replace(ls_index_pref,"");
			if(ls = localStorage.getItem(ls_index)){
				item_config[index]=ls;
			}
		});
		sec_tickets_por_pagar_config();
		sec_tickets_por_pagar_events();	
	}
}

function sec_tickets_por_pagar_config(){
	console.log("sec_tickets_por_pagar_config");
	
	$.each(localStorage, function(ls_index, val) {
		var ls_index_pref = "sec_"+sec_id+"_"+sub_sec_id+"_";
		if(ls_index.indexOf(ls_index_pref) >= 0){
			var input_name = ls_index.replace(ls_index_pref,"");
			// console.log(val);
			$(".item_config[name="+input_name+"]").val(val).change();
			var real_date = $(".item_config[name="+input_name+"]").data('real-date');
			if(real_date){
				var new_date = moment(val).format("DD-MM-YYYY");
				$("#"+real_date).val(new_date);
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
}

function sec_tickets_por_pagar_events(){
	console.log("sec_tickets_por_pagar_events");	
	$(".btn_export_ticketPagar_reporte").hide();
	$(".btnticketpagar")
		.off()
		.click(function(event) {
			sec_tickets_por_pagar_get_reporte();
		});

	if($(".search_btn").length>0){
		$(".search_btn").click();
	}	


	$(".select2").select2({
		closeOnSelect: true,
		width:"100%"
	});
}

function sec_tickets_por_pagar_get_reporte(){
	// console.log("sec_caja_get_reporte");
	loading(true);
	// console.log(item_config);
	$(".item_config").each(function(index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_"+sec_id+"_"+sub_sec_id+"_"+config_index;
		localStorage.setItem(ls_index,config_val);
		item_config[config_index]=config_val;
	});
	var get_data = jQuery.extend({}, item_config);
	$.post('/sys/get_reportes_tickets_por_pagar.php', {
		"sec_ticket_por_pagar_reporte": get_data
	}, function(r) {
		loading();
		try{
			$(".tabla_contenedor_reportes").html(r);
			if($(".btn_export_ticketPagar_reporte").length>0){
				$(".btn_export_ticketPagar_reporte").show();
			}
			
			$(".btn_export_ticketPagar_reporte").off().on("click",function(e){
				loading(true);
				$.ajax({
					url: '/export/tickets_por_pagar.php',
					type: 'post',
					data: get_data,
				})
				.done(function(dataresponse) {
					var obj = JSON.parse(dataresponse);
					window.open(obj.path);
					loading();
				})
			});

			// console.log("sec_caja_get_reporte:READY");
			$(".table_container .view_more_btn").tooltip({
				placement:'left'
			});
			// console.log(url_object);
			if(url_object.fragment){
				if(url_object.fragment.ste){
					// console.log(url_object.fragment.ste);
					// var obj_top = $(url_object.fragment.ste).position();
					// setTimeout(function(){
					var obj_offset = $(".table_container").offset();
					// console.log(obj_offset);
					$(document).scrollTop(obj_offset.top - 52);	
					// }, 10);
				}
			}
		}catch(err){
			// console.log(r);
		}
		// console.log(r);
	});
	// console.log(item_config);
}