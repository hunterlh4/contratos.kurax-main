// INICIO DECLARACION DE VARIABLES ARRAY

var array_boveda_programacion_de_pagos = [];

// FIN DECLARACION DE VARIABLES ARRAY


function sec_prestamo_tesoreria_atencion()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_boveda_prestamo_tesoreria_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA
}

$('#boveda_prestamo_tesoreria_atencion_tipo_empresa').change(function () 
{
	$("#boveda_prestamo_tesoreria_atencion_tipo_empresa option:selected").each(function ()
	{	
		$("#mepa_tesoreria_div_asignacion_detalle_programacion_pagos").hide();
		$("#mepa_tesoreria_div_liquidacion_detalle_programacion_pagos").hide();
		$("#mepa_tesoreria_div_aumento_asignacion_detalle_programacion_pagos").hide();

		var selectValor = $(this).val();

		if(selectValor == 0)
		{
			var html = '<option value="0">-- Seleccione --</option>';
			$("#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta").html(html).trigger("change");
	        
			alertify.error('Seleccione Empresa',5);
	        $("#boveda_prestamo_tesoreria_atencion_tipo_empresa").focus();
	        setTimeout(function() 
	        {
	            $('#boveda_prestamo_tesoreria_atencion_tipo_empresa').select2('open');
	        }, 200);


	        return false;
		}

		prestamo_tesoreria_atencion_obtener_empresa_numero_cuenta(selectValor);
	});
});

function prestamo_tesoreria_atencion_obtener_empresa_numero_cuenta(empresa) 
{	
	var data = {
		"accion": "prestamo_tesoreria_atencion_obtener_empresa_numero_cuenta",
		"empresa": empresa
	}
	
	var array_empresa_num_cuenta = [];
	
	$.ajax({
		url: "/sys/set_prestamo_tesoreria_atencion.php",
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
			if (parseInt(respuesta.http_code) == 400) 
			{
				var html = '<option value="0">-- Seleccione --</option>';
				$("#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta").html(html).trigger("change");

				setTimeout(function() {
					$('#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta').select2('open');
				}, 500);

				return false;

			}
			else if (parseInt(respuesta.http_code) == 200) 
			{
				array_empresa_num_cuenta.push(respuesta.result);
			
				var html = '<option value="0">-- Seleccione --</option>';

				for (var i = 0; i < array_empresa_num_cuenta[0].length; i++) 
				{
					html += '<option value=' + array_empresa_num_cuenta[0][i].id  + '>' + array_empresa_num_cuenta[0][i].nombre + '</option>';
				}

				$("#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta").html(html).trigger("change");

				setTimeout(function() {
					$('#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta').select2('open');
				}, 500);
				
				return false;
			}
		},
		error: function() {}
	});
};

$('#boveda_prestamo_tesoreria_atencion_tipo_tienda').change(function () 
{
	$("#boveda_prestamo_tesoreria_atencion_tipo_tienda option:selected").each(function ()
	{	
		$("#prestamo_tesoreria_atencion_div_boveda_detalle_programacion_pagos").hide();

		var selectValor = $(this).val();

		if(selectValor == 0)
		{
			$("#boveda_prestamo_tesoreria_atencion_banco_div").hide();

			alertify.error('Seleccione Tipo Tienda',5);
	        $("#boveda_prestamo_tesoreria_atencion_tipo_tienda").focus();
	        setTimeout(function() 
	        {
	            $('#boveda_prestamo_tesoreria_atencion_tipo_tienda').select2('open');
	        }, 200);

	        return false;
		}
		else if(selectValor == 9)
		{
			// Red Sportsbars
			$("#boveda_prestamo_tesoreria_atencion_banco_div").hide();
		}
		else if(selectValor == 1 || selectValor == 16)
		{
			// Red AT
			// Red IGH
			$("#boveda_prestamo_tesoreria_atencion_banco_div").show();
		}
	});
});

