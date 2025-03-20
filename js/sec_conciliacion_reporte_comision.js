function sec_conciliacion_reporte_comision() {

		loading(true);
		conci_reporte_comision_events();
		conci_reporte_comision_obtener_proveedor();
		conci_reporte_comision_settings();
		//conci_reporte_comision_obtener_formula();
		loading();

		function conci_reporte_comision_settings() {
			$('.conci_reporte_comision_select2').select2({
				closeOnSelect: false,
				allowClear: false,
			});
			
			$('.conci_reporte_comision_zona').select2({
				closeOnSelect: false,
				allowClear: false
			});
			
			}
		function conci_reporte_comision_events() {
			$(".conci_reporte_comision_btn_filtrarConciliacion")
				.off("click")
				.on("click", function () {
					loading(true);
					conci_reporte_comision_get_comisiones();
				});
			}

		function conci_reporte_comision_get_comisiones() {
			loading(true);
			let data = {};
			//data.zonas = $("#sec_consolidado_free_games_zona").val();
			data.locales = $("#search_conci_reporte_comision_proveedor_id").val();
			console.log(data.locales);
			data.canales_de_venta = $('#search_conci_reporte_comision_formula').val();
			//data.supervisores = $('#sec_consolidado_free_games_supervisor').val();
			data.concepto = $('#search_conci_reporte_comision_tipo').val();
			/*data.razon_social_id = sec_consolidado_free_games_razon_social_id;
			if ($("#sec_consolidado_free_games_estado_locales").hasClass('btn-success')) {
				data.estado_locales = "inactivos";
			} else {
				data.estado_locales = "activos";
			}
			*/
			data.accion = "conci_reporte_comision_Datatable";

			//localStorage.setItem('sec_consolidado_free_games_filtro_locales', data.locales);
			localStorage.setItem('sec_consolidado_free_games_filtro_canales_de_venta', data.canales_de_venta);

			auditoria_send({"proceso": "conci_reporte_comision_Datatable", data});

			$.ajax({
				data,
				type: "POST",
				url: "/sys/get_conciliacion_reporte_comision.php",
				async: "false"
			}).done(function (response) {
				try {
					let json = jQuery.parseJSON(response);
					let obj = json.data;
					conci_reporte_comision_mostrar_datatable(obj);
				} catch (err) {
					swal({
						title: 'Error',
						text: err.message,
						type: "warning",
						timer: 3000,
					}, function () {
						swal.close();
						loading();
					});
				}
			})
				.fail(function (jqXHR, textStatus, errorThrown) {
					swal({
						title: errorThrown + ' (' + textStatus + ')',
						html: true,
						text: jqXHR.responseText,
						type: "warning",
						closeOnConfirm: true
					});
				});
		}

		function conci_reporte_comision_mostrar_datatable(obj) {
			var datatable_data = obj.datatable_data;
			var meses = obj.meses;
			var totales = obj.totales;
			var concepto = $('#search_conci_reporte_comision_tipo').val();

			$('#tabla_conci_reporte_comision tfoot').empty();
			$('#tabla_conci_reporte_comision tfoot').append("<tr>");
			$('#tabla_conci_reporte_comision tfoot tr')
				.append("<th>")
				.append("<th>")
			;

			$(meses).each(function (i, e) {
				$('#tabla_conci_reporte_comision tfoot tr')
					.append("<th></th>")
			});
			var columnas = [];

			columnas.push({
				title: "PROVEEDOR",
				data: "PROVEEDOR",
				className: "tabla_conci_reporte_comision_local_td",
				defaultContent: "---"
			});
			columnas.push({
				title: "FORMULAS",
				data: "CANAL DE VENTA",
				className: "tabla_conci_reporte_comision_cdv_td",
				defaultContent: "---"
			});
			$(meses).each(function (i, e) {
				columnas.push({
					//title: e,
					title: moment(e, "YYYY-MM").locale("es").format("MMMYY").replace(".", "-"),
					data: e,
					className: "text-right tabla_conci_reporte_comision_tfoot_meses",
					defaultContent: "-",
					render: function (data, type, row, meta) {
						if (concepto != 'CANTIDAD DE TICKETS') {
							let formattedData;
							if (concepto === "Conteo") {
								formattedData = parseInt(data, 10);
							} else {
								let formatter = new Intl.NumberFormat('en-US', {
									maximumFractionDigits: 3,
									minimumFractionDigits: 2
								});
								formattedData = formatter.format(data);
							}
							return formattedData;
						}
						return data;
					}					
				})
				;
			})
			table_dt = $('#tabla_conci_reporte_comision').DataTable
			(
				{
					
					"bDestroy": true,
					scrollX: true,
					scrollY: false,
					fixedColumns:
						{
							leftColumns: 2
						},
					lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
					paging: true,
					searching: true,
					bSort: false,
					sPaginationType: "full_numbers",
					Sorting: [[1, 'asc']],
					rowsGroup: [0, 1, 2, 3, 4, 5],
					columns: columnas,
					data: datatable_data,
					//dom: 'Blrftip',
					//sDom:"<'row'<'col-sm-12'B>><'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
					sDom: "<'row'<'col-xs-2'l><'col-xs-6'B><'col-xs-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
					buttons:
						[
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
								className: 'csvButton'
								,
								filename: $(".page-title").text().trim().replace(/ /g, '_') + "_" + moment(new Date()).format("YYYY_MM_DD_HH_mm_ss")
							},
							{
								extend: 'excelHtml5',
								text: 'Excel',
								footer: true,
								className: 'excelButton'
								,
								filename: $(".page-title").text().trim().replace(/ /g, '_') + "_" + moment(new Date()).format("YYYY_MM_DD_HH_mm_ss")
								,
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
							var total = api.column(i, {filter: 'applied'}).data().sum().toFixed(2);
							var total_pagina = api.column(i, {filter: 'applied', page: 'current'}).data().sum().toFixed(2);
							if (total < 0 && total_pagina < 0) {
								$(api.column(i).footer()).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">' + formatonumeros(total / 2) + '<span><br>');
							} else {
								$(api.column(i).footer()).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">' + formatonumeros(total / 2) + '<span><br>');
							}
						}
					},
					fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
						if (aData["CANAL DE VENTA"] == "TOTAL") {
							$('td', nRow).css('cursor', 'default', 'important');
							$('td', nRow).css('background-color', '#9BDFFD', 'important');
							$('td', nRow).css('color', '#080FFC');
							$('td', nRow).css('font-weight', '800');
						}
					},
					createdRow: function (row, data, index) {
					},
					columnDefs: [
						{
							aTargets: 'tabla_conci_reporte_comision_tfoot_meses',
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
								if (sData < "0") {
									$(nTd).css('color', 'red')
									$(nTd).css('font-weight', 'bold')
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
				}
			);

			loading();
		}

	}
	
	function conci_reporte_comision_obtener_formula() {
		let select = $("[name='search_conci_reporte_comision_formula']");

			var get_consolidado_data = {};
			get_consolidado_data.filtro = {};
			get_consolidado_data.proveedor = $("#search_conci_reporte_comision_proveedor_id").val();
			get_consolidado_data.accion = "conci_reporte_comision_formula_listar";

			var array_provincias = [];
			$.ajax({
				url: "/sys/get_conciliacion_reporte_comision.php",
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
							let opcionDefault = $('<option value="all" selected>Sin formulas</option>');
						$(select).append(opcionDefault);
					}
					if (parseInt(respuesta.http_code) == 200) {
						array_provincias.push(respuesta.result);
						var html = '<option value="all"> Todos</option>';
						for (var i = 0; i < array_provincias[0].length; i++) {
							html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
						}
						$("#search_conci_reporte_comision_formula").html(html).trigger("change");
						$("#search_conci_reporte_comision_formula").val("all").trigger("change");
						return false;
					}
					console.log(array_provincias);
				},
				error: function () { },
			});
	}

function conci_reporte_comision_obtener_proveedor() {
	let select = $("[name='search_conci_reporte_comision_proveedor_id']");
			
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
				let opcionDefault = $('<option value="" selected>Sin proveedores</option>');
				$(select).append(opcionDefault);
			}
			if (parseInt(respuesta.http_code) == 200) {
				array_provincias.push(respuesta.result);
				var html = '';
				for (var i = 0; i < array_provincias[0].length; i++) {
					html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
					}
				$(select).html(html).trigger("change");
				if (array_provincias[0].length > 0) {
					$(select).val(array_provincias[0][0].id).trigger("change"); // Selecciona el primer proveedor
					}
				return false;
			}
			console.log(array_provincias);
		},
		error: function () { },
	});
	}
			