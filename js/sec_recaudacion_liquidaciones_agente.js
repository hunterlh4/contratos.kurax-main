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
var at_liquidaciones_filtro_fecha_inicio = moment().subtract(1, 'days').format("YYYY-MM-DD");
var at_liquidaciones_filtro_fecha_fin = moment().subtract(1, 'days').format("YYYY-MM-DD");;
var at_liquidaciones_filtro_locales = false;
var at_liquidaciones_filtro_canales_de_venta = false;
var column_name = false;
var count = 0;
var $table_tck = false;
function sec_recaudacion_liquidaciones_agente(){
	if(sec_id == "recaudacion_liquidaciones_agentes") {
		loading(true);
		sec_recaudacion_liquidaciones_agente_events();
		sec_recaudacion_liquidaciones_agente_settings();
		sec_recaudacion_get_locales();
		sec_recaudacion_get_canales_venta(); 
		loading();

		/*fn*/
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
					// if( $.inArray( parseInt(val.id) , [16,17,21,30,33,34,37,42,43]) === -1 ){
					// 	return;
					// }
					canales_de_venta[val.id]=val.codigo;
					var new_option = $("<option>");
					$(new_option).val(val.id);
					$(new_option).html(val.codigo);
					$(".canalventarecaudacion").append(new_option);

				  });
				  $('.canalventarecaudacion').select2({closeOnSelect: false});
				}
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
		function sec_recaudacion_liquidaciones_agente_settings(){

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
		function sec_recaudacion_liquidaciones_agente_events(){
			console.log("sec_recaudacion_liquidaciones_agente_events");

			$(".btnfiltarrecaudacion")
				.off()
				.on("click",function(){
					loading(true);    
					var btn =  $(this).data("button");
					sec_recaudacion_get_liquidaciones_agente();
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
		function sec_recaudacion_agente_mostrar_datatable(model){
			var heightdoc = window.innerHeight;
			var heightnavbar= $(".navbar-header").height();
				
			var heighttable =heightdoc-heightnavbar-300;
			
			$.fn.dataTable.ext.errMode = 'none';
			table_dt = $('#tabla_sec_recaudacion').DataTable({
				scrollX:true,
				fixedColumns:   {
					leftColumns: 5
				},
				bRetrieve: true,
				lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],
				paging: true,	      
				searching: true,
				sPaginationType: "full_numbers",
				Sorting: [[1, 'asc']], 
				rowsGroup: [0,1,2,3],
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
					columns = [5,6,7,8,9,10,11,12,13,14,15,17,19]; 
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
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
					if ( aData[4] == "Total" )
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
					if (data[4]=="Total") {
						$('td', row).eq(25).addClass('test_diff_background_color');
					};
				},	      
				columnDefs: [
					{ className: "local_id", "targets": [0] },
					{ className: "local_nombre", "targets": [1] },
					{ className: "dias", "targets": [2] },
					{ className: "zona_nombre", "targets": [3] },
					{ className: "canales_de_venta", "targets": [4] },
					{ className: "total_apostado", "targets": [5] },
					{ className: "total_ganado", "targets": [6] },
					{ className: "total_pagado", "targets": [7] },
					{ className: "total_produccion", "targets": [8] },
					{ className: "saldo_arrastrar_anterior_mes", "targets": [9] },
					{ className: "resultado_negocio", "targets": [10] },
					{ className: "base_imponible_mntto", "targets": [11] },
					{ className: "impuesto", "targets": [12] },
					{ className: "saldo_arrastrar_siguiente_mes", "targets": [13] },
					{ className: "base_calculo", "targets": [14] },
					{ className: "total_depositado_web", "targets": [15] },
					{ className: "porcentaje_cliente", "targets": [16] },
					{ className: "participacion_cliente", "targets": [17] },
					{ className: "porcentaje_freegames", "targets": [18] },
					{ className: "participacion_freegames", "targets": [19] },

					{ sortable: false,"class": "index",targets: [0]},
					{ sortable: true, "targets": [0] },
					{
						aTargets: [5,6,7,8,9,10,11,12,13,14,15,16,17,18,19],
						fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
							if ( sData < "0" ) {
								$(nTd).css('color', 'red')
								$(nTd).css('font-weight', 'bold')
							}
						}
					},
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
		function sec_recaudacion_agente_process_data(obj){
			var datafinal=[];
			var i = 0;
			$.each(obj, function(index, val) {
				$.each(val.locales, function(index1, val1) {
					$.each(val1.liquidaciones, function(index2, val2) {
						$.each(val2, function(index3, val3) {
							//console.log('canal_de_venta--> '+canales_de_venta[index3]);
							var data_canal_de_venta_id = val3.canal_de_venta_id;
							var data_local_id = val3.local_id;
							var data_nombre_local=val1.local_nombre;
							var data_dias_procesados = val1.dias_procesados;
							var data_zona_nombre = val1.zona_nombre;
							var data_canales_de_venta = canales_de_venta[index3];
							var data_total_depositado = formatonumeros(val3.total_depositado);
							var data_total_anulado_retirado = formatonumeros(val3.total_anulado_retirado);
							var data_total_apostado = formatonumeros(val3.total_apostado);
							var data_total_ganado = formatonumeros(val3.total_ganado);
							var data_total_pagado = formatonumeros(val3.total_pagado);
							var data_total_produccion = formatonumeros(val3.total_produccion);
							var data_resultado_negocio = formatonumeros(val3.resultado_negocio);
							var data_base_imponible_mntto = formatonumeros(val3.base_imponible_mntto);
							var data_impuesto = formatonumeros(val3.impuesto);
							var data_base_calculo = formatonumeros(val3.base_calculo);
							var data_total_depositado_web = formatonumeros(val3.total_depositado_web);
							var data_total_retirado_web = formatonumeros(val3.total_retirado_web);
							var data_total_caja_web = formatonumeros(val3.total_caja_web);
							var data_porcentaje_cliente = val3.porcentaje_cliente;
							var data_total_cliente = formatonumeros(val3.participacion_cliente);
							var data_porcentaje_freegames = val3.porcentaje_freegames;
							var data_total_freegames = formatonumeros(val3.participacion_freegames);
							var data_pagado_en_otra_tienda = formatonumeros(val3.pagado_en_otra_tienda);
							var data_saldo_arrastrar_siguiente_mes = formatonumeros(val3.saldo_arrastrar_siguiente_mes);
							var data_saldo_arrastrar_anterior_mes = formatonumeros(val3.saldo_arrastrar_anterior_mes);
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
							data_local_id,
							data_nombre_local,
							data_dias_procesados,
							data_zona_nombre,
							data_canales_de_venta,
							data_total_apostado,
							data_total_ganado,
							data_total_pagado,
							data_total_produccion,
							data_saldo_arrastrar_anterior_mes,
							data_resultado_negocio,
							data_base_imponible_mntto,
							data_impuesto,
							data_saldo_arrastrar_siguiente_mes,
							data_base_calculo,
							data_total_depositado_web,
							data_porcentaje_cliente,
							data_total_cliente,
							data_porcentaje_freegames,
							data_total_freegames
							]

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
			
			sec_recaudacion_agente_mostrar_datatable(datafinal);

		}
		function sec_recaudacion_get_liquidaciones_agente(){
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
			auditoria_send({"proceso":"sec_recaudacion_get_liquidaciones_agente","data":get_liquidaciones_data});

			$.ajax({
				data: get_liquidaciones_data,
				type: "POST",
				url: "/api/?json",
				async: "false"
			})
			.done(function(responsedata, textStatus, jqXHR ) {
				//console.log(responsedata);
				try{
					var obj = jQuery.parseJSON(responsedata);
					sec_recaudacion_agente_process_data(obj);	
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
		/*fin fn*/


	}
};