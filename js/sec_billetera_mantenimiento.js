var global_param_billetera_id = 0;
var global_param_billetera_cuenta_id = 0;
var global_param_billetera_telefono_id = 0;
var global_param_billetera_motivo_rechazo_id = 0;

//INICIO FUNCIONES INICIALIZADOS
const sec_billetera_mantenimiento = () => {

    // INICIO FORMATO COMBO CON BUSQUEDA
    $(".sec_billetera_mantenimiento_select_filtro").select2({ width: '100%' });
    // FIN FORMATO COMBO CON BUSQUEDA

    // INICIO: SOLO NUMEROS
    $('.sec_billetera_mantenimiento_solo_numero').on('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    // INICIO: SOLO NUMEROS

	sec_billetera_mantenimiento_grupo_billetera_listar_billetera();
	sec_billetera_mantenimiento_grupo_billetera_cuenta_listar_billetera_cuenta();
    sec_billetera_mantenimiento_grupo_billetera_telefono_listar_billetera_telefono();
    sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_listar_billetera_motivo_rechazo();
}
//FIN FUNCIONES INICIALIZADOS

const sec_billetera_mantenimiento_grupo_billetera_listar_billetera = () => {
    
    if(sec_id == "billetera" && sub_sec_id == "mantenimiento")
    {
		var data = {
            "accion": "sec_billetera_mantenimiento_grupo_billetera_listar_billetera"
        }

        tabla = $("#sec_billetera_mantenimiento_grupo_billetera_div_tabla_datatable").dataTable(
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
                url : "/sys/set_billetera_mantenimiento.php",
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
            aLengthMenu:[10, 20, 30, 40, 50],
            "order" : 
            [
                0, "desc"   
            ]
        }).DataTable();
    }
}

const sec_billetera_mantenimiento_grupo_billetera_nuevo = () => {

    sec_billetera_mantenimiento_grupo_billetera_limpiar_input();

	$("#sec_billetera_mantenimiento_grupo_billetera_modal_billetera .btn_guardar").text("Guardar");
    $("#title_modal_sec_billetera_mantenimiento_grupo_billetera_modal_billetera").text("Nueva Billetera");
    $("#sec_billetera_mantenimiento_grupo_billetera_modal_billetera").modal("show");
}

const sec_billetera_mantenimiento_grupo_billetera_limpiar_input = () => {
    $('#sec_billetera_mantenimiento_grupo_billetera_modal_billetera_param_id').val("");
    $('#sec_billetera_mantenimiento_grupo_billetera_modal_billetera_param_nombre').val("");
    $('#sec_billetera_mantenimiento_grupo_billetera_modal_billetera_param_descripcion').val("");
}

const sec_billetera_mantenimiento_grupo_billetera_guardar = () => {
	
	var param_id = $('#sec_billetera_mantenimiento_grupo_billetera_modal_billetera_param_id').val();
    var param_nombre = $('#sec_billetera_mantenimiento_grupo_billetera_modal_billetera_param_nombre').val().trim();
    var param_descripcion = $('#sec_billetera_mantenimiento_grupo_billetera_modal_billetera_param_descripcion').val().trim();

    let tipo_accion = 0;
    var title = "";

    if(param_id == "")
    {
        // TIPO INSERCION
        tipo_accion = 1;
        title = '¿Está seguro de guardar?';
    }
    else if(param_id != "")
    {
        // TIPO ACTUALIZACION
        // EDITAR
    	if(param_id == global_param_billetera_id)
    	{
    		tipo_accion = 2;
        	title = '¿Está seguro de editar?';
    	}
    	else
    	{
    		alertify.error('Ocurrió un error, no manipular datos, vuelve a refrescar la página',5);
        	return false;
    	}
    }

    if(param_nombre == "")
    {
        alertify.error('Ingrese Nombre',5);
        return false;
    }

    if(param_descripcion == "")
    {
        alertify.error('Ingrese Descripción',5);
        return false;
    }

    swal(
    {
        title: title,
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    function(isConfirm)
    {
        if (isConfirm) 
        {
            var dataForm = new FormData($("#form_sec_billetera_mantenimiento_grupo_billetera_modal_guardar_o_editar_billetera")[0]);
            dataForm.append("accion","sec_billetera_mantenimiento_grupo_billetera_guardar");
            dataForm.append("param_id", param_id);
            dataForm.append("param_nombre", param_nombre);
            dataForm.append("param_descripcion", param_descripcion);
            dataForm.append("tipo_accion", tipo_accion);

            $.ajax({
                url: "sys/set_billetera_mantenimiento.php",
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function( xhr ) {
                    loading(true);
                },
                success: function(data){
                    
                    var respuesta = JSON.parse(data);
                    auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_guardar", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200) 
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "success",
                            timer: 6000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        },
                        function (isConfirm) {
                            swal.close();
                            $("#sec_billetera_mantenimiento_grupo_billetera_modal_billetera").modal("hide");
                            sec_billetera_mantenimiento_grupo_billetera_listar_billetera();
                        });
                        
                        return true;
                    }
                    else
                    {
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
                },
                complete: function(){
                    loading(false);
                }
            });
        }
    });
}

