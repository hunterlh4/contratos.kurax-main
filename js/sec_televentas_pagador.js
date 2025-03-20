var countdownTimer_sec_televentas_retiros;
localStorage.removeItem("listNew");
var var_fecha_hora = "";
var tls_pagador_version = 1.04;
let isTabActivePagador = true;
let isUserActivePagador = true;
let inactivityTimeoutPagador;
function sec_televentas_pagador() {

    if (sec_id == 'televentas_pagador') {
        if(parseFloat(tls_pagador_version)<parseFloat(tls_pagador_version_actual)){
			alertify.error('El sistema ha sido actualizado. Ctrl+F5 (Si estás en PC) o Ctrl+Tecla Función +F5 (Si estás en laptop) o contactar con el área de soporte..', 180);
		}

        $('#buscador_fecha_inicio_retiro').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#buscador_fecha_fin_retiro').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#buscador_fecha_inicio_retiro').val($('#g_fecha_actual').val());
        $('#buscador_fecha_fin_retiro').val($('#g_fecha_actual').val());

        $('#buscador_fecha_inicio_retiro').change(function() {
            var var_fecha_change = $('#buscador_fecha_inicio_retiro').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#buscador_fecha_inicio_retiro").val($("#g_fecha_actual").val());
            }
        });
        $('#buscador_fecha_fin_retiro').change(function() {
            var var_fecha_change = $('#buscador_fecha_fin_retiro').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#buscador_fecha_fin_retiro").val($("#g_fecha_actual").val());
            }
        });
        
        $("#buscador_estado_transaccion_retiro").val("1");
        $('#sec_tlv_pag_select_saldo').select2();
        $("#sec_tlv_pag_select_saldo").val('1').trigger('change');
        //sec_televentas_listar_bancos();
        cargar_cargar_multiple_select_pagador();
        listar_tabla_validaciones_retiros();
        
        $('#sec_tlv_pag_validador').select2();
        $('#sec_tlv_pag_select_banco_pago').select2();
        $('#sec_tlv_pag_modal_cuentas_banco').select2();
        $('#sec_tlv_pag_edit_select_banco_pago').select2();
        
        

        function startTimer() {
            if (isTabActivePagador && isUserActivePagador) {
                if (countdownTimer_sec_televentas_retiros) {
                    clearInterval(countdownTimer_sec_televentas_retiros);
                }
                countdownTimer_sec_televentas_retiros = setInterval(actualizar_tabla_validaciones_retiro, 3000);
            }
        }
        
        function stopTimer() {
            if (countdownTimer_sec_televentas_retiros) {
                clearInterval(countdownTimer_sec_televentas_retiros);
                countdownTimer_sec_televentas_retiros = null;
            }
        }
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimeoutPagador);
            isUserActivePagador = true;
            startTimer();
            
            inactivityTimeoutPagador = setTimeout(() => {
                isUserActivePagador = false;
                stopTimer();
            }, 30000);
        }
        
        document.addEventListener("visibilitychange", function () {
            if (document.hidden) {
                isTabActivePagador = false;
                stopTimer();
            } else {
                isTabActivePagador = true;
                startTimer()
                resetInactivityTimer();
            }
        });
        
        document.addEventListener("mousemove", resetInactivityTimer);
        document.addEventListener("keydown", resetInactivityTimer);
        
        if (isTabActivePagador && isUserActivePagador) {
            startTimer();
        }  
        

        //**************************************************************************************
        // RESULTADO
        //**************************************************************************************

        $('#div_dp_motivo_retiro').select2();
        $("#div_dp_motivo_retiro").val('0');
        $('#div_dp_motivo_retiro').select2().trigger('change');
        $('#div_dp_comision_select_retiro').select2();
        $("#div_dp_comision_select_retiro").val('0.00');
        $('#div_dp_comision_select_retiro').select2().trigger('change');
        

        $('#nombre_cliente_retiro').on('click', function(e) {
            e.preventDefault();
            var copiarTexto = $(this).attr('data-text');
            var textarea = document.createElement("textarea");
            textarea.textContent = copiarTexto;
            textarea.style.position = "fixed";
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand("copy");
            document.body.removeChild(textarea);
        });

        $('#buscador_pagador').autocomplete({
            source: '/sys/set_televentas_pagador.php?accion=sec_tlv_pag_listar_pagadores',
            minLength: 3,
            select: function (event, ui)
            {
                gen_pagador_seleccionado = ui.item.codigo;
                if(gen_pagador_seleccionado == undefined){
                    gen_pagador_seleccionado = 0;
                }
            }
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            $('ul.ui-menu').css('display', 'block !important');
            $('ul.ui-menu').css('z-index', '100000');
            return $( "<li>" )
            .append( item.label)
            .appendTo( ul );
        };

    }
}




