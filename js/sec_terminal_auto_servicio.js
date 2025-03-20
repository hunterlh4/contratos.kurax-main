$(document).ready(function () {
    if ($('#sec_form_terminal_balance').length !== 0) {
        setTimeout(() => {
            fnc_sec_terminal_mostrar_terminales()
        }, 1000);

        $(document).on('click', '.btn_modal_deposito_show', function () {
            let usuario_id = $('#sec_terminal_auto_servicio_usuario_id').val();
            let id_terminal_auto_servicio = $(this).attr('data-id-terminal-auto-servicio');
            $.when(fnc_sec_terminal_verificar_caja(usuario_id)).done(function (response1) {
                if (response1.data && response1.data.estado_caja === true) {
                    $.when(fnc_sec_terminal_verificar_estado_terminal(id_terminal_auto_servicio)).done(function (response2) {
                        if (response2.data && response2.data.estado_terminal === true) {
                            $.when(fnc_sec_terminal_verificar_proveedores_activos(id_terminal_auto_servicio)).done(function (response3) {
                                if (response3.data && response3.data.estado_terminal === true) {
                                    fnc_sec_terminal_show_modal_nuevo_deposito(id_terminal_auto_servicio);
                                }
                            });
                            //fnc_sec_terminal_show_modal_nuevo_deposito(id_terminal_auto_servicio);
                        }
                    });
                }
            });
            return false;
        });

        $(document).on('click', '.btn_modal_retiro_show', function () {
            let usuario_id = $('#sec_terminal_auto_servicio_usuario_id').val();
            let id_terminal_auto_servicio = $(this).attr('data-id-terminal-auto-servicio');
            $.when(fnc_sec_terminal_verificar_caja(usuario_id)).done(function (response1) {
                if (response1.data && response1.data.estado_caja === true) {
                    $.when(fnc_sec_terminal_verificar_estado_terminal(id_terminal_auto_servicio)).done(function (response2) {
                        if (response2.data && response2.data.estado_terminal === true) {
                            $.when(fnc_sec_terminal_verificar_proveedores_activos(id_terminal_auto_servicio)).done(function (response3) {
                                if (response3.data && response3.data.estado_terminal === true) {
                                    fnc_sec_terminal_show_modal_nuevo_retiro(id_terminal_auto_servicio);
                                }
                            });
                            //fnc_sec_terminal_show_modal_nuevo_retiro(id_terminal_auto_servicio);
                        }
                    });
                }
            });
            return false;
        });

        /*$('.btn_modal_transacciones_show').click(function () {
            var id_auto_servicio = $(this).attr('data-id-terminal-auto-servicio');
            fnc_sec_terminal_listar_transacciones(id_auto_servicio);
            return false;
        });*/

        $('#btn_dep_guardar').click(function () {
            fnc_sec_terminal_validar_nuevo_deposito();
            return false;
        });

        $('#btn_ret_guardar').click(function () {
            fnc_sec_terminal_validar_nuevo_retiro();
            return false;
        });

        $('#id_btn_sec_terminal_get_report_caja').click(function () {
            fnc_sec_terminal_get_reporte_caja();
            return false;
        });

        $('#sec_terminal_auto_servicio_modal_deposito').on('shown.bs.modal', function () {
            $('#modal_dep_monto')[0].select();
        });

        $(document).on('click', '[name="btn-toggle-block"]', function () {
            let $button = $(this);
            let hold = parseInt($button.data('hold'));
            let id_terminal_auto_servicio = parseInt($button.data('id-terminal-auto-servicio'));
            let accion = 'block_terminal';

            hold = hold === 1 ? 0 : 1;

            let data = {
                id_terminal_auto_servicio, hold, accion
            };

            fnc_sec_terminal_block_terminal(data);

            return false;
        });
    }
});

