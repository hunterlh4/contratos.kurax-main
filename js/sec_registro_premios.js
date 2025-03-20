function registro_premios() {
    // tipo de documento 0 dni; 1 CE , 2 PASSPORT
    if (sec_id == "registro") {
        if (sub_sec_id == "premios") {
            $('#paidLocal').select2();
            $('.search_Ce').toggle(false);
            $('.search_Ps').toggle(false);
            registro_premios_events();
            actualizar_tbl();

            $('input[type=radio]').on('click', function () {
                var element = $(this);

                if (element.parent().parent().parent().hasClass('btn_tipo_premio')) {

                    var dos = $('input[name="tipoDoc"]:checked').val();
                    console.log(dos);
                    switch (dos) {
                        case 'DNI':
                            $('#txtDniCliente').focus();
                            break;
                        case 'CE_PTP':
                            $('#Nro_doc').focus();
                            break;
                        case 'PS':
                            $('#Nro_doc_Ps').focus();
                            break;
                        default:

                    }

                    $('input[type=radio]').parent().removeClass('clicked_t');
                    element.parent().addClass('clicked_t');
                }
            });

            $('input[type=radio]').on('click', function () {
                var element = $(this);
                if (element.parent().parent().hasClass('btnRadio') && !element.parent().parent().hasClass('radioCheck')) {
                    $('input[type=radio]').parent().removeClass('clicked_o');
                    element.parent().addClass('clicked_o');
                }
            });

            $("input[name='checkAut']").on('click', function () {
                var element = $(this);
                $('.btnSavePrint').attr('disabled', false);
                $('.radioCheck').removeClass('clicked_o');
                $('.radioCheck').removeClass('clicked_no');
                if (element.attr('id') === "checkAut") {
                    element.parent().addClass('clicked_o');
                } else {
                    element.parent().addClass('clicked_no');
                }

            });

            $('#checkAut').on('click', function () {
                var padre = $(this).parent();
                var clase = padre.hasClass('clicked');
                if (clase) {
                    padre.removeClass('clicked');
                    $('#btnSaveSig').attr('disabled', 'disabled');
                } else {
                    padre.addClass('clicked');
                    $('#btnSaveSig').removeAttr('disabled');
                }
            });


            function actualizar_tbl() {
                let datas = {};
                datas.update = true;
                datas.isCashier = $('#paidLocal').data('isCashier')
                datas.localId = $('#paidLocal').val()
                $.post('/sys/get_registro_premios.php', {"listado_tickets_jackpot": datas}, function (data) {
                    loading();
                    $('.actTableBody').html('');
                    let result = JSON.parse(data);
                    if (result.code == "001") {
                        $('.actTableBody').html(result.message);
                    } else {
                        console.log(result.error);
                    }
                });
            };

        }
    }
};

var regprem_validate_tls= 0;

