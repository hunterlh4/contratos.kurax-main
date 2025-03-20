$(document).ready(function () {
    if (localStorage.getItem("anuncio_reload") === "false") {
        localStorage.removeItem("anuncio_reload")
        return
    }

    $.each($(".anuncio_popup"), function (index, val) {
        if (parseInt($(val).data("estado")) !== "6") {
            if (localStorage.getItem("sec_id") === "home") {
                localStorage.removeItem("anuncio_popup_" + $(val).data("id"));
            }
            anuncio_fire_popup($(val));
        }
    });

    // grupo_id  8: at-sistemas
    // grupo_id 11: operaciones-cajero

    // if (anuncios_grupo_id) {
    // 	if( anuncios_grupo_id == 11 )
    // 	{
    // 		anuncios_obtener_fecha_del_ultimo_cambio();

    // 		setTimeout(function() {
    // 			anuncios_mostrar_anuncio();
    // 			anuncios_llamar_anuncio();
    // 		}, 1000);
    // 	}
    // }

    anuncios_obtener_fecha_del_ultimo_cambio();

    setTimeout(function () {
        anuncios_mostrar_anuncio();
        anuncios_llamar_anuncio();
    }, 1000);

    setTimeout(function () {
        var anuncio_random = $(".anuncio_popup[data-estado='6']")[Math.floor(Math.random() * ($(".anuncio_popup[data-estado='6']")).length)];
        if (anuncio_random !== undefined) {
            anuncio_fire_popup_cajero($(anuncio_random));
        }
    }, 3000);

    sec_anuncios_div_table_anuncios();
    set_anuncios_imagen($('#anuncios_imagen'));
    set_anuncios_audio($('#anuncios_audio'));
    set_anuncios_video($('#anuncios_video'));
    $(".anuncios_select_filtro").select2({width: '100%'});

    //INICIO DECLARACION DE MASK
    $('.anuncios_tiempo_anuncio').mask('000');
    //FIN DECLARACION DE MASK

    sec_anuncios_inicializar_fechas_formulario_anuncio();

    $.each($(".anuncio_popup_atencion_ganadora"), function (index, val) {
        if (parseInt($(val).data("estado")) === 20) {
            if (!(localStorage.getItem("popup_atencion_ganadora"))) {
                var fecha_actual_milisegundo = new Date();
                var numero_milisegundos = fecha_actual_milisegundo.getTime();

                var agregar_una_hora = 60 * 60000;
                //var agregar_una_hora = 15000;

                var nueva_fecha = new Date(numero_milisegundos);

                //localStorage.setItem("popup_atencion_ganadora", 1000 * 60 * 60);
                localStorage.setItem("popup_atencion_ganadora", numero_milisegundos);
            }

            sec_anuncio_atencion_ganadora($(val));
        }
    });

    $("#anuncios_check_image_multiple").click(() => {
        $("#txt_anuncio_imagen").html("");
        $("#anuncios_imagen").val("");
    });

    anuncios_listar_soporte_notas();
});

function NotaSoporte() {
    this.id = 0;
    this.image = '';
    this.title = '';
    this.text = '';
    this.estado = 0;
}

function anuncios_listar_soporte_notas() {
    // let cargo_id = parseInt($('#page_footer_input_text_cargo_id').val());
    // let area_id = parseInt($('#page_footer_input_text_area_id').val());
    // if (cargo_id === 5 && area_id === 21 || cargo_id === 9 && area_id === 6) {
    //     $.ajax({
    //         url: '/sys/get_incidencias_ca.php',
    //         type: 'POST',
    //         data: {
    //             get_incidencias_ca_get_soporte_notas: true
    //         },
    //         success: function (response) {
    //             let result = JSON.parse(response);
    //             if (result.error === true) {
    //                 swal({
    //                     type: "warning",
    //                     title: "Atención",
    //                     text: result.message
    //                 });
    //             } else {
    //                 let soporte_notas = result.data;
    //                 if (soporte_notas.length > 0) {
    //                     sessionStorage.removeItem('soporte_notas_storage');
    //                     sessionStorage.setItem('soporte_notas_storage', JSON.stringify(soporte_notas));
    //                     let last_index = soporte_notas.length - 1;
    //                     anuncios_mostrar_popup_soporte_notas(soporte_notas[last_index], last_index);
    //                 }
    //             }
    //         }
    //     });
    // }
    // /*let soporte_notas_timeout = sessionStorage.getItem("soporte_notas_timeout");
    // let current_time = new Date().getTime();
    // if (current_time >= soporte_notas_timeout) {
    //     let minutes = 30;
    //     soporte_notas_timeout = current_time + minutes * 60000;
    //     sessionStorage.setItem('soporte_notas_timeout', soporte_notas_timeout);
    //     let soporte_notas = [];
    //     sessionStorage.removeItem('soporte_notas_storage')
    //     $.each($('.anuncio_popup_soporte_notas'), function (index, element) {
    //         let nota = $(element).data();
    //         soporte_notas.push(nota);
    //     });
    //     if (soporte_notas.length) {
    //         sessionStorage.setItem('soporte_notas_storage', JSON.stringify(soporte_notas));
    //         let last_index = soporte_notas.length - 1;
    //         anuncios_mostrar_popup_soporte_notas(soporte_notas[last_index], last_index);
    //     }
    //     setTimeout(function () {
    //         anuncios_listar_soporte_notas();
    //     }, 60000);
    // } else {
    //     setTimeout(function () {
    //         anuncios_listar_soporte_notas();
    //     }, 60000);
    // }*/
}

/**
 * @param {NotaSoporte} soporte_nota The Notification
 * @param {int} last_index The length of notifications
 */
function anuncios_mostrar_popup_soporte_notas(soporte_nota, last_index) {
    alertify.alert('Notificación de Soporte', soporte_nota.nota_txt, function () {
        $.ajax({
            url: '/sys/get_incidencias_ca.php',
            type: 'POST',
            data: {
                get_incidencias_ca_insert_soporte_notas_usuarios: true,
                soporte_notas_id: soporte_nota.id
            },
            success: function (response) {
                let result = JSON.parse(response);
                if (result.error === true) {
                    swal({
                        type: "warning",
                        title: "Atención",
                        text: result.message
                    });
                } else {
                    if (last_index > 0) {
                        let soporte_notas = JSON.parse(sessionStorage.getItem("soporte_notas_storage"));
                        last_index = last_index - 1;
                        setTimeout(function () {
                            anuncios_mostrar_popup_soporte_notas(soporte_notas[last_index], last_index);
                        }, 1000);
                    }
                }
            }
        });
    });
}

