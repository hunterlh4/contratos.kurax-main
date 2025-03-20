//INICIO FUNCIONES INICIALIZADOS
const sec_billetera_reporte = () => {
	
    // INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_billetera_reporte_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	// INICIO FORMATO Y BUSQUEDA DE FECHA
    $('.sec_billetera_reporte_datepicker')
        .datepicker({
            dateFormat:'dd-mm-yy',
            changeMonth: true,
            changeYear: true,
            minDate: start_date
        })
        .on("change", function(ev) {
            $(this).datepicker('hide');
            var newDate = $(this).datepicker("getDate");
            $("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
            // localStorage.setItem($(this).atrr("id"),)
        });
    // FIN FORMATO Y BUSQUEDA DE FECHA

	// INICIO: MONTO DECIMAL
    $(".sec_billetera_reporte_convert_decimal").on({
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
    $(".sec_billetera_reporte_div_validador").hide();
    $("#sec_billetera_reporte_param_fecha_inicio_validacion").val('');
    $("#sec_billetera_reporte_param_fecha_fin_validacion").val('');

}
//FIN FUNCIONES INICIALIZADOS

$("#sec_billetera_reporte_param_estado").change(function(){
    
    var selectValor = $(this).val();

    //4 Rechazado
    //2 validado
    //3 en revision

    if(selectValor == "2" || selectValor == "4" )
    {
        $(".sec_billetera_reporte_div_validador").show();
        $(".sec_billetera_reporte_div_tienda").show();
        $(".sec_billetera_reporte_div_cajero").show();
        $(".sec_billetera_reporte_div_fecha_deposito").hide();

        $("#sec_billetera_reporte_param_fecha_inicio_deposito").val('');
        $("#sec_billetera_reporte_param_fecha_fin_deposito").val('');
    }
    // else if(selectValor == "4")
    // {
    //     $(".sec_billetera_reporte_div_validador").hide();
    //     $(".sec_billetera_reporte_div_tienda").hide();
    // }
    else if(selectValor == "1" || selectValor == "3")
    {
        $(".sec_billetera_reporte_div_validador").hide();
        $(".sec_billetera_reporte_div_tienda").hide();
        $(".sec_billetera_reporte_div_cajero").hide();
        $(".sec_billetera_reporte_div_fecha_deposito").show();

        $("#sec_billetera_reporte_param_fecha_inicio_validacion").val('');
        $("#sec_billetera_reporte_param_fecha_fin_validacion").val('');

        if(selectValor == '3'){
            $(".sec_billetera_reporte_div_tienda").show();
            $(".sec_billetera_reporte_div_cajero").show();
        }
    }
    else
    {
        // todos
        $(".sec_billetera_reporte_div_validador").hide();
        $(".sec_billetera_reporte_div_tienda").show();
        $(".sec_billetera_reporte_div_fecha_deposito").show();
        
        $("#sec_billetera_reporte_param_fecha_inicio_validacion").val('');
        $("#sec_billetera_reporte_param_fecha_fin_validacion").val('');
    }
});

function validar_parametros_busqueda(accion){

    var param_tipo_origen = $("#sec_billetera_reporte_param_tipo_origen").val();
    var param_telefono = $("#sec_billetera_reporte_param_telefono").val();
    var param_usuario_cajero = $("#sec_billetera_reporte_param_usuario_cajero").val();
    var param_fecha_inicio_deposito = $("#sec_billetera_reporte_param_fecha_inicio_deposito").val();
    var param_fecha_fin_deposito = $("#sec_billetera_reporte_param_fecha_fin_deposito").val();
    var param_fecha_inicio_validacion = $("#sec_billetera_reporte_param_fecha_inicio_validacion").val();
    var param_fecha_fin_validacion = $("#sec_billetera_reporte_param_fecha_fin_validacion").val();
    var param_usuario_validador_manual = $("#sec_billetera_reporte_param_usuario_validador_manual").val();
    var param_estado = $("#sec_billetera_reporte_param_estado").val();
    var param_tienda = $("#sec_billetera_reporte_param_tienda").val();
    
    var param_monto_desde = $("#sec_billetera_reporte_param_monto_desde").val();
    var param_monto_desde = param_monto_desde.replace(/,/g, '');
    var monto_desde_length = param_monto_desde.replace('.', '');

    var param_monto_hasta = $("#sec_billetera_reporte_param_monto_hasta").val();
    var param_monto_hasta = param_monto_hasta.replace(/,/g, '');
    var monto_hasta_length = param_monto_hasta.replace('.', '');

    var param_cliente = $("#form_modal_sec_billetera_reporte_param_cliente").val().trim();
    let cant_palabras = param_cliente.split(" ");

    if(param_monto_desde == "" || param_monto_desde == 0)
    {
        alertify.error('Ingrese el Monto Desde S/',5);
        $("#sec_billetera_reporte_param_monto_desde").focus();
        return false;
    }

    if(monto_desde_length.length > 9)
    {
        alertify.error('El monto se permite 9 digitos, incluye 2 decimales',5);
        $("#sec_billetera_reporte_param_monto_desde").focus();
        return false;
    }

    if(param_monto_hasta == "" || param_monto_hasta == 0)
    {
        alertify.error('Ingrese el Monto Hasta S/',5);
        $("#sec_billetera_reporte_param_monto_hasta").focus();
        return false;
    }

    if(monto_hasta_length.length > 9)
    {
        alertify.error('El monto se permite 9 digitos, incluye 2 decimales',5);
        $("#sec_billetera_reporte_param_monto_hasta").focus();
        return false;
    }

    if(param_cliente != "")
    {
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
            $("#form_modal_sec_billetera_reporte_param_cliente").focus();
            return false;
        }
    }

    var data = {
        "accion": accion,
        "param_tipo_origen": param_tipo_origen,
        "param_telefono": param_telefono,
        "param_usuario_cajero": param_usuario_cajero,
        "param_fecha_inicio_validacion": param_fecha_inicio_validacion,
        "param_fecha_fin_validacion": param_fecha_fin_validacion,
        "param_usuario_validador_manual": param_usuario_validador_manual,
        "param_fecha_inicio_deposito": param_fecha_inicio_deposito,
        "param_fecha_fin_deposito": param_fecha_fin_deposito,
        "param_estado": param_estado,
        "param_tienda": param_tienda,
        "param_monto_desde": param_monto_desde,
        "param_monto_hasta": param_monto_hasta,
        "param_cliente": param_cliente
    }

    return data;

}

const sec_billetera_reporte_listar_transacciones = () => {
	
	if(sec_id == "billetera" && sub_sec_id == "reporte")
	{
        var data = validar_parametros_busqueda('sec_billetera_reporte_listar_transacciones')

        $("#sec_billetera_reporte_div_btn_export").show();
        $("#sec_billetera_reporte_btn_export").show();
        $("#sec_billetera_reporte_div_listar_transacciones").show();

        tabla = $("#sec_billetera_reporte_div_listar_transacciones_datatable").dataTable(
        {
            language:{
                "decimal":        "",
                "emptyTable":     "No existen registros",
                "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered":   "(filtered from _MAX_ total entradas)",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing":     "Procesando...",
                "search":         "Filtrar:",
                "zeroRecords":    "Sin resultados",
                "paginate": {
                    "first":      "Primero",
                    "last":       "Ultimo",
                    "next":       "Siguiente",
                    "previous":   "Anterior"
                },
                "aria": {
                    "sortAscending":  ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
            "aProcessing" : true,
            "aServerSide" : true,
            "ajax" :
            {
                url : "/sys/set_billetera_reporte.php",
                data : data,
                type : "POST",
                dataType : "json",
                beforeSend: function() {
                    loading("true");
                },
                complete: function(data) {
                    var totales = JSON.parse(data.responseText).totales
                    console.log(totales);
                    $('#tabla_resumen_totales_estados tbody' ).html('')
                    $('#tabla_resumen_totales_origen tbody' ).html('')
                    $('#tabla_resumen_totales_total tbody' ).html('')
                    $.each(totales,function(i, val){
                        if(i == 'tossstal'){
                                console.log('My array has at position ' + i + ', this value: ' + val.suma + ' ' + val.cantidad);
                        } else {
                            console.log('My array has at position ' + i )
                            $.each(val,function(index, value){
                                $('#tabla_resumen_totales_'+ i + ' tbody' ).append(`  <tr>
                                                                                            <td>` + value.descripcion + `</td>
                                                                                            <td class='text-center'>` + value.cantidad + `</td>
                                                                                            <td class='text-center'>` + value.suma + `</td>
                                                                                        </tr>`)
                                console.log('My array has at position ' + i + ', this value: ' + value.suma + ' ' + value.cantidad);
                            });
                        }
                    });
                    loading();
                },
                error : function(e)
                {
                    console.log(e.responseText);
                }
            },
            columnDefs: [
                {
                    className: 'text-center',
                    targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                }
            ],
            "bDestroy" : true,
            aLengthMenu:[10, 20, 30, 40, 50],
            "order" : []
        }).DataTable();
	}
}

const sec_billetera_reporte_export_listar_transacciones = () => {
	
    var data = validar_parametros_busqueda('sec_billetera_reporte_export_listar_transacciones')

    $.ajax({
        url: "/sys/set_billetera_reporte.php",
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
            if(parseInt(obj.res_estado_archivo) == 1)
            {
                window.open(obj.res_ruta_archivo);
                loading(false);
            }
            else if(parseInt(obj.res_estado_archivo) == 0)
            {
                swal({
                    title: obj.res_titulo,
                    text: obj.res_descripcion,
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
            else
            {
                swal({
                    title: "Error",
                    text: "Ponerse en contacto con Soporte",
                    html:true,
                    type: "warning",
                    closeOnConfirm: false,
                    showCancelButton: false
                });
                return false;
            }
        },
        error: function(resp, status) {

        }
    });
}