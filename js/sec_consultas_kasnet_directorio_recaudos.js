function sec_consultas_kasnet_directorio_recaudos(){

	$('body').ready(function(){
		$('.kdr_search').focus();
	});

	$(document).on('keyup', function(e){
		if(e.which == 27){
			$('.kdr_search').val('');
			var value = "";
			filtrar(value);
		}
	});

	$(document).on("keyup", ".kdr_search", function(){
		$('.val_repli').val($(this).val());
	});

	$(document).on('click', '.limpiar_btn', function(){
		$('.kdr_search').val('');
		var value = "";
		filtrar(value);
		$('.kdr_search').focus();
	});


	$('#btnKdrImport').on('click', function(event) {
		event.preventDefault();
		$('#fileKdrUpload').click();
	});

	$('#fileKdrUpload').on('change', function(event) {
		event.preventDefault();
		$('#formKdr').submit();
	});

	$('#formKdr').on('submit', function(event) {
		event.preventDefault();
		var form_data = (new FormData(this));
		$.ajax({
			url: "/sys/get_consulta_kasnet_directorio_recaudos.php",
			type: "POST",
			data: form_data,
			cache: false,
			contentType: false,
			processData:false,
			success: function(data){
				$('#fileKdrUploadReset').click();
				swal("Archivos Importados", "Los recaudos fueron insertados/actualizados exitosamente.", "success");
				$('.kdr_search').focus();
				setTimeout(
					function() {
						location.reload();
					}, 2000);
			},
			always: function(data){
				console.log(data);
			}
		});
	});

	$("#tblKdr").width($(window).width()-95);
	$('#tblKdr').fixMe({"columns": 0, "footer": false, "marginTop":50, "zIndex": 1, "bgColor": "white", "bgHeaderColor": "white"});

	$(".kdr_search").on("keyup", function() {
		var value = $(this).val().toLowerCase();
		filtrar(value);
	});
}


function filtrar(value){
	$("#tblKdr tbody tr").filter(function() {
		$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
	});
}
