function sec_mepa_mantenimiento_correlativo() {

  let data = {
    action:'correlativo/listar',
  }

  let url = 'sys/router/mepa-mantenimiento/index.php';

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
        sec_mepa_mant_render_table_correlativo(respuesta.result);
      }
    },
    error: function (resp, status) {},
  });

}

function sec_mepa_mant_render_table_correlativo(data = []) {
	$("#table_mepa_correlativo")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'asc']],
		pageLength: 25,
		columns: [
		  { data: "name", className: "text-left" },
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

function sec_mepa_mantenimiento_confirmar_resetear_correlativo(id) {

  let title = "Esta seguro de resetar el Correlativo";

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
      sec_mepa_mantenimiento_resetear_correlativo(id);
    } 
  });

}
function sec_mepa_mantenimiento_resetear_correlativo(id) {

  let data = {
    action:'correlativo/resetear_correlativo',
    id: id
  }


  let url = 'sys/router/mepa-mantenimiento/index.php';

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
        alertify.success(respuesta.message, 5);
      }else{
        alertify.error(respuesta.message, 5);
      }
    },
    error: function (resp, status) {},
  });

}



function sec_mepa_mantenimiento_historial_reseteos(id) {

  let data = {
    action:'correlativo/historial_reseteos',
    id: id,
  }

  let url = 'sys/router/mepa-mantenimiento/index.php';

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
        $('#modal_mepa_historial_correlativo').modal('show');
        sec_mepa_mant_render_table_historial_correlativo(respuesta.result);
        $('#title_reset_mepa_correlativo').html(respuesta.title);
      }
    },
    error: function (resp, status) {},
  });

}

function sec_mepa_mant_render_table_historial_correlativo(data = []) {
	$("#table_mepa_correlativo_historial")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[1, 'desc']],
		pageLength: 25,
		columns: [
		  { data: "usuario", className: "text-left" },
		  { data: "created_at", className: "text-center" },
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
	
	  })
	  .DataTable();



	 
}
