
function sec_comercial_zona_meta() {
    if (sec_id === 'comercial' && sub_sec_id === 'zona_meta') {
        console.log("sub_sec_id: "+sub_sec_id);
        $('#secComZonMet_busqueda_ciclo').select2();
        $('#secComZonMet_busqueda_ciclo').val(gen_ciclo);
        $('#secComZonMet_busqueda_ciclo').select2().trigger('change');
    }
}



$(function() {
    //console.log(gen_ciclo);

    $('#secComZonMet_btn_agregar_meta').click(function() {
        secComZonMet_nueva_meta();
    });
    $('#modal_meta_btn_guardar').click(function() {
        modal_meta_btn_guardar();
    });
    $('#secComZonMet_btn_agregar_meta_masiva').click(function() {
        secComZonMet_nueva_masiva();
    });
    $('#modal_masiva_btn_guardar').click(function() {
        modal_masiva_btn_guardar();
    });

    $('#secComZonMet_busqueda_ciclo').change(function () {
        secComZonMet_recargar_pagina();
    });

    $(".secComZonMet_format_num").on({
        "focus": function (event) {
            $(event.target).select();
            console.log('focus');
        },
        "blur": function (event) {
            console.log('blur');
            $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
            $(event.target).val(function (index, value ) {
                return value.replace(/\D/g, "")
                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
            });
        }
    });

});
function secComZonMet_recargar_pagina(){
    var secComZonMet_ano=$('#secComZonMet_busqueda_ciclo option:selected').attr('ano');
    var secComZonMet_mes=$('#secComZonMet_busqueda_ciclo option:selected').attr('mes');
    window.open("?sec_id=comercial&sub_sec_id=zona_meta&ano="+secComZonMet_ano+"&mes="+secComZonMet_mes,"_self");
}

function secComZonMet_nueva_meta(){
    limpiar_campos_modal_meta();
    limpiar_bordes_modal_meta();
    $('#modal_meta_btn_guardar').show();
    $('#modal_meta').modal();
}
function limpiar_campos_modal_meta() {
    $('#modal_meta_zona').val('0');
    $('#modal_meta_producto').val('0');
    $('#modal_meta_monto').val('');
}
function limpiar_bordes_modal_meta() {
    $('#modal_meta_zona_div').css('border', '');
    $('#modal_meta_producto_div').css('border', '');
    $('#modal_meta_monto').css('border', '');
}
function modal_meta_btn_guardar() {
    $('#modal_meta_btn_guardar').hide();
    limpiar_bordes_modal_meta();
    var zona = $('#modal_meta_zona').val();
    var producto = $('#modal_meta_producto').val();
    var monto = $('#modal_meta_monto').val();

    if (!(parseInt(zona) > 0)) {
        $('#modal_meta_zona_div').css('border', '1px solid red');
        $('#modal_meta_zona').focus();
        $('#modal_meta_btn_guardar').show();
        return false;
    }
    if (!(parseInt(producto) > 0)) {
        $('#modal_meta_producto_div').css('border', '1px solid red');
        $('#modal_meta_producto').focus();
        $('#modal_meta_btn_guardar').show();
        return false;
    }
    if (!(parseFloat(monto) > 0)) {
        $('#modal_meta_monto').css('border', '1px solid red');
        $('#modal_meta_monto').focus();
        $('#modal_meta_btn_guardar').show();
        return false;
    }
    var data = {
        "accion": "guardar_meta",
        "zona": zona,
        "producto": producto,
        "monto": monto
    }
    //console.log(data);
    //return false;

    $.ajax({
        url: "/sys/set_comercial_zona_meta.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) { //  alert(datat)
            var respuesta = JSON.parse(resp);
            auditoria_send({ "respuesta": "guardar_meta", "data": resp });
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                $('#modal_meta_btn_guardar').show();
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                swal({
                    title: "Meta registrada con éxito.",
                    text: false,
                    type: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                secComZonMet_recargar_pagina();
                return false;
            }
            return false;
        },
        error: function(result) {
            auditoria_send({ "proceso": "guardar_meta_error", "data": result });
            $('#modal_meta_btn_guardar').show();
            return false;
        }
    });
    return false;
}


