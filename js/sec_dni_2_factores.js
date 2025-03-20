function sec_dni_2_factores(){
	if(sec_id == "dni_2_factores"  ||  sec_id=="dni_2_factores_form") {
		console.log("sec:dni_2_factores");
		sec_dni_2_factores_events();
	}
}

function sec_dni_2_factores_events(){
    tablaserver = listar_dni_2_factores();

    // $("#zona_select").on("change", function() {
    //     let zona_id = $(this).find(':selected').attr("value");
    //     sec_servicio_tecnico_cargar_locales(zona_id);
	// })

	// $(".select2")
	// 	.filter(function(){
	// 		return $(this).css('display') !== "none";
	// 	})
	// 	.select2({
	// 	closeOnSelect: true,
	// 	width:"100%"
	// });

    $("#agregar_dni_2_factores #sec_dni_2_factores_guardar_btn").off("click").on("click",function(){
		var form = $("#agregar_dni_2_factores form")[0];
        // let tipo = $("#tipo").val();
        // console.log(tipo)
        // if(tipo == "servicio tecnico"){

        // }else{

        // }
        // console.log(form)
		sec_dni_2_factores_save(form);
	})

    $("#modal_detalle #sec_dni_2_factores_update_btn").off("click").on("click",function(){
		var form = $("#modal_detalle form")[0];
		sec_dni_2_factores_update(form);
	})
	$("#modal_detalle").off("shown.bs.modal").on("shown.bs.modal",function(){
		$('.modal').css('overflow-y', 'auto');
		loading();
	});

    // time_cod_verif
    $("#btn_dni_2_factores_save_104").off("click").on("click",function(){
		var form = $("#form_time_cod_verif")[0];
		set_dni_2_factores_save_104(form);
	})

    // max_intentos_sms
    $("#btn_dni_2_factores_save_105").off("click").on("click",function(){
		var form = $("#form_max_intentos_sms")[0];
		set_dni_2_factores_save_105(form);
	})

    // tiempo_intentos_sms
    $("#btn_dni_2_factores_save_106").off("click").on("click",function(){
		var form = $("#form_tiempo_intentos_sms")[0];
		set_dni_2_factores_save_106(form);
	})

    // 2doFactor_autenticacion
    $("#btn_dni_2_factores_save_107").off("click").on("click",function(){
		var form = $("#form_2doFactor_autenticacion")[0];
		set_dni_2_factores_save_107(form);
	})
}

function listar_dni_2_factores(){
	tablaserver = $("#tbl_dni_2_factores")
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
					datat.action = typeof action == "undefined" ? "sec_dni_2_factores_list" : action;

                	datat.fecha_inicio = $("#fecha_inicio").attr("data-fecha_formateada");
                	datat.fecha_fin = $("#fecha_fin").attr("data-fecha_formateada");

                    ajaxrepitiendo = $.ajax({
                        global: false,
                        url: "/sys/set_dni_2_factores.php",
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
	                {data:"dni",nombre:"dni",title:"DNI"},
                    {data:"red",nombre:"red",title:"Red"},
	                {data:"estado",nombre:"estado",title:"Estado"},
	                {data:"id",nombre:"id",orderable:false,title:"Detalle"
						,"render": function (data, type, row ) {
							var html = '<a class="btn btn-rounded btn-primary btn-sm ver_detalle" data-estado= "' + row["estado"] + '" title="Ver detalle">';
							html += '<i class="fa fa-eye"></i>';
							html += ' Ver';
							html += '</a>';
							return html;
	                	}
	            	}
                ],
                "drawCallback":function (){
					$("#tbl_dni_2_factores tbody .ver_detalle").off("click").on("click",function(){
						var solicitud_id = $(this).closest("tr").attr("id");
						var estado_id = $(this).attr("data-estado");
						sec_dni_2_factores_cargar_solicitud(solicitud_id,estado_id);
					})
                },
                "initComplete": function (settings, json) {
					setTimeout(function(){
						$("#servicio_tecnico_recargar").off("click").on("click",function(){
							tablaserver.ajax.reload(null, false);
						})
						tablaserver.columns.adjust();
					},100)
					// 0 => Nuevo, 1 => Atendido, 2 => Asignado
					action = "sec_dni_2_factores_list";
					filtrar_datatable_sec_dni_2_factores(settings,json);
                }
            });
	return tablaserver;
}

