var global_param_producto_id = 0;

//INICIO: FUNCIONES INICIALIZADOS
function sec_mantenimiento_producto()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mantenimiento_producto_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	sec_mantenimiento_producto_listar_producto();
    sec_mantenimiento_producto_listar_canal_venta();
}
//FIN: FUNCIONES INICIALIZADOS

const sec_mantenimiento_producto_listar_producto = () => {
    if(sec_id == "mantenimiento" && sub_sec_id == "producto")
    {
        var data = {
            "accion": "sec_mantenimiento_producto_listar_producto"
        }

        tabla = $("#sec_mantenimiento_producto_div_listar_producto_datatable").dataTable(
            {
                language:{
                    "decimal":        "",
                    "emptyTable":     "No existen registros",
                    "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                    "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
                    "infoFiltered":   "",
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
                    url : "/sys/set_mantenimiento_producto.php",
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
                        targets: [0, 1, 2, 3, 4, 5]
                    }
                ],
                "bDestroy" : true,
                aLengthMenu:[10, 20, 30, 40, 50, 100],
                "order" : 
                [
                    0, "desc"   
                ]
            }
        ).DataTable();
    }
}

const sec_mantenimiento_producto_btn_nuevo = () => {

	sec_mantenimiento_producto_limpiar_input();
    $("#sec_mantenimiento_producto_modal_nuevo_producto_titulo").text("Registro de Producto");
    $("#sec_mantenimiento_producto_modal_nuevo_producto").modal("show");
}

const sec_mantenimiento_producto_limpiar_input = () => {
	$('#form_modal_sec_mantenimiento_producto_param_id').val(0);
	$('#form_modal_sec_mantenimiento_producto_param_nombre').val("");
	$('#form_modal_sec_mantenimiento_producto_param_descripcion').val("");
	$("#form_modal_sec_mantenimiento_producto_param_canal_venta").val(0).trigger("change.select2");
}

const sec_mantenimiento_producto_listar_canal_venta = () => {

	var data = {
        "accion": "sec_mantenimiento_producto_listar_canal_venta"
    }

    var array_servicios = [];
    
    $.ajax({
        url: "/sys/set_mantenimiento_producto.php",
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
            auditoria_send({ "respuesta": "sec_mantenimiento_canal_caja_listar_canal_venta", "data": respuesta });
            
            if(parseInt(respuesta.http_code) == 400)
            {
                if(parseInt(respuesta.codigo) == 1)
                {

                    var html = '<option value="0">-- Seleccione una opción --</option>';
                    html += '<option value="">' + respuesta.texto +'</option>';
                    $("#form_modal_sec_mantenimiento_producto_param_canal_venta").html(html).trigger("change");

                    return false;
                }
                else if(parseInt(respuesta.codigo) == 2)
                {
                    swal({
                        title: respuesta.titulo,
                        text: respuesta.texto,
                        html:true,
                        type: "warning",
                        closeOnConfirm: false,
                        showCancelButton: false
                    });
                    return false;
                }
            }
            else if(parseInt(respuesta.http_code) == 200) 
            {
                array_servicios.push(respuesta.texto);
            
                var html = '<option value="0">-- Seleccione una opción --</option>';

                for (var i = 0; i < array_servicios[0].length; i++) 
                {
                    html += '<option value=' + array_servicios[0][i].id  + '>' + array_servicios[0][i].nombre +'</option>';
                }

                $("#form_modal_sec_mantenimiento_producto_param_canal_venta").html(html).trigger("change");

                return false;
            }
        },
        error: function() {}
    });
}

