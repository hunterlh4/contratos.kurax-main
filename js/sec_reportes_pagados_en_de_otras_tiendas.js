var reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage = false;
var reportes_pagados_de_otras_tiendas_fin_fecha_localstorage = false;
var $table_pdot = false;
var $table_peot = false;
function sec_reportes_pagados_en_de_otras_tiendas() {
	console.log("sec_reportes_pagados_en_de_otras_tiendas");
	loading(true);
	sec_reportes_pagados_en_de_otras_tiendas_settings();
	sec_reportes_pagados_en_de_otras_tiendas_events();
	sec_reportes_pagados_en_otras_tiendas_get_data_reporte();
	sec_reportes_pagados_de_otras_tiendas_get_data_reporte();
	sec_reportes_pagados_en_de_otras_tiendas_get_canales_venta();
	sec_reportes_pagados_en_de_otras_tiendas_get_locales();
	sec_reportes_pagados_en_de_otras_tiendas_get_redes();	
}

function sec_reportes_pagados_en_de_otras_tiendas_get_redes(){
	var data = {};
		data.what={};
		data.what[0]="id";
		data.what[1]="nombre";
		data.where="redes";
		data.filtro={}
	$.ajax({
		data: data,
		type: "POST",
		dataType: "json",
		url: "/api/?json",
		async: "false"
	})
	.done(function( data, textStatus, jqXHR ) {
		try{
			if ( console && console.log ) {
				$.each(data.data,function(index,val){
					var new_option = $("<option>");
					$(new_option).val(val.id);
					$(new_option).html(val.nombre);
					$(".red_reporte_pagados_de_otras_tiendas").append(new_option);
				});
				$('.red_reporte_pagados_de_otras_tiendas').select2({closeOnSelect: false});
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
			console.log( "La solicitud locales a fallado: " +  textStatus);
		}
	})
}
function sec_reportes_pagados_en_de_otras_tiendas_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_pagados_de_otras_tiendas_get_canales_venta","data":data});
		$.ajax({
			data: data,
			type: "POST",
			dataType: "json",
			url: "/api/?json",
		})
		.done(function( data, textStatus, jqXHR ) {
			try{
				if ( console && console.log ) {
					$.each(data.data,function(index,val){
						canales_de_venta[val.id]=val.codigo;
						var new_option = $("<option>");
						$(new_option).val(val.id);
						$(new_option).html(val.codigo);
						$(".canal_venta_reporte_pagados_de_otras_tiendas").append(new_option);

					});
					$('.canal_venta_reporte_pagados_de_otras_tiendas').select2({closeOnSelect: false});
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
				console.log( "La solicitud canales de ventas a fallado: " +  textStatus);
			}
		})
}
function sec_reportes_pagados_en_de_otras_tiendas_get_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_pagados_de_otras_tiendas_get_locales","data":data});
		$.ajax({
			data: data,
			type: "POST",
			dataType: "json",
			url: "/api/?json",
		})
		.done(function( data, textStatus, jqXHR ) {
			try{
				if ( console && console.log ) {
					$.each(data.data,function(index,val){
						var new_option = $("<option>");
						$(new_option).val(val.id);
						$(new_option).html(val.nombre);
						$(".local_reporte_pagados_de_otras_tiendas").append(new_option);
					});
					$('.local_reporte_pagados_de_otras_tiendas').select2({closeOnSelect: false});
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
				console.log( "La solicitud locales a fallado: " +  textStatus);
			}
		})
}
function sec_reportes_pagados_en_de_otras_tiendas_events(){
	$(".btn_filtrar_reporte_pagados_de_otras_tiendas").off().on("click",function(){
		var btn = $(this).data("button");
		sec_reportes_pagados_en_de_otras_tiendas_validacion_permisos_usuarios(btn);
	});		
	$(".btn_export_pagados_de_xlsx").off().on("click",function(){
console.log("eee")
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
			try{
				console.log(dataresponse);
				if (dataresponse.permisos==true) {
					var reinit = $table_pdot.floatThead('destroy');		
					sec_reportes_ejecutar_reporte_pagados_de_otras_tiendas('xlsx');
					sec_reportes_get_table_to_export_pagados_de_otras_tiendas('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_pagados_de_otras_tiendas.xlsx');
					reinit();
				}else{
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
				console.log( "La solicitud validar permisos exportar xlsx a fallado: " +  textStatus);
			}
		})
	})
	$(".btn_export_pagados_de_xls").off().on("click",function(){
		event.preventDefault();
		var buton = $(this);
		var data = Object();
		data.filtro = Object();	
		data.where="validar_usuario_permiso_botones";			
		$(".input_text_validacion").each(function(index, el) {
			data.filtro[$(el).attr("data-col")]=$(el).val();
		});	
		data.filtro.text_btn = buton.data("button");
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
					var reinit = $table_pdot.floatThead('destroy');
					sec_reportes_ejecutar_reporte_pagados_de_otras_tiendas('biff2', 'reporte_pagados_de_otras_tiendas.xls');
					sec_reportes_get_table_to_export_pagados_de_otras_tiendas('biff2btn', 'xportbiff2', 'biff2', 'reporte_pagados_de_otras_tiendas.xls');				
				 	reinit();	
				}else{
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
				console.log( "La solicitud validar permisos exportar xls  a fallado: " +  textStatus);
			}
		})
	})
	$(".btn_export_pagados_en_xlsx").off().on("click",function(){
		event.preventDefault();
		var buton = $(this);
		var data = Object();
		data.filtro = Object();	
		data.where="validar_usuario_permiso_botones";			
		$(".input_text_validacion").each(function(index, el) {
			data.filtro[$(el).attr("data-col")]=$(el).val();
		});	
		data.filtro.text_btn = buton.data("button");
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
					var reinit = $table_peot.floatThead('destroy');	
					sec_reportes_ejecutar_reporte_pagados_en_otras_tiendas('xlsx');
					sec_reportes_get_table_to_export_pagados_en_otras_tiendas('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_pagados_en_otras_tiendas.xlsx');			
					reinit();
				}else{
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
				console.log( "La solicitud validar permisos exportar xlsx a fallado: " +  textStatus);
			}
		})
	})
	$(".btn_export_pagados_en_xls").off().on("click",function(){
		event.preventDefault();
		var buton = $(this);
		var data = Object();
		data.filtro = Object();	
		data.where="validar_usuario_permiso_botones";			
		$(".input_text_validacion").each(function(index, el) {
			data.filtro[$(el).attr("data-col")]=$(el).val();
		});	
		data.filtro.text_btn = buton.data("button");
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
					var reinit = $table_peot.floatThead('destroy');
					sec_reportes_ejecutar_reporte_pagados_en_otras_tiendas('biff2', 'reporte_pagados_en_otras_tiendas.xls');
					sec_reportes_get_table_to_export_pagados_en_otras_tiendas('biff2btn', 'xportbiff2', 'biff2', 'reporte_pagados_en_otras_tiendas.xls');				
				 	reinit();
				}else{
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
				console.log( "La solicitud validar permisos exportar xls a fallado: " +  textStatus);
			}
		})
	
	})	
    $table_pdot = $('#tabla_pagados_de_otras_tiendas');
    $table_pdot.floatThead({
        top:50     
    });	
    $table_peot = $('#tabla_pagados_en_otras_tiendas');
    $table_peot.floatThead({
        top:50     
    });    
	$('td').each(function() {
		var cellvalue = $(this).html();
		if ( cellvalue < 0) {
			$(this).wrapInner('<strong class="negative_number_pdot"></strong>');    
		}
	});   	
}
function sec_reportes_pagados_en_de_otras_tiendas_settings(){
	$('.local_reporte_pagados_de_otras_tiendas').select2({
		closeOnSelect: false,            
		allowClear: true,
	});
	$('.canal_venta_reporte_pagados_de_otras_tiendas').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.red_reporte_pagados_de_otras_tiendas').select2({
		closeOnSelect: false,            
		allowClear: true,
	});
	$('.reportes_pagados_de_otras_tiendas_datepicker')
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
	reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage = localStorage.getItem("reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage");
	if(reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage){
		var reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage_new = moment(reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_pagados_de_otras_tiendas_inicio_fecha")
			.datepicker("setDate", reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage_new)
			.trigger('change');
	}

	reportes_pagados_de_otras_tiendas_fin_fecha_localstorage = localStorage.getItem("reportes_pagados_de_otras_tiendas_fin_fecha_localstorage");
	if(reportes_pagados_de_otras_tiendas_fin_fecha_localstorage){
		var reportes_pagados_de_otras_tiendas_fin_fecha_localstorage_new = moment(reportes_pagados_de_otras_tiendas_fin_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_pagados_de_otras_tiendas_fin_fecha")
			.datepicker("setDate", reportes_pagados_de_otras_tiendas_fin_fecha_localstorage_new)
			.trigger('change');
	}
}
function sec_reportes_pagados_de_otras_tiendas_get_data_reporte(){
	var get_pagados_en_de_data = {};
	get_pagados_en_de_data.where = "pagados_de_otras_tiendas";
	get_pagados_en_de_data.filtro = {};
	get_pagados_en_de_data.filtro.fecha_inicio = $('.reportes_pagados_de_otras_tiendas_inicio_fecha').val();
	get_pagados_en_de_data.filtro.fecha_fin = $('.reportes_pagados_de_otras_tiendas_fin_fecha').val();
	get_pagados_en_de_data.filtro.locales = $('.local_reporte_pagados_de_otras_tiendas').val();
	get_pagados_en_de_data.filtro.canales_de_venta = $('.canal_venta_reporte_pagados_de_otras_tiendas').val();
	get_pagados_en_de_data.filtro.red_id=$('.red_reporte_pagados_de_otras_tiendas').val();
	localStorage.setItem("reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage",get_pagados_en_de_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_pagados_de_otras_tiendas_fin_fecha_localstorage",get_pagados_en_de_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_reportes_pagados_de_otras_tiendas_get_data_reporte","data":get_pagados_en_de_data});
    $.ajax({
		url: "/api/?json",
        type: 'POST',
        data:get_pagados_en_de_data
    })
    .done(function(dataresponse) {
		try{
			console.log(dataresponse);
	        var obj = JSON.parse(dataresponse);
	        sec_reportes_pagados_de_otras_tiendas_create_table(obj);
		}catch(err){
			console.log(err);
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
    .fail(function() {
        console.log("error reportes pagados en otras tiendas");
    })
    .always(function() {
        console.log("complete reportes pagados en otras tiendas");
    });
}
function sec_reportes_pagados_en_otras_tiendas_get_data_reporte(){
		var get_pagados_en_de_data = {};
		get_pagados_en_de_data.where = "pagados_en_otras_tiendas";
		get_pagados_en_de_data.filtro = {};
		get_pagados_en_de_data.filtro.fecha_inicio = $('.reportes_pagados_de_otras_tiendas_inicio_fecha').val();
		get_pagados_en_de_data.filtro.fecha_fin = $('.reportes_pagados_de_otras_tiendas_fin_fecha').val();
		get_pagados_en_de_data.filtro.locales = $('.local_reporte_pagados_de_otras_tiendas').val();
		get_pagados_en_de_data.filtro.canales_de_venta = $('.canal_venta_reporte_pagados_de_otras_tiendas').val();
		get_pagados_en_de_data.filtro.red_id=$('.red_reporte_pagados_de_otras_tiendas').val();
		localStorage.setItem("reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage",get_pagados_en_de_data.filtro.fecha_inicio);
		localStorage.setItem("reportes_pagados_de_otras_tiendas_fin_fecha_localstorage",get_pagados_en_de_data.filtro.fecha_fin);	
		auditoria_send({"proceso":"sec_reportes_pagados_de_otras_tiendas_get_data_reporte","data":get_pagados_en_de_data});
	    $.ajax({
			url: "/api/?json",
	        type: 'POST',
	        data:get_pagados_en_de_data
	    })
	    .done(function(dataresponse) {
			try{
		        var obj = JSON.parse(dataresponse);
		        console.log(obj);
		        sec_reportes_pagados_en_otras_tiendas_create_table(obj);	
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
	    .fail(function() {
	        console.log("error reportes pagados en otras tiendas");
	    })
	    .always(function() {
	        console.log("complete reportes pagados en otras tiendas");
	    });
}
function sec_reportes_pagados_de_otras_tiendas_create_table(obj){
	var info_local_array = [];
	$.each(obj.data, function(id_local_pago, val_local) {
		if (id_local_pago!="total") {
			$.each(val_local, function(id_local_origen, val_detalles) {
				if (id_local_origen!="total") {
					var info_dot = {};
					info_dot.origen = val_detalles.origen;
					info_dot.origen_local_id = val_detalles.origen_local_id;
					info_dot.razon_social = val_detalles.razon_social;
					info_dot.razon_social_p = val_detalles.razon_social_p;
					info_dot.pagado = val_detalles.pagado;
					info_dot.pago = val_detalles.pago;
					info_dot.pago_local_id = val_detalles.pago_local_id;
					info_local_array.push(info_dot);
				};
				
			});
		};
	});
	var info_sub_totales_array = [];
	$.each(obj.data, function(id_local_pago, val_local) {
		if (id_local_pago!="total") {
			$.each(val_local, function(id_local_origen, val_detalles) {
				if (id_local_origen=="total") {
					info_sub_totales_array[id_local_pago] = val_detalles;
				};
			});
		};
	});
	var info_super_total_array = [];
	$.each(obj.data, function(id_local_pago, val_local) {
		if (id_local_pago=="total") {
			info_super_total_array["total"]= val_local;
		}
	});
	var html ="<table id='tabla_pagados_de_otras_tiendas' class='tabla_pagados_de_otras_tiendas' width='100%' >";
			html += "<thead style='background-color:#70ad47; color:#fafafa !important;'>";
				html += "<tr>";
					html += "<th class='sec_rep_pdot_tienda_pago_th'>TIENDA DE PAGO</th>";
					html += "<th class='sec_rep_pdot_tienda_pago_razon_social_th'>RAZON SOCIAL</th>";
					html += "<th class='sec_rep_pdot_tienda_origen_th'>TIENDA DE ORIGEN</th>";
					html += "<th class='sec_rep_pdot_tienda_origen_razon_social_th'>RAZON SOCIAL</th>";
					html += "<th class='sec_rep_pdot_cantidad_pago_th border_standar_tiendas_de_en_pagar'>PAGOS DE OTRAS TIENDAS</th>";
				html += "</tr>";
			html += "</thead>";
			html +="<tbody>";
				$.each(info_local_array, function(index_local_totales, val_detalles) {
					html+="<tr>";
					html += "<td class='sec_rep_pdot_tienda_pago'>"+val_detalles.pago+"</td>";
					html += "<td class='sec_rep_pdot_tienda_pago_razon_social border_personalizado_tiendas_de_en_pagar'>"+val_detalles.razon_social_p+"</td>";
					html += "<td class='sec_rep_pdot_tienda_origen border_personalizado_tiendas_de_en_pagar'>"+val_detalles.origen+"</td>";
					html += "<td class='sec_rep_pdot_tienda_origen_razon_social border_personalizado_tiendas_de_en_pagar'>"+val_detalles.razon_social+"</td>";
					html += "<td class='sec_rep_pdot_cantidad_pagado border_standar_tiendas_de_en_pagar'>"+val_detalles.pagado+"</td>";										
					html+="</tr>";
		    		if (info_local_array.length>parseInt(index_local_totales)+1) {
						var next_index = parseInt(index_local_totales)+parseInt(1);
						var next_info_local_array = info_local_array[next_index].pago_local_id;
						var detalles_local_id= val_detalles.pago_local_id;
           
		                if(detalles_local_id!=next_info_local_array){  
		                	var detalles_locales_id = parseInt(val_detalles.pago_local_id);  
								html+="<tr class='sub_totales_pdot'>";
									html += "<td class='sec_rep_pdot_nombre_total_tienda'>"+info_sub_totales_array[val_detalles.pago_local_id].pago+"</td>";
									html += "<td class='sec_rep_pdot_tienda_pago_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_pdot_tienda_origen border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_pdot_tienda_origen_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_pdot_cantidad_pagado border_standar_tiendas_de_en_pagar'>"+info_sub_totales_array[val_detalles.pago_local_id].pagado+"</td>";										
								html+="</tr>";
		                }   
		            };
		            if (info_local_array.length-1==index_local_totales) {
								html+="<tr class='sub_totales_pdot'>";
									html += "<td class='sec_rep_pdot_nombre_total_tienda'>"+info_sub_totales_array[val_detalles.pago_local_id].pago+"</td>";
									html += "<td class='sec_rep_pdot_tienda_pago_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_pdot_tienda_origen border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_pdot_tienda_origen_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_pdot_cantidad_pagado border_standar_tiendas_de_en_pagar'>"+info_sub_totales_array[val_detalles.pago_local_id].pagado+"</td>";										
								html+="</tr>";		            	
		            };
				});
				html+="<tr class='total_general_pdot'>";
					html += "<td class='sec_rep_pdot_nombre_total_tienda'>Total General</td>";
					html += "<td class='sec_rep_pdot_tienda_pago_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
					html += "<td class='sec_rep_pdot_tienda_origen border_personalizado_tiendas_de_en_pagar'></td>";
					html += "<td class='sec_rep_pdot_tienda_origen_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
					html += "<td class='sec_rep_pdot_cantidad_pagado border_standar_tiendas_de_en_pagar'>"+info_super_total_array["total"]+"</td>";										
				html+="</tr>";		            	
			html +="</tbody>";
		html += "</table>";
	$(".tabla_contenedor_reporte_pagados_de_otras_tiendas").html(html);
	sec_reportes_pagados_en_de_otras_tiendas_events();
	loading();
	
}
function sec_reportes_pagados_en_otras_tiendas_create_table(obj){
	var info_local_array = [];
	$.each(obj.data, function(id_local_pago, val_local) {
		if (id_local_pago!="total") {
			$.each(val_local, function(id_local_origen, val_detalles) {
				if (id_local_origen!="total") {
					var info_dot = {};
					info_dot.origen = val_detalles.origen;
					info_dot.razon_social = val_detalles.razon_social;
					info_dot.razon_social_p = val_detalles.razon_social_p;
					info_dot.origen_local_id = val_detalles.origen_local_id;
					info_dot.pagado = val_detalles.pagado;
					info_dot.pago = val_detalles.pago;
					info_dot.pago_local_id = val_detalles.pago_local_id;
					info_local_array.push(info_dot);
				};
				
			});
		};
	});
	var info_sub_totales_array = [];
	$.each(obj.data, function(id_local_pago, val_local) {
		if (id_local_pago!="total") {
			$.each(val_local, function(id_local_origen, val_detalles) {
				if (id_local_origen=="total") {
					info_sub_totales_array[id_local_pago] = val_detalles;
				};
			});
		};
	});
	var info_super_total_array = [];
	$.each(obj.data, function(id_local_pago, val_local) {
		if (id_local_pago=="total") {
			info_super_total_array["total"]= val_local;
		}
	});
	var html ="<table id='tabla_pagados_en_otras_tiendas' class='tabla_pagados_en_otras_tiendas' width='100%' >";
			html += "<thead style='background-color:#70ad47; color:#fafafa !important;'>";
				html += "<tr>";
					html += "<th class='sec_rep_peot_tienda_origen_th'>TIENDA DE ORIGEN</th>";
					html += "<th class='sec_rep_peot_tienda_origen_razon_social_th'>RAZON SOCIAL</th>";
					html += "<th class='sec_rep_peot_tienda_pago_th'>TIENDA DE PAGO</th>";
					html += "<th class='sec_rep_peot_tienda_pago_razon_social_th'>RAZON SOCIAL</th>";
					html += "<th class='sec_rep_peot_cantidad_pago_th'>PAGOS EN OTRAS TIENDAS</th>";
				html += "</tr>";
			html += "</thead>";
			html +="<tbody>";
				$.each(info_local_array, function(index_local_totales, val_detalles) {
					html+="<tr>";
					html += "<td class='sec_rep_peot_tienda_pago' style=''>"+val_detalles.origen+"</td>";
					html += "<td class='sec_rep_peot_tienda_origen_razon_social border_personalizado_tiendas_de_en_pagar'>"+val_detalles.razon_social+"</td>";
					html += "<td class='sec_rep_peot_tienda_origen border_personalizado_tiendas_de_en_pagar'>"+val_detalles.pago+"</td>";
					html += "<td class='sec_rep_peot_tienda_pago_razon_social border_personalizado_tiendas_de_en_pagar'>"+val_detalles.razon_social_p+"</td>";
					html += "<td class='sec_rep_peot_cantidad_pagado border_standar_tiendas_de_en_pagar'>"+val_detalles.pagado+"</td>";										
					html+="</tr>";
		    		if (info_local_array.length>parseInt(index_local_totales)+1) {
						var next_index = parseInt(index_local_totales)+parseInt(1);
						var next_info_local_array = info_local_array[next_index].origen_local_id;
						var detalles_local_id= val_detalles.origen_local_id;
           
		                if(detalles_local_id!=next_info_local_array){  
		                	var detalles_locales_id = parseInt(val_detalles.origen_local_id);  
								html+="<tr class='sub_totales_peot'>";
									html += "<td class='sec_rep_peot_nombre_total_tienda'>"+info_sub_totales_array[val_detalles.origen_local_id].origen+"</td>";
									html += "<td class='sec_rep_peot_tienda_origen_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_peot_tienda_origen border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_peot_tienda_pago_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_peot_cantidad_pagado border_standar_tiendas_de_en_pagar'>"+info_sub_totales_array[val_detalles.origen_local_id].pagado+"</td>";										
								html+="</tr>";
		                }   
		            };
		            if (info_local_array.length-1==index_local_totales) {
								html+="<tr class='sub_totales_peot'>";
									html += "<td class='sec_rep_peot_nombre_total_tienda'>"+info_sub_totales_array[val_detalles.origen_local_id].origen+"</td>";
									html += "<td class='sec_rep_peot_tienda_origen_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_peot_tienda_origen border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_peot_tienda_pago_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
									html += "<td class='sec_rep_peot_cantidad_pagado border_standar_tiendas_de_en_pagar'>"+info_sub_totales_array[val_detalles.origen_local_id].pagado+"</td>";										
								html+="</tr>";		            	
		            };
				});
				html+="<tr class='total_general_peot'>";
					html += "<td class='sec_rep_peot_nombre_total_tienda'>Total General</td>";
					html += "<td class='sec_rep_peot_tienda_origen_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
					html += "<td class='sec_rep_peot_tienda_origen border_personalizado_tiendas_de_en_pagar'></td>";
					html += "<td class='sec_rep_peot_tienda_pago_razon_social border_personalizado_tiendas_de_en_pagar'></td>";
					html += "<td class='sec_rep_peot_cantidad_pagado border_standar_tiendas_de_en_pagar'>"+info_super_total_array["total"]+"</td>";										
				html+="</tr>";		            	
			html +="</tbody>";
		html += "</table>";
	$(".tabla_contenedor_reporte_pagados_en_otras_tiendas").html(html);
	sec_reportes_pagados_en_de_otras_tiendas_events();
	loading();
}

function sec_reportes_ejecutar_reporte_pagados_de_otras_tiendas(type, fn) { 
   	return sec_reportes_export_table_to_excel_pagados_de_otras_tiendas('tabla_pagados_de_otras_tiendas', type || 'xlsx', fn); 
}  
function sec_reportes_validar_exportacion_pagados_de_otras_tiendas(s) {
   if(typeof ArrayBuffer !== 'undefined') {
     var buf = new ArrayBuffer(s.length);
     var view = new Uint8Array(buf);
     for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
     return buf;
   } else {
     var buf = new Array(s.length);
     for (var i=0; i!=s.length; ++i) buf[i] = s.charCodeAt(i) & 0xFF;
     return buf;
   }
}
function sec_reportes_export_table_to_excel_pagados_de_otras_tiendas(id, type, fn) {
	 var wb = XLSX.utils.table_to_book(document.getElementById(id), {raw:true}, {sheet:"Sheet JS"});     
     var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
     var fname = fn || $(".export_filename_reporte_pagado_en_de").val()+"."+ type;
     try {
       saveAs(new Blob([sec_reportes_validar_exportacion_apuestas(wbout)],{type:"application/octet-stream"}), fname);
     } catch(e) { if(typeof console != 'undefined') console.log(e, wbout); }
     return wbout;     
}
function sec_reportes_get_table_to_export_pagados_de_otras_tiendas(pid, iid, fmt, ofile) {
   if(fallback) {
     if(document.getElementById(iid)) document.getElementById(iid).hidden = true; 
     Downloadify.create(pid,{
       swf: 'media/downloadify.swf',
       downloadImage: 'download.png',
       width: 100,
       height: 30,
       filename: ofile, data: function() { var o = sec_reportes_ejecutar_reporte_pagados_de_otras_tiendas(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";
}


function sec_reportes_ejecutar_reporte_pagados_en_otras_tiendas(type, fn) { 
   	return sec_reportes_export_table_to_excel_pagados_en_otras_tiendas('tabla_pagados_en_otras_tiendas', type || 'xlsx', fn); 
}  
function sec_reportes_validar_exportacion_pagados_en_otras_tiendas(s) {
   if(typeof ArrayBuffer !== 'undefined') {
     var buf = new ArrayBuffer(s.length);
     var view = new Uint8Array(buf);
     for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
     return buf;
   } else {
     var buf = new Array(s.length);
     for (var i=0; i!=s.length; ++i) buf[i] = s.charCodeAt(i) & 0xFF;
     return buf;
   }
}
function sec_reportes_export_table_to_excel_pagados_en_otras_tiendas(id, type, fn) {
	 var wb = XLSX.utils.table_to_book(document.getElementById(id), {raw:true}, {sheet:"Sheet JS"});     
     var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
     var fname = fn || $(".export_filename_reporte_pagado_en_de").val()+"."+ type;
     try {
       saveAs(new Blob([sec_reportes_validar_exportacion_apuestas(wbout)],{type:"application/octet-stream"}), fname);
     } catch(e) { if(typeof console != 'undefined') console.log(e, wbout); }
     return wbout; 
}
function sec_reportes_get_table_to_export_pagados_en_otras_tiendas(pid, iid, fmt, ofile) {
   if(fallback) {
     if(document.getElementById(iid)) document.getElementById(iid).hidden = true; 
     Downloadify.create(pid,{
       swf: 'media/downloadify.swf',
       downloadImage: 'download.png',
       width: 100,
       height: 30,
       filename: ofile, data: function() { var o = sec_reportes_ejecutar_reporte_pagados_en_otras_tiendas(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";
}
function sec_reportes_pagados_en_de_otras_tiendas_validacion_permisos_usuarios(btn){
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			console.log(event.event_data);
			loading(true);
			sec_reportes_pagados_de_otras_tiendas_get_data_reporte();
			sec_reportes_pagados_en_otras_tiendas_get_data_reporte();
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