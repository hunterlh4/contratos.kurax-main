$(document).ready(function () {
    if ($('#tab_local_terminal').length) {
        window.addEventListener('popstate', function () {
            if (fnc_sec_locales_terminal_check_hash()) {
                fnc_sec_locales_terminal_init();
            }
        });

        if (fnc_sec_locales_terminal_check_hash()) {
            fnc_sec_locales_terminal_init();
        }
    }
});

function fnc_sec_locales_terminal_check_hash() {
    return window.location.hash && window.location.hash.indexOf('#tab=tab_local_terminal') > -1;
}

function fnc_sec_locales_terminal_init() {

    fnc_sec_locales_terminal_list_table();

    //fnc_sec_locales_terminal_get_proveedores_api_habilitada();

    $('#idBtnGuardarLocalTerminal').on('click', (e) => {
        e.preventDefault();
        $('#sec_locales_terminal_form').submit();
        return false;
    });

    $('#sec_locales_terminal_form').validate({
        rules: {
            sec_locales_terminal_form_input_nombre_terminal: {
                required: true,
            }
        }, messages: {
            sec_locales_terminal_form_input_nombre_terminal: {
                required: 'Por favor, es necesario un nombre de terminal.',
            }
        }, submitHandler: function () {
            fnc_sec_locales_terminal_auto_servicio_guardar();
            return false;
        }
    });

    $('#btn_generate_token_auto_servicio').click(function () {
        fnc_sec_locales_terminal_save_token();
    });

    $('#sec_locales_terminal_autoservicio_modal').on('hide.bs.modal', function () {
        $('#sec_locales_terminal_form_input_nombre_terminal').val('');
        $('#sec_locales_terminal_form_input_id').val('');
        $('#sec_locales_terminal_form_input_entity_cashier').val('');
        $('#sec_locales_terminal_form_input_entity_parent').val('');
        $('#sec_locales_terminal_form_input_servicio_id').val('');
        $('#sec_locales_terminal_form_input_servicio_clave').val('');
        $('#sec_locales_nuevo_autoservicio_title').text('Nuevo Terminal');
    });

    $('#sec_local_terminal_password_modal').on('hide.bs.modal', function () {
        $('#id_input_text_terminal_local_id').val('0');
        $('#id_input_text_terminal_local_nombre_usuario').val('');
        $('#id_input_text_terminal_local_nombre_terminal').val('');
        $('#sec_local_terminal_nombre_terminal').val('');
        $('#id_input_text_terminal_local_password').val('');
        $('#alert_terminal_auto_servicio').addClass('hide');
    });

    $('#sec_local_terminal_frm_proveeedores').on('submit', function (event) {
        event.preventDefault();
        fnc_sec_locales_terminal_update_proveedores();
    });

    $(document).on('change', ':input[name="sec_locales_terminal_chk_require_mac"]', function () {
        let id = parseInt(this.value);
        fnc_sec_locales_terminal_change_require_mac(id, this.checked);
    });

    let dropdownMenu;

    $(window).on('show.bs.dropdown', function (e) {
        // grab the menu
        dropdownMenu = $(e.target).find('.dropdown-menu');

        // detach it and apientes it to the body
        $('body').append(dropdownMenu.detach());

        // grab the new offset position
        let eOffset = $(e.target).offset();

        // make sure to place it where it would normally go (this could be improved)
        dropdownMenu.css({
            display: 'block', top: eOffset.top + $(e.target).outerHeight(), left: eOffset.left,
        });
    });

    $(window).on('hide.bs.dropdown', function (e) {
        $(e.target).append(dropdownMenu.detach());
        dropdownMenu.hide();
    });

    $(document).on('click', '.btn-cambiar-estado-terminal', function () {
        let id = $(this).data('id');
        let estado = parseInt($(this).data('estado'));
        let nuevo_estado = estado === 1 ? 0 : 1;
        fnc_sec_locales_terminal_auto_servicio_update_estado(id, nuevo_estado);
    });

    $(document).on('click', '.btn-actualizar-terminal', function () {
        let id = $(this).data('id');
        let nombre_terminal = $(this).data('nombre-terminal');
        $('#sec_locales_terminal_form_input_id').val(id);
        $('#sec_locales_terminal_form_input_nombre_terminal').val(nombre_terminal);
        $('#sec_locales_terminal_autoservicio_modal').modal('show');
        $('#sec_locales_nuevo_autoservicio_title').text('Actualizar Terminal');
    });

    $(document).on('click', '.btn-password-terminal', function () {
        let id = $(this).data('id');
        let terminal_usuario = $(this).data('terminal-usuario');
        let nombre_terminal = $(this).data('nombre-terminal');
        let row = $(this).data('row');
        $('#id_input_text_terminal_local_id').val(id);
        $('#id_input_text_terminal_local_nombre_usuario').val(terminal_usuario);
        $('#id_input_text_terminal_local_nombre_terminal').val(nombre_terminal);
        $('#sec_local_terminal_nombre_terminal').text(nombre_terminal);
        $('#txt_row_index').val(row);
        $('#sec_local_terminal_password_modal').modal('show');
    });

    $(document).on('click', '.btn-proveedores', function () {
        let id_terminal_auto_servicio = $(this).data('id');
        $.ajax({
            type: 'POST',
            data: {
                accion: 'listar_proveedores_by_terminal',
                id_terminal_auto_servicio
            },
            url: 'sys/get_locales_terminal.php',
            success: function (response) {
                loading(false);
                let jsonData = JSON.parse(response);
                if (jsonData.error === false) {
                    let data = jsonData.data;
                    let $ul = $('#sec_local_terminal_proveedores_checkbox_list');
                    $ul.empty();
                    data.forEach(function (provider) {
                        let $li = $('<li>').addClass('list-inline');
                        let id = 'cbx_terminal_' + provider.terminal_id + '_proveedor_' + provider.provider_id;
                        let $label = $('<label />', {'for': id, text: provider.name}).addClass('control-label');
                        if (provider.api_enabled === 0) {
                            $label.addClass('text-default');
                        } else {
                            $label.addClass('text-primary');
                        }

                        $('<input />', {
                            type: 'checkbox',
                            id: id,
                            value: provider.provider_id,
                            css: {margin: 'auto 10px'},
                            disabled: provider.api_enabled === 0

                        })
                            .attr('data-id_terminal_auto_servicio', provider.terminal_id)
                            .attr('data-id_terminal_proveedor', provider.provider_id)
                            .attr('data-clave', provider.key_name)
                            .attr('data-nombre', provider.nombre)
                            .attr('data-api_enabled', provider.api_enabled)
                            .addClass('form-control').addClass('checkbox-inline')
                            .prop('checked', provider.provider_enabled === 1)
                            .appendTo($li);
                        $label.appendTo($li);
                        $li.appendTo($ul);
                    });
                    $('#sec_local_terminal_proveeedores_modal').modal('show');
                } else {
                    swal('Error', jsonData.message, 'error');
                    loading(false);
                }
            }, beforeSend: function () {
                loading(true);
            }, error: function (jqXHR, textStatus, errorThrown) {
                loading(false);
                swal({
                    title: errorThrown, html: true, text: jqXHR.responseText, type: textStatus, closeOnConfirm: true
                });
            }
        });
    });
}

