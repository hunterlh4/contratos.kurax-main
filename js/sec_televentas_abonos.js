
$(function () {

    if (sec_id == 'televentas_abonos') {

        // Generales
        $('#tls_abono_buscador_fecha_inicio').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#tls_abono_buscador_fecha_fin').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#tls_abono_buscador_fecha_inicio').val(tls_abono_fecha_actual);
        $('#tls_abono_buscador_fecha_fin').val(tls_abono_fecha_actual);

        $('#tls_abono_buscador_fecha_inicio').change(function() {
            var var_fecha_change = $('#tls_abono_buscador_fecha_inicio').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#tls_abono_buscador_fecha_inicio").val(tls_abono_fecha_actual);
            }
        });
        $('#tls_abono_buscador_fecha_fin').change(function() {
            var var_fecha_change = $('#tls_abono_buscador_fecha_fin').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#tls_abono_buscador_fecha_fin").val(tls_abono_fecha_actual);
            }
        });

        $('#tls_abono_buscador_abonador').select2();
        $('#tls_abono_buscador_cuenta_origen').select2();
        $('#tls_abono_buscador_cuenta_destino').select2();

        $('#tls_abono_buscar_btn').click(function(){
            tls_abono_listar_tabla_resumen();
            tls_abono_listar_tabla_detalle();
            return false;
        });

        tls_abono_listar_tabla_resumen();
        tls_abono_listar_tabla_detalle();
        
    }
});

window.addEventListener("paste", function (e) {
	// Handle the event
	retrieveImageFromClipboardAsBlob(e, function (imageBlob) {
		// If there's an image, display it in the canvas
		if (imageBlob) {
			let fileInputElement = undefined;
            if($('#modal_abonos_resumen').is(':visible')){
				console.log('modal_abonos_resumen');
				//fileInputElement = $('#modal_abonos_resumen_imagen');
				fileInputElement = document.getElementById('modal_abonos_resumen_captura_input');
				console.log(fileInputElement);
			}
			if($('#modal_abonos_detalle').is(':visible')){
				console.log('modal_abonos_detalle');
				//fileInputElement = $('#modal_abonos_detalle_imagen');
				fileInputElement = document.getElementById('modal_abonos_detalle_voucher_input');
				console.log(fileInputElement);
			}
			if(typeof fileInputElement !== 'undefined'){
				let container = new DataTransfer();
				let data = imageBlob;
				let img_nombre = new Date().getTime();
				let file = new File([data], img_nombre + ".jpg", {type: "image/jpeg", lastModified: img_nombre});
				container.items.add(file);
				fileInputElement.files = container.files;
			}
		}
	});
}, false);





//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// LISTAR
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

