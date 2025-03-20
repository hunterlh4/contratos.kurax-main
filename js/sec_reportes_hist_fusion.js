function sec_reportes_hist_fusion() {
	if (sec_id == 'reportes' && sub_sec_id=='hist_fusion') {
 
		$('#SecRepTel_fecha_inicio_dl').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepTel_fecha_fin_dl').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepTel_fecha_inicio_dl').val($('#g_fecha_actual').val());
		$('#SecRepTel_fecha_fin_dl').val($('#g_fecha_actual').val());

 
		//$('#SecRepTel_cliente_hf').select2();

		$('#SecRepTel_cliente_hf').autocomplete({
            source: '/sys/get_reportes_hist_fusion.php?accion=SecRepTel_listar_clientes_hf',
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

		$('#SecRepTel_usuario_hf').select2();
	 
		$('#SecRepTel_fecha_inicio_dl').change(function () {
			var var_fecha_change = $('#SecRepTel_fecha_inicio_dl').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepTel_fecha_inicio_dl").val($("#g_fecha_actual").val());
			}
		});
		$('#SecRepTel_fecha_fin_dl').change(function () {
			var var_fecha_change = $('#SecRepTel_fecha_fin_dl').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepTel_fecha_fin_dl").val($("#g_fecha_actual").val());
			}
		});

		$('#SecRepTel_btn_buscar_hf').click(function() {
			SecRepTel_buscar_hf(gen_cliente_seleccionado);
		});

		$("#SecRepTel_btn_exportar_dl").on('click', function () {
			SecRepTel_exportar_excel_dl(gen_cliente_seleccionado);
		});
 
	}
}


function SecRepTel_buscar_hf(gen_cliente_seleccionado){
 
	var fec_inicio = $('#SecRepTel_fecha_inicio_dl').val();
	var fec_fin = $('#SecRepTel_fecha_fin_dl').val();
	var cliente = $('#SecRepTel_cliente_hf').val()!= '' ? gen_cliente_seleccionado : '0'; 
	var usuario = $('#SecRepTel_usuario_hf').val();
 
	let data = {
		accion:'listar_transacciones_hist_fusion',
		fec_inicio: fec_inicio,
		fec_fin: fec_fin,
		cliente: cliente,
		usuario: usuario,
	};

	SecRepTel_exportar_excel_dl(gen_cliente_seleccionado);
	$.ajax({
		url: "/sys/get_reportes_hist_fusion.php",
		type: 'POST',
		data: data,
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.status) == 200) {
				$('#reportes_hist_fusion_div_tabla').html(respuesta.result);
				sec_reportes_hist_fusion_initialize_table('sec_reportes_hist_fusion');
				return false;
			}
		},
		error: function() {}
	});
}

function SecRepTel_exportar_excel_dl(gen_cliente_seleccionado) {
 
	var fec_inicio = $('#SecRepTel_fecha_inicio_dl').val();
	var fec_fin = $('#SecRepTel_fecha_fin_dl').val();
	var cliente = $('#SecRepTel_cliente_hf').val()!= '' ? gen_cliente_seleccionado : '0'; 
	var usuario = $('#SecRepTel_usuario_hf').val();

 
	document.getElementById('SecRepTel_btn_exportar_dl').innerHTML = '<a href="export.php?export=reporte_hist_fusion&amp;type=lista&amp;fec_inicio='+fec_inicio+'&amp;fec_fin='+fec_fin+'&amp;cliente='+cliente+'&amp;usuario='+usuario+'" class="btn btn-success" style="width: 100%;" download="reporte_hist_fusion.xls"><span class="glyphicon glyphicon-download-alt"></span> Exportar</a>';

}