function copyToClipboardPassword() {
    let input = document.getElementById('id_input_text_terminal_local_password');
    input.select();
    document.execCommand('copy');
    //fnc_sec_locales_terminal_list_table();
}

function copyToClipboardNombreUsuario() {
    let input = document.getElementById('id_input_text_terminal_local_nombre_usuario');
    input.select();
    document.execCommand('copy');
    //fnc_sec_locales_terminal_list_table();
}

function fnc_sec_locales_terminal_save_token() {
    let nombre_terminal = $('#id_input_text_terminal_local_nombre_terminal').val();
    swal({
        title: '¿Estás seguro?',
        text: 'Se va a actualizar el password del terminal ' + nombre_terminal,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: 'Si, proceder',
        cancelButtonText: 'No, cancelar',
        closeOnConfirm: true,
        closeOnCancel: true,

    }, function (isConfirm) {
        if (isConfirm) {
            let id = $('#id_input_text_terminal_local_id').val();

            let data = {
                id,
                accion: 'generate_token_auto_servicio'
            };

            auditoria_send({'proceso': 'fnc_sec_locales_terminal_save_token', 'data': data});

            $.ajax({
                type: 'POST',
                data: data,
                url: 'sys/get_locales_terminal.php',
                cache: false,
                success: function (response) {
                    loading(false);
                    let jsonData = JSON.parse(response);
                    if (jsonData.error === false) {
                        $('#id_input_text_terminal_local_password').val(jsonData.data.password);
                        $('#alert_terminal_auto_servicio').removeClass('hide');
                        fnc_sec_locales_terminal_list_table();
                    } else {
                        swal('Error', jsonData.message, 'error');
                    }
                },
                beforeSend: function () {
                    loading(true);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    loading(false);
                    swal({
                        title: errorThrown, html: true, text: jqXHR.responseText, type: textStatus, closeOnConfirm: true
                    });
                }
            });
        }
    });
}

