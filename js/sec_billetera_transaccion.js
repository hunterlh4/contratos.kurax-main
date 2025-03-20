//INICIO FUNCIONES INICIALIZADOS
const sec_billetera_transaccion = () => {

    // INICIO FORMATO Y BUSQUEDA DE FECHA
    $('#sec_billetera_transaccion_fecha_show')
        .datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true
        })
        .on("change", function (ev) {
            $(this).datepicker('hide');
            var newDate = $(this).datepicker("getDate");
            $("#sec_billetera_transaccion_fecha").val($.format.date(newDate, "yyyy-MM-dd"));
        });
    // FIN FORMATO Y BUSQUEDA DE FECHA

    $('#form_modal_sec_billetera_transaccion_editar_fecha_show')
        .datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true
        })
        .on("change", function (ev) {
            $(this).datepicker('hide');
            var newDate = $(this).datepicker("getDate");
            $("#form_modal_sec_billetera_transaccion_editar_fecha").val($.format.date(newDate, "yyyy-MM-dd"));
        });

    // INICIO: MONTO DECIMAL
    $("#sec_billetera_transaccion_monto_desde, #sec_billetera_transaccion_monto_hasta").on({
        "focus": function (event) {
            $(event.target).select();
        },
        "change": function (event) {
            if (parseFloat($(event.target).val().replace(/\,/g, '')) > 0) {
                $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
                $(event.target).val(function (index, value) {
                    return value.replace(/\D/g, "")
                        .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                });
            } else {
                $(event.target).val("");
            }
        }
    });
    // FIN: MONTO DECIMAL

    $('#sec_billetera_transaccion_listar_transacciones_en_revision').click(function () {
        sec_billetera_transaccion_listar_transacciones_en_revision();
    })

    $('#sec_billetera_transaccion_fecha_show').change()

    sec_billeterea_transaccion_get_locales()
    sec_billeterea_transaccion_get_cajeros()
    sec_billeterea_transaccion_get_clientes()
    sec_billeterea_transaccion_get_estados()
    sec_billeterea_transaccion_get_cuentas()

    $("#sec_billetera_transaccion_local_id").change(function () {
        sec_billeterea_transaccion_get_cajeros()
    })

    $('#form_modal_sec_billetera_transaccion_rechazar_btn').click(async () => {

        var motivos = await get_motivos_rechazo();
        var motivos_alert = {};
        console.log(motivos)
        motivos_alert['otro'] = 'Otro motivo'
        motivos.forEach(element => {
            motivos_alert[element.id] = element.nombre
        });

        var motivo_to_save = '';
        var motivo_id = '';

        const { value: alert_select_motivo } = await Swal.fire({
            title: "Selecciona el motivo de rechazo",
            input: "select",
            inputOptions: motivos_alert,
            inputPlaceholder: "Selecciona aquí",
            confirmButtonColor: "#d33",
            confirmButtonText: "Continuar",
            showCancelButton: true,
            inputValidator: (value) => {
                return new Promise((resolve) => {
                    if (value) {
                        resolve();
                        if (value == 'otro') {

                        }
                    } else {
                        resolve("You need to select oranges :)");
                    }
                });
            },
            didOpen: async () => {
                Swal.getInput().addEventListener('change', async (event) => {
                    let val = event.target.value
                    console.log(val)
                    if (val == 'otro') {
                        Swal.close();
                        const { value: new_motivo } = await Swal.fire({
                            title: "Ingresa el motivo de rechazo",
                            input: "text",
                            inputLabel: "Especifica el motivo",
                            inputPlaceholder: "Escribe...",
                            confirmButtonColor: "#d33",
                            confirmButtonText: "Confirmar",
                            showCancelButton: true,
                            inputValidator: (value) => {
                                if (!value) {
                                    return "Debes especificar el motivo o cancelar!";
                                }
                            }
                        });

                        if (new_motivo) {
                            motivo_to_save = new_motivo;
                            confirmarRechazo('', motivo_to_save)
                        }
                    }
                })
            },
        });

        if (alert_select_motivo != 'otro') {
            motivo_id = alert_select_motivo
            motivo_to_save = motivos_alert[motivo_id]
        }

        if (motivo_to_save) {
            confirmarRechazo(motivo_id, motivo_to_save)
        }
        //m_reload()
    })

    sec_billetera_transaccion_listar_transacciones_en_revision()

    var count = cantidad_segundos
    setInterval(function() {
        if(count == 0){
            sec_billetera_transaccion_listar_transacciones_en_revision()
            count = cantidad_segundos
            // $('.timer').append("<br> Se actualizó a las: " + (new Date ))
        }
        $('.timer').text("Se refrescará en: " + (count--) + " segundos");

    }, 1000);
}

