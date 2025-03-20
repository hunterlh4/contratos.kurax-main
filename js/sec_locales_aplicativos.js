$(document).ready(function () {
    if ($('#tab_locales_aplicativos').length) {
        window.addEventListener('popstate', function () {
            if (fnc_sec_locales_aplicativos__check_hash()) {
                fnc_sec_locales_aplicativos__init();
            }
        });

        if (fnc_sec_locales_aplicativos__check_hash()) {
            fnc_sec_locales_aplicativos__init();
        }
    }
});

function fnc_sec_locales_aplicativos__check_hash() {
    return window.location.hash && window.location.hash.indexOf('#tab=tab_locales_aplicativos') > -1;
}

function fnc_sec_locales_aplicativos__init() {
    fnc_sec_locales_aplicativos__render_table_locales_aplicativos();

    $('#sec_locales_aplicativos__frm_comentarios').on('submit', function (event) {
        event.preventDefault();
        let comentario = $('#sec_locales_aplicativos__txt_comentario').val();
        let str_form_data = window.localStorage.getItem("sec_locales_aplicativos__form_comentarios_data");
        if (str_form_data) {
            let form_data = JSON.parse(str_form_data);
            let id = form_data.id;
            let aplicativo_id = form_data.aplicativo_id;
            let local_id = form_data.local_id;
            let habilitado = form_data.habilitado;
            //let checked = form_data.checked;
            //let attr_id = form_data.attr_id;
            //let $toggle = $('#' + attr_id);
            $.when(fnc_sec_locales_aplicativos__change_habilitado(id, aplicativo_id, local_id, habilitado, comentario))
                .done(function (response) {
                    loading(false);
                    if (response) {
                        let json_response = JSON.parse(response);
                        if (!json_response.error) {
                            $("#sec_locales_aplicativos__mdl_comentarios").modal("hide");
                            //$toggle.prop('checked', checked).change();
                            window.alertify.success(json_response.message, 5);
                        } else {
                            //$toggle.prop('checked', !checked).change();
                            swal('Error', json_response.message, 'error');
                            fnc_sec_locales_aplicativos__render_table_locales_aplicativos();
                        }

                    }
                }).fail(function (jqXHR, textStatus, errorThrown) {
                loading(false);
                swal({
                    title: errorThrown,
                    html: true,
                    text: jqXHR.responseText,
                    type: textStatus,
                    closeOnConfirm: true
                });
                //$toggle.prop('checked', !checked).change();
                fnc_sec_locales_aplicativos__render_table_locales_aplicativos();
            });
        }
    });

    $("#sec_locales_aplicativos__mdl_comentarios").on('hidden.bs.modal', function () {
        $('#sec_locales_aplicativos__txt_comentario').val('');
        window.localStorage.removeItem('sec_locales_aplicativos__form_comentarios_data');
        fnc_sec_locales_aplicativos__render_table_locales_aplicativos();
    });

    $('#sec_locales_aplicativos__btn_ver_aplicativos').on('click', function () {
        $.when(fnc_sec_locales_aplicativos__render_table_aplicativos()).done(function () {
            $('#sec_locales_aplicativos__mdl_aplicativos').modal('show');
        });
    });

    $('#sec_locales_aplicativos__mdl_aplicativos').on('shown.bs.modal', function () {
        let table_id = '#sec_locales_aplicativos__tbl_aplicativos';
        if ($.fn.DataTable.isDataTable(table_id)) {
            let datatable = $(table_id).DataTable();
            datatable.columns.adjust().draw();
        }
    });

    $('#sec_locales_aplicativos__btn_nuevo_aplicativo').on('click', function () {
        $('#sec_locales_aplicativos__mdl_nuevo_aplicativo').modal('show');
    });

    $('#sec_locales_aplicativos__mdl_nuevo_aplicativo').on('shown.bs.modal', function () {
        $('#sec_locales_aplicativos__txt_producto').trigger('focus');
    });

    $("#sec_locales_aplicativos__mdl_nuevo_aplicativo").on('hidden.bs.modal', function () {
        $('#sec_locales_aplicativos__txt_producto').val('');
        $('#sec_locales_aplicativos__txt_servicio').val('');
        $('#sec_locales_aplicativos__txt_id').val('');
        $('#sec_locales_aplicativos__chk_estado').closest('.form-group').addClass('hidden');
        $('#sec_locales_aplicativos__mlb_nuevo_aplicativo').val('Nuevo Aplicativo');
    });

    $('#sec_locales_aplicativos__frm_nuevo_aplicativo').on('submit', function (event) {
        event.preventDefault();
        let id = $('#sec_locales_aplicativos__txt_id').val();
        let producto = $('#sec_locales_aplicativos__txt_producto').val();
        let servicio = $('#sec_locales_aplicativos__txt_servicio').val();
        let estado = $('#sec_locales_aplicativos__chk_estado').is(":checked") ? 1 : 0;
        if (!producto) {
            swal('Error', 'El campo producto está vacío.', 'error');
            return false;
        }

        fnc_sec_aplicativos__insert_or_update_aplicativo(id, producto, servicio, estado);
    });

    $(document).on('click', 'button[name="sec_locales_aplicativos__btn_editar_aplicativo"]', function () {
        let id = $(this).data('id');
        fnc_sec_locales_aplicativos__populate_form__frm_nuevo_aplicativo(id);
    });
}

