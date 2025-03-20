$(document).ready(function() {
    $('#razon_social_id').change(function() {
        var razon_social_id = $(this).val();
        $.ajax({
            type: 'POST',
            url: 'sys/set_solicitud_estimacion.php',
            data: {razon_social_id: razon_social_id},
            dataType: 'json',
            success: function(data) {
                $('#zona_id').empty();
                $.each(data, function(key, value) {
                    $('#zona_id').append('<option value="' + value.id + '">' + value.nombre + '</option>');
                });
            }
        });
    });
});

$(document).ready(function() {
	$("#sec_sol_estim_tipo").change(function() {
		console.log($(this).val())
		if($(this).val() == 1){
			$("#div-fil-sistemas").hide();
            $("#div-fil-equipos").show();
		}else if($(this).val() == 2){
			$("#div-fil-sistemas").show();
            $("#div-fil-equipos").hide();
		}
	});
});

function sec_solicitud_estimacion(){
	if(sec_id == "solicitud_estimacion"  ||  sec_id=="solicitud_estimacion_form") {
		console.log("sec:solicitud_estimacion");
		sec_solicitud_estimacion_events();
	}
}

function sec_solicitud_estimacion_events(){
	$(".sistema").hide();
    $("#agregar_estimacion_servicio_tecnico #tipo").off("change").on("change",function(){
        let tipo = $(this).val();
        if($(this).val() == "servicio tecnico" ){
            $(".equipo").show();
            $(".sistema").hide();
            // $(".foto_terminado").hide();
            // $(".div_comentario_terminado").hide();
            // $("#div_derivacion_tecnico").hide();
            // $(".div_comentario_terminado_editar").hide();
        }
        if($(this).val() == "mantenimiento" ){
            $(".sistema").show();
            $(".equipo").hide();
            // $(".comentario").show();
            // $(".div_comentario_terminado").hide();
            // $("#div_derivacion_tecnico").show();
            // $(".div_comentario_terminado_editar").hide();
        }
    })

    $("#agregar_estimacion_servicio_tecnico #sec_solicitud_estimacion_guardar_btn").off("click").on("click",function(){
		var form = $("#agregar_estimacion_servicio_tecnico form")[0];
        // let tipo = $("#tipo").val();
        // console.log(tipo)
        // if(tipo == "servicio tecnico"){

        // }else{

        // }
        // console.log(form)
		sec_solicitud_estimacion_save(form);
	})

    tablaserver = listar_solicitud_estimacion();

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

function sec_solicitud_estimacion_save(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_solicitud_estimacion_save","set_solicitud_estimacion_save");
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
		url: 'sys/set_solicitud_estimacion.php',
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
				set_data.curr_login = obj.curr_login;
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

                // if(obj.table_reload == 'servicio tecnico'){
                //     $("#tbl_solicitud_estimacion_servicio_tecnico").DataTable().ajax.reload();
                // }
                // if(obj.table_reload == 'mantenimiento'){
                    // $("#tbl_solicitud_estimacion_mantenimiento").DataTable().ajax.reload();
                // }
                $("#tbl_solicitud_estimacion_servicio_tecnico").DataTable().ajax.reload();
				$("#agregar_estimacion_servicio_tecnico").modal("hide");

				document.getElementById("t_revision").value = "";
				document.getElementById("t_coordinacion").value = "";
				document.getElementById("valor_v").value = "";
				document.getElementById("valor_e").value = "";
				document.getElementById("razon_social_id").selectedIndex = 0;
				document.getElementById("zona_id").value = "";
				document.getElementById("departamento_id").selectedIndex = 0;
				document.getElementById("equipo_id").selectedIndex = 0;
				document.getElementById("sistema_id").selectedIndex = 0;
			}
		}
	});
}

