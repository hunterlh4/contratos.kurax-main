function sec_conciliacion_mantenimiento_correo() {

    conci_mant_correo_form_proveedor_listar();
    conci_mant_correo_search_proveedor_listar();
	conci_mant_correo_listar();

    $(".conci_select_correo_filtro").select2({ width: '100%' });

    $('#btn_conci_mant_correo_limpiar_filtros_de_busqueda').click(function() {
		$('#search_conci_mant_correo_param_menu_id').select2().val(0).trigger("change");
		$('#search_conci_mant_correo_param_estado').select2().val('').trigger("change");
        conci_mant_correo_listar();

	});


	$("#Frm_conciCorreoRegistroUsuario").submit(function (e) {
		e.preventDefault();
		conci_mant_correo_guardar_usuario();
		});
}
function conci_mant_correo_listar()
{
    if(sec_id == "conciliacion" && sub_sec_id == "mantenimiento"){

        var estado_id = $("#search_conci_mant_correo_param_estado").val();
        var proveedor_id = $("#search_conci_mant_correo_param_menu_id").val();


        var data = {
            "accion": "conci_mant_correo_listar",
            estado_id: estado_id,
            proveedor_id:proveedor_id
        }
        
        tabla = $("#table_conci_mant_correo").dataTable({
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
                url: "/sys/get_conciliacion_mantenimiento_correo.php",
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

function conci_mant_correo_form_proveedor_listar(){
    let select = $("[name='form_modal_conci_mant_correo_param_menu_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_correo_param_menu_id").val();
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento_correo.php",
        type: "POST",
        data: {
            accion: "conci_mant_correo_menu_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();
        
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
                }
        
            
                $(respuesta.result).each(function (i, e) {
                    let opcion = $("<option value='" + e.id + "'>" + e.titulo + "</option>");
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

function conci_mant_correo_search_proveedor_listar(){
    let select = $("[name='search_conci_mant_correo_param_menu_id']");
    
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento_correo.php",
        type: "POST",
        data: {
            accion: "conci_mant_correo_menu_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            console.log(respuesta);
            $(select).empty();
        
            let opcionDefault = $("<option value=0 selected>Todos</option>");
            $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.titulo + "</option>");
                $(select).append(opcion);
                });
                  
                },
        error: function () {
            console.error("Error al obtener la lista de metodos.");
            }
        });
}

function conci_mant_correo_eliminar(id_correo){

    swal({
            title: '¿Está seguro de eliminar el método?',
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
                    id_correo: id_correo,
                    accion: 'conci_mant_correo_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_conciliacion_mantenimiento_correo.php",
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
                            "proceso": "conci_mant_correo_eliminar",
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
                                conci_mant_correo_listar();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar el método",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se realizaron cambios',5);
                conci_mant_correo_listar();
                return false;
            }
        });
    }
$("#conci_mant_correo_btn_nuevo").off("click").on("click",function(){
    conci_mant_correo_limpiar_input();
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });

    $("#campoFechaActualiacionConciMantMetodoCorreoCreate").hide();
    $("#campoFechaActualiacionConciMantMetodoCorreoUpdate").hide();

    $("#conci_mant_correo_modal_guardar_titulo").text("Nuevo Método");
    $("#conci_mant_correo_modal_nuevo").modal("show");
  })
function conci_mant_correo_ver(param_id) {
    conci_mant_correo_obtener(param_id);
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = true;
    });

    $("#conci_mant_correo_modal_guardar_titulo").text("Ver Método");
    $("#campoFechaActualiacionConciMantMetodoCorreoCreate").show();
    $("#campoFechaActualiacionConciMantMetodoCorreoUpdate").show();
    document.getElementById('conciMantCorreoGuardar').style.display = 'none';


}

