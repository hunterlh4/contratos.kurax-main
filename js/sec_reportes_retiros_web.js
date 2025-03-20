var searchsTable = {};
$(function () {

    listar_reporte_retiros_web();
    $('#sec_rep_ret_web_local').select2();
    $('#sec_rep_ret_web_zona').select2();
    $('#sec_rep_ret_web_tipo_ingreso_doc').select2();
    $('#sec_rep_ret_web_estado').select2();
    /*($('#sec_rep_ret_web_fecha_inicio').datetimepicker({
        format: 'YYYY-MM-DD'
    });
    $('#sec_rep_ret_web_fecha_fin').datetimepicker({
        format: 'YYYY-MM-DD'
    });*/
    $('#sec_rep_ret_web_btn_buscar').click( function(){
        listar_reporte_retiros_web();
    });
    $('#sec_rep_ret_web_btn_exp_excel').click( function(){
        reporte_retiros_exportar_excel();
    });
    $(".filtro_datepicker_rrw")
    .datepicker({
        dateFormat:'dd-mm-yy',
        changeMonth: true,
        changeYear: true
    })
    .on("change", function(ev) {
        $(this).datepicker('hide');
        var newDate = $(this).datepicker("getDate");
        $(this).attr("data-fecha_formateada",$.format.date(newDate, "yyyy-MM-dd"));
    });

});

function sec_rep_ret_web_limpiar_fechas(){
    console.log("limpiando fechas")
    $("#sec_rep_ret_web_fecha_inicio").attr("data-fecha_formateada","");
    $("#sec_rep_ret_web_fecha_fin").attr("data-fecha_formateada","");
    $("#sec_rep_ret_web_fecha_inicio").val("");
    $("#sec_rep_ret_web_fecha_fin").val("");
}

function reporte_retiros_exportar_excel() {
    searchsTable.action = "sec_reporte_retiros_web_excel",
    searchsTable.fecha_inicio = $("#sec_rep_ret_web_fecha_inicio").attr("data-fecha_formateada"),
    searchsTable.fecha_fin = $("#sec_rep_ret_web_fecha_fin").attr("data-fecha_formateada"),
    searchsTable.zona_id = $("#sec_rep_ret_web_zona").val(),
    searchsTable.local_id = $("#sec_rep_ret_web_local").val(),
    searchsTable.tipo_ingreso_doc = $("#sec_rep_ret_web_tipo_ingreso_doc").val(),
    searchsTable.estado = $("#sec_rep_ret_web_estado").val(),
    loading(true)
    $.ajax({
        global: false,
        url: "/sys/set_reportes_retiros_web.php",
        type: 'POST',
        data: searchsTable,
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (datos) {
            loading(false)
            try {
                var respuesta = JSON.parse(datos);
                if (respuesta.http_code === 200) {
                    window.open(respuesta.path);
                    swal('Reporte generado', '', 'success');
                } else {
                    swal('Error', 'Error al generar el reporte', 'error');
                }
            } catch (error) {
                console.log(error)
                swal('Error', 'Error al generar el reporte', 'error');
            }
        },
        error: function () {
        }
    });
}

function listar_reporte_retiros_web(){
    if( $("#sec_rep_ret_web_fecha_inicio").attr("data-fecha_formateada")!=="" &&  $("#sec_rep_ret_web_fecha_fin").attr("data-fecha_formateada")!==""){
        if( $("#sec_rep_ret_web_fecha_inicio").attr("data-fecha_formateada") > $("#sec_rep_ret_web_fecha_fin").attr("data-fecha_formateada")){
            swal('Error', 'La fecha de inicio no puede ser mayor a la fecha fin', 'error');
            return ;
        }
    }

    tablaserver = $("#sec_rep_ret_web_tbl_retiros")
        .on('order.dt', function () {
            $('table').css('width', '100%');
            $('.dataTables_scrollHeadInner').css('width', '100%');
            $('.dataTables_scrollFootInner').css('width', '100%');
        })
        .on('search.dt', function () {
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
            "scrollX": true,
            "sScrollX": "100%",
            "bProcessing": true,
            'processing': false,
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

            "bDeferRender": false,
            "autoWidth": true,
            pageResize:true,
            "bAutoWidth": true,
            "pageLength": 10,
            serverSide: true,
            "bDestroy": true,
            //colReorder: true,
            "lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
            "order": [[ 0, "desc" ]],
            buttons: [
                {
                    text: '<span class="glyphicon glyphicon-refresh"></span>',
                    action: function ( e, dt, node, config ) {
                        tablaserver.ajax.reload(null,false);
                    }
                }
            ],
            ajax: function (datat, callback, settings) {////AJAX DE CONSULTA
                datat.action = "reporte_retiros_web";

                datat.fecha_inicio = $("#sec_rep_ret_web_fecha_inicio").attr("data-fecha_formateada");
                datat.fecha_fin = $("#sec_rep_ret_web_fecha_fin").attr("data-fecha_formateada");
                datat.zona_id = $("#sec_rep_ret_web_zona").val();
                datat.local_id = $("#sec_rep_ret_web_local").val();
                datat.tipo_ingreso_doc = $("#sec_rep_ret_web_tipo_ingreso_doc").val();
                datat.estado = $("#sec_rep_ret_web_estado").val();
                //console.log(datat);
                searchsTable = datat;
                ajaxrepitiendo = $.ajax({
                    global: false,
                    url: "/sys/set_reportes_retiros_web.php",
                    type: 'POST',
                    data: datat,
                    beforeSend: function () {
                        tablaserver.columns.adjust();
                    },
                    complete: function () {
                        tablaserver.columns.adjust();
                        //responsive_tabla_scroll(tablaserver);
                    },
                    success: function (datos) {//  alert(datat)
                        //aaaa = datos;
                        var respuesta = JSON.parse(datos);
                        if(datat.action === "sec_reporte_retiros_web_excel"){
                            $(".dataTables_processing").hide();
                            window.open(respuesta.path);
                            return;
                        }
                        callback(respuesta);
                    },
                    error: function () {
                    }
                });
            },
            rowId: function(row){
                return row.id;
            },
            columns: [
                {data:"cod_transaccion",nombre:"cod_transaccion",title:"ID",orderable: false},
                {data:"registro",nombre:"registro",title:"Registro", orderable: false},
                {data:"client_id",nombre:"client_id",title:"ID Cliente", orderable: false},
                {data:"client_num_doc",nombre:"client_num_doc",title:"DNI", orderable: false},
                {data:"txn_id",nombre:"txn_id",title:"Transacción", orderable: false},
                {data:"monto",nombre:"monto",title:"Monto", orderable: false},
                {data:"status",nombre:"status",title:"Estado", orderable: false},
                {data:"scan_doc",nombre:"scan_doc",title:"DNI ingreso", orderable: false},
                {data:"usuario",nombre:"usuario",title:"Usuario", orderable: false},
                {data:"cc_id",nombre:"cc_id",title:"CECO", orderable: false},
                {data:"zona_nombre",nombre:"zona_nombre",title:"Zona", orderable: false},
                {data:"local_nombre",nombre:"local_nombre",title:"Local", orderable: false},

                {data:"observacion_scan_doc",nombre:"observacion_scan_doc",title:"Observación", orderable: false},
            ],
        });
    return tablaserver;
}