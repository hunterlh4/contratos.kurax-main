var anio = false;
var mes = false;
var dia = false;
var fallback = false;
var all_periods="";
var d = new Date();
var time_report = d.getTime();
var array_meses_count=[];
var reportes_resultado_apuestas_inicio_fecha_localstorage = false;
var reportes_resultado_apuestas_fin_fecha_localstorage = false;
var cantidad_meses=0;
var anio_actual=0;
var $table_apt=false;
var nombre_mes = [];
function sec_reportes_resultados_apuestas(){
	console.log("sec_reporte_resultado_apuestas");
	sec_reporte_resultados_apuestas_get_canales_venta();
	sec_reporte_resultados_apuestas_get_locales();
	sec_reportes_resultados_apuestas_settings();
	sec_reportes_resultados_apuestas_events();
	// sec_reportes_get_reportes();
	sec_reportes_resultados_apuestas_get_redes();
	sec_reportes_resultados_apuestas_get_zonas();
}
function sec_reportes_resultados_apuestas_get_redes(){
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
					$(".red_resultado_apuestas").append(new_option);
				});
				$('.red_resultado_apuestas').select2({closeOnSelect: false});
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
function sec_reportes_resultados_apuestas_get_zonas(){
	var data = {};
		data.what={};
		data.what[0]="id";
		data.what[1]="nombre";
		data.where="zonas";
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
					$(".zona_resultado_apuestas").append(new_option);
				});
				$('.zona_resultado_apuestas').select2({closeOnSelect: false});
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
function sec_reporte_resultados_apuestas_get_canales_venta(){
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
			try{
				if ( console && console.log ) {
					$.each(data.data,function(index,val){
						canales_de_venta[val.id]=val.codigo;
						var new_option = $("<option>");
						$(new_option).val(val.id);
						$(new_option).html(val.codigo);
						$(".canal_venta_reporte_apuestas").append(new_option);

					});
					$('.canal_venta_reporte_apuestas').select2({closeOnSelect: false});
				}
		
			}catch(err){
	            swal({
	                title: 'Error en base de datos',
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
				console.log( "La solicitud canales de ventas a fallado en reporte resultado apuestas: " +  textStatus);
			}
		})
}
function sec_reporte_resultados_apuestas_get_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
		auditoria_send({"proceso":"sec_reporte_resultados_apuestas_get_locales","data":data});
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
						$(".local_reporte_apuestas").append(new_option);
					});
					$('.local_reporte_apuestas').select2({closeOnSelect: false});
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
				console.log( "La solicitud locales a fallado en reporte resultado apuestas: " +  textStatus);
			}
		})
}
function sec_reportes_resultados_apuestas_events(){
	nombre_mes = ["","Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre","Total"];	
	$(".btn_filtrar_reporte")
		.off("click")
		.on("click",function(e) {
			var btn = $(this).data("button");
			sec_reportes_apuestas_validacion_permisos_usuarios(btn);			
	});
	/*	
	$('.btn_export_resultado_apuestas_xlsx').off().on("click",function(){
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
					var reinit = $table_apt.floatThead('destroy');		
					sec_reportes_ejecutar_reporte_apuestas('xlsx');
				 	sec_reportes_get_table_to_export('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_apuestas.xlsx');
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
	});*/
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
			try{
				console.log(dataresponse);
				if (dataresponse.permisos==true) {
					var reinit = $table_apt.floatThead('destroy');			
					sec_reportes_ejecutar_reporte_apuestas('biff2', 'reporte_apuestas.xls');
				 	sec_reportes_get_table_to_export('biff2btn', 'xportbiff2', 'biff2', 'reporte_apuestas.xls');
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

	$('.tabla_reportes').off("click").on('click', '.clickable-row', function(event) {
		$(this).addClass('active').siblings().removeClass('active');
	});	


 
	// expand_collapse_columns($table_apt);
}
function sec_reportes_resultados_apuestas_settings(){
		$('.local_reporte_apuestas').select2({
			closeOnSelect: false,            
			allowClear: true,
		});
		$('.canalventarepuestaapuesta').select2({
			closeOnSelect: false,            
			allowClear: true,
		});	
		$('.red_resultado_apuestas').select2({
			closeOnSelect: false,            
			allowClear: true,
		});
		$('.zona_resultado_apuestas').select2({
			closeOnSelect: false,            
			allowClear: true,
		});

		$('.reportes_resultado_apuestas_datepicker')
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


		reportes_resultado_apuestas_inicio_fecha_localstorage = localStorage.getItem("reportes_resultado_apuestas_inicio_fecha_localstorage");
		if(reportes_resultado_apuestas_inicio_fecha_localstorage){
			var reportes_resultado_apuestas_inicio_fecha_localstorage_new = moment(reportes_resultado_apuestas_inicio_fecha_localstorage).format("DD-MM-YYYY");
			$("#input_text-reportes_resultado_apuestas_inicio_fecha")
				.datepicker("setDate", reportes_resultado_apuestas_inicio_fecha_localstorage_new)
				.trigger('change');
		}

		reportes_resultado_apuestas_fin_fecha_localstorage = localStorage.getItem("reportes_resultado_apuestas_fin_fecha_localstorage");
		if(reportes_resultado_apuestas_fin_fecha_localstorage){
			var reportes_resultado_apuestas_fin_fecha_localstorage_new = moment(reportes_resultado_apuestas_fin_fecha_localstorage).format("DD-MM-YYYY");
			$("#input_text-reportes_resultado_apuestas_fin_fecha")
				.datepicker("setDate", reportes_resultado_apuestas_fin_fecha_localstorage_new)
				.trigger('change');
		}
}
function sec_reportes_get_reportes(){
	loading(true);
	var get_reportes_data = {};
	get_reportes_data.where = "resultado_apuestas";
	get_reportes_data.filtro = {};
	get_reportes_data.filtro.fecha_inicio = $('.reportes_resultado_apuestas_inicio_fecha').val();
	get_reportes_data.filtro.fecha_fin = $('.reportes_resultado_apuestas_fin_fecha').val();
	get_reportes_data.filtro.locales = $('.local_reporte_apuestas').val();
	get_reportes_data.filtro.canales_de_venta = $('.canalventarepuestaapuesta').val();
	get_reportes_data.filtro.red_id=$('.red_resultado_apuestas').val();
	get_reportes_data.filtro.zona_id=$('.zona_resultado_apuestas').val();

	localStorage.setItem("reportes_resultado_apuestas_inicio_fecha_localstorage",get_reportes_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_resultado_apuestas_fin_fecha_localstorage",get_reportes_data.filtro.fecha_fin);

	auditoria_send({"proceso":"sec_reportes_get_reportes","data":get_reportes_data});
	$.ajax({
		url: '../sys/get_reporte_resultado_apuestas.php',
		type: 'POST',
		data:get_reportes_data,
	})
	.done(function(responsedata, textStatus, jqXHR) {
		$(".tabla_contenedor_reportes").html(responsedata);
		sec_reportes_expand_collapse_row();
		sec_reportes_apuestas_filter_search();

		$table_apt = $('.tabla_reportes');
		$table_apt.floatThead({
			top:50
		});	
		$('td').each(function() {
			var cellvalue = $(this).html();
			if ( cellvalue < 0) {
				$(this).wrapInner('<strong class="negative_number"></strong>');    
			}
		});			
		loading();
		$(".btn_export_resultado_apuestas").on("click",function(e){
			loading(true);
			$.ajax({
				url: '/export/reporte_apuestas.php',
				type: 'post',
				data: get_reportes_data,
			})
			.done(function(dataresponse) {
				var obj = JSON.parse(dataresponse);
				window.open(obj.path);
				loading();
			})
		})		
	})
	.fail(function() {
		console.log( "La solicitud reportes a fallado: " +  textStatus);
	})
}
function expand_collapse_columns($table){
	//collapse - expand table months
	$(".oculto").show();  
	$(".cabecera_mes").attr("colspan","14");
	$(".btn_hide_month").show();
	$(".btn_show_month").hide();
	var count_clicks_month_show=0;
	var colspan_final_show=0;
	$(".btn_show_month").on("click",function(){
		count_clicks_month_show++;
		colspan_final_show=(count_clicks_month_show*13)+(cantidad_meses-count_clicks_month_show);
		$("#cabeceraanio_"+anio_actual).attr("colspan",colspan_final_show); 
		var current_period = $(this).attr("id").split("_")[3];
		var $monthShow = $table.find("#btn_show_month_"+current_period);
		$("#btn_show_month_"+current_period).hide();
		$("#btn_hide_month_"+current_period).show();

		$(".cabeceras"+current_period).show();
		$("."+current_period).show(); 
		$monthShow[$monthShow.hasClass("hide") ? "removeClass" : "addClass"]("hide");
        $(this)[$(this).hasClass("show_thead") ? "removeClass" : "addClass"]("show_thead");
        $table.floatThead("reflow");
		$("#cabecera_"+current_period).attr("colspan",14); 
	});
	var count_clicks_month_hide=0;
	var colspan_final_hide=0;
	$(".btn_hide_month").on("click",function(){
		count_clicks_month_hide++;
		colspan_final_hide=(14*cantidad_meses)-(13*count_clicks_month_hide);
		$("#cabeceraanio_"+anio_actual).attr("colspan",colspan_final_hide);	
		var current_period = $(this).attr("id").split("_")[3];
		var $monthHide = $table.find("#btn_hide_month_"+current_period);
		$("#btn_hide_month_"+current_period).hide();
		$("#btn_show_month_"+current_period).show();
		$(".cabeceras"+current_period).hide();
		$("."+current_period).hide();
		$monthHide[$monthHide.hasClass("hide") ? "removeClass" : "addClass"]("hide");
        $(this)[$(this).hasClass("show_thead") ? "removeClass" : "addClass"]("show_thead");
        $table.floatThead("reflow");
		$("#cabecera_"+current_period).attr("colspan",1);
	});
	//collapse - expand table years 
	var $reportNameColumnYear = $table.find(".oculto");
	$(".btn_hide_year").show();
	$(".btn_show_year").hide();
	var array_all_periods=all_periods.slice(0, -1).split("_");
	$.each(array_all_periods, function(index_period, current_period) {
		$(".btn_show_year").on("click",function(){
			//loading(true);
			$(".btn_show_year").hide();
			$(".btn_hide_year").show();
			$("#btn_show_month_"+current_period).hide();
			$("#btn_hide_month_"+current_period).show();
			var current_year = $(this).attr("id");
			$(".cabeceras"+current_period).show();
			$("."+current_period).show();
			$reportNameColumnYear[$reportNameColumnYear.hasClass("hide") ? "removeClass" : "addClass"]("hide");
	        $(this)[$(this).hasClass("show_thead") ? "removeClass" : "addClass"]("show_thead");
	        $table.floatThead("reflow");
			$("#cabecera_"+current_period).attr("colspan",14);	
	        $("#cabeceraanio_"+current_year).attr("colspan",cantidad_meses*14);	
		});    
		$(".btn_hide_year").on("click",function(){
			$(".btn_hide_year").hide();
			$(".btn_show_year").show();
			$("#btn_hide_month_"+current_period).hide();
			$("#btn_show_month_"+current_period).show();            
			var current_year = $(this).attr("id"); 
			anio_actual= current_year;
			$(".cabeceras"+current_period).hide();
			$("."+current_period).hide(); 
			$reportNameColumnYear[$reportNameColumnYear.hasClass("hide") ? "removeClass" : "addClass"]("hide");
	        $(this)[$(this).hasClass("show_thead") ? "removeClass" : "addClass"]("show_thead");
	        $table.floatThead("reflow");
	        $("#cabecera_"+current_period).attr("colspan",1);
	        $("#cabeceraanio_"+current_year).attr("colspan",cantidad_meses);
		});
	});
}
function sec_reportes_expand_collapse_row(){
    $(".all_parent").off().on("click",function(){
        if ($(".children").hasClass("rows_expanded")){
            $(".children").hide();
            $(".children").removeClass('rows_expanded').addClass('rows_hidden');
            $(this).find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
            $(".btn_collapse_expand_row_reporte_apuestas").find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
        }else{
            $(".children").show();
            $(".children").removeClass('rows_hidden').addClass('rows_expanded');
            $(this).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
            $(".btn_collapse_expand_row_reporte_apuestas").find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
        }
    })
    $(".parent").off().on("click",function(){
        var id_row_children = $(this).attr("id");
        if($(".children_row_collapse_expand_"+id_row_children).hasClass("rows_hidden"))
        {
        	$(".children_row_collapse_expand_"+id_row_children).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
            $(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_hidden').addClass('rows_expanded');
            $(".parent_row_collapse_expand_"+id_row_children).find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
            $(".all_parent").find("span").removeClass('glyphicon-plus').addClass('glyphicon-minus');
        }else{
            $(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_expanded').addClass('rows_hidden'); 
            $(".parent_row_collapse_expand_"+id_row_children).find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');
            $(".all_parent").find("span").removeClass('glyphicon-minus').addClass('glyphicon-plus');                           
        }
    })	

}
function sec_reporte_apuestas_leer_meses(periodo){
	var mes = periodo.split("_");
	return mes[0]+" "+nombre_mes[parseInt(mes[1])];
}

function sec_reportes_ejecutar_reporte_apuestas(type, fn) { 
   return sec_reportes_export_table_to_excel('reporte_apuestas', type || 'xlsx', fn); 
}  
function sec_reportes_validar_exportacion_apuestas(s) {
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
     var fname = fn || $(".export_filename_resultado_apuestas").val()+"."+ type;
     try {
       saveAs(new Blob([sec_reportes_validar_exportacion_apuestas(wbout)],{type:"application/octet-stream"}), fname);
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
       filename: ofile, data: function() { var o = sec_reportes_ejecutar_reporte_apuestas(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";
}

// function sec_reportes_busqueda_tabla_reporte_apuestas(){



// 		$('#icon_clean_input_search_canal_venta').click(function() {
// 			sec_reportes_resetear_busqueda_canal_venta();
// 		});
// 		$('#icon_clean_input_search_local').click(function() {
// 			sec_reportes_resetear_busqueda_local();
// 		});       
// 		$('#icon_clean_input_search_tipo').click(function() {
// 			sec_reportes_resetear_busqueda_tipo();
// 		});
// 		$('#icon_clean_input_search_asesor').click(function() {
// 			sec_reportes_resetear_busqueda_asesor();
// 		});		
// 		$('#icon_clean_input_search_tipo_admin').click(function() {
// 			sec_reportes_resetear_busqueda_tipo_admin();
// 		});
// 		$('#icon_clean_input_search_tipo_punto').click(function() {
// 			sec_reportes_resetear_busqueda_tipo_punto();
// 		});
// 		$('#icon_clean_input_search_qty').click(function() {
// 			sec_reportes_resetear_busqueda_qty();
// 		});


// 		$('#buscar_canal_venta').keyup(function(event) {
// 			if (event.keyCode == 27) {
// 				sec_reportes_resetear_busqueda_canal_venta();
// 			}
// 		});
// 		$('#buscar_local').keyup(function(event) {
// 			if (event.keyCode == 27) {
// 				sec_reportes_resetear_busqueda_local();
// 			}
// 		}); 
// 		$('#buscar_tipo').keyup(function(event) {
// 			if (event.keyCode == 27) {
// 				sec_reportes_resetear_busqueda_tipo();
// 			}
// 		});
// 		$('#buscar_asesor').keyup(function(event) {
// 			if (event.keyCode == 27) {
// 				sec_reportes_resetear_busqueda_asesor();
// 			}
// 		});		
// 		$('#buscar_tipo_admin').keyup(function(event) {
// 			if (event.keyCode == 27) {
// 				sec_reportes_resetear_busqueda_tipo_admin();
// 			}
// 		});
// 		$('#buscar_tipo_punto').keyup(function(event) {
// 			if (event.keyCode == 27) {
// 				sec_reportes_resetear_busqueda_tipo_punto();
// 			}
// 		});
// 		$('#buscar_qty').keyup(function(event) {
// 			if (event.keyCode == 27) {
// 				sec_reportes_resetear_busqueda_qty();
// 			}
// 		});		
// }

function sec_reportes_apuestas_filter_search(){
	$('#nombre_canal_de_venta_reporte').hide();
	$('#nombre_local_reporte').hide();       
	$('#col_local_propiedad').hide(); 
	$('#col_local_asesor_nombre').hide(); 		
	$('#col_local_administracion').hide(); 
	$('#col_local_tipo_de_punto').hide();		
	$('#col_qty').hide();

	$('.filter_reporte_apuestas').off().on('keyup', function() {
		var rex = new RegExp($(this).val(), 'i');
		var filter = $(this).data("filter-name");
		// $("#"+filter).show();
		$('.tbody_table_reporte_apuestas tr').hide();
		$('.tbody_table_reporte_apuestas tr').filter(function() {
			return rex.test($(this).find('td.' + filter).text());
		}).show();
	});

	$(".icon_filter_reporte_apuestas").off().on("click",function(){
		var icon_id = $(this).attr("id");
		$("#"+icon_id).hide();
	})			
}
function sec_reportes_apuestas_validacion_permisos_usuarios(btn){
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			console.log(event.event_data);
			loading(true);
			sec_reportes_get_reportes();
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