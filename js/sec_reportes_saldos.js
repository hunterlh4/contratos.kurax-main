var reportes_saldos_inicio_fecha_localstorage = false;
var reportes_saldos_fin_fecha_localstorage = false;
var $table_rd = false;
var array_local_nombres=Array();
function sec_reportes_saldos(){
	console.log("sec_reportes_saldos");
	sec_reportes_saldos_settings();
	sec_reportes_saldos_events();
	sec_reportes_saldos_get_canales_venta();
	sec_reportes_saldos_locales();	
}
function sec_reportes_saldos_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
	auditoria_send({"proceso":"sec_reportes_saldos_get_canales_venta","data":data});
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
					$(".canalventareporte_saldos").append(new_option);

				});
				$('.canalventareporte_saldos').select2({closeOnSelect: false});
			}
		}catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "info",
                timer: 2000,
            }, function(){
                swal.close();
            }); 
		}
	})
	.fail(function( jqXHR, textStatus, errorThrown ) {
		if ( console && console.log ) {
			console.log( "La solicitud canales de ventas a fallado: " +  textStatus);
		}
	})	
}
function sec_reportes_saldos_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
	auditoria_send({"proceso":"sec_reportes_saldos_locales","data":data});
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
					$(".localreporte_saldos").append(new_option);
				});
				$('.localreporte_saldos').select2({closeOnSelect: false});
			}
			sec_reportes_saldos_get_data();
		}catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "info",
                timer: 2000,
            }, function(){
                swal.close();
            }); 
		}
	})
	.fail(function( jqXHR, textStatus, errorThrown ) {
		if ( console && console.log ) {
			console.log( "La solicitud locales a fallado: " +  textStatus);
		}
	})
}
function sec_reportes_saldos_events(){
	$(".btn_filtrar_reporte_saldos").off().on("click",function(){
		var btn = $(this).data("button");
		sec_reportes_saldos_validacion_permisos_usuarios(btn);
	})
	sec_reportes_export_excel_table_saldos();
    $table_rd = $('#tabla_reportes_saldos');
    $table_rd.floatThead({
    	top:50
    });	
    sec_reportes_saldos_expand_collapse_rows();	
}
function sec_reportes_saldos_settings(){
	$('.localreporte_saldos').select2({
		closeOnSelect: false,            
		allowClear: true,
	});
	$('.canalventareporte_saldos').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.red_reporte_saldos').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.reportes_saldos_datepicker')
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

	reportes_saldos_inicio_fecha_localstorage = localStorage.getItem("reportes_saldos_inicio_fecha_localstorage");
	if(reportes_saldos_inicio_fecha_localstorage){
		var reportes_saldos_inicio_fecha_localstorage_new = moment(reportes_saldos_inicio_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_saldos_inicio_fecha")
			.datepicker("setDate", reportes_saldos_inicio_fecha_localstorage_new)
			.trigger('change');
	}

	reportes_saldos_fin_fecha_localstorage = localStorage.getItem("reportes_saldos_fin_fecha_localstorage");
	if(reportes_saldos_fin_fecha_localstorage){
		var reportes_saldos_fin_fecha_localstorage_new = moment(reportes_saldos_fin_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_saldos_fin_fecha")
			.datepicker("setDate", reportes_saldos_fin_fecha_localstorage_new)
			.trigger('change');
	}	
}
function sec_reportes_saldos_get_data(){
	var get_saldos_data = {};
	get_saldos_data.where = "saldos";
	get_saldos_data.filtro = {};
	get_saldos_data.filtro.fecha_inicio = $('.reportes_saldos_inicio_fecha').val();
	get_saldos_data.filtro.fecha_fin = $('.reportes_saldos_fin_fecha').val();
	get_saldos_data.filtro.locales = $('.localreporte_saldos').val();
	get_saldos_data.filtro.canales_de_venta = $('.canalventareporte_saldos').val();
	get_saldos_data.filtro.red_id=$('.red_reporte_saldos').val();
	localStorage.setItem("reportes_saldos_inicio_fecha_localstorage",get_saldos_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_saldos_fin_fecha_localstorage",get_saldos_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_reportes_saldos_get_data","data":get_saldos_data});
    console.log(get_saldos_data);
    $.ajax({
		url: "/api/?json",
        type: 'POST',
        data:get_saldos_data
    })
    .done(function(dataresponse) {
		try{
	        var obj = JSON.parse(dataresponse);
	        sec_reportes_saldos_create_table(obj);
		}catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "info",
                timer: 2000,
            }, function(){
                swal.close();
            }); 
		}
    })
    .fail(function() {
        console.log("error");
    })	
}
function sec_reportes_saldos_create_table(obj){
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

		var html ="<table class='tabla_reportes_saldos' id='tabla_reportes_saldos' width='100%' cellspacing='0'>";
	    	html +='<thead style="background-color: #C0C0C0 !important; color: #333 !important;">';
	    		html +='<tr>';	
	    				html += '<th id="th_boton"><button class="all_parent btn_expand_collapse_rows"><span class="glyphicon glyphicon-pushpin"></span></button></th>'; 
					$.each(cols, function(index_cols, val_cols) {
						html +='<th id="th_'+index_cols+'" class="'+index_cols+'">'+val_cols+'</th>';
					});
	    		html +='<tr>';
		   	html +='</thead><tbody>';

					html += '<tr class="total_por_anio_row parent_anio">';		
						html += '<td><button class="all_parent btn_expand_collapse_rows"><span class="glyphicon glyphicon-pushpin"></span></button></td>';
						html += '<td class="sec_reportes_resumen_dia_text">'+anio+'</td>';
						html += '<td class="sec_reportes_resumen_dia_text"></td>';

						html += '<td class="sec_reportes_resumen_dia_text"></td>';
						html += '<td class="sec_reportes_resumen_dia_text"></td>';
						html += '<td class="sec_reportes_resumen_dia_text">Total</td>';

						html += '<td class="sec_reportes_resumen_dia_text">Año</td>';
						html += '<td class="sec_reportes_resumen_dia_text"></td>';
						html += '<td class="sec_reportes_resumen_dia_text"></td>';
					html += '</tr>';
			   					
	    	html += '</tbody><tfoot><tr>';
	    	html += '</tr></tfoot>';
    	html+='</table>';	   			
	$(".tabla_contenedor_reportes_saldos").html(html);
	sec_reportes_saldos_events();
	loading();	
}
function sec_reportes_saldos_expand_collapse_rows(){
	$(".all_parent").off().on("click",function(){
		if ($(".children").hasClass("rows_expanded_saldos") ) {
			$(".children").hide();
			$(".children").removeClass('rows_expanded_saldos').addClass('rows_hidden_saldos');
		}else{
			$(".children").show();
			$(".children").removeClass('rows_hidden_saldos').addClass('rows_expanded_saldos');
		}
	})
	$(".parent_dia").off().on("click",function(){
		var id_row_children = $(this).data("id-dia");
		if($(".children_row_collapse_expand_"+id_row_children).hasClass("rows_hidden_saldos"))
		{
			$(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_hidden_saldos').addClass('rows_expanded_saldos');
		}else{
			$(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_expanded_saldos').addClass('rows_hidden_saldos');                
		}
	})		
}
function sec_reportes_saldos_validacion_permisos_usuarios(btn){
	console.log("btn_filtrar_resumen_dia:click");
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			loading(true);
			sec_reportes_saldos_get_data();			
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
function sec_reportes_ejecutar_saldos(type, fn) {
   	return sec_reportes_export_table_to_excel_saldos('tabla_reportes_saldos', type || 'xlsx', fn);  
}  
function sec_reportes_validar_exportacion_saldos(s) {
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
function sec_reportes_export_table_to_excel_saldos(id, type, fn) {
     var wb = XLSX.utils.table_to_book(document.getElementById(id), {sheet:"Sheet JS"});
     var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
     var fname = fn || 'tabla_reportes_saldos.' + type;
     try {
       saveAs(new Blob([sec_reportes_validar_exportacion_saldos(wbout)],{type:"application/octet-stream"}), fname);
     } catch(e) { if(typeof console != 'undefined') console.log(e, wbout); }
     return wbout;	
}
function sec_reportes_get_table_to_export_saldos(pid, iid, fmt, ofile) {
   if(fallback) {
     if(document.getElementById(iid)) document.getElementById(iid).hidden = true; 
     Downloadify.create(pid,{
       swf: 'media/downloadify.swf',
       downloadImage: 'download.png',
       width: 100,
       height: 30,
       filename: ofile, data: function() { var o = sec_reportes_ejecutar_reporte_saldos(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";	
}
function sec_reportes_export_excel_table_saldos(){
	$(".btn_export_saldos_xlsx").off().on("click",function(){
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
		auditoria_send({"proceso":"validar_usuario_permiso_botones reporte saldos","data":data});
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
					sec_reportes_ejecutar_saldos('xlsx');
					sec_reportes_get_table_to_export_saldos('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_saldos.xlsx');			
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
	$(".btn_export_saldos_xls").off().on("click",function(){
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
					sec_reportes_ejecutar_saldos('biff2', 'reporte_saldos.xls');
					sec_reportes_get_table_to_export_saldos('biff2btn', 'xportbiff2', 'biff2', 'reporte_saldos.xls');				
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