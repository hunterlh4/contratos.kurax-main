function sec_servicio_tecnico_observado(){
	if(sec_id == "servicio_tecnico_observado") {
		console.log("sec:servicio_tecnico_observado");
		sec_servicio_tecnico_observado_events();
	}
}
function sec_servicio_tecnico_observado_events(){
	$(".select2")
		.filter(function(){
			return $(this).css('display') !== "none";
		})
		.select2({
		closeOnSelect: true,
		width:"100%"
	});

    tablaserver = listar_servicio_tecnico_observado();

    $("#btn_actualizar_tbl").off("click").on("click",function(){
    	tablaserver.ajax.reload(null, false);
    });

	$("#zona_id").on("change", function() {
		let zona_id = $(this).find(':selected').attr("value");
		sec_servicio_tecnico_observado_cargar_locales(zona_id);
	})

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

	$("#zona_id").val($("#zona_id option:first").val()).change();
}
function modal_detalle_events(){
	$("#modal_detalle #sec_servicio_tecnico_observado_guardar_btn").off("click").on("click",function(){
		var form = $("#modal_detalle form")[0];
		sec_servicio_tecnico_observado_update(form);
	})
	$("#modal_detalle #motivo_observado").on("change",function(){	
		val = $(this).val();
		if( val ==  0)
		{
			$("#estado").val(3).change();
			$(".comentario_mantenimiento").hide();
		}
		if( val ==  1)
		{
			$("#estado").val(3).change();
		}
		if( val ==  2)
		{
			$("#estado").val(2).change();
			$(".comentario_mantenimiento").hide();
		}
		if( val ==  3)
		{
			$("#estado").val(2).change();
			$(".comentario_mantenimiento").hide();
		}
	})
	$("#modal_detalle").off("shown.bs.modal").on("shown.bs.modal",function(){
		$('.modal').css('overflow-y', 'auto');
		loading();
	});
	$("#modal_detalle").off("hidden.bs.modal").on("hidden.bs.modal",function(){
		$(".comentario, .foto_terminado").hide();
		$("#modal_detalle select, textarea ,input:file").val("");
		$("#modal_detalle #foto_terminado").attr("src","data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==");
	});
}
///////guardar sec_servicio_tecnico_observado_cargar_locales
function sec_servicio_tecnico_observado_cargar_locales(zona_id){
	loading(true);
    var set_data = {};
    set_data.zona_id = zona_id ;
	set_data.sec_servicio_tecnico_observado_cargar_locales = "sec_servicio_tecnico_cargar_locales";

	$.ajax({
		url: 'sys/set_servicio_tecnico.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			$('#local_id').empty();
			$(obj.locales).each(function(i,e){
				$('#local_id').append($('<option>', { 
					value: e.id,
					text : e.nombre 
    			}));
			})
			$('#local_id').select2();
			loading();
		}
	});
}

