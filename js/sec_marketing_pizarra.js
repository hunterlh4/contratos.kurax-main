function sec_marketing_pizarra() {
	// if(sec_id=="marketing"){
	// 	if (sub_sec_id=="pizarra") {
			console.log("sub_sec_id:pizarra");
			// sec_marketing_pizarra();			
	// 	}
	// }
	$(".add_button")
		.off()
		.click(function(event) {
			smp_add_modal(true);
		});
	// $(".add_button").first().click();
	$(".add_pizarra_cerrar_btn")
		.off()
		.click(function(event) {
			smp_add_modal();
		});
	
	$(".pizarra_datepicker")
		.datepicker({
			dateFormat:'yy-mm-dd',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function(ev) {
			// $(this).datepicker('hide');
			// var newDate = $(this).datepicker("getDate");
			// $("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
		});
	$(".add_pizarra_btn")
		.off()
		.click(function(event) {
			smp_add_pizarra();
		});
}
function smp_add_modal(opt){
	if(opt){
		$("#marketing_pizarra_add_modal").modal("show");
		$("#add_deuda_input_linea_1").focus();
		// $("#marketing_pizarra_add_modal").fir
	}else{
		$("#marketing_pizarra_add_modal").modal("hide");
	}
}
function smp_add_pizarra(){
	console.log("smp_add_pizarra");
	loading(true);
	var save_data = {};

	$(".add_col").each(function(index, el) {
		save_data[$(el).data("col")]=$(el).val();
	});

	$.post('sys/set_data.php', {
		"opt": 'marketing_pizarra_add'
		,"data":save_data
	}, function(r) {
		try{
			loading();
			var obj = jQuery.parseJSON(r);
			console.log(obj);
			swal({
					title: "Agregado",
					text: "",
					type: "success",
					timer: 400,
					closeOnConfirm: false
				},
				function(){
					m_reload();
				});
		}catch(err){
			console.log(r);
			// ajax_error(true,r,err);//opt,response,catch-error
		}
		// auditoria_send({"proceso":"recaudacion_div_trans_bancaria","data":save_data});
	});

		// save_data.locales.estado = 1;
	console.log(save_data);
}