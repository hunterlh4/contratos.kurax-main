var select_items_vales_a_aprobar = [];
function sec_vale_control_interno() {

	$(".select2").select2({ width: "100%", placeholder: "- Seleccione una opción -", });
	$(".sec_vale_control_int_datepicker").datepicker({
		dateFormat: "dd-mm-yy",
		changeMonth: true,
		changeYear: true,
	}).on("change", function (ev) {
		$(this).datepicker("hide");
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
	});

	sec_vale_control_int_obtener_opciones("listar_empresas_por_usuario", $("#sec_vale_control_int_empresa"));
	sec_vale_control_int_obtener_estados();

	setTimeout(() => {
		sec_vale_control_int_buscar_vale_descuento();
	  }, 2000);

	$("#sec_vale_control_int_empresa").change(function () {
		sec_vale_control_int_obtener_zonas();
	});

	$("#frm-vale-descuento-btn-reset").click(function () {
		sec_vale_control_int_resetear_form_vale_descuento();
	});
	
	$('#frm_control_interno_vale_descuento').submit(function (evt) {
		evt.preventDefault();

		var empresa = $('#sec_vale_control_int_empresa').val();
		var zona = $('#sec_vale_control_int_zona').val();
		var empleado = $('#sec_vale_control_int_empleado').val();
		var dni = $('#sec_vale_control_int_dni').val();
		var fecha_desde_vale = $('#sec_vale_control_int_fecha_desde_vale').val();
		var fecha_hasta_vale = $('#sec_vale_control_int_fecha_hasta_vale').val();
		var estado = $('#sec_vale_control_int_estado').val();

		if (empresa == null) {
			alertify.error("Seleccione al menos una empresa", 5);
			$("#sec_vale_control_int_empresa").focus();
			$("#sec_vale_control_int_empresa").select2("open");
			return false;
		}
		if (zona == null) {
			alertify.error("Seleccione al menos una zona", 5);
			$("#sec_vale_control_int_zona").focus();
			$("#sec_vale_control_int_zona").select2("open");
			return false;
		}
		if (estado == null) {
			alertify.error("Seleccione un estado", 5);
			$("#sec_vale_control_int_estado").focus();
			$("#sec_vale_control_int_estado").select2("open");
			return false;
		}
		if (fecha_desde_vale.length == 0) {
			alertify.error("Seleccione uan fecha", 5);
			$("#sec_vale_sinc_fecha_desde_vale").focus();
			$("#sec_vale_sinc_fecha_desde_vale").select2("open");
			return false;
		}
		if (fecha_hasta_vale.length == 0) {
			alertify.error("Seleccione uan fecha", 5);
			$("#sec_vale_sinc_fecha_hasta_vale").focus();
			$("#sec_vale_sinc_fecha_hasta_vale").select2("open");
			return false;
		}

		sec_vale_control_int_buscar_vale_descuento();
	});

	$("#button-aprobar-selected").click(function () {
		sec_vale_control_int_validar_vales_seleccionados();
	});

	
}

function sec_vale_control_int_obtener_opciones(accion, select) {
	$.ajax({
		url: "/vales/controllers/DataController.php",
		type: "POST",
		data: { accion: accion }, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			
			$(select).find("option").remove().end();
			if (respuesta.status == 200) {
				var result = respuesta.result;
				var values = [];
				$(result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$(select).append(opcion);
					values.push(e.id);
				});
				$(select).val(values).trigger('change.select2');
				$(select).trigger('change');
				  
			}
			
		},
		error: function () {},
	});
}

