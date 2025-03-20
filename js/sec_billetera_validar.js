//INICIO FUNCIONES INICIALIZADOS
const sec_billetera_validar = () => {
	
    // INICIO FORMATO COMBO CON BUSQUEDA
    $("#form_modal_sec_billetera_validar_param_telefono").val(1)// SELECCIONADO POR DEFECTO 
    $(".sec_billetera_validar_select_filtro").select2({ width: '100%' });
    // FIN FORMATO COMBO CON BUSQUEDA

    // INICIO FORMATO Y BUSQUEDA DE FECHA
    $('.sec_billetera_validar_datepicker')
        .datepicker({
            dateFormat:'dd-mm-yy',
            changeMonth: true,
            changeYear: true
        })
        .on("change", function(ev) {
            $(this).datepicker('hide');
            var newDate = $(this).datepicker("getDate");
            $("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
            // localStorage.setItem($(this).atrr("id"),)
        });
    // FIN FORMATO Y BUSQUEDA DE FECHA

    // INICIO: MONTO DECIMAL
    $(".sec_billetera_validar_convert_decimal").on({
        "focus": function (event) {
            $(event.target).select();
        },
        "change": function (event) {
            if(parseFloat($(event.target).val().replace(/\,/g, ''))>0)
            {
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
    // FIN: MONTO DECIMAL

    // INICIO: SOLO NUMEROS
    $('.sec_billetera_validar_solo_numero').on('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    // INICIO: SOLO NUMEROS
    
    sec_billetera_validar_listar_mis_transacciones_validadas();

    var count = cantidad_segundos
    setInterval(function() {
        if(count == 0){
            sec_billetera_validar_listar_mis_transacciones_validadas()
            count = cantidad_segundos
        }
        $('.timer').text("Se refrescará en: " + (count--) + " segundos");

    }, 1000);

    $("html").on('keyup', function (e) {
        var keycode = e.keyCode || e.which;
          if (keycode == 27) {//scape
            $('#sec_billetera_validar_modal_listar_transaccion').modal('hide')
            $('#sec_billetera_validar_modal_nueva_transaccion').modal('hide')
          }
    });

    $('#form_modal_sec_billetera_validar_param_fecha').val(fecha_actual)
    $('#form_modal_sec_billetera_validar_param_hora').val(hora_actual)
    $('#sec_billetera_validar_param_fecha').val(fecha_actual)
    $('#sec_billetera_validar_param_hora').val(hora_actual)
}
//FIN FUNCIONES INICIALIZADOS

const sec_billetera_validar_listar_mis_transacciones_validadas = () => {
    
    var data = {
        "accion": "sec_billetera_validar_listar_mis_transacciones_validadas"
    }

    auditoria_send({ "proceso": "sec_billetera_validar_listar_mis_transacciones_validadas", "data": data });

    $.ajax({
        url: "/sys/set_billetera_validar.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            $('#sec_billetera_validar_div_listar_mis_transacciones_validadas_datatable_body').html("<tr><th class='text-center' colspan='9'>Cargando...</th></tr>");
           // loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            
            var respuesta = JSON.parse(resp);
            auditoria_send({ "respuesta": "sec_billetera_validar_listar_mis_transacciones_validadas", "data": respuesta });
            
            if(parseInt(respuesta.http_code) == 400 || parseInt(respuesta.http_code) == 200)
            {
                $('#sec_billetera_validar_div_listar_mis_transacciones_validadas_datatable_body').html(respuesta.data);
                $('#sec_billetera_validar_input_monto_total').val(respuesta.total_monto);
                $('#sec_billetera_validar_input_cantidad_validados').val(respuesta.cant_registros);
                $('#sec_billetera_validar_informacion_local').text(respuesta.informacion_local);

                return false;
            }
        },
        error: function() {}
    });
}

const sec_billetera_validar_guardar_atencion = (param_transaccion_id, param_valor) => {
    
    var titulo = "";

    if(param_valor == 1)
    {
        // Activar
        titulo = "pasar a atendido";
    }
    else
    {
        // Desactivar
        titulo = "pasar a NO atendido";
    }

    swal(
    {
        title: '¿Está seguro de '+titulo+'?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    function(isConfirm)
    {
        if(isConfirm)
        {
            var data = {
                "accion" : "sec_billetera_validar_guardar_atencion",
                "param_transaccion_id" : param_transaccion_id,
                "param_valor" : param_valor
            }
            
            $.ajax({
                url: "sys/set_billetera_validar.php",
                type: 'POST',
                data: data,
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                    loading();
                },
                success: function(data){
                    
                    var respuesta = JSON.parse(data);
                    auditoria_send({ "respuesta": "sec_billetera_validar_guardar_atencion", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200)
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "success",
                            timer: 6000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        },
                        function (isConfirm)
                        {
                            if (isConfirm)
                            {
                                swal.close();
                                sec_billetera_validar_listar_mis_transacciones_validadas();

                                return true;
                            }
                            else
                            {
                                setTimeout(function() {
                                    swal.close();
                                    sec_billetera_validar_listar_mis_transacciones_validadas();

                                    return true
                                }, 5000);
                            }
                        });

                        return true;
                    }
                    else
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "warning",
                            closeOnConfirm: false,
                            showCancelButton: false
                        });

                        return false;
                    }

                }
            });
        }
        else
        {
            swal.close();
            sec_billetera_validar_listar_mis_transacciones_validadas();

            return true;
        }
    });
}