var gen_tabla_validaciones_ultimo_id = 0;
var gen_tabla_validaciones_fecha_inicio = '';
var gen_tabla_validaciones_fecha_fin = '';
var gen_tabla_validaciones_estado = '';
var gen_tabla_validaciones_cuenta = '';
var gen_tabla_validaciones_validador = 0;
var gen_tabla_validaciones_tipo = 0;
var gen_tabla_validaciones_validador_supervisor = 0;
var gen_tabla_validaciones_cuenta_pago = 0;
var gen_tabla_validaciones_tipo_saldo = 1;
var array_tabla_validaciones = [];
var cargaPagina = 0;
var gen_pagador_seleccionado = 0;
function limpiar_tabla_validaciones_retiros() {
    $('#tabla_validaciones_retiro').html(
        '<thead>' +
        '   <tr>' +
        '       <th style="display:none;">Id Transaccion</th>' +
        '       <th>Cod Transaccion</th>' +
        '       <th>Tipo Transacción</th>' +
        '       <th>Fecha Solicitud</th>' +
        '       <th>Fecha Pago</th>' +
        '       <th>Usuario</th>' +
        '       <th>Caja</th>' +
        '       <th>Cliente</th>' +
        '       <th>Banco</th>' +
        '       <th>Nro Cuenta</th>' +
        '       <th>Nro CCI</th>' +
        '       <th>Link Atención</th>' +
        '       <th>Banco Pago</th>' +
        '       <th>Monto</th>' +
        '       <th>Comisión</th>' +
        '       <th>Nro Op</th>' +
        '       <th>Estado</th>' +
        '       <th>Pagador</th>' +
        '       <th>Razón</th>' +
        '       <th>Comprobante</th>' +
        // '       <th>Tipo</th>' +
        '       <th>Aprobado Por</th>' +
        '       <th>Ver</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
    //'       <th class="text-center"><input type="checkbox" id="sec_tlv_pag_check_todo" class="form-control" OnChange="checkboxChange(0);"/></th>' +
}
transacciones_id = "";
function listar_tabla_validaciones_retiros() {
    var monto_total_real = 0;
    var monto_total_comision = 0;
    $('#SecTelDel_monto_total_comision_retiro').val('0.00');
    $('#SecTelDel_monto_total_real_retiro').val('0.00');
    limpiar_tabla_validaciones_retiros();
    array_tabla_validaciones = [];

    var fecha_inicio = $("#buscador_fecha_inicio_retiro").val();
    var fecha_fin = $("#buscador_fecha_fin_retiro").val();
    var estado = $("#buscador_estado_transaccion_retiro").val();
    var cuenta = $("#buscador_cuenta_retiro").val();
    var validador = $('#buscador_pagador').val() != '' ? gen_pagador_seleccionado : '0';
    var razon = $("#sec_tlv_pag_select_razon").val();
    var tipo = $("#sec_tlv_pag_select_tipo").val();
    var validador_supervisor = $("#sec_tlv_pag_validador").val();
    var cuenta_pago = $("#buscador_cuenta_pago").val();
    var tipo_saldo = $("#sec_tlv_pag_select_saldo").val();
    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menos a la de fin.', 'warning');
        return false;
    }

    if(validador_supervisor == undefined){
        validador_supervisor = 0;
    }

    var data = {
        "accion": "obtener_transacciones_x_estado",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "estado": estado,
        "cuenta": cuenta,
        "validador": validador,
        "razon": razon,
        "tipo": tipo,
        "validador_supervisor": validador_supervisor,
        "cuenta_pago" : cuenta_pago,
        "tipo_saldo" : tipo_saldo
    }
    gen_tabla_validaciones_ultimo_id = 0;
    gen_tabla_validaciones_fecha_inicio = fecha_inicio;
    gen_tabla_validaciones_fecha_fin = fecha_fin;
    gen_tabla_validaciones_estado = estado;
    gen_tabla_validaciones_cuenta = cuenta;
    gen_tabla_validaciones_validador = validador;
    gen_tabla_validaciones_tipo = tipo;
    gen_tabla_validaciones_validador_supervisor = validador_supervisor;
    gen_tabla_validaciones_cuenta_pago = cuenta_pago;
    gen_tabla_validaciones_tipo_saldo = tipo_saldo;
    //$('#tabla_validaciones_retiro').html('');
    auditoria_send({ "proceso": "obtener_transacciones_x_estado", "data": data });
    if(estado == "1"){
        var ftable = $('#tabla_validaciones_retiro').DataTable();
        ftable.clear();
        ftable.destroy();
        $.ajax({
            url: "/sys/set_televentas_pagador.php",
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
                transacciones_id = "";
                if (parseInt(respuesta.http_code) == 400) {
                    limpiar_tabla_validaciones_retiros();
                    //swal('Aviso', respuesta.status, 'warning');
                    /*$('#tabla_validaciones_retiro').append(
                        '<tr>' +
                        '<td class="text-center" colspan="16">No hay transacciones</td>' +
                        '</tr>'
                    );*/
                    cargaPagina=1;
                    return false;
                }
                if (parseInt(respuesta.http_code) == 200) {
                    
                    $.each(respuesta.result, function(index, item) {
                        if(transacciones_id == ""){
                            transacciones_id = (item.id).toString();
                        }else{
                            transacciones_id = transacciones_id + ","+ (item.id).toString();
                        }
                        array_tabla_validaciones.push(item);
                        gen_tabla_validaciones_ultimo_id = item.id;
                        monto_total_real = parseFloat(monto_total_real) + parseFloat(item.monto);
                        monto_total_comision = parseFloat(monto_total_comision) + parseFloat(item.comision_monto);
                        var cuenta = "'" + item.cuenta + "'";
                        var monto_real = "'" + item.monto + "'";
                        var monto_real_td =item.monto.replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                        var color = '';
                        if(item.estado_id == 2){
                            var color = 'color : green; ';
                        }else if(item.estado_id == 3){
                            var color = 'color : red; ';
                        }
                        var ver = '';
                        if(item.estado_id != 5 || (item.estado_id == 5 && item.update_user_id == $("#sec_tlv_pag_id_usuario_login").val())){
                            ver = `<button type="button" class="btn btn-primary" 
                                    onclick="ver_detalle_retiro(${item.id}, ${item.estado_id}, ${item.cuenta_id}, ${cuenta}, 
                                    '${item.num_operacion}', '${item.cliente}', '${item.turno_local}', '${item.monto}', '${item.fecha_hora_registro}' 
                                    , '${item.cuenta_num}' , '${item.cci}' , 'CUENTA DE AHORROS','${item.tipo_rechazo_id}','${item.cuenta_pago_id}',
                                    '${item.cliente_id}','${item.razon}','${item.id_operacion_retiro}','${item.afect_balance}','${item.tipo_operacion}',
                                    '${item.motivo_devolucion}','${item.fecha_pago}')">
                                    <span class="glyphicon glyphicon-edit"></span></button>`;
                        }

                        var listadoNuevoItem = [];

                        //var total = (parseFloat(item.monto) + parseFloat(item.comision_monto)).toFixed(2);

                        $('#tabla_validaciones_retiro tbody').append(
                            '<tr style=" background:'+item.color_celda+'; " id="'+item.id+'" class="sec_tlv_pag_listado_transaccion">' +
                                '<td style="display:none;" class="sec_tlv_pag_listado_transaccion_id_transaccion">' + item.id + '</td>' +
                                '<td class="text-center">' + item.cod_transaction + '</td>' +
                                '<td class="text-center">' + item.tipo_operacion + '</td>' +
                                '<td class="text-center">' + item.fecha_hora_registro + '</td>' +
                                '<td class="text-center">' + item.fecha_hora_validacion + '</td>' +
                                '<td class="text-center">' + item.cajero + '</td>' +
                                '<td class="text-center">' + item.turno_local + '</td>' +
                                '<td class="text-right">' + item.cliente + '</td>' +
                                '<td class="text-center">' + item.cuenta + '</td>' +
                                '<td class="text-center" onclick="copiarAlPortapapeles(this.id, ' + item.id  + ', ' + item.estado_id  + ')" id="text-' + item.id  + '">' + item.cuenta_num + '</td>' +
                                '<td class="text-center">' + item.cci + '</td>' +
                                '<td class="text-center"><a href="' + item.link_atencion + '" target="_blank" style="text-transform: lowercase;">' + item.link_atencion + '</a></td>' +
                                '<td class="text-center">' + item.banco_pago + '</td>' +
                                '<td class="text-center">' + item.monto + '</td>' +
                                '<td class="text-center">' + item.comision_monto + '</td>' +
                                '<td class="text-center">' + item.num_operacion + '</td>' +
                                '<td class="text-right" style="' + color + '"><b>' + item.estado + '<b></td>' +
                                '<td class="text-right">' + item.validador_nombre + '</td>' +
                                '<td class="text-right">' + item.razon + '</td>' +
                                '<td class="text-right">' + item.enviar_comprobante + '</td>' +
                                // '<td class="text-right">' + item.tipo_operacion + '</td>' +
                                '<td class="text-right">' + item.validado_por + '</td>' +
                                '<td class="text-center">' + ver + '</td>' +
                            '</tr>'
                        );
                        // '<td class="text-center"><input type="checkbox" name="" class="sec_tlv_pag_checked_register_banco"></td>' +
                        rr_solicitudes_pendientes.push({id : item.id});
                        item2=item;
                        if(item.estado=='PENDIENTE'){
                            var nuevos = [];
                            var notificacionOld = JSON.parse(localStorage.getItem("listNew")); 
                            nuevos = notificacionOld == null ? [] : notificacionOld;                      
                            if (cargaPagina!=0){ 
                                if (notificacionOld!=null) {
                                    notificacionOld.forEach(element => {
                                        if(element != item2.id){
                                            nuevos.push(item2.id);                                    
                                            }
                                        });
                                    }
                            
                            } else {
                                nuevos.push(item2.id); 
                            } 
                            var filteredArray = nuevos.filter(function(ele , pos){
                                return nuevos.indexOf(ele) == pos;
                            }) 
                            nuevos=filteredArray;
                            localStorage.setItem("listNew", JSON.stringify(nuevos));
                        }
                    });
                    monto_total_real = (monto_total_real.toFixed(2)).replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    monto_total_comision = (monto_total_comision.toFixed(2)).replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                    $('#SecTelDel_monto_total_comision_retiro').val(monto_total_comision);
                    $('#SecTelDel_monto_total_real_retiro').val(monto_total_real);

                    tabla_validaciones_datatable_formato_tlv_pag('#tabla_validaciones_retiro');
                    cargaPagina=1;
                    return false;
                }
                cargaPagina=1;            
            },
            error: function() {}
        });
    }else{
        var id_usuario_login = $("#sec_tlv_pag_id_usuario_login").val();
        var ftable = $('#tabla_validaciones_retiro').DataTable();
        ftable.clear();
        ftable.destroy();
        array_tabla_validaciones = [];
        var monto_total_real = 0;
        var monto_total_comision = 0;
        var ftable = $('#tabla_validaciones_retiro').DataTable({
            'destroy': true,
            'scrollX': true,
            "processing": true,
            "serverSide": true,
            "order" : [],
            "ajax": {
                type: "POST",
                async : true,
                "url": "/sys/set_televentas_pagador.php",
                "data": data
            },
            "order": [],
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
            },
            "columns": [
                { 
                    "data": "id",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).css('display', 'none');
                        $(td).addClass('sec_tlv_pag_listado_transaccion_id_transaccion');
                    }
                },
                { 
                    "data": "cod_transaction",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "tipo_operacion",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                        $(td).parent().css('background', item.color_celda);
                        $(td).parent().attr('id', item.id);
                        $(td).parent().addClass('sec_tlv_pag_listado_transaccion');
                    }
                },
                { 
                    "data": "fecha_hora_registro",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "fecha_hora_validacion",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "cajero",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "turno_local",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "cliente",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { 
                    "data": "cuenta",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "cuenta_num",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).attr('onclick', "copiarAlPortapapeles(this.id, '" + item.id + "', '" + item.estado_id  + "')");
                        $(td).attr('id','text-' + item.id);
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "cci",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "link_atencion",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    },
                    render: function (data, type,item,row) {
                        link = '<a href="' + item.link_atencion + '" target="_blank" style="text-transform: lowercase;">' + item.link_atencion + '</a>';
                        return link;
                    }
                },
                { 
                    "data": "banco_pago",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "monto",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "comision_monto",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "num_operacion",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "estado",
                    createdCell: function (td, cellData, item, row, col) {
                        if(item.estado == "PAGADO"){
                            $(td).css('color', 'green');
                        }else if(item.estado == "RECHAZADO"){
                            $(td).css('color', 'red');
                        }
                        $(td).css('font-weight', 'bold');
                        $(td).addClass('text-right');
                    }
                },
                { 
                    "data": "validador_nombre",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { 
                    "data": "razon",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { 
                    "data": "enviar_comprobante",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { 
                    "data": "validado_por",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { 
                    "data": "monto",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-right');
                    },
                    render: function (data, type,item,row) {
                        var ver = '';
                        var cuenta = "'" + item.cuenta + "'";
                        if(item.estado_id != 5 || (item.estado_id == 5 && item.update_user_id == id_usuario_login)){
                            ver = `<button type="button" class="btn btn-primary" 
                                    onclick="ver_detalle_retiro(${item.id}, ${item.estado_id}, ${item.cuenta_id}, ${cuenta}, 
                                    '${item.num_operacion}', '${item.cliente}', '${item.turno_local}', '${item.monto}', '${item.fecha_hora_registro}' 
                                    , '${item.cuenta_num}' , '${item.cci}' , 'CUENTA DE AHORROS','${item.tipo_rechazo_id}','${item.cuenta_pago_id}',
                                    '${item.cliente_id}','${item.razon}','${item.id_operacion_retiro}','${item.afect_balance}','${item.tipo_operacion}',
                                    '${item.motivo_devolucion}','${item.fecha_pago}')">
                                    <span class="glyphicon glyphicon-edit"></span></button>`;
                        }
                        return ver;
                    }
                }
            ]
        });
        sec_tlv_pag_obtener_totales();
    }
}
var rr_solicitudes_pendientes = [];
function actualizar_tabla_validaciones_retiro() {
    if(gen_tabla_validaciones_estado !== "1"){
        return;
    }
    var monto_total_real = 0;
    var monto_total_comision = 0;
    var nFilas = $("#tabla_validaciones_retiro tr").length;
    var data = {
        "accion": "actualizar_transacciones_x_estado",
        "ultimo_id": gen_tabla_validaciones_ultimo_id,
        "fecha_inicio": gen_tabla_validaciones_fecha_inicio,
        "fecha_fin": gen_tabla_validaciones_fecha_fin,
        "estado": gen_tabla_validaciones_estado,
        "cuenta" :gen_tabla_validaciones_cuenta,
        "validador" :gen_tabla_validaciones_validador,
        "tipo" :gen_tabla_validaciones_tipo,
        "validador_supervisor" : gen_tabla_validaciones_validador_supervisor,
        "transacciones_id" : transacciones_id,
        "cuenta_pago" : gen_tabla_validaciones_cuenta_pago,
        "tipo_saldo" : gen_tabla_validaciones_tipo_saldo
    }
    //auditoria_send({ "proceso": "actualizar_transacciones_x_estado", "data": data });
    $.ajax({
        url: "/sys/set_televentas_pagador.php",
        type: 'POST',
        data: data,
        beforeSend: function() {},
        complete: function() {},
        success: function(resp) {
            //console.log(resp);
            var respuesta = JSON.parse(resp);
            if(parseFloat(respuesta.result_tls_pagador_ultima_version)>0){
                if(parseFloat(tls_pagador_version)<parseFloat(respuesta.result_tls_pagador_ultima_version)){
                    alertify.error('El sistema ha sido actualizado. '+
                        'Ctrl+F5 (Si estás en PC) o Ctrl+Tecla Función +F5 (Si estás en laptop) o contactar con el área de soporte..', 5);
                }
            }
            if(respuesta.list_transacciones_canc > 0){
                limpiar_tabla_validaciones_retiros();
                listar_tabla_validaciones_retiros();
            }
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                /*$.each(respuesta.result, function(index2, item2) {
                    gen_tabla_validaciones_ultimo_id = item2.id;
                    array_tabla_validaciones.push(item2);
                });*/

                //limpiar_tabla_validaciones_retiros();
                
                $.each(respuesta.result, function(index, item) {
                    array_tabla_validaciones.push(item);

                    var color = 'blue';                    
                    var cuenta = "'" + item.cuenta + "'";
                    var monto_comision = "'" + item.comision_monto + "'";
                    var monto_comision_td =item.comision_monto.replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    var monto_real = "'" + item.monto + "'";
                    var monto_real_td =item.monto.replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                    /*monto_total_real = parseFloat(monto_total_real) + parseFloat(item.monto);
                    monto_total_comision = parseFloat(monto_total_comision) + parseFloat(item.comision_monto);*/

                    ver = `<button type="button" class="btn btn-primary" 
                                onclick="ver_detalle_retiro(${item.id}, ${item.estado_id}, ${item.cuenta_id}, ${cuenta}, 
                                '${item.num_operacion}', '${item.cliente}', '${item.turno_local}', '${item.monto}', '${item.fecha_hora_registro}' 
                                , '${item.cuenta_num}' , '${item.cci}' , 'CUENTA DE AHORROS','${item.tipo_rechazo_id}','${item.cuenta_pago_id}',
                                '${item.cliente_id}','${item.razon}','${item.id_operacion_retiro}','${item.afect_balance}','${item.tipo_operacion}',
                                '${item.motivo_devolucion}','${item.fecha_pago}')">
                                <span class="glyphicon glyphicon-edit"></span></button>`;
                    //var total = (parseFloat(item.monto) + parseFloat(item.comision_monto)).toFixed(2);
                    if(transacciones_id == ""){
                        transacciones_id = (item.id).toString();
                    }else{
                        transacciones_id = transacciones_id + ","+ (item.id).toString();
                    }
                    var h = rr_solicitudes_pendientes.filter(x=> x.id == item.id);
                    if(h.length > 0){
                        //console.log("nel | " + h);
                        return false;
                    }
                    $('#tabla_validaciones_retiro').append(
                        '<tr id="'+item.id+'" class="sec_tlv_pag_listado_transaccion">' +
                            '<td style="display:none;" class="sec_tlv_pag_listado_transaccion_id_transaccion">' + item.id + '</td>' +
                            '<td class="text-center">' + item.cod_transaction + '</td>' +
                            '<td class="text-center">' + item.tipo_operacion + '</td>' +
                            '<td class="text-center">' + item.fecha_hora_registro + '</td>' +
                            '<td class="text-center">' + item.fecha_hora_validacion + '</td>' +
                            '<td class="text-center">' + item.cajero + '</td>' +
                            '<td class="text-center">' + item.turno_local + '</td>' +
                            '<td class="text-right">' + item.cliente + '</td>' +
                            '<td class="text-center">' + item.cuenta + '</td>' +
                            '<td class="text-center">' + item.cuenta_num + '</td>' +
                            '<td class="text-center">' + item.cci + '</td>' +
                            '<td class="text-center">' + item.banco_pago + '</td>' +
                            '<td class="text-center">' + item.monto + '</td>' +
                            '<td class="text-center">' + item.comision_monto + '</td>' +
                            '<td class="text-center">' + item.num_operacion + '</td>' +
                            '<td class="text-right" style="' + color + '"><b>' + item.estado + '<b></td>' +
                            '<td class="text-right">' + item.validador_nombre + '</td>' +
                            '<td class="text-right">' + item.razon + '</td>' +
                            '<td class="text-right">' + item.enviar_comprobante + '</td>' +
                            // '<td class="text-right">' + item.tipo_operacion + '</td>' +
                            '<td class="text-right">' + item.validado_por + '</td>' +
                            '<td class="text-center">' + ver + '</td>' +
                        '</tr>'
                    );

                    //'<td class="text-center"><input type="checkbox" name="" class="sec_tlv_pag_checked_register_banco"></td>' +
                    
                    gen_tabla_validaciones_ultimo_id = item.id;
                    rr_solicitudes_pendientes.push({id : item.id});
                    item2 = item;
                    if(item.estado === 'PENDIENTE'){
                        var nuevos = [];
                        var notificacionOld = JSON.parse(localStorage.getItem("listNew"));                                 
                        var filteredArray = null;
                        if (notificacionOld !== null) {
                            console.log("no tiene not null | " + notificacionOld);
                            filteredArray = notificacionOld.filter(function(ele , pos){
                                return notificacionOld.indexOf(ele) == pos;
                            });
                            notificacionOld = filteredArray; 
                        } 
                        nuevos = notificacionOld == null ? [] : notificacionOld;  
                        var nuevosMaximoRegistro =array_tabla_validaciones.length;
                        var regMaximo =3;
                        var title = item2.cuenta + ' - ' + item2.cliente;
                        var message = 'NUEVA SOLICITUD DE RETIRO' ;
                        var icono = '../images/foco-azul.png' ;
                        if (cargaPagina!=0) {                                
                            if(notificacionOld!= null){  
                                let nuevaNotificacion = notificacionOld.includes(item2.id);
                                if(!nuevaNotificacion) {
                                    nuevos.push(item2.id);                                       
                                    notification(title, message+ icono); 
                                } 
                            } else{
                                notification(title, message+ icono); 
                                nuevos.push(item2.id); 
                            }
                        } else{
                            notification(title, message+ icono); 
                            nuevos.push(item2.id); 
                        }
                        var nuevos = nuevos.filter(function(ele , pos){
                            return nuevos.indexOf(ele) == pos;
                        });                              
                        localStorage.setItem("listNew", JSON.stringify(nuevos));
                    }
                                    
                });     
                /*monto_total_real = (monto_total_real.toFixed(2)).replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                monto_total_comision = (monto_total_comision.toFixed(2)).replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                $('#SecTelDel_monto_total_comision_retiro').val(monto_total_comision);
                $('#SecTelDel_monto_total_real_retiro').val(monto_total_real);*/

                //tabla_validaciones_datatable_formato_tlv_pag('#tabla_validaciones_retiro');
                return false;
            }
            
        },
        error: function() {}
    });
}
function exportar_tabla_validaciones_retiro(){
    var fecha_inicio = $("#buscador_fecha_inicio_retiro").val();
    var fecha_fin = $("#buscador_fecha_fin_retiro").val();
    var estado = $("#buscador_estado_transaccion_retiro").val();
    var cuenta = $("#buscador_cuenta_retiro").val();
    var validador = $('#buscador_pagador').val()!= '' ? gen_pagador_seleccionado : '0';
    var razon = $("#sec_tlv_pag_select_razon").val();
    var tipo = $("#sec_tlv_pag_select_tipo").val();
    var validador_supervisor = $("#sec_tlv_pag_validador").val();
    var cuenta_pago = $("#buscador_cuenta_pago").val();
    var tipo_saldo = $("#sec_tlv_pag_select_saldo").val();

    //console.log(fecha_inicio.replace(/-/g, ""));

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menos a la de fin.', 'warning');
        return false;
    }


    if(validador_supervisor == undefined){
        validador_supervisor = 0;
    }

    var data = {
        "accion": "listar_transacciones_retiros_export_xls",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "estado": estado,
        "cuenta": cuenta,
        "validador": validador,
        "razon": razon,
        "tipo": tipo,
        "validador_supervisor": validador_supervisor,
        "cuenta_pago" : cuenta_pago,
        "tipo_saldo" : tipo_saldo
    }
    $.ajax({
        url: "/sys/set_televentas_pagador.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            //console.log(respuesta);
            let obj = JSON.parse(resp);
            window.open(obj.path);
            loading(false);
        },
        error: function() {}
    });
}





