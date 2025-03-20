
$(function () {

    if (sec_id == 'televentas_abonos_pendientes') {
        // Generales
        var fecha_caj_val = false;
        if( cajero_validador == 1 ){
            fecha_caj_val = tls_abono_pendiente_2dias_anterior
        }

        if(tls_abono_pendiente_permiso_consulta == 0){
            $('#tls_abono_pendiente_buscador_fecha_inicio').datetimepicker({
                format: 'YYYY-MM-DD',
                minDate: tls_abono_pendiente_fecha_anterior
            });
        }else{
            $('#tls_abono_pendiente_buscador_fecha_inicio').datetimepicker({
                format: 'YYYY-MM-DD',
                minDate: fecha_caj_val
            });
        }
        
        $('#tls_abono_pendiente_buscador_fecha_fin').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#tls_abono_pendiente_buscador_fecha_inicio').val(tls_abono_pendiente_fecha_actual);
        $('#tls_abono_pendiente_buscador_fecha_fin').val(tls_abono_pendiente_fecha_actual);

        $('#tls_abono_pendiente_buscador_fecha_inicio').change(function() {
            var var_fecha_change = $('#tls_abono_pendiente_buscador_fecha_inicio').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#tls_abono_pendiente_buscador_fecha_inicio").val(tls_abono_pendiente_fecha_actual);
            }
        });
        $('#tls_abono_pendiente_buscador_fecha_fin').change(function() {
            var var_fecha_change = $('#tls_abono_pendiente_buscador_fecha_fin').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#tls_abono_pendiente_buscador_fecha_fin").val(tls_abono_pendiente_fecha_actual);
            }
        });

        $('#tls_abono_pendiente_buscador_banco').select2();
        $("#tls_abono_pendiente_buscador_estado").val("1").attr("selected", true);
        if ( permiso_vista_supervisor == false && permiso_vista_usuario == false ){
            $("#tls_abono_pendiente_buscador_estado").val("4").attr("selected", true);
        }
        $('#tls_abono_pendiente_buscador_estado').select2({ minimumResultsForSearch: -1 }); // Quita el search, del select2()

        $('#tls_abono_pendiente_buscar_btn').click(function(){
            tls_abonos_pendientes_listar();
            return false;
        });

        $("#modal_abonos_pendientes_banco").change(function () {
            $("#modal_abonos_pendientes_banco option:selected").each(function () {
                banco_id_temp = parseInt($(this).val());
                // fecha_operacion_temp = $("#modal_abonos_resumen_fecha_operacion").val();
                
                if ( $(this).val() == undefined || $(this).val() == ""){
                    //console.log("NOOOOO YAPE  ----  banco no seleccionado");
                    $("#modal_abonos_pendientes_nro_operacion").val("");
                    $("#modal_abonos_pendientes_nro_operacion").attr('disabled', true);
                    $("#modal_abonos_pendientes_nro_operacion").attr("placeholder","Seleccionar un banco");
                }
                /* else if ( !(banco_id_temp === 3 || banco_id_temp === 4 || banco_id_temp === 14 || banco_id_temp === 15 || banco_id_temp === 22) ) {
                    console.log("NOOOOO YAPE");
                    $("#modal_abonos_pendientes_nro_operacion").val("");
                    $("#modal_abonos_pendientes_nro_operacion").attr('disabled', false);
                    $("#modal_abonos_pendientes_nro_operacion").removeAttr("placeholder");
                    $("#modal_abonos_pendientes_nro_operacion").attr("oninput","this.value=this.value.replace(/[^0-9]/g,'');");
                    if( banco_id_temp === 1 || banco_id_temp === 2 || banco_id_temp === 9 || banco_id_temp === 10 || banco_id_temp === 11 ||
                        banco_id_temp === 12 || banco_id_temp === 17 || banco_id_temp === 18 || banco_id_temp === 20 || banco_id_temp === 21 ){
                        console.log("no es yape, pero es Interbank o BBVA");
                        $("#modal_abonos_pendientes_nro_operacion").val("");
                        // $("#modal_abonos_pendientes_nro_operacion").attr('disabled', false);
                        $("#modal_abonos_pendientes_nro_operacion").removeAttr("placeholder");
                        $("#modal_abonos_pendientes_nro_operacion").removeAttr("oninput");
                        $("#modal_abonos_pendientes_nro_operacion").attr("oninput","this.value=this.value.replace(/[^0-9+]/g,'');");
                    } */
                else if( banco_id_temp === 1 || banco_id_temp === 2 || banco_id_temp === 9 || banco_id_temp === 10 || banco_id_temp === 11 ||
                        banco_id_temp === 12 || banco_id_temp === 17 || banco_id_temp === 18 || banco_id_temp === 20 || banco_id_temp === 21 ) {
                    //console.log(" es Interbank o BBVA");
                    $("#modal_abonos_pendientes_nro_operacion").val("");
                    $("#modal_abonos_pendientes_nro_operacion").attr('disabled', false);
                    $("#modal_abonos_pendientes_nro_operacion").removeAttr("placeholder");
                    $("#modal_abonos_pendientes_nro_operacion").attr("onKeyUp","pierdeFoco(this)");
                    $("#modal_abonos_pendientes_nro_operacion").attr("oninput","this.value=this.value.replace(/[^0-9+]/g,'');");
                } else {
                    //console.log("YAPE y otros");
                    $("#modal_abonos_pendientes_nro_operacion").val("");
                    $("#modal_abonos_pendientes_nro_operacion").attr('disabled', false);
                    $("#modal_abonos_pendientes_nro_operacion").removeAttr("placeholder");
                    $("#modal_abonos_pendientes_nro_operacion").removeAttr("onKeyUp");
                    $("#modal_abonos_pendientes_nro_operacion").removeAttr("oninput");
                }
    
            });
        });

        tls_abonos_pendientes_listar();
        
    }
});

