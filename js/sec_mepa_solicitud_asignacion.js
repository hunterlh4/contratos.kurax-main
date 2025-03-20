// INICIO DECLARACION DE VARIABLES ARRAY
var array_check_asignacion_aprobar = [];
// FIN DECLARACION DE VARIABLES ARRAY

function sec_mepa_solicitud_asignacion()
{

}


function sec_mepa_solicitud_asignacion_check_aprobar_todos()
{
	var num_tabla_anterior = 0;
	
	var nro_filas_tabla = $('#tabla_form_solicitudes_asignacion_detalle tr').length;
	num_tabla_anterior = nro_filas_tabla - 1;

	for(var i = 1; i <= num_tabla_anterior; i++)
	{
		document.getElementById("check_asignacion_"+i).checked = true;
	}
}

function sec_mepa_solicitud_asignacion_check_guardar_solo_check()
{
	array_check_asignacion_aprobar = [];

	var id_asignacion = "";

	var num_tabla_anterior = 0;
	var nro_filas_tabla = $('#tabla_form_solicitudes_asignacion_detalle tr').length;

	num_tabla_anterior = nro_filas_tabla - 1;

	for(var i = 1; i <= num_tabla_anterior; i++)
	{
		if(document.getElementById('check_asignacion_'+i).checked)
		{
			id_asignacion = $("#check_asignacion_"+i).val();
			var add_data = {
				"item_id" : id_asignacion
			};
			array_check_asignacion_aprobar.push(add_data);	
		}
	}

	if(array_check_asignacion_aprobar.length == 0)
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
			var dataForm = new FormData($("#mepa_solicitudes_asignacion_form")[0]);
			dataForm.append("accion","sec_mepa_solicitud_asignacion_check_guardar_solo_check");
			dataForm.append("array_check_asignacion_aprobar",JSON.stringify(array_check_asignacion_aprobar));

			auditoria_send({ "proceso": "sec_mepa_solicitud_asignacion_check_guardar_solo_check", "data": dataForm });

			$.ajax({
				url: "sys/set_mepa_solicitud_asignacion.php",
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
					auditoria_send({ "respuesta": "guardar_solicitud_asignacion_caja_chica", "data": respuesta });
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
					        window.location.href = "?sec_id=mepa&sub_sec_id=solicitud_asignacion";
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=solicitud_asignacion";
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