$('#boveda_prestamo_tesoreria_atencion_tipo_prestamo').change(function () 
{
	$("#boveda_prestamo_tesoreria_atencion_tipo_prestamo option:selected").each(function ()
	{	
		$("#prestamo_tesoreria_atencion_div_boveda_detalle_programacion_pagos").hide();

		var select_param_tipo_tienda = $("#boveda_prestamo_tesoreria_atencion_tipo_tienda").val();

		var selectValor = $(this).val();

		if(selectValor == 0)
		{
			$("#boveda_prestamo_tesoreria_atencion_banco_div").hide();

			alertify.error('Seleccione Tipo Tienda',5);
	        $("#boveda_prestamo_tesoreria_atencion_tipo_prestamo").focus();
	        setTimeout(function() 
	        {
	            $('#boveda_prestamo_tesoreria_atencion_tipo_prestamo').select2('open');
	        }, 200);

	        return false;
		}

		if(select_param_tipo_tienda == 9)
		{
			// RED SPORTBARS
			if(selectValor == 7)
			{
				// PRESTAMO BOVEDA
				$("#boveda_prestamo_tesoreria_atencion_banco_div").hide();
			}
			else if(selectValor == 8)
			{
				// PRESTAMO PAGO DE PREMIOS
				$("#boveda_prestamo_tesoreria_atencion_banco_div").show();
			}

		}
		else if(select_param_tipo_tienda == 1 || select_param_tipo_tienda == 16)
		{
			// RED AT
			// RED IGH
			$("#boveda_prestamo_tesoreria_atencion_banco_div").show();
		}
	});
});

$('#boveda_prestamo_tesoreria_atencion_banco').change(function () 
{
	$("#boveda_prestamo_tesoreria_atencion_banco option:selected").each(function ()
	{	
		$("#prestamo_tesoreria_atencion_div_boveda_detalle_programacion_pagos").hide();
	});
});

$('#boveda_prestamo_tesoreria_atencion_tipo_empresa').change(function () 
{
	$("#boveda_prestamo_tesoreria_atencion_tipo_empresa option:selected").each(function ()
	{	
		$("#prestamo_tesoreria_atencion_div_boveda_detalle_programacion_pagos").hide();
	});
});

$('#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta').change(function () 
{
	$("#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta option:selected").each(function ()
	{	
		$("#prestamo_tesoreria_atencion_div_boveda_detalle_programacion_pagos").hide();
	});
});


$("#btn_buscar_prestamo_boveda_pendiente_de_pago").click(function () {

	$("#div_prestamo_boveda_en_la_programacion_de_pagos").hide();
	
	var select_param_tipo_banco = $("#boveda_prestamo_tesoreria_atencion_banco").val();
	var select_param_tipo_tienda = $("#boveda_prestamo_tesoreria_atencion_tipo_tienda").val();
	var select_param_tipo_prestamo = $("#boveda_prestamo_tesoreria_atencion_tipo_prestamo").val();
	var select_param_tipo_empresa = $("#boveda_prestamo_tesoreria_atencion_tipo_empresa").val();
	var select_param_tipo_empresa_num_cuenta = $("#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta").val();

	array_boveda_programacion_de_pagos = [];
	
	if (parseInt(select_param_tipo_empresa) == 0) 
	{
		alertify.error('Seleccione la empresa',5);
		$('#boveda_prestamo_tesoreria_atencion_tipo_empresa').focus();
		$('#boveda_prestamo_tesoreria_atencion_tipo_empresa').select2('open');
		return false;
	}

	if (parseInt(select_param_tipo_empresa_num_cuenta) == 0) 
	{
		alertify.error('Seleccione el numero de cuenta',5);
		$('#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta').focus();
		$('#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta').select2('open');
		return false;
	}
	
	if (parseInt(select_param_tipo_tienda) == 0) 
	{
		alertify.error('Seleccione el tipo tienda',5);
		$('#boveda_prestamo_tesoreria_atencion_tipo_tienda').focus();
		$('#boveda_prestamo_tesoreria_atencion_tipo_tienda').select2('open');
		return false;
	}

	if (parseInt(select_param_tipo_prestamo) == 0) 
	{
		alertify.error('Seleccione el tipo préstamo',5);
		$('#boveda_prestamo_tesoreria_atencion_tipo_prestamo').focus();
		$('#boveda_prestamo_tesoreria_atencion_tipo_prestamo').select2('open');
		return false;
	}
	
	if(parseInt(select_param_tipo_tienda) == 1 || parseInt(select_param_tipo_tienda) == 16) 
	{
		// Red AT
		// Red IGH
		if (parseInt(select_param_tipo_banco) == 0) 
		{
			alertify.error('Seleccione el banco',5);
			$('#boveda_prestamo_tesoreria_atencion_banco').focus();
			$('#boveda_prestamo_tesoreria_atencion_banco').select2('open');
			return false;
		}
	}

	if(parseInt(select_param_tipo_tienda) == 9 && parseInt(select_param_tipo_prestamo) == 8) 
	{
		// select_param_tipo_tienda = 9 => Red Sportsbars
		if (parseInt(select_param_tipo_banco) == 0) 
		{
			alertify.error('Seleccione el banco',5);
			$('#boveda_prestamo_tesoreria_atencion_banco').focus();
			$('#boveda_prestamo_tesoreria_atencion_banco').select2('open');
			return false;
		}
	}
	
	prestamo_tesoreria_atencion_boveda_atencion_pagos();

});

