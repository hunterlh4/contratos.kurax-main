//INICIO FUNCIONES INICIALIZADOS
function sec_prestamo_slot()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_prestamo_slot_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	// INICIO FORMATO Y BUSQUEDA DE FECHA
    $('.sec_prestamo_slot_datepicker')
        .datepicker({
            dateFormat:'yy/mm/dd',
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

    $("#form_modal_sec_prestamo_slot_param_monto").on({
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
	
}
//FIN FUNCIONES INICIALIZADOS

$('#sec_prestamo_slot_param_tipo_busqueda').change(function () 
{
    $("#sec_prestamo_slot_param_tipo_busqueda option:selected").each(function ()
    {   
        var selectValor = $(this).val();

        sec_prestamo_slot_listar_locales_busqueda(selectValor);
    });
});

function sec_prestamo_slot_listar_locales_busqueda(tipo_busqueda) 
{   
    var data = {
        "accion": "sec_prestamo_slot_listar_locales_busqueda",
        "tipo_busqueda": tipo_busqueda
    }
    
    var array_locales = [];
    
    $.ajax({
        url: "/sys/set_prestamo_slot.php",
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
            if (parseInt(respuesta.http_code) == 400) 
            {
                var html = '<option value="0">-- Todos --</option>';
                $("#sec_prestamo_slot_param_local").html(html).trigger("change");

                setTimeout(function() {
                    $('#sec_prestamo_slot_param_local').select2('open');
                }, 500);

                return false;

            }
            
            if (parseInt(respuesta.http_code) == 200) 
            {
                array_locales.push(respuesta.result);
            
                var html = '<option value="0">-- Todos --</option>';

                for (var i = 0; i < array_locales[0].length; i++) 
                {
                    html += '<option value=' + array_locales[0][i].local_id  + '>' + array_locales[0][i].nombre + '</option>';
                }

                $("#sec_prestamo_slot_param_local").html(html).trigger("change");

                setTimeout(function() {
                    $('#sec_prestamo_slot_param_local').select2('open');
                }, 500);
                
                return false;
            }
        },
        error: function() {}
    });
}

$("#sec_prestamo_slot_param_filtrar_por_fechas").change(function()
{
	if($(this).prop('checked') == true)
	{
		$("#sec_prestamo_slot_div_filtrar_por_fechas_inicio").show();
		$("#sec_prestamo_slot_div_filtrar_por_fechas_fin").show();
	}
	else
	{
		$("#sec_prestamo_slot_div_filtrar_por_fechas_inicio").hide();
		$("#sec_prestamo_slot_div_filtrar_por_fechas_fin").hide();
	}
})

function sec_prestamo_slot_listar_prestamos()
{
	
    if(sec_id == "prestamo" && sub_sec_id == "slot")
    {
        
        var param_tipo_busqueda = $("#sec_prestamo_slot_param_tipo_busqueda").val();
        var param_local = $("#sec_prestamo_slot_param_local").val();
        var param_fecha_inicio = $("#sec_prestamo_slot_param_fecha_inicio").val();
        var param_fecha_fin = $("#sec_prestamo_slot_param_fecha_fin").val();
        var param_situacion = $("#sec_prestamo_slot_param_situacion").val();

        var incluir_busqueda_por_fecha = 0;

        if($("#sec_prestamo_slot_param_filtrar_por_fechas").prop('checked') == true)
		{
			if(param_fecha_inicio == "")
	        {
	            alertify.error('Seleccione Fecha Inicio',5);
	            $("#sec_prestamo_slot_param_fecha_inicio").focus();
	            setTimeout(function() 
	            {
	                $('#sec_prestamo_slot_param_fecha_inicio').select2('open');
	            }, 200);
	            return false;
	        }
	        
	        if(param_fecha_fin == "")
	        {
	            alertify.error('Seleccione Fecha Fin',5);
	            $("#sec_prestamo_slot_param_fecha_fin").focus();
	            setTimeout(function() 
	            {
	                $('#sec_prestamo_slot_param_fecha_fin').select2('open');
	            }, 200);
	            return false;
	        }
	        
			incluir_busqueda_por_fecha = 1;
		}

        var data = {
            "accion": "sec_prestamo_slot_listar_prestamos",
            "param_tipo_busqueda": param_tipo_busqueda,
            "param_local": param_local,
            "param_fecha_inicio": param_fecha_inicio,
            "param_fecha_fin": param_fecha_fin,
            "incluir_busqueda_por_fecha": incluir_busqueda_por_fecha,
            "param_situacion": param_situacion
        }

        $("#sec_prestamo_slot_div_btn_export").show();
        $("#sec_prestamo_slot_btn_export").show();
        $("#sec_prestamo_slot_div_listar_prestamos").show();
        
        tabla = $("#sec_prestamo_slot_div_listar_prestamos_datatble").dataTable(
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
                    url : "/sys/set_prestamo_slot.php",
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

$("#sec_prestamo_slot_btn_nuevo").off("click").on("click",function(){
    
    $("#form_modal_sec_prestamo_slot_param_monto").val('');

    $("#sec_prestamo_slot_modal_nuevo_prestamo").modal("show");

})

$('#form_modal_sec_prestamo_slot_param_local_origen').change(function () 
{
    $("#form_modal_sec_prestamo_slot_param_local_origen option:selected").each(function ()
    {   
        var selectValor = $(this).val();

        if(selectValor != 0)
        {
            sec_prestamo_slot_listar_locales_destino_receptor(selectValor);
        }
    });
});

function sec_prestamo_slot_listar_locales_destino_receptor(id_local_origen) 
{   
    var data = {
        "accion": "sec_prestamo_slot_listar_locales_destino_receptor",
        "id_local_origen": id_local_origen
    }
    
    var array_locales_receptor = [];
    
    $.ajax({
        url: "/sys/set_prestamo_slot.php",
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
            auditoria_send({ "respuesta": "sec_prestamo_slot_listar_locales_destino_receptor", "data": respuesta });
            
            if (parseInt(respuesta.http_code) == 400) 
            {
                var html = '<option value="0">-- Seleccione --</option>';
                $("#form_modal_sec_prestamo_slot_param_local_destino").html(html).trigger("change");

                setTimeout(function() {
                    $('#form_modal_sec_prestamo_slot_param_local_destino').select2('open');
                }, 500);

                return false;

            }
            
            if (parseInt(respuesta.http_code) == 200) 
            {
                array_locales_receptor.push(respuesta.result);
            
                var html = '<option value="0">-- Seleccione --</option>';

                for (var i = 0; i < array_locales_receptor[0].length; i++) 
                {
                    html += '<option value=' + array_locales_receptor[0][i].local_id  + '>' + array_locales_receptor[0][i].nombre + ' [' + array_locales_receptor[0][i].ceco + ']' + '</option>';
                }

                $("#form_modal_sec_prestamo_slot_param_local_destino").html(html).trigger("change");

                // INICIO MOSTRAR DIV SI EN CASO NO EXISTE CAJA ABIERTA
                if(respuesta.existe_caja_abierta == 0)
                {
                    $("#sec_prestamo_form_modal_div_existe_caja_abierta").show();
                    $('#sec_prestamo_slot_modal_nuevo_prestamo .btn_guardar').prop("disabled", true);
                }
                else
                {
                    $("#sec_prestamo_form_modal_div_existe_caja_abierta").hide();
                    $('#sec_prestamo_slot_modal_nuevo_prestamo .btn_guardar').prop("disabled", false);

                    setTimeout(function() {
                        $('#form_modal_sec_prestamo_slot_param_local_destino').select2('open');
                    }, 500);
                }
                // FIN MOSTRAR DIV SI EN CASO NO EXISTE CAJA ABIERTA
                
                return false;
            }
        },
        error: function() {}
    });
}

$("#sec_prestamo_slot_modal_nuevo_prestamo .btn_guardar").off("click").on("click",function(){
    
    var dataForm = new FormData($("#form_modal_prestamo_slot_nuevo_prestamo")[0]);

    var modal_param_local_origen = $('#form_modal_sec_prestamo_slot_param_local_origen').val();
    var modal_param_local_destino = $('#form_modal_sec_prestamo_slot_param_local_destino').val();
    var modal_param_monto = $('#form_modal_sec_prestamo_slot_param_monto').val();

    if(modal_param_local_origen == "0")
    {
        alertify.error('Seleccione Local Origen',5);
        $("#form_modal_sec_prestamo_slot_param_local_origen").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_prestamo_slot_param_local_origen').select2('open');
        }, 200);

        return false;
    }

    if(modal_param_local_destino == "0")
    {
        alertify.error('Seleccione Local Destino',5);
        $("#form_modal_sec_prestamo_slot_param_local_destino").focus();
        setTimeout(function() 
        {
            $('#form_modal_sec_prestamo_slot_param_local_destino').select2('open');
        }, 200);
        return false;
    }

    if(modal_param_monto == "" || modal_param_monto == 0)
    {
        alertify.error('Ingrese el Monto',5);
        $("#form_modal_sec_prestamo_slot_param_monto").focus();
        return false;
    }

    dataForm.append("accion","sec_prestamo_slot_modal_nuevo_prestamo");

    $.ajax({
        url: "sys/set_prestamo_slot.php",
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
            auditoria_send({ "respuesta": "sec_prestamo_slot_modal_nuevo_prestamo", "data": respuesta });
            if(parseInt(respuesta.http_code) == 200) 
            {
                swal({
                    title: "Registro exitoso",
                    text: "El prestamo se registró exitosamente",
                    html:true,
                    type: "success",
                    timer: 6000,
                    closeOnConfirm: false,
                    showCancelButton: false
                },
                function (isConfirm) {
                    window.location.href = "?sec_id=prestamo&sub_sec_id=slot";
                });

                setTimeout(function() {
                    window.location.href = "?sec_id=prestamo&sub_sec_id=slot";
                }, 5000);

                return true;
            }
            else if(parseInt(respuesta.http_code) == 400) 
            {
                swal({
                    title: respuesta.status,
                    text: '',
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
})

$("#sec_prestamo_slot_btn_export").on('click', function () 
{
    
    var param_tipo_busqueda = $("#sec_prestamo_slot_param_tipo_busqueda").val();
    var param_local = $("#sec_prestamo_slot_param_local").val();
    var param_fecha_inicio = $("#sec_prestamo_slot_param_fecha_inicio").val();
    var param_fecha_fin = $("#sec_prestamo_slot_param_fecha_fin").val();
    var param_situacion = $("#sec_prestamo_slot_param_situacion").val();

    var incluir_busqueda_por_fecha = 0;

    if($("#sec_prestamo_slot_param_filtrar_por_fechas").prop('checked') == true)
    {
        if(param_fecha_inicio == "")
        {
            alertify.error('Seleccione Fecha Inicio',5);
            $("#sec_prestamo_slot_param_fecha_inicio").focus();
            setTimeout(function() 
            {
                $('#sec_prestamo_slot_param_fecha_inicio').select2('open');
            }, 200);
            return false;
        }
        
        if(param_fecha_fin == "")
        {
            alertify.error('Seleccione Fecha Fin',5);
            $("#sec_prestamo_slot_param_fecha_fin").focus();
            setTimeout(function() 
            {
                $('#sec_prestamo_slot_param_fecha_fin').select2('open');
            }, 200);
            return false;
        }
        
        incluir_busqueda_por_fecha = 1;
    }

    var data = {
        "accion": "sec_prestamo_slot_btn_export",
        "param_tipo_busqueda": param_tipo_busqueda,
        "param_local": param_local,
        "param_fecha_inicio": param_fecha_inicio,
        "param_fecha_fin": param_fecha_fin,
        "incluir_busqueda_por_fecha": incluir_busqueda_por_fecha,
        "param_situacion": param_situacion
    }

    $.ajax({
        url: "/sys/set_prestamo_slot.php",
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
            auditoria_send({ "respuesta": "sec_prestamo_slot_btn_export", "data": obj });

            window.open(obj.ruta_archivo);
            loading(false);
        },
        error: function(resp, status) {

        }
    });
});