var tabla_indice_inflacion = '';
function sec_adm_feriados() {
    if (sec_id == 'adm_feriados') {
        console.log('sec:adm_feriados');
        sec_adm_feriados_events();
        cargar_tabla_feriados();
    }
}

function sec_adm_feriados_events() {
    $('#btn_actualizar')
        .off('click')
        .on('click', function () {
            cargar_tabla_feriados();
        });
    $('.fechas').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
    });
    $('.save_btn')
        .off()
        .click(function (event) {
            var btn = $(this);
            var nombre_feriado = $.trim($('#nombre_feriado').val());

            if (nombre_feriado.length == 0) {
                alertify.error('Ingrese nombre de feriado.', 10);
                $('#nombre_feriado').val('').focus();
                return false;
            }
            if (nombre_feriado.length > 45) {
                alertify.error(
                    'Ingreso ' +
                        nombre_feriado.length +
                        ' caracteres, el tamaño maximo permitido es 45.',
                    10
                );
                $('#nombre_feriado').val('').focus();
                return false;
            }
            swal(
                {
                    title:
                        "¿Guardar Datos de feriado con fecha '" +
                        $('#fecha_feriado').val() +
                        "' y nombre '" +
                        $('#nombre_feriado').val() +
                        "' ?",
                    type: 'warning',
                    timer: 10000,
                    showCancelButton: true,
                    closeOnConfirm: true,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar',
                },
                function (result) {
                    if (result) {
                        sec_adm_feriados_save(btn);
                    }
                }
            );
        });

    $(document).on('click', '.indice_inflacion_historial', function () {
        id = $(this).attr('data-feriado_id');
        cargar_tabla_feriados_historial(id);
    });
    $('.validar_numerico').validar_numerico_decimales({ decimales: 3 });
    $("#panel-datos_2 input:visible:not('.fechas'):first").focus();
}

function sec_adm_feriados_save(btn) {
    var set_data = {};
    var estado_feriado = $('#estado_feriado').val();
    $('.save_data').each(function (index, el) {
        set_data[$(el).attr('name')] = $(el).val();
    });
    loading(true);
    $.post(
        '/sys/set_adm_feriados.php',
        {
            sec_adm_feriados_save: set_data,
        },
        function (r) {
            try {
                loading();
                var obj = jQuery.parseJSON(r);
                if (obj.error) {
                    set_data.error = obj.error;
                    set_data.error_msg = obj.error_msg;
                    auditoria_send({
                        proceso: 'sec_adm_feriados_save_error',
                        data: set_data,
                    });
                    swal_msg({
                        type: 'warning',
                        text: obj.error_msg,
                        title: 'Advertencia!',
                        callback: function () {
                            swal.close();
                            custom_highlight(
                                $(".save_data[name='" + obj.error_focus + "']")
                            );
                            setTimeout(function () {
                                $(".save_data[name='" + obj.error_focus + "']")
                                    .val('')
                                    .focus();
                            }, 10);
                        },
                    });
                } else {
                    set_data.curr_login = obj.curr_login;
                    auditoria_send({
                        proceso: 'sec_adm_feriados_save_done',
                        data: set_data,
                    });
                    swal(
                        {
                            type: 'success',
                            text: obj.mensaje,
                            title: 'Guardado!',
                        },
                        function () {
                            m_reload();
                            if (btn.data('then') == 'reload') {
                                if (set_data.id == 'new') {
                                    set_data.id = obj.id;
                                    auditoria_send({
                                        proceso: 'add_item',
                                        data: set_data,
                                    });
                                    window.location =
                                        './?sec_id=' +
                                        sec_id +
                                        '&sub_sec_id=' +
                                        sub_sec_id +
                                        '&item_id=' +
                                        obj.id;
                                } else {
                                    auditoria_send({
                                        proceso: 'save_item',
                                        data: set_data,
                                    });
                                    swal.close();
                                    m_reload();
                                }
                            } else if (btn.data('then') == 'exit') {
                                auditoria_send({
                                    proceso: 'save_item',
                                    data: set_data,
                                });
                                window.location =
                                    './?sec_id=' +
                                    sec_id +
                                    '&sub_sec_id=' +
                                    sub_sec_id;
                            } else {
                            }
                        }
                    );
                }
            } catch (err) {
                loading();
                // console.log(r);
            }
        }
    );
}

