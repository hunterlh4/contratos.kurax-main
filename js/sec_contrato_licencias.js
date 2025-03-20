var con_test = false;
var table_detalle_lic_funcionamiento;
var contrato_id	=	'';

function sec_contrato_licencias()
{
	inicializarFechasLicenciaMunicipal();
	cargarlistado_giros();
	setArchivoLicFuncionamiento($('#fileArchivoLicFuncionamiento'));
	setArchivoLicFuncionamiento_edit($('#fileArchivoLicFuncionamiento_edit'));

	setArchivoLicIndeci($('#fileArchivoLicIndeci'));
	setArchivoLicIndeci_edit($('#fileArchivoLicIndeci_edit'));

	setArchivoLicPublicidad($('#fileArchivoLicPublicidad'));
	setArchivoLicPublicidad_edit($('#fileArchivoLicPublicidad_edit'));

	setArchivoLicDeclaracionJurada($('#fileArchivoLicDeclaracionJurada'));
	setArchivoLicDeclaracionJurada_edit($('#fileArchivoLicDeclaracionJurada_edit'));

	sec_contratos_licencias_locales();
	sec_contratos_licencias_detalle_Funcionamiento();
	sec_contratos_licencias_detalle_Indeci();
	sec_contratos_licencias_detalle_Publicidad();
	sec_contrato_listado_licencias_funcionamiento(4);
	
	$(".select2").select2({ width: '100%' });
	// tabla_autorizacion_municipales=null;
}

function inicializarFechasLicenciaMunicipal()
{
	$('.licencia_funcionamiento_datepicker')
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

//ESTE ES PARA LAS ALERTAS

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
	control.hide().html(divMensaje.toString()).fadeIn(2000).delay(8000).fadeOut("slow");
}


function sec_contratos_licencias_locales()
{
	if(sub_sec_id=="licencias")
	{
		var table_contratos_licencias = $('#cont_locales_licencias_datatable').DataTable(
		 {
			language:{
				"decimal":        "",
				"emptyTable":     "Tabla vacia",
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
			}
			,aLengthMenu:[10, 20, 30, 40, 50]
			,"order": [[ 0, 'desc' ]],
			dom: 
				"<'row'<'col-sm-12 text-center'B>>" + // Botón centrado
				"<'row'<'col-sm-6'l><'col-sm-6'f>>" + // Filtros a la izquierda y buscador a la derecha
				"<'row'<'col-sm-12'tr>>" + // Tabla debajo de los filtros y el buscador
				"<'row'<'col-sm-6'i><'col-sm-6'p>>", // Paginación e información debajo de la tabla
				aLengthMenu: [10, 20, 30, 40, 50],
				order: [[0, 'desc']],
				buttons: [{
					extend: 'excel',
					title: 'Archivo',
					filename: 'Export_File',
					text: 
					'<button class="btn btn-success">Exportar a Excel <i class="fas fa-file-excel"></i></button>',
					// exportOptions: {
					// 	columns: ':not(:last-child)'
					// }
				}],
				 
			 
		});


	}
}


function setArchivoLicFuncionamiento(object){

	$(document).on('click', '#btnBuscarFileFuncionamiento', function(event) {

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
			mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLicFuncionamiento'));
			$("#fileArchivoLicFuncionamiento").val("");
		}

		$("#txtFileLicFuncionamiento").html(truncated);

	});
}

function setArchivoLicFuncionamiento_edit(object){

	$(document).on('click', '#btnBuscarFileFuncionamiento_edit', function(event) {

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
			mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLicFuncionamiento'));
			$("#fileArchivoLicFuncionamiento_edit").val("");
		}

		$("#txtFileLicFuncionamiento_edit").html(truncated);

	});
}



function setArchivoLicIndeci(object){
	$(document).on('click', '#btnBuscarFileIndeci', function(event) {
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
			mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLicIndeci'));
			$("#fileArchivoLicIndeci").val("");

		}

		$("#txtFileLicIndeci").html(truncated);
	});
}
function setArchivoLicIndeci_edit(object){
	$(document).on('click', '#btnBuscarFileIndeci_edit', function(event) {
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
			mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLicIndeci_edit'));
			$("#fileArchivoLicIndeci_edit").val("");

		}

		$("#txtFileLicIndeci_edit").html(truncated);
	});
}


function setArchivoLicPublicidad(object){
	$(document).on('click', '#btnBuscarFilePublicidad', function(event) {
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
			mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLicPublicidad'));
			$("#fileArchivoLicPublicidad").val("");
		}

		$("#txtFileLicPublicidad").html(truncated);
	});
}
function setArchivoLicPublicidad_edit(object){
	$(document).on('click', '#btnBuscarFilePublicidad_edit', function(event) {
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
			mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLicPublicidad_edit'));
			$("#fileArchivoLicPublicidad_edit").val("");
		}

		$("#txtFileLicPublicidad_edit").html(truncated);
	});
}

function setArchivoLicDeclaracionJurada(object){
	$(document).on('click', '#btnBuscarFileDeclaracionJurada', function(event) {
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
			mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaDeclaracionJurada'));
			$("#fileArchivoLicDeclaracionJurada").val("");
		}

		$("#txtFileLicDeclaracionJurada").html(truncated);
	});
}
function setArchivoLicDeclaracionJurada_edit(object){
	$(document).on('click', '#btnBuscarFileDeclaracionJurada_edit', function(event) {
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
			mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaDeclaracionJurada_edit'));
			$("#fileArchivoLicDeclaracionJurada_edit").val("");
		}

		$("#txtFileLicDeclaracionJurada_edit").html(truncated);
	});
}

$("#txtLicFuncionamiento").on("change", function()
{
	var selectValor = $(this).val();

	if(selectValor == "CONCLUIDO")
	{
		$('#cont_licencias_div_condicion_funcionamiento').show();
		$('#cont_licencias_div_file_funcionamiento').show();
	}
	else
	{
		$('#cont_licencias_div_condicion_funcionamiento').hide();
		$('#cont_licencias_div_fecha_vencimiento_funcionamiento').hide();
		$('#cont_licencias_div_fecha_renovacion_funcionamiento').hide();
		$('#cont_licencias_div_file_funcionamiento').hide();
	}
})


$("#txtCondicionLicFuncionamiento").on("change", function()
{
	var selectValor = $(this).val();

	if(selectValor == "TEMPORAL")
	{
		$('#cont_licencias_div_fecha_vencimiento_funcionamiento').show();
		$('#cont_licencias_div_fecha_renovacion_funcionamiento').show();
	}
	else
	{
		$('#cont_licencias_div_fecha_vencimiento_funcionamiento').hide();
		$('#cont_licencias_div_fecha_renovacion_funcionamiento').hide();
	}
})

