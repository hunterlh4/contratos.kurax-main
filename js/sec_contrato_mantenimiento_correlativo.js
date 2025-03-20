function sec_contrato_mantenimiento_correlativo() {
	sec_contrato_mant_met_listar_correlativo();
}

function sec_contrato_mant_met_listar_correlativo() {
  
  let data = {
    action:'correlativo/listar',
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
        sec_contrato_mant_render_table_correlativo(respuesta.result);
      }
    },
    error: function (resp, status) {},
  });

}

function sec_contrato_mant_render_table_correlativo(data = []) {
	$("#tbl_datos_mantenimientos_contrato_correlativo")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'asc']],
		pageLength: 25,
		columns: [
		  { data: "id", className: "text-center" },
		  { data: "tipo_contrato", className: "text-left" }, 
		  { data: "sigla", className: "text-left" },
		  { data: "numero", className: "text-right" },
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

function sec_contrato_mant_show_modal_correlativo(id) {
	let data = {
		action:'correlativo/obtener_por_id',
		id: id,
	}

	$('#modal_correlativo_editar').modal('show');

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
			$('#modal_corr_tipo_contrato').val(respuesta.result.tipo_contrato);
			$('#modal_corr_sigla').val(respuesta.result.sigla);
			$('#modal_corr_numero').val(respuesta.result.numero);
			$('#modal_corr_id').val(respuesta.result.id);

		}
	},
	error: function (resp, status) {},
	});
}

function sec_contrato_mant_modificar_correlativo() {

	var sigla = $('#modal_corr_sigla').val();
	var numero = $('#modal_corr_numero').val();
	var id = $('#modal_corr_id').val();

	if (sigla == "") {
		alertify.error('Ingrese una sigla',5);
		$("#modal_corr_sigla").focus();
		return false;
	}

	if (numero == "") {
		alertify.error('Ingrese un numero',5);
		$("#modal_corr_numero").focus();
		return false;
	}

	let data = {
		action:'correlativo/modificar_correlativo',
		id: id,
		sigla: sigla,
		numero: numero,
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
			$('#modal_correlativo_editar').modal('hide');
			sec_contrato_mant_met_listar_correlativo();
		}
	},
	error: function (resp, status) {},
	});
}



function sec_contrato_mantenimiento_tipo_contrato() {
	sec_contrato_mant_tipo_contrato();
	sec_contrato_mant_listar_cambio_tipo_contrato();

   // SELECT2 AJAX
   $("#mant_tp_responsable_id").select2({

    ajax: {
      url: "sys/router/contrato-mantenimiento/index.php",
      type: "POST",
      data: function (params) {
        var query = {
          search: params.term,
          action: "correo/obtener_usuarios",
        };
        return query;
      },
      processResults: function (info) {
        var data = JSON.parse(info);
        return {
          results: $.map(data.result, function (item) {
            return {
              text: item.text,
              id: item.id,
            };
          }),
        };
      },
    },
    width: "100%",
    placeholder: "Seleccion un responsable",
    minimumInputLength: 2,
    language: {
      inputTooShort: function () {
        return 'Por favor ingrese 2 o mas caracteres';
      }
    }
  });
}

function sec_contrato_mant_listar_cambio_tipo_contrato() {
  
	let data = {
	  action:'tipo_contrato/listar_cambio_tipo_contrato',
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
		  sec_contrato_mant_render_table_cambio_tipo_contrato(respuesta.result);
		}
	  },
	  error: function (resp, status) {},
	});
  
  }
  
function sec_contrato_mant_render_table_cambio_tipo_contrato(data = []) {
	$("#tbl_datos_mantenimientos_cambio_tipo_contrato")
	.dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'desc']],
		pageLength: 25,
		columns: [
		{ data: "id", className: "text-center" },
		{ data: "tipo_contrato_original", className: "text-left" }, 
		{ data: "tipo_contrato_nuevo", className: "text-left" },
		{ data: "codigo", className: "text-center" },
		{ data: "nro_ticket", className: "text-left" },
		{ data: "responsable", className: "text-left" },
		{ data: "estado", className: "text-center" },
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
		scrollY: true,
		scrollX: true,
		dom: 'Bfrtip',
		buttons: [
			'pageLength',
		],
	})
	.DataTable();



	
}

function sec_contrato_mant_tipo_contrato(){
	let me = this;
	let url = 'sys/router/contrato-mantenimiento/index.php';
	let data = {
		action : 'tipo_contrato/listar',
	};
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
      $('#mant_tp_tipo_contrato_id').find('option').remove().end();
      $('#mant_tp_tipo_contrato_id').append('<option value="0">- Seleccione -</option>');
      if (respuesta.status == 200) {
        $(respuesta.result).each(function(i,e){
          opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
          $('#mant_tp_tipo_contrato_id').append(opcion);	
        })
      }
    },
    error: function (resp, status) {},
  });
}

