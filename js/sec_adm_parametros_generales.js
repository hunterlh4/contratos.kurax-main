function sec_adm_parametros_generales(){
  if (sec_id == 'adm_parametros_generales') {
    sec_adm_listar_parametros_generales();
  }
}

function sec_adm_listar_parametros_generales() {
  
    let data = {
      action:'parametros_generales/listar',
    }
  
    let url = 'sys/router/mantenimiento/index.php';
  
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
          sec_adm_render_table_parametros_generales(respuesta.result);
        }
      },
      error: function (resp, status) {},
    });
  
}

function sec_adm_render_table_parametros_generales(data = []) {
    $("#sec_table_parametros_generales")
    .dataTable({
        bDestroy: true,
        data: data,
        responsive: true,
        order: [[0, 'asc']],
        pageLength: 25,
        columns: [
        { data: "id", className: "text-center" },
        { data: "descripcion", className: "text-left" }, 
        { data: "codigo", className: "text-left" },
        { data: "valor", className: "text-left" },
        { data: "ticket", className: "text-left" },
        { data: "fecha", className: "text-center" },
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

function sec_adm_modal_ver_parametros_generales(id) {
  sec_adm_modal_show_parametros_generales(id,'ver');
}
function sec_adm_modal_editar_parametros_generales(id) {
  sec_adm_modal_show_parametros_generales(id,'editar');
}
function sec_adm_modal_ver_historial_parametros_generales(id){

    $('#modalParametrosHistoricoCambios').modal('show');

    var data = {
        action: "parametros_generales/listar_historial",
        parametro_general_id: id
    };
    $("#parametros_historico_div_tabla").show();

    var columnDefs = [{
        className: 'text-center',
        targets: [0, 1, 2, 3, 4, 5, 6, 7]
    }];

    var tabla = crearDataTable(
        "#parametros_historico_datatable",
        "sys/router/mantenimiento/index.php",
        data,
        columnDefs
    );

    tabla.on('init.dt', function () {
        $('.dataTables_filter').hide();
    });
}
  
function sec_adm_modal_show_parametros_generales(id,tipo) {
  let data = {
    action:'parametros_generales/obtener_por_id',
    id : id,
  }
  if (tipo == "ver") {
    $('#modal-title-parametros-generales').html('Ver Parámetros Generales');
    $('#sec_adm_modal_pg_descripcion').prop('disabled', true);
    $('#sec_adm_modal_pg_id').prop('disabled', true);
    $('#sec_adm_modal_pg_codigo').prop('disabled', true);
    $('#sec_adm_modal_pg_valor').prop('disabled', true);
    $('#sec_adm_modal_pg_ticket').prop('disabled', true);
    $('#sec_adm_modal_pg_estado').prop('disabled', true);
    $('#btn-modal-parametros-generales-modificar').hide();
  }else if(tipo == "editar"){
    $('#modal-title-parametros-generales').html('Modificar Parámetros Generales');
    $('#sec_adm_modal_pg_descripcion').prop('disabled', false);
    $('#sec_adm_modal_pg_id').prop('disabled', false);
    $('#sec_adm_modal_pg_codigo').prop('disabled', false);
    $('#sec_adm_modal_pg_valor').prop('disabled', false);
    $('#sec_adm_modal_pg_ticket').prop('disabled', false);
    $('#sec_adm_modal_pg_estado').prop('disabled', false);
    $('#btn-modal-parametros-generales-modificar').show();
  }

  let url = 'sys/router/mantenimiento/index.php';

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
        $('#sec_adm_modal_parametros_generales').modal('show');

        $('#sec_adm_modal_pg_descripcion').val(respuesta.result.descripcion);
        $('#sec_adm_modal_pg_id').val(respuesta.result.id);
        $('#sec_adm_modal_pg_codigo').val(respuesta.result.codigo);
        $('#sec_adm_modal_pg_valor').val(respuesta.result.valor);
        $('#sec_adm_modal_pg_ticket').val(respuesta.result.ticket);
        $('#sec_adm_modal_pg_estado').val(respuesta.result.estado);
      }
    },
    error: function (resp, status) {},
  });
}

function sec_adm_modal_registrar_parametro_general() {
  
  var descripcion = $('#sec_pg_descripcion').val().trim();
  var codigo = $('#sec_pg_codigo').val().trim();
  var valor = $('#sec_pg_valor').val().trim();
  var ticket = $('#sec_pg_ticket').val().trim();
  
  if (descripcion.length == 0){
    alertify.error('Ingrese una descripción',5);
		$("#sec_pg_descripcion").focus();
		return false;
  }
  if (codigo.length == 0){
    alertify.error('Ingrese un codigo',5);
		$("#sec_pg_codigo").focus();
		return false;
  }
  if (valor.length == 0){
    alertify.error('Ingrese un valor',5);
		$("#sec_pg_valor").focus();
		return false;
  }
  if (ticket.length == 0){
    alertify.error('Ingrese una ticket',5);
		$("#sec_pg_ticket").focus();
		return false;
  }
	
  let data = {
    action:'parametros_generales/registrar_parametro_general',
    descripcion : descripcion,
    codigo : codigo,
    valor : valor,
    ticket : ticket,
    estado : 1,
  }

  let url = 'sys/router/mantenimiento/index.php';

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
        alertify.success('Se ha registrado correctamente el parámetro general',5);
        $('#sec_pg_descripcion').val('');
        $('#sec_pg_codigo').val('');
        $('#sec_pg_valor').val('');
        $('#sec_pg_ticket').val('');
        sec_adm_listar_parametros_generales();


      }
    },
    error: function (resp, status) {},
  });

}


function sec_adm_modal_modificar_parametro_general() {
  
  var id = $('#sec_adm_modal_pg_id').val().trim();
  var descripcion = $('#sec_adm_modal_pg_descripcion').val().trim();
  var codigo = $('#sec_adm_modal_pg_codigo').val().trim();
  var valor = $('#sec_adm_modal_pg_valor').val().trim();
  var ticket = $('#sec_adm_modal_pg_ticket').val().trim();
  var estado = $('#sec_adm_modal_pg_estado').val();

  if (descripcion.length == 0){
    alertify.error('Ingrese una descripción',5);
		$("#sec_adm_modal_pg_descripcion").focus();
		return false;
  }
  if (codigo.length == 0){
    alertify.error('Ingrese un codigo',5);
		$("#sec_adm_modal_pg_codigo").focus();
		return false;
  }
  if (valor.length == 0){
    alertify.error('Ingrese un valor',5);
		$("#sec_adm_modal_pg_valor").focus();
		return false;
  }
  if (ticket.length == 0){
    alertify.error('Ingrese una ticket',5);
		$("#sec_adm_modal_pg_ticket").focus();
		return false;
  }

  let data = {
    action:'parametros_generales/modificar_parametro_general',
    id : id,
    descripcion : descripcion,
    codigo : codigo,
    valor : valor,
    ticket : ticket,
    estado : estado,
  }

  let url = 'sys/router/mantenimiento/index.php';

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
        alertify.success('Se ha actualizado correctamente el parámetro general',5);
        $('#sec_adm_modal_parametros_generales').modal('hide');
        sec_adm_listar_parametros_generales();
      }
    },
    error: function (resp, status) {},
  });

}