$("#txtLicFuncionamiento_edit").on("change", function () {
	var selectValor = $(this).val();

	if (selectValor == "CONCLUIDO") {
		$('#cont_licencias_div_condicion_funcionamiento_edit').show();
		$('#cont_licencias_div_file_funcionamiento_edit').show();
	}
	else {
		$('#cont_licencias_div_condicion_funcionamiento_edit').hide();
		$('#cont_licencias_div_fecha_vencimiento_funcionamiento_edit').hide();
		$('#cont_licencias_div_fecha_renovacion_funcionamiento_edit').hide();
		$('#cont_licencias_div_file_funcionamiento_edit').hide();
	}
})
$("#txtCondicionLicFuncionamiento_edit").on("change", function () {
	var selectValor = $(this).val();

	if (selectValor == "TEMPORAL") {
		$('#cont_licencias_div_fecha_vencimiento_funcionamiento_edit').show();
		$('#cont_licencias_div_fecha_renovacion_funcionamiento_edit').show();
	}
	else {
		$('#cont_licencias_div_fecha_vencimiento_funcionamiento_edit').hide();
		$('#cont_licencias_div_fecha_renovacion_funcionamiento_edit').hide();
	}
})
$(document).on('submit', "#formularioFuncionamiento", function(e)
{
	e.preventDefault();
	var contrato_id = $("#contrato_id").val();
	var contrato_nombre_local = $("#contrato_nombre_local").val();
	var fileArchivoLicFuncionamiento = document.getElementById("fileArchivoLicFuncionamiento");

	var txtLicFuncionamiento = $("#txtLicFuncionamiento").val();
	var txtCondicionLicFuncionamiento = $("#txtCondicionLicFuncionamiento").val();
	var txtFechaVencimientoLicFuncionamiento = $("#txtFechaVencimientoLicFuncionamiento").val();
	var txtFechaRenovacionLicFuncionamiento = $("#txtFechaRenovacionLicFuncionamiento").val();

	if(txtLicFuncionamiento == "")
	{
		alertify.error('Seleccione estado licencia',5);
		$('#txtLicFuncionamiento').focus();
		return false;
	}
	else if(txtLicFuncionamiento == "CONCLUIDO")
	{
		if (txtCondicionLicFuncionamiento == "")
		{
			alertify.error('Seleccione la condición',5);
			$('#txtCondicionLicFuncionamiento').focus();
			return false;
		}

		if(fileArchivoLicFuncionamiento.files.length == 0 )
		{
			alertify.error('Seleccione el file',5);
			$("#fileArchivoLicFuncionamiento").focus();
			return false;
		}
	}

	var form_data = (new FormData(this));
	form_data.append("post_archivo_funcionamiento", 1);
	form_data.append("contrato_id", contrato_id);
	form_data.append("contrato_nombre_local", contrato_nombre_local);

	form_data.append("txtLicFuncionamiento", txtLicFuncionamiento);
	form_data.append("txtCondicionLicFuncionamiento", txtCondicionLicFuncionamiento);
	form_data.append("txtFechaVencimientoLicFuncionamiento", txtFechaVencimientoLicFuncionamiento);
	form_data.append("txtFechaRenovacionLicFuncionamiento", txtFechaRenovacionLicFuncionamiento);

	loading(true);

	$.ajax({
		url: "/sys/set_contrato_licencias.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {

			result = JSON.parse(response);
			// loading();
			if(result.status)
			{
				// m_reload();
				
				 
				$('#fileArchivoLicFuncionamiento').val('');
				$('#txtFileLicFuncionamiento').html('');
				sec_contrato_listado_licencias_funcionamiento(4);

				swal(result.message, "", "success");

			}
			else
			{
				swal(
				{
					type: "warning",
					title: "Alerta!",
					text: result.message,
					html: true,
				});
			}
			//filter_archivos_table(0);
		},
		always: function(data){
			// loading();
			console.log(data);
		}
	});
});
$(document).on('submit', "#formularioFuncionamiento_edit", function(e)
{
	e.preventDefault();
	var contrato_id = $("#contrato_id_edit").val();
	var contrato_nombre_local = $("#contrato_nombre_local_edit").val();
	var fileArchivoLicFuncionamiento = document.getElementById("fileArchivoLicFuncionamiento_edit");
	var contrato_licencia_id	=	$("#contrato_licencia_id").val();
	var txtLicFuncionamiento = $("#txtLicFuncionamiento_edit").val();
	var txtCondicionLicFuncionamiento = $("#txtCondicionLicFuncionamiento_edit").val();
	var txtFechaVencimientoLicFuncionamiento = $("#txtFechaVencimientoLicFuncionamiento_edit").val();
	var txtFechaRenovacionLicFuncionamiento = $("#txtFechaRenovacionLicFuncionamiento_edit").val();

	if(txtLicFuncionamiento == "")
	{
		alertify.error('Seleccione estado licencia',5);
		$('#txtLicFuncionamiento_edit').focus();
		return false;
	}
	else if(txtLicFuncionamiento == "CONCLUIDO")
	{
		if (txtCondicionLicFuncionamiento == "")
		{
			alertify.error('Seleccione la condición',5);
			$('#txtCondicionLicFuncionamiento_edit').focus();
			return false;
		}

		if(fileArchivoLicFuncionamiento.files.length == 0 )
		{
			alertify.error('Seleccione el file',5);
			$("#fileArchivoLicFuncionamient_edit").focus();
			return false;
		}
	}

	var form_data = (new FormData(this));
	form_data.append("post_archivo_funcionamiento_edit", 1);
	form_data.append("contrato_id_edit", contrato_id);
	form_data.append("contrato_licencia_id", contrato_licencia_id);
	form_data.append("contrato_nombre_local_edit", contrato_nombre_local);

	form_data.append("txtLicFuncionamiento_edit", txtLicFuncionamiento);
	form_data.append("txtCondicionLicFuncionamiento_edit", txtCondicionLicFuncionamiento);
	form_data.append("txtFechaVencimientoLicFuncionamiento_edit", txtFechaVencimientoLicFuncionamiento);
	form_data.append("txtFechaRenovacionLicFuncionamiento_edit", txtFechaRenovacionLicFuncionamiento);

	// loading(true);
	$.ajax({
		url: "/sys/set_contrato_licencias.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {

			result = JSON.parse(response);
			loading();
			if(result.status)
			{
				// m_reload();
				$('#fileArchivoLicFuncionamiento_edit').val('');
				$('#txtFileLicFuncionamiento_edit').html('');
				sec_contrato_listado_licencias_funcionamiento(4);
				$('#modal_editar_licencia').modal('hide');
				
				swal(result.message, "", "success");

			}
			else
			{
				swal(
				{
					type: "warning",
					title: "Alerta!",
					text: result.message,
					html: true,
				});
			}
			// filter_archivos_table(0);
		},
		always: function(data){
			loading();
			console.log(data);
		}
	});
});
$(document).on('submit', "#formularioIndeci_edit", function(e)
{
	e.preventDefault();
	var contrato_id = $("#contrato_cert_indeci_id").val();
	var contrato_nombre_local_indeci_dit = $("#contrato_nombre_local_indeci_dit").val();
	var fileArchivoLicIndeci_edit = document.getElementById("fileArchivoLicIndeci_edit");
	var cert_indeci_id	=	$("#cert_indeci_id").val();
	var txtLicIndeci_edit = $("#txtLicIndeci_edit").val();
	var txtCondicionLicFuncionamiento = $("#txtCondicionLicFuncionamiento_edit").val();
	var txtFechaVencimientoLicFuncionamiento = $("#txtFechaVencimientoLicIndeci_edit").val();
	var txtFechaRenovacionLicFuncionamiento = $("#txtFechaRenovacionLicIndeci_edit").val();

	if(txtLicIndeci_edit == "")
	{
		alertify.error('Seleccione estado licencia',5);
		$('#txtLicIndeci_edit').focus();
		return false;
	}
	if(fileArchivoLicIndeci_edit.files.length == 0 )
	{
			alertify.error('Seleccione el file',5);
			$("#fileArchivoLicIndeci_edit").focus();
			return false;
	}

	var form_data = (new FormData(this));
	form_data.append("post_archivo_indeci_edit", 1);
	form_data.append("contrato_cert_indeci_id", contrato_id);
	form_data.append("cert_indeci_id", cert_indeci_id);
	form_data.append("contrato_nombre_local_indeci_dit", contrato_nombre_local_indeci_dit);

	form_data.append("txtLicIndeci_edit", txtLicIndeci_edit);
	form_data.append("txtFechaVencimientoLicFuncionamiento_edit", txtFechaVencimientoLicFuncionamiento);
	form_data.append("txtFechaRenovacionLicFuncionamiento_edit", txtFechaRenovacionLicFuncionamiento);

	// loading(true);
	$.ajax({
		url: "/sys/set_contrato_licencias.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {

			result = JSON.parse(response);
			loading();
			if(result.status)
			{
				// m_reload();
				$('#fileArchivoLicIndeci_edit').val('');
				$('#txtFileLicIndeci_edit').html('');
				sec_contrato_listado_licencias_funcionamiento(5);
				$('#modal_editar_cert_indeci').modal('hide');
				
				swal(result.message, "", "success");

			}
			else
			{
				swal(
				{
					type: "warning",
					title: "Alerta!",
					text: result.message,
					html: true,
				});
			}
			// filter_archivos_table(0);
		},
		always: function(data){
			loading();
			console.log(data);
		}
	});
});
$(document).on('submit', "#formularioPublicidad_edit", function(e)
{
	e.preventDefault();
	var contrato_id_autorizacion = $("#contrato_id_autorizacion").val();
	var contrato_nombre_local_autorizacion = $("#contrato_nombre_local_autorizacion").val();
	var fileArchivoLicPublicidad_edit = document.getElementById("fileArchivoLicPublicidad_edit");
	var autorizacion_id	=	$("#autorizacion_id").val();
	var txtLicPublicidad_edit = $("#txtLicPublicidad_edit").val();
	var txtCondicionLicPublicidad_edit = $("#txtCondicionLicPublicidad_edit").val();
	var txtFechaVencimientoLicPublicidad_edit = $("#txtFechaVencimientoLicPublicidad_edit").val();
	var txtFechaRenovacionLiPublicidad_edit = $("#txtFechaRenovacionLiPublicidad_edit").val();

	if(txtLicPublicidad_edit == "")
	{
		alertify.error('Seleccione estado licencia',5);
		$('#txtLicPublicidad_edit').focus();
		return false;
	}
	else if(txtLicPublicidad_edit == "CONCLUIDO")
	{
		if (txtCondicionLicPublicidad_edit == "")
		{
			alertify.error('Seleccione la condición',5);
			$('#txtCondicionLicPublicidad_edit').focus();
			return false;
		}

		if(fileArchivoLicPublicidad_edit.files.length == 0 )
		{
			alertify.error('Seleccione el file',5);
			$("#fileArchivoLicPublicidad_edit").focus();
			return false;
		}
	}

	var form_data = (new FormData(this));
	form_data.append("post_archivo_publicidad_edit", 1);
	form_data.append("contrato_id_autorizacion", contrato_id_autorizacion);
	form_data.append("autorizacion_id", autorizacion_id);
	form_data.append("contrato_nombre_local_autorizacion", contrato_nombre_local_autorizacion);

	form_data.append("txtLicPublicidad_edit", txtLicPublicidad_edit);
	form_data.append("txtCondicionLicPublicidad_edit", txtCondicionLicPublicidad_edit);
	form_data.append("txtFechaVencimientoLicPublicidad_edit", txtFechaVencimientoLicPublicidad_edit);
	form_data.append("txtFechaRenovacionLiPublicidad_edit", txtFechaRenovacionLiPublicidad_edit);

	// loading(true);
	$.ajax({
		url: "/sys/set_contrato_licencias.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {

			result = JSON.parse(response);
			loading();
			if(result.status)
			{
				// m_reload();
				$('#fileArchivoLicPublicidad_edit').val('');
				$('#txtFileLicPublicidad_edit').html('');
				sec_contrato_listado_licencias_funcionamiento(6);
				$('#modal_editar_autorizacion').modal('hide');
				
				swal(result.message, "", "success");

			}
			else
			{
				swal(
				{
					type: "warning",
					title: "Alerta!",
					text: result.message,
					html: true,
				});
			}
			// filter_archivos_table(0);
		},
		always: function(data){
			loading();
			console.log(data);
		}
	});
});
//AGREGAR DIRECCION MUNICIPAL
function sec_contrato_detalle_solicitud_guardar_direccion_municipal() {
	var contrato_id = $("#contrato_id").val();
	var direccion_municipal = $('#direccion_municipal').val().trim();



	if (direccion_municipal.length == 0) {
		alertify.error('Ingrese dirección municipal', 5);
		$("#direccion_municipal").focus();
		return false;
	}
	if (direccion_municipal.length >= 1000) {
		alertify.error('Tamaño maximo de caracteres permitidos (1000)', 5);
		$("#direccion_municipal").focus();
		return false;
	}
	var data = {
		"accion": "guardar_direccion_municipal",
		"contrato_id": contrato_id,
		"direccion_municipal": direccion_municipal,
	}
	swal(
		{
			title: "¿Desea guardar esta dirección municipal?",
			type: "warning",
			timer: 10000,
			showCancelButton: true,
			closeOnConfirm: true,
			confirmButtonColor: "#3085d6",
			confirmButtonText: "Aceptar",
			cancelButtonText: "Cancelar"
		},
		function (result) {
			if (result) {
				auditoria_send({ "proceso": "guardar_direccion_municipal", "data": data });

				$.ajax({
					url: "/sys/set_contrato_licencias.php",
					type: 'POST',
					data: data,
					beforeSend: function () {
						loading("true");
					},
					complete: function () {
						loading();
					},
					success: function (resp) {
						console.log(resp);
						var respuesta = JSON.parse(resp);

						auditoria_send({ proceso: "guardar_autorizacion_municipal", data: respuesta });

						if (parseInt(respuesta.http_code) == 200) {
							swal({
								title: "Registro exitoso",
								text: "La dirección municipal se actualizo correctamente",
								html: true,
								type: "success",
								timer: 6000,
								closeOnConfirm: false,
								showCancelButton: false,
							});

						} else {
							swal({
								title: "Error al registrar la dirección municipal ",
								text: respuesta.error,
								html: true,
								type: "warning",
								closeOnConfirm: false,
								showCancelButton: false,
							});
						}

					},
					error: function () { }
				});
			}
		});


}
function cont_habrir_modal_editar_licencia(licencia_id,contrato_id,tipo_archivo){

	var data = {
		"accion": "get_licencia",
		"licencia_id": licencia_id,
		"contrato_id": contrato_id,
		"tipo_archivo":tipo_archivo
	}
	console.log(data);
	$.ajax({
		url: "/sys/set_contrato_licencias.php",
		type: 'POST',
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


			auditoria_send({ proceso: "get_licencia", data: respuesta });

				switch(tipo_archivo){
					case	4	:	
								$('#modal_editar_licencia').modal('show');

								$('#contrato_licencia_id').val(respuesta.data.id);
								if (respuesta.data.status_licencia == "CONCLUIDO") {
									var selectElement = document.getElementById("txtLicFuncionamiento_edit");
									selectElement.value = respuesta.data.status_licencia;
									var selectElement2 = document.getElementById("txtCondicionLicFuncionamiento_edit");
									selectElement2.value = respuesta.data.condicion;
									$('#cont_licencias_div_condicion_funcionamiento_edit').show();
									$('#cont_licencias_div_file_funcionamiento_edit').show();
								}
								else {
									$('#cont_licencias_div_condicion_funcionamiento_edit').hide();
									$('#cont_licencias_div_fecha_vencimiento_funcionamiento_edit').hide();
									$('#cont_licencias_div_fecha_renovacion_funcionamiento_edit').hide();
									$('#cont_licencias_div_file_funcionamiento_edit').hide();
								}
				
				
								if (respuesta.data.condicion == "TEMPORAL") {
									var selectElement2 = document.getElementById("txtCondicionLicFuncionamiento_edit");
									selectElement2.value = respuesta.data.condicion;
									$('#cont_licencias_div_fecha_vencimiento_funcionamiento_edit').show();
									$('#cont_licencias_div_fecha_renovacion_funcionamiento_edit').show();
				
									$('#txtFechaVencimientoLicFuncionamiento_edit').val(respuesta.data.fecha_vencimiento)
									$('#txtFechaRenovacionLicFuncionamiento_edit').val(respuesta.data.fecha_renovacion)
								}
								else {
									$('#cont_licencias_div_fecha_vencimiento_funcionamiento_edit').hide();
									$('#cont_licencias_div_fecha_renovacion_funcionamiento_edit').hide();
								}

								break;

					case	5	:
								$('#modal_editar_cert_indeci').modal('show');

								$('#cert_indeci_id').val(respuesta.data.id);
								if (respuesta.data.status_licencia == "CONCLUIDO") {
									var selectElement = document.getElementById("txtLicIndeci_edit");
									selectElement.value = respuesta.data.status_licencia;
									var selectElement2 = document.getElementById("txtFechaVencimientoLicIndeci_edit");
									selectElement2.value = respuesta.data.condicion;
				
									$('#txtFechaVencimientoLicIndeci_edit').val(respuesta.data.fecha_vencimiento)
									$('#txtFechaRenovacionLicIndeci_edit').val(respuesta.data.fecha_renovacion)
									$('#cont_licencias_div_file_indeci_edit').show();
									$('#cont_licencias_div_fecha_vencimiento_indeci_edit').show();
									$('#cont_licencias_div_fecha_renovacion_indeci_edit').show();
								}
								else {
									
									$('#cont_licencias_div_file_indeci_edit').hide();
									$('#cont_licencias_div_fecha_vencimiento_indeci_edit').hide();
									$('#cont_licencias_div_fecha_renovacion_indeci_edit').hide();
								} break;
					case 	6	: 
					
								$('#modal_editar_autorizacion').modal('show');

								$('#autorizacion_id').val(respuesta.data.id);
								if (respuesta.data.status_licencia == "CONCLUIDO") {
									var selectElement = document.getElementById("txtLicPublicidad_edit");
									selectElement.value = respuesta.data.status_licencia;
									var selectElement2 = document.getElementById("txtCondicionLicPublicidad_edit");
									selectElement2.value = respuesta.data.condicion;
									$('#cont_licencias_div_condicion_publicidad_edit').show();
									$('#cont_licencias_div_file_publicidad_edit').show();
								}
								else {
									$('#cont_licencias_div_condicion_publicidad_edit').hide();
									$('#cont_licencias_div_fecha_vencimiento_publicidad_edit').hide();
									$('#cont_licencias_div_fecha_renovacion_publicidad_edit').hide();
									$('#cont_licencias_div_file_publicidad_edit').hide();
								}
				
				
								if (respuesta.data.condicion == "TEMPORAL") {
									var selectElement2 = document.getElementById("txtCondicionLicFuncionamiento_edit");
									selectElement2.value = respuesta.data.condicion;
									$('#cont_licencias_div_fecha_vencimiento_publicidad_edit').show();
									$('#cont_licencias_div_fecha_renovacion_publicidad_edit').show();
									
									$('#txtFechaVencimientoLicPublicidad_edit').val(respuesta.data.fecha_vencimiento)
									$('#txtFechaRenovacionLiPublicidad_edit').val(respuesta.data.fecha_renovacion)
								}
								else {
									$('#cont_licencias_div_fecha_vencimiento_publicidad_edit').hide();
									$('#cont_licencias_div_fecha_renovacion_publicidad_edit').hide();
								}
					
								break;
					case	7	:
								$('#modal_editar_declaracion').modal('show');
								$('#declaracion_id').val(respuesta.data.id);
								
								break;


					
				}
				


		},
		error: function () { }
	});


}


