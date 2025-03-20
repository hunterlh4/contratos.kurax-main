

function sec_marketing_nuevo() {
	$(".select2").select2({ width: "100%" });

	sec_mak_nuevo_obtener_opciones("obtener_areas","[name='sec_mkt_nuevo_area_id']");
	sec_mak_nuevo_obtener_opciones("obtener_productos","[name='sec_mkt_nuevo_producto_id']");
	sec_mak_nuevo_obtener_opciones("obtener_tipo_solicitud","[name='sec_mkt_nuevo_tipo_solicitud_id']");
	sec_mak_nuevo_obtener_opciones("obtener_tipo_requerimiento_estrategico","[name='sec_mkt_nuevo_req_estrategico_1']");
	sec_mak_nuevo_obtener_opciones("obtener_tipo_requerimiento_estrategico","[name='sec_mkt_nuevo_req_estrategico_2']");
	sec_mak_nuevo_obtener_opciones("obtener_tipo_requerimiento_estrategico","[name='sec_mkt_nuevo_req_estrategico_3']");
	sec_mak_nuevo_obtener_opciones("obtener_tipo_requerimiento_estrategico","[name='sec_mkt_nuevo_req_estrategico_4']");
	sec_mak_nuevo_obtener_opciones("obtener_tipo_requerimiento_estrategico","[name='sec_mkt_nuevo_req_estrategico_5']");
	sec_mak_nuevo_obtener_opciones("obtener_tipo_requerimiento_estrategico","[name='sec_mkt_nuevo_req_estrategico_6']");
	sec_mak_nuevo_obtener_opciones("obtener_tipo_requerimiento_estrategico","[name='sec_mkt_nuevo_req_estrategico_7']");
	sec_mak_nuevo_obtener_opciones("obtener_tipo_requerimiento_estrategico","[name='sec_mkt_nuevo_req_estrategico_8']");


	setTimeout(function () {
		$("#sec_mkt_nuevo_area_id").select2("open");
	}, 800);

}


