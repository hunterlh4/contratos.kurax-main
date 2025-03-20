
$(function () {

	if (sec_id === 'televentas_cuentas_at') {

		$('#SecTlsCuentasAt_banco').select2();

		SecTlsCuentasAt_listar();
	}

});

function SecTlsCuentasAt_listar() {
	
	$('#SecTlsCuentasAt_mensaje').show();
    $('#SecTlsCuentasAt_mensaje').html('Cargando ...');

    var banco = $("#SecTlsCuentasAt_banco").val();

    var data = {
        "accion": "listar_cuentas_at",
        "banco": banco
    }
    return false;
    auditoria_send({ "proceso": "listar_cuentas_at", "data": data });
    $.ajax({
        url: "/sys/set_televentas_cuentas_at.php",
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

                    $('#tabla_abonos tbody').append(
                        '<tr id="'+item.id+'" class="sec_tlv_pag_listado_transaccion">' +
                            '<td style="display:none;" class="sec_tlv_pag_listado_transaccion_id_transaccion">' + item.cod_transaccion + '</td>' +
                            '<td class="text-center">' + item.fecha_operacion + ' ' + item.hora_operacion + '</td>' +
                            '<td class="text-center">' + item.nro_corte + '</td>' +
                            '<td class="text-center">' + item.fecha_registro + '</td>' +
                            '<td class="text-center">' + item.usuario + '</td>' +
                            '<td class="text-center">' + item.nombre_banco + '</td>' +
                            '<td class="text-center">' + item.importe + '</td>' +  // cierre
                            '<td class="text-center">' + item.importe + '</td>' + // monto apertura
                            '<td class="text-center">' + item.fondo_para_pagos + '</td>' +  // fondo para pagos
                            '<td class="text-center" ' + style_negativo + ' >' + (item.importe - item.fondo_para_pagos).toFixed(2) + '</td>' + // abono de ventas
                            '<td class="text-center">' + item.monto_abonado + '</td>' + // abono depositado
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