function sec_conciliacion_reporte_conciliacion() {

		loading(true);
		conci_reporte_conciliacion_events();
		conci_reporte_conciliacion_obtener_proveedor();
		conci_reporte_conciliacion_settings();
		loading();

		function conci_reporte_conciliacion_settings() {
			$('.conci_reporte_conciliacion_select2').select2({
				closeOnSelect: false,
				allowClear: false,
			});
			
			$('.conci_reporte_conciliacion_zona').select2({
				closeOnSelect: false,
				allowClear: false
			});
			
			}
		function conci_reporte_conciliacion_events() {
			$(".conci_reporte_conciliacion_btn_filtrarConciliacion")
				.off("click")
				.on("click", function () {
					loading(true);
					conci_reporte_conciliacion_get_liquidaciones();
				});
			}

		function conci_reporte_conciliacion_get_liquidaciones() {
			loading(true);
			var get_liquidaciones_data = {};
			get_liquidaciones_data.filtro = {};
			get_liquidaciones_data.filtro.zona_id = $("#search_conci_reporte_conciliacion_zona").val();
			get_liquidaciones_data.filtro.locales = $("#search_conci_reporte_conciliacion_proveedor_id").val();
			get_liquidaciones_data.filtro.canales_de_venta = $('#search_conci_reporte_conciliacion_count').val();
			get_liquidaciones_data.filtro.supervisores = $('#search_conci_reporte_conciliacion_supervisor').val();
			get_liquidaciones_data.filtro.estado_locales = "activos";

			console.log(get_liquidaciones_data.filtro.canales_de_venta);
			get_liquidaciones_data.accion = "conci_reporte_conciliacion_Datatable";

			$.ajax({
				data: get_liquidaciones_data,
				type: "POST",
				url: "/sys/get_conciliacion_reporte_conciliacion.php",
				async: "false"
			})
				.done(function (responsedata, textStatus, jqXHR) {
					try {
						var response = jQuery.parseJSON(responsedata);
						console.log(response)
						var obj = response.data;
						conci_reporte_conciliacion_mostrar_datatable(obj);
					} catch (err) {
						console.log(err);
						swal({
							title: 'Error en la base de datos',
							type: "warning",
							timer: 2000,
						}, function () {
							swal.close();
							loading();
						});
					}
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					console.log("La solicitud liquidaciones a fallado: " + textStatus);
				});
			}

		const colors = ['#f0f8ff', '#e6e6fa', '#e0ffff', '#b0e0e6', '#add8e6', '#87cefa', '#d3d3d3', '#f5f5f5'];

		function getColorForProvider(index) {
			return colors[index % colors.length];
			}

		function conci_reporte_conciliacion_mostrar_datatable(obj) {

			var datatable_data = obj.datatable_data;
			var meses = obj.meses;
			var totales = obj.totales;
			var concepto = "GASTOS";

			$('#tabla_conci_reporte_conciliacion tfoot').empty();
			$('#tabla_conci_reporte_conciliacion tfoot').append("<tr>");
			$('#tabla_conci_reporte_conciliacion tfoot tr')
				.append("<th>")
				.append("<th>")
				;

			$(meses).each(function (i, e) {
				$('#tabla_conci_reporte_conciliacion tfoot tr')
					.append("<th></th>")
			});

			var columnas = [];
			columnas.push({ title: "PROVEEDOR", data: "PROVEEDOR", className: "tabla_conci_reporte_conciliacion_local_td", defaultContent: "---" });
			columnas.push({ title: "CONCEPTOS", data: "CANAL DE VENTA", className: "tabla_search_conci_reporte_conciliacion_count_td", defaultContent: "---" });
			$(meses).each(function (i, e) {
				columnas.push({
					title: moment(e, "YYYY-MM").locale("es").format("MMMM YYYY").toUpperCase(),
					data: e,
					className: "text-right tabla_conci_reporte_conciliacion_tfoot_meses",
					defaultContent: "-",
					render: function (data, type, row, meta) {
						let formattedData;
						if (row["CANAL DE VENTA"].includes("Conteo")) {
							formattedData = parseInt(data, 10);
						} else if (concepto != 'CANTIDAD DE TICKETS') {
							let formatter = new Intl.NumberFormat('en-US', {
								maximumFractionDigits: 3,
								minimumFractionDigits: 2
							});
							formattedData = formatter.format(data);
						} else {
							formattedData = data;
						}
		
						if ((row["CANAL DE VENTA"] === "Monto Diferencia" || row["CANAL DE VENTA"] === "Conteo Diferencia"  || row["CANAL DE VENTA"] === "Registros no CONCILIADOS") && data > 0) {
							return '<span style="color:red;">' + formattedData + '</span>';
						}
						return formattedData;
					}
				});
			});

			var table_dt = $('#tabla_conci_reporte_conciliacion').DataTable({
				"bDestroy": true,
				scrollX: true,
				scrollY: false,
				fixedColumns: {
					leftColumns: 2
				},
				lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
				paging: true,
				searching: true,
				bSort: false,
				sPaginationType: "full_numbers",
				Sorting: [[1, 'asc']],
				rowsGroup: [0, 1],
				columns: columnas,
				data: datatable_data,
				sDom: "<'row'<'col-xs-2'l><'col-xs-6'B><'col-xs-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'copy',
						text: 'Copiar',
						footer: true,
						className: 'copiarButton'
					},
					{
						extend: 'csv',
						text: 'CSV',
						footer: true,
						className: 'csvButton',
						filename: 'Reporte_de_conciliacion_' + moment(new Date()).format("YYYY_MM_DD_HH_mm_ss")
					},
					{
						extend: 'excelHtml5',
						text: 'Excel',
						footer: true,
						className: 'excelButton',
						filename: 'Reporte_de_conciliacion_' + moment(new Date()).format("YYYY_MM_DD_HH_mm_ss"),
						customize: function (xlsx) {
							var sheet = xlsx.xl.worksheets['sheet1.xml'];
							$('row:first c', sheet).attr('s', '22');
							$('row c', sheet).each(function () {
								if ($('is t', this).text() == 'TOTAL') {
									$(this).attr('s', '20');
								}
							});
						}
					},
					{
						extend: 'colvis',
						text: 'Visibilidad',
						className: 'visibilidadButton',
						postfixButtons: ['colvisRestore']
					}
				],
				footerCallback: function () {
					var api = this.api();
					for (var i = 2; i < columnas.length; i++) {
						var total = api.column(i, { filter: 'applied' }).data().sum().toFixed(2);
						var total_pagina = api.column(i, { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
						if (total < 0 && total_pagina < 0) {
							//$(api.column(i).footer()).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">' + formatonumeros(total / 2) + '<span><br>');
						}
						else {
							//$(api.column(i).footer()).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">' + formatonumeros(total / 2) + '<span><br>');
						}
					}
				},
				fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
					if (aData["CANAL DE VENTA"] == "TOTAL") {
						$('td', nRow).css('cursor', 'default');
						$('td', nRow).css('background-color', '#9BDFFD');
						$('td', nRow).css('color', '#080FFC');
						$('td', nRow).css('font-weight', '800');
					} else {
						// Get row index
						var rowIndex = $(nRow).index();
						// Get color for provider
						var color = getColorForProvider(rowIndex);
						// Apply color only to the "PROVEEDOR" column (column index 0)
						$('td:eq(0)', nRow).css('background-color', color);
						$('td:eq(0)', nRow).css('color', '#000'); // Ensuring text color is black for readability
					}
				},
				createdRow: function (row, data, index) {},
				columnDefs: [
					{
						aTargets: 'tabla_conci_reporte_conciliacion_tfoot_meses',
						fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
							if (sData < "0") {
								$(nTd).css('color', 'red');
								$(nTd).css('font-weight', 'bold');
							}
						}
					},
				],
				pageLength: '30',
				language: {
					"decimal": ".",
					"thousands": ",",
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
					},
					"buttons": {
						"copyTitle": 'Contenido Copiado',
						"copySuccess": {
							_: '%d filas copiadas',
							1: '1 fila copiada'
						}
					}
				}
			});
			loading();
			}	
	}
	
	function conci_reporte_conciliacion_obtener_proveedor() {
		let select = $("[name='search_conci_reporte_conciliacion_proveedor_id']");

			var get_consolidado_data = {};
			get_consolidado_data.accion = "conci_reporte_conciliacion_proveedor_listar";

			var array_provincias = [];
			$.ajax({
				url: "/sys/get_conciliacion_reporte_conciliacion.php",
				type: "POST",
				data: get_consolidado_data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
				},
				success: function (resp) {
					var respuesta = JSON.parse(resp);
					if (parseInt(respuesta.http_code) == 400) { 
						$(select).empty();
							let opcionDefault = $('<option value="all" selected>Sin proveedores</option>');
						$(select).append(opcionDefault);
					}
					if (parseInt(respuesta.http_code) == 200) {
						array_provincias.push(respuesta.result);
						var html = '<option value="all"> Todos</option>';
						for (var i = 0; i < array_provincias[0].length; i++) {
							html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
						}
						$("#search_conci_reporte_conciliacion_proveedor_id").html(html).trigger("change");
						$("#search_conci_reporte_conciliacion_proveedor_id").val("all").trigger("change");
						return false;
					}
					console.log(array_provincias);
				},
				error: function () { },
			});
	}