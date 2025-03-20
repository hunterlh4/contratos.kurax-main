function sec_herramientas_ti_mantenimiento()
{
	sec_herramientas_ti_mantenimiento_obtener_modulos();
	sec_herramientas_ti_mantenimiento_obtener_tablas();
	sec_herramientas_ti_mantenimiento_listar_procesos();

	$("#frm_herramientas_ti_mantenimiento").submit(function (e) {
		e.preventDefault();

		var modulo_id = $('#sec_herramientas_ti_new_modulo_id').val();
		var nombre = $('#sec_herramientas_ti_new_nombre').val();
		var entidad = $('#sec_herramientas_ti_new_entidad').val();
		var filtro_fecha = $('#sec_herramientas_ti_new_filtro_fecha').val();	
		
		if (modulo_id.length == 0) {
			alertify.error("Seleccione un modulo", 5);
			$("#sec_herramientas_ti_new_modulo_id").focus();
			return false;
		}
		if (nombre.length == 0) {
			alertify.error("Ingrese un nombre", 5);
			$("#sec_herramientas_ti_new_nombre").focus();
			return false;
		}
		if (parseInt(entidad) == 0) {
			alertify.error("Seleccione un entidad", 5);
			$("#sec_herramientas_ti_new_entidad").focus();
			$("#sec_herramientas_ti_new_entidad").select2("open");
			return false;
		}
		if (parseInt(filtro_fecha) == 0) {
			alertify.error("Seleccione la columna de filtro de fecha", 5);
			$("#sec_herramientas_ti_new_filtro_fecha").focus();
			$("#sec_herramientas_ti_new_filtro_fecha").select2("open");
			return false;
		}


		sec_herramientas_ti_mantenimiento_registrar_proceso();
	});

}