// CARGAR TABLA LICENCIAS
$("#lic_funcionamiento_heading a").on('click',function() {
	
	if(!$("#lic_funcionamiento_body").hasClass("loaded")) {
		sec_contrato_listado_licencias_funcionamiento(4);

		$("#lic_funcionamiento_body").css("pointer-events", "auto");
	 	$(this).off("shown.bs.collapse");
	}

});
$("#lic_indeci_heading a").on('click',function() {
	// tabla_autorizacion_municipales.clear().draw();

	if(!$("#lic_indeci_body").hasClass("loaded")) {
		sec_contrato_listado_licencias_funcionamiento(5);

		$("#lic_indeci_body").css("pointer-events", "auto");
	 	$(this).off("shown.bs.collapse");
	}
	

});
$("#lic_publicidad_heading a").on('click',function() {
	if(!$("#lic_publicidad_body").hasClass("loaded")) {
		sec_contrato_listado_licencias_funcionamiento(6);

		$("#lic_publicidad_body").css("pointer-events", "auto");
	 	$(this).off("shown.bs.collapse");
	}
	

});
$("#lic_declaracion_jurada_heading a").on('click',function() {
	if(!$("#lic_declaracion_jurada_body").hasClass("loaded")) {
		sec_contrato_listado_declaraciones_juradas();

		$("#lic_declaracion_jurada_body").css("pointer-events", "auto");
	 	$(this).off("shown.bs.collapse");
	}
	

});
function sec_contrato_listado_declaraciones_juradas(){
	contrato_id	= $("#contrato_id").val();
	tipo_autorizacion	=	7;
		set_data = { sec_contrato_licencias_list: 1,contrato_id:contrato_id,tipo_autorizacion:tipo_autorizacion};
		$.ajax({
			url: "/sys/set_contrato_licencias.php",
			data: set_data,
			type: 'POST',
			complete: function () {
				loading();
			},
			success: function (response_table_licencias) {//  alert(datat)
				var response_table_licencias = JSON.parse(response_table_licencias);

				var data_licencias_funcionamiento = response_table_licencias.lista;
				auditoria_send({ "proceso": "sec_contrato_licencias_list", "data": set_data });
				tabla_autorizacion_municipales= $('#licencias_detalle_declaracion_jurada_datatable').DataTable({

					"bDestroy": true,
    				"deferRender": true,
					aLengthMenu:[6, 15]
					,"order": [[ 5, 'desc' ]],
					"language": {
						"search": "Buscar:",
						"lengthMenu": "Mostrar _MENU_ registros por página",
						"zeroRecords": "No se encontraron registros",
						"info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
						"infoEmpty": "No hay registros",
						"infoFiltered": "(filtrado de _MAX_ total records)",
						"paginate": {
							"first": "Primero",
							"last": "Último",
							"next": "Siguiente",
							"previous": "Anterior"
						},
						sProcessing: "Procesando..."
					},
					data: data_licencias_funcionamiento,

					
					order: [],  
					columns: [
						{
							title: "<div class='text-center'>Fech. Creación</div>",
							data: 'created_at',
							class: "text-left"

						},
						{
							title: "<div class='text-center'>Giro</div>",
							data: 'giro',
							class: "text-left"

						},
						 

						{
							title: "<div class='text-center'>Acciones</div>",
					
							width: "150px",
							"render": function (data, type, row) {
								// var ided = row["id"];

								var html = "<div style='text-align: center;'>";
								if(row["nombre_file"]!==null){
									html += ' <a class="btn btn-rounded btn-primary btn-sm" title="Ver documento" onclick="cont_licenciafileVerLicenciaEnVisor(\''+row["extension"]+'\',\''+row["download_file"]+'\')">';
									html += ' <i class="fa fa-eye"></i>';
	
									html += '</a>';
								}
								if(row["estado"]==1){
									html += ' <a class="btn btn-rounded btn-default btn-sm btn-edit" title="Editar" onclick="cont_habrir_modal_editar_licencia('+row["id"]+','+row["contrato_id"]+','+tipo_autorizacion+')">';
									html += ' <i class="glyphicon glyphicon-edit"></i>';
	
									html += '</a>';
								}


								html += '</div>';
								return html;
							}

						},

					],
				});
			},
			error: function () {
				set_data.error = respuesta_mantenimiento_list.error_ex;
				swal(
					{
						title: "Error de Actualización" ,
						text: respuesta_mantenimiento_list.error_ex,
						type: "warning",
						timer: 10000,
						showCancelButton: false,
						closeOnConfirm: true,
						confirmButtonColor: "#3085d6",
						confirmButtonText: "Aceptar",
					}
				);
				auditoria_send({ "proceso": "sec_contrato_licencias_list", "data": set_data });
			}
		});
}
function sec_contrato_listado_licencias_funcionamiento(tipo_autorizacion){

	switch(tipo_autorizacion){
		case 4: name_table_licencia		=	'#licencias_detalle_funcionamiento_datatable';break;
		case 5: name_table_licencia		=	'#licencias_detalle_indeci_datatable';break;
		case 6: name_table_licencia		=	'#licencias_detalle_publicidad_datatable';break;
		case 7: $name_table_licencia	=	'#licencias_detalle_declaracion_jurada_datatable';break;
	}
	
		contrato_id	= $("#contrato_id").val();
		set_data = { sec_contrato_licencias_list: 1,contrato_id:contrato_id,tipo_autorizacion:tipo_autorizacion};
		$.ajax({
			url: "/sys/set_contrato_licencias.php",
			data: set_data,
			type: 'POST',
			complete: function () {
				loading();
			},
			success: function (response_table_licencias) {//  alert(datat)
				var response_table_licencias = JSON.parse(response_table_licencias);

				var data_licencias_funcionamiento = response_table_licencias.lista;
				auditoria_send({ "proceso": "sec_contrato_licencias_list", "data": set_data });

				
				// tabla_autorizacion_municipales.clear().draw();
				tabla_autorizacion_municipales= $(name_table_licencia).DataTable({

					"bDestroy": true,
					
    				"deferRender": true,
					
					aLengthMenu:[6, 15]
					,"order": [[ 5, 'desc' ]],
					"language": {
						"search": "Buscar:",
						"lengthMenu": "Mostrar _MENU_ registros por página",
						"zeroRecords": "No se encontraron registros",
						"info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
						"infoEmpty": "No hay registros",
						"infoFiltered": "(filtrado de _MAX_ total records)",
						"paginate": {
							"first": "Primero",
							"last": "Último",
							"next": "Siguiente",
							"previous": "Anterior"
						},
						sProcessing: "Procesando..."
					},
					data: data_licencias_funcionamiento,

					
					order: [],
					columns: [
						{
							title: "<div class='text-center'>Estado</div>",
							data: 'status_licencia',
							class: "text-left"

						},
						{
							title: "<div class='text-center'>Condición</div>",
							data: "condicion",
							class: "text-left"

						},
						{
							title: "<div class='text-center'>Fech. Vencimiento</div>",
							
							data: "fecha_vencimiento",
							class: "text-left"

						},
						{
							title: "<div class='text-center'>Fech. Renovacíón</div>",
							data: "fecha_renovacion",
							class: "text-left"

						},
						{
							title: "<div class='text-center'>Fech. Creación</div>",
							
							data: "created_at",
							class: "text-left"
						},
						{
							title: "<div class='text-center'>Vigente</div>",
							"render": function (data, type, row) {
								var status_valor = row["estado"];
								var html_status = '';
								if (status_valor == 1)
									html_status = 'SI';
								else
									html_status = 'NO';

								return html_status;
							}

						},

						{
							title: "<div class='text-center'>Acciones</div>",
					
							width: "150px",
							"render": function (data, type, row) {
								// var ided = row["id"];

								var html = "<div style='text-align: center;'>";
								if(row["nombre_file"]!==null){
									html += ' <a class="btn btn-rounded btn-primary btn-sm" title="Ver documento" onclick="cont_licenciafileVerLicenciaEnVisor(\''+row["extension"]+'\',\''+row["download_file"]+'\')">';
									html += ' <i class="fa fa-eye"></i>';
	
									html += '</a>';
								}
								if(row["estado"]==1){
									html += ' <a class="btn btn-rounded btn-default btn-sm btn-edit" title="Editar" onclick="cont_habrir_modal_editar_licencia('+row["id"]+','+row["contrato_id"]+','+tipo_autorizacion+')">';
									html += ' <i class="glyphicon glyphicon-edit"></i>';
	
									html += '</a>';
								}


								html += '</div>';
								return html;
							}

						},

					],
				});
			},
			error: function () {
				set_data.error = respuesta_mantenimiento_list.error_ex;
				swal(
					{
						title: "Error de Actualización" ,
						text: respuesta_mantenimiento_list.error_ex,
						type: "warning",
						timer: 10000,
						showCancelButton: false,
						closeOnConfirm: true,
						confirmButtonColor: "#3085d6",
						confirmButtonText: "Aceptar",
					}
				);
				auditoria_send({ "proceso": "sec_contrato_licencias_list", "data": set_data });
			}
		});
		
	
}