function limpiar_tabla_abonos() {
    $('#tabla_abonos').html(
        '<thead>' +
        '   <tr>' +
        '       <th class="text-center" style="display:none;">Id</th>' +
        '       <th class="text-center">Fecha Operación</th>' +
        '       <th class="text-center">N° Corte</th>' +
        '       <th class="text-center">Registro</th>' +
        '       <th class="text-center">Abonador</th>' +
        '       <th class="text-center">Cuenta</th>' +
        '       <th class="text-center">Monto de Cierre</th>' +
        '       <th class="text-center">Monto de Apertura</th>' +
        '       <th class="text-center">Fondo para Pagos</th>' +
        '       <th class="text-center">Abono de Ventas</th>' +
        '       <th class="text-center">Abono Depositado</th>' +
        '       <th class="text-center">Acciones</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
}

function tls_abono_listar_tabla_resumen() {

    limpiar_tabla_abonos();

    var fecha_inicio = $("#tls_abono_buscador_fecha_inicio").val();
    var fecha_fin = $("#tls_abono_buscador_fecha_fin").val();
    var abonador = $("#tls_abono_buscador_abonador").val();
    var cuenta_origen = $("#tls_abono_buscador_cuenta_origen").val();

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menor a la de fin.', 'warning');
        return false;
    }

    if(abonador == undefined){
        abonador = 0;
    }

    var data = {
        "accion": "listar_abonos_resumen",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "abonador": abonador,
        "cuenta_origen": cuenta_origen
    }

    auditoria_send({ "proceso": "listar_abonos_resumen", "data": data });
    $.ajax({
        url: "/sys/set_televentas_abonos.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            var respuesta = JSON.parse(resp);
            // console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                $('#tabla_abonos tbody').append(
                    '<tr>' +
                    '<td class="text-center" colspan="7">'+ respuesta.status +'</td>' +
                    '</tr>'
                );
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                limpiar_tabla_abonos();
                $.each(respuesta.result, function(index, item) {
                    var variables = "'" + item.cod_transaccion + "','" + item.fecha_operacion + "','" + item.hora_operacion + "','" + item.nro_corte + "','" + item.nombre_imagen + "','" + 
                                    item.cuentas_pago_id + "','" + item.fondo_para_pagos + "','" + item.importe + "','" + item.observacion + "'";

                    var btn_editar = ' <button type="button" class="btn btn-warning" style="padding: 2px 3px;" ' + 
                                    '       onclick="ver_modal_abonos_resumen(' + variables + ')">'+
                                    '   <span class="fa fa-pencil"></span></button> ';

                    var btn_eliminar = ' <button type="button" class="btn btn-danger" style="padding: 2px 3px;" onclick="eliminar_abono( ';
                    btn_eliminar += " '" + item.cod_transaccion + "' ";
                    btn_eliminar += ' )"><span class="fa fa-times"></span></button> ';

                    var style_negativo = "";
                    if ( (item.importe - item.fondo_para_pagos) < 0 ){
                        style_negativo = ' style="color: red;" ';
                    }

                    item.importe = item.importe.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    item.fondo_para_pagos = item.fondo_para_pagos.replace(/\D/g, "")
                                        .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    item.monto_para_abonar = item.monto_para_abonar.replace(/\D/g, "")
                                        .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    item.monto_abonado = item.monto_abonado.replace(/\D/g, "")
                                        .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                    $('#tabla_abonos tbody').append(
                        '<tr id="'+item.id+'" class="sec_tlv_pag_listado_transaccion">' +
                            '<td style="display:none;" class="sec_tlv_pag_listado_transaccion_id_transaccion">' + item.cod_transaccion + '</td>' +
                            '<td class="text-center">' + item.fecha_operacion + ' ' + item.hora_operacion + '</td>' +
                            '<td class="text-center">' + item.nro_corte + '</td>' +
                            '<td class="text-center">' + item.fecha_registro + '</td>' +
                            '<td class="text-center">' + item.usuario + '</td>' +
                            '<td class="text-center">' + item.nombre_banco + '</td>' +
                            '<td class="text-center"> S/ ' + item.importe + '</td>' +  // cierre
                            '<td class="text-center"> S/ ' + item.importe + '</td>' + // monto apertura
                            '<td class="text-center"> S/ ' + item.fondo_para_pagos + '</td>' +  // fondo para pagos
                            '<td class="text-center" ' + style_negativo + ' > S/ ' + item.monto_para_abonar + '</td>' + // abono de ventas
                            '<td class="text-center"> S/ ' + item.monto_abonado + '</td>' + // abono depositado
                            '<td class="text-center">' + btn_editar + btn_eliminar + '</td>' +
                        '</tr>'
                    );
                });

                tabla_validaciones_datatable_formato_tlv_pag('#tabla_abonos');
                return false;
            }
            return false;
        },
        error: function() {}
    });
}

function limpiar_tabla_abonos_detalle() {
    $('#tabla_abonos_detalle').html(
        '<thead>' +
        '   <tr>' +
        '       <th style="display:none;">Id</th>' +
        '       <th>Fecha Operación</th>' +
        '       <th>N° Corte</th>' +
        '       <th>Registro</th>' +
        '       <th>Abonador</th>' +
        '       <th>Cuenta Origen</th>' +
        '       <th>Cuenta Destino</th>' +
        '       <th>N° operación</th>' +
        '       <th>Abono</th>' +
        '       <th>Comisión</th>' +
        '       <th>Acciones</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
}

function tls_abono_listar_tabla_detalle() {

    limpiar_tabla_abonos_detalle();

    var fecha_inicio = $("#tls_abono_buscador_fecha_inicio").val();
    var fecha_fin = $("#tls_abono_buscador_fecha_fin").val();
    var abonador = $("#tls_abono_buscador_abonador").val();
    var cuenta_origen = $("#tls_abono_buscador_cuenta_origen").val();
    var cuenta_destino = $("#tls_abono_buscador_cuenta_destino").val();

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menor a la de fin.', 'warning');
        return false;
    }

    if(abonador == undefined){
        abonador = 0;
    }

    var data = {
        "accion": "listar_abonos_detalle",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "abonador": abonador,
        "cuenta_origen": cuenta_origen,
        "cuenta_destino": cuenta_destino
    }

    auditoria_send({ "proceso": "listar_abonos_detalle", "data": data });
    $.ajax({
        url: "/sys/set_televentas_abonos.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            var respuesta = JSON.parse(resp);
            // console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                $('#tabla_abonos_detalle tbody').append(
                    '<tr>' +
                    '<td class="text-center" colspan="10">'+ respuesta.status +'</td>' +
                    '</tr>'
                );
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                limpiar_tabla_abonos_detalle();
                $.each(respuesta.result, function(index, item) {

                    var variables = "'" + item.cod_transaccion + "','" + item.fecha_operacion + "','" + item.nro_corte_id + "','" + item.nombre_imagen + "','" + 
                                    item.cuentas_pago_id_origen + "','" + item.cuentas_pago_id_destino + "','" + item.nro_operacion + "','" + 
                                    item.importe + "','" + item.comision_id + "','" + item.observacion + "'";

                    var btn_editar = ' <button type="button" class="btn btn-warning" style="padding: 2px 3px;" ' + 
                                    '       onclick="ver_modal_abonos_detalle(' + variables + ')">'+
                                    '   <span class="fa fa-pencil"></span></button> ';

                    var btn_eliminar = ' <button type="button" class="btn btn-danger" style="padding: 2px 3px;" onclick="eliminar_abono_detalle( ';
                    btn_eliminar += " '" + item.cod_transaccion + "' ";
                    btn_eliminar += ' )"><span class="fa fa-times"></span></button> ';

                    item.importe = item.importe.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");


                    $('#tabla_abonos_detalle tbody').append(
                        '<tr id="'+item.cod_transaccion+'" class="sec_tlv_pag_listado_transaccion">' +
                            '<td style="display:none;" class="sec_tlv_pag_listado_transaccion_id_transaccion">' + item.cod_transaccion + '</td>' +
                            '<td class="text-center">' + item.fecha_operacion + '</td>' +
                            '<td class="text-center">' + item.nro_corte_id + '</td>' +
                            '<td class="text-center">' + item.fecha_registro + '</td>' +
                            '<td class="text-center">' + item.usuario + '</td>' +
                            '<td class="text-center">' + item.cuenta_origen + '</td>' +
                            '<td class="text-center">' + item.cuenta_destino + '</td>' +
                            '<td class="text-center">' + item.nro_operacion + '</td>' +
                            '<td class="text-center"> S/ ' + item.importe + '</td>' +
                            '<td class="text-center"> S/ ' + item.comision_id + '</td>' +
                            '<td class="text-center">' + btn_editar + btn_eliminar + '</td>' +
                        '</tr>'
                    );
                });

                tabla_validaciones_datatable_formato_tlv_pag('#tabla_abonos_detalle');
                return false;
            }
        },
        error: function() {}
    });
}