function fnc_sec_terminal_mostrar_terminales() {

    let local_id = $("#sec_terminal_auto_servicio_local_id").val();
    let data = {
        local_id: local_id,
        accion: 'get_terminal_list'
    }

    auditoria_send({"proceso": "fnc_sec_terminal_mostrar_terminales", "data": data});

    $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio.php',
        data: data,
        async: true,
        cache: false,
        success: function (response) {
            loading(false);
            let jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                let html = '';
                for (let index = 0; index < jsonData.data.length; index++) {
                    const element = jsonData.data[index];

                    let bg_block_terminal = 'bg-danger';
                    if (parseFloat(element.balance) > 0) {
                        bg_block_terminal = 'bg-success';
                    }

                    let hold_active = '';
                    let hold_text = 'OFF';
                    let hold_class = 'danger';

                    if (element.hold === 0) {
                        hold_active = 'active';
                        hold_text = 'ON';
                        hold_class = 'success';
                        bg_block_terminal = 'bg-default';
                    }

                    let div_terminal = '' +
                        '<div class="col-xs-6 col-sm-6 col-md-3 col-lg-3 mb-3">' +
                        '<div id="block-terminal-' + element.id_terminal_auto_servicio + '" class="row block-terminal ' + bg_block_terminal + '">' +
                        '<div class="w-100 block-terminal-header">' +
                        '<div class="row">' +
                        '<div class="col-xs-6 col-md-6 col-lg-6 block-terminal-nombre">' +
                        '<span>' + element.nombre_terminal + '</span>' +
                        '</div>' +
                        '<div class="col-xs-6 col-md-6 col-lg-6 block-terminal-saldo">' +
                        '<strong id="lbl_saldo_terminal_' + element.id_terminal_auto_servicio + '">S/ ' + element.balance + '</strong>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '<div class="w-100 mb-5 pb-1">' +
                        '<div class="row">' +
                        '<div class="col-xs-3 col-md-3 col-lg-3 text-center">' +
                        '<button data-terminal-balance="' + element.id_terminal_balance + '" data-id-terminal-auto-servicio="' + element.id_terminal_auto_servicio + '" type="button" class="btn_modal_deposito_show btn btn-sm btn-primary"><i class="icon fa fa-fw fa-plus"></i></button>' +
                        '</div>' +
                        '<div class="col-xs-3 col-md-3 col-lg-3 text-center">' +
                        '<button data-terminal-balance="' + element.id_terminal_balance + '" data-id-terminal-auto-servicio="' + element.id_terminal_auto_servicio + '" type="button" class="btn_modal_retiro_show btn btn-sm btn-warning"><i class="icon fa fa-fw fa-minus-square"></i></button>' +
                        '</div>' +
                        '<div class="col-xs-3 col-md-3 col-lg-3 text-center">' +
                        '<button data-terminal-balance="' + element.id_terminal_balance + '" data-id-terminal-auto-servicio="' + element.id_terminal_auto_servicio + '" type="button" class="btn_modal_transacciones_show btn btn-sm btn-default"><i class="icon fa fa-fw fa-list-ul"></i></button>' +
                        '</div>' +
                        '<div class="col-xs-3 col-md-3 col-lg-3 text-center">' +
                        '<button name="btn-toggle-block" class="btn btn-default btn-sm text-' + hold_class + ' text-bold ' + hold_active + '" data-toggle="button" aria-pressed="false" data-id-terminal-auto-servicio="' + element.id_terminal_auto_servicio + '" data-key-firebase="' + element.key_firebase + '" data-hold="' + element.hold + '">' + hold_text + '</button>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                    html += div_terminal;
                }

                $('#block-terminales').html(html);

                $('.btn_modal_transacciones_show').click(function () {
                    let nombre_terminal = $(this).closest('.block-terminal').find('.block-terminal-nombre').text();
                    $('#modal_transacciones_nombre_terminal').text(nombre_terminal);
                    let id_auto_servicio = $(this).attr('data-id-terminal-auto-servicio');
                    fnc_sec_terminal_listar_transacciones(id_auto_servicio);
                    return false;
                });

            } else {
                swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), "error");
            }

        },
        beforeSend: function () {
            loading(true);
        },
        error: function (xhr) {
            loading(false);
            swal(xhr.statusText, xhr.responseText, "error");
        }
    });
}

