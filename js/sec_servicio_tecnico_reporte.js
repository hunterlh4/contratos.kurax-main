function sec_servicio_tecnico_reporte(){
	if( sec_id == "servicio_tecnico_reporte" || sec_id == "servicio_tecnico_reporte_form" ) {

		sec_servicio_tecnico_reporte_events();

        function sec_servicio_tecnico_reporte_events(){
            $(".select2")
                .filter(function(){
                    return $(this).css('display') !== "none";
                })
                .select2({
                closeOnSelect: true,
                width:"100%"
            });
            tablaserver = listar_servicio_tecnico_reporte();
    
            $("#btn_actualizar_tbl").off("click").on("click",function(){
                tablaserver.ajax.reload(null, false);
            });
    
            $("#zona_id").on("change", function() {
                let zona_id = $(this).find(':selected').attr("value");
                sec_servicio_tecnico_reporte_cargar_locales(zona_id);
            })
    
            $(".filtro_datepicker")
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
    
            $("#zona_id").val($("#zona_id option:first").val()).change();
            modal_reporte_historial_events();
        }
        function modal_reporte_historial_events(){
            $("#modal_reporte_historial").off("shown.bs.modal").on("shown.bs.modal",function(){
                $('.modal').css('overflow-y', 'auto');

                loading();
            });
            $("#modal_detalle").off("hidden.bs.modal").on("hidden.bs.modal",function(){
                $(".comentario, .foto_terminado").hide();
                $("#modal_detalle select, textarea ,input:file").val("");
                $("#modal_detalle #foto_terminado").attr("src","data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==");
            });
        }
        ///////guardar sec_servicio_tecnico_reporte_cargar_locales
        function sec_servicio_tecnico_reporte_cargar_locales(zona_id){
            loading(true);
            var set_data = {};
            set_data.zona_id = zona_id ;
            set_data.sec_servicio_tecnico_reporte_cargar_locales = "sec_servicio_tecnico_reporte_cargar_locales";
    
            $.ajax({
                url: 'sys/set_servicio_tecnico.php',
                method: 'POST',
                data: set_data,
                success: function(r){
                    var obj = jQuery.parseJSON(r);
                    $('#local_id').empty();
                    $(obj.locales).each(function(i,e){
                        $('#local_id').append($('<option>', { 
                            value: e.id,
                            text : e.nombre 
                        }));
                    })
                    $('#local_id').select2();
                    loading();
    
                }
            });
        }

        function listar_servicio_tecnico_reporte(){
            tablaserver = $("#tbl_servicio_tecnico_reporte")
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
                        colReorder: true,
                        "lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
                        "order": [[ 1, "desc" ]],
                        buttons: [
                            {
                                text: '<span class="glyphicon glyphicon-refresh"></span>',
                                action: function ( e, dt, node, config ) {
                                    tablaserver.ajax.reload(null,false);
                                }
                            }
                        ],
                        ajax: function (datat, callback, settings) {////AJAX DE CONSULTA                    
                            datat.action = typeof action=="undefined"?"sec_servicio_tecnico_list":action; //"sec_servicio_tecnico_list";
                            datat.fecha_inicio = $("#fecha_inicio").attr("data-fecha_formateada");
                            datat.fecha_fin = $("#fecha_fin").attr("data-fecha_formateada");
    
                            ajaxrepitiendo = $.ajax({
                                global: false,
                                url: "/sys/set_servicio_tecnico.php",
                                type: 'POST',
                                data: datat,
                                beforeSend: function () {
                                    tablaserver.columns.adjust();
                                },
                                complete: function () {
                                    tablaserver.columns.adjust();
                                },
                                success: function (datos) {//  alert(datat)
                                    var respuesta = JSON.parse(datos);
                                    if(datat.action == "sec_servicio_tecnico_reporte_list_excel"){
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
                            {data:"id",nombre:"id",title:"Id",visible:false},
                            {data:"created_at",nombre:"created_at",title:"Fecha Ingreso"},
                            {data:"zona",nombre:"zona",title:"Zona"},
                            {data:"local",nombre:"local",title:"Tienda"},
                            {data:"incidencia_txt",nombre:"incidencia_txt",title:"Descrip. Incidente"},
                            {data:"equipo",nombre:"equipo",title:"Equipo"},
                            {data:"recomendacion",nombre:"recomendacion",title:"Recomendación"},
                            {data:"nota_tecnico",nombre:"nota_tecnico",title:"Nota para el Técnico"},
                            {data:"estado_vt",nombre:"estado_vt",title:"Estado"},                            
                            {data:"fecha_cierre_vt",nombre:"fecha_cierre",title:"Fecha de Cierre",defaultContent: "-"},
                            {data:"tecnico",nombre:"tecnico",title:"Nombre de Tecnico",defaultContent: "-"},
                            {data: null,nombre:null,orderable:false,title:"Detalle"
                                ,"render": function (data, type, row ) {
                                    var html = '<a class="btn btn-rounded btn-primary btn-sm ver_detalle_reporte" title="Ver detalle">';
                                    html += '<i class="fa fa-eye"></i>';
                                    html += ' Ver';
                                    html += '</a>';
                                    return html;
                                }
                            }
                        ],
                        "drawCallback":function (){
                            $("#tbl_servicio_tecnico_reporte tbody .ver_detalle_reporte").off("click").on("click",function(){
                                var id = $(this).closest("tr").attr("id");
                                listar_servicio_tecnico_reporte_historial(id);
                                $("#modal_reporte_historial").modal("show");
                            })
                        },
                        "initComplete": function (settings, json) {
                            setTimeout(function(){
                                $("#servicio_tecnico_reporte_recargar").off("click").on("click",function(){
                                    tablaserver.ajax.reload(null, false);
                                })
                                tablaserver.columns.adjust();
                            },100)
                            // 0 => Nuevo, 1 => Atendido, 2 => Asignado
                            action = "sec_servicio_tecnico_list";
                            filtrar_datatable_sec_servicio_tecnico_reporte(settings,json);
                        }
                    });
            return tablaserver;
        }

    
        function filtrar_datatable_sec_servicio_tecnico_reporte(settings,json){
            var boton_buscar = true;
    

            var datatable = settings.oInstance.api();
    
            $("#btn_servicio_tecnico_reporte_search").off("click").on("click",function(){
                action = "sec_servicio_tecnico_list";
                datatable.ajax.reload(null, false);
            })
            $("#btn_servicio_tecnico_reporte_excel").off("click").on("click",function(){
                action = "sec_servicio_tecnico_reporte_list_excel";
                datatable.ajax.reload(null, false);
            });

            var localStorage_estado_var="sec_servicio_tecnico_reporte_estado_select";
            $("#estado_select").off("change").on("change",function(){
                var val = $(this).val();
                datatable.column(8).search(val);//.draw();
                if(!boton_buscar){
                    datatable.ajax.reload(null, false);
                }
                datatable.columns.adjust();
                localStorage.setItem(localStorage_estado_var,val);
            })
            $("#estado_select").select2();
    
            /*if(localStorage.getItem(localStorage_estado_var) && localStorage.getItem(localStorage_estado_var)!="null"){
                setTimeout(function(){
                    var valor = localStorage.getItem(localStorage_estado_var).split(',');
                    $("#estado_select").val(valor).change();
                },200);
            }
            else{
                setTimeout(function(){
                    $("#estado_select").val([0,2]).change();//nuevos,asignados
                },200);
            }*/
    
            var localStorage_local_var="sec_solicitud_mantenimento_local_select";
            $("#local_select").off("change").on("change",function(){
                var val = $(this).val();
                datatable.column(3).search(val);//.draw();
                if(!boton_buscar){
                    datatable.ajax.reload(null, false);
                }
                datatable.columns.adjust();
                localStorage.setItem(localStorage_local_var,val);
            })
            $("#local_select").select2();
    
            var localStorage_zona_var="sec_solicitud_mantenimento_zona_select";
            $("#zona_select").off("change").on("change",function(){
                var val = $(this).val();
                datatable.column(2).search(val);//.draw();
                if(!boton_buscar){
                    datatable.ajax.reload(null, false);
                }
                datatable.columns.adjust();
                localStorage.setItem(localStorage_zona_var,val);
            })
            $("#zona_select").select2();
    
            var localStorage_sistema_var="sec_solicitud_mantenimento_sistema_select";
            $("#sistema_select").off("change").on("change",function(){
                var val = $(this).val();
                datatable.column(4).search(val);//.draw();
                if(!boton_buscar){
                    datatable.ajax.reload(null, false);
                }
                datatable.columns.adjust();
                localStorage.setItem(localStorage_sistema_var,val);
            })
            $("#sistema_select").select2();    
        }


        function listar_servicio_tecnico_reporte_historial(id){
            tablaserver_reporte_historial = $("#tbl_servicio_tecnico_reporte_historial")
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
                        sDom: 'lrtip',
                        "bDeferRender": false,
                        "autoWidth": true,
                        pageResize:true,
                        "bAutoWidth": true,
                        "pageLength": 10,
                        serverSide: true,
                        "bDestroy": true,
                        colReorder: true,
                        "lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
                        "order": [[ 1, "desc" ]],
                        ajax: function (datat, callback, settings) {////AJAX DE CONSULTA                    
                            datat.action = "sec_servicio_tecnico_reporte_historial_list"; //"sec_servicio_tecnico_list";
                            datat.incidencia_id = id;
    
                            $.ajax({
                                global: false,
                                url: "/sys/set_servicio_tecnico.php",
                                type: 'POST',
                                data: datat,
                                beforeSend: function () {
                                },
                                complete: function () {
                                },
                                success: function (datos) {//  alert(datat)
                                    var respuesta = JSON.parse(datos);
                                    if(datat.action == "sec_servicio_tecnico_reporte_historial_list_excel"){
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
                            {data:"id",nombre:"id",title:"Id",visible:false},
                            {data:"created_at",nombre:"created_at",title:"Fecha"},
                            {data:"estado",nombre:"estado",title:"Estado"},
                            {data:"equipo",nombre:"equipo",title:"Equipo"},
                            {data:"Usuario",nombre:"Usuario",title:"Usuario"},
                            {data:"Técnico",nombre:"Técnico",title:"Técnico" , defaultContent : "---"},
                            {data:"Comentario Téc",nombre:"Comentario Téc",title:"Comentario Téc" , defaultContent : "---"},
                            {data:"Comentario Terminado.",nombre:"Comentario Terminado.",title:"Comentario Terminado." , defaultContent : "---"}
                        ],
                        "drawCallback":function (){
                        },
                        "initComplete": function (settings, json) {
                            //filtrar_datatable_sec_servicio_tecnico_reporte(settings,json);
                        }
                    });
            return tablaserver;
        }

	}
    
}