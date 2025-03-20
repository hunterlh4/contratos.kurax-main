$(document).ready(function () {
    if ($('#sec_terminal_auto_servicio_reporte_transaccciones').length !== 0) {
        fnc_sec_terminal_auto_servicio_reporte_transacciones_render_tabla_balance_transacciones(null);
        fnc_sec_terminal_auto_servicio_reporte_trasacciones_render_tabla_transacciones();

        /*$.when(fnc_sec_terminal_auto_servicio_reporte_transacciones_listar_locales()).done(function () {
            let local_id = $('#sec_terminal_auto_servicio_reporte_transaccciones_local_id').val();
            if (local_id) {
                fnc_sec_terminal_auto_servicio_reporte_transacciones_get_nombres_terminal_by_local_id(local_id);
            }
        });*/

        $('#sec_terminal_auto_servicio_reporte_transaccciones_local_id').on('change', function () {
            fnc_sec_terminal_auto_servicio_reporte_transacciones_get_nombres_terminal_by_local_id(this.value);
        });

        $(document).on('click', '[id^="sec_terminal_auto_servicio_reporte_transaccciones_ver_balance"]', function () {
            let id = $(this).data('id');
            $.when(fnc_sec_terminal_auto_servicio_reporte_transacciones_get_balance_transaccion(id)).done(function (response) {
                let jsonData = JSON.parse(response);
                if (jsonData.error === false) {
                    fnc_sec_terminal_auto_servicio_reporte_transacciones_render_tabla_balance_transacciones(jsonData.data);
                    $('#sec_terminal_auto_servicio_reporte_transaccciones_modal_balance_transacciones').modal('show');
                } else {
                    swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), 'error');
                }
            });
            return false;
        });

        $(document).on('click', '[id^="sec_terminal_auto_servicio_reporte_transaccciones_ver_ticket_data"]', function () {
            let ticket_id = $(this).data('txn-id');
            $.when(fnc_sec_terminal_auto_servicio_reporte_transacciones_get_ticket_data(ticket_id)).done(function (response) {
                let jsonData = JSON.parse(response);
                if (jsonData.error === false) {
                    let data = jsonData.data;
                    $('#json-renderer').jsonViewer(data);
                    $('#sec_terminal_auto_servicio_reporte_transaccciones_modal_ticket_data').modal('show');
                } else {
                    swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), 'error');
                }

            });

            return false;
        });

        $("#sec_terminal_auto_servicio_reporte_transaccciones_modal_ticket_data").on('hidden.bs.modal', function () {
            $('#json-renderer').empty();
        });

        let local_id = $('#sec_terminal_auto_servicio_reporte_transaccciones_local_id').val();
        if (local_id) {
            fnc_sec_terminal_auto_servicio_reporte_transacciones_get_nombres_terminal_by_local_id(local_id);
        }
    }
});