$("#txtLicIndeci").on("change", function()
{
	var selectValor = $(this).val();

	if(selectValor == "CONCLUIDO")
	{
		$('#cont_licencias_div_fecha_vencimiento_indeci').show();
		$('#cont_licencias_div_fecha_renovacion_indeci').show();
		$('#cont_licencias_div_file_indeci').show();
	}
	else
	{
		$('#cont_licencias_div_fecha_vencimiento_indeci').hide();
		$('#cont_licencias_div_fecha_renovacion_indeci').hide();
		$('#cont_licencias_div_file_indeci').hide();
	}
})
$("#txtLicIndeci_edit").on("change", function()
{
	var selectValor = $(this).val();

	if(selectValor == "CONCLUIDO")
	{
		$('#cont_licencias_div_fecha_vencimiento_indeci_edit').show();
		$('#cont_licencias_div_fecha_renovacion_indeci_edit').show();
		$('#cont_licencias_div_file_indeci_edit').show();
	}
	else
	{
		$('#cont_licencias_div_fecha_vencimiento_indeci_edit').hide();
		$('#cont_licencias_div_fecha_renovacion_indeci_edit').hide();
		$('#cont_licencias_div_file_indeci_edit').hide();
	}
})

$(document).on('submit', "#formularioIndeci", function(e)
{
	e.preventDefault();

	var contrato_id = $("#contrato_id").val();
	var contrato_nombre_local = $("#contrato_nombre_local").val();
	var fileArchivoLicIndeci = document.getElementById("fileArchivoLicIndeci");

	var txtLicIndeci = $("#txtLicIndeci").val();
	var txtFechaVencimientoLicIndeci = $("#txtFechaVencimientoLicIndeci").val();
	var txtFechaRenovacionLicIndeci = $("#txtFechaRenovacionLicIndeci").val();

	if(txtLicIndeci == "")
	{
		alertify.error('Seleccione estado licencia',5);
		$('#txtLicIndeci').focus();
		return false;
	}
	else if(txtLicIndeci == "CONCLUIDO")
	{
		if(fileArchivoLicIndeci.files.length == 0 )
		{
			alertify.error('Seleccione el file',5);
			$("#fileArchivoLicIndeci").focus();
			return false;
		}
	}

	var form_data = (new FormData(this));
	form_data.append("post_archivo_indeci", 1);
	form_data.append("contrato_id", contrato_id);
	form_data.append("contrato_nombre_local", contrato_nombre_local);

	form_data.append("txtLicIndeci", txtLicIndeci);
	form_data.append("txtFechaVencimientoLicIndeci", txtFechaVencimientoLicIndeci);
	form_data.append("txtFechaRenovacionLicIndeci", txtFechaRenovacionLicIndeci);

	loading(true);

	$.ajax({
		url: "/sys/set_contrato_licencias.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {
			result = JSON.parse(response);
			loading();
			if(result.status)
			{
				// m_reload();
				$('#fileArchivoLicIndeci').val('');
				$('#txtFileLicIndeci').html('');
				sec_contrato_listado_licencias_funcionamiento(5);
				
				swal(result.message, "", "success");

			}
			else
			{
				swal(
				{
					type: "warning",
					title: "Alerta!",
					text: result.message,
					html: true,
				});
			}
		},
		always: function(data){
			loading();
			console.log(data);
		}
	});
});