function prestamo_tesoreria_atencion_boveda_atencion_pagos()
{
	
	var tipo_consulta = 1;
	var select_param_tipo_banco = $("#boveda_prestamo_tesoreria_atencion_banco").val();
	var select_param_tipo_tienda = $("#boveda_prestamo_tesoreria_atencion_tipo_tienda").val();
	var select_param_tipo_prestamo = $("#boveda_prestamo_tesoreria_atencion_tipo_prestamo").val();
	var select_param_tipo_empresa = $("#boveda_prestamo_tesoreria_atencion_tipo_empresa").val();
	var select_param_tipo_empresa_num_cuenta = $("#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta").val();

	var data = {
		"accion": "prestamo_tesoreria_atencion_boveda_atencion_pagos",
		"param_tipo_consulta": tipo_consulta,
		"param_tipo_banco": select_param_tipo_banco,
		"param_tipo_tienda": select_param_tipo_tienda,
		"param_tipo_prestamo": select_param_tipo_prestamo,
		"param_tipo_empresa": select_param_tipo_empresa,
		"param_tipo_empresa_num_cuenta": select_param_tipo_empresa_num_cuenta,
		"ids_prestamo_boveda": JSON.stringify(array_boveda_programacion_de_pagos)
	}

	auditoria_send({ "proceso": "prestamo_tesoreria_atencion_boveda_atencion_pagos", "data": data });

	$.ajax({
		url: "/sys/set_prestamo_tesoreria_atencion.php",
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
			auditoria_send({ "respuesta": "prestamo_tesoreria_atencion_boveda_atencion_pagos", "data": respuesta });
			
			if (parseInt(respuesta.http_code) == 400)
			{
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200)
			{
				$('#div_prestamo_boveda_pendiente_pago').html(respuesta.result);
				$("#prestamo_tesoreria_atencion_div_boveda_detalle_programacion_pagos").show();
				return false;
			}
		},
		error: function() {}
	});

	$('#boveda_prestamo_tesoreria_atencion_tipo_empresa').select2('open');
	return false;
}

function boveda_tesoreria_atencion_agregar_prestamo_a_la_programacion_pagos(prestamo_id) 
{
	$("#div_prestamo_boveda_en_la_programacion_de_pagos").show();

	if (array_boveda_programacion_de_pagos.includes(prestamo_id) === false)
	{
		array_boveda_programacion_de_pagos.push(prestamo_id)
	}
	
	prestamo_tesoreria_atencion_actualizar_boveda_programacion();
	prestamo_tesoreria_atencion_boveda_atencion_pagos();
}

