//INICIO FUNCIONES INICIALIZADOS
function sec_mepa_asignacion()
{	
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_caja_chica_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	//INICIO DECLARACION DE MASK
	// 20 CARACTERES (0 -> CEROS)
	$('.sec_mepa_caja_chica_txt_numero_cuenta').mask('');
	$('.sec_mepa_caja_chica_txt_buscar_dni').mask('00000000');
	//FIN DECLARACION DE MASK

	$("#sec_mepa_caja_chica_txt_fondo_asignado").on({
		"focus": function (event) {
			$(event.target).select();
		},
		"change": function (event) {
			if(parseFloat($(event.target).val().replace(/\,/g, ''))>0)
			{
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
				$(event.target).val(function (index, value ) {
					return value.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		}
	});

	$("#btn_guardar_solicitud_asignacion_caja_chica").click(function()
	{
		sec_btn_guardar_solicitud_asignacion_caja_chica();
	});

	$("#sec_mepa_caja_chica_buscar_dni").click(function()
	{
		sec_btn_buscar_dni_personal();
	});	
}

$('#sec_mepa_caja_chica_txt_banco').change(function () 
{
	$("#sec_mepa_caja_chica_txt_banco option:selected").each(function ()
	{	
		var selectValor = $(this).val();

		$('.sec_mepa_caja_chica_txt_numero_cuenta').val("");

		//12 = BBVA
		if(selectValor == 12)
		{
			//18 DIGITOS
			$('.sec_mepa_caja_chica_txt_numero_cuenta').mask('000000000000000000');
			$("#sec_mepa_caja_chica_txt_num_digitos_cuenta").html(18)
		}
		else
		{
			//20 DIGITOS
			$('.sec_mepa_caja_chica_txt_numero_cuenta').mask('00000000000000000000');
			$("#sec_mepa_caja_chica_txt_num_digitos_cuenta").html(20)
		}
		
		
	});
});

//FIN FUNCIONES INICIALIZADOS

function sec_btn_buscar_dni_personal()
{
	$("#sec_mepa_caja_chica_txt_usuario_asignado_id").val("0");
	$("#sec_mepa_caja_chica_txt_usuario_asignado_nombre").val("");

	var txt_dni_buscar = $("#sec_mepa_caja_chica_txt_buscar_dni").val();

	if(txt_dni_buscar.length < 8)
	{
		alertify.error('Ingrese DNI de 8 dígitos',5);
		$("#sec_mepa_caja_chica_txt_buscar_dni").focus();
		return false;
	}

	var data = {
        "accion": "sec_btn_buscar_dni_personal",
        "txt_buscar_dni" : txt_dni_buscar
    }

    $.ajax({
		url: "sys/set_mepa_asignacion.php",
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

			if(parseInt(respuesta.valor_auto_asignacion) == 1)
			{
				$("#mepa_asignacion_div_reportar_usuarios").show();
				$("#mepa_asignacion_se_reportara_al_usuario").val("1");
			}
			else
			{
				$("#mepa_asignacion_div_reportar_usuarios").hide();
				$("#mepa_asignacion_se_reportara_al_usuario").val("0");
			}

			if(parseInt(respuesta.http_code) == 200) 
			{
				$("#sec_mepa_caja_chica_txt_usuario_asignado_id").val(respuesta.id_personal_dni);
				$("#sec_mepa_caja_chica_txt_usuario_asignado_nombre").val(respuesta.personal_nombre);

				// AGREGAR LA RAZON SOCIAL EN EL COMBO
				if( respuesta.personal_razon_social_id != null)
				{
					var select = '<option value="'+respuesta.personal_razon_social_id+'">'+respuesta.personal_razon_social+'</option>';
					$("#sec_mepa_caja_chica_txt_empresa").html(select);
				}
				else
				{
					var select = '<option value="0">-- Seleccione --</option>';
					$("#sec_mepa_caja_chica_txt_empresa").html(select);

					swal({
						title: '',
						text: respuesta.informacion_texto,
						html:true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
					});
					return false;
				}
			}
			else if(parseInt(respuesta.http_code) == 300) 
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
			else 
			{
				swal({
					title: "Error al guardar Solicitud",
					text: respuesta.status,
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

var contador_mepa_asignacion_tabla_fila_reportar_a_usuario = 0;
var detalles_mepa_asignacion_table_reportar_a_usuario = 0;

function mepa_asignacion_agregar_usuarios_a_reportar()
{
	var int_reportar_usuario = $('#sec_mepa_caja_chica_txt_reportar_usuario').val();
	var text_reportar_usuario = $('#sec_mepa_caja_chica_txt_reportar_usuario option:selected').text();

	if(int_reportar_usuario == 0)
	{
		alertify.error('Seleccione Reportar a',5);
		$("#sec_mepa_caja_chica_txt_reportar_usuario").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_caja_chica_txt_reportar_usuario').select2('open');
		}, 200);
		return false;
	}

	var fila ='<tr id="mepa_asignacion_tabla_fila_reportar_a_usuario'+contador_mepa_asignacion_tabla_fila_reportar_a_usuario+'">'+
		'<td>'+
			'<button type="button" class="btn btn-danger btn-xs" id="boton_guardar_contratos" onclick="mepa_asignacion_eliminar_reportar_usuarios('+contador_mepa_asignacion_tabla_fila_reportar_a_usuario+');">'+
				'<span class="glyphicon glyphicon-remove"></span>'+
			'</button>'+
		'</td>'+
		'<td>'+
			'<select class="form-control txt_reporte_usuario sec_mepa_caja_chica_select_filtro"><option value="'+int_reportar_usuario+'">'+text_reportar_usuario+'</option></select>'+
		'</td>'+
	'</tr>';

	contador_mepa_asignacion_tabla_fila_reportar_a_usuario ++;
	detalles_mepa_asignacion_table_reportar_a_usuario ++;

	$("#mepa_asignacion_reportar_usuarios_detalle_table").append(fila);
}

function mepa_asignacion_eliminar_reportar_usuarios(indice)
{
	$("#mepa_asignacion_tabla_fila_reportar_a_usuario" + indice).remove();
	detalles_mepa_asignacion_table_reportar_a_usuario = detalles_mepa_asignacion_table_reportar_a_usuario - 1;
}

function sec_btn_guardar_solicitud_asignacion_caja_chica()
{
	var txt_motivo = $('#sec_mepa_caja_chica_txt_motivo').val();
	var int_banco = $('#sec_mepa_caja_chica_txt_banco').val();
	var txt_numero_cuenta = $('#sec_mepa_caja_chica_txt_numero_cuenta').val();
	var txt_fondo_asignado = $('#sec_mepa_caja_chica_txt_fondo_asignado').val();
	var int_usuario_asignado = $('#sec_mepa_caja_chica_txt_usuario_asignado_id').val();
	var int_zona = $('#sec_mepa_caja_chica_txt_zona').val();
	var int_empresa = $('#sec_mepa_caja_chica_txt_empresa').val();
	var int_se_reportara_usuario = $('#mepa_asignacion_se_reportara_al_usuario').val();
	var int_reportar_usuario = $('#sec_mepa_caja_chica_txt_reportar_usuario').val();

	var array_usuarios = new Array();
    var usuarios_value = document.getElementsByClassName('txt_reporte_usuario');

	// INICIO VALIDAR CAMPOS

	if(txt_motivo.length == 0)
	{
		alertify.error('Ingrese Motivo',5);
		$("#sec_mepa_caja_chica_txt_motivo").focus();
		return false;
	}

	if(int_banco == 0)
	{
		alertify.error('Seleccione Banco',5);
		$("#sec_mepa_caja_chica_txt_banco").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_caja_chica_txt_banco').select2('open');
		}, 200);
		return false;
	}

	if(txt_numero_cuenta.length == 0)
	{
		alertify.error('Ingrese Número de Cuenta',5);
		$("#sec_mepa_caja_chica_txt_numero_cuenta").focus();
		return false;
	}

	if(int_banco == 12)
	{
		if(txt_numero_cuenta.length < 18)
		{
			alertify.error('Ingrese Número de Cuenta de 18 dígitos',5);
			$("#sec_mepa_caja_chica_txt_numero_cuenta").focus();
			return false;
		}
	}
	else
	{
		if(txt_numero_cuenta.length < 20)
		{
			alertify.error('Ingrese Número de Cuenta de 20 dígitos',5);
			$("#sec_mepa_caja_chica_txt_numero_cuenta").focus();
			return false;
		}
	}

	if(txt_fondo_asignado.length == 0)
	{
		alertify.error('Ingrese Fondo Asignado',5);
		$("#sec_mepa_caja_chica_txt_fondo_asignado").focus();
		return false;
	}

	if(txt_fondo_asignado == "0.00")
	{
		alertify.error('No se permite 0.00',5);
		$("#sec_mepa_caja_chica_txt_fondo_asignado").focus();
		return false;
	}

	if(int_zona == "0")
	{
		alertify.error('Seleccione la Zona',5);
		$("#sec_mepa_caja_chica_txt_zona").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_caja_chica_txt_zona').select2('open');
		}, 200);
		return false;
	}

	if(int_usuario_asignado == "0" || int_usuario_asignado == "")
	{
		alertify.error('Debe asignar un usuario',5);
		$("#sec_mepa_caja_chica_txt_buscar_dni").focus();
		return false;
	}

	if(int_empresa == "0")
	{
		alertify.error('Seleccione la Empresa',5);
		$("#sec_mepa_caja_chica_txt_empresa").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_caja_chica_txt_empresa').select2('open');
		}, 200);
		return false;
	}

	if(int_se_reportara_usuario == "1")
	{
		// INICIO: VALIDAR DETALLE DE USUARIOS A REPORTAR
		
		if(usuarios_value.length != 0)
		{
			for (var i = 0; i < usuarios_value.length; i++) 
		    {
		        if(usuarios_value[i].value == "")
		        {
		        	alertify.error('Ingrese usuario a reportar',5);
					return false;
		        }
		        array_usuarios.push(usuarios_value[i].value);
		    }
		}
		else
		{
			alertify.error('Ingrese usuario a reportar',5);
			$("#sec_mepa_caja_chica_txt_reportar_usuario").focus();
			setTimeout(function() 
			{
				$('#sec_mepa_caja_chica_txt_reportar_usuario').select2('open');
			}, 200);
			return false;
		}

		// FIN: VALIDAR DETALLE DE USUARIOS A REPORTAR
	}

	// FIN VALIDAR CAMPOS
	swal(
	{
		title: '¿Está seguro de solicitar la Asignación?',
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
			var dataForm = new FormData($("#sec_mepa_caja_chica_formulario_nueva_asignacion")[0]);
			dataForm.append("accion","guardar_solicitud_asignacion_caja_chica");
			dataForm.append("txt_motivo", txt_motivo);
			dataForm.append("int_banco", int_banco);
			dataForm.append("txt_numero_cuenta", txt_numero_cuenta);
			dataForm.append("txt_fondo_asignado", txt_fondo_asignado);
			dataForm.append("int_usuario_asignado", int_usuario_asignado);
			dataForm.append("int_zona", int_zona);
			dataForm.append("int_empresa", int_empresa);
			dataForm.append("int_se_reportara_usuario", int_se_reportara_usuario);
			dataForm.append("array_reportar_usuario", JSON.stringify(array_usuarios));

			//auditoria_send({ "proceso": "sec_btn_guardar_solicitud_asignacion_caja_chica", "data": dataForm });

			$.ajax({
				url: "sys/set_mepa_asignacion.php",
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
					auditoria_send({ "respuesta": "guardar_solicitud_asignacion_caja_chica", "data": respuesta });
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
					        window.location.href = "?sec_id=mepa&sub_sec_id=asignacion";
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=asignacion";
						}, 5000);

						return true;
					}
					else if(parseInt(respuesta.http_code) == 300) 
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