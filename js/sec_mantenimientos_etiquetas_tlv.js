
$(function () {

    $("#modal_editar_nuevo_limite_dias_ret_dep").on('input', function (evt) {
		// Allow only numbers.
		$(this).val($(this).val().replace(/[^0-9]/g, ''));
	});
    $('#sec_eti_modal_etiqueta_tipo').select2();
    $('#modal_motivos_tipo').select2();
});

function sec_mantenimientos_etiquetas_tlv() {
    if (sec_id == 'mantenimientos' && sub_sec_id=='etiquetas_tlv') {
        listar_SecManEtiTlv_tabla_principal();
        $('.money').mask('0000000', {'translation': {0: {pattern: /[0-9.]/}}});

        $('#SecManEtiTlv_btn_nuevo').click(function() {
            modal_etiqueta_limpiar_campos();
            $('#modal_etiqueta_btn_guardar').show();
            $('#modal_etiqueta_btn_guardar').removeAttr("onclick");
            $('#modal_etiqueta_btn_guardar').attr("onclick", 'mante_guardar_etiqueta("0")');
            $('#modal_etiqueta').modal();
            return false;
        });

        $('#SecManEtiTlv_btn_nueva_comision').click(function() {
            listar_SecManComisionTlv_tabla_principal();
            modal_comision_limpiar_campos();
            $('#modal_comision_btn_guardar').show();
            $('#modal_comision_btn_guardar').removeAttr("onclick");
            $('#modal_comision_btn_guardar').attr("onclick", 'mante_guardar_motivo("0")');
            $('#modal_comision').modal();
            setTimeout(function() {
                $('#modal_comision_i_comision').focus();
            }, 500);
            return false;
        });

        $('#SecManEtiTlv_btn_nueva_programacion').click(function() {
            $('#sec_mant_modal_programacion_pago').modal();
            sec_mant_listarSupervisores();
            resetear_fecha_hora_inputs();
            sec_mant_listarProgramaciones();

        });

        $('#SecManEtiTlv_btn_limite_clientes').click(function() {
            $('#sec_mant_modal_limite_clientes').modal();
            sec_mant_buscar_ult_limite();
        });

         ///// ETIQUETAS MASIVAS

         $('#SecManEtiTlv_btn_etiquetas_masivas').click(function() {
            $('#sec_mant_modal_etiquetas_masivas').modal();
            $('#SecManEtiTlv_modal_etiq').val('');
            $('#SecManEtiTlv_modal_etiq_cli').val('');
            sec_mant_buscar_etiquetas();   

        });

        $('#modal_etiqueta_masivas_btn_guardar').click(function() {
            sec_mant_guardar_etiquetas_masivas();   

        });

        $('#SecManEtiTlv_modal_etiq').autocomplete({
            source: '/sys/set_mantenimientos_etiquetas_tlv.php?accion=SecManEtiTlv_modal_lista_etiq',
            minLength: 2,
            select: function (event, ui)
            {
                gen_etiq_seleccionado=ui.item.codigo;
                if(gen_etiq_seleccionado == undefined){
                    gen_etiq_seleccionado = 0;
                }

            }
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            $('ul.ui-menu').css('display', 'block !important');
            $('ul.ui-menu').css('z-index', '100000');
            return $( "<li>" )
            .append( item.label)
            .appendTo( ul );
        };

        var gen_etiq_seleccionado = 0; 

        $('#SecManEtiTlv_modal_etiq_cli').autocomplete({
            source: '/sys/set_mantenimientos_etiquetas_tlv.php?accion=SecManEtiTlv_modal_lista_etiq_cli',
            minLength: 2,
            select: function (event, ui)
            {
                gen_etiq_cli_seleccionado=ui.item.codigo;
                if(gen_etiq_cli_seleccionado == undefined){
                    gen_etiq_cli_seleccionado = 0;
                }

            }
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            $('ul.ui-menu').css('display', 'block !important');
            $('ul.ui-menu').css('z-index', '100000');
            return $( "<li>" )
            .append( item.label)
            .appendTo( ul );
        };

        var gen_etiq_cli_seleccionado = 0; 

        $('#SecManEtiTlv_modal_btn_etiq_cli').click(function() {
            sec_mant_agregar_cliente_temp(gen_etiq_seleccionado, gen_etiq_cli_seleccionado);
        });

        ///// FIN ETIQUETAS MASIVAS

        $('#SecManEtiTlv_btn_comprobante_de_pago_sin_notificar').click(function () {
            $('#sec_mant_modal_comprobante_de_pago_sin_notificar').modal();
            sec_mant_buscar_comprobante_de_pago_sin_notificar();
        });

        $('#SecManEtiTlv_btn_limite_dias_editar_depositos_retiros').click(function() {
            $('#sec_modal_limite_dias_editar_depositos_retiros').modal();
            mostrar_dias_editables_para_depositos_retiros();
        });

        $('#modal_etiqueta_i_color').ColorPicker({
            color: '#0000ff',
            onShow: function (colpkr) {
                $(colpkr).fadeIn(500);
                return false;
            },
            onHide: function (colpkr) {
                $(colpkr).fadeOut(500);
                return false;
            },
            onChange: function (hsb, hex, rgb) {
                $('#modal_etiqueta_div_pintar').css('background', '#' + hex);
                $("#modal_etiqueta_i_color").val('#'+hex);
            }
        });

        $('#SecManEtiTlv_btn_nuevo_motivo').click(function() {
            listar_SecManMotivosTlv_tabla_principal();
            modal_motivos_limpiar_campos();
            $('#modal_motivos_btn_guardar').show();
            $('#modal_motivos_btn_guardar').removeAttr("onclick");
            $('#modal_motivos_btn_guardar').attr("onclick", 'mante_guardar_motivo("0")');
            $('#modal_motivos').modal();
            setTimeout(function() {
                $('#modal_motivos_nuevo').focus();
            }, 500);
            return false;
        });
    }
}



function limpiar_SecManEtiTlv_tabla_principal() {
    $('#SecManEtiTlv_tabla_principal').html(
        '<thead>' +
        '   <tr>' +
        '       <th class="text-center" width="5%">#</th>' +
        '       <th class="text-center" width="20%">ETIQUETA</th>' +
        '       <th class="text-center" width="30%">DESCRIPCIÓN</th>' +
        '       <th class="text-center" width="5%">COLOR</th>' +
        '       <th class="text-center" width="5%">TIPO</th>' +
        '       <th class="text-center" width="12%">CLIENTES</th>' +
        '       <th class="text-center" width="15%">REGISTRO</th>' +
        '       <th class="text-center" width="13%">ACCIONES</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>'
    );
}

function listar_SecManEtiTlv_tabla_principal() {
    limpiar_SecManEtiTlv_tabla_principal();

    var data = {
        "accion": "listar_transacciones"
    }

    auditoria_send({ "proceso": "listar_transacciones", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
                loading("true");
        },
        complete: function() {
                loading();
        },
        success: function(resp) { //  alert(datat)
            //console.log(resp); debugger;
            var respuesta = JSON.parse(resp);
            //console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                if (respuesta.result.length > 0) {
                    $.each(respuesta.result, function(index, item) {
                        var variables="'"+ item.id +"','"+item.label+"','"+item.description+"','"+item.color+"','" + item.tipo_id + "'";

                        var acciones = '<div class="btn-group" role="group">';
                        if(item.tipo_id == 1){
                            if(permiso_editar){
                                acciones += 
                                    '<button type="button" class="btn btn-default" onclick="obtener_etiqueta(' + variables + ')" style="padding: 3px 7px;">'+
                                    '<i class="fa fa-edit fa-2x" style="font-size: 1.5em;color:black;"></i>' +
                                    '</button>';
                            }
                            if(permiso_eliminar){
                                acciones += 
                                    '<button type="button" class="btn btn-default" onclick="mante_eliminar_etiqueta(' + item.id + ')" style="padding: 3px 7px;">'+
                                    '<i class="fa fa-trash fa-2x" style="font-size: 1.5em;color:black;"></i>' +
                                    '</button>';
                            }
                        }
                        
                        acciones += '</div>';

                        $('#SecManEtiTlv_tabla_principal').append(
                            '<tr>' +
                            '<td class="text-center">' + (index+1) + '</td>' +
                            '<td class="text-center">' + item.label + '</td>' +
                            '<td class="text-center">' + item.description + '</td>' +
                            '<td class="text-center" style="background-color: '+item.color+'"></td>' +
                            '<td class="text-center">' + item.tipo + '</td>' +
                            '<td class="text-center">' + item.cant_clientes + '</td>' +
                            '<td class="text-center">' + item.created_at + '</td>' +
                            '<td class="text-center">'+acciones+'</td>' +
                            '</tr>'
                        );
                    });
                    DATATABLE_FORMATO_SecManEtiTlv_tabla_principal('#SecManEtiTlv_tabla_principal');
                } else {
                    $('#SecManEtiTlv_tabla_principal').append(
                        '<tr>' +
                        '<td class="text-center" colspan="8">No hay transacciones.</td>' +
                        '</tr>'
                    );
                }
                //console.log(array_clientes);
                return false;
            }
        },
        error: function() {}
    });
}


function listar_SecManComisionTlv_tabla_principal() {
    var data = {
        "accion": "listar_comisiones"
    }

    auditoria_send({ "proceso": "listar_comisiones", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#tbl_comisiones').html(respuesta.result);
                setTimeout(function() {
            DATATABLE_FORMATO_SecManEtiTlv_tabla_principal('#tbl_comisiones_data');
        }, 500);
                
                //console.log(array_clientes);
                return false;
            }
        },
        error: function() {}
    });
}