//**********************************************************************************************************************************
//**********************************************************************************************************************************
// CUENTAS MULTIPLE SELECT
//**********************************************************************************************************************************
//**********************************************************************************************************************************

function activar_desactivar_usarios_cuentas_apt_retiro(params) {
    $.ajax({
        url: "/sys/get_usuario_banco.php",
        type: 'POST',
        data: params,
        async: true,
        success: function(resp) { //  alert(datat)
            var respuesta = JSON.parse(resp);
           // console.log(respuesta);
                 
        },        
        error: function() {}
    });
}
function activar_desactivar_usarios_cuentas_apt_retiro_exportar_plantilla(params) {
    $.ajax({
        url: "/sys/get_usuario_banco.php",
        type: 'POST',
        data: params,
        async: true,
        success: function(resp) { //  alert(datat)
            var respuesta = JSON.parse(resp);
           // console.log(respuesta);
                 
        },        
        error: function() {}
    });
}
function cargar_cargar_multiple_select_pagador(){

    $('#buscador_cuenta_retiro').multiselect({
           
            buttonClass:'form-control',
            buttonWidth: '100%',
            includeSelectAllOption: true, 
            onSelectAll: function(options) {
                $.each(options, function(index, item) {
                    //console.log(item[0].value);
                    data = {
                        'accion': 'crear_usuario_banco',
                        'id_cuenta_apt': item[0].value,
                        'activar':1
                    } 
                    activar_desactivar_usarios_cuentas_apt_retiro(data);
                });
            }, 
            onDeselectAll: function(options) {
                $.each(options, function(index, item) {
                    //console.log(item[0].value);
                    data = {
                        'accion': 'crear_usuario_banco',
                        'id_cuenta_apt': item[0].value,
                        'activar':0
                    } 
                    activar_desactivar_usarios_cuentas_apt_retiro(data);
                });
            },            
            onChange: function(element, checked) {
                var activar = 0;
                var id_cuenta =0;
                if (checked === true) {
                    activar =1;
                    id_cuenta=element.val();
                    
                }
                else if (checked === false) {
                    activar =0;
                    id_cuenta=element.val();          
                }
                data = {
                    'accion': 'crear_usuario_banco',
                    'id_cuenta_apt': id_cuenta,
                    'activar':activar
                }               
                
                activar_desactivar_usarios_cuentas_apt_retiro(data);
            }
    });

    $('#buscador_cuenta_retiro_exportar_plantilla').multiselect({
           
            buttonClass:'form-control',
            buttonWidth: '100%',
            includeSelectAllOption: true, 
            onSelectAll: function(options) {
                $.each(options, function(index, item) {
                    //console.log(item[0].value);
                });
            }, 
            onDeselectAll: function(options) {
                $.each(options, function(index, item) {
                    //console.log(item[0].value);
                });
            },            
            onChange: function(element, checked) {

                var activar = 0;
                var id_cuenta =0;
                if (checked === true) {
                    activar =1;
                    id_cuenta=element.val();
                    
                }
                else if (checked === false) {
                    activar =0;
                    id_cuenta=element.val();          
                }
            }
    });

    $('#buscador_cuenta_pago').multiselect({
           
            buttonClass:'form-control',
            buttonWidth: '100%',
            includeSelectAllOption: true, 
            onSelectAll: function(options) {
                
            }, 
            onDeselectAll: function(options) {
                
            },            
            onChange: function(element, checked) {
                var activar = 0;
                var id_cuenta =0;
                if (checked === true) {
                    activar =1;
                    id_cuenta=element.val();
                    
                }
                else if (checked === false) {
                    activar =0;
                    id_cuenta=element.val();          
                }
            }
    });
}
function sec_televentas_listar_bancos(){
    //var cuenta =$("#buscador_cuenta").val();
    var data ={
        "accion": "obtener_listado_bancos"
    }
    $.ajax({
        url: "/sys/set_televentas_pagador.php",
        type: 'POST',
        data: data,
        success: function(resp) { //  alert(datat)
            var respuesta = JSON.parse(resp);

            $.each(respuesta.result, function(index, item) {

                var seleccionado ="";
                if (item.activos!=0){seleccionado = "selected";}

                if(item.id != 1){
                    $('#buscador_cuenta_retiro').append(                    
                        '<option value="' + item.codigo +'" '+seleccionado+'>' +item.nombre+'</option>'                                 
                    );
                }
            });
        },
        complete: function() {
            cargar_cargar_multiple_select_pagador();
        },
        error: function() {}
    });
}







//**********************************************************************************************************************************
//**********************************************************************************************************************************
// RESULTADO
//**********************************************************************************************************************************
//**********************************************************************************************************************************
$(function() {
    if (sec_id == 'televentas_pagador') {
        $('#div_dp_comision_select_retiro').change(function() {
            //onchange_monto_calcular_total_retiro();
            return false;
        });
        $('#div_dp_btn_regresar_retiro').click(function() {
            //console.log("regresar");
            $("#div_dp_resultado_retiro").hide();
            $("#div_dp_resultado_footer_retiro").hide();
            $("#div_televentas_validaciones_retiros").show();
        });
    }
});

