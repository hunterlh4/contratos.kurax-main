var reportes_resumen_dia_inicio_fecha_localstorage = false;
var reportes_resumen_dia_fin_fecha_localstorage = false;
var $table_rd = false;
var array_local_nombres=Array();
function sec_reportes_depositos(){
	console.log("sec_reportes_depositos");
	sec_reportes_depositos_settings();
	sec_reportes_depositos_events();
	sec_reportes_depositos_bancos();
	sec_reportes_depositos_get_data();
	sec_reportes_depositos_get_canales_venta();
	sec_reportes_depositos_locales();	
}
function sec_reportes_depositos_get_canales_venta(){
		var data = {};
		data.what={};
		data.what[0]="id";
		data.what[1]="codigo";
		data.where="canales_de_venta";
		data.filtro={}
		auditoria_send({"proceso":"sec_reportes_depositos_get_canales_venta","data":data});
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
					$(".canalventareporte_depositos").append(new_option);

				});
				$('.canalventareporte_depositos').select2({closeOnSelect: false});
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud canales de ventas en depositos a fallado: " +  textStatus);
			}
		})	
}
function sec_reportes_depositos_locales(){
		var data = {};
		data.what={};
		data.what[0]="id";
		data.what[1]="nombre";
		data.where="locales";
		data.filtro={}
		auditoria_send({"proceso":"sec_reportes_depositos_locales","data":data});
		var local_call = $.ajax({
			data: data,
			type: "POST",
			dataType: "json",
			url: "/api/?json",
		})
		$.when(local_call).done(function( data, textStatus, jqXHR ) {
			if ( console && console.log ) {
				$.each(data.data,function(index,val){
					array_local_nombres[val.id]= val.nombre;
					var new_option = $("<option>");
					$(new_option).val(val.id);
					$(new_option).html(val.nombre);
					$(".localreporte_depositos").append(new_option);
				});
				$('.localreporte_depositos').select2({closeOnSelect: false});
			}
			sec_reportes_depositos_get_data();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud locales en depositos a fallado: " +  textStatus);
			}
		})
}
function sec_reportes_depositos_bancos(){
		var data = {};
		data.what={};
		data.what[0]="id";
		data.what[1]="nombre";
		data.where="bancos";
		data.filtro={}
		auditoria_send({"proceso":"sec_reportes_depositos_bancos","data":data});
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
					$(".bancos_reporte_depositos").append(new_option);

				});
				$('.bancos_reporte_depositos').select2({closeOnSelect: false});
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud bancos en depositos a fallado: " +  textStatus);
			}
		})
}
function sec_reportes_depositos_events(){
	$(".btn_filtrar_reporte_depositos").off().on("click",function(){
		var btn = $(this).data("button");
		sec_reportes_depositos_validacion_permisos_usuarios(btn);
	})
	sec_reportes_depositos_export_excel_table();
    $table_rd = $('#tabla_reportes_depositos');
    $table_rd.floatThead({
    	top:50
    });	
	$('td').each(function() {
		var cellvalue = $(this).html();
		if ( cellvalue < 0) {
			$(this).wrapInner('<strong class="negative_number_depositos"></strong>');    
		}
	});    
    sec_reportes_depositos_expand_collapse_rows();	
}
function sec_reportes_depositos_settings(){
	$('.bancos_reporte_depositos').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.localreporte_depositos').select2({
		closeOnSelect: false,            
		allowClear: true,
	});
	$('.canalventareporte_depositos').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.red_reporte_depositos').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.reportes_depositos_datepicker')
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

	reportes_depositos_inicio_fecha_localstorage = localStorage.getItem("reportes_depositos_inicio_fecha_localstorage");
	if(reportes_depositos_inicio_fecha_localstorage){
		var reportes_depositos_inicio_fecha_localstorage_new = moment(reportes_depositos_inicio_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_depositos_inicio_fecha")
			.datepicker("setDate", reportes_depositos_inicio_fecha_localstorage_new)
			.trigger('change');
	}

	reportes_depositos_fin_fecha_localstorage = localStorage.getItem("reportes_depositos_fin_fecha_localstorage");
	if(reportes_depositos_fin_fecha_localstorage){
		var reportes_depositos_fin_fecha_localstorage_new = moment(reportes_depositos_fin_fecha_localstorage).format("DD-MM-YYYY");
		$("#input_text-reportes_depositos_fin_fecha")
			.datepicker("setDate", reportes_depositos_fin_fecha_localstorage_new)
			.trigger('change');
	}	
}
function sec_reportes_depositos_get_data(){
	var get_depositos_data = {};
	get_depositos_data.where = "depositos";
	get_depositos_data.filtro = {};
	get_depositos_data.filtro.fecha_inicio = $('.reportes_depositos_inicio_fecha').val();
	get_depositos_data.filtro.fecha_fin = $('.reportes_depositos_fin_fecha').val();
	get_depositos_data.filtro.bancos = $('.bancos_reporte_depositos').val();
	get_depositos_data.filtro.locales = $('.localreporte_depositos').val();
	get_depositos_data.filtro.canales_de_venta = $('.canalventareporte_depositos').val();
	get_depositos_data.filtro.red_id=$('.red_reporte_depositos').val();
	localStorage.setItem("reportes_depositos_inicio_fecha_localstorage",get_depositos_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_depositos_fin_fecha_localstorage",get_depositos_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_reportes_depositos_get_data","data":get_depositos_data});
    console.log(get_depositos_data);
    $.ajax({
		url: "/api/?json",
        type: 'POST',
        data:get_depositos_data
    })
    .done(function(dataresponse) {
        var obj = JSON.parse(dataresponse);

        console.log(obj);
        sec_reportes_depositos_create_table(obj);
    })
    .fail(function() {
        console.log("error");
    })	
}
function sec_reportes_depositos_create_table(obj){
	var nombre_mes = ["","Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"]; 	
	var cols = {};

	cols["fecha_de_registro"] = "Fecha de registro";
	cols["fecha_de_deposito"] = "Fecha de depósito";
	cols["deposito"] = "Banco";
	cols["numero_de_cuenta"] = "Número de cuenta";
	cols["N_de_transaccion_del_deposito"] = "No de transacción";
	cols["monto_del_deposito"] = "Monto";
	cols["tipo_de_transaccion"] = "Tipo";
	cols["color"] = "Color";			
	var cdv = Object();
	cdv[15]="Web";
	cdv[16]="PBET";
	cdv[17]="SBT-Negocios";
	cdv[18]="JV Global Bet";
	cdv[19]="Tablet BC";
	cdv[20]="SBT-BC";
	cdv[21]="JV Golden Race";

	var html ="<table class='tabla_reportes_depositos' id='tabla_reportes_depositos' width='100%' cellspacing='0'>";
	html +='<thead style="background-color: #C0C0C0 !important; border-right:1px solid #fafafa !important; color: #333 !important; ">';
	html +='<tr>';	

	$.each(cols, function(index_cols, val_cols) {
		html +='<th id="th_'+index_cols+'" class="'+index_cols+'">'+val_cols+'</th>';
	});
	html +='<tr>';
	html +='</thead><tbody>';
	$.each(obj.data, function(index_deposito, val_deposito) {
		if (val_deposito.abono==null) {
			val_deposito.abono = 0;
		};
		html += '<tr class="">';	

		html += '<td class="sec_reportes_depositos_">'+val_deposito.fecha_ingreso+'</td>';
		html += '<td class="sec_reportes_depositos_">'+val_deposito.fecha_operacion+'</td>';
		html += '<td class="sec_reportes_depositos_">'+val_deposito.banco+'</td>';
		html += '<td class="sec_reportes_depositos_numero_movimiento"></td>';
		html += '<td class="sec_reportes_depositos_">'+val_deposito.numero_movimiento+'</td>';
		html += '<td class="sec_reportes_depositos_importe">'+val_deposito.importe+'</td>';
		html += '<td class="sec_reportes_depositos_"></td>';
		html += '<td class="sec_reportes_depositos_color" style="background-color:#'+val_deposito.banco_color_hex+';"></td>';										
		html += '</tr>';		   					
	});
	html += '<tr class="total_reporte_depositos ">';

	html += '<td>TOTAL</td>';
	html += '<td></td>';
	html += '<td></td>';
	html += '<td></td>';
	html += '<td></td>';
	html += '<td class="sec_reportes_depositos_importe">'+obj.total_deposito+'</td>';
	html += '<td></td>';
	html += '<td></td>';																																									
	html += '</tr>';
	html += '</tbody><tfoot><tr>';
	html += '</tr></tfoot>';
	html+='</table>';	   			
	$(".tabla_contenedor_reportes_depositos").html(html);
	sec_reportes_depositos_events();
	loading();	
}
function sec_reportes_depositos_expand_collapse_rows(){
	$(".all_parent").off().on("click",function(){
		if ($(".children").hasClass("rows_expanded_depositos") ) {
			$(".children").hide();
			$(".children").removeClass('rows_expanded_depositos').addClass('rows_hidden_depositos');
		}else{
			$(".children").show();
			$(".children").removeClass('rows_hidden_depositos').addClass('rows_expanded_depositos');
		}
	})
	$(".parent_dia").off().on("click",function(){
		var id_row_children = $(this).data("id-dia");
		if($(".children_row_collapse_depositos_"+id_row_children).hasClass("rows_hidden_depositos"))
		{
			$(".children_row_collapse_depositos_"+id_row_children).toggle().removeClass('rows_hidden_depositos').addClass('rows_expanded_depositos');
		}else{
			$(".children_row_collapse_depositos_"+id_row_children).toggle().removeClass('rows_expanded_depositos').addClass('rows_hidden_depositos');                
		}
	})		
}
function sec_reportes_depositos_validacion_permisos_usuarios(btn){
	console.log("btn_filtrar_depositos:click");
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			loading(true);
			sec_reportes_depositos_get_data();
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
function sec_reportes_ejecutar_depositos(type, fn) {
   	return sec_reportes_export_table_to_excel_depositos('tabla_reportes_depositos', type || 'xlsx', fn);  
}  
function sec_reportes_validar_exportacion_depositos(s) {
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
function sec_reportes_export_table_to_excel_depositos(id, type, fn) {
     var wb = XLSX.utils.table_to_book(document.getElementById(id), {sheet:"Sheet JS"});
     var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
     var fname = fn || 'tabla_reportes_depositos.' + type;
     try {
       saveAs(new Blob([sec_reportes_validar_exportacion_depositos(wbout)],{type:"application/octet-stream"}), fname);
     } catch(e) { if(typeof console != 'undefined') console.log(e, wbout); }
     return wbout;	
}
function sec_reportes_get_table_to_export_depositos(pid, iid, fmt, ofile) {
   if(fallback) {
     if(document.getElementById(iid)) document.getElementById(iid).hidden = true; 
     Downloadify.create(pid,{
       swf: 'media/downloadify.swf',
       downloadImage: 'download.png',
       width: 100,
       height: 30,
       filename: ofile, data: function() { var o = sec_reportes_ejecutar_reporte_depositos(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";	
}
function sec_reportes_depositos_export_excel_table(){
	$(".btn_export_depositos_xlsx").off().on("click",function(){
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
		auditoria_send({"proceso":"validar_usuario_permiso_botones reporte depositos","data":data});
		$.ajax({
			data: data,
			type: "POST",
			dataType: "json",
			url: "/api/?json"
		})
		.done(function( dataresponse) {
			//console.log(dataresponse);
			if (dataresponse.permisos==true) {
				var reinit = $table_rd.floatThead('destroy');		
				sec_reportes_ejecutar_depositos('xlsx');
				sec_reportes_get_table_to_export_depositos('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_depositos.xlsx');			
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
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud validar permisos exportar xlsx a fallado: " +  textStatus);
			}
		})
	})
	$(".btn_export_depositos_xls").off().on("click",function(){
		event.preventDefault();
		var buton = $(this);
		var data = Object();
		data.filtro = Object();	
		data.where="validar_usuario_permiso_botones";			
		$(".input_text_validacion").each(function(index, el) {
			data.filtro[$(el).attr("data-col")]=$(el).val();
		});	
		data.filtro.text_btn = buton.data("button");
		//console.log(data);
		auditoria_send({"proceso":"validar_usuario_permiso_botones","data":data});
		$.ajax({
			data: data,
			type: "POST",
			dataType: "json",
			url: "/api/?json"
		})
		.done(function( dataresponse) {
			//console.log(dataresponse);
			if (dataresponse.permisos==true) {
				var reinit = $table_rd.floatThead('destroy');
				sec_reportes_ejecutar_depositos('biff2', 'reporte_depositos.xls');
				sec_reportes_get_table_to_export_depositos('biff2btn', 'xportbiff2', 'biff2', 'reporte_depositos.xls');				
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
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud validar permisos exportar xls a fallado: " +  textStatus);
			}
		})
	})	
}
