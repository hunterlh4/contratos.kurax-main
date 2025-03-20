function sec_reportes_correcciones() {
    if (sec_id == 'reportes' && sub_sec_id == 'correcciones') {
        $('#secRepCor_fecha_inicio').datetimepicker({
            format: 'YYYY-MM-DD',
        });
        $('#secRepCor_fecha_fin').datetimepicker({
            format: 'YYYY-MM-DD',
        });
        $('#secRepCor_fecha_inicio').val($('#secRepCor_g_fecha_actual').val());
        $('#secRepCor_fecha_fin').val($('#secRepCor_g_fecha_actual').val());

        $('#secRepCor_fecha_inicio').change(function () {
            var var_fecha_change = $('#secRepCor_fecha_inicio').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $('#secRepCor_fecha_inicio').val(
                    $('#secRepCor_g_fecha_actual').val()
                );
            }
        });
        $('#secRepCor_fecha_fin').change(function () {
            var var_fecha_change = $('#secRepCor_fecha_fin').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $('#secRepCor_fecha_fin').val(
                    $('#secRepCor_g_fecha_actual').val()
                );
            }
        });

        $('#secRepCor_btn_buscar').click(function () {
            SecRepCor_buscar();
        });

        $('#secRepCor_btn_exportar').on('click', function () {
            SecRepCor_exportar_excel();
        });

        $('.secRepCor_detail_class').hide();
    }
}

function SecRepCor_buscar() {
    SecRepCor_limpiar_data_registros();
    var SecRepCor_fecha_inicio = $.trim($('#secRepCor_fecha_inicio').val());
    var SecRepCor_fecha_fin = $.trim($('#secRepCor_fecha_fin').val());

    if (SecRepCor_fecha_inicio.length !== 10) {
        $('#secRepCor_fecha_inicio').focus();
        return false;
    }
    if (SecRepCor_fecha_fin.length !== 10) {
        $('#secRepCor_fecha_fin').focus();
        return false;
    }
    var data = {
        accion: 'SecRepCor_listar_registros',
        fecha_inicio: SecRepCor_fecha_inicio,
        fecha_fin: SecRepCor_fecha_fin,
    };

    auditoria_send({ proceso: 'SecRepCor_listar_registros', data: data });
    $.ajax({
        url: '/sys/get_reportes_correcciones.php',
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading('true');
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            //  alert(datat)
            var respuesta = JSON.parse(resp);
            console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                SecRepCor_limpiar_data_registros();
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                if (respuesta.result.length > 0) {
                    $.each(respuesta.result, function (index, item) {
                        $('#secRepCor_RES').show();

                        $('#secRepCor_tabla_registros').append(
                            '<tr>' +
                                '<td class="text-center">' +
                                (index + 1) +
                                '</td>' +
                                '<td class="text-center">' +
                                item.fecha_modificacion +
                                '</td>' +
                                '<td class="text-center">' +
                                item.usuario +
                                '</td>' +
                                '<td class="text-center">' +
                                item.modulo +
                                '</td>' +
                                '<td class="text-center">' +
                                item.cliente +
                                '</td>' +
                                '<td class="text-center">' +
                                item.promotor +
                                '</td>' +
                                '<td class="text-center">' +
                                item.caja +
                                '</td>' +
                                '<td class="text-center">' +
                                item.cuenta +
                                '</td>' +
                                '<td class="text-center">' +
                                item.monto +
                                '</td>' +
                                '<td class="text-center">' +
                                item.valid +
                                '</td>' +
                                '<td class="text-center">' +
                                item.cm_tipo_constancia +
                                '</td>' +
                                '<td class="text-center">' +
                                item.cm_cuenta_deposito +
                                '</td>' +
                                '<td class="text-center">' +
                                item.cm_fecha_abono +
                                '</td>' +
                                '<td class="text-center">' +
                                item.cm_num_operacion +
                                '</td>' +
                                '<td class="text-center">' +
                                item.cm_num_operacion_pag +
                                '</td>' +
                                '<td class="text-center">' +
                                item.cm_cuenta_pagadora +
                                '</td>' +
                                '<td class="text-center">' +
                                item.cm_banco_pago +
                                '</td>' +
                                '<td class="text-center">' +
                                item.cant_changes +
                                '</td>' +
                                '<td class="text-center"> ' +
                                '<button type="button" class="btn btn-info btn-sm" ' +
                                '    onclick="SecRepCor_view_detail(' +
                                item.transaccion_id +
                                ',' +
                                item.tipo_id +
                                ')">' +
                                '<span class="fa fa-eye"></span>' +
                                '</button>' +
                                '</td>' +
                                '</tr>'
                        );
                    });
                    SecRepCor_listar_data_registros(
                        '#secRepCor_tabla_registros'
                    );
                } else {
                    $('#secRepCor_tabla_registros').append(
                        '<tr>' +
                            '<td class="text-center" colspan="8">No hay transacciones.</td>' +
                            '</tr>'
                    );
                }

                //console.log(array_clientes);
                return false;
            }
        },
        error: function () {},
    });
}

