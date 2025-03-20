function sec_recaudacion_liquidaciones_cont_agentes() {
    if (sec_id == "recaudacion_liquidaciones_cont_agentes") {
        var get_liquidaciones_cont_agente_data = {};
        loading(true);
        sec_recaudacion_liquidaciones_cont_agente_events();
		sec_recaudacion_liquidaciones_cont_agente_settings();
        sec_recaudacion_cont_agente_get_locales();
        sec_recaudacion_cont_agente_get_canales_venta();
        sec_recaudacion_cont_agente_get_redes();
	    sec_recaudacion_cont_agente_get_zonas();
        loading();

        function sec_recaudacion_cont_agente_get_canales_venta(){
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
							/*
                            if( $.inArray( parseInt(val.id) , [16,17,19,21,30,33,34,37]) === -1 ){
                                return;
                            }
							*/
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

        function sec_recaudacion_cont_agente_get_locales(){
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

        function sec_recaudacion_cont_agente_get_redes(){
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

        function sec_recaudacion_cont_agente_get_zonas(){
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

        function sec_recaudacion_liquidaciones_cont_agente_settings() {
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
  
            if(localStorage.getItem("at_liquidaciones_filtro_locales")){
                at_liquidaciones_filtro_locales = localStorage.getItem("at_liquidaciones_filtro_locales");
            }
            if(localStorage.getItem("at_liquidaciones_filtro_canales_de_venta")){
                at_liquidaciones_filtro_canales_de_venta = localStorage.getItem("at_liquidaciones_filtro_canales_de_venta");
            }

            $(".filtro_datepicker").datepicker({
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
            $("#input_text-liq_filtro_inicio_fecha").datepicker("setDate", m_liq_filtro_inicio_fecha).trigger('change');

            var m_liq_filtro_fin_fecha = moment(at_liquidaciones_filtro_fecha_fin).format("DD-MM-YYYY");
			$("#input_text-liq_filtro_fin_fecha").datepicker("setDate", m_liq_filtro_fin_fecha).trigger('change');
        }

        function sec_recaudacion_liquidaciones_cont_agente_events() {
            console.log("sec_recaudacion_liquidaciones_cont_agente_events");
            $(".btnfiltarrecaudacion").off().on("click",function(){
                loading(true);
                var btn =  $(this).data("button");
                sec_recaudacion_get_liquidaciones_cont_agente();
			});
        }

        function sec_recaudacion_get_liquidaciones_cont_agente() {
            loading(true);
			
			get_liquidaciones_cont_agente_data.filtro={};
			get_liquidaciones_cont_agente_data.filtro.fecha_inicio=$(".fecha_inicio_enviar").val();
			get_liquidaciones_cont_agente_data.filtro.hora_inicio=$(".hora_inicio_enviar").val();
			get_liquidaciones_cont_agente_data.filtro.fecha_fin=$(".fecha_fin_enviar").val();
			get_liquidaciones_cont_agente_data.filtro.hora_fin=$(".hora_fin_enviar").val();
			get_liquidaciones_cont_agente_data.filtro.locales = $(".local").val();
			get_liquidaciones_cont_agente_data.filtro.canales_de_venta=$('.canalventarecaudacion').val();
			get_liquidaciones_cont_agente_data.filtro.red_id=$('.red_recaudacion').val();
			get_liquidaciones_cont_agente_data.filtro.zona_id=$('.zona_recaudacion').val();
			get_liquidaciones_cont_agente_data.where="liquidaciones";


			if(url_object){
				if(url_object.query){
					if(url_object.query.proceso_unique_id){
						get_liquidaciones_cont_agente_data.filtro.proceso_unique_id=url_object.query.proceso_unique_id;
					}
				}
			}
			
			localStorage.setItem('at_liquidaciones_filtro_fecha_inicio', get_liquidaciones_cont_agente_data.filtro.fecha_inicio);
			localStorage.setItem('at_liquidaciones_filtro_fecha_fin', get_liquidaciones_cont_agente_data.filtro.fecha_fin);
			localStorage.setItem('at_liquidaciones_filtro_locales', get_liquidaciones_cont_agente_data.filtro.locales);
			localStorage.setItem('at_liquidaciones_filtro_canales_de_venta', get_liquidaciones_cont_agente_data.filtro.canalesventa);
			auditoria_send({"proceso":"sec_recaudacion_get_liquidaciones_agente","data":get_liquidaciones_cont_agente_data});

			$.ajax({
				data: get_liquidaciones_cont_agente_data,
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
        function sec_recaudacion_agente_process_data(obj){
			var datafinal=[];
			var i = 0;
			$.each(obj, function(index, val) {
				$.each(val.locales, function(index1, val1) {
					$.each(val1.liquidaciones, function(index2, val2) {
						$.each(val2, function(index3, val3) {
							//console.log('canal_de_venta--> '+canales_de_venta[index3]);
                            var data_local_id = val3.local_id;
                            var data_cc_id = val1.cc_id;
                            var data_nombre_local=val1.local_nombre;
							var data_razon_social=val1.razon_social;
							var data_ruc=val1.ruc;
                            var data_canal_de_venta_id = val3.canal_de_venta_id;
                            var data_canales_de_venta = canales_de_venta[index3];
                            var data_num_tickets = val3.num_tickets;
                            var data_num_tickets_pendientes = val3.num_tickets_pendientes;
                            var data_efectivo_tickets_pendientes = formatonumeros(val3.efectivo_tickets_pendientes);
                            var data_total_apostado = formatonumeros(val3.total_apostado);
                            var data_total_ganado = formatonumeros(val3.total_ganado);
							var data_GGR = formatonumeros(val3.GGR);
                            var data_resultado_negocio = formatonumeros(val3.resultado_negocio);
							var data_base_imponible_mntto = formatonumeros(val3.base_imponible_mntto);
							var data_impuesto = formatonumeros(val3.impuesto);
							// var data_impuesto_juego = formatonumeros(val3.impuesto_juego);
							var data_base_calculo = formatonumeros(val3.base_calculo);
							var data_porcentaje_cliente = val3.porcentaje_cliente;
							var data_porcentaje_freegames = val3.porcentaje_freegames;
							var data_participacion_cliente = formatonumeros(val3.participacion_cliente);
							var data_participacion_freegames = formatonumeros(val3.participacion_freegames);
							var data_saldo_arrastrar_siguiente_mes = formatonumeros(val3.saldo_arrastrar_siguiente_mes);
							var data_saldo_arrastrar_anterior_mes = formatonumeros(val3.saldo_arrastrar_anterior_mes);
							// var data_acumulado = val3.base_calculo < 0 ? 0.00 : 2;
						
							var newObject =[
                            data_cc_id,
							data_nombre_local,
                            data_razon_social,
                            data_ruc,
                            data_canales_de_venta,
                            data_num_tickets,
                            data_num_tickets_pendientes,
                            data_efectivo_tickets_pendientes,
                            data_total_apostado,
                            data_total_ganado,
							data_saldo_arrastrar_anterior_mes,
							data_GGR,
                            data_resultado_negocio,
							data_base_imponible_mntto,
							data_impuesto,
							// data_impuesto_juego,
							data_saldo_arrastrar_siguiente_mes,
							data_base_calculo,
                            data_porcentaje_cliente,
                            // data_total_cliente,
							data_participacion_cliente,
                            data_porcentaje_freegames,
							data_participacion_freegames
                            // data_total_freegames
							]

							datafinal[i] =  newObject;
							i++;
						});
					});
				});
			});
			// console.log(obj);
			sec_recaudacion_cont_agente_mostrar_datatable(datafinal);

		}
        function sec_recaudacion_cont_agente_mostrar_datatable(model){
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
					columns = [5,6,7,8,9,10,11,12,13,14,15,17,19,20];
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
					{ className: "cc_id", "targets": [0] },
					{ className: "local_nombre", "targets": [1] },
					{ className: "razon_social", "targets": [2] },
					{ className: "ruc", "targets": [3] },
					{ className: "canales_de_venta", "targets": [4] },
					{ className: "num_tickets", "targets": [5] },
					{ className: "num_tickets_pendientes", "targets": [6] },
					{ className: "efectivo_tickets_pendientes", "targets": [7] },
					{ className: "total_apostado", "targets": [8] },
					{ className: "total_ganado", "targets": [9] },
					{ className: "saldo_arrastrar_anterior_mes", "targets": [10] },
					{ className: "GGR", "targets": [11] },
					{ className: "resultado_negocio", "targets": [12] },
					{ className: "base_imponible_mntto", "targets": [13] },
					{ className: "impuesto", "targets": [14] },
					// { className: "impuesto_juego", "targets": [13] },
					{ className: "saldo_arrastrar_siguiente_mes", "targets": [15] },
					{ className: "base_calculo", "targets": [16] },
					{ className: "porcentaje_cliente", "targets": [17] },
					{ className: "participacion_cliente", "targets": [18] },
					{ className: "porcentaje_freegames", "targets": [19] },
					{ className: "participacion_freegames", "targets": [20] },

					{ sortable: false,"class": "index",targets: [0]},
					{ sortable: true, "targets": [0] },
					{
						aTargets: [5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20],
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
		                },
						"colvisRestore": "Restaurar Visibilidad"
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
    }
}