function confirmarRechazo(motivo_rechazo_id, otro_motivo) {

    updateTransaccion(4, motivo_rechazo_id, otro_motivo)

}
//FIN FUNCIONES INICIALIZADOS

function get_motivos_rechazo() {
    var data = {
        get_motivos_rechazo: 'get_motivos_rechazo'
    }
    return new Promise((resolve) => {
        $.ajax({
            url: "/sys/get_billetera_transaccion.php",
            type: "POST",
            data: data,
            cache: false,
            dataType: "json",
            beforeSend: function () {
                loading("true");
            },
            complete: function () {
                loading();
            },
            success: function (response, status) {
                loading();
                result = response;
                if (result.status == 200) {
                    resolve(result.motivos)
                }
                else {
                }
            },
            always: function (data) {
                loading();
            }
        });
    })
}

function sec_billeterea_transaccion_get_locales() {
    let redes_val = $('#').val()
    let redes = '_all_'
    if (redes_val != null && redes_val.length > 0) {
        redes = (redes_val)
    }

    var data = {
        billetera_transaccion_get_locales: {
            redes: redes
        }
    };

    $.ajax({
        data: data,
        type: "POST",
        dataType: "json",
        url: "/sys/get_billetera_transaccion.php",
    }).done(function (data, textStatus, jqXHR) {
        try {
            if (console && console.log) {
                $("#sec_billetera_transaccion_local_id").html('');
                var new_option = $("<option>");
                $(new_option).val('');
                $(new_option).html(' - Todos - ');
                $("#sec_billetera_transaccion_local_id").append(new_option);
                $.each(data.locales, function (index, val) {
                    var new_option = $("<option>");
                    $(new_option).val(val.id);
                    $(new_option).html(val.nombre);
                    $("#sec_billetera_transaccion_local_id").append(new_option);
                });
                $('#sec_billetera_transaccion_local_id').select2({ closeOnSelect: true });
            }
        } catch (err) {
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function () {
                swal.close();
                loading();
            });
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        if (console && console.log) {
            console.log("La solicitud locales a fallado: " + textStatus);
        }
    })
}

function sec_billeterea_transaccion_get_cajeros() {

    let local_val = $('#sec_billetera_transaccion_local_id').val()
    let local_id = []
    if (local_val != null && local_val > 0) {
        local_id = [local_val]
    }

    var data = {
        billetera_transaccion_get_cajeros: {
            locales: local_id,
            cargos: [5]
        }
    };

    $.ajax({
        data: data,
        type: "POST",
        dataType: "json",
        url: "/sys/get_billetera_transaccion.php",
    }).done(function (data, textStatus, jqXHR) {
        try {
            if (console && console.log) {
                $("#sec_billetera_transaccion_cajero").html('');
                var new_option = $("<option>");
                $(new_option).val('');
                $(new_option).html(' - Todos - ');
                $("#sec_billetera_transaccion_cajero").append(new_option);
                $.each(data.personales, function (index, val) {
                    var new_option = $("<option>");
                    $(new_option).val(val.id);
                    $(new_option).html("[" + val.id + "]" + val.personal_nombre);
                    $("#sec_billetera_transaccion_cajero").append(new_option);
                });
                $('#sec_billetera_transaccion_cajero').select2({ closeOnSelect: true });
            }
        } catch (err) {
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function () {
                swal.close();
                loading();
            });
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        if (console && console.log) {
            console.log("La solicitud de cajeros a fallado: " + textStatus);
        }
    })
}

