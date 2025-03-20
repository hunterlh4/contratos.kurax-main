// INICIO DECLARACION DE VARIABLES ARRAY
var array_check_atencion_liquidacion_jefe_aprobar = [];
// FIN DECLARACION DE VARIABLES ARRAY


function sec_mepa_atencion_liquidacion()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_atencion_liquidacion_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA
}


function sec_mepa_solicitud_atencion_liquidacion_jefe_check_aprobar_todos()
{
	var num_tabla_anterior = 0;
	
	var nro_filas_tabla = $('#sec_mepa_atencion_liquidacion_div_pendiente_datatable tr').length;
	num_tabla_anterior = nro_filas_tabla - 1;

	for(var i = 1; i <= num_tabla_anterior; i++)
	{
		document.getElementById("check_atencion_liquidacion_jefe_"+i).checked = true;
	}
}

function sec_mepa_solicitud_atencion_liquidacion_jefe_check_guardar_solo_check()
{
	
	array_check_atencion_liquidacion_jefe_aprobar = [];

	var id_asignacion = "";

	var num_tabla_anterior = 0;
	var nro_filas_tabla = $('#sec_mepa_atencion_liquidacion_div_pendiente_datatable tr').length;

	num_tabla_anterior = nro_filas_tabla - 1;

	for(var i = 1; i <= num_tabla_anterior; i++)
	{
		if(document.getElementById('check_atencion_liquidacion_jefe_'+i).checked)
		{
			id_asignacion = $("#check_atencion_liquidacion_jefe_"+i).val();
			var add_data = {
				"item_id" : id_asignacion
			};
			array_check_atencion_liquidacion_jefe_aprobar.push(add_data);	
		}
	}

	if(array_check_atencion_liquidacion_jefe_aprobar.length == 0)
	{
		alertify.error('Tiene que hacer check al menos un registro',5);
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
			var dataForm = new FormData($("#mepa_solicitudes_atencion_liquidacion_jefe_form")[0]);
			dataForm.append("accion","sec_mepa_solicitud_atencion_liquidacion_jefe_check_guardar_solo_check");
			dataForm.append("array_check_atencion_liquidacion_jefe_aprobar",JSON.stringify(array_check_atencion_liquidacion_jefe_aprobar));

			$.ajax({
				url: "sys/set_mepa_atencion_liquidacion.php",
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
					auditoria_send({ "respuesta": "guardar_solicitud_liquidacion_jefe", "data": respuesta });
					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: "Registro exitoso",
							text: "La solicitud fue registrada exitosamente",
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        window.location.href = "?sec_id=mepa&sub_sec_id=atencion_liquidacion";
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=atencion_liquidacion";
						}, 5000);

						return true;
					} 
					else {
						swal({
							title: "Error al guardar Solicitud",
							text: respuesta.error,
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
	}
	);
}

function sec_mepa_atencion_liquidacion_listar_liquidacion()
{
    
    if(sec_id == "mepa" && sub_sec_id == "atencion_liquidacion")
    {
        $("#sec_mepa_atencion_liquidacion_div_pendiente").hide();
        $("#sec_mepa_atencion_liquidacion_div_pendiente_datatable").hide();

        $("#sec_mepa_atencion_liquidacion_div_diferente_pendiente").hide();
        $("#sec_mepa_atencion_liquidacion_div_diferente_pendiente_datatable").hide();

        var param_situacion = $("#sec_mepa_atencion_liquidacion_param_situacion").val();

        if(param_situacion == 0)
        {
            alertify.error('Seleccione Situación',5);
            $("#sec_mepa_atencion_liquidacion_param_situacion").focus();
            setTimeout(function() 
            {
                $('#sec_mepa_atencion_liquidacion_param_situacion').select2('open');
            }, 200);
            return false;
        }
        else if(param_situacion == 1)
        {
            sec_mepa_atencion_liquidacion_listar_liquidacion_pendiente();
        }
        else
        {
            sec_mepa_atencion_liquidacion_listar_liquidacion_diferente_pendiente();
        }
        
        $('#sec_mepa_atencion_liquidacion_param_situacion').select2('open');
    }
}

function sec_mepa_atencion_liquidacion_listar_liquidacion_pendiente()
{

    var param_situacion = $("#sec_mepa_atencion_liquidacion_param_situacion").val();

    if(param_situacion == 0)
    {
        alertify.error('Seleccione Situación',5);
        $("#sec_mepa_atencion_liquidacion_param_situacion").focus();
        setTimeout(function() 
        {
            $('#sec_mepa_atencion_liquidacion_param_situacion').select2('open');
        }, 200);
        return false;
    }

    var data = {
        "accion": "sec_mepa_atencion_liquidacion_listar_liquidacion_pendiente",
        "param_situacion": param_situacion
    }

    $.ajax({
        url: "/sys/set_mepa_atencion_liquidacion.php",
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
            auditoria_send({ "respuesta": "sec_mepa_atencion_liquidacion_listar_liquidacion_pendiente", "data": respuesta });
            
            if (parseInt(respuesta.http_code) == 400)
            {
                swal('Aviso', respuesta.result, 'warning');
                return false;
            }
            if (parseInt(respuesta.http_code) == 200)
            {   

                $("#sec_mepa_atencion_liquidacion_div_pendiente").show();
                $("#sec_mepa_atencion_liquidacion_div_pendiente_datatable").show();
                $('#sec_mepa_atencion_liquidacion_div_pendiente_datatable_body').html(respuesta.result);
                
                if(parseInt(respuesta.total_registro) == 0)
                {
                    $(".sec_mepa_atencion_liquidacion_check_guardar_aprobar_pendiente").hide();
                }
                else
                {
                    $(".sec_mepa_atencion_liquidacion_check_guardar_aprobar_pendiente").show();
                }

                return false;
            }
        },
        error: function() {}
    });
}

function sec_mepa_atencion_liquidacion_listar_liquidacion_diferente_pendiente()
{
    
    var param_situacion = $("#sec_mepa_atencion_liquidacion_param_situacion").val();

    if(param_situacion == 0)
    {
        alertify.error('Seleccione Situación',5);
        $("#sec_mepa_atencion_liquidacion_param_situacion").focus();
        setTimeout(function() 
        {
            $('#sec_mepa_atencion_liquidacion_param_situacion').select2('open');
        }, 200);
        return false;
    }

    var data = {
        "accion": "sec_mepa_atencion_liquidacion_listar_liquidacion_diferente_pendiente",
        "param_situacion": param_situacion
    }

    $("#sec_mepa_atencion_liquidacion_div_diferente_pendiente").show();
    $("#sec_mepa_atencion_liquidacion_div_diferente_pendiente_datatable").show();

    tabla = $("#sec_mepa_atencion_liquidacion_div_diferente_pendiente_datatable").dataTable(
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
                url : "/sys/set_mepa_atencion_liquidacion.php",
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
            aLengthMenu:[10, 20, 30, 40, 50, 100]
        }
    ).DataTable();
}
