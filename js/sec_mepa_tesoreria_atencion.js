// INICIO DECLARACION DE VARIABLES ARRAY
var array_asignacion_programacion_de_pagos = [];
var array_liquidacion_programacion_de_pagos = [];
var array_aumento_asignacion_programacion_de_pagos = [];
// FIN DECLARACION DE VARIABLES ARRAY

function sec_mepa_tesoreria_atencion()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_tesoreria_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	$("#btn_buscar_caja_chica_pendiente_de_pago").click(function () {

		$("#div_caja_chica_asignacion_en_la_programacion_de_pagos").hide();
		$("#div_caja_chica_liquidacion_en_la_programacion_de_pagos").hide();

		var select_param_tipo_caja_chica = $("#mepa_tesoreria_atencion_param_tipo_caja_chica").val();
		var select_param_tipo_banco = $("#mepa_tesoreria_atencion_param_tipo_banco").val();
		var select_param_tipo_empresa = $("#mepa_tesoreria_atencion_param_tipo_empresa").val();
		var select_param_tipo_empresa_num_cuenta = $("#mepa_tesoreria_atencion_param_empresa_num_cuenta").val();

		array_asignacion_programacion_de_pagos = [];
		array_liquidacion_programacion_de_pagos = [];
		var array_aumento_asignacion_programacion_de_pagos = [];
		
		if (parseInt(select_param_tipo_caja_chica) == 0) 
		{
			alertify.error('Seleccione el tipo',5);
			$('#mepa_tesoreria_atencion_param_tipo_caja_chica').focus();
			$('#mepa_tesoreria_atencion_param_tipo_caja_chica').select2('open');
			return false;
		}
		else if (parseInt(select_param_tipo_banco) == 0) 
		{
			alertify.error('Seleccione el banco',5);
			$('#mepa_tesoreria_atencion_param_tipo_banco').focus();
			$('#mepa_tesoreria_atencion_param_tipo_banco').select2('open');
			return false;
		}
		else if (parseInt(select_param_tipo_empresa) == 0) 
		{
			alertify.error('Seleccione la empresa',5);
			$('#mepa_tesoreria_atencion_param_tipo_empresa').focus();
			$('#mepa_tesoreria_atencion_param_tipo_empresa').select2('open');
			return false;
		}
		else if (parseInt(select_param_tipo_empresa_num_cuenta) == 0) 
		{
			alertify.error('Seleccione el numero de cuenta',5);
			$('#mepa_tesoreria_atencion_param_empresa_num_cuenta').focus();
			$('#mepa_tesoreria_atencion_param_empresa_num_cuenta').select2('open');
			return false;
		}
		else if(select_param_tipo_caja_chica == "1")
		{
			// SOLCITUD ASIGNACION CAJA CHICA
			mepa_tesoreria_atencion_asignacion_caja_chica_atencion_pagos();
		}
		else if (select_param_tipo_caja_chica == '2') 
		{
			// SOLCITUD LIQUIDACION CAJA CHICA
			mepa_tesoreria_atencion_liquidacion_caja_chica_atencion_pagos();
		}
		else if (select_param_tipo_caja_chica == '9') 
		{
			// AUMENTO DE FONDO - ASIGNACION
			mepa_tesoreria_atencion_aumento_asignacion_caja_chica_atencion_pagos();
		}
	});
}

$('#mepa_tesoreria_atencion_param_tipo_caja_chica').change(function () 
{
	$("#mepa_tesoreria_atencion_param_tipo_caja_chica option:selected").each(function ()
	{	
		$("#mepa_tesoreria_div_asignacion_detalle_programacion_pagos").hide();
		$("#mepa_tesoreria_div_liquidacion_detalle_programacion_pagos").hide();
		$("#mepa_tesoreria_div_aumento_asignacion_detalle_programacion_pagos").hide();
	});
});

$('#mepa_tesoreria_atencion_param_tipo_banco').change(function () 
{
	$("#mepa_tesoreria_atencion_param_tipo_banco option:selected").each(function ()
	{	
		$("#mepa_tesoreria_div_asignacion_detalle_programacion_pagos").hide();
		$("#mepa_tesoreria_div_liquidacion_detalle_programacion_pagos").hide();
		$("#mepa_tesoreria_div_aumento_asignacion_detalle_programacion_pagos").hide();
	});
});

$('#mepa_tesoreria_atencion_param_tipo_empresa').change(function () 
{
	$("#mepa_tesoreria_atencion_param_tipo_empresa option:selected").each(function ()
	{	
		$("#mepa_tesoreria_div_asignacion_detalle_programacion_pagos").hide();
		$("#mepa_tesoreria_div_liquidacion_detalle_programacion_pagos").hide();
		$("#mepa_tesoreria_div_aumento_asignacion_detalle_programacion_pagos").hide();

		var selectValor = $(this).val();

		mepa_tesoreria_atencion_obtener_empresa_numero_cuenta(selectValor);
	});
});