function sec_anuncio_atencion_ganadora(param) {

    var ejecutar_popup = localStorage.getItem("popup_atencion_ganadora");
    var fecha_actual_milisegundo = new Date();
    var numero_milisegundos_actual = fecha_actual_milisegundo.getTime();

    if (numero_milisegundos_actual >= ejecutar_popup) {
        var agregar_una_hora = 60 * 60000;
        //var agregar_una_hora = 15000;
        localStorage.setItem("popup_atencion_ganadora", numero_milisegundos_actual + agregar_una_hora);

        let param_imagen = param.data("imagen");
        let param_size = "";

        if (param_imagen != "") {
            param_size = "450x450";
        }

        swal({
                title: param.data("title"),

                text: '',
                html: true,
                closeOnConfirm: false,
                imageUrl: param_imagen,
                imageSize: param_size,
            },
            function () {
                setTimeout(function () {
                    sec_anuncio_atencion_ganadora(param);
                }, 60000);
                swal.close();
            });
    } else {
        setTimeout(function () {
            sec_anuncio_atencion_ganadora(param);
        }, 60000);
    }
}

function sec_anuncios_inicializar_fechas_formulario_anuncio() {
    $('.anuncio_fecha_datepicker')
        .datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true
        })
        .on("change", function (ev) {
            $(this).datepicker('hide');
            var newDate = $(this).datepicker("getDate");
            $("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
            // localStorage.setItem($(this).atrr("id"),)
        });
}

