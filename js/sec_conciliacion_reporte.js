function sec_conciliacion_reporte() {
	
	sec_conciliacion_reporte_conciliacion();

	$(".tab-conciliacion-reporte > div:not(:first-child)").hide();

	$(".tab-conciliacion-reporte > div:first-child").show();

	$("a.tab_btn").click(function (event) {
		event.preventDefault();

		var tab = $(this).data("tab");

		$(".tab-conciliacion-reporte > div").hide();

		$(".tab-conciliacion-reporte > ." + tab).show();
	});

    $('#tab-conciliacion-reporte a[href="#reporte_conciliacion"]').click(function (e) {
        e.preventDefault();
        sec_conciliacion_reporte_conciliacion();
    });

    
	$('#tab-conciliacion-reporte a[href="#reporte_liquidacion"]').click(function (e) {
        e.preventDefault();
        sec_conciliacion_reporte_liquidacion();
    });
    
	$('#tab-conciliacion-reporte a[href="#reporte_anulacion"]').click(function (e) {
        e.preventDefault();
        sec_conciliacion_reporte_anulacion();
    });
	$('#tab-conciliacion-reporte a[href="#reporte_devolucion"]').click(function (e) {
        e.preventDefault();
        sec_conciliacion_reporte_devolucion();
    });

    $('#tab-conciliacion-reporte a[href="#reporte_recaudacion"]').click(function (e) {
        e.preventDefault();
        sec_conciliacion_reporte_recaudacion();
    });

    $('#tab-conciliacion-reporte a[href="#reporte_comision"]').click(function (e) {
        e.preventDefault();
        sec_conciliacion_reporte_comision();
    });
    
}



///