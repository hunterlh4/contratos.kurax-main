// INICIO DECLARACION DE VARIABLES ARRAY
var array_programacion_de_pagos = [];
var puedo_buscar_acreedores_pendientes_de_pago = false;
// FIN DECLARACION DE VARIABLES ARRAY
var flag_etapa_provicion	= true;
var flag_etapa_provicion_edit	= true;
var programacion_id_edit = '';
var tipo_boton = 0;
function sec_contrato_nueva_programacion(){

	programacion_id_edit =	$('#programacion_id_edit').val();
	$('.sec_contrato_nueva_programacion_datepicker')
		.datepicker({
			dateFormat:'dd/mm/yy',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
			// localStorage.setItem($(this).atrr("id"),)
		});
	
	
	$("#btn_buscar_acreedores_pendiente_de_pago").click(function () {
		tipo_boton = 1;

		if (sec_contrato_nueva_programacion_validar_filtros_de_busqueda()) {
			puedo_buscar_acreedores_pendientes_de_pago = true;
			sec_contrato_nueva_programacion_obtener_acreedores_pendientes_de_pago();
		}
	});
	if(programacion_id_edit!=0){
		puedo_buscar_acreedores_pendientes_de_pago = true;
		
		// OBTENEMOS LOS ID DE LAS PROVICIONES PARA CARGARLA EN LA TABLA 
		var data = {
			"accion": "obtener_provisiones_ids",
			"programacion_id": programacion_id_edit
		}
		$.ajax({
			url: "/sys/get_contrato_nueva_programacion.php",
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
				console.log(respuesta);
				if (parseInt(respuesta.http_code) == 200) {
					sec_contrato_nueva_programacion_agregar_varios_a_la_programacion(respuesta.result);
				}
				 
			},
			error: function() {}
		});

	}

	$("#tipo_programacion_id").change(function () {
		$("#tipo_programacion_id option:selected").each(function () {
			tipo_programacion_id = $(this).val();
			var data = {
				"accion": "obtener_concepto",
				"tipo_programacion_id": tipo_programacion_id
			}
			var array_concepto = [];
			auditoria_send({ "proceso": "obtener_concepto", "data": data });
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
				success: function(resp) { //  alert(datat)
					var respuesta = JSON.parse(resp);
					if (parseInt(respuesta.http_code) == 400) {
					}
					
					if (parseInt(respuesta.http_code) == 200) {
						array_concepto.push(respuesta.result);
						console.log('Cantidad de Registro: ' + array_concepto.length);
						var html = '';

						if (array_concepto[0].length > 1) {
							html = '<option value="0">- Seleccione -</option>';
						}

						for (var i = 0; i < array_concepto[0].length; i++) {
							html += '<option value=' + array_concepto[0][i].id  + '>' + array_concepto[0][i].nombre + '</option>';
						}


						$("#tipo_concepto_id").html(html).trigger("change");

						if (tipo_programacion_id == 1) {
							var tipo_concepto_id = $('#tipo_concepto_id').val().trim();
							if (tipo_concepto_id == 0) {
								$('#div_tipo_anticipo').show();
								$('#tipo_anticipo_id').select2('open');
							}
						} else {
							$('#div_tipo_anticipo').hide();

							if (array_concepto[0].length > 1) {
								setTimeout(function() {
									$('#tipo_concepto_id').select2('open');
								}, 200);
							} else {
								$("#tipo_concepto_id").change();
							}
						}

						
						
						return false;
					}
				},
				error: function() {}
			});
		});
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
				success: function(resp) { //  alert(datat)
					var respuesta = JSON.parse(resp);
					if (parseInt(respuesta.http_code) == 400) {
					}
					
					if (parseInt(respuesta.http_code) == 200) {
						array_empresas.push(respuesta.result);
						console.log('Cantidad de Registro: ' + array_empresas.length);
						var html = '';

						if (array_empresas[0].length > 1) {
							html = '<option value="0">- Seleccione -</option>';
						}

						for (var i = 0; i < array_empresas[0].length; i++) {
							html += '<option value=' + array_empresas[0][i].id  + '>' + array_empresas[0][i].nombre + '</option>';
						}


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
				success: function(resp) { //  alert(datat)
					var respuesta = JSON.parse(resp);
					if (parseInt(respuesta.http_code) == 400) {
					}
					
					if (parseInt(respuesta.http_code) == 200) {
						array_concepto.push(respuesta.result);
						console.log('Cantidad de Registro: ' + array_concepto.length);

						var html = '';
						if (array_concepto[0].length > 1) {
							html = '<option value="0">- Seleccione -</option>';
						}

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

	$("#busqueda_por").change(function () {
		$("#busqueda_por option:selected").each(function () {
			busqueda_por = $(this).val();
			if (busqueda_por == 1) {
				$('#div_mes').show();
				$('#div_dia_de_pago_desde').show();
				$('#div_dia_de_pago_hasta').show();
				$('#div_fecha_de_vencimiento_desde').hide();
				$('#div_fecha_de_vencimiento_hasta').hide();

				setTimeout(function() {
					$('#mes_id').select2('open');
				}, 200);
			} else if (busqueda_por == 2) {
				$('#div_mes').hide();
				$('#div_dia_de_pago_desde').hide();
				$('#div_dia_de_pago_hasta').hide();
				$('#div_fecha_de_vencimiento_desde').show();
				$('#div_fecha_de_vencimiento_hasta').show();

				setTimeout(function() {
					$('#label_fecha_vencimiento_inicio').click();
				}, 200);
			} else {
				$('#div_mes').hide();
				$('#div_dia_de_pago_desde').hide();
				$('#div_dia_de_pago_hasta').hide();
				$('#div_fecha_de_vencimiento_desde').hide();
				$('#div_fecha_de_vencimiento_hasta').hide();
			}
		});
	});

	$("#mes_id").change(function () {
		$("#mes_id option:selected").each(function () {
			mes_id = $(this).val();
			if (mes_id != 0) {
				setTimeout(function() {
					$('#dia_de_pago_desde').select2('open');
				}, 200);
			}
		});
	});

	$("#dia_de_pago_desde").change(function () {
		$("#dia_de_pago_desde option:selected").each(function () {
			dia_de_pago_desde = $(this).val();
			if (dia_de_pago_desde != 0) {
				setTimeout(function() {
					$('#dia_de_pago_hasta').select2('open');
				}, 200);
			}
		});
	});

	$("#fecha_vencimiento_inicio").change(function () {
		setTimeout(function() {
			$('#label_fecha_vencimiento_fin').click();
		}, 200);
	});

	if(programacion_id_edit==0){
		sec_contrato_nueva_programacion_consultar_tipo_de_cambio();

	}

	$(".select2").select2({ width: "100%" });
}

function sec_contrato_nueva_programacion_validar_filtros_de_busqueda() {
	var tipo_programacion_id = $('#tipo_programacion_id').val().trim();
	var tipo_concepto_id = $('#tipo_concepto_id').val().trim();
	var empresa_id = $('#empresa_id').val().trim();
	var busqueda_por = $('#busqueda_por').val().trim();
	var mes_id = $('#mes_id').val().trim();
 	var dia_de_pago = $('#dia_de_pago').val().trim();
	// var dia_de_pago_hasta = $('#dia_de_pago_hasta').val().trim();

	if (parseInt(tipo_programacion_id) == 0) {
		alertify.error('Seleccione el tipo de programación',5);
		$("#tipo_programacion_id").focus();
		$('#tipo_programacion_id').select2('open');
		return false;
	}

	if (parseInt(tipo_concepto_id) == 0) {
		alertify.error('Seleccione el tipo de concepto',5);
		$("#tipo_concepto_id").focus();
		$('#tipo_concepto_id').select2('open');
		return false;
	}

	if (parseInt(empresa_id) == 0) {
		alertify.error('Seleccione el tipo de concepto',5);
		$("#empresa_id").focus();
		$('#empresa_id').select2('open');
		return false;
	}

	if (parseInt(busqueda_por) == 0) {
		alertify.error('Seleccione el tipo de busqueda',5);
		$("#busqueda_por").focus();
		$('#busqueda_por').select2('open');
		return false;
	}

	if (parseInt(busqueda_por) == 1 && parseInt(mes_id) == 0) {
		alertify.error('Seleccione el mes',5);
		$("#mes_id").focus();
		$('#mes_id').select2('open');
		return false;
	}

	if (parseInt(busqueda_por) == 1 && (parseInt(dia_de_pago) == 0 && parseInt(dia_de_pago) == 0)) {
		alertify.error('Seleccione el Día de pago',5);
		$("#dia_de_pago").focus();
		$('#dia_de_pago').select2('open');
		return false;
	}

	var fecha_vencimiento_inicio = $('#fecha_vencimiento_inicio').val().trim();
	var fecha_vencimiento_inicio_anio = fecha_vencimiento_inicio.substring(6, 10);
	var fecha_vencimiento_inicio_mes = fecha_vencimiento_inicio.substring(3, 5);
	var fecha_vencimiento_inicio_dia = fecha_vencimiento_inicio.substring(0, 2);
	// var fecha_vencimiento_fin = $('#fecha_vencimiento_fin').val().trim();
	// var fecha_vencimiento_fin_anio = fecha_vencimiento_fin.substring(6, 10);
	// var fecha_vencimiento_fin_mes = fecha_vencimiento_fin.substring(3, 5);
	// var fecha_vencimiento_fin_dia = fecha_vencimiento_fin.substring(0, 2);
	// var fecha_vencimiento_desde = new Date(fecha_vencimiento_inicio_anio, fecha_vencimiento_inicio_mes, fecha_vencimiento_inicio_dia);
	// var fecha_vencimiento_hasta = new Date(fecha_vencimiento_fin_anio, fecha_vencimiento_fin_mes, fecha_vencimiento_fin_dia);

	// if (parseInt(busqueda_por) == 2 && (fecha_vencimiento_desde.getTime() > fecha_vencimiento_hasta.getTime())) {
	// 	alertify.error('La Fecha de Vencimiento Desde debe ser menor a la Fecha de Vencimiento Hasta',5);
	// 	return false;
	// }

	return true;
}

function sec_contrato_nueva_programacion_guardar_y_aprobar_programacion(programacion_id){	
	var data = sec_contrato_nueva_programacion_validar_variables('nuevo');
	console.log(data)
	if (!data) {
		return false;
	}

	auditoria_send({ "proceso": "guardar_programacion_de_pago", "data": data });

	$.ajax({
		url: "/sys/set_contrato_nueva_programacion.php",
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
					window.location.href = "?sec_id=contrato&sub_sec_id=tesoreria";
					return false;
				}, 3000);
				return false;
			}
		},
		error: function() {}
	});
}


