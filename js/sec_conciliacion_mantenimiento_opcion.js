function sec_conciliacion_mantenimiento_opcion() {

	conci_mant_opcion_listar();

    $('#btn_conci_mant_opcion_limpiar_filtros_de_busqueda').click(function() {
		$('#search_conci_mant_opcion_param_proveedor_id').select2().val(0).trigger("change");
		$('#search_conci_mant_opcion_param_estado').select2().val('').trigger("change");
        conci_mant_opcion_listar();

	});
}
function conci_mant_opcion_listar()
{
    if(sec_id == "conciliacion" && sub_sec_id == "mantenimiento"){

        var estado_id = $("#search_conci_mant_opcion_param_estado").val();


        var data = {
            "accion": "conci_mant_opcion_listar",
            estado_id: estado_id
        }
        
        tabla = $("#table_conci_mant_opcion").dataTable({
            language: {
            decimal: "",
            emptyTable: "No existen registros",
            info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
            infoEmpty: "Mostrando 0 a 0 de 0 entradas",
            infoFiltered: "",
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
                },
                
                },
            scrollY: true,
            scrollX: true,
            dom: 'Bfrtip',
            buttons: [
                    'pageLength',
                ],
            aProcessing: true,
            aServerSide: true,
            ajax: {
                url: "/sys/get_conciliacion_mantenimiento_opcion.php",
                data: data,
                type: "POST",
                dataType: "json",
                error: function(e) {
                },
            },
            createdRow: function(row, data, dataIndex) {
                if (data[0] === 'error') {
                    $('td:eq(0)', row).attr('colspan', 6);
                    $('td:eq(0)', row).attr('align', 'center');
                    $('td:eq(0)', row).addClass('text-center');
                    $('td:eq(1)', row).addClass('text-center');
                    $('td:eq(2)', row).addClass('text-center');
                    $('td:eq(3)', row).addClass('text-center');
                    $('td:eq(4)', row).addClass('text-center');
                    $('td:eq(5)', row).addClass('text-center');
                    this.api().cell($('td:eq(0)', row)).data(data[1]);
                }
            },
            columnDefs: [{
                className: "text-center",
                targets: "_all"
            }],
            bDestroy: true,
            aLengthMenu: [10, 20, 30, 40, 50, 100],
            initComplete: function () {
                // Ocultar la barra de búsqueda
                $('.dataTables_filter').css('display', 'none');
            },
        }).DataTable();
    
    }
    }

function conci_mant_opcion_eliminar(id_opcion){

    swal({
            title: '¿Está seguro de eliminar la opción?',
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
                    id_opcion: id_opcion,
                    accion: 'conci_mant_opcion_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_conciliacion_mantenimiento_opcion.php",
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
                            "proceso": "conci_mant_opcion_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "La opción se eliminó correctamente",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                conci_mant_opcion_listar();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar la opción",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se realizaron cambios',5);
                conci_mant_opcion_listar();
                return false;
            }
        });
    }
$("#conci_mant_opcion_btn_nuevo").off("click").on("click",function(){
    conci_mant_opcion_limpiar_input();
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    $("#conci_mant_opcion_modal_guardar_titulo").text("Nueva Opción de fórmulas");
    $("#conci_mant_opcion_modal_nuevo").modal("show");
  })
function conci_mant_opcion_ver(param_id) {
    conci_mant_opcion_obtener(param_id);
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = true;
    });
    $("#conci_mant_opcion_modal_guardar_titulo").text("Ver Opción");

}

