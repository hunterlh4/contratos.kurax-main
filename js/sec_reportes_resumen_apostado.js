var reportes_resumen_apostado_inicio_fecha_localstorage = false;
var reportes_resumen_apostado_fin_fecha_localstorage = false;
var table_rap = false;
var all_periods="";
var cantidad_meses=0;
function sec_reportes_resumen_apostado(){
	console.log("sec_reportes_resumen_apostado");
	sec_reportes_resumen_apostado_settings();
	sec_reportes_resumen_apostado_events();
	loading(true);
	sec_reportes_resumen_apostado_get_data_reporte();
	sec_reportes_resumen_apostado_get_canales_venta();
	sec_reportes_resumen_apostado_get_locales();
	sec_reportes_resumen_apostado_get_redes();
}

function sec_reportes_resumen_apostado_get_redes(){
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
					$(".red_reporte_resumen_apostado").append(new_option);
				});
				$('.red_reporte_resumen_apostado').select2({closeOnSelect: false});
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
function sec_reportes_resumen_apostado_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_resumen_apostado_get_canales_venta","data":data});
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
						$(".canal_venta_reporte_resumen_apostado").append(new_option);

					});
					$('.canal_venta_reporte_resumen_apostado').select2({closeOnSelect: false});
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
function sec_reportes_resumen_apostado_get_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_resumen_apostado_get_locales","data":data});
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
						$(".local_reporte_resumen_apostado").append(new_option);
					});
					$('.local_reporte_resumen_apostado').select2({closeOnSelect: false});
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
function sec_reportes_resumen_apostado_events(){
	$(".btn_filtrar_reporte_resumen_apostado").off().on("click",function(){
		var btn = $(this).data("button");
		sec_reportes_resumen_apostado_validacion_permisos_usuarios(btn);
	})	
	$(".btn_export_resumen_apostado_xlsx").off().on("click",function(){
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
					var reinit = $table_rap.floatThead('destroy');		
					sec_reportes_ejecutar_reporte_resumen_apostado('xlsx');
					sec_reportes_get_table_to_export_reporte_resumen_apostado('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_resumen_apostado.xlsx');
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
	$(".btn_export_resumen_apostado_xls").off().on("click",function(){
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
					var reinit = $table_rap.floatThead('destroy');
					sec_reportes_ejecutar_reporte_resumen_apostado('biff2', 'reporte_resumen_apostado.xls');
					sec_reportes_get_table_to_export_reporte_resumen_apostado('biff2btn', 'xportbiff2', 'biff2', 'reporte_resumen_apostado.xls');				
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
				console.log( "La solicitud validar permisos  a fallado: " +  textStatus);
			}
		})
	})
    $table_rap = $('.tabla_reportes_resumen_apostado');
    $table_rap.floatThead({
    	top:50
    });
	expand_collapse_columns_resumen_apostado($table_rap);
}
function sec_reportes_resumen_apostado_settings(){
	$('.local_reporte_resumen_apostado').select2({
		closeOnSelect: false,            
		allowClear: true,
	});
	$('.canal_venta_reporte_resumen_apostado').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.red_reporte_resumen_apostado').select2({
		closeOnSelect: false,            
		allowClear: true,
	});
	$('.reportes_resumen_apostado_datepicker')
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
	reportes_resumen_apostado_inicio_fecha_localstorage = localStorage.getItem("reportes_resumen_apostado_inicio_fecha_localstorage");
	if(reportes_resumen_apostado_inicio_fecha_localstorage){
		var reportes_resumen_apostado_inicio_fecha_localstorage_new = moment(reportes_resumen_apostado_inicio_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_resumen_apostado_inicio_fecha")
			.datepicker("setDate", reportes_resumen_apostado_inicio_fecha_localstorage_new)
			.trigger('change');
	}

	reportes_resumen_apostado_fin_fecha_localstorage = localStorage.getItem("reportes_resumen_apostado_fin_fecha_localstorage");
	if(reportes_resumen_apostado_fin_fecha_localstorage){
		var reportes_resumen_apostado_fin_fecha_localstorage_new = moment(reportes_resumen_apostado_fin_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_resumen_apostado_fin_fecha")
			.datepicker("setDate", reportes_resumen_apostado_fin_fecha_localstorage_new)
			.trigger('change');
	}	
}
function sec_reportes_resumen_apostado_get_data_reporte(){
	var get_resumen_apostado_data = {};
	get_resumen_apostado_data.where = "resumen_apostado";
	get_resumen_apostado_data.filtro = {};
	get_resumen_apostado_data.filtro.fecha_inicio = $('.reportes_resumen_apostado_inicio_fecha').val();
	get_resumen_apostado_data.filtro.fecha_fin = $('.reportes_resultado_apuestas_fin_fecha').val();
	get_resumen_apostado_data.filtro.locales = $('.local_reporte_resumen_apostado').val();
	get_resumen_apostado_data.filtro.canales_de_venta = $('.canal_venta_reporte_resumen_apostado').val();
	get_resumen_apostado_data.filtro.red_id=$('.red_reporte_resumen_apostado').val();

	localStorage.setItem("reportes_resumen_apostado_inicio_fecha_localstorage",get_resumen_apostado_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_resumen_apostado_fin_fecha_localstorage",get_resumen_apostado_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_reportes_resumen_apostado_get_data_reporte","data":get_resumen_apostado_data});
    $.ajax({
		url: "/api/?json",
        type: 'POST',
        data:get_resumen_apostado_data
    })
    .done(function(dataresponse) {
		try{
	        var obj = JSON.parse(dataresponse);
    		console.log(obj);
	        sec_reportes_resumen_apostado_create_table(obj);	
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
        console.log("error");
    })
    .always(function() {
        console.log("complete");
    });
}
function sec_reportes_resumen_apostado_create_table(obj){
	var nombre_mes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];  
	try{
		var obj = jQuery.parseJSON(r);
	}catch(err){
	}
	var cols = Object();
		cols["total_apostado"]="Dinero Apostado";
	
	var cdv = Object();
		cdv[15]="Web";
		cdv[16]="PBET";
		cdv[17]="SBT-Negocios";
		cdv[18]="JV Global Bet";
		cdv[19]="Tablet BC";
		cdv[20]="SBT-BC";
		cdv[21]="JV Golden Race";

	var period_inicio = false;
	var period_fin = false;
	var period_arr = Object();
	var array_meses_thead=[];
	var array_anios_thead=[];	
	var count_months=0;
	var array_meses_reorder=[];
	var array_anios_reorder=[];	
	$.each(obj.resumen, function(year_index, year_data) {
		$.each(year_data, function(month_year, month_data) {
			array_meses_thead[count_months]=month_year;
			array_anios_thead[count_months]=year_index;
			if(!period_inicio){
				period_inicio = year_index+""+month_year;
			}
			period_fin = year_index+""+month_year;
			period_arr[year_index+""+month_year]=true;
			count_months++;
		});
	});
	array_meses_count=count_months;
	array_anios_reorder=array_anios_thead.sort();
	array_meses_reorder=array_meses_thead.sort();
	var periodo_inicio = array_anios_reorder[0]+""+array_meses_reorder[0];

	cantidad_meses=count_months;
	var html_table = $("<table class='tabla_reportes_resumen_apostado table table-bordered'>").attr("id","tabla_reportes_resumen_apostado").attr("width","100%");
	var html_thead=$("<thead>");
	var html_tr = $("<tr>");
		html_tr.append('<th rowspan="3" class="cabecera_canal_venta_resumen_apostado">Canal de venta</th>');
	$.each(obj.resumen, function(year_index, year_data) {
		var year_th_td = $("<th class='cabecera_anio_resumen_apostado' id='cabeceraanio_resumen_apostado_"+year_index+"'>").attr("rowspan","1").attr("colspan",(Object.keys(year_data).length * Object.keys(cols).length)).html("<button class='btn_show_year_resumen_apostado' id='"+year_index+"'>+</button><button class='btn_hide_year_resumen_apostado' id='"+year_index+"'>-</button>"+year_index);
		html_tr.append(year_th_td);
	});
	html_thead.append(html_tr);
	var html_tr = $("<tr>");
	var array_final_months_names=[];
	array_final_months_names=array_meses_thead.sort();
	var count_final_month_names=0;
	$.each(obj.resumen, function(year_index, year_data) {
		$.each(year_data, function(month_year, month_data) {
			all_periods+=year_index+''+month_year+'_';
			var month_th_td = $("<th class='cabecera_mes cabecera_meses_del_anio_resumen_apostado ocultar"+count_final_month_names+"' data-period="+year_index+''+month_year+" id='cabecera_"+year_index+''+month_year+"'>").attr("colspan",1).html(nombre_mes[parseInt(array_final_months_names[count_final_month_names])-1]);
			html_tr.append(month_th_td);
			count_final_month_names++;
		});
	});
	html_thead.append(html_tr);

	var html_tr = $("<tr>");
	var count_cabecera_dinero_apostado=0;
	$.each(obj.resumen, function(year_index, year_data) {
		$.each(year_data, function(month_year, month_data) {
			$.each(cols, function(col_index, col_data) {
				var options="";
				if (col_index=="total_apostado") {
					options ="<th class='cabecera_dinero_apostado_resumen_apostado ocultar"+count_cabecera_dinero_apostado+"'>" ;      
				}
				var year_th_td = $(options).html(col_data);
				html_tr.append(year_th_td);
				count_cabecera_dinero_apostado++;
			});
		});
	});

	html_thead.append(html_tr);
	html_table.append(html_thead);	
	var new_obj = Array();
	$.each(obj.resumen, function(year_index, months_data) {
		$.each(months_data, function(month_index, csdv_data) {
			$.each(csdv_data, function(cdv_id, locales_data) {
				$.each(locales_data, function(local_id, local_data) {
					var local = {};
						local.year = year_index;
						local.month = month_index;
						local.period = year_index+""+month_index;
						local.canal_de_venta_id = local_data.canal_de_venta_id;
						local.canal_de_venta = local_data.canal_de_venta;
 						local.total_apostado = local_data.total_apostado;
					new_obj.push(local);
				});
			});
		});
	});
	var totales_array = Array();
	$.each(obj.totales, function(year_index, months_data) {
		$.each(months_data, function(month_index,csdv_data) {
			$.each(csdv_data, function(cdv_id, total_local_data) {
				if (cdv_id!="total") {
					if (month_index!="total" ) {
						var total = {};//$.extend({},total_local_data);
							total.year = year_index;                
							total.month = month_index;
							total.period = year_index+""+month_index; 
							total.canal_de_venta_id= cdv_id;
							total.canal_de_venta = total_local_data.canal_de_venta;
	 						total.total_apostado = total_local_data.total_apostado;							
						totales_array.push(total);
					}
				}
			});
		});
	});
	var super_total_array = Array();
	$.each(obj.totales, function(year_index, months_data) {
		$.each(months_data, function(month_index,csdv_data) {
			$.each(csdv_data, function(cdv_id, total_local_data) {
				if (cdv_id=="total") {
					if (month_index!="total" ) {
						var super_total = {};//$.extend({},total_local_data);
							super_total.year = year_index;
							super_total.month = month_index;
							super_total.period = year_index+""+month_index; 
							super_total.canal_de_venta_id= cdv_id;
							//total.canal_de_venta_id= cdv_id;
							super_total.canal_de_venta = total_local_data.canal_de_venta;
	 						super_total.total_apostado = total_local_data.total_apostado;								
						super_total_array.push(super_total); 
					}
				}
			});
		});
	});
	var obj_by_period = Object();
	var obj_by_canal = Object();
	var obj_local = Object();
	$.each(new_obj, function(n_in, n_val) {
		if(!obj_by_period[n_val.period]){
			obj_by_period[n_val.period]=Object();

		}
		obj_by_period[n_val.period][n_val.canal_de_venta_id]=n_val;
	});
	var obj_total_by_period = Object();
	$.each(totales_array, function(n_in,n_val) {
		if (!obj_total_by_period[n_val.period]) {
			obj_total_by_period[n_val.period]=Object();
		}
		obj_total_by_period[n_val.period][n_val.canal_de_venta_id]=n_val;
	});
	var obj_super_total_by_period = Object();
	$.each(super_total_array, function(n_in,n_val) {
		if (!obj_super_total_by_period[n_val.period]) {
			obj_super_total_by_period[n_val.period]=Object();
		}
		obj_super_total_by_period[n_val.period][n_val.canal_de_venta_id]=n_val;
	});
	$.each(cdv, function(cdv_index, cdv_nombre) {
		$.each(new_obj, function(obj_index, obj_data) {
			if(obj_data.canal_de_venta_id == cdv_index){
				if(obj_data.period == periodo_inicio){
					var html_tr = $('<tr>');
						html_tr.append('<td class="">'+cdv_nombre+'</td>');
						html_tr.append('<td class="">'+obj_data.nombre+'</td>');
						html_tr.append('<td class="">'+obj_data.propiedad+'</td>');
						html_tr.append('<td class="">'+obj_data.administracion+'</td>');
						html_tr.append('<td class="">'+obj_data.tipo+'</td>');
						html_tr.append('<td class="">-</td>');
						var count_td =0;
						if(new_obj.length > obj_index+1){
							var next_object = new_obj[obj_index+1];
							if(obj_data.canal_de_venta_id.localeCompare(next_object.canal_de_venta_id) != 0){
								var html_tr1 = $('<tr class="" >');
									html_tr1.append('<td colspan="1" class="etiqueta_total_resultado_apostado ">'+cdv_nombre+'</td>');                          
								$.each(period_arr, function(period_index, period_val) {
									$.each(cols, function(col_index, col_data) {
										if(obj_total_by_period[period_index][obj_data.canal_de_venta_id]){
											if(obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]){
												if(col_index=="total_apostado"){
													html_tr1.append('<td class="mostrado ocultar'+count_td+'">'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');
												}else{
													html_tr1.append('<td class="'+period_index +' ocultar'+count_td+' ">'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');  
												}
											}else{
												if (col_index=="total_apostado") {
													html_tr1.append('<td class="mostrado ocultar ocultar'+count_td+'">0</td>');
												}else{
													html_tr1.append('<td class="'+period_index+' ocultar ocultar'+count_td+'">0</td>');
												}
											}
										}else{
											if(col_index=="total_apostado"){
												html_tr1.append('<td class="mostrado ocultar ocultar'+count_td+'">0</td>');  
											}else{
												html_tr1.append('<td class="'+period_index+' ocultar ocultar'+count_td+'">0</td>');
											}
										}
										count_td++;
									});
								});
							}
						}
						if(new_obj.length -1 == obj_index){
							var count_td_total =0;
							var html_tr1 = $('<tr class="" id="'+cdv_index+'">');
								html_tr1.append('<td colspan="1" class="etiqueta_total_resultado_apostado ">'+cdv_nombre+'</td>');
							$.each(period_arr, function(period_index, period_val) {
								$.each(cols, function(col_index, col_data) {
									if(obj_total_by_period[period_index][obj_data.canal_de_venta_id]){
										if(obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]){
											if(col_index=="total_apostado"){
												html_tr1.append('<td class="mostrado ocultar'+count_td+'">'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');
											}else{
												html_tr1.append('<td class="'+period_index+'ocultar'+count_td+'" >'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');    
											}
										}else{
											if(col_index=="total_apostado") {
												html_tr1.append('<td class="mostrado ocultar ocultar'+count_td+'">0</td>');
											}else{
												html_tr1.append('<td class="'+period_index+' ocultar ocultar'+count_td+'" >0</td>');
											}
										}
									}else{
										if(col_index=="total_apostado") {
											html_tr1.append('<td class="mostrado ocultar ocultar'+count_td+'">0</td>');
										}else{
											html_tr1.append('<td class="'+period_index+' ocultar ocultar'+count_td+'" >0</td>');
										}
									}
									count_td_total++;
								});
							});
						}
						html_table.append(html_tr1); 
				}
			}
		});
	});

	var html_tr2 = $("<tr class='total_canales_resumen_apostado'>");
		html_tr2.append('<td colspan="1" class="etiqueta_total_resultado_apostado">Total Canales</td>');
	var count_stotal=0;	
	$.each(obj_super_total_by_period, function(index_stotal, val_stotal) {
		html_tr2.append('<td class="mostrado ocultar'+count_stotal+'">'+val_stotal.total.total_apostado+'</td>');
		count_stotal++;
	});
	html_table.append(html_tr2); 
	$(".tabla_contenedor_reporte_resumen_apostado").html(html_table);
	sec_reportes_resumen_apostado_events();
	loading();
}
function expand_collapse_columns_resumen_apostado($table){
	var $reportNameColumnYear = $table.find(".ocultar");
	$(".btn_hide_year_resumen_apostado").show();
	$(".btn_show_year_resumen_apostado").hide();
	var array_all_periods=all_periods.slice(0, -1).split("_");
	$.each(array_all_periods, function(index_period, current_period) {
		$(".btn_show_year_resumen_apostado").on("click",function(){
			if (index_period>0) {
				$(".ocultar"+index_period).show();
			};
			$(".btn_show_year_resumen_apostado").hide();
			$(".btn_hide_year_resumen_apostado").show();
			var current_year = $(this).attr("id");
	        $("#cabeceraanio_resumen_apostado_"+current_year).attr("colspan",1*cantidad_meses);
			$reportNameColumnYear[$reportNameColumnYear.hasClass("hide") ? "removeClass" : "addClass"]("hide");
	        $(this)[$(this).hasClass("show_thead") ? "removeClass" : "addClass"]("show_thead");
	        $table.floatThead("reflow");
	
		});    
		$(".btn_hide_year_resumen_apostado").on("click",function(){
			if (index_period>0) {
				$(".ocultar"+index_period).hide();
			};
			$(".btn_hide_year_resumen_apostado").hide();
			$(".btn_show_year_resumen_apostado").show();
			var current_year = $(this).attr("id");			
	        $("#cabeceraanio_resumen_apostado_"+current_year).attr("colspan",1);
			$reportNameColumnYear[$reportNameColumnYear.hasClass("hide") ? "removeClass" : "addClass"]("hide");
	        $(this)[$(this).hasClass("show_thead") ? "removeClass" : "addClass"]("show_thead");
	        $table.floatThead("reflow");	        					
		});
	});
}
function sec_reportes_ejecutar_reporte_resumen_apostado(type, fn) { 
   	return sec_reportes_export_table_to_excel_reporte_resumen_apostado('tabla_reportes_resumen_apostado', type || 'xlsx', fn); 
}  
function sec_reportes_validar_exportacion_reporte_resumen_apostado(s) {
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
function sec_reportes_export_table_to_excel_reporte_resumen_apostado(id, type, fn) {
	var wb = XLSX.utils.table_to_book(document.getElementById(id), {raw:true}, {sheet:"Sheet JS"});     
	var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
	var fname = fn || 'tabla_reportes_resumen_apostado.' + type;
	try {
	saveAs(new Blob([sec_reportes_validar_exportacion_reporte_resumen_apostado(wbout)],{type:"application/octet-stream"}), fname);
	} catch(e) { if(typeof console != 'undefined') console.log(e, wbout); }
	return wbout;
}
function sec_reportes_get_table_to_export_reporte_resumen_apostado(pid, iid, fmt, ofile) {
   if(fallback) {
     if(document.getElementById(iid)) document.getElementById(iid).hidden = true; 
     Downloadify.create(pid,{
       swf: 'media/downloadify.swf',
       downloadImage: 'download.png',
       width: 100,
       height: 30,
       filename: ofile, data: function() { var o = sec_reportes_ejecutar_reporte_resumen_apostado(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";
}
function sec_reportes_resumen_apostado_validacion_permisos_usuarios(btn){
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			console.log(event.event_data);
			loading(true);
			sec_reportes_resumen_apostado_get_data_reporte();
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