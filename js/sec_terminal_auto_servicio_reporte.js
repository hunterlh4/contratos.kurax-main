let cajeros = [];
let locales = [];

$(document).ready(function () {
    if ($('#sec_terminal_auto_servicio_reporte').length !== 0) {
        fnc_sec_terminal_auto_servicio_reporte_listar_cajeros();
        //fnc_sec_terminal_auto_servicio_reporte_listar_locales();
        fnc_render_sec_terminal_reporte_auto_servicio_tabla_transacciones();
        $(".select2").select2({
            "width": "100%"
        });
        $("#sec_terminal_reporte_btn_buscar").click(function (e) {
            e.preventDefault();
            fnc_get_date_render_sec_terminal_reporte_auto_servicio_tabla_transacciones();
        });

        $(document).on('click', '[id^="sec_terminal_auto_servicio_reporte_imprimir_ticket"]', function () {
            let table_id = '#sec_terminal_reporte_auto_servicio_tabla_transacciones';
            let row = $(this).data('row');
            let $table = $(table_id);
            if ($.fn.DataTable.isDataTable(table_id)) {
                let datatable = $table.DataTable();
                let dataRow = datatable.row(row).data();
                if(dataRow)  {
                    dataRow.is_copy = true;
                    dataRow.cajero = fnc_sec_terminal_auto_servicio_get_nombre_cajero_from_local_storage(dataRow.user_created)
                    dataRow.local_nombre = fnc_sec_terminal_auto_servicio_get_nombre_local_from_local_storage(dataRow.cc_id);
                    fnc_sec_terminal_imprimir_ticket(dataRow);
                }
            }
        });
    }
});

function fnc_get_date_render_sec_terminal_reporte_auto_servicio_tabla_transacciones() {

    let fecha_inicio = $("#sec_terminal_reporte_fecha_inicio").val();
    let fecha_fin = $("#sec_terminal_reporte_fecha_fin").val();

    const d1 = Date.parse(fecha_inicio);
    const d2 = Date.parse(fecha_fin);

    if (d1 > d2) {
        swal('Alerta!', 'El campo Fecha Inicio no puede ser mayor al campo Fecha Fin.', 'error');
        return;
    }

    var data = {
        fecha_inicio,
        fecha_fin,
        tipo: $("#sec_terminal_reporte_tipo_transaccion").val(),
        cc_id: $("#sec_terminal_reporte_local").val(),
        cajero: $("#sec_terminal_reporte_cajero").val(),
        accion: 'listar-transacciones'
    }

    $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio_reporte.php',
        data: data,
        async: true,
        success: function (response) {
            loading(false);
            let jsonData = JSON.parse(response);
            console.log(jsonData);
            if (jsonData.error === false) {
                fnc_render_sec_terminal_reporte_auto_servicio_tabla_transacciones(jsonData.data.data_table)
                $("#sec_terminal_reporte_cant_depositos").val(jsonData.data.summary.transacciones_deposito);
                $("#sec_terminal_reporte_cant_pagos").val(jsonData.data.summary.transacciones_retiro);
                $("#sec_terminal_reporte_total_ventas").val(jsonData.data.summary.monto_deposito);
                $("#sec_terminal_reporte_total_pagos").val(jsonData.data.summary.monto_retiro);
            } else {
                swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), 'error');
            }
        },
        beforeSend: function () {
            loading(true);
        },
        error: function (xhr) {
            loading(false);
            swal(xhr.statusText, xhr.responseText, 'error');
        }
    });
}