function listar_SecManMotivosTlv_tabla_principal() {
    var data = {
        "accion": "listar_motivos"
    }

    auditoria_send({ "proceso": "listar_motivos", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
            console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#tbl_motivos').html(respuesta.result);
                setTimeout(function() {
                    DATATABLE_FORMATO_SecManEtiTlv_tabla_principal('#tbl_motivos_data');
                }, 500);
                
                //console.log(array_clientes);
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
// DINERO AT
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
//************************************************************************************************
$(function() {

    $('#modal_tlv_mant_etiq_dinero_at_evento_inicio').datetimepicker({
        format: 'YYYY-MM-DD',
        minDate: $("#sectlv_manteetiquetas_fecha_actual").val()
    });

    $('#modal_tlv_mant_etiq_dinero_at_evento_fin').datetimepicker({
        format: 'YYYY-MM-DD',
        minDate: $("#sectlv_manteetiquetas_fecha_actual").val()
    });

    $('#modal_tlv_mant_etiq_dinero_at_evento_inicio').val($('#sectlv_manteetiquetas_fecha_actual').val());
    $('#modal_tlv_mant_etiq_dinero_at_evento_fin').val($('#sectlv_manteetiquetas_fecha_actual').val());

    $('#modal_tlv_mant_etiq_dinero_at_evento_inicio').change(function () {
        var var_fecha_change = $('#modal_tlv_mant_etiq_dinero_at_evento_inicio').val();
        if (!(parseInt(var_fecha_change.length) > 0)) {
            $("#modal_tlv_mant_etiq_dinero_at_evento_inicio").val($("#sectlv_manteetiquetas_fecha_actual").val());
        }
    });

    $('#modal_tlv_mant_etiq_dinero_at_evento_fin').change(function () {
        var var_fecha_change = $('#modal_tlv_mant_etiq_dinero_at_evento_fin').val();
        if (!(parseInt(var_fecha_change.length) > 0)) {
            $("#modal_tlv_mant_etiq_dinero_at_evento_fin").val($("#sectlv_manteetiquetas_fecha_actual").val());
        }
    });

    $('#modal_dinero_at_editar_fecha_inicio').datetimepicker({
        format: 'YYYY-MM-DD'
    });

    $('#modal_dinero_at_editar_fecha_fin').datetimepicker({
        format: 'YYYY-MM-DD'
    });

    
    
    $('#modal_tlv_mant_etiq_dinero_at_evento_clientes_limite,#modal_tlv_mant_etiq_dinero_at_evento_rollover,#modal_dinero_at_editar_clientes_limite').on('input', function () { 
        this.value = this.value.replace(/[^0-9]/g,'');
    });
    var val_dat_clientes_limite = $("#modal_tlv_mant_etiq_dinero_at_evento_clientes_limite");
    val_dat_clientes_limite.on("blur", function() {
        var valor1 = val_dat_clientes_limite.val();
        valor1 = valor1.replace(/^0+/, '');
        val_dat_clientes_limite.val(valor1);
    });

    var val_dat_rollover = $("#modal_tlv_mant_etiq_dinero_at_evento_rollover");
    val_dat_rollover.on("blur", function() {
        var valor2 = val_dat_rollover.val();
        valor2 = valor2.replace(/^0+/, '');
        val_dat_rollover.val(valor2);
    });
    var val_dat_editar_clientes_limite = $("#modal_dinero_at_editar_clientes_limite");
    val_dat_editar_clientes_limite.on("blur", function() {
        var valor_edit_lim = val_dat_editar_clientes_limite.val();
        valor_edit_lim = valor_edit_lim.replace(/^0+/, '');
        val_dat_editar_clientes_limite.val(valor_edit_lim);
    });
    $('#modal_dinero_at_editar_clientes_limite').on('input', function () { 
        this.value = this.value.replace(/^\d{10}$/,'');
    });

    $('#modal_tlv_mant_etiq_dinero_at_evento_codigo').on('input', function () { 
        this.value = this.value.replace(/[^a-zA-Z0-9]/g,'');
        this.value = this.value.toUpperCase();
    });
    $('#modal_tlv_mant_etiq_dinero_at_evento_nombre').on('input', function () { 
        this.value = this.value.replace(/[\'\"]/g,'');
    });
    $('#modal_tlv_mant_etiq_dinero_at_evento_descripcion').on('input', function () { 
        this.value = this.value.replace(/[\'\"]/g,'');
    });

    $("#modal_tlv_mant_etiq_dinero_at_evento_monto_minimo").on({
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
    $("#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente").on({
        "focus": function (event) {
            $(event.target).select();
            //console.log('focus');
        },
        "blur": function (event) {
            var valor_conv_max = $('input[type="radio"][name="modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio"]:checked').val();
            if ( parseInt(valor_conv_max)===1 ) {
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
            } else {
                $(event.target).val(function (index, value) {
                    return value.replace(/[^0-9]/g,'')
                                .replace(/^0+/, '');
                });
            }
        }
    });

    $("#modal_tlv_mant_etiq_dinero_at_evento_conversion_max").on({
        "focus": function (event) {
            $(event.target).select();
            //console.log('focus');
        },
        "blur": function (event) {
            var valor_conv_max = $('input[type="radio"][name="modal_tlv_mant_etiq_dinero_at_evento_conversion_max_radio"]:checked').val();
            if ( parseInt(valor_conv_max)===1 ) {
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
            } else {
                $(event.target).val(function (index, value) {
                    return value.replace(/[^0-9]/g,'')
                                .replace(/^0+/, '');
                });
            }
        }
    });

    $('#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente, #modal_tlv_mant_etiq_dinero_at_evento_conversion_max, #modal_tlv_mant_etiq_dinero_at_evento_monto_minimo').on('input', function () { 
        this.value = this.value.replace(/[^0-9\.]/g,'');
    });

    $('input[type="radio"][name="modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio"]').change(function() {
        $('#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente').removeAttr('disabled');
        $('#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente').val('');
        $('#modal_tlv_mant_etiq_dinero_at_evento_monto_minimo').val('');

        if ($('#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio_soles').prop('checked')) {
            $('#modal_tlv_mant_etiq_dinero_at_evento_monto_minimo').hide();
        } else if ($('#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio_porcentaje').prop('checked')) {
            $('#modal_tlv_mant_etiq_dinero_at_evento_monto_minimo').show();
        } else {
            $('#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente').attr('disabled', true);
        }
    });

    $('input[type="radio"][name="modal_tlv_mant_etiq_dinero_at_evento_conversion_max_radio"]').change(function() {
        $('#modal_tlv_mant_etiq_dinero_at_evento_conversion_max').removeAttr('disabled');
        $('#modal_tlv_mant_etiq_dinero_at_evento_conversion_max').val('');
    });

    // Manejar el checkbox "Marcar/Desmarcar todos"
    $('#select_all').click(function(event) {
        if (this.checked) {// Seleccionar todos los checkboxes con clase check_trans
            $('.check_trans').each(function() {
                this.checked = true;
            });
        } else { // Deseleccionar todos los checkboxes con clase check_trans
            $('.check_trans').each(function() {
                this.checked = false;
            });
        }
    });
    
    $('.check_trans').click(function(event) {
        var esta_virtuales = $("#modal_tlv_mant_etiq_dinero_at_juegos_virtuales").prop("checked");
        var esta_bingo = $("#modal_tlv_mant_etiq_dinero_at_bingo").prop("checked");
        var esta_sportbook = $("#modal_tlv_mant_etiq_dinero_at_sportbook").prop("checked");

        // Verificar el estado y tomar acción en consecuencia
        if ( !esta_virtuales || !esta_bingo || !esta_sportbook ) {
            $('#select_all').removeAttr('checked');
        } else if ( esta_virtuales && esta_bingo && esta_sportbook ) {
            $('#select_all').prop('checked', true);
        }
    });
    
    //Abrir el modal para dinero AT
    $('#sec_tlv_btn_import_dinero_at').click(function () {
        // console.log("entrooo");
        limpiar_campos_modal_dinero_at();
        listar_tabla_eventos_dinero_at();
        $('#modal_tlv_mant_etiq_dinero_at').modal();
    });
    $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').click(function () {
        guardar_nuevo_codigo_promocional();
        return false;
    });
    // importar_dinero_at();



});

function formato_datatable_sec_mant_etiq_tabla_dinero_at__destroy(id) {
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
        $('#tabla_eventos_dinero_at tbody').empty();
    }
}

function formato_datatable_sec_mant_etiq_tabla_dinero_at(id) {
    $(id).DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': false,
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

function limpiar_tabla_dinero_at() {
    $('#tabla_eventos_dinero_at tbody').html('');
}

function limpiar_campos_modal_dinero_at() {
    $("#modal_tlv_mant_etiq_dinero_at_evento_nombre").val('');
    $("#modal_tlv_mant_etiq_dinero_at_evento_codigo").val('');
    $("#modal_tlv_mant_etiq_dinero_at_evento_descripcion").val('');
    $("#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente").val('');
    $("#modal_tlv_mant_etiq_dinero_at_evento_conversion_max").val('');
    $("#modal_tlv_mant_etiq_dinero_at_evento_clientes_limite").val('');
    $("#modal_tlv_mant_etiq_dinero_at_evento_rollover").val(1);
    $('#modal_tlv_mant_etiq_dinero_at_evento_inicio').val($('#sectlv_manteetiquetas_fecha_actual').val());
    $('#modal_tlv_mant_etiq_dinero_at_evento_fin').val($('#sectlv_manteetiquetas_fecha_actual').val());
    $('#select_all').removeAttr('checked');
    $('#modal_tlv_mant_etiq_dinero_at_juegos_virtuales').removeAttr('checked');
    $('#modal_tlv_mant_etiq_dinero_at_bingo').removeAttr('checked');
    $('#modal_tlv_mant_etiq_dinero_at_sportbook').removeAttr('checked');
    $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();

    $("input[name='modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio']").prop('checked', false);
    $("input[name='modal_tlv_mant_etiq_dinero_at_evento_conversion_max_radio']").prop('checked', false);
    $('#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente').attr('disabled', true);
    $('#modal_tlv_mant_etiq_dinero_at_evento_conversion_max').attr('disabled', true);
    $('#modal_tlv_mant_etiq_dinero_at_evento_monto_minimo').hide();
}

function listar_tabla_eventos_dinero_at(){
    limpiar_tabla_dinero_at();
    var data = {
        "accion": "listar_eventos_dinero_at"
    }
    auditoria_send({ "proceso": "listar_eventos_dinero_at", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            // debugger;
            var respuesta = JSON.parse(resp);
            // console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                $('#tabla_eventos_dinero_at tbody').append(
                    '<tr>' +
                    '<td class="text-center" colspan="12">'+ respuesta.status +'</td>' +
                    '</tr>'
                );
                return false;
            }else if(parseInt(respuesta.http_code) == 200) {
                // console.log(respuesta.data);
                formato_datatable_sec_mant_etiq_tabla_dinero_at__destroy('#tabla_eventos_dinero_at');
                $.each(respuesta.data, function(index, item) {

                    item.monto_cliente = item.monto_cliente.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    item.limite_monto = item.limite_monto.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    item.conversion_maxima = item.conversion_maxima.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    item.porcentaje_monto_minimo = item.porcentaje_monto_minimo.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                    
                    signo_monto_minimo = "S/ ";
                    if ( !parseFloat(item.porcentaje_monto_minimo)>0 ){
                        item.porcentaje_monto_minimo = "-";
                        signo_monto_minimo = "";
                    }

                    var signo_monto_soles = "";
                    var signo_monto_porcentaje = "";
                    if( parseInt(item.tipo_monto)==1 ) {
                        signo_monto_soles = "S/ ";
                    }else{
                        signo_monto_porcentaje = " %";
                        item.limite_monto = "-";
                        item.monto_cliente = parseFloat(item.monto_cliente).toFixed(2).replace(/\.00$/, '');
                    }

                    var signo_conversion_soles = "";
                    var signo_conversion_porcentaje = "";
                    if( parseInt(item.tipo_conversion)==1 ) {
                        signo_conversion_soles = "S/ ";
                    }else{
                        signo_conversion_porcentaje = " %";
                        item.conversion_maxima = parseFloat(item.conversion_maxima).toFixed(2).replace(/\.00$/, '');
                    }

                    var btn_eliminar = "";
                    var btn_import = "";
                    var btn_listar_clientes = "";
                    var btn_editar = "";

                    // if( parseInt(item.eliminable) === 1 ){
                        btn_eliminar = ' <button type="button" class="btn btn-danger" data-toggle="tooltip" data-placement="bottom" title="Eliminar la Promo" style="padding: 2px 3px;" onclick="eliminar_evento_dinero_at( ';
                        btn_eliminar += " '" + item.id + "' , '" + item.codigo_evento + "' ";
                        btn_eliminar += ' )"><span class="fa fa-times"></span></button> ';
                    // }
                    if( parseInt(item.importable) === 1 ){
                        btn_import = ' <button type="button" class="btn btn-success" data-toggle="tooltip" data-placement="bottom" title="Cargar Clientes" style="padding: 2px 3px;" onclick="importar_clientes_dinero_at( ';
                        btn_import += " '" + item.id + "', '" + item.codigo_evento + "' ";
                        btn_import += ' )"><span class="fa fa-cloud-upload"></span></button> ';

                        btn_editar = ' <button type="button" class="btn btn-warning" data-toggle="tooltip" data-placement="bottom" title="Editar Promo" style="padding: 2px 3px;" onclick="modal_editar_evento_dineroAT( ' ;
                        btn_editar += " '" + item.id + "', '" + item.codigo_evento + "' ";
                        btn_editar += ' )"><span class="fa fa-pencil"></span></button> ';
                    }
                    btn_listar_clientes = ' <button type="button" class="btn btn-info" data-toggle="tooltip" data-placement="bottom" title="Listar Clientes" style="padding: 2px 3px;" onclick="listar_clientes_evento_at( ';
                    btn_listar_clientes += " '" + item.id + "', '" + item.codigo_evento + "' ";
                    btn_listar_clientes += ' )"><span class="fa fa-list"></span></button> ';

                    var icon1 = '';
                    var icon2 = '';
                    var icon3 = '';
                    var icon4 = '';
                    parseInt(item.juegos_virtuales_activo) ? icon1='<i class="fa fa-check text-success"></i>' : icon1='<i class="fa fa-remove text-danger"></i>' ; 
                    parseInt(item.bingo_activo)            ? icon2='<i class="fa fa-check text-success"></i>' : icon2='<i class="fa fa-remove text-danger"></i>' ; 
                    // parseInt(item.recargas_activo)         ? icon3='<i class="fa fa-check text-success"></i>' : icon3='<i class="fa fa-remove text-danger"></i>' ; 
                    parseInt(item.sportbook_activo)        ? icon4='<i class="fa fa-check text-success"></i>' : icon4='<i class="fa fa-remove text-danger"></i>' ; 

                    $('#tabla_eventos_dinero_at tbody').append(
                        '<tr id="'+item.id+'" class="sec_tlv_pag_listado_transaccion" style="height:28px">' +
                            '<td style="display:none;" class="sec_tlv_pag_listado_transaccion_id_transaccion">' + item.id + '</td>' +
                            '<td class="text-center">' + item.codigo_evento + '</td>' +
                            '<td class="text-center">' + item.fecha_inicio + '</td>' +
                            '<td class="text-center">' + item.fecha_fin + '</td>' +
                            // '<td class="text-center">' + signo_monto_soles + item.limite_monto + '</td>' +
                            '<td class="text-center">' + signo_monto_minimo + item.porcentaje_monto_minimo + '</td>' +
                            '<td class="text-center">' + signo_monto_soles + item.monto_cliente + signo_monto_porcentaje + '</td>' +
                            '<td class="text-center">' + item.limite_clientes + '</td>' +
                            '<td class="text-center">' + item.cantidad_clientes + '</td>' +
                            '<td class="text-center">' + item.cant_clientes_usando + '</td>' +
                            '<td class="text-center">' + signo_conversion_soles + item.conversion_maxima + signo_conversion_porcentaje + '</td>' +
                            '<td class="text-center">' + item.rollover + '</td>' +
                            '<td class="text-center">' + icon1 + '</td>' +
                            '<td class="text-center">' + icon2 + '</td>' +
                            // '<td class="text-center">' + icon3 + '</td>' +
                            '<td class="text-center">' + icon4 + '</td>' +
                            '<td class="text-center">' + btn_eliminar + btn_import + btn_listar_clientes + btn_editar + '</td>' +
                        '</tr>'
                    );
                });
                formato_datatable_sec_mant_etiq_tabla_dinero_at('#tabla_eventos_dinero_at');
                return false;
            }
            return false;
        },
        error: function() {}
    });
}

function guardar_nuevo_codigo_promocional(){
    // Limpiar borders, ocultar botón de registrar
    $('#modal_tlv_mant_etiq_dinero_at_evento_nombre_div').css('border', '');
	$('#modal_tlv_mant_etiq_dinero_at_evento_codigo_div').css('border', '');
	$('#modal_tlv_mant_etiq_dinero_at_evento_descripcion_div').css('border', '');
	$('#modal_tlv_mant_etiq_dinero_at_evento_inicio_div').css('border', '');
	$('#modal_tlv_mant_etiq_dinero_at_evento_fin_div').css('border', '');
	$('#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_div').css('border', '');
	$('#modal_tlv_mant_etiq_dinero_at_evento_conversion_max_div').css('border', '');
	$('#modal_tlv_mant_etiq_dinero_at_evento_clientes_limite_div').css('border', '');
	$('#modal_tlv_mant_etiq_dinero_at_juegos_div').css('border', '');
	$('#modal_tlv_mant_etiq_dinero_at_evento_rollover_div').css('border', '');
	$('#modal_tlv_mant_etiq_dinero_at_btn_enviar').hide();

    var nombre          = $.trim($('#modal_tlv_mant_etiq_dinero_at_evento_nombre').val());
    var codigo          = $.trim($('#modal_tlv_mant_etiq_dinero_at_evento_codigo').val());
    var descripcion     = $.trim($('textarea#modal_tlv_mant_etiq_dinero_at_evento_descripcion').val());
    var fecha_inicio    = $("#modal_tlv_mant_etiq_dinero_at_evento_inicio").val();
    var fecha_fin       = $("#modal_tlv_mant_etiq_dinero_at_evento_fin").val();
    var monto_cliente   = $('#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente').val().replace(/\,/g, '');
    var conversion_max  = $('#modal_tlv_mant_etiq_dinero_at_evento_conversion_max').val().replace(/\,/g, '');
    var clientes_limite = $('#modal_tlv_mant_etiq_dinero_at_evento_clientes_limite').val();
    var check_virtuales = $('#modal_tlv_mant_etiq_dinero_at_juegos_virtuales').prop('checked');
    var check_bingo     = $('#modal_tlv_mant_etiq_dinero_at_bingo').prop('checked');
    var check_sportbook = $('#modal_tlv_mant_etiq_dinero_at_sportbook').prop('checked');
    var tipo_monto      = $('input:radio[name=modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_radio]:checked').val();
    var tipo_conversion = $('input:radio[name=modal_tlv_mant_etiq_dinero_at_evento_conversion_max_radio]:checked').val();
    var monto_minimo    = $('#modal_tlv_mant_etiq_dinero_at_evento_monto_minimo').val();
    var rollover        = $('#modal_tlv_mant_etiq_dinero_at_evento_rollover').val();

    /* console.log(tipo_monto_cliente);
    return false; */

    if( $.trim(nombre) === '' ){
        $('#modal_tlv_mant_etiq_dinero_at_evento_nombre_div').css('border', '1px solid red');
        $('#modal_tlv_mant_etiq_dinero_at_evento_nombre').focus();
        $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
		swal({
			title: "Ingrese nombre",
			text: "Debe ingresar, un nombre para el evento promocional.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		});
        return false;
    }
    if( $.trim(codigo) === '' ){
        $('#modal_tlv_mant_etiq_dinero_at_evento_nombre_div').css('border', '1px solid red');
        $('#modal_tlv_mant_etiq_dinero_at_evento_nombre').focus();
        $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
		swal({
			title: "Ingrese código",
			text: "Debe ingresar, un código para el evento promocional.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		});
        return false;
    }
    if (fecha_inicio.length !== 10) {
        $("#modal_tlv_mant_etiq_dinero_at_evento_inicio_div").css("border", "1px solid red");
        $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
        swal('Aviso', 'Seleccione una fecha inicio válida.', 'warning');
        return false;
    }
    if (fecha_fin.length !== 10) {
        $("#modal_tlv_mant_etiq_dinero_at_evento_fin_div").css("border", "1px solid red");
        $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
        swal('Aviso', 'Seleccione una fecha fin válida.', 'warning');
        return false;
    }
    if ( fecha_inicio < $('#sectlv_manteetiquetas_fecha_actual').val() ) {
        $("#modal_tlv_mant_etiq_dinero_at_evento_inicio_div").css("border", "1px solid red");
        $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
        swal('Aviso', 'La fecha de inicio, debe ser como mínimo hoy.', 'warning');
        return false;
    }
    // console.log( fecha_inicio.replace(/-/g, "") );
    if (parseInt(fecha_fin.replace(/-/g, "")) < parseInt(fecha_inicio.replace(/-/g, ""))) {
        $("#modal_tlv_mant_etiq_dinero_at_evento_fin_div").css("border", "1px solid red");
        $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
        swal('Aviso', 'La fecha fin debe ser mayor o igual a la de inicio.', 'warning');
        return false;
    }
    if ( parseInt(tipo_monto)!=1 && parseInt(tipo_monto)!=2 ){
        $("#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_div").css("border", "1px solid red");
        $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
        swal('Aviso', 'Por favor seleccione una opcion, en Bono por cliente.', 'warning');
        return false;
    }
	if ( !(parseFloat(monto_cliente) > 0) ) {
		$('#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_div').css('border', '1px solid red');
		$('#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente').focus();
		$('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
		swal('Aviso', 'El bono debe ser mayor a cero.', 'warning');
		return false;
	}
    if ( parseInt(tipo_conversion)!=1 && parseInt(tipo_conversion)!=2 ){
        $("#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_div").css("border", "1px solid red");
        $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
        swal('Aviso', 'Por favor seleccione una opcion, en conversión máxima.', 'warning');
        return false;
    }
	if ( !(parseFloat(conversion_max) > 0) ) {
		$('#modal_tlv_mant_etiq_dinero_at_evento_conversion_max_div').css('border', '1px solid red');
		$('#modal_tlv_mant_etiq_dinero_at_evento_conversion_max').focus();
		$('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
		swal('Aviso', 'El monto de la conversión máxima debe ser mayor a cero.', 'warning');
		return false;
	}
    if ( !(parseInt(clientes_limite) > 0) ) {
        $('#modal_tlv_mant_etiq_dinero_at_evento_clientes_limite_div').css('border', '1px solid red');
		$('#modal_tlv_mant_etiq_dinero_at_evento_clientes_limite').focus();
		$('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
		swal('Aviso', 'Ingrese un límite de clientes válido.', 'warning');
		return false;
    }
    if ( parseInt(clientes_limite) > 99999999999 ) {
        $('#modal_tlv_mant_etiq_dinero_at_evento_clientes_limite_div').css('border', '1px solid red');
		$('#modal_tlv_mant_etiq_dinero_at_evento_clientes_limite').focus();
		$('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
		swal('Aviso', 'El límite de clientes debe ser menor a 1,000,000,000.', 'warning');
		return false;
    }
    if ( !(check_virtuales || check_bingo || check_sportbook) ){
        $('#modal_tlv_mant_etiq_dinero_at_juegos_div').css('border', '1px solid red');
		$('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
		swal('Aviso', 'Debe seleccionar al menos un tipo de juego, para que la promoción tenga validez.', 'warning');
		return false;
    }
    if ( parseInt(tipo_monto)===2 ){
        if( !parseFloat(monto_minimo)>0 ){
            $("#modal_tlv_mant_etiq_dinero_at_evento_monto_cliente_div").css("border", "1px solid red");
            $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
            swal('Aviso', 'Debe incluir un valor mínimo para cuando desea realizar una promoción con porcentaje.', 'warning');
            return false;
        }
    }
   /*  if ( !(parseInt(clientes_limite) > 0) ) {
        $('#modal_tlv_mant_etiq_dinero_at_evento_rollover_div').css('border', '1px solid red');
		$('#modal_tlv_mant_etiq_dinero_at_evento_rollover').focus();
		$('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
		swal('Aviso', 'Ingrese un rollover válido.', 'warning');
		return false;
    } */
    if ( rollover!=='' ) {
        if ( parseInt(rollover) <= 0 || parseInt(rollover) > 30 ) {
            $("#modal_tlv_mant_etiq_dinero_at_evento_rollover_div").css("border", "1px solid red");
            $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
            swal('Aviso', 'El Rollover debe estar en un rango de 1 a 30.', 'warning');
            return false;
        }
    }else {
        $('#modal_tlv_mant_etiq_dinero_at_evento_rollover_div').css('border', '1px solid red');
		$('#modal_tlv_mant_etiq_dinero_at_evento_rollover').focus();
		$('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
		swal('Aviso', 'Ingrese un rollover válido.', 'warning');
		return false;
    }


    var data = new FormData();
    data.append('accion', "guardar_evento_promocional_dinero_at");
    data.append('nombre', nombre);
    data.append('codigo', codigo);
    data.append('descripcion', descripcion);
    data.append('fecha_inicio', fecha_inicio);
    data.append('fecha_fin', fecha_fin);
    data.append('monto_cliente', monto_cliente);
    data.append('conversion_max', conversion_max);
    data.append('clientes_limite', clientes_limite);
    data.append('check_virtuales', check_virtuales);
    data.append('check_bingo', check_bingo);
    data.append('check_sportbook', check_sportbook);
    data.append('tipo_monto', tipo_monto);
    data.append('tipo_conversion', tipo_conversion);
    data.append('monto_minimo', monto_minimo);
    data.append('rollover', rollover);

    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
            var respuesta = JSON.parse(resp);
            auditoria_send({"proceso": "guardar_evento_promocional_dinero_at", "data": respuesta});
            // console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
                $('#modal_tlv_mant_etiq_dinero_at').modal();
                swal('Aviso', respuesta.status, 'warning');
                listar_tabla_eventos_dinero_at();
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#modal_tlv_mant_etiq_dinero_at_btn_enviar').hide();
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                $('#modal_tlv_mant_etiq_dinero_at').show();
                listar_tabla_eventos_dinero_at();
                limpiar_campos_modal_dinero_at();
                return false;
            }
            return false;
        },
        error: function (result) {
            auditoria_send({"proceso": "guardar_evento_promocional_dinero_at_error", "data": result});
            return false;
        }
    });
    return false;
}

function importar_clientes_dinero_at(evento_id, codigo_evento){

    // Limpiar los campos
    $('#dinero_at_titulo').html("");
    $('#modal_tlv_mant_etiq_dinero_at_evento_id').val('');
    $('#modal_tlv_mant_etiq_dinero_at_archivo').val('');


    $('#modal_dinero_at_importar_clientes').modal();
    $('#sec_modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
    $('#dinero_at_titulo').html("Importar clientes para el Evento Promocional de "+codigo_evento);
    $('#modal_tlv_mant_etiq_dinero_at_evento_id').val(evento_id);

    var nombre_archivo = '';
    $("#modal_tlv_mant_etiq_dinero_at_archivo").change(function(){
        nombre_archivo = $("#modal_tlv_mant_etiq_dinero_at_archivo").val();
        if ( nombre_archivo.length > 0 ) {
            swal({
                title: `<h3>¿Estás seguro de importar el archivo?</h3>`,
                text: 'No hay vuelta atrás',
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Si",
                cancelButtonText: "No",
                closeOnConfirm: false,
                closeOnCancel: false,
                html: true,
            },
            function (opt) {
                if (opt) {
                    // swal.close();
                    loading(false);
                    importar_archivo_clientes_dinero_at(codigo_evento);
                    return false;
                } else {
                    swal({
                            title: "Estuvo cerca!",
                            text: "¡Verifique el archivo a importar!",
                            type: "success",
                            timer: 1000,
                            closeOnConfirm: true
                        },
                        function (opt) {
                            $('#modal_tlv_mant_etiq_dinero_at_archivo').val('');
                            swal.close();
                        });
                }
            });
            return false;
        }
    });

}

function datatable_dineroAT_destroy__clientes_evento(id) {
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
        $('#modal_dinero_at_lista_clientes_evento tbody').empty();
    }
}

function datatable_dineroAT__clientes_evento(id) {
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

function listar_clientes_evento_at(evento_id,codigo_evento){
    $('#modal_dinero_at_listar_clientes_evento_titulo').html('Lista de Clientes en la promoción '+codigo_evento);
    $('#modal_dinero_at_lista_clientes_evento tbody').empty();
    var data = {
        "evento_id": evento_id,
        "accion": "listar_clientes_evento_at"
    }
    auditoria_send({ "proceso": "listar_clientes_evento_at", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
                datatable_dineroAT_destroy__clientes_evento('#modal_dinero_at_lista_clientes_evento');
                $('#div_modal_dinero_at_lista_clientes_evento').addClass("col-md-12");
                $('#modal_dinero_at_lista_clientes_evento tbody').append(
                    '<tr>' +
                        '<td class="text-center" colspan="4">'+ respuesta.status +'</td>' +
                    '</tr>'
                );
                // datatable_dineroAT__clientes_evento('#modal_dinero_at_lista_clientes_evento');
                $('#modal_dinero_at_listar_clientes_evento_btn_exportar').removeAttr("onclick");
                $("#modal_dinero_at_listar_clientes_evento_btn_exportar").hide();
                $('#modal_dinero_at_listar_clientes_evento').modal();
                return false;
            }else if(parseInt(respuesta.http_code) == 200) {
                // console.log(respuesta.data);
                datatable_dineroAT_destroy__clientes_evento('#modal_dinero_at_lista_clientes_evento');
                $('#div_modal_dinero_at_lista_clientes_evento').removeClass("col-md-12");
                $.each(respuesta.data, function(index, item) {
                    // parseInt(item.uso) ? item.uso='<i class="fa fa-check text-success"></i>' : item.uso='' ; 
                    $('#modal_dinero_at_lista_clientes_evento tbody').append(
                        '<tr class="sec_tlv_pag_listado_transaccion" style="height:28px">' +
                            '<td class="text-center">' + item.tipo_doc + '</td>' +
                            '<td class="text-center">' + item.num_doc + '</td>' +
                            '<td class="text-center">' + item.nombre_completo + '</td>' +
                            '<td class="text-center">' + item.uso + '</td>' +
                        '</tr>'
                    );
                });
                $('#modal_dinero_at_listar_clientes_evento_btn_exportar').removeAttr("onclick");
                $('#modal_dinero_at_listar_clientes_evento_btn_exportar').attr("onclick", 'exportar_clientes_evento_dineroAT('+evento_id+')');
                $("#modal_dinero_at_listar_clientes_evento_btn_exportar").show();
                datatable_dineroAT__clientes_evento('#modal_dinero_at_lista_clientes_evento');
                $('#modal_dinero_at_listar_clientes_evento').modal();
                return false;
            }
            return false;
        },
        error: function() {}
    });
}

function exportar_clientes_evento_dineroAT(evento_id){

    var evento_id = parseInt(evento_id);

    if ( evento_id <= 0 ) {
        swal('Aviso', 'No se puede exportar, por favor comuníquese con el área de Sistemas.', 'warning');
        return false;
    }

    var data = {
        "accion": "dineroAT_exportar_clientes_registrados",
        "evento_id": evento_id
    }

    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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


function importar_archivo_clientes_dinero_at(codigo_evento){

    var evento_id = $('#modal_tlv_mant_etiq_dinero_at_evento_id').val();
    var archivo   = $("#modal_tlv_mant_etiq_dinero_at_archivo").val();
	var f_archivo = $("#modal_tlv_mant_etiq_dinero_at_archivo")[0].files[0];
	var archivo_extension = archivo.substring(archivo.lastIndexOf("."));
	// var archivo_extension = $("#modal_tlv_mant_etiq_dinero_at_archivo").val().split('.').pop();
	// console.log(archivo);
	// console.log(archivo_extension);

	if (!(archivo.length > 0)) {
		swal('Aviso', 'Agregue un archivo.', 'warning');
		$('#modal_dinero_at_importar_clientes').show();
		return false;
	}
	if (archivo_extension !== ".csv" && archivo_extension !== ".CSV" &&
        archivo_extension !== ".xls" && archivo_extension !== ".XLS" &&
        archivo_extension !== ".ods" && archivo_extension !== ".ODS" &&
        archivo_extension !== ".xlsx" && archivo_extension !== ".XLSX") {
		swal('Aviso', 'El archivo debe ser XLSX, XLS, CSV ó ODS.', 'warning');
		$('#modal_dinero_at_importar_clientes').show();
		return false;
	}

	var data = new FormData( $('#modal_tlv_mant_etiq_dinero_at_form')[0] );
    data.append('accion', "importar_archivo_dinero_at");
	data.append('archivo', f_archivo);
	data.append('evento_id', evento_id);

    // data.delete('archivo');
    // auditoria_send({"proceso": "importar_archivo_dinero_at_SEND", "data": data});

	$.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
            data.delete('archivo');
            auditoria_send({"proceso": "importar_archivo_dinero_at_SEND", "data": (Object.fromEntries(data.entries()))});

            var respuesta = JSON.parse(resp);
            auditoria_send({"proceso": "importar_archivo_dinero_at", "data": respuesta});
            console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                $('#sec_modal_tlv_mant_etiq_dinero_at_btn_enviar').show();
                $('#modal_dinero_at_importar_clientes').modal();
                $('#modal_tlv_mant_etiq_dinero_at_archivo').val('');
                swal('Aviso', respuesta.status, 'warning');
                listar_tabla_eventos_dinero_at();
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#sec_modal_tlv_mant_etiq_dinero_at_btn_enviar').hide();
                $('#modal_dinero_at_importar_clientes').modal('hide');
                
                var array = respuesta.clientes_no_registrados;
                var texto_no_registrados = ";"
                if ( parseInt(respuesta.cont_clientes_no_importados) > 0 ) {
                    var jsonString = JSON.stringify(array);
                    var array2 = JSON.parse(jsonString);
                    texto_no_registrados = "Para su revisión: Se exporta la data de clientes no registrados.";
                    dineroAT_export_excel_clientes_no_registrados(array2);
                }

                swal({
                    title: "Importación exitosa",
                    text: ` Cantidad de registrados: ${respuesta.cont_clientes_importados} 
                            No registrados: ${respuesta.cont_clientes_no_importados}
                            ${texto_no_registrados} `,
                    type: "success",
                    closeOnConfirm: true,
                    showCancelButton: false,
                },
                    function (isConfirm) {
                        listar_clientes_evento_at(evento_id,codigo_evento);
                    }
                );
                listar_tabla_eventos_dinero_at();
                return false;
            }
            return false;
        },
        error: function (result) {
            auditoria_send({"proceso": "importar_archivo_dinero_at_ERROR", "data": result});
            return false;
        }
    });
    return false;
}

function dineroAT_export_excel_clientes_no_registrados(array_data) {
    const wb = XLSX.utils.book_new();
    const wsName = "Sheet1";

    // Convertir los datos a formato Excel
    const wsData = [["Numero de Documento", "Motivo"]];
    array_data.forEach((item) => {
        wsData.push([`${item.nro_doc}`, item.motivo]);
    });

    const ws = XLSX.utils.aoa_to_sheet(wsData);
    XLSX.utils.book_append_sheet(wb, ws, wsName);

    // Generar el archivo Excel y descargarlo
    const wbOptions = { bookType: "xlsx", type: "array" };
    const wbout = XLSX.write(wb, wbOptions);
    const fileName = "Clientes_no_registrados.xlsx";

    const blob = new Blob([wbout], { type: "application/octet-stream" });
    if (navigator.msSaveBlob) {
        // Para IE 10 y versiones posteriores
        navigator.msSaveBlob(blob, fileName);
    } else {
        // Crear un enlace y simular un clic para descargar el archivo
        const link = document.createElement("a");
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", fileName);
            link.style.visibility = "hidden";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
}

function limpiar_campos_modal_editar_dineroAT(){
    $('#modal_dinero_at_editar_codigo').html('');
    $('#modal_dinero_at_editar_nombre').val('');
    $('#modal_dinero_at_editar_descripcion').val('');
    $('#modal_dinero_at_editar_fecha_inicio').val('');
    $('#modal_dinero_at_editar_fecha_fin').val('');
    $('#modal_dinero_at_editar_clientes_limite').val('');
    $('#modal_dinero_at_editar_btn').removeAttr("onclick");
	$('#modal_dinero_at_editar_btn').show();
}

function modal_editar_evento_dineroAT(evento_id, codigo_evento){

    if ( !(parseInt(evento_id) > 0) ) {
        swal('Aviso', 'Se está enviando un código de evento erróneo.', 'warning');
        return false;
    }

    limpiar_campos_modal_editar_dineroAT();
    $('#modal_dinero_at_editar_codigo').html(codigo_evento);

    var data = {
        "evento_id": parseFloat(evento_id),
        "accion": "evento_dineroAT_mostrar"
    }
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
            auditoria_send({ "proceso": "evento_dineroAT_mostrar", "data": respuesta });
            // console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }else if(parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.data, function(index, item) {
                    $('#modal_dinero_at_editar_nombre').val(item.nombre);
                    $('#modal_dinero_at_editar_descripcion').val(item.descripcion);
                    $('#modal_dinero_at_editar_fecha_inicio').val(item.fecha_inicio);
                    $('#modal_dinero_at_editar_fecha_fin').val(item.fecha_fin);
                    $('#modal_dinero_at_editar_clientes_limite').val(item.limite_clientes);
                    $('#modal_dinero_at_editar_btn').attr("onclick", 'modal_dinero_at_editar_btn_guardar('+item.id_evento+',"'+item.fecha_inicio+'")');
                });
                $('#modal_dinero_at_editar').modal();
                return false;
            }
            return false;
        },
        error: function(result) {
            auditoria_send({"proceso": "evento_dineroAT_mostrar_ERROR", "data": result});
			return false;
        }
    });
}

function modal_dinero_at_editar_btn_guardar(evento_id){
    // Limpiar borders, ocultar botón de registrar
    $('#modal_dinero_at_editar_nombre_div').css('border', '');
	$('#modal_dinero_at_editar_descripcion_div').css('border', '');
	$('#modal_dinero_at_editar_fecha_inicio_div').css('border', '');
	$('#modal_dinero_at_editar_fecha_fin_div').css('border', '');
	$('#modal_dinero_at_editar_clientes_limite_div').css('border', '');
	$('#modal_dinero_at_editar_btn').hide();

    var fecha = new Date();
    var anio = fecha.getFullYear();
    var mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
    var dia = ('0' + fecha.getDate()).slice(-2);
    var fecha_actual = anio + '-' + mes + '-' + dia;

    var nombre          = $.trim($('#modal_dinero_at_editar_nombre').val());
    var descripcion     = $.trim($('textarea#modal_dinero_at_editar_descripcion').val());
    var fecha_inicio    = $("#modal_dinero_at_editar_fecha_inicio").val();
    var fecha_fin       = $("#modal_dinero_at_editar_fecha_fin").val();
    var clientes_limite = $('#modal_dinero_at_editar_clientes_limite').val();

    if( $.trim(nombre) === '' ){
        $('#modal_dinero_at_editar_nombre_div').css('border', '1px solid red');
        $('#modal_dinero_at_editar_nombre').focus();
        $('#modal_dinero_at_editar_btn').show();
		swal({
			title: "Ingrese un nombre.",
			text: "Debe ingresar, un nombre válido para el evento promocional.",
			type: 'info',
			timer: 3500,
			showConfirmButton: true
		});
        return false;
    }
    if (fecha_inicio.length !== 10) {
        $("#modal_dinero_at_editar_fecha_inicio_div").css("border", "1px solid red");
        $('#modal_dinero_at_editar_btn').show();
        swal('Aviso', 'Seleccione una fecha inicio válida.', 'warning');
        return false;
    }
    if (fecha_fin.length !== 10) {
        $("#modal_dinero_at_editar_fecha_fin_div").css("border", "1px solid red");
        $('#modal_dinero_at_editar_btn').show();
        swal('Aviso', 'Seleccione una fecha fin válida.', 'warning');
        return false;
    }
    if (parseInt(fecha_actual.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        $("#modal_dinero_at_editar_fecha_fin_div").css("border", "1px solid red");
        $('#modal_dinero_at_editar_btn').show();
        swal('Aviso', 'La fecha fin de la promoción a modificar, no puede ser menor a hoy.', 'warning');
        return false;
    }
    if (parseInt(fecha_inicio.replace(/-/g, "")) > parseInt(fecha_fin.replace(/-/g, ""))) {
        $("#modal_dinero_at_editar_fecha_fin_div").css("border", "1px solid red");
        $('#modal_dinero_at_editar_btn').show();
        swal('Aviso', 'La fecha fin del evento debe ser mayor o igual a la fecha de inicio del evento y la fecha de inicio en este evento es: '+fecha_inicio, 'warning');
        return false;
    }
    if ( !(parseInt(clientes_limite) > 0) ) {
        $('#modal_dinero_at_editar_clientes_limite_div').css('border', '1px solid red');
		$('#modal_dinero_at_editar_clientes_limite').focus();
		$('#modal_dinero_at_editar_btn').show();
		swal('Aviso', 'Ingrese un límite de clientes válido.', 'warning');
		return false;
    }

    var data = new FormData();
    data.append('accion', "guardar_cambios_evento_dineroAT");
    data.append('evento_id', parseInt(evento_id));
    data.append('nombre', nombre);
    data.append('descripcion', descripcion);
    data.append('fecha_inicio', fecha_inicio);
    data.append('fecha_fin', fecha_fin);
    data.append('clientes_limite', clientes_limite);

    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
            var respuesta = JSON.parse(resp);
            // console.log(respuesta);
            auditoria_send({"proceso": "guardar_cambios_evento_dineroAT", "data": respuesta});
            if (parseInt(respuesta.http_code) == 400) {
                $('#modal_dinero_at_editar_btn').show();
                $('#modal_dinero_at_editar').modal();
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#modal_dinero_at_editar').modal('hide');
                listar_tabla_eventos_dinero_at();
                swal('Aviso', respuesta.status, 'success');
                return false;
            }
            return false;
        },
        error: function (result) {
            auditoria_send({"proceso": "guardar_cambios_evento_dineroAT_ERROR", "data": result});
            return false;
        }
    });
    return false;
}

