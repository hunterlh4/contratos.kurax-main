// INICIO: FUNCIONES INICIALIZADOS
function sec_conciliacion_anulacion()
{

    //  Filtros de Busqueda

    conci_anulacion_search_proveedor_listar();
    conci_anulacion_search_etapa_listar();
    conci_anulacion_search_tipo_listar();
    conci_anulacion_search_usuario_autorizador_listar();
    conci_anulacion_tipo_listar();
    conci_anulacion_listar_datatable();
    conci_anulacion_tipo_editar_listar();

	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".conci_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

    $('#btn_conci_anulacion_limpiar_filtro').click(function() {
		$('#search_conci_anulacion_proveedor_id').select2().val(0).trigger("change");
		$('#search_conci_anulacion_etapa_id').select2().val(0).trigger("change");
        $('#search_conci_anulacion_tipo_id').select2().val(0).trigger("change");
		$('#search_conci_anulacion_usuario_autorizador').select2().val(0).trigger("change");
		$('#search_conci_anulacion_fecha_inicio').val('');
		$('#search_conci_anulacion_fecha_fin').val('');
        $('#search_conci_anulacion_fecha_inicio_autorizacion').val('');
		$('#search_conci_anulacion_fecha_fin_autorizacion').val('');
        conci_anulacion_listar_datatable();

	});


}

/// FILTROS DE BUSQUEDA

function conci_anulacion_search_proveedor_listar() {
    let select = $("[name='search_conci_anulacion_proveedor_id']");

    $.ajax({
        url: "/sys/get_conciliacion_venta.php",
        type: "POST",
        data: {
            accion: "conci_venta_proveedor_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

                let opcionDefault = $("<option value=0 selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
            
        },
        error: function() {
            console.error("Error al obtener la lista de tipos de proveedores.");
        }
    });
    }
function conci_anulacion_search_etapa_listar() {
    let select = $("[name='search_conci_anulacion_etapa_id']");

    $.ajax({
        url: "/sys/get_conciliacion_anulacion.php",
        type: "POST",
        data: {
            accion: "conci_anulacion_etapa_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

                let opcionDefault = $("<option value=0 selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
            
        },
        error: function() {
            console.error("Error al obtener la lista de tipos de proveedores.");
        }
    });
    }

function conci_anulacion_search_tipo_listar() {
    let select = $("[name='search_conci_anulacion_tipo_id']");

    $.ajax({
        url: "/sys/get_conciliacion_anulacion.php",
        type: "POST",
        data: {
            accion: "conci_anulacion_tipo_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

                let opcionDefault = $("<option value=0 selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
            
        },
        error: function() {
            console.error("Error al obtener la lista de tipos de proveedores.");
        }
    });
    }

function conci_anulacion_search_usuario_autorizador_listar() {
    let select = $("[name='search_conci_anulacion_usuario_autorizador']");

    $.ajax({
        url: "/sys/get_conciliacion_anulacion.php",
        type: "POST",
        data: {
            accion: "conci_anulacion_autorizador_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

                let opcionDefault = $("<option value=0 selected>Seleccionar</option>");
                    $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
            
        },
        error: function() {
            console.error("Error al obtener la lista de tipos de proveedores.");
        }
    });
    }

function conci_anulacion_listar_datatable() {
	$("#conci_anulacion_div_listar").hide();

    if(sec_id == "conciliacion" && sub_sec_id == "anulacion"){

	$("#conci_anulacion_div_listar").show();
	var proveedor_id = $("#search_conci_anulacion_proveedor_id").val();
	var etapa_id = $("#search_conci_anulacion_etapa_id").val();
	var tipo_id = $("#search_conci_anulacion_tipo_id").val();
	var fecha_inicio = $("#search_conci_anulacion_fecha_inicio").val();
	var fecha_fin = $("#search_conci_anulacion_fecha_fin").val();
    var fecha_inicio_autorizacion = $("#search_conci_anulacion_fecha_inicio_autorizacion").val();
    var fecha_fin_autorizacion = $("#search_conci_anulacion_fecha_fin_autorizacion").val();
	var usuario_autorizador = $("#search_conci_anulacion_usuario_autorizador").val();

	if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio);
		var fecha_fin_date = new Date(fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}

    if (fecha_inicio_autorizacion.length > 0 && fecha_fin_autorizacion.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio_autorizacion);
		var fecha_fin_date = new Date(fecha_fin_autorizacion);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}

    var data = {
        "accion": "conci_anulacion_listar",
		etapa_id: etapa_id,
        tipo_id: tipo_id,
		usuario_autorizador: usuario_autorizador,
        proveedor_id: proveedor_id,
		fecha_inicio: fecha_inicio,
		fecha_fin: fecha_fin,  
		fecha_inicio_autorizacion: fecha_inicio_autorizacion,
		fecha_fin_autorizacion: fecha_fin_autorizacion    
    }

    tabla = $("#conci_anulacion_div_listar_datatable").dataTable(
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
                },
                "buttons": {
                    pageLength: {
                            _: "Mostrar %d Resultados",
                            '-1': "Tout afficher"
                        }
                    }
            },
            "aProcessing" : true,
            "aServerSide" : true,
            /*
            buttons: [
                'pageLength',
            ],
            scrollY: true,
            scrollX: true,
            dom: 'Bfrtip',
            */
            "ajax" :
            {
                url : "/sys/get_conciliacion_anulacion.php",
                data : data,
                type : "POST",
                dataType : "json",
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                    loading();
                },
                error : function(e){
                    console.log(e.responseText);
                }
            },
            columnDefs: [
                {
                    className: 'text-center',
                    targets: [0, 1, 2, 3, 4, 5, 6, 7,8]
                },
                {
                    render: $.fn.dataTable.render.number(',', '.', 2),
                    targets: 5
                }
            ],
            "createdRow": function(row, data, dataIndex) {
                // Aplicar estilo basado en data[8]
                if (data[9] === 'red') {
                    $(row).css('background-color', '#FFCDD2');  // Color rojo claro
                } else if (data[9] === 'yellow') {
                    $(row).css('background-color', '#FFF9C4');  // Color amarillo claro
                }
            },
            "bDestroy" : true,
            //"order": [[0, 'desc']],
            aLengthMenu:[10, 20, 30, 40, 50, 100]
        }).DataTable();
        
    }
    }

