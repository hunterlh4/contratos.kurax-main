function sec_reportes_solicitud_mantenimiento(){
	if(sec_id=="reportes_solicitud_mantenimiento" ) {
		console.log("sec:reportes_solicitud_mantenimiento");
		sec_reportes_solicitud_mantenimiento_events();
		document.getElementById('btn_solicitud_mantenimiento_excel').innerHTML = '<button class="btn btn-primary ">Excel</button> ';
	}
}

function sec_reportes_solicitud_mantenimiento_events(){
	$(".select2")
		.filter(function(){
			return $(this).css('display') !== "none";
		})
		.select2({
		closeOnSelect: true,
		width:"100%"
	});

    tablaserver = listar_reportes_solicitudes_mantenimiento();

    $("#btn_actualizar_tbl").off("click").on("click",function(){
    	tablaserver.ajax.reload(null, false);
    });

	$(".filtro_datepicker")
	  .datepicker({
		dateFormat:'dd-mm-yy',
		changeMonth: true,
		changeYear: true
	  })
	  .on("change", function(ev) {
		$(this).datepicker('hide');
		var newDate = $(this).datepicker("getDate");
		$(this).attr("data-fecha_formateada",$.format.date(newDate, "yyyy-MM-dd"));
		//tablaserver.ajax.reload();
	  });
}

function listar_reportes_solicitudes_mantenimiento(){
	tablaserver =$("#tbl_solicitud_mantenimiento")
						.on('order.dt', function () {
                            $('table').css('width', '100%');
                            $('.dataTables_scrollHeadInner').css('width', '100%');
                            $('.dataTables_scrollFootInner').css('width', '100%');
                        })
                        .on('search.dt', function () {
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
                "paging": true,
                "scrollX": true,
                "sScrollX": "100%",
                "bProcessing": true,
                'processing': false,
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

                "bDeferRender": false,
                "autoWidth": true,
                 pageResize:true,
                "bAutoWidth": true,
                "pageLength": 10,
                serverSide: true,
                "bDestroy": true,
                colReorder: true,
                "lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
                "order": [[ 1, "desc" ]],
			    buttons: [
			        {
			            text: '<span class="glyphicon glyphicon-refresh"></span>',
			            action: function ( e, dt, node, config ) {
			                tablaserver.ajax.reload(null,false);
			            }
			        }
			    ],
                ajax: function (datat, callback, settings) {////AJAX DE CONSULTA
                	datat.action = typeof action=="undefined"?"sec_solicitud_mantenimiento_list":action; //"sec_solicitud_mantenimiento_list";

                	datat.fecha_inicio = $("#fecha_inicio").attr("data-fecha_formateada");
                	datat.fecha_fin = $("#fecha_fin").attr("data-fecha_formateada");

                    ajaxrepitiendo = $.ajax({
                        global: false,
                        url: "/sys/set_solicitud_mantenimiento.php",
                        type: 'POST',
                        data: datat,
                        beforeSend: function () {
                        	tablaserver.columns.adjust();
                        },
                        complete: function () {
                        	tablaserver.columns.adjust();
                            //responsive_tabla_scroll(tablaserver);
                        },
                        success: function (datos) {//  alert(datat)
                            var respuesta = JSON.parse(datos);
                            if(datat.action == "sec_solicitud_mantenimiento_list_excel"){
								datat.curr_login = respuesta.curr_login;
								auditoria_send({"proceso":"sec_solicitud_mantenimiento_list_excel","data":datat});

                            	$(".dataTables_processing").hide();
								action = "sec_solicitud_mantenimiento_list";
                            	window.open(respuesta.path);
                            	return;
                            }
                            callback(respuesta);
                        },
                        error: function () {
                        }
                    });
                },
                rowId: function(row){
					return row.id;
				},
                columns: [
	                {data:"id",nombre:"id",title:"Id",visible:false},
	                {data:"created_at",nombre:"created_at",title:"Fecha Ingreso"},
	                {data:"zona",nombre:"zona",title:"Zona"},
	                {data:"local",nombre:"local",title:"Tienda"},
	                {data:"sistema",nombre:"sistema",title:"Sistema"},
					{data:"reporte",nombre:"reporte",title:"Reporte",
						"render": function (data, type, row ) {
							var length = data.length;
							var max_length = 25;
							if(length > max_length){
								return data.slice(0,max_length) +"..." ;
							}
								return data;
						}
					},
	                {data:"tipo_mantenimiento",nombre:"tipo_mantenimiento",title:"Tipo Mantenimiento",
						defaultContent: "-"
					},
	                {data:"estado",nombre:"estado",title:"Estado"
						,"render": function (data, type, row ) {
							var inc_id=row["id"];
	                                var estado = data;
	                                html=estado;
	                                if (estado == "Solucionar") {
	                                	html="<button class='btn btn-sm text-success btn-default  btn-solucionar' data-id="+inc_id+"><span class='glyphicon glyphicon-ok-circle'></span> Solucionar</button>"; 
	                                } 
	                                return html;
	                        }
	             	},
	                {data:"fecha_cierre",nombre:"fecha_cierre",title:"Fecha de Cierre",
						defaultContent: "-"
	            	}
                ],
                "drawCallback":function (){                	
                },

                "initComplete": function (settings, json) {
					setTimeout(function(){
						$("#solicitud_mantenimiento_recargar").off("click").on("click",function(){
							tablaserver.ajax.reload(null, false);
						})
						tablaserver.columns.adjust();
					},100)
					// 0 => Nuevo, 1 => Atendido, 2 => Asignado
					action = "sec_solicitud_mantenimiento_list";
					filtrar_datatable(settings,json);
                }
            });
	return tablaserver;
}

