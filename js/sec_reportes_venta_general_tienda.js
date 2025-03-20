var reportes_venta_general_tienda_inicio_fecha_localstorage = false;
var reportes_venta_general_tienda_fin_fecha_localstorage = false;
var $table_vgt = false;
var array_local_nombres=Array();
function sec_reportes_venta_general_tienda(){
	console.log("sec_reportes_venta_general_tienda");
	loading(true);
	sec_reportes_venta_general_tienda_settings();
	sec_reportes_venta_general_tienda_events();
	sec_reportes_venta_general_tienda_get_canales_venta();
	sec_reportes_venta_general_tienda_locales();
	sec_reportes_venta_general_tienda_get_redes();
}
function sec_reportes_venta_general_tienda_get_redes(){
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
					$(".red_reporte_venta_general_tienda").append(new_option);
				});
				$('.red_reporte_venta_general_tienda').select2({closeOnSelect: false});
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
function sec_reportes_venta_general_tienda_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
	auditoria_send({"proceso":"sec_reportes_venta_general_tienda_get_canales_venta","data":data});
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
					$(".canalventareporteventa_general_tienda").append(new_option);

				});
				$('.canalventareporteventa_general_tienda').select2({closeOnSelect: false});
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
function sec_reportes_venta_general_tienda_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
	auditoria_send({"proceso":"sec_reportes_venta_general_tienda_locales","data":data});
	var local_call = $.ajax({
		data: data,
		type: "POST",
		dataType: "json",
		url: "/api/?json",
	})
	$.when(local_call).done(function( data, textStatus, jqXHR ) {
		try{
			if ( console && console.log ) {
				$.each(data.data,function(index,val){
					array_local_nombres[val.id]= val.nombre;
					var new_option = $("<option>");
					$(new_option).val(val.id);
					$(new_option).html(val.nombre);
					$(".localreporteventa_general_tienda").append(new_option);
				});
				$('.localreporteventa_general_tienda').select2({closeOnSelect: false});
			}
			sec_reportes_venta_general_tienda_get_data();
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
function sec_reportes_venta_general_tienda_events(){

	$(".btn_filtrar_reporte_venta_general_tienda").off().on("click",function(){
		var btn = $(this).data("button");
		sec_reportes_venta_general_tienda_validacion_permisos_usuarios(btn);
	})
    $table_vgt = $('#reporte_apuestas_venta_general_tienda');
    $table_vgt.floatThead({
        top:50,
        responsiveContainer: function($table_vgt){
            return $table_vgt.closest('.table-responsive');
        }       
    });
	sec_reportes_venta_general_tienda_expand_collapse_rows();
	$(".btn_export_venta_general_tienda_xlsx").off().on("click",function(){
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
					var reinit = $table_vgt.floatThead('destroy');		
					sec_reportes_ejecutar_reporte_venta_general_tienda('xlsx');
					sec_reportes_get_table_to_export_venta_general_tienda('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_venta_general_tienda.xlsx');			
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
	$(".btn_export_venta_general_tienda_xls").off().on("click",function(){
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
					var reinit = $table_vgt.floatThead('destroy');
					sec_reportes_ejecutar_reporte_venta_general_tienda('biff2', 'reporte_venta_general_tienda.xls');
					sec_reportes_get_table_to_export_venta_general_tienda('biff2btn', 'xportbiff2', 'biff2', 'reporte_venta_general_tienda.xls');				
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
}
function sec_reportes_venta_general_tienda_settings(){
	$('.localreporteventa_general_tienda').select2({
		closeOnSelect: false,            
		allowClear: true,
	});
	$('.canalventareporteventa_general_tienda').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.red_reporte_venta_general_tienda').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.reportes_venta_general_tienda_datepicker')
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

	reportes_venta_general_tienda_inicio_fecha_localstorage = localStorage.getItem("reportes_venta_general_tienda_inicio_fecha_localstorage");
	if(reportes_venta_general_tienda_inicio_fecha_localstorage){
		var reportes_venta_general_tienda_inicio_fecha_localstorage_new = moment(reportes_venta_general_tienda_inicio_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_venta_general_tienda_inicio_fecha")
			.datepicker("setDate", reportes_venta_general_tienda_inicio_fecha_localstorage_new)
			.trigger('change');
	}

	reportes_venta_general_tienda_fin_fecha_localstorage = localStorage.getItem("reportes_venta_general_tienda_fin_fecha_localstorage");
	if(reportes_venta_general_tienda_fin_fecha_localstorage){
		var reportes_venta_general_tienda_fin_fecha_localstorage_new = moment(reportes_venta_general_tienda_fin_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_venta_general_tienda_fin_fecha")
			.datepicker("setDate", reportes_venta_general_tienda_fin_fecha_localstorage_new)
			.trigger('change');
	}
}
function sec_reportes_venta_general_tienda_get_data(){
	var get_venta_general_tienda_data = {};
	get_venta_general_tienda_data.where = "venta_general_x_tienda";
	get_venta_general_tienda_data.filtro = {};
	get_venta_general_tienda_data.filtro.fecha_inicio = $('.reportes_venta_general_tienda_inicio_fecha').val();
	get_venta_general_tienda_data.filtro.fecha_fin = $('.reportes_venta_general_tienda_fin_fecha').val();
	get_venta_general_tienda_data.filtro.locales = $('.local_reportes_venta_general_tienda').val();
	get_venta_general_tienda_data.filtro.canales_de_venta = $('.canal_venta_reporte_venta_general_tienda').val();
	get_venta_general_tienda_data.filtro.red_id=$('.red_reporte_venta_general_tienda').val();
	localStorage.setItem("reportes_venta_general_tienda_inicio_fecha_localstorage",get_venta_general_tienda_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_venta_general_tienda_fin_fecha_localstorage",get_venta_general_tienda_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_reportes_venta_general_tienda_get_data","data":get_venta_general_tienda_data});
    $.ajax({
		url: "/api/?json",
        type: 'POST',
        data:get_venta_general_tienda_data
    })
    .done(function(dataresponse) {
		try{
	        var obj = JSON.parse(dataresponse);
	        //console.log(obj);
	        sec_reportes_venta_general_tienda_create_table(obj);	
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
}
function sec_reportes_venta_general_tienda_create_table(obj){
	var array_propiedad = Array();
		array_propiedad[0] = "";
		array_propiedad[1] = "Propio&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		array_propiedad[2] = "Terceros&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

	var array_red= Array();
		array_red[0] = "Sin Red";
		array_red[1] = "Bet Bar";
		array_red[2] = "Dalu&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";		
		
	var cols = {};
		cols["total_apostado"]="Apostado";
		cols["net_win"]="Net Win T";
		cols["hold"]="Hold%";

	var cdv = Object();
		cdv[15]="Web";
		cdv[16]="PBET";
		cdv[17]="SBT-Negocios";
		cdv[18]="JV Global Bet";
		cdv[19]="Tablet BC";
		cdv[20]="SBT-BC";
		cdv[21]="JV Golden Race";

	var html = "<table class='tabla_reportes_venta_general_tienda' id='reporte_apuestas_venta_general_tienda' width='100%' cellspacing='0'>";
	    	html +='<thead style="background-color: #C0C0C0 !important; color: #333 !important;">';
				html += "<tr>";
					html += "<th class='text-center border_cabecera' rowspan='2'>";
						html += "<button class='btn_collapse_expand_row_venta_general_tienda all_parent_venta_general_tienda'>";
							html += "<span class='glyphicon glyphicon-plus'></span>";
						html += "</button>";
					html += "</th>";				
					html += "<th class='text-center border_cabecera' rowspan='2'>Propiedad</th>";
					html += "<th class='text-center titulo_red_cabecera border_cabecera' rowspan='2'>Red</th>";
					html += "<th class='text-center border_cabecera' rowspan='2'>local</th>";				
					var count_cdv = 0;
					$.each(cdv, function(indice_columna, nombre_columna) {
						 html += "<th colspan='3' class='text-center border_cabecera_canal_venta cabecera_venta_general_tienda_canal_de_venta"+indice_columna+"'>"+nombre_columna+"-"+indice_columna+"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>";
						count_cdv++; 
					});
					html += "<th class='text-center border_cabecera' rowspan='2'>Total Apostado</th>";
					html += "<th class='text-center border_cabecera' rowspan='2'>Total Net Win</th>";
					html += "<th class='text-center border_cabecera' rowspan='2'>Total Hold %</th>";	
				html += "</tr>";
				html += "<tr>";		
				for (var i = 0; i < count_cdv; i++) {
					$.each(cols, function(indice_columna, nombre_columna) {
						 html += "<th class='text-center cabecera_vgt_columna border_cabecera cabecera_venta_general_tienda_columna"+indice_columna+"_"+i+"'>"+nombre_columna+"</th>";
					});	
				};
				html += "</tr>";
		   	html +='</thead><tbody>';	
		   		$.each(obj.data, function(propiedad, val_propiedad) {
					if (propiedad=="total") {
			   			html += "<tr class='total_general_venta_general_tienda '>";	
									html += "<td>";
										html += "<button class='btn_collapse_expand_row_venta_general_tienda all_parent_venta_general_tienda'>";
											html += "<span class='glyphicon glyphicon-plus'></span>";
										html += "</button>";
									html += "</td>";					
									html += "<td colspan='3'>TOTAL GENERAL</td>";
									$.each(cdv, function(index_cv, val_canal_venta) {
										if (val_propiedad[index_cv]) {
											html += "<td class='alineacion_numeros'>"+val_propiedad[index_cv].apostado+"</td>";
											html += "<td class='alineacion_numeros'>"+val_propiedad[index_cv].net_win+"</td>";
											html += "<td class='alineacion_numeros'>"+val_propiedad[index_cv].hold+"</td>";									
										}else{
											html += "<td class='alineacion_numeros'>0</td>";
											html += "<td class='alineacion_numeros'>0</td>";
											html += "<td class='alineacion_numeros'>0</td>";									
										}
									});
				   					if (val_propiedad['total']) {
										html += "<td class='alineacion_numeros'>"+val_propiedad['total'].apostado+"</td>";	
										html += "<td class='alineacion_numeros'>"+val_propiedad['total'].net_win+"</td>";
										html += "<td class='alineacion_numeros'>"+val_propiedad['total'].hold+"</td>";																													   						
				   					}else{
										html += "<td class='alineacion_numeros'>0</td>";
										html += "<td class='alineacion_numeros'>0</td>";
										html += "<td class='alineacion_numeros'>0</td>";
				   					}							
			    		html += '</tr>';
					}else{
			   			$.each(val_propiedad, function(red, val_red) {

			   				if (red=="total") {
								html += "<tr class='sub_total_propiedad_venta_general_tienda ' data-red='"+propiedad+"'>";
									//html += "<td data-total-red='"+propiedad+"'><button class='btn_collapse_expand_row_venta_general_tienda parent_venta_general_tienda_propiedad' data-propiedad='"+propiedad+"'><span class='glyphicon glyphicon-pushpin'></span></button></td>";						
									html += "<td></td>";									
									html += "<td colspan='2'>"+array_propiedad[parseInt(propiedad)]+"</td>";	
									html += "<td>Total</td>";									
			   						$.each(cdv, function(index_cv, val_canal_venta) {
			   							if (val_red[index_cv]) {
											html += "<td  class='alineacion_numeros'>"+val_red[index_cv].apostado+"</td>";
											html += "<td  class='alineacion_numeros'>"+val_red[index_cv].net_win+"</td>";
											html += "<td  class='alineacion_numeros'>"+val_red[index_cv].hold+"</td>";							
					   					}else{
											html += "<td  class='alineacion_numeros'>0</td>";
											html += "<td  class='alineacion_numeros'>0</td>";
											html += "<td  class='alineacion_numeros'>0</td>";					   						
					   					}				   							
			   						});
				   					if (val_red['total']) {
										html += "<td  class='alineacion_numeros'>"+val_red['total'].apostado+"</td>";	
										html += "<td  class='alineacion_numeros'>"+val_red['total'].net_win+"</td>";
										html += "<td  class='alineacion_numeros'>"+val_red['total'].hold+"</td>";																													   						
				   					}else{
										html += "<td  class='alineacion_numeros'>0</td>";
										html += "<td  class='alineacion_numeros'>0</td>";
										html += "<td  class='alineacion_numeros'>0</td>";
				   					}
								html += "</tr>";
			   				}else{
					   			$.each(val_red, function(local, val_local) {
					   				if (local=="total") {
										html += "<tr class='sub_total_red_venta_general_tienda children_row_collapse_expand_propiedad_"+propiedad+"' data-propiedad='"+propiedad+"' data-red='"+red+"'>";	
						   						html += '<td>';
							   						html += '<button class="btn_collapse_expand_row_venta_general_tienda parent_venta_general_tienda_red parent_row_collapse_expand_'+propiedad+''+red+'" id="'+propiedad+''+red+'">';
							   							html += '<span class="glyphicon glyphicon-plus"></span>';
							   						html += '</button>';
						   						html += '</td>';																
												html += "<td >"+array_propiedad[parseInt(propiedad)]+"</td>";
												html += "<td >"+array_red[parseInt(red)]+"</td>";									
												html += "<td >Total&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
							   					$.each(cdv, function(index_cv, val_canal_venta) {
							   						if (val_local[index_cv]) {
														html += "<td  class='alineacion_numeros'>"+val_local[index_cv].apostado+"</td>";	
														html += "<td  class='alineacion_numeros'>"+val_local[index_cv].net_win+"</td>";
														html += "<td  class='alineacion_numeros'>"+val_local[index_cv].hold+"</td>";						   						
													}else{
														html += "<td  class='alineacion_numeros'>0</td>";
														html += "<td  class='alineacion_numeros'>0</td>";
														html += "<td  class='alineacion_numeros'>0</td>";													
													}
							   					});
							   					if (val_local['total']) {
													html += "<td  class='alineacion_numeros'>"+val_local['total'].apostado+"</td>";	
													html += "<td  class='alineacion_numeros'>"+val_local['total'].net_win+"</td>";
													html += "<td  class='alineacion_numeros'>"+val_local['total'].hold+"</td>";																													   						
							   					}else{
													html += "<td  class='alineacion_numeros'>0</td>";
													html += "<td  class='alineacion_numeros'>0</td>";
													html += "<td  class='alineacion_numeros'>0</td>";
							   					}
										html += "</tr>";
		   							}else{
					   					html += '<tr class="rows_hidden_venta_general_tienda children_venta_general_tienda children_row_collapse_expand_propiedad_'+propiedad+' children_row_collapse_expand_'+propiedad+''+red+'" id="'+propiedad+''+red+'"  data-propiedad="'+propiedad+'" data-local="'+local+'" data-red="'+red+'" >';
						   						html += '<td>';
							   						html += '<button class="btn_collapse_expand_row_venta_general_tienda parent_venta_general_tienda_red parent_row_collapse_expand_"'+propiedad+''+red+'" id="'+propiedad+''+red+'">';
							   							html += '<span class="glyphicon glyphicon-plus"></span>';
							   						html += '</button>';
						   						html += '</td>';		   				
						   						html += '<td class="nombre_propiedad_venta_general_tienda">'+array_propiedad[parseInt(propiedad)]+'</td>';	
						   						html += '<td class="nombre_red_venta_general_tienda">'+array_red[parseInt(red)]+'</td>';	
						   						if (array_local_nombres[parseInt(local)]!=null) {
						   							html += '<td class="nombre_local_venta_general_tienda">'+array_local_nombres[parseInt(local)]+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
						   						}else{
													html += '<td class="nombre_local_venta_general_tienda">'+local+'</td>';	   							
						   						}	   						
							   					$.each(cdv, function(index_cv, val_canal_venta) {
							   						if (val_local[index_cv]) {	
								   						if (val_local[index_cv].local_id==local) {	
								   							if (val_local[index_cv].canal_de_venta_id==index_cv) {
											   					html += '<td class="apostado_venta_general_tienda" data-local="'+local+'" data-cv="'+index_cv+'">'+val_local[index_cv].apostado+'</td>';
											   					html += '<td class="net_win_venta_general_tienda" data-local="'+local+'" data-cv="'+index_cv+'">'+val_local[index_cv].net_win+'</td>';
											   					html += '<td class="hold_venta_general_tienda" data-local="'+local+'" data-cv="'+index_cv+'">'+val_local[index_cv].hold+'</td>';	
								   							};
								   						}			   						
								   					}else{
								   						html += '<td class="apostado_venta_general_tienda" data-local="'+local+'" data-cv="'+index_cv+'">0</td>';
								   						html += '<td class="net_win_venta_general_tienda" data-local="'+local+'" data-cv="'+index_cv+'">0</td>';
								   						html += '<td class="hold_venta_general_tienda" data-local="'+local+'" data-cv="'+index_cv+'">0</td>';			   									   						
								   					}	
							   					});					   				
					   							if (val_local.total) {
						   							html += '<td class="apostado_venta_general_tienda" data-local="'+local+'" data-total="total">'+val_local.total.apostado+'</td>';
						   							html += '<td class="net_win_venta_general_tienda" data-local="'+local+'" data-total="total">'+val_local.total.net_win+'</td>';
						   							html += '<td class="hold_venta_general_tienda" data-local="'+local+'" data-total="total">'+val_local.total.hold+'</td>';
					   							}else{
						   							html += '<td class="apostado_venta_general_tienda" data-local="'+local+'" data-total="total">0</td>';
						   							html += '<td class="net_win_venta_general_tienda" data-local="'+local+'" data-total="total">0</td>';
						   							html += '<td class="hold_venta_general_tienda" data-local="'+local+'" data-total="total">0</td>';
					   							}
				   						html += '</tr>';
		   							}
					   			});
			   				}
			   			});
					}
		   		});
			
	    	html += '</tbody><tfoot><tr>';
	    	html += '</tr></tfoot>';
    	html+='</table>';	   			
	$(".tabla_contenedor_reportes_venta_general_tienda").html(html);
	sec_reportes_venta_general_tienda_events();
	loading();
}
function sec_reportes_venta_general_tienda_expand_collapse_rows(){
	$(".all_parent_venta_general_tienda").off().on("click",function(){
		if ($(".children_venta_general_tienda").hasClass("rows_expanded_venta_general_tienda") ) {
			$(".children_venta_general_tienda").hide();
			$(".children_venta_general_tienda").removeClass('rows_expanded_venta_general_tienda').addClass('rows_hidden_venta_general_tienda');
			$(this).find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
			$(".btn_collapse_expand_row_venta_general_tienda").find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
		}else{
			$(".children_venta_general_tienda").show();
			$(".children_venta_general_tienda").removeClass('rows_hidden_venta_general_tienda').addClass('rows_expanded_venta_general_tienda');
			$(this).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
			$(".btn_collapse_expand_row_venta_general_tienda").find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
		}
	})
	$(".parent_venta_general_tienda_red").off().on("click",function(){
		var id_row_children = $(this).attr("id");
		if($(".children_row_collapse_expand_"+id_row_children).hasClass("rows_hidden_venta_general_tienda")){
			$(".children_row_collapse_expand_"+id_row_children).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
			$(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_hidden_venta_general_tienda').addClass('rows_expanded_venta_general_tienda');
            $(".parent_row_collapse_expand_"+id_row_children).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
            $(".all_parent").find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');			
		}else{
			$(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_expanded_venta_general_tienda').addClass('rows_hidden_venta_general_tienda');                
            $(".parent_row_collapse_expand_"+id_row_children).find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
            $(".all_parent").find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');  			
		}
	})
}
function sec_reportes_venta_general_tienda_validacion_permisos_usuarios(btn){
	console.log("btn_filtrar_venta_general_tienda:click");
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			loading(true);
			sec_reportes_venta_general_tienda_get_data();
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
function sec_reportes_ejecutar_reporte_venta_general_tienda(type, fn) { 
   	return sec_reportes_export_table_to_excel_venta_general_tienda('reporte_apuestas_venta_general_tienda', type || 'xlsx', fn); 
}  
function sec_reportes_validar_exportacion_venta_general_tienda(s) {
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
function sec_reportes_export_table_to_excel_venta_general_tienda(id, type, fn) {
	 var wb = XLSX.utils.table_to_book(document.getElementById(id), {raw:true}, {sheet:"Sheet JS"}); 	
     var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
     var fname = fn || 'reporte_apuestas_venta_general_tienda.' + type;
     try {
       saveAs(new Blob([sec_reportes_validar_exportacion_venta_general_tienda(wbout)],{type:"application/octet-stream"}), fname);
     } catch(e) { if(typeof console != 'undefined') console.log(e, wbout); }
     return wbout;
}
function sec_reportes_get_table_to_export_venta_general_tienda(pid, iid, fmt, ofile) {
   if(fallback) {
     if(document.getElementById(iid)) document.getElementById(iid).hidden = true; 
     Downloadify.create(pid,{
       swf: 'media/downloadify.swf',
       downloadImage: 'download.png',
       width: 100,
       height: 30,
       filename: ofile, data: function() { var o = sec_reportes_ejecutar_reporte_venta_general_tienda(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";
}