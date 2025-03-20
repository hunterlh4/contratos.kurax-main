
$(document).ready(function () {
	if ($('#reporte_en_de_torito_l').length == 0) {
	} else {
		sec_reportes_en_de_torito();
		fnc_get_data_en_torito_list_table(); 
		$("#btn_consultar").off("click").on("click",function(){
	        
			fnc_get_data_en_torito_list_table();
	
		})

		function sec_reportes_en_de_torito() {
			console.log("sec_reportes_pagados_en_de_otras_tiendas");
			//sec_reportes_pagados_en_de_otras_tiendas_get_canales_venta_torito();
			sec_reportes_pagados_en_de_otras_tiendas_get_zonas_torito();
			sec_reportes_pagados_en_de_otras_tiendas_get_locales_torito();
			sec_reportes_pagados_en_de_otras_tiendas_get_redes_torito();

		}

	

		function fnc_get_data_en_torito_list_table() {

			var formData = new FormData();
			formData.append('fecha_inicio', $("#fecha_inicio").val());
			formData.append('fecha_fin', $("#fecha_fin").val());
			formData.append('zona', $('#zona').val());
			formData.append('local', $('#local').val());
			formData.append('canal_venta', $('#canal_venta').val());
			formData.append('red', $('#red').val());
			formData.append('action', 'list_torito_en');

			$.ajax({
				type: "POST",
				data: formData,
				url: 'app/routes/ToritoEnDe/index.php',
				contentType: false,
				processData: false,
				cache: false,
				success: function (response) {
					var jsonData = JSON.parse(response);
					if (jsonData.error == true) {


					} else {
						table_reportes_pagados_en_torito(jsonData.data);
						loading();
					}


				}, beforeSend: function () {
					loading(true);
				}
			});


		}
		function fnc_get_data_en_torito_excel() {

			var formData = new FormData();
			formData.append('fecha_inicio', $("#fecha_inicio").val());
			formData.append('fecha_fin', $("#fecha_fin").val());
			formData.append('zona', $('#zona').val());
			formData.append('local', $('#local').val());
			formData.append('canal_venta', $('#canal_venta').val());
			formData.append('red', $('#red').val());
			formData.append('action', 'excel_torito_en');

			$.ajax({
				type: "POST",
				data: formData,
				url: 'app/routes/ToritoEnDe/index.php',
				contentType: false,
				processData: false,
				cache: false,
				success: function (response) {
					var jsonData = JSON.parse(response);
					var nuevaVentanaURL = jsonData.data.path;
					window.open(nuevaVentanaURL, '_blank');
					loading();
				}, beforeSend: function () {
					loading(true);
				}
			});


		}
		function table_reportes_pagados_en_torito(data = {}) {
			var table = $('#tabla_torito_en_de').DataTable();
			table.clear();
			table.destroy(); 0
			let table_id = '#tabla_torito_en_de';
			let $table = $(table_id);
			let datatable;
			if (!$.fn.DataTable.isDataTable(table_id)) {
				datatable = $table.DataTable({
					'destroy': true,
					"data": data,
					dom: 'Bfrtip',
					lengthMenu: [
						[10, 25, 50, -1],
						['10 registros', '25 registros', '50 registros', 'Mostrar Todos']
					],
					buttons: {
						buttons: [
							{
								extend: "pageLength",
								className: 'btn-dark',
								exportOptions: {
									orthogonal: "exportcsv",
								}
							},
							{
								className: 'btn-success',
								text: '<i class="fa fa-file-pdf-o"></i>',
								action: function (e, dt, node, config) {
									fnc_get_data_en_torito_excel();
								}
							},
							// {
							// 	text: 'Generar EXCEL',
							// 	className: function (e, dt, node, config) {
									
							// 		var hidden = '';
							// 		if ($('#sec_mepa_movilidad_txt_estado').val() == "2") 
							// 		{
							// 			hidden = 'invisible';
							// 		}
							// 		return 'btn btn-danger ' + hidden;
							// 	},
							// 	action: function (e, dt, node, config) {
							// 		close_mobility_expenses($('#idCajaChicaMovilidad').val());
							// 	}
							// }
						],
						dom: {
							button: {
								className: 'btn'
							},
							buttonLiner: {
								tag: null
							}
						}
					},
					"language": {
						
						url: "/locales/Datatable/es.json"
					},
					columnDefs: [{
						className: 'text-center',
						targets: [0, 1, 2, 3, 4,5,6]
					},
					{ targets: 0, visible: true },
					{ targets: 1, orderable: false },
					{ targets: 2, orderable: false },
					{ targets: 3, orderable: false },
					{ targets: 4, orderable: false }
					],
					"columns": [
						{
							"data": "local_pago"
						},
						{
							"data": "pago_fecha"
						},						
						{
							"data": "pago_razon_social"
						},
						{
							"data": "local_venta"
						},
						{
							"data": "venta_fecha"
						},
						
						{
							"data": "venta_razon_social"
						},
						{
							"data": 'monto',
						}
					],
					"order": [[0, 'asc']],
					"drawCallback": function (settings) {
						
						var api = this.api();
						var rows = api.rows({ page: 'all' }).nodes();
						var last = null;
			
						// Remove the formatting to get integer data for summation
						var intVal = function (i) {
							return typeof i === 'string' ?
								i.replace(/[\$,]/g, '') * 1 :
								typeof i === 'number' ?
									i : 0;
						};
						total = [];
						api.column(0, { page: 'all' }).data().each(function (group, i) {
							group_assoc = group.replace(/ /g, "_");//clases de los rows							
							//console.log(group_assoc);
							if (typeof total[group_assoc] != 'undefined') {
								total[group_assoc] = total[group_assoc] + intVal(api.column(6).data()[i]); //columna a sumar
							} else {
								total[group_assoc] = intVal(api.column(6).data()[i]);
							}
							if (last !== group) {
								$(rows).eq(i).before(
									'<tr style="background-color: #ddd !important;"><td class="text-center">' + '<h4><span class="badge badge-dark">' + group + '</span></h4></td><td></td><td></td><td></td><td></td><td></td><td class=" text-center ' + group_assoc + '"></td></tr>'
								);
			
								last = group;
							}
						});
						var sumTotalMonto = 0;
						for (var key in total) {
							$("." + key).html('<h4><span class="badge badge-primary">' + "S/." + total[key].toFixed(2) + '</span></h4>');
							//console.log(total);
							//console.log(total[key]);
							sumTotalMonto += total[key];
						}
			
						$("#idTotalMontoTorito").html(sumTotalMonto.toFixed(2));
					},
					initComplete: function () {
						this.api().columns().every( function () {
							var column = this;
							var select = $('<select><option value="">--Seleccione--</option></select>')
								.appendTo( $(column.footer()).empty() )
								.on( 'change', function () {
									var val = $.fn.dataTable.util.escapeRegex(
										$(this).val()
									);
			 
									column
										.search( val ? '^'+val+'$' : '', true, false )
										.draw();
								} );
			 
							column.data().unique().sort().each( function ( d, j ) {
								select.append( '<option value="'+d+'">'+d+'</option>' )
							} );
						} );
					}
			
			
				});
				
				$('#tabla_torito_en_de tbody').off('click');
			} else {
				datatable = new $.fn.dataTable.Api(table_id);
				datatable.clear();
				datatable.rows.add(data);
				datatable.draw();
			}
			return datatable;
		}







		function sec_reportes_pagados_en_de_otras_tiendas_get_redes_torito() {
			var data = {};
			data.what = {};
			data.what[0] = "id";
			data.what[1] = "nombre";
			data.where = "redes";
			data.filtro = {}
			$.ajax({
				data: data,
				type: "POST",
				dataType: "json",
				url: "/api/?json",
				async: "false"
			})
				.done(function (data, textStatus, jqXHR) {
					try {
						if (console && console.log) {
							$.each(data.data, function (index, val) {
								var new_option = $("<option>");
								$(new_option).val(val.id);
								$(new_option).html(val.nombre);
								$(".red_reporte_pagados_de_otras_tiendas_torito").append(new_option);
							});
							$('.red_reporte_pagados_de_otras_tiendas_torito').select2({ closeOnSelect: false });
						}
					} catch (err) {
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
					if (console && console.log) {
						console.log("La solicitud locales a fallado: " + textStatus);
					}
				})
		}
		function sec_reportes_pagados_en_de_otras_tiendas_get_canales_venta_torito() {
			var data = {};
			data.what = {};
			data.what[0] = "id";
			data.what[1] = "codigo";
			data.where = "canales_de_venta";
			data.filtro = {}
			auditoria_send({ "proceso": "sec_reportes_pagados_de_otras_tiendas_get_canales_venta", "data": data });
			$.ajax({
				data: data,
				type: "POST",
				dataType: "json",
				url: "/api/?json",
			})
				.done(function (data, textStatus, jqXHR) {
					try {
						if (console && console.log) {
							$.each(data.data, function (index, val) {
								canales_de_venta[val.id] = val.codigo;
								var new_option = $("<option>");
								$(new_option).val(val.id);
								$(new_option).html(val.codigo);
								$(".canal_venta_reporte_pagados_de_otras_tiendas_torito").append(new_option);

							});
							$('.canal_venta_reporte_pagados_de_otras_tiendas_torito').select2({ closeOnSelect: false });
							/*	$('.canal_venta_reporte_pagados_de_otras_tiendas').on("change",function(val){
									if($.inArray("30",$('#canal_venta').val())){
										 $('#canal_venta').val(30).trigger("change");
									}
								})*/
							/*  $('.canal_venta_reporte_pagados_de_otras_tiendas').on('select2:selecting', function(e) {
								console.log('Selecting: ' , e.params.args.data);
								if(e.params.args.data.text=="Bingo"){
									 $('#canal_venta').val(30).trigger("change");
								}
								else{
									if($.inArray("30",$('#canal_venta').val())>-1){
									   $("#canal_venta").find("option[value='30']").prop("selected",false);
										$("#canal_venta").trigger("change");
									}
		
								}
		
							  });*/
						}
					} catch (err) {
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
					if (console && console.log) {
						console.log("La solicitud canales de ventas a fallado: " + textStatus);
					}
				})
		}
		function sec_reportes_pagados_en_de_otras_tiendas_get_locales_torito() {
			var data = {};
			data.what = {};
			data.what[0] = "id";
			data.what[1] = "nombre";
			data.where = "locales";
			data.filtro = {}
			auditoria_send({ "proceso": "sec_reportes_pagados_de_otras_tiendas_get_locales", "data": data });
			$.ajax({
				data: data,
				type: "POST",
				dataType: "json",
				url: "/api/?json",
			})
				.done(function (data, textStatus, jqXHR) {
					try {
						if (console && console.log) {
							$.each(data.data, function (index, val) {
								var new_option = $("<option>");
								$(new_option).val(val.id);
								$(new_option).html(val.nombre);
								$(".local_reporte_pagados_de_otras_tiendas_torito").append(new_option);
							});
							$('.local_reporte_pagados_de_otras_tiendas_torito').select2({ closeOnSelect: false });
						}
					} catch (err) {
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
					if (console && console.log) {
						console.log("La solicitud locales a fallado: " + textStatus);
					}
				})
		}
		function sec_reportes_pagados_en_de_otras_tiendas_get_zonas_torito() {
			var data = {};
			data.what = {};
			data.what[0] = "id";
			data.what[1] = "nombre";
			data.where = "zonas";
			data.filtro = {}
			$.ajax({
				data: data,
				type: "POST",
				dataType: "json",
				url: "/api/?json",
				async: "false"
			})
				.done(function (data, textStatus, jqXHR) {
					try {
						if (console && console.log) {
							$.each(data.data, function (index, val) {
								var new_option = $("<option>");
								$(new_option).val(val.id);
								$(new_option).html(val.nombre);
								$(".zona_reporte_pagados_de_otras_tiendas_torito").append(new_option);
							});
							$('.zona_reporte_pagados_de_otras_tiendas_torito').select2({ closeOnSelect: false });
						}
					} catch (err) {
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
					if (console && console.log) {
						console.log("La solicitud locales a fallado: " + textStatus);
					}
				})
		}




	}
});
