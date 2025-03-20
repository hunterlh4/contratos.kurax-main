//INICIO FUNCIONES INICIALIZADOS
function sec_mepa_detalle_solicitud_asignacion()
{	
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_detalle_solicitud_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA
	
	$('.mepa_detalle_solicitud_asignacion_form_txt_nuevo_numero_cuenta').mask('');

	var banco_actual = $("#txt_banco_actual").val();

	if(banco_actual == 12)
	{
	//18 DIGITOS
		$('.mepa_detalle_solicitud_asignacion_form_txt_editar_numero_cuenta').mask('000000000000000000');
		$("#mepa_detalle_solicitud_asignacion_form_txt_editar_digitos").html(18)
	}
	else
	{
		//20 DIGITOS
		$('.mepa_detalle_solicitud_asignacion_form_txt_editar_numero_cuenta').mask('00000000000000000000');
		$("#mepa_detalle_solicitud_asignacion_form_txt_editar_digitos").html(20)
	}
}
//FIN FUNCIONES INICIALIZADOS

$("#sec_mepa_detalle_solicitud_form_asignacion_txt_situacion").on("change", function()
{
	var selectValor = $(this).val();

	if(selectValor == "7")
	{
		// MOSTRAR CAJA DE COMENTARIO DE RECHAZO
		$('#sec_mepa_detalle_solicitud_form_asignacion_txt_motivo_rechazo').val("");
		document.getElementById("sec_mepa_detalle_solicitud_form_asignacion_txt_motivo_rechazo_cantidad_caracteres").innerHTML = "200";
		$("#mepa_detalle_solicitud_asignacion_div_motivo_rechazo").show();
	}
	else
	{
		// OCULTAR CAJA DE RECHAZO PORQUE NO SE PERMITIRA INGRESAR UN MOTIVO CUANDO SE APRUEBA
		$("#mepa_detalle_solicitud_asignacion_div_motivo_rechazo").hide();
	}
})

$("#sec_mepa_detalle_solicitud_form_asignacion_txt_motivo_rechazo").keyup(function ()
{
	$("#sec_mepa_detalle_solicitud_form_asignacion_txt_motivo_rechazo_cantidad_caracteres").text(200 - $(this).val().length)
});

