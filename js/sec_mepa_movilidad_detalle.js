$(document).ready(function () {

	if ($('#sec_form_nueva_caja_chica_movilidad').length == 0) {
	} else {
		fncSecMepaGetDataRegistroCajaChicaMovilidad();
		fncSecMepaRenderizarDataTableListadoDetalleMovilidad();

		$('#btn_agregar_detalle_movilidad').on('click', (e) => {
			e.preventDefault();
			$('#form_sec_mepa_solicitud_movilidad').submit();
			return false;
		});


		$("#id_sec_mepa_movilidad_txt_centro_costos").dblclick(function (e) {
			if ($('#btn_agregar_detalle_movilidad').length != 0) {
				$(this).attr("readonly", false);
				$(this).focus();
			}

		});

		$('#id_sec_mepa_movilidad_txt_centro_costos').on('keypress', function (e) {
			if ($('#btn_agregar_detalle_movilidad').length != 0) {
				if (e.which == 13) {
					
					var data = {
						accion: "sec_mepa_data_caja_chica_update_solicitud_movilidad_cc_id",
						cc_id: $("#id_sec_mepa_movilidad_txt_centro_costos").val(),
						idCajaChicaMovilidad: $("#idCajaChicaMovilidad").val()
					}
					$.ajax({
						type: "POST",
						url: "sys/get_mepa_movilidad.php",
						data: data,
						async: false,
						cache: false,
						success: function (response) {
							var jsonData = JSON.parse(response);							
							if (jsonData.error == false) {
								alertify.set('notifier', 'position', 'top-right');
								alertify.success('Centro de costos Actualizado');
								$("#id_sec_mepa_movilidad_txt_centro_costos").attr("readonly", true);
							}		
							if (jsonData.error == true) {
								alertify.set('notifier', 'position', 'top-right');
								alertify.error(jsonData.message);
							}					
						}
					});
					
				}
			}

		});

		$('#form_sec_mepa_solicitud_movilidad').validate({
			rules: {
				name_sec_mepa_movilidad_fecha_detalle: {
					required: true,
				},
				name_sec_mepa_movilidad_partida_destino_detalle: {
					required: true,
				},
				name_sec_mepa_movilidad_motivo_detalle: {
					required: true,
				},
				name_sec_mepa_movilidad_subtotal_viaje_detalle: {
					required: true,
				}

			},
			messages: {
				name_sec_mepa_movilidad_fecha_detalle: {
					required: "Por favor, introduce una fecha válida",
				},
				name_sec_mepa_movilidad_partida_destino_detalle: {
					required: "Por favor, un punto de partida y destino es requerido",
				},
				name_sec_mepa_movilidad_motivo_detalle: {
					required: "Por favor, un motivo es requerido",
				},
				name_sec_mepa_movilidad_subtotal_viaje_detalle: {
					required: "Por favor, un monto es requerido",
				}
			},
			submitHandler: function (form) {
				//var formData = fncPromocionMarketingGetDataInsertUpdate();
				var $this = $("#form_sec_mepa_solicitud_movilidad")
					, viewArr = $this.serializeArray()
					, data = {};

				for (var i in viewArr) {
					data[viewArr[i].name] = viewArr[i].value;
				}
				//console.log(view);
				fncMepaGuardarDetalleMovilidad(data);
				return false;
			}
		});
	}
});
function fncMepaGuardarDetalleMovilidad(data) {
	data.accion = 'sec_mepa_get_data_caja_chica_guardar_detalle_movilidad';
	//console.log(data);
	$.ajax({
		type: "POST",
		url: "sys/get_mepa_movilidad.php",
		data: data,
		async: false,
		cache: false,
		success: function (response) {
			var jsonData = JSON.parse(response);
			//console.log(jsonData);
			if (jsonData.error == false) {
				fncSecMepaRenderizarDataTableListadoDetalleMovilidad();
				$('#id_sec_mepa_movilidad_partida_destino_detalle').val('');
				$('#id_sec_mepa_movilidad_motivo_detalle').val('');
				$('#id_sec_mepa_movilidad_subtotal_viaje_detalle').val('');
			}
			if (jsonData.error == true) {
				swal("Error", jsonData.message, "error");
			}
		}
	});
}
function fncSecMepaRenderizarDataTableListadoDetalleMovilidad() {
	
	var table = $('#tabla_form_nueva_movilidad_detalle').DataTable();
	table.clear();
	table.destroy(); 0
	var table = $('#tabla_form_nueva_movilidad_detalle').DataTable({
		'destroy': true,
		"ajax": {
			type: "POST",
			async: false,
			"url": "sys/get_mepa_movilidad.php",
			"data": { cc_movilidad_id: $("#idCajaChicaMovilidad").val(), accion: 'sec_mepa_get_data_caja_chica_listar_detalle_movilidad' }

		},
		"dataSrc": function (json) {
			console.log(json);
			var result = JSON.parse(json);
			return result.data;
		},
		dom: 'Bfrtip',
		lengthMenu: [
			[10, 25, 50, -1],
			['10 registros', '25 registros', '50 registros', 'Mostrar Todos']
		],
		buttons: {
			buttons: [
				{
					extend: "pageLength",
					className: 'btn-dark',
					exportOptions: {
						orthogonal: "exportcsv",
					}
				},
				{
					className: 'btn-success',
					text: '<i class="fa fa-file-pdf-o"></i>',
					action: function (e, dt, node, config) {
						mepa_generate_report_pdf_mobility_expenses($('#idCajaChicaMovilidad').val());
					}
				},
				{
					text: 'Cerrar Gastos de Movilidad',
					className: function (e, dt, node, config) {
						
						var hidden = '';
						if ($('#sec_mepa_movilidad_txt_estado').val() == "2") 
						{
							hidden = 'invisible';
						}
						return 'btn btn-danger ' + hidden;
					},
					action: function (e, dt, node, config) {
						close_mobility_expenses($('#idCajaChicaMovilidad').val());
					}
				}
			],
			dom: {
				button: {
					className: 'btn'
				},
				buttonLiner: {
					tag: null
				}
			}
		},
		"language": {
			//url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json'
			url: "/locales/Datatable/es.json"
		},
		columnDefs: [{
			className: 'text-center',
			targets: [0, 1, 2, 3, 4]
		},
		{ targets: 0, visible: true },
		{ targets: 1, orderable: false },
		{ targets: 2, orderable: false },
		{ targets: 3, orderable: false },
		{ targets: 4, orderable: false }
		],
		"columns": [
			{
				"data": "fecha"
			},
			{
				"data": "partida_destino"
			},
			{
				"data": "motivo_traslado"
			},
			{
				"data": "monto"
			},
			{
				"defaultContent": '',
				render: function (data, type, row) {
					var btn_disabled = '';
					if ($('#btn_agregar_detalle_movilidad').length == 0) {
						btn_disabled = 'style="pointer-events: none;"';
					}
					var btn = '<button class="btn-danger btn-round btnEliminar " ' + btn_disabled + ' ><i class="fa fa-close"></i></button>';
					return btn;
				}
			}
		],
		"order": [[0, 'asc']],
		"drawCallback": function (settings) {
			
			var api = this.api();
			var rows = api.rows({ page: 'all' }).nodes();
			var last = null;

			// Remove the formatting to get integer data for summation
			var intVal = function (i) {
				return typeof i === 'string' ?
					i.replace(/[\$,]/g, '') * 1 :
					typeof i === 'number' ?
						i : 0;
			};
			total = [];
			api.column(0, { page: 'all' }).data().each(function (group, i) {
				group_assoc = group.replace(' ', "_");
				//console.log(group_assoc);
				if (typeof total[group_assoc] != 'undefined') {
					total[group_assoc] = total[group_assoc] + intVal(api.column(3).data()[i]);
				} else {
					total[group_assoc] = intVal(api.column(3).data()[i]);
				}
				if (last !== group) {
					$(rows).eq(i).before(
						'<tr style="background-color: #ddd !important;"><td class="text-center">' + '<h4><span class="badge badge-dark">' + group + '</span></h4></td><td></td><td></td><td class=" text-center ' + group_assoc + '"></td><td></td></tr>'
					);

					last = group;
				}
			});
			var sumTotalMonto = 0;
			for (var key in total) {
				$("." + key).html('<h4><span class="badge badge-primary">' + "S/." + total[key].toFixed(2) + '</span></h4>');
				sumTotalMonto += total[key];
			}

			$("#idTotalMonto").html(sumTotalMonto.toFixed(2));
		}


	});
	$('#tabla_form_nueva_movilidad_detalle tbody').off('click');
	// $('#tabla_form_nueva_movilidad_detalle tbody').on('click', 'tr', function () {
	//     $(this).toggleClass('selected');
	// });

	$('#tabla_form_nueva_movilidad_detalle tbody').on('click', '.btnEliminar', function () {

		var data = table.row($(this).parents('tr')).data();
		var rowData = null;
		if (data == undefined) {
			var selected_row = $(this).parents('tr');
			if (selected_row.hasClass('child')) {
				selected_row = selected_row.prev();
			}
			rowData = $('#tabla_form_nueva_movilidad_detalle').DataTable().row(selected_row).data();
		} else {
			rowData = data;
		}
		//console.log(rowData);
		fncSecMepaEliminarMarDetalleMovilidad(rowData.id);


	});


	//  $('#tabla_form_nueva_movilidad_detalle tbody').on('click', '#idBtnVerArchivosPromocionMarketing', function() {

	// 	var data = table.row($(this).parents('tr')).data();
	// 	var idRow = table.row($(this).parents('tr'));
	// 	var rowData = null;
	// 	if (data == undefined) {
	// 		var selected_row = $(this).parents('tr');
	// 		if (selected_row.hasClass('child')) {
	// 			selected_row = selected_row.prev();
	// 		}
	// 		rowData = $('#tabla_form_nueva_movilidad_detalle').DataTable().row(selected_row).data();
	// 	} else {
	// 		rowData = data;
	// 	}
	// 	$('#idTextTituloPromocionMarketing').text(rowData.nombre_promocion);
	// 	fncListarArchivosMarketingPromocion(rowData.id,idRow.index());

	// });

}
function fncSecMepaEliminarMarDetalleMovilidad(idDetalle) {
	var cc_movilidad_id = $('#idCajaChicaMovilidad').val();
	var idMovilidadDetalle = idDetalle;
	var data = {
		'cc_movilidad_id': cc_movilidad_id,
		'idMovilidadDetalle': idMovilidadDetalle,
		'accion': 'sec_mepa_delete_caja_chica_movilidad_detalle'
	}
	$.ajax({
		type: "POST",
		url: "sys/get_mepa_movilidad.php",
		data: data,
		success: function (response) {
			var jsonData = JSON.parse(response);
			if (jsonData.error == false) {
				fncSecMepaRenderizarDataTableListadoDetalleMovilidad();
				swal("Eliminado", jsonData.message, "success");
			}
			if (jsonData.error == true) {
				swal("Error", jsonData.message, "error");
			}
		}
	});

}

