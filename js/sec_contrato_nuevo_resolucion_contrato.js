function sec_contrato_nuevo_resolucion_contrato() {
	$(".select2").select2({ width: "100%" });
	
	$('.sec_contrato_nuevo_resolucion_datepicker')
	.datepicker({
		dateFormat:'dd-mm-yy',
		changeMonth: true,
		changeYear: true
	})
	.on("change", function(ev) {
		$(this).datepicker('hide');
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
	});
	sec_con_nuevo_resol_obtener_opciones("obtener_tipos_contrato","[name='sec_con_nuevo_resol_tipo_contrato_id']");
	sec_con_nuevo_resol_obtener_opciones("obtener_directores","[name='sec_con_nuevo_resol_aprobante_id']");
	sec_con_nuevo_resol_obtener_opciones("obtener_cargos","[name='sec_con_nuevo_resol_cargo_aprobante_id']");



	$("#sec_con_nuevo_resol_tipo_contrato_id").change(function () {
		$("#sec_con_nuevo_resol_tipo_contrato_id option:selected").each(function () {
		  var tipo_contrato_id = $(this).val();
		  if (tipo_contrato_id == 1 || tipo_contrato_id == 6) {
			$("#div_fecha_carta").show();
			$('#div_anexo_resolucion_contrato').removeClass("col-md-5");
			$('#div_anexo_resolucion_contrato').removeClass("col-lg-5");

			$('#div_anexo_resolucion_contrato').addClass("col-md-3");
			$('#div_anexo_resolucion_contrato').addClass("col-lg-3");
		  } else {
			$("#div_fecha_carta").hide();
			$('#div_anexo_resolucion_contrato').removeClass("col-md-3");
			$('#div_anexo_resolucion_contrato').removeClass("col-lg-3");

			$('#div_anexo_resolucion_contrato').addClass("col-md-5");
			$('#div_anexo_resolucion_contrato').addClass("col-lg-5");
		  }
		});
	  });
	


}

function sec_con_nuevo_resol_obtener_opciones(accion,select){
	$.ajax({
	   url: "/sys/get_contrato_nuevo_resolucion_contrato.php",
	   type: 'POST',
	   data:{accion:accion} ,//+data,
	   beforeSend: function () {
	   },
	   complete: function () {
	   },
	   success: function (datos) {//  alert(datat)
		   var respuesta = JSON.parse(datos);
		   $(select).find('option').remove().end();
		   $(select).append('<option value="0">- Seleccione -</option>');
		   $(respuesta.result).each(function(i,e){
			   opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
			   $(select).append(opcion);	
		   })
	   },
	   error: function () {
	   }
   });
}

function sec_con_nuevo_resol_obtener_contratos() {
	var tipo_contrato_id = $('#sec_con_nuevo_resol_tipo_contrato_id').val();
	if (tipo_contrato_id == 0) {
		$(select).find('option').remove().end();
		$(select).append('<option value="0">- Seleccione -</option>');
		return false;
	}
	let data = {
		accion:'obtener_contratos',
		tipo_contrato_id:tipo_contrato_id,
	};
	var select = "[name='sec_con_nuevo_resol_contrato_id']";
	$.ajax({
		url: "/sys/get_contrato_nuevo_resolucion_contrato.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
		},
		complete: function () {
		},
		success: function (datos) {//  alert(datat)
			var respuesta = JSON.parse(datos);
			$(select).find('option').remove().end();
			$(select).append('<option value="0">- Seleccione -</option>');
			$(respuesta.result).each(function(i,e){
				opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
				$(select).append(opcion);	
			})
		},
		error: function () {
		}
	});
}