function fnc_sec_terminal_auto_servicio_reporte_transacciones_listar_transacciones_by_terminal() {

    let fecha_inicio = $('#sec_terminal_auto_servicio_reporte_transaccciones_fecha_inicio').val();
    let fecha_fin = $('#sec_terminal_auto_servicio_reporte_transaccciones_fecha_fin').val();
    let local_id = $('#sec_terminal_auto_servicio_reporte_transaccciones_local_id').val();
    let $select_terminal_id = $('#sec_terminal_auto_servicio_reporte_transaccciones_terminal_id');
    let id_terminal_auto_servicio = $select_terminal_id.val();

    if (!$.trim(fecha_inicio)) {
        swal('Alerta!', 'El campo Fecha de Inicio no puede estar vacío.', 'error');
        return;
    }

    if (!$.trim(fecha_fin)) {
        swal('Alerta!', 'El campo Fecha de Fin no puede estar vacío.', 'error');
        return;
    }

    const d1 = Date.parse(fecha_inicio);
    const d2 = Date.parse(fecha_fin);

    if (d1 > d2) {
        swal('Alerta!', 'El campo Fecha Inicio no puede ser mayor al campo Fecha Fin.', 'error');
        return;
    }

    if ($.trim(local_id) === '') {
        swal('Alerta!', 'El campo Local no puede estar vacío.', 'error');
        return;
    }

    if ($.trim(id_terminal_auto_servicio) === '') {
        swal('Alerta!', 'El campo Terminal no puede estar vacío.', 'error');
        return;
    }

    let data = {
        fecha_inicio,
        fecha_fin,
        local_id,
        id_terminal_auto_servicio,
        accion: 'listar-transacciones-by-terminal'
    }

    $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio_reporte.php',
        data: data,
        success: function (response) {
            loading(false);
            let jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                if (jsonData.data.length === 0) {
                    alertify.warning(
                        'No existen transacciones.'
                    );
                }
                fnc_sec_terminal_auto_servicio_reporte_trasacciones_render_tabla_transacciones(jsonData.data)
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

function fnc_sec_terminal_auto_servicio_reporte_trasacciones_render_tabla_transacciones(data = {}) {
    let table_id = '#sec_terminal_reporte_transacciones_auto_servicio_tabla_transacciones';
    let datatable;
    if (!$.fn.DataTable.isDataTable(table_id)) {
        datatable = $(table_id).DataTable({
            destroy: true,
            autoWidth: false,
            dom: 'Bfrtip',
            buttons: {
                buttons: [
                    {
                        className: 'btn-primary',
                        text: '<i class="fa fa-search"> Buscar</i>',
                        action: function () {
                            fnc_sec_terminal_auto_servicio_reporte_transacciones_listar_transacciones_by_terminal();
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
            },
            data: data,
            order: [
                [0, 'desc']
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
            },
            columnDefs: [
                {
                    className: 'text-center',
                    targets: [0, 1, 2, 3, 4, 5]
                },
            ],
            columns: [
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
                    data: 'operacion'
                },
                {
                    data: 'monto'
                },
                {
                    data: 'nombre_terminal'
                },
                {
                    data: 'transaccion_rechazada',
                    className: 'text-center',
                    render: function (data) {
                        data = parseInt(data);
                        let text = 'No';
                        let status = 'success';
                        if (data === 1) {
                            text = 'Si';
                            status = 'danger';
                        }
                        return '<span class="label label-' + status + '">' + text + '</span>'
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    render: function (data, type, obj, meta) {
                        return '<div class="dropdown">' +
                            '<button id="dLabel_' + meta.row + '" type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Acciones <span class="caret"></span></button>' +
                            '<ul class="dropdown-menu" aria-labelledby="dLabel_' + meta.row + '">' +
                            '<li><a class="btn btn-sm" role="button" href="#" id="sec_terminal_auto_servicio_reporte_transaccciones_ver_balance_' + data.id + '" data-id="' + data.id + '" title="Ver Balance"> Ver Balance </a></li>' +
                            '<li><a class="btn btn-sm" role="button" href="#" id="sec_terminal_auto_servicio_reporte_transaccciones_ver_ticket_data_' + data.id + '" data-id="' + data.id + '" data-txn-id="' + data.txn_id + '" title="Ver Ticket Data"> Ver Ticket Data </a></li>' +
                            '</ul>' +
                            '</div>';
                    }
                }
            ]
        });
    } else {
        datatable = new $.fn.dataTable.Api(table_id);
        datatable.clear();
        datatable.rows.add(data);
        datatable.draw();
    }
    return datatable;
}

function fnc_sec_terminal_auto_servicio_reporte_transacciones_listar_locales() {
    let data = {
        accion: 'listar-locales-by-user-id',
        usuario_id: $('#sec_terminal_auto_servicio_reporte_transaccciones_usuario_id').val()
    };
    return $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio_reporte.php',
        data: data,
        success: function (response) {
            loading(false);
            let jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                let $select = $('#sec_terminal_auto_servicio_reporte_transaccciones_local_id');
                let data = jsonData.data;
                if (data.length) {
                    for (let index = 0; index < data.length; index++) {
                        $select.append('<option value="' + data[index].id + '">[' + data[index].cc_id + '] ' + data[index].nombre + '</option>');
                    }
                } else {
                    alertify.warning(
                        'No existen locales para el usuario actual.'
                    );
                }
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

function fnc_sec_terminal_auto_servicio_reporte_transacciones_get_nombres_terminal_by_local_id(local_id) {
    let data = {
        accion: 'get-nombres-terminal-by-local-id',
        local_id: parseInt(local_id)
    };
    return $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio_reporte.php',
        data: data,
        success: function (response) {
            loading(false);
            let jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                let $select = $('#sec_terminal_auto_servicio_reporte_transaccciones_terminal_id');
                $select.find('option').remove();
                let data = jsonData.data;
                if (data.length) {
                    for (let index = 0; index < data.length; index++) {
                        $select.append('<option value="' + data[index].id + '">' + data[index].nombre_terminal + '</option>');
                    }
                    alertify.success(
                        'Se encontraron terminales para el local seleccionado.'
                    );
                } else {
                    alertify.warning(
                        'No existen terminales para el local seleccionado.'
                    );
                }
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

function fnc_sec_terminal_auto_servicio_reporte_transacciones_get_balance_transaccion(id) {
    let data = {
        accion: 'get-balance-transaccion',
        transaccion_id: id
    };
    return $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio_reporte.php',
        data: data,
        success: function (response) {
            loading(false);
            return response;
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


function fnc_sec_terminal_auto_servicio_reporte_transacciones_render_tabla_balance_transacciones(data = {}) {
    let table_id = '#sec_terminal_auto_servicio_reporte_transaccciones_modal_balance_transacciones_tabla_transacciones';
    let datatable;
    if (!$.fn.DataTable.isDataTable(table_id)) {
        datatable = $(table_id).DataTable({
            destroy: true,
            autoWidth: false,
            lengthChange: false,
            paginate: false,
            filter: false,
            info: false,
            data,
            order: [
                [0, 'desc']
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
            },
            columnDefs: [
                {
                    className: 'text-center',
                    targets: [0, 1, 2]
                },
            ],
            columns: [
                {
                    data: 'descripcion'
                },
                {
                    data: 'balance'
                },
                {
                    data: 'balance_actual'
                },
                {
                    data: 'balance_nuevo'
                },
                {
                    data: 'monto_transaccion'
                }
            ]
        });
    } else {
        datatable = new $.fn.dataTable.Api(table_id);
        datatable.clear();
        datatable.rows.add(data);
        datatable.draw();
    }
    return datatable;
}

function fnc_sec_terminal_auto_servicio_reporte_transacciones_get_ticket_data(ticket_id) {
    let data = {
        accion: 'get-ticket-id',
        ticket_id
    };
    return $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio_reporte.php',
        data: data,
        success: function (response) {
            loading(false);
            return response;
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