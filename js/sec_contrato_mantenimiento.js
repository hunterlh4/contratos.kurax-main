var tabla_contrato_mantenimiento_tipo= '';
var mantenimiento_contrato_tipo = '';
var mantenimiento_nombre_tipo = '';
var tipo_general_mantenimiento = '';


function sec_contrato_mantenimiento() {
	if (sec_id == "contrato") {

        $(".tab-contrato > div:not(:first-child)").hide();

        $(".tab-contrato > div:first-child").show();

        $("a.tab_btn").click(function (event) {
          event.preventDefault();

          // obtenemos el tab seleccionado
          var tab = $(this).data("tab");

          // ocultamos todos los div 
          $(".tab-contrato > div").hide();

          // Mostramos el div correspondiente al tab
          $(".tab-contrato > ." + tab).show();
        });

	
		$('.contrato_tipo_select2').select2();
		$('#estado_contrato_tipo').select2();
		cargar_tabla_mantenimientos_contrato_tipo(0);

		$('#tipo_mantenimiento_contrato').on('change',function(){
			mantenimiento_contrato_tipo =  $(this).val();
			if(mantenimiento_contrato_tipo == 0){
				cargar_tabla_mantenimientos_contrato_tipo(0);
			}else{
				cargar_tabla_mantenimientos_contrato_tipo(mantenimiento_contrato_tipo);
			}
		})

		$('#guardar_contrato_tipo').on('click', function () {

			nombre_tabla = $('#tipo_mantenimiento_contrato').val();
			var estado_tipo = $('#estado_tipo').val();
			var tipo_accion_modal = $('#tipo_accion_modal').val();
			var id_contrato_tipo = $('#id_contrato_tipo').val();
		
			var nombre_contrato_tipo = $.trim($('#nombre_contrato_tipo').val());
		
			if (nombre_contrato_tipo.length == 0) {
				alertify.error("Ingrese nombre de tipo.", 10);
				$("#nombre_contrato_tipo").val('').focus();
				return false;
			}
			if (nombre_contrato_tipo.length > 500) {
				alertify.error("Solo tamaño de caracteres manores a 500 .", 10);
				$("#nombre_contrato_tipo").val('').focus();
				return false;
			}
		
			swal(
				{
					title: "¿Desea guardar   " + nombre_contrato_tipo + " \n para tipo  " + mantenimiento_nombre_tipo+" , con estado '"+(estado_tipo == 1 ? 'Activo' : 'Inactivo') +"' ?",
					type: "warning",
					showCancelButton: true,
					closeOnConfirm: true,
					confirmButtonColor: "#3085d6",
					confirmButtonText: "Aceptar",
					cancelButtonText: "Cancelar"
				},
				function (result) {
					if (result) {
		
						set_data = {
							sec_mantenimientos_contrato_tipo_save: 1,
							nombre_tabla: nombre_tabla,
							nombre: nombre_contrato_tipo,
							estado: estado_tipo,
							nombre_tipo: mantenimiento_nombre_tipo,
							tipo_accion_modal: tipo_accion_modal,
							id_contrato_tipo: id_contrato_tipo
						};
						auditoria_send({"proceso":"sec_mantenimientos_contrato_tipo_save","data":set_data});
		
						$.ajax({
							url: "/sys/set_contrato_mantenimiento.php",
							data: set_data,
							type: 'POST',
							success: function (response_mant) {
								var obj_mant = jQuery.parseJSON(response_mant);
								// console.log(obj_mant);
								auditoria_send({"proceso":"sec_mantenimientos_contrato_tipo_save","data":obj_mant});
		
								if(obj_mant.error){
									swal(
										{
											title: "Error de Actualización" ,
											text: obj_mant.error,
											type: "warning",
											timer: 10000,
											showCancelButton: false,
											closeOnConfirm: true,
											confirmButtonColor: "#3085d6",
											confirmButtonText: "Aceptar",
										}
									);
		
								}else{
									swal(
										{
											// title: "Registro '" + nombre_contrato_tipo + "' \n se guardó en tipo '" + mantenimiento_nombre_tipo+"' \n con estado: '"+(estado_tipo == 1 ? 'Activo' : 'Inactivo')+"' " ,
											title: "Actualizado Correctamente",
											type: "success",
											timer: 10000,
											showCancelButton: false,
											closeOnConfirm: true,
											confirmButtonColor: "#3085d6",
											confirmButtonText: "Aceptar",
										}
									);
		
								}
		
		
								cargar_tabla_mantenimientos_contrato_tipo(nombre_tabla);
		
							},
							error: function () {
								set_data.error = obj_mant.error_ex;
								swal(
									{
										title: "Error de Actualización" ,
										text: obj_mant.error_ex,
										type: "warning",
										timer: 10000,
										showCancelButton: false,
										closeOnConfirm: true,
										confirmButtonColor: "#3085d6",
										confirmButtonText: "Aceptar",
									});
								auditoria_send({ "proceso": "sec_mantenimientos_contrato_tipo_list", "data": set_data });
							}
						})
					} else {
						if ($('#tipo_accion_modal').val() == 'new') {
							sec_mantenimientos_contrato_tipo_modal();
		
						} else {
							$('#txtipo').html(estado_tipo);
							setTimeout(function () {
								$("#nombre_contrato_tipo").focus();
		
							}, 500);
						}
					}
				}
			);
		
		})
		

		$('#tab-contrato a').click(function (e) {
			e.preventDefault()

		  })


		$('#tab-contrato a[href="#mant_tipos"]').click(function (e) {
			e.preventDefault()
			sec_contrato_mantenimiento_servicio_publico();
		})
		$('#tab-contrato a[href="#mant_responsable_area"]').click(function (e) {
			e.preventDefault()
			sec_con_res_ar_listar_responsable_area();
		})
		$('#tab-contrato a[href="#mant_directores_area"]').click(function (e) {
			e.preventDefault()
			sec_con_dir_ar_listar_director_area();
		})
		$('#tab-contrato a[href="#mant_correo_metodo"]').click(function (e) {
			e.preventDefault()
			sec_contrato_mantenimiento_correo_metodo();
		})
		$('#tab-contrato a[href="#mant_notificacion_contrato"]').click(function (e) {
			e.preventDefault()
			sec_contrato_mantenimiento_notificacion_contrato();
		})
		$('#tab-contrato a[href="#mant_correo_cargo"]').click(function (e) {
			e.preventDefault()
			sec_contrato_mantenimiento_correo_cargo();
		})
		$('#tab-contrato a[href="#mant_correlativo"]').click(function (e) {
			e.preventDefault()
			sec_contrato_mantenimiento_correlativo();
		})
		$('#tab-contrato a[href="#mant_cambio_tipo_contrato"]').click(function (e) {
			e.preventDefault()
			sec_contrato_mantenimiento_tipo_contrato();
		})
		$('#tab-contrato a[href="#mant_servicio_publico"]').click(function (e) {
			e.preventDefault()
			sec_contrato_mantenimiento_servicio_publico();
		})


		
	}
}


