//INICIO FUNCIONES INICIALIZADOS
function sec_mepa_detalle_atencion_liquidacion()
{	
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_detalle_atencion_liquidacion_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA


	$(".detalle_liquidacion_empresa").select2();
	$(".detalle_liquidacion_ruc").validar_numerico_decimales({decimales : 0});
	$(".detalle_liquidacion_tasa_igv").validar_numerico_decimales({decimales : 0});
	$(".detalle_liquidacion_codigo_provision").validar_numerico_decimales({decimales : 0});
	$("#modal_detalle_liquidacion").off("shown.bs.modal").on("shown.bs.modal",function(){
		if ($(".modal-backdrop").length > -1) {
        	$(".modal-backdrop").not(':first').remove();
    	}
		$("input:visible:first",$(this)).focus();
	});
	$(document).on("keypress", ".detalle_liquidacion_ruc", function(e){
		if(e.which === 13){
			e.preventDefault();
			var tr = $(this).closest("tr");
			$(".btn_detalle_liquidacion_guardar",tr).click();
		}
	});

	$(document).on("keypress", ".detalle_liquidacion_tasa_igv", function(e){
		if(e.which === 13){
			e.preventDefault();
			var tr = $(this).closest("tr");
			$(".btn_detalle_liquidacion_tasa_igv_guardar",tr).click();
		}
	});

	$('#centro_costo').mask('0000');
	$('#mepa_detalle_atencion_liquidacion_num_correlativo_liquidacion').mask('00000');
	$('.mepa_detalle_atencion_liquidacion_txt_add_fila').mask('000');
	
	$("#importe").on({
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

	$("#tasa_igv").on({
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

	$("#monto").on({
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
}
//FIN FUNCIONES INICIALIZADOS

$("#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_jefe").on("change", function()
{

	var selectValor = $(this).val();

	if(selectValor == "7")
	{
		// MOSTRAR CAJA DE COMENTARIO DE RECHAZO
		$("#mepa_detalle_atencion_liquidacion_div_motivo_cerrar_caja_jefe").hide();
		$("#mepa_detalle_atencion_liquidacion_div_motivo_rechazo_jefe").show();
	}
	else if(selectValor == "12")
	{
		// MOSTRAR CAJA DE COMENTARIO DE RECHAZO
		$("#mepa_detalle_atencion_liquidacion_div_motivo_rechazo_jefe").hide();
		$("#mepa_detalle_atencion_liquidacion_div_motivo_cerrar_caja_jefe").show();
	}
	else
	{
		// OCULTAR CAJA DE RECHAZO PORQUE NO SE PERMITIRA INGRESAR UN MOTIVO CUANDO SE APRUEBA
		$("#mepa_detalle_atencion_liquidacion_div_motivo_rechazo_jefe").hide();
		$("#mepa_detalle_atencion_liquidacion_div_motivo_cerrar_caja_jefe").hide();
	}
})

function btn_mepa_detalle_atencion_guardar_atencion_jefe()
{
	
	var mepa_detalle_atencion_liquidacion_id = $('#mepa_detalle_atencion_liquidacion_id').val();
	var txt_situacion = $('#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_jefe').val();
	var txt_motivo_rechazo = $('#sec_mepa_detalle_atencion_liquidacion_form_txt_motivo_rechazo_jefe').val().trim();
	var txt_motivo_cerrar = $('#sec_mepa_detalle_atencion_liquidacion_form_txt_motivo_cerrar_jefe').val().trim();

	var txt_titulo_pregunta = "";

	if(txt_situacion == 0)
	{
		alertify.error('Seleccione Situacion',5);
		$("#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_jefe").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_jefe').select2('open');
		}, 200);
		return false;
	}
	else if(txt_situacion == 7)
	{
		if(txt_motivo_rechazo.length == 0)
		{
			alertify.error('Ingrese el motivo del rechazo',5);
			$("#sec_mepa_detalle_atencion_liquidacion_form_txt_motivo_rechazo_jefe").focus();
			return false;
		}
	}
	else if(txt_situacion == 12)
	{
		if(txt_motivo_cerrar.length == 0)
		{
			alertify.error('Ingrese el motivo del cierre',5);
			$("#sec_mepa_detalle_atencion_liquidacion_form_txt_motivo_cerrar_jefe").focus();
			return false;
		}
	}

	if(txt_situacion == 6)
	{
		txt_titulo_pregunta = "Aprobar";
	}
	else if(txt_situacion == 12)
	{
		txt_titulo_pregunta = "Aprobar y Cerrar";
	}
	else if(txt_situacion == 7)
	{
		txt_titulo_pregunta = "Rechazar";
	}
	else if(txt_situacion == 13)
	{
		txt_titulo_pregunta = "dar de baja";
	}

	swal(
	{
		title: '¿Está seguro de '+txt_titulo_pregunta+' la Solicitud?',
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
			var dataForm = new FormData($("#sec_btn_mepa_detalle_atencion_liquidacion_jefe_guardar_atencion")[0]);
			dataForm.append("accion","guardar_detalle_atencion_liquidacion_atencion_jefe");
			dataForm.append("mepa_detalle_atencion_liquidacion_id", mepa_detalle_atencion_liquidacion_id);
			dataForm.append("txt_situacion_jefe", txt_situacion);
			dataForm.append("txt_motivo_rechazo_jefe", txt_motivo_rechazo);
			dataForm.append("txt_motivo_cerrar_jefe", txt_motivo_cerrar);

			$.ajax({
				url: "sys/set_mepa_detalle_atencion_liquidacion.php",
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
					auditoria_send({ "respuesta": "sec_btn_mepa_detalle_atencion_liquidacion_jefe_guardar_atencion", "data": respuesta });
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


$("#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_contabilidad").on("change", function()
{
	
	var selectValor = $(this).val();

	if(selectValor == "7")
	{
		// MOSTRAR CAJA DE COMENTARIO DE RECHAZO
		$("#mepa_detalle_atencion_liquidacion_div_motivo_rechazo_contabilidad").show();
		$("#mepa_detalle_atencion_liquidacion_div_correos_adjuntos").show();
		$("#mepa_detalle_atencion_liquidacion_table_correos_adjuntos").show();
	}
	else
	{
		// OCULTAR CAJA DE RECHAZO PORQUE NO SE PERMITIRA INGRESAR UN MOTIVO CUANDO SE APRUEBA
		$("#mepa_detalle_atencion_liquidacion_div_motivo_rechazo_contabilidad").hide();
		$("#mepa_detalle_atencion_liquidacion_div_correos_adjuntos").hide();
		$("#mepa_detalle_atencion_liquidacion_table_correos_adjuntos").hide();
	}
})

var contador_fila_correo_detalle_table = 0;
var detalles_table_correo_detalle = 0;

function mepa_detalle_atencion_liquidacion_agregar_correos()
{
	var fila ='<tr id="fila_correo_detalle_table'+contador_fila_correo_detalle_table+'">'+
		'<td>'+
			'<button type="button" class="btn btn-danger btn-xs" id="boton_guardar_contratos" onclick="mepa_detalle_atencion_liquidacion_eliminar_detalle_correo('+contador_fila_correo_detalle_table+');">'+
				'<span class="glyphicon glyphicon-remove"></span>'+
			'</button>'+
		'</td>'+
		'<td>'+
			'<input type="text" name="txt_correo" class="data_correos_adjuntos form-control">'+
		'</td>'+
	'</tr>';

	contador_fila_correo_detalle_table ++;
	detalles_table_correo_detalle ++;

	$("#correos_detalle_table").append(fila);
}

function mepa_detalle_atencion_liquidacion_eliminar_detalle_correo(indice)
{
	$("#fila_correo_detalle_table" + indice).remove();
	detalles_table_correo_detalle = detalles_table_correo_detalle - 1;
}

function btn_mepa_detalle_atencion_guardar_atencion_contabilidad()
{
	
	var mepa_detalle_atencion_liquidacion_id = $('#mepa_detalle_atencion_liquidacion_id').val();
	var mepa_detalle_atencion_liquidacion_empresa_id = $('#mepa_detalle_atencion_liquidacion_empresa_id').val();
	var txt_situacion_contabilidad = $('#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_contabilidad').val();
	var txt_motivo_rechazo_contabilidad = $('#sec_mepa_detalle_atencion_liquidacion_form_txt_motivo_rechazo_contabilidad').val();
	var mepa_detalle_atencion_liquidacion_situacion_cerrar_caja_chica = $('#mepa_detalle_atencion_liquidacion_situacion_cerrar_caja_chica').val();

	var array_input = new Array();
    var input_value = document.getElementsByClassName('data_correos_adjuntos');

	var txt_titulo_pregunta = "";

	if(txt_situacion_contabilidad == 0)
	{
		alertify.error('Seleccione Situacion',5);
		$("#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_contabilidad").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_contabilidad').select2('open');
		}, 200);
		return false;
	}

	if(txt_situacion_contabilidad == 7)
	{
		if(txt_motivo_rechazo_contabilidad.length == 0)
		{
			alertify.error('Ingrese el motivo del rechazo',5);
			$("#sec_mepa_detalle_atencion_liquidacion_form_txt_motivo_rechazo_contabilidad").focus();
			return false;
		}
	}

	if(input_value.length != 0)
	{
		for (var i = 0; i < input_value.length; i++) 
	    {
	        if(input_value[i].value == "")
	        {
	        	alertify.error('Ingrese correo en el detalle',5);
				return false;
	        }
	        array_input.push(input_value[i].value);
	    }
	}
	
	if(txt_situacion_contabilidad == 6)
	{
		txt_titulo_pregunta = "Aprobar";
	}
	else if(txt_situacion_contabilidad == 7)
	{
		txt_titulo_pregunta = "Rechazar";
	}

	swal(
	{
		title: '¿Está seguro de '+txt_titulo_pregunta+' la Solicitud?',
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
			var dataForm = new FormData($("#sec_btn_mepa_detalle_atencion_liquidacion_contabilidad_guardar_atencion")[0]);
			dataForm.append("accion","guardar_detalle_atencion_liquidacion_atencion_contabilidad");
			dataForm.append("mepa_detalle_atencion_liquidacion_id", mepa_detalle_atencion_liquidacion_id);
			dataForm.append("mepa_detalle_atencion_liquidacion_empresa_id", mepa_detalle_atencion_liquidacion_empresa_id);
			dataForm.append("mepa_detalle_atencion_liquidacion_situacion_cerrar_caja_chica", mepa_detalle_atencion_liquidacion_situacion_cerrar_caja_chica);
			dataForm.append("txt_situacion_contabilidad", txt_situacion_contabilidad);
			dataForm.append("txt_motivo_rechazo_contabilidad", txt_motivo_rechazo_contabilidad);
			dataForm.append("txt_correo_adjuntos", JSON.stringify(array_input));

			$.ajax({
				url: "sys/set_mepa_detalle_atencion_liquidacion.php",
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
					auditoria_send({ "respuesta": "sec_btn_mepa_detalle_atencion_liquidacion_contabilidad_guardar_atencion", "data": respuesta });
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
					        window.location.href = "?sec_id=mepa&sub_sec_id=detalle_atencion_liquidacion&id="+respuesta.mepa_detalle_atencion_liquidacion_id;
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=detalle_atencion_liquidacion&id="+respuesta.mepa_detalle_atencion_liquidacion_id;
						}, 5000);

						return true;
					} 
					else {
						swal({
							title: "Error al guardar la Solicitud",
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

$("#firmar_checkbox").on("change",function(){
	if($(this).prop("checked") == true )
	{
		$(".generar_pdf_firmado").show();
	}
	else
	{
		$(".generar_pdf_firmado").hide();
	}
})

$("#form_pdf").on("click", ".vista_previa_firma_img", function(e){
	e.preventDefault();
	var src = $(this).attr("data-src");
	$("#vista_previa_modal #img01").attr("src","files_bucket/mepa/firmas/" + src);

	$("#vista_previa_modal").off("shown.bs.modal").on("shown.bs.modal",function(){
		if ($(".modal-backdrop").length > -1) {
        	$(".modal-backdrop").not(':first').remove();
    	}
		$("#img01").imgViewer2();
	});
	$("#vista_previa_modal").off("hide.bs.modal").on("hide.bs.modal",function(){
		$("#img01").imgViewer2("destroy");
	});
	$("#vista_previa_modal").modal("show");

})

$("#form_pdf").submit(function(e){
	e.preventDefault();
	let url = "sys/set_mepa_detalle_atencion_liquidacion.php";
	var dataForm = new FormData(this);

    $.ajax({
        url: url,
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
        	
        	var resp = JSON.parse(data);
        	window.open(resp.file_pdf, '_blank').focus();
        	location.reload();
        }
    });
});


$("#modal_detalle_liquidacion").on("hidden.bs.modal",function(){
	$("#form_detalle_liquidacion input:not('#mepa_caja_chica_liquidacion_id, #asignacion_id')").val("");
	$("#form_detalle_liquidacion textarea").val("");
	$("#form_detalle_liquidacion .detalle_archivo, .detalle_archivo_xml").hide();
})

$("#tabla_form_solicitudes_asignacion_detalle").off("click").on("click", ".btn_detalle_liquidacion_guardar", function(){
	
	var tr = $(this).closest("tr");
	var detalle_id = $(this).closest("tr").attr("data-detalle_liquidacion_id");
	var ruc_anterior = $(this).closest("tr").attr("data-detalle_liquidacion_ruc");
	var ruc = $("input[name='ruc']",tr).val();
	var url = "sys/set_mepa_detalle_atencion_liquidacion.php";
	$.ajax({
        url: url,
        type: 'POST',
        data: {
			accion : "guardar_detalle_liquidacion_ruc",
			detalle_id : detalle_id,
			ruc : ruc
        },
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data){
        	
        	var resp = JSON.parse(data);
			if(typeof resp.error != "undefined")
			{
				swal({
					title: resp.titulo,
					text: resp.texto,
					type: 'error',
					confirmButtonText: 'Ok',
					closeOnConfirm: true,
				}, function(isConfirm){
						swal.close();
						setTimeout(function(){
								$("input[name='" + resp.focus + "']", tr).val(ruc_anterior);
						},200);
					}
				);
	    		return false;
	    	}
        	swal({
				title: resp.titulo,
				text: resp.texto,
				type: 'success',
				confirmButtonText: 'Ok',
				closeOnConfirm: true,
			}, function(isConfirm){
				}
			);
        }
    });
})

$("#tabla_form_solicitudes_asignacion_detalle").on("click", ".btn_detalle_liquidacion_tipo_documento_guardar", function(){
	
	var tr = $(this).closest("tr");
	var detalle_id = $(this).closest("tr").attr("data-detalle_liquidacion_id");
	var tipo_documento_anterior = $(this).closest("tr").attr("data_detalle_liquidacion_tipo_documento");
	var tipo_documento = $("select[name='tipo_documento']",tr).val();

	if(tipo_documento == "0")
	{
		alertify.error('Seleccione código provision',5);
		$("#sec_mepa_detalle_atencion_liquidacion_tipo_documento_contable").focus();
		setTimeout(function()
		{
			$('#sec_mepa_detalle_atencion_liquidacion_tipo_documento_contable').select2('open');
		}, 200);
		return false;
	}

	var url = "sys/set_mepa_detalle_atencion_liquidacion.php";
	$.ajax({
        url: url,
        type: 'POST',
        data: {
			accion : "guardar_detalle_liquidacion_tipo_documento",
			detalle_id : detalle_id,
			tipo_documento : tipo_documento
        },
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data){
        	debugger;
        	var resp = JSON.parse(data);
			if(typeof resp.error != "undefined")
			{
				swal({
					title: resp.titulo,
					text: resp.texto,
					type: 'error',
					confirmButtonText: 'Ok',
					closeOnConfirm: true,
				}, function(isConfirm){
						swal.close();
						setTimeout(function(){
								$("input[name='" + resp.focus + "']", tr).val(tipo_documento_anterior);
						},200);
					}
				);
	    		return false;
	    	}
        	swal({
				title: resp.titulo,
				text: resp.texto,
				type: 'success',
				confirmButtonText: 'Ok',
				closeOnConfirm: true,
			}, function(isConfirm){
				}
			);
        }
    });
})

$("#tabla_form_solicitudes_asignacion_detalle").on("click", ".btn_detalle_liquidacion_codigo_provision_guardar", function(){
	
	var tr = $(this).closest("tr");
	var detalle_id = $(this).closest("tr").attr("data-detalle_liquidacion_id");
	var codigo_provision_anterior = $(this).closest("tr").attr("data-detalle_liquidacion_codigo_provision");
	var codigo_provision = $("select[name='codigo_provision']",tr).val();

	if(codigo_provision == "0")
	{
		alertify.error('Seleccione código provision',5);
		$("#sec_mepa_detalle_atencion_liquidacion_codigo_provision_contable").focus();
		setTimeout(function()
		{
			$('#sec_mepa_detalle_atencion_liquidacion_codigo_provision_contable').select2('open');
		}, 200);
		return false;
	}

	var url = "sys/set_mepa_detalle_atencion_liquidacion.php";
	$.ajax({
        url: url,
        type: 'POST',
        data: {
			accion : "guardar_detalle_liquidacion_codigo_provision",
			detalle_id : detalle_id,
			codigo_provision : codigo_provision
        },
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data){
        	
        	var resp = JSON.parse(data);
			if(typeof resp.error != "undefined")
			{
				swal({
					title: resp.titulo,
					text: resp.texto,
					type: 'error',
					confirmButtonText: 'Ok',
					closeOnConfirm: true,
				}, function(isConfirm){
						swal.close();
						setTimeout(function(){
								$("input[name='" + resp.focus + "']", tr).val(codigo_provision_anterior);
						},200);
					}
				);
	    		return false;
	    	}
        	swal({
				title: resp.titulo,
				text: resp.texto,
				type: 'success',
				confirmButtonText: 'Ok',
				closeOnConfirm: true,
			}, function(isConfirm){
				}
			);
        }
    });
})

$("#tabla_form_solicitudes_asignacion_detalle").on("click", ".btn_detalle_liquidacion_serie_comprobante_guardar", function(){
	
	var tr = $(this).closest("tr");
	var detalle_id = $(this).closest("tr").attr("data-detalle_liquidacion_id");
	var serie_comprobante_anterior = $(this).closest("tr").attr("data_detalle_liquidacion_serie_comprobante");
	var serie_comprobante = $("input[name='serie_comprobante']",tr).val();
	var url = "sys/set_mepa_detalle_atencion_liquidacion.php";
	$.ajax({
        url: url,
        type: 'POST',
        data: {
			accion : "guardar_detalle_liquidacion_serie_comprobante",
			detalle_id : detalle_id,
			serie_comprobante : serie_comprobante
        },
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data){
        	
        	var resp = JSON.parse(data);
			if(typeof resp.error != "undefined")
			{
				swal({
					title: resp.titulo,
					text: resp.texto,
					type: 'error',
					confirmButtonText: 'Ok',
					closeOnConfirm: true,
				}, function(isConfirm){
						swal.close();
						setTimeout(function(){
								$("input[name='" + resp.focus + "']", tr).val(serie_comprobante_anterior);
						},200);
					}
				);
	    		return false;
	    	}
        	swal({
				title: resp.titulo,
				text: resp.texto,
				type: 'success',
				confirmButtonText: 'Ok',
				closeOnConfirm: true,
			}, function(isConfirm){
				}
			);
        }
    });
})

$("#tabla_form_solicitudes_asignacion_detalle").on("click", ".btn_detalle_liquidacion_num_cuenta_guardar", function(){
	
	var tr = $(this).closest("tr");
	var detalle_id = $(this).closest("tr").attr("data-detalle_liquidacion_id");
	var num_comprobante_anterior = $(this).closest("tr").attr("data_detalle_liquidacion_num_comprobante");
	var num_comprobante = $("input[name='num_comprobante']",tr).val();
	var url = "sys/set_mepa_detalle_atencion_liquidacion.php";
	$.ajax({
        url: url,
        type: 'POST',
        data: {
			accion : "guardar_detalle_liquidacion_num_comprobante",
			detalle_id : detalle_id,
			num_comprobante : num_comprobante
        },
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data){
        	
        	var resp = JSON.parse(data);
			if(typeof resp.error != "undefined")
			{
				swal({
					title: resp.titulo,
					text: resp.texto,
					type: 'error',
					confirmButtonText: 'Ok',
					closeOnConfirm: true,
				}, function(isConfirm){
						swal.close();
						setTimeout(function(){
								$("input[name='" + resp.focus + "']", tr).val(num_comprobante_anterior);
						},200);
					}
				);
	    		return false;
	    	}
        	swal({
				title: resp.titulo,
				text: resp.texto,
				type: 'success',
				confirmButtonText: 'Ok',
				closeOnConfirm: true,
			}, function(isConfirm){
				}
			);
        }
    });
})

$("#tabla_form_solicitudes_asignacion_detalle").on("click", ".btn_detalle_liquidacion_centro_costo_guardar", function(){
	
	var tr = $(this).closest("tr");
	var detalle_id = $(this).closest("tr").attr("data-detalle_liquidacion_id");
	var centro_costo_anterior = $(this).closest("tr").attr("data_detalle_liquidacion_centro_costo");
	var centro_costo = $("input[name='centro_costo']",tr).val();
	var url = "sys/set_mepa_detalle_atencion_liquidacion.php";
	$.ajax({
        url: url,
        type: 'POST',
        data: {
			accion : "guardar_detalle_liquidacion_centro_costo",
			detalle_id : detalle_id,
			centro_costo : centro_costo
        },
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data){
        	
        	var resp = JSON.parse(data);
			if(typeof resp.error != "undefined")
			{
				swal({
					title: resp.titulo,
					text: resp.texto,
					type: 'error',
					confirmButtonText: 'Ok',
					closeOnConfirm: true,
				}, function(isConfirm){
						swal.close();
						setTimeout(function(){
								$("input[name='" + resp.focus + "']", tr).val(centro_costo_anterior);
						},200);
					}
				);
	    		return false;
	    	}
        	swal({
				title: resp.titulo,
				text: resp.texto,
				type: 'success',
				confirmButtonText: 'Ok',
				closeOnConfirm: true,
			}, function(isConfirm){
				}
			);
        }
    });
})

$("#tabla_form_solicitudes_asignacion_detalle").on("click", ".btn_detalle_liquidacion_tasa_igv_guardar", function(){
	
	var tr = $(this).closest("tr");
	var detalle_id = $(this).closest("tr").attr("data-detalle_liquidacion_id");
	var tasa_igv_anterior = $(this).closest("tr").attr("data-detalle_liquidacion_tasa_igv");
	var tasa_igv = $("input[name='tasa_igv']",tr).val();
	var url = "sys/set_mepa_detalle_atencion_liquidacion.php";
	$.ajax({
        url: url,
        type: 'POST',
        data: {
			accion : "guardar_detalle_liquidacion_tasa_igv",
			detalle_id : detalle_id,
			tasa_igv : tasa_igv
        },
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data){
        	
        	var resp = JSON.parse(data);
			if(typeof resp.error != "undefined")
			{
				swal({
					title: resp.titulo,
					text: resp.texto,
					type: 'error',
					confirmButtonText: 'Ok',
					closeOnConfirm: true,
				}, function(isConfirm){
						swal.close();
						setTimeout(function(){
								$("input[name='" + resp.focus + "']", tr).val(ruc_anterior);
						},200);
					}
				);
	    		return false;
	    	}
        	swal({
				title: resp.titulo,
				text: resp.texto,
				type: 'success',
				confirmButtonText: 'Ok',
				closeOnConfirm: true,
			}, function(isConfirm){
				}
			);
        }
    });
})


$("#tabla_form_solicitudes_asignacion_detalle").on("click", ".detalle_liquidacion_delete", function(){
	
	var detalle_id = $(this).closest("tr").attr("data-detalle_liquidacion_id");
	var id = $(this).closest("tr").attr("data-liquidacion_id");
	var asignacion_id = $(this).closest("tr").attr("data-asignacion_id");
	var importe = $(this).closest("tr").attr("data-detalle_liquidacion_importe");
	var nombre_file = $(this).closest("tr").attr("data-nombre_file");
	var url = "sys/set_mepa_detalle_atencion_liquidacion.php";
	swal({
			title: "¿Está Seguro?",
			text: "Se eliminará el registro.",
			type: "warning",
			html: true,
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Si!",
			cancelButtonText: "No!",
			closeOnConfirm: true,
			closeOnCancel: true
		},
		function (isConfirm) {
			if (isConfirm) {
				$.ajax({
			        url: url,
			        type: 'POST',
			        data: {
			        	accion : "eliminar_detalle_liquidacion",
			        	detalle_id : detalle_id,
			        	asignacion_id : asignacion_id,
			        	importe : importe,
			        	nombre_file : nombre_file,
			        	id : id
			        },
			        beforeSend: function() {
			            loading("true");
			        },
			        success: function(data){
			        	
			        	var resp = JSON.parse(data);
			        	if(parseInt(resp.http_code) == 200)
			        	{
							swal({
								title: "Detalle Liquidación",
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
		}
	);
})


$("#tabla_form_solicitudes_asignacion_detalle").on("click", ".detalle_liquidacion_edit", function(){
	$.ajax({
        url: "sys/set_mepa_detalle_atencion_liquidacion.php",
        type: 'POST',
        data: {
        	accion : "get_detalle_liquidacion",
        	detalle_id : $(this).closest("tr").attr("data-detalle_liquidacion_id")
        },
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data){
        	
        	var resp = JSON.parse(data);
        	$.each(resp.detalle_liquidacion,function(name,val)
        	{
        		if(name == "nombre_file")
        		{
        			return true;
        		}

        		if(name == "nombre_file_xml")
        		{
        			return true;
        		}
        		
        		$( "#"+name,$("#modal_detalle_liquidacion form")).val(val);
        		
        	});
        	
        	$("#modal_detalle_liquidacion .detalle_archivo")
        		.attr("href","/files_bucket/mepa/solicitudes/liquidacion/" + resp.detalle_liquidacion.nombre_file)
        		.attr("data-nombre_file_actual",resp.detalle_liquidacion.nombre_file)
        		.show();
        	
        	if(resp.detalle_liquidacion.nombre_file_xml != null)
        	{
        		$("#modal_detalle_liquidacion .detalle_archivo_xml")
        		.attr("href","/files_bucket/mepa/solicitudes/liquidacion/" + resp.detalle_liquidacion.nombre_file_xml)
        		.attr("data-nombre_file_xml_actual",resp.detalle_liquidacion.nombre_file_xml)
        		.show();
        	}
        	else
        	{
        		$("#modal_detalle_liquidacion .detalle_archivo_xml").hide();
        	}
        	
			$("#modal_detalle_liquidacion .btn_guardar").hide();
			$("#modal_detalle_liquidacion .btn_editar").show();
			$("#title_modal_detalle_liquidacion").text("Editar detalle liquidación");
			$("#modal_detalle_liquidacion").modal("show");
        }
    });

})

$("#tabla_form_solicitudes_asignacion_detalle").on("click", ".btn_detalle_liquidacion_add_detalle_fila", function(){
	
	var tr = $(this).closest("tr");
	var detalle_id = $(this).closest("tr").attr("data-detalle_liquidacion_id");
	var txt_add_fila = $("input[name='txt_add_fila']",tr).val();
	
	if(txt_add_fila == "")
	{
		alertify.error('Ingrese un número en Agregar Fila',5);
		$(".txt_add_fila").focus();
		return false;
	}

	if(txt_add_fila == "0")
	{
		alertify.error('Ingrese un número diferente a cero (0)',5);
		$(".txt_add_fila").focus();
		return false;
	}

	swal({
		title: "¿Está seguro?",
		text: `Desea agregar ${txt_add_fila} fila(s).`,
		type: "warning",
		html: true,
		showCancelButton: true,
		confirmButtonClass: "btn-danger",
		confirmButtonText: "Si",
		cancelButtonText: "No",
		closeOnConfirm: true,
		closeOnCancel: true
	},
	function (isConfirm) {
		if (isConfirm) {
			$.ajax({
		        url: "sys/set_mepa_detalle_atencion_liquidacion.php",
		        type: 'POST',
		        data: {
		        	accion : "guardar_detalle_liquidacion_add_detalle_fila",
		        	detalle_id : detalle_id,
		        	txt_add_fila : txt_add_fila
		        },
		        beforeSend: function() {
		            loading("true");
		        },
		        success: function(data){
		        	
		        	var resp = JSON.parse(data);
					if(typeof resp.error != "undefined")
					{
						swal({
							title: 'Agregar Detalle Fila',
							text: resp.mensaje,
							type: 'error',
							confirmButtonText: 'Ok',
							closeOnConfirm: true,
						}, function(isConfirm){
								swal.close();
								setTimeout(function(){
										$("input[name='" + resp.focus + "']", tr).val(txt_add_fila);
								},200);
							}
						);
			    		return false;
			    	}
		        	swal({
						title: 'Agregar Detalle Fila',
						text: resp.mensaje,
						type: 'success',
						confirmButtonText: 'Ok',
						closeOnConfirm: true,
					}, function(isConfirm){
							window.location.reload();
						}
					);
		        },
		        complete: function() {
		            loading(false);
		        }
		    });
		}
	});

	
})

$("#modal_detalle_liquidacion .btn_editar").off("click").on("click",function(){
	
	
	var file_actual = $("#modal_detalle_liquidacion .detalle_archivo").attr("data-nombre_file_actual");
	var file_xml_actual = $("#modal_detalle_liquidacion .detalle_archivo").attr("data-nombre_file_xml_actual");
	
	var importe_modal_detalle_liquidacion = $('#importe').val();
	var asignacion_id = $('#asignacion_id').val();

    if(importe_modal_detalle_liquidacion == "0.00")
	{
		alertify.error('No se permite 0.00',5);
		$("#importe").focus();
		return false;
	}

	var dataForm = new FormData($("#form_detalle_liquidacion")[0]);
    dataForm.append("accion","editar_detalle_liquidacion");
    dataForm.append("nombre_file_actual",file_actual);
    dataForm.append("nombre_file_xml_actual",file_xml_actual);
    dataForm.append("asignacion_id",asignacion_id);
	
	$.ajax({
	    url: "sys/set_mepa_detalle_atencion_liquidacion.php",
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
	    	
	    	var resp = JSON.parse(data);
	    	if(typeof resp.error !="undefined"){
	        	swal({
					title: 'Editar Detalle Liquidación',
					text: resp.mensaje,
					type: 'error',
					confirmButtonText: 'Ok',
					closeOnConfirm: true,
				}, function(isConfirm){
						swal.close();
						setTimeout(function(){
								$("#"+resp.focus).focus();
						},200);
					}
				);
	    		return false;
	    	}
	    	swal({
				title: 'Detalle Liquidación',
				text: resp.mensaje,
				type: 'success',
				confirmButtonText: 'Ok',
				closeOnConfirm: true,
			}, function(isConfirm){
					swal.close();
					window.location.reload();
				}
			
			);
	    }
	});
})


$(".btn_agregar_detalle_liquidacion").off("click").on("click",function(){

	var fecha_desde_cabecera = $("#sec_mepa_liquidacion_txt_fecha_desde").val();
	var fecha_hasta_cabecera = $("#sec_mepa_liquidacion_txt_fecha_hasta").val();

	$('#fecha_documento').attr("min", fecha_desde_cabecera);
	$("#fecha_documento").attr("max", fecha_hasta_cabecera);

	$("#modal_detalle_liquidacion .btn_editar").hide();
	$("#modal_detalle_liquidacion .btn_guardar").show();
	$("#title_modal_detalle_liquidacion").text("Agregar detalle liquidación");
	$("#modal_detalle_liquidacion").modal("show");
})


$("#modal_detalle_liquidacion .btn_guardar").off("click").on("click",function(){
	
    var dataForm = new FormData($("#form_detalle_liquidacion")[0]);

    var importe_modal_detalle_liquidacion = $('#importe').val();
    
    if(importe_modal_detalle_liquidacion == "0.00")
	{
		alertify.error('No se permite 0.00 en el Importe',5);
		$("#importe").focus();
		return false;
	}

	dataForm.append("accion","agregar_detalle_liquidacion");
	$.ajax({
        url: "sys/set_mepa_detalle_atencion_liquidacion.php",
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
        	
        	var resp = JSON.parse(data);
        	if(typeof resp.error != "undefined")
        	{
	        	swal({
					title: 'Agregar Detalle Liquidación',
					text: resp.mensaje,
					type: 'error',
					confirmButtonText: 'Ok',
					closeOnConfirm: true,
				}, function(isConfirm){
						swal.close();
						setTimeout(function(){
							$("#"+resp.focus).focus();
						},200);
					}
				);
        		return false;
        	}
        	swal({
				title: 'Detalle Liquidación',
				text: resp.mensaje,
				type: 'success',
				confirmButtonText: 'Ok',
				closeOnConfirm: true,
			}, function(isConfirm){
					swal.close();
					window.location.reload();
				}
			
			);
        }
    });
})

function mepa_detalle_atencion_liquidacion_ver_archivo(tipo_documento, ruta_file) 
{
	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf') 
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" height="525"></iframe>';
		$('#mepa_detalle_atencion_liquidacion_ver_archivo_visor_pdf').html(htmlModal);

		$('#mepa_atencion_liquidacion_div_visor_pdf_modal').modal('show');

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

function mepa_detalle_atencion_liquidacion_concar_excel(liquidacion_id)
{
	
    var data = {
        "accion": "mepa_detalle_atencion_liquidacion_concar_excel",
        "liquidacion_id" : liquidacion_id
    }

    $.ajax({
        url: "/sys/set_mepa_detalle_atencion_liquidacion.php",
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
            if(parseInt(obj.estado_archivo) == 1)
            {
                window.open(obj.ruta_archivo);
                loading(false);
            }
            else if(parseInt(obj.estado_archivo) == 0)
            {
                swal({
                    title: "Error al Generar el Concar",
                    text: obj.ruta_archivo,
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

function mepa_detalle_atencion_liquidacion_concar_provision_gastos_excel(liquidacion_id)
{
	
    var data = {
        "accion": "mepa_detalle_atencion_liquidacion_concar_provision_gastos_excel",
        "liquidacion_id" : liquidacion_id
    }

    $.ajax({
        url: "/sys/set_mepa_detalle_atencion_liquidacion.php",
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
            if(parseInt(obj.estado_archivo) == 1)
            {
                window.open(obj.ruta_archivo);
                loading(false);
            }
            else if(parseInt(obj.estado_archivo) == 0)
            {
                swal({
                    title: "Error al Generar el Concar - Provisión",
                    text: obj.ruta_archivo,
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

function mepa_detalle_atencion_liquidacion_enviar_notificacion_de_correciones(liquidacion_id, es_contabilidad)
{
	
	var txt_titulo_pregunta = "";

	var data = {
		"accion": "mepa_detalle_atencion_liquidacion_enviar_notificacion_de_correciones",
		"liquidacion_id": liquidacion_id,
		"es_contabilidad": es_contabilidad
	}

	if(es_contabilidad == 0)
	{
		txt_titulo_pregunta = "Jefe Inmediato";
	}
	else if(es_contabilidad == 1)
	{
		txt_titulo_pregunta = "Asistente Contable";
	}
	else
	{
		alertify.error('Ocurrio un error',5);
		return false;
	}

	swal({
        html:true,
        title: 'Notificar al ' +txt_titulo_pregunta,
        text: "¿Desea enviar el email?",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1cb787',
        cancelButtonColor: '#d56d6d',
        confirmButtonText: 'Si, enviar email',
        cancelButtonText: 'Cancelar',
        closeOnConfirm: false,
        //,showLoaderOnConfirm: true
    }, function(){
    	auditoria_send({ "proceso": "mepa_detalle_atencion_liquidacion_enviar_notificacion_de_correciones", "data": data });
		$.ajax({
			url: "/sys/set_mepa_detalle_atencion_liquidacion.php",
			type: 'POST',
			data: data,
			beforeSend: function() 
			{
				loading("true");
			},
			complete: function() 
			{
				loading();
			},
			success: function(resp) 
			{

				var respuesta = JSON.parse(resp);
				
				if (parseInt(respuesta.http_code) == 400) 
				{
					swal({
						title: "Error al enviar la notificación",
						text: respuesta.error,
						html:true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
					});
				}
				
				if (parseInt(respuesta.http_code) == 200) 
				{
					swal({
						title: "Envío exitoso",
						text: "La notificación fue enviada exitosamente",
						html:true,
						type: "success",
						timer: 6000,
						closeOnConfirm: false,
						showCancelButton: false
					});
					
					return false;
				}
			},
			error: function() {}
		});
    });

    return false;
}

$(".btn_agregar_detalle_movilidad").off("click").on("click",function(){

	var fecha_del = $("#sec_mepa_movilidad_fecha_del").val();
	var fecha_al = $("#sec_mepa_movilidad_fecha_al").val();

	$('#fecha').attr("min", fecha_del);
	$("#fecha").attr("max", fecha_al);

	$("#modal_detalle_movilidad .btn_editar").hide();
	$("#modal_detalle_movilidad .btn_guardar").show();
	$("#title_modal_detalle_movilidad").text("Agregar detalle movilidad");
	$("#modal_detalle_movilidad").modal("show");
})

$("#modal_detalle_movilidad .btn_guardar").off("click").on("click",function(){
    
    var dataForm = new FormData($("#mepa_detalle_atencion_liquidacion_form_modal_detalle_movilidad")[0]);

    var monto_modal_detalle_movilidad = $('#monto').val();

    if(monto_modal_detalle_movilidad == "0.00")
	{
		alertify.error('No se permite 0.00 en el Monto',5);
		$("#monto").focus();
		return false;
	}

	dataForm.append("accion","agregar_detalle_movilidad");
	$.ajax({
        url: "sys/set_mepa_detalle_atencion_liquidacion.php",
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
        	
        	var resp = JSON.parse(data);
        	if(typeof resp.error != "undefined")
        	{
	        	swal({
					title: 'Agregar Detalle Movilidad',
					text: resp.mensaje,
					type: 'error',
					confirmButtonText: 'Ok',
					closeOnConfirm: true,
				}, function(isConfirm){
						swal.close();
						setTimeout(function(){
							$("#"+resp.focus).focus();
						},200);
					}
				);
        		return false;
        	}
        	swal({
				title: 'Detalle Movilidad',
				text: resp.mensaje,
				type: 'success',
				confirmButtonText: 'Ok',
				closeOnConfirm: true,
			}, function(isConfirm){
					swal.close();
					window.location.reload();
				}
			
			);
        }
    });
})

function sec_mepa_detalle_atencion_liquidacion_eliminar_detalle_movilidad(detalle_movilidad_id, monto, asignacion_id, movilidad_id)
{
	var url = "sys/set_mepa_detalle_atencion_liquidacion.php";

	swal({
		title: "¿Está seguro?",
		text: "Se eliminará el registro",
		type: "warning",
		html: true,
		showCancelButton: true,
		confirmButtonClass: "btn-danger",
		confirmButtonText: "Si",
		cancelButtonText: "No",
		closeOnConfirm: true,
		closeOnCancel: true
	},
	function (isConfirm) {
		if (isConfirm) {
			$.ajax({
		        url: url,
		        type: 'POST',
		        data: {
		        	accion : "sec_mepa_detalle_atencion_liquidacion_eliminar_detalle_movilidad",
		        	detalle_movilidad_id : detalle_movilidad_id,
		        	monto : monto,
		        	asignacion_id : asignacion_id,
		        	movilidad_id : movilidad_id
		        },
		        beforeSend: function() {
		            loading("true");
		        },
		        success: function(data){
		        	
		        	var resp = JSON.parse(data);
		        	if(parseInt(resp.http_code) == 200)
		        	{
						swal({
							title: "Detalle Movilidad",
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

$("#mepa_detalle_atencion_liquidacion_eliminar_liquidacion_motivo_eliminacion").keyup(function ()
{
	$("#mepa_detalle_atencion_liquidacion_eliminar_liquidacion_motivo_eliminacion_cantidad_caracteres").text(100 - $(this).val().length)
});

function mepa_detalle_atencion_liquidacion_eliminar_liquidacion_btn()
{
	
	var mepa_detalle_atencion_liquidacion_id = $('#mepa_detalle_atencion_liquidacion_id').val();
	var txt_situacion = $('#mepa_detalle_atencion_liquidacion_eliminar_liquidacion_situacion').val();
	var txt_motivo = $('#mepa_detalle_atencion_liquidacion_eliminar_liquidacion_motivo_eliminacion').val().trim();
	
	if(txt_situacion == 0)
	{
		alertify.error('Seleccione Situacion',5);
		$("#mepa_detalle_atencion_liquidacion_eliminar_liquidacion_situacion").focus();
		setTimeout(function() 
		{
			$('#mepa_detalle_atencion_liquidacion_eliminar_liquidacion_situacion').select2('open');
		}, 200);
		return false;
	}

	if(txt_motivo == "")
	{
		alertify.error('Ingrese el motivo',5);
		$("#mepa_detalle_atencion_liquidacion_eliminar_liquidacion_motivo_eliminacion").focus();
		return false;
	}

	var txt_titulo = "";
	if(txt_situacion == 13)
	{
		txt_titulo = "¿Está seguro de eliminar?";
	}
	else
	{
		txt_titulo = "¿Está seguro de revertir eliminar?";	
	}
	swal(
	{
		title: txt_titulo,
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
			var dataForm = new FormData($("#sec_btn_mepa_detalle_atencion_liquidacion_jefe_guardar_atencion")[0]);
			dataForm.append("accion","mepa_detalle_atencion_liquidacion_eliminar_liquidacion");
			dataForm.append("mepa_detalle_atencion_liquidacion_id", mepa_detalle_atencion_liquidacion_id);
			dataForm.append("txt_situacion", txt_situacion);
			dataForm.append("txt_motivo", txt_motivo);

			$.ajax({
				url: "sys/set_mepa_detalle_atencion_liquidacion.php",
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
					auditoria_send({ "respuesta": "sec_btn_mepa_detalle_atencion_liquidacion_jefe_guardar_atencion", "data": respuesta });
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
					else {
						swal({
							title: respuesta.titulo,
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

function sec_mepa_detalle_atencion_liquidacion_btn_regresar_etapa_anterior(liquidacion_id, verificar_etapa, situacion_asignacion_id, asignacion_id)
{
	
	var txt_situacion = "";
	var txt_titulo = "";

	if(verificar_etapa == 1)
	{
		txt_titulo = "¿Está seguro de regresar una etapa anterior a nivel jefe?";
		txt_situacion = $('#sec_mepa_detalle_atencion_liquidacion_etapa_verificacion_nivel_jefe').val();

		if(txt_situacion == 0)
		{
			alertify.error('Seleccione Situacion Verificación Nivel Jefe',5);
			$("#sec_mepa_detalle_atencion_liquidacion_etapa_verificacion_nivel_jefe").focus();
			setTimeout(function() 
			{
				$('#sec_mepa_detalle_atencion_liquidacion_etapa_verificacion_nivel_jefe').select2('open');
			}, 200);
			return false;
		}
	}
	else if(verificar_etapa == 2)
	{
		txt_titulo = "¿Está seguro de regresar una etapa anterior a nivel contabilidad?";
		txt_situacion = $('#sec_mepa_detalle_atencion_liquidacion_etapa_verificacion_nivel_contabilidad').val();

		if(txt_situacion == 0)
		{
			alertify.error('Seleccione Situacion Verificación Nivel Contabilidad',5);
			$("#sec_mepa_detalle_atencion_liquidacion_etapa_verificacion_nivel_contabilidad").focus();
			setTimeout(function() 
			{
				$('#sec_mepa_detalle_atencion_liquidacion_etapa_verificacion_nivel_contabilidad').select2('open');
			}, 200);
			return false;
		}
	}
	else
	{
		alertify.error('No se encontro la situacion de la etapa.',5);
		return false;
	}

	swal(
	{
		title: txt_titulo,
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
				"accion": "sec_mepa_detalle_atencion_liquidacion_btn_regresar_etapa_anterior",
				"liquidacion_id": liquidacion_id,
				"verificar_etapa": verificar_etapa,
				"txt_situacion": txt_situacion,
				"situacion_asignacion_id": situacion_asignacion_id,
				"asignacion_id": asignacion_id
			}

			$.ajax({
				url: "sys/set_mepa_detalle_atencion_liquidacion.php",
				type: 'POST',
				data: dataForm,
				beforeSend: function()
				{
					loading(true);
				},
				complete: function() 
				{
					loading();
				},
				success: function(data)
				{

					var respuesta = JSON.parse(data);
					auditoria_send({ "respuesta": "sec_btn_mepa_detalle_atencion_liquidacion_jefe_guardar_atencion", "data": respuesta });
					
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
							title: "Error al registrar",
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
	});
}

function sec_mepa_detalle_atencion_btn_dar_de_baja()
{
	
	var mepa_detalle_atencion_liquidacion_id = $('#mepa_detalle_atencion_liquidacion_id').val();
	var txt_situacion = $('#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_dar_de_baja').val();
	var txt_tipo = $('#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_tipo').val();
	var txt_motivo_cerrar = $('#sec_mepa_detalle_atencion_liquidacion_form_txt_motivo_cerrar_dar_de_baja').val().trim();

	if(txt_situacion == 0)
	{
		alertify.error('Seleccione Situacion',5);
		$("#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_dar_de_baja").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_dar_de_baja').select2('open');
		}, 200);
		return false;
	}
	
	if(txt_tipo == 0)
	{
		alertify.error('Seleccione Tipo',5);
		$("#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_tipo").focus();
		setTimeout(function() 
		{
			$('#sec_mepa_detalle_atencion_liquidacion_form_txt_situacion_tipo').select2('open');
		}, 200);
		return false;
	}
	
	if(txt_motivo_cerrar.length == 0)
	{
		alertify.error('Ingrese el detalle',5);
		$("#sec_mepa_detalle_atencion_liquidacion_form_txt_motivo_cerrar_dar_de_baja").focus();
		return false;
	}
	
	swal(
	{
		title: '¿Está seguro de Cerrar la caja chica?',
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
			var dataForm = new FormData($("#sec_btn_mepa_detalle_atencion_liquidacion_dar_de_baja")[0]);
			dataForm.append("accion","sec_mepa_detalle_atencion_liquidacion_atencion_jefe_dar_de_baja");
			dataForm.append("mepa_detalle_atencion_liquidacion_id", mepa_detalle_atencion_liquidacion_id);
			dataForm.append("txt_situacion", txt_situacion);
			dataForm.append("txt_tipo", txt_tipo);
			dataForm.append("txt_motivo_cerrar", txt_motivo_cerrar);

			$.ajax({
				url: "sys/set_mepa_detalle_atencion_liquidacion.php",
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
					auditoria_send({ "respuesta": "sec_mepa_detalle_atencion_liquidacion_atencion_jefe_dar_de_baja", "data": respuesta });
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

function sec_mepa_detalle_atencion_liquidacion_btn_editar_correlativo_liquidacion(liquidacion_id, solicitante_usuario_id)
{
	
	let num_correlativo = $("#mepa_detalle_atencion_liquidacion_num_correlativo_liquidacion").val().trim();
	
	if(num_correlativo == "0")
	{
		alertify.error('Ingrese un número diferente a cero (0)',5);
		return false;
	}

	if(num_correlativo == "")
	{
		alertify.error('Ingrese un número',5);
		return false;
	}

	swal(
	{
		title: "¿Está seguro de editar el correlativo de la liquidación?",
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
				"accion": "sec_mepa_detalle_atencion_liquidacion_btn_editar_correlativo_liquidacion",
				"liquidacion_id": liquidacion_id,
				"solicitante_usuario_id": solicitante_usuario_id,
				"num_correlativo": num_correlativo
			}

			$.ajax({
				url: "sys/set_mepa_detalle_atencion_liquidacion.php",
				type: 'POST',
				data: dataForm,
				beforeSend: function()
				{
					loading(true);
				},
				complete: function() 
				{
					loading();
				},
				success: function(data)
				{
					
					var respuesta = JSON.parse(data);
					auditoria_send({ "respuesta": "sec_mepa_detalle_atencion_liquidacion_btn_editar_correlativo_liquidacion", "data": respuesta });
					
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
							title: "Error al registrar",
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
	});
}

function sec_mepa_detalle_atencion_liquidacion_btn_editar_correlativo_movilidad(id_movilidad, solicitante_usuario_id)
{
	
	let num_correlativo = $("#mepa_detalle_atencion_liquidacion_num_correlativo_movilidad").val().trim();
	
	if(num_correlativo == "0")
	{
		alertify.error('Ingrese un número diferente a cero (0)',5);
		return false;
	}

	if(num_correlativo == "")
	{
		alertify.error('Ingrese un número',5);
		return false;
	}

	swal(
	{
		title: "¿Está seguro de editar el correlativo de la movilidad?",
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
				"accion": "sec_mepa_detalle_atencion_liquidacion_btn_editar_correlativo_movilidad",
				"id_movilidad": id_movilidad,
				"solicitante_usuario_id": solicitante_usuario_id,
				"num_correlativo": num_correlativo
			}

			$.ajax({
				url: "sys/set_mepa_detalle_atencion_liquidacion.php",
				type: 'POST',
				data: dataForm,
				beforeSend: function()
				{
					loading(true);
				},
				complete: function() 
				{
					loading();
				},
				success: function(data)
				{
					
					var respuesta = JSON.parse(data);
					auditoria_send({ "respuesta": "sec_mepa_detalle_atencion_liquidacion_btn_editar_correlativo_movilidad", "data": respuesta });
					
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
							title: "Error al registrar",
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
	});
}

const mepa_detalle_atencion_liquidacion_ver_comprobante_pago = (tipo_documento, ruta_file) =>
{
	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf') 
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" height="525"></iframe>';
		$('#mepa_detalle_atencion_liquidacion_ver_archivo_visor_pdf').html(htmlModal);

		$('#mepa_atencion_liquidacion_div_visor_pdf_modal').modal('show');

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

const sec_mepa_detalle_atencion_liquidacion_btn_editar_aplica_movilidad = (se_aplica_movilidad, fecha_desde, fecha_hasta) => {
	
	if(se_aplica_movilidad == 0)
	{
		$("#div_modal_incluir_movilidad").hide();
		document.getElementById("check_agg_detalle_liquidacion_movilidad").checked = false;
		$("#mepa_detalle_atencion_liquidacion_modal_incluir_movilidad_param_fecha_desde_liquidacion").val(fecha_desde)
		$("#mepa_detalle_atencion_liquidacion_modal_incluir_movilidad_fecha_desde_liquidacion").val(fecha_desde)
		$("#mepa_detalle_atencion_liquidacion_modal_incluir_movilidad_param_fecha_hasta_liquidacion").val(fecha_hasta)
		$("#mepa_detalle_atencion_liquidacion_modal_incluir_movilidad_fecha_hasta_liquidacion").val(fecha_hasta)
		$("#mepa_detalle_atencion_liquidacion_modal_incluir_movilidad").modal("show");
	}
	else
	{
		var mepa_detalle_atencion_liquidacion_id = $('#mepa_detalle_atencion_liquidacion_id').val();

		swal(
		{
			title: '¿Está seguro de Anular la Plantilla de Movilidad?',
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
				dataForm.append("accion","mepa_detalle_atencion_liquidacion_anular_plantilla_movilidad");
				dataForm.append("mepa_detalle_atencion_liquidacion_id", mepa_detalle_atencion_liquidacion_id);
				
				$.ajax({
					url: "sys/set_mepa_detalle_atencion_liquidacion.php",
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
						auditoria_send({ "respuesta": "mepa_detalle_atencion_liquidacion_anular_plantilla_movilidad", "data": respuesta });
						if(parseInt(respuesta.http_code) == 200) 
						{
							swal({
								title: respuesta.titulo,
								text: respuesta.descripcion,
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
								text: respuesta.descripcion,
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
	
}

$("#check_agg_detalle_liquidacion_movilidad").change(function()
{
	if($(this).prop('checked') == true)
	{
		//INICIO MOSTRAR LAS MOVILIDADES DE ACUERDO A LAS FECHAS DEL - FECHAS DESDE, DE LA LIQUIDACION
		mepa_detalle_atencion_liquidacion_mostrar_movilidad_rango_fecha();
		//FIN MOSTRAR LAS MOVILIDADES DE ACUERDO A LAS FECHAS DEL - FECHAS HASTA, DE LA LIQUIDACION

		$("#div_modal_incluir_movilidad").show();
	}
	else
	{
		$("#div_modal_incluir_movilidad").hide();
	}
})

const mepa_detalle_atencion_liquidacion_mostrar_movilidad_rango_fecha = () => {
	
	var fecha_del = $("#mepa_detalle_atencion_liquidacion_modal_incluir_movilidad_param_fecha_desde_liquidacion").val();
	var fecha_al = $("#mepa_detalle_atencion_liquidacion_modal_incluir_movilidad_param_fecha_hasta_liquidacion").val();

	var data = {
		"accion": "mepa_detalle_atencion_liquidacion_mostrar_movilidad_rango_fecha",
		"param_fecha_del": fecha_del,
		"param_fecha_al": fecha_al
	}
	
	var array_movilidades = [];
	
	$.ajax({
		url: "/sys/set_mepa_detalle_atencion_liquidacion.php",
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

				$("#mepa_detalle_liquidacion_modal_incluir_txt_id_movilidad").html(html).trigger("change");

				return false;
			}
			else if (parseInt(respuesta.http_code) == 400) 
			{
				var html = '<option value="">-- Seleccione --</option>';
				$("#mepa_detalle_liquidacion_modal_incluir_txt_id_movilidad").html(html).trigger("change");

				return false;

			}
		},
		error: function() {}
	});
}

const mepa_detalle_atencion_liquidacion_guardar_incluir_movilidad = () => {
	
	var mepa_detalle_atencion_liquidacion_id = $('#mepa_detalle_atencion_liquidacion_id').val();
	var param_incluir_txt_id_movilidad = $('#mepa_detalle_liquidacion_modal_incluir_txt_id_movilidad').val();
	
	let dividir_select = param_incluir_txt_id_movilidad.split(',');
	var id_movilidad_select =  dividir_select[0];
	var monto_movilidad_select =  dividir_select[1];

	if($("#check_agg_detalle_liquidacion_movilidad").prop('checked') == true)
	{
		var selectValor = $("#mepa_detalle_liquidacion_modal_incluir_txt_id_movilidad").val();

		if(selectValor == 0)
		{
			alertify.error('Tiene que seleccionar una solicitud de movilidad',5);
			return false;
		}
	}
	else
	{
		alertify.error('Tiene que incluir la solicitud de movilidad.',5);
		return false;
	}

	swal(
	{
		title: '¿Está seguro de incluir la Movilidad?',
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
			dataForm.append("accion","mepa_detalle_atencion_liquidacion_guardar_incluir_movilidad");
			dataForm.append("mepa_detalle_atencion_liquidacion_id", mepa_detalle_atencion_liquidacion_id);
			dataForm.append("id_movilidad", id_movilidad_select);
			//auditoria_send({ "proceso": "guardar_mepa_solicitud_liquidacion", "data": dataForm });

			$.ajax({
				url: "sys/set_mepa_detalle_atencion_liquidacion.php",
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
					auditoria_send({ "respuesta": "mepa_detalle_atencion_liquidacion_guardar_incluir_movilidad", "data": respuesta });
					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: respuesta.titulo,
							text: respuesta.descripcion,
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
							text: respuesta.descripcion,
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