const sec_billetera_mantenimiento_grupo_billetera_obtener_billetera = (param_billetera_id) => {
    
    var data = {
        "accion": "sec_billetera_mantenimiento_grupo_billetera_obtener_billetera",
        "param_billetera_id": param_billetera_id
    }

    $.ajax({
        url: "sys/set_billetera_mantenimiento.php",
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
            auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_obtener_billetera", "data": respuesta });
            if(parseInt(respuesta.http_code) == 200)
            {
                var data_back = respuesta.data;

                $('#sec_billetera_mantenimiento_grupo_billetera_modal_billetera_param_id').val(data_back[0].id);
                global_param_billetera_id = data_back[0].id;
                $('#sec_billetera_mantenimiento_grupo_billetera_modal_billetera_param_nombre').val(data_back[0].nombre);
                $('#sec_billetera_mantenimiento_grupo_billetera_modal_billetera_param_descripcion').val(data_back[0].descripcion);
                
                $("#title_modal_sec_billetera_mantenimiento_grupo_billetera_modal_billetera").text("Editar Billetera");
                $("#sec_billetera_mantenimiento_grupo_billetera_modal_billetera").modal("show");
            }
            else
            {
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
        }
    });
}

const sec_billetera_mantenimiento_grupo_billetera_activar_desactivar_billetera = (param_billetera_id, param_valor) => {
    
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
                "accion" : "sec_billetera_mantenimiento_grupo_billetera_activar_desactivar_billetera",
                "param_billetera_id" : param_billetera_id,
                "param_valor" : param_valor
            }
            
            $.ajax({
                url: "sys/set_billetera_mantenimiento.php",
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
                    auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_activar_desactivar_billetera", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200)
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
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
                                sec_billetera_mantenimiento_grupo_billetera_listar_billetera();

                                return true;
                            }
                            else
                            {
                                setTimeout(function() {
                                    swal.close();
                                    sec_billetera_mantenimiento_grupo_billetera_listar_billetera();

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
                            text: respuesta.descripcion,
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

const sec_billetera_mantenimiento_grupo_billetera_cuenta_listar_billetera_cuenta = () => {
    
    if(sec_id == "billetera" && sub_sec_id == "mantenimiento")
    {
		var data = {
            "accion": "sec_billetera_mantenimiento_grupo_billetera_cuenta_listar_billetera_cuenta"
        }

        tabla = $("#sec_billetera_mantenimiento_grupo_billetera_cuenta_div_tabla_datatable").dataTable(
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
                url : "/sys/set_billetera_mantenimiento.php",
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
                    targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                }
            ],
            "bDestroy" : true,
            aLengthMenu:[10, 20, 30, 40, 50],
            "order" : 
            [
                0, "desc"   
            ]
        }).DataTable();
    }
}

const sec_billetera_mantenimiento_grupo_billetera_cuenta_nuevo = () => {
    
    sec_billetera_mantenimiento_grupo_billetera_cuenta_limpiar_input();

    $("#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera .btn_guardar").text("Guardar");
    $("#title_modal_sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera").text("Nueva Billetera Cuenta");
    $("#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera").modal("show");
}

const sec_billetera_mantenimiento_grupo_billetera_cuenta_limpiar_input = () => {
    $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_id').val("");
    $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_num_cuenta').val("");
    $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_num_cuenta_cci').val("");
    $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_nombre_corto').val("");
    $("#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_tipo_banco").val(0).trigger("change.select2");
    $("#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_tipo_empresa").val(0).trigger("change.select2");
}

const sec_billetera_mantenimiento_grupo_billetera_cuenta_guardar = () => {
    
    var param_id = $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_id').val();
    var param_num_cuenta = $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_num_cuenta').val().trim();
    var param_num_cuenta_cci = $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_num_cuenta_cci').val().trim();
    var param_nombre_corto = $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_nombre_corto').val().trim();
    var param_tipo_banco = $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_tipo_banco').val();
    var param_tipo_empresa = $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_tipo_empresa').val();

    let tipo_accion = 0;
    var title = "";

    if(param_id == "")
    {
        // TIPO INSERCION
        tipo_accion = 1;
        title = '¿Está seguro de guardar?';
    }
    else if(param_id != "")
    {
        // TIPO ACTUALIZACION
        // EDITAR
        if(param_id == global_param_billetera_cuenta_id)
        {
            tipo_accion = 2;
            title = '¿Está seguro de editar?';
        }
        else
        {
            alertify.error('Ocurrió un error, no manipular datos, vuelve a refrescar la página',5);
            return false;
        }
    }

    if(param_num_cuenta == "")
    {
        alertify.error('Ingrese Nº Cuenta',5);
        return false;
    }

    if(param_num_cuenta_cci == "")
    {
        alertify.error('Ingrese Nº Cuenta CCI',5);
        return false;
    }

    if(param_nombre_corto == "")
    {
        alertify.error('Ingrese Nombre Corto',5);
        return false;
    }

    if(param_tipo_banco == "0")
    {
        alertify.error('Seleccione Tipo Banco',5);
        $("#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_tipo_banco").focus();
        setTimeout(function() 
        {
            $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_tipo_banco').select2('open');
        }, 200);

        return false;
    }

    if(param_tipo_empresa == "0")
    {
        alertify.error('Seleccione Tipo Empresa',5);
        $("#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_tipo_empresa").focus();
        setTimeout(function() 
        {
            $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_tipo_empresa').select2('open');
        }, 200);
        
        return false;
    }

    swal(
    {
        title: title,
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    function(isConfirm)
    {
        if (isConfirm) 
        {
            var dataForm = new FormData($("#form_sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_guardar_o_editar_billetera_cuenta")[0]);
            dataForm.append("accion","sec_billetera_mantenimiento_grupo_billetera_cuenta_guardar");
            dataForm.append("param_id", param_id);
            dataForm.append("param_num_cuenta", param_num_cuenta);
            dataForm.append("param_num_cuenta_cci", param_num_cuenta_cci);
            dataForm.append("param_nombre_corto", param_nombre_corto);
            dataForm.append("param_tipo_banco", param_tipo_banco);
            dataForm.append("param_tipo_empresa", param_tipo_empresa);
            dataForm.append("tipo_accion", tipo_accion);

            $.ajax({
                url: "sys/set_billetera_mantenimiento.php",
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function( xhr ) {
                    loading(true);
                },
                success: function(data){
                    
                    var respuesta = JSON.parse(data);
                    auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_cuenta_guardar", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200) 
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "success",
                            timer: 6000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        },
                        function (isConfirm) {
                            swal.close();
                            $("#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera").modal("hide");
                            sec_billetera_mantenimiento_grupo_billetera_cuenta_listar_billetera_cuenta();
                        });
                        
                        return true;
                    }
                    else
                    {
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
                },
                complete: function(){
                    loading(false);
                }
            });
        }
    });
}