const sec_billetera_validar_ver_detalle_rechazo = (param_usuario_revisor, param_motivo_rechazo) => {

    swal({
        title: "Detalle del rechazo",
        text: '<strong>Usuario Validador:</strong> ' +param_usuario_revisor +' </br> <strong>Motivo:</strong> ' +param_motivo_rechazo,
        html:true,
        closeOnConfirm: false,
        showCancelButton: false
    });
}

const sec_billetera_validar_transacciones = () => {
    var fecha = new Date;
    var num_meses = ["01","02","03","04","05","06","07","08","09","10","11","12"]
    $('#sec_billetera_validar_param_fecha').val(fecha.getDate() + "-" + num_meses[fecha.getMonth()] + "-" + fecha.getFullYear())
    $('#sec_billetera_validar_param_hora').val(fecha.getHours() + ":" + fecha.getMinutes());

    $('#sec_billetera_validar_div_listar_transacciones_datatable_body').html('<tr><th class="text-center" colspan="8">No existen registros</th></tr>');
    $("#sec_billetera_validar_modal_listar_transaccion").modal("show");
}

const sec_billetera_validar_listar_transacciones = () => {
    
    var param_fecha = $("#sec_billetera_validar_param_fecha").val();
    var param_hora = $("#sec_billetera_validar_param_hora").val();
    var param_monto = $("#sec_billetera_validar_param_monto").val();
    var monto_coma_length = param_monto.replace(/,/g, '');
    var monto_length = monto_coma_length.replace('.', '');

    var param_depositante = $("#sec_billetera_validar_param_depositante").val().trim();
    let cant_palabras = param_depositante.split(" ");

    if(param_fecha == "")
    {
        alertify.error('Seleccione la Fecha',5);
        $("#sec_billetera_validar_param_fecha").focus();
        return false;
    }

    if(param_hora == "")
    {
        alertify.error('Ingrese la Hora',5);
        $("#sec_billetera_validar_param_hora").focus();
        return false;
    }

    if(param_monto == "" || param_monto == 0)
    {
        alertify.error('Ingrese el Monto S/',5);
        $("#sec_billetera_validar_param_monto").focus();
        return false;
    }

    if(monto_length.length > 9)
    {
        alertify.error('El monto se permite 9 digitos, incluye 2 decimales',5);
        $("#sec_billetera_validar_param_monto").focus();
        return false;
    }

    if(param_depositante == "")
    {
        alertify.error('Ingrese Depositante',5);
        $("#sec_billetera_validar_param_depositante").focus();
        return false;
    }

    var cant_for = 0;
    cant_palabras.forEach(function(palabra) {
        
        if(palabra != "")
        {
            cant_for += 1;
        }
    });

    if(cant_for <= 1)
    {
        alertify.error('Ingrese al menos un Nombre y un Apellido',5);
        $("#sec_billetera_validar_param_depositante").focus();
        return false;
    }

    var data = {
        "accion": "sec_billetera_validar_listar_transacciones",
        "param_fecha": param_fecha,
        "param_hora": param_hora,
        "param_monto": param_monto,
        "param_depositante": param_depositante
    }
    
    auditoria_send({ "proceso": "sec_billetera_validar_listar_transacciones", "data": data });

    $.ajax({
        url: "/sys/set_billetera_validar.php",
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
            auditoria_send({ "respuesta": "sec_billetera_validar_listar_transacciones", "data": respuesta });
            
            if(parseInt(respuesta.http_code) == 200)
            {
                $('#sec_billetera_validar_div_listar_transacciones_datatable_body').html(respuesta.data);
                $('.sec_billetera_validar_solo_numero').on('input', function() {
                    this.value = this.value.replace(/\D/g, '');
                });

                return false;
            }
            else if(parseInt(respuesta.http_code) == 400)
            {
                $('#sec_billetera_validar_div_listar_transacciones_datatable_body').html(respuesta.data);

                swal({
                    title: "No se encontró yapes pendientes" + 
                    "<br/><span style='color: red;'>Esto puede generar una duplicidad en el cliente</span>" + 
                    "<br/>¿Desea enviar la solicitud de depósito a los validadores?",
                    text: respuesta.descripcion,
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Si',
                    cancelButtonText: 'No',
                    closeOnConfirm: false,
                    closeOnCancel: true,
                    html: true,
                },
                function (isConfirm) {
                    if(isConfirm)
                    {
                        swal.close();
                        $("#sec_billetera_validar_modal_listar_transaccion").modal("hide");
                        sec_billetera_validar_nueva_transaccion();
                    }

                    return true;
                });
            }
        },
        error: function() {}
    });
}

