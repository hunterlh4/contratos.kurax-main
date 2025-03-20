$(document).ready(function () {
    if ($('#sec_soporte_alerta_caja_cash_in_retail').length !== 0) {
        fncRenderizarDataTableListadoCajasAlertaCajaCashIn();
    }
});

function fncRenderizarDataTableListadoCajasAlertaCajaCashIn() {
    let $table = $('#tbl_alerta_caja_cash_in_switch');
    let table = $table.DataTable();
    table.clear();
    table.destroy();
    table = $table.DataTable({
        destroy: true,
        autoWidth: false,
        ajax: {
            type: 'POST',
            async: false,
            url: 'sys/get_soporte_alerta_caja_cash_in.php',
            data: {
                accion: 'sec_soporte_alerta_caja_cash_in_listar_cajas'
            }

        },
        dataSrc: function (json) {
            console.log(json);
            var result = JSON.parse(json);
            return result.data;
        },
        order: [
            [3, 'asc']
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
        },
        columnDefs: [
            {
                searchable: false,
                orderable: false,
                className: 'text-center',
                targets: [0, 2, 3]
            }
        ],
        columns: [

            {
                data: 'cc_id'
            },
            {
                data: 'local'
            },
            {
                data: 'terminal_name'
            },
            {
                data: 'proveedor_id'
            },
            {
                data: 'config_param',
                render: function (data, type, row) {
                    if (parseInt(data) === 1) {
                        return '<span id="idSpan' + row.id + '">Encendida</span>';
                    } else {
                        return '<span id="idSpan' + row.id + '">Apagada</span>';
                    }

                }
            },
            {
                data: null,
                className: 'text-center',
                render: function (data) {
                    let input = '';
                    if (parseInt(data.config_param) === 1) {
                        input = '<button class="btn btn-info" type="button" id="' + data.id + '">Apagar</button>';
                    }
                    return input;
                }
            }
        ]
    });
    let $tbody = $('#tbl_alerta_caja_cash_in_switch tbody');

    $tbody.off('click');

    $tbody.on('click', '.btn', function () {

        let data = table.row($(this).parents('tr')).data();
        let rowColor = $(this).parents('tr');
        let idRow = table.row($(this).parents('tr'));

        let rowData = null;
        if (!data) {
            let selected_row = $(this).parents('tr');
            if (selected_row.hasClass('child')) {
                selected_row = selected_row.prev();
            }
            rowData = $('#tbl_alerta_caja_cash_in_switch').DataTable().row(selected_row).data();
        } else {
            rowData = data;
        }
        data = new FormData();
        data.append('accion', 'sec_soporte_alerta_caja_cash_in_cambiar_estado');
        data.append('id', rowData.id);
        let checked_estado = '';
        // $('.switchAp').change(function() {
        //     checked_estado = $(this).prop('checked');
        // })
        $.ajax({
            type: "POST",
            data: data,
            url: 'sys/get_soporte_alerta_caja_cash_in.php',
            contentType: false,
            processData: false,
            cache: false,
            success: function (response) {

                let jsonData = JSON.parse(response);
                //console.log(jsonData.error);
                if (jsonData.error === false) {
                    swal("Apagado", jsonData.message, "success");
                    // if (checked_estado == true) {
                    //     checked_estado = true;
                    //     rowColor.css('background-color', '');
                    // }else{
                    //     checked_estado = false;
                    //     rowColor.css('background-color', '#FFE3C9');
                    // }
                    let idSpan_elemente = '#idSpan' + rowData.id;
                    $(idSpan_elemente).text('Apagada');
                } else {
                    swal("Error", jsonData.message, "error");

                    // if (checked_estado == true) {
                    //     checked_estado = false;
                    //     rowColor.css('background-color', '#FFE3C9');
                    // }else{
                    //     checked_estado = true;
                    //     rowColor.css('background-color', '');
                    // }
                }
                let switch_elemente = '#' + rowData.id
                console.log("🚀 ~ file: sec_soporte_alerta_caja_cash_in.js:152 ~ switch_elemente", switch_elemente)
                $(switch_elemente).css('display', 'none');
            }
        });
    });
}