const sec_billetera_mantenimiento_grupo_billetera_cuenta_obtener_billetera_cuenta = (param_billetera_cuenta_id) => {
    
    var data = {
        "accion": "sec_billetera_mantenimiento_grupo_billetera_cuenta_obtener_billetera_cuenta",
        "param_billetera_cuenta_id": param_billetera_cuenta_id
    }

    $.ajax({
        url: "sys/set_billetera_mantenimiento.php",
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
            auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_cuenta_obtener_billetera_cuenta", "data": respuesta });
            if(parseInt(respuesta.http_code) == 200)
            {
                var data_back = respuesta.data;

                $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_id').val(data_back[0].id);
                global_param_billetera_cuenta_id = data_back[0].id;
                $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_num_cuenta').val(data_back[0].numero_cuenta);
                $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_num_cuenta_cci').val(data_back[0].numero_cci);
                $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_nombre_corto').val(data_back[0].nombre_corto);
                $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_tipo_banco').val(data_back[0].banco_id).trigger("change.select2");
                $('#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera_param_tipo_empresa').val(data_back[0].razon_social_id).trigger("change.select2");
                
                $("#title_modal_sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera").text("Editar Billetera Cuenta");
                $("#sec_billetera_mantenimiento_grupo_billetera_cuenta_modal_billetera").modal("show");
            }
            else
            {
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
        }
    });
}

