function sec_contrato_mantenimiento_notificacion_contrato() {  

    sec_contrato_mantenimiento_notificacion_contrato_listar();
     // SELECT2 AJAX
    $("#modal_mant_not_cont_usuario_id").select2({
        ajax: {
            url: "sys/router/contrato-mantenimiento/index.php",
            type: "POST",
            data: function (params) {
                var query = {
                search: params.term,
                action: "notificacion_contrato/obtener_usuarios",
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
        placeholder: "Ingrese un usuario",
            language: {
            noResults: function () {
                return 'Ingrese un usuario, si no se encuentra comuniquesé con Soporte para su registro.';
            },
            searching: function () {
                return 'Buscando...';
            },
            errorLoading: function () {
                return 'No se pudo cargar el resultado';
            }
        }
    });

    $("#modal_mant_not_cont_usuario_id").on('select2:select', function (e) {
      var usuarioId = e.params.data.id;
  
      // Realizar la solicitud para obtener el área del usuario seleccionado
      $.ajax({
          url: 'sys/router/contrato-mantenimiento/index.php',
          type: 'POST',
          data: {
              action: 'notificacion_contrato/obtener_area_por_usuario',
              usuario_id: usuarioId
          },
          success: function (response) {
              var data = JSON.parse(response);
  
              if (data.status === 200) {
                  // Vaciar el campo de áreas antes de agregar el nuevo valor
                  $("#modal_mant_not_cont_area_id").empty();
  
                  // Crear una nueva opción y seleccionarla
                  var areaOption = new Option(data.result.nombre, data.result.id, true, true);
                  $('#modal_mant_not_cont_area_id').append(areaOption).trigger('change');
              } else {
                  // Manejo de errores
                  alertify.error(data.message || "Ocurrió un error al obtener el área.");
              }
          }
      });
    });

    $("#modal_mant_not_cont_area_id").select2({
  
      ajax: {
          url: "sys/router/contrato-mantenimiento/index.php",
          type: "POST",
          data: function (params) {
              var query = {
              search: params.term,
              action: "notificacion_contrato/obtener_areas",
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
      placeholder: "Ingrese un área",
      language: {
        noResults: function () {
            return 'Ingrese un área.';
        },
        searching: function () {
            return 'Buscando...';
        },
        errorLoading: function () {
            return 'No se pudo cargar el resultado';
        }
      }
    });

    $("#Frm_RegistroNotificacionContrato").submit(function (e) {
        e.preventDefault();
        var usuario_id = $("#modal_mant_not_cont_usuario_id").val();
        var area_id = $("#modal_mant_not_cont_area_id").val();
        if (usuario_id == null) {
          alertify.warning("Seleccione un usuario.", 5);
          return;
        }
        if (area_id == null) {
          alertify.warning("Seleccione una área.", 5);
          return;
        }
        sec_con_mant_not_contrato_guardar();
    });
}


function sec_con_mant_not_contrato_guardar() {

  swal({
    title: 'Este usuario se incluirá en todos los envíos de correo de contratos. ¿Está seguro?',
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: "Sí, ¡estoy seguro!",
    cancelButtonText: "No, cancelar",
    closeOnConfirm: true,
    closeOnCancel: true,

  },function (isConfirm) {
    if (isConfirm) {
    
      var usuario_id = $('#modal_mant_not_cont_usuario_id').val();
      var area_id = $('#modal_mant_not_cont_area_id').val();

      var action = 'notificacion_contrato/registrar';

      var data = {
          usuario_id: usuario_id,
          area_id: area_id,
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
                limpiarFormulario();
                sec_contrato_mantenimiento_notificacion_contrato_listar();
            } else if (respuesta.status == 400) {
              alertify.error(respuesta.message || "Ocurrió un error al procesar la solicitud.", 5);
            }
          },
          error: function (resp, status) {},
      });
    }
    
  });

}

function sec_contrato_mantenimiento_notificacion_contrato_listar(){

    var data = {
        action: "notificacion_contrato/listar"
    };
    $("#notificacion_contrato_div_tabla").show();

    var columnDefs = [{
        className: 'text-center',
        targets: [0, 1, 2, 3, 4, 5, 6, 7]
    }];

    var tabla = crearDataTable(
        "#notificacion_contrato_datatable",
        "sys/router/contrato-mantenimiento/index.php",
        data,
        columnDefs
    );

}


function sec_contrato_mant_not_cont_cambiar_estado(id, estado) {

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
          action:'notificacion_contrato/cambiar_estado',
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
                sec_contrato_mantenimiento_notificacion_contrato_listar();           
              } else if (respuesta.status == 400) {
                alertify.error(respuesta.message || "Ocurrió un error al procesar la solicitud.", 5);
              }
          },
          error: function (resp, status) {},
        });
      } 
    });
  }

  function sec_con_mant_not_cont_editar(id) {

    $('.btn-form-registro-correo-metodo-registrar').hide();
    $('#modal_mant_not_cont_usuario_id').prop('disabled', true);

    let url = 'sys/router/contrato-mantenimiento/index.php';

    $.ajax({
        url: url,
        type: "POST",
        data: {
            action: 'notificacion_contrato/obtener_por_id',
            id: id
        },
        success: function (resp) {
            var data = JSON.parse(resp);
            if (data.status == 200) {
                // Aseguramos que el valor esté disponible en select2 antes de asignarlo
                let usuarioOption = new Option(data.result.usuario_text, data.result.usuario_id, true, true);
                $('#modal_mant_not_cont_usuario_id').append(usuarioOption).trigger('change');
                
                let areaOption = new Option(data.result.area_text, data.result.area_id, true, true);
                $('#modal_mant_not_cont_area_id').append(areaOption).trigger('change');

                $('#cont_mant_corr_met_id').val(id);
                $('.btn-form-correo-metodo-modificar').show();
                $('.btn-form-correo-metodo-cancelar').show();
            } else {
                alertify.error(data.message || "Ocurrió un error al procesar la solicitud.", 5);
            }
        }
    });
  }

  $('#btn-form-correo-metodo-modificar').click(function () {
    var id = $('#cont_mant_corr_met_id').val();
    var area_id = $('#modal_mant_not_cont_area_id').val();

    let url = 'sys/router/contrato-mantenimiento/index.php';

    $.ajax({
        url: url,
        type: "POST",
        data: {
            action: 'notificacion_contrato/modificar',
            id: id,
            area_id: area_id
        },
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
                limpiarFormulario();
                sec_contrato_mantenimiento_notificacion_contrato_listar();
            } else {
                alertify.error(respuesta.message || "Ocurrió un error al procesar la solicitud.", 5);
            }
        },
        error: function (resp, status) {},
    });
  });

  $('.btn-form-correo-metodo-cancelar').click(function () {
    limpiarFormulario();
  });

  function limpiarFormulario() {
    $('#modal_mant_not_cont_usuario_id').val(null).trigger('change');
    $('#modal_mant_not_cont_area_id').val(null).trigger('change');
    $('#cont_mant_corr_met_id').val('');
    $('.btn-form-registro-correo-metodo-registrar').show();
    $('.btn-form-correo-metodo-modificar').hide();
    $('.btn-form-correo-metodo-cancelar').hide();
    $('#modal_mant_not_cont_usuario_id').prop('disabled', false);
  }

  function sec_con_mant_not_cont_ver_historial(id){

    $('#modalNotificacionHistoricoContrato').modal('show');

    var data = {
        action: "notificacion_contrato/listar_historial",
        parametro_general_id: id
    };
    $("#notificacion_contrato_historial_div_tabla").show();

    var columnDefs = [{
        className: 'text-center',
        targets: [0, 1, 2, 3, 4]
    }];

    var tabla = crearDataTable(
        "#notificacion_contrato_historial_datatable",
        "sys/router/contrato-mantenimiento/index.php",
        data,
        columnDefs
    );

    tabla.on('init.dt', function () {
        $('.dataTables_filter').hide();
    });
  }


  
function sec_con_mant_not_cont_eliminar_notificacion_por_area_id(id) {

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
        action:'notificacion_contrato/eliminar_notificacion_por_area_id',
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
            sec_contrato_mantenimiento_notificacion_contrato_listar();                
          }
        },
        error: function (resp, status) {},
      });
    } 
  });

  

}



  

  
  
  
  
  
  
  