const sec_billetera_validar_transaccion = (param_id, param_fecha, param_hora, param_monto, param_depositante) => {
    
    var titulo = '¿Está seguro de Validar?';

    swal(
    {
        title: `<h3>${titulo}</h3>` + 
        '<div class="col-md-6">'+
            '<span style="font-size:12px">Fecha:</span> '+
            '<input type="text" class="form-control" value="'+param_fecha+'" readonly style="display:block; font-size:11px; margin-top: -10px; text-align: center;"> '+
        '</div>'+
        '<div class="col-md-6">'+
            '<span style="font-size:12px">Hora:</span> '+
            '<input type="text" class="form-control" value="'+param_hora+'" readonly style="display:block; font-size:11px; margin-top: -10px; text-align: center;">'+
        '</div>'+
        '<div class="col-md-6">'+
            '<span style="font-size:12px">Monto:</span> '+
            '<input type="text" class="form-control" value="S/ '+param_monto+'" readonly style="display:block; font-size:11px; margin-top: -10px; text-align:center;"> '+
        '</div>'+
        '<div class="col-md-6">'+
            '<span style="font-size:12px">Depositante:</span> '+
            '<input type="text" class="form-control" value="'+param_depositante+'" readonly style="display:block; font-size:11px; margin-top: -10px; text-align: center;">'+
        '</div>'
        ,
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true,
        html: true,
    },
    function(isConfirm)
    {
        if (isConfirm) 
        {

            var data = {
                "accion": "sec_billetera_validar_transaccion",
                "param_id": param_id,
            }

            auditoria_send({ "proceso": "sec_billetera_validar_transaccion", "data": data });

            $.ajax({
                url: "sys/set_billetera_validar.php",
                type: 'POST',
                data: data,
                //cache: false,
                //contentType: false,
                //processData: false,
                beforeSend: function( xhr ) {
                    loading(true);
                },
                success: function(data){
                    
                    var respuesta = JSON.parse(data);
                    auditoria_send({ "respuesta": "sec_billetera_validar_transaccion", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200) 
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "success",
                            timer: 6000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        },
                        function (isConfirm) {
                            swal.close();
                            $("#sec_billetera_validar_modal_listar_transaccion").modal("hide");
                            sec_billetera_validar_listar_mis_transacciones_validadas();
                        });

                        return true;
                    } 
                    else {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "warning",
                            closeOnConfirm: false,
                            showCancelButton: false
                        });
                        return false;
                    }
                },
                complete: function(){
                    loading(false);
                }
            });
        }
    });

    $('.sec_billetera_validar_solo_numero').on('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
}

