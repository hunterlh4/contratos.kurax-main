// INICIO FUNCION DE INICIALIZACION
function sec_mepa_migrar_dato()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_migrar_dato_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	set_mepa_migrar_dato_archivo($('#mepa_reporte_migrar_dato_select_opcion_9_file'));

}
// FIN FUNCION DE INICIALIZACION

function set_mepa_migrar_dato_archivo(object)
{
	
	$(document).on('click', '#mepa_reporte_migrar_dato_select_opcion_9_btn_buscar_file', function(event) {
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

		$("#mepa_reporte_migrar_dato_select_opcion_9_btn_buscar_file_txt_archivo").html(truncated);
	});
}

function mepa_migrar_dato_btn_descargar(ruta_archivo)
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

$('#sec_mepa_migrar_dato_select_tipo').change(function () 
{
	$("#sec_mepa_migrar_dato_select_tipo option:selected").each(function ()
	{	
		// INICIO DIV OPCIONES DEL SELECT
		$("#mepa_reporte_migrar_dato_select_opcion_9_div").hide();
		// FIN DIV OPCIONES DEL SELECT

		var selectValor = $(this).val();
		
		if(selectValor == 0)
		{
			alertify.error('Seleccione Tipo Reporte',5);
			$("#sec_mepa_migrar_dato_select_tipo").focus();
			setTimeout(function() {
				$('#sec_mepa_migrar_dato_select_tipo').select2('open');
			}, 500);

			return false;
		}
		else if(selectValor == 9)
		{
			// Migrar razón social de los usuarios
			$("#mepa_reporte_migrar_dato_select_opcion_9_div").show();

		}
		else
		{
			alertify.error('Tipo Reporte no encontrado ',5);
			$("#sec_mepa_migrar_dato_select_tipo").focus();
			setTimeout(function() {
				$('#sec_mepa_migrar_dato_select_tipo').select2('open');
			}, 500);

			return false;
		}
	});
});

$(document).on('submit', "#form_mepa_reporte_migrar_dato_select_opcion_9", function(e) 
{
	e.preventDefault();
	
	var migrar_dato_file = document.getElementById("mepa_reporte_migrar_dato_select_opcion_9_file");

	if(migrar_dato_file.files.length == 0)
	{
		alertify.error('Seleccione el archivo',5);
		$("#mepa_reporte_migrar_dato_select_opcion_9_file").focus();
		return false;
	}

	swal(
	{
		title: '¿Está seguro de subir el archivo?',
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
			var dataForm = new FormData($("#form_mepa_reporte_migrar_dato_select_opcion_9")[0]);
			dataForm.append("accion","mepa_migrar_dato_file_select_opcion_9");

			$.ajax({
				url: "sys/set_mepa_migrar_dato.php",
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

					if(parseInt(respuesta.http_code) == 200) 
					{
						swal({
							title: "Migración exitoso",
							text: "Nº de registros encontrados: " +respuesta.cant_registros + " <br> Nº de registros migrados: " + respuesta.cant_registros_migrados,
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        window.location.href = "?sec_id=mepa&sub_sec_id=migrar_dato";
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=migrar_dato";
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
	});

});