function eliminar_evento_dinero_at(evento_id, cod_evento){
    swal({
        title: `<h3>¿Estás seguro de eliminar el evento promocional "`+ cod_evento +`"?</h3>`,
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
            data.append('accion', "eliminar_evento_dinero_at");
            data.append('evento_id', evento_id);
        
            auditoria_send({"proceso": "eliminar_evento_dinero_at_SEND", "data": (Object.fromEntries(data.entries()))});

            $.ajax({
                url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
                    var respuesta = JSON.parse(resp);
                    auditoria_send({"respuesta": "eliminar_evento_dinero_at", "data": respuesta});
                    // console.log(respuesta);
                    if (parseInt(respuesta.http_code) == 400) {
                        swal('Aviso', respuesta.status, 'warning');
                        $('#modal_tlv_mant_etiq_dinero_at').modal();
                        listar_tabla_eventos_dinero_at();
                        return false;
                    }
                    if (parseInt(respuesta.http_code) == 200) {
                        swal('Aviso', 'El evento promocional fue eliminado con éxito.', 'success');
                        $('#modal_tlv_mant_etiq_dinero_at').modal();
                        listar_tabla_eventos_dinero_at();
                        return false;
                    }
                    return false;
                },
                error: function (result) {
                    auditoria_send({"respuesta": "eliminar_evento_dinero_at_ERROR", "data": result});
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
        text: "El evento promocional está a salvo!",
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

function modal_etiqueta_limpiar_campos(){
    $('#modal_etiqueta_i_etiqueta').val('');
    $('textarea#modal_etiqueta_txa_observacion').val('');
    $('#modal_etiqueta_i_color').val('#0000ff');
    $('#modal_etiqueta_div_pintar').css('background', '#0000ff');
    $('#sec_eti_modal_etiqueta_agregar_div_tipo').css('border', '');
    $('#sec_eti_modal_etiqueta_tipo').val(1).trigger('change');
}

function modal_comision_limpiar_campos(){
    $('#modal_comision_i_comision').val('');
}

function modal_motivos_limpiar_campos(){
    $('#modal_motivos_nuevo').val('');
    $('#modal_motivos_tipo').val(0).trigger('change');
}

function modal_comision_editar_limpiar_campos(){
    $('#modal_comision_i_comision_edit').val('');
}

function modal_motivos_editar_limpiar_campos(){
    $('#modal_motivo_nuevo_editar').val('');
    $('#modal_motivos_tipo_editar').val(0);
    $('#modal_motivo_estado_editar').val('');
}

function modal_etiqueta_limpiar_bordes(){
    $('#modal_etiqueta_i_etiqueta').css('border', '');
    $('#modal_etiqueta_txa_observacion').css('border', '');
    $('#modal_etiqueta_i_color').css('border', '');
}

function modal_comision_limpiar_bordes(){
    $('#modal_comision_i_comision').css('border', '');
}

function modal_motivos_limpiar_bordes(){
    $('#modal_motivos_nuevo_div').css('border', '');
    $('#modal_motivos_tipo_div').css('border', '');
}

function modal_comision_editar_limpiar_bordes(){
    $('#modal_comision_i_comision_edit').css('border', '');
}

function modal_motivo_editar_limpiar_bordes(){
    $('#modal_motivo_nuevo_editar_div').css('border', '');
    $('#modal_motivos_tipo_editar_div').css('border', '');
}

function obtener_etiqueta(id, label, description, color, tipo){
    modal_etiqueta_limpiar_campos();
    modal_etiqueta_limpiar_bordes();

    $('#modal_etiqueta_i_etiqueta').val(label);
    $('textarea#modal_etiqueta_txa_observacion').val(description);
    $('#modal_etiqueta_i_color').val(color);
    $('#modal_etiqueta_div_pintar').css('background', color);
    $('#sec_eti_modal_etiqueta_tipo').val(tipo).trigger('change');

    $('#modal_etiqueta_btn_guardar').show();
    $('#modal_etiqueta_btn_guardar').removeAttr("onclick");
    $('#modal_etiqueta_btn_guardar').attr("onclick", 'mante_guardar_etiqueta('+id+')');
    $('#modal_etiqueta').modal();

    return false;
}

function obtener_comision(id, comision_monto, estado){
    $('#modal_comision').modal('hide')
    modal_comision_editar_limpiar_campos();
    modal_comision_editar_limpiar_bordes();

    $('#modal_comision_editar_titulo').html('Editar comisión de ' + comision_monto + ' a:');
    $('#modal_comision_i_comision_edit').val(comision_monto);
    $('#modal_comision_i_estado_edit').val(estado);

    $('#modal_comision_btn_editar_comision').show();
    $('#modal_comision_btn_editar_comision').removeAttr("onclick");
    $('#modal_comision_btn_editar_comision').attr("onclick", 'mante_guardar_comision('+id+')');
    $('#modal_comision_editar').modal();

    setTimeout(function() {
        $('#modal_comision_i_comision_edit').focus();
    }, 500);

    return false;
}

function obtener_motivos(id, motivo, tipo_motivo, estado){
    $('#modal_motivos').modal('hide')
    modal_motivos_editar_limpiar_campos();
    modal_motivo_editar_limpiar_bordes();

    $('#modal_motivo_editar_titulo').html('Editar motivo de ' + motivo + ' a:');
    $('#modal_motivo_nuevo_editar').val(motivo);
    $('#modal_motivos_tipo_editar').val(tipo_motivo);
    $('#modal_motivo_estado_editar').val(estado);

    $('#modal_motivo_editar_btn').show();
    $('#modal_motivo_editar_btn').removeAttr("onclick");
    $('#modal_motivo_editar_btn').attr("onclick", 'mante_guardar_motivo('+id+')');
    $('#modal_motivo_editar').modal();

    setTimeout(function() {
        $('#modal_motivo_nuevo_editar').focus();
    }, 500);

    return false;
}

function mante_guardar_etiqueta(cod_etiqueta) {
    console.log('mante_guardar_etiqueta');
    $('#modal_etiqueta_btn_guardar').hide();

    modal_etiqueta_limpiar_bordes();

    var i_etiqueta = $('#modal_etiqueta_i_etiqueta').val();
    var txa_observacion = $('textarea#modal_etiqueta_txa_observacion').val();
    var i_color = $('#modal_etiqueta_i_color').val();
    var tipo = $('#sec_eti_modal_etiqueta_tipo').val();
    if (!(i_etiqueta.length > 0)) {
        $('#modal_etiqueta_i_etiqueta').css('border', '1px solid red');
        $('#modal_etiqueta_i_etiqueta').focus();
        $('#modal_etiqueta_btn_guardar').show();
        return false;
    }
    if (!(txa_observacion.length > 0)) {
        $('#modal_etiqueta_txa_observacion').css('border', '1px solid red');
        $('#modal_etiqueta_txa_observacion').focus();
        $('#modal_etiqueta_btn_guardar').show();
        return false;
    }
    if (!(i_color.length > 0)) {
        $('#modal_etiqueta_i_color').css('border', '1px solid red');
        $('#modal_etiqueta_i_color').focus();
        $('#modal_etiqueta_btn_guardar').show();
        return false;
    }
    if(tipo == 0){
        $('#sec_eti_modal_etiqueta_agregar_div_tipo').css('border', '1px solid red');
        $('#sec_eti_modal_etiqueta_agregar_div_tipo').focus();
        $('#modal_etiqueta_btn_guardar').show();
        return false;
    }
    var data = {
        "accion": "guardar_etiqueta",
        "cod_etiqueta": cod_etiqueta,
        "i_etiqueta": i_etiqueta,
        "txa_observacion": txa_observacion,
        "i_color": i_color,
        "tipo" : tipo
    }
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
            auditoria_send({ "respuesta": "mante_guardar_etiqueta", "data": resp });
            if (parseInt(respuesta.http_code) == 400) {
                $('#modal_etiqueta').modal('hide');
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#modal_etiqueta').modal('hide');
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                listar_SecManEtiTlv_tabla_principal();
                return false;
            }
            return false;
        },
        error: function(result) {
            $('#modal_etiqueta').modal('hide');
            swal('Aviso', result, 'warning');
            auditoria_send({ "proceso": "mante_guardar_etiqueta_error", "data": result });
            return false;
        }
    });
    return false;
}

function mante_guardar_comision(cod_comision) {
    console.log('mante_guardar_comision');

    if (parseInt(cod_comision) == 0) {
        $('#modal_comision_btn_guardar').hide();

        modal_comision_limpiar_bordes();

        var i_comision = $('#modal_comision_i_comision').val();

        if (!(i_comision.length > 0)) {
            $('#modal_comision_i_comision').css('border', '1px solid red');
            $('#modal_comision_i_comision').focus();
            $('#modal_comision_btn_guardar').show();
            return false;
        }

        $('#modal_comision_btn_guardar').show();

        var data = {
            "accion": "guardar_comision",
            "cod_comision": cod_comision,
            "i_comision": i_comision
        }
    } else {
        $('#modal_comision_btn_editar_comision').hide();

        modal_comision_editar_limpiar_bordes();

        var i_comision = $('#modal_comision_i_comision_edit').val();
        var i_estado = $('#modal_comision_i_estado_edit').val();

        if (!(i_comision.length > 0)) {
            $('#modal_comision_i_comision_edit').css('border', '1px solid red');
            $('#modal_comision_i_comision_edit').focus();
            $('#modal_comision_btn_editar_comision').show();
            return false;
        }

        $('#modal_comision_btn_editar_comision').show();

        var data = {
            "accion": "guardar_comision",
            "cod_comision": cod_comision,
            "i_comision": i_comision,
            "i_estado": i_estado

        }
    }
    

    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
            auditoria_send({ "respuesta": "mante_guardar_comision", "data": resp });
            $('#modal_comision_btn_guardar').show();
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                modal_comision_limpiar_campos();
                listar_SecManComisionTlv_tabla_principal();
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                if (parseInt(cod_comision) != 0) {
                    $('#modal_comision').modal()
                    $('#modal_comision_editar').modal('hide');
                }
                return false;
            }
            return false;
        },
        error: function(result) {
            $('#modal_comision_btn_guardar').show();
            $('#modal_etiqueta').modal('hide');
            swal('Aviso', result, 'warning');
            auditoria_send({ "proceso": "mante_guardar_comision_error", "data": result });
            return false;
        }
    });
    return false;
}