const form_modal_sec_mantenimiento_producto_btn_guardar = () => {
	
	var param_producto_id = $('#form_modal_sec_mantenimiento_producto_param_id').val();
	var param_nombre = $('#form_modal_sec_mantenimiento_producto_param_nombre').val().trim();
	var param_descripcion = $('#form_modal_sec_mantenimiento_producto_param_descripcion').val().trim();
	var param_canal_venta = $('#form_modal_sec_mantenimiento_producto_param_canal_venta').val();

	var accion = "";
	var titulo = "";

	if(param_producto_id == "")
    {
        alertify.error('Ocurrió un error, vuelve a refrescar la página',5);
        return false;
    }
    else if(param_producto_id == "0")
    {
    	// CREAR
    	accion = "mantenimiento_producto_nuevo";
    	titulo = "registrar";
    }
    else if(param_producto_id != "0")
    {
    	// EDITAR
    	if(param_producto_id == global_param_producto_id)
    	{
    		accion = "mantenimiento_producto_editar";
    		titulo = "editar";
    	}
    	else
    	{
    		alertify.error('Ocurrió un error, no manipular datos, vuelve a refrescar la página',5);
        	return false;
    	}
    }

    if(param_nombre.length == 0)
    {
        alertify.error('Ingrese Nombre',5);
        $("#form_modal_sec_mantenimiento_producto_param_nombre").focus();
       	return false;
    }

    if(param_descripcion.length == 0)
    {
        alertify.error('Ingrese Descripción',5);
        $("#form_modal_sec_mantenimiento_producto_param_descripcion").focus();
       	return false;
    }

    if(param_canal_venta == "0")
    {
        alertify.error('Seleccione Canal Venta',5);
        $("#form_modal_sec_mantenimiento_producto_param_canal_venta").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_mantenimiento_producto_param_canal_venta').select2('open');
        }, 200);

        return false;
    }

    swal(
    {
        title: '¿Está seguro de '+titulo+'?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    function(isConfirm)
    {
        if(isConfirm)
        {
            var dataForm = new FormData($("#form_modal_sec_mantenimiento_producto")[0]);
            dataForm.append("accion",accion);
            
            $.ajax({
                url: "sys/set_mantenimiento_producto.php",
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                    loading();
                },
                success: function(data){
                    
                    var respuesta = JSON.parse(data);
                    auditoria_send({ "respuesta": "form_modal_sec_mantenimiento_producto_btn_guardar", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200)
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.texto,
                            html:true,
                            type: "success",
                            timer: 6000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        },
                        function (isConfirm)
                        {
                            if (isConfirm)
                            {
                                $("#sec_mantenimiento_producto_modal_nuevo_producto").modal("hide");
                                swal.close();
                                sec_mantenimiento_producto_listar_producto();

                                return true;
                            }
                            else
                            {
                                setTimeout(function() {
                                    $("#sec_mantenimiento_producto_modal_nuevo_producto").modal("hide");
                                    swal.close();
                                    sec_mantenimiento_producto_listar_producto();

                                    return true
                                }, 5000);
                            }
                        });

                        return true;
                    }
                    else
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.texto,
                            html:true,
                            type: "warning",
                            closeOnConfirm: false,
                            showCancelButton: false
                        });

                        return false;
                    }

                }
            });
        }
    });
}

const sec_mantenimiento_producto_obtener_producto = (param_producto_id) => {
    var data = {
        "accion": "sec_mantenimiento_producto_obtener_producto",
        "param_producto_id": param_producto_id
    }

    $.ajax({
        url: "sys/set_mantenimiento_producto.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(data) {
            
            var respuesta = JSON.parse(data);
            auditoria_send({ "respuesta": "sec_mantenimiento_producto_obtener_producto", "data": respuesta });
            if(parseInt(respuesta.http_code) == 200)
            {
                var data_back = respuesta.texto;

                $('#form_modal_sec_mantenimiento_producto_param_id').val(data_back[0].id);
                global_param_producto_id = data_back[0].id;
                $('#form_modal_sec_mantenimiento_producto_param_nombre').val(data_back[0].nombre);
                $('#form_modal_sec_mantenimiento_producto_param_descripcion').val(data_back[0].descripcion);
                $('#form_modal_sec_mantenimiento_producto_param_canal_venta').val(data_back[0].canal_venta_id).trigger('change.select2');

                $("#sec_mantenimiento_producto_modal_nuevo_producto_titulo").text("Editar Producto");
                $("#sec_mantenimiento_producto_modal_nuevo_producto").modal("show");
            }
            else
            {
                swal({
                    title: respuesta.titulo,
                    text: respuesta.texto,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });

                return false;
            }    
        }
    });
}