function sec_vale_control_int_obtener_zonas() {

	var empresa_id = $('#sec_vale_control_int_empresa').val();
	if (empresa_id == null) {
		console.log("null")
		$("#sec_vale_control_int_zona").find("option").remove().end();
		return false;
	}
	var data = {
		empresa_id : empresa_id,
		accion : 'listar_zonas_por_empresa_multiple'
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
				$("#sec_vale_control_int_zona").find("option").remove().end();
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("#sec_vale_control_int_zona").append(opcion);
				});
				$("#sec_vale_control_int_zona").val(respuesta.value).trigger('change.select2');

			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_vale_control_int_obtener_estados() {

	var data = {
		accion : 'listar_estados_control_interno'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			console.log(respuesta)
			if (respuesta.status == 200) {
				$("#sec_vale_control_int_estado").find("option").remove().end();
				$(respuesta.result.data).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("#sec_vale_control_int_estado").append(opcion);
				});
				$("#sec_vale_control_int_estado").val(respuesta.result.value).trigger('change.select2');

			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_vale_control_int_buscar_vale_descuento() {

	var empresa = $('#sec_vale_control_int_empresa').val();
	var zona = $('#sec_vale_control_int_zona').val();
	var empleado = $('#sec_vale_control_int_empleado').val();
	var dni = $('#sec_vale_control_int_dni').val();
	var fecha_desde_vale = $('#sec_vale_control_int_fecha_desde_vale').val();
	var fecha_hasta_vale = $('#sec_vale_control_int_fecha_hasta_vale').val();
	var estado = $('#sec_vale_control_int_estado').val();

	var data = {
		empresa : empresa,
		zona : zona,
		dni : dni,
		empleado : empleado,
		fecha_desde_vale : fecha_desde_vale,
		fecha_hasta_vale : fecha_hasta_vale,
		estado : estado,
		accion : 'listar_vales_control_interno'
	};

	$.ajax({
		url: "vales/controllers/ValeController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				fnc_render_table_control_interno_vales(respuesta.result);
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



function sec_vale_control_int_resetear_form_vale_descuento() {

	$('#sec_vale_control_int_empresa').val('0').trigger('change.select2');
	$('#sec_vale_control_int_zona').val('0').trigger('change.select2');
	$('#sec_vale_control_int_local').val('0').trigger('change.select2');
	$('#sec_vale_control_int_solicitante').val('0').trigger('change.select2');
	$('#sec_vale_control_int_empleado').val('0').trigger('change.select2');
	$('#sec_vale_control_int_motivo').val('0').trigger('change.select2');
	//$('#sec_vale_control_int_fecha_incidencia').val();
	$('#sec_vale_control_int_monto').val('');
	$('#sec_vale_control_int_observacion').val('');

}

function fnc_render_table_control_interno_vales(data = []) {
	
	var table = $("#tbl_vale_control_interno")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'desc']],
		pageLength: 25,
		columns: [
		  { data: "id", className: "text-center" },
		  { data: "nro_vale", className: "text-center" },
		  { data: "empresa", className: "text-left" }, 
		  { data: "zona", className: "text-left" },
		  { data: "local", className: "text-left" },
		  { data: "empleado", className: "text-left" },
		  { data: "dni_empleado", className: "text-left" },
		  { data: "motivo", className: "text-left" },
		  { data: "fecha_incidencia", className: "text-center" },
		  { data: "monto", className: "text-right" },
		  { data: "nro_vale_totalizado", className: "text-center" },
		  { data: "observacion", className: "text-left" },
		  { data: "archivo", className: "text-center" },
		  { data: "estado", className: "text-center" },
		  { data: "acciones", className: "text-center" },
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
			/*
			{
				text: "Selecionar Todos",
				action: function () {
				table.rows().select();
				},
			},
			{
				text: "Seleccionar Ninguno",
				action: function () {
				table.rows().deselect();
				},
			}
			*/

			,{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [1,2,3,4,5,6,7,8,9,10,11,13],
                },
				title: 'Vales de Descuento - Control Interno'
            },
			
        ],
		columnDefs: [
            {
                targets: [0,11],
                visible: false
            }
        ]
	  })
	  .DataTable();

	  /*
	  table.rows().deselect();
	  select_items_vales_a_aprobar = [];

	  table.on("select", function (e, dt, type, indexes) {
			var rowData = table.rows(indexes).data().toArray();
			rowData.forEach((element) => {
				if (!select_items_vales_a_aprobar.includes(element.id)) {
					select_items_vales_a_aprobar.push(element.id);
				}
			});
			if (select_items_vales_a_aprobar.length > 0) {
				$('#button-aprobar-selected').show();
			}else{
				$('#button-aprobar-selected').hide();
			}
		})
		.on("deselect", function (e, dt, type, indexes) {
			var rowData = table.rows(indexes).data().toArray();
			rowData.forEach((element) => {
				const index = select_items_vales_a_aprobar
				.map((item) => item)
				.indexOf(element.id);
				select_items_vales_a_aprobar.splice(index, 1);
			});
			if (select_items_vales_a_aprobar.length > 0) {
				$('#button-aprobar-selected').show();
			}else{
				$('#button-aprobar-selected').hide();
			}
		
		});
	
	  */
	  $('.btn-vale-control-aprobar').click(function () {
		var vale_id = $(this).attr('data-id');
		var nro_vale = $(this).attr('data-nro-vale');
		var data_confirm = $(this).attr('data-confirm');
		var data = {
			id : vale_id,
			vale_estado_id:2,
			accion: 'aprobar_rechazar_estado_vale'
		}
		swal({
			title: data_confirm,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			confirmButtonText: "Si, estoy de acuerdo!",
			cancelButtonText: "No, cancelar",
			closeOnConfirm: true,
			closeOnCancel: true,
	
		},function (isConfirm) {
			if (isConfirm) {
				sec_vale_control_int_modificar_estado_vale_descuento(data);
			}
		});
		return false;
	});

	$('.btn-vale-control-rechazar').click(function () {
		var vale_id = $(this).attr('data-id');
		var nro_vale = $(this).attr('data-nro-vale');
		var data_confirm = $(this).attr('data-confirm');

		var data = {
			id : vale_id,
			vale_estado_id:3,
			accion: 'aprobar_rechazar_estado_vale'
		}
		swal({
			title: data_confirm,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			confirmButtonText: "Si, estoy de acuerdo!",
			cancelButtonText: "No, cancelar",
			closeOnConfirm: true,
			closeOnCancel: true,
	
		},function (isConfirm) {
			if (isConfirm) {
				sec_vale_control_int_modificar_estado_vale_descuento(data);
			}
		});
		return false;
	});

	
}