function limpiar_tabla_rep_mantenimiento_vt() {
	$("#tbl_solicitud_derivado").html(
	  "<thead>" +
		"   <tr>" +

		'		<th class="text-center">Id</th>' +		                    
		'		<th class="text-center">Fecha Ingreso</th>' + 
		'		<th class="text-center">Zona</th>' +
		'		<th class="text-center">Tienda</th>' + 
		'		<th class="text-center">Descrip. Incidente</th>' +
		'		<th class="text-center">Equipo</th>' +	
		'		<th class="text-center">Recomendación</th>' +
		'		<th class="text-center">Nota para el técnico</th>' +		
		'		<th class="text-center">Estado</th>' +		                    
		'		<th class="text-center">Fecha Cierre</th>' +		
		 
		"   </tr>" +
		"</thead>" +
		"<tbody>"
	);
	 
  }


  function listar_rep_solicitudes_mantenimiento_vt_excel(){

	var sec_sol_mt_estado = $.trim($("#estado_select").val());	 
	var sec_sol_mt_tienda = $.trim($("#local_select").val());
	var sec_sol_mt_fecha_inicio = $("#fecha_inicio").attr("data-fecha_formateada");
	var sec_sol_mt_fecha_fin = $("#fecha_fin").attr("data-fecha_formateada");
  
 
	document.getElementById('btn_solicitud_mantenimiento_excel').innerHTML = '<a href="export.php?export=solicitud_derivados_at&amp;type=lista&amp;sec_sol_mt_estado='+sec_sol_mt_estado+'&amp;sec_sol_mt_tienda='+sec_sol_mt_tienda+'&amp;sec_sol_mt_fecha_inicio='+sec_sol_mt_fecha_inicio+'&amp;sec_sol_mt_fecha_fin='+sec_sol_mt_fecha_fin+'"  class="btn btn-primary " download="solicitud_derivados_at.xls">Excel</a>';


	
	
}