function mante_guardar_motivo(cod_motivo) {
    console.log('mante_guardar_motivo');

    if (parseInt(cod_motivo) == 0) {
        $('#modal_motivos_btn_guardar').hide();

        modal_motivos_limpiar_bordes();

        var motivo = $('#modal_motivos_nuevo').val().trim();
        var tipo = $('#modal_motivos_tipo').val();

        if (!(motivo.length > 0)) {
            $('#modal_motivos_nuevo_div').css('border', '1px solid red');
            $('#modal_motivos_nuevo_div').focus();
            $('#modal_motivos_btn_guardar').show();
            return false;
        }
        
        if (!(tipo > 0)) {
            $('#modal_motivos_tipo_div').css('border', '1px solid red');
            $('#modal_motivos_tipo_div').focus();
            $('#modal_motivos_btn_guardar').show();
            return false;
        }

        $('#modal_motivos_btn_guardar').show();

        var data = {
            "accion": "guardar_motivo",
            "cod_motivo": cod_motivo,
            "motivo": motivo,
            "tipo": tipo
        }
    } else {
        $('#modal_motivo_editar_btn').hide();

        modal_motivo_editar_limpiar_bordes();

        var motivo = $('#modal_motivo_nuevo_editar').val().trim();
        var tipo = $('#modal_motivos_tipo_editar').val();
        var estado = $('#modal_motivo_estado_editar').val();

        if (!(motivo.length > 0)) {
            $('#modal_motivo_nuevo_editar_div').css('border', '1px solid red');
            $('#modal_motivo_nuevo_editar_div').focus();
            $('#modal_motivo_editar_btn').show();
            return false;
        }

        if (!(tipo > 0)) {
            $('#modal_motivos_tipo_editar_div').css('border', '1px solid red');
            $('#modal_motivos_tipo_editar_div').focus();
            $('#modal_motivo_editar_btn').show();
            return false;
        }

        $('#modal_motivo_editar_btn').show();

        var data = {
            "accion": "guardar_motivo",
            "cod_motivo": cod_motivo,
            "motivo": motivo,
            "tipo": tipo,
            "estado": estado

        }
    }
    

    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
            auditoria_send({ "respuesta": "mante_guardar_motivo", "data": resp });
            $('#modal_motivos_btn_guardar').show();
            if (parseInt(respuesta.http_code) == 400) {
                swal('Aviso', respuesta.status, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                modal_motivos_limpiar_campos();
                listar_SecManMotivosTlv_tabla_principal();
                swal('Aviso', 'Acción realizada con éxito.', 'success');
                if (parseInt(cod_comision) != 0) {
                    $('#modal_comision').modal()
                    $('#modal_comision_editar').modal('hide');
                }
                return false;
            }
            return false;
        },
        error: function(result) {
            $('#modal_motivos_btn_guardar').show();
            $('#modal_etiqueta').modal('hide');
            swal('Aviso', result, 'warning');
            auditoria_send({ "proceso": "mante_guardar_motivo_error", "data": result });
            return false;
        }
    });
    return false;
}