function cargar_tabla_feriados() {
    set_data = { sec_feriados_list: 1 };
    $.ajax({
        url: '/sys/set_adm_feriados.php',
        data: set_data,
        type: 'POST',
        beforeSend: function () {
            loading(true);
        },
        complete: function () {
            loading();
        },
        success: function (response) {
            //  alert(datat)
            var resp = JSON.parse(response);
            var data = resp.lista;
            var data_meses = resp.lista_meses;
            console.log(resp);
            set_data.curr_login = resp.curr_login;
            auditoria_send({ proceso: 'sec_feriados_list', data: set_data });

            tabla_feriados = $('#tbl_datos_feriados').DataTable({
                bDestroy: true,
                rowCallback: function (row, data, index) {
                    $('td:eq(0)', row).html(index + 1);
                },
                language: {
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ registros por página',
                    zeroRecords: 'No se encontraron registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'No hay registros',
                    infoFiltered: '(filtrado de _MAX_ total records)',
                    paginate: {
                        first: 'Primero',
                        last: 'Último',
                        next: 'Siguiente',
                        previous: 'Anterior',
                    },
                    sProcessing: 'Procesando...',
                },
                data: data,
                sDom: "<'row'<'col-sm-3'l><'col-sm-2 div_select_year'><'col-sm-3 div_select_mes'><'col-sm-2 div_select_estado'><'col-sm-2'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                initComplete: function (settings, json) {
                    var sele = $(
                        '<select name="mes_select" id="mes_select" class="form-control input-sm" style="width:80%"></select>'
                    );
                    sele.append($('<option value="">-- Todos --</option>'));
                    for (var i = 0; i < data_meses.length; i++) {
                        sele.append(
                            $(
                                '<option value="' +
                                    data_meses[i].nombre +
                                    '">' +
                                    data_meses[i].nombre +
                                    '</option>'
                            )
                        );
                    }
                    $('.div_select_mes').append('Mes  ');
                    $('.div_select_mes').append(sele);

                    $('#mes_select')
                        .off('change')
                        .on('change', function () {
                            var val = $(this).val();
                            tabla_feriados.column(1).search(val).draw();
                            tabla_feriados.columns.adjust();
                        });

                    var sele = $(
                        '<select name="estado_feriado" id="estado_feriado" class="form-control input-sm" style="width:80%"></select>'
                    );
                    sele.append($('<option value="">-- Todos --</option>'));
                    sele.append($('<option value="1">Activo</option>'));
                    sele.append($('<option value="0">Inactivo</option>'));

                    $('.div_select_estado').append('Estado  ');
                    $('.div_select_estado').append(sele);
                    $('#estado_feriado')
                        .off('change')
                        .on('change', function () {
                            var val = $(this).val();
                            tabla_feriados.column(10).search(val).draw();
                            tabla_feriados.columns.adjust();
                        });

                    var sele = $(
                        '<select name="year_select" id="year_select" class="form-control input-sm" style="width:80%"></select>'
                    );
                    var rango_años = new Date().getFullYear();
                    sele.append($('<option value="">-- Todos --</option>'));
                    for (var i = 2015; i <= rango_años + 1; i++) {
                        sele.append(
                            $('<option value="' + i + '">' + i + '</option>')
                        );
                    }
                    $('.div_select_year').append('Año  ');
                    $('.div_select_year').append(sele);

                    $('#year_select')
                        .off('change')
                        .on('change', function () {
                            var val = $(this).val();
                            tabla_feriados.column(2).search(val).draw();
                            tabla_feriados.columns.adjust();
                        });
                },
                order: [],
                columns: [
                    {
                        title: "<div class='text-center'>Nro Orden</div>",
                        data: null,
                        class: 'text-right sorting_1',
                    },

                    {
                        title: 'mes',
                        render: function (data, type, row) {
                            var mes = parseInt(row['mes']);
                            mes = data_meses[mes - 1].nombre;
                            return mes;
                        },
                        visible: false,
                    },
                    {
                        title: "<div class='text-center'>Número</div>",
                        data: 'anio',
                        visible: false,
                    },
                    {
                        title: "<div class='text-center'>Fecha de feriado</div>",
                        data: 'fecha',
                        class: 'text-left',
                    },
                    {
                        title: "<div class='text-center'>Nombre de feriado</div>",
                        data: 'descripcion',
                        class: 'text-left',
                    },
                    {
                        title: "<div class='text-center'>Registrado por</div>",
                        data: 'usuario_created',
                        class: 'text-left',
                    },
                    {
                        title: "<div class='text-center'>Fecha de registro</div>",
                        data: 'created_at',
                        class: 'text-left',
                    },
                    {
                        title: "<div class='text-center'>Modificado por</div>",
                        data: 'usuario_updated',
                        class: 'text-left',
                    },
                    {
                        title: "<div class='text-center'>Fecha de modificación</div>",
                        data: 'updated_at',
                        class: 'text-left',
                    },
                    {
                        title: 'Estado',
                        render: function (data, type, row) {
                            var status_valor = row['status_cadena'];
                            var html_status = '';
                            if (status_valor == 'Activo')
                                html_status =
                                    '<span class="badge bg-success text-white">' +
                                    status_valor +
                                    '</span>';
                            else
                                html_status =
                                    '<span class="badge bg-danger text-white">' +
                                    status_valor +
                                    '</span>';

                            return html_status;
                        },
                        class: 'text-center',
                    },
                    {
                        title: 'status',
                        data: 'status',
                        visible: false,
                    },

                    {
                        title: 'Opciones',
                        width: '150px',
                        class: 'text-center',
                        render: function (data, type, row) {
                            var id = row['id'];
                            var estado_feriado =
                                row['status'] == 'Activo' ? 1 : 0;

                            var html = "<div style='text-align: center;'>";
                            var btn_class =
                                'btn btn-sm btn-success indice_inflacion_historial';

                            html +=
                                ' <a class="btn btn-rounded btn-default btn-sm btn-edit" title="Editar" href="./?sec_id=' +
                                sec_id +
                                '&amp;sub_sec_id=' +
                                sub_sec_id +
                                '&amp;item_id=' +
                                id +
                                '">';
                            html += '<i class="glyphicon glyphicon-edit"></i>';
                            html += '</a>';
                            html += '</div>';
                            return html;
                        },
                    },
                ],
            });
        },
        error: function () {
            set_data.error = obj.error;
            set_data.error_msg = obj.error_msg;
            auditoria_send({ proceso: 'sec_tipo_cambio_list', data: set_data });
        },
    });
}

