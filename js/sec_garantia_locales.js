function sec_garantia_locales(){
    if (sec_id=="garantia_locales") {
        console.log("sec:garantia_locales");
        sec_garantia_locales_events();
    }
}

function sec_garantia_locales_events(){
    sec_garantia_modal_nueva_solicitud();

    $(".select2").select2();

    if ($("#li_local_table").hasClass("active")) {
        sec_garantia_locales_table();
    } else if ($("#li_solicitud_table").hasClass("active")) {
        sec_garantia_solicitudes_table();
    }

    $(document).on("click", "#tab_local_table", function() {
        sec_garantia_locales_table();
    });

    $(document).on("click", "#tab_solicitudes_table", function() {
        sec_garantia_solicitudes_table();
    });

    $(document).on("click", ".btn_modal_editar_fecha", function() {
        var val_id_local_garantia = $(this).data("id");
        var val_cc_id_local_garantia = $(this).data("cc_id");
        var val_nombre = $(this).data("nombre");
        var val_fecha_inicio_garantia = $(this).data("fecha_inicio_garantia");
        var val_fecha_fin_garantia = $(this).data("fecha_fin_garantia");

        var text_nombre_local_garantia = "["+val_cc_id_local_garantia+"] "+val_nombre;
        $("#modal_nombre_local_garantia").text(text_nombre_local_garantia);

        $("#id_local_garantia").val(val_id_local_garantia);
        $("#fecha_inicio_garantia").val(val_fecha_inicio_garantia);
        $("#fecha_fin_garantia").val(val_fecha_fin_garantia);

		$("#modal_cambiar_fecha_garantia").modal('show');
	});

    $(document).on("click", ".btn_local_nueva_solicitud", function() {
        $("#modal_local_select_id").val($(this).data("id")).trigger('change');
        $("#modal_nueva_solicitud_garantia").modal('show');
    });

    $(document).on("click", "#btn_modal_nueva_solicitud", function() {
        sec_garantia_modal_nueva_solicitud_limpiar();
        $("#modal_nueva_solicitud_garantia").modal('show');
    });

    $(document).on("click", ".btn_modal_detalle_solicitud", function() {
        loading(true);
        
        $("#modal_solicitud_files").val('');
        document.getElementById('modal_solicitud_label_name_File').innerHTML = "";
    
        var id_solicitud_garantia = $(this).data("id");

        var set_data = {};

        set_data['opt'] = "sec_garantia_detalle_solicitud";
        set_data['id_solicitud_garantia'] = id_solicitud_garantia;

        $.ajax({
            url: 'sys/set_garantia_locales.php',
            type: 'POST',
            data: set_data,
        })
        .done(function(data) {
            loading(false);
            var res = JSON.parse(data);
            $("#modal_solicitud_id").val(res.data.id);
            $("#modal_solicitud_created_at").text(res.data.created_at);
            $("#modal_solicitud_zona").text(res.data.nombre_zona);
            $("#modal_solicitud_local").text(res.data.nombre_local);
            $("#modal_solicitud_sistema").text(res.data.nombre_sistema);
            $("#modal_solicitud_subsistema").text(res.data.nombre_subsistema);
            $("#modal_solicitud_criticidad").val(res.data.tipo_criticidad);
            $("#modal_solicitud_reporte").text(res.data.reporte);
            $("#modal_solicitud_estado").val(res.data.estado);

            $("#modal_detalle_solicitud_garantia #modal_solicitud_imagenes_cargar").empty();
            $(res.imagenes).each(function(i,e){
                $("#modal_detalle_solicitud_garantia #modal_solicitud_imagenes_cargar").append(
                    $("<img class='imagenes_modal'>").attr("src","files_bucket/solicitud_garantia/"+e.archivo)
                )
            });
            if (res.imagenes.length === 0) {
                $("#solicitud_con_foto").hide();
                $("#solicitud_sin_foto").show();
            } else {
                $("#solicitud_con_foto").show();
                $("#solicitud_sin_foto").hide();
            }

            $("#modal_detalle_solicitud_garantia #modal_solicitud_imagenes_atendido").empty();
            if (res.data.foto_terminado != null) {
                $("#modal_detalle_solicitud_garantia #modal_solicitud_imagenes_atendido").append(
                    $("<img class='imagenes_modal'>").attr("src","files_bucket/solicitud_garantia/" + res.data.foto_terminado)
                );
                $("#solicitud_con_foto_atendido").show();
            } else {
                $("#solicitud_con_foto_atendido").hide();
            }

            $(".imagenes_modal").off("click").on("click",function(){
                var src = $(this).attr("src");
                $("#vista_previa_modal #img01").attr("src",src);
                $("#vista_previa_modal").modal("show");
            });
        });

        $("#modal_detalle_solicitud_garantia").modal('show');
    });

    $("#modal_btn_derivar_a_mantenimiento").on('click', function (event) {
        event.preventDefault();

        swal({
            title: "¡ATENCIÓN!",
			text: "¿Está seguro(a) de derivar esta solicitud a Mantenimiento",
            type: "info",
            showCancelButton: true,
			confirmButtonText: "Si",
			cancelButtonText:"No",
			closeOnConfirm: true
        },function(){
            loading(true);
            var set_data = {};
            set_data['opt'] = "sec_garantia_derivar_a_mantenimiento";
            set_data['id_solicitud'] = $("#modal_solicitud_id").val();
            //set_data['sistema_solicitud_text'] = $("#modal_solicitud_sistema").text();
            console.log(set_data);

            $.ajax({
                url: 'sys/set_garantia_locales.php',
                type: 'POST',
                data: set_data,
            })
            .done(function(data) {
                $("#modal_detalle_solicitud_garantia").modal('hide');
                loading(false);
                var res = JSON.parse(data);
                if(res.error==true){
                    swal({
                        title: res.error_title,
                        text: res.error_msg,
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        showConfirmButton: true
                    },function(){
                        swal.close();
                    });
                } else {
                    swal({
                        title: "Solicitud Derivada",
                        text: "La solicitud fue derivada a mantenimiento con éxito",
                        type: "success"
                    });
                    sec_garantia_solicitudes_table();
                }
            });
        });
    });

    $("#modal_solicitud_files").on("change", function () {
        var files = this.files;
        var filesLength = files.length;

        document.getElementById('modal_solicitud_label_name_File').innerHTML = '';

        for (var i = 0; i < filesLength; i++) {
            if (files[i].size > 80000000) {
                swal({
                    title: "Tamaño excedido",
                    text: "El archivo: " + files[i].name + " ha excedido el peso máximo por archivo (80 MB)",
                    type: "warning"
                });
                $(this).val('');
                document.getElementById('modal_solicitud_label_name_File').innerHTML = "";
                break;
            } else {
                if (i > 0) {
                    document.getElementById('modal_solicitud_label_name_File').innerHTML += "; ";
                }
                document.getElementById('modal_solicitud_label_name_File').innerHTML += document.getElementById('modal_solicitud_files').files[i].name;
            }
        }
    });

    $("#btn_solicitud_garantia_search").off("click").on("click",function(){
		sec_garantia_solicitudes_table();
	})

    $("#btn_guardar_fechas_garantia").on('click', function (event) {
        event.preventDefault();
        loading(true);
        var set_data = {};

        set_data['opt'] = "sec_garantia_cambiar_fechas";
        set_data['id_local_garantia'] = $("#id_local_garantia").val();
        set_data['fecha_inicio_garantia'] = $("#fecha_inicio_garantia").val();
        set_data['fecha_fin_garantia'] = $("#fecha_fin_garantia").val();

        $.ajax({
            url: 'sys/set_garantia_locales.php',
            type: 'POST',
            data: set_data,
        })
        .done(function(data) {
            $("#modal_cambiar_fecha_garantia").modal("hide");
            loading(false);
            var res = JSON.parse(data);
            if(res.error==true){
                swal({
                    title: res.error_title,
                    text: res.error_msg,
                    type: "warning",
                    timer: 4500,
                    closeOnConfirm: true,
                    showCancelButton: false,
                    showConfirmButton: true
                },function(){
                    swal.close();
                });
            } else {
                swal({
					title: "Fechas actualizadas",
					text: "Las fechas de garantía fueron actualizadas correctamente",
					type: "success"
				});
                sec_garantia_locales_table();
            }
        });
    });

    $(".filtro_datepicker").datepicker({
        dateFormat:'dd-mm-yy',
		changeMonth: true,
		changeYear: true
    }).on("change", function(ev) {
        $(this).datepicker('hide');
		var newDate = $(this).datepicker("getDate");
		$(this).attr("data-fecha_formateada",$.format.date(newDate, "yyyy-MM-dd"));
    });

    $("#modal_solicitud_estado").off("change").on("change",function(){
		if($(this).val() == "1"){
            $("#solicitud_sin_foto").show();
        } else {
            $("#solicitud_sin_foto").hide();
        }
	});

    $('#solicitud_zona_select, #solicitud_local_select, #solicitud_estado_select, #solicitud_sistema_select, #solicitud_subsistema_select, #solicitud_criticidad_select').on('change', function() {
        sec_garantia_solicitudes_table();
    });

    $(document).on('submit', "#modal_form_solicitud", function(e) {
        e.preventDefault();
        loading(true);

        if ($("#modal_local_select_id").val() == 'none' || $("#modal_local_select_sistema").val() == 'none' || $("#modal_local_select_subsistema").val() == 'none' || $("#modal_local_select_criticidad").val() == 'none' || $("#modal_local_reporte").val() == '') {
            loading(false);
            swal({
                title: "Faltan Datos",
                text: "Complete el formulario para poder continuar",
                type: "warning"
            });
        } else if ($("#modal_local_files").val() == '') {
            loading(false);
            swal({
                title: "¡Error!",
                text: "Debe Ingresar imagen para continuar",
                type: "warning"
            });
        } else{
            var form_data = (new FormData(this));
            form_data.append("opt", "sec_garantia_guardar_solicitud");

            $.ajax({
                url: "sys/set_garantia_locales.php",
                type: "POST",
                data: form_data,
                cache: false,
                contentType: false,
                processData:false,
                success: function(response) {
                    $("#modal_nueva_solicitud_garantia").modal('hide');
                    loading(false);
                    sec_garantia_modal_nueva_solicitud_limpiar();
                    var res = JSON.parse(response);
                    if(res.error==true){
                        swal({
                            title: res.error_title,
                            text: res.error_msg,
                            type: "warning",
                            timer: 4500,
                            closeOnConfirm: true,
                            showCancelButton: false,
                            showConfirmButton: true
                        },function(){
                            swal.close();
                        });
                    } else {
                        swal({
                            title: "Solicitud enviada",
                            text: "La solicitud fue enviada correctamente",
                            type: "success"
                        });
                    }
                }
            });
        }
    });

    $(document).on('submit', "#modal_form_actualizar_solicitud", function(e) {
        e.preventDefault();
        loading(true);

        var form_data = (new FormData(this));
        form_data.append("opt", "sec_garantia_actualizar_solicitud");

        $.ajax({
            url: "sys/set_garantia_locales.php",
            type: "POST",
            data: form_data,
            cache: false,
            contentType: false,
            processData:false,
            success: function(response) {
                $("#modal_detalle_solicitud_garantia").modal('hide');
                sec_garantia_solicitudes_table();
                loading(false);
                var res = JSON.parse(response);
                if(res.error==true){
                    swal({
                        title: res.error_title,
                        text: res.error_msg,
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        showConfirmButton: true
                    },function(){
                        swal.close();
                    });
                } else {
                    swal({
                        title: "¡Completado!",
                        text: "La información se ha actualizado correctamente",
                        type: "success"
                    });
                }
            }
        });
    });

    $("#btn_tutorial_ayuda").on("click",function(){

        if ($("#li_local_table").hasClass("active")) {
            introJs().setOptions({
                nextLabel: 'Siguiente',
                prevLabel: 'Anterior',
                doneLabel: 'Finalizar',
                showProgress: true,
                showBullets: false,
                steps: [{
                    title: "¡Hola!",
                    intro: "¡Bienvenido al tour de Locales del Módulo de Garantías! 👋"
                }, {
                    element: document.querySelector('#btn_modal_nueva_solicitud'),
                    intro: "Con este botón podemos crear una nueva solicitud"
                }, {
                    element: document.querySelector('.btn_local_nueva_solicitud'),
                    intro: "Este botón también permite crear una nueva solicitud, pero del local específico"
                }, {
                    element: document.querySelector('.btn_modal_editar_fecha'),
                    intro: "Con este botón se puede editar las fechas de garantía, en caso de no verlo, es encesario permisos adicionales"
                }]
            }).start();
        } else if ($("#li_solicitud_table").hasClass("active")) {
            introJs().setOptions({
                nextLabel: 'Siguiente',
                prevLabel: 'Anterior',
                doneLabel: 'Finalizar',
                showProgress: true,
                showBullets: false,
                steps: [{
                    title: "¡Hola!",
                    intro: "¡Bienvenido al tour de Solicitudes del Módulo de Garantías! 👋"
                }, {
                    element: document.querySelector('#cont_filtros_garantia_solicitudes'),
                    intro: "Empezamos primero con los filtros de nuestra tabla de solicitud, se tiene que clickar en el botón CONSULTAR  para poder aplicarlos"
                }, {
                    element: document.querySelector('.btn_modal_detalle_solicitud'),
                    intro: "Con este botón podemos ver los detalles y editar el Estado y la Criticidad de la Solicitud"
                }]
            }).start();
        }
    });
}

