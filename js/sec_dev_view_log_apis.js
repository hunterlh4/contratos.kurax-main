function sec_dev_view_log_apis() {
	if (sec_id == 'dev' && sub_sec_id=='view_log_apis') {
 
		SecDevView_buscar_list_x_fec(0);

		$('#SecDevView_fecha_inicio_log').datetimepicker({
			format: 'YYYY-MM-DD HH:mm:ss'
		});
		$('#SecDevView_fecha_fin_log').datetimepicker({
			format: 'YYYY-MM-DD HH:mm:ss' 
		});

		$('#SecDevView_fecha_inicio').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecDevView_fecha_fin').datetimepicker({
			format: 'YYYY-MM-DD' 
		});

		$('#SecDevView_fecha_inicio').val($('#g_fecha_hace_15_dias').val());
		$('#SecDevView_fecha_fin').val($('#g_fecha_actual').val());

		$('#SecDevView_fecha_inicio_log').val($('#g_fecha_actual_log').val());
		$('#SecDevView_fecha_fin_log').val($('#g_fecha_actual_log').val());

		//BUSCADOR PROVEEDOR

		$('#SecDevView_proveedor_log').autocomplete({
            source: '/sys/get_dev_view_log_apis.php?accion=SecDevView_listar_proveedor_log',
            minLength: 1,
            select: function (event, ui)
            {
                gen_proveedor_seleccionado=ui.item.codigo;
                if(gen_proveedor_seleccionado == undefined){
                    gen_proveedor_seleccionado = 0;
                }

            }
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            $('ul.ui-menu').css('display', 'block !important');
            $('ul.ui-menu').css('z-index', '100000');
            return $( "<li>" )
            .append( item.label)
            .appendTo( ul );
        };

		var gen_proveedor_seleccionado = 0; 

		//BUSCADOR CLIENTE

		$('#SecDevView_cliente_log').autocomplete({
            source: '/sys/get_dev_view_log_apis.php?accion=SecDevView_listar_clientes_log',
            minLength: 2,
            select: function (event, ui)
            {
                gen_cliente_seleccionado=ui.item.codigo;
                if(gen_cliente_seleccionado == undefined){
                    gen_cliente_seleccionado = 0;
                }

            }
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            $('ul.ui-menu').css('display', 'block !important');
            $('ul.ui-menu').css('z-index', '100000');
            return $( "<li>" )
            .append( item.label)
            .appendTo( ul );
        };

		var gen_cliente_seleccionado = 0; 

		//BUSCADOR USUARIO

		$('#SecDevView_usuario_log').autocomplete({
            source: '/sys/get_dev_view_log_apis.php?accion=SecDevView_listar_usuario_log',
            minLength: 2,
            select: function (event, ui)
            {
                gen_usuario_seleccionado=ui.item.codigo;
                if(gen_usuario_seleccionado == undefined){
                    gen_usuario_seleccionado = 0;
                }

            }
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            $('ul.ui-menu').css('display', 'block !important');
            $('ul.ui-menu').css('z-index', '100000');
            return $( "<li>" )
            .append( item.label)
            .appendTo( ul );
        };

		var gen_usuario_seleccionado = 0; 

	 
		$('#SecDevView_fecha_inicio_dl').change(function () {
			var var_fecha_change = $('#SecDevView_fecha_inicio_dl').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecDevView_fecha_inicio_dl").val($("#g_fecha_actual").val());
			}
		});
		$('#SecDevView_fecha_fin_dl').change(function () {
			var var_fecha_change = $('#SecDevView_fecha_fin_dl').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecDevView_fecha_fin_dl").val($("#g_fecha_actual").val());
			}
		});

		$('#SecDevView_btn_buscar_list').click(function() {
			SecDevView_buscar_list_x_fec(1);
		});

		$('#SecDevView_btn_buscar_x_dia').click(function() {

			SecDevView_buscar_list(gen_proveedor_seleccionado, gen_cliente_seleccionado, gen_usuario_seleccionado);
		});

		$("#SecDevView_btn_exportar_dl").on('click', function () {
			SecDevView_exportar_excel_dl(gen_proveedor_seleccionado, gen_cliente_seleccionado, gen_usuario_seleccionado);
		});
 
	}
}

