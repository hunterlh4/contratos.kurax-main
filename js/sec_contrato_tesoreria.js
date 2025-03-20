function sec_contrato_tesoreria(){
	$('.sec_contrato_tesoreria_datepicker')
		.datepicker({
			dateFormat:'dd/mm/yy',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "dd/mm/yy"));
			// localStorage.setItem($(this).atrr("id"),)
		});

	$("#comprobante_de_pago").change(function () {
		var fileExtension = ['jpeg', 'jpg', 'png', 'pdf'];
		if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
			alertify.error("Solo se permiten formatos : " + fileExtension.join(', '),5);
		}
	});

	sec_contrato_tesoreria_listar_programaciones();

	$(".select2").select2({ width: "100%" });

	$("#tipo_programacion_id").change(function () {
		setTimeout(function() {
			$('#tipo_concepto_id').select2('open');
		}, 200);
		
		return false;
	});

	$("#tipo_anticipo_id").change(function () {
		$("#tipo_programacion_id option:selected").each(function () {
			var tipo_concepto_id = $('#tipo_concepto_id').val().trim();
			if (tipo_concepto_id == 0) {
				$('#tipo_concepto_id').show();
				$('#tipo_concepto_id').select2('open');
			}
		});
	});

	$("#tipo_concepto_id").change(function () {
		$("#tipo_concepto_id option:selected").each(function () {
			tipo_concepto_id = $(this).val();
			if (tipo_concepto_id == 7) {
				$('#moneda').val('DOLAR');
			} else if (tipo_concepto_id == 8) {
				$('#moneda').val('SOL');
			} else {
				$('#moneda').val('');
			}
		});

		if (tipo_concepto_id != 0) {

			tipo_programacion_id = $('#tipo_programacion_id').val();

			var data = {
				"accion": "obtener_empresas",
				"tipo_programacion_id": tipo_programacion_id,
				"tipo_concepto_id": tipo_concepto_id
			}

			var array_empresas = [];
			auditoria_send({ "proceso": "obtener_empresas", "data": data });
			$.ajax({
				url: "/sys/get_contrato_nueva_programacion.php",
				type: 'POST',
				data: data,
				beforeSend: function() {
					// loading("true");
				},
				complete: function() {
					// loading();
				},
				success: function(resp) {
					var respuesta = JSON.parse(resp);
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 400) {
					}
					
					if (parseInt(respuesta.http_code) == 200) {
						array_empresas.push(respuesta.result);
						console.log('Cantidad de Registro: ' + array_empresas.length);
						var html = '';

						if (array_empresas[0].length > 1) {
							html = '<option value="_all_">TODOS</option>';
						}

						for (var i = 0; i < array_empresas[0].length; i++) {
							html += '<option value=' + array_empresas[0][i].id  + '>' + array_empresas[0][i].nombre + '</option>';
						}

						console.log(html);

						$("#empresa_id").html(html).trigger("change");

						if (array_empresas[0].length > 1) {
							setTimeout(function() {
								$('#empresa_id').select2('open');
							}, 200);
						} else {
							$("#empresa_id").change();
						}
						
						return false;
					}
				},
				error: function() {}
			});
		}
	});

	$("#empresa_id").change(function () {
		$("#empresa_id option:selected").each(function () {
			concepto_id = $('#tipo_concepto_id').val();
			tipo_programacion_id = $('#tipo_programacion_id').val();
			empresa_id = $(this).val();
			var data = {
				"accion": "obtener_numeros_de_cuentas",
				"concepto_id": concepto_id,
				"tipo_programacion_id": tipo_programacion_id,
				"empresa_id": empresa_id
			}
			var array_concepto = [];
			auditoria_send({ "proceso": "obtener_numeros_de_cuentas", "data": data });
			$.ajax({
				url: "/sys/get_contrato_nueva_programacion.php",
				type: 'POST',
				data: data,
				beforeSend: function() {
					// loading("true");
				},
				complete: function() {
					// loading();
				},
				success: function(resp) {
					var respuesta = JSON.parse(resp);
					console.log(respuesta);
					if (parseInt(respuesta.http_code) == 400) {
					}
					
					if (parseInt(respuesta.http_code) == 200) {
						array_concepto.push(respuesta.result);
						console.log('Cantidad de Registro: ' + array_concepto.length);

						html = '<option value="_all_">TODOS</option>';

						for (var i = 0; i < array_concepto[0].length; i++) {
							html += '<option value=' + array_concepto[0][i].id  + '>' + array_concepto[0][i].nombre + '</option>';
						}

						$("#banco_id").html(html).trigger("change");

						if (array_empresas[0].length > 1) {
							setTimeout(function() {
								$('#banco_id').select2('open');
							}, 200);
						} else {
							$("#banco_id").change();
						}
						
						return false;
					}
				},
				error: function() {}
			});
		});
	});
}