function sec_vale_control_int_validar_vales_seleccionados() {
	
	if (select_items_vales_a_aprobar.length == 0) {
		alertify.error('Seleccione una o mas vales de descuento', 10);
		return false;
	}

	swal({
		title: 'Esta seguro de aprobar los vales de descuentos seleccionados?',
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		confirmButtonText: "Si, estoy de acuerdo!",
		cancelButtonText: "No, cancelar",
		closeOnConfirm: true,
		closeOnCancel: true,

	},function (isConfirm) {
		if (isConfirm) {
			sec_vale_control_int_aprobar_vales_seleccionados();
		}
	});
}

function sec_vale_control_int_aprobar_vales_seleccionados() {

	var data = {
		vales_descuento : select_items_vales_a_aprobar,
		accion : 'aprobar_vales_descuento'
	}
	auditoria_send({ proceso: "aprobar_vales_descuento", data: data });
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
				auditoria_send({ proceso: "aprobar_vales_descuento", data: respuesta });

				var table = $('#tbl_vale_control_interno').DataTable();
				select_items_vales_a_aprobar.forEach((element) => {
					var tr = $('#tdoby-td-button-rechazar-'+element).parent().parent();
					table.row(tr).remove().draw();
				})
				table.rows().deselect();
				select_items_vales_a_aprobar = [];

				alertify.success(respuesta.message, 5);
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




function sec_vale_control_int_modificar_estado_vale_descuento(data) {
	auditoria_send({ proceso: "modificar_estado_vale_descuento", data: data });
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
			auditoria_send({ proceso: "modificar_estado_vale_descuento", data: respuesta });
			if (respuesta.status == 200) {
				if (respuesta.result.vale_estado_id == 2) {
					alertify.success(respuesta.message, 5);
					var table = $('#tbl_vale_control_interno').DataTable();
					var tr = $('#tdoby-td-button-rechazar-'+respuesta.result.id).parent().parent();
					table.row(tr).remove().draw();

				}else if(respuesta.result.vale_estado_id == 3){
					alertify.warning(respuesta.message, 5);
					var table = $('#tbl_vale_control_interno').DataTable();
					var tr = $('#tdoby-td-button-rechazar-'+respuesta.result.id).parent().parent();
					table.row(tr).remove().draw();
				}
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

function sec_vale_control_int_ver_documento_en_visor(ruta,tipodocumento)
{   

	$('#sec_vale_control_int_modal_vizualizacion_archivo').modal('show');
	var path = 'files_bucket/cajas/';
	var mi_ruta_temporal = '';
	var tipo_documento = tipodocumento.toLowerCase();

	$('#div-container-img').html('');
	$('#div-container-img-full-pantalla').html('');
	$('#div-container-pdf').html('');

	if (tipodocumento == 'jpg' || tipodocumento == 'png' || tipodocumento == 'jpeg') {
		//mi_ruta_temporal = path + 'test-img.jpg';
		mi_ruta_temporal = path + ruta;
		var html_img = '<img class="img-responsive" src="'+mi_ruta_temporal+'">';
		$('#div-container-img').html(html_img);

		var html_pantalla_completa = '<button onclick="sec_vale_control_int_ver_imagen_full_pantalla('+ "'"+ mi_ruta_temporal+"'"+ ')" type="button" class="btn btn-primary mt-2">Ver Imagen en Pantalla Completa</button>';
		$('#div-container-img-full-pantalla').html(html_pantalla_completa);
	}


	if (tipodocumento == 'pdf') {
		//mi_ruta_temporal = path + 'test.pdf';
		mi_ruta_temporal = path + ruta;
		var html_pdf = '<iframe src="'+mi_ruta_temporal+'" width="100%" height="500px"></iframe>';
		$('#div-container-pdf').html(html_pdf);
	}
}

function sec_vale_control_int_ver_imagen_full_pantalla(url_image) {
	var image = new Image();
	image.src = url_image;
	var viewer = new Viewer(image, {
		hidden: function () {
			viewer.destroy();
		},
	});
	viewer.show();
}