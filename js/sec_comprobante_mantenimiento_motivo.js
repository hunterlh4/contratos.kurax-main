function sec_comprobante_mantenimiento_motivo() {
    sec_comp_mant_motivo_datatable_listar_datatable();
}


//  MOTIVO

function sec_comp_mant_motivo_datatable_listar_datatable() {

    if(sec_id == "comprobante" && sub_sec_id == "mantenimiento"){

    var data = {
        "accion": "comp_mantenimiento_motivo_listar"
    }

    tabla = $("#sec_comp_mantenimiento_motivo_listar_datatable").dataTable(
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

//
function sec_comp_mant_motivo_obtener(id_motivo) {
  let data = {
          id : id_motivo,
          accion:'comp_mantenimiento_motivo_obtener'
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
              
              $('#sec_comp_mant_motivo_param_id').val(respuesta.result.id);
              $('#sec_comp_mant_motivo_param_descripcion').val(respuesta.result.descripcion);
              $('#sec_comp_mant_motivo_param_nombre').val(respuesta.result.nombre);
              $('#btn-form-registro-motivo').html('Modificar');
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

$("#Frm_RegistroCompMantMotivo").submit(function (e) {
  e.preventDefault();
  sec_comp_mant_motivo_validar();
  });
  
function sec_comp_mant_motivo_validar()
{
    var paramMotivo_nombre = $('#sec_comp_mant_motivo_param_nombre').val();
    var id_motivo = $('#sec_comp_mant_motivo_param_id').val();

    if (paramMotivo_nombre.length == 0) {
        alertify.error('Ingrese el nombre', 5);
        $("#sec_comp_mant_motivo_param_nombre").focus();
        return false;
    }else{

        var data = {
            'accion': 'comp_mantenimiento_motivo_verificar_nombre',
            'nombre': paramMotivo_nombre,
            'id_motivo': id_motivo
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
                sec_comp_mant_motivo_guardar();
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

function sec_comp_mant_motivo_guardar(){
  var paramMotivo_id = $('#sec_comp_mant_motivo_param_id').val();
  var paramMotivo_descripcion = $('#sec_comp_mant_motivo_param_descripcion').val();
  var paramMotivo_nombre = $('#sec_comp_mant_motivo_param_nombre').val();


  //    VALIDACION DE DATOS

    if (paramMotivo_descripcion.length == 0) {
        alertify.error('Ingrese la descripción del motivo', 5);
        $("#sec_comp_mant_motivo_param_descripcion").focus();
        return false;
    }
  var titulo = "";

    if(paramMotivo_id == 0)
    {
      // CREAR
        aviso = "¿Está seguro de crear el nuevo motivo?";
      titulo = "Registrar";
    }
    else
    {
      // EDITAR
        aviso = "¿Está seguro de editar el motivo?";
      titulo = "Editar";
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
                      "accion" : "comp_mant_motivo_guardar",
                      "nombre" : paramMotivo_nombre,
                      "descripcion" : paramMotivo_descripcion,
                      "id_motivo" : paramMotivo_id
                  }
      
                  auditoria_send({ "respuesta": "comp_mant_motivo_guardar", "data": data });
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
                                  title: "Error al guardar el motivo.",
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
                                  text: "El motivo se guardó correctamente.",
                                  html:true,
                                  type: "success",
                                  closeOnConfirm: false,
                                  showCancelButton: false
                              });
                              $('#Frm_RegistroCompMantMotivo')[0].reset();
                              $("#sec_comp_mant_motivo_param_id").val(0);
                              $('#btn-form-registro-motivo').html('Registrar');
                              sec_comp_mant_motivo_datatable_listar_datatable();
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

function sec_comp_mant_motivo_reset_form() {
  $("#sec_comp_mant_motivo_param_id").val(0);
  $('#sec_comp_mant_motivo_param_descripcion').val("");
  $('#sec_comp_mant_motivo_param_nombre').val("");
  $('#btn-form-registro-motivo').html('Registrar');
}

function sec_comp_mant_motivo_eliminar(id_motivo){
  swal({
      title: '¿Esta seguro de eliminar el motivo?',
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
            id_motivo : id_motivo,
              accion:'comp_mant_motivo_eliminar'
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
                      "proceso": "comp_mant_motivo_eliminar",
                      "data": respuesta
                  });
                  if (parseInt(respuesta.http_code) == 200) {
                      swal({
                          title: "Eliminación exitosa",
                          text: "El motivo se eliminó correctamente",
                          html: true,
                          type: "success",
                          timer: 3000,
                          closeOnConfirm: false,
                          showCancelButton: false
                      });
                      setTimeout(function() {
                          $("#sec_comp_mant_motivo_param_id").val(0);
                          sec_comp_mant_motivo_datatable_listar_datatable();
                          return false;
                      }, 3000);
                  } else {
                      swal({
                          title: "Error al eliminar el motivo",
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