function registro_premios_events() {
    get_turno();
    actualizar_tbl();

    $('#txtDniCliente').keyup(function (e) {
        if (/\D{1,9}/g.test(this.value)) {
            this.value = this.value.replace(/\D/g, '');
        }
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8);
        }
    });

    $('#Nro_doc').keyup(function (e) {
        if (this.value.length > 12) {
            this.value = this.value.slice(0, 12);
        }
    });

    $('#Nro_doc_Ps').keyup(function (e) {
        if (this.value.length > 12) {
            this.value = this.value.slice(0, 12);
        }
    });

    $('#btnClean').on('click', function () {
        clean();
        reset();
    });

    $('.btnSaveSig').on('click', function (e) {
        e.preventDefault();
        guardar(1);
        reset();
    });

    $('.btnSavePrint').on('click', function (e) {
        e.preventDefault();
        loading();
        guardar(2);
        reset();
    });
    $('.btnSaveTeleservicios').on('click', function (e) {
        e.preventDefault();
        loading()
        guardarSorteoTeleservicios();
        reset();
    });
    $('.btnSavePrint_sorteotls').on('click', function (e) {
        e.preventDefault();
        loading()
        regprem_validate_tls = 1;
        guardarSorteo();
        reset();
    });

    $('body').on('click', '.printOnlySignature', function () {
        var id = $(this).attr('data-id-paper');
        var tipoDoc = $(this).attr('data-tipodoc');
        printOnlySignature(id, tipoDoc);
    });


    $('body').on('click', '.printOnly', function () {
        var id = $(this).attr('data-id-paper');
        var tipoDoc = $(this).attr('data-tipodoc');
        printOnly(id, tipoDoc);
    });


    $('.cerrrar').on('click', function () {
        location.reload();
    });

    $('html').bind('keypress', function (e) {
        let inputClienteDni = $('#txtDniCliente');
        let inputClienteCE = $('#Nro_doc');
        let inputClientePS = $('#Nro_doc_Ps');

        if (e.keyCode == 13) {
            var focused = $(':focus');
            var index = focused.index("input");
            if (index == -1) {
                index = focused.index("button");
                index = index + 'B';
            }
            let isDraw = $('input[name="selectTipo"]:checked').val() === "3";
            if (inputClienteDni.is(":focus")) {
                $("#btnBuscarTicket,#txtNroTicket").removeAttr("disabled");
                if (isDraw) {
                    consultarSorteo(inputClienteDni.val(), 0);
                } else {
                    consultaDni();
                }
            }
            if (inputClienteCE.is(":focus")) {
                let nro = $('.search_Ce').find('input[name="Nro_doc"]').val();
                if (isDraw) {
                    consultarSorteo(nro, 1);
                } else {
                    consultaEx(nro, 1);
                }
            }
            if (inputClientePS.is(":focus")) {
                let nro = $('.search_Ps').find('input[name="Nro_doc"]').val();
                if (isDraw) {
                    consultarSorteo(nro, 2);
                } else {
                    consultaEx(nro, 2);
                }
            }
            if ($("#txtNroTicket").is(":focus")) {
                buscarTicket();
            }
            if ($("#btnSavePrint").is(":focus")) {
                guardar();
            }
            /*if (index === '9B') {
                printPdf();
            }*/

            e.preventDefault();
        }
    });

    $('#btnBuscarTicket').on('click', function (e) {
        e.preventDefault();
        buscarTicket();
    });

    $('#btnBuscarTicketTeleservicios').on('click', function (e) {
        e.preventDefault();
        buscarTicket();
    });

    $('.selectTipo').on('change', function () {
        regprem_validate_tls = 0;
        $(".btnSaveTeleservicios").hide();
        $(".btnSavePrint_sorteotls").hide();
        $(".btnSavePrint").show();
        // console.log($('input[name="selectTipo"]:checked').val());
        if ($('input[name="selectTipo"]:checked').val() === "3") {
            $('input[name="tipoDoc"][value="PS"]').parent().parent().show();
            $(".hidden_content").hide();

            $(".content_data_sorteo").show();
            $("#btnSavePrint").html("Generar Pago - Imprimir <i class=\"fa fa-print\" aria-hidden=\"true\"></i>");
            
            $('.btnSavePrint').attr('disabled', false);

            $(".btnSavePrint_sorteotls").show();
            $('.btnSavePrint_sorteotls').attr('disabled', false);
        } else if ($('input[name="selectTipo"]:checked').val() === "7") {
            $('input[name="tipoDoc"][value="PS"]').parent().parent().hide();
            $(".hidden_content").hide();
            $(".content_data_sorteo").show();

            $(".btnSaveTeleservicios").show();
            $(".btnSavePrint").hide();
            $("#btnSaveTeleservicios").html("Generar <i class=\"fa fa-print\" aria-hidden=\"true\"></i>");
            $('.btnSavePrint').attr('disabled', false);
        } else {
            $('input[name="tipoDoc"][value="PS"]').parent().parent().show();
            $(".hidden_content").show();
            $(".content_data_sorteo").hide();
            $("#btnSavePrint").html("Guardar - Imprimir <i class=\"fa fa-print\" aria-hidden=\"true\"></i>");
        }
        if ($('input[name="selectTipo"]:checked').val() === "6") {
            $('.tabla_jackpot .tablaRegJackpot td:nth-child(2),th:nth-child(2)').hide();
            $("#txtNroTicket").attr("placeholder","NRO DE TRANSACCIÓN");
            $(".tabla_jackpot .tablaRegJackpot thead tr td:first").text("NRO DE TRANSACCIÓN");
        } else{
            $('.tabla_jackpot .tablaRegJackpot td:nth-child(2),th:nth-child(2)').show();
            $("#txtNroTicket").attr("placeholder","NRO DE TICKET");
            $(".tabla_jackpot .tablaRegJackpot thead tr td:first").text("NRO DE TICKET");

        }

        clean();
        reset();
    });

    $('body').on('click', '.addFoto', function () {
        // deleteImg();
        let data = $(this).data('id-jackpot');
        let cantidad = $(this).data('cant');
        let tipo = $(this).data('type');

        cargaImg(data, tipo);
        $('#showQR').attr('data-id', data);
        $('#showQR').attr('data-cantidad', cantidad);
        $('#id-Jackpot').val(data);
        $('#id-Jackpot').parent().attr("data-type", tipo);
        var fadein = $(this).hasClass('in');
        if (fadein == false) {
            $('#fotoModal').modal('show');

        } else {

            $('#fotoModal').modal('hide');
            $('#qrcode').html(" ");
            $('#miniatura').html(" ");
        }
    });

    $('#imgInp').on('click', function () {
        deleteImg();
        let id = $('#id-Jackpot').val();
        let type = $("#formUpload").attr("data-type");
        cargaImg(id, type);
    });

    $('#imgInp').on('change', function (e) {
        var files = e.target.files;
        var filesLength = files.length;
        // deleteImg();
        let conteo = $(this).data('cant').replace('{count}', filesLength);
        $('#leyenda').html('');
        $('#leyenda').html(conteo);
        $('.uploadInput').attr('disabled', false);

        if (filesLength > 0) {
            $('.uploadInput').addClass('activate');
        } else {
            $('.uploadInput').addClass('desactivate');
        }

        if (filesLength <= 4) {

            for (var i = 0; i < filesLength; i++) {
                var f = files[i];
                var fileReader = new FileReader();
                fileReader.onload = (function (e) {
                    var file = e.target;
                    $('#previewImg').attr('src', e.target.result);

                    $("<img></img>", {
                        class: "mini",
                        src: e.target.result,
                        title: file.name
                    }).insertAfter("#minions");

                });
                fileReader.readAsDataURL(f);
            }
            $('.uploadInput').trigger('click');
        }
    });

    $('body').on('click', '.mini', function () {
        var src = $(this).attr('src');
        src = src.replace('min_', '');
        $('#previewImg').attr('src', src);
    });

    $("#showQR").on('click', function (e) {
        let io = $(this);
        io.parent().find('.showQR').toggle();
        io.parent().find('.formUpload').toggle();
        $('#qrcode').html('');
        let cant = $(this).attr('data-cantidad');
        let id = $(this).data('id');
        let type = $("#formUpload").attr("data-type");

        setInterval(function () {
            if (io.parent().find('.showQR').css("display") == "block") {
                verifica(id, cant, type);
            }
        }, 5000);

        let token = $('#showQR').attr('data-token');

        let locationURL = window.location.protocol + '//' + window.location.hostname;
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: locationURL + "/external/registro_foto_jackpot/?id=" + id + "&tkn=" + token + "&type=" + type,
            width: 128,
            height: 128,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });

    $('.close').on('click', function (e) {
        $('#fotoModal').toggle();
        deleteImg();
        $('#resette').trigger('click');
        $('.showQR').css('display', 'none');
        $('.formUpload').css('display', 'block');
        //$(this).parent().parent().find('.showQR').toggle(false);
        $('.uploadInput').removeClass('desactivate');
        $('.uploadInput').removeClass('activate');
    });

    $("#formUpload").submit(function (e) {
        e.preventDefault();

        if ($('#imgInp').val() != "" && $('#imgInp').val() != " ") {

            let urlget = "sys/set_registro_fotos_premios.php";
            let photoType = $("#formUpload").attr("data-type");
            var dataForm = new FormData(this);

            dataForm.append("sec_registro_fotos_jackpot", "sec_registro_fotos_jackpot");
            dataForm.append("photoType", photoType);
            loading(true);
            $.ajax({
                url: urlget,
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    swal({
                        title: "Registro Exitoso",
                        text: "",
                        type: "success",
                        timer: 500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: false
                    });
                    $('#fotoModal').modal("hide");
                    loading(false);
                    deleteImg();
                    window.setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            });
        }
    });

    $('#paidLocal').on('change', function(){
        actualizar_tbl()
    })

    $('#imprimir').on('click', function () {
        printPdf();
        $('#ticketModal').modal('hide');
    });

    $('input[name="tipoDoc"]').on('change', function () {
        var valor = $('input[name="tipoDoc"]:checked').val();

        switch (valor) {
            case 'DNI':
                $('.search_dni').toggle(true);
                $('#txtDniCliente').focus();
                $('.search_Ce').toggle(false);
                $('.search_Ps').toggle(false);
                actualizar_tbl();
                $('#Nro_doc').focus();
                clean();
                break;
            case 'CE_PTP':
                $('.search_Ce').toggle(true);
                $('#Nro_doc').focus();
                $('.search_dni').toggle(false);
                $('.search_Ps').toggle(false);
                actualizar_tbl();
                $('#Nro_doc').focus();
                clean();
                break;
            case 'PS':
                $('.search_Ps').toggle(true);
                $('#Nro_doc_Ps').focus();
                $('.search_Ce').toggle(false);
                $('.search_dni').toggle(false);
                actualizar_tbl();
                clean();
                break;
            default:
                $('.search_dni').toggle(true);
                $('.search_Ce').toggle(false);
                $('.search_Ps').toggle(false);
                actualizar_tbl();
                $('#Nro_doc').focus();
                clean();
        }

    });

    $('.consultar').on('click', function () {
        var focused = $(':focus');
        var index = focused.index("input")
        let valor = $('.section_type_doc').find('input[name="tipoDoc"]:checked').val();
        let tipoDoc = $("input[name=tipoDoc]:checked").data("val");
        let numDoc = $('#txtDniCliente').val();
        $("#btnBuscarTicket,#txtNroTicket").removeAttr("disabled");

        if ($('input[name="selectTipo"]:checked').val() === "3") {
            consultarSorteo(getDoc(), tipoDoc);
            return;
        } else if ($('input[name="selectTipo"]:checked').val() === "7") {
            consultarSorteoTeleservicios(getDoc(), tipoDoc);
            return;
        } else {
            consultarDatos(numDoc, tipoDoc);
        }

        if (valor == "CE_PTP") {
            nro = $('.search_Ce').find('input[name="Nro_doc"]').val();
            consultaEx(nro, 1);
            tipoDoc = 1;
            numDoc = nro;
        } else if (valor == "PS") {
            nro = $('.search_Ps').find('input[name="Nro_doc"]').val();
            consultaEx(nro, 2);
            tipoDoc = 2;
            numDoc = nro;
        } else {
            consultaDni();
        }
    });

    function genQrFirma(id, lastID) {
        let locationURL = window.location.protocol + '//' + window.location.hostname;
        let token = $('#tokenSesion').val();
        var qrcode = new QRCode(document.getElementById("qrcodeFirma"), {
            text: locationURL + "/external/registro_firma/?id=" + id + "&tkn=" + token + "&lastid=" + lastID,
            width: 128,
            height: 128,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        $('#firmaModal').modal('show');
        $('#firmaModal').addClass('show');

        setInterval(function () {
            if ($('#firmaModal').hasClass(
                'in')) {
                console.log('verifiacando firma');
                //console.log(id);
                verificaFirma(lastID);
            } else {
                location.reload();
            }
        }, 2000);
    };


    $('body').on('click', '.btnSignature', function () {
        let idRegistro = $(this).attr('data-id-paper');
        let numDoc = $(this).attr('data-num-doc');

        genQrFirma(numDoc, idRegistro);

    });

    $( "#premios_table" ).on( "change", 'input[name="drawRow"]', function() {
        if ( $('input[name="selectTipo"]:checked').val() !== "8" ){
            $('#prize_amount_show').html("S/. " + $(this).data("prize"))

            if ($.trim($(this).data("value")) != "") {
                $("#prize_amount_text").html(' ' + $(this).data("type"));
            } else {
                $("#prize_amount_text").html(' en efectivo');
            }
            
            $(".btnSavePrint_sorteotls").hide();
            $("#premio_tipo_codigo").val($(this).data("value"));
        } else {
            $("#prize_amount_text").html('');
        }
    });

    function buscarTicket() {
        var tipoReg = $('.selectTipo:checked').val();
        if (tipoReg >= 0) {
            switch (tipoReg) {
                case '0':
                case '4':
                    buscarTicketJackpot();
                    break;
                case '1':
                    buscarTicketBingo();
                    break;
                case '2':
                    buscarTicket_Mayor();
                    break;
                case '6':
                    buscarTicketTorito();
                    break;
                case '7':
                    buscarTicketTelservicios();
                    break;
                default:
            }
        } else {
            swal({
                title: "Debe seleccionar un tipo de registro",
                type: "warning",
                closeOnConfirm: true,
                showCancelButton: false,
                showConfirmButton: true
            });
        }

    };

    function buscarTicketJackpot() {
        var valor = $('#txtNroTicket').val();
        if (valor == "") {
            swal({
                title: "Ingrese Nro de Ticket!",
                type: "warning",
                timer: 4500,
                closeOnConfirm: false,
                showCancelButton: false,
                showConfirmButton: true
            });
        } else {
            datas = {};
            datas.idTicket = valor;
            loading(true);
            $.post('/sys/get_registro_premios.php', {"search_ticket_jackpot": datas}, function (data) {
                loading();
                console.log(data);
                let result = JSON.parse(data);

                if (result.code === '210') {
                    var obj = result.message;
                    $('#txtNroTicket').val(obj['ticket_id']);
                    $('#txtMontoApostado').val(obj['stake_amount']);
                    $('#txtMonto').val(obj['jackpot']);
                    $('#cbLocales').attr("data-idlocal", obj['local_id']);
                    $('#cbLocales').val('[' + obj["cc_id"] + '] ' + obj["nombre"]);
                    $('.btnSaves').focus();
                } else {

                    swal({
                        title: "Alerta",
                        text: result.message,
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });

                    $('#txtNroTicket').val('');
                    $('#txtMontoApostado').val('');
                    $('#txtMonto').val('');
                    $('#cbLocales').attr("data-idlocal", '');
                    $('#cbLocales').val('');
                }
            });
        }
    };
    function buscarTicketTorito(){
        var valor = $('#txtNroTicket').val();
        if (valor == "") {
            swal({
                title: "Ingrese Nro de Ticket!",
                type: "warning",
                timer: 4500,
                closeOnConfirm: false,
                showCancelButton: false,
                showConfirmButton: true
            });
        } else {
            datas = {};
            datas.idTicket = valor;
            loading(true);
            $.post('/sys/get_registro_premios.php', {"search_ticket_torito": datas}, function (data) {
                loading();
                let result = JSON.parse(data);

                if (result.code === '210') {
                    var obj = result.message;
                    $('#txtNroTicket').val(obj['ticket_id']);
                    $('#txtMontoApostado').val("0.00");
                    $('#txtMonto').val(obj['amount']);
                    $('#cbLocales').attr("data-idlocal", obj['local_id']);
                    $('#cbLocales').val('[' + obj["cc_id"] + '] ' + obj["nombre"]);
                    $('.btnSaves').focus();
                } else {

                    swal({
                        title: "Alerta",
                        text: result.message,
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });

                    $('#txtNroTicket').val('');
                    $('#txtMontoApostado').val('');
                    $('#txtMonto').val('');
                    $('#cbLocales').attr("data-idlocal", '');
                    $('#cbLocales').val('');
                }
            });
        }
    };
    function buscarTicketBingo() {
        var valor = $('#txtNroTicket').val();
        if (valor == "") {
            swal({
                title: "Ingrese Nro de Ticket!",
                type: "warning",
                timer: 4500,
                closeOnConfirm: false,
                showCancelButton: false,
                showConfirmButton: true
            });
        } else {
            datas = {};
            datas.idTicket = valor;
            loading(true);
            $.post('/sys/get_registro_premios.php', {"search_ticket_bingo": datas}, function (data) {
                loading();
                let result = JSON.parse(data);

                if (result.code === '210') {
                    var obj = result.message;
                    $('#txtNroTicket').val(obj['ticket_id']);
                    $('#txtMontoApostado').val(obj['amount']);
                    $('#txtMonto').val(obj['winning']);
                    $('#cbLocales').attr("data-idlocal", obj['local_id']);
                    $('#cbLocales').val('[' + obj["cc_id"] + '] ' + obj["nombre"]);
                    $('.btnSaves').focus();
                } else {

                    swal({
                        title: "Alerta",
                        text: result.message,
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });

                    $('#txtNroTicket').val('');
                    $('#txtMontoApostado').val('');
                    $('#txtMonto').val('');
                    $('#cbLocales').attr("data-idlocal", '');
                    $('#cbLocales').val('');
                }
            });
        }
    };

    function buscarTicket_Mayor() {
        var valor = $('#txtNroTicket').val();
        if (valor == "") {
            swal({
                title: "Ingrese Nro de Ticket!",
                type: "warning",
                timer: 4500,
                closeOnConfirm: false,
                showCancelButton: false,
                showConfirmButton: true
            });
        } else {
            datas = {};
            datas.idTicket = valor;
            loading(true);
            $.post('/sys/get_registro_premios.php', {"search_ticket_mayor": datas}, function (data) {
                loading();
                let result = JSON.parse(data);

                if (result.code === '210') {
                    var obj = result.message;
                    $('#txtNroTicket').val(obj['ticket_id']);
                    $('#txtMontoApostado').val(obj['amount']);
                    $('#txtMonto').val(obj['winning']);
                    $('#cbLocales').attr("data-idlocal", obj['local_id']);
                    $('#cbLocales').val('[' + obj["cc_id"] + '] ' + obj["nombre"]);
                    $('.btnSaves').focus();
                } else {

                    swal({
                        title: "Alerta",
                        text: result.message,
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });

                    $('#txtNroTicket').val('');
                    $('#txtMontoApostado').val('');
                    $('#txtMonto').val('');
                    $('#cbLocales').attr("data-idlocal", '');
                    $('#cbLocales').val('');
                }
            });
        }
    };

    function buscarTicketTelservicios() {
        var valor = $('#txtNroTicketTeleservicios').val();
        if (valor == "") {
            swal({
                title: "Ingrese Nro de Ticket!",
                type: "warning",
                timer: 4500,
                closeOnConfirm: false,
                showCancelButton: false,
                showConfirmButton: true
            });
        } else {
            datas = {};
            datas.idTicket = valor;
            loading(true);
            $.post('/sys/get_registro_premios.php', {"search_ticket_teleservicios": datas}, function (data) {
                loading();
                console.log(data);
                let result = JSON.parse(data);

                if (result.code === '210') {
                    var obj = result.message;
                    $('#txtNroTicketTeleservicios').val(obj['ticket_id']);
                    $('#txtMontoApostadoTeleservicios').val(obj['amount']);
                    $('#txtMontoTeleservicios').val(obj['jackpot']);
                    $('#cbLocalesTeleservicios').attr("data-idlocal", obj['local_id']);
                    $('#cbLocalesTeleservicios').val('[' + obj["cc_id"] + '] ' + obj["local_name"]);
                    $('.btnSavesTeleservicios').focus();
                } else {
                    swal({
                        title: "Alerta",
                        text: result.message,
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });

                    $('#txtNroTicketTeleservicios').val('');
                    $('#txtMontoApostadoTeleservicios').val('');
                    $('#txtMontoTeleservicios').val('');
                    $('#cbLocalesTeleservicios').attr("data-idlocal", '');
                    $('#cbLocalesTeleservicios').val('');
                }
            });
        }
    };

    function get_Date() {
        Number.prototype.padLeft = function (base, chr) {
            var len = (String(base || 10).length - String(this).length) + 1;
            return len > 0 ? new Array(len).join(chr || '0') + this : this;
        }

        var d = new Date,
            dformat = [d.getFullYear().padLeft(),
                    (d.getMonth() + 1),
                    d.getDate().padLeft()].join('-') +
                ' ' +
                [d.getHours().padLeft(),
                    d.getMinutes().padLeft(),
                    d.getSeconds().padLeft()].join(':');
        return dformat;
    };

    function actualizar_tbl() {
        loading(true);
        let datas = {};
        datas.update = true;
        datas.isCashier = $('#paidLocal').data('isCashier')
        datas.localId = $('#paidLocal').val()
        $.post('/sys/get_registro_premios.php', {"listado_tickets_jackpot": datas}, function (data) {
            loading();
            $('.actTableBody').html('');
            let result = JSON.parse(data);
            if (result.code == "001") {
                $('.actTableBody').html(result.message);
            } else {
                console.log(result.error);
            }
        });
    };

    function guardar(print) {
        var tipo_doc = $('.section_type_doc').find('input[name="tipoDoc"]:checked').val();
        var tipo_premio = $('.selectTipo:checked').val();
        var redes = $('#checkAut').is(':checked') ? 1 : 0;
        let paidLocalId = $("#paidLocal").val();
        let paidLocalName = $("#paidLocal option:selected").text();
        if (redes == 0) {
            $('#autorizacion').toggle(false);
        }

        if (!guardarSorteo()) return;

        let phone = $('#clientPhone').val();
        let email = $('#clientEmail').val();
        let clientProfession = $('#clientProfession').val();
        let isEmailValid = /\S+@\S+\.\S+/.test(email);
        let isPhoneValid = /^\d*\.?\d*$/.test(phone);
        let client_data = {
            phone: phone,
            email: email,
            clientProfession: clientProfession
        }

        if ($('input[name="selectTipo"]:checked').val() === "2") {
            if (clientProfession == "") {
                swal({
                    title: "Falta profesión",
                    text: "El ganador debe consignar su profesión para premios mayores a S/. 36 000",
                    type: "warning",
                    showCancelButton: false,
                });
                return;
            }
        }

        if (!(isEmailValid && isPhoneValid) && phone !== '' && email !== '') {
            if (!isEmailValid) {
                swal({
                    title: "Datos inválidos",
                    text: "Correo electrónico no válido",
                    type: "error",
                    showCancelButton: false,
                });
            }
            if (!isPhoneValid) {
                swal({
                    title: "Datos inválidos",
                    text: "Teléfono no válido, ingrese solo números",
                    type: "error",
                    showCancelButton: false,
                });
            }

        } else {
            if (tipo_doc == 'DNI') {
                var local, dni, ticket, monto, user;
                var tdni = $('#tdni').val() != null ? 1 : 0;

                local = $('#cbLocales').attr('data-idLocal');
                nmbLocal = $('#cbLocales').val();
                fecha = get_Date();
                dni = $('#txtDniCliente').val();
                ticket = $('#txtNroTicket').val();
                montoApostado = $('#txtMontoApostado').val();
                monto = $('#txtMonto').val();
                user = $('#user').val();
                nombre = $('#tnombres').text();
                apellido = $('#tapepat').text() + ' ' + $('#tapemat').text();
                let isAuthChecked = $('#checkAut').is(':checked') ? 1 : 0 || $('#checkAutNo').is(':checked') ? 1 : 0;

                client_data.num_doc = dni;
                client_data.tipo_doc = 0;

                if (local == "" || local == 0) {
                    swal({
                        title: "Local no encontrado",
                        text: "Abra una caja para poder registrar premio",
                        type: "warning",
                        showCancelButton: false,
                    });
                    return;
                }

                guardarDatosCliente(client_data).then(function () {
                    if ((tdni != 0) && (local != '00') && (dni != '') && (ticket != '') && (monto != '') && isAuthChecked) {
                        var datas = {};
                        datas.local_id = local;
                        datas.nmbLocal = nmbLocal;
                        datas.created_at = fecha
                        datas.tipo_doc = 0;
                        datas.num_doc = dni;
                        datas.tipo_registro = tipo_premio;
                        datas.ticket_id = ticket;
                        datas.autoriza = redes;
                        datas.monto_apostado = montoApostado;
                        datas.monto_entregado = monto;
                        datas.session_cookie = user;
                        datas.paid_local_id = paidLocalId;
                        datas.paidLocalName = paidLocalName;
                        datas.clientProfession = clientProfession;
                        datas.nombre = nombre;
                        datas.apellido = apellido;
                        datas.local = local;
                        loading(true);
                        $.post('/sys/get_registro_premios.php', {"reg_ticket_jackpot": datas}, function (data) {
                            loading();
                            let last = JSON.parse(data);
                            swal({
                                title: "Registro Exitoso",
                                text: "",
                                type: "success",
                                timer: 800,
                                closeOnConfirm: false,
                                showCancelButton: false,
                                showConfirmButton: false
                            });
                            actualizar_tbl();

                            if (print == 1) {
                                genQrFirma(datas.num_doc, last.lastID);
                            } else {
                                printOnly(last.lastID, 0);
                                setTimeout(() => {
                                    location.reload();
                                }, 3000)
                            }

                            reset();
                            clean();
                        });

                    } else swal("Error!", "No se permiten campos vacios.", "warning");
                });
            }

            if (tipo_doc == 'CE_PTP') {
                let local = $('#cbLocales').attr('data-idLocal');
                let nmbLocal = $('#cbLocales').val();
                let nro = $('#CE_Nro').val();
                let nom = $('#CE_Nombres').val();
                let apePat = $('#CE_ApePat').val();
                let apeMat = $('#CE_ApeMat').val();
                let apellidos = $('#CE_ApePat').val() + ' ' + $('#CE_ApeMat').val();
                let ticket = $('#txtNroTicket').val();
                montoApostado = $('#txtMontoApostado').val();
                let monto = $('#txtMonto').val();
                let user = $('#user').val();
                let fecha = get_Date();

                client_data.num_doc = nro;
                client_data.tipo_doc = 1;
                if (local == "" || local == 0) {
                    swal({
                        title: "Local no encontrado",
                        text: "Abra una caja para poder registrar premio",
                        type: "atencion",
                        showCancelButton: false,
                    });
                    return;
                }
                guardarDatosCliente(client_data).then(function () {
                    if ((tdni != 0) && (local != '00') && (dni != '') && (ticket != '') && (monto != '')) {
                        var datas = {};
                        datas.tipo_doc = 1;
                        datas.local_id = local;
                        datas.created_at = fecha;
                        datas.num_doc = nro;
                        datas.nombres = nom;
                        datas.apePat = apePat;
                        datas.apeMat = apeMat;
                        datas.ticket_id = ticket;
                        datas.autoriza = redes;
                        datas.tipo_registro = tipo_premio;
                        datas.monto_apostado = montoApostado;
                        datas.monto_entregado = monto;
                        datas.session_cookie = user;
                        datas.paid_local_id = paidLocalId;

                        loading(true);
                        $.post('/sys/get_registro_premios.php', {"reg_ticket_jackpot_ex": datas}, function (datos) {
                            loading();
                            let dato = JSON.parse(datos);
                            if (dato.code == "500") {
                                swal({
                                    title: dato.message,
                                    text: "",
                                    type: "error",
                                    closeOnConfirm: true
                                });
                            } else {

                                swal({
                                    title: "Registro Exitoso",
                                    text: "",
                                    type: "success",
                                    timer: 1500,
                                    closeOnConfirm: false,
                                    showCancelButton: false,
                                    showConfirmButton: false
                                });

                                actualizar_tbl();

                                if (print == 1) {
                                    genQrFirma(datas.num_doc, dato.lastID);
                                } else {
                                    printOnly(dato.lastID, 1);
                                    setTimeout(() => {
                                        location.reload();
                                    }, 3000);
                                }

                                reset();
                                clean();
                            }

                        });

                    } else swal("Error!", "No se permiten campos vacios.", "warning");
                });


            }

            if (tipo_doc == 'PS') {
                let local = $('#cbLocales').attr('data-idLocal');
                let nmbLocal = $('#cbLocales').val();

                let nro = $('#PS_Nro').val();
                let nom = $('#PS_Nombres').val();
                let apePat = $('#PS_ApePat').val();
                let apeMat = $('#PS_ApeMat').val();
                let apellidos = $('#PS_ApePat').val() + ' ' + $('#PS_ApeMat').val();
                let ticket = $('#txtNroTicket').val();
                let montoApostado = $('#txtMontoApostado').val();
                let monto = $('#txtMonto').val();
                let user = $('#user').val();
                let fecha = get_Date();

                client_data.num_doc = nro;
                client_data.tipo_doc = 1;

                if (local == "" || local == 0) {
                    swal({
                        title: "Local no encontrado",
                        text: "Abra una caja para poder registrar premio",
                        type: "atencion",
                        showCancelButton: false,
                    });
                    return;
                }

                guardarDatosCliente(client_data).then(function () {
                    if ((tdni != 0) && (local != '00') && (dni != '') && (ticket != '') && (monto != '')) {
                        var datas = {};
                        datas.tipo_doc = 2;
                        datas.local_id = local;
                        datas.created_at = fecha;
                        datas.num_doc = nro;
                        datas.nombres = nom;
                        datas.apePat = apePat;
                        datas.apeMat = apeMat;
                        datas.ticket_id = ticket;
                        datas.autoriza = redes;
                        datas.monto_apostado = montoApostado;
                        datas.monto_entregado = monto;
                        datas.tipo_registro = tipo_premio;
                        datas.session_cookie = user;
                        datas.paid_local_id = paidLocalId;

                        loading(true);
                        $.post('/sys/get_registro_premios.php', {"reg_ticket_jackpot_ex": datas}, function (data) {
                            loading();
                            let dato = JSON.parse(data);
                            swal({
                                title: "Registro Exitoso",
                                text: "",
                                type: "success",
                                timer: 1500,
                                closeOnConfirm: false,
                                showCancelButton: false,
                                showConfirmButton: false
                            });
                            actualizar_tbl();

                            if (print == 1) {
                                genQrFirma(datas.num_doc, dato.lastID);
                            } else {
                                printOnly(dato.lastID, 2);
                                setTimeout(() => {
                                    location.reload();
                                }, 3000);
                            }

                            reset();
                            clean();
                        });
                    } else swal("Error!", "No se permiten campos vacios.", "warning");
                });
            }
        }
    }

    const guardarDatosCliente = (data) => {
        return new Promise((resolve, reject) => {
            $.post('sys/get_registro_premios.php', {"reg_client_data": data}, function (r) {
                let response = JSON.parse(r);
                if (response.code === "201") {
                    console.log("datos guardados")
                } else if (response.code === "400") {
                    console.log("datos ya existían")
                } else {
                    console.log("datos no guardados")
                }
                resolve("Success");
            });
        })
    };

    function clean() {
        $('#formJackpots').find('input[type="text"]').each(function () {
            $(this).val('');
        });
        $("#btnBuscarTicket,#txtNroTicket").removeAttr("disabled");

        $('#Nro_doc').val('');
        $('#cbLocales').val("");
        $('#cbLocales').prop("data-idLocal", "");
        $('#checkAut').prop('checked', false);
        $('#checkAut').parent().removeClass('clicked');
        $("#tdni").html("DNI");
        $("#tnombres").html("NOMBRES");
        $("#tapepat").html("APELLIDO PATERNO");
        $("#tapemat").html("APELLIDO MATERNO");
        $('.btnSaves').attr('disabled', true);
        /*$('.btnSavePrint').attr('disabled', true);*/

        $('#clientEmail').attr('disabled', true);
        $('#clientPhone').attr('disabled', true);
        $('#clientProfession').attr('disabled', true);
        $('#clientEmail').val('');
        $('#clientPhone').val('');
        $('#clientProfession').val('');

        $("#winner_id").val("");
        $("#local_id").val("");
        $("#prize_amount").val("");
        $('#premios_table > tbody').html('')
        $("#prize_amount_show").html("S/. 0.00");
        $("#prize_amount_text").html('');
        $("#premio_tipo_codigo").val('');
    }

    function consultaEx(resp, tipo) {
        data = {}
        data.doc = resp;
        data.tipo = tipo;

        loading(true);
        auditoria_send({"proceso": "consultas_doc_show_doc", "data": data});
        $.post('sys/get_registro_premios.php', {"show_doc": data}, function (r) {
            response = JSON.parse(r);
            process_responseRJ(response);
            if (response.code == "201") {
                $('.btnSaves').prop('disabled', false);
                /*$('.btnSavePrint').attr('disabled', false);*/
                $('#CE_ApePat').attr('disabled', true);
                $('#CE_ApeMat').attr('disabled', true);
                $('#txtNroTicket').focus();

                let padreCheck = $('#checkAut').parent();
                if (padreCheck.hasClass('clicked')) {
                    $('.btnSaveSig').removeAttr('disabled');
                } else {
                    $('.btnSaveSig').attr('disabled', 'disabled');
                }
                /* $('.btnSaveSig').attr('disabled', false); */
            }
            if (response.code == "202") {
                $('.btnSaves').prop('disabled', false);
                /*$('.btnSavePrint').attr('disabled', false);*/
                $('#PS_ApePat').attr('disabled', true);
                $('#PS_ApeMat').attr('disabled', true);
                $('#txtNroTicket').focus();

                let padreCheck = $('#checkAut').parent();
                if (padreCheck.hasClass('clicked')) {
                    $('.btnSaveSig').removeAttr('disabled');
                } else {
                    $('.btnSaveSig').attr('disabled', 'disabled');
                }
                /* $('.btnSaveSig').attr('disabled', false); */
            }
            if (response.code == "401") {
                $('#CE_Nro').val(resp);
                $('#CE_Nombres').attr('disabled', false);
                $('#CE_ApePat').attr('disabled', false);
                $('#CE_ApeMat').attr('disabled', false);
                $('.btnSaves').attr('disabled', false);
                /*$('.btnSavePrint').attr('disabled', false);*/
                $('#CE_ApePat').val('');
                $('#CE_ApeMat').val('');
                $('#CE_Nombres').val('');
                $('#CE_Nombres').focus();

                let padreCheck = $('#checkAut').parent();
                if (padreCheck.hasClass('clicked')) {
                    $('.btnSaveSig').removeAttr('disabled');
                } else {
                    $('.btnSaveSig').attr('disabled', 'disabled');
                }
                /* $('.btnSaveSig').attr('disabled', false); */
            } else {
                $('#CE_Nombres').attr('disabled', true);
                $('#CE_ApePat').attr('disabled', true);
                $('#CE_ApeMat').attr('disabled', true);

            }
            if (response.code == "402") {
                $('#PS_Nro').val(resp);
                $('#PS_Nombres').val('');
                $('#PS_ApePat').val('');
                $('#PS_ApeMat').val('');

                $('#PS_Nombres').attr('disabled', false);
                $('#PS_ApePat').attr('disabled', false);
                $('#PS_ApeMat').attr('disabled', false);
                $('.btnSaves').attr('disabled', false);
                /*$('.btnSavePrint').attr('disabled', false);*/
                $('#PS_Nombres').focus();

                let padreCheck = $('#checkAut').parent();
                if (padreCheck.hasClass('clicked')) {
                    $('.btnSaveSig').removeAttr('disabled');
                } else {
                    $('.btnSaveSig').attr('disabled', 'disabled');
                }
                /* $('.btnSaveSig').attr('disabled', false); */
            } else {
                $('#PS_Nombres').attr('disabled', true);
                $('#PS_ApePat').attr('disabled', true);
                $('#PS_ApeMat').attr('disabled', true);

            }
            consultarDatos(resp, tipo);
            loading();
        });
    }

    function consultaDni() {
        data = {};
        data.dni = $('#txtDniCliente').val();
        loading(true);
        auditoria_send({"proceso": "consultas_dni_show_dni", "data": data});
        $.post('sys/get_registro_premios.php', {"show_dni": data}, function (r) {
            response = JSON.parse(r);
            process_responseRJ(response);
            if (response.code == "200") {
                $('.btnSaves').attr('disabled', false);
                /*$('.btnSavePrint').attr('disabled', false);*/
                $('#txtNroTicket').focus();
                let padreCheck = $('#checkAut').parent();
                if (padreCheck.hasClass('clicked')) {
                    $('.btnSaveSig').removeAttr('disabled');
                } else {
                    $('.btnSaveSig').attr('disabled', 'disabled');
                }
            }
            consultarDatos(data.dni, 0);
            loading();
        });
    }

    function consultarDatos(numDoc, tipoDoc) {
        let data = {
            numDoc: numDoc,
            tipoDoc: tipoDoc
        };

        $('#clientPhone').val("");
        $('#clientEmail').val("");
        $('#clientProfession').val("");

        auditoria_send({"proceso": "consultas_cliente_show_data", "data": data});
        $.post('sys/get_registro_premios.php', {"show_cliente_data": data}, function (r) {
            let response = JSON.parse(r);
            $('#clientEmail').attr('disabled', false);
            $('#clientPhone').attr('disabled', false);
            $('#clientProfession').attr('disabled', false);
            if (response.code === "200") {
                let a = response.message;
                a.forEach(row => {
                    if (row !== null) {
                        if (row.tipo_data === "1") {
                            $('#clientPhone').val(row.data);
                        } else if (row.tipo_data === "2") {
                            $('#clientEmail').val(row.data);
                        }else if (row.tipo_data === "4") {
                            $('#clientProfession').val(row.data);
                        }
                    }
                })
            }
        });
    }

    function consultarSorteo(numDoc, tipoDoc) {
        $('#premios_table > tbody').html('')
        $("#premio_tipo_codigo").val('');
        $("#prize_amount_text").html('');
        $("#prize_amount_show").html("S/. 0.00");

        let data = {
            numDoc: numDoc,
            tipoDoc: tipoDoc,
            domain: window.location.protocol + '//' + window.location.hostname
        };

        auditoria_send({"proceso": "consultas_get_cliente_sorteo", "data": data});
        loading(true);
        $.post('/sys/get_registro_premios.php', {"search_sorteo_winner": data}, function (r) {
            loading(false);
            let response = JSON.parse(r);
            let message = JSON.parse(response.message);
            if (response.code === "200") {
                if (!message){
                    swal({
                        title: "Error al consultar el cliente",
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });
                    return;
                }
                if (message.http_code === 404) {
                    swal({
                        title: "El cliente no está registrado en sorteos",
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });
                } else if (message.message.length > 0) {
                    swal({
                        title: message.message,
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });
                    $('.btnSavePrint').attr('disabled', true);
                    clean();
                } else if (message.event.length === 0) {
                    swal({
                        title: "El cliente no tiene un premio, ó expiró el límite de 7 días",
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });
                    $('.btnSavePrint').attr('disabled', true);
                    clean();
                } else {
                    let events = message.event
                    let totalAmount = 0.00
                    let resultTable = $("#premios_table > tbody:last-child")

                    let client = message.client
                    //$("#winner_id").val(firstRow.client.id);
                    //$("#local_id").val(firstRow.client.shop_id ?? 0);

                    let names = splitNames(client.last_name);
                    switch (tipoDoc) {
                        case 0:
                            $("#tdni").html(client.document_number);
                            $("#tnombres").html(client.name);
                            $("#tapepat").html(names.first_surname);
                            $("#tapemat").html(names.last_surname);
                            break;
                        case 1:
                            $("#CE_Nro").val(client.document_number);
                            $("#CE_Nombres").val(client.name);
                            $("#CE_ApePat").val(names.first_surname);
                            $("#CE_ApeMat").val(names.last_surname);
                            break;
                        case 2:
                            $("#PS_Nro").val(client.document_number);
                            $("#PS_Nombres").val(client.name);
                            $("#PS_ApePat").val(names.first_surname);
                            $("#PS_ApeMat").val(names.last_surname);
                            break;
                    }

                    events.forEach(event => {
                        console.log(event);
                        resultTable.append(`
                            <tr>
                                <td><input type="radio" class="drawRow" name="drawRow" value="${event.id}" data-local-id="${event.shop_id}" data-prize="${event.prize}" data-type="${event.type}" data-value="${event.value}"></td>
                                <td>${event.name_draw}</td>
                                <td>${event.day_event}</td>
                                <td>${event.prize}</td>
                                <td>${event.paid_at === null ? "Sin Cobrar" : "Pendiente"}</td>
                            </tr>
                        `)
                        totalAmount += parseFloat(event.prize)
                    })

                    //$("#prize_amount").val(totalAmount ?? 0);
                    //$("#prize_amount_show").html("S/. " + totalAmount ?? 0);
                    $('.btnSavePrint').attr('disabled', false);
                }
            }
        });
    }

    function consultarSorteoTeleservicios(numDoc, tipoDoc) {
        //console.log('AQUI TLS');
        //return false;
        $('#premios_table > tbody').html('')

        let data = {
            numDoc: numDoc,
            tipoDoc: tipoDoc,
            domain: window.location.protocol + '//' + window.location.hostname
        };

        auditoria_send({"proceso": "consultas_get_cliente_sorteo", "data": data});
        loading(true);
        $.post('/sys/get_registro_premios.php', {"search_sorteo_winner_teleservicios": data}, function (r) {
            loading(false);
            let response = JSON.parse(r);
            let message = JSON.parse(response.message);
            if (response.code === "200") {
                if (!message){
                    swal({
                        title: "Error al consultar el cliente",
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });
                    return;
                }
                if (message.http_code === 400) {
                    swal({
                        title: "El reclamo del premio Fidelidad Teleservicios tenia vigencia del 28/04/2022 hasta el 04/05/2022.",
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });
                } else if (message.http_code === 404) {
                    swal({
                        title: "El cliente no está registrado",
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });
                } else if (message.event.length === 0) {
                    swal({
                        title: "El cliente no tiene un premio",
                        type: "warning",
                        timer: 4500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    });
                    $('.btnSavePrint').attr('disabled', true);
                    clean();
                } else {
                    let events = message.event
                    let totalAmount = 0.00
                    let resultTable = $("#premios_table > tbody:last-child")

                    let client = message.client;
                    //$("#winner_id").val(events);
                    //$("#local_id").val(firstRow.client.shop_id ?? 0);

                    $("#regpre_tipo_doc").val(tipoDoc);
                    $("#regpre_num_doc").val(numDoc);

                    let names = splitNames(client.last_name);
                    switch (tipoDoc) {
                        case 0:
                            $("#tdni").html(client.document_number);
                            $("#tnombres").html(client.name);
                            $("#tapepat").html(names.first_surname);
                            $("#tapemat").html(names.last_surname);
                            break;
                        case 1:
                            $("#CE_Nro").val(client.document_number);
                            $("#CE_Nombres").val(client.name);
                            $("#CE_ApePat").val(names.first_surname);
                            $("#CE_ApeMat").val(names.last_surname);
                            break;
                        case 2:
                            $("#PS_Nro").val(client.document_number);
                            $("#PS_Nombres").val(client.name);
                            $("#PS_ApePat").val(names.first_surname);
                            $("#PS_ApeMat").val(names.last_surname);
                            break;
                    }

                    events.forEach(event => {
                        //console.log("winner_id: "+event.id);
                        $("#winner_id").val(event.id);
                        //console.log(event);
                        resultTable.append(`
                            <tr>
                                <td><input type="radio" class="drawRow" name="drawRow" value="${event.id}" data-local-id="${event.shop_id}" data-prize="${event.amount}"></td>
                                <td>${event.name_draw}</td>
                                <td>${event.day_event}</td>
                                <td>${event.amount}</td>
                                <td>${event.paid_at === null ? "Sin Cobrar" : "Pendiente"}</td>
                            </tr>
                        `)
                        totalAmount += parseFloat(event.amount)
                    })

                    $("#prize_amount").val(totalAmount ?? 0);
                    //$("#prize_amount_show").html("S/. " + totalAmount ?? 0);
                    $('.btnSavePrint').attr('disabled', false);
                }
            }
        });
    }

    function guardarSorteo() {
        if ($('input[name="selectTipo"]:checked').val() === "3") {
            let selectedRow = $('input[name="drawRow"]:checked')
            /*let winnerId = $("#winner_id").val();
            let localId = $("#local_id").val() ?? "0";*/
            let tipoDoc = $("input[name=tipoDoc]:checked").data("val");
            //let prizeAmount = $("#prize_amount").val();
            let prizeAmount = selectedRow.data("prize");
            let clientData = getClientData(tipoDoc);
            let paidLocalId = $("#paidLocal").val();
            let winnerId = selectedRow.val()
            let localId = selectedRow.data('localId')
            let premio_tipo_codigo = $("#premio_tipo_codigo").val();

            if (localId === "" || localId === undefined || winnerId === "" || winnerId === undefined) {
                swal({
                    title: "Complete todo los campos",
                    text: "",
                    type: "warning",
                    timer: 800,
                    closeOnConfirm: false,
                    showCancelButton: false,
                    showConfirmButton: false
                });
                return false;
            }
            let data = {
                created_at: get_Date(),
                regprem_validate_tls : regprem_validate_tls,
                local_id: localId,
                tipo_doc: tipoDoc,
                num_doc: getDoc(),
                ticket_id: winnerId,
                autoriza: 1,
                monto_apostado: 0,
                monto_entregado: prizeAmount ?? 0,
                tipo_registro: $('.selectTipo:checked').val(),
                session_cookie: $('#user').val(),
                domain: window.location.protocol + '//' + window.location.hostname,
                nombres: clientData.nombres,
                apellido_paterno: clientData.apellido_paterno,
                apellido_materno: clientData.apellido_materno,
                paid_local_id: paidLocalId,
                premio_tipo_codigo: premio_tipo_codigo
            }
            $.post('sys/get_registro_premios.php', {"reg_ticket_sorteo": data}, function (r) {
                loading();
                let response = JSON.parse(r);
                if (response.result) {
                    $.post('sys/get_registro_premios.php', {"send_registration": data}, function (r) {
                        let response = JSON.parse(r)
                        if(parseInt(response.http_code) == 400){
                            swal({
                                title: response.status,
                                type: "warning",
                                timer: 2000,
                                closeOnConfirm: true,
                                showCancelButton: false,
                                showConfirmButton: true
                            });
                            return false;
                        }else{
                            swal({
                                title: "Registro Exitoso",
                                text: "",
                                type: "success",
                                timer: 800,
                                closeOnConfirm: false,
                                showCancelButton: false,
                                showConfirmButton: false
                            });
                        }
                    });
                }else{
                    if(response.code == 400){
                        swal({
                            title: response.message,
                            text: "",
                            type: "warning",
                            timer: 800,
                            closeOnConfirm: false,
                            showCancelButton: false,
                            showConfirmButton: false
                        });
                        return;
                    }
                    swal({
                        title: "Registro Exitoso",
                        text: "",
                        type: "success",
                        timer: 800,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: false
                    });
                }
            }).always(function (r) {
                let last = JSON.parse(r);
                printOnly(last.lastID, tipoDoc);
                setTimeout(() => {
                    location.reload();
                }, 3000)

                reset();
                clean();
            });
            return false;
        } else {
            return true;
        }
    }

    function guardarSorteoTeleservicios() {
        if ($('input[name="selectTipo"]:checked').val() === "7") {
            let winner_id = $("#winner_id").val();
            let nro_ticket = $('#txtNroTicketTeleservicios').val();
            let monto_ticket = $('#txtMontoApostadoTeleservicios').val();
            let idlocal_ticket = $('#cbLocalesTeleservicios').attr("data-idlocal");
            let tipo_doc = $("#regpre_tipo_doc").val();
            let num_doc = $("#regpre_num_doc").val();

            let prize_amount = $("#prize_amount").val();

            //console.log("prize_amount: "+prize_amount);
            //console.log("monto_ticket: "+monto_ticket);

            if (nro_ticket === "" || nro_ticket === undefined || winner_id === "" || winner_id === undefined) {
                swal({
                    title: "Complete todo los campos",
                    text: "",
                    type: "warning",
                    timer: 800,
                    closeOnConfirm: false,
                    showCancelButton: false,
                    showConfirmButton: false
                });
                return false;
            }
            if (parseFloat(monto_ticket) !== parseFloat(prize_amount)) {
                swal({
                    title: "Monto de de FreeBet obtenido debe ser igual al monto del Ticket.",
                    text: "",
                    type: "warning",
                    timer: 5000,
                    closeOnConfirm: false,
                    showCancelButton: false,
                    showConfirmButton: false
                });
                return false;
            }
            let data = {
                ticket_id: nro_ticket,
                winner_id: winner_id,
                monto_ticket: monto_ticket,
                idlocal_ticket: idlocal_ticket,
                tipo_doc: tipo_doc,
                num_doc: num_doc,
                domain: window.location.protocol + '//' + window.location.hostname,
                session_cookie: $('#user').val()
            }
            /*
            console.log(data);
            return false;
            */
            $.post('sys/get_registro_premios.php', {"send_registration_teleservicios": data}, 
                function (r) {
                loading();
                let response = JSON.parse(r)
                swal({
                    title: "Registro Exitoso",
                    text: "",
                    type: "success",
                    timer: 5000,
                    closeOnConfirm: false,
                    showCancelButton: false,
                    showConfirmButton: false
                });
            }).always(function (r) {
                let last = JSON.parse(r);
                printOnly(last.lastID, tipo_doc);
                setTimeout(() => {
                    location.reload();
                }, 5000);
                reset();
                clean();
            });
            return false;
        } else {
            return true;
        }
    }

    function process_responseRJ(response) {

        if (response.code == "200") {
            let data = response.message;
            if (data['dni'] != undefined) {
                let html = '<tr>';
                html += '<td id="tdni">' + data['dni'] + '</td>';
                html += '<td id="tnombres">' + data['nombres'] + '</td>';
                html += '<td id="tapepat">' + data['apellido_paterno'] + '</td>';
                html += '<td id="tapemat">' + data['apellido_materno'] + '</td>';
                '</tr>';
                $('#bodyDni').html(html);
            } else {
                clean()
                swal({
                    title: "Alerta",
                    text: response.message,
                    type: "warning",
                    timer: 1500,
                    closeOnConfirm: false,
                    showCancelButton: false,
                    showConfirmButton: false
                });
            }
        } else if (response.code == "201") {
            let data = response.message;
            if (data['num_doc'] != undefined) {
                $('#CE_Nro').val(data['num_doc']);
                $('#CE_Nombres').val(data['nombres']);
                $('#CE_ApePat').val(data['apellido_paterno']);
                $('#CE_ApeMat').val(data['apellido_materno']);
            }
        } else if (response.code == "202") {
            let data = response.message;
            if (data['num_doc'] != undefined) {
                $('#PS_Nro').val(data['num_doc']);
                $('#PS_Nombres').val(data['nombres']);
                $('#PS_ApePat').val(data['apellido_paterno']);
                $('#PS_ApeMat').val(data['apellido_materno']);
            }
        } else if (response.code == "401") {
            clean()
            swal({
                title: "Alerta",
                text: response.message,
                type: "warning",
                timer: 1500,
                closeOnConfirm: false,
                showCancelButton: false,
                showConfirmButton: false
            });
        } else if (response.code == "402") {
            clean()
            swal({
                title: "Alerta",
                text: response.message,
                type: "warning",
                timer: 1500,
                closeOnConfirm: false,
                showCancelButton: false,
                showConfirmButton: false
            });
        }
        else if (response.code == "405") {
            clean();
            swal({
                title: "Alerta",
                text: response.message,
                type: "warning",
                timer: 2500,
                closeOnConfirm: true,
                showCancelButton: false,
                showConfirmButton: true
            });
            $("#btnBuscarTicket,#txtNroTicket").attr("disabled","disabled");
            return;
        }
        else {
            clean()
            swal({
                title: "Alerta",
                text: response.message,
                type: "warning",
                timer: 1500,
                closeOnConfirm: false,
                showCancelButton: false,
                showConfirmButton: false
            });
        }
    }

    /* FUNCTION ANTIGUA E INUTILIZADA PARA IMPRIMIR TICKET DESDE MODAL */
    /*
        function printPdf(){

                    var obj = {};
                    var redes = $('#checkAut').is(':checked')?1:0;
                    console.log('redes: '+redes);
                    var nro = $('#mNroTicket').html();


                    obj.nroTicket = nro;
                    obj.fecha = $('#mfecha').html();
                    obj.local = $('#mLocal').html();
                    obj.cajero = $('#mCajero').html();
                    obj.tipoDoc = $('#tipoDocHtml').html();
                    obj.nroDoc = $('#mDni').html();
                    obj.nombres = $('#mNombres').html();
                    obj.apellidos = $('#mApellidos').html();
                    obj.monto = $('#mMonto').html();
                    obj.texto = $('#textoLegal').html();

                    var doc = new jsPDF('p', 'mm', [80, 130]);
                    var docfin = new jsPDF('p', 'mm', [80, 80]);

                    if(redes == 0){
                        console.log('redes: '+redes);
                        doc.deletePage(1);
                        doc.addPage([80, 80], 'portrait');
                        //doc = docfin;
                    }

                    doc.setFontSize(8)
                    doc.text(28,6,'APUESTA TOTAL')
                    doc.setFontType("bold");
                    doc.text(26,9,'Registro de Premios')
                    doc.text(23,12,'Nro de Ticket:'+obj.nroTicket+'')

                    doc.setFontSize(8)
                    doc.text(6, 19, 'Fecha: ')
                    doc.setFontType("normal");
                    doc.text(15,19,''+obj.fecha+'')

                    // doc.setFontType("bold")
                    // doc.text(6,45,'Cajero: ')
                    // doc.setFontType("normal")

                    // cajero = doc.setFont()
                    // .setFontSize(9)
                    // .splitTextToSize(obj.cajero, 65)
                    // doc.text(20,45,cajero)

                    doc.setFontType("bold")
                    doc.text(6,23,'Local: ')
                    doc.setFontType("normal")

                    local = doc.setFont()
                    .setFontSize(8)
                    .splitTextToSize(obj.local, 45)
                    doc.text(15,23,local)

                    doc.setFontSize(8)
                    doc.setFontType("bold")
                    doc.text(6,29,''+obj.tipoDoc+':')
                    doc.setFontType("normal")
                    doc.text(20,29,''+obj.nroDoc+'')

                    doc.setFontType("bold")
                    doc.text(6,33,'Nombres: ')
                    doc.setFontType("normal")

                    nombres = doc.setFont()
                    .setFontSize(8)
                    .splitTextToSize(obj.nombres, 40)
                    doc.text(20,33,nombres)

                    doc.setFontType("bold")
                    doc.text(6,38,'Apellidos: ')
                    doc.setFontType("normal")
                    apellidos = doc.setFont()
                    .setFontSize(8)
                    .splitTextToSize(obj.apellidos, 40)
                    doc.text(20,38,apellidos)

                    doc.setFontSize(10)
                    doc.setFontType("bold")
                    doc.text(6,43,'Monto: ')
                    doc.text(20,43,'s/ '+obj.monto+'')
                    doc.setFontType("normal")

                    if(redes == 1){
                        console.log('redesss: '+redes);
                        doc.setFontType("bold")
                        doc.setFontSize(7)
                        doc.text(6,49,'AUTORIZACIÓN')

                        doc.setFontType("normal")
                        doc.setFontSize(7)
                        texto = obj.texto;
                        lines = doc.setFont()
                        .setFontSize(6.3)
                        .splitTextToSize(texto, 65)
                        doc.text(6,52,lines)

                        textou = 'Para mayor información respecto a nuestra política de privacidad, dirigirse a la siguiente dirección: https://www.apuestatotal.com/politicas-de-privacidad';
                        linesu = doc.setFont()
                        .setFontSize(6.3)
                        .splitTextToSize(textou, 65)
                        doc.text(6,96,linesu)

                        doc.setFontType("normal")
                        doc.text(6,106,'Firma')
                        doc.text(6,109,'___________________________________')
                        doc.text(6,113,'.\n')

                    }else{
                        console.log('redesss: '+redes);
                        doc.setFontType("normal")
                        doc.text(6,49,'Firma')
                        doc.text(6,53,'___________________________________')
                        doc.text(6,57,'.\n');
                    }

                    docfin = doc;

                    docfin.autoPrint();
                    docfin.save(''+obj.nroTicket+'.pdf');
                    window.open(docfin.output('bloburl'), '_blank');

                    reset();
                    location.reload();
                }; */

    const setImagenFirma = (archivo) => {

        let promesa = new Promise((res, rej) => {
            let urlImage = '../files_bucket/registros/firmas/' + archivo;
            $('#imgFirmaRecurso').attr('src', urlImage);
            let imgHtml = $('#imgFirmaRecurso');

            setTimeout(() => {
                if (imgHtml.attr('src') === urlImage) {
                    res(true);
                } else {
                    rej(false);
                }
            }, 1000);
        });

        return promesa;
    }


    const getSignatureImg = (id) => {
        console.log('llego a promesa de traer url');
        let data = {};
        data.id = id;

        let promesa = new Promise((res, rej) => {

            $.post('/sys/get_registro_premios.php', {"valida_firma": data}, function (resp) {
                respuesta = JSON.parse(resp);
                if (respuesta.code == '001') {
                    res(respuesta.message);
                } else {
                    res(respuesta.message);
                }
            });

        });
        return promesa;
    };


    const getBase64Image = (img) => {
        //console.log('llego a promesa de imagen');
        //console.log(img);
        let promesa = new Promise((res, rej) => {

            var canvas = document.createElement("canvas");
            canvas.width = img.width;
            canvas.height = img.height;
            var ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0);
            let dataURL = canvas.toDataURL("image/png");

            setTimeout(() => {
                //console.log('entro al cslg: '+dataURL);
                if (dataURL === 'data:,') {
                    rej(dataURL);
                } else {
                    res(dataURL);
                }

            }, 1000);
            /* res (dataURL.replace(/^data:image\/(png|jpg);base64,/, "")); */
        });
        return promesa;
    };

    /* SOLO HACE printOnlySignature DE LOS REGISTROS CON FIRMA */
    const printOnlySignature = (id, tipoDoc) => {
        loading(true);
        var obj = {};
        var retorn = {};
        var data = {};
        var tiposDoc = ['DNI', 'CE/PTP', 'Pasaporte'];

        data.id = id;
        data.tipodoc = tipoDoc;

        getSignatureImg(id)
            .then(archivo => {
                //console.log(archivo);

                setImagenFirma(archivo)
                    .then(resp => {
                        //console.log(resp);
                        return getBase64Image(document.getElementById("imgFirmaRecurso"));
                    })
                    .then(imgbase64 => {
                        // console.log(imgbase64);
                        $.post('/sys/get_registro_premios.php', {"re_print_ticket": data}, function (datas) {
                            loading();
                            response = JSON.parse(datas);
                            //console.log('llego al post');
                            if (response.code == "001") {
                                retorn = response.message[0];
                                obj.nroTicket = retorn.ticket_id;
                                obj.fecha = retorn.created_at;
                                obj.local = retorn.local;
                                obj.tipoDoc = tiposDoc[tipoDoc];
                                obj.nroDoc = retorn.num_doc;
                                obj.nombres = retorn.nombre_cliente;
                                obj.apellidos = retorn.apellidos_cliente;
                                obj.monto = retorn.monto_entregado;
                                obj.texto = $('#textoLegal').html();
                                obj.textoLegalMarketing = $('#textoLegalMarketing').html();
                                obj.textoLegalClienteDB = $('#textoLegalClienteDB').html();
                                obj.redes = retorn.autoriza;

                                /* console.log(retorn.local);
                                console.log(imgbase64); */

                                /*var doc = new jsPDF('p', 'mm', [80, 160])*/
                                var docAux = new jsPDF('p', 'mm', [200, 200]);
                                let marketingLines = docAux.setFont().setFontSize(6.3).splitTextToSize(obj.textoLegalMarketing, 65).length;
                                let dbLines = docAux.setFont().setFontSize(6.3).splitTextToSize(obj.textoLegalClienteDB, 65).length;
                                let baseHeight = 52 + dbLines * 2.7 + 10 + 30;
                                let docHeight = obj.redes == 1 ? baseHeight + marketingLines * 2.7 : baseHeight;
                                //let docHeight = obj.redes == 1 ? 190 : 150;

                                var cRatio = 2.83;
                                var doc = new jsPDF('p', 'mm', [80 * cRatio, docHeight * cRatio])
                                var docfin = new jsPDF('p', 'mm', [80 * cRatio, 160 * cRatio]);

                                /*  if (obj.redes == 0) {
                                      doc.deletePage(1);
                                      doc.addPage([80, 80], 'portrait');
                                      //doc = docfin;
                                  }*/

                                let justifyTextOption = {
                                    align: "justify",
                                    maxWidth: 65
                                }

                                doc.setFontSize(8)
                                doc.text(28, 6, 'APUESTA TOTAL')
                                doc.setFontType("bold");
                                doc.text(26, 9, 'Registro de Premios')
                                doc.text(23, 12, 'Nro de Ticket:' + obj.nroTicket + '')

                                doc.setFontSize(8)
                                doc.text(6, 19, 'Fecha: ')
                                doc.setFontType("normal");
                                doc.text(15, 19, '' + obj.fecha + '')

                                doc.setFontType("bold")
                                doc.text(6, 23, 'Local: ')
                                doc.setFontType("normal")

                                local = doc.setFont()
                                    .setFontSize(8)
                                    .splitTextToSize(obj.local, 45)
                                doc.text(15, 23, local)

                                doc.setFontSize(8)
                                doc.setFontType("bold")
                                doc.text(6, 29, '' + obj.tipoDoc + ':')
                                doc.setFontType("normal")
                                doc.text(20, 29, '' + obj.nroDoc + '')

                                doc.setFontType("bold")
                                doc.text(6, 33, 'Nombres: ')
                                doc.setFontType("normal")

                                nombres = doc.setFont()
                                    .setFontSize(8)
                                    .splitTextToSize(obj.nombres, 40)
                                doc.text(20, 33, nombres)

                                doc.setFontType("bold")
                                doc.text(6, 38, 'Apellidos: ')
                                doc.setFontType("normal")
                                apellidos = doc.setFont()
                                    .setFontSize(8)
                                    .splitTextToSize(obj.apellidos, 40)
                                doc.text(20, 38, apellidos)

                                doc.setFontSize(10)
                                doc.setFontType("bold")
                                doc.text(6, 43, 'Monto: ')
                                doc.text(20, 43, 's/ ' + obj.monto + '')
                                doc.setFontType("normal")

                                doc.setFontType("bold")
                                doc.setFontSize(7)
                                doc.text(6, 49, 'AUTORIZACIÓN')

                                doc.setFontType("normal")
                                doc.setFontSize(7)
                                let textoLegalClienteDB = obj.textoLegalClienteDB;
                                let linesClienteDB = doc.setFont()
                                    .setFontSize(6.3)
                                    .splitTextToSize(textoLegalClienteDB, 65)
                                doc.text(6, 52, linesClienteDB, justifyTextOption)
                                let marketingHeight = 52 + linesClienteDB.length * 2.7;

                                if (obj.redes == 1) {
                                    doc.setFontType("normal")
                                    let textoLegalMarketing = obj.textoLegalMarketing;
                                    var linesMarketing = doc.setFont()
                                        .setFontSize(6.3)
                                        .splitTextToSize(textoLegalMarketing, 65)
                                    doc.text(6, marketingHeight, linesMarketing, justifyTextOption)

                                    /*textou = 'Para mayor información respecto a nuestra política de privacidad, dirigirse a la siguiente dirección: https://www.apuestatotal.com/politicas-de-privacidad';
                                    linesu = doc.setFont()
                                        .setFontSize(6.3)
                                        .splitTextToSize(textou, 65)
                                    let policyHeight = marketingHeight + 11 * 2.6;
                                    doc.text(6, policyHeight, linesu)*/
                                } else {
                                    /* doc.setFontType("normal")
                                    doc.text(6,49,'Firma')
                                    doc.text(6,53,'___________________________________') */
                                    //doc.text(6, 57, '\n')
                                }
                                doc.setFontSize(6);
                                let signatureHeight = obj.redes == 1 ? marketingHeight + linesMarketing.length * 2.7 : marketingHeight;
                                doc.text(6, signatureHeight + 1, 'Firma:')
                                doc.text(15, signatureHeight + 1, nombres)
                                doc.text(15, signatureHeight + 3, apellidos)
                                doc.rect(6, signatureHeight + 6, 65, 30)
                                doc.addImage(imgbase64, 'PNG', 6, signatureHeight + 6, 65, 30)
                                //doc.addImage(imgbase64, 'PNG',6,113,65,30)
                                doc.text(6, 152, '\n')

                                docfin = doc;
                                docfin.autoPrint();
                                docfin.save('' + obj.nroTicket + '.pdf');
                                window.open(docfin.output('bloburl'), '_blank');

                                loading(false);

                            } else {
                                console.log(result.error);
                            }

                        });

                    })
                    .catch(error => {
                        console.log('Canvas vacio: ' + error);
                    })
                    .catch(error => {
                        console.log(error);
                    })
            })
            .catch(error => {
                console.log(error);
            });
    }

    function reset() {
        $('#txtMonto').attr('disabled', true);
        $('#cbLocales').attr('disabled', true);
        $('#CE_Nro').attr('disabled', true);
        $('#CE_Nombres').attr('disabled', true);
        $('#CE_ApePat').attr('disabled', true);
        $('#CE_ApeMat').attr('disabled', true);
        $('#PS_Nro').attr('disabled', true);
        $('#PS_Nombres').attr('disabled', true);
        $('#PS_ApePat').attr('disabled', true);
        $('#PS_ApeMat').attr('disabled', true);
    }

    function cargaImg(id, type) {
        var data = {};
        data.id = id;

        /*datas.update = true;*/
        $.post("sys/set_registro_fotos_premios.php", {"get_img": data, "type": type}, function (data) {
            loading();
            let result = JSON.parse(data);
            let i = result.length;
            for (let x = 0; x < i; x++) {
                if (typeof (result[x]['archivo']) != 'undefined') {
                    //$('#miniatura').html(" ");
                    $('#miniatura').append("<img class='mini' src='../files_bucket/registros/premios/min_" + result[x]['archivo'] + "' />");
                }
            }

            if (typeof (result[0]) != 'undefined') {
                $('#previewImg').attr('src', "../files_bucket/registros/premios/" + result[0]['archivo']);
            }
        });
    };

    function verifica(id, cantAnt, type) {
        var cantAct;
        let data = {};
        data.id = id;
        $.post("sys/set_registro_fotos_premios.php", {"upload_status": data, "type": type}, function (datas) {
            loading();
            let result = JSON.parse(datas);
            cantAct = result.cant;
            if (cantAct > cantAnt) {
                //location.reload();
                $('#miniatura').html(" ");
                cargaImg(id, type);
                $('.showQR').css('display', 'none');
                $('.formUpload').css('display', 'block');
                $('[data-id-jackpot="' + id + '"]').attr('data-cant', cantAct);
                $('[data-id-jackpot="' + id + '"]').html('');
                $('[data-id-jackpot="' + id + '"]').html('<i class="fa fa-camera" aria-hidden="true"></i> (' + cantAct + ') ');

                if ($('[data-id-jackpot="' + id + '"]').hasClass('btn-danger') == 1) {
                    $('[data-id-jackpot="' + id + '"]').removeClass('btn-danger');
                    $('[data-id-jackpot="' + id + '"]').addClass('btn-primary');
                }

                swal({
                    title: "Registro Exitoso",
                    text: "",
                    type: "success",
                    timer: 500,
                    closeOnConfirm: false,
                    showCancelButton: false,
                    showConfirmButton: false
                });

                window.setTimeout(function () {
                    loading(true);
                }, 2000);

                window.setTimeout(function () {
                    location.reload();
                }, 2500);
            }
        });
    };

    function verificaFirma(id) {
        let data = {};
        data.id = id;
        $.post("sys/set_registro_fotos_premios.php", {"upload_status_firma": data}, function (datas) {
            loading();
            console.log(datas);
            let result = JSON.parse(datas);
            cantAct = result.cant;
            if (cantAct > 0) {
                swal({
                    title: "Registro Exitoso",
                    text: "",
                    type: "success",
                    timer: 500,
                    closeOnConfirm: false,
                    showCancelButton: false,
                    showConfirmButton: false
                });

                window.setTimeout(function () {
                    loading(true);
                }, 2000);

                window.setTimeout(function () {
                    location.reload();
                }, 2500);
            }
        });

    }

    function deleteImg() {
        let src = 'images/default_avatar.png';
        $('#miniatura').find('img').remove();
        $('#previewImg').attr('src', src);
        let html = "<i class='fa fa-picture-o' aria-hidden='true'></i> <span id='leyenda'>Elegir imagenes</span>";
        $('#labelbtn').html('');
        $('.labelbtn').html(html);
    }

    function getDoc() {
        let dni = $('#txtDniCliente').val();
        let cex = $('#Nro_doc').val();
        let ps = $('#Nro_doc_Ps').val();

        return dni !== "" ? dni : cex !== "" ? cex : ps !== "" ? ps : "";
    }

    function getClientData(tipoDoc) {
        let client = {
            nombres: "",
            apellido_paterno: "",
            apellido_materno: ""
        };
        switch (tipoDoc) {
            case 0:
                client.nombres = $("#tnombres").html();
                client.apellido_paterno = $("#tapepat").html();
                client.apellido_materno = $("#tapemat").html();
                break;
            case 1:
                client.nombres = $("#CE_Nombres").val();
                client.apellido_paterno = $("#CE_ApePat").val();
                client.apellido_materno = $("#CE_ApeMat").val();
                break;
            case 2:
                client.nombres = $("#PS_Nombres").val();
                client.apellido_paterno = $("#PS_ApePat").val();
                client.apellido_materno = $("#PS_ApeMat").val();
                break;
            default:
                break;
        }
        return client;
    }

    function splitNames(surname) {
        let names = surname.split(" ");
        let first_surname = "";
        for (let i = 0; i < names.length - 1; i++) {
            first_surname += names[i] + " ";
        }
        if (names.length === 1) {
            return {
                first_surname: surname,
                last_surname: ""
            };
        } else {
            return {
                first_surname: first_surname,
                last_surname: names[names.length - 1]
            };
        }

    }
}


