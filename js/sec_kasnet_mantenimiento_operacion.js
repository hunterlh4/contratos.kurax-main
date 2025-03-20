function sec_kasnet_mantenimiento_operacion() {

	kasnet_mant_operacion_search_tipo_listar();
	kasnet_mant_operacion_form_tipo_listar();

    $(".kasnet_mant_select_filtro").select2({ width: '100%' });

    $('#btn_kasnet_mant_operacion_limpiar_filtros_de_busqueda').click(function() {
		$('#search_kasnet_mant_operacion_param_tipo_id').select2().val(0).trigger("change");
		$('#search_kasnet_mant_operacion_param_estado').select2().val('').trigger("change");
        kasnet_mant_operacion_listar();

	});

    $('.kasnet_mant_limpiar_select2').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val(0).trigger("change");
	});

    $('.kasnet_mant_limpiar_v1_select2').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val("").trigger("change");
	});

	kasnet_mant_operacion_listar();
}
function kasnet_mant_operacion_listar()
{
    if(sec_id == "kasnet" && sub_sec_id == "mantenimiento"){

        var estado_id = $("#search_kasnet_mant_operacion_param_estado").val();
        var tipo_id = $("#search_kasnet_mant_operacion_param_tipo_id").val();


        var data = {
            "accion": "kasnet_mant_operacion_listar",
            estado_id: estado_id,
            tipo_id:tipo_id
        }
        
        tabla = $("#table_kasnet_mant_operacion").dataTable({
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
                url: "/sys/get_kasnet_mantenimiento_operacion.php",
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

function kasnet_mant_operacion_btn_cambiar_estado(id_operacion, estado){

    var title = '';

    if (estado == 0) {
        title = '¿Está seguro de inactivar la operación?';
        respuesta_exito = 'Se inactivo la operación exitosamente.';
    }else if(estado == 1){
        title = '¿Está seguro de activar la operación?';
        respuesta_exito = 'Se activo la operación exitosamente.';
    }

    swal({
            title: title,
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
                    id_operacion: id_operacion,
                    estado:estado,
                    accion: 'kasnet_mant_operacion_cambiar_estado'
                };
    
                $.ajax({
                    url: "sys/set_kasnet_mantenimiento_operacion.php",
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
                            "proceso": "kasnet_mant_operacion_cambiar_estado",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Operación exitosa",
                                text: respuesta_exito,
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                kasnet_mant_operacion_listar(); 
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al cambiar el estado de operación",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se realizaron cambios',5);
                kasnet_mant_operacion_listar();
                return false;
            }
        });
    
	}

$("#kasnet_mant_operacion_btn_nuevo").off("click").on("click",function(){
    kasnet_mant_operacion_limpiar_input();
    $("#kasnetMantOperacion_campoFechaActualiacion").hide();

    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    $("#kasnet_mant_operacion_modal_guardar_titulo").text("Nueva Operación");
    $("#kasnet_mant_operacion_modal_nuevo").modal("show");
  })

function kasnet_mant_operacion_btn_ver(param_id) {
    kasnet_mant_operacion_obtener(param_id);
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = true;
    });
    $("#kasnet_mant_operacion_modal_guardar_titulo").text("Ver Operación");
    $("#kasnetMantOperacion_campoFechaActualiacion").show();

    document.getElementById('kasnetMantOperacion_btnGuardar').style.display = 'none';

}

function kasnet_mant_operacion_obtener(param_id) {
    $("#kasnetMantOperacion_campoFechaActualiacion").hide();

    $("#kasnet_mant_operacion_modal_guardar_titulo").text("Editar Operación");
    document.getElementById('kasnetMantOperacion_btnGuardar').style.display = 'block';

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
          accion:'kasnet_mant_operacion_obtener'
      }

    $.ajax({
        url:  "/sys/get_kasnet_mantenimiento_operacion.php",
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

                $('#form_modal_kasnet_mant_operacion_param_id').val(respuesta.result.id);
                $('#form_modal_kasnet_mant_operacion_param_nombre').val(respuesta.result.nombre);
				$('#form_modal_kasnet_mant_operacion_param_tipo_id').val(respuesta.result.tipo_id).trigger("change.select2");

                if(respuesta.result.updated_at==""){
                    //document.getElementById('campoFechaActualiacionConciMantMetodo').style.display = 'none';

                }else{
                    //document.getElementById('campoFechaActualiacionConciMantMetodo').style.display = 'block';
                    $('#form_modal_kasnet_mant_operacion_param_fecha_update').val(respuesta.result.updated_at);
                    $('#form_modal_kasnet_mant_operacion_param_usuario_update').val(respuesta.result.usuario_update);    
                }

                $("#kasnet_mant_operacion_modal_nuevo").modal("show");
            }else{
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

$("#kasnet_mant_operacion_modal_nuevo .btn_guardar").off("click").on("click",function(){

	
    var nombre = $('#form_modal_kasnet_mant_operacion_param_nombre').val();
	var tipo_id = $("#form_modal_kasnet_mant_operacion_param_tipo_id").val();

    if(nombre.length == 0)
    {
        alertify.error('Ingrese el nombre de operación',5);
        $("#form_modal_kasnet_mant_operacion_param_nombre").focus();
        return false;
    }

    if(tipo_id == 0){
        alertify.error('Seleccione el tipo de operación',5);
        $("#form_modal_kasnet_mant_operacion_param_tipo_id").focus();
        return false;
    }
    
    kasnet_mant_operacion_validar_nombre();     
    })

function kasnet_mant_operacion_validar_nombre(){
	var nombre = $('#form_modal_kasnet_mant_operacion_param_nombre').val();
	var id_operacion = $('#form_modal_kasnet_mant_operacion_param_id').val();

	var data = {
		'accion': 'kasnet_mant_operacion_validar',
		'nombre': nombre,
		'id_operacion': id_operacion
		};

	$.ajax({
		url: "sys/get_kasnet_mantenimiento_operacion.php",
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

		    if (parseInt(respuesta.http_code) == 400) {
				kasnet_mant_operacion_guardar();
                console.log("nuevo");

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

function kasnet_mant_operacion_limpiar_input()
  {

    $('#form_modal_kasnet_mant_operacion_param_id').val(0);
    $('#form_modal_kasnet_mant_operacion_param_nombre').val("");
    $('#form_modal_kasnet_mant_operacion_param_tipo_id').val(0).trigger("change.select2");
    $('#form_modal_kasnet_mant_operacion_param_usuario_create').val("");
    $('#form_modal_kasnet_mant_operacion_param_usuario_update').val("");

    //kasnet_mant_operacion_form_archivos_listar();

    }

function kasnet_mant_operacion_guardar(){
    var id = $('#form_modal_kasnet_mant_operacion_param_id').val();

    var nombre = $('#form_modal_kasnet_mant_operacion_param_nombre').val().trim();
    var tipo_id = $('#form_modal_kasnet_mant_operacion_param_tipo_id option:selected').text();

    if(id == 0)
    {
        // CREAR
        aviso = "¿Está seguro de registrar la nueva operación?";
        titulo = "Registrar";
    }
    else
    {
        // EDITAR
        aviso = "¿Está seguro de editar la operación?";
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

            var dataForm = new FormData($("#Frm_KasnetMantOperacion")[0]);
            dataForm.append("accion", "kasnet_mant_operacion_guardar");
            dataForm.append("nombre", nombre);
            dataForm.append("tipo_id", tipo_id);
      
            auditoria_send({ "respuesta": "kasnet_mant_operacion_guardar", "data": dataForm });
            $.ajax({
                url: "sys/set_kasnet_mantenimiento_operacion.php",
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData:false,
                beforeSend: function() {
                    loading("true");
                    },
                complete: function() {
                    loading();
                       },
                success: function(resp) {
                    loading("false");
                    var respuesta = JSON.parse(resp);
                    if (parseInt(respuesta.http_code) == 400) {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "warning",
                            closeOnConfirm: false,
                            showCancelButton: false
                            });
                        return false;
                    }
        
                    if (parseInt(respuesta.http_code) == 200) {
                        swal({
                            title: respuesta.titulo,
                            text:  respuesta.descripcion,
                            html:true,
                            type: "success",
                            closeOnConfirm: false,
                            showCancelButton: false
                            });
                            $('#Frm_KasnetMantOperacion')[0].reset();
                            $("#form_modal_kasnet_mant_operacion_param_id").val(0);
                            $("#kasnet_mant_operacion_modal_nuevo").modal("hide");
                            kasnet_mant_operacion_listar();
                            return false;
                        }      
                    },
                error: function() {}
                    });          
        }else{
            alertify.error('No se guardaron los cambios',5);
            return false;
        }
        });
}

function kasnet_mant_operacion_btn_historico_cambios(operacion_id) {
	
    // var num_documento = this.dataset.numDocumento;
 
     $('#kasnet_mant_operacion_modal_historial_cambio').modal('show');
     $('#kasnet_mant_operacion_modal_historico_cambio_titulo').html((operacion_id + ' - HISTORIAL DE CAMBIOS').toUpperCase());
     kasnet_mant_operacion_historico_listar_Datatable(operacion_id);
 
 }
 
 
 function kasnet_mant_operacion_historico_listar_Datatable(operacion_id) {
     if (sec_id == "kasnet" && sub_sec_id == "mantenimiento") {
         
         var data = {
             accion: "kasnet_mant_operacion_historico",
             operacion_id:operacion_id
             };
         $("#kasnet_mant_operacion_modal_historial_cambios_div_tabla").show();
         
         $('#kasnet_mant_operacion_modal_historial_cambios_div_tabla tfoot th').each(function () {
             var title = $(this).text();
             $(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
             });
         
         tabla = $("#kasnet_mant_operacion_modal_historial_cambios_datatable").dataTable({
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
                         url: "/sys/get_kasnet_mantenimiento_operacion.php",
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

 function kasnet_mant_operacion_form_tipo_listar(){
    let select = $("[name='form_modal_kasnet_mant_operacion_param_tipo_id']");
    let valorSeleccionado = $("#form_modal_kasnet_mant_operacion_param_tipo_id").val();
    
    $.ajax({
        url: "/sys/get_kasnet_mantenimiento_operacion.php",
        type: "POST",
        data: {
            accion: "kasnet_mant_operacion_tipo_listar"
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

function kasnet_mant_operacion_search_tipo_listar(){
    let select = $("[name='search_kasnet_mant_operacion_param_tipo_id']");
    
    $.ajax({
        url: "/sys/get_kasnet_mantenimiento_operacion.php",
        type: "POST",
        data: {
            accion: "kasnet_mant_operacion_tipo_listar"
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