/// INICIO NUEVO DEPOSITO
function fnc_sec_terminal_show_modal_nuevo_deposito(id_auto_servicio) {

    let data = {
        'id_terminal_auto_servicio': id_auto_servicio,
        'accion': 'get_terminal_balance'
    }

    //auditoria_send({"proceso": "fnc_sec_terminal_show_modal_nuevo_deposito", "data": data});

    $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio.php',
        data: data,
        async: true,
        cache: false,
        success: function (response) {
            loading(false);
            let jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                $('#modal_dep_terminal').val(jsonData.data.data_terminal.nombre_terminal);
                $('#modal_dep_id_terminal_auto_servicio').val(jsonData.data.data_terminal.id);
                $('#modal_dep_terminal_id').val(jsonData.data.data_terminal.id);
                $('#modal_dep_nombre_local').val(jsonData.data.data_terminal.nombre_local);
                $('#btn_dep_guardar').show();
                $('#btn_dep_editar').hide();
                $('#sec_terminal_auto_servicio_modal_deposito').modal('show');
                $('#modal_dep_monto').val('0.00');
            } else {
                swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), "error");
            }
        },
        beforeSend: function () {
            loading(true);
        },
        error: function (xhr) {
            loading(false);
            swal(xhr.statusText, xhr.responseText, "error");
        }
    });
}

function fnc_sec_terminal_validar_nuevo_deposito() {
    let $input_monto = $('#modal_dep_monto');
    let id_terminal_auto_servicio = $('#modal_dep_id_terminal_auto_servicio').val();
    let caja_id = $('#modal_dep_caja_id').val();
    let usuario_id = $('#modal_dep_usuario_id').val();
    let cc_id = $('#modal_dep_cc_id').val();
    let monto = $input_monto.val();
    let nombre_terminal = $('#modal_dep_terminal').val();

    if (isEmptyOrSpaces(id_terminal_auto_servicio)) {
        alertify.error('El parámetro id_terminal_auto_servicio no está seteado.', 5);
        return false;
    }

    if (isEmptyOrSpaces(caja_id)) {
        alertify.error('El parámetro caja_id no está seteado.', 5);
        return false;
    }

    if (isEmptyOrSpaces(usuario_id)) {
        alertify.error('El parámetro usuario_id no está seteado.', 5);
        return false;
    }

    if (isEmptyOrSpaces(cc_id)) {
        alertify.error('El parámetro cc_id no está seteado.', 5);
        return false;
    }

    monto = parseFloat(monto);
    if (!monto) {
        alertify.error("Ingrese un monto válido", 5);
        $input_monto.focus();
        return false;
    }

    if (isEmptyOrSpaces(nombre_terminal)) {
        alertify.error('El parámetro nombre_terminal no está seteado.', 5);
        return false;
    }

    swal({
        title: "Recarga de S/ " + monto,
        text: "El deposito se registrara en el terminal " + nombre_terminal,
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Si, Depositar",
        cancelButtonText: "No, cancelar",
        closeOnConfirm: true,
        closeOnCancel: true
    }, function (isConfirm) {
        if (isConfirm) {
            fnc_sec_terminal_guardar_nuevo_deposito();
        }
    });
}