function boveda_tesoreria_atencion_prestamo_agregar_varios_a_la_programacion(...prestamo_id) 
{
	$("#div_prestamo_boveda_en_la_programacion_de_pagos").show();

    var i;
    for(i = 0; i < prestamo_id.length; i++)
    {
		if (array_boveda_programacion_de_pagos.includes(prestamo_id[i]) === false)
		{
			array_boveda_programacion_de_pagos.push(prestamo_id[i])
		}
    }
    prestamo_tesoreria_atencion_actualizar_boveda_programacion();
	prestamo_tesoreria_atencion_boveda_atencion_pagos();
}

function boveda_tesoreria_atencion_quitar_prestamo_de_la_programacion(prestamo_id)
{
	
	const index = array_boveda_programacion_de_pagos.indexOf(prestamo_id);
	if (index > -1) 
	{
		array_boveda_programacion_de_pagos.splice(index, 1);
	}
	
	prestamo_tesoreria_atencion_actualizar_boveda_programacion();
	prestamo_tesoreria_atencion_boveda_atencion_pagos();
}

function boveda_tesoreria_atencion_prestamo_quitar_varios_a_la_programacion(...prestamo_id) 
{
    var i;
    for(i = 0; i < prestamo_id.length; i++)
    {
		const index = array_boveda_programacion_de_pagos.indexOf(prestamo_id[i]);
		if (index > -1) 
		{
			array_boveda_programacion_de_pagos.splice(index, 1);
		}
		
    }
    prestamo_tesoreria_atencion_actualizar_boveda_programacion();
	prestamo_tesoreria_atencion_boveda_atencion_pagos();
}

function prestamo_tesoreria_atencion_actualizar_boveda_programacion()
{
	
	var tipo_consulta = 2;
	var select_param_tipo_tienda = $("#boveda_prestamo_tesoreria_atencion_tipo_tienda").val();
	var select_param_tipo_prestamo = $("#boveda_prestamo_tesoreria_atencion_tipo_prestamo").val();
	var select_param_tipo_banco = $('#boveda_prestamo_tesoreria_atencion_banco').val();
	var select_param_tipo_empresa = $('#boveda_prestamo_tesoreria_atencion_tipo_empresa').val();

	var data = {
		"accion": "prestamo_tesoreria_atencion_boveda_atencion_pagos",
		"param_tipo_consulta": tipo_consulta,
		"param_tipo_tienda": select_param_tipo_tienda,
		"param_tipo_prestamo": select_param_tipo_prestamo,
		"param_tipo_banco": select_param_tipo_banco,
		"param_tipo_empresa": select_param_tipo_empresa,
		"ids_prestamo_boveda": JSON.stringify(array_boveda_programacion_de_pagos)
	}

	auditoria_send({ "proceso": "prestamo_tesoreria_atencion_boveda_atencion_pagos", "data": data });
	$.ajax({
		url: "/sys/set_prestamo_tesoreria_atencion.php",
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
			
			if (parseInt(respuesta.http_code) == 400)
			{
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			
			if (parseInt(respuesta.http_code) == 200)
			{
				$('#div_prestamo_boveda_en_la_programacion_de_pagos').html(respuesta.result);
				return false;
			}
		},
		error: function() {}
	});
}

