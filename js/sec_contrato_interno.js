var tabla;

function sec_contrato_interno() {
	$('.cont_interno_datepicker').datepicker({
		dateFormat: 'yy-mm-dd',
		changeMonth: true,
		changeYear: true
	});
	sec_con_int_buscar();
	setTimeout(() => {
		mostrarReporteExcel();
	}, 2000);
	$(".select2").select2({
		width: "100%"
	});

	$('.limpiar_input').click(function() {
		$('#' + $(this).attr("limpiar")).val('');
	});

	$('.limpiar_select2').click(function() {
		$('#' + $(this).attr("limpiar")).select2().val("").trigger("change");
		sec_con_int_buscar();
	});

	$('.change-contrato-interno-firmado').on('select2:select', function (e) {
		sec_con_int_buscar();
	});

	sec_contrato_solicitud_obtener_opciones("obtener_directores", $("[name='director_aprobacion_id']"));

	$('#btn_limpiar_filtros_de_busqueda').click(function() {
		$('#sec_con_int_empresa_1').select2().val('').trigger("change");
		$('#sec_con_int_empresa_2').select2().val('').trigger("change");
		$('#sec_con_int_area').select2().val('').trigger("change");
		$('#fecha_inicio_solicitud').val('');
		$('#fecha_fin_solicitud').val('');
		$('#fecha_inicio_inicio').val('');
		$('#fecha_fin_inicio').val('');
	});
}

function sec_con_int_buscar() {
	var empresa_1 = $('#sec_con_int_empresa_1').val();
	var empresa_2 = $('#sec_con_int_empresa_2').val();
	var area = $('#sec_con_int_area').val();
	var fecha_inicio_solicitud = $("#fecha_inicio_solicitud").val();
	var fecha_fin_solicitud = $("#fecha_fin_solicitud").val();
	var fecha_inicio_inicio = $("#fecha_inicio_inicio").val();
	var fecha_fin_inicio = $("#fecha_fin_inicio").val();

	var aprobante	=	$('#director_aprobacion_id').val();
	var estado_aprobacion	= $('#sec_sol_estado_aprobacion').val();

	var search_fecha_fin_aprobacion_firmado = $('#search_fecha_fin_aprobacion').val();
	var search_fecha_inicio_aprobacion_firmado = $('#search_fecha_inicio_aprobacion').val();

	if (fecha_inicio_solicitud.length > 0 && fecha_fin_solicitud.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio_solicitud);
		var fecha_fin_date = new Date(fecha_fin_solicitud);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha de solicitud desde debe ser menor o igual a la fecha de solicitud hasta ',5);
			return false;
		}
	}

	if (fecha_inicio_inicio.length > 0 && fecha_fin_inicio.length > 0) {
		var fecha_inicio_date = new Date(fecha_inicio_inicio);
		var fecha_fin_date = new Date(fecha_fin_inicio);
		if (fecha_inicio_date > fecha_fin_date) {
			alertify.error('La fecha inicio desde debe ser menor o igual a la fecha inicio hasta',5);
			return false;
		}
	}
	var data = {
		accion: "obtener_contratos_internos",
		empresa_1: empresa_1,
		empresa_2: empresa_2,
		area: area,
		fecha_inicio_inicio: fecha_inicio_inicio,
		fecha_fin_inicio: fecha_fin_inicio,
		fecha_inicio_solicitud: fecha_inicio_solicitud,
		fecha_fin_solicitud: fecha_fin_solicitud,
		search_fecha_fin_aprobacion_firmado : search_fecha_fin_aprobacion_firmado,
		search_fecha_inicio_aprobacion_firmado: search_fecha_inicio_aprobacion_firmado,
		aprobante	:	aprobante,
		estado_aprobacion	:	estado_aprobacion
	};
	mostrarReporteExcel();
	$.ajax({
		url: "/sys/get_contrato_interno.php",
		type: "POST",
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			let response = JSON.parse(resp);
			if (response.status == 200) {
				$('#cont_contrato_interno_div_tabla').html(response.result);
				sec_con_int_init_datatable();
			}
		},
		error: function(resp, status) {},
	});
}