function mante_eliminar_etiqueta(id){

    swal({
        title: '¿Está seguro de eliminar esta etiqueta?',
        text: '',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'No, cancelar',
        closeOnConfirm: false,
        closeOnCancel: true
    }, function(isConfirm){
        if (isConfirm){
            var data = {
                "accion": "eliminar_etiqueta",
                "cod_etiqueta": id
            }
            $.ajax({
                url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
                    auditoria_send({ "respuesta": "mante_eliminar_etiqueta", "data": resp });
                    if (parseInt(respuesta.http_code) == 400) {
                        swal('Aviso', respuesta.status, 'warning');
                        return false;
                    }
                    if (parseInt(respuesta.http_code) == 200) {
                        swal('Aviso', 'Acción realizada con éxito.', 'success');
                        listar_SecManEtiTlv_tabla_principal();
                        return false;
                    }
                    return false;
                },
                error: function(result) {
                    swal('Aviso', result, 'warning');
                    auditoria_send({ "proceso": "mante_eliminar_etiqueta_error", "data": result });
                    return false;
                }
            });
            return false;
        }
    });

}

function exportar_ej_formato_importar_dinero_at(){

}

// Exportar Formato CSV, de ejemplo
$(document).ready(function() {
    $('#btnExport_formatoClientesDineroAT').click(function(event) {
        event.preventDefault(); // Prevenir comportamiento predeterminado del navegador
        window.open('export_formato_dinero_at.php', '_blank');
        // exportTableToCSV();
    });

    function exportTableToCSV() {
      var csv = [];
      var rows = $('#tablaFormatoDineroAT').find('tr');
      $.each(rows, function(rowIndex, row) {
        var rowData = [];
        var cells = $(row).find('td,th');
        $.each(cells, function(cellIndex, cell) {
          rowData.push($(cell).text());
        });
        csv.push(rowData.join(','));
      });
      var csvData = csv.join('\n');
      var blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
      saveAs(blob, 'clientes_dineroAT.csv');
    }
});
      







