function sec_reportes_en_de() {
	console.log("sec_reportes_pagados_en_de_otras_tiendas");
	loading(true);
	sec_reportes_pagados_en_de_otras_tiendas_get_canales_venta();
	sec_reportes_pagados_en_de_otras_tiendas_get_zonas();
	sec_reportes_pagados_en_de_otras_tiendas_get_locales();
	sec_reportes_pagados_en_de_otras_tiendas_get_redes();	
	datatable_events();

	loading();

}

function sec_reportes_pagados_en_de_otras_tiendas_get_redes(){
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
					$(".red_reporte_pagados_de_otras_tiendas").append(new_option);
				});
				$('.red_reporte_pagados_de_otras_tiendas').select2({closeOnSelect: false});
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
function sec_reportes_pagados_en_de_otras_tiendas_get_canales_venta(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="codigo";
	data.where="canales_de_venta";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_pagados_de_otras_tiendas_get_canales_venta","data":data});
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
						$(".canal_venta_reporte_pagados_de_otras_tiendas").append(new_option);

					});
					$('.canal_venta_reporte_pagados_de_otras_tiendas').select2({closeOnSelect: false});
				/*	$('.canal_venta_reporte_pagados_de_otras_tiendas').on("change",function(val){
						if($.inArray("30",$('#canal_venta').val())){
							 $('#canal_venta').val(30).trigger("change");
						}
					})*/
					/*  $('.canal_venta_reporte_pagados_de_otras_tiendas').on('select2:selecting', function(e) {
					    console.log('Selecting: ' , e.params.args.data);
						if(e.params.args.data.text=="Bingo"){
							 $('#canal_venta').val(30).trigger("change");
						}
						else{
							if($.inArray("30",$('#canal_venta').val())>-1){
							   $("#canal_venta").find("option[value='30']").prop("selected",false);
								$("#canal_venta").trigger("change");
							}

						}

					  });*/
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
				console.log( "La solicitud canales de ventas a fallado: " +  textStatus);
			}
		})
}
function sec_reportes_pagados_en_de_otras_tiendas_get_locales(){
	var data = {};
	data.what={};
	data.what[0]="id";
	data.what[1]="nombre";
	data.where="locales";
	data.filtro={}
		auditoria_send({"proceso":"sec_reportes_pagados_de_otras_tiendas_get_locales","data":data});
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
						$(".local_reporte_pagados_de_otras_tiendas").append(new_option);
					});
					$('.local_reporte_pagados_de_otras_tiendas').select2({closeOnSelect: false});
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

function sec_reportes_pagados_en_de_otras_tiendas_get_zonas(){
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
					$(".zona_reporte_pagados_de_otras_tiendas").append(new_option);
				});
				$('.zona_reporte_pagados_de_otras_tiendas').select2({closeOnSelect: false});
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


function iniciar_datatable(){

	opciones1={
		jsonop:   {
			fn:"dt_tickets_de_otras_tiendas",
            where : "tickets_de_otras_tiendas"
        },
        col_agrup:0,
        columns:[
			{data:"locpago_nombre",name:"TIENDA DE PAGO",title:"TIENDA DE PAGO", className:"sec_rep_pdot_tienda_pago","bSortable": false},
			{data:"nombrerazlocpago",name:"RAZÓN SOCIAL",title:"RAZÓN SOCIAL "},
			{data:"canal_venta",name:"CANAL VENTA",title:"CANAL VENTA" },
			{data:"locorigen_nombre",name:"TIENDA DE ORIGEN",title:"TIENDA DE ORIGEN" },
			{data:"nombrerazlocorigen",name:"RAZÓN SOCIAL",title:"RAZÓN SOCIAL"},
			{data:"ganado",name:"PAGOS DE OTRAS TIENDAS",title:"PAGOS DE OTRAS TIENDAS",className:""}
        ]
	};
	     
	tablaserver1=listar_tickets_de_en("tbl_de",opciones1);

	opciones2={
		jsonop:  
		{
			fn:"dt_tickets_en_otras_tiendas",
        	where : "tickets_en_otras_tiendas"
        },
        col_agrup:0,
		columns:[
				{data:"locorigen_nombre",name:"TIENDA DE ORIGEN",title:"TIENDA DE ORIGEN", className:"sec_rep_pdot_tienda_pago" },
				{data:"nombrerazlocorigen",name:"RAZÓN SOCIAL",title:"RAZÓN SOCIAL"},
				{data:"canal_venta",name:"CANAL VENTA",title:"CANAL VENTA" },
				{data:"locpago_nombre",name:"TIENDA DE PAGO",title:"TIENDA DE PAGO" },
				{data:"nombrerazlocpago",name:"RAZÓN SOCIAL",title:"RAZÓN SOCIAL "},			
				{data:"ganado",name:"PAGOS EN OTRAS TIENDAS",title:"PAGOS EN OTRAS TIENDAS",className:"sec_rep_pdot_cantidad_pagado"}
		 
		        ]
	};
	tablaserver2=listar_tickets_de_en("tbl_en",opciones2);
}