const sec_billetera_mantenimiento_grupo_billetera_cuenta_activar_desactivar_billetera_cuenta = (param_billetera_cuenta_id, param_valor) => {
    
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
                "accion" : "sec_billetera_mantenimiento_grupo_billetera_cuenta_activar_desactivar_billetera_cuenta",
                "param_billetera_cuenta_id" : param_billetera_cuenta_id,
                "param_valor" : param_valor
            }
            
            $.ajax({
                url: "sys/set_billetera_mantenimiento.php",
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
                    auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_cuenta_activar_desactivar_billetera_cuenta", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200)
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
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
                                sec_billetera_mantenimiento_grupo_billetera_cuenta_listar_billetera_cuenta();

                                return true;
                            }
                            else
                            {
                                setTimeout(function() {
                                    swal.close();
                                    sec_billetera_mantenimiento_grupo_billetera_cuenta_listar_billetera_cuenta();

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
                            text: respuesta.descripcion,
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

const sec_billetera_mantenimiento_grupo_billetera_telefono_listar_billetera_telefono = () => {
    
    if(sec_id == "billetera" && sub_sec_id == "mantenimiento")
    {
        var data = {
            "accion": "sec_billetera_mantenimiento_grupo_billetera_telefono_listar_billetera_telefono"
        }

        tabla = $("#sec_billetera_mantenimiento_grupo_billetera_telefono_div_tabla_datatable").dataTable(
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
                url : "/sys/set_billetera_mantenimiento.php",
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
                    targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                }
            ],
            "bDestroy" : true,
            aLengthMenu:[10, 20, 30, 40, 50],
            "order" : 
            [
                0, "desc"   
            ]
        }).DataTable();
    }
}

const sec_billetera_mantenimiento_grupo_billetera_telefono_nuevo = () => {
    
    sec_billetera_mantenimiento_grupo_billetera_telefono_limpiar_input();

    $("#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera .btn_guardar").text("Guardar");
    $("#title_modal_sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera").text("Nueva Billetera Teléfono");
    $("#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera").modal("show");
}

const sec_billetera_mantenimiento_grupo_billetera_telefono_limpiar_input = () => {
    $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_id').val("");
    $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_telefono').val("");
    $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_descripcion').val("");
    $("#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_numero_cuenta").val(0).trigger("change.select2");
    $("#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_tipo_billetera").val(0).trigger("change.select2");
}

const sec_billetera_mantenimiento_grupo_billetera_telefono_guardar = () => {
    
    var param_id = $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_id').val();
    var param_telefono = $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_telefono').val().trim();
    var param_descripcion = $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_descripcion').val().trim();
    var param_numero_cuenta = $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_numero_cuenta').val();
    var param_tipo_billetera = $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_tipo_billetera').val();

    let tipo_accion = 0;
    var title = "";

    if(param_id == "")
    {
        // TIPO INSERCION
        tipo_accion = 1;
        title = '¿Está seguro de guardar?';
    }
    else if(param_id != "")
    {
        // TIPO ACTUALIZACION
        // EDITAR
        if(param_id == global_param_billetera_telefono_id)
        {
            tipo_accion = 2;
            title = '¿Está seguro de editar?';
        }
        else
        {
            alertify.error('Ocurrió un error, no manipular datos, vuelve a refrescar la página',5);
            return false;
        }
    }

    if(param_telefono == "")
    {
        alertify.error('Ingrese Teléfono',5);
        return false;
    }

    if(param_telefono.length < "9" || param_telefono.length > "9")
    {
        alertify.error('Ingrese Teléfono solo 9 digitos',5);
        return false;
    }

    if(param_descripcion == "")
    {
        alertify.error('Ingrese Descripción',5);
        return false;
    }

    if(param_numero_cuenta == "0")
    {
        alertify.error('Seleccione Número Cuenta',5);
        $("#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_numero_cuenta").focus();
        setTimeout(function() 
        {
            $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_numero_cuenta').select2('open');
        }, 200);

        return false;
    }

    if(param_tipo_billetera == "0")
    {
        alertify.error('Seleccione Tipo Billetera',5);
        $("#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_tipo_billetera").focus();
        setTimeout(function() 
        {
            $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_tipo_billetera').select2('open');
        }, 200);
        
        return false;
    }

    swal(
    {
        title: title,
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    function(isConfirm)
    {
        if (isConfirm) 
        {
            var dataForm = new FormData($("#form_sec_billetera_mantenimiento_grupo_billetera_telefono_modal_guardar_o_editar_billetera_telefono")[0]);
            dataForm.append("accion","sec_billetera_mantenimiento_grupo_billetera_telefono_guardar");
            dataForm.append("param_id", param_id);
            dataForm.append("param_telefono", param_telefono);
            dataForm.append("param_descripcion", param_descripcion);
            dataForm.append("param_numero_cuenta", param_numero_cuenta);
            dataForm.append("param_tipo_billetera", param_tipo_billetera);
            dataForm.append("tipo_accion", tipo_accion);

            $.ajax({
                url: "sys/set_billetera_mantenimiento.php",
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function( xhr ) {
                    loading(true);
                },
                success: function(data){
                    
                    var respuesta = JSON.parse(data);
                    auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_telefono_guardar", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200) 
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "success",
                            timer: 6000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        },
                        function (isConfirm) {
                            swal.close();
                            $("#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera").modal("hide");
                            sec_billetera_mantenimiento_grupo_billetera_telefono_listar_billetera_telefono();
                        });
                        
                        return true;
                    }
                    else
                    {
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
                },
                complete: function(){
                    loading(false);
                }
            });
        }
    });
}