function sec_herramientas_ti_mantenimiento_obtener_modulos() {
	
	if (tabla == 0) {
		$('#sec_herramientas_ti_new_modulo_id').find("option").remove().end();
		$('#sec_herramientas_ti_new_modulo_id').append('<option value="0">- Seleccione -</option>');
		return false;
	}
	let data = {
		action: 'herramientas_ti/mantenimiento/obtener_modulos',
		tabla: tabla,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			sec_herramientas_ti_select_value($("[name='sec_herramientas_ti_new_modulo_id']"), respuesta.result);
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_mantenimiento_obtener_tablas() {
	
	let data = {
		action: 'herramientas_ti/mantenimiento/obtener_tablas'
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			sec_herramientas_ti_select_value($("[name='sec_herramientas_ti_new_entidad']"), respuesta.result);
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_mantenimiento_obtener_columndas_de_tabla() {
	
	var tabla = $('#sec_herramientas_ti_new_entidad').val();
	if (tabla == 0) {
		$('#sec_herramientas_ti_new_filtro_fecha').find("option").remove().end();
		$('#sec_herramientas_ti_new_filtro_fecha').append('<option value="0">- Seleccione -</option>');
		return false;
	}
	let data = {
		action: 'herramientas_ti/mantenimiento/obtener_columnas_de_tabla',
		tabla: tabla,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			sec_herramientas_ti_select_value($("[name='sec_herramientas_ti_new_filtro_fecha']"), respuesta.result);
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_select_value(select, data) {
	$(select).find("option").remove().end();
	$(select).append('<option value="0">- Seleccione -</option>');
	$(data).each(function (i, e) {
		opcion = $("<option value='" + e.value + "'>" + e.text + "</option>");
		$(select).append(opcion);
	});
}


function sec_herramientas_ti_mantenimiento_registrar_proceso() {
	
	var modulo_id = $('#sec_herramientas_ti_new_modulo_id').val();
	var nombre = $('#sec_herramientas_ti_new_nombre').val();
	var entidad = $('#sec_herramientas_ti_new_entidad').val();
	var filtro_fecha = $('#sec_herramientas_ti_new_filtro_fecha').val();	


	let data = {
		action: 'herramientas_ti/mantenimiento/registrar_proceso',
		modulo_id: modulo_id,
		nombre: nombre,
		entidad: entidad,
		filtro_fecha: filtro_fecha,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			alertify.success("Se ha registrado exitosamente el proceso.", 5);
			sec_herramientas_ti_mantenimiento_listar_procesos();
			$('#sec_herramientas_ti_new_modulo_id').val(0).trigger('change');
			$('#sec_herramientas_ti_new_nombre').val('');
			$('#sec_herramientas_ti_new_entidad').val(0).trigger('change');
			$('#sec_herramientas_ti_new_filtro_fecha').val(0).trigger('change');	
			
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_mantenimiento_listar_procesos() {
	
	let data = {
		action: 'herramientas_ti/mantenimiento/listar_procesos',
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			fnc_render_table_procesos(respuesta.result);
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_mantenimiento_modal_proceso(proceso_id,type) {

	$('#sec_herramientas_ti_modal_proceso').modal('show');

	let data = {
		action : 'herramientas_ti/mantenimiento/obtener_proceso_por_id',
		proceso_id : proceso_id,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
		
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
			sec_herramientas_ti_mantenimiento_modal_proceso_obtener_modulos();
			
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				
				
				$('#modal_herramienta_ti_proc_proceso_id').val(proceso_id);
				$('#modal_herramienta_ti_proc_nombre').val(respuesta.result.nombre);
				$('#modal_herramienta_ti_proc_tabla').val(respuesta.result.tabla);
				$('#modal_herramienta_ti_proc_status').val(respuesta.result.status);
				
				sec_herramientas_ti_mantenimiento_modal_proceso_obtener_columnas_tabla();
				
				setTimeout(() => {
					$('#modal_herramienta_ti_proc_modulo_id').val(respuesta.result.modulo_id).trigger('change');
					$('#modal_herramienta_ti_proc_filtro_fecha').val(respuesta.result.filtro_fecha).trigger('change');
				}, 1000);
			}
			},
		error: function () {},
	});
}

function sec_herramientas_ti_mantenimiento_modal_proceso_obtener_modulos() {
	

	let data = {
		action: 'herramientas_ti/mantenimiento/obtener_modulos',
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			sec_herramientas_ti_select_value($("[name='modal_herramienta_ti_proc_modulo_id']"), respuesta.result);
		  }
		},
		error: function () {},
	});
}


function sec_herramientas_ti_mantenimiento_modal_proceso_obtener_columnas_tabla() {
	
	var tabla = $('#modal_herramienta_ti_proc_tabla').val();
	
	let data = {
		action: 'herramientas_ti/mantenimiento/obtener_columnas_de_tabla',
		tabla: tabla,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			sec_herramientas_ti_select_value($("[name='modal_herramienta_ti_proc_filtro_fecha']"), respuesta.result);
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_mantenimiento_modal_editar_proceso() {
	
	var id = $('#modal_herramienta_ti_proc_proceso_id').val();
	var modulo_id = $('#modal_herramienta_ti_proc_modulo_id').val();
	var nombre = $('#modal_herramienta_ti_proc_nombre').val();
	var tabla = $('#modal_herramienta_ti_proc_tabla').val();
	var filtro_fecha = $('#modal_herramienta_ti_proc_filtro_fecha').val();	
	var status = $('#modal_herramienta_ti_proc_status').val();	

	if (parseInt(modulo_id) == 0) {
		alertify.error("Seleccione un modulo", 5);
		$("#modal_herramienta_ti_proc_modulo_id").focus();
		$("#modal_herramienta_ti_proc_modulo_id").select2("open");
		return false;
	}
	if (nombre.length == 0) {
		alertify.error("Ingrese un nombre", 5);
		$("#modal_herramienta_ti_proc_nombre").focus();
		return false;
	}
	if (parseInt(filtro_fecha) == 0) {
		alertify.error("Seleccione la columna de filtro de fecha", 5);
		$("#modal_herramienta_ti_proc_filtro_fecha").focus();
		$("#modal_herramienta_ti_proc_filtro_fecha").select2("open");
		return false;
	}

	let data = {
		action: 'herramientas_ti/mantenimiento/editar_proceso',
		id: id,
		modulo_id: modulo_id,
		nombre: nombre,
		tabla: tabla,
		filtro_fecha: filtro_fecha,
		status: status,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			alertify.success("Se ha modificado exitosamente el proceso.", 5);
			$('#sec_herramientas_ti_modal_proceso').modal('hide');
			sec_herramientas_ti_mantenimiento_listar_procesos();
		  }else{
			alertify.error("A ocurrido un error, intentalo mas tarde.", 5);
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_mantenimiento_eliminar_proceso(proceso_id) {
	

	let data = {
		action: 'herramientas_ti/mantenimiento/eliminar_proceso',
		id: proceso_id,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			alertify.success("Se ha eliminado el proceso correctamente", 5);
			sec_herramientas_ti_mantenimiento_listar_procesos();
		  }else{
			alertify.error("A ocurrido un error, intentalo mas tarde.", 5);
		  }
		},
		error: function () {},
	});
}


function fnc_render_table_procesos(data = []) {
	$("#tbl_herramienta_ti_procesos")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'desc']],
		pageLength: 25,
		columns: [
		  { data: "id", className: "text-center" },
		  { data: "modulo", className: "text-left" },
		  { data: "nombre", className: "text-left" }, 
		  { data: "tabla", className: "text-left" },
		  { data: "filtro_fecha", className: "text-left" },
		  { data: "estado", className: "text-center" },
		  { data: "acciones", className: "text-center" },
		],
		language: {
		  decimal: "",
		  emptyTable: "Tabla vacia",
		  info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
		  infoEmpty: "Mostrando 0 a 0 de 0 entradas",
		  infoFiltered: "(filtrado de _MAX_ entradas)",
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
		  
		}
	  })
	  .DataTable();



	 
}

function sec_herramientas_ti_mantenimiento_modal_definir_columnas(proceso_id,tabla) {
	
	$('#sec_herramientas_ti_modal_definir_columnas').modal('show');
	$('#modal_herramienta_ti_proc_det_proceso_id').val(proceso_id);
	sec_herramientas_ti_mantenimiento_modal_obtener_columndas_de_tabla(tabla);
	sec_herramientas_ti_mantenimiento_listar_procesos_detalle();
	let data = {
		action: 'herramientas_ti/mantenimiento/listar_procesos',
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			
			fnc_render_table_procesos(respuesta.result);
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_mantenimiento_modal_obtener_columndas_de_tabla(tabla) {
	

	let data = {
		action: 'herramientas_ti/mantenimiento/obtener_columnas_de_tabla',
		tabla: tabla,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			sec_herramientas_ti_select_value($("[name='modal_herramienta_ti_proc_det_columna']"), respuesta.result);
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_mantenimiento_modal_registrar_proceso_detalle() {
	
	var proceso_id = $('#modal_herramienta_ti_proc_det_proceso_id').val();
	var columna = $('#modal_herramienta_ti_proc_det_columna').val();

	if (parseInt(columna) == 0) {
		alertify.error("Seleccione una columna", 5);
		$("#modal_herramienta_ti_proc_det_columna").focus();
		$("#modal_herramienta_ti_proc_det_columna").select2("open");
		return false;
	}

	let data = {
		action: 'herramientas_ti/mantenimiento/registrar_proceso_detalle',
		proceso_id: proceso_id,
		columna: columna,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {

			alertify.success("Se ha registrado exitosamente la columna al proceso.", 5);
			sec_herramientas_ti_mantenimiento_listar_procesos_detalle();
		  }else{
			alertify.error(respuesta.message, 5);
			return false;
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_mantenimiento_listar_procesos_detalle() {
	var  proceso_id = $('#modal_herramienta_ti_proc_det_proceso_id').val();
	let data = {
		action: 'herramientas_ti/mantenimiento/listar_procesos_detalle',
		proceso_id: proceso_id,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			$('#modal-title-definir-columna').html(respuesta.result.proceso.nombre + ' - Definir Columnas')
			fnc_render_table_procesos_detalle(respuesta.result.proceso_detalle);
		  }
		},
		error: function () {},
	});
}

function fnc_render_table_procesos_detalle(data = []) {
	$("#tbl_herramienta_ti_proceso_detalle")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'desc']],
		pageLength: 25,
		columns: [
		  { data: "id", className: "text-center" },
		  { data: "columna", className: "text-left" }, 
		  { data: "estado", className: "text-center" },
		  { data: "acciones", className: "text-center" },
		],
		language: {
		  decimal: "",
		  emptyTable: "Tabla vacia",
		  info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
		  infoEmpty: "Mostrando 0 a 0 de 0 entradas",
		  infoFiltered: "(filtrado de _MAX_ entradas)",
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
		  
		}
	  })
	  .DataTable();


	sec_herramientas_ti_mantenimiento_change_state_proceso_detalle();
	
}

function sec_herramientas_ti_mantenimiento_eliminar_procesos_detalle(proceso_detalle_id) {
	let data = {
		action: 'herramientas_ti/mantenimiento/eliminar_procesos_detalle',
		proceso_detalle_id: proceso_detalle_id,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {

			alertify.success("Se ha eliminado exitosamente la columna.", 5);
			sec_herramientas_ti_mantenimiento_listar_procesos_detalle();
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_mantenimiento_change_state_proceso_detalle() {
	$('.btn_state_proceso_detalle').on('click', function() {

		var btn_estado = $(this);
		var proceso_detalle_id = btn_estado.attr('proceso_detalle_id');
		var estado = btn_estado.attr('estado');
		let data = {
			action: 'herramientas_ti/mantenimiento/actualizar_estado_proceso_detalle',
			proceso_detalle_id : proceso_detalle_id,
			estado : estado,
		}

		$.ajax({
			url: "/sys/router/herramientas_ti/index.php",
			type: "POST",
			data: data,
			beforeSend: function () {
				loading("true");
			},
			complete: function () {
				loading();
			},
			success: function (datos) {
			  var respuesta = JSON.parse(datos);
			  if (respuesta.status == 200) {
				if (parseInt(respuesta.result) == 1) {

					alertify.success("Se ha activado la columna.", 5);
					btn_estado.removeClass('btn-warning');
					btn_estado.addClass('btn-info');
					btn_estado.html('Activo');
					btn_estado.attr('estado',1);

				}else{
					alertify.warning("Se ha inactivado la columna.", 5);
					btn_estado.removeClass('btn-info');
					btn_estado.addClass('btn-warning');
					btn_estado.html('Inactivo');
					btn_estado.attr('estado',0);
				}
			  }
			},
			error: function () {},
		});
	
	});
}