function sec_contrato_nueva_programacion_guardar_cambios_programacion() {
	var data = sec_contrato_nueva_programacion_validar_variables('editar');

	if (!data) {
		return false;
	}

	auditoria_send({ "proceso": "guardar_cambios_del_programacion_de_pago", "data": data });

	$.ajax({
		url: "/sys/set_contrato_nueva_programacion.php",
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
					title: "Error al guardar cambios en la programación de pagos.",
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
					text: "Los cambios en la programación de pago fueron guardados exitosamente",
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
				return false;
			}
		},
		error: function() {}
	});
}


function sec_contrato_nueva_programacion_validar_variables($operacion){
	var empresa_id = $('#empresa_id').val();
	var tipo_concepto_id = $('#tipo_concepto_id').val();
	var tipo_programacion_id = $('#tipo_programacion_id').val();
	var tipo_pago_id = $('#tipo_pago_id').val();
	var banco_id = $('#banco_id').val();
	var fecha_programacion = $('#fecha_programacion').val();
	var tipo_de_cambio = $('#tipo_de_cambio').val();
	programacion_id_edit = $('#programacion_id_edit').val();

	if (parseInt(tipo_programacion_id) == 0) {
		alertify.error('Seleccione el tipo de programación',5);
		$('#tipo_programacion_id').focus();
		$('#tipo_programacion_id').select2('open');
		return false;
	} else if (parseInt(tipo_concepto_id) == 0) {
		alertify.error('Seleccione el concepto',5);
		$('#tipo_concepto_id').focus();
		$('#tipo_concepto_id').select2('open');
		return false;
	} else if (parseInt(tipo_pago_id) == 0) {
		alertify.error('Seleccione el tipo de pago',5);
		$('#tipo_pago_id').focus();
		$('#tipo_pago_id').select2('open');
		return false;
	} else if (parseInt(banco_id) == 0) {
		alertify.error('Seleccione el banco',5);
		$('#banco_id').focus();
		$('#banco_id').select2('open');
		return false;
	} else if (array_programacion_de_pagos.length == 0) {
		alertify.error('La programación no posee ningún acreedor',5);
		return false;
	}else if (tipo_de_cambio.length == 0) {
		alertify.error('La programación no posee tipo de cambio',5);
		return false;
	}

	if ($operacion == 'nuevo') {
		$accion = 'guardar_programacion_de_pago';
	} else if ($operacion == 'editar') {
		$accion = 'guardar_cambios_del_programacion_de_pago';
	}

	var data = {
		"accion": $accion,
		"empresa_id": empresa_id,
		"tipo_programacion_id": tipo_programacion_id,
		"tipo_concepto_id": tipo_concepto_id,
		"tipo_pago_id": tipo_pago_id,
		"banco_id": banco_id,
		"fecha_programacion": fecha_programacion,
		"provision_ids": JSON.stringify(array_programacion_de_pagos),
		"programacion_id_edit": programacion_id_edit,
		"tipo_de_cambio": tipo_de_cambio,
	}
	
	return data;
}

