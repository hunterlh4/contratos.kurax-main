var global_param_canal_caja_id = 0;

//INICIO: FUNCIONES INICIALIZADOS
function sec_mantenimiento_canal_caja()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mantenimiento_canal_caja_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	sec_mantenimiento_canal_caja_listar_canal_caja();
    sec_mantenimiento_canal_caja_listar_canal_venta();
    
}
//FIN: FUNCIONES INICIALIZADOS

function sec_mantenimiento_canal_caja_listar_canal_caja()
{
    if(sec_id == "mantenimiento" && sub_sec_id == "canal_caja")
    {
        var data = {
            "accion": "sec_mantenimiento_canal_caja_listar_canal_caja"
        }

        tabla = $("#sec_mantenimiento_canal_caja_div_listar_canal_caja_datatable").dataTable(
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
                    url : "/sys/set_mantenimiento_canal_caja.php",
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
                        targets: [0, 1, 2, 3, 4, 5, 6, 7, 8]
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

const sec_mantenimiento_canal_caja_listar_canal_venta = () => {

	var data = {
        "accion": "sec_mantenimiento_canal_caja_listar_canal_venta"
    }

    var array_servicios = [];
    
    $.ajax({
        url: "/sys/set_mantenimiento_canal_caja.php",
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
                    $("#form_modal_sec_mantenimiento_canal_caja_param_canal_venta").html(html).trigger("change");

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

                $("#form_modal_sec_mantenimiento_canal_caja_param_canal_venta").html(html).trigger("change");

                return false;
            }
        },
        error: function() {}
    });
}

const sec_mantenimiento_canal_caja_btn_nuevo = () => {

	sec_mantenimiento_canal_caja_limpiar_input();
	
	// INICIO: AGREGAR OPCIONES AL SELECTED In
	
	var select = document.getElementById("form_modal_sec_mantenimiento_canal_caja_param_in");
	while(select.options.length > 1)
	{
		select.remove(1);
	}

	var opcion = document.createElement("option");
	opcion.text = "Si";
	opcion.value = "1";
	select.add(opcion);

	// FIN: AGREGAR OPCIONES AL SELECTED In
    
    $("#sec_mantenimiento_canal_caja_modal_nuevo_canal_caja_titulo").text("Registro de Tipo Canal de Caja");
    $("#sec_mantenimiento_canal_caja_modal_nuevo_canal_caja").modal("show");
}

const sec_mantenimiento_canal_caja_limpiar_input = () => {
	$('#form_modal_sec_mantenimiento_canal_caja_param_id').val(0);
	$("#form_modal_sec_mantenimiento_canal_caja_param_canal_venta").val(0).trigger("change.select2");
	$('#form_modal_sec_mantenimiento_canal_caja_param_nombre').val("");
	$('#form_modal_sec_mantenimiento_canal_caja_param_descripcion').val("");
	$("#form_modal_sec_mantenimiento_canal_caja_param_in").val("").trigger("change.select2");
	$("#form_modal_sec_mantenimiento_canal_caja_param_out").val("").trigger("change.select2");
	$('#form_modal_sec_mantenimiento_canal_caja_param_ord').val("");
}

const form_modal_sec_mantenimiento_canal_caja_btn_guardar = () => {
	
	var param_canal_caja_id = $('#form_modal_sec_mantenimiento_canal_caja_param_id').val();
	var param_canal_venta = $('#form_modal_sec_mantenimiento_canal_caja_param_canal_venta').val();
	var param_nombre = $('#form_modal_sec_mantenimiento_canal_caja_param_nombre').val().trim();
	var param_descripcion = $('#form_modal_sec_mantenimiento_canal_caja_param_descripcion').val().trim();
	var param_in = $('#form_modal_sec_mantenimiento_canal_caja_param_in').val().trim();
	var param_out = $('#form_modal_sec_mantenimiento_canal_caja_param_out').val().trim();
	
	var accion = "";
	var titulo = "";

	if(param_canal_caja_id == "")
    {
        alertify.error('Ocurrió un error, vuelve a refrescar la página',5);
        return false;
    }
    else if(param_canal_caja_id == "0")
    {
    	// CREAR
    	accion = "mantenimiento_canal_caja_nuevo";
    	titulo = "registrar";
    }
    else if(param_canal_caja_id != "0")
    {
    	// EDITAR
    	if(param_canal_caja_id == global_param_canal_caja_id)
    	{
    		accion = "mantenimiento_canal_caja_editar";
    		titulo = "editar";
    	}
    	else
    	{
    		alertify.error('Ocurrió un error, no manipular datos, vuelve a refrescar la página',5);
        	return false;
    	}
    }

    if(param_canal_venta == "0")
    {
        alertify.error('Seleccione Canal de Venta',5);
        $("#form_modal_sec_mantenimiento_canal_caja_param_canal_venta").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_mantenimiento_canal_caja_param_canal_venta').select2('open');
        }, 200);

        return false;
    }

    if(param_nombre.length == 0)
    {
        alertify.error('Ingrese Nombre',5);
        $("#form_modal_sec_mantenimiento_canal_caja_param_nombre").focus();
       	return false;
    }
	
	if(param_descripcion.length == 0)
    {
        alertify.error('Ingrese Descripción',5);
        $("#form_modal_sec_mantenimiento_canal_caja_param_descripcion").focus();
       	return false;
    }

    if(param_in == "")
    {
        alertify.error('Seleccione In (entrada de dinero)',5);
        $("#form_modal_sec_mantenimiento_canal_caja_param_in").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_mantenimiento_canal_caja_param_in').select2('open');
        }, 200);

        return false;
    }

    if(param_out == "")
    {
        alertify.error('Seleccione Out (salida de dinero)',5);
        $("#form_modal_sec_mantenimiento_canal_caja_param_out").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_mantenimiento_canal_caja_param_out').select2('open');
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
            var dataForm = new FormData($("#form_modal_sec_mantenimiento_canal_caja")[0]);
            dataForm.append("accion",accion);
            
            $.ajax({
                url: "sys/set_mantenimiento_canal_caja.php",
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
                                $("#sec_mantenimiento_canal_caja_modal_nuevo_canal_caja").modal("hide");
                                swal.close();
                                sec_mantenimiento_canal_caja_listar_canal_caja();

                                return true;
                            }
                            else
                            {
                                setTimeout(function() {
                                    $("#sec_mantenimiento_canal_caja_modal_nuevo_canal_caja").modal("hide");
                                    swal.close();
                                    sec_mantenimiento_canal_caja_listar_canal_caja();

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

function sec_mantenimiento_canal_caja_obtener_canal_caja(param_canal_caja_id)
{
	
	// INICIO: AGREGAR OPCIONES AL SELECTED In
	var select = document.getElementById("form_modal_sec_mantenimiento_canal_caja_param_in");
	while(select.options.length > 1)
	{
		select.remove(1);
	}

	var opcion_uno = document.createElement("option");
	opcion_uno.text = "Si";
	opcion_uno.value = "1";
	select.add(opcion_uno);

	var opcion_dos = document.createElement("option");
	opcion_dos.text = "No";
	opcion_dos.value = "0";
	select.add(opcion_dos);
	// FIN: AGREGAR OPCIONES AL SELECTED In

    var data = {
        "accion": "sec_mantenimiento_canal_caja_obtener_canal_caja",
        "param_canal_caja_id": param_canal_caja_id
    }

    $.ajax({
        url: "sys/set_mantenimiento_canal_caja.php",
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
            auditoria_send({ "respuesta": "sec_mantenimiento_canal_caja_obtener_canal_caja", "data": respuesta });
            if(parseInt(respuesta.http_code) == 200)
            {
                var data_back = respuesta.texto;

                $('#form_modal_sec_mantenimiento_canal_caja_param_id').val(data_back[0].id);
                global_param_canal_caja_id = data_back[0].id;
                $("#form_modal_sec_mantenimiento_canal_caja_param_canal_venta").val(data_back[0].cdv_id).trigger("change.select2");
                $('#form_modal_sec_mantenimiento_canal_caja_param_nombre').val(data_back[0].nombre);
                $('#form_modal_sec_mantenimiento_canal_caja_param_descripcion').val(data_back[0].descripcion);
                $("#form_modal_sec_mantenimiento_canal_caja_param_in").val(data_back[0].in).trigger("change.select2");
                $("#form_modal_sec_mantenimiento_canal_caja_param_out").val(data_back[0].out).trigger("change.select2");
                $('#form_modal_sec_mantenimiento_canal_caja_param_ord').val(data_back[0].ord);
                
                $("#sec_mantenimiento_canal_caja_modal_nuevo_canal_caja_titulo").text("Editar Tipo Canal de Caja");
                $("#sec_mantenimiento_canal_caja_modal_nuevo_canal_caja").modal("show");
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

const sec_mantenimiento_canal_caja_activar_desactivar_canal_caja = (param_canal_caja_id, param_valor) => {
    
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
                "accion" : "sec_mantenimiento_canal_caja_activar_desactivar_canal_caja",
                "param_canal_caja_id" : param_canal_caja_id,
                "param_valor" : param_valor
            }
            
            $.ajax({
                url: "sys/set_mantenimiento_canal_caja.php",
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
                    auditoria_send({ "respuesta": "sec_mantenimiento_canal_caja_activar_desactivar_canal_caja", "data": respuesta });
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
                                sec_mantenimiento_canal_caja_listar_canal_caja();

                                return true;
                            }
                            else
                            {
                                setTimeout(function() {
                                    swal.close();
                                    sec_mantenimiento_canal_caja_listar_canal_caja();

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

const sec_mantenimiento_canal_caja_clic_abrir_modal = () => {
	alert("Holis");
}