function datatable_events(){
	$('.datep')
	.datepicker({
		dateFormat:'dd-mm-yy',
		changeMonth: true,
		changeYear: true
	});
	/*.on("change", function(ev) {
  	   filtrofecha(tablaserver1,
            $(this).attr("data-columna"),
            false
        );
  	   filtrofecha(tablaserver2,
            $(this).attr("data-columna"),
            false
        );
	});
	 $('#filtros select').on('change', function (i, e) {
        filtrarselect(tablaserver1, this, true, false);
        filtrarselect(tablaserver2, this, true, false);
    });*/
 	$('.local_reporte_pagados_de_otras_tiendas').select2({
		closeOnSelect: false,            
		allowClear: true,
	});
	$('.canal_venta_reporte_pagados_de_otras_tiendas').select2({
		closeOnSelect: false,            
		allowClear: true,
	});	
	$('.red_reporte_pagados_de_otras_tiendas').select2({
		closeOnSelect: false,            
		allowClear: true,
	});


	$("#btn_consultar").off("click").on("click",function(){
	        
	    if(typeof tablaserver1 === "undefined") {
	        iniciar_datatable();
	    } 
	    else 
	    {
	        tablaserver1.ajax.reload();
	        tablaserver2.ajax.reload();
	    }

	})
	setTimeout(function(){$("#btn_consultar").click()},800);

}


