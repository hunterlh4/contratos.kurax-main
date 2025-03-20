var modelo = false;
var table_cct = false;
var table_dt = false;
var get_bet_bar_liquidaciones_data = {};
var reportes_betbar_inicio_fecha_localstorage = false;
var reportes_betbar_fin_fecha_localstorage = false;
function sec_reportes_bet_bar(){
	console.log("sec_reportes_bet_bar");
	sec_reporte_bet_bar_settings();
	sec_reporte_bet_bar_events();
	sec_reporte_bet_bar_get_canales_venta();
	sec_reporte_bet_bar_get_locales();
	
	modelo = $(".sec_reporte_bet_bar_tipo_de_modelo").val();

	if (modelo==2) {
		var limit_date_1 = $('.reportes_betbar_inicio_fecha').val();
		var limit_date_2 = $('.reportes_betbar_fin_fecha').val();

		var start = new Date(limit_date_1);
		var end = new Date(limit_date_2);
		var diff  = new Date(end - start);
		var dias = diff/1000/60/60/24;

		if(dias > 31){
			sweetAlert("Oops...", "seleccione menos de 31 días!", "error");
		}else{
			loading(true);	
			sec_reportes_bet_bar_caja_sistema_get_reporte();				
		}		
	}
	if (modelo==3) {
		sec_reportes_bet_bar_apuestas_get_reportes();
	}	
}
function sec_reporte_bet_bar_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
		auditoria_send({"proceso":"sec_reporte_bet_bar_get_canales_venta","data":data});
		var canal_de_venta_call = $.ajax({
			data: data,
			type: "POST",
			dataType: "json",
			url: "/api/?json",
			async: "false"
		})
		$.when(canal_de_venta_call).done(function( data, textStatus, jqXHR ) {
			try{
				if ( console && console.log ) {
					$.each(data.data,function(index,val){
						canales_de_venta[val.id]=val.codigo;
						var new_option = $("<option>");
						$(new_option).val(val.id);
						$(new_option).html(val.codigo);
						$(".canalventareportebetbar").append(new_option);

					});
					$('.canalventareportebetbar').select2({closeOnSelect: false});
				}
				if (modelo==1) {
					sec_reportes_bet_bar_get_liquidaciones();
				}
			}catch(err){
	            swal({
	                title: 'Error en la base de datos',
	                type: "info",
	                timer: 2000,
	            }, function(){
	                swal.close();
	                loading();
	            }); 
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud canales de ventas a fallado en reporte betbar: " +  textStatus);
			}
		})
}
function sec_reporte_bet_bar_get_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
	data.filtro.red_id = {};
	data.filtro.red_id[0] = 1;
		auditoria_send({"proceso":"sec_reporte_bet_bar_get_locales","data":data});
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
						$(".localreportebetbar").append(new_option);
					});
					$('.localreportebetbar').select2({closeOnSelect: false});
				}
			}catch(err){
	            swal({
	                title: 'Error en la base de datos',
	                type: "info",
	                timer: 2000,
	            }, function(){
	                swal.close();
	                loading();
	            }); 
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud locales a fallado en reporte betbar: " +  textStatus);
			}
		})
}
function sec_reporte_bet_bar_settings(){
	$('.localreportebetbar').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.canalventareportebetbar').select2({
		closeOnSelect: false,            
		allowClear: true,
	});		
	$('.red_reporte_betbar').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.reportes_betbar_datepicker')
		.datepicker({
			dateFormat:'dd-mm-yy'
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
		});


		reportes_betbar_inicio_fecha_localstorage = localStorage.getItem("reportes_betbar_inicio_fecha_localstorage");
		if(reportes_betbar_inicio_fecha_localstorage){
			var reportes_betbar_inicio_fecha_localstorage_new = moment(reportes_betbar_inicio_fecha_localstorage).format("DD-MM-YYYY");
			$("#input_text-reportes_betbar_inicio_fecha")
				.datepicker("setDate", reportes_betbar_inicio_fecha_localstorage_new)
				.trigger('change');
		}

		reportes_betbar_fin_fecha_localstorage = localStorage.getItem("reportes_betbar_fin_fecha_localstorage");
		if(reportes_betbar_fin_fecha_localstorage){
			var reportes_betbar_fin_fecha_localstorage_new = moment(reportes_betbar_fin_fecha_localstorage).format("DD-MM-YYYY");
			$("#input_text-reportes_betbar_fin_fecha")
				.datepicker("setDate", reportes_betbar_fin_fecha_localstorage_new)
				.trigger('change');
		}
}
function sec_reporte_bet_bar_events(){
	$(".btn_filtrar_reporte_betbar").off().on("click",function(){
		loading(true);
		var btn = $(this).data("button");
			if (modelo==1) {
				sec_reportes_bet_bar_recaudacion_validacion_permisos_usuarios(btn);
			}
			if (modelo==2) {
				sec_reportes_bet_bar_caja_sistema_validacion_permisos_usuarios(btn);
			}
			if (modelo==3) {
				sec_reportes_bet_bar_apuestas_validacion_permisos_usuarios(btn);
				
			}
	})
	sec_reporte_bet_bar_events_caja_sistema();
	sec_reportes_bet_bar_resultados_apuestas_events();

	$('#tabla_sec_recaudacion').on( 'click', 'tbody td', function () {
			//$(this).css("background-color","#ffff99");
		    var idx_row = table_dt.cell(this).index().row;
		    var data = table_dt.cells(idx_row,'').render('display');
		    var idx_column = table_dt.cell(this).index().column;
		    var title = table_dt.column(idx_column).header();
			var th = $('#tabla_sec_recaudacion th').eq($(this).index());
			var textString = th.text().toLowerCase();
		 	if ($(title).html().split('"')[3]=="total_cliente" && data[3]!="Total") {
		 		var fecha_inicio = $("#reportes_betbar_inicio_fecha").val();
		 		var fecha_fin = $("#reportes_betbar_fin_fecha").val();
		 		var canal_de_venta_id = data[24];
		 		var local_id = data[0];
		 		var columna = $(title).html().split('"')[3];
		 		//sec_reportes_bet_bar_tickets_comision_cuota_bet_bar(fecha_inicio,fecha_fin,canal_de_venta_id,local_id,columna);
		 	};
	});
}
function sec_reporte_bet_bar_events_caja_sistema(){
	$(".btn_export_xlsx").off().on("click",function(){
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
					sec_reportes_bet_bar_ejecutar_reporte_caja_sistema('xlsx');
					sec_reportes_bet_bar_get_table_to_export_caja_sistema('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_caja_sistema.xlsx');			
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
					var reinit = $table_cs.floatThead('destroy');
					sec_reportes_bet_bar_ejecutar_reporte_caja_sistema('biff2', 'reporte_caja_sistema.xls');
					sec_reportes_bet_bar_get_table_to_export_caja_sistema('biff2btn', 'xportbiff2', 'biff2', 'reporte_caja_sistema.xls');				
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
    $table_cs = $('#tabla_caja_sistema');
    $table_cs.floatThead({
        top:44,
        responsiveContainer: function($table_cs){
            return $table_cs.closest('.table-responsive');
        }       
    });
	$(".all_parent_caja_sistema").off().on("click",function(){
		if ($(".children_caja_sistema").hasClass("rows_expanded_caja_sistema") ) {
			$(".children_caja_sistema").hide();
			$(".children_caja_sistema").removeClass('rows_expanded_caja_sistema').addClass('rows_hidden_caja_sistema');
		}else{
			$(".children_caja_sistema").show();
			$(".children_caja_sistema").removeClass('rows_hidden_caja_sistema').addClass('rows_expanded_caja_sistema');
		}
	})
	$(".parent_caja_sistema").off().on("click",function(){
		var id_row_children = $(this).attr("id");
		if($(".children_row_collapse_expand_"+id_row_children).hasClass("rows_hidden_caja_sistema"))
		{
			$(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_hidden_caja_sistema').addClass('rows_expanded_caja_sistema');
		}else{
			$(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_expanded_caja_sistema').addClass('rows_hidden_caja_sistema');                
		}
	})	
}
function sec_reportes_bet_bar_resultados_apuestas_events(){
	$('.btn_export_xlsx').off().on("click",function(){
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
					sec_reportes_bet_bar_ejecutar_reporte_apuestas('xlsx');
				 	sec_reportes_bet_bar_get_table_to_export_apuestas('xlsxbtn',  'xportxlsx',  'xlsx',  'reporte_apuestas.xlsx');
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
	                loading();
	            }); 
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud validar permisos exportar xlsx a fallado: " +  textStatus);
			}
		})
	});
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
					sec_reportes_bet_bar_ejecutar_reporte_apuestas('biff2', 'reporte_apuestas.xls');
				 	sec_reportes_bet_bar_get_table_to_export_apuestas('biff2btn', 'xportbiff2', 'biff2', 'reporte_apuestas.xls');
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
	$('td').each(function() {
		var cellvalue = $(this).html();
		if ( cellvalue < 0) {
			$(this).wrapInner('<strong class="negative_number"></strong>');    
		}
	});

	$('.tabla_reportes').off("click").on('click', '.clickable-row', function(event) {
		$(this).addClass('active').siblings().removeClass('active');
	});	


    $table_apt = $('.tabla_reportes');
    $table_apt.floatThead({
    	top:50
    });
	sec_reportes_bet_bar_expand_collapse_row_apuestas(); 
	sec_reportes_bet_bar_expand_collapse_columns_apuestas($table_apt);
	
	sec_reportes_bet_bar_busqueda_tabla_reporte_apuestas();		
}
function sec_reportes_bet_bar_expand_collapse_columns_apuestas($table){
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
function sec_reportes_bet_bar_expand_collapse_row_apuestas(){
    $(".all_parent").off().on("click",function(){
        if ($(".children").hasClass("rows_expanded") ) {
            $(".children").hide();
            $(".children").removeClass('rows_expanded').addClass('rows_hidden');
        }else{
            $(".children").show();
            $(".children").removeClass('rows_hidden').addClass('rows_expanded');
        }
    })
    $(".parent").off().on("click",function(){
        var id_row_children = $(this).attr("id");
        if($(".children_row_collapse_expand_"+id_row_children).hasClass("rows_hidden"))
        {
            $(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_hidden').addClass('rows_expanded');
        }else{
            $(".children_row_collapse_expand_"+id_row_children).toggle().removeClass('rows_expanded').addClass('rows_hidden');                
        }
    })	
}
function sec_reportes_bet_bar_recaudacion_mostrar_datatable(model){
	var heightdoc = window.innerHeight;
	var heightnavbar= $(".navbar-header").height();
	var heighttable =heightdoc-heightnavbar-300;
	if(modelo==1){ 
		$.fn.dataTable.ext.errMode = 'none';
	    table_dt = $('#tabla_sec_recaudacion').DataTable({ 
		  /*
		  scrollY:heighttable,
		  scrollX:true,
		  scrollCollapse:true,	    	
		  fixedColumns:   {
			  leftColumns: 4
		  },
		  */
	      bRetrieve: true,
	      lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],
		  paging: true,	      
	      searching: true,
	      sPaginationType: "full_numbers",
	      Sorting: [[1, 'asc']], 
	      rowsGroup: [0,1,2],
	      bSort: false,
	      data:model,
	      dom: 'Blrftip',
          buttons: [
                { 
                    extend: 'copy',
                    text:'Copiar',
                    footer: true,
                    className: 'copiarButton'
                 },
                { 
                    extend: 'csv',
                    text:'CSV',
                    footer: true,
                    className: 'csvButton' 
                    ,filename: $(".export_filename").val()
                },
                {   extend: 'excelHtml5',
                    text:'Excel',
                    footer: true,
                    className: 'excelButton'
                    ,filename: $(".export_filename").val()
		            ,customize: function(xlsx) {
		                var sheet = xlsx.xl.worksheets['sheet1.xml'];
		                $('row:first c', sheet).attr( 's', '22' );
		                $('row c', sheet).each( function () {
		                    if ( $('is t', this).text() == 'Total' ) {
		                        $(this).attr( 's', '20' );
		                    }
		                    
		                });
						
		            }                    
                }, 
                {
                    extend: 'colvis',
                    text:'Visibilidad',
                    className:'visibilidadButton',
                    postfixButtons: [ 'colvisRestore' ]
                }
          ],
	      fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
		        if ( aData[3] == "Total" )
		        {
		            $('td', nRow).css('background-color','#9BDFFD','important');
		            $('td', nRow).css('color','#080FFC'); 
		            $('td', nRow).css('font-weight','800');                       
		        }

	      },
		  createdRow: function ( row, data, index ) {
		    if (data[3]=="Total" && data[23]!=0) {
		    	$('td', row).eq(23).addClass('cashdesk_balance');
		    };  
		  },
		  footerCallback: function () {
			var api = this.api(),
			columns = [4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24]; 
					for (var i = 0; i < columns.length; i++) {
						var total =  api.column(columns[i], {filter: 'applied'}).data().sum().toFixed(2);
						var total_pagina = api.column(columns[i], { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
							if (total<0 && total_pagina<0){
								$('tfoot th').eq(columns[i]).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total/2) +'<span><br>');
							}
							else{
								$('tfoot th').eq(columns[i]).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total/2) +'<span><br>');
							}
					}
		  }, 
          columnDefs: [
		        { className: "colunmasControl columna_local_id_modelo_cuatro","targets": [0] },          
		        { className: "colunmasControl columna_nombre_local_modelo_cuatro","targets": [1] },
		        { className: "columnaprincipal columna_dias_modelo_cuatro_body_td", "targets": [2] }, 
		        { className: "canal_de_venta_modelo_cuatro", "targets": [3] }, 
	        	{ className: "apostado_modelo_cuatro", "targets": [4] },
		        { className: "ganado_modelo_cuatro", "targets": [5] },
		        { className: "pagado_modelo_cuatro", "targets": [6] },
		        { className: "produccion_modelo_cuatro", "targets": [7] },        
		        { className: "columnasnumeros_body_td","targets": [4,5,6,7,8,9,10,11,12,13,15,16,17,18,19,20,21,22,23]},
		        { className: "total_cliente_bet_bar", "targets": [14] },
		        { sortable: false,"class": "index",targets: [0]},
		        { sortable: true, "targets": [0] },
		        { type: 'num-fmt', "targets": 13 },
		        {
		          aTargets: [4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],
			          fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
			             if ( sData < "0" ) {
	                          $(nTd).css('color', 'red')
	                          $(nTd).css('font-weight', 'bold')
			            }
		          }
		        },
		        { "visible": false, "targets": 24 }	                
	      ], 
	      pageLength: '23',
	      language:{
	            "decimal":        ".",
	            "thousands":      ",",            
	            "emptyTable":     "Tabla vacia",
	            "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
	            "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
	            "infoFiltered":   "(filtered from _MAX_ total entradas)",
	            "infoPostFix":    "",
	            "thousands":      ",",
	            "lengthMenu":     "Mostrar _MENU_ entradas",
	            "loadingRecords": "Cargando...",
	            "processing":     "Procesando...",
	            "search":         "Filtrar:",
	            "zeroRecords":    "Sin resultados",
	            "paginate": {
	                "first":      "Primero",
	                "last":       "Ultimo",
	                "next":       "Siguiente",
	                "previous":   "Anterior"
	            },
	            "aria": {
	                "sortAscending":  ": activate to sort column ascending",
	                "sortDescending": ": activate to sort column descending"
	            },
	            "buttons": {
	                "copyTitle": 'Contenido Copiado',
	                "copySuccess": {
	                    _: '%d filas copiadas',
	                    1: '1 fila copiada'
	                }
	            }		            
	        }
	  });
	  table_dt.clear().draw();
	  table_dt.rows.add(model).draw();
	  table_dt.columns.adjust().draw(); 
	  loading();		
	}
}
function sec__reportes_bet_bar_recaudacion_process_data(obj){
	if (modelo==1) {
		var datafinal=[];
		var i = 0;
		$.each(obj, function(index, val) {
			$.each(val.locales, function(index1, val1) {
				$.each(val1.liquidaciones, function(index2, val2) {
					$.each(val2, function(index3, val3) {
						var data_canal_de_venta_id = val3.canal_de_venta_id;
						var data_local_id = val3.local_id;
						var data_nombre_local=val1.local_nombre;
						var data_dias_procesados = val1.dias_procesados;
						var data_canales_de_venta = canales_de_venta[index3];
						var data_total_depositado = formatonumeros(val3.total_depositado);
						var data_total_anulado_retirado = formatonumeros(val3.total_anulado_retirado);
						var data_total_apostado = formatonumeros(val3.total_apostado);
						var data_total_ganado = formatonumeros(val3.total_ganado);
						var data_total_pagado = formatonumeros(val3.total_pagado);
						var data_total_produccion = formatonumeros(val3.total_produccion);
						var data_total_depositado_web = formatonumeros(val3.total_depositado_web);
						var data_total_retirado_web = formatonumeros(val3.total_retirado_web);
						var data_total_caja_web = formatonumeros(val3.total_caja_web);
						var data_porcentaje_cliente = val3.porcentaje_cliente;
						var data_total_cliente = formatonumeros(val3.total_cliente);
						var data_porcentaje_freegames = val3.porcentaje_freegames;
						var data_total_freegames = formatonumeros(val3.total_freegames);
						var data_pagado_en_otra_tienda = formatonumeros(val3.pagado_en_otra_tienda);
						var data_pagado_de_otra_tienda = formatonumeros(val3.pagado_de_otra_tienda);
						var data_total_pagos_fisicos = formatonumeros(val3.total_pagos_fisicos);
						var data_caja_fisico = formatonumeros(val3.caja_fisico);
						var data_cashdesk_balance = formatonumeros(val3.cashdesk_balance);
						var data_test_balance = formatonumeros(val3.test_balance);						
						var data_test_diff = formatonumeros(val3.test_diff);												
												
						var newObject =[data_local_id,data_nombre_local,data_dias_procesados,data_canales_de_venta,data_total_depositado,data_total_anulado_retirado,data_total_apostado,data_total_ganado,data_total_pagado,data_total_produccion,data_total_depositado_web,data_total_retirado_web,data_total_caja_web,data_porcentaje_cliente,data_total_cliente,data_porcentaje_freegames,data_total_freegames,data_pagado_en_otra_tienda,data_pagado_de_otra_tienda,data_total_pagos_fisicos,data_caja_fisico,data_cashdesk_balance,data_test_balance,data_test_diff,data_canal_de_venta_id];
						datafinal[i] =  newObject;
						i++;
					});
				});
			});
		});
		
		//console.log(obj);
		$.each(obj.data.totales, function(indgeneral, valgeneral) {
				var data_caja_fisico =formatonumeros(valgeneral.caja_fisico);
				var data_num_tickets =	formatonumeros(valgeneral.num_tickets);
				var data_pagado_de_otra_tienda =	formatonumeros(valgeneral.pagado_de_otra_tienda);
				//valgeneral.pagado_en_otra_tienda=valgeneral.pagado_en_otra_tienda;
				var data_pagado_en_otra_tienda =	formatonumeros(valgeneral.pagado_en_otra_tienda);
				var data_retirado_de_otras_tiendas=	formatonumeros(valgeneral.retirado_de_otras_tiendas);
				var data_total_anulado_retirado	=formatonumeros(valgeneral.total_anulado_retirado);
				var data_total_apostado	=formatonumeros(valgeneral.total_apostado);
				var data_total_caja_web	=formatonumeros(valgeneral.total_caja_web);
				var data_porcentaje_cliente = formatonumeros(valgeneral.porcentaje_clientes);
				var data_total_cliente	=formatonumeros(valgeneral.total_cliente);
				var data_total_depositado	=formatonumeros(valgeneral.total_depositado);
				var data_total_depositado_web	=formatonumeros(valgeneral.total_depositado_web);
				var data_porcentaje_freegames = formatonumeros(valgeneral.porcentaje_freegames);
				var data_total_freegames	=formatonumeros(valgeneral.total_freegames);
				var data_total_ganado	=formatonumeros(valgeneral.total_ganado);
				var data_total_ingresado	=formatonumeros(valgeneral.total_ingresado);
				var data_total_pagado	=formatonumeros(valgeneral.total_pagado);
				var data_total_pagos_fisicos	=formatonumeros(valgeneral.total_pagos_fisicos);
				var data_total_produccion	=formatonumeros(valgeneral.total_produccion);
				var data_total_retirado_web	=formatonumeros(valgeneral.total_retirado_web);
				var data_total_cashdesk_balance = formatonumeros(valgeneral.cashdesk_balance);
				var data_total_test_balance = 0;				
				var data_test_diff = formatonumeros(valgeneral.test_diff);	
				//$('tfoot').html("<tr><td class='colunmasControl columna_nombre_local_modelo_cuatro'><span style='color:#fff !important;'>TOTAL:</span></td><td class='colunmasControl columna_nombre_local_modelo_cuatro'><span></span><span style='visibility:hidden;'>#############################</span></td><td class='columnaprincipal columna_dias_modelo_cuatro_body_td' style='color: #337ab7 !important; background-color:#337ab7 !important; border-bottom:1px solid #ddd !important;'><span>####</span></td><td class='tdft canal_de_venta_modelo_cuatro_footer'><span><span style='visibility:hidden;'>#####################</span></span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Depositado:</span><br><span class='etotv'>"+data_total_depositado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. A. Retirado</span><br><span class='etotv'>"+data_total_anulado_retirado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T Apostado</span><br><span class='etotv'>"+data_total_apostado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Ganado</span><br><span class='etotv'>"+data_total_ganado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Pagado</span><br><span class='etotv'>"+data_total_pagado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Producción</span><br><span class='etotv'>"+data_total_produccion+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Dep. Web</span><br><span class='etotv'>"+data_total_depositado_web+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Ret. Web</span><br><span class='etotv'>"+data_total_retirado_web+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Caja Web</span><br><span class='etotv'>"+data_total_caja_web+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>P. Cliente</span><br><span class='etotv'>"+data_porcentaje_cliente+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Cliente</span><br><span class='etotv'>"+data_total_cliente+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>P. Freegames</span><br><span class='etotv'>"+data_porcentaje_freegames+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Freegames</span><br><span class='etotv'>"+data_total_freegames+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. P. en Otra Tienda</span><br><span class='etotv'>"+data_pagado_en_otra_tienda+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. P. de Otra Tienda</span><br><span class='etotv'>"+data_pagado_de_otra_tienda+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. P. Fisicos</span><br><span class='etotv'>"+data_total_pagos_fisicos+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Caja Fisico</span><br><span class='etotv'>"+data_caja_fisico+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Cash Balance</span><br><span class='etotv'>"+data_total_cashdesk_balance+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Test Balance</span><br><span class='etotv'>"+data_total_test_balance+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Test Diff</span><br><span class='etotv'>"+data_test_diff+"</span></td></tr>");								
		});
		
		sec_reportes_bet_bar_recaudacion_mostrar_datatable(datafinal);
	}
}
function sec_reportes_bet_bar_caja_sistema_create_table(obj){
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
    	var html = '<table id="tabla_caja_sistema" cellspacing="0">';
		    	html +='<thead>';
					html += "<tr>";
					    html += "<th class='cabecera_seleccionar_todos_row'><button type='button' class='btn_collapse_expand_row_caja_sistema all_parent_caja_sistema'><span class='glyphicon glyphicon-pushpin'></span></button></th>";
							$.each(cols, function(indice_columna, nombre_columna) {
								 html += "<th class='cabecera_web_total_"+indice_columna+"'>"+nombre_columna+"</th>";
							});
					html += "</tr>";
			   	html +='</thead><tbody>';
		    	$.each(info_canal_array, function(index_canal_totales, detalles) {

		    			html+='<tr class="'+detalles.index_cv_total+'_dia_caja_sistema rows_hidden_caja_sistema children_caja_sistema children_row_collapse_expand_'+detalles.local_id+'" id="'+detalles.local_id+'">';
							html+='<td><button class="btn_collapse_expand_row_caja_sistema parent_caja_sistema parent_row_collapse_expand_'+detalles.local_id+'" id="'+detalles.local_id+'"><span class="glyphicon glyphicon-pushpin"/></button></td>';
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
		    							html+='<td><button class="btn_collapse_expand_row_caja_sistema parent_caja_sistema parent_row_collapse_expand_'+detalles.local_id+'" id="'+detalles.local_id+'"><span class="glyphicon glyphicon-pushpin"/></button></td>';
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
								html+='<td><button class="btn_collapse_expand_row_caja_sistema parent_caja_sistema parent_row_collapse_expand_'+detalles.local_id+'" id="'+detalles.local_id+'"><span class="glyphicon glyphicon-pushpin"/></button></td>';
								if (detalles.local_id) {						
									html+='<td>'+array_nombre_local[detalles.local_id]+' - Total</td>';	
								}
								html+='<td class="alinear_letras"></td>';
								html+='<td class="alinear_letras"></td>';
								html+='<td class="alinear_letras"></td>';
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
							html+='<td><button type="button" class="btn_collapse_expand_row_caja_sistema all_parent_caja_sistema"><span class="glyphicon glyphicon-pushpin"></span></button></td>';		    		
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
		sec_reporte_bet_bar_events_caja_sistema();  
		loading();
}
function sec_reportes_bet_bar_get_liquidaciones(){
	loading(true);
	//modelo = $(".sec_reporte_bet_bar_tipo_de_modelo").val();
	get_bet_bar_liquidaciones_data.filtro={};
	get_bet_bar_liquidaciones_data.filtro.fecha_inicio=$(".reportes_betbar_inicio_fecha").val();
	get_bet_bar_liquidaciones_data.filtro.fecha_fin=$(".reportes_betbar_fin_fecha").val();
	get_bet_bar_liquidaciones_data.filtro.locales = $(".local_reportes_betbar").val();
	get_bet_bar_liquidaciones_data.filtro.canales_de_venta=$('.canal_venta_reporte_betbar').val();
	get_bet_bar_liquidaciones_data.filtro.red_id = {};
	get_bet_bar_liquidaciones_data.filtro.red_id[0] = 1;

	//get_bet_bar_liquidaciones_data.filtro.red_id=$('.red_reporte_betbar').val();
	get_bet_bar_liquidaciones_data.where="liquidaciones";
	if(url_object){
		if(url_object.query){
			if(url_object.query.proceso_unique_id){
				get_bet_bar_liquidaciones_data.filtro.proceso_unique_id=url_object.query.proceso_unique_id;
			}
		}
	}
	console.log(get_bet_bar_liquidaciones_data);

	localStorage.setItem("reportes_betbar_inicio_fecha_localstorage",get_bet_bar_liquidaciones_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_betbar_fin_fecha_localstorage",get_bet_bar_liquidaciones_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_reportes_betbar_get_reporte","data":get_bet_bar_liquidaciones_data});
	$.ajax({
		data: get_bet_bar_liquidaciones_data,
		type: "POST",
		url: "/api/?json",
		async: "false"
	})
	.done(function(responsedata, textStatus, jqXHR ) {
		try{
			//console.log(responsedata);
			var obj = jQuery.parseJSON(responsedata);
			//console.log(obj);
			sec__reportes_bet_bar_recaudacion_process_data(obj);
		}catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "info",
                timer: 2000,
            }, function(){
                swal.close();
                loading();
            }); 
		}
	})
	.fail(function( jqXHR, textStatus, errorThrown ) {
		if ( console && console.log ) {
			console.log( "La solicitud liquidaciones a fallado: " +  textStatus);
		}
	})
    .always(function() {
        console.log("complete liquidaciones");
    });
}
function sec_reportes_bet_bar_caja_sistema_get_reporte(){
	loading(true);	
	var get_caja_sistema_data = {};
	get_caja_sistema_data.where = "reporte_caja";
	get_caja_sistema_data.filtro = {};
	get_caja_sistema_data.filtro.fecha_inicio = $('.reportes_betbar_inicio_fecha').val();
	get_caja_sistema_data.filtro.fecha_fin = $('.reportes_betbar_fin_fecha').val();
	get_caja_sistema_data.filtro.locales = $('.local_reportes_betbar').val();
	get_caja_sistema_data.filtro.canales_de_venta = $('.canal_venta_reporte_betbar').val();
	get_caja_sistema_data.filtro.red_id = {};	
	get_caja_sistema_data.filtro.red_id[0] = 1;
	//get_caja_sistema_data.filtro.red_id=$('.red_reporte_betbar').val();

	localStorage.setItem("reportes_betbar_inicio_fecha_localstorage",get_caja_sistema_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_betbar_fin_fecha_localstorage",get_caja_sistema_data.filtro.fecha_fin);	
	auditoria_send({"proceso":"sec_reportes_betbar_get_reporte","data":get_caja_sistema_data});
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
	        //console.log(obj);
	        sec_reportes_bet_bar_caja_sistema_create_table(obj);
		}catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "info",
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
        console.log("complete caja sistema");
    });
}
function sec_reportes_bet_bar_apuestas_get_reportes(){
	loading(true);
	var get_reportes_data = {};
	get_reportes_data.where = "resultado_apuestas";
	get_reportes_data.filtro = {};
	get_reportes_data.filtro.fecha_inicio = $('.reportes_betbar_inicio_fecha').val();
	get_reportes_data.filtro.fecha_fin = $('.reportes_betbar_fin_fecha').val();
	get_reportes_data.filtro.locales = $('.local_reportes_betbar').val();
	get_reportes_data.filtro.canales_de_venta = $('.canal_venta_reporte_betbar').val();
	get_reportes_data.filtro.red_id = {};	
	get_reportes_data.filtro.red_id[0]=1;
	//get_reportes_data.filtro.red_id=$('.red_reporte_betbar').val();

	localStorage.setItem("reportes_betbar_inicio_fecha_localstorage",get_reportes_data.filtro.fecha_inicio);
	localStorage.setItem("reportes_betbar_fin_fecha_localstorage",get_reportes_data.filtro.fecha_fin);

	//console.log(get_reportes_data);
	auditoria_send({"proceso":"sec_reportes_bet_bar_apuestas_get_reportes","data":get_reportes_data});
	$.ajax({
		type: "POST",
		url: "/api/?json",
		data: get_reportes_data,
		beforeSend: function( xhr ) {
			//console.log("START");
		}
	})
	.done(function(responsedata, textStatus, jqXHR ) {
		try{
			var obj = jQuery.parseJSON(responsedata);
			//console.log(obj);
			sec_reportes_bet_data_apuestas_process_data(obj);
		}catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "info",
                timer: 2000,
            }, function(){
                swal.close();
                loading();
            }); 
		}
	})
	.fail(function( jqXHR, textStatus, errorThrown ) {
		console.log( "La solicitud reportes a fallado: " +  textStatus);
	})
    .always(function() {
        console.log("complete apuestas");
    });	 
}
function sec_reportes_bet_data_apuestas_process_data(obj){
	var nombre_mes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];  
	try{
		var obj = jQuery.parseJSON(r);
	}catch(err){
	}
	var cols = Object();
		cols["total_apostado"]="Dinero Apostado";
		/*cols["por_pagar"]="Premios x Pagar";*/
		cols["total_ganado"]="Dinero Ganado";
		cols["total_pagado"]="Dinero Pagado";
		cols["por_pagar"]="Dinero por Pagar";					
		cols["net_win"]="Net Win T";
		cols["hold"]="Hold%";
		cols["num_tickets"]="Tickets Emitidos ";
		cols["num_tickets_ganados"]="Tickets Ganados";	
		cols["num_tickets_ganados_pagados"]="Tickets Pagados";
		cols["num_tickets_por_pagar"]="Tickets por Pagar";
		cols["apuesta_x_ticket"]="Apuesta x Ticket";
		cols["tickets_premiados"]="% Ticket Premiados";
		cols["total_depositado_web"]="Dinero Depositado Web";
		cols["total_retirado_web"]="Dinero Retirado Web";
		
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
	var html_table = $("<table class='tabla_reportes'>").attr("id","reporte_apuestas").attr("width","100%");
	var html_thead=$("<thead>");
	var html_tr = $("<tr>");
		html_tr.append('<th rowspan="2" class="cabecera_collapse_expand"></th>');
		html_tr.append('<th rowspan="2" class="cabecera_canal_venta">Canal de venta</th>');
		html_tr.append('<th rowspan="2" class="cabecera_local">Nombre de Local</th>');
		html_tr.append('<th rowspan="2" class="cabecera_tipo">Tipo</th>');
		html_tr.append('<th rowspan="2" class="cabecera_asesor">Agente</th>');		
		html_tr.append('<th rowspan="2" class="cabecera_tipo_administracion">Tipo admin.</th>');
		html_tr.append('<th rowspan="2" class="cabecera_tipo_punto">Tipo de punto</th>');
		//html_tr.append('<th rowspan="2" class="cabecera_qty">QTY</th>');
		
	$.each(obj.resumen, function(year_index, year_data) {
		var year_th_td = $("<th class='cabecera_anio' id='cabeceraanio_"+year_index+"'>").attr("rowspan","1").attr("colspan",(Object.keys(year_data).length * Object.keys(cols).length)).html("<button class='btn_show_year' id='"+year_index+"'>+</button><button class='btn_hide_year' id='"+year_index+"'>-</button>"+year_index);
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
			var month_th_td = $("<th class='cabecera_mes cabecera_meses_del_anio' data-period="+year_index+''+month_year+" id='cabecera_"+year_index+''+month_year+"'>").attr("colspan",Object.keys(cols).length).html("<button class='btn_show_month' data-period="+year_index+''+month_year+" id='btn_show_month_"+year_index+''+month_year+"'>+</button><button class='btn_hide_month' data-period="+year_index+''+month_year+" id='btn_hide_month_"+year_index+''+month_year+"'>-</button>"+nombre_mes[parseInt(array_final_months_names[count_final_month_names])-1]);
			html_tr.append(month_th_td);
			count_final_month_names++;
		});
	});
	html_thead.append(html_tr);
	var html_tr = $("<tr>");
		html_tr.append('<th rowspan="1" class="cabecera_collapse_expand"><button type="button" class="btn_collapse_expand_row_reporte_apuestas all_parent"><span class="glyphicon glyphicon-pushpin"></span></th>');
		html_tr.append('<th rowspan="1" class="cabecera_canal_venta"><div class="inner-addon right-addon"><i class="glyphicon glyphicon-remove icon" id="icon_clean_input_search_canal_venta"></i><input type="text" id="buscar_canal_venta" class="buscador_canal_venta form-control" placeholder="Buscar" /></div></th>');
		html_tr.append('<th rowspan="1" class="cabecera_local"><div class="inner-addon right-addon"><i class="glyphicon glyphicon-remove icon" id="icon_clean_input_search_local"></i><input type="text" id="buscar_local" class="buscador_local form-control" placeholder="Buscar" /></div></th>');
		html_tr.append('<th rowspan="1" class="cabecera_tipo"><div class="inner-addon right-addon"><i class="glyphicon glyphicon-remove icon" id="icon_clean_input_search_tipo"></i><input type="text" id="buscar_tipo" class="buscador_tipo form-control" placeholder="Buscar" /></div></th>');
		html_tr.append('<th rowspan="1" class="cabecera_asesor"><div class="inner-addon right-addon"><i class="glyphicon glyphicon-remove icon" id="icon_clean_input_search_asesor"></i><input type="text" id="buscar_asesor" class="buscador_asesor form-control" placeholder="Buscar" /></div></th>');
		html_tr.append('<th rowspan="1" class="cabecera_tipo_administracion"><div class="inner-addon right-addon"><i class="glyphicon glyphicon-remove icon" id="icon_clean_input_search_tipo_admin"></i><input type="text" id="buscar_tipo_admin" class="buscador_tipo_admin form-control" placeholder="Buscar" /></div></th>');
		html_tr.append('<th rowspan="1" class="cabecera_tipo_punto"><div class="inner-addon right-addon"><i class="glyphicon glyphicon-remove icon" id="icon_clean_input_search_tipo_punto"></i><input type="text" id="buscar_tipo_punto" class="buscador_tipo_punto form-control" placeholder="Buscar" /></div></th>');

		//html_tr.append('<th rowspan="1" class="cabecera_qty"><div class="inner-addon right-addon"><i class="glyphicon glyphicon-remove icon" id="icon_clean_input_search_qty"></i><input type="text" id="buscar_qty" class="buscador_qty form-control" placeholder="Buscar" /></div></th>');	
	$.each(obj.resumen, function(year_index, year_data) {
		$.each(year_data, function(month_year, month_data) {
			$.each(cols, function(col_index, col_data) {
				var options="";
				if (col_index=="total_apostado") {
					options ="<th class='cabecera_dinero_apostado'>" ;      
				}
				else if (col_index=="total_ganado") {
					options ="<th class='cabecera_total_ganado data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ;                 
				}		
	
				else if(col_index=="total_pagado"){
					options ="<th class='cabecera_premios_pagados data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ; 
				}
				else if (col_index=="por_pagar") {
					options ="<th class='cabecera_premios_pagados_por_pagar data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ; 
				}
				else if(col_index=="net_win"){
					options ="<th class='cabecera_net_win_t data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ;                 
				}else if(col_index=="hold"){
					options ="<th class='cabecera_hold data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ;                 
				}else if(col_index=="num_tickets"){
					options ="<th class='cabecera_tickets_emitidos data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ;                 
				}else if(col_index=="apuesta_x_ticket"){
					options ="<th class='cabecera_apuesta_por_ticket data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ;                 
				}else if(col_index=="num_tickets_ganados"){
					options ="<th class='cabecera_ticket_premiados data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ;                 
				}
				else if(col_index=="num_tickets_ganados_pagados"){
					options ="<th class='cabecera_num_tickets_ganados_pagados data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ;                 
				}
				else if(col_index=="num_tickets_por_pagar"){
					options ="<th class='cabecera_num_tickets_por_pagar data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ;                 
				}
				else if(col_index=="tickets_premiados"){
					options ="<th class='cabecera_ticket_premiados data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ;                 
				}else if(col_index=="total_depositado_web"){
					options ="<th class='cabecera_dinero_depositado_web data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ;                 
				}else if(col_index=="total_retirado_web"){
					options ="<th class='cabecera_dinero_retirado_web data-period="+year_index+''+month_year+" cabeceras"+year_index+''+month_year+" oculto'>" ;                 
				}

				var year_th_td = $(options).html(col_data);
				html_tr.append(year_th_td);
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
					var local = $.extend({},local_data);
						local.year = year_index;
						local.month = month_index;
						local.period = year_index+""+month_index;
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
						var total = $.extend({},total_local_data);
							total.year = year_index;                
							total.month = month_index;
							total.period = year_index+""+month_index; 
							total.canal_de_venta_id= cdv_id;
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
						var super_total = $.extend({},total_local_data);
							super_total.year = year_index;
							super_total.month = month_index;
							super_total.period = year_index+""+month_index; 
							super_total.canal_de_venta_id= cdv_id;
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
		obj_by_period[n_val.period][n_val.local_id+""+n_val.canal_de_venta_id]=n_val;
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
					var html_tr = $('<tr class="clickable-row rows_hidden children children_row_collapse_expand_'+cdv_index+'" id="'+cdv_index+'">');
						html_tr.append('<td><button class="btn_collapse_expand_row_reporte_apuestas parent parent_row_collapse_expand_'+cdv_index+'" id="'+cdv_index+'"><span class="glyphicon glyphicon-pushpin"/></button></td>');					
						html_tr.append('<td class="nombre_canal_de_venta_reporte">'+cdv_nombre+'</td>');
						html_tr.append('<td class="nombre_local_reporte">'+obj_data.nombre+'</td>');
						html_tr.append('<td class="col_local_propiedad">'+obj_data.propiedad+'</td>');
						if (obj_data.asesor_nombre==null) {
							html_tr.append('<td class="col_local_asesor_nombre"></td>');
						}else{
							html_tr.append('<td class="col_local_asesor_nombre">'+obj_data.asesor_nombre+'</td>');
						}
				
						html_tr.append('<td class="col_local_administracion">'+obj_data.administracion+'</td>');
						html_tr.append('<td class="col_local_tipo_de_punto">'+obj_data.tipo+'</td>');
						//html_tr.append('<td class="">-</td>');
					$.each(period_arr, function(period_index, period_val) {
						$.each(cols, function(col_index, col_data) {
							if(obj_by_period[period_index][obj_data.local_id+""+obj_data.canal_de_venta_id]){
								if(obj_by_period[period_index][obj_data.local_id+""+obj_data.canal_de_venta_id][col_index]){
									if(col_index=="total_apostado"){
										html_tr.append('<td class="mostrado">'+obj_by_period[period_index][obj_data.local_id+""+obj_data.canal_de_venta_id][col_index]+'</td>');
									}else if(col_index=="total_pagado"){
										if(obj_data.local_id==1){
											html_tr.append('<td class="'+period_index+' oculto">'+obj_by_period[period_index][obj_data.local_id+""+obj_data.canal_de_venta_id]["total_ganado"]+'</td>');
										}else{
											html_tr.append('<td class="'+period_index+' oculto">'+obj_by_period[period_index][obj_data.local_id+""+obj_data.canal_de_venta_id][col_index]+'</td>');
										}
									}else{
										html_tr.append('<td class="'+period_index+' oculto">'+obj_by_period[period_index][obj_data.local_id+""+obj_data.canal_de_venta_id][col_index]+'</td>');
									}
								}else{
									if(col_index=="total_apostado"){
										html_tr.append('<td class="mostrado">0</td>');
									}else{
										html_tr.append('<td class="'+period_index+' oculto">0</td>');
									}
								}
							}else{
								if(col_index=="total_apostado"){
									html_tr.append('<td class="mostrado">0</td>');
								}else{
									html_tr.append('<td class="'+period_index+' oculto">0</td>');
								}
							}
						});
					});
					html_table.append(html_tr);
					if(new_obj.length > obj_index+1){
						var next_object = new_obj[obj_index+1];
						if(obj_data.canal_de_venta_id.localeCompare(next_object.canal_de_venta_id) != 0){
							var html_tr1 = $('<tr class="total_reporte clickable-row" >');
								html_tr1.append('<td><button class="btn_collapse_expand_row_reporte_apuestas parent parent_row_collapse_expand_'+cdv_index+'" id="'+cdv_index+'" ><span class="glyphicon glyphicon-pushpin"/></button></td>');							
								html_tr1.append('<td colspan="6" class="etiqueta_total nombre_canal_de_venta_reporte">Total Canal '+cdv_nombre+'</td>');                          
							$.each(period_arr, function(period_index, period_val) {
								$.each(cols, function(col_index, col_data) {
									if(obj_total_by_period[period_index][obj_data.canal_de_venta_id]){
										if(obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]){
											if(col_index=="total_apostado"){
												html_tr1.append('<td class="mostrado">'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');
											}else{
												html_tr1.append('<td class="'+period_index +'  oculto">'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');  
											}
										}else{
											if (col_index=="total_apostado") {
												html_tr1.append('<td class="mostrado">0</td>');
											}else{
												html_tr1.append('<td class="'+period_index+' oculto">0</td>');
											}
										}
									}else{
										if(col_index=="total_apostado"){
											html_tr1.append('<td class="mostrado">0</td>');  
										}else{
											html_tr1.append('<td class="'+period_index+' oculto">0</td>');
										}
									}
								});
							});
						}
					}
					if(new_obj.length -1 == obj_index){
						var html_tr1 = $('<tr class="total_reporte clickable-row" id="'+cdv_index+'">');
							html_tr1.append('<td><button class="btn_collapse_expand_row_reporte_apuestas parent parent_row_collapse_expand_'+cdv_index+'" id="'+cdv_index+'"><span class="glyphicon glyphicon-pushpin"/></button></td>');
							html_tr1.append('<td colspan="6" class="etiqueta_total nombre_canal_de_venta_reporte">Total Canal '+cdv_nombre+'</td>');
						$.each(period_arr, function(period_index, period_val) {
							$.each(cols, function(col_index, col_data) {
								if(obj_total_by_period[period_index][obj_data.canal_de_venta_id]){
									if(obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]){
										if(col_index=="total_apostado"){
											html_tr1.append('<td class="mostrado">'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');
										}else{
											html_tr1.append('<td class="'+period_index+' oculto" >'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');    
										}
									}else{
										if(col_index=="total_apostado") {
											html_tr1.append('<td class="mostrado">0</td>');
										}else{
											html_tr1.append('<td class="'+period_index+' oculto" >0</td>');
										}
									}
								}else{
									if(col_index=="total_apostado") {
										html_tr1.append('<td class="mostrado">0</td>');
									}else{
										html_tr1.append('<td class="'+period_index+' oculto" >0</td>');
									}
								}
							});
						});
					}
					html_table.append(html_tr1); 
				}
			}
		});
	});
	var html_tr2 = $("<tr class='total_reporte clickable-row'>");
		html_tr2.append('<td></td>');	
		html_tr2.append('<td colspan="6" class="etiqueta_total">Total Canales</td>');
	$.each(obj_super_total_by_period, function(index_stotal, val_stotal) {
		//console.log(val_stotal);


		html_tr2.append('<td class="mostrado">'+val_stotal.total.total_apostado+'</td>');
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.total_ganado+'</td>');		
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.total_pagado+'</td>');
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.por_pagar+'</td>');		
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.net_win+'</td>');
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.hold+'</td>');
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.num_tickets+'</td>');
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.num_tickets_ganados+'</td>');
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.num_tickets_ganados_pagados+'</td>');
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.num_tickets_por_pagar+'</td>');						
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.apuesta_x_ticket+'</td>');		
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.tickets_premiados+'</td>');
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.total_depositado_web+'</td>');
		html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.total_retirado_web+'</td>');
	});

	html_table.append(html_tr2); 

	$(".tabla_contenedor_reportes").html(html_table);
	$(".tabla_contenedor_reportes").removeClass('table-responsive');
	sec_reportes_resultados_apuestas_events();
	loading();
}
function sec_reportes_bet_bar_recaudacion_validacion_permisos_usuarios(btn){
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");

		console.log(event.event_data);

		if (event.event_data==true) {
			console.log(event.event_data);
			//loading(true);
			var selectionslocales = $(".local_reportes_betbar").select2('data').text; 
			var selectionscanalventarecaudacion = $(".canal_venta_reporte_betbar").select2('data').text;  
			sec_reportes_bet_bar_get_liquidaciones();
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
function sec_reportes_bet_bar_caja_sistema_validacion_permisos_usuarios(btn){
	console.log("btn_filtrar_reporte_caja_sistema:click");
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			console.log(event.event_data);
			//loading(true);
				var limit_date_1 = $('.reportes_betbar_inicio_fecha').val();
				var limit_date_2 = $('.reportes_betbar_fin_fecha').val();

				var start = new Date(limit_date_1);
				var end = new Date(limit_date_2);
				var diff  = new Date(end - start);
				var dias = diff/1000/60/60/24;

				if(dias > 31){
					sweetAlert("Oops...", "seleccione menos de 31 días!", "error");
				}else{
					loading(true);	
					sec_reportes_bet_bar_caja_sistema_get_reporte();				
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
function sec_reportes_bet_bar_apuestas_validacion_permisos_usuarios(btn){
	$(document).on("evento_validar_permiso_usuario",function(event) {
		$(document).off("evento_validar_permiso_usuario");
		console.log("EVENT: evento_validar_permiso_usuario");
		if (event.event_data==true) {
			console.log(event.event_data);
			loading(true);
			sec_reportes_bet_bar_apuestas_get_reportes();
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
function sec_reportes_bet_bar_busqueda_tabla_reporte_apuestas(){
		var message_colspan=0;
		var meses_columns = 0;
		meses_columns =10*array_meses_count;
		message_colspan = 7+meses_columns;
		$.expr[":"].containsNoCase = function(el, i, m) {
		var search = m[3];
		if (!search) return false;
			return eval("/" + search + "/i").test($(el).text());
		};
		$('#icon_clean_input_search_canal_venta').hide();
		$('#icon_clean_input_search_local').hide();       
		$('#icon_clean_input_search_tipo').hide(); 
		$('#icon_clean_input_search_tipo_admin').hide(); 
		$('#icon_clean_input_search_tipo_punto').hide();		
		$('#icon_clean_input_search_qty').hide();

		$('#icon_clean_input_search_canal_venta').click(function() {
			sec_reportes_bet_bar_resetear_busqueda_canal_venta();
		});
		$('#icon_clean_input_search_local').click(function() {
			sec_reportes_bet_bar_resetear_busqueda_local();
		});       
		$('#icon_clean_input_search_tipo').click(function() {
			sec_reportes_bet_bar_resetear_busqueda_tipo();
		});
		$('#icon_clean_input_search_tipo_admin').click(function() {
			sec_reportes_bet_bar_resetear_busqueda_tipo_admin();
		});
		$('#icon_clean_input_search_tipo_punto').click(function() {
			sec_reportes_bet_bar_resetear_busqueda_tipo_punto();
		});
		$('#icon_clean_input_search_qty').click(function() {
			sec_reportes_bet_bar_resetear_busqueda_qty();
		});


		$('#buscar_canal_venta').keyup(function(event) {
			if (event.keyCode == 27) {
				sec_reportes_bet_bar_resetear_busqueda_canal_venta();
			}
		});
		$('#buscar_local').keyup(function(event) {
			if (event.keyCode == 27) {
				sec_reportes_bet_bar_resetear_busqueda_local();
			}
		}); 
		$('#buscar_tipo').keyup(function(event) {
			if (event.keyCode == 27) {
				sec_reportes_bet_bar_resetear_busqueda_tipo();
			}
		});
		$('#buscar_tipo_admin').keyup(function(event) {
			if (event.keyCode == 27) {
				sec_reportes_bet_bar_resetear_busqueda_tipo_admin();
			}
		});
		$('#buscar_tipo_punto').keyup(function(event) {
			if (event.keyCode == 27) {
				sec_reportes_bet_bar_resetear_busqueda_tipo_punto();
			}
		});
		$('#buscar_qty').keyup(function(event) {
			if (event.keyCode == 27) {
				sec_reportes_bet_bar_resetear_busqueda_qty();
			}
		});		

		$('#buscar_canal_venta').keyup(function() {
			if ($('#buscar_canal_venta').val().length > 2) {
				$('#reporte_apuestas tr').hide();
				$('#reporte_apuestas tr:first').show();
				$('#reporte_apuestas tr td:containsNoCase(\'' + $('#buscar_canal_venta').val() + '\')').parent().show();
				$('#icon_clean_input_search_canal_venta').show();
			}
			else if ($('#buscar_canal_venta').val().length == 0) {
				sec_reportes_bet_bar_resetear_busqueda_canal_venta();
			}
			if ($('#reporte_apuestas tr:visible').length == 1) {
				$('.norecords').remove();
				$('#reporte_apuestas').append('<tr class="norecords"><td colspan="'+message_colspan+'" class="mensaje_busqueda_no_resultados"><span class="mensaje_text_no_registros">No se encontraron registros.</span></td></tr>');
			}
		});


		$('#buscar_local').keyup(function() {
			if ($('#buscar_local').val().length > 2) {
				$('#reporte_apuestas tr').hide();
				$('#reporte_apuestas tr:first').show();
				$('#reporte_apuestas tr td:containsNoCase(\'' + $('#buscar_local').val() + '\')').parent().show();
				$('#icon_clean_input_search_local').show();
			}
			else if ($('#buscar_local').val().length == 0) {
				sec_reportes_bet_bar_resetear_busqueda_local();
			}
			if ($('#reporte_apuestas tr:visible').length == 1) {
				$('.norecords').remove();
				$('#reporte_apuestas').append('<tr class="norecords"><td colspan="'+message_colspan+'" class="mensaje_busqueda_no_resultados"><span class="mensaje_text_no_registros">No se encontraron registros.</span></td></tr>');
			}
		}); 
		$('#buscar_tipo').keyup(function() {
			if ($('#buscar_tipo').val().length > 2) {
				$('#reporte_apuestas tr').hide();
				$('#reporte_apuestas tr:first').show();
				$('#reporte_apuestas tr td:containsNoCase(\'' + $('#buscar_tipo').val() + '\')').parent().show();
				$('#icon_clean_input_search_tipo').show();
			}
			else if ($('#buscar_tipo').val().length == 0) {
				sec_reportes_bet_bar_resetear_busqueda_tipo();
			}
			if ($('#reporte_apuestas tr:visible').length == 1) {
				$('.norecords').remove();
				$('#reporte_apuestas').append('<tr class="norecords"><td colspan="'+message_colspan+'" class="mensaje_busqueda_no_resultados"><span class="mensaje_text_no_registros">No se encontraron registros.</span></td></tr>');
			}
		});


		$('#buscar_tipo_admin').keyup(function() {
			if ($('#buscar_tipo_admin').val().length > 2) {
				$('#reporte_apuestas tr').hide();
				$('#reporte_apuestas tr:first').show();
				$('#reporte_apuestas tr td:containsNoCase(\'' + $('#buscar_tipo_admin').val() + '\')').parent().show();
				$('#icon_clean_input_search_tipo_admin').show();
			}
			else if ($('#buscar_tipo_admin').val().length == 0) {
				sec_reportes_bet_bar_resetear_busqueda_tipo();
			}
			if ($('#reporte_apuestas tr:visible').length == 1) {
				$('.norecords').remove();
				$('#reporte_apuestas').append('<tr class="norecords"><td colspan="'+message_colspan+'" class="mensaje_busqueda_no_resultados"><span class="mensaje_text_no_registros">No se encontraron registros.</span></td></tr>');
			}
		});

		$('#buscar_tipo_punto').keyup(function() {
			if ($('#buscar_tipo_punto').val().length > 2) {
				$('#reporte_apuestas tr').hide();
				$('#reporte_apuestas tr:first').show();
				$('#reporte_apuestas tr td:containsNoCase(\'' + $('#buscar_tipo_punto').val() + '\')').parent().show();
				$('#icon_clean_input_search_tipo_punto').show();
			}
			else if ($('#buscar_tipo_punto').val().length == 0) {
				sec_reportes_bet_bar_resetear_busqueda_tipo();
			}
			if ($('#reporte_apuestas tr:visible').length == 1) {
				$('.norecords').remove();
				$('#reporte_apuestas').append('<tr class="norecords"><td colspan="'+message_colspan+'" class="mensaje_busqueda_no_resultados"><span class="mensaje_text_no_registros">No se encontraron registros.</span></td></tr>');
			}
		});


		$('#buscar_qty').keyup(function() {
			if ($('#buscar_qty').val().length > 2) {
				$('#reporte_apuestas tr').hide();
				$('#reporte_apuestas tr:first').show();
				$('#reporte_apuestas tr td:containsNoCase(\'' + $('#buscar_qty').val() + '\')').parent().show();
				$('#icon_clean_input_search_qty').show();
			}
			else if ($('#buscar_qty').val().length == 0) {
				sec_reportes_bet_bar_resetear_busqueda_tipo();
			}
			if ($('#reporte_apuestas tr:visible').length == 1) {
				$('.norecords').remove();
				$('#reporte_apuestas').append('<tr class="norecords"><td colspan="'+message_colspan+'" class="mensaje_busqueda_no_resultados"><span class="mensaje_text_no_registros">No se encontraron registros.</span></td></tr>');
			}
		});
}
function sec_reportes_bet_bar_resetear_busqueda_canal_venta() {
  	$('#buscar_canal_venta').val('');
  	$('#reporte_apuestas tr').show();
  	$('.norecords').remove();
	$('#icon_clean_input_search_canal_venta').hide();
 	$('#buscar_canal_venta').focus();
}
function sec_reportes_bet_bar_resetear_busqueda_local(){
  	$('#buscar_local').val('');
  	$('#reporte_apuestas tr').show();
  	$('.norecords').remove();
	$('#icon_clean_input_search_local').hide();
 	$('#buscar_local').focus();	
}
function sec_reportes_bet_bar_resetear_busqueda_tipo(){
  	$('#buscar_tipo').val('');
  	$('#reporte_apuestas tr').show();
  	$('.norecords').remove();
	$('#icon_clean_input_search_tipo').hide();
 	$('#buscar_tipo').focus();	
}
function sec_reportes_bet_bar_resetear_busqueda_tipo_admin(){
  	$('#buscar_tipo_admin').val('');
  	$('#reporte_apuestas tr').show();
  	$('.norecords').remove();
	$('#icon_clean_input_search_tipo_admin').hide();
 	$('#buscar_tipo_admin').focus();	
}
function sec_reportes_bet_bar_resetear_busqueda_tipo_punto(){
  	$('#buscar_tipo_punto').val('');
  	$('#reporte_apuestas tr').show();
  	$('.norecords').remove();
	$('#icon_clean_input_search_tipo_punto').hide();
 	$('#buscar_tipo_punto').focus();	
}
function sec_reportes_bet_bar_resetear_busqueda_qty(){
  	$('#buscar_qty').val('');
  	$('#reporte_apuestas tr').show();
  	$('.norecords').remove();
	$('#icon_clean_input_search_qty').hide();
 	$('#buscar_qty').focus();	
}
function sec_reportes_bet_bar_ejecutar_reporte_caja_sistema(type, fn) { 
   	return sec_reportes_bet_bar_export_table_to_excel_caja_sistema('tabla_caja_sistema', type || 'xlsx', fn); 
}  
function sec_reportes_bet_bar_validar_exportacion_caja_sistema(s) {
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
function sec_reportes_bet_bar_export_table_to_excel_caja_sistema(id, type, fn) {
     var wb = XLSX.utils.table_to_book(document.getElementById(id), {sheet:"Sheet JS"});
     var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
     var fname = fn || 'tabla_caja_sistema.' + type;
     try {
       saveAs(new Blob([sec_reportes_bet_bar_validar_exportacion_caja_sistema(wbout)],{type:"application/octet-stream"}), fname);
     } catch(e) { if(typeof console != 'undefined') console.log(e, wbout); }
     return wbout;
}
function sec_reportes_bet_bar_get_table_to_export_caja_sistema(pid, iid, fmt, ofile) {
   if(fallback) {
     if(document.getElementById(iid)) document.getElementById(iid).hidden = true; 
     Downloadify.create(pid,{
       swf: 'media/downloadify.swf',
       downloadImage: 'download.png',
       width: 100,
       height: 30,
       filename: ofile, data: function() { var o = sec_reportes_bet_bar_ejecutar_reporte_caja_sistema(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";
}
function sec_reportes_bet_bar_ejecutar_reporte_apuestas(type, fn) { 
   return sec_reportes_bet_bar_export_table_to_excel_apuestas('reporte_apuestas', type || 'xlsx', fn); 
}  
function sec_reportes_bet_bar_validar_exportacion_apuestas(s) {
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
function sec_reportes_bet_bar_export_table_to_excel_apuestas(id, type, fn) {
     var wb = XLSX.utils.table_to_book(document.getElementById(id), {sheet:"Sheet JS"});
     var wbout = XLSX.write(wb, {bookType:type, bookSST:true, type: 'binary'});
     var fname = fn || 'reporte_apuestas.' + type;
     try {
       saveAs(new Blob([sec_reportes_bet_bar_validar_exportacion_apuestas(wbout)],{type:"application/octet-stream"}), fname);
     } catch(e) { if(typeof console != 'undefined') console.log(e, wbout); }
     return wbout;
}
function sec_reportes_bet_bar_get_table_to_export_apuestas(pid, iid, fmt, ofile) {
   if(fallback) {
     if(document.getElementById(iid)) document.getElementById(iid).hidden = true; 
     Downloadify.create(pid,{
       swf: 'media/downloadify.swf',
       downloadImage: 'download.png',
       width: 100,
       height: 30,
       filename: ofile, data: function() { var o = sec_reportes_bet_bar_ejecutar_reporte_apuestas(fmt, ofile); return window.btoa(o); },
       transparent: false,
       append: false,
       dataType: 'base64',
       onComplete: function(){ alert('Your File Has Been Saved!'); },
       onCancel: function(){ alert('You have cancelled the saving of this file.'); },
       onError: function(){ alert('You must put something in the File Contents or there will be nothing to save!'); }
     });
   }//else document.getElementById(pid).innerHTML = "";
}

function sec_reportes_bet_bar_tickets_comision_cuota_bet_bar(fecha_inicio,fecha_fin,canal_de_venta_id,local_id,columna){
	
	loading(true);
	$("#modal_detalle_liquidaciones").modal("show");

	var data_tickets_comision_cuota = Object();
	data_tickets_comision_cuota.where = "tickets_comision_cuota";
	data_tickets_comision_cuota.filtro = {};
	data_tickets_comision_cuota.filtro.fecha_inicio = fecha_inicio;
	data_tickets_comision_cuota.filtro.fecha_fin = fecha_fin;
	data_tickets_comision_cuota.filtro.canal_de_venta_id = canal_de_venta_id;
	data_tickets_comision_cuota.filtro.local_id = local_id;
	data_tickets_comision_cuota.filtro.columna = columna;

	console.log(data_tickets_comision_cuota);

	$.ajax({
		url: '/api/?json',
		type: 'POST',
		dataType: 'json',
		data: data_tickets_comision_cuota
	})
	.done(function(obj) {
		try{
			console.log(obj);
			sec_reportes_bet_bar_get_data_comision_cuota_bet_bar(obj);
		}catch(err){
            swal({
                title: 'Error en la base de datos',
                type: "info",
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
function sec_reportes_bet_bar_get_data_comision_cuota_bet_bar(obj){
		var dataf=[];
		var i = 0;
		$.each(obj.data, function(index, val) {
			if (index=="tickets") {
				$.each(val, function(index, val) {
						var data_bet_id = val.bet_id;
						var data_bet_number = val.bet_number;
						var data_bonus_amount = val.bonus_amount;
						var data_calc_date = val.calc_date;
						var data_cash_desk_info = val.cash_desk_info;
						var data_cashdesk = val.cashdesk;
						var data_comision = val.comision;
						var data_created = val.created;
						var data_currency = val.currency;
						var data_fecha_apostado = val.fecha_apostado;
						var data_freebet_amount = val.freebet_amount;
						var data_is_live = val.is_live;
						var data_odds = val.odds;
						var data_paid_cash_desk_name = val.paid_cash_desk_name;
						var data_percent = val.percent;
						var data_stake = val.stake;
						var data_stakes_in = val.stakes_in;
						var data_state = val.state;
						var data_type = val.type;
						var data_winnings = val.winnings;
						var data_winnings_in = val.winnings_in;
						var data_paiddate = val._paiddate_;
						$(".cashdesk_nombre_tickets_comision_cuota").text(data_cashdesk);
						var newObj =[
							data_bet_id,
							data_bet_number,
							data_currency,
							data_stake,
							data_odds,
							data_percent,
							data_comision,
							data_winnings,
							data_type,

							data_state,
							data_created,
							data_fecha_apostado,
							data_calc_date,
							data_is_live,

							data_paiddate,
							data_paid_cash_desk_name
						];						
						dataf[i] =  newObj;
						i++;

				});

			} 




		});
		//console.log(dataf);
		sec_reportes_bet_bar_tickets_comision_cuota_table_bet_bar(dataf);	
}
function sec_reportes_bet_bar_tickets_comision_cuota_table_bet_bar(model){
		//$.fn.dataTable.ext.errMode = 'none';
	      table_cct = $('#table_tickets_comision_cuota').DataTable({ 
	      fixedHeader: true,
	      bRetrieve: true,
	      lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],
		  paging: true,	      
	      searching: true,
	      sPaginationType: "full_numbers",
	      Sorting: [[1, 'asc']], 
	      rowsGroup: [0,1,2],
	      bSort: false,
	      data:model,
	      dom: 'Blrftip',
          buttons: [
                { 
                    extend: 'copy',
                    text:'Copiar',
                    footer: true,                    
                    className: 'copiarButton'
                 },
                { 
                    extend: 'csv',
                    text:'CSV',
                    footer: true,                    
                    className: 'csvButton' 
                    ,filename: $(".export_tickets_comision_cuota").val()
                },
                {   extend: 'excelHtml5',
                    text:'Excel',
                    footer: true,
                    className: 'excelButton'
                    ,filename: $(".export_tickets_comision_cuota").val()
		            ,customize: function(xlsx) {
		                var sheet = xlsx.xl.worksheets['sheet1.xml'];
		                $('row:first c', sheet).attr( 's', '22' );
		                $('row c', sheet).each( function () {
		                    if ( $('is t', this).text() == 'Total' ) {
		                        $(this).attr( 's', '20' );
		                    }
		                    
		                });
						
		            },
					exportOptions: {
						//columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21]
					} 		                                
                }, 
                {
                    extend: 'colvis',
                    text:'Visibilidad',
                    className:'visibilidadButton',
                    postfixButtons: [ 'colvisRestore' ]
                }
          ],
          columnDefs: [
		        { className: "tcc_id_apuestas_number", "targets": [0] },          
		        { className: "tcc_bet_number", "targets": [1] },
		        { className: "tcc_moneda_text", "targets": [2] }, 
		        { className: "tcc_monto_number", "targets": [3] }, 
		        { className: "tcc_cuotas_number", "targets": [4] },
		        { className: "tcc_porcentaje_number", "targets": [5] },
		        { className: "tcc_importe_de_comision_number", "targets": [6] },
		        { className: "tcc_ganancias_number", "targets": [7] },
		        { className: "tcc_tipo_text", "targets": [8] },
		        { className: "tcc_estado_text", "targets": [9] }, 
		        { className: "tcc_creado_number", "targets": [10] },	
		        { className: "tcc_fecha_de_apostado_number", "targets": [11] },
		        { className: "tcc_fecha_calc_number", "targets": [12] },
		        { className: "tcc_is_live_number", "targets": [13] },
		        { className: "tcc_paiddate_number", "targets": [14] },	
		        { className: "tcc_paid_cash_desk_name_number", "targets": [15] }

	      ],
		  footerCallback: function () {
			  var api = this.api(),
			  columns = [3,6,7]; 
			  for (var i = 0; i < columns.length; i++) {
				  var total =  api.column(columns[i], {filter: 'applied'}).data().sum().toFixed(2);
				  var total_pagina = api.column(columns[i], { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
				  if (total<0 && total_pagina<0){
					  $('tfoot th').eq(columns[i]).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
				  }
				  else{
					  $('tfoot th').eq(columns[i]).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
				  }
			  }
		  },  
	      pageLength: '17',		           	      
	      language:{
	            "decimal":        ".",
	            "thousands":      ",",            
	            "emptyTable":     "Tabla vacia",
	            "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
	            "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
	            "infoFiltered":   "(filtered from _MAX_ total entradas)",
	            "infoPostFix":    "",
	            "thousands":      ",",
	            "lengthMenu":     "Mostrar _MENU_ entradas",
	            "loadingRecords": "Cargando...",
	            "processing":     "Procesando...",
	            "search":         "Filtrar:",
	            "zeroRecords":    "Sin resultados",
	            "paginate": {
	                "first":      "Primero",
	                "last":       "Ultimo",
	                "next":       "Siguiente",
	                "previous":   "Anterior"
	            },
	            "aria": {
	                "sortAscending":  ": activate to sort column ascending",
	                "sortDescending": ": activate to sort column descending"
	            },
	            "buttons": {
	                "copyTitle": 'Contenido Copiado',
	                "copySuccess": {
	                    _: '%d filas copiadas',
	                    1: '1 fila copiada'
	                }
	            }		            
	      }
	  });
	  table_cct.clear().draw();
	  table_cct.rows.add(model).draw();
	  table_cct.columns.adjust().draw();
      loading();
}

