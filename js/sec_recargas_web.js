$(function () {

    if (sec_id === 'recargas_web') {
        $('#saldoweb_btn_consultar').click(function () {
            saldoweb_buscar_cliente();
        });
        $('#saldoweb_idweb').keypress(function(event){
            var keycode = (event.keyCode ? event.keyCode : event.which);
            if(keycode == '13'){
                saldoweb_buscar_cliente();
            }
        });
        $('#saldoweb_btn_regresar').click(function () {
            $('#saldoweb_btn_regresar').hide();
            $('#saldoweb_cliente_div').hide();
            $('#saldoweb_cliente_buscador_div').show();
            $('#saldoweb_idweb').val('');
        });
        $('#saldoweb_btn_deposito').click(function () {
            saldoweb_nuevo_deposito();
        });
        $('#saldoweb_btn_retiro').click(function () {
            saldoweb_nuevo_retiro();
        });

        getCreditoDisponible();

        $('#saldoweb_idweb').focus();
    }

});

function saldoweb_buscar_cliente_limpiar_campos(){
    $('#saldoweb_cliente_idweb').html('');
    $('#saldoweb_cliente_name').html('');
    $('#saldoweb_cliente_div').hide();
}

function saldoweb_buscar_cliente() {
    saldoweb_buscar_cliente_limpiar_campos();
    $('#saldoweb_btn_consultar').hide();
    $('#saldoweb_idweb').css('border', '');

    var id_web = $('#saldoweb_idweb').val();
    if (!(parseInt(id_web) > 0)) {
        $('#saldoweb_idweb').css('border', '1px solid red');
        $('#saldoweb_idweb').focus();
        $('#saldoweb_btn_consultar').show();
        return false;
    }
    var data = {
        "accion": "obtener_cliente",
        "id_web": id_web
    };
    //auditoria_send({ "proceso": "obtener_cliente", "data": data });
    $.ajax({
        url: "/sys/set_recargas_web.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            $('#saldoweb_btn_consultar').show();
            var respuesta = JSON.parse(resp);
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) === 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) === 200) {
                $('#saldoweb_btn_regresar').show();
                $('#saldoweb_cliente_buscador_div').hide();
                $('#saldoweb_cliente_div').show();
                $('#saldoweb_cliente_idweb').html(id_web);
                $('#saldoweb_cliente_name').html(respuesta.cliente_name);
                saldoweb_tbl_transacciones_listar();
                return false;
            }
        },
        error: function () {}
    });
}

function saldoweb_tbl_transacciones_limpiar() {
    $('#saldoweb_tbl_transacciones').html(
        '<thead>' +
        '<tr>' +
        '   <th class="text-center">Registro</th>' +
        '   <th class="text-center">Tipo</th>' +
        '   <th class="text-center">Transacción</th>' +
        '   <th class="text-center">Monto</th>' +
        '   <th class="text-center">Estado</th>' +
        '   <th class="text-center">Usuario</th>' +
        '   <th class="text-center">Acción</th>' +
        '</tr>' +
        '</thead>' +
        '<tbody>'
    );
}