function sec_servicio_tecnico_observado_update(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_servicio_tecnico_observado_update","set_servicio_tecnico_observado_update");
	dataForm.append("estado_vt", $("#estado option:selected" ,$(form)).text());	
	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	result.estado_vt = $("#estado option:selected" ,$(form)).text(); 
	var set_data = {};

	set_data = result;

	$.ajax({
		url: 'sys/set_servicio_tecnico_observado.php',
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
				auditoria_send({"proceso":"sec_servicio_tecnico_observado_update_error","data":set_data});
				loading(false);
				swal({
					title: "¡Error!",
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
				auditoria_send({"proceso":"sec_servicio_tecnico_observado_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
				//tablaserver.ajax.reload();
				//$("#modal_detalle").modal("hide");
				$("#tbl_servicio_tecnico_observado").DataTable().ajax.reload();
				$("#modal_detalle").modal("hide");
			}
		}
	});
}

function listar_servicio_tecnico_observado(){
	tablaserver = $("#tbl_servicio_tecnico_observado")
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
 				//"deferLoading": 0,
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
                    datat["columns"][8]["search"]["value"] = "Observado";
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
					{data:"razon_social",nombre:"razon_social",title:"Razón Social"},
	                {data:"zona",nombre:"zona",title:"Zona"},
	                {data:"local",nombre:"local",title:"Tienda"},
	                {data:"incidencia_txt",nombre:"incidencia_txt",title:"Descrip. Incidente"},
	                {data:"equipo",nombre:"equipo",title:"Equipo"},
	                {data:"recomendacion",nombre:"recomendacion",title:"Recomendación"},
	                {data:"nota_tecnico",nombre:"nota_tecnico",title:"Nota para el Técnico"},
	                {data:"estado_vt",nombre:"estado_vt",title:"Estado"},
	                {data:"fecha_cierre",nombre:"fecha_cierre",title:"Fecha de Cierre",
						defaultContent: "-"
	            	},
	                {data:"id",nombre:"id",orderable:false,title:"Detalle"
						,"render": function (data, type, row ) {
							var html = '<a class="btn btn-rounded btn-primary btn-sm ver_detalle" title="Ver detalle">';
							html += '<i class="fa fa-eye"></i>';
							html += ' Ver';
							html += '</a>';
							return html;
	                	}
	            	}
                ],
                "drawCallback":function (){
					$("#tbl_servicio_tecnico_observado tbody .ver_detalle").off("click").on("click",function(){
						var solicitud_id = $(this).closest("tr").attr("id");
						sec_servicio_tecnico_observado_cargar_solicitud(solicitud_id);
					})
                },
                "initComplete": function (settings, json) {
					setTimeout(function(){
						$("#servicio_tecnico_observado_recargar").off("click").on("click",function(){
							tablaserver.ajax.reload(null, false);
						})
						tablaserver.columns.adjust();
					},100)
					// 0 => Nuevo, 1 => Atendido, 2 => Asignado
					action = "sec_servicio_tecnico_list";
					filtrar_datatable_sec_servicio_tecnico_observado(settings,json);
                }
            });
	return tablaserver;
}
function sec_servicio_tecnico_observado_cargar_solicitud(solicitud_id){
	loading(true);
    var set_data = {};
    set_data.solicitud_id = solicitud_id ;
	set_data.sec_servicio_tecnico_observado_cargar_solicitud = "sec_servicio_tecnico_observado_cargar_solicitud";
	$('#tecnico_id').select2({
		placeholder: "Seleccione"
	});
	$('#equipo_id').select2({
		placeholder: "Seleccione"
	});

	$.ajax({
		url: 'sys/set_servicio_tecnico_observado.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			loading();
			if (obj.error == true){
				swal("Error", obj.error_msg, "error");
			} else {
				modal_detalle_events();
				$.each(obj.local, function (i, e) {
					if ($("#modal_detalle #" + i).length > 0) {
						if( i == "equipo_id" )
						{
							let estado = $("#modal_detalle #equipo_id [data-nombre = '" + e +"']").val();
							$("#modal_detalle #equipo_id").val( estado ).change();
						}else if( i == "tecnico_id" ){
							let estado = $("#modal_detalle #tecnico_id [data-nombre = '" + e +"']").val();
							$("#modal_detalle #tecnico_id").val( estado ).change();
						}else if( i == "motivo_observado"){
							let motivo_observado = $("#modal_detalle #motivo_observado [data-nombre = '" + e +"']").val();
							$("#modal_detalle #motivo_observado").val( motivo_observado ).change();
						}
						else
						{
							if ($("#modal_detalle #" + i)[0].nodeName == "P" || $("#modal_detalle #" + i)[0].nodeName == "DIV") {
								$("#modal_detalle #" + i).text(e);
							} else {
								$("#modal_detalle #" + i).val(e);
							}
						}
					}
				})

				if (obj.local["estado"] == 3) {
					$("#modal_detalle #foto_terminado").attr("src", "files_bucket/servicio_tecnico/" + obj.local["foto_terminado"]);
				}

				if (!obj.imagenes || (obj.imagenes).length == 0) {
					$("#modal_detalle div.imagenes").hide();
				}

				$("#modal_detalle #imagenes_cargar").empty();
				$(obj.imagenes).each(function (i, e) {
					$("#modal_detalle #imagenes_cargar").append(
						$("<img class='imagenes_modal'>").attr("src", "files_bucket/servicio_tecnico/" + e.archivo)
					)
				})
				$(".imagenes_modal").off("click").on("click", function () {
					var src = $(this).attr("src");
					$("#vista_previa_modal #img01").attr("src", src);
					$("#vista_previa_modal").modal("show");
				})

				$("#div-sistema-modal").hide();

				$("#modal_detalle #estado").off("change").on("change", function () {
					let estado = $(this).val();
					if (estado == 1) { //Derivado
						$(".foto_terminado").hide();
						$(".comentario").hide();
						$("#div_derivacion_tecnico").hide();
						$(".comentario_terminado").hide();
						$(".comentario_mantenimiento").hide();
					}
					if (estado == 2) {//Programado
						$(".foto_terminado").hide();
						$(".comentario").show();
						$("#div_derivacion_tecnico").show();
						$(".comentario_terminado").hide();
						$(".comentario_mantenimiento").hide();
					}
					if (estado == 3) {//Observado
						$(".foto_trminado").show();
						$(".comentario").hide();
						$("#div_derivacion_tecnico").hide();
						$(".comentario_terminado").hide();
						$(".comentario_mantenimiento").show();
					}
					if (estado == 4) {//Terminado
						$(".foto_trminado").show();
						$(".comentario").hide();
						$("#div_derivacion_tecnico").hide();
						$(".comentario_terminado").show();
						$(".comentario_mantenimiento").hide();
					}
				})
				$("#vista_previa_modal").off("shown.bs.modal").on("shown.bs.modal", function () {
					$("#vista_previa_modal #img01").imgViewer2();
				})
				$("#vista_previa_modal").off("hidden.bs.modal").on("hidden.bs.modal", function () {
					$("#vista_previa_modal #img01").imgViewer2("destroy");
				})
				$("#modal_detalle #estado").val(obj.local["estado"]).change();
				$("#comentario_tecnico").val(obj.local.comentario);
				$("#comentario_terminado").val(obj.local.comentario_terminado);
				$("#comentario_mantenimiento").val(obj.local.comentario_mantenimiento);
				$("#modal_detalle").modal("show");
			}
		},
		complete : function (){
			loading();
		},
        error: function () {
			loading();
       	}
	});
}