function fnc_sec_locales_terminal_list_table() {

    let data = {
        local_id: $('#sec_locales_terminal_form_input_local_id').val(),
        accion: 'list_terminal'
    }

    //auditoria_send({'proceso': 'fnc_sec_locales_terminal_list_table', 'data': data});

    $.ajax({
        type: 'POST', data: data, async: true, url: 'sys/get_locales_terminal.php', success: function (response) {
            loading(false);
            let jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                fnc_sec_locales_terminal_render_table(jsonData.data);
            } else {
                swal('Error', jsonData.message, 'error');
            }
        }, beforeSend: function () {
            loading(true);
        }, error: function (jqXHR, textStatus, errorThrown) {
            loading(false);
            swal({
                title: errorThrown, html: true, text: jqXHR.responseText, type: textStatus, closeOnConfirm: true
            });
        }
    });

}

function fnc_sec_locales_terminal_auto_servicio_update_estado(id, nuevo_estado) {
    let formData = new FormData();
    formData.append('accion', 'update_estado_local_terminal');
    formData.append('id', id);
    formData.append('status', nuevo_estado);
    let object_auditoria = {}
    formData.forEach((value, key) => object_auditoria[key] = value);

    swal({
        title: '¿Estás seguro?',
        text: 'Esta seguro que desea cambiar de estado el terminal.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: 'Si, proceder',
        cancelButtonText: 'No, cancelar',
        closeOnConfirm: true,
        closeOnCancel: true,

    }, function (isConfirm) {
        if (isConfirm) {
            auditoria_send({
                'proceso': 'fnc_sec_locales_terminal_auto_servicio_update_estado',
                'data': fnc_locales_terminal_auto_servicio_formdata_to_json(formData)
            });
            $.ajax({
                type: 'POST',
                data: formData,
                url: 'sys/get_locales_terminal.php',
                contentType: false,
                processData: false,
                cache: false,
                success: function (response) {
                    loading(false);
                    let jsonData = JSON.parse(response);
                    if (jsonData.error === false) {
                        swal('OK', jsonData.message, 'success');
                        fnc_sec_locales_terminal_list_table();
                        $('#sec_locales_terminal_form_input_nombre_terminal').val('');
                    } else {
                        swal('Error', jsonData.message, 'error');
                    }
                },
                beforeSend: function () {
                    loading(true);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    loading(false);
                    swal({
                        title: errorThrown, html: true, text: jqXHR.responseText, type: textStatus, closeOnConfirm: true
                    });
                }
            });
        }
    });
}

function fnc_sec_locales_terminal_auto_servicio_guardar() {
    var formData = new FormData();
    let accion = 'guardar_local_terminal';
    let motivo = 'registrar';
    let id = $('#sec_locales_terminal_form_input_id').val();
    let local_id = $('#sec_locales_terminal_form_input_local_id').val();
    let nombre_terminal = $('#sec_locales_terminal_form_input_nombre_terminal').val();
    let user_id_created = $('#sec_locales_terminal_form_input_user_id_created').val();
    let user_name_created = $('#sec_locales_terminal_form_input_user_name_created').val();

    if (id) {
        accion = 'actualizar_local_terminal';
        motivo = 'actualizar';
    }

    formData.append('accion', accion);
    formData.append('id', id);
    formData.append('local_id', local_id);
    formData.append('name', nombre_terminal);
    formData.append('user_id_created', user_id_created);
    formData.append('user_name_created', user_name_created);

    swal({
        title: '¿Estás seguro?',
        text: 'Se enviarán datos para ' + motivo + ' el terminal',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: 'Si, proceder',
        cancelButtonText: 'No, cancelar',
        closeOnConfirm: false,
        closeOnCancel: false,
    }, function (isConfirm) {

        if (isConfirm) {

            auditoria_send({
                proceso: 'fnc_sec_locales_terminal_auto_servicio_guardar',
                data: fnc_locales_terminal_auto_servicio_formdata_to_json(formData)
            });

            $.ajax({
                type: 'POST',
                data: formData,
                url: 'sys/get_locales_terminal.php',
                contentType: false,
                processData: false,
                cache: false,
                success: function (response) {
                    loading(false);
                    let jsonData = JSON.parse(response);
                    if (jsonData.error === false) {
                        swal('OK', jsonData.message, 'success');
                        $('#sec_locales_terminal_autoservicio_modal').modal('hide');
                        fnc_sec_locales_terminal_list_table();
                    } else {
                        swal('Error', jsonData.message, 'error');
                    }
                },
                beforeSend: function () {
                    loading(true);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    loading(false);
                    swal({
                        title: errorThrown, html: true, text: jqXHR.responseText, type: textStatus, closeOnConfirm: true
                    });
                }
            });
        }
    });
}

