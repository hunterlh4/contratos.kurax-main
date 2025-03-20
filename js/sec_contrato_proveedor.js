var tabla;

function sec_contrato_proveedor() {
	$(".cont_proveedor_param_ruc").mask("00000000000");
	sec_contrato_proveedor_cargar_fechas();
	sec_contrato_proveedor_cargar_data_ini();
	sec_contrato_solicitud_obtener_opciones("obtener_directores", $("[name='director_aprobacion_id']"));

	$(".select2").select2({
		width: "100%"
	});

	$('.limpiar_input').click(function() {
		$('#' + $(this).attr("limpiar")).val('');
	});

	$('.limpiar_select2').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val("").trigger("change");
		cont_proveedor_buscar_proveedor_por_parametros();
	});

	$('.change-contrato-proveedor-firmado').on('select2:select', function (e) {
		cont_proveedor_buscar_proveedor_por_parametros();
	});


	$('#btn_limpiar_filtros_de_busqueda').click(function() {
		$('#cont_proveedor_param_empresa').select2().val('').trigger("change");
		$('#cont_proveedor_param_area_solicitante').select2().val('').trigger("change");
		$('#cont_proveedor_param_ruc').val('');
		$('#cont_proveedor_param_razon_social').val('');
		$('#cont_proveedor_param_moneda').select2().val('').trigger("change");
		$('#fecha_inicio_solicitud').val('');
		$('#fecha_fin_solicitud').val('');
		$('#fecha_inicio_inicio').val('');
		$('#fecha_fin_inicio').val('');
		$('#director_aprobacion_id').val('');
	});

	$(".sec_contrato_solicitud_datepicker").datepicker({
		dateFormat: "yy-mm-dd",
		changeMonth: true,
		changeYear: true,
	});
}

function sec_contrato_proveedor_cargar_fechas() {
	// INICIO INICIALIZACION DE DATEPICKER
	$(".cont_proveedor_datepicker").datepicker({
		dateFormat: "yy-mm-dd",
		changeMonth: true,
		changeYear: true,
	}).on("change", function(ev) {
		$(this).datepicker("hide");
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
		// localStorage.setItem($(this).atrr("id"),)
	});
}