function sec_contrato_mant_seleccionar_tipo_contrato(){

	var tipo_contrato_id = $('#mant_tp_tipo_contrato_id').val();

	if(tipo_contrato_id.length == 0){
		alertify.error('Seleccione un tipo de contrato',5);
		$("#mant_tp_tipo_contrato_id").focus();
		return false;
	}

	let me = this;
	let url = 'sys/router/contrato-mantenimiento/index.php';
	let data = {
		action : 'tipo_contrato/seleccionar',
		tipo_contrato_id: tipo_contrato_id,
	};
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
      $('#mant_tp_cambiar_tipo_contrato_id').find('option').remove().end();
      $('#mant_tp_cambiar_tipo_contrato_id').append('<option value="0">- Seleccione -</option>');

	  $('#mant_tp_contrato_id').find('option').remove().end();
      $('#mant_tp_contrato_id').append('<option value="0">- Seleccione -</option>');
      if (respuesta.status == 200) {
        $(respuesta.result.tipo_contrato).each(function(i,e){
          opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
          $('#mant_tp_cambiar_tipo_contrato_id').append(opcion);	
        })

		$(respuesta.result.contratos).each(function(i,e){
			opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
			$('#mant_tp_contrato_id').append(opcion);	
		  })
      }
    },
    error: function (resp, status) {},
  });
}

function sec_contrato_mant_obtener_correlativo_por_id(){
	var tipo_contrato_id = $('#mant_tp_cambiar_tipo_contrato_id').val();
	if (tipo_contrato_id.length == 0) {
		$("#mant_tp_codigo").val('');
		return false;
	}
	let me = this;
	let url = 'sys/router/contrato-mantenimiento/index.php';
	let data = {
		action : 'tipo_contrato/obtener_correlativo_por_id',
		tipo_contrato_id: tipo_contrato_id,
	};
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
				$("#mant_tp_codigo").val(respuesta.result.numero);	
			}
			
		},
		error: function (resp, status) {},
	});
}

function sec_contrato_mant_guardar_cambio_tipo_contrato(){

	var tipo_contrato_id = $('#mant_tp_tipo_contrato_id').val();
	var contrato_id = $('#mant_tp_contrato_id').val();
	var cambiar_tipo_contrato_id = $('#mant_tp_cambiar_tipo_contrato_id').val();
	var codigo = $('#mant_tp_codigo').val();
	var nro_ticket = $('#mant_tp_nro_ticket').val();
	var responsable_id = $('#mant_tp_responsable_id').val();
	

	if(tipo_contrato_id.length == 0 || tipo_contrato_id == "0"){
		alertify.error('Seleccione un tipo de contrato',5);
		$("#mant_tp_tipo_contrato_id").select2("open");
		return false;
	}

	if(contrato_id.length == 0 || contrato_id == "0"){
		alertify.error('Seleccione un contrato',5);
		$("#mant_tp_contrato_id").select2("open");
		return false;
	}

	if(cambiar_tipo_contrato_id.length == 0 || cambiar_tipo_contrato_id == "0"){
		alertify.error('Seleccione un tipo de contrato',5);
		$("#mant_tp_cambiar_tipo_contrato_id").select2("open");
		return false;
	}

	if(codigo.length == 0){
		alertify.error('Ingrese un código ',5);
		$("#mant_tp_codigo").focus();
		return false;
	}
	
	if(nro_ticket.length == 0){
		alertify.error('Ingrese un nro de ticket',5);
		$("#mant_tp_nro_ticket").focus();
		return false;
	}

	if(responsable_id.length == 0  || responsable_id == "0"){
		alertify.error('Seleccione un responsable',5);
		$("#mant_tp_responsable_id").select2("open");
		return false;
	}

	let me = this;
	let url = 'sys/router/contrato-mantenimiento/index.php';
	let data = {
		action : 'tipo_contrato/guardar_cambio_tipo_contrato',
		tipo_contrato_id: tipo_contrato_id,
		contrato_id: contrato_id,
		cambiar_tipo_contrato_id: cambiar_tipo_contrato_id,
		codigo: codigo,
		nro_ticket: nro_ticket,
		responsable_id: responsable_id,
	};
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

		$('#mant_tp_tipo_contrato_id').find('option').remove().end();
		$('#mant_tp_tipo_contrato_id').append('<option value="0">- Seleccione -</option>');

		$('#mant_tp_cambiar_tipo_contrato_id').find('option').remove().end();
		$('#mant_tp_cambiar_tipo_contrato_id').append('<option value="0">- Seleccione -</option>');
  
		$('#mant_tp_contrato_id').find('option').remove().end();
		$('#mant_tp_contrato_id').append('<option value="0">- Seleccione -</option>');

		$('#mant_tp_codigo').val('');
		$('#mant_tp_nro_ticket').val('');
		
		$('#mant_tp_responsable_id').find('option').remove().end();
		$('#mant_tp_responsable_id').append('<option value="0">- Seleccione -</option>');

		sec_contrato_mant_tipo_contrato();
		sec_contrato_mant_listar_cambio_tipo_contrato();
      }
    },
    error: function (resp, status) {},
  });
}