$("#txtLicPublicidad").on("change", function()
{
	var selectValor = $(this).val();

	if(selectValor == "CONCLUIDO")
	{
		$('#cont_licencias_div_condicion_publicidad').show();
		$('#cont_licencias_div_file_publicidad').show();
	}
	else
	{
		$('#cont_licencias_div_condicion_publicidad').hide();
		$('#cont_licencias_div_fecha_vencimiento_publicidad').hide();
		$('#cont_licencias_div_fecha_renovacion_publicidad').hide();
		$('#cont_licencias_div_file_publicidad').hide();
	}
})

$("#txtCondicionLicPublicidad").on("change", function()
{
	var selectValor = $(this).val();

	if(selectValor == "TEMPORAL")
	{
		$('#cont_licencias_div_fecha_vencimiento_publicidad').show();
		$('#cont_licencias_div_fecha_renovacion_publicidad').show();
	}
	else
	{
		$('#cont_licencias_div_fecha_vencimiento_publicidad').hide();
		$('#cont_licencias_div_fecha_renovacion_publicidad').hide();
	}
})
$("#txtLicPublicidad_edit").on("change", function()
{
	var selectValor = $(this).val();

	if(selectValor == "CONCLUIDO")
	{
		$('#cont_licencias_div_condicion_publicidad_edit').show();
		$('#cont_licencias_div_file_publicidad_edit').show();
	}
	else
	{
		$('#cont_licencias_div_condicion_publicidad_edit').hide();
		$('#cont_licencias_div_fecha_vencimiento_publicidad_edit').hide();
		$('#cont_licencias_div_fecha_renovacion_publicidad_edit').hide();
		$('#cont_licencias_div_file_publicidad_edit').hide();
	}
})