function sec_contrato_solicitud_obtener_opciones(accion, select) {
	$.ajax({
		url: "/sys/set_contrato_nuevo.php",
		type: "POST",
		data: { accion: accion },
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			$(select).find("option").remove().end();
			$(select).append('<option value="">-- TODOS --</option>');
			$(respuesta.result).each(function (i, e) {
				opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);
			});
		},
		error: function () {},
	});
}
function sec_contrato_proveedor_cargar_data_ini() {
	// INICIO INICIALIZACION DE DATEPICKER
	if (sec_id == "contrato" && sub_sec_id == "proveedor") {
		var cont_proveedor_param_empresa = $("#cont_proveedor_param_empresa").val();
		var cont_proveedor_param_area_solicitante = $("#cont_proveedor_param_area_solicitante").val();
		var cont_proveedor_param_ruc = $("#cont_proveedor_param_ruc").val();
		var cont_proveedor_param_razon_social = $("#cont_proveedor_param_razon_social").val();
		var cont_proveedor_param_moneda = $("#cont_proveedor_param_moneda").val();
		var fecha_inicio_solicitud = $("#fecha_inicio_solicitud").val();
		var fecha_fin_solicitud = $("#fecha_fin_solicitud").val();
		var fecha_inicio_inicio = $("#fecha_inicio_inicio").val();
		var fecha_fin_inicio = $("#fecha_fin_inicio").val();

		var aprobante	=	$('#director_aprobacion_id').val();

		var search_fecha_fin_aprobacion_firmado = $('#search_fecha_fin_aprobacion_firmado').val();
		var search_fecha_inicio_aprobacion_firmado = $('#search_fecha_inicio_aprobacion_firmado').val();
		if (fecha_inicio_solicitud.length > 0 && fecha_fin_solicitud.length > 0) {
			var fecha_inicio_date = new Date(fecha_inicio_solicitud);
			var fecha_fin_date = new Date(fecha_fin_solicitud);
			if (fecha_inicio_date > fecha_fin_date) {
				alertify.error('La fecha de solicitud desde debe ser menor o igual a la fecha de solicitud hasta ',5);
				return false;
			}
		}

		if (fecha_inicio_inicio.length > 0 && fecha_fin_inicio.length > 0) {
			var fecha_inicio_date = new Date(fecha_inicio_inicio);
			var fecha_fin_date = new Date(fecha_fin_inicio);
			if (fecha_inicio_date > fecha_fin_date) {
				alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta',5);
				return false;
			}
		}

		var data = {
			accion: "cont_listar_proveedores",
			cont_proveedor_param_empresa: cont_proveedor_param_empresa,
			cont_proveedor_param_area_solicitante: cont_proveedor_param_area_solicitante,
			cont_proveedor_param_ruc: cont_proveedor_param_ruc,
			cont_proveedor_param_razon_social: cont_proveedor_param_razon_social,
			cont_proveedor_param_moneda: cont_proveedor_param_moneda,
			fecha_inicio_solicitud: fecha_inicio_solicitud,
			fecha_fin_solicitud: fecha_fin_solicitud,
			fecha_inicio_inicio: fecha_inicio_inicio,
			fecha_fin_inicio: fecha_fin_inicio,
			search_fecha_fin_aprobacion_firmado : search_fecha_fin_aprobacion_firmado,
			search_fecha_inicio_aprobacion_firmado: search_fecha_inicio_aprobacion_firmado,
			aprobante	:	aprobante,
		};
		$("#div_proveedor_boton_export").show();
		$("#cont_contrato_proveedor_div_tabla").show();

		$('#cont_locales_proveedor_datatable tfoot th').each(function () {
			var title = $(this).text();
			$(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
		});

		tabla = $("#cont_locales_proveedor_datatable").dataTable({
			language: {
				decimal: "",
				emptyTable: "No existen registros",
				info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
				infoEmpty: "Mostrando 0 a 0 de 0 entradas",
				infoFiltered: "(filtered from _MAX_ total entradas)",
				infoPostFix: "",
				thousands: ",",
				lengthMenu: "Mostrar _MENU_ entradas",
				loadingRecords: "Cargando...",
				processing: "Procesando...",
				search: "Filtrar:",
				zeroRecords: "Sin resultados",
				paginate: {
					first: "Primero",
					last: "Ultimo",
					next: "Siguiente",
					previous: "Anterior",
				},
				aria: {
					sortAscending: ": activate to sort column ascending",
					sortDescending: ": activate to sort column descending",
				},
			},
			aProcessing: true,
			aServerSide: true,
			ajax: {
				url: "/sys/set_contrato_proveedor.php",
				data: data,
				type: "POST",
				dataType: "json",
				error: function(e) {
					console.log(e.responseText);
				},
			},
			bDestroy: true,
			aLengthMenu: [10, 20, 30, 40, 50, 100],
			initComplete: function () {
				this.api()
				.columns()
				.every(function () {
					var that = this;

					$('input', this.footer()).on('keyup change clear', function () {
						if (that.search() !== this.value) {
							that.search(this.value).draw();
						}
					});
				});
			},
		}).DataTable();
	}
}

function cont_proveedor_buscar_proveedor_por_parametros() {
	sec_contrato_proveedor_listar_contratos_Datatable();
}

function sec_contrato_proveedor_listar_contratos_Datatable() {
	if (sec_id == "contrato" && sub_sec_id == "proveedor") {
		var cont_proveedor_param_empresa = $("#cont_proveedor_param_empresa").val();
		var cont_proveedor_param_area_solicitante = $("#cont_proveedor_param_area_solicitante").val();
		var cont_proveedor_param_ruc = $("#cont_proveedor_param_ruc").val();
		var cont_proveedor_param_razon_social = $("#cont_proveedor_param_razon_social").val();
		var cont_proveedor_param_moneda = $("#cont_proveedor_param_moneda").val();
		var fecha_inicio_solicitud = $("#fecha_inicio_solicitud").val();
		var fecha_fin_solicitud = $("#fecha_fin_solicitud").val();
		var fecha_inicio_inicio = $("#fecha_inicio_inicio").val();
		var fecha_fin_inicio = $("#fecha_fin_inicio").val();
		var aprobante	=	$('#director_aprobacion_id').val();

		var search_fecha_fin_aprobacion_firmado = $('#search_fecha_fin_aprobacion_firmado').val();
		var search_fecha_inicio_aprobacion_firmado = $('#search_fecha_inicio_aprobacion_firmado').val();
		

		if (fecha_inicio_solicitud.length > 0 && fecha_fin_solicitud.length > 0) {
			var fecha_inicio_date = new Date(fecha_inicio_solicitud);
			var fecha_fin_date = new Date(fecha_fin_solicitud);
			if (fecha_inicio_date > fecha_fin_date) {
				alertify.error('La fecha de solicitud desde debe ser menor o igual a la fecha de solicitud hasta ',5);
				return false;
			}
		}

		if (fecha_inicio_inicio.length > 0 && fecha_fin_inicio.length > 0) {
			var fecha_inicio_date = new Date(fecha_inicio_inicio);
			var fecha_fin_date = new Date(fecha_fin_inicio);
			if (fecha_inicio_date > fecha_fin_date) {
				alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta',5);
				return false;
			}
		}
		var data = {
			accion: "cont_listar_proveedores",
			cont_proveedor_param_empresa: cont_proveedor_param_empresa,
			cont_proveedor_param_area_solicitante: cont_proveedor_param_area_solicitante,
			cont_proveedor_param_ruc: cont_proveedor_param_ruc,
			cont_proveedor_param_razon_social: cont_proveedor_param_razon_social,
			cont_proveedor_param_moneda: cont_proveedor_param_moneda,
			fecha_inicio_solicitud: fecha_inicio_solicitud,
			fecha_fin_solicitud: fecha_fin_solicitud,
			fecha_inicio_inicio: fecha_inicio_inicio,
			fecha_fin_inicio: fecha_fin_inicio,
			search_fecha_fin_aprobacion_firmado : search_fecha_fin_aprobacion_firmado,
			search_fecha_inicio_aprobacion_firmado: search_fecha_inicio_aprobacion_firmado,
			aprobante	:	aprobante,
		};
		console.log(data);
		$("#div_proveedor_boton_export").show();
		$("#cont_contrato_proveedor_div_tabla").show();
		tabla = $("#cont_locales_proveedor_datatable").dataTable({
			language: {
				decimal: "",
				emptyTable: "No existen registros",
				info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
				infoEmpty: "Mostrando 0 a 0 de 0 entradas",
				infoFiltered: "(filtered from _MAX_ total entradas)",
				infoPostFix: "",
				thousands: ",",
				lengthMenu: "Mostrar _MENU_ entradas",
				loadingRecords: "Cargando...",
				processing: "Procesando...",
				search: "Filtrar:",
				zeroRecords: "Sin resultados",
				paginate: {
					first: "Primero",
					last: "Ultimo",
					next: "Siguiente",
					previous: "Anterior",
				},
				aria: {
					sortAscending: ": activate to sort column ascending",
					sortDescending: ": activate to sort column descending",
				},
			},
			aProcessing: true,
			aServerSide: true,
			ajax: {
				url: "/sys/set_contrato_proveedor.php",
				data: data,
				type: "POST",
				dataType: "json",
				error: function(e) {
					console.log(e.responseText);
				},
			},
			bDestroy: true,
			aLengthMenu: [10, 20, 30, 40, 50, 100],
		}).DataTable();
	}
}
$("#cont_proveedor_btn_export_proveedor").on("click", function() {
	var cont_proveedor_param_empresa = $("#cont_proveedor_param_empresa").val();
	var cont_proveedor_param_area_solicitante = $("#cont_proveedor_param_area_solicitante").val();
	var cont_proveedor_param_ruc = $("#cont_proveedor_param_ruc").val();
	var cont_proveedor_param_razon_social = $("#cont_proveedor_param_razon_social").val();
	var cont_proveedor_param_moneda = $("#cont_proveedor_param_moneda").val();
	var fecha_inicio_solicitud = $("#fecha_inicio_solicitud").val();
	var fecha_fin_solicitud = $("#fecha_fin_solicitud").val();
	var fecha_inicio_inicio = $("#fecha_inicio_inicio").val();
	var fecha_fin_inicio = $("#fecha_fin_inicio").val();

	var aprobante	=	$('#director_aprobacion_id').val();

	var search_fecha_fin_aprobacion_firmado = $('#search_fecha_fin_aprobacion_firmado').val();
	var search_fecha_inicio_aprobacion_firmado = $('#search_fecha_inicio_aprobacion_firmado').val();
		

	if (fecha_inicio_solicitud.length > 0 && fecha_fin_solicitud.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio_solicitud);
		var fecha_fin_date = new Date(fecha_fin_solicitud);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de solicitud desde debe ser menor o igual a la fecha de solicitud hasta ',5);
			return false;
		}
	}

	if (fecha_inicio_inicio.length > 0 && fecha_fin_inicio.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio_inicio);
		var fecha_fin_date = new Date(fecha_fin_inicio);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta',5);
			return false;
		}
	}
	
	var data = {
		accion: "cont_proveedor_reporte_proveedor_excel",
		cont_proveedor_param_empresa: cont_proveedor_param_empresa,
		cont_proveedor_param_area_solicitante: cont_proveedor_param_area_solicitante,
		cont_proveedor_param_ruc: cont_proveedor_param_ruc,
		cont_proveedor_param_razon_social: cont_proveedor_param_razon_social,
		cont_proveedor_param_moneda: cont_proveedor_param_moneda,
		fecha_inicio_solicitud: fecha_inicio_solicitud,
		fecha_fin_solicitud: fecha_fin_solicitud,
		fecha_inicio_inicio: fecha_inicio_inicio,
		fecha_fin_inicio: fecha_fin_inicio,

		search_fecha_fin_aprobacion_firmado : search_fecha_fin_aprobacion_firmado,
		search_fecha_inicio_aprobacion_firmado: search_fecha_inicio_aprobacion_firmado,
		aprobante	:	aprobante,
	};
	$.ajax({
		url: "/sys/set_contrato_proveedor.php",
		type: "POST",
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			let obj = JSON.parse(resp);
			window.open(obj.ruta_archivo);
			loading(false);
		},
		error: function(resp, status) {},
	});
});

