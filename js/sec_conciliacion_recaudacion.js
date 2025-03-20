// INICIO: FUNCIONES INICIALIZADOS
function sec_conciliacion_recaudacion()
{

    //  Filtros de Busqueda

    conci_recaudacion_search_proveedor_listar();
    conci_recaudacion_listar_datatable();
    //conci_recaudacion_tipo_editar_listar();

	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".conci_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

    $('#btn_conci_recaudacion_limpiar_filtro').click(function() {
		$('#search_conci_recaudacion_proveedor_id').select2().val('').trigger("change");
		$('#search_conci_recaudacion_fecha_inicio').val('');
		$('#search_conci_recaudacion_fecha_fin').val('');
        $('#search_conci_recaudacion_fecha_periodo').val('');
        conci_recaudacion_listar_datatable();

	});


}

/// FILTROS DE BUSQUEDA

function conci_recaudacion_search_proveedor_listar() {
    let select = $("[name='search_conci_recaudacion_proveedor_id']");

    $.ajax({
        url: "/sys/get_conciliacion_venta.php",
        type: "POST",
        data: {
            accion: "conci_venta_proveedor_listar"
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);

            $(select).empty();

                let opcionDefault = $("<option value='' selected>Seleccionar</option>");
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

function conci_recaudacion_listar_datatable() {
    if (sec_id == "conciliacion" && sub_sec_id == "recaudacion") {
        var proveedor_id = $("#search_conci_recaudacion_proveedor_id").val();
        var periodo = $("#search_conci_recaudacion_periodo").val();
        var fecha_inicio = $("#search_conci_recaudacion_fecha_inicio").val();
        var fecha_fin = $("#search_conci_recaudacion_fecha_fin").val();

        var data = {
            accion: "conci_recaudacion_historial_periodo",
            proveedor_id: proveedor_id,
            periodo: periodo,
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin

            };
        
        tabla = $("#conci_venta_div_listar_datatable").dataTable(
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
                    url : "/sys/get_conciliacion_recaudacion.php",
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
                aLengthMenu:[10, 20, 30, 40, 50, 100]
            }).DataTable();
        $("#conci_recaudacion_div_tabla").show();

    }
    }

function conci_recaudacion_btn_exportar(formato){

	var proveedor_id = $("#search_conci_recaudacion_proveedor_id").val();
	var fecha_inicio = $("#search_conci_recaudacion_fecha_inicio").val();
	var fecha_fin = $("#search_conci_recaudacion_fecha_fin").val();
	var periodo = $("#search_conci_recaudacion_periodo").val();

	
	if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio);
		var fecha_fin_date = new Date(fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}

    var data = {
        "accion": "conci_recaudacion_exportar",
        formato: formato,
        periodo: periodo,
        proveedor_id: proveedor_id,
		fecha_inicio: fecha_inicio,
		fecha_fin: fecha_fin
    }

    $.ajax({
        url: "/sys/get_conciliacion_recaudacion.php",
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

function conci_recaudacion_periodo_historial_btn_editar(periodo_id, proveedor_id){
    conci_recaudacion_formPeriodo_cuenta_bancaria_listar(proveedor_id);
    $('#form_modal_sec_conci_recaudacion_periodo_editar_param_id').val(periodo_id);
    $('#form_modal_sec_conci_recaudacion_periodo_editar_param_proveedor_id').val(proveedor_id);

    let data = {
        id : periodo_id,
        accion:'conci_venta_periodo_obtener'
    }

    $.ajax({
        url:  "/sys/get_conciliacion_venta.php",
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
            $('#form_modal_sec_conci_recaudacion_periodo_editar_param_id').val(respuesta.result.id);
            $('#form_modal_sec_conci_recaudacion_periodo_editar_param_proveedor').val(respuesta.result.proveedor);

            var periodo = respuesta.result.periodo;

            var year = periodo.substring(0, 4);
            var month = periodo.substring(5, 7);

            var periodoFormatted = year + '-' + month;

            $('#form_modal_sec_conci_recaudacion_periodo_editar_param_periodo').val(periodoFormatted);

            document.getElementById('conciRecaudacionPeriodoActualizacion').style.display = 'none';

            /*
            if(respuesta.result.updated_at==""){
                document.getElementById('conciRecaudacionPeriodoActualizacion').style.display = 'none';

            }else{
                document.getElementById('conciRecaudacionPeriodoActualizacion').style.display = 'block';
                $('#form_modal_sec_conci_recaudacion_periodo_editar_param_fecha_update').val(respuesta.result.updated_at);
                $('#form_modal_sec_conci_recaudacion_periodo_editar_param_usuario_update').val(respuesta.result.usuario_update);    
            }

            */
            var periodo_formato = respuesta.result.periodo_formato;
            var proveedor = respuesta.result.proveedor;

            $('#modal_conci_recaudacion_detalle_periodo').modal('show');
            $('#modal_title_conci_recaudacion_detalle_periodo').html((proveedor + ' - ' + periodo_formato).toUpperCase());
            sec_conci_recaudacion_periodo_historial_Datatable();
            //sec_conci_recaudacion_periodo_historial_importacion_calimaco_Datatable();
            $("#form_modal_sec_conci_periodo_recaudacion_param_cuenta_id").val(0).trigger("change.select2");

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


$("#sec_conci_recaudacion_periodo_btn_registrar").off("click").on("click", function(event) {
    event.preventDefault();

    var id = $("#form_modal_sec_conci_recaudacion_periodo_editar_param_id").val();
    var proveedor_id = $("#form_modal_sec_conci_recaudacion_periodo_editar_param_proveedor_id").val();
    var fecha = $("#form_modal_sec_conci_periodo_recaudacion_param_fecha").val();
    var monto = $("#form_modal_sec_conci_periodo_recaudacion_param_monto").val();
    var cuenta_id = $("#form_modal_sec_conci_periodo_recaudacion_param_cuenta_id").val();


    if (fecha.length == 0){
        alertify.error('Ingrese la fecha',5);
        $("#form_modal_sec_conci_venta_periodo_editar_param_periodo").focus();
        return false;
    }

    swal({
        title: "Editar",
        text: "Estado seguro de registrar la recaudación",
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
            "accion" : "conci_recaudacion_registrar",
            "id" : id,
            "fecha": fecha,
            "monto": monto,
            "proveedor_id": proveedor_id ,      
            "cuenta_id": cuenta_id,

   
        }

        auditoria_send({ "respuesta": "conci_recaudacion_registrar", "data": data });
        $.ajax({
            url: "sys/set_conciliacion_recaudacion.php",
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
                        title: "Error al guardar la recaudación.",
                        text: respuesta.error,
                        html:true,
                        type: "warning",
                        closeOnConfirm: false,
                        showCancelButton: false
                        });
                    return false;
                }

                if (parseInt(respuesta.http_code) == 200) {
                    sec_conci_recaudacion_periodo_historial_Datatable();
                    conci_recaudacion_listar_datatable();
                    swal({
                        title: "Guardar",
                        text: "La recaudación se guardó correctamente.",
                        html:true,
                        type: "success",
                        closeOnConfirm: false,
                        showCancelButton: false
                        });
                        }      
                    },
                    error: function() {}
                });
            }else{
                return false;
            }
    });
})


function conci_recaudacion_formPeriodo_cuenta_bancaria_listar(proveedor_id) {
    let select = $("[name='form_modal_sec_conci_periodo_recaudacion_param_cuenta_id']");

    $.ajax({
        url: "/sys/get_conciliacion_recaudacion.php",
        type: "POST",
        data: {
            accion: "conci_recaudacion_cuenta_bancaria_listar",
            proveedor_id: proveedor_id
        },
        success: function(datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();

           let opcionDefault = $("<option value='0' selected>Seleccionar</option>");
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

function sec_conci_recaudacion_periodo_historial_Datatable() {
    if (sec_id == "conciliacion" && sub_sec_id == "recaudacion") {
        var proveedor_id = $("#form_modal_sec_conci_recaudacion_periodo_editar_param_proveedor_id").val();
        var periodo_id = $("#form_modal_sec_conci_recaudacion_periodo_editar_param_id").val();

        var data = {
            accion: "conci_recaudacion_periodo_historial_recaudacion",
            proveedor_id: proveedor_id,
            periodo_id: periodo_id
            };
        $("#conci_recaudacion_periodo_historial_importacion_proveedor_div_tabla").show();
        
        tabla = $("#conci_recaudacion_periodo_historial_importacion_proveedor_datatable").dataTable({
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
                        url: "/sys/get_conciliacion_recaudacion.php",
                        data: data,
                        type: "POST",
                        dataType: "json",
                        error: function(e) {
                        },
                    },
                    createdRow: function(row, data, dataIndex) {
                        if (data[0] === 'error') {
                            $('td:eq(0)', row).attr('colspan', 9);
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

function conci_recaudacion_btn_eliminar(recaudacion_id, periodo_id){

    swal({
            title: '¿Está seguro de eliminar el registro de recaudación?',
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
                    recaudacion_id: recaudacion_id,
                    periodo_id:periodo_id,
                    accion: 'conci_recaudacion_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_conciliacion_recaudacion.php",
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
                            "proceso": "conci_recaudacion_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "El registro de recaudación se eliminó correctamente",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                sec_conci_recaudacion_periodo_historial_Datatable();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar el registro de recaudación",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se guardaron los cambios',5);
                sec_conci_recaudacion_periodo_historial_Datatable();
                return false;
            }
        });
    }