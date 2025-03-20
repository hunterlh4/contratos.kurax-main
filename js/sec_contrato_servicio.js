var tabla;

function sec_contrato_servicio()
{
	$(".select2").select2({ width: '100%' });

	listarServiciosCategoriaDatatable();
	listarServiciosTipoCategoriaDatatable();

	$("#formulario").on("submit", function(e)
	{
		guardaryeditar(e);
	})

	$("#formulariotipocategoria").on("submit", function(e)
	{
		guardaryeditarTipoCategoria(e);
	})

	$('#div_servicio_categoria').show();
	$('#div_servicio_tipo_categoria').hide();

	$('#cont_servicio_categoria_datatable').DataTable().on("draw", function()
	{
		mostrar_switch();
	})

	$('#cont_servicio_tipo_categoria_datatable').DataTable().on("draw", function()
	{
		mostrar_switch();
	})

}

$("#select_servicio").on("change", function()
{
	var selectValor = $(this).val();

	obtenerSelectCategorias();
	mostrar_div(selectValor);
})

$("#cont_categoria_select_situacion").on("change", function()
{
	var select_servicio = $("#select_servicio").val();

	if(select_servicio == "1")
	{
		listarServiciosCategoriaDatatable();
	}
})

$("#cont_tipo_categoria_select_situacion").on("change", function()
{
	var select_servicio = $("#select_servicio").val();

	if(select_servicio == "2")
	{
		listarServiciosTipoCategoriaDatatable();
	}
})


function mostrar_div(param)
{
	mostrar_switch();
	if (param == '1') 
	{
		$('#div_servicio_categoria').show();
		$('#div_servicio_tipo_categoria').hide();
	}
	else if(param == '2')
	{
		$('#div_servicio_categoria').hide();
		$('#div_servicio_tipo_categoria').show();
	}
}

// INICIO ALERTAS EN LOS DIVS
var claseTipoAlertas = 
{
	alertaSuccess: 1,
	alertaInfo: 2,
	alertaWarning: 3,
	alertaDanger: 4
};

function RecuperarClaseAlerta(valor)
{
	var clase = "";
	switch(valor)
	{
		case 1 : clase = 'alert alert-success alerta-dismissible';
		break;

		case 2 : clase = 'alert alert-info alerta-dismissible';
		break;

		case 3 : clase = 'alert alert-warning alerta-dismissible';
		break;

		case 4 : clase = 'alert alert-danger alerta-dismissible';
		break; 
	}

	return clase;
}

function tipoFont(valor)
{
	var clase = "";
	switch(valor)
	{
		case 1:
		case 2: clase = "<i class='fa fa-info-circle fa-2x'></i>";
		break;

		case 3:
		case 4: clase = "<i class='fa fa-exclamation-triangle fa-2x'></i>";
		break;

	}

	return clase;
}

var mensajeAlerta = function (titulo, mensaje, tipoClase, controlDiv)
{
	var clase = RecuperarClaseAlerta(tipoClase);
	var font = tipoFont(tipoClase);
	var control = $(controlDiv);
	var divMensaje = "<div class = '"+ clase +"' role = 'alert'>";
	divMensaje += "<button type = 'button' class = 'close' data-dismiss = 'alert' aria-label = 'close'>";
	divMensaje += "<span aria-hidden = 'true'>&times;</span>";
	divMensaje += "</button>";
	divMensaje += font + "<strong>" + titulo + "</strong><br/>" + mensaje;
	divMensaje += "</div>";
	control.empty();
	control.hide().html(divMensaje.toString()).fadeIn(2000).delay(2000).fadeOut("slow");
}

// FIN ALERTAS EN LOS DIVS


function cancelarFormulario()
{
	limpiarFormulario();
}

function listarServiciosCategoriaDatatable()
{
	if(sub_sec_id == "servicio")
	{
		var cont_categoria_select_situacion = $("#cont_categoria_select_situacion").val();
		
		var data = {
			"accion": "cont_listar_servicios_categoria",
			"cont_categoria_select_situacion" : cont_categoria_select_situacion
		}

		$.ajax({
			url : "/sys/set_contrato_servicio.php",
			data : data,
			type : "POST",
			dataType : "json",
			success : function(data)
			{
				tabla = $("#cont_servicio_categoria_datatable").dataTable(
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
						"bDestroy" : true,
						aLengthMenu:[5, 10],
						"order" : 
						[
							0, "desc"	
						],
						"data" : data.aaData,
						"columns" : [
							{
								"data" : "0"
							},
							{
								"data" : "1"
							},
							{
								"data" : "2"
							}

						]
					}
				).DataTable();

				mostrar_switch();
			}

		});
	}
}