$("#form_resolucion_contrato").submit(function (e) {
	e.preventDefault();

	var tipo_contrato_id = $('#sec_con_nuevo_resol_tipo_contrato_id').val();
	var contrato_id = $('#sec_con_nuevo_resol_contrato_id').val();
	var aprobante_id = $('#sec_con_nuevo_resol_aprobante_id').val();
	var cargo_aprobante_id = $('#sec_con_nuevo_resol_cargo_aprobante_id').val();
	var fecha_resolucion = $('#sec_con_nuevo_resol_fecha_resolucion').val();
	var fecha_carta = $('#sec_con_nuevo_resol_fecha_carta').val();
	var motivo = $('#sec_con_nuevo_resol_motivo').val();
	// var fecha_solicitud = $('#sec_con_nuevo_resol_fecha_solicitud').val();
	

	if(tipo_contrato_id.length == 0 || tipo_contrato_id == "0") {
		alertify.error('Seleccione un tipo de contrato',5);
		$('#sec_con_nuevo_resol_tipo_contrato_id').select2("open");
		return false;
	}
	if(contrato_id.length == 0 || contrato_id == "0") {
		alertify.error('Seleccione un contrato',5);
		$('#sec_con_nuevo_resol_contrato_id').select2("open");
		return false;
	}
	if(aprobante_id.length == 0 || aprobante_id == "0") {
		alertify.error('Seleccione un aprobante',5);
		$('#sec_con_nuevo_resol_aprobante_id').select2("open");
		return false;
	}
	if(cargo_aprobante_id.length == 0 || cargo_aprobante_id == "0") {
		alertify.error('Seleccione un cargo',5);
		$('#sec_con_nuevo_resol_cargo_aprobante_id').select2("open");
		return false;
	}
	if(fecha_carta.length == 0 && (parseInt(tipo_contrato_id) == 1 || parseInt(tipo_contrato_id) == 6) ) {
		alertify.error('Ingrese una fecha resolución',5);
		$('#sec_con_nuevo_resol_fecha_carta').focus();
		return false;
	}
	if(fecha_resolucion.length == 0) {
		alertify.error('Ingrese una fecha resolución',5);
		$('#sec_con_nuevo_resol_fecha_resolucion').focus();
		return false;
	}
	if(motivo.length == 0) {
		alertify.error('Ingrese un motivo',5);
		$('#sec_con_nuevo_resol_motivo').focus();
		return false;
	}

	var formData = new FormData($("#form_resolucion_contrato")[0]);
	formData.append("accion","guardar_resolucion_contrato");

	auditoria_send({ "proceso": "guardar_resolucion_contrato", "data": formData });
	$.ajax({
		url: "/sys/set_contrato_nuevo_resolucion_contrato.php",
		type: 'POST',
		data: formData,
		contentType: false,
		processData: false,
		cache: false,
		beforeSend: function( xhr ) {
			loading(true);
		},
		success: function(resp) { 
			var respuesta = JSON.parse(resp);
			// auditoria_send({ "respuesta": "guardar_resolucion_contrato", "data": respuesta });
			if (parseInt(respuesta.http_code) == 400) {
				swal({
					title: respuesta.message,
					text: "",
					html:true,
					type: 'warning',
					timer: 3000,
					closeOnConfirm: false,
					showCancelButton: false
				});
				return false;
			}
			swal({
				title: respuesta.message,
				text: "",
				html:true,
				type: respuesta.status == 200 ? 'success':'warning',
				timer: 3000,
				closeOnConfirm: false,
				showCancelButton: false
			});
			if (parseInt(respuesta.status) == 200) {
				setTimeout(function() {
					window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
					return false;
				}, 3000);
			}
		},
		complete: function(){
			loading(false);
		},
		error: function() {
			loading(false);
		}
	});

})


function sec_con_nuevo_resol_obtener_cargo_aprobante() {

	var aprobante_id = $('#sec_con_nuevo_resol_aprobante_id').val();

	let data = {
		accion:'obtener_cargo_aprobante',
		aprobante_id:aprobante_id,
	};

	$.ajax({
		url: "/sys/get_contrato_nuevo_resolucion_contrato.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
		},
		complete: function () {
		},
		success: function (datos) {//  alert(datat)
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
					$('#sec_con_nuevo_resol_cargo_aprobante_id').val(respuesta.result).trigger("change");
			}
			
		},
		error: function () {
		}
	});

	
}























