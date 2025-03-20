function sec_contrato_mantenimiento_correo_metodo() {
  sec_contrato_mant_corr_met_obtener_datos("obtener_tipo_correo_metodo","[name='cont_mant_corr_met_tipo_correo_metodo_id']");
  sec_contrato_mant_met_listar_metodo();
  sec_contrato_mant_met_frm_reset();
  sec_contrato_mant_corr_tabs();
  sec_con_mant_not_cont_cerrar_area();
  sec_con_mant_not_ajustar_dataTable();
  $("#Frm_RegistroCorreoMetodo").submit(function (e) {
    e.preventDefault();
    sec_contrato_mant_corr_met_validar_metodo();
  });

  $("#Frm_RegistroCorreo").submit(function (e) {
    e.preventDefault();
    sec_contrato_mant_corr_modal_validar_correo();
  });

  $("#Frm_RegistroArea").submit(function (e) {
    e.preventDefault();
    sec_contrato_mant_corr_modal_validar_correo_area();
  });


   // SELECT2 AJAX
  $("#modal_mant_corr_usuario_id").select2({

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
    placeholder: "Seleccionar un responsable de Área",
    minimumInputLength: 2,
    language: {
      inputTooShort: function () {
        return 'Por favor ingrese 2 o más caracteres';
      }
    }
  });

  $("#modal_mant_area_grupo_id").select2({

    ajax: {
      url: "sys/router/contrato-mantenimiento/index.php",
      type: "POST",
      data: function (params) {
        var query = {
          search: params.term,
          action: "correo/obtener_area_grupo_id",
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
    placeholder: "Seleccionar un grupo de Área"
  });
}

function sec_contrato_mant_corr_tabs(){
  $('#tab_mantenimiento a:first').tab('show');

  $('#tab_mantenimiento a').click(function (e) {
      e.preventDefault();
      $(this).tab('show');
  });
}

function sec_contrato_mant_corr_met_obtener_datos(action, select){
	let me = this;
	let url = 'sys/router/contrato-mantenimiento/index.php';
	let data = {
		action : action
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
      $(select).find('option').remove().end();
      $(select).append('<option value="0">- Seleccione -</option>');
      if (respuesta.status == 200) {
        $(respuesta.result).each(function(i,e){
          opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
          $(select).append(opcion);	
        })
      }
    },
    error: function (resp, status) {},
  });
}

function sec_contrato_mant_met_listar_metodo() {
  
  var edicion_metodo_correo = $('#edicion_metodo_correo').val();
  let data = {
    action:'correo_metodo/listar',
    edicion_metodo_correo : edicion_metodo_correo,
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
        sec_contrato_mant_met_render_table_corro_metodo(respuesta.result);
      }
    },
    error: function (resp, status) {},
  });

}

function sec_contrato_mant_met_render_table_corro_metodo(data = []) {
	$("#tbl_correo_metodo")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'asc']],
		pageLength: 25,
		columns: [
		  { data: "index", className: "text-center" },
		  { data: "tipo_contrato", className: "text-left" }, 
		  { data: "nombre", className: "text-left" },
		  { data: "metodo", className: "text-left" },
		  { data: "status", className: "text-center" },
		  { data: "acciones", className: "text-center" },
		],
		language: {
		  decimal: "",
		  emptyTable: "Tabla vacia",
		  info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
		  infoEmpty: "Mostrando 0 a 0 de 0 entradas",
		  infoFiltered: "(Filtrado de _MAX_ total entradas)",
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

function sec_contrato_mant_corr_met_validar_metodo() {

  var id = $('#cont_mant_corr_met_id').val();
  var tipo_metodo_id = $('#cont_mant_corr_met_tipo_correo_metodo_id').val();
  var nombre = $('#cont_mant_corr_met_nombre').val();
  var metodo = $('#cont_mant_corr_met_metodo').val();
  var status = $('#cont_mant_corr_met_status').val();

  if (parseInt(tipo_metodo_id) == 0) {
    alertify.error("Seleccione un tipo de contrato", 5);
    $('#cont_mant_corr_met_tipo_correo_metodo_id').focus();
    return false;
  }

  if (nombre.length == 0) {
    alertify.error("Ingrese un nombre", 5);
    $('#cont_mant_corr_met_nombre').focus();
    return false;
  }

  if (metodo.length == 0) {
    alertify.error("Ingrese un metodo", 5);
    $('#cont_mant_corr_met_metodo').focus();
    return false;
  }

  if (status.length == 0) {
    alertify.error("Seleccione un estado", 5);
    $('#cont_mant_corr_met_status').focus();
    return false;
  }

  var title = '';
  if(id.length == 0){
    title = "¿Esta seguro de registrar el método?";
  }else{
    title = "¿Esta seguro de modificar el método?";
  }

  swal({
    title: title,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: "Sí, ¡estoy de acuerdo!",
    cancelButtonText: "No, cancelar",
    closeOnConfirm: true,
    closeOnCancel: true,

  },function (isConfirm) {
    if (isConfirm) {
      sec_contrato_mant_met_guardar_metodo();
    } 
  });

}

function sec_contrato_mant_met_guardar_metodo() {
  
  var id = $('#cont_mant_corr_met_id').val();
  var tipo_metodo_id = $('#cont_mant_corr_met_tipo_correo_metodo_id').val();
  var nombre = $('#cont_mant_corr_met_nombre').val();
  var metodo = $('#cont_mant_corr_met_metodo').val();
  var status = $('#cont_mant_corr_met_status').val();

  var action = 'correo_metodo/registrar';
  if (id.length > 0) {
    action = 'correo_metodo/modificar';
  }

  var data = {
    id: id,
    tipo_metodo_id:tipo_metodo_id,
    nombre:nombre,
    metodo:metodo,
    status:status,
    action:action,
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
        alertify.success(respuesta.message, 5);
        sec_contrato_mant_met_frm_reset();
        sec_contrato_mant_met_listar_metodo();
      }
    },
    error: function (resp, status) {},
  });

}

