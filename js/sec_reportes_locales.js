$(document).ready(function () {
	if ($('#id_sec_reporte_locales').length == 0) {
	} else {
		fncGetaDataFiltrosReporteLocales();
		$("#btnShowDisashopFullHistory").click(function (e) {
			e.preventDefault();

			//$("#hdDisashopHistoryLocalId").val(0);
			$("#mdDisashopHistory").modal("show");
			$('#txtDisashopHistoryTitle').css('display', 'none');
			$('#divCbLocalesDisashop').css('display', '');

			/* -- filtros -- */
			//$("#cbDisashopTipoHistoryLocalId").val(0).change();
			$("#cbLocalesZona").val(0);
            $("#cbLocalesDepartamento").val(0);
            $("#cbLocalesEstado").val(0);
			//$("#cbDisashopTipoHistoryLocalId").val(0);
			//$("#cbDisashopTipoHistoryLocalId").val(0);
			$("#cbLocalesDisashop").val(0).change();
			//$("#dtDisashopHistoryHasta").val("");
			//$("#dtDisashopHistoryDesde").val("");
			/* -- /filtros -- */
		});

		$("#btnLocalesHistoryExport").on("click", function(event){
			event.preventDefault();
			//loading(true);
			var get_data 	= {};
			get_data.accion = 'export_reporte_locales';
            get_data.zona 	= $("#cbLocalesZona option:selected").val();
			get_data.red 	= $("#cbLocalesRedes option:selected").val();
			get_data.departamento 	= $("#cbLocalesDepartamento option:selected").val();
            get_data.estado 	= $("#cbLocalesEstado option:selected").val();
			//get_data.estado 	= $("#cbLocalesTipo option:selected").val();
			//get_data.localId 	= $("#hdDisashopHistoryLocalId").val();
			
			$.ajax({
				type: "POST",
				url: "sys/get_reportes_locales.php",
				data: get_data,
				success: function (response) {
					var jsnoData = JSON.parse(response);
					console.log(jsnoData);
					window.open(jsnoData.path);	
					loading();			
				}
			});
		});
	}
    
	$(".select2").select2({
		closeOnSelect: true,
		width:"100%"
	});
});

function fncGetaDataFiltrosReporteLocales() {
	$.ajax({
		type: "POST",
		data: { accion: 'listar_zonas_departamentos' },
		url: 'sys/get_reportes_locales.php',
		cache: false,
		success: function (response) {
			var jsonData = JSON.parse(response);
			//console.log(jsonData);
            $.each(jsonData.data.departamentos, function (i, item) {
				$('#cbLocalesDepartamento').append($('<option>', {
					value: item.id,
					text: item.nombre
				}));
			});
			$.each(jsonData.data.zones, function (i, item) {
				$('#cbLocalesZona').append($('<option>', {
					value: item.id,
					text: item.nombre
				}));
			});
			$.each(jsonData.data.redes, function (i, item) {
				$('#cbLocalesRedes').append($('<option>', {
					value: item.id,
					text: item.nombre
				}));
			});

		}
	});
}