function SecDevView_buscar_list_x_fec(param){

	if(parseInt(param) == 0){
		var fec_inicio = $('#g_fecha_hace_15_dias').val();
		var fec_fin = $('#g_fecha_actual').val();

	}else{
		var fec_inicio = $('#SecDevView_fecha_inicio').val();
		var fec_fin = $('#SecDevView_fecha_fin').val();
	}

	var fechaInicio = new Date(fec_inicio).getTime();
	var fechaFin    = new Date(fec_fin).getTime();
	var diff = fechaFin - fechaInicio;
	var cant_dias=diff/(1000*60*60*24);

	if(cant_dias>30){

		swal('Aviso', 'No puede consultar más de 30 días.', 'warning');

	}else{

		var data = {
			"accion": "listar_log_apis_x_fec",
			"fec_inicio": fec_inicio,
			"fec_fin": fec_fin
		}


		$.ajax({
			url: "/sys/get_dev_view_log_apis.php",
			type: 'POST',
			data: data,
			beforeSend: function () {
				loading("true");
			},
			complete: function () {
				loading();
			},
			
			success: function (resp) {  
				var respuesta = JSON.parse(resp);
				auditoria_send({"proceso": "listar_log_apis_x_fec", "data": respuesta});
				
				if (parseInt(respuesta.http_code) == 400) {
					swal('Aviso', respuesta.status, 'warning');
					
					$('#sec_dev_view_log_x_fec tbody').html('');
					$('#sec_dev_view_log_x_fec tbody').append(
						'<tr>' +
						'<td class="text-center" colspan="8">No hay registros.</td>' +
						'</tr>'
					);
					return false;
				}
				if (parseInt(respuesta.http_code) == 200) {
	
					var ftable = $('#sec_dev_view_log_x_fec').DataTable();
					ftable.clear();
					ftable.destroy();
					var ftable = $('#sec_dev_view_log_x_fec').DataTable({
						'destroy': true,
						'scrollX': true,
						"processing": true,
						"serverSide": true,
						"order" : [],
						"ajax": {
							type: "POST",
							async : true,
							"url": "/sys/get_dev_view_log_apis.php",
							"data": data
						},
						"order": [],
						"language": {
							"processing": "Procesando...",
							"lengthMenu": "Mostrar _MENU_ registros",
							"zeroRecords": "No se encontraron resultados",
							"emptyTable": "Ningún dato disponible en esta tabla",
							"info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
							"infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
							"infoFiltered": "(filtrado de un total de _MAX_ registros)",
							"infoPostFix": "",
							"search": "Buscar: ",
							"url": "",
							"infoThousands": ",",
							"loadingRecords": "Cargando...",
							"paginate": {
								"first": "Primero",
								"last": "Último",
								"next": "Siguiente",
								"previous": "Anterior"
							},
							"aria": {
								"sortAscending": ": Activar para ordenar la columna de manera ascendente",
								"sortDescending": ": Activar para ordenar la columna de manera descendente"
							}
						},  
						"columns": [
							{
								"data": null,
								"sortable": false,
								createdCell: function (td, cellData, item, row, col) {
									$(td).addClass('text-center');
								},
								render: function (data, type, row, meta) {
									return meta.row + meta.settings._iDisplayStart + 1;
								}
							},
							{ 
								"data": "created_at",
								createdCell: function (td, cellData, item, row, col) {
									$(td).addClass('text-center');
									
								},
								render: function (data, type,item,row) {
									var td_created_at = '';
									var td_created_at = `<span class="badge bg-info text-white">${item.created_at}</span>`;
									return td_created_at;
								}
							},
							{ 
								"data": "total_error",
								createdCell: function (td, cellData, item, row, col) {
									$(td).addClass('text-center');
								}
							},
							{ 
								"data": "total_sucess",
								createdCell: function (td, cellData, item, row, col) {
									$(td).addClass('text-center');
								}
							},
							{ 
								"data": "total",
								createdCell: function (td, cellData, item, row, col) {
									$(td).addClass('text-center');
								}
							},
							
							
							
							{ 
								"data": "total",
								createdCell: function (td, cellData, item, row, col) {
									$(td).addClass('text-center');
								},
								render: function (data, type,item,row) {
									var ver = '';
								
									var ver = `<button type="button" class="btn btn-sm btn-info" title="Ver registros del dia" onclick="SecDevView_buscar_list_dia('${item.created_at}')"><span class="fa fa-eye"></span></button> <button class="btn btn-sm btn-success" title="Descargar" onclick="SecDevView_excel_list_dia('${item.created_at}')" id="SecDevView_excel_list_dia">
									<span class="fa fa-download"></span>
									</button>`;
									return ver;
								}
							}
						]
					});
				
					return false;
				}
			return false;
			},
			error: function (result) {
				auditoria_send({"proceso": "listar_log_apis_x_fec_error", "data": result});
				return false;
			}
		});
	}
}


