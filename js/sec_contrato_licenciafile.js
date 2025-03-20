
var tabla;


function sec_contrato_licencia_file()
{
	$(".select2").select2({ width: '100%' });
}

function buscarArchivosLicenciasMunicipalesLocales()
{
	listarContratosDatatable();
}

function listarContratosDatatable()
{
	
	if(sub_sec_id == "licenciafile")
	{
		$("#cont_contrato_div_tabla").show();

		var cont_licencia_municipal_select_tienda = $("#cont_licencia_municipal_select_tienda").val();

		var cont_licencia_municipal_select_estado = $("#cont_licencia_municipal_select_estado").val();

		var data = {
			"accion": "cont_listar_locales_licenciafile",
			"cont_licencia_municipal_select_tienda" : cont_licencia_municipal_select_tienda
		}

		tabla = $("#cont_locales_licenciafile_datatable").dataTable(
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
					url : "/sys/set_contrato_licenciafile.php",
					data : data,
					type : "POST",
					dataType : "json",
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
				aLengthMenu:[10, 20, 50, 100],
				"order" : 
				[
					0, "desc"	
				] 
			}
		).DataTable();

	}

}

function cont_licenciafileVerFileEnVisor(tipo_documento, ruta_file) 
{
	//debugger;
	var tipodocumento = tipo_documento;
	var ruta = ruta_file;
	var html = '';

	if (tipodocumento == 'pdf') 
	{
		var htmlModal = '<iframe src="'+ruta+'" class="col-xs-12 col-md-12 col-sm-12" width="500" height="525"></iframe>';
		$('#cont_licienciafileDivVisorPdfModal').html(htmlModal);

		$('#exampleModalPreviewServicio').modal('show');

	} 
	else if (tipodocumento == 'jpg' || tipodocumento == 'png' || tipodocumento == 'jpeg') 
	{
		var image = new Image();
		image.src = ruta;
		var viewer = new Viewer(image, 
		{
			hidden: function () {
				viewer.destroy();
			},
		});
		// image.click();
		viewer.show();
	}
}

function sec_contrato_licenciafile_btn_descargar(ruta_archivo)
{
	var extension = "";

	// Obtener el nombre del archivo
	var ultimoPunto = ruta_archivo.lastIndexOf("/");

	if(ultimoPunto !== -1)
	{
	    var extension = ruta_archivo.substring(ultimoPunto + 1);
	}
	
	// Crear un enlace temporal
    var enlace = document.createElement('a');
    enlace.href = ruta_archivo;

    // Darle un nombre al archivo que se descargará
    enlace.download = extension;

    // Simular un clic en el enlace
    document.body.appendChild(enlace);
    enlace.click();

    // Limpiar el enlace temporal
    document.body.removeChild(enlace);
}