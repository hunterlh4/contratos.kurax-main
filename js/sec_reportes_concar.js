function fnc_sec_reportes_concar_inicializar() {

    $.validator.prototype.checkForm = function () {
        //overriden in a specific page
        this.prepareForm();
        for (
            let i = 0, elements = (this.currentElements = this.elements());
            elements[i];
            i++
        ) {
            if (
                this.findByName(elements[i].name).length !== undefined &&
                this.findByName(elements[i].name).length > 1
            ) {
                for (
                    let cnt = 0;
                    cnt < this.findByName(elements[i].name).length;
                    cnt++
                ) {
                    this.check(this.findByName(elements[i].name)[cnt]);
                }
            } else {
                this.check(elements[i]);
            }
        }
        return this.valid();
    };

    $.validator.addMethod(
        "greaterThan",
        function (value, element, params) {
            let date = moment(value, params[2]).toDate(); //new Date(value);
            let compare_date = moment($(params[0]).val(), params[2]).toDate();
            if (!/Invalid|NaN/.test(date)) {
                return date > compare_date;
            }
            return (
                (isNaN(value) && isNaN($(params[0]).val())) ||
                Number(value) > Number($(params[0]).val())
            );
        },
        "Debe ser mayor que '{1}'."
    );

    $.validator.addMethod(
        "greaterThanOrEqual",
        function (value, element, params) {
            let date = moment(value, params[2]).toDate(); //new Date(value);
            let compare_date = moment($(params[0]).val(), params[2]).toDate();
            if (!/Invalid|NaN/.test(date)) {
                return date >= compare_date;
            }
            return (
                (isNaN(value) && isNaN($(params[0]).val())) ||
                Number(value) >= Number($(params[0]).val())
            );
        },
        "Debe ser mayor o igual que '{1}'."
    );

    $.validator.addMethod(
        "positiveNumber",
        function (value) {
            return Number(value) > 0;
        },
        "Enter a positive number."
    );

    $.validator.addMethod(
        "decimal",
        function (value, element) {
            return (
                this.optional(element) ||
                /^((\d+(\\.\d{0,2})?)|((\d*(\.\d{1,2}))))$/.test(value)
            );
        },
        "Please enter a correct number, format 0.00"
    );

    $.validator.addMethod(
        "exactlength",
        function (value, element, param) {
            return this.optional(element) || value.length === param;
        },
        $.validator.format("Porfavor ingrese exactamente {0} carácteres.")
    );

    $(document).on("show.bs.modal", ".modal", function () {
        const zIndex = 1040 + 10 * $(".modal:visible").length;
        $(this).css("z-index", zIndex);
        setTimeout(() =>
            $(".modal-backdrop")
                .not(".modal-stack")
                .css("z-index", zIndex - 1)
                .addClass("modal-stack")
        );
    });

    let dropdownMenu;

    $(window).on("show.bs.dropdown", function (e) {
        // grab the menu
        dropdownMenu = $(e.target).find(".dropdown-menu");

        // detach it and apientes it to the body
        $("body").append(dropdownMenu.detach());

        // grab the new offset position
        let eOffset = $(e.target).offset();

        // make sure to place it where it would normally go (this could be improved)
        dropdownMenu.css({
            display: "block",
            top: eOffset.top + $(e.target).outerHeight(),
            left: eOffset.left,
        });
    });

    $(window).on("hide.bs.dropdown", function (e) {
        $(e.target).append(dropdownMenu.detach());
        dropdownMenu.hide();
    });

    let hidden_columns = [
        "id",
        "created_at",
        "updated_at",
        "id",
        "archivo_proveedor_id",
        "proveedor_id",
    ];

    fnc_sec_reportes_concar_init_datetimepickers();

    fnc_sec_reportes_concar_obtener_nombre_proveedores().done(function (
        response
    ) {
        if (response) {
            for (let id in response) {
                let nombre_proveedor = response[id];
                $("#sec-reportes-concar-sel-proveedor-importar-archivo").append(
                    new Option(nombre_proveedor, id, false, false)
                );
                $("#sec-reportes-concar-sel-proveedor-exportar-concar").append(
                    new Option(nombre_proveedor, id, false, false)
                );
            }
        }
    });

    fnc_sec_reportes_concar_obtener_y_renderizar_tabla_archivos_proveedor_maestro();

    fnc_sec_reportes_concar_agregar_reglas_validacion_formularios();

    $("#sec-reportes-concar-sel-proveedor-importar-archivo").focus();

    $("#sec-reportes-concar-mdl-archivos-proveedor-detalle").on(
        "shown.bs.modal",
        function () {
            let table_id = "#sec-reportes-concar-tbl-archivos-proveedor-detalle";
            if ($.fn.DataTable.isDataTable(table_id)) {
                $(table_id).DataTable().columns.adjust().draw();
            }
        }
    );

    $("#sec-reportes-concar-mdl-exportar-archivo-concar-2").on(
        "shown.bs.modal",
        function () {
            $("#sec-reportes-concar-txt-fecha-comprobante").focus();
        }
    );

    $("#sec-reportes-concar-mdl-exportar-archivo-concar-2").on(
        "hide.bs.modal",
        function () {
            reset_form("#sec-reportes-concar-frm-exportar-archivo-concar-2");
            $("#sec-reportes-concar-fg-numero-documento").css("display", "block");
        }
    );

    $("#sec-reportes-concar-mdl-centros-costo").on("shown.bs.modal", function () {
        let table_id = "#sec-reportes-concar-tbl-centros-costo";
        if ($.fn.DataTable.isDataTable(table_id)) {
            $(table_id).DataTable().columns.adjust().draw();
        }
    });

    $("#sec-reportes-concar-mdl-centros-costo").on("hide.bs.modal", function () {
        $(
            "#sec-reportes-concar-frm-editar-centro-costo [name='sec-reportes-concar-txt-archivo-proveedor-id']"
        ).val("");
        $(
            "#sec-reportes-concar-frm-editar-centro-costo [name='sec-reportes-concar-txt-proveedor-id']"
        ).val("");
    });

    $("#sec-reportes-concar-mdl-editar-centro-costo").on(
        "shown.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-centro-costo";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-local']").focus().select();
        }
    );

    $("#sec-reportes-concar-mdl-editar-centro-costo").on(
        "hide.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-centro-costo";
            reset_form(form_id);
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("");
        }
    );

    $("#sec-reportes-concar-mdl-numeros-cuenta").on(
        "shown.bs.modal",
        function () {
            let table_id = "#sec-reportes-concar-tbl-numeros-cuenta";
            if ($.fn.DataTable.isDataTable(table_id)) {
                $(table_id).DataTable().columns.adjust().draw();
            }
        }
    );

    $("#sec-reportes-concar-mdl-numeros-cuenta").on("hide.bs.modal", function () {
        $(
            "#sec-reportes-concar-frm-editar-numero-cuenta [name='sec-reportes-concar-txt-archivo-proveedor-id']"
        ).val("");
    });

    $("#sec-reportes-concar-mdl-editar-numero-cuenta").on(
        "shown.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-numero-cuenta";
            let form = $(form_id);
            let txt_nro_cuenta = form.find("[name='sec-reportes-concar-txt-nro-cuenta']");
            let nro_cuenta = txt_nro_cuenta.val().trim();
            let sel_ceco = form.find("[name='sec-reportes-concar-sel-ceco']");
            let sel_ceco_data = sel_ceco.select2("data");
            if (nro_cuenta === "") {
                txt_nro_cuenta.focus().select();
            } else if (sel_ceco_data.length && sel_ceco_data[0].id === '') {
                sel_ceco.select2("open");
            } else {
                txt_nro_cuenta.focus().select();
            }
        }
    );

    $("#sec-reportes-concar-mdl-editar-numero-cuenta").on(
        "hide.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-numero-cuenta";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("");
            form
                .find("[name='sec-reportes-concar-txt-nro-cuenta']")
                .attr("readonly", false);
            form
                .find("[name='sec-reportes-concar-sel-ceco']")
                .val("")
                .trigger("change");
            reset_form(form_id);
        }
    );

    $("#sec-reportes-concar-mdl-numeros-abonado").on("shown.bs.modal", function () {
        let table_id = "#sec-reportes-concar-tbl-numeros-abonado";
        if ($.fn.DataTable.isDataTable(table_id)) {
            $(table_id).DataTable().columns.adjust().draw();
        }
    });

    $("#sec-reportes-concar-mdl-numeros-abonado").on("hide.bs.modal", function () {
        $(
            "#sec-reportes-concar-frm-editar-numero-abonado [name='sec-reportes-concar-txt-archivo-proveedor-id']"
        ).val("");
    });

    $("#sec-reportes-concar-mdl-editar-numero-abonado").on(
        "shown.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-numero-abonado";
            let form = $(form_id);
            let txt_nro_abonado = form.find("[name='sec-reportes-concar-txt-nro-abonado']");
            let nro_abonado = txt_nro_abonado.val().trim();
            let sel_ceco = form.find("[name='sec-reportes-concar-sel-ceco']");
            let sel_ceco_data = sel_ceco.select2("data");
            if (nro_abonado === "") {
                txt_nro_abonado.focus().select();
            } else if (sel_ceco_data.length && sel_ceco_data[0].id === '') {
                sel_ceco.select2("open");
            } else {
                txt_nro_abonado.focus().select();
            }
        }
    );

    $("#sec-reportes-concar-mdl-editar-numero-abonado").on(
        "hide.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-numero-abonado";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("");
            form
                .find("[name='sec-reportes-concar-txt-nro-abonado']")
                .attr("readonly", false);
            form
                .find("[name='sec-reportes-concar-sel-ceco']")
                .val("")
                .trigger("change");
            reset_form(form_id);
        }
    );

    $("#sec-reportes-concar-mdl-codigos-pago").on("shown.bs.modal", function () {
        let table_id = "#sec-reportes-concar-tbl-codigos-pago";
        if ($.fn.DataTable.isDataTable(table_id)) {
            $(table_id).DataTable().columns.adjust().draw();
        }
    });

    $("#sec-reportes-concar-mdl-codigos-pago").on("hide.bs.modal", function () {
        $(
            "#sec-reportes-concar-frm-editar-codigo-pago [name='sec-reportes-concar-txt-archivo-proveedor-id']"
        ).val("");
    });

    $("#sec-reportes-concar-mdl-editar-codigo-pago").on(
        "shown.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-codigo-pago";
            let form = $(form_id);
            let txt_cod_pago = form.find("[name='sec-reportes-concar-txt-cod-pago']");
            let cod_pago = txt_cod_pago.val().trim();
            let sel_ceco = form.find("[name='sec-reportes-concar-sel-ceco']");
            let sel_ceco_data = sel_ceco.select2("data");
            if (cod_pago === "") {
                txt_cod_pago.focus();
            } else if (sel_ceco_data.length && sel_ceco_data[0].id === '') {
                sel_ceco.select2("open");
            } else {
                txt_cod_pago.focus().select();
            }
        }
    );

    $("#sec-reportes-concar-mdl-editar-codigo-pago").on(
        "hide.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-codigo-pago";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("");
            form
                .find("[name='sec-reportes-concar-txt-cod-pago']")
                .attr("readonly", false);
            form
                .find("[name='sec-reportes-concar-sel-ceco']")
                .val("")
                .trigger("change");
            reset_form(form_id);
        }
    );

    $("#sec-reportes-concar-mdl-cuentas-contables").on(
        "shown.bs.modal",
        function () {
            let table_id = "#sec-reportes-concar-tbl-cuentas-contables";
            if ($.fn.DataTable.isDataTable(table_id)) {
                $(table_id).DataTable().columns.adjust().draw();
            }
        }
    );

    $("#sec-reportes-concar-mdl-cuentas-contables").on(
        "hide.bs.modal",
        function () {
        }
    );

    $("#sec-reportes-concar-mdl-editar-cuenta-contable").on(
        "shown.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-cuenta-contable";
            let form = $(form_id);
            form
                .find("[name='sec-reportes-concar-txt-cta-contable']")
                .focus()
                .select();
        }
    );

    $("#sec-reportes-concar-mdl-editar-cuenta-contable").on(
        "hide.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-cuenta-contable";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("");
            reset_form(form_id);
        }
    );

    $("#sec-reportes-concar-mdl-proveedores").on("shown.bs.modal", function () {
        let table_id = "#sec-reportes-concar-tbl-proveedores";
        if ($.fn.DataTable.isDataTable(table_id)) {
            $(table_id).DataTable().columns.adjust().draw();
        }
    });

    $("#sec-reportes-concar-mdl-proveedores").on("hide.bs.modal", function () {
    });

    $("#sec-reportes-concar-mdl-editar-proveedor").on(
        "shown.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-proveedor";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-nombre']").focus().select();
        }
    );

    $("#sec-reportes-concar-mdl-editar-proveedor").on(
        "hide.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-proveedor";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("");
            reset_form(form_id);
        }
    );

    $("#sec-reportes-concar-mdl-conceptos-facturables").on(
        "shown.bs.modal",
        function () {
            let table_id = "#sec-reportes-concar-tbl-conceptos-facturables";
            if ($.fn.DataTable.isDataTable(table_id)) {
                $(table_id).DataTable().columns.adjust().draw();
            }
        }
    );

    $("#sec-reportes-concar-mdl-conceptos-facturables").on(
        "hide.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-concepto-facturable";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-archivo-proveedor-id']").val("");
        }
    );

    $("#sec-reportes-concar-mdl-editar-concepto-facturable").on(
        "shown.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-concepto-facturable";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-concepto']").focus().select();
        }
    );

    $("#sec-reportes-concar-mdl-editar-concepto-facturable").on(
        "hide.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-concepto-facturable";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("");
            form.find("[name='sec-reportes-concar-txt-concepto']").attr("readonly", false);
            form.find("[name='sec-reportes-concar-sel-cta-contable']")
                .val("")
                .trigger("change");
            reset_form(form_id);


        }
    );

    $("#sec-reportes-concar-mdl-codigos-comercio").on(
        "shown.bs.modal",
        function () {
            let table_id = "#sec-reportes-concar-tbl-codigos-comercio";
            if ($.fn.DataTable.isDataTable(table_id)) {
                $(table_id).DataTable().columns.adjust().draw();
            }
        }
    );

    $("#sec-reportes-concar-mdl-codigos-comercio").on(
        "hide.bs.modal",
        function () {
            $(
                "#sec-reportes-concar-frm-editar-codigo-comercio [name='sec-reportes-concar-txt-archivo-proveedor-id']"
            ).val("");
        }
    );

    $("#sec-reportes-concar-mdl-editar-codigo-comercio").on(
        "shown.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-codigo-comercio";
            let form = $(form_id);
            let txt_cod_comercio = form.find("[name='sec-reportes-concar-txt-cod-comercio']");
            let cod_comercio = txt_cod_comercio.val().trim();
            let sel_ceco = form.find("[name='sec-reportes-concar-sel-ceco']");
            let sel_ceco_data = sel_ceco.select2("data");
            if (cod_comercio === "") {
                txt_cod_comercio.focus();
            } else if (sel_ceco_data.length && sel_ceco_data[0].id === "") {
                sel_ceco.select2("open");
            } else {
                txt_cod_comercio.focus().select();
            }
        }
    );

    $("#sec-reportes-concar-mdl-editar-codigo-comercio").on(
        "hide.bs.modal",
        function () {
            let form_id = "#sec-reportes-concar-frm-editar-codigo-comercio";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("");
            form
                .find("[name='sec-reportes-concar-txt-cod-comercio']")
                .attr("readonly", false);
            form
                .find("[name='sec-reportes-concar-sel-ceco']")
                .val("")
                .trigger("change");
            reset_form(form_id);
        }
    );

    $("#sec-reportes-concar-mdl-bancos").on("shown.bs.modal", function () {
        let table_id = "#sec-reportes-concar-tbl-bancos";
        if ($.fn.DataTable.isDataTable(table_id)) {
            $(table_id).DataTable().columns.adjust().draw();
        }
    });

    $("#sec-reportes-concar-mdl-bancos").on("hide.bs.modal", function () {
    });

    $("#sec-reportes-concar-mdl-editar-banco").on("shown.bs.modal", function () {
        let form_id = "#sec-reportes-concar-frm-editar-banco";
        let form = $(form_id);
        form.find("[name='sec-reportes-concar-txt-nombre']").focus().select();
    });

    $("#sec-reportes-concar-mdl-editar-banco").on("hide.bs.modal", function () {
        let form_id = "#sec-reportes-concar-frm-editar-banco";
        reset_form(form_id);
        let form = $(form_id);
        form.find("[name='sec-reportes-concar-txt-id']").val("");
    });

    $("#sec-reportes-concar-mdl-detalle-bancos").on(
        "shown.bs.modal",
        function () {
            let table_id = "#sec-reportes-concar-tbl-detalle-bancos";
            if ($.fn.DataTable.isDataTable(table_id)) {
                $(table_id).DataTable().columns.adjust().draw();
            }
        }
    );

    $("#sec-reportes-concar-mdl-detalle-bancos").on("hide.bs.modal", function () {
        $("#sec-reportes-concar-frm-editar-detalle-bancos")
            .find("[name='sec-reportes-concar-txt-archivo-proveedor-id']")
            .val("");
        $("#sec-reportes-concar-btn-agregar-detalle-bancos").data(
            "archivo-proveedor-id",
            ""
        );
    });

    $(document).on(
        'click',
        '[id^="sec-reportes-concar-btn-mostrar-detalle-archivo-proveedor-id"]',
        function (event) {
            btn = event.target;
            archivo_proveedor_id = $(btn).data("id");
            fnc_sec_reportes_concar_obtener_archivos_proveedor_detalle(
                archivo_proveedor_id
            ).done(function (data) {
                if (data) {
                    let first_row = data[0];
                    let columns =
                        fnc_sec_reportes_concar_obtener_columns_archivos_proveedor_detalle(
                            first_row,
                            hidden_columns
                        );
                    fnc_sec_reportes_concar_renderizar_tabla_archivos_proveedor_detalle(
                        data,
                        columns
                    );
                    $("#sec-reportes-concar-mdl-archivos-proveedor-detalle").modal(
                        "show"
                    );
                } else {
                    swal("No Data", "No se ha recibido datos del documento.", "warning");
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-eliminar-detalle-archivo-proveedor-id"]',
        function (event) {
            let archivo_proveedor_id = $(event.target).data("id");
            alertify
                .confirm(
                    "Alerta de eliminación.",
                    "¿Está seguro de eliminar el archivo y sus datos?",
                    function () {
                        fnc_sec_reportes_concar_eliminar_archivo_proveedor(
                            archivo_proveedor_id
                        ).done(function (response) {
                            if (response.success) {
                                swal(
                                    {
                                        title: "Éxito",
                                        text: "El archivo fue eliminado exitosamente.",
                                        type: "success",
                                        timer: 3000,
                                        closeOnConfirm: true,
                                    },
                                    function () {
                                        swal.close();
                                        fnc_sec_reportes_concar_obtener_y_renderizar_tabla_archivos_proveedor_maestro();
                                    }
                                );
                            }
                        });
                    },
                    function () {
                        //alertify.error('Cancel');
                    }
                )
                .set({labels: {ok: "Aceptar", cancel: "Cancelar"}});
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-exportar-archivo-concar-por-archivo-proveedor-id"]',
        function (event) {
            let target = $(event.target);
            let archivo_proveedor_id = target.data("id");
            let proveedor_id = target.data("proveedor-id");
            let nombre_proveedor = target.data("nombre-proveedor");
            let archivo_proveedor_ids = {};
            archivo_proveedor_ids[proveedor_id] = [archivo_proveedor_id];

            let json_archivo_proveedor_ids = JSON.stringify([archivo_proveedor_ids]);

            if (nombre_proveedor === "movistar") {
                $("#sec-reportes-concar-fg-numero-documento").css("display", "none");
            }

            let date = $("#sec-reportes-concar-wrap-fecha-comprobante").data().date;
            let numero_mes = moment(date, "DD/MM/YYYY").format("MM");
            $("#sec-reportes-concar-txt-mes-comprobante").val(numero_mes);

            $("#sec-reportes-concar-txt-tipo-exportacion").val(
                "por-archivo-proveedor-ids"
            );
            $("#sec-reportes-concar-txt-archivo-proveedor-ids").val(
                json_archivo_proveedor_ids
            );
            $("#sec-reportes-concar-mdl-exportar-archivo-concar-2").modal("show");
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-centros-costo-pendientes"]',
        function (event) {
            let archivo_proveedor_id = $(event.target).data("id");
            let nombre_proveedor = $(event.target).data("nombre-proveedor");
            if (nombre_proveedor === "direc_tv") {
                fnc_sec_reportes_concar_obtener_numeros_cuenta_pendientes(
                    archivo_proveedor_id
                ).done(function (response) {
                    if (response && response.length) {
                        let form = $("#sec-reportes-concar-frm-editar-numero-cuenta");
                        form
                            .find("[name='sec-reportes-concar-txt-archivo-proveedor-id']")
                            .val(archivo_proveedor_id);
                        form
                            .find("[name='sec-reportes-concar-txt-nro-cuenta']")
                            .attr("readonly", true);
                        $("#sec-reportes-concar-mdl-numeros-cuenta").modal("show");
                        fnc_sec_reportes_concar_renderizar_tabla_numeros_cuenta(response);
                    } else {
                        alertify.warning(
                            "Los Números de Cuenta están correctamente editados."
                        );
                    }
                });
            } else if (nombre_proveedor === "movistar") {
                fnc_sec_reportes_concar_obtener_codigos_pago_pendientes(
                    archivo_proveedor_id
                ).done(function (response) {
                    if (response && response.length) {
                        let form = $("#sec-reportes-concar-frm-editar-codigo-pago");
                        form
                            .find("[name='sec-reportes-concar-txt-archivo-proveedor-id']")
                            .val(archivo_proveedor_id);
                        form
                            .find("[name='sec-reportes-concar-txt-cod-pago']")
                            .attr("readonly", true);
                        $("#sec-reportes-concar-mdl-codigos-pago").modal("show");
                        fnc_sec_reportes_concar_renderizar_tabla_codigos_pago(response);
                    } else {
                        alertify.warning(
                            "Los Códigos de Pago están correctamente editados."
                        );
                    }
                });
            } else if (nombre_proveedor === "niubiz") {
                fnc_sec_reportes_concar_obtener_codigos_comercio_pendientes(
                    archivo_proveedor_id
                ).done(function (response) {
                    if (response && response.length) {
                        let form = $("#sec-reportes-concar-frm-editar-codigo-comercio");
                        form
                            .find("[name='sec-reportes-concar-txt-archivo-proveedor-id']")
                            .val(archivo_proveedor_id);
                        form
                            .find("[name='sec-reportes-concar-txt-cod-comercio']")
                            .attr("readonly", true);
                        $("#sec-reportes-concar-mdl-codigos-comercio").modal("show");
                        fnc_sec_reportes_concar_renderizar_tabla_codigos_comercio(response);
                    } else {
                        alertify.warning(
                            "Los Códigos de Comercio están correctamente editados."
                        );
                    }
                });
            } else if (nombre_proveedor === "prosegur") {
                fnc_sec_reportes_concar_obtener_numeros_abonado_pendientes(
                    archivo_proveedor_id
                ).done(function (response) {
                    if (response && response.length) {
                        let form = $("#sec-reportes-concar-frm-editar-numero-abonado");
                        form
                            .find("[name='sec-reportes-concar-txt-archivo-proveedor-id']")
                            .val(archivo_proveedor_id);
                        form
                            .find("[name='sec-reportes-concar-txt-nro-abonado']")
                            .attr("readonly", true);
                        $("#sec-reportes-concar-mdl-numeros-abonado").modal("show");
                        fnc_sec_reportes_concar_renderizar_tabla_numeros_abonado(response);
                    } else {
                        alertify.warning(
                            "Los Números de Abonado están correctamente editados."
                        );
                    }
                });
            }
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-centros-costo"]',
        function (event) {
            fnc_sec_reportes_concar_obtener_centros_costo().done(function (response) {
                if (response && response.length) {
                    $("#sec-reportes-concar-mdl-centros-costo").modal("show");
                    fnc_sec_reportes_concar_renderizar_tabla_centros_costo(response);
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-numeros-cuenta"]',
        function (event) {
            fnc_sec_reportes_concar_obtener_numeros_cuenta().done(function (
                response
            ) {
                if (response && response.length) {
                    $("#sec-reportes-concar-mdl-numeros-cuenta").modal("show");
                    fnc_sec_reportes_concar_renderizar_tabla_numeros_cuenta(response);
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-codigos-pago"]',
        function (event) {
            fnc_sec_reportes_concar_obtener_codigos_pago().done(function (response) {
                if (response && response.length) {
                    $("#sec-reportes-concar-mdl-codigos-pago").modal("show");
                    fnc_sec_reportes_concar_renderizar_tabla_codigos_pago(response);
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-codigos-comercio"]',
        function (event) {
            fnc_sec_reportes_concar_obtener_codigos_comercio().done(function (
                response
            ) {
                if (response && response.length) {
                    $("#sec-reportes-concar-mdl-codigos-comercio").modal("show");
                    fnc_sec_reportes_concar_renderizar_tabla_codigos_comercio(response);
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-bancos"]',
        function (event) {
            fnc_sec_reportes_concar_obtener_bancos().done(function (response) {
                if (response && response.length) {
                    $("#sec-reportes-concar-mdl-bancos").modal("show");
                    fnc_sec_reportes_concar_renderizar_tabla_bancos(response);
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-numeros-abonado"]',
        function (event) {
            fnc_sec_reportes_concar_obtener_numeros_abonado().done(function (
                response
            ) {
                if (response && response.length) {
                    fnc_sec_reportes_concar_renderizar_tabla_numeros_abonado(response);
                    $("#sec-reportes-concar-mdl-numeros-abonado").modal("show");
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-editar-centro-costo"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-centro-costo").text(
                "Editar Centro de Costo"
            );
            let id = $(event.target).data("id");
            fnc_sec_reportes_concar_obtener_centro_costo(id).done(function (
                response
            ) {
                if (response) {
                    let form_id = "#sec-reportes-concar-frm-editar-centro-costo";
                    reset_form(form_id);
                    let form = $(form_id);
                    form.find("[name='sec-reportes-concar-txt-id']").val(response.id);
                    form
                        .find("[name='sec-reportes-concar-txt-local']")
                        .val(response.local); //
                    form
                        .find("[name='sec-reportes-concar-txt-descripcion']")
                        .val(response.descripcion);
                    form.find("[name='sec-reportes-concar-txt-ceco']").val(response.ceco);
                    form
                        .find("[name='sec-reportes-concar-txt-costo-mensual']")
                        .val(response.costo_mensual);
                    form
                        .find("[name='sec-reportes-concar-txt-observacion']")
                        .val(response.observacion);
                    let date = response.fecha_baja
                        ? moment(response.fecha_baja).format("DD/MM/YYYY")
                        : null;
                    form
                        .find("[name='sec-reportes-concar-txt-fecha-baja']")
                        .parent()
                        .data("DateTimePicker")
                        .date(date);
                    form
                        .find("[name='sec-reportes-concar-sel-estado']")
                        .val(response.estado);
                    $("#sec-reportes-concar-mdl-editar-centro-costo").modal("show");
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-eliminar-centro-costo"]',
        function (event) {
            let centro_costo_id = $(event.target).data("id");
            alertify
                .confirm(
                    "Alerta de eliminación.",
                    "¿Está seguro de eliminar el centro de costo?",
                    function () {
                        fnc_sec_reportes_concar_eliminar_centro_costo(centro_costo_id).done(
                            function () {
                                swal(
                                    {
                                        title: "Éxito",
                                        text: "La operación se realizó exitosamente.",
                                        type: "success",
                                        timer: 3000,
                                        closeOnConfirm: true,
                                    },
                                    function () {
                                        fnc_sec_reportes_concar_obtener_centros_costo().done(
                                            function (response) {
                                                fnc_sec_reportes_concar_renderizar_tabla_centros_costo(
                                                    response
                                                );
                                            }
                                        );
                                        swal.close();
                                    }
                                );
                            }
                        );
                    },
                    function () {
                        //alertify.error('Cancel');
                    }
                )
                .set({labels: {ok: "Aceptar", cancel: "Cancelar"}});
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-editar-numero-cuenta"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-numero-cuenta").text(
                "Editar Número de Cuenta"
            );
            let id = $(event.target).data("id");
            let nro_cuenta = $(event.target).data("numero-cuenta");
            fnc_sec_reportes_concar_obtener_numero_cuenta(id, nro_cuenta).done(
                function (response) {
                    if (response) {
                        fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_numero_cuenta().done(
                            function () {
                                let form_id = "#sec-reportes-concar-frm-editar-numero-cuenta";
                                reset_form(form_id);
                                let form = $(form_id);
                                form
                                    .find("[name='sec-reportes-concar-txt-id']")
                                    .val(response.id);
                                form
                                    .find("[name='sec-reportes-concar-txt-nro-cuenta']")
                                    .val(response.nro_cuenta);
                                form
                                    .find("[name='sec-reportes-concar-sel-ceco']")
                                    .val(response.ceco)
                                    .trigger("change");
                                $("#sec-reportes-concar-mdl-editar-numero-cuenta").modal(
                                    "show"
                                );
                            }
                        );
                    }
                }
            );
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-eliminar-numero-cuenta"]',
        function (event) {
            let numero_cuenta_id = $(event.target).data("id");
            alertify
                .confirm(
                    "Alerta de eliminación.",
                    "¿Está seguro de eliminar el número de cuenta?",
                    function () {
                        fnc_sec_reportes_concar_eliminar_numero_cuenta(
                            numero_cuenta_id
                        ).done(function () {
                            swal(
                                {
                                    title: "Éxito",
                                    text: "La operación se realizó exitosamente.",
                                    type: "success",
                                    timer: 3000,
                                    closeOnConfirm: true,
                                },
                                function () {
                                    fnc_sec_reportes_concar_obtener_numeros_cuenta().done(
                                        function (response) {
                                            fnc_sec_reportes_concar_renderizar_tabla_numeros_cuenta(
                                                response
                                            );
                                        }
                                    );
                                    swal.close();
                                }
                            );
                        });
                    },
                    function () {
                        //alertify.error('Cancel');
                    }
                )
                .set({labels: {ok: "Aceptar", cancel: "Cancelar"}});
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-eliminar-codigo-pago"]',
        function (event) {
            let codigo_pago_id = $(event.target).data("id");
            alertify
                .confirm(
                    "Alerta de eliminación.",
                    "¿Está seguro de eliminar el código de pago?",
                    function () {
                        fnc_sec_reportes_concar_eliminar_codigo_pago(codigo_pago_id).done(
                            function () {
                                swal(
                                    {
                                        title: "Éxito",
                                        text: "La operación se realizó exitosamente.",
                                        type: "success",
                                        timer: 3000,
                                        closeOnConfirm: true,
                                    },
                                    function () {
                                        fnc_sec_reportes_concar_obtener_codigos_pago().done(
                                            function (response) {
                                                fnc_sec_reportes_concar_renderizar_tabla_codigos_pago(
                                                    response
                                                );
                                            }
                                        );
                                        swal.close();
                                    }
                                );
                            }
                        );
                    },
                    function () {
                        //alertify.error('Cancel');
                    }
                )
                .set({labels: {ok: "Aceptar", cancel: "Cancelar"}});
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-editar-codigo-pago"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-codigo-pago").text(
                "Editar Código de Pago"
            );
            let id = $(event.target).data("id");
            let cod_pago = $(event.target).data("codigo-pago");
            fnc_sec_reportes_concar_obtener_codigo_pago(id, cod_pago).done(function (
                response
            ) {
                if (response) {
                    fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_codigo_pago().done(
                        function () {
                            let form_id = "#sec-reportes-concar-frm-editar-codigo-pago";
                            reset_form(form_id);
                            let form = $(form_id);
                            form.find("[name='sec-reportes-concar-txt-id']").val(response.id);
                            form
                                .find("[name='sec-reportes-concar-txt-cod-pago']")
                                .val(response.cod_pago);
                            form
                                .find("[name='sec-reportes-concar-sel-ceco']")
                                .val(response.ceco)
                                .trigger("change");
                            $("#sec-reportes-concar-mdl-editar-codigo-pago").modal("show");
                        }
                    );
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-nuevo-centro-costo"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-centro-costo").text(
                "Nuevo Centro de Costo"
            );
            let form_id = "#sec-reportes-concar-frm-editar-centro-costo";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("0");
            form.find("[name='sec-reportes-concar-sel-estado']").val("1");
            form
                .find("[name='sec-reportes-concar-txt-fecha-baja']")
                .parent()
                .data("DateTimePicker")
                .date(null);
            $("#sec-reportes-concar-mdl-editar-centro-costo").modal("show");
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-nuevo-numero-cuenta"]',
        function () {
            $("#sec-reportes-concar-mlb-editar-numero-cuenta").text(
                "Nuevo Número de Cuenta"
            );
            fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_numero_cuenta().done(
                function () {
                    let form_id = "#sec-reportes-concar-frm-editar-numero-cuenta";
                    let form = $(form_id);
                    form.find("[name='sec-reportes-concar-txt-id']").val("0");
                    $("#sec-reportes-concar-mdl-editar-numero-cuenta").modal("show");
                }
            );
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-nuevo-codigo-pago"]',
        function () {
            $("#sec-reportes-concar-mlb-editar-codigo-pago").text(
                "Nuevo Código de Pago"
            );
            fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_codigo_pago().done(
                function () {
                    let form_id = "#sec-reportes-concar-frm-editar-codigo-pago";
                    let form = $(form_id);
                    form.find("[name='sec-reportes-concar-txt-id']").val("0");
                    $("#sec-reportes-concar-mdl-editar-codigo-pago").modal("show");
                }
            );
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-cuentas-contables"]',
        function () {
            fnc_sec_reportes_concar_obtener_cuentas_contables().done(function (
                response
            ) {
                if (response && response.length) {
                    $("#sec-reportes-concar-mdl-cuentas-contables").modal("show");
                    fnc_sec_reportes_concar_renderizar_tabla_cuentas_contables(response);
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-conceptos-facturables"]',
        function (event) {
            fnc_sec_reportes_concar_obtener_conceptos_facturables().done(function (
                response
            ) {
                if (response && response.length) {
                    $("#sec-reportes-concar-mdl-conceptos-facturables").modal("show");
                    fnc_sec_reportes_concar_renderizar_tabla_conceptos_facturables(
                        response
                    );
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-editar-cuenta-contable"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-cuenta-contable").text(
                "Editar Cuenta Contable"
            );
            let id = $(event.target).data("id");
            fnc_sec_reportes_concar_obtener_cuenta_contable(id).done(function (
                response
            ) {
                if (response) {
                    let form_id = "#sec-reportes-concar-frm-editar-cuenta-contable";
                    reset_form(form_id);
                    let form = $(form_id);
                    form.find("[name='sec-reportes-concar-txt-id']").val(response.id);
                    form
                        .find("[name='sec-reportes-concar-txt-cta-contable']")
                        .val(response.cta_contable);
                    form
                        .find("[name='sec-reportes-concar-txt-concar']")
                        .val(response.concar);
                    $("#sec-reportes-concar-mdl-editar-cuenta-contable").modal("show");
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-nuevo-cuenta-contable"]',
        function () {
            $("#sec-reportes-concar-mlb-editar-cuenta-contable").text(
                "Nueva Cuenta Contable"
            );
            let form_id = "#sec-reportes-concar-frm-editar-cuenta-contable";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("0");
            $("#sec-reportes-concar-mdl-editar-cuenta-contable").modal("show");
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-eliminar-cuenta-contable"]',
        function (event) {
            let id = $(event.target).data("id");
            alertify
                .confirm(
                    "Alerta de eliminación.",
                    "¿Está seguro de eliminar la cuenta contable?",
                    function () {
                        fnc_sec_reportes_concar_eliminar_cuenta_contable(id).done(
                            function () {
                                swal(
                                    {
                                        title: "Éxito",
                                        text: "La operación se realizó exitosamente.",
                                        type: "success",
                                        timer: 3000,
                                        closeOnConfirm: true,
                                    },
                                    function () {
                                        fnc_sec_reportes_concar_obtener_cuentas_contables().done(
                                            function (response) {
                                                fnc_sec_reportes_concar_renderizar_tabla_cuentas_contables(
                                                    response
                                                );
                                            }
                                        );
                                        swal.close();
                                    }
                                );
                            }
                        );
                    },
                    function () {
                        //alertify.error('Cancel');
                    }
                )
                .set({labels: {ok: "Aceptar", cancel: "Cancelar"}});
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-proveedores"]',
        function (event) {
            fnc_sec_reportes_concar_obtener_proveedores().done(function (response) {
                if (response && response.length) {
                    $("#sec-reportes-concar-mdl-proveedores").modal("show");
                    fnc_sec_reportes_concar_renderizar_tabla_proveedores(response);
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-editar-proveedor"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-proveedor").text("Editar Proveedor");
            let id = $(event.target).data("id");
            fnc_sec_reportes_concar_obtener_proveedor(id).done(function (response) {
                if (response) {
                    let form_id = "#sec-reportes-concar-frm-editar-proveedor";
                    reset_form(form_id);
                    let form = $(form_id);
                    form.find("[name='sec-reportes-concar-txt-id']").val(response.id);
                    form
                        .find("[name='sec-reportes-concar-txt-nombre']")
                        .val(response.nombre);
                    form.find("[name='sec-reportes-concar-txt-ruc']").val(response.ruc);
                    $("#sec-reportes-concar-mdl-editar-proveedor").modal("show");
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-eliminar-proveedor"]',
        function (event) {
            let id = $(event.target).data("id");
            alertify
                .confirm(
                    "Alerta de eliminación.",
                    "¿Está seguro de eliminar el proveedor?",
                    function () {
                        fnc_sec_reportes_concar_eliminar_proveedor(id).done(function () {
                            swal(
                                {
                                    title: "Éxito",
                                    text: "La operación se realizó exitosamente.",
                                    type: "success",
                                    timer: 3000,
                                    closeOnConfirm: true,
                                },
                                function () {
                                    fnc_sec_reportes_concar_obtener_proveedores().done(function (
                                        response
                                    ) {
                                        fnc_sec_reportes_concar_renderizar_tabla_proveedores(
                                            response
                                        );
                                    });
                                    swal.close();
                                }
                            );
                        });
                    },
                    function () {
                        //alertify.error('Cancel');
                    }
                )
                .set({labels: {ok: "Aceptar", cancel: "Cancelar"}});
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-nuevo-proveedor"]',
        function () {
            $("#sec-reportes-concar-mlb-editar-proveedor").text("Nuevo Proveedor");
            let form_id = "#sec-reportes-concar-frm-editar-proveedor";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("0");
            $("#sec-reportes-concar-mdl-editar-proveedor").modal("show");
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-editar-concepto-facturable"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-concepto-facturable").text(
                "Editar Concepto Facturable"
            );
            let id = $(event.target).data("id");
            let concepto = $(event.target).data("concepto");
            fnc_sec_reportes_concar_obtener_concepto_facturable(id, concepto).done(function (
                response
            ) {
                if (response) {
                    fnc_sec_reportes_concar_refresh_select_cta_contable_from_frm_editar_concepto_facturable().done(
                        function () {
                            let form_id = "#sec-reportes-concar-frm-editar-concepto-facturable";
                            reset_form(form_id);
                            let form = $(form_id);
                            form.find("[name='sec-reportes-concar-txt-id']").val(response.id);
                            form
                                .find("[name='sec-reportes-concar-txt-concepto']")
                                .val(response.concepto);
                            form
                                .find("[name='sec-reportes-concar-sel-cta-contable']")
                                .val(response.cta_contable)
                                .trigger("change");
                            $("#sec-reportes-concar-mdl-editar-concepto-facturable").modal(
                                "show"
                            );
                        }
                    );
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-nuevo-concepto-facturable"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-concepto-facturable").text(
                "Nuevo Concepto Facturable"
            );
            fnc_sec_reportes_concar_refresh_select_cta_contable_from_frm_editar_concepto_facturable().done(
                function () {
                    let form_id = "#sec-reportes-concar-frm-editar-concepto-facturable";
                    let form = $(form_id);
                    form.find("[name='sec-reportes-concar-txt-id']").val("0");
                    $("#sec-reportes-concar-mdl-editar-concepto-facturable").modal(
                        "show"
                    );
                }
            );
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-eliminar-concepto-facturable"]',
        function (event) {
            let id = $(event.target).data("id");
            alertify
                .confirm(
                    "Alerta de eliminación.",
                    "¿Está seguro de eliminar el concepto facturable?",
                    function () {
                        fnc_sec_reportes_concar_eliminar_concepto_facturable(id).done(
                            function () {
                                swal(
                                    {
                                        title: "Éxito",
                                        text: "La operación se realizó exitosamente.",
                                        type: "success",
                                        timer: 3000,
                                        closeOnConfirm: true,
                                    },
                                    function () {
                                        fnc_sec_reportes_concar_obtener_conceptos_facturables().done(
                                            function (response) {
                                                fnc_sec_reportes_concar_renderizar_tabla_conceptos_facturables(
                                                    response
                                                );
                                            }
                                        );
                                        swal.close();
                                    }
                                );
                            }
                        );
                    },
                    function () {
                        //alertify.error('Cancel');
                    }
                )
                .set({labels: {ok: "Aceptar", cancel: "Cancelar"}});
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-editar-codigo-comercio"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-proveedor").text(
                "Editar Código de Comercio"
            );
            let id = $(event.target).data("id");
            let cod_comercio = $(event.target).data("cod-comercio");
            fnc_sec_reportes_concar_obtener_codigo_comercio(id, cod_comercio).done(function (
                response
            ) {
                if (response) {
                    fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_codigo_comercio().done(
                        function () {
                            let form_id = "#sec-reportes-concar-frm-editar-codigo-comercio";
                            reset_form(form_id);
                            let form = $(form_id);
                            form.find("[name='sec-reportes-concar-txt-id']").val(response.id);
                            form
                                .find("[name='sec-reportes-concar-txt-cod-comercio']")
                                .val(response.cod_comercio);
                            form
                                .find("[name='sec-reportes-concar-sel-ceco']")
                                .val(response.ceco)
                                .trigger("change");
                            $("#sec-reportes-concar-mdl-editar-codigo-comercio").modal(
                                "show"
                            );
                        }
                    );
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-nuevo-codigo-comercio"]',
        function () {
            $("#sec-reportes-concar-mlb-editar-codigo-comercio").text(
                "Nuevo Código de Comercio"
            );
            fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_codigo_comercio().done(
                function () {
                    let form_id = "#sec-reportes-concar-frm-editar-codigo-comercio";
                    let form = $(form_id);
                    form.find("[name='sec-reportes-concar-txt-id']").val("0");
                    $("#sec-reportes-concar-mdl-editar-codigo-comercio").modal("show");
                }
            );
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-eliminar-codigo-comercio"]',
        function (event) {
            let id = $(event.target).data("id");
            alertify
                .confirm(
                    "Alerta de eliminación.",
                    "¿Está seguro de eliminar el código de comercio?",
                    function () {
                        fnc_sec_reportes_concar_eliminar_codigo_comercio(id).done(
                            function () {
                                swal(
                                    {
                                        title: "Éxito",
                                        text: "La operación se realizó exitosamente.",
                                        type: "success",
                                        timer: 3000,
                                        closeOnConfirm: true,
                                    },
                                    function () {
                                        fnc_sec_reportes_concar_obtener_codigos_comercio().done(
                                            function (response) {
                                                fnc_sec_reportes_concar_renderizar_tabla_codigos_comercio(
                                                    response
                                                );
                                            }
                                        );
                                        swal.close();
                                    }
                                );
                            }
                        );
                    },
                    function () {
                        //alertify.error('Cancel');
                    }
                )
                .set({labels: {ok: "Aceptar", cancel: "Cancelar"}});
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-editar-banco"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-banco").text("Editar Banco");
            let id = $(event.target).data("id");
            fnc_sec_reportes_concar_obtener_banco(id).done(function (response) {
                if (response) {
                    let form_id = "#sec-reportes-concar-frm-editar-banco";
                    reset_form(form_id);
                    let form = $(form_id);
                    form.find("[name='sec-reportes-concar-txt-id']").val(response.id);
                    form
                        .find("[name='sec-reportes-concar-txt-nombre']")
                        .val(response.nombre);
                    form
                        .find("[name='sec-reportes-concar-txt-razon-social']")
                        .val(response.razon_social);
                    form.find("[name='sec-reportes-concar-txt-ruc']").val(response.ruc);
                    form
                        .find("[name='sec-reportes-concar-sel-estado']")
                        .val(response.estado)
                        .trigger("change");
                    $("#sec-reportes-concar-mdl-editar-banco").modal("show");
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-nuevo-banco"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-banco").text("Nuevo Banco");
            let form_id = "#sec-reportes-concar-frm-editar-banco";
            let form = $(form_id);
            form.find("[name='sec-reportes-concar-txt-id']").val("0");
            form
                .find("[name='sec-reportes-concar-sel-estado']")
                .val("1")
                .trigger("change");
            $("#sec-reportes-concar-mdl-editar-banco").modal("show");
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-eliminar-banco"]',
        function (event) {
            let id = $(event.target).data("id");
            alertify
                .confirm(
                    "Alerta de eliminación.",
                    "¿Está seguro de eliminar el banco?",
                    function () {
                        fnc_sec_reportes_concar_eliminar_banco(id).done(function () {
                            swal(
                                {
                                    title: "Éxito",
                                    text: "La operación se realizó exitosamente.",
                                    type: "success",
                                    timer: 3000,
                                    closeOnConfirm: true,
                                },
                                function () {
                                    fnc_sec_reportes_concar_obtener_bancos().done(function (
                                        response
                                    ) {
                                        fnc_sec_reportes_concar_renderizar_tabla_bancos(response);
                                        fnc_sec_reportes_concar_obtener_bancos_activos().done(
                                            function (bancos_activos) {
                                                fnc_sec_reportes_concar_guardar_bancos_activos_localstorage(
                                                    bancos_activos
                                                );
                                            }
                                        );
                                    });
                                    swal.close();
                                }
                            );
                        });
                    },
                    function () {
                        //alertify.error('Cancel');
                    }
                )
                .set({labels: {ok: "Aceptar", cancel: "Cancelar"}});
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-detalle-bancos"]',
        function (event) {
            let target = $(event.target);
            let archivo_proveedor_id = target.data("id");
            let nombre_proveedor = target.data("nombre-proveedor");
            if (nombre_proveedor === "niubiz") {
                $("#sec-reportes-concar-frm-editar-detalle-bancos")
                    .find("[name='sec-reportes-concar-txt-archivo-proveedor-id']")
                    .val(archivo_proveedor_id);
                $("#sec-reportes-concar-btn-agregar-detalle-bancos").data(
                    "archivo-proveedor-id",
                    archivo_proveedor_id
                );

                $.when(
                    fnc_sec_reportes_concar_obtener_detalle_bancos(archivo_proveedor_id),
                    fnc_sec_reportes_concar_obtener_bancos_activos()
                ).done(function (response1, response2) {
                    let detalle_bancos = response1[0];
                    let bancos_activos = response2[0];
                    fnc_sec_reportes_concar_guardar_bancos_activos_localstorage(
                        bancos_activos
                    );
                    fnc_sec_reportes_concar_renderizar_tabla_detalle_bancos(
                        detalle_bancos
                    );
                    let table_id = "#sec-reportes-concar-tbl-detalle-bancos";
                    apply_select2_plugin_to_selects_column(
                        table_id,
                        0,
                        "#sec-reportes-concar-mdl-detalle-bancos"
                    );
                    $("#sec-reportes-concar-mdl-detalle-bancos").modal("show");
                    $(table_id).on("page.dt", function () {
                        apply_select2_plugin_to_selects_column(
                            table_id,
                            0,
                            "#sec-reportes-concar-mdl-detalle-bancos"
                        );
                    });
                });
                /*fnc_sec_reportes_concar_obtener_detalle_bancos(archivo_proveedor_id).done(function (detalle_bancos) {
                fnc_sec_reportes_concar_renderizar_tabla_detalle_bancos(detalle_bancos).done(function () {
                    let table_id = "#sec-reportes-concar-tbl-detalle-bancos";
                    apply_select2_plugin_to_selects_column(table_id, 0, "#sec-reportes-concar-mdl-detalle-bancos");
                    $("#sec-reportes-concar-mdl-detalle-bancos").modal('show');
                    $(table_id).on('page.dt', function () {
                        apply_select2_plugin_to_selects_column(table_id, 0, "#sec-reportes-concar-mdl-detalle-bancos");
                    });
                });
            });*/
            }
        }
    );

    $(document).on(
        "click",
        'button[id="sec-reportes-concar-btn-agregar-detalle-bancos"]',
        function (event) {
            let target = $(event.target);
            let archivo_proveedor_id = target.data("archivo-proveedor-id");
            let data = {
                id: 0,
                banco_id: "",
                importe: 0,
                archivo_proveedor_id: archivo_proveedor_id,
            };

            let table_id = "#sec-reportes-concar-tbl-detalle-bancos";
            let datatable = $(table_id).DataTable();
            let currentPage = datatable.page();
            datatable.row.add(data).page(currentPage).draw(false);
            apply_select2_plugin_to_selects_column(
                table_id,
                0,
                "#sec-reportes-concar-mdl-detalle-bancos"
            );
        }
    );

    $(document).on(
        "click",
        'button[id^="sec-reportes-concar-btn-eliminar-detalle-banco"]',
        function () {
            let button = $(this);
            let id = parseInt(button.data("id"));
            let tr = button.parents("tr");
            let table_id = "#sec-reportes-concar-tbl-detalle-bancos";
            let datatable = $(table_id).DataTable();
            let currentPage = datatable.page();
            if (id === 0) {
                datatable.row(tr).remove().page(currentPage).draw(false);
                let rows_count_in_current_page = datatable
                    .rows({page: "current"})
                    .data().length;
                if (rows_count_in_current_page === 0) {
                    datatable.page("previous").draw("page");
                }
                reorder_input_ids_from_datatable(table_id);
            } else {
                alertify
                    .confirm(
                        "Alerta de eliminación.",
                        "¿Está seguro de eliminar el registro?",
                        function () {
                            fnc_sec_reportes_concar_eliminar_detalle_banco(id).done(
                                function () {
                                    swal(
                                        {
                                            title: "Éxito",
                                            text: "La operación se realizó exitosamente.",
                                            type: "success",
                                            timer: 3000,
                                            closeOnConfirm: true,
                                        },
                                        function () {
                                            swal.close();
                                            datatable.row(tr).remove().page(currentPage).draw(false);
                                        }
                                    );
                                }
                            );
                        },
                        function () {
                            //alertify.error('Cancel');
                        }
                    )
                    .set({labels: {ok: "Aceptar", cancel: "Cancelar"}});
            }
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-eliminar-numero-abonado"]',
        function (event) {
            let id = $(event.target).data("id");
            alertify
                .confirm(
                    "Alerta de eliminación.",
                    "¿Está seguro de eliminar el número de abonado?",
                    function () {
                        fnc_sec_reportes_concar_eliminar_numero_abonado(id).done(
                            function () {
                                swal(
                                    {
                                        title: "Éxito",
                                        text: "La operación se realizó exitosamente.",
                                        type: "success",
                                        timer: 3000,
                                        closeOnConfirm: true,
                                    },
                                    function () {
                                        fnc_sec_reportes_concar_obtener_numeros_abonado().done(
                                            function (response) {
                                                fnc_sec_reportes_concar_renderizar_tabla_numeros_abonado(
                                                    response
                                                );
                                            }
                                        );
                                        swal.close();
                                    }
                                );
                            }
                        );
                    },
                    function () {
                        //alertify.error('Cancel');
                    }
                )
                .set({labels: {ok: "Aceptar", cancel: "Cancelar"}});
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-editar-numero-abonado"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-numero-abonado").text(
                "Editar Número de Abonado"
            );
            let id = $(event.target).data("id");
            let nro_abonado = $(event.target).data("nro-abonado");
            fnc_sec_reportes_concar_obtener_numero_abonado(id, nro_abonado).done(function (
                response
            ) {
                if (response) {
                    fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_numero_abonado().done(
                        function () {
                            let form_id = "#sec-reportes-concar-frm-editar-numero-abonado";
                            reset_form(form_id);
                            let form = $(form_id);
                            form.find("[name='sec-reportes-concar-txt-id']").val(response.id);
                            form
                                .find("[name='sec-reportes-concar-txt-nro-abonado']")
                                .val(response.nro_abonado);
                            form
                                .find("[name='sec-reportes-concar-sel-ceco']")
                                .val(response.ceco)
                                .trigger("change");
                            $("#sec-reportes-concar-mdl-editar-numero-abonado").modal("show");
                        }
                    );
                }
            });
        }
    );

    $(document).on(
        "click",
        '[id="sec-reportes-concar-btn-nuevo-numero-abonado"]',
        function (event) {
            $("#sec-reportes-concar-mlb-editar-numero-abonado").text(
                "Nuevo Número de Abonado"
            );
            fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_numero_abonado().done(
                function () {
                    let form_id = "#sec-reportes-concar-frm-editar-numero-abonado";
                    let form = $(form_id);
                    form.find("[name='sec-reportes-concar-txt-id']").val("0");
                    $("#sec-reportes-concar-mdl-editar-numero-abonado").modal("show");
                }
            );
        }
    );

    $(document).on(
        "click",
        '[id^="sec-reportes-concar-btn-conceptos-facturables-pendientes"]',
        function (event) {
            let archivo_proveedor_id = $(event.target).data("id");
            let nombre_proveedor = $(event.target).data("nombre-proveedor");
            if (nombre_proveedor === "movistar") {
                fnc_sec_reportes_concar_obtener_conceptos_facturables_pendientes(
                    archivo_proveedor_id
                ).done(function (response) {
                    if (response && response.length) {
                        let form = $("#sec-reportes-concar-frm-editar-concepto-facturable");
                        form
                            .find("[name='sec-reportes-concar-txt-archivo-proveedor-id']")
                            .val(archivo_proveedor_id);
                        /*form
                            .find("[name='sec-reportes-concar-txt-concepto']")
                            .attr("readonly", true);*/
                        fnc_sec_reportes_concar_renderizar_tabla_conceptos_facturables(response);
                        $("#sec-reportes-concar-mdl-conceptos-facturables").modal("show");
                    } else {
                        alertify.warning(
                            "Los Conceptos Facturables están correctamente editados."
                        );
                    }
                });
            }
        }
    );


    $("#sec-reportes-concar-txt-archivo-proveedor").next().on("click", function () {
        $("#sec-reportes-concar-txt-archivo-proveedor").trigger("click");
    });

    $("#sec-reportes-concar-txt-archivo-proveedor").change(function () {
        $(this).blur().focus();
    });

    $("#sec-reportes-concar-frm-editar-centro-costo select[name='sec-reportes-concar-sel-estado']").on("change", function () {
        let validator = $(
            "#sec-reportes-concar-frm-editar-centro-costo"
        ).validate();
        validator.element("#sec-reportes-concar-txt-fecha-baja");
    });

    $(document).on(
        "keyup",
        '#sec-reportes-concar-tbl-detalle-bancos input[name="sec-reportes-concar-txt-importe[]"]',
        function (event) {
            let table_id = "#sec-reportes-concar-tbl-detalle-bancos";
            let datatable = null;
            if ($.fn.DataTable.isDataTable(table_id)) {
                datatable = new $.fn.dataTable.Api(table_id);
                let sum = 0;
                let column = datatable.column(2);
                var nodes = column.nodes();

                $.each(nodes, function () {
                    let value = $(this).find(":input").val();
                    sum += parseFloat(value);
                });

                $(column.footer()).html(sum.toFixed(2));
            }
        }
    );

    $(document).on("change", ".select2-hidden-accessible", function () {
        let form = $(this).closest("form");
        let validator = form.validate();
        if (validator) {
            try {
                let select_id = "#" + $(this).attr("id");
                validator.element(select_id);
            } catch (ex) {
                form.valid();
            }
        }
    });
}

function fnc_sec_reportes_concar_obtener_columns_archivos_proveedor_detalle(
    first_row,
    hidden_columns
) {
    let keys = Object.keys(first_row);
    let columns = [];
    for (let index in keys) {
        let name = keys[index];
        let options = {
            data: name,
            title: replaceAll(name, "_", " ").toUpperCase(),
            visible: !hidden_columns.includes(name),
        };

        let datetime_columns = get_datetime_columns();
        if (
            Array.isArray(datetime_columns) &&
            datetime_columns.length &&
            datetime_columns.includes(name)
        ) {
            options.render = $.fn.dataTable.render.moment(
                "YYYY-MM-DD HH:mm:ss",
                "DD/MM/YYYY"
            );
        }

        columns.push(options);
    }
    return columns;
}

function fnc_sec_reportes_concar_obtener_y_renderizar_tabla_archivos_proveedor_maestro() {
    return fnc_sec_reportes_concar_obtener_archivos_proveedor_maestro().done(
        function (data) {
            fnc_sec_reportes_concar_renderizar_tabla_archivos_proveedor_maestro(data);
        }
    );
}

function fnc_sec_reportes_concar_obtener_numeros_cuenta_pendientes(
    archivo_proveedor_id
) {
    let data = {
        archivo_proveedor_id,
        accion: "get-numeros-cuenta-pendientes",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_numeros_cuenta_pendientes", "data": data});
    return ajax_request({
        data,
    });
}

function fnc_sec_reportes_concar_obtener_centros_costo() {
    let data = {
        accion: "get-centros-costo",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_centros_costo", "data": data});
    return ajax_request({
        data,
    });
}

function fnc_sec_reportes_concar_obtener_numeros_cuenta() {
    let data = {
        accion: "get-numeros-cuenta",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_numeros_cuenta", "data": data});
    return ajax_request({
        data,
    });
}

function fnc_sec_reportes_concar_obtener_codigos_pago() {
    let data = {
        accion: "get-codigos-pago",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_codigos_pago", "data": data});
    return ajax_request({
        data,
    });
}

function fnc_sec_reportes_concar_obtener_codigos_comercio() {
    let data = {
        accion: "get-codigos-comercio",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_codigos_comercio", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_obtener_cuentas_contables() {
    let data = {
        accion: "get-cuentas-contables",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_cuentas_contables", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_obtener_conceptos_facturables() {
    let data = {
        accion: "get-conceptos-facturables",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_conceptos_facturables", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_obtener_codigos_pago_pendientes(
    archivo_proveedor_id
) {
    let data = {
        archivo_proveedor_id,
        accion: "get-codigos-pago-pendientes",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_codigos_pago_pendientes", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_obtener_centro_costo(id) {
    let data = {
        accion: "get-centro-costo",
        id
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_centro_costo", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_obtener_numero_cuenta(id, nro_cuenta) {
    let data = {
        accion: "get-numero-cuenta",
        id,
        nro_cuenta
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_numero_cuenta", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_obtener_codigo_pago(id, cod_pago) {
    let data = {
        accion: "get-codigo-pago",
        id,
        cod_pago
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_codigo_pago", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_obtener_codigo_comercio(id, cod_comercio) {
    let data = {
        accion: "get-codigo-comercio",
        id,
        cod_comercio
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_codigo_comercio", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_importar_archivo_proveedor() {
    let formData = new FormData();
    let accion = "procesar-archivo-proveedor";
    let nombre_proveedor = $(
        "#sec-reportes-concar-sel-proveedor-importar-archivo option:selected"
    ).text();
    let proveedor_id = $(
        "#sec-reportes-concar-sel-proveedor-importar-archivo"
    ).val();
    let file = document.getElementById(
        "sec-reportes-concar-txt-archivo-proveedor"
    ).files[0];
    let nombre_proveedor_clave = nombre_proveedor.toLowerCase().replace(" ", "_");
    formData.append("accion", accion);
    formData.append("nombre-proveedor", nombre_proveedor);
    formData.append("proveedor-id", proveedor_id);
    formData.append("archivo-proveedor", file);

    var data = {};
    formData.forEach(function (value, key) {
        if (key !== "archivo-proveedor") {
            data[key] = value;
        }
    });

    auditoria_send({"proceso": "fnc_sec_reportes_concar_importar_archivo_proveedor", "data": data});
    return ajax_request(
        {
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    let centros_costo = response.data_centros_costo;
                    let conceptos_facturables = response.data_conceptos;
                    let archivo_proveedor_id = response.archivo_proveedor_id;


                    $(document).on('click', 'button[name="sec-reportes-concar-btn-confirm-centros-costo"]', function () {
                        if (nombre_proveedor_clave === "movistar") {
                            let form = $("#sec-reportes-concar-frm-editar-codigo-pago");
                            form.find("[name='sec-reportes-concar-txt-archivo-proveedor-id']").val(archivo_proveedor_id);
                            fnc_sec_reportes_concar_renderizar_tabla_codigos_pago(centros_costo);
                            $("#sec-reportes-concar-mdl-codigos-pago").modal("show");
                        }
                    });

                    $(document).on('click', 'button[name="sec-reportes-concar-btn-confirm-conceptos-facturables"]', function () {
                        let form = $("#sec-reportes-concar-frm-editar-concepto-facturable");
                        form.find("[name='sec-reportes-concar-txt-archivo-proveedor-id']").val(archivo_proveedor_id);
                        fnc_sec_reportes_concar_renderizar_tabla_conceptos_facturables(conceptos_facturables);
                        $("#sec-reportes-concar-mdl-conceptos-facturables").modal("show");
                    });

                    if (centros_costo.length > 0 && conceptos_facturables.length > 0) {
                        swal({
                                title: 'Éxito',
                                html: true,
                                type: 'warning',
                                text: 'El archivo se importó con éxito pero existen Centros de Costo y Conceptos Facturables pendientes por agregar. ¿Desea agregarlos ahora?' +
                                    '<br>' +
                                    '<button type="button" role="button" tabindex="0" name="sec-reportes-concar-btn-confirm-centros-costo">' + 'Códigos de Pago' + '</button>' +
                                    '<button type="button" role="button" tabindex="0" name="sec-reportes-concar-btn-confirm-conceptos-facturables">' + 'Conceptos Facturables' + '</button>',
                                showCancelButton: true,
                                cancelButtonText: 'Cancelar',
                                showConfirmButton: false,
                                closeOnCancel: true
                            },
                            function (isConfirm) {
                                console.log(isConfirm);
                                if (isConfirm) {
                                    //alert("ok");
                                }
                            }
                        );
                    } else if (centros_costo.length > 0) {
                        swal(
                            {
                                title: "Éxito",
                                text: "El archivo se importó con éxito pero existen Centros de Costo pendientes por agregar. ¿Desea agregarlos ahora?",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonText: "Si",
                                cancelButtonText: "No",
                                closeOnConfirm: true,
                                closeOnCancel: true,
                            },
                            function (isConfirm) {
                                if (isConfirm) {
                                    let form;
                                    if (nombre_proveedor_clave === "direc_tv") {
                                        form = $("#sec-reportes-concar-frm-editar-numero-cuenta");
                                        form.find("[name='sec-reportes-concar-txt-archivo-proveedor-id']").val(archivo_proveedor_id);
                                        fnc_sec_reportes_concar_renderizar_tabla_numeros_cuenta(centros_costo);
                                        $("#sec-reportes-concar-mdl-numeros-cuenta").modal("show");
                                    } else if (nombre_proveedor_clave === "movistar") {
                                        form = $("#sec-reportes-concar-frm-editar-codigo-pago");
                                        form.find("[name='sec-reportes-concar-txt-archivo-proveedor-id']").val(archivo_proveedor_id);
                                        fnc_sec_reportes_concar_renderizar_tabla_codigos_pago(centros_costo);
                                        $("#sec-reportes-concar-mdl-codigos-pago").modal("show");
                                    } else if (nombre_proveedor_clave === "niubiz") {
                                        form = $("#sec-reportes-concar-frm-editar-codigo-comercio");
                                        form.find("[name='sec-reportes-concar-txt-archivo-proveedor-id']").val(archivo_proveedor_id);
                                        fnc_sec_reportes_concar_renderizar_tabla_codigos_comercio(centros_costo);
                                        $("#sec-reportes-concar-mdl-codigos-comercio").modal("show");
                                    } else if (nombre_proveedor_clave === "prosegur") {
                                        form = $("#sec-reportes-concar-frm-editar-numero-abonado");
                                        form.find("[name='sec-reportes-concar-txt-archivo-proveedor-id']").val(archivo_proveedor_id);
                                        fnc_sec_reportes_concar_renderizar_tabla_numeros_abonado(centros_costo);
                                        $("#sec-reportes-concar-mdl-numeros-abonado").modal("show");
                                    }
                                }
                            }
                        );
                    } else if (conceptos_facturables.length > 0) {
                        swal(
                            {
                                title: "Éxito",
                                text: "El archivo se importó con éxito pero existen Conceptos Facturables pendientes por agregar. ¿Desea agregarlos ahora?",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonText: "Si",
                                cancelButtonText: "No",
                                closeOnConfirm: true,
                                closeOnCancel: true,
                            },
                            function (isConfirm) {
                                if (isConfirm) {
                                    if (nombre_proveedor_clave === "movistar") {
                                        fnc_sec_reportes_concar_renderizar_tabla_conceptos_facturables(conceptos_facturables);
                                        $("#sec-reportes-concar-mdl-conceptos-facturables").modal("show");
                                    }
                                }
                            }
                        );
                    } else {
                        swal(
                            {
                                title: "Éxito",
                                text: "El archivo fue importado exitosamente.",
                                type: "success",
                                timer: 3000,
                                closeOnConfirm: true,
                            },
                            function () {
                                //swal.close();
                            }
                        );
                    }
                    fnc_sec_reportes_concar_obtener_y_renderizar_tabla_archivos_proveedor_maestro();
                }


            },
        },
        null,
        function () {
            reset_form("#sec-reportes-concar-frm-importar-archivo-proveedor");
        }
    );
}

function fnc_sec_reportes_concar_editar_centro_costo() {
    let form_id = "#sec-reportes-concar-frm-editar-centro-costo";
    let form = $(form_id);
    let id = form.find("[name='sec-reportes-concar-txt-id']").val();
    let local = form.find("[name='sec-reportes-concar-txt-local']").val();
    let descripcion =
        form.find("[name='sec-reportes-concar-txt-descripcion']").val() || null;
    let ceco = form.find("[name='sec-reportes-concar-txt-ceco']").val();
    let fecha_baja =
        form.find("[name='sec-reportes-concar-txt-fecha-baja']").parent().data()
            .date || null;
    let costo_mensual =
        form.find("[name='sec-reportes-concar-txt-costo-mensual']").val() || 0;
    let observacion =
        form.find("[name='sec-reportes-concar-txt-observacion']").val() || null;
    let estado = form.find("[name='sec-reportes-concar-sel-estado']").val();
    let accion = "editar-centro-costo";

    let data = {
        id,
        local,
        descripcion,
        ceco,
        fecha_baja,
        costo_mensual,
        observacion,
        estado,
        accion,
    };

    //Object.assign(target, source);
    auditoria_send({"proceso": "fnc_sec_reportes_concar_editar_centro_costo", "data": data});
    return ajax_request(
        {
            data,
            dataType: "text",
        },
        function () {
            swal(
                {
                    title: "Éxito",
                    text: "La operación se realizó exitosamente.",
                    type: "success",
                    timer: 3000,
                    closeOnConfirm: true
                },
                function () {
                    swal.close();

                    $("#sec-reportes-concar-mdl-editar-centro-costo").modal("hide");

                    fnc_sec_reportes_concar_obtener_centros_costo().done(function (
                        response
                    ) {
                        fnc_sec_reportes_concar_renderizar_tabla_centros_costo(response);
                    });
                }
            );
        }
    );
}

function fnc_sec_reportes_concar_eliminar_centro_costo(id) {
    let data = {
        accion: "eliminar-centro-costo",
        id
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_eliminar_centro_costo", "data": data});
    return ajax_request({
        data,
        dataType: "text",
    });
}

function fnc_sec_reportes_concar_eliminar_numero_cuenta(id) {
    let data = {
        accion: "eliminar-numero-cuenta",
        id
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_eliminar_numero_cuenta", "data": data});
    return ajax_request({
        data,
        dataType: "text",
    });
}

function fnc_sec_reportes_concar_eliminar_codigo_pago(id) {
    let data = {
        accion: "eliminar-codigo-pago",
        id
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_eliminar_codigo_pago", "data": data});
    return ajax_request({
        data,
        dataType: "text",
    });
}

function fnc_sec_reportes_concar_editar_numero_cuenta() {
    let id = $(
        "#sec-reportes-concar-mdl-editar-numero-cuenta [name='sec-reportes-concar-txt-id']"
    ).val();
    let nro_cuenta = $(
        "#sec-reportes-concar-mdl-editar-numero-cuenta [name='sec-reportes-concar-txt-nro-cuenta']"
    ).val();
    let ceco = $(
        "#sec-reportes-concar-mdl-editar-numero-cuenta [name='sec-reportes-concar-sel-ceco']"
    ).val();
    let archivo_proveedor_id = $(
        "#sec-reportes-concar-frm-editar-numero-cuenta [name='sec-reportes-concar-txt-archivo-proveedor-id']"
    ).val();
    let data = {
        id,
        nro_cuenta,
        ceco,
        accion: "editar-numero-cuenta"
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_editar_numero_cuenta", "data": data});
    return ajax_request(
        {
            data,
            dataType: "text",
        },
        function () {
            $("#sec-reportes-concar-mdl-editar-numero-cuenta").modal("hide");
            swal(
                {
                    title: "Éxito",
                    text: "La operación se realizó exitosamente.",
                    type: "success",
                    timer: 3000,
                    closeOnConfirm: true
                },
                function () {
                    swal.close();
                    if (archivo_proveedor_id) {
                        fnc_sec_reportes_concar_obtener_numeros_cuenta_pendientes(
                            archivo_proveedor_id
                        ).done(function (response) {
                            fnc_sec_reportes_concar_renderizar_tabla_numeros_cuenta(response);
                        });
                    } else {
                        fnc_sec_reportes_concar_obtener_numeros_cuenta().done(function (
                            response
                        ) {
                            fnc_sec_reportes_concar_renderizar_tabla_numeros_cuenta(response);
                        });
                    }
                }
            );
        }
    );
}

function fnc_sec_reportes_concar_editar_codigo_pago() {
    let id = $(
        "#sec-reportes-concar-mdl-editar-codigo-pago [name='sec-reportes-concar-txt-id']"
    ).val();
    let cod_pago = $(
        "#sec-reportes-concar-mdl-editar-codigo-pago [name='sec-reportes-concar-txt-cod-pago']"
    ).val();
    let ceco = $(
        "#sec-reportes-concar-mdl-editar-codigo-pago [name='sec-reportes-concar-sel-ceco']"
    ).val();
    let archivo_proveedor_id = $(
        "#sec-reportes-concar-frm-editar-codigo-pago [name='sec-reportes-concar-txt-archivo-proveedor-id']"
    ).val();
    let data = {
        id,
        cod_pago,
        ceco,
        accion: "editar-codigo-pago",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_editar_codigo_pago", "data": data});
    return ajax_request(
        {
            data,
            dataType: "text",
        },
        function () {
            $("#sec-reportes-concar-mdl-editar-codigo-pago").modal("hide");
            swal(
                {
                    title: "Éxito",
                    text: "La operación se realizó exitosamente.",
                    type: "success",
                    timer: 3000,
                    closeOnConfirm: true
                },
                function () {
                    swal.close();
                    if (archivo_proveedor_id) {
                        fnc_sec_reportes_concar_obtener_codigos_pago_pendientes(
                            archivo_proveedor_id
                        ).done(function (response) {
                            fnc_sec_reportes_concar_renderizar_tabla_codigos_pago(response);
                        });
                    } else {
                        fnc_sec_reportes_concar_obtener_codigos_pago(
                            archivo_proveedor_id
                        ).done(function (response) {
                            fnc_sec_reportes_concar_renderizar_tabla_codigos_pago(response);
                        });
                    }
                }
            );
        }
    );
}

function fnc_sec_reportes_concar_init_datetimepickers() {
    let txt_ids = [
        "#sec-reportes-concar-wrap-fecha-creacion-desde",
        "#sec-reportes-concar-wrap-fecha-creacion-hasta",
        "#sec-reportes-concar-wrap-fecha-comprobante",
        "#sec-reportes-concar-wrap-fecha-emision",
        "#sec-reportes-concar-wrap-fecha-vencimiento",
        "#sec-reportes-concar-wrap-fecha-baja",
    ];

    let options = {
        locale: "es",
        ignoreReadonly: true,
        allowInputToggle: true,
        format: "L",
    };

    txt_ids.forEach((id) => {
        if (id == "#sec-reportes-concar-wrap-fecha-baja") {
            options.showClear = true;
        } else {
            options.showClear = false;
        }
        $(id)
            .datetimepicker(options)
            .on("dp.change", function (event) {
                //$(this).datetimepicker('hide');
                if (event.target.id == "sec-reportes-concar-wrap-fecha-comprobante") {
                    let date = $(this).data().date;
                    let numero_mes = moment(date, "DD/MM/YYYY").format("MM");
                    $("#sec-reportes-concar-txt-mes-comprobante").val(numero_mes);
                }
            });
    });
}

function fnc_sec_reportes_concar_agregar_reglas_validacion_formularios() {
    let form_validation_options = [
        {
            id: "#sec-reportes-concar-frm-editar-centro-costo",
            rules: {
                "sec-reportes-concar-txt-id": {
                    required: true
                },
                "sec-reportes-concar-txt-local": {
                    required: true,
                    maxlength: 255
                },
                "sec-reportes-concar-txt-descripcion": {
                    maxlength: 255
                },
                "sec-reportes-concar-txt-ceco": {
                    required: true,
                    digits: true,
                    maxlength: 12
                },
                "sec-reportes-concar-txt-costo-mensual": {
                    number: true
                },
                "sec-reportes-concar-sel-estado": {
                    required: true
                },
                "sec-reportes-concar-txt-observacion": {
                    maxlength: 255
                },
                "sec-reportes-concar-txt-fecha-baja": {
                    required: {
                        depends: function (element) {
                            return $("#sec-reportes-concar-sel-estado").val() == "0";
                        }
                    }
                }
            },
            callback: fnc_sec_reportes_concar_editar_centro_costo,
        },
        {
            id: "#sec-reportes-concar-frm-importar-archivo-proveedor",
            rules: {
                "sec-reportes-concar-sel-proveedor-importar-archivo": {
                    required: true
                },
                "sec-reportes-concar-txt-archivo-proveedor": {
                    required: true
                },
            },
            callback: fnc_sec_reportes_concar_importar_archivo_proveedor,
        },
        {
            id: "#sec-reportes-concar-frm-exportar-archivo-concar-1",
            rules: {
                "sec-reportes-concar-sel-proveedor-exportar-concar": {
                    required: true
                },
                "sec-reportes-concar-txt-fecha-creacion-desde": {
                    required: true
                },
                "sec-reportes-concar-txt-fecha-creacion-hasta": {
                    required: true,
                    greaterThanOrEqual: [
                        "#sec-reportes-concar-txt-fecha-creacion-desde",
                        "Fecha Creación Desde",
                        "DD/MM/YYYY",
                    ]
                },
            },
            callback: function () {
                $("#sec-reportes-concar-txt-tipo-exportacion").val("por-rango-fechas");
                let nombre_proveedor = $(
                    "#sec-reportes-concar-sel-proveedor-exportar-concar option:selected"
                )
                    .text()
                    .toLowerCase()
                    .replace(" ", "_");
                if (nombre_proveedor == "movistar") {
                    $("#sec-reportes-concar-fg-numero-documento").css("display", "none");
                }
                let date = $("#sec-reportes-concar-wrap-fecha-comprobante").data().date;
                let numero_mes = moment(date, "DD/MM/YYYY").format("MM");
                $("#sec-reportes-concar-txt-mes-comprobante").val(numero_mes);
                $("#sec-reportes-concar-mdl-exportar-archivo-concar-2").modal("show");
            },
            customValidation: null,
        },
        {
            id: "#sec-reportes-concar-frm-exportar-archivo-concar-2",
            rules: {
                "sec-reportes-concar-txt-fecha-comprobante": {
                    required: true
                },
                "sec-reportes-concar-txt-numero-comprobante": {
                    required: true,
                    maxlength: 255
                },
                "sec-reportes-concar-txt-numero-documento": {
                    required: true,
                    maxlength: 255
                },
                "sec-reportes-concar-txt-fecha-emision": {
                    required: true
                },
                "sec-reportes-concar-wrap-fecha-vencimiento": {
                    required: true
                },
            },
            callback: fnc_sec_reportes_concar_exportar_archivo_concar,
        },
        {
            id: "#sec-reportes-concar-frm-editar-numero-cuenta",
            rules: {
                "sec-reportes-concar-txt-nro-cuenta": {
                    required: true,
                    digits: true,
                    maxlength: 12
                },
                "sec-reportes-concar-sel-ceco": {
                    required: true
                },
            },
            callback: fnc_sec_reportes_concar_editar_numero_cuenta,
        },
        {
            id: "#sec-reportes-concar-frm-editar-codigo-pago",
            rules: {
                "sec-reportes-concar-txt-cod-pago": {
                    required: true,
                    maxlength: 12,
                    digits: true
                },
                "sec-reportes-concar-sel-ceco": {
                    required: true
                },
            },
            callback: fnc_sec_reportes_concar_editar_codigo_pago,
        },
        {
            id: "#sec-reportes-concar-frm-editar-cuenta-contable",
            rules: {
                "sec-reportes-concar-txt-cta-contable": {
                    required: true,
                    maxlength: 12
                },
                "sec-reportes-concar-txt-concar": {
                    required: true,
                    maxlength: 50
                },
            },
            callback: fnc_sec_reportes_concar_editar_cuenta_contable,
        },
        {
            id: "#sec-reportes-concar-frm-editar-proveedor",
            rules: {
                "sec-reportes-concar-txt-nombre": {
                    required: true,
                    maxlength: 255
                },
                "sec-reportes-concar-txt-ruc": {
                    required: true,
                    digits: true,
                    exactlength: 11
                },
            },
            callback: fnc_sec_reportes_concar_editar_proveedor,
        },
        {
            id: "#sec-reportes-concar-frm-editar-concepto-facturable",
            rules: {
                "sec-reportes-concar-txt-concepto": {
                    required: true,
                    maxlength: 255
                },
                "sec-reportes-concar-sel-cta-contable": {
                    required: true
                },
            },
            callback: fnc_sec_reportes_concar_editar_concepto_facturable,
        },
        {
            id: "#sec-reportes-concar-frm-editar-codigo-comercio",
            rules: {
                "sec-reportes-concar-txt-cod-comercio": {
                    required: true,
                    maxlength: 12
                },
                "sec-reportes-concar-sel-ceco": {
                    required: true
                },
            },
            callback: fnc_sec_reportes_concar_editar_codigo_comercio,
        },
        {
            id: "#sec-reportes-concar-frm-editar-banco",
            rules: {
                "sec-reportes-concar-txt-nombre": {
                    required: true
                },
                "sec-reportes-concar-txt-ruc": {
                    required: true,
                    digits: true,
                    exactlength: 11
                },
                "sec-reportes-concar-sel-estado": {
                    required: true
                },
            },
            callback: fnc_sec_reportes_concar_editar_banco,
        },
        {
            id: "#sec-reportes-concar-frm-editar-detalle-bancos",
            rules: {
                "sec-reportes-concar-sel-banco-id[]": {
                    required: true
                },
                "sec-reportes-concar-txt-importe[]": {
                    required: true,
                    number: true
                },
            },
            callback: fnc_sec_reportes_concar_editar_detalle_bancos,
        },
        {
            id: "#sec-reportes-concar-frm-editar-numero-abonado",
            rules: {
                "sec-reportes-concar-nro-abonado": {
                    required: true,
                    maxlength: 12
                },
                "sec-reportes-concar-sel-ceco": {
                    required: true
                },
            },
            callback: fnc_sec_reportes_concar_editar_numero_abonado,
        },
    ];

    for (index in form_validation_options) {
        let id = form_validation_options[index].id;
        let rules = form_validation_options[index].rules;
        let callback = form_validation_options[index].callback;
        fnc_sec_reportes_concar_inicializar_validacion_formulario(
            id,
            rules,
            callback
        );
    }
    return false;
}

function fnc_sec_reportes_concar_inicializar_validacion_formulario(
    id,
    rules,
    callback
) {
    $(id)
        .submit(function (e) {
            e.preventDefault();
        })
        .validate({
            rules: rules,
            errorElement: "span",
            submitHandler: function () {
                if (callback) {
                    callback();
                }
            },
            errorPlacement: function (error, element) {
                error.addClass("help-block");
                element.closest("[class^='col-']").append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).closest(".form-group").addClass("has-error");
                $(element).closest("td").addClass("has-error");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).closest(".form-group").removeClass("has-error");
                $(element).closest("td").removeClass("has-error");
            },
            invalidHandler: function (form, validator) {
                let errors = validator.numberOfInvalids();
                if (errors) {
                    validator.errorList[0].element.focus();
                }
            },
        });
}

function fnc_sec_reportes_concar_renderizar_tabla_archivos_proveedor_maestro(
    data
) {
    let table_id = "#sec-reportes-concar-tbl-archivos-proveedor-maestro";
    let datatable = null;
    if (!$.fn.DataTable.isDataTable(table_id)) {
        datatable = $(table_id).DataTable({
            /*dom:
                "<'row'<'col-lg-2 col-md-3 col-sm-4' l><'col-lg-8 col-md-6 col-sm-4 text-center' <'row' B>><'col-lg-2 col-md-3 col-sm-4' f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: {
                buttons: [
                    {
                        text: '<i class="glyphicon glyphicon-export"></i> Exportar',
                        className: "btn btn-success",
                        action: function (e, dt, node, config) {
                            node.attr("id", "sec-reportes-concar-btn-exportar-concar");
                            let rowcollection = this.$(".call-checkbox:checked", { "page": "all" });
                            if (rowcollection.length) {
                                let archivo_proveedor_ids = {};
                                rowcollection.each(function (index, checkbox) {
                                    let proveedor_id = $(checkbox).data("proveedor-id");
                                    let archivo_proveedor_id = $(checkbox).val();
                                    if (!archivo_proveedor_ids.hasOwnProperty(proveedor_id)) {
                                        archivo_proveedor_ids[proveedor_id] = [];
                                    }
                                    archivo_proveedor_ids[proveedor_id].push(archivo_proveedor_id);
                                });

                                let json_archivo_proveedor_ids = JSON.stringify([
                                    archivo_proveedor_ids
                                ]);

                                $("#sec-reportes-concar-txt-tipo-exportacion").val("por-archivo-proveedor-ids");
                                $("#sec-reportes-concar-txt-archivo-proveedor-ids").val(json_archivo_proveedor_ids);
                                $("#sec-reportes-concar-mdl-exportar-archivo-concar-2").modal('show');
                            } else {
                                alertify.warning('Debe seleccionar uno o más documentos de la lista.');
                            }
                        }
                    },
                ],
                dom: {
                    button: {
                        tag: "button",
                        className: ""
                    }
                },
            },*/
            scrollX: true,
            scrollY: "70vh",
            scrollCollapse: true,
            autoWidth: false,
            data: data,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
            },
            columns: [
                {
                    data: "id",
                    title: "",
                    render: function (data, type, row, meta) {
                        let disabled = parseInt(row.estado) === 1 ? "disabled" : "";
                        let proveedor_id = row.proveedor_id;
                        return (
                            '<input class="call-checkbox" type="checkbox" data-proveedor-id="' +
                            proveedor_id +
                            '" id="sec-reportes-concar-chk-archivo-proveedor-id-' +
                            data +
                            '" value="' +
                            data +
                            '" ' +
                            disabled +
                            ">"
                        );
                    },
                    orderable: false
                },
                {
                    data: "nombre_archivo",
                    title: "NOMBRE DE ARCHIVO"
                },
                {
                    data: "created_at",
                    title: "FECHA DE CREACIÓN",
                    className: "text-center",
                    render: $.fn.dataTable.render.moment(
                        "YYYY-MM-DD HH:mm:ss",
                        "DD/MM/YYYY"
                    )
                },
                {
                    data: "proveedor_id",
                    title: "PROVEEDOR ID",
                    visible: false
                },
                {
                    data: "nombre_proveedor",
                    title: "PROVEEDOR",
                    className: "text-center"
                },
                {
                    data: "numero_documento",
                    title: "NRO. DOCUMENTO",
                    className: "text-center"
                },
                {
                    data: "estado",
                    title: "ESTADO",
                    className: "text-center",
                    render: function (data) {
                        let label = "warning";
                        let estado = "PENDIENTE";
                        if (parseInt(data) === 1) {
                            label = "success";
                            estado = "GENERADO";
                        }
                        return (
                            '<span class="label label-' + label + '">' + estado + "</span>"
                        );
                    }
                },
                {
                    data: null,
                    title: "",
                    className: "text-center",
                    width: "100px",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        let nombre_proveedor = data.nombre_proveedor
                            .toLowerCase()
                            .replace(" ", "_");
                        let disabled = parseInt(data.estado) === 1 ? "disabled" : "";
                        let buttons =
                            '<div class="dropdown">' +
                            '<button id="dLabel' +
                            meta.row +
                            '" type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                            ' Acciones <span class="caret"></span>' +
                            "</button>" +
                            '<ul class="dropdown-menu" aria-labelledby="dLabel' +
                            meta.row +
                            '">' +
                            '<li><a class="btn btn-sm" role="button" href="#" id="sec-reportes-concar-btn-mostrar-detalle-archivo-proveedor-id-' +
                            data.id +
                            '" data-id="' +
                            data.id +
                            '" title="Ver Detalle"> Ver Detalle </a></li>' +
                            '<li><a class="btn btn-sm" role="button" href="#" id="sec-reportes-concar-btn-eliminar-detalle-archivo-proveedor-id-' +
                            data.id +
                            '" data-id="' +
                            data.id +
                            '" title="Eliminar"> Eliminar </a></li>' +
                            '<li><a class="btn btn-sm" role="button" href="#" id="sec-reportes-concar-btn-centros-costo-pendientes-' +
                            data.id +
                            '" data-id="' +
                            data.id +
                            '" data-nombre-proveedor="' +
                            nombre_proveedor +
                            '" title="Centros de Costo"> Centros de Costo </a></li>';
                        if (nombre_proveedor === "niubiz") {
                            buttons +=
                                '<li><a class="btn btn-sm" href="#" role="button" id="sec-reportes-concar-btn-detalle-bancos-' +
                                data.id +
                                '" data-id="' +
                                data.id +
                                '" data-nombre-proveedor="' +
                                nombre_proveedor +
                                '" title="Detalle Bancos"> Detalle Bancos </a></li>';
                        }

                        if (nombre_proveedor === "movistar") {
                            buttons +=
                                '<li><a class="btn btn-sm" href="#" role="button" id="sec-reportes-concar-btn-conceptos-facturables-pendientes-' +
                                data.id +
                                '" data-id="' +
                                data.id +
                                '" data-nombre-proveedor="' +
                                nombre_proveedor +
                                '" title="Conceptos Facturables"> Conceptos Facturables </a></li>';
                        }

                        buttons +=
                            '<li><a class="btn btn-sm ' +
                            disabled +
                            '" role="button" id="sec-reportes-concar-btn-exportar-archivo-concar-por-archivo-proveedor-id-' +
                            data.id +
                            '" data-id="' +
                            data.id +
                            '" data-proveedor-id="' +
                            data.proveedor_id +
                            '" data-nombre-proveedor="' +
                            nombre_proveedor +
                            '" ' +
                            disabled +
                            ' title="Exportar"> Exportar </a></li>' +
                            '</ul>' +
                            '</div>';
                        return buttons;
                    }
                },
            ],
            createdRow: function (row, data, dataIndex) {
                $("td:eq(7)", row).css("min-width", "100px");
            },
        });

        $(window).on("resize", function () {
            datatable.columns.adjust().draw();
        });

        return datatable;
    } else {
        datatable = new $.fn.dataTable.Api(table_id);
        datatable.clear();
        datatable.rows.add(data);
        datatable.draw();
        return datatable;
    }
}

function fnc_sec_reportes_concar_renderizar_tabla_archivos_proveedor_detalle(
    data,
    columns
) {
    let table_name = "#sec-reportes-concar-tbl-archivos-proveedor-detalle";

    if ($.fn.DataTable.isDataTable(table_name)) {
        $(table_name).DataTable().destroy();
        $("#sec-reportes-concar-tbl-archivos-proveedor-detalle").empty();
    }

    return fnc_sec_reportes_concar_renderizar_tabla(
        table_name,
        data,
        columns,
        null,
        null
    );
}

function fnc_sec_reportes_concar_renderizar_tabla_centros_costo(data) {
    let table_name = "#sec-reportes-concar-tbl-centros-costo";
    let columns = [
        {
            data: 'id',
            title: '',
            visible: false,
            orderable: false,
            searchable: false,
        },
        {
            data: 'ceco',
            title: 'CECO',
            className: 'text-center',
        },
        {
            data: 'local',
            title: 'LOCAL',
        },
        {
            data: 'fecha_baja',
            title: 'FECHA DE BAJA',
            className: 'text-center',
            render: $.fn.dataTable.render.moment('YYYY-MM-DD HH:mm:ss', 'DD/MM/YYYY'),
        },
        {
            data: 'estado',
            title: 'ESTADO',
            className: 'text-center',
            render: function (data) {
                let label = 'success';
                let estado = 'ABIERTO';
                if (data === 0) {
                    label = 'danger';
                    estado = 'CERRADO';
                }
                return '<span class="label label-' + label + '">' + estado + "</span>";
            },
        },
        {
            data: null,
            title: '',
            className: 'text-center',
            orderable: false,
            searchable: false,
            width: '80px',
            render: function (data) {
                return (
                    '<div class="btn-group" role="group">' +
                    '<button type="button" id="sec-reportes-concar-btn-editar-centro-costo-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" class="btn btn-warning btn-sm" title="Editar Centro de Costo"><i class="glyphicon glyphicon-edit" data-id="' +
                    data.id +
                    '"></i></button>' +
                    " &nbsp; " +
                    '<button type="button" id="sec-reportes-concar-btn-eliminar-centro-costo-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" class="btn btn-danger btn-sm" title="Eliminar Centro de Costo"><i class="glyphicon glyphicon-trash" data-id="' +
                    data.id +
                    '"></i></button>' +
                    "</div>"
                );
            },
        },
    ];
    return fnc_sec_reportes_concar_renderizar_tabla(
        table_name,
        data,
        columns,
        null,
        null
    );
}

function fnc_sec_reportes_concar_renderizar_tabla_numeros_cuenta(data) {
    let table_name = '#sec-reportes-concar-tbl-numeros-cuenta';
    let columns = [
        {
            data: 'id',
            title: '',
            visible: false,
            orderable: false,
            searchable: false,
        },
        {
            data: "nro_cuenta",
            title: "NRO. CUENTA",
        },
        {
            data: "ceco",
            title: "CECO",
        },
        {
            data: "local",
            title: "LOCAL",
        },
        {
            data: null,
            title: "",
            className: "text-center",
            orderable: false,
            searchable: false,
            width: "80px",
            render: function (data) {
                return (
                    '<div class="btn-group" role="group">' +
                    '<button type="button" id="sec-reportes-concar-btn-editar-numero-cuenta-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" data-numero-cuenta="' +
                    data.nro_cuenta +
                    '" class="btn btn-warning btn-sm" title="Editar Número Cuenta"><i class="glyphicon glyphicon-edit" data-id="' +
                    data.id +
                    '" data-numero-cuenta="' +
                    data.nro_cuenta +
                    '"></i></button>' +
                    " &nbsp; " +
                    '<button type="button" id="sec-reportes-concar-btn-eliminar-numero-cuenta-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" data-numero-cuenta="' +
                    data.nro_cuenta +
                    '" class="btn btn-danger btn-sm" title="Eliminar Número Cuenta"><i class="glyphicon glyphicon-trash" data-id="' +
                    data.id +
                    '" data-numero-cuenta="' +
                    data.nro_cuenta +
                    '"></i></button>' +
                    "</div>"
                );
            },
        },
    ];
    return fnc_sec_reportes_concar_renderizar_tabla(
        table_name,
        data,
        columns,
        null,
        null
    );
}

function fnc_sec_reportes_concar_renderizar_tabla_codigos_pago(data) {
    let table_name = "#sec-reportes-concar-tbl-codigos-pago";
    let columns = [
        {
            data: "id",
            title: "",
            visible: false,
            orderable: false,
            searchable: false,
        },
        {
            data: "cod_pago",
            title: "CÓD. PAGO",
        },
        {
            data: "ceco",
            title: "CECO",
        },
        {
            data: "local",
            title: "LOCAL",
        },
        {
            data: null,
            title: "",
            className: "text-center",
            orderable: false,
            searchable: false,
            width: "80px",
            render: function (data) {
                return (
                    '<div class="btn-group" role="group">' +
                    '<button type="button" id="sec-reportes-concar-btn-editar-codigo-pago-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" data-codigo-pago="' +
                    data.cod_pago +
                    '" class="btn btn-warning btn-sm" title="Editar Código de Pago"><i class="glyphicon glyphicon-edit" data-id="' +
                    data.id +
                    '" data-codigo-pago="' +
                    data.cod_pago +
                    '"></i></button>' +
                    " &nbsp; " +
                    '<button type="button" id="sec-reportes-concar-btn-eliminar-codigo-pago-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" data-codigo-pago="' +
                    data.cod_pago +
                    '" class="btn btn-danger btn-sm" title="Eliminar Código de Pago"><i class="glyphicon glyphicon-trash" data-id="' +
                    data.id +
                    '" data-codigo-pago="' +
                    data.cod_pago +
                    '"></i></button>' +
                    "</div>"
                );
            },
        },
    ];
    return fnc_sec_reportes_concar_renderizar_tabla(
        table_name,
        data,
        columns,
        null,
        null
    );
}

function fnc_sec_reportes_concar_renderizar_tabla_cuentas_contables(data) {
    let table_name = "#sec-reportes-concar-tbl-cuentas-contables";
    let columns = [
        {
            data: "id",
            title: "",
            visible: false,
            orderable: false,
            searchable: false,
        },
        {
            data: "cta_contable",
            title: "CTA. CONTABLE",
        },
        {
            data: "concar",
            title: "CONCAR",
        },
        {
            data: null,
            title: "",
            className: "text-center",
            orderable: false,
            searchable: false,
            width: "80px",
            render: function (data) {
                return (
                    '<div class="btn-group" role="group">' +
                    '<button type="button" id="sec-reportes-concar-btn-editar-cuenta-contable-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" class="btn btn-warning btn-sm" title="Editar Cuenta Contable"><i class="glyphicon glyphicon-edit" data-id="' +
                    data.id +
                    '"></i></button>' +
                    " &nbsp; " +
                    '<button type="button" id="sec-reportes-concar-btn-eliminar-cuenta-contable-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" class="btn btn-danger btn-sm" title="Eliminar Cuenta Contable"><i class="glyphicon glyphicon-trash" data-id="' +
                    data.id +
                    '"></i></button>' +
                    "</div>"
                );
            },
        },
    ];
    return fnc_sec_reportes_concar_renderizar_tabla(
        table_name,
        data,
        columns,
        null,
        null
    );
}

function fnc_sec_reportes_concar_renderizar_tabla_proveedores(data) {
    let table_name = "#sec-reportes-concar-tbl-proveedores";
    let columns = [
        {
            data: "id",
            title: "",
            visible: false,
            orderable: false,
            searchable: false,
        },
        {
            data: "nombre",
            title: "NOMBRE",
        },
        {
            data: "ruc",
            title: "RUC",
        },
        {
            data: null,
            title: "",
            className: "text-center",
            orderable: false,
            searchable: false,
            width: "80px",
            render: function (data) {
                return (
                    '<div class="btn-group" role="group">' +
                    '<button type="button" id="sec-reportes-concar-btn-editar-proveedor-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" class="btn btn-warning btn-sm" title="Editar Proveedor"><i class="glyphicon glyphicon-edit" data-id="' +
                    data.id +
                    '"></i></button>' +
                    " &nbsp; " +
                    '<button type="button" id="sec-reportes-concar-btn-eliminar-proveedor-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" class="btn btn-danger btn-sm" title="Eliminar Proveedor"><i class="glyphicon glyphicon-trash" data-id="' +
                    data.id +
                    '"></i></button>' +
                    "</div>"
                );
            },
        },
    ];
    return fnc_sec_reportes_concar_renderizar_tabla(
        table_name,
        data,
        columns,
        null,
        null
    );
}

function fnc_sec_reportes_concar_renderizar_tabla_conceptos_facturables(data) {
    let table_name = "#sec-reportes-concar-tbl-conceptos-facturables";
    let columns = [
        {
            data: "id",
            title: "",
            visible: false,
            orderable: false,
            searchable: false,
        },
        {
            data: "concepto",
            title: "CONCEPTO",
        },
        {
            data: "cta_contable",
            title: "CTA. CONTABLE",
        },
        {
            data: "concar",
            title: "CONCAR",
        },
        {
            data: null,
            title: "",
            className: "text-center",
            orderable: false,
            searchable: false,
            width: "80px",
            render: function (data) {
                return (
                    '<div class="btn-group" role="group">' +
                    '<button type="button" id="sec-reportes-concar-btn-editar-concepto-facturable-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" data-concepto="' +
                    data.concepto +
                    '" class="btn btn-warning btn-sm" title="Editar Concepto Facturables"><i class="glyphicon glyphicon-edit" data-id="' +
                    data.id +
                    '" data-concepto="' +
                    data.concepto +
                    '"></i></button>' +
                    " &nbsp; " +
                    '<button type="button" id="sec-reportes-concar-btn-eliminar-concepto-facturable-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" class="btn btn-danger btn-sm" title="Eliminar Concepto Facturables"><i class="glyphicon glyphicon-trash" data-id="' +
                    data.id +
                    '"></i></button>' +
                    "</div>"
                );
            },
        },
    ];
    return fnc_sec_reportes_concar_renderizar_tabla(
        table_name,
        data,
        columns,
        null,
        null
    );
}

function fnc_sec_reportes_concar_renderizar_tabla_codigos_comercio(data) {
    let table_name = "#sec-reportes-concar-tbl-codigos-comercio";
    let columns = [
        {
            data: "id",
            title: "",
            visible: false,
            orderable: false,
            searchable: false,
        },
        {
            data: "cod_comercio",
            title: "CÓD. COMERCIO",
        },
        {
            data: "ceco",
            title: "CECO",
        },
        {
            data: "local",
            title: "LOCAL",
        },
        {
            data: null,
            title: "",
            className: "text-center",
            orderable: false,
            searchable: false,
            width: "80px",
            render: function (data) {
                return (
                    '<div class="btn-group" role="group">' +
                    '<button type="button" id="sec-reportes-concar-btn-editar-codigo-comercio-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" data-cod-comercio="' +
                    data.cod_comercio +
                    '" class="btn btn-warning btn-sm" title="Editar Código de Comercio"><i class="glyphicon glyphicon-edit" data-id="' +
                    data.id +
                    '" data-cod-comercio="' +
                    data.cod_comercio +
                    '"></i></button>' +
                    " &nbsp; " +
                    '<button type="button" id="sec-reportes-concar-btn-eliminar-codigo-comercio-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" data-cod-comercio="' +
                    data.cod_comercio +
                    '" class="btn btn-danger btn-sm" title="Eliminar Código de Comercio"><i class="glyphicon glyphicon-trash" data-id="' +
                    data.id +
                    '" data-cod-comercio="' +
                    data.cod_comercio +
                    '"></i></button>' +
                    "</div>"
                );
            },
        },
    ];
    return fnc_sec_reportes_concar_renderizar_tabla(
        table_name,
        data,
        columns,
        null,
        null
    );
}

function fnc_sec_reportes_concar_renderizar_tabla_bancos(data) {
    let table_name = "#sec-reportes-concar-tbl-bancos";
    let columns = [
        {
            data: "id",
            title: "",
            visible: false,
            orderable: false,
            searchable: false,
        },
        {
            data: "nombre",
            title: "NOMBRE",
        },
        {
            data: "ruc",
            title: "RUC",
        },
        {
            data: "estado",
            title: "ESTADO",
            render: function (data) {
                let label = "success";
                let estado = "ACTIVO";
                if (data === 0) {
                    label = "danger";
                    estado = "INACTIVO";
                }
                return (
                    '<span class="label label-' +
                    label +
                    '" data-value="' +
                    data +
                    '">' +
                    estado +
                    "</span>"
                );
            },
        },
        {
            data: null,
            title: "",
            className: "text-center",
            orderable: false,
            searchable: false,
            width: "80px",
            render: function (data) {
                return (
                    '<div class="btn-group" role="group">' +
                    '<button type="button" id="sec-reportes-concar-btn-editar-banco-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" class="btn btn-warning btn-sm" title="Editar Código de Comercio"><i class="glyphicon glyphicon-edit" data-id="' +
                    data.id +
                    '"></i></button>' +
                    " &nbsp; " +
                    '<button type="button" id="sec-reportes-concar-btn-eliminar-banco-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" class="btn btn-danger btn-sm" title="Eliminar Código de Comercio"><i class="glyphicon glyphicon-trash" data-id="' +
                    data.id +
                    '"></i></button>' +
                    "</div>"
                );
            },
        },
    ];
    return fnc_sec_reportes_concar_renderizar_tabla(
        table_name,
        data,
        columns,
        null,
        null
    );
}

function fnc_sec_reportes_concar_renderizar_tabla_detalle_bancos(data) {
    let table_name = "#sec-reportes-concar-tbl-detalle-bancos";
    let columns = [
        {
            data: "id",
            title: "",
            visible: false,
            orderable: false,
            searchable: false,
            render: function (data, type, row, meta) {
                return (
                    '<input type="hidden" name="sec-reportes-concar-txt-id[]" value="' +
                    data +
                    '" />'
                );
            },
        },
        {
            data: "banco_id",
            title: "BANCO",
            className: "col-xs-12",
            render: function (data, type, row, meta) {
                let select = fnc_sec_reportes_concar_create_select_bancos_activos(
                    meta.row,
                    data
                );
                return select.prop("outerHTML");
            },
        },
        {
            data: "importe",
            title: "IMPORTE",
            className: "col-xs-12 text-right",
            width: "20%",
            render: function (data, type, row, meta) {
                return (
                    '<input type="number" class="form-control text-right w-100" id="sec-reportes-concar-txt-importe-' +
                    meta.row +
                    '" name="sec-reportes-concar-txt-importe[]" value="' +
                    data +
                    '">'
                );
            },
        },
        {
            data: "archivo_proveedor_id",
            title: "",
            visible: false,
            orderable: false,
            searchable: false,
            render: function (data, type, row, meta) {
                return (
                    '<input type="hidden" name="sec-reportes-concar-txt-archivo-proveedor-id[]" value="' +
                    data +
                    '" />'
                );
            },
        },
        {
            data: null,
            title: "",
            className: "text-center",
            orderable: false,
            searchable: false,
            width: "60px",
            render: function (data, type, row, meta) {
                return (
                    '<div class="btn-group" role="group">' +
                    '<button type="button" id="sec-reportes-concar-btn-eliminar-detalle-banco-' +
                    data.id +
                    '" data-archivo-proveedor-id="' +
                    data.archivo_proveedor_id +
                    '" data-id="' +
                    data.id +
                    '" class="btn btn-danger btn-sm" title="Eliminar Código de Comercio"><i class="glyphicon glyphicon-trash" data-id="' +
                    data.id +
                    '" data-archivo-proveedor-id="' +
                    data.archivo_proveedor_id +
                    '"></i></button>' +
                    "</div>"
                );
            },
        },
    ];
    return fnc_sec_reportes_concar_renderizar_tabla(
        table_name,
        data,
        columns,
        null,
        function (api, row, data, start, end, display) {
            let sum = 0;
            let nodes = api.column(2).nodes();
            $.each(nodes, function () {
                let value = $(this).find(":input").val();
                sum += parseFloat(value);
            });
            $(api.column(2).footer()).html(sum.toFixed(2));
        }
    );
}

function fnc_sec_reportes_concar_renderizar_tabla_numeros_abonado(data) {
    let table_name = "#sec-reportes-concar-tbl-numeros-abonado";
    let columns = [
        {
            data: "id",
            title: "",
            visible: false,
            orderable: false,
            searchable: false,
        },
        {
            data: "nro_abonado",
            title: "NRO. ABONADO",
        },
        {
            data: "ceco",
            title: "CECO",
        },
        {
            data: "local",
            title: "LOCAL",
        },
        {
            data: null,
            title: "",
            className: "text-center",
            orderable: false,
            searchable: false,
            width: "80px",
            render: function (data) {
                return (
                    '<div class="btn-group" role="group">' +
                    '<button type="button" id="sec-reportes-concar-btn-editar-numero-abonado-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" data-nro-abonado="' +
                    data.nro_abonado +
                    '" class="btn btn-warning btn-sm" title="Editar Número Abonado"><i class="glyphicon glyphicon-edit" data-id="' +
                    data.id +
                    '" data-nro-abonado="' +
                    data.nro_abonado +
                    '"></i></button>' +
                    " &nbsp; " +
                    '<button type="button" id="sec-reportes-concar-btn-eliminar-numero-abonado-' +
                    data.id +
                    '" data-id="' +
                    data.id +
                    '" data-nro-abonado="' +
                    data.nro_abonado +
                    '" class="btn btn-danger btn-sm" title="Eliminar Número de Abonado"><i class="glyphicon glyphicon-trash" data-id="' +
                    data.id +
                    '" data-nro-abonado="' +
                    data.nro_abonado +
                    '"></i></button>' +
                    "</div>"
                );
            },
        },
    ];
    return fnc_sec_reportes_concar_renderizar_tabla(
        table_name,
        data,
        columns,
        null,
        null
    );
}

function fnc_sec_reportes_concar_renderizar_tabla(
    table_name,
    data,
    columns,
    initComplete,
    footerCallback
) {
    let datatable = null;
    if (!$.fn.DataTable.isDataTable(table_name)) {
        datatable = $(table_name).DataTable({
            scrollX: true,
            scrollY: "60vh",
            scrollCollapse: true,
            autoWidth: true,
            data,
            searching: true,
            paging: true,
            info: true,
            order: [
                [1, "asc"],
                [2, "asc"],
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
            },
            columns,
            initComplete: function (settings, json) {
                if (initComplete && typeof initComplete === "function") {
                    initComplete(settings, json);
                }
            },
            footerCallback: function (row, data, start, end, display) {
                if (footerCallback && typeof footerCallback === "function") {
                    let api = this.api();
                    footerCallback(api, row, data, start, end, display);
                }
            },
        });

        $(window).on("resize", function () {
            datatable.columns.adjust().draw();
        });
    } else {
        datatable = new $.fn.dataTable.Api(table_name);
        let currentPage = datatable.page();
        datatable.clear();
        datatable.rows.add(data);
        datatable.draw(false);
        datatable.page(currentPage);
    }

    return datatable;
}

function fnc_sec_reportes_concar_obtener_archivos_proveedor_maestro() {
    let data = {accion: "get-archivos-proveedor-maestro"};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_archivos_proveedor_maestro", "data": data});
    return ajax_request({data});
}

function fnc_sec_reportes_concar_obtener_archivos_proveedor_detalle(
    archivo_proveedor_id
) {
    let data = {archivo_proveedor_id, accion: "get-archivos-proveedor-detalle"};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_archivos_proveedor_detalle", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_eliminar_archivo_proveedor(
    archivo_proveedor_id
) {
    let data = {archivo_proveedor_id, accion: "eliminar-archivo-proveedor"};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_eliminar_archivo_proveedor", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_exportar_archivo_concar() {
    let tipo = $("#sec-reportes-concar-txt-tipo-exportacion").val();
    let data = {
        accion: "exportar-archivo-concar",
        tipo,
        fecha_comprobante: $("#sec-reportes-concar-wrap-fecha-comprobante").data()
            .date,
        numero_comprobante: $("#sec-reportes-concar-txt-numero-comprobante").val(),
        fecha_emision: $("#sec-reportes-concar-wrap-fecha-emision").data().date,
        numero_documento: $("#sec-reportes-concar-txt-numero-documento").val(),
        fecha_vencimiento: $("#sec-reportes-concar-wrap-fecha-vencimiento").data()
            .date,
    };

    switch (tipo) {
        case "por-rango-fechas":
            Object.assign(data, {
                proveedor_id: $(
                    "#sec-reportes-concar-sel-proveedor-exportar-concar"
                ).val(),
                fecha_creacion_desde: $(
                    "#sec-reportes-concar-wrap-fecha-creacion-desde"
                ).data().date,
                fecha_creacion_hasta: $(
                    "#sec-reportes-concar-wrap-fecha-creacion-hasta"
                ).data().date,
            });

            break;
        case "por-archivo-proveedor-ids":
            Object.assign(data, {
                archivo_proveedor_ids: $(
                    "#sec-reportes-concar-txt-archivo-proveedor-ids"
                ).val(),
            });

            break;
    }
    auditoria_send({"proceso": "fnc_sec_reportes_concar_exportar_archivo_concar", "data": data});
    return ajax_request(
        {
            data,
        },
        function (responses) {
            if (responses && responses.length) {
                responses.forEach((response) => {
                    let $a = $("<a>");
                    let file = response.file;
                    $a.attr("href", file);
                    $("body").append($a);
                    let file_name = response.file_name;

                    if (!file_name) {
                        file_name = "REPORTE CONCAR - " + new Date().getTime() + ".xls";
                    }
                    $a.attr("download", file_name);
                    $a[0].click();
                    $a.remove();
                    $("#sec-reportes-concar-mdl-exportar-archivo-concar-2").modal("hide");
                    fnc_sec_reportes_concar_obtener_y_renderizar_tabla_archivos_proveedor_maestro();
                });
            }
        }
    );
}

function fnc_sec_reportes_concar_obtener_cuenta_contable(id) {
    let data = {accion: "get-cuenta-contable", id};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_cuenta_contable", "data": data});
    return ajax_request({data});
}

function fnc_sec_reportes_concar_editar_cuenta_contable() {
    let form_id = "#sec-reportes-concar-frm-editar-cuenta-contable";
    let form = $(form_id);
    let id = form.find("[name='sec-reportes-concar-txt-id']").val();
    let cta_contable = form
        .find("[name='sec-reportes-concar-txt-cta-contable']")
        .val();
    let concar = form.find("[name='sec-reportes-concar-txt-concar']").val();
    let data = {
        id,
        cta_contable,
        concar,
        accion: "editar-cuenta-contable",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_editar_cuenta_contable", "data": data});
    return ajax_request(
        {
            data,
            dataType: "text",
        },
        function () {
            $("#sec-reportes-concar-mdl-editar-cuenta-contable").modal("hide");
            swal(
                {
                    title: "Éxito",
                    text: "La operación se realizó exitosamente.",
                    type: "success",
                    timer: 3000,
                    closeOnConfirm: true
                },
                function () {
                    swal.close();
                    fnc_sec_reportes_concar_obtener_cuentas_contables().done(function (
                        response
                    ) {
                        fnc_sec_reportes_concar_renderizar_tabla_cuentas_contables(
                            response
                        );
                    });
                }
            );
        }
    );
}

function fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_codigo_pago() {
    let form_id = "#sec-reportes-concar-frm-editar-codigo-pago";
    return fnc_sec_reportes_concar_refresh_select_ceco(form_id);
}

function fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_numero_cuenta() {
    let form_id = "#sec-reportes-concar-frm-editar-numero-cuenta";
    return fnc_sec_reportes_concar_refresh_select_ceco(form_id);
}

function fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_codigo_comercio() {
    let form_id = "#sec-reportes-concar-frm-editar-codigo-comercio";
    return fnc_sec_reportes_concar_refresh_select_ceco(form_id);
}

function fnc_sec_reportes_concar_refresh_select_ceco_from_frm_editar_numero_abonado() {
    let form_id = "#sec-reportes-concar-frm-editar-numero-abonado";
    return fnc_sec_reportes_concar_refresh_select_ceco(form_id);
}

function fnc_sec_reportes_concar_refresh_select_ceco(form_id) {
    return fnc_sec_reportes_concar_obtener_centros_costo().done(function (
        response
    ) {
        if (response && response.length) {
            let data = [];

            data.push({
                id: "",
                text: " -- Seleccione una opción -- ",
            });

            $.each(response, function () {
                data.push({
                    id: this.ceco,
                    text: this.ceco + " | " + this.local
                });
            });

            let $select = $(form_id).find("[name='sec-reportes-concar-sel-ceco']");
            if ($select.hasClass("select2-hidden-accessible")) {
                $select.select2().empty();
                $select.select2("destroy");
                $select.empty();
            }
            $select.select2({
                placeholder: " -- Seleccione una opción -- ",
                allowClear: true,
                data,
                width: "100%",
                dropdownParent: $(form_id).closest(".modal"),
            });
        }
    });
}

function fnc_sec_reportes_concar_refresh_select_cta_contable_from_frm_editar_concepto_facturable() {
    return fnc_sec_reportes_concar_obtener_cuentas_contables().done(function (
        response
    ) {
        if (response && response.length) {
            let data = [];

            data.push({
                id: "",
                text: " -- Seleccione una opción -- ",
            });

            $.each(response, function () {
                data.push({
                    id: this.cta_contable,
                    text: this.cta_contable + " | " + this.concar
                });
            });

            let form_id = "#sec-reportes-concar-frm-editar-concepto-facturable";
            let $select = $(form_id).find(
                "[name='sec-reportes-concar-sel-cta-contable']"
            );
            if ($select.hasClass("select2-hidden-accessible")) {
                $select.select2().empty();
                $select.select2("destroy");
                $select.empty();
            }
            $select.select2({
                placeholder: " -- Seleccione una opción -- ",
                allowClear: true,
                data,
                width: "100%",
                dropdownParent: $(form_id).closest(".modal"),
            });
        }
    });
}

function fnc_sec_reportes_concar_create_select_bancos_activos(
    row,
    selected_value
) {
    let bancos_activos =
        fnc_sec_reportes_concar_obtener_bancos_activos_localstorage();
    let select = $("<select>")
        .attr({
            id: "sec-reportes-concar-sel-banco-id-" + row,
            name: "sec-reportes-concar-sel-banco-id[]",
        })
        .addClass("form-control");
    let first_option = $("<option>")
        .attr("value", "")
        .text(" -- Seleccione una opción -- ");

    select.append(first_option);
    first_option.attr("selected", true);

    $.each(bancos_activos, function () {
        let option = $("<option>").attr("value", this.id).text(this.nombre);
        select.append(option);
        if (this.id == selected_value) {
            first_option.attr("selected", false);
            option.attr("selected", true);
        }
    });

    return select;
}

function fnc_sec_reportes_concar_eliminar_cuenta_contable(id) {
    let data = {
        accion: "eliminar-cuenta-contable",
        id,
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_eliminar_cuenta_contable", "data": data});
    return ajax_request({
        data,
        dataType: "text"
    });
}

function fnc_sec_reportes_concar_obtener_proveedores() {
    let data = {
        accion: "get-proveedores",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_proveedores", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_obtener_nombre_proveedores() {
    let data = {accion: "get-concar-proveedores"};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_nombre_proveedores", "data": data});
    return ajax_request({data});
}

function fnc_sec_reportes_concar_obtener_proveedor(id) {
    let data = {accion: "get-proveedor", id};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_proveedor", "data": data});
    return ajax_request({data});
}

function fnc_sec_reportes_concar_editar_proveedor() {
    let form_id = "#sec-reportes-concar-frm-editar-proveedor";
    let form = $(form_id);
    let id = parseInt(form.find("[name='sec-reportes-concar-txt-id']").val());
    let nombre = form.find("[name='sec-reportes-concar-txt-nombre']").val();
    let ruc = form.find("[name='sec-reportes-concar-txt-ruc']").val();
    let data = {
        id,
        nombre,
        ruc,
        accion: "editar-proveedor",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_editar_proveedor", "data": data});
    return ajax_request(
        {
            data,
            dataType: "text",
        },
        function () {
            $("#sec-reportes-concar-mdl-editar-proveedor").modal("hide");
            swal(
                {
                    title: "Éxito",
                    text: "La operación se realizó exitosamente.",
                    type: "success",
                    timer: 3000,
                    closeOnConfirm: true
                },
                function () {
                    swal.close();
                    fnc_sec_reportes_concar_obtener_proveedores().done(function (
                        response
                    ) {
                        fnc_sec_reportes_concar_renderizar_tabla_proveedores(response);
                    });
                }
            );
        }
    );
}

function fnc_sec_reportes_concar_eliminar_proveedor(id) {
    let data = {
        accion: "eliminar-proveedor",
        id,
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_eliminar_proveedor", "data": data});
    return ajax_request({
        data,
        dataType: "text",
    });
}

function fnc_sec_reportes_concar_obtener_concepto_facturable(id, concepto) {
    let data = {accion: "get-concepto-facturable", id, concepto};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_concepto_facturable", "data": data});
    return ajax_request({data});
}

function fnc_sec_reportes_concar_editar_concepto_facturable() {
    let form_id = "#sec-reportes-concar-frm-editar-concepto-facturable";
    let form = $(form_id);
    let id = form.find("[name='sec-reportes-concar-txt-id']").val();
    let concepto = form.find("[name='sec-reportes-concar-txt-concepto']").val();
    let cta_contable = form
        .find("[name='sec-reportes-concar-sel-cta-contable']")
        .val();
    let archivo_proveedor_id = form
        .find("[name='sec-reportes-concar-txt-archivo-proveedor-id']")
        .val();
    let data = {
        id,
        concepto,
        cta_contable,
        accion: "editar-concepto-facturable",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_editar_concepto_facturable", "data": data});
    return ajax_request(
        {
            data,
            dataType: "text",
        },
        function () {

            swal(
                {
                    title: "Éxito",
                    text: "La operación se realizó exitosamente.",
                    type: "success",
                    timer: 3000,
                    closeOnConfirm: true
                },
                function () {
                    swal.close();

                    $("#sec-reportes-concar-mdl-editar-concepto-facturable").modal("hide");

                    if (archivo_proveedor_id) {
                        fnc_sec_reportes_concar_obtener_conceptos_facturables_pendientes(
                            archivo_proveedor_id
                        ).done(function (response) {
                            fnc_sec_reportes_concar_renderizar_tabla_conceptos_facturables(response);
                        });
                    } else {
                        fnc_sec_reportes_concar_obtener_conceptos_facturables().done(function (
                            response
                        ) {
                            fnc_sec_reportes_concar_renderizar_tabla_conceptos_facturables(response);
                        });
                    }
                    swal.close();
                }
            );
        }
    );
}

function fnc_sec_reportes_concar_eliminar_concepto_facturable(id) {
    let data = {
        accion: "eliminar-concepto-facturable",
        id
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_eliminar_concepto_facturable", "data": data});
    return ajax_request({
        data,
        dataType: "text",
    });
}

function fnc_sec_reportes_concar_obtener_conceptos_facturables_pendientes(archivo_proveedor_id) {
    let data = {
        archivo_proveedor_id,
        accion: "get-conceptos-facturables-pendientes",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_conceptos_facturables_pendientes", "data": data});
    return ajax_request({
        data,
    });
}

function fnc_sec_reportes_concar_obtener_codigos_comercio_pendientes(
    archivo_proveedor_id
) {
    let data = {
        archivo_proveedor_id,
        accion: "get-codigos-comercio-pendientes"
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_codigos_comercio_pendientes", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_editar_codigo_comercio() {
    let form_id = "#sec-reportes-concar-mdl-editar-codigo-comercio";
    let form = $(form_id);
    let id = form.find("[name='sec-reportes-concar-txt-id']").val();
    let cod_comercio = form
        .find("[name='sec-reportes-concar-txt-cod-comercio']")
        .val();
    let ceco = form.find("[name='sec-reportes-concar-sel-ceco']").val();
    let archivo_proveedor_id = form
        .find("[name='sec-reportes-concar-txt-archivo-proveedor-id']")
        .val();
    let data = {
        id,
        cod_comercio,
        ceco,
        accion: "editar-codigo-comercio",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_editar_codigo_comercio", "data": data});
    return ajax_request(
        {
            data,
            dataType: "text",
        },
        function () {
            swal(
                {
                    title: "Éxito",
                    text: "La operación se realizó exitosamente.",
                    type: "success",
                    timer: 3000,
                    closeOnConfirm: true
                },
                function () {
                    $("#sec-reportes-concar-mdl-editar-codigo-comercio").modal("hide");
                    if (archivo_proveedor_id) {
                        fnc_sec_reportes_concar_obtener_codigos_comercio_pendientes(
                            archivo_proveedor_id
                        ).done(function (response) {
                            fnc_sec_reportes_concar_renderizar_tabla_codigos_comercio(response);
                        });
                    } else {
                        fnc_sec_reportes_concar_obtener_codigos_comercio().done(function (
                            response
                        ) {
                            fnc_sec_reportes_concar_renderizar_tabla_codigos_comercio(response);
                        });
                    }
                    swal.close();
                }
            );
        }
    );
}

function fnc_sec_reportes_concar_eliminar_codigo_comercio(id) {
    let data = {
        accion: "eliminar-codigo-comercio",
        id,
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_eliminar_codigo_comercio", "data": data});
    return ajax_request({
        data,
        dataType: "text",
    });
}

function fnc_sec_reportes_concar_obtener_bancos() {
    let data = {accion: "get-bancos"};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_bancos", "data": data});
    return ajax_request({data});
}

function fnc_sec_reportes_concar_obtener_bancos_activos() {
    let data = {accion: "get-bancos-activos"};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_bancos_activos", "data": data});
    return ajax_request({data});
}

function fnc_sec_reportes_concar_obtener_banco(id) {
    let data = {accion: "get-banco", id};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_banco", "data": data});
    return ajax_request({data});
}

function fnc_sec_reportes_concar_editar_banco() {
    let form_id = "#sec-reportes-concar-frm-editar-banco";
    let form = $(form_id);
    let id = parseInt(form.find("[name='sec-reportes-concar-txt-id']").val());
    let nombre = form.find("[name='sec-reportes-concar-txt-nombre']").val();
    let razon_social = form
        .find("[name='sec-reportes-concar-txt-razon-social']")
        .val();
    let ruc = form.find("[name='sec-reportes-concar-txt-ruc']").val();
    let estado = form.find("[name='sec-reportes-concar-sel-estado']").val();
    let data = {
        id,
        nombre,
        razon_social,
        ruc,
        estado,
        accion: "editar-banco"
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_editar_banco", "data": data});
    return ajax_request(
        {
            data,
            dataType: "text",
        },
        function () {
            $("#sec-reportes-concar-mdl-editar-banco").modal("hide");
            swal(
                {
                    title: "Éxito",
                    text: "La operación se realizó exitosamente.",
                    type: "success",
                    timer: 3000,
                    closeOnConfirm: true
                },
                function () {
                    swal.close();
                    fnc_sec_reportes_concar_obtener_bancos().done(function (response) {
                        fnc_sec_reportes_concar_renderizar_tabla_bancos(response);
                        fnc_sec_reportes_concar_obtener_bancos_activos().done(function (
                            bancos_activos
                        ) {
                            fnc_sec_reportes_concar_guardar_bancos_activos_localstorage(
                                bancos_activos
                            );
                        });
                    });
                }
            );
        }
    );
}

function fnc_sec_reportes_concar_eliminar_banco(id) {
    let data = {
        accion: "eliminar-banco",
        id
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_eliminar_banco", "data": data});
    return ajax_request({
        data,
        dataType: "text"
    });
}

function fnc_sec_reportes_concar_obtener_detalle_bancos(archivo_proveedor_id) {
    let data = {
        accion: "get-detalle-bancos",
        archivo_proveedor_id
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_detalle_bancos", "data": data});
    return ajax_request({
        data
    });
}

function fnc_sec_reportes_concar_eliminar_detalle_banco(id) {
    let data = {
        accion: "eliminar-detalle-banco",
        id
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_eliminar_detalle_banco", "data": data});
    return ajax_request({
        data,
        dataType: "text"
    });
}

function fnc_sec_reportes_concar_editar_detalle_bancos() {
    let table_id = "#sec-reportes-concar-tbl-detalle-bancos";
    let table = $(table_id).DataTable();
    let submit_data = [];
    table.rows().every(function () {
        let element = {};
        let node = this.node();
        let data = this.data();
        element["id"] = data.id;
        element["archivo_proveedor_id"] = data.archivo_proveedor_id;
        $.each($(node).find("input, select"), function () {
            let name = $(this).attr("name");
            if (name == "sec-reportes-concar-sel-banco-id[]") {
                element["banco_id"] = $(this).val();
            }
            if (name == "sec-reportes-concar-txt-importe[]") {
                element["importe"] = $(this).val();
            }
        });
        submit_data.push(element);
    });

    auditoria_send({"proceso": "fnc_sec_reportes_concar_editar_detalle_bancos", "data": submit_data});

    let jsonString = JSON.stringify(submit_data);

    ajax_request(
        {
            data: {data: jsonString, accion: "editar-detalle-banco"},
            dataType: "text"
        },
        function () {
            swal(
                {
                    title: "Éxito",
                    text: "La operación se realizó exitosamente.",
                    type: "success",
                    timer: 3000,
                    closeOnConfirm: true
                },
                function () {
                    swal.close();
                    let archivo_proveedor_id = $(
                        "#sec-reportes-concar-frm-editar-detalle-bancos"
                    )
                        .find("[name='sec-reportes-concar-txt-archivo-proveedor-id']")
                        .val();
                    fnc_sec_reportes_concar_obtener_detalle_bancos(
                        archivo_proveedor_id
                    ).done(function (response) {
                        fnc_sec_reportes_concar_renderizar_tabla_detalle_bancos(response);
                        apply_select2_plugin_to_selects_column(
                            table_id,
                            0,
                            "#sec-reportes-concar-mdl-detalle-bancos"
                        );
                    });
                }
            );
        }
    );
}

function fnc_sec_reportes_concar_guardar_bancos_activos_localstorage(
    bancos_activos
) {
    localStorage.setItem("bancos-activos", JSON.stringify(bancos_activos));
}

function fnc_sec_reportes_concar_obtener_bancos_activos_localstorage() {
    return JSON.parse(localStorage.getItem("bancos-activos"));
}

function fnc_sec_reportes_concar_obtener_numeros_abonado() {
    let data = {accion: "get-numeros-abonados"};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_numeros_abonado", "data": data});
    return ajax_request({data});
}

function fnc_sec_reportes_concar_eliminar_numero_abonado(id) {
    let data = {
        accion: "eliminar-numero-abonado",
        id
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_eliminar_numero_abonado", "data": data});
    return ajax_request({
        data,
        dataType: "text"
    });
}

function fnc_sec_reportes_concar_obtener_numero_abonado(id, nro_abonado) {
    let data = {accion: "get-numero-abonado", id, nro_abonado};
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_numero_abonado", "data": data});
    return ajax_request({data});
}

function fnc_sec_reportes_concar_editar_numero_abonado() {
    let form_id = "#sec-reportes-concar-frm-editar-numero-abonado";
    let form = $(form_id);
    let id = form.find("[name='sec-reportes-concar-txt-id']").val();
    let nro_abonado = form.find("[name='sec-reportes-concar-txt-nro-abonado']").val();
    let ceco = form.find("[name='sec-reportes-concar-sel-ceco']").val();
    let archivo_proveedor_id = form.find("[name='sec-reportes-concar-txt-archivo-proveedor-id']").val();
    let data = {
        id,
        nro_abonado,
        ceco,
        accion: "editar-numero-abonado",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_editar_numero_abonado", "data": data});
    return ajax_request(
        {
            data,
            dataType: "text",
        },
        function () {
            $("#sec-reportes-concar-mdl-editar-numero-abonado").modal("hide");
            swal(
                {
                    title: "Éxito",
                    text: "La operación se realizó exitosamente.",
                    type: "success",
                    timer: 3000,
                    closeOnConfirm: true
                },
                function () {
                    swal.close();
                    if (archivo_proveedor_id) {
                        fnc_sec_reportes_concar_obtener_numeros_abonado_pendientes(
                            archivo_proveedor_id
                        ).done(function (response) {
                            fnc_sec_reportes_concar_renderizar_tabla_numeros_abonado(response);
                        });
                    } else {
                        fnc_sec_reportes_concar_obtener_numeros_abonado().done(function (
                            response
                        ) {
                            fnc_sec_reportes_concar_renderizar_tabla_numeros_abonado(response);
                        });
                    }
                }
            );
        }
    );
}

function fnc_sec_reportes_concar_obtener_numeros_abonado_pendientes(
    archivo_proveedor_id
) {
    let data = {
        archivo_proveedor_id,
        accion: "get-numeros-abonado-pendientes",
    };
    auditoria_send({"proceso": "fnc_sec_reportes_concar_obtener_numeros_abonado_pendientes", "data": data});
    return ajax_request({
        data,
    });
}

function reorder_input_ids_from_datatable(table_id) {
    let table = $(table_id).DataTable();
    table.rows().every(function (rowIdx, tableLoop, rowLoop) {
        let inputs = $(this.node()).find(
            "input, select, textarea, [contenteditable]"
        );
        $.each(inputs, function () {
            let new_id = this.id.replace(/\d+/g, "") + rowIdx;
            $(this).attr("id", new_id);
        });
    });
}

function apply_select2_plugin_to_selects_column(
    table_id,
    index_column,
    dropdownParent
) {
    let table = $(table_id).DataTable();
    table.rows().every(function () {
        let row = $(this.node());
        let td = row.find("td");
        let select = td.eq(index_column).find("select");
        if (!select.hasClass("select2-hidden-accessible")) {
            select.select2({
                placeholder: " -- Seleccione una opción -- ",
                allowClear: true,
                width: "100%",
                dropdownParent: $(dropdownParent),
            });
        }
    });
}

function replaceAll(str, find, replace) {
    return str.replace(new RegExp(find, "g"), replace);
}

function get_datetime_columns() {
    if (!localStorage.hasOwnProperty("datetime_columns")) {
        try {
            let data = {accion: "get-datetime-columns"};
            auditoria_send({"proceso": "get_datetime_columns", "data": data});
            ajax_request(
                {data},
                function (response) {
                    localStorage.setItem("datetime_columns", response);
                },
                {async: false}
            );
        } catch (ex) {
            localStorage.removeItem("datetime_columns");
        }
    }
    datetime_columns = localStorage.getItem("datetime_columns");
    if (datetime_columns) {
        return datetime_columns.split(",");
    }
    return false;
}

function ajax_request(new_settigns, success_callback, complete_callback) {
    let settings = {
        data: null,
        type: "POST",
        dataType: "json",
        url: "sys/get_reportes_concar.php",
        success: function (response) {
            if (success_callback && typeof success_callback === "function") {
                success_callback(response);
            }
        },
        beforeSend: function () {
            loading(true);
        },
        complete: function () {
            if (complete_callback && typeof complete_callback === "function") {
                complete_callback();
            }
            loading(false);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log("errorThrown", errorThrown);
            console.log("jqXHR.responseText", jqXHR.responseText);
            console.log("textStatus", textStatus);

            if (errorThrown === "Bad Request") {
                textStatus = "warning";
            }

            let responseText = jqXHR.responseText || "Ha ocurrido un error";

            swal("¡Alerta!", responseText, textStatus);
        },
    };
    if (new_settigns) {
        Object.assign(settings, new_settigns);
    }
    return $.ajax(settings);
}

function reset_form(form_id) {
    $(form_id)[0].reset();
    if ($(form_id).data("validator")) {
        $(form_id).validate().resetForm();
    }
}