function filtrar_datatable_sec_servicio_tecnico_observado(settings,json){
	var boton_buscar = true;

	var localStorage_estado_var="sec_servicio_tecnico_observado_estado_select";
	var datatable = settings.oInstance.api();

	$("#btn_servicio_tecnico_observado_search").off("click").on("click",function(){
        action = "sec_servicio_tecnico_list";
        datatable.ajax.reload(null, false);
	})
	$("#btn_servicio_tecnico_observado_excel").off("click").on("click",function(){
		action = "sec_servicio_tecnico_list_excel";
		datatable.ajax.reload(null, false);
	})

	$("#sec_servicio_tecnico_observado_estado_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(9).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})

	if(localStorage.getItem(localStorage_estado_var) && localStorage.getItem(localStorage_estado_var)!="null"){
		setTimeout(function(){
			var valor = localStorage.getItem(localStorage_estado_var).split(',');
			$("#sec_servicio_tecnico_observado_estado_select").val(valor).change();
		},200);
	}
	else{
		setTimeout(function(){
			$("#sec_servicio_tecnico_observado_estado_select").val([0,2]).change();//nuevos,asignados
		},200);
	}

	var localStorage_estado_var="sec_servicio_tecnico_observado_local_select";
	$("#local_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(4).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#local_select").select2();


	var localStorage_estado_var="sec_servicio_tecnico_observado_zona_select";
	$("#zona_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(3).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#zona_select").select2();

	// var localStorage_estado_var="sec_servicio_tecnico_observado_sistema_select";
	// $("#sistema_select").off("change").on("change",function(){
	// 	var val = $(this).val();
	// 	datatable.column(4).search(val);//.draw();
	// 	if(!boton_buscar){
	// 		datatable.ajax.reload(null, false);
	// 	}
	// 	datatable.columns.adjust();
	// 	localStorage.setItem(localStorage_estado_var,val);
	// })
	// $("#sistema_select").select2();

	var localStorage_estado_var="sec_servicio_tecnico_observado_razon_social_select";
	$("#razon_social_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(2).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#razon_social_select").select2();
}