/* INICIO -- Funcion para que no acepte ceros a la izquierda */
function pierdeFoco(e){
    var valor = e.value.replace(/^0*/, '');
    e.value = valor;
}
/* FIN -- funcion para que no acepte ceros a la izquierda */

window.addEventListener("paste", function (e) {
    // Handle the event
    retrieveImageFromClipboardAsBlob(e, function (imageBlob) {
        // If there's an image, display it in the canvas
        if (imageBlob) {
            let fileInputElement = undefined;
            if($('#modal_abonos_pendientes').is(':visible')){
                console.log('modal_abonos_pendientes');
                //fileInputElement = $('#modal_abonos_pendientes_imagen');
                fileInputElement = document.getElementById('modal_abonos_pendientes_voucher_input');
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

function limpiar_tabla_abonos_pendientes() {
    $('#tabla_abonos_pendientes').html(
        '<thead>' +
        '   <tr>' +
        '       <th class="text-center" style="display:none;">Id</th>' +
        '       <th class="text-center">Fecha Operación</th>' +
        '       <th class="text-center">Registro</th>' +
        '       <th class="text-center">Usuario</th>' +
        '       <th class="text-center">Cliente</th>' +
        '       <th class="text-center">Usuario validador</th>' +
        '       <th class="text-center">Fecha validación</th>' +
        '       <th class="text-center">N° operación</th>' +
        '       <th class="text-center">Banco</th>' +
        '       <th class="text-center">Monto</th>' +
        '       <th class="text-center">Comisión</th>' +
        '       <th class="text-center">Medio</th>' +
        '       <th class="text-center">Estado</th>' +
        '       <th class="text-center">Acciones</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
}

function tls_abonos_pendientes_listar() {

    var monto_total = 0;
    var comision_total = 0;
    $('#secTLS_abonos_pendientes_monto_total').val('0.00');
    $('#secTLS_abonos_pendientes_cant').val('0');
    $('#secTLS_abonos_pendientes_comision_total').val('0.00');
    limpiar_tabla_abonos_pendientes();

    var fecha_inicio = $("#tls_abono_pendiente_buscador_fecha_inicio").val();
    var fecha_fin = $("#tls_abono_pendiente_buscador_fecha_fin").val();
    var banco = $("#tls_abono_pendiente_buscador_banco").val();
    var estado = $("#tls_abono_pendiente_buscador_estado").val();
    var nro_operacion = $.trim($("#tls_abono_pendiente_buscador_numero_operacion").val());
    
    if ( parseInt(estado) === 4 ) {
        swal('Aviso', 'Por favor asignar permisos de vista.', 'warning');
        return false;
    }
    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menor a la de fin.', 'warning');
        return false;
    }  

    var data = {
        "accion": "listar_abonos",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "banco": banco,
        "estado": estado,
        "nro_operacion": nro_operacion
    }

    auditoria_send({ "proceso": "listar_abonos", "data": data });
    $.ajax({
        url: "/sys/set_televentas_abonos_pendientes.php",
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
                $('#tabla_abonos_pendientes tbody').append(
                    '<tr>' +
                    '<td class="text-center" colspan="13">'+ respuesta.status +'</td>' +
                    '</tr>'
                );
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                limpiar_tabla_abonos_pendientes();
                $.each(respuesta.result, function(index, item) {
                    var variables = "'" + item.cod_transaccion + "','" + item.fecha_operacion + "','" + item.hora_operacion + "','" + item.cliente_id + "','" + item.cliente + "','" + item.medio_id + "','" + item.nombre_imagen + "','" + item.banco_id + "','" + item.nro_operacion + "','" + item.monto + "','" + item.comision_id + "','" + item.observacion + "'";

                    monto_total = parseFloat(monto_total) + parseFloat(item.monto);
                    comision_total = parseFloat(comision_total) + parseFloat(item.comision_id);

                    item.monto = item.monto.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    
                    var btn_eliminar = "";
                    var btn_editar = "";
                    var btn_cancelar = "";
                    var btn_ver_detalle_validado = "";
                    var nombre_estado_abono = "";

                    if(parseInt(item.estado_abono) === 1 ){
                        nombre_estado_abono = "PENDIENTE";
                        
                        if ( item.origen_abono_pendiente == 'TLS' ){
                            btn_eliminar = ' <button type="button" data-toggle="tooltip" data-placement="bottom" title="Eliminar" class="btn btn-danger" style="padding: 2px 3px;" onclick="eliminar_abono_pendiente( ';
                            btn_eliminar += " '" + item.cod_transaccion + "' ";
                            btn_eliminar += ' )"><span class="fa fa-times"></span></button> ';

                            if(parseInt(permiso_editar) === 1 ){
                                btn_editar = ' <button type="button" data-toggle="tooltip" data-placement="bottom" title="Editar" class="btn btn-warning" style="padding: 2px 3px;" ' ;
                                btn_editar += ' onclick="ver_modal_abono_pendiente(' + variables + ')">' ;
                                btn_editar += '   <span class="fa fa-pencil"></span></button> ';
                            }
                        } else {
                            // Codigo para CANCELLAR YAPE
                            btn_cancelar = ' <button type="button" data-toggle="tooltip" data-placement="bottom" title="Cancelar" class="btn btn-danger" style="padding: 2px 3px;" onclick="cancelar_yape_pendiente( ';
                            btn_cancelar += " '" + item.cod_transaccion + "' ";
                            btn_cancelar += ' )"><span class="fa fa-ban"></span></button> ';
                        }
                    } else if ( parseInt(item.estado_abono) === 2 ) {
                        nombre_estado_abono = "VALIDADO";
                        btn_ver_detalle_validado = ' <button type="button" data-toggle="tooltip" data-placement="bottom" title="Ver Detalle" class="btn btn-info" style="padding: 2px 3px;" onclick="ver_detalle_abono_validado( ';
                        btn_ver_detalle_validado += "'" + item.cod_transaccion + "', '" + item.origen_abono_pendiente + "'";
                        btn_ver_detalle_validado += ' )"><span class="fa fa-eye"></span></button> ';
                    } else if ( parseInt(item.estado_abono) === 3 ) {
                        nombre_estado_abono = "CANCELADO";
                    }

                    $('#tabla_abonos_pendientes tbody').append(
                        '<tr id="'+item.id+'" class="sec_tlv_pag_listado_transaccion">' +
                            '<td style="display:none;" class="sec_tlv_pag_listado_transaccion_id_transaccion">' + item.cod_transaccion + '</td>' +
                            '<td class="text-center">' + item.fecha_operacion + ' ' + item.hora_operacion + '</td>' +
                            '<td class="text-center">' + item.fecha_registro + '</td>' +
                            '<td class="text-center">' + item.usuario + '</td>' +
                            '<td class="text-center" style="font-size: 20px; font-weight: bold;">' + item.cliente + '</td>' +
                            '<td class="text-center">' + item.usuario_validador + '</td>' +
                            '<td class="text-center">' + item.fecha_validacion + '</td>' +
                            '<td class="text-center" style="font-size: 20px; font-weight: bold;">' + item.nro_operacion + '</td>' +
                            '<td class="text-center">' + item.nombre_banco + '</td>' +
                            '<td class="text-center" style="font-size: 20px; font-weight: bold;"> S/ ' + item.monto + '</td>' + 
                            '<td class="text-center"> S/ ' + item.comision_id + '</td>' +
                            '<td class="text-center">' + item.nombre_medio + '</td>' +
                            '<td class="text-center">' + nombre_estado_abono + '</td>' +
                            '<td class="text-center">' + btn_ver_detalle_validado + btn_editar + btn_eliminar + btn_cancelar + '</td>' +
                        '</tr>'
                    );
                });
                monto_total = (monto_total.toFixed(2)).replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                comision_total = (comision_total.toFixed(2)).replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                $('#secTLS_abonos_pendientes_monto_total').val(monto_total);
                $('#secTLS_abonos_pendientes_cant').val(respuesta.cantidad);
                $('#secTLS_abonos_pendientes_comision_total').val(comision_total);

                tabla_validaciones_datatable_formato_tlv_abonos_pendientes('#tabla_abonos_pendientes');
                return false;
            }
            return false;
        },
        error: function() {}
    });
}

function eliminar_abono_pendiente(abono_pendiente_id){

    swal({
        title: `<h3>¿Estás seguro de eliminar el abono pendiente?</h3>`,
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
            data.append('accion', "eliminar_abono_pendiente");
            data.append('abono_pendiente_id', abono_pendiente_id);

            $.ajax({
                url: "/sys/set_televentas_abonos_pendientes.php",
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
                    auditoria_send({"respuesta": "eliminar_abono_pendiente", "data": respuesta});
                    console.log(respuesta);
                    if (parseInt(respuesta.http_code) == 400) {
                        swal('Aviso', respuesta.status, 'warning');
                        tls_abonos_pendientes_listar();
                        return false;
                    }
                    if (parseInt(respuesta.http_code) == 200) {
                        swal('Aviso', 'Su registro de abono a sido eliminado con éxito.', 'success');
                        tls_abonos_pendientes_listar();
                        return false;
                    }
                    return false;
                },
                error: function (result) {
                    auditoria_send({"respuesta": "eliminar_abono_pendiente_ERROR", "data": result});
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

function cancelar_yape_pendiente(yape_pendiente_id){

    swal({
        title: `<h3>¿Estás seguro que desea cancelar un Yape Pendiente - APK?</h3>`,
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
            data.append('accion', "cancelar_yape_pendiente");
            data.append('yape_pendiente_id', yape_pendiente_id);

            $.ajax({
                url: "/sys/set_televentas_abonos_pendientes.php",
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
                    auditoria_send({"respuesta": "cancelar_yape_pendiente", "data": respuesta});
                    console.log(respuesta);
                    if (parseInt(respuesta.http_code) == 400) {
                        swal('Aviso', respuesta.status, 'warning');
                        tls_abonos_pendientes_listar();
                        return false;
                    }
                    if (parseInt(respuesta.http_code) == 200) {
                        swal('Aviso', respuesta.result, 'success');
                        tls_abonos_pendientes_listar();
                        return false;
                    }
                    return false;
                },
                error: function (result) {
                    auditoria_send({"respuesta": "cancelar_yape_pendiente_ERROR", "data": result});
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
// MODAL REGISTRAR NUEVO ABONO PENDIENTE
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************

var cliente_id_tls_abonos_pendientes=0;
$(function () {
    
    if (sec_id == 'televentas_abonos_pendientes') {

        $('#modal_abonos_pendientes_fecha_operacion').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#modal_abonos_pendientes_hora_operacion').datetimepicker({
            format: 'HH:mm' // format: 'LT' - 'HH:mm'
        });
        $('#modal_abonos_pendientes_fecha_operacion').val(tls_abono_pendiente_fecha_actual);
        $('#modal_abonos_pendientes_hora_operacion').val(tls_abono_pendiente_hora_actual);

        $('#modal_abonos_pendientes_medio').select2({ minimumResultsForSearch: -1 }); // Quita el search, del select2()
        $('#modal_abonos_pendientes_banco').select2();
        $('#modal_abonos_pendientes_comision').select2();

        $("#modal_abonos_pendientes_monto").on({
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

        $('#tls_abono_pendiente_nuevo').click(function(){
            nuevo_modal_abono_pendiente();
            return false;
        });

        // $( "#modal_abonos_pendientes_nombre" ).keyup(autocompleteClienteTLS() );
        $('#modal_abonos_pendientes_nombre').autocomplete({
            source: '/sys/set_televentas_abonos_pendientes.php?action=buscar',
            minLength: 3,
            select: function (event, ui)
            {
                console.log('cliente_id_tls_abonos_pendientes: '+ui.item.codigo);
                cliente_id_tls_abonos_pendientes=ui.item.codigo;
            }
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            $('ul.ui-menu').css('display', 'block !important');
            $('ul.ui-menu').css('z-index', '100000');
            return $( "<li>" )
            .append( item.label.toUpperCase() )
            .appendTo( ul );
        };
    }
});

function nuevo_modal_abono_pendiente(){
    limpiar_campos_modal_abonos_pendientes();
    $('#modal_abonos_pendientes_img_div').hide();
    $('#modal_abonos_pendientes_btn_guardar').removeAttr("onclick");
    $('#modal_abonos_pendientes_btn_guardar').attr("onclick", 'guardar_modal_abonos_pendientes(0)');
    gen_permitir_insertar = 0;
    $('#modal_abonos_pendientes').modal();
}

function limpiar_campos_modal_abonos_pendientes(){
    // Limpiar campos
    cliente_id_tls_abonos_pendientes = 0;
    $('#modal_abonos_pendientes_img_div').hide();
    $('#modal_abonos_pendientes_nombre').val('');
    $('#modal_abonos_pendientes_fecha_operacion').val(tls_abono_pendiente_fecha_actual);
    $('#modal_abonos_pendientes_hora_operacion').val(tls_abono_pendiente_hora_actual);
    $('#modal_abonos_pendientes_voucher_input').val('');
    $('#modal_abonos_pendientes_img').removeAttr("src");
    $('#modal_abonos_pendientes_medio').val("0").trigger('change');
    $('#modal_abonos_pendientes_banco').val(null).trigger('change');
    $('#modal_abonos_pendientes_nro_operacion').val('');
    $('#modal_abonos_pendientes_comision').val('0.00').trigger('change');
    $('#modal_abonos_pendientes_monto').val('0.00');
    $('#modal_abonos_pendientes_observacion').val('');
    $('#modal_abonos_pendientes_btn_guardar').show();

    $('#modal_abonos_pendientes_btn_guardar').removeClass('btn-warning');
    $('#modal_abonos_pendientes_btn_guardar').addClass('btn-success');
}

function ver_modal_abono_pendiente(cod_transaccion, fecha_operacion, hora_operacion, cliente_id, cliente, nombre_medio, nombre_imagen, banco_id, nro_operacion, monto, comision_id, observacion){
    limpiar_campos_modal_abonos_pendientes();
    cliente_id_tls_abonos_pendientes = parseInt(cliente_id);
    $('#modal_abonos_pendientes_nombre').val(cliente);
    if( $('#modal_abonos_pendientes_nombre').val().length == 0 ){
        cliente_id_tls_abonos_pendientes = 0;
    }
    $('#modal_abonos_pendientes_medio').val(nombre_medio).trigger('change');
    $('#modal_abonos_pendientes_fecha_operacion').val(fecha_operacion);
    $('#modal_abonos_pendientes_hora_operacion').val(hora_operacion);
    if (nombre_imagen.length > 10) {
        $('#modal_abonos_pendientes_img_div').show();
        $('#modal_abonos_pendientes_img').attr('src', 'files_bucket/depositos/' + nombre_imagen);
    }
    $('#modal_abonos_pendientes_banco').val(banco_id).trigger('change');
    $('#modal_abonos_pendientes_nro_operacion').val(nro_operacion);
    $('#modal_abonos_pendientes_monto').val(monto);
    $('#modal_abonos_pendientes_comision').val(comision_id).trigger('change');
    $('#modal_abonos_pendientes_observacion').val(observacion);
    $('#modal_abonos_pendientes_btn_guardar').removeAttr("onclick");
    $('#modal_abonos_pendientes_btn_guardar').attr("onclick", 'guardar_modal_abonos_pendientes("'+cod_transaccion+'")');
    $('#modal_abonos_pendientes_btn_guardar').show();
    $('#modal_abonos_pendientes').modal();
};
gen_permitir_insertar = 0;
function guardar_modal_abonos_pendientes(cod_abono_pendiente){
    var permitir_dupl = 0;
    if(gen_permitir_insertar == 1){
        permitir_dupl = 1;
    }else{
        permitir_dupl = 0;
    }
    // Limpiar borders
    $('#modal_abonos_pendientes_nombre').css('border', '');
    $('#modal_abonos_pendientes_fecha_operacion').css('border', '');
    $('#modal_abonos_pendientes_hora_operacion').css('border', '');
    $('#modal_abonos_pendientes_voucher_input').css('border', '');
    $('#modal_abonos_pendientes_medio').css('border', '');
    $('#modal_abonos_pendientes_banco').css('border', '');
    $('#modal_abonos_pendientes_nro_operacion').css('border', '');
    $('#modal_abonos_pendientes_comision').css('border', '');
    $('#modal_abonos_pendientes_monto').css('border', '');
    $('#modal_abonos_pendientes_observacion').css('border', '');
    $('#modal_abonos_pendientes_btn_guardar').hide();

    var fecha_operacion = $('#modal_abonos_pendientes_fecha_operacion').val();
    var hora_operacion = $('#modal_abonos_pendientes_hora_operacion').val();
    var medio_id = $('#modal_abonos_pendientes_medio').val();
    var banco_id = $('#modal_abonos_pendientes_banco').val();
    var is_valid_yape_banco = $('#modal_abonos_pendientes_banco option:selected').attr('is_yape');
    var nro_operacion = $.trim($('#modal_abonos_pendientes_nro_operacion').val());
    var monto = $('#modal_abonos_pendientes_monto').val().replace(/\,/g, '');
    var comision_id = $('#modal_abonos_pendientes_comision').val().replace(/\,/g, '');
    var observacion = $.trim($('textarea#modal_abonos_pendientes_observacion').val());
    var imagen = $('#modal_abonos_pendientes_voucher_input').val();
    var f_imagen = $("#modal_abonos_pendientes_voucher_input")[0].files[0];
    var imagen_extension = imagen.substring(imagen.lastIndexOf("."));

    var data = new FormData();
    data.append('accion', "guardar_abono_pendiente");
    data.append('cod_abono_pendiente', cod_abono_pendiente);

    if(cliente_id_tls_abonos_pendientes == undefined){
        cliente_id_tls_abonos_pendientes = 0;
    }
    if( $('#modal_abonos_pendientes_nombre').val().length == 0 ){
        cliente_id_tls_abonos_pendientes = 0;
    }
    if ((cliente_id_tls_abonos_pendientes == 0 && $('#modal_abonos_pendientes_nombre').val().length > 0 ) ) {

        $("#modal_abonos_pendientes_nombre").css("border", "1px solid red");
        $('#modal_abonos_pendientes_btn_guardar').show();
        swal('Aviso', 'Por favor registre una opción válida en el nombre del cliente o deje el campo nombre de cliente en blanco.', 'warning');
        return false;
    }
    if (fecha_operacion.length !== 10) {
        $("#modal_abonos_pendientes_fecha_operacion").css("border", "1px solid red");
        $('#modal_abonos_pendientes_btn_guardar').show();
        swal('Aviso', 'Seleccione una fecha válida.', 'warning');
        return false;
    }
    if (hora_operacion.length !== 5) {
        $("#modal_abonos_pendientes_hora_operacion").css("border", "1px solid red");
        $('#modal_abonos_pendientes_btn_guardar').show();
        swal('Aviso', 'Seleccione una hora válida.', 'warning');
        return false;
    }
    if (medio_id == undefined || $.trim(medio_id) == "" || $.trim(medio_id) == "0") {
        $("#modal_abonos_pendientes_medio").css("border", "1px solid red");
        $('#modal_abonos_pendientes_btn_guardar').show();
        swal('Aviso', 'Debe seleccionar un medio válido', 'warning');
        return false;
    }
    if(!nro_operacion.length > 0){
        $("#modal_abonos_pendientes_nro_operacion").css("border", "1px solid red");
        $('#modal_abonos_pendientes_btn_guardar').show();
        swal('Aviso', 'Agregue un número de operación.', 'warning');
        return false;
    }
    if (!(parseFloat(monto) > 0)) {
        $('#modal_abonos_pendientes_monto').css('border', '1px solid red');
        $('#modal_abonos_pendientes_btn_guardar').show();
        $('#modal_abonos_pendientes_monto').focus();
        swal('Aviso', 'Agregue el monto.', 'warning');
        return false;
    }
    /* if (!(imagen.length > 0)) {
        $("#modal_abonos_pendientes_voucher_input").css("border", "1px solid red");
        $('#modal_abonos_pendientes_btn_guardar').show();
        swal('Aviso', 'Agregue una imágen.', 'warning');
        return false;
    } */
    if ((imagen.length > 0)) {
        if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
            imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
            imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG") {
            $("#modal_abonos_pendientes_voucher_input").css("border", "1px solid red");
            $('#modal_abonos_pendientes_btn_guardar').show();
            swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
            return false;
        }
    }
    if (banco_id == undefined || $.trim(banco_id) == "") {
        $("#modal_abonos_pendientes_banco").css("border", "1px solid red");
        $('#modal_abonos_pendientes_btn_guardar').show();
        swal('Aviso', 'Debe seleccionar un banco', 'warning');
        return false;
    }

    // Llamado AJAX
    data.append('cliente_id_tls_abonos_pendientes', cliente_id_tls_abonos_pendientes);
    data.append('fecha_operacion', fecha_operacion);
    data.append('hora_operacion', hora_operacion);
    data.append('medio_id', medio_id);
    data.append('nro_operacion', nro_operacion);
    data.append('monto', monto);
    data.append('comision_id', comision_id);
    data.append('observacion', observacion);
    data.append('banco_id', banco_id);
    data.append('is_valid_yape_banco', is_valid_yape_banco);
    data.append('imagen_voucher', f_imagen);
    data.append('permitir_dupl', permitir_dupl);

    $.ajax({
        url: "/sys/set_televentas_abonos_pendientes.php",
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
            auditoria_send({"proceso": "guardar_abono_pendiente_SEND", "data": (Object.fromEntries(data.entries()))});

            var respuesta = JSON.parse(resp);
            auditoria_send({"proceso": "guardar_abono_pendiente", "data": respuesta});
            console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                if(respuesta.question_yape == 1){
                    swal({
                        html : true,
                        title : respuesta.status,
                        text: "Si lo desea, puede agregar algún caracter al número de operación.",
                        type : 'warning',
                        showCancelButton : true,
                        cancelButtonColor : '#d33',
                        cancelButtonText: 'NO',
                        confirmButtonColor : '#0336FF',
                        confirmButtonText : 'SI',
                        closeOnConfirm : false
                    }, function(){
                        gen_permitir_insertar = 1;
                        $("#modal_abonos_pendientes_nro_operacion").css("border", "1px solid red");
                        $('#modal_abonos_pendientes_nro_operacion').focus();
                        $('#modal_abonos_pendientes_btn_guardar').show();
                        $('#modal_abonos_pendientes_btn_guardar').removeClass('btn-success');
                        $('#modal_abonos_pendientes_btn_guardar').addClass('btn-warning');
                        swal.close();
                    });
                }else{
                    $('#modal_abonos_pendientes_btn_guardar').show();
                    $('#modal_abonos_pendientes').modal();
                    swal('Aviso', respuesta.status, 'warning');
                    tls_abonos_pendientes_listar();
                }
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                if(permitir_dupl == 1){
                    gen_permitir_insertar = 0; //reiniciar
                }
                $('#modal_abonos_pendientes').modal('hide');
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                tls_abonos_pendientes_listar();
                return false;
            }
            return false;
        },
        error: function (result) {
            data.delete('imagen_voucher');
            auditoria_send({"proceso": "guardar_abono_pendiente_SEND", "data": (Object.fromEntries(data.entries()))});
            auditoria_send({"proceso": "guardar_abono_pendiente_error", "data": result});
            return false;
        }
    });
    return false;
}

function ver_detalle_abono_validado(cod_transaccion,origen){
    var currentDomain = window.location.origin;
    var url = currentDomain + "/?&sec_id=televentas_depositos&abono_id=" + cod_transaccion + "&origen=" + origen;
    window.open(url, "_blank");
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

function exportar_tabla_abonos_pendientes_validados(){

    var fecha_inicio = $("#tls_abono_pendiente_buscador_fecha_inicio").val();
    var fecha_fin = $("#tls_abono_pendiente_buscador_fecha_fin").val();
    var banco = $("#tls_abono_pendiente_buscador_banco").val();
    var estado = $("#tls_abono_pendiente_buscador_estado").val();
    var nro_operacion = $.trim($("#tls_abono_pendiente_buscador_numero_operacion").val());

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menor a la de fin.', 'warning');
        return false;
    }

    var data = {
        "accion": "listar_abonos_export_xls",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "banco": banco,
        "estado": estado,
        "nro_operacion": nro_operacion
    }

    $.ajax({
        url: "/sys/set_televentas_abonos_pendientes.php",
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

function tabla_validaciones_datatable_formato_tlv_abonos_pendientes(id) {
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
        
    }
    $(id).DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': busqueda_datatable,
        'ordering': true,
        'order': [],
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

$('#modal_abonos_pendientes_nro_operacion').keyup(function(e){
    var banco_selected = $('#modal_abonos_pendientes_banco option:selected').attr('is_yape');
    var num_operacion = $('#modal_abonos_pendientes_nro_operacion').val();
    if(banco_selected == 0){ // Si no es yape
        //Validar numero de operación, que no contenga 0 a la izquierda
        num_operacion = num_operacion.replace(/^(0+)/g, '');
        $('#modal_abonos_pendientes_nro_operacion').val(num_operacion);

    }
});