$("#txtCondicionLicPublicidad_edit").on("change", function()
{
	var selectValor = $(this).val();

	if(selectValor == "TEMPORAL")
	{
		$('#cont_licencias_div_fecha_vencimiento_publicidad_edit').show();
		$('#cont_licencias_div_fecha_renovacion_publicidad_edit').show();
	}
	else
	{
		$('#cont_licencias_div_fecha_vencimiento_publicidad_edit').hide();
		$('#cont_licencias_div_fecha_renovacion_publicidad_edit').hide();
	}
})
$(document).on('submit', "#formularioPublicidad", function(e)
{
	e.preventDefault();
	var contrato_id = $("#contrato_id").val();
	var contrato_nombre_local = $("#contrato_nombre_local").val();
	var fileArchivoLicPublicidad = document.getElementById("fileArchivoLicPublicidad");

	var txtLicPublicidad = $("#txtLicPublicidad").val();
	var txtCondicionLicPublicidad = $("#txtCondicionLicPublicidad").val();
	var txtFechaVencimientoLicPublicidad = $("#txtFechaVencimientoLicPublicidad").val();
	var txtFechaRenovacionLiPublicidad = $("#txtFechaRenovacionLiPublicidad").val();

	if(txtLicPublicidad == "")
	{
		alertify.error('Seleccione estado licencia',5);
		$('#txtLicPublicidad').focus();
		return false;
	}
	else if(txtLicPublicidad == "CONCLUIDO")
	{
		if (txtCondicionLicPublicidad == "")
		{
			alertify.error('Seleccione la condición',5);
			$('#txtCondicionLicPublicidad').focus();
			return false;
		}

		if(fileArchivoLicPublicidad.files.length == 0 )
		{
			alertify.error('Seleccione el file',5);
			$("#fileArchivoLicPublicidad").focus();
			return false;
		}
	}

	var form_data = (new FormData(this));
	form_data.append("post_archivo_publicidad", 1);
	form_data.append("contrato_id", contrato_id);
	form_data.append("contrato_nombre_local", contrato_nombre_local);

	form_data.append("txtLicPublicidad", txtLicPublicidad);
	form_data.append("txtCondicionLicPublicidad", txtCondicionLicPublicidad);
	form_data.append("txtFechaVencimientoLicPublicidad", txtFechaVencimientoLicPublicidad);
	form_data.append("txtFechaRenovacionLiPublicidad", txtFechaRenovacionLiPublicidad);

	loading(true);

	$.ajax({
		url: "/sys/set_contrato_licencias.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {

			result = JSON.parse(response);
			loading();
			if(result.status)
			{
				// m_reload();
				$('#fileArchivoLicPublicidad').val('');
				$('#txtFileLicPublicidad').html('');
				sec_contrato_listado_licencias_funcionamiento(6);

				swal(result.message, "", "success");

			}
			else
			{
				swal(
				{
					type: "warning",
					title: "Alerta!",
					text: result.message,
					html: true,
				});
			}
		},
		always: function(data)
		{
			loading();
			console.log(data);
		}
	});
});