function DATATABLE_FORMATO_SecManEtiTlv_tabla_principal(id) {
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
    }
    $(id).DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'order': [[ 0, "asc" ]],
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


function sec_mant_buscar_ult_limite(){
    var data = {
        "accion": "obtener_limites_reg"
    }

    auditoria_send({ "proceso": "obtener_limites_reg", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
            $('#editar_valor_actual_lc').html('');
            $('#editar_valor_actual_aten').html('');
            $('#editar_valor_actual_tercero').html('');

            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                console.log(respuesta.result);
                respuesta.result.forEach((elemento) => {
					console.log(elemento.limite);
                    $('#editar_valor_actual_lc').append(
                      ''  + elemento.limite +''
                        );

                    $('#editar_valor_actual_aten').append(
                            ''  + elemento.max_aten +''
                    );

                    $('#editar_valor_actual_tercero').append(
                        ''  + elemento.limite_terc +''
                    );
				});
                
           
                return false;
            }       
        },
        error: function() {}
    });
}

///// ETIQUETAS MASIVAS

function sec_mant_agregar_cliente_temp(gen_etiq_seleccionado, gen_etiq_cli_seleccionado){
    
    var etiqueta = $('#SecManEtiTlv_modal_etiq').val()!= '' ? gen_etiq_seleccionado : '0'; 
    var cliente = $('#SecManEtiTlv_modal_etiq_cli').val()!= '' ? gen_etiq_cli_seleccionado : '0'; 

    if(etiqueta == 0){
        swal('Aviso', 'Debe seleccionar una etiqueta.', 'warning');
        return false;
    }   

    if(cliente == 0){
        swal('Aviso', 'Debe seleccionar un cliente.', 'warning');
        return false;
    }  

    var data = {
        "accion": "guardar_temp_clientes_etiquetas",     
        "etiqueta": etiqueta,
        "cliente": cliente

    }
    auditoria_send({ "proceso": "guardar_temp_clientes_etiquetas", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
                //localStorage.removeItem('list_temp_cli');
                //console.log(respuesta.result);
                let array_temp_cli =[];
                let cli_val = "";

                if(localStorage.getItem('list_temp_cli') === null){
                    array_temp_cli = [{cliente_id: cliente, cliente: respuesta.result[0].cliente, etiqueta_id: etiqueta, etiqueta: respuesta.result[0].label}];
                     
                }else{
                    array_temp_cli = localStorage.getItem('list_temp_cli');
                    array_temp_cli = JSON.parse(array_temp_cli);
                    
                    i=0;
                    cli_val = 0;

				    $.each(array_temp_cli, function(index, item) {
                        i ++;
                        if(parseInt(item.cliente_id)===parseInt(cliente) && parseInt(item.etiqueta_id)===parseInt(etiqueta)){
                            cli_val = 1;
                            swal('Aviso', 'El cliente ya esta en el listado para etiquetado', 'warning');
                            return false;
                        }else{
                            cli_val = 0;
                        }
				    });	
                  
                    if(cli_val==0){
                        array_temp_cli.push({cliente_id: cliente, cliente: respuesta.result[0].cliente, etiqueta_id: etiqueta, etiqueta: respuesta.result[0].label});
                    }
                }
                
                localStorage.setItem('list_temp_cli', JSON.stringify(array_temp_cli));
                
                let list_temp_cli_e =[];
                list_temp_cli_e = localStorage.getItem('list_temp_cli');
                list_temp_cli_e = JSON.parse(list_temp_cli_e);

				if ($.fn.dataTable.isDataTable('#SecManEtiTlv_modal_list_etiq_cli')) {
					$('#SecManEtiTlv_modal_list_etiq_cli').DataTable().destroy();
				}
                $('#SecManEtiTlv_modal_etiq_cli').val('');
                $('#modal_etiqueta_masivas_btn_guardar').removeAttr('disabled');						 
				$('#SecManEtiTlv_modal_list_etiq_cli_tbody').html('');			
				i=0;
                j=0;
				$.each(list_temp_cli_e, function(index, item) {
                    j ++;
					$('#SecManEtiTlv_modal_list_etiq_cli_tbody').append(
						'<tr>'+
							'<td style="text-align: center;">' +  j + '</td>'+
							'<td style="text-align: center;">' + item.cliente + '</td>'+
							'<td style="text-align: center;">' + item.etiqueta + '</td>'+
							'<td style="text-align: center;"><button type="button" class="btn btn-danger" onclick="sec_mant_eliminar_cliente_temp(' + i + ')">' +
							'<span class="fa fa-trash"></span>' +
							'</button></td>'+
						'</tr>'
					);

                    i ++;
				});			 
				DATATABLE_FORMATO_SecManEtiTlv_tabla_principal('#SecManEtiTlv_modal_list_etiq_cli');
				return false;
			}else{
                swal('Aviso', respuesta.status, 'warning');
				return false;
			}     
        },
        error: function (result) {
			auditoria_send({"proceso": "guardar_temp_clientes_etiquetas_error", "data": result});
		}
    });
}