function set_anuncios_imagen(object) {
    $(document).on('click', '#btn_buscar_anuncio_imagen', function (event) {
        event.preventDefault();
        object.click();
    });

    object.on('change', function (event) {
        const isMultiple = $("#anuncios_check_image_multiple").is(":checked");
        if (isMultiple) {
            if ($(this)[0].files.length <= 1) {
                alertify.error("Debe cargar más de una imagen", 5);
                $("#anuncios_imagen").val("");
                $("#txt_anuncio_imagen").html("");
            }
            truncated = 'Multiples imagenes' + '(' + $(this)[0].files.length + ')';
        }
        //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
        if (!isMultiple) {
            if ($(this)[0].files.length <= 1) {
                const name = $(this).val().split(/\\|\//).pop();
                //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
                truncated = name;
            } else {
                truncated = "";
                //mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLicFuncionamiento'));
                alertify.error("Solo se permite una imagen", 5);
                $("#anuncios_imagen").val("");
            }
        }

        $("#txt_anuncio_imagen").html(truncated ? truncated : "");

    });
}

function set_anuncios_audio(object) {
    $(document).on('click', '#btn_buscar_anuncio_audio', function (event) {
        event.preventDefault();
        object.click();
    });

    object.on('change', function (event) {

        //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
        if ($(this)[0].files.length <= 1) {
            const name = $(this).val().split(/\\|\//).pop();
            //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
            truncated = name;
        } else {
            truncated = "";
            mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLicFuncionamiento'));
            $("#anuncios_audio").val("");
        }

        $("#txt_anuncio_audio").html(truncated);

    });
}

function set_anuncios_video(object) {
    $(document).on('click', '#btn_buscar_anuncio_video', function (event) {
        event.preventDefault();
        object.click();
    });

    object.on('change', function (event) {

        //let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
        if ($(this)[0].files.length <= 1) {
            const name = $(this).val().split(/\\|\//).pop();
            //truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
            truncated = name;
        } else {
            truncated = "";
            mensajeAlerta("Advertencia:", "Solo esta permitido adjuntar un archivo.", claseTipoAlertas.alertaWarning, $('#divMensajeAlertaLicFuncionamiento'));
            $("#anuncios_video").val("");
        }

        $("#txt_anuncio_video").html(truncated);

    });
}

function sec_anuncios_div_table_anuncios() {
    if (sub_sec_id == "anuncios") {
        var table_anuncios = $('#anuncios_div_table_anuncios').DataTable(
            {
                language: {
                    "decimal": "",
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
                    }
                }
                , aLengthMenu: [10, 20, 30, 40, 50]
            });
    }
    $("#anuncios_comprobar_hora_btn").on("click", function () {
        loading(true);
        var no_complete_data = "";
        var anun_data = {};
        anun_data["anuncios_comprobar_hora"] = 1;
        anun_data["anuncios_fecha_desde"] = $("#anuncios_fecha_desde").val();
        anun_data["anuncios_fecha_hasta"] = $("#anuncios_fecha_hasta").val();
        anun_data["anuncios_horario_desde"] = $("#anuncios_horario_desde").val();
        anun_data["anuncios_horario_hasta"] = $("#anuncios_horario_hasta").val();
        anun_data["anuncios_tiempo_anuncio"] = Number($("#anuncios_tiempo_anuncio").val());
        anun_data["anuncios_dias_semana"] = $("#anuncios_input_dias_semana").val();

        let fechaParseada = anun_data["anuncios_fecha_desde"].split("-");
        let fechaDesde = new Date(fechaParseada[2], fechaParseada[1] - 1, fechaParseada[0]);
        fechaParseada = anun_data["anuncios_fecha_hasta"].split("-");
        let fechaHasta = new Date(fechaParseada[2], fechaParseada[1] - 1, fechaParseada[0]);

        if (fechaDesde > fechaHasta) {
            alertify.error('La fecha de inicio debe ser menor a la fecha fin', 5);
            $("#anuncios_fecha_desde").focus();
            $("#anuncios_fecha_hasta").focus();
            loading(false);
            return false;
        }
        if (anun_data["anuncios_horario_desde"] == "" || anun_data["anuncios_horario_hasta"] == "") {
            alertify.error('Debe ingresar horario inicio y horario fin', 5);
            $("#anuncios_horario_desde").focus();
            $("#anuncios_horario_hasta").focus();
            loading(false);
            return false;
        }
        if (anun_data["anuncios_horario_desde"] >= anun_data["anuncios_horario_hasta"]) {
            alertify.error('La hora de inicio debe ser menor a la hora fin', 5);
            $("#anuncios_horario_desde").focus();
            $("#anuncios_horario_hasta").focus();
            loading(false);
            return false;
        }

        if (anun_data["anuncios_dias_semana"] == "") {
            no_complete_data = no_complete_data + "Dias de la semana\n";
        }

        if (anun_data["anuncios_tiempo_anuncio"] == "" || anun_data["anuncios_tiempo_anuncio"] < 1) {
            no_complete_data = no_complete_data + "Reproducir cada (minutos)\n";
        }
        if (anun_data["anuncios_horario_desde"] == "") {
            no_complete_data = no_complete_data + "Horario Inicio\n";
        }
        if (anun_data["anuncios_horario_hasta"] == "") {
            no_complete_data = no_complete_data + "Horario Fin\n";
        }

        if (no_complete_data.length > 0) {
            swal({
                type: "warning",
                title: "Atención",
                text: "Para calcular la disponiblidad falta completar:\n" + no_complete_data,
            });
            loading(false);
        } else {
            $.ajax({
                url: "/sys/set_anuncios.php",
                type: "POST",
                data: anun_data,
                success: function (response) {
                    loading(false);
                    result = JSON.parse(response);
                    if (result.status == 1) {
                        swal({
                            type: "warning",
                            title: "Horario ocupado",
                            text: "El horario escogido está ocupado a las " + result.sel_anuncio_hora + " por el anuncio de ID: " + result.sel_anuncio_id_select,
                        });
                    } else {
                        swal({
                            type: "success",
                            title: "Horario disponible",
                            text: "El horario está disponible para las horas especificadas",
                        });
                    }
                }
            });
        }
    });
}

$("#anuncios_tipo_archivo").on("change", function () {
    var selectValor = $(this).val();

    if (selectValor == '0') {
        $('#anuncios_div_imagen').hide();
        $('#anuncios_div_audio').hide();
        $('#anuncios_div_video').hide();
    } else if (selectValor == '6') {
        $('#anuncios_div_imagen').show();
        $('#anuncios_div_audio').hide();
        $('#anuncios_div_video').hide();
    } else if (selectValor == '7') {
        $('#anuncios_div_imagen').hide();
        $('#anuncios_div_audio').show();
        $('#anuncios_div_video').hide();
    } else if (selectValor == '8') {
        $('#anuncios_div_imagen').hide();
        $('#anuncios_div_audio').hide();
        $('#anuncios_div_video').show();
    }
})

$("#anuncios_tipo_reproduccion").on("change", function () {
    var selectValor = $(this).val();

    if (selectValor == '0' || selectValor == '1') {
        $('#anuncios_div_tiempo_anuncio').hide();
        $('#anuncios_div_horario_desde').hide();
        $('#anuncios_div_horario_hasta').hide();

        $('#anuncios_div_btn_agg_detalle').show();
        $('#anuncios_div_table_detalle').show();
    } else if (selectValor == '2') {
        $('#anuncios_div_tiempo_anuncio').show();
        $('#anuncios_div_horario_desde').show();
        $('#anuncios_div_horario_hasta').show();

        $('#anuncios_div_btn_agg_detalle').hide();
        $('#anuncios_div_table_detalle').hide();
    }
})

var contador_fila_anuncio_detalle_table = 0;
var detalles_table_anuncio_detalle = 0;

function anuncios_agregar_detalle_anuncio() {
    var fila = '<tr id="fila_anuncio_detalle_table' + contador_fila_anuncio_detalle_table + '">' +
        '<td>' +
        '<button type="button" class="btn btn-danger btn-xs" id="boton_guardar_contratos" onclick="anuncios_eliminar_detalle_anuncio(' + contador_fila_anuncio_detalle_table + ');">' +
        '<span class="glyphicon glyphicon-remove"></span>' +
        '</button>' +
        '</td>' +
        '<td>' +
        '<input type="time" name="codigo" class="data_input" style="width: 100px; height: 30px;">' +
        '</td>' +
        '</tr>';

    contador_fila_anuncio_detalle_table++;
    detalles_table_anuncio_detalle++;

    $("#anuncio_detalle_table").append(fila);
}

function anuncios_eliminar_detalle_anuncio(indice) {
    $("#fila_anuncio_detalle_table" + indice).remove();
    detalles_table_anuncio_detalle = detalles_table_anuncio_detalle - 1;
}

$("#btn_prueba_select").on("click", function (e) {
    e.preventDefault();
    var anuncios_grupo_select_filtro = $("#anuncios_grupo_select_filtro").val();
})

function seleccionarDiasDeSemana(dia) {
    dia = parseInt(dia);
    let inputDias = document.getElementById("anuncios_input_dias_semana");
    let inputDiasValue = inputDias.value;
    let arrayDias = inputDiasValue.split(",");

    if (dia == 0) {
        if (inputDiasValue === "") {
            inputDias.value = "1,2,3,4,5,6,7";
            let btnGroup = document.getElementById("anuncios_dias_semana");
            let btns = btnGroup.getElementsByTagName("button");
            for (let i = 0; i < btns.length; i++) {
                btns[i].classList.remove("btn-default");
                btns[i].classList.add("btn-success");
            }
        } else {
            inputDias.value = "";
            let btnGroup = document.getElementById("anuncios_dias_semana");
            let btns = btnGroup.getElementsByTagName("button");
            for (let i = 0; i < btns.length; i++) {
                btns[i].classList.remove("btn-success");
                btns[i].classList.add("btn-default");
            }
        }
    }
    if (dia > 0) {
        let btn = document.getElementById("anuncios_dia_" + dia);
        let btnClass = btn.classList;
        if (btnClass.contains("btn-default")) {
            btnClass.remove("btn-default");
            btnClass.add("btn-success");
            arrayDias.push(dia);
            arrayDias.sort((a, b) => a - b);
            inputDiasValue = arrayDias.join(",");
        } else {
            btnClass.remove("btn-success");
            btnClass.add("btn-default");
            arrayDias.filter((item, index) => {
                if (item == dia) {
                    arrayDias.splice(index, 1);
                }
            });
            arrayDias.sort((a, b) => a - b);
            inputDiasValue = arrayDias.join(",");
        }
        if (inputDiasValue.charAt(0) == ",") {
            inputDiasValue = inputDiasValue.substr(1);
        }
        if (inputDiasValue.charAt(inputDiasValue.length - 1) == ",") {
            inputDiasValue = inputDiasValue.substr(0, inputDiasValue.length - 1);
        }
        inputDias.value = inputDiasValue;
    }
}


$(document).on('submit', "#anuncios_formulario_anuncios", function (e) {
    //debugger;
    e.preventDefault();

    var anuncios_texto = $("#anuncios_texto").val();
    anuncios_texto = anuncios_texto.trim();
    $("#anuncios_texto").val(anuncios_texto);
    var anuncios_fecha_desde = $("#anuncios_fecha_desde").val();
    var anuncios_fecha_hasta = $("#anuncios_fecha_hasta").val();
    var anuncios_dias_semana = $("#anuncios_input_dias_semana").val();
    var anuncios_tipo_archivo = $("#anuncios_tipo_archivo").val();

    var anuncios_imagen = document.getElementById("anuncios_imagen");
    var anuncios_audio = document.getElementById("anuncios_audio");
    var anuncios_video = document.getElementById("anuncios_video");

    var anuncios_tipo_reproduccion = $("#anuncios_tipo_reproduccion").val();
    var anuncios_tiempo_anuncio = Number($("#anuncios_tiempo_anuncio").val());
    var anuncios_horario_desde = $("#anuncios_horario_desde").val();
    var anuncios_horario_hasta = $("#anuncios_horario_hasta").val();

    var anuncios_area_select_filtro = $("#anuncios_area_select_filtro").val();
    var anuncios_grupo_select_filtro = $("#anuncios_grupo_select_filtro").val();

    var array_input = new Array();
    var input_value = document.getElementsByClassName('data_input');

    if (anuncios_texto == null || anuncios_texto === '') {
        alertify.error('Ingrese el nombre del anuncio', 5);
        $("#anuncios_texto").focus();
        return false;
    }
    if (anuncios_texto.length > 1000) {
        alertify.error('El nombre no debe ser mayor a 100 caracteres', 5);
        $("#anuncios_texto").focus();
        return false;
    }

    let fechaParseada = anuncios_fecha_desde.split("-");
    let fechaDesde = new Date(fechaParseada[2], fechaParseada[1] - 1, fechaParseada[0]);
    fechaParseada = anuncios_fecha_hasta.split("-");
    let fechaHasta = new Date(fechaParseada[2], fechaParseada[1] - 1, fechaParseada[0]);

    if (fechaDesde > fechaHasta) {
        alertify.error('La fecha de inicio debe ser menor o igual a la fecha fin', 5);
        $("#anuncios_fecha_desde").focus();
        $("#anuncios_fecha_hasta").focus();
        return false;
    }

    if (anuncios_dias_semana == null || anuncios_dias_semana === '') {
        alertify.error('Seleccione los días de la semana', 5);
        return false;
    }
    const imagesPermitidas = ['jpg', 'jpeg', 'png'];
    const audiosPermitidos = ['mp3'];
    const videosPermitidos = ['mp4', 'avi', 'mov'];
    if (parseInt(anuncios_tipo_archivo) != "0") {
        if (parseInt(anuncios_tipo_archivo) == "6") {
            if (anuncios_imagen.files.length == 0) {
                alertify.error('Seleccione la imagen', 5);
                $("#anuncios_imagen").focus();
                return false;
            }
            const imgs = anuncios_imagen.files;
            let validacion = false;
            for (let i = 0; i < imgs.length; i++) {
                const extension = imgs[i].name.split('.').pop().toLowerCase();
                if (!imagesPermitidas.includes(extension)) {
                    swal("Error", "Solo se permiten imagenes con extensión jpg, jpeg y png", "error");
                    return false;
                } else {
                    validacion = false;
                }
                let size = imgs[i].size;
                if (size > 2000000) {
                    swal("Error", "El tamaño de la imagen no debe ser mayor a 2MB", "error");
                    return false;
                }
            }
        } else if (parseInt(anuncios_tipo_archivo) == "7") {
            if (anuncios_audio.files.length == 0) {
                alertify.error('Seleccione el audio', 5);
                $("#anuncios_audio").focus();
                return false;
            }
            const audios = anuncios_audio.files;
            for (let i = 0; i < audios.length; i++) {
                const extension = audios[i].name.split('.').pop().toLowerCase();
                if (!audiosPermitidos.includes(extension)) {
                    swal("Error", "Solo se permiten audios con extensión mp3", "error");
                    return false;
                }
                let size = audios[i].size;
                if (size > 2000000) {
                    swal("Error", "El tamaño del audio no debe ser mayor a 2MB", "error");
                    return false;
                }
            }
        } else if (parseInt(anuncios_tipo_archivo) == "8") {
            if (anuncios_video.files.length == 0) {
                alertify.error('Seleccione el video', 5);
                $("#anuncios_video").focus();
                return false;
            }
            const videos = anuncios_video.files;
            for (let i = 0; i < videos.length; i++) {
                const extension = videos[i].name.split('.').pop().toLowerCase();
                if (!videosPermitidos.includes(extension)) {
                    swal("Error", "Solo se permiten videos con extensión mp4, avi y mov", "error");
                    return false;
                }
                let size = videos[i].size;
                if (size > 2000000) {
                    swal("Error", "El tamaño del video no debe ser mayor a 2MB", "error");
                    return false;
                }
            }
        }
    } else {
        alertify.error('Seleccione el tipo archivo', 5);
        $("#anuncios_tipo_archivo").focus();
        return false;
    }

    if (parseInt(anuncios_tipo_reproduccion) != "0") {
        if (parseInt(anuncios_tipo_reproduccion) == "1") {
            if (input_value.length != 0) {
                for (var i = 0; i < input_value.length; i++) {
                    //alert("Valor Celda: " + input_value[i].value);

                    if (input_value[i].value == "") {
                        alertify.error('Ingrese la hora en el detalle', 5);
                        return false;
                    }
                    array_input.push(input_value[i].value);
                }
            } else {
                alertify.error('Agregar horario de detalle', 5);
                $("#btn_agg_detalle_horario").focus();
                return false;
            }

        } else if (parseInt(anuncios_tipo_reproduccion) == "2") {
            if (anuncios_tiempo_anuncio == "" || anuncios_tiempo_anuncio < "1") {
                alertify.error('Ingresar tiempo a reproducir (minutos)', 5);
                $("#anuncios_tiempo_anuncio").focus();
                return false;
            }

            if (anuncios_horario_desde == "") {
                alertify.error('Ingresar horario inicio', 5);
                $("#anuncios_horario_desde").focus();
                return false;
            }

            if (anuncios_horario_hasta == "") {
                alertify.error('Ingresar horario fin', 5);
                $("#anuncios_horario_hasta").focus();
                return false;
            }
        }
    } else {
        alertify.error('Seleccione el tipo reproduccion', 5);
        $("#anuncios_tipo_reproduccion").focus();
        return false;
    }

    if (anuncios_horario_desde >= anuncios_horario_hasta) {
        alertify.error('La hora de inicio debe ser menor a la hora fin', 5);
        $("#anuncios_horario_desde").focus();
        $("#anuncios_horario_hasta").focus();
        return false;
    }
    if (anuncios_area_select_filtro === null) {
        alertify.error('Seleccione el objetivo por área', 5);
        $("#anuncios_grupo_select_filtro").focus();
        return false;
    }

    if (anuncios_grupo_select_filtro != null) {
        anuncios_grupo_select_filtro = anuncios_grupo_select_filtro.toString();
    }
    if (anuncios_grupo_select_filtro === null) {
        alertify.error('Seleccione el objetivo por grupo', 5);
        $("#anuncios_grupo_select_filtro").focus();
        return false;
    }

    var form_data = (new FormData(this));
    form_data.append("anuncios_formulario_anuncios", 1);
    form_data.append("anuncios_texto", anuncios_texto);
    form_data.append("anuncios_fecha_desde", anuncios_fecha_desde);
    form_data.append("anuncios_fecha_hasta", anuncios_fecha_hasta);
    form_data.append("anuncios_tipo_archivo", anuncios_tipo_archivo);

    form_data.append("anuncios_tipo_reproduccion", anuncios_tipo_reproduccion);
    form_data.append("anuncios_tiempo_anuncio", anuncios_tiempo_anuncio);
    form_data.append("anuncios_horario_desde", anuncios_horario_desde);
    form_data.append("anuncios_horario_hasta", anuncios_horario_hasta);
    form_data.append("anuncios_dias_semana", anuncios_dias_semana);
    form_data.append("detalle_horarios", JSON.stringify(array_input));

    form_data.append("anuncios_area_select_filtro", anuncios_area_select_filtro);
    form_data.append("anuncios_grupo_select_filtro", anuncios_grupo_select_filtro);

    //auditoria_send({ "Anuncios": "anuncios_formulario_anuncios", "data": form_data });

    if (anuncios_imagen.files.length > 1) {
        form_data.delete('anuncios_imagen');
        const images = anuncios_imagen.files;
        for (let i = 0; i < images.length; i++) {
            form_data.append("anuncios_imagenes[]", images[i]);
        }
    } else {
        form_data.delete('anuncios_imagenes[]');
    }

    loading(true);

    $.ajax({
        url: "/sys/set_anuncios.php",
        type: "POST",
        data: form_data,
        cache: false,
        contentType: false,
        processData: false,
        success: function (response, status) {
            result = JSON.parse(response);
            loading();
            if (result.status) {
                swal(result.message, "", "success");
                setTimeout(function () {
                    loading(true);
                    window.location.href = "?sec_id=anuncios&sub_sec_id=anuncios";
                    return false;
                }, 2000);
            } else {
                swal(
                    {
                        type: "warning",
                        title: "Alerta!",
                        text: result.message,
                        html: true,
                    });
            }
        },
        always: function (data) {
            loading();
        }
    });
});

var anuncios_count_timer;

function anuncios_llamar_anuncio() {
    anuncios_count_timer = setInterval(anuncios_obtener_fecha_del_ultimo_cambio, 60000);
    anuncios_mostar_count_timer = setInterval(anuncios_mostrar_anuncio, 60000);
}

function anuncios_obtener_anuncio() {
    var form_data = (new FormData());
    form_data.append("anuncios_obtener_anuncio_disponible", 1);

    $.ajax({
        url: "/sys/set_anuncios.php",
        type: "POST",
        data: form_data,
        cache: false,
        contentType: false,
        processData: false,
        success: function (response, status) {
            //debugger;
            result = JSON.parse(response);
            loading();
            if (result.status) {
                //Imagen
                if (result.id_tipo_archivo == "6") {
                    swal({
                        title: "",
                        text: result.anuncio_texto,
                        html: true,
                        closeOnConfirm: false,
                        imageUrl: result.anuncio,
                        imageSize: '400x400',
                    })
                }
                //Audio
                else if (result.id_tipo_archivo == "7") {
                    mostrar_video(result.anuncio);
                }
                //Video
                else if (result.id_tipo_archivo == "8") {
                    mostrar_video(result.anuncio);
                }
            } else {
                swal(
                    {
                        type: "warning",
                        title: "Alerta!",
                        text: result.message,
                        html: true,
                    });
            }
        }
    });

}

function anuncios_obtener_fecha_del_ultimo_cambio() {
    // var hoy = new Date();
    // var fecha_actual = hoy.getFullYear() + '-' + (hoy.getMonth() + 1) + '-' + hoy.getDate();
    // var form_data = (new FormData());
    // form_data.append("accion", "obtener_fecha_del_ultimo_cambio");

    // $.ajax({
    //     url: "/sys/set_anuncios.php",
    //     global: false,
    //     type: "POST",
    //     data: form_data,
    //     cache: false,
    //     contentType: false,
    //     processData: false,
    //     success: function (response, status) {
    //         //debugger;
    //         result = JSON.parse(response);
    //         if (result.http_code == "200") {
    //             if (result.fecha != localStorage.getItem("fecha_del_ultimo_cambio_en_anuncios") || fecha_actual != localStorage.getItem("fecha_actual_localStorage")) {
    //                 anuncios_actualizar_localstorage(result.fecha, fecha_actual);
    //             }
    //         }
    //         ///loading();
    //     }
    // });
}

function anuncios_actualizar_localstorage(fecha_del_ultimo_cambio_en_anuncios, fecha_actual) {
    localStorage.setItem("fecha_del_ultimo_cambio_en_anuncios", fecha_del_ultimo_cambio_en_anuncios);
    localStorage.setItem("fecha_actual_localStorage", fecha_actual);

    var form_data = (new FormData());
    form_data.append("accion", "obtener_anuncios_del_dia_de_hoy");
    form_data.append("anuncios_area_id", anuncios_area_id);
    form_data.append("anuncios_grupo_id", anuncios_grupo_id);

    $.ajax({
        url: "/sys/set_anuncios.php",
        type: "POST",
        data: form_data,
        cache: false,
        contentType: false,
        processData: false,
        success: function (response, status) {
            //debugger;
            result = JSON.parse(response);
            localStorage.setItem("array_de_anuncios", JSON.stringify(result.array_anuncios));
            //loading();
        }
    });
}

function anuncios_mostrar_anuncio() {
    if (localStorage.getItem("array_de_anuncios") && JSON.parse(localStorage.getItem("array_de_anuncios")).length != 0) {
        var hoy = new Date();
        var hora = (hoy.getHours() < 10 ? '0' : '') + hoy.getHours() + ':' + (hoy.getMinutes() < 10 ? '0' : '') + hoy.getMinutes();
        let array_anuncios = JSON.parse(localStorage.getItem("array_de_anuncios"));
        const anuncio_a_presentar = array_anuncios.find(anuncio => anuncio.minuto === hora);
        const anuncio_a_presentar_index = array_anuncios.findIndex(anuncio => anuncio.minuto === hora);
        if (!(typeof anuncio_a_presentar === 'undefined')) {
            //Imagen
            if (anuncio_a_presentar.tipo == "6") {
                /*swal({
                    title: "",
                    text: "",
                    html:true,
                    closeOnConfirm: false,
                    imageUrl: anuncio_a_presentar.anuncio,
                    imageSize: '400x400',
                    customClass: {
                        confirmButton: 'btn btn-success',
                        container : 'bg-danger',
                    }
                })*/
                const imagenes = anuncio_a_presentar.anuncio.split(",");

                if (imagenes.length > 1) {
                    const imagenesUploading = imagenes.map(imagen => {
                        return new Promise((resolve, reject) => {
                            const imagenUploading = new Image();
                            imagenUploading.onload = function () {
                                resolve(imagenUploading);
                            }
                            imagenUploading.src = imagen;
                        });
                    });

                    Promise.all(imagenesUploading).then(imagenes => {
                        var screen_width = $(window).width();
                        var screen_height = $(window).height();
                        screen_width = (screen_width * 65) / 100;

                        ///find the biggest width
                        var max_width = 0;
                        var max_height = 0;
                        for (var i = 0; i < imagenes.length; i++) {
                            if (imagenes[i].width > max_width) {
                                max_width = imagenes[i].width;
                            }
                            if (imagenes[i].height > max_height) {
                                max_height = imagenes[i].height;
                            }
                        }

                        var imagenWidth = max_width;
                        var imagenHeight = max_height;

                        if (imagenWidth > screen_width) {
                            imagenHeight = (screen_width * imagenHeight) / imagenWidth;
                            imagenWidth = screen_width;
                        }
                        if (imagenHeight > screen_height) {
                            imagenWidth = (screen_height * imagenWidth) / imagenHeight;
                            imagenHeight = screen_height;
                        }

                        var currentItemCarousel = 0;
                        //<img src="${imagenes[currentItemCarousel].currentSrc}" alt="imagen" width="100%" height="100%" id="item_carousel">
                        let htmlImagenes = '';
                        for (let i = 0; i < imagenes.length; i++) {
                            if (i === 0) {
                                htmlImagenes += `<img src="${imagenes[i].currentSrc}" alt="imagen" width="${imagenWidth}" height="${imagenHeight}" id="item_carousel_${i}" style="display:block;">`;
                            } else {
                                htmlImagenes += `<img src="${imagenes[i].currentSrc}" alt="imagen" width="${imagenWidth}" height="${imagenHeight}" id="item_carousel_${i}" style="display:none;">`;
                            }
                        }
                        const carousel = `
						<div class="anuncio_slider-wrapper">
							  <button class="anuncio_slide-arrow" id="anuncio_slide-arrow-prev">
								&#8249;
							  </button>
							  <button class="anuncio_slide-arrow" id="anuncio_slide-arrow-next">
								&#8250;
							  </button>
							  <div class="anuncio_slides-container" id="anuncio_slides-container">
								${htmlImagenes}
							  </div>
							</div>
						`;

                        const carouselCss = `
						<style>
							.anuncio_slider-wrapper {
							  position: relative;
							  overflow: hidden;
							}
							
							.anuncio_slide-arrow {
							  position: absolute;
							  display: flex;
							  top: 0;
							  bottom: 0;
							  margin: auto;
							  height: 4rem;
							  background-color: white;
							  border: none;
							  width: 2rem;
							  font-size: 3rem;
							  padding: 0;
							  cursor: pointer;
							  opacity: 0.5;
							  transition: opacity 100ms;
							}
							
							.anuncio_slide-arrow:hover,
							.anuncio_slide-arrow:focus {
							  opacity: 1;
							}
							
							#anuncio_slide-arrow-prev {
							  left: 0;
							  padding-left: 0.25rem;
							  border-radius: 0 2rem 2rem 0;
							}
							
							#anuncio_slide-arrow-next {
							  right: 0;
							  padding-left: 0.75rem;
							  border-radius: 2rem 0 0 2rem;
							}
													</style>
						`;


                        const anuncioClasses = `<style>
						.anuncio-dialog {
							width: ${imagenWidth + 30}px;
							height: ${imagenHeight + 30}px;
							padding: 0;
						}
						</style>`;
                        $("head").append(anuncioClasses);
                        $("head").append(carouselCss);
                        const modal = $("#anuncio_modal_imagen");
                        const dialog = modal.find(".modal-dialog");
                        dialog.addClass("anuncio-dialog");
                        modal.find(".modal-body").html(carousel);
                        modal.modal("show");
                        const anuncioPrevButton = document.getElementById("anuncio_slide-arrow-prev");
                        const anuncioNextButton = document.getElementById("anuncio_slide-arrow-next");

                        anuncioNextButton.addEventListener("click", () => {
                            currentItemCarousel++;
                            if (currentItemCarousel >= imagenes.length) {
                                currentItemCarousel = 0;
                            }
                            const currentImagen = document.getElementById(`item_carousel_${currentItemCarousel}`);
                            const imagenesInCarousel = document.querySelectorAll(".anuncio_slides-container img");
                            imagenesInCarousel.forEach(imagen => {
                                imagen.style.display = "none";
                            });
                            currentImagen.style.display = "block";

                        });

                        anuncioPrevButton.addEventListener("click", () => {
                            currentItemCarousel--;
                            if (currentItemCarousel < 0) {
                                currentItemCarousel = imagenes.length - 1;
                            }
                            const currentImagen = document.getElementById(`item_carousel_${currentItemCarousel}`);
                            const imagenesInCarousel = document.querySelectorAll(".anuncio_slides-container img");
                            imagenesInCarousel.forEach(imagen => {
                                imagen.style.display = "none";
                            });
                            currentImagen.style.display = "block";
                        });

                        // set timeout to change image every 2 seconds
                        setInterval(() => {
                            currentItemCarousel++;
                            if (currentItemCarousel >= imagenes.length) {
                                currentItemCarousel = 0;
                            }
                            const currentImagen = document.getElementById(`item_carousel_${currentItemCarousel}`);
                            const imagenesInCarousel = document.querySelectorAll(".anuncio_slides-container img");
                            imagenesInCarousel.forEach(imagen => {
                                imagen.style.display = "none";
                            });
                            currentImagen ? currentImagen.style.display = "block" : null;
                        }, 2000);
                    });
                }
                if (imagenes.length === 1) {
                    const imagen = new Image();
                    imagen.onload = function () {
                        var screen_width = $(window).width();
                        var screen_height = $(window).height();
                        screen_width = (screen_width * 65) / 100;

                        var imagenWidth = imagen.width;
                        var imagenHeight = imagen.height;

                        if (imagenWidth > screen_width) {
                            imagenHeight = (screen_width * imagenHeight) / imagenWidth;
                            imagenWidth = screen_width;
                        }
                        if (imagenHeight > screen_height) {
                            imagenWidth = (screen_height * imagenWidth) / imagenHeight;
                            imagenHeight = screen_height;
                        }


                        const anuncioClasses = `<style>
						.anuncio-dialog {
							width: ${imagenWidth}px;
							height: ${imagenHeight}px;
							padding: 0;
						}
						</style>`;
                        $("head").append(anuncioClasses);
                        const modal = $("#anuncio_modal_imagen");
                        const dialog = modal.find(".modal-dialog");
                        dialog.addClass("anuncio-dialog");
                        //const text = `<p class="m-1">${anuncio_a_presentar.texto}</p>`;
                        modal.find(".modal-body").html('<img src="' + anuncio_a_presentar.anuncio + '" width="100%" height="100%">');
                        //modal.find(".modal-body").append(text);
                        modal.modal("show");
                    }
                    imagen.src = anuncio_a_presentar.anuncio;
                }
            }
            //Audio
            else if (anuncio_a_presentar.tipo == "7") {
                mostrar_video(anuncio_a_presentar.anuncio);
            }
            //Video
            else if (anuncio_a_presentar.tipo == "8") {
                mostrar_video(anuncio_a_presentar.anuncio);
            }

            var new_array_anuncios = array_anuncios.splice(anuncio_a_presentar_index, 1);
            localStorage.setItem("array_de_anuncios", JSON.stringify(array_anuncios));
        }
    }
}

function mostrar_video(download) {
    anuncios_contenido_modal_anuncio_video.innerHTML = '<video id="video_archivo" width="70%" height="150" controls><source src=' + download + '></video>';
    //$('#anuncio_modal_video').modal('show');
    reproducir_video_play();
}

function reproducir_video_play() {
    var video = document.getElementById('video_archivo');
    video.play();
}

function anuncio_fire_popup(a) {
    var expiration = parseInt(localStorage.getItem("anuncio_popup_" + a.data("id")));
    if (!expiration) {
        expiration = 0;
    }

    let id = a.data("id")
    let image = a.data("imagen")
    let size = ""
    switch (id) {
        case 8:
        case 9:
            size = '200x200'
            break;
        case 10:
            size = '400x400'
            break;
        case 12:
            sound_notification(a.data("text"))
            return;
        case 13:
            size = '400x400'
            break;
        //sound_notification_video(a.data("text"))
        //return;
    }

    if (expiration < Date.now()) {
        swal({
                title: a.data("title"),
                text: a.data("text"),
                html: true,
                closeOnConfirm: false,
                imageUrl: image,
                imageSize: size,
            },
            function () {
                localStorage.setItem("anuncio_popup_" + a.data("id"), (Date.now() + (a.data("timer") * 1000)));
                setTimeout(function () {
                    anuncio_fire_popup(a);
                }, (a.data("timer") * 1000));
                swal.close();
            });
    } else {
        setTimeout(function () {
            anuncio_fire_popup(a);
        }, (a.data("timer") * 1000));
    }
}

function sound_notification(text) {
    const notification = new Audio('sounds/notification.mp3');
    let now = new Date();

    let triggerA = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 11, 0, 0, 0)
    let triggerB = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 16, 0, 0, 0)
    let currentTime = now.getTime()
    let millisecondsToA = triggerA - currentTime;
    let millisecondsToB = triggerB - currentTime;

    if (millisecondsToA > 0) {
        setTimeout(function () {
            notification.play()
            swal({title: "ANUNCIO", text: text, closeOnConfirm: false})
        }, millisecondsToA);
    }

    if (millisecondsToB > 0) {
        setTimeout(function () {
            notification.play()
            swal({title: "ANUNCIO", text: text, closeOnConfirm: false})
        }, millisecondsToB);
    }
}

