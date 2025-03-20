
$(function () {

    var rtlv_ap_altenar_fecha_actual = $('#rtlv_ap_altenar_fecha_actual').val();

    // Generales
    $('#RepTLV_apuestasAltenar_fecha_inicio').datetimepicker({
        format: 'YYYY-MM-DD'
    });
    $('#RepTLV_apuestasAltenar_fecha_fin').datetimepicker({
        format: 'YYYY-MM-DD'
    });
    $('#RepTLV_apuestasAltenar_fecha_inicio').val(rtlv_ap_altenar_fecha_actual);
    $('#RepTLV_apuestasAltenar_fecha_fin').val(rtlv_ap_altenar_fecha_actual);

    $('#RepTLV_apuestasAltenar_fecha_inicio').change(function() {
        var var_fecha_change = $('#RepTLV_apuestasAltenar_fecha_inicio').val();
        if (!(parseInt(var_fecha_change.length) > 0)) {
            $("#RepTLV_apuestasAltenar_fecha_inicio").val(rtlv_ap_altenar_fecha_actual);
        }
    });
    $('#RepTLV_apuestasAltenar_fecha_fin').change(function() {
        var var_fecha_change = $('#RepTLV_apuestasAltenar_fecha_fin').val();
        if (!(parseInt(var_fecha_change.length) > 0)) {
            $("#RepTLV_apuestasAltenar_fecha_fin").val(rtlv_ap_altenar_fecha_actual);
        }
    });

    $('#RepTLV_apuestasAltenar_estado').select2({
        minimumResultsForSearch: -1
    });

    $('#RepTLV_apuestasAltenar_btn_buscar').click(function(){
        RepTLV_apuestasAltenar_listar();
        return false;
    });

    RepTLV_apuestasAltenar_datatable_formato_tlv_pag('#RepTLV_apuestasAltenar_tabla_apuestas_deportivas');


});





//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// LISTAR APUESTAS ALTENAR
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

function RepTLV_apuestasAltenar_limpiar_tabla() {
    $('#RepTLV_apuestasAltenar_tabla_apuestas_deportivas').html(
        '<thead>' +
        '   <tr>' +
        '       <th class="text-center">CAJA</th>' +
        '       <th class="text-center">FECHA</th>' +
        '       <th class="text-center">ID TICKET</th>' +
        '       <th class="text-center">ESTADO</th>' +
        '       <th class="text-center">CLIENTE</th>' +
        '       <th class="text-center">DNI</th>' +
        '       <th class="text-center">MONTO APOSTADO</th>' +
        '       <th class="text-center">MONTO GANADO</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
}

function RepTLV_apuestasAltenar_listar() {

    RepTLV_apuestasAltenar_limpiar_tabla();

    var fecha_inicio = $("#RepTLV_apuestasAltenar_fecha_inicio").val();
    var fecha_fin = $("#RepTLV_apuestasAltenar_fecha_fin").val();
    var estado = $("#RepTLV_apuestasAltenar_estado").val();

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menor a la de fin.', 'warning');
        return false;
    }

    var data = {
        "accion": "listar_apuestas_altenar",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "estado": estado
    }

    auditoria_send({ "proceso": "listar_apuestas_altenar", "data": data });
    $.ajax({
        url: "/sys/get_reportes_televentas.php",
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
            // console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                $('#RepTLV_apuestasAltenar_tabla_apuestas_deportivas tbody').append(
                    '<tr>' +
                    '<td class="text-center" colspan="8">'+ respuesta.status +'</td>' +
                    '</tr>'
                );
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                RepTLV_apuestasAltenar_limpiar_tabla();
                $.each(respuesta.result, function(index, item) {

                    item.MontoApostado = item.MontoApostado.replace(/\D/g, "")
                                        .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    item.MontoGanado = item.MontoGanado.replace(/\D/g, "")
                                        .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                    $('#RepTLV_apuestasAltenar_tabla_apuestas_deportivas tbody').append(
                        '<tr id="'+item.id+'" class="sec_tlv_pag_listado_transaccion">' +
                            // '<td style="display:none;" class="sec_tlv_pag_listado_transaccion_id_transaccion">' + item.id_ticket + '</td>' +
                            '<td class="text-center">' + item.caja + '</td>' +
                            '<td class="text-center">' + item.fecha + '</td>' +
                            '<td class="text-center">' + item.id_ticket + '</td>' +
                            '<td class="text-center">' + item.estado + '</td>' +
                            '<td class="text-center">' + item.cliente + '</td>' +
                            '<td class="text-center">' + item.dni + '</td>' +
                            '<td class="text-center"> S/ ' + item.MontoApostado + '</td>' +  // cierre
                            '<td class="text-center"> S/ ' + item.MontoGanado + '</td>' + // monto apertura
                        '</tr>'
                    );
                });

                RepTLV_apuestasAltenar_datatable_formato_tlv_pag('#RepTLV_apuestasAltenar_tabla_apuestas_deportivas');
                return false;
            }
            return false;
        },
        error: function() {}
    });
}





//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// EXPORTAR
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

function RepTLV_apuestasAltenar_btn_excel(){

    var fecha_inicio = $("#RepTLV_apuestasAltenar_fecha_inicio").val();
    var fecha_fin = $("#RepTLV_apuestasAltenar_fecha_fin").val();
    var estado = $("#RepTLV_apuestasAltenar_estado").val();

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menor a la de fin.', 'warning');
        return false;
    }
    
    var data = {
        "accion": "listar_apuestas_altenar_export_xls",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "estado": estado
    }
    $.ajax({
        url: "/sys/get_reportes_televentas.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            let obj = JSON.parse(resp);
            window.open(obj.path);
            loading(false);
        },
        error: function() {}
    });
}





//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// EXTRAS
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

function RepTLV_apuestasAltenar_datatable_formato_tlv_pag(id) {
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
        
    }
    $(id).DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'info': true,
        'autoWidth': false,
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
        }
    });
}