function conci_anulacion_btn_exportar(formato){

	var proveedor_id = $("#search_conci_anulacion_proveedor_id").val();
	var etapa_id = $("#search_conci_anulacion_etapa_id").val();
	var tipo_id = $("#search_conci_anulacion_tipo_id").val();
	var fecha_inicio = $("#search_conci_anulacion_fecha_inicio").val();
	var fecha_fin = $("#search_conci_anulacion_fecha_fin").val();
    var fecha_inicio_autorizacion = $("#search_conci_anulacion_fecha_inicio_autorizacion").val();
    var fecha_fin_autorizacion = $("#search_conci_anulacion_fecha_fin_autorizacion").val();
	var usuario_autorizador = $("#search_conci_anulacion_usuario_autorizador").val();

	if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio);
		var fecha_fin_date = new Date(fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}

    if (fecha_inicio_autorizacion.length > 0 && fecha_fin_autorizacion.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio_autorizacion);
		var fecha_fin_date = new Date(fecha_fin_autorizacion);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}

    var data = {
        "accion": "conci_anulacion_exportar",
        formato: formato,
		etapa_id: etapa_id,
        tipo_id: tipo_id,
		usuario_autorizador: usuario_autorizador,
        proveedor_id: proveedor_id,
		fecha_inicio: fecha_inicio,
		fecha_fin: fecha_fin,  
		fecha_inicio_autorizacion: fecha_inicio_autorizacion,
		fecha_fin_autorizacion: fecha_fin_autorizacion    
    }

    $.ajax({
        url: "/sys/get_conciliacion_anulacion.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            
            let respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                    swal({
                        title: respuesta.titulo,
                        text: respuesta.error,
                        html:true,
                        type: "warning",
                        closeOnConfirm: false,
                        showCancelButton: false
                        });
                    return false;
                    }
        
            if (parseInt(respuesta.http_code) == 200) {
                window.open(respuesta.ruta_archivo);
                loading(false);
            }      
        },
        error: function(resp, status) {

        }
    });
    }


