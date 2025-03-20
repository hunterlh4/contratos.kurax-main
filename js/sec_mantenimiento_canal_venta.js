var global_param_canal_venta_id = 0;

//INICIO: FUNCIONES INICIALIZADOS
function sec_mantenimiento_canal_venta()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mantenimiento_canal_venta_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

    sec_mantenimiento_canal_venta_listar_servicios();
    sec_mantenimiento_canal_venta_listar_canal_venta();
}
//FIN: FUNCIONES INICIALIZADOS

const sec_mantenimiento_canal_venta_btn_nuevo = () => {

	sec_mantenimiento_canal_venta_limpiar_input();
    $("#sec_mantenimiento_canal_venta_modal_nuevo_canal_venta_titulo").text("Registro de Canal de Venta");
    $("#sec_mantenimiento_canal_venta_modal_nuevo_canal_venta").modal("show");
}

const sec_mantenimiento_canal_venta_listar_canal_venta = () => {
    if(sec_id == "mantenimiento" && sub_sec_id == "canal_venta")
    {
        var data = {
            "accion": "sec_mantenimiento_canal_venta_listar_canal_venta"
        }

        tabla = $("#sec_mantenimiento_canal_venta_div_listar_canal_venta_datatable").dataTable(
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
                    url : "/sys/set_mantenimiento_canal_venta.php",
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
                        targets: [0, 1, 2, 3, 4, 5, 7, 8]
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

const sec_mantenimiento_canal_venta_listar_servicios = () => {

	var data = {
        "accion": "sec_mantenimiento_canal_venta_listar_servicios"
    }

    var array_servicios = [];
    
    $.ajax({
        url: "/sys/set_mantenimiento_canal_venta.php",
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
            auditoria_send({ "respuesta": "sec_mantenimiento_canal_venta_listar_servicios", "data": respuesta });
            
            if(parseInt(respuesta.http_code) == 400)
            {
                if(parseInt(respuesta.codigo) == 1)
                {

                    var html = '<option value="0">-- Seleccione una opción --</option>';
                    html += '<option value="">' + respuesta.texto +'</option>';
                    $("#form_modal_sec_mantenimiento_canal_venta_param_servicio").html(html).trigger("change");

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
                    html += '<option value=' + array_servicios[0][i].servicio_id  + '>' + array_servicios[0][i].nombre +'</option>';
                }

                $("#form_modal_sec_mantenimiento_canal_venta_param_servicio").html(html).trigger("change");

                return false;
            }
        },
        error: function() {}
    });
}

const sec_mantenimiento_canal_venta_limpiar_input = () => {
	$('#form_modal_sec_mantenimiento_canal_venta_param_id').val(0);
	$("#form_modal_sec_mantenimiento_canal_venta_param_servicio").val(0).trigger("change.select2");
	$("#form_modal_sec_mantenimiento_canal_venta_param_aplica_liquidacion").val("").trigger("change.select2");
	$('#form_modal_sec_mantenimiento_canal_venta_param_nombre').val("");
	$('#form_modal_sec_mantenimiento_canal_venta_param_codigo').val("");
	$('#form_modal_sec_mantenimiento_canal_venta_param_descripcion').val("");
	$('#form_modal_sec_mantenimiento_canal_venta_param_color_hexadecimal').val("");
    $("#form_modal_sec_mantenimiento_canal_venta_param_aplica_pago_manual").val(1).trigger("change.select2");
    $("#form_modal_sec_mantenimiento_canal_venta_param_estado").val(1).trigger("change.select2");
}

const form_modal_sec_mantenimiento_canal_venta_btn_guardar = () => {
	
	var param_canal_venta_id = $('#form_modal_sec_mantenimiento_canal_venta_param_id').val();
	var param_servicio = $('#form_modal_sec_mantenimiento_canal_venta_param_servicio').val();
	var param_aplica_liquidacion = $('#form_modal_sec_mantenimiento_canal_venta_param_aplica_liquidacion').val();
	var param_nombre = $('#form_modal_sec_mantenimiento_canal_venta_param_nombre').val().trim();
	var param_codigo = $('#form_modal_sec_mantenimiento_canal_venta_param_codigo').val().trim();
	var param_descripcion = $('#form_modal_sec_mantenimiento_canal_venta_param_descripcion').val().trim();
	var param_color_hexadecimal = $('#form_modal_sec_mantenimiento_canal_venta_param_color_hexadecimal').val().trim();

	var accion = "";
	var titulo = "";

	if(param_canal_venta_id == "")
    {
        alertify.error('Ocurrió un error, vuelve a refrescar la página',5);
        return false;
    }
    else if(param_canal_venta_id == "0")
    {
    	// CREAR
    	accion = "mantenimiento_canal_venta_nuevo";
    	titulo = "registrar";
    }
    else if(param_canal_venta_id != "0")
    {
    	// EDITAR
    	if(param_canal_venta_id == global_param_canal_venta_id)
    	{
    		accion = "mantenimiento_canal_venta_editar";
    		titulo = "editar";
    	}
    	else
    	{
    		alertify.error('Ocurrió un error, no manipular datos, vuelve a refrescar la página',5);
        	return false;
    	}
    }

    if(param_servicio == "0")
    {
        alertify.error('Seleccione Servicio',5);
        $("#form_modal_sec_mantenimiento_canal_venta_param_servicio").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_mantenimiento_canal_venta_param_servicio').select2('open');
        }, 200);

        return false;
    }

    if(param_aplica_liquidacion == "")
    {
        alertify.error('Seleccione Aplica Liquidación',5);
        $("#form_modal_sec_mantenimiento_canal_venta_param_aplica_liquidacion").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_mantenimiento_canal_venta_param_aplica_liquidacion').select2('open');
        }, 200);

        return false;
    }

    if(param_nombre.length == 0)
    {
        alertify.error('Ingrese Nombre',5);
        $("#form_modal_sec_mantenimiento_canal_venta_param_nombre").focus();
       	return false;
    }

    if(param_codigo.length == 0)
    {
        alertify.error('Ingrese Código',5);
        $("#form_modal_sec_mantenimiento_canal_venta_param_codigo").focus();
       	return false;
    }

    if(param_descripcion.length == 0)
    {
        alertify.error('Ingrese Descripción',5);
        $("#form_modal_sec_mantenimiento_canal_venta_param_descripcion").focus();
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
            var dataForm = new FormData($("#form_modal_sec_mantenimiento_canal_venta")[0]);
            dataForm.append("accion",accion);
            
            $.ajax({
                url: "sys/set_mantenimiento_canal_venta.php",
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
                    auditoria_send({ "respuesta": "form_modal_sec_mantenimiento_canal_venta_btn_guardar", "data": respuesta });
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
                                $("#sec_mantenimiento_canal_venta_modal_nuevo_canal_venta").modal("hide");
                                swal.close();
                                sec_mantenimiento_canal_venta_listar_canal_venta();

                                return true;
                            }
                            else
                            {
                                setTimeout(function() {
                                    $("#sec_mantenimiento_canal_venta_modal_nuevo_canal_venta").modal("hide");
                                    swal.close();
                                    sec_mantenimiento_canal_venta_listar_canal_venta();

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

const sec_mantenimiento_canal_venta_obtener_canal_venta = (param_canal_venta_id) => {
    var data = {
        "accion": "sec_mantenimiento_canal_venta_obtener_canal_venta",
        "param_canal_venta_id": param_canal_venta_id
    }

    $.ajax({
        url: "sys/set_mantenimiento_canal_venta.php",
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
            auditoria_send({ "respuesta": "sec_mantenimiento_canal_venta_obtener_canal_venta", "data": respuesta });
            if(parseInt(respuesta.http_code) == 200)
            {
                var data_back = respuesta.texto;

                $('#form_modal_sec_mantenimiento_canal_venta_param_id').val(data_back[0].id);
                global_param_canal_venta_id = data_back[0].id;
                $("#form_modal_sec_mantenimiento_canal_venta_param_servicio").val(data_back[0].servicio_id).trigger("change.select2");
                $("#form_modal_sec_mantenimiento_canal_venta_param_aplica_liquidacion").val(data_back[0].en_liquidacion).trigger("change.select2");
                $('#form_modal_sec_mantenimiento_canal_venta_param_nombre').val(data_back[0].nombre);
                $('#form_modal_sec_mantenimiento_canal_venta_param_codigo').val(data_back[0].codigo);
                $('#form_modal_sec_mantenimiento_canal_venta_param_descripcion').val(data_back[0].descripcion);
                $('#form_modal_sec_mantenimiento_canal_venta_param_color_hexadecimal').val(data_back[0].hex_color);
                $('#form_modal_sec_mantenimiento_canal_venta_param_aplica_pago_manual').val(data_back[0].pago_manual);
                $('#form_modal_sec_mantenimiento_canal_venta_param_estado').val(data_back[0].estado);
                
                $("#sec_mantenimiento_canal_venta_modal_nuevo_canal_venta_titulo").text("Editar Canal de Venta");
                $("#sec_mantenimiento_canal_venta_modal_nuevo_canal_venta").modal("show");
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

const sec_mantenimiento_canal_venta_activar_desactivar_canal_venta = (param_canal_venta_id, param_valor) => {
    
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
                "accion" : "sec_mantenimiento_canal_venta_activar_desactivar_canal_venta",
                "param_canal_venta_id" : param_canal_venta_id,
                "param_valor" : param_valor
            }
            
            $.ajax({
                url: "sys/set_mantenimiento_canal_venta.php",
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
                    auditoria_send({ "respuesta": "sec_mantenimiento_canal_venta_activar_desactivar_canal_venta", "data": respuesta });
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
                                sec_mantenimiento_canal_venta_listar_canal_venta();

                                return true;
                            }
                            else
                            {
                                setTimeout(function() {
                                    swal.close();
                                    sec_mantenimiento_canal_venta_listar_canal_venta();

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