function sec_mak_nuevo_obtener_opciones(accion,select){
	$.ajax({
	   url: "/sys/get_marketing_nuevo.php",
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


// INICIO GUARDAR SOLICITUD DE REQUERIMIENTO
$("#form_marketing_requerimiento_nuevo").submit(function (e) {
	e.preventDefault();

	var area_id = $("#sec_mkt_nuevo_area_id").val();
	var producto_id = $("#sec_mkt_nuevo_producto_id").val();
	var tipo_solicitud_id = $("#sec_mkt_nuevo_tipo_solicitud_id").val();
	var objetivo = $("#sec_mkt_nuevo_objetivo").val();
	var bullet_1 = $("#sec_mkt_nuevo_bullet_1").val();
	var bullet_2 = $("#sec_mkt_nuevo_bullet_2").val();
	var bullet_3 = $("#sec_mkt_nuevo_bullet_3").val();
	var bullet_4 = $("#sec_mkt_nuevo_bullet_4").val();
	var bullet_5 = $("#sec_mkt_nuevo_bullet_5").val();
	var req_estrategico_1 = $("#sec_mkt_nuevo_req_estrategico_1").val();
	var req_estrategico_2 = $("#sec_mkt_nuevo_req_estrategico_2").val();
	var req_estrategico_3 = $("#sec_mkt_nuevo_req_estrategico_3").val();
	var req_estrategico_4 = $("#sec_mkt_nuevo_req_estrategico_4").val();
	var req_estrategico_5 = $("#sec_mkt_nuevo_req_estrategico_5").val();
	var req_estrategico_6 = $("#sec_mkt_nuevo_req_estrategico_6").val();
	var req_estrategico_7 = $("#sec_mkt_nuevo_req_estrategico_7").val();
	var req_estrategico_8 = $("#sec_mkt_nuevo_req_estrategico_8").val();
	var sustento_req_estrategico = $("#sec_mkt_nuevo_sustento_req_estrategico").val();
	



	if (area_id == 0) {
		alertify.error("Seleccione una area.", 5);
		$("#sec_mkt_nuevo_area_id").focus();
		$("#sec_mkt_nuevo_area_id").select2("open");
		return false;
	}
	if (producto_id == 0) {
		alertify.error("Seleccione un producto", 5);
		$("#sec_mkt_nuevo_producto_id").focus();
		$("#sec_mkt_nuevo_producto_id").select2("open");
		return false;
	}
	if (tipo_solicitud_id == 0) {
		alertify.error("Seleccione un solicitud", 5);
		$("#sec_mkt_nuevo_tipo_solicitud_id").focus();
		$("#sec_mkt_nuevo_tipo_solicitud_id").select2("open");
		return false;
	}
	if (objetivo.trim().length == 0) {
		alertify.error("Ingrese un objetivo", 5);
		$("#sec_mkt_nuevo_objetivo").focus();
		return false;
	}
	if (bullet_1.trim().length == 0) {
		alertify.error("Ingrese la bullet 1", 5);
		$("#sec_mkt_nuevo_bullet_1").focus();
		return false;
	}
	if (bullet_2.trim().length == 0) {
		alertify.error("Ingrese la bullet 2", 5);
		$("#sec_mkt_nuevo_bullet_2").focus();
		return false;
	}
	if (bullet_3.trim().length == 0) {
		alertify.error("Ingrese la bullet 3", 5);
		$("#sec_mkt_nuevo_bullet_3").focus();
		return false;
	}
	if (req_estrategico_1 == 0) {
		alertify.error("Seleccione un requerimiento estrategico", 5);
		$("#sec_mkt_nuevo_req_estrategico_1").focus();
		$("#sec_mkt_nuevo_req_estrategico_1").select2("open");
		return false;
	}
	if (sustento_req_estrategico.trim().length == 0) {
		alertify.error("Ingrese un sustento del requerimiento estrategico", 5);
		$("#sec_mkt_nuevo_sustento_req_estrategico").focus();
		return false;
	}

	var dataform = {
		"accion" : "guardar_requerimiento_marketing",
		"area_id" : area_id,
		"producto_id" : producto_id,
		"tipo_solicitud_id" : tipo_solicitud_id,
		"objetivo" : objetivo,
		"bullet_1" : bullet_1,
		"bullet_2" : bullet_2,
		"bullet_3" : bullet_3,
		"bullet_4" : bullet_4,
		"bullet_5" : bullet_5,
		"req_estrategico_1" : req_estrategico_1,
		"req_estrategico_2" : req_estrategico_2,
		"req_estrategico_3" : req_estrategico_3,
		"req_estrategico_4" : req_estrategico_4,
		"req_estrategico_5" : req_estrategico_5,
		"req_estrategico_6" : req_estrategico_6,
		"req_estrategico_7" : req_estrategico_7,
		"req_estrategico_8" : req_estrategico_8,
		"sustento_req_estrategico" : sustento_req_estrategico,
	}

	auditoria_send({ proceso: "guardar_requerimiento_marketing", data: dataform });

	$.ajax({
		url: "sys/set_marketing_nuevo.php",
		type: "POST",
		data: dataform,
		// cache: false,
		// contentType: false,
		// processData: false,
		beforeSend: function (xhr) {
			loading(true);
		},
		success: function (data) {
			var respuesta = JSON.parse(data);

			auditoria_send({ respuesta: "guardar_requerimiento_marketing", data: respuesta });

		
			if (parseInt(respuesta.status) == 200) {
				swal({
					title: respuesta.message,
					text: "",
					html: true,
					type: "success",
					timer: 10000,
					closeOnConfirm: false,
					showCancelButton: false,
				},
					function (isConfirm) {
						window.location.href = "?sec_id=marketing&sub_sec_id=solicitud";
					}
				);
			}else{
				swal({
					title: respuesta.message,
					text: "",
					html: true,
					type: "error",
					timer: 10000,
					closeOnConfirm: false,
					showCancelButton: false,
				},
					function (isConfirm) {
						
					}
				);
			}
		},
		complete: function () {
			loading(false);
		},
	});
});
// FIN GUARDAR SOLICITUD DE REQUERIMIENTO

