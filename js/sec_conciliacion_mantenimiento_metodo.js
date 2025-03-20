function sec_conciliacion_mantenimiento_metodo() {

    conci_mant_metodo_form_proveedor_listar();
    conci_mant_metodo_search_proveedor_listar();
	conci_mant_metodo_listar();

    $('#btn_conci_mant_metodo_limpiar_filtros_de_busqueda').click(function() {
		$('#search_conci_mant_metodo_param_proveedor_id').select2().val(0).trigger("change");
		$('#search_conci_mant_metodo_param_estado').select2().val('').trigger("change");
        conci_mant_metodo_listar();

	});
}
function conci_mant_metodo_listar()
{
    if(sec_id == "conciliacion" && sub_sec_id == "mantenimiento"){

        var estado_id = $("#search_conci_mant_metodo_param_estado").val();
        var proveedor_id = $("#search_conci_mant_metodo_param_proveedor_id").val();


        var data = {
            "accion": "conci_mant_metodo_listar",
            estado_id: estado_id,
            proveedor_id:proveedor_id
        }
        
        tabla = $("#table_conci_mant_metodo").dataTable({
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
                url: "/sys/get_conciliacion_mantenimiento_metodo.php",
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

function conci_mant_metodo_form_proveedor_listar(){
    let select = $("[name='form_modal_conci_mant_metodo_param_proveedor_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_metodo_param_proveedor_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento_metodo.php",
        type: "POST",
        data: {
            accion: "conci_mant_metodo_proveedor_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
        
            
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                    $(select).append(opcion);
                    });
        
                    
                    if (valorSeleccionado != 0 || valorSeleccionado != null) {
                        $(select).val(valorSeleccionado);
                    }
                    
                },
            error: function () {
                console.error("Error al obtener la lista de monedas.");
                }
        });
}

function conci_mant_metodo_search_proveedor_listar(){
    let select = $("[name='search_conci_mant_metodo_param_proveedor_id']");
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento_metodo.php",
        type: "POST",
        data: {
            accion: "conci_mant_metodo_proveedor_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            console.log(respuesta);
            $(select).empty();
        
            let opcionDefault = $("<option value=0 selected>Todos</option>");
            $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
                  
                },
        error: function () {
            console.error("Error al obtener la lista de metodos.");
            }
        });
}

function conci_mant_metodo_eliminar(id_metodo){

    swal({
            title: '¿Está seguro de eliminar el metodo?',
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
                    id_metodo: id_metodo,
                    accion: 'conci_mant_metodo_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_conciliacion_mantenimiento_metodo.php",
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
                            "proceso": "conci_mant_metodo_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "El metodo se eliminó correctamente",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                conci_mant_metodo_listar();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar el metodo",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se realizaron cambios',5);
                conci_mant_metodo_listar();
                return false;
            }
        });
    }
$("#conci_mant_metodo_btn_nuevo").off("click").on("click",function(){
    conci_mant_metodo_limpiar_input();
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    $("#accordionConciMantMetodoCamposAuditoria").hide();
    $("#conci_mant_metodo_modal_guardar_titulo").text("Nuevo Método");
    $("#conci_mant_metodo_modal_nuevo").modal("show");
  })
function conci_mant_metodo_ver(param_id) {
    conci_mant_metodo_obtener(param_id);
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = true;
    });
    $("#conci_mant_metodo_modal_guardar_titulo").text("Ver Método");
    $("#campoFechaActualiacionConciMantMetodo").show();
    document.getElementById('conciMantMetodoGuardar').style.display = 'none';

}