function SecDevView_buscar_list_dia(dia){

	$('#SecDevView_fecha_inicio_log').val(dia+' 00:00:00');
	$('#SecDevView_fecha_fin_log').val(dia+' 23:59:59');

	var fec_inicio = $('#SecDevView_fecha_inicio_log').val();
	var fec_fin = $('#SecDevView_fecha_fin_log').val();
 
	var data = {
        "accion": "listar_log_apis_x_dia",
		"fec_inicio": fec_inicio,
		"fec_fin": fec_fin
    }

	$("#dev_view_log_div_tabla").css("display","block");
	$("#SecDevView_div_btn_return").css("display","block");
	$("#SecDevView_filtros_x_dia").css("display","block");
	$("#SecDevView_filtros_x_dia2").css("display","block");
	$("#SecDevView_filtros_x_dia3").css("display","block");

	$("#dev_view_log_x_fec_div_tabla").css("display","none");
	$("#SecDevView_filtros_x_fec").css("display","none");

	var ftable = $('#sec_dev_view_log_apis').DataTable();
	ftable.clear();
	ftable.destroy();
	var ftable = $('#sec_dev_view_log_apis').DataTable({
		'destroy': true,
		'scrollX': true,
		"processing": true,
		"serverSide": true,
		"order" : [],
		"ajax": {
			type: "POST",
			async : true,
			"url": "/sys/get_dev_view_log_apis.php",
			"data": data
		},
		"order": [],
		"language": {
			"processing": "Procesando...",
			"lengthMenu": "Mostrar _MENU_ registros",
			"zeroRecords": "No se encontraron resultados",
			"emptyTable": "Ningún dato disponible en esta tabla",
			"info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
			"infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
			"infoFiltered": "(filtrado de un total de _MAX_ registros)",
			"infoPostFix": "",
			"search": "Buscar: ",
			"url": "",
			"infoThousands": ",",
			"loadingRecords": "Cargando...",
			"paginate": {
				"first": "Primero",
				"last": "Último",
				"next": "Siguiente",
				"previous": "Anterior"
			},
			"aria": {
				"sortAscending": ": Activar para ordenar la columna de manera ascendente",
				"sortDescending": ": Activar para ordenar la columna de manera descendente"
			}
		},  
		"columns": [
			{
				"data": null,
				"sortable": false,
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				},
				render: function (data, type, row, meta) {
					return meta.row + meta.settings._iDisplayStart + 1;
				}
			},
			{ 
				"data": "created_at",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
					 
				},
				render: function (data, type,item,row) {
					var td_created_at = '';
					var td_created_at = `<span style="background: #6c757d;display: inline-block;padding: 0.25em 0.4em;font-weight: bold;line-height: 1;color: #fff;text-align: center;white-space: nowrap;vertical-align: baseline;border-radius: 0.25rem;">${item.created_at}</span>`;
					return td_created_at;
				}
			},
			{ 
				"data": "proveedor",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				},
				render: function (data, type,item,row) {
					var td_proveedor = '';
					var td_proveedor = `<span style="background: #6A1B9A;display: inline-block;padding: 0.25em 0.4em;font-weight: bold;line-height: 1;color: #fff;text-align: center;white-space: nowrap;vertical-align: baseline;border-radius: 0.25rem;">${item.proveedor}</span>`;
					return td_proveedor;
				}
			},
			{ 
				"data": "method",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				},
				render: function (data, type,item,row) {
					var td_method = '';
					var td_method = `<span class="badge bg-info text-white">${item.method}</span>`;
					return td_method;
				}
			},
			{ 
				"data": "bet_id",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				}
			},
			{ 
				"data": "cliente",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-right');
				}
			},
			{ 
				"data": "body",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				},
				render: function (data, type,item,row) {
					var td_body = '';
					if (item.body === '') {
						td_body = '';
					}else{
						td_body = `${item.body} <button type="button" style="float: right!important; padding: 5px 5px!important;" title="Ver detalle" class="btn btn-sm btn-primary pull-left" onclick="SecDevView_modal_detalle('${item.id}', 0)"><span class="fa fa-eye"></span></button>`;
					}					 
					return td_body;
				}
			},
			{ 
				"data": "response",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				},
				render: function (data, type,item,row) {
					var td_response = '';
					if (item.response === '') {
						td_response = '';
					}else{
						td_response = `${item.response} <button type="button" style="float: right!important; padding: 5px 5px!important;" title="Ver detalle" class="btn btn-sm btn-primary pull-left" onclick="SecDevView_modal_detalle('${item.id}', 1)"><span class="fa fa-eye"></span></button>`;
					}
					return td_response;
				}
			},
			{ 
				"data": "hash",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				}
			},
			{ 
				"data": "turno_id",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				}
			},
			{ 
				"data": "cc_id",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				}
			},
			{ 
				"data": "status",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				}
			},
			{ 
				"data": "usuario",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				},
				render: function (data, type,item,row) {
					var td_usuario = '';
					var td_usuario = `<span style="background: orange;display: inline-block;padding: 0.25em 0.4em;font-weight: bold;line-height: 1;color: #fff;text-align: center;white-space: nowrap;vertical-align: baseline;border-radius: 0.25rem;">${item.usuario}</span>`;
					return td_usuario;
				}
			},
			{ 
				"data": "updated_at",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				}
			},
			 
			{ 
				"data": "updated_at",
				createdCell: function (td, cellData, item, row, col) {
					$(td).addClass('text-center');
				},
				render: function (data, type,item,row) {
					var ver = '';
				  
					var ver = `<button class="btn btn-sm btn-success" title="Descargar" onclick="SecDevView_excel_registro('${item.id}','${item.created_at}')" id="SecDevView_excel_registro">
					<span class="fa fa-download"></span>
					</button>`;
					return ver;
				}
			}
		]
	});	 

}


