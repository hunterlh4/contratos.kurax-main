function sec_herramientas_ti_proceso()
{
	$('.fecha_datepicker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:ss',
    });


	var fromInput = $('.fecha_datepicker_desde');
	var toInput = $('.fecha_datepicker_hasta');

	fromInput.datetimepicker({
		format: 'YYYY-MM-DD HH:mm:ss',
		locale: 'es',
		maxDate: toInput.val(),
	});

	toInput.datetimepicker({
		format: 'YYYY-MM-DD HH:mm:ss',
		locale: 'es',
		minDate: fromInput.val(),
		//useCurrent: false //Important! See issue #1075
	});

	fromInput.on("dp.change", function (e) {
		toInput.data("DateTimePicker").minDate(e.date);
	});

	toInput.on("dp.change", function (e) {
		fromInput.data("DateTimePicker").maxDate(e.date);
	});

	
	$("#frm_herramientas_ti_proceso").submit(function (e) {
		e.preventDefault();
		var proceso_id = $('#sec_herr_ti_proc_proceso_id').val();
		var proceso_detalle_id = $('#sec_herr_ti_proc_proceso_detalle_id').val();
		var fecha_inicio = $('#sec_herr_ti_proc_fecha_inicio').val();
		var fecha_fin = $('#sec_herr_ti_proc_fecha_fin').val();	
		
		if (parseInt(proceso_id) == 0) {
			alertify.error("Seleccione una entidad", 5);
			$("#sec_herr_ti_proc_proceso_id").focus();
			$("#sec_herr_ti_proc_proceso_id").select2("open");
			return false;
		}
		if (parseInt(proceso_detalle_id) == 0) {
			alertify.error("Seleccione los datos a consultar", 5);
			$("#sec_herr_ti_proc_proceso_detalle_id").focus();
			$("#sec_herr_ti_proc_proceso_detalle_id").select2("open");
			return false;
		}
		if (fecha_inicio.length == 0) {
			alertify.error("seleccione una fecha inicio", 5);
			$("#sec_herr_ti_proc_fecha_inicio").focus();
			return false;
		}
		if (fecha_fin.length == 0) {
			alertify.error("seleccione una fecha fin", 5);
			$("#sec_herr_ti_proc_fecha_fin").focus();
			return false;
		}

		sec_herramientas_ti_proc_buscar_reporte();
	});
}

function sec_herramientas_ti_proc_obtener_proceso_detalle() {
	
	var proceso_id = $('#sec_herr_ti_proc_proceso_id').val();
	let data = {
		action: 'herramientas_ti/proceso/obtener_proceso_detalle',
		proceso_id : proceso_id
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  $('#sec_herr_ti_proc_proceso_detalle_id').find("option").remove().end();
		  var datos_value = [];
		  if (respuesta.status == 200) {
			$(respuesta.result).each(function (i, e) {
				datos_value.push(e.id);
				opcion = $("<option value='" + e.id + "'>" + e.columna + "</option>");
				$('#sec_herr_ti_proc_proceso_detalle_id').append(opcion);
			});
			$('#sec_herr_ti_proc_proceso_detalle_id').val(datos_value).trigger("change");
		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_proc_buscar_reporte() {
	
	var proceso_id = $('#sec_herr_ti_proc_proceso_id').val();
	var proceso_detalle_id = $('#sec_herr_ti_proc_proceso_detalle_id').val();
	var fecha_inicio = $('#sec_herr_ti_proc_fecha_inicio').val();
	var fecha_fin = $('#sec_herr_ti_proc_fecha_fin').val();	

	let data = {
		action: 'herramientas_ti/proceso/buscar_reporte',
		proceso_id : proceso_id,
		proceso_detalle_id : proceso_detalle_id,
		fecha_inicio : fecha_inicio,
		fecha_fin : fecha_fin,
	};

	$.ajax({
		url: "/sys/router/herramientas_ti/index.php",
		type: "POST",
		data: data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
		  var respuesta = JSON.parse(datos);
		  if (respuesta.status == 200) {
			if ($.fn.DataTable.isDataTable('#tbl_herramienta_ti_reporte')) {
				var table = $('#tbl_herramienta_ti_reporte').DataTable()
                table.destroy();
                table = null; // Limpiar la variable
            }
			$("#tbl_herramienta_ti_reporte thead tr").empty();
			$("#tbl_herramienta_ti_reporte tbody").empty();
			var columnHeaders = respuesta.result.columnas.map(function(columnName) {
				return '<th clsas="text-center">' + columnName + '</th>';
			});
			$("#tbl_herramienta_ti_reporte thead tr").append(columnHeaders);

			sec_herramientas_ti_proc_render_table_reporte(respuesta.result.data, respuesta.result.columnas);

		  }
		},
		error: function () {},
	});
}

function sec_herramientas_ti_proc_render_table_reporte(data = [], columnNames = []) {

	
	var columns = [];
	columnNames.forEach(element => {
		columns.push({data : element})
	});

	$("#tbl_herramienta_ti_reporte")
	  .dataTable({
		bDestroy: true,
		data: data,
		columns: columns,
		responsive: true,
		order: [[0, 'desc']],
		pageLength: 25,
		language: {
		  decimal: "",
		  emptyTable: "Tabla vacia",
		  info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
		  infoEmpty: "Mostrando 0 a 0 de 0 entradas",
		  infoFiltered: "(filtrado de _MAX_ entradas)",
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
		  buttons: {
			pageLength: {
				_: "Mostrar %d Resultados",
				'-1': "Tout afficher"
			}
		  }
		  
		},
		autoWidth: true ,
		scrollX: true, 
		sScrollX: '100%',
		sScrollXInner: '110%',
		bScrollCollapse: true,
		dom: 'Bfrtip',
		buttons: [
            'pageLength',
			{
                extend: 'excelHtml5',
				title: 'Herramientas TI - Reporte'
            },
        ],
	  })
	  .DataTable();



	 
}

// function sec_report_repot_ti_data_table() {

// 	$('#tbl_herramienta_ti_reporte').DataTable({
//         dom: 'Bfrtip',
// 		pageLength: 50,
// 		language: {
// 		  decimal: "",
// 		  emptyTable: "Tabla vacia",
// 		  info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
// 		  infoEmpty: "Mostrando 0 a 0 de 0 entradas",
// 		  infoFiltered: "(filtrado de _MAX_ total entradas)",
// 		  infoPostFix: "",
// 		  thousands: ",",
// 		  lengthMenu: "Mostrar _MENU_ entradas",
// 		  loadingRecords: "Cargando...",
// 		  processing: "Procesando...",
// 		  search: "Filtrar:",
// 		  zeroRecords: "Sin resultados",
// 		  paginate: {
// 			first: "Primero",
// 			last: "Ultimo",
// 			next: "Siguiente",
// 			previous: "Anterior",
// 		  },
// 		  aria: {
// 			sortAscending: ": activate to sort column ascending",
// 			sortDescending: ": activate to sort column descending",
// 		  },
// 		  buttons: {
// 			pageLength: {
// 				_: "Mostrar %d Resultados",
// 				'-1': "Tout afficher"
// 			}
// 		  }
// 		},
//         buttons: [
//             'excelHtml5'
//         ]
//     });

// }

