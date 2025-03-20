function sec_mepa_solicitud_movilidad_volante_detalle()
{
	// INICIO FORMATO COMBO CON BUSQUEDA
	$(".sec_mepa_movilidad_volante_detalle_select_filtro").select2({ width: '100%' });
	// FIN FORMATO COMBO CON BUSQUEDA

	// INICIO FORMATO CAMPO MONTO
	$(".sec_mepa_movilidad_volante_param_monto").on({
		"focus": function (event) {
			$(event.target).select();
		},
		"change": function (event) {
			
			if(parseFloat($(event.target).val().replace(/\,/g, ''))>0)
			{
				$(event.target).val(parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2));
				$(event.target).val(function (index, value ) {
					return value.replace(/\D/g, "")
								.replace(/([0-9])([0-9]{2})$/, '$1.$2')
								.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
				});
			} else {
				$(event.target).val("0.00");
			}
		}
	});
	// FIN FORMATO CAMPO MONTO

	sec_mepa_movilidad_volante_table_detalle();
}

$('#sec_mepa_movilidad_volante_param_tipo_centro_costo').change(function () 
{
    $("#sec_mepa_movilidad_volante_param_tipo_centro_costo option:selected").each(function ()
    {   
        var selectValor = $(this).val();

        if(selectValor != 0)
        {
            sec_mepa_movilidad_volante_detalle_listar_centro_costo(selectValor);
        }
        else
        {
        	alertify.error('Seleccione Tipo Centro Costo',5);
	        $("#sec_mepa_movilidad_volante_param_tipo_centro_costo").focus();
	        setTimeout(function() 
	        {
	            $('#sec_mepa_movilidad_volante_param_tipo_centro_costo').select2('open');
	        }, 200);

	        return false;
        }
    });
});

function sec_mepa_movilidad_volante_detalle_listar_centro_costo(tipo_ceco) 
{   
    var data = {
        "accion": "sec_mepa_movilidad_volante_detalle_listar_centro_costo",
        "tipo_ceco": tipo_ceco
    }
    
    var array_cecos = [];
    
    $.ajax({
        url: "/sys/set_mepa_movilidad_volante_detalle.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            
            var respuesta = JSON.parse(resp);
            auditoria_send({ "respuesta": "sec_mepa_movilidad_volante_detalle_listar_centro_costo", "data": respuesta });
            
            if (parseInt(respuesta.http_code) == 400) 
            {
                var html = '<option value="0">-- Seleccione --</option>';
                $("#sec_mepa_movilidad_volante_param_centro_costo").html(html).trigger("change");

                setTimeout(function() {
                    $('#sec_mepa_movilidad_volante_param_centro_costo').select2('open');
                }, 500);

                return false;

            }
            
            if (parseInt(respuesta.http_code) == 200) 
            {
                array_cecos.push(respuesta.result);
            
                var html = '<option value="0">-- Seleccione --</option>';

                for (var i = 0; i < array_cecos[0].length; i++) 
                {
                    html += '<option value=' + array_cecos[0][i].ceco  + '>' + array_cecos[0][i].nombre + ' [' + array_cecos[0][i].ceco + ']' + '</option>';
                }

                $("#sec_mepa_movilidad_volante_param_centro_costo").html(html).trigger("change");

                setTimeout(function() {
                    $('#form_modal_sec_prestamo_slot_param_local_destino').select2('open');
                }, 500);

                return false;
            }
        },
        error: function() {}
    });
}