function btn_mepa_detalle_solicitud_asignacion_guardar_atencion()
{
	var mepa_asignacion_caja_chica_id = $('#mepa_asignacion_caja_chica_id').val();
	var txt_situacion = $('#sec_mepa_detalle_solicitud_form_asignacion_txt_situacion').val();
	var txt_motivo_rechazo = $('#sec_mepa_detalle_solicitud_form_asignacion_txt_motivo_rechazo').val();

	if(txt_situacion == 0)
	{
		alertify.error('Seleccione Situacion',5);
		$("#sec_mepa_detalle_solicitud_form_asignacion_txt_situacion").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_detalle_solicitud_form_asignacion_txt_situacion').select2('open');
		}, 200);
		return false;
	}

	if(txt_situacion == 7)
	{
		if(txt_motivo_rechazo.length == 0)
		{
			alertify.error('Ingrese el motivo del rechazo',5);
			$("#sec_mepa_detalle_solicitud_form_asignacion_txt_motivo_rechazo").focus();
			return false;
		}
	}

	swal(
	{
		title: '¿Está seguro de registrar la Solicitud?',
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
			var dataForm = new FormData($("#sec_btn_mepa_detalle_solicitud_asignacion_guardar_atencion")[0]);
			dataForm.append("accion","guardar_detalle_solicitud_asignacion_atencion");
			dataForm.append("mepa_asignacion_caja_chica_id", mepa_asignacion_caja_chica_id);
			dataForm.append("txt_situacion", txt_situacion);
			dataForm.append("txt_motivo_rechazo", txt_motivo_rechazo);

			$.ajax({
				url: "sys/set_mepa_detalle_solicitud_asignacion.php",
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
					auditoria_send({ "respuesta": "sec_btn_mepa_detalle_solicitud_asignacion_guardar_atencion", "data": respuesta });
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
					        window.location.href = "?sec_id=mepa&sub_sec_id=detalle_solicitud_asignacion&id="+respuesta.mepa_asignacion_caja_chica_id;
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=detalle_solicitud_asignacion&id="+respuesta.mepa_asignacion_caja_chica_id;
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

function mepa_detalle_solicitud_asignacion_ver_comprobante_pago(tipo_documento, ruta_file) 
{
	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf') 
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" height="525"></iframe>';
		$('#mepa_detalle_programacion_visor_pdf').html(htmlModal);

		$('#mepa_detalle_solicitud_asignacion_div_visor_pdf_modal').modal('show');

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

function sec_mepa_detalle_solicitud_asignacion_btn_editar_cuenta_bancaria()
{
	
	$("#mepa_detalle_solicitud_asignacion_modal_editar_cuenta_bancaria .btn_editar").show();
	$("#title_mepa_detalle_solicitud_asignacion_modal_editar_cuenta_bancaria").text("Editar Cuenta Bancaria");
	
	$("#mepa_detalle_solicitud_asignacion_modal_editar_cuenta_bancaria").modal("show");
}

$('#mepa_detalle_solicitud_asignacion_form_txt_editar_banco').change(function () 
{
	$("#mepa_detalle_solicitud_asignacion_form_txt_editar_banco option:selected").each(function ()
	{	
		
		var selectValor = $(this).val();

		//12 = BBVA
		if(selectValor == 12)
		{
			//18 DIGITOS
			$('.mepa_detalle_solicitud_asignacion_form_txt_editar_numero_cuenta').mask('000000000000000000');
			$("#mepa_detalle_solicitud_asignacion_form_txt_editar_digitos").html(18)
		}
		else
		{
			//20 DIGITOS
			$('.mepa_detalle_solicitud_asignacion_form_txt_editar_numero_cuenta').mask('00000000000000000000');
			$("#mepa_detalle_solicitud_asignacion_form_txt_editar_digitos").html(20)
		}
		
		
	});
});

$("#mepa_detalle_solicitud_asignacion_modal_editar_cuenta_bancaria .btn_editar").off("click").on("click",function(){
    
	
    var form_txt_id_asignacion = $('#mepa_detalle_solicitud_asignacion_form_txt_id_asignacion').val();
    var form_txt_editar_banco = $('#mepa_detalle_solicitud_asignacion_form_txt_editar_banco').val();
    var form_txt_editar_numero_cuenta = $('#mepa_detalle_solicitud_asignacion_form_txt_editar_numero_cuenta').val();

    if(form_txt_id_asignacion == 0 || form_txt_id_asignacion == "")
    {
    	alertify.error('No existe la asignación',5);
		return false;
    }

    if(form_txt_editar_banco == "0")
	{
		alertify.error('Seleccione el Banco',5);
		$("#mepa_detalle_solicitud_asignacion_form_txt_editar_banco").focus();
		return false;
	}

	if(form_txt_editar_banco == 12)
	{
		if(form_txt_editar_numero_cuenta.length < 18)
		{
			alertify.error('Ingrese Número de Cuenta de 18 dígitos',5);
			$("#mepa_detalle_solicitud_asignacion_form_txt_editar_numero_cuenta").focus();
			return false;
		}
	}
	else
	{
		if(form_txt_editar_numero_cuenta.length < 20)
		{
			alertify.error('Ingrese Número de Cuenta de 20 dígitos',5);
			$("#mepa_detalle_solicitud_asignacion_form_txt_editar_numero_cuenta").focus();
			return false;
		}
	}

    swal(
	{
		title: '¿Está seguro de editar?',
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

			var dataForm = new FormData($("#sec_mepa_detalle_solicitud_asignacion_form_editar_cuenta_bancaria")[0]);
			dataForm.append("accion","mepa_detalle_solicitud_asignacion_editar_cuenta_bancaria");
			dataForm.append("form_txt_id_asignacion", form_txt_id_asignacion);
			dataForm.append("form_txt_editar_banco", form_txt_editar_banco);
			dataForm.append("form_txt_editar_numero_cuenta", form_txt_editar_numero_cuenta);

			$.ajax({
				url: "sys/set_mepa_detalle_solicitud_asignacion.php",
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
})

function sec_mepa_detalle_solicitud_asignacion_btn_nuevo_cuenta_bancaria()
{
	
	$("#mepa_detalle_solicitud_asignacion_modal_nuevo_cuenta_bancaria .btn_guardar").show();
	$("#title_mepa_detalle_solicitud_asignacion_modal_nuevo_cuenta_bancaria").text("Nueva Cuenta Bancaria");
	
	$("#mepa_detalle_solicitud_asignacion_modal_nuevo_cuenta_bancaria").modal("show");
}

$('#mepa_detalle_solicitud_asignacion_form_txt_nuevo_banco').change(function () 
{
	$("#mepa_detalle_solicitud_asignacion_form_txt_nuevo_banco option:selected").each(function ()
	{	
		var selectValor = $(this).val();

		//12 = BBVA
		if(selectValor == 12)
		{
			//18 DIGITOS
			$('.mepa_detalle_solicitud_asignacion_form_txt_nuevo_numero_cuenta').mask('000000000000000000');
			$("#mepa_detalle_solicitud_asignacion_form_txt_nuevo_digitos").html(18)
		}
		else
		{
			//20 DIGITOS
			$('.mepa_detalle_solicitud_asignacion_form_txt_nuevo_numero_cuenta').mask('00000000000000000000');
			$("#mepa_detalle_solicitud_asignacion_form_txt_nuevo_digitos").html(20)
		}
		
		
	});
});

$("#mepa_detalle_solicitud_asignacion_modal_nuevo_cuenta_bancaria .btn_guardar").off("click").on("click",function(){
    
	var form_txt_id_asignacion = $('#mepa_detalle_solicitud_asignacion_form_txt_nuevo_id_asignacion').val();
    var form_txt_banco = $('#mepa_detalle_solicitud_asignacion_form_txt_nuevo_banco').val();
    var form_txt_numero_cuenta = $('#mepa_detalle_solicitud_asignacion_form_txt_nuevo_numero_cuenta').val();

    if(form_txt_id_asignacion == 0 || form_txt_id_asignacion == "")
    {
    	alertify.error('No existe la asignación',5);
		return false;
    }

    if(form_txt_banco == "0")
	{
		alertify.error('Seleccione el Banco',5);
		$("#mepa_detalle_solicitud_asignacion_form_txt_nuevo_banco").focus();
		return false;
	}

	if(form_txt_banco == 12)
	{
		if(form_txt_numero_cuenta.length < 18)
		{
			alertify.error('Ingrese Número de Cuenta de 18 dígitos',5);
			$("#mepa_detalle_solicitud_asignacion_form_txt_nuevo_numero_cuenta").focus();
			return false;
		}
	}
	else
	{
		if(form_txt_numero_cuenta.length < 20)
		{
			alertify.error('Ingrese Número de Cuenta de 20 dígitos',5);
			$("#mepa_detalle_solicitud_asignacion_form_txt_nuevo_numero_cuenta").focus();
			return false;
		}
	}

    swal(
	{
		title: '¿Está seguro de agregar?',
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

			var dataForm = new FormData($("#sec_mepa_detalle_solicitud_asignacion_form_nuevo_cuenta_bancaria")[0]);
			dataForm.append("accion","mepa_detalle_solicitud_asignacion_nuevo_cuenta_bancaria");
			dataForm.append("form_txt_id_asignacion", form_txt_id_asignacion);
			dataForm.append("form_txt_banco", form_txt_banco);
			dataForm.append("form_txt_numero_cuenta", form_txt_numero_cuenta);

			$.ajax({
				url: "sys/set_mepa_detalle_solicitud_asignacion.php",
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
})

function sec_mepa_detalle_solicitud_asignacion_guardar_zona()
{
	
	var mepa_asignacion_caja_chica_id = $('#mepa_asignacion_caja_chica_id').val();
	var txt_zona = $('#sec_mepa_detalle_solicitud_asignacion_txt_zona').val();

	if(txt_zona == 0)
	{
		alertify.error('Seleccione Zona - Centro de Costo',5);
		$("#sec_mepa_detalle_solicitud_asignacion_txt_zona").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_detalle_solicitud_asignacion_txt_zona').select2('open');
		}, 200);
		return false;
	}

	swal(
	{
		title: '¿Está seguro de registrar la zona?',
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
			var dataForm = {
				"accion": "sec_mepa_detalle_solicitud_asignacion_guardar_zona",
				"mepa_asignacion_caja_chica_id": mepa_asignacion_caja_chica_id,
				"txt_zona": txt_zona
			}
			
			$.ajax({
				url: "sys/set_mepa_detalle_solicitud_asignacion.php",
				type: 'POST',
				data: dataForm,
				beforeSend: function( xhr ) 
				{
					loading(true);
				},
				success: function(data)
				{
					
					var respuesta = JSON.parse(data);
					auditoria_send({ "respuesta": "sec_mepa_detalle_solicitud_asignacion_guardar_zona", "data": respuesta });
					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: respuesta.status,
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
					else 
					{
						swal({
							title: respuesta.status,
							text: respuesta.texto,
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

function sec_mepa_detalle_solicitud_asignacion_btn_editar_empresa()
{
	
	$("#mepa_detalle_solicitud_asignacion_modal_editar_empresa .btn_editar").show();
	$("#title_mepa_detalle_solicitud_asignacion_modal_editar_empresa").text("Editar Empresa");
	
	$("#mepa_detalle_solicitud_asignacion_modal_editar_empresa").modal("show");
}

$("#mepa_detalle_solicitud_asignacion_modal_editar_empresa .btn_editar").off("click").on("click",function(){
    
	
    var form_txt_id_asignacion = $('#mepa_asignacion_caja_chica_id').val();
    var form_txt_editar_empresa = $('#mepa_detalle_solicitud_asignacion_form_txt_editar_empresa').val();

    if(form_txt_id_asignacion == 0 || form_txt_id_asignacion == "")
    {
    	alertify.error('No existe la asignación',5);
		return false;
    }

    if(form_txt_editar_empresa == "0")
	{
		alertify.error('Seleccione el Banco',5);
		$("#mepa_detalle_solicitud_asignacion_form_txt_editar_empresa").focus();
		return false;
	}

	swal(
	{
		title: '¿Está seguro de editar la empresa?',
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

			var dataForm = new FormData($("#sec_mepa_detalle_solicitud_asignacion_form_editar_cuenta_bancaria")[0]);
			dataForm.append("accion","mepa_detalle_solicitud_asignacion_editar_empresa");
			dataForm.append("form_txt_id_asignacion", form_txt_id_asignacion);
			dataForm.append("form_txt_editar_empresa", form_txt_editar_empresa);

			$.ajax({
				url: "sys/set_mepa_detalle_solicitud_asignacion.php",
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
							title: "Edición exitoso",
							text: "La edición fue registrada exitosamente",
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
})