function sec_contrato_mant_met_frm_reset() {
  $("#cont_mant_corr_met_id").val("");
  $('#cont_mant_corr_met_tipo_correo_metodo_id').val('0').trigger('change.select2');
  $('#cont_mant_corr_met_nombre').val("");
  $('#cont_mant_corr_met_metodo').val("");
  $('#cont_mant_corr_met_status').val("1");
  $('#btn-form-registro-correo-metodo-registrar').html('Registrar');
}

function sec_contrato_mant_met_obtener_por_id(id) {
  sec_contrato_mant_met_frm_reset();
  let data = {
    id : id,
    action:'correo_metodo/obtener_por_id',
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
        $("#cont_mant_corr_met_id").val(respuesta.result.id);
        $('#cont_mant_corr_met_tipo_correo_metodo_id').val(respuesta.result.tipo_metodo_id).trigger('change.select2');
        $('#cont_mant_corr_met_nombre').val(respuesta.result.nombre);
        $('#cont_mant_corr_met_metodo').val(respuesta.result.metodo);
        $('#cont_mant_corr_met_status').val(respuesta.result.status);
        $('#btn-form-registro-correo-metodo-registrar').html('Modificar');
      
      }
    },
    error: function (resp, status) {},
  });

}

function sec_contrato_mant_met_eliminar_por_id(id) {

  swal({
    title: '¿Esta seguro de eliminar el registro?',
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: "Sí, ¡estoy de acuerdo!",
    cancelButtonText: "No, cancelar",
    closeOnConfirm: true,
    closeOnCancel: true,

  },function (isConfirm) {
    if (isConfirm) {
      let data = {
        id : id,
        action:'correo_metodo/eliminar_por_id',
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
            alertify.success(respuesta.message, 5);
            sec_contrato_mant_met_listar_metodo();                
          }
        },
        error: function (resp, status) {},
      });
    } 
  });

  

}

function sec_con_mant_not_cont_eliminar_por_area_id(id) {

  swal({
    title: '¿Está seguro de eliminar el registro?',
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: "Sí, ¡estoy de acuerdo!",
    cancelButtonText: "No, cancelar",
    closeOnConfirm: true,
    closeOnCancel: true,

  },function (isConfirm) {
    if (isConfirm) {
      let data = {
        id : id,
        action:'correo/eliminar_por_area_id',
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
            alertify.success(respuesta.message, 5);
            sec_contrato_mant_corr_modal_listar_correos_por_area();                
          }
        },
        error: function (resp, status) {},
      });
    } 
  });

  

}

///modal
function sec_contrato_mant_met_modal_correos(id_metodo) {
  sec_contrato_mant_corr_modal_frm_reset();
  let data = {
    id : id_metodo,
    action:'correo_metodo/obtener_por_id',
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
        $('#modalMantemientoCorreos').modal('show');
        $('#modal_title_mantenimiento_correo').html((respuesta.result.tipo_contrato+' - '+ respuesta.result.nombre).toUpperCase());
        $('#modal_mant_corr_metodo_id').val(respuesta.result.id);
        sec_contrato_mant_corr_modal_listar_correos();
        sec_contrato_mant_corr_modal_listar_correos_por_area();
      }
    },
    error: function (resp, status) {},
  });
}

function sec_contrato_mant_corr_modal_frm_reset() {
  $("#modal_mant_corr_id").val("");
  $('#modal_mant_corr_usuario_id').val('0').trigger('change.select2');
  //$('#cont_mant_corr_status').val("1");
}