function SecRepCor_limpiar_data_registros() {
    $('#secRepCor_tabla_registros').html(
        '<thead>' +
            '   <tr>' +
            '       <th width="10%">#</th>' +
            '		<th width="10%">FECHA</th>' +
            '		<th width="10%">MODIFICADOR</th>' +
            '		<th width="20%">MODULO</th>' +
            '		<th width="30%">CLIENTE</th>' +
            '		<th width="30%">USUARIO</th>' +
            '		<th width="30%">CAJA</th>' +
            '		<th width="30%">CUENTA/BANCO PAGO</th>' +
            '		<th width="30%">MONTO</th>' +
            '		<th width="30%">VALIDADOR/PAGADOR</th>' +
            '		<th width="30%">CM-TIPO CONSTANCIA</th>' +
            '		<th width="30%">CM-CUENTA DEPOSITO</th>' +
            '		<th width="30%">CM-FECHA ABONO</th>' +
            '		<th width="30%">CM-NUM OP VALIDADOR</th>' +
            '		<th width="30%">CM-NUM OP PAGADOR</th>' +
            '		<th width="30%">CM-CUENTA PAGADORA</th>' +
            '		<th width="30%">CM-BANCO PAGO</th>' +
            '		<th class="secRepCor_cant_class" width="20%">CANT MODIFICACIONES</th>' +
            '		<th>VER</th>' +
            '   </tr>' +
            '</thead>' +
            '<tbody>'
    );
}

function SecRepCor_listar_data_registros(id) {
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
    }
    $(id).DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        scrollX: true,
        order: [[0, 'asc']],
        info: true,
        autoWidth: false,
        language: {
            processing: 'Procesando...',
            lengthMenu: 'Mostrar _MENU_ registros',
            zeroRecords: 'No se encontraron resultados',
            emptyTable: 'Ningún dato disponible en esta tabla',
            info: 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros',
            infoEmpty:
                'Mostrando registros del 0 al 0 de un total de 0 registros',
            infoFiltered: '(filtrado de un total de _MAX_ registros)',
            infoPostFix: '',
            search: 'Buscar: ',
            url: '',
            infoThousands: ',',
            loadingRecords: 'Cargando...',
            paginate: {
                first: 'Primero',
                last: 'Último',
                next: 'Siguiente',
                previous: 'Anterior',
            },
            aria: {
                sortAscending:
                    ': Activar para ordenar la columna de manera ascendente',
                sortDescending:
                    ': Activar para ordenar la columna de manera descendente',
            },
        },
    });
}