function listar_tickets_de_en(idtabla,opciones){

	tablaserver=$("#"+idtabla)
						.on('order.dt', function () {
                            $('table').css('width', '100%');
                            $('.dataTables_scrollHeadInner').css('width', '100%');
                            $('.dataTables_scrollFootInner').css('width', '100%');
                        })
                        .on('search.dt', function () {
								//responsive_tabla_scroll(tablaserver);
                                $('table').css('width', '100%');
                                $('.dataTables_scrollHeadInner').css('width', '100%');
                                $('.dataTables_scrollFootInner').css('width', '100%');
                            })
                        .on('page.dt', function () {
                                $('table').css('width', '100%');
                                $('.dataTables_scrollHeadInner').css('width', '100%');
                                $('.dataTables_scrollFootInner').css('width', '100%');
                            })
			.DataTable({
            //    "paging": true,
                "scrollX": true,
                "sScrollX": "100%",
                //"scrollY": "450px",
             //   "scrollCollapse": false,
                "bProcessing": true,
                'processing': true,
               // "sScrollXInner":'100%',
                "language": {
                    "search": "Buscar:",
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "No se encontraron registros",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "No hay registros",
                    "infoFiltered": "(filtrado de _MAX_ total records)",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    sProcessing: "Procesando..."
                },
          //    "sort": false,
                "bDeferRender": false,
                "autoWidth": true,
                pageResize:true,
                "bAutoWidth": true,
               // "pageLength": 10,
                serverSide: true,
                "bDestroy": true,
                //colReorder: true,
                //"lengthMenu": [[20, 50, 200, -1], [20, 50, 200, "Todo"]],
            	responsive: false,
        		paging: false,
                //"lengthMenu": [[-1], [ "Todo"]],
                 "bSort": false,
                 //"ordering": false,
                 //processing: true,
                "columnDefs":[],
				//searchDelay:1000,
                sDom:"<'row'<'col-sm-12'B>><'row'<'col-sm-6'l>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                //sDom:"<'row'<'col-sm-12'B>><'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                //sDom:"<'row'<'col-sm-12'B>><'row'<'col-sm-12'tr>>",
                 //sDom: 'lfrtip',
			   //fixedHeader: true,
			       /*fixedHeader: {
             	 header: true,
               headerOffset: $('.contenedor_reporte_pagados_de_otras_tiendas').outerHeight()
             }     ,*/
			    buttons: [
			   /* {
	                extend: 'excel',
	                text:'Excel XLSX',
	                className:"btn_export_pagados_de_xlsx",
	                messageTop: 'Reporte Tickets',
	                 exportOptions: {
	                    columns: [ 0, 1, 2,4,6 ]
	                }
            	},*/
			        {
		            	text: 'Excel XLSX',
	                	className:"btn_export_pagados_de_xlsx",
			            action: function ( e, dt, node, config ) {
			            	if($("#"+idtabla).DataTable().data().count()>0){
			            		GENERAR_EXCEL=1;
			                	$("#"+idtabla).DataTable().ajax.reload(null,false);			            		
			            	}else{
	            					swal({
										title: 'No hay datos',
										type: "warning",
										timer: 2000,
									}, function(){
										swal.close();
									}); 
			            	}

			            }
			        }
			    ],
                ajax: function (datat, callback, settings) {////AJAX DE CONSULTA
            		datat.filtros={};

					datat.filtros["fecha_inicio"]=moment($("#fecha_inicio").val(), 'DD-MM-YYYY').format('YYYY-MM-DD');
					datat.filtros["fecha_fin"]=moment($("#fecha_fin").val(), 'DD-MM-YYYY').format('YYYY-MM-DD');
					datat.filtros["zona"]=$("#zona").val()?($("#zona").val()).join(","):"";
					datat.filtros["local"]=$("#local").val()?($("#local").val()).join(","):"";
					datat.filtros["canal_venta"]=$("#canal_venta").val()?($("#canal_venta").val()).join(","):"";
					datat.filtros["red"]=$("#red").val()?($("#red").val()).join(","):"";
                	datat[opciones.jsonop.fn]=opciones.jsonop.fn;
                	datat.where = opciones.jsonop.where;

                	if(typeof GENERAR_EXCEL!="undefined"){
                		datat.general_xlsx=1;
                	}

					auditoria_send({"proceso":"sec_reporte_en_de_get_data_reporte","data":datat});
                    ajaxrepitiendo = $.ajax({
                        global: false,
						url: "/api/?json",
                        type: 'POST',
                        data: datat,//+data,
                        beforeSend: function () {
                			loading(true);

                        },
                        complete: function () {
                        	loading();
                            //responsive_tabla_scroll(tablaserver);
                        },
                        success: function (datos, status, xhr) {//  alert(datat)
                            //aaaa = datos;
                            var respuesta = JSON.parse(datos);

                            if(typeof respuesta.file!="undefined"){
                            	var filename=(xhr.getResponseHeader("Content-Disposition")).split("filename=")[1];
            				    var $a = $("<a>");
							    $a.attr("href",respuesta.file);
							    $("body").append($a);
							    $a.attr("download",filename);
							    $a[0].click();
							    $a.remove();
							    delete GENERAR_EXCEL;
							    $(".dataTables_processing").hide();
                                return false;

                            }
                            else{
	                            callback(JSON.parse(respuesta.data));
                            }
                        },
                        error: function () {
                        	loading();
                        }
                    });



                },

                columns: opciones.columns,
			   /*'columnDefs': [{
            	    "targets": [0],
                	"orderable": true
            	}],*/
//              "ordering": false,

               "drawCallback": function ( settings ) {
		            var api = this.api();
		            var rows = api.rows( {page:'current'} ).nodes();
		            var last=null;
		            var subTotal = new Array();
		            var groupID = -1;
		            var aData = new Array();
		            var index = 0;
		            
		            api.column(opciones.col_agrup, {page:'current'} ).data().each( function ( group, i ) {
		              var vals = api.row(api.row($(rows).eq(i)).index()).data();
		              var salary = vals["ganado"] ? parseFloat(vals["ganado"]) : 0;
		              if (typeof aData[group] == 'undefined') {
		                 aData[group] = new Array();
		                 aData[group].rows = [];
		                 aData[group].salary = [];
		              }
		          
		           		aData[group].rows.push(i); 
		        		aData[group].salary.push(salary); 
		            });
		    
		            console.log(aData);
		            var idx= 0;		      
		          	for(var tienda in aData){
						idx =  Math.max.apply(Math,aData[tienda].rows);
		      
	                   var sum = 0; 
	                   $.each(aData[tienda].salary,function(k,v){
	                        sum = parseFloat(sum) + parseFloat(v);
	                   });
	  				   console.log(aData[tienda].salary);
					   /* Se muestra el total pagado por tienda
	                   $(rows).eq( idx ).after(
	                        '<tr style="background-color:#c5e0b4 !important" class="sub_totales_pdot"><td colspan="4" style="text-align:right">Total '+tienda+'</td>'+'<td class="sec_rep_pdot_cantidad_pagado">'+sum.toFixed(2)+'</td></tr>'
	                    );
						*/
		            };

    		    },

                "initComplete": function (settings, json) {
                }

            });
	return tablaserver;
}