function fnc_sec_terminal_guardar_nuevo_deposito() {
    let caja_id = $('#modal_dep_caja_id').val();
    let cc_id = $('#modal_dep_cc_id').val();
    let usuario_id = $('#modal_dep_usuario_id').val();
    let id_terminal_auto_servicio = $('#modal_dep_id_terminal_auto_servicio').val();
    let nombre_terminal = $('#modal_dep_terminal').val();
    let monto = $('#modal_dep_monto').val();
    let nombre = $('#modal_dep_nombre').val();
    let apellido_paterno = $('#modal_dep_apellido_paterno').val();
    let local = $('#modal_dep_nombre_local').val();
    let local_id = $('#modal_dep_local_id').val();

    let data = new FormData();
    data.append('id_terminal_auto_servicio', id_terminal_auto_servicio);
    data.append('id_terminal_transaccion_tipo', '1');
    data.append('monto_transaccion', monto);
    data.append('cc_id', cc_id);
    data.append('user_created', usuario_id);
    data.append('caja_id', caja_id);
    data.append('local_id', local_id);
    data.append('usuario_id', usuario_id);
    data.append('nombre_terminal', nombre_terminal);
    data.append('accion', 'deposito_retiro');

    auditoria_send({
        "proceso": "fnc_sec_terminal_guardar_nuevo_deposito",
        "data": fnc_sec_terminal_formdata_to_json(data)
    });

    $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio.php',
        cache: false,
        contentType: false,
        processData: false,
        data: data,
        success: function (response) {
            loading(false);
            let jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                let listBalance = jsonData.data.listBalance;
                if (listBalance && listBalance.length) {
                    let terminal_data = listBalance.find(item => item.id_terminal_balance_tipo === 1);
                    $('#lbl_saldo_terminal_' + terminal_data.id_terminal_auto_servicio).html('S/ ' + terminal_data.balance);
                    let block_id = '#block-terminal-' + terminal_data.id_terminal_auto_servicio;
                    if (!parseFloat(terminal_data.balance)) {
                        $(block_id).removeClass('bg-success')
                        $(block_id).addClass('bg-danger')
                    } else {
                        $(block_id).removeClass('bg-danger')
                        $(block_id).addClass('bg-success')
                    }
                    $('#sec_terminal_auto_servicio_modal_deposito').modal('hide');
                    swal("Aviso", 'Se ha registrado exitosamente el depósito', "success");
                    let ticket_data = {
                        id_terminal_auto_servicio: terminal_data.id_terminal_auto_servicio,
                        nombre_terminal: terminal,
                        tipo_transaccion: 'Depósito',
                        local_nombre: local,
                        cc_id: cc_id,
                        cajero: nombre + ' ' + apellido_paterno,
                        created_at: terminal_data.created_at,
                        updated_at: terminal_data.updated_at,
                        monto
                    };

                    fnc_sec_terminal_imprimir_ticket(ticket_data);
                }
            } else {
                swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), "error");
            }
        },
        beforeSend: function () {
            loading(true);
        },
        error: function (xhr) {
            loading(false);
            swal(xhr.statusText, xhr.responseText, "error");
        }
    });
}

/// FIN NUEVO DEPOSITO

/// INICIO NUEVO RETIRO
function fnc_sec_terminal_show_modal_nuevo_retiro(id_auto_servicio) {
    let data = {
        'id_terminal_auto_servicio': id_auto_servicio,
        'accion': 'get_terminal_balance'
    }

    auditoria_send({"proceso": "fnc_sec_terminal_show_modal_nuevo_retiro", "data": data});

    $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio.php',
        data: data,
        cache: false,
        success: function (response) {
            loading(false);
            let jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                let terminal_data = jsonData.data.data_balance.find(item => item.id_terminal_balance_tipo === 1);
                if (parseFloat(terminal_data.balance) === 0) {
                    alertify.error('No se puede efectuar un retiro si el balance es 0.00.', 5);
                    return false;
                }
                $('#modal_ret_terminal').val(jsonData.data.data_terminal.nombre_terminal);
                $('#modal_ret_id_terminal_auto_servicio').val(jsonData.data.data_terminal.id);
                $('#modal_ret_terminal_id').val(jsonData.data.data_terminal.id);
                $('#modal_ret_local').val(jsonData.data.data_terminal.nombre_local);
                $('#modal_ret_monto').val(terminal_data.balance);
                $('#btn_ret_guardar').show();
                $('#btn_ret_editar').hide();
                $('#sec_terminal_auto_servicio_modal_retiro').modal('show');
                $('#modal_ret_observacion').val('');
            } else {
                swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), "error");
            }
        },
        beforeSend: function () {
            loading(true);
        },
        error: function (xhr) {
            loading(false);
            swal(xhr.statusText, xhr.responseText, "error");
        }
    });
}

