// INICIO: FUNCIONES INICIALIZADOS

function sec_comprobante_reporte()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_comp_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA
    sec_comp_reporte_listar_datatable();

     // FILTRO DE BUSQUEDA

     sec_comprobante_reporte_filtro_proveedor_listar();
     sec_comprobante_reporte_filtro_razon_social_listar();
     sec_comprobante_reporte_filtro_etapa_listar();

    sec_comprobante_reporte_historico_listar_campo();

    $('.comp_limpiar_input').click(function() {
		$('#' + $(this).attr("limpiar")).val('');
	});

	$('.comp_limpiar_select2').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val(0).trigger("change");
	});

    $('.comp_limpiar_vacio_select2').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val("").trigger("change");
	});

    $('#btn_comprobante_reporte_historico_limpiar_filtros_de_busqueda').click(function() {
        $('#search_comp_reporte_campo_id').select2().val("").trigger("change");
    });

    $('#btn_comp_reporte_limpiar_filtros_de_busqueda').click(function() {
		$('#search_comp_reporte_proveedor_id').select2().val(0).trigger("change");
		$('#search_comp_reporte_razon_social_id').select2().val(0).trigger("change");
		$('#search_comp_reporte_etapa_id').select2().val(0).trigger("change");
		$('#search_comp_reporte_fecha_inicio_registro').val('');
		$('#search_comp_reporte_fecha_fin_registro').val('');
		$('#search_comp_reporte_fecha_inicio_emision').val('');
		$('#search_comp_reporte_fecha_fin_emision').val('');
        $('#search_comp_reporte_estado_id').select2().val('').trigger("change");
        sec_comp_reporte_listar_datatable();

	});

}

// FIN: FUNCIONES INICIALIZADOS


function sec_comprobante_reporte_limpiar_input()
{
    //  DATOS DEL COMPROBANTE DE PAGO

	$('#form_modal_sec_comp_param_id').val(0);
	$("#form_modal_sec_comp_param_tipo_comprobante_id").val(0).trigger("change.select2");
    $('#form_modal_sec_comp_param_num_documento_prefijo').val("");
    $('#form_modal_sec_comp_param_num_documento_sufijo').val("");
    $('#form_modal_sec_comp_param_fecha_emision').val("");
    $('#form_modal_sec_comp_param_fecha_vencimiento').val("");
	$("#form_modal_sec_comp_param_proveedor_id").val(0).trigger("change.select2");
    $('#form_modal_sec_comp_param_proveedor_nombre').val("");
    $("#form_modal_sec_comp_param_razon_social_id").val(0).trigger("change.select2");
    $('#form_modal_sec_comp_param_razon_social_nombre').val("");
    $("#form_modal_sec_comp_param_moneda_id").val(0).trigger("change.select2");
    $('#form_modal_sec_comp_param_monto').val("");
    $("#form_modal_sec_comp_param_area_id").val(0).trigger("change.select2");

    //  DATOS DE ORDEN DE COMPRA

	$('#form_modal_sec_comp_op_param_num_orden_pago').val("");
    $("#form_modal_sec_comp_op_param_ceco_id").val('').trigger("change.select2");
    $('#form_modal_sec_comp_op_param_ceco_descripcion').val("");

    //  DATOS DE ORDEN DE COMPRA

    $("#form_modal_sec_comp_fp_param_banco_id").val(0).trigger("change.select2");
    $("#form_modal_sec_comp_fp_param_moneda_id").val(0).trigger("change.select2");
    $('#form_modal_sec_comp_fp_param_num_cuenta_corriente').val("");
    $('#form_modal_sec_comp_fp_param_num_cuenta_interbancaria').val("");

    }

function  sec_comprobante_reporte_obtener_historico_cambios(comprobante_id) {
	
   // var num_documento = this.dataset.numDocumento;

    $('#modalComprobantesReporteHistoricoCambios').modal('show');
    $('#modal_title_comprobante_reporte_historico').html((comprobante_id + ' - HISTORIAL DE CAMBIOS').toUpperCase());
    //$('#modal_title_comprobante_historico').html((comprobante_id + ' - HISTORIAL DE CAMBIOS').toUpperCase());
    $('#search_comp_reporte_id').val(comprobante_id);
    $('#search_comp_id').val(comprobante_id);
    sec_comprobante_reporte_historico_listar_Datatable();
    sec_comprobante_reporte_historico_etapas_listar_Datatable(comprobante_id);

}