function sec_contrato_nueva_programacion_obtener_acreedores_pendientes_de_pago() {
  if (puedo_buscar_acreedores_pendientes_de_pago) {
    var concepto_id = $("#tipo_concepto_id").val();
    var tipo_programacion_id = $("#tipo_programacion_id").val();
    var tipo_anticipo_id = $("#tipo_anticipo_id").val();
    var empresa_id = $("#empresa_id").val();
    var empresa_nombre = $("#empresa_id option:selected").text();
    var busqueda_por = $("#busqueda_por").val();
    var mes_id = $("#mes_id").val();
    var dia_de_pago = $("#dia_de_pago").val();
    var dia_de_pago_hasta = $("#dia_de_pago_hasta").val();
    var fecha_vencimiento_inicio = $("#fecha_vencimiento_inicio").val();
    var fecha_vencimiento_fin = $("#fecha_vencimiento_fin").val();
    var banco_de_acreedores = $("#banco_de_acreedores").val();
    var tipo_consulta = 1;
    var data = {
      accion: "obtener_acreedores_pendientes_de_pago",
      tipo_consulta: tipo_consulta,
      tipo_concepto_id: concepto_id,
      tipo_programacion_id: tipo_programacion_id,
      tipo_anticipo_id: tipo_anticipo_id,
      empresa_id: empresa_id,
      empresa_nombre: empresa_nombre,
      busqueda_por: busqueda_por,
      mes_id: mes_id,
      dia_de_pago: dia_de_pago,
      dia_de_pago_hasta: dia_de_pago_hasta,
      fecha_vencimiento_inicio: fecha_vencimiento_inicio,
      fecha_vencimiento_fin: fecha_vencimiento_fin,
      banco_de_acreedores: banco_de_acreedores,
      provision_ids: JSON.stringify(array_programacion_de_pagos),
      tipo_boton: tipo_boton,
      programacion_id_edit: programacion_id_edit,
    };
    console.log(data);
    auditoria_send({
      proceso: "obtener_acreedores_pendientes_de_pago",
      data: data,
    });

    $.ajax({
      url: "/sys/get_contrato_nueva_programacion.php",
      type: "POST",
      data: data,
      beforeSend: function () {
        loading("true");
      },
      complete: function () {
        loading();
      },
      success: function (resp) {
        var respuesta = JSON.parse(resp);
        auditoria_send({
          proceso: "obtener_acreedores_pendientes_de_pago",
          data: respuesta,
        });
        if (parseInt(respuesta.http_code) == 400) {
          swal("Aviso", respuesta.result, "warning");
          return false;
        }
        if (parseInt(respuesta.http_code) == 200) {
          console.log(respuesta);
          $("#div_acreedores_pendiente_pago").html(respuesta.result);
          $("#div_acreedores_pendiente_pago table").DataTable({
            language: {
              search: "Buscar",
              lengthMenu: "Mostrar _MENU_ registros por página",
              zeroRecords: "No se encontraron registros",
              info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
              infoEmpty: "No hay registros",
              infoFiltered: "(filtrado de _MAX_ total records)",
              paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior",
              },
              sProcessing: "Procesando...",
            },
            dom:
              "<'row'<'col-sm-12 text-center'B>>" + // Botón centrado
              "<'row'<'col-sm-6'l><'col-sm-6'f>>" + // Filtros a la izquierda y buscador a la derecha
              "<'row'<'col-sm-12'tr>>" + // Tabla debajo de los filtros y el buscador
              "<'row'<'col-sm-6'i><'col-sm-6'p>>", // Paginación e información debajo de la tabla
            aLengthMenu: [10, 20, 30, 40, 50],
            order: [[0, "desc"]],
            buttons: [
              {
                extend: "excel",
                title: "Exportar a excel",
                filename: "Provisiones_tesoreria",
                text: '<button class="btn btn-success btn-sm"><span class="fa fa-file-excel-o"> Exportar pendientes</span></button>',
              },
            ],
          });
          return false;
        }
      },
      error: function () {},
    });
  }
}