function sec_contrato_mant_corr_modal_frm_reset_area() {
  $("#modal_mant_corr_id").val("");
  $('#modal_mant_area_grupo_id').val('0').trigger('change.select2');
}

function sec_contrato_mant_corr_modal_validar_correo() {

  var id = $('#modal_mant_corr_id').val();
  var usuario_id = $('#modal_mant_corr_usuario_id').val();
  var metodo_id = $('#modal_mant_corr_metodo_id').val();


  if (usuario_id == null) {
    alertify.error("Seleccione un usuario", 5);
    $('#modal_mant_corr_usuario_id').focus();
    return false;
  }
  if (metodo_id.length == 0) {
    alertify.error("Seleccione un metodo", 5);
    $('#modal_mant_corr_metodo_id').focus();
    return false;
  }

  var title = 'Está seguro de registrar el usuario?';
  
  swal({
    title: title,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: "Sí, ¡estoy de acuerdo!",
    cancelButtonText: "No, cancelar",
    closeOnConfirm: true,
    closeOnCancel: true,

  },function (isConfirm) {
    if (isConfirm) {
      sec_contrato_mant_corr_modal_guardar_correo();
    } 
  });

}

function sec_contrato_mant_corr_modal_validar_correo_area(){
  
  var area_id = $('#modal_mant_area_grupo_id').val();
  var metodo_id = $('#modal_mant_corr_metodo_id').val();


  if (area_id == null) {
    alertify.error("Seleccione un área", 5);
    $('#modal_mant_area_grupo_id').focus();
    return false;
  }
  if (metodo_id.length == 0) {
    alertify.error("Seleccione un metodo", 5);
    $('#modal_mant_corr_metodo_id').focus();
    return false;
  }

  var title = '¿Está seguro de registrar el área?';
  
  swal({
    title: title,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: "Sí, ¡estoy seguro!",
    cancelButtonText: "No, cancelar",
    closeOnConfirm: true,
    closeOnCancel: true,

  },function (isConfirm) {
    if (isConfirm) {
      sec_contrato_mant_corr_modal_guardar_correo_area();
    } 
  });
}


function sec_contrato_mant_corr_modal_guardar_correo_area() {
  
  var id = $('#modal_mant_corr_id').val();
  var area_id = $('#modal_mant_area_grupo_id').val();
  var metodo_id = $('#modal_mant_corr_metodo_id').val();
  var status = 1;
  var action = 'correo/registrar_area';

  var data = {
    id: id,
    area_id:area_id,
    metodo_id:metodo_id,
    status:status,
    action:action,
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
        alertify.success(respuesta.message, 5);
        sec_contrato_mant_corr_modal_frm_reset_area();
        sec_contrato_mant_corr_modal_listar_correos_por_area();
      }else{
        alertify.error(respuesta.message, 5);
      }
    },
    error: function (resp, status) {},
  });

}

function sec_contrato_mant_corr_modal_guardar_correo() {
  
  var id = $('#modal_mant_corr_id').val();
  var usuario_id = $('#modal_mant_corr_usuario_id').val();
  var metodo_id = $('#modal_mant_corr_metodo_id').val();
  var status = 1;
  var action = 'correo/registrar';

  var data = {
    id: id,
    usuario_id:usuario_id,
    metodo_id:metodo_id,
    status:status,
    action:action,
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
        alertify.success(respuesta.message, 5);
        sec_contrato_mant_corr_modal_frm_reset();
        sec_contrato_mant_corr_modal_listar_correos();
      }else{
        alertify.error(respuesta.message, 5);
      }
    },
    error: function (resp, status) {},
  });

}

function sec_contrato_mant_corr_modal_listar_correos() {
  
  var metodo_id = $('#modal_mant_corr_metodo_id').val();
  let data = {
    metodo_id: metodo_id,
    action:'correo/listar',
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
        sec_contrato_mant_corr_modal_render_table_correos(respuesta.result);
      }
    },
    error: function (resp, status) {},
  });

}

function sec_contrato_mant_corr_modal_listar_correos_por_area(){
  var metodo_id = $('#modal_mant_corr_metodo_id').val();
  
  var data = {
      metodo_id: metodo_id,
      action:'correo/listar_por_area'
  };

  $("#correo_por_area_div_tabla").show();

  var columnDefs = [{
      className: 'text-center',
      targets: [0, 1, 2, 3]
  }];

  var tabla = crearDataTable(
      "#correo_por_area_datatable",
      "sys/router/contrato-mantenimiento/index.php",
      data,
      columnDefs
  );

}

