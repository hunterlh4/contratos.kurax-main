function sec_vale_motivo() {
	sec_vale_motivo_reset();
	sec_vale_motivo_listar();
	sec_vale_motivo_obtener_empresas();

	$('#frm_vale_mant_motivo').submit(function (evt) {
		evt.preventDefault();
		var id = $('#sec_vale_motivo_id').val();
		var empresa = $('#sec_vale_motivo_empresa').val();
		var nombre = $('#sec_vale_motivo_nombre').val();

		var title = '';
		if (id.length == 0) {
			title = 'Esta seguro de registrar el Motivo?';
		}else{
			title = 'Esta seguro de modificar el Motivo?';
		}
		if (empresa.length == 0) {
			alertify.error("Selecicona una empresa", 5);
			$("#sec_vale_motivo_empresa").focus();
			return false;
		}
		if (nombre.length == 0) {
			alertify.error("Ingrese un nombre", 5);
			$("#sec_vale_motivo_nombre").focus();
			return false;
		}

		swal({
			title: title,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			confirmButtonText: "Si, estoy de acuerdo!",
			cancelButtonText: "No, cancelar",
			closeOnConfirm: true,
			closeOnCancel: true,
	
		},function (isConfirm) {
			if (isConfirm) {
				sec_vale_motivo_guardar();
			} 
		});
	});

	
}

function sec_vale_motivo_obtener_empresas() {

	
	var data = {
		accion : 'listar_empresas_activas'
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
				$('#sec_vale_motivo_empresa').find("option").remove().end();
				$('#sec_vale_motivo_empresa').append('<option value="0">- Seleccione -</option>');
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$('#sec_vale_motivo_empresa').append(opcion);
				});
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}


function sec_vale_motivo_listar() {

	var data = {
		accion : 'listar_motivos'
	};

	$.ajax({
		url: "vales/controllers/MotivoController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				fnc_render_table_motivos(respuesta.result);
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

function fnc_render_table_motivos(data = []) {
	$("#tbl_vale_motivos")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'asc']],
		columns: [
		  { data: "index", className: "text-center" },
		  { data: "empresa", className: "text-left" }, 
		  { data: "nombre", className: "text-left" }, 
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
		},
		scrollY: false,
		scrollX: false,
	  })
	  .DataTable();

	  $(".switch-vale-motivo").bootstrapToggle({
		on: "activo",
		off: "inactivo",
		onstyle: "success",
		offstyle: "danger",
		size: "mini",
	  });

	  $(".switch-vale-motivo")
	  .off()
	  .change(function (event) {
		var id_motivo = $(this).attr("data-id");
		sec_vale_motivo_modificar_estado(id_motivo);
	  });


	

	  $('#tbl_vale_motivos').on('draw.dt', function() {
		$(".switch-vale-motivo").bootstrapToggle({
			on: "activo",
			off: "inactivo",
			onstyle: "success",
			offstyle: "danger",
			size: "mini",
		  });
	
		  $(".switch-vale-motivo")
		  .off()
		  .change(function (event) {
			var id_motivo = $(this).attr("data-id");
			sec_vale_motivo_modificar_estado(id_motivo);
		  });
	
	});
  
}

function sec_vale_motivo_guardar() {

	var id = $('#sec_vale_motivo_id').val();
	var nombre = $('#sec_vale_motivo_nombre').val();
	var empresa = $('#sec_vale_motivo_empresa').val();
	var estado = $('#sec_vale_motivo_estado').val();

	var data = {
		id : id,
		empresa : empresa,
		nombre : nombre,
		estado : estado,
		accion : 'guardar_motivo'
	};

	auditoria_send({ proceso: "guardar_motivo_vale_descuento", data: data });

	$.ajax({
		url: "vales/controllers/MotivoController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			auditoria_send({ proceso: "guardar_motivo_vale_descuento", data: respuesta });
			if (respuesta.status == 200) {
				alertify.success(respuesta.message, 10);
				sec_vale_motivo_reset();
				sec_vale_motivo_listar();
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


function sec_vale_motivo_modificar_estado(id_motivo) {

	let data = {
		id_motivo : id_motivo,
		accion : "modificar_estado",
	}
	auditoria_send({ proceso: "modificar_estado_vale_descuento", data: data });
	$.ajax({
		url: "vales/controllers/MotivoController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			auditoria_send({ proceso: "modificar_estado_vale_descuento", data: respuesta });
			if (respuesta.status == 200) {
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

function sec_vale_motivo_reset() {
	$('#sec_vale_motivo_id').val('');
	$('#sec_vale_motivo_empresa').val('0').trigger('change.select2');
	$('#sec_vale_motivo_nombre').val('');
	$('#sec_vale_motivo_estado').val('1');
	$('#btn-motivo-registrar').show();
	$('#btn-motivo-modificar').hide();
}

function sec_vale_motivo_obtener_por_id(id) {

	let data = {
		id : id,
		accion : "obtener_motivo_por_id",
	}
	$.ajax({
		url: "vales/controllers/MotivoController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$('#sec_vale_motivo_id').val(respuesta.result.id);
				$('#sec_vale_motivo_empresa').val(respuesta.result.razon_social_id).trigger('change.select2');
				$('#sec_vale_motivo_nombre').val(respuesta.result.nombre);
				$('#sec_vale_motivo_estado').val(respuesta.result.status);
				$('#btn-motivo-registrar').hide();
				$('#btn-motivo-modificar').show();
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

function sec_vale_motivo_eliminar_por_id(id) {
	swal({
		title: "Esta seguro de eliminar el motivo?",
		text:'Los cambios son irreversible',
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		confirmButtonText: "Si, estoy de acuerdo!",
		cancelButtonText: "No, cancelar",
		closeOnConfirm: true,
		closeOnCancel: true,

	},function (isConfirm) {
		if (isConfirm) {
			let data = {
				id_motivo : id,
				accion : "eliminar_motivo",
			}
			auditoria_send({ proceso: "eliminar_motivo_vale_descuento", data: data });
			$.ajax({
				url: "vales/controllers/MotivoController.php",
				type: "POST",
				data: data, //+data,
				beforeSend: function () {},
				complete: function () {},
				success: function (datos) {
					var respuesta = JSON.parse(datos);
					auditoria_send({ proceso: "eliminar_motivo_vale_descuento", data: respuesta });
					if (respuesta.status == 200) {
						alertify.success(respuesta.message, 5);
						sec_vale_motivo_listar();
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
	});
}




