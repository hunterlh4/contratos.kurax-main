function sec_mepa_detalle_tesoreria_programacion()
{
	// INICIO FORMATO Y BUSQUEDA DE FECHA
	$('.sec_mepa_detalle_tesoreria_datepicker')
		.datepicker({
			dateFormat:'yy/mm/dd',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
			// localStorage.setItem($(this).atrr("id"),)
		});
	// FIN FORMATO Y BUSQUEDA DE FECHA

	 $('.mepa_programacion_num_comprobante').mask('0000');
	 $('.mepa_programacion_num_documento').mask('000000000000');

	set_mepa_tesoreria_comprobante_pago($('#tesoreria_comprobante_pago'));
	set_mepa_tesoreria_comprobante_pago_edit($('#tesoreria_comprobante_pago_edit'));
}

function set_mepa_tesoreria_comprobante_pago(object){
	
	$(document).on('click', '#btn_buscar_comprobante', function(event) {
		event.preventDefault();
		object.click();
	});

	object.on('change', function(event) {
		let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
		
		if($(this)[0].files.length <= 1)
		{
			const name = $(this).val().split(/\\|\//).pop();
			truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
		}

		$("#txt_comprobante_archivo").html(truncated);
	});
}

function set_mepa_tesoreria_comprobante_pago_edit(object){

	$(document).on('click', '#btn_buscar_comprobante_edit', function(event) {
		event.preventDefault();
		object.click();
	});

	object.on('change', function(event) {
		let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
		if($(this)[0].files.length <= 1)
		{
			const name = $(this).val().split(/\\|\//).pop();
			truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
		}
		
		$("#txt_comprobante_archivo_edit").html(truncated);
	});
}


$(document).on('submit', "#form_tesoreria_guardar_comprobante_pago", function(e) 
{
	e.preventDefault();
	
	var tesoreria_comprobante_pago = document.getElementById("tesoreria_comprobante_pago");

	var tesoreria_fecha_comprobante_pago = $("#tesoreria_fecha_comprobante_pago").val();
	var mepa_programacion_id = $("#mepa_programacion_id").val();
	
	
	if(tesoreria_comprobante_pago.files.length > 0)
    {
        for(var i = 0; i < tesoreria_comprobante_pago.files.length; i ++)
        {
            if(tesoreria_comprobante_pago.files[i].size > 1000000)
            {
                alertify.error(`EL Archivo ${tesoreria_comprobante_pago.files[i].name} debe pesar menos de 1MB`,5);
                return false;
            }
        }
    }
    else
    {
        alertify.error('Seleccione el comprobante',5);
        $("#tesoreria_comprobante_pago").focus();
        return false;
    }

	swal(
	{
		title: '¿Está seguro de registrar?',
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

			var dataForm = new FormData($("#form_tesoreria_guardar_comprobante_pago")[0]);
			dataForm.append("accion","mepa_tesoreria_guardar_comprobante_pago");
			dataForm.append("tesoreria_fecha_comprobante_pago", tesoreria_fecha_comprobante_pago);
			dataForm.append("mepa_programacion_id", mepa_programacion_id);

			$.ajax({
				url: "sys/set_mepa_detalle_tesoreria_programacion.php",
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

					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: "Registro exitoso",
							text: "La solicitud fue registrada exitosamente",
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        window.location.href = "?sec_id=mepa&sub_sec_id=detalle_tesoreria_programacion&id="+mepa_programacion_id;
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=detalle_tesoreria_programacion&id="+mepa_programacion_id;
						}, 5000);

						return true;
					} 
					else {
						swal({
							title: "Error al guardar Solicitud",
							text: respuesta.error,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
				},
				complete: function(){
					loading(false);
				}
			});
		}
	});

});

function sec_mepa_detalle_tesoreria_programacion_editar_comprobante()
{
	$("#sec_mepa_detalle_tesoreria_programacion_mostrar_comprobante_div").hide();
	$("#sec_mepa_detalle_tesoreria_programacion_editar_comprobante_div").show();
}

function sec_mepa_detalle_tesoreria_programacion_mostrar_comprobante()
{
	$("#sec_mepa_detalle_tesoreria_programacion_editar_comprobante_div").hide();
	$("#sec_mepa_detalle_tesoreria_programacion_mostrar_comprobante_div").show();
}