function sec_contrato_nueva_programacion_agregar_a_la_programacion(provision_id) {

	
	if(flag_etapa_provicion_edit && programacion_id_edit != 0){
		var elementos = provision_id[0].split(',');

		// Convertir cada elemento en un número utilizando parseInt() o parseFloat()
		var nuevoArreglo = elementos.map(function(elemento) {
		return parseInt(elemento); // o parseFloat(elemento) si hay decimales
		});
		flag_etapa_provicion_edit= false;
		if (array_programacion_de_pagos.includes(nuevoArreglo) === false){
			array_programacion_de_pagos.push(nuevoArreglo)
		}
	
		sec_contrato_nueva_programacion_actualizar_tabla_programación_de_pagos();
		sec_contrato_nueva_programacion_obtener_acreedores_pendientes_de_pago();

	}else{
		sec_contrato_nueva_programacion_validar_provicion(provision_id)
		.then(function(result) {
			if(result){
				if (array_programacion_de_pagos.includes(provision_id) === false){
					array_programacion_de_pagos.push(provision_id)
				}
			
				sec_contrato_nueva_programacion_actualizar_tabla_programación_de_pagos();
				sec_contrato_nueva_programacion_obtener_acreedores_pendientes_de_pago();
			}
			

		}).catch(function(error) {
			// Manejar cualquier error aquí
			console.error(error);
		});
	}
	
}