var gen_validacion_cuenta_yape = 0;
function limpiar_campos_div_dp_resultado_retiro() {
    gen_validacion_cuenta_yape = 0;
    $('#sec_tlv_pag_file_imagen').val('');
    $("#div_dp_imagen_lista_retiro").html('');
    $("#div_dp_cuenta_retiro").val('');
    $("#div_dp_registro_retiro").val('');
    $("#div_dp_num_operacion_retiro").val('');
    $('#div_dp_num_operacion_retiro').css('border', '');
    $("#div_dp_monto_retiro").val('');
    $("#div_dp_comision_select_retiro").val('0.00');
    $('#div_dp_comision_select_retiro').select2().trigger('change');
    $("#div_dp_monto_comision").val('');
    $("#div_dp_monto_real_retiro").val('');
    $("#div_dp_bono_select").val('0');
    //$("#div_dp_monto_bono").val('');
    $("#div_dp_monto_total").val('');
    $("#div_dp_motivo_retiro").val('0');
    $('#div_dp_motivo_retiro').select2().trigger('change');
    $("textarea#div_dp_observacion_cajero").val('');
    $("textarea#div_dp_observacion_validador").val('');

    $("#div_dp_motivo_contenedor_retiro").hide();
    $("#div_dp_btn_rechazar").show();
    $("#div_dp_btn_confirmar_rechazo_retiro").hide();
}
var estado_transaccion_detalle = 0;
var gen_id_cliente = 0;
var gen_id_transaccion = 0;
var gen_cuenta_id_register = 0;
var gen_banco_pago_id_register = 0;
function ver_detalle_retiro(id_transaccion, id_estado, cuenta_id, cuenta, num_operacion, cliente_nombre, turno_local, monto_retiro, registro_retiro,
                            num_cuenta, num_cuenta_cci, tipo_cuenta, tipo_rechazo_id, cuenta_pago_id, cliente_id, razon, id_operacion_retiro, 
                            afect_balance, tipo_operacion, motivo_devolucion, fecha_pago) {
    if(parseInt(id_estado) == 2){
        $('.SecTelPag_edit_control').show();
        gen_cuenta_id_register = cuenta_id;
        gen_banco_pago_id_register = cuenta_pago_id;
        gen_num_ope_register = num_operacion;
        editar_detalle_retiro(tipo_operacion,cuenta_pago_id, num_operacion);
    }else{
        $('.SecTelPag_edit_control').hide();
    }
    gen_id_transaccion = id_transaccion;
    estado_transaccion_detalle = id_estado;
    gen_id_cliente = cliente_id;
    $('#div_dp_num_operacion_retiro').prop( "disabled", true);
        $('#sec_tlv_pag_motivo_devolucion_div').hide();
    limpiar_campos_div_dp_resultado_retiro();
    if(id_estado == 1 || id_estado == 5){ // Si está pendiente o en proceso, actualizarlo a EN PROCESO
        $("#sec_tlv_pag_subir_img").show();
        $("#sec_tlv_pag_file_imagen").removeAttr('src')
        sec_tlv_pag_set_estado_transaccion(id_transaccion, 5);
        $('#selc_tlv_pag_div_btn_regresar').html('');
        $('#selc_tlv_pag_div_btn_regresar').append(
            '<button type="button" class="btn btn-default pull-left" onclick="sec_tlv_pag_regresar_btn(1)"><b><i class="fa fa-close"></i> REGRESAR</b>'
        );
    }else{
        $('#selc_tlv_pag_div_btn_regresar').html('');
        $('#selc_tlv_pag_div_btn_regresar').append(
            '<button type="button" class="btn btn-default pull-left" onclick="sec_tlv_pag_regresar_btn(0)"><b><i class="fa fa-close"></i> REGRESAR</b>'
        );
    }
    
    if(tipo_operacion == "DEVOLUCIÓN"){
        $('#sec_tlv_pag_motivo_devolucion_div').show();
        $('#sec_tlv_pag_motivo_devolucion').val(motivo_devolucion);
    }
    if(tipo_operacion == "PROPINA"){
        $('.sectlvpag_control_propina').show();
        $('#secTelPag_div_edit_image_comprobante_propina').show();
    }else{
        $('.sectlvpag_control_propina').hide();
        $('#secTelPag_div_edit_image_comprobante_propina').hide();
    }

    /*********************************************************************************************/
    /*var data ={
        "accion": "sec_tlv_pagador_obtener_supervisor_pagador_turno"
    }
    $.ajax({
        url: "/sys/set_televentas_pagador.php",
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

            $.each(respuesta.result, function(index, item) {
                supervisor_turno_id = item.supervisor;
                pagador_turno_id = item.pagador;
            });
        },
        complete: function() {
            
        },
        error: function() {}
    });*/
    /*********************************************************************************************/

    $('#div_dp_comision_select_retiro').prop("disabled", true);
    $('#div_dp_registro_retiro').prop("disabled", true);

    //OBTENER DATOS
    $("#nombre_cliente_retiro").text(cliente_nombre + ' | ' + turno_local);
    $('#nombre_cliente_retiro').attr('data-text', cliente_nombre);
    $("#div_televentas_validaciones_retiros").hide();
    $("#div_dp_resultado_retiro").show();
    cargar_voucher_propina_pagado(id_transaccion);   
    $('#sec_tlv_pag_file_imagen').html('');
    $('#sec_tlv_pag_ver_imagen').attr('src','');
    $('#sec_tlv_pag_id_operacion').val(id_operacion_retiro);
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    var temp_fecha_hora_actual = now.toISOString().slice(0, -5);

    $("#input_tlv_dep_id_transaccion").val(id_transaccion);
    $("#div_dp_cuenta_retiro").val(cuenta);
    $("#div_dp_id_cuenta").val(cuenta_id);
    $("#div_dp_tipo_cuenta_retiro").val(tipo_cuenta);
    $("#div_dp_num_cuenta_retiro").val(num_cuenta);
    $("#div_dp_num_cuenta_cci_retiro").val(num_cuenta_cci);
    $('#sec_tlv_pag_select_banco_pago').val(cuenta_pago_id).trigger('change.select2');
    $('#sec_tlv_pag_select_operacion').val(razon);
    $('#sec_tlv_pag_select_banco_pago').prop("disabled", true);
    $("#div_dp_num_operacion_retiro").val(num_operacion);
    $('#div_dp_observacion_validador').prop('disabled', true);
    //$("#div_dp_comision_select_retiro").val(monto_comision);
    monto_retiro = (parseFloat(monto_retiro).toFixed(2)).replace(/\D/g, "")
                        .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    $("#div_dp_monto_retiro").val(monto_retiro);
    $("#div_dp_monto_real_retiro").val(monto_retiro);
    $("#sec_tlv_pag_tipo_operacion").val(tipo_operacion);

    if ([1,5,6].includes(id_estado)) { // pendiente || proceso
        $("#div_dp_resultado_footer_retiro").show();
        $("#div_dp_registro_retiro").val(temp_fecha_hora_actual.substr(0,16));
        $('#div_dp_comision_select_retiro').prop("disabled", false);
        $('#sec_tlv_pag_select_banco_pago').prop("disabled", false);
        $('#sec_tlv_pag_select_banco_pago').val(0).trigger('change.select2');
        $('#div_dp_observacion_validador').prop('disabled', false);
        $('#div_dp_motivo_retiro').prop( "disabled", false);
    $('#div_dp_registro_retiro').prop("disabled", false);
    } else if (parseInt(id_estado) === 3) { //rechazo
        var motivo_rechazo_val = tipo_rechazo_id + "|" + afect_balance;
        $("#div_dp_motivo_contenedor_retiro").show();
        $('#div_dp_motivo_retiro').val(motivo_rechazo_val).trigger('change.select2');
        $('#div_dp_motivo_retiro').prop( "disabled", true );
    } else {
        $("#div_dp_registro_retiro").val(registro_retiro.substr(0,16).replace(" ", "T"));

    }

    if (parseInt(id_estado) === 2) { //pagado
        $('#sec_tlv_dep_div_edit').show();
        $('#div_dp_registro_retiro').val(fecha_pago);
    }else{
        $('#sec_tlv_dep_div_edit').hide();
    }

    //onchange_monto_calcular_total_retiro();
    $('#div_dp_btn_rechazar').removeAttr('onclick');
    $('#div_dp_btn_rechazar').attr('onclick', 'mostrar_opciones_rechazo_retiro()');
    $('#div_dp_btn_confirmar_rechazo_retiro').removeAttr('onclick');
    $('#div_dp_btn_confirmar_rechazo_retiro').attr('onclick', "guardar_validacion_retiro(" + id_transaccion + ", 3, " + cuenta_id + ")");
    $('#div_dp_btn_validar_retiro').removeAttr('onclick');
    $('#div_dp_btn_validar_retiro').attr('onclick', "guardar_validacion_retiro(" + id_transaccion + ", 2, " + cuenta_id + ")");

    if(![1,5,6].includes(id_estado)){
        $("#sec_tlv_pag_subir_img").hide();
    }else{
        $("#sec_tlv_pag_subir_img").show();
    }
    if(id_estado == 2){
        var data = {
            "accion": "obtener_imagenes_x_transaccion_retiro",
            "id_transaccion": id_transaccion
        }
        auditoria_send({ "proceso": "obtener_imagenes_x_transaccion_retiro", "data": data });
        $.ajax({
            url: "/sys/set_televentas_pagador.php",
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
                //console.log(respuesta);
                if (parseInt(respuesta.http_code) == 400) {
                    swal('Aviso', respuesta.status, 'warning');
                    return false;
                }
                if (parseInt(respuesta.http_code) == 200) {
                    $.each(respuesta.result, function(index, item) {
                        var nuevo_id = (respuesta.fecha_hora).toString() + '_' + (item.id).toString();
                        $('#div_dp_imagen_lista_retiro').append(
                            '<div class="col-md-12">' +
                            '   <div align="center">' +
                            '       <img id="' + nuevo_id + '" src="files_bucket/retiros/' + item.archivo + '" style="max-width: 450px;" />' +
                            '   </div>' +
                            '</div>'
                        );
                        $("#" + nuevo_id).imgViewer2();
                    });
                    return false;
                }
            },
            error: function() {}
        });
    }
    
}
function onchange_monto_calcular_total_retiro() {
    var monto = $('#div_dp_monto_retiro').val().replace(/\,/g, '');
    var comision_select = $('#div_dp_comision_select_retiro').val();
    var bono_select = $('#div_dp_bono_select').val();
    //console.log('onchange_deposito_calcular_total');
    //console.log(bono_select);
    var comision = 0;
    var monto_real = 0;
    var bono = 0;
    var total = 0;
    if (parseFloat(monto) > 0) {
        monto_real = monto;
        if(parseFloat(comision_select)>0 && parseFloat(monto)<80) {
            monto_real = (parseFloat(monto) - parseFloat(comision_select)).toFixed(2);
        }
        total = monto_real;
        if(parseFloat(monto_real)>= 40){
            if(parseInt(bono_select)>0){
                total = (parseFloat(monto_real) * 1.05).toFixed(2);
                bono = (parseFloat(monto_real) * 0.05).toFixed(2);
            }
        }
    }
    //console.log('onchange_deposito_calcular_total');
    comision = (parseFloat(comision).toFixed(2)).replace(/\D/g, "")
                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    monto_real = (parseFloat(monto_real).toFixed(2)).replace(/\D/g, "")
                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    /*bono = (parseFloat(bono).toFixed(2)).replace(/\D/g, "")
                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");*/
    total = (parseFloat(total).toFixed(2)).replace(/\D/g, "")
                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    $('#div_dp_monto_comision').val(comision);
    $('#div_dp_monto_real_retiro').val(monto_real);
    //$('#div_dp_monto_bono').val(bono);
    $('#div_dp_monto_total').val(total);
    return false;
}


function mostrar_opciones_rechazo_retiro() {
    $("#div_dp_motivo_contenedor_retiro").show();
    $("#div_dp_btn_rechazar").hide();
    $("#div_dp_btn_confirmar_rechazo_retiro").show();
}

function limpiar_bordes_validacion_retiro() {
    $('#div_dp_registro_div').css('border', '');
    $('#div_dp_num_operacion_div_retiro').css('border', '');
    $('#div_dp_monto').css('border', '');
    $('#div_dp_motivo_contenedor_retiro').css('border', '');
    $('#div_dp_observacion').css('border', '');
}

function cargar_voucher_propina_pagado(id_transaccion) {
	var data = {
		"accion": "obtener_imagenes_x_transaccion_propina",
		"id_transaccion": id_transaccion
	}
	//auditoria_send({"proceso": "obtener_imagenes_x_transaccion_propina", "data": data});
	$.ajax({
		url: "/sys/set_televentas_pagador.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) { //  alert(datat)
			$('#sec_tlv_pag_img_referencia_propina').html('');
			var respuesta = JSON.parse(resp);
			//console.log(respuesta);
			if (parseInt(respuesta.http_code) == 400) {

				//swal('Aviso', respuesta.status, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
                $('#sec_tlv_pag_div_img_referencia_propina').show();
                $("#sec_tlv_pag_btn_admin_cuentas").hide();
				$.each(respuesta.result, function (index, item) {
					var nuevo_id = (respuesta.fecha_hora).toString() + '_' + (item.id).toString();
					$('#sec_tlv_pag_img_referencia_propina').append(
							'<div class="col-md-12">' +
							'   <div align="center">' +
							'       <img  id="sec_tlv_pag_imagen_referencia_propina" src="files_bucket/propinas/' + item.archivo + '" style="max-width: 400px; height: 300px;"/>' +
							'   </div>' +
							'</div>'
							);
					$("#sec_tlv_pag_imagen_referencia_propina").imgViewer2();
				});
				return false;
			}
		},
		error: function () {}
	});
}

