var reportes_caja_sistema_inicio_fecha_localstorage = false;
var reportes_caja_sistema_fin_fecha_localstorage = false;
var $table_cs = false;
function sec_reportes_caja_sistema(){
	console.log("sec_caja_sistema");
	loading(true);	
    sec_reportes_caja_sistema_settings();	
	sec_reportes_caja_sistema_events();
	sec_reportes_caja_sistema_get_reporte();
	sec_reportes_caja_sistemas_get_canales_venta();
	sec_reportes_caja_sistemas_get_locales();
	sec_reportes_caja_sistemas_get_redes();
}
function sec_reportes_caja_sistemas_get_redes(){
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
					$(".red_caja_sistema").append(new_option);
				});
				$('.red_caja_sistema').select2({closeOnSelect: false});
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
function sec_reportes_caja_sistemas_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_caja_sistemas_get_canales_venta","data":data});
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
						$(".canalventareportecajasistema").append(new_option);

					});
					$('.canalventareportecajasistema').select2({closeOnSelect: false});
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
function sec_reportes_caja_sistemas_get_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_caja_sistemas_get_locales","data":data});
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
						$(".localreportecajasistema").append(new_option);
					});
					$('.localreportecajasistema').select2({closeOnSelect: false});
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
function sec_reportes_caja_sistema_events(){
	$(".btn_export_xls_caja_sistema").off().on("click",function(){
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
					var reinit = $table_cs.floatThead('destroy');
					sec_reportes_ejecutar_reporte_caja_sistema('biff2', $(".export_caja_sistema_filename").val()+".xls");
					sec_reportes_get_table_to_export('biff2btn', 'xportbiff2', 'biff2', 'reporte_caja_sistema.xls');				
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
	$(".btn_filtrar_reporte_caja_sistema").off().on("click",function(){	
		var btn = $(this).data("button");
		sec_reportes_caja_sistema_validacion_permisos_usuarios(btn);
	})	
	/*
	$(".btn_filtrar_reporte_caja_sistema")
		.off()
		.on("click",function(e) {
			console.log("btn_filtrar_reporte_caja_sistema:click");

			var limit_date_1 = $('.reporte_caja_sistema_inicio_fecha').val();
			var limit_date_2 = $('.reporte_caja_sistema_fin_fecha').val();

			var start = new Date(limit_date_1);
			var end = new Date(limit_date_2);
			var diff  = new Date(end - start);
			var dias = diff/1000/60/60/24;

			if(dias > 31){
				sweetAlert("Oops...", "seleccione menos de 31 días!", "error");
			}else{
				loading(true);	
				sec_reportes_caja_sistema_get_reporte();				
			}
	});	
	*/	
    $table_cs = $('#tabla_caja_sistema');
    $table_cs.floatThead({
        top:44,
        responsiveContainer: function($table_cs){
            return $table_cs.closest('.table-responsive');
        }       
    });
}
function sec_reportes_caja_sistema_settings(){
	$('.localreportecajasistema').select2({
		closeOnSelect: false,            
		allowClear: true,
	});
	$('.canalventareportecajasistema').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.red_caja_sistema').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.reportes_caja_sistema_datepicker')
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

	reportes_caja_sistema_inicio_fecha_localstorage = localStorage.getItem("reportes_caja_sistema_inicio_fecha_localstorage");
	if(reportes_caja_sistema_inicio_fecha_localstorage){
		var reportes_caja_sistema_inicio_fecha_localstorage_new = moment(reportes_caja_sistema_inicio_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reporte_caja_sistema_inicio_fecha")
			.datepicker("setDate", reportes_caja_sistema_inicio_fecha_localstorage_new)
			.trigger('change');
	}

	reportes_caja_sistema_fin_fecha_localstorage = localStorage.getItem("reportes_caja_sistema_fin_fecha_localstorage");
	if(reportes_caja_sistema_fin_fecha_localstorage){
		var reportes_caja_sistemas_fin_fecha_localstorage_new = moment(reportes_caja_sistema_fin_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reporte_caja_sistema_fin_fecha")
			.datepicker("setDate", reportes_caja_sistemas_fin_fecha_localstorage_new)
			.trigger('change');
	}
}
function sec_reportes_caja_sistema_get_reporte(){
	var get_caja_sistema_data = {};
	get_caja_sistema_data.where = "reporte_caja";
	get_caja_sistema_data.filtro = {};
	get_caja_sistema_data.filtro.fecha_inicio = $('.reporte_caja_sistema_inicio_fecha').val();
	get_caja_sistema_data.filtro.fecha_fin = $('.reporte_caja_sistema_fin_fecha').val();
	get_caja_sistema_data.filtro.locales = $('.localreportecajasistema').val();
	get_caja_sistema_data.filtro.canales_de_venta = $('.canalventareportecajasistema').val();
	get_caja_sistema_data.filtro.red_id=$('.red_caja_sistema').val();

	localStorage.setItem("reportes_caja_sistema_inicio_fecha_localstorage",get_caja_sistema_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_caja_sistema_fin_fecha_localstorage",get_caja_sistema_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_reportes_caja_sistema_get_reporte","data":get_caja_sistema_data});
    $.ajax({
		url: "/api/?json",
        type: 'POST',
        data:get_caja_sistema_data
    })
    .done(function(dataresponse) {
		try{
	    	//console.log(dataresponse);
	    	//break;
	        var obj = JSON.parse(dataresponse);
	        console.log(obj);
	        sec_reportes_caja_sistema_create_table(obj);
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
function sec_reportes_caja_sistema_create_table(obj){
	var cols = {};
		cols["local"]="Local";
		cols["canal_de_venta_nombre"]="Canal de venta";
		cols["dia"]="Dia";	
		cols["mes"]="Mes";
		cols["anio"]="Año";					
		cols["total_ingresado"]="Total ingresado";
		cols["total_retirado_cobrado"]="Total retirado cobrado";
		cols["total_apostado"]="Total apostado";
		cols["total_pagado"]="Total pagado";
		cols["caja_fisico"]="Caja fisico";
		cols["total_depositado_web"]="Total depositado web";
		cols["total_retirado_web"]="Total retirado web";
		cols["total_caja_web"]="Caja web";
		cols["total_balance_pos"]="Total Balance POS";
		cols["total_caja_real"]="Total caja real";
		cols["pagado_de_otra_tienda"]="Pagado de otra tienda";
		cols["pagado_en_otra_tienda"]="Pagado en otra tienda";
    	var info_canal_array = [];
    	var info_total_local = [];
    	var i=0;
        $.each(obj.data, function(local, val_anio) {
        	//console.log(local);
        	if (local!="total") {
				$.each(val_anio, function(index_anio_total, val_meses_totales) {
				   	if (index_anio_total!="total") {
				            $.each(val_meses_totales, function(mes, val_dias) {
				                $.each(val_dias, function(dias, val_canales_venta) {

				                    $.each(val_canales_venta, function(index_cv_total, detalles_cv) {
				                		var info_canal={};
				                		
				                		info_canal.dia=dias;

				                		info_canal.mes=mes;
				                		info_canal.anio=index_anio_total;
				                		info_canal.index_cv_total=index_cv_total;
										info_canal.caja_fisico = detalles_cv.caja_fisico;
										info_canal.canal_de_venta_id = detalles_cv.canal_de_venta_id;
										info_canal.canal_de_venta_nombre = detalles_cv.canal_de_venta_nombre;
										info_canal.fecha = detalles_cv.fecha;
										info_canal.total_anulado_retirado = detalles_cv.total_anulado_retirado;	
										info_canal.local_id = detalles_cv.local_id;
										info_canal.balance_pos = detalles_cv.balance_pos;
										info_canal.caja_real = detalles_cv.caja_real;																		
										info_canal.pagado_de_otra_tienda = detalles_cv.pagado_de_otra_tienda;
										info_canal.pagado_en_otra_tienda = detalles_cv.pagado_en_otra_tienda;
										info_canal.total_apostado = detalles_cv.total_apostado;
										info_canal.total_caja_web = detalles_cv.total_caja_web;
										info_canal.total_depositado = detalles_cv.total_depositado;									
										info_canal.total_depositado_web = detalles_cv.total_depositado_web;
										info_canal.total_ingresado = detalles_cv.total_ingresado;
										info_canal.total_pagado = detalles_cv.total_pagado;
										info_canal.total_retirado_web = detalles_cv.total_retirado_web;
										info_canal_array.push(info_canal);
				                    });
				                });
				            });
					}
				});
			}else{
				var info_total={};
				info_total.balance_pos=val_anio.balance_pos;
				info_total.caja_fisico=val_anio.caja_fisico;
				info_total.caja_real=val_anio.caja_real;
				info_total.canal_de_venta_id=val_anio.canal_de_venta_id;
				info_total.canal_de_venta_nombre=val_anio.canal_de_venta_nombre;
				info_total.pagado_de_otra_tienda=val_anio.pagado_de_otra_tienda ;
				info_total.pagado_en_otra_tienda=val_anio.pagado_en_otra_tienda;
				info_total.total_anulado_retirado=val_anio.total_anulado_retirado;
				info_total.total_apostado=val_anio.total_apostado;
				info_total.total_caja_web=val_anio.total_caja_web;
				info_total.total_depositado=val_anio.total_depositado;
				info_total.total_depositado_web=val_anio.total_depositado_web;
				info_total.total_ingresado=val_anio.total_ingresado;
				info_total.total_pagado=val_anio.total_pagado;
				info_total.total_retirado_web=val_anio.total_retirado_web;
				info_total_local.push(info_total);

			}
        });
		//console.log(info_canal_array.sort(function(a, b){return a - b}));

		var caja_sistema_sub_totales= Array();
		$.each(obj.data, function(local, val_anio) {
			$.each(val_anio, function(index_anio_total, val_meses_totales){
				if (index_anio_total=="total") {
				caja_sistema_sub_totales[val_meses_totales.local_id]=val_meses_totales;
				}
			});
		});	
        var array_nombre_local=[];
        var array_tipo_admin=[];
        var array_propiedad=[];
        var array_tipo=[];
        $.each(obj.locales_arr, function(local_id, local_val) {
			array_nombre_local[local_id ]=local_val.nombre;
			array_tipo_admin[local_id] = local_val.administracion_tipo;
			array_propiedad[local_id] = local_val.propiedad_id;
			array_tipo[local_id] = local_val.tipo;
        });
    	var html = '<table id="tabla_caja_sistema" cellspacing="0" width="100%">';
		    	html +='<thead>';
					html += "<tr>";
					    html += "<th class='cabecera_seleccionar_todos_row'>";
					    html += "<button type='button' class='btn_collapse_expand_row_caja_sistema all_parent_caja_sistema'>";
					    html += "<span class='glyphicon glyphicon-plus'></span>";
					    html += "</button>";
					    html += "</th>";
							$.each(cols, function(indice_columna, nombre_columna) {
								 html += "<th class='cabecera_web_total_"+indice_columna+"'>"+nombre_columna+"</th>";
							});
					html += "</tr>";
			   	html +='</thead><tbody>';
		    	$.each(info_canal_array, function(index_canal_totales, detalles) {

		    			html+='<tr class="'+detalles.index_cv_total+'_dia_caja_sistema rows_hidden_caja_sistema children_caja_sistema children_row_collapse_expand_'+detalles.local_id+'" id="'+detalles.local_id+'">';
							html+='<td>';
							html+='<button class="btn_collapse_expand_row_caja_sistema parent_caja_sistema parent_row_collapse_expand_'+detalles.local_id+'" id="'+detalles.local_id+'">';
							html+='<span class="glyphicon glyphicon-plus"/>';
							html+='</button>';
							html+='</td>';
							if (detalles.local_id) { 
								html+='<td class="alinear_letras">'+array_nombre_local[detalles.local_id]+'</td>';
							};
							html+='<td class="alinear_letras" >'+detalles.canal_de_venta_nombre+'</td>';					
							html+='<td class="alinear_letras">'+detalles.dia+'</td>';
							html+='<td class="alinear_letras">'+detalles.mes+'</td>';	
							html+='<td class="alinear_letras">'+detalles.anio+'</td>';																	
							html+='<td class="alinear_numeros">'+detalles.total_depositado+'</td>';
							html+='<td class="alinear_numeros">'+detalles.total_anulado_retirado+'</td>';
							html+='<td class="alinear_numeros">'+detalles.total_apostado+'</td>';
							html+='<td class="alinear_numeros">'+detalles.total_pagado+'</td>';
							html+='<td class="alinear_numeros">'+detalles.caja_fisico+'</td>';
							html+='<td class="alinear_numeros">'+detalles.total_depositado_web+'</td>';							
							html+='<td class="alinear_numeros">'+detalles.total_retirado_web+'</td>';
							html+='<td class="alinear_numeros">'+detalles.total_caja_web+'</td>';
							html+='<td class="alinear_numeros">'+detalles.balance_pos+'</td>';
							html+='<td class="alinear_numeros">'+detalles.caja_real+'</td>';												
							html+='<td class="alinear_numeros">'+detalles.pagado_de_otra_tienda+'</td>';
							html+='<td class="alinear_numeros">'+detalles.pagado_en_otra_tienda+'</td>';
		     			html+='</tr>';
		    		if (info_canal_array.length>parseInt(index_canal_totales)+1) {
						var next_index = parseInt(index_canal_totales)+parseInt(1);
						var next_info_canal_array = info_canal_array[next_index].local_id;
						var detalles_local_id= detalles.local_id;
		                if(detalles_local_id!=next_info_canal_array){  
		                	var detalles_locales_id = parseInt(detalles.local_id);  

						    		html+='<tr class="fila_ocultar">';
		    							html+='<td>';
		    							html+='<button class="btn_collapse_expand_row_caja_sistema parent_caja_sistema parent_row_collapse_expand_'+detalles.local_id+'" id="'+detalles.local_id+'">';
		    								html+='<span class="glyphicon glyphicon-plus"></span>';
		    							html+='</button>';
		    							html+='</td>';
										if (detalles.local_id) {						
											html+='<td class="alinear_letras">'+array_nombre_local[detalles.local_id]+' - Total</td>';	
										}
										//html+='<td class="alinear_letras">'+caja_sistema_sub_totales[detalles.local_id].canal_de_venta_nombre+'</td>';
										html+='<td class="alinear_letras"></td>';										
										html+='<td class="alinear_letras"></td>';
										html+='<td class="alinear_letras"></td>';
										html+='<td class="alinear_letras"></td>';																				
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_depositado+'</td>';	
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_anulado_retirado+'</td>';					
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_apostado+'</td>';
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_pagado+'</td>';
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].caja_fisico+'</td>';
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_depositado_web+'</td>';										
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_retirado_web+'</td>';
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_caja_web+'</td>';
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].balance_pos+'</td>';
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].caja_real+'</td>';								
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].pagado_de_otra_tienda+'</td>';
										html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].pagado_en_otra_tienda+'</td>';
							   		html+='</tr>'; 
		                }                
		            };
		            if (info_canal_array.length-1==index_canal_totales) {
				    		html+='<tr class="fila_ocultar">';
								html+='<td>';
									html+='<button class="btn_collapse_expand_row_caja_sistema parent_caja_sistema parent_row_collapse_expand_'+detalles.local_id+'" id="'+detalles.local_id+'">';
										html+='<span class="glyphicon glyphicon-plus"></span>';
									html+='</button>';
								html+='</td>';
								if (detalles.local_id) {						
									html+='<td>'+array_nombre_local[detalles.local_id]+' - Total</td>';	
								}
								html+='<td class="alinear_letras"></td>';
								html+='<td class="alinear_letras"></td>';
								// html+='<td class="alinear_letras"></td>';
								html+='<td class="alinear_letras"></td>';
								/*
								html+='<td class="alinear_letras">'+caja_sistema_sub_totales[detalles.local_id].canal_de_venta_nombre+'</td>';
								html+='<td class="alinear_letras">'+detalles.dia+'</td>';
								html+='<td class="alinear_letras">'+detalles.mes+'</td>';
								html+='<td class="alinear_letras">'+detalles.anio+'</td>';
								*/
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_depositado+'</td>';	
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_anulado_retirado+'</td>';					
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_apostado+'</td>';
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_pagado+'</td>';
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].caja_fisico+'</td>';
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_depositado_web+'</td>';								
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_retirado_web+'</td>';
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].total_caja_web+'</td>';
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].balance_pos+'</td>';
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].caja_real+'</td>';								
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].pagado_de_otra_tienda+'</td>';
								html+='<td class="alinear_numeros">'+caja_sistema_sub_totales[detalles.local_id].pagado_en_otra_tienda+'</td>';
				     		html+='</tr>';            	
		            };
		    	});
				$.each(info_total_local, function(index_total_general, val_total_general) {
						html+='<tr class="fila_ocultar" >';
							html+='<td>';
								html+='<button type="button" class="btn_collapse_expand_row_caja_sistema all_parent_caja_sistema">';
									html+='<span class="glyphicon glyphicon-plus"></span>';
								html+='</button>';
							html+='</td>';		    		
							html+='<td>TOTAL</td>';	
							html+='<td></td>';	
							html+='<td></td>';
							html+='<td></td>';
							html+='<td></td>';																												
							html+='<td class="alinear_numeros">'+val_total_general.total_depositado+'</td>';
							html+='<td class="alinear_numeros">'+val_total_general.total_anulado_retirado+'</td>';
							html+='<td class="alinear_numeros">'+val_total_general.total_apostado+'</td>';
							html+='<td class="alinear_numeros">'+val_total_general.total_pagado+'</td>';
							html+='<td class="alinear_numeros">'+val_total_general.caja_fisico+'</td>';
							html+='<td class="alinear_numeros">'+val_total_general.total_depositado_web+'</td>';
							html+='<td class="alinear_numeros">'+val_total_general.total_retirado_web+'</td>';
							html+='<td class="alinear_numeros">'+val_total_general.total_caja_web+'</td>';
							html+='<td class="alinear_numeros">'+val_total_general.balance_pos+'</td>';
							html+='<td class="alinear_numeros">'+val_total_general.caja_real+'</td>';
							html+='<td class="alinear_numeros">'+val_total_general.pagado_de_otra_tienda+'</td>';
							html+='<td class="alinear_numeros">'+val_total_general.pagado_en_otra_tienda+'</td>';
	 					html+='</tr>';						
				});	
		    	html += '</tbody><tfoot><tr>';
		    	html += '</tr></tfoot>';
		    html+='</table>';	
		$(".contenedor_reportes_caja_sistema").html(html);
		sec_reportes_caja_sistema_events(); 
		sec_reportes_caja_sistema_collapse_expand_rows();  
		loading();
}
function sec_reportes_ejecutar_reporte_caja_sistema(type, fn) { 
   	return sec_reportes_export_table_to_excel('tabla_caja_sistema', type || 'xlsx', fn); 
}  
function sec_reportes_validar_exportacion_caja_sistema(s) {
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
function sec_reportes_export_table_to_excel(id, type, fn) {
	 var wb = XLSX.utils.table_to_book(document.getElementById(id), {raw:true}, {sheet:"Sheet JS"});     
     var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
     var fname = fn || 'tabla_caja_sistema.' + type;
     try {
       saveAs(new Blob([sec_reportes_validar_exportacion_caja_sistema(wbout)],{type:"application/octet-stream"}), fname);
     } catch(e) { if(typeof console != 'undefined') console.log(e, wbout); }
     return wbout;
}
function sec_reportes_get_table_to_export(pid, iid, fmt, ofile) {
   if(fallback) {
     if(document.getElementById(iid)) document.getElementById(iid).hidden = true; 
     Downloadify.create(pid,{
       swf: 'media/downloadify.swf',
       downloadImage: 'download.png',
       width: 100,
       height: 30,
       filename: ofile, data: function() { var o = sec_reportes_ejecutar_reporte_caja_sistema(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";
}
function sec_reportes_caja_sistema_validacion_permisos_usuarios(btn){
	console.log("btn_filtrar_reporte_caja_sistema:click");
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			console.log(event.event_data);
			//loading(true);
				var limit_date_1 = $('.reporte_caja_sistema_inicio_fecha').val();
				var limit_date_2 = $('.reporte_caja_sistema_fin_fecha').val();

				var start = new Date(limit_date_1);
				var end = new Date(limit_date_2);
				var diff  = new Date(end - start);
				var dias = diff/1000/60/60/24;

				if(dias > 31){
					sweetAlert("Oops...", "seleccione menos de 31 días!", "error");
				}else{
					loading(true);	
					sec_reportes_caja_sistema_get_reporte();				
				}				

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
function sec_reportes_caja_sistema_collapse_expand_rows(){
	$(".all_parent_caja_sistema").off().on("click",function(){
		if ($(".children_caja_sistema").hasClass("rows_expanded_caja_sistema") ) {
			$(".children_caja_sistema").hide();
			$(".children_caja_sistema").removeClass('rows_expanded_caja_sistema').addClass('rows_hidden_caja_sistema');
			$(this).find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
			$(".btn_collapse_expand_row_caja_sistema").find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
		}else{
			$(".children_caja_sistema").show();
			$(".children_caja_sistema").removeClass('rows_hidden_caja_sistema').addClass('rows_expanded_caja_sistema');
			$(this).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
			$(".btn_collapse_expand_row_caja_sistema").find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');			
		}
	})
	$(".parent_caja_sistema").off().on("click",function(){
		var id_row_children = $(this).attr("id");
		if($(".children_row_collapse_expand_"+id_row_children).hasClass("rows_hidden_caja_sistema")){
			$(".children_row_collapse_expand_"+id_row_children).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
			$(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_hidden_caja_sistema').addClass('rows_expanded_caja_sistema');
			$(".parent_row_collapse_expand_"+id_row_children).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');			
			$(".all_parent_caja_sistema").find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
		}else{
			$(".parent_row_collapse_expand_"+id_row_children).find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
			$(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_expanded_caja_sistema').addClass('rows_hidden_caja_sistema');                
			$(".all_parent_caja_sistema").find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus'); 
		}
	})
}