function fnc_sec_locales_terminal_render_table(data = {}) {
    let table_id = '#tbl_sec_locales_terminal';
    let $table = $(table_id);
    let datatable;
    if (!$.fn.DataTable.isDataTable(table_id)) {
        datatable = $table.DataTable({
            responsive: false,
            destroy: true,
            autoWidth: true,
            scrollX: true,
            lengthChange: false,
            searching: false,
            data: data,
            ordering: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
            },
            columns: [
                {
                    data: 'name'
                },
                {
                    data: 'require_mac',
                    className: 'text-center',
                    orderable: false,
                    render: function (data, type, row) {
                        let checked = parseInt(data) === 1 ? 'checked' : '';
                        return '<input type="checkbox" ' + checked + ' class="form-control" data-id="' + row.id + '" id="sec_locales_terminal_chk_require_mac_' + row.id + '" name="sec_locales_terminal_chk_require_mac" value="' + row.id + '">'
                    }
                },
                {
                    data: 'has_token', className: 'text-center', render: function (data) {
                        let text = 'SI';
                        let color_style = 'success';
                        let has_token = parseInt(data);
                        if (has_token === 0) {
                            text = 'NO';
                            color_style = 'danger';
                        }
                        return '<span class="label label-' + color_style + '">' + text + '</span>';
                    }
                },
                {
                    data: 'providers', className: 'text-center', render: function (data) {

                        let color_style = 'success';
                        let count = parseInt(data);
                        if (count === 0) {
                            color_style = 'danger';
                        }
                        return '<span class="label label-' + color_style + '"> ' + count + ' </span>';
                    }
                },
                {
                    data: 'status', className: 'text-center', render: function (data) {
                        let text = 'Activo';
                        let color_style = 'success';
                        if (parseInt(data) === 0) {
                            text = 'Inactivo';
                            color_style = 'danger';
                        }
                        return '<span class="label label-' + color_style + '">' + text + '</span>';
                    }
                },
                {
                    data: 'id', orderable: false, className: 'text-center', render: function (id, type, row, meta) {
                        let nombre_estado = 'Activar';
                        if (row.status === 1) {
                            nombre_estado = 'Desactivar';
                        }
                        return '<div class="dropdown">' + '<button id="dLabel' + meta.row + '" type="button" class="btn btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' + ' Acciones <span class="caret"></span>' + '</button>' + '<ul class="dropdown-menu" aria-labelledby="dLabel' + meta.row + '">' + '<li>' + '<a title="Cambiar Estado" data-id="' + id + '" data-estado="' + row.status + '"  class="btn btn-default btn-xs btn-cambiar-estado-terminal">' + nombre_estado + '</a>' + '</li>' + '<li>' + '<a title="Actualizar Terminal" data-id="' + id + '" data-nombre-terminal="' + row.name + '" class="btn btn-default btn-xs btn-actualizar-terminal">' + 'Actualizar Nombre' + '</a>' + '</li>' + '<li>' + '<a title="Cambiar Contraseña" data-id="' + id + '" data-terminal-usuario="' + row.user_terminal + '" data-nombre-terminal="' + row.name + '" data-row="' + meta.row + '" class="btn btn-default btn-xs btn-password-terminal">' + 'Cambiar Contraseña' + '</a>' + '</li>' + '<li>' + '<a title="Proveedores" data-id="' + id + '"  class="btn btn-default btn-xs btn-proveedores">' + 'Proveedores' + '</a>' + '</li>' + '</ul>' + '</div>';
                    }
                }]
        });
    } else {
        datatable = new $.fn.dataTable.Api(table_id);
        datatable.clear();
        datatable.rows.add(data);
        datatable.draw();
    }
    return datatable;
}

