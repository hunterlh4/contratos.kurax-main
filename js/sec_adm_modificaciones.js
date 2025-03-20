function sec_adm_modificaciones(){
  if (sec_id == 'adm_versiones') {
    sec_adm_listar_modificaciones();
  }



  $('.modal_fecha_datetime').datetimepicker({
    format: 'YYYY-MM-DD HH:mm:ss',
    icons: {
      time: 'fa fa-clock-o',
      date: 'fa fa-calendar',
      up: 'fa fa-chevron-up',
      down: 'fa fa-chevron-down',
      previous: 'fa fa-chevron-left',
      next: 'fa fa-chevron-right',
      today: 'fa fa-calendar-check-o',
      clear: 'fa fa-trash-o',
      close: 'fa fa-times'
    }
  });


}

function sec_adm_listar_modificaciones() {
  
    let data = {
      action:'modificaciones/listar',
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
          sec_adm_render_table_modificaciones(respuesta.result);
        }
      },
      error: function (resp, status) {},
    });
  
}

function sec_adm_render_table_modificaciones(data = []) {
  
    $("#sec_table_modificaciones")
    .dataTable({
        bDestroy: true,
        data: data,
        responsive: true,
        order: [[0, 'asc']],
        pageLength: 25,
        columns: [
        { data: "id", className: "text-center" },
        { data: "modulo", className: "text-left" }, 
        { data: "version", className: "text-center" },
        { data: "comment", className: "text-left" },
        { data: "updated_at", className: "text-left" },
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

function sec_adm_modal_ver_modificaciones(id) {
  sec_adm_modal_show_modificaciones(id,'ver');
}
function sec_adm_modal_editar_modificaciones(id) {
  sec_adm_modal_show_modificaciones(id,'editar');
}
  
function sec_adm_modal_show_modificaciones(id,tipo) {
  let data = {
    action:'modificaciones/obtener_por_id',
    id : id,
  }
  if (tipo == "ver") {
    $('#modal-title-modificaciones').html('Ver');
    $('#sec_adm_modal_mod_id').prop('disabled', true);
    $('#sec_adm_modal_mod_modulo').prop('disabled', true);
    $('#sec_adm_modal_mod_version').prop('disabled', true);
    $('#sec_adm_modal_mod_descripcion').prop('disabled', true);
    $('#sec_adm_modal_mod_fecha_modificacicon').prop('disabled', true);
    $('#sec_adm_modal_mod_estado').prop('disabled', true);
    $('#btn-modal-modificacion-modificar').hide();
  }else if(tipo == "editar"){
    $('#modal-title-modificaciones').html('Modificar');
    $('#sec_adm_modal_mod_id').prop('disabled', false);
    $('#sec_adm_modal_mod_modulo').prop('disabled', false);
    $('#sec_adm_modal_mod_version').prop('disabled', false);
    $('#sec_adm_modal_mod_descripcion').prop('disabled', false);
    $('#sec_adm_modal_mod_fecha_modificacicon').prop('disabled', false);
    $('#sec_adm_modal_mod_estado').prop('disabled', false);
    $('#btn-modal-modificacion-modificar').show();
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
        $('#sec_adm_modal_modificaciones').modal('show');

        $('#sec_adm_modal_mod_id').val(respuesta.result.id);
        $('#sec_adm_modal_mod_modulo').val(respuesta.result.modulo);
        $('#sec_adm_modal_mod_version').val(respuesta.result.version);
        $('#sec_adm_modal_mod_descripcion').val(respuesta.result.comment);
        $('#sec_adm_modal_mod_fecha_modificacicon').val(respuesta.result.updated_at);
        $('#sec_adm_modal_mod_estado').val(respuesta.result.status);
      }
    },
    error: function (resp, status) {},
  });
}

function sec_adm_modal_registrar_modificacion() {
  
  var modulo = $('#sec_mod_modulo').val().trim();
  var version = $('#sec_mod_version').val().trim();
  var descripcion = $('#sec_mod_descripcion').val().trim();

  if (modulo.length == 0){
    alertify.error('Ingrese un modulo',5);
		$("#sec_mod_modulo").focus();
		return false;
  }
  if (version.length == 0){
    alertify.error('Ingrese una versión',5);
		$("#sec_mod_version").focus();
		return false;
  }

	
  let data = {
    action:'modificaciones/registrar',
    modulo : modulo,
    version : version,
    descripcion : descripcion,
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
        alertify.success('Se ha registrado correctamente la Versión JS',5);
        $('#sec_mod_modulo').val('');
        $('#sec_mod_version').val('');
        $('#sec_mod_descripcion').val('');
        sec_adm_listar_modificaciones();
      }
    },
    error: function (resp, status) {},
  });

}


function sec_adm_modal_modificar_modificacion() {
  
  var id = $('#sec_adm_modal_mod_id').val().trim();
  var modulo = $('#sec_adm_modal_mod_modulo').val().trim();
  var version = $('#sec_adm_modal_mod_version').val().trim();
  var descripcion = $('#sec_adm_modal_mod_descripcion').val().trim();
  var fecha_modificacion = $('#sec_adm_modal_mod_fecha_modificacicon').val().trim();
  var estado = $('#sec_adm_modal_mod_estado').val();

  if (modulo.length == 0){
    alertify.error('Ingrese un modulo',5);
		$("#sec_adm_modal_mod_modulo").focus();
		return false;
  }

  if (fecha_modificacion.length == 0){
    alertify.error('Ingrese una fecha de modificación',5);
		$("#sec_adm_modal_mod_fecha_modificacicon").focus();
		return false;
  }

  let data = {
    action:'modificaciones/modificar',
    id : id,
    modulo : modulo,
    version : version,
    descripcion : descripcion,
    fecha_modificacion : fecha_modificacion,
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
        alertify.success('Se ha modificado correctamente la Versión JS',5);
        $('#sec_adm_modal_modificaciones').modal('hide');
        sec_adm_listar_modificaciones();
      }
    },
    error: function (resp, status) {},
  });

}