//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// MODAL ABONO RESUMEN
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

$(function () {

    if (sec_id == 'televentas_abonos') {

        $('#modal_abonos_resumen_fecha_operacion').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#modal_abonos_resumen_hora_operacion').datetimepicker({
            format: 'HH:mm' // format: 'LT' - 'HH:mm'
        });
        $('#modal_abonos_resumen_fecha_operacion').val(tls_abono_fecha_actual);
        $('#modal_abonos_resumen_hora_operacion').val(tls_abono_hora_actual);

        $('#modal_abonos_resumen_cuenta').select2();
        $('#modal_abonos_resumen_nro_corte').select2();

        $("#modal_abonos_resumen_importe").on({
            "focus": function (event) {
                $(event.target).select();
                //console.log('focus');
            },
            "blur": function (event) {
                //console.log('keyup');
                if (parseFloat($(event.target).val().replace(/\,/g, '')) > 0) {
                    $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
                    $(event.target).val(function (index, value) {
                        return value.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    });
                } else {
                    $(event.target).val("0.00");
                }
            }
        });

        $("#modal_abonos_resumen_fondo_pago").on({
            "focus": function (event) {
                $(event.target).select();
                //console.log('focus');
            },
            "blur": function (event) {
                //console.log('keyup');
                if (parseFloat($(event.target).val().replace(/\,/g, '')) > 0) {
                    $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
                    $(event.target).val(function (index, value) {
                        return value.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    });
                } else {
                    $(event.target).val("0.00");
                }
            }
        });

        $('#tls_abono_nuevo_resumen').click(function(){
            nuevo_modal_abonos_resumen();
            return false;
        });

        $("#modal_abonos_resumen_cuenta").change(function () {
            $("#modal_abonos_resumen_cuenta option:selected").each(function () {
                cuenta_origen_temp = $(this).val();
                fecha_operacion_temp = $("#modal_abonos_resumen_fecha_operacion").val();
    
                if (cuenta_origen_temp == '22' && fecha_operacion_temp >= '2022-12-12') {
                    $("#modal_abonos_resumen_fondo_pago").prop('disabled', false);
                } else {
                    $("#modal_abonos_resumen_fondo_pago").prop('disabled', true);
                    $("#modal_abonos_resumen_fondo_pago").val(100000).blur();
                }
    
            });
        });

        $('#modal_abonos_resumen_fecha_operacion').on('dp.change', function(e){ 
            $("#modal_abonos_resumen_cuenta option:selected").each(function () {
                cuenta_origen_temp = $(this).val();
                fecha_operacion_temp = $("#modal_abonos_resumen_fecha_operacion").val();
    
                if (cuenta_origen_temp == '22' && fecha_operacion_temp >= '2022-12-12') {
                    $("#modal_abonos_resumen_fondo_pago").prop('disabled', false);
                } else {
                    $("#modal_abonos_resumen_fondo_pago").prop('disabled', true);
                    $("#modal_abonos_resumen_fondo_pago").val(100000).blur();
                }
    
            });
        })
        
    }
});

function nuevo_modal_abonos_resumen(){
    limpiar_campos_modal_abonos_resumen();
    $('#modal_abonos_resumen_img_div').hide();
    $('#modal_abonos_resumen_btn_guardar').removeAttr("onclick");
    $('#modal_abonos_resumen_btn_guardar').attr("onclick", 'guardar_modal_abonos_resumen(0)');
    $('#modal_abonos_detalle_btn_guardar').show();
    $('#modal_abonos_resumen').modal();
}

