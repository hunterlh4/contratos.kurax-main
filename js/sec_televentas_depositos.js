var countdownTimer_sec_televentas_depositos;
localStorage.removeItem("listNew");
var gen_fecha_hora = "";
var tls_depositos_version = 1.07;
let isTabActiveDepositos = true;
let isUserActiveDeposito = true;
let inactivityTimeoutDeposito;

function sec_televentas_depositos() {

    if (sec_id == 'televentas_depositos') {
        if(parseFloat(tls_depositos_version)<parseFloat(tls_depositos_version_actual)){
            alertify.error('El sistema ha sido actualizado. Ctrl+F5 (Si estás en PC) o Ctrl+Tecla Función +F5 (Si estás en laptop) o contactar con el área de soporte..', 180);
        }

        $('#buscador_fecha_inicio').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#buscador_fecha_fin').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('#buscador_fecha_inicio').val($('#g_fecha_actual').val());
        $('#buscador_fecha_fin').val($('#g_fecha_actual').val());

        $('#buscador_fecha_inicio').change(function() {
            var var_fecha_change = $('#buscador_fecha_inicio').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#buscador_fecha_inicio").val($("#g_fecha_actual").val());
            }
        });
        $('#buscador_fecha_fin').change(function() {
            var var_fecha_change = $('#buscador_fecha_fin').val();
            if (!(parseInt(var_fecha_change.length) > 0)) {
                $("#buscador_fecha_fin").val($("#g_fecha_actual").val());
            }
        });

        $('#div_dp_titular_abono').select2();

        $('#div_dp_origen').select2();
        $("#buscador_estado_transaccion").val("0");
        $('#sec_tlv_dep_edit_modal_cuenta').select2();
        $('#buscador_tipo_constancia').select2();

        $('#buscador_validador').autocomplete({
            source: '/sys/set_televentas_deposito.php?accion=sec_tlv_dep_listar_validadores',
            minLength: 3,
            select: function (event, ui)
            {
                gen_validador_seleccionado = ui.item.codigo;
                if(gen_validador_seleccionado == undefined){
                    gen_validador_seleccionado = 0;
                }
            }
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            $('ul.ui-menu').css('display', 'block !important');
            $('ul.ui-menu').css('z-index', '100000');
            return $( "<li>" )
            .append( item.label)
            .appendTo( ul );
        };

        sec_televentas_depositos_listar_cuentas();
        

        

        //**************************************************************************************
        // RESULTADO
        //**************************************************************************************

        $('#div_dp_motivo').select2();
        $("#div_dp_motivo").val('0');
        $('#div_dp_motivo').select2().trigger('change');
        $('#div_dp_comision_select').select2();
        $("#div_dp_comision_select").val('0.00');
        $('#div_dp_comision_select').select2().trigger('change');

        $('#nombre_cliente').on('click', function(e) {
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

        var get_abono_id = $('#get_abono_id').val();
        var get_origen = $('#get_origen').val();
        if(get_abono_id != ''){
            ver_detalle_x_abono(get_abono_id,get_origen);
        }

        
        function startTimer() {
            if (isTabActiveDepositos && isUserActiveDeposito) {
                if (countdownTimer_sec_televentas_depositos) {
                    clearInterval(countdownTimer_sec_televentas_depositos);
                }
                countdownTimer_sec_televentas_depositos = setInterval(actualizar_tabla_validaciones, 4000);         
            }
        }
        
        function stopTimer() {
            if (countdownTimer_sec_televentas_depositos) {
                clearInterval(countdownTimer_sec_televentas_depositos);
                countdownTimer_sec_televentas_depositos = null;
            }
        }
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimeoutDeposito);
            isUserActiveDeposito = true;
            startTimer();
            
            inactivityTimeoutDeposito = setTimeout(() => {
                isUserActiveDeposito = false;
                stopTimer();
            }, 30000);
        }
        
        document.addEventListener("visibilitychange", function () {
            if (document.hidden) {
                isTabActiveDepositos = false;
                stopTimer();
            } else {
                console.log("activo")
                isTabActiveDepositos = true;
                startTimer()
                resetInactivityTimer();
            }
        });
        
        document.addEventListener("mousemove", resetInactivityTimer);
        document.addEventListener("keydown", resetInactivityTimer);
        
        if (isTabActiveDepositos && isUserActiveDeposito) {
            startTimer();
        }  
    }
}

/* INICIO -- Funcion para que no acepte ceros a la izquierda */
function pierdeFoco(e){
    var valor = e.value.replace(/^0*/, '');
    e.value = valor;
}
/* FIN -- funcion para que no acepte ceros a la izquierda */




var gen_tabla_validaciones_ultimo_id = 0;
var gen_tabla_validaciones_fecha_inicio = '';
var gen_tabla_validaciones_fecha_fin = '';
var gen_tabla_validaciones_estado = '';
var gen_tabla_validaciones_cuenta = '';
var gen_tabla_validaciones_validador = 0;

var array_tabla_validaciones = [];
var cargaPagina = 0;

