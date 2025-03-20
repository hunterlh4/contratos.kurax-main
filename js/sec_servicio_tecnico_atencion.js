function sec_servicio_tecnico_atencion(){
	if(sec_id == "servicio_tecnico_atencion"  ||  sec_id=="servicio_tecnico_atencion_form") {
		console.log("sec:servicio_tecnico_atencion");
		sec_servicio_tecnico_atencion_events();
	}
}
function sec_servicio_tecnico_atencion_events(){
	

    tablaserver = listar_servicio_tecnico_atencion();

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
	  });
	  $("#zona_select").on("change", function() {
		let zona_id = $(this).find(':selected').attr("value");
		sec_servicio_tecnico_cargar_locales(zona_id);
	})

	$(".select2")
		.filter(function(){
			return $(this).css('display') !== "none";
		})
		.select2({
		closeOnSelect: true,
		width:"100%"
	});

}
function sec_servicio_tecnico_atencion_modal_detalle_events(){
	$("#modal_detalle #sec_servicio_tecnico_atencion_guardar_btn").off("click").on("click",function(){
		var form = $("#modal_detalle form")[0];
		sec_servicio_tecnico_atencion_update(form);
	})
	$("#modal_detalle").off("shown.bs.modal").on("shown.bs.modal",function(){
		$('.modal').css('overflow-y', 'auto');
		loading();
	});
	$("#modal_detalle").off("hidden.bs.modal").on("hidden.bs.modal",function(){
		$(".comentario, .foto_terminado").hide();
		$("#div_derivacion_tecnico").hide();
		$("#modal_detalle select, textarea ,input:file").val("");
		$("#modal_detalle #foto_terminado").attr("src","data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==");
	});
}
///////guardar sec_servicio_tecnico_cargar_locales
function sec_servicio_tecnico_cargar_locales(zona_id){
	loading(true);
    var set_data = {};
    set_data.zona_id = zona_id ;
	set_data.sec_servicio_tecnico_cargar_locales = "sec_servicio_tecnico_cargar_locales";

	$.ajax({
		url: 'sys/set_servicio_tecnico.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			$('#local_select').empty();
			$(obj.locales).each(function(i,e){
				$('#local_select').append($('<option>', { 
					value: e.id,
					text : e.nombre 
    			}));
			})
			$('#local_select').select2();
			loading();
		}
	});
}