function limpiar_campos_modal_abonos_resumen(){
    // Limpiar campos
    $('#modal_abonos_resumen_fecha_operacion').val(tls_abono_fecha_actual);
    $('#modal_abonos_resumen_hora_operacion').val(tls_abono_hora_actual);
    $('#modal_abonos_resumen_nro_corte').val("0").trigger('change');
    $('#modal_abonos_resumen_captura_input').val('');
    $('#modal_abonos_resumen_img').removeAttr("src");
    $('#modal_abonos_resumen_cuenta').val(null).trigger('change');
    $('#modal_abonos_resumen_importe').val('');
    $('#modal_abonos_resumen_fondo_pago').val('100,000.00');
    $('#modal_abonos_resumen_imagen').val('');
    $('#modal_abonos_resumen_observacion').val('');
    $('#modal_abonos_resumen_btn_guardar').show();
}

function guardar_modal_abonos_resumen(cod_transaccion){
    //return false;
    // Limpiar borders
    $('#modal_abonos_resumen_fecha_operacion').css('border', '');
    $('#modal_abonos_resumen_hora_operacion').css('border', '');
    $('#modal_abonos_resumen_nro_corte').css('border', '');
    $('#modal_abonos_resumen_cuenta').css('border', '');
    $('#modal_abonos_resumen_importe').css('border', '');
    $('#modal_abonos_resumen_fondo_pago').css('border', '');
    $('#modal_abonos_resumen_imagen').css('border', '');
    $('#modal_abonos_resumen_observacion').css('border', '');
    $('#modal_abonos_resumen_btn_guardar').hide();

    var fecha_operacion = $('#modal_abonos_resumen_fecha_operacion').val();
    var hora_operacion = $('#modal_abonos_resumen_hora_operacion').val();
    var nro_corte = $('#modal_abonos_resumen_nro_corte').val();
    var cuenta_id = $('#modal_abonos_resumen_cuenta').val();
    var importe = $('#modal_abonos_resumen_importe').val().replace(/\,/g, '');
    var fondo_pago = $('#modal_abonos_resumen_fondo_pago').val().replace(/\,/g, '');
    var observacion = $.trim($('textarea#modal_abonos_resumen_observacion').val());
    var imagen = $('#modal_abonos_resumen_captura_input').val();
    var f_imagen = $("#modal_abonos_resumen_captura_input")[0].files[0];
    var imagen_extension = imagen.substring(imagen.lastIndexOf("."));

    var data = new FormData();
    data.append('accion', "guardar_abono_resumen");
    data.append('cod_transaccion', cod_transaccion);

    if (fecha_operacion.length !== 10) {
        $("#modal_abonos_resumen_fecha_operacion").css("border", "1px solid red");
        $('#modal_abonos_resumen_btn_guardar').show();
        swal('Aviso', 'Seleccione una fecha válida.', 'warning');
        return false;
    }
    if (hora_operacion.length !== 5) {
        $("#modal_abonos_resumen_hora_operacion").css("border", "1px solid red");
        $('#modal_abonos_resumen_btn_guardar').show();
        swal('Aviso', 'Seleccione una hora válida.', 'warning');
        return false;
    }
    if (nro_corte == undefined || $.trim(nro_corte) == "" || $.trim(nro_corte) == "0") {
        $("#modal_abonos_resumen_nro_corte").css("border", "1px solid red");
        $('#modal_abonos_resumen_btn_guardar').show();
        swal('Aviso', 'Debe seleccionar el número de corte a utilizar', 'warning');
        return false;
    }
    if(parseInt(cod_transaccion)===0 || imagen.length > 0){
        if (!(imagen.length > 0)) {
            $("#modal_abonos_resumen_imagen").css("border", "1px solid red");
            $('#modal_abonos_resumen_btn_guardar').show();
            swal('Aviso', 'Agregue una imágen.', 'warning');
            return false;
        }
        if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
            imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
            imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG") {
            $("#modal_abonos_resumen_imagen").css("border", "1px solid red");
            $('#modal_abonos_resumen_btn_guardar').show();
            swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
            return false;
        }
        data.append('imagen_voucher', f_imagen);
    }
    if (cuenta_id == undefined || $.trim(cuenta_id) == "") {
        $("#modal_abonos_resumen_cuenta").css("border", "1px solid red");
        $('#modal_abonos_resumen_btn_guardar').show();
        swal('Aviso', 'Debe seleccionar la cuenta a utilizar', 'warning');
        return false;
    }
    if (!(parseFloat(importe) > 0)) {
        $('#modal_abonos_resumen_importe').css('border', '1px solid red');
        $('#modal_abonos_resumen_btn_guardar').show();
        $('#modal_abonos_resumen_importe').focus();
        return false;
    }
    if (( !(parseFloat(fondo_pago) == 100000) ) && (parseInt(cuenta_id) !== 22) ) {
        $('#modal_abonos_resumen_fondo_pago').css('border', '1px solid red');
        $('#modal_abonos_resumen_btn_guardar').show();
        swal('Aviso', 'El monto de Abono para pagos siempre debe ser 100,000.00', 'warning');
        return false;
    }
    if (( !(parseFloat(fondo_pago) == 100000) ) && (parseInt(cuenta_id) === 22 && fecha_operacion < '2022-12-12')) {
        $('#modal_abonos_resumen_fondo_pago').css('border', '1px solid red');
        $('#modal_abonos_resumen_btn_guardar').show();
        swal('Aviso', 'El monto de Abono para pagos en Yape Temporal antes del "12-12-2022" siempre debe ser 100,000.00', 'warning');
        return false;
    }
    if ( (parseInt(cuenta_id) === 22 && fecha_operacion >= '2022-12-12') ) {
        if( parseFloat(fondo_pago) > parseFloat(importe) ){
            $('#modal_abonos_resumen_fondo_pago').css('border', '1px solid red');
            $('#modal_abonos_resumen_btn_guardar').show();
            swal('Aviso', 'El monto de Abono para pagos NO debe ser mayor al importe, en Yape Temporal desde el "12-12-2022".', 'warning');
            return false;
        }
    }

    // Llamado AJAX
    data.append('fecha_operacion', fecha_operacion);
    data.append('hora_operacion', hora_operacion);
    data.append('nro_corte', nro_corte);
    data.append('importe', importe);
    data.append('fondo_pago', fondo_pago);
    data.append('observacion', observacion);
    data.append('cuenta_id', cuenta_id);

    $.ajax({
        url: "/sys/set_televentas_abonos.php",
        type: 'POST',
        data: data,
        processData: false,
        cache: false,
        contentType: false,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            data.delete('imagen_voucher');
            auditoria_send({"proceso": "guardar_abono_resumen_SEND", "data": (Object.fromEntries(data.entries()))});

            var respuesta = JSON.parse(resp);
            auditoria_send({"proceso": "guardar_abono_resumen", "data": respuesta});
            // console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                $('#modal_abonos_resumen_btn_guardar').show();
                $('#modal_abonos_resumen').modal();
                swal('Aviso', respuesta.status, 'warning');
                tls_abono_listar_tabla_resumen();
                tls_abono_listar_tabla_detalle();
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#modal_abonos_resumen').modal('hide');
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                tls_abono_listar_tabla_resumen();
                tls_abono_listar_tabla_detalle();
                return false;
            }
            return false;
        },
        error: function (result) {
            data.delete('imagen_voucher');
            auditoria_send({"proceso": "guardar_abono_resumen_SEND", "data": (Object.fromEntries(data.entries()))});
            auditoria_send({"proceso": "guardar_abono_resumen_error", "data": result});
            return false;
        }
    });
    return false;
}