function sec_contrato_mant_corr_modal_render_table_correos(data = []) {
	$("#tbl_modal_correo")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		order: [[0, 'asc']],
		pageLength: 25,
		columns: [
		  { data: "index", className: "text-center" },
		  { data: "personal", className: "text-left" }, 
		  { data: "usuario", className: "text-left" },
		  { data: "correo", className: "text-left" },
		  { data: "status", className: "text-center" },
		  { data: "acciones", className: "text-center" },
		],
		language: {
		  decimal: "",
		  emptyTable: "Tabla vacia",
		  info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
		  infoEmpty: "Mostrando 0 a 0 de 0 entradas",
		  infoFiltered: "(Filtrado de _MAX_ total entradas)",
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

function sec_contrato_mant_corr_modal_cambiar_estado(id, estado) {

  var title = '';
  if (estado == 0) {
    title = '¿Está seguro de inactivar el correo?';
  }else if(estado == 1){
    title = '¿Está seguro de activar el correo?';
  }
  swal({
    title: title,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: "Sí, ¡estoy de acuerdo!",
    cancelButtonText: "No, cancelar",
    closeOnConfirm: true,
    closeOnCancel: true,

  },function (isConfirm) {
    if (isConfirm) {
      let data = {
        id : id,
        estado: estado,
        action:'correo/cambiar_estado',
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
            alertify.success(respuesta.message, 5);
            sec_contrato_mant_corr_modal_listar_correos();                
          }
        },
        error: function (resp, status) {},
      });
    } 
  });

  

}


function sec_contrato_mant_corr_modal_cambiar_estado_por_area(id, estado) {

  var title = '';
  if (estado == 0) {
    title = '¿Está seguro de inactivar el correo?';
  }else if(estado == 1){
    title = '¿Está seguro de activar el correo?';
  }
  swal({
    title: title,
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: "Sí, ¡estoy de acuerdo!",
    cancelButtonText: "No, cancelar",
    closeOnConfirm: true,
    closeOnCancel: true,

  },function (isConfirm) {
    if (isConfirm) {
      let data = {
        id : id,
        estado: estado,
        action:'correo/cambiar_estado_por_area',
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
            alertify.success(respuesta.message, 5);
            sec_contrato_mant_corr_modal_listar_correos_por_area();                
          }
        },
        error: function (resp, status) {},
      });
    } 
  });

  

}

function sec_contrato_mant_corr_modal_eliminar_por_id(id) {

  swal({
    title: '¿Esta seguro de eliminar el registro?',
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: "Sí, ¡estoy de acuerdo!",
    cancelButtonText: "No, cancelar",
    closeOnConfirm: true,
    closeOnCancel: true,

  },function (isConfirm) {
    if (isConfirm) {
      let data = {
        id : id,
        action:'correo/eliminar_por_id',
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
            alertify.success(respuesta.message, 5);
            sec_contrato_mant_corr_modal_listar_correos();                
          }
        },
        error: function (resp, status) {},
      });
    } 
  });

  

}

function sec_con_mant_not_cont_ver_area(area_id){
  

  var data = {
    area_id: area_id,
    action:'correo/listar_por_usuarios_por_area'
  };

  $("#usuarios_registrados_div_tabla").show();

  var columnDefs = [{
      className: 'text-center',
      targets: [0, 1, 2, 3]
  }];

  var tabla = crearDataTable(
      "#usuarios_registrados_datatable",
      "sys/router/contrato-mantenimiento/index.php",
      data,
      columnDefs
  );

  $('#usuarios_registrados_datatable').on('init.dt', function (e, settings, json) {
    if (json.area) {
        console.log(json.area);
        $('#modal_title_mantenimiento_usuarios_por_area').text("Usuarios Registrados - Área: " + json.area);
    } else {
        $('#modal_title_mantenimiento_usuarios_por_area').text("Usuarios Registrados");
    }
});

  $('#modalMantemientoCorreos').modal('hide');
  $('#modalMantemientoCorreosUsuariosPorArea').modal('show');
}

function sec_con_mant_not_cont_cerrar_area(){
  
  $('#closeModalUsuariosPorArea').on('click', function() {
    $('#modalMantemientoCorreosUsuariosPorArea').modal('hide');
    $('#modalMantemientoCorreos').modal('show');
  });

  $('#modalMantemientoCorreosUsuariosPorArea').on('hidden.bs.modal', function () {
    $('#modalMantemientoCorreos').modal('show');
  });

}

function sec_con_mant_not_ajustar_dataTable(){
  
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("href");
    if (target === "#tab-area") {
        if ($.fn.DataTable.isDataTable("#correo_por_area_datatable")) {
            $('#correo_por_area_datatable').DataTable().columns.adjust().draw();
        }
    }
  });

}