var tlvdep_switch_listacompleta = 0;
var gen_validador_seleccionado = 0;
function limpiar_tabla_validaciones() {
    $('#tabla_validaciones').html(
        '<thead>' +
        '   <tr>' +
        '       <th>Fecha</th>' +
        '       <th>Autovalidación</th>' +
        '       <th>Usuario</th>' +
        '       <th>Caja</th>' +
        '       <th>Cliente</th>' +
        '       <th>Cuenta</th>' +
        '       <th>Bono</th>' +
        '       <th>Nº Op.</th>' +
        '       <th>Depósito S/</th>' +
        '       <th>Comisión S/</th>' +
        '       <th>Real S/</th>' +
        '       <th>Constancia</th>' +
        '       <th>Estado</th>' +
        '       <th>Validador</th>' +
        '       <th>Titular Abono</th>' +
        '       <th>Ver</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
}
function listar_tabla_validaciones() {
    tlvdep_switch_listacompleta = 0;

    var monto_cant_depositado = 0;
    var monto_total_depositado = 0;
    var monto_total_comision = 0;
    var monto_total_real = 0;
    
    $('#SecTelDel_monto_cant_depositado').val('0');
    $('#SecTelDel_monto_cant_dep_d_ant_yape').val('0');
    $('#SecTelDel_monto_dep_d_ant_yape').val('0');
    $('#SecTelDel_monto_total_depositado').val('0.00');
    $('#SecTelDel_monto_total_comision').val('0.00');
    $('#SecTelDel_monto_total_real').val('0.00');
    $('#SecTelDel_cant_autovalidaciones').val('0');
    $('#SecTelDel_monto_total_autovalidaciones').val('0.00');
    $('#SecTelDel_cant_abonos_pendientes').val('0');
    $('#SecTelDel_monto_abonos_pendientes').val('0.00');
    limpiar_tabla_validaciones();
    array_tabla_validaciones = [];

    var fecha_inicio = $("#buscador_fecha_inicio").val();
    var fecha_fin = $("#buscador_fecha_fin").val();
    var estado = $("#buscador_estado_transaccion").val();
    var cuenta = $("#buscador_cuenta").val();
    var validador = $("#buscador_validador").val() != '' ? gen_validador_seleccionado : '0';
    var autovalidacion = $('#buscador_autovalidacion_transaccion').val();
    var tipo_constancia = $('#buscador_tipo_constancia').val();
 
    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menos a la de fin.', 'warning');
        return false;
    }

    var data = {
        "accion": "obtener_transacciones_x_estado",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "estado": estado,
        "cuenta": cuenta,
        "validador": validador,
        "autovalidacion": autovalidacion,
        "tipo_constancia": tipo_constancia
    }

    gen_tabla_validaciones_ultimo_id = 0;
    gen_tabla_validaciones_fecha_inicio = fecha_inicio;
    gen_tabla_validaciones_fecha_fin = fecha_fin;
    gen_tabla_validaciones_estado = estado;
    gen_tabla_validaciones_cuenta = cuenta;
    gen_tabla_validaciones_validador = validador;
    gen_tabla_validaciones_autovalidacion = autovalidacion;
    gen_tabla_validaciones_tipo_constancia = tipo_constancia;
    var monto_cant_depositado = 0;
    var monto_total_depositado = 0;
    var monto_total_comision = 0;
    var monto_total_real = 0;
    var cant_autovalidaciones = 0;
    var monto_total_autovalidaciones = 0;
    var monto_total_tipo_constancia = 0;
    var cant_abonos_pendientes = 0;
    var total_abonos_pendientes = 0;

    auditoria_send({ "proceso": "obtener_transacciones_x_estado", "data": data });

    if(estado === "0"){ //PENDIENTES
        //Activar el actualizador de validaciones

        $.ajax({
            url: "/sys/set_televentas_deposito.php",
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

                if ( respuesta.totales ) {
                    $.each(respuesta.totales, function(index, item) {
                        cant_abonos_pendientes += parseInt(item.cant);
                        total_abonos_pendientes += parseFloat(item.total);
                    });
    
                    total_abonos_pendientes = (total_abonos_pendientes.toFixed(2)).replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    
                    $('#SecTelDel_cant_abonos_pendientes').val(cant_abonos_pendientes);
                    $('#SecTelDel_monto_abonos_pendientes').val(total_abonos_pendientes);
                }

                if (parseInt(respuesta.http_code) == 400) {
                    //swal('Aviso', respuesta.status, 'warning');
                    $('#tabla_validaciones').append(
                        '<tr>' +
                        '<td class="text-center" colspan="16">No hay transacciones</td>' +
                        '</tr>'
                    );
                    cargaPagina=1;
                    tlvdep_switch_listacompleta = 1;
                    return false;
                }
                if (parseInt(respuesta.http_code) == 200) {
                    
                    $.each(respuesta.result, function(index, item) {
                        array_tabla_validaciones.push(item);
                        
                        gen_tabla_validaciones_ultimo_id = item.id;
                        var color = '';
                        if (parseInt(item.estado_id) === 0) {
                            color = '';
                        } else if (parseInt(item.estado_id) === 1 && parseInt(item.estado_id_aprov) === 1) {
                            monto_cant_depositado ++;
                            monto_total_depositado = parseFloat(monto_total_depositado) + parseFloat(item.monto_deposito);
                            monto_total_comision = parseFloat(monto_total_comision) + parseFloat(item.comision_monto);
                            monto_total_real = parseFloat(monto_total_real) + parseFloat(item.monto);
                            color = 'color:green;';
                        } else if (parseInt(item.estado_id) === 2) {
                            color = 'color:red;';
                        } else if(parseInt(item.estado_id_aprov) === 3){
                            color = 'color:gray;';
                        }

                        if(item.autovalidacion == 'SI'){
                            cant_autovalidaciones ++;
                            monto_total_autovalidaciones = parseFloat(monto_total_autovalidaciones) + parseFloat(item.monto_deposito);
                        }

                        var cuenta = "'" + item.cuenta + "'";
                        var monto_deposito = "'" + item.monto_deposito + "'";
                        var monto_deposito_td =item.monto_deposito.replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                        var monto_comision = "'" + item.comision_monto + "'";
                        var monto_comision_td =item.comision_monto.replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                        var monto_real = "'" + item.monto + "'";
                        var monto_real_td =item.monto.replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                        var observacion_cajero = "'" + ((item.observacion_cajero).replace(/(\r\n|\n|\r)/gm, "")).trim() + "'";
                        var observacion_validador = "'" + ((item.observacion_validador).replace(/(\r\n|\n|\r)/gm, "")).trim() + "'";

                        var ver = `<button type="button" class="btn btn-primary" onclick="ver_detalle(
                                    ${item.id}, ${item.estado_id}, '0', ${item.valid_num_ope_existe}, ${item.valid_num_ope_unico}, 
                                    ${item.cuenta_id}, ${cuenta}, ${item.bono_id}, '${item.bono_nombre}', '${item.num_operacion}', 
                                    ${monto_deposito}, ${monto_comision}, 
                                    ${observacion_cajero}, ${observacion_validador}, ${item.tipo_rechazo_id}, 
                                    '${item.cliente}', '${item.tipo_contacto}', '${item.turno_local}', '${item.registro_deposito}', ${item.bono_mensual_actual}, ${item.valid_cuenta_yape},
                                     '${item.titular_abono}','${item.tipo_constancia}', '${item.id_tipo_constancia}', '${item.cliente_id}', '${item.id_titular_abono}', '${item.tipo_jugada}',
                                     '${item.ap_fecha_operacion}','${item.ap_hora_operacion}','${item.ap_nro_operacion}','${item.ap_nombre_medio}','${item.ap_monto}','${item.fecha_hora_registro}'
                                )"><span class="glyphicon glyphicon-edit"></span></button>`;

                        var listadoNuevoItem = [];

                        $('#tabla_validaciones').append(
                            '<tr id="'+item.id+'">' +
                            '<td class="text-center">' + item.fecha_hora_registro + '</td>' +
                            '<td class="text-center">' + item.autovalidacion + '</td>' +
                            '<td class="text-center">' + item.cajero + '</td>' +
                            '<td class="text-center">' + item.turno_local + '</td>' +
                            '<td class="text-right">' + item.cliente + '</td>' +
                            '<td class="text-center" style="font-size:17px;font-weight:bold;">' + item.cuenta + '</td>' +
                            '<td class="text-center">' + item.bono_nombre + '</td>' +
                            '<td class="text-center">' + item.num_operacion + '</td>' +
                            '<td class="text-right" style="font-size:17px;font-weight:bold;">' + monto_deposito_td + '</td>' +
                            '<td class="text-right" style="font-size:17px;font-weight:bold;">' + monto_comision_td + '</td>' +
                            '<td class="text-right" style="font-size:17px;font-weight:bold;">' + monto_real_td + '</td>' +
                            '<td class="text-right">' + item.tipo_constancia + '</td>' +
                            '<td class="text-right" style="' + color + '"><b>' + item.estado + '<b></td>' +
                            '<td class="text-right">' + item.validador_nombre + '</td>' +
                            '<td class="text-right">' + item.titular_abono + '</td>' +
                            //'<td class="text-right">' + item.fecha_hora_validacion + '</td>' +
                            '<td class="text-center">' + ver + '</td>' +
                            '</tr>'
                        );
                        
                        item2=item;
                        if(item.estado=='Pendiente'){
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
                    monto_total_depositado = (monto_total_depositado.toFixed(2)).replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    monto_total_comision = (monto_total_comision.toFixed(2)).replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    monto_total_real = (monto_total_real.toFixed(2)).replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    $('#SecTelDel_monto_cant_depositado').val(monto_cant_depositado);
                    $('#SecTelDel_monto_total_depositado').val(monto_total_depositado);
                    $('#SecTelDel_monto_total_comision').val(monto_total_comision);
                    $('#SecTelDel_monto_total_real').val(monto_total_real);
                    $('#SecTelDel_cant_autovalidaciones').val(cant_autovalidaciones);
                    $('#SecTelDel_monto_total_autovalidaciones').val(monto_total_autovalidaciones);

                    tabla_validaciones_datatable_formato('#tabla_validaciones');
                    //console.log(array_clientes);
                    cargaPagina=1;
                    tlvdep_switch_listacompleta = 1;
                    return false;
                }
                cargaPagina=1;            
            },
            error: function() {}
        });

    } else {
        //Desactivar el actualizador de validaciones
        
        //Fin de Desactivar el actualizador de validaciones
        var ftable = $('#tabla_validaciones').DataTable();
        ftable.clear();
        ftable.destroy();
        var ftable = $('#tabla_validaciones').DataTable({
            'destroy': true,
            'scrollX': true,
            "processing": true,
            "serverSide": true,
            "order" : [],
            "ajax": {
                type: "POST",
                async : true,
                "url": "/sys/set_televentas_deposito.php",
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
                    "data": "fecha_hora_registro",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-center');
                    }
                },
                { 
                    "data": "autovalidacion",
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
                        $(td).css('font-size', '17px');
                        $(td).css('font-weight', 'bold');
                        $(td).addClass('text-center');
                    }
                },
                { "data": "bono_nombre",
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
                    "data": "monto_deposito",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).css('font-size', '17px');
                        $(td).css('font-weight', 'bold');
                        $(td).addClass('text-right');
                    },
                    render: function (data, type,item,row) {
                        var monto_deposito_td =item.monto_deposito.replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                        return monto_deposito_td;
                    }
                },
                { 
                    "data": "comision_monto",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).css('font-size', '17px');
                        $(td).css('font-weight', 'bold');
                        $(td).addClass('text-right');
                    },
                    render: function (data, type,item,row) {
                        var monto_comision_td =item.comision_monto.replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                        return monto_comision_td;
                    }
                },
                { 
                    "data": "monto",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).css('font-size', '17px');
                        $(td).css('font-weight', 'bold');
                        $(td).addClass('text-right');
                    },
                    render: function (data, type,item,row) {
                        var monto_real_td =item.monto.replace(/\D/g, "")
                                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                        return monto_real_td;
                    }
                },
                { 
                    "data": "tipo_constancia",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { 
                    "data": "estado",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-right');
                        
                        var color = '';
                        if (parseInt(item.estado_id) === 0) {
                            color = '';
                        } else if (parseInt(item.estado_id) === 1 && parseInt(item.estado_id_aprov) === 1) {
                            monto_cant_depositado ++;
                            monto_total_depositado = parseFloat(monto_total_depositado) + parseFloat(item.monto_deposito);
                            monto_total_comision = parseFloat(monto_total_comision) + parseFloat(item.comision_monto);
                            monto_total_real = parseFloat(monto_total_real) + parseFloat(item.monto);
                            color = 'green';
                        } else if (parseInt(item.estado_id) === 2) {
                            color = 'red';
                        }else if(parseInt(item.estado_id_aprov) === 3){
                            color = 'gray';
                        }
                        $(td).css('color', color);
                        $(td).css('font-weight', 'bold');
                    }
                },
                { 
                    "data": "validador_nombre",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { 
                    "data": "titular_abono",
                    createdCell: function (td, cellData, item, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { 
                    "data": "titular_abono",
                    render: function (data, type,item,row) {
                        var ver = '';
                        var cuenta = "'" + item.cuenta + "'";
                        var monto_deposito = "'" + item.monto_deposito + "'";
                        var monto_comision = "'" + item.comision_monto + "'";

                        var observacion_cajero = "'" + ((item.observacion_cajero).replace(/(\r\n|\n|\r)/gm, "")).trim() + "'";
                        var observacion_validador = "'" + ((item.observacion_validador).replace(/(\r\n|\n|\r)/gm, "")).trim() + "'";
                        var ver = `<button type="button" class="btn btn-primary" onclick="ver_detalle(
                                    ${item.id}, ${item.estado_id}, ${item.estado_id_aprov}, ${item.valid_num_ope_existe}, ${item.valid_num_ope_unico}, 
                                    ${item.cuenta_id}, ${cuenta}, ${item.bono_id}, '${item.bono_nombre}', '${item.num_operacion}', 
                                    ${monto_deposito}, ${monto_comision}, 
                                    ${observacion_cajero}, ${observacion_validador}, ${item.tipo_rechazo_id}, 
                                    '${item.cliente}', '${item.tipo_contacto}', '${item.turno_local}', '${item.registro_deposito}', ${item.bono_mensual_actual}, ${item.valid_cuenta_yape},
                                     '${item.titular_abono}','${item.tipo_constancia}', '${item.id_tipo_constancia}', '${item.cliente_id}', '${item.id_titular_abono}', '${item.tipo_jugada}',
                                     '${item.ap_fecha_operacion}','${item.ap_hora_operacion}','${item.ap_nro_operacion}','${item.ap_nombre_medio}','${item.ap_monto}','${item.fecha_hora_registro}'
                                )"><span class="glyphicon glyphicon-edit"></span></button>`;
                        return ver;
                    }
                }
            ]
        });
        sec_tlv_dep_obtener_totales();
    }





    
}
function actualizar_tabla_validaciones() {
    //console.log(gen_tabla_validaciones_ultimo_id);
    //console.log(array_tabla_validaciones);
    //console.log('actualizar_tabla_validaciones');

    if(parseInt(tlvdep_switch_listacompleta)===0){
        return false;
    }
    if(gen_tabla_validaciones_estado !== "0"){return false;}
    var data = {
        "accion": "actualizar_transacciones_x_estado",
        "where_id": gen_tabla_validaciones_ultimo_id,
        "fecha_inicio": gen_tabla_validaciones_fecha_inicio,
        "fecha_fin": gen_tabla_validaciones_fecha_fin,
        "estado": gen_tabla_validaciones_estado,
        "cuenta" :gen_tabla_validaciones_cuenta,
        "validador" :gen_tabla_validaciones_validador,
        "autovalidacion": gen_tabla_validaciones_autovalidacion,
        "tipo_constancia": gen_tabla_validaciones_tipo_constancia
    }

    //auditoria_send({ "proceso": "actualizar_transacciones_x_estado", "data": data });
    
    $.ajax({
        url: "/sys/set_televentas_deposito.php",
        type: 'POST',
        data: data,
        beforeSend: function() {},
        complete: function() {},
        success: function(resp) { //  alert(datat)
            var respuesta = JSON.parse(resp);
            if(parseFloat(respuesta.result_tls_dep_ultima_version)>0){
                if(parseFloat(tls_depositos_version)<parseFloat(respuesta.result_tls_dep_ultima_version)){
                    alertify.error('El sistema ha sido actualizado. '+
                        'Ctrl+F5 (Si estás en PC) o Ctrl+Tecla Función +F5 (Si estás en laptop) o contactar con el área de soporte..', 5);
                }
            }
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.result, function(index2, item2) {
                    gen_tabla_validaciones_ultimo_id = item2.id;
                    if(!(array_tabla_validaciones.includes(gen_tabla_validaciones_ultimo_id))){
                        array_tabla_validaciones.push(item2);
                    }
                });

                limpiar_tabla_validaciones();
                
                $.each(array_tabla_validaciones, function(index, item) {

                    var color = '';
                    if (parseInt(item.estado_id) === 0) {
                        color = '';
                    } else if (parseInt(item.estado_id) === 1) {
                        color = 'color:green;';
                    } else if (parseInt(item.estado_id) === 2) {
                        color = 'color:red;';
                    }
                    
                    var cuenta = "'" + item.cuenta + "'";
                    var monto_deposito = "'" + item.monto_deposito + "'";
                    var monto_deposito_td =item.monto_deposito.replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    var monto_comision = "'" + item.comision_monto + "'";
                    var monto_comision_td =item.comision_monto.replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    var monto_real = "'" + item.monto + "'";
                    var monto_real_td =item.monto.replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                    var observacion_cajero = "'" + ((item.observacion_cajero).replace(/(\r\n|\n|\r)/gm, "")).trim() + "'";
                    var observacion_validador = "'" + ((item.observacion_validador).replace(/(\r\n|\n|\r)/gm, "")).trim() + "'";

                    var ver = `<button type="button" class="btn btn-primary" onclick="ver_detalle(
                                ${item.id}, ${item.estado_id}, '0', ${item.valid_num_ope_existe}, ${item.valid_num_ope_unico}, 
                                ${item.cuenta_id}, ${cuenta}, ${item.bono_id}, '${item.bono_nombre}', '${item.num_operacion}', 
                                ${monto_deposito}, ${monto_comision}, 
                                ${observacion_cajero}, ${observacion_validador}, ${item.tipo_rechazo_id}, 
                                '${item.cliente}', '${item.tipo_contacto}', '${item.turno_local}', '${item.registro_deposito}', ${item.bono_mensual_actual}, ${item.valid_cuenta_yape},
                                '${item.titular_abono}','${item.tipo_constancia}', '${item.id_tipo_constancia}', '${item.cliente_id}', '${item.id_titular_abono}', '${item.tipo_jugada}',
                                '${item.ap_fecha_operacion}','${item.ap_hora_operacion}','${item.ap_nro_operacion}','${item.ap_nombre_medio}','${item.ap_monto}','${item.fecha_hora_registro}'
                            )"> <span class="glyphicon glyphicon-edit"></span></button>`;

                    $('#tabla_validaciones').append(
                        '<tr id="'+item.id+'">' +
                        '<td class="text-center">' + item.fecha_hora_registro + '</td>' +
                        '<td class="text-center">' + item.autovalidacion + '</td>' +
                        '<td class="text-center">' + item.cajero + '</td>' +
                        '<td class="text-center">' + item.turno_local + '</td>' +
                        '<td class="text-right">' + item.cliente + '</td>' +
                        '<td class="text-center" style="font-size:17px;font-weight:bold;">' + item.cuenta + '</td>' +
                        '<td class="text-center">' + item.bono_nombre + '</td>' +
                        '<td class="text-center">' + item.num_operacion + '</td>' +
                        '<td class="text-right" style="font-size:17px;font-weight:bold;">' + monto_deposito_td + '</td>' +
                        '<td class="text-right" style="font-size:17px;font-weight:bold;">' + monto_comision_td + '</td>' +
                        '<td class="text-right" style="font-size:17px;font-weight:bold;">' + monto_real_td + '</td>' +
                        '<td class="text-right">' + item.tipo_constancia + '</td>' +
                        '<td class="text-right" style="' + color + '"><b>' + item.estado + '<b></td>' +
                        '<td class="text-right">' + item.validador_nombre + '</td>' +
                        '<td class="text-right">' + item.titular_abono + '</td>' +
                        //'<td class="text-right">' + item.fecha_hora_validacion + '</td>' +
                        '<td class="text-center">' + ver + '</td>' +
                        '</tr>'
                    );

                    item2 = item;
                    if(item.estado === 'Pendiente'){
                        var nuevos = [];
                        var notificacionOld = JSON.parse(localStorage.getItem("listNew"));                                 
                        var filteredArray = null;
                        if (notificacionOld !== null) {
                            console.log("no ti not null");
                            filteredArray = notificacionOld.filter(function(ele , pos){
                                return notificacionOld.indexOf(ele) == pos;
                            });
                            notificacionOld = filteredArray; 
                        } 
                        nuevos = notificacionOld == null ? [] : notificacionOld;  
                            var nuevosMaximoRegistro =array_tabla_validaciones.length;
                            var regMaximo =3;
                            var title = item2.cuenta + ' - ' + item2.cliente;
                            var message = 'NUEVO DEPOSITO' ;
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
                tabla_validaciones_datatable_formato('#tabla_validaciones');
                return false;
            }
        },
        error: function() {}
    });
}
function exportar_tabla_validaciones(){
    var fecha_inicio = $("#buscador_fecha_inicio").val();
    var fecha_fin = $("#buscador_fecha_fin").val();
    var estado = $("#buscador_estado_transaccion").val();
    var cuenta = $("#buscador_cuenta").val();
    var validador = $("#buscador_validador").val() != '' ? gen_validador_seleccionado : '0';
    var autovalidacion = $('#buscador_autovalidacion_transaccion').val();
    var tipo_constancia = $('#buscador_tipo_constancia').val();

    //console.log(fecha_inicio.replace(/-/g, ""));

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menos a la de fin.', 'warning');
        return false;
    }

    var data = {
        "accion": "listar_transacciones_export_xls",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "estado": estado,
        "cuenta": cuenta,
        "validador": validador,
        "autovalidacion": autovalidacion,
        "tipo_constancia": tipo_constancia
    }
    $.ajax({
        url: "/sys/set_televentas_deposito.php",
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

function activar_desactivar_usarios_cuentas_apt(params) {
    $.ajax({
        url: "/sys/get_usuario_cuenta_apt.php",
        type: 'POST',
        data: params,
        async: true,
        success: function(resp) { //  alert(datat)
            console.log(resp);
            var respuesta = JSON.parse(resp);
           // console.log(respuesta);
                 
        },        
        error: function() {}
    });
}
function cargar_cargar_multiple_select(){
    $('#buscador_cuenta').multiselect({
           
            buttonClass:'form-control',
            buttonWidth: '100%',
            includeSelectAllOption: true, 
            onSelectAll: function(options) {
                $.each(options, function(index, item) {
                    //console.log(item[0].value);
                    data = {
                        'accion': 'crear_usuario_cuentas_apt',
                        'id_cuenta_apt': item[0].value,
                        'activar':1
                    } 
                    activar_desactivar_usarios_cuentas_apt(data);
                });
            }, 
            onDeselectAll: function(options) {
                $.each(options, function(index, item) {
                    //console.log(item[0].value);
                    data = {
                        'accion': 'crear_usuario_cuentas_apt',
                        'id_cuenta_apt': item[0].value,
                        'activar':0
                    } 
                    activar_desactivar_usarios_cuentas_apt(data);
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
                    'accion': 'crear_usuario_cuentas_apt',
                    'id_cuenta_apt': id_cuenta,
                    'activar':activar
                }               
                
                activar_desactivar_usarios_cuentas_apt(data);
            }
    });

    listar_tabla_validaciones();
}
function sec_televentas_depositos_listar_cuentas(){
    //var cuenta =$("#buscador_cuenta").val();
    var data ={
        "accion": "obtener_listado_cuentas"
    }
    $.ajax({
        url: "/sys/set_televentas_deposito.php",
        type: 'POST',
        data: data,
        success: function(resp) { //  alert(datat)
            var respuesta = JSON.parse(resp);

            $.each(respuesta.result, function(index, item) {

                var seleccionado ="";
                if (item.activos!=0){seleccionado = "selected";}

                if(item.id != 1){
                    $('#buscador_cuenta').append(                    
                        '<option value="' + item.id +'" '+seleccionado+'>' +item.cuenta_descripcion+'</option>'                                 
                    );
                } 
            });         
        },
        complete: function() {
            cargar_cargar_multiple_select();
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
    if (sec_id == 'televentas_depositos') {
        $('#div_dp_comision_select').change(function() {
            onchange_monto_calcular_total();
            return false;
        });
        $('#div_dp_btn_regresar').click(function() {
            $("#div_dp_resultado").hide();
            $("#div_dp_resultado_footer").hide();
            $("#div_televentas_validaciones").show();
        });
        $('#div_dp_num_operacion').keyup(function (){
            this.value = (this.value + '').replace(/[.]/g, '');
        });

        // CAMBIAR LOS INPUTS PARA LLENAR CON LOS DATOS DEL ABONO PENDIENTE
        $("#div_dp_origen").change(function(){
            // console.log("se cambia de abono pendiente al id de abono pendiente " + $("#div_dp_origen").val() );
            $("#div_dp_num_operacion").val('');
            $("#div_dp_comision_select").val('0.00').trigger('change');
            onchange_monto_calcular_total();
            $("#div_dp_id_abono_pendiente").val('');
            $("#div_dp_origen_abono_pendiente").val('');

            var valorSeleccionado = $(this).val();
            var origen_abono_pendiente = $(this).find('option[value="' + valorSeleccionado + '"]').attr('origen');

            if(parseInt($("#div_dp_origen").val())>0){
                llenar_data_abono_pendiente($("#div_dp_origen").val(), origen_abono_pendiente);
            } else {
                swal('Aviso', 'Seleccione una opción válida.', 'warning');
                return false;
            }
            

        });
    }
});

var gen_validacion_num_operacion_existe = 0;
var gen_validacion_num_operacion_unico = 0;
var gen_validacion_cuenta_yape = 0;

function limpiar_campos_div_dp_resultado() {
    $("#div_dp_id_abono_pendiente").val('0');
    $("#div_dp_origen_abono_pendiente").val('');
    $("#div_dp_valid_num_ope_existe").val('0');
    $("#div_dp_valid_num_ope_unico").val('0');
    gen_validacion_num_operacion_existe = 0;
    gen_validacion_num_operacion_unico = 0;
    gen_validacion_cuenta_yape = 0;
    $("#div_dp_imagen_lista").html('');
    $("#div_dp_origen").empty();
    $("#div_dp_cuenta").val('');
    $("#div_dp_registro").val('');
    $("#div_dp_num_operacion").val('');
    $("#div_dp_num_operacion").removeAttr("onKeyUp");
    $("#div_dp_num_operacion").removeAttr("oninput");
    $("#div_dp_monto_deposito").val('');
    $("#div_dp_comision_select").val('0.00');
    $('#div_dp_comision_select').select2().trigger('change');
    $("#div_dp_monto_comision").val('');
    $("#div_dp_monto_real").val('');
    $("#div_dp_bono_nombre").val('');
    $("#div_dp_bono_select").val('0');
    $("#div_dp_monto_bono").val('');
    $("#div_dp_monto_total").val('');
    $("#div_dp_titular_abono").val('');
    $("#div_dp_motivo").val('0');
    $('#div_dp_motivo').select2().trigger('change');
    $("textarea#div_dp_observacion_cajero").val('');
    $("textarea#div_dp_observacion_validador").val('');
    $('#sec_tlv_dep_file_comision').val('');

    $("#div_dp_motivo_contenedor").hide();
    $("#div_dp_btn_rechazar").show();
    $("#div_dp_btn_confirmar_rechazo").hide();
}
gen_cuenta_id_register = 0 ;
gen_num_ope_register = 0 ;
gen_tipo_constancia_register = 0 ;
gen_id_transaccion = 0 ;

function ver_detalle_x_abono(abono_id, origen){
    var get_abono_id = abono_id;
    var get_origen = origen;
    if(get_abono_id != ''){
        try{
            var data_get_trans = {
                "accion": "obtener_data_transaccion_x_abono_id",
                "abono_id": get_abono_id,
                "origen": get_origen
            };

            $.ajax({
                url: "/sys/set_televentas_deposito.php",
                type: 'POST',
                data: data_get_trans,
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                    loading();
                },
                success: function(resp) {
                    //console.log(resp); debugger;
                    var respuesta = JSON.parse(resp);
                    if (parseInt(respuesta.http_code) === 400) {
                        alertify.error(respuesta.status, 3);
                        return false;
                    }
                    if (parseInt(respuesta.http_code) === 200) {
                        if (respuesta.result.length > 0) {
                            var id_transaccion = '';
                            var id_estado = '';
                            var id_estado_aprov = '';
                            var valid_num_ope_existe = '';
                            var valid_num_ope_unico = '';
                            var cuenta_id = '';
                            var cuenta = '';
                            var bono_id = '';
                            var bono_nombre = '';
                            var num_operacion = '';
                            var monto_deposito = '';
                            var monto_comision = '';
                            var observacion_cajero = '';
                            var observacion_validador = '';
                            var tipo_rechazo_id = '';
                            var cliente_nombre = '';
                            var tipo_contacto = '';
                            var turno_local = '';
                            var registro_deposito = '';
                            var bono_mensual_actual = '';
                            var valid_cuenta_yape = '';
                            var titular_abono = '';
                            var tipo_constancia = '';
                            var id_tipo_constancia = '';
                            var id_cliente = '';
                            var id_titular_abono = '';
                            var tipo_jugada = '';

                            var ap_fecha_operacion = '';
                            var ap_hora_operacion = '';
                            var ap_nro_operacion = '';
                            var ap_nombre_medio = '';
                            var ap_monto = '';
                            var fecha_hora_registro = '';
                            $(respuesta.result).each(function (index, item) {
                                cuenta = item.cuenta;
                                monto_deposito = item.monto_deposito;
                                monto_comision = item.comision_monto;

                                observacion_cajero = ((item.observacion_cajero).replace(/(\r\n|\n|\r)/gm, "")).trim();
                                observacion_validador = + ((item.observacion_validador).replace(/(\r\n|\n|\r)/gm, "")).trim();

                                id_transaccion = item.id;
                                id_estado = item.estado_id;
                                id_estado_aprov = '0';
                                valid_num_ope_existe = item.valid_num_ope_existe;
                                valid_num_ope_unico = item.valid_num_ope_unico;
                                cuenta_id = item.cuenta_id;
                                
                                bono_id = item.bono_id;
                                bono_nombre = item.bono_nombre;
                                num_operacion = item.num_operacion;
                                monto_deposito = monto_deposito;
                                monto_comision = monto_comision;
                               
                                tipo_rechazo_id = item.tipo_rechazo_id;
                                cliente_nombre = item.cliente;
                                tipo_contacto = item.tipo_contacto;
                                turno_local = item.turno_local;
                                registro_deposito = item.registro_deposito;
                                bono_mensual_actual = item.bono_mensual_actual;
                                valid_cuenta_yape = item.valid_cuenta_yape;
                                titular_abono = item.titular_abono;
                                tipo_constancia = item.tipo_constancia;
                                id_tipo_constancia = item.id_tipo_constancia;
                                id_cliente = item.cliente_id;
                                id_titular_abono = item.id_titular_abono;
                                tipo_jugada = item.tipo_jugada;

                                ap_fecha_operacion = item.ap_fecha_operacion;
                                ap_hora_operacion = item.ap_hora_operacion;
                                ap_nro_operacion = item.ap_nro_operacion;
                                ap_nombre_medio = item.ap_nombre_medio;
                                ap_monto = item.ap_monto;
                                fecha_hora_registro = item.fecha_hora_registro;
                            });

                            if ( respuesta.data_yape ) {
                                $(respuesta.data_yape).each(function (index, item) {
                                    ap_fecha_operacion = item.fecha_operacion;
                                    ap_hora_operacion = item.hora_operacion;
                                    ap_nro_operacion = item.nro_operacion;
                                    ap_nombre_medio = item.nombre_medio;
                                    ap_monto = item.monto;
                                });
                            }

                            ver_detalle(id_transaccion, id_estado, id_estado_aprov, valid_num_ope_existe, valid_num_ope_unico, cuenta_id, 
                                cuenta, bono_id, bono_nombre, num_operacion, monto_deposito, monto_comision, observacion_cajero, 
                                observacion_validador, tipo_rechazo_id, cliente_nombre, tipo_contacto, turno_local, registro_deposito, 
                                bono_mensual_actual, valid_cuenta_yape, titular_abono, tipo_constancia, id_tipo_constancia, id_cliente, 
                                id_titular_abono, tipo_jugada, ap_fecha_operacion, ap_hora_operacion, ap_nro_operacion, ap_nombre_medio, ap_monto, fecha_hora_registro)
                        } else {
                            
                        }
                        return false;
                    }
                },
                error: function() {}
            });
        }catch(error){

        }
    }else{

    }
}

function ver_detalle(id_transaccion, id_estado, id_estado_aprov, valid_num_ope_existe, valid_num_ope_unico, cuenta_id, cuenta, bono_id, bono_nombre, num_operacion, monto_deposito, monto_comision, 
    observacion_cajero, observacion_validador, tipo_rechazo_id, cliente_nombre, tipo_contacto, turno_local, registro_deposito, bono_mensual_actual, valid_cuenta_yape, titular_abono, tipo_constancia, 
    id_tipo_constancia, id_cliente, id_titular_abono, tipo_jugada, ap_fecha_operacion, ap_hora_operacion, ap_nro_operacion, ap_nombre_medio, ap_monto, fecha_hora_registro) {
    //console.log('A');
    
    if(parseInt(id_estado) == 1 && parseInt(id_estado_aprov) == 1){
        $('.SecTelDep_edit_control').show();
        gen_cuenta_id_register = cuenta_id;
        gen_id_transaccion = id_transaccion;
        gen_num_ope_register = num_operacion;
        gen_tipo_constancia_register = id_tipo_constancia;
        editar_detalle();
    }else{
        $('.SecTelDep_edit_control').hide();
    }
    limpiar_campos_div_dp_resultado();
    //console.log('B');
    cargar_tercero_aut(id_cliente, id_titular_abono,titular_abono);
    
    $("#nombre_cliente").text(cliente_nombre+' | '+tipo_jugada+' | '+turno_local);
    $('#nombre_cliente').attr('data-text', cliente_nombre);
    var acumulado_cliente_bono = bono_mensual_actual;
    $('#acumulado_bono_cliente').html('TOTAL BONO: ' + acumulado_cliente_bono);
    $("#div_televentas_validaciones").hide();
    $("#div_dp_resultado").show();
    //console.log('C');
    if (parseInt(id_estado) === 0) {
        $("#div_dp_resultado_footer").show();

        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        var temp_fecha_hora_actual = now.toISOString().slice(0, -5);
        //console.log("temp_fecha_hora_actual: " + temp_fecha_hora_actual);
        //console.log("temp_fecha_hora_actual: " + temp_fecha_hora_actual.substr(0,16));
        $("#div_dp_registro").val(temp_fecha_hora_actual.substr(0,16));
        $("#sec_tlv_dep_tipo_constancia").attr('disabled', false);
        $("#div_dp_num_operacion").attr('disabled', false);
        $("#div_dp_comision_select").attr('disabled', false);
        $("#sec_tlv_dep_file_comision").attr('disabled', false);
        $("#div_dp_observacion_validador").attr('disabled', false);

        $('#div_dp_titular_abono').attr('disabled', false);
        $('#div_dp_origen').attr('disabled', false);
    } else {
        if (parseInt(id_estado) === 2) {
            $("#div_dp_motivo_contenedor").show();
            $("#div_dp_motivo").val(tipo_rechazo_id);
            $('#div_dp_motivo').select2().trigger('change');
        } else {
            $("#div_dp_registro").val(registro_deposito.substr(0,16).replace(" ", "T"));
        }

        $("#div_dp_comision_select").attr('disabled', true);
        $("#sec_tlv_dep_file_comision").attr('disabled', true);
        $("#div_dp_observacion_validador").attr('disabled', true);
        $("#div_dp_num_operacion").attr('disabled', true);
        $("#sec_tlv_dep_tipo_constancia").attr('disabled', true);

        $('#div_dp_titular_abono').attr('disabled', true);
        $('#div_dp_origen').attr('disabled', true);
    }

    //console.log('D');
    $("#input_tlv_dep_id_transaccion").val(id_transaccion);
    $("#div_dp_cuenta").val(cuenta);
    $("#div_dp_id_cuenta").val(cuenta_id); 

    if (parseInt(id_estado) === 1) {
        $('#sec_tlv_dep_div_edit').show();
    }else{
        $('#sec_tlv_dep_div_edit').hide();
    }
    

    //console.log(cuenta.slice(0, 4));
    /*
    if(cuenta.slice(0, 4) === 'Yape' || cuenta.slice(0, 4) === 'YAPE') {
        gen_validacion_num_operacion=1;
    }
    */
    /*
    $("#div_dp_comision_select").removeAttr('disabled');
    if(parseFloat(monto_deposito) >= 80){
        $("#div_dp_comision_select").attr('disabled', 'disabled');
    }
    */
    //debugger;
    gen_validacion_num_operacion_existe = valid_num_ope_existe;
    gen_validacion_num_operacion_unico = valid_num_ope_unico;
    gen_validacion_cuenta_yape = valid_cuenta_yape;
    $("#div_dp_valid_num_ope_existe").val(valid_num_ope_existe);
    $("#div_dp_valid_num_ope_unico").val(valid_num_ope_unico);
    $("#div_dp_valid_cuenta_yape").val(valid_cuenta_yape);
    
    $("#div_dp_num_operacion").val(num_operacion);
    var monto_deposito_sin_formato_de_miles = monto_deposito;

    monto_deposito = (parseFloat(monto_deposito).toFixed(2)).replace(/\D/g, "")
                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    
    $("#div_dp_bono_nombre").val(bono_nombre);
    $("#div_dp_monto_deposito").val(monto_deposito);
    $("#div_dp_comision_select").val(monto_comision);
    $("#div_dp_bono_select").val(bono_id);

    onchange_monto_calcular_total();
    /*
    $("#div_dp_monto_comision").val(monto_comision);
    $("#div_dp_monto_real").val(monto_real);
    $("#div_dp_monto_bono").val(monto_bono);
    $("#div_dp_monto_total").val(monto_total);
    */

    // Bloqueo las teclas que no sean numeros, en caso que la cuenta no sea YAPE
    /* if( !(cuenta_id === 3 || cuenta_id === 4 || cuenta_id === 14 || cuenta_id === 15 || cuenta_id === 22) ){
        // console.log("no es yape, la cuenta es "+cuenta_id);
        // $("#div_dp_num_operacion").attr("oninput","this.value=this.value.replace(/[^0-9]/g,'');");
        if( cuenta_id === 1 || cuenta_id === 2 || cuenta_id === 9 || cuenta_id === 10 || cuenta_id === 11 ||
            cuenta_id === 12 || cuenta_id === 17 || cuenta_id === 18 || cuenta_id === 20 || cuenta_id === 21 ){
            console.log("no es yape, pero es Interbank o BBVA");
            // $("#div_dp_num_operacion").removeAttr("oninput");
            $("#div_dp_num_operacion").attr("onKeyUp","pierdeFoco(this)");
            $("#div_dp_num_operacion").attr("oninput","this.value=this.value.replace(/[^0-9+]/g,'');");
        }
    } */

    if( cuenta_id === 1 || cuenta_id === 2 || cuenta_id === 9 || cuenta_id === 10 || cuenta_id === 11 ||
        cuenta_id === 12 || cuenta_id === 17 || cuenta_id === 18 || cuenta_id === 20 || cuenta_id === 21 ){
        //console.log("es Interbank o BBVA");
        $("#div_dp_num_operacion").attr("onKeyUp","pierdeFoco(this)");
        $("#div_dp_num_operacion").attr("oninput","this.value=this.value.replace(/[^0-9+]/g,'');");
    }

    $("textarea#div_dp_observacion_cajero").val(observacion_cajero);
    $("#div_dp_titular_abono").val(titular_abono);
    $("textarea#div_dp_observacion_validador").val(observacion_validador);
    $('#div_dp_btn_rechazar').removeAttr('onclick');
    $('#div_dp_btn_rechazar').attr('onclick', 'mostrar_opciones_rechazo()');
    $('#div_dp_btn_confirmar_rechazo').removeAttr('onclick');
    $('#div_dp_btn_confirmar_rechazo').attr('onclick', "guardar_validacion_deposito(" + id_transaccion + ", 2, " + cuenta_id + ", 0)");
    $('#div_dp_btn_validar').removeAttr('onclick');
    $('#div_dp_btn_validar').attr('onclick', "guardar_validacion_deposito(" + id_transaccion + ", 1, " + cuenta_id + ", 0)");

    // TRAER ABONOS PENDIENTES
    if(parseInt(id_estado) === 0){
        var data1 = {
            "accion": "obtener_abonos_pendientes",
            "cuenta_id": cuenta_id,
            "id_cliente": id_cliente,
            "id_titular_abono": id_titular_abono,
            "titular_abono": titular_abono,
            "fecha_hora_registro": fecha_hora_registro,
            "monto_deposito": monto_deposito_sin_formato_de_miles
        }

        auditoria_send({ "proceso": "obtener_abonos_pendientes", "data": data1 });
        $.ajax({
            url: "/sys/set_televentas_deposito.php",
            type: 'POST',
            data: data1,
            beforeSend: function() {
                loading("true");
            },
            complete: function() {
                loading();
            },
            success: function(resp) { //  alert(datat)
                var respuesta = JSON.parse(resp);
                //console.log(respuesta);
                if (parseInt(respuesta.http_code) === 400) {
                    $('#div_dp_origen').append('<option value="0" origen="">:: No hay abonos pendientes ::</option>');
                    //$('#div_dp_origen').select2(0).trigger('change');
                    //swal('Aviso', respuesta.status, 'warning');
                    return false;
                }
                if (parseInt(respuesta.http_code) === 200) {
                    if (respuesta.result.length > 0) {
                        $('#div_dp_origen').append('<option value="0" origen="">:: Seleccione ::</option>');
                        //$('#div_dp_origen').select2(0).trigger('change');
                        $(respuesta.result).each(function (i, e) {
                            var opcion = $("<option value='" + e.cod_transaccion + "' origen='" + e.origen_abono_pendiente + "'>" + e.fecha_operacion + ' ' + 
                                                e.hora_operacion + ' || ' + e.nro_operacion + ' || ' + e.nombre_medio + ' || ' + 
                                                e.monto + ' || ' + e.cliente +
                                            "</option>");
                            $('#div_dp_origen').append(opcion);
                        });
                    } else {
                        $('#div_dp_origen').append('<option value="0" origen="">:: No hay abonos pendientes ::</option>');
                        //$('#div_dp_origen').select2(0).trigger('change');
                    }
                    return false;
                }
            },
            error: function() {}
        });
    }else{
        var opcion = "";
        if(ap_fecha_operacion != ''){
            opcion = "<option>" + 
                        ap_fecha_operacion + ' ' + ap_hora_operacion + ' || ' + ap_nro_operacion + ' || ' + ap_nombre_medio + ' || ' + ap_monto +
                    "</option>"
        }else{
            opcion = "<option>No hay abono pendiente</option>"
        }
        $('#div_dp_origen').append(opcion);
    }

    var data = {
        "accion": "obtener_imagenes_x_transaccion",
        "id_transaccion": id_transaccion
    }

    auditoria_send({ "proceso": "obtener_imagenes_x_transaccion", "data": data });
    $.ajax({
        url: "/sys/set_televentas_deposito.php",
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
                    $('#div_dp_imagen_lista').append(
                        '<div class="col-md-12">' +
                        '   <div align="center" style="height: 100%;width: 100%;">' +
                        '       <img  id="' + nuevo_id + '" src="files_bucket/depositos/' + item.archivo + '" width="500px" />' +
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


    var tipo_cuenta = 1; // bancos
    if(gen_validacion_cuenta_yape == 1){
        tipo_cuenta = 2; // yape
    }
    var data = {
        "accion": "sec_tlv_dep_obtener_tipos_constancia",
        "tipo_cuenta": tipo_cuenta
    }
    $.ajax({
        url: "/sys/set_televentas_deposito.php",
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
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#sec_tlv_dep_tipo_constancia').html('');
                $.each(respuesta.result, function(index, item) {
                    $('#sec_tlv_dep_tipo_constancia').append(
                        '<option value="' + item.id + '" '+(item.id == id_tipo_constancia ? 'selected' : '') +' >' + item.descripcion +  '</option>'
                    );
                }); 
                $('#sec_tlv_dep_tipo_constancia').select2();
                if (parseInt(id_estado) != 0){
                    if(id_tipo_constancia != 0){
                        $('#sec_tlv_dep_tipo_constancia').val(id_tipo_constancia).trigger('change');
                    }else{
                        $('#sec_tlv_dep_tipo_constancia').val(0).trigger('change');
                    }
                }
                return true;
            }
        },
        error: function() {}
    });
}

function llenar_data_abono_pendiente(abono_pendiente_id, origen_abono_pendiente){
    // limpiar_campos_div_dp_resultado();

    // LLENAR DATA DE ABONOS PENDIENTES
    var data = {
        "accion": "obtener_data_abono_pendiente",
        "abono_pendiente_id": abono_pendiente_id,
        "origen_abono_pendiente": origen_abono_pendiente
    }

    auditoria_send({ "proceso": "obtener_data_abono_pendiente", "data": data });
    $.ajax({
        url: "/sys/set_televentas_deposito.php",
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
                // var select = $("[name='div_dp_origen']");
                var respuesta = JSON.parse(resp);
                
                $(respuesta.result).each(function (i, e) {
                    $("#div_dp_num_operacion").val(e.nro_operacion);
                    // $("#div_dp_bono_nombre").val(bono_nombre);
                    $("#div_dp_comision_select").val(e.comision_id).trigger('change');
                    // $("#div_dp_bono_select").val(bono_id);
                    $("#div_dp_registro").val(e.fecha_operacion+' '+e.hora_operacion.substr(0,16).replace(" ", "T"));

                    // $("#div_dp_monto_real").val(monto_real);
                    onchange_monto_calcular_total();

                    $("#div_dp_id_abono_pendiente").val(e.cod_transaccion);
                    $("#div_dp_origen_abono_pendiente").val(e.origen_abono_pendiente);

                    // console.log(respuesta.result[0].nro_operacion );
                    
                });
                return false;
            }
        },
        error: function() {}
    });

}
function onchange_monto_calcular_total() {
    var monto = $('#div_dp_monto_deposito').val().replace(/\,/g, '');
    var comision_select = $('#div_dp_comision_select').val();
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
    bono = (parseFloat(bono).toFixed(2)).replace(/\D/g, "")
                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    total = (parseFloat(total).toFixed(2)).replace(/\D/g, "")
                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
    $('#div_dp_monto_comision').val(comision);
    $('#div_dp_monto_real').val(monto_real);
    $('#div_dp_monto_bono').val(bono);
    $('#div_dp_monto_total').val(total);
    return false;
}


function mostrar_opciones_rechazo() {
    $("#div_dp_motivo_contenedor").show();
    $("#div_dp_btn_rechazar").hide();
    $("#div_dp_btn_confirmar_rechazo").show();
}

function limpiar_bordes_validacion_deposito() {
    $('#div_dp_registro_div').css('border', '');
    $('#div_dp_num_operacion_div').css('border', '');
    $('#div_dp_monto').css('border', '');
    $('#div_dp_motivo_contenedor').css('border', '');
    $('#div_dp_observacion').css('border', '');
}


function guardar_validacion_deposito(id_transaccion, id_estado, cuenta_id, permitir_dupl) {
    $("#div_dp_resultado_footer").hide();
    limpiar_bordes_validacion_deposito();

    var registro = $.trim($('#div_dp_registro').val()) + ":00";
    var num_operacion = $.trim($('#div_dp_num_operacion').val());
    var bono_select = $("#div_dp_bono_select").val().replace(/\,/g, '');
    var monto_deposito = $("#div_dp_monto_deposito").val().replace(/\,/g, '');
    var monto_comision = $("#div_dp_comision_select").val().replace(/\,/g, '');
    var monto_real = $("#div_dp_monto_real").val().replace(/\,/g, '');
    var monto_bono = $("#div_dp_monto_bono").val().replace(/\,/g, '');
    var monto_total = $("#div_dp_monto_total").val().replace(/\,/g, '');
    var motivo = $("#div_dp_motivo").val();
    var observacion_validador = $("textarea#div_dp_observacion_validador").val();
    var tipo_constancia = $("#sec_tlv_dep_tipo_constancia").val();
    var new_titular_abono = $("#div_dp_titular_abono").val();
    
    //console.log("registro: " + registro);
    if (registro.length !== 19) {
        $("#div_dp_resultado_footer").show();
        $("#div_dp_registro_div").css("border", "1px solid red");
        $('#div_dp_registro').focus();
        return false;
    }
    //debugger;
    //console.log("gen_validacion_num_operacion_existe: " + gen_validacion_num_operacion_existe);
    //console.log("gen_validacion_num_operacion_unico: " + gen_validacion_num_operacion_unico);
    //console.log("gen_validacion_cuenta_yape: " + gen_validacion_cuenta_yape);
    if((parseInt(gen_validacion_num_operacion_existe) === 1 && parseInt(id_estado) === 1) 
                || parseInt(gen_validacion_cuenta_yape) == 1 && parseInt(id_estado) == 1){
        if (!(num_operacion.length > 0)) {
            $("#div_dp_resultado_footer").show();
            $("#div_dp_num_operacion_div").css("border", "1px solid red");
            $('#div_dp_num_operacion').focus();
            return false;
        }
    }
    if (!(parseFloat(monto_deposito) > 0)) {
        $("#div_dp_resultado_footer").show();
        $('#div_dp_monto_deposito').css('border', '1px solid red');
        $('#div_dp_monto_deposito').focus();
        return false;
    }
    if (!(parseFloat(monto_real) > 0)) {
        $("#div_dp_resultado_footer").show();
        $('#div_dp_monto_deposito').css('border', '1px solid red');
        $('#div_dp_monto_deposito').focus();
        return false;
    }
    if (!(parseFloat(monto_total) > 0)) {
        $("#div_dp_resultado_footer").show();
        $('#div_dp_monto_deposito').css('border', '1px solid red');
        $('#div_dp_monto_deposito').focus();
        return false;
    }

    if ( parseFloat(monto_bono) > 0 ) {
        if (!( parseFloat(bono_select) > 0 )) {
            $("#div_dp_resultado_footer").show();
            $('#div_dp_monto_deposito').css('border', '1px solid red');
            $('#div_dp_monto_deposito').focus();
            return false;
        }
    }
    /*
    if (!(  parseFloat(monto_deposito) >= parseFloat(monto_real) && parseFloat(monto_deposito) > parseFloat(monto_comision) && 
            parseFloat(monto_real) > parseFloat(monto_comision) && parseFloat(monto_real) > parseFloat(monto_bono) && 
            parseFloat(monto_total) >= parseFloat(monto_deposito) && parseFloat(monto_total) >= parseFloat(monto_real)
        )) {
        $("#div_dp_resultado_footer").show();
        $('#div_dp_monto_deposito').css('border', '1px solid red');
        $('#div_dp_monto_deposito').focus();
        return false;
    }
    */
    if (parseInt(id_estado) === 2) {
        if (!(parseInt(motivo) > 0)) {
            $("#div_dp_resultado_footer").show();
            $('#div_dp_motivo_contenedor').css('border', '1px solid red');
            $('#div_dp_motivo').focus();
            return false;
        }
    }

    var imagen = $('#sec_tlv_dep_file_comision').val();
    var f_imagen = $("#sec_tlv_dep_file_comision")[0].files[0];
    var imagen_extension = imagen.substring(imagen.lastIndexOf("."));
    
    if ((imagen.length > 0)) {
       if (imagen_extension !== ".png" && imagen_extension !== ".PNG" &&
                imagen_extension !== ".jpg" && imagen_extension !== ".JPG" &&
                imagen_extension !== ".jpeg" && imagen_extension !== ".JPEG") {
            $("#sec_tlv_dep_file_comision_div").css("border", "1px solid red");
            swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
            $("#div_dp_resultado_footer").show();
            return false;
        }
    }

    var id_abono_pendiente = $("#div_dp_id_abono_pendiente").val();
    var origen_abono_pendiente = $("#div_dp_origen_abono_pendiente").val();
    var data = new FormData();
    data.append('accion', "guardar_validacion_deposito");
    data.append('id_transaccion', id_transaccion);
    data.append('id_estado', id_estado);
    data.append('validacion_num_operacion_unico', gen_validacion_num_operacion_unico);
    data.append('gen_validacion_cuenta_yape', gen_validacion_cuenta_yape);
    data.append('cuenta_id', cuenta_id);
    data.append('observacion', observacion_validador);
    data.append('registro', registro);
    data.append('num_operacion', num_operacion);
    data.append('bono_select', bono_select);
    data.append('monto_deposito', monto_deposito);
    data.append('monto_comision', monto_comision);
    data.append('monto_real', monto_real);
    data.append('monto_bono', monto_bono);
    data.append('monto_total', monto_total);
    data.append('motivo', motivo);
    data.append('id_abono_pendiente', id_abono_pendiente);
    data.append('origen_abono_pendiente', origen_abono_pendiente);
    data.append('imagen_voucher', f_imagen);
    data.append('permitir_dupl', permitir_dupl);
    data.append('tipo_constancia', tipo_constancia);
    data.append('new_titular_abono', new_titular_abono);

    $.ajax({
        url: "/sys/set_televentas_deposito.php",
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
            //auditoria_send({ "respuesta": "guardar_validacion_deposito_respuesta", "data": respuesta });
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                $("#div_dp_resultado_footer").show();
                if(respuesta.question_yape == 1){
                    swal({
                        html : true,
                        title : respuesta.status,
                        type : 'warning',
                        showCancelButton : true,
                        cancelButtonColor : '#d33',
                        cancelButtonText: 'NO',
                        confirmButtonColor : '#0336FF',
                        confirmButtonText : 'SI',
                        closeOnConfirm : false
                    }, function(){
                        guardar_validacion_deposito(id_transaccion, id_estado, cuenta_id, 1)
                    });
                }else{
                    swal('Aviso', respuesta.status, 'warning');
                }
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $("#div_dp_resultado").hide();
                $("#div_dp_resultado_footer").hide();
                $("#div_televentas_validaciones").show();
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                listar_tabla_validaciones();
                return false;
            }
        },
        error: function (result) {
           
        }
    });

    

}







































function tabla_validaciones_datatable_formato(id) {
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

function sec_tlv_dep_abrir_modal(){

    var fecha_hora_actual = $('#div_dp_registro').val();
    $('#div_dp_registro_edit').val(fecha_hora_actual);

    $('#sec_tlv_dep_modal_editar_fecha_hora').modal({backdrop: 'static', keyboard: false});
}

function sec_tlv_dep_copiar_hora_abono(){
    var fecha_hora_actual = $('#div_dp_registro').val();
    var hora = fecha_hora_actual.substr(11,16);

    var $bridge = $("<input>")
    $("body").append($bridge);
    $bridge.val(hora).select();
    document.execCommand("copy");
    $bridge.remove();
    var cuenta_id = $("#div_dp_id_cuenta").val();
    //debugger;
    if($("#div_dp_valid_cuenta_yape").val() == 1){
         $('#div_dp_num_operacion').val(hora);
    }
    /*
    if(cuenta_id == 3 || cuenta_id == 4 || cuenta_id == 14){ // Validar para que solo se copie en Numero de Operacion cuando sea YAPE
        $('#div_dp_num_operacion').val(hora);
    }
    */
}

function sec_tlv_dep_actualizar_fecha_abono(){
    var fecha_hora_actual = $('#div_dp_registro').val();
    var fecha_hora_modificada = $('#div_dp_registro_edit').val();

    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    var temp_fecha_hora_actual = now.toISOString().slice(0, -5);


    /* console.log("temp_fecha_hora_actual: " + fecha_hora_actual.substr(0,10));

    var diferencia = (Date.parse(fecha_hora_modificada) - Date.parse(fecha_hora_actual))/(1000*60*60*24);

    if(diferencia > 31 || diferencia < -31){
        swal('Aviso', 'La fecha no puede puede disminuir o aumentar mas de 31 días.', 'warning');
        return;
    } */

    var transaccion_id = $("#input_tlv_dep_id_transaccion").val();
    //debugger;
    var data = {
        "accion": "sec_tlv_dep_actualizar_fecha_abono",
        "id_transaccion": transaccion_id,
        "fecha_hora_actual": fecha_hora_actual,
        "fecha_hora_modificada": fecha_hora_modificada
    }

    auditoria_send({ "proceso": "sec_tlv_dep_actualizar_fecha_abono", "data": data });
    $.ajax({
        url: "/sys/set_televentas_deposito.php",
        type: 'POST',
        data: data,
        beforeSend: function() { loading("true"); },
        complete: function() { loading(); },
        success: function(resp) {
            console.log(resp);
            //debugger;
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
                loading(false);
                 $('#sec_tlv_dep_modal_editar_fecha_hora').modal('hide');
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#div_dp_registro').val(fecha_hora_modificada);
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                loading(false);
                 $('#sec_tlv_dep_modal_editar_fecha_hora').modal('hide');
            }
        },
        error: function() {}
    });
}
var secTlvDep_edit_data = [];
function editar_detalle() {
    $('.SecTelDep_edit_control').show();
    $('#sec_tlv_dep_edit_modal_cuenta').val(gen_cuenta_id_register).trigger('change');
    $('#sec_tlv_dep_edit_ver_imagen').attr('src', '');
    $('#sec_tlv_dep_edit_imagen_voucher').val('');

}

$('#sec_tlv_dep_edit_btn_cuenta').click(function(){
    $('#sec_tlv_dep_modal_cuenta').modal('show');
});

$('#sec_tlv_dep_edit_btn_cancel_changes').click(function(){
    $("#div_dp_resultado").hide();
    $("#div_dp_resultado_footer").hide();
    $("#div_televentas_validaciones").show();
});


/********************************************************************************/
/******************************* GUARDAR CAMBIOS ********************************/
/********************************************************************************/
$('#sec_tlv_dep_edit_btn_save_changes').click(function(){
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
        var cuenta_id_nuevo = $('#sec_tlv_dep_edit_modal_cuenta').val();
        if(cuenta_id_nuevo == 0){ cuenta_id_nuevo = gen_cuenta_id_register; }

        var num_ope_nuevo = $('#div_dp_num_operacion').val();
        if($.trim(num_ope_nuevo) == ""){ num_ope_nuevo = gen_num_ope_register; }

        var id_tipo_constancia_nuevo = $('#sec_tlv_dep_tipo_constancia').val();
        if($.trim(id_tipo_constancia_nuevo) == ""){ id_tipo_constancia_nuevo = gen_tipo_constancia_register; }
        
        secTlvDep_edit_data = [];
        secTlvDep_edit_data.push(
            {'campo' : 'cuenta_id', 'valor_anterior': gen_cuenta_id_register, 'valor_nuevo' : cuenta_id_nuevo , 'updated' : (gen_cuenta_id_register == cuenta_id_nuevo)},
            {'campo' : 'num_operacion', 'valor_anterior': gen_num_ope_register, 'valor_nuevo' : num_ope_nuevo , 'updated' : (gen_num_ope_register == num_ope_nuevo)},
            {'campo' : 'id_tipo_constancia', 'valor_anterior': gen_tipo_constancia_register, 'valor_nuevo' : id_tipo_constancia_nuevo , 'updated' : (gen_tipo_constancia_register == id_tipo_constancia_nuevo)}

        );
        //console.log(secTlvDep_edit_data);

        var imagen = $('#sec_tlv_dep_edit_imagen_voucher').val();
        var f_imagen = $("#sec_tlv_dep_edit_imagen_voucher")[0].files[0];
        
        var data = new FormData();
        data.append('accion', "sec_tlv_dep_guardar_cambios_registro");
        data.append('id_transaccion', gen_id_transaccion);
        data.append('rr_data', JSON.stringify(secTlvDep_edit_data));
        data.append('imagen_voucher', f_imagen);
        data.append('imagen', imagen);

        //auditoria_send({ "proceso": "sec_tlv_dep_guardar_cambios_registro", "data": data });
        $.ajax({
            url: "/sys/set_televentas_deposito.php",
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
                //console.log(resp); debugger;
                var respuesta = JSON.parse(resp);
                //auditoria_send({ "respuesta": "sec_tlv_dep_guardar_cambios_registro_respuesta", "data": respuesta });
                console.log(respuesta);
                if (parseInt(respuesta.http_code) == 400) {
                    $('.SecTelDep_edit_control').show();
                    swal('Aviso', respuesta.status, 'warning');
                    return false;
                }
                if (parseInt(respuesta.http_code) == 200) {
                    swal('Aviso', 'Acción realizada con éxito.', 'success');
                    $('.SecTelDep_edit_control').hide();
                    $('#sec_tlv_dep_modal_cuenta').modal('hide');
                    $("#div_dp_resultado").hide();
                    $("#div_dp_resultado_footer").hide();
                    $("#div_televentas_validaciones").show();
                    listar_tabla_validaciones();
                    return false;
                }
            },
            error: function() {}
        });
    });
});