function SecRepCor_view_detail(tra_id, tipo_id) {
    //console.log('tra_id : ' + tra_id + ' tipo_id: ' + tipo_id);
    $('#secRepCor_detail_pagos').hide();
    $('#secRepCor_detail_deposito').hide();
    $('#secRepCor_RES').hide();
    $('.secRepCor_detail_class').show();
    var title_detail = 'REGISTRO ';
    if (parseInt(tipo_id) == 1) {
        title_detail += 'DEPÓSITO';

        var data = {
            accion: 'secRepCor_obtener_detalle_transaccion_deposito',
            transaccion_id: tra_id,
        };

        auditoria_send({
            proceso: 'secRepCor_obtener_detalle_transaccion_deposito',
            data: data,
        });
        $.ajax({
            url: '/sys/get_reportes_correcciones.php',
            type: 'POST',
            data: data,
            beforeSend: function () {
                loading('true');
            },
            complete: function () {
                loading();
            },
            success: function (resp) {
                var respuesta = JSON.parse(resp);
                if (parseInt(respuesta.http_code) == 400) {
                    swal('Aviso', respuesta.status, 'warning');
                    return false;
                }
                if (parseInt(respuesta.http_code) == 200) {
                    $('#secRepCor_detail_imagen_lista').html('');
                    $.each(respuesta.result, function (index, item) {
                        var nuevo_id =
                            item.fecha_hora.toString() +
                            '_' +
                            item.id.toString();
                        $('#secRepCor_detail_imagen_lista').append(
                            '<div class="col-md-12">' +
                                '   <div align="center" style="height: 100%;width: 100%;">' +
                                '       <img class="img-responsive" id="' +
                                nuevo_id +
                                '" src="files_bucket/depositos/' +
                                item.archivo +
                                '" width="300px" />' +
                                '   </div>' +
                                '</div>'
                        );
                        $('#' + nuevo_id).imgViewer2();

                        $('#secRepCor_det_dep_cuenta').val(item.cuenta);
                        $('#secRepCor_det_dep_tipo_constancia').val(
                            item.tipo_constancia
                        );
                        $('#secRepCor_det_dep_fecha_abono').val(
                            item.registro_deposito
                        );
                        $('#secRepCor_det_dep_num_operacion').val(
                            item.num_operacion
                        );
                        $('#secRepCor_det_dep_monto_deposito').val(
                            item.monto_deposito
                        );
                        $('#secRepCor_det_dep_comision').val(
                            item.comision_monto
                        );
                        $('#secRepCor_det_dep_monto_real').val(
                            item.total_recarga
                        );
                        $('#secRepCor_det_dep_bono').val(item.bono_monto);
                        $('#secRepCor_det_dep_bono_max').val(item.bono_monto);
                        $('#secRepCor_det_dep_obs_cajero').val(
                            item.observacion_cajero
                        );
                        $('#secRepCor_det_dep_obs_valid').val(
                            item.observacion_validador
                        );
                    });
                    return false;
                }
            },
            error: function () {},
        });

        $('#secRepCor_detail_deposito').show();
        $('#secRepCor_detail_pagos').hide();
    } else if ([11, 21, 29].includes(parseInt(tipo_id))) {
        title_detail += 'PAGADOR';

        var data = {
            accion: 'secRepCor_obtener_detalle_transaccion_pago',
            transaccion_id: tra_id,
            tipo_id: tipo_id,
        };

        auditoria_send({
            proceso: 'secRepCor_obtener_detalle_transaccion_pago',
            data: data,
        });
        $.ajax({
            url: '/sys/get_reportes_correcciones.php',
            type: 'POST',
            data: data,
            beforeSend: function () {
                loading('true');
            },
            complete: function () {
                loading();
            },
            success: function (resp) {
                var respuesta = JSON.parse(resp);
                if (parseInt(respuesta.http_code) == 400) {
                    swal('Aviso', respuesta.status, 'warning');
                    return false;
                }
                if (parseInt(respuesta.http_code) == 200) {
                    $('#secRepCor_detail_imagen_lista_pag').html('');
                    $('#secRepCor_detail_imagen_lista_pag_2').html('');
                    $.each(respuesta.result, function (index, item) {
                        if (parseInt(tipo_id) == 21) {
                            var nuevo_id_p =
                                item.fecha_hora_registro.toString() +
                                '_' +
                                item.id_archivo_2.toString();
                            $('#secRepCor_detail_imagen_lista_pag').append(
                                '<div class="col-md-12">' +
                                    '   <div align="center" style="height: 100%;width: 100%;">' +
                                    '       <img class="img-responsive" id="' +
                                    nuevo_id_p +
                                    '" src="files_bucket/propinas/' +
                                    item.archivo_2 +
                                    '" width="300px" />' +
                                    '   </div>' +
                                    '</div>'
                            );
                            $('#' + nuevo_id_p).imgViewer2();

                            $('#secRepCor_detail_imagen_lista_pag').show();
                        }

                        var nuevo_id =
                            item.fecha_hora_registro.toString() +
                            '_' +
                            item.id_archivo.toString();
                        $('#secRepCor_detail_imagen_lista_pag_2').append(
                            '<div class="col-md-12">' +
                                '   <div align="center" style="height: 100%;width: 100%;">' +
                                '       <img class="img-responsive" id="' +
                                nuevo_id +
                                '" src="files_bucket/retiros/' +
                                item.archivo +
                                '" width="300px" />' +
                                '   </div>' +
                                '</div>'
                        );
                        $('#' + nuevo_id).imgViewer2();

                        $('#secRepCor_det_pag_cuenta').val(item.cuenta);
                        $('#secRepCor_det_pag_tipo_cuenta').val(
                            'CUENTA DE AHORROS'
                        );
                        $('#secRepCor_det_pag_tipo_nro_cuenta').val(
                            item.cuenta_num
                        );
                        $('#secRepCor_det_pag_tipo_nro_cci_cuenta').val(
                            item.cci
                        );
                        $('#secRepCor_det_pag_fecha_pago').val(
                            item.fecha_abono
                        );
                        $('#secRepCor_det_pag_razon').val(item.razon);
                        $('#secRepCor_det_pag_tipo_operacion').val(
                            item.tipo_operacion
                        );
                        $('#secRepCor_det_pag_banco_pago').val(item.banco_pago);
                        $('#secRepCor_det_pag_num_operacion').val(
                            item.num_operacion
                        );
                        $('#secRepCor_det_pag_monto').val(item.monto);
                        $('#secRepCor_det_pag_comision').val(
                            item.comision_monto
                        );
                        $('#secRepCor_det_pag_obs_pag').val(
                            item.observacion_validador
                        );
                    });
                    return false;
                }
            },
            error: function () {},
        });

        $('#secRepCor_detail_pagos').show();
        $('#secRepCor_detail_deposito').hide();
    }
    secRepCor_show_changes(tra_id, tipo_id);
    secRepCor_show_history(tra_id);
    $('#secRepCor_detail_title').html(title_detail);
}