function sec_mant_eliminar_cliente_temp(cliente){
 
    let array_temp_cli =[];
    array_temp_cli = localStorage.getItem('list_temp_cli');
    array_temp_cli = JSON.parse(array_temp_cli);
    array_temp_cli.splice(cliente, 1);

    localStorage.setItem('list_temp_cli', JSON.stringify(array_temp_cli));

    let list_temp_cli_e =[];
    list_temp_cli_e = localStorage.getItem('list_temp_cli');
    list_temp_cli_e = JSON.parse(list_temp_cli_e);
 
    if(list_temp_cli_e.length > 0){

        if ($.fn.dataTable.isDataTable('#SecManEtiTlv_modal_list_etiq_cli')) {
            $('#SecManEtiTlv_modal_list_etiq_cli').DataTable().destroy();
        }
        $('#SecManEtiTlv_modal_etiq_cli').val('');
        $('#modal_etiqueta_masivas_btn_guardar').removeAttr('disabled');		 
        $('#SecManEtiTlv_modal_list_etiq_cli_tbody').html('');	

        i=0;
        j=0;
		$.each(array_temp_cli, function(index, item) {
		    j ++;
			$('#SecManEtiTlv_modal_list_etiq_cli_tbody').append(
				'<tr>'+
				'<td style="text-align: center;">' +  j + '</td>'+
				'<td style="text-align: center;">' + item.cliente + '</td>'+
				'<td style="text-align: center;">' + item.etiqueta + '</td>'+
				'<td style="text-align: center;"><button type="button" class="btn btn-danger" onclick="sec_mant_eliminar_cliente_temp(' + i + ')">' +
				'<span class="fa fa-trash"></span>' +
				'</button></td>'+
				'</tr>'
			);
            i ++;
		});			 
		DATATABLE_FORMATO_SecManEtiTlv_tabla_principal('#SecManEtiTlv_modal_list_etiq_cli');
        $('#SecManEtiTlv_modal_etiq_cli').val('');
        $('#modal_etiqueta_masivas_btn_guardar').removeAttr('disabled');
	    return false;

    }else{
        localStorage.removeItem('list_temp_cli');
        console.log(localStorage);
        swal('Aviso', 'Sin registros', 'warning');	 
        $('#SecManEtiTlv_modal_list_etiq_cli tbody').html('');
        $('#SecManEtiTlv_modal_list_etiq_cli tbody').append(
            '<tr>' +
            '   <td colspan="4">No hay registros</td>' +
            '</tr>'
            );
            return false;
    }
}

function sec_mant_buscar_etiquetas(){
    
    let array_temp_cli =[];

    if(localStorage.getItem('list_temp_cli') === null){
        $('#modal_etiqueta_masivas_btn_guardar').attr('disabled', 'disabled');		 
        $('#SecManEtiTlv_modal_list_etiq_cli tbody').html('');

        $('#SecManEtiTlv_modal_list_etiq_cli tbody').append(
            '<tr>' +
            '   <td colspan="4">No hay registros</td>' +
            '</tr>'
            );
            return false;
    }else{
        array_temp_cli = localStorage.getItem('list_temp_cli');
        array_temp_cli = JSON.parse(array_temp_cli);

        if ($.fn.dataTable.isDataTable('#SecManEtiTlv_modal_list_etiq_cli')) {
            $('#SecManEtiTlv_modal_list_etiq_cli').DataTable().destroy();
        }
        $('#SecManEtiTlv_modal_etiq_cli').val('');
        $('#modal_etiqueta_masivas_btn_guardar').removeAttr('disabled');		 
        $('#SecManEtiTlv_modal_list_etiq_cli_tbody').html('');			
        i=0;
        j=0;
        $.each(array_temp_cli, function(index, item) {
            j ++;
            $('#SecManEtiTlv_modal_list_etiq_cli_tbody').append(
                '<tr>'+
                    '<td style="text-align: center;">' +  j + '</td>'+
                    '<td style="text-align: center;">' + item.cliente + '</td>'+
                    '<td style="text-align: center;">' + item.etiqueta + '</td>'+
                    '<td style="text-align: center;"><button type="button" class="btn btn-danger" onclick="sec_mant_eliminar_cliente_temp(' + i + ')">' +
                    '<span class="fa fa-trash"></span>' +
                    '</button></td>'+
                '</tr>'
            );

            i ++;
        });			 
        DATATABLE_FORMATO_SecManEtiTlv_tabla_principal('#SecManEtiTlv_modal_list_etiq_cli');
        return false;
        
    }
}

function sec_mant_guardar_etiquetas_masivas(){

    let array_temp_cli =[];

    if(localStorage.getItem('list_temp_cli') === null){
        $('#SecManEtiTlv_modal_etiq_cli').val('');
        $('#modal_etiqueta_masivas_btn_guardar').attr('disabled', 'disabled');
        swal('Aviso', 'No hay registros', 'warning');
        return false;
    }else{
        array_temp_cli = localStorage.getItem('list_temp_cli');
        array_temp_cli = JSON.parse(array_temp_cli);

        var data = {
            "accion": "sec_mant_guardar_etiquetas_masivas",
            "array_temp_cli": array_temp_cli
        }

        auditoria_send({ "proceso": "sec_mant_guardar_etiquetas_masivas", "data": data });
        $.ajax({
            url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
                    $('#SecManEtiTlv_modal_etiq_cli').val('');
                    swal('Aviso', respuesta.status, 'success');
                    localStorage.removeItem('list_temp_cli');
                    if ($.fn.dataTable.isDataTable('#SecManEtiTlv_modal_list_etiq_cli')) {
                        $('#SecManEtiTlv_modal_list_etiq_cli').DataTable().destroy();
                    }
                    $('#SecManEtiTlv_modal_list_etiq_cli tbody').html('');
                    $('#SecManEtiTlv_modal_list_etiq_cli tbody').append(
                        '<tr>' +
                        '   <td colspan="4">No hay registros</td>' +
                        '</tr>'
                        );
                        
                    return false;
                }   
            },
            error: function (result) {
                auditoria_send({"proceso": "sec_mant_guardar_etiquetas_masivas_error", "data": result});
            }
        });
    }
}


///// FIN ETIQUETAS MASIVAS

function sec_mant_buscar_comprobante_de_pago_sin_notificar() {
	var data = {
		"accion": "obtener_paremetros_comprobante_de_pago_sin_notificar"
	}

	auditoria_send({ "proceso": "obtener_paremetros_comprobante_de_pago_sin_notificar", "data": data });
	$.ajax({
		url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
			$('#editar_valor_actual_num_minutos_consultar_voucher_sin_envio').html('');
			$('#editar_valor_actual_rango_dias_consultar_voucher_sin_envio').html('');

			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {

				$('#editar_valor_actual_num_minutos_consultar_voucher_sin_envio').append(
					'' + respuesta.num_minutos_consultar_voucher_sin_envio + ''
				);

				$('#editar_valor_actual_rango_dias_consultar_voucher_sin_envio').append(
					'' + respuesta.rango_dias_consultar_voucher_sin_envio + ''
				);

				return false;
			}
		},
		error: function () { }
	});
}

function mostrar_dias_editables_para_depositos_retiros(){
    var data = {
        "accion": "obtener_limites_dias_para_editar_depositos_retiros"
    }

    auditoria_send({ "proceso": "obtener_limites_dias_para_editar_depositos_retiros", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
            $('#modal_edit_trans_aprob_valor_actual').html('');
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                // console.log(respuesta.result);
                respuesta.result.forEach((elemento) => {
					console.log(elemento.valor);
                    $('#modal_edit_trans_aprob_valor_actual').append(
                      ''  + elemento.valor +''
                        );
				});
                $("#modal_editar_nuevo_limite_dias_ret_dep").focus();
                return false;
            }       
        },
        error: function() {}
    });
}

function sec_mant_listarSupervisores(){
    var data = {
        "accion": "obtener_supervisores"
    }

    auditoria_send({ "proceso": "obtener_supervisores", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
            $('#sec_mant_select_supervisores').html('');
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $('#sec_mant_select_supervisores').append(
                    '<option value="0"> TODOS </option>'
                );
                $.each(respuesta.result, function(index, item) {
                    $('#sec_mant_select_supervisores').append(
                        '<option value="' + item.id + '">' + item.supervisor + '</option>'
                    );
                });
                return false;
            }       
        },
        error: function() {}
    });
}

function sec_mant_listarProgramaciones(){
    var supervisor = $('#sec_mant_select_supervisores').val();
    var desde = $('#sec_mant_desde_fecha').val();
    var hasta = $('#sec_mant_hasta_fecha').val();
    var data = {
        "accion": "obtener_programaciones_supervisores",
        "supervisor": supervisor,
        "desde": desde,
        "hasta": hasta
    }

    auditoria_send({ "proceso": "obtener_programaciones_supervisores", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            formato_datatable_destroy('#sec_mant_table_programaciones');
            $('#sec_mant_table_programaciones tbody').html('');
            var respuesta = JSON.parse(resp);
            rr_data_programaciones = [];
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                $.each(respuesta.result, function(index, item) {
                    var boton_editar = '';

                    var fecha = fecha_hora();
                    if(fecha >= item.desde){
                        boton_editar = '';
                        //$('#sec_mant_supervisor_de_turno').html(item.supervisor);
                    }else{
                        var onclick = "sec_mant_abrir_modal_edit_programacion(" + item.id + "," + item.user_id + ",'" + item.supervisor + "','" + item.desde + "', '" + item.hasta + "')";
                        boton_editar = '<button type="button" class="btn btn-info btn-sm" onclick="' + onclick + '"><i class="fa fa-edit"></i></button>';
                    }
                    $('#sec_mant_table_programaciones tbody').append(
                        '<tr>'
                        + '<td>' + item.id + '</td>'
                        + '<td>' + item.supervisor + '</td>'
                        + '<td style="color: #5CA946; font-weight: bold;">' + item.desde + '</td>'
                        + '<td style="color: #DE4F45; font-weight: bold;">' + item.hasta + '</td>'
                        + '<td>' + item.created_at + '</td>'
                        + '<td>' + boton_editar + '</td>'
                        + '</tr>'
                    );
                });
                formato_datatable('#sec_mant_table_programaciones');
                return false;
            }       
        },
        error: function() {}
    });
}

function sec_mant_modif_limite_aten(){
    
    var max_aten = $('#editar_limite_aten').val();   
    
    if(max_aten == 0){
        swal('Aviso', 'Debe ingresar un valor.', 'warning');
        return false;
    }   
    
    var data = {
        "accion": "guardar_max_aten_clientes",     
        "max_aten": max_aten 
    }
    auditoria_send({ "proceso": "guardar_max_aten_clientes", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            console.log(resp);
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                swal('Aviso', 'Se modificó el valor exitosamente.', 'success');
                $('#sec_mant_modal_limite_clientes').modal('hide');
                $('#editar_limite_aten').val('');
                $('#editar_limite_cli').val('');
                $('#editar_limite_tercero').val('');
                return false;
            }      
        },
        error: function() {}
    });
}


