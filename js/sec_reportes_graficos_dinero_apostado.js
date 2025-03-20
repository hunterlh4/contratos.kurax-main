var reportes_graficos_dinero_apostado_inicio_fecha_localstorage = false;
var reportes_graficos_dinero_apostado_fin_fecha_localstorage = false;
function sec_reportes_graficos_dinero_apostado(){
	console.log("sec_reportes_graficos_dinero_apostado");
	sec_reportes_graficos_dinero_apostado_settings();
	sec_reportes_graficos_dinero_apostado_events();
	sec_reportes_graficos_dinero_apostado_api_get();
	sec_reporte_graficos_dinero_apostado_get_canales_venta();
	sec_reportes_graficos_dinero_apostado_get_locales();	
}
function sec_reporte_graficos_dinero_apostado_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
		auditoria_send({"proceso":"sec_reporte_graficos_dinero_apostado_get_canales_venta","data":data});
		$.ajax({
			data: data,
			type: "POST",
			dataType: "json",
			url: "/api/?json",
		})
		.done(function( data, textStatus, jqXHR ) {
			if ( console && console.log ) {
				$.each(data.data,function(index,val){
					canales_de_venta[val.id]=val.codigo;
					var new_option = $("<option>");
					$(new_option).val(val.id);
					$(new_option).html(val.codigo);
					$(".canalventagraficosdineroapostado").append(new_option);

				});
				$('.canalventagraficosdineroapostado').select2({closeOnSelect: false});
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud canales de ventas a fallado: " +  textStatus);
			}
		})
}
function sec_reportes_graficos_dinero_apostado_get_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_graficos_dinero_apostado_get_locales","data":data});
		$.ajax({
			data: data,
			type: "POST",
			dataType: "json",
			url: "/api/?json",
		})
		.done(function( data, textStatus, jqXHR ) {
			if ( console && console.log ) {
				$.each(data.data,function(index,val){
					var new_option = $("<option>");
					$(new_option).val(val.id);
					$(new_option).html(val.nombre);
					$(".local_reporte_graficos_dinero_apostado").append(new_option);
				});
				$('.local_reporte_graficos_dinero_apostado').select2({closeOnSelect: false});
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud locales a fallado: " +  textStatus);
			}
		})
}
function sec_reportes_graficos_dinero_apostado_api_get(){
	console.log("sec_reportes_graficos_dinero_apostado_api_get");
	loading(true);
	var get_reportes_data = {};
	get_reportes_data.where = "graficos_dinero_apostado";
	get_reportes_data.filtro = {};
	get_reportes_data.filtro.fecha_inicio = $('.reportes_graficos_dinero_apostado_inicio_fecha').val();
	get_reportes_data.filtro.fecha_fin = $('.reportes_graficos_dinero_apostado_fin_fecha').val();
	get_reportes_data.filtro.locales = $('.local_reporte_graficos_dinero_apostado').val();
	get_reportes_data.filtro.canales_de_venta = $('.canalventagraficosdineroapostado').val();
	get_reportes_data.filtro.red_id = $('.red_reportes_graficos').val();
	localStorage.setItem("reportes_graficos_dinero_apostado_inicio_fecha_localstorage",get_reportes_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_graficos_dinero_apostado_fin_fecha_localstorage",get_reportes_data.filtro.fecha_fin);
	$.ajax({
		type: "POST",
		url: "/api/?json",		
		data: get_reportes_data,
		beforeSend: function( xhr ) {
		}
	})
	.done(function(response) {
		try{
			var obj = jQuery.parseJSON(response);
			console.log(obj);
			sec_reportes_graficos_dinero_apostado_process_data(obj);
		}catch(e){
			console.log(e);
			console.log(response);
		}
		loading();
	})
	.fail(function( jqXHR, textStatus, errorThrown ) {
		console.log( "La solicitud reportes a fallado: " +  textStatus);
	});  
}
function sec_reportes_graficos_dinero_apostado_process_data(obj){
	console.log("sec_reportes_graficos_dinero_apostado_process_data");
	$(".table_datos_totales tbody, .table_datos_totales tfoot").html("");
	$(".total_dinero_apostado").html("");
	$(".total_hold").html("");

	var charts = {};
		charts.pie={};
		charts.pie.apostado_general={};
		charts.pie.apostado_general.labels=[];
		charts.pie.apostado_general.data=[];
		charts.pie.apostado_general.backgroundColor=[];

		charts.bar={};
		charts.bar.hold_general={};
		charts.bar.hold_general.labels=[];
		charts.bar.hold_general.data=[];
		charts.bar.hold_general.backgroundColor=[];

	$.each(obj.data, function(cdv_id, cdv_data) {
		var cdv_td = $("<tr>");
		if(cdv_id=="total"){
			$(cdv_td).append('<th>'+cdv_data.canal_de_venta_nombre+'</th>');
			$(cdv_td).append('<th>'+cdv_data.total_apostado+'</th>');
			$(cdv_td).append('<th>'+cdv_data.hold+'%</th>');
			$(cdv_td).append('<th>'+cdv_data.dinero_apostado+'%</th>');
			$(".table_datos_totales tfoot").append(cdv_td);
		}else{
			$(cdv_td).append('<td>'+cdv_data.canal_de_venta_nombre+'</td>');
			$(cdv_td).append('<td>'+cdv_data.total_apostado+'</td>');
			$(cdv_td).append('<td>'+cdv_data.hold+'%</td>');
			$(cdv_td).append('<td>'+cdv_data.dinero_apostado+'%</td>');
			$(".table_datos_totales tbody").append(cdv_td);	

			charts.pie.apostado_general.labels.push(cdv_data.canal_de_venta_nombre);
			charts.pie.apostado_general.data.push(cdv_data.dinero_apostado);
			charts.pie.apostado_general.backgroundColor.push(cdv_data.bg_color);

			charts.bar.hold_general.labels.push(cdv_data.canal_de_venta_nombre);
			charts.bar.hold_general.data.push(cdv_data.hold);
			charts.bar.hold_general.backgroundColor.push(cdv_data.bg_color);
		}

	});

	// console.log(chart_labels);

	var $reportes_graficos_apostado_general = $("#reportes_graficos_apostado_general");
	if ($reportes_graficos_apostado_general.length > 0) {
		new Chart($reportes_graficos_apostado_general, {
			type: 'pie',
			data: {
				labels: charts.pie.apostado_general.labels,
				datasets: [{
					label: 'Apostado General',
					data: charts.pie.apostado_general.data,
					backgroundColor : charts.pie.apostado_general.backgroundColor,
					borderColor: '#fff',
					hoverBorderColor: '#eee',
					borderWidth: 1
				}]
			},
			options:{
				legend:{
					labels: {
						usePointStyle: true
					}
				}
			}
		});
	}

	var $reportes_graficos_hold_general = $("#reportes_graficos_hold_general");
	if ($reportes_graficos_hold_general.length > 0) {
		new Chart($reportes_graficos_hold_general, {
			type: 'bar',
			data: {
				labels: charts.bar.hold_general.labels,
				datasets: [{
					label: 'Hold General',
					data: charts.bar.hold_general.data,
					backgroundColor : charts.bar.hold_general.backgroundColor,
					borderColor: '#fff',
					hoverBorderColor: '#eee',
					borderWidth: 1
				}]
			},
			options:{
				legend:{
					labels: {
						usePointStyle: true
					}
				}
			}
		});
	}
}

function sec_reportes_graficos_dinero_apostado_settings(){
		$('.red_reportes_graficos').select2({
			closeOnSelect: false,            
			allowClear: true,
		});
		$('.reportes_graficos_dinero_apostado_datepicker')
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
		reportes_graficos_dinero_apostado_inicio_fecha_localstorage = localStorage.getItem("reportes_graficos_dinero_apostado_inicio_fecha_localstorage");
		if(reportes_graficos_dinero_apostado_inicio_fecha_localstorage){
			var reportes_graficos_dinero_apostado_inicio_fecha_localstorage_new = moment(reportes_graficos_dinero_apostado_inicio_fecha_localstorage).format("DD-MM-YYYY");
			$("#input_text-reportes_graficos_dinero_apostado_inicio_fecha")
				.datepicker("setDate", reportes_graficos_dinero_apostado_inicio_fecha_localstorage_new)
				.trigger('change');
		}
		reportes_graficos_dinero_apostado_fin_fecha_localstorage = localStorage.getItem("reportes_graficos_dinero_apostado_fin_fecha_localstorage");
		if(reportes_graficos_dinero_apostado_fin_fecha_localstorage){
			
			var reportes_graficos_dinero_apostado_fin_fecha_localstorage_new = moment(reportes_graficos_dinero_apostado_fin_fecha_localstorage).format("DD-MM-YYYY");
			$("#input_text-reportes_graficos_dinero_apostado_fin_fecha")
				.datepicker("setDate", reportes_graficos_dinero_apostado_fin_fecha_localstorage_new)
				.trigger('change');
		}
}
function sec_reportes_graficos_dinero_apostado_events(){
	console.log("sec_reportes_graficos_dinero_apostado_events");
	$(".btn_filtrar_reporte_graficos_dinero_apostado").off().on("click",function(){	
		var btn = $(this).data("button");
		sec_reportes_graficos_dinero_apostado_validacion_permisos_usuarios(btn);
	});		
}
function sec_reportes_graficos_dinero_apostado_validacion_permisos_usuarios(btn){
		$(document).on("evento_validar_permiso_usuario",function(event) {
			$(document).off("evento_validar_permiso_usuario");
			console.log("EVENT: evento_validar_permiso_usuario");
			if (event.event_data==true) {
				console.log(event.event_data);
				loading(true);
				sec_reportes_graficos_dinero_apostado_api_get();
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