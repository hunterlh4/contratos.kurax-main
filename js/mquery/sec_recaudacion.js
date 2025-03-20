var data_paginas;
var data_desde;
var data_hasta;
var data_numero_por_pagina;
var data_pagina_actual;
var get_liquidaciones_data = {};
var table_dt = false;
var dt_numero = 10;
var dt_pagina = 0;
var modelo = false;
var monedas = {};
var canales_de_venta = {};
canales_de_venta["total"]="Total";
var at_liquidaciones_filtro_fecha_inicio = '2017-06-01';
var at_liquidaciones_filtro_fecha_fin = '2017-06-12';
var at_liquidaciones_filtro_locales = false;
var at_liquidaciones_filtro_canales_de_venta = false;
function sec_recaudacion_get_moneda(){
	var data = {};
	data.where="monedas";
	data.filtro={}
	$.ajax({
		data: data,
		type: "POST",
		dataType: "json",
		url: "/api/?json",
	})
	.done(function( data, textStatus, jqXHR ) {
		if ( console && console.log ) {
			$.each(data.data,function(index,val){
				monedas[val.id]=val.simbolo;
			});
		}
	})
	.fail(function( jqXHR, textStatus, errorThrown ) {
		if ( console && console.log ) {
			console.log( "La solicitud monedas a fallado: " +  textStatus);
		}
	})
}
function sec_recaudacion_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
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
					$(".canalventarecaudacion").append(new_option);

				});
				$('.canalventarecaudacion').select2({closeOnSelect: false});
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud canales de ventas a fallado: " +  textStatus);
			}
		})
}
function sec_recaudacion_get_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
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
					$(".local").append(new_option);
				});
				$('.local').select2({closeOnSelect: false});
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			if ( console && console.log ) {
				console.log( "La solicitud locales a fallado: " +  textStatus);
			}
		})
}
function sec_recaudacion_mostrar_datatable(model){
	var heightdoc = window.innerHeight;
	var heightnavbar= $(".navbar-header").height();
	var heighttable =heightdoc-heightnavbar-300;
	if (modelo==1) {
		table_dt = $('#tabla_sec_recaudacion').DataTable({ 
		responsive:false,
		fixedHeader: {
			header: true
		},
		fixedColumns:{
			leftColumns: 2
		},      
		dom: 'Blftip',
		buttons: [
		    { 
		        extend: 'copy',
		        text:'Copiar',
		        className: 'copiarButton'
		     },
		    { 
		        extend: 'csv',
		        text:'CSV',
		        className: 'csvButton' 
		    },
		    {   extend: 'excel',
		        text:'Excel',
		        className: 'excelButton' 
		    },
		    /*                    
		    {
		        text: 'Email',
		        className:'emailButton',
		        action: function ( e, dt, node, config ) {
		            //$('#modal_sec_recaudacion_modelo_uno').modal('toggle');
		        }
		    },
		    */
		    {
		        extend: 'colvis',
		        text:'Visibilidad',
		        className:'visibilidadButton',
		        postfixButtons: [ 'colvisRestore' ]
		    }
		],
		fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
			if(aData[0] && aData[1]){
				$('td', nRow).css('background-color','#337ab7');
			}
		},
		footerCallback: function () {
			var api = this.api(),
			columns = [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29]; 
			for (var i = 0; i < columns.length; i++) {
				var total =  api.column(columns[i], {filter: 'applied'}).data().sum().toFixed(2);
				var total_pagina = api.column(columns[i], { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
				if (total<0 && total_pagina<0){
					$('tfoot th').eq(columns[i]).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
					//$('tfoot th').eq(columns[i]).append('Pagina:<br><span style="color:red; font-weight: bold; font-size: 11px !important;"> ' + formatonumeros(total_pagina)+'<span>');
				}
				else{
					$('tfoot th').eq(columns[i]).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
					//$('tfoot th').eq(columns[i]).append('Pagina:<br><span style="color:green; font-weight: bold; font-size: 11px !important;"> ' + formatonumeros(total_pagina)+'<span>');                  
				}
			}
		},       
		bRetrieve: true,
		sPaginationType: "full_numbers",
		lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]], 
		pageLength: 10,
		iDisplayLength: 10,
		searching: true,
		ordering: true,           
		paging: true,
		lengthChange: true,      
		bProcessing: true,
		bAutoWidth: true,
		bStateSave: true,
		sScrollY: heighttable, 
		sScrollX: "100%", 
		sScrollXInner: "10%", 
		bScrollCollapse: true,       
		bPaginate: true, 
		bFilter: true,
		Sorting: [[1, 'asc']], 
		bSort: false,
		rowsGroup: [0,1],
		data:model,
		columnDefs: [
			{ className: "columna_local_modelo_uno", "targets": [0] }, 
			{ className: "columna_dias_procesados_modelo_uno", "targets": [1] },
			{ className: "apostado_modelo_uno", "targets": [2,6,10,14,18,22,26] }, 
			{ className: "ganado_modelo_uno", "targets": [3,7,11,15,19,23,27] },
			{ className: "pagado_modelo_uno", "targets": [4,8,12,16,20,24,28] },
			{ className: "produccion_modelo_uno", "targets": [5,9,13,17,21,25,29] },                                    
			{ className: "columnasnumeros_modelo_uno","targets": [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29]},
			{ sortable: false,"class": "index",targets: [0,1]},
			{
			aTargets: [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29],
				fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					if ( sData < "0" ) {
						$(nTd).css('color', 'red')
						$(nTd).css('font-weight', 'bold')
					}
				}
			}         
		], 
		order: [[ 1, 'asc' ]],
		aoColumns: [
			{
				"model": "cliente_local",
				sDefaultContent: ""
			},
			{
				"model": "dias_procesados",
				sDefaultContent: ""
			},
			{
				"model": "apostado",
				sDefaultContent: "0",
				className: ""     
			},      
			{
				"model": "ganado",
				sDefaultContent: "0",
				className: ""       
			},
			{
				"model": "pagado",
				sDefaultContent: "0"
			},
			{
				"model": "produccion",
				sDefaultContent: "0"
			},
			{
				"model": "apostado",
				sDefaultContent: "0",
				className: ""     
			},      
			{
				"model": "ganado",
				sDefaultContent: "0",
				className: ""       
			},
			{
				"model": "pagado",
				sDefaultContent: "0"
			},
			{
				"model": "produccion",
				sDefaultContent: "0"
			}, 
			{
				"model": "apostado",
				sDefaultContent: "0",
				className: ""     
			},      
			{
				"model": "ganado",
				sDefaultContent: "0",
				className: ""       
			},
			{
				"model": "pagado",
				sDefaultContent: "0"
			},
			{
				"model": "produccion",
				sDefaultContent: ""
			},
			{
				"model": "apostado",
				sDefaultContent: "0",
				className: ""     
			},      
			{
				"model": "ganado",
				sDefaultContent: "0",
				className: ""       
			},
			{
				"model": "pagado",
				sDefaultContent: "0"
			},
			{
				"model": "produccion",
				sDefaultContent: "0"
			},
			{
				"model": "apostado",
				sDefaultContent: "0",
				className: ""     
			},      
			{
				"model": "ganado",
				sDefaultContent: "0",
				className: ""       
			},
			{
				"model": "pagado",
				sDefaultContent: "0"
			},
			{
				"model": "produccion",
				sDefaultContent: "0"
			},
			{
				"model": "apostado",
				sDefaultContent: "0",
				className: ""     
			},      
			{
				"model": "ganado",
				sDefaultContent: "0",
				className: ""       
			},
			{
				"model": "pagado",
				sDefaultContent: "0"
			},
			{
				"model": "produccion",
				sDefaultContent: "0"
			},
			{
				"model": "apostado",
				sDefaultContent: "0",
				className: ""     
			},      
			{
				"model": "ganado",
				sDefaultContent: "0",
				className: ""       
			},
			{
				"model": "pagado",
				sDefaultContent: "0"
			},
			{
				"model": "produccion",
				sDefaultContent: "0"
			}                    
		], 
		pageLength: '30',
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
	}
	if (modelo==2) {
		table_dt = $('#tabla_sec_recaudacion').DataTable({ 
			responsive:false,
			fixedHeader: {
			header: true
			},
			dom: 'Blftip',
			buttons: [
			    { 
			        extend: 'copy',
			        text:'Copiar',
			        className: 'copiarButton'
			     },
			    { 
			        extend: 'csv',
			        text:'CSV',
			        className: 'csvButton' 
			    },
			    {   extend: 'excel',
			        text:'Excel',
			        className: 'excelButton' 
			    },                    
			    /*{
			        text: 'Email',
			        className:'emailButton',
			        action: function ( e, dt, node, config ) {
			            //$('#modal_sec_recaudacion_modelo_uno').modal('toggle');
			        }
			    },*/
			    {
			        extend: 'colvis',
			        text:'Visibilidad',
			        className:'visibilidadButton',
			        postfixButtons: [ 'colvisRestore' ]
			    }
			],
			fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
				if(aData[0] && aData[1]){
					$('td', nRow).css('background-color','#337ab7');
				}
				if (aData[2] == "Total"){
					$('td', nRow).css('background-color','#9BDFFD','important');
					$('td', nRow).css('z-index: ','10');  
					$('td', nRow).css('color','#080FFC'); 
					$('td', nRow).css('font-weight','800'); 
					$('td', nRow).css('z-index: ','10');                       
				}
				else if ( aData[2] != "Total" )
				{
					$('td', nRow).css('background-color', '#fafafa','important');
				}
			},
			footerCallback: function () {
				/*
				var api = this.api(),
				columns = [4,5,6,7]; // Add columns here
				for (var i = 0; i < columns.length; i++) {
					var total =  api.column(columns[i], {filter: 'applied'}).data().sum().toFixed(2);
					var total_pagina = api.column(columns[i], { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
					if (total<0 && total_pagina<0){
					    $('tfoot th').eq(columns[i]).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
					    $('tfoot th').eq(columns[i]).append('Pagina:<br><span style="color:red; font-weight: bold; font-size: 11px !important;"> ' + formatonumeros(total_pagina)+'<span>');
					}
					else{
					    $('tfoot th').eq(columns[i]).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
					    $('tfoot th').eq(columns[i]).append('Pagina:<br><span style="color:green; font-weight: bold; font-size: 11px !important;"> ' + formatonumeros(total_pagina)+'<span>');                  
					}
				}
				*/
			},      
			bRetrieve: true,
			sPaginationType: "full_numbers",
			lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],  
			pageLength: 10,          
			paging: true,
			lengthChange: true,      
			searching: true,
			ordering: true,      
			bProcessing: true,
			bAutoWidth: true,
			bStateSave: true,
			sScrollY: heighttable, 
			sScrollX: "100%", 
			sScrollXInner: "100%", 
			bScrollCollapse: true,       
			bPaginate: true, 
			bFilter: true,
			Sorting: [[1, 'asc']], 
			bSort: false,
			rowsGroup: [0],
			data:model,
			columnDefs: [
				{ className: "columnaprincipal_modelo_dos", "targets": [0] }, 
				{ className: "columna_dias_modelo_dos", "targets": [1] },
				{ className: "canal_venta_modelo_dos", "targets": [2] },        
				{ className: "apostado_modelo_dos", "targets": [3] }, 
				{ className: "ganado_modelo_dos", "targets": [4] },
				{ className: "pagado_modelo_dos", "targets": [5] },
				{ className: "produccion_modelo_dos", "targets": [6] },                                    
				{ className: "columnasnumeros_modelo_dos","targets": [3,4,5,6]},
				{ sortable: false,"class": "index",targets: [0,1]},
				{
				aTargets: [3,4,5,6],
					fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
						if ( sData < "0" ) {
				              $(nTd).css('color', 'red')
				              $(nTd).css('font-weight', 'bold')
						}
					}
				}                 
			], 
			order: [[ 1, 'asc' ]],
			aoColumns: [
				{
					"model": "cliente_local",
					sDefaultContent: ""
				},
				{
					"model": "dias_procesados",
					sDefaultContent: ""
				},
				{
					"model": "canal_de_venta",
					sDefaultContent: ""
				},          
				{
					"model": "apostado",
					sDefaultContent: "0",
					className: ""     
				},      
				{
					"model": "ganado",
					sDefaultContent: "0",
					className: ""       
				},
				{
					"model": "pagado",
					sDefaultContent: "0"
				},
				{
					"model": "produccion",
					sDefaultContent: "0"
				}
			], 
			pageLength: '7',
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
	}
	if(modelo==3){ 	
		table_dt = $('#tabla_sec_recaudacion').DataTable({ 
			responsive:false,
			fixedHeader: {
				header: true
			},
			dom: 'Blftip',
			buttons: [
			    { 
			        extend: 'copy',
			        text:'Copiar',
			        className: 'copiarButton'
			     },
			    { 
			        extend: 'csv',
			        text:'CSV',
			        className: 'csvButton' 
			    },
			    {   extend: 'excel',
			        text:'Excel',
			        className: 'excelButton' 
			    }, 
			    /*                   
			    {
			        text: 'Email',
			        className:'emailButton',
			        action: function ( e, dt, node, config ) {
			            //$('#modal_sec_recaudacion_modelo_uno').modal('toggle');
			        }
			    },
			    */
			    {
			        extend: 'colvis',
			        text:'Visibilidad',
			        className:'visibilidadButton',
			        postfixButtons: [ 'colvisRestore' ]
			    }
			],
			fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
				if(aData[0] && aData[1]){
					$('td', nRow).css('background-color','#337ab7');
				}
				if ( aData[2] == "Total" )
				{
					$('td', nRow).css('background-color','#9BDFFD','important');
					$('td', nRow).css('z-index: ','10');  
					$('td', nRow).css('color','#080FFC'); 
					$('td', nRow).css('font-weight','800'); 
					$('td', nRow).css('z-index: ','10');                       
				}
				else if ( aData[2] != "Total" )
				{
					$('td', nRow).css('background-color', '#fafafa','important');
				}
			},
			footerCallback: function () {
				/*
				var api = this.api(),
				columns = [4,5,6,7]; // Add columns here
				for (var i = 0; i < columns.length; i++) {
				    var total =  api.column(columns[i], {filter: 'applied'}).data().sum().toFixed(2);
				    var total_pagina = api.column(columns[i], { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
				    if (total<0 && total_pagina<0){
				        $('tfoot th').eq(columns[i]).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
				        $('tfoot th').eq(columns[i]).append('Pagina:<br><span style="color:red; font-weight: bold; font-size: 11px !important;"> ' + formatonumeros(total_pagina)+'<span>');
				    }
				    else{
				        $('tfoot th').eq(columns[i]).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
				        $('tfoot th').eq(columns[i]).append('Pagina:<br><span style="color:green; font-weight: bold; font-size: 11px !important;"> ' + formatonumeros(total_pagina)+'<span>');                  
				    }
				}
				*/
			},      
			bRetrieve: true,
			sPaginationType: "full_numbers",
			lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],  
			pageLength: 10,          
			paging: true,
			searching: true,
			lengthChange: true,
			ordering: true,      
			bProcessing: true,
			bAutoWidth: true,
			bStateSave: true,
			sScrollY: heighttable, 
			sScrollX: "100%", 
			sScrollXInner: "100%", 
			bScrollCollapse: true,       
			bPaginate: true, 
			bFilter: true,
			Sorting: [[1, 'asc']], 
			bSort: false,
			rowsGroup: [0],
			data:model,
			columnDefs:[
				{ className: "columnaprincipal_modelo_tres", "targets": [0] }, 
				{ className: "columna_dias_modelo_tres", "targets": [1] },
				{ className: "columna_canal_de_venta_modelo_tres", "targets": [2] },				
				{ className: "apostado_modelo_tres", "targets": [3] }, 
				{ className: "ganado_modelo_tres", "targets": [4] },
				{ className: "pagado_modelo_tres", "targets": [5] },
				{ className: "produccion_modelo_tres", "targets": [6] }, 
				{ className: "porcentaje_modelo_tres", "targets": [7] },
				{ className: "cliente_modelo_tres", "targets": [8] },
				{ className: "freegames_modelo_tres", "targets": [9] },
				{ sortable: false,"class": "index",targets: [0,1]},
				{ targets: -1,visible: false },
				{
				aTargets: [3,4,5,6],
					fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
						 if ( sData < "0" ) {
			                  $(nTd).css('color', 'red')
			                  $(nTd).css('font-weight', 'bold')
						}
					}
				}                                                                                    
			], 
			order: [[ 1, 'asc' ]],
			aoColumns: [
				{
					"model": "cliente_local",
					sDefaultContent: ""
				},
				{
					"model": "dias_procesados",
					sDefaultContent: ""
				},
				{
					"model": "canal_de_venta",
					sDefaultContent: ""
				},          
				{
					"model": "apostado",
					sDefaultContent: "0",
					className: ""     
				},      
				{
					"model": "ganado",
					sDefaultContent: "0",
					className: ""       
				},
				{
					"model": "pagado",
					sDefaultContent: "0"
				},
				{
					"model": "produccion",
					sDefaultContent: "0"
				},
				{
					"model": "porcentaje",
					sDefaultContent: "0"
				},
				{
					"model": "cliente",
					sDefaultContent: "0"
				},
				{
					"model": "freegames",
					sDefaultContent: "0"
				}                              
			], 
			pageLength: '10',
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
	}
	if(modelo==4){ 
	    table_dt = $('#tabla_sec_recaudacion').DataTable({ 
	      responsive: false, 
	      fixedHeader: {
	        header: true,
	        footer:false
	      },
	      fixedColumns:{
	        leftColumns: 3
	      }, 
	      bRetrieve: true,
	      lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],
	      iDisplayLength: 10,
	      bInfo :true,
		  iDisplayLength: 10,	      
	      lengthChange: true,
	      searching: true,
	      ordering: true,      
	      bProcessing:false,
          bAutoWidth: false,
	      bStateSave: false,
	      sScrollY: false, 
	      sScrollX: "100%", 
	      sScrollXInner: "100%",      
	      bScrollCollapse: true, 
	      sPaginationType: "full_numbers",
	      bStateSave: false, 
	      bFilter: true,
	      Sorting: [[1, 'asc']], 
	      rowsGroup: [0,1],
	      bSort: true,
	      data:model,
	      dom: 'Blftip',
          buttons: [
                { 
                    extend: 'copy',
                    text:'Copiar',
                    className: 'copiarButton'
                 },
                { 
                    extend: 'csv',
                    text:'CSV',
                    className: 'csvButton' 
                },
                {   extend: 'excel',
                    text:'Excel',
                    className: 'excelButton' 
                }, 
                /*                   
                {
                    text: 'Email',
                    className:'emailButton',
                    action: function ( e, dt, node, config ) {
                        //$('#modal_sec_recaudacion_modelo_uno').modal('toggle');
                    }
                },
                */
                {
                    extend: 'colvis',
                    text:'Visibilidad',
                    className:'visibilidadButton',
                    postfixButtons: [ 'colvisRestore' ]
                }
          ],
	      fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
		        if(aData[0] && aData[1]){
		            $('td', nRow).css('background-color','#337ab7');
		        }
		        if ( aData[2] == "Total" )
		        {
		            $('td', nRow).css('background-color','#9BDFFD','important');
		            $('td', nRow).css('color','#080FFC'); 
		            $('td', nRow).css('font-weight','800');                       
		        }
		        else if ( aData[2] != "Total" )
		        {
		           $('td', nRow).css('background-color', '#fafafa','important');
		        }
	      },
          columnDefs: [
		        { className: "colunmasControl columna_nombre_local_modelo_cuatro","targets": [0] },
		        { className: "columnaprincipal columna_dias_modelo_cuatro_body_td", "targets": [1] }, 
		        { className: "canal_de_venta_modelo_cuatro", "targets": [2] }, 
	        	{ className: "apostado_modelo_cuatro", "targets": [5] },
		        { className: "ganado_modelo_cuatro", "targets": [6] },
		        { className: "pagado_modelo_cuatro", "targets": [7] },
		        { className: "produccion_modelo_cuatro", "targets": [8] },        
		        { className: "columnasnumeros_body_td","targets": [3,4,5,6,7,8,9,10,11,12,13,14,15,16,17]},
		        { sortable: false,"class": "index",targets: [0]},
		        { sortable: true, "targets": [0] },
		        {
		          aTargets: [3,4,5,6,7,8,9,10,11,12,13,14,15,16,17],
			          fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
			             if ( sData < "0" ) {
	                          $(nTd).css('color', 'red')
	                          $(nTd).css('font-weight', 'bold')
			            }
		          }
		        }         
	      ], 
	      aoColumns: [
	          {
	            "model": "data_nombre_local",
	            sDefaultContent: "0"
	          },     
	          {
	            "model": "data_dias_procesados",
	            sDefaultContent: "0"
	          },               
	          {
	            "model": "data_canales_de_venta",
	            sDefaultContent: "0",
	            className: ""     
	          },      
	          {
	            "model": "data_total_depositado",
	            sDefaultContent: "0",
	            className: ""       
	          },
	          {
	            "model": "data_total_anulado_retirado",
	            sDefaultContent: "0"
	          },
	          {
	            "model": "data_total_apostado",
	            sDefaultContent: "0"
	          },      
	          {
	            "model": "data_total_ganado",
	            sDefaultContent: "0",
	            className: ""     
	          },      
	          {
	            "model": "data_total_pagado",
	            sDefaultContent: "0",
	            className: ""       
	          },
	          {
	            "model": "data_total_produccion",
	            sDefaultContent: "0",
	            className: ""       
	          }, 
	          {
	            "model": "data_total_depositado_web",
	            sDefaultContent: "0"

	          },
	          {
	            "model": "data_total_retirado_web",
	            sDefaultContent: "0"
	          },      
	          {
	            "model": "data_total_caja_web",
	            sDefaultContent: "0",
	            className: ""     
	          },      
	          {
	            "model": "data_total_cliente",
	            sDefaultContent: "0",
	            className: ""       
	          },
	          {
	            "model": "data_total_freegames",
	            sDefaultContent: "0",
	            className: ""       
	          },
	          {
	            "model": "data_pagado_en_otra_tienda",
	            sDefaultContent: "0"

	          },
	          {
	            "model": "data_pagado_de_otra_tienda",
	            sDefaultContent: "0"
	          },      
	          {
	            "model": "data_total_pagos_fisicos",
	            sDefaultContent: "0",
	            className: ""     
	          },      
	          {
	            "model": "data_caja_fisico",
	            sDefaultContent: "0",
	            className: ""       
	          }
	         ], 
	        pageLength: '18',
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
	}
	if(modelo==5){
		table_dt = $('#tabla_sec_recaudacion').DataTable({ 
			responsive:false,
			fixedHeader: {
				header: true
			},
			dom: 'Blftip',
			buttons: [
			    { 
			        extend: 'copy',
			        text:'Copiar',
			        className: 'copiarButton'
			     },
			    { 
			        extend: 'csv',
			        text:'CSV',
			        className: 'csvButton' 
			    },
			    {   extend: 'excel',
			        text:'Excel',
			        className: 'excelButton' 
			    }, 
			    /*                   
			    {
			        text: 'Email',
			        className:'emailButton',
			        action: function ( e, dt, node, config ) {
			            //$('#modal_sec_recaudacion_modelo_uno').modal('toggle');
			        }
			    },
			    */
			    {
			        extend: 'colvis',
			        text:'Visibilidad',
			        className:'visibilidadButton',
			        postfixButtons: [ 'colvisRestore' ]
			    }
			],
			fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
				if(aData[0] && aData[1]){
					$('td', nRow).css('background-color','#337ab7');
				}
				if ( aData[2] == "Total" )
				{
					$('td', nRow).css('background-color','#9BDFFD','important');
					$('td', nRow).css('z-index: ','10');  
					$('td', nRow).css('color','#080FFC'); 
					$('td', nRow).css('font-weight','800'); 
					$('td', nRow).css('z-index: ','10');                       
				}
				else if ( aData[2] != "Total" )
				{
					$('td', nRow).css('background-color', '#fafafa','important');
				}
			},
			footerCallback: function () {
				/*
				var api = this.api(),
				columns = [4,5,6,7]; // Add columns here
				for (var i = 0; i < columns.length; i++) {
				    var total =  api.column(columns[i], {filter: 'applied'}).data().sum().toFixed(2);
				    var total_pagina = api.column(columns[i], { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
				    if (total<0 && total_pagina<0){
				        $('tfoot th').eq(columns[i]).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
				        $('tfoot th').eq(columns[i]).append('Pagina:<br><span style="color:red; font-weight: bold; font-size: 11px !important;"> ' + formatonumeros(total_pagina)+'<span>');
				    }
				    else{
				        $('tfoot th').eq(columns[i]).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
				        $('tfoot th').eq(columns[i]).append('Pagina:<br><span style="color:green; font-weight: bold; font-size: 11px !important;"> ' + formatonumeros(total_pagina)+'<span>');                  
				    }
				}
				*/
			},      
			bRetrieve: true,
			sPaginationType: "full_numbers",
			lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],  
			pageLength: 10,          
			paging: true,
			searching: true,
			lengthChange: true,
			ordering: true,      
			bProcessing: true,
			bAutoWidth: true,
			bStateSave: true,
			sScrollY: heighttable, 
			sScrollX: "100%", 
			sScrollXInner: "100%", 
			bScrollCollapse: true,       
			bPaginate: true, 
			bFilter: true,
			Sorting: [[1, 'asc']], 
			bSort: false,
			rowsGroup: [0],
			data:model,
			columnDefs:[
				{ className: "colunmas_prueba_uno_modelo_cinco", "targets": [0] }, 
				{ className: "colunmas_prueba_dos_modelo_cinco", "targets": [1] },
				{ className: "colunmas_prueba_tres_modelo_cinco", "targets": [2] },				
				{ className: "colunmas_prueba_cuatro_modelo_cinco", "targets": [3] }, 
				{ className: "colunmas_prueba_cinco_modelo_cinco", "targets": [4] },
				{ className: "colunmas_prueba_seis_modelo_cinco", "targets": [5] },
				{ className: "colunmas_prueba_siete_modelo_cinco", "targets": [6] }, 
				{ className: "colunmas_prueba_ocho_modelo_cinco", "targets": [7] },
				{ className: "colunmas_prueba_nueve_modelo_cinco", "targets": [8] },
				{ className: "colunmas_prueba_diez_modelo_cinco", "targets": [9] },
				{ sortable: false,"class": "index",targets: [0,1]},
				{ targets: -1,visible: false },
				{
				aTargets: [3,4,5,6],
					fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
						 if ( sData < "0" ) {
			                  $(nTd).css('color', 'red')
			                  $(nTd).css('font-weight', 'bold')
						}
					}
				}                                                                                    
			], 
			order: [[ 1, 'asc' ]],
			aoColumns: [
				{
					"model": "nombre_prueba_uno_modelo_cinco",
					sDefaultContent: ""
				},
				{
					"model": "nombre_prueba_dos_modelo_cinco",
					sDefaultContent: ""
				},
				{
					"model": "nombre_prueba_tres_modelo_cinco",
					sDefaultContent: ""
				},          
				{
					"model": "nombre_prueba_cuatro_modelo_cinco",
					sDefaultContent: "0",
					className: ""     
				},      
				{
					"model": "nombre_prueba_cinco_modelo_cinco",
					sDefaultContent: "0",
					className: ""       
				},
				{
					"model": "nombre_prueba_seis_modelo_cinco",
					sDefaultContent: "0"
				},
				{
					"model": "nombre_prueba_siete_modelo_cinco",
					sDefaultContent: "0"
				},
				{
					"model": "nombre_prueba_ocho_modelo_cinco",
					sDefaultContent: "0"
				},
				{
					"model": "nombre_prueba_nueve_modelo_cinco",
					sDefaultContent: "0"
				},
				{
					"model": "nombre_prueba_diez_modelo_cinco",
					sDefaultContent: "0"
				}                              
			], 
			pageLength: '10',
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
	}
}
function sec_recaudacion_process_data(obj){
	if (modelo==1) {
		var datafinal=[];
		$.each(obj.data.locales, function(data_index, data_local) {
			console.log(obj);
			var new_data=[];
			//new_data[0]=(data_index+1);
			new_data[0]=data_local.local_nombre;
			new_data[1]=data_local.dias_procesados;
			var test_number = 0;
			$.each(canales_de_venta, function(cdv_id, cdv_val) {
				var cdv_data = [];
				cdv_data[0]="total_apostado";
				cdv_data[1]="total_ganado";
				cdv_data[2]="total_pagado";
				cdv_data[3]="total_produccion";
				if(data_local.liquidaciones.total_rango_fecha[cdv_id]!=null){
					$.each(cdv_data, function(cdv_data_index, cdv_data_val) {
						data_local.liquidaciones.total_rango_fecha[cdv_id][cdv_data_val] = formatonumeros(data_local.liquidaciones.total_rango_fecha[cdv_id][cdv_data_val]);
						new_data.push(data_local.liquidaciones.total_rango_fecha[cdv_id][cdv_data_val]);
					});
				}else{
					$.each(cdv_data, function(cdv_data_index, cdv_data_val) {
						new_data.push("0");
					});
				}
			});
			datafinal[data_index]=new_data;
		});

		sec_recaudacion_mostrar_datatable(datafinal);
	}
	if (modelo==2) {
		var datafinal=[];
		var i = 0;
		$.each(obj, function(index, val) {
			$.each(val.locales, function(index1, val1) {
				$.each(val1.liquidaciones, function(index2, val2) {
					$.each(val2, function(index3, val3) {
						var data_nombre_local=val1.local_nombre;
						var data_dias_procesados = val1.dias_procesados;
						var data_canales_de_venta = canales_de_venta[index3];
						var data_total_apostado = formatonumeros(val3.total_apostado);
						var data_total_ganado = formatonumeros(val3.total_ganado);
						var data_total_pagado = formatonumeros(val3.total_pagado);
						var data_total_produccion = formatonumeros(val3.total_produccion);
						var newObject =[data_nombre_local,data_dias_procesados,data_canales_de_venta,data_total_apostado,data_total_ganado,data_total_pagado,data_total_produccion];
						datafinal[i] =  newObject;
						i++;
					});
				});
			});
		});
		$.each(obj.data.totales, function(indgeneral, valgeneral) {
				var data_caja_fisico =formatonumeros(valgeneral.caja_fisico);
				var data_num_tickets =	formatonumeros(valgeneral.num_tickets);
				var data_pagado_de_otra_tienda =	formatonumeros(valgeneral.pagado_de_otra_tienda);
				var data_pagado_en_otra_tienda =	formatonumeros(valgeneral.pagado_en_otra_tienda);
				var data_retirado_de_otras_tiendas=	formatonumeros(valgeneral.retirado_de_otras_tiendas);
				var data_total_anulado_retirado	=formatonumeros(valgeneral.total_anulado_retirado);
				var data_total_apostado	=formatonumeros(valgeneral.total_apostado);
				var data_total_caja_web	=formatonumeros(valgeneral.total_caja_web);
				var data_total_cliente	=formatonumeros(valgeneral.total_cliente);
				var data_total_depositado	=formatonumeros(valgeneral.total_depositado);
				var data_total_depositado_web	=formatonumeros(valgeneral.total_depositado_web);
				var data_total_freegames	=formatonumeros(valgeneral.total_freegames);
				var data_total_ganado	=formatonumeros(valgeneral.total_ganado);
				var data_total_ingresado	=formatonumeros(valgeneral.total_ingresado);
				var data_total_pagado	=formatonumeros(valgeneral.total_pagado);
				var data_total_pagos_fisicos	=formatonumeros(valgeneral.total_pagos_fisicos);
				var data_total_produccion	=formatonumeros(valgeneral.total_produccion);
				var data_total_retirado_web	=formatonumeros(valgeneral.total_retirado_web);
				$('tfoot').html("<tr><td class='ftm4 tdft'>TOTAL:</td><td class='tdft' style='border-bottom:1px solid #ddd !important; border-top:1px solid #ddd !important;'></td><td class='tdft'></td><td class='tdft'><span class='etotl'>T. Apostado</span><br><span class='etotv'>"+formatonumeros(data_total_apostado)+"</span></td><td class='tdft'><span class='etotl'>T. Ganado</span><br><span class='etotv'>"+formatonumeros(data_total_ganado)+"</span></td><td class='tdft'><span class='etotl'>T. Pagado</span><br><span class='etotv'>"+formatonumeros(data_total_pagado)+"</span></td><td class='tdft'><span class='etotl'>T. Producción</span><br><span class='etotv'>"+formatonumeros(data_total_produccion)+"</span></td></tr>");												
    	});

		sec_recaudacion_mostrar_datatable(datafinal);
	}
	if (modelo==3) {
		var datafinal=[];
		var i = 0;
		$.each(obj, function(index, val) {
			$.each(val.locales, function(index1, val1) {
				$.each(val1.liquidaciones, function(index2, val2) {
					$.each(val2, function(index3, val3) {
						var data_nombre_local= val1.local_nombre;
						var data_dias_procesados = val1.dias_procesados;
						var data_canales_de_venta = canales_de_venta[index3];
						var data_total_apostado = formatonumeros(val3.total_apostado);
						var data_total_ganado = formatonumeros(val3.total_ganado);
						var data_total_pagado = formatonumeros(val3.total_pagado);
						var data_total_produccion = formatonumeros(val3.total_produccion);
						var data_total_porcentaje ="";
						var data_total_cliente ="";
						var data_total_freegames = "";
						var newObject =[data_nombre_local,data_dias_procesados,data_canales_de_venta,data_total_apostado,data_total_ganado,data_total_pagado,data_total_produccion,data_total_porcentaje,data_total_cliente,data_total_freegames];
						datafinal[i] =  newObject;
						i++;
					});
				});
			});
		});
		$.each(obj.data.totales, function(indgeneral, valgeneral) {
				var data_caja_fisico =formatonumeros(valgeneral.caja_fisico);
				var data_num_tickets =	formatonumeros(valgeneral.num_tickets);
				var data_pagado_de_otra_tienda =	formatonumeros(valgeneral.pagado_de_otra_tienda);
				var data_pagado_en_otra_tienda =	formatonumeros(valgeneral.pagado_en_otra_tienda);
				var data_retirado_de_otras_tiendas=	formatonumeros(valgeneral.retirado_de_otras_tiendas);
				var data_total_anulado_retirado	=formatonumeros(valgeneral.total_anulado_retirado);
				var data_total_apostado	=formatonumeros(valgeneral.total_apostado);
				var data_total_caja_web	=formatonumeros(valgeneral.total_caja_web);
				var data_total_cliente	=formatonumeros(valgeneral.total_cliente);
				var data_total_depositado	=formatonumeros(valgeneral.total_depositado);
				var data_total_depositado_web	=formatonumeros(valgeneral.total_depositado_web);
				var data_total_freegames	=formatonumeros(valgeneral.total_freegames);
				var data_total_ganado	=formatonumeros(valgeneral.total_ganado);
				var data_total_ingresado	=formatonumeros(valgeneral.total_ingresado);
				var data_total_pagado	=formatonumeros(valgeneral.total_pagado);
				var data_total_pagos_fisicos	=formatonumeros(valgeneral.total_pagos_fisicos);
				var data_total_produccion	=formatonumeros(valgeneral.total_produccion);
				var data_total_retirado_web	=formatonumeros(valgeneral.total_retirado_web);
				$('tfoot').html("<tr><td class='ftm4 tdft colunmasControl columna_nombre_local_modelo_cuatro sorting_1'>TOTAL:</td><td class='tdft columna_dias_modelo_cuatro_body_td sorting_2'></td><td class='tdft  canal_de_venta_modelo_cuatro'></td><td class='tdft'><span class='etotl'>T. Apostado</span><br><span class='etotv'>"+formatonumeros(data_total_apostado)+"</span></td><td class='tdft'><span class='etotl'>T. Ganado</span><br><span class='etotv'>"+formatonumeros(data_total_ganado)+"</span></td><td class='tdft'><span class='etotl'>T. Pagado</span><br><span class='etotv'>"+formatonumeros(data_total_pagado)+"</span></td><td class='tdft'><span class='etotl'>T. Producción</span><br><span class='etotv'>"+formatonumeros(data_total_produccion)+"</span></td><td class='tdft' style='border-bottom:1px solid #ddd !important;'></td><td class='tdft' style='border-bottom:1px solid #ddd !important;'></td></tr>");		});
		sec_recaudacion_mostrar_datatable(datafinal);
	}
	if (modelo==4) {
		var datafinal=[];
		var i = 0;
		$.each(obj, function(index, val) {
			$.each(val.locales, function(index1, val1) {
				$.each(val1.liquidaciones, function(index2, val2) {
					$.each(val2, function(index3, val3) {
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
						var data_total_cliente = formatonumeros(val3.total_cliente);
						var data_total_freegames = formatonumeros(val3.total_freegames);
						var data_pagado_en_otra_tienda = formatonumeros(val3.pagado_en_otra_tienda);
						var data_pagado_de_otra_tienda = formatonumeros(val3.pagado_de_otra_tienda);
						var data_total_pagos_fisicos = formatonumeros(val3.total_pagos_fisicos);
						var data_caja_fisico = formatonumeros(val3.caja_fisico);
						var newObject =[data_nombre_local,data_dias_procesados,data_canales_de_venta,data_total_depositado,data_total_anulado_retirado,data_total_apostado,data_total_ganado,data_total_pagado,data_total_produccion,data_total_depositado_web,data_total_retirado_web,data_total_caja_web,data_total_cliente,data_total_freegames,data_pagado_en_otra_tienda,data_pagado_de_otra_tienda,data_total_pagos_fisicos,data_caja_fisico];
						datafinal[i] =  newObject;
						i++;
					});
				});
			});
		});
		$.each(obj.data.totales, function(indgeneral, valgeneral) {
				var data_caja_fisico =formatonumeros(valgeneral.caja_fisico);
				var data_num_tickets =	formatonumeros(valgeneral.num_tickets);
				var data_pagado_de_otra_tienda =	formatonumeros(valgeneral.pagado_de_otra_tienda);
				var data_pagado_en_otra_tienda =	formatonumeros(valgeneral.pagado_en_otra_tienda);
				var data_retirado_de_otras_tiendas=	formatonumeros(valgeneral.retirado_de_otras_tiendas);
				var data_total_anulado_retirado	=formatonumeros(valgeneral.total_anulado_retirado);
				var data_total_apostado	=formatonumeros(valgeneral.total_apostado);
				var data_total_caja_web	=formatonumeros(valgeneral.total_caja_web);
				var data_total_cliente	=formatonumeros(valgeneral.total_cliente);
				var data_total_depositado	=formatonumeros(valgeneral.total_depositado);
				var data_total_depositado_web	=formatonumeros(valgeneral.total_depositado_web);
				var data_total_freegames	=formatonumeros(valgeneral.total_freegames);
				var data_total_ganado	=formatonumeros(valgeneral.total_ganado);
				var data_total_ingresado	=formatonumeros(valgeneral.total_ingresado);
				var data_total_pagado	=formatonumeros(valgeneral.total_pagado);
				var data_total_pagos_fisicos	=formatonumeros(valgeneral.total_pagos_fisicos);
				var data_total_produccion	=formatonumeros(valgeneral.total_produccion);
				var data_total_retirado_web	=formatonumeros(valgeneral.total_retirado_web);
				$('tfoot').html("<tr><td class='colunmasControl columna_nombre_local_modelo_cuatro'><span>TOTAL:</span><span style='visibility:hidden;'>12345674545664564456456456489</span></td><td class='columnaprincipal columna_dias_modelo_cuatro_body_td' style='color: #337ab7 !important; background-color:#337ab7 !important; border-bottom:1px solid #ddd !important;'><span>4654567548</span></td><td class='tdft canal_de_venta_modelo_cuatro'><span><span style='visibility:hidden;'>345345345344451254444</span></span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Depositado:</span><br><span class='etotv'>"+data_total_depositado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. A. Retirado</span><br><span class='etotv'>"+data_total_anulado_retirado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T Apostado</span><br><span class='etotv'>"+data_total_apostado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Ganado</span><br><span class='etotv'>"+data_total_ganado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Pagado</span><br><span class='etotv'>"+data_total_pagado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Producción</span><br><span class='etotv'>"+data_total_produccion+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Dep. Web</span><br><span class='etotv'>"+data_total_depositado_web+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Ret. Web</span><br><span class='etotv'>"+data_total_retirado_web+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Caja Web</span><br><span class='etotv'>"+data_total_caja_web+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Cliente</span><br><span class='etotv'>"+data_total_cliente+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Freegames</span><br><span class='etotv'>"+data_total_freegames+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. P. en Otra Tienda</span><br><span class='etotv'>"+data_pagado_en_otra_tienda+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. P. de Otra Tienda</span><br><span class='etotv'>"+data_pagado_de_otra_tienda+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. P. Fisicos</span><br><span class='etotv'>"+data_total_pagos_fisicos+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Caja Fisico</span><br><span class='etotv'>"+data_caja_fisico+"</span></td></tr>");								
		});
		sec_recaudacion_mostrar_datatable(datafinal);
	}
	if (modelo==5) {
		var datafinal=[];
		var i = 0;
		$.each(obj, function(index, val) {
			$.each(val.locales, function(index1, val1) {
				$.each(val1.liquidaciones, function(index2, val2) {
					$.each(val2, function(index3, val3) {
						var data_nombre_local= val1.local_nombre;
						var data_dias_procesados = val1.dias_procesados;
						var data_canales_de_venta = canales_de_venta[index3];
						var data_total_apostado = formatonumeros(val3.total_apostado);
						var data_total_ganado = formatonumeros(val3.total_ganado);
						var data_total_pagado = formatonumeros(val3.total_pagado);
						var data_total_produccion = formatonumeros(val3.total_produccion);
						var data_total_porcentaje ="";
						var data_total_cliente ="";
						var data_total_freegames = "";
						var newObject =[data_nombre_local,data_dias_procesados,data_canales_de_venta,data_total_apostado,data_total_ganado,data_total_pagado,data_total_produccion,data_total_porcentaje,data_total_cliente,data_total_freegames];
						datafinal[i] =  newObject;
						i++;
					});
				});
			});
		});
		$.each(obj.data.totales, function(indgeneral, valgeneral) {
				var data_caja_fisico =formatonumeros(valgeneral.caja_fisico);
				var data_num_tickets =	formatonumeros(valgeneral.num_tickets);
				var data_pagado_de_otra_tienda =	formatonumeros(valgeneral.pagado_de_otra_tienda);
				var data_pagado_en_otra_tienda =	formatonumeros(valgeneral.pagado_en_otra_tienda);
				var data_retirado_de_otras_tiendas=	formatonumeros(valgeneral.retirado_de_otras_tiendas);
				var data_total_anulado_retirado	=formatonumeros(valgeneral.total_anulado_retirado);
				var data_total_apostado	=formatonumeros(valgeneral.total_apostado);
				var data_total_caja_web	=formatonumeros(valgeneral.total_caja_web);
				var data_total_cliente	=formatonumeros(valgeneral.total_cliente);
				var data_total_depositado	=formatonumeros(valgeneral.total_depositado);
				var data_total_depositado_web	=formatonumeros(valgeneral.total_depositado_web);
				var data_total_freegames	=formatonumeros(valgeneral.total_freegames);
				var data_total_ganado	=formatonumeros(valgeneral.total_ganado);
				var data_total_ingresado	=formatonumeros(valgeneral.total_ingresado);
				var data_total_pagado	=formatonumeros(valgeneral.total_pagado);
				var data_total_pagos_fisicos	=formatonumeros(valgeneral.total_pagos_fisicos);
				var data_total_produccion	=formatonumeros(valgeneral.total_produccion);
				var data_total_retirado_web	=formatonumeros(valgeneral.total_retirado_web);
				$('tfoot').html("<tr><td class='ftm4 tdft colunmasControl columna_nombre_local_modelo_cuatro sorting_1'>TOTAL:</td><td class='tdft columna_dias_modelo_cuatro_body_td sorting_2'></td><td class='tdft  canal_de_venta_modelo_cuatro'></td><td class='tdft'><span class='etotl'>T. Apostado</span><br><span class='etotv'>"+formatonumeros(data_total_apostado)+"</span></td><td class='tdft'><span class='etotl'>T. Ganado</span><br><span class='etotv'>"+formatonumeros(data_total_ganado)+"</span></td><td class='tdft'><span class='etotl'>T. Pagado</span><br><span class='etotv'>"+formatonumeros(data_total_pagado)+"</span></td><td class='tdft'><span class='etotl'>T. Producción</span><br><span class='etotv'>"+formatonumeros(data_total_produccion)+"</span></td><td class='tdft'></td><td class='tdft'></td></tr>");
		});
		sec_recaudacion_mostrar_datatable(datafinal);		
	}
}
function sec_recaudacion_get_liquidaciones(){
	modelo = $(".sec_recaudaciones_tipo_de_modelo").val();
	/*	
	if ($.fn.DataTable.isDataTable('#tabla_sec_recaudacion')){
		var info = $('#tabla_sec_recaudacion').DataTable().page.info();
			get_liquidaciones_data.pagina=info.page;
			get_liquidaciones_data.numero=info.length; 
			//console.log(info);
	}else{
			get_liquidaciones_data.pagina=dt_pagina;
			get_liquidaciones_data.numero=dt_numero; 
			//console.log(info);		
	}
	*/	
	get_liquidaciones_data.filtro={};
	get_liquidaciones_data.filtro.fecha_inicio=$(".fecha_inicio_enviar").val();
	get_liquidaciones_data.filtro.hora_inicio=$(".hora_inicio_enviar").val();
	get_liquidaciones_data.filtro.fecha_fin=$(".fecha_fin_enviar").val();
	get_liquidaciones_data.filtro.hora_fin=$(".hora_fin_enviar").val();
	get_liquidaciones_data.filtro.locales = $(".local").val();
	get_liquidaciones_data.filtro.canales_de_venta=$('.canalventarecaudacion').val();
	get_liquidaciones_data.where="liquidaciones";
	localStorage.setItem('at_liquidaciones_filtro_fecha_inicio', get_liquidaciones_data.filtro.fecha_inicio);
	localStorage.setItem('at_liquidaciones_filtro_fecha_fin', get_liquidaciones_data.filtro.fecha_fin);
	localStorage.setItem('at_liquidaciones_filtro_locales', get_liquidaciones_data.filtro.locales);
	localStorage.setItem('at_liquidaciones_filtro_canales_de_venta', get_liquidaciones_data.filtro.canalesventa);
	$.ajax({
		data: get_liquidaciones_data,
		type: "POST",
		url: "/api/?json",
	})
	.done(function(responsedata, textStatus, jqXHR ) {
		var obj = jQuery.parseJSON(responsedata);
		sec_recaudacion_process_data(obj);
	})
	.fail(function( jqXHR, textStatus, errorThrown ) {
		if ( console && console.log ) {
			console.log( "La solicitud liquidaciones a fallado: " +  textStatus);
		}
	});
}
function sec_send_data_json(){
	var finicio = $(".fecha_inicio_enviar").val();
	var hinicio = $(".hora_inicio_enviar").val();
	var ffinal = $(".fecha_final_enviar").val();
	var hfinal = $(".hora_final_enviar").val();
	var data = {};
	data.url = window.location.href;
	data.filtro={};
	data.filtro.fecha_inicio = finicio;
	data.filtro.fecha_fin = ffinal;
	data.filtro.hora_inicio = hinicio;
	data.filtro.hora_final = hfinal;
	data.filtro.locales = {};
	data.filtro.locales[0]=104;
	data.filtro.locales[1]=77;
	data.filtro.locales[2]=162;
	data.filtro.canales_de_venta = {};
	data.filtro.canales_de_venta[0]=16;
	data.filtro.canales_de_venta[1]=17;
        $.ajax({
            data: data,
            type: "POST",
            dataType: "json",
            url: "http://192.168.0.8/at_liquidaciones_v2/sys/request_data.php?json",
        })
         .done(function( data, textStatus, jqXHR ) {
             if ( console && console.log ) {
                 console.log( "La solicitud se ha completado correctamente." );
                 console.log(data);
             }
         })
         .fail(function( jqXHR, textStatus, errorThrown ) {
             if ( console && console.log ) {
                 console.log( "La solicitud a fallado: " +  textStatus);
             }
        })
}
function sec_recaudacion() {
	if(sec_id=="recaudacion"){
		//console.log("sec:recaudacion");
		sec_recaudacion_events();
		sec_recaudacion_settings();
		if(sub_sec_id=="liquidaciones"){
			sec_recaudacion_get_moneda();
			sec_recaudacion_get_locales();
			sec_recaudacion_get_canales_venta();  
			sec_recaudacion_get_liquidaciones();
		}
	    /*      
	    $(function(){
	    $("body").on("sequence", function(){

	    $("body").trigger("sequence");
	    });    
	    */

		setTimeout(function () {
			//m_reload();
		}, 3000);
	}
}
function sec_recaudacion_settings(){

	$('.local').select2({
		closeOnSelect: false,            
		allowClear: true,
	});
	$('.canalventarecaudacion').select2({
		closeOnSelect: false,            
		allowClear: true,
	}); 
	if(localStorage.getItem("at_liquidaciones_filtro_fecha_inicio")){
		at_liquidaciones_filtro_fecha_inicio = localStorage.getItem("at_liquidaciones_filtro_fecha_inicio");
	}
	if(localStorage.getItem("at_liquidaciones_filtro_fecha_fin")){
		at_liquidaciones_filtro_fecha_fin = localStorage.getItem("at_liquidaciones_filtro_fecha_fin");
	}
	if(localStorage.getItem("at_liquidaciones_filtro_locales")){
		at_liquidaciones_filtro_locales = localStorage.getItem("at_liquidaciones_filtro_locales");
	}
	if(localStorage.getItem("at_liquidaciones_filtro_canales_de_venta")){
		at_liquidaciones_filtro_canales_de_venta = localStorage.getItem("at_liquidaciones_filtro_canales_de_venta");
	}

	//console.log(at_liquidaciones_filtro_locales);
	//console.log(at_liquidaciones_filtro_canales_de_venta);


	$(".filtro_datepicker")
		.datepicker({
			dateFormat:'dd-mm-yy'
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
		});

	var m_liq_filtro_inicio_fecha = moment(at_liquidaciones_filtro_fecha_inicio).format("DD-MM-YYYY");
	$("#input_text-liq_filtro_inicio_fecha")
		.datepicker("setDate", m_liq_filtro_inicio_fecha)
		.trigger('change');

	var m_liq_filtro_fin_fecha = moment(at_liquidaciones_filtro_fecha_fin).format("DD-MM-YYYY");
	$("#input_text-liq_filtro_fin_fecha")
		.datepicker("setDate", m_liq_filtro_fin_fecha)
		.trigger('change');



	/*$('#liquidaciones_filtro_form .datepicker')
		.datepicker({
			format: 'dd-mm-yyyy',
			autoclose:true
		}).on('show', function(ev){

		}).on('changeDate', function(ev){
			$(this).datepicker('hide');
			var newDate = new Date(ev.date);
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
		});*/
	/*$("#liquidaciones_filtro_form .timepicker")
		.timepicker({
			"showMeridian":false,
			"minuteStep":1
		}).on('changeTime.timepicker', function(e) {
			console.log(e.time);
		});*/

	//$(".datepicker").datepicker('update', '05-03-2011');
	//$(".fecha_inicio_enviar").val(at_liquidaciones_filtro_fecha_inicio);

	//$(".fecha_inicio_enviar").datepicker("setValue", new Date(at_liquidaciones_filtro_fecha_inicio) );

	//$(".fecha_fin_enviar").val(at_liquidaciones_filtro_fecha_fin);

	//$(".fecha_fin_enviar").datepicker("setValue", new Date(at_liquidaciones_filtro_fecha_fin) );  
	/*
	$(".local").val(at_liquidaciones_filtro_locales).trigger('change');
	$('.canalventarecaudacion').val(at_liquidaciones_filtro_canales_de_venta).trigger('change');
	*/
   
	var pos = Object();
	$(".table_tr_fixed_me").each(function(index, el) {
		pos[index] = {};
		pos[index].top = $(el).offset().top;
		pos[index].height = $(el).height();
	});
	$(document).on('scroll', function(event) {
		$(".table_tr_fixed_me").each(function(index, el) {
			var doc_top = $(window).scrollTop();
			if(doc_top>pos[index].top){
				//$(el).stop().addClass("table_tr_fixed");
				//$(".table_tr_fixed_new").remove();
				//var new_el = $(el).clone(true).addClass("table_tr_fixed").addClass("table_tr_fixed_new");
				//$("body").append(new_el);
			}else{
				//$(".table_tr_fixed_new").remove();
				//$(el).stop().removeClass("table_tr_fixed");
			}
		});
		//console.log(event);
	});
}
function sec_recaudacion_events(){
	console.log("sec_recaudacion_events");
	$(".btnfiltarrecaudacion").click(function() {
		var selectionslocales = $(".local").select2('data').text; 
		var selectionscanalventarecaudacion = $(".canalventarecaudacion").select2('data').text;  
		sec_recaudacion_get_liquidaciones();
	
	});	
	$(".recaudacion_import_btn")
		.off()
		.click(function(event) {
			console.log("recaudacion_import_btn:click");
			$("#recaudacion_import_modal")
				//.off()
				.off('shown.bs.modal')
				.on('shown.bs.modal', function (e) {
					sec_recaudacion_events();
					recaudacion_import_modal_events();

					
				})
				.modal({
					"backdrop":'static',
					"keyboard":false,
					"show":true
				});
		})
		//.click()
		;

	$(".recaudacion_import_from_bc_btn")
		.off()
		.click(function(event) {
			console.log("recaudacion_import_from_bc_btn:click");
			$("#recaudacion_import_from_bc_modal")
				.off('shown.bs.modal')
				.on('shown.bs.modal', function (e) {
					sec_recaudacion_events();
					recaudacion_import_modal_events();
				})
				.modal({
					"backdrop":'static',
					"keyboard":false,
					"show":true
				});
		})
		//.click()
		;


	$(".rec_reprocess_btn")
		.off()
		.click(function(event) {
			rec_reprocess($(this));
		});

	$(".rec_reprocess_all_btn")
		.off()
		.click(function(event) {
			rec_reprocess_all($(this));
		})
		//.click()
		;
	$(".rec_hide_all_btn")
		.off()
		.click(function(event) {
			rec_hide_all($(this));
		})
		;
	$(".checkbox_me")
		.off()
		.click(function(event) {
			event.preventDefault();
			var checkbox = $(this).find("input[type=checkbox]");
			if(checkbox.prop('checked')){
				checkbox.prop('checked', false);
				$(this).removeClass('checked');
			}else{
				checkbox.prop('checked', true);
				$(this).addClass('checked');
			}
			//console.log(checkbox);
		});
	$(".re_process_checkbox")
		.click(function(event) {
			if($(this).data("id")=="all"){
				if($(this).prop('checked')){
					$(".re_process_checkbox").prop('checked', true);
					$(".checkbox_me").addClass('checked');
				}else{
					$(".re_process_checkbox").prop('checked', false);
					$(".checkbox_me").removeClass('checked');
				}
			}
		});

	$(".recaudacion_generar_liquidaciones_btn")
		.off()
		.click(function(event) {
			$("#recaudacion_generar_liquidacion_modal")
				.off('shown.bs.modal')
				.on('shown.bs.modal', function (e) {
					sec_recaudacion_events();
					recaudacion_import_modal_events();					
				})
				.modal({
					"backdrop":'static',
					"keyboard":false,
					"show":true
				});
		})
		//.click();
		;

	



	/*$('#recaudacion_import_from_bc_form .recaudacion_datepicker')
		.datepicker({
			format: 'dd-mm-yyyy',
			autoclose:true
		}).on('show', function(ev){
	    }).on('changeDate', function(ev){
			$(this).datepicker('hide');
			var newDate = new Date(ev.date);
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
	    });*/
}
function recaudacion_import_modal_events() {
	console.log("recaudacion_import_modal_events");

	$(".recaudacion_import_from_bc_submit_btn")
		.off()
		.click(function(event) {
			console.log("recaudacion_import_from_bc_submit_btn:CLICK");
			var import_data = {};


			$(".import_bc_data").each(function(index, el) {
				if($(el).attr("type")=="radio"){
					if($(el).prop('checked')){
						import_data[$(el).attr("name")]=$(el).val();
					}
				}else if($(el).attr("type")=="checkbox"){
					if($(el).attr("name") in import_data){

					}else{
						import_data[$(el).attr("name")]={};
					}
					if($(el).prop('checked')){
						import_data[$(el).attr("name")][index]=$(el).val();
						//import_data[$(el).attr("name")].push($(el).val());
					}
				}else{
					import_data[$(el).attr("name")]=$(el).val();
				}
			});

			console.log(import_data);
			import_from_bc_init(import_data);
		})
		//.click();
		;
	$("#recaudacion_import_from_bc_form")
		.off()
		.submit(function(event) {
			event.preventDefault();
			console.log("recaudacion_import_from_bc_form:submit");

			var import_data = {};


			$(".import_bc_data").each(function(index, el) {
				if($(el).attr("type")=="radio"){
					if($(el).prop('checked')){
						import_data[$(el).attr("name")]=$(el).val();
					}
				}else if($(el).attr("type")=="checkbox"){
					if($(el).attr("name") in import_data){

					}else{
						import_data[$(el).attr("name")]={};
					}
					if($(el).prop('checked')){
						import_data[$(el).attr("name")][index]=$(el).val();
						//import_data[$(el).attr("name")].push($(el).val());
					}
				}else{
					import_data[$(el).attr("name")]=$(el).val();
				}
			});

			console.log(import_data);
			import_from_bc_init(import_data);
		})
		//.submit()
		;

	$("#recaudacion_import_modal .btn_servicio")
		.off()
		.change(function(event) {
			//var servicio_id = $(this).val();
			//console.log(servicio_id);
			//$(".tipo_servicio").addClass('hidden');
			//$(".tipo_servicio_"+servicio_id).removeClass('hidden');
			//$(".nav-tabs li").removeClass('active');
			//$(this).addClass('active');
			//$(".tab-content div.tab-pane").removeClass('active');
			//$("#cnl_"+tab).addClass('active');
			//$(this).tab("show");
		});

	/*$("#recaudacion_import_modal .timepicker")
		.timepicker({
	    	"showMeridian":false,
	    	"minuteStep":1
	    });*/

	/*$('#recaudacion_import_modal .recaudacion_datepicker')
		.datepicker({
			format: 'dd-mm-yyyy',
			autoclose:true
		}).on('show', function(ev){
			//console.log($(this));
	    }).on('changeDate', function(ev){
			$(this).datepicker('hide');
			var newDate = new Date(ev.date);
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
	    });*/


   $(".recaudacion_datepicker")
   		.datepicker("destroy")
		.datepicker({
			dateFormat:'dd-mm-yy'
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
		});
		$.datepicker.regional['es'] = {
		     closeText: 'Cerrar',
		     prevText: '< Ant',
		     nextText: 'Sig >',
		     currentText: 'Hoy',
		     monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
		     monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
		     dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
		     dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
		     dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
		     weekHeader: 'Sm',
		     dateFormat: 'dd-mm-yy',
		     firstDay: 1,
		     isRTL: false,
		     showMonthAfterYear: false,
		     yearSuffix: ''
		 };
		 $.datepicker.setDefaults($.datepicker.regional['es']);

	
	$("#recaudacion_import_modal")
		.off('hidden.bs.modal')
		.on('hidden.bs.modal', function (e) {
			console.log("bye");
			//console.log(form);
			//console.log(uploader.form);
			//$("#"+form+" div.files_list_holder").html("");
			//uploader.destroy();
		});


	var input_file = $("#file");
		if(input_file){
			var upload_btn = $(".upload-btn");
		    var form = upload_btn.data("form");
		    var data = {};
		    	data["tabla"]="tbl_transacciones_repositorio";

			input_file.hide();
			input_file.off();
			var ele = document.getElementById(input_file.attr("id"));
			if(ele){
			    var files = ele.files;
					
					input_file.change(function(e) {
						files = e.target.files 
						$.each(files, function(index, file) {
							console.log(file);
							var new_file_div = $(".file_example").clone();
								new_file_div.removeClass('file_example');
								new_file_div.removeClass('hidden');
								new_file_div.addClass('file_'+index);
								new_file_div.find(".filename .name").html(file.name);
								new_file_div.find(".size").html((file.size/1024).toFixed(2)+"Kb");
							$("#"+form+" div.files_list_holder").append(new_file_div);
						});
					});
				}
		}

	$("#recaudacion_import_form")
		.off()
		.submit(function(event) {
			event.preventDefault();
			console.log("recaudacion_import_form:submit");


		    var upload_btn = $(".upload-btn");
		    var form = upload_btn.data("form");
		    var data = {};
		    	data["tabla"]="tbl_transacciones_repositorio";


			$(".import_data").each(function(index, el) {
				if($(el).attr("type")=="radio"){
					if($(el).prop('checked')){
						data[$(el).attr("name")]=$(el).val();
					}
				}else{
					data[$(el).attr("name")]=$(el).val();
				}
			});
			console.log(data);			

			//var input_file = $("#file");
			
			
    		if(files.length){
    			var files_count = 0;
    			input_file.simpleUpload("sys/SimpleUpload.php", {
    				init: function(){

    				},
    				data:data,
    				finish: function(){
    					loading();
						swal({
							title: "Listo!",
							text: "Los archivos subieron exitosamente",
							type: "success",
							timer: 400,
							closeOnConfirm: false
						},
						function(){
							swal.close();
							loading(true);
							m_reload();
						});
    				},
					start: function(file){
						//console.log("start"); console.log(file);
						this.progressBar = $(".file_"+files_count+" .progress-bar");
						this.progress_num = $(".file_"+files_count+" .por .num");
						files_count++;
					},

					progress: function(progress){
						//console.log("progress"); console.log(progress);
						this.progressBar.width(progress + "%");
						this.progress_num.html(progress.toFixed(0));
					},

					success: function(data){
						console.log("success"); console.log(data);
					},

					error: function(error){
						//console.log("error"); console.log(error);
					}

				});
    		}else{
    			swal("Error!", "Seleccione un archivo", "warning");
    		}
    		/**/
		});
	
	$(".generar_cerrar_btn")
		.off()
		.click(function(event) {
			$("#recaudacion_generar_liquidacion_modal").modal("hide");
		});
	$("#recaudacion_generar_liquidacion_modal .timepicker")
		.timepicker({
	    	"showMeridian":false,
	    	"minuteStep":1
	    });
	/*$('#recaudacion_generar_liquidacion_modal .recaudacion_datepicker')
		.datepicker({
			format: 'dd-mm-yyyy',
			autoclose:true
		}).on('show', function(ev){
			//console.log($(this));
	    }).on('changeDate', function(ev){
			$(this).datepicker('hide');
			var newDate = new Date(ev.date);
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
	    });
	    */
	//rec_gen_liq(true);
	$(".generar_btn")
		.off()
		.click(function(event) {
			var servicio_id = $("input[name=gen_servicio]:checked").val();
			var btn = $(this);
			rec_gen_liq(btn);
			/*if(servicio_id){
				swal({
					title: '¿Seguro?',
					text: '',
					type: 'info',
					showCancelButton: true,
					confirmButtonText: 'Si, procesar!',
					cancelButtonText: 'No, cancelar!',
					closeOnConfirm: false,
					closeOnCancel: true
				}, function(isConfirm){
					if (isConfirm){ 
						//swal.close();
						//loading(true);
						rec_gen_liq(btn);
					}
				});
			}else{
				swal("Error!", "Seleccione un servicio", "warning");
			}*/
		});
	//$(".nav-tabs label").first().click();
}
function formatonumeros(x) {
	if (x) {
    	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}else{
		return 0;
	}
}
function getcurrentdate(){
	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth()+1; //January is 0!
	var yyyy = today.getFullYear();
	if(dd<10) {dd = '0'+dd} 
	if(mm<10) {mm = '0'+mm} 
	today =  dd+ '-'+ mm+ '-'+yyyy ;
	return today;
}	
function gettomorrowdate(){
	var today = new Date();
	var dd = today.getDate()+1;
	var mm = today.getMonth()+1; //January is 0!
	var yyyy = today.getFullYear();
	if(dd<10) {dd = '0'+dd} 
	if(mm<10) {mm = '0'+mm} 
	today =  dd+ '-'+ mm+ '-'+yyyy ;
	return today;
}