function ver_modal_abonos_resumen(cod_transaccion, fecha_operacion, hora_operacion, nro_corte, nombre_imagen, cuenta_pago_id, fondo_para_pagos, importe, observacion){
    limpiar_campos_modal_abonos_resumen();
    var hora_operacion = hora_operacion.substring(0, 5); // Retorna hh:ii:ss a hh:ii
    $('#modal_abonos_resumen_img_div').show();
    $('#modal_abonos_resumen_fecha_operacion').val(fecha_operacion);
    $('#modal_abonos_resumen_hora_operacion').val(hora_operacion);
    $('#modal_abonos_resumen_nro_corte').val(nro_corte).trigger('change');
    $('#modal_abonos_resumen_img').attr('src', 'files_bucket/depositos/' + nombre_imagen);
    $('#modal_abonos_resumen_cuenta').val(cuenta_pago_id).trigger('change');
    $('#modal_abonos_resumen_fondo_pago').val(fondo_para_pagos);
    $('#modal_abonos_resumen_importe').val(importe);
    $('#modal_abonos_resumen_observacion').val(observacion);
    $('#modal_abonos_resumen_btn_guardar').removeAttr("onclick");
    $('#modal_abonos_resumen_btn_guardar').attr("onclick", 'guardar_modal_abonos_resumen("'+cod_transaccion+'")');
    $('#modal_abonos_detalle_btn_guardar').show();
    $('#modal_abonos_resumen').modal();
};


function eliminar_abono(abono_id){

    swal({
        title: `<h3>¿Estás seguro de eliminar el abono?</h3>`,
        text: 'Esta acción es irreversible',
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: false,
        closeOnCancel: false,
        html: true,
        // customClass: "sweet_alert_wide",
    },
    function (opt) {
        if (opt) {
            // console.log("SISISI");
            // Llamado AJAX
            var data = new FormData();
            data.append('accion', "eliminar_abono_resumen");
            data.append('abono_id', abono_id);

            $.ajax({
                url: "/sys/set_televentas_abonos.php",
                type: 'POST',
                data: data,
                processData: false,
                cache: false,
                contentType: false,
                beforeSend: function () {
                    loading("true");
                },
                complete: function () {
                    loading();
                },
                success: function (resp) { //  alert(datat)
                    var respuesta = JSON.parse(resp);
                    auditoria_send({"respuesta": "eliminar_abono_resumen", "data": respuesta});
                    console.log(respuesta);
                    if (parseInt(respuesta.http_code) == 400) {
                        swal('Aviso', respuesta.status, 'warning');
                        tls_abono_listar_tabla_resumen();
                        tls_abono_listar_tabla_detalle();
                        return false;
                    }
                    if (parseInt(respuesta.http_code) == 200) {
                        swal('Aviso', 'Su registro de abono a sido eliminado con éxito.', 'success');
                        tls_abono_listar_tabla_resumen();
                        tls_abono_listar_tabla_detalle();
                        return false;
                    }
                    return false;
                },
                error: function (result) {
                    auditoria_send({"respuesta": "eliminar_abono_resumen_ERROR", "data": result});
                    return false;
                }
            });
    
            swal.close();
            loading(false);
            return false;

        } else {
            swal({
                    title: "Estuvo cerca!",
                    text: "Tu registro está a salvo!",
                    type: "success",
                    timer: 1000,
                    closeOnConfirm: true
                },
                function (opt) {
                    swal.close();
                });
        }
    });
}