function sound_notification_video(text) {
    const notification = new Audio('sounds/notification.mp3');
    let now = new Date();

    let triggerA = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 11, 0, 0, 0)
    let triggerB = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 16, 0, 0, 0)
    let currentTime = now.getTime()
    let millisecondsToA = triggerA - currentTime;
    let millisecondsToB = triggerB - currentTime;

    if (millisecondsToA > 0) {
        setTimeout(function () {
            notification.play()
            swal({title: "ANUNCIO", text: text, closeOnConfirm: false})
        }, millisecondsToA);
    }

    if (millisecondsToB > 0) {
        setTimeout(function () {
            notification.play()
            swal({title: "ANUNCIO", text: text, closeOnConfirm: false})
        }, millisecondsToB);
    }
}


function anuncio_fire_popup_cajero(a) {
    var expiration = parseInt(localStorage.getItem("anuncio_popup_" + a.data("id")));
    if (!expiration) {
        expiration = 0;
    }

    let id = a.data("id");
    let image = a.data("imagen");
    let size = '400x400';

    if (expiration < Date.now()) {
        swal({
                title: a.data("title"),
                text: a.data("text"),
                html: true,
                closeOnConfirm: false,
                imageUrl: image,
                imageSize: size,
            },
            function () {
                localStorage.setItem("anuncio_popup_" + a.data("id"), (Date.now() + (a.data("timer") * 1000)));
                setTimeout(function () {
                    var anuncio_random = $($(".anuncio_popup[data-estado='6']")[Math.floor(Math.random() * ($(".anuncio_popup[data-estado='6']")).length)]);
                    anuncio_fire_popup_cajero(anuncio_random);
                }, (a.data("timer") * 1000));
                swal.close();
            });
    } else {
        setTimeout(function () {
            var anuncio_random = $($(".anuncio_popup[data-estado='6']")[Math.floor(Math.random() * ($(".anuncio_popup[data-estado='6']")).length)]);
            anuncio_fire_popup_cajero(anuncio_random);
        }, (a.data("timer") * 1000));
    }
}