function sec_billeterea_transaccion_get_clientes() {

    var data = {
        billetera_transaccion_get_clientes: {
            estados: [1, 2, 3, 4]
        }
    };

    $.ajax({
        data: data,
        type: "POST",
        dataType: "json",
        url: "/sys/get_billetera_transaccion.php",
    }).done(function (data, textStatus, jqXHR) {
        try {
            $("#sec_billetera_transaccion_cliente").html('');
            var new_option = $("<option>");
            $(new_option).val('');
            $(new_option).html(' - Todos - ');
            $("#sec_billetera_transaccion_cliente").append(new_option);
            $.each(data.clientes, function (index, val) {
                var new_option = $("<option>");
                $(new_option).val(val.cliente_nombre);
                $(new_option).html(val.cliente_nombre);
                $("#sec_billetera_transaccion_cliente").append(new_option);
            });
            $('#sec_billetera_transaccion_cliente').select2({ closeOnSelect: true });
        } catch (err) {
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function () {
                swal.close();
                loading();
            });
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log("La solicitud de clientes a fallado: " + textStatus);
    })
}

function sec_billeterea_transaccion_get_estados() {

    var data = {
        billetera_transaccion_get_estados: {
            estados: [1, 2, 3, 4]
        }
    };

    $.ajax({
        data: data,
        type: "POST",
        dataType: "json",
        url: "/sys/get_billetera_transaccion.php",
    }).done(function (data, textStatus, jqXHR) {
        try {
            $("#sec_billetera_transaccion_estado").html('');
            var new_option = $("<option>");
            $(new_option).val('');
            $(new_option).html(' - Todos - ');
            $("#sec_billetera_transaccion_estado").append(new_option);
            $.each(data.estados, function (index, val) {
                if(val.id != 1) {
                    var new_option = $("<option>");
                    $(new_option).val(val.id);
                    $(new_option).html(val.descripcion);
                    $("#sec_billetera_transaccion_estado").append(new_option);
                }
            });
            $('#sec_billetera_transaccion_estado').select2({ closeOnSelect: true });
        } catch (err) {
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function () {
                swal.close();
                loading();
            });
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log("La solicitud de clientes a fallado: " + textStatus);
    })
}

