function sec_conciliacion_mantenimiento_tipo_cambio() {

	conci_mant_tipo_cambio_listar();
    conci_mant_tipo_cambio_search_moneda_listar();
    conci_mant_tipo_cambio_form_moneda_listar();

    $('#btn_conci_mant_tipo_cambio_limpiar_filtros_de_busqueda').click(function() {
		$('#search_conci_mant_tipo_cambio_param_proveedor_id').select2().val(0).trigger("change");
		$('#search_conci_mant_tipo_cambio_param_estado').select2().val('').trigger("change");
        conci_mant_tipo_cambio_listar();

	});
}

function conci_mant_tipo_cambio_search_moneda_listar(){
    let select = $("[name='search_conci_mant_tipo_cambio_param_moneda_id']");
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento_tipo_cambio.php",
        type: "POST",
        data: {
            accion: "conci_mant_tipo_cambio_moneda_listar"
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



function conci_mant_tipo_cambio_form_moneda_listar(){
    let select = $("[name='form_modal_conci_mant_tipo_cambio_param_moneda_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_tipo_cambio_param_moneda_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento_tipo_cambio.php",
        type: "POST",
        data: {
            accion: "conci_mant_tipo_cambio_moneda_listar"
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
function conci_mant_tipo_cambio_listar()
{
    if(sec_id == "conciliacion" && sub_sec_id == "mantenimiento"){

        var estado_id = $("#search_conci_mant_tipo_cambio_param_estado").val();


        var data = {
            "accion": "conci_mant_tipo_cambio_listar",
            estado_id: estado_id
        }
        
        tabla = $("#table_conci_mant_tipo_cambio").dataTable({
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
                url: "/sys/get_conciliacion_mantenimiento_tipo_cambio.php",
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
                    $('td:eq(6)', row).addClass('text-center');
                    $('td:eq(7)', row).addClass('text-center');
                    $('td:eq(8)', row).addClass('text-center');
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

function conci_mant_tipo_cambio_eliminar(id_tipo_cambio){

    swal({
            title: '¿Está seguro de eliminar el tipo de cambio?',
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
                    id_tipo_cambio: id_tipo_cambio,
                    accion: 'conci_mant_tipo_cambio_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_conciliacion_mantenimiento_tipo_cambio.php",
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
                            "proceso": "conci_mant_tipo_cambio_eliminar",
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
                                conci_mant_tipo_cambio_listar();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar el tipo de cambio",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se realizaron cambios',5);
                conci_mant_tipo_cambio_listar();
                return false;
            }
        });
    }
$("#conci_mant_tipo_cambio_btn_nuevo").off("click").on("click",function(){
    conci_mant_tipo_cambio_limpiar_input();
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    $("#conci_mant_tipo_cambio_modal_guardar_titulo").text("Nuevo Tipo de Cambio");
    $("#conci_mant_tipo_cambio_modal_nuevo").modal("show");
  })
function conci_mant_tipo_cambio_ver(param_id) {
    conci_mant_tipo_cambio_obtener(param_id);
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = true;
    });
    $("#conci_mant_tipo_cambio_modal_guardar_titulo").text("Ver Tipo de Cambio");
    document.getElementById('conci_mant_tipo_cambio_footer').style.display = 'none';
    document.getElementById('campoFechaActualiacionConciMantTipoCambio').style.display = 'block';

}

function conci_mant_tipo_cambio_obtener(param_id) {

    document.getElementById('conci_mant_tipo_cambio_footer').style.display = 'block';

    $("#conci_mant_tipo_cambio_modal_guardar_titulo").text("Editar Tipo de Cambio");

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
          accion:'conci_mant_tipo_cambio_obtener'
      }

    $.ajax({
          url:  "/sys/get_conciliacion_mantenimiento_tipo_cambio.php",
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
              $('#form_modal_conci_mant_tipo_cambio_param_id').val(param_id);
              $('#form_modal_conci_mant_tipo_cambio_param_fecha').val(respuesta.result.fecha);
              $("#form_modal_conci_mant_tipo_cambio_param_moneda_id").val(respuesta.result.moneda_id).trigger("change.select2");
              $('#form_modal_conci_mant_tipo_cambio_param_monto_venta').val(respuesta.result.monto_venta);
              $('#form_modal_conci_mant_tipo_cambio_param_monto_compra').val(respuesta.result.monto_compra);


              if(respuesta.result.updated_at==""){
                  document.getElementById('campoFechaActualiacionConciMantTipoCambio').style.display = 'none';

              }else{
                  document.getElementById('campoFechaActualiacionConciMantTipoCambio').style.display = 'block';
                  $('#form_modal_conci_mant_tipo_cambio_param_fecha_update').val(respuesta.result.updated_at);
                  $('#form_modal_conci_mant_tipo_cambio_param_usuario_update').val(respuesta.result.usuario_update);    
              }

              document.getElementById('campoFechaActualiacionConciMantTipoCambio').style.display = 'none';

            $("#conci_mant_tipo_cambio_modal_nuevo").modal("show");
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

function conci_mant_tipo_cambio_limpiar_input()
  {
    document.getElementById('campoFechaActualiacionConciMantTipoCambio').style.display = 'none';

    $('#form_modal_conci_mant_tipo_cambio_param_id').val(0);
    $("#form_modal_conci_mant_tipo_cambio_param_moneda_id").val(0).trigger("change.select2");
    $('#form_modal_conci_mant_tipo_cambio_param_monto_venta').val("");
    $('#form_modal_conci_mant_tipo_cambio_param_monto_compra').val("");
    $('#form_modal_conci_mant_tipo_cambio_param_fecha_update').val("");
    $('#form_modal_conci_mant_tipo_cambio_param_usuario_update').val("");
    }

$("#conci_mant_tipo_cambio_modal_nuevo .btn_guardar").off("click").on("click",function(){

	
    var moneda_id = $('#form_modal_conci_mant_tipo_cambio_param_moneda_id').val();
    var fecha = $("#form_modal_conci_mant_tipo_cambio_param_fecha").val();
    var monto_venta = $("#form_modal_conci_mant_tipo_cambio_param_monto_venta").val();
    var monto_compra = $("#form_modal_conci_mant_tipo_cambio_param_monto_compra").val();

    if(moneda_id== 0){
        alertify.error('Seleccione la moneda',5);
        $("#form_modal_conci_mant_tipo_cambio_param_moneda_id").focus();
        return false;
    }
    if(fecha.length == 0){
        alertify.error('Ingrese la fecha',5);
        $("#form_modal_conci_mant_tipo_cambio_param_fecha").focus();
        return false;
    }
    if(monto_venta.length == 0){
        alertify.error('Ingrese el monto de venta',5);
        $("#form_modal_conci_mant_tipo_cambio_param_monto_venta").focus();
        return false;
    }
    if(monto_compra.length == 0){
            alertify.error('Ingrese el monto de compra',5);
            $("#form_modal_conci_mant_tipo_cambio_param_monto_compra").focus();
            return false;
        }
    
        conci_mant_tipo_cambio_guardar();     
    })

function conci_mant_tipo_cambio_guardar(){
    var id = $('#form_modal_conci_mant_tipo_cambio_param_id').val();
    var moneda_id = $('#form_modal_conci_mant_tipo_cambio_param_moneda_id').val();
    var fecha = $("#form_modal_conci_mant_tipo_cambio_param_fecha").val();
    var monto_venta = $("#form_modal_conci_mant_tipo_cambio_param_monto_venta").val();
    var monto_compra = $("#form_modal_conci_mant_tipo_cambio_param_monto_compra").val();

    if(id == 0)
    {
        // CREAR
        aviso = "¿Está seguro de registrar la nueva opción?";
        titulo = "Registrar";
    }
    else
    {
        // EDITAR
        aviso = "¿Está seguro de editar el tipo de cambio?";
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
                  "accion" : "conci_mant_tipo_cambio_guardar",
                  "id" : id,
                  "moneda_id" : moneda_id,
                  "fecha": fecha,
                  "monto_venta": monto_venta,  
                  "monto_compra": monto_compra    
              }
      
          auditoria_send({ "respuesta": "conci_mant_tipo_cambio_guardar", "data": data });
          $.ajax({
              url: "sys/set_conciliacion_mantenimiento_tipo_cambio.php",
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
                          title: "Error al guardar el tipo de cambio.",
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
                              $('#Frm_RegistroConciMantTipoCambio')[0].reset();
                              $("#form_modal_conci_mant_tipo_cambio_param_id").val(0);
                              $("#conci_mant_tipo_cambio_modal_nuevo").modal("hide");
                              conci_mant_tipo_cambio_listar();
                          }      
                      },
                      error: function() {}
                  });
              }else{
                  return false;
              }
          });
}