function conci_mant_correo_obtener(param_id) {


    $("#campoFechaActualiacionConciMantMetodoCorreoCreate").hide();
    $("#campoFechaActualiacionConciMantMetodoCorreoUpdate").hide();

    $("#conci_mant_correo_modal_guardar_titulo").text("Editar Método");
    document.getElementById('conciMantCorreoGuardar').style.display = 'block';

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
          accion:'conci_mant_correo_obtener'
      }

    $.ajax({
          url:  "/sys/get_conciliacion_mantenimiento_correo.php",
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
              //$("#accordionConciMantMetodoCamposAuditoria").show();

              $('#form_modal_conci_mant_correo_param_id').val(param_id);
              $('#form_modal_conci_mant_correo_param_nombre').val(respuesta.result.nombre);
              $('#form_modal_conci_mant_correo_param_metodo').val(respuesta.result.metodo);
              $('#form_modal_conci_mant_correo_param_menu_id').val(respuesta.result.menu_id).trigger("change.select2");

              if(respuesta.result.created_at==""){
                //document.getElementById('campoFechaActualiacionConciMantMetodoCorreoCreate').style.display = 'none';

            }else{
                //document.getElementById('campoFechaActualiacionConciMantMetodoCorreoCreate').style.display = 'block';
                $('#form_modal_conci_mant_correo_param_fecha_create').val(respuesta.result.created_at);
                $('#form_modal_conci_mant_correo_param_usuario_create').val(respuesta.result.usuario_create);    
            }

              if(respuesta.result.updated_at==""){
                  //document.getElementById('campoFechaActualiacionConciMantMetodoCorreoUpdate').style.display = 'none';

              }else{
                  //document.getElementById('campoFechaActualiacionConciMantMetodoCorreoUpdate').style.display = 'block';
                  $('#form_modal_conci_mant_correo_param_fecha_update').val(respuesta.result.updated_at);
                  $('#form_modal_conci_mant_correo_param_usuario_update').val(respuesta.result.usuario_update);    
              }

            $("#conci_mant_correo_modal_nuevo").modal("show");
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

$("#conci_mant_correo_modal_nuevo .btn_guardar").off("click").on("click",function(){

	
    var nombre = $('#form_modal_conci_mant_correo_param_nombre').val();
    var metodo = $('#form_modal_conci_mant_correo_param_metodo').val();
    var menu_id = $("#form_modal_conci_mant_correo_param_menu_id").val();

    if(nombre.length == 0)
    {
        alertify.error('Ingrese el nombre del metodo',5);
        $("#form_modal_conci_mant_correo_param_nombre").focus();
        return false;
    }
    if(metodo.length == 0)
        {
            alertify.error('Ingrese el método',5);
            $("#form_modal_conci_mant_correo_param_metodo").focus();
            return false;
        }
    if(menu_id == 0){
        alertify.error('Seleccione el Sub Menu',5);
        $("#form_modal_conci_mant_correo_param_menu_id").focus();
        return false;
    }
    
    conci_mant_correo_validar_metodo();     
    })

function conci_mant_correo_validar_metodo(){
	var metodo = $('#form_modal_conci_mant_correo_param_metodo').val();
	var id_correo = $('#form_modal_conci_mant_correo_param_id').val();

	var data = {
		'accion': 'conci_mant_correo_validar',
		'metodo': metodo,
		'id_correo': id_correo
		};

	$.ajax({
		url: "sys/get_conciliacion_mantenimiento_correo.php",
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
				conci_mant_correo_guardar();
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
function conci_mant_correo_limpiar_input()
  {
    document.getElementById('campoFechaActualiacionConciMantMetodo').style.display = 'none';

    $('#form_modal_conci_mant_correo_param_id').val(0);
    $('#form_modal_conci_mant_correo_param_nombre').val("");
    $('#form_modal_conci_mant_correo_param_metodo').val("");
    $('#form_modal_conci_mant_correo_param_menu_id').val(0).trigger("change.select2");
    $('#form_modal_conci_mant_correo_param_fecha_create').val("");
    $('#form_modal_conci_mant_correo_param_fecha_update').val("");
    $('#form_modal_conci_mant_correo_param_usuario_create').val("");
    $('#form_modal_conci_mant_correo_param_usuario_update').val("");
    }

function conci_mant_correo_guardar(){
    var id = $('#form_modal_conci_mant_correo_param_id').val();
    var nombre = $('#form_modal_conci_mant_correo_param_nombre').val();
    var metodo = $('#form_modal_conci_mant_correo_param_metodo').val();
    var menu_id = $('#form_modal_conci_mant_correo_param_menu_id').val();

    if(id == 0)
    {
        // CREAR
        aviso = "¿Está seguro de registrar el nuevo metodo de correo?";
        titulo = "Registrar";
    }
    else
    {
        // EDITAR
        aviso = "¿Está seguro de editar el método de correo?";
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
                  "accion" : "conci_mant_correo_guardar",
                  "id" : id,
                  "nombre" : nombre,
                  "metodo":metodo,
                  "menu_id": menu_id       
              }
      
          auditoria_send({ "respuesta": "conci_mant_correo_guardar", "data": data });
          $.ajax({
              url: "sys/set_conciliacion_mantenimiento_correo.php",
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
                          title: "Error al guardar el método.",
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
                              $("#form_modal_conci_mant_correo_param_id").val(0);
                              $("#conci_mant_correo_modal_nuevo").modal("hide");
                              conci_mant_correo_listar();
                          }      
                      },
                      error: function() {}
                  });
              }else{
                  return false;
              }
          });
}

function conci_mant_correo_btn_obtener_usuarios(id) {

    conci_mant_correo_listar_usuarios()
    $('#conci_mant_correo_modal_usuarios').modal('show');
    $('#modal_title_mantenimiento_usuarios').html(('INTEGRANTES').toUpperCase());
    $('#form_modal_conci_mant_correo_usuarios_param_id').val(id);
    conci_mant_correo_listar_integrantes();
}