function conci_anulacion_btn_cambiar_etapa(etapa_id,solicitud_anulacion_id,  etapa_nombre){

    swal({
            title: '¿Está seguro cambiar de etapa a ' + etapa_nombre + '?',
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
                        solicitud_anulacion_id: solicitud_anulacion_id,
                        accion: 'conci_anulacion_cambiar_etapa',
                        etapa_id: etapa_id,
                        etapa_nombre: etapa_nombre
                        };
            
                    $.ajax({
                        url: "sys/set_conciliacion_anulacion.php",
                        type: 'POST',
                        data: data,
                        beforeSend: function () {
                            loading(true);
                        },
                        complete: function () {
                            loading(false);
                            },
                        success: function (resp) {
                            var titulo = etapa_nombre + " exitosamente";
                            var respuesta = JSON.parse(resp);
                            auditoria_send({
                                "proceso": "conci_anulacion_cambiar_etapa",
                                "data": respuesta
                                });
                            if (parseInt(respuesta.http_code) == 200) {
                                swal({
                                    title: titulo,
                                    text: "La solicitud se cambio de etapa correctamente",
                                    type: "success",
                                    timer: 2000,
                                    showConfirmButton: false
                                    });
                                setTimeout(function () {
                                    conci_anulacion_listar_datatable();
                                    }, 2000);
                            } else {
                                swal({
                                    title: "Error al cambiar de etapa",
                                    text: respuesta.error,
                                    type: "warning"
                                });
                            }
                            }
                        });
                }else{
                    alertify.error('No se realizaron los cambios',5);
                    conci_anulacion_listar_datatable();
                    return false;
                }
        });
    }

function conci_anulacion_btn_aprobar(solicitud_anulacion_id,cantidad_aprobaciones,first_user_authorized_id,calimaco_id){
    console.log(calimaco_id);
    swal({
            title: '¿Está seguro de aprobar la solicitud de anulación?',
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
                        anulacion_id: solicitud_anulacion_id,
                        cantidad_aprobaciones:cantidad_aprobaciones,
                        first_user_authorized_id:first_user_authorized_id,
                        calimaco_id: calimaco_id,
                        accion: 'conci_anulacion_aprobar'
                        };
            
                    $.ajax({
                        url: "sys/set_conciliacion_anulacion.php",
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
                                "proceso": "conci_anulacion_aprobar",
                                "data": respuesta
                                });
                            if (parseInt(respuesta.http_code) == 200) {
                                swal({
                                    title: "Aprobación exitosa",
                                    text: "La solicitud se aprobo correctamente",
                                    type: "success",
                                    timer: 2000,
                                    showConfirmButton: false
                                    });
                                setTimeout(function () {
                                    conci_anulacion_listar_datatable();
                                    }, 2000);
                            } else {
                                swal({
                                    title: respuesta.titulo,
                                    text: respuesta.error,
                                    type: "warning"
                                });
                            }
                            }
                        });
                }else{
                    alertify.error('No se realizaron los cambios',5);
                    conci_anulacion_listar_datatable();
                    return false;
                }
        });
    }


