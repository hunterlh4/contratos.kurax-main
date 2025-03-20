function sec_extorno(){
	if(sec_id == "extorno") {
		console.log("sec:extorno");
		sec_extorno_events();
	}
}
function sec_extorno_events(){
    tablaserver = listar_extorno();
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
	  $("#sec_extorno_zona_select").on("change", function() {
		let zona_id = $(this).find(':selected').attr("value");
		sec_extorno_cargar_locales(zona_id);
	})

	$(".select2")
		.filter(function(){
			return $(this).css('display') !== "none";
		})
		.select2({
		closeOnSelect: true,
		width:"100%"
	});
	$(".sec_extorno_btn_actualizar").off("click").on("click",function(){
    	tablaserver.ajax.reload(null, false);
    });

}
function sec_extorno_modal_detalle_events(){
	$("#modal_detalle #sec_extorno_guardar_btn").off("click").on("click",function(){
		var form = $("#modal_detalle form")[0];
		sec_extorno_update(form);
	})
	$("#modal_detalle").off("shown.bs.modal").on("shown.bs.modal",function(){
		$('.modal').css('overflow-y', 'auto');
		loading();
	});
	$("#modal_detalle").off("hidden.bs.modal").on("hidden.bs.modal",function(){
		$("#modal_detalle select, textarea ,input").val("");
		$("#modal_detalle form #detalles").empty();
	});
}
///////guardar sec_extorno_cargar_locales
function sec_extorno_cargar_locales(zona_id){
	loading(true);
    var set_data = {};
    set_data.sec_extorno_zona_select = zona_id ;
	set_data.sec_extorno_cargar_locales = "sec_extorno_cargar_locales";

	$.ajax({
		url: 'sys/set_extorno.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			$('#sec_extorno_local_select').empty();
			$(obj.locales).each(function(i,e){
				$('#sec_extorno_local_select').append($('<option>', { 
					value: e.id,
					text : e.nombre 
    			}));
			})
			$('#sec_extorno_local_select').select2();
			loading();
		}
	});
}

function sec_extorno_update(form){
	loading(true);
    var dataForm = new FormData(form);
	dataForm.append("set_extorno_update","set_extorno_update");
	dataForm.append("monto",$("#modal_detalle #monto").val());
	result = {};
	for (var entry of dataForm.entries())
	{
		result[entry[0]] = entry[1];
	}
	var set_data = {};
	set_data = result;

	$.ajax({
		url: 'sys/set_extorno.php',
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
				auditoria_send({"proceso":"sec_extorno_update_error","data":set_data});
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
				auditoria_send({"proceso":"sec_extorno_update_done","data":set_data});	
				swal({
					title: "Registro Exitoso",
					text: obj.mensaje,
					type: "success",
					closeOnConfirm: true,
					showCancelButton: false,
					showConfirmButton: true
				});
				tablaserver.ajax.reload();
				$("#modal_detalle").modal("hide");
			}
		}
	});
}

