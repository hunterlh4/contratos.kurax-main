var global_param_razon_social_id = 0;

// INICIO: FUNCIONES INICIALIZADOS
function sec_mantenimiento_razon_social()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mantenimiento_razon_social_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	sec_mantenimiento_razon_social_listar();
    sec_mantenimiento_razon_social_listar_canales();
    sec_mantenimiento_razon_social_listar_redes();
}
// FIN: FUNCIONES INICIALIZADOS

//////////   FUNCIONES PARA RAZON SOCIAL

function sec_mantenimiento_razon_social_listar()
{
	if(sec_id == "mantenimiento" && sub_sec_id == "razon_social")
    {
        var data = {
            "accion": "mantenimiento_razon_social_listar"
        }

        tabla = $("#sec_mantenimiento_razon_social_div_listar_datatable").dataTable(
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
                    url : "/sys/get_mantenimiento_razon_social.php",
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
                aLengthMenu:[10, 20, 30, 40, 50, 100]
            }
        ).DataTable();
    }
    }

$("#sec_mantenimiento_razon_social_btn_nuevo").off("click").on("click",function(){
    sec_mantenimiento_razon_social_limpiar_input();
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = false;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = false;
    });
    $("#accordionRazónSocialCamposAuditoria").hide();
    $("#sec_mantenimiento_razon_social_modal_guardar_titulo").text("Nueva Empresa");
    $("#sec_mantenimiento_razon_social_modal_nuevo").modal("show");
    })

function sec_mantenimiento_razon_social_limpiar_input()
{
	$('#form_modal_sec_mantenimiento_razon_social_param_id').val(0);
    $('#form_modal_sec_mantenimiento_razon_social_param_nombre_empresa').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_codigo_empresa').val("");
	$("#form_modal_sec_mantenimiento_razon_social_param_canal").val(0).trigger("change.select2");
	$("#form_modal_sec_mantenimiento_razon_social_param_red").val(0).trigger("change.select2");
	$('#form_modal_sec_mantenimiento_razon_social_param_ruc').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_codigo_sap').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_descripcion').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_contabilidad').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_contabilidad_descripcion').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_con_igv').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_con_igv_descripcion').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_sin_igv').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_sin_igv_descripcion').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_cancelacion_caja_chica').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_cancelacion_caja_chica_descripcion').val("");
	$("#form_modal_sec_mantenimiento_razon_social_param_habilitado_servicios_publicos").val(0).trigger("change.select2");
	$("#form_modal_sec_mantenimiento_razon_social_param_habilitado_prestamo_boveda").val(0).trigger("change.select2");
	$("#form_modal_sec_mantenimiento_razon_social_param_habilitado_recargas_kasnet").val(0).trigger("change.select2");
	$("#form_modal_sec_mantenimiento_razon_social_param_estado_tesoreria").val(0).trigger("change.select2");
    $("#form_modal_sec_mantenimiento_razon_social_param_estado_vale").val(0).trigger("change.select2");
    $('#form_modal_sec_mantenimiento_razon_social_param_fecha_create').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_fecha_update').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_usuario_create').val("");
    $('#form_modal_sec_mantenimiento_razon_social_param_usuario_update').val("");
    }

$("#sec_mantenimiento_razon_social_modal_nuevo .btn_guardar").off("click").on("click",function(){

	
    var nombre = $('#form_modal_sec_mantenimiento_razon_social_param_nombre_empresa').val();

    if(nombre.length == 0)
    {
        alertify.error('Ingrese el nombre de la empresa',5);
        $("#form_modal_sec_mantenimiento_razon_social_param_nombre_empresa").focus();
           return false;
    }

    sec_mantenimiento_razon_social_guardar();     

    })

