

function sec_marketing_solicitud() {
	$(".select2").select2({ width: "100%" });

	sec_mkt_req_obtener_opciones("obtener_areas","[name='sec_mkt_req_area_id']");
	sec_mkt_req_obtener_opciones("obtener_productos","[name='sec_mkt_req_producto_id']");
	sec_mkt_req_obtener_opciones("obtener_tipo_solicitud","[name='sec_mkt_req_tipo_solicitud_id']");
	sec_mkt_req_obtener_opciones("obtener_estados","[name='sec_mkt_req_estado']");
	
	$(".mkt_req_datepicker").datepicker({
		dateFormat: "dd-mm-yy",
		changeMonth: true,
		changeYear: true,
	})
	.on("change", function (ev) {
		$(this).datepicker("hide");
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
		// localStorage.setItem($(this).atrr("id"),)
	});

	setTimeout(function () {
		sec_mkt_req_listar_solicitudes();
	}, 800);

}


function sec_mkt_req_obtener_opciones(accion,select){
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
		   $(select).append('<option value="0">- Todos -</option>');
		   $(respuesta.result).each(function(i,e){
			   opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
			   $(select).append(opcion);	
		   })
	   },
	   error: function () {
	   }
   });
}

function sec_mkt_req_restablecer_parametros() {
	$('#sec_mkt_req_area_id').val('0').trigger("change");;
	$('#sec_mkt_req_producto_id').val('0').trigger("change");;
	$('#sec_mkt_req_tipo_solicitud_id').val('0').trigger("change");;
	$('#sec_mkt_req_estado').val('0').trigger("change");;
	$('#sec_mkt_req_fecha_inicio').val('');
	$('#sec_mkt_req_fecha_fin').val('');
	$('#currentPage').val('1');
	
	sec_mkt_req_listar_solicitudes();

	
}

function sec_mkt_req_cambiar_de_pagina(pagina) {
	$('#currentPage').val(pagina);
	sec_mkt_req_listar_solicitudes();
}

function sec_mkt_req_listar_solicitudes() {
	
	let currentPage = $('#currentPage').val();
	let area_id = $('#sec_mkt_req_area_id').val();
	let producto_id = $('#sec_mkt_req_producto_id').val();
	let tipo_solicitud_id = $('#sec_mkt_req_tipo_solicitud_id').val();
	let estado = $('#sec_mkt_req_estado').val();
	let fecha_inicio = $("#sec_mkt_req_fecha_inicio").val();
	let fecha_fin = $("#sec_mkt_req_fecha_fin").val();

	if (fecha_inicio.length > 0 || fecha_fin.length > 0) {
		if (fecha_inicio.length == 0) {
			alertify.error('Seleccione una fecha inicio',5);
			$("#sec_mkt_req_fecha_inicio").focus();
			return false;
		}
		if (fecha_fin.length == 0) {
			alertify.error('Seleccione una fecha fin',5);
			$("#sec_mkt_req_fecha_fin").focus();
			return false;
		}

		if (fecha_inicio > fecha_fin) {
			alertify.error('La fecha inicio debe ser menor o igual a la fecha final',5);
			return false;
		}
	}
	console.log(currentPage);
	currentPage = currentPage.length == 0 ? 1 : currentPage;
	
	let data = {
		action : "listar_solicitudes",
		page: currentPage,
		area_id: area_id,
		producto_id: producto_id,
		tipo_solicitud_id: tipo_solicitud_id,
		estado: estado,
		fecha_inicio: fecha_inicio,
		fecha_fin: fecha_fin,
	};

	$.ajax({
		url: "sys/set_marketing_solicitud.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			$('#block-resultado-tabla').html(resp);
		},
		error: function() {}
	});
}
