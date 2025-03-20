//INICIO FUNCIONES INICIALIZADOS
function sec_prestamo_boveda()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_prestamo_boveda_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

    $('.form_modal_sec_prestamo_boveda_param_num_cuenta_cajero').mask('');
    $('#form_modal_sec_prestamo_boveda_param_cliente_dni').mask('00000000');

	// INICIO FORMATO Y BUSQUEDA DE FECHA
    $('.sec_prestamo_boveda_datepicker')
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

    $("#form_modal_sec_prestamo_boveda_param_monto").on({
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

    set_form_modal_sec_prestamo_boveda_param_archivos($('#form_modal_sec_prestamo_boveda_param_archivos'));
	
}
//FIN FUNCIONES INICIALIZADOS

function set_form_modal_sec_prestamo_boveda_param_archivos(object){
    
    $(document).on('click', '#btn_buscar_form_modal_sec_prestamo_boveda_param_archivos', function(event) {
        event.preventDefault();
        object.click();
    });

    object.on('change', function(event) {
        let truncated = $(this)[0].files.length+" Archivos Seleccionados.";
        
        if($(this)[0].files.length <= 1)
        {
            const name = $(this).val().split(/\\|\//).pop();
            truncated = name.length > 1720 ? name.substr(name.length - 17)+'...' : name;
        }

        $("#txt_form_modal_sec_prestamo_boveda_param_archivos").html(truncated);
    });
}

$('#form_modal_sec_prestamo_boveda_param_banco').change(function () 
{
    $("#form_modal_sec_prestamo_boveda_param_banco option:selected").each(function ()
    {   
        var selectValor = $(this).val();

        $('.form_modal_sec_prestamo_boveda_param_num_cuenta_cajero').val("");

        //12 = BBVA
        if(selectValor == 12)
        {
            //18 DIGITOS
            $('.form_modal_sec_prestamo_boveda_param_num_cuenta_cajero').mask('000000000000000000');
            $("#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero_txt_num_digitos_cuenta").html(18)
        }
        else
        {
            //20 DIGITOS
            $('.form_modal_sec_prestamo_boveda_param_num_cuenta_cajero').mask('00000000000000000000');
            $("#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero_txt_num_digitos_cuenta").html(20)
        }
    });
});

$("#sec_prestamo_boveda_param_filtrar_por_fechas").change(function()
{
	if($(this).prop('checked') == true)
	{
		$("#sec_prestamo_boveda_div_filtrar_por_fechas_inicio").show();
		$("#sec_prestamo_boveda_div_filtrar_por_fechas_fin").show();
	}
	else
	{
		$("#sec_prestamo_boveda_div_filtrar_por_fechas_inicio").hide();
		$("#sec_prestamo_boveda_div_filtrar_por_fechas_fin").hide();
	}
})

function sec_prestamo_boveda_listar_prestamos()
{
	
    if(sec_id == "prestamo" && sub_sec_id == "boveda")
    {
        
        var param_local = $("#sec_prestamo_boveda_param_local").val();
        var param_fecha_inicio = $("#sec_prestamo_boveda_param_fecha_inicio").val();
        var param_fecha_fin = $("#sec_prestamo_boveda_param_fecha_fin").val();
        var param_situacion = $("#sec_prestamo_boveda_param_situacion").val();

        var incluir_busqueda_por_fecha = 0;

        if($("#sec_prestamo_boveda_param_filtrar_por_fechas").prop('checked') == true)
		{
			if(param_fecha_inicio == "")
	        {
	            alertify.error('Seleccione Fecha Inicio',5);
	            $("#sec_prestamo_boveda_param_fecha_inicio").focus();
	            setTimeout(function() 
	            {
	                $('#sec_prestamo_boveda_param_fecha_inicio').select2('open');
	            }, 200);
	            return false;
	        }
	        
	        if(param_fecha_fin == "")
	        {
	            alertify.error('Seleccione Fecha Fin',5);
	            $("#sec_prestamo_boveda_param_fecha_fin").focus();
	            setTimeout(function() 
	            {
	                $('#sec_prestamo_boveda_param_fecha_fin').select2('open');
	            }, 200);
	            return false;
	        }
	        
			incluir_busqueda_por_fecha = 1;
		}

        var data = {
            "accion": "sec_prestamo_boveda_listar_prestamos",
            "param_local": param_local,
            "param_fecha_inicio": param_fecha_inicio,
            "param_fecha_fin": param_fecha_fin,
            "param_situacion": param_situacion,
            "incluir_busqueda_por_fecha": incluir_busqueda_por_fecha
        }

        $("#sec_prestamo_boveda_div_btn_export").show();
        $("#sec_prestamo_boveda_btn_export").show();
        $("#sec_prestamo_boveda_div_listar_prestamos").show();
        
        tabla = $("#sec_prestamo_boveda_div_listar_prestamos_datatble").dataTable(
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
                    url : "/sys/set_prestamo_boveda.php",
                    data : data,
                    type : "POST",
                    dataType : "json",
                    beforeSend: function() {
                        loading("true");
                    },
                    complete: function() {
                        loading();
                    },
                    error : function(e)
                    {
                        console.log(e.responseText);
                    }
                },
                "bDestroy" : true,
                aLengthMenu:[10, 20, 30, 40, 50, 100],
                "order" : 
                [
                    0, "desc"   
                ]
            }
        ).DataTable();
    }
}

