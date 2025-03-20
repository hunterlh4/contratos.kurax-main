function sec_mepa_solicitud_liquidacion()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_liquidacion_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	// INICIO INICIALIZAR FECHA
	$('.anuncio_fecha_datepicker')
		.datepicker({
			dateFormat:'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
			minDate: '-15d',
			maxDate: '0d'
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
			// localStorage.setItem($(this).atrr("id"),)
		});
	// FIN INICIALIZAR FECHA

	$('.mepa_solicitud_liquidacion_detalle_centro_costo').mask('0000');

	$(".mepa_solicitud_liquidacion_detalle_importe").on({
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

	$(".mepa_solicitud_liquidacion_detalle_tasa_igv").on({
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

	mepa_solicitud_liquidacion_listar_detalles_pendientes();
}

function mepa_solicitud_liquidacion_listar_detalles_pendientes()
{
	
	var data = {
		"accion": "mepa_solicitud_liquidacion_listar_detalles_pendientes"
	}

	$.ajax({
		url: "/sys/set_mepa_solicitud_liquidacion.php",
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
			auditoria_send({ "respuesta": "mepa_solicitud_liquidacion_listar_detalles_pendientes", "data": respuesta });
			
			if (parseInt(respuesta.http_code) == 400)
			{
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200)
			{
				$('#mepa_solicitud_liquidacion_listar_detalle').html(respuesta.result);
				return false;
			}
		},
		error: function() {}
	});
}

function mepa_solicitud_liquidacion_btn_agregar_detalle_liquidacion()
{
	
	var txt_liquidacion_fecha_del = $("#sec_mepa_liquidacion_txt_fecha_del").val();
	var txt_liquidacion_fecha_hasta = $("#sec_mepa_liquidacion_txt_fecha_hasta").val();

	if(txt_liquidacion_fecha_del == "")
	{
		alertify.error('Falta seleccionar Fecha Del.',5);
		$("#sec_mepa_liquidacion_txt_fecha_del").focus();
		setTimeout(function() {
			$('.sec_mepa_liquidacion_txt_fecha_del').select2('open');
		}, 500);

		return false;
	}

	if(txt_liquidacion_fecha_hasta == "")
	{
		alertify.error('Falta seleccionar Fecha Al.',5);
		$("#sec_mepa_liquidacion_txt_fecha_hasta").focus();
		setTimeout(function() {
			$('.sec_mepa_liquidacion_txt_fecha_hasta').select2('open');
		}, 500);

		return false;
	}

	$('#fecha_documento').attr("min", txt_liquidacion_fecha_del);
	$("#fecha_documento").attr("max", txt_liquidacion_fecha_hasta);

	$("#mepa_solicitud_liquidacion_modal_agregar_detalle .btn_guardar").show();
	$("#title_mepa_solicitud_liquidacion_modal_agregar_detalle").text("Agregar detalle liquidación");
	$("#mepa_solicitud_liquidacion_modal_agregar_detalle").modal("show");
}

$("#mepa_solicitud_liquidacion_modal_agregar_detalle .btn_guardar").off("click").on("click",function(){
    
    
    var modal_param_fecha_documento = $('#fecha_documento').val();
    var modal_param_tipo_documento = $('#tipo_documento').val();
    var modal_param_serie_comprobante = $('#serie_comprobante').val().trim();
    var modal_param_num_comprobante = $('#num_comprobante').val().trim();
    var modal_param_centro_costo = $('#centro_costo').val();
    var modal_param_detalle = $('#detalle').val().trim();
    var modal_param_archivo = document.getElementById("nombre_file");
    var modal_param_archivo_xml = document.getElementById("nombre_file_xml");
    var modal_param_importe = $('#importe').val();
    var modal_param_tasa_igv = $('#tasa_igv').val();

    if(modal_param_fecha_documento == "")
	{
		alertify.error('Falta seleccionar Fecha Documento',5);
		return false;
	}

	if(modal_param_tipo_documento == "0")
    {
        alertify.error('Seleccione Tipo Documento',5);
        $("#tipo_documento").focus();
        setTimeout(function() 
        {
            $('#tipo_documento').select2('open');
        }, 200);

        return false;
    }

	if(modal_param_serie_comprobante == "")
	{
		alertify.error('Falta ingresar la Serie Comprobante',5);
		return false;
	}

	if(modal_param_num_comprobante == "")
	{
		alertify.error('Falta ingresar el Nº Comprobante',5);
		return false;
	}

	if(modal_param_centro_costo == "")
	{
		alertify.error('Falta ingresar el Centro de Costo',5);
		return false;
	}

	if(modal_param_detalle == "")
	{
		alertify.error('Falta ingresar el Detalle',5);
		return false;
	}

	if(modal_param_archivo.files.length == 0)
	{
		alertify.error('Seleccione el Comprobante',5);
		return false;
	}

	if(modal_param_archivo.files[0].size > 1000000)
	{
		alertify.error('EL Comprobante debe pesar menos de 1MB',5);
		return false;
	}

	if(modal_param_archivo_xml.files.length > 0)
	{
		if(modal_param_archivo_xml.files[0].size > 1000000)
		{
			alertify.error('EL XML debe pesar menos de 1MB',5);
			return false;
		}
	}

	if(modal_param_importe == "")
	{
		alertify.error('Falta ingresar el Importe',5);
		return false;
	}

	if(modal_param_tasa_igv == "")
	{
		alertify.error('Falta ingresar el IGV',5);
		return false;
	}
	
	swal(
    {
        title: '¿Está seguro de agregar el detalle?',
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
        	var dataForm = new FormData($("#mepa_solicitud_liquidacion_form_modal_agregar_detalle")[0]);
            dataForm.append("accion","mepa_solicitud_liquidacion_agregar_detalle");

            $.ajax({
            url: "sys/set_mepa_solicitud_liquidacion.php",
            type: 'POST',
            data: dataForm,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                loading("true");
            },
            complete: function() {
                loading();
            },
            success: function(data){
                
                var respuesta = JSON.parse(data);
                auditoria_send({ "respuesta": "mepa_solicitud_liquidacion_agregar_detalle", "data": respuesta });
                if(parseInt(respuesta.http_code) == 200) 
                {
                	$("#mepa_solicitud_liquidacion_modal_agregar_detalle").modal("hide");
                	mepa_liquidacion_limpiar_cajas_modal_form_detalle();
                	
                	swal('Registro exitoso', 'Datos registrados correctamente', 'success');
                	
                	mepa_solicitud_liquidacion_listar_detalles_pendientes();
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
                else {
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
            }
            });
        }
    });    
})

$("#detalle").keyup(function ()
{
	$("#mepa_solcitud_liquidacion_detalle_cantidad_caracteres").text(200 - $(this).val().length)
});

function mepa_liquidacion_limpiar_cajas_modal_form_detalle()
{
	
	$('#fecha_documento').val("");
    $("#tipo_documento").val("0").trigger("change.select2");
    $('#serie_comprobante').val("")
    $('#num_comprobante').val("");
    $('#centro_costo').val("");
    $('#detalle').val("");
    document.getElementById("nombre_file").value = "";
    document.getElementById("nombre_file_xml").value = "";
    $('#importe').val("");
    $('#tasa_igv').val("");
}

function mepa_solicitud_liquidacion_elimnar_detalle(detalle_id)
{

	swal({
		title: "Está Seguro?",
		text: "Se eliminará el registro",
		type: "warning",
		html: true,
		showCancelButton: true,
		confirmButtonClass: "btn-danger",
		confirmButtonText: "Si!",
		cancelButtonText: "No!",
		closeOnConfirm: true,
		closeOnCancel: true
	},
	function (isConfirm)
	{
		if (isConfirm)
		{
			$.ajax({
		        url: "sys/set_mepa_solicitud_liquidacion.php",
		        type: 'POST',
		        data: {
		        	accion : "mepa_solicitud_liquidacion_elimnar_detalle",
		        	detalle_id : detalle_id
		        },
		        beforeSend: function() {
		            loading("true");
		        },
		        success: function(data){
		        	
		        	var resp = JSON.parse(data);
		        	if(parseInt(resp.http_code) == 200)
		        	{
						swal({
							title: "Detalle Eliminado",
							text: resp.mensaje,
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        window.location.reload();
					    });

						setTimeout(function() {
							window.location.reload();
						}, 1000);

						return true;
					}
					else {
						swal({
							title: "Error al Eliminar",
							text: resp.mensaje,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
		        },
		        complete: function() {
		            loading(false);
		        }
		    });	
		}
	});
}

$("#check_agg_liquidacion_movilidad").change(function()
{
	if($(this).prop('checked') == true)
	{
		//INICIO MOSTRAR LAS MOVILIDADES DE ACUERDO A LAS FECHAS DEL - FECHAS AL, DE LA LIQUIDACION
		mepa_solicitud_liquidacion_mostrar_movilidad_rango_fecha();
		//FIN MOSTRAR LAS MOVILIDADES DE ACUERDO A LAS FECHAS DEL - FECHAS AL, DE LA LIQUIDACION

		$("#div_liquidacion_movilidad").show();
	}
	else
	{
		$("#div_liquidacion_movilidad").hide();	
	}
})

$('#sec_mepa_liquidacion_txt_fecha_del,#sec_mepa_liquidacion_txt_fecha_hasta').change(function (e) {
	e.preventDefault();
	
	var fecha_del = $("#sec_mepa_liquidacion_txt_fecha_del").val();
	var fecha_fin = $("#sec_mepa_liquidacion_txt_fecha_hasta").val();
	
	if(fecha_del != '' & fecha_fin != '')
	{
		var arr_fecha_del = fecha_del.split("-");
		//CONVERTIMOS A AÑO-MES-DIA
	  	fecha_del = arr_fecha_del[2] + "-" +arr_fecha_del[1] + "-" + arr_fecha_del[0];

	  	var arr_fecha_fin = fecha_fin.split("-");
	    //CONVERTIMOS A AÑO-MES-DIA
	    fecha_fin = arr_fecha_fin[2] + "-" + arr_fecha_fin[1] + "-" +arr_fecha_fin[0];

		if(new Date(fecha_del) > new Date(fecha_fin))
		{
			swal({
				type: "warning",
				title: "Alerta, rango de fechas",
				text: "La Fecha Del, debe ser menor a la Fecha Al.",
				timer: 8000
			});
			$("#sec_mepa_solicitud_liquidacion_div_mensaje_cruce_fechas").show();
			$('#btn_guardar_mepa_solicitud_liquidacion').prop("disabled", true);
		}
		else
		{
			$("#sec_mepa_solicitud_liquidacion_div_mensaje_cruce_fechas").hide();
			$('#btn_guardar_mepa_solicitud_liquidacion').prop("disabled", false);

			//INICIO MOSTRAR LAS MOVILIDADES DE ACUERDO A LAS FECHAS DEL - FECHAS AL, DE LA LIQUIDACION
			mepa_solicitud_liquidacion_mostrar_movilidad_rango_fecha();
			//FIN MOSTRAR LAS MOVILIDADES DE ACUERDO A LAS FECHAS DEL - FECHAS AL, DE LA LIQUIDACION
		}
	}
});

function mepa_solicitud_liquidacion_mostrar_movilidad_rango_fecha() 
{
	var fecha_del = $("#sec_mepa_liquidacion_txt_fecha_del").val();
	var fecha_al = $("#sec_mepa_liquidacion_txt_fecha_hasta").val();

	var data = {
		"accion": "mepa_solicitud_liquidacion_mostrar_movilidad_rango_fecha",
		"param_fecha_del": fecha_del,
		"param_fecha_al": fecha_al
	}
	
	var array_movilidades = [];
	
	$.ajax({
		url: "/sys/set_mepa_solicitud_liquidacion.php",
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
				array_movilidades.push(respuesta.result);
			
				var html = '<option value="">- Seleccione -</option>';

				for (var i = 0; i < array_movilidades[0].length; i++) 
				{
					html += '<option value=' + array_movilidades[0][i].id + "," +array_movilidades[0][i].monto_cierre + '>' + "Correlativo :" + array_movilidades[0][i].num_correlativo + " - Monto: " + array_movilidades[0][i].monto_cierre +'</option>';

				}

				$("#sec_mepa_liquidacion_txt_id_movilidad").html(html).trigger("change");

				return false;
			}
			else if (parseInt(respuesta.http_code) == 400) 
			{
				var html = '<option value="">-- Seleccione --</option>';
				$("#sec_mepa_liquidacion_txt_id_movilidad").html(html).trigger("change");

				return false;

			}
		},
		error: function() {}
	});
}

function liquidacion_sumar_refrescar_monto()
{
	let importe_total_detalle = $("#mepa_solicitud_liquidacion_detalle_total_importe").val();
	
	var suma_refrescar_monto_total = 0;

	suma_refrescar_monto_total += parseFloat(importe_total_detalle);
	
	if($("#check_agg_liquidacion_movilidad").prop('checked') == true)
	{
		
		var selectValor = $("#sec_mepa_liquidacion_txt_id_movilidad").val();

		if(selectValor == 0)
		{
			alertify.error('Tiene que seleccionar una solicitud de movilidad',5);
			return false;
		}
		else
		{
			let dividir_select = selectValor.split(',');

			var monto_select =  dividir_select[1];

			suma_refrescar_monto_total += parseFloat(monto_select);
		}
	}

	document.getElementById("sec_mepa_solicitud_liquidacion_monto_total_liquidacion").innerHTML = parseFloat(suma_refrescar_monto_total).toFixed(2);
}

function guardar_mepa_solicitud_liquidacion()
{
	
	var txt_asignacion_zona_id = $('#param_asignacion_zona_id').val();
	var txt_liquidacion_fecha_del = $('#sec_mepa_liquidacion_txt_fecha_del').val();
	var txt_liquidacion_fecha_hasta = $('#sec_mepa_liquidacion_txt_fecha_hasta').val();
	let monto_total_liquidacion = $("#mepa_solicitud_liquidacion_detalle_total_importe").val();
	var txt_motivo_cerrar = $('#mepa_detalle_atencion_liquidacion_motivo_cerrar_jefe').val().trim();
	var id_asignacion_usuario = $('#id_asignacion_usuario').val();

	var incluir_solicitud_moviliad = 0;
	var ultima_caja_chica = 0;

	if(txt_liquidacion_fecha_del == "")
	{
		alertify.error('Falta seleccionar Fecha Del.',5);
		$("#sec_mepa_liquidacion_txt_fecha_del").focus();
		setTimeout(function() {
			$('.sec_mepa_liquidacion_txt_fecha_del').select2('open');
		}, 500);

		return false;
	}

	if(txt_liquidacion_fecha_hasta == "")
	{
		alertify.error('Falta seleccionar Fecha Al.',5);
		$("#sec_mepa_liquidacion_txt_fecha_hasta").focus();
		setTimeout(function() {
			$('.sec_mepa_liquidacion_txt_fecha_hasta').select2('open');
		}, 500);
		
		return false;
	}
	
	var sec_mepa_liquidacion_txt_id_movilidad = $('#sec_mepa_liquidacion_txt_id_movilidad').val();

	let dividir_select = sec_mepa_liquidacion_txt_id_movilidad.split(',');
	var id_movilidad_select =  dividir_select[0];
	var monto_movilidad_select =  dividir_select[1];
	var txt_titulo_pregunta = "";

	if($("#check_agg_liquidacion_movilidad").prop('checked') == true)
	{
		var selectValor = $("#sec_mepa_liquidacion_txt_id_movilidad").val();

		if(selectValor == 0)
		{
			alertify.error('Tiene que seleccionar una solicitud de movilidad',5);
			return false;
		}

		incluir_solicitud_moviliad = 1;
	}

	if($("#mepa_solicitud_liquidacion_ultima_liquidacion").prop('checked') == true)
	{
		if(txt_motivo_cerrar.length == 0)
		{
			alertify.error('Ingrese el motivo del cierre',5);
			$("#mepa_detalle_atencion_liquidacion_motivo_cerrar_jefe").focus();
			return false;
		}

		ultima_caja_chica = 1;
		txt_titulo_pregunta = "¿Está seguro de registrar la ultima caja chica y cerrar tu fondo de asignación?";
	}
	else
	{
		txt_titulo_pregunta = "¿Está seguro de registrar la caja chica?";
	}

	if(monto_total_liquidacion == 0 && incluir_solicitud_moviliad == 0)
	{
		alertify.error('Ingrese al menos un detalle',5);
		return false;
	}
	
	swal(
	{
		title: txt_titulo_pregunta,
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
			var dataForm = new FormData();
			dataForm.append("accion","guardar_solicitud_liquidacion_caja_chica");
			dataForm.append("txt_asignacion_zona_id", txt_asignacion_zona_id);
			dataForm.append("txt_liquidacion_fecha_del", txt_liquidacion_fecha_del);
			dataForm.append("txt_liquidacion_fecha_hasta", txt_liquidacion_fecha_hasta);
			dataForm.append("monto_total_liquidacion", monto_total_liquidacion);
			dataForm.append("incluir_solicitud_moviliad", incluir_solicitud_moviliad);
			dataForm.append("id_movilidad", id_movilidad_select);
			dataForm.append("id_asignacion_usuario", id_asignacion_usuario);
			dataForm.append("ultima_caja_chica", ultima_caja_chica);
			dataForm.append("txt_motivo_cerrar", txt_motivo_cerrar);
			//auditoria_send({ "proceso": "guardar_mepa_solicitud_liquidacion", "data": dataForm });

			$.ajax({
				url: "sys/set_mepa_solicitud_liquidacion.php",
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
					        window.location.href = "?sec_id=mepa&sub_sec_id=caja_chica";
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=caja_chica";
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

$("#mepa_solicitud_liquidacion_ultima_liquidacion").change(function()
{
	if($(this).prop('checked') == true)
	{
		$("#mepa_solicitud_liquidacion_div_motivo_ultima_caja_chica").show();
	}
	else
	{
		$("#mepa_solicitud_liquidacion_div_motivo_ultima_caja_chica").hide();	
	}
})