var supervisor_turno_id = 0;
var pagador_turno_id = 0;
function guardar_validacion_retiro(id_transaccion, id_estado, cuenta_id) {
    $("#div_dp_resultado_footer_retiro").hide();
    limpiar_bordes_validacion_retiro();

    var registro = $.trim($('#div_dp_registro_retiro').val()) + ":00";
    var num_operacion = $.trim($('#div_dp_num_operacion_retiro').val());
    var monto_retiro = $("#div_dp_monto_retiro").val().replace(/\,/g, '');
    var monto_comision = $("#div_dp_comision_select_retiro").val().replace(/\,/g, '');
    var monto_real = $("#div_dp_monto_real_retiro").val().replace(/\,/g, '');
    var id_motivo_ent = $("#div_dp_motivo_retiro").val();
    var mot = id_motivo_ent.split("|");
    var motivo = mot[0];
    var afect_balance = mot[1];
    var observacion_pagador = $("textarea#div_dp_observacion_validador").val();
    var imagen = $('#sec_tlv_pag_ver_imagen').attr("src");
    var imagen_name = $('#sec_tlv_pag_file_imagen').val();
    var imagen_extension = imagen_name.substring(imagen_name.lastIndexOf("."));
    var cuenta_pago_id = $('#sec_tlv_pag_select_banco_pago').val();
    var operation = $('#sec_tlv_pag_id_operacion').val();
    

    if (registro.length !== 19) {
        $("#div_dp_resultado_footer_retiro").show();
        $("#div_dp_registro_div").css("border", "1px solid red");
        $('#div_dp_registro_retiro').focus();
        return false;
    }
    //console.log("monto_retiro: " + monto_retiro);
    if (!(parseFloat(monto_retiro) > 0)) {
        $("#div_dp_resultado_footer_retiro").show();
        $('#div_dp_monto_retiro').css('border', '1px solid red');
        $('#div_dp_monto_retiro').focus();
        return false;
    }
    //console.log("monto_real: " + monto_real);
    if (!(parseFloat(monto_real) > 0)) {
        $("#div_dp_resultado_footer_retiro").show();
        $('#div_dp_monto_retiro').css('border', '1px solid red');
        $('#div_dp_monto_retiro').focus();
        return false;
    }

    if(imagen == "" && id_estado == 2){
        $("#div_dp_resultado_footer_retiro").show();
        swal('Aviso', "Debe agregar el comprobante de pago.", 'warning');
        return false;
    }

    if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
        imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
        imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG" && id_estado == 2) {
        $("#div_dp_resultado_footer_retiro").show();
        swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
        return false;
    }
    
    if(cuenta_pago_id == 0 && id_estado == 2){
        $("#div_dp_resultado_footer_retiro").show();
        swal('Aviso', "Debe seleccionar la cuenta con la que se está realizando el pago.", 'warning');
        return false;
    }

    if (num_operacion == "" && id_estado == 2) {
        $("#div_dp_resultado_footer_retiro").show();
        $('#div_dp_num_operacion_retiro').css('border', '1px solid red');
        $('#div_dp_num_operacion_retiro').focus();
        return false;
    }

    if (parseInt(id_estado) === 3) {
        if (!(parseInt(motivo) > 0)) {
            $("#div_dp_resultado_footer_retiro").show();
            $('#div_dp_motivo_contenedor_retiro').css('border', '1px solid red');
            $('#div_dp_motivo_retiro').focus();
            return false;
        }
    }

    var f_imagen = $("#sec_tlv_pag_file_imagen")[0].files[0];
    var data = new FormData();
    data.append('accion', "guardar_validacion_solicitud_retiro");
    data.append('id_transaccion', id_transaccion);
    data.append('id_estado', id_estado);
    data.append('cuenta_id', cuenta_id);
    data.append('observacion', observacion_pagador);
    data.append('registro', registro);
    data.append('num_operacion', num_operacion);
    data.append('monto_retiro', monto_retiro);
    data.append('monto_comision', monto_comision);
    data.append('monto_real', monto_real);
    data.append('motivo', motivo);
    data.append('sec_tlv_pag_file_imagen', f_imagen);
    data.append('cuenta_pago_id', cuenta_pago_id);
    data.append('operation', operation);
    //data.append('supervisor_turno_id', supervisor_turno_id);
    //data.append('pagador_turno_id', pagador_turno_id);

    $.ajax({
        url: "/sys/set_televentas_pagador.php",
        type: 'POST',
        data: data,
        processData: false,
        cache: false,
        contentType: false,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) { //  alert(datat)
            //console.log(resp);
            debugger;
            var respuesta = JSON.parse(resp);
            auditoria_send({ "respuesta": "guardar_validacion_solicitud_retiro", "data": respuesta });
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                $("#div_dp_resultado_footer_retiro").show();
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $("#div_dp_resultado_retiro").hide();
                $("#div_dp_resultado_footer_retiro").hide();
                $("#div_televentas_validaciones_retiros").show();
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                if(afect_balance == 1){
                    $.post("/sys/set_televentas_pagador.php", {
                        accion: "eliminar_transaccion_pago_apuesta",
                        cliente_id: gen_id_cliente,
                        trans_id: id_transaccion,
                        tipo_id: 5,
                        observacion: ""
                    })
                    .done(function (data) {
                        try {
                            var respuesta = JSON.parse(data);
                            if (parseInt(respuesta.http_code) == 400) {
                                listar_tabla_validaciones_retiros();
                                swal({ title: 'Error', text: respuesta.status, type: 'error', timer: 1500, showConfirmButton: false });
                                return false;
                            }
                            if (parseInt(respuesta.http_code) == 200) {
                                listar_tabla_validaciones_retiros();
                                swal({ title: 'Reversión de Pago de Apuesta Exitoso.', text: '', type: 'success', timer: 1500, showConfirmButton: false });
                                return false;
                            }
                        } catch (e) {
                            swal('¡Error!', e, 'error');
                            console.log("Error de TRY-CATCH --> Error: " + e);
                            return false;
                        }
                    })
                    .fail(function (xhr, status, error) {
                        $('#modal_observacion_supervisor').modal('hide');
                        swal('¡Error!', error, 'error');
                        console.log("Error de .FAIL -- Error: " + error);
                        return false;
                    });
                }else{
                    listar_tabla_validaciones_retiros();
                    return false;
                }
            }
            return false;
        },
        error: function(result) {
            auditoria_send({ "respuesta": "guardar_validacion_solicitud_retiro_error", "data": result });
            return false;
        }
    });
    return false;
}

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

$("#sec_tlv_pag_file_imagen").change(function (e) {
    // Código a ejecutar cuando se detecta un cambio de archivO
    var filePath = this.value;

    var allowedExtensions = /(.jpg|.jpeg|.png|.gif)$/i;
    if(!allowedExtensions.exec(filePath)){
        swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
        this.value = '';
        return false;
    }else{
        var newPath = filePath.replace('jpg','png');
        //$("#sec_tlv_pag_file_imagen").val(newPath); 
        //this.files[0].name = newPath;
        readImage(this);
    }

});

function readImage (input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#sec_tlv_pag_ver_imagen').attr('src', e.target.result); // Renderizamos la imagen
            $('#div_dp_num_operacion_retiro').prop( "disabled", false );
        }
        //$("#sec_tlv_pag_file_imagen").val("C:/fakepath/288868804_556407445975184_3044994456328067038_n.png");
        console.log(input.files[0].name);
        reader.readAsDataURL(input.files[0]);
        $("sec_tlv_pag_ver_imagen").imgViewer2();
    }
}

function obtener_fecha_hora_retiro() {
    $.post("/sys/set_televentas.php", {
            accion: "obtener_fecha_hora"
        })
        .done(function(data) {
            try {
                var respuesta = JSON.parse(data);
                if (parseInt(respuesta.http_code) == 200) {
                    var_fecha_hora = respuesta.result;
                }
            } catch (e) {
                console.log("Error de TRY-CATCH -- Error: " + e);
            }
        })
        .fail(function(xhr, status, error) {
            console.log("Error de .FAIL -- Error: " + error);
        });

}

//comentado por el momento | Exportar plantilla de pagos
/*function obtenerRegistrosSeleccionados(){
    var banco = $("#buscador_cuenta_retiro_exportar_plantilla").val();
    var rr_data_seleccionados = [];
    $('.sec_tlv_pag_checked_register_banco:checked').each(function(indice, elemento){
        var fila = $(this).parents(".sec_tlv_pag_listado_transaccion");
        id_transaccion = fila.find(".sec_tlv_pag_listado_transaccion_id_transaccion").html();
        rr_data_seleccionados.push(id_transaccion);
    });
    console.log(banco);
    console.log(rr_data_seleccionados);
}*/

function sec_tlv_pag_set_estado_transaccion(id_trans, id_estado){
    var data = {
        "accion": "cambiar_estado_solicitud_retiro",
        "id_transaccion": id_trans,
        "id_estado": id_estado
    }
    auditoria_send({ "proceso": "cambiar_estado_solicitud_retiro", "data": data });
    $.ajax({
        url: "/sys/set_televentas_pagador.php",
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
            if (parseInt(respuesta.http_code) == 200) {
                //console.log("Solicitud En Proceso")
            }
        },
        error: function() {}
    });
}

//comentado por el momento | Exportar plantilla de pagos
/*$(document).on("change", ".sec_tlv_pag_checked_register_banco", function() {
    console.log("llegó");
    if ($(".sec_tlv_pag_checked_register_banco").length == $(".sec_tlv_pag_checked_register_banco:checked").length) {
        $("#sec_tlv_pag_check_todo").prop("checked", true);
    } else {
        $("#sec_tlv_pag_check_todo").prop("checked", false);
    }
});*/

//if (sec_id === 'televentas_pagador' && (estado_transaccion_detalle == 1 || estado_transaccion_detalle == 5)){

window.addEventListener("paste", function(e){
    if (sec_id === 'televentas_pagador' && [1,5,6].includes(estado_transaccion_detalle)){
        retrieveImageFromClipboardAsBlob(e, function(imageBlob){
            // If there's an image, display it in the canvas
            var allowedExtensions = /(.jpg|.jpeg|.png|.gif)$/i;
            if(imageBlob){
                let fileInputElement = document.getElementById('sec_tlv_pag_file_imagen');
                let container = new DataTransfer();
                let data = imageBlob;
                
                if(!allowedExtensions.exec(imageBlob.name)){
                    swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
                    fileInputElement.value = '';
                    return false;
                }
                let img_nombre=new Date().getTime();
                let file = new File([data], img_nombre+".png",{type:"image/jpeg", lastModified:img_nombre});
                container.items.add(file);
                fileInputElement.files = container.files;

                var filePath = fileInputElement.value;
                if(!allowedExtensions.exec(filePath)){
                    swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
                    fileInputElement.value = '';
                    return false;
                }else{
                    readImage(fileInputElement);
                }
            }
        });
    }
}, false);
//}

$('#sec_tlv_pag_btn_admin_cuentas').on('click', function(e) {
    if (!document.getElementById("sec_tlv_pag_imagen_referencia_propina")) {
        // var fullPath = document.getElementById("sec_tlv_pag_imagen_referencia_propina").src();

        /* if(fullPath == window.location){
            console.log("No hay imagen: Esto es un retiro"); */
        sec_tlv_pag_modal_retiro_obtener_cuentas();
        sec_tlv_pag_modal_retiro_disponible();
        $('#sec_tlv_pag_btn_confirmar_cambio_cuenta').hide();
        $('#sec_tlv_pag_btn_actualizar_solicitud').show();
        $('#sec_tlv_pag_modal_retiro_btn_agregar_cuenta').show();
        $('#sec_tlv_pag_modal_retiro').modal();
        // }

    
    /* var fullPath = document.getElementById("sec_tlv_pag_imagen_referencia_propina").src();
        console.log(fullPath); */

    
    } else {
        // Aca solo el nombre imagen
        /* var filename = fullPath.replace(/^.*[\\\/]/, '');
        console.log("nombre imagen: " + filename); */
            sec_tlv_pag_modal_propina_obtener_cuentas();
            sec_tlv_pag_modal_retiro_disponible();
            $('#sec_tlv_pag_modal_propina').modal();
    }
    /* sec_tlv_pag_modal_retiro_obtener_cuentas();
    sec_tlv_pag_modal_retiro_disponible();
    $('#sec_tlv_pag_modal_retiro').modal(); */
    });