function fnc_sec_locales_aplicativos__get_aplicativos_by_local_id() {

    let local_id = $('#sec_locales_aplicativos__input_local_id').val();

    if (!local_id) {
        swal('Error', 'El parámetro local_id no existe.', 'error');
        return;
    }

    let data = {
        local_id,
        accion: 'list_locales_aplicativos'
    };

    return fnc_sec_locales_aplicativos_ajax_request(data);
}


function fnc_sec_locales_aplicativos__render_table_locales_aplicativos() {
    $.when(fnc_sec_locales_aplicativos__get_aplicativos_by_local_id()).done(function (response) {
        if (response) {
            let json_response = JSON.parse(response);
            if (!json_response.error) {
                let data = json_response.data;
                if (data && data.length) {
                    let table_id = "#sec_locales_aplicativos__tbl_locales_aplicativos";
                    let datatable = null;
                    if (!$.fn.DataTable.isDataTable(table_id)) {
                        datatable = $(table_id).DataTable({
                            scrollX: true,
                            //scrollY: "70vh",
                            scrollCollapse: true,
                            autoWidth: false,
                            data: data,
                            info: false,
                            order: [[3, 'desc'], [1, 'asc']],
                            language: {
                                url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
                            },
                            columns: [
                                {
                                    data: "id",
                                    visible: false
                                },
                                {
                                    data: "producto",
                                    title: "PRODUCTO"
                                },
                                {
                                    data: "servicio",
                                    title: "SERVICIO"
                                },
                                {
                                    data: "habilitado",
                                    title: "HABILITADO",
                                    visible: false,
                                    render: function (data) {
                                        let value = 'Habilitado';
                                        if (!data || parseInt(data, 10) === 0) {
                                            value = 'Deshabilitado';
                                        }
                                        return value;
                                    }
                                },
                                {
                                    data: null,
                                    title: "",
                                    className: "text-center",
                                    width: "100px",
                                    orderable: false,
                                    render: function (data, type, row) {
                                        return '<input class="switch switch_is_open" ' +
                                            'data-toggle="toggle" ' +
                                            'name="sec_locales_aplicativos__input_habilitado" ' +
                                            'id="sec_locales_aplicativos__input_habilitado_"' + row.aplicativo_id + ' ' +
                                            'type="checkbox" ' +
                                            'data-table="sec_locales_aplicativos__tbl_locales_aplicativos" ' +
                                            'data-id="' + (row.id || '') + '" ' +
                                            'data-aplicativo-id="' + row.aplicativo_id + '" ' +
                                            'data-local-id="' + row.local_id + '" ' +
                                            'data-on-value="1" ' +
                                            'data-off-value="0" ' +
                                            (parseInt(row.habilitado) === 1 ? 'checked' : '') + ' ' +
                                            'value="' + (row.id || '') + '" ' +
                                            '>';
                                    }
                                },
                            ],
                            createdRow: function (row) {
                                $("td:eq(4)", row).css("min-width", "100px");
                            },
                            drawCallback: function () {
                                $('input[name="sec_locales_aplicativos__input_habilitado"]').bootstrapToggle().change(function (event) {
                                    event.preventDefault();
                                    let $toggle = $(this);
                                    let id = $toggle.val();
                                    let aplicativo_id = $toggle.data('aplicativo-id');
                                    let local_id = $toggle.data('local-id');
                                    let checked = $toggle.prop('checked');
                                    let habilitado = checked ? 1 : 0;
                                    let attr_id = $toggle.attr('id');

                                    let form_comentarios_data = {
                                        id,
                                        aplicativo_id,
                                        local_id,
                                        checked,
                                        habilitado,
                                        attr_id
                                    };

                                    window.localStorage.setItem("sec_locales_aplicativos__form_comentarios_data", JSON.stringify(form_comentarios_data));

                                    $('#sec_locales_aplicativos__mdl_comentarios').modal('show');
                                });
                            }
                        });

                        $(window).on("resize", function () {
                            datatable.columns.adjust().draw();
                        });
                    } else {
                        datatable = new $.fn.dataTable.Api(table_id);
                        datatable.clear();
                        datatable.rows.add(data).draw();
                    }
                    return datatable;
                }
            } else {
                swal('Error', json_response.message, 'error');
            }
        }
    });
}