//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// DETALLE ABONO
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

$(function () {

    if (sec_id == 'televentas_abonos') {

        $('#modal_abonos_detalle_fecha_operacion').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#modal_abonos_detalle_fecha_operacion').val(tls_abono_fecha_actual);

        $('#modal_abonos_detalle_nro_corte').select2();
        $('#modal_abonos_detalle_cuenta_origen').select2();
        $('#modal_abonos_detalle_cuenta_destino').select2();

        $("#modal_abonos_detalle_importe").on({
            "focus": function (event) {
                $(event.target).select();
                //console.log('focus');
            },
            "blur": function (event) {
                //console.log('keyup');
                if (parseFloat($(event.target).val().replace(/\,/g, '')) > 0) {
                    $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
                    $(event.target).val(function (index, value) {
                        return value.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    });
                } else {
                    $(event.target).val("0.00");
                }
            }
        });

        $('#tls_abono_nuevo_detalle').click(function(){
            nuevo_modal_abonos_detalle();
            return false;
        });
    }
});

function nuevo_modal_abonos_detalle(){
    limpiar_campos_modal_abonos_detalle();
    $('#modal_abonos_detalle_img').hide();
    $('#modal_abonos_detalle_btn_guardar').removeAttr("onclick");
    $('#modal_abonos_detalle_btn_guardar').attr("onclick", 'guardar_modal_abono_detalle(0)');
    $('#modal_abonos_detalle_btn_guardar').show();
    $('#modal_abonos_detalle').modal();
}

function limpiar_campos_modal_abonos_detalle(){
    // Limpiar campos
    $('#modal_abonos_detalle_fecha_operacion').val(tls_abono_fecha_actual);
    $('#modal_abonos_detalle_nro_corte').val('0').trigger('change');
    $('#modal_abonos_detalle_voucher_input').val('');
    $('#modal_abonos_detalle_img').removeAttr("src");
    $('#modal_abonos_detalle_cuenta_origen').val(null).trigger('change');
    $('#modal_abonos_detalle_cuenta_destino').val(null).trigger('change');
    $('#modal_abonos_detalle_nro_operacion').val('');
    $('#modal_abonos_detalle_importe').val('0');
    $('#modal_abonos_detalle_comision').val('0.00');
    $('#modal_abonos_detalle_observacion').val('');
    $('#modal_abonos_detalle').show();
}