function sec_tlv_ver_detalle_historial_f(id) {

	let id_temp =0;
  	let data = {
	accion:'listar_detalle_hist_fusion',
	id: id,
	};
 

		$.ajax({
			url: "/sys/get_reportes_hist_fusion.php",
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
				//console.log(respuesta);
				if (parseInt(respuesta.http_code) == 400) {
					swal('Aviso', respuesta.result, 'warning');
					return false;
				}
				if (parseInt(respuesta.http_code) == 200) {
					$('#modal_detalle_clientes_duplicados').modal('show');
					console.log(respuesta.result);
					list_cli_dup =  respuesta.result;
					sec_tlv_rep_limpiar_tabla_duplicados();

					const resultado_fusion = list_cli_dup.reduce((previous, current) => {
						return current.id > previous.id ? current : previous;
					  });

					$.each(respuesta.result, function (index, item) {
						
						if(resultado_fusion.id != item.id ){

							$('#modal_tabla_clientes_duplicados tbody').append(
								'<tr>' +

								'	<td>' + item.tipo_doc_nomb + '</td>' +
								'	<td>' + item.num_doc_f + '</td>' +
								'	<td>' + item.telefono_f + '</td>' +
								'	<td>' + item.cliente_f + '</td>' +
								'	<td>' + item.correo_f + '</td>' +
								'	<td>' + item.player_id_f + '</td>' +
								'	<td>' + item.web_id_f + '</td>' +
								'	<td>' + item.web_full_name_f + '</td>' +				
								'	<td>' + item.transac_f + '</td>' +
								'	<td>' + item.balance_f + '</td>' +

								'</tr>'
							);

						}else{

							$('#modal_tabla_clientes_duplicados').append(
								'<tfoot>'+
								'<tr style="background: #6bed86cc;">'+
								'<td align="center" colspan="10" >RESULTADO FUSIÓN</td>'+
								'</tr>'+
								'<tr >'+
								'	<td>' + resultado_fusion.tipo_doc_nomb + '</td>' +
								'	<td>' + resultado_fusion.num_doc_f + '</td>' +
								'	<td>' + resultado_fusion.telefono_f + '</td>' +
								'	<td>' + resultado_fusion.cliente_f + '</td>' +
								'	<td>' + resultado_fusion.correo_f + '</td>' +
								'	<td>' + resultado_fusion.player_id_f + '</td>' +
								'	<td>' + resultado_fusion.web_id_f + '</td>' +
								'	<td>' + resultado_fusion.web_full_name_f + '</td>' +				
								'	<td>' + resultado_fusion.transac_f + '</td>' +
								'	<td>' + resultado_fusion.balance_f + '</td>' +
								'</tr>'+
								'</tfoot>' 

							);

						}

						id_temp =item.id;

					});
				 
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
 

function sec_tlv_rep_limpiar_tabla_duplicados(){
	$('#modal_tabla_clientes_duplicados').empty();	
	$('#modal_tabla_clientes_duplicados').append(
		'<thead>'+
		'<tr style="background: #6ba9ff;">'+
		 
			'<td>TIPO DOC</td>'+
			'<td >NUM DOC</td>'+							
			'<td >CELULAR</td>'+
			'<td >CLIENTE</td>'+
			'<td >CORREO</td>'+
			'<td >PLAYER ID</td>'+
			'<td >WEB ID</td>'+
			'<td >WEB FULL NAME</td>'+
			'<td >NUM TRANSAC</td>'+
			'<td >BALANCE</td>'+
		 
		'</tr>'+
		'</thead>' +
		'<tbody>' +
		'</tbody'
	);
}

function sec_reportes_hist_fusion_initialize_table(tabla){
	$('#' + tabla).DataTable({
		"bDestroy": true,
		scrollX: true,
		language:{
			"decimal":        "",
			"emptyTable":     "Tabla vacia",
			"info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
			"infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
			"infoFiltered":   "(filtered from _MAX_ total entradas)",
			"infoPostFix":    "",
			"thousands":      ",",
			"lengthMenu":     "Mostrar _MENU_ entradas",
			"loadingRecords": "Cargando...",
			"processing":     "Procesando...",
			"search":         "Filtrar:",
			"zeroRecords":    "Sin resultados",
			"paginate": {
				"first":      "Primero",
				"last":       "Ultimo",
				"next":       "Siguiente",
				"previous":   "Anterior"
			},
			"aria": {
				"sortAscending":  ": activate to sort column ascending",
				"sortDescending": ": activate to sort column descending"
			}
		}
		,aLengthMenu:[10, 20, 30, 40, 50]
		,"order": [[ 0, 'desc' ]]
	});  
}
 