$("#sec_prestamo_boveda_btn_nuevo").off("click").on("click",function(){
    
    $("#form_modal_sec_prestamo_boveda_param_monto").val('');

    $("#sec_prestamo_boveda_modal_nuevo_prestamo").modal("show");
})

$("#form_modal_sec_prestamo_boveda_param_tipo_tienda").change(function()
{
    var modal_param_tipo_tienda = $('#form_modal_sec_prestamo_boveda_param_tipo_tienda').val();
    
    document.getElementById("form_modal_sec_prestamo_boveda_param_asignar_cajero").checked = false;

    $("#form_modal_sec_prestamo_boveda_param_tipo_prestamo").val("0").trigger("change.select2");

    //INICIO OCULTAR DIV
    $(".sec_prestamo_boveda_modal_nuevo_div_supervisor").hide();
    $(".sec_prestamo_boveda_modal_nuevo_div_cajero").hide();
    $(".sec_prestamo_boveda_modal_nuevo_div_cliente").hide();
    //FIN OCULTAR DIV

    if(modal_param_tipo_tienda == 0)
    {
        alertify.error('Seleccione Tipo Tienda',5);
        $("#form_modal_sec_prestamo_boveda_param_tipo_tienda").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_prestamo_boveda_param_tipo_tienda').select2('open');
        }, 200);

        return false;
    }
    else
    {
        sec_prestamo_boveda_listar_locales(modal_param_tipo_tienda);
    }
})

function sec_prestamo_boveda_listar_locales(id_local_red) 
{   
    var data = {
        "accion": "sec_prestamo_boveda_listar_locales",
        "id_local_red": id_local_red
    }
    
    var array_locales = [];
    
    $.ajax({
        url: "/sys/set_prestamo_boveda.php",
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
            auditoria_send({ "respuesta": "sec_prestamo_boveda_listar_locales", "data": respuesta });
            
            if(parseInt(respuesta.http_code) == 400)
            {
                if(parseInt(respuesta.codigo) == 1)
                {

                    var html = '<option value="0">-- Seleccione --</option>';
                    $("#form_modal_sec_prestamo_boveda_param_local").html(html).trigger("change");

                    setTimeout(function() {
                        $('#form_modal_sec_prestamo_boveda_param_local').select2('open');
                    }, 500);

                    return false;
                }
                else if(parseInt(respuesta.codigo) == 2)
                {
                    swal({
                        title: respuesta.status,
                        text: respuesta.result,
                        html:true,
                        type: "warning",
                        closeOnConfirm: false,
                        showCancelButton: false
                    });
                    return false;
                }
            }
            else if(parseInt(respuesta.http_code) == 200) 
            {
                array_locales.push(respuesta.result);
            
                var html = '<option value="0">-- Seleccione --</option>';

                for (var i = 0; i < array_locales[0].length; i++) 
                {
                    html += '<option value=' + array_locales[0][i].local_id  + '>' + array_locales[0][i].nombre + ' ['+ array_locales[0][i].ceco + ']</option>';
                }

                $("#form_modal_sec_prestamo_boveda_param_local").html(html).trigger("change");

                setTimeout(function() {
                    $('#form_modal_sec_prestamo_boveda_param_local').select2('open');
                }, 500);

                return false;
            }
        },
        error: function() {}
    });
}

