// INICIO: FUNCIONES INICIALIZADOS
function sec_conciliacion_devolucion()
{

    //  Filtros de Busqueda

    conci_devolucion_search_proveedor_listar();
    conci_devolucion_listar_datatable();

	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".conci_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

    $('#btn_conci_devolucion_limpiar_filtro').click(function() {
		$('#search_conci_devolucion_proveedor_id').select2().val(0).trigger("change");
		$('#search_conci_devolucion_etapa_id').select2().val(0).trigger("change");
        $('#search_conci_devolucion_tipo_id').select2().val(0).trigger("change");
		$('#search_conci_devolucion_usuario_autorizador').select2().val(0).trigger("change");
		$('#search_conci_devolucion_fecha_inicio').val('');
		$('#search_conci_devolucion_fecha_fin').val('');
        $('#search_conci_devolucion_fecha_inicio_autorizacion').val('');
		$('#search_conci_devolucion_fecha_fin_autorizacion').val('');
        conci_devolucion_listar_datatable();

	});


}

/// FILTROS DE BUSQUEDA

function conci_devolucion_search_proveedor_listar() {
    let select = $("[name='search_conci_devolucion_proveedor_id']");

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

function conci_devolucion_listar_datatable() {
	$("#conci_devolucion_div_listar").hide();

    if(sec_id == "conciliacion" && sub_sec_id == "devolucion"){

	$("#conci_devolucion_div_listar").show();
	var proveedor_id = $("#search_conci_devolucion_proveedor_id").val();
	var fecha_inicio = $("#search_conci_devolucion_fecha_inicio").val();
	var fecha_fin = $("#search_conci_devolucion_fecha_fin").val();

	if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio);
		var fecha_fin_date = new Date(fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}

    var data = {
        "accion": "conci_devolucion_listar",
        proveedor_id: proveedor_id,
		fecha_inicio: fecha_inicio,
		fecha_fin: fecha_fin
    }

    tabla = $("#conci_devolucion_div_listar_datatable").dataTable(
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
                url : "/sys/get_conciliacion_devolucion.php",
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
                    targets: [0, 1, 2, 3, 4, 5, 6]
                },
                {
                    render: $.fn.dataTable.render.number(',', '.', 2),
                    targets: 5
                }
            ],
            "bDestroy" : true,
            //"order": [[0, 'desc']],
            aLengthMenu:[10, 20, 30, 40, 50, 100]
        }).DataTable();
        
    }
    }

function conci_devolucion_btn_exportar(formato){

	var proveedor_id = $("#search_conci_devolucion_proveedor_id").val();
	var fecha_inicio = $("#search_conci_devolucion_fecha_inicio").val();
	var fecha_fin = $("#search_conci_devolucion_fecha_fin").val();

	if (fecha_inicio.length > 0 && fecha_fin.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio);
		var fecha_fin_date = new Date(fecha_fin);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}

    var data = {
        "accion": "conci_devolucion_exportar",
        formato: formato,
        proveedor_id: proveedor_id,
		fecha_inicio: fecha_inicio,
		fecha_fin: fecha_fin,  
    }

    $.ajax({
        url: "/sys/get_conciliacion_devolucion.php",
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

function conci_devolucion_btn_eliminar(devolucion_id, calimaco_id){

    swal({
            title: '¿Está seguro de eliminar la solicitud de devolución?',
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
                    devolucion_id: devolucion_id,
                    calimaco_id:calimaco_id,
                    accion: 'conci_devolucion_eliminar'
                };
    
                $.ajax({
                    url: "sys/set_conciliacion_devolucion.php",
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
                            "proceso": "conci_devolucion_eliminar",
                            "data": respuesta
                        });
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: "Eliminación exitosa",
                                text: "La devolución se eliminó correctamente",
                                type: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                conci_devolucion_listar_datatable();
                            }, 2000);
                        } else {
                            swal({
                                title: "Error al eliminar la devolución",
                                text: respuesta.error,
                                type: "warning"
                            });
                        }
                    }
                });
            }else{
                alertify.error('No se guardaron los cambios',5);
                conci_devolucion_listar_datatable();
                return false;
            }
        });
    }

