function sec_caja_eliminadas(){
	if(sub_sec_id=="cajas_eliminadas"){

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
		sec_caja_eliminada_config();
		sec_caja_eliminadas_events();
		
	}
}

function sec_caja_eliminada_config(){
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

function sec_caja_eliminadas_events(){
	console.log("sec_caja_eliminadas_events");	
	$(document).off('click', '.td_mensaje');
	$(document).on("click",".td_mensaje",function(){
		var mensaje = $(this).data("mensaje");
			swal({
			  title: '<strong>Mensaje</strong><div style="font-size:11px;background-color:#e9f6ff;height: auto;" class="form-control">'+mensaje+'</div>',
			  type: 'info',
			    html: true,
			  showCloseButton: true,
			  showCancelButton: false,
			  focusConfirm: false,
			});
	});

	$(".search_btn")
		.off()
		.click(function(event) {
			sec_caja_eliminadas_get_reporte();
		});
	//if($(".search_btn").length>0){
	//	$(".search_btn").click();
	//}	

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

function sec_caja_eliminadas_get_reporte(){
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
	$.post('/sys/get_reportes_caja_eliminadas.php', {
		"sec_caja_get_reporte": get_data
	}, function(r) {
		loading();
		try{
			$(".table_container").html(r);

			$(".btn_export_caja_reporte").off().on("click",function(e){
				loading(true);
				$.ajax({
					url: '/export/caja_reporte.php',
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

function sec_caja_eliminadas_get_cierre_efectivo(caja_id) {
	$('#modal_cierre_efectivo').modal({backdrop: 'static', keyboard: false});
	var data = {
		action: "obtener_cierre_efectivo_eliminado_por_caja_id",
		caja_id: caja_id,
	};
	$.ajax({
		url: "sys/router/caja_cierre_efectivo/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			var respuesta = JSON.parse(resp);
			if (respuesta.status == 200) {
				$('#modal_ce_billete_10').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.billete_10_cant)));
				$('#modal_ce_billete_20').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.billete_20_cant)));
				$('#modal_ce_billete_50').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.billete_50_cant)));
				$('#modal_ce_billete_100').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.billete_100_cant)));
				$('#modal_ce_billete_200').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.billete_200_cant)));
				$('#modal_ce_moneda_001').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_001_cant)));
				$('#modal_ce_moneda_002').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_002_cant)));
				$('#modal_ce_moneda_005').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_005_cant)));
				$('#modal_ce_moneda_010').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_010_cant)));
				$('#modal_ce_moneda_020').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_020_cant)));
				$('#modal_ce_moneda_050').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_050_cant)));

				$('#modal_ce_importe_boveda').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.monto_boveda)));

				$('#lbl_ce_billete_total_10').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.billete_10_total)));
				$('#lbl_ce_billete_total_20').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.billete_20_total)));
				$('#lbl_ce_billete_total_50').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.billete_50_total)));
				$('#lbl_ce_billete_total_100').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.billete_100_total)));
				$('#lbl_ce_billete_total_200').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.billete_200_total)));
				$('#lbl_ce_moneda_total_001').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_001_total)));
				$('#lbl_ce_moneda_total_002').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_002_total)));
				$('#lbl_ce_moneda_total_005').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_005_total)));
				$('#lbl_ce_moneda_total_010').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_010_total)));
				$('#lbl_ce_moneda_total_020').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_020_total)));
				$('#lbl_ce_moneda_total_050').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.moneda_050_total)));

				$('#lbl_ce_billete_total').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.total_billete)));
				$('#lbl_ce_moneda_total').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.total_moneda)));
				$('#lbl_ce_total').html(formatearNumeroConDelimitador(parseFloat(respuesta.result.monto_final)));
				if (respuesta.result.red_id == 9) {
					$('#div-container-boveda').show();
				}else{
					$('#div-container-boveda').hide();
				}
			}else{
				$('#modal_ce_billete_10').html('0.00');
				$('#modal_ce_billete_20').html('0.00');
				$('#modal_ce_billete_50').html('0.00');
				$('#modal_ce_billete_100').html('0.00');
				$('#modal_ce_billete_200').html('0.00');
				$('#modal_ce_moneda_001').html('0.00');
				$('#modal_ce_moneda_002').html('0.00');
				$('#modal_ce_moneda_005').html('0.00');
				$('#modal_ce_moneda_010').html('0.00');
				$('#modal_ce_moneda_020').html('0.00');
				$('#modal_ce_moneda_050').html('0.00');

				$('#modal_ce_importe_boveda').html('0.00');

				$('#lbl_ce_billete_total_10').html('0.00');
				$('#lbl_ce_billete_total_20').html('0.00');
				$('#lbl_ce_billete_total_50').html('0.00');
				$('#lbl_ce_billete_total_100').html('0.00');
				$('#lbl_ce_billete_total_200').html('0.00');
				$('#lbl_ce_moneda_total_001').html('0.00');
				$('#lbl_ce_moneda_total_002').html('0.00');
				$('#lbl_ce_moneda_total_005').html('0.00');
				$('#lbl_ce_moneda_total_010').html('0.00');
				$('#lbl_ce_moneda_total_020').html('0.00');
				$('#lbl_ce_moneda_total_050').html('0.00');

				$('#lbl_ce_billete_total').html('0.00');
				$('#lbl_ce_moneda_total').html('0.00');
				$('#lbl_ce_total').html('0.00');
				if (respuesta.result.red_id == 9) {
					$('#div-container-boveda').show();
				}else{
					$('#div-container-boveda').hide();
				}
			}
			
		},
		error: function () {},
	});
}

function sec_caja_eliminada_cerrar_modal_denominacion_billetes() {
	$('#modal_cierre_efectivo').modal('hide');
}

function formatearNumeroConDelimitador(numero, locale = 'en-US', minDecimales = 2, maxDecimales = 2) {
	return numero.toLocaleString(locale, {
		style: 'decimal',
		minimumFractionDigits: minDecimales,
		maximumFractionDigits: maxDecimales,
		useGrouping: true,
	});
}
