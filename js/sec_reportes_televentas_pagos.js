
function sec_reportes_televentas_pagos() {
    if (sec_id == 'reportes' && sub_sec_id=='televentas_pagos') {

        $('#SecRepTel_fecha_inicio_tlv_pagos').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#SecRepTel_fecha_fin_tlv_pagos').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#SecRepTel_fecha_inicio_tlv_pagos').val($('#g_fecha_actual_tlv_pago').val());
        $('#SecRepTel_fecha_fin_tlv_pagos').val($('#g_fecha_actual_tlv_pago').val());
        $('#SecRptTelPag_txt_cant_pagos').val('0.00');
        $('#SecRptTelPag_txt_total_comision').val('0.00');
        $('#SecRptTelPag_txt_total_retiro').val('0.00');
        $('#SecRptTelPag_txt_cant_dev').val('0.00');
        $('#SecRptTelPag_txt_total_dev').val('0.00');
        $('#SecRepTel_tipo_busqueda').select2();
        $('#SecRepTel_tipo_transaccion').select2();
        $('#SecRepTel_local').select2();
        $('#SecRepTel_cajero').select2();
        $('#SecRepTel_estado_cierre').select2();

        //$('#SecRepTel_cuenta').multiSelect();

        $('#SecRepTelPag_cuenta').multiselect({
            buttonClass:'form-control',
            buttonWidth: '100%',
            includeSelectAllOption: true, 
            onSelectAll: function(options) {
                $.each(options, function(index, item) {
                    //console.log(item[0].value);
                });
            },
            onDeselectAll: function(options) {
                $.each(options, function(index, item) {
                    //console.log(item[0].value);
                });
            },
            onChange: function(element, checked) {

                var activar = 0;
                var id_cuenta =0;
                if (checked === true) {
                    activar =1;
                    id_cuenta=element.val();
                    
                }
                else if (checked === false) {
                    activar =0;
                    id_cuenta=element.val();          
                }
            }
        });

        $('#SecRepTel_fecha_inicio_tlv_pagos').change(function () {
            var var_fecha_change = $('#SecRepTel_fecha_inicio_tlv_pagos').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#SecRepTel_fecha_inicio_tlv_pagos").val($("#g_fecha_actual_tlv_pago").val());
                console.log($("#g_fecha_actual_tlv_pago").val());
            }
        });
        $('#SecRepTel_fecha_fin_tlv_pagos').change(function () {
            var var_fecha_change = $('#SecRepTel_fecha_fin_tlv_pagos').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#SecRepTel_fecha_fin_tlv_pagos").val($("#g_fecha_actual_tlv_pago").val());
            }
        });

        $("#SecRepTelPag_btn_exportar").on('click', function () {
            exportar_excel_SecRepTelPag();
        });

        $('#SecRepTelPag_btn_buscar').click(function() {
            //listar_SecRepTel_tabla_transacciones();
            listar_SecRepTelPag_tabla_pagos();
            listar_SecRepTel_tabla_pagos_totales();
            //listar_SecRepTel_tabla_transacciones_resumen_v2();
        });

        //listar_SecRepTel_tabla_transacciones();
        //limpiar_SecRepTel_tabla_transacciones();
        $('#SecRepTelPag_tabla_transacciones').append(
            '<tr>' +
            '<td class="text-center" colspan="16">Por favor realice la busqueda.</td>' +
            '</tr>'
        );/*
        $('#SecRepTel_tabla_transacciones_totales tbody').append(
            '<tr>' +
            '<td class="text-center" colspan="17">Por favor realice la busqueda.</td>' +
            '</tr>'
        );*/

        $('#SecRepTelPag_comprobante').val(0).trigger('change.select2');
        $('#SecRepTelPag_estado_solicitud').val(1).select2().trigger('change');
        $('#SecRepTelPag_comprobante').val(0).select2().trigger('change');
        $('#SecRepTelPag_pagador').val(0).select2().trigger('change');
        $('#SecRepTelPag_razon').val(0).select2().trigger('change');
        $('#SecRepTelPag_tipo_transaccion').val(0).select2().trigger('change');
        $('#SecRepTelPag_motivo_dev').val(0).select2().trigger('change');
        $('#SecRepTelPag_tipo_busqueda').val(1).select2().trigger('change');


    }
}

