var at_consolidado_gastos_filtro_locales = false;
var at_consolidado_gastos_filtro_canales_de_venta = false;
$(document).ready(function () {
	sec_consolidado_gastos()
})
function sec_consolidado_gastos() {
		loading(true);
		sec_consolidado_gastos_events();
		sec_consolidado_gastos_obtener_conceptos();
		sec_consolidado_gastos_obtener_zonas();
		sec_consolidado_gastos_obtener_supervisor_por_zona();
		sec_consolidado_gastos_settings();
		loading();

		function sec_consolidado_gastos_settings() {
			$('.sec_consolidado_gastos_select2').select2({
				closeOnSelect: false,
				allowClear: false,
			});
			
			$('.sec_consolidado_gastos_zona').select2({
				closeOnSelect: false,
				allowClear: false
			});
			
			}
		function sec_consolidado_gastos_events() {
			$(".btnfiltarconsolidadoGastos")
				.off("click")
				.on("click", function () {
					loading(true);
					sec_consolidado_gastos_get_liquidaciones();
				});
			}

		function sec_consolidado_gastos_get_liquidaciones() {
			loading(true);
			var get_liquidaciones_data = {};
			get_liquidaciones_data.filtro = {};
			get_liquidaciones_data.filtro.zona_id = $("#search_id_consolidado_zona").val();
			get_liquidaciones_data.filtro.locales = $("#search_id_consolidado_locales").val();
			get_liquidaciones_data.filtro.canales_de_venta = $('#sec_consolidado_gastos_cdv').val();
			get_liquidaciones_data.filtro.supervisores = $('#search_id_consolidado_supervisor').val();
			get_liquidaciones_data.filtro.estado_locales = "activos";

			get_liquidaciones_data.accion = "consolidado_gastos";

			$.ajax({
				data: get_liquidaciones_data,
				type: "POST",
				url: "/sys/get_consolidado_gastos.php",
				async: "false"
			})
				.done(function (responsedata, textStatus, jqXHR) {
					try {
						var response = jQuery.parseJSON(responsedata);
						console.log(response)
						var obj = response.data;
						sec_consolidado_gastos_mostrar_datatable(obj);
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

		function sec_consolidado_gastos_mostrar_datatable(obj) {
			var datatable_data = obj.datatable_data;
			var meses = obj.meses;
			var totales = obj.totales;
			//var concepto = $('#sec_consolidado_gastos_concepto').val();
			var concepto = "GASTOS";

			$('#tabla_sec_consolidado_gastos_provincia tfoot').empty();
			$('#tabla_sec_consolidado_gastos_provincia tfoot').append("<tr>");
			$('#tabla_sec_consolidado_gastos_provincia tfoot tr')
				.append("<th>")
				.append("<th>")
				.append("<th>")
				.append("<th>")
				.append("<th>")
				.append("<th>")
				.append("<th>")
				;

			$(meses).each(function (i, e) {
				$('#tabla_sec_consolidado_gastos_provincia tfoot tr')
					.append("<th></th>")
			});
			var columnas = [];
			columnas.push({ title: "ZONA", data: "ZONA", className: "tabla_sec_consolidado_gastos_local_td", defaultContent: "---" });
			columnas.push({ title: "DEPARTAMENTO", data: "DEPARTAMENTO", className: "tabla_sec_consolidado_gastos_local_td", defaultContent: "---" });
			columnas.push({ title: "PROVINCIA", data: "PROVINCIA", className: "tabla_sec_consolidado_gastos_local_td", defaultContent: "---" });
			columnas.push({ title: "DISTRITO", data: "DISTRITO", className: "tabla_sec_consolidado_gastos_local_td", defaultContent: "---" });
			columnas.push({ title: "NOMBRE SOP", data: "NOMBRE SOP", className: "tabla_sec_consolidado_gastos_local_td", defaultContent: "---" });
			columnas.push({ title: "NOMBRE TIENDA", data: "NOMBRE TIENDA", className: "tabla_sec_consolidado_gastos_local_td", defaultContent: "---" });
			columnas.push({ title: "CONCEPTOS DE GASTOS  ", data: "CANAL DE VENTA", className: "tabla_sec_consolidado_gastos_cdv_td", defaultContent: "---" });
			$(meses).each(function (i, e) {
				columnas.push({
					//title: e,
					title: moment(e, "YYYY-MM").locale("es").format("MMMYY").replace(".", "-"),
					data: e,
					className: "text-right tabla_sec_consolidado_gastos_tfoot_meses",
					defaultContent: "-",
					render: function (data, type, row, meta) {
						if(concepto != 'CANTIDAD DE TICKETS'){
							let formatter = new Intl.NumberFormat('en-US', {
								maximumFractionDigits: 3,
								minimumFractionDigits: 2
							});
							return formatter.format(data);
						} 
						return data;
					}
				})
					;
			})
			table_dt = $('#tabla_sec_consolidado_gastos_provincia').DataTable
				(
					{					
						"bDestroy": true,
						scrollX: true,
						scrollY: false,
						fixedColumns:
						{
							leftColumns: 7
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
									, filename: $(".page-title").text().trim().replace(/ /g, '_') + "_" + moment(new Date()).format("YYYY_MM_DD_HH_mm_ss")
								},
								{
									extend: 'excelHtml5',
									text: 'Excel',
									footer: true,
									className: 'excelButton'
									, filename: $(".page-title").text().trim().replace(/ /g, '_') + "_" + moment(new Date()).format("YYYY_MM_DD_HH_mm_ss")
									, customize: function (xlsx) {
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
							for (var i = 7; i < columnas.length; i++) {
								var total = api.column(i, { filter: 'applied' }).data().sum().toFixed(2);
								var total_pagina = api.column(i, { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
								if (total < 0 && total_pagina < 0) {
									$(api.column(i).footer()).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">' + formatonumeros(total / 2) + '<span><br>');
								}
								else {
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
								aTargets: 'tabla_sec_consolidado_gastos_tfoot_meses',
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

		$("#sec_consolidado_gastos_cargar_datos").on('click', function (event) {
			$('#modalCargarDataConsolidadoGastos').modal('show');

			});

		$("#Frm_ImportarConsolidadoGastos").submit(function (e) {
			e.preventDefault();
			validar_excel_consolidado_gastos();
			});
			
	//------------- Funciones
	function validar_excel_consolidado_gastos() {
				var inputFile = document.getElementById('fileInput');
				var fileName = inputFile.value;

				// Verificar si el campo de archivo está vacío
				if (fileName === '') {
					swal({
						icon: 'error',
						title: 'Error',
						text: 'Por favor, selecciona un archivo.'
					});					
					return false;
				}

				// Obtener la extensión del archivo
				var fileExtension = fileName.split('.').pop().toLowerCase();

				// Verificar si la extensión es válida (xls o xlsx)
				if (fileExtension !== 'xls' && fileExtension !== 'xlsx') {
					swal({
						icon: 'error',
						title: 'Error',
						text: 'Por favor, selecciona un archivo Excel válido (xls o xlsx).'
					});
					return false;
				}else{
					var title = "¿Está seguro de subir el consolidado de gastos?";
						
					swal({
							title: title,
							type: "warning",
							showCancelButton: true,
							confirmButtonColor: '#3085d6',
							confirmButtonText: "Sí, estoy de acuerdo",
							cancelButtonText: "No, cancelar",
							closeOnConfirm: true,
							closeOnCancel: true,
						}, function (isConfirm) {
							if (isConfirm) {
								cargar_data_consolidado_gastos();
							}
					});
				}
		}

	function cargar_data_consolidado_gastos() {
					var fileInput = document.getElementById('fileInput');
					var file = fileInput.files[0];
				
					if (file) {
						var dataForm = new FormData($("#Frm_ImportarConsolidadoGastos")[0]);
						dataForm.append('excelFile', file);
						dataForm.append("accion", "cargar_consolidado_gastos");
				
						console.log(dataForm);
						console.log(1);
				
						$.ajax({
							url: "/sys/set_consolidado_gastos.php",
							type: 'POST',
							data: dataForm,
							cache: false,
							contentType: false,
							processData: false,
							beforeSend: function (xhr) {
								loading(true);
							},
							success: function (resp) {
								var respuesta = JSON.parse(resp);
								console.log(respuesta);
				
								if (parseInt(respuesta.http_code) === 200) {
									swal({
										title: "Registro exitoso",
										text: "Se guardó correctamente",
										html: true,
										type: "success",
										closeOnConfirm: false
									});
				
									location.reload();
								} else {
									swal({
										title: "Error al guardar",
										text: "Hubo un problema al guardar los datos. Detalles: " + respuesta.error,
										html: true,
										type: "error",
										closeOnConfirm: false
									});
				
								}
							},
							complete: function () {
								loading(false);
							}
						});
					}
		}
				
	function sec_consolidado_gastos_obtener_conceptos() {
		let select = $("[name='search_id_consolidado_conceptos']");
		let valorSeleccionado = $("#area_id").val();
	
		$.ajax({
			url: "/sys/get_consolidado_gastos.php",
			type: "POST",
			data: {
				accion: "consolidado_gastos_obtener_conceptos"
			},
			success: function (datos) {
				var respuesta = JSON.parse(datos);
				$(select).empty();
				if (!valorSeleccionado) {
					let opcionDefault = $('<option value="all">Todos</option>');
					$(select).append(opcionDefault);
				}
				$(respuesta.result).each(function (i, e) {
					let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$(select).append(opcion);
				});
	
				if (valorSeleccionado != null) {
					$(select).val(valorSeleccionado);
				} else {
					$(select).prop('selectedIndex', 0);
				}
			},
			error: function () {
				// Manejar el error si es necesario
			}
		});
		}

	}

	function sec_consolidado_gastos_obtener_zonas() {
		let select = $("[name='search_id_consolidado_zona']");
		let valorSeleccionado = $("#search_id_consolidado_zona").val();
		$.ajax({
			url: "/sys/get_consolidado_gastos.php",
			type: "POST",
			data: {
				accion: "consolidado_gastos_obtener_zonas"
				},
			complete: function () {
				loading();
				},
			success: function (datos) {
				var respuesta = JSON.parse(datos);
				$(select).empty();
				
				if (respuesta.result.length > 1) {
					let opcionTodos = $('<option value="all" selected>Todos</option>');
					$(select).append(opcionTodos);
				}
				$(respuesta.result).each(function (i, e) {
					let opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$(select).append(opcion);
					});
		
				if (valorSeleccionado != null) {
					$(select).val(valorSeleccionado);
					} 
				else {
					$(select).prop('selectedIndex', 0);
					}
				},
			error: function () {
				}
			});
		}
	
	function sec_consolidado_gastos_obtener_supervisor_por_zona() {
			let select = $("[name='search_id_consolidado_supervisor']");

			var get_zonas_data = {};
			get_zonas_data.filtro = {};
			get_zonas_data.filtro.zona_id = $("#search_id_consolidado_zona").val();
			get_zonas_data.accion = "consolidado_gastos_obtener_supervisores";

			var array_supervisor = [];
			$.ajax({
				url: "/sys/get_consolidado_gastos.php",
				type: "POST",
				data: get_zonas_data,
				beforeSend: function () {
					loading("true");
				},
				complete: function () {
					loading();
					sec_consolidado_gastos_obtener_locales_por_supervisor();
				},
				success: function (resp) {
					var respuesta = JSON.parse(resp);
					if (parseInt(respuesta.http_code) == 400) { 
						$(select).empty();
        					let opcionDefault = $('<option value="all" selected>Sin supervisores</option>');
						$(select).append(opcionDefault);
					}
					if (parseInt(respuesta.http_code) == 200) {
						array_supervisor.push(respuesta.result);
						var html = '<option value="all"> Todos </option>';
						for (var i = 0; i < array_supervisor[0].length; i++) {
							html += "<option value=" + array_supervisor[0][i].id + ">" + array_supervisor[0][i].supervisor_nombre + "</option>";
						}
						$("#search_id_consolidado_supervisor").html(html).trigger("change");
	
						$("#search_id_consolidado_supervisor").val("all").trigger("change");
	
						return false;
					}
				},
				error: function () { },
			});
		//});
	}
	
	function sec_consolidado_gastos_obtener_locales_por_supervisor() {
		let select = $("[name='search_id_consolidado_locales']");

			var get_consolidado_data = {};
			get_consolidado_data.filtro = {};
			get_consolidado_data.filtro.supervisores = $("#search_id_consolidado_supervisor").val();
			get_consolidado_data.filtro.zona_id = $("#search_id_consolidado_zona").val();
			get_consolidado_data.accion = "consolidado_gastos_obtener_locales_por_supervisor";

			var array_provincias = [];
			$.ajax({
				url: "/sys/get_consolidado_gastos.php",
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
							let opcionDefault = $('<option value="all" selected>Sin locales</option>');
						$(select).append(opcionDefault);
					}
					if (parseInt(respuesta.http_code) == 200) {
						array_provincias.push(respuesta.result);
						var html = '<option value="all"> Todos</option>';
						for (var i = 0; i < array_provincias[0].length; i++) {
							html += "<option value=" + array_provincias[0][i].id + ">" + array_provincias[0][i].nombre + "</option>";
						}
						$("#search_id_consolidado_locales").html(html).trigger("change");
						$("#search_id_consolidado_locales").val("all").trigger("change");
						return false;
					}
					console.log(array_provincias);
				},
				error: function () { },
			});
	}
	
function sec_consolidado_gastos_exportar_formato()
	{	
		var data = {
			"accion": "exportar_consolidado_gastos"
			}

		$.ajax({
			url: "/sys/get_consolidado_gastos.php",
			type: 'POST',
			data: data,
			beforeSend: function() {
				loading("true");
				},
			complete: function() {
				loading();
				},
			success: function(resp) {	
				let obj = JSON.parse(resp);
				if(parseInt(obj.estado_archivo) == 1)
					{
					window.open(obj.ruta_archivo);
					loading(false);
					}
				else if(parseInt(obj.estado_archivo) == 0)
					{
					swal({
						title: "Error al Generar el detalle de locales",
						text: obj.ruta_archivo,
						html:true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
						});
					return false;
					}
				else
					{
					swal({
						title: "Error",
						text: "Ponerse en contacto con Soporte",
						html:true,
						type: "warning",
						closeOnConfirm: false,
						showCancelButton: false
						});
					return false;
					}
				},
				error: function(resp, status) {

				}
			});
		}
		
		
	