const sec_billetera_validar_nueva_transaccion = () => {
    var param_fecha = $("#sec_billetera_validar_param_fecha").val();
    var param_hora = $("#sec_billetera_validar_param_hora").val();
    var param_monto = $("#sec_billetera_validar_param_monto").val();
    var param_depositante = $("#sec_billetera_validar_param_depositante").val().trim();

    $("#form_modal_sec_billetera_validar_param_fecha").val(param_fecha);
    $("#form_modal_sec_billetera_validar_param_hora").val(param_hora);
    $("#form_modal_sec_billetera_validar_param_monto").val(param_monto);
    $("#form_modal_sec_billetera_validar_param_depositante").val(param_depositante);
    
    $("#sec_billetera_validar_modal_nueva_transaccion").modal("show");
}

const sec_billetera_validar_guardar_nueva_transaccion = () => {
    
    var param_fecha = $("#form_modal_sec_billetera_validar_param_fecha").val();
    var param_hora = $("#form_modal_sec_billetera_validar_param_hora").val();
    var param_monto = $("#form_modal_sec_billetera_validar_param_monto").val();
    var monto_coma_length = param_monto.replace(/,/g, '');
    var monto_length = monto_coma_length.replace('.', '');

    var param_depositante = $("#form_modal_sec_billetera_validar_param_depositante").val().trim();
    var param_num_operacion = $("#form_modal_sec_billetera_validar_param_num_operacion").val().trim();
    var param_telefono = $("#form_modal_sec_billetera_validar_param_telefono").val().trim();

    if(param_fecha == "")
    {
        alertify.error('Seleccione la Fecha',5);
        $("#form_modal_sec_billetera_validar_param_fecha").focus();
        return false;
    }

    if(param_hora == "")
    {
        alertify.error('Ingrese la Hora',5);
        $("#form_modal_sec_billetera_validar_param_hora").focus();
        return false;
    }

    if(param_monto == "" || param_monto == 0)
    {
        alertify.error('Ingrese el Monto S/',5);
        $("#form_modal_sec_billetera_validar_param_monto").focus();
        return false;
    }

    if(monto_length.length > 9)
    {
        alertify.error('El monto se permite 9 digitos, incluye 2 decimales',5);
        $("#form_modal_sec_billetera_validar_param_monto").focus();
        return false;
    }

    if(param_depositante == "")
    {
        alertify.error('Ingrese Depositante',5);
        $("#form_modal_sec_billetera_validar_param_depositante").focus();
        return false;
    }

    if(param_num_operacion == "")
    {
        alertify.error('Ingrese Nº Operación',5);
        $("#form_modal_sec_billetera_validar_param_num_operacion").focus();
        return false;
    }

    if(param_telefono == "0")
    {
        alertify.error('Seleccione Teléfono',5);
        $("#form_modal_sec_billetera_validar_param_telefono").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_billetera_validar_param_telefono').select2('open');
        }, 200);
        return false;
    }

    swal(
    {
        title: '¿Está seguro de enviar la solicitud?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        closeOnConfirm: false,
        closeOnCancel: true
    },
    function(isConfirm)
    {
        if (isConfirm)
        {
            var dataForm = new FormData($("#form_modal_billetera_validar_nueva_transaccion")[0]);
            dataForm.append("accion","sec_billetera_validar_guardar_nueva_transaccion");
            
            $.ajax({
                url: "sys/set_billetera_validar.php",
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                    loading();
                },
                success: function(data){
                    
                    var respuesta = JSON.parse(data);
                    auditoria_send({ "respuesta": "sec_billetera_validar_guardar_nueva_transaccion", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200)
                    {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "success",
                            timer: 6000,
                            closeOnConfirm: false,
                            showCancelButton: false
                        },
                        function (isConfirm) {
                            location.reload();
                        });

                        setTimeout(function() {
                            location.reload();
                        }, 5000);

                        return true;
                    }
                    else {
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.descripcion,
                            html:true,
                            type: "warning",
                            closeOnConfirm: false,
                            showCancelButton: false
                        });
                        return false;
                    }
                }
            });
        }
    });
}