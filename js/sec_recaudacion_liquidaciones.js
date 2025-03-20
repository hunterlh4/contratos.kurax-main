var data_paginas;
var data_desde;
var data_hasta;
var data_numero_por_pagina;
var data_pagina_actual;
var get_liquidaciones_data = {};
var table_dt = false;
var table_rc = false;
var table_cct = false;
var dt_numero = 10;
var dt_pagina = 0;
var modelo = false;
var monedas = {};
var canales_de_venta = {};
canales_de_venta["total"]="Total";
// var at_liquidaciones_filtro_fecha_inicio = '2017-06-01';
// var at_liquidaciones_filtro_fecha_fin = '2017-06-12';
var at_liquidaciones_filtro_fecha_inicio = moment().subtract(1, 'days').format("YYYY-MM-DD");
var at_liquidaciones_filtro_fecha_fin = moment().subtract(1, 'days').format("YYYY-MM-DD");;
var at_liquidaciones_filtro_locales = false;
var at_liquidaciones_filtro_canales_de_venta = false;
var column_name = false;
var count = 0;
var $table_tck = false;
function sec_recaudaciones_liquidaciones(){
	loading(true);
	sec_recaudacion_liquidaciones_events();
	sec_recaudacion_liquidaciones_settings();
	sec_recaudacion_get_locales();
	sec_recaudacion_get_canales_venta(); 
	sec_recaudacion_get_redes();
	sec_recaudacion_get_zonas();
	// sec_recaudacion_get_liquidaciones(); 
	loading();
};
function sec_recaudacion_get_zonas(){
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
					$(".zona").append(new_option);
				});
				$('.zona').select2({closeOnSelect: false});
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
function sec_recaudacion_get_redes(){
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
					$(".red").append(new_option);
				});
				$('.red').select2({closeOnSelect: false});
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
function sec_recaudacion_get_canales_venta(){
  var data = {};
  data.what={};
  data.what[0]="id";
  data.what[1]="codigo";
  data.where="canales_de_venta";
  data.en_liquidacion=1;
  data.filtro={}
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
			$(".canalventarecaudacion").append(new_option);

		  });
		  $('.canalventarecaudacion').select2({closeOnSelect: false});
		}
		// sec_recaudacion_get_liquidaciones();  
	  }catch(err){
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
	  async: "false"
	})
	.done(function( data, textStatus, jqXHR ) {
	  try{
		if ( console && console.log ) {
		  $.each(data.data,function(index,val){
			var new_option = $("<option>");
			$(new_option).val(val.id);
			$(new_option).html(val.nombre);
			$(".local").append(new_option);
		  });
		  $('.local').select2({closeOnSelect: false});
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
function sec_recaudacion_liquidaciones_settings(){

	$('.local').select2({
	  closeOnSelect: false,            
	  allowClear: true,
	});
	$('.canalventarecaudacion').select2({
	  closeOnSelect: false,            
	  allowClear: true,
	});
	$('.red_recaudacion').select2({
	  closeOnSelect: false,            
	  allowClear: true,
	});
	if(localStorage.getItem("at_liquidaciones_filtro_fecha_inicio")){
	  at_liquidaciones_filtro_fecha_inicio = localStorage.getItem("at_liquidaciones_filtro_fecha_inicio");
	}
	if(localStorage.getItem("at_liquidaciones_filtro_fecha_fin")){
	  at_liquidaciones_filtro_fecha_fin = localStorage.getItem("at_liquidaciones_filtro_fecha_fin");
	}

	var pro_liq_fecha_inicio = $("input[name=pro_liq_fecha_inicio]").val();
	if(pro_liq_fecha_inicio){
	  at_liquidaciones_filtro_fecha_inicio = pro_liq_fecha_inicio;
	}
	var pro_liq_fecha_fin = $("input[name=pro_liq_fecha_fin]").val();
	if(pro_liq_fecha_fin){
	  at_liquidaciones_filtro_fecha_fin = pro_liq_fecha_fin;
	}

	if(localStorage.getItem("at_liquidaciones_filtro_locales")){
	  at_liquidaciones_filtro_locales = localStorage.getItem("at_liquidaciones_filtro_locales");
	}
	if(localStorage.getItem("at_liquidaciones_filtro_canales_de_venta")){
	  at_liquidaciones_filtro_canales_de_venta = localStorage.getItem("at_liquidaciones_filtro_canales_de_venta");
	}
	$(".filtro_datepicker")
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
	var m_liq_filtro_inicio_fecha = moment(at_liquidaciones_filtro_fecha_inicio).format("DD-MM-YYYY");
	$("#input_text-liq_filtro_inicio_fecha")
	  .datepicker("setDate", m_liq_filtro_inicio_fecha)
	  .trigger('change');

	var m_liq_filtro_fin_fecha = moment(at_liquidaciones_filtro_fecha_fin).format("DD-MM-YYYY");
	$("#input_text-liq_filtro_fin_fecha")
	  .datepicker("setDate", m_liq_filtro_fin_fecha)
	  .trigger('change');       
}
function sec_recaudacion_liquidaciones_events(){
	console.log("sec_recaudacion_liquidaciones_events");

	$(".btnfiltarrecaudacion")
		.off()
		.on("click",function(){
			loading(true);    
			var btn =  $(this).data("button");
			sec_recaudacion_validacion_permisos_usuarios(btn);
			// m_reload();
		});
	$('#tabla_sec_recaudacion').off().on( 'click', 'tbody tr', function (e) {
		var columna_class = $(e.target).closest("td").attr("class");
		// console.log("clase "+columna_class);
		var data = table_dt.row(this).data();
		  if (data[3]!="Total" && columna_class==" total_cliente") {
			var fecha_inicio = $(".fecha_inicio_enviar").val();
			var fecha_fin = $(".fecha_fin_enviar").val();
			var canal_de_venta_id = data[25];
			var local_id = data[0];
			$(".cashdesk_nombre_tickets_comision_cuota").text(data[1]);
			var columna = columna_class;
			sec_recaudacion_tickets_comision_cuota(fecha_inicio,fecha_fin,canal_de_venta_id,local_id,columna);
		  };
	}); 	
}
function sec_recaudacion_mostrar_datatable(model){
	var heightdoc = window.innerHeight;
	var heightnavbar= $(".navbar-header").height();
		
	var heighttable =heightdoc-heightnavbar-300;
	if (modelo==1) {
		$.fn.dataTable.ext.errMode = 'none';
		table_dt = $('#tabla_sec_recaudacion').DataTable({ 
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
		        footer: true,
		        className: 'copiarButton'
		     },
		    { 
		        extend: 'csv',
		        text:'CSV',
		        footer: true,
		        className: 'csvButton' 
		    },
		    {   extend: 'excel',
		        text:'Excel',
		        footer: true,
		        className: 'excelButton'
		    },
		    {
		        extend: 'colvis',
		        text:'Visibilidad',
		        className:'visibilidadButton',
		        postfixButtons: [ 'colvisRestore' ]
		    }
		],
		footerCallback: function () {
			var api = this.api(),
			columns = [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29]; 
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
		bRetrieve: true,
		sPaginationType: "full_numbers",
		lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]], 
		searching: true,
		paging: true,
		sScrollY: heighttable, 
		sScrollX: "100%", 
		sScrollXInner: "10%", 
		bScrollCollapse: true,       
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
		loading();
	}
	if (modelo==2) {
		$.fn.dataTable.ext.errMode = 'none';
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
			    {
			        extend: 'colvis',
			        text:'Visibilidad',
			        className:'visibilidadButton',
			        postfixButtons: [ 'colvisRestore' ]
			    }
			],
			fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
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
			},      
			bRetrieve: true,
			sPaginationType: "full_numbers",
			lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],  
			paging: true,
			searching: true,
			sScrollY: heighttable, 
			sScrollX: "100%", 
			sScrollXInner: "100%", 
			bScrollCollapse: true,       
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
	if(modelo==3){ 
		$.fn.dataTable.ext.errMode = 'none';	
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
			    {
			        extend: 'colvis',
			        text:'Visibilidad',
			        className:'visibilidadButton',
			        postfixButtons: [ 'colvisRestore' ]
		           ,collectionLayout :"two-column"
			    }
			],
			fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
				//if ( aData[2] == "Total" )
				if ( aData[6] == "Total" )
				{
					$('td', nRow).css('background-color','#9BDFFD','important');
					$('td', nRow).css('z-index: ','10');  
					$('td', nRow).css('color','#080FFC'); 
					$('td', nRow).css('font-weight','800'); 
					$('td', nRow).css('z-index: ','10');                       
				}
				//else if ( aData[2] != "Total" )
				else if ( aData[6] != "Total" )
				{
					$('td', nRow).css('background-color', '#fafafa','important');
				}
			},
			footerCallback: function () {
				var api = this.api(),
				//columns = [3,4,5,6]; 
				columns = [7,8,9,10]; 
				for (var i = 0; i < columns.length; i++) {
					var total =  api.column(columns[i], {filter: 'applied'}).data().sum().toFixed(2);
					var total_pagina = api.column(columns[i], { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
					if (total<0 && total_pagina<0){
						$(api.column(columns[i]).footer()).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total/2) +'<span><br>');
					}
					else{
						$(api.column(columns[i]).footer()).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total/2) +'<span><br>');
					}
				}				
			},      
			bRetrieve: true,
			sPaginationType: "full_numbers",
			lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],  
			paging: true,
			searching: true,
			sScrollY: heighttable, 
			sScrollX: "100%", 
			sScrollXInner: "100%", 
			bScrollCollapse: true,       
			Sorting: [[1, 'asc']], 
			bSort: false,
			rowsGroup: [0,1,2,3,4],
			data:model,
			columnDefs:[
				{ className: "columnaprincipal_modelo_tres", "targets": [0] }, 
				
				{ className: "columnaprincipal_modelo_tres", "targets": [1] },
				{ className: "columnaprincipal_modelo_tres", "targets": [2] },
				{ className: "columnaprincipal_modelo_tres", "targets": [3] },
				{ className: "columnaprincipal_modelo_tres", "targets": [4] },

				{ className: "columna_dias_modelo_tres", "targets": [5] },
				{ className: "columna_canal_de_venta_modelo_tres", "targets": [6] },				
				{ className: "apostado_modelo_tres", "targets": [7] }, 
				{ className: "ganado_modelo_tres", "targets": [8] },
				{ className: "pagado_modelo_tres", "targets": [9] },
				{ className: "produccion_modelo_tres", "targets": [10] }, 
				{ className: "porcentaje_modelo_tres", "targets": [11] },
				{ className: "cliente_modelo_tres", "targets": [12] },
				{ className: "freegames_modelo_tres", "targets": [13] },
				//{ sortable: false,"class": "index",targets: [0,1]},
				{ sortable: false,"class": "index",targets: [0,5]},
				{ targets: -1,visible: false },
				{
				//aTargets: [3,4,5,6],
				aTargets: [7,8,9,10],
					fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
						 if ( sData < "0" ) {
			                  $(nTd).css('color', 'red')
			                  $(nTd).css('font-weight', 'bold')
						}
					}
				} 
				,{ "visible": false, "targets": [1,2,3,4] }

			], 
			order: [[ 5, 'asc' ]],
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
	
	if(modelo==4){ 
		
		$.fn.dataTable.ext.errMode = 'none';
		table_dt = $('#tabla_sec_recaudacion').DataTable({ 
		scrollX:true,
		fixedColumns:   {
			leftColumns: 9
		},
		bRetrieve: true,
		lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],
		paging: true,	      
		searching: true,
		sPaginationType: "full_numbers",
		Sorting: [[1, 'asc']], 
		//rowsGroup: [0,1,2],
		rowsGroup: [0,1,2 ,3 ,4 ,5 ,6 ,7,8],
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
		footerCallback: function () {
		var api = this.api(),
		//columns = [4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25]; 
		columns = [9,10,11,12,13,14,15,16,17,18,19,21,23,24,25,26,27,28,29,30]; 
			for (var i = 0; i < columns.length; i++) {
				var total =  api.column(columns[i], {filter: 'applied'}).data().sum().toFixed(2);
				var total_pagina = api.column(columns[i], { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
				var val = formatonumeros(total/2);
				if(columns[i] == 9)/*num tickets*/
				{
					total =  api.column(columns[i], {filter: 'applied'}).data().sum();
					total_pagina = api.column(columns[i], { filter: 'applied', page: 'current' }).data().sum();
					val = parseInt(total/2);
				}
				if (total<0 && total_pagina<0){
					$(api.column(columns[i]).footer()).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">'+val +'<span><br>');
				}
				else{
					$(api.column(columns[i]).footer()).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">'+val +'<span><br>');
				}
			}
		},
		fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
		if ( aData[8] == "Total" )
		{
			$('td', nRow).css( 'cursor', 'default','important' );	
		    $('td', nRow).css('background-color','#9BDFFD','important');
		    $('td', nRow).css('color','#080FFC'); 
		    $('td', nRow).css('font-weight','800');                       
		}else{
			//$('td', nRow).css( 'cursor', 'default');
		}

		},
		createdRow: function ( row, data, index ) {
			if (data[8]=="Total" && data[31]!=0) {
				//$('td', row).eq(25).addClass('test_diff_background_color');
				$('td.test_diff', row).addClass('test_diff_background_color');
			};
		},	      
		columnDefs: [
			{ className: "cc_id", "targets": [0] },
			{ className: "local_id", "targets": [1] },
			{ className: "local_nombre", "targets": [2] },

			{ className: "propietario", "targets": [3] },
			{ className: "departamento", "targets": [4] },
			{ className: "provincia", "targets": [5] },
			{ className: "distrito", "targets": [6] },

			{ className: "dias", "targets": [7] },
			{ className: "canales_de_venta", "targets": [8] },
			{ className: "total_num_tickets", "targets": [9] },
			{ className: "total_depositado", "targets": [10] },
			{ className: "total_anulado_retirado", "targets": [11] },
			{ className: "total_apostado", "targets": [12] },
			{ className: "total_ganado", "targets": [13] },
			{ className: "total_pagado", "targets": [14] },
			{ className: "total_produccion", "targets": [15] },
			{ className: "resultado_negocio", "targets": [16] },
			{ className: "total_depositado_web", "targets": [17] },
			{ className: "total_retirado_web", "targets": [18] },
			{ className: "total_caja_web", "targets": [19] },
			{ className: "porcentaje_cliente", "targets": [20] },
			{ className: "total_cliente", "targets": [21] },
			{ className: "porcentaje_freegames", "targets": [22] },
			{ className: "total_freegames", "targets": [23] },
			{ className: "pagado_en_otra_tienda", "targets": [24] },
			{ className: "propios_pagados_en_su_punto", "targets": [25] },
			{ className: "pagado_de_otra_tienda", "targets": [26] },
			{ className: "total_pagos_fisicos", "targets": [27] },
			{ className: "caja_fisico", "targets": [28] },
			{ className: "cashdesk_balance", "targets": [29] },
			{ className: "test_balance", "targets": [30] },
			{ className: "test_diff", "targets": [31] },
			
			{ sortable: false,"class": "index",targets: [1]},
			{ sortable: true, "targets": [1] },
			//{ type: 'num-fmt', "targets": 13 },
			{ type: 'num-fmt', "targets": 18 },
			{
			//aTargets: [4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25],
			aTargets: [9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31],
			fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
				if ( sData < "0" ) {
					$(nTd).css('color', 'red')
					$(nTd).css('font-weight', 'bold')
				}
			}
			},
			//{ "visible": false, "targets": 26 }                  
			{ "visible": false, "targets": [32 ,3,4,5,6] }
		], 
	      pageLength: '25',
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

	$(function() {
		// Check the initial Poistion of the Sticky Header
		var stickyHeaderTop = $('#tabla_sec_recaudacion').offset().top;
		$(window).scroll(function() {
			if ($(window).scrollTop() > stickyHeaderTop) {
				$('.dataTables_scrollHead, .DTFC_LeftHeadWrapper').css('transform', 'translateY(0%)');
				$('.DTFC_LeftHeadWrapper').css({position: 'fixed',top: '50px',zIndex: '1',left: 'auto'});
				$('.dataTables_scrollHead').css({position: 'fixed',top: '50px', zIndex: '1' });
				$('.DTFC_ScrollWrapper').css({height: ''});
			}
			else {
				$('.DTFC_LeftHeadWrapper, .DTFC_LeftHeadWrapper').css({position: 'relative',top: '0px'});
				$('.dataTables_scrollHead').css({position: 'relative', top: '0px'});
				$('.dataTables_scrollHead').css('transform', 'translateY(0%)');
			}
		});
	});
	 

	  loading();
	}
	if(modelo==5){
		$.fn.dataTable.ext.errMode = 'none';
	    table_dt = $('#tabla_sec_recaudacion').DataTable({ 
	      responsive: false, 
	      fixedHeader: {
	        header: true,
	        footer:false
	      },
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
                    className: 'copiarButton'
                 },
                { 
                    extend: 'csv',
                    text:'CSV',
                    className: 'csvButton' 
                    ,filename: $(".export_filename").val()
                },
                {   extend: 'excelHtml5',
                    text:'Excel',
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

		        if ( aData[1] == "Total" ){
		            $('td', nRow).css('background-color','#9BDFFD','important');
		            $('td', nRow).css('color','#080FFC'); 
		            $('td', nRow).css('font-weight','800');                       
		        }
	      },
          columnDefs: [
		        { className: "colunmasControl columna_local_id_modelo_cuatro","targets": [0] },          
		        { className: "canal_de_venta_modelo_cuatro", "targets": [1] },
		        { className: "columnaprincipalm5 columna_dias_modelo_cuatro_body_td", "targets": [2] },
		        { className: "columnaprincipalm5 columna_dias_modelo_cuatro_body_td", "targets": [3] },  
	        	{ className: "apostado_modelo_cuatro", "targets": [4] },
		        { className: "ganado_modelo_cuatro", "targets": [5] },
		        { className: "pagado_modelo_cuatro", "targets": [6] },
		        { className: "produccion_modelo_cuatro", "targets": [7] },        
		        { className: "columnasnumeros_body_td","targets": [4,5,6,7,8,9,10,11,12,13,14,15]},
		        { sortable: false,"class": "index",targets: [0]},
		        { sortable: true, "targets": [0] },
		        { type: 'num-fmt', "targets": 12 },
		        {
		          aTargets: [4,5,6,7,8,9,10,11,12,13,14,15],
			          fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
			             if ( sData < "0" ) {
	                          $(nTd).css('color', 'red')
	                          $(nTd).css('font-weight', 'bold')
			            }
		          }
		        }
	      ], 
	      pageLength: '16',
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
function sec_recaudacion_process_data(obj){
	if (modelo==1) {
		var datafinal=[];
		$.each(obj.data.locales, function(data_index, data_local) {
			var new_data=[];
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
				$('#tabla_sec_recaudacion tfoot').html("<tr><td class='ftm4 tdft'>TOTAL:</td><td class='tdft' style='border-bottom:1px solid #ddd !important; border-top:1px solid #ddd !important;'></td><td class='tdft'></td><td class='tdft'><span class='etotl'>T. Apostado</span><br><span class='etotv'>"+formatonumeros(data_total_apostado)+"</span></td><td class='tdft'><span class='etotl'>T. Ganado</span><br><span class='etotv'>"+formatonumeros(data_total_ganado)+"</span></td><td class='tdft'><span class='etotl'>T. Pagado</span><br><span class='etotv'>"+formatonumeros(data_total_pagado)+"</span></td><td class='tdft'><span class='etotl'>T. Producción</span><br><span class='etotv'>"+formatonumeros(data_total_produccion)+"</span></td></tr>");												
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
						var newObject =[data_nombre_local,
										val1.propietario,
										val1.departamento,
										val1.provincia,
										val1.distrito,
										data_dias_procesados,data_canales_de_venta,data_total_apostado,data_total_ganado,data_total_pagado,data_total_produccion,data_total_porcentaje,data_total_cliente,data_total_freegames];
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
				$('#tabla_sec_recaudacion tfoot').html("<tr><td class='ftm4 tdft colunmasControl columna_nombre_local_modelo_cuatro sorting_1'>TOTAL:</td><td class='tdft colunmasControl columna_propietario_modelo_cuatro_body_td sorting_2'></td><td class='tdft colunmasControl columna_departamento_modelo_cuatro_body_td sorting_2'></td><td class='tdft colunmasControl columna_provincia_modelo_cuatro_body_td sorting_2'></td><td class='tdft colunmasControl columna_distrito_modelo_cuatro_body_td sorting_2'></td><td class='tdft columna_dias_modelo_cuatro_body_td sorting_2'></td><td class='tdft  canal_de_venta_modelo_cuatro'></td><td class='tdft'><span class='etotl'>T. Apostado</span><br><span class='etotv'>"+formatonumeros(data_total_apostado)+"</span></td><td class='tdft'><span class='etotl'>T. Ganado</span><br><span class='etotv'>"+formatonumeros(data_total_ganado)+"</span></td><td class='tdft'><span class='etotl'>T. Pagado</span><br><span class='etotv'>"+formatonumeros(data_total_pagado)+"</span></td><td class='tdft'><span class='etotl'>T. Producción</span><br><span class='etotv'>"+formatonumeros(data_total_produccion)+"</span></td><td class='tdft' style='border-bottom:1px solid #ddd !important;'></td><td class='tdft' style='border-bottom:1px solid #ddd !important;'></td></tr>");		});
		sec_recaudacion_mostrar_datatable(datafinal);
	}
	if (modelo==4) {
		var datafinal=[];
		var i = 0;
		$.each(obj, function(index, val) {
			$.each(val.locales, function(index1, val1) {
				$.each(val1.liquidaciones, function(index2, val2) {
					$.each(val2, function(index3, val3) {

						//console.log('canal_de_venta--> '+canales_de_venta[index3]);

						var data_canal_de_venta_id = val3.canal_de_venta_id;
						var data_cc_id = val1.cc_id;
						var data_local_id = val3.local_id;
						var data_nombre_local=val1.local_nombre;
						var data_dias_procesados = val1.dias_procesados;
						var data_canales_de_venta = canales_de_venta[index3];
						var data_num_tickets = val3.num_tickets;
						var data_total_depositado = formatonumeros(val3.total_depositado);
						var data_total_anulado_retirado = formatonumeros(val3.total_anulado_retirado);
						var data_total_apostado = formatonumeros(val3.total_apostado);
						var data_total_ganado = formatonumeros(val3.total_ganado);
						var data_total_pagado = formatonumeros(val3.total_pagado);
						var data_total_produccion = formatonumeros(val3.total_produccion);
						var data_resultado_negocio = formatonumeros(val3.resultado_negocio);
						var data_total_depositado_web = formatonumeros(val3.total_depositado_web);
						var data_total_retirado_web = formatonumeros(val3.total_retirado_web);
						var data_total_caja_web = formatonumeros(val3.total_caja_web);
						var data_porcentaje_cliente = val3.porcentaje_cliente;
						var data_total_cliente = formatonumeros(val3.total_cliente);
						var data_porcentaje_freegames = val3.porcentaje_freegames;
						var data_total_freegames = formatonumeros(val3.total_freegames);
						var data_pagado_en_otra_tienda = formatonumeros(val3.pagado_en_otra_tienda);
						//torito
						//var data_pagados_en_su_punto_propios = formatonumeros(val3.pagados_en_su_punto_propios);
						var difff_torito=0;
						if(canales_de_venta[index3]=='Torito'){
							var data_pagados_en_su_punto_propios = formatonumeros(0);
							difff_torito=val3.pagados_en_su_punto_propios;
						}else{
							var data_pagados_en_su_punto_propios = formatonumeros(val3.pagados_en_su_punto_propios);
						}
						//var data_pagados_en_su_punto_propios = formatonumeros(0);
						var data_pagado_de_otra_tienda = formatonumeros(val3.pagado_de_otra_tienda);
						var data_total_pagos_fisicos = formatonumeros(val3.total_pagos_fisicos);
						var data_caja_fisico = formatonumeros(val3.caja_fisico);
						var data_cashdesk_balance = formatonumeros(val3.cashdesk_balance);
						var data_test_balance = formatonumeros(val3.test_balance);						
						var data_test_diff = formatonumeros(val3.test_diff);
					
						var newObject =[
						data_cc_id,
						data_local_id,
						data_nombre_local,

						val1.propietario,
						val1.departamento,
						val1.provincia,
						val1.distrito,

						data_dias_procesados,
						data_canales_de_venta,
						data_num_tickets,
						data_total_depositado,

						data_total_anulado_retirado,
						data_total_apostado,
						data_total_ganado,
						data_total_pagado,
						data_total_produccion,
						data_resultado_negocio,

						data_total_depositado_web,
						data_total_retirado_web,
						data_total_caja_web,
						data_porcentaje_cliente,
						data_total_cliente,

						data_porcentaje_freegames,
						data_total_freegames,
						data_pagado_en_otra_tienda,
						data_pagados_en_su_punto_propios,
						data_pagado_de_otra_tienda,

						data_total_pagos_fisicos,
						data_caja_fisico,
						data_cashdesk_balance,
						data_test_balance,
						data_test_diff,
						data_canal_de_venta_id];						

						datafinal[i] =  newObject;
						i++;
					});
				});
			});
		});
		
		// console.log(obj);
		$.each(obj.data.totales, function(indgeneral, valgeneral) {
				var data_caja_fisico =formatonumeros(valgeneral.caja_fisico);
				var data_num_tickets =	formatonumeros(valgeneral.num_tickets);
				var data_pagado_de_otra_tienda =	formatonumeros(valgeneral.pagado_de_otra_tienda);
				valgeneral.pagado_en_otra_tienda=valgeneral.pagado_en_otra_tienda;
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
				var data_resultado_negocio	=formatonumeros(valgeneral.resultado_negocio);
				var data_total_retirado_web	=formatonumeros(valgeneral.total_retirado_web);
				var data_total_cashdesk_balance = formatonumeros(valgeneral.cashdesk_balance);
				var data_total_test_balance = 0;				
				var data_test_diff = formatonumeros(valgeneral.test_diff);	
				//$('tfoot').html("<tr><td class='colunmasControl columna_nombre_local_modelo_cuatro'><span style='color:#fff !important;'>TOTAL:</span></td><td class='colunmasControl columna_nombre_local_modelo_cuatro'><span></span><span style='visibility:hidden;'>#############################</span></td><td class='columnaprincipal columna_dias_modelo_cuatro_body_td' style='color: #337ab7 !important; background-color:#337ab7 !important; border-bottom:1px solid #ddd !important;'><span>####</span></td><td class='tdft canal_de_venta_modelo_cuatro_footer'><span><span style='visibility:hidden;'>#####################</span></span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Depositado:</span><br><span class='etotv'>"+data_total_depositado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. A. Retirado</span><br><span class='etotv'>"+data_total_anulado_retirado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T Apostado</span><br><span class='etotv'>"+data_total_apostado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Ganado</span><br><span class='etotv'>"+data_total_ganado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Pagado</span><br><span class='etotv'>"+data_total_pagado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Producción</span><br><span class='etotv'>"+data_total_produccion+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Dep. Web</span><br><span class='etotv'>"+data_total_depositado_web+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Ret. Web</span><br><span class='etotv'>"+data_total_retirado_web+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Caja Web</span><br><span class='etotv'>"+data_total_caja_web+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>P. Cliente</span><br><span class='etotv'>"+data_porcentaje_cliente+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Cliente</span><br><span class='etotv'>"+data_total_cliente+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>P. Freegames</span><br><span class='etotv'>"+data_porcentaje_freegames+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Freegames</span><br><span class='etotv'>"+data_total_freegames+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. P. en Otra Tienda</span><br><span class='etotv'>"+data_pagado_en_otra_tienda+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. P. de Otra Tienda</span><br><span class='etotv'>"+data_pagado_de_otra_tienda+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. P. Fisicos</span><br><span class='etotv'>"+data_total_pagos_fisicos+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Caja Fisico</span><br><span class='etotv'>"+data_caja_fisico+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Cash Balance</span><br><span class='etotv'>"+data_total_cashdesk_balance+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Test Balance</span><br><span class='etotv'>"+data_total_test_balance+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Test Diff</span><br><span class='etotv'>"+data_test_diff+"</span></td></tr>");								
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
						var data_nombre_local=val1.local_nombre;
						var data_canales_de_venta = canales_de_venta[index3];
						var data_tipo_punto = "";						
						var data_qty = "";
						var data_porcentaje_freegames = val3.porcentaje_freegames;
						var data_total_depositado = formatonumeros(val3.total_depositado);
						var data_total_anulado_retirado = formatonumeros(val3.total_anulado_retirado);
						var data_total_apostado = formatonumeros(val3.total_apostado);
						var data_tk_pagados_en_su_punto = "";
						var data_tk_pagados_en_otro_punto = formatonumeros(val3.pagado_en_otra_tienda);
						var data_total_pagado = formatonumeros(val3.total_pagado);
						var data_total_produccion = formatonumeros(val3.total_produccion);
						var data_caja_fisico = formatonumeros(val3.caja_fisico);
						var data_total_depositado_web = formatonumeros(val3.total_depositado_web);
						var data_total_retirado_web = formatonumeros(val3.total_retirado_web);
						var data_pagado_de_otra_tienda = formatonumeros(val3.pagado_de_otra_tienda);												
						var newObject =[
								data_nombre_local,
								data_canales_de_venta,
								data_tipo_punto,
								data_qty,
								data_porcentaje_freegames,	
								data_total_depositado,
								data_total_anulado_retirado,
								data_total_apostado,
								data_tk_pagados_en_su_punto,
								data_tk_pagados_en_otro_punto,
								data_total_pagado,	
								data_total_produccion,
								data_caja_fisico,
								data_total_depositado_web,
								data_total_retirado_web,
								data_pagado_de_otra_tienda	
						];
						datafinal[i] =  newObject;
						i++;
					});
				});
			});
		});
		// console.log(datafinal);
		// console.log(obj);
		$.each(obj.data.totales, function(indgeneral, valgeneral) {
				var data_tipo_punto = "";
				var data_qty = "";
				var data_porcentaje_freegames = formatonumeros(valgeneral.porcentaje_freegames);				
				var data_total_depositado	=formatonumeros(valgeneral.total_depositado);
				var data_total_anulado_retirado	=formatonumeros(valgeneral.total_anulado_retirado);
				var data_total_apostado	=formatonumeros(valgeneral.total_apostado);
				var data_pagado_en_su_punto ="";
				var data_pagado_en_otra_tienda =	formatonumeros(valgeneral.pagado_en_otra_tienda);
				var data_total_pagado	=formatonumeros(valgeneral.total_pagado);
				var data_total_produccion	=formatonumeros(valgeneral.total_produccion);				
				var data_caja_fisico =formatonumeros(valgeneral.caja_fisico);
				var data_total_depositado_web	=formatonumeros(valgeneral.total_depositado_web);
				var data_total_retirado_web	=formatonumeros(valgeneral.total_retirado_web);
				var data_pagado_de_otra_tienda =	formatonumeros(valgeneral.pagado_de_otra_tienda);
				$('#tabla_sec_recaudacion tfoot').html("<tr><td class='colunmasControl columna_nombre_local_modelo_cuatro'><span style='color:#fff !important;'>TOTAL:</span></td><td class='tdft canal_de_venta_modelo_cuatro_footer'><span><span style='visibility:hidden;'>345345345344451254444</span></span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'></span><br><span class='etotv'>"+data_tipo_punto+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'></span><br><span class='etotv'>"+data_qty+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>%</span><br><span class='etotv'>"+data_porcentaje_freegames+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>T. Depositado:</span><br><span class='etotv'>"+data_total_depositado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Anulado/Retirado</span><br><span class='etotv'>"+data_total_anulado_retirado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Total Apostado</span><br><span class='etotv'>"+data_total_apostado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>TK Pagado en su Punto</span><br><span class='etotv'>"+data_pagado_en_su_punto+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>TK Pagado en otro Punto</span><br><span class='etotv'>"+data_pagado_en_otra_tienda+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Total Premios Pagados</span><br><span class='etotv'>"+data_total_pagado+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Resultado del Negocio</span><br><span class='etotv'>"+data_total_produccion+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Caja</span><br><span class='etotv'>"+data_caja_fisico+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Deposito Web</span><br><span class='etotv'>"+data_total_depositado_web+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>Retirado Web</span><br><span class='etotv'>"+data_total_retirado_web+"</span></td><td class='tdft columnasnumeros_body_td'><span class='etotl'>TK Pagado de Otro Punto</span><br><span class='etotv'>"+data_pagado_de_otra_tienda+"</span></td></tr>");	
		});

		sec_recaudacion_mostrar_datatable(datafinal);	
	}
}
function sec_recaudacion_get_liquidaciones(){
	loading(true);
	
	modelo = $(".sec_recaudaciones_tipo_de_modelo").val();
	get_liquidaciones_data.filtro={};
	get_liquidaciones_data.filtro.fecha_inicio=$(".fecha_inicio_enviar").val();
	get_liquidaciones_data.filtro.hora_inicio=$(".hora_inicio_enviar").val();
	get_liquidaciones_data.filtro.fecha_fin=$(".fecha_fin_enviar").val();
	get_liquidaciones_data.filtro.hora_fin=$(".hora_fin_enviar").val();
	get_liquidaciones_data.filtro.locales = $(".local").val();
	get_liquidaciones_data.filtro.canales_de_venta=$('.canalventarecaudacion').val();
	get_liquidaciones_data.filtro.red_id=$('.red_recaudacion').val();
	get_liquidaciones_data.filtro.zona_id=$('.zona_recaudacion').val();
	get_liquidaciones_data.where="liquidaciones";


	if(url_object){
		if(url_object.query){
			if(url_object.query.proceso_unique_id){
				get_liquidaciones_data.filtro.proceso_unique_id=url_object.query.proceso_unique_id;
			}
		}
	}
	
	localStorage.setItem('at_liquidaciones_filtro_fecha_inicio', get_liquidaciones_data.filtro.fecha_inicio);
	localStorage.setItem('at_liquidaciones_filtro_fecha_fin', get_liquidaciones_data.filtro.fecha_fin);
	localStorage.setItem('at_liquidaciones_filtro_locales', get_liquidaciones_data.filtro.locales);
	localStorage.setItem('at_liquidaciones_filtro_canales_de_venta', get_liquidaciones_data.filtro.canalesventa);
	auditoria_send({"proceso":"sec_recaudacion_get_liquidaciones","data":get_liquidaciones_data});

	$.ajax({
		data: get_liquidaciones_data,
		type: "POST",
		url: "/api/?json",
		async: "false"
	})
	.done(function(responsedata, textStatus, jqXHR ) {
		console.log(responsedata);
		try{
			var obj = jQuery.parseJSON(responsedata);
			sec_recaudacion_process_data(obj);	
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
			console.log( "La solicitud liquidaciones a fallado: " +  textStatus);
		}
	});
}
function sec_recaudacion_tickets_comision_cuota(fecha_inicio,fecha_fin,canal_de_venta_id,local_id,columna){
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

  // console.log(data_tickets_comision_cuota);

  $.ajax({
	url: '/api/?json',
	type: 'POST',
	dataType: 'json',
	data: data_tickets_comision_cuota
  })
  .done(function(obj) {
	try{
	  console.log(obj);
	  sec_recaudacion_get_data_comision_cuota(obj); 
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
function sec_recaudacion_get_data_comision_cuota(obj){
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
    		var newObj =[data_bet_id,data_bet_number,data_currency,data_stake,data_odds,data_percent,data_comision,data_winnings,data_type,data_state,data_created,data_fecha_apostado,data_calc_date,data_is_live,data_paiddate,data_paid_cash_desk_name];            
			  dataf[i] =  newObj;
			i++;
		});
	  } 
	});
	// console.log(dataf);
	sec_recaudacion_tickets_comision_cuota_table(dataf);  
}
function sec_recaudacion_tickets_comision_cuota_table(model){
	table_cct = $('#table_tickets_comision_cuota').DataTable({ 
		bRetrieve: true,
		lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],
		paging: true,       
		searching: true,
		sPaginationType: "full_numbers",
		Sorting: [[1, 'asc']], 
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
			  var total_comisiones =  api.column(columns[i], {filter: 'applied'}).data().sum().toFixed(2);
			  var total_pagina_comisiones = api.column(columns[i], { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
			  if (total_comisiones<0 && total_pagina_comisiones < 0){
				$('#table_tickets_comision_cuota tfoot th').eq(columns[i]).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total_comisiones) +'<span><br>');
			  }
			  else{
				$('#table_tickets_comision_cuota tfoot th').eq(columns[i]).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total_comisiones) +'<span><br>');
			  }
		  }
		},  
		pageLength: '16',                     
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
function sec_recaudacion_validacion_permisos_usuarios(btn){
	$(document).on("evento_validar_permiso_usuario",function(event) {
	  $(document).off("evento_validar_permiso_usuario");
	  console.log("EVENT: evento_validar_permiso_usuario");

	  console.log(event.event_data);

	  if (event.event_data==true) {
		console.log(event.event_data);
		var selectionslocales = $(".local").select2('data').text; 
		var selectionscanalventarecaudacion = $(".canalventarecaudacion").select2('data').text;  
		sec_recaudacion_get_liquidaciones();
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