var mi_ruta_temporal = '';

function sec_anuncios_ver_archivo(anuncio_id, ruta, tipodocumento, fe_ini_reprod, fe_fin_reprod, hora_desde_reprod, hora_hasta_reprod, frecuencia_reprod, area_nombre, id_grupos) {
    mi_ruta_temporal = ruta;
    var tipodocumento = tipodocumento.toLowerCase();
    var html = '';
    var destino = '';
    var detalle_programacion = '';
    var titulo_panel = "Detalle Programación - ID: " + anuncio_id;
    let docs = ruta.split(',');

    detalle_programacion = '<table class="table table-bordered table-hover">' +
        '<tr style="text-transform: none; background-color: #81bfe6; ">' +
        '<td colspan="2"><b>Fechas a Reproducir:</b></td>' +
        '</tr>' +
        '<tr style="text-transform: none;">' +
        '<td style="width: 170px; text-align: left;"><b>Inicio:</b></td>' +
        '<td>' + fe_ini_reprod + '</td>' +
        '</tr>' +
        '<tr style="text-transform: none;">' +
        '<td style="width: 100px; text-align: left;"><b>Fin:</b></td>' +
        '<td>' + fe_fin_reprod + '</td>' +
        '</tr>' +
        '<tr style="text-transform: none; background-color: #81bfe6; ">' +
        '<td colspan="2"><b>Horarios a Reproducir:</b></td>' +
        '</tr>' +
        '<tr style="text-transform: none;">' +
        '<td style="width: 170px; text-align: left;"><b>Inicio:</b></td>' +
        '<td>' + hora_desde_reprod + '</td>' +
        '</tr>' +
        '<tr style="text-transform: none;">' +
        '<td style="width: 170px; text-align: left;"><b>Fin:</b></td>' +
        '<td>' + hora_hasta_reprod + '</td>' +
        '</tr> ' +
        '<tr style="text-transform: none;">' +
        '<td style="width: 170px; text-align: left;"><b>Frecuencia:</b></td>' +
        '<td>' + frecuencia_reprod + ' minuto(s)</td>' +
        '</tr> ' +
        '<tr style="text-transform: none; background-color: #81bfe6; ">' +
        '<td colspan="2"><b>Área y grupos objetivo:</b></td>' +
        '</tr>' +
        '<tr style="text-transform: none;">' +
        '<td style="width: 170px; text-align: left;"><b>Área:</b></td>' +
        '<td>' + area_nombre + '</td>' +
        '</tr>' +
        '<tr style="text-transform: none;">' +
        '<td style="width: 170px; text-align: left;"><b>Grupos:</b></td>' +
        '<td>' + id_grupos + '</td>' +
        '</tr> ' +
        '</table>';


    $('#sec_anuncios_div_detalle_programacion_anuncio').html(detalle_programacion);

    if (tipodocumento == 'avi' || tipodocumento == 'flv' || tipodocumento == 'wmv' || tipodocumento == 'mov' || tipodocumento == 'mp4') {
        html = '<video width="100%" height="150" controls><source src="' + mi_ruta_temporal + '"></video>';
        $('#sec_anuncios_div_visor_ver_archivo_full_pantalla').hide();

        $('#sec_anuncios_div_nombre_panel_visor_titulo_ver_archivo').html(titulo_panel);
        $('#sec_anuncios_div_panel_ver_anuncio').show();
        $('#sec_anuncios_div_nombre_panel_visor_titulo_ver_archivo').show();
        $('#sec_anuncios_div_visor_ver_archivo').html(html);
        $('#sec_anuncios_div_visor_ver_archivo').show();
    } else if (tipodocumento == 'mp3') {
        html = '<video width="100%" height="100" controls> <source src="' + mi_ruta_temporal + '"></video>';
        $('#sec_anuncios_div_visor_ver_archivo_full_pantalla').hide();

        $('#sec_anuncios_div_nombre_panel_visor_titulo_ver_archivo').html(titulo_panel);
        $('#sec_anuncios_div_panel_ver_anuncio').show();
        $('#sec_anuncios_div_nombre_panel_visor_titulo_ver_archivo').show();
        $('#sec_anuncios_div_visor_ver_archivo').html(html);
        $('#sec_anuncios_div_visor_ver_archivo').show();
    } else if (tipodocumento == 'jpg' || tipodocumento == 'png' || tipodocumento == 'jpeg') {
        html = '<img src="' + mi_ruta_temporal + '" class="img-responsive" style="border: 1px solid;">';
        $('#sec_anuncios_div_visor_ver_archivo_full_pantalla').show();

        document.getElementById('sec_anuncios_ver_imagen_full_pantalla').removeEventListener('click', sec_anuncios_ver_imagen_full_pantalla);
        document.getElementById('sec_anuncios_ver_imagen_full_pantalla').addEventListener('click', sec_anuncios_ver_imagen_full_pantalla);

        $('#sec_anuncios_div_nombre_panel_visor_titulo_ver_archivo').html(titulo_panel);
        $('#sec_anuncios_div_panel_ver_anuncio').show();
        $('#sec_anuncios_div_nombre_panel_visor_titulo_ver_archivo').show();
        $('#sec_anuncios_div_visor_ver_archivo').html(html);
        $('#sec_anuncios_div_visor_ver_archivo').show();
        $('#sec_anuncios_ver_imagen_full_pantalla').show();
    } else if (docs.length > 1) {
        html = `
				<div>
					<span class="text-info">Puede hacer clic en la imagen para verla en pantalla completa.</span> 
				</div>
				<div class="row">
				`;
        for (var i = 0; i < docs.length; i++) {
            html += `<img src="${docs[i]}" class="img-responsive" style="border: 1px solid; cursor: pointer;" onclick="sec_anuncios_ver_imagen_full_pantalla2('${docs[i]}')">`;
        }
        html += '</div>';
        document.getElementById('sec_anuncios_ver_imagen_full_pantalla').removeEventListener('click', sec_anuncios_ver_imagen_full_pantalla);
        $('#sec_anuncios_div_visor_ver_archivo_full_pantalla').show();

        $('#sec_anuncios_div_nombre_panel_visor_titulo_ver_archivo').html(titulo_panel);
        $('#sec_anuncios_div_panel_ver_anuncio').show();
        $('#sec_anuncios_div_nombre_panel_visor_titulo_ver_archivo').show();
        $('#sec_anuncios_div_visor_ver_archivo').html(html);
        $('#sec_anuncios_div_visor_ver_archivo').show();
        $('#sec_anuncios_ver_imagen_full_pantalla').hide();
    }
}

