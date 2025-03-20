function sec_cobranzas_detalle_estados_cuenta() {
	console.log("sec_cobranzas_detalle_estados_cuenta");
	sec_cobranzas_detalle_estados_cuenta_events();
}

function sec_cobranzas_detalle_estados_cuenta_events() {
    console.log("sec_cobranzas_detalle_estados_cuenta_events");

	var $select_2_perido_inicio = $("#periodo_inicio_cobranzas_detalle_ec");
	var lastOption_select2 = $select_2_perido_inicio.find('option').last();
	lastOption_select2.prop('selected', true);
	$select_2_perido_inicio.trigger('change');

	$("#btn_consultar_ec").off().on("click", function () {
		$("#tipo_cobranzas_detalle_ec").val("1");
		cargar_tabla_locales_server()
	});
	/*
	$("#local_cobranzas_detalle_ec").on("change",function(){
		$("#tipo_cobranzas_detalle_ec").val("1");
		cargar_tabla_locales_server()
	});
	*/
	// $("#periodo_inicio_cobranzas_detalle_ec").on("change",function(){
	// 	$("#tipo_cobranzas_detalle_ec").val("1");
	// 	cargar_tabla_locales_server()
	// });
	// $("#periodo_fin_cobranzas_detalle_ec").on("change",function(){
	// 	$("#tipo_cobranzas_detalle_ec").val("1");
	// 	cargar_tabla_locales_server()
	// });

	$(document).on("click", "#table_estados_de_cuenta .descargar_excel_detalle", function(){
		detalle_estado_cuenta_descarga_excel($(this).data());
	});
}

function detalle_estado_cuenta_descarga_excel(data) {
	var data_descarga = {};
	data_descarga.opt = "detalle_estado_cuenta_descarga_excel";
	data_descarga.local_id = data.local_id;
	data_descarga.id_periodo_inicio = $("#periodo_inicio_cobranzas_detalle_ec").val();
	data_descarga.id_periodo_fin = $("#periodo_fin_cobranzas_detalle_ec").val();

	loading(true);
	$.ajax({
		type: "POST",
		url: "sys/get_cobranzas_estados_de_cuenta.php",
		data: data_descarga,
		xhrFields: {
			responseType: "blob"
		},
	}).done(function(res) {
		loading(false);

		swal({
			title: '¡Éxito!',
			text: 'El archivo se ha descargado correctamente',
			type: "success"
		});
		
		let today = new Date();
		let dd = String(today.getDate()).padStart(2, '0');
		let mm = String(today.getMonth() + 1).padStart(2, '0');
		let yyyy = today.getFullYear();
		today = `${dd}_${mm}_${yyyy}`;
		var blob = res;
		var downloadUrl = URL.createObjectURL(blob);
		var a = document.createElement("a");
		a.href = downloadUrl;
		a.download = `detalle_estados_de_cuenta_${data.local_id}-${today}.xls`;
		a.target = '_blank';
		a.click();
	});
}