var process_all_list = {};
var process_all_progress = 0;
var process_all_list_length = 0;
function rec_reprocess_all(btn){
	console.log("rec_reprocess_all");
	swal({
		title: '¿Seguro?',
		text: 'Esta accion creará un nuevo proceso y procesará toda la informacion nuevamente.',
		type: 'info',
		showCancelButton: true,
		confirmButtonText: 'Si, procesar!',
		cancelButtonText: 'No, cancelar!',
		closeOnConfirm: false,
		closeOnCancel: true
	}, function(isConfirm){
		if (isConfirm){ 
			swal.close();
			loading(true);
			
			process_all_list = {};
			var ndx=0;
			$(".re_process_checkbox").each(function(index, el) {
				if($(el).prop("checked")){
					process_all_list[ndx]=$(el).data("id");
					ndx++;
				}
			});
			process_all_list_length = Object.keys(process_all_list).length;
			loading(true);
			var progress_bar = $('<div class="progress progress-lg"><div class="this-bar progress-bar progress-bar-striped active progress-bar-success"></div></div>');
			$(".loading_box").addClass("loading_box_progress");
			$(".loading_box").append(progress_bar);

			$(".this-bar").html(process_all_progress+"%");	
			$(".this-bar").stop().css({width: process_all_progress+"%"});

			rec_reprocess_all_send(0);

			$(document).on("rec_reprocess_all_"+(ndx-1),function(event) {
				console.log("FIN EVENT: rec_reprocess_all_"+(ndx-1));

				swal({
					title: "Listo!",
					text: "",
					type: "success",
					timer: 400,
					closeOnConfirm: false
				},
				function(){		
					swal.close();
					loading(true);	
					m_reload();
				});
				//loading();
			});
		}
	});
}
function rec_reprocess_all_send(index) {
	//console.log("rec_reprocess_all_send");
	//console.log(process_all_list[index]);

	var next_index = index+1;

	$(document).on("rec_reprocess_all_"+index,function(event) {
		//console.log("EVENT: rec_reprocess_all_"+index);
		$(document).off("rec_reprocess_all_"+index);

		process_all_progress = (next_index / process_all_list_length) * 100;
		$(".this-bar").html(next_index+"/"+process_all_list_length+" "+(process_all_progress).toFixed(0)+"%");	
		$(".this-bar").stop().css({width: process_all_progress+"%"});

		if(next_index in process_all_list){
			rec_reprocess_all_send(next_index);
		}
	});
	
	var data = Object();
		data.id = process_all_list[index];
	$.post('sys/set_data.php', {
		"opt": 'rec_reprocess'
		,"data":data
	}, function(r) {
		console.log(r);
		//auditoria_send({"proceso":"rec_reprocess_all_send","data":data});
		$(document).trigger("rec_reprocess_all_"+index);
	});
}
function rec_reprocess(btn) {
	console.log("rec_reprocess");

	swal({
		title: '¿Seguro?',
		text: 'Esta accion creará un nuevo proceso y procesará toda la informacion nuevamente.',
		type: 'info',
		showCancelButton: true,
		confirmButtonText: 'Si, procesar!',
		cancelButtonText: 'No, cancelar!',
		closeOnConfirm: false,
		closeOnCancel: true
	}, function(isConfirm){
		if (isConfirm){ 
			swal.close();
			loading(true);
			var data = Object();
				data.id = btn.data("id");
			console.log(data);
			$.post('sys/set_data.php', {
				"opt": 'rec_reprocess'
				,"data":data
			}, function(r, textStatus, xhr) {
				console.log(r);
				loading();
				swal({
					title: "Listo!",
					text: "",
					type: "success",
					timer: 400,
					closeOnConfirm: false
				},
				function(){
					//auditoria_send({"proceso":"rec_reprocess","data":data});
					swal.close();
					loading(true);
					m_reload();
				});
			});
		}
	});
}
function rec_hide_all(btn){
	//console.log("rec_hide_all");
	swal({
		title: '¿Seguro?',
		text: 'Esta accion ocultará todos los procesos seleccionados.',
		type: 'info',
		showCancelButton: true,
		confirmButtonText: 'Si, archivar!',
		cancelButtonText: 'No, cancelar!',
		closeOnConfirm: false,
		closeOnCancel: true
	}, function(isConfirm){
		if (isConfirm){ 
			swal.close();
			loading(true);

			//var list = {};
			var ndx=0;
			$(".re_process_checkbox").each(function(index, el) {
				if($(el).prop("checked")){
					var data = Object();
						data.table = "tbl_transacciones_procesos";
						data.id = $(el).data("id");
						data.col = "estado";
						data.val = "0";
					//console.log(data);
					/*auditoria_send({"proceso":"switch_data","data":data});*/
					$.post('sys/set_data.php', {
						"opt": 'switch_data'
						,"data":data
					}, function(r, textStatus, xhr) {
						$(document).trigger("rec_reprocess_all_"+ndx);
					});
					ndx++;
				}
			});
			$(document).on("rec_reprocess_all_"+(ndx),function(event) {
				//console.log("FIN EVENT: rec_reprocess_all_"+(ndx));
				loading();
				swal({
					title: "Listo!",
					text: "",
					type: "success",
					timer: 400,
					closeOnConfirm: false
				},
				function(){
					swal.close();
					loading(true);
					m_reload();
				});
				
			});			
		}
	});
}
var liq_days_list = {};
var liq_progress = 0;
var liq_list_length=0;
function rec_gen_liq(btn){
	console.log("rec_gen_liq");
	var data = Object();
		data.servicio_id = $("input[name=gen_servicio]:checked").val();
		data.inicio_fecha = $("input[name=inicio_fecha]").val();
		data.fin_fecha = $("input[name=fin_fecha]").val();
	
	
	var start = new Date(data.inicio_fecha);
	var end = new Date(data.fin_fecha);
	var diff  = new Date(end - start);
	var dias = diff/1000/60/60/24;

	liq_days_list = {};
	var ndx=0;
	for (d = 0; d < (dias+1); d++) { 
		var pro_date = new Date(data.inicio_fecha);
		var new_d = (d + 1);
		pro_date.setDate(pro_date.getDate() + new_d);
		liq_days_list[ndx]=pro_date;
		ndx++;
	}
	liq_list_length = ndx;


	swal.close();
	loading(true);

	var progress_bar = $('<div class="progress progress-lg"><div class="this-bar progress-bar progress-bar-striped active progress-bar-success"></div></div>');
	$(".loading_box").addClass("loading_box_progress");
	$(".loading_box").append(progress_bar);

	$(".this-bar").html(liq_progress+"%");	
	$(".this-bar").stop().css({width: liq_progress+"%"});

	var send_data = Object();
		send_data.servicio_id = data.servicio_id;
	rec_gen_liq_send(0,send_data);

	$(document).on("rec_gen_liq_"+(ndx-1),function(event) {
		//console.log("FIN EVENT: rec_gen_liq_"+(ndx-1));

		swal({
			title: "Listo!",
			text: "",
			type: "success",
			timer: 400,
			closeOnConfirm: false
		},
		function(){		
			swal.close();
			loading(true);	
			m_reload();
		});
		//loading();
	});
}
function rec_gen_liq_send(index,data){	
	//console.log("rec_gen_liq_send");
	//console.log(index);
	//console.log(data);
	var next_index = index+1;

	$(document).on("rec_gen_liq_"+index,function(event) {
		//console.log("EVENT: rec_reprocess_all_"+index);
		$(document).off("rec_gen_liq_"+index);

		liq_progress = (next_index / liq_list_length) * 100;
		$(".this-bar").html(next_index+"/"+liq_list_length+" "+(liq_progress).toFixed(0)+"%");	
		$(".this-bar").stop().css({width: liq_progress+"%"});

		if(next_index in liq_days_list){
			rec_gen_liq_send(next_index,data);
		}
	});
	
	//var data = Object();
		data.fecha = liq_days_list[index];
	//	data.canal_de_venta_id = 
	$.post('sys/set_data.php', {
		"opt": 'rec_gen_liq'
		,"data":data
	}, function(r) {
		console.log("rec_gen_liq_send:DONE");
		console.log(r);
		//auditoria_send({"proceso":"rec_gen_liq_send","data":data});
		$(document).trigger("rec_gen_liq_"+index);
	});
}
var import_days_list = {};
var import_progress = 0;
var import_progress_day = 0;
var import_list_length = 0;
var import_curr_index = 0;
var import_pages = 1;
var import_curr_page = 1;
var import_num_tickets = 0;
var import_repo_insert = 0;
var import_repo_update = 0;
var import_deta_insert = 0;
var import_deta_update = 0;
var import_no_procesados = {};
var import_total_time = 0;
function import_from_bc_init(data){
	//console.log("import_from_bc_init");
	//console.log(data);	
	
	var start = new Date(data.inicio_fecha);
	var end = new Date(data.fin_fecha);
	var diff  = new Date(end - start);
	var dias = diff/1000/60/60/24;

	import_days_list = {};
	var ndx=0;
	for (d = 0; d < (dias+1); d++) { 
		var pro_date = new Date(data.inicio_fecha);
		pro_date.setDate(pro_date.getDate() + d);
		import_days_list[ndx]=pro_date.toISOString().slice(0,10);
		ndx++;
	}
	import_list_length = ndx;

	loading(true);

	var progress_bar = $('<div class="progress progress-lg"><div class="this-bar progress-bar progress-bar-striped active progress-bar-success"></div></div>');
	$(".loading_box").addClass("loading_box_progress");
	$(".loading_box").append(progress_bar);

	$(".this-bar").html(import_progress+"%");	
	$(".this-bar").stop().css({width: import_progress+"%"});

	$(document).on("EVENT_import_from_bc"+(ndx-1),function(event) {
		$(document).off("EVENT_import_from_bc"+(ndx-1));
		import_progress = 100;
		import_curr_index = import_list_length;
		import_from_bc_progress_bar();

		
		loading();
		var swal_text = "La importación ha sido un éxito";
			swal_text += "<br>";
			swal_text += "Total Tickets: " + import_num_tickets;
			swal_text += "<br>";
			swal_text += "Repo Insert: " + import_repo_insert;
			swal_text += "<br>";
			swal_text += "Repo Update: " + import_repo_update;
			swal_text += "<br>";
			swal_text += "Deta Insert: " + import_deta_insert;
			swal_text += "<br>";
			swal_text += "Deta Update: " + import_deta_update;
			swal_text += "<br>";
			swal_text += "Tiempo Total: " + import_total_time.toFixed(0) + " Segundos.";
		swal({
			title: "Listo!",
			text: swal_text,
			type: "success",
			//timer: 400,
			html:true,
			closeOnConfirm: true
		},
		function(){		
			swal.close();
			// RESET 
				import_days_list = {};
				import_progress = 0;
				import_progress_day = 0;
				import_list_length = 0;
				import_curr_index = 0;
				import_pages = 1;
				import_curr_page = 1;
				import_num_tickets = 0;
				import_repo_insert = 0;
				import_repo_update = 0;
				import_deta_insert = 0;
				import_deta_update = 0;
				import_no_procesados = {};
			// FIN RESET
			//loading ();
			//loading(true);	
			//m_reload();
		});
	});

	var send_data = $.extend({},data);
		send_data.servicio_id = data.servicio;
	//console.log(send_data);
	import_from_bc_call_api(send_data);

	//console.log(import_days_list);
	//console.log(import_progress);
	//console.log(import_list_length);
}
function import_from_bc_call_api(data){
	//console.log("import_from_bc_call_api");
	var next_index = import_curr_index+1;

	var send_data = $.extend({},data);
		//send_data.servicio_id = data.servicio;
		send_data.fecha = import_days_list[import_curr_index];
		send_data.page = import_curr_page;


	$(document).on("EVENT_import_from_bc"+import_curr_index,function(event) {
		console.log("EVENT: EVENT_import_from_bc"+import_curr_index);
		$(document).off("EVENT_import_from_bc"+import_curr_index);

		if(next_index in import_days_list){
			import_pages=1;
			import_curr_page=1;
			import_curr_index=next_index;
			import_from_bc_call_api(send_data);
			/**/
		}
		import_progress = import_progress_day = (next_index / import_list_length) * 100;
		import_from_bc_progress_bar();
	});	
	//data.fecha = import_days_list[import_curr_index];
	//data.page = import_curr_page;
	//console.log(send_data);
	import_from_bc(send_data);
	//var data = Object();
}
function import_from_bc(data){
	//console.log("import_from_bc");
	//console.log(data);
	//*
		$.post('sys/set_data.php', {
			"opt": 'import_from_bc'
			,"data":data
		}, function(r) {
			//console.log("import_from_bc_SEND:DONE");
			var r_obj = {};
				r_obj["API_continue"] = false;
			try{
				r_obj = jQuery.parseJSON(r);
			}catch(err){
				r_obj["API_continue"] = false;
				console.log("HORROR");
				//console.log(err);
				//console.log(r);
			}
			if(r_obj["API_continue"]){
				console.log(r_obj);
				console.log("API_time_to_response: "+r_obj["API_time_to_response"]);
				console.log("time_total: "+r_obj["time_total"]);
				import_pages = r_obj["API_day_pages"];

				import_repo_insert += r_obj["repositorios_insertados"];
				import_repo_update += r_obj["repositorios_updateados"];
				import_deta_insert += r_obj["detalles_insertados"];
				import_deta_update += r_obj["detalles_updateados"];

				import_total_time += r_obj["time_total"];
				if(import_curr_page==1){
					import_num_tickets = import_num_tickets + r_obj["API_tickets_count"];
				}
				if(import_curr_page == import_pages){
					//console.log("import_curr_page == import_pages");
					$(document).trigger("EVENT_import_from_bc"+import_curr_index);	
				}else{
					import_progress = import_progress_day + (((import_curr_page / import_pages) * 100) / import_list_length);
					import_from_bc_progress_bar();
					import_curr_page = import_curr_page+1;
					data.page = import_curr_page;
					import_from_bc(data);
				}
			}else{
				console.log("HORROR - ALL PROCESS STOPPED!!!");
				console.log(r_obj);
				console.log(r);
			}		
		});	
	/**/
}
function import_from_bc_progress_bar(){

	//import_progress = ((import_pages/import_curr_page) / import_list_length) * 100;
	//var total = Math.ceil(100 / import_list_length);
	//console.log(total);
	//import_progress = ()
	//console.log("import_progress");
	//console.log(import_progress_day);
	//console.log(import_progress);
	$(".this-bar").html((import_curr_index)+"/"+import_list_length+" "+(import_progress).toFixed(0)+"%");	
	$(".this-bar").stop().css({width: import_progress+"%"});
}
var API_data = {};
function recaudacion_import_from_bc(){
	console.log("recaudacion_import_from_bc");
	var data = {};
	//data.username = "ManuelLaguno";
	//data.password = "Ap280317$";

	data.username = "carlosmesta";
	data.password = "Cms2204$";

	data.language = "en";

	API_data.Authentication = localStorage.getItem("API_Authentication");


	$(document).on("API_CheckUserLoginPassword",function(event) {
		$(document).off("API_CheckUserLoginPassword");
		console.log("EVENT: API_CheckUserLoginPassword");
		console.log(event.event_data);
		if(event.event_data.textStatus=="success"){
			API_CheckForLogin(data);
		}else{
			console.log("ERROR");
		}
	});

	$(document).on("API_CheckForLogin",function(event) {
		$(document).off("API_CheckForLogin");
		console.log("EVENT: API_CheckForLogin");recaudacion_datepicker
		console.log(event.event_data);
		if(event.event_data.textStatus=="success"){
			if(event.event_data.return_data.Data===true){
				console.log("ESTA LOGEADO");
				console.log(API_data);
				API_GetBetReport();				
			}else{
				console.log("NO ESTA LOGEADO");
				API_Login(data);
			}
		}else{
			console.log("NO ESTA LOGEADO");
		}
	});

	$(document).on("API_Login",function(event) {
		$(document).off("API_Login");
		console.log("EVENT: API_Login");
		console.log(event.event_data);
		if(event.event_data.textStatus=="success"){
			API_data.Authentication = event.event_data.xhr.getResponseHeader("Authentication");
			localStorage.setItem("API_Authentication", API_data.Authentication);

			if(event.event_data.return_data.HasError === true){
				console.log("LOGIN INCORRECTO: "+event.event_data.return_data.AlertMessage);
			}else{
				console.log("LOGIN CORRECTO");
				console.log(event.event_data.xhr.getResponseHeader("Authentication"));
				console.log(API_data);
				console.log(localStorage);
				API_GetBetReport();
			}
		}else{
			console.log("NO LOGIN");
		}
	});

	$(document).on("API_Logout",function(event) {
		$(document).off("API_Logout");
		console.log("EVENT: API_Logout");
		console.log(event.event_data);
		if(event.event_data.textStatus=="success"){
			console.log("LOG OUT");
			localStorage.removeItem("API_Authentication");
			console.log(localStorage);
		}
	});

	$(document).on("API_GetBetReport",function(event) {
		$(document).off("API_GetBetReport");
		console.log("EVENT: API_GetBetReport");
		console.log(event.event_data);
		if(event.event_data.textStatus=="success"){
			console.log("HAY DATA");
			if(event.event_data.return_data.Data != null){
				console.log(event.event_data.return_data.Data.BetData.Count);
			}
			loading();
		}else{
			console.log("NO HAY DATA");
		}
	});

	$(document).on('API_loading', function(event) {
		loading(true,true);
	});
	$(document).on('API_CheckAuthentication', function(event) {
		$(document).off("API_CheckAuthentication");
		console.log("EVENT: API_CheckAuthentication");
		console.log(event.event_data);
		API_GetBetReport();
	});


	if(API_data.Authentication){
		var var_API_CheckAuthentication = API_CheckAuthentication(data);
		if(var_API_CheckAuthentication){
			//API_GetBetReport(data);
			API_GetBetReport();
		}
	}else{
		API_Login(data);		
	}
}
//recaudacion_import_from_bc();
function API_CheckAuthentication(data){
	console.log("API_CheckAuthentication");
	console.log(API_data.Authentication);
	$.ajax({
		type: "GET",
		url: 'https://backofficewebadmin.betconstruct.com/api/en/Account/CheckAuthentication',
		//data:Object(),
		success: function(return_data, textStatus, xhr) {
			var event_data = {};
			event_data.return_data = return_data;
			event_data.textStatus = textStatus;
			event_data.xhr = xhr;
			console.log(event_data);
			$(document).trigger({
				type:"API_CheckAuthentication",
				"event_data":event_data
			});
		},
		//contentType: "application/json; charset=utf-8",
		headers: {
			"Authentication": API_data.Authentication,
			"Accept":"application/json, text/plain, */*"
		}
	});	
}
function API_CheckUserLoginPassword(data){
	console.log("API_CheckUserLoginPassword");
	$.post('https://backofficewebadmin.betconstruct.com/api/en/Account/CheckUserLoginPassword'
	,{
		"Username":data.username
		,"Password":data.password
		,"Language":data.language
	}
	, function(return_data, textStatus, xhr) {
		var event_data = {};
		event_data.return_data = return_data;
		event_data.textStatus = textStatus;
		event_data.xhr = xhr;
		$(document).trigger({
			type:"API_CheckUserLoginPassword",
			"event_data":event_data
		});
	});
}
function API_CheckForLogin(data){
	console.log("API_CheckForLogin");
	$.get('https://backofficewebadmin.betconstruct.com/api/en/Account/CheckForLogin'
	,{
		"username":data.username
	}
	, function(return_data, textStatus, xhr) {
		var event_data = {};
		event_data.return_data = return_data;
		event_data.textStatus = textStatus;
		event_data.xhr = xhr;
		$(document).trigger({
			type:"API_CheckForLogin",
			"event_data":event_data
		});
	});
}
function API_Login(data){
	console.log("API_Login");
	$.post('https://backofficewebadmin.betconstruct.com/api/en/Account/Login'
	,{
		"Username":data.username
		,"Password":data.password
		,"Language":data.language
	}
	, function(return_data, textStatus, xhr) {
		var event_data = {};
		event_data.return_data = return_data;
		event_data.textStatus = textStatus;
		event_data.xhr = xhr;
		$(document).trigger({
			type:"API_Login",
			"event_data":event_data
		});
		//console.log(xhr.getResponseHeader("Authentication"));
	});
}
function API_Logout(data){
	console.log("API_Logout");
	$.ajax({
		type: "POST",
		url: 'https://backofficewebadmin.betconstruct.com/api/en/Account/Logout',
		success: function(return_data, textStatus, xhr) {
			var event_data = {};
			event_data.return_data = return_data;
			event_data.textStatus = textStatus;
			event_data.xhr = xhr;
			$(document).trigger({
				type:"API_Logout",
				"event_data":event_data
			});
		},
		headers: {
			Authentication: API_data.Authentication
		}
	});
}
function API_GetBetReport(data){
	console.log("API_CheckUserLoginPassword");
	var request = {};
		request.filterBet = {}
		request.filterBet.AmountFrom = ""
		request.filterBet.AmountTo = ""
		request.filterBet.WinningAmountFrom = ""
		request.filterBet.WinningAmountTo = ""
		request.filterBet.TypeName = "All"
		request.filterBet.StateName = "All"
		request.filterBet.CalcStartDateLocal = ""
		request.filterBet.CalcEndDateLocal = ""
		request.filterBet.StartDateLocal = "01-05-17 - 00:00:00"
		request.filterBet.EndDateLocal = "02-05-17 - 00:00:00"
		request.filterBet.Source = ""
		request.filterBet.OrderedItem = 11
		request.filterBet.IsOrderedDesc = ""
		request.filterBet.SportsbookProfileId = ""
		request.filterBet.ClientLoginIp = ""
		request.filterBet.PriceFrom = ""
		request.filterBet.PriceTo = ""
		request.filterBet.IsTest = ""
		request.filterBet.BetshopId = ""
		request.filterBet.InfoBetshopId = ""
		request.filterBet.InfoCashDeskId = ""
		request.filterBet.CurrencyId = ""
		request.filterBet.IsBonusBet = ""
		request.filterBet.BonusTypeId = ""
		request.filterBet.IsCashDeskPaid = ""
		request.filterBet.SkeepRows = 0
		request.filterBet.MaxRows = 500
		request.filterBet.IsWithSelections = true

		request.filterBetSelection = {}
		request.filterBetSelection.SportId = ""
		request.filterBetSelection.RegionId = ""
		request.filterBetSelection.CompetitionId = ""
		request.filterBetSelection.MatchId = ""

		request.matchFilter = {}
		request.matchFilter.currentSport = ""
		request.matchFilter.currentRegion = ""
		request.matchFilter.currentCompetition = ""
		request.matchFilter.currentMatch = ""

		request.filterDate = {}
		request.filterDate.fromDate = "20-05-17"
		request.filterDate.toDate = "21-05-17"
		request.filterDate.currentTimePeriod = 1
		request.filterDate.fromTimeObj = "2017-05-20T05:00:00.602Z"
		request.filterDate.toTimeObj = "2017-05-21T05:00:00.602Z"
		request.filterDate.fromTime = "00:00:00"
		request.filterDate.toTime = "00:00:00"

		request.isCreatedTime = true

		request.filterText = {}
		request.filterText.Text = ""

		request.ToCurrencyId = "PEN"


	console.log(request);

	var event_data = {};
		event_data.function = "API_GetBetReport";
		event_data.data = data;
		$(document).trigger({
			type:"API_loading",
			"event_data":event_data
		});
	//var request_json = JSON.stringify(request);
	//console.log(request_json);
	//request_json = '{"filterBet":{"AmountFrom":null,"AmountTo":null,"WinningAmountFrom":null,"WinningAmountTo":null,"TypeName":"All","StateName":"All","CalcStartDateLocal":null,"CalcEndDateLocal":null,"StartDateLocal":"22-05-17 - 00:00:00","EndDateLocal":"23-05-17 - 00:00:00","Source":"","OrderedItem":11,"IsOrderedDesc":false,"SportsbookProfileId":"","ClientLoginIp":"","PriceFrom":null,"PriceTo":null,"IsTest":false,"BetshopId":"","InfoBetshopId":"","InfoCashDeskId":"","CurrencyId":null,"IsBonusBet":null,"BonusTypeId":"","IsCashDeskPaid":null,"SkeepRows":0,"MaxRows":10,"IsWithSelections":true},"filterBetSelection":{"SportId":null,"RegionId":null,"CompetitionId":null,"MatchId":null},"matchFilter":{"currentSport":null,"currentRegion":null,"currentCompetition":null,"currentMatch":null},"filterDate":{"fromDate":"22-05-17","toDate":"23-05-17","currentTimePeriod":1,"fromTimeObj":"2017-05-22T05:00:00.602Z","toTimeObj":"2017-05-22T05:00:00.602Z","fromTime":"00:00:00","toTime":"00:00:00"},"isCreatedTime":true,"filterText":{"Text":null},"ToCurrencyId":"PEN"}';
	//request = jQuery.parseJSON('{"filterBet":{"AmountFrom":null,"AmountTo":null,"WinningAmountFrom":null,"WinningAmountTo":null,"TypeName":"All","StateName":"All","CalcStartDateLocal":null,"CalcEndDateLocal":null,"StartDateLocal":"01-05-17 - 00:00:00","EndDateLocal":"02-05-17 - 00:00:00","Source":"","OrderedItem":11,"IsOrderedDesc":false,"SportsbookProfileId":"","ClientLoginIp":"","PriceFrom":null,"PriceTo":null,"IsTest":false,"BetshopId":"","InfoBetshopId":"","InfoCashDeskId":"","CurrencyId":null,"IsBonusBet":null,"BonusTypeId":"","IsCashDeskPaid":null,"SkeepRows":0,"MaxRows":10,"IsWithSelections":true},"filterBetSelection":{"SportId":null,"RegionId":null,"CompetitionId":null,"MatchId":null},"matchFilter":{"currentSport":null,"currentRegion":null,"currentCompetition":null,"currentMatch":null},"filterDate":{"fromDate":"01-05-17","toDate":"02-05-17","currentTimePeriod":"custom","fromTimeObj":"2017-05-22T05:00:00.345Z","toTimeObj":"2017-05-22T05:00:00.345Z","fromTime":"00:00:00","toTime":"00:00:00"},"isCreatedTime":true,"filterText":{"Text":null},"ToCurrencyId":"PEN"}');
	$.ajax({
		type: "POST",
		url: 'https://backofficewebadmin.betconstruct.com/api/en/Report/GetBetReport',
		//data:Object(),
		data:request,
		success: function(return_data, textStatus, xhr) {
			var event_data = {};
			event_data.return_data = return_data;
			event_data.textStatus = textStatus;
			event_data.xhr = xhr;
			$(document).trigger({
				type:"API_GetBetReport",
				"event_data":event_data
			});
		},
		//contentType: "application/json; charset=utf-8",
		headers: {
			"Authentication": API_data.Authentication,
			"Accept":"application/json, text/plain, */*"
		}
	});	/**/
}