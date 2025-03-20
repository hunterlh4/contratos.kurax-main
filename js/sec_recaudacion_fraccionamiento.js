function sec_recaudacion_fraccionamiento() {
	console.log("sec_recaudacion_fraccionamiento");
	frac_events();
}
function frac_events(){
	console.log("frac_events");
	$("#fraccionamiento_modal .save_btn")
		.off()
		.click(function(event) {
			console.log("fraccionamiento_modal.save_btn:click");
			recaudacion_frac_add();
		});
	$('#fraccionamiento_modal')
		.off()
		.on('shown.bs.modal', function () {
			console.log("fraccionamiento_modal:shown.bs.modal");
			// frac_get_clientes();
			frac_get_locales();
		})
		.on('hide.bs.modal', function () {		
			console.log("fraccionamiento_modal:hide.bs.modal");
			$("#frac_holder").addClass('hidden');
		});
	$(".fraccionamiento_add_btn")
		.off()
		.click(function(event) {
			console.log("fraccionamiento_add_btn:click");
			$("#fraccionamiento_modal").modal("show");
			// auditoria_send({"proceso":"frac_get_locales","data":api_filtro});
		});
	// $(".fraccionamiento_add_btn").first().click();
	$("#fraccionamiento_modal .close_btn")
		.off()
		.click(function(event) {
			$("#fraccionamiento_modal").modal("hide");
		});

	$('#input_cliente')
		.off()
		.change(function(event) {
			console.log("input_cliente:change");
			// $("#input_local").html("");
			// var cliente_id = $(this).val();
			// if(cliente_id>0){
			// 	console.log(cliente_id);
			// 	frac_get_locales(cliente_id);
			// }else{

			// }
		});
	$("#input_local")
		.off()
		.change(function(event) {
			console.log("#input_local:change");
			$("#frac_load_btn").first().click();
			// $("#input_periodo").html("");
			// var local_id = $(this).val();
			// console.log(local_id);
			// frac_get_local_periodos(local_id);
		});
	$('.make_me_select2').select2();
	$(".frac_calc_cuotas_btn")
		.off()
		.click(function(event) {
			console.log("frac_calc_cuotas_btn:click");
			frac_calc_cuotas();
		});
	// $(".frac_calc_cuotas_btn").first().click();
	$("#input_cuotas")
		.off()
		.change(function(event) {
			frac_calc_cuotas();
			// var input_facturacion = $("#input_facturacion").val();
			// if(input_facturacion=="cut"){
			// 	fac_fecha_hidden(true);
			// }else{
			// 	fac_fecha_hidden();
			// }
		});

	$("#input_facturacion")
		.off()
		.change(function(event) {
			fac_fecha_hidden();
			frac_calc_cuotas();
		});
	$("#fraccionamiento_modal .makeme_datepicker")
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
	$("#frac_load_btn")
		.off()
		.click(function(event) {
			console.log("#frac_load_btn:click");
			// frac_load();
			frac_get_local_periodos();
		});


	// setTimeout(function(){
	// }, 100);
	// setTimeout(function(){
	// }, 500);
	// setTimeout(function(){		
	// },800);

	// $("#input_local").val("81").change();
	// $("#input_periodo").val("81").change();
}
function frac_get_clientes(){
	console.log("frac_get_clientes");
	loading(true);
	$.post('api/?json', {
		"where": 'clientes'
	}, function(r) {
		try{
			var obj = jQuery.parseJSON(r);
			obj.data[0]={"id":0,"nombre":"Seleccione un Cliente"};
			console.log(obj);
			$("#input_cliente").html("");
			$.each(obj.data, function(index, val) {
				$('#input_cliente').append($('<option>', { 
					value: val.id,
					text : val.nombre 
				}));
			});
			console.log("frac_get_clientes:done");
			// $('#input_local').select2('open');
			// $('#input_local').val(186);
			// $('#input_local').trigger('change');
			// $('#input_local').select2('close');
			loading();
		}catch(err){
			ajax_error(true,r,err);//opt,response,catch-error
		}
		auditoria_send({"proceso":"frac_get_clientes"});
	});
}
function frac_get_locales(cliente_id){
	console.log("frac_get_locales");
	loading(true);
	var api_filtro = {};
	if(cliente_id){
		console.log(cliente_id);
		api_filtro.cliente_id = cliente_id;
	}
	$.post('api/?json', {
		"where": 'locales'
		,"filtro":api_filtro
	}, function(r) {
		try{
			var obj = jQuery.parseJSON(r);
			console.log(obj);
			// obj.data[0]={"id":0,"nombre":"Seleccione un Local"};
			$("#input_local").html("");
			$('#input_local').append($('<option>', { 
				value: 0,
				text : "Seleccione un Local"
				}));
			$.each(obj.data, function(index, val) {
				$('#input_local').append($('<option>', { 
				value: val.id,
				text : val.nombre 
				}));
			});
			console.log("frac_get_locales:done");

			// $('#input_local').select2('open');
			// $('#input_local').val(98);
			// $('#input_local').trigger('change');
			// $('#input_local').select2('close');
			loading();
		}catch(err){
			ajax_error(true,r,err);//opt,response,catch-error
		}
		auditoria_send({"proceso":"frac_get_locales","data":api_filtro});
	});
}
function frac_get_local_periodos(local_id){
	console.log("frac_get_local_periodos");
	if(!local_id){
		local_id = $("#input_local").val();
	}
	if(local_id>0){
		$("#cuotas_maker_holder").addClass('hidden');
		console.log(local_id);
		loading(true);
		$.post('api/?json', {
			"where": 'local_periodos'
			,"local_id":local_id
		}, function(r) {
			try{
				var obj = jQuery.parseJSON(r);
				$("#frac_holder").removeClass('hidden');
				$("#periodos_holder").html("");

				console.log(obj);
				// $("#input_periodo").html("");
				$.each(obj.data, function(index, val) {
					var c_tr = $("<tr>");
						c_tr.append("<td>"+val.anio+"</td>");
						c_tr.append("<td>"+val.mes_nombre+"</td>");
						c_tr.append("<td>"+val.semana+"</td>");
						c_tr.append("<td>"+val.cdv_nombre+"</td>");
						c_tr.append("<td>"+val.monto+"</td>");
					var move_to_maker_btn = $('<td><button class="btn btn-xs btn-primary">></button></td>');
						c_tr.append(move_to_maker_btn);
					$("#periodos_holder").append(c_tr);
					$(move_to_maker_btn)
						.off()
						.click(function(event) {
							frac_move_to_maker(val);
						});
				});
				loading();
			}catch(err){
				ajax_error(true,r,err);//opt,response,catch-error
			}
			// auditoria_send({"proceso":"frac_get_local_periodos","data":local_id});
		});
	}else{
		swal({
			title: 'Seleccione un local!',
			type: "warning",
			timer: 1000,
		}, function(){
			swal.close();
			loading();
			$("#input_local").select2("open");
		}); 
	}
}
function frac_move_to_maker(frac_data){
	console.log("frac_move_to_maker");
	$("#cuotas_maker_holder").removeClass('hidden');
	$("#cuotas_maker_holder #input_monto").val(frac_data.monto);
	$("#cuotas_maker_holder #input_cuotas").val(1);
	$("#cuotas_maker_holder #proceso_id").val(frac_data.at_unique_id);
	// $("#cuotas_holder").html("");
	frac_calc_cuotas();
}
function frac_calc_cuotas(opt){
	console.log("frac_calc_cuotas");
	var monto = $("#fraccionamiento_modal #input_monto").val();
	var cuotas = parseInt($("#fraccionamiento_modal #input_cuotas").val());
	var fecha_inicio = $("#fraccionamiento_modal #fecha_inicio").val();
	var input_facturacion = $("#fraccionamiento_modal #input_facturacion").val();

	// if(opt=="reset"){
	// 	cuotas=0;
	// }

	// var test = moment(fecha_inicio).add(1, 'months').format();
	// console.log(test);
	if(cuotas>0){
		var cuota = (monto / cuotas).toFixed(2);
		var redondeo = ((cuota*cuotas)-monto).toFixed(2);
		var ultima_cuota = (cuota-redondeo).toFixed(2);
		console.log(cuotas);
		$("#cuotas_holder").html("");
		for (var i = 0; i < cuotas; i++) {
			// var cuota_fecha = new Date(2009, 0, 31);
			if(input_facturacion=="cut"){
				var cuota_fecha = null;
			}if(input_facturacion=="fortnight"){
				var cuota_fecha = moment(fecha_inicio).add((2*i),'weeks').format("DD/MM/YYYY");
			}else{
				var cuota_fecha = moment(fecha_inicio).add(i,input_facturacion).format("DD/MM/YYYY");
			}
			var c_tr = $('<tr class="frac_calc_cuota_item">');
				c_tr.append('<td class="frac_calc_cuota_num">'+(i+1)+"</td>");
				c_tr.append('<td class="cuota_fecha_col fac_fecha_hidden">'+cuota_fecha+"</td>");
			if(i==cuotas){
				c_tr.append('<td class="frac_calc_cuota">'+ultima_cuota+"</td>");
			}else{
				c_tr.append('<td class="frac_calc_cuota">'+cuota+"</td>");
			}
			$("#cuotas_holder").append(c_tr);			
		}
		fac_fecha_hidden();
		// $("#input_facturacion").trigger('change');
	}else{
		swal({
			title: 'Las cuotas deben ser mayores a 0',
			type: "warning",
			timer: 2000,
		}, function(){
			swal.close();
			loading();
		}); 
	}
}
function fac_fecha_hidden(){
	console.log("fac_fecha_hidden");
	var input_facturacion = $("#input_facturacion").val();
	if(input_facturacion=="cut"){
		$(".fac_fecha_hidden").addClass('hidden');
	}else{
		$(".fac_fecha_hidden").removeClass('hidden');
	}
}
function frac_load(){
	console.log("frac_load");
	var local_id = $("#input_local").val();
	// var periodo_id = $("#input_periodo").val();

	console.log(local_id);
	// console.log(periodo_id);
	$("#frac_holder").addClass('hidden');
	if(local_id>0){
		// if(periodo_id>0){

			// var api_data = {};
			// 	api_data.local_id = local_id;
				// api_data.periodo_id = periodo_id;

			$.post('api/?json', {
				"where": 'local_periodos'
				,"local_id":local_id
			}, function(r) {
				try{
					var obj = jQuery.parseJSON(r);

					$("#periodos_holder").html("");

					// obj.data[0]={"id":0,"anio":"Seleccione un Periodo","mes_nombre":"","semana":""};
					console.log(obj);
					// $("#input_periodo").html("");
					$.each(obj.data, function(index, val) {
						var c_tr = $("<tr>");
							c_tr.append("<td>"+val.anio+"</td>");
							c_tr.append("<td>"+val.mes_nombre+"</td>");
							c_tr.append("<td>"+val.semana+"</td>");
							c_tr.append("<td>"+val.cdv_nombre+"</td>");
							c_tr.append("<td>"+val.monto+"</td>");
							c_tr.append('<td><button class="btn btn-xs btn-primary">></button></td>');
						$("#periodos_holder").append(c_tr);
					});
					loading();

				}catch(err){
					ajax_error(true,r,err);//opt,response,catch-error
				}
				// auditoria_send({"proceso":"api_local_periodo","data":api_data});
			});


		// }else{
		// 	swal({
		// 		title: 'Seleccione un periodo!',
		// 		type: "warning",
		// 		timer: 1000,
		// 	}, function(){
		// 		swal.close();
		// 		loading();
		// 		$("#input_periodo").select2("open");
		// 	});
		// }
	}else{
		swal({
			title: 'Seleccione un local!',
			type: "warning",
			timer: 1000,
		}, function(){
			swal.close();
			loading();
			$("#input_local").select2("open");
		}); 
	}
}
function recaudacion_frac_add() {
	console.log("recaudacion_frac_add");
	var send_data = {};
		send_data.proceso_unique_id = $("#cuotas_maker_holder #proceso_id").val();
		send_data.local_id = $("#fraccionamiento_modal #input_local").val();
		send_data.monto = $("#cuotas_maker_holder #input_monto").val();
		send_data.num_cuotas = $("#cuotas_maker_holder #input_cuotas").val();
		send_data.facturacion_ciclo = $("#cuotas_maker_holder #input_facturacion").val();
		send_data.fecha_inicio = $("#cuotas_maker_holder #fecha_inicio").val();

		send_data.cuotas = {};

		$(".frac_calc_cuota_item").each(function(index, el) {
			var cuota = {};
				cuota.num = $(el).children('.frac_calc_cuota_num').html();
				cuota.monto = $(el).children('.frac_calc_cuota').html();
				cuota.fecha = $(el).children('.cuota_fecha_col').html();
			send_data.cuotas[index]=cuota;
		});
		
	console.log(send_data);
	$.post('sys/set_data.php', {
		"opt": 'recaudacion_frac_add'
		,"data":send_data
	}, function(r) {
		try{
			var obj = jQuery.parseJSON(r);
			console.log(obj);
			// swal({
			// 	title: "Guardado!",
			// 	text: "",
			// 	type: "success",
			// 	timer: 600,
			// 	closeOnConfirm: false
			// },
			// function(){							
			// 	swal.close();
			// 	m_reload();
			// });
		}catch(err){
			console.log(r);
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function(){
                swal.close();
                loading();
            }); 			
			// loading();
		}
		// auditoria_send({"proceso":"recaudacion_frac_add","data":send_data});
	});
}