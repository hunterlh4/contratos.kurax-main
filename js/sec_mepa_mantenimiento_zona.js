function sec_mepa_mantenimiento_zona() {

	sec_mepa_mantenimiento_zona_listar();
  sec_mepa_mantenimiento_zona_listar_redes();

}
function sec_mepa_mantenimiento_zona_listar()
{
        var data = {
            "accion": "mepa_mantenimiento_zona_listar"
        }

        tabla = $("#table_mepa_zona").dataTable(
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
                    url : "/sys/get_mepa_mantenimiento_zona.php",
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
						targets: [0, 1, 2, 3, 4, 5, 6]
					}
				],
                "bDestroy" : true,
                aLengthMenu:[10, 20, 30, 40, 50, 100]
            }
        ).DataTable();
    }



$("#sec_mepa_mantenimiento_zona_btn_nuevo").off("click").on("click",function(){
    sec_mepa_mantenimiento_zona_limpiar_input();
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    $("#accordionMepaZonaCamposAuditoria").hide();
    $("#sec_mepa_mantenimiento_zona_modal_guardar_titulo").text("Nueva Zona");
    $("#sec_mepa_mantenimiento_zona_modal_nuevo").modal("show");
  })
  function sec_mepa_mantenimiento_zona_ver(param_id) {
    sec_mepa_mantenimiento_zona_obtener(param_id);
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = true;
    });
}

function sec_mepa_mantenimiento_zona_listar_redes(){
    let select = $("[name='form_modal_sec_mepa_mantenimiento_zona_param_red']");
    let valorSeleccionado = $("#form_modal_sec_mepa_mantenimiento_zona_param_red").val();
    
    $.ajax({
        url: "/sys/get_mepa_mantenimiento_zona.php",
        type: "POST",
        data: {
            accion: "mepa_mantenimiento_zona_red_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
    
            $(select).empty();
    
            if (!valorSeleccionado) {
                    let opcionDefault = $("<option value=0 selected>Seleccione</option>");
                    $(select).append(opcionDefault);
                }
    
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                });
    
                if (valorSeleccionado != 0) {
                    $(select).val(valorSeleccionado);
                }
            },
        error: function () {
            console.error("Error al obtener la lista de redes.");
            }
        });
}