function listar_solicitud_estimacion(){
	tablaserver = $("#tbl_solicitud_estimacion_servicio_tecnico")
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
                    "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "infoEmpty": "No hay registros",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
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
					datat.action = typeof action == "undefined" ? "sec_solicitud_estimacion_list" : action;

                	// datat.fecha_inicio = $("#fecha_inicio").attr("data-fecha_formateada");
                	// datat.fecha_fin = $("#fecha_fin").attr("data-fecha_formateada");

                    ajaxrepitiendo = $.ajax({
                        global: false,
                        url: "/sys/set_solicitud_estimacion.php",
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
							// if(datat.action == "sec_servicio_tecnico_list_excel"){
                            // 	$(".dataTables_processing").hide();
                            // 	window.open(respuesta.path);
                            // 	return;
                            // }
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
					{data:"razon_social",nombre:"razon_social",title:"Razón Social"},
	                {data:"zona",nombre:"zona",title:"Zona"},
	                {data:"provincia",nombre:"provincia",title:"Provincia",
                        defaultContent: "-"},
	                {data:"equipo",nombre:"equipo",title:"Equipo"},
	                {data:"t_revision",nombre:"t_revision",title:"T. Revisión"},
	                {data:"t_coordinacion",nombre:"t_coordinacion",title:"T. Coordinación"},
	                {data:"valor_v",nombre:"valor_v",title:"Valor V"},
	                {data:"valor_e",nombre:"valor_e",title:"Valor E"},
	                {data:"tipo",nombre:"tipo",title:"Tipo"},
					{data:"estado",nombre:"estado",title:"Estado"},
	                {data:"id",nombre:"id",orderable:false,title:"Detalle"
						,"render": function (data, type, row ) {
							var html = '<a class="btn btn-rounded btn-primary btn-sm ver_detalle" data-tipo= "' + row["tipo"] + '" title="Ver detalle">';
							html += '<i class="fa fa-eye"></i>';
							html += ' Ver';
							html += '</a>';
							return html;
	                	}
	            	}
                ],
                "drawCallback":function (){
					$("#tbl_solicitud_estimacion_servicio_tecnico tbody .ver_detalle").off("click").on("click",function(){
						var solicitud_id = $(this).closest("tr").attr("id");
						var tipo = $(this).attr("data-tipo");
						sec_solicitud_estimacion_cargar_solicitud(solicitud_id,tipo);
					})
                },
                "initComplete": function (settings, json) {
					setTimeout(function(){
						$("#solicitud_estimacion_recargar").off("click").on("click",function(){
							tablaserver.ajax.reload(null, false);
						})
						tablaserver.columns.adjust();
					},100)
					// 0 => Nuevo, 1 => Atendido, 2 => Asignado
					action = "sec_solicitud_estimacion_list";
					filtrar_datatable_sec_solicitud_estimacion(settings,json);
                }
            });
	return tablaserver;
}