function secComZonMet_listar_detalle(cod_zona, zona, meta_apd, meta_jv, meta_bingo){

    $('#modal_auditoria_zona').val(zona);
    $('#modal_auditoria_apd').val('0.00');
    $('#modal_auditoria_jv').val('0.00');
    $('#modal_auditoria_bingo').val('0.00');
    $('#modal_auditoria_apd').val(meta_apd);
    $('#modal_auditoria_jv').val(meta_jv);
    $('#modal_auditoria_bingo').val(meta_bingo);
    $('#modal_auditoria_tbl').html(
        '<thead>' +
        '<tr>' +
        '<td class="text-center">Producto</td>' +
        '<td class="text-center">Meta</td>' +
        '<td class="text-center">Usuario</td>' +
        '<td class="text-right">Registro</td>' +
        '</tr>' +
        '</thead>' +
        '<tbody>'
    );
    var data = {
        "accion": "obtener_transacciones_x_zona",
        "cod_zona": cod_zona,
        "ano": gen_ciclo_ano,
        "mes": gen_ciclo_mes
    }
    //console.log(data);
    //return false;

    $.ajax({
        url: "/sys/set_comercial_zona_meta.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) { //  alert(datat)
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) === 200) {
                $.each(respuesta.result, function(index, item) {
                    item.meta=(parseFloat(item.meta).toFixed(2)).replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    $('#modal_auditoria_tbl').append(
                        '<tr>' +
                        '<td class="text-left">' + item.producto + '</td>' +
                        '<td class="text-right">' + (parseFloat(item.meta)).toFixed(2) + '</td>' +
                        '<td class="text-center">' + item.usuario + '</td>' +
                        '<td class="text-right">' + item.registro + '</td>' +
                        '</tr>'
                    );
                });
                secComZonMet_datatable_formato('#modal_auditoria_tbl');
            }
            if (parseInt(respuesta.http_code) === 400) {
                $('#modal_auditoria_tbl').append(
                    '<tr>' +
                    '<td colspan="4" class="text-center">Sin transacciones</td>' +
                    '</tr>'
                );
            }
            $('#modal_auditoria').modal();
            return false;
        },
        error: function(result) {
            auditoria_send({ "proceso": "obtener_transacciones_x_zona_error", "data": result });
            return false;
        }
    });
    return false;
}










function secComZonMet_nueva_masiva(){
    limpiar_campos_modal_masiva();
    $('#modal_masiva_btn_guardar').show();
    $('#modal_masiva').modal();
}
function limpiar_campos_modal_masiva() {
    $('#modal_masiva_ciclo').val(gen_ciclo);
    $('#modal_masiva_titulo').html('Meta: '+gen_ciclo_ano+' - '+gen_ciclo_mes_texto);
    $('#modal_masiva_tbl_principal').html('');
    $('#modal_masiva_tbl_principal').html(
        '<thead>'+
            '<tr class="small">'+
                '<th style="color: white;width: 5%;">#</th>'+
                '<th style="color: white;width: 35%;text-align: center;">Zona</th>'+
                '<th style="color: white;width: 20%;text-align: right;">Apuestas Deportivas</th>'+
                '<th style="color: white;width: 20%;text-align: right;">Juegos Virtuales</th>'+
                '<th style="color: white;width: 20%;text-align: right;">Bingo</th>'+
            '</tr>'+
        '</thead>'+
        '<tbody>'
    );
    $('#secComZonMet_tbl_principal tbody tr').each(function () {
        var tr_cod_zona = $(this).attr("cod_zona");
        var tr_td_0 = $(this).find("th").eq(0).html().trim();
        var tr_td_1 = $(this).find("th").eq(1).html().trim();
        var tr_td_2 = parseFloat($(this).find("th").eq(2).html().trim().replace(/\,/g, '')).toFixed(2)
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        var tr_td_3 = parseFloat($(this).find("th").eq(3).html().trim().replace(/\,/g, '')).toFixed(2)
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        var tr_td_4 = parseFloat($(this).find("th").eq(4).html().trim().replace(/\,/g, '')).toFixed(2)
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

        var temp_id_apd="'modal_masiva_tr_apd_"+tr_cod_zona+"'";
        var temp_id_ajv="'modal_masiva_tr_jv_"+tr_cod_zona+"'";
        var temp_id_bingo="'modal_masiva_tr_bingo_"+tr_cod_zona+"'";
        $('#modal_masiva_tbl_principal tbody').append(
            '<tr class="small" cod_zona="'+tr_cod_zona+'">'+
                '<th style="width: 5%;">'+tr_td_0+'</th>'+
                '<th style="width: 35%;">'+tr_td_1+'</th>'+
                '<th style="width: 20%;text-align: right;">'+
                    '<input type="text" class="form-control" id="modal_masiva_tr_apd_'+tr_cod_zona+'" placeholder="0.00" '+
                    'autocomplete="off" onblur="secComZonMet_blur('+temp_id_apd+');" '+
                    'onkeypress="return secComZonMet_filterFloat_2(event, this);" style="text-align:right;" '+
                    'value="'+tr_td_2+'">'+
                '</th>'+
                '<th style="width: 20%;text-align: right;">'+
                    '<input type="text" class="form-control" id="modal_masiva_tr_jv_'+tr_cod_zona+'" placeholder="0.00" '+
                    'autocomplete="off" onblur="secComZonMet_blur('+temp_id_ajv+');" '+
                    'onkeypress="return secComZonMet_filterFloat_2(event, this);" style="text-align:right;" '+
                    'value="'+tr_td_3+'">'+
                '</th>'+
                '<th style="width: 20%;text-align: right;">'+
                    '<input type="text" class="form-control" id="modal_masiva_tr_bingo_'+tr_cod_zona+'" placeholder="0.00" '+
                    'autocomplete="off" onblur="secComZonMet_blur('+temp_id_bingo+');" '+
                    'onkeypress="return secComZonMet_filterFloat_2(event, this);" style="text-align:right;" '+
                    'value="'+tr_td_4+'">'+
                '</th>'+
            '</tr>'
        );
    });
}
function limpiar_bordes_modal_masiva() {
    $('#modal_masiva_ciclo').css('border', '');
}