function sec_garantia_locales_table(){
    var data = {opt: "sec_garantia_locales_table"};

    $('#tbl_garantia_locales').DataTable({
        paging: true,
        processing: true,
        autoWidth: true,
        pageLength: 10,
        serverSide: true,
        destroy: true,
        colReorder: true,
        lengthMenu: [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
		order: [[5, 'DESC']],
        language: {
            decimal: "",
            emptyTable: "Tabla vacia",
            info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
            infoEmpty: "Mostrando 0 a 0 de 0 entradas",
            infoFiltered: "(filtrado de _MAX_ registros)",
            infoPostFix: "",
            thousands: ",",
            lengthMenu: "Mostrar _MENU_ entradas",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "Sin resultados",
            paginate: {
                first: "Primero",
                last: "Ultimo",
                next: "Siguiente",
                previous: "Anterior",
            },
            aria: {
                sortAscending: ": activate to sort column ascending",
                sortDescending: ": activate to sort column descending",
            },
            buttons: {
                pageLength: {
                    _: "Mostrar %d Resultados",
                    '-1': "Tout afficher"
                }
            }
        },
        ajax: {
            url: "sys/set_garantia_locales.php",
            type: "POST",
            data: data
        },
        columns: [
            { data: "id" },
            { data: "cc_id" },
            { data: "zona_nombre" },
            { data: "nombre" },
            { data: "fecha_inicio_garantia" },
            { data: "fecha_fin_garantia" },
            { data: "dias" },
            { data: "options" },
        ],
        createdRow: function ( row, data, dataIndex ) {
            var fin_garantia_alter = $('td', row).eq(6);
            $('td', row).eq(7).css('text-align', 'center');

            if (data["dias"] > 0) {
                fin_garantia_alter.css('background-color', '#1cb787');
            } else {
                fin_garantia_alter.css('background-color', '#ed6b76');
            }
            fin_garantia_alter.css('color', 'white');
            fin_garantia_alter.css('font-weight', 'bold');
        },
    });
}

function sec_garantia_modal_nueva_solicitud(){
    $("#modal_local_select_id").change(function(){
        loading(true);
        var data={};
        data.id=$("#modal_local_select_id").val();
        data.opt="sec_garantia_comprobar_disp_local";
        $.ajax({
            data: data,
            url: 'sys/set_garantia_locales.php',
            type: 'POST',
            dataType: 'json'
        })
        .done(function(response){
            loading(false);
            var in_num_dias = response.num_dias;
            if (in_num_dias > 0) {
                $("#modal_solicitud_num_dias").css("color", "green");
                in_num_dias = in_num_dias + " días restantes";
            } else {
                $("#modal_solicitud_num_dias").css("color", "red");
                in_num_dias = "Garantía no vigente";
            }
            $("#modal_solicitud_num_dias").val(in_num_dias);
        });
    });

    $("#modal_local_select_sistema").change(function(){
        loading(true);
        var data={};
        data.id=$("#modal_local_select_sistema").val();
        data.opt="sec_garantia_select_subsistema";
        $.ajax({
            data: data,
            url: 'sys/set_garantia_locales.php',
            type: 'POST',
            dataType: 'json'
        })
        .done(function(response){
            loading(false);
            $("#modal_local_select_subsistema").html(response.option_subsistema);
        });
    });

    $("#modal_local_files").on("change", function () {
        var files = this.files;
        var filesLength = files.length;

        document.getElementById('modal_label_name_File').innerHTML = '';

        if (filesLength > 3) {
            swal({
                title: "Cantidad excedida",
                text: "Sólo se permite un máximo de 3 imágenes o videos por solicitud",
                type: "warning"
            });
            $(this).val('');
        } else {
            for (var i = 0; i < filesLength; i++) {
                if (files[i].size > 80000000) {
                    swal({
                        title: "Tamaño excedido",
                        text: "El archivo: " + files[i].name + " ha excedido el peso máximo por archivo (80 MB)",
                        type: "warning"
                    });
                    $(this).val('');
                    document.getElementById('modal_label_name_File').innerHTML = "";
                    break;
                } else {
                    if (i > 0) {
                        document.getElementById('modal_label_name_File').innerHTML += "; ";
                    }
                    document.getElementById('modal_label_name_File').innerHTML += document.getElementById('modal_local_files').files[i].name;
                }
            }
        }
    });

    $(document).on("click", "#btn_modal_limpiar_campos", function() {
        sec_garantia_modal_nueva_solicitud_limpiar();
    }); 
}

function sec_garantia_solicitudes_table() {
    var data = {
        opt: "sec_garantia_solicitudes_table",
        zona: $.trim($("#solicitud_zona_select").val()),
        tienda: $.trim($("#solicitud_local_select").val()),
        estado: $.trim($("#solicitud_estado_select").val()),
        sistema: $.trim($("#solicitud_sistema_select").val()),
        subsitema: $.trim($("#solicitud_subsistema_select").val()),
        criticidad: $.trim($("#solicitud_criticidad_select").val()),
        fecha_inicio: $("#solicitud_fecha_inicio").attr("data-fecha_formateada"),
        fecha_fin: $("#solicitud_fecha_fin").attr("data-fecha_formateada")
    };

    $('#tbl_garantia_solicitudes').DataTable({
        paging: true,
        processing: true,
        autoWidth: true,
        pageLength: 10,
        serverSide: true,
        destroy: true,
        colReorder: true,
        lengthMenu: [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
		order: [[0, 'DESC']],
        language: {
            decimal: "",
            emptyTable: "Tabla vacia",
            info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
            infoEmpty: "Mostrando 0 a 0 de 0 entradas",
            infoFiltered: "(filtrado de _MAX_ registros)",
            infoPostFix: "",
            thousands: ",",
            lengthMenu: "Mostrar _MENU_ entradas",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "Sin resultados",
            paginate: {
                first: "Primero",
                last: "Ultimo",
                next: "Siguiente",
                previous: "Anterior",
            },
            aria: {
                sortAscending: ": activate to sort column ascending",
                sortDescending: ": activate to sort column descending",
            },
            buttons: {
                pageLength: {
                    _: "Mostrar %d Resultados",
                    '-1': "Tout afficher"
                }
            }
        },
        ajax: {
            url: "sys/set_garantia_locales.php",
            type: "POST",
            data: data
        },
        columns: [
            { data: "id" },
            { data: "created_at" },
            { data: "nombre_zona" },
            { data: "nombre_local" },
            { data: "nombre_sistema" },
            { data: "nombre_subsistema" },
            { data: "criticidad" },
            { data: "reporte" },
            { data: "estado" },
            { data: "options" }
        ],
        createdRow: function ( row, data, dataIndex ) {
            var data_estado_alter = $('td', row).eq(8);
            $('td', row).eq(9).css('text-align', 'center');

            data_estado_alter.css('color', 'white');
            data_estado_alter.css('font-weight', 'bold');

            switch (data["estado"]) {
                case 'ATENDIDO':
                    data_estado_alter.css('background-color', '#1cb787');
                    break;
                case 'PENDIENTE':
                    data_estado_alter.css('background-color', '#ed6b76');
                    break;
                case 'PROGRAMADO':
                    data_estado_alter.css('background-color', '#9c5700');
                    break;
                case 'DERIVADO':
                    data_estado_alter.css('background-color', '#E0E0E0');
                    data_estado_alter.css('color', 'black');
                    break;
                default:
                    break;
            }
        },
    });
}

function sec_garantia_modal_nueva_solicitud_limpiar() {
    $("#modal_local_select_sistema").val('none').trigger('change');
    $("#modal_local_select_subsistema").val('none').trigger('change');
    $("#modal_local_select_criticidad").val('none').trigger('change');
    $("#modal_local_reporte").val('');
    $("#modal_local_files").val('');
    document.getElementById('modal_label_name_File').innerHTML = "";
}