function sec_mepa_movilidad_volante_table_detalle()
{
	
	if(sec_id == "mepa" && sub_sec_id == "solicitud_movilidad_volante_detalle")
	{
		var param_id_caja_chica_movilidad = $("#sec_mepa_movilidad_volante_id_caja_chica_movilidad").val();

		var data = {
			"accion": "sec_mepa_solicitud_movilidad_volante_listar",
			"param_id_caja_chica_movilidad" : param_id_caja_chica_movilidad
		}

		auditoria_send({"proceso":"sec_mepa_solicitud_movilidad_volante_listar","data":data});
				
		tabla = $("#sec_mepa_movilidad_volante_table_detalle").dataTable(
		{
			language:{
				"decimal":        "",
				"emptyTable":     "No existen registros",
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
				},
				"aProcessing" : true,
				"aServerSide" : true,

				"ajax" :
				{
					url : "/sys/set_mepa_movilidad_volante_detalle.php",
					data : data,
					type : "POST",
					dataType : "json",
					beforeSend: function( xhr ) {
						loading(true);
					},
					complete: function(){
						loading(false);
					},
					error : function(e)
					{
						console.log(e.responseText);
					}
				},
				"bDestroy" : true,
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
							text: 'PDF <i class="fa fa-file-pdf-o"></i>',
							action: function (e, dt, node, config) {
								sec_mepa_movilidad_volante_generate_report_pdf_mobility_expenses($('#sec_mepa_movilidad_volante_id_caja_chica_movilidad').val());
							}
						},
						{
							text: 'Cerrar Gastos de Movilidad',
							className: function (e, dt, node, config) {
								
								var hidden = '';
								if ($('#sec_mepa_movilidad_volante_id_estado_cierre').val() == "2") 
								{
									hidden = 'invisible';
								}
								return 'btn btn-danger ' + hidden;
							},
							action: function (e, dt, node, config) {
								sec_mepa_movilidad_volante_close_mobility_expenses($('#sec_mepa_movilidad_volante_id_caja_chica_movilidad').val());
							}
						}
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
				"order": [
					[0, 'asc']
				],
				columnDefs: [{
					className: 'text-center',
					targets: [0, 1, 2, 3, 4, 5]
				},
					{ targets: 0, visible: true },
					{ targets: 1, orderable: false },
					{ targets: 2, orderable: false },
					{ targets: 3, orderable: false },
					{ targets: 4, orderable: false },
					{ targets: 5, orderable: false }
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
						group_assoc = group.replace(' ', "_");
						if (typeof total[group_assoc] != 'undefined')
						{
							total[group_assoc] = total[group_assoc] + intVal(api.column(4).data()[i]);
						}
						else
						{
							total[group_assoc] = intVal(api.column(4).data()[i]);
						}
						
						if (last !== group)
						{
							$(rows).eq(i).before(
								'<tr style="background-color: #ddd !important;"><td class="text-center">' + '<h4><span class="badge badge-dark">' + group + '</span></h4></td><td></td><td></td><td></td><td class=" text-center ' + group_assoc + '"></td><td></td></tr>'
							);

							last = group;
						}
					});

					var sumTotalMonto = 0;
					for (var key in total)
					{
						$("." + key).html('<h4><span class="badge badge-primary">' + "S/." + total[key].toFixed(2) + '</span></h4>');
						sumTotalMonto += total[key];
					}

					$("#sec_mepa_movilidad_volante_table_detalle_total_monto").html(sumTotalMonto.toFixed(2));
				}

			}
		).DataTable();

	}
	else
	{
		alertify.error('No estas en la vista correspondiente, por favor contactarse con Sistemas',5);
		return false;
	}
}

