function sec_tesoreria_retiros_web(){
    let retiros_web_search_box = {
        filters: {
            start_date: $("#start_date_filter"),
            start_time: $("#start_time_filter"),
            end_date: $("#end_date_filter"),
            end_time: $("#end_time_filter"),
            state: $("#state_filter"),
        },
        buttons: {
            search: $("#btn_tesoreria_retiros_search"),
            clear_filters: $("#clear_filters_btn"),
        },
        getData: function(){
            return {
                start_date: this.filters.start_date.val(),
                start_time: this.filters.start_time.val(),
                end_date: this.filters.end_date.val(),
                end_time: this.filters.end_time.val(),
                state: this.filters.state.val(),
            }
        }
    };

    set_retiros_web_actions(retiros_web_search_box)
    set_retiros_web_defaults(retiros_web_search_box)
    set_tesoreria_retiros_web_local_storage_data(retiros_web_search_box)
}

const set_retiros_web_defaults = (retiros_web_filters) => {
    retiros_web_filters.filters.start_date.datetimepicker({ format: 'DD-MM-YY' })
    retiros_web_filters.filters.end_date.datetimepicker({ format: 'DD-MM-YY' })
    retiros_web_filters.filters.start_time.datetimepicker({ format: 'HH:mm:ss' })
    retiros_web_filters.filters.end_time.datetimepicker({ format: 'HH:mm:ss' })

    retiros_web_filters.filters.start_date.val(moment().format("DD-MM-YY"))
    retiros_web_filters.filters.start_time.val("00:00:00")
    retiros_web_filters.filters.end_date.val(moment().add(1,'days').format("DD-MM-YY"))
    retiros_web_filters.filters.end_time.val("00:00:00")
    retiros_web_filters.filters.state.val("-1")
}

const set_retiros_web_actions = (retiros_web_search_box) => {
    //Clear Filters
    retiros_web_search_box.buttons.clear_filters.on('click', () => {
        set_retiros_web_defaults(retiros_web_search_box)
    })

    //Consultar Button
    retiros_web_search_box.buttons.search.on('click', () => {
        set_retiros_web_local_storage_data(retiros_web_search_box)
        get_retiros_web(retiros_web_search_box)
    })

    // Kashio Action Modal Close Button
    $("#kashioActionModalCloseButton").on('click', function(e) {
        m_reload()
    })
}

const set_tesoreria_retiros_web_local_storage_data = (retiros_web_search_box) => {
    let ls_start_date_filter = localStorage.getItem("tesoreria_retiros_start_date");
    let tesoreria_retiros_start_time_filter = localStorage.getItem("tesoreria_retiros_start_time");
    let tesoreria_retiros_end_date_filter = localStorage.getItem("tesoreria_retiros_end_date");
    let tesoreria_retiros_end_time_filter = localStorage.getItem("tesoreria_retiros_end_time");
    let tesoreria_retiros_status_filter = localStorage.getItem("tesoreria_retiros_state");

    if (ls_start_date_filter !== 'undefined' && ls_start_date_filter !== null)
        retiros_web_search_box.filters.start_date.val(ls_start_date_filter)
    if (tesoreria_retiros_start_time_filter !== 'undefined' && tesoreria_retiros_start_time_filter !== null)
        retiros_web_search_box.filters.start_time.val(tesoreria_retiros_start_time_filter)
    if (tesoreria_retiros_end_date_filter !== 'undefined' && tesoreria_retiros_end_date_filter !== null)
        retiros_web_search_box.filters.end_date.val(tesoreria_retiros_end_date_filter)
    if (tesoreria_retiros_end_time_filter !== 'undefined' && tesoreria_retiros_end_time_filter !== null)
        retiros_web_search_box.filters.end_time.val(tesoreria_retiros_end_time_filter)
    if (tesoreria_retiros_status_filter !== 'undefined' && tesoreria_retiros_status_filter !== null)
        retiros_web_search_box.filters.state.val(tesoreria_retiros_status_filter)
}

