//INICIO FUNCIONES INICIALIZADOS
function sec_prestamo_slot_detalle_solicitud()
{	
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_prestamo_slot_detalle_solicitud_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA
}
//FIN FUNCIONES INICIALIZADOS

$('#form_prestamo_slot_detalle_param_situacion').change(function () 
{
    $("#form_prestamo_slot_detalle_param_situacion option:selected").each(function ()
    {   
        var selectValor = $(this).val();

        if(selectValor != 0)
        {
            sec_prestamo_slot_detalle_validar_caja_abierta_tienda_receptor();
        }
    });
});

function sec_prestamo_slot_detalle_validar_caja_abierta_tienda_receptor() 
{   
	var prestamo_slot_id = $('#sec_prestamo_slot_id').val();
    var data = {
        "accion": "sec_prestamo_slot_detalle_validar_caja_abierta_tienda_receptor",
        "prestamo_slot_id": prestamo_slot_id
    }
    
    $.ajax({
        url: "/sys/set_prestamo_slot_detalle_solicitud.php",
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
            if (parseInt(respuesta.http_code) == 200) 
            {
                // INICIO MOSTRAR DIV SI EN CASO NO EXISTE CAJA ABIERTA
                if(respuesta.existe_caja_abierta == 0)
                {
                    $("#sec_prestamo_slot_detalle_div_existe_caja_abierta").show();
                    $('#sec_prestamo_slot_detalle_btn_guardar_atencion_solicitud').prop("disabled", true);
                }
                else
                {
                    $("#sec_prestamo_slot_detalle_div_existe_caja_abierta").hide();
                    $('#sec_prestamo_slot_detalle_btn_guardar_atencion_solicitud').prop("disabled", false);
                }
                // FIN MOSTRAR DIV SI EN CASO NO EXISTE CAJA ABIERTA
                
                return false;
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
        error: function() {}
    });
}

function sec_prestamo_slot_detalle_guardar_atencion()
{
	var sec_prestamo_slot_id = $('#sec_prestamo_slot_id').val();
	var txt_situacion = $('#form_prestamo_slot_detalle_param_situacion').val();

	if(txt_situacion == 0)
	{
		alertify.error('Seleccione Situacion',5);
		$("#form_prestamo_slot_detalle_param_situacion").focus();
		setTimeout(function() 
		{
			$('#form_prestamo_slot_detalle_param_situacion').select2('open');
		}, 200);
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
			var dataForm = new FormData($("#prestamo_slot_detalle_form_guardar_atencion")[0]);
			dataForm.append("accion","prestamo_slot_detalle_guardar_atencion");
			dataForm.append("sec_prestamo_slot_id", sec_prestamo_slot_id);
			dataForm.append("txt_situacion", txt_situacion);

			$.ajax({
				url: "sys/set_prestamo_slot_detalle_solicitud.php",
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

function sec_prestamo_slot_detalle_eliminar_prestamo()
{
	var sec_prestamo_slot_id = $('#sec_prestamo_slot_id').val();
	var txt_motivo = $('#form_prestamo_slot_detalle_eliminar_param_motivo').val().trim();

	if(txt_motivo.length == 0)
	{
		alertify.error('Ingrese el motivo',5);
		$("#form_prestamo_slot_detalle_eliminar_param_motivo").focus();
		return false;
	}

	swal(
	{
		title: '¿Está seguro de anular?',
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
			var dataForm = new FormData($("#prestamo_slot_detalle_form_eliminar_prestamo")[0]);
			dataForm.append("accion","prestamo_slot_detalle_eliminar_prestamo");
			dataForm.append("sec_prestamo_slot_id", sec_prestamo_slot_id);
			dataForm.append("txt_motivo", txt_motivo);

			$.ajax({
				url: "sys/set_prestamo_slot_detalle_solicitud.php",
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
							title: "Anulación exitoso",
							text: "La anulación fue exitoso.",
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
