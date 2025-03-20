var sec_incidencias_ca_table_data = null;

function sec_incidencias() {
    if (sec_id === "incidencias_ca" || sec_id === "incidencias") {
        sec_incidencias_events();
    }
}

function set_incidencias_get(id) {
    let set_data = {id: id};
    loading(true);
    $.post('/sys/set_incidencias.php', {
        "set_incidencias_get": id
    }, function (r) {
        loading();
        let obj = jQuery.parseJSON(r);
        let error = obj.error;
        let status = obj.status;
        let objeto = obj.incidencia;

        if (error) {
            swal({
                title: "Incidencia",
                text: mensaje,
                type: status,
                timer: 3000,
                closeOnConfirm: true
            },
                function () {
                    swal.close();
                });
        } else {
            let $modal = $('#modal_incidencia');
            $modal.on("shown.bs.modal", function () {
                let $form = $("#formGuardarSolucion");
                $.each(objeto, function (i, e) {
                    $("label[for='" + i + "']").parent().find("p").text(e);
                })
                $('#id', $form).val(objeto.id);
                $('#tienda', $form).val(objeto.tienda);
                $('#incidencia_txt', $form).val(objeto.incidencia_txt);
                $('#reimpresion', $form).val(objeto.reimpresion);
                $('#producto', $form).val(objeto.producto).change();
                $('#tipo', $form).val(objeto.tipo);
                $('#razon_social_id', $form).val(objeto.razon_social_id);
                $('#razon_social', $form).val(objeto.razon_social);
                $('#local_red_id', $form).val(objeto.local_red_id);
                $('#local_red_nombre', $form).val(objeto.local_red_nombre);

                $("#solucion_txt", $('#modal_incidencia')).focus();
            })
            $modal.off("hidden.bs.modal").on("hidden.bs.modal", function () {
                $("#solucion_txt", $('#modal_incidencia')).val("");

                $("[name='selectRecomendacion']").closest("label").removeClass("clicked_t");
                $("[name='selectRecomendacion']:checked").prop("checked", false);
                //$("[name='selectPeri']").closest("label").removeClass("clicked_t");
                //$("[name='selectPeri']:checked").prop("checked",false);
                limpiar();
            })
            sec_incidencias_solucionar_modal()
            //$('#modal_incidencia').modal("show");
        }

    });
}

function set_incidencias_reasignar(incidencia_id) {

    loading(true);
    $.post('/sys/set_incidencias.php', {
        "set_incidencias_reasignar": incidencia_id,
    }, function (r) {
        loading();
        let obj = jQuery.parseJSON(r);
        if (obj.error) {
            auditoria_send({
                "proceso": "sec_incidencias_reasignar_error", "data": {
                    incidencia_id,
                    error: obj.msg
                }
            });
            loading(false);
            swal({
                title: "Incidencia",
                text: obj.msg,
                type: obj.swal_type,
                timer: obj.swal_timeout,
                closeOnConfirm: true
            },
                function () {
                    swal.close();
                });
        } else {
            swal({
                title: "Incidencia",
                text: obj.msg,
                type: obj.swal_type,
                timer: 3000,
                closeOnConfirm: true
            },
                function () {
                    swal.close();
                    //tablaserver.ajax.reload(null, false);
                    let tbl_incidencias = $('#tbl_incidencias')
                    tbl_incidencias.DataTable().ajax.reload();
                    tbl_incidencias.DataTable().columns.adjust();
                });
        }

    });
}

function sec_incidencias_asignar(id) {
    let set_data = {
        id: id
    };
    loading(true);
    $.post('/sys/set_incidencias.php', {
        "set_incidencias_asignar": id,
    }, function (r) {
        loading();
        let obj = jQuery.parseJSON(r);
        let id = obj.login_id;
        let mensaje = obj.mensaje;

        if (obj.error) {
            set_data.error = obj.error;
            auditoria_send({"proceso": "sec_incidencias_asignar_error", "data": set_data});
            loading(false);
            swal({
                title: "¡Error!",
                text: obj.error,
                type: obj.swal_type,
                timer: obj.swal_timeout,
                closeOnConfirm: true
            },
                function () {
                    swal.close();
                });
            return false;
        }

        let incidencia_ya_atendida = obj.incidencia_ya_atendida;
        if (incidencia_ya_atendida) {
            swal({
                title: "Incidencia",
                text: mensaje,
                type: "warning",
                timer: 3000,
                closeOnConfirm: true
            },
                function () {
                    swal.close();
                });
        } else {
            swal({
                title: "Incidencia",
                text: mensaje,
                type: "success",
                timer: 3000,
                closeOnConfirm: true
            },
                function () {
                    swal.close();
                    //tablaserver.ajax.reload(null, false);
                    let tbl_incidencias = $('#tbl_incidencias')
                    tbl_incidencias.DataTable().ajax.reload();
                    tbl_incidencias.DataTable().columns.adjust();
                });
        }
    });
}

function sec_incidencias_reabrir(id) {

    loading(true);
    $.post('/sys/set_incidencias.php', {
        "set_incidencias_reabrir": id,
    }, function (r) {
        loading(false);
        let obj = jQuery.parseJSON(r);
        let message = obj.message;
        if (obj.error) {
            auditoria_send({
                "proceso": "sec_incidencias_reabrir_error", "data": {
                    id,
                    message
                }
            });

            swal({
                title: obj.title,
                text: message,
                type: obj.swal_type,
                timer: 5000,
                closeOnConfirm: true
            },
                function () {
                    swal.close();
                });
            return false;
        } else {
            swal({
                title: "Reabierto",
                text: message,
                type: "success",
                closeOnConfirm: true
            },
                function () {
                    swal.close();
                    //tablaserver.ajax.reload(null, false);
                    let tbl_incidencias = $('#tbl_incidencias')
                    tbl_incidencias.DataTable().ajax.reload();
                    tbl_incidencias.DataTable().columns.adjust();
                });
        }

    });
}