function sec_mantenimiento_razon_social_ver(param_id) {
    sec_mantenimiento_razon_social_obtener(param_id);
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = true;
    });
}
function sec_mantenimiento_razon_social_obtener(param_id) {
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
            accion:'mantenimiento_razon_social_obtener'
        }

    $.ajax({
            url:  "/sys/get_mantenimiento_razon_social.php",
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
                $("#accordionRazónSocialCamposAuditoria").show();
                $('#form_modal_sec_mantenimiento_razon_social_param_id').val(param_id);
                $('#form_modal_sec_mantenimiento_razon_social_param_nombre_empresa').val(respuesta.result.nombre);
                $('#form_modal_sec_mantenimiento_razon_social_param_subdiario').val(respuesta.result.subdiario);
                sec_mantenimiento_razon_social_obtener_subdiario_descripcion();
                $('#form_modal_sec_mantenimiento_razon_social_param_codigo_empresa').val(respuesta.result.codigo_empresa);
                $('#form_modal_sec_mantenimiento_razon_social_param_estado_vale').val(respuesta.result.estado_vale).trigger("change.select2");
                $('#form_modal_sec_mantenimiento_razon_social_param_ruc').val(respuesta.result.ruc);
                $("#form_modal_sec_mantenimiento_razon_social_param_canal").val(respuesta.result.canal_id).trigger("change.select2");
                $('#form_modal_sec_mantenimiento_razon_social_param_red').val(respuesta.result.red_id).trigger("change.select2");
                $('#form_modal_sec_mantenimiento_razon_social_param_estado_tesoreria').val(respuesta.result.estado_tesoreria).trigger("change.select2");
                $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_contabilidad').val(respuesta.result.subdiario_contabilidad);
                sec_mantenimiento_razon_social_obtener_subdiario_contabilidad_descripcion();
                $('#form_modal_sec_mantenimiento_razon_social_param_codigo_sap').val(respuesta.result.codigo_sap);
                $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_con_igv').val(respuesta.result.subdiario_compra_con_igv);
                sec_mantenimiento_razon_social_obtener_subdiario_compra_con_igv_descripcion();
                $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_sin_igv').val(respuesta.result.subdiario_compra_sin_igv);
                sec_mantenimiento_razon_social_obtener_subdiario_compra_sin_igv_descripcion();
                $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_cancelacion_caja_chica').val(respuesta.result.subdiario_cancelacion_caja_chica);
                sec_mantenimiento_razon_social_obtener_subdiario_cancelacion_caja_chica_descripcion();
                $('#form_modal_sec_mantenimiento_razon_social_param_habilitado_servicios_publicos').val(respuesta.result.permiso_servicios_publicos).trigger("change.select2");
                $('#form_modal_sec_mantenimiento_razon_social_param_habilitado_prestamo_boveda').val(respuesta.result.habilitado_prestamo_boveda).trigger("change.select2");
                $('#form_modal_sec_mantenimiento_razon_social_param_habilitado_recargas_kasnet').val(respuesta.result.habilitado_recargas_kasnet).trigger("change.select2");
                
                if(respuesta.result.created_at==""){
                    document.getElementById('campoFechaCreacion').style.display = 'none';

                }else{
                    document.getElementById('campoFechaCreacion').style.display = 'block';
                    $('#form_modal_sec_mantenimiento_razon_social_param_fecha_create').val(respuesta.result.created_at);
                    $('#form_modal_sec_mantenimiento_razon_social_param_usuario_create').val(respuesta.result.usuario_create);
                }
                if(respuesta.result.updated_at==""){
                    document.getElementById('campoFechaActualiacion').style.display = 'none';

                }else{
                    document.getElementById('campoFechaActualiacion').style.display = 'block';
                    $('#form_modal_sec_mantenimiento_razon_social_param_fecha_update').val(respuesta.result.updated_at);
                    $('#form_modal_sec_mantenimiento_razon_social_param_usuario_update').val(respuesta.result.usuario_update);    
                }
                
                if(respuesta.result.created_at=="" && respuesta.result.updated_at==""){
                    $("#accordionRazónSocialCamposAuditoria").hide();
                }

                $("#sec_mantenimiento_razon_social_modal_guardar_titulo").text("Editar Empresa");
            	$("#sec_mantenimiento_razon_social_modal_nuevo").modal("show");
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

function sec_mantenimiento_razon_social_guardar(){
        var id = $('#form_modal_sec_mantenimiento_razon_social_param_id').val();
        var nombre = $('#form_modal_sec_mantenimiento_razon_social_param_nombre_empresa').val();
        var codigo_empresa = $('#form_modal_sec_mantenimiento_razon_social_param_codigo_empresa').val();
        var canal = $('#form_modal_sec_mantenimiento_razon_social_param_canal').val();
        var red = $('#form_modal_sec_mantenimiento_razon_social_param_red').val();
        var ruc = $('#form_modal_sec_mantenimiento_razon_social_param_ruc').val();
        var codigo_sap = $('#form_modal_sec_mantenimiento_razon_social_param_codigo_sap').val();
        var subdiario = $('#form_modal_sec_mantenimiento_razon_social_param_subdiario').val();
        var subdiario_contabilidad = $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_contabilidad').val();
        var subdiario_compra_con_igv = $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_con_igv').val();
        var subdiario_compra_sin_igv = $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_sin_igv').val();
        var subdiario_cancelacion_caja_chica = $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_cancelacion_caja_chica').val();
        var habilitado_servicios_publicos = $('#form_modal_sec_mantenimiento_razon_social_param_habilitado_servicios_publicos').val();
        var habilitado_prestamo_boveda = $('#form_modal_sec_mantenimiento_razon_social_param_habilitado_prestamo_boveda').val();
        var habilitado_recargas_kasnet = $('#form_modal_sec_mantenimiento_razon_social_param_habilitado_recargas_kasnet').val();
        var estado_tesoreria = $('#form_modal_sec_mantenimiento_razon_social_param_estado_tesoreria').val();
        var estado_vale = $('#form_modal_sec_mantenimiento_razon_social_param_estado_vale').val();

        if(id == 0)
        {
            // CREAR
            aviso = "¿Está seguro de registrar la nueva empresa?";
            titulo = "Registrar";
        }
        else
        {
            // EDITAR
            aviso = "¿Está seguro de editar la empresa?";
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
                        "accion" : "mantenimiento_razon_social_guardar",
                        "id" : id,
                        "nombre" : nombre,
                        "codigo_empresa" : codigo_empresa,
                        "canal" : canal,
                        "red" : red,
                        "ruc" : ruc,
                        "codigo_sap" : codigo_sap,
                        "subdiario" : subdiario,
                        "subdiario_contabilidad" : subdiario_contabilidad,
                        "subdiario_compra_con_igv" : subdiario_compra_con_igv,
                        "subdiario_compra_sin_igv" : subdiario_compra_sin_igv,
                        "subdiario_cancelacion_caja_chica" : subdiario_cancelacion_caja_chica,
                        "habilitado_servicios_publicos" : habilitado_servicios_publicos,
                        "habilitado_prestamo_boveda" : habilitado_prestamo_boveda,
                        "habilitado_recargas_kasnet" : habilitado_recargas_kasnet,
                        "estado_tesoreria" : estado_tesoreria,
                        "estado_vale" : estado_vale,
                    }
            
                auditoria_send({ "respuesta": "mantenimiento_razon_social_guardar", "data": data });
                $.ajax({
                    url: "sys/set_mantenimiento_razon_social.php",
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
                                title: "Error al guardar la empresa.",
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
                                text: "La empresa se guardó correctamente.",
                                html:true,
                                type: "success",
                                closeOnConfirm: false,
                                showCancelButton: false
                                });
                                    $('#Frm_RegistroRazonSocial')[0].reset();
                                    $("#form_modal_sec_mantenimiento_razon_social_param_id").val(0);
                                    $("#sec_mantenimiento_razon_social_modal_nuevo").modal("hide");
                                    sec_mantenimiento_razon_social_listar();
                                }      
                            },
                            error: function() {}
                        });
                    }else{
                        //alertify.error('No se guardó el monto',5);
                        return false;
                    }
                });
    }

