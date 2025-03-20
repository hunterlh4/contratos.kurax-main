function sec_conciliacion_mantenimiento() {
	
	sec_conciliacion_mantenimiento_proveedor();

	$(".tab-conciliacion-mantenimiento > div:not(:first-child)").hide();

	$(".tab-conciliacion-mantenimiento > div:first-child").show();

	$("a.tab_btn").click(function (event) {
		event.preventDefault();

		var tab = $(this).data("tab");

		$(".tab-conciliacion-mantenimiento > div").hide();

		$(".tab-conciliacion-mantenimiento > ." + tab).show();
	});

    $('#tab-conciliacion-mantenimiento a[href="#mant_proveedor"]').click(function (e) {
        e.preventDefault();
        sec_conciliacion_mantenimiento_proveedor();
    });

	$('#tab-conciliacion-mantenimiento a[href="#mant_metodo"]').click(function (e) {
        e.preventDefault();
        sec_conciliacion_mantenimiento_metodo();
    });

	$('#tab-conciliacion-mantenimiento a[href="#mant_opcion"]').click(function (e) {
        e.preventDefault();
        sec_conciliacion_mantenimiento_opcion();
    });

	$('#tab-conciliacion-mantenimiento a[href="#mant_correo"]').click(function (e) {
        e.preventDefault();
        sec_conciliacion_mantenimiento_correo();
    });

    $('#tab-conciliacion-mantenimiento a[href="#mant_tipo_cambio"]').click(function (e) {
        e.preventDefault();
        sec_conciliacion_mantenimiento_tipo_cambio();
    });
}



///