function fncSecMepaGetDataRegistroCajaChicaMovilidad() {
	
	var cc_movilidad_id = $('#idCajaChicaMovilidad').val();
	var data = {
		'cc_movilidad_id': cc_movilidad_id,
		'accion': 'sec_mepa_get_data_caja_chica_movilidad'
	}
	$.ajax({
		type: "POST",
		url: "sys/get_mepa_movilidad.php",
		data: data,
		async: false,
		cache : false,
		success: function (response) {
			
			var jsonData = JSON.parse(response);
			if (jsonData.error == false) {
				$('#id_sec_mepa_movilidad_fecha_del').val(jsonData.data.fecha_del);
				$('#id_sec_mepa_movilidad_fecha_al').val(jsonData.data.fecha_al);
				$('#id_sec_mepa_movilidad_nombre').val(jsonData.data.nombre);
				$('#id_sec_mepa_movilidad_dni').val(jsonData.data.dni);
				$('#sec_mepa_movilidad_txt_correlativo').val(jsonData.data.num_correlativo);
				$('#id_sec_mepa_movilidad_txt_centro_costos').val(jsonData.data.cc_id);
				$('#id_sec_mepa_movilidad_fecha_detalle').attr("max", jsonData.data.fecha_al);
				$('#id_sec_mepa_movilidad_fecha_detalle').attr("min", jsonData.data.fecha_del);
				$('#id_sec_mepa_movilidad_id_usuario').val(jsonData.data.user_created_id);
				$('#sec_mepa_movilidad_txt_estado').val(jsonData.data.status);
				var fechaInicio = new Date(jsonData.data.fecha_del);
				var fechaFin = new Date(jsonData.data.fecha_al);
				var fechaValidar = new Date();
				var fecha_hoy = fncSecMepaValidarFechaEnRango(fechaInicio, fechaFin, fechaValidar);
				if (parseInt(jsonData.data.status) == 2) 
				{
					$("#btn_agregar_detalle_movilidad").prop('disabled', true);
					$("#sec_mepa_solicitud_movilidad_detalle_div_editar_fechas").hide();
				}
				if (fecha_hoy) {
					//console.log(fechaValidar.toISOString().split('T')[0]);
					$('#id_sec_mepa_movilidad_fecha_detalle').val(fechaValidar.toISOString().split('T')[0]);
				}
				
				if (typeof jsonData.data.user_request_id !== 'undefined') {
					if (jsonData.data.user_request_id !== jsonData.data.user_created_id) {
						$("#sec_mepa_form_sec_mepa_solicitud_movilidad").attr("hidden", true);
						$("#btn_agregar_detalle_movilidad").remove();
					}
				}
			}
		}
	});
}