function guardar_modal_abono_detalle(cod_transaccion){
    // Limpiar borders
    $('#modal_abonos_detalle_fecha_operacion').css('border', '');
    $('#modal_abonos_detalle_nro_corte').css('border', '');
    $('#modal_abonos_detalle_voucher_input').css('border', '');
    $('#modal_abonos_detalle_cuenta_origen').css('border', '');
    $('#modal_abonos_detalle_cuenta_destino').css('border', '');
    $('#modal_abonos_detalle_nro_operacion').css('border', '');
    $('#modal_abonos_detalle_importe').css('border', '');
    $('#modal_abonos_detalle_comision').css('border', '');
    $('#modal_abonos_detalle_observacion').css('border', '');
    $('#modal_abonos_detalle_btn_guardar').hide();

    // Declaracion de variables
    var fecha_operacion = $.trim($('#modal_abonos_detalle_fecha_operacion').val());
    var nro_corte = $('#modal_abonos_detalle_nro_corte').val();
    var cuenta_origen = $('#modal_abonos_detalle_cuenta_origen').val();
    var cuenta_destino = $('#modal_abonos_detalle_cuenta_destino').val();
    var num_operacion = $.trim($('#modal_abonos_detalle_nro_operacion').val());
    var importe = $('#modal_abonos_detalle_importe').val().replace(/\,/g, '');
    var comision_id = $("#modal_abonos_detalle_comision").val();
    var observacion = $.trim($('textarea#modal_abonos_detalle_observacion').val());
    var imagen = $('#modal_abonos_detalle_voucher_input').val();
    var f_imagen = $("#modal_abonos_detalle_voucher_input")[0].files[0];
    var imagen_extension = imagen.substring(imagen.lastIndexOf("."));

    var data = new FormData();
    data.append('accion', "guardar_abono_detalle");
    data.append('cod_transaccion', cod_transaccion);

    if (fecha_operacion.length !== 10) {
        $("#modal_abonos_detalle_fecha_operacion").css("border", "1px solid red");
        $('#modal_abonos_detalle_btn_guardar').show();
        swal('Aviso', 'Seleccione una fecha válida.', 'warning');
        return false;
    }
    if (nro_corte == undefined || $.trim(nro_corte) == "" || $.trim(nro_corte) == "0") {
        $("#modal_abonos_detalle_nro_corte").css("border", "1px solid red");
        $('#modal_abonos_detalle_btn_guardar').show();
        swal('Aviso', 'Debe seleccionar el número de corte a utilizar', 'warning');
        return false;
    }
    if(parseInt(cod_transaccion)===0 || imagen.length > 0){
        if (!(imagen.length > 0)) {
            $("#modal_abonos_detalle_voucher_input").css("border", "1px solid red");
            swal('Aviso', 'Agregue una imágen.', 'warning');
            $('#modal_abonos_detalle_btn_guardar').show();
            return false;
        }
        if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
            imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
            imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG") {
            $("#modal_abonos_detalle_voucher_input").css("border", "1px solid red");
            swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
            $('#modal_abonos_detalle_btn_guardar').show();
            return false;
        }
        data.append('imagen_voucher', f_imagen);
    }

    if (cuenta_origen == undefined || $.trim(cuenta_origen) == "") {
        swal('Aviso', 'Debe seleccionar la cuenta a utilizar', 'warning');
        $('#modal_abonos_detalle_btn_guardar').show();
        return false;
    }
    if (cuenta_destino == undefined || $.trim(cuenta_destino) == "") {
        swal('Aviso', 'Debe seleccionar la cuenta a utilizar', 'warning');
        $('#modal_abonos_detalle_btn_guardar').show();
        return false;
    }
    if(!num_operacion.length > 0){
        $("#modal_abonos_detalle_nro_operacion").css("border", "1px solid red");
        swal('Aviso', 'Agregue un número de operación.', 'warning');
        $('#modal_abonos_detalle_btn_guardar').show();
        return false;
    }
    if (!(parseFloat(importe) > 0)) {
        $('#modal_abonos_detalle_importe').css('border', '1px solid red');
        $('#modal_abonos_detalle_importe').focus();
        $('#modal_abonos_detalle_btn_guardar').show();
        return false;
    }

    data.append('fecha_operacion', fecha_operacion);
    data.append('nro_corte', nro_corte);
    data.append('cuenta_origen', cuenta_origen);
    data.append('cuenta_destino', cuenta_destino);
    data.append('num_operacion', num_operacion);
    data.append('importe', importe);
    data.append('comision_id', comision_id);
    data.append('observacion', observacion);

    $.ajax({
        url: "/sys/set_televentas_abonos.php",
        type: 'POST',
        data: data,
        processData: false,
        cache: false,
        contentType: false,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) {
            data.delete('imagen_voucher');
            auditoria_send({"proceso": "guardar_abono_detalle_SEND", "data": (Object.fromEntries(data.entries()))});

            var respuesta = JSON.parse(resp);
            auditoria_send({"proceso": "guardar_abono_detalle", "data": respuesta});
            // console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                $('#modal_abonos_detalle_btn_guardar').show();
                $('#modal_abonos_detalle').modal();
                swal('Aviso', respuesta.status, 'warning');
                tls_abono_listar_tabla_resumen();
                tls_abono_listar_tabla_detalle();
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#modal_abonos_detalle').modal('hide');
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                tls_abono_listar_tabla_resumen();
                tls_abono_listar_tabla_detalle();
                return false;
            }
            return false;
        },
        error: function (result) {
            data.delete('imagen_voucher');
            auditoria_send({"proceso": "guardar_abono_detalle_SEND", "data": (Object.fromEntries(data.entries()))});
            auditoria_send({"proceso": "guardar_abono_detalle_error", "data": result});
            return false;
        }
    });
    return false;
}

function ver_modal_abonos_detalle(cod_transaccion, fecha_operacion, nro_corte_id, nombre_imagen, cuentas_pago_id_origen, cuentas_pago_id_destino, 
    nro_operacion, importe, comision_id, observacion){
    limpiar_campos_modal_abonos_detalle();
    $('#modal_abonos_detalle_img').show();
    $('#modal_abonos_detalle_fecha_operacion').val(fecha_operacion);
    $('#modal_abonos_detalle_nro_corte').val(nro_corte_id).trigger('change');
    $('#modal_abonos_detalle_img').attr('src', 'files_bucket/depositos/' + nombre_imagen);
    $('#modal_abonos_detalle_cuenta_origen').val(cuentas_pago_id_origen).trigger('change');
    $('#modal_abonos_detalle_cuenta_destino').val(cuentas_pago_id_destino).trigger('change');
    $('#modal_abonos_detalle_nro_operacion').val(nro_operacion);
    $('#modal_abonos_detalle_importe').val(importe);
    $('#modal_abonos_detalle_comision').val(comision_id);
    $('#modal_abonos_detalle_observacion').val(observacion);
    $('#modal_abonos_detalle_btn_guardar').removeAttr("onclick");
    $('#modal_abonos_detalle_btn_guardar').attr("onclick", 'guardar_modal_abono_detalle("'+cod_transaccion+'")');
    $('#modal_abonos_detalle_btn_guardar').show();
    $('#modal_abonos_detalle').modal();
};

