function sec_comprobante_mantenimiento_proveedor() {
    sec_comp_mant_proveedor_datatable_listar_datatable();
}


//  PROVEEDORES

function sec_comp_mant_proveedor_datatable_listar_datatable() {

    if(sec_id == "comprobante" && sub_sec_id == "mantenimiento"){

    var data = {
        "accion": "comp_mantenimiento_proveedor_listar"
    }

    tabla = $("#sec_comp_mantenimiento_proveedor_listar_datatable").dataTable(
        {
            language:{
                "decimal":        "",
                "emptyTable":     "No existen registros",
                "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered":   "(filtered from _MAX_ total entradas)",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing":     "Procesando...",
                "search":         "Filtrar:",
                "zeroRecords":    "Sin resultados",
                "paginate": {
                    "first":      "Primero",
                    "last":       "Ultimo",
                    "next":       "Siguiente",
                    "previous":   "Anterior"
                },
                "aria": {
                    "sortAscending":  ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
            "aProcessing" : true,
            "aServerSide" : true,

            "ajax" :
            {
                url : "/sys/get_comprobante_mantenimiento.php",
                data : data,
                type : "POST",
                dataType : "json",
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                    loading();
                },
                error : function(e)
                {
                    console.log(e.responseText);
                }
            },
            columnDefs: [
                {
                    className: 'text-center',
                    targets: [0, 1, 2, 3, 4]
                }
            ],
            "bDestroy" : true,
            "order": [[0, 'desc']],
            aLengthMenu:[10, 20, 30, 40, 50, 100]
        }
    ).DataTable();
    }
}


function sec_comp_mant_proveedor_obtener(id_proveedor) {
  let data = {
          id : id_proveedor,
          accion:'comp_mantenimiento_proveedor_obtener'
      }

  $.ajax({
          url:  "/sys/get_comprobante_mantenimiento.php",
          type: "POST",
          data:  data,
          beforeSend: function () {
          loading("true");
          },
          complete: function () {
          loading();
          },
          success: function (resp) {
          var respuesta = JSON.parse(resp);
          if (respuesta.status == 200) {	
              
              $('#sec_comp_mant_proveedor_param_id').val(respuesta.result.id);
              $('#sec_comp_mant_proveedor_param_ruc').val(respuesta.result.ruc);
              $('#sec_comp_mant_proveedor_param_nombre').val(respuesta.result.nombre);
              $('#btn-form-registro-proveedor').html('Modificar');
              }
          else
              {
              swal({
                      title: 'Error',
                      text: respuesta.message,
                      html:true,
                      type: "warning",
                      closeOnConfirm: false,
                      showCancelButton: false
                  });
  
                  return false;
              }    
          },
          error: function (resp, status) {},
          });
  }

$("#Frm_RegistroCompMantProveedor").submit(function (e) {
  e.preventDefault();
  sec_comp_mant_proveedor_validar();
  });
function sec_comp_mant_proveedor_validar()
{
    var ruc = $('#sec_comp_mant_proveedor_param_ruc').val();
    var id_proveedor = $('#sec_comp_mant_proveedor_param_id').val();

    if (ruc.length == 0) {
        alertify.error('Ingrese el ruc', 5);
        $("#sec_comp_mant_proveedor_param_ruc").focus();
        return false;
    }else if (ruc.length < 11) {
        alertify.error('Ingrese los 11 digitos del RUC', 5);
        $("#sec_comp_mant_proveedor_param_ruc").focus();
        return false;
    }else {

    var data = {
        'accion': 'comp_mantenimiento_proveedor_verificar_ruc',
        'ruc': ruc,
        'id_proveedor': id_proveedor
    };

    $.ajax({
        url: "sys/get_comprobante_mantenimiento.php",
        data : data,
        type : "POST",
        beforeSend: function() {
        loading("true");
        },
        complete: function() {
        loading();
        },
        success: function(resp){
        var respuesta = JSON.parse(resp);
                console.log(respuesta.titulo);

            if (parseInt(respuesta.http_code) == 400) {
                sec_comp_mant_proveedor_guardar();
                }

            if (parseInt(respuesta.http_code) == 200) {
            alertify.error(respuesta.titulo,5);
            return false;
                }   		
        },
        error: function(){
        alert('failure');
        }
    });

    }

}

function sec_comp_mant_proveedor_guardar(){
  var paramProveedor_id = $('#sec_comp_mant_proveedor_param_id').val();
  var paramProveedor_ruc = $('#sec_comp_mant_proveedor_param_ruc').val();
  var paramProveedor_nombre = $('#sec_comp_mant_proveedor_param_nombre').val();


  //    VALDIACION DE DATOS

  if (paramProveedor_ruc.length == 0) {
        alertify.error('Ingrese el ruc', 5);
        $("#sec_comp_mant_proveedor_param_ruc").focus();
        return false;
    }else if (paramProveedor_ruc.length < 11) {
        alertify.error('Ingrese los 11 digitos del RUC', 5);
        $("#sec_comp_mant_proveedor_param_ruc").focus();
        return false;
    }
  var titulo = "";

    if(paramProveedor_id == 0)
    {
      // CREAR
        aviso = "¿Está seguro de crear el nuevo proveedor?";
      titulo = "Registrar";
    }
    else
    {
      // EDITAR
        aviso = "¿Está seguro de editar el proveedor?";
      titulo = "Editar";
    }
    
    if(paramProveedor_nombre.length == 0)
    {
        alertify.error('Ingrese nombre del proveedor',5);
        $("#sec_comp_mant_proveedor_param_nombre").focus();
          return false;
    }
    
    swal({
              title: titulo,
              text: aviso,
              type: "warning",
              showCancelButton: true,
              cancelButtonText: "NO",
              confirmButtonColor: "#529D73",
              confirmButtonText: "SI",
              closeOnConfirm: false
          },
  function (isConfirm) {
              if(isConfirm){
                  
                  var data = {
                      "accion" : "comp_mant_proveedor_guardar",
                      "nombre" : paramProveedor_nombre,
                      "ruc" : paramProveedor_ruc,
                      "proveedor_id" : paramProveedor_id
                  }
      
                  auditoria_send({ "respuesta": "comp_mant_proveedor_guardar", "data": data });
                  $.ajax({
                      url: "sys/set_comprobante_mantenimiento.php",
                      type: 'POST',
                      data: data,
                      beforeSend: function() {
                          loading("true");
                      },
                      complete: function() {
                          loading();
                      },
                      success: function(resp) {
                          
                          var respuesta = JSON.parse(resp);
      
                          if (parseInt(respuesta.http_code) == 400) {
                              swal({
                                  title: "Error al guardar el proveedor.",
                                  text: respuesta.error,
                                  html:true,
                                  type: "warning",
                                  closeOnConfirm: false,
                                  showCancelButton: false
                              });
                              return false;
                          }
      
                          if (parseInt(respuesta.http_code) == 200) {
                              swal({
                                  title: "Guardar",
                                  text: "El proveedor se guardó correctamente.",
                                  html:true,
                                  type: "success",
                                  closeOnConfirm: false,
                                  showCancelButton: false
                              });
                              $('#Frm_RegistroCompMantProveedor')[0].reset();
                              $("#sec_comp_mant_proveedor_param_id").val(0);
                              $('#btn-form-registro-proveedor').html('Registrar');
                              sec_comp_mant_proveedor_datatable_listar_datatable();
                          }      
                      },
                      error: function() {}
                  });
      
      
              }else{
                  //alertify.error('No se guardó el monto',5);
                  return false;
              }
          });
}

function sec_comp_mant_proveedor_reset_form() {
  $("#sec_comp_mant_proveedor_param_id").val(0);
  $('#sec_comp_mant_proveedor_param_ruc').val("");
  $('#sec_comp_mant_proveedor_param_nombre').val("");
  $('#btn-form-registro-proveedor').html('Registrar');
}

function sec_comp_mant_proveedor_eliminar(id_proveedor){
  swal({
      title: '¿Esta seguro de eliminar el proveedor?',
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
            id_proveedor : id_proveedor,
              accion:'comp_mant_proveedor_eliminar'
              }
  
          $.ajax({
              url: "sys/set_comprobante_mantenimiento.php",
              type: 'POST',
              data: data,
              beforeSend: function() {
                  loading("true");
              },
              complete: function() {
                  loading();
              },
              success: function(resp) {
                  var respuesta = JSON.parse(resp);
                  auditoria_send({
                      "proceso": "comp_mant_proveedor_eliminar",
                      "data": respuesta
                  });
                  if (parseInt(respuesta.http_code) == 200) {
                      swal({
                          title: "Eliminación exitosa",
                          text: "El proveedor se eliminó correctamente",
                          html: true,
                          type: "success",
                          timer: 3000,
                          closeOnConfirm: false,
                          showCancelButton: false
                      });
                      setTimeout(function() {
                          $("#sec_comp_mant_proveedor_param_id").val(0);
                          sec_comp_mant_proveedor_datatable_listar_datatable();
                          return false;
                      }, 3000);
                  } else {
                      swal({
                          title: "Error al eliminar el proveedor",
                          text: respuesta.error,
                          html: true,
                          type: "warning",
                          closeOnConfirm: false,
                          showCancelButton: false
                      });
                  }
              },
              complete: function() {
                  loading(false);
              }
              });
              
      
      } 
  });
  }

