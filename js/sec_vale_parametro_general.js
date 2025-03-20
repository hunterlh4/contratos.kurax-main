function sec_vale_parametro_general() {
	sec_vale_param_gener_obtener();

	$('#frm_vale_param_general').submit(function (evt) {
		evt.preventDefault();
		
		var id = $('#sec_vale_param_gener_id').val();
		var valor = $('#sec_vale_param_gener_monto_maximo').val();


		if (valor.length == 0) {
			alertify.error("Ingrese un monto", 5);
			$("#sec_vale_param_gener_monto_maximo").focus();
			return false;
		}

		swal({
			title: 'Esta seguro de modificar el monto maximo de descuento?',
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			confirmButtonText: "Si, estoy de acuerdo!",
			cancelButtonText: "No, cancelar",
			closeOnConfirm: true,
			closeOnCancel: true,
	
		},function (isConfirm) {
			if (isConfirm) {
				sec_vale_param_gener_guardar();
			} 
		});
	});

	
}


function sec_vale_param_gener_guardar() {

	var id = $('#sec_vale_param_gener_id').val();
	var valor = $('#sec_vale_param_gener_monto_maximo').val();

	var data = {
		id : id,
		valor : valor,
		accion : 'guardar_parametro_general'
	};

	auditoria_send({ proceso: "guardar_parametro_general", data: data });

	$.ajax({
		url: "vales/controllers/ParametroGeneralController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			auditoria_send({ proceso: "guardar_parametro_general", data: respuesta });

			if (respuesta.status == 200) {
				alertify.success(respuesta.message, 10);
				$('#sec_vale_param_gener_monto_maximo').val(respuesta.result.valor);
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




function sec_vale_param_gener_obtener() {

	let data = {
		accion : "ObtenerParametroDeDescuento",
	}

	$.ajax({
		url: "vales/controllers/ParametroGeneralController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$('#sec_vale_param_gener_id').val(respuesta.result.id);
				$('#sec_vale_param_gener_monto_maximo').val(respuesta.result.valor);
			}else{
				alertify.error(respuesta.message, 5);
			}
			return false;
		},
		error: function (error) {
			console.log(error);
		},
	});
}