function mostrar_switch()
{
	$(".switch").bootstrapToggle({
		on:"activo",
		off:"inactivo",
		onstyle:"success",
		offstyle:"danger",
		size:"mini"
	});

	$(".toggle").off().on('click', function(event) {
		if(typeof $(this).find('.switch').data().ignore === 'undefined')
			$(this).find('.switch').bootstrapToggle('toggle');
	});

	$(".switch")
	.off()
	.on("change",function(event) {
		switch_data($(event.target));
	});
}

function guardaryeditar(e)
{
	e.preventDefault();
	
	var txtidserviciocategoria = $("#txtidserviciocategoria").val();
	var txtnombre = $("#txtnombre").val();

	if (txtnombre == "") 
	{
		mensajeAlerta("Advertencia:", "Tiene que ingresar una categoría.", claseTipoAlertas.alertaWarning, $('#cont_servicio_campos_formulario'));
		return;
	}

	var formData = new FormData($("#formulario")[0]);
	formData.append("accion","guardaryeditar");
	formData.append("txtidserviciocategoria", txtidserviciocategoria);
	formData.append("txtnombre", txtnombre);

	$.ajax({
		url : "/sys/set_contrato_servicio.php",
		type : "POST",
		data : formData,
		contentType : false,
		processData : false,
		success : function(resp)
		{
			var respuesta = JSON.parse(resp);

			if (respuesta.valor == 1) 
			{
				if (respuesta.status) 
				{
					swal({
						title: "Listo!",
						text: respuesta.message,
						type: "success",
						timer: 3000,
						closeOnConfirm: false
					});
				}
				else
				{
					swal({
						title: "Error!",
						text: "Ocurrio un error: "+ respuesta.message +", pongase en contacto con el personal de SOPORTE",
						type: "warning",
						timer: 5000,
						closeOnConfirm: false
					});
				}
				//tabla.ajax.reload();
				listarServiciosCategoriaDatatable();
				limpiarFormulario();
			}
			else if(respuesta.valor == 2)
			{
				swal({
					title: "Ooopss!",
					text: respuesta.message,
					type: "warning",
					timer: 5000,
					closeOnConfirm: false
				});
			}
			else if(respuesta.valor == 3)
			{
				if (respuesta.status) 
				{
					swal({
						title: "Listo!",
						text: respuesta.message,
						type: "success",
						timer: 3000,
						closeOnConfirm: false
					});
				}
				else
				{
					swal({
						title: "Error!",
						text: "Ocurrio un error: "+ respuesta.message +", pongase en contacto con el personal de SOPORTE",
						type: "warning",
						timer: 5000,
						closeOnConfirm: false
					});
				}
				//tabla.ajax.reload();
				listarServiciosCategoriaDatatable();
				limpiarFormulario();
			}
			else if(respuesta.valor == 4)
			{
				swal({
					title: "Ooopss!",
					text: respuesta.message,
					type: "warning",
					timer: 5000,
					closeOnConfirm: false
				});
			}
			
			
		}
	});
}

function desactivar_categoria(idserviciocategoria)
{
	swal(
	{
		title: '¿Está seguro de desactivar la categoría?',
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
			"accion": "desactivar_categoria",
			"txtidserviciocategoria" : idserviciocategoria
			}

			$.ajax({
				url : "/sys/set_contrato_servicio.php",
				type : "POST",
				data : data,
				//contentType : false,
				//processData : false,
				success : function(resp)
				{
					var respuesta = JSON.parse(resp);

					if (respuesta.status) 
					{
						swal({
							title: "Listo!",
							text: respuesta.message,
							type: "success",
							timer: 5000,
							closeOnConfirm: false
						});
					}
					else
					{
						swal({
							title: "Error!",
							text: "Ocurrio un error: "+ respuesta.message +", pongase en contacto con el personal de SOPORTE",
							type: "warning",
							timer: 5000,
							closeOnConfirm: false
						});
					}

					//tabla.ajax.reload();
					listarServiciosCategoriaDatatable();
				}
			});
		}
	}
	);
}

function activar_categoria(idserviciocategoria)
{
	swal(
	{
		title: '¿Está seguro de activar la categoría?',
		type: 'info',
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
			"accion": "activar_categoria",
			"txtidserviciocategoria" : idserviciocategoria
			}

			$.ajax({
				url : "/sys/set_contrato_servicio.php",
				type : "POST",
				data : data,
				//contentType : false,
				//processData : false,
				success : function(resp)
				{
					var respuesta = JSON.parse(resp);

					if (respuesta.status) 
					{
						swal({
							title: "Listo!",
							text: respuesta.message,
							type: "success",
							timer: 5000,
							closeOnConfirm: false
						});
					}
					else
					{
						swal({
							title: "Error!",
							text: "Ocurrio un error: "+ respuesta.message +", pongase en contacto con el personal de SOPORTE",
							type: "warning",
							timer: 5000,
							closeOnConfirm: false
						});
					}

					//tabla.ajax.reload();
					listarServiciosCategoriaDatatable();
				}
			});
		}
	}
	);
}