function sec_contrato_proveedor_validar_solo_numeros(e) {
	var val = document.all;
	var key = val ? e.keyCode : e.which;
	if (key > 31 && (key < 48 || key > 57)) {
		if (val) {
			window.event.keyCode = 0;
		} else {
			e.stopPropagation();
			e.preventDefault();
		}
	}
}

function sec_contrato_proveedor_alerta(contrato_id) {
	$("#contrato_id_temp").val(contrato_id);
	$("#contenido_modal_alerta").html('');
	$("#modal_alertas").modal("show");
	var data = {
		accion: "obtener_info_alerta",
		contrato_id: contrato_id,
	};
	loading(true);
	$.ajax({
		url: "/sys/set_contrato_proveedor.php",
		type: "POST",
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 200) {
				var datos_alerta = respuesta.result[0];
				$("#contrato_id_temp").val(contrato_id);
				var num_dias_alerta = $.trim(datos_alerta.num_dias_para_alertar_vencimiento);
				var txt_num_dias_alerta = "";
				if (num_dias_alerta == 0 || num_dias_alerta == "") {
					txt_num_dias_alerta = "No existe numero de dias para la alerta";
				} else {
					txt_num_dias_alerta = num_dias_alerta + " dias antes de la fecha de vencimiento";
				}
				var body = '';
				body += '<tr>';
				body += '<td>' + datos_alerta.razon_social + '</td>';
				body += '<td class="text-center">' + datos_alerta.fecha_inicio + '</td>';
				body += '<td class="text-center">' + datos_alerta.fecha_vencimiento_proveedor + '</td>';
				body += '<td class="text-center">' + txt_num_dias_alerta + '</td>';
				body += '</tr>';
				loading();
				$("#contenido_modal_alerta").html(body);
				$("#num_dias").val('');
				setTimeout(function() {
					$("#num_dias").focus();
				}, 500);
				return true;
			}
		},
		error: function(textStatus) {
			console.log("La solicitud obtener datos para alerta a fallado: " + textStatus);
		},
	});
}