function SecDevView_buscar_list(gen_proveedor_seleccionado, gen_cliente_seleccionado, gen_usuario_seleccionado){

	var fec_inicio = $('#SecDevView_fecha_inicio_log').val();
	var fec_fin = $('#SecDevView_fecha_fin_log').val();
	var proveedor = $('#SecDevView_proveedor_log').val()!= '' ? gen_proveedor_seleccionado : '0'; 
	var method = $('#SecDevView_method_log').val();
	var cliente = $('#SecDevView_cliente_log').val()!= '' ? gen_cliente_seleccionado : '0'; 
	var betid = $('#SecDevView_betid_log').val();
	var usuario = $('#SecDevView_usuario_log').val()!= '' ? gen_usuario_seleccionado : '0'; 

	var data = {
        "accion": "listar_log_apis_x_dia_filtros",
        "fec_inicio": fec_inicio,
		"fec_fin": fec_fin,
		"proveedor": proveedor,
		"method": method,
		"cliente": cliente,
		"betid": betid,
		"usuario": usuario
    }

	$.ajax({
		url: "/sys/get_dev_view_log_apis.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		
		success: function (resp) {  
			var respuesta = JSON.parse(resp);
			auditoria_send({"proceso": "listar_log_apis_x_dia_filtros", "data": respuesta});
			 
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.status, 'warning');
				 
                $('#sec_dev_view_log_apis tbody').html('');
				$('#sec_dev_view_log_apis tbody').append(
					'<tr>' +
					'<td class="text-center" colspan="15">No hay registros.</td>' +
					'</tr>'
				);
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {

				$("#dev_view_log_div_tabla").css("display","block");
				$("#SecDevView_div_btn_return").css("display","block");
				$("#SecDevView_filtros_x_dia").css("display","block");
				$("#SecDevView_filtros_x_dia2").css("display","block");
				$("#SecDevView_filtros_x_dia3").css("display","block");

				$("#dev_view_log_x_fec_div_tabla").css("display","none");
				$("#SecDevView_filtros_x_fec").css("display","none");

				var ftable = $('#sec_dev_view_log_apis').DataTable();
				ftable.clear();
				ftable.destroy();
				var ftable = $('#sec_dev_view_log_apis').DataTable({
					'destroy': true,
					'scrollX': true,
					"processing": true,
					"serverSide": true,
					"order" : [],
					"ajax": {
						type: "POST",
						async : true,
						"url": "/sys/get_dev_view_log_apis.php",
						"data": data
					},
					"order": [],
					"language": {
						"processing": "Procesando...",
						"lengthMenu": "Mostrar _MENU_ registros",
						"zeroRecords": "No se encontraron resultados",
						"emptyTable": "Ningún dato disponible en esta tabla",
						"info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
						"infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
						"infoFiltered": "(filtrado de un total de _MAX_ registros)",
						"infoPostFix": "",
						"search": "Buscar: ",
						"url": "",
						"infoThousands": ",",
						"loadingRecords": "Cargando...",
						"paginate": {
							"first": "Primero",
							"last": "Último",
							"next": "Siguiente",
							"previous": "Anterior"
						},
						"aria": {
							"sortAscending": ": Activar para ordenar la columna de manera ascendente",
							"sortDescending": ": Activar para ordenar la columna de manera descendente"
						}
					},  
					"columns": [
						{
							"data": null,
							"sortable": false,
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							},
							render: function (data, type, row, meta) {
								return meta.row + meta.settings._iDisplayStart + 1;
							}
						},
						{ 
							"data": "created_at",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
								
							},
							render: function (data, type,item,row) {
								var td_created_at = '';
								var td_created_at = `<span style="background: #6c757d;display: inline-block;padding: 0.25em 0.4em;font-weight: bold;line-height: 1;color: #fff;text-align: center;white-space: nowrap;vertical-align: baseline;border-radius: 0.25rem;">${item.created_at}</span>`;
								return td_created_at;
							}
						},
						{ 
							"data": "proveedor",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							},
							render: function (data, type,item,row) {
								var td_proveedor = '';
								var td_proveedor = `<span style="background: #6A1B9A;display: inline-block;padding: 0.25em 0.4em;font-weight: bold;line-height: 1;color: #fff;text-align: center;white-space: nowrap;vertical-align: baseline;border-radius: 0.25rem;">${item.proveedor}</span>`;
								return td_proveedor;
							}
						},
						{ 
							"data": "method",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							},
							render: function (data, type,item,row) {
								var td_method = '';
								var td_method = `<span class="badge bg-info text-white">${item.method}</span>`;
								return td_method;
							}
						},
						{ 
							"data": "bet_id",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							}
						},
						{ 
							"data": "cliente",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-right');
							}
						},
						{ 
							"data": "body",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							},
							render: function (data, type,item,row) {
								var td_body = '';
								if (item.body === '') {
									td_body = '';
								}else{
									td_body = `${item.body} <button type="button" style="float: right!important; padding: 5px 5px!important;" class="btn btn-sm btn-primary pull-left" title="Ver detalle" onclick="SecDevView_modal_detalle('${item.id}', 0)"><span class="fa fa-eye"></span></button>`;
								}					 
								return td_body;
							}
						},
						{ 
							"data": "response",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							},
							render: function (data, type,item,row) {
								var td_response = '';
								if (item.response === '') {
									td_response = '';
								}else{
									td_response = `${item.response} <button type="button" style="float: right!important; padding: 5px 5px!important;" class="btn btn-sm btn-primary pull-left" title="Ver detalle" onclick="SecDevView_modal_detalle('${item.id}', 1)"><span class="fa fa-eye"></span></button>`;
								}
								return td_response;
							}
						},
						{ 
							"data": "hash",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							}
						},
						{ 
							"data": "turno_id",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							}
						},
						{ 
							"data": "cc_id",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							}
						},
						{ 
							"data": "status",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							}
						},
						{ 
							"data": "usuario",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							},
							render: function (data, type,item,row) {
								var td_usuario = '';
								var td_usuario = `<span style="background: orange;display: inline-block;padding: 0.25em 0.4em;font-weight: bold;line-height: 1;color: #fff;text-align: center;white-space: nowrap;vertical-align: baseline;border-radius: 0.25rem;">${item.usuario}</span>`;
								return td_usuario;
							}
						},
						{ 
							"data": "updated_at",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							}
						},
						
						{ 
							"data": "updated_at",
							createdCell: function (td, cellData, item, row, col) {
								$(td).addClass('text-center');
							},
							render: function (data, type,item,row) {
								var ver = '';
							
								var ver = `<button class="btn btn-sm btn-success" title="Descargar" onclick="SecDevView_excel_registro('${item.id}','${item.created_at}')" id="SecDevView_excel_registro">
								<span class="fa fa-download"></span>
								</button>`;
								return ver;
							}
						}
					]
				});	
				 
			
			 
				return false;
			}
		return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "listar_log_apis_x_dia_filtros_error", "data": result});
			return false;
		}
	});

	
}