function listar_SecRepTelPag_tabla_pagos(){
    var ftable = $('#SecRepTelPag_tabla_pagos').DataTable();
    ftable.clear();
    ftable.destroy();
    var ftable = $('#SecRepTelPag_tabla_pagos').DataTable({
        'destroy': true,
        'scrollX': true,
        "processing": true,
        "serverSide": true,
        "order" : [],
        "ajax": {
            type: "POST",
            async : true,
            "url": "/sys/get_reportes_televentas_pagos.php",
            "data": get_data_listar_SecRepTelPag_tabla_pagos()
        },
        "order": [],
        "language": {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "infoPostFix": "",
            "search": "Buscar: ",
            "url": "",
            "infoThousands": ",",
            "loadingRecords": "Cargando...",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        "columns": [
            {
                "data": null,
                "sortable": false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { "data": "fecha_hora_registro"},
            { "data": "fecha_hora_pago"},
            { "data": "pagador" },
            { "data": "asesor" },
            { "data": "num_doc" },
            { "data": "cliente" },
            { "data": "monto" },
            { "data": "comision" },
            { "data": "banco" },
            { "data": "estado_solicitud" },
            { 
                "data": "link_atencion",
                createdCell: function (td, cellData, item, row, col) {
                    $(td).addClass('text-left');
                },
                render: function (data, type,item,row) {
                    link = '<a href="' + item.link_atencion + '" target="_blank" style="text-transform: lowercase;">' + item.link_atencion + '</a>';
                    return link;
                }
            },
            { "data": "observacion_pagador" },
            { "data": "razon" },            
            { "data": "enviar_comprobante" },
            { "data": "tipo_operacion" },
            { "data": "motivo_devolucion" },
            { "data": "validado_por" }

        ]
    });
}

function get_data_listar_SecRepTelPag_tabla_pagos() {
    var SecRepTelPag_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio_tlv_pagos").val());
    var SecRepTelPag_fecha_fin = $.trim($("#SecRepTel_fecha_fin_tlv_pagos").val());
    var SecRepTelPag_cuenta = $("#SecRepTelPag_cuenta").val();
    var SecRepTelPag_pagador = $("#SecRepTelPag_pagador").val();
    var SecRepTelPag_tipo_busqueda = $("#SecRepTelPag_tipo_busqueda").val();
    var SecRepTelPag_comprobante = $("#SecRepTelPag_comprobante").val();
    var SecRepTelPag_razon = $("#SecRepTelPag_razon").val();
    var SecRepTelPag_tipo_operacion = $("#SecRepTelPag_tipo_transaccion").val();
    var SecRepTelPag_motivo_dev = $("#SecRepTelPag_motivo_dev").val();
    var SecRepTelPag_estado_solicitud = $("#SecRepTelPag_estado_solicitud").val();

    if(SecRepTelPag_estado_solicitud == 999){
        swal('Aviso', "Debe seleccionar el estado de solicitud", 'warning');
        return false;
    }

    if (SecRepTelPag_fecha_inicio.length !== 10) {
        $("#SecRepTel_fecha_inicio_tlv_pagos").focus();
        return false;
    }

    var data = {
        "accion": "listar_pagos_clientes",
        "fecha_inicio": SecRepTelPag_fecha_inicio,
        "fecha_fin": SecRepTelPag_fecha_fin,
        "cuenta": SecRepTelPag_cuenta,
        "pagador": SecRepTelPag_pagador,
        "tipo_busqueda" : SecRepTelPag_tipo_busqueda,
        "comprobante" : SecRepTelPag_comprobante,
        "razon" : SecRepTelPag_razon,
        "tipo_operacion" : SecRepTelPag_tipo_operacion,
        "motivo_dev" : SecRepTelPag_motivo_dev,
        "estado_solicitud" : SecRepTelPag_estado_solicitud
    }
    return data;
}

function exportar_excel_SecRepTelPag() {
    var SecRepTelPag_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio_tlv_pagos").val());
    var SecRepTelPag_fecha_fin = $.trim($("#SecRepTel_fecha_fin_tlv_pagos").val());
    var SecRepTelPag_cuenta = $("#SecRepTelPag_cuenta").val();
    var SecRepTelPag_pagador = $("#SecRepTelPag_pagador").val();
    var SecRepTelPag_tipo_busqueda = $("#SecRepTelPag_tipo_busqueda").val();
    var SecRepTelPag_comprobante = $("#SecRepTelPag_comprobante").val();
    var SecRepTelPag_razon = $("#SecRepTelPag_razon").val();
    var SecRepTelPag_tipo_operacion = $("#SecRepTelPag_tipo_transaccion").val();
    var SecRepTelPag_motivo_dev = $("#SecRepTelPag_motivo_dev").val();
    var SecRepTelPag_estado_solicitud = $("#SecRepTelPag_estado_solicitud").val();


    var data = {
        "accion": "listar_transacciones_pagos_export_xls",
        "fecha_inicio": SecRepTelPag_fecha_inicio,
        "fecha_fin": SecRepTelPag_fecha_fin,
        "cuenta": SecRepTelPag_cuenta,
        "pagador": SecRepTelPag_pagador,
        "tipo_busqueda" : SecRepTelPag_tipo_busqueda,
        "comprobante" : SecRepTelPag_comprobante,
        "razon" : SecRepTelPag_razon,
        "tipo_operacion" : SecRepTelPag_tipo_operacion,
        "motivo_dev" : SecRepTelPag_motivo_dev,
        "estado_solicitud" : SecRepTelPag_estado_solicitud
    }

    $.ajax({
        url: "/sys/get_reportes_televentas_pagos.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            //console.log(respuesta);
            let obj = JSON.parse(resp);
            window.open(obj.path);
            loading(false);
        },
        error: function() {}
    });
}

function listar_SecRepTel_tabla_pagos_totales(){
    var SecRepTelPag_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio_tlv_pagos").val());
    var SecRepTelPag_fecha_fin = $.trim($("#SecRepTel_fecha_fin_tlv_pagos").val());
    var SecRepTelPag_cuenta = $("#SecRepTelPag_cuenta").val();
    var SecRepTelPag_pagador = $("#SecRepTelPag_pagador").val();
    var SecRepTelPag_tipo_busqueda = $("#SecRepTelPag_tipo_busqueda").val();
    var SecRepTelPag_comprobante = $("#SecRepTelPag_comprobante").val();
    var SecRepTelPag_razon = $("#SecRepTelPag_razon").val();
    var SecRepTelPag_tipo_operacion = $("#SecRepTelPag_tipo_transaccion").val();
    var SecRepTelPag_motivo_dev = $("#SecRepTelPag_motivo_dev").val();
    var SecRepTelPag_estado_solicitud = $("#SecRepTelPag_estado_solicitud").val();

    var data = {
        "accion": "listar_totales_pagos_clientes",
        "fecha_inicio": SecRepTelPag_fecha_inicio,
        "fecha_fin": SecRepTelPag_fecha_fin,
        "cuenta": SecRepTelPag_cuenta,
        "pagador": SecRepTelPag_pagador,
        "tipo_busqueda" : SecRepTelPag_tipo_busqueda,
        "comprobante" : SecRepTelPag_comprobante,
        "razon" : SecRepTelPag_razon,
        "tipo_operacion" : SecRepTelPag_tipo_operacion,
        "motivo_dev" : SecRepTelPag_motivo_dev,
        "estado_solicitud" : SecRepTelPag_estado_solicitud
    }

    $.ajax({
        url: "/sys/get_reportes_televentas_pagos.php",
        type: 'POST',
        data: data,
        success: function(resp) {
            var respuesta = JSON.parse(resp);
            if(respuesta.http_code == 200){
                $.each(respuesta.data, function(index, item) {
                    $('#SecRptTelPag_txt_cant_pagos').val(item.cant_pagos);
                    $('#SecRptTelPag_txt_total_comision').val(item.total_comision);
                    $('#SecRptTelPag_txt_total_retiro').val(item.total_monto);
                    $('#SecRptTelPag_txt_cant_dev').val(item.cant_dev);
                    $('#SecRptTelPag_txt_total_dev').val(item.total_dev);
                });
            }
        },
        complete: function() {
            cargar_cargar_multiple_select_pagador();
        },
        error: function() {}
    });
}

$('#SecRepTelPag_estado_solicitud').on('change', function() {
    var valor = parseInt(this.value);
    if(valor == 0 || valor == 2 || valor == 999){
        $('#SecRepTelPag_comprobante').val(999).trigger('change.select2');
    }else if(valor == 1){
        //$('#SecRepTelPag_comprobante').val(0).trigger('change.select2');
    }
});

$('#SecRepTelPag_comprobante').on('change', function() {
    var valor = this.value;
    if(valor != 999){
        $('#SecRepTelPag_estado_solicitud').val(1).select2().trigger('change');
    }
});