function sec_contrato_tesoreria_listar_programaciones(){
	sec_contrato_tesoreria_limpiar_tabla_programaciones();
	var fecha_inicio = $("#fecha_inicio").val();
	var fecha_fin = $("#fecha_fin").val();
	var tipo_programacion_id = $("#tipo_programacion_id").val();
	var id_concepto = $("#tipo_concepto_id").val();
	var tipo_pago_id = $("#tipo_pago_id").val();
	var empresa_id = $("#empresa_id").val();
	var banco_id = $("#banco_id").val();
	var situacion_id = $("#situacion_id").val();

	array_tabla_programaciones = [];
	var data = {
		"accion": "obtener_lista_programaciones",
		"fecha_inicio":fecha_inicio,
		"fecha_fin":fecha_fin,
		"tipo_programacion_id":tipo_programacion_id,
		"id_concepto":id_concepto,
		"tipo_pago_id":tipo_pago_id,
		"empresa_id":empresa_id,
		"banco_id":banco_id,
		"situacion_id":situacion_id
	}
	//console.log(data);
	auditoria_send({ "proceso": "obtener_lista_programaciones", "data": data });
	$.ajax({
		url: "sys/set_contrato_tesoreria.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			//debugger;
			var respuesta = JSON.parse(resp);
			
			if (parseInt(respuesta.http_code) == 400) {
				$('#tabla_programaciones').append(
					'<tr>' +
					'<td class="text-center" colspan="10">No hay programaciones</td>' +
					'</tr>'
				);
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$.each(respuesta.result, function(index, fila) {
					array_tabla_programaciones.push(fila);
					var onClickVerDetalle = "$(location).attr('href','?sec_id=contrato&sub_sec_id=detalle_programacion&id=" + fila.programacion_id + "');";
					var onClickEditar = "$(location).attr('href','?sec_id=contrato&sub_sec_id=nueva_programacion&id=" + fila.programacion_id + "');"
					var onClickProcesar = "$(location).attr('href','?sec_id=contrato&sub_sec_id=procesar_programacion&id=" + fila.programacion_id + "');"
					var opciones = '';

					opciones += '<a type="button"class="btn btn-info btn-xs" title="Ver programación" onclick="sec_contrato_modal_detalle_programacion(' + fila.programacion_id + ')"><i class="fa fa-eye"></i></a>';
					
					if(fila.etapa_id != 4 && (fila.etapa_id != 3 && fila.etapa_id != 5)){
						opciones += '<button type="button"class="btn btn-warning btn-xs" style="margin-left: 2px;" onclick="' + onClickEditar + '" title="Editar programación" ><i class="fa fa-edit"></i></button>';
					}
					
					if(fila.etapa_id == 3 || fila.etapa_id == 5){
						opciones += '<a type="button"class="btn btn-xs" style="margin-left: 2px; background-color: #563D7C; color: #fff;" title="Subir comprobante de pago" onclick="sec_contrato_tesoreria_modal_comprobante_pago(' + fila.programacion_id + ');"><i class="fa fa-cloud-upload"></i></a>';
						opciones += '<a type="button"class="btn btn-success btn-xs" style="margin-left: 2px;" title="Exportar excel" href="sec_contrato_tesoreria_export_excel.php?id=' + fila.programacion_id + '" target="_blank"><i class="fa fa-file-excel-o"></i></a>';
						opciones += '<a type="button"class="btn btn-primary btn-xs" style="margin-left: 2px;" title="Exportar .txt" href="sec_contrato_tesoreria_export_txt.php?id=' + fila.programacion_id + '" target="_blank"><i class="fa fa-file-text-o"></i></a>';
						opciones += '<a type="button"class="btn btn-success btn-xs" style="margin-left: 2px;" title="Exportar asiento contable" onclick="sec_contrato_tesoreria_modal_exportar_asiento(' + fila.programacion_id + ')" target="_blank"><i class="fa fa-file-excel-o"></i></a>';
					}

					if (fila.etapa_id == 1 || fila.etapa_id == 2){
						opciones += '<button type="button"class="btn btn-primary btn-xs" style="margin-left: 2px;" onclick="sec_contrato_modal_procesar_programacion(' + fila.programacion_id + ');" title="Procesar programación" ><i class="fa fa-cogs"></i></button>';
						opciones += '<button type="button"class="btn btn-danger btn-xs" style="margin-left: 2px;" title="Eliminar programación" onclick="sec_contrato_tesoreria_modal_eliminar_programacion(' + fila.programacion_id + ');" ><i class="fa fa-trash-o"></i></button>';
					}

					if(fila.etapa_id != 4 && fila.etapa_id != 3){
						
					}
					const [anio, mes, dia] = fila.fecha_programacion.split('-');

					// Paso 2: Formatear la fecha en el nuevo formato "d/m/a"
					const fechaFormateada = `${dia}/${mes}/${anio}`;
				  
					$('#tabla_programaciones').append(
						'<tr>' +
						'<td>' + fila.programacion_numero + '</td>' +
						'<td>' + fila.arrendatario + '</td>' +
						'<td>' + fila.tipo_programacion + '</td>' +
						'<td>' + fechaFormateada + '</td>' +
						'<td>' + fila.banco + '</td>' +
						'<td>' + fila.moneda + '</td>' +
						'<td style="text-align:right">' + parseFloat(fila.importe, 10).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,").toString() + '</td>' +
						'<td>' + fila.etapa + '</td>' +
						'<td>' + opciones + '</td>' +
						'</tr>'
					);

				});
				return false;
			}      
		},
		error: function() {}
	});
}

