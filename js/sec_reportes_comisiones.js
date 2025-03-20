var reportes_resumen_dia_inicio_fecha_localstorage = false;
var reportes_resumen_dia_fin_fecha_localstorage = false;
var $table_rd = false;
var array_local_nombres=Array();
function sec_reportes_comisiones(){
	console.log("sec_reportes_comisiones");
	loading(true);
	sec_reportes_comisiones_settings();
	sec_reportes_comisiones_events();
	sec_reportes_comisiones_get_canales_venta();
	sec_reportes_comisiones_locales();	
}
function sec_reportes_comisiones_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
	auditoria_send({"proceso":"sec_reportes_comisiones_get_canales_venta","data":data});
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
					$(".canalventareporte_comisiones").append(new_option);

				});
				$('.canalventareporte_comisiones').select2({closeOnSelect: false});
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
function sec_reportes_comisiones_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
	auditoria_send({"proceso":"sec_reportes_comisiones_locales","data":data});
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
					$(".localreporte_comisiones").append(new_option);
				});
				$('.localreporte_comisiones').select2({closeOnSelect: false});
			}
			sec_reportes_comisiones_get_data();
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
function sec_reportes_comisiones_events(){
	$(".btn_filtrar_reporte_comisiones").off().on("click",function(){
		var btn = $(this).data("button");
		sec_reportes_comisiones_validacion_permisos_usuarios(btn);
	})
	sec_reportes_export_excel_table_comisiones();
    $table_rd = $('#tabla_reportes_comisiones');
    $table_rd.floatThead({
    	top:50
    });	
    sec_reportes_comisiones_expand_collapse_rows();	
	$('td').each(function() {
		var cellvalue = $(this).html();
		if ( cellvalue < 0) {
			$(this).wrapInner('<strong class="negative_number_reportes_comisiones"></strong>');    
		}
	});    
}
function sec_reportes_comisiones_settings(){
	$('.localreporte_comisiones').select2({
		closeOnSelect: false,            
		allowClear: true,
	});
	$('.canalventareporte_comisiones').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.red_reporte_comisiones').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.reportes_comisiones_datepicker')
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

	reportes_comisiones_inicio_fecha_localstorage = localStorage.getItem("reportes_comisiones_inicio_fecha_localstorage");
	if(reportes_comisiones_inicio_fecha_localstorage){
		var reportes_comisiones_inicio_fecha_localstorage_new = moment(reportes_comisiones_inicio_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_comisiones_inicio_fecha")
			.datepicker("setDate", reportes_comisiones_inicio_fecha_localstorage_new)
			.trigger('change');
	}

	reportes_comisiones_fin_fecha_localstorage = localStorage.getItem("reportes_comisiones_fin_fecha_localstorage");
	if(reportes_comisiones_fin_fecha_localstorage){
		var reportes_comisiones_fin_fecha_localstorage_new = moment(reportes_comisiones_fin_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_comisiones_fin_fecha")
			.datepicker("setDate", reportes_comisiones_fin_fecha_localstorage_new)
			.trigger('change');
	}	
}
function sec_reportes_comisiones_get_data(){
	var get_comisiones_data = {};
	get_comisiones_data.where = "reporte_comisiones";
	get_comisiones_data.filtro = {};
	get_comisiones_data.filtro.fecha_inicio = $('.reportes_comisiones_inicio_fecha').val();
	get_comisiones_data.filtro.fecha_fin = $('.reportes_comisiones_fin_fecha').val();
	get_comisiones_data.filtro.locales = $('.localreporte_comisiones').val();
	get_comisiones_data.filtro.canales_de_venta = $('.canalventareporte_comisiones').val();
	get_comisiones_data.filtro.red_id=$('.red_reporte_comisiones').val();
	localStorage.setItem("reportes_comisiones_inicio_fecha_localstorage",get_comisiones_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_comisiones_fin_fecha_localstorage",get_comisiones_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_reportes_comisiones_get_data","data":get_comisiones_data});
    console.log(get_comisiones_data);
    $.ajax({
		url: "/api/?json",
        type: 'POST',
        data:get_comisiones_data
    })
    .done(function(dataresponse) {
		try{
	        var obj = JSON.parse(dataresponse);
	        console.log(obj);
	        sec_reportes_comisiones_create_table(obj);
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
function sec_reportes_comisiones_create_table(obj){
		var nombre_mes = ["","Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"]; 	
		var cols = {};
			cols["anio_comisiones"] = "Año";
			cols["mes_comisiones"] = "Mes";
			cols["fecha_comisiones"] = "Fecha";
			cols["semana_comisiones"] = "Semana";
			cols["canal_de_venta_comisiones"] = "&nbsp;&nbsp;Canal de Venta&nbsp;&nbsp;";

			cols["cliente_comisiones"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Clientes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			cols["punto_de_venta_comisiones"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Punto de Venta&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			cols["tipo_administracion_comisiones"] = "Tipo Administrac.";
			cols["tipo_de_punto_comisiones"] = "Tipo de Punto";
			cols["qty_comisiones"] = "QTY";

			cols["porcentaje_apostado_comisiones"] = "% Apostado";
			cols["porcentaje_web_comisiones"] = "% WEB";
			cols["total_depositado_comisiones"] = "Total Depositado";
			cols["anulado_retirado_comisiones"] = "Anulado/Retirado";
			cols["total_apostado_comisiones"] = "Total Apostado";

			cols["tk_pagados_en_su_punto"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tk Pagados en<br> su Punto&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			cols["tk_pagados_en_otro_punto"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tk Pagados en<br> otro Punto&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			cols["total_premio_pagados_comisiones"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total Premios<br> Pagados&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			cols["resultado_del_negocio_comisiones"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Resultado del<br> Negocio&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			cols["caja_comisiones"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Caja&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

			cols["depositado_web_comisiones"] = "Depositado Web";
			cols["retirado_web_comisiones"] = "Retirado Web";
			cols["difer_web_comisiones"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Difer. Web&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			cols["tk_pagado_de_otro_punto_comisiones"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tk Pagado de<br> otro Punto&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			cols["participacion_cliente_freegames_comisiones"] = "Participación cliente / Freegames";

			cols["comision_apostado_comisiones"] = "Comision Apostado";
			cols["comision_web_comisiones"] = "Comision Web";
			cols["total_a_abonar_freegames_comisiones"] = "Total a abonar FreeGames";


		var html ="<table class='tabla_reportes_comisiones' id='tabla_reportes_comisiones' width='100%' cellspacing='0'>";
	    	html +='<thead style="background-color: #C0C0C0 !important; color: #333 !important;">';
	    		html +='<tr>';	
	    				html += '<th id="th_boton"><button class="all_parent_comisiones btn_expand_collapse_rows"><span class="glyphicon glyphicon-pushpin"></span></button></th>'; 
					$.each(cols, function(index_cols, val_cols) {
						html +='<th id="th_'+index_cols+'" class="'+index_cols+'">'+val_cols+'</th>';
					});
	    		html +='<tr>';
		   	html +='</thead><tbody>';


		   	$.each(obj.data, function(anio, val_anio) {
		   		$.each(val_anio, function(mes, val_mes) {
		   			 //console.log(val_mes);
		   			 $.each(val_mes, function(semanas, val_semanas) {
		   			 	$.each(val_semanas, function(details, val_details) {
							html += '<tr class="rows_hidden_comisiones children_comisiones children_row_collapse_expand_comisiones_'+anio+''+mes+'">';		
								html += '<td class="btn_collapse_expand_comisiones"><button class="parent_comisiones btn_expand_collapse_rows_comisiones" data-id="'+anio+''+mes+'"><span class="glyphicon glyphicon-pushpin"></span></button></td>';
								html += '<td class="anio_comisiones">'+validate_null_number(val_details.anio)+'</td>';
								html += '<td class="mes_comisiones">'+nombre_mes[parseInt(validate_null_number(val_details.mes))]+'</td>';
								html += '<td class="fecha_comisiones">'+validate_null_number(val_details.fecha)+'</td>';
								html += '<td class="semana_comisiones">'+validate_null_number(val_details.semana)+'</td>';
								html += '<td class="canal_de_venta_comisiones">'+validate_null_string(val_details.canal_de_venta)+'</td>';
								html += '<td class="cliente_comisiones">'+validate_null_string(val_details.cliente)+'</td>';
								html += '<td class="punto_de_venta_comisiones">'+validate_null_string(val_details.punto_de_venta)+'</td>';
								html += '<td class="tipo_admin_comisiones">'+validate_null_string(val_details.tipo_administrac)+'</td>';								
								html += '<td class="tipo_de_punto_comisiones">'+validate_null_string(val_details.tipo_de_punto)+'</td>';
								html += '<td class="qty_comisiones">'+validate_null_number(val_details.qty)+'</td>';
								html += '<td class="porcentaje_apostado_comisiones">'+validate_null_string(val_details.porcentaje_apostado)+'</td>';
								html += '<td class="porcentaje_web_apostado">'+validate_null_number(val_details.porcentaje_web)+'</td>';
								html += '<td class="total_depositado_comisiones">'+validate_null_number(val_details.total_depositado)+'</td>';					
								html += '<td class="total_anulado_retirado_comisiones">'+validate_null_number(val_details.total_anulado_retirado)+'</td>';
								html += '<td class="total_apostado_comisiones">'+validate_null_number(val_details.total_apostado)+'</td>';
								html += '<td class="total_pagados_en_su_punto_comisiones">'+validate_null_number(val_details.total_pagados_en_su_punto)+'</td>';
								html += '<td class="total_pagado_en_otro_punto_comisiones">'+validate_null_number(val_details.total_pagado_en_otro_punto)+'</td>';
								html += '<td class="total_premios_pagados_comisiones">'+validate_null_number(val_details.total_premios_pagados)+'</td>';						
								html += '<td class="resultado_del_negocio_comisiones">'+validate_null_number(val_details.resultado_del_negocio)+'</td>';
								html += '<td class="caja_comisiones">'+validate_null_number(val_details.caja)+'</td>';
								html += '<td class="total_depositado_web_comisiones">'+validate_null_number(val_details.total_depositado_web)+'</td>';
								html += '<td class="total_retirado_web_comisiones">'+validate_null_number(val_details.total_retirado_web)+'</td>';
								html += '<td class="difer_web_comisiones">'+validate_null_number(val_details.difer_web)+'</td>';
								html += '<td class="total_pagado_de_otro_punto_comisiones">'+validate_null_number(val_details.total_pagado_de_otro_punto)+'</td>';
								html += '<td class="participacion_cliente_freegames_comisiones">'+validate_null_number(val_details.participacion_freegames)+'</td>';								
								html += '<td class="comision_apostado_comisiones">'+validate_null_number(val_details.comision_apostado)+'</td>';
								html += '<td class="comision_web_comisiones">'+validate_null_number(val_details.comision_web)+'</td>';
								html += '<td class="total_a_abonar_freegames_comisiones">'+validate_null_number(val_details.total_a_abonar_freegames)+'</td>';
							html += '</tr>';
						});
		   			 });

					html += '<tr class="row_mes_collapse_expand_comisiones">';
						html += '<td class="sec_reportes_comisiones_button"><button class="parent_comisiones btn_expand_collapse_rows_comisiones" data-id="'+anio+''+mes+'"><span class="glyphicon glyphicon-pushpin"></span></button></td>';
						html += '<td class="anio_comisiones_anio">'+validate_null_number(anio)+'</td>';									
						html += '<td class="mes_comisiones_mes" >'+nombre_mes[parseInt(validate_null_number(mes))]+'</td>';
						html += '<td colspan="35"></td>';										
					html += '</tr>';


		   		});

				html += '<tr class="row_anio_collapse_expand_comisiones">';
					html += '<th id="th_boton_all_year"></th>'; 
					html += '<td class="comisiones_anio">'+validate_null_number(anio)+'</td>';									
					html += '<td class="mes_comisiones" ></td>';
					html += '<td colspan="35"></td>';										
				html += '</tr>';

		   	});


			html += '<tr class="row_collapse_expand_all_comisiones">';
				html += '<th id="th_boton_all_comisiones"><button class="all_parent_comisiones btn_expand_collapse_rows"><span class="glyphicon glyphicon-pushpin"></span></button></th>'; 
				html += '<td class="sec_reportes_comisiones_anio"></td>';									
				html += '<td class="sec_reportes_comisiones_mes" ></td>';
				html += '<td colspan="35"></td>';										
			html += '</tr>';


	    	html += '</tbody><tfoot><tr>';
	    	html += '</tr></tfoot>';
    	html+='</table>';	   			
	$(".tabla_contenedor_reportes_comisiones").html(html);
	sec_reportes_comisiones_events();
	loading();	
}
function sec_reportes_comisiones_expand_collapse_rows(){
	$(".all_parent_comisiones").off().on("click",function(){
		if ($(".children_comisiones").hasClass("rows_expanded_comisiones") ) {
			$(".children_comisiones").hide();
			$(".children_comisiones").removeClass('rows_expanded_comisiones').addClass('rows_hidden_comisiones');
		}else{
			$(".children_comisiones").show();
			$(".children_comisiones").removeClass('rows_hidden_comisiones').addClass('rows_expanded_comisiones');
		}
	})
	$(".parent_comisiones").off().on("click",function(){
		var id_row_children = $(this).data("id");
		if($(".children_row_collapse_expand_comisiones_"+id_row_children).hasClass("rows_hidden_comisiones"))
		{
			$(".children_row_collapse_expand_comisiones_"+id_row_children).toggle().removeClass('rows_hidden_comisiones').addClass('rows_expanded_comisiones');
		}else{
			$(".children_row_collapse_expand_comisiones_"+id_row_children).toggle().removeClass('rows_expanded_comisiones').addClass('rows_hidden_comisiones');                
		}
	})		
}
function sec_reportes_comisiones_validacion_permisos_usuarios(btn){
	console.log("btn_filtrar_resumen_dia:click");
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			loading(true);
			sec_reportes_comisiones_get_data();
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
function sec_reportes_ejecutar_comisiones(type, fn) {
   	return sec_reportes_export_table_to_excel_comisiones('tabla_reportes_comisiones', type || 'xlsx', fn);  
}  
function sec_reportes_validar_exportacion_comisiones(s) {
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
function sec_reportes_export_table_to_excel_comisiones(id, type, fn) {
     var wb = XLSX.utils.table_to_book(document.getElementById(id), {raw:true}, {sheet:"Sheet JS"});
     var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
     var fname = fn || 'tabla_reportes_comisiones.' + type;
     try {
       saveAs(new Blob([sec_reportes_validar_exportacion_comisiones(wbout)],{type:"application/octet-stream"}), fname);
     } catch(e) { if(typeof console != 'undefined') console.log(e, wbout); }
     return wbout;	
}
function sec_reportes_get_table_to_export_comisiones(pid, iid, fmt, ofile) {
   if(fallback) {
     if(document.getElementById(iid)) document.getElementById(iid).hidden = true; 
     Downloadify.create(pid,{
       swf: 'media/downloadify.swf',
       downloadImage: 'download.png',
       width: 100,
       height: 30,
       filename: ofile, data: function() { var o = sec_reportes_ejecutar_comisiones(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";	
}
function sec_reportes_export_excel_table_comisiones(){
	$(".btn_export_comisiones_xlsx").off().on("click",function(){
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
			auditoria_send({"proceso":"validar_usuario_permiso_botones reporte comisiones","data":data});
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
						sec_reportes_ejecutar_comisiones('xlsx');
						sec_reportes_get_table_to_export_comisiones('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_comisiones.xlsx');			
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
	$(".btn_export_comisiones_xls").off().on("click",function(){
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
					sec_reportes_ejecutar_comisiones('biff2', 'reporte_comisiones.xls');
					sec_reportes_get_table_to_export_comisiones('biff2btn', 'xportbiff2', 'biff2', 'reporte_comisiones.xls');				
				 	reinit();
				}else{
					swal({
						title: 'No tienes permisos',
						type: "info",
						timer: 2000,
					}, function(){
						swal.close();
						loading();
					});			
				}
			}catch(err){
	            swal({
	                title: 'Error en la base de datos',
	                type: "warning",
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