function sec_contrato_nueva_programacion_agregar_varios_a_la_programacion(...provision_ids) {
    var i;
	console.log(provision_ids[0]);

	
	if(flag_etapa_provicion_edit && programacion_id_edit!=0){
		var elementos = provision_ids[0].split(',');

		// Convertir cada elemento en un número utilizando parseInt() o parseFloat()
		var nuevoArreglo = elementos.map(function(elemento) {
			return parseInt(elemento); // o parseFloat(elemento) si hay decimales
		});

		flag_etapa_provicion_edit= false;
	
		for(i=0;i<nuevoArreglo.length;i++){
			if (array_programacion_de_pagos.includes(nuevoArreglo[i]) === false){
				array_programacion_de_pagos.push(nuevoArreglo[i])
			}
		}

		sec_contrato_nueva_programacion_actualizar_tabla_programación_de_pagos();
		sec_contrato_nueva_programacion_obtener_acreedores_pendientes_de_pago();
	}else{
		sec_contrato_nueva_programacion_validar_provicion(provision_ids)
		.then(function(result) {
			// Manejar el resultado aquí
		
			if(result){
		
				for(i=0;i<provision_ids.length;i++){
					if (array_programacion_de_pagos.includes(provision_ids[i]) === false){
						array_programacion_de_pagos.push(provision_ids[i])
					}
					// console.log(array_programacion_de_pagos);
				}

				sec_contrato_nueva_programacion_actualizar_tabla_programación_de_pagos();
				sec_contrato_nueva_programacion_obtener_acreedores_pendientes_de_pago();
			}

			
		})
		.catch(function(error) {
			// Manejar cualquier error aquí
			console.error(error);
		});
			
	}
	
	
	

   
}


