//INICIO FUNCIONES INICIALIZADOS
function sec_prestamo_tesoreria()
{   
    // INICIO FORMATO Y BUSQUEDA DE FECHA
    $('.sec_prestamo_tesoreria_datepicker')
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
}
//FIN FUNCIONES INICIALIZADOS

function prestamo_tesoreria_listar_programacion_pago_boveda()
{
    if(sec_id == "prestamo" && sub_sec_id == "tesoreria")
    {
        var param_fecha_inicio = $("#prestamo_tesoreria_param_fecha_inicio").val();
        var param_fecha_fin = $("#prestamo_tesoreria_param_fecha_fin").val();

        var data = {
            "accion": "prestamo_tesoreria_listar_programacion_pago_boveda",
            "param_fecha_inicio": param_fecha_inicio,
            "param_fecha_fin": param_fecha_fin
        }

        $("#prestamo_tesoreria_programaciones_div_tabla").show();
        
        tabla = $("#prestamo_tesoreria_programaciones_datatable").dataTable(
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
                    url : "/sys/set_prestamo_tesoreria.php",
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