const sec_mantenimiento_producto_activar_desactivar_producto = (param_producto_id, param_valor) => {
    
    var titulo = "";

    if(param_valor == 1)
    {
        // Activar
        titulo = "activar";
    }
    else
    {
        // Desactivar
        titulo = "desactivar";
    }

    swal(
    {
        title: '¿Está seguro de '+titulo+'?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    function(isConfirm)
    {
        if(isConfirm)
        {
            var data = {
                "accion" : "sec_mantenimiento_producto_activar_desactivar_producto",
                "param_producto_id" : param_producto_id,
                "param_valor" : param_valor
            }
            
            $.ajax({
                url: "sys/set_mantenimiento_producto.php",
                type: 'POST',
                data: data,
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                    loading();
                },
                success: function(data){
                    
                    var respuesta = JSON.parse(data);
                    auditoria_send({ "respuesta": "sec_mantenimiento_producto_activar_desactivar_producto", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200)
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.texto,
                            html:true,
                            type: "success",
                            timer: 6000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        },
                        function (isConfirm)
                        {
                            if (isConfirm)
                            {
                                swal.close();
                                sec_mantenimiento_producto_listar_producto();

                                return true;
                            }
                            else
                            {
                                setTimeout(function() {
                                    swal.close();
                                    sec_mantenimiento_producto_listar_producto();

                                    return true
                                }, 5000);
                            }
                        });

                        return true;
                    }
                    else
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.texto,
                            html:true,
                            type: "warning",
                            closeOnConfirm: false,
                            showCancelButton: false
                        });

                        return false;
                    }

                }
            });
        }
    });
}

const sec_mantenimiento_producto_historial_cambios = (param_producto_id, param_producto_nombre) => {

	let data = {
		"accion": "sec_mantenimiento_producto_historial_cambios",
		"param_producto_id": param_producto_id
	}

	$("#sec_mantenimiento_producto_modal_historial_cambio_producto").modal("show");

	tabla = $("#sec_mantenimiento_producto_div_listar_historial_cambio_producto_datatable").dataTable(
    {
        language:{
            "decimal":        "",
            "emptyTable":     "No existen registros",
            "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
            "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
            "infoFiltered":   "",
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
            url : "/sys/set_mantenimiento_producto.php",
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
                targets: [0, 1, 2, 3, 4, 5, 6, 7]
            }
        ],
        "bDestroy" : true,
        aLengthMenu:[10, 20, 30, 40, 50, 100],
        "order" : 
        [
            
        ]
    }).DataTable();
}

function sec_mepa_obtener_historico_usuarios_por_grupo_id(id_grupo) {

			let data = {
				id : id_grupo,
				accion:'mepa_asignacion_grupo_detalle'
				}
		
			$.ajax({
				url:  "/sys/get_mepa_asignacion_usuario.php",
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
					$('#modalMepaHistoricoUsuario').modal('show');
					$('#modal_title_mepa_historico_usuarios').html(('HISTORICO DE USUARIOS - '+respuesta.result.usuario_creador_nombre ).toUpperCase());
					$('#mepa_grupo_id').val(respuesta.result.id);
					sec_mepa_asignacion_listar_usuarios_creador_Datatable(id_grupo);
					sec_mepa_asignacion_listar_usuarios_aprobador_Datatable(id_grupo);
					sec_mepa_asignacion_listar_usuarios_integrante_Datatable(id_grupo);
		
					}
				},
				error: function (resp, status) {},
				});
		}
