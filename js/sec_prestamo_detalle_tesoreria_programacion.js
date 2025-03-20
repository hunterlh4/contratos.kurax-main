function sec_prestamo_detalle_tesoreria_programacion()
{
	// INICIO FORMATO Y BUSQUEDA DE FECHA
	$('.sec_prestamo_detalle_tesoreria_programacion_datepicker')
		.datepicker({
			dateFormat:'dd-mm-yy',
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

	set_prestamo_tesoreria_comprobante_pago($('#prestamo_tesoreria_comprobante_pago'));
	set_prestamo_tesoreria_comprobante_pago_edit($('#prestamo_tesoreria_comprobante_pago_edit'));
}

function set_prestamo_tesoreria_comprobante_pago(object){
	
	$(document).on('click', '#btn_buscar_prestamo_tesoreria_comprobante', function(event) {
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

		$("#txt_prestamo_tesoreria_comprobante_archivo").html(truncated);
	});
}

function set_prestamo_tesoreria_comprobante_pago_edit(object){

	$(document).on('click', '#btn_buscar_prestamo_tesoreria_comprobante_edit', function(event) {
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
		
		$("#txt_prestamo_tesoreria_comprobante_archivo_edit").html(truncated);
	});
}

$(document).on('submit', "#form_prestamo_tesoreria_guardar_comprobante_pago", function(e) 
{
	e.preventDefault();
	
	var tesoreria_comprobante_pago = document.getElementById("prestamo_tesoreria_comprobante_pago");
	var tesoreria_fecha_comprobante_pago = $("#tesoreria_fecha_comprobante_pago").val();
	var prestamo_programacion_id = $("#prestamo_programacion_id").val();
	
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
        $("#prestamo_tesoreria_comprobante_pago").focus();
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

			var dataForm = new FormData($("#form_prestamo_tesoreria_guardar_comprobante_pago")[0]);
			dataForm.append("accion","prestamo_tesoreria_guardar_comprobante_pago");
			dataForm.append("tesoreria_fecha_comprobante_pago", tesoreria_fecha_comprobante_pago);
			dataForm.append("prestamo_programacion_id", prestamo_programacion_id);

			$.ajax({
				url: "sys/set_prestamo_detalle_tesoreria_programacion.php",
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
							text: "Se registro exitosamente",
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        window.location.href = "?sec_id=prestamo&sub_sec_id=detalle_tesoreria_programacion&id="+prestamo_programacion_id;
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=prestamo&sub_sec_id=detalle_tesoreria_programacion&id="+prestamo_programacion_id;
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

function sec_prestamo_detalle_tesoreria_programacion_editar_comprobante()
{
	$("#sec_prestamo_detalle_tesoreria_programacion_mostrar_comprobante_div").hide();
	$("#sec_prestamo_detalle_tesoreria_programacion_editar_comprobante_div").show();
}

function sec_prestamo_detalle_tesoreria_programacion_mostrar_comprobante()
{
	$("#sec_prestamo_detalle_tesoreria_programacion_editar_comprobante_div").hide();
	$("#sec_prestamo_detalle_tesoreria_programacion_mostrar_comprobante_div").show();
}

$(document).on('submit', "#form_prestamo_tesoreria_editar_comprobante_pago", function(e) 
{
	e.preventDefault();
	
	var tesoreria_comprobante_pago_edit = document.getElementById("prestamo_tesoreria_comprobante_pago_edit");

	var tesoreria_fecha_comprobante_pago_edit = $("#prestamo_tesoreria_fecha_comprobante_pago_edit").val();
	var tesoreria_motivo_comprobante_pago_edit = $("#prestamo_tesoreria_motivo_comprobante_pago_edit").val().trim();
	var prestamo_programacion_id = $("#prestamo_programacion_id").val();
	

	if(tesoreria_comprobante_pago_edit.files.length > 0)
    {
        for(var i = 0; i < tesoreria_comprobante_pago_edit.files.length; i ++)
        {
            if(tesoreria_comprobante_pago_edit.files[i].size > 1000000)
            {
                alertify.error(`EL Archivo ${tesoreria_comprobante_pago_edit.files[i].name} debe pesar menos de 1MB`,5);
                return false;
            }
        }
    }
    else
    {
        alertify.error('Seleccione el comprobante',5);
        $("#prestamo_tesoreria_comprobante_pago_edit").focus();
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
			var dataForm = new FormData($("#form_prestamo_tesoreria_editar_comprobante_pago")[0]);
			dataForm.append("accion","prestamo_tesoreria_editar_comprobante_pago");
			dataForm.append("tesoreria_fecha_comprobante_pago_edit", tesoreria_fecha_comprobante_pago_edit);
			dataForm.append("tesoreria_motivo_comprobante_pago_edit", tesoreria_motivo_comprobante_pago_edit);
			dataForm.append("prestamo_programacion_id", prestamo_programacion_id);

			$.ajax({
				url: "sys/set_prestamo_detalle_tesoreria_programacion.php",
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
							text: "Se registro exitosamente",
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        window.location.href = "?sec_id=prestamo&sub_sec_id=detalle_tesoreria_programacion&id="+prestamo_programacion_id;
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=prestamo&sub_sec_id=detalle_tesoreria_programacion"+prestamo_programacion_id;
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

function sec_prestamo_detalle_tesoreria_programacion_rechazar_usuario(id_prestamo_detalle, id_prestamo)
{
	var titulo = '¿Está seguro de rechazar?';

	swal(
	{
		title: `<h3>${titulo}</h3>` + '<span style="font-size:12px">Motivo :</span> <textarea id="sec_prestamo_detalle_tesoreria_motivo_rechazo" autofocus name="sec_prestamo_detalle_tesoreria_motivo_rechazo" class="form-control" style="display:block;font-size:11px;margin-top: -10px;" maxlength="50"></textarea>',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true,
		html: true,
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{

			var motivo_rechazo = $('#sec_prestamo_detalle_tesoreria_motivo_rechazo').val().trim();

			if(motivo_rechazo.length == 0)
			{
				alertify.error('Ingrese el motivo',5);
				$("#sec_prestamo_detalle_tesoreria_motivo_rechazo").focus();
				return false;
			}

			var data = {
				"accion": "sec_prestamo_detalle_tesoreria_programacion_rechazar_usuario",
				"id_prestamo_detalle": id_prestamo_detalle,
				"id_prestamo": id_prestamo,
				"motivo_rechazo": motivo_rechazo
			}

			auditoria_send({ "proceso": "sec_prestamo_detalle_tesoreria_programacion_rechazar_usuario", "data": data });

			$.ajax({
				url: "sys/set_prestamo_detalle_tesoreria_programacion.php",
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
					auditoria_send({ "respuesta": "sec_prestamo_detalle_tesoreria_programacion_rechazar_usuario", "data": respuesta });
					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: "Rechazó exitoso",
							text: "El rechazó fue exitoso",
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
							title: "Error al rechazar",
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

function prestamo_detalle_tesoreria_programacion_ver_comprobante_pago(tipo_documento, ruta_file) 
{

	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf') 
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" height="650"></iframe>';
		$('#prestamo_detalle_programacion_visor_pdf').html(htmlModal);

		$('#prestamo_detalle_programacion_div_visor_pdf_modal').modal('show');

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

function sec_prestamo_detalle_tesoreria_btn_reporte_boveda(programacion_id)
{
	var data = {
        "accion": "sec_prestamo_detalle_tesoreria_btn_reporte_boveda",
        "programacion_id": programacion_id
    }

    $.ajax({
        url: "/sys/set_prestamo_detalle_tesoreria_programacion.php",
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
            auditoria_send({ "respuesta": "sec_prestamo_detalle_tesoreria_btn_reporte_boveda", "data": obj });

            window.open(obj.ruta_archivo);
            loading(false);
        },
        error: function(resp, status) {

        }
    });
}

function mepa_egresos_boveda_concar_excel(programacion_boveda_id)
{

	var programacion_boveda_tipo_prestamo = $("#mepa_programacion_boveda_tipo_prestamo").val();
    var programacion_boveda_num_comprobante = $("#mepa_programacion_boveda_num_comprobante").val();
    var programacion_boveda_num_documento = $("#mepa_programacion_boveda_num_documento").val();

    if(programacion_boveda_num_comprobante == "")
    {
        alertify.error('Ingrese el numero de comprobante',5);
        $("#mepa_programacion_boveda_num_comprobante").focus();
        return false;
    }

    if(programacion_boveda_num_documento == "")
    {
        alertify.error('Ingrese el numero de comprobante',5);
        $("#mepa_programacion_boveda_num_documento").focus();
        return false;
    }

    var data = {
        "accion": "mepa_prestamo_boveda_concar_excel",
        "programacion_boveda_id" : programacion_boveda_id,
        "programacion_boveda_num_comprobante" : programacion_boveda_num_comprobante,
        "programacion_boveda_num_documento" : programacion_boveda_num_documento,
		"programacion_boveda_tipo_prestamo": programacion_boveda_tipo_prestamo,
    }

    $.ajax({
        url: "/sys/set_prestamo_detalle_tesoreria_programacion.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
			console.log(resp)
            
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
                    text: obj.error,
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

function sec_prestamo_detalle_tesoreria_programacion_anular_prestamo(id_prestamo_detalle, id_prestamo)
{
	
	swal(
	{
		title: '¿Está seguro de anular?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true,
		html: true,
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{

			var data = {
				"accion": "sec_prestamo_detalle_tesoreria_programacion_anular_prestamo",
				"id_prestamo_detalle": id_prestamo_detalle,
				"id_prestamo": id_prestamo
			}

			auditoria_send({ "proceso": "sec_prestamo_detalle_tesoreria_programacion_anular_prestamo", "data": data });

			$.ajax({
				url: "sys/set_prestamo_detalle_tesoreria_programacion.php",
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
					auditoria_send({ "respuesta": "sec_prestamo_detalle_tesoreria_programacion_anular_prestamo", "data": respuesta });
					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: "Anulación exitoso",
							text: "El anulación fue exitoso",
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
							title: "Error al anular",
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