function sec_rep_cor_cerrar_detalle() {
    $('.secRepCor_detail_class').hide();
    $('#secRepCor_RES').show();
    $('#secRepCor_table_correcciones_history tbody').html('');
    $('#secRepCor_table_correcciones_history').hide();
}

function SecRepCor_exportar_excel() {
    var SecRepCor_fecha_inicio = $.trim($('#secRepCor_fecha_inicio').val());
    var SecRepCor_fecha_fin = $.trim($('#secRepCor_fecha_fin').val());

    if (SecRepCor_fecha_inicio.length !== 10) {
        $('#SecRepCor_fecha_inicio').focus();
        return false;
    }
    if (SecRepCor_fecha_fin.length !== 10) {
        $('#SecRepCor_fecha_inicio').focus();
        return false;
    }

    var data = {
        accion: 'secRepCor_exportar_listado_xls',
        fecha_inicio: SecRepCor_fecha_inicio,
        fecha_fin: SecRepCor_fecha_fin,
    };

    $.ajax({
        url: '/sys/get_reportes_correcciones.php',
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading('true');
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            let obj = JSON.parse(resp);
            window.open(obj.path);
            loading(false);
        },
        error: function () {},
    });
}

function secRepCor_show_changes(tra_id, tipo_id) {
    var data = {
        accion: 'secRepCor_show_changes_transaction',
        transaccion_id: tra_id,
    };

    auditoria_send({
        proceso: 'secRepCor_show_changes_transaction',
        data: data,
    });
    $.ajax({
        url: '/sys/get_reportes_correcciones.php',
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading('true');
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            $('.cl_input_edited').removeClass('cl_input_edited');
            $('.cl_image_edited').removeClass('cl_image_edited');

            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.result, function (index, item) {
                    switch (item.campo_name) {
                        case 'cuenta_id':
                            if (parseInt(tipo_id) == 1) {
                                $('#secRepCor_det_dep_cuenta').addClass(
                                    'cl_input_edited'
                                );
                            } else {
                                $('#secRepCor_det_pag_cuenta').addClass(
                                    'cl_input_edited'
                                );
                            }
                            break;
                        case 'cajero_cuenta_id':
                            if (parseInt(tipo_id) == 1) {
                                $('#secRepCor_det_dep_cuenta').addClass(
                                    'cl_input_edited'
                                );
                            } else {
                                $('#secRepCor_det_pag_cuenta').addClass(
                                    'cl_input_edited'
                                );
                            }
                            break;
                        case 'archivo':
                            if (parseInt(tipo_id) == 21) {
                                $(
                                    '#secRepCor_detail_imagen_lista_pag'
                                ).addClass('cl_image_edited');
                            } else if (parseInt(tipo_id) == 1) {
                                $('#secRepCor_detail_imagen_lista').addClass(
                                    'cl_image_edited'
                                );
                            } else {
                                $(
                                    '#secRepCor_detail_imagen_lista_pag_2'
                                ).addClass('cl_image_edited');
                            }
                            break;
                        case 'cuenta_pago_id':
                            $('#secRepCor_det_pag_banco_pago').addClass(
                                'cl_input_edited'
                            );
                            break;
                        case 'num_operacion':
                            if (parseInt(tipo_id) == 1) {
                                $('#secRepCor_det_dep_num_operacion').addClass(
                                    'cl_input_edited'
                                );
                            } else {
                                $('#secRepCor_det_pag_num_operacion').addClass(
                                    'cl_input_edited'
                                );
                            }
                            break;
                        case 'id_tipo_constancia':
                            $('#secRepCor_det_dep_tipo_constancia').addClass(
                                'cl_input_edited'
                            );
                            break;
                    }
                });
                return false;
            }
        },
        error: function () {},
    });
}

function secRepCor_show_history(tra_id) {
    var data = {
        accion: 'secRepCor_show_changes_transaction',
        transaccion_id: tra_id,
    };

    auditoria_send({
        proceso: 'secRepCor_show_changes_transaction',
        data: data,
    });
    $.ajax({
        url: '/sys/get_reportes_correcciones.php',
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading('true');
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#secRepCor_table_correcciones_history').show();
                var c = 1;
                $.each(respuesta.result, function (index, item) {
                    $('#secRepCor_table_correcciones_history tbody').append(
                        '<tr>' +
                            '	<td>' +
                            c +
                            '</td>' +
                            '	<td>' +
                            item.campo_name_obs +
                            '</td>' +
                            '	<td>' +
                            item.valor_original_desc +
                            '</td>' +
                            '	<td>' +
                            item.valor_nuevo_desc +
                            '</td>' +
                            '	<td>' +
                            item.usuario +
                            '</td>' +
                            '	<td>' +
                            item.fecha_creacion +
                            '</td>' +
                            '</tr>'
                    );
                    c += 1;
                });
                return false;
            }
        },
        error: function () {},
    });
}
