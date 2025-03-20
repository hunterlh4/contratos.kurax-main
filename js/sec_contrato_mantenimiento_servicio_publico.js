function sec_contrato_mantenimiento_servicio_publico() {
	sec_contrato_mant_met_listar_servicio_publico();
}

function sec_contrato_mant_met_listar_servicio_publico() {
  
  let data = {
    action:'servicio_publico/listar_empresas',
  }

  let url = 'sys/router/contrato-mantenimiento/index.php';

  $.ajax({
    url: url,
    type: "POST",
    data: data,

    beforeSend: function () {
      loading("true");
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      var respuesta = JSON.parse(resp);
      if (respuesta.status == 200) {
        sec_contrato_mant_render_table_servicio_publico(respuesta.result);
      }
    },
    error: function (resp, status) {},
  });

}

function sec_contrato_mant_render_table_servicio_publico(data = []) {
	$("#tbl_datos_mantenimientos_empresas_servicio_publicos")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'asc']],
		pageLength: 25,
		columns: [
		  { data: "id", className: "text-center" },
		  { data: "ruc", className: "text-left" }, 
		  { data: "razon_social", className: "text-left" }, 
		  { data: "nombre_comercial", className: "text-left" },
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
        ],
	  })
	  .DataTable();



	 
}

function sec_contrato_mant_show_modal_servicio_publico(id) {
	let data = {
		action:'servicio_publico/obtener_empresa_por_id',
		id: id,
	}

	$('#modal_empresa_servicio_publico_editar').modal('show');

	let url = 'sys/router/contrato-mantenimiento/index.php';

	$.ajax({
	url: url,
	type: "POST",
	data: data,

	beforeSend: function () {
		loading("true");
	},
	complete: function () {
		loading();
	},
	success: function (resp) {
		var respuesta = JSON.parse(resp);
		if (respuesta.status == 200) {
			$('#modal_ser_pub_ruc').val(respuesta.result.ruc);
			$('#modal_ser_pub_razon_social').val(respuesta.result.razon_social);
			$('#modal_ser_pub_nombre_comercial').val(respuesta.result.nombre_comercial);
			$('#modal_ser_pub_status').val(respuesta.result.status);
			$('#modal_ser_pub_id').val(respuesta.result.id);

		}
	},
	error: function (resp, status) {},
	});
}

function sec_contrato_mant_modificar_empresa_servicio_publico() {

	var ruc = $('#modal_ser_pub_ruc').val();
	var razon_social = $('#modal_ser_pub_razon_social').val();
	var nombre_comercial = $('#modal_ser_pub_nombre_comercial').val();
	var status = $('#modal_ser_pub_status').val();
	var id = $('#modal_ser_pub_id').val();

	if (ruc.length != 11) {
		alertify.error('Ingrese un ruc',5);
		$("#modal_ser_pub_ruc").focus();
		return false;
	}

	if (razon_social == "") {
		alertify.error('Ingrese una razón social',5);
		$("#modal_ser_pub_razon_social").focus();
		return false;
	}

	if (nombre_comercial == "") {
		alertify.error('Ingrese un nombre comercial',5);
		$("#modal_ser_pub_nombre_comercial").focus();
		return false;
	}

	if (status == "") {
		alertify.error('Seleccione un estado',5);
		$("#modal_ser_pub_status").focus();
		return false;
	}

	let data = {
		action:'servicio_publico/modificar_empresa',
		id: id,
		ruc: ruc,
		razon_social: razon_social,
		nombre_comercial: nombre_comercial,
		status: status,
	}

	let url = 'sys/router/contrato-mantenimiento/index.php';

	$.ajax({
	url: url,
	type: "POST",
	data: data,

	beforeSend: function () {
		loading("true");
	},
	complete: function () {
		loading();
	},
	success: function (resp) {
		var respuesta = JSON.parse(resp);
		if (respuesta.status == 200) {
			$('#modal_empresa_servicio_publico_editar').modal('hide');
			sec_contrato_mant_met_listar_servicio_publico();
		}
	},
	error: function (resp, status) {},
	});
}

function sec_contrato_mant_registrar_empresa_servicio_publico() {

	var ruc = $('#sec_cont_mant_ser_pub_ruc').val();
	var razon_social = $('#sec_cont_mant_ser_pub_razon_social').val();
	var nombre_comercial = $('#sec_cont_mant_ser_pub_nombre_comercial').val();
	var status = 1;

	if (ruc.length != 11) {
		alertify.error('El ruc debe tener 11 digitos',5);
		$("#modal_ser_pub_ruc").focus();
		return false;
	}

	if (razon_social == "") {
		alertify.error('Ingrese una razón social',5);
		$("#modal_ser_pub_razon_social").focus();
		return false;
	}

	if (nombre_comercial == "") {
		alertify.error('Ingrese un nombre comercial',5);
		$("#modal_ser_pub_nombre_comercial").focus();
		return false;
	}


	let data = {
		action:'servicio_publico/registrar_empresa',
		ruc: ruc,
		razon_social: razon_social,
		nombre_comercial: nombre_comercial,
		status: status,
	}

	let url = 'sys/router/contrato-mantenimiento/index.php';

	$.ajax({
	url: url,
	type: "POST",
	data: data,

	beforeSend: function () {
		loading("true");
	},
	complete: function () {
		loading();
	},
	success: function (resp) {
		var respuesta = JSON.parse(resp);
		if (respuesta.status == 200) {
			sec_contrato_mant_met_listar_servicio_publico();
			$('#sec_cont_mant_ser_pub_ruc').val('');
			$('#sec_cont_mant_ser_pub_razon_social').val('');
			$('#sec_cont_mant_ser_pub_nombre_comercial').val('');
		}
	},
	error: function (resp, status) {},
	});
}

