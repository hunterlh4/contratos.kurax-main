function sec_mepa_mantenimiento_cuenta_contable() {

	sec_mepa_mantenimiento_cuenta_contable_listar();
    sec_mepa_mantenimiento_cuenta_contable_listar_param_empresa();
    sec_mepa_mantenimiento_cuenta_contable_listar_empresa();

    $('#btn_mepa_mant_cuenta_limpiar_filtros_de_busqueda').click(function() {
		$('#search_mepa_mant_cuenta_contable_param_empresa').select2().val(0).trigger("change");
		$('#search_mepa_mant_cuenta_contable_param_estado').select2().val('').trigger("change");
        sec_mepa_mantenimiento_cuenta_contable_listar();

	});
}
function sec_mepa_mantenimiento_cuenta_contable_listar()
{
    if(sec_id == "mepa" && sub_sec_id == "mantenimiento"){

        $("#sec_comprobante_pago_div_listar").show();

        var empresa_id = $("#search_mepa_mant_cuenta_contable_param_empresa").val();
        var estado_id = $("#search_mepa_mant_cuenta_contable_param_estado").val();
    

        var data = {
            "accion": "mepa_mantenimiento_cuenta_contable_listar",
            empresa_id: empresa_id,
            estado_id: estado_id
        }

        tabla = $("#table_mepa_cuenta_contable").dataTable(
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
                    url : "/sys/get_mepa_mantenimiento_cuenta_contable.php",
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
                "order": [[0, 'desc']],
                aLengthMenu:[10, 20, 30, 40, 50, 100]
            }
        ).DataTable();
    }
    }


function sec_mepa_mantenimiento_cuenta_contable_listar_empresa(){
        let select = $("[name='search_mepa_mant_cuenta_contable_param_empresa']");
        
        $.ajax({
            url: "/sys/get_mepa_mantenimiento_cuenta_contable.php",
            type: "POST",
            data: {
                accion: "mepa_mantenimiento_cuenta_contable_listar_empresa"
                },
            success: function (datos) {
                var respuesta = JSON.parse(datos);
            
                $(select).empty();
            
                let opcionDefault = $("<option value=0 selected>Todos</option>");
                $(select).append(opcionDefault);
            
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
                      
                    },
            error: function () {
                console.error("Error al obtener la lista de empresas.");
                }
            });
    }