function eliminar_abono_detalle(abono_id){

    swal({
        title: `<h3>¿Estás seguro de eliminar el abono?</h3>`,
        text: 'Esta acción es irreversible',
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: false,
        closeOnCancel: false,
        html: true,
        // customClass: "sweet_alert_wide",
    },
    function (opt) {
        if (opt) {
            // console.log("SISISI");
            // Llamado AJAX
            var data = new FormData();
            data.append('accion', "eliminar_abono_detalle");
            data.append('abono_id', abono_id);
        
            auditoria_send({"proceso": "eliminar_abono_detalle_SEND", "data": (Object.fromEntries(data.entries()))});

            $.ajax({
                url: "/sys/set_televentas_abonos.php",
                type: 'POST',
                data: data,
                processData: false,
                cache: false,
                contentType: false,
                beforeSend: function () {
                    loading("true");
                },
                complete: function () {
                    loading();
                },
                success: function (resp) { //  alert(datat)
                    var respuesta = JSON.parse(resp);
                    auditoria_send({"respuesta": "eliminar_abono_detalle", "data": respuesta});
                    console.log(respuesta);
                    if (parseInt(respuesta.http_code) == 400) {
                        swal('Aviso', respuesta.status, 'warning');
                        tls_abono_listar_tabla_resumen();
                        tls_abono_listar_tabla_detalle();
                        return false;
                    }
                    if (parseInt(respuesta.http_code) == 200) {
                        swal('Aviso', 'Su registro de abono a sido eliminado con éxito.', 'success');
                        tls_abono_listar_tabla_resumen();
                        tls_abono_listar_tabla_detalle();
                        return false;
                    }
                    return false;
                },
                error: function (result) {
                    auditoria_send({"respuesta": "eliminar_abono_detalle_ERROR", "data": result});
                    return false;
                }
            });
    
            swal.close();
            loading(false);
            return false;

        } else {
            // console.log("NONONONO");
            swal({
                    title: "Estuvo cerca!",
                    text: "Tu registro está a salvo!",
                    type: "success",
                    timer: 1000,
                    closeOnConfirm: true
                },
                function (opt) {
                    /* if (opt) {
                        auditoria_send({
                            "proceso": "sec_caja_eliminar_stop",
                            "data": save_data
                        });
                    } */
                    // m_reload();
                    swal.close();
                });
        }
    });
}













//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// EXPORTAR
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

function exportar_tabla_abonos_resumen(){

    var fecha_inicio = $("#tls_abono_buscador_fecha_inicio").val();
    var fecha_fin = $("#tls_abono_buscador_fecha_fin").val();
    var abonador = $("#tls_abono_buscador_abonador").val();
    var cuenta_origen = $("#tls_abono_buscador_cuenta_origen").val();

    //console.log(fecha_inicio.replace(/-/g, ""));

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menor a la de fin.', 'warning');
        return false;
    }

    if(abonador == undefined){
        abonador = 0;
    }

    var data = {
        "accion": "listar_transacciones_abonos_resumen_export_xls",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "abonador": abonador,
        "cuenta_origen": cuenta_origen
    }
    $.ajax({
        url: "/sys/set_televentas_abonos.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            let obj = JSON.parse(resp);
            window.open(obj.path);
            loading(false);
        },
        error: function() {}
    });
}

function exportar_tabla_abonos_detalle(){

    var fecha_inicio = $("#tls_abono_buscador_fecha_inicio").val();
    var fecha_fin = $("#tls_abono_buscador_fecha_fin").val();
    var abonador = $("#tls_abono_buscador_abonador").val();
    var cuenta_origen = $("#tls_abono_buscador_cuenta_origen").val();
    var cuenta_destino = $("#tls_abono_buscador_cuenta_destino").val();

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menor a la de fin.', 'warning');
        return false;
    }

    if(abonador == undefined){
        abonador = 0;
    }

    var data = {
        "accion": "listar_transacciones_abonos_detalle_export_xls",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "abonador": abonador,
        "cuenta_origen": cuenta_origen,
        "cuenta_destino": cuenta_destino
    }

    $.ajax({
        url: "/sys/set_televentas_abonos.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            let obj = JSON.parse(resp);
            window.open(obj.path);
            loading(false);
        },
        error: function() {}
    });
}





//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
// EXTRAS
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

function tabla_validaciones_datatable_formato_tlv_pag(id) {
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
        
    }
    $(id).DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'info': true,
        'autoWidth': false,
        "language": {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "infoPostFix": "",
            "search": "Buscar: ",
            "url": "",
            "infoThousands": ",",
            "loadingRecords": "Cargando...",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
}