function sec_servicio_tecnico_atencion_update(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_servicio_tecnico_atencion_update","set_servicio_tecnico_atencion_update");
	dataForm.append("estado_vt", $("#estado option:selected" ,$(form)).text());
	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	result.estado_vt = $("#estado option:selected" ,$(form)).text(); 
	var set_data = {};
	if(result.foto_terminado_update.name != ""){
		result.foto_terminado_update = result.foto_terminado_update.name; 
	}
	else{
		result.foto_terminado_update = "";
	}
	set_data = result;

	$.ajax({
		url: 'sys/set_servicio_tecnico_atencion.php',
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			if(obj.error){
				set_data.error = obj.error;
				set_data.error_msg = obj.error_msg;
				auditoria_send({"proceso":"sec_servicio_tecnico_atencion_update_error","data":set_data});
				loading(false);
				swal({
					title: "Error!",
					text: obj.error_msg,
					type: "warning",
					timer: 3000,
					closeOnConfirm: true
				},
				function(){
					swal.close();
				});
			}else{
				loading(false);
				set_data.curr_login = obj.curr_login;
				auditoria_send({"proceso":"sec_servicio_tecnico_atencion_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
				$("#tbl_servicio_tecnico_atencion").DataTable().ajax.reload();
				$("#modal_detalle").modal("hide");
			}
		}
	});
}

function listar_servicio_tecnico_atencion(){
	tablaserver = $("#tbl_servicio_tecnico_atencion")
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
					datat.action = typeof action == "undefined" ? "sec_servicio_tecnico_list" : action;

                	datat.fecha_inicio = $("#fecha_inicio").attr("data-fecha_formateada");
                	datat.fecha_fin = $("#fecha_fin").attr("data-fecha_formateada");

                    ajaxrepitiendo = $.ajax({
                        global: false,
                        url: "/sys/set_servicio_tecnico.php",
                        type: 'POST',
                        data: datat,
                        beforeSend: function () {
                        	tablaserver.columns.adjust();
                        },
                        complete: function () {
                        	tablaserver.columns.adjust();
                        },
                        success: function (datos) {//  alert(datat)
                            var respuesta = JSON.parse(datos);
                            if(datat.action == "sec_servicio_tecnico_list_excel"){
                            	$(".dataTables_processing").hide();
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
	                {data:"id",nombre:"id",title:"ID"},//,visible:false},
	                {data:"created_at",nombre:"created_at",title:"Fecha Ingreso"},
	                {data:"zona",nombre:"zona",title:"Zona"},
	                {data:"local",nombre:"local",title:"Tienda"},
	                {data:"incidencia_txt",nombre:"incidencia_txt",title:"Descrip. Incidente"},
	                {data:"equipo",nombre:"equipo",title:"Equipo"},
	                {data:"recomendacion",nombre:"recomendacion",title:"Recomendación"},
	                {data:"nota_tecnico",nombre:"nota_tecnico",title:"Nota para el Técnico"},
	                {data:"estado_vt",nombre:"estado_vt",title:"Estado"},
	                {data:"fecha_cierre_vt",nombre:"fecha_cierre_vt",title:"Fecha de Cierre",
						defaultContent: "-"
	            	},
	                {data:"id",nombre:"id",orderable:false,title:"Detalle"
						,"render": function (data, type, row ) {
							var html = '<a class="btn btn-rounded btn-primary btn-sm ver_detalle" data-estado= "' + row["estado_servicio_tecnico_id"] + '" title="Ver detalle">';
							html += '<i class="fa fa-eye"></i>';
							html += ' Ver';
							html += '</a>';
							return html;
	                	}
	            	}
                ],
                "drawCallback":function (){
					$("#tbl_servicio_tecnico_atencion tbody .ver_detalle").off("click").on("click",function(){
						var solicitud_id = $(this).closest("tr").attr("id");
						var estado_id = $(this).attr("data-estado");
						sec_servicio_tecnico_atencion_cargar_solicitud(solicitud_id,estado_id);
					})
                },
                "initComplete": function (settings, json) {
					setTimeout(function(){
						$("#servicio_tecnico_atencion_recargar").off("click").on("click",function(){
							tablaserver.ajax.reload(null, false);
						})
						tablaserver.columns.adjust();
					},100)
					// 0 => Nuevo, 1 => Atendido, 2 => Asignado
					action = "sec_servicio_tecnico_list";
					filtrar_datatable_sec_servicio_tecnico_atencion(settings,json);
                }
            });
	return tablaserver;
}
function sec_servicio_tecnico_atencion_cargar_solicitud(solicitud_id,estado_id){
	loading(true);
    var set_data = {};
    set_data.solicitud_id = solicitud_id ;
	set_data.sec_servicio_tecnico_atencion_cargar_solicitud = "sec_servicio_tecnico_atencion_cargar_solicitud";
	set_data.estado_id = estado_id;
	$('#tecnico_id').select2({
		placeholder: "Seleccione"
	});
	$('#equipo_id').select2({
		placeholder: "Seleccione"
	});

	$.ajax({
		url: 'sys/set_servicio_tecnico_atencion.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			loading();
			sec_servicio_tecnico_atencion_modal_detalle_events();
			$.each(obj.local,function(i,e){
				if($("#modal_detalle #" + i ).length > 0 ){
					if( i == "equipo_id" )
					{
						let estado = $("#modal_detalle #equipo_id [data-nombre = '" + e +"']").val();
						$("#modal_detalle #equipo_id").val( estado ).change();
					}else if( i == "tecnico_id" ){
						let estado = $("#modal_detalle #tecnico_id [data-nombre = '" + e +"']").val();
						$("#modal_detalle #tecnico_id").val( estado ).change();
					}
					else
					{
						if($("#modal_detalle #" + i )[0].nodeName == "P" || $("#modal_detalle #" + i )[0].nodeName == "DIV")
						{
							$("#modal_detalle #" + i ).text(e);
						}
						else{
							$("#modal_detalle #" + i).val(e);
						}

					}
				}
			})

			if( obj.local["estado"] == 4 ){/*terminado */
				$("#modal_detalle #foto_terminado").attr("src","files_bucket/servicio_tecnico/"+obj.local["foto_terminado"]);
			}
			$("#modal_detalle #imagenes_cargar").empty();
			if( !obj.imagenes ||  (obj.imagenes).length == 0 )
			{
				$("#modal_detalle div.imagenes").hide();
			}
			$(obj.imagenes).each(function(i,e){
				$("#modal_detalle #imagenes_cargar").append(
					$("<img class='imagenes_modal'>").attr("src","files_bucket/servicio_tecnico/"+e.archivo)
				)
			})
			$(".imagenes_modal").off("click").on("click",function(){
				var src = $(this).attr("src");
				$("#vista_previa_modal #img01").attr("src",src);
				$("#vista_previa_modal").modal("show");
			})

			$("#modal_detalle #estado").off("change").on("change",function(){
				let estado = $(this).val();
				

				if($(this).val() == 1 ){
					$(".comentario").hide();
					$(".foto_terminado").hide();
					$(".div_comentario_terminado").hide();
					$("#div_derivacion_tecnico").hide();
					$(".div_comentario_terminado_editar").hide();
				}
				if($(this).val() == 2 ){
					$(".foto_terminado").hide();
					$(".comentario").show();
					$(".div_comentario_terminado").hide();
					$("#div_derivacion_tecnico").show();
					$(".div_comentario_terminado_editar").hide();
				}
				if($(this).val() == 3 ){
					$(".comentario").hide();
					$(".foto_terminado").hide();
                    $(".div_comentario_terminado").hide();
					$("#div_derivacion_tecnico").hide();
					$(".div_comentario_terminado_editar").hide();
					
				}
				if($(this).val() == 4 ){
					$(".foto_terminado").show();
					$(".comentario").hide();
					$("#div_derivacion_tecnico").hide();
					$(".div_comentario_terminado").show();
					$(".div_comentario_terminado_editar").show();
				}
				
			})
			$("#vista_previa_modal").off("shown.bs.modal").on("shown.bs.modal",function(){
				$("#vista_previa_modal #img01").imgViewer2();
			})
			$("#vista_previa_modal").off("hidden.bs.modal").on("hidden.bs.modal",function(){
				$("#vista_previa_modal #img01").imgViewer2("destroy");
			})
			$("#modal_detalle #estado").val(obj.local["estado"]).change();
			$("#modal_detalle").modal("show");

			$("#nota_para_tecnico").val(obj.local.nota_tecnico);
			$("#comentario_terminado_editar").val(obj.local.comentario_terminado);
		},
		complete : function (){
			loading();
		},
        error: function () {
			loading();
       	}
	});
}

function filtrar_datatable_sec_servicio_tecnico_atencion(settings,json){
	var boton_buscar = true;

	var localStorage_estado_var="sec_servicio_tecnico_atencion_estado_select";
	var datatable = settings.oInstance.api();

	$("#btn_servicio_tecnico_atencion_search").off("click").on("click",function(){
        action = "sec_servicio_tecnico_list";
        datatable.ajax.reload(null, false);
	})
	$("#btn_servicio_tecnico_atencion_excel").off("click").on("click",function(){
		action = "sec_servicio_tecnico_list_excel";
		datatable.ajax.reload(null, false);
	})

	$("#sec_servicio_tecnico_atencion_estado_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(8).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#sec_servicio_tecnico_atencion_estado_select").select2();

	setTimeout(function(){
		var valor = localStorage.getItem(localStorage_estado_var).split(',');
		$("#sec_servicio_tecnico_atencion_estado_select").val(valor).change();
	},200);
	

	var localStorage_estado_var="sec_servicio_tecnico_atencion_local_select";
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


	var localStorage_estado_var="sec_servicio_tecnico_atencion_zona_select";
	$("#zona_select").off("change").on("change",function(){
		//let zona_id = $(this).find(':selected').attr("value");
		let zona_id = $("#zona_select").val();
		sec_servicio_tecnico_cargar_locales(zona_id);

		var val = $(this).val();
		datatable.column(2).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#zona_select").select2();

	var localStorage_estado_var="sec_servicio_tecnico_atencion_equipo_select";
	$("#equipo_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(5).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#equipo_select").select2();

	setTimeout(function(){
		var valor = localStorage.getItem(localStorage_estado_var).split(',');
		$("#equipo_select").val(valor).change();
	},200);

	var localStorage_estado_var="sec_servicio_tecnico_atencion_sistema_select";
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