function sec_mant_modif_limite_cli(){
    
    var limite = $('#editar_limite_cli').val();   
    
    if(limite == 0){
        swal('Aviso', 'Debe ingresar un valor.', 'warning');
        return false;
    }   
    
    var data = {
        "accion": "guardar_limite_clientes",     
        "limite": limite 
    }
    auditoria_send({ "proceso": "guardar_limite_clientes", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            console.log(resp);
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                swal('Aviso', 'Se modificó el limite exitosamente.', 'success');
                $('#sec_mant_modal_limite_clientes').modal('hide');
                $('#editar_limite_cli').val('');
                $('#editar_limite_aten').val('');
                $('#editar_limite_tercero').val('');
                return false;
            }      
        },
        error: function() {}
    });
}

function sec_mant_modif_num_minutos_consultar_voucher_sin_envio() {

	var num_minutos = $('#editar_num_minutos_consultar_voucher_sin_envio').val().trim();

	if (num_minutos == "0") {
		swal('Aviso', 'Debe ingresar un valor mayor a 0.', 'warning');
		return false;
	}

	if (num_minutos == "") {
		swal('Aviso', 'Debe ingresar un valor.', 'warning');
		return false;
	}

	var data = {
		"accion": "guardar_num_minutos_consultar_voucher_sin_envio",
		"num_minutos": num_minutos
	}
	auditoria_send({ "proceso": "guardar_num_minutos_consultar_voucher_sin_envio", "data": data });
	$.ajax({
		url: "/sys/set_mantenimientos_etiquetas_tlv.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			console.log(resp);
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				swal('Aviso', 'Se modificó el número de minutos exitosamente.', 'success');
				$('#sec_mant_modal_comprobante_de_pago_sin_notificar').modal('hide');
				$('#editar_num_minutos_consultar_voucher_sin_envio').val('');
				$('#editar_rango_dias_consultar_voucher_sin_envio').val('');
				return false;
			}
		},
		error: function () { }
	});
}

function sec_mant_modif_rango_dias_consultar_voucher_sin_envio() {

	var num_dias = $('#editar_rango_dias_consultar_voucher_sin_envio').val().trim();

	if (num_dias == "0") {
		swal('Aviso', 'Debe ingresar un valor mayor a 0.', 'warning');
		return false;
	}

	if (num_dias == "") {
		swal('Aviso', 'Debe ingresar un valor.', 'warning');
		return false;
	}

	var data = {
		"accion": "guardar_rango_dias_consultar_voucher_sin_envio",
		"num_dias": num_dias
	}
	auditoria_send({ "proceso": "guardar_rango_dias_consultar_voucher_sin_envio", "data": data });
	$.ajax({
		url: "/sys/set_mantenimientos_etiquetas_tlv.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		success: function (resp) {
			console.log(resp);
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) == 400) {
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				swal('Aviso', 'Se modificó el número de días exitosamente.', 'success');
				$('#sec_mant_modal_comprobante_de_pago_sin_notificar').modal('hide');
				$('#editar_num_minutos_consultar_voucher_sin_envio').val('');
				$('#editar_rango_dias_consultar_voucher_sin_envio').val('');
				return false;
			}
		},
		error: function () { }
	});
}

function sec_mant_modif_limite_tercero(){

    var limite_terc = $('#editar_limite_tercero').val();   

    if(limite_terc == 0){
        swal('Aviso', 'Debe ingresar un valor.', 'warning');
        return false;
    }   

    var data = {
        "accion": "guardar_limite_terceros",     
        "limite_terc": limite_terc 
    }
    auditoria_send({ "proceso": "guardar_limite_terceros_autorizados", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            console.log(resp);
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                swal('Aviso', 'Se modificó el limite exitosamente.', 'success');
                $('#sec_mant_modal_limite_clientes').modal('hide');
                $('#editar_limite_cli').val('');
                $('#editar_limite_aten').val('');
                $('#editar_limite_tercero').val('');
                return false;
            }      
        },
        error: function() {}
    });
}

function modificar_limite_dias_editar_depositos_retiros_aprobados(){
    
    var limite_dias = $('#modal_editar_nuevo_limite_dias_ret_dep').val();
    
    if(limite_dias == 0){
        swal('Aviso', 'Debe ingresar un valor válido.', 'warning');
        return false;
    }   
    
    var data = {
        "accion": "modificar_limite_dias_para_editar_reti_depos_aprobados",     
        "limite_dias": limite_dias 
    }
    auditoria_send({ "proceso": "modificar_limite_dias_para_editar_reti_depos_aprobados", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            // console.log(resp);
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                swal('Aviso', 'Se modificó el límite de días exitosamente.', 'success');
                $('#sec_modal_limite_dias_editar_depositos_retiros').modal('hide');
                $('#modal_editar_nuevo_limite_dias_ret_dep').val('');
                return false;
            }      
        },
        error: function() {}
    });
}

function sec_mant_agregar_programacion(){
    var supervisor = $('#sec_mant_select_supervisores').val();
    var desde = $('#sec_mant_desde_fecha').val();
    var hasta = $('#sec_mant_hasta_fecha').val();
    var fecha = fecha_hora();
    if(supervisor == 0){
        swal('Aviso', 'Seleccione un supervisor.', 'warning');
        return false;
    }
    if(desde > hasta){
        swal('Aviso', 'La fecha y hora de inicio no puede ser mayor a la fecha y hora final.', 'warning');
        return false;
    }else if(desde == hasta){
        swal('Aviso', 'Las fechas y horas no pueden ser iguales', 'warning');
        return false;
    }else if(desde.replace('T',' ') < fecha || hasta.replace('T',' ') < fecha){
        swal('Aviso', 'Las fechas y horas no pueden ser menores a la fecha y hora actual', 'warning');
        return false;
    }
    /***********************VALIDACION HORARIO****************************/
    var data = {
        "accion": "obtener_programaciones_supervisores_guardadas",
        "desde": desde,
        "hasta": hasta
    }
    auditoria_send({ "proceso": "obtener_programaciones_supervisores_guardadas", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            console.log(resp);
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                guardar_nueva_programacion();
            }
            if (parseInt(respuesta.http_code) == 200) {

                $.each(respuesta.result, function(index, item) {
                    swal('Aviso', "El supervisor " + item.supervisor + " tiene un turno \n desde : " + item.desde + " hasta : " + item.hasta, 'warning');
                    return false;
                });
                return false;
            }       
        },
        error: function() {}
    });
}

function guardar_nueva_programacion(){
    var supervisor = $('#sec_mant_select_supervisores').val();
    var desde = $('#sec_mant_desde_fecha').val();
    var hasta = $('#sec_mant_hasta_fecha').val();
    var data = {
        "accion": "guardar_programacion_horario_supervisor",
        "supervisor": supervisor,
        "desde": desde,
        "hasta": hasta
    }

    auditoria_send({ "proceso": "guardar_programacion_horario_supervisor", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
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
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                //swal('Aviso', 'Se agregó la programación exitosamente.', 'success');
                resetear_fecha_hora_inputs();
                sec_mant_listarProgramaciones();
                return false;
            }       
        },
        error: function() {}
    });
}

function resetear_fecha_hora_inputs(){
    var dt = new Date();
    var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
    var hora_actual = dt.getHours();
    //var hora_actual_desde = String('0' + hora_actual).slice(-2);
    //var hora_actual_hasta = ((hora_actual >= 23)?hora_actual:String('0' + (hora_actual + 1))).slice(-2);

    var fecha_hora_input_desde = new Date().toJSON().slice(0,11) + '00:00';
    var fecha_hora_input_hasta = new Date().toJSON().slice(0,11) + '23:59';

    $("#sec_mant_desde_fecha").val(fecha_hora_input_desde);
    $("#sec_mant_hasta_fecha").val(fecha_hora_input_hasta);

    $("#sec_mant_desde_fecha").attr("min", fecha_hora_input_desde);
    $("#sec_mant_hasta_fecha").attr("min", fecha_hora_input_hasta);
}

function fecha_hora(){
    var dateN = new Date(); 
    var fecha = dateN.getFullYear() + '-' +
                String('0' + (dateN.getMonth() + 1)).slice(-2) + '-' +
                String('0' + dateN.getDate()).slice(-2) + ' ' +
                String('0' + dateN.getHours()).slice(-2) + ':' +
                String('0' + dateN.getMinutes()).slice(-2) + ':' +
                String('0' + dateN.getSeconds()).slice(-2);
    return fecha;
}

function cancelar_edicion_programacion(){
    $('#sec_mant_input_id_supervisor_edit').val('');
    $('#sec_mant_input_id_programacion_edit').val('');

    $('#sec_mant_input_supervisor_edit').val('');
    $('#sec_mant_input_desde_edit').val('');
    $('#sec_mant_input_hasta_edit').val('');
    $('#sec_mant_modal_edit_programacion_pago').modal('hide')
}

function sec_mant_abrir_modal_edit_programacion(id_programacion, id_supervisor, supervisor, desde, hasta){
    $('#sec_mant_modal_edit_programacion_pago').modal();  

    $('#sec_mant_input_id_supervisor_edit').val(id_supervisor);
    $('#sec_mant_input_id_programacion_edit').val(id_programacion);

    $('#sec_mant_input_supervisor_edit').val(supervisor);
    $('#sec_mant_input_desde_edit').val(desde.replace(' ','T'));
    $('#sec_mant_input_desde_edit').attr("min", desde.replace(' ','T'));
    $('#sec_mant_input_hasta_edit').val(hasta.replace(' ','T'));

}

function validar_actualizacion_programacion(){
    var supervisor = $('#sec_mant_input_id_supervisor_edit').val();
    var programacion = $('#sec_mant_input_id_programacion_edit').val();
    var desde = $('#sec_mant_input_desde_edit').val();
    var hasta = $('#sec_mant_input_hasta_edit').val();
    var fecha = fecha_hora();
    if(desde > hasta){
        swal('Aviso', 'La fecha y hora de inicio no puede ser mayor a la fecha y hora final.', 'warning');
        return false;
    }else if(desde == hasta){
        swal('Aviso', 'Las fechas y horas no pueden ser iguales', 'warning');
        return false;
    }else if(desde.replace('T',' ') < fecha || hasta.replace('T',' ') < fecha){
        swal('Aviso', 'Las fechas y horas no pueden ser menores a la fecha y hora actual', 'warning');
        return false;
    }
    /***********************VALIDACION PROGRAMACION****************************/
    var data = {
        "accion": "obtener_programaciones_supervisores_edicion",
        "programacion": programacion,
        "desde": desde,
        "hasta": hasta
    }
    auditoria_send({ "proceso": "obtener_programaciones_supervisores_edicion", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            console.log(resp);
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                sec_mant_actualizar_programacion();
            }
            if (parseInt(respuesta.http_code) == 200) {

                $.each(respuesta.result, function(index, item) {
                    swal('Aviso', "El supervisor " + item.supervisor + " tiene un turno \n desde : " + item.desde + " hasta : " + item.hasta, 'warning');
                    return false;
                });
                return false;
            }       
        },
        error: function() {}
    });
}

function sec_mant_actualizar_programacion(){
    var programacion = $('#sec_mant_input_id_programacion_edit').val();
    var supervisor = $('#sec_mant_input_id_supervisor_edit').val();
    var desde = $('#sec_mant_input_desde_edit').val();
    var hasta = $('#sec_mant_input_hasta_edit').val();
    var data = {
        "accion": "actualizar_programacion_horario_supervisor",
        "programacion": programacion,
        "supervisor": supervisor,
        "desde": desde,
        "hasta": hasta
    }

    auditoria_send({ "proceso": "actualizar_programacion_horario_supervisor", "data": data });
    $.ajax({
        url: "/sys/set_mantenimientos_etiquetas_tlv.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            console.log(resp);
            var respuesta = JSON.parse(resp);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                //swal('Aviso', 'Se agregó la programación exitosamente.', 'success');
                cancelar_edicion_programacion();
                sec_mant_listarProgramaciones();
                return false;
            }       
        },
        error: function() {}
    });
}

function formato_datatable_destroy(id){
    if ($.fn.dataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
    }
}

function formato_datatable(id) {
    $(id).DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'order': [[ 0, "asc" ]],
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