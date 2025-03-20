

function sec_cron_report_tls() {
    if (sec_id == 'cron' && sub_sec_id=='report_tls') {

        $('#SecCronTls_fecha').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#SecCronTls_fecha').change(function () {
            var var_fecha_change = $('#SecCronTls_fecha').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#SecCronTls_fecha").val(gen_fecha_actual);
            }
        });
        $('#SecCronTls_fecha').val(gen_fecha_actual);

        $('#SecCronTls_btn_buscar').click(function() {
            SecCronTls_listar_resultado();
        });
        $('#SecCronTls_btn_generar').click(function() {
            SecCronTls_generar();
        });


        $('#SecCronTls_btn_enviar').click(function() {
            SecCronTls_nuevo_enviar();
        });
        $('#modal_correo_btn_limpiar').click(function() {
            SecCronTls_modal_correo_limpiar();
        });
        $('#modal_correo_btn_guardar').click(function() {
            SecCronTls_enviar_correo();
        });
    }
}

function SecCronTls_limpiar(fecha, mes, ano) {
    $('#titulo_diario').html('');
    $('#tabla_ventas_x_producto_diario').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_diario" style="background-color: #395168;color: white;font-size: 14px;">' +
        '       <th colspan="11" class="text-center">TELESERVICIOS - Ventas por Producto - ' + fecha + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Productos</th>' +
        '       <th class="text-center">Tks<br>Apostado</th>' +
        '       <th class="text-center">Apostado</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Calculados</th>' +
        '       <th class="text-center">Calculado</th>' +
        '       <th class="text-center">Resultado</th>' +
        '       <th class="text-center" style="min-width: 50px;">Hold</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Tks Pagados</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Pagos</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">% Tks Pagados</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_transaccionales_diario').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_diario" style="background-color: #395168;color: white;font-size: 14px;">' +
        '       <th colspan="6" class="text-center">TELESERVICIOS - Ventas Transaccionales - ' + fecha + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Transaccionales</th>' +
        '       <th class="text-center">Tks Torito</th>' +
        '       <th class="text-center">Venta Torito</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Pagados</th>' +
        '       <th class="text-center">Pagos</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_x_caja_diario').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_diario" style="background-color: #395168;color: white;font-size: 14px;">' +
        '       <th colspan="11" class="text-center">TELESERVICIOS - Ventas AD por Caja - ' + fecha + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Apuesta Deportivas x Caja</th>' +
        '       <th class="text-center">Tks Apostado</th>' +
        '       <th class="text-center">Apostado</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Calculados</th>' +
        '       <th class="text-center">Calculado</th>' +
        '       <th class="text-center">Resultado</th>' +
        '       <th class="text-center" style="min-width: 50px;">Hold</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Tks Pagados</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Pagos</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">% Tks Pagados</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_jv_x_juego_diario').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_diario" style="background-color: #395168;color: white;font-size: 14px;">' +
        '       <th colspan="11" class="text-center">TELESERVICIOS - Ventas JV por Juego - ' + fecha + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Productos</th>' +
        '       <th class="text-center">Tks<br>Apostado</th>' +
        '       <th class="text-center">Apostado</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Calculados</th>' +
        '       <th class="text-center">Calculado</th>' +
        '       <th class="text-center">Resultado</th>' +
        '       <th class="text-center" style="min-width: 50px;">Hold</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Tks Pagados</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Pagos</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">% Tks Pagados</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_recargas_web_diario').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_diario" style="background-color: #395168;color: white;font-size: 14px;">' +
        '       <th colspan="4" class="text-center">TELESERVICIOS - Recargas Web - ' + fecha + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Otros Ingresos</th>' +
        '       <th class="text-center">transacciones</th>' +
        '       <th class="text-center">Monto</th>' +
        '       <th class="text-center">Promedio</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_terminales_diario').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_diario" style="background-color: #395168;color: white;font-size: 14px;">' +
        '       <th colspan="4" class="text-center">TELESERVICIOS - Venta para Terminales - ' + fecha + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Otros Ingresos</th>' +
        '       <th class="text-center">transacciones</th>' +
        '       <th class="text-center">Monto</th>' +
        '       <th class="text-center">Promedio</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_otros_pagos_diario').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_diario" style="background-color: #395168;color: white;font-size: 14px;">' +
        '       <th colspan="4" class="text-center">TELESERVICIOS - Otros Pagos de Tickets - ' + fecha + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Otras Salidas</th>' +
        '       <th class="text-center">transacciones</th>' +
        '       <th class="text-center">Monto</th>' +
        '       <th class="text-center">Promedio</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    //***************************************************************************************************************
    //***************************************************************************************************************
    // MENSUAL
    //***************************************************************************************************************
    //***************************************************************************************************************
    $('#titulo_mensual').html('');
    $('#tabla_ventas_x_producto_mensual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_mensual" style="background-color: #2971b1;font-size: 14px;color: white;">' +
        '       <th colspan="11" class="text-center">TELESERVICIOS - Ventas por Producto - ' + mes + '-' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Productos</th>' +
        '       <th class="text-center">Tks Apostado</th>' +
        '       <th class="text-center">Apostado</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Calculados</th>' +
        '       <th class="text-center">Calculado</th>' +
        '       <th class="text-center">Resultado</th>' +
        '       <th class="text-center" style="min-width: 50px;">Hold</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Tks Pagados</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Pagos</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">% Tks Pagados</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_transaccionales_mensual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_mensual" style="background-color: #2971b1;font-size: 14px;color: white;">' +
        '       <th colspan="6" class="text-center">TELESERVICIOS - Ventas Transaccionales - ' + mes + '-' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Transaccionales</th>' +
        '       <th class="text-center">Tks Torito</th>' +
        '       <th class="text-center">Venta Torito</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Pagados</th>' +
        '       <th class="text-center">Pagos</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_x_caja_mensual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_mensual" style="background-color: #2971b1;font-size: 14px;color: white;">' +
        '       <th colspan="11" class="text-center">TELESERVICIOS - Ventas AD por Caja - ' + mes + '-' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Apuesta Deportivas x Caja</th>' +
        '       <th class="text-center">Tks Apostado</th>' +
        '       <th class="text-center">Apostado</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Calculados</th>' +
        '       <th class="text-center">Calculado</th>' +
        '       <th class="text-center">Resultado</th>' +
        '       <th class="text-center" style="min-width: 50px;">Hold</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Tks Pagados</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Pagos</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">% Tks Pagados</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_jv_x_juego_mensual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_mensual" style="background-color: #2971b1;font-size: 14px;color: white;">' +
        '       <th colspan="11" class="text-center">TELESERVICIOS - Ventas JV por Juego - ' + mes + '-' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Productos</th>' +
        '       <th class="text-center">Tks Apostado</th>' +
        '       <th class="text-center">Apostado</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Calculados</th>' +
        '       <th class="text-center">Calculado</th>' +
        '       <th class="text-center">Resultado</th>' +
        '       <th class="text-center" style="min-width: 50px;">Hold</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Tks Pagados</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Pagos</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">% Tks Pagados</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_recargas_web_mensual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_mensual" style="background-color: #2971b1;font-size: 14px;color: white;">' +
        '       <th colspan="4" class="text-center">TELESERVICIOS - Recargas Web - ' + mes + '-' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Otros Ingresos</th>' +
        '       <th class="text-center">transacciones</th>' +
        '       <th class="text-center">Monto</th>' +
        '       <th class="text-center">Promedio</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_terminales_mensual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_mensual" style="background-color: #2971b1;font-size: 14px;color: white;">' +
        '       <th colspan="4" class="text-center">TELESERVICIOS - Venta para Terminales - ' + mes + '-' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Otros Ingresos</th>' +
        '       <th class="text-center">transacciones</th>' +
        '       <th class="text-center">Monto</th>' +
        '       <th class="text-center">Promedio</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_otros_pagos_mensual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_mensual" style="background-color: #2971b1;font-size: 14px;color: white;">' +
        '       <th colspan="4" class="text-center">TELESERVICIOS - Otros Pagos de Tickets - ' + mes + '-' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Otras Salidas</th>' +
        '       <th class="text-center">transacciones</th>' +
        '       <th class="text-center">Monto</th>' +
        '       <th class="text-center">Promedio</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    //***************************************************************************************************************
    //***************************************************************************************************************
    // ANUAL
    //***************************************************************************************************************
    //***************************************************************************************************************
    $('#titulo_anual').html('');
    $('#tabla_ventas_x_producto_anual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_anual" style="background-color: #6f9ac0;font-size: 14px;color: white;">' +
        '       <th colspan="11" class="text-center">TELESERVICIOS - Ventas por Producto - ' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Productos</th>' +
        '       <th class="text-center">Tks Apostado</th>' +
        '       <th class="text-center">Apostado</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Calculados</th>' +
        '       <th class="text-center">Calculado</th>' +
        '       <th class="text-center">Resultado</th>' +
        '       <th class="text-center" style="min-width: 50px;">Hold</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Tks Pagados</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Pagos</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">% Tks Pagados</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_transaccionales_anual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_anual" style="background-color: #6f9ac0;font-size: 14px;color: white;">' +
        '       <th colspan="6" class="text-center">TELESERVICIOS - Ventas Transaccionales - ' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Transaccionales</th>' +
        '       <th class="text-center">Tks Torito</th>' +
        '       <th class="text-center">Venta Torito</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Pagados</th>' +
        '       <th class="text-center">Pagos</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_x_caja_anual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_anual" style="background-color: #6f9ac0;font-size: 14px;color: white;">' +
        '       <th colspan="11" class="text-center">TELESERVICIOS - Ventas AD por Caja - ' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Apuesta Deportivas x Caja</th>' +
        '       <th class="text-center">Tks Apostado</th>' +
        '       <th class="text-center">Apostado</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Calculados</th>' +
        '       <th class="text-center">Calculado</th>' +
        '       <th class="text-center">Resultado</th>' +
        '       <th class="text-center" style="min-width: 50px;">Hold</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Tks Pagados</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Pagos</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">% Tks Pagados</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_jv_x_juego_anual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_anual" style="background-color: #6f9ac0;font-size: 14px;color: white;">' +
        '       <th colspan="11" class="text-center">TELESERVICIOS - Ventas JV por Juego - ' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Productos</th>' +
        '       <th class="text-center">Tks Apostado</th>' +
        '       <th class="text-center">Apostado</th>' +
        '       <th class="text-center">Promedio</th>' +
        '       <th class="text-center">Tks Calculados</th>' +
        '       <th class="text-center">Calculado</th>' +
        '       <th class="text-center">Resultado</th>' +
        '       <th class="text-center" style="min-width: 50px;">Hold</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Tks Pagados</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">Pagos</th>' +
        '       <th class="text-center background_color_verde" style="background-color: #a9d18e;">% Tks Pagados</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_recargas_web_anual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_anual" style="background-color: #6f9ac0;font-size: 14px;color: white;">' +
        '       <th colspan="4" class="text-center">TELESERVICIOS - Recargas Web - ' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Otros Ingresos</th>' +
        '       <th class="text-center">transacciones</th>' +
        '       <th class="text-center">Monto</th>' +
        '       <th class="text-center">Promedio</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_terminales_anual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_anual" style="background-color: #6f9ac0;font-size: 14px;color: white;">' +
        '       <th colspan="4" class="text-center">TELESERVICIOS - Venta para Terminales - ' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Otros Ingresos</th>' +
        '       <th class="text-center">transacciones</th>' +
        '       <th class="text-center">Monto</th>' +
        '       <th class="text-center">Promedio</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    $('#tabla_ventas_otros_pagos_anual').html(
        '<thead>' +
        '   <tr class="tabla_primer_th tabla_primer_th_anual" style="background-color: #6f9ac0;font-size: 14px;color: white;">' +
        '       <th colspan="4" class="text-center">TELESERVICIOS - Otros Pagos de Tickets - ' + ano + '</th>' +
        '   </tr>' +
        '   <tr class="tabla_segundo_th" style="background-color: #ffffdd;color:  black;">' +
        '       <th class="text-left">Otras Salidas</th>' +
        '       <th class="text-center">transacciones</th>' +
        '       <th class="text-center">Monto</th>' +
        '       <th class="text-center">Promedio</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
}

function SecCronTls_listar_resultado() {
    $('#SecCronTls_div_resultado').show();

    var nombres_mes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

    var var_fecha = $('#SecCronTls_fecha').val();
    
    SecCronTls_limpiar(var_fecha, nombres_mes[parseInt(var_fecha.substr(5,2))-1], var_fecha.substr(0,4));
    $('#titulo_diario').html('Reporte del Día: ' + var_fecha);
    $('#titulo_mensual').html('Reporte del Mes: ' + nombres_mes[parseInt(var_fecha.substr(5,2))-1]);
    $('#titulo_anual').html('Reporte del Año: ' + var_fecha.substr(0,4));

    var SecRepTel_fecha_inicio = $.trim($("#SecRepTel_fecha_inicio").val());
    var SecRepTel_fecha_fin = $.trim($("#SecRepTel_fecha_fin").val());
    var SecRepTel_tipo_transaccion = $("#SecRepTel_tipo_transaccion").val();
    var SecRepTel_local = $("#SecRepTel_local").val();
    var SecRepTel_cajero = $("#SecRepTel_cajero").val();
    var SecRepTel_estado_cierre = $("#SecRepTel_estado_cierre").val();

    if (var_fecha.length !== 10) {
        $("#SecCronTls_fecha").focus();
        return false;
    }

    var data = {
        "accion": "listar_transacciones",
        "fecha": var_fecha
    }

    auditoria_send({ "proceso": "listar_transacciones", "data": data });
    $.ajax({
        url: "/sys/set_cron_report_tls.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
                loading("true");
        },
        complete: function() {
                loading();
                $("#SecRepTel_resultado").css("display","");
        },
        success: function(resp) {
            var respuesta = JSON.parse(resp);
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) === 200) {
                $.each(respuesta.result_tabla_ventas_x_producto_diario, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado'){
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    $('#tabla_ventas_x_producto_diario').append(
                        '<tr '+tr_color+'>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.resultado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.hold + ' %</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.porcentaje_tickets_pagado + ' %</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_transaccionales_diario, function(index, item) {
                    $('#tabla_ventas_transaccionales_diario').append(
                        '<tr>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_x_caja_diario, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado'){
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    $('#tabla_ventas_x_caja_diario').append(
                        '<tr '+tr_color+'>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.resultado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.hold + ' %</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.porcentaje_tickets_pagado + ' %</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_jv_x_juego_diario, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado'){
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    $('#tabla_ventas_jv_x_juego_diario').append(
                        '<tr '+tr_color+'>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.resultado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.hold + ' %</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.porcentaje_tickets_pagado + ' %</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_recargas_web_diario, function(index, item) {
                    $('#tabla_ventas_recargas_web_diario').append(
                        '<tr>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_terminales_diario, function(index, item) {
                    $('#tabla_ventas_terminales_diario').append(
                        '<tr>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_otros_pagos_diario, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado'){
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    $('#tabla_ventas_otros_pagos_diario').append(
                        '<tr '+tr_color+'>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '</tr>'
                    );
                });

                // ******************************************************************************
                // ******************************************************************************
                // MENSUAL
                // ******************************************************************************
                // ******************************************************************************

                $.each(respuesta.result_tabla_ventas_x_producto_mensual, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado'){
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    $('#tabla_ventas_x_producto_mensual').append(
                        '<tr '+tr_color+'>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.resultado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.hold + ' %</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.porcentaje_tickets_pagado + ' %</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_transaccionales_mensual, function(index, item) {
                    $('#tabla_ventas_transaccionales_mensual').append(
                        '<tr>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_x_caja_mensual, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado') {
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    if(item.concepto!=='Proyectado') {
                        $('#tabla_ventas_x_caja_mensual').append(
                            '<tr '+tr_color+'>' +
                            '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.num_tickets_calculado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.total_tickets_calculado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.resultado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.hold + ' %</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.porcentaje_tickets_pagado + ' %</td>' +
                            '</tr>'
                        );
                    } else {
                        $('#tabla_ventas_x_caja_mensual').append(
                            '<tr style="color: black;font-weight: 900;">' +
                            '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                            '<td class="text-right" style="text-align: right;"></td>' +
                            '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                            '<td class="text-right" colspan="8"></td>' +
                            '</tr>'
                        );
                    }
                });
                $.each(respuesta.result_tabla_ventas_jv_x_juego_mensual, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado'){
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    if(item.concepto!=='Proyectado') {
                        $('#tabla_ventas_jv_x_juego_mensual').append(
                            '<tr '+tr_color+'>' +
                            '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.num_tickets_calculado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.total_tickets_calculado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.resultado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.hold + ' %</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.porcentaje_tickets_pagado + ' %</td>' +
                            '</tr>'
                        );
                    } else {
                        $('#tabla_ventas_jv_x_juego_mensual').append(
                            '<tr style="color: black;font-weight: 900;">' +
                            '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                            '<td class="text-right" style="text-align: right;"></td>' +
                            '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                            '<td class="text-right" colspan="8"></td>' +
                            '</tr>'
                        );
                    }
                });
                $.each(respuesta.result_tabla_ventas_recargas_web_mensual, function(index, item) {
                    if(item.concepto!=='Proyectado') {
                        $('#tabla_ventas_recargas_web_mensual').append(
                            '<tr>' +
                            '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                            '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                            '</tr>'
                        );
                    } else {
                        $('#tabla_ventas_recargas_web_mensual').append(
                            '<tr style="color: black;font-weight: 900;">' +
                            '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                            '<td class="text-right" style="text-align: right;"></td>' +
                            '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                            '<td class="text-right" style="text-align: right;"></td>' +
                            '</tr>'
                        );
                    }
                });
                $.each(respuesta.result_tabla_ventas_terminales_mensual, function(index, item) {
                    $('#tabla_ventas_terminales_mensual').append(
                        '<tr>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_otros_pagos_mensual, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado'){
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    $('#tabla_ventas_otros_pagos_mensual').append(
                        '<tr '+tr_color+'>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '</tr>'
                    );
                });

                // ******************************************************************************
                // ******************************************************************************
                // ANUAL
                // ******************************************************************************
                // ******************************************************************************

                $.each(respuesta.result_tabla_ventas_x_producto_anual, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado'){
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    $('#tabla_ventas_x_producto_anual').append(
                        '<tr '+tr_color+'>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.resultado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.hold + ' %</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.porcentaje_tickets_pagado + ' %</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_transaccionales_anual, function(index, item) {
                    $('#tabla_ventas_transaccionales_anual').append(
                        '<tr>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_x_caja_anual, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado'){
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    $('#tabla_ventas_x_caja_anual').append(
                        '<tr '+tr_color+'>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.resultado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.hold + ' %</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.porcentaje_tickets_pagado + ' %</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_jv_x_juego_anual, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado'){
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    $('#tabla_ventas_jv_x_juego_anual').append(
                        '<tr '+tr_color+'>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_calculado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.resultado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.hold + ' %</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_pagado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.porcentaje_tickets_pagado + ' %</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_recargas_web_anual, function(index, item) {
                    $('#tabla_ventas_recargas_web_anual').append(
                        '<tr>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_terminales_anual, function(index, item) {
                    $('#tabla_ventas_terminales_anual').append(
                        '<tr>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '</tr>'
                    );
                });
                $.each(respuesta.result_tabla_ventas_otros_pagos_anual, function(index, item) {
                    var tr_color = '';
                    if(item.concepto==='Acumulado'){
                        tr_color = 'style="color: black;font-weight: 900;background-color: darkgrey;"'
                    }
                    $('#tabla_ventas_otros_pagos_anual').append(
                        '<tr '+tr_color+'>' +
                        '<td class="text-left" style="background-color: #ffffdd;">' + item.concepto + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.num_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.total_tickets_apostado + '</td>' +
                        '<td class="text-right" style="text-align: right;">' + item.promedio + '</td>' +
                        '</tr>'
                    );
                });

                //console.log(array_clientes);
                return false;
            } else if (parseInt(respuesta.http_code) === 204) {
                $('table').append(
                    '<tr>' +
                    '<td class="text-center" colspan="11">No hay transacciones.</td>' +
                    '</tr>'
                );
                return false;
            } else {
                $('table').append(
                    '<tr>' +
                    '<td class="text-center" colspan="11">No hay transacciones.</td>' +
                    '</tr>'
                );
                return false;
            }
        },
        error: function(error) {
            console.log(error);
            return false;
        }
    });
}




function SecCronTls_generar() {
    $('#SecCronTls_btn_generar').hide();

    var var_fecha = $('#SecCronTls_fecha').val();

    if (var_fecha.length !== 10) {
        $("#SecCronTls_fecha").focus();
        return false;
    }
    var data = {
        "accion": "generar_transacciones",
        "fecha": var_fecha
    }

    auditoria_send({ "proceso": "generar_transacciones", "data": data });
    $.ajax({
        url: "/sys/set_cron_report_tls.php",
        type: 'POST',
        async : true,
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
                $('#SecCronTls_btn_generar').show();
                SecCronTls_listar_resultado();
                return false;
            }
            return false;
        },
        error: function() {
            $('#SecCronTls_btn_generar').show();
            return false;
        }
    });
    return false;
}







function SecCronTls_nuevo_enviar() {
    $('#modal_correo_btn_guardar').show();
    $('#modal_correo').modal();
}
function SecCronTls_modal_correo_limpiar(){
    $('#modal_correo_i_asunto').val('');
    $('textarea#modal_correos_principales_txa_lista').val('');
    $('textarea#modal_correos_ocultos_txa_lista').val('');
}
function SecCronTls_enviar_correo() {
    $('#modal_correo_btn_guardar').hide();

    var var_fecha = $('#SecCronTls_fecha').val();
    var var_asunto = $('#modal_correo_i_asunto').val();
    var var_correos_principales = $('textarea#modal_correos_principales_txa_lista').val();
    var var_correos_ocultos = $('textarea#modal_correos_ocultos_txa_lista').val();
    var body = $('#SecCronTls_div_resultado').html();

    if (var_fecha.length !== 10) {
        $('#modal_correo_btn_guardar').show();
        $("#SecCronTls_fecha").focus();
        return false;
    }
    if (!(var_correos_principales.length > 5)) {
        $('#modal_correo_btn_guardar').show();
        $("#modal_correos_principales_txa_lista").focus();
        return false;
    }
    if (!(body.length > 5)) {
        $('#modal_correo_btn_guardar').show();
        $("#SecCronTls_div_resultado").focus();
        return false;
    }

    var data = {
        "accion": "enviar_correo",
        "fecha": var_fecha,
        "asunto": var_asunto,
        "correos_principales": var_correos_principales,
        "correos_ocultos": var_correos_ocultos,
        "body": body
    }

    auditoria_send({ "proceso": "enviar_correo", "data": data });
    $.ajax({
        url: "/sys/set_cron_report_tls.php",
        type: 'POST',
        async : true,
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
                $('#modal_correo').modal('hide');
                $('#modal_correo_btn_guardar').show();
                swal('Aviso', 'Correo enviado con éxito.', 'success');
                return false;
            }
            return false;
        },
        error: function() {
            $('#modal_correo_btn_guardar').show();
            return false;
        }
    });
    return false;
}
