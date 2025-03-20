function sec_contrato_mantenimiento_correo_cargo() {
    sec_contrato_mant_corr_met_obtener_datos_cargo("obtener_area_id","[name='cont_mant_corr_area_id']");
    sec_contrato_mant_met_listar_metodo_cargo();
    sec_contrato_mant_met_frm_reset_cargo();
    $("#Frm_RegistroCorreoMetodo").submit(function (e) {
      e.preventDefault();
      sec_contrato_mant_corr_met_validar_metodo();
    });
  
    $("#Frm_RegistroCargo").submit(function (e) {
      e.preventDefault();
      sec_contrato_mant_corr_modal_validar_correo_cargo();
    });
    
  
     // SELECT2 AJAX
     $("#modal_mant_cargo_usuario_id").select2({
      ajax: {
        url: "sys/router/contrato-mantenimiento/index.php",
        type: "POST",
        data: function (params) {
          var query = {
            cargo_id: $('#modal_mant_cargo_cargo_id').val(),
            area_id: $('#modal_mant_cargo_area_id').val(),
            action: "cargo/obtener_usuarios",
          };
          return query;
        },
        processResults: function (info) {
          var data = JSON.parse(info);
          return {
            results: $.map(data.result, function (item) {
              return {
                text: item.text,
                id: item.usuario_id,
              };
            }),
          };
        },
      },
      width: "100%",
      placeholder: "Seleccionar un responsable de Área",
      minimumInputLength: 0, // Permitir búsqueda con cualquier cantidad de caracteres
      language: {
        inputTooShort: function () {
          return 'Por favor ingrese 2 o más caracteres';
        }
      }
    });
    
  
      $("#modal_mant_cargo_cargo_id").select2({
        ajax: {
          url: "sys/router/contrato-mantenimiento/index.php",
          type: "POST",
          data: function (params) {
            var query = {
              // search: params.term,
              area_id : $('#modal_mant_cargo_metodo_id').val(),
              action: "cargo/obtener_cargos",
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
        placeholder: "Seleccion un cargo",
        // minimumInputLength: 2,
        // language: {
        //   inputTooShort: function () {
        //     return 'Por favor ingrese 2 o mas caracteres';
        //   }
        // }
      });
      $("#modal_mant_cargo_area_id").select2({
  
        ajax: {
          url: "sys/router/contrato-mantenimiento/index.php",
          type: "POST",
          data: function (params) {
            var query = {
              search: params.term,
              action: "cargo/obtener_areas",
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
        placeholder: "Seleccion un cargo",
        minimumInputLength: 2,
        language: {
          inputTooShort: function () {
            return 'Por favor ingrese 2 o más caracteres';
          }
        }
      });


      $("#modal_usuario_correo_area_id").select2({
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
        placeholder: "Seleccion un personal",
        minimumInputLength: 2,
        language: {
          inputTooShort: function () {
            return 'Por favor ingrese 2 o más caracteres';
          }
        }
      });
  }
  
  function sec_contrato_mant_corr_met_obtener_datos_cargo(action, select){
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
  
  function sec_contrato_mant_met_listar_metodo_cargo() {
    var edicion_metodo_correo = $('#edicion_metodo_correo').val();
    let data = {
      action:'cargo/listar_areas',
      // edicion_metodo_correo : edicion_metodo_correo,
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
        // console.log(respuesta)
        if (respuesta.status == 200) {
          sec_contrato_mant_met_render_table_corro_cargo(respuesta.result);
        }
      },
      error: function (resp, status) {},
    });
  
  }
  
  function sec_contrato_mant_met_render_table_corro_cargo(data = []) {
    $("#tbl_correo_cargo")
        .dataTable({
          bDestroy: true,
          data: data,
          responsive: true,
          order: [[0, 'asc']],
          pageLength: 25,
          columns: [
            { data: "index", className: "text-center" },
            { data: "nombre", className: "text-left" }, 
            // { data: "tipo_contrato", className: "text-left" }, 
            // { data: "nombre", className: "text-left" },
            // { data: "metodo", className: "text-left" },
            
            // { data: "status", className: "text-center" },
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
      title = "¿Está seguro de registrar el método?";
    }else{
      title = "¿Está seguro de modificar el método?";
    }
  
    swal({
      title: title,
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      confirmButtonText: " Sí, ¡estoy de acuerdo!",
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
          sec_contrato_mant_met_frm_reset_cargo();
          sec_contrato_mant_met_listar_metodo();
        }
      },
      error: function (resp, status) {},
    });
  
  }
  
  function sec_contrato_mant_met_frm_reset_cargo() {
    $("#cont_mant_corr_met_id").val("");
    $('#cont_mant_corr_met_tipo_correo_metodo_id').val('0').trigger('change.select2');
    $('#cont_mant_corr_met_nombre').val("");
    $('#cont_mant_corr_met_metodo').val("");
    $('#cont_mant_corr_met_status').val("1");
    $('#btn-form-registro-correo-metodo-registrar').html('Registrar');
  }
  
  function sec_contrato_mant_met_obtener_por_id(id) {
    sec_contrato_mant_met_frm_reset_cargo();
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
  
  ///modal
  function sec_contrato_mant_met_modal_correos_cargos(id_metodo) {
    sec_contrato_mant_corr_modal_frm_reset_cargo();
    let data = {
      id : id_metodo,
      action:'cargo_metodo/obtener_por_id',
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
        // console.log(respuesta)
        if (respuesta.status == 200) {
          $('#modalMantemientoCargo').modal('show');
          $('#modal_title_mantenimiento_cargo').html(('Área : '+respuesta.result.nombre).toUpperCase());
          $('#modal_mant_cargo_metodo_id').val(respuesta.result.id);
          sec_contrato_mant_corr_modal_listar_correos_cargos();
        }
      },
      error: function (resp, status) {},
    });
  }
  // historial
  function sec_contrato_mant_met_modal_correos_cargos_historial(id_area) {
    // sec_contrato_mant_corr_modal_frm_reset_cargo();
    let data = {
      id : id_area,
      action:'cargo_metodo/obtener_por_id',
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
        // console.log(respuesta)
        if (respuesta.status == 200) {
          $('#modalMantemientoCargo_historial').modal('show');
          $('#modal_title_mantenimiento_cargo_historial').html(('Área : '+respuesta.result.nombre).toUpperCase());
          $('#modal_mant_cargo_metodo_id_historial').val(respuesta.result.id);
          sec_contrato_mant_corr_modal_listar_correos_cargos_historial();
        }
      },
      error: function (resp, status) {},
    });
  }
  
  function sec_contrato_mant_corr_modal_frm_reset_cargo() {
    $("#modal_mant_cargo_id").val("");
    $('#modal_mant_cargo_cargo_id').val('0').trigger('change.select2');
    $('#modal_mant_cargo_area_id').val('0').trigger('change.select2');
    //$('#cont_mant_corr_status').val("1");
  }
  
  function sec_contrato_mant_corr_modal_validar_correo_cargo() {
  
    var id = $('#modal_mant_cargo_id').val();
    // var usuario_id = $('#modal_mant_cargo_cargo_id').val();
    var usuario_id = $('#modal_mant_cargo_usuario_id').val();
    var cargo_id = $('#modal_mant_cargo_cargo_id').val();
    // var area_id = $('#modal_mant_cargo_area_id').val();
    var metodo_id = $('#modal_mant_cargo_metodo_id').val();
  
  
    // if (usuario_id == null) {
    //   alertify.error("Seleccione un usuario", 5);
    //   $('#modal_mant_cargo_cargo_id').focus();
    //   return false;
    // }
    if (cargo_id == null) {
      alertify.error("Seleccione un cargo", 5);
      $('#modal_mant_cargo_cargo_id').focus();
      return false;
    }
    // if (area_id == null) {
    //   alertify.error("Seleccione un area", 5);
    //   $('#modal_mant_cargo_area_id').focus();
    //   return false;
    // }
    // if (metodo_id.length == 0) {
    //   alertify.error("Seleccione un metodo", 5);
    //   $('#modal_mant_cargo_metodo_id').focus();
    //   return false;
    // }
  
    var title = '¿Está seguro de registrar el cargo?';
    
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
        sec_contrato_mant_corr_modal_guardar_cargo();
      } 
    });
  
  }
  
  function sec_contrato_mant_corr_modal_guardar_cargo() {
    
    // var id = $('#modal_mant_cargo_id').val();
    var cargo_id = $('#modal_mant_cargo_cargo_id').val();
    // var area_id = $('#modal_mant_cargo_area_id').val();
    var area_id = $('#modal_mant_cargo_metodo_id').val();
    // var usuario_id = $('#modal_mant_cargo_usuario_id').val();
    // var metodo_id = $('#modal_mant_cargo_metodo_id').val();
    var status = 1;
    var action = 'cargo/registrar';
  
    var data = {
      // id: id,
      // usuario_id:usuario_id,
      area_id:area_id,
      cargo_id:cargo_id,
      // metodo_id:metodo_id,
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
          sec_contrato_mant_corr_modal_frm_reset_cargo();
          sec_contrato_mant_corr_modal_listar_correos_cargos();
        }else{
          alertify.error(respuesta.message, 5);
        }
      },
      error: function (resp, status) {},
    });
  
  }
  
  function sec_contrato_mant_corr_modal_listar_correos_cargos() {
    var metodo_id = $('#modal_mant_cargo_metodo_id').val();
    if($('#modal_mant_cargo_metodo_id_sin_permiso').val() ==  -1){
        $('#modalMantemientoCargo').modal('hide');
       
        swal({
            title: "No tienes permisos para agregar Cargos en Áreas",
            type: "info",
            confirmButtonColor: '#3085d6',
            confirmButtonText: "OK",
        });
        
    }else{
        let data = {
            metodo_id: metodo_id,
            action:'cargo/listar',
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
            //   console.log(respuesta)
              if (respuesta.status == 200) {
                sec_contrato_mant_corr_modal_render_table_cargos(respuesta.result);
              }
            },
            error: function (resp, status) {},
          });
    }
   
  
  }
  
  function sec_contrato_mant_corr_modal_render_table_cargos(data = []) {
      $("#tbl_modal_cargo")
        .dataTable({
          bDestroy: true,
          data: data,
          responsive: true,
          order: [[0, 'asc']],
          pageLength: 25,
          columns: [
            { data: "index", className: "text-center" },
            // { data: "personal", className: "text-left" }, 
            // { data: "usuario", className: "text-left" },
            // { data: "correo", className: "text-left" },
            { data: "cargo", className: "text-center" },
            { data: "usuario", className: "text-center" },
            { data: "fecha_creacion", className: "text-center", render: function (data, type, row) {
                    // Formatear la fecha si es tipo de renderización o visualización
                    if (type === 'display' || type === 'filter') {
                        var fecha = new Date(data);
                        var dia = fecha.getDate().toString().padStart(2, '0');
                        var mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
                        var anio = fecha.getFullYear();
                        var hora = fecha.getHours().toString().padStart(2, '0');
                        var minutos = fecha.getMinutes().toString().padStart(2, '0');
                        var segundos = fecha.getSeconds().toString().padStart(2, '0');
                        return dia + '/' + mes + '/' + anio + ' ' + hora + ':' + minutos + ':' + segundos;
                    }
                    return data; // Devuelve la fecha sin formato para otras operaciones
                } 
            },
            // { data: "area", className: "text-left" },
            { data: "status", className: "text-center" },
            // { data: "acciones", className: "text-center" },
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
  
  function sec_contrato_mant_corr_modal_cambiar_estado_cargo(id, estado) {
    var area_id = $('#modal_mant_cargo_metodo_id').val();
    var title = '';
    if (estado == 0) {
      title = '¿Está seguro de inactivar el Cargo?';
    }else if(estado == 1){
      title = '¿Está seguro de activar el Cargo?';
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
          area_id: area_id,
          estado: estado,
          action:'cargo/cambiar_estado',
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
            // console.log(respuesta);
            if (respuesta.status == 200) {
              alertify.success(respuesta.message, 5);
              sec_contrato_mant_corr_modal_render_table_cargos(respuesta.result);                
            }
          },
          error: function (resp, status) {},
        });
      } 
    });
  
    
  
  }
  
  function sec_contrato_mant_corr_modal_eliminar_por_id(id) {
  
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
  
  function sec_contrato_mant_corr_modal_listar_correos_cargos_historial() {
    var id_area = $('#modal_mant_cargo_metodo_id_historial').val();
    let data = {
      id_area: id_area,
      action:'cargo_historial/listar_historial',
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
        // console.log(respuesta)
        if (respuesta.status == 200) {
          sec_contrato_mant_corr_modal_render_table_cargos_historial(respuesta.result);
        }
      },
      error: function (resp, status) {},
    });
  
  }
  function sec_contrato_mant_corr_modal_render_table_cargos_historial(data = []) {
      $("#tbl_modal_cargo_historial")
        .dataTable({
          bDestroy: true,
          data: data,
          responsive: true,
          order: [[0, 'asc']],
          pageLength: 25,
          columns: [
            { data: "index", className: "text-center" },
            // { data: "personal", className: "text-left" }, 
            // { data: "usuario", className: "text-left" },
            // { data: "correo", className: "text-left" },
            { data: "cargo", className: "text-center" },
            { data: "usuario", className: "text-center" },
            { data: "fecha_creacion", className: "text-center" ,
                render: function (data, type, row) {
                    // Formatear la fecha si es tipo de renderización o visualización
                    if (type === 'display' || type === 'filter') {
                        var fecha = new Date(data);
                        var dia = fecha.getDate().toString().padStart(2, '0');
                        var mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
                        var anio = fecha.getFullYear();
                        var hora = fecha.getHours().toString().padStart(2, '0');
                        var minutos = fecha.getMinutes().toString().padStart(2, '0');
                        var segundos = fecha.getSeconds().toString().padStart(2, '0');
                        return dia + '/' + mes + '/' + anio + ' ' + hora + ':' + minutos + ':' + segundos;
                    }
                    return data; // Devuelve la fecha sin formato para otras operaciones
                }
            },
            { data: "estado_historial", className: "text-center" },
            // { data: "area", className: "text-left" },
            // { data: "status", className: "text-center" },
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




  function sec_contrato_mant_met_modal_personal_area_cargo(area_id) {
    let data = {
      area_id : area_id,
      action:'area_cargo/obtener_personal',
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
          $('#modalMantemientoCargo_personal').modal('show');
          $('#modal_title_mantenimiento_cargo_personal').html(('Área : '+respuesta.result.area.nombre).toUpperCase());
          $('#modal_personal_cargo_area_id').val(respuesta.result.area.id);
          
          $("#tbl_modal_cargo_personal").dataTable({
            bDestroy: true,
            data: respuesta.result.personal,
            responsive: true,
            order: [[2, 'asc'],[0, 'asc']],
            pageLength: 25,
            columns: [
              { data: "personal", className: "text-left" },
              { data: "correo", className: "text-left" },
              { data: "nombre_cargo", className: "text-left" },
              { data: "fecha_ingreso_laboral", className: "text-center"} ,
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
            columnDefs: [
              { width: "45%",targets: 0},
              { width: "25%",targets: 1},
              { width: "15%",targets: 2},
              { width: "15%",targets: 3},
          ],
         
         
          })
          .DataTable();
         
          sec_contrato_mant_met_modal_listar_correo_area();
        }
      },
      error: function (resp, status) {},
    });
  }



  function sec_contrato_mant_met_modal_registrar_correo_area() {

    var usuario_id = $('#modal_usuario_correo_area_id').val();
    var area_id = $('#modal_personal_cargo_area_id').val();

    
    if (usuario_id == null) {
      alertify.error("Seleccione un personal", 5);
      $('#modal_usuario_correo_area_id').focus();
      return false;
    }

    let data = {
      usuario_id : usuario_id,
      area_id : area_id,
      action:'area_correo/registrar',
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
          sec_contrato_mant_met_modal_listar_correo_area();
        }else{
          alertify.error(respuesta.message, 5);
        }
      },
      error: function (resp, status) {},
    });
  }



  function sec_contrato_mant_met_modal_listar_correo_area() {

    var area_id = $('#modal_personal_cargo_area_id').val();
    let data = {
      area_id : area_id,
      action:'area_correo/listar',
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
          $("#tbl_modal_correo_area").dataTable({
            bDestroy: true,
            data: respuesta.result,
            responsive: true,
            pageLength: 25,
            columns: [
              { data: "personal", className: "text-left" },
              { data: "correo", className: "text-left" },
              { data: "cargo", className: "text-left" },
              { data: "estado", className: "text-center"} ,
              { data: "acciones", className: "text-center"} ,
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
            columnDefs: [
              { width: "45%",targets: 0},
              { width: "25%",targets: 1},
              { width: "15%",targets: 2},
              { width: "15%",targets: 3},
          ],
         
         
          })
          .DataTable();
        }
      },
      error: function (resp, status) {},
    });
  }


  function sec_contrato_mant_corr_area_eliminar(correo_id) {
    let data = {
      correo_id : correo_id,
      action:'area_correo/eliminar',
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
          alertify.success('Se ha eliminado correctamente el personal', 5);
          sec_contrato_mant_met_modal_listar_correo_area();
        }
      },
      error: function (resp, status) {},
    });
  }

  function sec_contrato_mant_corr_area_cambiar_estado(correo_id, status) {
    let data = {
      correo_id : correo_id,
      status : status,
      action:'area_correo/cambiar_estado',
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
          alertify.success('Se ha modificado correctamente el estado', 5);
          sec_contrato_mant_met_modal_listar_correo_area();
        }
      },
      error: function (resp, status) {},
    });
  }

  