// RETIRO
function sec_tlv_pag_modal_retiro_obtener_cuentas() {
    var data = {
        accion: "obtener_cuentas_x_cliente",
        cliente_id: gen_id_cliente
    }
    $.ajax({
        url: "/sys/set_televentas_pagador.php",
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
            $('#sec_tlv_pag_modal_cuentas_tabla tbody').html('');
            auditoria_send({ "proceso": "obtener_cuentas_x_cliente", "data": respuesta });
            if (parseInt(respuesta.http_code) == 200) {
                //$('#modal_retiro_cuenta').append('<option value="0">:: Seleccione ::</option>');
                //$('#modal_retiro_cuenta').val('0');

                $.each(respuesta.result, function(index, item) {
                    var btn_edit = '';
                    var btn_delete = '';
                    var variables = item.cod + "," + item.banco_id + ",'" + item.cuenta_num_ent + "', '" + item.cci_ent + "'";
                    btn_edit = '<button type="button" class="btn btn-info btn-sm" onclick="sec_tlv_pag_edit_cuenta(2,'+variables+')"><i class="fa fa-edit"></i></button>';
                    btn_delete = '<button style="margin-left: 10px;" type="button" class="btn btn-danger btn-sm" onclick="sec_tlv_pag_edit_cuenta(3,'+variables+')"><i class="fa fa-close"></i></button>';
                    $('#sec_tlv_pag_modal_cuentas_tabla tbody').append(
                        '<tr class="listado_cuentas_bancarias_clientes" for="'+item.cod+'_'+item.cuenta_num_cliente+'">'+
                        '   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_id_cuenta_cliente" value="'+item.cod+'" /></td>'+
                        '   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_id_banco_cuenta_cliente" value="'+item.banco_id+'" /></td>'+
                        '   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_num_cuenta_cliente_total" value="'+item.cuenta_num_ent+'" /></td>'+
                        '   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_num_cuenta_cci_cliente_total" value="'+item.cci_ent+'" /></td>'+
                        '   <td style="font-weight: bold; text-align: center;" >'+
                        '       <label class="sec_tlv_nom_cuenta_cliente">'+item.banco+'</label>'+
                        '   </td>'+
                        '   <td style="font-weight: bold; text-align: center;">'+
                        '       <label class="sec_tlv_num_cuenta_cliente">'+item.cuenta_num+'</label>'+
                        '   </td>'+
                        '   <td style="font-weight: bold; text-align: center;">'+
                        '       <label class="sec_tlv_num_cci_cuenta_cliente">'+item.cci+'</label>'+
                        '   </td>'+
                        '   <td style="text-align: center;">' + btn_edit + btn_delete + '</td>'+
                        '   <td style="text-align: center;">'+
                        '       <input type="radio" name="sec_tlv_pag_sec_cuentas_cliente" id="'+item.cod+'_'+item.cuenta_num_cliente+'" class="sec_tlv_pag_sec_cuentas_cliente"/>'+
                        '   </td>'+
                        '</tr>'
                        );
                });
            }else{
                //$('#modal_retiro_cuenta').append('<option value="0">:: No existen cuentas ::</option>');
                /*$('#sec_tlv_pag_modal_cuentas_tabla tbody').append(
                    '<tr>'+
                    '   <td colspan="2">No hay cuentas registradas</td>'+
                    '</tr>'
                );*/
                //('#modal_retiro_cuenta').val('0');
            }
            return false;
        },
        error: function(result) {
            auditoria_send({ "proceso": "obtener_cuentas_x_cliente_error", "data": result });
            return false;
        }
    });
}

    // PROPINAS
    function sec_tlv_pag_modal_propina_obtener_cuentas() {
        var data = {
            accion: "obtener_cuentas_x_cajero",
            cliente_id: gen_id_cliente,
            id_transaccion : gen_id_transaccion
        }
        $.ajax({
            url: "/sys/set_televentas_pagador.php",
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
                $('#sec_tlv_pag_modal_cuentas_tabla_propina tbody').html('');
                //auditoria_send({ "proceso": "obtener_cuentas_x_cliente", "data": respuesta });
                if (parseInt(respuesta.http_code) == 200) {
                    //$('#modal_retiro_cuenta').append('<option value="0">:: Seleccione ::</option>');
                    //$('#modal_retiro_cuenta').val('0');
    
                    $.each(respuesta.result, function(index, item) {
                        var btn_edit = '';
                        var btn_delete = '';
                        var variables = item.cod + "," + item.banco_id + ",'" + item.cuenta_num_ent + "', '" + item.cci_ent + "'";
                        btn_edit = '<button type="button" class="btn btn-info btn-sm" onclick="sec_tlv_pag_edit_cuenta(2,'+variables+')"><i class="fa fa-edit"></i></button>';
                        btn_delete = '<button style="margin-left: 10px;" type="button" class="btn btn-danger btn-sm" onclick="sec_tlv_pag_edit_cuenta(3,'+variables+')"><i class="fa fa-close"></i></button>';
                        $('#sec_tlv_pag_modal_cuentas_tabla_propina tbody').append(
                            '<tr class="listado_cuentas_bancarias_cajero" for="'+item.cod+'_'+item.cuenta_num_cliente+'">'+
                            '   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_id_cuenta_cajero" value="'+item.cod+'" /></td>'+
                            '   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_id_banco_cuenta_cajero" value="'+item.banco_id+'" /></td>'+
                            '   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_num_cuenta_cajero_total" value="'+item.cuenta_num_ent+'" /></td>'+
                            '   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_num_cuenta_cci_cajero_total" value="'+item.cci_ent+'" /></td>'+
                            '   <td style="font-weight: bold; text-align: center;" >'+
                            '       <label class="sec_tlv_nom_cuenta_cajero">'+item.banco+'</label>'+
                            '   </td>'+
                            '   <td style="font-weight: bold; text-align: center;">'+
                            '       <label class="sec_tlv_num_cuenta_cajero">'+item.cuenta_num+'</label>'+
                            '   </td>'+
                            '   <td style="font-weight: bold; text-align: center;">'+
                            '       <label class="sec_tlv_num_cci_cuenta_cajero">'+item.cci+'</label>'+
                            '   </td>'+
                            // '   <td style="text-align: center;">' + btn_edit + btn_delete + '</td>'+
                            '   <td style="text-align: center;">'+
                            '       <input type="radio" name="sec_tlv_pag_sec_cuentas_cajero" id="'+item.cod+'_'+item.cuenta_num_cliente+'" class="sec_tlv_pag_sec_cuentas_cajero"/>'+
                            '   </td>'+
                            '</tr>'
                            );
                    });
                }else{
                    //$('#modal_retiro_cuenta').append('<option value="0">:: No existen cuentas ::</option>');
                    /*$('#sec_tlv_pag_modal_cuentas_tabla tbody').append(
                        '<tr>'+
                        '   <td colspan="2">No hay cuentas registradas</td>'+
                        '</tr>'
                    );*/
                    //('#modal_retiro_cuenta').val('0');
                }
                return false;
            },
            error: function(result) {
                //auditoria_send({ "proceso": "obtener_cuentas_x_cliente_error", "data": result });
                return false;
            }
        });
    }
    
function sec_tlv_pag_modal_retiro_disponible() {
    /*var data = {
        accion: "obtener_balance_retiro_disponible",
        cliente_id: gen_id_cliente
    }
    $.ajax({
        url: "/sys/set_televentas_pagador.php",
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
            auditoria_send({ "proceso": "obtener_cuentas_x_cliente", "data": respuesta });
            if (parseInt(respuesta.http_code) == 200) {
                //$('#modal_retiro_cuenta').append('<option value="0">:: Seleccione ::</option>');
                //$('#modal_retiro_cuenta').val('0');

                $.each(respuesta.result, function(index, item) {
                    var btn_edit = '';
                    var btn_delete = '';
                    var variables = item.cod + ",'" + item.cuenta_num_ent + "', '" + item.cci_ent + "'";
                    btn_edit = '<button type="button" class="btn btn-info btn-sm" onclick="sec_tlv_pag_edit_cuenta('+variables+')"><i class="fa fa-edit"></i></button>';
                    btn_delete = '<button style="margin-left: 10px;" type="button" class="btn btn-danger btn-sm" onclick="sec_tlv_pag_edit_cuenta('+variables+')"><i class="fa fa-close"></i></button>';
                    $('#sec_tlv_pag_modal_cuentas_tabla tbody').append(
                        '<tr class="listado_cuentas_bancarias_clientes" for="'+item.cod+'_'+item.cuenta_num_cliente+'">'+
                        '   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_id_cuenta_cliente" value="'+item.cod+'" /></td>'+
                        '   <td style="font-weight: bold; text-align: center; display: none;"><input class="sec_tlv_id_banco_cuenta_cliente" value="'+item.banco_id+'" /></td>'+
                        '   <td style="font-weight: bold; text-align: center;" >'+
                        '       <label for="'+item.cod+'_'+item.cuenta_num_cliente+'" class="sec_tlv_nom_cuenta_cliente">'+item.banco+'</label>'+
                        '   </td>'+
                        '   <td style="font-weight: bold; text-align: center;">'+
                        '       <label for="'+item.cod+'_'+item.cuenta_num_cliente+'" class="sec_tlv_num_cuenta_cliente">'+item.cuenta_num+'</label>'+
                        '   </td>'+
                        '   <td style="font-weight: bold; text-align: center;">'+
                        '       <label for="'+item.cod+'_'+item.cuenta_num_cliente+'" class="sec_tlv_num_cci_cuenta_cliente">'+item.cci+'</label>'+
                        '   </td>'+
                        '   <td>' + btn_edit + btn_delete + '</td>'+
                        '   <td style="text-align: center;"></td>'+
                        '</tr>'
                        );
                });
            }else{
                //$('#modal_retiro_cuenta').append('<option value="0">:: No existen cuentas ::</option>');
                $('#sec_tlv_pag_modal_cuentas_tabla tbody').append(
                        '<tr>'+
                        '   <td colspan="2">No hay cuentas registradas</td>'+
                        '</tr>'
                        );
                //('#modal_retiro_cuenta').val('0');
            }
            return false;
        },
        error: function(result) {
            auditoria_send({ "proceso": "obtener_cuentas_x_cliente_error", "data": result });
            return false;
        }
    });*/
}

function sec_tlv_pag_clean_modal_agregar_cuenta(){
    $('#sec_tlv_pag_modal_cuentas_banco').val(0).trigger('change.select2');
    $('#sec_tlv_pag_modal_cuentas_banco').prop("disabled", false);
    $('#sec_tlv_pag_modal_cuentas_cuenta_num').val('');
    $('#sec_tlv_pag_modal_cuentas_cci').val('');
    $('#sec_tlv_pag_mdl_cuentas_id_cuenta').val('');
}

$('#sec_tlv_pag_modal_retiro_btn_agregar_cuenta').on('click', function(e) {
    sec_tlv_pag_clean_modal_agregar_cuenta();
    $('#sec_tlv_pag_mdl_cuentas_id_operacion').val(1);
    $('#sec_tlv_pag_modal_cuentas').modal();
});

function sec_tlv_pag_edit_cuenta(operacion, id_cuenta, id_banco, num_cuenta, cci){
    $('#sec_tlv_pag_mdl_cuentas_id_operacion').val(operacion);
    $('#sec_tlv_pag_mdl_cuentas_id_cuenta').val(id_cuenta);
    if(operacion == 2){ //Editar
        $('#sec_tlv_pag_modal_cuentas_banco').val(id_banco).trigger('change.select2');
        $('#sec_tlv_pag_modal_cuentas_banco').prop("disabled", true);
        $('#sec_tlv_pag_modal_cuentas_cuenta_num').val(num_cuenta);
        $('#sec_tlv_pag_modal_cuentas_cci').val(cci);

        $('#sec_tlv_pag_modal_cuentas').modal();
    }else if(operacion == 3){ //Desactivar
        $('#sec_tlv_pag_modal_cuentas_banco').val(0).trigger('change.select2');
        $('#sec_tlv_pag_modal_cuentas_cuenta_num').val('');
        $('#sec_tlv_pag_modal_cuentas_cci').val('');
        swal({
            html:false,
            title: '¿Desea eliminar esta cuenta?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0336FF',
            cancelButtonColor: '#d33',
            confirmButtonText: 'SI, CONFIRMAR',
            cancelButtonText: 'CANCELAR',
            closeOnConfirm: false,
            //,showLoaderOnConfirm: true
        }, function(){
            sec_tlv_pag_guardar_cuenta_x_cliente();
            return false;
        });
    }
}

$('#sec_tlv_pag_modal_cuentas_btn_guardar').on('click', function(e) {
    sec_tlv_pag_guardar_cuenta_x_cliente();
});