$(document).on('submit', "#formularioDeclaracionJurada", function(e)
{

	var contrato_id = $("#contrato_id").val();
	var contrato_nombre_local = $("#contrato_nombre_local").val();
	var fileArchivoLicDeclaracionJurada = document.getElementById("fileArchivoLicDeclaracionJurada");

	var txtDeclaracionJurada = $("#txtDeclaracionJurada").val();
	var txtNuevoDeclaracionJurada = $("#txtNuevoDeclaracionJurada").val();


	e.preventDefault();

	

		if(fileArchivoLicDeclaracionJurada.files.length == 0 )
		{
			alertify.error('Seleccione el file',5);
			$("#fileArchivoLicDeclaracionJurada").focus();
			return false;
		}
	
	var form_data = (new FormData(this));
	form_data.append("post_archivo_declaracion_jurada", 1);
	form_data.append("contrato_id", contrato_id);
	form_data.append("contrato_nombre_local", contrato_nombre_local);

	form_data.append("txtDeclaracionJurada", txtDeclaracionJurada);
	form_data.append("txtNuevoDeclaracionJurada", txtNuevoDeclaracionJurada);

	loading(true);
	$.ajax({
		url: "/sys/set_contrato_licencias.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {
			result = JSON.parse(response);
			loading();
			if(result.status)
			{
				$('#fileArchivoLicDeclaracionJurada').val('');
				$('#txtFileLicDeclaracionJurada').html('');
				sec_contrato_listado_declaraciones_juradas();
				// m_reload();
				swal(result.message, "", "success");

			}
			else
			{
				swal(
				{
					type: "warning",
					title: "Alerta!",
					text: result.message,
					html: true,
				});
			}
			//filter_archivos_table(0);
		},
		always: function(data){
			loading();
			console.log(data);
		}
	});
});
$('#txtDeclaracionJurada').one('select2:open', function() {
	cargarlistado_giros();
	 
});
function cargarlistado_giros(id=null){
	var	declaracion_jurada_id	=	$('#declaracion_jurada_id').val();
	$.ajax ({
		url: "/sys/set_contrato_licencias.php",
		data: {
			get_listado_giros:1
		},
		type: 'POST',
		complete: function () {
			loading();
		},
		success: function(data) {
			var data = JSON.parse(data);
			$('#txtDeclaracionJurada').empty();
			var options = '';
			console.log(data.lista);

			data.lista.forEach(function(item) {
				if(id==item.id){
					options += '<option value="' + item.id + '" selected>' + item.nombre_declaracion_jurada + '</option>';

				}else{
					if(item.id==declaracion_jurada_id){
						options += '<option value="' + item.id + '" selected>' + item.nombre_declaracion_jurada + '</option>';
					}else{
						options += '<option value="' + item.id + '">' + item.nombre_declaracion_jurada + '</option>';
					}
					
				}
				
			});

			$('#txtDeclaracionJurada').append(options);
			$('#txtDeclaracionJurada').trigger('change.select2');
		},
		cache: true
	})
}