function prestamo_tesoreria_atencion_boveda_guardar_programacion($num_tipo_grabacion)
{	
	
	var accion = '';
	var programacion_id_edit = $('#programacion_id_edit').val();
	var select_param_tipo_tienda = $("#boveda_prestamo_tesoreria_atencion_tipo_tienda").val();
	var select_param_tipo_prestamo = $("#boveda_prestamo_tesoreria_atencion_tipo_prestamo").val();
	var select_param_tipo_banco = $("#boveda_prestamo_tesoreria_atencion_banco").val();
	var select_param_tipo_empresa = $("#boveda_prestamo_tesoreria_atencion_tipo_empresa").val();
	var select_param_tipo_empresa_num_cuenta = $("#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta").val();

	var txt_titulo_pregunta = "";

	if(parseInt(select_param_tipo_tienda) == 0)
	{
		alertify.error('Seleccione el tipo Tienda',5);
		$('#boveda_prestamo_tesoreria_atencion_tipo_tienda').focus();
		$('#boveda_prestamo_tesoreria_atencion_tipo_tienda').select2('open');
		return false;
	}
	else if(parseInt(select_param_tipo_tienda) == 6)
	{
		if (parseInt(select_param_tipo_banco) == 0) 
		{
			alertify.error('Seleccione el Banco',5);
			$('#boveda_prestamo_tesoreria_atencion_banco').focus();
			$('#boveda_prestamo_tesoreria_atencion_banco').select2('open');
			return false;
		}
	}

	if(parseInt(select_param_tipo_prestamo) == 0)
	{
		alertify.error('Seleccione el tipo Préstamo',5);
		$('#boveda_prestamo_tesoreria_atencion_tipo_prestamo').focus();
		$('#boveda_prestamo_tesoreria_atencion_tipo_prestamo').select2('open');
		return false;
	}

	if (parseInt(select_param_tipo_empresa) == 0) 
	{
		alertify.error('Seleccione la Empresa',5);
		$('#boveda_prestamo_tesoreria_atencion_tipo_empresa').focus();
		$('#boveda_prestamo_tesoreria_atencion_tipo_empresa').select2('open');
		return false;
	}

	if (parseInt(select_param_tipo_empresa_num_cuenta) == 0) 
	{
		alertify.error('Seleccione el número de cuenta',5);
		$('#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta').focus();
		$('#boveda_prestamo_tesoreria_atencion_empresa_num_cuenta').select2('open');
		return false;
	}

	if (array_boveda_programacion_de_pagos.length == 0)
	{
		alertify.error('Tiene que agregar al menos un registro en la programación',5);
		return false;
	}

	if ($num_tipo_grabacion == '1') 
	{
		txt_titulo_pregunta = "Guardar";
		accion = 'tesoreria_guardar_prestamo_boveda_programacion_de_pago';
	} 
	else if ($num_tipo_grabacion == '2') 
	{
		txt_titulo_pregunta = "Editar";
		accion = 'tesoreria_atencion_guardar_prestamo_boveda_cambios_programacion_de_pago';
	}
	else
	{
		alertify.error('¡Ups, ocurrio un error, vuelve a refrescar la pagina!',5);
		return false;
	}

	swal(
	{
		title: '¿Está seguro de '+txt_titulo_pregunta+' la Programación?',
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
				"accion": accion,
				"param_tipo_tienda": select_param_tipo_tienda,
				"param_tipo_prestamo": select_param_tipo_prestamo,
				"param_tipo_banco": select_param_tipo_banco,
				"param_tipo_empresa": select_param_tipo_empresa,
				"param_tipo_empresa_num_cuenta": select_param_tipo_empresa_num_cuenta,
				"ids_prestamo": JSON.stringify(array_boveda_programacion_de_pagos),
				"programacion_id_edit": programacion_id_edit
			}

			auditoria_send({ "proceso": "guardar_programacion_de_pago_prestamo_boveda", "data": data });

			$.ajax({
				url: "/sys/set_prestamo_tesoreria_atencion.php",
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
					
					if(parseInt(respuesta.http_code) == 400)
					{
						swal({
							title: "Error al guardar programación de pagos.",
							text: respuesta.result,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
					else if(parseInt(respuesta.http_code) == 200)
					{
						swal({
							title: "Registro exitoso",
							text: "La programación de pago fue registrada exitosamente",
							html:true,
							type: "success",
							timer: 3000,
							closeOnConfirm: false,
							showCancelButton: false
						});
						setTimeout(function() {
							window.location.href = "?sec_id=prestamo&sub_sec_id=tesoreria";
							return false;
						}, 3000);
						return false;
					}
				},
				error: function() {}
			});

		}
	});
}