function sec_contrato_tesoreria_limpiar_tabla_programaciones() {
	$('#tabla_programaciones').html(
		'<thead>' +
		'   <tr>' +
		'       <th>N.°</th>' +
		'       <th>Arrendatario</th>' +
		'       <th>Tipo</th>' +
		'       <th>Fecha</th>' +
		'       <th>Banco</th>' +
		'       <th>Moneda</th>' +
		'       <th>Importe</th>' +
		'       <th>Situación</th>' +
		'       <th>Opciones</th>' +
		'   </tr>' +
		'</thead>' +
		'<tbody>'
	);
	//console.log("limpiar tabla comprobantes");
}

function sec_contrato_modal_detalle_programacion(programacion_id){
	$('#sec_contrato_modal_detalle_programacion').modal({backdrop: 'static', keyboard: false});
	$('#id_detalle_programacion_seleccionada').val(programacion_id)
	sec_contrato_tesoreria_detalle_programacion();
	console.log("sec_contrato_modal_detalle_programacion")
}

function sec_contrato_tesoreria_modal_comprobante_pago(programacion_id){
	$('#modal_comprobante_de_pago').modal({backdrop: 'static', keyboard: false});
	sec_contrato_tesoreria_listar_tabla_comprobantes(programacion_id);
}

function sec_contrato_tesoreria_modal_eliminar_programacion(provision_id){
	$('#modal_eliminar_programacion').modal({backdrop: 'static', keyboard: false});
	var id_programacion = provision_id;
	$('#programacion_id_eliminar').val(id_programacion);
}