function sec_tlv_pag_guardar_cuenta_x_cliente() {
    
    var operacion = $('#sec_tlv_pag_mdl_cuentas_id_operacion').val();
    var id_cuenta = $('#sec_tlv_pag_mdl_cuentas_id_cuenta').val();
    var banco = $('#sec_tlv_pag_modal_cuentas_banco').val();
    var cuenta_num = $('#sec_tlv_pag_modal_cuentas_cuenta_num').val();
    var cci = $('#sec_tlv_pag_modal_cuentas_cci').val();
    
    if(id_cuenta == ''){id_cuenta = 0};
    if([1,2].includes(parseInt(operacion))){
        if (!(parseInt(banco) > 0)) {
            $('#sec_tlv_pag_modal_cuentas_banco').css('border', '1px solid red');
            $('#sec_tlv_pag_modal_cuentas_banco').focus();
            //$('#sec_tlv_pag_modal_cuentas_btn_guardar').show();
            return false;
        }

        if(parseInt(banco) != 53){ //bn
            if($.trim(cuenta_num) == "" && $.trim(cci) == ""){
                swal('Aviso', "Debe Ingresar el Número de Cuenta o CCI", 'warning');
                //$('#sec_tlv_pag_modal_cuentas_btn_guardar').show();
                return false;
            }
        }else{
            if (!(cuenta_num.length > 0)) {
                //$('#sec_tlv_pag_modal_cuentas_cuenta_num').css('border', '1px solid red');
                //$('#sec_tlv_pag_modal_cuentas_cuenta_num').focus();
                swal('Aviso', "Debe Ingresar el Número de Cuenta", 'warning');
                //$('#sec_tlv_pag_modal_cuentas_btn_guardar').show();
                return false;
            }
            if (!(cci.length > 0)) {
                //$('#sec_tlv_pag_modal_cuentas_cci').css('border', '1px solid red');
                //$('#sec_tlv_pag_modal_cuentas_cci').focus();
                swal('Aviso', "Debe Ingresar el CCI", 'warning');
                //$('#sec_tlv_pag_modal_cuentas_btn_guardar').show();
                return false;
            }
        }

    }
    
    var data = {
        "accion": "guardar_cuenta_x_cliente",
        "id_cuenta": id_cuenta,
        "id_cliente": gen_id_cliente,
        "id_banco": banco,
        "cuenta_num": cuenta_num,
        "cci": cci,
        "operacion" : operacion
    }

    $.ajax({
        url: "/sys/set_televentas_pagador.php",
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
            auditoria_send({ "respuesta": "guardar_cuenta_x_cliente", "data": respuesta });
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                $('#sec_tlv_pag_modal_cuentas').modal('hide');
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                sec_tlv_pag_modal_retiro_obtener_cuentas();
                $('#sec_tlv_pag_modal_cuentas').modal('hide');
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                return false;
            }
            return false;
        },
        error: function(result) {
            auditoria_send({ "proceso": "guardar_cuenta_x_cliente_error", "data": result });
            return false;
        }
    });
    return false;
}

function sec_tlv_pag_regresar_btn(tipo){
    if(tipo == 0){
        $("#div_dp_resultado_retiro").hide();
        $("#div_dp_resultado_footer_retiro").hide();
        $("#div_televentas_validaciones_retiros").show();    
    }else if (tipo == 1){
        sec_tlv_pag_retiro_validado();
    }
}

function sec_tlv_pag_retiro_validado(){
    var data = {
        "accion": "sec_tlv_pag_transaccion_verificada",
        "id_transaccion": gen_id_transaccion
    }

    $.ajax({
        url: "/sys/set_televentas_pagador.php",
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
            auditoria_send({ "respuesta": "sec_tlv_pag_transaccion_verificada", "data": respuesta });
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $("#div_dp_resultado_retiro").hide();
                $("#div_dp_resultado_footer_retiro").hide();
                $("#div_televentas_validaciones_retiros").show();    
                return false;
            }
            return false;
        },
        error: function(result) {
            auditoria_send({ "proceso": "sec_tlv_pag_transaccion_verificada_error", "data": result });
            return false;
        }
    });
    return false;
}

$('#sec_tlv_pag_btn_actualizar_solicitud').click(function() {
    var cuenta_id = 0;
    var banco_nombre = '';
    if([2,3,4].includes(estado_transaccion_detalle)){
        swal('Aviso', 'No puede editar esta solicitud.', 'warning');
        return false;
    }
    $('.sec_tlv_pag_sec_cuentas_cliente:checked').each(function(indice, elemento){
        var fila = $(this).parents(".listado_cuentas_bancarias_clientes");
        cuenta_id = fila.find(".sec_tlv_id_cuenta_cliente").val();
        banco_nombre = fila.find(".sec_tlv_nom_cuenta_cliente").html();
        nro_cuenta = fila.find(".sec_tlv_num_cuenta_cliente_total").val();
        cci = fila.find(".sec_tlv_num_cuenta_cci_cliente_total").val();
        valor = $(this).val();
    });
    if(cuenta_id == 0){
        swal('Aviso', 'Debe seleccionar una cuenta para actualizar la solicitud de retiro', 'warning');
        return false;
    }else{
        swal({
            html:false,
            title: '¿Desea usar esta cuenta en la solicitud?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0336FF',
            cancelButtonColor: '#d33',
            confirmButtonText: 'SI, CONFIRMAR',
            cancelButtonText: 'CANCELAR',
            closeOnConfirm: false,
            //,showLoaderOnConfirm: true
        }, function(){
            sec_tlv_pag_actualizar_cuenta_solicitud(cuenta_id,banco_nombre,nro_cuenta,cci);
            return false;
        });
    }
});


function copiarAlPortapapeles(id_elemento, id, estado) {
    var aux = document.createElement("input");
  aux.setAttribute("value", document.getElementById(id_elemento).innerHTML);
  document.body.appendChild(aux);
  aux.select();
  document.execCommand("copy");
  document.body.removeChild(aux);
  sec_tlv_pg_cambio_estado(id, estado);
  }


  function sec_tlv_pg_cambio_estado(id, estado) {
    
if(estado == 6){
    var id_transaccion = id;

    var data = {
        "accion": "sec_tlv_pg_cambio_estado_color",
        "id_transaccion": id_transaccion
    }
 
    $.ajax({
        url: "/sys/set_televentas_pagador.php",
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
            auditoria_send({ "respuesta": "sec_tlv_pg_cambio_estado_color", "data": respuesta });
            if (parseInt(respuesta.http_code) == 400) {
                 
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                //sec_tlv_pag_modal_retiro_obtener_cuentas();
                
                
                listar_tabla_validaciones_retiros();
                return false;
                
            }
            return false;
        },
        error: function(result) {
            auditoria_send({ "proceso": "sec_tlv_pg_cambio_estado_color", "data": result });
            return false;
        }
    });
    return false;
}else{

}
  
  }
 
function sec_tlv_pag_actualizar_cuenta_solicitud(cta_id,banco_nombre,nro_cuenta,cci){
    var id_cuenta = cta_id;
    var id_transaccion = gen_id_transaccion;
    var banco = banco_nombre;
    var nro_cta = nro_cuenta;
    var nro_cci = cci;

    var data = {
        "accion": "sec_tlv_pag_actualizar_cuenta_solicitud",
        "id_cuenta": id_cuenta,
        "id_transaccion": id_transaccion
    }

    $.ajax({
        url: "/sys/set_televentas_pagador.php",
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
            auditoria_send({ "respuesta": "sec_tlv_pag_actualizar_cuenta_solicitud", "data": respuesta });
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                //sec_tlv_pag_modal_retiro_obtener_cuentas();
                $('#div_dp_cuenta_retiro').val(banco);
                $('#div_dp_num_cuenta_retiro').val(nro_cta);
                $('#div_dp_num_cuenta_cci_retiro').val(nro_cci);
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                return false;
            }
            return false;
        },
        error: function(result) {
            auditoria_send({ "proceso": "sec_tlv_pag_actualizar_cuenta_solicitud_error", "data": result });
            return false;
        }
    });
    return false;
}
function editar_detalle_retiro(tipo_operacion, cuenta_pago_id, num_operacion){
    $('#sec_tlv_pag_tipo_operacion').val(tipo_operacion);
    gen_cuenta_id_nuevo_edit = 0;
    gen_banco_pago_id_nuevo_edit = 0;
    gen_num_ope_nuevo_edit = 0;
    $('.SecTelPag_edit_control').show();
    $('#sec_tlv_pag_edit_ver_imagen').attr('src', '');
    $('#sec_tlv_pag_edit_imagen_voucher').val('');
    $('#sec_tlv_pag_edit_ver_imagen_propina').attr('src', '');
    $('#sec_tlv_pag_edit_imagen_voucher_propina').val('');
    $('#sec_tlv_pag_edit_select_banco_pago').val(cuenta_pago_id).trigger('change.select2');
    if(tipo_operacion == "PROPINA"){
        $('#secTelPag_div_edit_image_comprobante').hide();
        $('#secTelPag_div_edit_image_comprobante_propina').show();
    }
}

$("#sec_tlv_pag_edit_imagen_voucher").change(function (e) {
    var filePath = this.value;
    var allowedExtensions = /(.jpg|.jpeg|.png|.gif)$/i;
    if(!allowedExtensions.exec(filePath)){
        swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
        this.value = '';
        return false;
    }else{
        var newPath = filePath.replace('jpg','png');
        sec_tlv_pag_readImage(this);
    }
});

function sec_tlv_pag_readImage(input) {
    $('#div_dp_imagen_lista_retiro').html('');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#sec_tlv_pag_edit_ver_imagen').attr('src', e.target.result);
        }
        console.log(input.files[0].name);
        reader.readAsDataURL(input.files[0]);
        $("sec_tlv_pag_edit_ver_imagen").imgViewer2();
    }
}

$('#sec_tlv_pag_limpiar_input_image').click(function(){
    $('#sec_tlv_pag_edit_ver_imagen').attr('src', '');
    $('#sec_tlv_pag_edit_imagen_voucher').val('');

    var data = {
        "accion": "obtener_imagenes_x_transaccion_retiro",
        "id_transaccion": gen_id_transaccion
    }
    auditoria_send({ "proceso": "obtener_imagenes_x_transaccion_retiro", "data": data });
    $.ajax({
        url: "/sys/set_televentas_pagador.php",
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
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#div_dp_imagen_lista_retiro').html('');
                $.each(respuesta.result, function(index, item) {
                    var nuevo_id = (respuesta.fecha_hora).toString() + '_' + (item.id).toString();
                    $('#div_dp_imagen_lista_retiro').append(
                        '<div class="col-md-12">' +
                        '   <div align="center">' +
                        '       <img id="' + nuevo_id + '" src="files_bucket/retiros/' + item.archivo + '" style="max-width: 450px;" />' +
                        '   </div>' +
                        '</div>'
                    );
                    $("#" + nuevo_id).imgViewer2();
                });
                return false;
            }
        },
        error: function() {}
    });
});

$('#sec_tlv_pag_edit_btn_cuenta').click(function(){
    var tipo_operacion = $('#sec_tlv_pag_tipo_operacion').val();
    if (tipo_operacion != "PROPINA") {
        $('#sec_tlv_pag_title_modal_cuentas').html('Cambiar cuenta registrada de la solicitud.');
        $('#sec_tlv_pag_modal_retiro_btn_agregar_cuenta').hide();
        $('#sec_tlv_pag_btn_actualizar_solicitud').hide();
        $('#sec_tlv_pag_btn_confirmar_cambio_cuenta').show();
        sec_tlv_pag_modal_retiro_obtener_cuentas();
        $('#sec_tlv_pag_modal_retiro').modal();
    }else{
        $('#sec_tlv_pag_title_modal_cuentas_propina').html('Cambiar cuenta registrada de la solicitud.');
        $('#sec_tlv_pag_btn_actualizar_solicitud_propina').hide();
        $('#sec_tlv_pag_btn_confirmar_cambio_cuenta_propina').show();
        sec_tlv_pag_modal_propina_obtener_cuentas();
        $('#sec_tlv_pag_modal_propina').modal();
    }
});

$('#sec_tlv_pag_edit_btn_num_ope').click(function(){
    $("#div_dp_num_operacion_retiro").attr('disabled', false);
    $('#div_dp_num_operacion_retiro').focus();
    $("#div_dp_num_operacion_retiro").css("border", "1px solid red");
});

