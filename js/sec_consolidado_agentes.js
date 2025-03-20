var at_consolidado_agentes_filtro_locales = false;
var at_consolidado_agentes_filtro_canales_de_venta = false;
function sec_consolidado_agentes(){
	if( sec_id == "consolidado_agentes") {
		loading(true);
		sec_consolidado_agentes_events();
		sec_consolidado_agentes_settings();
		sec_consolidado_agentes_get_locales();
		sec_consolidado_agentes_get_agentes();
		loading();
		function sec_consolidado_agentes_get_locales(){
		  var data = {};
		  data.what={};
		  data.what[0] = "id";
		  data.what[1] = "nombre";
		  data.where = "locales";
		  data.filtro = {red_id: [5]}
			$.ajax({
			  data: data,
			  type: "POST",
			  dataType: "json",
			  url: "/api/?json",
			  async: "false"
			})
			.done(function( data, textStatus, jqXHR ) {
			  try{
				  $.each(data.data,function(index,val){
					var new_option = $("<option>");
					$(new_option).val(val.id);
					$(new_option).html(val.nombre);
					$("#sec_consolidado_agentes_local").append(new_option);
				  });
				  $('#sec_consolidado_agentes_local').select2({closeOnSelect: false});
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
				console.log( "La solicitud locales a fallado: " +  textStatus);
			})
		}
		function sec_consolidado_agentes_get_agentes(){
		  var data = {};		  		  		
		  data.opt = "get_agentes";
			$.ajax({
			  data: data,
			  type: "POST",
			  dataType: "json",
			  url: "/sys/get_consolidado_agentes.php",
			})
			.done(function( data, textStatus, jqXHR ) {
			  try{
				  $.each(data.listado,function(index,val){
					var new_option = $("<option>");
					$(new_option).val(val.nombre);
					$(new_option).html(val.nombre);
					$("#sec_consolidado_agentes_agente").append(new_option);
				  });
				  $('#sec_consolidado_agentes_agente').select2({closeOnSelect: false});
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
				console.log( "La solicitud locales a fallado: " +  textStatus);
			})
		}
		function sec_consolidado_agentes_settings(){
			$('.sec_consolidado_agentes_select2').select2({
			  closeOnSelect: false,            
			  allowClear: true,
			});
			$('.sec_consolidado_agentes_concepto').select2({
				closeOnSelect: false,
			  	allowClear: false
			});

			if(localStorage.getItem("at_consolidado_agentes_filtro_locales")){
			  at_consolidado_agentes_filtro_locales = localStorage.getItem("at_consolidado_agentes_filtro_locales");
			}
			if(localStorage.getItem("at_consolidado_agentes_filtro_canales_de_venta")){
			  at_consolidado_agentes_filtro_canales_de_venta = localStorage.getItem("at_consolidado_agentes_filtro_canales_de_venta");
			}
		}
		function sec_consolidado_agentes_events(){
			$(".btnfiltarconsolidado")
				.off("click")
				.on("click",function(){
					loading(true);    
					sec_consolidado_agentes_get_liquidaciones();
				});
		}

		function sec_consolidado_agentes_get_liquidaciones(){
			loading(true);
			var get_liquidaciones_data = {};
			get_liquidaciones_data.filtro = {};
			get_liquidaciones_data.filtro.locales = $("#sec_consolidado_agentes_local").val();
			get_liquidaciones_data.filtro.canales_de_venta = $('#sec_consolidado_agentes_cdv').val();
			get_liquidaciones_data.filtro.agente = $('#sec_consolidado_agentes_agente').val();
			get_liquidaciones_data.filtro.concepto = $('#sec_consolidado_agentes_concepto').val();
			if($("#sec_consolidado_agentes_estado_locales").hasClass('btn-success')) {
				get_liquidaciones_data.filtro.estado_locales = "inactivos";
			} else {
				get_liquidaciones_data.filtro.estado_locales = "activos";
			}
			
			get_liquidaciones_data.opt = "consolidado_agentes";

			localStorage.setItem('at_consolidado_agentes_filtro_locales', get_liquidaciones_data.filtro.locales);
			localStorage.setItem('at_consolidado_agentes_filtro_canales_de_venta', get_liquidaciones_data.filtro.canalesventa);
			auditoria_send({"proceso":"sec_consolidado_agentes_get_liquidaciones","data":get_liquidaciones_data});

			$.ajax({
				data: get_liquidaciones_data,
				type: "POST",
				url: "/sys/get_consolidado_agentes.php",
				async: "false"
			})
			.done(function(responsedata, textStatus, jqXHR ) {
				try{
					var response = jQuery.parseJSON(responsedata);
					var obj = response.data;
					sec_consolidado_agentes_mostrar_datatable(obj);
				}catch(err){
					console.log(err);
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
				console.log( "La solicitud liquidaciones a fallado: " +  textStatus);
			});
		}

		function sec_consolidado_agentes_mostrar_datatable(obj){
			var datatable_data = obj.datatable_data;
			var meses = obj.meses ;
			var totales = obj.totales;

			$('#tabla_sec_consolidado_agente tfoot').empty();
			$('#tabla_sec_consolidado_agente tfoot').append("<tr>");
			$('#tabla_sec_consolidado_agente tfoot tr')
				.append("<th>")
				.append("<th>")
				.append("<th>")
				.append("<th>")
				.append("<th>")
				.append("<th>")
				.append("<th>")
				;

			$(meses).each(function(i,e){
				$('#tabla_sec_consolidado_agente tfoot tr')
				.append("<th></th>")
			});	
			var columnas = [];
			columnas.push({title : "NOMBRE TIENDA" , data : "NOMBRE TIENDA"  , className : "tabla_sec_consolidado_agente_local_td" ,defaultContent: "---" 	});
			columnas.push({title : "NOMBRE AGENTE" , data : "NOMBRE AGENTE"  , className : "tabla_sec_consolidado_agente_local_td" ,defaultContent: "---" 	});
			columnas.push({title : "DEPARTAMENTO" , data : "DEPARTAMENTO"  , className : "tabla_sec_consolidado_agente_local_td" ,defaultContent: "---" 	});
			columnas.push({title : "PROVINCIA" , data : "PROVINCIA"  , className : "tabla_sec_consolidado_agente_local_td" ,defaultContent: "---" 	});
			columnas.push({title : "DISTRITO" , data : "DISTRITO"  , className : "tabla_sec_consolidado_agente_local_td" ,defaultContent: "---" 	});
			columnas.push({title : "ZONA" , data : "ZONA"  , className : "tabla_sec_consolidado_agente_local_td" ,defaultContent: "---" 	});
			columnas.push({title : "CANAL DE VENTA" , data : "CANAL DE VENTA" ,className : "tabla_sec_consolidado_agente_cdv_td" ,defaultContent: "---" 	});
			$(meses).each(function(i,e){
				columnas.push({
					//title: e,
					title : moment(e, "YYYY-MM").locale("es").format("MMMYY").replace(".","-"),
					data : e,
					className : "text-right tabla_sec_consolidado_agente_tfoot_meses",
					defaultContent: "-",
						render: function ( data, type, row, meta ) {
							let formatter = new Intl.NumberFormat('en-US', {
								maximumFractionDigits: 3,
								minimumFractionDigits: 2
							});
							return formatter.format(data);
						}
					})
				;
			})
			//$.fn.dataTable.ext.errMode = 'none';
			table_dt = $('#tabla_sec_consolidado_agente').DataTable
			(
				{
					/*"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
						//return iStart +" to "+ iEnd;
						var nro_cdv = $("#sec_consolidado_agentes_cdv").val() ? $("#sec_consolidado_agentes_cdv").val().length : 4;
						var nro_locales = parseInt(iEnd) / (parseInt(nro_cdv) + 1 );
						var max_locales = iTotal / (parseInt(nro_cdv) + 1 );
						var string_loc = nro_locales > 1 ? "local " : "locales";
						var desde = Math.ceil( iStart / nro_cdv );
						var hasta = Math.ceil( (parseINt(iEnd) - iStart) / nro_cdv );
						return "Mostrando " + desde + " a " + hasta + " " + string_loc + " de " + max_locales + " registros";
						//return "Mostrando del " + iStart + " al " + iEnd + " de " + iMax + " entradas.";
					},*/
	              	"bDestroy": true,
					scrollX : true,
					fixedColumns:   
					{
						leftColumns: 7
					},
					lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],
					paging: true,	      
					searching: true,
					bSort: false,
					sPaginationType: "full_numbers",
					Sorting: [[1, 'asc']],
					rowsGroup: [0,1,2,3,4,5],
					columns : columnas,
					data: datatable_data,
					//dom: 'Blrftip',
                	//sDom:"<'row'<'col-sm-12'B>><'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                	sDom:"<'row'<'col-sm-2'l><'col-sm-6'B><'col-sm-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
					buttons: 
					[
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
						    ,filename: $(".page-title").text().trim().replace(/ /g, '_') + "_" +  moment(new Date()).format("YYYY_MM_DD_HH_mm_ss")
						},
						{   extend: 'excelHtml5',
						    text:'Excel',
						    footer: true,
						    className: 'excelButton'
						    ,filename: $(".page-title").text().trim().replace(/ /g, '_') + "_" +  moment(new Date()).format("YYYY_MM_DD_HH_mm_ss")
						    ,customize: function(xlsx) {
						        var sheet = xlsx.xl.worksheets['sheet1.xml'];
						        $('row:first c', sheet).attr( 's', '22' );
						        $('row c', sheet).each( function () {
						            if ( $('is t', this).text() == 'TOTAL' ) {
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
						var api = this.api();
						for (var i = 7; i < columnas.length; i++) {
							var total =  api.column( i , {filter: 'applied'}).data().sum().toFixed(2);
							var total_pagina = api.column( i , { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
								if (total < 0 && total_pagina < 0){
									$(api.column( i ).footer()).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total/2) +'<span><br>');
								}
								else{
									$(api.column( i ).footer()).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total/2) +'<span><br>');
								}
						}
					},
					fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ){
						if ( aData["CANAL DE VENTA"] == "TOTAL" )
						{
							$('td', nRow).css( 'cursor', 'default','important' );	
						    $('td', nRow).css('background-color','#9BDFFD','important');
						    $('td', nRow).css('color','#080FFC'); 
						    $('td', nRow).css('font-weight','800');                       
						}
					},
					createdRow: function ( row, data, index ) {
					},
					columnDefs: [
						{
							aTargets: 'tabla_sec_consolidado_agente_tfoot_meses',
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
								if ( sData < "0" ) {
									$(nTd).css('color', 'red')
									$(nTd).css('font-weight', 'bold')
								}
							}
						},
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
				}
			);

			/*$(function() {
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
			});*/
			loading();
		}

		$("#sec_consolidado_agentes_estado_locales").on('click', function(event){
			event.preventDefault();
			if($(this).hasClass('btn-danger')){
				$(this).removeClass('btn-danger');
				$(this).addClass('btn-success');
				$(this).text('Mostrar Activos');
			}
			else{
				$(this).removeClass('btn-success');
				$(this).addClass('btn-danger');
				$(this).text('Mostrar Inactivos');
			}
			sec_consolidado_agentes_get_liquidaciones();
		});
	}
};