function SecDevView_excel_list_dia(dia) {

	var fec_inicio = dia+' 00:00:00';
	var fec_fin = dia+' 23:59:59';
 
	var data = {
		"accion": "SecDevView_excel_list_dia",
		"fec_inicio": fec_inicio,
		"fec_fin": fec_fin, 
		"dia": dia
	}

	$.ajax({
        url: "/sys/get_dev_view_log_apis.php",
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
            window.open(obj.path);
            loading(false);
        },
        error: function() {}
    });

}

function SecDevView_excel_registro(id,fecha) {

	var data = {
		"accion": "SecDevView_excel_registro",
		"id": id,
		"fecha": fecha
	}

	$.ajax({
        url: "/sys/get_dev_view_log_apis.php",
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
            window.open(obj.path);
            loading(false);
        },
        error: function() {}
    });

}

function SecDevView_exportar_excel_dl(gen_proveedor_seleccionado, gen_cliente_seleccionado, gen_usuario_seleccionado) {

	var fec_inicio = $('#SecDevView_fecha_inicio_log').val();
	var fec_fin = $('#SecDevView_fecha_fin_log').val();
	var proveedor = $('#SecDevView_proveedor_log').val()!= '' ? gen_proveedor_seleccionado : '0'; 
	var method = $('#SecDevView_method_log').val();
	var cliente = $('#SecDevView_cliente_log').val()!= '' ? gen_cliente_seleccionado : '0'; 
	var betid = $('#SecDevView_betid_log').val();
	var usuario = $('#SecDevView_usuario_log').val()!= '' ? gen_usuario_seleccionado : '0'; 


	var data = {
		"accion": "SecDevView_exportar_excel_dl",
		"fec_inicio": fec_inicio,
		"fec_fin": fec_fin,
		"proveedor": proveedor,
		"method": method,
		"cliente": cliente,
		"betid": betid,
		"usuario": usuario
	}

	$.ajax({
        url: "/sys/get_dev_view_log_apis.php",
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
            window.open(obj.path);
            loading(false);
        },
        error: function() {}
    });

}