function cargar_tabla_feriados_historial(id) {
    set_data = {
        sec_indice_inflacion_historial_list: 1,
        id: id,
    };
    $.ajax({
        url: '/sys/set_adm_feriados.php',
        data: set_data,
        type: 'POST',
        beforeSend: function () {
            loading(true);
        },
        complete: function () {
            loading();
        },
        success: function (response) {
            //  alert(datat)
            var resp = JSON.parse(response);
            var data = resp.lista;
            set_data.curr_login = resp.curr_login;
            auditoria_send({
                proceso: 'sec_indice_inflacion_historial_list',
                data: set_data,
            });

            tabla_indice_inflacion_historial = $(
                '#tbl_indice_inflacion_historial'
            ).DataTable({
                bDestroy: true,
                language: {
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ registros por página',
                    zeroRecords: 'No se encontraron registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'No hay registros',
                    infoFiltered: '(filtrado de _MAX_ total records)',
                    paginate: {
                        first: 'Primero',
                        last: 'Último',
                        next: 'Siguiente',
                        previous: 'Anterior',
                    },
                    sProcessing: 'Procesando...',
                },
                data: data,
                initComplete: function (settings, json) {
                    $('#modal_historial_indice_inflacion').modal('show');
                },
                order: [],
                columns: [
                    {
                        title: 'Índice de inflación',
                        data: 'valor_porc',
                        /*class:"text-right"*/
                    },
                    {
                        title: 'Fecha de creación',
                        data: 'created_at',
                    },
                    {
                        title: 'Usuario',
                        data: 'usuario',
                    },
                ],
            });
        },
        error: function () {
            set_data.error = obj.error;
            set_data.error_msg = obj.error_msg;
            auditoria_send({
                proceso: 'sec_indice_inflacion_historial_list',
                data: set_data,
            });
        },
    });
}
/*swal_msg({type : "error" , text : obj.mensaje , title : "Error"});*/
/*swal_msg({mensaje:"default"})*/
function swal_msg(opc) {
    defaults = {
        title: 'Registro',
        text: '',
        type: 'success',
        timer: 8000,
        callback: function () {
            swal.close();
        },
    };
    opciones = $.extend(defaults, opc);
    swal(
        {
            title: opciones.title,
            text: opciones.text,
            type: opciones.type,
            timer: opciones.timer,
            closeOnConfirm: true,
        },
        function () {
            defaults.callback();
        }
    );
}