$(document).on('submit', "#formularioDeclaracionJurada_edit", function(e)
{

	var contrato_id = $("#contrato_id_declaracion").val();
	var contrato_nombre_local = $("#contrato_nombre_local_declaracion").val();
	var declaracion_id = $("#declaracion_id").val();

	var fileArchivoLicDeclaracionJurada_edit = document.getElementById("fileArchivoLicDeclaracionJurada_edit");


	e.preventDefault();
	if(fileArchivoLicDeclaracionJurada_edit.files.length == 0 )
		{
			alertify.error('Seleccione el file',5);
			$("#fileArchivoLicDeclaracionJurada_edit").focus();
			return false;
		}
	var form_data = (new FormData(this));
	form_data.append("post_archivo_declaracion_jurada_edit", 1);
	form_data.append("contrato_id", contrato_id);
	form_data.append("declaracion_id", declaracion_id);
	form_data.append("contrato_nombre_local", contrato_nombre_local);

	auditoria_send({ "proceso": "post_archivo_declaracion_jurada_edit", "data": set_data });

	loading(true);

	$.ajax({
		url: "/sys/set_contrato_licencias.php",
		type: "POST",
		data: form_data,
		cache: false,
		contentType: false,
		processData:false,
		success: function(response, status) {
			result = JSON.parse(response);
			loading();
			if(result.status)
			{
				// m_reload();
				$('#fileArchivoLicDeclaracionJurada_edit').val('');
				$('#txtFileLicDeclaracionJurada_edit').html('');
				sec_contrato_listado_declaraciones_juradas();
				$('#modal_editar_declaracion').modal('hide');
				swal(result.message, "", "success");

			}
			else
			{
				swal(
				{
					type: "warning",
					title: "Alerta!",
					text: result.message,
					html: true,
				});
			}
		},
		always: function(data){
			loading();
			console.log(data);
		}
	});
});
function sec_contratos_licencias_detalle_Funcionamiento()
{

	table_detalle_lic_funcionamiento = $('#licencias_detalle_funcionamiento_datatable').DataTable(
	 {
		language:{
			"decimal":        "",
			"emptyTable":     "Tabla vacia",
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
		}
		,aLengthMenu:[6, 15]
		,"order": [[ 5, 'desc' ]]
	});
}

function sec_contratos_licencias_detalle_Indeci()
{

	var table_detalle_lic_indeci = $('#licencias_detalle_indeci_datatable').DataTable(
	 {
		language:{
			"decimal":        "",
			"emptyTable":     "Tabla vacia",
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
		}
		,aLengthMenu:[6, 15]
		,"order": [[ 4, 'desc' ]]
	});
}


function sec_contratos_licencias_detalle_Publicidad()
{

	var table_detalle_lic_publicidad = $('#licencias_detalle_publicidad_datatable').DataTable(
	 {
		language:{
			"decimal":        "",
			"emptyTable":     "Tabla vacia",
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
		}
		,aLengthMenu:[6, 15]
		,"order": [[ 5, 'desc' ]]
	});
}

function aggNuevoDJ()
{
	$("#divRegistrarDeclaracionJurada").hide();
	$("#divNuevaDeclaracionJurada").show();

}

function omitirNuevoDJ()
{

	$("#divNuevaDeclaracionJurada").hide();
	$("#txtNuevoDeclaracionJurada").val("");

	$("#txtFileLicDeclaracionJurada").html("");
	$("#fileArchivoLicDeclaracionJurada").html("");
	$("#fileArchivoLicDeclaracionJurada").val("");
	$("#divRegistrarDeclaracionJurada").show();

}
$('#select_add_giro_btn').on('click',function(){ // MOSTRAR MODAL PAR REGISTRAR GIRO (DECLARACION JURADA)
	$('#txtNuevoDeclaracionJurada').focus();

	$('#modal_registrar_giro').modal('show');

})
function btnGrabarDeclaracionJuradaNuevo()
{
	var txtNuevoDeclaracionJurada = $("#txtNuevoDeclaracionJurada").val();

	if(txtNuevoDeclaracionJurada == "")
	{
		alertify.error('Ingresar declaración jurada',5);
		$('#txtNuevoDeclaracionJurada').focus();
		return false;
	}
	else
	{
		btnGrabarDeclaracionJurada();
	}
}

function btnGrabarDeclaracionJurada()
{

	var contrato_id = $("#contrato_id").val();

	var txtDeclaracionJurada = $("#txtDeclaracionJurada").val();
	var txtNuevoDeclaracionJurada = $("#txtNuevoDeclaracionJurada").val();


	var data = {
		"accion": "actualizar_licencias_declaracion_jurada",
		"contrato_id" : contrato_id,

		"txtDeclaracionJurada" : txtDeclaracionJurada,
		"txtNuevoDeclaracionJurada" : txtNuevoDeclaracionJurada
	}
	console.log(data);
	loading(true);
	$.ajax({
			url: "/sys/set_contrato_licencias.php",
			type: "POST",
			data: data,
			//cache: false,
			//contentType: false,
			//processData:false,
			success: function(response, status)
			{

				result = JSON.parse(response);
				loading();
				if(result.status)
				{
					// m_reload();
					// cargarlistado_giros();
					$('#fileArchivoLicDeclaracionJurada').val('');
					$('#txtFileLicDeclaracionJurada').html('');

					cargarlistado_giros(result.id);

					$('#modal_registrar_giro').modal('hide');

					swal(result.message, "", "success");

				}
				else
				{
					swal(
					{
						type: "warning",
						title: "Alerta!",
						text: result.message,
						html: true,
					});
				}
				//filter_archivos_table(0);
			},
			always: function(data)
			{
				loading();
				console.log(data);
			}
		});
}



function m_reload()
{
	console.log("m_reload:reload");
	window.location.reload();
}

function cont_licenciafileVerLicenciaEnVisor(tipo_documento, ruta_file)
{
	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf')
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" width="500" height="525"></iframe>';
		$('#cont_licienciasDivVisorPdfModal').html(htmlModal);

		$('#exampleModalPreviewServicio').modal('show');

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
