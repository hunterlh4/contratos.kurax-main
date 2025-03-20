function sec_mepa_devolucion_asignacion()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_devolucion_asignacion_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	sec_mepa_devolucion_asignacion_inicializar_param_fechas();

	set_mepa_devolucion_asignacion_div_voucher($('#mepa_devolucion_asignacion_modal_param_voucher'));
}

function sec_mepa_devolucion_asignacion_inicializar_param_fechas()
{
	// INICIO INICIALIZACION DE DATEPICKER
	$('.mepa_devolucion_asignacion_datepicker')
		.datepicker({
			dateFormat:'dd-mm-yy',
			changeMonth: true,
			changeYear: true
		})
		.on("change", function(ev) {
			$(this).datepicker('hide');
			var newDate = $(this).datepicker("getDate");
			$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
			// localStorage.setItem($(this).atrr("id"),)
		});
}

function set_mepa_devolucion_asignacion_div_voucher(object){
	
	$(document).on('click', '#mepa_devolucion_asignacion_modal_btn_buscar_comprobante', function(event) {
		event.preventDefault();
		object.click();
	});

	object.on('change', function(event) {
		
		//let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
		if($(this)[0].files.length <= 1)
		{
			const name = $(this).val().split(/\\|\//).pop();
			//truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
			truncated = name;
		}
		else
		{
			truncated = "";
			$("#mepa_devolucion_asignacion_modal_param_voucher").val("");
		}

		$("#mepa_devolucion_asignacion_modal_txt_comprobante_archivo").html(truncated);
	});
}

function mepa_devolucion_asignacion_buscar()
{
	$("#mepa_devolucion_asignacion_div_tabla").hide();
	mepa_devolucion_asignacion_listar_asignacion_datatable();
}

function mepa_devolucion_asignacion_listar_asignacion_datatable()
{
	if(sec_id == "mepa" && sub_sec_id == "devolucion_asignacion")
	{
		var param_usuario = $("#mepa_devolucion_asignacion_param_usuario").val();

		var data = {
			"accion": "mepa_devolucion_asignacion_listar_asignacion",
			"param_usuario" : param_usuario
		}
		
		$("#mepa_devolucion_asignacion_div_tabla").show();
		
		tabla = $("#mepa_devolucion_asignacion_datatable").dataTable(
		{
			language:{
				"decimal":        "",
				"emptyTable":     "No existen registros",
				"info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
				"infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
				"infoFiltered":   "(filtered from _MAX_ total entradas)",
				"infoPostFix":    "",
				"thousands":      ",",
				"lengthMenu":     "Mostrar _MENU_ entradas",
				"loadingRecords": "Cargando...",
				"processing":     "Procesando...",
				"search":         "Filtrar:",
				"zeroRecords":    "Sin resultados",
				"paginate": {
					"first":      "Primero",
					"last":       "Ultimo",
					"next":       "Siguiente",
					"previous":   "Anterior"
				},
				"aria": {
					"sortAscending":  ": activate to sort column ascending",
					"sortDescending": ": activate to sort column descending"
				}
				},
				"aProcessing" : true,
				"aServerSide" : true,

				"ajax" :
				{
					url : "/sys/set_mepa_devolucion_asignacion.php",
					data : data,
					type : "POST",
					dataType : "json",
					beforeSend: function() {
						loading("true");
					},
					complete: function() {
						loading();
					},
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[10, 20, 30, 40, 50, 100]
			}
		).DataTable();

	}
}

function sec_mepa_devolucion_btn_crear_devolucion_asignacion(asignacion_id)
{
	$("#modal_form_devolucion_asignacion .btn_editar").hide();
	$("#modal_form_devolucion_asignacion .btn_guardar").show();
	$("#title_modal_form_devolucion_asignacion").text("Crear Devolución");
	$("#modal_form_devolucion_asignacion").modal("show");
	$( "#modal_form_id_asignacion",$("#modal_form_devolucion_asignacion form")).val(asignacion_id);
}

$("#mepa_devolucion_asignacion_modal_param_aplica_voucher").change(function()
{
	if($(this).prop('checked') == true)
	{
		$("#mepa_devolucion_asignacion_modal_form_div_voucher").show();
	}
	else
	{
		$("#mepa_devolucion_asignacion_modal_form_div_voucher").hide();
		$("#mepa_devolucion_asignacion_modal_param_voucher").val("");
		$("#mepa_devolucion_asignacion_modal_txt_comprobante_archivo").html("");
	}
})

$("#modal_form_devolucion_asignacion .btn_guardar").off("click").on("click",function(){
    
    var dataForm = new FormData($("#form_devolucion_asignacion")[0]);

    var modal_form_id_asignacion = $('#modal_form_id_asignacion').val();
    var modal_form_param_tipo_devolucion = $('#mepa_devolucion_asignacion_modal_form_param_tipo_devolucion').val();
    var modal_form_param_fecha = $('#mepa_devolucion_asignacion_modal_form_param_fecha').val();
    var modal_param_voucher = document.getElementById("mepa_devolucion_asignacion_modal_param_voucher");

    var modal_param_aplica_voucher = 0;

    if(modal_form_id_asignacion == ""  || modal_form_id_asignacion == 0)
	{
		alertify.error('No existe el ID Asignación, por favor contactarse con Sistemas',5);
		return false;
	}

    if (document.getElementById("mepa_devolucion_asignacion_modal_param_aplica_voucher").checked)
	{
		modal_param_aplica_voucher = 1;
	}
	else
	{
		modal_param_aplica_voucher = 0;
	}

    if(modal_form_param_tipo_devolucion == "0")
	{
		alertify.error('Seleccione Tipo Devolución',5);
		$("#mepa_devolucion_asignacion_modal_form_param_tipo_devolucion").focus();
		setTimeout(function() {
			$('.mepa_devolucion_asignacion_modal_form_param_tipo_devolucion').select2('open');
		}, 500);
		return false;
	}

	if(modal_form_param_fecha == "")
	{
		alertify.error('Seleccione Fecha',5);
		$("#mepa_devolucion_asignacion_modal_form_param_fecha").focus();
		return false;
	}
	
	if(modal_form_param_tipo_devolucion == "11")
	{
		if(modal_param_aplica_voucher == 0)
		{
			alertify.error('Tiene que aplicar voucher',5);
			return false;
		}
	}

	if(modal_param_aplica_voucher == 1)
	{
		if(modal_param_voucher.files.length == 0)
		{
			alertify.error('Seleccione el Voucher',5);
			$("#mepa_devolucion_asignacion_modal_param_voucher").focus();
			return false;
		}
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
			dataForm.append("accion", "mepa_devolucion_asignacion_crear_devolucion");
			dataForm.append("param_aplica_voucher", modal_param_aplica_voucher);
			$.ajax({
		        url: "sys/set_mepa_devolucion_asignacion.php",
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
					        window.location.reload();
					    });

						setTimeout(function() {
							window.location.reload();
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
})

function sec_mepa_devolucion_btn_detalle_devolucion_asignacion(usuario_atencion_cierre, fecha_devolucion, nombre_devolucion, aplica_voucher, imagen, extension, download)
{
	$("#title_modal_form_devolucion_asignacion_detalle").text("Detalle Devolución");
	$("#modal_form_devolucion_asignacion_detalle").modal("show");

	var aplica_voucher_nombre = "";
	if(aplica_voucher == 1)
	{
		$("#modal_detalle_archivo").text(imagen);
		$("#modal_detalle_btn_archivo").html('<button type="button" class="btn btn-success btn-xs btn-block" onclick="mepa_devolucion_asignacion_ver_voucher(\''+extension+'\', \''+download+'\');" title="Ver archivo"><i class="icon fa fa-eye"></i><span id="demo-button-text">Ver Archivo</span></button>');
		aplica_voucher_nombre = "Si";
	}
	else
	{
		$("#modal_detalle_archivo").text("No aplica");
		$("#modal_detalle_btn_archivo").text("No aplica");
		aplica_voucher_nombre = "No";
	}
	$("#modal_detalle_usuario_atencion").text(usuario_atencion_cierre);
	$("#modal_detalle_fecha_atencion").text(fecha_devolucion);
	$("#modal_detalle_tipo_devolucion").text(nombre_devolucion);
	$("#modal_detalle_aplica_voucher").text(aplica_voucher_nombre);

}

function mepa_devolucion_asignacion_ver_voucher(tipo_documento, ruta_file) 
{
	
	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf') 
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" height="525"></iframe>';
		$('#mepa_devolucion_asignacion_visor_pdf').html(htmlModal);

		$('#mepa_devolucion_asignacion_div_visor_pdf_modal').modal('show');

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

function mepa_devolucion_asignacion_tesoreria_confirmar_deolucion(asignacion_id)
{
	var usuario_login_nombre = $("#mepa_devolucion_asignacion_usuario_login_nombre").val();

	swal(
	{
		title: '¿Está seguro de confirmar la devolución?',
		type: 'warning',
		text: 'Usuario aprobación: '+usuario_login_nombre,
		showCancelButton: true,
		confirmButtonText: 'Si, confirmar',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{
			var data = {
		        "accion": "mepa_devolucion_asignacion_tesoreria_confirmar_deolucion",
		        "param_asignacion_id" : asignacion_id
		    }

			$.ajax({
		        url: "sys/set_mepa_devolucion_asignacion.php",
		        type: 'POST',
		        data: data,
		        //cache: false,
		        //contentType: false,
		        //processData: false,
		        beforeSend: function() {
		            loading("true");
		        },
		        complete: function() {
		            loading();
		        },
		        success: function(data){
		        	
		        	var respuesta = JSON.parse(data);
		        	if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: "Registro exitoso",
							text: "La confirmación fue registrada exitosamente",
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
						}, 5000);

						return true;
					} 
					else {
						swal({
							title: "Error al guardar",
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