function sec_anuncios_ver_imagen_full_pantalla() {
    var image = new Image();
    image.src = mi_ruta_temporal;
    var viewer = new Viewer(image, {
        hidden: function () {
            viewer.destroy();
        },
    });
// image.click();
    viewer.show();
}

function sec_anuncios_ver_imagen_full_pantalla2(img) {
    var image = new Image();
    image.src = img;
    var viewer = new Viewer(image, {
        hidden: function () {
            viewer.destroy();
        },
    });
// image.click();
    viewer.show();
}

function sec_anuncio_eliminar_anuncio(anuncio_id) {
    //debugger;
    swal(
        {
            title: '¿Está seguro de eliminar el anuncio?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si',
            cancelButtonText: 'No',
            closeOnConfirm: false,
            closeOnCancel: true
        },
        function (isConfirm) {
            if (isConfirm) {
                var data = {
                    "accion": "sec_anuncio_eliminar_anuncio",
                    "anuncio_id": anuncio_id
                }

                auditoria_send({"proceso": "sec_anuncio_eliminar_anuncio", "data": data});

                $.ajax({
                    url: "/sys/set_anuncios.php",
                    type: "POST",
                    data: data,
                    //contentType : false,
                    //processData : false,
                    beforeSend: function (xhr) {
                        loading(true);
                    },
                    success: function (resp) {
                        var respuesta = JSON.parse(resp);

                        if (respuesta.status) {
                            swal({
                                title: "Eliminado!",
                                text: respuesta.message,
                                type: "success",
                                timer: 5000,
                                closeOnConfirm: false
                            });
                        } else {
                            swal({
                                title: "Error!",
                                text: "Ocurrio un error: " + respuesta.message + ", pongase en contacto con el personal de SOPORTE",
                                type: "warning",
                                timer: 5000,
                                closeOnConfirm: false
                            });
                        }
                        //tabla.ajax.reload();
                    },
                    complete: function () {
                        loading(false);
                        location.reload(true);
                    }
                });
            }
        }
    );

}