function sec_contrato_proveedor_registrar_alerta() {
	var contrato_id = $("#contrato_id_temp").val();
	var num_dias = $("#num_dias").val().trim();
	if (parseInt(num_dias) === 0 || num_dias === "") {
		mensajeAlerta("Advertencia:", "Tiene que ingresar la cantidad de días para la alerta a este contrato.", claseTipoAlertas.alertaWarning, $("#divMensajeAlerta"));
		return;
	}
	var data = {
		accion: "actualizar_alerta_contrato",
		contrato_id: contrato_id,
		num_dias: num_dias,
	};
	loading(true);
	$.ajax({
		url: "/sys/set_contrato_proveedor.php",
		type: "POST",
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 200) {
				$("#modal_alertas").modal("hide");
				swal({
					title: "Registro exitoso",
					text: "La alerta se guardo correctamente",
					html: true,
					type: "success",
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false
				});

				$("#cont_proveedor_btn_buscar").click();
			} else {
				swal('Aviso', respuesta.error, 'warning');
				return;
			}
			loading();
			return true;
		},
		error: function(textStatus) {
			console.log("La solicitud para actualizar alerta contrato a fallado: " + textStatus);
		},
	});
}

function sec_contrato_proveedor_btn_descargar(ruta_archivo)
{
	var extension = "";

	// Obtener el nombre del archivo
	var ultimoPunto = ruta_archivo.lastIndexOf("/");

	if(ultimoPunto !== -1)
	{
	    var extension = ruta_archivo.substring(ultimoPunto + 1);
	}
	
	// Crear un enlace temporal
    var enlace = document.createElement('a');
    enlace.href = ruta_archivo;

    // Darle un nombre al archivo que se descargará
    enlace.download = extension;

    // Simular un clic en el enlace
    document.body.appendChild(enlace);
    enlace.click();

    // Limpiar el enlace temporal
    document.body.removeChild(enlace);
}