function sec_mepa_mantenimiento_zona_obtener(param_id) {
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    let data = {
          id : param_id,
          accion:'mepa_mantenimiento_zona_obtener'
      }

    $.ajax({
          url:  "/sys/get_mepa_mantenimiento_zona.php",
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
              $("#accordionMepaZonaCamposAuditoria").show();
              $('#form_modal_sec_mepa_mantenimiento_zona_param_id').val(param_id);
              $('#form_modal_sec_mepa_mantenimiento_zona_param_nombre').val(respuesta.result.nombre);
              $('#form_modal_sec_mepa_mantenimiento_zona_param_cc').val(respuesta.result.centro_costo);
              $('#form_modal_sec_mepa_mantenimiento_zona_param_estado').val(respuesta.result.satus).trigger("change.select2");
              $('#form_modal_sec_mepa_mantenimiento_zona_param_red').val(respuesta.result.red_id).trigger("change.select2");
             
              if(respuesta.result.created_at==""){
                  document.getElementById('campoFechaCreacionZona').style.display = 'none';

              }else{
                  document.getElementById('campoFechaCreacionZona').style.display = 'block';
                  $('#form_modal_sec_mepa_mantenimiento_zona_param_fecha_create').val(respuesta.result.created_at);
                  $('#form_modal_sec_mepa_mantenimiento_zona_param_usuario_create').val(respuesta.result.usuario_create);
              }
              if(respuesta.result.updated_at==""){
                  document.getElementById('campoFechaActualiacionZona').style.display = 'none';

              }else{
                  document.getElementById('campoFechaActualiacionZona').style.display = 'block';
                  $('#form_modal_sec_mepa_mantenimiento_zona_param_fecha_update').val(respuesta.result.updated_at);
                  $('#form_modal_sec_mepa_mantenimiento_zona_param_usuario_update').val(respuesta.result.usuario_update);    
              }
              
              if(respuesta.result.created_at=="" && respuesta.result.updated_at==""){
                  $("#accordionMepaZonaCamposAuditoria").hide();
              }

              $("#sec_mepa_mantenimiento_zona_modal_guardar_titulo").text("Editar Zona");
            $("#sec_mepa_mantenimiento_zona_modal_nuevo").modal("show");
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

$("#sec_mepa_mantenimiento_zona_modal_nuevo .btn_guardar").off("click").on("click",function(){

	
  var nombre = $('#form_modal_sec_mepa_mantenimiento_zona_param_nombre').val();
  var red = $('#form_modal_sec_mepa_mantenimiento_zona_param_red').val();


  if(nombre.length == 0)
  {
      alertify.error('Ingrese el nombre de la zona',5);
      $("#form_modal_sec_mepa_mantenimiento_zona_param_nombre").focus();
         return false;
  }

  if(red == "0")
    {
        alertify.error('Seleccione la red',5);
        $("#form_modal_sec_mepa_mantenimiento_zona_param_red").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_mepa_mantenimiento_zona_param_red').select2('open');
        }, 200);

        return false;
    }

  sec_mepa_mantenimiento_zona_guardar();     

  })

function sec_mepa_mantenimiento_zona_limpiar_input()
  {
    $('#form_modal_sec_mepa_mantenimiento_zona_param_id').val(0);
    $('#form_modal_sec_mepa_mantenimiento_zona_param_nombre').val("");
    $('#form_modal_sec_mepa_mantenimiento_zona_param_cc').val("");
    $("#form_modal_sec_mepa_mantenimiento_zona_param_estado").val(0).trigger("change.select2");
    $("#form_modal_sec_mepa_mantenimiento_zona_param_red").val(0).trigger("change.select2");
    $('#form_modal_sec_mantenimiento_razon_social_param_fecha_create').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_fecha_update').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_usuario_create').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_usuario_update').val("");
    }

function sec_mepa_mantenimiento_zona_guardar(){
  var id = $('#form_modal_sec_mepa_mantenimiento_zona_param_id').val();
  var nombre = $('#form_modal_sec_mepa_mantenimiento_zona_param_nombre').val();
  var cc = $('#form_modal_sec_mepa_mantenimiento_zona_param_cc').val();
  var estado = $('#form_modal_sec_mepa_mantenimiento_zona_param_estado').val();
  var red = $('#form_modal_sec_mepa_mantenimiento_zona_param_red').val();


  if(id == 0)
  {
      // CREAR
      aviso = "¿Está seguro de registrar la nueva zona?";
      titulo = "Registrar";
  }
  else
  {
      // EDITAR
      aviso = "¿Está seguro de editar la zona?";
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
                  "accion" : "mepa_mantenimiento_zona_guardar",
                  "id" : id,
                  "nombre" : nombre,
                  "cc" : cc,
                  "estado" : estado,
                  "red" : red                 
              }
      
          auditoria_send({ "respuesta": "mepa_mantenimiento_zona_guardar", "data": data });
          $.ajax({
              url: "sys/set_mepa_mantenimiento_zona.php",
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
                          title: "Error al guardar la zona.",
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
                          text: "La zona se guardó correctamente.",
                          html:true,
                          type: "success",
                          closeOnConfirm: false,
                          showCancelButton: false
                          });
                              $('#Frm_RegistroMepaZona')[0].reset();
                              $("#form_modal_sec_mepa_mantenimiento_zona_param_id").val(0);
                              $("#sec_mepa_mantenimiento_zona_modal_nuevo").modal("hide");
                              sec_mepa_mantenimiento_zona_listar();
                          }      
                      },
                      error: function() {}
                  });
              }else{
                  return false;
              }
          });
}