$(document).on('submit', "#form_tesoreria_editar_comprobante_pago", function(e) 
{
	e.preventDefault();
	
	var tesoreria_comprobante_pago_edit = document.getElementById("tesoreria_comprobante_pago_edit");

	var tesoreria_fecha_comprobante_pago_edit = $("#tesoreria_fecha_comprobante_pago_edit").val();
	var tesoreria_motivo_comprobante_pago_edit = $("#tesoreria_motivo_comprobante_pago_edit").val();
	var mepa_programacion_id = $("#mepa_programacion_id").val();
	

	if(tesoreria_comprobante_pago_edit.files.length == 0)
	{
		alertify.error('Seleccione el comprobante',5);
		$("#tesoreria_comprobante_pago_edit").focus();
		return false;
	}

	if(tesoreria_motivo_comprobante_pago_edit.length == 0)
	{
		alertify.error('Ingrese Motivo',5);
		$("#tesoreria_motivo_comprobante_pago_edit").focus();
		return false;
	}

	swal(
	{
		title: '¿Está seguro de guardar?',
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
			var dataForm = new FormData($("#form_tesoreria_editar_comprobante_pago")[0]);
			dataForm.append("accion","mepa_tesoreria_editar_comprobante_pago");
			dataForm.append("tesoreria_fecha_comprobante_pago_edit", tesoreria_fecha_comprobante_pago_edit);
			dataForm.append("tesoreria_motivo_comprobante_pago_edit", tesoreria_motivo_comprobante_pago_edit);
			dataForm.append("mepa_programacion_id", mepa_programacion_id);

			$.ajax({
				url: "sys/set_mepa_detalle_tesoreria_programacion.php",
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

					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: "Registro exitoso",
							text: "La solicitud fue registrada exitosamente",
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        window.location.href = "?sec_id=mepa&sub_sec_id=detalle_tesoreria_programacion&id="+mepa_programacion_id;
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=detalle_tesoreria_programacion"+mepa_programacion_id;
						}, 5000);

						return true;
					} 
					else {
						swal({
							title: "Error al guardar Solicitud",
							text: respuesta.error,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
				},
				complete: function(){
					loading(false);
				}
			});
		}
	}
	);

});

function mepa_detalle_tesoreria_programacion_ver_comprobante_pago(tipo_documento, ruta_file) 
{
	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf') 
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" height="525"></iframe>';
		$('#mepa_detalle_programacion_visor_pdf').html(htmlModal);

		$('#mepa_detalle_programacion_div_visor_pdf_modal').modal('show');

	} 
	else if (tipodocumento == 'jpg' || tipodocumento == 'png' || tipodocumento == 'jpeg') 
	{
		var image = new Image();
		image.src = ruta;
		var viewer = new Viewer(image, 
		{
			hidden: function () {
				viewer.destroy();
			},
		});
		// image.click();
		viewer.show();
	}
}