function saldoweb_tbl_transacciones_listar(){
    saldoweb_tbl_transacciones_limpiar();
    $.post("/sys/set_recargas_web.php", {
            accion: "obtener_cliente_x_transacciones",
            id_web: $('#saldoweb_idweb').val()
        })
    .done(function (data) {
        try {
            //console.log(data);
            var respuesta = JSON.parse(data);
            if (parseInt(respuesta.http_code) === 200) {
                if (respuesta.result.length > 0) {
                    $.each(respuesta.result, function(index, item) {

                        //var variables = "'" + item.registro + "','" + item.tipo + "','"  + item.txn_id + "','" + item.monto + "','" + item.usuario + "'";
                        var status_color = 'red';
                        var btn = '';
                        if(item.status==='Completado'){
                            status_color = 'green';
                            btn = '<button type="button" class="btn btn-primary" style="padding: 2px 5px;"'+
                                            //'    onclick="ver_voucher('+variables+')">'+
                                            '    title="Imprimir Voucher" onclick="imprimir_voucher_exacto('+item.cod_transaccion+','+item.tipo_id+')">'+
                                            '<span class="fa fa-print"></span>'+
                                            '</button>';
                        } else {
                            var today = (new Date()).toISOString().split('T')[0];
                            var day = Date.parse(new Date(today + " 00:00:00"));
                            var registro = Date.parse(item.registro);
                            console.log(day+"-----"+registro);
                            if(parseInt(item.tipo_id)===1 && registro > day && saldoweb_ccid == item.cc_id){
                                btn = '<button type="button" class="btn btn-success" style="padding: 2px 5px;"'+
                                                '    title="Reenviar Solicitud" onclick="saldoweb_realizar_deposito_reintento('+item.cod_transaccion+')">'+
                                                '<span class="fa fa-cloud-upload"></span>'+
                                                '</button>';
                            }
                        }

                        $('#saldoweb_tbl_transacciones').append(
                            '<tr>' +
                                '<td class="text-center">' + item.registro + '</td>' +
                                '<td class="text-center">' + item.tipo + '</td>' +
                                '<td class="text-center">' + item.txn_id + '</td>' +
                                '<td class="text-right">' + item.monto + '</td>' +
                                '<td class="text-center" style="color:'+status_color+';">' + item.status + '</td>' +
                                '<td class="text-center">' + item.usuario + '</td>' +
                                '<td class="text-center">' + 
                                        btn +
                                '</td>' +
                            '</tr>'
                        );
                    });
                } else {
                    $('#saldoweb_tbl_transacciones').append(
                        '<tr>' +
                        '<td colspan="7" class="text-center">NO HAY DATOS</td>' +
                        '</tr>'
                    );
                }
            } else {
                $('#saldoweb_tbl_transacciones').append(
                    '<tr>' +
                    '<td colspan="7" class="text-center">NO HAY DATOS</td>' +
                    '</tr>'
                );
            }
        } catch (e) {
            swal('¡Error!', e, 'error');
            console.log("Error de TRY-CATCH --> Error: " + e);
        }
    })
    .fail(function (xhr, status, error) {
        swal('¡Error!', error, 'error');
        console.log("Error de .FAIL -- Error: " + error);
    });
}


function getCreditoDisponible(){

    var data = {
        "accion": "obtener_credito_disponible_local",
        "cc_id": saldoweb_ccid,
        "local_id": saldoweb_local_id,
    };    
    var credito = 0;
    $.ajax({
        url: "/sys/set_recargas_web.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (resp) {
            var respuesta = JSON.parse(resp);
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) === 400) {
                $('#saldoweb_modal_deposito').modal('hide');
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) === 200) {
                
                credito = (Math.round((respuesta.result+ Number.EPSILON) * 100) / 100).toFixed(2);
                $('.credito_disponible').html('S/ '+ credito);
                if (credito == 0.00) {
                    $(".credito_disponible").css("color", "red");
                }
            }
        },
        error: function () {
            alert('error');
        },
        async: false 
    });

    return credito;
}




//*******************************************************************************************************************
//*******************************************************************************************************************
// REALIZAR DEPÓSITO
//*******************************************************************************************************************
//*******************************************************************************************************************
$(function () {
    if (sec_id === 'recargas_web') {
        //Monto
        $("#saldoweb_modal_deposito_monto").on({
            "focus": function (event) {
                $(event.target).select();
                //console.log('focus');
            },
            "blur": function (event) {
                //console.log('blur');
                if(parseFloat($(event.target).val().replace(/\,/g, ''))>0){
                    $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
                    $(event.target).val(function (index, value ) {
                        return value.replace(/\D/g, "")
                                    .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    });
                } else {
                    $(event.target).val("0.00");
                }
            }
        });
        $('#saldoweb_modal_deposito_btn_guardar').click(function () {
            saldoweb_realizar_deposito();
        });
    }

});