function fncSecMepaValidarFechaEnRango(fechaInicio, fechaFin, fechaValidar) {

	const fechaInicioMs = fechaInicio.getTime();
	const fechaFinMs = fechaFin.getTime();
	const fechaValidarMs = fechaValidar.getTime();

	if (fechaValidarMs >= fechaInicioMs && fechaValidarMs <= fechaFinMs) {
		return true;
	} else {
		return false;
	}

}

const close_mobility_expenses = (cc_movilidad_id) => {
	//alert(idCajaChicaMobilidad);
	
	swal({
		title: "¿Estás seguro?",
		text: "¡No podra ingresar nuevos gastos de movilidad!",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Si, cerrar",
		cancelButtonText: "No, cancelar",
		closeOnConfirm: false,
		closeOnCancel: false,

	},
		function (isConfirm) {
			var id_usuario = $('#id_sec_mepa_movilidad_id_usuario').val();
			var data = {
				'accion': 'sec_mepa_get_data_caja_chica_cerrar_detalle_movilidad',
				'cc_movilidad_id': cc_movilidad_id,
				'id_usuario': id_usuario
			}
			if (isConfirm) {
				$.ajax({
					type: "POST",
					data: data,
					url: 'sys/get_mepa_movilidad.php',
					cache: false,
					success: function (response) {
						
						var jsonData = JSON.parse(response);
						if (jsonData.error == false) {
							swal("Cerrado", jsonData.message, "success");
							$("#btn_agregar_detalle_movilidad").prop('disabled', true);
							$(".btn_desabilitado").prop('disabled', true);
							fncSecMepaGetDataRegistroCajaChicaMovilidad();
							location.reload();
						} else {
							swal("Error", jsonData.message, "error");
						}
					}
				});
			} else {
				swal("Cancelado", "Los datos no se enviaron", "error");
			}
		});
}