function sec_mantenimientos_contrato_tipo_modal(){
	mantenimiento_nombre_tipo = $('#tipo_mantenimiento_contrato option:selected').data('nombre_tipo');

	tipo_general_mantenimiento =  $('#tipo_mantenimiento_contrato option:selected').data('nombre_tipo');
	if(tipo_general_mantenimiento == 0){
		swal(
			{
				title: "Seleccione un tipo ",
				type: "warning",
				timer: 10000,
				showCancelButton: false,
				closeOnConfirm: true,
				confirmButtonColor: "#3085d6",
				confirmButtonText: "Aceptar",
			}
		);
	}else{
		$('.titulo_modal_contrato_tipo').html(`Nuevo registro en tipo : <label>${mantenimiento_nombre_tipo}</label>`)

		$('#tipo_accion_modal').val('new');
		$("#nombre_contrato_tipo").val('');
		$("#estado_tipo").val('1')

		setTimeout(function() {
			$("#nombre_contrato_tipo").focus();

		  }, 500);
		setTimeout(function() {
			$('#estado_tipo').select2();


		}, 100);
		$("#modal_nuevo_contrato_tipo").modal("show");

	}

}

function cargar_tabla_mantenimientos_contrato_tipo(tipo_general) {
	$("#modal_nuevo_contrato_tipo").modal("hide");
	// alert(tipo_general);
	set_data = { sec_mantenimientos_contrato_tipo_list: 1 ,tipo_general: tipo_general};
	$.ajax({
		url: "/sys/set_contrato_mantenimiento.php",
		data: set_data,
		type: 'POST',
		beforeSend: function () {
			loading(true);
		},
		complete: function () {
			loading();
		},
		success: function (response_table_mant) {//  alert(datat)
			var respuesta_mantenimiento_list = JSON.parse(response_table_mant);
			// console.log(respuesta_mantenimiento_list);
			var data_mantenimiento_contrato = respuesta_mantenimiento_list.lista;
			// set_data.curr_login = resp.curr_login;
			auditoria_send({ "proceso": "sec_mantenimientos_contrato_tipo_list", "data": set_data });

			tabla_contrato_mantenimiento_tipo = $('#tbl_datos_mantenimientos_contrato_tipo').DataTable({

				"bDestroy": true,
				"rowCallback": function (row, data, index) {
					$('td:eq(0)', row).html(index + 1);
				 },
				"language": {
					"search": "Buscar:",
					"lengthMenu": "Mostrar _MENU_ registros por página",
					"zeroRecords": "No se encontraron registros",
					"info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
					"infoEmpty": "No hay registros",
					"infoFiltered": "(filtrado de _MAX_ total records)",
					"paginate": {
						"first": "Primero",
						"last": "Último",
						"next": "Siguiente",
						"previous": "Anterior"
					},
					sProcessing: "Procesando..."
				},
				data: data_mantenimiento_contrato,

				"initComplete": function (settings, json) {



					$("#estado_contrato_tipo").off("change").on("change",function(){
						var val=$(this).val();
						tabla_contrato_mantenimiento_tipo.column(2).search("\\b" + val + "\\b", true, false).draw();
						tabla_contrato_mantenimiento_tipo.columns.adjust();
					})
                },
				order: [],
				columns: [
					{
						title: "ID",
						data: null,
						class: "text-right"
					},
					{
						title: "Nombre",
						data: "nombre",
						class: "text-right"

					},
					{
						title: "Estado",
						data: "status",
						class: "text-right"

					},
					{
						title: "Registrado por",
						data: "usuario",
						class: "text-right"

					},
					{
						title: "Fecha de registro",
						data: "created_at",
						class: "text-right"
					},
					{
						title: "Modificado por ",
						data: "usuario_actualiza",
						class: "text-right"

					},
					{
						title: "Fecha Actualización",
						data: "updated_at",
						class: "text-right"

					},
					{
						title: "Acciones",


						width: "150px",
						"render": function (data, type, row) {
							// var ided = row["id"];
							var man_estado = row["status"]=='Activo'?1:0;
							var mant_nombre = row["nombre"];
							var html = "<div style='text-align: center;'>";
							var btn_class = "btn btn-sm btn-success indice_inflacion_historial";


							html += ' <a class="btn btn-rounded btn-default btn-sm btn-edit" onclick="editar_contrato_tipo(\''+tipo_general+'\','+row["id"]+',\''+mant_nombre+'\','+man_estado+')" title="Editar">';
							html += ' <i class="glyphicon glyphicon-edit"></i>';

							html += '</a>';
							html += '</div>';
							return html;
						}

					},

				],
			});
		},
		error: function () {
			set_data.error = respuesta_mantenimiento_list.error_ex;
			swal(
				{
					title: "Error de Actualización" ,
					text: respuesta_mantenimiento_list.error_ex,
					type: "warning",
					timer: 10000,
					showCancelButton: false,
					closeOnConfirm: true,
					confirmButtonColor: "#3085d6",
					confirmButtonText: "Aceptar",
				}
			);
			auditoria_send({ "proceso": "sec_mantenimientos_contrato_tipo_list", "data": set_data });
		}
	});
}

function editar_contrato_tipo(tipo,id,nombre,estado){
	mantenimiento_nombre_tipo = $('#tipo_mantenimiento_contrato option:selected').data('nombre_tipo');
	$('.titulo_modal_contrato_tipo').html(`Editar registro en tipo : <label>${mantenimiento_nombre_tipo}</label>`)


	$('#estado_tipo').val(estado);
	$('#estado_tipo').select2();

	$('#tipo_accion_modal').val('edit');
	$('#id_contrato_tipo').val(id);
	setTimeout(function() {
		$("#nombre_contrato_tipo").val(nombre).focus();
	  }, 500);
	$("#modal_nuevo_contrato_tipo").modal("show");

}




///