function saldoweb_nuevo_deposito(){
    $('#saldoweb_modal_deposito_monto').val('');
    $('#saldoweb_modal_deposito').modal();
}

var mensaje_limite = 'Haz alcanzado el límite máximo de recargas. Comunícate con tu asesor a la brevedad.';

function saldoweb_realizar_deposito(){
    //console.log(saldoweb_ccid);
    $('#saldoweb_modal_deposito_btn_guardar').hide();

    var monto = parseFloat($('#saldoweb_modal_deposito_monto').val().replace(/\,/g, '')).toFixed(2);
    if (!(parseFloat(monto) > 0)) {
        $('#saldoweb_modal_deposito_monto').css('border', '1px solid red');
        $('#saldoweb_modal_deposito_monto').focus();
        $('#saldoweb_modal_deposito_btn_guardar').show();
        return false;
    }
    // if (!(parseFloat(monto) >= 1.00 && parseFloat(monto) <= 3000.00)) {
    //     $('#saldoweb_modal_deposito_monto').css('border', '1px solid red');
    //     $('#saldoweb_modal_deposito_monto').focus();
    //     $('#saldoweb_modal_deposito_btn_guardar').show();
    //     swal('Aviso', 'El monto debe ser mínimo de 1.00 y máximo de 3,000.00.', 'warning');
    //     return false;
    // }
    var data = {
        "accion": "realizar_deposito",
        "id_web": $('#saldoweb_idweb').val(),
        "client_name": $('#saldoweb_cliente_name').html(),
        "monto": monto,
        "cc_id": saldoweb_ccid,
        "local_id": saldoweb_local_id,
        "tienda_nombre": saldoweb_tienda_nombre
    };
    //auditoria_send({ "proceso": "obtener_cliente", "data": data });
    //console.log ("imprimir voucher");
    $.ajax({
        url: "/sys/set_recargas_web.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            $('#saldoweb_modal_deposito_btn_guardar').show();
            
            var respuesta = JSON.parse(resp);
            if(respuesta.lock_agente_can_deposit == true){
                $('#saldoweb_btn_deposito').hide();
            }
            if (parseInt(respuesta.http_code) === 400) {
                $('#saldoweb_modal_deposito').modal('hide');
                swal('Aviso', respuesta.status, 'warning');
                saldoweb_tbl_transacciones_listar();
                return false;
            }
            if (parseInt(respuesta.http_code) === 200) {
                $('#saldoweb_modal_deposito').modal('hide');
                $('#saldoweb_cliente_buscador_div').hide();

                printOnlySignature_saldo_web2(respuesta.cod_transaccion, 1, 0);
                saldoweb_tbl_transacciones_listar();
                
                // swal('Aviso', 'El depósito fue exitoso.', 'success');

                Swal.fire({
                    title: 'Aviso',
                    html: '<span style="font-size:25px">El depósito fue exitoso.</span>',
                    icon: 'success',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#3085d6',
                  }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    var credito = getCreditoDisponible();
                    if (respuesta.alerta_limite) {
                      Swal.fire('Aviso', mensaje_limite , 'warning')
                    } 
                  })
                  
                return false;
            }
        },
        error: function () {}
    });
}
function saldoweb_realizar_deposito_reintento(cod_txn){

    if (!(parseInt(cod_txn) > 0)) {
        return false;
    }
    var data = {
        "accion": "realizar_deposito_reintento",
        "id_web": $('#saldoweb_idweb').val(),
        "cod_txn": cod_txn,
        "tienda_nombre": saldoweb_tienda_nombre,
        "cc_id": saldoweb_ccid,
    };
    //auditoria_send({ "proceso": "obtener_cliente", "data": data });
    //console.log ("imprimir voucher");
    $.ajax({
        url: "/sys/set_recargas_web.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            $('#saldoweb_modal_deposito_btn_guardar').show();
            
            var respuesta = JSON.parse(resp);
            if(respuesta.lock_agente_can_deposit == true){
                $('#saldoweb_btn_deposito').hide();
            }
            if (parseInt(respuesta.http_code) === 400) {
                $('#saldoweb_modal_deposito').modal('hide');
                swal('Aviso', respuesta.status, 'warning');
                saldoweb_tbl_transacciones_listar();
                return false;
            }
            if (parseInt(respuesta.http_code) === 200) {
                $('#saldoweb_modal_deposito').modal('hide');
                $('#saldoweb_cliente_buscador_div').hide();

                printOnlySignature_saldo_web2(respuesta.cod_transaccion, 1, 0);
                saldoweb_tbl_transacciones_listar();
                
                // swal('Aviso', 'El depósito fue exitoso.', 'success');
                Swal.fire({
                    title: 'Aviso',
                    html: '<span style="font-size:25px">El depósito fue exitoso.</span>',
                    icon: 'success',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#3085d6',
                  }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    var credito = getCreditoDisponible();
                    if (respuesta.alerta_limite) {
                      Swal.fire('Aviso', mensaje_limite , 'warning')
                    } 
                  })
                  
                return false;
            }
        },
        error: function () {}
    });
}