function fnc_sec_locales_aplicativos__render_table_aplicativos() {
    return $.when(fnc_sec_locales_aplicativos__get_aplicativos()).done(function (response) {
        if (response) {
            let json_response = JSON.parse(response);
            if (!json_response.error) {
                let data = json_response.data;
                if (data && data.length) {
                    let table_id = "#sec_locales_aplicativos__tbl_aplicativos";
                    let datatable = null;
                    if (!$.fn.DataTable.isDataTable(table_id)) {
                        datatable = $(table_id).DataTable({
                            scrollX: true,
                            //scrollY: "70vh",
                            scrollCollapse: true,
                            autoWidth: false,
                            data: data,
                            info: false,
                            order: [[1, 'asc']],
                            language: {
                                url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
                            },
                            columns: [
                                {
                                    data: "id",
                                    visible: false
                                },
                                {
                                    data: "producto",
                                    title: "PRODUCTO"
                                },
                                {
                                    data: "servicio",
                                    title: "SERVICIO"
                                },
                                {
                                    data: 'estado',
                                    className: 'text-center',
                                    title: 'ESTADO',
                                    render: function (data) {
                                        return parseInt(data) === 1 ? '<span class="label label-success">Activo</span>' : '<span class="label label-danger">Inactivo</span>';

                                    }
                                },
                                {
                                    data: null,
                                    className: 'text-center',
                                    title: 'EDITAR',
                                    render: function (data, type, row) {
                                        return '<button class="btn btn-warning" id="sec_locales_aplicativos__btn_editar_aplicativo_' + row.id + '" data-id="' + row.id + '" name="sec_locales_aplicativos__btn_editar_aplicativo">Editar</button>'
                                    }
                                },
                            ]
                        });

                        $(window).on("resize", function () {
                            datatable.columns.adjust().draw();
                        });
                    } else {
                        datatable = new $.fn.dataTable.Api(table_id);
                        datatable.clear();
                        datatable.rows.add(data).draw();
                    }
                    return datatable;
                }
            } else {
                swal('Error', json_response.message, 'error');
            }
        }
    });
}

function fnc_sec_locales_aplicativos__get_aplicativos() {

    let data = {
        accion: 'get_aplicativos'
    };

    return fnc_sec_aplicativos_ajax_request(data);
}

function fnc_sec_locales_aplicativos__change_habilitado(id, aplicativo_id, local_id, habilitado, comentario) {

    let data = {
        id,
        aplicativo_id,
        local_id,
        habilitado,
        comentario,
        accion: 'change_habilitado'
    };

    return fnc_sec_locales_aplicativos_ajax_request(data);
}

function fnc_sec_aplicativos__insert_or_update_aplicativo(id, producto, servicio, estado) {

    let data = {
        id,
        producto,
        servicio,
        estado,
        accion: 'insert_or_update_aplicativo'
    };

    $.when(fnc_sec_aplicativos_ajax_request(data)).done(function (response) {
        if (response) {
            let json_response = JSON.parse(response);
            if (!json_response.error) {
                $('#sec_locales_aplicativos__mdl_nuevo_aplicativo').modal('hide');
                window.alertify.success(json_response.message, 5);
            } else {
                swal('Error', json_response.message, 'error');
            }
            fnc_sec_locales_aplicativos__render_table_aplicativos();
            fnc_sec_locales_aplicativos__render_table_locales_aplicativos();
        }
    });
}

function fnc_sec_locales_aplicativos__populate_form__frm_nuevo_aplicativo(id) {
    let data = {
        id,
        accion: 'get_aplicativo'
    };

    $.when(fnc_sec_aplicativos_ajax_request(data)).done(function (response) {
        if (response) {
            let json_response = JSON.parse(response);
            if (!json_response.error) {
                if (json_response.data.length) {
                    let aplicativo = json_response.data[0];
                    $('#sec_locales_aplicativos__txt_id').val(aplicativo.id);
                    $('#sec_locales_aplicativos__txt_producto').val(aplicativo.producto);
                    $('#sec_locales_aplicativos__txt_servicio').val(aplicativo.servicio);
                    let $chk_estado = $('#sec_locales_aplicativos__chk_estado');
                    //$chk_estado.prop('checked', parseInt(aplicativo.estado) === 1);
                    if(parseInt(aplicativo.estado) === 0) {
                        $chk_estado.removeAttribute('checked');
                    }
                    $chk_estado.closest('.form-group').removeClass('hidden');
                    $('#sec_locales_aplicativos__mlb_nuevo_aplicativo').val('Editar Aplicativo');
                    $('#sec_locales_aplicativos__mdl_nuevo_aplicativo').modal('show');
                } else {
                    swal('Error', 'Aplicativo not found.', 'error');
                }
            } else {
                swal('Error', json_response.message, 'error');
            }
        }
    });
}

function fnc_sec_locales_aplicativos_ajax_request(data, type) {
    type = type || 'POST';
    return $.ajax({
        type,
        data,
        async: true,
        url: 'sys/get_locales_aplicativos.php',
        success: function (response) {
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

function fnc_sec_aplicativos_ajax_request(data, type) {
    type = type || 'POST';
    return $.ajax({
        type,
        data,
        async: true,
        url: 'sys/get_aplicativos.php',
        success: function (response) {
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