function fnc_sec_terminal_validar_nuevo_retiro() {
    let nombre_terminal = $('#modal_ret_terminal').val();
    //let id_balance_transaccion = $('#modal_ret_id_balance_transaccion').val();
    let id_terminal_auto_servicio = $('#modal_ret_id_terminal_auto_servicio').val();
    let caja_id = $('#modal_ret_caja_id').val();
    let usuario_id = $('#modal_ret_usuario_id').val();
    let cc_id = $('#modal_ret_cc_id').val();
    let monto = $('#modal_ret_monto').val();

    // var observacion = $('#modal_ret_observacion').val();

    if (id_terminal_auto_servicio.length === 0) {
        alertify.error('El prámetro id_terminal_auto_servicio no esta seteado.', 5);
        return false;
    }

    if (caja_id.length === 0) {
        alertify.error('El prámetro caja_id no esta seteado.', 5);
        return false;
    }

    if (usuario_id.length === 0) {
        alertify.error('El prámetro usuario_id no esta seteado.', 5);
        return false;
    }

    if (cc_id.length === 0) {
        alertify.error('El prámetro cc_id no esta seteado.', 5);
        return false;
    }

    if (nombre_terminal.length === 0) {
        alertify.error('El prámetro nombre_terminal no esta seteado.', 5);
        return false;
    }

    if (!parseFloat(monto)) {
        alertify.error("Ingrese un monto", 5);
        $('#modal_dep_monto').focus();
        return false;
    }

    swal({
        title: "Retiro de S/ " + monto,
        text: "El retiro se registrara en el terminal " + nombre_terminal,
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Si, Retirar",
        cancelButtonText: "No, cancelar",
        closeOnConfirm: true,
        closeOnCancel: true,
    }, function (isConfirm) {
        if (isConfirm) {
            fnc_sec_terminal_guardar_nuevo_retiro();
        }
    });
}

function fnc_sec_terminal_guardar_nuevo_retiro() {
    let id_terminal_auto_servicio = $('#modal_ret_id_terminal_auto_servicio').val();
    let nombre_terminal = $('#modal_ret_terminal').val();
    let caja_id = $('#modal_ret_caja_id').val();
    let usuario_id = $('#modal_ret_usuario_id').val();
    let cc_id = $('#modal_ret_cc_id').val();
    let monto = $('#modal_ret_monto').val();
    let nombre = $('#modal_ret_nombre').val();
    let apellido_paterno = $('#modal_ret_apellido_paterno').val();
    let local = $('#modal_ret_local').val();
    let local_id = $('#modal_ret_local_id').val();

    let data = new FormData();
    data.append('id_terminal_auto_servicio', id_terminal_auto_servicio);
    data.append('id_terminal_transaccion_tipo', '2');
    data.append('monto_transaccion', monto);
    data.append('retiro_observacion', '');
    data.append('cc_id', cc_id);
    data.append('user_created', usuario_id);
    data.append('caja_id', caja_id);
    data.append('local_id', local_id);
    data.append('usuario_id', usuario_id);
    data.append('nombre_terminal', nombre_terminal);
    data.append('accion', 'deposito_retiro');

    auditoria_send({
        "proceso": "fnc_sec_terminal_guardar_nuevo_retiro",
        "data": fnc_sec_terminal_formdata_to_json(data)
    });

    $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio.php',
        cache: false,
        contentType: false,
        processData: false,
        data: data,
        success: function (response) {
            loading(false);
            const jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                if (jsonData.status === 'success') {
                    let listBalance = jsonData.data.listBalance;
                    if (listBalance && listBalance.length) {
                        let terminal_data = listBalance.find(item => item.id_terminal_balance_tipo === 1);
                        $('#lbl_saldo_terminal_' + id_terminal_auto_servicio).html('S/ ' + terminal_data.balance);
                        let block_id = '#block-terminal-' + id_terminal_auto_servicio;
                        if (parseFloat(terminal_data.balance) === 0) {
                            $(block_id).removeClass('bg-success')
                            $(block_id).addClass('bg-danger')
                        } else {
                            $(block_id).removeClass('bg-danger')
                            $(block_id).addClass('bg-success')
                        }
                        swal("Aviso", 'Se ha registrado exitosamente el retiro', "success");
                        $('#sec_terminal_auto_servicio_modal_retiro').modal('hide');
                        let ticket_data = {
                            id_terminal_auto_servicio: terminal_data.id_terminal_auto_servicio,
                            nombre_terminal: terminal,
                            tipo_transaccion: 'Retiro',
                            local_nombre: local,
                            cc_id: cc_id,
                            cajero: nombre + ' ' + apellido_paterno,
                            created_at: terminal_data.created_at,
                            updated_at: terminal_data.updated_at,
                            monto
                        };

                        fnc_sec_terminal_imprimir_ticket(ticket_data);
                    }
                } else if (jsonData.status === 'fail') {
                    swal("Aviso", jsonData.data.retiro_observacion, "error");
                }
            } else {
                swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), "error");
            }
        },
        beforeSend: function () {
            loading(true);
        },
        error: function (xhr) {
            loading(false);
            swal(xhr.statusText, xhr.responseText, "error");
        }
    });
}