function imprimir_voucher_exacto(txn_id, tipo_id){    

    var data1 = {
        "accion": "obtener_transaccion",
        "txn_id": txn_id,
        "tipo_id": tipo_id
    };
    //auditoria_send({ "proceso": "obtener_cliente", "data": data });
    $.ajax({
        url: "/sys/set_recargas_web.php",
        type: 'POST',
        data: data1,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            var respuesta = JSON.parse(resp);
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) === 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) === 200) {
                if(parseInt(tipo_id)===1){
                    $('#modal_saldoWeb_deposito_voucher_nrecibo').html(respuesta.result.txn_id);
                    $('#modal_saldoWeb_deposito_voucher_fechahora').html(respuesta.result.created_at);
                    $('#modal_saldoWeb_deposito_voucher_datosCliente').html(respuesta.result.client_name+ " - " +respuesta.result.client_id);
                    $('#modal_saldoWeb_deposito_voucher_monto').html(respuesta.result.monto+" PEN");
                    $('#modal_saldoWeb_deposito_voucher_tienda').html(respuesta.result.local_nombre);
                    $('#modal_saldoWeb_deposito_voucher_direccion').html(respuesta.result.local_direccion);
                    $('#div_voucher_saldo_web').html('');
                    if ([6,9,10].includes(parseInt(saldoweb_area_id))) {
                        $('#div_voucher_saldo_web').html('<button id="sec_tlv_copiar_voucher_apuesta_pagada" type="button" '
                            +'class="btn btn-success btn-sm pull-right printOnlySignature_saldo_web2" '
                            +'data-txn_id-paper="' + txn_id + '" '
                            +'data-tipo_id-paper="' + tipo_id + '" >'
                            +'<b><i class="fa fa-print"></i> Imprimir</b>'
                            +'</button>');
                    }
                    $('#modal_saldoWeb_deposito_voucher').modal();
                }
                if(parseInt(tipo_id)===2){
                    $('#modal_saldoWeb_retiro_voucher_nrecibo').html(respuesta.result.txn_id);
                    $('#modal_saldoWeb_retiro_voucher_fechahora').html(respuesta.result.created_at);
                    $('#modal_saldoWeb_retiro_voucher_nombreCompleto').html(respuesta.result.client_name);
                    $('#modal_saldoWeb_retiro_voucher_DNI').html(respuesta.result.client_num_doc);
                    $('#modal_saldoWeb_retiro_voucher_jugadorID').html(respuesta.result.client_id);
                    $('#modal_saldoWeb_retiro_voucher_monto').html(respuesta.result.monto+" PEN");
                    $('#modal_saldoWeb_retiro_voucher_tienda').html(respuesta.result.local_nombre);
                    $('#modal_saldoWeb_retiro_voucher_direccion').html(respuesta.result.local_direccion);
                    $('#div_voucher_saldo_web_retiro').html('');
                    if ([6,9,10].includes(parseInt(saldoweb_area_id))) {
                        $('#div_voucher_saldo_web_retiro').html('<button id="sec_tlv_copiar_voucher_apuesta_pagada_retiro" type="button" '
                            +'class="btn btn-success btn-sm pull-right printOnlySignature_saldo_web_retiro" '
                            +'data-txn_id-paper="' + txn_id + '" '
                            +'data-tipo_id-paper="' + tipo_id + '" >'
                            +'<b><i class="fa fa-print"></i> Imprimir</b>'
                            +'</button>');
                    }
                    $('#modal_saldoWeb_retiro_voucher').modal();
                }
                return false;
            }
        },
        error: function () {
            return false;
        }
    });

}