$("#form_sec_mepa_solicitud_movilidad_volante").submit(function(e)
{
	e.preventDefault();
	
	var sec_mepa_movilidad_volante_param_fecha = $("#sec_mepa_movilidad_volante_param_fecha").val();
	var sec_mepa_movilidad_volante_param_destino = $("#sec_mepa_movilidad_volante_param_destino").val();
	var sec_mepa_movilidad_volante_param_tipo_centro_costo = $("#sec_mepa_movilidad_volante_param_tipo_centro_costo").val();
	var sec_mepa_movilidad_volante_param_centro_costo = $("#sec_mepa_movilidad_volante_param_centro_costo").val();
	var sec_mepa_movilidad_volante_param_motivo = $("#sec_mepa_movilidad_volante_param_motivo").val();
	var sec_mepa_movilidad_volante_param_monto = $("#sec_mepa_movilidad_volante_param_monto").val();
	var idCajaChicaMovilidad = $("#sec_mepa_movilidad_volante_id_caja_chica_movilidad").val();

	if(sec_mepa_movilidad_volante_param_fecha == "")
	{
		alertify.error('Seleccione Fecha Detalle',5);
		$("#sec_mepa_movilidad_volante_param_fecha").focus();
		return false;
	}

	if(sec_mepa_movilidad_volante_param_destino.trim() == "")
	{
		alertify.error('Ingrese Paratida - Destino',5);
		$("#sec_mepa_movilidad_volante_param_destino").focus();
		return false;
	}

	if(sec_mepa_movilidad_volante_param_tipo_centro_costo == "0")
	{
		alertify.error('Seleccione Tipo Centro Costo',5);
        $("#sec_mepa_movilidad_volante_param_tipo_centro_costo").focus();
        setTimeout(function() 
        {
            $('#sec_mepa_movilidad_volante_param_tipo_centro_costo').select2('open');
        }, 200);

        return false;
	}

	if(sec_mepa_movilidad_volante_param_centro_costo == "0")
	{
		alertify.error('Seleccione Centro Costo',5);
        $("#sec_mepa_movilidad_volante_param_centro_costo").focus();
        setTimeout(function() 
        {
            $('#sec_mepa_movilidad_volante_param_centro_costo').select2('open');
        }, 200);

        return false;
	}

	if(sec_mepa_movilidad_volante_param_motivo.trim() == "")
	{
		alertify.error('Ingrese Motivo Movilidad',5);
		$("#sec_mepa_movilidad_volante_param_motivo").focus();
		return false;
	}

	if(sec_mepa_movilidad_volante_param_monto == "")
	{
		alertify.error('Ingrese Monto',5);
		$("#sec_mepa_movilidad_volante_param_monto").focus();
		return false;
	}

	if(sec_mepa_movilidad_volante_param_monto == 0)
	{
		alertify.error('Monto tiene que ser diferente a cero',5);
		$("#sec_mepa_movilidad_volante_param_monto").focus();
		return false;
	}

	if(idCajaChicaMovilidad == "")
	{
		alertify.error('No se encontro el Id de la movilidad, por favor contactarse con Soporte',5);
		return false;
	}

	swal(
	{
		title: '¿Está seguro de registrar la Movilidad?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{
			var dataForm = new FormData($("#form_sec_mepa_solicitud_movilidad_volante")[0]);
			dataForm.append("accion","sec_mepa_guardar_solicitud_movilidad_volante");
			dataForm.append("id_caja_chica_movilidad", idCajaChicaMovilidad);
			dataForm.append("param_fecha", sec_mepa_movilidad_volante_param_fecha);
			dataForm.append("param_destino", sec_mepa_movilidad_volante_param_destino);
			dataForm.append("param_tipo_centro_costo", sec_mepa_movilidad_volante_param_tipo_centro_costo);
			dataForm.append("param_centro_costo", sec_mepa_movilidad_volante_param_centro_costo);
			dataForm.append("param_motivo", sec_mepa_movilidad_volante_param_motivo);
			dataForm.append("param_monto", sec_mepa_movilidad_volante_param_monto);

			$.ajax({
				url: "sys/set_mepa_movilidad_volante_detalle.php",
				type: 'POST',
				data: dataForm,
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function( xhr ) {
					loading(true);
				},
				success: function(data){
					
					var respuesta = JSON.parse(data);
					
					auditoria_send({ "respuesta": "sec_mepa_guardar_solicitud_movilidad_volante", "data": respuesta });
					if(parseInt(respuesta.http_code) == 200)
					{
						swal({
							title: "Registro exitoso",
							text: "Se registrado exitosamente",
							html:true,
							type: "success",
							timer: 6000,
							closeOnConfirm: false,
							showCancelButton: false
						},
					    function (isConfirm) {
					        window.location.href = "?sec_id=mepa&sub_sec_id=solicitud_movilidad_volante_detalle&cc_movilidad="+idCajaChicaMovilidad;
					    });

						setTimeout(function() {
							window.location.href = "?sec_id=mepa&sub_sec_id=solicitud_movilidad_volante_detalle&cc_movilidad="+idCajaChicaMovilidad;
						}, 5000);

						return true;
					}
					else {
						swal({
							title: "Error al guardar Solicitud",
							text: respuesta.error,
							html:true,
							type: "warning",
							closeOnConfirm: false,
							showCancelButton: false
						});
						return false;
					}
				},
				complete: function(){
					loading(false);
				}
			});
		}
	}
	);
});