function sec_billeterea_transaccion_get_cuentas() {

    var data = {
        billetera_transaccion_get_cuentas: {
            estados: [1]
        }
    };

    $.ajax({
        data: data,
        type: "POST",
        dataType: "json",
        url: "/sys/get_billetera_transaccion.php",
    }).done(function (data, textStatus, jqXHR) {
        try {
            $("#sec_billetera_transaccion_cuenta").html('');
            var new_option = $("<option>");
            $(new_option).val('');
            $(new_option).html(' - Todos - ');
            $("#sec_billetera_transaccion_cuenta").append(new_option);

            $.each(data.cuentas, function (index, val) {
                var new_option = $("<option>");
                $(new_option).val(val.id);
                $(new_option).html(val.nombre_corto);
                $("#sec_billetera_transaccion_cuenta").append(new_option);
            });
            $('#sec_billetera_transaccion_cuenta').val('1');
            $('#sec_billetera_transaccion_cuenta').select2({ closeOnSelect: true });

        } catch (err) {
            swal({
                title: 'Error en la base de datos',
                type: "warning",
                timer: 2000,
            }, function () {
                swal.close();
                loading();
            });
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log("La solicitud de cuentas a fallado: " + textStatus + '----' + errorThrown);
    })
}

function sec_billetera_transaccion_listar_transacciones_en_revision() {
    if (sec_id == "billetera" && sub_sec_id == "transaccion") {
        var fecha = $("#sec_billetera_transaccion_fecha").val();
        var hora = $("#sec_billetera_transaccion_hora").val();
        var monto_desde = $("#sec_billetera_transaccion_monto_desde").val();
        if (monto_desde != '') {
            monto_desde = parseFloat(monto_desde.replace(/\,/g, '')).toFixed(2)
        }
        var monto_hasta = $("#sec_billetera_transaccion_monto_hasta").val();
        if (monto_hasta != '') {
            monto_hasta = parseFloat(monto_hasta.replace(/\,/g, '')).toFixed(2)
        }
        var cliente = $("#sec_billetera_transaccion_cliente").val();
        var cajero_id = $("#sec_billetera_transaccion_cajero").val();
        var local_id = $("#sec_billetera_transaccion_local_id").val();
        var cuenta_id = $("#sec_billetera_transaccion_cuenta").val();
        var estado = $("#sec_billetera_transaccion_estado").val();
        
        if (fecha == "") {
            alertify.error('Seleccione la Fecha', 5);
            $("#sec_billetera_transaccion_fecha").focus();
            return false;
        }

        var data = {
            sec_billetera_transaccion_listar_transacciones_en_revision: {
                fecha: fecha, // + ' ' + hora,
                monto_desde: monto_desde,
                monto_hasta: monto_hasta,
                cliente: cliente,
                cajero_id: cajero_id,
                local_id: local_id,
                cuenta_id: cuenta_id,
                estado: estado
            }
        };

        var columnDefs = [{
            className: 'text-center',
            targets: [0, 1, 2, 3, 4, 5, 6, 7, 10]
        }];

        var tabla = crearDataTable(
            "#sec_billetera_transaccion_table_transacciones_en_revision",
            "/sys/get_billetera_transaccion.php",
            data,
            columnDefs,
            [
                { extend: 'pageLength' }, {
                    extend: 'excel',
                    exportOptions: {
                        columns: function (column, data, node) {
                            if (column == 11) {
                                return false;
                            }
                            return true;
                        }
                    },
                }
            ]
        );

        tabla.on('init.dt', function () {
            //  $('.dataTables_filter').hide();
            prepareDatTableTransactions()
        });

        $('#sec_billetera_transaccion_table_transacciones_en_revision tbody').on('click', 'tr', function () {
            prepareDatTableTransactions()
            //var data = tabla.row(this).data();
            //console.log('You clicked on ' + data[0] + '\'s row');
        });
    }


}

function prepareDatTableTransactions() {
    $('.btn-editar-billetera-transaccion').click(function (e) {
        open_modal_editar_transacion(e.currentTarget.dataset.id)
    })

    $('.btn-registrar-billetera-transaccion').click(function (e) {
        let transaccion_id = e.currentTarget.dataset.id
        Swal.fire({
            title: "Desea Aprobar o Rechazar esta transacción?",
            text: '',
            icon: "warning",
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Aprobar',
            confirmButtonColor: "#1cb787",
            denyButtonColor: "#d33",
            denyButtonText: 'Rechazar',
        }).then((result) => {
            if (result.isConfirmed) {
                registrar_transaccion(transaccion_id, 1)
            } else if (result.isDenied) {
                registrar_transaccion(transaccion_id, 4)
            } else if (result.idDismissed) {
            }
        });
    })
}

function open_modal_editar_transacion(transaccion_id) {
    $('#sec_billetera_editar_transaccion').modal('show')

    $('#editar_transaccion_id').val(transaccion_id)

    let transaccion = getTransaccion(transaccion_id)
        .then(function (result) {
            console.log(result)

            let trx = result
            $('#form_modal_sec_billetera_transaccion_editar_fecha').val(trx['fecha'])
            $('#form_modal_sec_billetera_transaccion_editar_depositante').val(trx['nombre_depositante'])
            $('#form_modal_sec_billetera_transaccion_editar_num_operacion').val(trx['numero_operacion'])
            $('#form_modal_sec_billetera_transaccion_editar_telefono').val(trx['telefono_id'])
            $('#form_modal_sec_billetera_transaccion_editar_monto').val(trx['monto_deposito'])
            $('#form_modal_sec_billetera_transaccion_editar_fecha_show').val(trx['fecha_format'])
            $('#form_modal_sec_billetera_transaccion_editar_hora').val(trx['hora'])
            $('#form_modal_sec_billetera_transaccion_editar_observacion').val(trx['observacion'])

        })

    $('#form_modal_sec_billetera_transaccion_editar_btn').click(function () {
        updateTransaccion(2)
    })
}

function updateTransaccion(estado_transaccion_id, motivo_rechazo_id = null, otro_motivo_rechazo = null) {
    var data = {
        update_transaccion: {
            id: $('#editar_transaccion_id').val(),
            fecha_deposito: $('#form_modal_sec_billetera_transaccion_editar_fecha').val() + ' ' + $('#form_modal_sec_billetera_transaccion_editar_hora').val(),
            nombre_depositante: $('#form_modal_sec_billetera_transaccion_editar_depositante').val(),
            numero_operacion: $('#form_modal_sec_billetera_transaccion_editar_num_operacion').val(),
            telefono_id: $('#form_modal_sec_billetera_transaccion_editar_telefono').val(),
            monto_deposito: $('#form_modal_sec_billetera_transaccion_editar_monto').val(),
            observacion: $('#form_modal_sec_billetera_transaccion_editar_observacion').val(),
            estado_transaccion_id: estado_transaccion_id,
            motivo_rechazo_id: motivo_rechazo_id,
            otro_motivo_rechazo: otro_motivo_rechazo,
            revision: true
        }
    }

    $.ajax({
        url: "/sys/get_billetera_transaccion.php",
        type: "POST",
        data: data,
        cache: false,
        dataType: "json",
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (response, status) {
            result = (response);
            loading();
            if (result.status == 200) {
                swal(
                    {
                        type: "success",
                        title: "¡Existoso!",
                        text: 'Se registró correctamente la transacción'
                    });
                setTimeout(function () {
                    location.reload(true);
                }, 1500);

            }
            else {
                swal(
                    {
                        type: "warning",
                        title: "Alerta!",
                        text: result.message
                    });
            }
            //filter_archivos_table(0);
        },
        always: function (data) {
            loading();

        }
    });
}

function getTransaccion(transaccion_id) {
    var data = {
        get_transaccion: {
            transaccion_id: transaccion_id
        }
    }
    return new Promise(function (resolve, reject) {

        $.ajax({
            url: "/sys/get_billetera_transaccion.php",
            type: "POST",
            data: data,
            cache: false,
            dataType: "json",
            beforeSend: function () {
                loading("true");
            },
            complete: function () {
                loading();
            },
            success: function (response, status) {
                result = (response);

                loading();
                if (response.data.length > 0) {
                    resolve(response.data[0])
                }
                else {
                    swal(
                        {
                            type: "warning",
                            title: "Alerta!",
                            text: 'No existe la transacción'
                        });
                }
            },
            always: function (data) {
                loading();
            },
            error: function (error) {
                reject(error)
            },
            dataType: "json"
        });
    })
}

function registrar_transaccion(transaccion_id, estado_transaccion_id, motivo_rechazo_id = '', otro_motivo = '') {

    var data = {
        billetera_transaccion_registrar: {
            id: transaccion_id,
            estado_transaccion_id: estado_transaccion_id,
            motivo_rechazo_id: motivo_rechazo_id,
            otro_motivo: otro_motivo,
        }
    }

    $.ajax({
        url: "/sys/get_billetera_transaccion.php",
        type: "POST",
        data: data,
        cache: false,
        dataType: "json",
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (response, status) {
            result = (response);
            loading();
            if (result.status == 200) {
                let msg_text = ''
                switch (estado_transaccion_id) {
                    case 1:
                        msg_text = 'Se registró correctamente la transacción.';
                    case 4:
                        msg_text = 'Se rechazó correctamente la transacción. ' + `Tu motivo es: ${otro_motivo}.`;
                        break;
                    default:
                        break;
                }
                Swal.fire({
                    icon: "success",
                    title: "¡Existoso!",
                    text: msg_text,
                    showConfirmButton: true,
                    confirmButtonColor: "#1cb787",
                    confirmButtonText: "Ok",
                });

                setTimeout(function () {
                    location.reload(true);
                }, 1300);

            }
            else {
                swal(
                    {
                        type: "warning",
                        title: "Alerta!",
                        text: result.message
                    });
            }
            //filter_archivos_table(0);
        },
        always: function (data) {
            loading();

        }
    });
}