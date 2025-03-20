function sec_reportes_contratos() {
	$(".select2").select2({ width: "100%" });
	$(".sec_rep_contrato_datepicker").datepicker({
		dateFormat: "dd-mm-yy",
		changeMonth: true,
		changeYear: true,
	}).on("change", function (ev) {
		$(this).datepicker("hide");
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
	});

	sec_reporte_contrato_obtener_opciones("obtener_tipos_de_contrato", $("[name='sec_rep_contrato_tipo_contrato']"));
	sec_reporte_contrato_obtener_opciones("obtener_areas", $("[name='sec_rep_contrato_area']"));
	sec_reporte_contrato_obtener_opciones("obtener_estados", $("[name='sec_rep_contrato_estado']"));
	sec_reporte_contrato_obtener_opciones("obtener_estados_solicitud", $("[name='sec_rep_contrato_estado_solicitud']"));
	sec_reporte_contrato_obtener_opciones("obtener_estados_aprobacion", $("[name='sec_rep_contrato_estado_aprobacion']"));

	setTimeout(() => {
		sec_reporte_contratos();
	}, 500);
}

function sec_reporte_contrato_obtener_opciones(accion, select) {
	$.ajax({
		url: "/sys/get_reportes_contratos.php",
		type: "POST",
		data: { accion: accion }, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			console.log(respuesta);
			$(select).find("option").remove().end();
			$(select).append('<option value="0">- Todos -</option>');
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

function sec_reporte_contratos() {
	var tipo_contrato = $('#sec_rep_contrato_tipo_contrato').val();
	var area = $('#sec_rep_contrato_area').val();
	var desde = $('#sec_rep_contrato_desde').val();
	var hasta = $('#sec_rep_contrato_hasta').val();
	var estado = $('#sec_rep_contrato_estado').val();
	var estado_solicitud = $('#sec_rep_contrato_estado_solicitud').val();
	var estado_aprobacion = $('#sec_rep_contrato_estado_aprobacion').val();
	
	var data = {
		tipo_contrato : tipo_contrato,
		area : area,
		desde : desde,
		hasta : hasta,
		estado : estado,
		estado_solicitud : estado_solicitud,
		estado_aprobacion : estado_aprobacion,
		accion : 'obtener_reporte'
	};

	$.ajax({
		url: "sys/get_reportes_contratos.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				sec_reporte_contrato_render_table(respuesta.result);
			}
		},
		error: function (error) {
			console.log(error);
		},
	});

}

function sec_reporte_contrato_render_table(data = []) {
	$("#sec_reporte_contrato_table").dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'desc']],
		pageLength: 25,
		columns: [
		  { data: "tipo_contrato", className: "text-center" },
		  { data: "codigo", className: "text-left" }, 
		  { data: "area", className: "text-left" },
		  { data: "solicitante", className: "text-left" },
		  { data: "empresa_suscribe", className: "text-left" },
		  { data: "proveedor", className: "text-left" },
		  {
			"data": 'detalle_servicio_resumen',
			render: function (data, type, row) {
				return (row.detalle_servicio_resumen.length > 50) ? row.detalle_servicio_resumen.slice(0, 50) + '&hellip;' : row.detalle_servicio_resumen;
			}
		  },
		  { data: "detalle_servicio", className: "text-left hidden" },
		  { data: "fecha_solicitud", className: "text-center" },
		  { data: "estado", className: "text-center" },
		  { data: "estado_solicitud", className: "text-center" },
		  { data: "estado_aprobacion", className: "text-center" },
		  { data: "fecha_inicio", className: "text-center" },
		  { data: "fecha_final", className: "text-center" },
		  { data: "fecha_suscripcion", className: "text-center" },
		  { data: "dias_habiles", className: "text-center" },
		],
		language: {
		  decimal: "",
		  emptyTable: "Tabla vacia",
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
		  buttons: {
			pageLength: {
				_: "Mostrar %d Resultados",
				'-1': "Tout afficher"
			}
		  }
		  
		},
		scrollY: true,
		scrollX: true,
		dom: 'Bfrtip',
		buttons: [
            'pageLength',
			{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [0,1,2,3,4,5,7,8,9,10,11,12,13,14,15]
                },
				title: 'Reporte de Contratos'
            },
        ],
	  })
	  .DataTable();



	 
}