var gen_cuenta_id_nuevo_edit = 0;
$('#sec_tlv_pag_btn_confirmar_cambio_cuenta').click(function(){
    $('.sec_tlv_pag_sec_cuentas_cliente:checked').each(function(indice, elemento){
        var fila = $(this).parents(".listado_cuentas_bancarias_clientes");
        cuenta_id = fila.find(".sec_tlv_id_cuenta_cliente").val();
        banco_nombre = fila.find(".sec_tlv_nom_cuenta_cliente").html();
        nro_cuenta = fila.find(".sec_tlv_num_cuenta_cliente_total").val();
        cci = fila.find(".sec_tlv_num_cuenta_cci_cliente_total").val();
        valor = $(this).val();
    });
    if(cuenta_id == 0){
        swal('Aviso', 'Debe seleccionar una cuenta para actualizar la cuenta', 'warning');
        return false;
    }else{
        gen_cuenta_id_nuevo_edit = cuenta_id;
        $('#div_dp_cuenta_retiro').val(banco_nombre);
        $('#div_dp_num_cuenta_retiro').val(nro_cuenta);
        $('#div_dp_num_cuenta_cci_retiro').val(cci);
        $('#sec_tlv_pag_modal_retiro').modal('hide');
    }
});

/********************************************************************************/
/******************************* GUARDAR CAMBIOS ********************************/
/********************************************************************************/
$('#sec_tlv_pag_edit_btn_save_changes').click(function(){

    var num_ope_nuevo = $('#div_dp_num_operacion_retiro').val();
    if(num_ope_nuevo.length == 0){
        swal('Aviso', 'El número de operación no puede quedar vacío.', 'warning');
        return false;
    }else{
        gen_num_ope_nuevo_edit = num_ope_nuevo; 
    }

    swal({
        html : true,
        title : '¿Desea guardar los cambios?',
        type : 'warning',
        showCancelButton : true,
        cancelButtonColor : '#d33',
        cancelButtonText: 'CANCELAR',
        confirmButtonColor : '#0336FF',
        confirmButtonText : 'SI, GUARDAR',
        closeOnConfirm : false
    }, function(){
        var tipo_operacion = $('#sec_tlv_pag_tipo_operacion').val();

        var cuenta_id_nuevo = gen_cuenta_id_nuevo_edit;
        if(cuenta_id_nuevo == 0){ cuenta_id_nuevo = gen_cuenta_id_register; }

        var banco_pago_id_nuevo = gen_banco_pago_id_nuevo_edit;
        if(banco_pago_id_nuevo == 0){ banco_pago_id_nuevo = gen_banco_pago_id_register; }

        var num_ope_nuevo = gen_num_ope_nuevo_edit;
        if(num_ope_nuevo == 0){ num_ope_nuevo = gen_num_ope_register; }
        
        secTlvPag_edit_data = [];
        secTlvPag_edit_data.push(
            {'campo' : (tipo_operacion == 'PROPINA')?'cajero_cuenta_id':'cuenta_id', 'valor_anterior': gen_cuenta_id_register, 'valor_nuevo' : cuenta_id_nuevo , 'updated' : (gen_cuenta_id_register == cuenta_id_nuevo)},
            {'campo' : 'cuenta_pago_id', 'valor_anterior': gen_banco_pago_id_register, 'valor_nuevo' : banco_pago_id_nuevo , 'updated' : (gen_banco_pago_id_register == banco_pago_id_nuevo)},
            {'campo' : 'num_operacion', 'valor_anterior': gen_num_ope_register, 'valor_nuevo' : num_ope_nuevo , 'updated' : (gen_num_ope_register == num_ope_nuevo)}
        );

        if (tipo_operacion != "PROPINA") {
            var imagen = $('#sec_tlv_pag_edit_imagen_voucher').val();
            var f_imagen = $("#sec_tlv_pag_edit_imagen_voucher")[0].files[0];
        }else{
            var imagen = $('#sec_tlv_pag_edit_imagen_voucher_propina').val();
            var f_imagen = $("#sec_tlv_pag_edit_imagen_voucher_propina")[0].files[0];
        }
        
        var data = new FormData();
        data.append('accion', "sec_tlv_pag_guardar_cambios_registro");
        data.append('id_transaccion', gen_id_transaccion);
        data.append('rr_data', JSON.stringify(secTlvPag_edit_data));
        data.append('imagen_voucher', f_imagen);
        data.append('imagen', imagen);
        data.append('tipo_operacion', tipo_operacion);

        auditoria_send({ "proceso": "sec_tlv_pag_guardar_cambios_registro", "data": data });
        $.ajax({
            url: "/sys/set_televentas_pagador.php",
            type: 'POST',
            data: data,
            processData: false,
            cache: false,
            contentType: false,
            beforeSend: function() {
                loading("true");
            },
            complete: function() {
                loading();
            },
            success: function(resp) {
                var respuesta = JSON.parse(resp);
                auditoria_send({ "respuesta": "sec_tlv_pag_guardar_cambios_registro_respuesta", "data": respuesta });
                if (parseInt(respuesta.http_code) == 400) {
                    $('.SecTelDep_edit_control').show();
                    swal('Aviso', respuesta.status, 'warning');
                    return false;
                }
                if (parseInt(respuesta.http_code) == 200) {
                    swal('Aviso', 'Acción realizada con éxito.', 'success');
                    $('.SecTelPag_edit_control').hide();
                    $("#div_dp_resultado_retiro").hide();
                    $("#div_dp_resultado_footer_retiro").hide();
                    $("#div_televentas_validaciones_retiros").show();  
                    listar_tabla_validaciones_retiros();
                    return false;
                }
            },
            error: function() {}
        });
    });
});

$("#sec_tlv_pag_edit_imagen_voucher_propina").change(function (e) {
    var filePath = this.value;
    var allowedExtensions = /(.jpg|.jpeg|.png|.gif)$/i;
    if(!allowedExtensions.exec(filePath)){
        swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
        this.value = '';
        return false;
    }else{
        var newPath = filePath.replace('jpg','png');
        sec_tlv_pag_readImage_propina(this);
    }
});

function sec_tlv_pag_readImage_propina(input) {
    $('#sec_tlv_pag_img_referencia_propina').html('');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#sec_tlv_pag_edit_ver_imagen_propina').attr('src', e.target.result);
        }
        console.log(input.files[0].name);
        reader.readAsDataURL(input.files[0]);
        $("sec_tlv_pag_edit_ver_imagen_propina").imgViewer2();
    }
}

$('#sec_tlv_pag_limpiar_input_image_propina').click(function(){
    $('#sec_tlv_pag_edit_ver_imagen_propina').attr('src', '');
    $('#sec_tlv_pag_edit_imagen_voucher_propina').val('');
    cargar_voucher_propina_pagado(gen_id_transaccion);
});

$('#sec_tlv_pag_btn_confirmar_cambio_cuenta_propina').click(function(){
    $('.sec_tlv_pag_sec_cuentas_cajero:checked').each(function(indice, elemento){
        var fila = $(this).parents(".listado_cuentas_bancarias_cajero");
        cuenta_id = fila.find(".sec_tlv_id_cuenta_cajero").val();
        banco_nombre = fila.find(".sec_tlv_nom_cuenta_cajero").html();
        nro_cuenta = fila.find(".sec_tlv_num_cuenta_cajero_total").val();
        cci = fila.find(".sec_tlv_num_cuenta_cci_cajero_total").val();
        valor = $(this).val();
    });
    if(cuenta_id == 0){
        swal('Aviso', 'Debe seleccionar una cuenta para actualizar la cuenta', 'warning');
        return false;
    }else{
        gen_cuenta_id_nuevo_edit = cuenta_id;
        $('#div_dp_cuenta_retiro').val(banco_nombre);
        $('#div_dp_num_cuenta_retiro').val(nro_cuenta);
        $('#div_dp_num_cuenta_cci_retiro').val(cci);
        $('#sec_tlv_pag_modal_propina').modal('hide');
    }
});

$('#sec_tlv_pag_edit_btn_banco_pago').click(function(){
    var tipo_operacion = $('#sec_tlv_pag_tipo_operacion').val();
    $('#sec_tlv_pag_modal_banco_pago').modal();
});
var gen_banco_pago_id_nuevo_edit = 0;
$('#sec_tlv_pag_btn_confirmar_banco_pago').click(function(){
    var id_banco_nuevo = $('#sec_tlv_pag_edit_select_banco_pago').val();
    gen_banco_pago_id_nuevo_edit = id_banco_nuevo;
    $('#sec_tlv_pag_select_banco_pago').val(id_banco_nuevo).trigger('change.select2');
    $('#sec_tlv_pag_modal_banco_pago').modal('hide');
});

$('#sec_tlv_pag_select_banco_pago').on("change", function(e){
    var valid_yape = $('#sec_tlv_pag_select_banco_pago option:selected').attr("valid-yape");
    if(valid_yape == 1){
        $('#div_dp_num_operacion_retiro').val("");
        $('#div_dp_num_operacion_retiro').attr("oninput","");
    }else{
        $('#div_dp_num_operacion_retiro').val("");
        $('#div_dp_num_operacion_retiro').attr("oninput","this.value=this.value.replace(/[^0-9]/g,'');");
    }
});


$('#div_dp_registro_retiro').on('change', function(e){
    var dato = $(this).val();
    var fecha_actual = '';
    var data = {
        "accion": "sec_tlv_pag_obtener_fecha_hora"
    }
    $.ajax({
        url: "/sys/set_televentas_pagador.php",
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
            if (parseInt(respuesta.http_code) == 200) {
                fecha_actual = respuesta.result.substring(0,16);
                var dato_d = dato.replace('T',' ');
                if(dato_d > fecha_actual){
                    $('#div_dp_registro_retiro').val(fecha_actual);
                    dato = $('#div_dp_registro_retiro').val();
                    alertify.error('La fecha de pago no puede ser mayor a la actual', 3);
                }
            }
        },
        error: function() {}
    });
    
});

function sec_tlv_pag_obtener_totales(){
    var fecha_inicio = $("#buscador_fecha_inicio_retiro").val();
    var fecha_fin = $("#buscador_fecha_fin_retiro").val();
    var estado = $("#buscador_estado_transaccion_retiro").val();
    var cuenta = $("#buscador_cuenta_retiro").val();
    var validador = $('#buscador_pagador').val() != '' ? gen_pagador_seleccionado : '0';
    var razon = $("#sec_tlv_pag_select_razon").val();
    var tipo = $("#sec_tlv_pag_select_tipo").val();
    var validador_supervisor = $("#sec_tlv_pag_validador").val();
    var cuenta_pago = $("#buscador_cuenta_pago").val();
    var tipo_saldo = $("#sec_tlv_pag_select_saldo").val();
    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menos a la de fin.', 'warning');
        return false;
    }

    if(validador_supervisor == undefined){
        validador_supervisor = 0;
    }

    var data = {
        "accion": "sec_tlv_pag_obtener_totales",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "estado": estado,
        "cuenta": cuenta,
        "validador": validador,
        "razon": razon,
        "tipo": tipo,
        "validador_supervisor": validador_supervisor,
        "cuenta_pago" : cuenta_pago,
        "tipo_saldo" : tipo_saldo
    }
    $.ajax({
        url: "/sys/set_televentas_pagador.php",
        type: 'POST',
        data: data,
        beforeSend: function () {
            loading("true");
        },
        complete: function () {
            loading();
        },
        success: function (resp) { 
            var respuesta = JSON.parse(resp);            
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                var monto_total_depositado = 0;
                var monto_total_comision = 0;
                var monto_total_real = 0;
                $.each(respuesta.result, function(index, item){
                    monto_total_comision = parseFloat(item.comision_monto);
                    monto_total_real = parseFloat(item.monto);
                });
                
                monto_total_real = (monto_total_real.toFixed(2)).replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                monto_total_comision = (monto_total_comision.toFixed(2)).replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                $('#SecTelDel_monto_total_comision_retiro').val(monto_total_comision);
                $('#SecTelDel_monto_total_real_retiro').val(monto_total_real);
                return false;
            }
        },
        error: function (result) {
            auditoria_send({"proceso": "sec_tlv_obtener_totales_transacciones_error", "data": result});
        }
    });
   
}