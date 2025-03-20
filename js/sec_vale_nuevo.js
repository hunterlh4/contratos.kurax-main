function sec_vale_nuevo() {
	var fecha_hoy = moment().format('DD-MM-YYYY');
	
	$(".select2").select2({ width: "100%" });
	$(".sec_vale_nuevo_datepicker").datepicker({
		dateFormat: "dd-mm-yy",
		changeMonth: true,
		changeYear: true,
		maxDate: fecha_hoy,
	}).on("change", function (ev) {
		$(this).datepicker("hide");
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
	});

	sec_vale_nuevo_obtener_opciones("listar_empresas_por_usuario", $("[name='sec_vale_nuevo_empresa']"));
	//sec_vale_nuevo_obtener_opciones("listar_motivos_activos", $("[name='sec_vale_nuevo_motivo']"));
	
	sec_vale_nuevo_obtener_solicitante();
	sec_vale_nuevo_obtener_motivos();
	
	$("#sec_vale_nuevo_empresa").change(function () {
		$("#sec_vale_nuevo_empresa option:selected").each(function () {
			sec_vale_nuevo_obtener_zonas();
			sec_vale_nuevo_obtener_motivos();
			$("[name='sec_vale_nuevo_local']").find("option").remove().end();
			$("[name='sec_vale_nuevo_local']").append('<option value="0">- Seleccione -</option>');
			$("[name='sec_vale_nuevo_empleado']").find("option").remove().end();
			$("[name='sec_vale_nuevo_empleado']").append('<option value="0">- Seleccione -</option>');

			empresa_id = $(this).val();

			if (empresa_id == "6") {
				$('#sec_vale_nuevo_local').val('0').trigger('change.select2');
				$('#sec_vale_nuevo_local').attr('disabled',true);
			}else{
				$('#sec_vale_nuevo_local').attr('disabled',false);
			}
			
		});
	});

	$("#sec_vale_nuevo_zona").change(function () {
		$("#sec_vale_nuevo_zona option:selected").each(function () {
			sec_vale_nuevo_obtener_locales_por_zona();
			sec_vale_nuevo_obtener_empleados_por_zona();
			
		});
	});

	$("#sec_vale_nuevo_local").change(function () {
		$("#sec_vale_nuevo_local option:selected").each(function () {
			sec_vale_nuevo_obtener_solicitante();
			sec_vale_nuevo_obtener_empleados_por_local();
		});
	});

	$("#frm-vale-descuento-btn-reset").click(function () {
		sec_vale_nuevo_resetear_form_vale_descuento();
	});
	


	$('#frm_nuevo_vale_descuento').submit(function (evt) {
		evt.preventDefault();
		var empresa = $('#sec_vale_nuevo_empresa').val();
		var zona = $('#sec_vale_nuevo_zona').val();
		var local = $('#sec_vale_nuevo_local').val();
		//var solicitante = $('#sec_vale_nuevo_solicitante').val();
		var empleado = $('#sec_vale_nuevo_empleado').val();
		var fecha_incidencia = $('#sec_vale_nuevo_fecha_vale').val();
		var motivo = $('#sec_vale_nuevo_motivo').val();
		var monto = $('#sec_vale_nuevo_monto').val();
		var observacion = $('#sec_vale_nuevo_observacion').val();
	
		if (empresa == 0) {
			alertify.error("Seleccione una empresa", 5);
			$("#sec_vale_nuevo_empresa").focus();
			$("#sec_vale_nuevo_empresa").select2("open");
			return false;
		}
		if (zona == 0) {
			alertify.error("Seleccione una zona", 5);
			$("#sec_vale_nuevo_zona").focus();
			$("#sec_vale_nuevo_zona").select2("open");
			return false;
		}
		if (empresa == "1" && local == 0) {
			alertify.error("Seleccione un local", 5);
			$("#sec_vale_nuevo_local").focus();
			$("#sec_vale_nuevo_local").select2("open");
			return false;
		}
		if (empleado == 0) {
			alertify.error("Seleccione un empleado", 5);
			$("#sec_vale_nuevo_empleado").focus();
			$("#sec_vale_nuevo_empleado").select2("open");
			return false;
		}
		if (fecha_incidencia.length == 0) {
			alertify.error("Seleccione una fecha de vale", 5);
			$("#sec_vale_nuevo_fecha_vale").focus();
			return false;
		}
		if (fecha_incidencia.length == 0) {
			alertify.error("Seleccione una fecha de vale", 5);
			$("#sec_vale_nuevo_fecha_vale").focus();
			return false;
		}
		
		var val_fecha_hoy = moment(fecha_hoy,'DD-MM-YYYY');
		var val_fecha_vale = moment(fecha_incidencia,'DD-MM-YYYY');
		var dias_dif = moment(val_fecha_hoy).diff(val_fecha_vale, 'days');
		if (dias_dif < 0) {
			alertify.error("Seleccione un fecha menor o igual a la de hoy", 5);
			$("#sec_vale_nuevo_fecha_vale").focus();
			return false;
		}

		if (motivo == 0) {
			alertify.error("Seleccione un motivo", 5);
			$("#sec_vale_nuevo_motivo").focus();
			$("#sec_vale_nuevo_motivo").select2("open");
			return false;
		}
		if (monto.length == 0) {
			alertify.error("Ingrese un monto", 5);
			$("#sec_vale_nuevo_monto").focus();
			return false;
		}

		monto = parseFloat(monto);

		if (empresa == "1" && monto < 0.10) { // operaciones at
			alertify.error("Ingrese un monto mayor a 0.10", 5);
			$("#sec_vale_nuevo_monto").focus();
			return false;
		}

		if (empresa == "6" && monto <= 0) { // business administration sac
			alertify.error("Ingrese un monto mayor a 0.00", 5);
			$("#sec_vale_nuevo_monto").focus();
			return false;
		}

		swal({
			title: "Esta seguro de registrar el Vale de Descuento?",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			confirmButtonText: "Si, estoy de acuerdo!",
			cancelButtonText: "No, cancelar",
			closeOnConfirm: true,
			closeOnCancel: true,
	
		},function (isConfirm) {
				
			if (isConfirm) {
				sec_vale_nuevo_validar_vale_descuento();
			} 
		});
		
	});

	
	
}