function modal_masiva_btn_guardar(){

    var detalle = [];
    $('#secComZonMet_tbl_principal tbody tr').each(function () {
        var cod_zona = $(this).attr('cod_zona');
        //var td_monto_apd = $(this).find("th").eq(2).find('children').val();
        var td_monto_apd = $("#modal_masiva_tr_apd_"+cod_zona).val().replace(/\,/g, '');
        var td_monto_jv = $("#modal_masiva_tr_jv_"+cod_zona).val().replace(/\,/g, '');
        var td_monto_bingo = $("#modal_masiva_tr_bingo_"+cod_zona).val().replace(/\,/g, '');
        if(!(parseFloat(td_monto_apd) >= 0)){
            td_monto_apd=0;
        }
        if(!(parseFloat(td_monto_jv) >= 0)){
            td_monto_jv=0;
        }
        if(!(parseFloat(td_monto_bingo) >= 0)){
            td_monto_bingo=0;
        }
        detalle.push({
            "cod_zona": cod_zona,
            "monto_apd": td_monto_apd,
            "monto_jv": td_monto_jv,
            "monto_bingo": td_monto_bingo
        });
    });

    var data = {
        "accion": "guardar_meta_masiva",
        "ciclo_ano": gen_ciclo_ano,
        "ciclo_mes": gen_ciclo_mes,
        "detalle": detalle
    }
    //console.log(data);
    //console.log(detalle);
    //return false;

    $.ajax({
        url: "/sys/set_comercial_zona_meta.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) { //  alert(datat)
            var respuesta = JSON.parse(resp);
            auditoria_send({ "respuesta": "guardar_meta_masiva", "data": resp });
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                $('#modal_masiva_btn_guardar').show();
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                swal({
                    title: "Edición exitosa.",
                    text: false,
                    type: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                secComZonMet_recargar_pagina();
                return false;
            }
            return false;
        },
        error: function(result) {
            auditoria_send({ "proceso": "guardar_meta_masiva_error", "data": result });
            $('#modal_masiva_btn_guardar').show();
            return false;
        }
    });
    return false;
}






















function secComZonMet_blur(input) {
    var valor=$('#'+input).val();
    //console.log(valor);
    valor=parseFloat(valor.replace(/\,/g, '')).toFixed(2);
    //console.log(valor);
    valor=valor.replace(/\D/g, "")
                    .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    //console.log(valor);
    $('#'+input).val(valor);
}





function secComZonMet_filterFloat_2(evt, input) {
    // Backspace = 8, Enter = 13, '0′ = 48, '9′ = 57, '.' = 46, '-' = 43
    var key = window.Event ? evt.which : evt.keyCode;
    var chark = String.fromCharCode(key);
    var tempValue = input.value + chark;
    if (key >= 48 && key <= 57) {
        if (secComZonMet_filter_2(tempValue) === false) {
            return false;
        } else {
            return true;
        }
    } else {
        if (key == 8 || key == 13 || key == 0) {
            return true;
        } else if (key == 46) {
            if (secComZonMet_filter_2(tempValue) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
}

function secComZonMet_filter_2(__val__) {
    console.log(secComZonMet_filter_2);
    var preg = /^([0-9]+\.?[0-9]{0,2})$/;
    if (preg.test(__val__) === true) {
        return true;
    } else {
        return false;
    }
}

function secComZonMet_datatable_formato(id) {
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
    }
    $(id).DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'order': [[ 3, "asc" ]],
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



