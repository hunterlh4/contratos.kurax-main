function sec_home(){
	if(sec_id=="home"){
		console.log("sec_home");
		var dashboard = false;
		dashboard = $(".sec_home_show_dashboard").val();
		if(dashboard){
			sec_home_settings();
			sec_home_event();
		}
	}
}
function sec_home_event(){
	var current_date = sec_home_get_current_date();
	sec_home_get_data_contratos_dashboard();
	sec_home_contratos_por_canal_de_venta(current_date);
	sec_home_estadistica_contratos_celebrados_por_meses();
	sec_home_estadistica_tipo_de_contratos();
	sec_home_apostado_canal_de_venta_por_mes();		
	sec_home_get_data_treeview_total_apostado_asesor_cdv();
	sec_home_get_data_asesores();
	sec_home_get_data_to_table();

	$(".btn_consultar_apostado_ejecutivo_ventas").off().on("click",function(){
		loading(true);
		sec_home_get_data_treeview_total_apostado_asesor_cdv();
	});
}
function sec_home_settings(){
	$('.contratos_por_canal_de_venta_datepicker')
		.datepicker({
			dateFormat:'yy-mm',
			changeMonth: true,
			changeYear: true,
			showButtonPanel: true,
			closeText : "Enviar",			
			onClose: function(dateText, inst) { 
				var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
				var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
				$(this).datepicker('setDate', new Date(year, month, 1));
				var date = $(this).val();
				sec_home_contratos_por_canal_de_venta(date);
			} 
    })

	$(".total_apostado_ejecutivo_ventas_cdv_datepicker_datepicker")
		.datepicker({
			dateFormat:'yy-mm',
			changeMonth: true,
			changeYear: true,
			showButtonPanel: true,
			closeText : "Enviar",
			onClose: function(dateText, inst) { 
				var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
				var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
				$(this).datepicker('setDate', new Date(year, month, 1));
				var date = $(this).val();
			} 						
		})
	$('.asesor_ejecutivo_de_ventas').select2({
		closeOnSelect: false,            
		allowClear: true,
		placeholder: "Asesor de Ventas",
		containerCssClass: "select2_asesor_ejecutivo_de_ventas" 
	});	
}
function sec_home_get_data_contratos_dashboard(){
		var data_dashboard = {};
		data_dashboard.where="dashboard";
		data_dashboard.condition="contratos_tipos";		
		data_dashboard.filtro = {};
		data_dashboard.filtro.filtro_contratos_creados = $(".contratos_creados_dashboard").data();
		data_dashboard.filtro.filtro_contratos_por_vencer = $(".contratos_por_vencer_dashboard").data();
		data_dashboard.filtro.filtro_contratos_vencidos = $(".contratos_vencidos_dashboard").data();
		data_dashboard.filtro.filtro_clientes_nuevos = $(".clientes_nuevos_dashboard").data();
		auditoria_send({"proceso":"sec_home_get_data_contratos_dashboard","data":data_dashboard});
		$.ajax({
			url: '/api/?json',
			type: 'POST',
			dataType: 'json',
			data: data_dashboard,
		})
		.done(function(data_response ) {
			$("div.contratos_creados_dashboard_value").text(data_response.contratos_creados.count);
			$("div.contratos_por_vencer_dashboard_value").text(data_response.contratos_por_vencer.count);
			$("div.contratos_vencidos_dashboard_value").text(data_response.contratos_vencidos.count);
			$("div.nuevos_clientes_dashboard_value").text(data_response.nuevos_clientes.count);
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			if ( console && console.log ) {
				console.log( "La solicitud dashboard contratos activos,por vencer,vencidos,clientes a fallado : " +  textStatus);
			}
		})
}
function sec_home_contratos_por_canal_de_venta(date){
	var month = date.split("-")[1];
	var next_month = parseInt(month)+1;
	if (next_month<10) {
		next_month="0"+next_month;
	};
	var next_period = date.split("-")[0]+"-"+next_month;
	var data_contratos_canal = {};
	data_contratos_canal.where = "chart_contratos_por_canal_de_venta";
	data_contratos_canal.filtro = {};
	data_contratos_canal.filtro.current_date=date;
	data_contratos_canal.filtro.next_date = next_period;
	auditoria_send({"proceso":"sec_home_contratos_por_canal_de_venta","data":data_contratos_canal});	
	$.ajax({
        url: 'sys/dashboard_data.php',
		type: 'POST',
		dataType: 'json',
		data: data_contratos_canal,
	    success: function(data) {
	        chartData = data;
	        var chartProperties = {
	            "caption": "",
	            "subcaption": "",
	            "canvasborderalpha": "0",
	            "canvasbordercolor": "333",
	            "canvasborderthickness": "1",
	            "captionpadding": "30",
	            "numberprefix": "",
	            "plotgradientcolor": "",
	            "captionFontSize": "14",
	            "subcaptionFontSize": "14",
	            "subcaptionFontBold": "0",
	            "paletteColors": "#8CCC04,#AE41FE,#FAAF02,#07DBCE,#FDAF07",
	            "bgcolor": "#ffffff",
	            "showBorder": "0",
	            "showShadow": "0",
	            "showCanvasBorder": "0",
	            "usePlotGradientColor": "0",
	            "legendBorderAlpha": "0",
	            "legendShadow": "0",
	            "linethickness": "3",
	            "showAxisLines": "0",
	            "showAlternateHGridColor": "0",
	            "divlineThickness": "1",
	            "divLineIsDashed": "1",
	            "divLineDashLen": "1",
	            "divLineGapLen": "1",
	            "divlinecolor":"111",
	            "yaxismaxvalue": "20",
	            "yaxisvaluespadding": "15",
	            "showValues": "1",
	            "exportenabled": "1"                                            ,
	        };
	        apiChart = new FusionCharts({
	            type: 'bar2d',
	            renderAt: 'chart-container_contratos_por_canal_de_venta',
	            width: '100%',
	            height: '220',
	            dataFormat: 'json',
	            dataSource: {
	                "chart": chartProperties,
	                "data": chartData
	            }
	        });
	        apiChart.render();
	    }
	})
}
function sec_home_estadistica_contratos_celebrados_por_meses(){
	var data_chart_contratos_meses = {};
	data_chart_contratos_meses.where = "chart_contratos_meses";	
	auditoria_send({"proceso":"sec_home_estadistica_contratos_celebrados_por_meses","data":data_chart_contratos_meses});	
    $.ajax({
        url: 'sys/dashboard_data.php',
        type: 'POST',
        dataType: "json",
        data:data_chart_contratos_meses,        
        success: function(data) {
            chartData = data.dataset;            
            apiChart = new FusionCharts({
                type: 'mscolumn3d',
                renderAt: 'chart-container_contratos_por_meses',
                width: '100%',
                height: '280',
                dataFormat: 'json',
                dataSource: data 
                
            });
            apiChart.render();
            
        }
    });
}
function sec_home_estadistica_tipo_de_contratos(){
	var data_chart_tipo_contrato = {};
	data_chart_tipo_contrato.where = "chart_tipo_de_contrato";
	auditoria_send({"proceso":"sec_home_estadistica_tipo_de_contratos","data":data_chart_tipo_contrato});	
    $.ajax({
        url: 'sys/dashboard_data.php',
        type: 'POST',
        dataType: "json",
        data:data_chart_tipo_contrato,
        success: function(data) {
            chartData = data;
            var chartProperties = {
                "xAxisName": "Tipo",
                "yAxisName": "Cantidad",
                "rotatevalues": "1",
                "theme": "zune",
                "showLabels": "1",
                "showPercentValues": "0",
                "showLegend": "1",
                "legendShadow": "1",
                "legendBorderAlpha": "1",                                
                "decimals": "1",
                "paletteColors": "#0075c2,#1aaf5d,#f2c500,#f45b00,#8e0000",
                "bgColor": "#ffffff",
                "showBorder": "0",
                "use3DLighting": "0",
                "showShadow": "1",
                "subcaptionFontSize": "14",
                "subcaptionFontBold": "0",
                "toolTipColor": "#ffffff",
                "toolTipBorderThickness": "0",
                "toolTipBgColor": "#000000",
                "toolTipBgAlpha": "80",
                "toolTipBorderRadius": "2",
                "toolTipPadding": "5",
			    "exportenabled": "1"
            };

            apiChart = new FusionCharts({
                type: 'doughnut2d',
                renderAt: 'chart-container_contrato_por_tipo',
                width: '95%',
                height: '250',
                dataFormat: 'json',
                dataSource: {
                    "chart": chartProperties,
                    "data": chartData
                }
            });
            apiChart.render();
        }
    });	
}
function sec_home_apostado_canal_de_venta_por_mes(){
	var data_chart_apostado_canal_de_venta_por_mes = {};
	data_chart_apostado_canal_de_venta_por_mes.where = "chart_apostado_canal_de_venta_por_meses";
	auditoria_send({"proceso":"sec_home_apostado_canal_de_venta_por_mes","data":data_chart_apostado_canal_de_venta_por_mes});	
    $.ajax({
        url: 'sys/dashboard_data.php',
        type: 'POST',
        dataType: "json",
        data:data_chart_apostado_canal_de_venta_por_mes,
        success: function(data) {
            chartData = data.dataset;
            console.log(data,"asasas")
            apiChart = new FusionCharts({
                type: 'msline',
                renderAt: 'chart-container_apostado_canal_de_venta_por_mes',
                width: '100%',
                height: '220',
                dataFormat: 'json',
                dataSource: data 
                
            });
            apiChart.render();
        }
    });
}
function sec_home_get_data_treeview_total_apostado_asesor_cdv(){
	var asesor_id = 0;
	if ($("#asesor_ejecutivo_de_ventas").val()=="all") {
		asesor_id = -1;
	}else{
		asesor_id = $("#asesor_ejecutivo_de_ventas").val();		
	}

	var current_date = $("#input_text-total_apostado_ejecutivo_ventas_cdv_datepicker_fecha").val();
  	var data_total_apostado_asesor_cdv = {};
    data_total_apostado_asesor_cdv.where="total_apostado_asesor_cdv";
    data_total_apostado_asesor_cdv.asesor_id = asesor_id;
    data_total_apostado_asesor_cdv.current_date = current_date;
	auditoria_send({"proceso":"sec_home_get_data_treeview_total_apostado_asesor_cdv","data":data_total_apostado_asesor_cdv});
     $.ajax({ 
       url: 'sys/dashboard_data.php',
       method:"POST",
       dataType: "json", 
       data:data_total_apostado_asesor_cdv,      
       success: function(dataresponse)  
       {

			try {
	            $(".resultado_cantidad_locales_registro_total_apostado_mes_cdv").text(dataresponse.length);            
	            $(".resultado_cantidad_cdv_registro_total_apostado_mes_cdv").text(dataresponse[0].filas_resultado);
			}
			catch(err) {

			}

          $('#treeview_total_apostado_por_mes_asesor').treeview({
                data: dataresponse,
                expandIcon: "glyphicon glyphicon-plus",
                collapseIcon: "glyphicon glyphicon-minus",
                uncheckedIcon:"",
                nodeIcon:"fa fa-money",                      
                showIcon: true,
                showCheckbox: true,
                highlightSelected:true,
                showBorder:true,
                selectedBackColor:"#deebf7",
                borderColor: '#d2d2d2',
                onhoverColor: '#ffff99',
                selectedColor: '#8E44AD', 
                backColor: '#fff',                                                   
                multiSelect:false,
                showTags:true,
                showCheckbox:false ,
                enableLinks: false,                          
                color: '#886104',
                showTags: true,
			    exportenabled: "1",                
                onNodeChecked: function(event, node) {
                  $('#checkable-output').prepend('<p>' + node.text + ' was checked</p>');
                },
                onNodeUnchecked: function (event, node) {
                  $('#checkable-output').prepend('<p>' + node.text + ' was unchecked</p>');
                }     
          });
		  loading();
       }  
     });    
}
function sec_home_get_data_asesores(){
	var data = {};
	data.where="sec_home_get_data_asesores";
		auditoria_send({"proceso":"sec_home_get_data_asesores","data":data});
		$.ajax({
			data: data,
			type: "POST",
			dataType: "json",
            url: 'sys/dashboard_data.php',
		})
		.done(function( data, textStatus, jqXHR ) {
			try{
				if ( console && console.log ) {
					$.each(data.data,function(index,val){
						var new_option = $("<option>");
						$(new_option).val(val.id);
						$(new_option).html(val.nombre+" "+val.apellido_paterno+" "+val.apellido_materno);
						$(".asesor_ejecutivo_de_ventas").append(new_option);
					});
					$('.asesor_ejecutivo_de_ventas').select2({closeOnSelect: false});
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
				console.log( "La solicitud de asesores a fallado: " +  textStatus);
			}
		})
}
function sec_home_get_current_date(){
	var d = new Date();
	var month = d.getMonth()+1;
	var day = d.getDate();
	var output = d.getFullYear() + '-' +
	((''+month).length<2 ? '0' : '') + month
	return output;
}
function sec_home_get_data_to_table(){
	var data_get_table = {};
	data_get_table.where = "sec_home_get_lista_de_clientes";
	auditoria_send({"proceso":"sec_home_get_data_to_table","data":data_get_table});
	$.ajax({
        url: 'sys/dashboard_data.php',
		type: 'POST',
		dataType: 'json',
		data: data_get_table,
	})
	.done(function(dataresponse) {
		var datafinal = [];
		var i=0;
		$.each(dataresponse, function(data, val_clientes) {
			$.each(val_clientes, function(index_clientes, val_detalles) {
				var newObj = [
				"<img class='panel-users users-table user avatar' src='images/default_avatar.png'>",
				"<a href='?sec_id=adm_mantenimientos&sub_sec_id=13&item_id="+val_detalles.id+"' target='_blank'>"+val_detalles.nombre+"</a><br><div class='post'>"+val_detalles.razon_social+"</div>",
				"<a	class='glyphicon glyphicon-info-sign btn-preview' data-table='tbl_clientes'	data-id="+val_detalles.id+"></a>"
				];
				datafinal[i] = newObj;
				i++;
			});
			sec_home_get_data_clientes_dashboard(datafinal);
		});
	})
	.fail(function() {
		console.log("error");
	})
}
function sec_home_get_data_clientes_dashboard(model){
	var heightdoc = window.innerHeight;
	var heightnavbar= $(".navbar-header").height();
	var heighttable =heightdoc-heightnavbar-95;
  $('#tbl_lista_clientes').DataTable( {
  		pagingType:"simple",
	    scrollY:154,
	    scrollX:true,
	    scrollCollapse:true,  		
  		lengthChange: false,
        dom: '<lf<t>ip>',
        bSort:false, 		
	    data: model,
	    columns: [
	      { title: ""}
	    , { title: "" }
	    ],
	      language:{
	            "decimal":        ".",
	            "thousands":      ",",            
	            "emptyTable":     "Tabla vacia",
	            "info":           " _START_ al _END_ de _TOTAL_ .",
	            "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
	            "infoFiltered":   "",
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
	            },
	            searchPlaceholder: "Búsqueda"		            
	      }	    
  });	
}