function sec_contrato_nueva_programacion_quitar_de_la_programacion(provision_id){

	console.log(provision_id);

	// var elementos = array_programacion_de_pagos.split(',');

	// // Convertir cada elemento en un número utilizando parseInt() o parseFloat()
	// array_programacion_de_pagos = elementos.map(function(elemento) {
	//   return parseInt(elemento); // o parseFloat(elemento) si hay decimales
	// });

	const index = array_programacion_de_pagos.indexOf(provision_id);
	if (index > -1) {
		array_programacion_de_pagos.splice(index, 1);
	}
	console.log(array_programacion_de_pagos);
	sec_contrato_nueva_programacion_actualizar_tabla_programación_de_pagos();
	sec_contrato_nueva_programacion_obtener_acreedores_pendientes_de_pago();
}

function sec_contrato_nueva_programacion_quitar_varios_a_la_programacion(...provision_ids) {
    var i;
	console.log(provision_ids)

	// var elementos = array_programacion_de_pagos.split(',');

	// // Convertir cada elemento en un número utilizando parseInt() o parseFloat()
	// array_programacion_de_pagos = elementos.map(function(elemento) {
	//   return parseInt(elemento); // o parseFloat(elemento) si hay decimales
	// });

    for(i=0;i<provision_ids.length;i++){
		const index = array_programacion_de_pagos.indexOf(provision_ids[i]);
		if (index > -1) {
			array_programacion_de_pagos.splice(index, 1);
		}
    }
	console.log(array_programacion_de_pagos);

    sec_contrato_nueva_programacion_actualizar_tabla_programación_de_pagos();
	sec_contrato_nueva_programacion_obtener_acreedores_pendientes_de_pago();
}
 