function conci_mant_metodo_obtener(param_id) {

    $("#conci_mant_metodo_modal_guardar_titulo").text("Editar Método");
    document.getElementById('conciMantMetodoGuardar').style.display = 'block';

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
          accion:'conci_mant_metodo_obtener'
      }

    $.ajax({
          url:  "/sys/get_conciliacion_mantenimiento_metodo.php",
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

            $("#campoFechaActualiacionConciMantMetodo").hide();

            $('#form_modal_conci_mant_metodo_param_id').val(param_id);
            $('#form_modal_conci_mant_metodo_param_nombre').val(respuesta.result.nombre);
            $('#form_modal_conci_mant_metodo_param_proveedor_id').val(respuesta.result.proveedor_id).trigger("change.select2");

              if(respuesta.result.updated_at==""){
                  //document.getElementById('campoFechaActualiacionConciMantMetodo').style.display = 'none';

              }else{
                  //document.getElementById('campoFechaActualiacionConciMantMetodo').style.display = 'block';
                  $('#form_modal_conci_mant_metodo_param_fecha_update').val(respuesta.result.updated_at);
                  $('#form_modal_conci_mant_metodo_param_usuario_update').val(respuesta.result.usuario_update);    
              }

            $("#conci_mant_metodo_modal_nuevo").modal("show");
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

$("#conci_mant_metodo_modal_nuevo .btn_guardar").off("click").on("click",function(){

	
    var nombre = $('#form_modal_conci_mant_metodo_param_nombre').val();
    var proveedor_id = $("#form_modal_conci_mant_metodo_param_proveedor_id").val();

    if(nombre.length == 0)
    {
        alertify.error('Ingrese el nombre del metodo',5);
        $("#form_modal_conci_mant_metodo_param_nombre").focus();
        return false;
    }
    if(proveedor_id == 0){
        alertify.error('Seleccione el proveedor',5);
        $("#form_modal_conci_mant_metodo_param_proveedor_id").focus();
        return false;
    }
    
    conci_mant_metodo_validar_nombre();     
    })

function conci_mant_metodo_validar_nombre(){
	var nombre = $('#form_modal_conci_mant_metodo_param_nombre').val();
	var id_metodo = $('#form_modal_conci_mant_metodo_param_id').val();

	var data = {
		'accion': 'conci_mant_metodo_validar',
		'nombre': nombre,
		'id_metodo': id_metodo
		};

	$.ajax({
		url: "sys/get_conciliacion_mantenimiento_metodo.php",
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
				conci_mant_metodo_guardar();
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
function conci_mant_metodo_limpiar_input()
  {
    document.getElementById('campoFechaActualiacionConciMantMetodo').style.display = 'none';

    $('#form_modal_conci_mant_metodo_param_id').val(0);
    $('#form_modal_conci_mant_metodo_param_nombre').val("");
    $('#form_modal_conci_mant_metodo_param_proveedor_id').val(0).trigger("change.select2");
    $('#form_modal_conci_mant_metodo_param_fecha_create').val("");
    $('#form_modal_conci_mant_metodo_param_fecha_update').val("");
    $('#form_modal_conci_mant_metodo_param_usuario_create').val("");
    $('#form_modal_conci_mant_metodo_param_usuario_update').val("");
    }

function conci_mant_metodo_guardar(){
  var id = $('#form_modal_conci_mant_metodo_param_id').val();
  var nombre = $('#form_modal_conci_mant_metodo_param_nombre').val();
  var proveedor_id = $('#form_modal_conci_mant_metodo_param_proveedor_id').val();

  if(id == 0)
  {
      // CREAR
      aviso = "¿Está seguro de registrar el nuevo metodo?";
      titulo = "Registrar";
  }
  else
  {
      // EDITAR
      aviso = "¿Está seguro de editar el metodo?";
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
                  "accion" : "conci_mant_metodo_guardar",
                  "id" : id,
                  "nombre" : nombre,
                  "proveedor_id": proveedor_id       
              }
      
          auditoria_send({ "respuesta": "conci_mant_metodo_guardar", "data": data });
          $.ajax({
              url: "sys/set_conciliacion_mantenimiento_metodo.php",
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
                          title: "Error al guardar el metodo.",
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
                          text: "El metodo se guardó correctamente.",
                          html:true,
                          type: "success",
                          closeOnConfirm: false,
                          showCancelButton: false
                          });
                              $('#Frm_RegistroConciMantMetodo')[0].reset();
                              $("#form_modal_conci_mant_metodo_param_id").val(0);
                              $("#conci_mant_metodo_modal_nuevo").modal("hide");
                              conci_mant_metodo_listar();
                          }      
                      },
                      error: function() {}
                  });
              }else{
                  return false;
              }
          });
}