function conci_anulacion_btn_rechazar(solicitud_anulacion_id){

    swal({
            title: '¿Está seguro de rechazar la solicitud de anulación?',
            text: '<input type="text" id="txtMotivo" name="txtMotivo" class="form-control" placeholder="Ingresar motivo" style="display:block;">',
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
                    let motivo = $('#txtMotivo').val().trim();
    
                    if (motivo === "") {
                        alertify.error('Ingrese un motivo para continuar', 5);
                        $("#txtMotivo").focus();
                        return false;
                    }
    
            
                    let data = {
                        anulacion_id: solicitud_anulacion_id,
                        accion: 'conci_anulacion_rechazar',
                        motivo: motivo 
                        };
            
                    $.ajax({
                        url: "sys/set_conciliacion_anulacion.php",
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
                                "proceso": "conci_anulacion_rechazar",
                                "data": respuesta
                                });
                            if (parseInt(respuesta.http_code) == 200) {
                                swal({
                                    title: "Rechazo exitoso",
                                    text: "La solicitud se rechazo correctamente",
                                    type: "success",
                                    timer: 2000,
                                    showConfirmButton: false
                                    });
                                setTimeout(function () {
                                    conci_anulacion_listar_datatable();
                                    }, 2000);
                            } else {
                                swal({
                                    title: "Error al rechazar solicitud",
                                    text: respuesta.error,
                                    type: "warning"
                                });
                            }
                            }
                        });
                }else{
                    alertify.error('No se realizaron los cambios',5);
                    conci_anulacion_listar_datatable();
                    return false;
                }
        });
    }

