function sec_vale_solicitud() {
	sec_vale_solicitud_listar_vales();
}

function sec_vale_solicitud_listar_vales() {

	var data = {
		accion : 'listar_vales_por_usuario'
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
				fnc_render_table_solicitud_vales(respuesta.result);
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function fnc_render_table_solicitud_vales(data = []) {
	$("#tbl_vale_solicitud")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'desc']],
		pageLength: 25,
		columns: [
		  { data: "nro_vale", className: "text-center" },
		  { data: "empresa", className: "text-left" }, 
		  { data: "zona", className: "text-left" },
		  { data: "local", className: "text-left" },
		  { data: "empleado", className: "text-left" },
		  { data: "dni_empleado", className: "text-left" },
		  { data: "motivo", className: "text-left" },
		  { data: "fecha_incidencia", className: "text-center" },
		  { data: "monto", className: "text-right" },
		  { data: "observacion", className: "text-left" },
		  { data: "vale_estado", className: "text-center" },
		  { data: "archivo", className: "text-center" },
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
			{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,8,9,10]
                },
				title: 'Listado de Vales de Descuento'
            },
        ],
		columnDefs: [
            {
                targets: [9],
                visible: false
            }
        ]
	  })
	  .DataTable();



	 
}

function sec_vale_solicitud_modal_sincronizar_archivo(vale_id) {

	var data = {
		id:vale_id,
		accion : 'listar_archivos_adjuntos'
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
				$('#sec-sol-resultado-archivos').html(respuesta.result);
			}
		},
		error: function (error) {
			
		},
	});


	$('#sec_vale_solicitud_modal_sincronizacion_archivo').modal('show');

	
}

function sec_vale_solicitud_ver_documento_en_visor(ruta,tipodocumento)
{   

	$('#sec_vale_solicitud_modal_vizualizacion_archivo').modal('show');
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

		var html_pantalla_completa = '<button onclick="sec_vale_solicitud__ver_imagen_full_pantalla('+ "'"+ mi_ruta_temporal+"'"+ ')" type="button" class="btn btn-primary mt-2">Ver Imagen en Pantalla Completa</button>';
		$('#div-container-img-full-pantalla').html(html_pantalla_completa);
	}


	if (tipodocumento == 'pdf') {
		//mi_ruta_temporal = path + 'test.pdf';
		mi_ruta_temporal = path + ruta;
		var html_pdf = '<iframe src="'+mi_ruta_temporal+'" width="100%" height="500px"></iframe>';
		$('#div-container-pdf').html(html_pdf);
	}
}

function sec_vale_solicitud__ver_imagen_full_pantalla(url_image) {
	var image = new Image();
	image.src = url_image;
	var viewer = new Viewer(image, {
		hidden: function () {
			viewer.destroy();
		},
	});
	viewer.show();
}


function sec_vale_solicitud_sincronizar_archivo(archivo_id,vale_id)
{   

	var data = {
		id:vale_id,
		archivo_id:archivo_id,
		accion : 'sincronizar_archivo'
	};
	auditoria_send({ proceso: "sincronizar_archivo_vale_descuento", data: data });
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
			auditoria_send({ proceso: "sincronizar_archivo_vale_descuento", data: respuesta });
			if (respuesta.status == 200) {
				$('#btn-vale-file-'+respuesta.result.id).parent().html(respuesta.button);
				$('#sec_vale_solicitud_modal_sincronizacion_archivo').modal('hide');
				alertify.success(respuesta.message, 5);
			}
		},
		error: function (error) {
			
		},
	});
	
}


function sec_vale_solicitud_modal_subir_archivo(vale_id) {
	$('#modal_vale_solicitud_vale_id').val(vale_id);
	$('#sec_vale_solicitud_modal_nuevo_archivo').modal('show');
}


function sec_vale_solicitud_modal_guardar_archivo() {

	var dataForm = new FormData($("#frm_vale_solicitud_nuevo_archivo")[0]);


	var image = $('#modal_vale_solicitud_archivo').val();
	if (image.length == 0) {
		alertify.error("Seleccione un documento adjunto", 5);
		$("#modal_vale_solicitud_archivo").focus();
		return false;
	}
	dataForm.append("accion", "guardar_documento_adjunto");

	
	$.ajax({
		url: "vales/controllers/ValeController.php",
		type: "POST",
		data: dataForm,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function (xhr) {
			loading(true);
		},
		success: function (data) {
			var respuesta = JSON.parse(data);
			auditoria_send({ proceso: "guardar_documento_adjunto", data: respuesta });
			if (respuesta.status == 200) {
				$('#btn-vale-file-'+respuesta.result.id).parent().html(respuesta.button);
				$('#sec_vale_solicitud_modal_nuevo_archivo').modal('hide');

				var inputImage = document.getElementById("modal_vale_solicitud_archivo");
				inputImage.value = '';

				alertify.success(respuesta.message, 5);
				
	
			}
		
		},
		complete: function () {
			loading(false);
		},
	});
	
}