function sec_mepa_movilidad_volante_eliminar_detalle(id_detalle_movilidad, id_movilidad)
{

	swal(
	{
		title: '¿Está seguro de eliminar el detalle?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: 'No',
		closeOnConfirm: false,
		closeOnCancel: true
	},
	function(isConfirm)
	{
		if (isConfirm) 
		{
			var data = {
				"accion": "sec_mepa_movilidad_volante_eliminar_detalle",
				"id_detalle_movilidad" : id_detalle_movilidad,
				"id_movilidad" : id_movilidad
			}

			auditoria_send({ "proceso": "sec_mepa_movilidad_volante_eliminar_detalle", "data": data });

			$.ajax({
				url : "/sys/set_mepa_movilidad_volante_detalle.php",
				type : "POST",
				data : data,
				//contentType : false,
				//processData : false,
				beforeSend: function( xhr ) {
					loading(true);
				},
				success : function(resp)
				{
					var respuesta = JSON.parse(resp);

					if(parseInt(respuesta.http_code) == 200)
					{
						swal({
							title: "Eliminado!",
							text: respuesta.message,
							type: "success",
							timer: 5000,
							closeOnConfirm: false
						});
					}
					else if(parseInt(respuesta.http_code) == 400)
					{

						swal({
							title: "No se pudo eliminar!",
							text: respuesta.message,
							type: "info",
							timer: 9000,
							closeOnConfirm: false
						});
					}
					else
					{
						swal({
							title: "Error!",
							text: "Ocurrio un error: "+ respuesta.message +", pongase en contacto con el personal de SOPORTE",
							type: "warning",
							timer: 5000,
							closeOnConfirm: false
						});
					}
					//tabla.ajax.reload();
				},
				complete: function(){
					loading(false);
					location.reload(true);
				}
			});
		}
	}
	);
}

const sec_mepa_movilidad_volante_generate_report_pdf_mobility_expenses = (cc_movilidad_id) => {
	
	var id_usuario = $('#sec_mepa_movilidad_volante_id_usuario_login').val();
	var data = {
		'accion': 'sec_mepa_generar_reporte_pdf_movilidad',
		'cc_movilidad_id': cc_movilidad_id,
		'id_usuario': id_usuario
	}
	window.open('/sys/get_mepa_movilidad_reporte.php?'+"accion=sec_mepa_generar_reporte_pdf_movilidad&"+"cc_movilidad_id="+data.cc_movilidad_id);	
}

const sec_mepa_movilidad_volante_close_mobility_expenses = (cc_movilidad_id) => {
	
	swal({
		title: "¿Estás seguro?",
		text: "¡No podra ingresar nuevos gastos de movilidad!",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Si, cerrar",
		cancelButtonText: "No, cancelar",
		closeOnConfirm: false,
		closeOnCancel: false,

	},
		function (isConfirm) {
			var id_usuario = $('#sec_mepa_movilidad_volante_id_usuario_login').val();
			var data = {
				'accion': 'sec_mepa_get_data_caja_chica_cerrar_detalle_movilidad',
				'cc_movilidad_id': cc_movilidad_id,
				'id_usuario': id_usuario
			}
			if (isConfirm) {
				$.ajax({
					type: "POST",
					data: data,
					url: 'sys/get_mepa_movilidad.php',
					cache: false,
					beforeSend: function( xhr ) {
						loading(true);
					},
					success: function (response) {
						
						var jsonData = JSON.parse(response);
						if(jsonData.error == false)
						{
							swal("Cerrado", jsonData.message, "success");
						}
						else
						{
							swal("Error", jsonData.message, "error");
						}
					},
					complete: function(){
						loading(false);
						location.reload(true);
					}
				});
			}
			else
			{
				swal("Cancelado", "Los datos no se enviaron", "error");
			}
		});
}