$("#sec_tlv_dep_edit_imagen_voucher").change(function (e) {
    var filePath = this.value;
    var allowedExtensions = /(.jpg|.jpeg|.png|.gif)$/i;
    if(!allowedExtensions.exec(filePath)){
        swal('Aviso', 'El archivo debe ser PNG, JPG ó JPEG.', 'warning');
        this.value = '';
        return false;
    }else{
        var newPath = filePath.replace('jpg','png');
        sec_tlv_dep_readImage(this);
    }
});

function sec_tlv_dep_readImage(input) {
    $('#div_dp_imagen_lista').html('');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#sec_tlv_dep_edit_ver_imagen').attr('src', e.target.result);
        }
        console.log(input.files[0].name);
        reader.readAsDataURL(input.files[0]);
        $("sec_tlv_dep_edit_ver_imagen").imgViewer2();
    }
}

function sec_tlv_dep_actualizar_cuenta(){
    var cuenta_id = $('#sec_tlv_dep_edit_modal_cuenta').val();
    if(cuenta_id > 0){
        var text_cuenta = $.trim($('#sec_tlv_dep_edit_modal_cuenta option:selected').text());
        $('#div_dp_cuenta').val(text_cuenta);
        $('#sec_tlv_dep_modal_cuenta').modal('hide');
    }else{
        $('#sec_tlv_dep_edit_modal_cuenta').val(gen_cuenta_id_register).trigger('change');
        swal('Aviso', 'Debe seleccionar una cuenta para cambiar', 'warning');
    }
}
//Limpiar input de imagen y volver a cargar el comprobante original
$('#sec_tlv_dep_limpiar_input_image').click(function(){
    $('#sec_tlv_dep_edit_ver_imagen').attr('src', '');
    $('#sec_tlv_dep_edit_imagen_voucher').val('');

    var data = {
        "accion": "obtener_imagenes_x_transaccion",
        "id_transaccion": gen_id_transaccion
    }

    auditoria_send({ "proceso": "obtener_imagenes_x_transaccion", "data": data });
    $.ajax({
        url: "/sys/set_televentas_deposito.php",
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
                $('#div_dp_imagen_lista').html('');
                $.each(respuesta.result, function(index, item) {
                    var nuevo_id = (respuesta.fecha_hora).toString() + '_' + (item.id).toString();
                    $('#div_dp_imagen_lista').append(
                        '<div class="col-md-12">' +
                        '   <div align="center" style="height: 100%;width: 100%;">' +
                        '       <img  id="' + nuevo_id + '" src="files_bucket/depositos/' + item.archivo + '" width="500px" />' +
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

window.addEventListener("paste", function (e) {
    retrieveImageFromClipboardAsBlob(e, function (imageBlob) {
        if (imageBlob) {
            let fileInputElement = undefined;
            if($('#div_dp_resultado').is(':visible')){
                fileInputElement = document.getElementById('sec_tlv_dep_file_comision');
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

function retrieveImageFromClipboardAsBlob(pasteEvent, callback) {
    if (pasteEvent.clipboardData == false) {
        if (typeof (callback) == "function") {
            callback(undefined);
        }
    }
    ;
    var items = pasteEvent.clipboardData.items;
    if (items == undefined) {
        if (typeof (callback) == "function") {
            callback(undefined);
        }
    }
    ;
    for (var i = 0; i < items.length; i++) {
        // Skip content if not image
        if (items[i].type.indexOf("image") == -1)
            continue;
        // Retrieve image on clipboard as blob
        var blob = items[i].getAsFile();

        if (typeof (callback) == "function") {
            callback(blob);
        }
    }
}


$('#sec_tlv_dep_edit_btn_num_operacion').click(function(){
    $("#div_dp_num_operacion").attr('disabled', false);
});


$('#div_dp_num_operacion').keyup(function(e){
    var num_operacion = $('#div_dp_num_operacion').val();
    if(gen_validacion_cuenta_yape == 0){ // Si no es yape
        //Validar numero de operación, que no contenga 0 a la izquierda
        num_operacion = num_operacion.replace(/^(0+)/g, '');
        $('#div_dp_num_operacion').val(num_operacion);

    }
});

$('#div_dp_registro').on('change', function(e){
    var dato = $(this).val();
    if(gen_validacion_cuenta_yape == 1){
        $('#div_dp_num_operacion').val(dato.substring(11,16));
    }
    var data = {
        "accion": "sec_tlv_dep_obtener_fecha_hora"
    }
    $.ajax({
        url: "/sys/set_televentas_deposito.php",
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
                gen_fecha_hora = respuesta.result.substring(0,16);
                var dato_d = dato.replace('T',' ');
                if(dato_d > gen_fecha_hora){
                    $('#div_dp_registro').val(gen_fecha_hora.replace(' ','T'));
                    dato = $('#div_dp_registro').val();
                    if(gen_validacion_cuenta_yape == 1){
                        $('#div_dp_num_operacion').val(dato.substring(11,16));
                    }
                    alertify.error('La fecha de abono no puede ser mayor a la actual', 3);
                }
            }
        },
        error: function() {}
    });

    
});

function sec_tlv_dep_listar_tipo_constancia(){
    var tipo_cuenta = 1; // bancos
    if(gen_validacion_cuenta_yape == 1){
        tipo_cuenta = 2; // yape
    }
    var data = {
        "accion": "sec_tlv_dep_obtener_tipos_constancia",
        "tipo_cuenta": tipo_cuenta
    }
    $.ajax({
        url: "/sys/set_televentas_deposito.php",
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
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#sec_tlv_dep_tipo_constancia').html('');
                $.each(respuesta.result, function(index, item) {
                    $('#sec_tlv_dep_tipo_constancia').append(
                        '<option value="' + item.id + '">' + item.descripcion +  '</option>'
                    );
                });
                $('#sec_tlv_dep_tipo_constancia').select2();
                return true;
            }
        },
        error: function() {}
    });
}

$('#sec_tlv_dep_edit_btn_tipo_constancia').click(function(){
    $('#sec_tlv_dep_tipo_constancia').attr('disabled', false);
});


function cargar_tercero_aut(cliente_id, id_titular_abono, titular_abono) {
 
    $('#div_dp_titular_abono').empty();  

    var data = {
        "accion": "obtener_tlv_tercero_aut",
        "id_cliente": cliente_id 
    }

    $.ajax({
        url: "/sys/set_televentas_deposito.php",
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
                swal('Aviso', respuesta.status, 'warning');
                //console.log(respuesta);
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                //console.log(respuesta);
                
                if(respuesta.result == "Sin registros de terceros autorizados"){
                    $('#div_dp_titular_abono').append(
                        '<option value=""  selected >' + titular_abono + '</option>'
                    );
                }else{

                    if(parseInt(id_titular_abono) === 0){

                        $('#div_dp_titular_abono').append(
                            '<option value=""  selected >' + titular_abono + '</option>'
                        );
    
                        $.each(respuesta.result, function(index, item) {
                            $('#div_dp_titular_abono').append(
                                '<option value="' + item.id + '" >' + item.nombre_apellido_titular + ' - ' + item.dni_titular + '</option>'
                            );
                        });
                        
                    }else{
                        $.each(respuesta.result, function(index, item) {
                            $('#div_dp_titular_abono').append(
                                '<option value="' + item.id + '" '+(item.id == id_titular_abono ? 'selected' : '') +' >' + item.nombre_apellido_titular + ' - ' + item.dni_titular + '</option>'
                            );
                        });
                        
                    }

                }

                return false;
            }
        },
        error: function (result) {
            auditoria_send({"proceso": "obtener_televentas_titular_abono_reg", "data": result});
        }
    });
}

function sec_tlv_dep_obtener_totales(){
    var fecha_inicio = $("#buscador_fecha_inicio").val();
    var fecha_fin = $("#buscador_fecha_fin").val();
    var estado = $("#buscador_estado_transaccion").val();
    var cuenta = $("#buscador_cuenta").val();
    var validador = $("#buscador_validador").val() != '' ? gen_validador_seleccionado : '0';
    var autovalidacion = $('#buscador_autovalidacion_transaccion').val();
    var tipo_constancia = $('#buscador_tipo_constancia').val();


    $('#SecTelDel_cant_abonos_pendientes').val('0');
    $('#SecTelDel_monto_abonos_pendientes').val('0.00');
    var cant_abonos_pendientes = 0;
    var total_abonos_pendientes = 0;

    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        swal('Aviso', 'La fecha de inicio debe ser menos a la de fin.', 'warning');
        return false;
    }

    var data = {
        "accion": "sec_tlv_obtener_totales_transacciones",
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
        "estado": estado,
        "cuenta": cuenta,
        "validador": validador,
        "autovalidacion": autovalidacion,
        "tipo_constancia": tipo_constancia
    }
    $.ajax({
        url: "/sys/set_televentas_deposito.php",
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
            
            if ( respuesta.totales ) {
                $.each(respuesta.totales, function(index, item) {
                    cant_abonos_pendientes += parseInt(item.cant);
                    total_abonos_pendientes += parseFloat(item.total);
                });

                total_abonos_pendientes = (total_abonos_pendientes.toFixed(2)).replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                $('#SecTelDel_cant_abonos_pendientes').val(cant_abonos_pendientes);
                $('#SecTelDel_monto_abonos_pendientes').val(total_abonos_pendientes);
            }
      
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {

                var monto_cant_dep_d_ant = 0;
                var monto_sum_dep_d_ant = 0;
                var monto_total_depositado = 0;
                var monto_total_comision = 0;
                var monto_total_real = 0;
                var monto_cant_depositado = 0;
                var cant_autovalidaciones = 0;
                var monto_total_autovalidaciones = 0;
                $.each(respuesta.result, function(index, item){
                    monto_cant_dep_d_ant = item.num_ant;
                    monto_sum_dep_d_ant = item.total_ant;
                    monto_cant_depositado = item.cant;
                    cant_autovalidaciones = item.cant_autovalidaciones;
                    monto_total_depositado = parseFloat(item.monto_deposito);
                    monto_total_comision = parseFloat(item.comision_monto);
                    monto_total_real = parseFloat(item.monto);
                    monto_total_autovalidaciones = parseFloat(item.monto_total_autovalidaciones);
                });
                
                monto_total_depositado = (monto_total_depositado.toFixed(2)).replace(/\D/g, "")
                                    .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                monto_total_comision = (monto_total_comision.toFixed(2)).replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                monto_total_real = (monto_total_real.toFixed(2)).replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                monto_total_autovalidaciones = (monto_total_autovalidaciones.toFixed(2)).replace(/\D/g, "")
                                            .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

            
                $('#SecTelDel_monto_cant_dep_d_ant_yape').val(monto_cant_dep_d_ant);
                $('#SecTelDel_monto_dep_d_ant_yape').val(monto_sum_dep_d_ant);
                $('#SecTelDel_monto_cant_depositado').val(monto_cant_depositado);
                $('#SecTelDel_monto_total_depositado').val(monto_total_depositado);
                $('#SecTelDel_monto_total_comision').val(monto_total_comision);
                $('#SecTelDel_monto_total_real').val(monto_total_real);
                $('#SecTelDel_cant_autovalidaciones').val(cant_autovalidaciones);
                $('#SecTelDel_monto_total_autovalidaciones').val(monto_total_autovalidaciones);
                return false;
            }
        },
        error: function (result) {
            auditoria_send({"proceso": "sec_tlv_obtener_totales_transacciones_error", "data": result});
        }
    });
}