function sec_mantenimiento_razon_social_eliminar(id_razon_social){
    swal({
            title: '¿Está seguro de eliminar la empresa?',
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: "SI",
            cancelButtonText: "NO",
            closeOnConfirm: true,
            closeOnCancel: true,
        
        },function (isConfirm) {
            if (isConfirm) {
                let data = {
                    id_razon_social : id_razon_social,
                    accion:'mantenimiento_razon_social_eliminar'
                    }
        
                $.ajax({
                    url: "sys/set_mantenimiento_razon_social.php",
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
                            "proceso": "mantenimiento_razon_social_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "La empresa se eliminó correctamente",
                                html: true,
                                type: "success",
                                timer: 3000,
                                closeOnConfirm: false,
                                showCancelButton: false
                            });
                            setTimeout(function() {
                                sec_mantenimiento_razon_social_listar();
                                return false;
                            }, 3000);
                        } else {
                            swal({
                                title: "Error al eliminar la empresa",
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

//////////   FUNCIONES PARA CANALES Y REDES

function sec_mantenimiento_razon_social_listar_canales(){
    let select = $("[name='form_modal_sec_mantenimiento_razon_social_param_canal']");
    let valorSeleccionado = $("#form_modal_sec_mantenimiento_razon_social_param_canal").val();
    
    $.ajax({
        url: "/sys/get_mantenimiento_razon_social.php",
        type: "POST",
        data: {
            accion: "mantenimientorazon_social_canal_listar"
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
            console.error("Error al obtener la lista de canales.");
            }
        });
    }

function sec_mantenimiento_razon_social_listar_redes(){
        let select = $("[name='form_modal_sec_mantenimiento_razon_social_param_red']");
        let valorSeleccionado = $("#form_modal_sec_mantenimiento_razon_social_param_red").val();
        
        $.ajax({
            url: "/sys/get_mantenimiento_razon_social.php",
            type: "POST",
            data: {
                accion: "mantenimientorazon_social_red_listar"
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

//////////   FUNCIONES PARA SUBDIARIOS


function sec_mantenimiento_razon_social_obtener_subdiario_descripcion()
{
	var subdiario = $('#form_modal_sec_mantenimiento_razon_social_param_subdiario').val();
    var primerosDosDigitosSubdiario = subdiario.toString().substring(0, 2);
    console.log(primerosDosDigitosSubdiario);
	var data = {
		'accion': 'mantenimiento_razon_social_obtener_subdiario_descripcion',
		'subdiario': primerosDosDigitosSubdiario
	};

	$.ajax({
			url: "sys/get_mantenimiento_razon_social.php",
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
                    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_descripcion').val(respuesta.descripcion);
                }

		        if (parseInt(respuesta.http_code) == 200) {
                    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_descripcion').val(respuesta.descripcion);
                }   		
			},
			error: function(){
				alert('failure');
			  }
	    });
    }

function sec_mantenimiento_razon_social_obtener_subdiario_contabilidad_descripcion()
{
	var subdiario = $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_contabilidad').val();
    var primerosDosDigitosSubdiario = subdiario.toString().substring(0, 2);
    console.log(primerosDosDigitosSubdiario);
	var data = {
		'accion': 'mantenimiento_razon_social_obtener_subdiario_descripcion',
		'subdiario': primerosDosDigitosSubdiario
	};

	$.ajax({
			url: "sys/get_mantenimiento_razon_social.php",
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
                    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_contabilidad_descripcion').val(respuesta.descripcion);
                }

		        if (parseInt(respuesta.http_code) == 200) {
                    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_contabilidad_descripcion').val(respuesta.descripcion);
                }   		
			},
			error: function(){
				alert('failure');
			  }
	    });
    }

function sec_mantenimiento_razon_social_obtener_subdiario_compra_con_igv_descripcion()
{
	var subdiario = $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_con_igv').val();
    var primerosDosDigitosSubdiario = subdiario.toString().substring(0, 2);
    console.log(primerosDosDigitosSubdiario);
	var data = {
		'accion': 'mantenimiento_razon_social_obtener_subdiario_descripcion',
		'subdiario': primerosDosDigitosSubdiario
	};

	$.ajax({
			url: "sys/get_mantenimiento_razon_social.php",
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
                    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_con_igv_descripcion').val(respuesta.descripcion);
                }

		        if (parseInt(respuesta.http_code) == 200) {
                    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_con_igv_descripcion').val(respuesta.descripcion);
                }   		
			},
			error: function(){
				alert('failure');
			  }
	    });
    }

function sec_mantenimiento_razon_social_obtener_subdiario_compra_sin_igv_descripcion()
{
	var subdiario = $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_sin_igv').val();
    var primerosDosDigitosSubdiario = subdiario.toString().substring(0, 2);
    console.log(primerosDosDigitosSubdiario);
	var data = {
		'accion': 'mantenimiento_razon_social_obtener_subdiario_descripcion',
		'subdiario': primerosDosDigitosSubdiario
	};

	$.ajax({
			url: "sys/get_mantenimiento_razon_social.php",
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
                    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_sin_igv_descripcion').val(respuesta.descripcion);
                }

		        if (parseInt(respuesta.http_code) == 200) {
                    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_sin_igv_descripcion').val(respuesta.descripcion);
                }   		
			},
			error: function(){
				alert('failure');
			  }
	    });
    }

function sec_mantenimiento_razon_social_obtener_subdiario_cancelacion_caja_chica_descripcion()
{
	var subdiario = $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_cancelacion_caja_chica').val();
    var primerosDosDigitosSubdiario = subdiario.toString().substring(0, 2);
    console.log(primerosDosDigitosSubdiario);
	var data = {
		'accion': 'mantenimiento_razon_social_obtener_subdiario_descripcion',
		'subdiario': primerosDosDigitosSubdiario
	};

	$.ajax({
			url: "sys/get_mantenimiento_razon_social.php",
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
                    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_cancelacion_caja_chica_descripcion').val(respuesta.descripcion);
                }

		        if (parseInt(respuesta.http_code) == 200) {
                    $('#form_modal_sec_mantenimiento_razon_social_param_subdiario_cancelacion_caja_chica_descripcion').val(respuesta.descripcion);
                }   		
			},
			error: function(){
				alert('failure');
			  }
	    });
    }