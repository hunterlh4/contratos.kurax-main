//INICIO FUNCIONES INICIALIZADOS
function sec_mepa_caja_chica()
{	
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_caja_chica_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA
}
//FIN FUNCIONES INICIALIZADOS

$('#mepa_caja_chica_select_tipo_solicitud').change(function () 
{
	$("#mepa_caja_chica_select_tipo_solicitud option:selected").each(function ()
	{	
		var selectValor = $(this).val();

		if(selectValor != 0)
		{
			mepa_caja_chica_listar_liquidacion(selectValor);
		}
	});
});

function mepa_caja_chica_listar_liquidacion($asignacion_id)
{
	
	if(sec_id == "mepa" && sub_sec_id == "caja_chica")
	{
		var data = {
			"accion": "mepa_caja_chica_listar_liquidacion",
			"mepa_caja_chica_liquidacion_param_asignacion_id" : $asignacion_id
		}

		$("#mepa_caja_chica_liquidacion_div_tabla").show();
		
		tabla = $("#mepa_caja_chica_liquidacion_datatable").dataTable(
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
					url : "/sys/set_mepa_caja_chica.php",
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
					1, "desc"	
				]
			}
		).DataTable();

	}
}