function sec_mepa_mantenimiento_cuenta_contable_eliminar(id_cuenta){

    swal({
            title: '¿Está seguro de eliminar la Cuenta Contable?',
            type: "warning",
            showCancelButton: true,
            html: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: "SI",
            cancelButtonText: "NO",
            closeOnConfirm: false,
            closeOnCancel: true,
        }, function (isConfirm) {
            if (isConfirm) {

                let data = {
                    id_cuenta: id_cuenta,
                    accion: 'mepa_mant_cuenta_contable_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_mepa_mantenimiento_cuenta_contable.php",
                    type: 'POST',
                    data: data,
                    beforeSend: function () {
                        loading(true);
                    },
                    complete: function () {
                        loading(false);
                    },
                    success: function (resp) {
                        var respuesta = JSON.parse(resp);
                        auditoria_send({
                            "proceso": "mepa_mant_cuenta_contable_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "La Cuenta Contable se eliminó correctamente",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                sec_mepa_mantenimiento_cuenta_contable_listar();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar la Cuenta Contable",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se guardaron los cambios',5);
                sec_mepa_mantenimiento_cuenta_contable_listar();
                return false;
            }
        });
    }
$("#sec_mepa_mantenimiento_cuenta_contable_btn_nuevo").off("click").on("click",function(){
    sec_mepa_mantenimiento_cuenta_contable_limpiar_input();
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    $("#accordionMepaCuentaCamposAuditoria").hide();
    $("#sec_mepa_mantenimiento_cuenta_contable_modal_guardar_titulo").text("Nueva Cuenta Contable");
    $("#sec_mepa_mantenimiento_cuenta_contable_modal_nuevo").modal("show");
  })
function sec_mepa_mantenimiento_cuenta_contable_ver(param_id) {
    sec_mepa_mantenimiento_cuenta_contable_obtener(param_id);
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = true;
    });
    $("#sec_mepa_mantenimiento_cuenta_contable_modal_guardar_titulo").text("Ver Cuenta Contable");

}

function sec_mepa_mantenimiento_cuenta_contable_listar_param_empresa(){
    let select = $("[name='form_modal_sec_mepa_mantenimiento_cuenta_contable_param_empresa']");
    let valorSeleccionado = $("#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_empresa").val();
    
    $.ajax({
        url: "/sys/get_mepa_mantenimiento_cuenta_contable.php",
        type: "POST",
        data: {
            accion: "mepa_mantenimiento_cuenta_contable_empresa_listar"
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


function sec_mepa_mantenimiento_cuenta_contable_obtener(param_id) {

    $("#sec_mepa_mantenimiento_cuenta_contable_modal_guardar_titulo").text("Editar Cuenta Contable");

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
          accion:'mepa_mantenimiento_cuenta_contable_obtener'
      }

    $.ajax({
          url:  "/sys/get_mepa_mantenimiento_cuenta_contable.php",
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
              $("#accordionMepaCuentaCamposAuditoria").show();
              $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_id').val(param_id);
              $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_nombre').val(respuesta.result.nombre);
              $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_codigo').val(respuesta.result.codigo);
              $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_cuenta').val(respuesta.result.cuenta_contable);
              $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_empresa').val(respuesta.result.empresa_id).trigger("change.select2");
             
              if(respuesta.result.created_at==""){
                  document.getElementById('campoFechaCreacionCuenta').style.display = 'none';

              }else{
                  document.getElementById('campoFechaCreacionCuenta').style.display = 'block';
                  $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_fecha_create').val(respuesta.result.created_at);
                  $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_usuario_create').val(respuesta.result.usuario_create);
              }
              if(respuesta.result.updated_at==""){
                  document.getElementById('campoFechaActualiacionCuenta').style.display = 'none';

              }else{
                  document.getElementById('campoFechaActualiacionCuenta').style.display = 'block';
                  $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_fecha_update').val(respuesta.result.updated_at);
                  $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_usuario_update').val(respuesta.result.usuario_update);    
              }
              
              if(respuesta.result.created_at=="" && respuesta.result.updated_at==""){
                  $("#accordionMepaCuentaCamposAuditoria").hide();
              }

            $("#sec_mepa_mantenimiento_cuenta_contable_modal_nuevo").modal("show");
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

$("#sec_mepa_mantenimiento_cuenta_contable_modal_nuevo .btn_guardar").off("click").on("click",function(){

	
  var nombre = $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_nombre').val();
  var empresa_id = $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_empresa').val();
  var cuenta_contable = $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_cuenta').val();


  if(nombre.length == 0)
  {
      alertify.error('Ingrese el nombre de la cuenta contable',5);
      $("#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_nombre").focus();
         return false;
  }

  if(cuenta_contable.length == 0)
  {
      alertify.error('Ingrese la cuenta contable',5);
      $("#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_cuenta").focus();
         return false;
  }

  if(empresa_id == "0")
    {
        alertify.error('Seleccione la empresa',5);
        $("#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_empresa").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_empresa').select2('open');
        }, 200);

        return false;
    }

  sec_mepa_mantenimiento_cuenta_contable_guardar();     

  })

function sec_mepa_mantenimiento_cuenta_contable_limpiar_input()
  {
    $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_id').val(0);
    $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_nombre').val("");
    $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_codigo').val("");
    $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_cuenta').val("");
    $("#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_empresa").val(0).trigger("change.select2");
    $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_fecha_create').val("");
    $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_fecha_update').val("");
    $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_usuario_create').val("");
    $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_usuario_update').val("");
    }

function sec_mepa_mantenimiento_cuenta_contable_guardar(){
  var id = $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_id').val();
  var nombre = $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_nombre').val();
  var codigo = $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_codigo').val();
  var cuenta_contable = $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_cuenta').val();
  var empresa_id = $('#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_empresa').val();


  if(id == 0)
  {
      // CREAR
      aviso = "¿Está seguro de registrar la nueva Cuenta Contable?";
      titulo = "Registrar";
  }
  else
  {
      // EDITAR
      aviso = "¿Está seguro de editar la Cuenta Contable?";
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
                  "accion" : "mepa_mantenimiento_cuenta_contable_guardar",
                  "id" : id,
                  "nombre" : nombre,
                  "codigo" : codigo,
                  "cuenta_contable" : cuenta_contable,
                  "empresa_id" : empresa_id                 
              }
      
          auditoria_send({ "respuesta": "mepa_mantenimiento_cuenta_contable_guardar", "data": data });
          $.ajax({
              url: "sys/set_mepa_mantenimiento_cuenta_contable.php",
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
                          title: "Error al guardar la Cuenta Contable.",
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
                          text: "La Cuenta Contable se guardó correctamente.",
                          html:true,
                          type: "success",
                          closeOnConfirm: false,
                          showCancelButton: false
                          });
                              $('#Frm_RegistroMepaCuenta')[0].reset();
                              $("#form_modal_sec_mepa_mantenimiento_cuenta_contable_param_id").val(0);
                              $("#sec_mepa_mantenimiento_cuenta_contable_modal_nuevo").modal("hide");
                              sec_mepa_mantenimiento_cuenta_contable_listar();
                          }      
                      },
                      error: function() {}
                  });
              }else{
                  return false;
              }
          });
}