function filtrar_datatable_sec_solicitud_estimacion(settings,json){
	var boton_buscar = true;

	var localStorage_estado_var="sec_solicitud_estimacion_estado_select";
	var datatable = settings.oInstance.api();

	$("#btn_solicitud_estimacion_search").off("click").on("click",function(){
		var tipo_b = $('#sec_sol_estim_tipo').val();
		if(tipo_b == 0){
			console.log("no pasa nada");
		}else if(tipo_b == 1){
			// const div = document.querySelector("#div-fil-equipos");		 
			// div.className;      
			// div.getAttribute("class");  
			// div.className = "col-lg-1 col-xs-12";
			// div.setAttribute("class", "col-lg-1 col-xs-12"); 

			// const div2 = document.querySelector("#div-fil-zona");		 
			// div2.className;      
			// div2.getAttribute("class");  
			// div2.className = "col-lg-1 col-xs-12";
			// div2.setAttribute("class", "col-lg-1 col-xs-12"); 

			// $("#div_filtros_mant").show();
            $("#div-fil-sistemas").hide();
            $("#div-fil-equipos").show();

			$("#div_list_estimacion_mantenimiento").hide();
			$("#div_list_estimacion_servicio_tecnico").show();
			action = "sec_solicitud_estimacion_list";
			datatable.ajax.reload(null, false);
		}else if(tipo_b == 2){
			// const div = document.querySelector("#div-fil-est"); 
			// div.className;   
			// div.getAttribute("class");  
			// div.className = "col-lg-1 col-xs-12";
			// div.setAttribute("class", "col-lg-2 col-xs-12"); 

			// const div2 = document.querySelector("#div-fil-zona");		 
			// div2.className;      
			// div2.getAttribute("class");  
			// div2.className = "col-lg-1 col-xs-12";
			// div2.setAttribute("class", "col-lg-2 col-xs-12"); 

			// $("#div_filtros_mant").hide();

            $("#div-fil-sistemas").show();
            $("#div-fil-equipos").hide();

            $("#div_list_estimacion_mantenimiento").show();
			$("#div_list_estimacion_servicio_tecnico").hide();
			listar_solicitudes_estimacion_mantenimiento();
			
		}
		
	})
	$("#btn_solicitud_mantenimiento_excel").off("click").on("click",function(){
		action = "sec_solicitud_mantenimiento_list_excel";
		datatable.ajax.reload(null, false);
	})

	$("#btn_solicitud_mantenimiento_dias_excel").off("click").on("click",function(){
		action = "sec_solicitud_mantenimiento_list_dias_excel";
		datatable.ajax.reload(null, false);
	})

	// $("#estado_select").off("change").on("change",function(){
	// 	var val = $(this).val();
	// 	datatable.column(7).search(val);//.draw();
	// 	if(!boton_buscar){
	// 		datatable.ajax.reload(null, false);
	// 	}
	// 	datatable.columns.adjust();
	// 	localStorage.setItem(localStorage_estado_var,val);
	// })
	// $("#estado_select").select2();

	// if(localStorage.getItem(localStorage_estado_var) && localStorage.getItem(localStorage_estado_var)!="null"){
	// 	setTimeout(function(){
	// 		var valor = localStorage.getItem(localStorage_estado_var).split(',');
	// 		$("#estado_select").val(valor).change();
	// 	},200);
	// }
	// else{
	// 	setTimeout(function(){
	// 		$("#estado_select").val([0,2]).change();//nuevos,asignados
	// 	},200);
	// }

	var localStorage_estado_var="sec_solicitud_estimacion_zona_select";
	$("#zona_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(2).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#zona_select").select2();


	var localStorage_estado_var="sec_solicitud_estimacion_departamento_select";
	$("#departamento_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(3).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#departamento_select").select2();

	var localStorage_estado_var="sec_solicitud_estimacion_equipo_select";
	$("#equipo_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(4).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#equipo_select").select2();

	var localStorage_estado_var="sec_solicitud_estimacion_estado_select";
	$("#estado_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(10).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#estado_select").select2();

	// var localStorage_estado_var="sec_solicitud_mantenimento_sistema_select";
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

}

function listar_solicitudes_estimacion_mantenimiento(){

	limpiar_tabla_mantenimiento()

	var sec_sol_mt_sistema = $.trim($("#sistema_select").val());
	var sec_sol_mt_zona = $.trim($("#zona_select").val());
	var sec_sol_mt_departamento = $.trim($("#departamento_select").val());
	var sec_sol_mt_estado = $.trim($("#estado_select").val());
	// var sec_sol_mt_fecha_inicio = $("#fecha_inicio").attr("data-fecha_formateada");
	// var sec_sol_mt_fecha_fin = $("#fecha_fin").attr("data-fecha_formateada");

	var data = {
		"action": "sec_solicitud_estimacion_mantenimiento_list",
		"sistema_select": sec_sol_mt_sistema,
		"zona_select": sec_sol_mt_zona,
		"departamento_select": sec_sol_mt_departamento,
		"estado_select": sec_sol_mt_estado,
		// "estado": sec_sol_mt_estado,
		// "zona": sec_sol_mt_zona ,
		// "tienda": sec_sol_mt_tienda,
		// "fecha_inicio": sec_sol_mt_fecha_inicio,
        // "fecha_fin": sec_sol_mt_fecha_fin 
	}

	// auditoria_send({ proceso: "listar_derivaciones_vt", data: data });
	$.ajax({
		url: "/sys/set_solicitud_estimacion.php",
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
			if (parseInt(respuesta.http_code) == 400) {
				DATATABLE_FORMATO_tabla_mantenimiento("#tbl_solicitud_estimacion_mantenimiento");
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
						$("#tbl_solicitud_estimacion_mantenimiento").append(
							"<tr>" +
							'<td class="text-center">' +
							item.id +
							"</td>" +
							'<td class="text-center">' +
							item.razon_social +
							"</td>" +
							'<td class="text-center">' +
							item.zona +
							"</td>" +
							'<td class="text-center">' +
							(item.provincia != null ? item.provincia : '-') +
							"</td>" +
							'<td class="text-center">' +
							item.sistema +
							"</td>" +				  
							'<td class="text-center">' +
							item.t_revision +
							"</td>" +
							'<td class="text-center">' +
							item.t_coordinacion +
							"</td>" +
							'<td class="text-center">' +
							item.valor_v +
							"</td>" +	
							'<td class="text-center">' +
							item.valor_e +
							"</td>" +					  
							'<td class="text-center">' +
							item.tipo +
							"</td>" +		
							'<td class="text-center">' +
							item.estado +
							"</td>" +	  
							'<td class="text-center"><button type="button" class="btn btn-rounded btn-primary btn-sm ver_detalle" title="Ver detalle" onclick="sec_solicitud_estimacion_mantenimiento_cargar_solicitud(' +
							item.id  +
							')"><i class="fa fa-eye"></i> Ver</button>' +
							"</td>" +			   
							"</tr>"
						);
					});
					DATATABLE_FORMATO_tabla_mantenimiento("#tbl_solicitud_estimacion_mantenimiento");
				}else{
					$("#tbl_solicitud_estimacion_mantenimiento").append("<tr>" + '<td class="text-center" colspan="12">No hay transacciones.</td>' + "</tr>");
				}
				//console.log(array_clientes);
				return false;
			}
		},
		error: function () {},
		});
}