$("#form_modal_sec_prestamo_boveda_param_tipo_prestamo").change(function()
{
    var modal_param_tipo_tienda = $('#form_modal_sec_prestamo_boveda_param_tipo_tienda').val();
    var modal_param_tipo_prestamo = $('#form_modal_sec_prestamo_boveda_param_tipo_prestamo').val();
    var param_tiene_num_cuenta = $('#form_modal_sec_prestamo_boveda_param_tiene_num_cuenta').val();

    $("#sec_prestamo_boveda_modal_nuevo_div_asignar_cajero").show();
    document.getElementById("form_modal_sec_prestamo_boveda_param_asignar_cajero").checked = false;
    
    //$(".sec_prestamo_boveda_modal_nuevo_div_supervisor").hide();
    
    if(modal_param_tipo_tienda == 0)
    {
        //$(".sec_prestamo_boveda_modal_nuevo_tipo_tienda_otras_tiendas").hide();
        $("#form_modal_sec_prestamo_boveda_param_tipo_prestamo").val("0").trigger("change.select2");

        alertify.error('Seleccione Tipo Tienda',5);
        $("#form_modal_sec_prestamo_boveda_param_tipo_tienda").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_prestamo_boveda_param_tipo_tienda').select2('open');
        }, 200);

        return false;
    }

    if(modal_param_tipo_prestamo == 0)
    {
        //INICIO OCULTAR DIV
        $(".sec_prestamo_boveda_modal_nuevo_div_supervisor").hide();
        $(".sec_prestamo_boveda_modal_nuevo_div_cajero").hide();
        $("#sec_prestamo_boveda_modal_btn_nuevo_prestamo").hide();
        $(".sec_prestamo_boveda_modal_nuevo_div_cliente").hide();
        $("#form_modal_sec_prestamo_boveda_param_tipo_prestamo").val("0").trigger("change.select2");
        
        //FIN OCULTAR DIV

        alertify.error('Seleccione Tipo Préstamo',5);
        $("#form_modal_sec_prestamo_boveda_param_tipo_prestamo").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_prestamo_boveda_param_tipo_prestamo').select2('open');
        }, 200);

        return false;
    }

    $("#sec_prestamo_boveda_modal_btn_nuevo_prestamo").show();

    if(modal_param_tipo_tienda == 9)
    {
        // RED SPORBARS

        if(modal_param_tipo_prestamo == 7)
        {
            // TIPO PRESTAMO BOVEDA
            $(".sec_prestamo_boveda_modal_nuevo_div_supervisor").hide();
            $(".sec_prestamo_boveda_modal_nuevo_div_cajero").hide();
            $(".sec_prestamo_boveda_modal_nuevo_div_cliente").hide();

            return false;
        }
        else if(modal_param_tipo_prestamo == 8)
        {
            // TIPO PAGO DE PREMIOS
            $("#sec_prestamo_boveda_modal_nuevo_div_asignar_cajero").hide();
            $(".sec_prestamo_boveda_modal_nuevo_div_supervisor").hide();
            $("#sec_prestamo_boveda_modal_nuevo_div_cajero").hide();
            $(".sec_prestamo_boveda_modal_nuevo_div_cliente").show();

            return false;
        }
    }
    else if(modal_param_tipo_tienda == 1 || modal_param_tipo_tienda == 16)
    {
        // RED AT
        // RED IGH

        if(modal_param_tipo_prestamo == 7)
        {
            // TIPO PRESTAMO BOVEDA
            $(".sec_prestamo_boveda_modal_nuevo_div_cajero").hide();
            $(".sec_prestamo_boveda_modal_nuevo_div_cliente").hide();
            $(".sec_prestamo_boveda_modal_nuevo_div_supervisor").show();

            if(param_tiene_num_cuenta != 0)
            {
                $("#sec_prestamo_boveda_modal_btn_nuevo_prestamo").show();
            }
            else
            {
                $("#sec_prestamo_boveda_modal_btn_nuevo_prestamo").hide();
            }

            return true;
        }
        else if(modal_param_tipo_prestamo == 8)
        {
            // TIPO PAGO DE PREMIOS
            $("#sec_prestamo_boveda_modal_nuevo_div_asignar_cajero").hide();
            $(".sec_prestamo_boveda_modal_nuevo_div_supervisor").hide();
            $("#sec_prestamo_boveda_modal_nuevo_div_cajero").hide();
            $(".sec_prestamo_boveda_modal_nuevo_div_cliente").show();

            return false;
        }
    }
})

$("#form_modal_sec_prestamo_boveda_param_asignar_cajero").change(function()
{
    
    var param_tipo_tienda = $('#form_modal_sec_prestamo_boveda_param_tipo_tienda').val();
    var modal_param_tipo_prestamo = $('#form_modal_sec_prestamo_boveda_param_tipo_prestamo').val();
    var param_tiene_num_cuenta = $('#form_modal_sec_prestamo_boveda_param_tiene_num_cuenta').val();

    if(modal_param_tipo_prestamo == 0)
    {
        document.getElementById("form_modal_sec_prestamo_boveda_param_asignar_cajero").checked = false;

        alertify.error('Seleccione Tipo Préstamo',5);
        $("#form_modal_sec_prestamo_boveda_param_tipo_prestamo").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_prestamo_boveda_param_tipo_prestamo').select2('open');
        }, 200);

        return false;
    }

    $("#sec_prestamo_boveda_modal_btn_nuevo_prestamo").show();

    if(param_tipo_tienda == 9)
    {
        // RED SPORTBARS

        if(modal_param_tipo_prestamo == 7)
        {
            // TIPO PRESTAMO BOVEDA
            if($(this).prop('checked') == true)
            {
                $(".sec_prestamo_boveda_modal_nuevo_div_supervisor").hide();
                $(".sec_prestamo_boveda_modal_nuevo_div_cliente").hide();
                $("#sec_prestamo_boveda_modal_nuevo_div_cajero").show();
            }
            else
            {
                $(".sec_prestamo_boveda_modal_nuevo_div_supervisor").hide();
                $(".sec_prestamo_boveda_modal_nuevo_div_cliente").hide();
                $("#sec_prestamo_boveda_modal_nuevo_div_cajero").hide();
            }

            return false;
        }
    }
    else if(param_tipo_tienda == 1 || param_tipo_tienda == 16)
    {
        // RED AT
        // RED IGH

        if(modal_param_tipo_prestamo == 7)
        {
            // TIPO PRESTAMO BOVEDA
            if($(this).prop('checked') == true)
            {
                $(".sec_prestamo_boveda_modal_nuevo_div_supervisor").hide();
                $(".sec_prestamo_boveda_modal_nuevo_div_cliente").hide();
                $(".sec_prestamo_boveda_modal_nuevo_div_cajero").show();
            }
            else
            {
                $(".sec_prestamo_boveda_modal_nuevo_div_cliente").hide();
                $(".sec_prestamo_boveda_modal_nuevo_div_cajero").hide();
                $(".sec_prestamo_boveda_modal_nuevo_div_supervisor").show();

                if(param_tiene_num_cuenta != 0)
                {
                    $("#sec_prestamo_boveda_modal_btn_nuevo_prestamo").show();
                }
                else
                {
                    $("#sec_prestamo_boveda_modal_btn_nuevo_prestamo").hide();
                }
            }
        }
    }
})

