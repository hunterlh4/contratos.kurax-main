var reportes_web_total_inicio_fecha_localstorage = false;
var reportes_web_total_fin_fecha_localstorage = false;
var $table_wt = false;
function sec_reportes_web_total(){
	console.log("sec_reportes_web_total");
	sec_reportes_web_total_settings();
	sec_reportes_web_total_events();
	sec_reportes_web_total_get_reportes();	
	sec_reportes_web_total_get_canales_venta();
	sec_reportes_web_total_get_locales();
	sec_reportes_web_total_get_redes();
}
function sec_reportes_web_total_get_redes(){
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
					$(".red_web_total").append(new_option);
				});
				$('.red_web_total').select2({closeOnSelect: false});
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
function sec_reportes_web_total_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_web_total_get_canales_venta","data":data});
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
						$(".canalventareportewebtotal").append(new_option);

					});
					$('.canalventareportewebtotal').select2({closeOnSelect: false});
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
function sec_reportes_web_total_get_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_web_total_get_locales","data":data});
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
						$(".localreportewebtotal").append(new_option);
					});
					$('.localreportewebtotal').select2({closeOnSelect: false});
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
function sec_reportes_web_total_get_reportes(){
	loading(true);
	var get_reportes_data = {};
	get_reportes_data.where = "web_total";
	get_reportes_data.filtro = {};
	get_reportes_data.filtro.fecha_inicio = $('.reportes_web_total_inicio_fecha').val();
	get_reportes_data.filtro.fecha_fin = $('.reportes_web_total_fin_fecha').val();
	get_reportes_data.filtro.locales = $('.localreportewebtotal').val();
	get_reportes_data.filtro.canales_de_venta  = $('.canalventareportewebtotal').val();		
	get_reportes_data.filtro.red_id= $('.red_web_total').val();
	localStorage.setItem("reportes_web_total_inicio_fecha_localstorage",get_reportes_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_web_total_fin_fecha_localstorage",get_reportes_data.filtro.fecha_fin);
	auditoria_send({"proceso":"sec_reportes_web_total_get_reportes","data":get_reportes_data});
	$.ajax({
		type: "POST",
		url: "/api/?json",		
		data: get_reportes_data,
		beforeSend: function( xhr ) {
		}
	})
	.done(function(responsedata, textStatus, jqXHR ) {
		try{
			var obj = jQuery.parseJSON(responsedata);
			// console.log(obj);
			sec_reportes_web_total_process_data(obj);	
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
		console.log( "La solicitud reportes a fallado: " +  textStatus);
	});  
}
function sec_reportes_web_total_process_data(obj){
	var nombre_mes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];  
		var cols = Object();
		cols["anio"]="Año";
		cols["mes"]="Mes";
		cols["numero_registros"]="# Registros";
		cols["bonus_web"]="Bonus web";
		cols["dinero_depositado"]="Dinero depositado";
		cols["dinero_retirado_web"]="Dinero retirado web";
		cols["caja_web"]="Caja web";
		cols["dinero_apostado"]="Dinero apostado";
		cols["payout_money"]="Payout money";
		cols["net_win"]="Net win";
		cols["pay_out"]="Pay out";
		cols["hold"]="Hold (%)";
		cols["tickets_emitidos"]="Tickets emitidos";
		cols["tickets_ganados"]="Num tickets ganados ";
		cols["apuesta_por_tickets"]="Apuesta x tickets";
		cols["tickets_premiados"]="% Tickets premiados";	
		cols["creditos_en_web"]="Créditos en web (Jugadores)";
		var array_info_meses=[];
		var array_meses=[]; 
		$.each(obj.data, function(anio, val_anio) {

			if (anio!="total") {
				$.each(val_anio, function(mes, val_mes) {
						var info_detalles_mes={};
							if (mes=="total") {
								info_detalles_mes.anio=anio;
								info_detalles_mes.mes="total "+anio;
							}else{
								info_detalles_mes.mes=nombre_mes[parseInt(mes)-1];	
								info_detalles_mes.anio=anio;													
							}	
							info_detalles_mes.apuesta_x_ticket = val_mes.apuesta_x_ticket;
							info_detalles_mes.bonus_web = val_mes.bonus_web;
							info_detalles_mes.caja_web = val_mes.caja_web;
							info_detalles_mes.creditos_web = val_mes.creditos_web;
							info_detalles_mes.dinero_apostado = val_mes.dinero_apostado;
							info_detalles_mes.dinero_depositado = val_mes.dinero_depositado;
							info_detalles_mes.dinero_retirado = val_mes.dinero_retirado;
							info_detalles_mes.hold = val_mes.hold;
							info_detalles_mes.net_win = val_mes.net_win;
							info_detalles_mes.num_registros = val_mes.num_registros;
							info_detalles_mes.num_tickets = val_mes.num_tickets;
							info_detalles_mes.pay_out = val_mes.pay_out;
							info_detalles_mes.payout_money = val_mes.payout_money;
							info_detalles_mes.tickets_pagados = val_mes.tickets_pagados;
							info_detalles_mes.tickets_premiados = val_mes.tickets_premiados;
							array_info_meses.push(info_detalles_mes);
				});

			};
		});
		var current_anio="";
		var totales_por_anio = [];
		$.each(obj.data, function(val_anio_total, val_anio) {
			if (val_anio_total!="total") {
				current_anio=val_anio_total;
			};
			if (val_anio_total=="total") {
				var info_anio_total={};
					info_anio_total.anio=current_anio;
					info_anio_total.apuesta_x_ticket = val_anio.apuesta_x_ticket;
					info_anio_total.bonus_web = val_anio.bonus_web;
					info_anio_total.caja_web = val_anio.caja_web;
					info_anio_total.creditos_web = val_anio.creditos_web;
					info_anio_total.dinero_apostado = val_anio.dinero_apostado;
					info_anio_total.dinero_depositado = val_anio.dinero_depositado;
					info_anio_total.dinero_retirado = val_anio.dinero_retirado;
					info_anio_total.hold = val_anio.hold;
					info_anio_total.net_win = val_anio.net_win;
					info_anio_total.num_registros = val_anio.num_registros;
					info_anio_total.num_tickets = val_anio.num_tickets;
					info_anio_total.pay_out = val_anio.pay_out;
					info_anio_total.payout_money = val_anio.payout_money;
					info_anio_total.tickets_pagados = val_anio.tickets_pagados;
					info_anio_total.tickets_premiados = val_anio.tickets_premiados				;
				totales_por_anio.push(info_anio_total);
			};
		});		
		var html = "<table id='tabla_reportes_web_total' cellspacing='0' class='table'>";
			html+="<thead>";
				html+="<tr>";
					html+="<th class='th_exportar_xlsx' colspan='2'>";
						html+="<input type='submit' class='btn btn-success btn-xs btn_export_xls_web_total' data-button='xls' value='Excel XLS' data-toggle='tooltip' data-placement='top' title='Exportar Excel formato XLS'/>";
					html+="</th>";
					for (var i = 2; i <14; i++) {
						if(i>=0 && i<13){html+="<th class='cabeceras_sin_bordes'></th>";}	
						if (i==13) {html+="<th colspan='4' class='cabecera_cantidad_web_total'>CANTIDAD</th>";}
						if(i>=13 && i<=14){html+="<th class='cabeceras_sin_bordes'></th>";}	
					};
				html+="</tr>";
				html+="<tr>";
					html += '<td class="titulo_seleccionar_todos_collapse_expand_row">';
						html += '<button class="btn_collapse_expand_row_web_total all_parent">';
							html += '<span class="glyphicon glyphicon-plus"/>';
						html += '</button>';
					html += '</td>';				
					$.each(cols, function(index_columnas, val_columnas) {
						html+="<th class='titulo_columna_tabla titulo_columna_tabla_"+index_columnas+"' >"+val_columnas+"</th>";
					});
				html += "</tr>";
			html += "</thead>";
			html += "<tbody>";
			$.each(array_info_meses, function(index_detalles, val_detalles) {
				if (val_detalles.mes=="total "+val_detalles.anio) {
					html += '<tr class="sub_total_web_total">';
								html += '<td>';
									html += '<button class="btn_collapse_expand_row_web_total parent parent_row_collapse_expand_'+val_detalles.anio+'" id="'+val_detalles.anio+'" >';
										html += '<span class="glyphicon glyphicon-plus"/>';
									html += '</button>';
								html += '</td>';
								html += "<td class='anio'>"+val_detalles.anio+"</td>";
								html += "<td class='mes_"+val_detalles.mes+"' >"+val_detalles.mes+"</td>";
								html += "<td class='num_registros'>"+val_detalles.num_registros+"</td>";	
								html += "<td class='bonus_web'>"+val_detalles.bonus_web+"</td>";
								html += "<td class='dinero_depositado'>"+val_detalles.dinero_depositado+"</td>";
								html += "<td class='dinero_retirado'>"+val_detalles.dinero_retirado+"</td>";
								html += "<td class='caja_web'>"+val_detalles.caja_web+"</td>";
								html += "<td class='dinero_apostado'>"+val_detalles.dinero_apostado+"</td>";
								html += "<td class='payout_money'>"+val_detalles.payout_money+"</td>";														
								html += "<td class='net_win'>"+val_detalles.net_win+"</td>";
								html += "<td class='pay_out'>"+val_detalles.pay_out+"</td>";
								html += "<td class='hold'>"+val_detalles.hold+"</td>";
								html += "<td class='num_tickets'>"+val_detalles.num_tickets+"</td>";
								html += "<td class='tickets_pagados'>"+val_detalles.tickets_pagados+"</td>";
								html += "<td class='apuesta_x_ticket'>"+val_detalles.apuesta_x_ticket+"</td>";
								html += "<td class='tickets_premiados'>"+val_detalles.tickets_premiados+"</td>";
								html += "<td class='creditos_web'>"+val_detalles.creditos_web+"</td>";
					html += "</tr>";					
				}else{
					html += '<tr class="mes_'+val_detalles.mes+' children children_row_collapse_expand_'+val_detalles.anio+' rows_hidden_web_total">';
								html += '<td>';
									html += '<button class="btn_collapse_expand_row_web_total parent parent_row_collapse_expand_'+val_detalles.anio+'" id="'+val_detalles.anio+'" >';
										html += '<span class="glyphicon glyphicon-plus"/>';
									html += '</button>';
								html += '</td>';
								html += "<td class='anio'>"+val_detalles.anio+"</td>";
								html += "<td class='mes_"+val_detalles.mes+"' >"+val_detalles.mes+"</td>";
								html += "<td class='num_registros'>"+val_detalles.num_registros+"</td>";	
								html += "<td class='bonus_web'>"+val_detalles.bonus_web+"</td>";
								html += "<td class='dinero_depositado'>"+val_detalles.dinero_depositado+"</td>";
								html += "<td class='dinero_retirado'>"+val_detalles.dinero_retirado+"</td>";
								html += "<td class='caja_web'>"+val_detalles.caja_web+"</td>";
								html += "<td class='dinero_apostado'>"+val_detalles.dinero_apostado+"</td>";
								html += "<td class='payout_money'>"+val_detalles.payout_money+"</td>";														
								html += "<td class='net_win'>"+val_detalles.net_win+"</td>";
								html += "<td class='pay_out'>"+val_detalles.pay_out+"</td>";
								html += "<td class='hold'>"+val_detalles.hold+"</td>";
								html += "<td class='num_tickets'>"+val_detalles.num_tickets+"</td>";
								html += "<td class='tickets_pagados'>"+val_detalles.tickets_pagados+"</td>";
								html += "<td class='apuesta_x_ticket'>"+val_detalles.apuesta_x_ticket+"</td>";
								html += "<td class='tickets_premiados'>"+val_detalles.tickets_premiados+"</td>";
								html += "<td class='creditos_web'>"+val_detalles.creditos_web+"</td>";
					html += "</tr>";					
				}
			});
			$.each(totales_por_anio, function(index, val_totales_anio) {
				html += '<tr class="total_general_web_total">';
						html += '<td>';
							html += '<button class="btn_collapse_expand_row_web_total all_parent">';
								html += '<span class="glyphicon glyphicon-plus"/>';
							html += '</button>';
						html += '</td>';
						html += "<td colspan='2'><span class='titulo_total_general'>Total general</span></td>";
						html += "<td class='num_registros'>"+val_totales_anio.num_registros+"</td>";
						html += "<td class='bonus_web'>"+val_totales_anio.bonus_web+"</td>";
						html += "<td class='dinero_depositado'> "+val_totales_anio.dinero_depositado+"</td>";
						html += "<td class='dinero_retirado'>"+val_totales_anio.dinero_retirado+"</td>";
						html += "<td class='caja_web caja_web_general'>"+val_totales_anio.caja_web+"</td>";
						html += "<td class='dinero_apostado dinero_apostado_total_general'>"+val_totales_anio.dinero_apostado+"</td>";
						html += "<td class='payout_money payout_money_total_general'>"+val_totales_anio.payout_money+"</td>";
						html += "<td class='net_win net_win_total_general'>"+val_totales_anio.net_win+"</td>";
						html += "<td class='pay_out pay_out_total_general'>"+val_totales_anio.pay_out+"</td>";
						html += "<td class='hold hold_total_general'>"+val_totales_anio.hold+"</td>";
						html += "<td class='num_tickets num_tickets_total_general'>"+val_totales_anio.num_tickets+"</td>";
						html += "<td class='tickets_pagados tickets_pagados_total_general'>"+val_totales_anio.tickets_pagados+"</td>";
						html += "<td class='apuesta_x_ticket apuesta_x_ticket_total_general'>"+val_totales_anio.apuesta_x_ticket+"</td>";					
						html += "<td class='tickets_premiados tickets_premiados_total_general'>"+val_totales_anio.tickets_premiados+"</td>";																				
						html += "<td class='creditos_web creditos_web_total_general'>"+val_totales_anio.creditos_web+"</td>";
				html += "</tr>";
			});
			html += "</tbody>";
		html += "</table>";
		$(".tabla_contenedor_reportes_web_total").html(html);
		sec_reportes_web_total_events();
		sec_reportes_web_total_collapse_expands_rows();		
		loading();
}
function sec_reportes_web_total_settings(){
		$('.localreportewebtotal').select2({
			closeOnSelect: false,            
			allowClear: true,
		});	
		$('.canalventareportewebtotal').select2({
			closeOnSelect: false,            
			allowClear: true,
		});				
		$('.red_web_total').select2({
			closeOnSelect: false,            
			allowClear: true,
		});	
		$('.reportes_web_total_datepicker')
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
		reportes_web_total_inicio_fecha_localstorage = localStorage.getItem("reportes_web_total_inicio_fecha_localstorage");
		if(reportes_web_total_inicio_fecha_localstorage){
			var reportes_web_total_inicio_fecha_localstorage_new = moment(reportes_web_total_inicio_fecha_localstorage).format("DD-MM-YYYY");
			$("#input_text-reportes_web_total_inicio_fecha")
				.datepicker("setDate", reportes_web_total_inicio_fecha_localstorage_new)
				.trigger('change');
		}
		reportes_web_total_fin_fecha_localstorage = localStorage.getItem("reportes_web_total_fin_fecha_localstorage");
		if(reportes_web_total_fin_fecha_localstorage){
			
			var reportes_web_total_fin_fecha_localstorage_new = moment(reportes_web_total_fin_fecha_localstorage).format("DD-MM-YYYY");
			$("#input_text-reportes_web_total_fin_fecha")
				.datepicker("setDate", reportes_web_total_fin_fecha_localstorage_new)
				.trigger('change');
		}
}
function sec_reportes_web_total_events(){
	// $(".btn_export_xlsx").off("click").on("click",function(){
	// 	event.preventDefault();
	// 	var buton = $(this);
	// 	var data = Object();
	// 	data.filtro = Object();	
	// 	data.where="validar_usuario_permiso_botones";			
	// 	$(".input_text_validacion").each(function(index, el) {
	// 		data.filtro[$(el).attr("data-col")]=$(el).val();
	// 	});	
	// 	data.filtro.text_btn = buton.data("button");
	// 	console.log(data);
	// 	auditoria_send({"proceso":"validar_usuario_permiso_botones","data":data});
	// 	$.ajax({
	// 		data: data,
	// 		type: "POST",
	// 		dataType: "json",
	// 		url: "/api/?json"
	// 	})
	// 	.done(function( dataresponse) {
	// 		try{
	// 			console.log(dataresponse);
	// 			if (dataresponse.permisos==true) {
	// 				// var reinit = $table_wt.floatThead('destroy');		
	// 				sec_reportes_ejecutar_reporte_web_total('xlsx');
	// 		 		sec_reportes_get_table_to_export('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_web_total.xlsx');			
	// 			 	// reinit();
	// 			}else{
	// 				swal({
	// 					title: 'No tienes permisos',
	// 					type: "info",
	// 					timer: 2000,
	// 				}, function(){
	// 					swal.close();
	// 				});			
	// 			}
		
	// 		}catch(err){
	//             swal({
	//                 title: 'Error en la base de datos',
	//                 type: "warning",
	//                 timer: 2000,
	//             }, function(){
	//                 swal.close();
	//                 loading();
	//             }); 
	// 		}
	// 	})
	// 	.fail(function( jqXHR, textStatus, errorThrown ) {
	// 		if ( console && console.log ) {
	// 			console.log( "La solicitud validar permisos exportar xlsx a fallado: " +  textStatus);
	// 		}
	// 	})
	// });	
	$(".btn_export_xls_web_total").off("click").on("click",function(){
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
					// var reinit = $table_wt.floatThead('destroy');		
					sec_reportes_ejecutar_reporte_web_total('biff2', 'reporte_web_total.xls');	
			 		sec_reportes_get_table_to_export('biff2btn', 'xportbiff2', 'biff2', 'reporte_web_total.xls');			
				 	// reinit();
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
	$(".btn_filtrar_reporte_web_total").off().on("click",function(){
		var btn = $(this).data("button");
		sec_reportes_web_total_validacion_permisos_usuarios(btn);
	});		
	/*	
	$table_wt = $('#tabla_reportes_web_total');
	$table_wt.floatThead({
		top:44,
		responsiveContainer: function($table_wt){
			return $table_wt.closest('.table-responsive');
		}
	});
	*/
	$('td').each(function() {
		var cellvalue = $(this).html();
		if ( cellvalue < 0) {
			$(this).wrapInner('<strong class="negative_number"></strong>');    
		}
	});
}
function sec_reportes_ejecutar_reporte_web_total(type, fn) { 
   return sec_reportes_export_table_to_excel('tabla_reportes_web_total', type || 'xlsx', fn); 
 }  
function sec_reportes_validar_exportacion_web_total(s) {
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
     var fname = fn || 'tabla_reportes_web_total.' + type;
     try {
       saveAs(new Blob([sec_reportes_validar_exportacion_web_total(wbout)],{type:"application/octet-stream"}), fname);
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
       filename: ofile, data: function() { var o = sec_reportes_ejecutar_reporte_web_total(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";
}
function sec_reportes_web_total_validacion_permisos_usuarios(btn){
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			console.log(event.event_data);
			loading(true);
			sec_reportes_web_total_get_reportes();
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
function sec_reportes_web_total_collapse_expands_rows(){
	$(".all_parent").off().on("click",function(){
		if ($(".children").hasClass("rows_expanded_web_total") ) {
			$(".children").hide();
			$(".children").removeClass('rows_expanded_web_total').addClass('rows_hidden_web_total');
			$(this).find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
			$(".btn_collapse_expand_row_web_total").find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');			
		}else{
			$(".children").show();
			$(".children").removeClass('rows_hidden_web_total').addClass('rows_expanded_web_total');
			$(this).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
			$(".btn_collapse_expand_row_web_total").find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');				
		}
	})
	$(".parent").off().on("click",function(){
		var id_row_children = $(this).attr("id");
		if($(".children_row_collapse_expand_"+id_row_children).hasClass("rows_hidden_web_total")){
			$(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_hidden_web_total').addClass('rows_expanded_web_total');
            $(".parent_row_collapse_expand_"+id_row_children).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
            $(".all_parent").find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');			
		}else{
			$(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_expanded_web_total').addClass('rows_hidden_web_total');                
            $(".parent_row_collapse_expand_"+id_row_children).find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
            $(".all_parent").find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');  			
		}
	})	
}