const printOnly = (id, tipoDoc) => {
    loading(true);
    var obj = {};
    var retorn = {};
    var data = {};
    var tiposDoc = ['DNI', 'CE/PTP', 'Pasaporte'];

    data.id = id;
    data.tipodoc = tipoDoc;

    // console.log(imgbase64);
    $.post('/sys/get_registro_premios.php', {"re_print_ticket": data}, function (datas) {
        loading();
        response = JSON.parse(datas);
        console.log('llego al post');
        if (response.code == "001") {
            retorn = response.message[0];
            obj.nroTicket = retorn.ticket_id;
            obj.type = retorn.type ? ' en ' + retorn.type : '';
            obj.fecha = retorn.created_at;
            obj.local = retorn.local ?? "No registrada";
            obj.tipoDoc = tiposDoc[tipoDoc];
            obj.nroDoc = retorn.num_doc;
            obj.nombres = retorn.nombre_cliente;
            obj.apellidos = retorn.apellidos_cliente;
            obj.monto = retorn.monto_entregado;
            obj.texto = $('#textoLegal').html();
            obj.textoLegalMarketing = $('#textoLegalMarketing').html();
            obj.textoLegalClienteDB = $('#textoLegalClienteDB').html();
            obj.textoLegalSorteo = $('#textoLegalSorteo').html() + ` ${obj.local}.`;
            obj.redes = retorn.autoriza;
            obj.tipo_registro = retorn.tipo_registro;


            /*var doc = new jsPDF('p', 'mm', [80, 160])*/
            var docAux = new jsPDF('p', 'mm', [200, 200]);
            let marketingLines = docAux.setFont().setFontSize(6.3).splitTextToSize(obj.textoLegalMarketing, 65).length;
            let dbLines = docAux.setFont().setFontSize(6.3).splitTextToSize(obj.textoLegalClienteDB, 65).length;
            let sorteoLines = docAux.setFont().setFontSize(6.3).splitTextToSize(obj.textoLegalSorteo, 65).length;

            let isSorteo = obj.tipo_registro === "3";

            let registroTitle;
            switch (obj.tipo_registro){
                case "0":
                    registroTitle = "Jackpot";
                    break;
                case "1":
                    registroTitle = "Bingo";
                    break;
                case "2":
                    registroTitle = "Premio";
                    break;
                default:
                    registroTitle = "Premio";
                    break;
            }

            let baseHeight = 52;
            let docHeight = 0;
            if (isSorteo) {
                baseHeight += sorteoLines * 2.7 + 10 + 30;
                docHeight = baseHeight;
            } else {
                baseHeight += dbLines * 2.7 + 10 + 30;
                docHeight = obj.redes == 1 ? baseHeight + marketingLines * 2.7 : baseHeight;
            }

            //let docHeight = obj.redes == 1 ? 190 : 150;

            var cRatio = 2.83;
            var doc = new jsPDF('p', 'mm', [80 * cRatio, docHeight * cRatio])
            var docfin;

            let justifyTextOption = {
                align: "justify",
                maxWidth: 65
            }

            if (isSorteo) {
                doc.setFontSize(8)
                doc.text(28, 6, 'APUESTA TOTAL')
                doc.setFontType("bold");
                doc.text(26, 9, 'Comprobante de Pago')
                doc.text(23, 12, '')

                doc.setFontSize(8)
                doc.text(6, 19, 'Fecha de registro: ')
                doc.setFontType("normal");
                doc.text(30, 19, '  ' + obj.fecha + '')

                doc.setFontSize(8)
                doc.setFontType("bold")
                doc.text(6, 23, '' + obj.tipoDoc + ':')
                doc.setFontType("normal")
                doc.text(22, 23, '' + obj.nroDoc + '')

                doc.setFontType("bold")
                doc.text(6, 27, 'Nombres: ')
                doc.setFontType("normal")

                nombres = doc.setFont()
                    .setFontSize(8)
                    .splitTextToSize(obj.nombres, 50)
                doc.text(20, 27, nombres)

                doc.setFontType("bold")
                doc.text(6, 32, 'Apellidos: ')
                doc.setFontType("normal")
                apellidos = doc.setFont()
                    .setFontSize(8)
                    .splitTextToSize(obj.apellidos, 50)
                doc.text(20, 32, apellidos)

                doc.setFontSize(10)
                doc.setFontType("bold")
                doc.text(6, 37, 'Monto Pagado: ')
                doc.text(34, 37, 's/ ' + obj.monto + obj.type)
                doc.setFontType("normal")

                doc.setFontType("bold")
                doc.setFontSize(7)
                doc.text(6, 43, 'Comprobante de pago')

                let signatureHeight = 0;
                doc.setFontType("normal")
                doc.setFontSize(7)
                let textoLegalSorteo = obj.textoLegalSorteo;
                let linesSorteo = doc.setFont()
                    .setFontSize(6.3)
                    .splitTextToSize(textoLegalSorteo, 65)
                doc.text(6, 49, linesSorteo, justifyTextOption)
                signatureHeight = 49 + linesSorteo.length * 2.7;

                doc.setFontSize(6);
                doc.text(6, signatureHeight + 3, 'Firma:')
                doc.text(15, signatureHeight + 3, nombres)
                doc.text(15, signatureHeight + 5, apellidos)
                doc.rect(6, signatureHeight + 8, 65, 30)
            } else {
                doc.setFontSize(8)
                doc.text(28, 6, 'APUESTA TOTAL')
                doc.setFontType("bold");
                doc.text(26, 9, `Registro de ${registroTitle}`)

                if(obj.tipo_registro == "6"){/*torito*/
                    doc.text(19, 12, 'Nro de Transacción:' + obj.nroTicket + '');
                }
                else{
                    doc.text(23, 12, 'Nro de Ticket:' + obj.nroTicket + '');
                }

                doc.setFontSize(8)
                doc.text(6, 19, 'Fecha de registro: ')
                doc.setFontType("normal");
                doc.text(30, 19, '  ' + obj.fecha + '')

                doc.setFontType("bold")
                doc.text(6, 23, 'Local de pago: ')
                doc.setFontType("normal")

                local = doc.setFont()
                    .setFontSize(8)
                    .splitTextToSize(obj.local, 45)
                doc.text(27, 23, local)

                doc.setFontSize(8)
                doc.setFontType("bold")
                doc.text(6, 29, '' + obj.tipoDoc + ':')
                doc.setFontType("normal")
                doc.text(20, 29, '' + obj.nroDoc + '')

                doc.setFontType("bold")
                doc.text(6, 33, 'Nombres: ')
                doc.setFontType("normal")

                nombres = doc.setFont()
                    .setFontSize(8)
                    .splitTextToSize(obj.nombres, 50)
                doc.text(20, 33, nombres)

                doc.setFontType("bold")
                doc.text(6, 38, 'Apellidos: ')
                doc.setFontType("normal")
                apellidos = doc.setFont()
                    .setFontSize(8)
                    .splitTextToSize(obj.apellidos, 50)
                doc.text(20, 38, apellidos)

                doc.setFontSize(10)
                doc.setFontType("bold")
                doc.text(6, 43, 'Monto Pagado: ')
                doc.text(34, 43, 's/ ' + obj.monto + obj.type)
                doc.setFontType("normal")

                doc.setFontType("bold")
                doc.setFontSize(7)
                doc.text(6, 49, 'AUTORIZACIÓN')

                doc.setFontType("normal")
                doc.setFontSize(7)
                let textoLegalClienteDB = obj.textoLegalClienteDB;
                let linesClienteDB = doc.setFont()
                    .setFontSize(6.3)
                    .splitTextToSize(textoLegalClienteDB, 65)
                doc.text(6, 52, linesClienteDB, justifyTextOption)
                let marketingHeight = 52 + linesClienteDB.length * 2.7;

                if (obj.redes == 1) {
                    doc.setFontType("normal")
                    let textoLegalMarketing = obj.textoLegalMarketing;
                    var linesMarketing = doc.setFont()
                        .setFontSize(6.3)
                        .splitTextToSize(textoLegalMarketing, 65)
                    doc.text(6, marketingHeight, linesMarketing, justifyTextOption)
                }

                signatureHeight = obj.redes == 1 ? marketingHeight + linesMarketing.length * 2.7 : marketingHeight;
                doc.setFontSize(6);
                doc.text(6, signatureHeight + 1, 'Firma:')
                doc.text(15, signatureHeight + 1, nombres)
                doc.text(15, signatureHeight + 3, apellidos)
                doc.rect(6, signatureHeight + 6, 65, 30)
            }

            //doc.addImage(imgbase64, 'PNG',6,113,65,30)

            docfin = doc;
            docfin.autoPrint();
            docfin.save('' + obj.nroTicket + '.pdf');
            window.open(docfin.output('bloburl'), '_blank');

            loading(false);

        } else {
            console.log(result.error);
        }

    });
}