//ACTIONS
const get_retiros_web = (retiros_web_search_box) => {
    loading(true)
    const tblRetirosWeb = $("#tblRetirosWeb")
    let data = retiros_web_search_box.getData()

    auditoria_send({"proceso":"tesoreria_retiros_web_get_retiros","data":data});
    $.post("sys/get_tesoreria_retiros_web.php",
        {
            "get_tesoreria_retiros_api" : data
        },
        function (response){
            let retiros = []
            try {
                retiros = JSON.parse(response)
            }
            catch(e) {
                console.log(e)
                loading(false)
                return
            }

            if ($.fn.DataTable.isDataTable("#tblRetirosWeb")) {
                tblRetirosWeb.DataTable().clear().destroy()
            }

            tblRetirosWeb.DataTable({
                data : retiros["data"],
                scrollY: "300px",
                scrollX: true,
                scrollCollapse: true,
                dom: "<'row'<'col-sm-6'l><'col-sm-6'Bf>>" +
                    "<'row'<'col-sm-12't>>" +
                    "<'row'<'col-sm-6'i><'col-sm-6'p>>",
                columnDefs:[
                    {
                        'targets': 0,
                        'checkboxes': {
                            'selectRow': true
                        }
                    }
                ],
                'select': {
                    'style': 'multi'
                },
                'order': [[1, 'asc']],
                rowId: function(row){
                    return 'retiro-' + row.id
                },
                "lengthMenu": [[10, 25, 50, 100, 200, 400, -1], [10, 25, 50, 100, 200, 400, "Todos"]],
                buttons: {
                    buttons: [
                        {
                            extend: "csvHtml5",
                            exportOptions : {
                                orthogonal: "exportcsv",
                            }
                        },
                        {
                            text: 'Enviar a KashIO',
                            className: 'btn-primary-border',
                            action: function (e, dt, node, config) {
                                send_to_kashio_action()
                            }
                        },
                        {
                            text: 'Pagar',
                            className: 'btn-info-border hidden',
                            action: function (e, dt, node, config) {
                                //withdrawal_action("pay")
                            }
                        },
                        {
                            text: 'Rechazar',
                            className: 'btn-danger-border hidden',
                            action: function (e, dt, node, config) {
                                //withdrawal_action("cancel")
                            }
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
                columns: [
                    {
                        title: "",
                        data: "id",
                        defaultContent: "-"
                    },
                    {
                        title: "Id solicitud",
                        data: "id",
                        defaultContent: "-"
                    },
                    {
                        title: "ID Cliente",
                        data: "client_id",
                        defaultContent: "-",
                    },
                    {
                        title: "Nombres",
                        data: "client_name",
                        defaultContent: "-",
                    },
                    {
                        title: "Monto",
                        data: "amount",
                        defaultContent: "-",
                        render: function ( data, type, row, meta ) {
                            let formatter = new Intl.NumberFormat('en-US', {
                                maximumFractionDigits: 3,
                                minimumFractionDigits: 2
                            });
                            return formatter.format(data);
                        }
                    },
                    {
                        title: "Estado BC",
                        data: "state_name",
                        defaultContent: "-",
                    },
                    {
                        title: "Estado AT",
                        data: "state_at_name",
                        defaultContent: "-",
                    },
                    {
                        title: "Fecha de Solicitud",
                        data: "request_time",
                        defaultContent: "-",
                    },
                    {
                        title: "Fecha de Aprobación",
                        data: "allow_time",
                        defaultContent: "-",
                    },
                    {
                        title: "Fecha de Modificación",
                        data: "state_at_update_time",
                        defaultContent: "-",
                    },
                    {
                        title: "Tipo de Pago",
                        data: "payment_type",
                        defaultContent: "-",
                    },
                    {
                        title: "Banco",
                        data: "bank",
                        defaultContent: "-",
                        render: function(data, type, row, meta){
                            if(row.bank === false){
                                return `No especificado`;
                            }else{
                                return row.bank;
                            }
                        },
                    },
                    {
                        title: "Account Holder",
                        data: "account_holder",
                        defaultContent: "-",
                        className: 'dt-body-center'
                    },
                    {
                        title: "BTag",
                        data: "btag",
                        defaultContent: "-",
                    },
                    {
                        data: "currency",
                        defaultContent: "PEN",
                        visible: false,
                    },
                    {
                        data: "phone",
                        defaultContent: "-",
                        visible: false,
                    },
                    {
                        data: "doc_number",
                        defaultContent: "DNI",
                        visible: false,
                    },
                    {
                        data: "bank_account",
                        defaultContent: "-",
                        visible: false,
                    },
                    {
                        data: "bank_cci",
                        defaultContent: "-",
                        visible: false,
                    },
                    {
                        data: "bank_account_type",
                        defaultContent: "-",
                        visible: false,
                    },

                ],
            })

            loading(false)
        })
}

const set_retiros_web_local_storage_data = (retiros_web_search_box) => {
    for (let key in retiros_web_search_box.filters){
        if (!retiros_web_search_box.filters.hasOwnProperty(key)) continue;
        localStorage.setItem("tesoreria_retiros_" + key, retiros_web_search_box.filters[key].val())
    }
}

const send_to_kashio_action = () => {
    if (!$.fn.DataTable.isDataTable("#tblRetirosWeb")) return

    let kashio_modal = {
        container: $("#kashioActionModal"),
        process_input: $("#kashioActionProcessInput"),
        progress_bar: $("#kashioActionProgressBar"),
        buttons: {
            close: $("#kashioActionModalCloseButton")
        }
    }

    // Modal reset
    kashio_modal.buttons.close.prop("disabled", true)
    kashio_modal.progress_bar.css("width", "0")
    kashio_modal.progress_bar.find("span").text("0%")

    // Getting selected Ids
    let retiros_table = $("#tblRetirosWeb").DataTable()
    let selected_rows = retiros_table.column(0).checkboxes.selected()
    let selected_rows_formatted = []
    $.each(selected_rows, function(index, rowId){
        selected_rows_formatted.push('#retiro-' + rowId)
    })
    let rows = retiros_table.rows(selected_rows_formatted).data()
    if (!rows) return

    let selected_rows_length = {
        total : rows.length,
        allowed : rows.filter( item => item.state_at_name === "Allowed").length,
        enviado : rows.filter( item => item.state_name === "Enviado").length,
        observado : rows.filter( item => item.state_name === "Observado").length,
    }

    if (selected_rows_length.total === selected_rows_length.allowed){
        swal({
                title: "Confirmación de Envío",
                text: `¿Está seguro que desea enviar estos ${selected_rows_length.allowed} retiros?`,
                type: "info",
                showCancelButton: true,
                confirmButtonText: "Sí",
                cancelButtonText:"Regresar"
            },
            function (){
                swal.close()
                execute_kashio_action(rows, kashio_modal)
            }
        )
    }else{
        swal({
            title: "No se pudo enviar",
            text: "Ha seleccionado retiros con estado diferente a: Allowed",
            type: "warning",
            showCancelButton: true
        })
    }
}

const execute_kashio_action = async (rows, modal) => {
    if (rows.length === 0) return
    console.log(rows)

    modal.container.modal("show")
    modal.process_input.val("INICIANDO PROCESO:")
    modal.process_input.val(modal.process_input.val() + "\n Retiros por procesar: " + rows.length)

    let errors = false
    for (let i = 0; i < rows.length; i++) {
        modal.process_input.val(modal.process_input.val() + `\n Ejecutando proceso: ${i + 1}/${rows.length}`).trigger('change');
        let data = {
            id: rows[i].id,
            client: {
                id: rows[i].client_id,
                name: rows[i].client_name,
                phone: rows[i].phone,
                document_type: rows[i].account_holder ?? "DNI",
                document_number: rows[i].doc_number,
                bank: {
                    name: rows[i].bank,
                    account_number: rows[i].bank_account,
                    type: rows[i].bank_account_type,
                    cci: rows[i].bank_cci,
                }
            },
            total: rows[i].amount,
            currency: rows[i].currency,
        }

        const postPromise = new Promise((resolve) => {
            auditoria_send({"proceso":"tesoreria_retiros_web_execute_kashio","data":data});
            $.post(
                'sys/get_tesoreria_retiros_web.php',
                {
                    "post_retiro_kashio_api": data
                },
                function (r) {
                    const responseCheck = (r) => {
                        try {
                            return JSON.parse(r)
                        } catch (e) {
                            return false
                        }
                    };
                    const response = responseCheck(r);

                    if (!response) {
                        modal.process_input.val(modal.process_input.val() + `\n ERROR: No se pudo conectar con el API`).trigger('change');
                        swal({
                            title: "Error",
                            text: "No se pudo conectar con el API",
                            type: "warning",
                            showCancelButton: true
                        })
                        modal.buttons.close.prop("disabled", false)
                        return resolve(false)
                    }

                    if (response.data.http_code !== 200) {
                        modal.process_input.val(modal.process_input.val() + `\n ERROR: ${JSON.stringify(response.data)}`).trigger('change');
                        swal({
                            title: "Error",
                            text: "Respuesta inadecuada del API, revise los logs",
                            type: "warning",
                            showCancelButton: true
                        })
                        modal.buttons.close.prop("disabled", false)
                        return resolve(false)
                    }
                    modal.process_input.val(modal.process_input.val() + `\n Proceso parcial finalizado`)
                    let percentageProgress = Math.round(100 * (i + 1) / rows.length)
                    modal.progress_bar.css("width", percentageProgress + "%")
                    modal.progress_bar.find("span").text(percentageProgress + "%")
                    return resolve(true)
                })
        })

        let result = await postPromise.then(success => {
            return success
        })
        if (!result) {
            errors = true
            break
        }
    }
    if (!errors) modal.process_input.val(modal.process_input.val() + "\nPROCESO FINALIZADO CON ÉXITO").trigger('change');
    else modal.process_input.val(modal.process_input.val() + "\nPROCESO FINALIZADO CON ERRORES").trigger('change');
    modal.buttons.close.prop("disabled", false)
}