function fnc_locales_terminal_auto_servicio_formdata_to_json(formData) {
    let object = {};
    formData.forEach(function (value, key) {
        object[key] = value;
    });
    return JSON.stringify(object);
}

function fnc_sec_locales_terminal_update_proveedores() {
    let $checkboxes = $('#sec_local_terminal_proveedores_checkbox_list').find(':input');
    let proveedores = [];
    $checkboxes.each(function (index, checkbox) {
        let api_enabled = parseInt(checkbox.dataset.api_enabled);
        let key_name = checkbox.dataset.clave;
        let terminal_id = checkbox.dataset.id_terminal_auto_servicio;
        let provider_id = checkbox.dataset.id_terminal_proveedor;
        let name = checkbox.dataset.nombre;
        let provider_enabled = checkbox.checked ? 1 : 0;
        let status = 1;
        if (api_enabled === 1) {
            let value = {
                terminal_id,
                provider_id,
                key_name,
                name,
                provider_enabled,
                api_enabled,
                status
            }
            proveedores.push(value);
        }
    });

    $.ajax({
        type: 'POST', data: {
            'accion': 'update_proveedores_by_terminal',
            'proveedores': proveedores,
        }, url: 'sys/get_locales_terminal.php', success: function (response) {
            loading(false);
            let jsonData = JSON.parse(response);
            if (jsonData.error === false) {
                let data = jsonData.data;
                let $ul = $('<ul>').css({
                    'list-style': 'none'
                });
                data.forEach(function (ele) {
                    let $li = $('<li>').text(ele.message);
                    $li.appendTo($ul);
                });
                swal({
                    type: 'success', title: 'OK', text: $ul.get(0).outerHTML, html: true
                });
                $('#sec_local_terminal_proveeedores_modal').modal('hide');
                fnc_sec_locales_terminal_list_table();
            } else {
                swal('Error', jsonData.message, 'error');
                loading(false);
            }
        }, beforeSend: function () {
            loading(true);
        }, error: function (jqXHR, textStatus, errorThrown) {
            loading(false);
            swal({
                title: errorThrown, html: true, text: jqXHR.responseText, type: textStatus, closeOnConfirm: true
            });
        }
    });
}

function fnc_sec_locales_terminal_change_require_mac(id, is_checked) {
    let message = 'Si deshabilita está opción no se va a requerir una dirección MAC para el inicio de sesión del terminal, esto puede suponer problemas de seguridad.';

    if (is_checked) {
        message = 'Si habilita está opción se va a requerir una dirección MAC para el inicio de sesión del terminal, esto es recomendable ya que mejora el nivel de seguridad.';
    }

    swal({
        title: '¿Estás seguro?',
        text: message,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: 'Si, proceder',
        cancelButtonText: 'No, cancelar',
        closeOnConfirm: true,
        closeOnCancel: true,
    }, function (isConfirm) {
        if (isConfirm) {

            let data = {
                require_mac: is_checked ? 1 : 0,
                id,
                accion: 'mac_requerida',
                usuario_id: $('#sec_locales_terminal_usuario_id').val()
            };

            auditoria_send({
                proceso: 'fnc_sec_locales_terminal_change_require_mac',
                data: JSON.stringify(data)
            });

            fnc_sec_locales_terminal_generic_ajax(data).done(function (response) {
                let jsonData = JSON.parse(response);
                if (!jsonData.error) {
                    swal('OK', jsonData.message, 'success');
                } else {
                    swal('Error', jsonData.message, 'error');
                    $('#sec_locales_terminal_chk_require_mac_' + id).prop('checked', !is_checked);
                }
            });
        } else {
            $('#sec_locales_terminal_chk_require_mac_' + id).prop('checked', !is_checked);
        }
    });
}

function fnc_sec_locales_terminal_get_proveedores_api_habilitada() {

    let data = {
        accion: 'get_proveedores_api_habilitada'
    };

    return fnc_sec_locales_terminal_generic_ajax(data).done(function (response) {
        return response;
    });
}

function fnc_sec_locales_terminal_generic_ajax(data, method = 'POST', async = true) {
    return $.ajax({
        type: method, data: data, async: async, url: 'sys/get_locales_terminal.php', success: function (response) {
            loading(false);
            return response;
        }, beforeSend: function () {
            loading(true);
        }, error: function (jqXHR, textStatus, errorThrown) {
            loading(false);
            swal({
                title: errorThrown, html: true, text: jqXHR.responseText, type: textStatus, closeOnConfirm: true
            });
        }
    });
}