$("#sec_prestamo_boveda_modal_nuevo_prestamo .btn_guardar").off("click").on("click",function(){
    
    var modal_param_tipo_tienda = $('#form_modal_sec_prestamo_boveda_param_tipo_tienda').val();
    var modal_param_local = $('#form_modal_sec_prestamo_boveda_param_local').val();
    var modal_param_tipo_prestamo = $('#form_modal_sec_prestamo_boveda_param_tipo_prestamo').val();
    
    var modal_param_num_cuenta_supervisor = $('#form_modal_sec_prestamo_boveda_param_num_cuenta').val();
    var modal_param_cajero = $('#form_modal_sec_prestamo_boveda_param_cajero').val();
    var modal_param_cliente = $('#form_modal_sec_prestamo_boveda_param_cliente').val().trim();
    var modal_param_dni_cliente = $('#form_modal_sec_prestamo_boveda_param_cliente_dni').val().trim();
    var modal_param_banco = $('#form_modal_sec_prestamo_boveda_param_banco').val();
    var modal_param_num_cuenta_cajero = $('#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero').val();

    var modal_param_monto = $('#form_modal_sec_prestamo_boveda_param_monto').val();
    var monto_coma_length = modal_param_monto.replace(/,/g, '');
    var monto_length = monto_coma_length.replace('.', '');
    
    var modal_param_archivos = document.getElementById("form_modal_sec_prestamo_boveda_param_archivos");
    
    var asignar_al_cajero = 0;

    if(modal_param_tipo_tienda == "0")
    {
        alertify.error('Seleccione Tipo Tienda',5);
        $("#form_modal_sec_prestamo_boveda_param_tipo_tienda").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_prestamo_boveda_param_tipo_tienda').select2('open');
        }, 200);

        return false;
    }

    if(modal_param_local == "0")
    {
        alertify.error('Seleccione Tienda',5);
        $("#form_modal_sec_prestamo_boveda_param_local").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_prestamo_boveda_param_local').select2('open');
        }, 200);

        return false;
    }

    if(modal_param_tipo_prestamo == "0")
    {
        alertify.error('Seleccione Tipo Préstamo',5);
        $("#form_modal_sec_prestamo_boveda_param_tipo_prestamo").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_prestamo_boveda_param_tipo_prestamo').select2('open');
        }, 200);

        return false;
    }

    if(modal_param_tipo_tienda == "9")
    {
        //RED SPORTBARS

        if(modal_param_tipo_prestamo == "7")
        {
            //TIPO PRESTAMO: BOVEDA
            
            if($("#form_modal_sec_prestamo_boveda_param_asignar_cajero").prop('checked') == true)
            {
                asignar_al_cajero = 1;

                if(modal_param_cajero == "0")
                {
                    alertify.error('Seleccione Cajero',5);
                    $("#form_modal_sec_prestamo_boveda_param_cajero").focus();
                    setTimeout(function() 
                    {
                        $('#form_modal_sec_prestamo_boveda_param_cajero').select2('open');
                    }, 200);

                    return false;
                }
            }
        }
        else if(modal_param_tipo_prestamo == "8")
        {
            //TIPO PRESTAMO: PAGO DE PREMIOS
            
            if(modal_param_cliente.length == 0)
            {
                alertify.error('Ingrese el Cliente',5);
                $("#form_modal_sec_prestamo_boveda_param_cliente").focus();
                return false;
            }

            if(modal_param_dni_cliente.length == 0)
            {
                alertify.error('Ingrese el DNI Cliente',5);
                $("#form_modal_sec_prestamo_boveda_param_cliente_dni").focus();
                return false;
            }

            if(modal_param_dni_cliente.length < 8)
            {
                alertify.error('Ingrese el DNI Cliente 8 dígitos',5);
                $("#form_modal_sec_prestamo_boveda_param_cliente_dni").focus();
                return false;
            }

            if(modal_param_banco == "0")
            {
                alertify.error('Seleccione Banco',5);
                $("#form_modal_sec_prestamo_boveda_param_banco").focus();
                setTimeout(function() 
                {
                    $('#form_modal_sec_prestamo_boveda_param_banco').select2('open');
                }, 200);

                return false;
            }

            if(modal_param_num_cuenta_cajero.length == 0)
            {
                alertify.error('Ingrese Número de Cuenta',5);
                $("#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero").focus();
                return false;
            }

            if(modal_param_banco == 12)
            {
                if(modal_param_num_cuenta_cajero.length < 18)
                {
                    alertify.error('Ingrese Número de Cuenta de 18 dígitos',5);
                    $("#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero").focus();
                    return false;
                }
            }
            else
            {
                if(modal_param_num_cuenta_cajero.length < 20)
                {
                    alertify.error('Ingrese Número de Cuenta de 20 dígitos',5);
                    $("#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero").focus();
                    return false;
                }
            }

            if(modal_param_archivos.files.length > 0)
            {
                for(var i = 0; i < modal_param_archivos.files.length; i ++)
                {
                    if(modal_param_archivos.files[i].size > 1000000)
                    {
                        alertify.error(`EL Archivo ${modal_param_archivos.files[i].name} debe pesar menos de 1MB`,5);
                        return false;
                    }
                }
            }
            else
            {
                alertify.error('Ingrese al menos 1 Archivo',5);
                $("#form_modal_sec_prestamo_boveda_param_archivos").focus();
                return false;
            }
        }
    }
    else if(modal_param_tipo_tienda == "1" || modal_param_tipo_tienda == "16")
    {
        //RED AT

        if(modal_param_tipo_prestamo == "7")
        {
            //TIPO PRESTAMO: BOVEDA

            if($("#form_modal_sec_prestamo_boveda_param_asignar_cajero").prop('checked') == true)
            {
                asignar_al_cajero = 1;

                if(modal_param_cajero == "0")
                {
                    alertify.error('Seleccione Cajero',5);
                    $("#form_modal_sec_prestamo_boveda_param_cajero").focus();
                    setTimeout(function() 
                    {
                        $('#form_modal_sec_prestamo_boveda_param_cajero').select2('open');
                    }, 200);

                    return false;
                }

                if(modal_param_banco == "0")
                {
                    alertify.error('Seleccione Banco',5);
                    $("#form_modal_sec_prestamo_boveda_param_banco").focus();
                    setTimeout(function() 
                    {
                        $('#form_modal_sec_prestamo_boveda_param_banco').select2('open');
                    }, 200);

                    return false;
                }

                if(modal_param_num_cuenta_cajero.length == 0)
                {
                    alertify.error('Ingrese Número de Cuenta',5);
                    $("#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero").focus();
                    return false;
                }

                if(modal_param_banco == 12)
                {
                    if(modal_param_num_cuenta_cajero.length < 18)
                    {
                        alertify.error('Ingrese Número de Cuenta de 18 dígitos',5);
                        $("#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero").focus();
                        return false;
                    }
                }
                else
                {
                    if(modal_param_num_cuenta_cajero.length < 20)
                    {
                        alertify.error('Ingrese Número de Cuenta de 20 dígitos',5);
                        $("#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero").focus();
                        return false;
                    }
                }
            }
            else
            {
                if(modal_param_num_cuenta_supervisor == "0")
                {
                    alertify.error('Seleccione número cuenta',5);
                    $("#form_modal_sec_prestamo_boveda_param_num_cuenta").focus();
                    setTimeout(function() 
                    {
                        $('#form_modal_sec_prestamo_boveda_param_num_cuenta').select2('open');
                    }, 200);

                    return false;
                }
            }

        }
        else if(modal_param_tipo_prestamo == "8")
        {
            //TIPO PRESTAMO: PAGO DE PREMIOS
            
            if(modal_param_cliente.length == 0)
            {
                alertify.error('Ingrese el Cliente',5);
                $("#form_modal_sec_prestamo_boveda_param_cliente").focus();
                return false;
            }

            if(modal_param_dni_cliente.length == 0)
            {
                alertify.error('Ingrese el DNI Cliente',5);
                $("#form_modal_sec_prestamo_boveda_param_cliente_dni").focus();
                return false;
            }

            if(modal_param_dni_cliente.length < 8)
            {
                alertify.error('Ingrese el DNI Cliente 8 dígitos',5);
                $("#form_modal_sec_prestamo_boveda_param_cliente_dni").focus();
                return false;
            }

            if(modal_param_banco == "0")
            {
                alertify.error('Seleccione Banco',5);
                $("#form_modal_sec_prestamo_boveda_param_banco").focus();
                setTimeout(function() 
                {
                    $('#form_modal_sec_prestamo_boveda_param_banco').select2('open');
                }, 200);

                return false;
            }

            if(modal_param_num_cuenta_cajero.length == 0)
            {
                alertify.error('Ingrese Número de Cuenta',5);
                $("#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero").focus();
                return false;
            }

            if(modal_param_banco == 12)
            {
                if(modal_param_num_cuenta_cajero.length < 18)
                {
                    alertify.error('Ingrese Número de Cuenta de 18 dígitos',5);
                    $("#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero").focus();
                    return false;
                }
            }
            else
            {
                if(modal_param_num_cuenta_cajero.length < 20)
                {
                    alertify.error('Ingrese Número de Cuenta de 20 dígitos',5);
                    $("#form_modal_sec_prestamo_boveda_param_num_cuenta_cajero").focus();
                    return false;
                }
            }

            if(modal_param_archivos.files.length > 0)
            {
                for(var i = 0; i < modal_param_archivos.files.length; i ++)
                {
                    if(modal_param_archivos.files[i].size > 1000000)
                    {
                        alertify.error(`EL Archivo ${modal_param_archivos.files[i].name} debe pesar menos de 1MB`,5);
                        return false;
                    }
                }
            }
            else
            {
                alertify.error('Ingrese al menos 1 Archivo',5);
                $("#form_modal_sec_prestamo_boveda_param_archivos").focus();
                return false;
            }
        }
    }

    if(modal_param_monto == "" || modal_param_monto == 0)
    {
        alertify.error('Ingrese el Monto',5);
        $("#form_modal_sec_prestamo_boveda_param_monto").focus();
        return false;
    }

    if(monto_length.length > 12)
    {
        alertify.error('El monto se permite 12 digitos, incluye 2 decimales',5);
        $("#form_modal_sec_prestamo_boveda_param_monto").focus();
        return false;
    }

    swal(
    {
        title: '¿Está seguro de registrar?',
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
            var dataForm = new FormData($("#form_modal_prestamo_boveda_nuevo_prestamo")[0]);
            dataForm.append("accion","sec_prestamo_boveda_modal_nuevo_prestamo");
            dataForm.append("asignar_al_cajero", asignar_al_cajero);
            dataForm.append("validar_prestamo_existente", "1");

            $.ajax({
                url: "sys/set_prestamo_boveda.php",
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
                    auditoria_send({ "respuesta": "sec_prestamo_boveda_modal_nuevo_prestamo", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200)
                    {
                        swal({
                            title: "Registro exitoso",
                            text: "El préstamo se registró exitosamente",
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
                    else if(parseInt(respuesta.http_code) == 201)
                    {
                        swal({
                            title: "Préstamo existente",
                            text: "Ya tienes un préstamo solicitado para el mismo CECO el dia de hoy.",
                            html:true,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: '#1cb787',
                            cancelButtonColor: '#d56d6d',
                            confirmButtonText: 'Continuar con el préstamo',
                            cancelButtonText: 'Cancelar',
                            closeOnConfirm: false,
                            closeOnCancel: true
                        },
                        function (isConfirm) {
                            if (isConfirm)
                            {
                                dataForm.append("accion","sec_prestamo_boveda_modal_nuevo_prestamo");
                                dataForm.append("asignar_al_cajero", asignar_al_cajero);
                                dataForm.append("validar_prestamo_existente", "0");

                                $.ajax({
                                    url: "sys/set_prestamo_boveda.php",
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
                                        auditoria_send({ "respuesta": "sec_prestamo_boveda_modal_nuevo_prestamo", "data": respuesta });
                                        if(parseInt(respuesta.http_code) == 200) 
                                        {
                                            swal({
                                                title: "Registro exitoso",
                                                text: "El préstamo se registró exitosamente",
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
                                        else if(parseInt(respuesta.http_code) == 400) 
                                        {
                                            swal({
                                                title: respuesta.status,
                                                text: respuesta.error,
                                                html:true,
                                                type: "warning",
                                                closeOnConfirm: false,
                                                showCancelButton: false
                                            });
                                            return false;
                                        }
                                        else {
                                            swal({
                                                title: respuesta.status,
                                                text: respuesta.error,
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
                        
                        return false;
                    }
                    else if(parseInt(respuesta.http_code) == 400) 
                    {
                        swal({
                            title: respuesta.status,
                            text: respuesta.error,
                            html:true,
                            type: "warning",
                            closeOnConfirm: false,
                            showCancelButton: false
                        });
                        return false;
                    }
                    else {
                        swal({
                            title: respuesta.status,
                            text: respuesta.error,
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
})

$("#sec_prestamo_boveda_btn_export").on('click', function () 
{
    
    var param_local = $("#sec_prestamo_boveda_param_local").val();
    var param_fecha_inicio = $("#sec_prestamo_boveda_param_fecha_inicio").val();
    var param_fecha_fin = $("#sec_prestamo_boveda_param_fecha_fin").val();
    var param_situacion = $("#sec_prestamo_boveda_param_situacion").val();

    var incluir_busqueda_por_fecha = 0;

    if($("#sec_prestamo_boveda_param_filtrar_por_fechas").prop('checked') == true)
    {
        if(param_fecha_inicio == "")
        {
            alertify.error('Seleccione Fecha Inicio',5);
            $("#sec_prestamo_boveda_param_fecha_inicio").focus();
            setTimeout(function() 
            {
                $('#sec_prestamo_boveda_param_fecha_inicio').select2('open');
            }, 200);
            return false;
        }
        
        if(param_fecha_fin == "")
        {
            alertify.error('Seleccione Fecha Fin',5);
            $("#sec_prestamo_boveda_param_fecha_fin").focus();
            setTimeout(function() 
            {
                $('#sec_prestamo_boveda_param_fecha_fin').select2('open');
            }, 200);
            return false;
        }
        
        incluir_busqueda_por_fecha = 1;
    }

    var data = {
        "accion": "sec_prestamo_boveda_btn_export",
        "param_local": param_local,
        "param_fecha_inicio": param_fecha_inicio,
        "param_fecha_fin": param_fecha_fin,
        "incluir_busqueda_por_fecha": incluir_busqueda_por_fecha,
        "param_situacion": param_situacion
    }

    $.ajax({
        url: "/sys/set_prestamo_boveda.php",
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
            auditoria_send({ "respuesta": "sec_prestamo_boveda_btn_export", "data": obj });

            window.open(obj.ruta_archivo);
            loading(false);
        },
        error: function(resp, status) {

        }
    });
});

$("#sec_prestamo_boveda_btn_atender_prestamo").off("click").on("click",function(){
    
    $("#sec_prestamo_boveda_modal_atender_prestamo_div_pendiente").hide();
    $("#sec_prestamo_boveda_modal_atender_prestamo_div_pendiente_datatable").hide();
    $("#sec_prestamo_boveda_modal_atender_prestamo_div").hide();
    $("#sec_prestamo_boveda_modal_atender_prestamo_div_datatable").hide();
    $(".sec_prestamo_boveda_modal_atender_prestamo_ocultar_situacion_prestamo_pendiente").hide();

    $("#sec_prestamo_boveda_modal_atender_prestamo").modal("show");
})

$('#sec_prestamo_boveda_modal_atender_prestamo_param_situacion').change(function () 
{
    $("#sec_prestamo_boveda_modal_atender_prestamo_param_situacion option:selected").each(function ()
    {   
        $("#sec_prestamo_boveda_modal_atender_prestamo_div_pendiente").hide();
        $("#sec_prestamo_boveda_modal_atender_prestamo_div_pendiente_datatable").hide();
        $("#sec_prestamo_boveda_modal_atender_prestamo_div").hide();
        $("#sec_prestamo_boveda_modal_atender_prestamo_div_datatable").hide();
        $(".sec_prestamo_boveda_modal_atender_prestamo_ocultar_situacion_prestamo_pendiente").hide();
    });
});

function sec_prestamo_boveda_modal_atender_prestamo_listar()
{
    
    if(sec_id == "prestamo" && sub_sec_id == "boveda")
    {
        $("#sec_prestamo_boveda_modal_atender_prestamo_div_pendiente").hide();
        $("#sec_prestamo_boveda_modal_atender_prestamo_div_pendiente_datatable").hide();
        $("#sec_prestamo_boveda_modal_atender_prestamo_div").hide();
        $("#sec_prestamo_boveda_modal_atender_prestamo_div_datatable").hide();

        var param_situacion = $("#sec_prestamo_boveda_modal_atender_prestamo_param_situacion").val();

        if(param_situacion == 0)
        {
            alertify.error('Seleccione Situación',5);
            $("#sec_prestamo_boveda_modal_atender_prestamo_param_situacion").focus();
            setTimeout(function() 
            {
                $('#sec_prestamo_boveda_modal_atender_prestamo_param_situacion').select2('open');
            }, 200);
            return false;
        }
        else if(param_situacion == 1)
        {
            sec_prestamo_boveda_modal_atender_prestamo_listar_pendiente();
        }
        else
        {
            sec_prestamo_boveda_modal_atender_prestamo_listar_diferente_pendiente();
        }

        $('#sec_prestamo_boveda_modal_atender_prestamo_param_situacion').select2('open');
    }
}

function sec_prestamo_boveda_modal_atender_prestamo_listar_pendiente()
{

    var param_situacion = $("#sec_prestamo_boveda_modal_atender_prestamo_param_situacion").val();

    if(param_situacion == 0)
    {
        alertify.error('Seleccione Situación',5);
        $("#sec_prestamo_boveda_modal_atender_prestamo_param_situacion").focus();
        setTimeout(function() 
        {
            $('#sec_prestamo_boveda_modal_atender_prestamo_param_situacion').select2('open');
        }, 200);
        return false;
    }

    var data = {
        "accion": "sec_prestamo_boveda_modal_atender_prestamo_listar",
        "param_situacion": param_situacion
    }

    $.ajax({
        url: "/sys/set_prestamo_boveda.php",
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
            auditoria_send({ "respuesta": "sec_prestamo_boveda_modal_atender_prestamo_listar", "data": respuesta });
            
            if (parseInt(respuesta.http_code) == 400)
            {
                swal('Aviso', respuesta.result, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200)
            {   
                $("#sec_prestamo_boveda_modal_atender_prestamo_div_pendiente").show();
                $("#sec_prestamo_boveda_modal_atender_prestamo_div_pendiente_datatable").show();
                $('#sec_prestamo_boveda_modal_atender_prestamo_div_pendiente_datatable_body').html(respuesta.result);
                
                if(parseInt(respuesta.total_registro) == 0)
                {
                    $(".sec_prestamo_boveda_modal_atender_prestamo_ocultar_situacion_prestamo_pendiente").hide();
                }
                else
                {
                    $(".sec_prestamo_boveda_modal_atender_prestamo_ocultar_situacion_prestamo_pendiente").show();
                }
                return false;
            }
        },
        error: function() {}
    });
}

function sec_prestamo_boveda_modal_atender_prestamo_listar_diferente_pendiente()
{
    
    var param_situacion = $("#sec_prestamo_boveda_modal_atender_prestamo_param_situacion").val();

    if(param_situacion == 0)
    {
        alertify.error('Seleccione Situación',5);
        $("#sec_prestamo_boveda_modal_atender_prestamo_param_situacion").focus();
        setTimeout(function() 
        {
            $('#sec_prestamo_boveda_modal_atender_prestamo_param_situacion').select2('open');
        }, 200);
        return false;
    }

    var data = {
        "accion": "sec_prestamo_boveda_modal_atender_prestamo_listar_diferente_pendiente",
        "param_situacion": param_situacion
    }

    $("#sec_prestamo_boveda_modal_atender_prestamo_div").show();
    $("#sec_prestamo_boveda_modal_atender_prestamo_div_datatable").show();

    $(".sec_prestamo_boveda_modal_atender_prestamo_ocultar_situacion_prestamo_pendiente").hide();

    tabla = $("#sec_prestamo_boveda_modal_atender_prestamo_div_datatable").dataTable(
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
                url : "/sys/set_prestamo_boveda.php",
                data : data,
                type : "POST",
                dataType : "json",
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                    loading();
                },
                error : function(e)
                {
                    console.log(e.responseText);
                }
            },
            "bDestroy" : true,
            aLengthMenu:[10, 20, 30, 40, 50, 100],
            "order" : 
            [
                0, "desc"   
            ]
        }
    ).DataTable();
}

function sec_prestamo_boveda_modal_atender_prestamo_check_seleccionar_todos()
{
    var num_tabla_anterior = 0;
    
    var nro_filas_tabla = $('#sec_prestamo_boveda_modal_atender_prestamo_div_pendiente_datatable tr').length;
    num_tabla_anterior = nro_filas_tabla - 1;

    for(var i = 1; i <= num_tabla_anterior; i++)
    {
        document.getElementById("sec_prestamo_boveda_modal_check_atencion_jefe_"+i).checked = true;
    }
}

$("#sec_prestamo_boveda_modal_atender_prestamo .btn_guardar").off("click").on("click",function(){
    
    array_check_atencion_prestamo_jefe_aprobar = [];

    var id_prestamo = "";

    var num_tabla_anterior = 0;
    var nro_filas_tabla = $('#sec_prestamo_boveda_modal_atender_prestamo_div_pendiente_datatable tr').length;

    num_tabla_anterior = nro_filas_tabla - 1;

    for(var i = 1; i <= num_tabla_anterior; i++)
    {
        if(document.getElementById('sec_prestamo_boveda_modal_check_atencion_jefe_'+i).checked)
        {
            id_prestamo = $("#sec_prestamo_boveda_modal_check_atencion_jefe_"+i).val();
            var add_data = {
                "item_id" : id_prestamo
            };
            array_check_atencion_prestamo_jefe_aprobar.push(add_data);   
        }
    }

    if(array_check_atencion_prestamo_jefe_aprobar.length == 0)
    {
        alertify.error('Tiene que hacer check al menos un registro',5);
        return false;
    }

    swal(
    {
        title: '¿Está seguro de aprobar?',
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
            var dataForm = new FormData($("#mepa_solicitudes_atencion_liquidacion_jefe_form")[0]);
            dataForm.append("accion","sec_prestamo_boveda_modal_atender_prestamo_datatable_btn_aprobar_prestamo");
            dataForm.append("array_check_atencion_prestamo_jefe_aprobar",JSON.stringify(array_check_atencion_prestamo_jefe_aprobar));

            auditoria_send({ "proceso": "sec_prestamo_boveda_modal_atender_prestamo_datatable_btn_aprobar_prestamo", "data": dataForm });

            $.ajax({
                url: "sys/set_prestamo_boveda.php",
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function( xhr ) {
                    loading(true);
                },
                success: function(data){
                    
                    var respuesta = JSON.parse(data);
                    auditoria_send({ "respuesta": "sec_prestamo_boveda_modal_atender_prestamo_datatable_btn_aprobar_prestamo", "data": respuesta });
                    if(parseInt(respuesta.http_code) == 200) 
                    {
                        swal({
                            title: "Aprobración exitoso",
                            text: "El préstamo fue aprobado exitosamente",
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
                            title: "Error al aprobar",
                            text: respuesta.result,
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
})