function sec_contrato_tesoreria_eliminar_programacion(){
	var programacion_id = $('#programacion_id_eliminar').val();
	var dataForm = new FormData($("#form_eliminar_programacion")[0]);
	dataForm.append("accion","eliminar_programacion");
	dataForm.append("programacion_id", programacion_id);

	auditoria_send({ "proceso": "eliminar_programacion", "data": dataForm });

	$.ajax({
		url: "sys/set_contrato_tesoreria.php",
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function( xhr ) {
			loading(true);
		},
		success: function(data){
			var respuesta = JSON.parse(data);
			console.log(respuesta.query);
			auditoria_send({ "respuesta": "eliminar_programacion", "data": respuesta });
			if(parseInt(respuesta.http_code) == 200) {
				swal({
					title: "Eliminación exitosa",
					text: "La programación se eliminó exitosamente",
					html:true,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false
				});
				setTimeout(function() {
					window.location.href = "?sec_id=contrato&sub_sec_id=tesoreria";
					return false;
				}, 3000);
			} else {
				swal({
					title: "Error al eliminar la programación",
					text: respuesta.error,
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
			}
		},
		complete: function(){
			loading(false);
		}
	});
}

function sec_contrato_tesoreria_listar_tabla_comprobantes(provision_id){
	//console.log("listar tabla comprobantes");
	sec_contrato_tesoreria_limpiar_tabla_comprobantes();
	
	array_tabla_comprobantes = [];
	var id_programacion = provision_id;
	$('#programacion_id_comprobante').val(id_programacion);
	var data = {
		"accion": "obtener_comprobantes_x_programacion",
		"id_programacion": id_programacion
	}
	auditoria_send({ "proceso": "obtener_comprobantes_x_programacion", "data": data });
	//debugger;
	$.ajax({
		url: "sys/set_contrato_tesoreria.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			//debugger;
			var respuesta = JSON.parse(resp);
			
			if (parseInt(respuesta.http_code) == 400) {
				//swal('Aviso', respuesta.status, 'warning');
				$('#tabla_comprobantes').append(
					'<tr>' +
					'<td class="text-center" colspan="10">No hay comprobantes</td>' +
					'</tr>'
				);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				
				$.each(respuesta.result, function(index, item) {
					array_tabla_comprobantes.push(item);
					
					var subido_por = item.nombre_usuario;
					var fecha_creacion = item.fecha_creacion;

					//var ver_comprobante = "<button type='button' class='btn btn-danger btn-xs' title='Ver comprobante de pago'<i class='fa fa-file-pdf-o'></i>Visualizar comprobante</button>";
					var href = 'href';
					var sec = 'sec_id=contrato&sub_sec_id=detalle_programacion';
					var nombre_archivo = "'" + item.nombre_archivo + "'";
					var extension_archivo = "'" + item.extension + "'";
					ver_comprobante = '<button type="button" class="btn btn-info btn-xs"title="Ver comprobante de pago" onclick="sec_contrato_tesoreria_verComprobanteVista(' + nombre_archivo + ', ' + extension_archivo + ')"><i class="fa fa-eye" style="margin-right:5px;"></i>Visualizar comprobante</button>'

					var listadoNuevoItem = [];

					$('#tabla_comprobantes').append(
						'<tr>' +
						'<td class="text-center" style="font-weight: bold;">' + item.nombre_usuario + '</td>' +
						'<td class="text-center">' + item.fecha_creacion + '</td>' +
						'<td class="text-center">' + ver_comprobante + '</td>' +
						'</tr>'
					);
					
					$('#midocu').val(item.nombre_archivo);
				});
				return false;
			}      
		},
		error: function() {}
	});
} 

function sec_contrato_tesoreria_subir_comprobante(){
	var x = sec_contrato_tesoreria_validar_formulario_subir_comprobante();

	if (!x) {
		return false;
	}

	var fecha_pago = new Date();
	fecha_pago = $('#fecha_de_pago').val();
	var programacion_id = $('#programacion_id_comprobante').val();
	var dataForm = new FormData($("#form_comprobante_pago")[0]);
	//console.log("Programacion id seleccionada: " . programacion_id);
	dataForm.append("accion","guardar_comprobante_pago");
	dataForm.append("fecha_pago", fecha_pago);
	dataForm.append("programacion_id", programacion_id);

	auditoria_send({ "proceso": "guardar_comprobante_pago", "data": dataForm });
		//debugger;
	$.ajax({
		url: "sys/set_contrato_tesoreria.php",
		type: 'POST',
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function( xhr ) {
			loading(true);
		},
		success: function(data){
			var respuesta = JSON.parse(data);
			console.log(respuesta.comando);
			auditoria_send({ "respuesta": "guardar_comprobante_pago", "data": respuesta });
			if(parseInt(respuesta.http_code) == 200) {
				swal({
					title: "Registro exitoso",
					text: "El comprobante se registró exitosamente",
					html:true,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false
				});
				setTimeout(function() {
					window.location.href = "?sec_id=contrato&sub_sec_id=tesoreria";
				}, 3000);
				sec_contrato_tesoreria_listar_tabla_comprobantes(programacion_id);
			} else {
				swal({
					title: "Error al guardar comprobante de pago",
					text: respuesta.error,
					html:true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false
				});
			}
		},
		complete: function(){
			loading(false);
		}
	});
}

function sec_contrato_tesoreria_validar_formulario_subir_comprobante(){
	var fecha_de_pago = $('#fecha_de_pago').val();
	if (fecha_de_pago == '') {
		alertify.error('Ingrese la fecha de pago',5);
		$('#fecha_de_pago').focus();
		return false;
	} else if ($("#comprobante_de_pago")[0].files.length === 0) {
		alertify.error('Seleccione el comprobante de pago',5);
		$('#comprobante_de_pago').focus();
		return false;
	}
	return true;
}

function sec_contrato_tesoreria_limpiar_tabla_comprobantes() {
	$('#tabla_comprobantes').html(
		'<thead>' +
		'   <tr>' +
		'       <th>Subido por</th>' +
		'       <th>Subido el</th>' +
		'       <th>Comprobante de pago</th>' +
		'   </tr>' +
		'</thead>' +
		'<tbody>'
	);
	//console.log("limpiar tabla comprobantes");
}

function sec_contrato_tesoreria_verComprobanteVista(nombre_archivo, extension){
	$('#div_modal_archivo').modal({backdrop: 'static', keyboard: false});
	//debugger;
	var midocu = nombre_archivo;
	var micarpeta = 'comprobantes_de_pago';
	var tipodocumento = extension;
	var html = '';
	var titulo = '';
		//debugger;
	if (tipodocumento == 'html') 
	{
		$('#div_contenido_archivo').hide();
	} 
	else if (tipodocumento == 'pdf') 
	{

		$('#div_heading_value_archivo').html(titulo);
		$('#div_contenido_archivo').show();
		$('#div_Ver_Pdf').show();
		$('#divVisorPdfPrincipal').show();
		$('#divVerImagenFullPantallaModal').hide();
		$('#divVisorImagen').hide();

		html = '<iframe src="files_bucket/contratos/' + micarpeta + '/' + midocu + '" class="col-xs-12 col-md-12 col-sm-12" height="580"></iframe>';
		var htmlModal = '<iframe src="files_bucket/contratos/' + micarpeta + '/' + midocu + '" class="col-xs-12 col-md-12 col-sm-12" height="525"></iframe>';

		$('#divVisorPdfPrincipal').html(html);
		$('#divVisorPdfModal').html(htmlModal);

	} 
	else if (tipodocumento == 'jpg' || tipodocumento == 'png' || tipodocumento == 'jpeg') 
	{

		$('#div_heading_value_archivo').html(titulo);
		$('#div_contenido_archivo').show();
		$('#div_Ver_Pdf').hide();
		$('#divVisorPdfPrincipal').hide();
		$('#divVerImagenFullPantallaModal').show();
		$('#divVisorImagen').show();

		html = '<img src="files_bucket/contratos/' + micarpeta + '/' + midocu + '" class="img-responsive" style="border: 1px solid;">';
		$('#divVisorImagen').html(html);

		//document.getElementById('verImagenFullComprobante').removeEventListener('click', sec_contrato_tesoreria_verFullImagenComprobante());
		//document.getElementById('verImagenFullComprobante').addEventListener('click', sec_contrato_tesoreria_verFullImagenComprobante());
		
		//$("#verImagenFullComprobante").click(function(){ sec_contrato_tesoreria_verFullImagenComprobante(midocu); });
	}
}

function sec_contrato_tesoreria_verFullImagenComprobante() {
	//debugger;
	var archivo = $('#midocu').val();
	console.log(archivo);
	if(archivo == undefined){
		return;
	}
	var image = new Image();
	image.src = 'files_bucket/contratos/comprobantes_de_pago/' + archivo;
	var viewer = new Viewer(image, 
	{
		hidden: function () {
			viewer.destroy();
		},
	});
	viewer.show()
	//console.log(viewer);
}


function sec_contrato_modal_procesar_programacion (programacion_id) {
	$('#sec_contrato_modal_procesar_programacion').modal({backdrop: 'static', keyboard: false});
	$('#sec_proc_id_programacion_seleccionada').val(programacion_id)
	sec_contrato_procesar_programacion();
}

function sec_contrato_procesar_programacion_modal_confirmar_proceso(){
	$('#sec_proc_modal_confirmar_proceso').modal({backdrop: 'static', keyboard: false});
}


function sec_contrato_tesoreria_modal_exportar_asiento(provision_id){
	$('#modal_exportar_asiento').modal({backdrop: 'static', keyboard: false});
	$('#programacion_id_asiento_contable').val(provision_id);
	$('#num_docu_asiento_contable').focus();
}

function sec_contrato_tesoreria_exportar_asiento_contable(){
	var programacion_id_asiento_contable = $('#programacion_id_asiento_contable').val();
	var num_docu_asiento_contable = $('#num_docu_asiento_contable').val();

	$('#modal_exportar_asiento').modal('hide');
	$('#num_docu_asiento_contable').val('');

	window.open('contrato_export_asiento_contable_concar.php?id=' + programacion_id_asiento_contable + '&num_docu=' + num_docu_asiento_contable, '_blank');
}


function sec_contrato_tesoreria_ver_detalle_de_pagos(condicion_economica_id, provision_id, tipo_id, anio){

	var data = {
		"accion": "listar_detalle_de_pagos",
		"condicion_economica_id":condicion_economica_id,
		"provision_id":provision_id,
		"tipo_id":tipo_id,
		"anio":anio
	}

	auditoria_send({ "proceso": "listar_detalle_de_pagos", "data": data });

	$.ajax({
		url: "sys/get_contrato_tesoreria.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			var respuesta = JSON.parse(resp);

			auditoria_send({ proceso: "listar_detalle_de_pagos", data: respuesta });
			
			if (parseInt(respuesta.http_code) == 400) {
				$('#div_detalle_de_pagos').html(respuesta.result);
				$("#modal_detalle_de_pagos").modal({ backdrop: "static", keyboard: false });
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$('#div_detalle_de_pagos').html(respuesta.result);
				$("#modal_detalle_de_pagos").modal({ backdrop: "static", keyboard: false });
				$(".select2").select2({ width: "100%" });
				return false;
			}      
		},
		error: function() {}
	});
}


function sec_contrato_tesoreria_consultar_detalle_de_pagos() {
	debugger;
	var condicion_economica_id = $('#condicion_economica_id').val();
	var provision_id = $('#provision_id').val();
	var tipo_id = $('#tipo_id').val();
	var anio = $('#anio_del_contrato_id').val();

	var data = {
		"accion": "listar_detalle_de_pagos_x_anio",
		"condicion_economica_id":condicion_economica_id,
		"provision_id":provision_id,
		"tipo_id":tipo_id,
		"anio":anio
	}

	auditoria_send({ "proceso": "listar_detalle_de_pagos_x_anio", "data": data });

	$.ajax({
		url: "sys/get_contrato_tesoreria.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			var respuesta = JSON.parse(resp);

			auditoria_send({ proceso: "listar_detalle_de_pagos_x_anio", data: respuesta });
			
			if (parseInt(respuesta.http_code) == 400) {
				$('#div_detalle_de_pagos_x_anio').html(respuesta.result);
				return false;
			}

			if (parseInt(respuesta.http_code) == 200) {
				$('#div_detalle_de_pagos_x_anio').html(respuesta.result);
				$(".select2").select2({ width: "100%" });
				return false;
			}      
		},
		error: function() {}
	});
}