/// FIN NUEVO RETIRO

/// INICIO OBTENER REPORTE CAJA
function fnc_sec_terminal_get_reporte_caja() {
    let data = {
        'caja_id': $("#id_input_caja_id_sec_terminal").val(),
        'accion': 'reporte_caja'
    }

    auditoria_send({"proceso": "fnc_sec_terminal_get_reporte_caja", "data": data});

    $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio.php',
        async: true,
        cache: false,
        data: data,
        success: function (response) {
            loading(false);
            const jsonData = JSON.parse(response);
            if (jsonData.error === false && jsonData.data) {
                const data = jsonData.data;
                const report = data['0'];
                $("#sec_terminal_modal_reporte_caja_fecha_operacion").html(data.fecha_operacion);
                $("#sec_terminal_modal_reporte_caja_nombre").html(data.local_nombre);
                $("#sec_terminal_modal_reporte_caja_cc_id").html(report.cc_id);
                $("#sec_terminal_modal_reporte_caja_cantidad_transacciones").html(report.cantidad_transacciones);
                $("#sec_terminal_modal_reporte_caja_deposito").html('S/. ' + (report.deposito || '0.00'));
                $("#sec_terminal_modal_reporte_caja_retiro").html('S/. ' + (report.retiro || '0.00'));
                $("#sec_terminal_modal_reporte_caja_resultado_diario").html('S/. ' + (report.resultado_diario || '0.00'));
                $("#sec_terminal_modal_reporte_caja_fecha").html(report.generado);
                $("#sec_terminal_modal_reporte_caja_usuario").html(data.usuario);
                $('#sec_terminal_modal_reporte_caja').modal('show');
            } else {
                swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), "error");
            }
        },
        beforeSend: function () {
            loading(true);
        },
        error: function (xhr) {
            loading(false);
            swal(xhr.statusText, xhr.responseText, "error");
        }
    });
}

// FIN OBTENER REPORTE CAJA

//INICIO LISTAR DATATABLE
function fnc_sec_terminal_listar_transacciones(id_terminal_auto_servicio) {
    $('#tbl_sec_terminal_transacciones_tbody').html('');
    let data = {
        id_terminal_auto_servicio,
        accion: 'listar_transacciones'
    };

    auditoria_send({"proceso": "fnc_sec_terminal_listar_transacciones", "data": data});

    $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio.php',
        data: data,
        async: true,
        cache: false,
        success: function (response) {
            loading(false);
            let jsonData = JSON.parse(response);
            if (jsonData.error === false && jsonData.data) {
                fnc_render_table_modal_transacciones_terminal(jsonData.data);
                $('#modal_transacciones').modal('show');
            } else {
                swal("Aviso", fnc_sec_terminal_get_response_message(jsonData.msj), "error");
            }
        },
        beforeSend: function () {
            loading(true);
        },
        error: function (xhr) {
            loading(false);
            swal(xhr.statusText, xhr.responseText, "error");
        }
    });
}

function fnc_render_table_modal_transacciones_terminal(data = []) {
    let tbody = '';
    if (data.length) {
        for (let index = 0; index < data.length; index++) {
            const element = data[index];
            const json_element = JSON.stringify(JSON.stringify(element));

            let tr = '' +
                '<tr>' +
                '<td class="text-left">' + element.tipo_transaccion + '</td>' +
                '<td class="text-left">S/ ' + parseFloat(element.monto) + '</td>' +
                '<td class="text-left">Turno ' + (element.turno || '[No encontrado]') + '</td>' +
                '<td class="text-left">' + (element.cajero || '[No encontrado]') + '</td>' +
                '<td class="text-center">' + element.created_at + '</td>' +
                '<td class="text-center">' +
                '<button ' +
                'class="btn btn-warning btn-sm" ' +
                'name="btn-generar-copia-ticket" ' +
                'onclick="fnc_sec_terminal_generar_copia_ticket(this)" ' +
                'data-element=\'' + json_element + '\' ' +
                'data-id_terminal_auto_servicio="' + element.id_terminal_auto_servicio + '">Imprimir Ticket</button>' +
                '</td>' +
                '</tr>';
            tbody += tr;

        }
    } else {
        tbody = '<tr><td class="text-center" colspan="6"> (Sin datos) </td></tr>';
    }
    $('#tbl_sec_terminal_transacciones_tbody').html(tbody);
    return false;
}