function listar_extorno(){
	tablaserver = 
			$("#tbl_extorno")
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
			.DataTable(
			{
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
                "order": [[ 0, "desc" ]],
			    buttons: [
			        {
			            text: '<span class="glyphicon glyphicon-refresh"></span>',
			            action: function ( e, dt, node, config ) {
			                tablaserver.ajax.reload(null,false);
			            }
			        }
			    ],
                ajax: function (datat, callback, settings) {////AJAX DE CONSULTA                    
                	datat.action = typeof action == "undefined" ? "sec_extorno_list" : action;

                	datat.fecha_inicio = $("#fecha_inicio").attr("data-fecha_formateada");
                	datat.fecha_fin = $("#fecha_fin").attr("data-fecha_formateada");

                    ajaxrepitiendo = $.ajax({
                        global: false,
                        url: "/sys/set_extorno.php",
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
                            if(datat.action == "sec_extorno_list_excel"){
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
	                {data:"Registro",nombre:"Registro",title:"Registro"},
	                {data:"Zona",nombre:"Zona",title:"Zona"},
	                {data:"Tipo",nombre:"Tipo",title:"Tipo"},
                    {data:"Monto",nombre:"Monto",title:"Monto" ,className: "text-right"},
	                {data:"Cliente",nombre:"Cliente",title:"Cliente"},
	                {data:"Cajero",nombre:"Cajero",title:"Cajero"},
	                {data:"Local",nombre:"Local",title:"Local"},
	                {data:"Transacción Id",nombre:"Transacción Id",title:"Transacción Id",defaultContent: "-"},
	                {data:"Motivo",nombre:"Motivo",title:"Motivo",defaultContent: "-"},
	                {data:"Estado",nombre:"Estado",title:"Estado"},
	                {data:"id",nombre:"id",title:"Acción"
						,"render": function (data, type, row ) 
						{
							var estado = row["estado_extorno_id"];/*1 2 3 sol , aprob, rechzd */
							var html = '';
							if(estado == 1)
							{
								html += '<button class="btn btn-xs btn-rounded btn-success btn-sm btn_aprobar_extorno" data-estado= "' + row["estado_extorno_id"] + '" title="Aprobar">';
								html += '<i class="fa fa-check"></i>';
								html += '</button>&nbsp;';
								html += '<button class="btn btn-xs btn-rounded btn-danger btn-sm btn_rechazar_extorno" data-estado= "' + row["estado_extorno_id"] + '" title="Rechazar">';
								html += '<i class="fa fa-close"></i>';
								html += '</button>';
							}
							else
							{
								html = " -- " ; 
							}
							return html;
	                	}
	            	},
	                {data:"Usuario Soporte",nombre:"Usuario Soporte",title:"Usuario Soporte"},
	                {data:"Fecha Proceso",nombre:"Fecha Proceso",title:"Fecha Proceso"},
	                {data:"Monto Aplicado",nombre:"Monto Aplicado",title:"Monto Aplicado" ,className: "text-right"}
                ],
                "drawCallback":function (){
					$("#tbl_extorno tbody .btn_aprobar_extorno").off("click").on("click",function(){
						var id = $(this).closest("tr").attr("id");
						var estado_id = $(this).attr("data-estado");
						sec_extorno_cargar_solicitud(id,estado_id);
					})
					$("#tbl_extorno tbody .btn_rechazar_extorno").off("click").on("click",function(){
						var id = $(this).closest("tr").attr("id");
						var estado_id = $(this).attr("data-estado");
						swal({
							title: 'Rechazar Extorno',
							text: "Está seguro que desea rechazar extorno? ",
							html: true,
							type: "warning",
							showCancelButton: true,
							confirmButtonColor: "#DD6B55",
							confirmButtonText: "Si",
							cancelButtonText:"No",
							closeOnConfirm: false,
							closeOnCancel: false
						},
						function(opt){
							if(opt){
								sec_extorno_rechazar(id);
							}else{
							  swal.close();
							}
				
						});

					})
                },
                "initComplete": function (settings, json) {
					setTimeout(function(){
						$("#extorno_recargar").off("click").on("click",function(){
							tablaserver.ajax.reload(null, false);
						})
						tablaserver.columns.adjust();
					},100)
					// 0 => Nuevo, 1 => Atendido, 2 => Asignado
					action = "sec_extorno_list";
					filtrar_datatable_sec_extorno(settings,json);
                }
            });
	return tablaserver;
}
function sec_extorno_rechazar(id){
	loading(true);
    var set_data = {};
    set_data.id = id ;
	set_data.sec_extorno_solicitud_rechazar = "sec_extorno_solicitud_rechazar";

	$.ajax({
		url: 'sys/set_extorno.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			loading();
			auditoria_send({"proceso":"sec_extorno_rechazar","data":set_data});	
				swal({
					title: "Rechazar Extorno",
					text: obj.mensaje,
					type: "success",
					closeOnConfirm: true,
					showCancelButton: false,
					showConfirmButton: true
				});
				tablaserver.ajax.reload();
		},
		complete : function (){
			loading();
		},
        error: function () {
			loading();
       	}
	});
}
function sec_extorno_cargar_solicitud(id){
	loading(true);
    var set_data = {};
    set_data.id = id ;
	set_data.sec_extorno_solicitud_detalle = "sec_extorno_solicitud_detalle";

	$.ajax({
		url: 'sys/set_extorno.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			loading();
			sec_extorno_modal_detalle_events();
			$.each(obj.registro,function(i,e){
				if(e == null)
                {
                    return true;
                }
				style = "";
				if( i == "saldo_web_transaccion_id")
                {
                    style = " style='display:none'";
                }
				var html = '<div class="form-group"' + style + '>';
					html +=		'<label  class="col-xs-4 control-label" for="">';
					html += 		i;
					html +=		'</label>';
					html +=		'<div class="col-xs-8">';
					html +=			'<p class="form-control-static">';
					html += 			e;
					html += 		'</p>';
					html +=		'</div>';
					html +=	'</div>';
				$("#modal_detalle form #detalles").prepend(html);
			})
			$("#modal_detalle #monto_aplicado").val(obj.registro["Monto"]);
			$("#modal_detalle #saldo_web_transaccion_id").val(obj.registro["saldo_web_transaccion_id"]);
			$("#modal_detalle #monto").val(obj.registro["Monto"]);
			$("#modal_detalle #id").val(obj.registro["id"]);
			
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

function filtrar_datatable_sec_extorno(settings,json){
	var boton_buscar = true;

	var localStorage_estado_var="sec_extorno_estado_select";
	var datatable = settings.oInstance.api();

	$("#btn_extorno_search").off("click").on("click",function(){
        action = "sec_extorno_list";
        datatable.ajax.reload(null, false);
	})
	$("#btn_extorno_excel").off("click").on("click",function(){
		action = "sec_extorno_list_excel";
		datatable.ajax.reload(null, false);
	})

	$("#sec_extorno_estado_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(9).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#sec_extorno_estado_select").select2();

	setTimeout(function(){
		if(localStorage.getItem(localStorage_estado_var))
		{
			var valor = localStorage.getItem(localStorage_estado_var).split(',');
			$("#sec_extorno_estado_select").val(valor).change();
		}
	},200);
	

	var localStorage_estado_var="sec_extorno_local_select";
	$("#sec_extorno_local_select").off("change").on("change",function(){
		var val = $(this).val();
		datatable.column(6).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#sec_extorno_local_select").select2();


	var localStorage_estado_var="sec_extorno_zona_select";
	$("#sec_extorno_zona_select").off("change").on("change",function(){
		//let zona_id = $(this).find(':selected').attr("value");
		let zona_id = $("#sec_extorno_zona_select").val();
		sec_extorno_cargar_locales(zona_id);

		var val = $(this).val();
		datatable.column(1).search(val);//.draw();
		if(!boton_buscar){
			datatable.ajax.reload(null, false);
		}
		datatable.columns.adjust();
		localStorage.setItem(localStorage_estado_var,val);
	})
	$("#sec_extorno_zona_select").select2();

	var localStorage_estado_var="sec_extorno_sistema_select";
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