function  conci_anulacion_btn_historial(solicitud_anulacion_id) {
	
    $('#modal_conci_anulacion_historial_etapas').modal('show');
    $('#modal_title_conci_anulacion_historico').html((solicitud_anulacion_id + ' - HISTORIAL DE CAMBIOS DE ETAPA').toUpperCase());

    if (sec_id == "conciliacion" && sub_sec_id == "anulacion") {

        var data = {
            accion: "conci_anulacion_historial_etapa",
            solicitud_anulacion_id:solicitud_anulacion_id
            };
        $("#conci_anulacion_historico_etapas_div_tabla").show();
        
        $('#conci_anulacion_historico_etapas_div_tabla tfoot th').each(function () {
            var title = $(this).text();
            $(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
            });
        
        tabla = $("#conci_anulacion_historico_etapas_datatable").dataTable({
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
                        url: "/sys/get_conciliacion_anulacion.php",
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

function conci_anulacion_btn_ver(anulacion_id) {
    var camposEditables = document.querySelectorAll('.campo-editable');
    camposEditables.forEach(function(campo) {
        campo.readOnly = true;
    });
    var camposSelect = document.querySelectorAll('.select-editable');
    camposSelect.forEach(function(select) {
        select.disabled = true;
    });

    $('#modal_conci_anulacion_solicitud').modal('show');
    $('#modal_title_conci_anulacion_solicitud').html(('DATOS DE ANULACIÓN').toUpperCase());

    
    $('#form_modal_conci_solicitud_anulacion_param_transaccion_calimaco_id').val(anulacion_id);

    let data = {
        id : anulacion_id,
        accion:'conci_anulacion_obtener'
    }

    $.ajax({
        url:  "/sys/get_conciliacion_anulacion.php",
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
                //$("#accordionConciProveedorCamposAuditoria").show();

                //  1. Datos de anulación

                    $('#form_modal_conci_solicitud_anulacion_param_transaccion_calimaco_id').val(anulacion_id);
                    $('#form_modal_conci_solicitud_anulacion_param_tipo').val(respuesta.anulacion.tipo).trigger("change.select2");
                    $('#form_modal_conci_solicitud_anulacion_param_motivo').val(respuesta.anulacion.motivo);
                    $('#form_modal_conci_solicitud_anulacion_param_fecha_creacion').val(respuesta.anulacion.created_at);
                    $('#form_modal_conci_solicitud_anulacion_param_usuario_creador').val(respuesta.anulacion.usuario_create);

                    if(respuesta.anulacion.updated_at==""){
                        document.getElementById('campoFechaActualiacionAnulación').style.display = 'none';
      
                    }else{
                        document.getElementById('campoFechaActualiacionAnulación').style.display = 'block';
                        $('#form_modal_conci_solicitud_anulacion_param_fecha_modificacion').val(respuesta.anulacion.first_authorized_at);
                        $('#form_modal_conci_solicitud_anulacion_param_usuario_modificador').val(respuesta.anulacion.first_user_authorized_id);   
                    }

                    if(respuesta.anulacion.first_authorized_at==""){
                        document.getElementById('accordionConciAnulacionAutorizacion').style.display = 'none';
      
                    }else{
                        document.getElementById('accordionConciAnulacionAutorizacion').style.display = 'block';
                        $('#form_modal_conci_solicitud_anulacion_autorizacion_param_fecha_primera_autorizacion').val(respuesta.anulacion.updated_at);
                        $('#form_modal_conci_solicitud_anulacion_autorizacion_param_usuario_primera_autorizacion').val(respuesta.anulacion.usuario_update);  

                        if(respuesta.anulacion.first_authorized_at==""){
                            document.getElementById('conciAnulacionSegundaAutorizacion').style.display = 'none';
          
                        }else{
                            document.getElementById('conciAnulacionSegundaAutorizacion').style.display = 'block';
                            $('#form_modal_conci_solicitud_anulacion_autorizacion_param_fecha_segunda_autorizacion').val(respuesta.anulacion.second_authorized_at);
                            $('#form_modal_conci_solicitud_anulacion_autorizacion_param_usuario_segunda_autorizacion').val(respuesta.anulacion.second_user_authorized_id);   
                        }
                    }

                //  6. Datos de transacción

                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_id').val(respuesta.transaccion.id);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_metodo').val(respuesta.transaccion.nombre_metodo);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_estado_id').val(respuesta.transaccion.nombre_estado);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_fecha').val(respuesta.transaccion.fecha);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_fecha_modificacion').val(respuesta.transaccion.fecha_modificacion);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_hora').val(respuesta.transaccion.hora);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_usuario').val(respuesta.transaccion.usuario);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_email').val(respuesta.transaccion.email);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_monto').val(respuesta.transaccion.cantidad);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_id_externo').val(respuesta.transaccion.id_externo);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_respuesta').val(respuesta.transaccion.respuesta);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_agente').val(respuesta.transaccion.agente);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_fecha_registro_jugador').val(respuesta.transaccion.fecha_registro_jugador);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_ref').val(respuesta.transaccion.ref);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_liquidacion').val(respuesta.transaccion.estado_liquidacion);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_conciliacion').val(respuesta.transaccion.estado_conciliacion);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_fecha_creacion').val(respuesta.transaccion.created_at);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_usuario_creador').val(respuesta.transaccion.usuario_create);

                    console.log(respuesta.transaccion.updated_at);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_fecha_actualizacion').val(respuesta.transaccion.updated_at);
                    $('#form_modal_conci_solicitud_anulacion_transaccion_param_usuario_modificador').val(respuesta.transaccion.usuario_update);

                $("#modal_conci_anulacion_solicitud").modal("show");
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