function listar_rep_solicitudes_mantenimiento_vt(){
 
	limpiar_tabla_rep_mantenimiento_vt()
 
	var sec_sol_mt_estado = $.trim($("#estado_select").val());
	var sec_sol_mt_zona = $.trim($("#zona_select").val());
	var sec_sol_mt_tienda = $.trim($("#local_select").val());
	var sec_sol_mt_fecha_inicio = $("#fecha_inicio").attr("data-fecha_formateada");
	var sec_sol_mt_fecha_fin = $("#fecha_fin").attr("data-fecha_formateada");
  
	var data = {
		"accion": "listar_derivaciones_vt",
		"estado": sec_sol_mt_estado,
		"zona": sec_sol_mt_zona ,
		"tienda": sec_sol_mt_tienda,
		"fecha_inicio": sec_sol_mt_fecha_inicio,
        "fecha_fin": sec_sol_mt_fecha_fin 
	  }
  
	auditoria_send({ proceso: "listar_derivaciones_vt", data: data });
	$.ajax({
	  url: "/sys/set_solicitud_mantenimiento.php",
	  type: "POST",
	  data: data,
	  beforeSend: function () {
		loading("true");
	  },
	  complete: function () {
		loading();
	  },
	  success: function (resp) {
		//  alert(datat)
		var respuesta = JSON.parse(resp);
		//console.log(respuesta);
		if (parseInt(respuesta.http_code) == 400) {
		  return false;
		}
		if (parseInt(respuesta.http_code) == 200) {
		  
		  if (respuesta.result.length > 0) {
			$.each(respuesta.result, function (index, item) {
			  
				if(!item.zona){
					item.zona="-";
				}

				if(!item.local){
					item.local="-";
				}
			  
				if(!item.incidencia_txt){
					item.incidencia_txt="-";
				}

				if(!item.estado_vt){
					item.estado_vt="Derivado";
				}
	  
				if(!item.fecha_cierre_vt){
					item.fecha_cierre_vt="-";
				   }
			 
			 
			  $("#tbl_solicitud_derivado").append(
				"<tr>" +
				  '<td class="text-center">' +
				  (index + 1) +
				  "</td>" +
				  '<td class="text-center">' +
				  item.created_at +
				  "</td>" +
				  '<td class="text-center">' +
				  item.zona +
				  "</td>" +
				  '<td class="text-center">' +
				  item.local +
				  "</td>" +				  
				  '<td class="text-center">' +
				  item.incidencia_txt +
				  "</td>" +
				  '<td class="text-center">' +
				  item.equipo +
				  "</td>" +
				  '<td class="text-center">' +
				  item.recomendacion +
				  "</td>" +	
				  '<td class="text-center">' +
				  item.nota_tecnico +
				  "</td>" +					  
				  '<td class="text-center">' +
				  item.estado_vt +
				  "</td>" +
				  '<td class="text-center">' +
				  item.fecha_cierre_vt +
				  "</td>" +				  
			 		   
				  "</tr>"
			  );
			});
			DATATABLE_FORMATO_tabla_derivados("#tbl_solicitud_derivado");
		  } else {
			$("#tbl_solicitud_derivado").append("<tr>" + '<td class="text-center" colspan="8">No hay transacciones.</td>' + "</tr>");
		  }
		  
		  //console.log(array_clientes);
		  return false;
		}
	  },
	  error: function () {},
	});
	 
}


function filtrar_datatable(settings,json){
	var boton_buscar = true;

	var localStorage_estado_var="sec_solicitud_mantenimento_estado_select";
	var datatable = settings.oInstance.api();

	$("#btn_solicitud_mantenimiento_search").off("click").on("click",function(){
		
		var tipo_b = $('#sec_sol_mant_tipo').val();
		console.log(tipo_b);
		if (tipo_b == 1){
			
			document.getElementById('btn_solicitud_mantenimiento_excel').innerHTML = '<button class="btn btn-primary ">Excel</button> ';

			$("#div_filtros_mant").show();
			$("#div_list_derivacion").hide();
			$("#div_list_mantenimiento").show();
			action = "sec_solicitud_mantenimiento_list";
			datatable.ajax.reload(null, false);

		}else{		  
			document.getElementById('btn_solicitud_mantenimiento_excel').innerHTML = '<a  class="btn btn-primary " download="solicitud_derivados_at.xls">Excel</a>';

			$("#div_filtros_mant").hide();
			$("#div_list_derivacion").show();
			$("#div_list_mantenimiento").hide();
			listar_rep_solicitudes_mantenimiento_vt();
			listar_rep_solicitudes_mantenimiento_vt_excel();
		}
	})
	$("#btn_solicitud_mantenimiento_excel").off("click").on("click",function(){
		
		var tipo_b = $('#sec_sol_mant_tipo').val();
		console.log(tipo_b);
		if (tipo_b == 1){
			$("#div-btn-mant").show();
			$("#div-btn-deriv").hide();
			$("#div_filtros_mant").show();
			$("#div_list_derivacion").hide();
			$("#div_list_mantenimiento").show();
			action = "sec_solicitud_mantenimiento_list_excel";
			datatable.ajax.reload(null, false);

		}else{		  
			$("#div-btn-mant").hide();
			$("#div-btn-deriv").show();

			$("#div_filtros_mant").hide();
			$("#div_list_derivacion").show();
			$("#div_list_mantenimiento").hide();
			listar_rep_solicitudes_mantenimiento_vt_excel();
			 
		}
		
		
		
	})

	$("#estado_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(7).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#estado_select").select2();

	if(localStorage.getItem(localStorage_estado_var) && localStorage.getItem(localStorage_estado_var)!="null"){
		setTimeout(function(){
			var valor = localStorage.getItem(localStorage_estado_var).split(',');
			$("#estado_select").val(valor).change();
		},200);
	}
	else{
		setTimeout(function(){
			$("#estado_select").val([0,2]).change();//nuevos,asignados
		},200);
	}

	var localStorage_estado_var="sec_solicitud_mantenimento_local_select";
	$("#local_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(3).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#local_select").select2();

	var localStorage_estado_var="sec_solicitud_mantenimento_sistema_select";
	$("#sistema_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(4).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#sistema_select").select2();


}