function sec_comprobante_reporte_historico_listar_Datatable() {
    if (sec_id == "comprobante" && sub_sec_id == "reporte") {
        var id_campo = $("#search_comp_reporte_campo_id").val();
        var comprobante_id = $("#search_comp_reporte_id").val();

        var data = {
            accion: "comp_obtener_historico",
            comprobante_id:comprobante_id,
            campo_id: id_campo
            };
        $("#comprobante_reporte_historico_cambios_div_tabla").show();
        
        $('#comprobante_reporte_historico_cambios_div_tabla tfoot th').each(function () {
            var title = $(this).text();
            $(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
            });
        
        tabla = $("#comprobante_historico_cambios_datatable").dataTable({
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
                        url: "/sys/get_comprobante_pago.php",
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

function sec_comprobante_reporte_historico_etapas_listar_Datatable(comprobante_id) {
    if (sec_id == "comprobante" && sub_sec_id == "reporte") {

        var data = {
            accion: "comp_obtener_historico_etapas",
            comprobante_id:comprobante_id
            };
        $("#comprobante_historico_etapas_div_tabla").show();
        
        $('#comprobante_historico_etapas_div_tabla tfoot th').each(function () {
            var title = $(this).text();
            $(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
            });
        
        tabla = $("#comprobante_historico_etapas_datatable").dataTable({
                    language: {
                    decimal: "",
                    emptyTable: "No existen registros",
                    info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                    infoEmpty: "Mostrando 0 a 0 de 0 entradas",
                    infoFiltered: "(filtrado de _MAX_ entradas)",
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
                        url: "/sys/get_comprobante_pago.php",
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

function sec_comprobante_reporte_historico_listar_campo() {
	let select = $("[name='search_comp_reporte_campo_id']");
	let valorSeleccionado = $("#search_comp_reporte_campo_id").val();
	
	$.ajax({
		url: "/sys/get_comprobante_pago.php",
		type: "POST",
		data: {
			accion: "comp_obtener_campos"
			},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			$(select).empty();
			if (!valorSeleccionado) {
				let opcionDefault = $('<option value=""> Todos</option>');
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

//////////   FUNCIONES DEL FILTROS DE BUSQUEDA

function sec_comprobante_reporte_filtro_proveedor_listar(){
    let select = $("[name='search_comp_reporte_proveedor_id']");
    
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_proveedor_listar_ruc_nombre"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            let opcionDefault = $("<option value=0 selected>Todos</option>");
            $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
                  
                },
        error: function () {
            console.error("Error al obtener la lista de tipos de comprobantes.");
            }
        });
}

function sec_comprobante_reporte_filtro_razon_social_listar(){
    let select = $("[name='search_comp_reporte_razon_social_id']");
    
    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: "POST",
        data: {
            accion: "comp_empresa_at_listar_nombre"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            let opcionDefault = $("<option value=0 selected>Todos</option>");
            $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
                
            },
        error: function () {
            console.error("Error al obtener la lista de tipos de comprobantes.");
            }
        });
}

function sec_comprobante_reporte_filtro_etapa_listar(){
    let select = $("[name='search_comp_reporte_etapa_id']");
    
    $.ajax({
        url: "/sys/get_comprobante_reporte.php",
        type: "POST",
        data: {
            accion: "comp_reporte_etapa_listar"
            },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
        
            $(select).empty();
        
            let opcionDefault = $("<option value=0 selected>Todos</option>");
            $(select).append(opcionDefault);
        
            $(respuesta.result).each(function (i, e) {
                let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
                $(select).append(opcion);
                });
                
            },
        error: function () {
            console.error("Error al obtener la lista de tipos de etapas.");
            }
        });
}

//////////   FUNCIONES PARA FILTRO DEz BUSQUEDA


function buscarComprobanteReportePorParametros() {
	$("#sec_comprobante_reporte_div_listar").hide();
    sec_comp_reporte_listar_datatable();

}

function sec_comp_reporte_listar_datatable() {

    if(sec_id == "comprobante" && sub_sec_id == "reporte"){

	$("#sec_comprobante_reporte_div_listar").show();
	var proveedor_id = $("#search_comp_reporte_proveedor_id").val();
	var razon_social_id = $("#search_comp_reporte_razon_social_id").val();
	var etapa_id = $("#search_comp_reporte_etapa_id").val();
	var estado_id = $("#search_comp_reporte_estado_id").val();

	var fecha_inicio_registro = $("#search_comp_reporte_fecha_inicio_registro").val();
	var fecha_fin_registro = $("#search_comp_reporte_fecha_fin_registro").val();
	var fecha_inicio_emision = $("#search_comp_reporte_fecha_inicio_emision").val();
	var fecha_fin_emision = $("#search_comp_reporte_fecha_fin_emision").val();


	
	if (fecha_inicio_registro.length > 0 && fecha_fin_registro.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio_registro);
		var fecha_fin_date = new Date(fecha_fin_registro);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
			return false;
		}
	}
	if (fecha_inicio_emision.length > 0 && fecha_fin_emision.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio_emision);
		var fecha_fin_date = new Date(fecha_fin_emision);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta', 5);
			return false;
		}
	}

    var data = {
        "accion": "comprobante_reporte_listar",
        proveedor_id: proveedor_id,
		razon_social_id: razon_social_id,
		etapa_id: etapa_id,
		estado_id: estado_id,
		fecha_inicio_registro: fecha_inicio_registro,
		fecha_fin_registro: fecha_fin_registro,
		fecha_inicio_emision: fecha_inicio_emision,
		fecha_fin_emision: fecha_fin_emision
    }

    tabla = $("#sec_comprobante_reporte_div_listar_datatable").dataTable(
        {
            language:{
                "decimal":        "",
                "emptyTable":     "No existen registros",
                "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered": "(filtrado de _MAX_ entradas)",
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
                url : "/sys/get_comprobante_reporte.php",
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
                    targets: [0, 1, 2, 3, 4, 5, 6,7,8,9]
                },
                {
                    render: $.fn.dataTable.render.number(',', '.', 2),
                    targets: 7
                }
            ],
            "bDestroy" : true,
            aLengthMenu:[10, 20, 30, 40, 50, 100]
        }
    ).DataTable();
    }
}

$('#btn_comp_reporte_exportar_filtros_de_busqueda').click(function() {
		
    var proveedor_id = $("#search_comp_reporte_proveedor_id").val();
    var razon_social_id = $("#search_comp_reporte_razon_social_id").val();
    var etapa_id = $("#search_comp_reporte_etapa_id").val();
    var estado_id = $("#search_comp_reporte_estado_id").val();

    var fecha_inicio_registro = $("#search_comp_reporte_fecha_inicio_registro").val();
    var fecha_fin_registro = $("#search_comp_reporte_fecha_fin_registro").val();
    var fecha_inicio_emision = $("#search_comp_reporte_fecha_inicio_emision").val();
    var fecha_fin_emision = $("#search_comp_reporte_fecha_fin_emision").val();


    
    if (fecha_inicio_registro.length > 0 && fecha_fin_registro.length > 0) {
        var fecha_inicio_date = new Date(fecha_inicio_registro);
        var fecha_fin_date = new Date(fecha_fin_registro);
        if (fecha_inicio_date > fecha_fin_date) {
            alertify.error('La fecha de registro desde debe ser menor o igual a la fecha de registro hasta ', 5);
            return false;
        }
    }
    if (fecha_inicio_emision.length > 0 && fecha_fin_emision.length > 0) {
        var fecha_inicio_date = new Date(fecha_inicio_emision);
        var fecha_fin_date = new Date(fecha_fin_emision);
        if (fecha_inicio_date > fecha_fin_date) {
            alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta', 5);
            return false;
        }
    }

    var data = {
        "accion": "comp_reporte_exportar_listado",
        "proveedor_id": proveedor_id,
        "razon_social_id": razon_social_id,
        "etapa_id": etapa_id,
        "estado_id": estado_id,
        "fecha_inicio_registro": fecha_inicio_registro,
        "fecha_fin_registro": fecha_fin_registro,
        "fecha_inicio_emision": fecha_inicio_emision,
        "fecha_fin_emision": fecha_fin_emision
    }
    $.ajax({
        url: "/sys/get_comprobante_reporte.php",
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

});

function sec_comprobante_reporte_exportar_zip(comprobante_id) {

    var data = {
        "accion": "comp_exportar_zip",
        "comprobante_id": comprobante_id
    };

    $.ajax({
        url: "/sys/get_comprobante_pago.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            let obj = JSON.parse(resp);
            window.open(obj.ruta_archivo);
            loading(false);
        },
        error: function (resp, status) {
        }
    });
}