function conci_anulacion_btn_editar(anulacion_id) {

    $('#modal_conci_anulacion_solicitud_editar').modal('show');
    $('#modal_title_conci_anulacion_solicitud_editar').html(('EDITAR SOLICITUD DE ANULACIÓN').toUpperCase());

    
    $('#form_modal_conci_solicitud_anulacion_editar_param_transaccion_calimaco_id').val(anulacion_id);

    let data = {
        id : anulacion_id,
        accion:'conci_anulacion_obtener'
    }

    $.ajax({
        url:  "/sys/get_conciliacion_anulacion.php",
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
                //$("#accordionConciProveedorCamposAuditoria").show();

                //  1. Datos de anulación

                    $('#form_modal_conci_solicitud_anulacion_editar_param_transaccion_calimaco_id').val(anulacion_id);
                    $('#form_modal_conci_solicitud_anulacion_editar_param_tipo').val(respuesta.anulacion.tipo).trigger("change.select2");
                    $('#form_modal_conci_solicitud_anulacion_editar_param_motivo').val(respuesta.anulacion.motivo);
                    /*
                    $('#form_modal_conci_solicitud_anulacion_param_fecha_creacion').val(respuesta.anulacion.created_at);
                    $('#form_modal_conci_solicitud_anulacion_param_usuario_creador').val(respuesta.anulacion.usuario_create);
                    */

                $("#modal_conci_anulacion_solicitud_editar").modal("show");
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

function conci_anulacion_tipo_listar() {
    let select = $("[name='form_modal_conci_solicitud_anulacion_param_tipo']");

    $.ajax({
        url: "/sys/get_conciliacion_anulacion.php",
        type: "POST",
        data: {
            accion: "conci_anulacion_tipo_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

            let opcionDefault = $("<option value=0 selected>Seleccionar</option>");
                $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
        },
        error: function() {
            console.error("Error al obtener la lista de tipos de anulaciones.");
        }
    });
    }
function conci_anulacion_btn_eliminar(anulacion_id){

    swal({
            title: '¿Está seguro de eliminar la solicitud de anulación?',
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
                    anulacion_id: anulacion_id,
                    accion: 'conci_anulacion_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_conciliacion_anulacion.php",
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
                            "proceso": "conci_anulacion_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "La solicitud se eliminó correctamente",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                sec_comprobante_listar_datatable();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar la solicitud",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se guardaron los cambios',5);
                sec_comprobante_listar_datatable();
                return false;
            }
        });
    }

function conci_anulacion_tipo_editar_listar() {
    let select = $("[name='form_modal_conci_solicitud_anulacion_editar_param_tipo']");

    $.ajax({
        url: "/sys/get_conciliacion_anulacion.php",
        type: "POST",
        data: {
            accion: "conci_anulacion_tipo_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

            let opcionDefault = $("<option value=0 selected>Seleccionar</option>");
                $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
        },
        error: function() {
            console.error("Error al obtener la lista de tipos de anulaciones.");
        }
    });
    }

$("#modal_conci_anulacion_solicitud_editar .btn_guardar").off("click").on("click",function(){

	
    var motivo = $('#form_modal_conci_solicitud_anulacion_editar_param_motivo').val();
    var tipo = $("#form_modal_conci_solicitud_anulacion_editar_param_tipo").val();

    if(tipo == 0){
        alertify.error('Seleccione el tipo de anulación',5);
        $("#form_modal_conci_solicitud_anulacion_editar_param_tipo").focus();
        return false;
    }

    if(motivo.length == 0)
        {
            alertify.error('Ingrese la descripción del motivo de anulación',5);
            $("#form_modal_conci_solicitud_anulacion_editar_param_motivo").focus();
            return false;
        }

    conci_anulacion_solicitud_guardar();     
})

function conci_anulacion_solicitud_guardar(){
    var motivo = $('#form_modal_conci_solicitud_anulacion_editar_param_motivo').val();
    var tipo_id = $("#form_modal_conci_solicitud_anulacion_editar_param_tipo").val();
    var id = $("#form_modal_conci_solicitud_anulacion_editar_param_transaccion_calimaco_id").val();


    swal({
            title: "Registrar",
            text: "¿Está seguro de editar la solicitud de anulación?",
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
                    "accion" : "conci_anulacion_editar",
                    "motivo" : motivo,
                    "tipo_id": tipo_id ,
                    "id": id,     
                }
        
            auditoria_send({ "respuesta": "conci_anulacion_editar", "data": data });
            $.ajax({
                url: "sys/set_conciliacion_anulacion.php",
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
                            title: "Error al guardar la solicitud de anulación.",
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
                            text: "La solicitud se guardó correctamente.",
                            html:true,
                            type: "success",
                            closeOnConfirm: false,
                            showCancelButton: false
                            });
                                $('#Frm_EditarSolicitudAnulacion')[0].reset();
                                $("#modal_conci_anulacion_solicitud_editar").modal("hide");
                                conci_anulacion_listar_datatable();
                            }      
                        },
                        error: function() {}
                    });
                }else{
                    return false;
                }
            });
  }