function DATATABLE_FORMATO_tabla_mantenimiento(id) {
	if ($.fn.dataTable.isDataTable(id)) {
		$(id).DataTable().destroy();
	}
	$(id).DataTable({
		paging: true,
		lengthChange: true,
		searching: true,
		ordering: true,
		order: [[0, "desc"]],
		info: true,
		autoWidth: false,
		language: {
		processing: "Procesando...",
		lengthMenu: "Mostrar _MENU_ registros",
		zeroRecords: "No se encontraron resultados",
		emptyTable: "Ningún dato disponible en esta tabla",
		info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
		infoEmpty: "No hay registros",
		infoFiltered: "(filtrado de un total de _MAX_ registros)",
		infoPostFix: "",
		search: "Buscar: ",
		url: "",
		infoThousands: ",",
		loadingRecords: "Cargando...",
		paginate: {
			first: "Primero",
			last: "Último",
			next: "Siguiente",
			previous: "Anterior",
		},
		aria: {
			sortAscending: ": Activar para ordenar la columna de manera ascendente",
			sortDescending: ": Activar para ordenar la columna de manera descendente",
		},
		},
	});
}

function limpiar_tabla_mantenimiento() {
	$("#tbl_solicitud_estimacion_mantenimiento").html(
		"<thead>" +
		"   <tr>" +
		'		<th class="text-center">ID</th>' +		
		'		<th class="text-center">Razón Social</th>' +		                    
		'		<th class="text-center">Zona</th>' + 
		'		<th class="text-center">Provincia</th>' +
		'		<th class="text-center">Sistema</th>' + 
		'		<th class="text-center">T. Revisión</th>' +
		'		<th class="text-center">T. Coordinación</th>' +	
		'		<th class="text-center">Valor V</th>' +
		'		<th class="text-center">Valor E</th>' +		
		'		<th class="text-center">Tipo</th>' +		
		'		<th class="text-center">Estado</th>' +	                    
		'		<th class="text-center">Detalle</th>' + 
		"   </tr>" +
		"</thead>" +
		"<tbody>"
	);
}