function obtener_categoria_servicio(idserviciocategoria)
{
	var data = {
	"accion": "obtener_dato_categoria_servicio",
	"txtidserviciocategoria" : idserviciocategoria
	}

	$.ajax({
		url : "/sys/set_contrato_servicio.php",
		type : "POST",
		data : data,
		//contentType : false,
		//processData : false,
		success : function(resp)
		{
			var respuesta = JSON.parse(resp);

			if (respuesta.status) 
			{
				$("#txtidserviciocategoria").val(respuesta.dato.id);
				$("#txtnombre").val(respuesta.dato.nombre);
			}
			else
			{
				swal({
					title: "Error!",
					text: "Ocurrio un error: "+ respuesta.message +", pongase en contacto con el personal de SOPORTE",
					type: "warning",
					timer: 5000,
					closeOnConfirm: false
				});
			}

			// tabla.ajax.reload();
		}
	});
}

function guardaryeditarTipoCategoria(e)
{
	e.preventDefault();
	
	var txtidserviciotipocategoria = $("#txtidserviciotipocategoria").val();
	var txtidserviciocategoriaselect = $("#txtidserviciocategoriaselect").val();
	var txtnombretipocategoria = $("#txtnombretipocategoria").val();

	if (txtnombretipocategoria == "") 
	{
		mensajeAlerta("Advertencia:", "Tiene que ingresar un tipo de categoría.", claseTipoAlertas.alertaWarning, $('#cont_servicio_campos_formulario_tipo_categoria'));
		return;
	}

	var formData = new FormData($("#formulariotipocategoria")[0]);
	formData.append("accion","guardaryeditarTipoCategoria");
	formData.append("txtidserviciotipocategoria", txtidserviciotipocategoria);
	formData.append("txtidserviciocategoriaselect", txtidserviciocategoriaselect);
	formData.append("txtnombretipocategoria", txtnombretipocategoria);

	$.ajax({
		url : "/sys/set_contrato_servicio.php",
		type : "POST",
		data : formData,
		contentType : false,
		processData : false,
		success : function(resp)
		{
			var respuesta = JSON.parse(resp);

			if (respuesta.valor == 1) 
			{
				if (respuesta.status) 
				{
					swal({
						title: "Listo!",
						text: respuesta.message,
						type: "success",
						timer: 3000,
						closeOnConfirm: false
					});
				}
				else
				{
					swal({
						title: "Error!",
						text: "Ocurrio un error: "+ respuesta.message +", pongase en contacto con el personal de SOPORTE",
						type: "danger",
						timer: 5000,
						closeOnConfirm: false
					});
				}
				//tabla.ajax.reload();
				listarServiciosTipoCategoriaDatatable();
				limpiarFormularioTipoCategoria();
			}
			else if(respuesta.valor == 2)
			{
				swal({
					title: "Ooopss!",
					text: respuesta.message,
					type: "warning",
					timer: 5000,
					closeOnConfirm: false
				});
			}
			else if(respuesta.valor == 3)
			{
				if (respuesta.status) 
				{
					swal({
						title: "Listo!",
						text: respuesta.message,
						type: "success",
						timer: 3000,
						closeOnConfirm: false
					});
				}
				else
				{
					swal({
						title: "Error!",
						text: "Ocurrio un error: "+ respuesta.message +", pongase en contacto con el personal de SOPORTE",
						type: "danger",
						timer: 5000,
						closeOnConfirm: false
					});
				}
				//tabla.ajax.reload();
				listarServiciosTipoCategoriaDatatable();
				limpiarFormularioTipoCategoria();
			}
			else if(respuesta.valor == 4)
			{
				swal({
					title: "Ooopss!",
					text: respuesta.message,
					type: "warning",
					timer: 5000,
					closeOnConfirm: false
				});
			}
			
			
		}
	});
}


function limpiarFormulario()
{
	$("#txtidserviciocategoria").val("");
	$("#txtnombre").val("");
}

// INICIO TIPO CATEGORIA //