const mepa_generate_report_pdf_mobility_expenses = (cc_movilidad_id) => {
	
	var id_usuario = $('#id_sec_mepa_movilidad_id_usuario').val();
	var data = {
		'accion': 'sec_mepa_generar_reporte_pdf_movilidad',
		'cc_movilidad_id': cc_movilidad_id,
		'id_usuario': id_usuario
	}
	window.open('/sys/get_mepa_movilidad_reporte.php?'+"accion=sec_mepa_generar_reporte_pdf_movilidad&"+"cc_movilidad_id="+data.cc_movilidad_id);	
}

function sec_mepa_solicitud_movilidad_detalle_editar_fechas()
{
	
	$("#id_sec_mepa_movilidad_fecha_del").prop('disabled', false);
	$("#id_sec_mepa_movilidad_fecha_al").prop('disabled', false);

	$("#sec_mepa_solicitud_movilidad_detalle_btn_editar_fechas").hide();
	$("#sec_mepa_solicitud_movilidad_detalle_btn_guardar_editar_fechas").show();
}

function sec_mepa_solicitud_movilidad_detalle_guardar_editar_fechas()
{
	let param_id_movilidad = $("#idCajaChicaMovilidad").val();
	let param_fecha_del = $("#id_sec_mepa_movilidad_fecha_del").val();
	let param_fecha_al = $("#id_sec_mepa_movilidad_fecha_al").val();
	
	swal(
    {
        title: '¿Está seguro de editar la fecha?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    function(isConfirm)
    {
        if (isConfirm)
        {
            var data = {
		        "accion": "sec_mepa_solicitud_movilidad_detalle_guardar_editar_fechas",
		        "param_id_movilidad": param_id_movilidad,
		        "param_fecha_del": param_fecha_del,
		        "param_fecha_al": param_fecha_al
		    }

            $.ajax({
	            url: "sys/get_mepa_movilidad.php",
	            type: 'POST',
	            data: data,
	            beforeSend: function() {
	                loading("true");
	            },
	            complete: function() {
	                loading();
	            },
	            success: function(data){
	                
	                var respuesta = JSON.parse(data);
	                auditoria_send({ "respuesta": "sec_mepa_solicitud_movilidad_detalle_guardar_editar_fechas", "data": respuesta });
	                if(parseInt(respuesta.http_code) == 200) 
	                {
	                    swal({
	                        title: "Registro exitoso",
	                        text: "Se actualizo la fecha correctamente",
	                        html:true,
	                        type: "success",
	                        timer: 6000,
	                        closeOnConfirm: false,
	                        showCancelButton: false
	                    },
	                    function (isConfirm) {
	                        location.reload();
	                    });

	                    setTimeout(function() {
	                        location.reload();
	                    }, 5000);

	                    return true;
	                }
	                else if(parseInt(respuesta.http_code) == 400) 
	                {
	                    swal({
	                        title: respuesta.status,
	                        text: respuesta.error,
	                        html:true,
	                        type: "warning",
	                        closeOnConfirm: false,
	                        showCancelButton: false
	                    });
	                    return false;
	                }
	                else {
	                    swal({
	                        title: respuesta.status,
	                        text: respuesta.error,
	                        html:true,
	                        type: "warning",
	                        closeOnConfirm: false,
	                        showCancelButton: false
	                    });
	                    return false;
	                }
	            }
            });
        }
    });
}

const sec_mepa_solicitud_movilidad_detalle_activar = (param_id_movilidad) => {

	swal(
    {
        title: '¿Está seguro de activar la movilidad?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    function(isConfirm)
    {
        if (isConfirm)
        {
            var data = {
		        "accion": "sec_mepa_solicitud_movilidad_detalle_activar",
		        "param_id_movilidad": param_id_movilidad
		    }

            $.ajax({
	            url: "sys/get_mepa_movilidad.php",
	            type: 'POST',
	            data: data,
	            beforeSend: function() {
	                loading("true");
	            },
	            complete: function() {
	                loading();
	            },
	            success: function(data){
	                
	                var respuesta = JSON.parse(data);
	                auditoria_send({ "respuesta": "sec_mepa_solicitud_movilidad_detalle_activar", "data": respuesta });
	                if(parseInt(respuesta.http_code) == 200) 
	                {
	                    swal({
	                        title: respuesta.titulo,
	                        text: respuesta.descripcion,
	                        html:true,
	                        type: "success",
	                        timer: 6000,
	                        closeOnConfirm: false,
	                        showCancelButton: false
	                    },
	                    function (isConfirm) {
	                        location.reload();
	                    });

	                    setTimeout(function() {
	                        location.reload();
	                    }, 5000);

	                    return true;
	                }
	                else if(parseInt(respuesta.http_code) == 400) 
	                {
	                    swal({
	                        title: respuesta.titulo,
	                        text: respuesta.descripcion,
	                        html:true,
	                        type: "warning",
	                        closeOnConfirm: false,
	                        showCancelButton: false
	                    });
	                    return false;
	                }
	                else {
	                    swal({
	                        title: respuesta.titulo,
	                        text: respuesta.descripcion,
	                        html:true,
	                        type: "warning",
	                        closeOnConfirm: false,
	                        showCancelButton: false
	                    });
	                    return false;
	                }
	            }
            });
        }
    });
}