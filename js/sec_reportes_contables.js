function sec_reportes_contables() {
    if (sec_id == 'reportes_contables') {
        $('#fecha_inicio').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#fecha_fin').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#fecha_inicio').val($('#g_fecha_actual').val());
        $('#fecha_fin').val($('#g_fecha_actual').val());

        $('#fecha_inicio').change(function () {
            var var_fecha_change = $('#fecha_inicio').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#fecha_inicio").val($("#g_fecha_actual").val());
            }
        });
        $('#fecha_fin').change(function () {
            var var_fecha_change = $('#fecha_fin').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#fecha_fin").val($("#g_fecha_actual").val());
            }
        });
        $("#btn_exportar").off('click').on('click', function () {
            exportar_excel_reportes_contables();
        });
    }
}

function exportar_excel_reportes_contables() {
    var fecha_inicio = $.trim($("#fecha_inicio").val());
    var fecha_fin = $.trim($("#fecha_fin").val());
    var tipo_reporte = $("#tipo_reporte").val();
    var numero_comprobante = $("#numero_comprobante").val();

    if (fecha_inicio.length !== 10) {
        $("#fecha_inicio").focus();
        return false;
    }
    if (fecha_fin.length !== 10) {
        $("#fecha_fin").focus();
        return false;
    }

    var data = {
        "accion": "reportes_contables_xls",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "tipo_reporte": tipo_reporte,
        "numero_comprobante" : numero_comprobante
    }

    $.ajax({
        url: "/sys/get_reportes_contables.php",
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
        error: function() {
            loading();
        }
    });
}