//FIN LISTAR DATATABLE

function fnc_sec_terminal_generar_copia_ticket(button) {
    let $button = $(button);
    let json_element = $button.data('element');
    let element = JSON.parse(JSON.parse(decodeURIComponent(json_element)));
    //window.open('/sys/get_terminal_balance_ticket.php?id='+id + '&is_copy=true', '_blank');
    element['is_copy'] = true;
    element['nombre_terminal'] = $('#modal_transacciones_nombre_terminal').text();
    fnc_sec_terminal_imprimir_ticket(element);
}

function fnc_sec_terminal_imprimir_ticket(data) {

    auditoria_send({"proceso": "fnc_sec_terminal_imprimir_ticket", "data": data});

    $.ajax({
        type: 'POST',
        url: '/sys/get_terminal_balance_ticket.php',
        async: true,
        cache: false,
        data: data,
        dataType: 'json',
        success: function (response) {
            loading(false);
            if (response.path) {
                $('#iframe_imprimir_ticket').attr("src", response.path)
                $('#modal_imprimir_ticket').modal('show');
            }
        },
        beforeSend: function () {
            loading(true);
        },
        error: function (xhr) {
            loading(false);
            swal(xhr.statusText, xhr.responseText, "error");
        }
    });
}

function fnc_sec_terminal_block_terminal(data) {
    return $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio.php',
        async: true,
        cache: false,
        data: data,
        dataType: 'json',
        success: function (response) {
            loading(false);
            if (response.error === true) {
                swal("Aviso", fnc_sec_terminal_get_response_message(response.msj), "error");
            }
            return response;
        },
        beforeSend: function () {
            loading(true);
        },
        error: function (xhr) {
            loading(false);
            swal(xhr.statusText, xhr.responseText, "error");
        }
    });
}

function fnc_sec_terminal_verificar_caja(usuario_id) {
    let data = {
        usuario_id,
        accion: "verificar_caja"
    };
    return fnc_get_terminal_auto_servicio_ajax_call(data);
}

function fnc_sec_terminal_verificar_estado_terminal(id_terminal_auto_servicio) {
    let data = {
        id_terminal_auto_servicio,
        accion: 'verificar_estado_terminal'
    };
    return fnc_get_terminal_auto_servicio_ajax_call(data);
}

function fnc_sec_terminal_verificar_proveedores_activos(id_terminal_auto_servicio) {
    let data = {
        id_terminal_auto_servicio,
        accion: "verificar_proveedores_activos"
    };
    return fnc_get_terminal_auto_servicio_ajax_call(data);
}

function isEmptyOrSpaces(str) {
    return str === undefined || str === null || str.match(/^ *$/) !== null;
}

function fnc_sec_terminal_get_response_message(message) {
    if (typeof message === 'object' && message.errorInfo) {
        return message.errorInfo[2];
    }
    return message;
}

function fnc_sec_terminal_formdata_to_json(formData) {
    let object = {};
    formData.forEach(function (value, key) {
        object[key] = value;
    });
    return JSON.stringify(object);
}

function fnc_get_terminal_auto_servicio_ajax_call(data) {
    return $.ajax({
        type: 'POST',
        url: 'sys/get_terminal_auto_servicio.php',
        async: true,
        cache: false,
        data: data,
        dataType: 'json',
        success: function (response) {
            loading(false);
            if (response) {
                if (response.error === false) {
                    return response;
                } else {
                    swal("Aviso", fnc_sec_terminal_get_response_message(response.msj), "error");
                }
            }
        },
        beforeSend: function () {
            loading(true);
        },
        error: function (xhr) {
            loading(false);
            swal(xhr.statusText, xhr.responseText, "error");
        }
    });
}