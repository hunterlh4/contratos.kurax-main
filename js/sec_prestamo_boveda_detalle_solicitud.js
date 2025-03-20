function sec_prestamo_boveda_detalle_solicitud()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".prestamo_boveda_detalle_solicitud_select").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA
}

function sec_prestamo_boveda_detalle_guardar_recibe_dinero()
{
	
	var sec_prestamo_boveda_id = $('#sec_prestamo_boveda_id').val();
	var txt_motivo = $('#form_prestamo_boveda_detalle_param_motivo').val().trim();

	if(txt_motivo.length == 0)
	{
		alertify.error('Ingrese el motivo',5);
		$("#form_prestamo_boveda_detalle_param_motivo").focus();
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
			var dataForm = new FormData($("#prestamo_boveda_detalle_form_guardar_recibe_dinero")[0]);
			dataForm.append("accion","sec_prestamo_boveda_detalle_guardar_recibe_dinero");
			dataForm.append("sec_prestamo_boveda_id", sec_prestamo_boveda_id);
			dataForm.append("txt_motivo", txt_motivo);

			$.ajax({
				url: "sys/set_prestamo_boveda_detalle_solicitud.php",
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
					auditoria_send({ "respuesta": "prestamo_slot_detalle_form_eliminar_prestamo", "data": respuesta });
					if(parseInt(respuesta.http_code) == 200)
					{
						swal({
							title: "Registro exitoso",
							text: "El registro fue exitoso.",
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
}

function sec_prestamo_boveda_detalle_guardar_atencion()
{
	var prestamo_boveda_id = $('#sec_prestamo_boveda_id').val();
	var txt_situacion = $('#prestamo_boveda_detalle_solicitud_atender_jefe_situacion').val();
	
	var texto_pregunta = "";
	if(txt_situacion == 0)
	{
		alertify.error('Seleccione Situacion',5);
		$("#prestamo_boveda_detalle_solicitud_atender_jefe_situacion").focus();
		setTimeout(function() 
		{
			$('#prestamo_boveda_detalle_solicitud_atender_jefe_situacion').select2('open');
		}, 200);
		return false;
	}
	else if(txt_situacion == 2)
	{
		texto_pregunta = "Aprobar";
	}
	else if(txt_situacion == 4)
	{
		texto_pregunta = "Rechazar";
	}

	swal(
	{
		title: '¿Está seguro de ' +texto_pregunta+ '?',
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
			var dataForm = new FormData($("#prestamo_boveda_detalle_form_guardar_atencion")[0]);
			dataForm.append("accion","sec_prestamo_boveda_detalle_guardar_atencion");
			dataForm.append("prestamo_boveda_id", prestamo_boveda_id);
			dataForm.append("txt_situacion", txt_situacion);

			$.ajax({
				url: "sys/set_prestamo_boveda_detalle_solicitud.php",
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
					auditoria_send({ "respuesta": "prestamo_slot_detalle_form_guardar_atencion", "data": respuesta });
					if(parseInt(respuesta.http_code) == 200)
					{
						swal({
							title: "Atención existoso",
							text: "La atención fue exitoso.",
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
}

function sec_prestamo_boveda_detalle_ver_archivo(tipo_documento, ruta_file) 
{
	
	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf') 
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" height="650"></iframe>';
		$('#prestamo_boveda_detalle_visor_pdf').html(htmlModal);

		$('#prestamo_boveda_detalle_div_visor_pdf_modal').modal('show');

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

const sec_prestamo_boveda_detalle_revertir_rechazado = () => {
	
	swal(
	{
		title: "Está seguro de revertir el préstamo bóveda",
		type: "warning",
		showCancelButton: true,
		confirmButtonText: "Si",
		cancelButtonText: "No",
		closeOnConfirm: false,
		closeOnCancel: true
	},
	function(isConfirm)
	{
		if(isConfirm)
		{
			var data = {
				"accion": "sec_prestamo_boveda_detalle_revertir_rechazado"
			}

			$.ajax({
				url:"sys/set_prestamo_boveda_detalle_solicitud.php",
				type: "POST",
				data: data,
				beforeSend: function( xhr )
				{
					loading(true);
				},
				success: function(data)
				{
					
					var respuesta = JSON.parse(data);
					auditoria_send({ "respuesta": "sec_prestamo_boveda_detalle_revertir_rechazado", "data": respuesta });
					if(parseInt(respuesta.http_code) == 200)
					{
						swal({
							title: respuesta.titulo,
							text: respuesta.texto,
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
							title: respuesta.titulo,
							text: respuesta.texto,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
				},
				complete: function()
				{
					loading(false);
				}
			});
		}
	});
}