function mepa_tesoreria_asignacion_concar_excel(programacion_id)
{

    var programacion_num_comprobante = $("#mepa_programacion_num_comprobante").val();
    var programacion_num_documento = $("#mepa_programacion_num_documento").val();

    if(programacion_num_comprobante == "")
    {
        alertify.error('Ingrese el numero de comprobante',5);
        $("#mepa_programacion_num_comprobante").focus();
        return false;
    }

    if(programacion_num_documento == "")
    {
        alertify.error('Ingrese el numero de comprobante',5);
        $("#mepa_programacion_num_documento").focus();
        return false;
    }

    var data = {
        "accion": "mepa_tesoreria_asignacion_concar_excel",
        "programacion_id" : programacion_id,
        "programacion_num_comprobante" : programacion_num_comprobante,
        "programacion_num_documento" : programacion_num_documento
    }

    $.ajax({
        url: "/sys/set_mepa_detalle_tesoreria_programacion.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            
            let obj = JSON.parse(resp);
            if(parseInt(obj.estado_archivo) == 1)
            {
                window.open(obj.ruta_archivo);
                loading(false);    
            }
            else if(parseInt(obj.estado_archivo) == 0)
            {
                swal({
                    title: "Error al Generar el Concar",
                    text: obj.ruta_archivo,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
            else
            {
                swal({
                    title: "Error",
                    text: "Ponerse en contacto con Soporte",
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
            
        },
        error: function(resp, status) {

        }
    });
}

function mepa_tesoreria_liquidacion_concar_excel(programacion_id)
{
    
    var programacion_num_comprobante = $("#mepa_programacion_num_comprobante").val();
    var programacion_num_documento = $("#mepa_programacion_num_documento").val();

    if(programacion_num_comprobante == "")
    {
        alertify.error('Ingrese el numero de comprobante',5);
        $("#mepa_programacion_num_comprobante_liquidacion_id"+programacion_id).focus();
        return false;
    }

    if(programacion_num_documento == "")
    {
        alertify.error('Ingrese el numero de comprobante',5);
        $("#mepa_programacion_num_documento").focus();
        return false;
    }

    var data = {
        "accion": "mepa_tesoreria_liquidacion_concar_excel",
        "programacion_id" : programacion_id,
        "programacion_num_comprobante" : programacion_num_comprobante,
        "programacion_num_documento" : programacion_num_documento
    }

    $.ajax({
        url: "/sys/set_mepa_detalle_tesoreria_programacion.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            
            let obj = JSON.parse(resp);
            if(parseInt(obj.estado_archivo) == 1)
            {
                window.open(obj.ruta_archivo);
                loading(false);    
            }
            else if(parseInt(obj.estado_archivo) == 0)
            {
                swal({
                    title: "Error al Generar el Concar",
                    text: obj.ruta_archivo,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
            else
            {
                swal({
                    title: "Error",
                    text: "Ponerse en contacto con Soporte",
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
        },
        error: function(resp, status) {

        }
    });
}

function mepa_tesoreria_aumento_asignacion_concar_excel(programacion_id)
{

    var programacion_num_comprobante = $("#mepa_programacion_num_comprobante").val();
    var programacion_num_documento = $("#mepa_programacion_num_documento").val();

    if(programacion_num_comprobante == "")
    {
        alertify.error('Ingrese el numero de comprobante',5);
        $("#mepa_programacion_num_comprobante").focus();
        return false;
    }

    if(programacion_num_documento == "")
    {
        alertify.error('Ingrese el numero de comprobante',5);
        $("#mepa_programacion_num_documento").focus();
        return false;
    }

    var data = {
        "accion": "mepa_tesoreria_aumento_asignacion_concar_excel",
        "programacion_id" : programacion_id,
        "programacion_num_comprobante" : programacion_num_comprobante,
        "programacion_num_documento" : programacion_num_documento
    }

    $.ajax({
        url: "/sys/set_mepa_detalle_tesoreria_programacion.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            
            let obj = JSON.parse(resp);
            if(parseInt(obj.estado_archivo) == 1)
            {
                window.open(obj.ruta_archivo);
                loading(false);    
            }
            else if(parseInt(obj.estado_archivo) == 0)
            {
                swal({
                    title: "Error al Generar el Concar",
                    text: obj.ruta_archivo,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
            else
            {
                swal({
                    title: "Error",
                    text: "Ponerse en contacto con Soporte",
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
            
        },
        error: function(resp, status) {

        }
    });
}

function sec_mepa_detalle_tesoreria_asignacion_usuarios_programacion_check_todos()
{
	var num_tabla_anterior = 0;

	var nro_filas_tabla = $('#tabla_form_asignacion_usuarios_programacion tr').length;
	num_tabla_anterior = nro_filas_tabla - 1;

	for(var i = 1; i <= num_tabla_anterior; i++)
	{
		document.getElementById("detalle_tesoreria_programacion_asignacion_enviar_correcion_num_cuenta"+i).checked = true;
	}

	$("#sec_mepa_detalle_tesoreria_programacion_boton_asignacion_usuarios_programacion_check_todos").hide();
	$("#sec_mepa_detalle_tesoreria_programacion_boton_asignacion_usuarios_programacion_check_quitar_todos").show();
	
}

function sec_mepa_detalle_tesoreria_asignacion_usuarios_programacion_check_quitar_todos()
{
	var num_tabla_anterior = 0;

	var nro_filas_tabla = $('#tabla_form_asignacion_usuarios_programacion tr').length;
	num_tabla_anterior = nro_filas_tabla - 1;

	for(var i = 1; i <= num_tabla_anterior; i++)
	{
		document.getElementById("detalle_tesoreria_programacion_asignacion_enviar_correcion_num_cuenta"+i).checked = false;
	}

	$("#sec_mepa_detalle_tesoreria_programacion_boton_asignacion_usuarios_programacion_check_quitar_todos").hide();
	$("#sec_mepa_detalle_tesoreria_programacion_boton_asignacion_usuarios_programacion_check_todos").show();
}

$(".detalle_tesoreria_programacion_asignacion_enviar_correcion_num_cuenta").change(function()
{
	var num_tabla_anterior = 0;

	var nro_filas_tabla = $('#tabla_form_asignacion_usuarios_programacion tr').length;
	num_tabla_anterior = nro_filas_tabla - 1;

	if($(this).prop('checked') == true)
	{
		for(var i = 1; i <= num_tabla_anterior; i++)
		{
			if(document.getElementById("detalle_tesoreria_programacion_asignacion_enviar_correcion_num_cuenta"+i).checked == true)
			{
				$("#sec_mepa_detalle_tesoreria_programacion_boton_asignacion_usuarios_programacion_check_quitar_todos").show();
				$("#sec_mepa_detalle_tesoreria_programacion_boton_asignacion_usuarios_programacion_check_todos").hide();

				return;
			}
		}
	}
	else
	{
		for(var i = 1; i <= num_tabla_anterior; i++)
		{
			if(document.getElementById("detalle_tesoreria_programacion_asignacion_enviar_correcion_num_cuenta"+i).checked == false)
			{
				$("#sec_mepa_detalle_tesoreria_programacion_boton_asignacion_usuarios_programacion_check_quitar_todos").hide();
				$("#sec_mepa_detalle_tesoreria_programacion_boton_asignacion_usuarios_programacion_check_todos").show();

				return;
			}
		}
	}
})

function sec_mepa_detalle_tesoreria_notificar_email_cuenta_bancaria()
{
	array_check_usuario_a_notificar = [];

	var usuario_asignado_id = "";

	var num_tabla_anterior = 0;

	var nro_filas_tabla = $('#tabla_form_asignacion_usuarios_programacion tr').length;
	num_tabla_anterior = nro_filas_tabla - 1;

	for(var i = 1; i <= num_tabla_anterior; i++)
	{
		if(document.getElementById('detalle_tesoreria_programacion_asignacion_enviar_correcion_num_cuenta'+i).checked)
		{
			usuario_asignado_id = $("#detalle_tesoreria_programacion_asignacion_enviar_correcion_num_cuenta"+i).val();
			var add_data = {
				"usuario_asignado_id" : usuario_asignado_id
			};
			array_check_usuario_a_notificar.push(add_data);	
		}
	}

	if(array_check_usuario_a_notificar.length == 0)
	{
		alertify.error('Tiene que seleccionar al menos un usuario',5);
		return false;
	}

	swal(
	{
		title: '¿Está seguro de notificar?',
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
				"accion": "mepa_tesoreria_notificar_usuarios_cuenta_bancaria",
				"array_check_usuario_a_notificar": JSON.stringify(array_check_usuario_a_notificar)
			}

			auditoria_send({ "proceso": "mepa_tesoreria_notificar_usuarios_cuenta_bancaria", "data": data });

			$.ajax({
				url: "sys/set_mepa_detalle_tesoreria_programacion.php",
				type: 'POST',
				data: data,
				//cache: false,
				//contentType: false,
				//processData: false,
				beforeSend: function( xhr ) {
					loading(true);
				},
				success: function(data){
					
					var respuesta = JSON.parse(data);
					auditoria_send({ "respuesta": "guardar_solicitud_asignacion_caja_chica", "data": respuesta });
					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: "Notificación exitosa",
							text: "La notificación fue enviada exitosamente",
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
					else {
						swal({
							title: "Error al guardar enviar notificación",
							text: respuesta.error,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
				},
				complete: function(){
					loading(false);
				}
			});
		}
	});
}