var reportes_cobranzas_inicio_fecha_localstorage = false;
var reportes_cobranzas_fin_fecha_localstorage = false;

function sec_reportes_cobranzas(){
	console.log("sec_reportes_cobranzas AS src");
	src_settings();
	src_events();
	src_cobranzas_get();
	sec_reporte_cobranzas_get_canales_venta();
	sec_reporte_cobranzas_get_locales();	
}

function sec_reporte_cobranzas_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
		auditoria_send({"proceso":"sec_recaudacion_get_canales_venta","data":data});
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
					$(".canalventareportecobranzas").append(new_option);

				});
				$('.canalventareportecobranzas').select2({closeOnSelect: false});
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud canales de ventas a fallado en reporte cobranzas: " +  textStatus);
			}
		})
}
function sec_reporte_cobranzas_get_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
		auditoria_send({"proceso":"sec_reporte_cobranzas_get_locales","data":data});
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
					$(".localreportecobranzas").append(new_option);
				});
				$('.localreportecobranzas').select2({closeOnSelect: false});
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud locales a fallado en reporte cobranzas: " +  textStatus);
			}
		})
}
function src_settings(){
	console.log("src_settings");
	
	$('.localreportecobranzas').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.canalventareportecobranzas').select2({
		closeOnSelect: false,            
		allowClear: true,
	});		


	$('.red_reporte_cobranzas').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.reportes_cobranzas_datepicker')
		.datepicker({
			dateFormat:'dd-mm-yy'
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
		});


		reportes_cobranzas_inicio_fecha_localstorage = localStorage.getItem("reportes_cobranzas_inicio_fecha_localstorage");
		if(reportes_cobranzas_inicio_fecha_localstorage){
			var reportes_cobranzas_inicio_fecha_localstorage_new = moment(reportes_cobranzas_inicio_fecha_localstorage).format("DD-MM-YYYY");
			$("#input_text-reportes_cobranzas_inicio_fecha")
				.datepicker("setDate", reportes_cobranzas_inicio_fecha_localstorage_new)
				.trigger('change');
		}

		reportes_cobranzas_fin_fecha_localstorage = localStorage.getItem("reportes_cobranzas_fin_fecha_localstorage");
		if(reportes_cobranzas_fin_fecha_localstorage){
			var reportes_cobranzas_fin_fecha_localstorage_new = moment(reportes_cobranzas_fin_fecha_localstorage).format("DD-MM-YYYY");
			$("#input_text-reportes_cobranzas_fin_fecha")
				.datepicker("setDate", reportes_cobranzas_fin_fecha_localstorage_new)
				.trigger('change');
		}
}
function src_events(){
	console.log("src_events");

	$(".src_cobranzas_get_btn")
		.off()
		.click(function(event) {
			src_cobranzas_get();
	});
	$(".btn_filtrar_reporte_cobranzas").off().on("click",function(){
		var btn = $(this).data("button");
		sec_reportes_cobranzas_validacion_permisos_ususarios(btn);		
	})	
	$(".btn_export_xlsx").off().on("click",function(){
		event.preventDefault();
		var buton = $(this);
		var data = Object();
		data.filtro = Object();	
		data.where="validar_usuario_permiso_botones";			
		$(".input_text_validacion").each(function(index, el) {
			data.filtro[$(el).attr("data-col")]=$(el).val();
		});	
		data.filtro.text_btn = buton.data("button");
		console.log(data);
		auditoria_send({"proceso":"validar_usuario_permiso_botones","data":data});
		$.ajax({
			data: data,
			type: "POST",
			dataType: "json",
			url: "/api/?json"
		})
		.done(function( dataresponse) {
			console.log(dataresponse);
			if (dataresponse.permisos==true) {
				swal({
					title: 'No implementado',
					type: "info",
					timer: 2000,
				}, function(){
					swal.close();
				});	
			}else{
				swal({
					title: 'No tienes permisos',
					type: "info",
					timer: 2000,
				}, function(){
					swal.close();
				});			
			}			
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud validar permisos exportar xlsx a fallado: " +  textStatus);
			}
		})
	})
	$(".btn_export_xls").off().on("click",function(){
		event.preventDefault();
		var buton = $(this);
		var data = Object();
		data.filtro = Object();	
		data.where="validar_usuario_permiso_botones";			
		$(".input_text_validacion").each(function(index, el) {
			data.filtro[$(el).attr("data-col")]=$(el).val();
		});	
		data.filtro.text_btn = buton.data("button");
		console.log(data);
		auditoria_send({"proceso":"validar_usuario_permiso_botones","data":data});
		$.ajax({
			data: data,
			type: "POST",
			dataType: "json",
			url: "/api/?json"
		})
		.done(function( dataresponse) {
			console.log(dataresponse);
			if (dataresponse.permisos==true) {
				swal({
					title: 'No implementado',
					type: "info",
					timer: 2000,
				}, function(){
					swal.close();
				});	
			}else{
				swal({
					title: 'No tienes permisos',
					type: "info",
					timer: 2000,
				}, function(){
					swal.close();
				});			
			}			
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud validar permisos exportar xls a fallado: " +  textStatus);
			}
		})		
	})

}
function src_cobranzas_get(){
	console.log("src_cobranzas_get");

	loading(true);
	var send_data = {};
	send_data.where = "src_cobranzas_get";
	send_data.filtro = {};
	send_data.filtro.fecha_inicio = $('.reportes_cobranzas_inicio_fecha').val();
	send_data.filtro.fecha_fin = $('.reportes_cobranzas_fin_fecha').val();
	send_data.filtro.locales = $('.localreportecobranzas').val();
	send_data.filtro.canales_de_venta = $('.canalventareportecobranzas').val();
	send_data.filtro.red_id = $('.red_reporte_cobranzas').val();
	localStorage.setItem("reportes_cobranzas_inicio_fecha_localstorage",send_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_cobranzas_fin_fecha_localstorage",send_data.filtro.fecha_fin);

	console.log(send_data);
	$.ajax({
		type: "POST",
		url: "/api/?json",
		data: send_data,
		beforeSend: function( xhr ) {
			console.log("START");
		}
	})
	.done(function(responsedata, textStatus, jqXHR ) {
		try{
			var obj = jQuery.parseJSON(responsedata);
			console.log(obj);
		}catch(err){
			console.log(responsedata);
		}
		//sec_reportes_process_data(obj);
		loading();
	})
	.fail(function( jqXHR, textStatus, errorThrown ) {
		console.log("fail:");
		console.log(jqXHR);
		console.log(textStatus);
		console.log(errorThrown);
	});  
}
function sec_reportes_get_cobranzas(){}
function sec_reportes_cobranzas_validacion_permisos_ususarios(btn){
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			console.log(event.event_data);
			loading(true);
			sec_reportes_get_cobranzas();
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