function conci_mant_correo_listar_usuarios() {
    let select = $("[name='form_modal_conci_mant_correo_usuarios_param_integrante_id']");
    let valorSeleccionado = $("#form_modal_conci_mant_correo_usuarios_param_integrante_id").val();
        
    $.ajax({
        url: "/sys/get_conciliacion_mantenimiento_correo.php",
        type: "POST",
        data: {
            accion: "conci_mant_correo_listar_usuarios"
        },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty(); 
            if (!valorSeleccionado) {
                let opcionDefault = $("<option value='' selected>Seleccione un usuario</option>");
                $(select).append(opcionDefault);
            }
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
        
            if (valorSeleccionado != null) {
                $(select).val(valorSeleccionado);
                }
                },
        error: function () {
             }
        });
    }

function conci_mant_correo_listar_integrantes() {
	if (sec_id == "conciliacion" && sub_sec_id == "mantenimiento") {
        var metodo_id = $('#form_modal_conci_mant_correo_usuarios_param_id').val();

		var data = {
			accion: "conci_mant_correo_obtener_usuarios",
			metodo_id:metodo_id
			};
		$("#mepa_asignacion_usuario_div_tabla").show();
		
		$('#mepa_asignacion_usuario_div_tabla tfoot th').each(function () {
			var title = $(this).text();
			$(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
			});
		
		tabla = $("#mepa_asignacion_usuario_datatable").dataTable({
			language: {
				decimal: "",
				emptyTable: "No existen registros",
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
						url: "/sys/get_conciliacion_mantenimiento_correo.php",
						data: data,
						type: "POST",
						dataType: "json",
						error: function(e) {
													},
					},
                    columnDefs: [
                        {
                            className: 'text-center',
                            targets: [0, 1, 2, 3, 4, 5, 6]
                        }
                    ],
					createdRow: function(row, data, dataIndex) {
						if (data[0] === 'error') {
							$('td:eq(0)', row).attr('colspan', 7);
							$('td:eq(0)', row).attr('align', 'center');
							$('td:eq(1)', row).css('align', 'center');
							$('td:eq(2)', row).css('align', 'center');
							$('td:eq(3)', row).css('align', 'center');
							$('td:eq(4)', row).css('align', 'center');
							$('td:eq(5)', row).css('align', 'center');
							$('td:eq(6)', row).css('align', 'center');
							this.api().cell($('td:eq(0)', row)).data(data[1]);
						}
					},
					bDestroy: true,
					aLengthMenu: [10, 20, 30, 40, 50, 100],
					initComplete: function () {
						this.api()
						.columns()
						.every(function () {
							var that = this;
		
							$('input', this.footer()).on('keyup change clear', function () {
								if (that.search() !== this.value) {
									that.search(this.value).draw();
								}
							});
						});
					},
				}).DataTable();
			}
		}

function conci_mant_correo_guardar_usuario() {

	var metodo_id = $('#form_modal_conci_mant_correo_usuarios_param_id').val();
    var usuario_id = $('#form_modal_conci_mant_correo_usuarios_param_integrante_id').val();

    aviso = "¿Está seguro de agregar el usuario?";
    titulo = "Añadir Usuario";
    
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
                "accion" : "conci_mant_correo_guardar_integrante",
                "id" : metodo_id,
                "integrante_id" : usuario_id    
            }
      
            auditoria_send({ "respuesta": "conci_mant_correo_guardar_integrante", "data": data });
            $.ajax({
                url: "sys/set_conciliacion_mantenimiento_correo.php",
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
                          title: "Error al guardar el usuario.",
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
                          text: "El usuario se guardó correctamente.",
                          html:true,
                          type: "success",
                          closeOnConfirm: false,
                          showCancelButton: false
                          });
                              //$('#Frm_conciCorreoRegistroUsuario')[0].reset();
                              $("#form_modal_conci_mant_correo_usuarios_param_integrante_id").val(0);
                              conci_mant_correo_listar_integrantes();
                          }      
                      },
                      error: function() {}
                  });
              }else{
                  return false;
              }
          });
	
		}

function conci_mant_correo_btn_eliminar_usuario(id_usuario){
	swal({
		title: 'Esta seguro de eliminar el usuario?',
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
					id : id_usuario,
					accion:'conci_mant_correo_eliminar_integrante'
					}
		
				$.ajax({
					url: "sys/set_conciliacion_mantenimiento_correo.php",
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
							"proceso": "conci_mant_correo_eliminar_integrante",
							"data": respuesta
						});
						if (parseInt(respuesta.http_code) == 200) {
							swal({
								title: "Eliminación exitosa",
								text: "El usuario se eliminó correctamente",
								html: true,
								type: "success",
								timer: 3000,
								closeOnConfirm: false,
								showCancelButton: false
							});
							setTimeout(function() {
								conci_mant_correo_listar_integrantes();
								return false;
							}, 3000);
						} else {
							swal({
								title: "Error al eliminar el usuario",
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