// DETALLE MODAL SOLICITUD ESTIMACION
function sec_solicitud_estimacion_cargar_solicitud(solicitud_id,tipo){
	loading(true);
    var set_data = {};
    set_data.solicitud_id = solicitud_id ;
	set_data.sec_solicitud_estimacion_cargar_solicitud = "sec_solicitud_estimacion_cargar_solicitud";
	set_data.tipo = tipo;
	// $('#zona_id').select2({
	// 	placeholder: "Seleccione"
	// });
	// $('#departamento_id').select2({
	// 	placeholder: "Seleccione"
	// });
    // $('#equipo_id').select2({
	// 	placeholder: "Seleccione"
	// });
    // $('#sistema_id').select2({
	// 	placeholder: "Seleccione"
	// });

	$.ajax({
		url: 'sys/set_solicitud_estimacion.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			loading();
            sec_solicitud_estimacion_modal_detalle_events();
			// sec_servicio_tecnico_modal_detalle_events();
			$.each(obj.solicitud,function(i,e){
				if($("#modal_detalle #" + i ).length > 0 ){
					if( i == "zona_id" )
					{
						let estado = $("#modal_detalle #zona_id [data-nombre = '" + e +"']").val();
						$("#modal_detalle #zona_id").val( estado ).change();
					}else if( i == "provincia" ){
						let estado = $("#modal_detalle #provincia [data-nombre = '" + e +"']").val();
						$("#modal_detalle #provincia").val( estado ).change();
					}else if( i == "equipo_id" ){
						let estado = $("#modal_detalle #equipo_id [data-nombre = '" + e +"']").val();
						$("#modal_detalle #equipo_id").val( estado ).change();
					}
					// else if( i == "razon_social_id_modal" ){
					// 	let estado = $("#modal_detalle #razon_social_id_modal [data-nombre = '" + e +"']").val();
					// 	$("#modal_detalle #razon_social_id_modal").val( estado ).change();
					// }
					
                    if($("#modal_detalle #"+i).length > 0 ){
                        if($("#modal_detalle #"+i)[0].nodeName == "P" || $("#modal_detalle #"+i)[0].nodeName == "DIV")
                        {
                            $("#modal_detalle #"+i).text(e);
                        }
                        else{
                            $("#modal_detalle #"+i).val(e);
                        }
                    }
				}
			})

			$('#razon_social_id_modal').change(function() {
				var razon_social_id = $(this).val();
				$.ajax({
					type: 'POST',
					url: 'sys/set_solicitud_estimacion.php',
					data: {razon_social_id: razon_social_id},
					dataType: 'json',
					success: function(data) {
						console.log(data)
						$('#zona_id_modal').empty();
						$.each(data, function(key, value) {
							$('#zona_id_modal').append('<option value="' + value.id + '">' + value.nombre + '</option>');
						});
					}
				});
			});

			$("#modal_detalle #tipo").off("change").on("change",function(){
				let tipo = $(this).val();

				if($(this).val() == "servicio tecnico"){
                    $(".equipo").show();
                    $(".sistema").hide();
				}
			})
            
			$("#modal_detalle #razon_social_id_modal").val(obj.solicitud["razon_social_id"]);
			$("#modal_detalle #zona_id_modal").val(obj.solicitud["zona_id"]);
			// $("#comentario_mantenimiento").val(obj.local.comentario_mantenimiento);

			$("#modal_detalle #tipo").val(obj.solicitud["tipo"]).change();
			$("#modal_detalle").modal("show");

            if(obj.solicitud.provincia == null){
                $("#provincia").val(0);
            }else{
                $("#provincia").val(obj.solicitud.provincia);
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

function sec_solicitud_estimacion_mantenimiento_cargar_solicitud(solicitud_id){
	loading(true);

    var set_data = {};
    set_data.solicitud_id = solicitud_id ;
	set_data.sec_solicitud_estimacion_mantenimiento_cargar_solicitud = "sec_solicitud_estimacion_mantenimiento_cargar_solicitud";

	$.ajax({
		url: 'sys/set_solicitud_estimacion.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
            // console.log(obj)
			loading();
			sec_solicitud_estimacion_modal_detalle_events();
			// $("#check_comercial_div").hide();
			$.each(obj.solicitud,function(i,e){
				if( i == "zona_id" )
                {
                    let estado = $("#modal_detalle #zona_id [data-nombre = '" + e +"']").val();
                    $("#modal_detalle #zona_id").val( estado ).change();
                }else if( i == "provincia" ){
                    let estado = $("#modal_detalle #provincia [data-nombre = '" + e +"']").val();
                    $("#modal_detalle #provincia").val( estado ).change();
                }else if( i == "sistema_id" ){
                    let estado = $("#modal_detalle #sistema_id [data-nombre = '" + e +"']").val();
                    $("#modal_detalle #sistema_id").val( estado ).change();
                }

                if($("#modal_detalle #"+i).length > 0 ){
					if($("#modal_detalle #"+i)[0].nodeName == "P" || $("#modal_detalle #"+i)[0].nodeName == "DIV")
					{
						$("#modal_detalle #"+i).text(e);
					}
					else{
						$("#modal_detalle #"+i).val(e);
					}
				}
			})
			
			// $("#div-tipo-mant-modal").hide();
			// $("#div-sistema-modal").hide();

			$('#razon_social_id_modal').change(function() {
				var razon_social_id = $(this).val();
				$.ajax({
					type: 'POST',
					url: 'sys/set_solicitud_estimacion.php',
					data: {razon_social_id: razon_social_id},
					dataType: 'json',
					success: function(data) {
						console.log(data)
						$('#zona_id_modal').empty();
						$.each(data, function(key, value) {
							$('#zona_id_modal').append('<option value="' + value.id + '">' + value.nombre + '</option>');
						});
					}
				});
			});

			$("#modal_detalle #tipo").off("change").on("change",function(){
				let tipo = $(this).val();
				
				if($(this).val() == "mantenimiento"){
                    $(".sistema").show();
                    $(".equipo").hide();
				}
			})

			$("#modal_detalle #razon_social_id_modal").val(obj.solicitud["razon_social_id"]);
			$("#modal_detalle #zona_id_modal").val(obj.solicitud["zona_id"]);

			$("#modal_detalle #tipo").val(obj.solicitud["tipo"]).change();
			$("#modal_detalle").modal("show");

            if(obj.solicitud.provincia == null){
                $("#provincia").val(0);
            }else{
                $("#provincia").val(obj.solicitud.provincia);
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

function sec_solicitud_estimacion_modal_detalle_events(){
	$("#modal_detalle #sec_solicitud_estimacion_update_btn").off("click").on("click",function(){
		var form = $("#modal_detalle form")[0];
		var tipo_b = $('#tipo').val();
        // console.log(tipo_b)
		if (tipo_b == "servicio tecnico"){
			// console.log("entro")
			sec_solicitud_estimacion_servicio_tecnico_update(form);
		}else if(tipo_b == "mantenimiento"){
			// console.log("entro2")
			sec_solicitud_estimacion_servicio_tecnico_update(form);
		}

	})
	// $("#modal_detalle").off("shown.bs.modal").on("shown.bs.modal",function(){
	// 	$('.modal').css('overflow-y', 'auto');
	// 	loading();
	// });
	// $("#modal_detalle").off("hidden.bs.modal").on("hidden.bs.modal",function(){
	// 	$(".comentario, .foto_terminado").hide();
	// 	$("#modal_detalle select, textarea ,input:file").val("");
	// 	$("#modal_detalle #foto_terminado").attr("src","data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==");
	// });
}

function sec_solicitud_estimacion_servicio_tecnico_update(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("sec_solicitud_estimacion_servicio_tecnico_update","sec_solicitud_estimacion_servicio_tecnico_update");
    dataForm.append("tipo", $("#tipo option:selected" ,$(form)).text());

	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	var set_data = {};
	result.zona_id = $('#zona_id_modal').val();
	// if(result.foto_terminado_update.name != ""){
	// 	result.foto_terminado_update = result.foto_terminado_update.name; 
	// }
	// else{
	// 	result.foto_terminado_update = "";
	// }
	set_data = result;

	$.ajax({
		url: 'sys/set_solicitud_estimacion.php',
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
				// auditoria_send({"proceso":"sec_solicitud_mantenimiento_update_error","data":set_data});
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
				// auditoria_send({"proceso":"sec_solicitud_mantenimiento_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false,
					showConfirmButton: true
				});
				$("#tbl_solicitud_estimacion_servicio_tecnico").DataTable().ajax.reload();
                // $("#tbl_solicitud_estimacion_mantenimiento").DataTable().ajax.reload();
				$("#modal_detalle").modal("hide");
			}
		}
	});
}

// function sec_solicitud_estimacion_mantenimiento_update(form){
// 	loading(true);
//     var dataForm = new FormData(form);
// 	dataForm.append("sec_solicitud_estimacion_mantenimiento_update","sec_solicitud_estimacion_mantenimiento_update");
//     dataForm.append("tipo", $("#tipo option:selected" ,$(form)).text());

// 	result = {};
// 	for (var entry of dataForm.entries())
// 	{
// 		result[entry[0]] = entry[1];
// 	}
// 	var set_data = {};
// 	// if(result.foto_terminado_update.name != ""){
// 	// 	result.foto_terminado_update = result.foto_terminado_update.name; 
// 	// }
// 	// else{
// 	// 	result.foto_terminado_update = "";
// 	// }
// 	set_data = result;

// 	$.ajax({
// 		url: 'sys/set_solicitud_estimacion.php',
// 		type: 'POST',
// 		data: dataForm,
// 		cache: false,
// 		contentType: false,
// 		processData: false,
// 		success: function(r){
// 			var obj = jQuery.parseJSON(r);
// 			if(obj.error){
// 				set_data.error = obj.error;
// 				set_data.error_msg = obj.error_msg;
// 				// auditoria_send({"proceso":"sec_solicitud_mantenimiento_update_error","data":set_data});
// 				loading(false);
// 				swal({
// 					title: "Error!",
// 					text: obj.error_msg,
// 					type: "warning",
// 					timer: 3000,
// 					closeOnConfirm: true
// 				},
// 				function(){
// 					swal.close();
// 				});
// 			}else{
// 				loading(false);
// 				set_data.curr_login = obj.curr_login;
// 				// auditoria_send({"proceso":"sec_solicitud_mantenimiento_update_done","data":set_data});	
// 				swal({
// 					title: "Registro Exitoso",
// 					text: obj.mensaje,
// 					type: "success",
// 					timer: 3000,
// 					closeOnConfirm: false,
// 					showCancelButton: false,
// 					showConfirmButton: true
// 				});
// 				// $("#tbl_solicitud_estimacion_servicio_tecnico").DataTable().ajax.reload();
//                 $("#tbl_solicitud_estimacion_mantenimiento").DataTable().ajax.reload();
// 				$("#modal_detalle").modal("hide");
// 			}
// 		}
// 	});
// }

function importar_archivo_servicio_tecnico(){

	var archivo = $('#archivo_solicitud_servicio_tecnico')[0];

	if (archivo.files.length === 0) {
		loading(false);
		swal({
			title: "¡Error!",
			text: "No se ha seleccionado ningún archivo.",
			type: "warning",
			timer: 3000,
			closeOnConfirm: true
		},
		function(){
			swal.close();
		});
	}else{
		loading(true);
		var dataForm = new FormData($('#subir_archivo')[0]);
		dataForm.append("sec_solicitud_estimacion_servicio_tecnico_upload","sec_solicitud_estimacion_servicio_tecnico_upload");
		
		$.ajax({
			url: 'sys/set_solicitud_estimacion.php',
			type: 'POST',
			data: dataForm,
			contentType: false,
			processData: false,
			success: function(r) {
				loading();
				console.log(r)
				var obj = JSON.parse(r);
				console.log(obj)
				if(obj.success){
					// window.location.href = '/?sec_id=solicitud_estimacion&sub_sec_id=';
					console.log(obj)
				}else if(obj.error){
					loading(false);
					swal({
						title: "¡Error!",
						text: obj.error,
						type: "warning",
						timer: 3000,
						closeOnConfirm: true
					},
					function(){
						swal.close();
					});
				}
				if(obj.error_formato){
					loading(false);
					swal({
						title: "¡Error!",
						text: obj.error_formato,
						type: "warning",
						timer: 3000,
						closeOnConfirm: true
					},
					function(){
						swal.close();
					});
				}
				if(obj.error_query){
					loading(false);
					swal({
						title: "¡Error!",
						text: obj.error_query,
						type: "warning",
						timer: 3000,
						closeOnConfirm: true
					},
					function(){
						swal.close();
					});
				}
				// loading();
				// if(obj.error){
				// 	loading(false);
				// 	swal({
				// 		title: "¡Error!",
				// 		text: "Algo fallo",
				// 		type: "warning",
				// 		timer: 3000,
				// 		closeOnConfirm: true
				// 	},
				// 	function(){
				// 		swal.close();
				// 	});
				// }else{
				// 	loading(false);
				// 	console.log('entro');
				// 	swal({
				// 		title: "Registro Exitoso",
				// 		text: "Correcto",
				// 		type: "success",
				// 		timer: 3000,
				// 		closeOnConfirm: false,
				// 		showCancelButton: false,
				// 		showConfirmButton: true
				// 	});
				// }
			},
			complete : function (){
				loading();
			},
			error: function () {
				loading();
			   }
		});
	}

}

function importar_archivo_ejemplo(){
	document.location = '../files_solicitud_estimacion/ejemplo_formato_solicitud_estimacion_tiempos.ods';
}