function listarServiciosTipoCategoriaDatatable()
{
	obtenerSelectCategorias();

	if(sub_sec_id == "servicio")
	{
		$("#cont_servicio_tipo_categoria_div_tabla").show();

		var cont_tipo_categoria_select_situacion = $("#cont_tipo_categoria_select_situacion").val();
		

		var data = {
			"accion": "cont_listar_servicios_tipo_categoria",
			"cont_tipo_categoria_select_situacion" : cont_tipo_categoria_select_situacion
		}

		$.ajax({
			url : "/sys/set_contrato_servicio.php",
			data : data,
			type : "POST",
			dataType : "json",
			success : function(data)
			{
				tabla = $("#cont_servicio_tipo_categoria_datatable").dataTable(
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
						"bDestroy" : true,
						aLengthMenu:[5, 10],
						"order" : 
						[
							0, "desc"	
						],
						"data" : data.aaData,
						"columns" : [
							{
								"data" : "0"
							},
							{
								"data" : "1"
							},
							{
								"data" : "2"
							},
							{
								"data" : "3"
							}

						]
					}
				).DataTable();

				mostrar_switch();
			}

		});
	}
}


function obtenerSelectCategorias()
{
	var data = {
	"accion": "obtenerSelectCategorias"
	}

	$.ajax({
		url : "/sys/set_contrato_servicio.php",
		type : "POST",
		data : data,
		//contentType : false,
		//processData : false,
		success : function(resp)
		{
			$("#txtidserviciocategoriaselect").html(resp);
		}
	});

}


function obtener_tipo_categoria_servicio(idserviciotipocategoria)
{
	var data = {
	"accion": "obtener_dato_tipo_categoria_servicio",
	"txtidserviciotipocategoria" : idserviciotipocategoria
	}

	$.ajax({
		url : "/sys/set_contrato_servicio.php",
		type : "POST",
		data : data,
		//contentType : false,
		//processData : false,
		success : function(resp)
		{
			var respuesta = JSON.parse(resp);

			if (respuesta.status) 
			{
				$("#txtidserviciotipocategoria").val(respuesta.dato.id);
				$("#txtidserviciocategoriaselect").val(respuesta.dato.id_categoria);
				
				$("#txtnombretipocategoria").val(respuesta.dato.tipo_categoria);
			}
			else
			{
				swal({
					title: "Error!",
					text: "Ocurrio un error: "+ respuesta.message +", pongase en contacto con el personal de SOPORTE",
					type: "warning",
					timer: 5000,
					closeOnConfirm: false
				});
			}

			//tabla.ajax.reload();
			listarServiciosTipoCategoriaDatatable();
		}
	});
}

function desactivar_tipo_categoria(idserviciotipocategoria)
{

	swal(
	{
		title: '¿Está seguro de desactivar el tipo de categoría?',
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
			"accion": "desactivar_tipo_categoria",
			"txtidserviciotipocategoria" : idserviciotipocategoria
			}

			$.ajax({
				url : "/sys/set_contrato_servicio.php",
				type : "POST",
				data : data,
				//contentType : false,
				//processData : false,
				success : function(resp)
				{
					var respuesta = JSON.parse(resp);

					if (respuesta.status) 
					{
						swal({
							title: "Listo!",
							text: respuesta.message,
							type: "success",
							timer: 5000,
							closeOnConfirm: false
						});
					}
					else
					{
						swal({
							title: "Error!",
							text: "Ocurrio un error: "+ respuesta.message +", pongase en contacto con el personal de SOPORTE",
							type: "warning",
							timer: 5000,
							closeOnConfirm: false
						});
					}

					//tabla.ajax.reload();
					listarServiciosTipoCategoriaDatatable();
				}
			});
		}
	}
	);
}

function activar_tipo_categoria(idserviciotipocategoria)
{
	swal(
	{
		title: '¿Está seguro de activar el tipo de categoría?',
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
			"accion": "activar_tipo_categoria",
			"txtidserviciotipocategoria" : idserviciotipocategoria
			}

			$.ajax({
				url : "/sys/set_contrato_servicio.php",
				type : "POST",
				data : data,
				//contentType : false,
				//processData : false,
				success : function(resp)
				{
					var respuesta = JSON.parse(resp);

					if (respuesta.status) 
					{
						swal({
							title: "Listo!",
							text: respuesta.message,
							type: "success",
							timer: 5000,
							closeOnConfirm: false
						});
					}
					else
					{
						swal({
							title: "Error!",
							text: "Ocurrio un error: "+ respuesta.message +", pongase en contacto con el personal de SOPORTE",
							type: "warning",
							timer: 5000,
							closeOnConfirm: false
						});
					}

					tabla.ajax.reload();
				}
			});
		}
	}
	);
}


function cancelarFormularioTipoCategoria()
{
	limpiarFormularioTipoCategoria();
}

function limpiarFormularioTipoCategoria()
{
	$("#txtidserviciotipocategoria").val("");
	$("#txtnombretipocategoria").val("");
}


// FIN TIPO CATEGORIA