function sec_dni_2_factores_cargar_solicitud(solicitud_id,estado_id){
	loading(true);
    var set_data = {};
    set_data.solicitud_id = solicitud_id ;
	set_data.sec_dni_2_factores_cargar_solicitud = "sec_dni_2_factores_cargar_solicitud";
	set_data.estado_id = estado_id;
	$('#locales_redes').select2({
		placeholder: "Seleccione"
	});
	$('#estado').select2({
		placeholder: "Seleccione"
	});

	$.ajax({
		url: 'sys/set_dni_2_factores.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			loading();
			sec_servicio_tecnico_modal_detalle_events();
			$.each(obj.local,function(i,e){
				if($("#modal_detalle #" + i ).length > 0 ){
					if( i == "locales_redes" )
					{
						let estado = $("#modal_detalle #locales_redes [data-nombre = '" + e +"']").val();
						$("#modal_detalle #locales_redes").val( estado ).change();
					}else if( i == "estado" ){
						let estado = $("#modal_detalle #estado [data-nombre = '" + e +"']").val();
						$("#modal_detalle #estado").val( estado ).change();
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

            $("#modal_detalle #estado").val(obj.local["estado"]).change();
			$("#modal_detalle #locales_redes").val(obj.local["red_id"]).change();
			$("#modal_detalle").modal("show");
		},
		complete : function (){
			loading();
		},
        error: function () {
			loading();
       	}
	});
}

function sec_dni_2_factores_save(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_dni_2_factores_save","set_dni_2_factores_save");
	// dataForm.append("estado_vt", $("#estado option:selected" ,$(form)).text());
	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	// result.estado_vt = $("#estado option:selected" ,$(form)).text(); 
	var set_data = {};
	// if(result.foto_terminado_update.name != ""){
	// 	result.foto_terminado_update = result.foto_terminado_update.name; 
	// }
	// else{
	// 	result.foto_terminado_update = "";
	// }
	set_data = result;

	$.ajax({
		url: 'sys/set_dni_2_factores.php',
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
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_error","data":set_data});
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
				// set_data.curr_login = obj.curr_login;
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});

                $("#tbl_dni_2_factores").DataTable().ajax.reload();
				$("#agregar_dni_2_factores").modal("hide");

				document.getElementById("dni").value = "";
				document.getElementById("locales_redes").selectedIndex = 0;
				document.getElementById("estado").selectedIndex = 0;
			}
		}
	});
}

function sec_dni_2_factores_update(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_dni_2_factores_update","set_dni_2_factores_update");
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
		url: 'sys/set_dni_2_factores.php',
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
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_error","data":set_data});
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
				// set_data.curr_login = obj.curr_login;
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
				$("#tbl_dni_2_factores").DataTable().ajax.reload();
				$("#modal_detalle").modal("hide");
			}
		}
	});
}

function set_dni_2_factores_save_104(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_dni_2_factores_save_104","set_dni_2_factores_save_104");
    dataForm.append("time_cod_verif", $("#time_cod_verif" ,$(form)).val());
	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	var set_data = {};
	set_data = result;

	$.ajax({
		url: 'sys/set_dni_2_factores.php',
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
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_error","data":set_data});
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
				// set_data.curr_login = obj.curr_login;
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
			}
		}
	});
}

function set_dni_2_factores_save_105(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_dni_2_factores_save_105","set_dni_2_factores_save_105");
    dataForm.append("max_intentos_sms", $("#max_intentos_sms" ,$(form)).val());
	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	var set_data = {};
	set_data = result;

	$.ajax({
		url: 'sys/set_dni_2_factores.php',
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
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_error","data":set_data});
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
				// set_data.curr_login = obj.curr_login;
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
			}
		}
	});
}

function set_dni_2_factores_save_106(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_dni_2_factores_save_106","set_dni_2_factores_save_106");
    dataForm.append("tiempo_intentos_sms", $("#tiempo_intentos_sms" ,$(form)).val());
	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	var set_data = {};
	set_data = result;

	$.ajax({
		url: 'sys/set_dni_2_factores.php',
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
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_error","data":set_data});
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
				// set_data.curr_login = obj.curr_login;
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
			}
		}
	});
}

function set_dni_2_factores_save_107(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_dni_2_factores_save_107","set_dni_2_factores_save_107");
    dataForm.append("a2doFactor_autenticacion", $("#a2doFactor_autenticacion" ,$(form)).val());
	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	var set_data = {};
	set_data = result;

	$.ajax({
		url: 'sys/set_dni_2_factores.php',
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
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_error","data":set_data});
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
				// set_data.curr_login = obj.curr_login;
				// auditoria_send({"proceso":"sec_servicio_tecnico_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
			}
		}
	});
}

function filtrar_datatable_sec_dni_2_factores(settings,json){
	var boton_buscar = true;

	var localStorage_estado_var="sec_dni_2_factores_estado_select";
	var datatable = settings.oInstance.api();

	$("#btn_dni_2_factores_search").off("click").on("click",function(){
        action = "sec_dni_2_factores_list";
        datatable.ajax.reload(null, false);
	})

	$("#sec_dni_2_factores_estado_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(3).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#sec_dni_2_factores_estado_select").select2();

	setTimeout(function(){
		var valor = localStorage.getItem(localStorage_estado_var).split(',');
		$("#sec_dni_2_factores_estado_select").val(valor).change();
	},200);
	
	var localStorage_estado_var="sec_dni_2_factores_select";
	$("#locales_redes_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(2).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#locales_redes_select").select2();

	setTimeout(function(){
		var valor = localStorage.getItem(localStorage_estado_var).split(',');
		$("#locales_redes_select").val(valor).change();
	},200);
}
