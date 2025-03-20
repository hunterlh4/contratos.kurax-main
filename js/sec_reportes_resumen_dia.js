var reportes_resumen_dia_inicio_fecha_localstorage = false;
var reportes_resumen_dia_fin_fecha_localstorage = false;
var $table_rd = false;
var array_local_nombres=Array();
function sec_reportes_resumen_dia(){
	console.log("sec_reportes_resumen_dia");
	//loading(true);
	sec_reportes_resumen_dia_settings();
	sec_reportes_resumen_dia_events();
	sec_reportes_resumen_dia_get_canales_venta();
	sec_reportes_resumen_dia_locales();
	//sec_reportes_resumen_dia_get_data();
	sec_reportes_resumen_dia_get_redes();
}
function sec_reportes_resumen_dia_get_redes(){
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
					$(".red_reporte_resumen_por_dia").append(new_option);
				});
				$('.red_reporte_resumen_por_dia').select2({closeOnSelect: false});
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
function sec_reportes_resumen_dia_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
	auditoria_send({"proceso":"sec_reportes_resumen_dia_get_canales_venta","data":data});
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
					$(".canalventareporte_resumen_por_dia").append(new_option);

				});
				$('.canalventareporte_resumen_por_dia').select2({closeOnSelect: false});
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
function sec_reportes_resumen_dia_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_resumen_dia_locales","data":data});
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
						$(".localreporte_resumen_por_dia").append(new_option);
					});
					$('.localreporte_resumen_por_dia').select2({closeOnSelect: false});
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
				console.log( "La solicitud locales a fallado en reporte resumen dia : " +  textStatus);
			}
		})
}
function sec_reportes_resumen_dia_events(){
	$(".btn_filtrar_reporte_resumen_dia").off().on("click",function(){
		loading(true);
		var btn = $(this).data("button");
		sec_reportes_resumen_dia_validacion_permisos_usuarios(btn);
	})
	$(".btn_export_resumen_dia_xlsx").off().on("click",function(){
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
		auditoria_send({"proceso":"validar_usuario_permiso_botones reporte resumen dia","data":data});
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
					var reinit = $table_rd.floatThead('destroy');
					sec_reportes_ejecutar_resumen_dia('xlsx');
					sec_reportes_get_table_to_export_resumen_dia('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_resumen_dia.xlsx');
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
	$(".btn_export_resumen_dia_xls").off().on("click",function(){
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
					var reinit = $table_rd.floatThead('destroy');
					sec_reportes_ejecutar_resumen_dia('biff2', 'reporte_resumen_dia.xls');
					sec_reportes_get_table_to_export_resumen_dia('biff2btn', 'xportbiff2', 'biff2', 'reporte_resumen_dia.xls');
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
    $table_rd = $('#tabla_reportes_resumen_dia');
    $table_rd.floatThead({
    	top:50
    });
    sec_reportes_resumen_dia_expand_collapse_rows();
}
function sec_reportes_resumen_dia_settings(){
	$('.localreporte_resumen_por_dia').select2({
		closeOnSelect: false,
		allowClear: true,
	});
	$('.canalventareporte_resumen_por_dia').select2({
		closeOnSelect: false,
		allowClear: true,
	});
	$('.red_reporte_resumen_por_dia').select2({
		closeOnSelect: false,
		allowClear: true,
	});
	$('.reportes_resumen_dia_datepicker')
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
	reportes_resumen_dia_inicio_fecha_localstorage = localStorage.getItem("reportes_resumen_dia_inicio_fecha_localstorage");
	if(reportes_resumen_dia_inicio_fecha_localstorage){
		var reportes_resumen_dia_inicio_fecha_localstorage_new = moment(reportes_resumen_dia_inicio_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_resumen_dia_inicio_fecha")
			.datepicker("setDate", reportes_resumen_dia_inicio_fecha_localstorage_new)
			.trigger('change');
	}
	reportes_resumen_dia_fin_fecha_localstorage = localStorage.getItem("reportes_resumen_dia_fin_fecha_localstorage");
	if(reportes_resumen_dia_fin_fecha_localstorage){
		var reportes_resumen_dia_fin_fecha_localstorage_new = moment(reportes_resumen_dia_fin_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_resumen_dia_fin_fecha")
			.datepicker("setDate", reportes_resumen_dia_fin_fecha_localstorage_new)
			.trigger('change');
	}
}
function sec_reportes_resumen_dia_get_data(){
	var get_resumen_dia_data = {};
	get_resumen_dia_data.where = "resumen_x_dia";
	get_resumen_dia_data.filtro = {};
	get_resumen_dia_data.filtro.fecha_inicio = $('.reportes_resumen_dia_inicio_fecha').val();
	get_resumen_dia_data.filtro.fecha_fin = $('.reportes_resumen_dia_fin_fecha').val();
	get_resumen_dia_data.filtro.locales = $('.localreporte_resumen_por_dia').val();
	get_resumen_dia_data.filtro.canales_de_venta = $('.canalventareporte_resumen_por_dia').val();
	get_resumen_dia_data.filtro.red_id=$('.red_reporte_resumen_por_dia').val();
	localStorage.setItem("reportes_resumen_dia_inicio_fecha_localstorage",get_resumen_dia_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_resumen_dia_fin_fecha_localstorage",get_resumen_dia_data.filtro.fecha_fin);
	auditoria_send({"proceso":"sec_reportes_resumen_dia_get_data","data":get_resumen_dia_data});
	$.ajax({
		url: "/api/?json",
		type: 'POST',
		data:get_resumen_dia_data
	})
	.done(function(dataresponse) {
		try{
			var obj = JSON.parse(dataresponse);
			console.log(obj);
			sec_reportes_resumen_dia_create_table(obj);
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
function sec_reportes_resumen_dia_create_table(obj){
		var nombre_mes = ["","Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];
		var cols = {};
		cols["anio"] = "Año";
		cols["mes"] = "Mes";
		cols["dia"] = "Día";
		cols["canales_de_venta"] = "Canal de Venta";
		cols["local"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Local&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		cols["tipo_de_administracion"] = "Tipo de Administración";
		cols["tipo_de_punto"] = "Tipo de <br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Punto&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		cols["qty"] = "Qty";
		cols["apostado"] = "&nbsp;&nbsp;&nbsp;Apostado&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		cols["net_win"] = "Net &nbsp;&nbsp;&nbsp;&nbsp;Win&nbsp;&nbsp;&nbsp;&nbsp;";
		cols["hold"] = "Hold &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		cols["depositado_web"] = "Depositado &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Web&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		cols["dinero_retitado_web"] = "Dinero Retirado Web";
		cols["caja_web"]="Caja Web";
		var cdv = Object();
			cdv[15]="Web";
			cdv[16]="PBET";
			cdv[17]="SBT-Negocios";
			cdv[18]="JV Global Bet";
			cdv[19]="Tablet BC";
			cdv[20]="SBT-BC";
			cdv[21]="JV Golden Race";

		var html ="<table class='tabla_reportes_resumen_dia' id='tabla_reportes_resumen_dia' width='100%' cellspacing='0'>";
	    	html +='<thead style="background-color: #C0C0C0 !important; color: #333 !important;">';
	    		html +='<tr>';
	    				html += '<th id="th_boton">';
		    				html += '<button class="all_parent btn_expand_collapse_rows">';
		    					html += '<span class="glyphicon glyphicon-plus"></span>';
		    				html += '</button>';
	    				html += '</th>';
					$.each(cols, function(index_cols, val_cols) {
						html +='<th id="th_'+index_cols+'" class="'+index_cols+'">'+val_cols+'</th>';
					});
	    		html +='<tr>';
		   	html +='</thead><tbody>';
		   			$.each(obj.data, function(anio, val_anio) {
		   				if (anio=="total") {
		   				}else{
			   				$.each(val_anio, function(mes, val_mes) {
			   					if (mes=="total") {
				   								html += '<tr class="total_por_anio_row parent_anio">';
													html += '<td>';
														html += '<button class="all_parent btn_expand_collapse_rows">';
															html += '<span class="glyphicon glyphicon-plus"></span>';
														html += '</button>';
													html += '</td>';
													html += '<td class="sec_reportes_resumen_dia_text">'+anio+'</td>';
													html += '<td class="sec_reportes_resumen_dia_text"></td>';

													html += '<td class="sec_reportes_resumen_dia_text"></td>';
													html += '<td class="sec_reportes_resumen_dia_text"></td>';
													html += '<td class="sec_reportes_resumen_dia_text">Total</td>';

													html += '<td class="sec_reportes_resumen_dia_text">Año</td>';
													html += '<td class="sec_reportes_resumen_dia_text"></td>';
													html += '<td class="sec_reportes_resumen_dia_text"></td>';

													html += '<td class="sec_reportes_resumen_dia_number" >'+val_mes.apostado+'</td>';
													html += '<td class="sec_reportes_resumen_dia_number" >'+val_mes.net_win+'</td>';
													html += '<td class="sec_reportes_resumen_dia_number" >'+val_mes.hold+'</td>';
													html += '<td class="sec_reportes_resumen_dia_number" >'+val_mes.total_depositado_web+'</td>';
													html += '<td class="sec_reportes_resumen_dia_number" >'+val_mes.total_retirado_web+'</td>';
													html += '<td class="sec_reportes_resumen_dia_number" >'+val_mes.total_caja_web+'</td>';
				   								html += '</tr>';
			   					}else{
				   					$.each(val_mes, function(dia, val_dia) {
										//MES
				   						if (dia=="total") {
				   								html += '<tr class="total_por_mes_row">';
													html += '<td></td>';
													html += '<td class="sec_reportes_resumen_dia_text">'+anio+'</td>';
													html += '<td class="sec_reportes_resumen_dia_text">'+nombre_mes[parseInt(mes)]+'</td>';

													html += '<td class="sec_reportes_resumen_dia_text sec_reportes_resumen_dia_color_dia"></td>';
													html += '<td class="sec_reportes_resumen_dia_text"></td>';
													html += '<td class="sec_reportes_resumen_dia_text">Total</td>';

													html += '<td class="sec_reportes_resumen_dia_text">Mes</td>';
													html += '<td class="sec_reportes_resumen_dia_text"></td>';
													html += '<td class="sec_reportes_resumen_dia_text"></td>';

													html += '<td class="sec_reportes_resumen_dia_number" >'+val_dia.apostado+'</td>';
													html += '<td class="sec_reportes_resumen_dia_number" >'+val_dia.net_win+'</td>';
													html += '<td class="sec_reportes_resumen_dia_number" >'+val_dia.hold+'</td>';
													html += '<td class="sec_reportes_resumen_dia_number" >'+val_dia.total_depositado_web+'</td>';
													html += '<td class="sec_reportes_resumen_dia_number" >'+val_dia.total_retirado_web+'</td>';
													html += '<td class="sec_reportes_resumen_dia_number" >'+val_dia.total_caja_web+'</td>';
				   								html += '</tr>';
				   						}else{
					   						$.each(val_dia, function(canal, val_canal) {
					   							//DIA
					   							if (canal=="total") {
					   								html += '<tr class="total_por_dia_row " >';
														html += '<td>';
															html += '<button class="btn_expand_collapse_rows parent_dia parent_row_collapse_expand_dia_'+anio+'_'+mes+'_'+dia+'" data-id-dia="'+anio+'_'+mes+'_'+dia+'">';
																html += '<span class="glyphicon glyphicon-plus"></span>';
															html += '</button>';
														html += '</td>';
														html += '<td class="sec_reportes_resumen_dia_text">'+anio+'</td>';
														html += '<td class="sec_reportes_resumen_dia_text">'+nombre_mes[parseInt(mes)]+'</td>';

														html += '<td class="sec_reportes_resumen_dia_number sec_reportes_resumen_dia_color_dia">'+dia+'</td>';
														html += '<td class="sec_reportes_resumen_dia_text"></td>';
														html += '<td class="sec_reportes_resumen_dia_text">Total</td>';

														html += '<td class="sec_reportes_resumen_dia_text">Día</td>';
														html += '<td class="sec_reportes_resumen_dia_text"></td>';
														html += '<td class="sec_reportes_resumen_dia_text"></td>';

														html += '<td class="sec_reportes_resumen_dia_number">'+val_canal.apostado+'</td>';
														html += '<td class="sec_reportes_resumen_dia_number">'+val_canal.net_win+'</td>';
														html += '<td class="sec_reportes_resumen_dia_number">'+val_canal.hold+'</td>';

														html += '<td class="sec_reportes_resumen_dia_number">'+val_canal.total_depositado_web+'</td>';
														html += '<td class="sec_reportes_resumen_dia_number">'+val_canal.total_retirado_web+'</td>';
														html += '<td class="sec_reportes_resumen_dia_number">'+val_canal.total_caja_web+'</td>';
					   								html += '</tr>';
					   							}else{
						   							$.each(val_canal, function(local, val_local) {
						   								//CANAL
						   								if (local=="total") {
							   								html += '<tr class="children rows_hidden_resumen_dia total_canal_de_venta_row children_row_collapse_expand_dia_'+anio+'_'+mes+'_'+dia+'">';
																html += '<td>';
																	html += '<button class="btn_expand_collapse_rows parent_dia  parent_row_collapse_expand_dia_'+anio+'_'+mes+'_'+dia+'">';
																		html += '<span class="glyphicon glyphicon-plus"></span>';
																	html += '</button>';
																html += '</td>';
																html += '<td class="sec_reportes_resumen_dia_text">'+anio+'</td>';
																html += '<td class="sec_reportes_resumen_dia_text">'+nombre_mes[parseInt(mes)]+'</td>';

																html += '<td class="sec_reportes_resumen_dia_number">'+dia+'</td>';
																html += '<td class="sec_reportes_resumen_dia_text">'+cdv[canal]+'</td>';
																html += '<td class="sec_reportes_resumen_dia_text">Total</td>';

																html += '<td class="sec_reportes_resumen_dia_text">Canal</td>';
																html += '<td class="sec_reportes_resumen_dia_text"></td>';
																html += '<td class="sec_reportes_resumen_dia_text"></td>';

																html += '<td class="sec_reportes_resumen_dia_number">'+val_local.apostado+'</td>';
																html += '<td class="sec_reportes_resumen_dia_number">'+val_local.net_win+'</td>';
																html += '<td class="sec_reportes_resumen_dia_number">'+val_local.hold+'</td>';

																html += '<td class="sec_reportes_resumen_dia_number">'+val_local.total_depositado_web+'</td>';
																html += '<td class="sec_reportes_resumen_dia_number">'+val_local.total_retirado_web+'</td>';
																html += '<td class="sec_reportes_resumen_dia_number">'+val_local.total_caja_web+'</td>';
							   								html += '</tr>';
						   								}else{

							   								html += '<tr class="children rows_hidden_resumen_dia children_row_collapse_expand_dia_'+anio+'_'+mes+'_'+dia+'" >';
																html += '<td class="sec_reportes_resumen_dia_boton">';
																	html += '<button class="btn_expand_collapse_rows parent_dia parent_row_collapse_expand_dia_'+anio+'_'+mes+'_'+dia+'" data-id-dia="'+anio+'_'+mes+'_'+dia+'">';
																		html += '<span class="glyphicon glyphicon-plus"></span>';
																	html += '</button>';
																html += '</td>';
																html += '<td class="sec_reportes_resumen_dia_anio">'+anio+'</td>';
																html += '<td class="sec_reportes_resumen_dia_mes">'+nombre_mes[parseInt(mes)]+'</td>';
																html += '<td class="sec_reportes_resumen_dia_dia">'+dia+'</td>';
																/*
																if (val_local.canal_de_venta) {
																	html += '<td class="sec_reportes_resumen_dia_cv">'+val_local.canal_de_venta+'</td>';
																}else{
																	html += '<td class="sec_reportes_resumen_dia_cv">'+val_local.cdv_nombre+'</td>';
																}
																*/
																html += '<td class="sec_reportes_resumen_dia_cv">'+val_local.cdv_nombre+'</td>';
																html += '<td class="sec_reportes_resumen_dia_local">'+val_local.local_nombre+'</td>';
																html += '<td class="sec_reportes_resumen_dia_tipo_admin">'+val_local.administracion+'</td>';
																html += '<td class="sec_reportes_resumen_dia_tipo_punto">'+val_local.tipo_de_punto+'</td>';
																html += '<td class="sec_reportes_resumen_dia_qty"></td>';

																	if (val_local.apostado== null) {
																		html += '<td class="sec_reportes_resumen_dia_local_apostado">0</td>';
																	}else{
																		html += '<td class="sec_reportes_resumen_dia_local_apostado">'+val_local.apostado+'</td>';
																	}
																	if (val_local.net_win== null) {
																		html += '<td class="sec_reportes_resumen_dia_net_win">0</td>';
																	}else{
																		html += '<td class="sec_reportes_resumen_dia_net_win">'+val_local.net_win+'</td>';
																	}

																	if (val_local.hold == null) {
																		html += '<td class="sec_reportes_resumen_dia_hold">0</td>';
																	}else{
																		html += '<td class="sec_reportes_resumen_dia_hold">'+val_local.hold+'</td>';
																	}

																	if (val_local.total_depositado_web == null) {
																		html += '<td class="sec_reportes_resumen_dia_dep_web">0</td>';
																	}else{
																		html += '<td class="sec_reportes_resumen_dia_dep_web">'+val_local.total_depositado_web+'</td>';
																	}

																	if (val_local.total_retirado_web == null) {
																		html += '<td class="sec_reportes_resumen_dia_ret_web">0</td>';
																	}else{
																		html += '<td class="sec_reportes_resumen_dia_ret_web">'+val_local.total_retirado_web+'</td>';
																	}

																	if (val_local.total_caja_web == null) {
																		html += '<td class="sec_reportes_resumen_dia_caja_web">0</td>';
																	}else{
																		html += '<td class="sec_reportes_resumen_dia_caja_web">'+val_local.total_caja_web+'</td>';

																	}
							   								html += '</tr>';
							   							}
						   							});
					   							}

					   						});
				   						}

				   					});
			   					}

			   				});
   	   				    }
		   			});

	    	html += '</tbody><tfoot><tr>';
	    	html += '</tr></tfoot>';
    	html+='</table>';
	$(".tabla_contenedor_reportes_resumen_por_dia").html(html);
	sec_reportes_resumen_dia_events();
	loading();
}
function sec_reportes_resumen_dia_expand_collapse_rows(){
}
function sec_reportes_resumen_dia_validacion_permisos_usuarios(btn){
	console.log("btn_filtrar_resumen_dia:click");
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			sec_reportes_resumen_dia_get_data();
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
function sec_reportes_resumen_dia_expand_collapse_rows(){
	$(".all_parent").off().on("click",function(){
		if ($(".children").hasClass("rows_expanded_resumen_dia") ) {
			$(".children").hide();
			$(".children").removeClass('rows_expanded_resumen_dia').addClass('rows_hidden_resumen_dia');
            $(this).find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
            $(".btn_expand_collapse_rows").find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
		}else{
			$(".children").show();
			$(".children").removeClass('rows_hidden_resumen_dia').addClass('rows_expanded_resumen_dia');
            $(this).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
            $(".btn_expand_collapse_rows").find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
		}
	})
    $(".parent_dia").off().on("click",function(){
        var id_row_children = $(this).data("id-dia");
        if($(".children_row_collapse_expand_dia_"+id_row_children).hasClass("rows_hidden_resumen_dia"))
        {
        	$(".children_row_collapse_expand_dia_"+id_row_children).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
            $(".children_row_collapse_expand_dia_"+id_row_children).toggle().removeClass('rows_hidden_resumen_dia').addClass('rows_expanded_resumen_dia');
            $(".parent_row_collapse_expand_dia_"+id_row_children).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
            $(".all_parent").find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
        }else{
            $(".children_row_collapse_expand_dia_"+id_row_children).toggle().removeClass('rows_expanded_resumen_dia').addClass('rows_hidden_resumen_dia');
            $(".parent_row_collapse_expand_dia_"+id_row_children).find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
            $(".all_parent").find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
        }
    })

}
function sec_reportes_ejecutar_resumen_dia(type, fn) {
	return sec_reportes_export_table_to_excel_resumen_dia('tabla_reportes_resumen_dia', type || 'xlsx', fn);
}
function sec_reportes_validar_exportacion_resumen_dia(s) {
	if(typeof ArrayBuffer !== 'undefined') {
		var buf = new ArrayBuffer(s.length);
		var view = new Uint8Array(buf);
		for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
		return buf;
	} else{
		var buf = new Array(s.length);
		for (var i=0; i!=s.length; ++i) buf[i] = s.charCodeAt(i) & 0xFF;
		return buf;
	}
}
function sec_reportes_export_table_to_excel_resumen_dia(id, type, fn) {
	var wb = XLSX.utils.table_to_book(document.getElementById(id), {raw:true}, {sheet:"Sheet JS"});
	var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
	var fname = fn || 'tabla_reportes_resumen_dia.' + type;
		try{
			saveAs(new Blob([sec_reportes_validar_exportacion_resumen_dia(wbout)],{type:"application/octet-stream"}), fname);
		} catch(e) {
			if(typeof console != 'undefined') console.log(e, wbout);
		}
	return wbout;
}
function sec_reportes_get_table_to_export_resumen_dia(pid, iid, fmt, ofile) {
	if(fallback) {
		if(document.getElementById(iid)) document.getElementById(iid).hidden = true;
		Downloadify.create(pid,{
			swf: 'media/downloadify.swf',
			downloadImage: 'download.png',
			width: 100,
			height: 30,
			filename: ofile, data: function() { var o = sec_reportes_ejecutar_resumen_dia(fmt, ofile); return window.btoa(o); },
			transparent: false,
			append: false,
			dataType: 'base64',
			onComplete: function(){ alert('Your File Has Been Saved!'); },
			onCancel: function(){ alert('You have cancelled the saving of this file.'); },
			onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
		});
	}//else document.getElementById(pid).innerHTML = "";
}