function sec_con_int_init_datatable() {
	$('#cont_interno_datatable tfoot th').each(function () {
        var title = $(this).text();
        $(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
    });

	$('#cont_interno_datatable').DataTable({
		"bDestroy": true,
		scrollX: true,
		language: {
			"decimal": "",
			"emptyTable": "Tabla vacia",
			"info": "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
			"infoEmpty": "Mostrando 0 a 0 de 0 entradas",
			"infoFiltered": "(filtered from _MAX_ total entradas)",
			"infoPostFix": "",
			"thousands": ",",
			"lengthMenu": "Mostrar _MENU_ entradas",
			"loadingRecords": "Cargando...",
			"processing": "Procesando...",
			"search": "Filtrar:",
			"zeroRecords": "Sin resultados",
			"paginate": {
				"first": "Primero",
				"last": "Ultimo",
				"next": "Siguiente",
				"previous": "Anterior"
			},
			"aria": {
				"sortAscending": ": activate to sort column ascending",
				"sortDescending": ": activate to sort column descending"
			}
		},
		aLengthMenu: [10, 20, 30, 40, 50],
		"order": [
			[0, 'desc']
		],
		initComplete: function () {
            this.api()
                .columns()
                .every(function () {
                    var that = this;
 
                    $('input', this.footer()).on('keyup change clear', function () {
                        if (that.search() !== this.value) {
                            that.search(this.value).draw();
                        }
                    });
                });
        },
	});
}

function mostrarReporteExcel() {
	var empresa_1 = $('#sec_con_int_empresa_1').val();
	var empresa_2 = $('#sec_con_int_empresa_2').val();
	var area = $('#sec_con_int_area').val();
	var fecha_inicio = $('#sec_con_int_fecha_inicio').val();
	var fecha_fin = $('#sec_con_int_fecha_fin').val();

	var aprobante	=	$('#director_aprobacion_id').val();

	var search_fecha_fin_aprobacion_firmado = $('#search_fecha_fin_aprobacion').val();
	var search_fecha_inicio_aprobacion_firmado = $('#search_fecha_inicio_aprobacion').val();
	document.getElementById('cont_interno_excel').innerHTML = '<a href="export.php?export=cont_contrato_interno&amp;type=lista&amp;empresa_1=' + empresa_1 + '&amp;empresa_2=' + empresa_2 + '&amp;area=' + area + '&amp;fecha_inicio=' + fecha_inicio + '&amp;fecha_fin=' + fecha_fin + '&amp;fecha_inicio_aprobacion='+search_fecha_inicio_aprobacion_firmado+'&amp;fecha_fin_aprobacion='+search_fecha_fin_aprobacion_firmado+'&amp;director_aprobacion_id='+aprobante+'"  class="btn btn-success" download="contrato_interno.xls"><span class="glyphicon glyphicon-export"></span> Exportar excel</a>';
}

function sec_contrato_interno_init() {
	$(".cont_proveedor_param_ruc").mask("00000000000");
	sec_contrato_proveedor_cargar_fechas();
	sec_contrato_proveedor_cargar_data_ini();
}

function sec_contrato_interno_cargar_fechas() {
	// INICIO INICIALIZACION DE DATEPICKER
	$(".cont_proveedor_datepicker").datepicker({
		dateFormat: "dd-mm-yy",
		changeMonth: true,
		changeYear: true,
	}).on("change", function(ev) {
		$(this).datepicker("hide");
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
	});
}

function sec_contrato_interno_cargar_data_ini() {
	// INICIO INICIALIZACION DE DATEPICKER
	if (sec_id == "contrato" && sub_sec_id == "proveedor") {
		var cont_proveedor_param_empresa = $("#cont_proveedor_param_empresa").val();
		var cont_proveedor_param_area_solicitante = $("#cont_proveedor_param_area_solicitante").val();
		var cont_proveedor_param_ruc = $("#cont_proveedor_param_ruc").val();
		var cont_proveedor_param_razon_social = $("#cont_proveedor_param_razon_social").val();
		var cont_proveedor_param_moneda = $("#cont_proveedor_param_moneda").val();
		var cont_proveedor_param_fecha_inicio = $("01-01-2020").val();
		var cont_proveedor_param_fecha_fin = $("#cont_proveedor_param_fecha_fin").val();
		var data = {
			accion: "cont_listar_proveedores",
			cont_proveedor_param_empresa: cont_proveedor_param_empresa,
			cont_proveedor_param_area_solicitante: cont_proveedor_param_area_solicitante,
			cont_proveedor_param_ruc: cont_proveedor_param_ruc,
			cont_proveedor_param_razon_social: cont_proveedor_param_razon_social,
			cont_proveedor_param_moneda: cont_proveedor_param_moneda,
			cont_proveedor_param_fecha_inicio: cont_proveedor_param_fecha_inicio,
			cont_proveedor_param_fecha_fin: cont_proveedor_param_fecha_fin,
		};
		$("#div_proveedor_boton_export").show();
		$("#cont_contrato_proveedor_div_tabla").show();
		tabla = $("#cont_locales_proveedor_datatable").dataTable({
			language: {
				decimal: "",
				emptyTable: "No existen registros",
				info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
				infoEmpty: "Mostrando 0 a 0 de 0 entradas",
				infoFiltered: "(filtered from _MAX_ total entradas)",
				infoPostFix: "",
				thousands: ",",
				lengthMenu: "Mostrar _MENU_ entradas",
				loadingRecords: "Cargando...",
				processing: "Procesando...",
				search: "Filtrar:",
				zeroRecords: "Sin resultados",
				paginate: {
					first: "Primero",
					last: "Ultimo",
					next: "Siguiente",
					previous: "Anterior",
				},
				aria: {
					sortAscending: ": activate to sort column ascending",
					sortDescending: ": activate to sort column descending",
				},
			},
			aProcessing: true,
			aServerSide: true,
			ajax: {
				url: "/sys/set_contrato_proveedor.php",
				data: data,
				type: "POST",
				dataType: "json",
				error: function(e) {
					console.log(e.responseText);
				},
			},
			bDestroy: true,
			aLengthMenu: [10, 20, 30, 40, 50, 100],
		}).DataTable();
	}
}

function sec_contrato_interno_buscar_proveedor_por_parametros() {
	sec_contrato_proveedor_listar_contratos_Datatable();
}
$("#cont_interno_btn_export_proveedor").on("click", function() {
	var cont_proveedor_param_empresa = $("#cont_proveedor_param_empresa").val();
	var cont_proveedor_param_area_solicitante = $("#cont_proveedor_param_area_solicitante").val();
	var cont_proveedor_param_ruc = $("#cont_proveedor_param_ruc").val();
	var cont_proveedor_param_razon_social = $("#cont_proveedor_param_razon_social").val();
	var cont_proveedor_param_moneda = $("#cont_proveedor_param_moneda").val();
	var cont_proveedor_param_fecha_inicio = $("#cont_proveedor_param_fecha_inicio").val();
	var cont_proveedor_param_fecha_fin = $("#cont_proveedor_param_fecha_fin").val();
	if (parseInt(cont_proveedor_param_moneda) == 0) {
		alertify.error("Seleccione el tipo de moneda", 5);
		$("#cont_proveedor_param_moneda").focus();
		$("#cont_proveedor_param_moneda").select2("open");
		return false;
	}
	var data = {
		accion: "cont_proveedor_reporte_proveedor_excel",
		cont_proveedor_param_empresa: cont_proveedor_param_empresa,
		cont_proveedor_param_area_solicitante: cont_proveedor_param_area_solicitante,
		cont_proveedor_param_ruc: cont_proveedor_param_ruc,
		cont_proveedor_param_razon_social: cont_proveedor_param_razon_social,
		cont_proveedor_param_moneda: cont_proveedor_param_moneda,
		cont_proveedor_param_fecha_inicio: cont_proveedor_param_fecha_inicio,
		cont_proveedor_param_fecha_fin: cont_proveedor_param_fecha_fin,
	};
	$.ajax({
		url: "/sys/set_contrato_proveedor.php",
		type: "POST",
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			let obj = JSON.parse(resp);
			window.open(obj.ruta_archivo);
			loading(false);
		},
		error: function(resp, status) {},
	});
});