$('#mepa_tesoreria_atencion_param_empresa_num_cuenta').change(function () 
{
	$("#mepa_tesoreria_atencion_param_empresa_num_cuenta option:selected").each(function ()
	{	
		$("#mepa_tesoreria_div_asignacion_detalle_programacion_pagos").hide();
		$("#mepa_tesoreria_div_liquidacion_detalle_programacion_pagos").hide();
		$("#mepa_tesoreria_div_aumento_asignacion_detalle_programacion_pagos").hide();
	});
});

function mepa_tesoreria_atencion_obtener_empresa_numero_cuenta(empresa) 
{	
	var data = {
		"accion": "mepa_tesoreria_atencion_obtener_empresa_numero_cuenta",
		"empresa": empresa
	}
	
	var array_empresa_num_cuenta = [];
	
	$.ajax({
		url: "/sys/set_mepa_tesoreria_atencion.php",
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
				$("#mepa_tesoreria_atencion_param_empresa_num_cuenta").html(html).trigger("change");

				setTimeout(function() {
					$('#mepa_tesoreria_atencion_param_empresa_num_cuenta').select2('open');
				}, 500);

				return false;

			}
			
			if (parseInt(respuesta.http_code) == 200) 
			{
				array_empresa_num_cuenta.push(respuesta.result);
			
				var html = '<option value="0">-- Seleccione --</option>';

				for (var i = 0; i < array_empresa_num_cuenta[0].length; i++) 
				{
					html += '<option value=' + array_empresa_num_cuenta[0][i].id  + '>' + array_empresa_num_cuenta[0][i].nombre + '</option>';
				}

				$("#mepa_tesoreria_atencion_param_empresa_num_cuenta").html(html).trigger("change");

				setTimeout(function() {
					$('#mepa_tesoreria_atencion_param_empresa_num_cuenta').select2('open');
				}, 500);
				
				return false;
			}
		},
		error: function() {}
	});
};

function mepa_tesoreria_atencion_asignacion_caja_chica_atencion_pagos()
{
	
	var tipo_consulta = 1;
	var mepa_tesoreria_atencion_param_tipo_caja_chica = 1;
	var mepa_tesoreria_atencion_param_tipo_caja_chica_texto = $('#mepa_tesoreria_atencion_param_tipo_caja_chica').find('option:selected').text();
	var mepa_tesoreria_atencion_param_tipo_banco = $('#mepa_tesoreria_atencion_param_tipo_banco').val();
	var mepa_tesoreria_atencion_param_tipo_empresa = $('#mepa_tesoreria_atencion_param_tipo_empresa').val();

	var data = {
		"accion": "mepa_asignacion_caja_chica_pendientes_de_pago",
		"tipo_consulta": tipo_consulta,
		"mepa_tesoreria_atencion_param_tipo_caja_chica": mepa_tesoreria_atencion_param_tipo_caja_chica,
		"mepa_tesoreria_atencion_param_tipo_caja_chica_texto": mepa_tesoreria_atencion_param_tipo_caja_chica_texto,
		"mepa_tesoreria_atencion_param_tipo_banco": mepa_tesoreria_atencion_param_tipo_banco,
		"mepa_tesoreria_atencion_param_tipo_empresa": mepa_tesoreria_atencion_param_tipo_empresa,
		"ids_asignacion": JSON.stringify(array_asignacion_programacion_de_pagos)
	}

	auditoria_send({ "proceso": "mepa_asignacion_caja_chica_pendientes_de_pago", "data": data });

	$.ajax({
		url: "/sys/set_mepa_tesoreria_atencion.php",
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
			auditoria_send({ "respuesta": "mepa_asignacion_caja_chica_pendientes_de_pago", "data": respuesta });
			
			if (parseInt(respuesta.http_code) == 400)
			{
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200)
			{
				$('#div_caja_chica_asignacion_pendiente_pago').html(respuesta.result);
				$("#mepa_tesoreria_div_asignacion_detalle_programacion_pagos").show();
				$("#mepa_tesoreria_div_liquidacion_detalle_programacion_pagos").hide();
				return false;
			}
		},
		error: function() {}
	});
}