function sec_incidencias_events() {

    $('#modal_incidencia [name=producto]').on('change', function () {
        let producto = $("option:selected", $(this)).text();
        $("#modal_incidencia #tipo").val("");
        $("#modal_incidencia #tipo option").hide();
        $("#modal_incidencia #tipo option[data-producto='" + producto + "']").show();
        $("#modal_incidencia #tipo").val($("#modal_incidencia #tipo option[data-producto='" + producto + "']:first").val());
    });

    $(".switch_reimpresion")
        .bootstrapToggle({
            on: "Si",
            off: "No",
            onstyle: "success",
            offstyle: "danger",
            size: "mini"
        });

    $('[name="selectProducto"]').on('click', function () {
        let producto = $(this).attr("value");
        $(".div_tipos").hide();
        $(".div_tipos[data-producto='" + producto + "']").show();
    });

    $('input[type=radio]').on('click', function () {
        var element = $(this);
        if (element.parent().parent().parent().parent().hasClass('botones_inc')) {
            var contenedor = element.closest(".botones_inc");
            $('input[type=radio]', contenedor).parent().removeClass('clicked_t');
            element.parent().addClass('clicked_t');
        }
    });

    $('#modal_incidencia input[type=checkbox]').on('click', function () {
        var element = $(this);
        let contenedor = element.closest('.botones_inc');
        if (contenedor.length > 0) {
            if ($(this).prop('checked')) {
                element.parent().removeClass('clicked_t');
                element.parent().addClass('clicked_t');
            } else {
                element.parent().removeClass('clicked_t');
            }
        }
    });

    $('.save_btn')
        .off()
        .click(function (event) {
            var btn = $(this);
            sec_incidencias_save(btn);
        });

    $("#modal_incidencia #solve_btn")
        .off()
        .click(function (event) {
            //sec_incidencias_solve();
            var form = $("#modal_incidencia form")[0];
            sec_incidencias_solve(form);
        });

    $("#modal_incidencia .close_btn")
        .off()
        .click(function (event) {
            sec_incidencias_solucionar_modal('hide');
        });

    $(".select2")
        .filter(function () {
            return $(this).css('display') !== 'none';
        })
        .select2({
            closeOnSelect: true,
            width: '100%'
        });

    $(document).on('click', '#tbl_incidencias .btn-reasignar', function () {
        let incidencia_id = $(this).attr("data-id");
        $.post('/sys/set_incidencias.php', {
            'set_incidencias_check_coordinador_supervisor': true
        }, function (response) {
            response = JSON.parse(response);
            if (response.check_result) {
                $.when(set_incidencias_agente_asignado(incidencia_id)).done(function (response) {
                    let puede_solucionar = check_incidencia_agente_puede_solucionar(response);
                    if (puede_solucionar !== null) {
                        if (puede_solucionar) {
                            swal({
                                title: "Solucionar",
                                text: "Este caso le pertenece. ¿Desea solucionar este caso?",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonText: "Sí",
                                cancelButtonText: "No",
                                closeOnConfirm: true
                            }, function () {
                                set_incidencias_get(incidencia_id);
                            });
                        } else {
                            let agentes = localStorage.getItem('sec_incidencias_ca_agentes');
                            if (agentes) {
                                if(localStorage.getItem('sec_incidencias_ca_agentes') === null){
                                    agentes = JSON.parse(agentes);
                                    render_agentes_list(agentes);
                                }
                                $('#sec_incidencias_input_incidencia_id').val(incidencia_id);
                                $('#sec_incidencias_modal_agentes').modal('show');
                            } else {
                                $.post('/sys/set_incidencias.php', {
                                    "set_incidencias_get_agentes": true
                                }, function (response) {
                                    if (response) {
                                        agentes = jQuery.parseJSON(response);
                                        if (agentes.length) {
                                            if(localStorage.getItem('sec_incidencias_ca_agentes') === null){
                                                localStorage.setItem('sec_incidencias_ca_agentes', JSON.stringify(agentes));
                                                render_agentes_list(agentes);
                                            }
                                            $('#sec_incidencias_input_incidencia_id').val(incidencia_id);
                                            $('#sec_incidencias_modal_agentes').modal('show');
                                        }
                                    }
                                });
                            }
                        }
                    }
                });

            } else {

                $.when(set_incidencias_agente_asignado(incidencia_id)).done(function (response) {
                    let puede_solucionar = check_incidencia_agente_puede_solucionar(response);
                    if (puede_solucionar !== null) {
                        if (puede_solucionar) {
                            swal({
                                title: "Solucionar",
                                text: "Este caso le pertenece. ¿Desea solucionar este caso?",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonText: "Sí",
                                cancelButtonText: "No",
                                closeOnConfirm: true
                            }, function () {
                                set_incidencias_get(incidencia_id);
                            });
                        } else {
                            swal({
                                title: "Reasignar",
                                text: "Este caso no le pertenece. ¿Desea reasignar este caso?",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonText: "Sí",
                                cancelButtonText: "No",
                                closeOnConfirm: true
                            }, function () {
                                set_incidencias_reasignar(incidencia_id);
                            });
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", "#tbl_incidencias .btn-solucionar", function () {
        let incidencia_id = $(this).attr("data-id");
        $.when(set_incidencias_agente_asignado(incidencia_id)).done(function (response) {
            let puede_solucionar = check_incidencia_agente_puede_solucionar(response);
            if (puede_solucionar !== null) {
                if (!puede_solucionar) {
                    swal({
                        title: "Reasignar",
                        text: "Este caso no le pertenece. ¿Desea reasignar este caso?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Sí",
                        cancelButtonText: "No",
                        closeOnConfirm: true
                    }, function () {
                        set_incidencias_reasignar(incidencia_id);
                    });
                } else {
                    set_incidencias_get(incidencia_id);
                }
            }
        });

    });

    $(document).on("click", "#tbl_incidencias .btn-reabrir", function () {
        let incidencia_id = $(this).attr("data-id");
        $.post('/sys/set_incidencias.php', {
            "set_incidencias_check_permiso_reabrir": true
        }, function (r) {
            try {
                let obj = jQuery.parseJSON(r);
                if (obj.puede_reabrir) {
                    swal({
                        title: "Reabrir",
                        text: "¿Está seguro que desea reabrir el caso?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Sí",
                        cancelButtonText: "No",
                        closeOnConfirm: true
                    }, function () {
                        sec_incidencias_reabrir(incidencia_id);
                    });
                } else {
                    swal({
                        title: "No Autorizado",
                        text: "Usted no esta aotorizado para reabrir el caso.",
                        type: "warning",
                        timer: 3000,
                        closeOnConfirm: true
                    },
                        function () {
                            swal.close();
                        });
                }
            } catch (e) {
            }
        }
        );
    });

    $(document).on("click", "#tbl_incidencias .btn-yolohago", function () {
        let incidencia_id = $(this).attr("data-id");
        let assigned = $(this).attr("data-assigned");
        if (assigned === "1") {
            swal({
                title: "Incidencia",
                text: "¿Deseas asignarte este caso?",
                type: "info",
                showCancelButton: true,
                confirmButtonText: "Sí",
                cancelButtonText: "No",
                closeOnConfirm: true
            }, function () {
                sec_incidencias_asignar(incidencia_id);
            });
        } else {
            sec_incidencias_asignar(incidencia_id);
        }

    });

    tablaserver = listar_incidencias();
    $("#btn_actualizar_tbl_fechas").off("click").on("click", function () {
        let tbl_incidencias = $('#tbl_incidencias');
        tbl_incidencias.DataTable().ajax.reload();
        tbl_incidencias.DataTable().columns.adjust();
    });
    $("#btn_actualizar_tbl").off("click").on("click", function () {
        let tbl_incidencias = $('#tbl_incidencias');
        tbl_incidencias.DataTable().ajax.reload();
        tbl_incidencias.DataTable().columns.adjust();
    });

    $("#local_id").on("change", function () {
        let phone = $(this).find(':selected').data('phone');
        $("#local_phone").val(phone);
    });

    $("#incidencias_redes").on("change", function () {
        tablaserver.ajax.reload(null, false);
        tablaserver.columns.adjust();
    });

    $("#incidencias_redes_ca").on("change", function () {
        refresh_tbl_incidencias_historial(false, false);
    });

    $("#incidencias_ca_excel").on("click", function () {
        let data = {
            user_id: $("#sec_incidencias_ca_user_id").val()
        }
        loading(true)
        $.post("/sys/get_incidencias_ca.php", {"get_incidencias_ca_historial_table_excel": data}).done(function (response) {
            let obj;
            try {
                obj = JSON.parse(response)
            } catch (e) {
                alert("Wrong File")
                return
            }
            if (obj) window.open(obj.path);
            loading(false);
        });
    });

    $(document).on("click", ".btn_calificar", function () {
        $("#modal_satisfaccion").modal("show");
        $("#incidencia_id").val($(this).data("id"))
    });

    $(document).on("click", ".satisfaccion_choice", function () {
        $("#modal_satisfaccion").modal("hide");
        let data = {
            incidencia_id: $("#incidencia_id").val(),
            value: $(this).data("value")
        }

        loading(true);
        auditoria_send({"proceso": "sec_incidencias_set_satisfaccion", "data": data});
        $.post('/sys/set_incidencias.php', {
            "set_incidencias_satisfaccion": data
        }, function (r) {
            loading();
            let obj = jQuery.parseJSON(r);
            $("#modal_satisfaccion").modal("hide");
            refresh_tbl_incidencias_historial(true);
        });

    });

    $("#btn_agregar_nota").off().on("click", function () {
        $("#modal_notas").modal("show");
    });

    $('#modal_notas').on("shown.bs.modal", function () {
        $("#nota_txt", $('#modal_notas')).focus();
    });

    $('#modal_notas').on("hidden.bs.modal", function () {
        $("#nota_txt", $('#modal_notas')).val("");
        $("#nota_id", $('#modal_notas')).val("");
        $("#nota_imagen", $('#modal_notas')).val("");
        $(".vista_previa_nota_img", $('#modal_notas')).attr("data-imagen", "");
        $("#vista_previa_modal #img01").attr("src", "");
        $(".vista_previa_nota_img").hide();

    });

    $("#modal_notas #save_btn")
        .off()
        .click(function (event) {
            var btn = $(this);
            if ($("#modal_notas #nota_id").val() === "") {
                sec_incidencias_notas_save(btn);
            } else {
                sec_incidencias_notas_editar(btn);
            }
        });

    $("#incidencias_ca_notas .contenedor ocultar_notas")
        .off()
        .click(function (event) {
            var btn = $(this);
            sec_incidencias_notas_ocultar(btn);
        });

    $("#incidencias_ca_notas .contenedor .editar_notas")
        .off()
        .click(function (event) {
            $("#modal_notas").modal("show");
            var btn = $(this);
            var id = btn.attr("data-id");
            var imagen = btn.attr("data-imagen");
            var nota_txt = btn.closest(".nota_fila").find(".nota_texto").text();
            $("#modal_notas #nota_txt").val(nota_txt);
            $("#modal_notas #nota_id").val(id);


            if (imagen !== "") {
                $(".vista_previa_nota_img").attr("data-imagen", imagen);
                $("#vista_previa_modal #img01").attr("src", "files_bucket/incidencia_notas/" + imagen);

                $(".vista_previa_nota_img").off("click").on("click", function (e) {
                    e.preventDefault();

                    $("#vista_previa_modal").off("shown.bs.modal").on("shown.bs.modal", function () {
                        if ($(".modal-backdrop").length > -1) {
                            $(".modal-backdrop").not(':first').remove();
                        }
                        $("#img01").imgViewer2();
                    });
                    $("#vista_previa_modal").off("hide.bs.modal").on("hide.bs.modal", function () {
                        $("#img01").imgViewer2("destroy");
                    });
                    $("#vista_previa_modal").modal("show");
                })
                $(".vista_previa_nota_img").show();
            } else {
                $(".vista_previa_nota_img").hide();
            }

            //	sec_incidencias_notas_editar(btn);
        });


    $("#modal_notas .close_btn")
        .off()
        .click(function (event) {
            $("#modal_notas").modal("hide");
        });

    if ($(".nota_fila .div_botones").children().length === 0) {
        $(".nota_fila").find(".col-xs-9").removeClass("col-xs-9").addClass("col-xs-12");
        $(".nota_fila .col-xs-3").remove();
    }

    $("#sec_incidencias_csv_btn")
        .off()
        .click(function () {
            sec_incidencias_report_csv();
        });

    $("#sec_incidencias_xls_btn")
        .off()
        .click(function () {
            sec_incidencias_report_xls();
        });

    if (document.getElementById("tbl_incidencias_historial") !== null) {
        refresh_tbl_incidencias_historial();
    }

    $("#btn_columnas_incidencias").off("click").on("click", function () {
        if ($("#tbl_incidencias tbody tr").length === 0) {
            swal("No hay datos!", "", "error");
            return false;
        }
        $("#filter_columnas_modal").modal("show");
    });

    /*
        $("[name='selectRecomendacion']").on("change",function(i,e){
            if($(this).val() =="Visita Técnica" ){
                $(".visita_tecnica").show();
            }
            else{
                $(".visita_tecnica").hide();
                limpiar();
            }
        })
    */

    $("[name='selectRecomendacion']").on("change", function (i, e) {
        switch ($(this).val()) {
            case "Visita Técnica":
                $(".visita_tecnica").show();
                $(".seguimiento_soporte").hide();
                $(".foto").show();
                limpiar_2();
                break;
            case "Seguimiento Soporte":
                $(".seguimiento_soporte").show();
                $(".visita_tecnica").hide();
                $(".foto").hide();
                limpiar();
                break;
            default:
                $(".visita_tecnica, .seguimiento_soporte").hide();
                $(".foto").hide();
                limpiar();
                limpiar_2();
                break;
        }
    });

    $(".nota_fila_imagen").off("click").on("click", function (e) {
        e.preventDefault();
        var imagen = $(this).attr("data-src");
        $("#vista_previa_modal #img01").attr("src", "files_bucket/incidencia_notas/" + imagen);
        $("#vista_previa_modal").off("shown.bs.modal").on("shown.bs.modal", function () {
            if ($(".modal-backdrop").length > -1) {
                $(".modal-backdrop").not(':first').remove();
            }
            $("#vista_previa_modal #img01").imgViewer2();
        });
        $("#vista_previa_modal").off("hide.bs.modal").on("hide.bs.modal", function () {
            $("#vista_previa_modal #img01").imgViewer2("destroy");
            $("#vista_previa_modal #img01").attr("src", "")
        });
        $("#vista_previa_modal").modal("show");
    })

    $("#telefono2").validar_numerico_decimales({decimales: 0});

    localStorage.removeItem('sec_incidencias_ca_agentes');

    $('#sec_incidencias_input_filtrar_agentes').on('keyup', function () {
        filtrar_agentes_reasignar();
    });

    $('#sec_incidencias_btn_reasignar_agente_seleccionado').on("click", function (e) {
        e.preventDefault();
        let selected_agente = document.querySelector('input[name="sec_incidencias_input_radio_agente"]:checked');
        if (selected_agente) {
            let usuario = selected_agente.closest('label').textContent;
            swal({
                title: "Alerta de Reasignación",
                text: "¿Está seguro que desea asignar el caso al usuario " + usuario + "?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí",
                cancelButtonText: "No",
                closeOnConfirm: true
            }, function () {
                let incidencia_id = $('#sec_incidencias_input_incidencia_id').val();
                let usuario_id = selected_agente.value;
                reasignar_agente_seleccionado(incidencia_id, usuario_id);
                $('#sec_incidencias_modal_agentes').modal('hide');
            });
        } else {
            swal({
                title: "Seleccione un Agente",
                text: 'Debe seleccionar un agente.',
                type: "warning",
                timer: 3000,
                closeOnConfirm: true
            },
                function () {
                    swal.close();
                });
        }
    });

    $('#sec_incidencias_modal_agentes').on('hidden.bs.modal', function () {
        $("#sec_incidencias_list_agentes input:radio:checked").removeAttr("checked");
        $('#sec_incidencias_input_incidencia_id').val('');
        restaurarDatosOriginales();
        $('#sec_incidencias_input_filtrar_agentes').val('')
    });

    $('#sec_incidencias_modal_agentes').on('shown.bs.modal', function () {
        $('#sec_incidencias_input_filtrar_agentes').focus();
    });

    $('#start_date').change(function() {
        //$('#tbl_incidencias').DataTable().ajax.reload();
    });

    $('#end_date').change(function() {
        //$('#tbl_incidencias').DataTable().ajax.reload();
    });

    // DETALLE BOTON REASIGNAR
    $(document).on('click', '#btn_reasignar', function () {
        // let incidencia_id = $(this).attr("data-id");
        var incidencia_id = $('#id_incidence--selected').text();
        // console.log(incidencia_id)
        $.post('/sys/set_incidencias.php', {
            'set_incidencias_check_coordinador_supervisor': true
        }, function (response) {
            response = JSON.parse(response);
            if (response.check_result) {
                $.when(set_incidencias_agente_asignado(incidencia_id)).done(function (response) {
                    let agentes = localStorage.getItem('sec_incidencias_ca_agentes');
                    if (agentes) {
                        if(localStorage.getItem('sec_incidencias_ca_agentes') === null){
                            agentes = JSON.parse(agentes);
                            render_agentes_list(agentes);
                        }
                        $('#sec_incidencias_input_incidencia_id').val(incidencia_id);
                        $('#modal_detalle_incidencia').modal('hide');
                        $('#sec_incidencias_modal_agentes').modal('show');
                    } else {
                        $.post('/sys/set_incidencias.php', {
                            "set_incidencias_get_agentes": true
                        }, function (response) {
                            if (response) {
                                agentes = jQuery.parseJSON(response);
                                if (agentes.length) {
                                    if(localStorage.getItem('sec_incidencias_ca_agentes') === null){
                                        localStorage.setItem('sec_incidencias_ca_agentes', JSON.stringify(agentes));
                                        render_agentes_list(agentes);
                                    }
                                    $('#sec_incidencias_input_incidencia_id').val(incidencia_id);
                                    $('#modal_detalle_incidencia').modal('hide');
                                    $('#sec_incidencias_modal_agentes').modal('show');
                                }
                            }
                        });     
                    }
                });
            }
        });
    });
}

function limpiar() {
    $("[name='selectEquipo']").closest("label").removeClass("clicked_t");
    $("[name='selectEquipo']:checked").prop("checked", false);
    $("[name='equipo']").val("");
    $("#nota_tecnico").val("");
    $(".visita_tecnica").hide();
}

function limpiar_2() {
    $("[name='selectEquipo']").closest("label").removeClass("clicked_t");
    $("[name='selectEquipo']:checked").prop("checked", false);
    $("#nota_soporte").val("");
    $(".seguimiento_soporte").hide();
}

/////solucionar incidencia
function sec_incidencias_solve(form) {
    loading(true);
    var dataForm = new FormData(form);
    dataForm.append("set_incidencias_solve", "set_incidencias_solve");
    dataForm.append("recomendacion", $("#selectRecomendacion checked", $(form)).text());

    result = {};
    for (var entry of dataForm.entries()) {
        result[entry[0]] = entry[1];
    }
    //result.recomendacion = $("#selectRecomendacion checked" ,$(form)).text();
    var set_data = {};
    if (result.foto.name != "") {
        result.foto = result.foto.name;
    } else {
        result.foto = "";
    }
    set_data = result;

    $.ajax({
        url: 'sys/set_incidencias.php',
        type: 'POST',
        data: dataForm,
        cache: false,
        contentType: false,
        processData: false,
        success: function (r) {
            var obj = jQuery.parseJSON(r);
            if (obj.error) {
                set_data.error = obj.error;
                set_data.error_msg = obj.error_msg;
                auditoria_send({"proceso": "sec_incidencias_solve_error", "data": set_data});
                loading(false);
                swal({
                    title: "¡Error!",
                    text: obj.error_msg,
                    type: "warning",
                    timer: 3000,
                    closeOnConfirm: true
                },
                    function () {
                        swal.close();
                        custom_highlight($("#" + obj.error_focus, $("#formGuardarSolucion")));
                        setTimeout(function () {
                            $("#" + obj.error_focus, $("#formGuardarSolucion")).val("").focus();
                        }, 10);
                    });
            } else if (obj.error_query) {
                set_data.error = obj.error_query;
                set_data.error_msg = obj.error_msg;
                auditoria_send({"proceso": "sec_incidencias_solve_error_query", "data": set_data});
                loading(false);
                swal({
                    title: "¡Error!",
                    text: obj.error_msg,
                    type: "warning",
                    timer: 3000,
                    closeOnConfirm: true
                },
                    function () {
                        swal.close();
                        custom_highlight($("#" + obj.error_focus, $("#formGuardarSolucion")));
                        setTimeout(function () {
                            $("#" + obj.error_focus, $("#formGuardarSolucion")).val("").focus();
                        }, 10);
                    });
            } 
            else {
                set_data.curr_login = obj.curr_login;
                auditoria_send({"proceso": "sec_incidencias_solve_done", "data": set_data});
                loading(false);
                swal({
                    title: obj.mensaje,
                    text: "",
                    type: "success",
                    timer: 5000,
                    closeOnConfirm: true
                },
                    function () {
                        //tablaserver.ajax.reload(null, false);
                        let tbl_incidencias = $('#tbl_incidencias');
                        tbl_incidencias.DataTable().ajax.reload();
                        tbl_incidencias.DataTable().columns.adjust();
                        $("#modal_incidencia").modal("hide");
                        //m_reload();

                        auditoria_send({"proceso": "save_item", "data": set_data});
                        //window.location="./?sec_id="+sec_id;

                    });
            }
        }
    });
}

///////guardar incidencia
function sec_incidencias_save(btn) {
    loading(true);
    var set_data = {};
    $(".save_data").each(function (index, el) {
        set_data[$(el).attr("name")] = $(el).val();
    });
    let producto = $("input[name='selectProducto']:checked").val();
    set_data["selectProducto"] = producto;
    let div_producto = $("div[data-producto = '" + producto + "']");
    set_data["selectTipo"] = $("input[name='selectTipo']:checked", div_producto).val();
    set_data["reimpresion"] = $("[name='reimpresion']:checked").val();

    $.post('/sys/set_incidencias.php', {
        "sec_incidencias_save": set_data
    }, function (r) {
        //loading();
        try {
            var obj = jQuery.parseJSON(r);
            if (obj.error) {
                swal_type = obj.swal_type === undefined ? "warning" : obj.swal_type;
                swal_timeout = obj.swal_timeout === undefined ? 3000 : obj.swal_timeout;
                set_data.error = obj.error;
                set_data.error_msg = obj.error_msg;
                auditoria_send({"proceso": "sec_incidencias_save_error", "data": set_data});
                loading(false);
                swal({
                    title: "¡Error!",
                    text: obj.error_msg,
                    type: swal_type,
                    timer: swal_timeout,
                    closeOnConfirm: true
                },
                    function () {
                        swal.close();
                        custom_highlight($(".save_data[name='" + obj.error_focus + "']"));
                        if (obj.error_focus !== "incidencia_txt") {
                            setTimeout(function () {
                                let $error_focus = $(".save_data[name='" + obj.error_focus + "']");
                                if ($error_focus.attr("type") !== "radio") {
                                    $$error_focus.val("").focus();
                                }
                            }, 10);
                        }

                    });
            } else {
                set_data.curr_login = obj.curr_login;
                auditoria_send({"proceso": "sec_incidencias_save_done", "data": set_data});
                loading(false);
                swal({
                    title: obj.mensaje,
                    text: "",
                    type: "success",
                    timer: 5000,
                    closeOnConfirm: true
                },
                    function () {
                        m_reload();
                        if (btn.data("then") == "reload") {
                            if (set_data.id == "new") {
                                set_data.id = obj.id;
                                auditoria_send({"proceso": "add_item", "data": set_data});
                                window.location = "./?sec_id=" + sec_id;
                            } else {
                                auditoria_send({"proceso": "save_item", "data": set_data});
                                swal.close();
                                m_reload();
                            }
                        } else if (btn.data("then") == "exit") {
                            auditoria_send({"proceso": "save_item", "data": set_data});
                            window.location = "./?sec_id=" + sec_id;
                        } else {
                        }
                    });
            }
        } catch (err) {

        }

    });
}

function sec_incidencias_report_csv() {
    loading(true);
    let start_date = $("#start_date").val();
    let end_date = $("#end_date").val();
    let data = {
        start_date,
        end_date
    }

    $.post('/sys/set_incidencias.php', {
        "sec_incidencias_csv": data
    }, function (r) {
        loading();
        try {
            var obj = jQuery.parseJSON(r);
            if (obj.error) {
                data.error = obj.error ?? '';
                data.error_msg = obj.error_msg ?? '';
                auditoria_send({"proceso": "sec_incidencias_report_csv", "data": data});
                loading(false);
                swal({
                    title: "¡Error!",
                    text: obj.error_msg,
                    type: "warning",
                    timer: 3000,
                    closeOnConfirm: true
                });
            } else {
                loading(false);
                data.curr_login = obj.curr_login ?? '';
                auditoria_send({"proceso": "sec_incidencias_report_csv", "data": data});
                window.open(obj.path);
            }
        } catch (err) {

        }
    });
}

function sec_incidencias_report_xls() {
    loading(true);
    let start_date = $("#start_date").val();
    let end_date = $("#end_date").val();
    let data = {
        start_date,
        end_date
    }
    $.post('/sys/set_incidencias.php', {
        "sec_incidencias_xls": data
    }, function (r) {
        loading(false);
        let obj = jQuery.parseJSON(r);
        loading(false);
        window.open(obj.path);
    });
}

function refresh_tbl_incidencias_historial(isSatisfaction = false, showMessage = true) {
    //loading(true);
    let user_id = $("#sec_incidencias_ca_user_id").val();
    let red_id = $("#incidencias_redes_ca").val();
    let data = {
        user_id,
        red_id
    }

    $.post('/sys/get_incidencias_ca.php', {
        "get_incidencias_ca_historial_table": data
    }, function (r) {
        //loading();
        try {
            var obj = jQuery.parseJSON(r);
            if (obj.error) {
                if (typeof obj.login != "undefined") {
                    swal({
                        title: "Sesión Finalizada",
                        text: "",
                        type: "warning",
                        timer: 3000,
                        closeOnConfirm: true
                    },
                        function () {
                            swal.close();
                            window.location.reload();
                        });
                    return false;
                }
                //loading(false);
                data.error = obj.error ?? '';
                data.error_msg = obj.error_msg ?? '';
                //auditoria_send({"proceso":"sec_incidencias_ca_historial_table","data":data});
                $("#tbl_incidencias_historial_body").html("<td colspan='8'>No hay registros</td>");
            } else {
                //loading(false);
                //auditoria_send({"proceso":"sec_incidencias_ca_historial_table","data":data});

                let table_data = obj.data;
                let table_data_html = "";
                if (table_data.length > 0) {
                    table_data.forEach(function (row) {
                        let satisfactionButton = '';
                        if (row.estado === "Atendido") {
                            if (row.satisfaccion) {
                                satisfactionButton = `<span class="mr-2">${row.satisfaccion}</span><button data-id="${row.id}" class="btn btn-info btn_calificar"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>`
                                if (!row.same) {
                                    satisfactionButton = `<span class="mr-2">${row.satisfaccion}</span>`
                                }
                            } else {

                                satisfactionButton = `<button data-id="${row.id}" class="btn btn-info btn_calificar">Calificar</button>`
                                if (!row.same) {
                                    satisfactionButton = `Sin calificar`
                                }
                            }
                        }

                        /*let satisfactionButton = row.satisfaccion ?
                            `<span class="mr-2">${row.satisfaccion}</span><button data-id="${row.id}" class="btn btn-info btn_calificar"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>` :
                            `<button data-id="${row.id}" class="btn btn-info btn_calificar">Calificar</button>`*/

                        table_data_html +=
                            `
							<tr>
								<td>${row.id ?? ''}</td>
								<td>${row.fecha ?? ''}</td>
								<td>${row.usuario ?? ''}</td>
								<td>${row.tienda ?? ''}</td>
								<td>${row.telefono ?? ''}</td>
								<td>${row.incidencia ?? ''}</td>
								<td>${row.estado ?? ''}</td>
								<td>${row.agente ?? ''}</td>
								<td>${row.observacion ?? ''}</td>
								<td>${satisfactionButton}</td>
							</tr>
						`
                    })
                }
                $("#tbl_incidencias_historial_body").html(table_data_html);
                if (isSatisfaction) {
                    swal({
                        title: "Mensaje",
                        text: "Su calificación ha sido ingresada.",
                        type: "success",
                        timer: 3000,
                        closeOnConfirm: true
                    })
                } else if (sec_incidencias_ca_table_data !== null) {
                    if (JSON.stringify(sec_incidencias_ca_table_data) !== JSON.stringify(table_data) && showMessage) {
                        swal({
                            title: "Nuevos Datos",
                            text: "La tabla Historial fue actualizada",
                            type: "info",
                            timer: 3000,
                            closeOnConfirm: true
                        })
                    }
                }
                sec_incidencias_ca_table_data = table_data;
            }
        } catch (err) {
            console.log(err);
        }
    });

    setTimeout(refresh_tbl_incidencias_historial, 1000 * 60);
}

/////guardar notas
function sec_incidencias_notas_save(btn) {
    loading(true);
    var set_data = {};
    $("TEXTAREA", $("#modal_notas form")).each(function (index, el) {
        set_data[$(el).attr("name")] = $(el).val();
    });

    var dataForm = new FormData($("#modal_notas form")[0]);
    dataForm.append("sec_incidencias_notas_save", 'sec_incidencias_notas_save');

    $.ajax({
        url: '/sys/set_incidencias.php',
        type: 'POST',
        data: dataForm,
        cache: false,
        contentType: false,
        processData: false,
        success: function (r) {
            try {
                var obj = jQuery.parseJSON(r);
                loading(false);
                if (obj.error) {
                    set_data.error = obj.error;
                    set_data.error_msg = obj.error_msg;
                    auditoria_send({"proceso": "sec_incidencias_notas_save_error", "data": set_data});
                    loading(false);
                    swal({
                        title: "¡Error!",
                        text: obj.error_msg,
                        type: "warning",
                        timer: 3000,
                        closeOnConfirm: true
                    },
                        function () {
                            swal.close();
                            var form = $("form", $("#modal_notas"));
                            custom_highlight($("#" + obj.error_focus, form));
                            setTimeout(function () {
                                $("#" + obj.error_focus, form).val("").focus();
                            }, 10);
                        });
                } else {
                    set_data.curr_login = obj.curr_login;
                    auditoria_send({"proceso": "sec_incidencias_notas_save_done", "data": set_data});
                    loading(false);
                    swal({
                        title: obj.mensaje,
                        text: "",
                        type: "success",
                        timer: 5000,
                        closeOnConfirm: true
                    },
                        function () {
                            m_reload();
                            auditoria_send({"proceso": "save_item", "data": set_data});
                            window.location = "./?sec_id=" + sec_id;
                        });
                }
            } catch (err) {

            }

        }
    });
}

function sec_incidencias_notas_ocultar(btn) {
    loading(true);
    var set_data = {};

    set_data["nota_id"] = btn.attr("data-id");


    $.post('/sys/set_incidencias.php', {
        "sec_incidencias_notas_ocultar": set_data
    }, function (r) {
        //loading();
        try {
            var obj = jQuery.parseJSON(r);

            if (obj.error) {
                set_data.error = obj.error;
                set_data.error_msg = obj.error_msg;
                auditoria_send({"proceso": "sec_incidencias_notas_ocultar_error", "data": set_data});
                loading(false);
                swal({
                    title: "¡Error!",
                    text: obj.error_msg,
                    type: "warning",
                    timer: 3000,
                    closeOnConfirm: true
                },
                    function () {
                        swal.close();
                        setTimeout(function () {
                        }, 10);
                    });
            } else {
                set_data.curr_login = obj.curr_login;
                auditoria_send({"proceso": "sec_incidencias_notas_ocultar_done", "data": set_data});
                loading(false);
                swal({
                    title: obj.mensaje,
                    text: "",
                    type: "success",
                    timer: 5000,
                    closeOnConfirm: true
                },
                    function () {
                        m_reload();
                        auditoria_send({"proceso": "ocultar_item", "data": set_data});
                        window.location = "./?sec_id=" + sec_id;

                    });
            }
        } catch (err) {

        }

    });
}

function sec_incidencias_notas_editar(btn) {
    loading(true);
    var set_data = {};

    //set_data["nota_id"]=btn.attr("data-id");
    $("TEXTAREA,input", $("#modal_notas form")).each(function (index, el) {
        set_data[$(el).attr("name")] = $(el).val();
    });

    var dataForm = new FormData($("#modal_notas form")[0]);
    dataForm.append("sec_incidencias_notas_update", 'sec_incidencias_notas_update');
    dataForm.append("imagen_actual", $(".vista_previa_nota_img").attr("data-imagen"));

    $.ajax({
        url: '/sys/set_incidencias.php',
        type: 'POST',
        data: dataForm,
        cache: false,
        contentType: false,
        processData: false,
        success: function (r) {
            try {
                var obj = jQuery.parseJSON(r);
                loading(false);
                if (obj.error) {
                    set_data.error = obj.error;
                    set_data.error_msg = obj.error_msg;
                    auditoria_send({"proceso": "sec_incidencias_notas_update_error", "data": set_data});
                    loading(false);
                    swal({
                        title: "¡Error!",
                        text: obj.error_msg,
                        type: "warning",
                        timer: 3000,
                        closeOnConfirm: true
                    },
                        function () {
                            swal.close();
                            setTimeout(function () {
                            }, 10);
                        });
                } else {
                    set_data.curr_login = obj.curr_login;
                    auditoria_send({"proceso": "sec_incidencias_notas_update_done", "data": set_data});
                    loading(false);
                    swal({
                        title: obj.mensaje,
                        text: "",
                        type: "success",
                        timer: 5000,
                        closeOnConfirm: true
                    },
                        function () {
                            m_reload();
                            auditoria_send({"proceso": "update_item", "data": set_data});
                            window.location = "./?sec_id=" + sec_id;
                        });
                }
            } catch (err) {

            }

        }
    });
}

function sec_incidencias_solucionar_modal(opt = "show") {
    var nombre = 'modal_incidencia';
    $("#" + nombre).modal(opt);
}

// Resize table when open sidebar menú Gestión
$('.sidebar-collapse').on('click', function () {
    setTimeout(() => {
        $(window).trigger('resize');
    }, 250);
});

let dataIncidenceAjax;
// Show Modal Detail Incidencia with Data by ID
$(document).on("click", "#tbl_incidencias #mostrar_detalle_incidencia", function () {
    let incidencia_id = $(this).attr("data-id");
    loading(true);
    $.post('/sys/set_incidencias.php', {
        "get_obtener_incidencia_por_id": 1,
        "id" : incidencia_id,
    }, function (r) {
        loading();
        let response = jQuery.parseJSON(r);
        if (response.status === 200) {
            const {
                id, created_at, usuario, local, red, phone, telefono2, producto, tipo, reimpresion, 
                teamviewer_id, teamviewer_password, incidencia_txt, EstadoCol, estado_servicio_tecnico,
                fecha_asignada, agente, agente2, agente_reasignado, fecha_solucion, recomendacion, solucion_txt, satisfaccion 
            } = response.result[0];
            
            $("#id_incidence--selected").text(id);
            $("#date_incidence--selected").text(created_at || '-');
            $("#user_incidence--selected").text(usuario || '-');
            $("#local_incidence--selected").text(local || '-');
            $("#red_incidence--selected").text(red || '-');
            $("#phone_incidence--selected").text(phone || '-');
            $("#phone2_incidence--selected").text(telefono2 || '-');
            $("#product_incidence--selected").text(producto || '-');
            $("#type_incidence--selected").text(tipo || '-');
            $("#reprint_incidence--selected").text(reimpresion === '0' ? 'No' : 'Si');
            $("#id_tvw_incidence--selected").text(teamviewer_id || '-');
            $("#pass_tvw_incidence--selected").text(teamviewer_password || '-');
            $("#problem_incidence--selected").text(incidencia_txt || '-');
            $("#status_incidence--selected").text(EstadoCol || '-');
            $("#status_serv_tec_incidence--selected").text(estado_servicio_tecnico || '-');
            $("#assigned_date_incidence--selected").text(fecha_asignada || '-');
            $("#agent_incidence--selected").text(agente || '-');
            $("#agent2_incidence--selected").text(agente2 || '-');
            $("#agent_reasignado_incidence--selected").text(agente_reasignado || '-');
            $("#solution_date_incidence--selected").text(fecha_solucion || '-');
            $("#tip_incidence--selected").text(recomendacion || '-');
            $("#obs_incidence--selected").text(solucion_txt || '-');
            $("#satisfaction_incidence--selected").text(satisfaccion || '-');

            var boton = document.getElementById("btn_reasignar");
            if(!response.result[0].check_area_cargo || response.result[0].EstadoCol !== 'Asignado'){
                boton.classList.add("invisible");
            }else{
                boton.classList.remove("invisible");
                // boton.classList.add("btn-reasignar");
            }

            // Open Modal Action
            $("#modal_detalle_incidencia").modal("show");
        } else {
            alert('¡Error!', response.status, response.message)
        }
    });
});

function listar_incidencias() {
    tablaserver = $("#tbl_incidencias")
        .on('order.dt', function () {
            $('table').css('width', '100%');
            $('.dataTables_scrollHeadInner').css('width', '100%');
            $('.dataTables_scrollFootInner').css('width', '100%');
        })
        .on('search.dt', function () {
            //                                responsive_tabla_scroll(tablaserver);
            $('table').css('width', '100%');
            $('.dataTables_scrollHeadInner').css('width', '100%');
            $('.dataTables_scrollFootInner').css('width', '100%');
        })
        .on('page.dt', function () {
            $('table').css('width', '100%');
            $('.dataTables_scrollHeadInner').css('width', '100%');
            $('.dataTables_scrollFootInner').css('width', '100%');
        })
        .DataTable({
            "paging": true,
            //"scrollX": true,
            "sScrollX": "100%",
            //"scrollY": "450px",
            //   "scrollCollapse": false,
            //"bProcessing": true,
            processing: true,
            // "sScrollXInner":'100%',
            "language": {
                "search": "Buscar:",
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron registros",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "No hay registros",
                "infoFiltered": "(filtrado de _MAX_ total records)",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                sProcessing: "Procesando..."
            },
            "deferLoading": 0,
            //"bDeferRender": false,
            autoWidth: true,
            //pageResize: true,
            // pageLength: 10,
            pageLength: document.querySelector('.main-container').offsetHeight < 800 ? 5 : 10,
            serverSide: true,
            destroy: true,
            colReorder: true,
            lengthMenu: [[5, 10, 50, 200, -1], [5, 10, 50, 200, "Todo"]],
            order: [[1, "DESC"]],
            buttons: [
                {
                    text: '<span class="glyphicon glyphicon-refresh"></span>',
                    action: function () {
                        tablaserver.ajax.reload(null, false);
                        tablaserver.columns.adjust();
                    }
                }
            ],
            ajax: function (data, callback) {
                data.sec_incidencias_list = true;
                data.red_id = $("#incidencias_redes").val();

                let start_date = $("#start_date").val();
                let end_date =  $("#end_date").val();
                //2023-04-15T00:00:00
                let d1 = start_date + 'T00:00:00';
                let d2 = end_date + 'T23:59:59';

                if (new Date(d1) > new Date(d2)) {
                    swal({
                        title: "Incidencia",
                        text: "La Fecha de Inicio no puede ser mayor a la Fecha Final.",
                        type: "warning",
                        timer: 5000,
                        closeOnConfirm: true
                    },
                        function () {
                            swal.close();
                        });
                    return;
                }

                data.start_date = start_date;
                data.end_date = end_date;
                //data.agente_id = $("#agente_select").val();
                if (typeof ajax_dt != "undefined") {
                    ajax_dt.abort();
                }
                ajax_dt = $.ajax({
                    global: false,
                    url: "/sys/set_incidencias.php",
                    type: 'POST',
                    data,
                    beforeSend: function () {
                        loading(true);
                        tablaserver.columns.adjust();
                    },
                    complete: function () {
                        tablaserver.columns.adjust();
                        loading();
                        //responsive_tabla_scroll(tablaserver);
                    },
                    success: function (response) {
                        dataIncidenceAjax = JSON.parse(response)["aaData"];
                        callback(JSON.parse(response));
                        $(window).trigger('resize');
                    },
                    error: function () {
                    }
                });
            },
            columns: [
                {data: "id", name: "id", title: "Id",
                    render: function (data, type, row) {
                        let inc_id = row["id"];
                        return `
                            <p style="text-align: center; margin: 0;">
                                ${data}</br>
                                <i role="button" id="mostrar_detalle_incidencia" data-id="${inc_id}" title="Ver Incidencia" class="icon icon-inline fa fa-fw fa-eye"></i>
                            </p>
                        `;
                    }
                },
                {data: "created_at", name: "inci.created_at", title: "Fecha y Hora"},
                {
                    data: "usuario", name: "usuario", title: "Usuario"
                    , render: function (data, type, row) {
                        let html = data;
                        let color = "";
                        if ((row["usuario_area"] == 21 && row["usuario_cargo"] == 4)  /*operaciones supervisor*/
                            || (row["usuario_area"] == 28 && row["usuario_cargo"] == 4)  /*agentes supervisor*/
                        ) {
                            color = "orange";
                        }
                        ;
                        if ((row["usuario_area"] == 28 && row["usuario_cargo"] == 16) /*agentes jefe*/
                            || (row["usuario_area"] == 21 && row["usuario_cargo"] == 16) /*operaciones jefe*/
                        ) {
                            color = "yellow";
                        }
                        ;

                        if (color != "") {
                            html = "<div style='background-color:" + color + ";font-weight:bold;padding:1px'>" + data + "</div>";
                        }
                        return html;
                    }
                },
                {data: "local", name: "local", title: "Tienda"},
                {data: "red", name: "red", title: "Red"},
                {data: "phone", name: "phone", title: "Telf. Tienda", defaultContent: "---"},
                {data: "telefono2", name: "telefono2", title: "Teléfono 2", defaultContent: "---"},
                {
                    data: "producto", name: "producto", title: "Prod"
                    , render: function (data, type, row) {
                        let abrev = data;
                        switch (data) {
                            case "Apuestas Deportivas":
                                abrev = "AD";
                                break;
                            case "Juegos Virtuales":
                                abrev = "JV";
                                break;
                        }
                        return abrev;
                    }
                },
                {
                    data: "tipo", name: "tipo", title: "Tipo"
                    , render: function (data, type, row) {
                        let abrev = data;
                        switch (data) {
                            case "Reimpresión":
                                abrev = "Rp";
                                break;
                            case "Caja":
                                abrev = "Cj";
                                break;
                            case "Terminal":
                                abrev = "Ter";
                                break;
                        }
                        return abrev;
                    }
                },
                {
                    data: "reimpresion", name: "reimpresion", title: "Reimpresión", defaultContent: "---"
                },
                {data: "teamviewer_id", name: "teamviewer_id", title: "ID Teamviewer", defaultContent: "---"},
                {
                    data: "teamviewer_password",
                    name: "teamviewer_password",
                    title: "Contraseña <br>Teamviewer",
                    defaultContent: "---"
                },
                {
                    data: "incidencia_txt",
                    name: "incidencia_txt",
                    title: " Incidencia &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",
                },
                {
                    data: 'EstadoCol',
                    name: 'estado',
                    className: 'text-center',
                    title: "Estado",
                    render: function (data, type, row) {
                        let button_color = 'btn-success';
                        let button_text = ' Solucionar';
                        let button_class = 'btn-solucionar';
                        let text_color = '';
                        let is_owner;
                        let agente_asignado_id;
                        if (row.agente_reasignado !== null) {
                            is_owner = parseInt(row.agente_reasignado_id) === parseInt(row.login_id);
                            agente_asignado_id = row.agente_reasignado_id;
                        } else {
                            is_owner = parseInt(row.agente_id) === parseInt(row.login_id);
                            agente_asignado_id = row.agente_id;
                        }
                        if (!is_owner) {
                            button_class = 'btn-reasignar';
                            button_color = 'btn-warning';
                            button_text = ' Reasignar';
                            text_color = 'text-dark';
                        }
                        let inc_id = row['id'];
                        let html = data;
                        let estado = parseInt(row.estado);
                        if (estado === 2) {
                            html = '<button class="btn btn-sm ' + text_color + ' ' + button_color + ' ' + button_class + '" data-id="' + inc_id + '" data-login-id="' + row.login_id + '" data-agente-asignado-id="' + agente_asignado_id + '"><span class="glyphicon glyphicon-ok-circle"></span>' + button_text + '</button>';
                        } else if (estado === 1) {
                            if (parseInt(row.puede_reabrir) === 1) {
                                html = '<button class="btn btn-sm btn-reabrir" style="background-color:#6c757d; color:white" data-id="' + inc_id + '" data-login-id="' + row.login_id + '"><span class="glyphicon glyphicon-ok-circle"></span> Reabrir </button>';
                            }
                        }
                        return html;
                    }
                },
                {data: "estado_servicio_tecnico", name: "estado_servicio_tecnico", title: "Estado Serv. Téc."},
                {data: "fecha_asignada", name: "fecha_asignada", title: "Fecha Asignada"},
                {
                    data: "agente", name: "agente", className: "text-center", title: "Agente"
                    , "render": function (data, type, row) {
                        let inc_id = row["id"];
                        let estado = parseInt(row["estado"]);
                        let assigned = parseInt(row["assigned"]);
                        let html = data;
                        if (estado === 0) {
                            html = `<div class='btn btn-sm btn-default text-warning btn-yolohago' data-id="${inc_id}" data-assigned="${assigned}"><span class='glyphicon glyphicon-ok-circle'></span> Yo lo hago</div>`;
                        }
                        return html;
                    }
                },
                {data: "agente2", name: "agente2", title: "Agente 2"},
                //{data: "agente_reasignado", name: "agente_reasignado", title: "Agente 3"},
                {data: "fecha_solucion", name: "fecha_solucion", title: "Fecha Solución"},
                {data: "recomendacion", name: "recomendacion", title: "Recomendación"},
                {data: "solucion_txt", name: "solucion_txt", title: "Observación"},
                {data: "satisfaccion", name: "satisfaccion", title: "Satisfacción"},
                {data: "tipo_incidencia", name: "tipo_incidencia", title: "Tipo Inc"},
                {data: "detalle_incidencia", name: "detalle_incidencia", title: "Detalle Inc"}
            ],
            initComplete: function (settings, json) {
                search_delay(settings, json);
                setTimeout(function () {
                    $("#incidencias_recargar").off("click").on("click", function () {
                        tablaserver.ajax.reload(null, false);
                        tablaserver.columns.adjust();
                    })

                    //$('.dataTables_scrollHeadInner').css('width', '100%');
                    //$('.dataTables_scrollHeadInner table').css('width', '100%');
                    // agregar_scrolltop(tablaserver);
                    //responsive_tabla_scroll(tablaserver);
                }, 100)
                ////show/hide colummns modal
                mostrar_ocultar_columnas(settings, json);
                // 0 => Nuevo, 1 => Atendido, 2 => Asignado
                filtrar_estado_datatable(settings, json);
                //filtrar_agente_datatable(settings, json)
                tablaserver.columns.adjust();
            },
            fnServerParams: function(data) {
                data['order'].forEach(function(items, index) {
                    data['order'][index]['column_name'] = data['columns'][items.column]['data'];
                });
            }
        });
    return tablaserver;
}

function search_delay(settings, json) {
    //searchdelay
    var datatable = settings.oInstance.api();
    var searchWait = 0;
    var searchWaitInterval;
    $('.dataTables_filter input')
        .unbind() // leave empty here
        .bind('input', function (e) { //leave input
            var item = $(this);
            searchWait = 0;
            if (!searchWaitInterval) searchWaitInterval = setInterval(function () {
                if (searchWait >= 3) {
                    clearInterval(searchWaitInterval);
                    searchWaitInterval = '';
                    searchTerm = $(item).val();
                    datatable.search(searchTerm).draw(); // change to new api
                    searchWait = 0;
                }
                searchWait++;
            }, 200);
        });
}

function filtrar_estado_datatable(settings, json) {
    let localStorage_estado_var = "estado_select_sec_incidencias";
    let datatable = settings.oInstance.api();

    let $estado_select = $("#estado_select");

    $estado_select.off("change").on("change", function () {
        let val = $(this).val();
        datatable.column(12).search(val).draw();
        datatable.columns.adjust();
        localStorage.setItem(localStorage_estado_var, val);
    });

    $estado_select.select2();

    if (localStorage.getItem(localStorage_estado_var) && localStorage.getItem(localStorage_estado_var) !== null) {
        setTimeout(function () {
            let valor = localStorage.getItem(localStorage_estado_var).split(',');
            $("#estado_select").val(valor).change();
        }, 200);
    } else {
        setTimeout(function () {
            $("#estado_select").val([0, 2]).change();//nuevos,asignados
        }, 200);
    }
}

function filtrar_agente_datatable(settings, json) {
    let localStorage_agente_var = "agente_select_sec_incidencias";
    let datatable = settings.oInstance.api();

    let $agente_select = $("#agente_select");

    $agente_select.off("change").on("change", function () {
        let val = $(this).val();
        datatable.column(14).search(val).draw();
        datatable.columns.adjust();
        localStorage.setItem(localStorage_agente_var, val);
    });

    $agente_select.select2();

    if (localStorage.getItem(localStorage_agente_var) && localStorage.getItem(localStorage_agente_var) !== null) {
        setTimeout(function () {
            let valor = localStorage.getItem(localStorage_agente_var).split(',');
            $("#agente_select").val(valor).change();
        }, 200);
    } else {
        setTimeout(function () {
            $("#agente_select").val('current_user').change();
        }, 200);
    }
}

function mostrar_ocultar_columnas(settings, json) {
    let modal = $("#filter_columnas_modal");
    let localStorage_var = "columnas_visibles_incidencias";
    let datatable = settings.oInstance.api();
    let col_visibles_array = [];
    let col_visibles = localStorage.getItem(localStorage_var);
    if (col_visibles != null) {
        col_visibles_array = JSON.parse(col_visibles);
    }

    $("#col_select_list", modal).empty();

    $(datatable.init().columns).each(function (i, e) {
        let column = datatable.column(i);

        if (!column) {
            return;
        }

        if (column.visible() === false) {
            return;
        }

        if (e.title === "") {
            return;
        }

        let chequeado = "checked";
        if (col_visibles) {
            datatable.column(i).visible(false);
            chequeado = "";
            $(col_visibles_array).each(function (ii, ee) {
                if (ee.i === i) {
                    chequeado = "checked";
                    datatable.column(i).visible(true);
                }
            })
        }
        var title = "";
        try {
            title = $(e.title).text() === "" ? e.title : $(e.title).text();
        } catch {
            title = e.title;
        }
        var li_html = "<li class='checkbox visible_input'>";
        li_html += "<label>";
        li_html += "<input type='checkbox' " + chequeado + " value=" + i + " name='" + title + "'>";
        li_html += title;
        li_html += "</label>";
        li_html += "</li>";
        $("#col_select_list", modal).append(li_html);
    });
    $("#col_select_list :checkbox", modal).off("change").on("change", function () {
        var index_column = $(this).attr("value");
        if ($(this).prop("checked")) {
            datatable.column(index_column).visible(true);
        } else {
            datatable.column(index_column).visible(false);
        }

        let visibles_array = [];
        $("#col_select_list :checkbox:checked", modal).each(function (i, e) {
            visibles_array.push({i: parseInt($(e).val()), title: $(e).attr("name")});
        })
        localStorage.setItem(localStorage_var, JSON.stringify(visibles_array));

    });
}

function render_agentes_list(data) {
    if (data.length) {
        let $ul = $("#sec_incidencias_list_agentes");
        for (const index in data) {
            let agente = data[index];
            let $li = $('<li>').css({
                padding: '5px',
                id: 'sec_incidencias_list_item_agente_' + +agente.id,
                name: 'sec_incidencias_list_item_agent'

            });
            let $radio = $('<input>').attr({
                type: 'radio',
                name: 'sec_incidencias_input_radio_agente',
                id: 'sec_incidencias_input_radio_agente_' + agente.id
            }).val(agente.id);
            let $label = $('<label>').attr({
                class: 'radio-inline',
                name: 'sec_incidencias_label_agente'
            });
            $label.append($radio);
            $label.append($.trim(agente.nombre_completo) || agente.usuario);
            $li.append($label);
            $ul.append($li);
        }
    }
}

function filtrar_agentes_reasignar() {
    // Declare variables
    let input, filter, ul, lis, label, i, txtValue;
    input = document.getElementById('sec_incidencias_input_filtrar_agentes');
    filter = input.value.toUpperCase();
    ul = document.getElementById("sec_incidencias_list_agentes");
    lis = ul.getElementsByTagName('li');
    // Loop through all list items, and hide those who don't match the search query
    for (i = 0; i < lis.length; i++) {
        label = lis[i].getElementsByTagName("label")[0];
        txtValue = label.textContent || label.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            lis[i].style.display = "";
        } else {
            lis[i].style.display = "none";
        }
    }
}

let datosOriginales = [];

function restaurarDatosOriginales() {
    $("#sec_incidencias_list_agentes li").show();
}

function reasignar_agente_seleccionado(incidencia_id, usuario_id) {
    $.ajax({
        url: '/sys/set_incidencias.php',
        type: 'POST',
        data: {
            set_incidencias_reasignar_seleccionado: true,
            incidencia_id,
            usuario_id
        },
        beforeSend: function () {
            loading(true);
        },
        success: function (response) {
            response = jQuery.parseJSON(response);
            loading(false);
            if (response.error) {
                auditoria_send({
                    "proceso": "sec_incidencias_reasignar_agente_seleccionado", "data": {
                        error_msg: response.msg
                    }
                });
                swal({
                    title: "¡Error!",
                    text: response.msg,
                    type: response.swal_type,
                    timer: 3000,
                    closeOnConfirm: true
                },
                    function () {
                        swal.close();
                    });
            } else {
                let tbl_incidencias = $('#tbl_incidencias');
                tbl_incidencias.DataTable().ajax.reload();
                tbl_incidencias.DataTable().columns.adjust();
            }
        },
        complete: function () {
            loading(false);
        }
    });
}

function check_incidencia_agente_puede_solucionar(response) {
    let puede_solucionar = null;
    try {
        let obj = jQuery.parseJSON(response);
        let estado = parseInt(obj.estado);
        if (estado === 2) {
            if (obj.agente_reasignado_id !== null && parseInt(obj.agente_reasignado_id) === parseInt(obj.login_id)) {
                puede_solucionar = true;
            } else if (obj.agente_reasignado_id === null && parseInt(obj.agente_1_id) === parseInt(obj.login_id)) {
                puede_solucionar = true;
            } else {
                puede_solucionar = false;
            }
        } else {
            swal({
                title: "No Asignado",
                text: "El caso aún no ha sido asignado.",
                type: "warning",
                timer: 3000,
                closeOnConfirm: true
            },
                function () {
                    swal.close();
                });
        }
    } catch (e) {
    }
    return puede_solucionar;
}

function set_incidencias_agente_asignado(incidencia_id) {
    return $.post('/sys/set_incidencias.php', {
        "set_incidencias_agente_asignado": incidencia_id
    }
    );
}

