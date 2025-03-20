function sec_mepa_detalle_solicitud_aumento()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_detalle_solicitud_aumento_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA
}

$("#sec_mepa_detalle_solicitud_aumento_form_aumento_txt_situacion").on("change", function()
{
	var selectValor = $(this).val();

	if(selectValor == "7")
	{
		// MOSTRAR CAJA DE COMENTARIO DE RECHAZO
		$('#sec_mepa_detalle_solicitud_aumento_form_aumento_txt_motivo_rechazo').val("");
		$("#mepa_detalle_solicitud_aumento_div_motivo_rechazo").show();
	}
	else
	{
		// OCULTAR CAJA DE RECHAZO PORQUE NO SE PERMITIRA INGRESAR UN MOTIVO CUANDO SE APRUEBA
		$("#mepa_detalle_solicitud_aumento_div_motivo_rechazo").hide();
	}
})

function btn_mepa_detalle_solicitud_aumento_guardar_atencion()
{
	var mepa_aumento_solicitud_id = $('#mepa_aumento_solicitud_id').val();
	var txt_situacion = $('#sec_mepa_detalle_solicitud_aumento_form_aumento_txt_situacion').val();
	var txt_motivo_rechazo = $('#sec_mepa_detalle_solicitud_aumento_form_aumento_txt_motivo_rechazo').val().trim();

	if(txt_situacion == 0)
	{
		alertify.error('Seleccione Situacion',5);
		$("#sec_mepa_detalle_solicitud_aumento_form_aumento_txt_situacion").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_detalle_solicitud_aumento_form_aumento_txt_situacion').select2('open');
		}, 200);
		return false;
	}

	if(txt_situacion == 7)
	{
		if(txt_motivo_rechazo == "")
		{
			alertify.error('Ingrese el motivo del rechazo',5);
			$("#sec_mepa_detalle_solicitud_aumento_form_aumento_txt_motivo_rechazo").focus();
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
			var dataForm = new FormData($("#sec_mepa_detalle_solicitud_aumento_guardar_atencion")[0]);
			dataForm.append("accion","guardar_detalle_solicitud_aumento_atencion");
			dataForm.append("mepa_aumento_solicitud_id", mepa_aumento_solicitud_id);
			dataForm.append("txt_situacion", txt_situacion);
			dataForm.append("txt_motivo_rechazo", txt_motivo_rechazo);

			$.ajax({
				url: "sys/set_mepa_detalle_solicitud_aumento.php",
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
					
					auditoria_send({ "respuesta": "sec_mepa_detalle_solicitud_aumento_guardar_atencion", "data": respuesta });
					
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
					        window.location.href = "?sec_id=mepa&sub_sec_id=detalle_solicitud_aumento&id="+respuesta.mepa_aumento_solicitud_id;
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=detalle_solicitud_aumento&id="+respuesta.mepa_aumento_solicitud_id;
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

