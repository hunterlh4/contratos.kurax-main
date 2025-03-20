var sec_consolidado_free_games_filtro_locales = false;
var sec_consolidado_free_games_filtro_canales_de_venta = false;
var sec_consolidado_free_games_razon_social_id = 5;
$(document).ready(function () {
    sec_consolidado_free_games()
});

function sec_consolidado_free_games() {

    sec_consolidado_free_games_events();
    sec_consolidado_free_games_settings();
    $.when(sec_consolidado_free_games_get_zonas_by_razon_social(sec_consolidado_free_games_razon_social_id)).then(function () {
        sec_consolidado_free_games_change_select_zonas();
    });

    function sec_consolidado_free_games_change_select_zonas() {
        let razon_social_id = sec_consolidado_free_games_razon_social_id;
        let $select_zona = $("#sec_consolidado_free_games_zona");
        let zona_ids = $select_zona.val();

        if (zona_ids !== null && zona_ids.length > 0) {

            $.when(sec_consolidado_free_games_get_locales_by_zonas(zona_ids, razon_social_id)).then(function () {
                sec_consolidado_free_games_change_select_local();
            });

        } else {
            $("#sec_consolidado_free_games_local > option").remove();
            $("#sec_consolidado_free_games_supervisor > option").remove();
        }
    }

    function sec_consolidado_free_games_change_select_local() {
        let razon_social_id = sec_consolidado_free_games_razon_social_id;
        let zona_ids = $("#sec_consolidado_free_games_zona").val();
        let local_ids = $("#sec_consolidado_free_games_local").val();
        if (local_ids !== null && local_ids.length) {
            sec_consolidado_free_games_get_supervisores_by_locales(local_ids, zona_ids, razon_social_id);
        } else {
            $("#sec_consolidado_free_games_supervisor > option").remove();
        }
    }

    function sec_consolidado_free_games_get_zonas_by_razon_social(razon_social_id) {
        return $.ajax({
            data: {
                opt: 'get_zonas_by_razon_social',
                razon_social_id
            },
            type: "POST",
            dataType: "json",
            url: "/sys/get_consolidado_free_games.php",
        }).done(function (response) {
            if (response) {
                if (!response.error) {
                    let $select_zonas = $("#sec_consolidado_free_games_zona");
                    $select_zonas.find('option').remove();
                    if(response.data.length > 0) {
                        let zonas = response.data;
                        $select_zonas.append('<option value="all" selected="selected" >Todos</option>');
                        $.each(zonas, function (index, val) {
                            $select_zonas.append(`<option value="${val.id}">${val.nombre}</option>`);
                        });
                    }
                } else {
                    swal({
                        title: 'Error',
                        text: response.error,
                        type: "warning",
                        closeOnConfirm: true
                    });
                }
            }

        })
            .fail(function (jqXHR, textStatus, errorThrown) {
                swal({
                    title: errorThrown + ' (' + textStatus + ')',
                    html: true,
                    text: jqXHR.responseText,
                    type: "warning",
                    closeOnConfirm: true
                });
            });
    }

    function sec_consolidado_free_games_get_locales_by_zonas(zona_ids, razon_social_id) {
        return $.ajax({
            data: {
                opt: 'get_locales_by_zonas',
                zona_ids,
                razon_social_id
            },
            type: 'POST',
            dataType: 'json',
            url: "/sys/get_consolidado_free_games.php",
            async: 'false'
        }).done(function (response) {
            if (response) {
                if (!response.error) {
                    let locales = response.data;
                    let $select_local = $('#sec_consolidado_free_games_local');
                    $select_local.find('option').remove();
                    $select_local.append('<option value="all" selected="selected" >Todos</option>');
                    $.each(locales, function (index, val) {
                        $select_local.append(`<option value="${val.id}">${val.nombre}</option>`);
                    });
                } else {
                    swal({
                        title: 'Error',
                        text: response.error,
                        type: "warning",
                        timer: 3000,
                        closeOnConfirm: true
                    });
                }
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            swal({
                title: errorThrown + ' (' + textStatus + ')',
                html: true,
                text: jqXHR.responseText,
                type: "warning",
                timer: 3000,
                closeOnConfirm: true
            });
        })
    }

    function sec_consolidado_free_games_get_supervisores_by_locales(local_ids, zona_ids, razon_social_id) {
        return $.ajax({
            data: {
                opt: 'get_supervisores_by_locales',
                local_ids,
                zona_ids,
                razon_social_id
            },
            type: 'POST',
            dataType: 'json',
            url: '/sys/get_consolidado_free_games.php',
        })
            .done(function (response) {
                if (response) {
                    if (!response.error) {
                        let supervisores = response.data;
                        let $select_supervisor = $('#sec_consolidado_free_games_supervisor');
                        $select_supervisor.find('option').remove();
                        $select_supervisor.append('<option data-usuario-id="" value="all" selected="selected" >Todos</option>');
                        $.each(supervisores, function (index, val) {
                            $select_supervisor.append(`<option data-usuario-id="${val.usuario_id}" value="${val.personal_id}">${val.nombre}</option>`);
                        });
                    } else {
                        swal({
                            title: 'Error',
                            text: response.error,
                            type: "warning",
                            timer: 3000,
                            closeOnConfirm: true
                        });
                    }
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                swal({
                    title: errorThrown + ' (' + textStatus + ')',
                    html: true,
                    text: jqXHR.responseText,
                    type: "warning",
                    closeOnConfirm: true
                });
            })
    }

    function sec_consolidado_free_games_settings() {

        $('.sec_consolidado_free_games_select2').select2({
            closeOnSelect: false,
            allowClear: false,
            placeholder: '-- Seleccione --'
        });

        if (localStorage.getItem("sec_consolidado_free_games_filtro_locales")) {
            sec_consolidado_free_games_filtro_locales = localStorage.getItem("sec_consolidado_free_games_filtro_locales");
        }
        if (localStorage.getItem("sec_consolidado_free_games_filtro_canales_de_venta")) {
            sec_consolidado_free_games_filtro_canales_de_venta = localStorage.getItem("sec_consolidado_free_games_filtro_canales_de_venta");
        }
    }

    function sec_consolidado_free_games_get_liquidaciones() {
        loading(true);
        let data = {};
        data.zonas = $("#sec_consolidado_free_games_zona").val();
        data.locales = $("#sec_consolidado_free_games_local").val();
        data.canales_de_venta = $('#sec_consolidado_free_games_cdv').val();
        data.supervisores = $('#sec_consolidado_free_games_supervisor').val();
        data.concepto = $('#sec_consolidado_free_games_concepto').val();
        data.razon_social_id = sec_consolidado_free_games_razon_social_id;
        if ($("#sec_consolidado_free_games_estado_locales").hasClass('btn-success')) {
            data.estado_locales = "inactivos";
        } else {
            data.estado_locales = "activos";
        }

        data.opt = "consolidado_free_games";

        localStorage.setItem('sec_consolidado_free_games_filtro_locales', data.locales);
        localStorage.setItem('sec_consolidado_free_games_filtro_canales_de_venta', data.canales_de_venta);

        auditoria_send({"proceso": "sec_consolidado_free_games_get_liquidaciones", data});

        $.ajax({
            data,
            type: "POST",
            url: "/sys/get_consolidado_free_games.php",
            async: "false"
        }).done(function (response) {
            try {
                let json = jQuery.parseJSON(response);
                let obj = json.data;
                sec_consolidado_free_games_mostrar_datatable(obj);
            } catch (err) {
                swal({
                    title: 'Error',
                    text: err.message,
                    type: "warning",
                    timer: 3000,
                }, function () {
                    swal.close();
                    loading();
                });
            }
        })
            .fail(function (jqXHR, textStatus, errorThrown) {
                swal({
                    title: errorThrown + ' (' + textStatus + ')',
                    html: true,
                    text: jqXHR.responseText,
                    type: "warning",
                    closeOnConfirm: true
                });
            });
    }

    function sec_consolidado_free_games_mostrar_datatable(obj) {
        var datatable_data = obj.datatable_data;
        var meses = obj.meses;
        var totales = obj.totales;
        var concepto = $('#sec_consolidado_free_games_concepto').val();

        $('#tabla_sec_consolidado_agente tfoot').empty();
        $('#tabla_sec_consolidado_agente tfoot').append("<tr>");
        $('#tabla_sec_consolidado_agente tfoot tr')
            .append("<th>")
            .append("<th>")
            .append("<th>")
            .append("<th>")
            .append("<th>")
            .append("<th>")
            .append("<th>")
        ;

        $(meses).each(function (i, e) {
            $('#tabla_sec_consolidado_agente tfoot tr')
                .append("<th></th>")
        });
        var columnas = [];
        columnas.push({
            title: "ZONA",
            data: "ZONA",
            className: "tabla_sec_consolidado_agente_local_td",
            defaultContent: "---"
        });
        columnas.push({
            title: "DEPARTAMENTO",
            data: "DEPARTAMENTO",
            className: "tabla_sec_consolidado_agente_local_td",
            defaultContent: "---"
        });
        columnas.push({
            title: "PROVINCIA",
            data: "PROVINCIA",
            className: "tabla_sec_consolidado_agente_local_td",
            defaultContent: "---"
        });
        columnas.push({
            title: "DISTRITO",
            data: "DISTRITO",
            className: "tabla_sec_consolidado_agente_local_td",
            defaultContent: "---"
        });
        columnas.push({
            title: "NOMBRE SOP",
            data: "NOMBRE SOP",
            className: "tabla_sec_consolidado_agente_local_td",
            defaultContent: "---"
        });
        columnas.push({
            title: "NOMBRE TIENDA",
            data: "NOMBRE TIENDA",
            className: "tabla_sec_consolidado_agente_local_td",
            defaultContent: "---"
        });
        columnas.push({
            title: "CANAL DE VENTA",
            data: "CANAL DE VENTA",
            className: "tabla_sec_consolidado_agente_cdv_td",
            defaultContent: "---"
        });
        $(meses).each(function (i, e) {
            columnas.push({
                //title: e,
                title: moment(e, "YYYY-MM").locale("es").format("MMMYY").replace(".", "-"),
                data: e,
                className: "text-right tabla_sec_consolidado_agente_tfoot_meses",
                defaultContent: "-",
                render: function (data, type, row, meta) {
                    if (concepto != 'CANTIDAD DE TICKETS') {
                        let formatter = new Intl.NumberFormat('en-US', {
                            maximumFractionDigits: 3,
                            minimumFractionDigits: 2
                        });
                        return formatter.format(data);
                    }
                    return data;
                }
            })
            ;
        })
        //$.fn.dataTable.ext.errMode = 'none';
        table_dt = $('#tabla_sec_consolidado_agente').DataTable
        (
            {
                /*"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                    //return iStart +" to "+ iEnd;
                    var nro_cdv = $("#sec_consolidado_free_games_cdv").val() ? $("#sec_consolidado_free_games_cdv").val().length : 4;
                    var nro_locales = parseInt(iEnd) / (parseInt(nro_cdv) + 1 );
                    var max_locales = iTotal / (parseInt(nro_cdv) + 1 );
                    var string_loc = nro_locales > 1 ? "local " : "locales";
                    var desde = Math.ceil( iStart / nro_cdv );
                    var hasta = Math.ceil( (parseINt(iEnd) - iStart) / nro_cdv );
                    return "Mostrando " + desde + " a " + hasta + " " + string_loc + " de " + max_locales + " registros";
                    //return "Mostrando del " + iStart + " al " + iEnd + " de " + iMax + " entradas.";
                },*/
                "bDestroy": true,
                scrollX: true,
                scrollY: false,
                fixedColumns:
                    {
                        leftColumns: 7
                    },
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                paging: true,
                searching: true,
                bSort: false,
                sPaginationType: "full_numbers",
                Sorting: [[1, 'asc']],
                rowsGroup: [0, 1, 2, 3, 4, 5],
                columns: columnas,
                data: datatable_data,
                //dom: 'Blrftip',
                //sDom:"<'row'<'col-sm-12'B>><'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                sDom: "<'row'<'col-xs-2'l><'col-xs-6'B><'col-xs-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                buttons:
                    [
                        {
                            extend: 'copy',
                            text: 'Copiar',
                            footer: true,
                            className: 'copiarButton'
                        },
                        {
                            extend: 'csv',
                            text: 'CSV',
                            footer: true,
                            className: 'csvButton'
                            ,
                            filename: $(".page-title").text().trim().replace(/ /g, '_') + "_" + moment(new Date()).format("YYYY_MM_DD_HH_mm_ss")
                        },
                        {
                            extend: 'excelHtml5',
                            text: 'Excel',
                            footer: true,
                            className: 'excelButton'
                            ,
                            filename: $(".page-title").text().trim().replace(/ /g, '_') + "_" + moment(new Date()).format("YYYY_MM_DD_HH_mm_ss")
                            ,
                            customize: function (xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                $('row:first c', sheet).attr('s', '22');
                                $('row c', sheet).each(function () {
                                    if ($('is t', this).text() == 'TOTAL') {
                                        $(this).attr('s', '20');
                                    }

                                });
                            }
                        },
                        {
                            extend: 'colvis',
                            text: 'Visibilidad',
                            className: 'visibilidadButton',
                            postfixButtons: ['colvisRestore']
                        }
                    ],
                footerCallback: function () {
                    var api = this.api();
                    for (var i = 7; i < columnas.length; i++) {
                        var total = api.column(i, {filter: 'applied'}).data().sum().toFixed(2);
                        var total_pagina = api.column(i, {filter: 'applied', page: 'current'}).data().sum().toFixed(2);
                        if (total < 0 && total_pagina < 0) {
                            $(api.column(i).footer()).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">' + formatonumeros(total / 2) + '<span><br>');
                        } else {
                            $(api.column(i).footer()).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">' + formatonumeros(total / 2) + '<span><br>');
                        }
                    }
                },
                fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    if (aData["CANAL DE VENTA"] == "TOTAL") {
                        $('td', nRow).css('cursor', 'default', 'important');
                        $('td', nRow).css('background-color', '#9BDFFD', 'important');
                        $('td', nRow).css('color', '#080FFC');
                        $('td', nRow).css('font-weight', '800');
                    }
                },
                createdRow: function (row, data, index) {
                },
                columnDefs: [
                    {
                        aTargets: 'tabla_sec_consolidado_agente_tfoot_meses',
                        fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                            if (sData < "0") {
                                $(nTd).css('color', 'red')
                                $(nTd).css('font-weight', 'bold')
                            }
                        }
                    },
                ],
                pageLength: '30',
                language: {
                    "decimal": ".",
                    "thousands": ",",
                    "emptyTable": "Tabla vacia",
                    "info": "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                    "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                    "infoFiltered": "(filtered from _MAX_ total entradas)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ entradas",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Filtrar:",
                    "zeroRecords": "Sin resultados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Ultimo",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": activate to sort column ascending",
                        "sortDescending": ": activate to sort column descending"
                    },
                    "buttons": {
                        "copyTitle": 'Contenido Copiado',
                        "copySuccess": {
                            _: '%d filas copiadas',
                            1: '1 fila copiada'
                        }
                    }
                }
            }
        );

        /*$(function() {
            var stickyHeaderTop = $('#tabla_sec_recaudacion').offset().top;
            $(window).scroll(function() {
                if ($(window).scrollTop() > stickyHeaderTop) {
                    $('.dataTables_scrollHead, .DTFC_LeftHeadWrapper').css('transform', 'translateY(0%)');
                    $('.DTFC_LeftHeadWrapper').css({position: 'fixed',top: '50px',zIndex: '1',left: 'auto'});
                    $('.dataTables_scrollHead').css({position: 'fixed',top: '50px', zIndex: '1' });
                    $('.DTFC_ScrollWrapper').css({height: ''});
                }
                else {
                    $('.DTFC_LeftHeadWrapper, .DTFC_LeftHeadWrapper').css({position: 'relative',top: '0px'});
                    $('.dataTables_scrollHead').css({position: 'relative', top: '0px'});
                    $('.dataTables_scrollHead').css('transform', 'translateY(0%)');
                }
            });
        });*/
        loading();
    }

    function sec_consolidado_free_games_events() {
        $("#sec_consolidado_free_games_filtrar_consolidado")
            .off("click")
            .on("click", function () {
                loading(true);
                sec_consolidado_free_games_get_liquidaciones();
            });

        $("#sec_consolidado_free_games_estado_locales").on('click', function (e) {
            e.preventDefault();
            if ($(this).hasClass('btn-danger')) {
                $(this).removeClass('btn-danger');
                $(this).addClass('btn-success');
                $(this).text('Mostrar Activos');
            } else {
                $(this).removeClass('btn-success');
                $(this).addClass('btn-danger');
                $(this).text('Mostrar Inactivos');
            }
            sec_consolidado_free_games_get_liquidaciones();
        });

        sec_consolidado_free_games_config_change_select_event('#sec_consolidado_free_games_zona', sec_consolidado_free_games_change_select_zonas);
        sec_consolidado_free_games_config_change_select_event('#sec_consolidado_free_games_local', sec_consolidado_free_games_change_select_local);
        sec_consolidado_free_games_config_change_select_event('#sec_consolidado_free_games_supervisor', null);
    }

    function sec_consolidado_free_games_config_change_select_event(select_id, change_event_callback) {
        let $select = $(select_id);

        $select.on('select2:select', function (e) {
            let selected_value = e.params.data.id;
            if (selected_value === 'all') {
                $(this).find('option[value!="all"]').prop('selected', false);
                $(this).find('option[value="all"]').prop('selected', true);
            } else {
                $(this).find('option[value="all"]').prop('selected', false);
            }
            $(this).trigger('change.select2');
            if (change_event_callback) {
                change_event_callback();
            }
        });

        $select.on('select2:unselect', function () {
            if (change_event_callback) {
                change_event_callback();
            }
        });
    }
}

