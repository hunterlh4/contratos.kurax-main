function sec_historial_monto(){
	if(sub_sec_id=="historial_monto"){

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
		sec_historial_config();
		sec_historial_events();
	}
}

function sec_historial_config(){
	// console.log("sec_caja_config");
	
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

		var ls_index_single_searcher = "sec_caja_turno_searcher_";
		if(ls_index.indexOf(ls_index_single_searcher) >= 0){
			$("."+ls_index).val(val);
		}
	});

	$(".sec_caja_eliminada_reporte_fecha_inicio")
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

		$(".sec_caja_eliminada_reporte_fecha_fin")
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

function sec_historial_events(){
	console.log("sec_historial_events");	
	
	$(".search_btn")
		.off()
		.click(function(event) {
			sec_historial_get_reporte();
		});
	if($(".search_btn").length>0){
		$(".search_btn").click();
	}	


	$(".select2").select2({
		closeOnSelect: true,
		width:"100%"
	});


	$(".single_searcher")
		.each(function(index, el) {
			var search_input = $(this);
			var holder_id = $(this).data("holder_id");
			var item_class = $(this).data("item_class");
			var item_where = $(this).data("where");
			var search_clear_btn = $(this).parent().find('.search_clear_btn');
				search_clear_btn
					.off()
					.click(function(event) {
						search_input.val("").change().focus();
					});
			search_input
				.off()
				.on('change keyup paste click', function () {
					var searchTerm = force_plain_text(search_input.val());
					if(searchTerm){
						localStorage.setItem("sec_caja_turno_searcher_"+item_where,searchTerm);
					}else{
						localStorage.removeItem("sec_caja_turno_searcher_"+item_where);
					}
					// console.log(searchTerm);
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
}

function sec_historial_get_reporte(){
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
	$.post('/sys/get_reportes_historial_monto.php', {
		"sec_caja_get_reporte": get_data
	}, function(r) {
		loading();
		try{
			$(".table_container").html(r);
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