function filtrar(datatabledtfil, t, exacto, filtrar_al_apretar_boton) {
    if (exacto) {
        if ($(t).val() !== "") {
            if (filtrar_al_apretar_boton) {
                datatabledtfil.column($(t).attr('data-columna')).search("^" + $(t).val() + "$", true, false);
            }
            else {
                datatabledtfil.column($(t).attr('data-columna')).search("^" + $(t).val() + "$", true, false).draw();
            }
        } else {
            if (filtrar_al_apretar_boton) {
                datatabledtfil.column($(t).attr('data-columna')).search($(t).val());
            }
            else {
                datatabledtfil.column($(t).attr('data-columna')).search($(t).val()).draw();
            }
        }
    }
    else {
        if (filtrar_al_apretar_boton) {
            datatabledtfil.column($(t).attr('data-columna')).search($(t).val());
        }
        else {
            datatabledtfil.column($(t).attr('data-columna')).search($(t).val()).draw();
        }
    }
}
function filtrarselect(datatabledtfil, t, exacto, filtrar_al_apretar_boton) {
    if (exacto) {
    	//var valor=$("option:selected",$(t)).val();
    	var valor=$(t).val();
 //   	valor=valor.join(",");
        if ($(t).val() !== "") {
            if (filtrar_al_apretar_boton) {
                datatabledtfil.column($(t).attr('data-columna')).search(valor , true, false);
            }
            else {
                datatabledtfil.column($(t).attr('data-columna')).search(valor, true, false).draw();
            }
        } else {
            if (filtrar_al_apretar_boton) {
                datatabledtfil.column($(t).attr('data-columna')).search(valor);
            }
            else {
                datatabledtfil.column($(t).attr('data-columna')).search(valor).draw();
            }
        }
    }
    else {
        if (filtrar_al_apretar_boton) {
            datatabledtfil.column($(t).attr('data-columna')).search(valor);
        }
        else {
            datatabledtfil.column($(t).attr('data-columna')).search(valor).draw();
        }
    }
}
function filtrofecharango(datatabledt, col, filtrar_al_apretar_boton) {
    desde = $('#fecha_inicio' ).val() ? $('#fecha_inicio' ).val() : '0';
    hasta = $('#fecha_fin' ).val() ? $('#fecha_fin' ).val() : '0';
    rango = desde + '~' + hasta;
    if (filtrar_al_apretar_boton) {
        datatabledt.column(col).search(rango);
    }
    else {
        datatabledt.column(col).search(rango).draw();
    }

}
function filtrofecha(datatabledt, col, filtrar_al_apretar_boton) {


     desde = $('#fecha_inicio' ).val() ? $('#fecha_inicio' ).val() : '0';


	var date = moment($('#fecha_inicio' ).val() , 'DD-MM-YYYY');
	desde=date.format('YYYY-MM-DD');

		 date = moment($('#fecha_fin' ).val() , 'DD-MM-YYYY');
	hasta=date.format('YYYY-MM-DD');

/*    desde = $('#fecha_inicio' ).val() ? desde : '0';
    hasta = $('#fecha_fin' ).val() ?hasta: '0';*/
    rango = desde + '~' + hasta;
    if ($('#fecha_fin').length > 0) {
        if (filtrar_al_apretar_boton) {
            datatabledt.column(col).search(rango);
        } else {
            datatabledt.column(col).search(rango).draw();
        }
    } else {
        if (filtrar_al_apretar_boton) {
            datatabledt.column(col).search(desde);
        } else {
            datatabledt.column(col).search(desde).draw();
        }
    }
}