function sec_vale_nuevo_obtener_opciones(accion, select) {
	$.ajax({
		url: "/vales/controllers/DataController.php",
		type: "POST",
		data: { accion: accion }, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			console.log(respuesta);
			$(select).find("option").remove().end();
			$(select).append('<option value="0">- Seleccione -</option>');
			if (respuesta.status == 200) {
				var result = respuesta.result;
				$(result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$(select).append(opcion);
				});
				  
			}
			
		},
		error: function () {},
	});
}

function sec_vale_nuevo_obtener_zonas() {

	var empresa_id = $('#sec_vale_nuevo_empresa').val();
	if (empresa_id == "0") {
		$("[name='sec_vale_nuevo_zona']").find("option").remove().end();
		$("[name='sec_vale_nuevo_zona']").append('<option value="0">- Seleccione -</option>');
		alertify.error("Seleccione una empresa", 5);
		return false;
	}
	var data = {
		empresa_id : empresa_id,
		accion : 'listar_zonas_por_empresa'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var zona_selected = "";
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$("[name='sec_vale_nuevo_zona']").find("option").remove().end();
				$("[name='sec_vale_nuevo_zona']").append('<option value="0">- Seleccione -</option>');
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("[name='sec_vale_nuevo_zona']").append(opcion);
					zona_selected = e.id;
				});

				if (empresa_id == "6" && zona_selected != "") {
					$('#sec_vale_nuevo_zona').val(zona_selected).trigger('change.select2');
					sec_vale_nuevo_obtener_empleados_por_zona();
				}
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_vale_nuevo_obtener_locales_por_zona() {

	var zona_id = $('#sec_vale_nuevo_zona').val();
	if (zona_id == "0") {
		$("[name='sec_vale_nuevo_local']").find("option").remove().end();
		$("[name='sec_vale_nuevo_local']").append('<option value="0">- Seleccione -</option>');

		$("[name='sec_vale_nuevo_empleado']").find("option").remove().end();
		$("[name='sec_vale_nuevo_empleado']").append('<option value="0">- Seleccione -</option>');
		alertify.error("Seleccione una zona", 5);
		return false;
	}
	var data = {
		zona_id : zona_id,
		accion : 'listar_locales_por_zona'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$("[name='sec_vale_nuevo_local']").find("option").remove().end();
				$("[name='sec_vale_nuevo_local']").append('<option value="0">- Seleccione -</option>');
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("[name='sec_vale_nuevo_local']").append(opcion);
				});
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_vale_nuevo_obtener_solicitante() {

	var data = {
		accion : 'obtener_solicitante'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$("[name='sec_vale_nuevo_solicitante']").find("option").remove().end();
				//$("[name='sec_vale_nuevo_solicitante']").append('<option value="0">- Seleccione -</option>');
				//$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + respuesta.result.id + "'>" + respuesta.result.nombre + "</option>");
					$("[name='sec_vale_nuevo_solicitante']").append(opcion);
				//});
				$("[name='sec_vale_nuevo_solicitante']").prop("readonly", true);
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_vale_nuevo_obtener_motivos() {

	var empresa_id = $('#sec_vale_nuevo_empresa').val();
	if (empresa_id == "0") {
		$("[name='sec_vale_nuevo_motivo']").find("option").remove().end();
		$("[name='sec_vale_nuevo_motivo']").append('<option value="0">- Seleccione -</option>');
		return false;
	}

	var data = {
		empresa_id: empresa_id,
		accion : 'listar_motivos_activos'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$("[name='sec_vale_nuevo_motivo']").find("option").remove().end();
				$("[name='sec_vale_nuevo_motivo']").append('<option value="0">- Seleccione -</option>');
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("[name='sec_vale_nuevo_motivo']").append(opcion);
				});
				
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_vale_nuevo_obtener_empleados_por_local() {

	var local_id = $('#sec_vale_nuevo_local').val();
	var zona_id = $('#sec_vale_nuevo_zona').val();
	var empresa_id = $('#sec_vale_nuevo_empresa').val();
	if (local_id == "0") {
		$("[name='sec_vale_nuevo_empleado']").find("option").remove().end();
		$("[name='sec_vale_nuevo_empleado']").append('<option value="0">- Seleccione -</option>');
		alertify.error("Seleccione un local", 5);
		return false;
	}
	var data = {
		local_id : local_id,
		zona_id : zona_id,
		empresa_id : empresa_id,
		accion : 'listar_empleados_por_local'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$("[name='sec_vale_nuevo_empleado']").find("option").remove().end();
				$("[name='sec_vale_nuevo_empleado']").append('<option value="0">- Seleccione -</option>');
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("[name='sec_vale_nuevo_empleado']").append(opcion);
				});
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}


function sec_vale_nuevo_obtener_empleados_por_zona() {

	var local_id = $('#sec_vale_nuevo_local').val();
	var zona_id = $('#sec_vale_nuevo_zona').val();
	var empresa_id = $('#sec_vale_nuevo_empresa').val();
	if (zona_id == "0") {
		$("[name='sec_vale_nuevo_empleado']").find("option").remove().end();
		$("[name='sec_vale_nuevo_empleado']").append('<option value="0">- Seleccione -</option>');
		alertify.error("Seleccione un local", 5);
		return false;
	}
	var data = {
		local_id : local_id,
		zona_id : zona_id,
		empresa_id : empresa_id,
		accion : 'listar_empleados_por_zona'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$("[name='sec_vale_nuevo_empleado']").find("option").remove().end();
				$("[name='sec_vale_nuevo_empleado']").append('<option value="0">- Seleccione -</option>');
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("[name='sec_vale_nuevo_empleado']").append(opcion);
				});
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_vale_nuevo_validar_vale_descuento() {

	var empresa = $('#sec_vale_nuevo_empresa').val();
	var zona = $('#sec_vale_nuevo_zona').val();
	var local = $('#sec_vale_nuevo_local').val();
	var solicitante = $('#sec_vale_nuevo_solicitante').val();
	var empleado = $('#sec_vale_nuevo_empleado').val();
	var fecha_incidencia = $('#sec_vale_nuevo_fecha_vale').val();
	var motivo = $('#sec_vale_nuevo_motivo').val();
	var monto = $('#sec_vale_nuevo_monto').val();
	var observacion = $('#sec_vale_nuevo_observacion').val();

	var data = {
		empresa : empresa,
		zona : zona,
		local : local,
		solicitante : solicitante,
		empleado : empleado,
		fecha_incidencia : fecha_incidencia,
		motivo : motivo,
		monto : monto,
		observacion : observacion,
		accion : 'validar_vale'
	};

	$.ajax({
		url: "vales/controllers/ValeController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				sec_vale_nuevo_guardar_vale_descuento();
			}else if(respuesta.status == 404){
				$("#sec_vale_nuevo_monto").focus();
				alertify.error(respuesta.message, 5);
				return false;
			}else{
				swal({
					title: respuesta.message,
					type: "warning",
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					confirmButtonText: "Si, estoy de acuerdo!",
					cancelButtonText: "No, cancelar",
					closeOnConfirm: true,
					closeOnCancel: true,
			
				},function (isConfirm) {
						
					if (isConfirm) {
						sec_vale_nuevo_guardar_vale_descuento();
					} 
				});
			}
			return false;
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_vale_nuevo_guardar_vale_descuento() {

	var empresa = $('#sec_vale_nuevo_empresa').val();
	var zona = $('#sec_vale_nuevo_zona').val();
	var local = $('#sec_vale_nuevo_local').val();
	var solicitante = $('#sec_vale_nuevo_solicitante').val();
	var empleado = $('#sec_vale_nuevo_empleado').val();
	var fecha_incidencia = $('#sec_vale_nuevo_fecha_vale').val();
	var motivo = $('#sec_vale_nuevo_motivo').val();
	var monto = $('#sec_vale_nuevo_monto').val();
	var observacion = $('#sec_vale_nuevo_observacion').val();

	var data = {
		empresa : empresa,
		zona : zona,
		local : local,
		solicitante : solicitante,
		empleado : empleado,
		fecha_incidencia : fecha_incidencia,
		motivo : motivo,
		monto : monto,
		observacion : observacion,
		accion : 'registrar_vale'
	};

	auditoria_send({ proceso: "registrar_vale", data: data });

	$.ajax({
		url: "vales/controllers/ValeController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			auditoria_send({ proceso: "registrar_vale", data: respuesta });
			if (respuesta.status == 200) {
				alertify.success(respuesta.message, 10);
				sec_vale_solicitud_listar_vales();
				sec_vale_nuevo_resetear_form_vale_descuento();
			}else{
				alertify.error(respuesta.message, 10);
			}
			return false;
		},
		error: function (error) {
			console.log(error);
		},
	});
}


function sec_vale_nuevo_resetear_form_vale_descuento() {

	$("[name='sec_vale_nuevo_zona']").find("option").remove().end();
	$("[name='sec_vale_nuevo_zona']").append('<option value="0">- Seleccione -</option>');

	$("[name='sec_vale_nuevo_local']").find("option").remove().end();
	$("[name='sec_vale_nuevo_local']").append('<option value="0">- Seleccione -</option>');

	$("[name='sec_vale_nuevo_empleado']").find("option").remove().end();
	$("[name='sec_vale_nuevo_empleado']").append('<option value="0">- Seleccione -</option>');

	$("[name='sec_vale_nuevo_motivo']").find("option").remove().end();
	$("[name='sec_vale_nuevo_motivo']").append('<option value="0">- Seleccione -</option>');

	$('#sec_vale_nuevo_empresa').val('0').trigger('change.select2');
	$('#sec_vale_nuevo_zona').val('0').trigger('change.select2');
	$('#sec_vale_nuevo_local').val('0').trigger('change.select2');
	$('#sec_vale_nuevo_solicitante').val('0').trigger('change.select2');
	$('#sec_vale_nuevo_empleado').val('0').trigger('change.select2');
	$('#sec_vale_nuevo_motivo').val('0').trigger('change.select2');
	//$('#sec_vale_nuevo_fecha_vale').val();
	$('#sec_vale_nuevo_monto').val('');
	$('#sec_vale_nuevo_observacion').val('');

	

}