const sec_billetera_mantenimiento_grupo_billetera_telefono_obtener_billetera_telefono = (param_billetera_telefono_id) => {
    
    var data = {
        "accion": "sec_billetera_mantenimiento_grupo_billetera_telefono_obtener_billetera_telefono",
        "param_billetera_telefono_id": param_billetera_telefono_id
    }

    $.ajax({
        url: "sys/set_billetera_mantenimiento.php",
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
            auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_telefono_obtener_billetera_telefono", "data": respuesta });
            if(parseInt(respuesta.http_code) == 200)
            {
                var data_back = respuesta.data;

                $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_id').val(data_back[0].id);
                global_param_billetera_telefono_id = data_back[0].id;
                $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_telefono').val(data_back[0].numero_telefono);
                $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_descripcion').val(data_back[0].descripcion);
                $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_numero_cuenta').val(data_back[0].billetera_cuenta_id).trigger("change.select2");
                $('#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera_param_tipo_billetera').val(data_back[0].billetera_id).trigger("change.select2");
                
                $("#title_modal_sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera").text("Editar Billetera Cuenta");
                $("#sec_billetera_mantenimiento_grupo_billetera_telefono_modal_billetera").modal("show");
            }   
            else
            {
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
        }
    });
}

const sec_billetera_mantenimiento_grupo_billetera_telefono_activar_desactivar_billetera_telefono = (param_billetera_telefono_id, param_valor) => {
    
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
                "accion" : "sec_billetera_mantenimiento_grupo_billetera_telefono_activar_desactivar_billetera_telefono",
                "param_billetera_telefono_id" : param_billetera_telefono_id,
                "param_valor" : param_valor
            }
            
            $.ajax({
                url: "sys/set_billetera_mantenimiento.php",
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
                    auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_telefono_activar_desactivar_billetera_telefono", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200)
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
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
                                sec_billetera_mantenimiento_grupo_billetera_telefono_listar_billetera_telefono();

                                return true;
                            }
                            else
                            {
                                setTimeout(function() {
                                    swal.close();
                                    sec_billetera_mantenimiento_grupo_billetera_telefono_listar_billetera_telefono();

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
                            text: respuesta.descripcion,
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

const sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_listar_billetera_motivo_rechazo = () => {
    
    if(sec_id == "billetera" && sub_sec_id == "mantenimiento")
    {
        var data = {
            "accion": "sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_listar_billetera_motivo_rechazo"
        }

        tabla = $("#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_div_tabla_datatable").dataTable(
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
                url : "/sys/set_billetera_mantenimiento.php",
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
            aLengthMenu:[10, 20, 30, 40, 50],
            "order" : 
            [
                0, "desc"   
            ]
        }).DataTable();
    }
}

const sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_nuevo = () => {
    sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_limpiar_input();

    $("#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera .btn_guardar").text("Guardar");
    $("#title_modal_sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera").text("Nueva Billetera Motivo Rechazo");
    $("#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera").modal("show");
}

const sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_limpiar_input = () => {
    $('#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera_param_id').val("");
    $('#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera_param_nombre').val("");
    $('#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera_param_descripcion').val("");
}

const sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_guardar = () => {
    
    var param_id = $('#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera_param_id').val();
    var param_nombre = $('#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera_param_nombre').val().trim();
    var param_descripcion = $('#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera_param_descripcion').val().trim();

    let tipo_accion = 0;
    var title = "";

    if(param_id == "")
    {
        // TIPO INSERCION
        tipo_accion = 1;
        title = '¿Está seguro de guardar?';
    }
    else if(param_id != "")
    {
        // TIPO ACTUALIZACION
        // EDITAR
        if(param_id == global_param_billetera_motivo_rechazo_id)
        {
            tipo_accion = 2;
            title = '¿Está seguro de editar?';
        }
        else
        {
            alertify.error('Ocurrió un error, no manipular datos, vuelve a refrescar la página',5);
            return false;
        }
    }

    if(param_nombre == "")
    {
        alertify.error('Ingrese Nombre',5);
        return false;
    }

    if(param_descripcion == "")
    {
        alertify.error('Ingrese Descripción',5);
        return false;
    }

    swal(
    {
        title: title,
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    function(isConfirm)
    {
        if (isConfirm) 
        {
            var dataForm = new FormData($("#form_sec_billetera_mantenimiento_grupo_billetera_modal_guardar_o_editar_billetera")[0]);
            dataForm.append("accion","sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_guardar");
            dataForm.append("param_id", param_id);
            dataForm.append("param_nombre", param_nombre);
            dataForm.append("param_descripcion", param_descripcion);
            dataForm.append("tipo_accion", tipo_accion);

            $.ajax({
                url: "sys/set_billetera_mantenimiento.php",
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function( xhr ) {
                    loading(true);
                },
                success: function(data){
                    
                    var respuesta = JSON.parse(data);
                    auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_guardar", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200) 
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "success",
                            timer: 6000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        },
                        function (isConfirm) {
                            swal.close();
                            $("#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera").modal("hide");
                            sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_listar_billetera_motivo_rechazo();
                        });
                        
                        return true;
                    }
                    else
                    {
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
                },
                complete: function(){
                    loading(false);
                }
            });
        }
    });
}

const sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_obtener_billetera_motivo_rechazo = (param_billetera_motivo_rechazo_id) => {
    
    var data = {
        "accion": "sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_obtener_billetera_motivo_rechazo",
        "param_billetera_motivo_rechazo_id": param_billetera_motivo_rechazo_id
    }

    $.ajax({
        url: "sys/set_billetera_mantenimiento.php",
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
            auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_obtener_billetera_motivo_rechazo", "data": respuesta });
            if(parseInt(respuesta.http_code) == 200)
            {
                var data_back = respuesta.data;

                $('#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera_param_id').val(data_back[0].id);
                global_param_billetera_motivo_rechazo_id = data_back[0].id;
                $('#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera_param_nombre').val(data_back[0].nombre);
                $('#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera_param_descripcion').val(data_back[0].descripcion);
                $("#title_modal_sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera").text("Editar Billetera Motivo Rechazo");
                $("#sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_modal_billetera").modal("show");
            }
            else
            {
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
        }
    });
}

const sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_activar_desactivar_billetera_motivo_rechazo = (param_billetera_motivo_rechazo_id, param_valor) => {
    
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
                "accion" : "sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_activar_desactivar_billetera_motivo_rechazo",
                "param_billetera_motivo_rechazo_id" : param_billetera_motivo_rechazo_id,
                "param_valor" : param_valor
            }
            
            $.ajax({
                url: "sys/set_billetera_mantenimiento.php",
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
                    auditoria_send({ "respuesta": "sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_activar_desactivar_billetera_motivo_rechazo", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200)
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
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
                                sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_listar_billetera_motivo_rechazo();

                                return true;
                            }
                            else
                            {
                                setTimeout(function() {
                                    swal.close();
                                    sec_billetera_mantenimiento_grupo_billetera_motivo_rechazo_listar_billetera_motivo_rechazo();

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
                            text: respuesta.descripcion,
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