//INICIO FUNCIONES INICIALIZADOS
function sec_mepa_tesoreria()
{   
    // INICIO FORMATO COMBO CON BUSQUEDA
    $(".sec_mepa_tesoreria_select_filtro").select2({ width: '100%' });
    // FIN FORMATO COMBO CON BUSQUEDA

    $('.mepa_programacion_num_comprobante_asignacion_id').mask('00000');
    $('.mepa_programacion_num_comprobante_liquidacion_id').mask('00000');

    // INICIO FORMATO Y BUSQUEDA DE FECHA
    $('.sec_mepa_tesoreria_datepicker')
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
}
//FIN FUNCIONES INICIALIZADOS


function mepa_tesoreria_listar_programaciones()
{
    if(sec_id == "mepa" && sub_sec_id == "tesoreria")
    {
        var accion = "";

        var mepa_tesoreria_param_tipo_solicitud = $("#mepa_tesoreria_param_tipo_solicitud").val();
        var mepa_tesoreria_param_fecha_inicio = $("#mepa_tesoreria_param_fecha_inicio").val();
        var mepa_tesoreria_param_fecha_fin = $("#mepa_tesoreria_param_fecha_fin").val();

        if(mepa_tesoreria_param_tipo_solicitud == 0)
        {
            alertify.error('Seleccione Tipo Solicitud',5);
            $("#mepa_tesoreria_param_tipo_solicitud").focus();
            setTimeout(function() 
            {
                $('#mepa_tesoreria_param_tipo_solicitud').select2('open');
            }, 200);
            return false;
        }

        if(mepa_tesoreria_param_tipo_solicitud == 1)
        {
            accion = "mepa_tesoreria_listar_programaciones_asignacion";
        }
        else if(mepa_tesoreria_param_tipo_solicitud == 2)
        {
            accion = "mepa_tesoreria_listar_programaciones_liquidacion";
        }
        else if(mepa_tesoreria_param_tipo_solicitud == 9)
        {
            accion = "mepa_tesoreria_listar_programaciones_aumento_asignacion";
        }
        else
        {
            alertify.error('No se encontro el Tipo de Solicitud',5);
            return false;
        }

        var data = {
            "accion": accion,
            "mepa_tesoreria_param_fecha_inicio":mepa_tesoreria_param_fecha_inicio,
            "mepa_tesoreria_param_fecha_fin":mepa_tesoreria_param_fecha_fin,
            "mepa_tesoreria_param_tipo_solicitud":mepa_tesoreria_param_tipo_solicitud
        }

        $("#mepa_tesoreria_programaciones_div_tabla").show();
        
        tabla = $("#mepa_tesoreria_programaciones_datatable").dataTable(
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
                    url : "/sys/set_mepa_tesoreria.php",
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