function sec_contrato_nueva_programacion_actualizar_tabla_programación_de_pagos(){
	var tipo_consulta = 2;
	var tipo_programacion_id = $('#tipo_programacion_id').val();
	tipo_boton  = 0;
	// alert("trav"+flag_etapa_provicion_edit);
	

	


		var data = {
			accion: "obtener_acreedores_pendientes_de_pago",
			tipo_consulta: tipo_consulta,
			tipo_programacion_id: tipo_programacion_id,
			provision_ids: JSON.stringify(array_programacion_de_pagos),
			// programacion_id_edit:programacion_id_edit,programacion_id_edit
			tipo_boton:tipo_boton,
			programacion_id_edit: programacion_id_edit
		  };
	      console.log(data);
		  auditoria_send({
			proceso: "obtener_acreedores_pendientes_de_pago",
			data: data,
		  });
		  $.ajax({
			url: "/sys/get_contrato_nueva_programacion.php",
			type: "POST",
			data: data,
			beforeSend: function () {
			  loading("true");
			},
			complete: function () {
			  loading();
			},
			success: function (resp) {
			  var respuesta = JSON.parse(resp);
			  console.log(respuesta);
			  if (parseInt(respuesta.http_code) == 400) {
				return false;
			  }
	
			  if (parseInt(respuesta.http_code) == 200) {
				$("#div_acreedores_en_la_programacion_de_pagos").html(
				  respuesta.result
				);
				$("#div_acreedores_en_la_programacion_de_pagos_montos").html(
					respuesta.result_footer_totales
				  );
				$('#div_acreedores_en_la_programacion_de_pagos table').DataTable({
					language: {
						search: "Buscar",
						lengthMenu: "Mostrar _MENU_ registros por página",
						zeroRecords: "No se encontraron registros",
						info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
						infoEmpty: "No hay registros",
						infoFiltered: "(filtrado de _MAX_ total records)",
						paginate: {
						  first: "Primero",
						  last: "Último",
						  next: "Siguiente",
						  previous: "Anterior",
						},
						sProcessing: "Procesando...",
					  },
					  dom:
					  "<'row'<'col-sm-12 text-center'B>>" + // Botón centrado
					  "<'row'<'col-sm-6'l><'col-sm-6'f>>" + // Filtros a la izquierda y buscador a la derecha
					  "<'row'<'col-sm-12'tr>>" + // Tabla debajo de los filtros y el buscador
					  "<'row'<'col-sm-6'i><'col-sm-6'p>>", // Paginación e información debajo de la tabla
					aLengthMenu: [10, 20, 30, 40, 50],
					order: [[0, "desc"]],
					buttons: [
					  {
						extend: "excel",
						title: "Exportar a excel",
						filename: "Provisiones_tesoreria",
						text: '<button class="btn btn-warning btn-sm"><span class="fa fa-file-excel-o"> Exportar programación </span></button>',
					  },
					],
				});
				
				return false;
			  }
			},
			error: function () {},
		  });
		
	


	
}
function sec_contrato_nueva_programacion_validar_provicion(valores) {
	
		return new Promise(function(resolve, reject) {
			var data = {
				"accion": "validar_provision",
				"provision_ids": JSON.stringify(valores)
				
			};
			auditoria_send({ "proceso": "validar_provision", "data": data });
		
			$.ajax({
				url: "/sys/get_contrato_nueva_programacion.php",
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
		
					auditoria_send({ proceso: "validar_provision", data: respuesta });
					if (parseInt(respuesta.http_code) == 200) {
						if(respuesta.list_no_cumplen>0){
							swal({
								title: respuesta.message,
								text: "",
								html: true,
								type: "warning",
								closeOnConfirm: false,
								showCancelButton: false,
							});
							resolve(false);
							// flag_etapa_provicion	=	false;
		
						}else{
							resolve(true);
							// flag_etapa_provicion	=	true;
		
						}
						
					} else if (parseInt(respuesta.http_code) == 400) {
						swal({
							title: respuesta.consulta_error,
							text: "",
							html: true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false,
						});
						resolve(false);
		
					}  
				},
				error: function() {
					reject(new Error('Error en la llamada AJAX'));
				}
			});
	
		});
	
	
   
}

function sec_contrato_nueva_programacion_consultar_tipo_de_cambio(){
	
	var data = {
		"accion": "obtener_tipo_de_cambio"
	}

	auditoria_send({ "proceso": "obtener_tipo_de_cambio", "data": data });

	$.ajax({
		url: "/sys/get_contrato_nueva_programacion.php",
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
			auditoria_send({ proceso: "obtener_tipo_de_cambio", data: respuesta });

			if (parseInt(respuesta.http_code) == 400) {
				swal({
					title: respuesta.consulta_error,
					text: "",
					html: true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false,
					timer: 2000 
				});
				location.href = '?sec_id=contrato&sub_sec_id=tesoreria';

			}
		},
		error: function() {}
	});
}

function btn_validar_tipo_cambio(){
	var data = {
		"accion": "obtener_tipo_de_cambio"
	}

	auditoria_send({ "proceso": "obtener_tipo_de_cambio", "data": data });

	$.ajax({
		url: "/sys/get_contrato_nueva_programacion.php",
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
			console.log(resp);

			auditoria_send({ proceso: "obtener_tipo_de_cambio", data: respuesta });
			// console.log(respuesta)

			if (parseInt(respuesta.http_code) == 400 && respuesta.result_error===0) {
				swal({
					title: respuesta.consulta_error,
					text: "",
					html: true,
					type: "warning",
					closeOnConfirm: false,
					showCancelButton: false,
				});
			}else{
				location.href = '?sec_id=contrato&sub_sec_id=nueva_programacion';
			}
		},
		error: function() {}
	});
}