//*******************************************************************************************************************
//*******************************************************************************************************************
// VOUCHER DEPÓSITO
//*******************************************************************************************************************
//*******************************************************************************************************************

$('body').on('click', '.printOnlySignature_saldo_web2', function () {
    var txn_id = $(this).attr('data-txn_id-paper');
    var tipo_id = $(this).attr('data-tipo_id-paper');
    printOnlySignature_saldo_web2(txn_id, tipo_id, 1);
});

const setImagenFirma2 = (archivo) => {

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

/* SOLO HACE printOnlySignature DE LOS REGISTROS CON FIRMA */
const printOnlySignature_saldo_web2 = (txn_id, tipo_id, valid_reimpresion) => {
    loading(true);
    var obj = {};
    var retorn = {};
    var data = {};
    var tiposDoc = ['DNI', 'CE/PTP', 'Pasaporte'];

    data.txn_id = txn_id;
    data.tipo_id = tipo_id;


    // console.log(imgbase64);
    $.post('/sys/set_recargas_web.php', {"obtener_transaccion": data}, function (datas) {
        loading();
        response = JSON.parse(datas);
        //console.log('llego al post');
        if (response.http_code == "200") {
            retorn = response.result;
            obj.txn_id = retorn.txn_id;
            obj.cc_id = retorn.cc_id;
            obj.created_at = retorn.created_at;
            obj.client_id = retorn.client_id;
            obj.client_name = retorn.client_name;
            obj.monto = retorn.monto;
            obj.nombre = retorn.local_nombre;
            obj.direccion = retorn.local_direccion;
            obj.texto = 'texto test';
            obj.textoLegalMarketing = 'legal marketing test';
            obj.textoLegalClienteDB = 'legal cliente test';

            /* console.log(retorn.local);
            console.log(imgbase64); */

            //*******************************************************************************************************************
            //*******************************************************************************************************************
            // CENTRAR TEXTO EN JSPDF
            //*******************************************************************************************************************
            //*******************************************************************************************************************

            (function(API){
                API.myText = function(txt, options, x, y) {
                    options = options ||{};
                    /* Use the options align property to specify desired text alignment
                     * Param x will be ignored if desired text alignment is 'center'.
                     * Usage of options can easily extend the function to apply different text 
                     * styles and sizes 
                    */
                    if( options.align == "center" ){
                        // Get current font size
                        var fontSize = this.internal.getFontSize();
            
                        // Get page width
                        var pageWidth = this.internal.pageSize.width;
            
                        // Get the actual text's width
                        /* You multiply the unit width of your string by your font size and divide
                         * by the internal scale factor. The division is necessary
                         * for the case where you use units other than 'pt' in the constructor
                         * of jsPDF.
                        */
                        txtWidth = this.getStringUnitWidth(txt)*fontSize/this.internal.scaleFactor;
            
                        // Calculate text's x coordinate
                        x = ( pageWidth - txtWidth ) / 2;
                    }
            
                    // Draw text at x,y
                    this.text(txt,x,y);
                }
            })(jsPDF.API);

            //*******************************************************************************************************************
            //*******************************************************************************************************************
            // CENTRAR TEXTO EN JSPDF (FIN)
            //*******************************************************************************************************************
            //*******************************************************************************************************************

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

            doc.setFontSize(6.5)
            doc.setFontType("bold");
            doc.myText(obj.nombre ,{align: "center"},0,8);
            doc.setFontSize(6.2)
            doc.setFontType("normal");
            
            if( (obj.direccion).length < 67 ){
                doc.text(obj.direccion, 40, 14, {align:"center", maxWidth: 68}) //Centrar y máximo de espaciado
                doc.line(6, 15, 73, 15);

                doc.setFontType("bold")
                doc.setFontSize(8)
                doc.myText('Depósito Web Apuesta Total' ,{align: "center"},0,22);            
                doc.myText('Copia de Cajero' ,{align: "center"},0,26);

            }else if( (obj.direccion).length < 134 ){
                doc.text(obj.direccion, 40, 12, {align:"center", maxWidth: 68}) //Centrar y máximo de espaciado
                doc.line(6, 16, 73, 16)

                doc.setFontType("bold")
                doc.setFontSize(8)
                doc.myText('Depósito Web Apuesta Total' ,{align: "center"},0,23);            
                doc.myText('Copia de Cajero' ,{align: "center"},0,27);

            }else if( (obj.direccion).length < 201 ){
                doc.text(obj.direccion, 40, 12, {align:"center", maxWidth: 68}) //Centrar y máximo de espaciado
                doc.line(6, 18, 73, 18)

                doc.setFontType("bold")
                doc.setFontSize(8)
                doc.myText('Depósito Web Apuesta Total' ,{align: "center"},0,24);            
                doc.myText('Copia de Cajero' ,{align: "center"},0,28);
                
            }else{
                doc.text(obj.direccion, 40, 12, {align:"center", maxWidth: 68}) //Centrar y máximo de espaciado
                doc.line(6, 20.5, 73, 20.5)

                doc.setFontType("bold")
                doc.setFontSize(8)
                doc.myText('Depósito Web Apuesta Total' ,{align: "center"},0,25);            
                doc.myText('Copia de Cajero' ,{align: "center"},0,29);
                
            }

            doc.setFontSize(8)
            doc.setFontType("bold");
            doc.text(6, 33, 'N° de Recibo: ')
            local = doc.setFont()
                .setFontSize(8)
                .setFontType("normal")
                .splitTextToSize(obj.txn_id, 45)
            doc.text(26, 33, local)

            local = doc.setFont()
                .setFontSize(8)
                .setFontType("normal")
                .splitTextToSize(obj.created_at, 45)
            doc.text(42, 33, local)

            if(parseInt(valid_reimpresion)===1){
                doc.setFontType("bold")
                doc.setFontSize(7)
                doc.text(6, 37, 'Reimpresión: ')
                doc.setFontType("normal")
                doc.setFontSize(7)
                doc.text(23, 37, response.fecha_hora_actual)
            }

            doc.setFontType("bold")
            doc.setFontSize(8)
            doc.text(6, 43, 'Datos del cliente:')
            doc.setFontType("normal")
            doc.setFontSize(6.7)
            doc.myText(obj.client_name + ' - ' +obj.client_id, {align: "center"}, 0, 48);

            doc.setFontSize(8)
            doc.setFontType("bold")
            doc.text(6, 56, 'Fundamento y finalidad del recibo:')
            doc.setFontType("normal")
            //doc.text(20, 61, 'Depósito Web en Apuesta Total')
            doc.myText('Depósito Web en Apuesta Total', {align: "center"}, 0, 61);
            doc.line(6, 62, 73, 62)

            

            doc.setFontSize(8)
            doc.setFontType("bold")
            doc.text(6, 70, 'Monto:')
            doc.setFontType("normal")
            doc.text(16, 70, obj.monto + ' PEN')

            doc.setFontSize(8)
            doc.setFontType("bold")
            doc.myText('PARA GANAR HAY QUE CREER', {align: "center"}, 0, 82);

            doc.addPage([80 * cRatio, docHeight * cRatio], "p")

            doc.setFontSize(6.5)
            doc.setFontType("bold");
            doc.myText(obj.nombre ,{align: "center"},0,8);
            doc.setFontSize(6.2)
            doc.setFontType("normal");

            if( (obj.direccion).length < 67 ){
                doc.text(obj.direccion, 40, 14, {align:"center", maxWidth: 68}) //Centrar y máximo de espaciado
                doc.line(6, 15, 73, 15);

                doc.setFontType("bold")
                doc.setFontSize(8)
                doc.myText('Depósito Web Apuesta Total' ,{align: "center"},0,22);            
                doc.myText('Copia de Cliente' ,{align: "center"},0,26);

            }else if( (obj.direccion).length < 134 ){
                doc.text(obj.direccion, 40, 12, {align:"center", maxWidth: 68}) //Centrar y máximo de espaciado
                doc.line(6, 16, 73, 16)

                doc.setFontType("bold")
                doc.setFontSize(8)
                doc.myText('Depósito Web Apuesta Total' ,{align: "center"},0,23);            
                doc.myText('Copia de Cliente' ,{align: "center"},0,27);

            }else if( (obj.direccion).length < 201 ){
                doc.text(obj.direccion, 40, 12, {align:"center", maxWidth: 68}) //Centrar y máximo de espaciado
                doc.line(6, 18, 73, 18)

                doc.setFontType("bold")
                doc.setFontSize(8)
                doc.myText('Depósito Web Apuesta Total' ,{align: "center"},0,24);            
                doc.myText('Copia de Cliente' ,{align: "center"},0,28);
                
            }else{
                doc.text(obj.direccion, 40, 12, {align:"center", maxWidth: 68}) //Centrar y máximo de espaciado
                doc.line(6, 20.5, 73, 20.5)

                doc.setFontType("bold")
                doc.setFontSize(8)
                doc.myText('Depósito Web Apuesta Total' ,{align: "center"},0,25);
                doc.myText('Copia de Cliente' ,{align: "center"},0,29);
                
            }

            doc.setFontSize(8)
            doc.setFontType("bold");
            doc.text(6, 33, 'N° de Recibo: ')
            //doc.text(39, 35, 'Fecha: ')

            local = doc.setFont()
                .setFontSize(8)
                .setFontType("normal")
                .splitTextToSize(obj.txn_id, 45)
            doc.text(26, 33, local)

            local = doc.setFont()
                .setFontSize(8)
                .setFontType("normal")
                .splitTextToSize(obj.created_at, 45)
            doc.text(42, 33, local)

            if(parseInt(valid_reimpresion)===1){
                doc.setFontType("bold")
                doc.setFontSize(7)
                doc.text(6, 37, 'Reimpresión: ')
                doc.setFontType("normal")
                doc.setFontSize(7)
                doc.text(23, 37, response.fecha_hora_actual)
            }

            doc.setFontType("bold")
            doc.setFontSize(8)
            doc.text(6, 43, 'Datos del cliente:')
            doc.setFontType("normal")
            doc.setFontSize(6.7)
            doc.myText(obj.client_name + ' - ' +obj.client_id, {align: "center"}, 0, 48);
            //doc.line(6, 49, 85, 49)

            doc.setFontSize(8)
            doc.setFontType("bold")
            doc.text(6, 56, 'Fundamento y finalidad del recibo:')
            doc.setFontType("normal")
            doc.myText('Depósito Web en Apuesta Total', {align: "center"}, 0, 61);
            doc.line(6, 62, 73, 62)

            doc.setFontSize(8)
            doc.setFontType("bold")
            doc.text(6, 70, 'Monto:')
            doc.setFontType("normal")
            doc.text(16, 70, obj.monto  + ' PEN')

            doc.setFontSize(8)
            doc.setFontType("bold")
            doc.myText('PARA GANAR HAY QUE CREER', {align: "center"}, 0, 82);

            docfin = doc;
            docfin.autoPrint();
            docfin.save('' + obj.txn_id + '.pdf');
            window.open(docfin.output('bloburl'), '_blank');

            loading(false);

        } else {
            console.log(result.error);
        }

    });

}