function fnc_render_sec_terminal_reporte_auto_servicio_tabla_transacciones(data = {}) {
    let table = $('#sec_terminal_reporte_auto_servicio_tabla_transacciones').DataTable();
    table.clear();
    table.destroy();

    table = $('#sec_terminal_reporte_auto_servicio_tabla_transacciones').DataTable({
        'destroy': true,
        "autoWidth": false,
        dom: 'Bfrtip',
        buttons: {
            buttons: [
                {
                    className: 'btn-primary',
                    text: '<i class="fa fa-search"> Buscar</i>',
                    action: function () {
                        fnc_get_date_render_sec_terminal_reporte_auto_servicio_tabla_transacciones();
                    }
                },
                {
                    extend: 'excelHtml5',
                    className: 'btn-success',
                    text: '<i class="fa fa-file-excel-o"> Exportar</i>'
                }

            ],
            dom: {
                button: {
                    className: 'btn'
                },
                buttonLiner: {
                    tag: null
                }
            }
        },
        fnDrawCallback: function () {
            // $(".switch").bootstrapToggle({
            //     on: "Encendido ",
            //     off: "Apagado",
            //     onstyle: "info",
            //     offstyle: "danger",
            // });
        },
        data: data,
        order: [
            [0, 'desc']
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
        },
        columnDefs: [{
            className: 'text-center',
            targets: [0, 1, 2, 3, 4, 5, 6]
        },],
        "columns": [
            {
                data: 'fecha'
            },
            {
                data: 'hora'
            },
            {
                data: 'tipo_transaccion'
            },
            {
                data: 'monto'
            },
            {
                data: 'user_created',
                render: function (data) {
                    return fnc_sec_terminal_auto_servicio_get_nombre_cajero_from_local_storage(data)
                }
            },
            {
                data: 'cc_id',
                render: function (data, type, row) {
                    return fnc_sec_terminal_auto_servicio_get_nombre_local_from_local_storage(data, row.caja_id);
                }
            },
            {
                data: 'nombre_terminal'
            },
            {
                data: null,
                className: 'text-center',
                render: function (data, type, obj, meta) {
                    return '<div class="dropdown">' +
                        '<button id="dLabel_' + meta.row + '" type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Acciones <span class="caret"></span></button>' +
                        '<ul class="dropdown-menu" aria-labelledby="dLabel_' + meta.row + '">' +
                        '<li><a class="btn btn-sm" data-row="' + meta.row + '" role="button" href="#" id="sec_terminal_auto_servicio_reporte_imprimir_ticket_' + obj.id + '" data-id="' + obj.id + '" title="Imprimir Ticket"> Reimprimir Ticket </a></li>' +
                        '</ul>' +
                        '</div>';
                }
            }
        ]


    });
}

function fnc_sec_terminal_auto_servicio_reporte_listar_cajeros() {
    let data = {
        'accion': 'listar-cajeros',
    };
    $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio_reporte.php',
        data: data,
        success: function (response) {
            let jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                let $select = $('#sec_terminal_reporte_cajero');
                let data = jsonData.data;
                for (let index = 0; index < data.length; index++) {
                    $select.append('<option value="' + data[index].cod_cajero + '">' + data[index].nombre_cajero + '</option>');
                }
                localStorage.setItem("cajeros", JSON.stringify(data));
            } else {
                swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), 'error');
            }
        }
    });
}

function fnc_sec_terminal_auto_servicio_reporte_listar_locales() {
    let data = {
        'accion': 'listar-locales',
    };
    $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio_reporte.php',
        data: data,
        success: function (response) {
            let jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                let $select = $('#sec_terminal_reporte_local');
                let data = jsonData.data;
                for (let index = 0; index < data.length; index++) {
                    $select.append('<option data-id="' + data[index].id + '" value="' + data[index].cc_id + '">[' + data[index].cc_id + '] ' + data[index].nombre + '</option>');
                }
                localStorage.setItem("locales", JSON.stringify(data));
            } else {
                swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), 'error');
            }
        }
    });
}

function fnc_sec_terminal_auto_servicio_get_nombre_cajero_from_local_storage(cod_cajero) {
    let nombre_cajero = '';
    if (cajeros.length === 0) {
        cajeros = JSON.parse(localStorage.getItem('cajeros'));
    }
    cajeros.every(function (cajero) {
        if (parseInt(cajero.cod_cajero) === parseInt(cod_cajero)) {
            nombre_cajero = cajero.nombre_cajero;
            return false;
        }
        return true;
    });
    return nombre_cajero;
}

function fnc_sec_terminal_auto_servicio_get_nombre_local_from_local_storage(cc_id, caja_id = '') {
    let nombre_local = '';
    if (locales.length === 0) {
        locales = JSON.parse(localStorage.getItem("locales"));
    }
    locales.every(function (local) {
        if (local.cc_id === cc_id) {
            if(caja_id) {
               caja_id =  '[ ' + caja_id + ' ] ';
            }
            nombre_local =  caja_id + local.nombre;
            return false;
        }
        return true;
    });
    return nombre_local;
}