function mepa_tesoreria_atencion_agregar_asignacion_a_la_programacion_pagos(asignacion_id) 
{
	$("#div_caja_chica_asignacion_en_la_programacion_de_pagos").show();

	if (array_asignacion_programacion_de_pagos.includes(asignacion_id) === false)
	{
		array_asignacion_programacion_de_pagos.push(asignacion_id)
	}
	
	mepa_tesoreria_atencion_actualizar_tabla_asignacion_programacion();
	mepa_tesoreria_atencion_asignacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_asignacion_agregar_varios_a_la_programacion(...asignacion_ids) 
{
	$("#div_caja_chica_asignacion_en_la_programacion_de_pagos").show();

    var i;
    for(i = 0; i < asignacion_ids.length; i++)
    {
		if (array_asignacion_programacion_de_pagos.includes(asignacion_ids[i]) === false)
		{
			array_asignacion_programacion_de_pagos.push(asignacion_ids[i])
		}
		
    }
    mepa_tesoreria_atencion_actualizar_tabla_asignacion_programacion();
	mepa_tesoreria_atencion_asignacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_quitar_asignacion_de_la_programacion(asignacion_id)
{
	
	const index = array_asignacion_programacion_de_pagos.indexOf(asignacion_id);
	if (index > -1) 
	{
		array_asignacion_programacion_de_pagos.splice(index, 1);
	}
	
	mepa_tesoreria_atencion_actualizar_tabla_asignacion_programacion();
	mepa_tesoreria_atencion_asignacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_asignacion_quitar_varios_a_la_programacion(...asignacion_id) 
{
    var i;
    for(i = 0; i < asignacion_id.length; i++)
    {
		const index = array_asignacion_programacion_de_pagos.indexOf(asignacion_id[i]);
		if (index > -1) 
		{
			array_asignacion_programacion_de_pagos.splice(index, 1);
		}
		
    }
    mepa_tesoreria_atencion_actualizar_tabla_asignacion_programacion();
	mepa_tesoreria_atencion_asignacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_actualizar_tabla_asignacion_programacion()
{
	
	var tipo_consulta = 2;
	var mepa_tesoreria_atencion_param_tipo_caja_chica = 1;
	var mepa_tesoreria_atencion_param_tipo_caja_chica_texto = $('#mepa_tesoreria_atencion_param_tipo_caja_chica').find('option:selected').text();
	var mepa_tesoreria_atencion_param_tipo_banco = $('#mepa_tesoreria_atencion_param_tipo_banco').val();
	var mepa_tesoreria_atencion_param_tipo_empresa = $('#mepa_tesoreria_atencion_param_tipo_empresa').val();

	var data = {
		"accion": "mepa_asignacion_caja_chica_pendientes_de_pago",
		"tipo_consulta": tipo_consulta,
		"mepa_tesoreria_atencion_param_tipo_caja_chica": mepa_tesoreria_atencion_param_tipo_caja_chica,
		"mepa_tesoreria_atencion_param_tipo_caja_chica_texto": mepa_tesoreria_atencion_param_tipo_caja_chica_texto,
		"mepa_tesoreria_atencion_param_tipo_banco": mepa_tesoreria_atencion_param_tipo_banco,
		"mepa_tesoreria_atencion_param_tipo_empresa": mepa_tesoreria_atencion_param_tipo_empresa,
		"ids_asignacion": JSON.stringify(array_asignacion_programacion_de_pagos)
	}

	auditoria_send({ "proceso": "mepa_asignacion_caja_chica_pendientes_de_pago", "data": data });
	$.ajax({
		url: "/sys/set_mepa_tesoreria_atencion.php",
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
			
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}
			
			if (parseInt(respuesta.http_code) == 200) {
				$('#div_caja_chica_asignacion_en_la_programacion_de_pagos').html(respuesta.result);
				return false;
			}
		},
		error: function() {}
	});
}

function mepa_tesoreria_atencion_asignacion_guardar_programacion($num_tipo_grabacion)
{	
	
	$accion = '';
	var programacion_id_edit = $('#programacion_id_edit').val();
	var param_tipo_caja_chica = $('#mepa_tesoreria_atencion_param_tipo_caja_chica').val();
	var param_tipo_banco = $('#mepa_tesoreria_atencion_param_tipo_banco').val();
	var param_tipo_empresa = $('#mepa_tesoreria_atencion_param_tipo_empresa').val();
	var param_tipo_empresa_num_cuenta = $('#mepa_tesoreria_atencion_param_empresa_num_cuenta').val();

	var txt_titulo_pregunta = "";

	if (parseInt(param_tipo_caja_chica) == 0) 
	{
		alertify.error('Seleccione el tipo',5);
		$('#mepa_tesoreria_atencion_param_tipo_caja_chica').focus();
		$('#mepa_tesoreria_atencion_param_tipo_caja_chica').select2('open');
		return false;
	}

	if (parseInt(param_tipo_banco) == 0) 
	{
		alertify.error('Seleccione el Banco',5);
		$('#mepa_tesoreria_atencion_param_tipo_banco').focus();
		$('#mepa_tesoreria_atencion_param_tipo_banco').select2('open');
		return false;
	}

	if (parseInt(param_tipo_empresa_num_cuenta) == 0) 
	{
		alertify.error('Seleccione la Empresa',5);
		$('#mepa_tesoreria_atencion_param_empresa_num_cuenta').focus();
		$('#mepa_tesoreria_atencion_param_empresa_num_cuenta').select2('open');
		return false;
	}

	if (array_asignacion_programacion_de_pagos.length == 0) 
	{
		alertify.error('Tiene que agregar al menos un registro en la programación',5);
		return false;
	}

	if ($num_tipo_grabacion == '1') 
	{
		txt_titulo_pregunta = "Guardar";
		$accion = 'mepa_tesoreria_atencion_guardar_asignacion_programacion_de_pago';
	} 
	else if ($num_tipo_grabacion == '2') 
	{
		txt_titulo_pregunta = "Editar";
		$accion = 'mepa_tesoreria_atencion_guardar_asignacion_cambios_programacion_de_pago';
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
				"accion": $accion,
				"param_tipo_caja_chica": param_tipo_caja_chica,
				"param_tipo_banco": param_tipo_banco,
				"param_tipo_empresa": param_tipo_empresa,
				"param_tipo_empresa_num_cuenta": param_tipo_empresa_num_cuenta,
				"ids_asignacion": JSON.stringify(array_asignacion_programacion_de_pagos),
				"programacion_id_edit": programacion_id_edit
			}

			auditoria_send({ "proceso": "guardar_programacion_de_pago_asignacion", "data": data });

			$.ajax({
				url: "/sys/set_mepa_tesoreria_atencion.php",
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
					
					if (parseInt(respuesta.http_code) == 400) {
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
					
					if (parseInt(respuesta.http_code) == 200) {
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
							window.location.href = "?sec_id=mepa&sub_sec_id=tesoreria";
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

function mepa_tesoreria_atencion_liquidacion_caja_chica_atencion_pagos()
{
	
	var tipo_consulta = 1;
	var mepa_tesoreria_atencion_param_tipo_caja_chica = 2;
	var mepa_tesoreria_atencion_param_tipo_caja_chica_texto = $('#mepa_tesoreria_atencion_param_tipo_caja_chica').find('option:selected').text();
	var mepa_tesoreria_atencion_param_tipo_banco = $('#mepa_tesoreria_atencion_param_tipo_banco').val();
	var mepa_tesoreria_atencion_param_tipo_empresa = $('#mepa_tesoreria_atencion_param_tipo_empresa').val();

	var data = {
		"accion": "mepa_liquidacion_caja_chica_pendientes_de_pago",
		"tipo_consulta": tipo_consulta,
		"mepa_tesoreria_atencion_param_tipo_caja_chica": mepa_tesoreria_atencion_param_tipo_caja_chica,
		"mepa_tesoreria_atencion_param_tipo_caja_chica_texto": mepa_tesoreria_atencion_param_tipo_caja_chica_texto,
		"mepa_tesoreria_atencion_param_tipo_banco": mepa_tesoreria_atencion_param_tipo_banco,
		"mepa_tesoreria_atencion_param_tipo_empresa": mepa_tesoreria_atencion_param_tipo_empresa,
		"ids_liquidacion": JSON.stringify(array_liquidacion_programacion_de_pagos)
	}

	auditoria_send({ "proceso": "mepa_liquidacion_caja_chica_pendientes_de_pago", "data": data });

	$.ajax({
		url: "/sys/set_mepa_tesoreria_atencion.php",
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
			auditoria_send({ "respuesta": "mepa_liquidacion_caja_chica_pendientes_de_pago", "data": respuesta });
			
			if (parseInt(respuesta.http_code) == 400)
			{
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200)
			{
				$('#div_caja_chica_liquidacion_pendiente_pago').html(respuesta.result);
				$("#mepa_tesoreria_div_liquidacion_detalle_programacion_pagos").show();
				$("#mepa_tesoreria_div_asignacion_detalle_programacion_pagos").hide();
				
				return false;
			}
		},
		error: function() {}
	});
}

function mepa_tesoreria_atencion_agregar_liquidacion_a_la_programacion_pagos(liquidacion_id) 
{
	$("#div_caja_chica_liquidacion_en_la_programacion_de_pagos").show();

	if (array_liquidacion_programacion_de_pagos.includes(liquidacion_id) === false)
	{
		array_liquidacion_programacion_de_pagos.push(liquidacion_id)
	}
	
	mepa_tesoreria_atencion_actualizar_tabla_liquidacion_programacion();
	mepa_tesoreria_atencion_liquidacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_quitar_liquidacion_de_la_programacion(liquidacion_id)
{
	const index = array_liquidacion_programacion_de_pagos.indexOf(liquidacion_id);
	if (index > -1) 
	{
		array_liquidacion_programacion_de_pagos.splice(index, 1);
	}
	
	mepa_tesoreria_atencion_actualizar_tabla_liquidacion_programacion();
	mepa_tesoreria_atencion_liquidacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_liquidacion_agregar_varios_a_la_programacion(...liquidacion_id) 
{

	$("#div_caja_chica_liquidacion_en_la_programacion_de_pagos").show();

    var i;
    for(i = 0; i < liquidacion_id.length; i++)
    {
		if (array_liquidacion_programacion_de_pagos.includes(liquidacion_id[i]) === false)
		{
			array_liquidacion_programacion_de_pagos.push(liquidacion_id[i])
		}

    }
    mepa_tesoreria_atencion_actualizar_tabla_liquidacion_programacion();
	mepa_tesoreria_atencion_liquidacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_liquidacion_quitar_varios_a_la_programacion(...liquidacion_id) 
{
    var i;
    for(i = 0; i < liquidacion_id.length; i++)
    {
		const index = array_liquidacion_programacion_de_pagos.indexOf(liquidacion_id[i]);
		if (index > -1) 
		{
			array_liquidacion_programacion_de_pagos.splice(index, 1);
		}
		
    }
    mepa_tesoreria_atencion_actualizar_tabla_liquidacion_programacion();
	mepa_tesoreria_atencion_liquidacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_actualizar_tabla_liquidacion_programacion()
{
	var tipo_consulta = 2;
	var mepa_tesoreria_atencion_param_tipo_caja_chica = 1;
	var mepa_tesoreria_atencion_param_tipo_caja_chica_texto = $('#mepa_tesoreria_atencion_param_tipo_caja_chica').find('option:selected').text();
	var mepa_tesoreria_atencion_param_tipo_banco = $('#mepa_tesoreria_atencion_param_tipo_banco').val();
	var mepa_tesoreria_atencion_param_tipo_empresa = $('#mepa_tesoreria_atencion_param_tipo_empresa').val();

	var data = {
		"accion": "mepa_liquidacion_caja_chica_pendientes_de_pago",
		"tipo_consulta": tipo_consulta,
		"mepa_tesoreria_atencion_param_tipo_caja_chica": mepa_tesoreria_atencion_param_tipo_caja_chica,
		"mepa_tesoreria_atencion_param_tipo_caja_chica_texto": mepa_tesoreria_atencion_param_tipo_caja_chica_texto,
		"mepa_tesoreria_atencion_param_tipo_banco": mepa_tesoreria_atencion_param_tipo_banco,
		"mepa_tesoreria_atencion_param_tipo_empresa": mepa_tesoreria_atencion_param_tipo_empresa,
		"ids_liquidacion": JSON.stringify(array_liquidacion_programacion_de_pagos)
	}

	auditoria_send({ "proceso": "mepa_liquidacion_caja_chica_pendientes_de_pago", "data": data });
	$.ajax({
		url: "/sys/set_mepa_tesoreria_atencion.php",
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
			
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}
			
			if (parseInt(respuesta.http_code) == 200) {
				$('#div_caja_chica_liquidacion_en_la_programacion_de_pagos').html(respuesta.result);
				return false;
			}
		},
		error: function() {}
	});
}

function mepa_tesoreria_atencion_liquidacion_guardar_programacion($num_tipo_grabacion)
{	
	
	$accion = '';
	var programacion_id_edit = $('#programacion_id_edit').val();
	var param_tipo_caja_chica = $('#mepa_tesoreria_atencion_param_tipo_caja_chica').val();
	var param_tipo_banco = $('#mepa_tesoreria_atencion_param_tipo_banco').val();
	var param_tipo_empresa = $('#mepa_tesoreria_atencion_param_tipo_empresa').val();
	var param_tipo_empresa_num_cuenta = $('#mepa_tesoreria_atencion_param_empresa_num_cuenta').val();

	var txt_titulo_pregunta = "";

	if (parseInt(param_tipo_caja_chica) == 0) 
	{
		alertify.error('Seleccione el tipo',5);
		$('#mepa_tesoreria_atencion_param_tipo_caja_chica').focus();
		$('#mepa_tesoreria_atencion_param_tipo_caja_chica').select2('open');
		return false;
	}

	if (parseInt(param_tipo_banco) == 0) 
	{
		alertify.error('Seleccione el Banco',5);
		$('#mepa_tesoreria_atencion_param_tipo_banco').focus();
		$('#mepa_tesoreria_atencion_param_tipo_banco').select2('open');
		return false;
	}

	if (parseInt(param_tipo_empresa) == 0) 
	{
		alertify.error('Seleccione la Empresa',5);
		$('#mepa_tesoreria_atencion_param_tipo_empresa').focus();
		$('#mepa_tesoreria_atencion_param_tipo_empresa').select2('open');
		return false;
	}

	if (parseInt(param_tipo_empresa_num_cuenta) == 0) 
	{
		alertify.error('Seleccione la Empresa',5);
		$('#mepa_tesoreria_atencion_param_empresa_num_cuenta').focus();
		$('#mepa_tesoreria_atencion_param_empresa_num_cuenta').select2('open');
		return false;
	}
	
	if (array_liquidacion_programacion_de_pagos.length == 0) 
	{
		alertify.error('Tiene que agregar al menos un registro en la programación',5);
		return false;
	}

	if ($num_tipo_grabacion == '1') 
	{
		txt_titulo_pregunta = "Guardar";
		$accion = 'mepa_tesoreria_atencion_guardar_liquidacion_programacion_de_pago';
	} 
	else if ($num_tipo_grabacion == '2') 
	{
		txt_titulo_pregunta = "Editar";
		$accion = 'mepa_tesoreria_atencion_guardar_liquidacion_cambios_programacion_de_pago';
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
				"accion": $accion,
				"param_tipo_caja_chica": param_tipo_caja_chica,
				"param_tipo_banco": param_tipo_banco,
				"param_tipo_empresa": param_tipo_empresa,
				"param_tipo_empresa_num_cuenta": param_tipo_empresa_num_cuenta,
				"ids_liquidacion": JSON.stringify(array_liquidacion_programacion_de_pagos),
				"programacion_id_edit": programacion_id_edit
			}

			auditoria_send({ "proceso": "guardar_programacion_de_pago", "data": data });

			$.ajax({
				url: "/sys/set_mepa_tesoreria_atencion.php",
				type: 'POST',
				data: data,
				beforeSend: function() {
					loading("true");
				},
				complete: function() {
					loading();
				},
				success: function(resp) { //  alert(datat)
					
					var respuesta = JSON.parse(resp);
					
					if (parseInt(respuesta.http_code) == 400) {
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
					
					if (parseInt(respuesta.http_code) == 200) {
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
							window.location.href = "?sec_id=mepa&sub_sec_id=tesoreria";
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

function mepa_tesoreria_atencion_aumento_asignacion_caja_chica_atencion_pagos()
{
	
	var tipo_consulta = 1;
	var mepa_tesoreria_atencion_param_tipo_caja_chica = 9;
	var mepa_tesoreria_atencion_param_tipo_caja_chica_texto = $('#mepa_tesoreria_atencion_param_tipo_caja_chica').find('option:selected').text();
	var mepa_tesoreria_atencion_param_tipo_banco = $('#mepa_tesoreria_atencion_param_tipo_banco').val();
	var mepa_tesoreria_atencion_param_tipo_empresa = $('#mepa_tesoreria_atencion_param_tipo_empresa').val();

	var data = {
		"accion": "mepa_aumento_asignacion_caja_chica_pendientes_de_pago",
		"tipo_consulta": tipo_consulta,
		"mepa_tesoreria_atencion_param_tipo_caja_chica": mepa_tesoreria_atencion_param_tipo_caja_chica,
		"mepa_tesoreria_atencion_param_tipo_caja_chica_texto": mepa_tesoreria_atencion_param_tipo_caja_chica_texto,
		"mepa_tesoreria_atencion_param_tipo_banco": mepa_tesoreria_atencion_param_tipo_banco,
		"mepa_tesoreria_atencion_param_tipo_empresa": mepa_tesoreria_atencion_param_tipo_empresa,
		"ids_aumento_asignacion": JSON.stringify(array_aumento_asignacion_programacion_de_pagos)
	}

	auditoria_send({ "proceso": "mepa_aumento_asignacion_caja_chica_pendientes_de_pago", "data": data });

	$.ajax({
		url: "/sys/set_mepa_tesoreria_atencion.php",
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
			auditoria_send({ "respuesta": "mepa_aumento_asignacion_caja_chica_pendientes_de_pago", "data": respuesta });
			
			if (parseInt(respuesta.http_code) == 400)
			{
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200)
			{
				$('#div_caja_chica_aumento_asignacion_pendiente_pago').html(respuesta.result);
				$("#mepa_tesoreria_div_aumento_asignacion_detalle_programacion_pagos").show();
				$("#mepa_tesoreria_div_asignacion_detalle_programacion_pagos").hide();
				$("#mepa_tesoreria_div_liquidacion_detalle_programacion_pagos").hide();
				return false;
			}
		},
		error: function() {}
	});
}

function mepa_tesoreria_atencion_agregar_aumento_asignacion_a_la_programacion_pagos(aumento_id) 
{
	$("#div_caja_chica_aumento_asignacion_en_la_programacion_de_pagos").show();

	if (array_aumento_asignacion_programacion_de_pagos.includes(aumento_id) === false)
	{
		array_aumento_asignacion_programacion_de_pagos.push(aumento_id)
	}
	
	mepa_tesoreria_atencion_actualizar_tabla_aumento_asignacion_programacion();
	mepa_tesoreria_atencion_aumento_asignacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_aumento_asignacion_agregar_varios_a_la_programacion(...aumento_ids) 
{
	$("#div_caja_chica_aumento_asignacion_en_la_programacion_de_pagos").show();

    var i;
    for(i = 0; i < aumento_ids.length; i++)
    {
		if (array_aumento_asignacion_programacion_de_pagos.includes(aumento_ids[i]) === false)
		{
			array_aumento_asignacion_programacion_de_pagos.push(aumento_ids[i])
		}
		
    }
    mepa_tesoreria_atencion_actualizar_tabla_aumento_asignacion_programacion();
	mepa_tesoreria_atencion_aumento_asignacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_quitar_aumento_asignacion_de_la_programacion(aumento_id)
{
	
	const index = array_aumento_asignacion_programacion_de_pagos.indexOf(aumento_id);
	if (index > -1) 
	{
		array_aumento_asignacion_programacion_de_pagos.splice(index, 1);
	}
	
	mepa_tesoreria_atencion_actualizar_tabla_aumento_asignacion_programacion();
	mepa_tesoreria_atencion_aumento_asignacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_aumento_asignacion_quitar_varios_a_la_programacion(...aumento_ids) 
{
    var i;
    for(i = 0; i < aumento_ids.length; i++)
    {
		const index = array_aumento_asignacion_programacion_de_pagos.indexOf(aumento_ids[i]);
		if (index > -1) 
		{
			array_aumento_asignacion_programacion_de_pagos.splice(index, 1);
		}
		
    }
    mepa_tesoreria_atencion_actualizar_tabla_aumento_asignacion_programacion();
	mepa_tesoreria_atencion_aumento_asignacion_caja_chica_atencion_pagos();
}

function mepa_tesoreria_atencion_actualizar_tabla_aumento_asignacion_programacion()
{
	
	var tipo_consulta = 2;
	var mepa_tesoreria_atencion_param_tipo_caja_chica = 9;
	var mepa_tesoreria_atencion_param_tipo_caja_chica_texto = $('#mepa_tesoreria_atencion_param_tipo_caja_chica').find('option:selected').text();
	var mepa_tesoreria_atencion_param_tipo_banco = $('#mepa_tesoreria_atencion_param_tipo_banco').val();
	var mepa_tesoreria_atencion_param_tipo_empresa = $('#mepa_tesoreria_atencion_param_tipo_empresa').val();

	var data = {
		"accion": "mepa_aumento_asignacion_caja_chica_pendientes_de_pago",
		"tipo_consulta": tipo_consulta,
		"mepa_tesoreria_atencion_param_tipo_caja_chica": mepa_tesoreria_atencion_param_tipo_caja_chica,
		"mepa_tesoreria_atencion_param_tipo_caja_chica_texto": mepa_tesoreria_atencion_param_tipo_caja_chica_texto,
		"mepa_tesoreria_atencion_param_tipo_banco": mepa_tesoreria_atencion_param_tipo_banco,
		"mepa_tesoreria_atencion_param_tipo_empresa": mepa_tesoreria_atencion_param_tipo_empresa,
		"ids_aumento_asignacion": JSON.stringify(array_aumento_asignacion_programacion_de_pagos)
	}

	auditoria_send({ "proceso": "mepa_aumento_asignacion_caja_chica_pendientes_de_pago", "data": data });
	$.ajax({
		url: "/sys/set_mepa_tesoreria_atencion.php",
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
			
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}
			
			if (parseInt(respuesta.http_code) == 200) {
				$('#div_caja_chica_aumento_asignacion_en_la_programacion_de_pagos').html(respuesta.result);
				return false;
			}
		},
		error: function() {}
	});
}

function mepa_tesoreria_atencion_aumento_asignacion_guardar_programacion($num_tipo_grabacion)
{	
	
	$accion = '';
	var programacion_id_edit = $('#programacion_id_edit').val();
	var param_tipo_caja_chica = $('#mepa_tesoreria_atencion_param_tipo_caja_chica').val();
	var param_tipo_banco = $('#mepa_tesoreria_atencion_param_tipo_banco').val();
	var param_tipo_empresa = $('#mepa_tesoreria_atencion_param_tipo_empresa').val();
	var param_tipo_empresa_num_cuenta = $('#mepa_tesoreria_atencion_param_empresa_num_cuenta').val();

	var txt_titulo_pregunta = "";

	if (parseInt(param_tipo_caja_chica) == 0) 
	{
		alertify.error('Seleccione el tipo',5);
		$('#mepa_tesoreria_atencion_param_tipo_caja_chica').focus();
		$('#mepa_tesoreria_atencion_param_tipo_caja_chica').select2('open');
		return false;
	}

	if (parseInt(param_tipo_banco) == 0) 
	{
		alertify.error('Seleccione el Banco',5);
		$('#mepa_tesoreria_atencion_param_tipo_banco').focus();
		$('#mepa_tesoreria_atencion_param_tipo_banco').select2('open');
		return false;
	}

	if (parseInt(param_tipo_empresa_num_cuenta) == 0) 
	{
		alertify.error('Seleccione la Empresa',5);
		$('#mepa_tesoreria_atencion_param_empresa_num_cuenta').focus();
		$('#mepa_tesoreria_atencion_param_empresa_num_cuenta').select2('open');
		return false;
	}

	if (array_aumento_asignacion_programacion_de_pagos.length == 0) 
	{
		alertify.error('Tiene que agregar al menos un registro en la programación',5);
		return false;
	}

	if ($num_tipo_grabacion == '1') 
	{
		txt_titulo_pregunta = "Guardar";
		$accion = 'mepa_tesoreria_atencion_guardar_aumento_asignacion_programacion_de_pago';
	} 
	else if ($num_tipo_grabacion == '2') 
	{
		txt_titulo_pregunta = "Editar";
		$accion = 'mepa_tesoreria_atencion_guardar_aumento_asignacion_cambios_programacion_de_pago';
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
				"accion": $accion,
				"param_tipo_caja_chica": param_tipo_caja_chica,
				"param_tipo_banco": param_tipo_banco,
				"param_tipo_empresa": param_tipo_empresa,
				"param_tipo_empresa_num_cuenta": param_tipo_empresa_num_cuenta,
				"ids_aumento_asignacion": JSON.stringify(array_aumento_asignacion_programacion_de_pagos),
				"programacion_id_edit": programacion_id_edit
			}

			auditoria_send({ "proceso": "guardar_programacion_de_pago_asignacion", "data": data });

			$.ajax({
				url: "/sys/set_mepa_tesoreria_atencion.php",
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
					
					if (parseInt(respuesta.http_code) == 400) {
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
					
					if (parseInt(respuesta.http_code) == 200) {
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
							window.location.href = "?sec_id=mepa&sub_sec_id=tesoreria";
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

function mepa_tesoreria_atencion_rechazar_asignacion(id_asignacion)
{
	
	var titulo = '¿Está seguro de rechazar?';

	swal(
	{
		title: `<h3>${titulo}</h3>` + '<span style="font-size:12px">Motivo :</span> <textarea id="sec_mepa_tesoreria_atencion_motivo_rechazo" autofocus name="sec_prestamo_detalle_tesoreria_motivo_rechazo" class="form-control" style="display:block;font-size:11px;margin-top: -10px;" maxlength="50"></textarea>',
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

			var motivo_rechazo = $('#sec_mepa_tesoreria_atencion_motivo_rechazo').val().trim();

			if(motivo_rechazo.length == 0)
			{
				alertify.error('Ingrese el motivo',5);
				$("#sec_mepa_tesoreria_atencion_motivo_rechazo").focus();
				return false;
			}

			var data = {
				"accion": "mepa_tesoreria_atencion_rechazar_asignacion",
				"id_asignacion": id_asignacion,
				"motivo_rechazo": motivo_rechazo
			}

			auditoria_send({ "proceso": "mepa_tesoreria_atencion_rechazar_asignacion", "data": data });

			$.ajax({
				url: "sys/set_mepa_tesoreria_atencion.php",
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
						return false;

						setTimeout(function() {
							location.reload();
						}, 5000);

						return false;
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

function mepa_tesoreria_atencion_rechazar_aumento_asignacion(id_aumento)
{
	
	var titulo = '¿Está seguro de rechazar?';

	swal(
	{
		title: `<h3>${titulo}</h3>` + '<span style="font-size:12px">Motivo :</span> <textarea id="sec_mepa_tesoreria_atencion_motivo_aumento_rechazo" autofocus name="sec_mepa_tesoreria_atencion_motivo_aumento_rechazo" class="form-control" style="display:block;font-size:11px;margin-top: -10px;" maxlength="50"></textarea>',
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

			var motivo_rechazo = $('#sec_mepa_tesoreria_atencion_motivo_aumento_rechazo').val().trim();

			if(motivo_rechazo.length == 0)
			{
				alertify.error('Ingrese el motivo',5);
				$("#sec_mepa_tesoreria_atencion_motivo_aumento_rechazo").focus();
				return false;
			}

			var data = {
				"accion": "mepa_tesoreria_atencion_rechazar_aumento_asignacion",
				"id_aumento": id_aumento,
				"motivo_rechazo": motivo_rechazo
			}

			auditoria_send({ "proceso": "mepa_tesoreria_atencion_rechazar_aumento_asignacion", "data": data });

			$.ajax({
				url: "sys/set_mepa_tesoreria_atencion.php",
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
							title: "Rechazo de aumento de fondo",
							text: "El rechazo se hizo correctamente",
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        location.reload();
					        
					    });
						return false;

						setTimeout(function() {
							location.reload();
						}, 5000);

						return false;
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