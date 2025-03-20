function sec_vale_parametros_fraccionamiento() {
	$(".select2").select2({ width: "100%" });
	sec_vale_fraccionamiento_reset();
	//sec_vale_fraccionamiento_listar();

	$('#frm_vale_mant_fraccionamiento').submit(function (evt) {
		evt.preventDefault();
		var id = $('#sec_vale_fracc_id').val();
		var monto_minimo = $('#sec_vale_fracc_monto_minimo').val();
		var monto_maximo = $('#sec_vale_fracc_monto_maximo').val();
		var cuotas = $('#sec_vale_fracc_cuotas').val();
		var estado = $('#sec_vale_fracc_estado').val();
		
		var title = '';
		if (id.length == 0) {
			title = 'Esta seguro de registrar el fraccionamiento?';
		}else{
			title = 'Esta seguro de modificar el fraccionamiento?';
		}

		if (monto_minimo.length == 0) {
			alertify.error("Ingrese un monto", 5);
			$("#sec_vale_fracc_monto_minimo").focus();
			return false;
		}

		if (monto_maximo.length == 0) {
			alertify.error("Ingrese un monto", 5);
			$("#sec_vale_fracc_monto_maximo").focus();
			return false;
		}

		if (cuotas == 0) {
			alertify.error("Ingrese un monto", 5);
			$("#sec_vale_fracc_cuotas").focus();
			$("#sec_vale_fracc_cuotas").select2("open");
			return false;
		}

		if (estado.length == 0) {
			alertify.error("Ingrese un monto", 5);
			$("#sec_vale_fracc_estado").focus();
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
				sec_vale_fraccionamiento_guardar();
			} 
		});
	});

	
}


function sec_vale_fraccionamiento_listar() {

	var data = {
		accion : 'listar_fraccionamientos'
	};

	$.ajax({
		url: "vales/controllers/FraccionamientoController.php",
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
				fnc_render_table_fraccionamiento(respuesta.result);
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

function fnc_render_table_fraccionamiento(data = []) {
	$("#tbl_vale_fraccionamiento")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'asc']],
		columns: [
		  { data: "index", className: "text-center" },
		  { data: "monto_minimo", className: "text-left" }, 
		  { data: "monto_maximo", className: "text-left" }, 
		  { data: "cuotas", className: "text-left" }, 
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

	  $(".switch-vale-fraccionamiento").bootstrapToggle({
		on: "activo",
		off: "inactivo",
		onstyle: "success",
		offstyle: "danger",
		size: "mini",
	  });

	  $(".switch-vale-fraccionamiento")
	  .off()
	  .change(function (event) {
		var id = $(this).attr("data-id");
		sec_vale_fraccionamiento_modificar_estado(id);
	  });
  
}

function sec_vale_fraccionamiento_guardar() {

	var id = $('#sec_vale_fracc_id').val();
	var monto_minimo = $('#sec_vale_fracc_monto_minimo').val();
	var monto_maximo = $('#sec_vale_fracc_monto_maximo').val();
	var cuotas = $('#sec_vale_fracc_cuotas').val();
	var estado = $('#sec_vale_fracc_estado').val();

	var data = {
		id : id,
		monto_minimo : monto_minimo,
		monto_maximo : monto_maximo,
		cuotas : cuotas,
		estado : estado,
		accion : 'guardar_fraccionamiento'
	};

	auditoria_send({ proceso: "guardar_fraccionamiento_vale_descuento", data: data });

	$.ajax({
		url: "vales/controllers/FraccionamientoController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			auditoria_send({ proceso: "guardar_fraccionamiento_vale_descuento", data: respuesta });
			if (respuesta.status == 200) {
				alertify.success(respuesta.message, 10);
				sec_vale_fraccionamiento_reset();
				sec_vale_fraccionamiento_listar();
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


function sec_vale_fraccionamiento_modificar_estado(id) {

	let data = {
		id : id,
		accion : "modificar_estado",
	}
	auditoria_send({ proceso: "modificar_estado_vale_descuento", data: data });
	$.ajax({
		url: "vales/controllers/FraccionamientoController.php",
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

function sec_vale_fraccionamiento_reset() {
	$('#sec_vale_fracc_id').val('');
	$('#sec_vale_fracc_monto_minimo').val('');
	$('#sec_vale_fracc_monto_maximo').val('');
	$('#sec_vale_fracc_cuotas').val('0').trigger('change.select2');
	$('#sec_vale_fracc_estado').val('1');
	$('#btn-fraccionamiento-registrar').show();
	$('#btn-fraccionamiento-modificar').hide();
}

function sec_vale_fraccionamiento_obtener_por_id(id) {

	let data = {
		id : id,
		accion : "obtener_fraccionamiento_por_id",
	}
	$.ajax({
		url: "vales/controllers/FraccionamientoController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$('#sec_vale_fracc_id').val(respuesta.result.id);
				$('#sec_vale_fracc_monto_minimo').val(respuesta.result.monto_minimo);
				$('#sec_vale_fracc_monto_maximo').val(respuesta.result.monto_maximo);
				$('#sec_vale_fracc_cuotas').val(respuesta.result.cuotas).trigger('change.select2');
				$('#sec_vale_fracc_estado').val(respuesta.result.status);
				$('#btn-fraccionamiento-registrar').hide();
				$('#btn-fraccionamiento-modificar').show();
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

function sec_vale_fraccionamiento_eliminar_por_id(id) {
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
				id : id,
				accion : "eliminar_fraccionamiento",
			}
			auditoria_send({ proceso: "eliminar_fraccionamiento_vale_descuento", data: data });
			$.ajax({
				url: "vales/controllers/FraccionamientoController.php",
				type: "POST",
				data: data, //+data,
				beforeSend: function () {},
				complete: function () {},
				success: function (datos) {
					var respuesta = JSON.parse(datos);
					auditoria_send({ proceso: "eliminar_fraccionamiento_vale_descuento", data: respuesta });
					if (respuesta.status == 200) {
						alertify.success(respuesta.message, 5);
						sec_vale_fraccionamiento_listar();
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