function conci_mant_opcion_obtener(param_id) {

    $("#conci_mant_opcion_modal_guardar_titulo").text("Editar Opción");

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
          accion:'conci_mant_opcion_obtener'
      }

    $.ajax({
          url:  "/sys/get_conciliacion_mantenimiento_opcion.php",
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
              $('#form_modal_conci_mant_opcion_param_id').val(param_id);
              $('#form_modal_conci_mant_opcion_param_nombre').val(respuesta.result.nombre);
              $('#form_modal_conci_mant_opcion_param_descripcion').val(respuesta.result.descripcion);

              if(respuesta.result.updated_at==""){
                  document.getElementById('campoFechaActualiacionConciMantOpcion').style.display = 'none';

              }else{
                  document.getElementById('campoFechaActualiacionConciMantOpcion').style.display = 'block';
                  $('#form_modal_conci_mant_opcion_param_fecha_update').val(respuesta.result.updated_at);
                  $('#form_modal_conci_mant_opcion_param_usuario_update').val(respuesta.result.usuario_update);    
              }

            $("#conci_mant_opcion_modal_nuevo").modal("show");
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

function conci_mant_opcion_limpiar_input()
  {
    document.getElementById('campoFechaActualiacionConciMantOpcion').style.display = 'none';

    $('#form_modal_conci_mant_opcion_param_id').val(0);
    $('#form_modal_conci_mant_opcion_param_nombre').val("");
    $('#form_modal_conci_mant_opcion_param_descripcion').val("");
    $('#form_modal_conci_mant_opcion_param_fecha_update').val("");
    $('#form_modal_conci_mant_opcion_param_usuario_update').val("");
    }

$("#conci_mant_opcion_modal_nuevo .btn_guardar").off("click").on("click",function(){

	
    var nombre = $('#form_modal_conci_mant_opcion_param_nombre').val();
    var descripcion = $("#form_modal_conci_mant_opcion_param_descripcion").val();

    if(nombre.length == 0)
    {
        alertify.error('Ingrese el nombre de la opción',5);
        $("#form_modal_conci_mant_opcion_param_nombre").focus();
        return false;
    }
    if(descripcion.length == 0)
        {
            alertify.error('Ingrese la descripción opción',5);
            $("#form_modal_conci_mant_opcion_param_descripcion").focus();
            return false;
        }
    
    conci_mant_opcion_validar_nombre();     
    })

function conci_mant_opcion_validar_nombre(){
	var nombre = $('#form_modal_conci_mant_opcion_param_nombre').val();
	var id_opcion = $('#form_modal_conci_mant_opcion_param_id').val();

	var data = {
		'accion': 'conci_mant_opcion_validar',
		'nombre': nombre,
		'id_opcion': id_opcion
		};

	$.ajax({
		url: "sys/get_conciliacion_mantenimiento_opcion.php",
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
				conci_mant_opcion_guardar();
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

function conci_mant_opcion_guardar(){
  var id = $('#form_modal_conci_mant_opcion_param_id').val();
  var nombre = $('#form_modal_conci_mant_opcion_param_nombre').val();
  var descripcion = $('#form_modal_conci_mant_opcion_param_descripcion').val();

  if(id == 0)
  {
      // CREAR
      aviso = "¿Está seguro de registrar la nueva opción?";
      titulo = "Registrar";
  }
  else
  {
      // EDITAR
      aviso = "¿Está seguro de editar la opción?";
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
                  "accion" : "conci_mant_opcion_guardar",
                  "id" : id,
                  "nombre" : nombre,
                  "descripcion": descripcion       
              }
      
          auditoria_send({ "respuesta": "conci_mant_opcion_guardar", "data": data });
          $.ajax({
              url: "sys/set_conciliacion_mantenimiento_opcion.php",
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
                          title: "Error al guardar la opción.",
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
                          text: "La opción se guardó correctamente.",
                          html:true,
                          type: "success",
                          closeOnConfirm: false,
                          showCancelButton: false
                          });
                              $('#Frm_RegistroConciMantOpcion')[0].reset();
                              $("#form_modal_conci_mant_opcion_param_id").val(0);
                              $("#conci_mant_opcion_modal_nuevo").modal("hide");
                              conci_mant_opcion_listar();
                          }      
                      },
                      error: function() {}
                  });
              }else{
                  return false;
              }
          });
}