function SecDevView_modal_detalle(id, campo) {
 
  	let data = {
	accion:'SecDevView_listar_modal_detalle',
	id: id
	};
	$.ajax({
		url: "/sys/get_dev_view_log_apis.php",
		type: 'POST',
		data: data,
		beforeSend: function () {
			loading("true");
		},
		complete: function () {
			loading();
		},
		
		success: function (resp) { //  alert(datat)
			var respuesta = JSON.parse(resp);
			auditoria_send({"proceso": "fusionar_clientes", "data": respuesta});
			 
			if (parseInt(respuesta.http_code) == 400) {
				swal('Aviso', respuesta.result, 'warning');
				return false;
			}
			if (parseInt(respuesta.http_code) == 200) {
				//	console.log(respuesta.result);
	
				if(parseInt(campo) == 0){
					$("#SecDevView_modal_tit").html('Body');
					$("#SecDevView_modal_campo").html(respuesta.result.body);
				}else{
					$("#SecDevView_modal_tit").html('Response');
					$("#SecDevView_modal_campo").html(respuesta.result.response);
				}
				
				$('#SecDevView_modal_detalle_registro').modal('show');
				return false;
			}
		return false;
		},
		error: function (result) {
			auditoria_send({"proceso": "fusionar_clientes_error", "data": result});
			return false;
		}
	});
	 
}
 